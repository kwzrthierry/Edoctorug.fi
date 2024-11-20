<?php
require 'test 3/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $loanId = $_POST['loanId'];
    $status = $_POST['status'];
    $reason = isset($_POST['reason']) ? $_POST['reason'] : '';

    // Prepare and execute the update query for loans_application table
    $stmt = $conn->prepare("UPDATE loans_application SET status = ?, reason = ? WHERE id = ?");
    if (!$stmt) {
        error_log("Prepare failed: (" . $conn->errno . ") " . $conn->error);
        die("Database error. Please try again later.");
    }
    $stmt->bind_param("ssi", $status, $reason, $loanId);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        if ($status === 'approved') {
            // Fetch the necessary data to insert into due_loans
            $loanStmt = $conn->prepare("SELECT name, national_id_number, email, phone, loan_amount FROM loans_application WHERE id = ?");
            if (!$loanStmt) {
                error_log("Prepare failed: (" . $conn->errno . ") " . $conn->error);
                die("Database error. Please try again later.");
            }
            $loanStmt->bind_param("i", $loanId);
            $loanStmt->execute();
            $loanResult = $loanStmt->get_result();
            $loanData = $loanResult->fetch_assoc();

            if (!$loanData) {
                error_log("No loan data found for loanId: " . $loanId);
                die("No loan data found. Please try again later.");
            }

            // Calculate loan amount with 10% interest
            $loanAmountWithInterest = $loanData['loan_amount'] * 1.10;

            // Fetch session data for the approver
            session_start();
            $approverNid = $_SESSION['national_id'];

            // Fetch the approver's name from the users table
            $adminStmt = $conn->prepare("SELECT name, email FROM users WHERE national_id_number = ? AND user_type = 'admin'");
            if (!$adminStmt) {
                error_log("Prepare failed: (" . $conn->errno . ") " . $conn->error);
                die("Database error. Please try again later.");
            }
            $adminStmt->bind_param("s", $approverNid);
            $adminStmt->execute();
            $adminResult = $adminStmt->get_result();
            $adminData = $adminResult->fetch_assoc();
            $approverName = $adminData['name'];
            $approverEmail = $adminData['email'];

            if (!$approverName) {
                error_log("No admin data found for national_id: " . $approverNid);
                die("No approver data found. Please try again later.");
            }

            // Calculate the due date (e.g., 30 days from approval)
            $approvalDate = date('Y-m-d');
            $dueDate = date('Y-m-d', strtotime($approvalDate . ' + 30 days'));

            // Prepare and execute the insert query for due_loans
            $dueStmt = $conn->prepare("INSERT INTO due_loans (loan_id, user_id, loanee_name, email, phone_number, approval_date, due_date, approved_by, approver_email, loan_amount) 
                                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            if (!$dueStmt) {
                error_log("Prepare failed: (" . $conn->errno . ") " . $conn->error);
                die("Database error. Please try again later.");
            }
            $dueStmt->bind_param("issssssssd", $loanId, $loanData['national_id_number'], $loanData['name'], $loanData['email'], $loanData['phone'], $approvalDate, $dueDate, $approverName, $approverEmail, $loanAmountWithInterest);
            $dueStmt->execute();

            if ($dueStmt->affected_rows > 0) {
                echo 'Status updated and record added to due_loans successfully.';
            } else {
                error_log("Failed to insert into due_loans for loanId: " . $loanId . ", Error: " . $dueStmt->error);
                echo 'Status updated but failed to add record to due_loans.';
            }

            $dueStmt->close();
            $loanStmt->close();
            $adminStmt->close();
        } else {
            echo 'Status updated successfully.';
        }
    } else {
        error_log("Failed to update status for loanId: " . $loanId . ", Error: " . $stmt->error);
        echo 'Failed to update status.';
    }

    $stmt->close();
} else {
    echo 'Invalid request method.';
}

$conn->close();
?>

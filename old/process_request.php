<?php
require 'test 3/db_connection.php';
session_start();
$userID = $_SESSION['user_id_lead'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $request_id = $_POST['request_id'];
    $comment = $_POST['comment'];
    $action_type = $_POST['action_type'];
    $loan_application_id = isset($_POST['loan_application']) ? $_POST['loan_application'] : null;

    // Determine the status based on the action type
    $status = ($action_type == 'approve') ? 'approved' : 'rejected';

    // Update lead_requests table
    $updateRequestSql = "
        UPDATE lead_requests 
        SET 
            request_status = ?, 
            admin_comments = ?, 
            reviewed_at = NOW() 
        WHERE id = ?";
    $stmt = $conn->prepare($updateRequestSql);
    $stmt->bind_param("ssi", $status, $comment, $request_id);
    $stmt->execute();

    // Only insert into leads table if the action is 'approve'
    if ($action_type == 'approve' && $loan_application_id) {
        // Fetch loan application details
        $loanAppSql = "SELECT * FROM loans_application WHERE id = ?";
        $stmt = $conn->prepare($loanAppSql);
        $stmt->bind_param("i", $loan_application_id);
        $stmt->execute();
        $loanApp = $stmt->get_result()->fetch_assoc();

        // Insert into leads table
        $insertLeadSql = "
            INSERT INTO leads (user_id, lead_national_id, lead_name, lead_contact, loan_amount, status, created_at, loan_id)
            VALUES (?, ?, ?, ?, ?, 'pending', NOW(), ?)";
        $stmt = $conn->prepare($insertLeadSql);
        $stmt->bind_param("isssss", $userID, $loanApp['national_id_number'], $loanApp['name'], $loanApp['phone'], $loanApp['loan_amount'], $loan_application_id);
        $stmt->execute();
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();

    echo "Request processed successfully.";
}
?>

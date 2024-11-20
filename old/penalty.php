<?php
require '../test 3/db_connection.php';  // Adjust this based on your actual database connection file path
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Function to deduct from savings automatically or create debt if insufficient funds
function deduct_loan($national_id_number) {
    global $conn;

    // Fetch all approved loans for the user
    $sql_loans = "SELECT loan_id, user_id, remaining_balance, due_date FROM payment_records WHERE user_id = (SELECT user_id FROM users WHERE national_id_number = '$national_id_number') AND payment_status = 'unpaid'";
    $result_loans = $conn->query($sql_loans);

    if ($result_loans->num_rows > 0) {
        while ($loan_row = $result_loans->fetch_assoc()) {
            $loan_id = $loan_row['loan_id'];
            $user_id = $loan_row['user_id'];
            $amount_due = $loan_row['remaining_balance'];
            $due_date = $loan_row['due_date'];

            // Check if due date has passed for daily deduction
            if (strtotime($due_date) < time()) {
                // Deduct 200 units daily
                $new_balance = $amount_due - 200;
                $update_loan = "UPDATE payment_records SET remaining_balance = $new_balance WHERE loan_id = $loan_id";
                $conn->query($update_loan);

                // Check if it's been 30 days since the due date to flag the loan
                if (strtotime($due_date) < strtotime('-30 days')) {
                    $flag_loan = "UPDATE payment_records SET payment_status = 'flagged' WHERE loan_id = $loan_id";
                    $conn->query($flag_loan);
                }

                // Calculate the total current savings by summing all entries
                $sql_total_savings = "SELECT SUM(amount) AS total_savings FROM savings WHERE id_number = '$national_id_number'";
                $result_savings = $conn->query($sql_total_savings);

                if ($result_savings->num_rows > 0) {
                    $row_savings = $result_savings->fetch_assoc();
                    $current_savings = $row_savings['total_savings'] ?? 0;

                    if ($current_savings >= $amount_due) {
                        // Deduct from savings
                        $insert_negative_savings = "INSERT INTO savings (id_number, amount, date) VALUES ('$national_id_number', -$amount_due, NOW())";
                        $conn->query($insert_negative_savings);

                        // Update total savings
                        $update_total_savings = "UPDATE savings 
                                                 SET total_current_amount = total_current_amount - $amount_due 
                                                 WHERE id_number = '$national_id_number'
                                                 ORDER BY savings_id DESC LIMIT 1";
                        $conn->query($update_total_savings);

                        // Mark the loan as paid
                        $update_loan_status = "UPDATE payment_records SET remaining_balance = 0, payment_status = 'paid' WHERE loan_id = $loan_id AND user_id = $user_id";
                        $conn->query($update_loan_status);

                        show_message("Loan paid successfully for loan ID $loan_id!");
                    } else {
                        // Not enough savings, create an entry showing negative debt
                        $remaining_balance = $amount_due - $current_savings;

                        // Insert a negative entry for the debt
                        $insert_negative_savings = "INSERT INTO savings (id_number, amount, date) VALUES ('$national_id_number', -$remaining_balance, NOW())";
                        $conn->query($insert_negative_savings);

                        // Update the loan as partially paid
                        $update_loan_status = "UPDATE payment_records SET remaining_balance = $remaining_balance, payment_status = 'partial' WHERE loan_id = $loan_id AND user_id = $user_id";
                        $conn->query($update_loan_status);

                        show_message("Insufficient savings for loan ID $loan_id. Loan will be deducted automatically when new savings are made.");
                    }
                } else {
                    // If no savings records exist, create a negative debt entry
                    $insert_negative_savings = "INSERT INTO savings (id_number, amount, date) VALUES ('$national_id_number', -$amount_due, NOW())";
                    $conn->query($insert_negative_savings);

                    show_message("No savings found for loan ID $loan_id. Loan will be deducted automatically when savings are made.");
                }
            }
        }
    } else {
        show_message("No unpaid loans found for national ID: $national_id_number.");
    }
}

// Function to show messages in a modal
function show_message($message) {
    echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                const modal = document.getElementById('messageModal');
                modal.textContent = '$message';
                modal.style.display = 'block';
                setTimeout(() => {
                    modal.style.display = 'none';
                }, 5000);
            });
          </script>";
}

// Fetch all users with overdue loans
$sql_users = "SELECT DISTINCT u.national_id_number 
              FROM users u 
              JOIN payment_records pr ON u.user_id = pr.user_id 
              WHERE pr.payment_status = 'unpaid' AND pr.due_date < NOW()";
$result_users = $conn->query($sql_users);

if ($result_users->num_rows > 0) {
    while ($user_row = $result_users->fetch_assoc()) {
        $national_id_number = $user_row['national_id_number'];
        // Call deduct_loan for each national ID with overdue loans
        deduct_loan($national_id_number);
    }
} else {
    show_message("No overdue loans found.");
}
?>

<!-- Modal Structure -->
<div id="messageModal" style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; border: 1px solid black; padding: 20px; z-index: 1000; border-radius: 5px;">
    <p id="modalMessage"></p>
</div>

<!-- Styles for the modal -->
<style>
    #messageModal {
        display: none;
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: white;
        border: 1px solid black;
        padding: 20px;
        z-index: 1000;
        border-radius: 5px;
    }
</style>

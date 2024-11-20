<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['national_id'])) {
    echo "<script>alert('You must be logged in to proceed.'); window.location.href = '../login_modal.php';</script>";
    exit();
}

// Database connection setup
require '../test 3/db_connection.php';  // Adjust this based on your actual database connection file path
if (!$conn) {
    echo "<script>alert('Connection failed: " . mysqli_connect_error() . "'); window.location.href = 'pay_loan.php';</script>";
    exit();
}

// Get form data
$loan_id = mysqli_real_escape_string($conn, $_POST['loan_id']);
$pay_amount = mysqli_real_escape_string($conn, $_POST['pay_amount']);
$national_id = $_SESSION['national_id'];

// Fetch loan details from due_loans table
$loan_query = "SELECT * FROM due_loans WHERE loan_id = '$loan_id' AND user_id = '$national_id'";
$loan_result = mysqli_query($conn, $loan_query);
if (mysqli_num_rows($loan_result) == 0) {
    echo "<script>alert('Loan not found or not authorized.'); window.location.href = 'pay_loan.php';</script>";
    exit();
}
$loan = mysqli_fetch_assoc($loan_result);

// Yo! Payments API credentials
$yo_username = "90005702859";
$yo_password = "MsUl-Ei4O-BmJo-apHP-npHY-wsYT-c8nc-skny";
$yo_endpoint = "https://sandbox.yo.co.ug/services/yopaymentsdev/task.php";

// Ensure the mobile money number is correctly formatted with the country code
$mobile_money_number = '256' . $_SESSION['mobile_money_number'];
$internal_reference = time() . '_' . $national_id . '_' . $loan_id;

// Prepare XML request
$request_xml = '<?xml version="1.0" encoding="UTF-8"?> 
<AutoCreate> 
  <Request> 
    <APIUsername>' . htmlspecialchars($yo_username, ENT_QUOTES, 'UTF-8') . '</APIUsername> 
    <APIPassword>' . htmlspecialchars($yo_password, ENT_QUOTES, 'UTF-8') . '</APIPassword> 
    <Method>acwithdrawfunds</Method> 
    <NonBlocking>FALSE</NonBlocking> 
    <Amount>' . htmlspecialchars($pay_amount, ENT_QUOTES, 'UTF-8') . '</Amount> 
    <Account>' . htmlspecialchars($mobile_money_number, ENT_QUOTES, 'UTF-8') . '</Account> 
    <Narrative>Loan payment for Loan ID ' . htmlspecialchars($loan_id, ENT_QUOTES, 'UTF-8') . '</Narrative>  
    <WithdrawFundsTarget>MERCHANT_ACCOUNT</WithdrawFundsTarget>
  </Request> 
</AutoCreate>';

// Initialize cURL session
$ch = curl_init($yo_endpoint);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $request_xml);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml'));

// Execute cURL session and get response
$response_xml = curl_exec($ch);
if (curl_errno($ch)) {
    echo "<script>alert('cURL error: " . htmlspecialchars(curl_error($ch), ENT_QUOTES, 'UTF-8') . "'); window.location.href = 'pay_loan.php';</script>";
    curl_close($ch);
    exit();
}
curl_close($ch);

// Parse the response
$response = simplexml_load_string($response_xml);
if ($response === false) {
    echo "<script>alert('Error parsing API response.'); window.location.href = 'pay_loan.php';</script>";
    exit();
}

// Check for success
if ($response->Response->StatusCode == 0) {
    // Payment successful, calculate new balance
    $new_balance = $loan['loan_amount'] - $pay_amount;

    // Insert payment record into payment_records table
    $insert_payment = "INSERT INTO payment_records (loan_id, user_id, amount_paid, remaining_balance, payment_date)
                       VALUES ('$loan_id', '$national_id', '$pay_amount', '$new_balance', NOW())";
    if (!mysqli_query($conn, $insert_payment)) {
        echo "<script>alert('Error inserting payment record: " . htmlspecialchars(mysqli_error($conn), ENT_QUOTES, 'UTF-8') . "'); window.location.href = 'pay_loan.php';</script>";
        exit();
    }

    if ($new_balance >= 0) {
        // If payment is equal to or less than the loan amount, update due_loans table
        $update_query = "UPDATE due_loans SET loan_amount = '$new_balance' WHERE loan_id = '$loan_id'";
        if (mysqli_query($conn, $update_query)) {
            echo "<script>alert('Loan payment successful. New balance: " . htmlspecialchars($new_balance, ENT_QUOTES, 'UTF-8') . "'); window.location.href = 'pay_loan.php';</script>";
        } else {
            echo "<script>alert('Error updating loan balance: " . htmlspecialchars(mysqli_error($conn), ENT_QUOTES, 'UTF-8') . "'); window.location.href = 'pay_loan.php';</script>";
        }
    } else {
        // If payment is more than the loan amount, transfer excess to savings and update due_loans to zero
        $excess_amount = abs($new_balance);
        $update_due_loans = "UPDATE due_loans SET loan_amount = '0' WHERE loan_id = '$loan_id'";
        if (!mysqli_query($conn, $update_due_loans)) {
            echo "<script>alert('Error updating due_loans table: " . htmlspecialchars(mysqli_error($conn), ENT_QUOTES, 'UTF-8') . "'); window.location.href = 'pay_loan.php';</script>";
            exit();
        }

        // Fetch user details for savings table
        $user_query = "SELECT name, phone, email FROM users WHERE national_id_number = '$national_id'";
        $user_result = mysqli_query($conn, $user_query);
        if (mysqli_num_rows($user_result) == 0) {
            echo "<script>alert('Error fetching user details.'); window.location.href = 'pay_loan.php';</script>";
            exit();
        }
        $user = mysqli_fetch_assoc($user_result);

        // Insert excess amount into savings table
        $insert_savings = "INSERT INTO savings (name, id_number, phone, email, amount, date) 
                           VALUES ('" . htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') . "', '$national_id', '" . htmlspecialchars($user['phone'], ENT_QUOTES, 'UTF-8') . "', '" . htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') . "', '$excess_amount', NOW())";
        if (mysqli_query($conn, $insert_savings)) {
            echo "<script>alert('Loan fully paid. Excess amount of " . htmlspecialchars($excess_amount, ENT_QUOTES, 'UTF-8') . " transferred to savings.'); window.location.href = 'pay_loan.php';</script>";
        } else {
            echo "<script>alert('Error transferring excess to savings: " . htmlspecialchars(mysqli_error($conn), ENT_QUOTES, 'UTF-8') . "'); window.location.href = 'pay_loan.php';</script>";
        }
    }
} else {
    // Payment failed, show error
    echo "<script>alert('Error processing payment: " . htmlspecialchars($response->Response->StatusMessage, ENT_QUOTES, 'UTF-8') . "'); window.location.href = 'pay_loan.php';</script>";
}

mysqli_close($conn);
?>

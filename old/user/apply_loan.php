<?php
// Include the file for database connection
require '../test 3/db_connection.php';

// Start session to get the national ID
session_start();

// Check if the user is logged in
if (!isset($_SESSION['national_id'])) {
    die("User is not logged in.");
}

// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve loan amount from the form
    $loanAmount = $_POST['loanAmount'];

    // Retrieve the national ID from the session
    $nationalIdNumber = $_SESSION['national_id'];

    // Fetch user data from the database
    $stmt = $conn->prepare("SELECT name, phone, email, national_id_file FROM users WHERE national_id_number = ?");
    $stmt->bind_param("s", $nationalIdNumber);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if (!$user) {
        die("User not found.");
    }

    // Close the prepared statement
    $stmt->close();

    // Prepare the SQL statement to insert loan application
    $stmt = $conn->prepare("INSERT INTO loans_application (name, phone, email, national_id_number, national_id_file, loan_amount, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");

    // Bind parameters
    $stmt->bind_param("sssssd", $user['name'], $user['phone'], $user['email'], $nationalIdNumber, $user['national_id_file'], $loanAmount);

    // Execute the database insertion query
    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close the prepared statement
    $stmt->close();
}

// Close the database connection
$conn->close();
?>

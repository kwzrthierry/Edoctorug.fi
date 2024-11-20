<?php
session_start();
require 'test 3/db_connection.php';

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process form data if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Set parameters and execute statement
    $email = $_POST["loginEmail"];
    $password = $_POST["loginPassword"];

    // Prepare SQL statement to retrieve user with the given email
    $stmt = $conn->prepare("SELECT user_id, name, user_type, national_id_number, phone, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if a user with the given email exists
    if ($result->num_rows > 0) {
        // Fetch user data
        $user = $result->fetch_assoc();
        
        // Check user type
        if ($user['user_type'] == 'admin' || $user['user_type'] == 'support') {
            // For admin or support user type, use simple verification
            if ($password === $user['password']) {
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['national_id'] = $user['national_id_number'];
                $_SESSION['user_type'] = $user['user_type'];
                echo 'success';
                exit();
            } else {
                echo 'Incorrect password. Please try again.';
            }
        } else {
            // For regular users, use password_verify
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['national_id'] = $user['national_id_number'];
                $_SESSION['user_type'] = $user['user_type'];
                $_SESSION['mobile_money_number'] = $user['phone'];
                $_SESSION['user_id'] = $user['user_id'];
                echo 'success';
                exit();
            } else {
                echo 'Incorrect password. Please try again.';
            }
        } 
    } else {
        echo 'User with the provided email does not exist.';
    }

    // Close statement
    $stmt->close();
}

// Close connection
$conn->close();
?>

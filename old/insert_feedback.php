<?php
// Database connection details
require 'test 3/db_connection.php';

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["feedbackName"];
    $message = $_POST["feedbackMessage"];
    $rating = $_POST["rating"];

    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO feedback (name, message, rating) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $name, $message, $rating);

    if ($stmt->execute()) {
        // Redirect to index.php with success parameter
        header("Location: index.php?status=success");
        exit();
    } else {
        // Redirect to index.php with error parameter
        header("Location: index.php?status=error");
        exit();
    }

    // Close statement
    $stmt->close();
}

$conn->close();
?>

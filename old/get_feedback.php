<?php
require 'test 3/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $loanId = $_POST['loanId'];

    // Prepare and execute the query to fetch reason for the given loan ID
    $stmt = $conn->prepare("SELECT reason FROM loans_application WHERE id = ?");
    $stmt->bind_param("i", $loanId);
    $stmt->execute();
    $stmt->bind_result($reason);
    $stmt->fetch();

    // Check if reason exists
    if ($reason) {
        echo htmlspecialchars($reason);
    } else {
        echo 'No feedback found for this loan application.';
    }

    $stmt->close();
} else {
    echo 'Invalid request method.';
}

$conn->close();
?>

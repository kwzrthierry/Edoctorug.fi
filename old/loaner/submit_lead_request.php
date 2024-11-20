<?php
require '../test 3/db_connection.php';
session_start();

// Fetch user ID and national ID from the session
$useridnumber = $_SESSION['national_id'];
$userID =  $_SESSION['user_id'];

// Check if the request is POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Fetch total savings for the user
    $savings_query = "SELECT SUM(amount) AS total_savings FROM savings WHERE id_number = ?";
    $stmt = $conn->prepare($savings_query);
    $stmt->bind_param("s", $useridnumber);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $total_savings = $result['total_savings'] ?: 0; // Default to 0 if null

    // Insert lead request into the database
    $stmt = $conn->prepare("INSERT INTO lead_requests (user_id, total_savings, request_status, created_at) VALUES (?, ?, 'pending', NOW())");
    $stmt->bind_param("sd", $userID, $total_savings);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to insert lead request.']);
    }

    $stmt->close();
    $conn->close();
    exit();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
?>

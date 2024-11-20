<?php
// Database connection settings
require '../test 3/db_connection.php';

// Get the lead ID from the request
$lead_id = $_GET['lead_id'];

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to get the national ID from the leads table
$leadQuery = $conn->prepare("SELECT lead_national_id FROM leads WHERE id = ?");
$leadQuery->bind_param("i", $lead_id);
$leadQuery->execute();
$leadResult = $leadQuery->get_result();

if ($leadResult->num_rows > 0) {
    $leadRow = $leadResult->fetch_assoc();
    $leadNationalId = $leadRow['lead_national_id'];

    // Query to get the user email from the users table
    $userQuery = $conn->prepare("SELECT email FROM users WHERE national_id_number = ?");
    $userQuery->bind_param("s", $leadNationalId);
    $userQuery->execute();
    $userResult = $userQuery->get_result();

    if ($userResult->num_rows > 0) {
        $userRow = $userResult->fetch_assoc();
        $email = $userRow['email'];
        echo json_encode(["email" => $email]);
    } else {
        echo json_encode(["error" => "No user found with that National ID."]);
    }
} else {
    echo json_encode(["error" => "No lead found with that Lead ID."]);
}

// Close connections
$leadQuery->close();
$userQuery->close();
$conn->close();
?>

<?php
include 'test 3/db_connection.php';

$loan_id = $_GET['loan_id'];
$stmt = $conn->prepare("SELECT * FROM reviews WHERE loan_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $loan_id);
$stmt->execute();
$result = $stmt->get_result();

$reviews = [];
while ($row = $result->fetch_assoc()) {
    $reviews[] = $row;
}

echo json_encode($reviews);

$stmt->close();
$conn->close();
?>

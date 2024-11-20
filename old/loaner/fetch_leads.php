<?php
// fetch_leads.php
require '../test 3/db_connection.php'; // Update with your actual database connection script

$query = "SELECT * FROM leads ORDER BY id DESC";
$result = $conn->query($query);

$leads = [];
while ($row = $result->fetch_assoc()) {
    $leads[] = $row;
}

echo json_encode($leads);
?>

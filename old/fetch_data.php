<?php
require 'db_connection.php';

function getData($table, $columns, $condition = '') {
    global $conn;
    $sql = "SELECT $columns FROM $table $condition";
    $result = $conn->query($sql);
    $data = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
    return $data;
}

// Fetch users
if ($_POST['action'] === 'fetchUsers') {
    $users = getData('users', '*');
    echo json_encode($users);
}

// Fetch user records (savings and loans)
if ($_POST['action'] === 'fetchUserRecords') {
    $userId = $_POST['userId'];
    $savings = getData('savings', '*', "WHERE user_id = $userId");
    $loans = getData('loans_application', '*', "WHERE user_id = $userId");
    echo json_encode(['savings' => $savings, 'loans' => $loans]);
}

// Fetch loans
if ($_POST['action'] === 'fetchLoans') {
    $loans = getData('loans_application', '*');
    echo json_encode($loans);
}

// Fetch savings
if ($_POST['action'] === 'fetchSavings') {
    $savings = getData('savings', '*');
    echo json_encode($savings);
}

// Fetch dashboard statistics
if ($_POST['action'] === 'fetchStatistics') {
    $approvedLoans = getData('loans_application', 'COUNT(*) AS count', "WHERE status = 'approved'");
    $pendingLoans = getData('loans_application', 'COUNT(*) AS count', "WHERE status = 'pending'");
    $deniedLoans = getData('loans_application', 'COUNT(*) AS count', "WHERE status = 'denied'");

    $statistics = [
        'approvedLoans' => $approvedLoans[0]['count'],
        'pendingLoans' => $pendingLoans[0]['count'],
        'deniedLoans' => $deniedLoans[0]['count'],
    ];

    echo json_encode($statistics);
}

$conn->close();
?>

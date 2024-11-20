<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "if0_36966230_database";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

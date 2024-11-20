<?php
session_start();

if (isset($_SESSION['user_type'])) {
    echo $_SESSION['user_type'];
} else {
    echo 'Unknown';
}
?>

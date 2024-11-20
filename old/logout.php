<?php
// Start the session
session_start();

// Terminate the session
session_destroy();

// Redirect to the homepage
header("Location: login_modal.php");
exit();
?>
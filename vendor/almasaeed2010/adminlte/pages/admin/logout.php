<?php
session_start(); // Start the session
session_destroy(); // Destroy the session

// Redirect to login page
header("Location: adminlogin.php");
exit;
?>

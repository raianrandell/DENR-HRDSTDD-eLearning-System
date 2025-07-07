<?php
session_start();

// Unset all session variables specific to the course manager
unset($_SESSION['course_manager_id']);
unset($_SESSION['name']);
unset($_SESSION['photo']);

// Optional: Destroy the entire session if no other session data is needed
// session_destroy();

// Redirect to the login page
header('location: course_manager_login.php');
exit();
?>
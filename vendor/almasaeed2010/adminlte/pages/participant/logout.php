<?php
session_start();
session_unset();
session_destroy();
header("Location: participantlogin.php"); // Redirect to your login page
exit();

?>
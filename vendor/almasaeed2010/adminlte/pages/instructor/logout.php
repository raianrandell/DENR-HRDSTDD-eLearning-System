<?php
session_start();
session_destroy();
header("Location: instructorlogin.php");
exit();
?>

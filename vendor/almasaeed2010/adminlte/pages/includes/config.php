<?php
$host = "localhost"; // Change this if using a remote database
$username = "root"; // Database username
$password = ""; // Database password
$dbname = "enra_db"; // Your database name

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

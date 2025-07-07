<?php
require '../includes/config.php'; // Database connection

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['id'])) {
    $id = $_POST['id'];

    // Prepare and bind
    $stmt = $conn->prepare("DELETE FROM user_course_manager WHERE id = ?");
    $stmt->bind_param("i", $id);

    // Execute the query
    if ($stmt->execute()) {
        echo "success"; // Send a success message
    } else {
        echo "error"; // Send an error message
    }

    $stmt->close();
}

$conn->close();
?>
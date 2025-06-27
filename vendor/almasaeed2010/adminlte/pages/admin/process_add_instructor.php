<?php
session_start();
require '../includes/config.php';
 // Ensure this file has your DB connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = trim($_POST['first_name']);
    $middle_name = trim($_POST['middle_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $contact_number = trim($_POST['contact_number']);
    $office = trim($_POST['office']);
    $position = trim($_POST['position']);
    $region = trim($_POST['region']);

    // File upload handling
    $photo = '';
    if (!empty($_FILES["photo"]["name"])) {
        $target_dir = "../uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $photo = $target_dir . basename($_FILES["photo"]["name"]);
        move_uploaded_file($_FILES["photo"]["tmp_name"], $photo);
    }

    $sql = "INSERT INTO user_instructors (first_name, middle_name, last_name, email, `password`, contact_number, office, position, region, photo) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssssssssss", $first_name, $middle_name, $last_name, $email, $password, $contact_number, $office, $position, $region, $photo);


        if ($stmt->execute()) {
            $_SESSION['success'] = "Instructor added successfully!";
        } else {
            $_SESSION['error'] = "Failed to add instructor. Try again.";
        }
        $stmt->close();
    } else {
        $_SESSION['error'] = "Database error!";
    }

    $conn->close();
    header("Location: instructors.php"); // Redirect back to the list
    exit();
}
?>

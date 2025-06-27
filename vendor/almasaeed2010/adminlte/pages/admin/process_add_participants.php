<?php
session_start();
require '../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize input data
    $first_name = trim($_POST['first_name']);
    $middle_name = !empty($_POST['middle_name']) ? trim($_POST['middle_name']) : NULL;
    $last_name = trim($_POST['last_name']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $contact_number = trim($_POST['contact_number']);
    $office = trim($_POST['office']);
    $position = trim($_POST['position']);
    $gender = trim($_POST['gender']);
    $age = (int) $_POST['age'];
    $salary_grade = (int) $_POST['salary_grade'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $in_training = isset($_POST['in_training']) && $_POST['in_training'] == '1' ? 1 : 0; // Ensure 0 or 1
    $status = "Active"; // Default status

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format.";
        header("Location: createParticipants.php");
        exit();
    }

    // File Upload Handling - store only the filename
    $photo = NULL;
    if (!empty($_FILES['photo']['name'])) {
        $target_dir =  "../uploads/";

        // Ensure the uploads folder exists
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        // Validate file type
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        $file_ext = strtolower(pathinfo($_FILES["photo"]["name"], PATHINFO_EXTENSION));

        if (!in_array($file_ext, $allowed_extensions)) {
            $_SESSION['error'] = "Invalid file type. Only JPG, JPEG, PNG, and GIF are allowed.";
            header("Location: createParticipants.php");
            exit();
        }

        // Generate unique filename - Store only the name!
        $photo = "trainee_" . uniqid() . "." . $file_ext;
        $target_file = $target_dir . $photo; // Full path for move_uploaded_file

        // Move uploaded file
        if (!move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) {
            $_SESSION['error'] = "Error uploading file.";
            header("Location: createParticipants.php");
            exit();
        }
    }

    // Database Insertion
    $sql = "INSERT INTO user_participants (first_name, middle_name, last_name, email, contact_number, office, position, gender, age, salary_grade, `password`, in_training, `status`, photo) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param(
        "ssssssssiissss",
        $first_name,
        $middle_name,
        $last_name,
        $email,
        $contact_number,
        $office,
        $position,
        $gender,
        $age,
        $salary_grade,
        $password,
        $in_training,
        $status,
        $photo
    );


        if ($stmt->execute()) {
            $_SESSION['success'] = "Participant added successfully!";
            header("Location: participants.php");
            exit();
        } else {
            $_SESSION['error'] = "Error adding participant: " . $stmt->error;
            error_log("Database error: " . $stmt->error);  // Log the error
            header("Location: createParticipants.php");
            exit();
        }
        $stmt->close();
    } else {
        $_SESSION['error'] = "Error preparing statement: " . $conn->error;
        error_log("Prepared statement error: " . $conn->error);
        header("Location: createParticipants.php");
        exit();
    }

    $conn->close();
}
?>

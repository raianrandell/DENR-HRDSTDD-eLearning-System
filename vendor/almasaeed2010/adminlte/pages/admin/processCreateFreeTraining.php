<?php
session_start();
require '../includes/config.php'; // Database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Sanitize input data (basic protection)
    $trainingTitle = htmlspecialchars(trim($_POST['trainingTitle']));
    $trainingDescription = htmlspecialchars(trim($_POST['trainingDescription']));
    $trainingLink = filter_var($_POST['trainingLink'], FILTER_SANITIZE_URL); // Sanitize URL
    $trainingHrs = intval($_POST['trainingHrs']); // Ensure integer
    $startDate = $_POST['startDate'];
    $endDate = $_POST['endDate'];


    // Handle file upload
    $targetDir = "../uploads/"; // Make sure this directory exists and is writable
    $fileName = basename($_FILES["trainingPhoto"]["name"]);
    $targetFilePath = $targetDir . $fileName;
    $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION)); // Convert to lowercase for comparison

    // Allow certain file formats
    $allowTypes = array('jpg','png','jpeg','gif','pdf');
    if(in_array($fileType, $allowTypes)){
        // Upload file to server
        if(move_uploaded_file($_FILES["trainingPhoto"]["tmp_name"], $targetFilePath)){
            // Insert image file name into database
            $sql = "INSERT INTO free_trainings (training_title, `description`, image_path, training_link, training_hrs, start_date, end_date, created_at)
                    VALUES ('$trainingTitle', '$trainingDescription', '$fileName', '$trainingLink', '$trainingHrs', '$startDate', '$endDate', NOW())";

            if ($conn->query($sql) === TRUE) {
                $_SESSION['success'] = "New training created successfully!";
                header("Location: free_trainings.php"); // Redirect to the listing page
                exit(); // Stop execution after redirect
            } else {
                $_SESSION['error'] = "Error: " . $sql . "<br>" . $conn->error;
                header("Location: createFreeTraining.php"); // Redirect back to the form
                exit(); // Stop execution after redirect
            }
        } else {
            $_SESSION['error'] = "Sorry, there was an error uploading your file.";
            header("Location: createFreeTraining.php"); // Redirect back to the form
            exit(); // Stop execution after redirect
        }
    } else {
        $_SESSION['error'] = 'Sorry, only JPG, JPEG, PNG, GIF, & PDF files are allowed to upload.';
        header("Location: createFreeTraining.php"); // Redirect back to the form
        exit(); // Stop execution after redirect
    }

    $conn->close();
} else {
    $_SESSION['error'] = "Invalid request!";
    header("Location: createFreeTraining.php"); // Redirect back to the form
    exit(); // Stop execution after redirect
}
?>
<?php

require '../includes/config.php'; // Database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $training_id = $_POST['training_id'];
    $training_title = $_POST['training_title']; // This field is readonly, so it should not be included
    $description = $_POST['description'];
    $location = $_POST['location'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $training_hrs = $_POST['training_hrs'];

    // Validate data (important!)  Add more validation as needed
    if (empty($description) || empty($location) || empty($start_date) || empty($end_date) || !is_numeric($training_hrs)) {
      $_SESSION['error'] = "All fields are required.";
      echo json_encode(['status' => 'error', 'message' => $_SESSION['error']]);
      exit();
    }

    try {
        $sql = "UPDATE training SET training_title=?, description=?, location=?, start_date=?, end_date=?, training_hrs=? WHERE training_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssii", $training_title, $description,  $location, $start_date, $end_date, $training_hrs, $training_id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $_SESSION['success'] = "Training updated successfully!";
            echo json_encode(['status' => 'success', 'message' => $_SESSION['success']]);
        } else {
            $_SESSION['error'] = "No changes were made or training not found.";
            echo json_encode(['status' => 'error', 'message' => $_SESSION['error']]);
        }

        $stmt->close();

    } catch (Exception $e) {
        $_SESSION['error'] = "Error updating training: " . $e->getMessage();
        echo json_encode(['status' => 'error', 'message' => $_SESSION['error']]);
    }

    $conn->close();
    exit(); // Important: Stop further execution
} else {
    // Handle non-POST requests (optional, but good practice)
    $_SESSION['error'] = "Invalid request.";
    echo json_encode(['status' => 'error', 'message' => $_SESSION['error']]);
    exit();
}
?>
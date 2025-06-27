<?php
session_start();
include '../includes/config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve training_id from $_POST and validate it
    if (!isset($_POST['training_id']) || !is_numeric($_POST['training_id'])) {
        $_SESSION['error'] = "Invalid training ID.";
        header("Location: training.php"); // Or maybe back to assignInstructors.php?
        exit();
    }
    $training_id = $_POST['training_id'];

    if (!isset($_POST['instructor_ids']) || !is_array($_POST['instructor_ids'])) {
        $_SESSION['error'] = "No instructors selected.";
        header("Location: assignInstructors.php?training_id=" . $training_id);
        exit();
    }
    $instructor_ids = $_POST['instructor_ids'];

    // Prepare INSERT statement for training_instructors
    $insert_sql = "INSERT INTO training_instructors (training_id, instructor_id) VALUES (?, ?)";
    $insert_stmt = $conn->prepare($insert_sql);
    if (!$insert_stmt) {
        die("Error preparing statement: " . $conn->error);
    }

    $conn->begin_transaction(); // Start transaction
    try {
        foreach ($instructor_ids as $instructor_id) {
            if (!is_numeric($instructor_id)) {
                throw new Exception("Invalid instructor ID: " . $instructor_id);
            }
            $insert_stmt->bind_param("ii", $training_id, $instructor_id);
            if (!$insert_stmt->execute()) {
                throw new Exception("Error inserting instructor ID " . $instructor_id . ": " . $insert_stmt->error);
            }
        }
        $conn->commit(); // Commit transaction
        $_SESSION['success'] = "Instructor successfully assigned to the training.";
    } catch (Exception $e) {
        $conn->rollback(); // Rollback transaction
        $_SESSION['error'] = "Error assigning instructors: " . $e->getMessage();
    } finally {
        $insert_stmt->close();
    }
} else {
    $_SESSION['error'] = "Invalid request method.";
}

header("Location: training.php"); // Redirect back to training page
exit();
?>
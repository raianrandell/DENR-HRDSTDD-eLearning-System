<?php
session_start(); // Start session first
include '../includes/config.php'; // Database connection

// Default redirect location
$redirect_location = 'courseManager.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve training_id from $_POST and validate it
    if (!isset($_POST['training_id']) || !is_numeric($_POST['training_id'])) {
        $_SESSION['error'] = "Invalid or missing training ID.";
        header("Location: " . $redirect_location);
        exit();
    }
    $training_id = (int)$_POST['training_id'];
    $redirect_location = "assignInstructors.php?training_id=" . $training_id; // Redirect back to assign page on error

    // Check if instructors were actually selected
    if (!isset($_POST['instructor_ids']) || !is_array($_POST['instructor_ids']) || empty($_POST['instructor_ids'])) {
        $_SESSION['warning'] = "No instructors were selected to assign."; // Use warning severity
        header("Location: " . $redirect_location);
        exit();
    }
    $instructor_ids = $_POST['instructor_ids'];

    // Prepare INSERT statement for training_instructors
    // Use INSERT IGNORE to prevent errors if an instructor somehow already exists for this training
    $insert_sql = "INSERT IGNORE INTO training_instructors (training_id, instructor_id) VALUES (?, ?)";
    $insert_stmt = $conn->prepare($insert_sql);
    if (!$insert_stmt) {
         $_SESSION['error'] = "Database error preparing statement: " . $conn->error;
         header("Location: " . $redirect_location);
         exit();
    }

    $conn->begin_transaction(); // Start transaction
    $assigned_count = 0;
    $error_occurred = false;

    try {
        foreach ($instructor_ids as $instructor_id_raw) {
            // Sanitize/Validate each ID
            $instructor_id = filter_var($instructor_id_raw, FILTER_VALIDATE_INT);
            if ($instructor_id === false || $instructor_id <= 0) {
                // Log this error if possible, but continue for others
                error_log("Invalid instructor ID skipped during assignment: " . $instructor_id_raw);
                continue; // Skip invalid IDs
            }

            $insert_stmt->bind_param("ii", $training_id, $instructor_id);
            if (!$insert_stmt->execute()) {
                 // Throw exception to trigger rollback
                 throw new Exception("Error assigning instructor ID " . $instructor_id . ": " . $insert_stmt->error);
            }
            // Check if a row was actually inserted (affected_rows > 0)
            if ($insert_stmt->affected_rows > 0) {
                 $assigned_count++;
            }
        }

        // If loop completes without exceptions, commit
        $conn->commit();

        if ($assigned_count > 0) {
             $_SESSION['success'] = $assigned_count . " instructor(s) successfully assigned to the training.";
             // Redirect to the main course manager page on success
             header("Location: courseManager.php");
             exit();
        } else {
            // This might happen if all selected instructors were already assigned (due to INSERT IGNORE)
             $_SESSION['warning'] = "No new instructors were assigned (they might have been assigned already).";
             header("Location: " . $redirect_location);
             exit();
        }

    } catch (Exception $e) {
        $conn->rollback(); // Rollback transaction on error
        $_SESSION['error'] = "An error occurred during assignment: " . $e->getMessage();
        $error_occurred = true; // Flag error
    } finally {
        $insert_stmt->close();
        $conn->close();
    }

    // Redirect back to the assignment page if an error occurred
     if ($error_occurred) {
        header("Location: " . $redirect_location);
        exit();
    }

} else {
    $_SESSION['error'] = "Invalid request method.";
    header("Location: " . $redirect_location); // Redirect to course manager if accessed directly
    exit();
}
?>
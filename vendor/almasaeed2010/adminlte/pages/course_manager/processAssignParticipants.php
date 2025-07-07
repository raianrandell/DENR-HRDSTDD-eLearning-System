<?php
session_start(); // Start session first
require '../includes/config.php'; // Database connection

// Default redirect location
$redirect_location = 'courseManager.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

     // Retrieve training_id from $_POST and validate it
    if (!isset($_POST['training_id']) || !is_numeric($_POST['training_id'])) {
        $_SESSION['error'] = "Invalid or missing training ID.";
        header("Location: " . $redirect_location);
        exit();
    }
    $training_id = (int)$_POST['training_id'];
    $redirect_location = "assignParticipants.php?training_id=" . $training_id; // Redirect back to assign page on error/warning


    // Check if participants were actually selected
    if (!isset($_POST['participant_ids']) || !is_array($_POST['participant_ids']) || empty($_POST['participant_ids'])) {
        $_SESSION['warning'] = "No participants were selected to assign.";
        header("Location: " . $redirect_location);
        exit();
    }

    $participant_ids_raw = $_POST['participant_ids'];
    $participant_ids = []; // Array to hold validated integer IDs

    // Validate all participant IDs first
    foreach ($participant_ids_raw as $pid_raw) {
        $pid = filter_var($pid_raw, FILTER_VALIDATE_INT);
        if ($pid !== false && $pid > 0) {
            $participant_ids[] = $pid;
        } else {
             error_log("Invalid participant ID skipped during assignment: " . $pid_raw . " for training " . $training_id);
             // Optionally add a warning message
        }
    }

    // If no valid IDs remain after filtering
    if (empty($participant_ids)) {
         $_SESSION['error'] = "No valid participant IDs were provided.";
         header("Location: " . $redirect_location);
         exit();
    }

    // Prepare queries
    // Use INSERT IGNORE to avoid errors if re-assigning
    $assign_sql = "INSERT IGNORE INTO training_participants (training_id, participant_id) VALUES (?, ?)";
    $assign_stmt = $conn->prepare($assign_sql);

    // Prepare update query for in_training status
    // Generate placeholders like ?, ?, ?
    $ids_placeholder = implode(',', array_fill(0, count($participant_ids), '?'));
    // Use UPDATE IGNORE if needed, though less likely to cause unique key issues here
    $update_sql = "UPDATE user_participants SET in_training = 1 WHERE participant_id IN ($ids_placeholder)";
    $update_stmt = $conn->prepare($update_sql);

    if (!$assign_stmt || !$update_stmt) {
        $_SESSION['error'] = "Database error preparing statements: " . $conn->error;
         header("Location: " . $redirect_location);
         exit();
    }

    // Bind parameters for the update statement dynamically
    // Create a string of 'i' types, e.g., 'iii' if count is 3
    $types = str_repeat('i', count($participant_ids));
    // Use the splat operator (...) to pass array elements as individual arguments
    $update_stmt->bind_param($types, ...$participant_ids);


    // Start transaction
    $conn->begin_transaction();
    $assigned_count = 0;
    $error_occurred = false;

    try {
        // Loop through validated IDs for assignment
        foreach ($participant_ids as $participant_id) {
            $assign_stmt->bind_param("ii", $training_id, $participant_id);
            if (!$assign_stmt->execute()) {
                 throw new Exception("Error assigning participant ID " . $participant_id . ": " . $assign_stmt->error);
            }
             if ($assign_stmt->affected_rows > 0) {
                 $assigned_count++;
             }
        }

        // Execute the bulk update for in_training status only if assignments were attempted
        if (count($participant_ids) > 0) {
             if (!$update_stmt->execute()) {
                throw new Exception("Error updating participant status: " . $update_stmt->error);
            }
        }

        // Commit if all operations were successful
        $conn->commit();

         if ($assigned_count > 0) {
             $_SESSION['success'] = $assigned_count . " participant(s) successfully assigned and status updated.";
             // Redirect to the main course manager page on success
             header("Location: courseManager.php");
             exit();
         } else {
            // This might happen if all selected were already assigned
             $_SESSION['warning'] = "No new participants were assigned (they might have been assigned already). Status update may still have occurred if they were previously not marked 'in_training'.";
             header("Location: " . $redirect_location);
             exit();
        }

    } catch (Exception $e) {
        $conn->rollback(); // Rollback on any error
        $_SESSION['error'] = "An error occurred during assignment: " . $e->getMessage();
        $error_occurred = true; // Flag error
    } finally {
        // Close statements and connection
        if (isset($assign_stmt)) $assign_stmt->close();
        if (isset($update_stmt)) $update_stmt->close();
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
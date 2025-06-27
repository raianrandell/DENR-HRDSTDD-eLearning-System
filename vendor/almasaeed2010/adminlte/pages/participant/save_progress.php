<?php
// --- save_progress.php ---

// Start session VERY FIRST
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json'); // Set response header to JSON
include '../includes/config.php'; // Include your database connection

$response = ['status' => 'error', 'message' => 'An unknown error occurred.'];

// 1. Check User Login
if (!isset($_SESSION["participant_id"])) {
    $response['message'] = 'Authentication required.';
    echo json_encode($response);
    exit;
}
$participant_id = $_SESSION["participant_id"];

// 2. Check Request Method and Content Type
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
     $response['message'] = 'Invalid request method.';
     echo json_encode($response);
     exit;
}

// 3. Get and Validate Input Data
$input = json_decode(file_get_contents('php://input'), true); // Expecting JSON payload

$training_id = isset($input['training_id']) ? filter_var($input['training_id'], FILTER_VALIDATE_INT) : null;
$module_id = isset($input['module_id']) ? filter_var($input['module_id'], FILTER_VALIDATE_INT) : null;
// $is_complete = isset($input['is_complete']) ? filter_var($input['is_complete'], FILTER_VALIDATE_BOOLEAN) : null; // Assuming 'true' means complete

// For this use case, we only care about marking as COMPLETE.
// We'll always insert with is_complete = 1 when this script is called.
$is_complete = true;

if (!$training_id || !$module_id || $is_complete === null) {
    $response['message'] = 'Missing or invalid data (training_id, module_id, is_complete).';
    echo json_encode($response);
    exit;
}


// 4. Security Check: Verify Participant Enrollment and Module Validity (Optional but Recommended)
// Check if participant is in this training
$sql_check_enrollment = "SELECT 1 FROM training_participants WHERE participant_id = ? AND training_id = ?";
$stmt_check_enrollment = $conn->prepare($sql_check_enrollment);
$stmt_check_enrollment->bind_param("ii", $participant_id, $training_id);
$stmt_check_enrollment->execute();
$stmt_check_enrollment->store_result();
if ($stmt_check_enrollment->num_rows == 0) {
     $response['message'] = 'Participant not enrolled in this training.';
     $stmt_check_enrollment->close();
     echo json_encode($response);
     exit;
}
$stmt_check_enrollment->close();

// Check if module belongs to this training
$sql_check_module = "SELECT 1 FROM modules WHERE module_id = ? AND training_id = ?";
$stmt_check_module = $conn->prepare($sql_check_module);
$stmt_check_module->bind_param("ii", $module_id, $training_id);
$stmt_check_module->execute();
$stmt_check_module->store_result();
if ($stmt_check_module->num_rows == 0) {
     $response['message'] = 'Module does not belong to this training.';
     $stmt_check_module->close();
     echo json_encode($response);
     exit;
}
$stmt_check_module->close();


// 5. Prepare and Execute Database Operation (INSERT)
// We use INSERT IGNORE because of the UNIQUE constraint. If the record already exists, it does nothing.
// If you wanted to track unchecking, you'd need DELETE logic or UPDATE `is_complete` to 0.

$sql_insert = "INSERT IGNORE INTO participant_module_progress (participant_id, training_id, module_id, is_complete) VALUES (?, ?, ?, 1)";
$stmt_insert = $conn->prepare($sql_insert);

if ($stmt_insert === false) {
     error_log("Prepare failed: (" . $conn->errno . ") " . $conn->error); // Log error
     $response['message'] = 'Database error preparing statement.';
     echo json_encode($response);
     exit;
}

$stmt_insert->bind_param("iii", $participant_id, $training_id, $module_id);

if ($stmt_insert->execute()) {
    if ($stmt_insert->affected_rows > 0 || $stmt_insert->affected_rows == 0) { // Modified to include 0 affected rows as success
        $response['status'] = 'success';
        $response['message'] = 'Progress saved successfully.';

        // --- 6. Check if all modules are completed and update training_participants status ---
        // Count total modules for the training
        $sql_total_modules = "SELECT COUNT(*) FROM modules WHERE training_id = ?";
        $stmt_total = $conn->prepare($sql_total_modules);
        if ($stmt_total === false) {
            error_log("Prepare failed (total modules count): (" . $conn->errno . ") " . $conn->error);
            // Non-critical error, but log it. Continue with response.
        } else {
            $stmt_total->bind_param("i", $training_id);
            if ($stmt_total->execute()) {
                $stmt_total->bind_result($total_modules);
                $stmt_total->fetch();
                $stmt_total->close();

                // Count completed modules for the participant in this training
                $sql_completed_modules = "SELECT COUNT(DISTINCT module_id) FROM participant_module_progress WHERE training_id = ? AND participant_id = ? AND is_complete = 1";
                $stmt_completed = $conn->prepare($sql_completed_modules);
                if ($stmt_completed === false) {
                    error_log("Prepare failed (completed modules count): (" . $conn->errno . ") " . $conn->error);
                    // Non-critical error, but log it. Continue with response.
                } else {
                    $stmt_completed->bind_param("ii", $training_id, $participant_id);
                    if ($stmt_completed->execute()) {
                        $stmt_completed->bind_result($completed_modules);
                        $stmt_completed->fetch();
                        $stmt_completed->close();

                        // if ($completed_modules >= $total_modules && $total_modules > 0) { // Ensure total_modules > 0
                        //     // --- Update training_participants status to 'Completed' ---
                        //     $sql_update_status = "UPDATE training_participants SET completion_status = 'Completed' WHERE training_id = ? AND participant_id = ?";
                        //     $stmt_update_status = $conn->prepare($sql_update_status);
                        //     if ($stmt_update_status === false) {
                        //         error_log("Prepare failed (update training status): (" . $conn->errno . ") " . $conn->error);
                        //         // Non-critical error, log it.
                        //     } else {
                        //         $stmt_update_status->bind_param("ii", $training_id, $participant_id);
                        //         if ($stmt_update_status->execute()) {
                        //             // Status updated successfully
                        //             error_log("Training status set to 'Completed' for participant $participant_id in training $training_id");
                        //         } else {
                        //             error_log("Error updating training status to 'Completed': (" . $stmt_update_status->errno . ") " . $stmt_update_status->error);
                        //             // Non-critical error, log it.
                        //         }
                        //         $stmt_update_status->close();
                        //     }
                        // }
                    } else {
                        error_log("Execute failed (completed modules count): (" . $stmt_completed->errno . ") " . $stmt_completed->error);
                        // Non-critical error, log it.
                    }
                }
            } else {
                error_log("Execute failed (total modules count): (" . $stmt_total->errno . ") " . $stmt_total->error);
                // Non-critical error, log it.
            }
        }


    } else {
        error_log("Execute failed (insert progress): (" . $stmt_insert->errno . ") " . $stmt_insert->error); // Log error
        $response['message'] = 'Database error saving progress.'; // Generic error message
    }
} else {
    error_log("Execute failed (insert progress): (" . $stmt_insert->errno . ") " . $stmt_insert->error); // Log error
    $response['message'] = 'Database error saving progress.';
}


$stmt_insert->close();
$conn->close();

echo json_encode($response);
?>
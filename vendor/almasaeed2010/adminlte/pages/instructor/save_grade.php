<?php
include '../includes/config.php';

header('Content-Type: application/json'); // Set response type to JSON

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $participant_id = $_POST['participant_id'];
    $training_id = $_POST['training_id'];
    $module_id = $_POST['module_id'];
    $grade_given = $_POST['grade'];
    $feedback = $_POST['feedback'];

    // Check if a grade already exists for this participant, training, and module
    $checkGradeSql = "SELECT grade_id FROM grades WHERE participant_id = ? AND training_id = ? AND module_id = ?";
    $checkGradeStmt = $conn->prepare($checkGradeSql);
    $checkGradeStmt->bind_param("iii", $participant_id, $training_id, $module_id);
    $checkGradeStmt->execute();
    $checkGradeResult = $checkGradeStmt->get_result();

    if ($checkGradeResult->num_rows > 0) {
        // Update existing grade
        $updateGradeSql = "UPDATE grades SET grade_given = ?, feedback = ?, graded_at = NOW(), grade_status = 'Graded' WHERE participant_id = ? AND training_id = ? AND module_id = ?";
        $updateGradeStmt = $conn->prepare($updateGradeSql);
        $updateGradeStmt->bind_param("ssiii", $grade_given, $feedback, $participant_id, $training_id, $module_id);
        if ($updateGradeStmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Grade updated successfully!', 'redirect' => true]); // Add redirect instruction
        } else {
            error_log("Update Grade Error: " . print_r($updateGradeStmt->errorInfo(), true));
            echo json_encode(['status' => 'error', 'message' => 'Failed to update grade. Please try again.']);
        }
    } else {
        // Insert new grade
        $insertGradeSql = "INSERT INTO grades (participant_id, training_id, module_id, grade_given, feedback, grade_status) VALUES (?, ?, ?, ?, ?, 'Graded')";
        $insertGradeStmt = $conn->prepare($insertGradeSql);
        $insertGradeStmt->bind_param("iiiss", $participant_id, $training_id, $module_id, $grade_given, $feedback);
        if ($insertGradeStmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Grade saved successfully!', 'redirect' => true]); // Add redirect instruction
        } else {
            error_log("Insert Grade Error: " . print_r($insertGradeStmt->errorInfo(), true));
            echo json_encode(['status' => 'error', 'message' => 'Failed to save grade. Please try again.']);
        }
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}

$conn->close();
?>
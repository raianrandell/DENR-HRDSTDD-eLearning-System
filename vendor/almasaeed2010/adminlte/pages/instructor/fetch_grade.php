<?php
include '../includes/config.php';

if (isset($_GET['participant_id']) && isset($_GET['training_id']) && isset($_GET['module_id'])) {
    $participant_id = $_GET['participant_id'];
    $training_id = $_GET['training_id'];
    $module_id = $_GET['module_id'];

    $checkExistingGradeSql = "SELECT grade_given, feedback FROM grades WHERE participant_id = ? AND training_id = ? AND module_id = ?";
    $checkExistingGradeStmt = $conn->prepare($checkExistingGradeSql);
    $checkExistingGradeStmt->bind_param("iii", $participant_id, $training_id, $module_id);
    $checkExistingGradeStmt->execute();
    $checkExistingGradeResult = $checkExistingGradeStmt->get_result();

    if ($checkExistingGradeResult->num_rows > 0) {
        $gradeData = $checkExistingGradeResult->fetch_assoc();
        echo json_encode($gradeData); // Return grade data as JSON
    } else {
        echo json_encode(null); // Return null if no grade exists
    }
} else {
    echo json_encode(null); // Return null if parameters are missing
}
?>
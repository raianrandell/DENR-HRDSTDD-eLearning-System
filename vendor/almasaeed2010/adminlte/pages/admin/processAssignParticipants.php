<?php
session_start();
require '../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $training_id = $_POST['training_id'];

    if (!isset($_POST['participant_ids']) || empty($_POST['participant_ids'])) {
        $_SESSION['error'] = "No participants selected.";
        header("Location: assignParticipants.php?training_id=$training_id");
        exit();
    }

    $participant_ids = $_POST['participant_ids'];

    // Prepare the insert query for assigning participants to the course
    $assign_sql = "INSERT INTO training_participants (training_id, participant_id) VALUES (?, ?)";
    if ($assign_stmt = $conn->prepare($assign_sql)) {
        foreach ($participant_ids as $participant_id) {
            $assign_stmt->bind_param("ii", $training_id, $participant_id);
            $assign_stmt->execute();
        }
        $assign_stmt->close();
    } else {
        $_SESSION['error'] = "Error preparing assignment query: " . $conn->error;
        header("Location: assignParticipants.php?training_id=$training_id");
        exit();
    }

    // **Update `in_training` status to 1 for assigned participants**
    $ids_placeholder = implode(',', array_fill(0, count($participant_ids), '?')); // Create placeholders for bind_param
    $update_sql = "UPDATE user_participants SET in_training = 1 WHERE participant_id IN ($ids_placeholder)";
    if ($update_stmt = $conn->prepare($update_sql)) {
        $update_stmt->bind_param(str_repeat('i', count($participant_ids)), ...$participant_ids);
        $update_stmt->execute();
        $update_stmt->close();
    }

    $_SESSION['success'] = "Participants successfully assigned to the course!";  // Success message
    header("Location: assignParticipants.php?training_id=$training_id");
    exit();
}
?>
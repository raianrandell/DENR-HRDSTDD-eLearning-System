<?php
require '../includes/config.php'; // Database connection

if (isset($_POST['training_id'])) {
    $training_id = intval($_POST['training_id']);
    $response = ['participants' => [], 'smes' => []];

    // Fetch Participants
    $sql_participants = "SELECT p.participant_id, p.first_name, p.last_name, p.email
                        FROM user_participants p
                        JOIN training_participants tp ON p.participant_id = tp.participant_id
                        WHERE tp.training_id = ?";
    $stmt = $conn->prepare($sql_participants);
    $stmt->bind_param("i", $training_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $response['participants'][] = [
            'name' => htmlspecialchars($row['first_name'] . ' ' . $row['last_name']),
            'email' => htmlspecialchars($row['email'])
        ];
    }
    $stmt->close();

    // Fetch SMEs (Instructors)
    $sql_smes = "SELECT i.instructor_id, i.first_name, i.last_name, i.email
                 FROM user_instructors i
                 JOIN training_instructors ti ON i.instructor_id = ti.instructor_id
                 WHERE ti.training_id = ?";
    $stmt = $conn->prepare($sql_smes);
    $stmt->bind_param("i", $training_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $response['smes'][] = [
            'name' => htmlspecialchars($row['first_name'] . ' ' . $row['last_name']),
            'email' => htmlspecialchars($row['email'])
        ];
    }
    $stmt->close();

    echo json_encode($response);
    exit;
}

echo json_encode(['error' => 'Invalid request']);
?>
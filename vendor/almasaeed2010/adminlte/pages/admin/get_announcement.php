<?php
require '../includes/config.php'; // Adjust the path if necessary

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT * FROM announcements WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        header('Content-Type: application/json');
        echo json_encode($row);
    } else {
        http_response_code(404); // Not Found
        echo json_encode(array('error' => 'Announcement not found'));
    }
    $stmt->close();
} else {
    http_response_code(400); // Bad Request
    echo json_encode(array('error' => 'ID parameter missing'));
}
?>
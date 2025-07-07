<?php
// deleteModuleHandler.php
session_start();
include '../includes/config.php';

if (!isset($_SESSION['instructor_id'])) {
    echo "Unauthorized access!";
    exit();
}

// Modified to expect POST request and get module_id from $_POST
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['module_id'])) {
    $moduleId = $_POST['module_id']; // Get module_id from $_POST

    // Get the file path to delete (optional: if you want to delete the file from uploads too)
    $getFileSql = "SELECT file_path FROM modules WHERE module_id = ?";
    $getFileStmt = $conn->prepare($getFileSql);
    $getFileStmt->bind_param("i", $moduleId);
    $getFileStmt->execute();
    $getFileResult = $getFileStmt->get_result();
    $moduleData = $getFileResult->fetch_assoc();
    $filePathToDelete = $moduleData['file_path']; // May be empty if no file was uploaded
    // $link_url is not used in delete process, so removing the line.

    // Delete module from database
    $deleteSql = "DELETE FROM modules WHERE module_id = ?";
    if ($deleteStmt = $conn->prepare($deleteSql)) {
        $deleteStmt->bind_param("i", $moduleId);
        if ($deleteStmt->execute()) {
            // Optionally delete the file from uploads directory if a file path exists and it's not empty
            if (!empty($filePathToDelete) && file_exists($filePathToDelete)) {
                unlink($filePathToDelete); // Delete the file
            }
            echo "Module deleted successfully!";
        } else {
            echo "Error deleting module from database: " . $deleteStmt->error;
        }
    } else {
        echo "Database error: " . $conn->error;
    }
} else {
    echo "Invalid request for module deletion.";
}
?>
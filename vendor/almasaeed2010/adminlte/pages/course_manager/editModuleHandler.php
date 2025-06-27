<?php
// editModuleHandler.php
session_start();
if (!isset($_SESSION['instructor_id'])) {
    echo "Error: Instructor session not active.";
    exit();
}

include '../includes/config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['module_id']) || empty($_POST['module_id'])) {
        echo "Error: Missing module ID.";
        exit();
    }

    $moduleId = $_POST['module_id'];
    $moduleDescription = isset($_POST['module_description']) ? trim($_POST['module_description']) : null;
    $linkUrl = isset($_POST['link_url']) ? trim($_POST['link_url']) : null;

    // Initialize SQL update parts and parameters
    $updateSqlParts = [];
    $params = [];
    $types = "";

    // 1. Update Module Description if provided
    if ($moduleDescription !== null) {
        $updateSqlParts[] = "module_description = ?";
        $params[] = $moduleDescription;
        $types .= "s";
    }

    // 2. Update Link URL if provided
    if ($linkUrl !== null) {
        $updateSqlParts[] = "link_url = ?";
        $params[] = $linkUrl;
        $types .= "s";
    }

    // 3. Handle File Upload (New file or keep existing)
    if ($_FILES['file_path']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/'; // Define your upload directory - Make sure this directory exists and is writable
        $fileName = basename($_FILES['file_path']['name']);
        $filePath = $uploadDir . $fileName; // **IMPORTANT: Sanitize filename and handle potential collisions for production!**

        // Move the uploaded file to the desired directory
        if (move_uploaded_file($_FILES['file_path']['tmp_name'], $filePath)) {
            $updateSqlParts[] = "file_path = ?";
            $params[] = $filePath;
            $types .= "s";
        } else {
            echo "Error: Failed to move uploaded file.";
            exit; // Stop execution if file move fails
        }
    } elseif ($_FILES['file_path']['error'] !== UPLOAD_ERR_NO_FILE && $_FILES['file_path']['error'] !== UPLOAD_ERR_OK) {
        // Handle other file upload errors (optional, but good practice)
        echo "Error: File upload error: " . $_FILES['file_path']['error'];
        exit;
    }
    // If UPLOAD_ERR_NO_FILE, it means no new file was uploaded, so we don't update file_path, keeping the existing one.


    // Construct the final SQL UPDATE query
    if (!empty($updateSqlParts)) {
        $sql = "UPDATE modules SET updated_at = NOW(), " . implode(", ", $updateSqlParts) . " WHERE module_id = ?";
        $params[] = $moduleId;
        $types .= "i";

        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            echo "Error: SQL prepare error: " . $conn->error;
            exit;
        }

        // Dynamically bind parameters
        $bind_names[] = $types;
        foreach ($params as $key => $val) {
            $bind_names[] = &$params[$key]; // Pass by reference is required
        }
        call_user_func_array([$stmt, 'bind_param'], $bind_names);


        if ($stmt->execute()) {
            echo "Module updated successfully.";
        } else {
            echo "Error: Module update failed: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "No module information to update."; // If no fields were intended to be updated.
    }
    $conn->close();

} else {
    echo "Error: Invalid request method.";
}
?>
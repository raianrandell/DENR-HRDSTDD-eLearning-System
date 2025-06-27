<?php
// addModuleHandler.php
session_start();
include '../includes/config.php'; // Make sure the path is correct

// Check if instructor is logged in
if (!isset($_SESSION['instructor_id'])) {
    // Use die() or exit() and provide a clear error message
    // It's better to send an HTTP error code too for AJAX requests
    http_response_code(401); // Unauthorized
    die("Error: Unauthorized access!");
}

// Check if it's a POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // --- Basic Input Validation ---
    if (
        !isset($_POST['training_id'], $_POST['module_name'], $_POST['module_description'], $_POST['module_type']) ||
        empty($_POST['training_id']) || empty(trim($_POST['module_name'])) || empty(trim($_POST['module_description'])) || empty(trim($_POST['module_type']))
    ) {
        http_response_code(400); // Bad Request
        die("Error: All required fields (Training ID, Module Name, Description, Type) must be filled.");
    }

    // --- Assign and Sanitize Variables ---
    $trainingID = filter_var($_POST['training_id'], FILTER_SANITIZE_NUMBER_INT);
    $module_name = trim($_POST['module_name']); // Keep as string, sanitize later if needed for output
    $module_description = trim($_POST['module_description']); // Keep as string, sanitize later if needed for output
    $module_type = trim($_POST['module_type']);
    // Use filter_var for URL validation/sanitization
    $link_url = isset($_POST['link_url']) && !empty(trim($_POST['link_url']))
              ? filter_var(trim($_POST['link_url']), FILTER_SANITIZE_URL)
              : '';
    $file_path = ''; // Initialize file path

    // Validate the sanitized URL (optional but good practice)
    if (!empty($link_url) && filter_var($link_url, FILTER_VALIDATE_URL) === false) {
         http_response_code(400);
         die("Error: Invalid Link URL provided.");
    }


    // --- **MODIFIED CHECK**: Check for existing Activity/Quiz within the SAME module name for this training ---
    if ($module_type == 'Activity' || $module_type == 'Quiz') {
        $checkSql = "SELECT COUNT(*)
                     FROM modules
                     WHERE training_id = ?
                       AND module_name = ?
                       AND module_type IN ('Activity', 'Quiz')"; // Check for either type

        $checkStmt = $conn->prepare($checkSql);

        if (!$checkStmt) {
            // Handle SQL preparation error
            error_log("Database error preparing check statement: " . $conn->error); // Log detailed error
            http_response_code(500); // Internal Server Error
            die("Error: Database error during validation check. Please try again later.");
        }

        // Bind parameters: integer (i), string (s)
        $checkStmt->bind_param("is", $trainingID, $module_name);

        if (!$checkStmt->execute()) {
            // Handle SQL execution error
             error_log("Database error executing check statement: " . $checkStmt->error); // Log detailed error
             http_response_code(500);
             die("Error: Database error during validation execution. Please try again later.");
        }

        $checkStmt->bind_result($count);
        $checkStmt->fetch();
        $checkStmt->close(); // Close the statement promptly

        if ($count > 0) {
            // Simpler error message as requested
            die("Error: Only 1 Activity or Quiz is allowed per module."); // <--- REPLACE WITH THIS
        }
    }
    // --- End Modified Check ---


    // --- File Upload Handling (Keep your existing robust logic) ---
    if (isset($_FILES['file_path']) && $_FILES['file_path']['error'] === UPLOAD_ERR_OK) {
        $file_info = $_FILES['file_path'];
        $file_name = $file_info['name'];
        $file_tmp = $file_info['tmp_name'];
        $file_size = $file_info['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        $allowed_ext = [
            "pdf", "doc", "docx", "ppt", "pptx", "xls", "xlsx", "csv",
            "mp4", "avi", "mov", "wmv", "flv", "mkv", "webm", "3gp", "mpeg", "mpg", "ogv",
            "jpg", "jpeg", "png", "gif", "bmp", "svg", "tiff", "webp"
        ];

        if (!in_array($file_ext, $allowed_ext)) {
             http_response_code(400);
             die("Error: Invalid file format ('" . htmlspecialchars($file_ext) . "'). Please upload allowed document, video, or image types.");
        }

        $max_file_size = 41943040; // 40MB
        if ($file_size > $max_file_size) {
             http_response_code(400);
             die("Error: File size (" . round($file_size / 1024 / 1024, 2) . "MB) exceeds the limit of " . ($max_file_size / 1024 / 1024) . "MB.");
        }

        // Sanitize the filename (base name only)
        $sanitized_base_name = preg_replace("/[^a-zA-Z0-9._-]/", "_", pathinfo($file_name, PATHINFO_FILENAME));

        // Define upload directory - IMPORTANT: Make sure this path is correct and writable by the web server
        // Consider using a path relative to the document root or an absolute path for reliability.
        $upload_dir = '../uploads/'; // Relative to this script's location.
        // Example: $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/lms/instructor/uploads/';

        if (!is_dir($upload_dir)) {
            if (!mkdir($upload_dir, 0755, true)) { // Create recursively with appropriate permissions
                 error_log("Failed to create upload directory: " . $upload_dir);
                 http_response_code(500);
                 die("Error: Server configuration error (cannot create upload directory).");
            }
        }
        if (!is_writable($upload_dir)) {
             error_log("Upload directory not writable: " . $upload_dir);
             http_response_code(500);
             die("Error: Server configuration error (upload directory not writable).");
        }

        // Construct the potential file path
        $file_path_base = $upload_dir . $sanitized_base_name;
        $file_path_ext = "." . $file_ext;
        $final_file_path_on_server = $file_path_base . $file_path_ext;

        // Handle filename collisions
        $counter = 1;
        while (file_exists($final_file_path_on_server)) {
            $final_file_path_on_server = $file_path_base . "_" . $counter . $file_path_ext;
            $counter++;
        }

        // Move the uploaded file
        if (!move_uploaded_file($file_tmp, $final_file_path_on_server)) {
            error_log("Failed to move uploaded file from $file_tmp to $final_file_path_on_server");
            http_response_code(500);
            die("Error: Failed to save uploaded file. Please try again.");
        }

        // Store a relative path (or URL path) in the database for linking/display
        // Adjust this based on how you access files from the web
        $file_path = $upload_dir . basename($final_file_path_on_server); // e.g., 'uploads/myfile_1.pdf'

    } elseif (isset($_FILES['file_path']) && $_FILES['file_path']['error'] !== UPLOAD_ERR_NO_FILE) {
        // Handle other specific upload errors
        $upload_errors = [
            UPLOAD_ERR_INI_SIZE   => "The uploaded file exceeds the upload_max_filesize directive in php.ini.",
            UPLOAD_ERR_FORM_SIZE  => "The uploaded file exceeds the MAX_FILE_SIZE directive specified in the HTML form.",
            UPLOAD_ERR_PARTIAL    => "The uploaded file was only partially uploaded.",
            UPLOAD_ERR_NO_TMP_DIR => "Missing a temporary folder.",
            UPLOAD_ERR_CANT_WRITE => "Failed to write file to disk.",
            UPLOAD_ERR_EXTENSION  => "A PHP extension stopped the file upload.",
        ];
        $error_code = $_FILES['file_path']['error'];
        $error_message = $upload_errors[$error_code] ?? "Unknown upload error code: $error_code";
        http_response_code(500); // Or 400 depending on the error
        die("Error: File upload failed: " . $error_message);
    }
    // --- End File Upload Handling ---


    // --- Insert module into the database ---
    $sql = "INSERT INTO modules (training_id, module_name, module_description, module_type, file_path, link_url, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())"; // Add timestamps

    if ($stmt = $conn->prepare($sql)) {
        // Bind parameters: integer (i), string (s), string (s), string (s), string (s), string (s)
        $stmt->bind_param("isssss", $trainingID, $module_name, $module_description, $module_type, $file_path, $link_url);

        if ($stmt->execute()) {
            // Success
            echo "Module added successfully!"; // Send success message back to AJAX
        } else {
            // Database execution error
            error_log("Error executing module insert statement: " . $stmt->error);
            http_response_code(500);
            die("Error: Failed to add module to the database: " . $stmt->error); // Show specific SQL error for debugging (remove in production)
            // die("Error: Failed to add module to the database. Please try again later."); // Production message
        }
        $stmt->close(); // Close statement
    } else {
        // Database preparation error
        error_log("Error preparing module insert statement: " . $conn->error);
        http_response_code(500);
        die("Error: Database error preparing the add operation. Please try again later.");
    }

    $conn->close(); // Close connection

} else {
    // Invalid request method
    http_response_code(405); // Method Not Allowed
    die("Error: Invalid request method.");
}
?>
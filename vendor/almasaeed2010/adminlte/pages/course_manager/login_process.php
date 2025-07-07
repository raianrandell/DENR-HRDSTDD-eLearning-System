<?php
session_start();
require '../includes/config.php'; // Ensure this path is correct

// Check if the form was submitted
if (isset($_POST['login'])) {

    // Get input
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Basic validation
    if (empty($username) || empty($password)) {
        $_SESSION['error'] = 'Username and Password are required.';
        header('Location: course_manager_login.php'); // Redirect back
        exit();
    }

    // Prepare SQL statement
    $sql = "SELECT id, first_name, last_name, password, photo FROM user_course_manager WHERE username = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        $_SESSION['error'] = 'Database error: Could not prepare statement.';
        error_log("Prepare failed: (" . $conn->errno . ") " . $conn->error);
        header('Location: course_manager_login.php');
        exit();
    }

    $stmt->bind_param("s", $username);

    if (!$stmt->execute()) {
        $_SESSION['error'] = 'Database error: Could not execute statement.';
        error_log("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
        $stmt->close();
        $conn->close();
        header('Location: course_manager_login.php');
        exit();
    }

    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            // Password is correct, set session variables
            $_SESSION['course_manager_id'] = $user['id'];
            $_SESSION['name'] = trim($user['first_name'] . ' ' . $user['last_name']);
            $_SESSION['photo'] = $user['photo'];

            // Set success message
            $_SESSION['success'] = 'Login successful! Redirecting...';

            // *** Redirect back to login page TO SHOW THE ALERT ***
            header('Location: course_manager_login.php');
            $stmt->close();
            $conn->close();
            exit();

        } else {
            // Incorrect password
            $_SESSION['error'] = 'Incorrect username or password.';
            header('Location: course_manager_login.php');
        }
    } else {
        // User not found
        $_SESSION['error'] = 'Incorrect username or password.';
        header('Location: course_manager_login.php');
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();
    exit();

} else {
    // Invalid access
    $_SESSION['error'] = 'Invalid login attempt.';
    header('Location: course_manager_login.php');
    exit();
}
?>
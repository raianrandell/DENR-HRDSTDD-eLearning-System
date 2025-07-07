<?php
session_start();
require '../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "All fields are required.";
        header("Location: instructorlogin.php");
        exit();
    }

    $sql = "SELECT * FROM user_instructors WHERE email = ? AND status = 'Active'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $_SESSION['instructor_id'] = $user['instructor_id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['photo'] = $user['photo'];

            $_SESSION['success'] = "Login successful.";
            header("Location: instructorlogin.php");
            exit();
        } else {
            $_SESSION['error'] = "Invalid email or password.";
            header("Location: instructorlogin.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "No account found or account is inactive.";
        header("Location: instructorlogin.php");
        exit();
    }
} else {
    header("Location: instructorlogin.php");
    exit();
}
?>

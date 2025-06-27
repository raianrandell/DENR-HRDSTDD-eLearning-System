<?php
session_start();
require '../includes/config.php';

if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("Location: admindashboard.php");
    exit;
}

$error = "";
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password.";
    } else {
        $stmt = $conn->prepare("SELECT id, username, password FROM user_admin WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->bind_result($user_id, $db_username, $hashed_password);
            $stmt->fetch();
            if (password_verify($password, $hashed_password)) {
                $_SESSION["loggedin"] = true;
                $_SESSION["username"] = $db_username;
                $_SESSION["user_id"] = $user_id;
                $success = true;
            } else {
                $error = "Invalid username or password.";
            }
        } else {
            $error = "Invalid username or password.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.1.0/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11">
    <style>
        body {
            min-height: 100vh;
            background: url('../../dist/img/denrbg.jpg') no-repeat center center/cover;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            position: relative;
        }

        body::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(75, 181, 67, 0.7), rgba(17, 117, 84, 0.7));
            z-index: 1;
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            position: relative;
            z-index: 2;
            padding: 2rem;
        }

        .card-header {
            text-align: center;
            padding: 1.5rem 0;
            border-bottom: none;
        }

        .logo {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            margin-bottom: 1rem;
            object-fit: cover;
        }

        .login-title {
            color: #2E7D32;
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .login-subtitle {
            color: #666;
            font-size: 1rem;
        }

        .card-body {
            padding: 2rem;
        }

        /* Uiverse.io Input Styles */
        .group {
            position: relative;
            margin-bottom: 2rem;
        }

        .input {
            font-size: 16px;
            padding: 10px 10px 10px 5px;
            display: block;
            width: 100%;
            border: none;
            border-bottom: 2px solid #4BB543;
            background: transparent;
        }

        .input:focus {
            outline: none;
        }

        .group label {
            color: #999;
            font-size: 18px;
            font-weight: normal;
            position: absolute;
            pointer-events: none;
            left: 5px;
            top: 10px;
            transition: 0.2s ease all;
            -moz-transition: 0.2s ease all;
            -webkit-transition: 0.2s ease all;
        }

        .input:focus ~ label, .input:valid ~ label {
            top: -20px;
            font-size: 14px;
            color: #117554;
        }

        .bar {
            position: relative;
            display: block;
            width: 100%;
        }

        .bar:before, .bar:after {
            content: '';
            height: 2px;
            width: 0;
            bottom: 0;
            position: absolute;
            background: #117554;
            transition: 0.2s ease all;
            -moz-transition: 0.2s ease all;
            -webkit-transition: 0.2s ease all;
        }

        .bar:before {
            left: 50%;
        }

        .bar:after {
            right: 50%;
        }

        .input:focus ~ .bar:before, .input:focus ~ .bar:after {
            width: 50%;
        }

        .highlight {
            position: absolute;
            height: 60%;
            width: 100px;
            top: 25%;
            left: 0;
            pointer-events: none;
            opacity: 0.5;
        }

        .input:focus ~ .highlight {
            animation: inputHighlighter 0.3s ease;
        }

        @keyframes inputHighlighter {
            from {
                background: #117554;
            }
            to {
                width: 0;
                background: transparent;
            }
        }

        /* Button Styles */
        .btn-login {
            background: linear-gradient(90deg, #4BB543, #117554);
            border: none;
            border-radius: 25px;
            padding: 0.8rem;
            width: 100%;
            color: white;
            font-weight: 600;
            transition: transform 0.3s ease;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            background: linear-gradient(90deg, #117554, #4BB543);
        }

        .forgot-password {
            text-align: center;
            margin-top: 1rem;
        }

        .forgot-password a {
            color: #4BB543;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .forgot-password a:hover {
            color: #117554;
            text-decoration: underline;
        }

        @media (max-width: 576px) {
            .login-card {
                margin: 1rem;
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="card-header">
            <img src="../../dist/img/denrlogo.jpg" alt="DENR Logo" class="logo">
            <h3 class="login-title">Admin Login</h3>
            <p class="login-subtitle">Talent Development Hub</p>
        </div>
        <div class="card-body">
            <form action="" method="POST">
                <div class="group">
                    <input required type="text" name="username" class="input">
                    <span class="highlight"></span>
                    <span class="bar"></span>
                    <label>Username</label>
                </div>
                <div class="group">
                    <input required type="password" name="password" class="input">
                    <span class="highlight"></span>
                    <span class="bar"></span>
                    <label>Password</label>
                </div>
                <button type="submit" class="btn btn-login">Log In</button>
                <div class="forgot-password">
                    <a href="#">Forgot password?</a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.1.0/js/adminlte.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            <?php if ($success): ?>
                Swal.fire({
                    title: 'Login Successful',
                    text: 'Redirecting to Dashboard...',
                    icon: 'success',
                    showConfirmButton: false,
                    timer: 1500,
                    timerProgressBar: true,
                }).then(() => {
                    window.location.href = "admindashboard.php";
                });
            <?php elseif (!empty($error)): ?>
                Swal.fire({
                    title: 'Error!',
                    text: '<?php echo addslashes($error); ?>',
                    icon: 'error',
                });
            <?php endif; ?>
        });
    </script>
</body>
</html>
<?php
// MUST be the very first line to handle sessions
session_start();

// --- CORRECTED CHECK ---
// Redirect logged-in users to dashboard ONLY IF there isn't a success/error flash message
// from the login process waiting to be displayed.
if (isset($_SESSION['course_manager_id']) && !isset($_SESSION['success']) && !isset($_SESSION['error'])) {
    // User is already properly logged in and didn't just come from login_process.php
    header('Location: courseManager.php'); // Adjust path if needed
    exit(); // Stop script execution
}
// If we reach here, it means either:
// 1. The user is not logged in.
// 2. The user just successfully logged in (course_manager_id is set) AND a $_SESSION['success'] message exists.
// 3. The user failed login (course_manager_id is NOT set) AND a $_SESSION['error'] message exists.
// In cases 2 and 3, we NEED to let the rest of the page load to show the SweetAlert.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Manager Login</title>
    <!-- CSS Includes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.1.0/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Custom CSS -->
    <style>
        body {
            min-height: 100vh;
            background: url('../../dist/img/denrbg.jpg') no-repeat center center/cover; /* Adjust path */
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            position: relative;
        }
        body::before {
            content: "";
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(135deg, rgba(75, 181, 67, 0.7), rgba(17, 117, 84, 0.7));
            z-index: 1;
        }
        .login-card {
            width: 100%; max-width: 420px; background: white; border-radius: 15px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2); overflow: hidden;
            position: relative; z-index: 2; padding: 2rem;
        }
        .card-header { text-align: center; padding: 1.5rem 0; border-bottom: none; }
        .logo { width: 80px; height: 80px; border-radius: 50%; margin-bottom: 1rem; object-fit: cover; }
        .login-title { color: #2E7D32; font-size: 1.8rem; font-weight: 600; margin-bottom: 0.5rem; }
        .login-subtitle { color: #666; font-size: 1rem; }
        .card-body { padding: 2rem; }
        .group { position: relative; margin-bottom: 2rem; }
        .input { font-size: 16px; padding: 10px 10px 10px 5px; display: block; width: 100%; border: none; border-bottom: 2px solid #4BB543; background: transparent; }
        .input:focus { outline: none; }
        .group label { color: #999; font-size: 18px; font-weight: normal; position: absolute; pointer-events: none; left: 5px; top: 10px; transition: 0.2s ease all; }
        .input:focus ~ label, .input:valid ~ label, .input:not(:placeholder-shown) ~ label {
             top: -20px; font-size: 14px; color: #117554;
        }
        .input:not(:placeholder-shown) {
           border-bottom: 2px solid #117554;
        }
        .input::placeholder { color: transparent; }
        .bar { position: relative; display: block; width: 100%; }
        .bar:before, .bar:after { content: ''; height: 2px; width: 0; bottom: 0; position: absolute; background: #117554; transition: 0.2s ease all; }
        .bar:before { left: 50%; } .bar:after { right: 50%; }
        .input:focus ~ .bar:before, .input:focus ~ .bar:after { width: 50%; }
        .btn-login { background: linear-gradient(90deg, #4BB543, #117554); border: none; border-radius: 25px; padding: 0.8rem; width: 100%; color: white; font-weight: 600; transition: transform 0.3s ease, background 0.3s ease; }
        .btn-login:hover { transform: translateY(-2px); background: linear-gradient(90deg, #117554, #4BB543); }
        .forgot-password { text-align: center; margin-top: 1rem; }
        .forgot-password a { color: #4BB543; text-decoration: none; font-size: 0.9rem; }
        .forgot-password a:hover { color: #117554; text-decoration: underline; }
        @media (max-width: 576px) { .login-card { margin: 1rem; padding: 1.5rem; } }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="card-header">
            <img src="../../dist/img/denrlogo.jpg" alt="DENR Logo" class="logo"> <!-- Adjust path -->
            <h3 class="login-title">Course Manager Login</h3>
            <p class="login-subtitle">Talent Development Hub</p>
        </div>
        <div class="card-body">
            <form action="login_process.php" method="POST">
                <div class="group">
                    <input required type="text" name="username" class="input" placeholder=" ">
                    <span class="bar"></span>
                    <label>Username</label>
                </div>
                <div class="group">
                    <input required type="password" name="password" class="input" placeholder=" ">
                    <span class="bar"></span>
                    <label>Password</label>
                </div>
                <button type="submit" name="login" class="btn btn-login">Log In</button>
                <div class="forgot-password">
                    <a href="#">Forgot password?</a>
                </div>
            </form>
        </div>
    </div>

    <!-- REQUIRED SCRIPTS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.1.0/js/adminlte.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- SweetAlert handler -->
    <?php
    // Check for Success Message (set by login_process.php)
    // This JS will now correctly execute because the redirect at the top didn't happen
    if (isset($_SESSION['success'])) {
        echo "
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'success',
                title: 'Login Successful',
                text: '" . addslashes($_SESSION['success']) . "',
                timer: 1800, // Auto close timer
                timerProgressBar: true,
                showConfirmButton: false // Hide OK button
            }).then(() => {
                // Redirect AFTER the alert timer is done
                window.location.href = 'courseManager.php'; // Adjust path if needed
            });
        });
        </script>";
        // Unset the message AFTER echoing the script
        unset($_SESSION['success']);
    }

    // Check for Error Message (set by login_process.php)
    if (isset($_SESSION['error'])) {
        echo "
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: 'Login Failed',
                text: '" . addslashes($_SESSION['error']) . "'
                // confirmButtonColor: '#d33' // Optional: Custom button color
            });
        });
        </script>";
        // Unset the message AFTER echoing the script
        unset($_SESSION['error']);
    }
    ?>
</body>
</html>
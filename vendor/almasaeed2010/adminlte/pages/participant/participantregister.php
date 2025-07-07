<?php
session_start();
// config.php should contain your database connection ($conn)
include '../includes/config.php';

// Display Error Messages
if (isset($_SESSION['error'])) {
    echo "
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: '" . addslashes($_SESSION['error']) . "' // Use addslashes for safety
        });
    });
    </script>";
    unset($_SESSION['error']);
}

// Display Pending Approval Message (Replaces the old success message)
if (isset($_SESSION['success_pending'])) {
    echo "
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'info', // Info icon for pending status
            title: 'Registration Submitted',
            text: '" . addslashes($_SESSION['success_pending']) . "',
            confirmButtonText: 'OK' // Just an OK button
        });
    });
    </script>";
    unset($_SESSION['success_pending']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Participant Registration</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.1.0/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <style>
        /* --- Keep your existing CSS styles here --- */
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
        .card {
            width: 100%;
            max-width: 600px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
            position: relative;
            z-index: 2;
            padding: 2rem;
        }
        .card-header { text-align: center; padding: 1.5rem 0; border-bottom: none; }
        .logo { width: 80px; height: 80px; border-radius: 50%; margin-bottom: 1rem; object-fit: cover; }
        .title { color: #2E7D32; font-size: 1.8rem; font-weight: 600; margin-bottom: 0.5rem; }
        .subtitle { color: #666; font-size: 1rem; }
        .card-body { padding: 2rem; }
        .group { position: relative; margin-bottom: 2rem; }
        .input { font-size: 16px; padding: 12px 10px; display: block; width: 100%; height: 45px; border: none; border-bottom: 2px solid #4BB543; background: transparent; box-sizing: border-box; }
        .input:focus { outline: none; }
        .group label { color: #999; font-size: 16px; font-weight: normal; position: absolute; pointer-events: none; left: 10px; top: 14px; transition: 0.2s ease all; }
        .input:focus ~ label, .input:not(:placeholder-shown) ~ label { top: -20px; font-size: 14px; color: #117554; }
        .bar { position: relative; display: block; width: 100%; }
        .bar:before, .bar:after { content: ''; height: 2px; width: 0; bottom: 0; position: absolute; background: #117554; transition: 0.2s ease all; }
        .bar:before { left: 50%; }
        .bar:after { right: 50%; }
        .input:focus ~ .bar:before, .input:focus ~ .bar:after { width: 50%; }
        select.input { height: 45px; padding: 10px; }
        .btn-action { background: transparent; border: 1px solid #4BB543; border-radius: 25px; padding: 12px; width: 100%; height: 45px; color: #4BB543; font-weight: 600; font-size: 16px; text-transform: uppercase; transition: transform 0.3s ease, background 0.3s ease, color 0.3s ease; }
        .btn-action:hover { transform: translateY(-2px); background: linear-gradient(90deg, #117554, #4BB543); color: #fff; }
        .link-section { text-align: center; margin-top: 1.5rem; }
        .link-section a { color: #4BB543; text-decoration: none; font-size: 0.9rem; transition: color 0.3s ease; }
        .link-section a:hover { color: #117554; text-decoration: underline; }
        @media (max-width: 576px) { .card { margin: 1rem; padding: 1.5rem; } }
    </style>
</head>
<body>
    <div class="card">
        <div class="card-header">
            <img src="../../dist/img/denrlogo.jpg" alt="DENR Logo" class="logo">
            <h3 class="title">Participant Registration</h3>
            <p class="subtitle">Talent Development Hub</p>
        </div>
        <div class="card-body">
            <!-- The action points to the processing script -->
            <form action="process_participant_registration.php" method="POST" enctype="multipart/form-data">
                <div class="group">
                     <!-- Make photo optional or required based on your needs -->
                    <input type="file" class="input" name="photo" accept="image/*" placeholder=" " id="photo">
                    <span class="bar"></span>
                    <label for="photo">Upload Photo (Optional)</label>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="group">
                            <input required type="text" class="input" name="first_name" placeholder=" " autofocus id="first_name">
                            <span class="bar"></span>
                            <label for="first_name">First Name *</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="group">
                            <input type="text" class="input" name="middle_name" placeholder=" " id="middle_name">
                            <span class="bar"></span>
                            <label for="middle_name">Middle Initial</label>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="group">
                            <input required type="text" class="input" name="last_name" placeholder=" " id="last_name">
                            <span class="bar"></span>
                            <label for="last_name">Last Name *</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="group">
                            <input required type="email" class="input" name="email" placeholder=" " id="email">
                            <span class="bar"></span>
                            <label for="email">Email *</label>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="group">
                            <input required type="password" class="input" id="password" name="password" placeholder=" ">
                            <span class="bar"></span>
                            <label for="password">Password *</label>
                             <!-- Optional: Add password visibility toggle -->
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="group">
                            <input type="tel" class="input" name="contact_number" pattern="[0-9]{11}" maxlength="11" placeholder=" " id="contact_number">
                            <span class="bar"></span>
                            <label for="contact_number">Contact Number (09...)</label>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="group">
                            <select required class="input" name="gender" id="gender">
                                <option value="" disabled selected hidden></option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Prefer not to say</option>
                            </select>
                            <span class="bar"></span>
                            <label for="gender">Gender *</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="group">
                            <input required type="number" class="input" name="age" placeholder=" " min="1" id="age">
                            <span class="bar"></span>
                            <label for="age">Age *</label>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="group">
                            <input type="text" class="input" name="office" placeholder=" " id="office">
                            <span class="bar"></span>
                            <label for="office">Office</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="group">
                            <input type="number" class="input" name="salary_grade" placeholder=" " min="1" id="salary_grade">
                            <span class="bar"></span>
                            <label for="salary_grade">Salary Grade</label>
                        </div>
                    </div>
                </div>
                <div class="group">
                    <input type="text" class="input" name="position" placeholder=" " id="position">
                    <span class="bar"></span>
                    <label for="position">Position/Designation</label>
                </div>

                <button type="submit" class="btn btn-action">Register</button>

                <div class="link-section">
                    <p>Already have an account? <a href="participantlogin.php">Log In here</a></p>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.1.0/js/adminlte.min.js"></script>
    <!-- Add password toggle JS if needed -->

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const togglePasswordButton = document.querySelector('#togglePassword');
            const passwordInput = document.querySelector('#password');

            if (togglePasswordButton && passwordInput) {
                togglePasswordButton.addEventListener('click', function () {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    this.querySelector('i').classList.toggle('fa-eye');
                    this.querySelector('i').classList.toggle('fa-eye-slash');
                });
            }
        });
    </script>
</body>
</html>

<?php
// Close connection if it was opened
if (isset($conn)) {
    $conn->close();
}
?>
<?php
session_start(); // Only once, at the very top
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- ... (your head section remains the same) ... -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Participants</title>
    <link rel="stylesheet" href="adminlte/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="adminlte/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.1/dist/css/adminlte.min.css">

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css">

    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
</head>
<body class="hold-transition sidebar-mini">
<!-- ... (your HTML remains largely the same) ... -->
<div class="preloader flex-column justify-content-center align-items-center">
    <img class="animation__shake rounded-circle" src="../../dist/img/denrlogo.jpg" alt="denrlogo" height="100" width="100">
</div>
<div class="wrapper">
    <?php include 'navbar.php'; ?>
    <?php include 'sidebar.php'; ?>

    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Add New Participants</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="courses.php">Home</a></li>
                            <li class="breadcrumb-item active">Add Participants</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-user"></i> Participants Information</h3>
                </div>
                <div class="card-body">
                    <form action="process_add_participants.php" method="POST" enctype="multipart/form-data">
                        <p><span style="color: red;">* Required</span></p>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Upload Photo</label>
                                    <input type="file" class="form-control" name="photo" accept="image/*">
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>First Name <span style="color: red;">*</span></label>
                                    <input type="text" class="form-control" name="first_name" required autofocus>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Middle Initial</label>
                                    <input type="text" class="form-control" name="middle_name">
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Last Name <span style="color: red;">*</span></label>
                                    <input type="text" class="form-control" name="last_name" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Email <span style="color: red;">*</span></label>
                                    <input type="email" class="form-control" name="email" required>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Password <span style="color: red;">*</span></label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="password" name="password" required>
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-secondary" id="togglePassword">
                                                <i class="fas fa-eye-slash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Contact Number</label>
                                    <input type="tel" class="form-control" name="contact_number" pattern="[0-9]{11}" maxlength="11">
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Gender <span style="color: red;">*</span></label>
                                    <select class="form-control select2" name="gender" required style="width: 100%;">
                                        <option value="">Select</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                        <option value="Other">Prefer not to say</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Office</label>
                                    <input type="text" class="form-control" name="office">
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Age <span style="color: red;">*</span></label>
                                    <input type="number" class="form-control" name="age" required>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Salary Grade</label>
                                    <input type="number" class="form-control" name="salary_grade">
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Position/Designation</label>
                                    <input type="text" class="form-control" name="position">
                                </div>
                            </div>
                           
                           
                        </div>
                        <div class="row">
                        <div class="col-md-3">
                        <div class="form-group" style="display:none;">
                            <label>In Training </label>
                            <select class="form-control select2" name="in_training" style="width: 100%;" disabled>
                                <option value="0" selected>No</option>   <!-- Value is always 0 -->
                            </select>
                            <input type="hidden" name="in_training" value="0">
                        </div>
                        </div>
                            <div class="col-12 text-right">
                                <a href="participants.php" class="btn btn-outline-secondary rounded-0">Back</a>
                                <button type="submit" class="btn btn-info rounded-0">Save</button>
                            </div>
                    </form>
                </div>
            </div>
        </section>
    </div>

    <?php include '../footer.php'; ?>
    </div>

    <script>
        $(document).ready(function () {
            let passwordField = $("#password");
            let icon = $("#togglePassword i");

            // Ensure password is hidden on load
            passwordField.attr("type", "password");
            icon.removeClass("fa-eye").addClass("fa-eye-slash");

            $("#togglePassword").click(function () {
                if (passwordField.attr("type") === "password") {
                    passwordField.attr("type", "text");
                    icon.removeClass("fa-eye-slash").addClass("fa-eye");
                } else {
                    passwordField.attr("type", "password");
                    icon.removeClass("fa-eye").addClass("fa-eye-slash");
                }
            });

            //Initialize Select2 Elements
            $('.select2').select2({
                theme: 'bootstrap4',
                width: 'resolve' // important!
            });


            //Display SweetAlert messages.  Put this *inside* $(document).ready()
            <?php if (isset($_SESSION['error'])) : ?>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '<?php echo $_SESSION['error']; ?>',
                confirmButtonColor: '#d33',
                confirmButtonText: 'OK'
            }).then(() => {
                <?php unset($_SESSION['error']); ?>
            });
            <?php endif; ?>
        });

    </script>

    <script src="adminlte/plugins/jquery/jquery.min.js"></script>

    <script src="adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>

    <script src="adminlte/dist/js/adminlte.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.1/dist/js/adminlte.min.js"></script>

    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

</body>
</html>
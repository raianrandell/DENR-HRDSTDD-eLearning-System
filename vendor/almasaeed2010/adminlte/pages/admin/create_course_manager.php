<?php
session_start(); // Only once, at the very top
require '../includes/config.php'; // Ensure DB connection is included

// Enable error reporting during development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Function to display SweetAlert messages (if reused elsewhere)
function showSweetAlert($icon, $title, $text) {
    echo "<script>
        Swal.fire({
            icon: '$icon',
            title: '$title',
            text: '$text',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'OK'
        });
    </script>";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $requiredFields = ['first_name', 'last_name', 'username', 'password'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            $_SESSION['error'] = "Please fill in all required fields.";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
    }

    $first_name = $_POST["first_name"];
    $middle_name = $_POST["middle_name"] ?? '';
    $last_name = $_POST["last_name"];
    $username = $_POST["username"];
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
    $office = $_POST["office"] ?? '';
    $position = $_POST["position"] ?? '';

    $target_dir = "uploads/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0775, true);
    }

    if ($_FILES["photo"]["error"] !== UPLOAD_ERR_OK) {
        $_SESSION['error'] = "Error uploading file. Error code: " . $_FILES["photo"]["error"];
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    $target_file = $target_dir . basename($_FILES["photo"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    $check = getimagesize($_FILES["photo"]["tmp_name"]);
    if ($check === false) {
        $_SESSION['error'] = "File is not an image.";
        $uploadOk = 0;
    }

    if ($_FILES["photo"]["size"] > 500000) {
        $_SESSION['error'] = "Sorry, your file is too large.";
        $uploadOk = 0;
    }

    if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
        $_SESSION['error'] = "Only JPG, JPEG, PNG & GIF files are allowed.";
        $uploadOk = 0;
    }

    if ($uploadOk) {
        $new_file_name = uniqid() . "." . $imageFileType;
        $target_file = $target_dir . $new_file_name;

        if (move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) {
            $sql = "INSERT INTO user_course_manager (first_name, middle_name, last_name, username, password, office, position, photo) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);

            if ($stmt) {
                $stmt->bind_param("ssssssss", $first_name, $middle_name, $last_name, $username, $password, $office, $position, $target_file);

                if ($stmt->execute()) {
                    $_SESSION['success'] = "Course Manager created successfully.";
                    header("Location: coursemanager.php");
                    exit();
                } else {
                    $_SESSION['error'] = "Error: " . $stmt->error;
                    error_log("MySQL error: " . $stmt->error);
                }

                $stmt->close();
            } else {
                $_SESSION['error'] = "Error preparing SQL statement.";
                error_log("Error preparing SQL statement: " . $conn->error);
            }
        } else {
            $_SESSION['error'] = "Sorry, there was an error uploading your file.";
        }
    }

    if (isset($_SESSION['error'])) {
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Course Manager</title>
    <link rel="stylesheet" href="adminlte/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="adminlte/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.1/dist/css/adminlte.min.css">

    <!-- DataTables -->
    <link rel="stylesheet" href="../../plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="../../plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="../../plugins/datatables-buttons/css/buttons.bootstrap4.min.css">

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <style>
        .warning-gradient {
            background: linear-gradient(225deg, #FFCC00, #FF9900, #FF6600);
            color: white;
        }
    </style>
</head>
<body class="hold-transition sidebar-mini">
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
                        <h1>Add New Course Manager</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="courses.php">Home</a></li>
                            <li class="breadcrumb-item active">Add Course Manager</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="card">
                <div class="card-header warning-gradient">
                    <h3 class="card-title"><i class="fas fa-user"></i> Course Manager Information</h3>
                </div>
                <div class="card-body">
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post"
                          enctype="multipart/form-data">
                        <p><span style="color: red;">* Required</span></p>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Upload Photo <span style="color: red;">*</span></label>
                                    <input type="file" class="form-control" name="photo" accept="image/*" required>
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
                                    <label>Username <span style="color: red;">*</span></label>
                                    <input type="text" class="form-control" name="username" required>
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
                                    <label>Office</label>
                                    <input type="text" class="form-control" name="office">
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Position/Designation</label>
                                    <input type="text" class="form-control" name="position">
                                </div>
                            </div>
                        </div>

                        <div class="col-12 text-right">
                            <a href="coursemanager.php" class="btn btn-outline-secondary rounded-0">Back</a>
                            <button type="submit" name="submit" class="btn warning-gradient rounded-0">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>

    <?php include '../footer.php'; ?>
</div>

<!-- Scripts -->
<script src="adminlte/plugins/jquery/jquery.min.js"></script>
<script src="adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="adminlte/dist/js/adminlte.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.1/dist/js/adminlte.min.js"></script>

<!-- DataTables & Plugins -->
<script src="../../plugins/datatables/jquery.dataTables.min.js"></script>
<script src="../../plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="../../plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="../../plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<script src="../../plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
<script src="../../plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
<script src="../../plugins/jszip/jszip.min.js"></script>
<script src="../../plugins/pdfmake/pdfmake.min.js"></script>
<script src="../../plugins/pdfmake/vfs_fonts.js"></script>
<script src="../../plugins/datatables-buttons/js/buttons.html5.min.js"></script>
<script src="../../plugins/datatables-buttons/js/buttons.print.min.js"></script>
<script src="../../plugins/datatables-buttons/js/buttons.colVis.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function () {
        let passwordField = $("#password");
        let icon = $("#togglePassword i");

        $("#togglePassword").click(function () {
            if (passwordField.attr("type") === "password") {
                passwordField.attr("type", "text");
                icon.removeClass("fa-eye-slash").addClass("fa-eye");
            } else {
                passwordField.attr("type", "password");
                icon.removeClass("fa-eye").addClass("fa-eye-slash");
            }
        });

        <?php if (isset($_SESSION['error'])) : ?>
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: '<?php echo $_SESSION['error']; ?>',
            confirmButtonColor: '#d33',
            confirmButtonText: 'OK'
        });
        <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])) : ?>
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: '<?php echo $_SESSION['success']; ?>',
            confirmButtonColor: '#28a745',
            confirmButtonText: 'OK'
        });
        <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
    });
</script>
</body>
</html>

<?php
require '../includes/config.php'; // Database connection
session_start(); // Start session to store messages

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $training_title = $_POST['training_name'];
    $description = $_POST['description'];
    $location = $_POST['location'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $training_hrs = isset($_POST['training_hrs']) && $_POST['training_hrs'] !== '' ? (int)$_POST['training_hrs'] : null;

    // Validate input (add more validation as needed)
    if (empty($training_title) || empty($description) || empty($location) || empty($start_date) || empty($end_date)) {
        $_SESSION['error'] = "All fields are required.";
        header("Location: createTraining.php"); // Redirect back to the form
        exit();
    }

    // Insert data into the course table
    $sql = "INSERT INTO training (training_title, `description`, `location`, `start_date`, `end_date`, training_hrs)
            VALUES (?, ?, ?, ?, ?, ?)";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("sssssi", $training_title, $description, $location, $start_date, $end_date, $training_hrs);  //Note the order of parameters here!
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Training Added Successfully";
        } else {
            $_SESSION['error'] = "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $_SESSION['error'] = "Database error. Please try again later.";
    }

    $conn->close();
    header("Location: training.php"); // Redirect to prevent form resubmission
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Training</title>
    <link rel="stylesheet" href="adminlte/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="adminlte/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.1/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="adminlte/plugins/icheck-bootstrap/icheck-bootstrap.min.css">

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <style>
    .required-asterisk {
      color: red;
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
                            <h1>Create Training</h1>
                        </div>
                        <div class="col-sm-6 text-right">
                            <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="courseManager.php">Trainings</a></li>
                                <li class="breadcrumb-item active">Add New Training</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </section>

            <section class="content">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-book-open"></i>  Training Information</h3>
                    </div>
                    <div class="card-body">
                    <p><span style="color: red;">* Required</span></p>
                        <form action="" method="POST">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Training Title <span class="required-asterisk">*</span></label>
                                        <input type="text" class="form-control" name="training_name" required autofocus>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                <div class="form-group">
                                    <label>Description <span class="required-asterisk">*</span></label>
                                    <textarea class="form-control" name="description" rows="2" required></textarea>
                                </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Venue</label>
                                        <input type="text" class="form-control" name="location">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Start Date <span class="required-asterisk">*</span></label>
                                        <input type="date" class="form-control" name="start_date" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>End Date <span class="required-asterisk">*</span></label>
                                        <input type="date" class="form-control" name="end_date" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Training Hours <span class="required-asterisk">*</span></label>
                                        <input type="number" class="form-control" name="training_hrs" placeholder="e.g., 8, 16, 24" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-12 text-right">
                                    <a href="courseManager.php" class="btn btn-outline-secondary rounded-0">Back</a> 
                                    <button type="submit" class="btn btn-info rounded-0">Save</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </section>
        </div>

        <?php include '../footer.php'; ?>
    </div>

    <script src="adminlte/plugins/jquery/jquery.min.js"></script>
    <script src="adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="adminlte/dist/js/adminlte.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.1/dist/js/adminlte.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    <?php
     if (isset($_SESSION['success'])) {
        echo "Swal.fire({
            title: 'Success!',
            text: '" . $_SESSION['success'] . "',
            icon: 'success',
            confirmButtonText: 'OK'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'training.php'; // Redirect after SweetAlert is closed
            }
        });";
        unset($_SESSION['success']); // Remove the message after displaying
    }

    if (isset($_SESSION['error'])) {
        echo "Swal.fire({
            title: 'Error!',
            text: '" . $_SESSION['error'] . "',
            icon: 'error',
            confirmButtonText: 'OK'
        });";
        unset($_SESSION['error']); // Remove the message after displaying
    }
    ?>
</script>

</body>
</html>
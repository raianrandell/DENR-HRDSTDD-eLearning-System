<?php
session_start(); // Only once, at the very top
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Free Training</title>
    <link rel="stylesheet" href="adminlte/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="adminlte/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.1/dist/css/adminlte.min.css">
    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
        <section class="content">
            <br>
            <div class="card">
                <div class="card-header warning-gradient">
                    <h3 class="card-title my-2 float-left">
                        <i class="fas fa-plus mr-2"></i> Create New Free Training
                    </h3>
                    <div class="float-right">
                    </div>
                    <div class="clearfix"></div>
                </div>

                <div class="card-body">
                    <!-- Form Start -->
                    <form id="createTrainingForm" method="post" action="processCreateFreeTraining.php" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="trainingTitle">Training Title</label>
                                    <input type="text" class="form-control" id="trainingTitle" name="trainingTitle" required>
                                </div>
                                <div class="form-group">
                                    <label for="trainingDescription">Description</label>
                                    <textarea class="form-control" id="trainingDescription" name="trainingDescription" rows="3" required></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="trainingLink">Training Link</label>
                                    <input type="url" class="form-control" id="trainingLink" name="trainingLink">
                                </div>
                                <div class="form-group">
                                    <label for="trainingHrs">Training Hours</label>
                                    <input type="number" class="form-control" id="trainingHrs" name="trainingHrs" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="trainingPhoto">Training Photo</label>
                                    <div class="input-group">
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" id="trainingPhoto" name="trainingPhoto">
                                            <label class="custom-file-label" for="trainingPhoto">Choose file</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="startDate">Start Date</label>
                                    <input type="date" class="form-control" id="startDate" name="startDate" required>
                                </div>
                                <div class="form-group">
                                    <label for="endDate">End Date</label>
                                    <input type="date" class="form-control" id="endDate" name="endDate" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-group float-right">
                            <a href="free_trainings.php" class="btn btn-outline-secondary rounded-0">Back</a>
                            <button type="submit" class="btn btn-info rounded-0">Save</button>
                        </div>
                    </form>
                    <!-- Form End -->
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
<script src="adminlte/bs-custom-file-input/bs-custom-file-input.min.js"></script>
<script>
$(function () {
  bsCustomFileInput.init();
});

$(document).ready(function () {
    // Display SweetAlert2 messages
    <?php if (isset($_SESSION['success'])) : ?>
    Swal.fire({
        icon: 'success',
        title: 'Success',
        text: '<?php echo $_SESSION['success']; ?>',
        confirmButtonColor: '#3085d6',
        confirmButtonText: 'OK'
    }).then(() => {
        <?php unset($_SESSION['success']); ?>
    });
    <?php endif; ?>

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
</body>
</html>
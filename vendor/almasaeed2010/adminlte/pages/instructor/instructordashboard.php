<?php
session_start();
if (!isset($_SESSION['instructor_id'])) {
    header("Location: instructorLogin.php");
    exit();
}

$instructorID = $_SESSION['instructor_id'];

include '../includes/config.php';

// Function to get training assigned to an instructor and display as a grid (3 per row)
function getInstructorTraining($instructorID, $conn) {
    $sql = "SELECT t.training_id, t.training_title, t.description, t.start_date, t.end_date
            FROM training t
            JOIN training_instructors ti ON t.training_id = ti.training_id
            WHERE ti.instructor_id = ?
            ORDER BY t.start_date DESC"; // Added ORDER BY clause
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $instructorID);
    $stmt->execute();
    $result = $stmt->get_result();

    $trainings = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $trainings[] = $row;
        }
    }
    return $trainings;
}

$assignedTrainings = getInstructorTraining($instructorID, $conn);

// Function to count assigned trainings (already have this count implicitly)
function countAssignedTrainings($trainings) {
    return count($trainings);
}

// Function to count ongoing trainings
function countOngoingTrainings($trainings) {
    $ongoingCount = 0;
    $currentDate = date("Y-m-d");
    foreach ($trainings as $training) {
        if ($training['start_date'] <= $currentDate && $training['end_date'] >= $currentDate) {
            $ongoingCount++;
        }
    }
    return $ongoingCount;
}

// Function to count completed trainings
function countCompletedTrainings($trainings) {
    $completedCount = 0;
    $currentDate = date("Y-m-d");
    foreach ($trainings as $training) {
        if ($training['end_date'] < $currentDate) {
            $completedCount++;
        }
    }
    return $completedCount;
}

$totalAssigned = countAssignedTrainings($assignedTrainings);
$ongoingTrainingsCount = countOngoingTrainings($assignedTrainings);
$completedTrainingsCount = countCompletedTrainings($assignedTrainings);

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instructor Dashboard</title>
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="adminlte/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="adminlte/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.1/dist/css/adminlte.min.css">
    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">

    <style>
        a.text-primary {
            text-decoration: none; /* Removes underline by default */
        }

        a.text-primary:hover {
            text-decoration: underline; /* Underline appears on hover */
        }

        
        .info-gradient {
            background: linear-gradient(225deg, #98D2C0, #17A2B8, #205781);
   }
    </style>
</head>
<body class="hold-transition sidebar-mini">
<div class="preloader flex-column justify-content-center align-items-center">
    <img class="animation__shake rounded-circle" src="../../dist/img/denrlogo.jpg" alt="denrlogo" height="100" width="100">
</div>

    <div class="wrapper">
        <!-- Navbar -->
        <?php include 'navbar.php'; ?>

        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>

        <!-- Content Wrapper -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                        </div><!-- /.col -->
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="#">Home</a></li>
                                <li class="breadcrumb-item active">Dashboard</li>
                            </ol>
                        </div><!-- /.col -->
                    </div><!-- /.row -->
                </div><!-- /.container-fluid -->
            </div>
            <!-- /.content-header -->

            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">
                    <!-- Small boxes (Stat box) -->
                    <div class="row">
                        <div class="col-lg-4 col-6">
                            <!-- small box -->
                            <div class="small-box info-gradient">
                                <div class="inner text-white">
                                    <h3><?= $totalAssigned ?></h3>
                                    <p>Assigned Trainings</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-graduation-cap"></i>
                                </div>
                                <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                            </div>
                        </div>
                        <!-- ./col -->
                        <div class="col-lg-4 col-6">
                            <!-- small box -->
                            <div class="small-box info-gradient">
                                <div class="inner text-white">
                                    <h3><?= $ongoingTrainingsCount ?></h3>
                                    <p>Finished Trainings</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-spinner"></i>
                                </div>
                                <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                            </div>
                        </div>
                        <!-- ./col -->
                        <div class="col-lg-4 col-6">
                            <!-- small box -->
                            <div class="small-box info-gradient">
                                <div class="inner text-white">
                                    <h3><?= $completedTrainingsCount ?></h3>
                                    <p>Overall Trainings</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                            </div>
                        </div>
                        <!-- ./col -->
                    </div>
                    <!-- /.row -->

                    <!-- Assigned Trainings Section -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card card-info card-outline">
                                <div class="card-header">
                                    <h3 class="card-title">Assigned Trainings</h3>
                                </div>
                                <!-- /.card-header -->
                                <div class="card-body">
                                    <div class="row">
                                        <?php if (empty($assignedTrainings)): ?>
                                            <div class="col-12">
                                                <div class="callout callout-warning text-center">
                                                    <h5><i class="fas fa-exclamation-circle"></i> No Training Assigned</h5>
                                                    <p>You have not been assigned any training yet.</p>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <?php foreach ($assignedTrainings as $training): ?>
                                                <div class="col-md-4 mb-4">
                                                    <div class="card bg-light shadow-md">
                                                        <div class="card-header text-truncate info-gradient text-white">
                                                            <h5 class="card-title font-weight-bold"><?= htmlspecialchars($training['training_title']) ?></h5>
                                                        </div>
                                                        <div class="card-body">
                                                            <p class="card-text text-truncate"><?= htmlspecialchars($training['description']) ?></p>
                                                            <p class="card-text"><small class="text-muted">
                                                                Duration: <?= date("F j, Y", strtotime($training['start_date'])) . ' to ' . date("F j, Y", strtotime($training['end_date'])) ?>
                                                            </small></p>
                                                            <a href="trainingDetails.php?trainingID=<?= $training['training_id'] ?>" class="btn btn-outline-info btn-md rounded-0">
                                                                View Details <i class="fas fa-arrow-circle-right ml-1"></i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                    <!-- /.row -->
                                </div>
                                <!-- /.card-body -->
                            </div>
                            <!-- /.card -->
                        </div>
                        <!-- /.col -->
                    </div>
                    <!-- /.row -->
                </div><!-- /.container-fluid -->
            </section>
            <!-- /.content -->
        </div>
        <!-- /.content-wrapper -->

        <!-- Footer -->
        <?php include '../footer.php'; ?>
    </div>
    <!-- ./wrapper -->

    <!-- AdminLTE JS -->
    <script src="adminlte/plugins/jquery/jquery.min.js"></script>
    <script src="adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="adminlte/dist/js/adminlte.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.1/dist/js/adminlte.min.js"></script>
    <script src="adminlte/plugins/select2/js/select2.full.min.js"></script>
</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <!-- Bootstrap 4 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    
    <!-- AdminLTE 3 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free/css/all.min.css">

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">

    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">

    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.min.css">

    <?php
    session_start();

    // Check if instructorID is set
    if (!isset($_SESSION["participant_id"])) {
        header("Location: participantlogin.php"); // Redirect to login if not set
        exit();
    }

    include '../includes/config.php';

    // SQL query to count the number of trainings with the top grade(not yet editted -Z)
    $sql_trainings = "SELECT COUNT(*) AS total_trainings FROM training";
    $result_trainings = $conn->query($sql_trainings);
    $total_trainings = 0; // Default to 0 if no records are found
    if ($result_trainings->num_rows > 0) {
        $row = $result_trainings->fetch_assoc();
        $total_trainings = $row['total_trainings'];
    }

    // SQL query to count the total trainings taken(not yet editted -Z)
    $sql_instructors = "SELECT COUNT(*) AS total_instructors FROM user_instructors";
    $result_instructors = $conn->query($sql_instructors);
    $total_instructors = 0; // Default to 0 if no records are found
    if ($result_instructors->num_rows > 0) {
        $row = $result_instructors->fetch_assoc();
        $total_instructors = $row['total_instructors'];
    }
    // Close the connection
    $conn->close();
    ?>
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
            <section class="content">
                <div class="container-fluid">
                    <!-- New Div that takes available space, with lighter gray, border radius, and shadow -->
                    <div class="row" style="min-height: 600px; background-color: #f8f9fa; border-radius: 15px; box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1); padding: 20px;">
                        <div class="col-12">
                            <?php include 'certTable.php' ?>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <!-- Footer -->
        <?php include '../footer.php'; ?>
    </div>

<!-- jQuery -->
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>

<!-- AdminLTE 3 -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

<!-- DataTables -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.all.min.js"></script>


</body>
</html>
<?php
// report_participants_with_training.php
session_start();
// Ensure user is authenticated and authorized if necessary
// if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
//     header('Location: login.php'); // Redirect to login page
//     exit;
// }

include '../includes/config.php'; // Contains database connection ($conn)

$participants_with_training = []; // Initialize as empty array
$error_message = null; // Initialize error message

// Check if the connection ($conn) was successful
if ($conn && !$conn->connect_error) {
    try {
        // --- Database Query using mysqli ---
        // Modified WHERE clause:
        // 1. INNER JOIN ensures they are linked to *some* training in training_participants
        // 2. WHERE p.status = 'Active' filters for participants whose account is 'Active'
        // 3. WHERE p.in_training = 1 filters for participants marked as currently 'In Training' (based on TINYINT 1)
            $sql = "SELECT * FROM user_participants WHERE in_training = 1;
";

        // Execute the query using the $conn object
        $result = $conn->query($sql);

        // Check if the query was successful
        if ($result === false) {
            // Query failed
            throw new Exception("Database query failed: " . $conn->error); // Throw an exception
        }

        // Check if any rows were returned
        if ($result->num_rows > 0) {
            // Fetch all results into the array
            while ($row = $result->fetch_assoc()) {
                $participants_with_training[] = $row;
            }
        }
        // No else needed here, $participants_with_training remains empty if no rows

        // Free the result set
        $result->free();

    } catch (Exception $e) { // Catch potential exceptions (like the one thrown above)
        // Handle database errors (log the error, display a user-friendly message)
        error_log("Database Error in report_participants_with_training.php: " . $e->getMessage());
        $error_message = "Error fetching participant data. Please contact support or check logs.";
        // In a production environment, avoid showing specific database errors to the user
        // $error_message = "An error occurred while fetching data.";
    }
    // mysqli connection doesn't strictly need explicit closing at the end of script execution
    // unless you have specific requirements or long-running scripts. PHP handles it.
    // $conn->close();

} else {
    // This case should ideally not be reached if your config.php uses die() on failure,
    // but it's good defensive programming.
    $error_message = "Database connection is not available.";
    error_log("Error in report_participants_with_training.php: \$conn object not available or connection error.");
}

// --- HTML Structure ---
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Report: Participants with Training</title>
    <!-- Include AdminLTE CSS, Font Awesome, etc. -->
    <link rel="stylesheet" href="../../plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="../../plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="../../plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
    <!-- Add DataTables Buttons CSS -->
    <link rel="stylesheet" href="../../plugins/datatables-buttons/css/buttons.bootstrap4.min.css">

    <link rel="stylesheet" href="../../dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">

</head>
<body class="hold-transition sidebar-mini">
<div class="preloader flex-column justify-content-center align-items-center">
    <img class="animation__shake rounded-circle" src="../../dist/img/denrlogo.jpg" alt="denrlogo" height="100" width="100">
</div>
<div class="wrapper">

<!-- Navbar -->
<?php
// Check if the include file exists before including
$navbar_path = 'navbar.php';
if (file_exists($navbar_path)) {
    include($navbar_path);
} else {
    echo "Navbar include not found at $navbar_path.<br>";
}
?>
<!-- /.navbar -->

<!-- Main Sidebar Container -->
<?php
// Check if the include file exists before including
$sidebar_path = 'sidebar.php'; // Assuming sidebar.php is in the same directory
if (file_exists($sidebar_path)) {
    include($sidebar_path);
} else {
    echo "Sidebar include not found at $sidebar_path.<br>";
}
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Report: Participants with Training</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="admindashboard.php">Home</a></li>
                        <li class="breadcrumb-item active">Reports</li>
                        <li class="breadcrumb-item active">Participants with Training</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">List of active participants currently enrolled in at least one training</h3>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <?php if ($error_message): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
                    <?php else: ?>
                        <table id="participantsTable" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Office</th>
                                    <th>Position</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($participants_with_training)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center">No active participants found currently marked as "In Training".</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($participants_with_training as $participant): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($participant['last_name'] . ', ' . $participant['first_name'] . (!empty($participant['middle_name']) ? ' ' . $participant['middle_name'] : '')); ?></td>
                                            <td><?php echo htmlspecialchars($participant['email']); ?></td>
                                            <td><?php echo htmlspecialchars($participant['office'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($participant['position'] ?? 'N/A'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
                <!-- /.card-body -->
            </div>
            <!-- /.card -->
        </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->

<!-- Footer -->
<?php
// Check if the include file exists before including
$footer_path = '../footer.php'; // Assuming footer.php is one directory up
if (file_exists($footer_path)) {
    include($footer_path);
} else {
    echo "Footer include not found at $footer_path.<br>";
}
?>
<!-- /.Footer -->

 <!-- Control Sidebar -->
 <aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
 </aside>
 <!-- /.control-sidebar -->

</div>
<!-- ./wrapper -->

<!-- REQUIRED SCRIPTS -->
<!-- jQuery -->
<script src="../../plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="../../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- DataTables & Plugins -->
<script src="../../plugins/datatables/jquery.dataTables.min.js"></script>
<script src="../../plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="../../plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="../../plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>

<!-- Add DataTables Buttons JS -->
<script src="../../plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
<script src="../../plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
<!-- For export formats -->
<script src="../../plugins/jszip/jszip.min.js"></script> <!-- Required for Excel -->
<script src="../../plugins/pdfmake/pdfmake.min.js"></script>
<script src="../../plugins/pdfmake/vfs_fonts.js"></script>
<script src="../../plugins/datatables-buttons/js/buttons.html5.min.js"></script> <!-- Includes PDF, CSV -->
<script src="../../plugins/datatables-buttons/js/buttons.print.min.js"></script>
<script src="../../plugins/datatables-buttons/js/buttons.colVis.min.js"></script>


<!-- AdminLTE App -->
<script src="../../dist/js/adminlte.min.js"></script>

<!-- Page specific script -->
<script>
  $(function () {
    $('#participantsTable').DataTable({
      "paging": true,
      "lengthChange": true, // Enable length change
      "searching": true,    // Enable Search
      "ordering": true,
      "info": true,
      "autoWidth": false,
      "responsive": true,
      "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
    }).buttons().container().appendTo('#participantsTable_wrapper .col-md-6:eq(0)'); // Place buttons in the top-left
  });
</script>

</body>
</html>
<?php
// report_participants_without_training.php

// Start session if needed, perform authentication checks, include config files
session_start();
include '../includes/config.php';

// --- Database Query using mysqli ---
$participants_without_training = []; // Initialize as empty array
$error_message = null; // Initialize error message

// Check if the connection ($conn) was successful
if ($conn && !$conn->connect_error) {
    try {
        // SQL query to find participants NOT in the training_participants table
        // Using LEFT JOIN / IS NULL method
        $sql = "SELECT * FROM user_participants WHERE in_training = 0;";

        // Alternative using NOT EXISTS (sometimes more efficient on large tables)
        /*
        $sql = "SELECT p.participant_id, p.first_name, p.middle_name, p.last_name, p.email, p.office, p.position
                FROM user_participants p
                WHERE NOT EXISTS (
                    SELECT 1
                    FROM training_participants tp
                    WHERE tp.participant_id = p.participant_id
                )
                AND p.status = 'Active' -- Optional: Only show active participants
                ORDER BY p.last_name, p.first_name";
        */

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
                $participants_without_training[] = $row;
            }
        }
        // No else needed here, $participants_without_training remains empty

        // Free the result set
        $result->free();

    } catch (Exception $e) { // Catch potential exceptions
        // Handle database errors
        error_log("Database Error in report_participants_without_training.php: " . $e->getMessage());
        $error_message = "Error fetching participant data. Please contact support or check logs.";
        // $error_message = "An error occurred while fetching data."; // User-friendly in production
    }
    // Optional: Close connection if not needed later in the script/footer
    // $conn->close();

} else {
    // Connection failed (shouldn't happen if connection file uses die())
    $error_message = "Database connection is not available.";
    error_log("Error in report_participants_without_training.php: \$conn object not available or connection error.");
}

// --- HTML Structure ---
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Report: Participants without Training</title>
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
<?php include('navbar.php'); // Replace with your actual navbar include if necessary ?>
<!-- /.navbar -->

<!-- Main Sidebar Container -->
<?php include('sidebar.php'); // Include the sidebar (adjust path if needed) ?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Report: Participants without Training</h1>
                </div>
                 <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="admindashboard.php">Home</a></li>
                        <li class="breadcrumb-item active">Reports</li>
                        <li class="breadcrumb-item active">Participants without Training</li>
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
                    <h3 class="card-title">List of all active participants not currently enrolled in any training</h3>
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
                                <?php if (empty($participants_without_training)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center">All active participants are enrolled in at least one training, or no active participants found.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($participants_without_training as $participant): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($participant['last_name'] . ', ' . $participant['first_name'] . (!empty($participant['middle_name']) ? ' ' . $participant['middle_name'] : '')); ?></td>
                                            <td><?php echo htmlspecialchars($participant['email']); ?></td>
                                            <td><?php htmlspecialchars($participant['office'] ?? 'N/A'); ?></td>
                                            <td><?php htmlspecialchars($participant['position'] ?? 'N/A'); ?></td>
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
<?php include('../footer.php'); // Replace with your actual footer include if necessary ?>
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
<!-- For PDF export -->
<script src="../../plugins/jszip/jszip.min.js"></script> <!-- Required for Excel, often included -->
<script src="../../plugins/pdfmake/pdfmake.min.js"></script>
<script src="../../plugins/pdfmake/vfs_fonts.js"></script>
<script src="../../plugins/datatables-buttons/js/buttons.html5.min.js"></script> <!-- Includes PDF -->
<script src="../../plugins/datatables-buttons/js/buttons.print.min.js"></script>
<script src="../../plugins/datatables-buttons/js/buttons.colVis.min.js"></script>

<!-- AdminLTE App -->
<script src="../../dist/js/adminlte.min.js"></script>

<!-- Page specific script -->
<script>
  $(function () {
    $('#participantsTable').DataTable({
      "paging": true,
      "lengthChange": true,
      "searching": true,
      "ordering": true,
      "info": true,
      "autoWidth": false,
      "responsive": true,
      // Enable DataTables Buttons
      "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
    }).buttons().container().appendTo('#participantsTable_wrapper .col-md-6:eq(0)'); // Place buttons in the top-left
  });
</script>

</body>
</html>
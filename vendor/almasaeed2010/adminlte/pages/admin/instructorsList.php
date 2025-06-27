<?php
session_start();
require '../includes/config.php'; // Database connection

// Fetch courses for the dropdown filter
$trainingQuery = "SELECT training_id, training_title FROM training ORDER BY training_title ASC";
$trainingResult = $conn->query($trainingQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subject Matter Expert List</title>
    
    <link rel="stylesheet" href="adminlte/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="adminlte/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.1/dist/css/adminlte.min.css">
    
    <!-- DataTables -->
    <link rel="stylesheet" href="../../plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="../../plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="../../plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">   
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="hold-transition sidebar-mini">
<div class="preloader flex-column justify-content-center align-items-center">
    <img class="animation__shake rounded-circle" src="../../dist/img/denrlogo.jpg" alt="denrlogo" height="100" width="100">
</div>
<?php
if (isset($_SESSION['success'])) {
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Success!',
                text: '" . $_SESSION['success'] . "',
                icon: 'success',
                confirmButtonText: 'OK'
            });
        });
    </script>";
    unset($_SESSION['success']); // Clear session message after displaying it
}
?>

<div class="wrapper">
    <?php include 'navbar.php'; ?>
    <?php include 'sidebar.php'; ?>

    <div class="content-wrapper">
    <section class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1></h1>
                        </div>
                        <div class="col-sm-6 text-right">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="training.php">Trainings</a></li>
                                <li class="breadcrumb-item active">List of Subject Matter Expert</li>
                            </ol>
                        </div>
                    </div>
                </div>
    </section>
        <section class="content">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title"><i class="fas fa-users mr-2"></i>List of Subject Matter Expert Assign in Training</h3>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <div class="mb-3 d-flex align-items-center">
                            <label for="trainingFilter" class="mr-2">Filter by Training Title:</label>
                            <select id="trainingFilter" class="form-control w-25 mr-2">
                                <option value="">All</option>
                                <?php while ($trainingRow = $trainingResult->fetch_assoc()): ?>
                                    <option value="<?= htmlspecialchars($trainingRow['training_title']) ?>">
                                        <?= htmlspecialchars($trainingRow['training_title']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                            <button id="resetFilter" class="btn btn-outline-secondary">Reset Filter</button>
                        </div>

                        <table id="traineesTable" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Full Name</th>
                                    <th>Email</th>
                                    <th>Training Title</th>
                                    <th>Office</th>
                                    <th>Position/Designation</th>
                                    <th>Assigned Date</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                                // Fetch Subject Matter Expert data with course details
                                $instructorQuery = "
                                    SELECT 
                                        ui.instructor_id,
                                        CONCAT(ui.first_name, ' ', IFNULL(ui.middle_name, ''), ' ', ui.last_name) AS full_name,
                                        ui.email,
                                        t.training_title,
                                        ui.office,
                                        ui.position,
                                        ti.assigned_date
                                    FROM 
                                        user_instructors ui
                                    JOIN 
                                        training_instructors ti ON ui.instructor_id = ti.instructor_id
                                    JOIN 
                                        training t ON ti.training_id = t.training_id
                                    ORDER BY ti.assigned_date ASC
                                ";

                                $instructorResult = $conn->query($instructorQuery);

                                if ($instructorResult->num_rows > 0) {
                                    $counter = 1; // Row counter
                                    while ($row = $instructorResult->fetch_assoc()) {
                                        echo "<tr>
                                                <td>{$counter}</td>
                                                <td>" . htmlspecialchars($row['full_name']) . "</td>
                                                <td>" . htmlspecialchars($row['email']) . "</td>
                                                <td>" . htmlspecialchars($row['training_title']) . "</td>
                                                <td>" . htmlspecialchars($row['office']) . "</td>
                                                <td>" . htmlspecialchars($row['position']) . "</td>
                                               <td>" . date('m-d-Y h:i:s a', strtotime($row['assigned_date'])) . "</td>
                                            </tr>";
                                        $counter++;
                                    }
                                } else {
                                    echo "<tr><td colspan='7' class='text-center'>No Subject Matter Expert found.</td></tr>";
                                }
                                ?>

                            </tbody>
                        </table>
                    </div>
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

<script>
$(document).ready(function () {
    var table = $("#traineesTable").DataTable({
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false
    });

    // Filter table based on selected course
    $("#trainingFilter").on("change", function () {
        var selectedCourse = $(this).val();
        table.column(3).search(selectedCourse).draw(); // Column 3 is Course Title
    });

    // Reset filter
    $("#resetFilter").on("click", function () {
        $("#trainingFilter").val(""); // Reset dropdown
        table.column(3).search("").draw(); // Reset search
    });
});
</script>

</body>
</html>

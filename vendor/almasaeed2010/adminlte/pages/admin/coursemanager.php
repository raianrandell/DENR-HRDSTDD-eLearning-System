<?php
session_start();
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


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Managers List</title>
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
        <!-- Content Header (Page header) -->
        <br>

        <section class="content">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title"><i class="fas fa-users mr-2"></i>Course Managers</h3>
                    <a href="create_course_manager.php" class="btn btn-outline-warning ml-auto rounded-0"
                       title="Add a new course manager">
                        <i class="fas fa-user-plus"></i> Create New Course Manager
                    </a>
                </div>

                <div class="card-body">
                    <div class="table-responsive">

                        <table id="courseManagersTable" class="table table-bordered table-striped">
                            <thead class="warning-gradient">
                            <tr>
                                <th>#</th>
                                <th>Full Name</th>
                                <th>Office</th>
                                <th>Position</th>
                                <th>Username</th>
                                <th>Created At</th>
                                <th>Updated At</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            require '../includes/config.php';

                            // Fetch course managers from the database
                            $sql = "SELECT * FROM user_course_manager";
                            $result = $conn->query($sql);

                            if ($result->num_rows > 0) {
                                // Initialize counter
                                $counter = 1;

                                // Output data of each row
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . $counter . "</td>"; // Use the counter
                                    // Concatenate last name, first name, and middle name
                                    $fullName = $row["last_name"] . ", " . $row["first_name"] . " " . $row["middle_name"];
                                    echo "<td>" . htmlspecialchars($fullName) . "</td>"; // Display full name and escape for HTML
                                    echo "<td>" . htmlspecialchars($row["office"]) . "</td>"; // Escape for HTML
                                    echo "<td>" . htmlspecialchars($row["position"]) . "</td>"; // Escape for HTML
                                    echo "<td>" . htmlspecialchars($row["username"]) . "</td>"; // Escape for HTML
                                    echo "<td>" . htmlspecialchars($row["created_at"]) . "</td>"; // Escape for HTML
                                    echo "<td>" . htmlspecialchars($row["updated_at"]) . "</td>"; // Escape for HTML
                                    echo "<td>
                                                <div class='btn-group'>
                                                    <button type='button' class='btn btn-outline-secondary btn-sm dropdown-toggle ml-2' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>
                                                        <i class='fas fa-cog'></i>
                                                    </button>
                                                    <div class='dropdown-menu dropdown-menu-right'>
                                                        <a class='dropdown-item edit-user-btn' href='edit_course_manager.php?id=" . $row["id"] . "'>
                                                            <i class='fas fa-edit mr-2'></i>Edit
                                                        </a>
                                                        <a class='dropdown-item delete-user-btn' href='#' data-id='" . $row["id"] . "'>
                                                            <i class='fas fa-user-slash mr-2'></i>Disable
                                                        </a>
                                                    </div>
                                                </div>
                                            </td>";

                                    echo "</tr>";  //Important: Closing the row here.
                                    $counter++; // Increment the counter
                                }
                            } else {
                                echo "<tr><td colspan='9'>No course managers found</td></tr>"; // Corrected colspan value
                            }

                            $conn->close();
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

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function () {
        var table = $("#courseManagersTable").DataTable({
            "responsive": true,
            "lengthChange": true,
            "autoWidth": false,
        });

        // Delete Functionality
        $('#courseManagersTable').on('click', '.delete-btn', function () {
            var id = $(this).data('id');
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'delete_course_manager.php',
                        type: 'POST',
                        data: { id: id },
                        success: function (response) {
                            if (response === 'success') {
                                Swal.fire(
                                    'Deleted!',
                                    'The course manager has been deleted.',
                                    'success'
                                ).then(() => {
                                    location.reload(); // Refresh the page
                                });
                            } else {
                                Swal.fire(
                                    'Error!',
                                    'Failed to delete the course manager.',
                                    'error'
                                );
                            }
                        }
                    });
                }
            });
        });
    });

</script>
</body>
</html>
<?php
session_start();
include '../includes/config.php'; // Database connection

if (!isset($_GET['training_id']) || !is_numeric($_GET['training_id'])) {
    $_SESSION['error'] = "Invalid training ID.";
    header("Location: assignInstructors.php"); // Redirect back to trainings page
    exit();
}
$training_id = $_GET['training_id'];

// Fetch training Details (Optional - for display)
$training_sql = "SELECT training_title FROM training WHERE training_id = ?";
$training_stmt = $conn->prepare($training_sql);
$training_stmt->bind_param("i", $training_id);
$training_stmt->execute();
$training_result = $training_stmt->get_result();
if ($training_result->num_rows === 0) {
    $_SESSION['error'] = "training not found.";
    header("Location: assignInstructors.php");
    exit();
}
$training = $training_result->fetch_assoc();
$training_title = $training['training_title'];
$training_stmt->close();


$instructors_sql = "
    SELECT ui.instructor_id, ui.first_name, ui.last_name, ui.email 
    FROM user_instructors ui
    WHERE ui.status = 'Active'
    AND ui.instructor_id NOT IN (
        SELECT instructor_id 
        FROM training_instructors 
        WHERE training_id = ?
    )
";
$instructors_stmt = $conn->prepare($instructors_sql);
$instructors_stmt->bind_param("i", $training_id);
$instructors_stmt->execute();
$instructors_result = $instructors_stmt->get_result();


?>
<!DOCTYPE html>
<html>
<head>
    <title>Assign Instructors to Training: <?php echo htmlspecialchars($training_title); ?></title>
      <!-- Include your CSS and JS links (AdminLTE, DataTables, etc.) -->
    <link rel="stylesheet" href="adminlte/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="adminlte/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.1/dist/css/adminlte.min.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="../../plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="../../plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="../../plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
</head>
<body class="hold-transition sidebar-mini">
<div class="preloader flex-column justify-content-center align-items-center">
    <img class="animation__shake rounded-circle" src="../../dist/img/denrlogo.jpg" alt="denrlogo" height="100" width="100">
</div>
<div class="wrapper">
    <?php include 'navbar.php'; ?>
    <?php include 'sidebar.php'; ?>

    <div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Assign Instructors</h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="admindashboard.php">Home</a></li>
                        <li class="breadcrumb-item"><a href="training.php">Trainings</a></li>
                        <li class="breadcrumb-item active">Assign Instructors</li>
                    </ol>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->
        <section class="content">
            <br>
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Assign Instructors to training: <strong><?php echo htmlspecialchars($training_title); ?></strong></h3>
                </div>
                <div class="card-body">
                    <form id="assignInstructorsForm" action="processAssignInstructors.php" method="post">
                        <input type="hidden" name="training_id" value="<?php echo $training_id; ?>">
                        <table id="instructorsTable" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="select-all-instructors"></th>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if ($instructors_result->num_rows > 0) {
                                    $i = 1;
                                    while ($instructor = $instructors_result->fetch_assoc()) {
                                        echo "<tr>
                                            <td><input type='checkbox' name='instructor_ids[]' value='{$instructor['instructor_id']}'></td>
                                            <td>{$i}</td>
                                            <td>{$instructor['first_name']} {$instructor['last_name']}</td>
                                            <td>{$instructor['email']}</td>
                                        </tr>";
                                        $i++;
                                    }
                                } else {
                                    echo "<tr><td colspan='4' class='text-center'>No instructors available.</td></tr>";
                                }
                                ?>    
                            </tbody>
                        </table>
                        <a href="training.php" class="btn btn-outline-secondary rounded-0 mt-2">Cancel</a>
                        <button type="submit" class="btn btn-info rounded-0 ml-2 mt-2">Save</button>
                      
                    </form>
                </div>
            </div>
        </section>
    </div>

    <?php include '../footer.php'; ?>
</div>
<!-- Include your JS files (jQuery, Bootstrap, AdminLTE, DataTables, SweetAlert2) -->
<script src="adminlte/plugins/jquery/jquery.min.js"></script>
<script src="adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="adminlte/dist/js/adminlte.min.js"></script>
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
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.1/dist/js/adminlte.min.js"></script>
<script>
    $(document).ready(function () {
        $("#instructorsTable").DataTable({
            "responsive": true, "lengthChange": true, "autoWidth": false,
        });

        // Handle "Select All" checkbox for instructors
        $('#select-all-instructors').click(function(event) { // IMPORTANT: Changed selector ID
            if(this.checked) {
                $(':checkbox', '#instructorsTable').each(function() {
                    this.checked = true;
                });
            } else {
                $(':checkbox', '#instructorsTable').each(function() {
                    this.checked = false;
                });
            }
        });

              // Display SweetAlert2 messages (if needed - you can handle success/error on process page)
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
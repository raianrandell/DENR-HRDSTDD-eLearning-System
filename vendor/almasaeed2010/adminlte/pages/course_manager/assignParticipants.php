<?php
session_start();
include '../includes/config.php'; // Database connection

if (!isset($_GET['training_id']) || !is_numeric($_GET['training_id'])) {
    $_SESSION['error'] = "Invalid training ID.";
    header("Location: training.php"); // Redirect back to trainings page
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
    $_SESSION['error'] = "Training not found.";
    header("Location: training.php");
    exit();
}
$training = $training_result->fetch_assoc();
$training_title = $training['training_title'];
$training_stmt->close();

// Fetch Distinct Offices
$offices_sql = "SELECT DISTINCT office FROM user_participants 
                WHERE status = 'Active' 
                AND in_training != 1 
                AND office IS NOT NULL 
                AND office != '' 
                ORDER BY office ASC";
$offices_result = $conn->query($offices_sql);

// Fetch Participants
// Fetch Participants
$participants_sql = "
    SELECT up.participant_id, up.first_name, up.last_name, up.email, up.office
    FROM user_participants up
    WHERE up.status = 'Active' 
    AND up.in_training != 1
    AND up.participant_id NOT IN (
        SELECT participant_id 
        FROM training_participants 
        WHERE training_id = ?
    )
";

$participants_stmt = $conn->prepare($participants_sql);
$participants_stmt->bind_param("i", $training_id);
$participants_stmt->execute();
$participants_result = $participants_stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Assign Participants to training: <?php echo htmlspecialchars($training_title); ?></title>
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
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Assign Participants</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="admindashboard.php">Home</a></li>
                        <li class="breadcrumb-item"><a href="training.php">Trainings</a></li>
                        <li class="breadcrumb-item active">Assign Participants</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <br>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Assign Participants to training: <strong><?php echo htmlspecialchars($training_title); ?></strong></h3>
            </div>
            <div class="card-body">
                
                <!-- Office Filter Dropdown -->
                <div class="mb-3">
                    <label for="officeFilter">Filter by Office:</label>
                    <select id="officeFilter" class="form-control w-25">
                        <option value="">All Offices</option>
                        <?php
                        if ($offices_result->num_rows > 0) {
                            while ($office_row = $offices_result->fetch_assoc()) {
                                $office_name = htmlspecialchars($office_row['office']);
                                echo "<option value='{$office_name}'>{$office_name}</option>";
                            }
                        }
                        ?>
                    </select>
                </div>

                <form id="assignParticipantsForm" action="processAssignParticipants.php" method="post">
                    <input type="hidden" name="training_id" value="<?php echo $training_id; ?>">
                    <table id="participantsTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="select-all"></th>
                                <th>#</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Office</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($participants_result->num_rows > 0) {
                                $i = 1;
                                while ($participant = $participants_result->fetch_assoc()) {
                                    echo "<tr>
                                    <td><input type='checkbox' name='participant_ids[]' value='{$participant['participant_id']}'></td>
                                    <td>{$i}</td>
                                    <td>{$participant['first_name']} {$participant['last_name']}</td>
                                    <td>{$participant['email']}</td>
                                    <td>{$participant['office']}</td>
                                </tr>";
                                    $i++;
                                }
                            } else {
                                echo "<tr><td colspan='5' class='text-center'>No participants available.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                    <a href="courseManager.php" class="btn btn-outline-secondary rounded-0">Cancel</a>
                    <button type="submit" class="btn btn-info rounded-0 ml-2">Save</button>
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
        var table = $("#participantsTable").DataTable({
            "responsive": true,
            "lengthChange": true,
            "autoWidth": true,
        });

        // Select All Checkbox
        $('#select-all').click(function() {
            $(':checkbox', '#participantsTable').prop('checked', this.checked);
        });

        // Office Filter Dropdown
        $('#officeFilter').on('change', function () {
            var office = $(this).val();
            table.columns(4).search(office).draw();
        });

        // SweetAlert2 Notifications
        <?php if (isset($_SESSION['success'])) : ?>
        Swal.fire('Success', '<?php echo $_SESSION['success']; ?>', 'success');
        <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
    });
</script>
</body>
</html>

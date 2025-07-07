<?php
session_start();
include '../includes/config.php';
if (!isset($_SESSION['instructor_id'])) {
    header("Location: instructorLogin.php");
    exit();
}

if (!isset($_GET['participant_id']) || !isset($_GET['training_id'])) {
    echo "Error: Participant ID or Training ID missing.";
    exit();
}

$participant_id = $_GET['participant_id'];
$training_id = $_GET['training_id'];

// Fetch participant and training details for display (same as before)
$participantSql = "SELECT first_name, last_name FROM user_participants WHERE participant_id = ?";
$participantStmt = $conn->prepare($participantSql);
$participantStmt->bind_param("i", $participant_id);
$participantStmt->execute();
$participantResult = $participantStmt->get_result();
$participant = $participantResult->fetch_assoc();

$trainingSql = "SELECT training_title FROM training WHERE training_id = ?";
$trainingStmt = $conn->prepare($trainingSql);
$trainingStmt->bind_param("i", $training_id);
$trainingStmt->execute();
$trainingResult = $trainingStmt->get_result();
$training = $trainingResult->fetch_assoc();

// Fetch modules for this training for dropdown (same as before)
$modulesSql = "SELECT module_id, module_name, module_type FROM modules WHERE training_id = ?";
$modulesStmt = $conn->prepare($modulesSql);
$modulesStmt->bind_param("i", $training_id);
$modulesStmt->execute();
$modulesResult = $modulesStmt->get_result();
$modules = $modulesResult->fetch_all(MYSQLI_ASSOC);

$selected_module_id = null;
$existing_grade = null; // To store existing grade details
$existing_feedback = null;

if (isset($_POST['module_id'])) {
    $selected_module_id = $_POST['module_id'];
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Grades - Module Selection</title>
    <!-- AdminLTE CSS and other styles -->
    <link rel="stylesheet" href="adminlte/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="adminlte/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.1/dist/css/adminlte.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="adminlte/plugins/jquery/jquery.min.js"></script> <!-- jQuery is needed for dropdown handling -->

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

    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Assign/Edit Grades - Select Module</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="instructordashboard.php">Home</a></li>
                            <li class="breadcrumb-item"><a href="grades.php">Assign Grades</a></li>
                            <li class="breadcrumb-item active">Select Module for Grading</li>
                        </ol>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content-header -->

        <!-- Main content -->
        <div class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title">Participant and Training Information</h3>
                            </div>
                            <div class="card-body">
                                <p><strong>Participant:</strong> <?php echo htmlspecialchars($participant['first_name'] . ' ' . $participant['last_name']); ?></p>
                                <p><strong>Training:</strong> <?php echo htmlspecialchars($training['training_title']); ?></p>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Module Grade Assignment</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="module_id">Select Module:</label>
                                    <select class="form-control" id="module_id" name="module_id">
                                        <option value="">-- Select Module --</option>
                                        <?php foreach ($modules as $module): ?>
                                            <?php if ($module['module_type'] !== 'Lecture'): ?>
                                                <option value="<?php echo $module['module_id']; ?>" <?php if ($selected_module_id == $module['module_id']) echo 'selected'; ?>>
                                                    <?php echo htmlspecialchars($module['module_name']) . " (" . htmlspecialchars($module['module_type']) . ")"; ?>
                                                </option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div id="grade_input_area" style="display: <?php echo $selected_module_id ? 'block' : 'none'; ?>;">
                                    <div class="form-group">
                                        <label for="grade">Grade:</label>
                                        <input type="text" class="form-control" id="grade" name="grade" placeholder="Enter Grade" value="<?php echo htmlspecialchars($existing_grade ?? ''); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="feedback">Feedback (Optional):</label>
                                        <textarea class="form-control" id="feedback" name="feedback" rows="3" placeholder="Enter Feedback"><?php echo htmlspecialchars($existing_feedback ?? ''); ?></textarea>
                                    </div>
                                    <div class="form-group">
                                        <a href="grades.php" class="btn btn-outline-secondary mr-1">Cancel</a>
                                        <button type="button" id="save_grade_button" class="btn btn-primary">Save</button>                                        
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content -->
    </div>
    <?php include '../footer.php'; ?>
</div>

<!-- AdminLTE JS and other scripts -->
<script src="adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="adminlte/dist/js/adminlte.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.1/dist/js/adminlte.min.js"></script>

<script>
    $(document).ready(function() {
        $('#module_id').change(function() {
            var module_id = $(this).val();
            var module_name = $('#module_id option:selected').text().split('(')[0].trim(); // Extract module name
            if (module_id !== '') {
                $.ajax({
                    url: 'fetch_grade.php',
                    type: 'GET',
                    data: {
                        participant_id: <?php echo $participant_id; ?>,
                        training_id: <?php echo $training_id; ?>,
                        module_id: module_id
                    },
                    dataType: 'json',
                    success: function(data) {
                        if (data) {
                            // Existing grade found - EDIT MODE
                            $('#grade').val(data.grade_given);
                            $('#feedback').val(data.feedback);
                            // Update page title and heading for EDIT mode
                            $('title').text('Edit Grade - ' + module_name);
                            $('.content-header h1').text('Edit Grade - ' + module_name);
                        } else {
                            // No existing grade - ASSIGN MODE
                            $('#grade').val(''); // Clear grade input
                            $('#feedback').val(''); // Clear feedback input
                            // Update page title and heading for ASSIGN mode
                            $('title').text('Assign Grade - ' + module_name);
                            $('.content-header h1').text('Assign Grade - ' + module_name);
                        }
                        $('#grade_input_area').show();
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.error("AJAX error: " + textStatus + ' : ' + errorThrown);
                        $('#grade_input_area').hide();
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Failed to fetch grade information. Please try again.',
                        });
                         // Optionally reset title to a generic state on error
                        $('title').text('Assign/Edit Grades - Select Module');
                        $('.content-header h1').text('Assign/Edit Grades - Select Module');
                    }
                });
            } else {
                $('#grade_input_area').hide();
                // Reset title to generic when no module is selected
                $('title').text('Assign/Edit Grades - Select Module');
                $('.content-header h1').text('Assign/Edit Grades - Select Module');
            }
        });

        // Initial title when page loads (before module selection)
        $('title').text('Assign/Edit Grades - Select Module');
        $('.content-header h1').text('Assign/Edit Grades - Select Module');


        // AJAX Save Grade Functionality
        $('#save_grade_button').click(function() {
            var module_id = $('#module_id').val();
            var grade = $('#grade').val();
            var feedback = $('#feedback').val();

            if (!module_id) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Warning!',
                    text: 'Please select a module to save the grade.',
                });
                return;
            }

            $.ajax({
                url: 'save_grade.php', // New PHP file to handle saving
                type: 'POST',
                data: {
                    participant_id: <?php echo $participant_id; ?>,
                    training_id: <?php echo $training_id; ?>,
                    module_id: module_id,
                    grade: grade,
                    feedback: feedback
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Grade Saved!',
                            text: response.message,
                            timer: 1000,
                            showConfirmButton: false
                        }).then(function() { // After SweetAlert closes
                            if (response.redirect) {
                                window.location.href = 'grades.php'; // Redirect to grades.php
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: response.message || 'Failed to save grade. Please try again.',
                        });
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error("AJAX error saving grade: " + textStatus + ' : ' + errorThrown);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Failed to save grade. Please check console for details and try again.',
                    });
                }
            });
        });
    });
</script>
</body>
</html>
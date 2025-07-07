<?php

include '../includes/config.php';

// Ensure participant is logged in
if (!isset($_SESSION["participant_id"])) {
    //die("User not logged in."); // Consider redirecting to login page
    echo '<div class="callout callout-danger">
              <h5>User Not Logged In!</h5>
              <p>You are not logged in as a participant. Please log in to view your grades.</p>
              <p><a href="login.php">Login Here</a></p> <!-- Replace login.php with your actual login page URL -->
            </div>';
    exit(); // Stop further execution
}

$participant_id = $_SESSION["participant_id"];

// Step 1: Get training_id from training_participants table
$sql_training_id = "SELECT training_id FROM training_participants WHERE participant_id = ?";
$stmt = $conn->prepare($sql_training_id);
$stmt->bind_param("i", $participant_id);
$stmt->execute();
$stmt->bind_result($training_id);
$stmt->fetch();
$stmt->close();

// Ensure training_id is valid
if (empty($training_id)) {
    //die("No training assigned to this participant.");
    echo '<div class="callout callout-warning">
              <h5>No Training Assigned!</h5>
              <p>It appears that you are not assigned to any training yet. Please contact the administrator if you believe this is an error.</p>
            </div>';
    exit(); // Stop further execution
}

// Step 2: Fetch module details excluding 'lecture' type
$sql_modules = "SELECT module_id, module_name, module_description, module_type
                FROM modules
                WHERE training_id = ? AND module_type != 'lecture'";
$stmt_modules = $conn->prepare($sql_modules);
$stmt_modules->bind_param("i", $training_id);
$stmt_modules->execute();
$result_modules = $stmt_modules->get_result();

// Default values
$modules = [];
$hasPassed = false;
$hasExamination = false;

// Fetch module data
while ($row = $result_modules->fetch_assoc()) {
    $module_id = $row['module_id'];

    // Fetch the grade and grade_status for the current module
    $sql_grade = "SELECT grade_given, feedback, graded_at FROM grades WHERE training_id = ? AND module_id = ? AND participant_id = ?"; // Added participant_id to WHERE clause
    $stmt_grade = $conn->prepare($sql_grade);
    $stmt_grade->bind_param("iii", $training_id, $module_id, $participant_id); // Bind participant_id
    $stmt_grade->execute();
    $result_grade = $stmt_grade->get_result();

    // Default grade and status
    $gradeGiven = "Not Graded";
    $gradeStatus = "Not Graded";
    $gradeFeedback = "No Feedback";
    $gradedAt = "Not Graded";


    if ($grade_row = $result_grade->fetch_assoc()) {
        $gradeGiven = htmlspecialchars($grade_row["grade_given"], ENT_QUOTES, 'UTF-8');
        $gradeFeedback = htmlspecialchars($grade_row["feedback"], ENT_QUOTES, 'UTF-8');

        // Format the graded_at timestamp
        $gradedAtTimestamp = strtotime($grade_row["graded_at"]);
        $gradedAt = date("m-d-Y h:i:s a", $gradedAtTimestamp);


        // Check if module is an examination and determine pass/fail
        if ($row['module_type'] && is_numeric($gradeGiven)) {
            $hasExamination = true;
            if (floatval($gradeGiven) >= 70) {
                $hasPassed = true;
                $gradeStatus = "Passed";
            } else {
                $hasPassed = false;
                $gradeStatus = "Failed";
            }
        } else {
            // If not an Examination, set gradeStatus to "N/A"
            $gradeStatus = "N/A";
        }
    }

    // Add module data to the array
    $modules[] = [
        'module_name' => htmlspecialchars($row["module_name"], ENT_QUOTES, 'UTF-8'),
        'module_description' => htmlspecialchars($row["module_description"], ENT_QUOTES, 'UTF-8'),
        'module_type' => htmlspecialchars($row["module_type"], ENT_QUOTES, 'UTF-8'),
        'grade_given' => is_numeric($gradeGiven) ? number_format((float)$gradeGiven, 2) : $gradeGiven,
        'feedback' => $gradeFeedback,
        'grade_status' => $gradeStatus,
        'graded_at' => $gradedAt  // Add graded_at to the array
    ];

    $stmt_grade->close();
}

// Close module statement & connection
$stmt_modules->close();
$conn->close();

// Determine the final pass/fail message
$passMessage = "";
if ($hasExamination) {
    if ($hasPassed) {
        $passMessage = "<p class='text-success font-weight-bold text-center'>Congratulations! You passed this course for reaching 70 or higher in exam grade.</p>";
    } else {
        $passMessage = "<p class='text-danger font-weight-bold text-center'>Sorry, You failed the course for not attaining an exam grade of 70 or higher.</p>";
    }
}else{
    $passMessage = " ";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
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

    <style>
        .success-gradient {
            background: linear-gradient(225deg, #9BEC00, #4BB543, #117554);
            color: white;
        }
    </style>
</head>
<body class="hold-transition sidebar-mini">

<div class="preloader flex-column justify-content-center align-items-center">
    <img class="animation__shake rounded-circle" src="../../dist/img/denrlogo.jpg" alt="denrlogo" height="100" width="100">
</div>




<div class="container-fluid mt-1">
    <div class="card">
        <div class="card-header success-gradient text-white d-flex justify-content-between align-items-center">
            <h3>Grades</h3>
        </div>
        <div class="card-body">
        <?php if (!empty($modules)): ?> <!-- Only show table if there are modules -->
            <table id="gradesTable" class="table table-bordered table-striped">
                <thead class="success-gradient">
                    <tr>
                        <th>Module Name</th>
                        <th>Description</th>
                        <th>Type</th>
                        <th>Grade</th>
                        <th>Feedback</th>
                        <th>Status</th>
                        <th>Graded At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($modules as $module): ?>
                        <tr>
                            <td><?php echo $module['module_name']; ?></td>
                            <td><?php echo $module['module_description']; ?></td>
                            <td><?php echo $module['module_type']; ?></td>
                            <td><?php echo $module['grade_given']; ?></td>
                            <td><?php echo $module['feedback']; ?></td>
                            <td>
                                <?php
                                    $badgeClass = 'badge-secondary'; // default
                                    if ($module['grade_status'] == 'Passed') {
                                        $badgeClass = 'badge-success';
                                    } elseif ($module['grade_status'] == 'Failed') {
                                        $badgeClass = 'badge-danger';
                                    }
                                ?>
                                <span class="badge <?php echo $badgeClass; ?>">
                                    <?php echo $module['grade_status']; ?>
                                </span>
                            </td>
                            <td><?php echo $module['graded_at']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="mt-4">
                <?php echo $passMessage; ?>
            </div>
        <?php endif; ?> <!-- End of table condition -->
        </div>
    </div>
</div>


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

<!-- Include Bootstrap & jQuery -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>

<!-- iCheck -->
<link rel="stylesheet" href="../../plugins/icheck-bootstrap/icheck-bootstrap.min.css">


<script>
  $(function () {
    $("#gradesTable").DataTable({
      "responsive": true, "lengthChange": true, "autoWidth": false,
      "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
    }).buttons().container().appendTo('#gradesTable_wrapper .col-md-6:eq(0)');
  });
</script>
</body>
</html>
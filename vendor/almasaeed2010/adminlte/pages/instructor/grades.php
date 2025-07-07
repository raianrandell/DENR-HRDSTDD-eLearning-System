<?php
    session_start();
    // Make sure this path is correct relative to your assignGrades.php file
    include '../includes/config.php'; 
    if (!isset($_SESSION['instructor_id'])) {
        // Make sure this path is correct
        header("Location: instructorLogin.php"); 
        exit();
    }

    $instructor_id = $_SESSION['instructor_id']; // Get instructor ID from session

    // --- Database Interaction ---
    // Function to get module statuses for a specific participant and training
    function getModuleStatuses($conn, $participant_id, $training_id) {
        $statuses = [];
        $sql = "SELECT 
                    m.module_type, 
                    COALESCE(g.grade_status, 'Not Graded') AS status
                FROM modules tm
                JOIN modules m ON tm.module_id = m.module_id
                LEFT JOIN grades g ON tm.module_id = g.module_id AND g.participant_id = ? AND g.training_id = ?
                WHERE tm.training_id = ?
                ORDER BY m.module_type"; // Order for consistent display

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("iii", $participant_id, $training_id, $training_id);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $statuses[] = $row; // Store module type and status
            }
            $stmt->close();
        } else {
            // Handle error - maybe log it
             error_log("Error preparing module status statement: " . $conn->error);
             return []; // Return empty array on error
        }
        return $statuses;
    }

    // --- Fetch Training Titles for Filter ---
    $trainingTitles = [];
    if ($conn) {
        $trainingTitlesQuery = "SELECT DISTINCT t.training_title, t.training_id
                                FROM training t
                                JOIN training_instructors ti ON t.training_id = ti.training_id
                                WHERE ti.instructor_id = ?
                                ORDER BY t.training_title";
        if ($trainingTitlesStmt = $conn->prepare($trainingTitlesQuery)) {
            $trainingTitlesStmt->bind_param("i", $instructor_id);
            $trainingTitlesStmt->execute();
            $trainingTitlesResult = $trainingTitlesStmt->get_result();
            while ($trainingTitleRow = $trainingTitlesResult->fetch_assoc()) {
                $trainingTitles[] = $trainingTitleRow;
            }
            $trainingTitlesStmt->close();
        } else {
             error_log("Error preparing training titles statement: " . $conn->error);
        }
    }

    // --- Fetch Main Participant Data ---
    $participantsData = [];
    if ($conn) {
         // Simpler main query: Get participants assigned to the instructor's trainings
        $sql = "SELECT DISTINCT
                    tp.participant_id,
                    up.first_name,
                    up.last_name,
                    t.training_title,
                    t.training_id
                FROM
                    training_participants tp
                JOIN
                    user_participants up ON tp.participant_id = up.participant_id
                JOIN
                    training t ON tp.training_id = t.training_id
                JOIN
                    training_instructors ti ON t.training_id = ti.training_id
                WHERE
                    ti.instructor_id = ?
                ORDER BY
                    t.training_title ASC, up.last_name ASC, up.first_name ASC";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $instructor_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    // For each participant, get their module statuses for this specific training
                    $row['module_statuses'] = getModuleStatuses($conn, $row['participant_id'], $row['training_id']);
                    $participantsData[] = $row;
                }
            }
            $stmt->close();
        } else {
             error_log("Error preparing main participant statement: " . $conn->error);
             // Display error message on page or handle differently
        }
    } else {
         // Handle database connection error
         error_log("Database connection error in assignGrades.php");
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Grades</title>
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="adminlte/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.1/dist/css/adminlte.min.css">
    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- DataTables -->
    <link rel="stylesheet" href="../../plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="../../plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="../../plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.min.css">

    <style>
        a.text-primary:hover {
            text-decoration: underline;
        }
        .info-gradient {
            background: linear-gradient(225deg, #98D2C0, #17A2B8, #205781);
        }
        /* Style for different grade statuses */
        .status-graded, .status-passed, .status-completed { /* Group positive statuses */
            color: green;
            font-weight: bold;
        }
        .status-not-graded {
            color: #6c757d; /* Grey for neutral */
            font-weight: bold;
        }
         .status-failed {
            color: red; /* Red for failed */
            font-weight: bold;
        }
         .status-incomplete {
             color: orange; /* Orange for incomplete/warning */
             font-weight: bold;
         }
         /* Badge styling */
        .module-status-badge {
            display: inline-block;
            padding: 0.25em 0.6em;
            font-size: 75%;
            font-weight: 700;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 0.25rem;
            margin-bottom: 0.3rem; /* Add space between badges */
            margin-right: 0.3rem; /* Add space between badges */
        }
         .badge-graded, .badge-passed, .badge-completed {
             color: #fff;
             background-color: #28a745; /* Bootstrap Success Green */
         }
         .badge-not-graded {
             color: #fff;
             background-color: #6c757d; /* Bootstrap Secondary Grey */
         }
         .badge-failed {
             color: #fff;
             background-color: #dc3545; /* Bootstrap Danger Red */
         }
        .badge-incomplete {
             color: #212529; /* Dark text for better contrast */
             background-color: #ffc107; /* Bootstrap Warning Yellow */
         }
         /* Adjust preloader path if needed */
         .preloader img { border-radius: 50%; }
    </style>
</head>
<body class="hold-transition sidebar-mini">
<div class="preloader flex-column justify-content-center align-items-center">
    <img class="animation__shake rounded-circle" src="../../dist/img/denrlogo.jpg" alt="denrlogo" height="100" width="100">
</div>

    <div class="wrapper">
        <!-- Navbar -->
        <?php include 'navbar.php'; // Make sure this path is correct ?>

        <!-- Sidebar -->
        <?php include 'sidebar.php'; // Make sure this path is correct ?>

        <!-- Content Wrapper -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">Assign Grades</h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="instructordashboard.php">Home</a></li>
                                <li class="breadcrumb-item active">Assign Grades</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main content -->
            <div class="content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-header info-gradient text-white d-flex justify-content-between align-items-center">
                                    <h3 class="card-title"><i class="fas fa-user-graduate mr-1"></i> Participants for Grading</h3>
                                </div>
                                <div class="card-body">
                                    <?php if (!empty($trainingTitles)): // Only show filter if there are trainings ?>
                                    <div class="mb-3">
                                        <label for="trainingFilter">Filter by Training Title:</label>
                                        <select class="form-control" id="trainingFilter" style="width: auto; display: inline-block;">
                                            <option value="">All Assigned Trainings</option>
                                            <?php foreach ($trainingTitles as $titleInfo): ?>
                                                <option value="<?php echo htmlspecialchars($titleInfo['training_title']); ?>">
                                                    <?php echo htmlspecialchars($titleInfo['training_title']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <?php endif; ?>

                                    <div class="table-responsive">
                                        <table id="participantsTable" class="table table-bordered table-striped">
                                            <thead class="info-gradient text-white">
                                                <tr>
                                                    <th style="width: 10px;">#</th>
                                                    <th>Name</th>
                                                    <th>Training Title</th>
                                                    <th>Module Grade Status</th> <!-- Updated Header -->
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (!empty($participantsData)): ?>
                                                    <?php $counter = 1; ?>
                                                    <?php foreach ($participantsData as $row): ?>
                                                        <tr data-training-title="<?php echo htmlspecialchars($row['training_title']); ?>">
                                                            <td><?php echo $counter++; ?></td>
                                                            <td><?php echo htmlspecialchars($row['first_name']) . " " . htmlspecialchars($row['last_name']); ?></td>
                                                            <td><?php echo htmlspecialchars($row['training_title']); ?></td>
                                                            <td>
                                                                <?php
                                                                if (!empty($row['module_statuses'])) {
                                                                    $displayed_a_module = false; // Flag to track if we showed anything

                                                                    foreach ($row['module_statuses'] as $module_status) {
                                                                        $current_module_type = trim($module_status['module_type']); // Trim whitespace

                                                                        // --- Check if the module type is 'Lecture' (case-insensitive) ---
                                                                        if (strcasecmp($current_module_type, 'lecture') === 0) {
                                                                            continue; // Skip this iteration if it's a lecture
                                                                        }
                                                                        // --- End of check ---

                                                                        // If we reach here, it's not a lecture, so we display it
                                                                        $displayed_a_module = true; // Mark that we displayed at least one module

                                                                        $status_text = htmlspecialchars(trim($module_status['status']));
                                                                        // Generate a CSS class from the status text (lowercase, replace spaces with hyphens)
                                                                        $status_class_suffix = strtolower(str_replace(' ', '-', $status_text));
                                                                        $badge_class = 'badge-' . $status_class_suffix; // e.g., badge-not-graded, badge-passed

                                                                        // Output badge - use a default if class doesn't match defined styles
                                                                        echo "<span class='module-status-badge " . $badge_class . "'>"
                                                                            . htmlspecialchars($current_module_type) . ": " // Use the trimmed type
                                                                            . $status_text
                                                                            . "</span>"; // Removed the space here as <br> will separate

                                                                        // --- Add a line break after each displayed module status badge ---
                                                                        echo "<br>"; 
                                                                        // --- End of line break ---
                                                                    }

                                                                    // After the loop, check if we actually displayed any module status
                                                                    if (!$displayed_a_module) {
                                                                        echo "<span class='badge badge-info'>No gradable modules found</span>"; // Message if only lectures existed or were skipped
                                                                    }

                                                                } else {
                                                                    // This message remains if the participant has no modules at all for this training
                                                                    echo "<span class='badge badge-secondary'>No modules assigned</span>";
                                                                }
                                                                ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                // Always provide a link to the grading form.
                                                                // The form itself (assignGradeForm.php) will handle viewing/editing/assigning specific modules.
                                                                ?>
                                                                <a href="assignGradeForm.php?participant_id=<?php echo $row['participant_id']; ?>&training_id=<?php echo $row['training_id']; ?>" class="btn btn-sm btn-outline-primary">
                                                                    <i class="fas fa-edit mr-1"></i> Manage Grades
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="5" class="text-center">No participants found for the trainings assigned to you, or database error occurred.</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div> <!-- /table-responsive -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div> <!-- /.content-wrapper -->

        <!-- Footer -->
        <?php include '../footer.php'; // Make sure this path is correct ?>
    </div> <!-- ./wrapper -->

    </div>
<!-- AdminLTE JS -->
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.all.min.js"></script>

<script>
    $(function () {
        // Initialize DataTable
        var table = $("#participantsTable").DataTable({
            "responsive": true,
            "lengthChange": true, // Allow changing number of entries shown
            "autoWidth": false,
             "columnDefs": [
                 { "orderable": false, "targets": 4 }, // Disable sorting for Action column (index 4)
                 { "searchable": false, "targets": [0, 4] } // Disable search for # and Action
             ],
             "order": [[ 2, "asc" ], [1, "asc"]] // Default sort by Training Title, then Name
        });

        // Append buttons container to the DataTables wrapper
        table.buttons().container().appendTo('#participantsTable_wrapper .col-md-6:eq(0)');

        // Training Title Filter Logic
        $('#trainingFilter').on('change', function() {
            var trainingTitle = $(this).val();
            // Use regex for exact match on the Training Title column (index 2)
            // The '^' and '$' ensure it matches the whole cell content
            // $.fn.dataTable.util.escapeRegex escapes special characters in the title
            table.column(2).search(trainingTitle ? '^'+$.fn.dataTable.util.escapeRegex(trainingTitle)+'$' : '', true, false).draw();
        });

        // Optional: Add SweetAlert confirmation if needed for actions later
        // Example:
        // $('.delete-btn').on('click', function(e) {
        //     e.preventDefault(); // Prevent default link behavior
        //     var deleteUrl = $(this).attr('href');
        //     Swal.fire({
        //         title: 'Are you sure?',
        //         text: "You won't be able to revert this!",
        //         icon: 'warning',
        //         showCancelButton: true,
        //         confirmButtonColor: '#3085d6',
        //         cancelButtonColor: '#d33',
        //         confirmButtonText: 'Yes, delete it!'
        //     }).then((result) => {
        //         if (result.isConfirmed) {
        //             window.location.href = deleteUrl; // Proceed with deletion
        //         }
        //     })
        // });

    });
</script>

</body>
</html>
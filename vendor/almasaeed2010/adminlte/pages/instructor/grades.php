<?php
    session_start();
    include '../includes/config.php'; // Make sure this path is correct
    if (!isset($_SESSION['instructor_id'])) {
        header("Location: instructorLogin.php"); // Make sure this path is correct
        exit();
    }

    $instructor_id = $_SESSION['instructor_id']; // Get instructor ID from session
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
        a.text-primary {
            text-decoration: none;
        }

        a.text-primary:hover {
            text-decoration: underline;
        }

        .info-gradient {
            background: linear-gradient(225deg, #98D2C0, #17A2B8, #205781);
        }

        /* Style for different grade statuses */
        .status-graded {
            color: green;
            font-weight: bold;
        }
        .status-not-graded {
            color: red;
            font-weight: bold;
        }
        /* Add more styles as needed */

    </style>
</head>
<body class="hold-transition sidebar-mini">
    <!-- Preloader removed for brevity, assuming it works -->
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
                            <h1 class="m-0">Assign Grades</h1> <!-- Added title here -->
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
                                <h3 class="card-title"><i class="fas fa-users mr-1"></i> List of Participants for Grading</h3>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="trainingFilter">Filter by Training Title:</label>
                                        <select class="form-control w-auto" id="trainingFilter">
                                            <option value="">All Assigned Trainings</option>
                                            <?php
                                                // Fetch distinct training titles for the logged-in instructor
                                                // Ensure connection is available ($conn) from config.php
                                                if ($conn) {
                                                    $trainingTitlesQuery = "SELECT DISTINCT t.training_title
                                                                            FROM training t
                                                                            JOIN training_instructors ti ON t.training_id = ti.training_id
                                                                            JOIN training_participants tp ON t.training_id = tp.training_id
                                                                            WHERE ti.instructor_id = ?";
                                                    $trainingTitlesStmt = $conn->prepare($trainingTitlesQuery);
                                                    if ($trainingTitlesStmt) {
                                                        $trainingTitlesStmt->bind_param("i", $instructor_id);
                                                        $trainingTitlesStmt->execute();
                                                        $trainingTitlesResult = $trainingTitlesStmt->get_result();

                                                        if ($trainingTitlesResult->num_rows > 0) {
                                                            while ($trainingTitleRow = $trainingTitlesResult->fetch_assoc()) {
                                                                echo "<option value='" . htmlspecialchars($trainingTitleRow['training_title']) . "'>" . htmlspecialchars($trainingTitleRow['training_title']) . "</option>";
                                                            }
                                                        }
                                                        $trainingTitlesStmt->close();
                                                    } else {
                                                        echo "<option value=''>Error preparing statement</option>";
                                                    }
                                                } else {
                                                    echo "<option value=''>Database connection error</option>";
                                                }
                                            ?>
                                        </select>
                                    </div>

                                    <div class="table-responsive"> <!-- Added for responsive table -->
                                        <table id="participantsTable" class="table table-bordered table-striped"> <!-- Changed ID for clarity -->
                                            <thead class="info-gradient text-white">
                                                <tr>
                                                    <th>#</th>
                                                    <th>Name</th>
                                                    <th>Training Title</th>
                                                    <th>Grade Status</th> <!-- Added Grade Status Header -->
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                    <?php
                                        // Modified SQL query to fetch participants and their grade status
                                        // Added COALESCE for grade_status
                                        $sql = "SELECT
                                                    tp.participant_id,
                                                    up.first_name,
                                                    up.last_name,
                                                    t.training_title,
                                                    t.training_id,
                                                    COALESCE(g.grade_status, 'Not Graded') AS grade_status -- Fetch status, default if NULL
                                                FROM
                                                    training_participants tp
                                                JOIN
                                                    user_participants up ON tp.participant_id = up.participant_id
                                                JOIN
                                                    training t ON tp.training_id = t.training_id
                                                JOIN
                                                    training_instructors ti ON t.training_id = ti.training_id
                                                LEFT JOIN
                                                    grades g ON tp.participant_id = g.participant_id AND t.training_id = g.training_id
                                                WHERE
                                                    ti.instructor_id = ?
                                                GROUP BY
                                                    tp.participant_id, t.training_id -- Group to avoid duplicates if multiple instructors assigned
                                                ORDER BY
                                                    t.training_title ASC, up.last_name ASC"; // Added last name sort

                                                if ($conn) {
                                                    $stmt = $conn->prepare($sql);
                                                    if ($stmt) {
                                                        $stmt->bind_param("i", $instructor_id);
                                                        $stmt->execute();
                                                        $result = $stmt->get_result();

                                                        if ($result->num_rows > 0) {
                                                            $counter = 1;
                                                            while ($row = $result->fetch_assoc()) {
                                                                // Determine status text and badge class for styling
                                                                $status_text = htmlspecialchars($row['grade_status']);
                                                                $badge_class = ''; // Initialize badge class variable

                                                                // Assign badge class based on the status
                                                                // --- Adjust these conditions based on your actual grade_status values ---
                                                                if ($row['grade_status'] == 'Not Graded') {
                                                                    $badge_class = 'badge badge-secondary'; // Red badge for Not Graded
                                                                } elseif ($row['grade_status'] == 'Graded' || $row['grade_status'] == 'Passed' || $row['grade_status'] == 'Completed') {
                                                                    // Assuming 'Graded', 'Passed', 'Completed' are positive statuses
                                                                    $badge_class = 'badge badge-success'; // Green badge for Graded/Passed/Completed
                                                                } elseif ($row['grade_status'] == 'Failed') {
                                                                    $badge_class = 'badge badge-warning'; // Yellow/Orange badge for Failed (or use badge-danger)
                                                                } else {
                                                                    // Default badge for any other status
                                                                    $badge_class = 'badge badge-secondary'; // Grey badge as default
                                                                }
                                                                // --- End of badge class assignment ---


                                                                echo "<tr data-training-title='" . htmlspecialchars($row['training_title']) . "'>";
                                                                echo "<td>" . $counter . "</td>";
                                                                echo "<td>" . htmlspecialchars($row['first_name']) . " " . htmlspecialchars($row['last_name']) . "</td>";
                                                                echo "<td>" . htmlspecialchars($row['training_title']) . "</td>";

                                                                // Display Grade Status using Bootstrap Badge
                                                                echo "<td><span class='" . $badge_class . "'>" . $status_text . "</span></td>"; // Output the badge HTML

                                                                echo "<td>";
                                                                // Update button logic based on grade_status (remains the same as before)
                                                                if ($row['grade_status'] != 'Not Graded') {
                                                                    // If status is anything other than 'Not Graded', show Edit button
                                                                    echo "<a href='assignGradeForm.php?participant_id=" . $row['participant_id'] . "&training_id=" . $row['training_id'] . "' class='btn btn-sm btn-warning'>
                                                                            <i class='fas fa-edit mr-1'></i> Edit Grade
                                                                        </a>";
                                                                } else {
                                                                    // If status is 'Not Graded', show Assign button
                                                                    echo "<a href='assignGradeForm.php?participant_id=" . $row['participant_id'] . "&training_id=" . $row['training_id'] . "' class='btn btn-sm btn-outline-info'>
                                                                            <i class='fas fa-plus mr-1'></i> Assign Grade
                                                                        </a>";
                                                                }
                                                                echo "</td>";
                                                                echo "</tr>";
                                                                $counter++;
                                                            }
                                                        } else {
                                                            // Update colspan to 5
                                                            echo "<tr><td colspan='5'>No participants found for the trainings assigned to you.</td></tr>";
                                                        }
                                                        $stmt->close();
                                                    } else {
                                                        echo "<tr><td colspan='5'>Error preparing statement: " . $conn->error . "</td></tr>";
                                                    }
                                                } else {
                                                    echo "<tr><td colspan='5'>Database connection error.</td></tr>";
                                                }
                                            ?>
                                        </tbody>
                                        </table>
                                    </div> <!-- /table-responsive -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <?php include '../footer.php'; // Make sure this path is correct ?>
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
            var table = $("#participantsTable").DataTable({ // Use the updated ID
                "responsive": true,
                "lengthChange": true,
                "autoWidth": false,
                // "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"], // Optional: Add buttons if needed
                "columnDefs": [
                    { "orderable": false, "targets": 4 } // Disable sorting for Action column
                 ]
            });//.buttons().container().appendTo('#participantsTable_wrapper .col-md-6:eq(0)'); // Add buttons container if using buttons

            // Training Title Filter
            $('#trainingFilter').on('change', function() {
                var trainingTitle = $(this).val();
                // Target the correct column index for Training Title (index 2)
                table.column(2).search(trainingTitle ? '^'+$.fn.dataTable.util.escapeRegex(trainingTitle)+'$' : '', true, false).draw();
            });

            // Note: Removed the second DataTable initialization for "#traineesTable" as it seemed redundant based on the code structure.
            // If you have another table with ID "traineesTable", you can keep its initialization.
        });
    </script>
</body>
</html>
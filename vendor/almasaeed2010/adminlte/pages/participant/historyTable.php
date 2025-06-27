<?php
// Start the session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include your database configuration ONCE
include '../includes/config.php'; // Make sure this path is correct

// --- Authentication Check ---
if (!isset($_SESSION["participant_id"])) {
    // Provide a more user-friendly message or redirect
    // Consider using header("Location: /login.php"); exit; for a redirect
    die('<div class="container mt-5"><div class="alert alert-danger text-center">Access Denied: User not logged in. Please <a href="/login.php">login</a>.</div></div>');
}
$participant_id = $_SESSION["participant_id"];

// --- Fetch Training HISTORY ---
$historical_trainings = []; // Array to hold the training details

// Prepare the SQL query using JOIN
$sql = "SELECT
            t.training_id,
            t.training_title,
            t.description,
            t.location,
            t.start_date,
            t.end_date,
            t.training_hrs,
            tp.completion_status
        FROM training t
        INNER JOIN training_participants tp ON t.training_id = tp.training_id
        WHERE tp.participant_id = ? AND tp.completion_status = 'Completed'"; // Filter for 'Completed'

// Prepare and execute the statement
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $participant_id);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $historical_trainings[] = $row;
        }
    } else {
        error_log("DB Execute Error (Training History): " . $stmt->error);
        echo '<div class="container mt-3"><div class="alert alert-warning text-center">Could not retrieve training history data at this time.</div></div>';
    }
    $stmt->close();
} else {
    error_log("DB Prepare Error (Training History): " . $conn->error);
    echo '<div class="container mt-3"><div class="alert alert-danger text-center">Database error retrieving training history. Please contact support.</div></div>';
}

// DO NOT close the connection here if needed inside the loop for modals
// $conn->close(); // Keep connection open for modal data fetching

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>History</title>

    <!-- Bootstrap 4.6 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome 6.4.2 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <style>
        /* --- Your existing styles --- */
        .success-gradient {
            background: linear-gradient(225deg, #9BEC00, #4BB543, #117554);
            color: white;
        }
        .card-text-details {
             white-space: normal; overflow: hidden; text-overflow: ellipsis;
             display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;
             font-size: 0.9rem; margin-bottom: 0.5rem;
         }
         .card-header h5 {
             font-size: 1rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
         }
         .card-details small {
             font-size: 0.85rem; display: block; margin-bottom: 0.3rem;
         }
          .card-details i.fa-solid, .card-details i.fa-regular {
              width: 18px; text-align: center; margin-right: 4px; color: #117554;
          }
          .status-badge {
              font-size: 0.75rem; font-weight: bold; padding: 0.2em 0.6em;
              border-radius: 0.25rem; vertical-align: middle;
          }
          .modal-body p strong i.fa-solid, .modal-body p strong i.fa-regular {
               width: 20px; text-align: center; margin-right: 5px; color: #117554;
          }
          .modal-body p { margin-bottom: 0.75rem; }
          .modal-body h4 { margin-bottom: 1rem; }
          .modal-footer { background-color: #f8f9fa; }
          /* --- End existing styles --- */
    </style>

    <!-- jQuery and Bootstrap 4.6 JS Bundle -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>

<div class="container-fluid mt-4">
    <!-- Training History Section -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header success-gradient">
                    <h2 class="mb-0"><i class="mr-2"></i>History</h2>
                </div>
                <div class="card-body">
                    <div class="row">

                        <?php if (!empty($historical_trainings)): ?>
                            <?php foreach ($historical_trainings as $training): ?>
                                <?php
                                    // --- Card Data Preparation ---
                                    $current_training_id = $training['training_id']; // Get ID for this iteration
                                    $startDateFormatted = 'N/A';
                                    $endDateFormatted = 'N/A';
                                    try {
                                        if (!empty($training['start_date'])) { $startDateFormatted = date('M d, Y', strtotime($training['start_date'])); }
                                        if (!empty($training['end_date'])) { $endDateFormatted = date('M d, Y', strtotime($training['end_date'])); }
                                    } catch (Exception $e) { error_log("Date formatting error for training ID " . $current_training_id . ": " . $e->getMessage()); }

                                    $status = $training['completion_status'] ?? 'Unknown';
                                    $badgeClass = 'badge-secondary'; // Default
                                    if ($status == 'Completed' || $status == 'Passed') { $badgeClass = 'badge-success'; }
                                    elseif ($status == 'Failed') { $badgeClass = 'badge-danger'; }
                                    $statusBadge = "<span class='badge $badgeClass status-badge ml-2'>" . htmlspecialchars($status) . "</span>";

                                    // --- Modal Data Fetching (Moved Inside Loop) ---
                                    $modules = [];
                                    $hasPassedOverall = false; // Changed variable name for clarity
                                    $hasExamination = false;
                                    $passMessage = "";

                                    // Use the connection established earlier ($conn)
                                    // Fetch module details (excluding 'lecture') for THIS specific training
                                    $sql_modules = "SELECT module_id, module_name, module_description, module_type
                                                    FROM modules
                                                    WHERE training_id = ? AND module_type != 'lecture'";
                                    if ($stmt_modules = $conn->prepare($sql_modules)) {
                                        $stmt_modules->bind_param("i", $current_training_id); // Use the correct ID
                                        if ($stmt_modules->execute()) {
                                            $result_modules = $stmt_modules->get_result();

                                            while ($module_row = $result_modules->fetch_assoc()) {
                                                $module_id = $module_row['module_id'];
                                                $gradeGiven = "Not Graded";

                                                // Fetch grade for this module and training
                                                $sql_grade = "SELECT grade_given FROM grades WHERE training_id = ? AND module_id = ? AND participant_id = ?"; // Added participant_id check
                                                if ($stmt_grade = $conn->prepare($sql_grade)) {
                                                    $stmt_grade->bind_param("iii", $current_training_id, $module_id, $participant_id); // Bind all params
                                                    if ($stmt_grade->execute()) {
                                                        $result_grade = $stmt_grade->get_result();
                                                        if ($grade_row = $result_grade->fetch_assoc()) {
                                                            $gradeGiven = htmlspecialchars($grade_row["grade_given"] ?? 'N/A', ENT_QUOTES, 'UTF-8');

                                                            // Check if module is an examination and determine pass/fail for this exam
                                                            if ($module_row['module_type'] === 'Examination') {
                                                                $hasExamination = true; // Mark that this training had an exam
                                                                // Check if THIS specific exam grade is passing
                                                                if (is_numeric($grade_row["grade_given"]) && floatval($grade_row["grade_given"]) >= 70) {
                                                                    $hasPassedOverall = true; // If ANY exam is passed, mark overall as passed (adjust logic if needed)
                                                                }
                                                            }
                                                        }
                                                    } else { error_log("DB Execute Error (Grades): " . $stmt_grade->error . " for T:{$current_training_id} M:{$module_id} P:{$participant_id}"); }
                                                    $stmt_grade->close();
                                                } else { error_log("DB Prepare Error (Grades): " . $conn->error); }

                                                // Add module and grade data to modules array
                                                $modules[] = [
                                                    'module_name' => htmlspecialchars($module_row["module_name"], ENT_QUOTES, 'UTF-8'),
                                                    'module_description' => htmlspecialchars($module_row["module_description"], ENT_QUOTES, 'UTF-8'),
                                                    'module_type' => htmlspecialchars($module_row["module_type"], ENT_QUOTES, 'UTF-8'),
                                                    'grade_given' => is_numeric($gradeGiven) ? number_format((float)$gradeGiven, 2) : $gradeGiven,
                                                ];
                                            } // end while modules fetch
                                        } else { error_log("DB Execute Error (Modules): " . $stmt_modules->error . " for T:{$current_training_id}"); }
                                        $stmt_modules->close();
                                    } else { error_log("DB Prepare Error (Modules): " . $conn->error); }

                                    // Determine the final pass/fail message based on whether any exam was passed
                                    if ($hasExamination) {
                                        if ($hasPassedOverall) {
                                            $passMessage = "<p class='text-success font-weight-bold text-center'>Congratulations! You passed this course.</p>";
                                        } else {
                                            $passMessage = "<p class='text-danger font-weight-bold text-center'>Sorry, you did not pass this course. The required examination grade (70 above) was not met.</p>";
                                        }
                                    } else {
                                        // Optionally add a message if there was no examination
                                        // $passMessage = "<p class='text-info text-center'>No examination module found for this training.</p>";
                                    }
                                    // --- End Modal Data Fetching ---
                                ?>

                                <!-- Card HTML -->
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card bg-light shadow-sm h-100">
                                        <div class="card-header text-truncate success-gradient d-flex justify-content-between align-items-center">
                                            <h5 class="card-title font-weight-bold text-truncate mb-0" title="<?php echo htmlspecialchars($training['training_title']); ?>">
                                                <?php echo htmlspecialchars($training['training_title']); ?>
                                            </h5>
                                            <?php echo $statusBadge; ?>
                                        </div>
                                        <div class="card-body d-flex flex-column card-details">
                                            <p class="card-text-details" title="<?php echo htmlspecialchars($training['description']); ?>">
                                                <i class="fas fa-info-circle mr-2"></i><?php echo htmlspecialchars($training['description'] ?? 'No description available.'); ?>
                                            </p>
                                            <p class="mt-auto mb-0">
                                                <small class="text-muted">
                                                    <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($training['location'] ?? 'N/A'); ?>
                                                </small>
                                                <small class="text-muted">
                                                    <i class="far fa-calendar-alt"></i> <?php echo $startDateFormatted; ?> - <?php echo $endDateFormatted; ?>
                                                </small>
                                                <small class="text-muted">
                                                    <i class="far fa-clock"></i> <?php echo htmlspecialchars($training['training_hrs'] ?? 'N/A'); ?> Hours
                                                </small>
                                            </p>
                                            <!-- * IMPORTANT: Update data-target to unique modal ID * -->
                                            <button class="btn btn-outline-success btn-sm rounded-0 mt-2 align-self-end" data-toggle="modal" data-target="#detailsModal_<?php echo $current_training_id; ?>">
                                                View Details <i class="fas fa-arrow-circle-right ml-1"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <!-- End Card HTML -->

                                <!-- * Modal specific to this training (unique ID) * -->
                                <div class="modal fade" id="detailsModal_<?php echo $current_training_id; ?>" tabindex="-1" role="dialog" aria-labelledby="detailsModalLabel_<?php echo $current_training_id; ?>" aria-hidden="true">
                                    <div class="modal-dialog modal-lg" role="document"> <!-- Consider modal-lg for better table view -->
                                        <div class="modal-content">
                                            <div class="modal-header success-gradient">
                                                <!-- * Use unique ID for label * -->
                                                <h5 class="modal-title" id="detailsModalLabel_<?php echo $current_training_id; ?>">
                                                   Grade Report: <?php echo htmlspecialchars($training['training_title']); ?>
                                                </h5>
                                            </div>
                                            <div class="modal-body">
                                                <?php if (!empty($modules)): ?>
                                                    <table class="table table-bordered table-sm"> <!-- table-sm for less padding -->
                                                        <thead>
                                                            <tr>
                                                                <th>Module Name</th>
                                                                <th>Description</th>
                                                                <th>Type</th>
                                                                <th>Grade</th>
                                                                <th>Status</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($modules as $module): ?>
                                                                <tr>
                                                                    <td><?php echo $module['module_name']; ?></td>
                                                                    <td><?php echo $module['module_description']; ?></td>
                                                                    <td><?php echo $module['module_type']; ?></td>
                                                                    <td><?php echo $module['grade_given']; ?></td>
                                                                    <td style="font-weight: bold; color: 
                                                                        <?php
                                                                            if (is_numeric($module['grade_given'])) {
                                                                                echo ($module['grade_given'] >= 70.00) ? 'green' : 'red';
                                                                            } else {
                                                                                echo 'black';
                                                                            }
                                                                        ?>">
                                                                        <?php
                                                                            if (is_numeric($module['grade_given'])) {
                                                                                echo ($module['grade_given'] >= 70.00) ? 'Passed' : 'Failed';
                                                                            } else {
                                                                                echo 'Not Graded';
                                                                            }
                                                                        ?>
                                                                    </td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                <?php else: ?>
                                                    <p class="text-center text-muted">No non-lecture module details or grades found for this training.</p>
                                                <?php endif; ?>
                                                <hr>
                                                <div>
                                                    <?php echo $passMessage; // Display the calculated pass/fail message ?>
                                                </div>
                                            </div>
                                             <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- * End Modal * -->

                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-12">
                                <p class="text-center text-muted font-italic">No completed training history found.</p>
                            </div>
                        <?php endif; ?>

                    </div> <!-- /.row -->
                </div> <!-- /.card-body -->
            </div> <!-- /.card -->
        </div> <!-- /.col -->
    </div> <!-- /.row -->
</div> <!-- /.container -->

<?php
// Close the connection now that we are truly done with it
if (isset($conn)) {
    $conn->close();
}
?>

</body>
</html>
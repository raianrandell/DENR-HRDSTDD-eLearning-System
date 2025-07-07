<?php
    // Ensure session is started AT THE VERY TOP
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    // Include Database Configuration
    require_once '../includes/config.php'; // Use require_once for essential includes

    // --- Check Participant and Get Training ID and in_training status ---
    if (!isset($_SESSION["participant_id"])) {
        // Redirect or display error if participant not logged in
        die('<div class="vh-100 d-flex justify-content-center align-items-center"><div class="alert alert-danger">User not logged in. Please <a href="/login.php">login</a>.</div></div>');
    }
    $participant_id = $_SESSION["participant_id"];
    $training_id = null; // Initialize
    $in_training_status = 0; // Default to not in training

    // Get training_id and in_training status
    $sql_training_info = "SELECT tp.training_id, up.in_training
                           FROM training_participants tp
                           INNER JOIN user_participants up ON tp.participant_id = up.participant_id
                           WHERE tp.participant_id = ? AND tp.completion_status = 'Enrolled'"; // Still fetch only 'Enrolled' trainings
    if ($stmt_info = $conn->prepare($sql_training_info)) {
        $stmt_info->bind_param("i", $participant_id);
        $stmt_info->execute();
        $stmt_info->bind_result($training_id, $in_training_status);
        $stmt_info->fetch();
        $stmt_info->close();
    } else {
        error_log("DB Error (fetch training_id and in_training): " . $conn->error); // Log errors
        die('<div class="alert alert-danger text-center">Database error fetching training assignment. Please contact support.</div>');
    }

    // If in_training_status is 0, effectively set no training assigned
    if ($in_training_status == 0) {
        $training_id = null; // No training if not marked as in_training
    }
    // --- End Check Participant and Training ---


    // --- Fetch Training Dates and Calculate Status ---
    $startDate = null;
    $endDate = null;
    $courseNotStarted = false;
    $courseEnded = false;
    $remaining_seconds = 0;
    $courseTitle = "Training"; // Default title
    $courseDescription = "No description available."; // Default description
    $current_in_training = 0; // Initialize default
    $current_completion_status = 'Enrolled'; // Initialize default

    if ($training_id) {
        // Fetch Training Details (Title, Desc, Dates)
        $sql_training_details = "SELECT training_title, description, start_date, end_date FROM training WHERE training_id = ?";
        if ($stmt_details = $conn->prepare($sql_training_details)) {
            $stmt_details->bind_param("i", $training_id);
            $stmt_details->execute();
            $stmt_details->bind_result($db_title, $db_desc, $start_date_str, $end_date_str);
            if ($stmt_details->fetch()) {
                $courseTitle = htmlspecialchars($db_title, ENT_QUOTES, 'UTF-8');
                $courseDescription = htmlspecialchars($db_desc, ENT_QUOTES, 'UTF-8');
                // Use DateTime objects for more robust date handling
                try {
                    $startDateTime = new DateTime($start_date_str);
                    $endDateTime = new DateTime($end_date_str);
                    // Set end date time to end of day for accurate comparison
                    $endDateTime->setTime(23, 59, 59);

                    $startDate = $startDateTime->getTimestamp();
                    $endDate = $endDateTime->getTimestamp();

                    $now = time();
                    $remaining_seconds = ($now < $endDate) ? $endDate - $now : 0;
                    $courseNotStarted = ($now < $startDate);
                    $courseEnded = ($now > $endDate); // Check against end of day

                } catch (Exception $e) {
                    error_log("Error parsing training dates: " . $e->getMessage());
                     // Handle invalid date format from DB
                     $startDate = null; $endDate = null; // Reset dates
                     $courseEnded = false; // Ensure courseEnded is false if dates invalid
                     $courseNotStarted = false;
                }
            }
            $stmt_details->close();
        } else {
             error_log("DB Error (fetch training_details): " . $conn->error);
             // Handle DB error - maybe show a message or use defaults
        }

        // =======================================================================
        // ==== NEW CODE: Update Statuses if Course Ended                   ====
        // =======================================================================
        if ($courseEnded) {

            // Check current statuses to avoid redundant updates
            $needs_in_training_update = false;
            $needs_completion_update = false;

            // Check user_participants.in_training
            $sql_check_it = "SELECT in_training FROM user_participants WHERE participant_id = ?";
            if ($stmt_check_it = $conn->prepare($sql_check_it)) {
                $stmt_check_it->bind_param("i", $participant_id);
                $stmt_check_it->execute();
                $stmt_check_it->bind_result($current_in_training);
                $stmt_check_it->fetch();
                $stmt_check_it->close();
                if ($current_in_training == 1) {
                    $needs_in_training_update = true;
                }
            } else {
                error_log("DB Error (check in_training): Participant $participant_id - " . $conn->error);
                // Decide if you want to proceed despite this error, maybe log and skip
            }

            // Check training_participants.completion_status
            $sql_check_cs = "SELECT completion_status FROM training_participants WHERE participant_id = ? AND training_id = ?";
            if ($stmt_check_cs = $conn->prepare($sql_check_cs)) {
                $stmt_check_cs->bind_param("ii", $participant_id, $training_id);
                $stmt_check_cs->execute();
                $stmt_check_cs->bind_result($current_completion_status);
                $stmt_check_cs->fetch();
                $stmt_check_cs->close();
                 // Only update if it's 'Enrolled' (or potentially other non-final states)
                if ($current_completion_status == 'Enrolled') {
                    $needs_completion_update = true;
                }
            } else {
                error_log("DB Error (check completion_status): Participant $participant_id, Training $training_id - " . $conn->error);
                // Decide if you want to proceed despite this error
            }


            // Perform updates within a transaction if either status needs changing
            if ($needs_in_training_update || $needs_completion_update) {
                $conn->begin_transaction();
                $update_successful = true;

                try {
                    // Update user_participants.in_training
                    if ($needs_in_training_update) {
                        $sql_update_it = "UPDATE user_participants SET in_training = 0 WHERE participant_id = ?";
                        if ($stmt_update_it = $conn->prepare($sql_update_it)) {
                            $stmt_update_it->bind_param("i", $participant_id);
                            if (!$stmt_update_it->execute()) {
                                throw new Exception("Failed to update in_training: " . $stmt_update_it->error);
                            }
                            $stmt_update_it->close();
                            error_log("Updated in_training to 0 for participant_id: " . $participant_id);
                        } else {
                            throw new Exception("Failed to prepare in_training update: " . $conn->error);
                        }
                    }

                    // Update training_participants.completion_status
                    if ($needs_completion_update) {
                        // ==== MODIFIED: Only update completion_status if course has ended ====
                        if ($courseEnded) {
                            $sql_update_cs = "UPDATE training_participants SET completion_status = 'Completed' WHERE participant_id = ? AND training_id = ?";
                            if ($stmt_update_cs = $conn->prepare($sql_update_cs)) {
                                $stmt_update_cs->bind_param("ii", $participant_id, $training_id);
                                if (!$stmt_update_cs->execute()) {
                                    throw new Exception("Failed to update completion_status: " . $stmt_update_cs->error);
                                }
                                $stmt_update_cs->close();
                                error_log("Updated completion_status to Completed for participant_id: " . $participant_id . " in training_id: " . $training_id);

                                 // Since completion status is now 'Completed', the next page load
                                 // *should not* find this training_id based on the modified
                                 // initial training_id fetch query.
                                 // Consider if you want to unset $training_id here to prevent
                                 // the rest of the page rendering the course content?
                                 // $training_id = null; // Example: Stop rendering course details immediately

                            } else {
                                throw new Exception("Failed to prepare completion_status update: " . $conn->error);
                            }
                        }
                    }

                    $conn->commit(); // Commit changes if all updates were successful

                } catch (Exception $e) {
                    $conn->rollback(); // Roll back changes on error
                    $update_successful = false;
                    error_log("Transaction failed for participant $participant_id, training $training_id: " . $e->getMessage());
                    // Optionally display an error to the user, but might be confusing
                    // echo '<div class="alert alert-success">Could not automatically update training status. Please contact support if issues persist.</div>';
                }
            }
        }
        // =======================================================================
        // ============ End of NEW CODE ==========================================
        // =======================================================================

    }
    // --- End Fetch Training Dates ---

    // --- Fetch Completed Module Items for the Current Participant ---
    $completed_module_ids = [];
    if ($participant_id && $training_id) { // Check $training_id again in case it was unset above
        $sql_progress = "SELECT module_id FROM participant_module_progress WHERE participant_id = ? AND training_id = ? AND is_complete = 1";
        if ($stmt_progress = $conn->prepare($sql_progress)) {
            $stmt_progress->bind_param("ii", $participant_id, $training_id);
            $stmt_progress->execute();
            $result_progress = $stmt_progress->get_result();
            while ($row_progress = $result_progress->fetch_assoc()) {
                $completed_module_ids[$row_progress['module_id']] = true;
            }
            $stmt_progress->close();
        } else {
            error_log("DB Error (fetch progress): " . $conn->error);
            echo '<div class="alert alert-success text-center">Could not load saved progress.</div>';
        }
    }
    // --- End Fetch Completed Items ---

    // --- Fetch Count of Completed Trainings ---
    $trainings_completed_count = 0;
    $sql_completed_trainings_count = "SELECT COUNT(DISTINCT training_id) FROM training_participants WHERE participant_id = ? AND completion_status = 'Completed'";
    if ($stmt_completed_count = $conn->prepare($sql_completed_trainings_count)) {
        $stmt_completed_count->bind_param("i", $participant_id);
        $stmt_completed_count->execute();
        $stmt_completed_count->bind_result($trainings_completed_count);
        $stmt_completed_count->fetch();
        $stmt_completed_count->close();
    } else {
        error_log("DB Error (fetch completed trainings count): " . $conn->error);
        $trainings_completed_count = 0; // Default to 0 in case of error
    }
    // --- End Fetch Completed Trainings Count ---

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Participant Dashboard</title>

    <!-- Bootstrap CSS (Using 5.3 for consistency with utilities and bundle) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-utilities@4.3.0/dist/bootstrap-utilities.min.css" rel="stylesheet"> <!-- Keep utilities if needed, but core BS5 includes many -->

    <!-- Font Awesome CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">

    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.min.css">

    <style>
        /* --- Styles from original prompt, slightly adapted for BS5 if needed --- */
        body {
            background-color: #f8f9fa; /* BS5 bg-light equivalent */
        }
        .card {
            background-color: rgba(255, 255, 255, 0.95); /* Slightly less transparent */
            margin-bottom: 1rem;
            border: 1px solid rgba(0,0,0,.125); /* Add subtle border */
            border-radius: 0.375rem; /* BS5 default */
        }

        ul {
            list-style: none;
            padding-left: 0; /* Reset padding */
        }

        input[type="checkbox"] {
            accent-color: green;
            transform: scale(1.2);
            margin-left: 5px;
        }
        input[type="checkbox"]:disabled {
            accent-color: #6c757d; /* Gray out accent for disabled */
            cursor: not-allowed;
        }

        .notice {
            color: #dc3545; /* BS5 danger color */
            font-size: 0.9em;
            margin-top: 0.5rem;
            font-weight: 500; /* Slightly bolder */
        }

        a.lecture-link {
            text-decoration: none;
            /* color: white; */ /* Inherit from button */
        }

        a.lecture-link:hover {
            /* color: white; */ /* Inherit from button */
            text-decoration: none;
        }

        /* Dropdown button styling */
        .dropdown-btn {
            background: linear-gradient(225deg, #9BEC00, #4BB543, #117554);
            color: white;
            padding: 10px 15px;
            border: none;
            cursor: pointer;
            width: 100%;
            text-align: left;
            border-radius: 5px 5px 0 0;
            transition: background-color 0.3s ease;
            font-weight: bold;
            display: flex; /* Use flexbox for alignment */
            justify-content: space-between; /* Space title and caret */
            align-items: center;
        }

        .dropdown-btn.locked {
            background: #e9ecef; /* Lighter gray for locked */
            color: #6c757d; /* Muted text */
            border: 1px solid #dee2e6; /* Add border for locked */
            opacity: 0.8;
            cursor: not-allowed;
        }
         .dropdown-btn.locked:hover {
            background-color: #e9ecef; /* No color change on hover when locked */
        }

        /* Dropdown content */
        .dropdown-container {
            display: none;
            background-color: #ffffff; /* White background */
            padding: 15px;
            border: 1px solid #dee2e6;
            border-top: none;
            border-radius: 0 0 5px 5px;
        }

        /* Caret styling */
        .dropdown-btn .fa-caret-down {
             /* Removed float, flexbox handles alignment */
            transition: transform 0.3s ease;
        }
        .dropdown-btn.active .fa-caret-down {
            transform: rotate(180deg);
        }

        .module-card {
            margin-bottom: 15px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

         .success-gradient {
            background: linear-gradient(225deg, #9BEC00, #4BB543, #117554);
            color: white;
        }

        .success-gradient-reverse {
            background: linear-gradient(135deg, #9BEC00, #4BB543, #117554);
            color: white;
        }


        /* Ensure small-box equivalent styles work */
        .small-box {
            border-radius: 0.375rem;
            box-shadow: 0 0 1px rgba(0,0,0,.125),0 1px 3px rgba(0,0,0,.2);
            display: block;
            margin-bottom: 20px;
            position: relative;
            color: #fff; /* Ensure text is white */
        }
        .small-box > .inner {
            padding: 10px;
        }
        .small-box h3, .small-box p {
            margin: 0 0 10px 0;
            padding: 0;
            z-index: 5; /* Keep text above icon */
        }
         .small-box h3 {
            font-size: 2.2rem;
            font-weight: 700;
         }
        .small-box p {
            font-size: 1rem;
        }
        .small-box .icon {
            color: rgba(255,255,255,.3); /* Lighter icon color for contrast */
            z-index: 0;
            transition: all .3s linear;
            position: absolute;
            top: -10px; /* Adjust as needed */
            right: 15px;
            font-size: 70px; /* Adjust size */
        }
        .small-box:hover .icon {
            font-size: 75px; /* Slightly larger on hover */
            color: rgba(255,255,255,.5);
        }
        .small-box > .small-box-footer {
            background-color: rgba(0,0,0,.1);
            color: rgba(255,255,255,.8);
            display: block;
            padding: 3px 0;
            position: relative;
            text-align: center;
            text-decoration: none;
            z-index: 10;
        }
        .small-box > .small-box-footer:hover {
            background-color: rgba(0,0,0,.15);
            color: #fff;
        }


        .status-label {
            width: 120px; /* Fixed width for alignment */
            font-weight: bold;
            font-size: 0.9em;
        }
         /* Style for the overlay */
        .content-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(33, 37, 41, 0.85); /* Darker overlay (BS5 dark) */
            color: #ffc107; /* Bootstrap success color */
            font-size: 1.5rem;
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 20px;
            z-index: 10;
            border-radius: 0.375rem; /* Match card radius */
        }

        /* Table styling adjustments */
        .table-sm > :not(caption) > * > * {
             padding: 0.4rem 0.4rem; /* Adjust padding for sm table */
        }
        .table-borderless > :not(caption) > * > * {
            border-bottom-width: 0;
        }
        .align-middle {
            vertical-align: middle!important;
        }
        thead th {
            border-bottom: 2px solid #dee2e6; /* Add bottom border to header */
        }

    </style>
</head>

<body class="bg-light">

    <?php
        // Ensure session is started AT THE VERY TOP
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Include Database Configuration
        require_once '../includes/config.php'; // Use require_once for essential includes

        // --- Check Participant and Get Training ID and in_training status ---
        if (!isset($_SESSION["participant_id"])) {
            // Redirect or display error if participant not logged in
            die('<div class="vh-100 d-flex justify-content-center align-items-center"><div class="alert alert-danger">User not logged in. Please <a href="/login.php">login</a>.</div></div>');
        }
        $participant_id = $_SESSION["participant_id"];
        $training_id = null; // Initialize
        $in_training_status = 0; // Default to not in training

        // Get training_id and in_training status
        $sql_training_info = "SELECT tp.training_id, up.in_training
                           FROM training_participants tp
                           INNER JOIN user_participants up ON tp.participant_id = up.participant_id
                           WHERE tp.participant_id = ? AND tp.completion_status = 'Enrolled'"; // Still fetch only 'Enrolled' trainings
        if ($stmt_info = $conn->prepare($sql_training_info)) {
            $stmt_info->bind_param("i", $participant_id);
            $stmt_info->execute();
            $stmt_info->bind_result($training_id, $in_training_status);
            $stmt_info->fetch();
            $stmt_info->close();
        } else {
            error_log("DB Error (fetch training_id and in_training): " . $conn->error); // Log errors
            die('<div class="alert alert-danger text-center">Database error fetching training assignment. Please contact support.</div>');
        }

        // If in_training_status is 0, effectively set no training assigned
        if ($in_training_status == 0) {
            $training_id = null; // No training if not marked as in_training
        }
        // --- End Check Participant and Training ---


        // --- Fetch Training Dates and Calculate Status ---
        $startDate = null;
        $endDate = null;
        $courseNotStarted = false;
        $courseEnded = false;
        $remaining_seconds = 0;
        $courseTitle = "Training"; // Default title
        $courseDescription = "No description available."; // Default description

        if ($training_id) {
            // Fetch Training Details (Title, Desc, Dates)
            $sql_training_details = "SELECT training_title, description, start_date, end_date FROM training WHERE training_id = ?";
            if ($stmt_details = $conn->prepare($sql_training_details)) {
                $stmt_details->bind_param("i", $training_id);
                $stmt_details->execute();
                $stmt_details->bind_result($db_title, $db_desc, $start_date_str, $end_date_str);
                if ($stmt_details->fetch()) {
                    $courseTitle = htmlspecialchars($db_title, ENT_QUOTES, 'UTF-8');
                    $courseDescription = htmlspecialchars($db_desc, ENT_QUOTES, 'UTF-8');
                    // Use DateTime objects for more robust date handling
                    try {
                        $startDateTime = new DateTime($start_date_str);
                        $endDateTime = new DateTime($end_date_str);
                        // Set end date time to end of day for accurate comparison
                        $endDateTime->setTime(23, 59, 59);

                        $startDate = $startDateTime->getTimestamp();
                        $endDate = $endDateTime->getTimestamp();

                        $now = time();
                        $remaining_seconds = ($now < $endDate) ? $endDate - $now : 0;
                        $courseNotStarted = ($now < $startDate);
                        $courseEnded = ($now > $endDate); // Check against end of day

                    } catch (Exception $e) {
                        error_log("Error parsing training dates: " . $e->getMessage());
                         // Handle invalid date format from DB
                         $startDate = null; $endDate = null; // Reset dates
                    }
                }
                $stmt_details->close();
            } else {
                 error_log("DB Error (fetch training_details): " . $conn->error);
                 // Handle DB error - maybe show a message or use defaults
            }
        }
        // --- End Fetch Training Dates ---

        // --- Fetch Completed Module Items for the Current Participant ---
        $completed_module_ids = [];
        if ($participant_id && $training_id) {
            $sql_progress = "SELECT module_id FROM participant_module_progress WHERE participant_id = ? AND training_id = ? AND is_complete = 1";
            if ($stmt_progress = $conn->prepare($sql_progress)) {
                $stmt_progress->bind_param("ii", $participant_id, $training_id);
                $stmt_progress->execute();
                $result_progress = $stmt_progress->get_result();
                while ($row_progress = $result_progress->fetch_assoc()) {
                    // Store completed module IDs (the actual ID from the 'modules' table)
                    $completed_module_ids[$row_progress['module_id']] = true;
                }
                $stmt_progress->close();
            } else {
                error_log("DB Error (fetch progress): " . $conn->error);
                // Handle error fetching progress if necessary, maybe show a success
                 echo '<div class="alert alert-success text-center">Could not load saved progress.</div>';
            }
        }
        // --- End Fetch Completed Items ---

        // --- Fetch Count of Completed Trainings ---
        $trainings_completed_count = 0;
        $sql_completed_trainings_count = "SELECT COUNT(DISTINCT training_id) FROM training_participants WHERE participant_id = ? AND completion_status = 'Completed'";
        if ($stmt_completed_count = $conn->prepare($sql_completed_trainings_count)) {
            $stmt_completed_count->bind_param("i", $participant_id);
            $stmt_completed_count->execute();
            $stmt_completed_count->bind_result($trainings_completed_count);
            $stmt_completed_count->fetch();
            $stmt_completed_count->close();
        } else {
            error_log("DB Error (fetch completed trainings count): " . $conn->error);
            $trainings_completed_count = 0; // Default to 0 in case of error
        }
        // --- End Fetch Completed Trainings Count ---
    ?>

    <div class="container-fluid mt-4 mb-5"> <!-- Added mb-5 for bottom spacing -->
        <div class="row"> <!-- START MAIN ROW -->

            <!-- Main Content Area (col-lg-8) -->
            <div class="col-lg-8">
                <?php if (!$training_id): // Show message if no training assigned ?>
                    <div class="card border-success"> <!-- BS5 border color utility -->
                         <div class="card-header text-center bg-success text-white">
                            <h4 class="card-title mb-0">
                                <i class="fas fa-exclamation-triangle mr-2"></i> No Assigned Courses <!-- mr-2 for margin end -->
                            </h4>
                        </div>
                        <div class="card-body text-center">
                            <p class="card-text fs-5">You are not currently assigned to any training program.</p>
                            <p class="card-text text-muted">Please wait for course assignment or contact the administrator if you believe this is an error.</p>
                        </div>
                    </div>
                <?php else: // Display training content (Modules and Progress Bar) ?>

                    <!-- Hidden inputs to store IDs needed by JavaScript -->
                    <input type="hidden" id="participantId" value="<?= htmlspecialchars($participant_id, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" id="trainingId" value="<?= htmlspecialchars($training_id, ENT_QUOTES, 'UTF-8') ?>">

                    <!-- Main Content Area with Relative Positioning for Overlay -->
                    <div class="position-relative">

                         <!-- The actual content - initially potentially hidden by overlay -->
                        <div id="training-content">
                            <div class="card shadow-sm mb-4"> <!-- Add shadow -->
                                <div class="card-header text-center success-gradient">
                                    <h2 class="my-3"> <!-- my-3 for vertical margin -->
                                        <?php echo $courseTitle; ?>
                                    </h2>
                                </div>
                                <div class="card-body">
                                    <p><strong>Course Description:</strong> <?php echo nl2br($courseDescription); // Use nl2br to respect newlines ?></p>

                                    <div class="progress mb-3 rounded" style="height: 25px;">
                                        <div id="progress-bar" class="progress-bar progress-bar-striped bg-success progress-bar-animated fw-bold" role="progressbar"
                                            style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%
                                        </div>
                                    </div>
                                    <div>
                                        <p class="notice text-center"><strong><i class="fas fa-info-circle mr-1"></i>Notice:</strong> Complete all items in the current Module (Lectures, Activities, Quizzes) to unlock the next Module.</p>
                                    </div>
                                    <hr> <!-- Separator -->

                                    <?php
                                        // --- Fetch Module Names ---
                                        $sql_modules = "SELECT module_name
                                                        FROM modules
                                                        WHERE training_id = ?
                                                        GROUP BY module_name
                                                        ORDER BY MIN(module_id) ASC"; // Order modules correctly

                                        $stmt_modules = $conn->prepare($sql_modules);
                                        $module_names = []; // Initialize

                                        if ($stmt_modules) {
                                            $stmt_modules->bind_param("i", $training_id);
                                            $stmt_modules->execute();
                                            $result_modules = $stmt_modules->get_result();
                                            if ($result_modules->num_rows > 0) {
                                                while ($row = $result_modules->fetch_assoc()) {
                                                    $module_names[] = $row["module_name"];
                                                }
                                            }
                                            $stmt_modules->close();
                                        } else {
                                             error_log("DB Error (fetch module names): " . $conn->error);
                                             echo '<div class="alert alert-danger">Error loading modules.</div>';
                                        }
                                        // --- End Fetch Module Names ---


                                        // --- Loop Through Modules ---
                                        $first_module = true; // Flag for the first module

                                        if (empty($module_names)) {
                                            echo '<p class="text-center text-muted mt-3">No modules found for this training.</p>';
                                        } else {
                                            foreach ($module_names as $index => $module_name) {
                                                $moduleHtmlId = "module-" . ($index + 1); // Unique HTML ID for container
                                                $nextModuleHtmlId = ($index + 1 < count($module_names)) ? "module-" . ($index + 2) : null;

                                                // Determine if locked (all modules except the first start locked)
                                                // Check if *all* items in the *previous* module are complete.
                                                // This check is now mainly handled by JS, but we set the initial state here.
                                                $isLocked = !$first_module; // Simplistic initial lock state
                                                $lockedClass = $isLocked ? 'locked' : 'unlocked';
                                                $buttonClasses = $isLocked ? 'dropdown-btn locked' : 'dropdown-btn unlocked'; // Base classes
                                                $lockedAttributes = $isLocked ? 'disabled' : '';


                                                echo '<div class="module-card" data-module-html-id="' . $moduleHtmlId . '" data-next-module-html-id="' . $nextModuleHtmlId . '">';
                                                // Apply locked styles directly to the button if locked
                                                echo '<button class="' . $buttonClasses . '" ' . $lockedAttributes . '>';
                                                echo '<span>' . htmlspecialchars($module_name, ENT_QUOTES, 'UTF-8') . '</span> <i class="fa fa-caret-down"></i>';
                                                echo '</button>';

                                                echo '<div class="dropdown-container" id="' . $moduleHtmlId . '">'; // Container linked by ID

                                                // Fetch module-specific items
                                                $sql_items = "SELECT module_id, module_description, module_type, file_path, link_url FROM modules WHERE training_id = ? AND module_name = ? ORDER BY module_id ASC";
                                                $stmt_items = $conn->prepare($sql_items);

                                                if ($stmt_items) {
                                                    $stmt_items->bind_param("is", $training_id, $module_name);
                                                    $stmt_items->execute();
                                                    $result_items = $stmt_items->get_result();
                                                    $stmt_items->close();

                                                    // Use a table for better alignment
                                                    echo '<table class="table table-sm table-borderless align-middle mb-0">';
                                                    echo '<thead><tr><th>Task</th><th class="text-center status-label">Status</th></tr></thead>';
                                                    echo '<tbody>';

                                                    if ($result_items->num_rows > 0) {
                                                        while ($item = $result_items->fetch_assoc()) {
                                                            $itemID = $item["module_id"]; // The actual module ID from the DB
                                                            $itemDescription = htmlspecialchars($item["module_description"], ENT_QUOTES, 'UTF-8');
                                                            $itemType = $item["module_type"];
                                                            $filePath = !empty($item["file_path"]) ? htmlspecialchars($item["file_path"], ENT_QUOTES, 'UTF-8') : "#";
                                                            $linkUrl = !empty($item["link_url"]) ? htmlspecialchars($item["link_url"], ENT_QUOTES, 'UTF-8') : "#";

                                                            // Unique IDs for HTML elements (safer prefixes)
                                                            $checkboxId = "item-check-" . $moduleHtmlId . "-" . $itemID;
                                                            $buttonId = strtolower($itemType) . "-btn-" . $moduleHtmlId . "-" . $itemID;

                                                            // --- Check if this item is completed based on loaded progress ---
                                                            $isChecked = isset($completed_module_ids[$itemID]);
                                                            $checkedAttribute = $isChecked ? 'checked' : '';

                                                            // --- Determine initial disabled state ---
                                                            $initially_disabled = $isChecked; // Always disable if completed
                                                            if ($itemType !== 'Lecture') {
                                                                $initially_disabled = true; // Non-lectures start disabled until lectures done (JS enables) or if completed
                                                            }
                                                            $disabledAttribute = $initially_disabled ? 'disabled' : '';
                                                            $buttonDisabledAttribute = ($itemType !== 'Lecture') ? 'disabled' : ''; // Buttons also start disabled


                                                            echo '<tr>';
                                                            // Column 1: Description and Button
                                                            echo '<td>';
                                                            $icon = '';
                                                            $button_class = 'btn btn-sm ml-2 mt-1'; // Base button classes
                                                            $button_text = '';

                                                            switch ($itemType) {
                                                                case "Lecture":
                                                                    $icon = 'fas fa-book-open mr-1';
                                                                    echo "<i class='$icon text-success'></i> Lecture: " . $itemDescription;
                                                                    if ($filePath !== '#') {
                                                                        echo ' <a href="' . $filePath . '" class="btn btn-sm btn-outline-success ml-2" target="_blank"><i class="fas fa-external-link-alt mr-1"></i>View Lesson</a>';
                                                                    }
                                                                    break;
                                                                case "Quiz":
                                                                    $icon = 'fas fa-pencil-alt mr-1';
                                                                    $button_class .= ' btn-success quiz-btn';
                                                                    $button_text = 'Take Quiz';
                                                                    echo "<i class='$icon text-success'></i> Quiz: " . $itemDescription;
                                                                    break;
                                                                case "Activity":
                                                                    $icon = 'fas fa-tasks mr-1';
                                                                    $button_class .= ' btn-success activity-btn'; // Changed color
                                                                    $button_text = 'Submit Activity';
                                                                     echo "<i class='$icon text-success'></i> Activity: " . $itemDescription;
                                                                    break;
                                                                case "Examination":
                                                                    $icon = 'fas fa-graduation-cap mr-1';
                                                                    $button_class .= ' btn-success exam-btn';
                                                                    $button_text = 'Take Post-Test';
                                                                    echo "<i class='$icon text-success'></i> Post-Test: " . $itemDescription;
                                                                    break;
                                                                case "Remedial":
                                                                    $icon = 'fas fa-redo mr-1';
                                                                    $button_class .= ' btn-success remedial-btn'; // Changed color
                                                                    $button_text = 'Take Remedial';
                                                                    echo "<i class='$icon text-success'></i> Remedial: " . $itemDescription;
                                                                    break;
                                                                default:
                                                                    echo $itemDescription;
                                                            }

                                                            // Add button for actionable types if link exists
                                                            if (in_array($itemType, ["Quiz", "Activity", "Examination", "Remedial"]) && $linkUrl !== '#') {
                                                                 echo ' <button class="' . $button_class . '" id="' . $buttonId . '" ';
                                                                 echo ' data-bs-toggle="modal" data-bs-target="#urlModal" ';
                                                                 echo ' data-link="' . $linkUrl . '" ';
                                                                 echo ' data-activity-type="' . strtolower($itemType) . '" ';
                                                                 echo ' data-checkbox-id="' . $checkboxId . '" ';
                                                                 echo ' data-module-html-id="' . $moduleHtmlId . '" '; // Pass HTML ID if needed by JS
                                                                 echo $buttonDisabledAttribute . '>'; // Initially disable button
                                                                 echo '<i class="' . $icon . '"></i> ' . $button_text . '</button>';
                                                            }
                                                            echo '</td>';

                                                            // Column 2: Checkbox (Status)
                                                            echo '<td class="text-center">';
                                                            echo '<input type="checkbox" class="form-check-input item-checkbox" ';
                                                            echo 'data-item-type="' . strtolower($itemType) . '" ';
                                                            echo 'data-title="' . $itemDescription . '" ';
                                                            echo 'id="' . $checkboxId . '" ';
                                                            echo 'data-module-html-id="' . $moduleHtmlId . '" '; // HTML ID of the module container
                                                            echo 'data-module-id="' . $itemID . '" '; // Database Module ID
                                                            echo $checkedAttribute . ' '; // Set if loaded as complete
                                                            echo $disabledAttribute . '>'; // Set initial disabled state
                                                            echo '</td>';
                                                            echo '</tr>';

                                                            // Add notice for Remedial
                                                            if ($itemType === "Remedial") {
                                                                echo '<tr><td colspan="2"><p class="notice mb-0 small text-muted fst-italic"><i class="fas fa-exclamation-circle mr-1"></i>Remedial exams are typically only required if you did not pass the main post-test. Please follow instructions from your coordinator.</p></td></tr>';
                                                            }
                                                        }
                                                    } else {
                                                        echo '<tr><td colspan="2" class="text-muted text-center">No items found for this module.</td></tr>';
                                                    }

                                                    echo '</tbody></table>';
                                                } else {
                                                    error_log("DB Error (fetch module items for $module_name): " . $conn->error);
                                                    echo '<div class="alert alert-danger">Error loading items for this module.</div>';
                                                }
                                                echo '</div>'; // Close dropdown-container
                                                echo '</div>'; // Close module-card

                                                $first_module = false; // Subsequent modules are not the first
                                            } // end foreach module_names
                                        } // end else for empty module_names check
                                    ?>
                                </div> <!-- End Card Body -->
                            </div> <!-- End Main Training Card -->
                        </div> <!-- End training-content -->

                        <!-- Conditional Overlay based on PHP variables -->
                        <?php if ($courseNotStarted || $courseEnded): ?>
                            <div class="content-overlay rounded"> <!-- Added rounded class -->
                                <h4 class="m-0 text-center lh-base"> <!-- Centered text, line height -->
                                    <?php
                                        if ($courseNotStarted) {
                                            echo "<i class='fas fa-hourglass-start fa-2x mb-3'></i><br>Sorry, the training is yet to start.<br><small class='text-white-50'>Please try accessing the course on or after</small> <small class='text-warning font-weight-bold'>" . ($startDate ? date('F j, Y', $startDate) : 'the start date') . ".</small>";
                                        } else if ($courseEnded) {
                                            echo "<i class='fas fa-calendar-times fa-2x mb-3'></i><br>Sorry, the training has ended.<br><small class='text-white-50'>Please contact your coordinator for further assistance.</small>";
                                        }
                                    ?>
                                </h4>
                            </div>
                        <?php endif; ?>

                    </div> <!-- End Position Relative Wrapper -->

                <?php endif; // End of main else for displaying training content ?>
            </div> <!-- END Main Content Area (col-lg-8) -->


            <!-- Sidebar Section (col-lg-4) -->
            <div class="col-lg-4">
                <!-- Trainings Completed -->
                <div class="small-box success-gradient shadow-sm">
                    <div class="inner">
                        <h3><?php echo $trainings_completed_count; ?></h3>
                        <p>Trainings Completed</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <!-- <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a> -->
                </div>

                <!-- Pending Lectures -->
                <div class="card shadow-sm mb-3">
                    <div class="card-header success-gradient"><strong><i class="fas fa-book-open mr-2"></i>Pending Lectures</strong></div>
                    <div class="card-body p-2"> <!-- Less padding -->
                        <ul id="pendingLessons" class="list-group list-group-flush">
                            <?php if (!$training_id): ?>
                                <li class="list-group-item text-muted text-center"><small>No training assigned.</small></li>
                            <?php else: ?>
                                <li class="list-group-item">Loading...</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>

                <!-- Pending Activities/Assessments -->
                <div class="card shadow-sm mb-3">
                    <div class="card-header success-gradient"><strong><i class="fas fa-tasks mr-2"></i>Pending Activities & Assessments</strong></div>
                    <div class="card-body p-2">
                        <ul id="pendingQuizzes" class="list-group list-group-flush">
                            <?php if (!$training_id): ?>
                                <li class="list-group-item text-muted text-center"><small>No training assigned.</small></li>
                            <?php else: ?>
                                <li class="list-group-item">Loading...</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>

                 <!-- Time Remaining -->
                <div class="card shadow-sm mb-3">
                    <div class="card-header success-gradient">
                        <strong><i class="fas fa-clock mr-2"></i>Time Remaining</strong>
                    </div>
                    <div class="card-body">
                        <?php if (!$training_id): ?>
                            <p class="mb-1"><strong>Status: <span class="badge bg-secondary text-white">No Training Assigned</span></strong></p>
                            <p class="text-muted small mb-0">You are not currently enrolled in a training.</p>
                        <?php elseif ($courseNotStarted): ?>
                            <p class="mb-1"><strong>Status: <span class="badge bg-info text-dark">Not Started</span></strong></p>
                            <p class="text-muted small mb-0">Starts on: <?= $startDate ? date('M j, Y, g:i a', $startDate) : 'N/A' ?></p>
                        <?php elseif ($courseEnded): ?>
                            <p class="mb-1"><strong>Status: <span class="badge bg-danger">Ended</span></strong></p>
                            <p class="text-muted small mb-0">Ended on: <?= $endDate ? date('M j, Y, g:i a', $endDate) : 'N/A' ?></p>
                        <?php elseif ($startDate && $endDate): ?>
                            <p class="mb-1"><strong>Time left: <span class="text-danger fw-bold" id="countdown">Calculating...</span></strong></p>
                            <p class="text-muted small mb-0">Ends on: <?= date('M j, Y', $endDate) ?></p>
                        <?php else: ?>
                            <p class="mb-1"><strong>Status: <span class="badge bg-success text-dark">Dates Unavailable</span></strong></p>
                            <p class="text-muted small mb-0">Training dates could not be determined.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Calendar -->
                <div class="card mt-3">
                                    <div class="card-header success-gradient text-white">
                                        <strong><i class="fas fa-calendar-alt mr-2"></i>Calendar</strong>
                                    </div>
                                    <div class="card-body">
                                        <div class="calendar-container h-50">
                                            <?php
                                            include '../includes/config.php';

                                            if (!isset($_SESSION["participant_id"])) {
                                                die("User not logged in."); // Or redirect to login page
                                            }

                                            $participant_id = $_SESSION["participant_id"];

                                            // Get training_id from training_participants table
                                            $sql_training_id = "SELECT training_id FROM training_participants WHERE participant_id = ?";
                                            $stmt = $conn->prepare($sql_training_id);
                                            $stmt->bind_param("i", $participant_id);
                                            $stmt->execute();
                                            $stmt->bind_result($training_id);
                                            $stmt->fetch();
                                            $stmt->close();

                                            // Fetch training start_date and end_date based on training_id
                                            $startDate = null;
                                            $endDate = null;

                                            if ($training_id) {
                                                $sql_dates = "SELECT start_date, end_date FROM training WHERE training_id = ?";
                                                $stmt = $conn->prepare($sql_dates);
                                                $stmt->bind_param("i", $training_id);
                                                $stmt->execute();
                                                $stmt->bind_result($start_date, $end_date);
                                                if ($stmt->fetch()) {
                                                    $startDate = strtotime($start_date);
                                                    $endDate = strtotime($end_date);
                                                }
                                                $stmt->close();
                                            }

                                            // Get current month and year from GET parameters (for navigation)
                                            $month = isset($_GET['month']) ? (int)$_GET['month'] : date('m');
                                            $year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

                                            // Adjust for previous and next month navigation
                                            if ($month < 1) {
                                                $month = 12;
                                                $year--;
                                            } elseif ($month > 12) {
                                                $month = 1;
                                                $year++;
                                            }

                                            function generateCalendar($month, $year, $startDate, $endDate) {
                                                $daysOfWeek = ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'];
                                                $firstDay = mktime(0, 0, 0, $month, 1, $year);
                                                $totalDays = date('t', $firstDay);
                                                $startingDay = date('w', $firstDay);
                                                $currentDay = date('j'); // Get today's date

                                                echo "<table class='table table-bordered text-center'>";
                                                echo "<thead class='success-gradient'>";
                                                echo "<tr>";

                                                // Previous and Next month navigation
                                                $prevMonth = $month - 1;
                                                $prevYear = $year;
                                                if ($prevMonth < 1) {
                                                    $prevMonth = 12;
                                                    $prevYear--;
                                                }

                                                $nextMonth = $month + 1;
                                                $nextYear = $year;
                                                if ($nextMonth > 12) {
                                                    $nextMonth = 1;
                                                    $nextYear++;
                                                }

                                                echo "<th colspan='7' class='text-center'>";
                                                echo "<div class='d-flex justify-content-between align-items-center'>";
                                                echo "<a href='?month=$prevMonth&year=$prevYear' class='text-white btn btn-transparent'>« Prev</a>";
                                                echo "<p class=' text-white m-0 px-3 py-2' style='font-size: 20px;'>" . date('F Y', $firstDay) . "</p>";
                                                echo "<a href='?month=$nextMonth&year=$nextYear' class='text-white btn btn-transparent'>Next »</a>";
                                                echo "</div>";
                                                echo "</th>";


                                                echo "</tr><tr>";

                                                // Print day headers
                                                foreach ($daysOfWeek as $day) {
                                                    echo "<th>$day</th>";
                                                }
                                                echo "</tr></thead><tbody><tr>";

                                                // Print empty cells before the first day
                                                for ($i = 0; $i < $startingDay; $i++) {
                                                    echo "<td></td>";
                                                }

                                                // Print days of the month
                                                for ($day = 1; $day <= $totalDays; $day++) {
                                                    if (($startingDay + $day - 1) % 7 == 0) {
                                                        echo "</tr><tr>"; // Start a new row every Sunday
                                                    }

                                                    $currentTimestamp = mktime(0, 0, 0, $month, $day, $year);
                                                    $isWithinRange =  ($currentTimestamp >= $startDate && $currentTimestamp <= $endDate);
                                                    $class = "p-2"; // Default padding
                                                    $tooltip = ""; // Default tooltip

                                                    // Apply Bootstrap classes for highlighting and tooltips
                                                   if ($day == date('j') && $month == date('m') && $year == date('Y') && $currentTimestamp == $startDate) {
                                                        $class .= " bg-info text-white font-weight-bold";
                                                        $tooltip = "Today is the first day of this course";
                                                    }
                                                    elseif ($currentTimestamp == $startDate) {
                                                        $class .= " bg-success text-white font-weight-bold";
                                                        $tooltip = "First day of this course";
                                                    }elseif ($day == date('j') && $month == date('m') && $year == date('Y') && $currentTimestamp == $endDate) {
                                                        $class .= " bg-danger text-white font-weight-bold";
                                                        $tooltip = "Today is the last day of this course";
                                                    }elseif ($currentTimestamp == $endDate) {
                                                        $class .= " bg-success text-white font-weight-bold";
                                                        $tooltip = "Last day of this course";
                                                    }elseif ($day == date('j') && $month == date('m') && $year == date('Y')) {
                                                        $class .= " bg-info text-white font-weight-bold";
                                                        $tooltip = "Today";
                                                    }elseif ($isWithinRange) {
                                                        $class .= " table-success opacity-75"; // Lighter highlight for range
                                                    }

                                                    // Add tooltip attribute if there's a message
                                                    $tooltipAttr = $tooltip ? "data-bs-toggle='tooltip' title='$tooltip'" : "";

                                                    echo "<td class='$class' $tooltipAttr>$day</td>";
                                                }


                                                echo "</tr></tbody></table>";
                                            }

                                            echo generateCalendar($month, $year, $startDate, $endDate);
                                            ?>
                                        </div>
                                    </div>
                                </div> <!-- End Calendar Card -->

            </div> <!-- END Sidebar Section (col-lg-4) -->

        </div> <!-- END MAIN ROW -->
    </div> <!-- End Container Fluid -->


    <!-- Modal for Google Form Links / External Links -->
    <div class="modal fade" id="urlModal" tabindex="-1" aria-labelledby="urlModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="urlModalLabel">Task Link</h5>
                </div>
                <div class="modal-body">
                    <p>Please complete the task using the link below. Once finished and submitted (if required), click "Mark as Done" here.</p>
                    <div class="text-center mt-3 mb-4"> <!-- Centered button -->
                         <a href="#" target="_blank" id="modalGformLink" class="btn btn-success btn-lg">
                            <i class="fas fa-external-link-alt mr-2"></i>Open Link
                        </a>
                    </div>
                     <p class="text-muted small fst-italic"><i class="fas fa-info-circle mr-1"></i>Ensure you fully complete and submit the task in the new window/tab before marking it as done here.</p>
                    <input type="hidden" id="modalCheckboxId"> <!-- Hidden input to store target checkbox ID -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="modalMarkDoneBtn" data-bs-dismiss="modal"><i class="fas fa-check mr-2"></i>Mark as Done</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Core JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.all.min.js"></script>
    <script>
    $(document).ready(function () {

        // --- Constants ---
        const participantId = $('#participantId').val(); // Will be undefined if no training
        const trainingId = $('#trainingId').val(); // Will be undefined if no training
        const SAVE_PROGRESS_URL = 'save_progress.php'; // Adjust path if needed - **IMPORTANT: Verify this path!**
        const hasTraining = !!trainingId; // Boolean flag

        // --- Initializations ---
        initializeTooltips();
        setupDropdownToggles();
        setupModalTriggers();
        setupModalMarkDone();
        setupCheckboxChangeListeners();

        // Only run training-specific updates if training exists
        if (hasTraining) {
            updateProgressAndUnlocks(); // Initial UI update based on loaded state
            updatePendingItems();       // Initial pending list update
            startCountdownTimer();
        } else {
            // Handle sidebar display when no training (e.g., clear loading states if needed)
            $('#pendingLessons li:contains("Loading...")').remove();
            $('#pendingQuizzes li:contains("Loading...")').remove();
            // Ensure countdown shows appropriate message (already handled by PHP, but belt-and-suspenders)
            const countdownElement = document.getElementById("countdown");
            if (countdownElement && countdownElement.textContent === "Calculating...") {
                 countdownElement.textContent = "N/A";
                 countdownElement.classList.remove('text-danger', 'fw-bold');
                 countdownElement.classList.add('text-muted');
            }
        }


        // --- Function Definitions ---

        function initializeTooltips() {
            const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
        }

        function setupDropdownToggles() {
             $(document).on('click', '.dropdown-btn:not(.locked)', function () {
                $(this).toggleClass("active");
                $(this).next(".dropdown-container").slideToggle(300);
            });
             $(document).on('click', '.dropdown-btn.locked', function(e) {
                e.preventDefault(); e.stopPropagation();
             });
        }

        function setupModalTriggers() {
             $(document).on('click', '.quiz-btn:not(:disabled), .activity-btn:not(:disabled), .exam-btn:not(:disabled), .remedial-btn:not(:disabled)', function() {
                const button = $(this);
                const link = button.data("link");
                const checkboxId = button.data("checkbox-id");
                const activityType = button.data("activity-type");

                let title = activityType.charAt(0).toUpperCase() + activityType.slice(1); // Capitalize
                $('#urlModalLabel').text(`${title} Task`);
                $('#modalGformLink').attr('href', link);
                $('#modalCheckboxId').val(checkboxId); // Store the target checkbox ID
             });
        }

        function setupModalMarkDone() {
            $('#modalMarkDoneBtn').on('click', function () {
                if (!hasTraining) return; // Don't run if no training

                const checkboxId = $('#modalCheckboxId').val();
                const checkbox = document.getElementById(checkboxId);

                if (!checkbox) {
                    console.error("ModalMarkDoneBtn: Checkbox not found with ID:", checkboxId);
                    Swal.fire('Error', 'Could not find the item to mark as done.', 'error');
                    return;
                }

                const moduleId = checkbox.getAttribute("data-module-id");

                if (!moduleId) {
                     console.error("ModalMarkDoneBtn: Missing data-module-id on checkbox:", checkboxId);
                     Swal.fire('Error', 'Could not mark item as done due to missing data.', 'error');
                     return;
                }

                saveProgress(moduleId)
                    .then(success => {
                        if (success) {
                            checkbox.disabled = false;
                            checkbox.checked = true;
                            checkbox.disabled = true;
                            handleItemCompletion(checkbox);
                        }
                    });

                 $('#modalCheckboxId').val('');
            });
        }

        function setupCheckboxChangeListeners() {
             $(document).on('change', '.item-checkbox', function(event) {
                 if (!hasTraining) return; // Don't run if no training

                 const changedCheckbox = event.target;
                 const itemType = changedCheckbox.getAttribute('data-item-type');
                 const moduleHtmlId = changedCheckbox.getAttribute("data-module-html-id");
                 const moduleId = changedCheckbox.getAttribute("data-module-id");

                 if (itemType === 'lecture') {
                     checkAndEnableModuleItems(moduleHtmlId);

                     if (changedCheckbox.checked && moduleId) {
                         saveProgress(moduleId)
                            .then(success => {
                                if (success) {
                                     changedCheckbox.disabled = true;
                                     handleItemCompletion(changedCheckbox);
                                } else {
                                    changedCheckbox.checked = false;
                                }
                            });
                     } else if (!changedCheckbox.checked) {
                          handleItemCompletion(changedCheckbox);
                     }
                 }
             });
        }

        // Central function to handle UI updates AFTER any item's state changes
        function handleItemCompletion(checkboxElement) {
            if (!hasTraining) return; // Don't run if no training

             updateProgressAndUnlocks();
             updatePendingItems();
             const moduleHtmlId = checkboxElement.getAttribute("data-module-html-id");
             if (moduleHtmlId) {
                 checkAndEnableModuleItems(moduleHtmlId);
             }
        }

        // Function to check if all lectures in a module are done and enable non-lecture BUTTONS
        function checkAndEnableModuleItems(moduleHtmlId) {
             if (!hasTraining) return; // Don't run if no training

            const moduleContainer = document.getElementById(moduleHtmlId);
            if (!moduleContainer) return;

            const parentCard = $(moduleContainer).closest('.module-card');
            if (parentCard.find('.dropdown-btn').hasClass('locked')) {
                return;
            }

            const lectureCheckboxes = moduleContainer.querySelectorAll('.item-checkbox[data-item-type="lecture"]');
            const nonLectureButtons = moduleContainer.querySelectorAll('.quiz-btn, .activity-btn, .exam-btn, .remedial-btn');

            let allLecturesDone = lectureCheckboxes.length === 0 || Array.from(lectureCheckboxes).every(cb => cb.checked);

            nonLectureButtons.forEach(button => {
                const correspondingCheckboxId = button.getAttribute('data-checkbox-id');
                const correspondingCheckbox = document.getElementById(correspondingCheckboxId);
                const isItemCompleted = correspondingCheckbox ? correspondingCheckbox.checked : false;

                if (allLecturesDone && !isItemCompleted) {
                    button.removeAttribute("disabled");
                } else {
                    button.setAttribute("disabled", "true");
                }
            });
        }

        // Combined function for calculating progress and checking module unlocks
        function updateProgressAndUnlocks() {
             if (!hasTraining) return; // Don't run if no training

            const allCheckboxes = document.querySelectorAll('.item-checkbox');
            const completedCheckboxes = document.querySelectorAll('.item-checkbox:checked');
            const totalItems = allCheckboxes.length;
            const completedItems = completedCheckboxes.length;

            // Update Progress Bar
            let progress = totalItems === 0 ? 100 : Math.round((completedItems / totalItems) * 100);
            const progressBar = document.getElementById("progress-bar");
            if (progressBar) {
                progressBar.style.width = progress + "%";
                progressBar.setAttribute("aria-valuenow", progress);
                progressBar.textContent = progress + "%";

                // Course Completion Check
                if (progress === 100 && totalItems > 0 && !progressBar.dataset.completed) {
                     Swal.fire({
                          title: 'Congratulations!',
                          html: 'You\'ve completed all items in this training!<br>Please check with your coordinator for the next steps or certificate availability.',
                          icon: 'success',
                          confirmButtonText: 'Okay',
                          allowOutsideClick: false, allowEscapeKey: false,
                     });
                     progressBar.dataset.completed = "true";
                } else if (progress < 100 && progressBar.dataset.completed) {
                     progressBar.dataset.completed = "";
                }
            }

            // Check Module Unlocks sequentially
            let previousModuleComplete = true;

            document.querySelectorAll('.module-card').forEach(moduleCard => {
                const moduleHtmlId = moduleCard.getAttribute('data-module-html-id');
                const dropdownButton = moduleCard.querySelector('.dropdown-btn');
                const moduleContainer = document.getElementById(moduleHtmlId);

                if (!dropdownButton || !moduleContainer) return;

                const isCurrentModuleComplete = checkModuleCompletion(moduleContainer);

                if (dropdownButton.classList.contains('locked')) {
                    if (previousModuleComplete) {
                        console.log(`Unlocking module: ${moduleHtmlId}`);
                        dropdownButton.classList.remove("locked");
                        dropdownButton.classList.add("unlocked");
                        dropdownButton.removeAttribute("disabled");
                        checkAndEnableModuleItems(moduleHtmlId);
                    }
                } else {
                     checkAndEnableModuleItems(moduleHtmlId);
                }
                previousModuleComplete = isCurrentModuleComplete;
            });
        }


        // Helper: Check if all items within a specific module's container are complete (checked)
        function checkModuleCompletion(moduleContainerElement) {
            if (!moduleContainerElement) return false;
            const allModuleCheckboxes = moduleContainerElement.querySelectorAll('.item-checkbox');
            if (allModuleCheckboxes.length === 0) return true;
            return Array.from(allModuleCheckboxes).every(checkbox => checkbox.checked);
        }

        // Update the Pending Items lists in the sidebar
        function updatePendingItems() {
            if (!hasTraining) return; // Don't run if no training

            const pendingLecturesList = $('#pendingLessons');
            const pendingQuizzesList = $('#pendingQuizzes');
            if (!pendingLecturesList.length || !pendingQuizzesList.length) return;

            pendingLecturesList.empty();
            pendingQuizzesList.empty();

            let lectureItemsHtml = "";
            let quizItemsHtml = "";
            let hasPendingLectures = false;
            let hasPendingQuizzes = false;

            $('.module-card').each(function() {
                const moduleCard = $(this);
                const dropdownBtn = moduleCard.find('.dropdown-btn');

                if (dropdownBtn.hasClass('locked')) {
                    return;
                }

                const moduleContainer = moduleCard.find('.dropdown-container');
                const moduleTitle = dropdownBtn.find('span').text().trim();

                moduleContainer.find('.item-checkbox').each(function() {
                    const checkbox = $(this);
                    if (!checkbox.prop('checked')) {
                        const itemType = checkbox.data('item-type');
                        const itemTitle = checkbox.data('title') || 'Untitled Item';
                        const typeDisplay = itemType.charAt(0).toUpperCase() + itemType.slice(1);
                        const listItem = `<li class="list-group-item py-1 px-2 border-0"><small><span class="fw-bold">${moduleTitle}:</span> ${itemTitle} <span class="text-muted">(${typeDisplay})</span></small></li>`;

                        if (itemType === 'lecture') {
                            lectureItemsHtml += listItem;
                            hasPendingLectures = true;
                        } else {
                            quizItemsHtml += listItem;
                            hasPendingQuizzes = true;
                        }
                    }
                });
            });

            const noItemsLectures = "<li class='list-group-item py-1 px-2 border-0 text-success'><small><i class='fas fa-check-circle mr-2'></i>No Pending Lectures</small></li>";
            const noItemsQuizzes = "<li class='list-group-item py-1 px-2 border-0 text-success'><small><i class='fas fa-check-circle mr-2'></i>No Pending Activities/Assessments</small></li>";

            pendingLecturesList.html(hasPendingLectures ? lectureItemsHtml : noItemsLectures);
            pendingQuizzesList.html(hasPendingQuizzes ? quizItemsHtml : noItemsQuizzes);
        }


        // Function to save progress via AJAX (Returns a Promise)
        async function saveProgress(moduleId) {
             if (!hasTraining) return false; // Don't run if no training

            if (!moduleId || !participantId || !trainingId) {
                console.error("Missing data for saving progress:", { moduleId, participantId, trainingId });
                Swal.fire('Error', 'Could not save progress (missing data).', 'error');
                return false;
            }

            const dataToSend = {
                training_id: parseInt(trainingId),
                module_id: parseInt(moduleId)
            };

            console.log("Attempting to save progress for module ID:", moduleId);

            try {
                const response = await fetch(SAVE_PROGRESS_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(dataToSend)
                });

                const data = await response.json();
                console.log("Save progress response:", data);

                if (data.status === 'success') {
                    console.log("Progress saved successfully for module:", moduleId);
                    return true;
                } else {
                    Swal.fire('Save Failed', data.message || 'Could not save progress.', 'error');
                    return false;
                }
            } catch (error) {
                console.error('Error saving progress via fetch:', error);
                Swal.fire('Network Error', 'Could not connect to save progress.', 'error');
                return false;
            }
        }


        function startCountdownTimer() {
             if (!hasTraining) return; // Don't run if no training

            let remainingSeconds = <?= is_numeric($remaining_seconds) ? $remaining_seconds : 0 ?>;
            let courseNotStarted = <?= $courseNotStarted ? 'true' : 'false' ?>;
            let courseEnded = <?= $courseEnded ? 'true' : 'false' ?>;
            const countdownElement = document.getElementById("countdown");

            if (!countdownElement) return;

            if (courseNotStarted || courseEnded || remainingSeconds <= 0) {
                if (countdownElement.textContent === "Calculating...") {
                    if (courseEnded) countdownElement.textContent = "Ended";
                    else if (courseNotStarted) countdownElement.textContent = "Not Started";
                    else countdownElement.textContent = "Time is up!";
                    countdownElement.classList.remove('text-danger');
                    countdownElement.classList.add('text-muted');
                }
                return;
            }

            const timerInterval = setInterval(updateCountdown, 1000);

            function updateCountdown() {
                if (remainingSeconds <= 0) {
                    clearInterval(timerInterval);
                    countdownElement.textContent = "Time is up!";
                    countdownElement.classList.add('text-danger', 'fw-bold');
                    // Optional: Add overlay dynamically or reload page
                    return;
                }

                let days = Math.floor(remainingSeconds / 86400);
                let hours = Math.floor((remainingSeconds % 86400) / 3600);
                let minutes = Math.floor((remainingSeconds % 3600) / 60);
                let seconds = remainingSeconds % 60;

                let timeString = "";
                if (days > 0) timeString += `${days}d `;
                timeString += `${String(hours).padStart(2, '0')}h `;
                timeString += `${String(minutes).padStart(2, '0')}m `;
                timeString += `${String(seconds).padStart(2, '0')}s`;

                countdownElement.textContent = timeString;
                countdownElement.classList.add('text-danger', 'fw-bold');
                remainingSeconds--;
            }
            updateCountdown();
        }

    }); // End document ready
</script>
</body>
</html>
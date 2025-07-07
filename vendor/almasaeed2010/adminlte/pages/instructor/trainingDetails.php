<?php
// training_details.php
session_start();
if (!isset($_SESSION['instructor_id'])) {
    header("Location: instructorLogin.php");
    exit();
}

include '../includes/config.php';

// Check if trainingID is set in URL
if (!isset($_GET['trainingID']) || empty($_GET['trainingID'])) {
    echo "Invalid Training ID.";
    exit();
}

$trainingID = $_GET['trainingID'];

// Fetch training details from database
$sql = "SELECT training_title, `description`, `location`, `start_date`, end_date
        FROM training WHERE training_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $trainingID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "Training not found.";
    exit();
}

$training = $result->fetch_assoc();

// Fetch participants for this training
$participantsSql = "SELECT
                        up.participant_id,
                        up.first_name,
                        up.middle_name,
                        up.last_name,
                        up.email,
                        up.contact_number,
                        up.office,
                        up.position,
                        tp.enrollment_date,
                        tp.completion_status
                    FROM training_participants tp
                    JOIN user_participants up ON tp.participant_id = up.participant_id
                    WHERE tp.training_id = ?";
$participantsStmt = $conn->prepare($participantsSql);
$participantsStmt->bind_param("i", $trainingID);
$participantsStmt->execute();
$participantsResult = $participantsStmt->get_result();
$participants = $participantsResult->fetch_all(MYSQLI_ASSOC);
$participantCount = count($participants);

// Fetch modules for this training
$modulesSql = "SELECT module_id, module_name, module_description, module_type, file_path, link_url, updated_at FROM modules WHERE training_id = ?";
$modulesStmt = $conn->prepare($modulesSql);
$modulesStmt->bind_param("i", $trainingID);
$modulesStmt->execute();
$modulesResult = $modulesStmt->get_result();
$modules = $modulesResult->fetch_all(MYSQLI_ASSOC);

// Group modules by module_name
$groupedModules = [];
foreach ($modules as $module) {
    $moduleName = $module['module_name'];
    if (!isset($groupedModules[$moduleName])) {
        $groupedModules[$moduleName] = [];
    }
    $groupedModules[$moduleName][] = $module;
}

// Separate Examination and Remedial modules to display last
$lastModuleNames = ["Examination", "Remedial"];
$orderedModuleNames = [];

// First, add all module names that are NOT in $lastModuleNames
foreach (array_keys($groupedModules) as $moduleName) {
    if (!in_array($moduleName, $lastModuleNames)) {
        $orderedModuleNames[] = $moduleName;
    }
}
// Then, add the module names from $lastModuleNames, if they exist
foreach ($lastModuleNames as $moduleName) {
    if (isset($groupedModules[$moduleName])) {
        $orderedModuleNames[] = $moduleName;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Training Details</title>
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="adminlte/plugins/fontawesome-free/css/all.min.css">
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
        .callout-info a {
            text-decoration: none; /* Remove underline by default */
        }
        .callout-info a:hover {
            text-decoration: underline; /* Show underline on hover */
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
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0"><i class="fas fa-graduation-cap mr-1"></i> Training Details</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="instructordashboard.php"><i class="fas fa-home"></i> Home</a></li>
                            <li class="breadcrumb-item active">Training Details</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
            <div class="row">
    <div class="col-md-12">

        <!-- Participants List Card -->
        <div class="card card-info card-outline collapsed-card" id="participantsList">
            <div class="card-header">
                <h3 class="card-title font-weight-bold">
                    <i class="fas fa-users mr-1"></i> List of Participants
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($participants)): ?>
                    <div class="alert alert-secondary">
                        <h5><i class="icon fas fa-info"></i> No Participants</h5>
                        No participants enrolled in this training yet.
                    </div>
                <?php else: ?>
                    <table id="participantsTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Contact Number</th>
                                <th>Office</th>
                                <th>Position</th>
                                <th>Enrollment Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($participants as $participant): ?>
                                <tr>
                                    <td><?= htmlspecialchars($participant['first_name'] . ' ' . ($participant['middle_name'] ? $participant['middle_name'] . ' ' : '') . $participant['last_name']) ?></td>
                                    <td><?= htmlspecialchars($participant['email']) ?></td>
                                    <td><?= htmlspecialchars($participant['contact_number']) ?></td>
                                    <td><?= htmlspecialchars($participant['office']) ?></td>
                                    <td><?= htmlspecialchars($participant['position']) ?></td>
                                    <td><?= date("F j, Y", strtotime($participant['enrollment_date'])) ?></td>
                                    <td>
                                        <?php
                                            $statusClass = '';
                                            if ($participant['completion_status'] == 'Completed') {
                                                $statusClass = 'badge bg-success';
                                            } elseif ($participant['completion_status'] == 'Dropped') {
                                                $statusClass = 'badge bg-danger';
                                            } else {
                                                $statusClass = 'badge bg-info'; // For 'Enrolled' - using warning to distinguish from completed
                                            }
                                        ?>
                                        <span class="<?= $statusClass ?>"><?= htmlspecialchars($participant['completion_status']) ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="7">
                                    <p class="text-sm text-muted">
                                        <i class="fas fa-user-friends mr-1"></i> Total Participants: <?= $participantCount ?>
                                    </p>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- Training Details Card -->
        <div class="card card-info card-outline">
            <div class="card-header">
                <h3 class="card-title font-weight-bold"><i class="fas fa-book-open mr-1"></i> Training Information</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-2">Title</dt>
                    <dd class="col-sm-10"><?= htmlspecialchars($training['training_title']) ?></dd>
                    <dt class="col-sm-2">Description</dt>
                    <dd class="col-sm-10"><?= nl2br(htmlspecialchars($training['description'])) ?></dd>
                    <dt class="col-sm-2">Location</dt>
                    <dd class="col-sm-10"><?= htmlspecialchars($training['location']) ?></dd>
                    <dt class="col-sm-2">Duration</dt>
                    <dd class="col-sm-10">
                        <?= date("F j, Y", strtotime($training['start_date'])) . ' to ' . date("F j, Y", strtotime($training['end_date'])) ?>
                    </dd>
                </dl>
            </div>
        </div>


        <!-- Modules Card with Tabs -->
        <div class="card card-info card-outline">
            <div class="card-header">
                <h3 class="card-title font-weight-bold"><i class="fas fa-list-alt mr-1"></i> Training Modules</h3>
                <div class="card-tools">
                <button type="button" class="btn btn-outline-info rounded-0 btn-sm" data-toggle="modal" data-target="#addModuleModal">
                    <i class="fas fa-plus-circle"></i> Add Module
                </button>
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($groupedModules)): ?>
                    <div class="alert alert-secondary">
                        <h5><i class="icon fas fa-info"></i> No Modules</h5>
                        No modules added for this training yet.
                    </div>
                <?php else: ?>
                    <ul class="nav nav-tabs" id="moduleTabs" role="tablist">
                        <?php $i = 0; foreach ($orderedModuleNames as $moduleName): $i++; ?>
                            <li class="nav-item">
                                <a class="nav-link <?= ($i == 1) ? 'active' : '' ?>" id="module-<?= $i ?>-tab" data-toggle="tab" href="#module-<?= $i ?>" role="tab" aria-controls="module-<?= $i ?>" aria-selected="<?= ($i == 1) ? 'true' : 'false' ?>">
                                    <i class="fas fa-folder-open mr-1"></i> <?= htmlspecialchars($moduleName) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="tab-content mt-3" id="moduleTabsContent">
                        <?php $j = 0; foreach ($orderedModuleNames as $moduleName): $j++; ?>
                            <div class="tab-pane fade <?= ($j == 1) ? 'show active' : '' ?>" id="module-<?= $j ?>" role="tabpanel" aria-labelledby="module-<?= $j ?>-tab">
                                <div class="row"> <!-- Add row here -->
                                <?php $module_count = 0; foreach ($groupedModules[$moduleName] as $module): $module_count++; ?>
                                    <div class="col-md-4"> <!-- Adjust column width for 3 modules per row -->
                                        <div class="callout callout-info">
                                            <h5><?= nl2br(htmlspecialchars($module['module_description'])) ?>
                                                <?php
                                                    $badgeClass = 'badge-secondary'; // Default to secondary
                                                    switch ($module['module_type']) {
                                                        case 'Lecture':
                                                            $badgeClass = 'badge-primary'; // Blue
                                                            break;
                                                        case 'Activity':
                                                            $badgeClass = 'badge-success'; // Green
                                                            break;
                                                        case 'Quiz':
                                                            $badgeClass = 'badge-warning'; // Yellow
                                                            break;
                                                    }
                                                ?>
                                                <small>
                                                    <span class="badge <?= $badgeClass ?> ml-2" style="font-size: 0.75em;">
                                                        <i class="fas fa-tag mr-1"></i> <?= htmlspecialchars($module['module_type']) ?>
                                                    </span>
                                                </small>
                                            </h5>

                                            <ul class="list-unstyled">
                                                <!-- File Path -->
                                                <?php if (!empty($module['file_path'])): ?>
                                                    <li>
                                                        <i class="fas fa-file mr-1"></i>
                                                        <?php
                                                            $filePath = $module['file_path'];
                                                            $fileName = basename($filePath);
                                                        ?>
                                                        <a class="text-info" href="<?= htmlspecialchars($module['file_path']) ?>" target="_blank">
                                                            <?= htmlspecialchars($fileName) ?>
                                                        </a>
                                                    </li>
                                                <?php endif; ?>

                                                <!-- Link URL -->
                                                <?php if (!empty($module['link_url'])): ?>
                                                    <li>
                                                        <i class="fas fa-link mr-1"></i>
                                                        <a class="text-primary" href="<?= htmlspecialchars($module['link_url']) ?>" target="_blank">
                                                            <?= htmlspecialchars($module['link_url']) ?>
                                                        </a>
                                                    </li>
                                                <?php endif; ?>
                                            </ul>

                                            <p class="text-muted text-sm">
                                                <i class="far fa-clock mr-1"></i> Updated: <?= date("j F Y, g:i a", strtotime($module['updated_at'])) ?>
                                            </p>
                                            <div class="module-actions">
                                                <button type="button" class="btn btn-sm btn-info edit-module-btn rounded-0" data-toggle="modal" data-target="#editModuleModal"
                                                        data-module-id="<?= htmlspecialchars($module['module_id']) ?>"
                                                        data-module-name="<?= htmlspecialchars($module['module_name']) ?>"
                                                        data-module-description="<?= htmlspecialchars($module['module_description']) ?>"
                                                        data-module-type="<?= htmlspecialchars($module['module_type']) ?>"
                                                        data-link-url="<?= htmlspecialchars($module['link_url']) ?>"
                                                        data-file-path="<?= htmlspecialchars($module['file_path']) ?>" >
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                                <button type="button" class="btn btn-sm btn-danger delete-module-btn rounded-0 ml-1" data-module-id="<?= htmlspecialchars($module['module_id']) ?>">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <?php if ($module_count % 3 == 0): ?>
                                        </div><div class="row"> <!-- Close and start new row after every 3 modules -->
                                    <?php endif; ?>
                                <?php endforeach; ?>
                                </div> <!-- Close row -->
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>
            </div>
        </section>
        </div>

<!-- Add Module Modal -->
<div class="modal fade" id="addModuleModal" tabindex="-1" role="dialog" aria-labelledby="addModuleModalLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addModuleModalLabel"><i class="fas fa-plus-circle mr-1"></i> Add New Module</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <form id="addModuleForm" action="addModuleHandler.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="training_id" value="<?= $trainingID ?>">
                    <div class="form-group">
                        <label for="add_module_name">Module Name</label>
                        <select class="form-control" name="module_name" id="add_module_name" required>
                            <option value="">Select Module Name</option>
                            <optgroup label="Specific Modules">
                                <option value="Examination">Examination</option>
                                <option value="Remedial">Remedial</option>
                            </optgroup>
                            <optgroup label="General Modules">
                                <?php
                                    for ($i = 1; $i <= 30; $i++) {
                                        echo "<option value='Module $i'>Module $i</option>";
                                    }
                                ?>
                            </optgroup>    
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="add_module_description">Module Title/Description</label>
                        <textarea class="form-control" name="module_description" id="add_module_description" rows="3" placeholder="Enter module title or a brief description" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="add_module_type">Module Type</label>
                        <select class="form-control" name="module_type" id="add_module_type" required>
                        <option value="">Select Module Type</option>
                            <option value="Lecture">Lecture</option>
                            <option value="Activity">Activity</option>
                            <option value="Quiz">Quiz</option>
                            <option value="Examination">Examination</option>
                            <option value="Remedial">Remedial</option>
                        </select>
                    </div>
                    <div class="form-group" id="add_link_upload_group" style="display: none;">  <!-- Hidden initially -->
                        <label for="add_link_url">Link URL</label>
                        <input type="url" class="form-control" name="link_url" id="add_link_url" placeholder="Enter URL (Optional)">
                        <small class="form-text text-muted">For Activities, Quizzes, Examinations, Remedial.</small>
                    </div>
                    <div class="form-group" id="add_file_upload_group" style="display: none;"> <!-- Hidden initially -->
                        <label for="add_file_path">Upload File</label>
                        <div class="input-group">
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" name="file_path" id="add_file_path">
                                <label class="custom-file-label" for="add_file_path">Choose file (Lecture Material)</label>
                            </div>
                        </div>
                        <small class="form-text text-muted">For Lecture modules, upload lecture slides, documents, etc.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary rounded-0" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info rounded-0"></i>Add</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Module Modal -->
<div class="modal fade" id="editModuleModal" tabindex="-1" role="dialog" aria-labelledby="editModuleModalLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModuleModalLabel"><i class="fas fa-edit mr-1"></i> Edit Module</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"> <!-- Added close button -->
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <form id="editModuleForm" action="editModuleHandler.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="module_id" id="edit_module_id">
                    <div class="form-group">
                        <label for="edit_module_name">Module Name</label>
                        <select class="form-control" name="module_name" id="edit_module_name" disabled>
                            <option value="">Select Module Name</option>
                             <optgroup label="General Modules">
                                <?php
                                    for ($i = 1; $i <= 30; $i++) {
                                        echo "<option value='Module $i'>Module $i</option>";
                                    }
                                ?>
                            </optgroup>
                            <optgroup label="Specific Modules">
                                <option value="Examination">Examination</option>
                                <option value="Remedial">Remedial</option>
                            </optgroup>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_module_description">Module Title/Description</label>
                        <textarea class="form-control" name="module_description" id="edit_module_description" rows="3" placeholder="Enter module title or a brief description" required></textarea>
                    </div>

                    <!-- ** START: Added Module Type Dropdown ** -->
                    <div class="form-group">
                        <label for="edit_module_type">Module Type</label>
                        <select class="form-control" name="module_type" id="edit_module_type" disabled>
                            <option value="">Select Module Type</option>
                            <option value="Lecture">Lecture</option>
                            <option value="Activity">Activity</option>
                            <option value="Quiz">Quiz</option>
                            <option value="Examination">Examination</option>
                            <option value="Remedial">Remedial</option>
                        </select>
                    </div>
                    <!-- ** END: Added Module Type Dropdown ** -->

                    <!-- ** START: Modified Link Upload Group ** -->
                    <div class="form-group" id="edit_link_upload_group" style="display: none;"> <!-- Initially hidden -->
                        <label for="edit_link_url">Link URL (Optional)</label>
                        <input type="url" class="form-control" name="link_url" id="edit_link_url" placeholder="Enter URL if applicable">
                        <small class="form-text text-muted">For non-Lecture modules (Activity, Quiz, etc.).</small>
                    </div>
                    <!-- ** END: Modified Link Upload Group ** -->

                    <!-- ** START: Modified File Upload Group ** -->
                    <div class="form-group" id="edit_file_upload_group" style="display: none;"> <!-- Initially hidden -->
                        <label for="edit_file_path">Upload New File (Optional)</label>
                        <div class="input-group">
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" name="file_path" id="edit_file_path">
                                <label class="custom-file-label" for="edit_file_path">Choose new file (Optional)</label>
                            </div>
                        </div>
                        <small class="form-text text-muted">Leave blank to keep the current file. For Lecture modules.</small>
                    </div>
                    <!-- ** END: Modified File Upload Group ** -->

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary rounded-0" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info rounded-0"> Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
    <!-- /.content-wrapper -->
    <?php include '../footer.php'; ?>
</div>
<!-- ./wrapper -->

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
    // Initialize DataTables
    $("#participantsTable").DataTable({
      "responsive": true, "lengthChange": true, "autoWidth": false,
      "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
    }).buttons().container().appendTo('#participantsTable_wrapper .col-md-6:eq(0)');

    // Initialize bsCustomFileInput for nice file input display
    bsCustomFileInput.init();
  });

  $(document).ready(function () {

    // --- ADD MODULE MODAL LOGIC ---

    // Reset Add Module modal on show
    $('#addModuleModal').on('show.bs.modal', function () {
        $('#addModuleForm')[0].reset(); // Reset form elements

        // Reset module type dropdown
        var moduleTypeSelect = $('#add_module_type');
        moduleTypeSelect.empty();
        moduleTypeSelect.append($('<option>', { value: '', text: 'Select Module Type' }));
        moduleTypeSelect.val('');

        // Hide optional fields and remove required attribute
        $('#add_link_upload_group').hide();
        $('#add_file_upload_group').hide();
        $('#add_link_url').prop('required', false);
        $('#add_file_path').prop('required', false);

        // Reset module name (to ensure its change handler runs correctly if needed)
        $('#add_module_name').val('');

        // Reset file input label
        $('#add_file_path').next('.custom-file-label').html('Choose file (Lecture Material)');
    });

    // Add Module Name change handler (restricts module type options)
    $('#add_module_name').change(function() {
        var selectedModuleName = $(this).val();
        var moduleTypeSelect = $('#add_module_type');

        moduleTypeSelect.empty(); // Clear existing options

        if (selectedModuleName === 'Examination') {
            moduleTypeSelect.append($('<option>', { value: 'Examination', text: 'Examination', selected: true }));
        } else if (selectedModuleName === 'Remedial') {
            moduleTypeSelect.append($('<option>', { value: 'Remedial', text: 'Remedial', selected: true }));
        } else if (selectedModuleName !== '') {
            moduleTypeSelect.append($('<option>', { value: '', text: 'Select Module Type' }));
            moduleTypeSelect.append($('<option>', { value: 'Lecture', text: 'Lecture' }));
            moduleTypeSelect.append($('<option>', { value: 'Activity', text: 'Activity' }));
            moduleTypeSelect.append($('<option>', { value: 'Quiz', text: 'Quiz' }));
            moduleTypeSelect.val(''); // Reset selection to placeholder
        } else {
            moduleTypeSelect.append($('<option>', { value: '', text: 'Select Module Type' }));
            moduleTypeSelect.val('');
        }
        // Trigger change on module type to update link/file visibility
        moduleTypeSelect.trigger('change');
    });

    // Add Module Type change handler (shows/hides Link or File input)
    $('#add_module_type').change(function () {
        var moduleType = $(this).val();

        // Reset visibility and requirements first
        $('#add_link_upload_group').hide();
        $('#add_file_upload_group').hide();
        $('#add_link_url').prop('required', false);
        $('#add_file_path').prop('required', false);

        if (moduleType === 'Lecture') {
            $('#add_file_upload_group').show();
            $('#add_file_path').prop('required', true);
        } else if (moduleType === 'Activity' || moduleType === 'Quiz' || moduleType === 'Examination' || moduleType === 'Remedial') {
            $('#add_link_upload_group').show();
            $('#add_link_url').prop('required', true);
        }
        // Reset file input label if file field is hidden again
        if (moduleType !== 'Lecture') {
            $('#add_file_path').next('.custom-file-label').html('Choose file (Lecture Material)');
        }
    });

    // --- EDIT MODULE MODAL LOGIC ---

    // Populate Edit Module modal on show
    $('#editModuleModal').on('show.bs.modal', function (event) {
        const button = $(event.relatedTarget); // Button that triggered the modal
        // Extract info from data-* attributes
        const moduleId = button.data('module-id');
        const moduleName = button.data('module-name');
        const moduleDescription = button.data('module-description');
        const moduleType = button.data('module-type');
        const linkUrl = button.data('link-url');
        // Note: We don't need the current file path for display logic,
        // but you might need it if you want to display the current filename.

        // Populate the modal's fields
        $('#edit_module_id').val(moduleId);
        $('#edit_module_name').val(moduleName);
        $('#edit_module_description').val(moduleDescription);
        $('#edit_link_url').val(linkUrl);
        $('#edit_file_path').val(''); // Clear the file input value on modal open

        // Populate the module type dropdown and trigger its change event
        $('#edit_module_type').val(moduleType);
        $('#edit_module_type').trigger('change'); // This is crucial to show/hide the correct fields

        // Reset the file input label using bsCustomFileInput's method if needed
        // It's often better to re-initialize it to handle the placeholder correctly
        bsCustomFileInput.destroy(); // Remove existing instance attached to the input
        bsCustomFileInput.init();    // Re-initialize bsCustomFileInput on the specific input
        $('#edit_file_path').next('.custom-file-label').html('Choose new file (Optional)'); // Explicitly set label text
    });

    // Edit Module Type change handler (shows/hides Link or File input)
    $('#edit_module_type').change(function() {
        var selectedType = $(this).val();

        // Hide both optional fields initially
        $('#edit_link_upload_group').hide();
        $('#edit_file_upload_group').hide();

        // Show the relevant field based on the selected type
        if (selectedType === 'Lecture') {
            $('#edit_file_upload_group').show();
        } else if (selectedType !== '') { // Show link for any *other* selected type (Activity, Quiz, Exam, Remedial)
            $('#edit_link_upload_group').show();
        }
        // Note: We don't make fields required in edit mode as they are optional updates.

        // Reset file input label if file field is hidden again
        if (selectedType !== 'Lecture') {
            $('#edit_file_path').next('.custom-file-label').html('Choose new file (Optional)');
        }
    });


    // --- FORM SUBMISSION HANDLERS ---

    // Add Module Form Submission
    $("#addModuleForm").submit(function(event) {
        event.preventDefault(); // Prevent default form submission
        var formData = new FormData(this);

        // Validation: Check if Module Type is selected when required
        var moduleName = $('#add_module_name').val();
        var moduleType = $('#add_module_type').val();
        if (moduleName !== '' && moduleName !== 'Examination' && moduleName !== 'Remedial' && moduleType === '') {
             Swal.fire({
                    title: 'Missing Information',
                    text: 'Please select a Module Type.',
                    icon: 'warning',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'OK'
                });
            return; // Stop submission
        }

        $.ajax({
            url: "addModuleHandler.php",
            type: "POST",
            data: formData,
            processData: false, // Important for FormData
            contentType: false, // Important for FormData
            success: function(response) {
                // Trim whitespace from response
                response = response.trim();
                if (response.startsWith("Error:")) {
                    Swal.fire({
                        title: 'Error!',
                        text: response,
                        icon: 'error',
                        confirmButtonColor: '#d33',
                        confirmButtonText: 'OK'
                    });
                } else {
                    Swal.fire({
                        title: 'Success!',
                        text: response,
                        icon: 'success',
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $("#addModuleModal").modal("hide");
                            location.reload(); // Reload page to see changes
                        }
                    });
                }
            },
            error: function(xhr, status, error) {
                Swal.fire({
                    title: 'Error!',
                    text: 'An error occurred while adding the module: ' + error,
                    icon: 'error',
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'OK'
                });
            }
        });
    });

    // Edit Module Form Submission
    $("#editModuleForm").submit(function(event) {
        event.preventDefault(); // Prevent default form submission
        var formData = new FormData(this);

        // Basic validation: Check if module type is selected
        if ($('#edit_module_type').val() === '') {
             Swal.fire({
                    title: 'Missing Information',
                    text: 'Please select a Module Type.',
                    icon: 'warning',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'OK'
                });
            return; // Stop submission
        }

        $.ajax({
            url: "editModuleHandler.php",
            type: "POST",
            data: formData,
            processData: false, // Important for FormData
            contentType: false, // Important for FormData
            success: function(response) {
                // Trim whitespace from response
                response = response.trim();
                 if (response.startsWith("Error:")) {
                     Swal.fire({
                         title: 'Error!',
                         text: response,
                         icon: 'error',
                         confirmButtonColor: '#d33',
                         confirmButtonText: 'OK'
                     });
                 } else {
                    Swal.fire({
                        title: 'Success!',
                        text: response, // Should be success message from handler
                        icon: 'success',
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $("#editModuleModal").modal("hide");
                            location.reload(); // Reload page to see changes
                        }
                    });
                 }
            },
            error: function(xhr, status, error) {
                Swal.fire({
                    title: 'Error!',
                    text: 'An error occurred while updating the module: ' + error,
                    icon: 'error',
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'OK'
                });
            }
        });
    });

    // --- DELETE MODULE HANDLER ---
    $('.delete-module-btn').click(function() {
        var moduleId = $(this).data('module-id');

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
                // Send AJAX request to delete handler
                $.ajax({
                    // Use POST for delete operations for better practice, sending ID in data
                    url: "deleteModuleHandler.php",
                    type: "POST",
                    data: { module_id: moduleId }, // Send module_id as POST data
                    success: function(response) {
                         // Trim whitespace from response
                        response = response.trim();
                         if (response.startsWith("Error:")) {
                             Swal.fire({
                                 title: 'Error!',
                                 text: response,
                                 icon: 'error',
                                 confirmButtonColor: '#d33',
                                 confirmButtonText: 'OK'
                             });
                         } else {
                            Swal.fire({
                                title: 'Deleted!',
                                text: response, // Should be success message from handler
                                icon: 'success',
                                confirmButtonColor: '#3085d6',
                                confirmButtonText: 'OK'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    location.reload(); // Reload page to reflect deletion
                                }
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.fire({
                            title: 'Error!',
                            text: 'An error occurred while deleting the module: ' + error,
                            icon: 'error',
                            confirmButtonColor: '#d33',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            }
        });
    });

}); // End of $(document).ready
</script>

<!-- Don't forget to include bs-custom-file-input JS if you haven't already -->
<!-- <script src="../../plugins/bs-custom-file-input/bs-custom-file-input.min.js"></script> -->

<!-- bs-custom-file-input -->
<script src="../../plugins/bs-custom-file-input/bs-custom-file-input.min.js"></script>
</body>
</html>

<?php
session_start(); // Only once, at the very top
require '../includes/config.php'; // Database connection
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Free Trainings</title>
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
        .modal-xl {
            max-width: 95%;
        }
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
        <section class="content">
            <br>
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title my-2 float-left">
                        <i class="fas fa-book mr-2"></i> Free Trainings
                    </h3>
                    <div class="float-right">
                        <div class="btn-group" role="group" aria-label="training Actions">
                            <a href="createFreeTraining.php" class="btn btn-outline-warning rounded-0">
                                <i class="fas fa-plus"></i> Create New Free Training
                            </a>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                </div>

                <div class="card-body">


                    <table id="trainingsTable" class="table table-bordered table-striped">
                        <thead class="warning-gradient">
                            <tr>
                                <th>#</th>
                                <th>Training Title</th>
                                <th>Description</th>
                                <th>Training Link</th>
                                <th>Training Hrs</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Fetch training data from the database
                            $sql = "SELECT * FROM free_trainings";
                            $result = $conn->query($sql);

                            if ($result->num_rows > 0) {
                                $count = 1;
                                while ($row = $result->fetch_assoc()) {
                                    // Determine the correct image path
                                    $imagePath = '../uploads/' . htmlspecialchars($row["image_path"]);

                                    echo "<tr>";
                                    echo "<td>" . $count . "</td>";
                                    echo "<td>" . htmlspecialchars($row["training_title"]) . "</td>";
                                    echo "<td>" . htmlspecialchars($row["description"]) . "</td>";
                                    echo "<td><a href='" . htmlspecialchars($row["training_link"]) . "' target='_blank'>" . htmlspecialchars($row["training_link"]) . "</a></td>";
                                    echo "<td>" . htmlspecialchars($row["training_hrs"]) . "</td>";
                                    echo "<td>" . htmlspecialchars($row["start_date"]) . "</td>";
                                    echo "<td>" . htmlspecialchars($row["end_date"]) . "</td>";
                                    echo "<td>" . htmlspecialchars($row["created_at"]) . "</td>";
                                    echo "<td>
                                        <button class='btn btn-sm btn-warning edit-training-btn' title='Edit Training' data-toggle='modal' data-target='#editTrainingModal'
                                            data-id='" . $row["id"] . "'
                                            data-title='" . htmlspecialchars($row["training_title"]) . "'
                                            data-description='" . htmlspecialchars($row["description"]) . "'
                                            data-startdate='" . htmlspecialchars($row["start_date"]) . "'
                                            data-enddate='" . htmlspecialchars($row["end_date"]) . "'
                                            data-hrs='" . htmlspecialchars($row["training_hrs"]) . "'><i class='fas fa-edit'></i></button>
                                    </td>";
                                    echo "</tr>";
                                    $count++;
                                }
                            } else {
                                echo "<tr><td colspan='10'>No trainings found.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
        </section>
    </div>

    <?php include '../footer.php'; ?>
</div>

<!-- Edit Training Modal -->
<div class="modal fade" id="editTrainingModal" tabindex="-1" role="dialog" aria-labelledby="editTrainingModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editTrainingModalLabel">Edit Training</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="editTrainingForm">
                    <input type="hidden" id="editTrainingId" name="training_id">
                    <div class="form-group">
                        <label for="editTrainingTitle">Title</label>
                        <input type="text" class="form-control" id="editTrainingTitle" name="training_title">
                    </div>
                    <div class="form-group">
                        <label for="editTrainingDescription">Description</label>
                        <textarea class="form-control" id="editTrainingDescription" name="training_description" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="editTrainingStartDate">Start Date</label>
                        <input type="date" class="form-control" id="editTrainingStartDate" name="training_start_date">
                    </div>
                    <div class="form-group">
                        <label for="editTrainingEndDate">End Date</label>
                        <input type="date" class="form-control" id="editTrainingEndDate" name="training_end_date">
                    </div>
                    <div class="form-group">
                        <label for="editTrainingHrs">Training Hours</label>
                        <input type="number" class="form-control" id="editTrainingHrs" name="training_hrs">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-warning" id="saveTrainingChanges">Save changes</button>
            </div>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<link rel="stylesheet" href="../../plugins/icheck-bootstrap/icheck-bootstrap.min.css">

<script>
$(function () {
    $("#trainingsTable").DataTable({
        "responsive": true, "lengthChange": true, "autoWidth": true,
        "ordering": false, // Disable sorting
    })().container().appendTo('#trainingsTable_wrapper .col-md-6:eq(0)');

    $("#traineesTable").DataTable({
        "responsive": false, "lengthChange": true, "autoWidth": false,
        "columnDefs": [{
            "targets": [0],
            "orderable": false
        }]
    });
});

$(document).ready(function () {
    $('[data-toggle="tooltip"]').tooltip();

    // Handle View Participants & SMEs Button Click
    $(".view-details-btn").click(function() {
        var trainingId = $(this).data("id");

        // Clear previous data
        $("#participantsList").empty();
        $("#smesList").empty();

        // Show loading state
        $("#participantsList").html("<tr><td colspan='3' class='text-center'>Loading...</td></tr>");
        $("#smesList").html("<tr><td colspan='3' class='text-center'>Loading...</td></tr>");

        // Fetch data via AJAX
        $.ajax({
            type: "POST",
            url: "fetchTrainingDetails.php",
            data: { training_id: trainingId },
            dataType: "json",
            success: function(response) {
                // Populate Participants
                if (response.participants && response.participants.length > 0) {
                    $("#participantsList").empty();
                    $.each(response.participants, function(index, participant) {
                        $("#participantsList").append(
                            `<tr>
                                <td>${index + 1}</td>
                                <td>${participant.name}</td>
                                <td>${participant.email}</td>
                            </tr>`
                        );
                    });
                } else {
                    $("#participantsList").html("<tr><td colspan='3' class='text-center'>No participants assigned.</td></tr>");
                }

                // Populate SMEs
                if (response.smes && response.smes.length > 0) {
                    $("#smesList").empty();
                    $.each(response.smes, function(index, sme) {
                        $("#smesList").append(
                            `<tr>
                                <td>${index + 1}</td>
                                <td>${sme.name}</td>
                                <td>${sme.email}</td>
                            </tr>`
                        );
                    });
                } else {
                    $("#smesList").html("<tr><td colspan='3' class='text-center'>No SMEs assigned.</td></tr>");
                }
            },
            error: function() {
                Swal.fire("Error!", "Failed to fetch details.", "error");
                $("#participantsList").html("<tr><td colspan='3' class='text-center'>Error loading data.</td></tr>");
                $("#smesList").html("<tr><td colspan='3' class='text-center'>Error loading data.</td></tr>");
            }
        });
    });

    // Handle Edit Training Button Click
    $(".edit-training-btn").click(function() {
        var trainingId = $(this).data("id");
        var title = $(this).data("title");
        var description = $(this).data("description");
        var location = $(this).data("location");
        var startDate = $(this).data("startdate");
        var endDate = $(this).data("enddate");
        var trainingHrs = $(this).data("hrs");

        $("#editTrainingId").val(trainingId);
        $("#editTrainingTitle").val(title);
        $("#editTrainingDescription").val(description);
        $("#editTrainingLocation").val(location);
        $("#editTrainingStartDate").val(startDate);
        $("#editTrainingEndDate").val(endDate);
        $("#editTrainingHrs").val(trainingHrs);
    });

    // Handle Save Changes for Edit Training
    $("#saveTrainingChanges").click(function() {
        var formData = $("#editTrainingForm").serialize();

        $.ajax({
            type: "POST",
            url: "updateTraining.php",
            data: formData,
            success: function(response) {
                var result = JSON.parse(response);
                if (result.status == "success") {
                    Swal.fire("Success!", result.message, "success").then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire("Error!", result.message, "error");
                }
            }
        });
    });

    // Display SweetAlert2 messages
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
<?php
$conn->close();
?>
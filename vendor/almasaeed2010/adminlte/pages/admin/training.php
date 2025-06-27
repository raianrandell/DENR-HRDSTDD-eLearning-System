<?php
session_start(); // Only once, at the very top

// NO UNSETTING HERE!  We want to display the messages.
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- ... (your head section remains the same) ... -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trainings</title>
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
        /* Style to make the modal wider */
        .modal-xl {
            max-width: 95%; /* Adjust as needed */
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
                <h3 class="card-title my-2 float-left">  <!-- Added float-left here -->
                    <i class="fas fa-book mr-2"></i> Training Management
                </h3>

                <div class="float-right">  <!-- Added float-right here -->

                <div class="btn-group" role="group" aria-label="training Actions">
                <a href="participantsList.php" class="btn btn-outline-warning rounded-0 mr-3">
                    <i class="fas fa-users"></i> List of Participants
                </a>
                <a href="instructorsList.php" class="btn btn-outline-warning rounded-0 mr-3">
                    <i class="fas fa-chalkboard-teacher"></i> List of Subject Matter Expert
                </a>
                    <a href="createTraining.php" class="btn btn-outline-warning rounded-0">
                        <i class="fas fa-plus"></i> Create New Training
                    </a>
                </div>

</div>

                <div class="clearfix"></div> <!--Important: Add clearfix after floating elements -->
            </div>

                <div class="card-body">
                <?php
                    require '../includes/config.php'; // Database connection

                    $sql = "SELECT t.*, 
                            COUNT(DISTINCT tp.participant_id) AS participant_count, 
                            COUNT(DISTINCT ti.instructor_id) AS instructor_count
                            FROM training t
                            LEFT JOIN training_participants tp ON t.training_id = tp.training_id
                            LEFT JOIN training_instructors ti ON t.training_id = ti.training_id
                            GROUP BY t.training_id
                            ORDER BY t.created_at DESC";
                            $result = $conn->query($sql);    
                    ?>

<table id="trainingsTable" class="table table-bordered table-striped">
    <thead class="warning-gradient">
        <tr>
            <th>#</th>
            <th>Training Title</th>
            <th>Description</th>
            <th>Venue</th>
            <th>Duration</th> <!-- Merged Start and End Date -->
            <th>Training Hrs</th>
            <th>Total Number of Participants</th>
            <th>Total Number of Instructor</th>
            <th>Created At</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php
// Assuming $result is your database query result containing the 'training_hrs' column
// Make sure your SQL query SELECTs t.training_hrs

if ($result->num_rows > 0) {
    $i = 1;
    while ($row = $result->fetch_assoc()) {
        // Prepare data, especially handling potential null hours
        $training_hours_display = ($row['training_hrs'] !== null && $row['training_hrs'] !== '') ? htmlspecialchars($row['training_hrs']) : 'N/A';
        $training_hours_data = ($row['training_hrs'] !== null && $row['training_hrs'] !== '') ? htmlspecialchars($row['training_hrs']) : '';

        echo "<tr>
                <td>{$i}</td>
                <td>" . htmlspecialchars($row['training_title']) . "</td>
                <td>" . htmlspecialchars($row['description']) . "</td>
                <td>" . htmlspecialchars($row['location']) . "</td>
                <td>" . date("M d, Y", strtotime($row['start_date'])) . " - " . date("M d, Y", strtotime($row['end_date'])) . "</td>
                <td>" . $training_hours_display . "</td> <!-- Display Training Hours -->
                <td>{$row['participant_count']}</td> <!-- Display participant count -->
                <td>{$row['instructor_count']}</td>  <!-- Display instructor count -->
                <td>" . date("M d, Y h:i A", strtotime($row['created_at'])) . "</td> <!-- Improved date format -->
                <td>
                    <div class='btn-group'>
                        <button type='button' class='btn btn-outline-secondary btn-sm dropdown-toggle ml-2' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>
                            <i class='fas fa-cog'></i>
                        </button>
                        <div class='dropdown-menu dropdown-menu-right'>
                               <a class='dropdown-item edit-training-btn'
                                href='#'
                                data-toggle='modal'
                                data-target='#editTrainingModal'
                                data-id='{$row['training_id']}'
                                data-title='" . htmlspecialchars($row['training_title'], ENT_QUOTES) . "'
                                data-description='" . htmlspecialchars($row['description'], ENT_QUOTES) . "'
                                data-location='" . htmlspecialchars($row['location'], ENT_QUOTES) . "'
                                data-startdate='{$row['start_date']}'
                                data-enddate='{$row['end_date']}'
                                data-hrs='" . $training_hours_data . "'> <!-- ADDED data-hrs attribute -->
                                <i class='fas fa-edit mr-2'></i>Edit
                                </a>

                                <a class='dropdown-item assign-participants-btn' href='assignParticipants.php?training_id=" . $row['training_id'] . "'>
                                    <i class='fas fa-user-plus mr-2'></i>Assign Participants
                                </a>
                                <a class='dropdown-item assign-instructors-btn' href='assignInstructors.php?training_id=" . $row['training_id'] . "'>
                                    <i class='fas fa-chalkboard-teacher mr-2'></i>Assign Subject Matter Expert
                                </a>
                                 <a class='dropdown-item manage-modules-btn' href='manage_modules.php?training_id=" .$row['training_id'] . "'>
                                    <i class='fas fa-folder-open mr-2'></i>Manage Modules
                                 </a>
                            </div>
                    </div>
                </td>
            </tr>";
        $i++;
    }
} else {
    // Adjust colspan if you added a new column
    echo "<tr><td colspan='10' class='text-center'>No trainings available.</td></tr>"; // Increased colspan to 10
}
?>
</tbody>

</table>

</div>
 <div class="modal fade" id="editTrainingModal" tabindex="-1" aria-labelledby="editTrainingModalLabel" aria-hidden="true" data-backdrop="static">
     <div class="modal-dialog modal-lg">
         <div class="modal-content">
             <div class="modal-header">
                 <h5 class="modal-title" id="editTrainingModalLabel">Edit Training</h5>
             </div>
             <div class="modal-body">
                 <form id="editTrainingForm">
                     <input type="hidden" id="editTrainingId" name="training_id">
                     
                     <div class="form-group">
                         <label for="editTrainingTitle">Training Title</label>
                         <input type="text" class="form-control" id="editTrainingTitle" name="training_title" readonly>
                     </div>

                     <div class="form-group">
                         <label for="editTrainingDescription">Description</label>
                         <textarea class="form-control" id="editTrainingDescription" name="description" required></textarea>
                     </div>

                     <div class="form-group">
                         <label for="editTrainingLocation">Venue</label>
                         <input type="text" class="form-control" id="editTrainingLocation" name="location" required>
                     </div>

                     <div class="form-group">
                         <label for="editTrainingStartDate">Start Date</label>
                         <input type="date" class="form-control" id="editTrainingStartDate" name="start_date" required>
                     </div>

                     <div class="form-group">
                         <label for="editTrainingEndDate">End Date</label>
                         <input type="date" class="form-control" id="editTrainingEndDate" name="end_date" required>
                     </div>

                     <div class="form-group">
                         <label>Training Hours</label>
                         <input type="number" class="form-control" id="editTrainingHrs" name="training_hrs" min="1" placeholder="e.g., 8, 16, 24">
                         <!-- 'min="1"' prevents zero or negative hours -->
                     </div>
                 </form>
             </div>
             <div class="modal-footer">
                 <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                 <button type="submit" class="btn btn-primary" id="saveTrainingChanges">Save changes</button>
             </div>
         </div>
     </div>
 </div>

            </div>
        </section>
    </div>

    <?php include '../footer.php'; ?>
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
            $("#trainingsTable").DataTable({
                "responsive": true, "lengthChange": true, "autoWidth": true
            }).buttons().container().appendTo('#trainingsTable_wrapper .col-md-6:eq(0)');

            $("#traineesTable").DataTable({
                "responsive": false, "lengthChange": true, "autoWidth": false,
                "columnDefs": [ {
                    "targets": [0], //index of column starting from 0
                    "orderable": false, //set not orderable
                }]
            });
        });
        // SweetAlert for Edit Button
      document.querySelectorAll(".edit-user-btn").forEach(button => {
          button.addEventListener("click", function() {
              Swal.fire({
                  title: "Edit Function",
                  text: "Edit functionality is under development.",
                  icon: "info",
                  confirmButtonText: "OK"
              });
          });
      });

        $(document).ready(function () {
            $('[data-toggle="tooltip"]').tooltip();

            // Handle the click event for "Assign Trainee" button
            $('.assign-trainee-btn').click(function() {
                var trainingId = $(this).data('training-id');
                $('#trainingIdDisplay').text(trainingId); // Display the training ID in the modal

                // You would typically load the trainee data here using AJAX
                // and populate the modal with the list of trainees and checkboxes.
                // For demonstration, I'm just opening the modal.
            });

            // Handle "Select All" checkbox
            $('#select-all').click(function(event) {
                if(this.checked) {
                    // Iterate each checkbox
                    $(':checkbox', '#traineesTable').each(function() {
                        this.checked = true;
                    });
                } else {
                    $(':checkbox', '#traineesTable').each(function() {
                        this.checked = false;
                    });
                }
            });

            // Handle Save Changes button click (example)
            $('#saveTraineeAssignments').click(function() {
                var selectedTrainees = [];
                $('input[name="trainee[]"]:checked').each(function() {
                    selectedTrainees.push($(this).val());
                });

                console.log("Selected Trainees:", selectedTrainees);
                // Here you would typically send the selectedTrainees array and the trainingId
                // to your server using AJAX to save the assignments.

                $('#assignTraineeModal').modal('hide'); // Close the modal after saving.
            });

            document.addEventListener("DOMContentLoaded", function() {
        // Alert for Edit button
        document.querySelectorAll(".actions-btn").forEach(button => {
            button.addEventListener("click", function() {
                alert("This button function is not available.");
            });
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

          // SweetAlert for Disable User Confirmation
  document.addEventListener("DOMContentLoaded", function() {
      document.querySelectorAll(".disable-user-btn").forEach(button => {
          button.addEventListener("click", function() {
              Swal.fire({
                title: "Disabled User",
                  text: "Edit functionality is under development.",
                  icon: "info",
                  confirmButtonText: "OK"
              });
          });
      });

      // SweetAlert for Edit Button
      document.querySelectorAll(".edit-user-btn").forEach(button => {
          button.addEventListener("click", function() {
              Swal.fire({
                  title: "Edit Function",
                  text: "Edit functionality is under development.",
                  icon: "info",
                  confirmButtonText: "OK"
              });
          });
      });
  });

// Inside the $(document).ready function in training.php
$(".edit-training-btn").click(function() {
    var trainingId = $(this).data("id");
    var title = $(this).data("title");
    var description = $(this).data("description");
    var location = $(this).data("location");
    var startDate = $(this).data("startdate");
    var endDate = $(this).data("enddate");
    var trainingHrs = $(this).data("hrs"); // Get the hours

    $("#editTrainingId").val(trainingId);
    $("#editTrainingTitle").val(title);
    $("#editTrainingDescription").val(description);
    $("#editTrainingLocation").val(location);
    $("#editTrainingStartDate").val(startDate);
    $("#editTrainingEndDate").val(endDate);
    $("#editTrainingHrs").val(trainingHrs); // Set the hours input
});

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


    </script>
</body>
</html>



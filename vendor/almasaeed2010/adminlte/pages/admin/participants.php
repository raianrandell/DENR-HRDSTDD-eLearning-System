<?php
session_start();
if (isset($_SESSION['success'])) {
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Success!',
                text: '" . $_SESSION['success'] . "',
                icon: 'success',
                confirmButtonText: 'OK'
            });
        });
    </script>";
    unset($_SESSION['success']); // Clear session message after displaying it
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Participants List</title>
    <link rel="stylesheet" href="adminlte/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="adminlte/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.1/dist/css/adminlte.min.css">
    
    <!-- DataTables -->
    <link rel="stylesheet" href="../../plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="../../plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="../../plugins/datatables-buttons/css/buttons.bootstrap4.min.css">

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <style>
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
        <!-- Content Header (Page header) -->
       <br>

        <section class="content">
            <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title"><i class="fas fa-users mr-2"></i>User Participants</h3>
                <a href="createParticipants.php" class="btn btn-outline-warning ml-auto rounded-0" title="Add a new trainee">
                    <i class="fas fa-user-plus"></i>&nbsp;Create New Participant    
                </a>
            </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <div class="mb-3 d-flex align-items-center">
                        <label for="trainingFilter" class="mr-2">Filter by In Training:</label>
                        <select id="trainingFilter" class="form-control w-25 mr-2">
                            <option value="">All</option>
                            <option value="Yes">Yes</option>
                            <option value="No">No</option>
                        </select>
                        <button id="resetFilter" class="btn btn-outline-secondary">Reset Filter</button>
                    </div>
                        <table id="traineesTable" class="table table-bordered table-striped">
                            <thead class="warning-gradient">
                                <tr>
                                    <th>#</th>
                                    <th>Full Name</th>
                                    <th>Email</th>
                                    <th>Office</th>
                                    <th>Position/Designation</th>
                                    <th>Salary Grade</th>
                                    <th>In Training</th>
                                    <th>Status</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                require '../includes/config.php'; // Database connection

                                // Fetch trainees from the database
                                $sql = "SELECT *FROM user_participants ORDER BY last_name ASC;";
                                $result = $conn->query($sql);
                                $count = 1;

                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        $photoPath = !empty($row['photo']) ? "../uploads/" . $row['photo'] : "uploads/default.jpg"; // Default image if no photo
                                        ?>
                                        <tr>
                                            <td><?php echo $count++; ?></td>
                                            <!-- <td>
                                                <img src="<?php echo $photoPath; ?>" alt="Trainee Photo" width="50" height="50" class="rounded-circle">
                                            </td> -->
                                            <td>
                                                <?php echo '<strong>' . $row['last_name'] . '</strong>, ' . $row['first_name'] . ' ' . substr($row['middle_name'], 0, 1); ?>
                                            </td>
                                            <td><?php echo $row['email']; ?></td>
                                            <td><?php echo $row['office']; ?></td>
                                            <td><?php echo $row['position']; ?></td>
                                            <td><?php echo $row['salary_grade']; ?></td>
                                            <td><?php echo ($row['in_training'] == 1) ? "Yes" : "No"; ?></td>
                                            <td>
                                                <span class="badge badge-success"><?php echo $row['status']; ?></span>
                                            </td>

                                            <td><?php echo date('m-d-Y h:i:s', strtotime($row['created_at'])); ?></td>
                                            <td>
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle ml-2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fas fa-cog"></i>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-right">
                                                    <a class="dropdown-item view-details-btn" href="#" 
                                                        data-id="<?php echo $row['participant_id']; ?>" 
                                                        data-photo="<?php echo $photoPath; ?>"
                                                        data-name="<?php echo $row['last_name'] . ', ' . $row['first_name'] . ' ' . substr($row['middle_name'], 0, 1); ?>"
                                                        data-email="<?php echo $row['email']; ?>"
                                                        data-contact="<?php echo $row['contact_number']; ?>"
                                                        data-office="<?php echo $row['office']; ?>"
                                                        data-position="<?php echo $row['position']; ?>"
                                                        data-gender="<?php echo $row['gender']; ?>"
                                                        data-age="<?php echo $row['age']; ?>"
                                                        data-salary_grade="<?php echo $row['salary_grade']; ?>"
                                                        data-training="<?php echo ($row['in_training'] == 1) ? 'Yes' : 'No'; ?>">
                                                        <i class="fas fa-eye mr-2"></i>View More Details
                                                    </a>

                                                        <a class="dropdown-item edit-user-btn" href="#">
                                                            <i class="fas fa-edit mr-2"></i>Edit
                                                        </a>
                                                        <a class="dropdown-item disable-user-btn" href="#">
                                                            <i class="fas fa-user-slash mr-2"></i>Disable
                                                        </a>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                } else {
                                    echo "<tr><td colspan='16' class='text-center'>No trainees found.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- View More Details Modal -->
<div class="modal fade" id="viewDetailsModal" tabindex="-1" role="dialog" aria-labelledby="viewDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewDetailsModalLabel">Trainee Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4 text-center">
                        <img id="participantPhoto" src="" alt="Participant Photo" class="img-fluid rounded-circle" width="150">
                    </div>
                    <div class="col-md-8">
                        <p><strong>Full Name:</strong> <span id="participantFullName"></span></p>
                        <p><strong>Email:</strong> <span id="participantEmail"></span></p>
                        <p><strong>Contact Number:</strong> <span id="participantContact"></span></p>
                        <p><strong>Office:</strong> <span id="participantOffice"></span></p>
                        <p><strong>Position:</strong> <span id="participantPosition"></span></p>
                        <p><strong>Gender:</strong> <span id="participantGender"></span></p>
                        <p><strong>Age:</strong> <span id="participantAge"></span></p>
                        <p><strong>Salary Grade:</strong> <span id="participantSalaryGrade"></span></p>
                        <p><strong>In Training:</strong> <span id="participantTraining"></span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


    <?php include '../footer.php'; ?>
</div>

<!-- Scripts -->
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function () {
    var table = $("#traineesTable").DataTable({
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
        "columnDefs": [
            { "targets": [1, 5, 6, 7], "className": "text-nowrap" } // These targets are likely correct for styling
        ]
    });

    // Custom filter for In Training column
    $("#trainingFilter").on("change", function () {
        var selectedValue = $(this).val();
        // --- FIX: Change column index from 5 to 6 ---
        table.column(6).search(selectedValue).draw(); // Column 6 (In Training)
    });

    // Reset filter
    $("#resetFilter").on("click", function () {
        $("#trainingFilter").val(""); // Reset dropdown
        // --- FIX: Change column index from 5 to 6 ---
        table.column(6).search("").draw(); // Reset search for Column 6
    });

    // Use event delegation to handle dynamically loaded rows for modal
    $("#traineesTable tbody").on("click", ".view-details-btn", function () {
         // ... (your existing modal logic here) ...
         var photo = $(this).data("photo");
         var name = $(this).data("name");
         var email = $(this).data("email");
         var contact = $(this).data("contact");
         var office = $(this).data("office");
         var position = $(this).data("position");
         var gender = $(this).data("gender");
         var age = $(this).data("age");
         var salaryGrade = $(this).data("salary_grade");
         var training = $(this).data("training");

         $("#participantPhoto").attr("src", photo);
         $("#participantFullName").text(name);
         $("#participantEmail").text(email);
         $("#participantContact").text(contact);
         $("#participantOffice").text(office);
         $("#participantPosition").text(position);
         $("#participantGender").text(gender);
         $("#participantAge").text(age);
         $("#participantSalaryGrade").text(salaryGrade);
         $("#participantTraining").text(training);

         $("#viewDetailsModal").modal("show");
     });


    // SweetAlert for Disable User Confirmation
    // Make sure these handlers are within the same $(document).ready block
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
</script>


</body>
</html>

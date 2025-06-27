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
    <title>Subject Matter Expert</title>
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
        
        <section class="content">
            <br>
            <div class="card">
                <div class="card-header d-flex align-items-center">
                <h3 class="card-title">
                    <i class="fas fa-chalkboard-teacher mr-2"></i> User Subject Matter Expert
                </h3>
                    <a href="createInstructor.php" class="btn btn-outline-warning rounded-0 ml-auto">
                        <i class="fas fa-chalkboard-teacher"></i> Create New Subject Matter Expert
                    </a>
                </div>

                    <div class="card-body">
                    <table id="instructor" class="table table-bordered table-striped">
                    <thead class="warning-gradient">
                        <tr>
                            <th>#</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Contact Number</th>
                            <th>Office</th>
                            <th>Position</th>
                            <th>Region</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
    <?php
    require '../includes/config.php'; // Ensure DB connection is included

    $sql = "SELECT *
            FROM user_instructors 
            ORDER BY last_name ASC";
    $result = $conn->query($sql);
    $count = 1;

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $full_name = '<strong>' . $row['last_name'] . '</strong>, ' . $row['first_name'] . ' ' . substr($row['middle_name'], 0, 1);
            ?>
            <tr>
                <td><?php echo $count++; ?></td>
                <td><?php echo $full_name; ?></td>
                <td><?php echo $row['email']; ?></td>
                <td><?php echo $row['contact_number']; ?></td>
                <td><?php echo $row['office']; ?></td>
                <td><?php echo $row['position']; ?></td>
                <td><?php echo $row['region']; ?></td>
                <td>
                    <span class="badge badge-success"><?php echo $row['status']; ?></span>
                </td>
                <td><?php echo date('m-d-Y h:i:s a', strtotime($row['created_at'])); ?></td>
                <td>
                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle ml-2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-cog"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item edit-user-btn" href="#">
                                <i class="fas fa-edit mr-2"></i>Edit
                            </a>
                            <a class="dropdown-item disable-user-btn" href="#">
                                <i class="fas fa-user-slash mr-2"></i>Disable
                            </a>
                        </div>
                    </div>

                    </div>
                </td>
            </tr>
            <?php
        }
    } else {
        echo "<tr><td colspan='10' class='text-center'>No instructors found.</td></tr>";
    }
    $conn->close();
    ?>
</tbody>

                </table>

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

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
  $(function () {
    $("#instructor").DataTable({
      "responsive": true, "lengthChange": true, "autoWidth": true
    }).buttons().container().appendTo('#instructor_wrapper .col-md-6:eq(0)');
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
  
</script>
</body>
</html>


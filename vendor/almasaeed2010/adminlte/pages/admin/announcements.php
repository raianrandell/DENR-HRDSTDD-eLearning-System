<?php
session_start();
require '../includes/config.php';

$message = "";
$message_type = "";

// Handle Delete
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $sql = "SELECT image_path FROM announcements WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $image_path = $row['image_path'];
    $stmt->close();

    // Delete image file
    if (file_exists($image_path) && !empty($image_path)) { // Ensure image_path is not empty
        unlink($image_path);
    }

    $sql = "DELETE FROM announcements WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $delete_id);
    if ($stmt->execute()) {
        $message = "Announcement deleted successfully.";
        $message_type = "success";
    } else {
        $message = "Database error on delete: " . $stmt->error;
        $message_type = "error";
    }
    $stmt->close();
}



// Handle Edit - Fetch data for pre-filling the form
$edit_id = isset($_GET['edit_id']) ? $_GET['edit_id'] : null;
$edit_title = "";
$edit_content = "";
$edit_author = "";
$edit_image_path = "";

if ($edit_id) {
    $sql = "SELECT * FROM announcements WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $edit_title = $row['title'];
        $edit_content = $row['content'];
        $edit_author = $row['author'];
        $edit_image_path = $row['image_path'];
    }
    $stmt->close();
}



if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['update_announcement'])) {
    $update_id = $_POST['update_id'];
    $title = $_POST["title"];
    $content = $_POST["content"];
    $author = $_POST["author"];


    $target_dir = "../uploads/";
    $uploadOk = 1;

    // If a new image is uploaded
    if ($_FILES["image"]["name"]) {
        $target_file = $target_dir . basename($_FILES["image"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Validate image
        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if ($check === false) {
            $message = "File is not an image.";
            $message_type = "error";
            $uploadOk = 0;
        }

        if ($_FILES["image"]["size"] > 5000000) {
            $message = "File is too large (max 5MB).";
            $message_type = "error";
            $uploadOk = 0;
        }

        if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
            $message = "Only JPG, JPEG, PNG & GIF files allowed.";
            $message_type = "error";
            $uploadOk = 0;
        }

        // If image validation passes, upload the new image
        if ($uploadOk) {
            $new_file_name = uniqid() . "." . $imageFileType;
            $target_file = $target_dir . $new_file_name;

            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                // Get the old image path to delete it
                $sql = "SELECT image_path FROM announcements WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $update_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                $old_image_path = $row['image_path'];
                $stmt->close();

                // Update announcement with the new image
                $sql = "UPDATE announcements SET title = ?, content = ?, image_path = ?, author = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                if ($stmt) {
                    $stmt->bind_param("ssssi", $title, $content, $target_file, $author, $update_id);
                    if ($stmt->execute()) {
                        // Delete the old image
                        if (file_exists($old_image_path) && !empty($old_image_path)) { // Check if old image path exists and is not empty before unlinking
                            unlink($old_image_path);
                        }
                        $message = "Announcement updated successfully.";
                        $message_type = "success";
                    } else {
                        $message = "Database error on update: " . $stmt->error;
                        $message_type = "error";
                    }
                    $stmt->close();
                } else {
                    $message = "Statement preparation failed.";
                    $message_type = "error";
                }
            } else {
                $message = "Failed to upload image.";
                $message_type = "error";
            }
        }
    } else {
        // If no new image is uploaded, update the announcement without changing the image
        $sql = "UPDATE announcements SET title = ?, content = ?, author = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("sssi", $title, $content, $author, $update_id); // Removed image_path from bind_param
            if ($stmt->execute()) {
                $message = "Announcement updated successfully.";
                $message_type = "success";
            } else {
                $message = "Database error on update: " . $stmt->error;
                $message_type = "error";
            }
            $stmt->close();
        } else {
            $message = "Statement preparation failed.";
            $message_type = "error";
        }
    }

}



if ($_SERVER["REQUEST_METHOD"] == "POST" && empty($_POST['update_announcement'])) {
    $title = $_POST["title"];
    $content = $_POST["content"];
    $author = $_POST["author"];

    $target_dir = "../uploads/";
    $uploadOk = 1;

    // If an image is uploaded
    if ($_FILES["image"]["name"]) {
        $target_file = $target_dir . basename($_FILES["image"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if ($check === false) {
            $message = "File is not an image.";
            $message_type = "error";
            $uploadOk = 0;
        }

        if ($_FILES["image"]["size"] > 5000000) {
            $message = "File is too large (max 5MB).";
            $message_type = "error";
            $uploadOk = 0;
        }

        if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
            $message = "Only JPG, JPEG, PNG & GIF files allowed.";
            $message_type = "error";
            $uploadOk = 0;
        }

        if ($uploadOk) {
            $new_file_name = uniqid() . "." . $imageFileType;
            $target_file = $target_dir . $new_file_name;

            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $sql = "INSERT INTO announcements (title, content, image_path, author) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);

                if ($stmt) {
                    $stmt->bind_param("ssss", $title, $content, $target_file, $author);
                    if ($stmt->execute()) {
                        $message = "Announcement created successfully.";
                        $message_type = "success";
                    } else {
                        $message = "Database error: " . $stmt->error;
                        $message_type = "error";
                    }
                    $stmt->close();
                } else {
                    $message = "Statement preparation failed.";
                    $message_type = "error";
                }
            } else {
                $message = "Failed to upload image.";
                $message_type = "error";
            }
        }
    } else {
        // If no image is uploaded, insert without image_path
        $sql = "INSERT INTO announcements (title, content, author) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("sss", $title, $content, $author);
            if ($stmt->execute()) {
                $message = "Announcement created successfully.";
                $message_type = "success";
            } else {
                $message = "Database error: " . $stmt->error;
                $message_type = "error";
            }
            $stmt->close();
        } else {
            $message = "Statement preparation failed.";
            $message_type = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Announcement</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
        .modal-image {
            max-width: 100%;
            height: auto;
            border-radius: 0.5rem;
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
        <div class="content-header">
            <div class="container-fluid">
                <h1 class="m-0 text-dark">Create Announcement</h1>
            </div>
        </div>

        <div class="content">
            <div class="container-fluid">
                <div class="row">

                    <!-- Form Card -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header warning-gradient">
                                <h3 class="card-title"><i class="fas fa-bullhorn"></i> New Announcement</h3>
                            </div>
                            <div class="card-body">
                                <form method="post" enctype="multipart/form-data" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                                    <?php if ($edit_id): ?>
                                        <input type="hidden" name="update_id" value="<?php echo htmlspecialchars($edit_id); ?>">
                                        <input type="hidden" name="update_announcement" value="true">
                                    <?php endif; ?>
                                    <div class="form-group">
                                        <label for="title">Title</label>
                                        <input type="text" name="title" id="title" class="form-control" value="<?php echo htmlspecialchars($edit_title); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="content">Content</label>
                                        <textarea name="content" id="content" class="form-control" rows="4" required><?php echo htmlspecialchars($edit_content); ?></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label for="image">Image</label>
                                        <input type="file" name="image" id="image" class="form-control-file">
                                        <?php if ($edit_image_path): ?>
                                            <p>Current Image: <img src="<?php echo htmlspecialchars($edit_image_path); ?>" style="max-width: 100px; max-height: 100px;"></p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="form-group">
                                        <label for="author">Author</label>
                                        <input type="text" name="author" id="author" class="form-control" value="<?php echo htmlspecialchars($edit_author); ?>" required>
                                    </div>
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-<?php echo $edit_id ? 'pen' : 'plus-circle'; ?>"></i> <?php echo $edit_id ? 'Update' : 'Create'; ?> Announcement
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Announcements -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header warning-gradient">
                                <h3 class="card-title"><i class="fas fa-clock"></i> Recent Announcements</h3>
                            </div>
                            <div class="card-body table-responsive">
                                <table id="example1" class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>Title</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    $sql = "SELECT * FROM announcements ORDER BY created_at DESC LIMIT 5";
                                    $result = $conn->query($sql);

                                    if ($result->num_rows > 0) {
                                        while($row = $result->fetch_assoc()) {
                                            echo "<tr>";
                                            echo "<td>" . htmlspecialchars($row["title"]) . "</td>";
                                            echo "<td>
                                                    <button class='btn btn-info btn-sm' data-toggle='modal' data-target='#announcementModal" . $row["id"] . "'><i class='fas fa-eye'></i></button>
                                                    <button class='btn btn-primary btn-sm edit-announcement' data-id='" . $row["id"] . "'><i class='fas fa-edit'></i></button>
                                                    <button class='btn btn-danger btn-sm delete-announcement' data-id='" . $row["id"] . "'><i class='fas fa-trash-alt'></i></button>
                                                  </td>";
                                            echo "</tr>";

                                            // Modal
                                            echo "<div class='modal fade' id='announcementModal" . $row["id"] . "' tabindex='-1'>";
                                            echo "  <div class='modal-dialog modal-lg'>";
                                            echo "    <div class='modal-content'>";
                                            echo "      <div class='modal-header warning-gradient'>";
                                            echo "        <h5 class='modal-title'>View Announcement</h5>";
                                            echo "        <button type='button' class='close' data-dismiss='modal'><span>×</span></button>";
                                            echo "      </div>";
                                            echo "      <div class='modal-body'>";
                                            echo "        <div class='row'>";
                                            echo "          <div class='col-md-12'>"; // Use col-md-12 for a single column layout
                                            echo "            <p class='font-weight-bold'>" . nl2br(htmlspecialchars($row["title"])) . "</p>";
                                            echo "            <p>" . nl2br(htmlspecialchars($row["content"])) . "</p>";
                                            if (!empty($row["image_path"])) {
                                                echo "            <img src='" . htmlspecialchars($row["image_path"]) . "' class='modal-image mb-3' alt='Announcement Image'>";
                                            }
                                            echo "            <p><strong>Posted By:</strong> " . htmlspecialchars($row["author"]) . "</p>";
                                            echo "            <p><strong></strong> " . date("F j, Y \a\\t g:i a", strtotime($row["created_at"])) . "</p>";
                                            echo "          </div>";
                                            echo "        </div>";
                                            echo "      </div>";
                                            echo "      <div class='modal-footer'>";
                                            echo "        <button type='button' class='btn btn-secondary' data-dismiss='modal'>Close</button>";
                                            echo "      </div>";
                                            echo "    </div>";
                                            echo "  </div>";
                                            echo "</div>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='2'>No announcements found.</td></tr>";
                                    }
                                    ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div> <!-- /.row -->
            </div> <!-- /.container-fluid -->
        </div> <!-- /.content -->
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editAnnouncementModal" tabindex="-1" role="dialog" aria-labelledby="editAnnouncementModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editAnnouncementModalLabel">Edit Announcement</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="editAnnouncementForm" method="post" enctype="multipart/form-data" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <input type="hidden" name="update_id" id="edit_update_id">
                        <input type="hidden" name="update_announcement" value="true">
                        <div class="form-group">
                            <label for="edit_title">Title</label>
                            <input type="text" class="form-control" id="edit_title" name="title" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_content">Content</label>
                            <textarea class="form-control" id="edit_content" name="content" rows="4" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="edit_image">Image</label>
                            <input type="file" class="form-control-file" id="edit_image" name="image">
                            <img id="edit_current_image" src="" style="max-width: 100px; max-height: 100px;">
                        </div>
                        <div class="form-group">
                            <label for="edit_author">Author</label>
                            <input type="text" class="form-control" id="edit_author" name="author" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Update Announcement</button>
                    </form>
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
    $(function () {
        $('#example1').DataTable({
            responsive: true,
            lengthChange: false,
            autoWidth: false
        });

        <?php if ($message): ?>
        Swal.fire({
            icon: '<?php echo $message_type === "success" ? "success" : "error"; ?>',
            title: '<?php echo $message_type === "success" ? "Success!" : "Error!"; ?>',
            text: '<?php echo $message; ?>',
            timer: 3000,
            showConfirmButton: false
        });
        <?php endif; ?>
    });


    // Edit Announcement - using SweetAlert and Modal
    $('.edit-announcement').on('click', function() {
        var announcementId = $(this).data('id');

        // Fetch announcement data via AJAX (consider using fetch API or jQuery.ajax)
        $.ajax({
            url: 'get_announcement.php', // Create this file to fetch announcement data
            type: 'GET',
            data: { id: announcementId },
            dataType: 'json',
            success: function(response) {
                if (response && response.id) {
                    // Populate the modal with the data
                    $('#edit_update_id').val(response.id);
                    $('#edit_title').val(response.title);
                    $('#edit_content').val(response.content);
                    $('#edit_author').val(response.author);
                    $('#edit_current_image').attr('src', response.image_path);


                    // Show the modal
                    $('#editAnnouncementModal').modal('show');
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Failed to load announcement data.'
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Failed to fetch announcement data.'
                });
            }
        });
    });

    // Delete Announcement - Using SweetAlert
    $('.delete-announcement').on('click', function(e) {
        e.preventDefault(); // Prevent the default link behavior
        var announcementId = $(this).data('id');

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
                // Redirect or submit a form to delete the announcement
                window.location.href = '<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?delete_id=' + announcementId;
            }
        });
    });
</script>

</body>
</html>
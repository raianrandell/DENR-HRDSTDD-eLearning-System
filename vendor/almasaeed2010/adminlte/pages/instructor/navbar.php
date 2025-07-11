<?php
if (!isset($_SESSION['instructor_id'])) {
    header("Location: instructorlogin.php");
    exit();
}

// Check if the user has a photo, otherwise display a sample avatar
$photo = !empty($_SESSION['photo']) ? '../../dist/img/' . $_SESSION['photo'] : '../../dist/img/default-user.png';
$name = $_SESSION['name'];
?>
 <!-- Google Font: Source Sans Pro -->
 <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
<!-- Navbar -->
<nav class="main-header navbar navbar-expand navbar-white bg-light">
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link text-secondary" data-widget="pushmenu" href="#" role="button">
                <i class="fas fa-bars"></i>
            </a>
        </li>
    </ul>

    <!-- User Profile Dropdown (Aligned to Right) -->
    <ul class="navbar-nav ml-auto">
        <li class="nav-item dropdown">
            <a class="nav-link" href="#" role="button" data-toggle="dropdown">
                <span class="text-secondary"><?php echo htmlspecialchars($name); ?></span>&nbsp;
                <img src="<?php echo $photo; ?>" class="img-circle elevation-2" alt="User Image" width="30" height="30" onerror="this.src='../../dist/img/dangerfield.png'">
                
                <i class="fas fa-caret-down ml-1 text-secondary"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-right">
                <a class="dropdown-item" href="change_password.php">
                    <i class="fas fa-key mr-2"></i> Change Password
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item text-danger" href="logout.php">
                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                </a>
            </div>
        </li>
    </ul>
</nav>

<!-- jQuery and Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

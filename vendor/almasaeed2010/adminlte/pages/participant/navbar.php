<?php
// navbar.php
if (!isset($_SESSION['participant_id'])) {
    header("Location: participantlogin.php");
    exit();
}

// User Info
$photo = !empty($_SESSION['photo']) ? '../../dist/img/' . $_SESSION['photo'] : '../../dist/img/default-user.png';
// Use $_SESSION['name'] which is set in participantlogin.php
$name = isset($_SESSION['name']) ? $_SESSION['name'] : 'Default Name';
?>

<!-- Navbar -->
<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left Navbar Links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link text-secondary" data-widget="pushmenu" href="#" role="button">
                <i class="fas fa-bars"></i>
            </a>
        </li>
    </ul>

    <!-- Right Navbar Links -->
    <ul class="navbar-nav ml-auto">
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-expanded="false">
                <span class="text-secondary"><?php echo htmlspecialchars($name); ?></span>
                <img src="<?php echo $photo; ?>" class="img-circle elevation-2 ml-2" alt="User Image" width="30" height="30" onerror="this.src='../../dist/img/dangerfield.png'">
            </a>
            <div class="dropdown-menu dropdown-menu-right">
                <a class="dropdown-item" href="#">
                    <i class="fas fa-key mr-2"></i> Change Password
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item text-danger" href="participantlogin.php">
                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                </a>
            </div>
        </li>
    </ul>
</nav>
<!-- jQuery and Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<?php
  // Using basename($_SERVER['PHP_SELF']) ensures we're getting the current script name correctly
  // Consider using parse_url if you have complex URLs or query strings
  // $current_page = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
  $current_page = basename($_SERVER['PHP_SELF']);

  // Define arrays for page groups to simplify menu-open/active logic
  $manage_user_pages = ['participants.php', 'instructors.php', 'coursemanager.php'];
  $report_pages = ['report_participants_with_training.php', 'report_participants_without_training.php'];
?>

<!-- Main Sidebar Container -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">

<aside class="main-sidebar bg-dark elevation-4">
  <!-- Brand Logo -->
  <a href="admindashboard.php" class="brand-link d-flex align-items-center">
    <img src="../../dist/img/denrlogo.jpg" alt="logo" class="brand-image img-circle elevation-3" style="opacity: .8">
    <span class="brand-text font-weight-bold text-white h3 d-flex align-items-center mt-1">
       <span class="text-info">DE</span><span class="text-success">NR</span> <!-- Use   for space -->
    </span>
  </a>

  <!-- Sidebar -->
  <div class="sidebar">
    <!-- Sidebar Menu -->
    <nav class="mt-2">
      <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">

        <!-- Dashboard -->
        <li class="nav-item">
          <a href="admindashboard.php" class="nav-link <?php echo ($current_page == 'admindashboard.php') ? 'bg-white' : ''; ?>">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>Dashboard</p>
          </a>
        </li>

        <!-- Manage Users Dropdown -->
        <li class="nav-item has-treeview <?php echo in_array($current_page, $manage_user_pages) ? 'menu-open' : ''; ?>">
          <a href="#" class="nav-link <?php echo in_array($current_page, $manage_user_pages) ? 'active' : ''; ?>">
            <i class="nav-icon fas fa-users"></i>
            <p>
              Manage Users
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="participants.php" class="nav-link <?php echo ($current_page == 'participants.php') ? 'bg-white' : ''; ?>">
                <i class="fas fa-user nav-icon"></i>
                <p>Participants</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="instructors.php" class="nav-link <?php echo ($current_page == 'instructors.php') ? 'bg-white' : ''; ?>">
                <i class="fas fa-chalkboard-teacher nav-icon"></i>
                <p>Subject Matter Expert</p>
              </a>
            </li>
            <li class="nav-item">
            <a href="coursemanager.php" class="nav-link <?php echo ($current_page == 'coursemanager.php') ? 'bg-white' : ''; ?>">
                <i class="fas fa-book nav-icon"></i>
                <p>Course Manager</p>
            </a>
            </li>
          </ul>
        </li>

        <!-- Trainings -->
        <li class="nav-item">
          <a href="training.php" class="nav-link <?php echo ($current_page == 'training.php') ? 'bg-white' : ''; ?>">
            <i class="nav-icon fas fa-book"></i>
            <p>Trainings</p>
          </a>
        </li>

        <!-- Announcements -->
        <li class="nav-item">
          <a href="announcements.php" class="nav-link <?php echo ($current_page == 'announcements.php') ? 'bg-white' : ''; ?>">
            <i class="nav-icon fas fa-bullhorn"></i>
            <p>Announcements</p>
          </a>
        </li>

        <!-- Free Trainings -->
        <li class="nav-item">
          <a href="free_trainings.php" class="nav-link <?php echo ($current_page == 'free_trainings.php') ? 'bg-white' : ''; ?>">
            <i class="nav-icon fas fa-certificate"></i>
            <p>Free Trainings</p>
          </a>
        </li>

        <!-- Reports Dropdown -->
        <li class="nav-item has-treeview <?php echo in_array($current_page, $report_pages) ? 'menu-open' : ''; ?>">
          <a href="#" class="nav-link <?php echo in_array($current_page, $report_pages) ? 'active' : ''; ?>">
            <i class="nav-icon fas fa-file-alt"></i>
            <p>
              Reports
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="report_participants_with_training.php" class="nav-link <?php echo ($current_page == 'report_participants_with_training.php') ? 'bg-white' : ''; ?>">
                <i class="fas fa-user-check nav-icon"></i>
                <p>List of Participants with Training</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="report_participants_without_training.php" class="nav-link <?php echo ($current_page == 'report_participants_without_training.php') ? 'bg-white' : ''; ?>">
                <i class="fas fa-user-times nav-icon"></i>
                <p>List of Participants without Training</p>
              </a>
            </li>
          </ul>
        </li>

      </ul>
    </nav>
    <!-- /.sidebar-menu -->
  </div>
  <!-- /.sidebar -->
</aside>
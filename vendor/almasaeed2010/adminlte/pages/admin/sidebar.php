<?php
  // Using basename($_SERVER['PHP_SELF']) ensures we're getting the current script name correctly
  $current_page = basename($_SERVER['PHP_SELF']);

  // Optional improvement: Try using $_SERVER['REQUEST_URI'] if basename($_SERVER['PHP_SELF']) doesn't work well in some cases
  // $current_page = basename($_SERVER['REQUEST_URI']);
?>

<!-- Main Sidebar Container -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">

<aside class="main-sidebar bg-dark elevation-4">
  <!-- Brand Logo -->
  <a href="admindashboard.php" class="brand-link d-flex align-items-center">
    <img src="../../dist/img/denrlogo.jpg" alt="logo" class="brand-image img-circle elevation-3" style="opacity: .8">
    <span class="brand-text font-weight-bold text-white h3 d-flex align-items-center mt-1">
      &nbsp;<span class="text-info">DE</span><span class="text-success">NR</span>
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
        <li class="nav-item has-treeview <?php echo ($current_page == 'participants.php' || $current_page == 'instructors.php') ? 'menu-open' : ''; ?>">
          <a href="#" class="nav-link">
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
          </ul>
        </li>

        <!-- Trainings -->
        <li class="nav-item">
          <a href="training.php" class="nav-link <?php echo ($current_page == 'training.php') ? 'bg-white' : ''; ?>">
            <i class="nav-icon fas fa-book"></i>
            <p>Trainings</p>
          </a>
        </li>

        <!-- ✅ Course Manager -->
        <li class="nav-item">
          <a href="coursemanager.php" class="nav-link <?php echo ($current_page == 'coursemanager.php') ? 'bg-white' : ''; ?>">
            <i class="nav-icon fas fa-user-cog"></i>
            <p>Course Manager</p>
          </a>
        </li>

        <!-- Reports Dropdown -->
        <li class="nav-item has-treeview <?php echo ($current_page == 'daily.php' || $current_page == 'weekly.php' || $current_page == 'monthly.php' || $current_page == 'annual.php') ? 'menu-open' : ''; ?>">
          <a href="#" class="nav-link">
            <i class="nav-icon fas fa-file-alt"></i>
            <p>
              Reports
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="#.php" class="nav-link <?php echo ($current_page == '#.php') ? 'bg-white' : ''; ?>">
                <i class="fas fa-user-check nav-icon"></i> 
                <p>List of Participants with Training</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="#.php" class="nav-link <?php echo ($current_page == '#.php') ? 'bg-white' : ''; ?>">
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

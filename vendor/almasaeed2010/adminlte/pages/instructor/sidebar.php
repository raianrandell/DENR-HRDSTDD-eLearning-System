<?php
  $current_page = basename($_SERVER['PHP_SELF']);
  $reports_pages = ['daily.php', 'weekly.php', 'monthly.php', 'annual.php']; // List of report pages
  $is_reports_active = in_array($current_page, $reports_pages); // Check if current page is in Reports
?>

<!-- Main Sidebar Container -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
 <!-- Google Font: Source Sans Pro -->
 <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">

<aside class="main-sidebar bg-dark elevation-4">
  <!-- Brand Logo -->
  <a href="instructordashboard.php" class="brand-link d-flex align-items-center">
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
          <a href="instructordashboard.php" class="nav-link <?php echo ($current_page == 'instructordashboard.php') ? 'bg-info' : ''; ?>">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>Dashboard</p>
          </a>
          <a href="grades.php" class="nav-link <?php echo ($current_page == 'grades.php') ? 'bg-info' : ''; ?>">
            <i class="nav-icon fas fa-clipboard-list"></i>
            <p>Assign Grades</p>
          </a>


        </li>
      </ul>
    </nav>
    <!-- /.sidebar-menu -->
  </div>
  <!-- /.sidebar -->
</aside>

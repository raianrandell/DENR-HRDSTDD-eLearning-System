<?php
  // Define pages belonging to the 'Trainings' section for active state highlighting
  $training_pages = [
      'courseManager.php',
      'createTraining.php',
      'assignParticipants.php',
      'assignInstructors.php',
      'participantsList.php', // Assuming you have this page
      'instructorsList.php',  // Assuming you have this page
  ];
  $module_pages = ['modules.php']; // Add other relevant module pages if any

  // Get the filename of the currently executing script
  $current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- Main Sidebar Container -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<!-- Google Font: Source Sans Pro (Ensure it's not duplicated in main layout) -->
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">

<aside class="main-sidebar bg-dark elevation-4">
  <!-- Brand Logo -->
  <a href="courseManager.php" class="brand-link d-flex align-items-center">
    <!-- Adjust the path relative to the including file's location or use an absolute path if needed -->
    <img src="../../dist/img/denrlogo.jpg" alt="logo" class="brand-image img-circle elevation-3" style="opacity: .8">
    <span class="brand-text font-weight-bold text-white h3 d-flex align-items-center mt-1">
       <span class="text-info">DE</span><span class="text-success">NR</span>
    </span>
  </a>

  <!-- Sidebar -->
  <div class="sidebar">
    <!-- Sidebar Menu -->
    <nav class="mt-2">
      <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
        <!-- Trainings -->
        <li class="nav-item">
          <a href="courseManager.php" class="nav-link <?php echo (in_array($current_page, $training_pages)) ? 'bg-white text-dark' : 'text-white'; ?>">
            <i class="nav-icon fas fa-book"></i>
            <p>Trainings</p>
          </a>
        </li>
        <!-- Modules -->
        <li class="nav-item">
        <a href="modules.php" class="nav-link <?php echo (in_array($current_page, $module_pages)) ? 'bg-white text-dark' : 'text-white'; ?>">
            <i class="nav-icon fas fa-folder"></i>
            <p>Modules</p>
        </a>
        </li>
        <!-- Add more nav-items here as needed -->

      </ul>
    </nav>
    <!-- /.sidebar-menu -->
  </div>
  <!-- /.sidebar -->
</aside>
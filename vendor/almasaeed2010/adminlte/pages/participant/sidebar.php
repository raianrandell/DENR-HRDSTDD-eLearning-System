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
  <a href="participantdashboard.php" class="brand-link d-flex align-items-center">
    <img src="../../dist/img/denrlogo.jpg" alt="logo" class="brand-image img-circle elevation-3" style="opacity: .8">
    <span class="brand-text font-weight-bold text-white h3 d-flex align-items-center mt-1">
       <span class="text-info">DE</span><span class="text-success">NR</span>
    </span>
</a>

  <!-- Sidebar -->
  <div class="sidebar">
    <!-- Sidebar Menu -->
    <nav class="mt-2">
      <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
        <!-- Dashboard -->
        <li class="nav-item brand-text">
          <a href="participantdashboard.php" class="nav-link <?php echo ($current_page == 'participantdashboard.php') ? 'bg-success' : ''; ?>">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>Dashboard</p>
          </a>
        </li>
        <!-- Grade -->
        <li class="nav-item brand-text">
          <a href="grade.php" class="nav-link <?php echo ($current_page == 'grade.php') ? 'bg-success' : ''; ?>">
            <i class="nav-icon fas fa-graduation-cap"></i>
            <p>Grade</p>
          </a>
        </li>
        <!-- Certificate
        <li class="nav-item brand-text">
          <a href="certificate.php" class="nav-link <?php echo ($current_page == 'certificate.php') ? 'bg-success' : ''; ?>">
            <i class="nav-icon fas fa-certificate"></i>
            <p>Certificate</p>
          </a>
        </li> -->
        <!-- History -->
        <li class="nav-item brand-text">
          <a href="history.php" class="nav-link <?php echo ($current_page == 'history.php') ? 'bg-success' : ''; ?>">
            <i class="nav-icon fas fa-history"></i>
            <p>History</p>
          </a>
        </li>
      </ul>
    </nav>
    <!-- /.sidebar-menu -->
  </div>
  <!-- /.sidebar -->
</aside>

<!-- The relevant CSS should be in a separate file or a <style> tag in the <head> -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Example Page</title>
    <!-- Link to your main CSS file (or embed styles in a <style> tag) -->
    <link rel="stylesheet" href="style.css">
    <!-- Link to Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">

    <style>
          body {
            font-family: 'Source Sans Pro', sans-serif; /* Make sure the font family name matches exactly */
          }
          /* Example usage of the font in other elements */
          .brand-text {
            font-family: 'Source Sans Pro', sans-serif;
          }
    </style>
</head>
<body>

</body>
</html>
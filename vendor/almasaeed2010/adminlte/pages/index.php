<?php
require 'includes/config.php'; // Include your database connection
$conn->set_charset("utf8mb4");

// --- Fetch Upcoming Courses (Not strictly needed for display, but kept for potential future use) ---
$upcomingCourses = array();
$sql_upcoming = "SELECT id, training_title, `description`, image_path, training_link, training_hrs, `start_date`, end_date
                 FROM free_trainings
                 WHERE start_date > CURDATE()
                 ORDER BY start_date ASC
                 LIMIT 3";
$result_upcoming = $conn->query($sql_upcoming);
if ($result_upcoming && $result_upcoming->num_rows > 0) {
    while($row = $result_upcoming->fetch_assoc()) {
        $row['image_path'] = (!empty($row['image_path']) && file_exists('uploads/' . $row['image_path'])) ? 'uploads/' . $row['image_path'] : 'images/course_placeholder.jpg';
        $upcomingCourses[] = $row;
    }
} elseif (!$result_upcoming) {
    error_log("Error fetching upcoming courses: " . $conn->error);
}

// --- Fetch All Courses (for Courses section) ---
$allCourses = array();
$sql_all = "SELECT id, training_title, description, image_path, training_link, training_hrs, start_date, end_date
            FROM free_trainings
            ORDER BY start_date DESC";
$result_all = $conn->query($sql_all);
if ($result_all && $result_all->num_rows > 0) {
    while($row = $result_all->fetch_assoc()) {
        $row['image_path'] = (!empty($row['image_path']) && file_exists('uploads/' . $row['image_path'])) ? 'uploads/' . $row['image_path'] : 'images/course_placeholder.jpg';
        $allCourses[] = $row;
    }
} elseif (!$result_all) {
    error_log("Error fetching all courses: " . $conn->error);
}

// --- Fetch Announcements (Limit 5 for Carousel) ---
$announcements = array();
$sql_announcements = "SELECT id, title, content, image_path, author, created_at
                      FROM announcements
                      ORDER BY created_at DESC
                      LIMIT 5";
$result_announcements = $conn->query($sql_announcements);
if ($result_announcements && $result_announcements->num_rows > 0) {
    while($row = $result_announcements->fetch_assoc()) {
        $row['image_path_full'] = null;
        if(!empty($row['image_path'])) {
            $potential_path = 'images/announcements/' . $row['image_path'];
             if(file_exists($potential_path)) {
                $row['image_path_full'] = $potential_path;
             }
        }
        $announcements[] = $row;
    }
} elseif (!$result_announcements) {
     error_log("Error fetching announcements: " . $conn->error);
}

// Close connection
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>TDDLMS - Training & Development Hub (Layout 2)</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <!-- AOS CSS -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
        /* --- Custom CSS for Layout 2 --- */
        :root {
            --denr-dark-blue: #003366;
            --denr-green: #28a745; /* Bootstrap Success Green */
            --light-gray: #f8f9fa;
            --medium-gray: #e9ecef;
            --dark-gray: #343a40;
        }
        body {
            font-family: 'Roboto', sans-serif; /* Changed font */
            padding-top: 70px;
            line-height: 1.7; /* Slightly increased line height */
            overflow-x: hidden !important;
            background-color: #ffffff; /* Default white background */
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: 'Poppins', sans-serif; /* Headings font */
            font-weight: 600; /* Bolder headings */
        }

        /* Custom DENR Colors */
        .bg-denr-blue { background-color: var(--denr-dark-blue) !important; }
        .text-denr-blue { color: var(--denr-dark-blue) !important; }
        .btn-denr-blue {
            color: #fff; background-color: var(--denr-dark-blue); border-color: var(--denr-dark-blue);
            transition: all 0.3s ease;
        }
        .btn-denr-blue:hover { color: #fff; background-color: #002244; border-color: #001a33; }
        .text-denr-green { color: var(--denr-green) !important; }
        .btn-denr-green {
            color: #fff; background-color: var(--denr-green); border-color: var(--denr-green);
            transition: all 0.3s ease;
        }
        .btn-denr-green:hover { color: #fff; background-color: #218838; border-color: #1e7e34; }

        /* --- Section Styling --- */
        section {
            padding: 80px 0; /* Increased padding */
            position: relative;
        }
        .section-divider {
            width: 80px;
            height: 4px;
            background-color: var(--denr-green);
            margin: 0 auto 30px auto;
            border-radius: 2px;
        }
        .section-heading {
            font-size: 2.5rem; /* Larger heading */
            font-weight: 700;
            margin-bottom: 15px;
            color: var(--denr-dark-blue);
        }
        .section-subheading {
            font-size: 1.1rem;
            color: #6c757d; /* Muted text */
            margin-bottom: 60px; /* More space after subheading */
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }


        /* Navbar Adjustments */
        .navbar {
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 2px 5px rgba(0,0,0,0.08);
            padding: 0.8rem 1rem; /* Slightly more padding */
        }
        .navbar-brand img { width: 45px; height: 45px; }
        .navbar-brand { font-size: 1.2rem; font-weight: 600; }

        /* --- NEW: Styles for Custom Nav List --- */
        .navbar-dark .main-nav-list {
            display: flex;
            flex-direction: column; /* Stacked on mobile */
            padding-left: 0;
            margin-bottom: 0;
            list-style: none;
        }
        /* Apply horizontal layout on larger screens (adjust breakpoint if needed) */
        @media (min-width: 992px) { /* Bootstrap lg breakpoint */
           .navbar-dark .main-nav-list {
               flex-direction: row; /* Row layout */
           }
        }

        /* Style the links within the custom list */
        .navbar-dark .main-nav-list .nav-link {
            display: inline-block;
            position: relative;
            color: rgba(255, 255, 255, 0.85);
            padding: 0.5rem 1rem; /* Keep Bootstrap's link padding */
            margin: 0 3px; /* Spacing between items */
            border-radius: 4px;
            transition: transform 0.3s ease, color 0.3s ease;
            font-size: 0.95rem;
            display: block; /* Ensure padding works */
            text-decoration: none; /* Ensure no underline */
        }
        .navbar-dark .main-nav-list .nav-link:hover,
        .navbar-dark .main-nav-list .nav-link:focus {
            transform: translateY(-5px);
            color: var(--denr-green); /* Change color on hover */
        }
        /* --- END: Styles for Custom Nav List --- */

        .dropdown-menu { background-color: #002a55; border: 1px solid rgba(255, 255, 255, 0.1); font-size: 0.9rem; }
        .dropdown-item { color: rgba(255, 255, 255, 0.85); transition: background-color 0.2s ease, color 0.2s ease; padding: 0.5rem 1rem; }
        .dropdown-item:hover, .dropdown-item:focus { color: #fff; background-color: rgba(255, 255, 255, 0.1); }
        .btn-login-toggle {
             font-size: 0.85rem;
             padding: 0.4rem 0.8rem;
        }

        /* Hero Section Override */
        #home-section {
            background: linear-gradient(rgba(0, 51, 102, 0.6), rgba(0, 26, 51, 0.7)), url('../dist/img/denrbg.jpg') no-repeat center center fixed;
            background-size: cover;
            min-height: calc(100vh - 70px); margin-top: -70px; /* Adjust for navbar */
            display: flex; align-items: center; justify-content: center;
            padding: 60px 0; /* Add some padding */
        }
         .hero-content {
             opacity: 0; transform: translateY(30px);
             transition: opacity 1.2s cubic-bezier(0.25, 0.8, 0.25, 1), transform 1.2s cubic-bezier(0.25, 0.8, 0.25, 1);
         }
         .hero-content.fade-in { opacity: 1; transform: translateY(0); }
         .hero-content h1 { font-size: 3.2rem; font-weight: 700; text-shadow: 2px 2px 4px rgba(0,0,0,0.4); }
         .hero-content .lead { font-size: 1.3rem; margin-bottom: 2rem; max-width: 700px; margin-left: auto; margin-right: auto; text-shadow: 1px 1px 2px rgba(0,0,0,0.3); }
         .hero-content .btn { font-size: 1.1rem; padding: 14px 35px; box-shadow: 0 4px 10px rgba(0,0,0,0.2); }
         /* Optional Scroll Down Hint */
         .scroll-down-hint {
             position: absolute;
             bottom: 30px;
             left: 50%;
             transform: translateX(-50%);
             color: rgba(255, 255, 255, 0.7);
             font-size: 1.8rem;
             animation: bounce 2s infinite;
             cursor: pointer;
             z-index: 10;
         }
         @keyframes bounce { 0%, 20%, 50%, 80%, 100% { transform: translateX(-50%) translateY(0); } 40% { transform: translateX(-50%) translateY(-15px); } 60% { transform: translateX(-50%) translateY(-7px); } }


        /* --- Announcements Section --- */
        #announcements-section {
            background-color: var(--light-gray); /* Light background */
             border-bottom: 1px solid var(--medium-gray); /* Subtle separator */
        }
        #announcementCarousel {
            background-color: #fff; /* White background for the carousel itself */
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            overflow: hidden; /* Needed for rounded corners */
        }
        #announcementCarousel .carousel-inner {
            padding: 40px 50px; /* More internal padding */
        }
        #announcementCarousel .announcement-slide-content {
             min-height: 160px; /* Adjust min-height */
             display: flex; flex-direction: column; justify-content: center;
             text-align: center;
        }
         #announcementCarousel .announcement-slide-content h4 {
             font-size: 1.4rem; /* Slightly larger title */
             color: var(--denr-dark-blue);
             margin-bottom: 1rem;
         }
         #announcementCarousel .announcement-slide-content p {
             font-size: 0.95rem;
             color: #555;
         }
         #announcementCarousel .announcement-slide-content .text-muted.small {
             font-size: 0.8rem;
             margin-top: 1.5rem;
         }

        /* Carousel Controls/Indicators (Using previous good style) */
        #announcements-section .carousel-indicators li { background-color: rgba(0, 51, 102, 0.4); border: none; height: 4px; width: 25px; margin-left: 5px; margin-right: 5px; border-radius: 2px; transition: background-color 0.3s ease; }
        #announcements-section .carousel-indicators .active { background-color: var(--denr-dark-blue); }
        #announcements-section .carousel-control-prev-icon, #announcements-section .carousel-control-next-icon { background-color: var(--denr-dark-blue); background-image: none; border-radius: 50%; padding: 15px; opacity: 0.7; transition: opacity 0.2s ease, background-color 0.2s ease; width: 2.2em; height: 2.2em; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 5px rgba(0,0,0,0.2); }
        #announcements-section .carousel-control-prev-icon::before, #announcements-section .carousel-control-next-icon::before { font-family: "Font Awesome 6 Free"; font-weight: 900; color: white; font-size: 0.9em; }
        #announcements-section .carousel-control-prev-icon::before { content: "\f053"; }
        #announcements-section .carousel-control-next-icon::before { content: "\f054"; }
        #announcements-section .carousel-control-prev:hover .carousel-control-prev-icon, #announcements-section .carousel-control-next:hover .carousel-control-next-icon { opacity: 1; background-color: #002244; }
        #announcements-section .carousel-control-prev, #announcements-section .carousel-control-next { width: 8%; opacity: 1; }


        /* --- Courses Section --- */
        #courses-section {
            background-color: #ffffff; /* Back to white */
             border-bottom: 1px solid var(--medium-gray);
        }
        .filter-section {
             background-color: var(--light-gray); /* Lighter bg for filter */
             padding: 15px 20px;
             border-radius: 6px;
             margin-bottom: 50px; /* Space before grid */
             box-shadow: 0 2px 5px rgba(0,0,0,0.05);
             display: flex;
             flex-wrap: wrap;
             justify-content: center;
             align-items: center;
        }
        .filter-section label { margin-bottom: 0; margin-right: 10px; font-weight: 500; color: var(--denr-dark-blue); }
        .filter-section .custom-select { font-size: 0.9rem; }

        /* Course Card Enhancements */
        .course-card {
            border: none; /* Remove default border */
            border-radius: 8px; /* Softer corners */
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08); /* Softer shadow */
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
            overflow: hidden; /* Ensure image corners match */
        }
        .course-card:hover {
            transform: translateY(-5px); /* Lift effect */
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        }
        .course-card .card-img-top {
            height: 180px; /* Slightly shorter image */
            object-fit: cover;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
        }
        .course-card .card-body { padding: 1.2rem; }
        .course-card .card-title { font-size: 1.1rem; font-weight: 600; color: var(--denr-dark-blue); margin-bottom: 0.5rem; }
        .course-card .course-meta { font-size: 0.8rem; color: #6c757d; margin-bottom: 0.8rem; }
        .course-card .course-meta i { color: var(--denr-green); }
        .course-card .card-text { font-size: 0.9rem; color: #555; margin-bottom: 1rem; }
        .course-card .btn-sm {
             padding: 0.3rem 0.8rem;
             font-size: 0.8rem;
             border-radius: 20px; /* Pill shape */
             background-color: var(--denr-green);
             border-color: var(--denr-green);
             color: #fff;
        }
        .course-card .btn-sm:hover { background-color: #218838; border-color: #1e7e34; }


        /* --- About Section --- */
        #about-section {
            background-color: var(--light-gray);
            /* Or use a subtle pattern:
            background-image: url('path/to/subtle-pattern.png'); */
             border-bottom: 1px solid var(--medium-gray);
        }
        .about-img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            object-fit: cover; /* Ensure image covers space well */
            max-height: 450px; /* Optional max height */
        }
        #about-section .content-column h3 {
            font-size: 1.8rem;
            color: var(--denr-dark-blue);
            margin-bottom: 1rem;
             margin-top: 0; /* Reset margin for column layout */
        }
         #about-section .content-column h5 {
             font-size: 1.2rem;
             color: var(--denr-green);
             font-weight: 600;
             margin-top: 1.5rem;
             margin-bottom: 0.5rem;
         }
         #about-section .content-column p {
             font-size: 0.95rem;
             color: #495057;
             margin-bottom: 1rem;
         }
        #about-section .blockquote {
            border-left: 4px solid var(--denr-green);
            background-color: #fff;
            padding: 1rem 1.5rem;
            margin: 1.5rem 0;
            font-size: 0.95rem;
            color: #555;
            border-radius: 4px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        #about-section .blockquote strong { color: var(--denr-dark-blue); }
        .about-cta-button {
             margin-top: 1.5rem;
        }

        /* --- Contact Section --- */
        #contact-section {
            background: linear-gradient(rgba(0, 51, 102, 0.85), rgba(0, 26, 51, 0.9)), url('../dist/img/map-bg.png') no-repeat center center; /* Example subtle map bg */
            background-size: cover;
            color: #f8f9fa; /* Light text on dark background */
             padding: 100px 0; /* More padding */
        }
        #contact-section .section-heading {
            color: #fff; /* White heading */
        }
         #contact-section .section-subheading {
            color: rgba(255, 255, 255, 0.8); /* Lighter subheading */
        }
         #contact-section .section-divider {
             background-color: var(--denr-green); /* Green divider still */
         }

        .contact-info-box {
            background-color: rgba(255, 255, 255, 0.1); /* Semi-transparent white */
            padding: 30px;
            border-radius: 8px;
            margin-bottom: 30px;
            transition: background-color 0.3s ease;
            height: 100%; /* Equal height boxes */
            display: flex;
            flex-direction: column;
            align-items: center; /* Center content */
        }
        .contact-info-box:hover {
             background-color: rgba(255, 255, 255, 0.15);
        }
        .contact-info-box i.fas {
            font-size: 2.5rem; /* Larger icons */
            color: var(--denr-green);
            margin-bottom: 1.5rem;
        }
         .contact-info-box h4 {
             font-size: 1.2rem;
             color: #fff;
             margin-bottom: 1rem;
             font-weight: 600;
         }
         .contact-info-box p {
             font-size: 0.9rem;
             color: rgba(255, 255, 255, 0.85);
             margin-bottom: 0.5rem;
             line-height: 1.6;
         }
          .contact-info-box a {
             color: #a5d6a7; /* Light green link */
             text-decoration: none;
             transition: color 0.3s ease;
         }
         .contact-info-box a:hover {
             color: #ffffff;
             text-decoration: underline;
         }
         /* Optional: Hide contact form for now */
         #contactFormSection { display: none; }


        /* Footer */
        footer {
            background-color: var(--dark-gray); /* Darker footer */
            color: #adb5bd; /* Lighter gray text */
            padding-top: 60px;
            padding-bottom: 0; /* Remove bottom padding */
            font-size: 0.9rem;
        }
        footer h4 {
            font-size: 1.1rem;
            color: #fff;
            margin-bottom: 1.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #495057;
            padding-bottom: 10px;
        }
        footer p, footer li { color: #adb5bd; }
        footer a { color: #dee2e6; transition: color 0.3s ease; }
        footer a:hover { color: #fff; text-decoration: none; }
        footer .list-unstyled li { margin-bottom: 0.7rem; }
        footer .list-unstyled i { color: var(--denr-green); margin-right: 8px; font-size: 0.8rem; }
        footer .social-icons a {
            color: #adb5bd; font-size: 1.4rem; margin-right: 15px;
            display: inline-block; transition: color 0.3s ease, transform 0.3s ease;
        }
        footer .social-icons a:hover { color: #fff; transform: translateY(-3px); }
        .footer-bottom {
            background-color: #212529; /* Even darker bottom bar */
            padding: 15px 0;
            margin-top: 40px;
            font-size: 0.85rem;
        }
        .footer-bottom p { margin-bottom: 0; color: #6c757d; }

        /* Modal Adjustments */
         .modal-header.bg-denr-blue .close { color: #fff; opacity: 0.9; text-shadow: none; transition: opacity 0.2s ease; }
         .modal-header.bg-denr-blue .close:hover { opacity: 1; }
         .modal-banner { max-height: 280px; width: 100%; object-fit: cover; }
         .modal-body { padding: 0; } /* Keep padding=0 for banner */
         .modal-content-area { padding: 2rem; } /* Add padding for text content */
         .modal-course-title { font-size: 1.8rem; font-weight: 700; }
         .modal-course-details span { font-size: 0.9rem; margin-right: 1.5rem; }
         .modal-course-details i { margin-right: 5px; }
         .modal-course-description { font-size: 1rem; line-height: 1.7; color: #444; }
         .modal-footer { background-color: var(--light-gray); }
         .modal-footer .btn { border-radius: 20px; padding: 8px 25px; font-size: 0.9rem; }
         .modal-footer .btn-success { background-color: var(--denr-green); border-color: var(--denr-green); }
         .modal-footer .btn-success:hover { background-color: #218838; border-color: #1e7e34; }


    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-denr-blue fixed-top shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="#home-section">
                <img src="../dist/img/denrlogo.jpg" alt="Logo" class="d-inline-block align-top rounded-circle mr-2">
                 Training Development Hub
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <!-- Use custom classes for the main nav list -->
                <ul class="main-nav-list list-unstyled mx-auto mb-0">
                    <li class="main-nav-item"><a class="nav-link" href="#home-section">Home</a></li>
                    <li class="main-nav-item"><a class="nav-link" href="#courses-section">Courses</a></li>
                    <li class="main-nav-item"><a class="nav-link" href="#announcements-section">Announcements</a></li>
                    <li class="main-nav-item"><a class="nav-link" href="#about-section">About</a></li>
                    <li class="main-nav-item"><a class="nav-link" href="#contact-section">Contact</a></li>
                </ul>
                <div class="dropdown ml-lg-auto">
                    <button class="btn btn-outline-light dropdown-toggle btn-login-toggle" type="button" id="loginDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-sign-in-alt mr-1"></i> Log In
                    </button>
                     <div class="dropdown-menu dropdown-menu-right" aria-labelledby="loginDropdown">
                        <a class="dropdown-item" href="participant/participantlogin.php"><i class="fas fa-user fa-fw mr-2"></i>Participant</a>
                        <a class="dropdown-item" href="instructor/instructorlogin.php"><i class="fas fa-chalkboard-teacher fa-fw mr-2"></i>SME</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Section: Home (Hero) -->
    <section id="home-section" class="text-white text-center">
        <div class="container">
            <div class="hero-content">
                <h1 class="display-4 font-weight-bold mb-3">Empowering Environmental Stewards</h1>
                <p class="lead mb-4">Discover training opportunities and enhance your skills with the DENR's Training and Development Division.</p>
                <a href="#courses-section" class="btn btn-denr-green btn-lg rounded-pill font-weight-bold shadow">Explore Courses Now</a>
            </div>
        </div>
        <!-- Optional Scroll Down Hint -->
         <a href="#announcements-section" class="scroll-down-hint d-none d-md-block" aria-label="Scroll down">
            <i class="fas fa-chevron-down"></i>
        </a>
    </section>

    <!-- Section: Announcements (CAROUSEL VERSION) -->
    <section id="announcements-section" class="py-5">
        <div class="container">
             <div class="text-center">
                <h2 class="section-heading" data-aos="fade-down">Latest Updates</h2>
                <div class="section-divider" data-aos="fade-down" data-aos-delay="100"></div>
                <p class="section-subheading" data-aos="fade-up" data-aos-delay="150">Stay informed with the latest news and announcements from the TDD.</p>
             </div>

            <?php if (!empty($announcements)): ?>
                <div id="announcementCarousel" class="carousel slide" data-ride="carousel" data-interval="8000" data-aos="fade-up" data-aos-delay="250">
                    <ol class="carousel-indicators">
                        <?php foreach ($announcements as $index => $announcement): ?>
                            <li data-target="#announcementCarousel" data-slide-to="<?php echo $index; ?>" class="<?php echo ($index == 0) ? 'active' : ''; ?>"></li>
                        <?php endforeach; ?>
                    </ol>
                    <div class="carousel-inner">
                        <?php foreach ($announcements as $index => $announcement): ?>
                            <div class="carousel-item <?php echo ($index == 0) ? 'active' : ''; ?>">
                                <div class="announcement-slide-content">
                                    <h4><?php echo htmlspecialchars($announcement['title']); ?></h4>
                                    <p>
                                        <?php
                                        $content = $announcement['content'];
                                        $limit = 220; // Adjusted limit
                                        if (strlen($content) > $limit) {
                                            echo nl2br(htmlspecialchars(substr($content, 0, $limit))) . '...';
                                        } else {
                                            echo nl2br(htmlspecialchars($content));
                                        }
                                        ?>
                                    </p>
                                    <p class="text-muted small mt-3">
                                        <i class="far fa-calendar-alt mr-1"></i> <?php echo date("F j, Y", strtotime($announcement['created_at'])); ?>
                                        <?php echo !empty($announcement['author']) ? ' <span class="mx-2 d-none d-md-inline">|</span><br class="d-md-none"> <i class="far fa-user mr-1"></i> By ' . htmlspecialchars($announcement['author']) : ''; ?>
                                    </p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <a class="carousel-control-prev" href="#announcementCarousel" role="button" data-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span> <span class="sr-only">Previous</span>
                    </a>
                    <a class="carousel-control-next" href="#announcementCarousel" role="button" data-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span> <span class="sr-only">Next</span>
                    </a>
                </div>
            <?php else: ?>
                <div class="col-12 text-center" data-aos="fade-up">
                    <p class="lead text-muted mt-4">No recent announcements found.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>


    <!-- Section: Courses (Main Listing) -->
    <section id="courses-section" class="py-5">
        <div class="container">
             <div class="text-center">
                <h2 class="section-heading" data-aos="fade-down">Our Training Catalog</h2>
                 <div class="section-divider" data-aos="fade-down" data-aos-delay="100"></div>
                <p class="section-subheading" data-aos="fade-up" data-aos-delay="150">Explore free programs designed to build capacity for environmental management and sustainable development.</p>
             </div>

            <!-- Filter Section -->
            <div class="filter-section shadow-sm" data-aos="fade-up" data-aos-delay="200">
                 <label for="courseStatusFilter" class="mr-2 my-1"><i class="fas fa-filter mr-1"></i> Filter:</label>
                 <select id="courseStatusFilter" class="form-control form-control-sm custom-select my-1" style="width: auto; min-width: 200px;">
                    <option value="all">All Courses</option>
                    <option value="upcoming">Upcoming Courses</option>
                    <option value="past">Past Courses</option>
                 </select>
            </div>

            <!-- Course Grid -->
            <div class="row course-grid" id="courseGrid">
                <?php if (!empty($allCourses)): ?>
                    <?php foreach ($allCourses as $index => $course):
                        // --- PHP Logic for Status & Data Attributes ---
                        $startDate = strtotime($course['start_date']);
                        $endDate = strtotime($course['end_date']);
                        $now = time();
                        $status = ($startDate > $now) ? 'upcoming' : 'past';
                        $data_attributes = 'data-course-id="' . htmlspecialchars($course['id']) . '" ';
                        $data_attributes .= 'data-title="' . htmlspecialchars($course['training_title']) . '" ';
                        $data_attributes .= 'data-description="' . htmlspecialchars($course['description']) . '" ';
                        $data_attributes .= 'data-image="' . htmlspecialchars($course['image_path']) . '" ';
                        $data_attributes .= 'data-start-date="' . htmlspecialchars($course['start_date']) . '" ';
                        $data_attributes .= 'data-end-date="' . htmlspecialchars($course['end_date']) . '" ';
                        $data_attributes .= 'data-hours="' . htmlspecialchars($course['training_hrs']) . '" ';
                        $data_attributes .= 'data-link="' . htmlspecialchars($course['training_link']) . '" ';
                        $data_attributes .= 'data-status="' . $status . '"';
                        $delay = ($index % 3) * 100;
                    ?>
                        <div class="col-lg-4 col-md-6 mb-4 course-item-col" data-aos="fade-up" data-aos-delay="<?php echo $delay; ?>">
                             <div class="card course-card course-item" <?php echo $data_attributes; ?>>
                                <img src="<?php echo htmlspecialchars($course['image_path']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($course['training_title']); ?>">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title"><?php echo htmlspecialchars($course['training_title']); ?></h5>
                                    <div class="course-meta mb-2">
                                        <span class="mr-3">
                                            <i class="far fa-calendar-alt"></i> <?php echo date("M d", $startDate); ?> - <?php echo date("M d, Y", $endDate); ?>
                                        </span>
                                        <?php if (!empty($course['training_hrs']) && $course['training_hrs'] > 0): ?>
                                            <span><i class="far fa-clock"></i> <?php echo htmlspecialchars($course['training_hrs']); ?> hrs</span>
                                        <?php endif; ?>
                                    </div>
                                    <p class="card-text flex-grow-1"><?php echo htmlspecialchars(substr($course['description'], 0, 100)) . (strlen($course['description']) > 100 ? '...' : ''); ?></p>
                                    <button type="button" class="btn btn-sm mt-auto align-self-start view-course-btn" data-toggle="modal" data-target="#courseModal">
                                        View Details <i class="fas fa-arrow-right ml-1"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                 <?php else: ?>
                    <div class="col-12 text-center" data-aos="fade-up">
                        <p class="lead text-muted">No courses are currently available.</p>
                    </div>
                <?php endif; ?>
                 <div id="noCoursesMessage" class="col-12 text-center" style="display: none;" data-aos="fade-up">
                    <p class="lead text-muted mt-4">No courses match the selected filter.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Section: About Us -->
    <section id="about-section" class="py-5">
        <div class="container">
            <div class="text-center">
                <h2 class="section-heading" data-aos="fade-down">Who We Are</h2>
                <div class="section-divider" data-aos="fade-down" data-aos-delay="100"></div>
                <p class="section-subheading" data-aos="fade-up" data-aos-delay="150">Learn more about the DENR's mandate and the role of the Training and Development Division.</p>
            </div>

             <div class="row align-items-center">
                <!-- Image Column -->
                <div class="col-lg-5 mb-4 mb-lg-0" data-aos="fade-right" data-aos-delay="200">
                    <img src="../dist/img/denrlogo.jpg" alt="DENR Team or Building" class="img-fluid about-img rounded-circle">
                     <!-- Replace 'about-placeholder.jpg' with a relevant image -->
                </div>
                <!-- Content Column -->
                <div class="col-lg-7 content-column" data-aos="fade-left" data-aos-delay="300">
                     <h3>Our Commitment to the Environment</h3>

                    <h5 class="text-denr-green">DENR Mandate & Vision</h5>
                    <p>
                        The Department of Environment and Natural Resources (DENR), established under E.O. 192 (s. 1987), is the primary government agency responsible for the conservation, management, development, and proper use of the country's environment and natural resources, ensuring equitable benefit sharing for present and future generations.
                    </p>
                    <blockquote class="blockquote">
                        <p class="mb-1 font-italic"><strong>Vision:</strong> "A nation enjoying and sustaining its natural resources and a clean and healthy environment."</p>
                         <p class="mb-0 font-italic"><strong>Mission:</strong> "To mobilize our citizenry in protecting, conserving, and managing the environment and natural resources for the present and future generations."</p>
                    </blockquote>

                    <h5 class="text-denr-green">The Training and Development Division (TDD)</h5>
                    <p>
                        The <strong>Training and Development Division (TDD)</strong> focuses on <strong>capacity building</strong> to achieve DENR's goals. We enhance the knowledge, skills, and competencies of DENR personnel and partners through comprehensive training programs covering environmental laws, resource management, climate change, GIS, and more.
                    </p>
                     <p>
                        We utilize diverse learning methods, including workshops, online seminars, and e-learning modules on this platform, empowering individuals to become effective environmental stewards for the Philippines' natural heritage.
                    </p>
                    <div class="text-center text-lg-left about-cta-button">
                        <a href="#contact-section" class="btn btn-denr-green rounded-pill px-4 py-2 shadow-sm">Connect With Us</a>
                    </div>
                </div> <!-- End Content Column -->
            </div> <!-- End Row -->
        </div> <!-- End Container -->
    </section>


    <!-- Section: Contact Us -->
    <section id="contact-section" class="py-5">
        <div class="container">
             <div class="text-center mb-5">
                <h2 class="section-heading" data-aos="fade-down">Get In Touch</h2>
                 <div class="section-divider" data-aos="fade-down" data-aos-delay="100"></div>
                <p class="section-subheading" data-aos="fade-up" data-aos-delay="150">Questions about our programs? Need assistance? Reach out using the details below.</p>
            </div>

            <div class="row justify-content-center text-center">
                 <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="contact-info-box">
                        <i class="fas fa-envelope"></i>
                        <h4>Email Us</h4>
                        <p><strong>General:</strong><br><a href="mailto:tdd.hrds@denr.gov.ph">tdd.hrds@denr.gov.ph</a></p>
                        <p><strong>Support:</strong><br><a href="mailto:training.support@denr.gov.ph">training.support@denr.gov.ph</a></p>
                    </div>
                </div>
                 <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="contact-info-box">
                        <i class="fas fa-phone"></i>
                        <h4>Call Us</h4>
                        <p><strong>Phone:</strong> <a href="tel:+63021234567">(02) 8123 4567</a></p>
                        <p><strong>Mobile:</strong> <a href="tel:+639876543210">0987 654 3210</a></p>
                        <p>Mon-Fri, 8AM-5PM</p>
                    </div>
                </div>
                 <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="400">
                     <div class="contact-info-box">
                        <i class="fas fa-map-marker-alt"></i>
                        <h4>Visit Us</h4>
                         <p>TDD, DENR Central Office<br>Visayas Ave., Diliman, QC</p>
                        <p><a href="https://www.google.com/maps?q=DENR+Visayas+Avenue+Quezon+City" target="_blank" rel="noopener noreferrer">View Map</a></p>
                    </div>
                </div>
            </div>

             <!-- Optional Contact Form Area (Hidden by default in CSS) -->
             <!-- <div id="contactFormSection" class="mt-5" data-aos="fade-up" data-aos-delay="500"> ... form code ... </div> -->

        </div>
    </section>

    <!-- Footer -->
    <footer class="pt-5 pb-0">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-4 mb-lg-0">
                    <h4>TDDLMS Office</h4>
                     <p class="small">Training and Development Division<br>
                     DENR Central Office, Visayas Ave.<br>
                     Diliman, Quezon City 1100</p>
                     <p class="small mb-1"><strong><i class="fas fa-phone-alt fa-fw mr-2 text-denr-green"></i></strong> <a href="tel:+63021234567">(02) 8123 4567</a></p>
                     <p class="small"><strong><i class="fas fa-envelope fa-fw mr-2 text-denr-green"></i></strong> <a href="mailto:tdd.hrds@denr.gov.ph">tdd.hrds@denr.gov.ph</a></p>
                </div>
                <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
                    <h4>Quick Links</h4>
                    <ul class="list-unstyled small">
                        <li><a href="#home-section"><i class="fas fa-angle-right"></i>Home</a></li>
                        <li><a href="#courses-section"><i class="fas fa-angle-right"></i>Courses</a></li>
                        <li><a href="#announcements-section"><i class="fas fa-angle-right"></i>Announcements</a></li>
                        <li><a href="#about-section"><i class="fas fa-angle-right"></i>About Us</a></li>
                        <li><a href="#contact-section"><i class="fas fa-angle-right"></i>Contact Us</a></li>
                        <li><a href="http://denr.gov.ph" target="_blank" rel="noopener noreferrer"><i class="fas fa-external-link-alt"></i>DENR Website</a></li>
                    </ul>
                </div>
                 <div class="col-lg-5 col-md-12 mb-4 mb-lg-0">
                    <h4>Connect With DENR</h4>
                     <p class="small">Follow DENR's official channels:</p>
                    <div class="social-icons">
                        <a href="#" target="_blank" aria-label="Facebook" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" target="_blank" aria-label="Twitter" title="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" target="_blank" aria-label="YouTube" title="YouTube"><i class="fab fa-youtube"></i></a>
                        <a href="http://denr.gov.ph" target="_blank" aria-label="DENR Website" title="DENR Website"><i class="fas fa-globe"></i></a>
                    </div>
                </div>
            </div>
        </div>
        <div class="footer-bottom text-center mt-4">
            <div class="container">
                <p class="small mb-0">© <?php echo date("Y"); ?> Training and Development Division - DENR. All Rights Reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Course Details Modal -->
    <div class="modal fade" id="courseModal" tabindex="-1" role="dialog" aria-labelledby="courseModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content shadow-lg">
                <div class="modal-header bg-denr-blue text-white">
                    <h5 class="modal-title font-weight-bold" id="courseModalLabel">Training Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body p-0">
                    <img class="modal-banner img-fluid" src="images/course_placeholder.jpg" alt="Course Banner">
                    <div class="modal-content-area"> <!-- Padding wrapper for content -->
                        <h3 class="modal-course-title text-denr-blue mb-3">Course Title Placeholder</h3>
                        <div class="modal-course-details text-muted mb-3">
                            <span><i class="far fa-calendar-alt text-success"></i><span class="modal-course-date">Date Placeholder</span></span>
                            <span><i class="far fa-clock text-success"></i><span class="modal-course-hours">Hours Placeholder</span></span>
                        </div>
                        <hr class="my-4">
                        <p class="modal-course-description">Description placeholder...</p>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                     <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <a href="#" target="_blank" rel="noopener noreferrer" class="btn btn-success modal-enroll-btn font-weight-bold" id="modalEnrollLink">
                       Go to Training <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>


    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

    <script>
        $(document).ready(function() {

            // --- Initialize AOS ---
            AOS.init({
                duration: 700, // Slightly faster duration
                easing: 'ease-out-cubic', // Different easing
                once: true,
                offset: 100 // Trigger slightly earlier
            });

            var scrollOffset = 70; // Navbar height

            // --- Smooth Scroll ---
            $('a.nav-link[href*="#"], a.btn[href*="#"], a.navbar-brand[href*="#"], a.scroll-down-hint[href*="#"]').not('[href="#"]').not('[href="#0"]').click(function(event) {
                if (location.pathname.replace(/^\//, '') == this.pathname.replace(/^\//, '') && location.hostname == this.hostname) {
                    var target = $(this.hash);
                    target = target.length ? target : $('[name=' + this.hash.slice(1) + ']');
                    if (target.length) {
                        event.preventDefault();
                        $('html, body').animate({
                            scrollTop: target.offset().top - scrollOffset
                        }, 900, 'swing', function() { // Slightly slower scroll
                            var $target = $(target);
                            $target.attr('tabindex', '-1');
                            $target.focus();
                            setTimeout(function() { $target.removeAttr('tabindex'); }, 1000);
                        });
                         // Collapse mobile nav if open
                         if ($('.navbar-toggler').is(':visible') && $('#navbarNav').hasClass('show')) {
                            $('#navbarNav').collapse('hide');
                        }
                    }
                }
            });

            // --- Active Link Highlighting on Scroll ---
             function setActiveLink() {
                var scrollDistance = $(window).scrollTop();
                 var foundActive = false;
                 $('section').each(function(i) {
                    const sectionTop = $(this).position().top;
                    const sectionHeight = $(this).height();
                    const windowHeight = $(window).height();
                    // Adjusted activation zone logic slightly for more reliable triggering
                    const activationPoint = scrollDistance + scrollOffset + (windowHeight * 0.2); // 20% from top

                    if (sectionTop <= activationPoint && (sectionTop + sectionHeight) > activationPoint) {
                         var sectionId = $(this).attr('id');
                         // Use the updated selector: .main-nav-list .nav-link
                         if ($('.main-nav-list .nav-link[href="#' + sectionId + '"]').length) {
                             $('.main-nav-list .nav-link.active').removeClass('active');
                             $('.main-nav-list .nav-link[href="#' + sectionId + '"]').addClass('active');
                             foundActive = true;
                             return false; // Exit loop once found
                         }
                     }
                 });

                 // Handle edge cases: Top of page and bottom of page
                 if (!foundActive) {
                    if (scrollDistance < ($('#home-section').height() * 0.5) ) { // Near top
                        $('.main-nav-list .nav-link.active').removeClass('active');
                        $('.main-nav-list .nav-link[href="#home-section"]').addClass('active');
                    } else if ($(window).scrollTop() + $(window).height() > $(document).height() - 150) { // Near bottom
                         $('.main-nav-list .nav-link.active').removeClass('active');
                         // Prioritize Contact, then About if Contact doesn't exist/isn't last
                         if ($('.main-nav-list .nav-link[href="#contact-section"]').length) {
                            $('.main-nav-list .nav-link[href="#contact-section"]').addClass('active');
                         } else if ($('.main-nav-list .nav-link[href="#about-section"]').length) {
                             $('.main-nav-list .nav-link[href="#about-section"]').addClass('active');
                         }
                    }
                 }
             }
            $(window).scroll(setActiveLink);
            setActiveLink(); // Call on load

            // --- Course Filtering Logic ---
            const courseGrid = $('#courseGrid');
            const courseCols = courseGrid.find('.course-item-col');
            const noCoursesMessage = $('#noCoursesMessage');
            $('#courseStatusFilter').on('change', function() {
                const filterValue = $(this).val();
                let visibleCount = 0;
                courseCols.each(function() {
                    const courseItem = $(this).find('.course-item');
                    const status = courseItem.data('status');
                    const shouldShow = (filterValue === 'all' || status === filterValue);
                    if (shouldShow) {
                        if (!$(this).is(':visible')) $(this).fadeIn(300).css('display', 'block');
                        visibleCount++;
                    } else {
                         if ($(this).is(':visible')) $(this).fadeOut(200);
                    }
                });
                // Use timeout to ensure fadeOut completes before checking count
                setTimeout(() => {
                    if (visibleCount === 0) {
                        if (!noCoursesMessage.is(':visible')) noCoursesMessage.fadeIn(300);
                    } else {
                         if (noCoursesMessage.is(':visible')) noCoursesMessage.fadeOut(200);
                    }
                    // Optional: Refresh AOS if elements change visibility significantly
                    // AOS.refresh();
                }, 350);
            });

            // --- Course Modal Population ---
             function populateModal(courseElement) {
                 const modal = $('#courseModal');
                 const courseData = courseElement.data();

                 modal.find('.modal-banner').attr('src', courseData.image || 'images/course_placeholder.jpg').attr('alt', (courseData.title || 'Course') + ' Banner');
                 modal.find('.modal-course-title').text(courseData.title || 'Course Details');
                 let description = courseData.description ? courseData.description.replace(/\n/g, '<br>') : 'No description available.';
                 modal.find('.modal-course-description').html(description);

                 let dateText = 'Date TBC';
                 if (courseData.startDate && courseData.endDate) {
                     try {
                         // Use UTC for parsing to avoid timezone issues if dates are just YYYY-MM-DD
                         const start = new Date(courseData.startDate + 'T00:00:00Z');
                         const end = new Date(courseData.endDate + 'T00:00:00Z');
                         // Display using a locale-friendly format (e.g., 'en-US')
                         const options = { year: 'numeric', month: 'long', day: 'numeric', timeZone: 'UTC' }; // Display as UTC date part
                         const dateFormatter = new Intl.DateTimeFormat('en-US', options);
                         if (!isNaN(start) && !isNaN(end)) {
                             dateText = `${dateFormatter.format(start)} - ${dateFormatter.format(end)}`;
                         } else { // Fallback if dates are invalid
                             dateText = `From ${courseData.startDate} to ${courseData.endDate}`;
                         }
                     } catch (e) { // Catch potential errors during date parsing/formatting
                          console.error("Error formatting date:", e);
                          dateText = `From ${courseData.startDate} to ${courseData.endDate}`;
                     }
                 }
                 modal.find('.modal-course-date').text(dateText);

                 let hoursText = 'Duration N/A';
                  if (courseData.hours && parseFloat(courseData.hours) > 0) {
                      hoursText = `${courseData.hours} Training Hours`;
                  }
                 modal.find('.modal-course-hours').text(hoursText);

                  const enrollLink = modal.find('#modalEnrollLink');
                  if (courseData.link && String(courseData.link).trim() !== '' && String(courseData.link).trim() !== '#') {
                      enrollLink.attr('href', courseData.link);
                      enrollLink.show();
                  } else {
                       enrollLink.hide(); // Hide button if no valid link
                  }
             }
            // Use event delegation for dynamically added items if needed, but direct binding is fine here.
            courseGrid.on('click', '.view-course-btn', function() {
                const courseElement = $(this).closest('.course-item');
                populateModal(courseElement);
            });

             // --- Contact Form Submission (AJAX Example - Keep if you add the form back) ---
             // $('#contactForm').on('submit', function(e) { ... });


            // Simple Hero Animation trigger
            setTimeout(function() {
                $('.hero-content').addClass('fade-in');
            }, 100);

            // Re-initialize/refresh AOS on resize (debounced)
            var resizeTimer;
            $(window).on('resize', function() {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(function() {
                    // Optional: Refresh AOS if layout changes affect animation triggers
                    // AOS.refresh();
                }, 250);
            });

        }); // End $(document).ready()
    </script>

</body>
</html>
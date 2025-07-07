<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>HRDSTDD</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .navbar {
            background: rgba(0, 0, 0, 0.6) !important;
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .navbar {
            background: linear-gradient(to bottom, rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0.3)) !important;
            backdrop-filter: blur(10px);
        }

        /* --- Existing Styles (unchanged) --- */
        .search-bar input { width: 300px; height: 30px; font-size: 14px; }
        .search-bar button { height: 30px; font-size: 14px; padding: 2px 10px; }
        .navbar { position: fixed; width: 100%; top: 0; z-index: 1000; }

        /* --- Modified Main Section --- */
        .main-section {
            background: url('./images/bg.jpg') no-repeat center center;
            background-size: cover;
            background-attachment: fixed;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-align: center;
            margin-top: 0px;
            overflow: hidden;
        }

        /* --- Carousel Styles --- */
        .carousel {
            width: 100%;
            height: 100%;
        }

        .carousel-inner {
            width: 100%;
            height: 100%;
        }

        .carousel-item {
            width: 100%;
            height: 100%;
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center; /* Vertically center */
            justify-content: center; /* Horizontally center */
            padding-top: 50px; /* Space for caption */
        }

        /* Set individual background images */
        .carousel-item:nth-child(1) { background-image: url('images/carousel1.jpg'); }
        .carousel-item:nth-child(2) { background-image: url('images/carousel2.jpg'); }
        .carousel-item:nth-child(3) { background-image: url('images/carousel3.jpg'); }

        /* Show captions, style them */
        .carousel-caption {
            display: block;
            color: white;
            text-align: center;
            position: absolute;  /* Back to absolute positioning */
            top: 50%;         /* Center vertically */
            left: 50%;        /* Center horizontally */
            transform: translate(-50%, -50%);  /* Offset to truly center */
            opacity: 0; /* Initially hide the caption */
            transition: opacity 0.5s ease-in-out; /* Fade-in transition */
        }

        /* Show the caption only when the item is active */
        .carousel-item.active .carousel-caption {
            opacity: 1; /* Fade in when active */
        }

        .carousel-caption h3 {
            font-size: 2.5em;
            font-weight: bold;
            margin-bottom: 0.5em;
        }

        .carousel-caption p {
            font-size: 1.2em;
            margin-bottom: 1em;
        }

        .carousel-indicators {
            position: absolute;
            bottom: 20px;
            left: 0;
            right: 0;
            width: auto;
            margin: 0;
            display: flex;
            justify-content: center;
        }

        .carousel-indicators li {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.5);
            border: none;
            margin: 0 5px;
            cursor: pointer;
        }

        .carousel-indicators .active {
            background-color: white;
        }

        /* --- Updated Arrow Styles --- */
        .carousel-control-prev,
        .carousel-control-next {
            width: 5%;
            opacity: 0.4;
            transition: opacity 0.3s ease-in-out;
        }

        .carousel-control-prev:hover,
        .carousel-control-next:hover {
            opacity: 0.8;
        }

        .carousel-control-prev-icon,
        .carousel-control-next-icon {
            background: none;
            font-size: 1.5em;
            color: rgba(255, 255, 255, 0.7);
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        .carousel-control-prev-icon::after {
            content: '←';
        }

        .carousel-control-next-icon::after {
            content: '→';
        }

        .carousel-item { transition: transform 0.5s ease-in-out; }
        .carousel-fade .carousel-item { opacity: 0; transition-duration: .6s; transition-property: opacity; }
        .carousel-fade .carousel-item.active { opacity: 1; }

        /* --- Section 1 Styles --- */
        .section-1 {
            padding: 50px 0;
            text-align: center;
            background-color: #f0f0f0;
        }

        /* Highlighted Home Link */
        .navbar-nav .nav-item .nav-link.active {
            color: #fff;
        }

       /* Navbar Brand Styling */
.navbar-brand {
    padding-left: 0px; /* Push logo to the left edge */
}

/* Navigation Menu Styling */
.collapse.navbar-collapse {
    justify-content: center !important; /* Center the nav items */
}

    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="images/logo.png" alt="Logo" width="40" height="40">
                TDDLMS
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link active" href="#">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="courses.php">Courses</a></li>
                    <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="contact.php">Contact Us</a></li>
                </ul>
            </div>
            <form class="form-inline d-none d-lg-block search-bar">
                <input class="form-control mr-sm-2" type="search" placeholder="Search">
                <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Search</button>
            </form>
        </div>
    </nav>

    <div class="main-section">
        <div id="carouselExampleIndicators" class="carousel slide carousel-fade" data-ride="carousel">
            <ol class="carousel-indicators">
                <li data-target="#carouselExampleIndicators" data-slide-to="0" class="active"></li>
                <li data-target="#carouselExampleIndicators" data-slide-to="1"></li>
                <li data-target="#carouselExampleIndicators" data-slide-to="2"></li>
            </ol>
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <div class="carousel-caption">
                        <h3>Welcome to TDDLMS!</h3>
                        <p>Your gateway to online learning. Discover a wide range of courses and skills development opportunities.</p>
                        <a href="courses.php" class="btn btn-primary">Explore Our Courses</a>
                    </div>
                </div>
                <div class="carousel-item">
                    <div class="carousel-caption">
                        <h3>Featured Course: Web Development Fundamentals</h3>
                        <p>Learn the basics of HTML, CSS, and JavaScript to build your own website.</p>
                        <a href="#" class="btn btn-primary">Learn More</a>
                    </div>
                </div>
                <div class="carousel-item">
                    <div class="carousel-caption">
                        <h3>Learn Anytime, Anywhere</h3>
                        <p>Access courses on any device. Interactive learning with quizzes and assignments. Connect with instructors and fellow learners.</p>
                        <a href="#" class="btn btn-primary">Get Started Today</a>
                    </div>
                </div>
            </div>
            <a class="carousel-control-prev" href="#carouselExampleIndicators" role="button" data-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="sr-only">Previous</span>
            </a>
            <a class="carousel-control-next" href="#carouselExampleIndicators" role="button" data-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="sr-only">Next</span>
            </a>
        </div>
    </div>

    <div class="section-1">
        <h2>Section 1</h2>
        <p>This is the content of Section 1.</p>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
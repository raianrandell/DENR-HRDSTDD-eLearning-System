<?php
session_start();
require '../includes/config.php';

// Check if the user is not logged in or the session has expired
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: adminlogin.php");
    exit;
}

// --- Data Fetching Functions ---
function getTotalParticipants($conn) {
    $sql = "SELECT COUNT(*) AS total_participants FROM user_participants";
    $result = $conn->query($sql);
    return ($result->num_rows > 0) ? $result->fetch_assoc()['total_participants'] : 0;
}

function getParticipantsWithTraining($conn) {
    $sql = "SELECT COUNT(*) AS with_training FROM user_participants WHERE in_training = 1";
    $result = $conn->query($sql);
    return ($result->num_rows > 0) ? $result->fetch_assoc()['with_training'] : 0;
}

function getTotalCourses($conn) {
    $sql = "SELECT COUNT(*) AS total_courses FROM training";
    $result = $conn->query($sql);
    return ($result->num_rows > 0) ? $result->fetch_assoc()['total_courses'] : 0;
}

function getTotalInstructors($conn) {
    $sql = "SELECT COUNT(*) AS total_instructors FROM user_instructors";
    $result = $conn->query($sql);
    return ($result->num_rows > 0) ? $result->fetch_assoc()['total_instructors'] : 0;
}

function getTopOfficesWithParticipants($conn, $filterOffices = []) {
    $sql = "SELECT office, COUNT(*) AS participant_count FROM user_participants WHERE in_training = 1";
    if (!empty($filterOffices)) {
        $sql .= " AND office IN ('" . implode("','", array_map('mysqli_real_escape_string', $conn, $filterOffices)) . "')";
    }
    $sql .= " GROUP BY office ORDER BY participant_count DESC LIMIT 5";
    $offices_result = $conn->query($sql);
    $top_offices = [];
    $office_counts = [];
    if ($offices_result->num_rows > 0) {
        while ($row = $offices_result->fetch_assoc()) {
            $top_offices[] = $row['office'];
            $office_counts[] = $row['participant_count'];
        }
    }
    return ['offices' => $top_offices, 'counts' => $office_counts];
}

function getMonthlyParticipantsWithTraining($conn, $year, $filterMonths = []) {
    $sql = "SELECT MONTH(created_at) AS month, COUNT(*) AS participant_count FROM user_participants WHERE YEAR(created_at) = ? AND in_training = 1";
    if (!empty($filterMonths)) {
        $sql .= " AND MONTH(created_at) IN (" . implode(",", array_map('intval', $filterMonths)) . ")";
    }
    $sql .= " GROUP BY MONTH(created_at) ORDER BY MONTH(created_at)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $year);
    $stmt->execute();
    $monthly_participants_result = $stmt->get_result();

    $monthly_counts = array_fill(0, 12, 0);
    if ($monthly_participants_result->num_rows > 0) {
        while ($row = $monthly_participants_result->fetch_assoc()) {
            $month_number = $row['month'] - 1;
            $monthly_counts[$month_number] = $row['participant_count'];
        }
    }
    $stmt->close();
    return $monthly_counts;
}

function getAllOffices($conn) {
    $sql = "SELECT DISTINCT office FROM user_participants ORDER BY office ASC";
    $result = $conn->query($sql);
    $offices = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $offices[] = $row['office'];
        }
    }
    return $offices;
}

function getAvailableYears($conn) {
    $sql = "SELECT DISTINCT YEAR(created_at) AS year FROM user_participants ORDER BY year DESC";
    $result = $conn->query($sql);
    $years = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $years[] = $row['year'];
        }
    }
    return $years;
}

function getAvailableMonths() {
    return [1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Aug', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec'];
}


// --- Fetch Data based on Filters ---
$total_participants = getTotalParticipants($conn);
$with_training = getParticipantsWithTraining($conn);
$without_training = $total_participants - $with_training;

// Calculate Percentage
$with_training_percentage = ($total_participants > 0) ? round(($with_training / $total_participants) * 100, 2) : 0;
$without_training_percentage = ($total_participants > 0) ? round(($without_training / $total_participants) * 100, 2) : 0;

$total_courses = getTotalCourses($conn);
$total_instructors = getTotalInstructors($conn);

// Bar Graph Data
$selectedOffices = isset($_GET['offices']) ? (is_array($_GET['offices']) ? $_GET['offices'] : [$_GET['offices']]) : [];
$topOfficeData = getTopOfficesWithParticipants($conn, $selectedOffices);
$top_offices = $topOfficeData['offices'];
$office_counts = $topOfficeData['counts'];
$allOffices = getAllOffices($conn);

// Line Graph Data
$selectedYear = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$selectedMonths = isset($_GET['months']) ? (is_array($_GET['months']) ? $_GET['months'] : [$_GET['months']]) : [];
$monthly_counts = getMonthlyParticipantsWithTraining($conn, $selectedYear, $selectedMonths);  // Corrected function name
$month_labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
$availableYears = getAvailableYears($conn);
$availableMonths = getAvailableMonths();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
     <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="adminlte/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="adminlte/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.1/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="adminlte/plugins/select2/css/select2.min.css"> <!-- Select2 CSS -->
    <link rel="stylesheet" href="adminlte/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css"> <!-- Select2 Bootstrap 4 Theme -->
    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- Chart.js -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- jQuery for AJAX -->


    <style>
        .warning-gradient {
            background: linear-gradient(225deg, #FCF596, #FFC107, #FF9B17);
        }
    </style>
</head>
<body class="hold-transition sidebar-mini">
<div class="preloader flex-column justify-content-center align-items-center">
    <img class="animation__shake rounded-circle" src="../../dist/img/denrlogo.jpg" alt="denrlogo" height="100" width="100">
</div>

    <div class="wrapper">
        <!-- Navbar -->
        <?php include 'navbar.php'; ?>

        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>

        <br>

        <!-- Content Wrapper -->
        <div class="content-wrapper">
            <section class="content">
    <div class="container-fluid">
    <div class="row">
        <div class="col-md-4">
    <div class="small-box warning-gradient">
        <div class="inner">
            <h3 id="totalParticipants"><?php echo $total_participants; ?></h3>
            <p>Total Number of Participants</p>
        </div>
        <div class="icon">
            <i class="fas fa-users"></i> <!-- Changed icon to match trainees -->
        </div>
        <a href="#" class="small-box-footer"> </a> <!-- Empty footer -->
    </div>
</div>

        <div class="col-md-4">
            <div class="small-box warning-gradient">
                <div class="inner">
                <h3 id="totalInstructors"><?php echo $total_instructors; ?></h3>
                    <p>Total Number of Subject Matter Expert</p>
                </div>
                <div class="icon">
                    <i class="fas fa-chalkboard-teacher"></i> <!-- Changed icon -->
                </div>
                <a href="#" class="small-box-footer"> </a> <!-- Empty footer -->
            </div>
        </div>

        <div class="col-md-4">
            <div class="small-box warning-gradient">
                <div class="inner">
                <h3 id="totalCourses"><?php echo $total_courses; ?></h3>
                    <p>Total Number of Trainings</p>
                </div>
                <div class="icon">
                    <i class="fas fa-book-open"></i>
                </div>
                <a href="#" class="small-box-footer"> </a> <!-- Empty footer -->
            </div>
        </div>
    </div>
     <!-- Graph Representation -->
     <div class="row">
     <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Participants Training Status (Pie Chart)</h3>
                                </div>
                                <div class="card-body">
                                    <canvas id="participantsChart"></canvas>
                                    <p><b>With Training:</b> <span id="withTrainingCount"><?php echo $with_training; ?></span></p>
                                    <p><b>Without Training:</b> <span id="withoutTrainingCount"><?php echo $without_training; ?></span></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Top Offices with Participants in Training (Bar Graph)</h3>
                                    <div class="card-tools">
                                        <form method="get">
                                            <div class="input-group input-group-sm" style="width: 300px;">
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <canvas id="officesChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Monthly Participants Enrolled (Line Graph) - <span id="selectedYearDisplay"><?php echo $selectedYear; ?></span></h3>
                                    <div class="card-tools">
                                        <form method="get">
                                            <div class="input-group input-group-sm" style="width: 300px;">
                                                <select class="form-control select2" name="year" style="width: 70px; margin-right: 5px;" onchange="this.form.submit()">
                                                    <?php
                                                        foreach ($availableYears as $year) {
                                                            $selected = ($year == $selectedYear) ? 'selected' : '';
                                                            echo "<option value=\"{$year}\" {$selected}>{$year}</option>";
                                                        }
                                                    ?>
                                                </select>
                                                <select class="form-control select2" name="months[]" multiple="multiple" data-placeholder="Filter Months">
                                                    <?php
                                                        foreach ($availableMonths as $month_number => $month_name) {
                                                            $selected = in_array($month_number, $selectedMonths) ? 'selected' : '';
                                                            echo "<option value=\"{$month_number}\" {$selected}>{$month_name}</option>";
                                                        }
                                                    ?>
                                                </select>
                                                <div class="input-group-append">
                                                    <button type="submit" class="btn btn-default">
                                                        <i class="fas fa-filter"></i> Filter
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <canvas id="monthlyParticipantsChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </section>
        </div>

        <!-- Footer -->
        <?php include '../footer.php'; ?>
    </div>

    <!-- AdminLTE JS -->
    <script src="adminlte/plugins/jquery/jquery.min.js"></script>
    <script src="adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="adminlte/dist/js/adminlte.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.1/dist/js/adminlte.min.js"></script>
    <script src="adminlte/plugins/select2/js/select2.full.min.js"></script> <!-- Select2 JS -->


    <!-- Chart.js for Graphs -->
    <script>
    $(function () {
        //Initialize Select2 Elements
        $('.select2').select2({
            theme: 'bootstrap4'
        });
    });

    // Pie Chart for Participants Training Status
    var ctxPie = document.getElementById('participantsChart').getContext('2d');
    var participantsChart = new Chart(ctxPie, {
        type: 'pie',
        data: {
            labels: ['With Training', 'Without Training'],
            datasets: [{
                data: [<?php echo $with_training; ?>, <?php echo $without_training; ?>],
                backgroundColor: ['#FFC107', '#FF9B17'], // Warning Gradient Colors for Pie Chart
                hoverOffset: 15
            }]
        },
        options: {
            responsive: true,
            animation: {
                animateScale: true,
                animateRotate: true,
                duration: 1200,
                easing: 'easeInOutBounce'
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(tooltipItem) {
                            let dataset = tooltipItem.dataset.data;
                            let total = dataset.reduce((sum, value) => sum + value, 0);
                            let value = dataset[tooltipItem.dataIndex];
                            let percentage = ((value / total) * 100).toFixed(2);
                            return tooltipItem.label + ': ' + value + ' (' + percentage + '%)';
                        }
                    }
                }
            },
            hover: {
                onHover: function (event, chartElement) {
                    if (chartElement.length) {
                        event.target.style.cursor = 'pointer';
                    } else {
                        event.target.style.cursor = 'default';
                    }
                }
            }
        }
    });

    // Bar Chart for Top Offices
    var ctxBar = document.getElementById('officesChart').getContext('2d');
    var officesChart = new Chart(ctxBar, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($top_offices); ?>,
            datasets: [{
                label: 'Number of Participants in Training',
                data: <?php echo json_encode($office_counts); ?>,
                backgroundColor: '#FFC107', // Single color from Warning Gradient for Bar Chart
                borderColor: 'rgba(60, 141, 188, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Number of Participants in Training'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Office'
                    }
                }
            }
        }
    });

    // Line Chart for Monthly Participants
    var ctxLine = document.getElementById('monthlyParticipantsChart').getContext('2d');
    var monthlyParticipantsChart = new Chart(ctxLine, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($month_labels); ?>,
            datasets: [{
                label: 'Participants Enrolled',
                data: <?php echo json_encode($monthly_counts); ?>,
                borderColor: '#FFC107', // Single color from Warning Gradient for Line Chart
                backgroundColor: '#FFC107', // Single color from Warning Gradient for Line Chart
                fill: false,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Number of Participants'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Month'
                    }
                }
            }
        }
    });

     // Function to update data via AJAX
     function updateData() {
        $.ajax({
            url: 'get_realtime_data.php', // Create a new PHP file to fetch data
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                // Update the HTML elements with the new data
                $('#totalParticipants').text(data.total_participants);
                $('#totalInstructors').text(data.total_instructors);
                $('#totalCourses').text(data.total_courses);
                $('#withTrainingCount').text(data.with_training);
                $('#withoutTrainingCount').text(data.total_participants - data.with_training);

                // Update chart data (you'll need to adjust this based on what you want to update)
                participantsChart.data.datasets[0].data = [data.with_training, data.total_participants - data.with_training];
                participantsChart.update();

                // Update Top Offices Chart
                officesChart.data.labels = data.top_offices.offices;
                officesChart.data.datasets[0].data = data.top_offices.counts;
                officesChart.update();

                monthlyParticipantsChart.data.datasets[0].data = data.monthly_counts;
                monthlyParticipantsChart.update();
            },
            error: function(xhr, status, error) {
                console.error("Error fetching data:", status, error);
            }
        });
    }

    // Call updateData() function every 5 seconds (adjust as needed)
    setInterval(updateData, 4000); // 4000 milliseconds = 4 seconds

    </script>

</body>
</html>
<?php $conn->close(); ?>
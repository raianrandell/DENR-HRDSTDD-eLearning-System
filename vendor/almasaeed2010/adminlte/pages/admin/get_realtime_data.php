<?php
// get_realtime_data.php

require '../includes/config.php'; // Ensure this path is correct

// Function to get monthly participants with training (same as in main file)
function getMonthlyParticipantsWithTraining($conn, $year, $filterMonths = []) {
    $sql = "SELECT MONTH(created_at) AS month, COUNT(*) AS participant_count FROM user_participants WHERE YEAR(created_at) = ? AND in_training = 1";
    if (!empty($filterMonths)) {
        $sql .= " AND MONTH(created_at) IN (" . implode(",", array_map('intval', $filterMonths)) . ")";
    }
    $sql .= " GROUP BY MONTH(created_at) ORDER BY MONTH(created_at)";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
      error_log("Prepare failed: " . $conn->error); // Log the error
      return array_fill(0, 12, 0); // Return default array on error
    }

    $stmt->bind_param("i", $year);
    if (!$stmt->execute()) {
      error_log("Execute failed: " . $stmt->error); // Log the error
      return array_fill(0, 12, 0); // Return default array on error
    }

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


// --- Fetch Data ---
try {
    $total_participants_sql = "SELECT COUNT(*) AS total_participants FROM user_participants";
    $total_participants_result = $conn->query($total_participants_sql);
    $total_participants = ($total_participants_result->num_rows > 0) ? $total_participants_result->fetch_assoc()['total_participants'] : 0;

    $with_training_sql = "SELECT COUNT(*) AS with_training FROM user_participants WHERE in_training = 1";
    $with_training_result = $conn->query($with_training_sql);
    $with_training = ($with_training_result->num_rows > 0) ? $with_training_result->fetch_assoc()['with_training'] : 0;

    $total_instructors_sql = "SELECT COUNT(*) AS total_instructors FROM user_instructors";
    $total_instructors_result = $conn->query($total_instructors_sql);
    $total_instructors = ($total_instructors_result->num_rows > 0) ? $total_instructors_result->fetch_assoc()['total_instructors'] : 0;

    $total_courses_sql = "SELECT COUNT(*) AS total_courses FROM training";
    $total_courses_result = $conn->query($total_courses_sql);
    $total_courses = ($total_courses_result->num_rows > 0) ? $total_courses_result->fetch_assoc()['total_courses'] : 0;

    // Top Offices data (simple example, adjust as needed)
    $top_offices_sql = "SELECT office, COUNT(*) AS participant_count FROM user_participants WHERE in_training = 1 GROUP BY office ORDER BY participant_count DESC LIMIT 5";
    $top_offices_result = $conn->query($top_offices_sql);

    $top_offices = ['offices' => [], 'counts' => []];
    if ($top_offices_result->num_rows > 0) {
        while ($row = $top_offices_result->fetch_assoc()) {
            $top_offices['offices'][] = $row['office'];
            $top_offices['counts'][] = $row['participant_count'];
        }
    }

    $current_year = date('Y');
    $monthly_counts = getMonthlyParticipantsWithTraining($conn, $current_year);


    $response = [
        'total_participants' => (int)$total_participants,
        'with_training' => (int)$with_training,
        'total_instructors' => (int)$total_instructors,
        'total_courses' => (int)$total_courses,
        'top_offices' => $top_offices,
        'monthly_counts' => $monthly_counts,
    ];

    header('Content-Type: application/json');
    echo json_encode($response);


} catch (Exception $e) {
    error_log("Exception in get_realtime_data.php: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Failed to fetch data.  Check server logs.']);
} finally {
  if (isset($conn)) {
    $conn->close();
  }
}
?>
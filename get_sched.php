<?php

session_start();
require_once 'db_config.php';

header('Content-Type: application/json');

// Check if React sent a specific date via the URL. If not, default to today.
$target_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Query to grab the shift details, the tutor's name, the course, and the current status
$query = "
    SELECT 
        s.shift_id as id, 
        t.first_name as name, 
        TIME_FORMAT(s.start_time, '%l:%i %p') as start, 
        TIME_FORMAT(s.end_time, '%l:%i %p') as end, 
        GROUP_CONCAT(DISTINCT c.course_code SEPARATOR ', ') as course, 
        st.status_state as section 
    FROM shift s
    JOIN tutors t ON s.student_id = t.student_id
    JOIN tutor_course tc ON t.student_id = tc.student_id 
    JOIN course c ON tc.course_code = c.course_code
    JOIN status st ON s.shift_id = st.shift_id
    WHERE st.`date` = ? AND s.day_of_week = DAYNAME(?)
    GROUP BY s.shift_id, t.first_name, s.start_time, s.end_time, st.status_state
";

$stmt = mysqli_prepare($db, $query);

// Passing the date twice to fill both question marks!
mysqli_stmt_bind_param($stmt, "ss", $target_date, $target_date);

$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, "ss", $target_date, $target_date);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$tutors_array = [];

while ($row = mysqli_fetch_assoc($result)) {
    // Convert the database status to match  React sections
    $row['section'] = strtolower($row['section']);

    // Default availability
    $row['availability'] = 'Open';

    $tutors_array[] = $row;
}

echo json_encode(["success" => true, "tutors" => $tutors_array]);

mysqli_stmt_close($stmt);
?>
<?php

session_start();
require_once 'db_config.php';

header('Content-Type: application/json');

// Check if React sent a specific date via the URL. If not, default to today.
$target_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Automatically updates any 'Upcoming' shifts to 'Late' if the current time is 15 mins past their start time
$auto_late_query = "
    UPDATE status st
    JOIN shift s ON st.shift_id = s.shift_id
    SET st.status_state = 'Late'
    WHERE st.status_state = 'Upcoming' 
      AND (st.`date` = CURRENT_DATE() AND CURRENT_TIME() > ADDTIME(s.start_time, '00:15:00')
      )
";

mysqli_query($db, $auto_late_query);

// Query to automatically mark shifts as 'Completed' if the current time is 1 min past their end time
$auto_complete_query = "
    UPDATE status st
    JOIN shift s ON st.shift_id = s.shift_id
    SET st.status_state = 'Completed'
    WHERE st.status_state IN ('Active', 'Late') 
      AND (
          st.`date` < CURRENT_DATE() OR 
          (st.`date` = CURRENT_DATE() AND CURRENT_TIME() >= s.end_time)
      )
";
mysqli_query($db, $auto_complete_query);

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
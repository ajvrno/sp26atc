<?php

session_start();
require_once 'db_config.php';

header('Content-Type: application/json');


// Query to grab the shift details, the tutor's name, the course, and the current status
$query = "
    SELECT 
        s.shift_id as id,
        t.first_name as name,
        TIME_FORMAT(s.start_time, '%l:%i %p') as start,
        TIME_FORMAT(s.end_time, '%l:%i %p') as end,
        s.day_of_week as day,
        GROUP_CONCAT(DISTINCT c.course_code SEPARATOR ', ') as course_code,
        GROUP_CONCAT(DISTINCT c.course_title SEPARATOR ', ') as course_title,
        c.course_subject as subject
    FROM shift s
    JOIN tutors t ON s.student_id = t.student_id
    JOIN tutor_course tc ON t.student_id = tc.student_id
    JOIN course c ON tc.course_code = c.course_code
    GROUP BY s.shift_id, t.first_name, s.start_time, s.end_time, s.day_of_week, c.course_subject, c.course_code
    ORDER BY c.course_subject, c.course_code, s.day_of_week, s.start_time
";

$result = mysqli_query($db, $query);

$shifts_array = [];

while ($row = mysqli_fetch_assoc($result)) {
    $shifts_array[] = $row;
}

echo json_encode(["success" => true, "shifts" => $shifts_array]);
?>
<!--Ashley Rabino - PHP script to generate daily status records -->

<?php
require_once 'db_config.php';

// Check if a date was passed in the URL, otherwise use today
$today_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Get the spelled-out day of the week for that specific date (e.g., "Monday")
$day_of_week = date('l', strtotime($today_date));

// query the 'shift' table to find all shifts scheduled for today
$find_shifts_query = "SELECT shift_id FROM shift WHERE day_of_week = ?";
$stmt = mysqli_prepare($db, $find_shifts_query);
mysqli_stmt_bind_param($stmt, "s", $day_of_week);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// store the shift ids in array for later use
$shifts_today = [];
while ($row = mysqli_fetch_assoc($result)) {
    $shifts_today[] = $row['shift_id'];
}
mysqli_stmt_close($stmt);

$inserted_count = 0;

// INSERT IGNORE prevents duplicates
$insert_status_query = "INSERT IGNORE INTO status (status_id, shift_id, date, status_state, admin_id) VALUES (?, ?, ?, 'Upcoming', NULL)";
$insert_stmt = mysqli_prepare($db, $insert_status_query);

foreach ($shifts_today as $shift_id) {
    $status_id = $shift_id . "_" . $today_date;

    mysqli_stmt_bind_param($insert_stmt, "sss", $status_id, $shift_id, $today_date);
    mysqli_stmt_execute($insert_stmt);

    if (mysqli_stmt_affected_rows($insert_stmt) > 0) {
        $inserted_count++;
    }
}
mysqli_stmt_close($insert_stmt);

echo "Morning setup complete. $inserted_count new shifts initialized to 'Upcoming' for $today_date.";
?>
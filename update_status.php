<?php
session_start();
require_once 'db_config.php';

header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
    exit;
}

// Block unauthorized users who bypass the login screen
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(["success" => false, "message" => "Unauthorized. Please log in."]);
    exit;
}

$admin_id = $_SESSION['admin_id'];

$json_payload = file_get_contents('php://input');
$data = json_decode($json_payload, true);

if (!isset($data['shift_id']) || !isset($data['new_status'])) {
    echo json_encode(["success" => false, "message" => "Missing required data."]);
    exit;
}

$shift_id = $data['shift_id'];
$new_status = $data['new_status'];

// Check if React sent a specific date (for the date arrows), otherwise default to today
$target_date = isset($data['date']) ? $data['date'] : date('Y-m-d');

// Update the 'status' table for this specific shift on the target date
$update_query = "UPDATE status SET status_state = ?, admin_id = ? WHERE shift_id = ? AND date = ?";
$stmt = mysqli_prepare($db, $update_query);

mysqli_stmt_bind_param($stmt, "ssss", $new_status, $admin_id, $shift_id, $target_date);

if (mysqli_stmt_execute($stmt)) {
    if (mysqli_stmt_affected_rows($stmt) > 0) {
        echo json_encode(["success" => true, "message" => "Status updated successfully!"]);
    } else {
        echo json_encode(["success" => false, "message" => "No changes made. Status might already be set or no schedule generated for this date."]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Database error: " . mysqli_error($db)]);
}

mysqli_stmt_close($stmt);
?>
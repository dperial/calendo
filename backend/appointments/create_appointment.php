<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");
ini_set('display_errors', 0); // Disable error display in production

include '../db_connect.php';

$data = json_decode(file_get_contents("php://input"), true);

// Debug: log the received data to a file
file_put_contents(__DIR__ . "/log.txt", json_encode($data) . PHP_EOL, FILE_APPEND);
// This will output to the server's error log
$title = $data['title'] ?? null;
$description = $data['description'] ?? null;
$category_id = $data['category_id'] ?? null;
$status = $data['status'] ?? 'scheduled';
$type = $data['type'] ?? 'private';
$start_date = $data['start_date'] ?? null;
$end_date = $data['end_date'] ?? null;
$start_time = $data['start_time'] ?? null;
$end_time = $data['end_time'] ?? null;
if (!$title || !$start_date || !$start_time) {
    echo json_encode(["success" => false, "error" => "Missing required fields."]);
    exit;
}
$user_id = 1; // $data['user_id']; Only for testing, replace with actual user ID from session or auth system

$sql = "INSERT INTO appointments (user_id, title, description, category_id, status, type, start_date, end_date, start_time, end_time)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ississssss", $user_id, $title, $description, $category_id, $status, $type, $start_date, $end_date, $start_time, $end_time);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true, 
        "message" => "Appointment created successfully."
    ]);
} else {
    echo json_encode([
        "success" => false,
        "error" => "Failed to create appointment.",
        "sql_error" => $stmt->error
    ]);
}
$stmt->close();
$conn->close();
exit;
?>
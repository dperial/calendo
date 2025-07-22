<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");

include './db_connect.php';

$data = json_decode(file_get_contents("php://input"), true);

$title = $data['title'];
$description = $data['description'];
$category_id = $data['category_id'];
$status = $data['status'];
$type = $data['type'];
$start_date = $data['start_date'];
$end_date = $data['end_date'];
$start_time = $data['start_time'];
$end_time = $data['end_time'];
$user_id = $data['user_id'];

$sql = "INSERT INTO appointments (user_id, title, description, category_id, status, type, start_date, end_date, start_time, end_time)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ississssss", $user_id, $title, $description, $category_id, $status, $type, $start_date, $end_date, $start_time, $end_time);

if ($stmt->execute()) {
    echo json_encode(["message" => "Appointment created successfully."]);
} else {
    echo json_encode(["error" => "Failed to create appointment."]);
}
?>

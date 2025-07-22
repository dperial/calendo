<?php
header("Content-Type: application/json");
include 'db_connect.php';

$data = json_decode(file_get_contents("php://input"), true);
$appointment_id = $data['appointment_id'];
$recurrence_pattern = $data['recurrence_pattern'];
$repeat_until = $data['repeat_until'];

$sql = "INSERT INTO recurring_appointments (appointment_id, recurrence_pattern, repeat_until) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iss", $appointment_id, $recurrence_pattern, $repeat_until);
$stmt->execute();

echo json_encode(["message" => "Recurring rule created"]);
?>

<?php
header("Content-Type: application/json");
// include '../db_connect.php';
require_once __DIR__ . '/../db.php';
$pdo = getPdo();

$data = json_decode(file_get_contents("php://input"), true);
$appointment_id     = $data['appointment_id'];
$recurrence_pattern = $data['recurrence_pattern'];
$repeat_until       = $data['repeat_until'];

$sql = "INSERT INTO recurring_appointments (appointment_id, recurrence_pattern, repeat_until) VALUES (?, ?, ?)";
$stmt = $pdo->prepare($sql);
$stmt->execute([$appointment_id, $recurrence_pattern, $repeat_until]);

echo json_encode(["message" => "Recurring rule created"]);
?>

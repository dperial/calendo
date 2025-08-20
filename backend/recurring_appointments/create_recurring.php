<?php
require dirname(__DIR__) . '/../vendor/autoload.php';
require_once dirname(__DIR__) . '/bootstrap.php';
use Project\Calendo\Database;

allowMethods('POST');

$pdo = Database::getPdo();

$data = json_decode(file_get_contents("php://input"), true);
$appointment_id     = $data['appointment_id'];
$recurrence_pattern = $data['recurrence_pattern'];
$repeat_until       = $data['repeat_until'];

$sql = "INSERT INTO recurring_appointments (appointment_id, recurrence_pattern, repeat_until) VALUES (?, ?, ?)";
$stmt = $pdo->prepare($sql);
$stmt->execute([$appointment_id, $recurrence_pattern, $repeat_until]);

echo json_encode(["message" => "Recurring rule created"]);
?>

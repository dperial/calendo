<?php
header("Content-Type: application/json");
// include '../db_connect.php';
require_once __DIR__ . '/../db.php';
$pdo = getPdo();

$data = json_decode(file_get_contents("php://input"), true);
$appointment_id      = $data['appointment_id'];
$shared_with_user_id = $data['shared_with_user_id'];

$sql = "INSERT INTO appointment_shares (appointment_id, shared_with_user_id) VALUES (?, ?)";
$stmt = $pdo->prepare($sql);
$stmt->execute([$appointment_id, $shared_with_user_id]);
if ($stmt->rowCount() > 0) {
    http_response_code(201); // Created
} else {
    http_response_code(500); // Internal Server Error
}
echo json_encode(["message" => "Share created"]);
?>

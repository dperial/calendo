<?php
header("Content-Type: application/json");
include './db_connect.php';

$data = json_decode(file_get_contents("php://input"), true);
$appointment_id = $data['appointment_id'];
$shared_with_user_id = $data['shared_with_user_id'];

$sql = "INSERT INTO appointment_shares (appointment_id, shared_with_user_id) VALUES (?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $appointment_id, $shared_with_user_id);
$stmt->execute();

echo json_encode(["message" => "Share created"]);
?>

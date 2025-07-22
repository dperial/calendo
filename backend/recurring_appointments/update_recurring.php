<?php
header("Content-Type: application/json");
include '../db_connect.php';

$id = $_GET['id'];
$data = json_decode(file_get_contents("php://input"), true);
$recurrence_pattern = $data['recurrence_pattern'];
$repeat_until = $data['repeat_until'];

$sql = "UPDATE recurring_appointments SET recurrence_pattern=?, repeat_until=? WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssi", $recurrence_pattern, $repeat_until, $id);
$stmt->execute();

echo json_encode(["message" => "Recurring rule updated"]);
?>

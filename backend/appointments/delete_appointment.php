<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: DELETE");

include '../db_connect.php';

$id = $_GET['id'];
$sql = "DELETE FROM appointments WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(["message" => "Appointment deleted."]);
} else {
    echo json_encode(["error" => "Delete failed."]);
}
?>

<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: PUT");

include '../db_connect.php';

$input = json_decode(file_get_contents("php://input"), true);
$id = $_GET['id'];

$sql = "UPDATE appointments SET title=?, description=?, category_id=?, status=?, type=?, start_date=?, end_date=?, start_time=?, end_time=? WHERE id=?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssissssssi", 
    $input['title'], $input['description'], $input['category_id'], $input['status'],
    $input['type'], $input['start_date'], $input['end_date'], $input['start_time'], $input['end_time'], $id
);

if ($stmt->execute()) {
    echo json_encode(["message" => "Appointment updated."]);
} else {
    echo json_encode(["error" => "Update failed."]);
}
?>

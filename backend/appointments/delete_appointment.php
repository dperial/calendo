<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: DELETE");

include '../db_connect.php';

$data = json_decode(file_get_contents("php://input"), true);
$id = $data['id'] ?? 0;
if (!$id) {
    echo json_encode([
        "success" => false,
        "error" => "Missing appointment ID."
    ]);
    exit;
}
$sql = "DELETE FROM appointments WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute() && $stmt->affected_rows) {
    echo json_encode([
        "success" => true, 
        "message" => "Appointment deleted successfully!"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "error" => "Delete failed.",
        "sql_error" => $stmt->error
    ]);
}
?>

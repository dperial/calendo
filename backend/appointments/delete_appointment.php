<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST, DELETE");

ini_set('display_errors', 1);       // ← keep during debugging
error_reporting(E_ALL);

include '../db_connect.php';

$data = json_decode(file_get_contents("php://input"), true);
$id   = $data['id'] ?? 0;

if (!$id) {
  echo json_encode(["success"=>false,"error"=>"Missing appointment ID"]); exit;
}

/* ---- start transaction so every step is atomic ---- */
$conn->begin_transaction();

try {
  /* 1. delete child tables that reference appointments.id */
  $conn->query("DELETE FROM appointment_shares    WHERE appointment_id = $id");
  $conn->query("DELETE FROM recurring_appointments WHERE appointment_id = $id");

  /* 2. delete the appointment itself */
  $stmt = $conn->prepare("DELETE FROM appointments WHERE id = ?");
  if (!$stmt) throw new Exception($conn->error);
  $stmt->bind_param("i", $id);
  if (!$stmt->execute()) throw new Exception($stmt->error);

  $affected = $stmt->affected_rows;
  $conn->commit();

  echo json_encode([
    "success" => $affected > 0,
    "message" => $affected ? "Appointment deleted successfully"
                           : "No appointment matched that ID"
  ]);
} catch (Exception $e) {
  $conn->rollback();
  echo json_encode([
    "success"   => false,
    "error"     => "Delete failed",
    "sql_error" => $e->getMessage()
  ]);
}
?>

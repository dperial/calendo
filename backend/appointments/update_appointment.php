<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: PUT");
ini_set('display_errors', 0); // Disable error display in production
include '../db_connect.php';
require_once '../helper.php'; // Include the helper functions

$data = json_decode(file_get_contents("php://input"), true);

/* build DateTime objects */
$tz      = new DateTimeZone(date_default_timezone_get());
$startDT = new DateTime($data['start_date'].' '.$data['start_time'], $tz);
$endDT   = new DateTime($data['end_date'].' '.$data['end_time'],   $tz);


$status  = $data['status'] ?? 'scheduled';

/* ---- validate ---- */
if ($err = validateStatusVsDates($status, $startDT, $endDT)) {
  echo json_encode(["success"=>false,"error"=>$err]); exit;
}

$id          = $data['id']          ?? null;
$title       = $data['title']       ?? null;
$description = $data['description'] ?? null;
$category_id = $data['category_id'] ?? null;
$type        = $data['type']        ?? 'private';
$start_date  = $data['start_date']  ?? null;
$end_date    = $data['end_date']    ?? null;
$start_time  = $data['start_time']  ?? null;
$end_time    = $data['end_time']    ?? null;
if (!$id || !$title || !$start_date || !$start_time) {
    echo json_encode(["success" => false, "error" => "Missing required fields."]);
    exit;
}

$sql = "UPDATE appointments SET title=?, description=?, category_id=?, status=?, type=?, start_date=?, end_date=?, start_time=?, end_time=? WHERE id=?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssissssssi", 
    $title, $description, $category_id, $status, $type,
    $start_date, $end_date, $start_time, $end_time, $id    
);

if ($stmt->execute() && $stmt->affected_rows) {
    echo json_encode([
        "success" => true, 
        "message" => "Appointment updated successfully."
    ]);
} else {
    echo json_encode([
  "success"   => false,
  "error"     => "Failed to update appointment.",
  "sql_error" => $stmt->error
]);
$stmt->close();
$conn->close();
exit;
}
?>

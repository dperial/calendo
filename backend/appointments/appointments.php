<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

include './db_connect.php';

$sql = "SELECT * FROM appointments ORDER BY start_date, start_time";
$result = $conn->query($sql);

$appointments = [];

while ($row = $result->fetch_assoc()) {
    $appointments[] = $row;
}

echo json_encode($appointments);
?>

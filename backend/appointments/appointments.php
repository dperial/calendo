<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

include '../db_connect.php';

$sql = "SELECT a.*, c.name AS category, c.icon_class, u.username AS creator_name FROM appointments a LEFT JOIN categories c ON a.category_id = c.id LEFT JOIN users u ON a.user_id = u.id ORDER BY a.start_date, a.start_time";
$result = $conn->query($sql);

$appointments = [];

while ($row = $result->fetch_assoc()) {
    $appointments[] = $row;
}

echo json_encode($appointments);
?>

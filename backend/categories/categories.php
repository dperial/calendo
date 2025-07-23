<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

include '../db_connect.php';

$sql = "SELECT c.* FROM categories c ORDER BY c.name";
$result = $conn->query($sql);

$categories = [];

while ($row = $result->fetch_assoc()) {
    $categories[] = $row;
}

echo json_encode($categories);
?>

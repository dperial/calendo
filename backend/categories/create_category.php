<?php
header("Content-Type: application/json");
include './db_connect.php';

$data = json_decode(file_get_contents("php://input"), true);
$name = $data['name'];
$color = $data['color'];

$sql = "INSERT INTO categories (name, color) VALUES (?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $name, $color);
$stmt->execute();

echo json_encode(["message" => "Category created"]);
?>

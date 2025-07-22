<?php
header("Content-Type: application/json");
include '../db_connect.php';

$id = $_GET['id'];
$data = json_decode(file_get_contents("php://input"), true);
$name = $data['name'];
$color = $data['color'];

$sql = "UPDATE categories SET name=?, color=? WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssi", $name, $color, $id);
$stmt->execute();

echo json_encode(["message" => "Category updated"]);
?>

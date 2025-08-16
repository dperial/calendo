<?php
header("Content-Type: application/json");
// include '../db_connect.php';
require_once __DIR__ . '/../db.php';
$pdo = getPdo();

$id = $_GET['id'];
$data = json_decode(file_get_contents("php://input"), true);
$name = $data['name'];
$color = $data['color'];

$sql = "UPDATE categories SET name=?, color=? WHERE id=?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$name, $color, $id]);

echo json_encode(["message" => "Category updated"]);
?>

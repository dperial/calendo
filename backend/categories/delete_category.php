<?php
header("Content-Type: application/json");
// include '../db_connect.php';
require_once __DIR__ . '/../db.php';
$pdo = getPdo();

$id = $_GET['id'];
$sql = "DELETE FROM categories WHERE id=?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);

echo json_encode(["message" => "Category deleted"]);
?>

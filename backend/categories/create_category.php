<?php
require_once __DIR__ . '/../bootstrap.php';
allowMethods('POST');

require_once __DIR__ . '/../db.php';
$pdo = getPdo();

$data = json_decode(file_get_contents("php://input"), true);
$name = $data['name'];
$color = $data['color'];

$sql = "INSERT INTO categories (name, color) VALUES (?, ?)";
$stmt = $pdo->prepare($sql);
$stmt->execute([$name, $color]);

echo json_encode(["message" => "Category created"]);
?>

<?php
require dirname(__DIR__) . '/../vendor/autoload.php';
require_once dirname(__DIR__) . '/../bootstrap.php';
use Project\Calendo\Database;

allowMethods('POST');

$pdo = Database::getPdo();

$data = json_decode(file_get_contents("php://input"), true);
$name = $data['name'];
$color = $data['color'];

$sql = "INSERT INTO categories (name, color) VALUES (?, ?)";
$stmt = $pdo->prepare($sql);
$stmt->execute([$name, $color]);

echo json_encode(["message" => "Category created"]);
?>

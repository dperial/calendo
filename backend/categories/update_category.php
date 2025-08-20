<?php
require dirname(__DIR__) . '/../vendor/autoload.php';
require_once dirname(__DIR__) . '/bootstrap.php';
use Project\Calendo\Database;

allowMethods('POST', 'PUT');

$pdo = Database::getPdo();

$id = $_GET['id'];
$data = json_decode(file_get_contents("php://input"), true);
$name = $data['name'];
$color = $data['color'];

$sql = "UPDATE categories SET name=?, color=? WHERE id=?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$name, $color, $id]);

echo json_encode(["message" => "Category updated"]);
?>

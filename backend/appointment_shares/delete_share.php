<?php
require dirname(__DIR__) . '/../vendor/autoload.php';
require_once dirname(__DIR__) . '/../bootstrap.php';
use Project\Calendo\Database;

allowMethods('POST', 'DELETE');

$pdo = Database::getPdo();

$id = $_GET['id'];
$sql = "DELETE FROM appointment_shares WHERE id=?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);

if ($stmt->rowCount() > 0) {
    echo json_encode(["message" => "Share deleted"]);
} else {
    http_response_code(404);
    echo json_encode(["message" => "Share not found"]);
}
?>

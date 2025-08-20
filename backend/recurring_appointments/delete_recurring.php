<?php

require dirname(__DIR__) . '/../vendor/autoload.php';
require_once dirname(__DIR__) . '/../bootstrap.php';
use Project\Calendo\Database;

allowMethods('POST', 'DELETE');

$pdo = Database::getPdo();

$id = $_GET['id'];
$sql = "DELETE FROM recurring_appointments WHERE id=?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);

echo json_encode(["message" => "Recurring rule deleted"]);
?>

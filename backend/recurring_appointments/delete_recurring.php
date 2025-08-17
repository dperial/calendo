<?php

require_once __DIR__ . '/../bootstrap.php';
allowMethods('POST', 'DELETE');

require_once __DIR__ . '/../db.php';
$pdo = getPdo();

$id = $_GET['id'];
$sql = "DELETE FROM recurring_appointments WHERE id=?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);

echo json_encode(["message" => "Recurring rule deleted"]);
?>

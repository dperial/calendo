<?php
require_once __DIR__ . '/../bootstrap.php';
allowMethods('GET');

require_once __DIR__ . '/../db.php';
$pdo = getPdo();

$sql = "SELECT c.* FROM categories c ORDER BY c.name";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($categories);
?>

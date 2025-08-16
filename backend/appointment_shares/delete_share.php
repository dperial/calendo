<?php
header("Content-Type: application/json");
// include '../db_connect.php';
require_once __DIR__ . '/../db.php';
$pdo = getPdo();

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

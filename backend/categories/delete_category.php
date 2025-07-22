<?php
header("Content-Type: application/json");
include './db_connect.php';

$id = $_GET['id'];
$sql = "DELETE FROM categories WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();

echo json_encode(["message" => "Category deleted"]);
?>

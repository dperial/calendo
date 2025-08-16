<?php
header("Content-Type: application/json");
// include '../db_connect.php';
require_once __DIR__ . '/../db.php';
$pdo = getPdo();

$data = json_decode(file_get_contents("php://input"), true);
$username   = $data['username'];
$email      = $data['email'];
$password   = password_hash($data['password'], PASSWORD_BCRYPT);
if (empty($username) || empty($email) || empty($data['password'])) {
    echo json_encode(["error" => "All fields are required"]);
    exit;
}
$sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
$stmt = $pdo->prepare($sql);
try {
    $stmt->execute([$username, $email, $password]);
    echo json_encode(["message" => "User registered successfully"]);
} catch (PDOException $e) {
    echo json_encode(["error" => "Email already registered"]);
    exit;
}
?>

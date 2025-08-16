<?php
session_start();
header("Content-Type: application/json");
// include '../db_connect.php';
require_once __DIR__ . '/../db.php';
$pdo = getPdo();

$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'];
$password = $data['password'];
if (empty($email) || empty($password)) {
    echo json_encode(["error" => "Email and password are required"]);
    exit;
}
$sql = "SELECT * FROM users WHERE email=?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user) {
    if (password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        echo json_encode(["message" => "Login successful", "user" => $user]);
    } else {
        echo json_encode(["error" => "Invalid password"]);
    }
} else {
    echo json_encode(["error" => "User not found"]);
}
?>

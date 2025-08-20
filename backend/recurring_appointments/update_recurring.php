<?php
require dirname(__DIR__) . '/../vendor/autoload.php';
require_once dirname(__DIR__) . '/bootstrap.php';
use Project\Calendo\Database;

allowMethods('POST', 'PUT');

$pdo = Database::getPdo();

$id = $_GET['id'];
$data               = json_decode(file_get_contents("php://input"), true);
$recurrence_pattern = $data['recurrence_pattern'];
$repeat_until       = $data['repeat_until'];

$sql = "UPDATE recurring_appointments SET recurrence_pattern=?, repeat_until=? WHERE id=?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$recurrence_pattern, $repeat_until, $id]);

echo json_encode(["message" => "Recurring rule updated"]);
?>

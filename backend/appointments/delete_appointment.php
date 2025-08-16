<?php
declare(strict_types=1);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if (($_SERVER['REQUEST_METHOD'] ?? 'POST') === 'OPTIONS') {
  http_response_code(204); exit; // CORS preflight
}

require_once __DIR__ . '/../db.php';
$pdo = getPdo();

try {
    $method = $_SERVER['REQUEST_METHOD'] ?? 'POST';
    if ($method !== 'POST' && $method !== 'DELETE') {
        http_response_code(405);
        echo json_encode(["success" => false, "error" => "Method not allowed"]);
        exit;
    }

    // Read JSON body (preferred); fall back to querystring for DELETE?id=123
    $raw  = file_get_contents("php://input");
    $data = [];
    if ($raw !== '' && $raw !== false) {
        try { $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR); }
        catch (JsonException $e) { /* ignore, maybe id is in query */ }
    }
    $id = (int)($data['id'] ?? ($_GET['id'] ?? 0));
    if ($id <= 0) {
        http_response_code(422);
        echo json_encode(["success" => false, "error" => "Missing appointment ID"]);
        exit;
    }

    // ---------- Transaction ----------
    $pdo->beginTransaction();

    // 1) Delete children first (if you don’t have ON DELETE CASCADE)
    $delShares = $pdo->prepare("DELETE FROM appointment_shares WHERE appointment_id = :id");
    $delShares->execute([':id' => $id]);

    $delRecur = $pdo->prepare("DELETE FROM recurring_appointments WHERE appointment_id = :id");
    $delRecur->execute([':id' => $id]);

    // 2) Delete the appointment
    $delAppt = $pdo->prepare("DELETE FROM appointments WHERE id = :id");
    $delAppt->execute([':id' => $id]);

    $affected = $delAppt->rowCount();
    $pdo->commit();

    echo json_encode([
        "success" => $affected > 0,
        "message" => $affected ? "Appointment deleted successfully" : "No appointment matched that ID"
    ]);
} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Database error", "detail" => $e->getMessage()]);
} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Server exception", "detail" => $e->getMessage()]);
}

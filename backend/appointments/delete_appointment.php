<?php
require dirname(__DIR__) . '/../vendor/autoload.php';
require_once dirname(__DIR__) . '/../bootstrap.php';
use Project\Calendo\Database;

allowMethods('POST', 'DELETE');

$pdo = Database::getPdo();

try {
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

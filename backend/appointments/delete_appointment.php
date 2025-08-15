<?php
declare(strict_types=1);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if (($_SERVER['REQUEST_METHOD'] ?? 'POST') === 'OPTIONS') {
  http_response_code(204); exit; // CORS preflight
}

error_reporting(E_ALL);
ini_set('log_errors', '1');
// ini_set('display_errors', '1'); // enable locally if needed

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

    // ---------- PDO connection (env-aware; CLI=tests use test DB, web uses prod) ----------
    $isCli = (PHP_SAPI === 'cli');
    $env   = $isCli ? (getenv('APP_ENV') ?: (defined('APP_ENV') ? APP_ENV : 'prod')) : 'prod';

    if ($env === 'test') {
        $dbHost = getenv('TEST_DB_HOST') ?: '127.0.0.1';
        $dbName = getenv('TEST_DB_NAME') ?: 'calendo_test';
        $dbUser = getenv('TEST_DB_USER') ?: 'root';
        $dbPass = getenv('TEST_DB_PASS') ?: '';
    } else {
        $dbHost = getenv('DB_HOST') ?: '127.0.0.1';
        $dbName = getenv('DB_NAME') ?: 'calendo_db';
        $dbUser = getenv('DB_USER') ?: 'root';
        $dbPass = getenv('DB_PASS') ?: '';
    }

    $dsn = "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4";
    $pdo = new PDO(
        $dsn, $dbUser, $dbPass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );

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
  
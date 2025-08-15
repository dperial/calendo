<?php
declare(strict_types=1);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
  http_response_code(204);
  exit; // CORS preflight
}

error_reporting(E_ALL);
ini_set('log_errors', '1');
// ini_set('display_errors', '1'); // enable locally if needed

require_once __DIR__ . '/../helper.php';
date_default_timezone_set('Europe/Berlin');

try {
  if (($_SERVER['REQUEST_METHOD'] ?? 'POST') !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "error" => "Method not allowed"]);
    exit;
  }

  $raw = file_get_contents("php://input");
  if ($raw === '' || $raw === false) {
    throw new RuntimeException("Empty request body");
  }

  $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);

  // Required
  $title      = trim((string)($data['title'] ?? ''));
  $start_date = (string)($data['start_date'] ?? '');
  $start_time = (string)($data['start_time'] ?? '');
  if ($title === '' || $start_date === '' || $start_time === '') {
    http_response_code(422);
    echo json_encode([
      "success" => false,
      "error"   => "Missing required fields: title, start_date, start_time"
    ]);
    exit;
  }

  // Optional / defaults
  $status      = (string)($data['status']      ?? 'scheduled');
  $type        = (string)($data['type']        ?? 'private');
    if (!isset($data['user_id'])) {
    http_response_code(422);
    echo json_encode(["success"=>false, "error"=>"Missing required field: user_id"]);
    exit;
    }
  $user_id = (int)$data['user_id'];  
  $description = array_key_exists('description', $data) ? (string)$data['description'] : null;
  $category_id = array_key_exists('category_id', $data) ? (int)$data['category_id']    : null;

  $end_date = (string)($data['end_date'] ?? $start_date);
  $end_time = (string)($data['end_time'] ?? $start_time);

  // Validate status vs dates
  $tz      = new DateTimeZone(date_default_timezone_get());
  $startDT = new DateTime("$start_date $start_time", $tz);
  $endDT   = new DateTime("$end_date $end_time",   $tz);

  if ($err = validateStatusVsDates($status, $startDT, $endDT)) {
    http_response_code(422);
    echo json_encode(["success"=>false,"error"=>$err]);
    exit;
  }

  // -------- PDO connection (env-aware) --------
  $env = getenv('APP_ENV') ?: (defined('APP_ENV') ? APP_ENV : 'prod');
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
  $pdo = new PDO($dsn, $dbUser, $dbPass, [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
  ]);

  // -------- Insert --------
  $sql = <<<SQL
    INSERT INTO appointments
      (user_id, title, description, category_id, status, type, start_date, end_date, start_time, end_time)
    VALUES
      (:user_id, :title, :description, :category_id, :status, :type, :start_date, :end_date, :start_time, :end_time)
  SQL;

  $stmt = $pdo->prepare($sql);
  $stmt->execute([
    ':user_id'     => $user_id,
    ':title'       => $title,
    ':description' => $description,
    ':category_id' => $category_id,
    ':status'      => $status,
    ':type'        => $type,
    ':start_date'  => $start_date,
    ':end_date'    => $end_date,
    ':start_time'  => $start_time,
    ':end_time'    => $end_time,
  ]);

  $id = (int)$pdo->lastInsertId();

  echo json_encode([
    "success" => true,
    "message" => "Appointment created successfully.",
    "id"      => $id
  ]);
  exit;

} catch (JsonException $e) {
  http_response_code(400);
  echo json_encode(["success"=>false, "error"=>"Invalid JSON", "detail"=>$e->getMessage()]);
  exit;
} catch (PDOException $e) {
  http_response_code(500);
  echo json_encode(["success"=>false, "error"=>"Database error", "detail"=>$e->getMessage()]);
  exit;
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(["success"=>false, "error"=>"Server exception", "detail"=>$e->getMessage()]);
  exit;
}

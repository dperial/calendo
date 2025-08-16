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

require_once __DIR__ . '/../helper.php';
require_once __DIR__ . '/../db.php';
session_start();
date_default_timezone_set('Europe/Berlin');

$pdo = getPdo();

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

  $user_id = $_SESSION['user_id'] ?? (int)($data['user_id'] ?? 2);
  if ($user_id === 2 && !isset($_SESSION['user_id']) && !isset($data['user_id'])) {
    error_log('create_appointment: user_id missing; defaulting to 2');
  }
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

<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: PUT, POST");

require_once __DIR__ . '/../helper.php';
date_default_timezone_set('Europe/Berlin');

try {
    $method = $_SERVER['REQUEST_METHOD'] ?? 'POST';
    if ($method !== 'PUT' && $method !== 'POST') {
        http_response_code(405);
        echo json_encode(["success" => false, "error" => "Method not allowed"]);
        exit;
    }

    $raw = file_get_contents("php://input");
    if ($raw === '' || $raw === false) {
        throw new RuntimeException("Empty request body");
    }

    $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);

    // ------- required fields -------
    $id         = isset($data['id']) ? (int)$data['id'] : 0;
    $title      = trim((string)($data['title']      ?? ''));
    $start_date =            (string)($data['start_date'] ?? '');
    $start_time =            (string)($data['start_time'] ?? '');

    if ($id <= 0 || $title === '' || $start_date === '' || $start_time === '') {
        http_response_code(422);
        echo json_encode([
            "success" => false,
            "error"   => "Missing required fields: id, title, start_date, start_time"
        ]);
        exit;
    }

    // ------- optional / defaults -------
    $description = array_key_exists('description', $data) ? (string)$data['description'] : null;
    $category_id = array_key_exists('category_id', $data) ? (int)$data['category_id']    : null;
    $status      = (string)($data['status'] ?? 'scheduled');
    $type        = (string)($data['type']   ?? 'private');
    $end_date    = (string)($data['end_date'] ?? $start_date);
    $end_time    = (string)($data['end_time'] ?? $start_time);

    // ------- validate status vs dates -------
    $tz      = new DateTimeZone(date_default_timezone_get());
    $startDT = new DateTime("$start_date $start_time", $tz);
    $endDT   = new DateTime("$end_date $end_time",   $tz);

    if ($err = validateStatusVsDates($status, $startDT, $endDT)) {
        http_response_code(422);
        echo json_encode(["success" => false, "error" => $err]);
        exit;
    }

    // ------- PDO connection (env-aware) -------
    // Better: Only honor APP_ENV when running under CLI (phpunit), never in Apache/Nginx
    $isCli = (PHP_SAPI === 'cli');
    $env   = $isCli ? (getenv('APP_ENV') ?: (defined('APP_ENV') ? APP_ENV : 'prod')) : 'prod';
    // Allow forcing the test database via query param or header (see db_connect.php)
    if (isset($_SERVER['HTTP_X_TEST_ENV']) || (($_GET['env'] ?? '') === 'test')) {
        $env = 'test';
    }
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
    $whichDb = $pdo->query('SELECT DATABASE()')->fetchColumn();
    error_log("UPDATE endpoint: env={$env} db={$whichDb} id={$id}");
    // First check if the record exists
    $exists = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE id=:id");
    $exists->execute([':id' => $id]);
    if ((int)$exists->fetchColumn() === 0) {
        http_response_code(404);
        echo json_encode([
            "success" => false, 
            "error" => "Appointment not found",
            "debug"   => ["env" => $env, "db" => $whichDb ?? null, "id" => $id]
        ]);
        exit;
    }

    // ------- UPDATE -------
    $sql = <<<SQL
        UPDATE appointments
           SET title       = :title,
               description = :description,
               category_id = :category_id,
               status      = :status,
               type        = :type,
               start_date  = :start_date,
               end_date    = :end_date,
               start_time  = :start_time,
               end_time    = :end_time
         WHERE id          = :id
    SQL;

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':title'       => $title,
        ':description' => $description,
        ':category_id' => $category_id,
        ':status'      => $status,
        ':type'        => $type,
        ':start_date'  => $start_date,
        ':end_date'    => $end_date,
        ':start_time'  => $start_time,
        ':end_time'    => $end_time,
        ':id'          => $id,
    ]);

    // rowCount() can be 0 if values are identical; treat as success with “no changes”
    if ($stmt->rowCount() > 0) {
        echo json_encode(["success" => true, "message" => "Appointment updated successfully."]);
    } else {
        echo json_encode(["success" => true, "message" => "No changes were applied."]);
    }
} catch (JsonException $e) {
    http_response_code(400);
    echo json_encode(["success"=>false, "error"=>"Invalid JSON", "detail"=>$e->getMessage()]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success"=>false, "error"=>"Database error", "detail"=>$e->getMessage()]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(["success"=>false, "error"=>"Server exception", "detail"=>$e->getMessage()]);
}

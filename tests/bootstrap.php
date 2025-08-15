<?php
date_default_timezone_set('Europe/Berlin');

// Optional Composer autoload if you add it later
$autoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoload)) require $autoload;

// Load the base Feature test class
require_once __DIR__ . '/Feature/FeatureTestCase.php';
// Load the base HTTP functions
require_once __DIR__ . '/Feature/Http.php';
// tests/bootstrap.php
define('APP_ENV', 'test');

putenv('APP_ENV=test');
putenv('TEST_DB_NAME=calendo_test');
putenv('TEST_DB_USER=root');
putenv('TEST_DB_PASS=');
putenv('TEST_DB_HOST=127.0.0.1');
// Handy DB helper for integration tests (optional)
function test_pdo(): PDO {
  static $pdo;
  if ($pdo) return $pdo;
  $host = getenv('TEST_DB_HOST') ?: '127.0.0.1';
  $name = getenv('TEST_DB_NAME') ?: 'calendo_test';
  $user = getenv('TEST_DB_USER') ?: 'root';
  $pass = getenv('TEST_DB_PASS') ?: '';
  $dsn  = getenv('TEST_DSN')  ?: "mysql:host=$host;dbname=$name;charset=utf8mb4";
  $user = getenv('TEST_USER') ?: "$user";
  $pass = getenv('TEST_PASS') ?: "$pass";
  $pdo  = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
  return $pdo;
}

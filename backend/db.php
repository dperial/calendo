<?php
declare(strict_types=1);

/**
 * Returns a PDO connection using environment variables.
 * Honors the X-Test-Env header or ?env=test query parameter to switch
 * to the test database.
 */
function getPdo(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $useTest = false;

    $isCli = (PHP_SAPI === 'cli');
    $env   = $isCli ? (getenv('APP_ENV') ?: (defined('APP_ENV') ? APP_ENV : 'prod')) : 'prod';

    // Allow forcing the test database via query param or header (see db_connect.php)
    if (isset($_SERVER['HTTP_X_TEST_ENV']) || (($_GET['env'] ?? '') === 'test')) {
        $env = 'test';
        $useTest = true;
    }

    if ($useTest) {
        $host = getenv('TEST_DB_HOST') ?: '127.0.0.1';
        $name = getenv('TEST_DB_NAME') ?: 'calendo_test';
        $user = getenv('TEST_DB_USER') ?: 'root';
        $pass = getenv('TEST_DB_PASS') ?: '';
    } else {
        $host = getenv('DB_HOST') ?: '127.0.0.1';
        $name = getenv('DB_NAME') ?: 'calendo_db';
        $user = getenv('DB_USER') ?: 'root';
        $pass = getenv('DB_PASS') ?: '';
    }

    $dsn = "mysql:host={$host};dbname={$name};charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);

    return $pdo;
}
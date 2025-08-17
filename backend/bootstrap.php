<?php
declare(strict_types=1);

// Enable error logging
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// Basic CORS headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Headers: Content-Type');

/**
 * Allow only the specified HTTP methods.
 * Automatically handles OPTIONS preflight requests.
 */
function allowMethods(string ...$methods): void {
    $allowed = array_map('strtoupper', $methods);
    header('Access-Control-Allow-Methods: ' . implode(', ', $allowed) . ', OPTIONS');

    $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    if ($requestMethod === 'OPTIONS') {
        http_response_code(204);
        exit;
    }

    if (!in_array($requestMethod, $allowed, true)) {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
        exit;
    }
}
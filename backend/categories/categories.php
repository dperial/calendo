<?php
require dirname(__DIR__) . '/../vendor/autoload.php';
require_once dirname(__DIR__) . '/bootstrap.php';
use Project\Calendo\Database;

allowMethods('GET');

$pdo = Database::getPdo();

$userId = filter_input(INPUT_GET, 'user_id', FILTER_VALIDATE_INT);

try {
    if ($userId !== null && $userId !== false) {
        $stmt = $pdo->prepare('SELECT c.* FROM categories c WHERE c.user_id = :user_id ORDER BY c.name');
        $stmt->execute([':user_id' => $userId]);
    } else {
        $stmt = $pdo->query('SELECT c.* FROM categories c ORDER BY c.name');
    }

    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($categories);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'Database error',
        'detail'  => $e->getMessage(),
    ]);
}
?>

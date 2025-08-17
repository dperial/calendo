<?php
require_once __DIR__ . '/../bootstrap.php';
allowMethods('GET');

require_once __DIR__ . '/../helper.php';
require_once __DIR__ . '/../db.php';
$pdo = getPdo();

$sql = "
    SELECT
        a.*,
        c.name AS category,
        c.icon_class,
        u.username AS creator_name
    FROM appointments a
    LEFT JOIN categories c ON a.category_id = c.id
    LEFT JOIN users u      ON a.user_id     = u.id
    ORDER BY a.start_date, a.start_time
";

try {
    $appointments = [];
    $stmt = $pdo->query($sql);
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Optional: default icon if null
    foreach ($appointments as &$row) {
        if (empty($row['icon_class'])) {
            $row['icon_class'] = 'bi-tag';
        }
    }

    echo json_encode($appointments);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error"   => "Unhandled exception.",
        "detail"  => $e->getMessage()
    ]);
}

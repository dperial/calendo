<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

include '../db_connect.php';

// Always set charset where possible
if ($conn instanceof mysqli) {
    mysqli_set_charset($conn, 'utf8mb4');
}

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

    if ($conn instanceof PDO) {
        // PDO path
        $stmt = $conn->query($sql);
        if (!$stmt) {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "error"   => "Query failed.",
                "detail"  => implode(" | ", $conn->errorInfo())
            ]);
            exit;
        }
        $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } elseif ($conn instanceof mysqli) {
        // mysqli path (with error visibility)
        mysqli_report(MYSQLI_REPORT_OFF); // we’ll handle errors ourselves
        $result = $conn->query($sql);
        if ($result === false) {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "error"   => "Query failed.",
                "detail"  => $conn->error
            ]);
            exit;
        }
        while ($row = $result->fetch_assoc()) {
            $appointments[] = $row;
        }

    } else {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "error"   => "Unknown DB connection type."
        ]);
        exit;
    }

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

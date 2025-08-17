<?php
require_once __DIR__ . '/../bootstrap.php';
allowMethods('GET');

session_start();

if (isset($_SESSION['user_id'])) {
    echo json_encode(["authenticated" => true, "user_id" => $_SESSION['user_id']]);
} else {
    echo json_encode(["authenticated" => false]);
}
?>

<?php
require_once __DIR__ . '/../bootstrap.php';
allowMethods('POST');

session_start();
session_destroy();
echo json_encode(["message" => "Logged out"]);
?>

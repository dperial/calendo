<?php
require dirname(__DIR__) . '/../vendor/autoload.php';
require_once dirname(__DIR__) . '/bootstrap.php';

allowMethods('POST');

session_start();
session_destroy();
echo json_encode(["message" => "Logged out"]);
?>

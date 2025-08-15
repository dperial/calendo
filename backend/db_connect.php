<?php
$servername = "localhost";
$username = "root";
$password = "";
// $dbname = "calendo_db";

date_default_timezone_set('Europe/Berlin');
// Choose the right DB base on th env
$isTestRequest = (isset($_GET['env']) && $_GET['env'] === 'test') || isset($_SERVER['HTTP_X_TEST_ENV']);
$dbname = $isTestRequest ? 'calendo_test' : 'calendo_db';

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
?>
<?php
// adjust to your DB config
$host = "localhost";
$user = "root";
$pass = "";
$db = "inventoryy";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("DB_CONN_ERR: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");
?>
    
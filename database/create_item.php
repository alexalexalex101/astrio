<?php
require "db.php";
$name = $_POST['name'] ?? '';
$hierarchy_id = intval($_POST['hierarchy_id'] ?? 0);
$notes = $_POST['notes'] ?? '';
$expiry = $_POST['expiry'] ?? null;
$calories = !empty($_POST['calories']) ? intval($_POST['calories']) : null;
$rfid = $_POST['rfid'] ?? '';
$type = $_POST['type'] ?? 'food';

if (trim($name) === '' || $hierarchy_id === 0) {
    echo "ERR: missing fields"; exit;
}

$stmt = $conn->prepare("INSERT INTO items (hierarchy_id, name, notes, expiry_date, calories, rfid, type, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
$stmt->bind_param("isssiss", $hierarchy_id, $name, $notes, $expiry, $calories, $rfid, $type);
$ok = $stmt->execute();
echo $ok ? "OK" : "ERR: " . $conn->error;

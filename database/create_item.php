<?php
require "db.php";

$name = $_POST['name'] ?? '';
$hierarchy_id = intval($_POST['hierarchy_id'] ?? 0);
$notes = $_POST['location'] ?? '';
$expiry = $_POST['expiry_date'] ?? null;
$calories = !empty($_POST['calories']) ? intval($_POST['calories']) : null;
$rfid = $_POST['rfid'] ?? '';
$type = $_POST['type'] ?? 'food';
$remaining = isset($_POST['remaining_percent']) ? intval($_POST['remaining_percent']) : 100;

if (trim($name) === '' || $hierarchy_id === 0) {
    exit("ERR: missing fields");
}

$stmt = $conn->prepare("
    INSERT INTO items (hierarchy_id, name, location, expiry_date, calories, rfid, type, remaining_percent, created_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())

");
if (!$stmt) {
    exit("ERR: Prepare failed: " . $conn->error);
}

// bind_param cannot bind null directly, convert nulls to NULL strings
$expiry = $expiry ?: null;
$calories_param = $calories ?? null;
$remaining = max(0, min(100, intval($remaining)));

$stmt->bind_param(
    "isssissi",
    $hierarchy_id,
    $name,
    $notes,
    $expiry,
    $calories_param,
    $rfid,
    $type,
    $remaining
);

$ok = $stmt->execute();
echo $ok ? "OK" : "ERR: " . $stmt->error;
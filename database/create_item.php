<?php
require "db.php";
require_once "action_logger.php";

$name         = trim($_POST['name'] ?? '');
$hierarchy_id = intval($_POST['hierarchy_id'] ?? 0);
$notes        = trim($_POST['location'] ?? '');
$expiry       = $_POST['expiry_date'] ?? null;
$calories     = !empty($_POST['calories']) ? intval($_POST['calories']) : null;
$rfid         = trim($_POST['rfid'] ?? '');
$type         = $_POST['type'] ?? 'food';
$remaining    = isset($_POST['remaining_percent']) ? intval($_POST['remaining_percent']) : 100;

// Basic validation
if (trim($name) === '' || $hierarchy_id === 0) {
    log_action($conn, 'create_item', 'error', 'create item failed: missing name or hierarchy_id', 'create_item.php');
    exit("ERR: missing fields");
}

// Normalize remaining percent
$remaining = max(0, min(100, $remaining));

// ────────────────────────────────────────────────
// Prepare INSERT
// ────────────────────────────────────────────────

$stmt = $conn->prepare("
    INSERT INTO items 
    (hierarchy_id, name, location, expiry_date, calories, rfid, type, remaining_percent, created_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
");

if (!$stmt) {
    $err = $conn->error;
    log_action($conn, 'create_item', 'error', "create item failed: prepare statement error – $err", 'create_item.php');
    exit("ERR: Prepare failed: " . $err);
}

// bind_param cannot bind null directly → use variables
$expiry_param   = $expiry ?: null;
$calories_param = $calories ?: null;

$stmt->bind_param(
    "isssissi",
    $hierarchy_id,
    $name,
    $notes,
    $expiry_param,
    $calories_param,
    $rfid,
    $type,
    $remaining
);

$ok = $stmt->execute();

if ($ok) {
    // Success message - plain text
    $msg = "new item created: \"$name\" (type: $type) in hierarchy $hierarchy_id";
    if ($rfid !== '') {
        $msg .= ", RFID: $rfid";
    }
    log_action($conn, 'create_item', 'success', $msg, 'create_item.php');
} else {
    // Error message - plain text
    $err = $stmt->error;
    $msg = "failed to create item \"$name\" (type: $type) in hierarchy $hierarchy_id – $err";
    log_action($conn, 'create_item', 'error', $msg, 'create_item.php');
}

echo $ok ? "OK" : "ERR: " . $stmt->error;

$stmt->close();
$conn->close();
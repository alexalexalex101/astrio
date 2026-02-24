<?php
require 'db.php';
require_once 'action_logger.php';

if (!isset($_POST['ids']) || !isset($_POST['amount'])) {
    log_action($conn, 'take_items', 'error', ['reason' => 'missing_parameters'], 'take_items.php');
    exit('Missing parameters');
}

$ids = array_filter(array_map('intval', explode(',', $_POST['ids'])));
$amount = intval($_POST['amount']);
if ($amount < 0) $amount = 0;

if (!$ids) {
    log_action($conn, 'take_items', 'error', ['reason' => 'no_ids'], 'take_items.php');
    exit('No ids');
}

// Build placeholders
$placeholders = implode(',', array_fill(0, count($ids), '?'));
// Prepare statement: decrease remaining_percent but not below 0
$sql = "UPDATE items SET remaining_percent = GREATEST(0, remaining_percent - ?) WHERE id IN ($placeholders)";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    log_action($conn, 'take_items', 'error', ['reason' => 'prepare_failed', 'db_error' => $conn->error], 'take_items.php');
    exit('Prepare failed: ' . $conn->error);
}

// Build types: one for amount + one per id
$types = str_repeat('i', count($ids) + 1);
$params = array_merge([$amount], $ids);

// bind_param requires references
$bind_names[] = $types;
for ($i = 0; $i < count($params); $i++) {
    $bind_name = 'bind' . $i;
    $$bind_name = $params[$i];
    $bind_names[] = &$$bind_name;
}

call_user_func_array([$stmt, 'bind_param'], $bind_names);

$ok = $stmt->execute();

if ($ok) {
    log_action($conn, 'take_items', 'success', ['ids' => $ids, 'amount' => $amount], 'take_items.php');
} else {
    log_action($conn, 'take_items', 'error', ['ids' => $ids, 'amount' => $amount, 'db_error' => $stmt->error], 'take_items.php');
}

echo $ok ? 'OK' : ('ERR: ' . $stmt->error);

$stmt->close();
$conn->close();

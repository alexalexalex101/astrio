<?php
require 'db.php';

if (!isset($_POST['ids']) || !isset($_POST['amount'])) {
    exit('Missing parameters');
}

$ids = array_filter(array_map('intval', explode(',', $_POST['ids'])));
$amount = intval($_POST['amount']);
if ($amount < 0) $amount = 0;

if (!$ids) exit('No ids');

// Build placeholders
$placeholders = implode(',', array_fill(0, count($ids), '?'));
// Prepare statement: decrease remaining_percent but not below 0
$sql = "UPDATE items SET remaining_percent = GREATEST(0, remaining_percent - ?) WHERE id IN ($placeholders)";
$stmt = $conn->prepare($sql);
if (!$stmt) {
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

echo $ok ? 'OK' : ('ERR: ' . $stmt->error);

$stmt->close();
$conn->close();

?>
<?php
require 'db.php';
require_once 'action_logger.php';

if (!isset($_POST['ids']) || !isset($_POST['amount'])) {
    log_action($conn, 'take_items', 'error', 'missing ids or amount parameter', 'take_items.php');
    exit('Missing parameters');
}

$raw_ids = explode(',', $_POST['ids']);
$ids = array_filter(array_map('intval', $raw_ids));
$amount = intval($_POST['amount']);
if ($amount < 0) $amount = 0;

if (!$ids) {
    log_action($conn, 'take_items', 'error', 'no valid numeric item IDs received', 'take_items.php');
    exit('No ids');
}

// ────────────────────────────────────────────────
// Prepare & execute UPDATE
// ────────────────────────────────────────────────

$placeholders = implode(',', array_fill(0, count($ids), '?'));
$sql = "UPDATE items SET remaining_percent = GREATEST(0, remaining_percent - ?) WHERE id IN ($placeholders)";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    log_action($conn, 'take_items', 'error', 'SQL prepare failed: ' . $conn->error, 'take_items.php');
    exit('Prepare failed: ' . $conn->error);
}

$types = str_repeat('i', count($ids) + 1);
$params = array_merge([$amount], $ids);

$bind_names = [$types];
for ($i = 0; $i < count($params); $i++) {
    $bind_name = 'bind' . $i;
    $$bind_name = $params[$i];
    $bind_names[] = &$$bind_name;
}

call_user_func_array([$stmt, 'bind_param'], $bind_names);

$ok = $stmt->execute();

// ────────────────────────────────────────────────
// Build readable log message
// ────────────────────────────────────────────────

$count = count($ids);
$item_word = $count === 1 ? 'item' : 'items';

if ($ok) {
    // Fetch names only on success (most common case)
    $id_list = implode(',', $ids);
    $name_stmt = $conn->prepare("SELECT id, name FROM items WHERE id IN ($id_list)");
    $name_stmt->execute();
    $result = $name_stmt->get_result();

    $items_display = [];
    $name_map = [];
    while ($row = $result->fetch_assoc()) {
        $name_map[$row['id']] = $row['name'] ?? 'Unnamed item #' . $row['id'];
    }
    $name_stmt->close();

    foreach ($ids as $id) {
        $name = $name_map[$id] ?? 'Unknown item #' . $id;
        $items_display[] = "\"$name\" (ID $id)";
    }

    $items_str = implode(', ', $items_display);

    $msg = "took {$amount}% from {$count} {$item_word}: $items_str";
    log_action($conn, 'take_items', 'success', $msg, 'take_items.php');
} else {
    // On error → fall back to IDs only (names would require extra query anyway)
    $ids_str = implode(', ', $ids);
    $msg = "failed to take {$amount}% from {$count} {$item_word}: $ids_str – {$stmt->error}";
    log_action($conn, 'take_items', 'error', $msg, 'take_items.php');
}

echo $ok ? 'OK' : ('ERR: ' . $stmt->error);

$stmt->close();
$conn->close();
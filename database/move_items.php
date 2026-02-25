<?php
require 'db.php';
require_once 'action_logger.php';

if (!isset($_POST['item_ids']) || !isset($_POST['target_hierarchy_id'])) {
    log_action($conn, 'move_items', 'error', 'missing item_ids or target_hierarchy_id parameter', 'move_items.php');
    exit('Missing parameters');
}

$raw_ids = explode(',', $_POST['item_ids']);
$ids = array_filter(array_map('intval', $raw_ids));
$target_id = (int)$_POST['target_hierarchy_id'];

if (empty($ids)) {
    log_action($conn, 'move_items', 'error', 'no valid numeric item IDs received', 'move_items.php');
    exit('No valid item IDs');
}

if ($target_id <= 0) {
    log_action($conn, 'move_items', 'error', 'invalid target hierarchy ID: ' . $target_id, 'move_items.php');
    exit('Invalid target location');
}
$current_locations = [];
if (!empty($ids)) {
     $ph = implode(',', array_fill(0, count($ids), '?'));
     $chk = $conn->prepare("SELECT DISTINCT hierarchy_id FROM items WHERE id IN ($ph)");
     $chk->bind_param(str_repeat('i', count($ids)), ...$ids);
     $chk->execute();
     $res = $chk->get_result();
     while ($row = $res->fetch_assoc()) {
         $current_locations[] = $row['hierarchy_id'];
     }
     $chk->close();
 }
 if (in_array($target_id, $current_locations) && count(array_unique($current_locations)) === 1) {
     log_action($conn, 'move_items', 'warning', 'attempted to move items to their current location: ' . $target_id, 'move_items.php');
     exit('Items are already in the target location');
 }

$placeholders = implode(',', array_fill(0, count($ids), '?'));
$sql = "UPDATE items SET hierarchy_id = ? WHERE id IN ($placeholders)";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    log_action($conn, 'move_items', 'error', 'SQL prepare failed: ' . $conn->error, 'move_items.php');
    exit('Prepare failed: ' . $conn->error);
}

// Build parameters: target first, then item IDs
$params = array_merge([$target_id], $ids);
$types  = 'i' . str_repeat('i', count($ids));

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
    // Fetch names + current location (optional but very helpful in logs)
    $id_list = implode(',', $ids);
    $name_stmt = $conn->prepare("
        SELECT i.id, i.name, h.name AS location_name 
        FROM items i 
        LEFT JOIN hierarchy h ON i.hierarchy_id = h.id 
        WHERE i.id IN ($id_list)
    ");
    $name_stmt->execute();
    $result = $name_stmt->get_result();

    $items_display = [];
    $name_map = [];
    while ($row = $result->fetch_assoc()) {
        $loc = $row['location_name'] ? $row['location_name'] : 'ID '.$row['hierarchy_id'];
        $name_map[$row['id']] = [
            'name' => $row['name'] ?? 'Unnamed item #' . $row['id'],
            'from' => $loc
        ];
    }
    $name_stmt->close();

    foreach ($ids as $id) {
        $info = $name_map[$id] ?? ['name' => 'Unknown item #' . $id, 'from' => 'unknown'];
        $items_display[] = "\"{$info['name']}\" (ID $id) from {$info['from']}";
    }

    // Optional: get target location name
    $target_name = 'unknown location';
    $tgt_stmt = $conn->prepare("SELECT name FROM hierarchy WHERE id = ?");
    $tgt_stmt->bind_param('i', $target_id);
    $tgt_stmt->execute();
    $tgt_res = $tgt_stmt->get_result();
    if ($tgt_row = $tgt_res->fetch_assoc()) {
        $target_name = $tgt_row['name'] ?: "ID $target_id";
    }
    $tgt_stmt->close();

    $items_str = implode(', ', $items_display);

    $msg = "moved {$count} {$item_word} to \"{$target_name}\": $items_str";
    log_action($conn, 'move_items', 'success', $msg, 'move_items.php');
} else {
    // Error – log with IDs only
    $ids_str = implode(', ', $ids);
    $msg = "failed to move {$count} {$item_word} (IDs: $ids_str) to target $target_id – {$stmt->error}";
    log_action($conn, 'move_items', 'error', $msg, 'move_items.php');
}

echo $ok ? 'OK' : ('ERR: ' . $stmt->error);

$stmt->close();
$conn->close();
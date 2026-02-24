<?php
include 'db.php';
include_once 'action_logger.php';

if (isset($_POST['delete_id'])) {
    $id = (int)$_POST['delete_id'];
    $stmtDel = $conn->prepare("DELETE FROM incoming WHERE id = ?");
    $stmtDel->bind_param('i', $id);
    if ($stmtDel->execute()) {
        log_action($conn, 'add_incoming_item_to_node', 'success', ['mode' => 'delete_incoming', 'delete_id' => $id], 'add_incoming_item_to_node.php');
        echo "OK";
    } else {
        log_action($conn, 'add_incoming_item_to_node', 'error', ['mode' => 'delete_incoming', 'delete_id' => $id, 'db_error' => $stmtDel->error], 'add_incoming_item_to_node.php');
        echo "Error deleting";
    }
    exit;
}

if (!isset($_POST['ids'], $_POST['hierarchy_id'], $_POST['remaining'])) {
    log_action($conn, 'add_incoming_item_to_node', 'error', ['reason' => 'missing_parameters'], 'add_incoming_item_to_node.php');
    exit('Missing parameters');
}

$ids = array_filter(array_map('intval', explode(',', $_POST['ids'])));
$node_id = (int)$_POST['hierarchy_id'];
$remainingJson = $_POST['remaining'];
$remainingMap = json_decode($remainingJson, true);
if (!is_array($remainingMap)) $remainingMap = [];

if (!$ids) {
    log_action($conn, 'add_incoming_item_to_node', 'error', ['reason' => 'no_ids_selected'], 'add_incoming_item_to_node.php');
    exit('No items selected');
}

$conn->begin_transaction();

try {
    $stmtSelect = $conn->prepare("SELECT id, name, type, location, expiry_date, calories, rfid FROM incoming WHERE id = ?");
    $stmtInsert = $conn->prepare("
        INSERT INTO items (name, type, location, expiry_date, calories, rfid, hierarchy_id, remaining_percent)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmtDelete = $conn->prepare("DELETE FROM incoming WHERE id = ?");

    foreach ($ids as $id) {
        $stmtSelect->bind_param('i', $id);
        $stmtSelect->execute();
        $res = $stmtSelect->get_result();
        if (!($row = $res->fetch_assoc())) continue;

        $remaining = isset($remainingMap[$id]) ? (int)$remainingMap[$id] : 100;
        if ($remaining < 0) $remaining = 0;
        if ($remaining > 100) $remaining = 100;

        $stmtInsert->bind_param(
            'ssssssii',
            $row['name'],
            $row['type'],
            $row['location'],
            $row['expiry_date'],
            $row['calories'],
            $row['rfid'],
            $node_id,
            $remaining
        );
        $stmtInsert->execute();

        $stmtDelete->bind_param('i', $id);
        $stmtDelete->execute();
    }

    $conn->commit();
    log_action($conn, 'add_incoming_item_to_node', 'success', ['ids' => $ids, 'hierarchy_id' => $node_id], 'add_incoming_item_to_node.php');
    echo "OK";
} catch (Exception $e) {
    $conn->rollback();
    log_action($conn, 'add_incoming_item_to_node', 'error', ['ids' => $ids, 'hierarchy_id' => $node_id, 'error' => $e->getMessage()], 'add_incoming_item_to_node.php');
    echo "Error: " . $e->getMessage();
}

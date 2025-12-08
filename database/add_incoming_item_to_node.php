<?php
include 'db.php'; // your DB connection

if (!isset($_POST['ids'], $_POST['hierarchy_id'])) {
    exit('Missing parameters');
}

$ids = explode(',', $_POST['ids']);
$node_id = intval($_POST['hierarchy_id']);

if (!$ids) exit('No items selected');

$conn->begin_transaction();

try {
    // Move each incoming item to items table
    $stmtInsert = $conn->prepare("INSERT INTO items (name, type, location, expiry_date, calories, rfid, hierarchy_id) 
                                  SELECT name, type, location, expiry_date, calories, rfid, ? 
                                  FROM incoming WHERE id = ?");
    $stmtDelete = $conn->prepare("DELETE FROM incoming WHERE id = ?");

    foreach ($ids as $id) {
        $id = intval($id);
        $stmtInsert->bind_param('ii', $node_id, $id);
        $stmtInsert->execute();

        $stmtDelete->bind_param('i', $id);
        $stmtDelete->execute();
    }

    $conn->commit();
    echo "OK";

} catch (Exception $e) {
    $conn->rollback();
    echo "Error: " . $e->getMessage();
}

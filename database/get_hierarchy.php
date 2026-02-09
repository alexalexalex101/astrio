<?php
require "db.php";

header('Content-Type: application/json');

// 1. Load all nodes
$res = $conn->query("SELECT id, parent_id, name FROM hierarchy ORDER BY id");
$flat = [];
while ($r = $res->fetch_assoc()) {
    $r['children'] = [];
    $r['item_count'] = 0;
    $r['remaining'] = 100; // default until we calculate real values
    $flat[$r['id']] = $r;
}

// 2. Load item counts per node
$itemRes = $conn->query("SELECT hierarchy_id, COUNT(*) AS cnt FROM items GROUP BY hierarchy_id");
while ($row = $itemRes->fetch_assoc()) {
    $hid = $row['hierarchy_id'];
    if (isset($flat[$hid])) {
        $flat[$hid]['item_count'] = (int)$row['cnt'];
    }
}

// 3. Build tree
$tree = [];
foreach ($flat as $id => &$node) {
    if ($node['parent_id']) {
        $flat[$node['parent_id']]['children'][] = &$node;
    } else {
        $tree[] = &$node;
    }
}

echo json_encode($tree);
$conn->close();
exit;
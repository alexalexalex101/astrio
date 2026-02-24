<?php
require "db.php";

header('Content-Type: application/json');

// ------------------------------------------------------------
// 1. Load all hierarchy nodes
// ------------------------------------------------------------
$res = $conn->query("
    SELECT 
        id,
        parent_id,
        name,
        ctb_type,
        capacity_liters,
        used_liters,
        is_generated_package_node
    FROM hierarchy
");

$flat = [];
while ($r = $res->fetch_assoc()) {
    $r['children'] = [];
    $r['item_count'] = 0;
    $r['used_volume'] = 0;
    $r['remaining'] = 100;
    $flat[$r['id']] = $r;
}

// ------------------------------------------------------------
// 2. Load all items grouped by hierarchy_id
// ------------------------------------------------------------
$itemRes = $conn->query("
    SELECT 
        hierarchy_id,
        COALESCE(volume_liters, 0) AS volume_liters,
        COALESCE(remaining_percent, 100) AS remaining_percent
    FROM items
");

$itemsByNode = [];
while ($row = $itemRes->fetch_assoc()) {
    $hid = $row['hierarchy_id'];
    if (!isset($itemsByNode[$hid])) {
        $itemsByNode[$hid] = [];
    }
    $itemsByNode[$hid][] = $row;
}

// ------------------------------------------------------------
// 3. Build tree structure
// ------------------------------------------------------------
$tree = [];
foreach ($flat as $id => &$node) {
    if ($node['parent_id']) {
        $flat[$node['parent_id']]['children'][] = &$node;
    } else {
        $tree[] = &$node;
    }
}

// ------------------------------------------------------------
// 4. Compute usage for every node
// ------------------------------------------------------------
foreach ($flat as $id => &$node) {

    $capacity = (float)($node['capacity_liters'] ?? 0);
    $type     = $node['ctb_type'];
    $node['item_count'] = isset($itemsByNode[$id]) ? count($itemsByNode[$id]) : 0;

    // ------------------------------------------------------------
    // 1. STACK LOGIC (4 meters)
    // ------------------------------------------------------------
    if ($type === 'STACK') {

        $used = 0.0;
        foreach ($node['children'] as $child) {
            if (strpos($child['ctb_type'], 'CTB-') === 0) {
                $size = (float)str_replace('CTB-', '', $child['ctb_type']);
                $used += $size / 2.0;
            }
        }

        $node['used_volume'] = $used;
        $node['remaining']   = max(0, 100 - (($used / 4.0) * 100));
        continue;
    }

    // ------------------------------------------------------------
    // 2. INCOMING PACKAGE NODES (use stored used_liters)
    // ------------------------------------------------------------
    if (!empty($node['is_generated_package_node'])) {

        $used = isset($node['used_liters'])
            ? (float)$node['used_liters']
            : 0.0;

        // fallback: sum items if used_liters is 0
        if ($used == 0 && isset($itemsByNode[$id])) {
            foreach ($itemsByNode[$id] as $item) {
                $used += (float)$item['volume_liters'];
            }
        }

        $node['used_volume'] = round($used, 2);
        $node['remaining']   = $capacity > 0
            ? max(0, 100 - (($used / $capacity) * 100))
            : 100;

        continue;
    }

    // ------------------------------------------------------------
    // 3. CTB + MICROCONTAINER MERGED LOGIC
    // ------------------------------------------------------------
    $isCTB   = strpos($type, 'CTB') === 0;
    $isMicro = in_array($type, ['POUCH', 'STRIP', 'SLEEVE', 'CASE']);

    if ($capacity > 0 && ($isCTB || $isMicro)) {

        $used = 0.0;

        // 3A. Nested CTBs
        foreach ($node['children'] as $child) {
            if (strpos($child['ctb_type'], 'CTB-') === 0) {
                $used += (float)$child['capacity_liters'];
            }
        }

        // 3B. Microcontainers
        foreach ($node['children'] as $child) {
            if (in_array($child['ctb_type'], ['POUCH','STRIP','SLEEVE','CASE'])) {
                $used += (float)$child['capacity_liters'];
            }
        }

        // 3C. Items inside this node
        if (isset($itemsByNode[$id])) {
            foreach ($itemsByNode[$id] as $item) {
                $vol = (float)$item['volume_liters'];
                $rem = (float)$item['remaining_percent'] / 100.0;
                $used += ($vol * $rem);
            }
        }

        $node['used_volume'] = round($used, 2);
        $node['remaining']   = $capacity > 0
            ? max(0, 100 - (($used / $capacity) * 100))
            : 100;

        continue;
    }

    // ------------------------------------------------------------
    // 4. DEFAULT — no bar
    // ------------------------------------------------------------
    $node['remaining']   = null;
    $node['used_volume'] = 0;
}

echo json_encode($tree);
$conn->close();
exit;
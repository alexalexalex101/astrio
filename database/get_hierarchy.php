<?php
require "db.php";

header('Content-Type: application/json');

// 1. Load all nodes
$res = $conn->query("
    SELECT 
        id, 
        parent_id, 
        name,
        ctb_type,
        capacity_liters
    FROM hierarchy
    ORDER BY id
");

$flat = [];
while ($r = $res->fetch_assoc()) {
    $r['children'] = [];
    $r['item_count'] = 0;
    $r['used_volume'] = 0;
    $r['remaining'] = 100;
    $flat[$r['id']] = $r;
}

// 2. Load items
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

// 3. Build tree (children are full node objects)
$tree = [];
foreach ($flat as $id => &$node) {
    if ($node['parent_id']) {
        $flat[$node['parent_id']]['children'][] = &$node;
    } else {
        $tree[] = &$node;
    }
}

// 4. Compute used volume + remaining percent
foreach ($flat as $id => &$node) {

    $capacity = (float)($node['capacity_liters'] ?? 0);
    $node['item_count'] = isset($itemsByNode[$id]) ? count($itemsByNode[$id]) : 0;

    // STACKS (S1, S2, S3, C1, C2) – 4m long
    if ($node['ctb_type'] === 'STACK') {

        $usedMeters = 0.0;

        foreach ($node['children'] as $childNode) {
            if (strpos($childNode['ctb_type'], 'CTB-') === 0) {
                $size = (float)str_replace('CTB-', '', $childNode['ctb_type']);
                $usedMeters += $size / 2.0;
            }
        }

        $node['used_volume'] = $usedMeters;
        $node['remaining'] = max(0, 100 - (($usedMeters / 4.0) * 100));
        continue;
    }

    // CTB logic (nested CTBs by size, direct children only)
    if ($capacity > 0 && !empty($node['children']) && strpos($node['ctb_type'], 'CTB') === 0) {

        $used = 0.0;

        $childCTB = null;
        if ($node['ctb_type'] === 'CTB-4.0') $childCTB = 'CTB-2.0';
        if ($node['ctb_type'] === 'CTB-2.0') $childCTB = 'CTB-1.0';
        if ($node['ctb_type'] === 'CTB-1.0') $childCTB = 'CTB-0.5';

        foreach ($node['children'] as $childNode) {
            if ($childCTB && $childNode['ctb_type'] === $childCTB) {
                $used += (float)$childNode['capacity_liters'];
            }
        }

        $node['used_volume'] = round($used, 2);
        $remainingPercent = $capacity > 0 ? max(0, 100 - (($used / $capacity) * 100)) : 100;
        $node['remaining'] = round($remainingPercent, 2);
        continue;
    }

    // Strip / pouch / sleeve / case – item volume
    if ($capacity > 0 && $node['item_count'] > 0) {

        $used = 0.0;

        foreach ($itemsByNode[$id] as $item) {
            $effective = $item['volume_liters'] * ($item['remaining_percent'] / 100);
            $used += $effective;
        }

        $node['used_volume'] = round($used, 2);
        $remainingPercent = $capacity > 0 ? max(0, 100 - (($used / $capacity) * 100)) : 100;
        $node['remaining'] = round($remainingPercent, 2);
        continue;
    }

    $node['remaining'] = 100;
}

echo json_encode($tree);
$conn->close();
exit;
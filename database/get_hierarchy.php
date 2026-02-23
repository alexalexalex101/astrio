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

// 3. Build tree
$tree = [];
foreach ($flat as $id => &$node) {
    if ($node['parent_id']) {
        $flat[$node['parent_id']]['children'][] = &$node;
    } else {
        $tree[] = &$node;
    }
}

// 4. Compute usage
foreach ($flat as $id => &$node) {

    $capacity = (float)($node['capacity_liters'] ?? 0);
    $node['item_count'] = isset($itemsByNode[$id]) ? count($itemsByNode[$id]) : 0;

    // ------------------------------------------------------------
    // STACK LOGIC (4 meters)
    // ------------------------------------------------------------
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

    // ------------------------------------------------------------
    // FIXED CTB LOGIC: CTBs count nested CTBs + microcontainers + items
    // ------------------------------------------------------------
    if ($capacity > 0 && strpos($node['ctb_type'], 'CTB') === 0) {

        $used = 0.0;

        // 1. Count nested CTBs
        foreach ($node['children'] as $childNode) {
            if (strpos($childNode['ctb_type'], 'CTB-') === 0) {
                $used += floatval($childNode['capacity_liters']);
            }
        }

        // 2. Count microcontainers
        $microContainers = ['POUCH', 'STRIP', 'SLEEVE', 'CASE'];
        foreach ($node['children'] as $childNode) {
            if (in_array($childNode['ctb_type'], $microContainers)) {
                $used += floatval($childNode['capacity_liters']);
            }
        }

        // 3. Count items inside this CTB
        if (isset($itemsByNode[$id])) {
            foreach ($itemsByNode[$id] as $item) {
                $vol = floatval($item['volume_liters']);
                $remaining = floatval($item['remaining_percent']) / 100.0;
                $used += ($vol * $remaining);
            }
        }

        // 4. Final calculation
        $node['used_volume'] = round($used, 2);
        $remainingPercent = $capacity > 0 ? max(0, 100 - (($used / $capacity) * 100)) : 100;
        $node['remaining'] = round($remainingPercent, 2);
        continue;
    }

    // ------------------------------------------------------------
    // MICROCONTAINERS (POUCH, STRIP, SLEEVE, CASE)
    // ------------------------------------------------------------
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

    // Default
    // ------------------------------------------------------------
    // NEW RULE: Only show bars for CTBs or nodes with real items
    // ------------------------------------------------------------
    $isCTB = strpos($node['ctb_type'], 'CTB') === 0;
    $hasItems = ($node['item_count'] > 0);

    if ($isCTB || $hasItems) {
        // They already have correct remaining from earlier logic
        // or they will fall into microcontainer logic above
        continue;
    }

    // Everyone else: no bar
    $node['remaining'] = null;   // or 100 if you prefer, but null hides the bar
    $node['used_volume'] = 0;
}

echo json_encode($tree);
$conn->close();
exit;

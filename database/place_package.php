<?php
session_start();
require_once 'db.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'POST only']);
    exit;
}

$packageId    = (int)($_POST['package_instance_id'] ?? 0);
$parentNodeId = (int)($_POST['hierarchy_id'] ?? 0);

if ($packageId <= 0 || $parentNodeId <= 0) {
    echo json_encode(['error' => 'Missing package or node']);
    exit;
}

$conn->begin_transaction();

try {
    // ------------------------------------------------------------
    // 1. Load package info
    // ------------------------------------------------------------
    $pkg = $conn->prepare("
        SELECT ip.package_id, ip.package_name, fp.package_type
        FROM incoming_packages ip
        JOIN food_packages fp ON fp.id = ip.package_id
        WHERE ip.id = ?
    ");
    $pkg->bind_param("i", $packageId);
    $pkg->execute();
    $pkgInfo = $pkg->get_result()->fetch_assoc();

    if (!$pkgInfo) throw new Exception("Package not found.");

    $packageName = $pkgInfo['package_name'];
    $packageType = $pkgInfo['package_type'];

    // ------------------------------------------------------------
    // 2. Generate node name (#1, #2, #3…)
    // ------------------------------------------------------------
    $countStmt = $conn->prepare("
        SELECT COUNT(*) AS c
        FROM hierarchy
        WHERE is_generated_package_node = 1
          AND name LIKE CONCAT(?, '%')
    ");
    $countStmt->bind_param("s", $packageName);
    $countStmt->execute();
    $count = $countStmt->get_result()->fetch_assoc()['c'] + 1;

    $nodeName = $packageName . " #" . $count;

    // ------------------------------------------------------------
    // 3. Load items inside incoming package
    // ------------------------------------------------------------
    $items = $conn->prepare("
        SELECT name, type, expiry_date, calories, rfid, remaining_percent, volume_liters
        FROM incoming_items
        WHERE package_instance_id = ?
    ");
    $items->bind_param("i", $packageId);
    $items->execute();
    $itemsRes = $items->get_result();

    $totalVolume = 0;
    $itemsList = [];

    while ($it = $itemsRes->fetch_assoc()) {
        $totalVolume += (float)$it['volume_liters'];
        $itemsList[] = $it;
    }

    // ------------------------------------------------------------
    // 4. STEP 1 — LIVE PARENT CAPACITY CHECK
    // ------------------------------------------------------------
    $parent = $conn->prepare("
        SELECT capacity_liters
        FROM hierarchy
        WHERE id = ?
    ");
    $parent->bind_param("i", $parentNodeId);
    $parent->execute();
    $parentInfo = $parent->get_result()->fetch_assoc();

    if (!$parentInfo) throw new Exception("Parent container not found.");

    $parentCapacity = (float)$parentInfo['capacity_liters'];

    // Compute parent used volume (same logic as get_hierarchy.php)
    $parentUsed = 0.0;
    $micro = ['POUCH','STRIP','SLEEVE','CASE'];

    // 4A. Children CTBs + microcontainers
    $childStmt = $conn->prepare("
        SELECT id, ctb_type, capacity_liters
        FROM hierarchy
        WHERE parent_id = ?
    ");
    $childStmt->bind_param("i", $parentNodeId);
    $childStmt->execute();
    $childRes = $childStmt->get_result();

    while ($child = $childRes->fetch_assoc()) {
        $ctbType = $child['ctb_type'];

        if (strpos($ctbType, 'CTB-') === 0) {
            $parentUsed += (float)$child['capacity_liters'];
        }

        if (in_array($ctbType, $micro)) {
            $parentUsed += (float)$child['capacity_liters'];
        }
    }

    // 4B. Items inside parent
    $itemStmt = $conn->prepare("
        SELECT volume_liters, remaining_percent
        FROM items
        WHERE hierarchy_id = ?
    ");
    $itemStmt->bind_param("i", $parentNodeId);
    $itemStmt->execute();
    $itemRes = $itemStmt->get_result();

    while ($it = $itemRes->fetch_assoc()) {
        $vol = (float)$it['volume_liters'];
        $rem = (float)$it['remaining_percent'] / 100.0;
        $parentUsed += ($vol * $rem);
    }

    $parentRemaining = $parentCapacity - $parentUsed;

    if ($totalVolume > $parentRemaining) {
        throw new Exception("Not enough space in the parent container.");
    }

    // ------------------------------------------------------------
    // 5. Create new node
    // ------------------------------------------------------------
    $insNode = $conn->prepare("
        INSERT INTO hierarchy (parent_id, name, type, ctb_type, is_generated_package_node)
        VALUES (?, ?, 'container', ?, 1)
    ");
    $insNode->bind_param("iss", $parentNodeId, $nodeName, $packageType);
    $insNode->execute();
    $newNodeId = $insNode->insert_id;

    // ------------------------------------------------------------
    // 6. Load CTB capacity
    // ------------------------------------------------------------
    $capStmt = $conn->prepare("
        SELECT capacity_liters
        FROM ctb_specifications
        WHERE ctb_type = ?
    ");
    $capStmt->bind_param("s", $packageType);
    $capStmt->execute();
    $capacity = $capStmt->get_result()->fetch_assoc()['capacity_liters'] ?? 0;

    // ------------------------------------------------------------
    // 7. Insert items into new node
    // ------------------------------------------------------------
    $insItem = $conn->prepare("
        INSERT INTO items
            (hierarchy_id, name, type, expiry_date, calories, rfid, remaining_percent, volume_liters, location)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, '')
    ");

    foreach ($itemsList as $it) {
        $expiry = $it['expiry_date'] ?: null;
        $cal    = $it['calories'] !== null ? (int)$it['calories'] : null;
        $rem    = $it['remaining_percent'] !== null ? (int)$it['remaining_percent'] : 100;
        $vol    = $it['volume_liters'] !== null ? (float)$it['volume_liters'] : 0;

        $insItem->bind_param(
            "isssisis",
            $newNodeId,
            $it['name'],
            $it['type'],
            $expiry,
            $cal,
            $it['rfid'],
            $rem,
            $vol
        );
        $insItem->execute();
    }

    // ------------------------------------------------------------
    // 8. Compute new node used_liters (correct formula)
    // ------------------------------------------------------------
    $usedStmt = $conn->prepare("
        SELECT SUM(volume_liters * (remaining_percent / 100)) AS used
        FROM items
        WHERE hierarchy_id = ?
    ");
    $usedStmt->bind_param("i", $newNodeId);
    $usedStmt->execute();
    $used = $usedStmt->get_result()->fetch_assoc()['used'] ?? 0;

    $updateNode = $conn->prepare("
        UPDATE hierarchy
        SET capacity_liters = ?, used_liters = ?
        WHERE id = ?
    ");
    $updateNode->bind_param("ddi", $capacity, $used, $newNodeId);
    $updateNode->execute();

    // ------------------------------------------------------------
    // 9. STEP 2 — Update parent used_liters
    // ------------------------------------------------------------
    $newParentUsed = $parentUsed + $used;

    $updateParent = $conn->prepare("
        UPDATE hierarchy
        SET used_liters = ?
        WHERE id = ?
    ");
    $updateParent->bind_param("di", $newParentUsed, $parentNodeId);
    $updateParent->execute();

    // ------------------------------------------------------------
    // 10. Delete incoming package
    // ------------------------------------------------------------
    $conn->query("DELETE FROM incoming_items WHERE package_instance_id = $packageId");
    $conn->query("DELETE FROM incoming_packages WHERE id = $packageId");

    $conn->commit();

    echo json_encode([
        'ok' => true,
        'new_node_id' => $newNodeId,
        'new_node_name' => $nodeName
    ]);

} catch (Throwable $e) {
    $conn->rollback();
    echo json_encode(['error' => $e->getMessage()]);
}
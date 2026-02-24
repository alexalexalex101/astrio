<?php
session_start();
require_once 'db.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'POST only']);
    exit;
}

$packageId    = isset($_POST['package_instance_id']) ? (int)$_POST['package_instance_id'] : 0;
$hierarchy_id = isset($_POST['hierarchy_id']) ? (int)$_POST['hierarchy_id'] : 0;

if ($packageId <= 0 || $hierarchy_id <= 0) {
    echo json_encode(['error' => 'Missing package or node']);
    exit;
}

$conn->begin_transaction();
try {
    // fetch items in this package
    $stmt = $conn->prepare("
      SELECT id, name, type, expiry_date, calories, rfid, remaining_percent, volume_liters
      FROM incoming_items
      WHERE package_instance_id = ?
    ");
    $stmt->bind_param("i", $packageId);
    $stmt->execute();
    $res = $stmt->get_result();
    $items = $res->fetch_all(MYSQLI_ASSOC);

    // insert into items table
    $ins = $conn->prepare("
      INSERT INTO items
        (hierarchy_id, name, type, expiry_date, calories, rfid, remaining_percent, volume_liters, location)
      VALUES
        (?, ?, ?, ?, ?, ?, ?, ?, '')
    ");

    foreach ($items as $it) {
        $expiry = $it['expiry_date'] ?: null;
        $cal    = $it['calories'] !== null ? (int)$it['calories'] : null;
        $rem    = $it['remaining_percent'] !== null ? (int)$it['remaining_percent'] : 100;
        $vol    = $it['volume_liters'] !== null ? (float)$it['volume_liters'] : null;

        $ins->bind_param(
            "isssisis",
            $hierarchy_id,
            $it['name'],
            $it['type'],
            $expiry,
            $cal,
            $it['rfid'],
            $rem,
            $vol
        );
        $ins->execute();
    }

    // delete from incoming tables
    $delItems = $conn->prepare("DELETE FROM incoming_items WHERE package_instance_id = ?");
    $delItems->bind_param("i", $packageId);
    $delItems->execute();

    $delPkg = $conn->prepare("DELETE FROM incoming_packages WHERE id = ?");
    $delPkg->bind_param("i", $packageId);
    $delPkg->execute();

    $conn->commit();
    echo json_encode(['ok' => true]);
} catch (Throwable $e) {
    $conn->rollback();
    echo json_encode(['error' => $e->getMessage()]);
}
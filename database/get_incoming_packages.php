<?php
session_start();
require_once 'db.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
header('Content-Type: application/json; charset=utf-8');

$out = [];

$q = $conn->query("
  SELECT ip.id AS package_instance_id,
         ip.package_name,
         ip.schedule_order_id
  FROM incoming_packages ip
  ORDER BY ip.id DESC
");
while ($p = $q->fetch_assoc()) {
    $pid = (int)$p['package_instance_id'];

    $itemsRes = $conn->prepare("
      SELECT id, name, type, expiry_date, calories, rfid, remaining_percent, volume_liters
      FROM incoming_items
      WHERE package_instance_id = ?
      ORDER BY id ASC
    ");
    $itemsRes->bind_param("i", $pid);
    $itemsRes->execute();
    $items = $itemsRes->get_result()->fetch_all(MYSQLI_ASSOC);

    $p['items'] = $items;
    $out[] = $p;
}

echo json_encode($out, JSON_UNESCAPED_UNICODE);
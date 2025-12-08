<?php
require "db.php";

header('Content-Type: application/json');

// Get and validate ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    echo json_encode([]);
    exit;
}

// Only SELECT — no updates, no side effects
$stmt = $conn->prepare("
    SELECT id, name, location, expiry_date, calories, rfid, type 
    FROM items 
    WHERE hierarchy_id = ? 
    ORDER BY name
");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();

$out = [];
while ($r = $res->fetch_assoc()) {
    $out[] = $r;
}

echo json_encode($out);

$stmt->close();
$conn->close();
exit;
<?php
require "db.php";
header('Content-Type: application/json');

$q = $_GET['q'] ?? '';
$q = trim($q);

if ($q === '') {
    echo json_encode([]);
    exit;
}

$stmt = $conn->prepare("SELECT id, name, type, expiry_date, calories, notes, rfid 
                        FROM items 
                        WHERE name LIKE ? OR rfid LIKE ? 
                        ORDER BY id ASC");
$like = "%$q%";
$stmt->bind_param("ss", $like, $like);
$stmt->execute();
$res = $stmt->get_result();

$items = [];
while ($row = $res->fetch_assoc()) {
    $items[] = $row;
}

echo json_encode($items);

$stmt->close();
$conn->close();

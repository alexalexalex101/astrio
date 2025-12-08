<?php
require "db.php";
header('Content-Type: application/json');

$rfid = $_GET['rfid'] ?? '';
$rfid = trim($rfid);

if ($rfid === '') {
    echo json_encode([]);
    exit;
}

$stmt = $conn->prepare("SELECT id, name, notes, expiry_date, calories, rfid, type 
                        FROM items 
                        WHERE rfid = ? LIMIT 1");
$stmt->bind_param("s", $rfid);
$stmt->execute();
$stmt->bind_result($id, $name, $notes, $expiry_date, $calories, $rfid_val, $type);

if ($stmt->fetch()) {
    $item = [
        'id' => $id,
        'name' => $name,
        'notes' => $notes,
        'expiry_date' => $expiry_date,
        'calories' => $calories,
        'rfid' => $rfid_val,
        'type' => $type
    ];
    echo json_encode($item);
} else {
    echo json_encode([]);
}

$stmt->close();
$conn->close();
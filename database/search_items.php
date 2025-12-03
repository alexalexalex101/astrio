<?php
require "db.php";
$q = $_GET['q'] ?? '';
$q = trim($q);
if ($q === '') { echo json_encode([]); exit; }
$like = "%".$q."%";
$stmt = $conn->prepare("SELECT id, name, notes, expiry_date, calories, rfid, type FROM items WHERE name LIKE ? OR notes LIKE ? ORDER BY name LIMIT 200");
$stmt->bind_param("ss", $like, $like);
$stmt->execute();
$res = $stmt->get_result();
$out = [];
while($r = $res->fetch_assoc()) $out[] = $r;
header('Content-Type: application/json');
echo json_encode($out);
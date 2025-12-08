<?php
require "db.php";
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$stmt = $conn->prepare("SELECT id, name, location, expiry_date, calories, rfid, type FROM items WHERE hierarchy_id = ? ORDER BY name");
$stmt->bind_param("i",$id);
$stmt->execute();
$res = $stmt->get_result();
$out = [];
while($r = $res->fetch_assoc()) $out[] = $r;
header('Content-Type: application/json');
echo json_encode($out);
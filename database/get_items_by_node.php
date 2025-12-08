<?php
require "db.php";

$conn->query("
  UPDATE items
  SET type = 'waste',
      hierarchy_id = (SELECT id FROM hierarchy WHERE name = 'Waste Bay' LIMIT 1),
      notes = CONCAT(notes, ' | Expired and moved to Waste Bay')
  WHERE type = 'food'
    AND expiry_date IS NOT NULL
    AND expiry_date < CURDATE()
");

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$stmt = $conn->prepare("SELECT id, name, location, expiry_date, calories, rfid, type FROM items WHERE hierarchy_id = ? ORDER BY name");
$stmt->bind_param("i",$id);
$stmt->execute();
$res = $stmt->get_result();

$out = [];
while($r = $res->fetch_assoc()) $out[] = $r;

header('Content-Type: application/json');
echo json_encode($out);

$stmt->close();
$conn->close();
?>

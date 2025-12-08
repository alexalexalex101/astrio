<?php
require "db.php";
header('Content-Type: application/json');

$conn->query("
  UPDATE items
  SET type = 'waste',
      hierarchy_id = (SELECT id FROM hierarchy WHERE name = 'Waste Bay' LIMIT 1),
      notes = CONCAT(notes, ' | Expired and moved to Waste Bay')
  WHERE type = 'food'
    AND expiry_date IS NOT NULL
    AND expiry_date < CURDATE()
");

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
?>

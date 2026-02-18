<?php
require 'db.php'; // your database connection

$q = $_GET['q'] ?? '';
$q = "%$q%";

$stmt = $conn->prepare("
    SELECT * FROM incoming
    WHERE name LIKE ? OR rfid LIKE ?
    ORDER BY created_at DESC
    LIMIT 50
");
$stmt->bind_param('ss', $q, $q);
$stmt->execute();
$res = $stmt->get_result();
$items = $res->fetch_all(MYSQLI_ASSOC);

echo json_encode($items);

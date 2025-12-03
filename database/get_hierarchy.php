<?php
require "db.php";

$res = $conn->query("SELECT id, parent_id, name, type FROM hierarchy ORDER BY parent_id IS NOT NULL, id");
$rows = [];
while($r = $res->fetch_assoc()) $rows[] = $r;
header('Content-Type: application/json');
echo json_encode($rows);
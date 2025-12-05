<?php
require "db.php";
$name = $_POST['name'] ?? '';
$type = $_POST['type'] ?? 'small_team';
$parent_id = intval($_POST['parent_id'] ?? 0);
if (trim($name) === '') { echo "ERR: missing name"; exit; }

$stmt = $conn->prepare("INSERT INTO hierarchy (parent_id, name, type) VALUES (?, ?, ?)");
$pid = $parent_id === 0 ? null : $parent_id;
$stmt->bind_param("iss", $pid, $name, $type);
$ok = $stmt->execute();
echo $ok ? "OK" : "ERR: " . $conn->error;

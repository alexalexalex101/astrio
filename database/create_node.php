<?php
require "db.php";

header('Content-Type: text/plain');

$name = trim($_POST['name'] ?? '');
$parent_id = $_POST['parent_id'] ?? 0;
$parent_id = $parent_id == 0 ? null : (int)$parent_id;

if ($name === '') {
    echo "ERR: missing name";
    exit;
}

$stmt = $conn->prepare("INSERT INTO hierarchy (name, parent_id) VALUES (?, ?)");
$stmt->bind_param("si", $name, $parent_id);

echo $stmt->execute() ? "OK" : "ERR: " . $stmt->error;

$stmt->close();
$conn->close();
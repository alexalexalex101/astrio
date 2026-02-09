<?php
require 'db.php';

if (!isset($_POST['name'], $_POST['type'], $_POST['location'], $_POST['expiry_date'], $_POST['calories'], $_POST['rfid'])) {
    exit('Missing fields');
}

$name = $_POST['name'];
$type = $_POST['type'];
$location = $_POST['location'];
$expiry = $_POST['expiry_date'];
$calories = $_POST['calories'];
$rfid = $_POST['rfid'];

$stmt = $conn->prepare("
    INSERT INTO incoming (name, type, location, expiry_date, calories, rfid)
    VALUES (?, ?, ?, ?, ?, ?)
");
$stmt->bind_param("ssssss", $name, $type, $location, $expiry, $calories, $rfid);

if ($stmt->execute()) {
    echo "OK";
} else {
    echo "Error: " . $stmt->error;
}

<?php
require "db.php";
header('Content-Type: application/json');

// --- Auto-move expired food to Waste Bay ---


// --- Read and sanitize query ---
$q = isset($_GET['q']) ? trim($_GET['q']) : "";

// --- If empty search, return ZERO items ---
if ($q === "") {
    echo json_encode([]);
    exit;
}

$like = "%$q%";

// --- Prepared search query ---
$stmt = $conn->prepare("
    SELECT 
        i.id,
        i.name,
        i.type,
        i.expiry_date,
        i.calories,
        i.location,
        i.rfid,
        i.hierarchy_id,
        h.name AS hierarchy_name,
        i.remaining_percent,
        i.volume_liters
    FROM items i
    LEFT JOIN hierarchy h ON h.id = i.hierarchy_id
    WHERE i.name LIKE ? 
       OR i.rfid LIKE ?
    ORDER BY i.id ASC
");

$stmt->bind_param("ss", $like, $like);
$stmt->execute();
$res = $stmt->get_result();

// --- Build result array ---
$items = [];
while ($row = $res->fetch_assoc()) {
    $items[] = $row;
}

echo json_encode($items);

$stmt->close();
$conn->close();

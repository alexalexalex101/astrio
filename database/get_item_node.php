<?php
// get_item_node.php
// Standalone version – no config.php, no includes

// Prevent any output before our JSON
ob_start();

// Force JSON response header
header('Content-Type: application/json; charset=utf-8');

// Turn off error display so it doesn't break JSON
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(E_ALL);  // still log errors to php error log

// ────────────────────────────────────────────────
//    CHANGE THESE VALUES TO MATCH YOUR DATABASE
// ────────────────────────────────────────────────
$host     = 'localhost';
$dbname   = 'inventory';     // ← put your actual database name here
$username = 'root';                   // ← usually 'root' in XAMPP
$password = '';                       // ← usually empty in XAMPP

// Table and column names – change if yours are different
$table_name       = 'items';          // your items table
$id_column        = 'id';
$hierarchy_column = 'hierarchy_id';

try {
    // Create PDO connection right here
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );

    // Get item ID from URL
    $item_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if ($item_id <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid or missing item ID']);
        exit;
    }

    // Query
    $stmt = $pdo->prepare("
        SELECT $hierarchy_column 
        FROM $table_name 
        WHERE $id_column = :id 
        LIMIT 1
    ");
    $stmt->execute(['id' => $item_id]);
    $row = $stmt->fetch();

    if ($row) {
        echo json_encode([
            'hierarchy_id' => (string)$row[$hierarchy_column]   // string to avoid type issues in JS
        ]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Item not found']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Database connection or query failed'
        // In production: do NOT show real error message
        // For debugging only: 'debug' => $e->getMessage()
    ]);
}

// Send output
ob_end_flush();

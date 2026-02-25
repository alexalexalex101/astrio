<?php
session_start();
include('connection.php');
include_once('action_logger.php');

$conn = isset($conn) ? $conn : null;

// Get table name from session (fallback to 'users')
$table_name = $_SESSION['table'] ?? 'users';

// Get form values
$first_name = trim($_POST['first_name'] ?? '');
$last_name  = trim($_POST['last_name'] ?? '');
$email      = trim($_POST['email'] ?? '');
$password   = $_POST['password'] ?? '';

// Hash the password
$encrypted = password_hash($password, PASSWORD_DEFAULT);

// Prepare response array
$response = [];

// ────────────────────────────────────────────────
// Validation checks (early exits)
// ────────────────────────────────────────────────

if (empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
    log_action($conn, 'add', 'error', 'registration failed: missing required fields', 'add.php');
    $response = [
        'success' => false,
        'message' => 'All fields are required.'
    ];
    $_SESSION['response'] = $response;
    header('Location: ../useradd.php');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    log_action($conn, 'add', 'error', "registration failed: invalid email format - {$email}", 'add.php');
    $response = [
        'success' => false,
        'message' => 'Please enter a valid email address.'
    ];
    $_SESSION['response'] = $response;
    header('Location: ../useradd.php');
    exit;
}

// ────────────────────────────────────────────────
// Check for duplicate email
// ────────────────────────────────────────────────

try {
    $check = $conn->prepare("SELECT id FROM $table_name WHERE email = :email LIMIT 1");
    $check->execute([':email' => $email]);

    if ($check->rowCount() > 0) {
        log_action($conn, 'add', 'error', "registration failed: email already exists - {$email}", 'add.php');
        $response = [
            'success' => false,
            'message' => 'This email is already registered.'
        ];
        $_SESSION['response'] = $response;
        header('Location: ../useradd.php');
        exit;
    }

    // ────────────────────────────────────────────────
    // Insert new user
    // ────────────────────────────────────────────────

    $stmt = $conn->prepare("
        INSERT INTO $table_name (first_name, last_name, email, password, created_at, updated_at)
        VALUES (:first_name, :last_name, :email, :password, NOW(), NOW())
    ");

    $stmt->execute([
        ':first_name' => $first_name,
        ':last_name'  => $last_name,
        ':email'      => $email,
        ':password'   => $encrypted
    ]);

    // Success
    $msg = "new user registered successfully: {$email} ({$first_name} {$last_name})";
    log_action($conn, 'add', 'success', $msg, 'add.php');

    $response = [
        'success' => true,
        'message' => 'User successfully added! Please log in.'
    ];

    $_SESSION['response'] = $response;
    header('Location: ../nasalogin.php');
    exit;

} catch (PDOException $e) {
    // Database error
    $error_msg = $e->getMessage();
    $log_msg = "registration failed due to database error for {$email} – {$error_msg}";
    log_action($conn, 'add', 'error', $log_msg, 'add.php');

    $response = [
        'success' => false,
        'message' => 'Database error occurred. Please try again later.'
    ];
}

$_SESSION['response'] = $response;
header('Location: ../useradd.php');
exit;
<?php
session_start();

// get table name from the session
$table_name = $_SESSION['table'] ?? 'users';

// get values sent from the form
$first_name = $_POST['first_name'] ?? '';
$last_name  = $_POST['last_name'] ?? '';
$email      = $_POST['email'] ?? '';
$password   = $_POST['password'] ?? '';

// turn the password into a secure hashed value
$encrypted = password_hash($password, PASSWORD_DEFAULT);

// this will store success or error messages
$response = [];

// make sure none of the form fields are empty
if (empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
    $response = [
        'success' => false,
        'message' => 'All fields are required.'
    ];
    $_SESSION['response'] = $response;
    header('Location: ../useradd.php');
    exit;
}

// check if the email typed in looks like a real email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $response = [
        'success' => false,
        'message' => 'Please enter a valid email address.'
    ];
    $_SESSION['response'] = $response;
    header('Location: ../useradd.php');
    exit;
}

try {
    // connect to the database
    include('connection.php');

    // check if this email is already in the table
    // this stops users from signing up with the same email
    $check = $conn->prepare("SELECT id FROM $table_name WHERE email = :email LIMIT 1");
    $check->execute([':email' => $email]);

    // if the query finds 1 or more rows, the email is already used
    if ($check->rowCount() > 0) {
        $response = [
            'success' => false,
            'message' => 'This email is already registered.'
        ];
        $_SESSION['response'] = $response;
        header('Location: ../useradd.php');
        exit;
    }

    // prepare the insert so it is safe from SQL injection
    $stmt = $conn->prepare("
        INSERT INTO $table_name (first_name, last_name, email, password, created_at, updated_at)
        VALUES (:first_name, :last_name, :email, :password, NOW(), NOW())
    ");

    // run the insert using the form values
    $stmt->execute([
        ':first_name' => $first_name,
        ':last_name'  => $last_name,
        ':email'      => $email,
        ':password'   => $encrypted
    ]);

    // set a success message
    $response = [
        'success' => true,
        'message' => 'User successfully added! Please log in.'
    ];

    $_SESSION['response'] = $response;

    // send user to login page after successful signup
    header('Location: ../nasalogin.php');
    exit;

} catch (PDOException $e) {
    // if something goes wrong with the database, show the message
    $response = [
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ];
}

$_SESSION['response'] = $response;
header('Location: ../useradd.php');
exit;
?>

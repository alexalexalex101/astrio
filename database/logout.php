<?php
session_start();
require 'connection.php';
require_once 'action_logger.php';

// Make sure we have the connection
if (!isset($conn) || !$conn) {
    // If connection failed → log minimally and still proceed with logout
    if (isset($conn)) {
        log_action($conn, 'logout', 'error', 'logout attempted but database connection missing', 'logout.php');
    }
    // Continue anyway - logout should work even without DB
} else {
    // Get user info from session before destroying it
    $user_email = '';
    if (isset($_SESSION['user']) && is_array($_SESSION['user'])) {
        $user_email = trim($_SESSION['user']['email'] ?? '');
    }

    // Log success (or at least the attempt) in plain text
    if ($user_email !== '') {
        $msg = "successful logout for user: {$user_email}";
        log_action($conn, 'logout', 'success', $msg, 'logout.php');
    } else {
        // If no user info in session (edge case)
        log_action($conn, 'logout', 'success', 'logout completed (no user email found in session)', 'logout.php');
    }
}

// Clear and destroy session
session_unset();
session_destroy();

// Redirect to public landing page
header('Location: ../index.html');
exit;
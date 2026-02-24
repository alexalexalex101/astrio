<?php
session_start();
require 'connection.php';
require_once 'action_logger.php';

if (isset($conn)) {
    log_action($conn, 'logout', 'success', ['message' => 'User logged out'], 'logout.php');
}

session_unset();

session_destroy();

header('Location: ../index.html');
exit;

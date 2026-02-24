<?php

if (!function_exists('action_log_get_user_context')) {
    function action_log_get_user_context()
    {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }

        $ctx = [
            'user_id' => null,
            'user_email' => null,
            'user_name' => null,
        ];

        if (isset($_SESSION['user']) && is_array($_SESSION['user'])) {
            $u = $_SESSION['user'];
            if (isset($u['id'])) $ctx['user_id'] = (int)$u['id'];
            if (isset($u['email'])) $ctx['user_email'] = (string)$u['email'];

            $first = isset($u['first_name']) ? trim((string)$u['first_name']) : '';
            $last = isset($u['last_name']) ? trim((string)$u['last_name']) : '';
            $full = trim($first . ' ' . $last);
            if ($full !== '') $ctx['user_name'] = $full;
        }

        if ($ctx['user_id'] === null && isset($_SESSION['user_id'])) {
            $ctx['user_id'] = (int)$_SESSION['user_id'];
        }
        if ($ctx['user_id'] === null && isset($_SESSION['id'])) {
            $ctx['user_id'] = (int)$_SESSION['id'];
        }

        return $ctx;
    }
}

if (!function_exists('action_log_ensure_table')) {
    function action_log_ensure_table($conn)
    {
        static $ready = false;
        if ($ready) return;

        $createSql = "CREATE TABLE IF NOT EXISTS action_logs (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            action_name VARCHAR(120) NOT NULL,
            source_file VARCHAR(120) NOT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'success',
            user_id INT NULL,
            user_email VARCHAR(255) NULL,
            user_name VARCHAR(255) NULL,
            ip_address VARCHAR(45) NULL,
            details TEXT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_created_at (created_at),
            INDEX idx_action_name (action_name)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        if ($conn instanceof PDO) {
            $conn->exec($createSql);
        } else {
            $conn->query($createSql);
        }

        $ready = true;
    }
}

if (!function_exists('log_action')) {
    function log_action($conn, $actionName, $status = 'success', $details = [], $sourceFile = null)
    {
        if (!$conn || !$actionName) return;

        try {
            action_log_ensure_table($conn);

            $ctx = action_log_get_user_context();
            $ip = isset($_SERVER['REMOTE_ADDR']) ? (string)$_SERVER['REMOTE_ADDR'] : null;
            $source = $sourceFile ?: basename(isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : 'unknown.php');

            $detailString = null;
            if (is_array($details)) {
                $json = json_encode($details, JSON_UNESCAPED_UNICODE);
                $detailString = $json !== false ? $json : null;
            } elseif ($details !== null) {
                $detailString = (string)$details;
            }

            if ($conn instanceof PDO) {
                $stmt = $conn->prepare("
                    INSERT INTO action_logs
                    (action_name, source_file, status, user_id, user_email, user_name, ip_address, details)
                    VALUES (:action_name, :source_file, :status, :user_id, :user_email, :user_name, :ip_address, :details)
                ");
                $stmt->execute([
                    ':action_name' => (string)$actionName,
                    ':source_file' => (string)$source,
                    ':status' => (string)$status,
                    ':user_id' => $ctx['user_id'],
                    ':user_email' => $ctx['user_email'],
                    ':user_name' => $ctx['user_name'],
                    ':ip_address' => $ip,
                    ':details' => $detailString
                ]);
            } else {
                $stmt = $conn->prepare("
                    INSERT INTO action_logs
                    (action_name, source_file, status, user_id, user_email, user_name, ip_address, details)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                if (!$stmt) return;

                $uid = $ctx['user_id'];
                $email = $ctx['user_email'];
                $name = $ctx['user_name'];
                $stmt->bind_param(
                    "sssissss",
                    $actionName,
                    $source,
                    $status,
                    $uid,
                    $email,
                    $name,
                    $ip,
                    $detailString
                );
                $stmt->execute();
                $stmt->close();
            }
        } catch (Throwable $e) {
            // Logging must never break application flows.
        }
    }
}


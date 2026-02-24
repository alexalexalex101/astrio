<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: nasalogin.php');
    exit;
}

require_once 'database/db.php';
require_once 'database/action_logger.php';

action_log_ensure_table($conn);

$logs = [];
$query = "SELECT id, action_name, source_file, status, user_id, user_email, user_name, ip_address, details, created_at
          FROM action_logs
          ORDER BY id DESC
          LIMIT 500";
$res = $conn->query($query);
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $logs[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Action Logs</title>
    <link rel="stylesheet" href="default.css">
    <style>
        body {
            min-height: 100vh;
            background: #000 url('images/space.avif');
            color: #fff;
            font-family: "League Spartan", system-ui, sans-serif;
        }

        #logoutBtn {
            position: fixed;
            top: 1.8rem;
            right: 1.8rem;
            padding: 0.8rem 1.6rem;
            font-size: 1.1rem;
            border-radius: 2.5rem;
            background: rgba(75, 83, 185, 0.75);
            color: white;
            text-decoration: none;
            font-weight: 600;
            box-shadow: 0 0 18px rgba(75, 83, 185, 0.45);
            z-index: 100;
        }

        .logs-wrap {
            width: min(1200px, 94vw);
            margin: 8.5rem auto 2.5rem;
            background: rgba(12, 18, 44, 0.86);
            border-radius: 14px;
            padding: 1rem;
            box-shadow: 0 0 18px rgba(75, 83, 185, 0.45);
        }

        .title {
            font-family: "nasalization", sans-serif;
            letter-spacing: 1px;
            margin: 0.5rem 0 1rem;
            text-align: center;
        }

        .table-scroll {
            overflow: auto;
            max-height: 72vh;
            border-radius: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 980px;
        }

        th, td {
            padding: 0.65rem 0.6rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            text-align: left;
            font-size: 0.95rem;
            vertical-align: top;
        }

        th {
            position: sticky;
            top: 0;
            background: #0b2f73;
            z-index: 2;
        }

        .status-success { color: #8effad; font-weight: 700; }
        .status-error { color: #ff8f8f; font-weight: 700; }
        .details-cell {
            white-space: pre-wrap;
            word-break: break-word;
            max-width: 360px;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <a href="dashboard.php">
        <img src="images/NASA-Logo.png" alt="NASA Logo" class="nasalogo">
    </a>
    <a href="database/logout.php" id="logoutBtn">Log out</a>
    <a href="dashboard.php" id="backbutton">Back</a>

    <div class="logs-wrap">
        <h1 class="title">Action Logs</h1>
        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Timestamp</th>
                        <th>Action</th>
                        <th>Source</th>
                        <th>Status</th>
                        <th>User</th>
                        <th>IP</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr><td colspan="8">No logs found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <?php
                                $status = strtolower((string)$log['status']);
                                $statusClass = $status === 'success' ? 'status-success' : ($status === 'error' ? 'status-error' : '');
                                $userLabel = trim((string)($log['user_name'] ?? ''));
                                if ($userLabel === '') $userLabel = (string)($log['user_email'] ?? '');
                                if ($userLabel === '') $userLabel = $log['user_id'] !== null ? ('User #' . $log['user_id']) : 'Guest';
                            ?>
                            <tr>
                                <td><?= (int)$log['id'] ?></td>
                                <td><?= htmlspecialchars((string)$log['created_at']) ?></td>
                                <td><?= htmlspecialchars((string)$log['action_name']) ?></td>
                                <td><?= htmlspecialchars((string)$log['source_file']) ?></td>
                                <td class="<?= $statusClass ?>"><?= htmlspecialchars((string)$log['status']) ?></td>
                                <td><?= htmlspecialchars($userLabel) ?></td>
                                <td><?= htmlspecialchars((string)$log['ip_address']) ?></td>
                                <td class="details-cell"><?= htmlspecialchars((string)$log['details']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>


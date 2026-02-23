<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
/**
 * schedule_orders.php
 * - Matches the fancy "space" design
 * - Matches the real inventory behavior:
 *    1) Request To Station -> inserts rows into `incoming`
 *    2) Send Back To Earth -> moves rows `items` -> `outgoing` and deletes from `items`
 * - Also logs a JSON summary into `schedule_orders`
 *
 * Requires: db.php defines $conn as mysqli connection to DB `inventory`
 */
session_start();
require_once 'database/db.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// ---------------------------
// Ensure schedule_orders table exists
// ---------------------------
try {
  $conn->query("
    CREATE TABLE IF NOT EXISTS schedule_orders (
      id INT AUTO_INCREMENT PRIMARY KEY,
      user_id INT NULL,
      order_to_station TEXT NOT NULL,
      sending_back TEXT NOT NULL,
      priority ENUM('Normal','High','Critical') NOT NULL DEFAULT 'Normal',
      scheduled_date DATE NOT NULL,
      status ENUM('Scheduled','In Transit','Delivered','Canceled') NOT NULL DEFAULT 'Scheduled',
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      INDEX (user_id),
      INDEX (scheduled_date),
      CONSTRAINT fk_schedule_orders_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  ");
} catch (Throwable $e) {
  // FK fallback (keeps app working if users table missing / mismatch)
  $conn->query("
    CREATE TABLE IF NOT EXISTS schedule_orders (
      id INT AUTO_INCREMENT PRIMARY KEY,
      user_id INT NULL,
      order_to_station TEXT NOT NULL,
      sending_back TEXT NOT NULL,
      priority ENUM('Normal','High','Critical') NOT NULL DEFAULT 'Normal',
      scheduled_date DATE NOT NULL,
      status ENUM('Scheduled','In Transit','Delivered','Canceled') NOT NULL DEFAULT 'Scheduled',
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      INDEX (user_id),
      INDEX (scheduled_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  ");
}

// ---------------------------
// Helpers
// ---------------------------
function h($s)
{
  return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

// ---------------------------
// Build catalog dropdown from items table (grouped counts by name+type)
// ---------------------------
$catalog = [];
$res = $conn->query("
  SELECT name, type, COUNT(*) AS qty
  FROM items
  GROUP BY name, type
  ORDER BY name ASC, type ASC
");
while ($row = $res->fetch_assoc()) $catalog[] = $row;

// ---------------------------
// Handle submit
// ---------------------------
$success = '';
$error   = '';

$old = [
  'request_key'   => '',
  'request_qty'   => '',
  'send_key'      => '',
  'send_qty'      => '',
  'priority'      => 'Normal',
  'scheduled_date' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Inputs
  $old['request_key']    = $_POST['request_key'] ?? '';
  $old['request_qty']    = $_POST['request_qty'] ?? '';
  $old['send_key']       = $_POST['send_key'] ?? '';
  $old['send_qty']       = $_POST['send_qty'] ?? '';
  $old['priority']       = $_POST['priority'] ?? 'Normal';
  $old['scheduled_date'] = $_POST['scheduled_date'] ?? '';

  $allowedPriorities = ['Normal', 'High', 'Critical'];
  if (!in_array($old['priority'], $allowedPriorities, true)) $old['priority'] = 'Normal';

  $scheduledDate = $old['scheduled_date'];
  $priority      = $old['priority'];

  $reqQty  = (int)($old['request_qty'] ?? 0);
  $sendQty = (int)($old['send_qty'] ?? 0);

  // Parse dropdown packed value: "name|||type"
  $reqName = $reqType = '';
  if ($old['request_key'] !== '' && str_contains($old['request_key'], '|||')) {
    [$reqName, $reqType] = explode('|||', $old['request_key'], 2);
    $reqName = trim($reqName);
    $reqType = trim($reqType);
  }

  $sendName = $sendType = '';
  if ($old['send_key'] !== '' && str_contains($old['send_key'], '|||')) {
    [$sendName, $sendType] = explode('|||', $old['send_key'], 2);
    $sendName = trim($sendName);
    $sendType = trim($sendType);
  }

  // Basic validation
  if (!$scheduledDate) {
    $error = "Please choose a scheduled date.";
  } elseif ($reqQty < 0 || $sendQty < 0) {
    $error = "Quantities cannot be negative.";
  } elseif (($reqQty > 0 && $reqName === '') || ($sendQty > 0 && $sendName === '')) {
    $error = "Please select an item for any quantity you enter.";
  } elseif ($reqQty === 0 && $sendQty === 0) {
    $error = "Please enter a quantity to request and/or send back.";
  } else {

    // Optional: attach logged-in user id if your auth sets it
    $user_id = null;
    if (isset($_SESSION['user_id'])) $user_id = (int)$_SESSION['user_id'];
    elseif (isset($_SESSION['id']))  $user_id = (int)$_SESSION['id'];

    // Build a schedule log payload (JSON)
    $orderToStation = [
      'item' => $reqName ? ['name' => $reqName, 'type' => $reqType, 'qty' => $reqQty] : null
    ];
    $sendingBack = [
      'item' => $sendName ? ['name' => $sendName, 'type' => $sendType, 'qty' => $sendQty] : null
    ];

    $conn->begin_transaction();
    try {
      // ---------------------------
      // 1) REQUEST TO STATION -> incoming
      // ---------------------------
      if ($reqQty > 0 && $reqName !== '') {
        // Adjust columns to match your incoming table.
        // Using the same column list you used in PDO version.
        $insIncoming = $conn->prepare("
          INSERT INTO incoming
            (hierarchy_id, name, type, location, expiry_date, calories, rfid, remaining_percent, volume_liters)
          VALUES
            (0, ?, ?, 'INCOMING / SCHEDULED', NULL, NULL, NULL, 100, NULL)
        ");
        $insIncoming->bind_param("ss", $reqName, $reqType);

        for ($i = 0; $i < $reqQty; $i++) {
          $insIncoming->execute();
        }
      }

      // ---------------------------
      // 2) SEND BACK TO EARTH -> items -> outgoing (and delete from items)
      // ---------------------------
      if ($sendQty > 0 && $sendName !== '') {
        // Lock and fetch N matching rows (deterministic order)
        $sel = $conn->prepare("
          SELECT id, name, type, rfid, location
          FROM items
          WHERE name = ? AND type = ?
          ORDER BY id ASC
          LIMIT ?
          FOR UPDATE
        ");
        $sel->bind_param("ssi", $sendName, $sendType, $sendQty);
        $sel->execute();
        $pickedRes = $sel->get_result();
        $picked = [];
        while ($r = $pickedRes->fetch_assoc()) $picked[] = $r;

        if (count($picked) < $sendQty) {
          throw new Exception("Not enough '{$sendName} ({$sendType})' in inventory. Available: " . count($picked) . ", requested: {$sendQty}.");
        }

        $insOutgoing = $conn->prepare("
          INSERT INTO outgoing (item_id, name, type, rfid, from_location, scheduled_date, priority)
          VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $delItem = $conn->prepare("DELETE FROM items WHERE id = ?");

        foreach ($picked as $it) {
          $itemId = (int)$it['id'];
          $rfid   = $it['rfid'];
          $from   = $it['location'];

          $insOutgoing->bind_param(
            "issssss",
            $itemId,
            $it['name'],
            $it['type'],
            $rfid,
            $from,
            $scheduledDate,
            $priority
          );
          $insOutgoing->execute();

          $delItem->bind_param("i", $itemId);
          $delItem->execute();
        }
      }

      // ---------------------------
      // 3) LOG INTO schedule_orders
      // ---------------------------
      $orderJson = json_encode($orderToStation, JSON_UNESCAPED_UNICODE);
      $sendJson  = json_encode($sendingBack, JSON_UNESCAPED_UNICODE);

      if ($user_id === null) {
        $stmtLog = $conn->prepare("
          INSERT INTO schedule_orders (user_id, order_to_station, sending_back, priority, scheduled_date)
          VALUES (NULL, ?, ?, ?, ?)
        ");
        $stmtLog->bind_param("ssss", $orderJson, $sendJson, $priority, $scheduledDate);
      } else {
        $stmtLog = $conn->prepare("
          INSERT INTO schedule_orders (user_id, order_to_station, sending_back, priority, scheduled_date)
          VALUES (?, ?, ?, ?, ?)
        ");
        $stmtLog->bind_param("issss", $user_id, $orderJson, $sendJson, $priority, $scheduledDate);
      }
      $stmtLog->execute();

      $conn->commit();
      $success = "Schedule updated ✅ Inventory updated immediately.";

      // Clear form
      $old = [
        'request_key' => '',
        'request_qty' => '',
        'send_key' => '',
        'send_qty' => '',
        'priority' => 'Normal',
        'scheduled_date' => ''
      ];

      // refresh catalog counts after commit
      $catalog = [];
      $res = $conn->query("
        SELECT name, type, COUNT(*) AS qty
        FROM items
        GROUP BY name, type
        ORDER BY name ASC, type ASC
      ");
      while ($row = $res->fetch_assoc()) $catalog[] = $row;
    } catch (Throwable $e) {
      $conn->rollback();
      $error = $e->getMessage();
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>NASA Schedule Orders - Solar System</title>
  <link rel="stylesheet" href="default.css">

  <style>
    /* Base Reset */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: "League Spartan", sans-serif;
    }

    html,
    body {
      background-image: none;
      background-color: transparent;
    }

    :root {
      --cyan: #00b7ff;
      --cyan2: #00e5ff;
      --blue: #0b2d6b;
      --glass: rgba(8, 14, 30, 0.62);
      --glass2: rgba(10, 18, 40, 0.72);
      --stroke: rgba(120, 220, 255, 0.22);
      --text: rgba(255, 255, 255, 0.92);
    }

    body {
      min-height: 100vh;
      color: var(--text);
      background: #000;
      overflow-x: hidden;
    }

    /* =========================
   IMMERSIVE SPACE BACKGROUND
   ========================= */
    .space {
      position: fixed;
      inset: 0;
      background:
        radial-gradient(1200px 800px at 70% 20%, rgba(0, 255, 255, 0.10), transparent 55%),
        radial-gradient(900px 700px at 20% 65%, rgba(120, 80, 255, 0.12), transparent 58%),
        radial-gradient(800px 600px at 80% 80%, rgba(0, 160, 255, 0.10), transparent 60%),
        radial-gradient(ellipse at center, #0a0c18 0%, #03040a 55%, #000 100%);
      z-index: -5;
    }

    .nebula {
      position: fixed;
      inset: -20%;
      background:
        radial-gradient(500px 360px at 35% 40%, rgba(140, 90, 255, 0.16), transparent 60%),
        radial-gradient(650px 420px at 65% 55%, rgba(0, 180, 255, 0.12), transparent 65%),
        radial-gradient(520px 420px at 55% 30%, rgba(0, 255, 200, 0.08), transparent 60%);
      filter: blur(18px) saturate(1.1);
      opacity: 0.9;
      animation: nebulaDrift 22s ease-in-out infinite alternate;
      z-index: -4;
    }

    @keyframes nebulaDrift {
      0% {
        transform: translate3d(-1.5%, -1%, 0) scale(1.02);
      }

      100% {
        transform: translate3d(1.5%, 1.2%, 0) scale(1.05);
      }
    }

    .stars,
    .stars2,
    .stars3 {
      position: fixed;
      inset: 0;
      z-index: -3;
      pointer-events: none;
      background-repeat: repeat;
      opacity: 0.85;
    }

    .stars {
      background-image:
        radial-gradient(1px 1px at 12% 18%, rgba(255, 255, 255, .85) 50%, transparent 52%),
        radial-gradient(1px 1px at 72% 34%, rgba(255, 255, 255, .75) 50%, transparent 52%),
        radial-gradient(1px 1px at 44% 66%, rgba(255, 255, 255, .7) 50%, transparent 52%),
        radial-gradient(1px 1px at 88% 78%, rgba(255, 255, 255, .8) 50%, transparent 52%),
        radial-gradient(1px 1px at 26% 82%, rgba(255, 255, 255, .65) 50%, transparent 52%),
        radial-gradient(1px 1px at 6% 58%, rgba(255, 255, 255, .6) 50%, transparent 52%);
      background-size: 260px 260px;
      animation: starDrift 160s linear infinite;
    }

    .stars2 {
      background-image:
        radial-gradient(1.5px 1.5px at 18% 24%, rgba(255, 255, 255, .75) 50%, transparent 52%),
        radial-gradient(1.5px 1.5px at 62% 12%, rgba(255, 255, 255, .65) 50%, transparent 52%),
        radial-gradient(1.5px 1.5px at 84% 56%, rgba(255, 255, 255, .7) 50%, transparent 52%),
        radial-gradient(1.5px 1.5px at 36% 74%, rgba(255, 255, 255, .6) 50%, transparent 52%);
      background-size: 420px 420px;
      opacity: 0.55;
      animation: starDrift2 240s linear infinite;
    }

    .stars3 {
      background-image:
        radial-gradient(2px 2px at 22% 40%, rgba(255, 255, 255, .9) 45%, transparent 55%),
        radial-gradient(2px 2px at 76% 22%, rgba(255, 255, 255, .85) 45%, transparent 55%),
        radial-gradient(2px 2px at 58% 78%, rgba(255, 255, 255, .8) 45%, transparent 55%);
      background-size: 700px 700px;
      opacity: 0.35;
      animation: starTwinkle 5s ease-in-out infinite alternate;
    }

    @keyframes starDrift {
      from {
        background-position: 0 0;
      }

      to {
        background-position: -9000px 4500px;
      }
    }

    @keyframes starDrift2 {
      from {
        background-position: 0 0;
      }

      to {
        background-position: -7000px 9000px;
      }
    }

    @keyframes starTwinkle {
      from {
        opacity: 0.25;
        filter: brightness(1);
      }

      to {
        opacity: 0.45;
        filter: brightness(1.2);
      }
    }

    .vignette {
      position: fixed;
      inset: 0;
      z-index: -2;
      pointer-events: none;
      background: radial-gradient(ellipse at center, transparent 0%, rgba(0, 0, 0, 0.15) 55%, rgba(0, 0, 0, 0.55) 100%);
    }

    /* =========================
   SOLAR SYSTEM SCENE
   ========================= */
    .scene {
      position: fixed;
      inset: 0;
      z-index: -1;
      pointer-events: none;
    }

    .station {
      position: absolute;
      top: 50%;
      left: 50%;
      width: 72px;
      height: 72px;
      border-radius: 50%;
      transform: translate(-50%, -50%);
      background:
        radial-gradient(circle at 30% 30%, rgba(255, 255, 255, 0.9), rgba(0, 229, 255, 0.55) 35%, rgba(0, 120, 255, 0.18) 62%, rgba(0, 0, 0, 0) 70%),
        radial-gradient(circle at 60% 70%, rgba(0, 255, 255, 0.22), rgba(0, 0, 0, 0) 60%);
      box-shadow: 0 0 22px rgba(0, 229, 255, 0.55), 0 0 60px rgba(0, 229, 255, 0.25), inset 0 0 22px rgba(255, 255, 255, 0.35);
    }

    .station::after {
      content: "";
      position: absolute;
      inset: -18px;
      border-radius: 50%;
      border: 1px solid rgba(150, 240, 255, 0.18);
      box-shadow: 0 0 25px rgba(0, 229, 255, 0.12);
    }

    .orbit {
      position: absolute;
      top: 50%;
      left: 50%;
      border-radius: 50%;
      transform: translate(-50%, -50%);
      border: 1px dashed rgba(200, 240, 255, 0.14);
      box-shadow: 0 0 18px rgba(0, 229, 255, 0.06);
    }

    .body {
      position: absolute;
      top: 50%;
      left: 50%;
      border-radius: 50%;
      filter: drop-shadow(0 0 10px rgba(0, 229, 255, 0.18));
    }

    @keyframes orbitSpin {
      from {
        transform: rotate(0deg) translateX(var(--r)) rotate(0deg);
      }

      to {
        transform: rotate(360deg) translateX(var(--r)) rotate(-360deg);
      }
    }

    .earth {
      --r: 320px;
      width: 34px;
      height: 34px;
      background:
        radial-gradient(circle at 30% 35%, rgba(255, 255, 255, 0.85), rgba(0, 0, 0, 0) 30%),
        radial-gradient(circle at 55% 55%, #2e7cff 0%, #0c2b6a 55%, #03122f 100%);
      box-shadow: 0 0 14px rgba(46, 124, 255, 0.45), 0 0 26px rgba(0, 229, 255, 0.18), inset 0 0 10px rgba(0, 255, 180, 0.18);
      animation: orbitSpin 44s linear infinite;
    }

    .earth::after {
      content: "";
      position: absolute;
      inset: 7px;
      border-radius: 50%;
      background:
        radial-gradient(circle at 40% 55%, rgba(0, 255, 180, 0.55), rgba(0, 0, 0, 0) 55%),
        radial-gradient(circle at 60% 40%, rgba(0, 255, 180, 0.35), rgba(0, 0, 0, 0) 55%);
      opacity: 0.75;
    }

    .transfer {
      position: absolute;
      top: 50%;
      left: 50%;
      width: 740px;
      height: 740px;
      transform: translate(-50%, -50%) rotate(-18deg);
      border-radius: 50%;
      border: 2px solid transparent;
      border-top-color: rgba(0, 229, 255, 0.22);
      filter: drop-shadow(0 0 10px rgba(0, 229, 255, 0.18));
      mask: radial-gradient(circle, transparent 57%, #000 58%);
      opacity: 0.8;
    }

    .ship {
      position: absolute;
      top: 50%;
      left: 50%;
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.95);
      box-shadow: 0 0 12px rgba(0, 229, 255, 0.8);
      animation: shipRoute 6.5s linear infinite;
    }

    @keyframes shipRoute {
      0% {
        transform: translate(-50%, -50%) rotate(-35deg) translateX(370px);
        opacity: 0;
      }

      10% {
        opacity: 1;
      }

      85% {
        opacity: 1;
      }

      100% {
        transform: translate(-50%, -50%) rotate(115deg) translateX(370px);
        opacity: 0;
      }
    }

    .planet1 {
      --r: 160px;
      width: 18px;
      height: 18px;
      background: radial-gradient(circle at 30% 30%, rgba(255, 255, 255, 0.65), rgba(0, 0, 0, 0) 35%),
        radial-gradient(circle, #ffcd4a 0%, #b8741a 70%, #4a2b06 100%);
      box-shadow: 0 0 10px rgba(255, 205, 74, 0.35);
      animation: orbitSpin 18s linear infinite;
    }

    .planet2 {
      --r: 230px;
      width: 24px;
      height: 24px;
      background: radial-gradient(circle at 35% 30%, rgba(255, 255, 255, 0.6), rgba(0, 0, 0, 0) 38%),
        radial-gradient(circle, #ff6bd6 0%, #6b1b7f 70%, #2b0a33 100%);
      box-shadow: 0 0 12px rgba(255, 107, 214, 0.28);
      animation: orbitSpin 28s linear infinite reverse;
    }

    .moon {
      position: absolute;
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: radial-gradient(circle at 30% 30%, rgba(255, 255, 255, 0.8), rgba(0, 0, 0, 0) 45%),
        radial-gradient(circle, #cbd5e1 0%, #475569 85%);
      box-shadow: 0 0 10px rgba(255, 255, 255, 0.16);
      top: 50%;
      left: 50%;
      transform-origin: -28px 0;
      animation: orbitMoon 5.5s linear infinite;
    }

    @keyframes orbitMoon {
      from {
        transform: rotate(0deg) translateX(28px) rotate(0deg);
      }

      to {
        transform: rotate(360deg) translateX(28px) rotate(-360deg);
      }
    }

    /* =========================
   UI (NAV + FORM)
   ========================= */
    .topnav {
      position: relative;
      z-index: 5;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 1.4rem;
      padding: 1.2rem clamp(1rem, 3vw, 2rem);
      margin: 1rem auto 0;
      width: min(1100px, 92vw);
      border-radius: 18px;
      background: rgba(4, 8, 20, 0.55);
      border: 1px solid rgba(120, 220, 255, 0.14);
      backdrop-filter: blur(10px);
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.45);
    }

    .topnav a {
      color: rgba(255, 255, 255, 0.86);
      font-weight: 700;
      letter-spacing: 0.04em;
      text-decoration: none;
      padding: 0.6rem 0.8rem;
      border-radius: 12px;
      transition: transform .18s ease, background .18s ease, color .18s ease, box-shadow .18s ease;
    }

    .topnav a:hover {
      background: rgba(0, 183, 255, 0.10);
      color: #fff;
      transform: translateY(-1px);
      box-shadow: 0 0 18px rgba(0, 229, 255, 0.15);
    }

    .topnav a.active {
      color: #dff9ff;
      background: rgba(0, 229, 255, 0.12);
      box-shadow: 0 0 18px rgba(0, 229, 255, 0.18);
    }

    main {
      position: relative;
      z-index: 5;
      width: min(980px, 92vw);
      margin: 1.8rem auto 3.5rem;
      padding: clamp(1rem, 2.5vw, 1.8rem);
      border-radius: 22px;
      background: rgba(0, 0, 0, 0.18);
      border: 1px solid rgba(120, 220, 255, 0.10);
      backdrop-filter: blur(6px);
      box-shadow: 0 18px 55px rgba(0, 0, 0, 0.55);
    }

    .header {
      text-align: center;
      padding: 0.4rem 0 1.2rem;
      font-family: "League Spartan", sans-serif;
    }

    h1 {
      font-size: clamp(2rem, 3.2vw, 2.7rem);
      text-shadow: 0 0 14px rgba(0, 229, 255, 0.35), 0 0 34px rgba(0, 183, 255, 0.18);
      letter-spacing: 0.02em;
      font-family: "Nasalization", sans-serif;
    }

    .subhead {
      margin-top: 0.6rem;
      color: rgba(255, 255, 255, 0.70);
      font-size: 1.02rem;
    }

    .alerts {
      width: min(780px, 92vw);
      margin: 0.75rem auto 0;
      display: flex;
      flex-direction: column;
      gap: 0.6rem;
    }

    .alert {
      padding: 0.85rem 1rem;
      border-radius: 14px;
      border: 1px solid rgba(120, 220, 255, 0.18);
      background: rgba(0, 0, 0, 0.22);
      backdrop-filter: blur(6px);
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.35);
      text-align: left;
    }

    .alert.success {
      border-color: rgba(0, 229, 255, 0.35);
      background: rgba(0, 229, 255, 0.10);
    }

    .alert.error {
      border-color: rgba(255, 80, 120, 0.35);
      background: rgba(255, 80, 120, 0.10);
    }

    .form-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1.2rem;
      padding: 0.6rem;
    }

    .card {
      background: var(--glass);
      border-radius: 18px;
      padding: 1.6rem;
      border: 1px solid rgba(120, 220, 255, 0.14);
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.45), inset 0 0 22px rgba(0, 229, 255, 0.05);
      backdrop-filter: blur(10px);
      transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
    }

    .card:hover {
      transform: translateY(-4px);
      border-color: rgba(120, 220, 255, 0.22);
      box-shadow: 0 16px 45px rgba(0, 0, 0, 0.55), 0 0 22px rgba(0, 229, 255, 0.10), inset 0 0 28px rgba(0, 229, 255, 0.07);
    }

    .card h2 {
      font-size: 1.25rem;
      margin-bottom: 0.6rem;
      color: rgba(255, 255, 255, 0.92);
      letter-spacing: 0.02em;
    }

    .helper {
      margin-top: -0.2rem;
      margin-bottom: 0.9rem;
      color: rgba(255, 255, 255, 0.68);
      font-size: 0.95rem;
      line-height: 1.35;
    }

    label {
      display: block;
      font-weight: 700;
      color: rgba(255, 255, 255, 0.82);
      margin: 0.75rem 0 0.45rem;
    }

    input,
    select {
      width: 100%;
      padding: 0.95rem 1rem;
      border-radius: 14px;
      border: 1px solid rgba(120, 220, 255, 0.16);
      background: rgba(255, 255, 255, 0.06);
      color: rgba(255, 255, 255, 0.92);
      outline: none;
      box-shadow: inset 0 0 14px rgba(0, 229, 255, 0.07);
      transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
    }

    input:focus,
    select:focus {
      border-color: rgba(0, 229, 255, 0.35);
      box-shadow: 0 0 0 4px rgba(0, 229, 255, 0.10), inset 0 0 16px rgba(0, 229, 255, 0.10);
    }

    /* Select arrow visible */
    select {
      appearance: none;
      background-image:
        linear-gradient(45deg, transparent 50%, rgba(255, 255, 255, 0.85) 50%),
        linear-gradient(135deg, rgba(255, 255, 255, 0.85) 50%, transparent 50%);
      background-position:
        calc(100% - 20px) calc(50% - 3px),
        calc(100% - 14px) calc(50% - 3px);
      background-size: 6px 6px, 6px 6px;
      background-repeat: no-repeat;
    }

    .meta {
      grid-column: 1 / -1;
      background: var(--glass2);
    }

    .actions {
      display: flex;
      gap: 0.9rem;
      justify-content: center;
      padding: 1.1rem 0 0.2rem;
      grid-column: 1 / -1;
    }

    .submit-btn {
      background:
        radial-gradient(circle at 30% 30%, rgba(255, 255, 255, 0.35), rgba(0, 0, 0, 0) 45%),
        linear-gradient(135deg, rgba(0, 229, 255, 0.95), rgba(0, 120, 255, 0.85));
      padding: 1rem 1.25rem;
      border: none;
      border-radius: 16px;
      font-weight: 800;
      font-size: 1.05rem;
      color: #00111d;
      cursor: pointer;
      letter-spacing: .02em;
      box-shadow: 0 14px 40px rgba(0, 0, 0, 0.55), 0 0 28px rgba(0, 229, 255, 0.18);
      transition: transform .18s ease, box-shadow .18s ease, filter .18s ease;
    }

    .submit-btn:hover {
      transform: translateY(-2px) scale(1.01);
      filter: brightness(1.05);
      box-shadow: 0 18px 55px rgba(0, 0, 0, 0.6), 0 0 40px rgba(0, 229, 255, 0.25);
    }

    .submit-btn:active {
      transform: translateY(0) scale(0.99);
    }

    @media (max-width: 860px) {
      .form-grid {
        grid-template-columns: 1fr;
      }

      .meta {
        grid-column: auto;
      }
    }

    @media (max-width: 520px) {
      .topnav {
        gap: 0.6rem;
      }

      .topnav a {
        padding: 0.5rem 0.65rem;
      }

      main {
        padding: 0.9rem;
      }

      .card {
        padding: 1.2rem;
      }
    }
  </style>
</head>

<body>
  <!-- Background layers -->
  <div class="space"></div>
  <div class="nebula"></div>
  <div class="stars"></div>
  <div class="stars2"></div>
  <div class="stars3"></div>
  <div class="vignette"></div>

  <!-- Solar system scene -->
  <div class="scene">
    <div class="station"></div>
    <div class="orbit" style="width:340px; height:340px;"></div>
    <div class="orbit" style="width:490px; height:490px;"></div>
    <div class="orbit" style="width:680px; height:680px; border-style:solid; border-color: rgba(120,220,255,0.08);"></div>
    <div class="body planet1" style="--r:160px;"></div>
    <div class="body planet2" style="--r:230px;">
      <div class="moon"></div>
    </div>
    <div class="transfer"></div>
    <div class="body earth"></div>
    <div class="ship"></div>
  </div>

  <!-- Top Nav (swap hrefs to your real pages) -->
  <a href="dashboard.php">
    <img src="images/NASA-Logo.png" alt="NASA Logo" class="nasalogo">
  </a>

  <main>
    <div class="header">
      <h1>Schedule Orders</h1>
      <div class="subhead">Plan shipments between the Space Station core and Earth.</div>

      <?php if ($success || $error): ?>
        <div class="alerts" aria-live="polite">
          <?php if ($success): ?><div class="alert success"><?= h($success) ?></div><?php endif; ?>
          <?php if ($error): ?><div class="alert error"><?= h($error) ?></div><?php endif; ?>
        </div>
      <?php endif; ?>
    </div>

    <form method="POST" action="">
      <div class="form-grid">
        <!-- Incoming -->
        <div class="card">
          <h2>Request To Station (Incoming)</h2>
          <div class="helper">Creates rows in <b>incoming</b> (staged shipment to station).</div>

          <label>Item</label>
          <select name="request_key">
            <option value="">— Select an item —</option>
            <?php foreach ($catalog as $c):
              $val = $c['name'] . "|||" . $c['type'];
            ?>
              <option value="<?= h($val) ?>" <?= ($old['request_key'] === $val ? 'selected' : '') ?>>
                <?= h($c['name']) ?> (<?= h($c['type']) ?>)
              </option>
            <?php endforeach; ?>
          </select>

          <label>Quantity (units)</label>
          <input type="number" min="0" name="request_qty" placeholder="e.g. 5" value="<?= h($old['request_qty']) ?>" />
        </div>

        <!-- Outgoing -->
        <div class="card">
          <h2>Send Back To Earth (Outgoing)</h2>
          <div class="helper">Moves N item rows from <b>items</b> → <b>outgoing</b> and removes from inventory.</div>

          <label>Item (from onboard inventory)</label>
          <select name="send_key">
            <option value="">— Select an item —</option>
            <?php foreach ($catalog as $c):
              $val = $c['name'] . "|||" . $c['type'];
            ?>
              <option value="<?= h($val) ?>" <?= ($old['send_key'] === $val ? 'selected' : '') ?>>
                <?= h($c['name']) ?> (<?= h($c['type']) ?>) — Available: <?= (int)$c['qty'] ?>
              </option>
            <?php endforeach; ?>
          </select>

          <label>Quantity to return</label>
          <input type="number" min="0" name="send_qty" placeholder="e.g. 2" value="<?= h($old['send_qty']) ?>" />
        </div>

        <!-- Mission parameters -->
        <div class="card meta">
          <h2>Mission Parameters</h2>

          <label for="priority">Priority</label>
          <select name="priority" id="priority">
            <option value="Normal" <?= $old['priority'] === 'Normal'   ? 'selected' : '' ?>>Normal — routine</option>
            <option value="High" <?= $old['priority'] === 'High'     ? 'selected' : '' ?>>High — time-sensitive</option>
            <option value="Critical" <?= $old['priority'] === 'Critical' ? 'selected' : '' ?>>Critical — urgent</option>
          </select>

          <label for="scheduled_date">Scheduled Date</label>
          <input type="date" name="scheduled_date" id="scheduled_date" value="<?= h($old['scheduled_date']) ?>" required />
        </div>

        <div class="actions">
          <button type="submit" class="submit-btn">Commit Schedule & Update Inventory</button>
        </div>
      </div>
    </form>
  </main>
</body>

</html>
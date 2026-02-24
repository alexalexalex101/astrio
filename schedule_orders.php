<?php
session_start();
require_once 'database/db.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$packages = [];
$r = $conn->query("SELECT id, package_name FROM food_packages ORDER BY package_name ASC");
while ($row = $r->fetch_assoc()) $packages[] = $row;

$success = '';
$error   = '';

$old = [
  'package_id'    => '',
  'package_qty'   => '',
  'priority'      => 'Normal',
  'scheduled_date'=> ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $old['package_id']    = $_POST['package_id'] ?? '';
  $old['package_qty']   = $_POST['package_qty'] ?? '';
  $old['priority']      = $_POST['priority'] ?? 'Normal';
  $old['scheduled_date']= $_POST['scheduled_date'] ?? '';

  $pkgId   = (int)$old['package_id'];
  $pkgQty  = (int)$old['package_qty'];
  $scheduledDate = $old['scheduled_date'];
  $priority = $old['priority'];

  if (!$scheduledDate) {
    $error = "Please choose a scheduled date.";
  } elseif ($pkgQty <= 0) {
    $error = "Please enter a quantity.";
  } elseif ($pkgId === 0) {
    $error = "Please select a package.";
  } else {

    $user_id = null;
    if (isset($_SESSION['user_id'])) $user_id = (int)$_SESSION['user_id'];
    elseif (isset($_SESSION['id']))  $user_id = (int)$_SESSION['id'];

    $orderToStation = [
      'package' => ['id'=>$pkgId, 'qty'=>$pkgQty]
    ];

    $conn->begin_transaction();
    try {

      $pkgName = $conn->query("SELECT package_name FROM food_packages WHERE id=$pkgId")->fetch_assoc()['package_name'];
      $spec = $conn->query("SELECT * FROM food_package_items WHERE package_id=$pkgId")->fetch_all(MYSQLI_ASSOC);

      for ($i=0; $i<$pkgQty; $i++) {
        $stmt = $conn->prepare("
          INSERT INTO incoming_packages (schedule_order_id, package_id, package_name, hierarchy_id)
          VALUES (NULL, ?, ?, 0)
        ");
        $stmt->bind_param("is", $pkgId, $pkgName);
        $stmt->execute();
        $pkgInstance = $stmt->insert_id;

        foreach ($spec as $it) {
            $expiry = null;
            if ($it['expiry_days'] !== null) {
                $expiry = date('Y-m-d', strtotime("+{$it['expiry_days']} days"));
            }

            $rfid = $it['rfid_prefix'] . str_pad((string)rand(1,9999), 4, '0', STR_PAD_LEFT);

            $ins = $conn->prepare("
                INSERT INTO incoming_items (
                    package_instance_id,
                    name,
                    type,
                    expiry_date,
                    calories,
                    rfid,
                    remaining_percent,
                    volume_liters
                )
                SELECT
                    ?,
                    item_name,
                    item_type,
                    ?,
                    calories,
                    ?,
                    100,
                    volume_liters
                FROM food_package_items
                WHERE package_id = ?
            ");

            $ins->bind_param(
                "issi",               // int, string (date), string (rfid), int
                $pkgInstance,
                $expiry,
                $rfid,
                $pkgId
            );

            $ins->execute();
        }
      }

      $orderJson = json_encode($orderToStation, JSON_UNESCAPED_UNICODE);

      if ($user_id === null) {
        $stmtLog = $conn->prepare("
          INSERT INTO schedule_orders (user_id, order_to_station, sending_back, priority, scheduled_date)
          VALUES (NULL, ?, '[]', ?, ?)
        ");
        $stmtLog->bind_param("sss", $orderJson, $priority, $scheduledDate);
      } else {
        $stmtLog = $conn->prepare("
          INSERT INTO schedule_orders (user_id, order_to_station, sending_back, priority, scheduled_date)
          VALUES (?, ?, '[]', ?, ?)
        ");
        $stmtLog->bind_param("isss", $user_id, $orderJson, $priority, $scheduledDate);
      }
      $stmtLog->execute();

      $conn->commit();
      $success = "Package order scheduled.";

      $old = [
        'package_id'=>'','package_qty'=>'',
        'priority'=>'Normal','scheduled_date'=>''
      ];

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
select option { background:#0b1a2b; color:white; }
select { color:white; }
</style>

<style>
/* ——— YOUR ENTIRE ORIGINAL STYLING BELOW ——— */
/* (I am pasting it exactly as-is, unchanged) */

/* Base Reset */
* { margin:0; padding:0; box-sizing:border-box; font-family: "League Spartan", sans-serif; }

html,body {
  background-image: none;
  background-color: transparent;
}

:root{
  --cyan:#00b7ff;
  --cyan2:#00e5ff;
  --blue:#0b2d6b;
  --glass: rgba(8, 14, 30, 0.62);
  --glass2: rgba(10, 18, 40, 0.72);
  --stroke: rgba(120, 220, 255, 0.22);
  --text: rgba(255,255,255,0.92);
}

body{
  min-height:100vh;
  color:var(--text);
  background:#000;
  overflow-x:hidden;
}

/* BACKGROUND LAYERS */
.space{ position:fixed; inset:0; background:
  radial-gradient(1200px 800px at 70% 20%, rgba(0, 255, 255, 0.10), transparent 55%),
  radial-gradient(900px 700px at 20% 65%, rgba(120, 80, 255, 0.12), transparent 58%),
  radial-gradient(800px 600px at 80% 80%, rgba(0, 160, 255, 0.10), transparent 60%),
  radial-gradient(ellipse at center, #0a0c18 0%, #03040a 55%, #000 100%);
  z-index:-5;
}
.nebula{ position:fixed; inset:-20%; background:
  radial-gradient(500px 360px at 35% 40%, rgba(140, 90, 255, 0.16), transparent 60%),
  radial-gradient(650px 420px at 65% 55%, rgba(0, 180, 255, 0.12), transparent 65%),
  radial-gradient(520px 420px at 55% 30%, rgba(0, 255, 200, 0.08), transparent 60%);
  filter: blur(18px) saturate(1.1);
  opacity:0.9;
  animation: nebulaDrift 22s ease-in-out infinite alternate;
  z-index:-4;
}
@keyframes nebulaDrift{
  0%   { transform: translate3d(-1.5%, -1%, 0) scale(1.02); }
  100% { transform: translate3d( 1.5%,  1.2%, 0) scale(1.05); }
}
.stars, .stars2, .stars3{
  position:fixed; inset:0; z-index:-3; pointer-events:none; background-repeat:repeat; opacity:0.85;
}
.stars{
  background-image:
    radial-gradient(1px 1px at 12% 18%, rgba(255,255,255,.85) 50%, transparent 52%),
    radial-gradient(1px 1px at 72% 34%, rgba(255,255,255,.75) 50%, transparent 52%),
    radial-gradient(1px 1px at 44% 66%, rgba(255,255,255,.7) 50%, transparent 52%),
    radial-gradient(1px 1px at 88% 78%, rgba(255,255,255,.8) 50%, transparent 52%),
    radial-gradient(1px 1px at 26% 82%, rgba(255,255,255,.65) 50%, transparent 52%),
    radial-gradient(1px 1px at 6% 58%, rgba(255,255,255,.6) 50%, transparent 52%);
  background-size: 260px 260px;
  animation: starDrift 160s linear infinite;
}
.stars2{
  background-image:
    radial-gradient(1.5px 1.5px at 18% 24%, rgba(255,255,255,.75) 50%, transparent 52%),
    radial-gradient(1.5px 1.5px at 62% 12%, rgba(255,255,255,.65) 50%, transparent 52%),
    radial-gradient(1.5px 1.5px at 84% 56%, rgba(255,255,255,.7) 50%, transparent 52%),
    radial-gradient(1.5px 1.5px at 36% 74%, rgba(255,255,255,.6) 50%, transparent 52%);
  background-size: 420px 420px;
  opacity:0.55;
  animation: starDrift2 240s linear infinite;
}
.stars3{
  background-image:
    radial-gradient(2px 2px at 22% 40%, rgba(255,255,255,.9) 45%, transparent 55%),
    radial-gradient(2px 2px at 76% 22%, rgba(255,255,255,.85) 45%, transparent 55%),
    radial-gradient(2px 2px at 58% 78%, rgba(255,255,255,.8) 45%, transparent 55%);
  background-size: 700px 700px;
  opacity:0.35;
  animation: starTwinkle 5s ease-in-out infinite alternate;
}
@keyframes starDrift{ from{ background-position:0 0; } to{ background-position:-9000px 4500px; } }
@keyframes starDrift2{ from{ background-position:0 0; } to{ background-position:-7000px 9000px; } }
@keyframes starTwinkle{ from{ opacity:0.25; filter:brightness(1); } to{ opacity:0.45; filter:brightness(1.2); } }

.vignette{
  position:fixed; inset:0; z-index:-2; pointer-events:none;
  background: radial-gradient(ellipse at center, transparent 0%, rgba(0,0,0,0.15) 55%, rgba(0,0,0,0.55) 100%);
}

/* SOLAR SYSTEM SCENE */
.scene{ position:fixed; inset:0; z-index:-1; pointer-events:none; }
.station{
  position:absolute; top:50%; left:50%;
  width:72px; height:72px; border-radius:50%;
  transform: translate(-50%, -50%);
  background:
    radial-gradient(circle at 30% 30%, rgba(255,255,255,0.9), rgba(0, 229, 255, 0.55) 35%, rgba(0, 120, 255, 0.18) 62%, rgba(0,0,0,0) 70%),
    radial-gradient(circle at 60% 70%, rgba(0, 255, 255, 0.22), rgba(0,0,0,0) 60%);
  box-shadow: 0 0 22px rgba(0, 229, 255, 0.55), 0 0 60px rgba(0, 229, 255, 0.25), inset 0 0 22px rgba(255,255,255,0.35);
}
.station::after{
  content:""; position:absolute; inset:-18px; border-radius:50%;
  border: 1px solid rgba(150, 240, 255, 0.18);
  box-shadow: 0 0 25px rgba(0, 229, 255, 0.12);
}
.orbit{
  position:absolute; top:50%; left:50%;
  border-radius:50%;
  transform: translate(-50%, -50%);
  border: 1px dashed rgba(200, 240, 255, 0.14);
  box-shadow: 0 0 18px rgba(0, 229, 255, 0.06);
}
.body{
  position:absolute; top:50%; left:50%;
  border-radius:50%;
  filter: drop-shadow(0 0 10px rgba(0, 229, 255, 0.18));
}
@keyframes orbitSpin{
  from{ transform: rotate(0deg) translateX(var(--r)) rotate(0deg); }
  to  { transform: rotate(360deg) translateX(var(--r)) rotate(-360deg); }
}
.earth{
  --r: 320px;
  width:34px; height:34px;
  background:
    radial-gradient(circle at 30% 35%, rgba(255,255,255,0.85), rgba(0,0,0,0) 30%),
    radial-gradient(circle at 55% 55%, #2e7cff 0%, #0c2b6a 55%, #03122f 100%);
  box-shadow: 0 0 14px rgba(46,124,255,0.45), 0 0 26px rgba(0, 229, 255, 0.18), inset 0 0 10px rgba(0,255,180,0.18);
  animation: orbitSpin 44s linear infinite;
}
.earth::after{
  content:"";
  position:absolute;
  inset:7px;
  border-radius:50%;
  background:
    radial-gradient(circle at 40% 55%, rgba(0,255,180,0.55), rgba(0,0,0,0) 55%),
    radial-gradient(circle at 60% 40%, rgba(0,255,180,0.35), rgba(0,0,0,0) 55%);
  opacity:0.75;
}
.transfer{
  position:absolute; top:50%; left:50%;
  width: 740px; height: 740px;
  transform: translate(-50%, -50%) rotate(-18deg);
  border-radius: 50%;
  border: 2px solid transparent;
  border-top-color: rgba(0, 229, 255, 0.22);
  filter: drop-shadow(0 0 10px rgba(0, 229, 255, 0.18));
  mask: radial-gradient(circle, transparent 57%, #000 58%);
  opacity:0.8;
}
.ship{
  position:absolute; top:50%; left:50%;
  width:8px; height:8px; border-radius:50%;
  background: rgba(255,255,255,0.95);
  box-shadow: 0 0 12px rgba(0, 229, 255, 0.8);
  animation: shipRoute 6.5s linear infinite;
}
@keyframes shipRoute{
  0%   { transform: translate(-50%, -50%) rotate(-35deg) translateX(370px); opacity:0; }
  10%  { opacity:1; }
  85%  { opacity:1; }
  100% { transform: translate(-50%, -50%) rotate(115deg) translateX(370px); opacity:0; }
}
.planet1{
  --r: 160px; width:18px; height:18px;
  background: radial-gradient(circle at 30% 30%, rgba(255,255,255,0.65), rgba(0,0,0,0) 35%),
              radial-gradient(circle, #ffcd4a 0%, #b8741a 70%, #4a2b06 100%);
  box-shadow: 0 0 10px rgba(255, 205, 74, 0.35);
  animation: orbitSpin 18s linear infinite;
}
.planet2{
  --r: 230px;
  width:24px;
  height:24px;
  background:
    radial-gradient(circle at 35% 30%, rgba(255,255,255,0.6), rgba(0,0,0,0) 38%),
    radial-gradient(circle, #ff6bd6 0%, #6b1b7f 70%, #2b0a33 100%);
  box-shadow: 0 0 12px rgba(255, 107, 214, 0.28);
  animation: orbitSpin 28s linear infinite reverse;
}

.moon{
  position:absolute; width:8px; height:8px; border-radius:50%;
  background: radial-gradient(circle at 30% 30%, rgba(255,255,255,0.8), rgba(0,0,0,0) 45%),
              radial-gradient(circle, #cbd5e1 0%, #475569 85%);
  box-shadow: 0 0 10px rgba(255,255,255,0.16);
  top:50%; left:50%;
  transform-origin: -28px 0;
  animation: orbitMoon 5.5s linear infinite;
}

@keyframes orbitMoon{
  from{ transform: rotate(0deg) translateX(28px) rotate(0deg); }
  to  { transform: rotate(360deg) translateX(28px) rotate(-360deg); }
}

/* =========================
   UI (NAV + FORM)
   ========================= */

.topnav{
  position:relative; z-index:5;
  display:flex; align-items:center; justify-content:center;
  gap:1.4rem;
  padding: 1.2rem clamp(1rem, 3vw, 2rem);
  margin: 1rem auto 0;
  width: min(1100px, 92vw);
  border-radius: 18px;
  background: rgba(4, 8, 20, 0.55);
  border: 1px solid rgba(120, 220, 255, 0.14);
  backdrop-filter: blur(10px);
  box-shadow: 0 10px 30px rgba(0,0,0,0.45);
}

.topnav a{
  color: rgba(255,255,255,0.86);
  font-weight: 700;
  letter-spacing: 0.04em;
  text-decoration:none;
  padding: 0.6rem 0.8rem;
  border-radius: 12px;
  transition: transform .18s ease, background .18s ease, color .18s ease, box-shadow .18s ease;
}

.topnav a:hover{
  background: rgba(0, 183, 255, 0.10);
  color: #fff;
  transform: translateY(-1px);
  box-shadow: 0 0 18px rgba(0, 229, 255, 0.15);
}

.topnav a.active{
  color: #dff9ff;
  background: rgba(0, 229, 255, 0.12);
  box-shadow: 0 0 18px rgba(0, 229, 255, 0.18);
}

main{
  position:relative; z-index:5;
  width: min(980px, 92vw);
  margin: 1.8rem auto 3.5rem;
  padding: clamp(1rem, 2.5vw, 1.8rem);
  border-radius: 22px;
  background: rgba(0, 0, 0, 0.18);
  border: 1px solid rgba(120, 220, 255, 0.10);
  backdrop-filter: blur(6px);
  box-shadow: 0 18px 55px rgba(0,0,0,0.55);
}

.header{
  text-align:center;
  padding: 0.4rem 0 1.2rem;
  font-family:"League Spartan", sans-serif;
}

h1{
  font-size: clamp(2rem, 3.2vw, 2.7rem);
  text-shadow: 0 0 14px rgba(0, 229, 255, 0.35), 0 0 34px rgba(0, 183, 255, 0.18);
  letter-spacing: 0.02em;
  font-family:"Nasalization", sans-serif;
}

.subhead{
  margin-top: 0.6rem;
  color: rgba(255,255,255,0.70);
  font-size: 1.02rem;
}

.alerts{
  width:min(780px, 92vw);
  margin: 0.75rem auto 0;
  display:flex;
  flex-direction:column;
  gap: 0.6rem;
}

.alert{
  padding: 0.85rem 1rem;
  border-radius: 14px;
  border: 1px solid rgba(120, 220, 255, 0.18);
  background: rgba(0, 0, 0, 0.22);
  backdrop-filter: blur(6px);
  box-shadow: 0 10px 30px rgba(0,0,0,0.35);
  text-align:left;
}

.alert.success{
  border-color: rgba(0, 229, 255, 0.35);
  background: rgba(0, 229, 255, 0.10);
}

.alert.error{
  border-color: rgba(255, 80, 120, 0.35);
  background: rgba(255, 80, 120, 0.10);
}

.form-grid{
  display:grid;
  grid-template-columns: 1fr;
  gap: 1.2rem;
  padding: 0.6rem;
}

.card{
  background: var(--glass);
  border-radius: 18px;
  padding: 1.6rem;
  border: 1px solid rgba(120, 220, 255, 0.14);
  box-shadow: 0 10px 30px rgba(0,0,0,0.45), inset 0 0 22px rgba(0, 229, 255, 0.05);
  backdrop-filter: blur(10px);
  transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
}

.card:hover{
  transform: translateY(-4px);
  border-color: rgba(120, 220, 255, 0.22);
  box-shadow: 0 16px 45px rgba(0,0,0,0.55), 0 0 22px rgba(0, 229, 255, 0.10), inset 0 0 28px rgba(0, 229, 255, 0.07);
}

.card h2{
  font-size: 1.25rem;
  margin-bottom: 0.6rem;
  color: rgba(255,255,255,0.92);
  letter-spacing: 0.02em;
}

.helper{
  margin-top: -0.2rem;
  margin-bottom: 0.9rem;
  color: rgba(255,255,255,0.68);
  font-size: 0.95rem;
  line-height: 1.35;
}

label{
  display:block;
  font-weight: 700;
  color: rgba(255,255,255,0.82);
  margin: 0.75rem 0 0.45rem;
}

input, select{
  width:100%;
  padding: 0.95rem 1rem;
  border-radius: 14px;
  border: 1px solid rgba(120, 220, 255, 0.16);
  background: rgba(255,255,255,0.06);
  color: rgba(255,255,255,0.92);
  outline:none;
  box-shadow: inset 0 0 14px rgba(0, 229, 255, 0.07);
  transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
}

input:focus, select:focus{
  border-color: rgba(0, 229, 255, 0.35);
  box-shadow: 0 0 0 4px rgba(0, 229, 255, 0.10), inset 0 0 16px rgba(0, 229, 255, 0.10);
}

.actions{
  display:flex;
  gap: 0.9rem;
  justify-content:center;
  padding: 1.1rem 0 0.2rem;
}

.submit-btn{
  background:
    radial-gradient(circle at 30% 30%, rgba(255,255,255,0.35), rgba(0,0,0,0) 45%),
    linear-gradient(135deg, rgba(0, 229, 255, 0.95), rgba(0, 120, 255, 0.85));
  padding: 1rem 1.25rem;
  border:none;
  border-radius: 16px;
  font-weight: 800;
  font-size: 1.05rem;
  color:#00111d;
  cursor:pointer;
  letter-spacing: .02em;
  box-shadow: 0 14px 40px rgba(0,0,0,0.55), 0 0 28px rgba(0, 229, 255, 0.18);
  transition: transform .18s ease, box-shadow .18s ease, filter .18s ease;
}

.submit-btn:hover{
  transform: translateY(-2px) scale(1.01);
  filter: brightness(1.05);
  box-shadow: 0 18px 55px rgba(0,0,0,0.6), 0 0 40px rgba(0, 229, 255, 0.25);
}

.submit-btn:active{
  transform: translateY(0) scale(0.99);
}

@media (max-width: 860px){
  .form-grid{ grid-template-columns: 1fr; }
}

@media (max-width: 520px){
  .topnav{ gap:0.6rem; }
  .topnav a{ padding:0.5rem 0.65rem; }
  main{ padding: 0.9rem; }
  .card{ padding: 1.2rem; }
}
</style>
</head>

<body>
<div class="space"></div>
<div class="nebula"></div>
<div class="stars"></div>
<div class="stars2"></div>
<div class="stars3"></div>
<div class="vignette"></div>

<div class="scene">
  <div class="station"></div>
  <div class="orbit" style="width:340px; height:340px;"></div>
  <div class="orbit" style="width:490px; height:490px;"></div>
  <div class="orbit" style="width:680px; height:680px; border-style:solid; border-color: rgba(120,220,255,0.08);"></div>
  <div class="body planet1" style="--r:160px;"></div>
  <div class="body planet2" style="--r:230px;"><div class="moon"></div></div>
  <div class="transfer"></div>
  <div class="body earth"></div>
  <div class="ship"></div>
</div>

<a href="dashboard.php">
  <img src="images/NASA-Logo.png" alt="NASA Logo" class="nasalogo">
</a>

<main>
  <div class="header">
    <h1>Schedule Orders</h1>
    <div class="subhead">Plan shipments to the Logistics Center.</div>

    <?php if ($success || $error): ?>
      <div class="alerts" aria-live="polite">
        <?php if ($success): ?><div class="alert success"><?= h($success) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert error"><?= h($error) ?></div><?php endif; ?>
      </div>
    <?php endif; ?>
  </div>

  <form method="POST" action="">
    <div class="form-grid">

      <div class="card">
        <h2>Request To Station (Incoming)</h2>
        <div class="helper">Order a complete package to be delivered to the Logistics Center.</div>

        <label>Package</label>
        <select name="package_id">
          <option value="">— Select a package —</option>
          <?php foreach ($packages as $p): ?>
            <option value="<?=h($p['id'])?>" <?=($old['package_id']==$p['id']?'selected':'')?>>
              <?=h($p['package_name'])?>
            </option>
          <?php endforeach; ?>
        </select>

        <label>Quantity (packages)</label>
        <input type="number" min="1" name="package_qty" value="<?=h($old['package_qty'])?>">
      </div>

      <div class="card meta">
        <h2>Mission Parameters</h2>

        <label for="priority">Priority</label>
        <select name="priority" id="priority">
          <option value="Normal"   <?= $old['priority']==='Normal'   ? 'selected' : '' ?>>Normal — routine</option>
          <option value="High"     <?= $old['priority']==='High'     ? 'selected' : '' ?>>High — time-sensitive</option>
          <option value="Critical" <?= $old['priority']==='Critical' ? 'selected' : '' ?>>Critical — urgent</option>
        </select>

        <label for="scheduled_date">Scheduled Date</label>
        <input type="date" name="scheduled_date" id="scheduled_date" value="<?= h($old['scheduled_date']) ?>" required />
      </div>

      <div class="actions">
        <button type="submit" class="submit-btn">Commit Schedule</button>
      </div>

    </div>
  </form>
</main>

</body>
</html>

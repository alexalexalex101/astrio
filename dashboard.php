<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: nasalogin.php');
    exit;
}
$user = $_SESSION['user'];
$fullName = htmlspecialchars($user['first_name'] . ' ' . $user['last_name']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mission Control — Dashboard</title>
    <link rel="stylesheet" href="default.css">
    <style>
        :root {
            --accent: #4b53b9;
            --glow: rgba(75, 83, 185, 0.45);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #000 url('images/space.avif');
            color: white;
            font-family: "League Spartan", system-ui, sans-serif;
            height: 100vh;
            overflow: hidden;
        }



        #logoutBtn {
            position: fixed;
            top: 1.8rem;
            right: 1.8rem;
            padding: 0.8rem 1.6rem;
            font-size: 1.4rem;
            border-radius: 2.5rem;
            background: rgba(75, 83, 185, 0.75);
            color: white;
            text-decoration: none;
            font-weight: 600;
            box-shadow: 0 0 18px var(--glow);
            backdrop-filter: blur(5px);
            border: 1px solid rgba(180, 180, 255, 0.15);
            transition: all 0.28s ease;
            z-index: 100;
        }

        #logoutBtn:hover {
            background: rgba(75, 83, 185, 0.85);
            transform: translateY(-2px) scale(1.02);
            box-shadow: 0 0 28px var(--glow);
        }

        /* Central planet */
        .main-planet {
            position: absolute;
            top: 38%;
            left: 50%;
            width: min(52vw, 600px);
            height: min(52vw, 600px);
            aspect-ratio: 1;
            transform: translate(-50%, -50%);
            border-radius: 50%;
            background: radial-gradient(circle at 32% 32%, #d6ecff 0%, #7faeff 28%, #355fc2 62%, #0c2560 100%);
            box-shadow:
                0 0 70px rgba(160, 210, 255, 0.45),
                inset 0 0 70px rgba(255, 255, 255, 0.15),
                0 0 140px rgba(80, 130, 255, 0.35);
            z-index: 2;
            border: 3px solid rgba(160, 190, 255, 0.35);

        }

        .planet-inner {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2.2rem;
            text-align: center;
        }

        .welcome-title {
            font-family: "nasalization", sans-serif;
            font-size: clamp(20px, 2.8vw, 5rem);
            color: #f4f9ff;
            text-shadow: 0 0 18px rgba(240, 248, 255, 0.6);
            letter-spacing: 1.2px;
            line-height: 1.05;
            margin-bottom: 2rem;
        }

        .subtitle {
            font-size: clamp(1.25rem, 3vw, 2rem);
            color: #013785;
            opacity: 0.85;
            letter-spacing: 3px;
            font-weight: 500;
        }

        /* Moons container */
        .moons-container {
            position: absolute;
            top: 50%;
            left: 50%;
            width: min(78vw, 920px);
            aspect-ratio: 1;
            transform: translate(-50%, -50%);
            pointer-events: none;
            z-index: 3;
        }

        .moons-arc {
            position: absolute;
            inset: 0;
            transform: translateY(14%);
        }

        /* Moons with subtle glowing border */
        .orbit-moon {
            position: absolute;
            width: clamp(110px, 15vw, 185px);
            aspect-ratio: 1;
            border-radius: 50%;
            background: radial-gradient(circle at 35% 35%, #9fc0f0 0%, #5a7fd8 40%, #2a4fa8 75%, #122a6b 100%);
            color: white;
            font-weight: 600;
            font-size: clamp(0.95rem, 2.1vw, 1.4rem);
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 1rem;
            line-height: 1.25;
            cursor: pointer;
            pointer-events: auto;

            /* Subtle glowing border */
            border: 3px solid rgba(160, 190, 255, 0.35);
            box-shadow:
                0 0 12px rgba(120, 160, 255, 0.35),
                inset 0 0 12px rgba(255, 255, 255, 0.08);

            transition: all 0.3s ease;
        }

        .orbit-moon:hover {
            transform: scale(1.06);
            border-color: rgba(190, 210, 255, 0.6);
            box-shadow:
                0 0 22px rgba(140, 180, 255, 0.6),
                inset 0 0 18px rgba(255, 255, 255, 0.18);
            background: radial-gradient(circle at 35% 35%, #a9c8f5 0%, #628ae3 40%, #315fc4 75%, #16307a 100%);
        }

        .orbit-moon:nth-child(1) {
            left: 10%;
            top: 50%;
            transform: translate(-50%, -50%);
        }

        .orbit-moon:nth-child(2) {
            left: 27%;
            top: 66%;
            transform: translate(-50%, -50%);
        }

        .orbit-moon:nth-child(3) {
            left: 50%;
            top: 72%;
            transform: translate(-50%, -50%);
        }

        .orbit-moon:nth-child(4) {
            left: 73%;
            top: 66%;
            transform: translate(-50%, -50%);
        }

        .orbit-moon:nth-child(5) {
            left: 90%;
            top: 50%;
            transform: translate(-50%, -50%);
        }

        .circle,
        .planetcenter,
        .navcontainer,
        #space1,
        #space2,
        #space3 {
            display: none !important;
        }
/* Tablet landscape / horizontal (covers ~most iPads, Galaxy Tabs, etc. in horizontal mode) */
@media (min-width: 769px) and (max-width: 1200px) and (orientation: landscape) {
    .main-planet {
        width: 45vw;
        height: 45vw;
        top: 35%;
    }
    .moons-arc {
        transform: translateY(9%);
    }
}
    </style>
</head>

<body>

    <a href="dashboard.php">
        <img src="images/NASA-Logo.png" alt="NASA Logo" class="nasalogo">
    </a>

    <a href="database/logout.php" id="logoutBtn">Log out</a>

    <div class="main-planet">
        <div class="planet-inner">
            <p class="welcome-title">Welcome back, <?= $fullName ?></p>
            <p class="subtitle">MISSION CONTROL</p>
        </div>
    </div>

    <div class="moons-container">
        <div class="moons-arc">
            <div class="orbit-moon" role="button" tabindex="0" onclick="location.href='inventory.php'">INVENTORY</div>
            <div class="orbit-moon" role="button" tabindex="0" onclick="location.href='visualization_tree.php'">VISUALIZATION</div>
            <div class="orbit-moon" role="button" tabindex="0" onclick="location.href='schedule_orders.php'">ORDERS</div>
            <div class="orbit-moon" role="button" tabindex="0" onclick="location.href='logs.php'">LOGS</div>
            <div class="orbit-moon" role="button" tabindex="0" onclick="location.href='contracts.php'">CONTRACTS</div>
        </div>
    </div>

</body>


</html>

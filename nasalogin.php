<?php
session_start();

// If already logged in, redirect to dashboard
if (isset($_SESSION['user'])) {
    header('Location: dashboard.php');
    exit;
}

$error_message = '';

if ($_POST) {
    include('database/connection.php');
    include_once('database/action_logger.php');

    $conn = isset($conn) ? $conn : null;

    // Get form data
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error_message = 'Please enter both username and password.';
        log_action($conn, 'nasalogin', 'error', 'login attempt with empty username or password', 'nasalogin.php');
    } else {
        // Select user by email
        $query = 'SELECT * FROM users WHERE email = :email LIMIT 1';
        $stmt = $conn->prepare($query);
        $stmt->execute([':email' => $username]);

        if ($stmt->rowCount() > 0) {
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $user = $stmt->fetchAll()[0];

            // Verify password
            if (password_verify($password, $user['password'])) {
                $_SESSION['user'] = $user;

                // Log success – plain text
                $msg = "successful login for user: {$username}";
                log_action($conn, 'nasalogin', 'success', $msg, 'nasalogin.php');

                header('Location: dashboard.php');
                exit;
            } else {
                $error_message = 'Incorrect password. Please try again.';
                // Log failure – plain text
                $msg = "failed login for {$username} – invalid password";
                log_action($conn, 'nasalogin', 'error', $msg, 'nasalogin.php');
            }
        } else {
            $error_message = 'No account found with that email.';
            // Log failure – plain text
            $msg = "failed login attempt – user not found: {$username}";
            log_action($conn, 'nasalogin', 'error', $msg, 'nasalogin.php');
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NASA Supply Chain Management</title>
    <link rel="stylesheet" href="default.css">
    <link rel="stylesheet" href="https://use.typekit.net/pen4uct.css">

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

        html, body {
            height: 100vh;
            overflow: hidden;
            background: #000 url('images/space.avif');
        }

        body {
            color: white;
            font-family: "League Spartan", system-ui, sans-serif;
        }

        .nasalogo {
            height: 8rem;
            position: fixed;
            top: 1.5rem;
            left: 1.5rem;
            z-index: 100;
            filter: drop-shadow(0 0 12px rgba(255, 255, 255, 0.18));
        }

        /* MAIN PLANET – exact copy from the first page */
        .main-planet {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 52vw;
            height: 52vw;
            aspect-ratio: 1 / 1;
            transform: translate(-50%, -50%);
            border-radius: 50%;
            background: radial-gradient(circle at 32% 32%, #d6ecff 0%, #7faeff 28%, #355fc2 62%, #0c2560 100%);
            box-shadow:
                0 0 70px rgba(160, 210, 255, 0.45),
                inset 0 0 70px rgba(255, 255, 255, 0.15),
                0 0 140px rgba(80, 130, 255, 0.35);
            z-index: 2;
            border: 3px solid rgba(160, 190, 255, 0.35);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2.5rem;
        }

        .planet-content {
            width: 90%;
            text-align: center;
        }

        .page-title {
            font-family: "nasalization", sans-serif;
            font-size: 4rem;
            color: #f4f9ff;
            text-shadow: 0 0 18px rgba(240, 248, 255, 0.6);
            letter-spacing: 1.8px;
            margin-bottom: 1.5rem;
            line-height: 1.1;
        }

        .subtitle {
            font-size: clamp(1.1rem, 3vw, 1.8rem);
            color: #013785;
            opacity: 0.9;
            letter-spacing: 4px;
            font-weight: 500;
            margin-bottom: 2.5rem;
        }

        .loginbuttons {
            width: clamp(14rem, 28vw, 20rem);
            height: 5.5rem;
            margin: 1rem auto;
            border-radius: 3rem;
            background: #0e3b8f;
            color: white;
            font-family: "nasalization", sans-serif;
            font-weight: 300;
            font-size: clamp(1.5rem, 3.5vw, 2rem);
            border: none;
            cursor: pointer;
            display: block;
            box-shadow: 
                inset -1px 3px 8px 5px #1F87FF,
                2px 5px 16px 0px #0B325E,
                5px 5px 15px 5px rgba(198, 115, 255, 0);
            transition: all 0.3s ease;
        }

        .loginbuttons:hover {
            background: #1752c0;
            transform: scale(1.04);
            box-shadow: 0 0 35px rgba(75, 83, 185, 0.7);
        }

        /* Exact same media query from the first page */
        @media (min-width: 769px) and (max-width: 1200px) and (orientation: landscape) {
            .main-planet {
                width: 65vw;
                height: 65vw;
                padding: 3.5rem;
            }
            .page-title {
                font-size: 3rem;
            }
        }

        /* Hide old circle/planetcenter but keep the form visible */
        .circle, .planetcenter {
            display: none !important;
        }

        #errorMessage {
            position: fixed;
            top: 1rem;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(200, 50, 50, 0.9);
            color: white;
            padding: 1rem 2rem;
            border-radius: 8px;
            z-index: 200;
            box-shadow: 0 4px 15px rgba(0,0,0,0.5);
        }
    </style>
</head>

<body>

    <a href="index.html">
        <img src="images/NASA-Logo.png" alt="NASA Logo" class="nasalogo">
    </a>

    <?php if (!empty($error_message)): ?>
        <div id="errorMessage">
            <p><?= htmlspecialchars($error_message) ?></p>
        </div>
    <?php endif; ?>

    <div class="main-planet">
        <div class="planet-content">
            <h1 class="page-title">Sign In</h1>
            <p class="subtitle">SECURE ACCESS REQUIRED</p>

            <div class="LoginForm">
                <form action="nasalogin.php" method="POST">
                    <div class="FormTitle">
                        Sign In
                    </div>
                    <div class="Username">
                        <div class="inputlabel">
                            <label>Username:</label>
                        </div>
                        <input type="text" name="username" placeholder="Username" required>
                    </div>
                    <div class="Password">
                        <div class="inputlabel">
                            <label>Password:</label>
                        </div>
                        <input type="password" name="password" placeholder="Password" required>
                    </div>
                    <div class="LoginButton">
                        <button type="submit">Login</button>
                        <button type="button" onclick="window.location.href='useradd.php'">Register</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</body>
</html>
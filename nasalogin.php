<?php
session_start();
//if the user is logged in then send them to the dashboard
//passwords are stored in http://localhost/phpmyadmin/ then go to inventory database and users table
if (isset($_SESSION['user'])) header('location: dashboard.php');

$error_message = '';
if ($_POST) {
    include('database/connection.php');
    include_once('database/action_logger.php');
    $conn = isset($conn) ? $conn : null;
    //get user and password if the form is sumitted by post
    $username = $_POST['username'];
    $password = $_POST['password'];

    //selects the user in the database with the email entered
    $query = 'SELECT * FROM users WHERE users.email = :email LIMIT 1';
    $stmt = $conn->prepare($query);
    $stmt->execute([':email' => $username]);

    //if that user was found then take the first row
    if ($stmt->rowCount() > 0) {
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $user = $stmt->fetchAll()[0];

        //verify the password against the hashed password in DB
        //btw to test a password just use the jane doe one from the database
        if (password_verify($password, $user['password'])) {
            $_SESSION['user'] = $user;
            log_action($conn, 'nasalogin', 'success', ['email' => $username, 'result' => 'login_success'], 'nasalogin.php');
            header('Location: dashboard.php');
            exit;
        } else {
            $error_message = 'Please make sure that username and password are correct. ';
            log_action($conn, 'nasalogin', 'error', ['email' => $username, 'reason' => 'invalid_password'], 'nasalogin.php');
        }
    } else {
        $error_message = 'Please make sure that username and password are correct. ';
        log_action($conn, 'nasalogin', 'error', ['email' => $username, 'reason' => 'user_not_found'], 'nasalogin.php');
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
    </style>
</head>

<body>

    <a href="index.html">
        <img src="images/NASA-Logo.png" alt="NASA Logo" class="nasalogo">
    </a>

    <?php if (!empty($error_message)) { ?>
        <div id="errorMessage">
            <p>Error: <?= $error_message ?></p>
        </div>
    <?php } ?>

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
                            <label>
                                Username:
                            </label>
                        </div>
                        <input type="text" name="username" placeholder="Username" required>
                    </div>
                    <div class="Password">
                        <div class="inputlabel">
                            <label>
                                Password:
                            </label>
                        </div>
                        <input type="Password" name="password" placeholder="Password" required>
                    </div>
                    <div class="LoginButton">
                        <button>Login</button>
                        <button type="button" onclick="window.location.href='useradd.php'">Register</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</body>
</html>

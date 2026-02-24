<?php
session_start(); // start the session so we can use session variables

$_SESSION['table'] = 'users'; // save the name of the database table we are using

// check if the user is already logged in
$user = isset($_SESSION['user']) ? $_SESSION['user'] : null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NASA User Registration</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="default.css">
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

        /* MAIN PLANET – exact copy from your landing/login page */
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

        /* Exact same media query from your landing/login page */
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

        /* Hide old circle/planetcenter structure */
        .circle, .planetcenter {
            display: none !important;
        }
    </style>
</head>

<body>

    <a href="index.html">
        <img src="images/NASA-Logo.png" alt="NASA Logo" class="nasalogo">
    </a>

    <div class="main-planet">
        <div class="planet-content">

            <div class="RegistrationForm">
                <!-- registration form sends data to database/add.php using the post method -->
                <form action="database/add.php" method="POST" class="appForm">
                    <div>
                        <label for="first_name">First Name</label>
                        <input type="text" class="appFormInput" id="first_name" name="first_name" required />
                    </div>
                    <div>
                        <label for="last_name">Last Name</label>
                        <input type="text" class="appFormInput" id="last_name" name="last_name" required />
                    </div>
                    <div>
                        <label for="email">Email</label>
                        <input type="email" class="appFormInput" id="email" name="email" required />
                    </div>
                    <div>
                        <label for="password">Password</label>
                        <input type="password" class="appFormInput" id="password" name="password" required />
                    </div>

                    <button type="submit" class="appBtn"><i class="fa fa-plus"></i> Add User</button>
                    <button type="button" class="appBtn" onclick="history.back()">
                        < Back</button>
                </form>

                <?php
                // check if there is a response message to show (success or error)
                if (isset($_SESSION['response'])) {
                    $response_message = $_SESSION['response']['message']; // get the message
                    $is_success = $_SESSION['response']['success']; // check if it was a success
                ?>
                    <div class="responseMessage">
                        <!-- show the message with the correct style -->
                        <p class="<?= $is_success ? 'responseMessage__success' : 'responseMessage__error' ?>">
                            <?= $response_message ?>
                        </p>
                    </div>
                <?php
                    unset($_SESSION['response']); // remove the message so it only shows once
                }
                ?>
            </div>
        </div>
    </div>

</body>
</html>
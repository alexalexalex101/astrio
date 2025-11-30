<?php

    session_start();

    //stops non signed in users from seeing dashboard

    if(!isset($_SESSION['user'])) header('location: nasalogin.php');
    $user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" href="default.css">
    </head>
    <body>

    <img src="images/NASA-Logo.png" alt="Nasa Logo" class="nasalogo">
    <a href="database/logout.php" id="logoutBtn">Log out</a>
    <div class="circle">
            <div class="planetcenter">
                <div>
                    <p><?= $user['first_name'] . ' ' . $user['last_name'] ?></p>
                </div>
                
            </div>
        </div>
</body>

</html>

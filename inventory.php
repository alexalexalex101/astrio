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
        <link rel="stylesheet" href="https://use.typekit.net/pen4uct.css">
    </head>
    <body>

    <a href="dashboard.php"><img src="images/NASA-Logo.png" alt="Nasa Logo" class="nasalogo"></a>
    <a href="dashboard.php" id="backbutton">Back</a>       
    <a href="database/logout.php" id="logoutBtn">Log out</a>
    <div class="circle">
            <div class="planetcenter">

                    <p class="LoginTitle">Inventory</p>

                
            </div>
        </div>
</body>

</html>

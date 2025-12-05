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
    <a href="database/logout.php" id="logoutBtn">Log out</a>
    <div class="circle">
            <div class="planetcenter">

                    <p class="LoginTitle">Welcome, <?= $user['first_name'] . ' ' . $user['last_name'] ?></p>

                    <div class="navcontainer">
                        <div>
                            <button onclick="window.location.href='inventory.php'">INVENTORY</button>
                            <span id="space1"></span>
                            <button onclick="window.location.href='suppliers.php'">SUPPLIERS</button>
                        </div>
                        <div>
                            <button>SCHEDULE ORDERS</button>
                            <span id="space2"></span>                            
                            <button>WASTE</button>
                        </div>
                        <div>
                            <button>LOGS</button>
                            <span id="space3"></span>
                            <button>CONTRACTS</button>
                        </div>
                    </div>
                
            </div>
        </div>
</body>

</html>

<?php

    session_start();

    //stops non signed in users from seeing dashboard

    if(!isset($_SESSION['user'])) header('location: nasalogin.php');
    $user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html>
    <body>
        <div>
            <p><?= $user['first_name'] . ' ' . $user['last_name'] ?></p>
        </div>
        <a href="database/logout.php" id="logoutBtn">Log out</a>

</body>

</html>
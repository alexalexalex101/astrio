<?php

    session_start();
    //if the user is logged in then send them to the login
    if(isset($_SESSION['user'])) header('location: nasalogin.php');
    $_SESSION['table'] = 'users';
    $user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html>

<head>
    <title>
        NASA User Registration
    </title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="nasalogin.css">
    <link rel="stylesheet" href="https://use.typekit.net/pen4uct.css">
</head>
     
<body>
    
    <div class="HoriStrip">
    <h1 class="LoginTitle">
        NASA DSLM Registration
    </h1>
    </div>
    <div class="RegistrationForm">
        <form action="database/add.php" method="POST" class="appForm">
            <div>
                <label for="first_name">First Name</label>
                <input type="text" class="appFormInput" id="first_name" name="first_name"/>
            </div>
            <div>
                <label for="last_name">Last Name</label>
                <input type="text" class="appFormInput"  id="last_name" name="last_name"/>
            </div>
            <div>
                <label for="email">Email</label>
                <input type="text" class="appFormInput"  id="email" name="email"/>
            </div>
            <div>
                <label for="password">Password</label>
                <input type="password" class="appFormInput"  id="password" name="password"/>
            </div>

            <button type="submit" class="appBtn"><i class="fa fa-plus"></i> Add User</button>
        </form>
        <?php
            if(isset($_SESSION['response'])){
                    $response_message = $_SESSION['response']['message'];
                    $is_success = $_SESSION['response']['success'];  
        ?>
                <div class="responseMessage">
                    <p class="<?= $is_success ? 'responseMessage__success' : 'responseMessage__error' ?>">
                        <?= $response_message ?>
                    </p>
                </div>
        <?php unset($_SESSION['response']);} ?>
    </div>


</body>
</html>
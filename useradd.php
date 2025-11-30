<?php
session_start(); // start the session so we can use session variables

$_SESSION['table'] = 'users'; // save the name of the database table we are using

// check if the user is already logged in
$user = isset($_SESSION['user']) ? $_SESSION['user'] : null;
?>

<!DOCTYPE html>
<html>

<head>
    <title>NASA User Registration</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="default.css">
    <link rel="stylesheet" href="https://use.typekit.net/pen4uct.css">
</head>
     
<body>
<a href="index.html"><img src="images/NASA-Logo.png" alt="Nasa Logo" class="nasalogo"></a>
    
<div class="circle">
<div class="planetcenter"> 
    <div class="RegistrationForm">
        <!-- registration form sends data to useradd.php using the post method-->
        <form action="database/add.php" method="POST" class="appForm">
            <div>
                <label for="first_name">First Name</label>
                <input type="text" class="appFormInput" id="first_name" name="first_name" required/>
            </div>
            <div>
                <label for="last_name">Last Name</label>
                <input type="text" class="appFormInput" id="last_name" name="last_name" required/>
            </div>
            <div>
                <label for="email">Email</label>
                <input type="email" class="appFormInput" id="email" name="email" required/>
            </div>
            <div>
                <label for="password">Password</label>
                <input type="password" class="appFormInput" id="password" name="password" required/>
            </div>

            <button type="submit" class="appBtn"><i class="fa fa-plus"></i> Add User</button>
            <button type="button" class="appBtn" onclick="history.back()">< Back</button>            
        </form>

        <?php
        // check if there is a response message to show (success or error)
        if(isset($_SESSION['response'])){
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

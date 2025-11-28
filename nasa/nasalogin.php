<?php

    session_start();
    //if the user is logged in then send them to the dashboard
    if(isset($_SESSION['user'])) header('location: dashboard.php');
    $error_message='';
    if($_POST){
        include('database/connection.php');
        //get user and password if the form is sumitted by post
        $username = $_POST['username'];
        $password = $_POST['password'];
        
        //selects the user  in the database with the user and password entered
        $query = 'SELECT * FROM users WHERE users.email="' . $username . '" AND users.password="' . $password . '" ';
        $stmt = $conn->prepare($query);
        $result = $stmt->execute();

        //if that user was found then take the first row, make the user of the session equal to the first row and the session user is going to be the name of the user
        if($stmt->rowCount() > 0){
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $user = $stmt->fetchAll()[0];
            $_SESSION['user'] = $user;

            header('Location: dashboard.php');
        } else $error_message = 'Please make sure that username and password are correct. ';
    }
?>
<!DOCTYPE html>
<html>

<head>
    <title>
        NASA Supply Chain Management
    </title>
    <link rel="stylesheet" type="text/css" href="nasalogin.css">
    <link rel="stylesheet" href="https://use.typekit.net/pen4uct.css">
</head>
     
<body>
    
    <div class="HoriStrip">
    <h1 class="LoginTitle">
        NASA DSLM Login
    </h1>
    </div>
    <?php if(!empty($error_message)) { ?>
        <div id="errorMessage">
            <p>Error: <?= $error_message ?></p>
        </div>
    <?php } ?>
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
                <input type="text" name="username" placeholder="Username">
            </div>
            <div class="Password">
                <div class="inputlabel">
                    <label>
                        Password:
                    </label>
                    </div>
                <input type="Password" name="password" placeholder="Password">
            </div>
        <div class="LoginButton">
            <button>Login</button>
        </div>

        </form>
    </div>


</body>
</html>
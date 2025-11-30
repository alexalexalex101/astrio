<?php
    session_start();
    //if the user is logged in then send them to the dashboard
    //passwords are stored in http://localhost/phpmyadmin/ then go to inventory database and users table
    if(isset($_SESSION['user'])) header('location: dashboard.php');
    $error_message='';
    if($_POST){
        include('database/connection.php'); 
        //get user and password if the form is sumitted by post
        $username = $_POST['username'];
        $password = $_POST['password'];
        
        //selects the user in the database with the email entered
        $query = 'SELECT * FROM users WHERE users.email = :email LIMIT 1';
        $stmt = $conn->prepare($query);
        $stmt->execute([':email' => $username]);

        //if that user was found then take the first row
        if($stmt->rowCount() > 0){
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $user = $stmt->fetchAll()[0];

            //verify the password against the hashed password in DB
            //btw to test a password just use the jane doe one from the database
            if(password_verify($password, $user['password'])){
                $_SESSION['user'] = $user;
                header('Location: dashboard.php');
                exit;
            } else {
                $error_message = 'Please make sure that username and password are correct. ';
            }
        } else {
            $error_message = 'Please make sure that username and password are correct. ';
        }
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
        </div>

        </form>
    </div>


</body>
</html>
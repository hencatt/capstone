<?php
require_once "gad_portal.php";
include 'variables.php';

if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['pass']);

    // Fetch the hashed password from the database
    $select = mysqli_query($con, "SELECT * FROM accounts_tbl WHERE email = '$email'");
    if ($select && mysqli_num_rows($select) > 0) {
        $user = mysqli_fetch_assoc($select);
        $hashedPassword = $user['pass']; // Get the hashed password from the database

        // Verify the entered password against the hashed password
        if (password_verify($password, $hashedPassword)) {
            header("Location: setupProfile.php");
            exit(); 
        } else {
            echo "<div class='alert alert-danger' role='alert'>Invalid email or password.</div>";
        }
    } else {
        echo "<div class='alert alert-danger' role='alert'>Invalid email or password.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEUST GAD Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/design.css" type="text/css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https:/     /fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="variables.php" type="php">
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-lg-6 col-xl-4 headerNEUST">
                <img src=<?php echo "$neustLogo" ?> alt="">
                <h5>NUEVA ECIJA UNIVERSITY OF SCIENCE AND TECHNOLOGY</h5>
            </div>
        </div>
        <div class="row">
            <div class="contents">
                <div class="col">
                    <h1>Welcome</h1>
                </div>
            </div>
        </div>
        <div class="mainContent">
            <div class="leftSide">
                <form action="" method="POST" class="row">
                    <div class="col-md-12">
                        <label for="inputEmail" class="form-label"><b>Email or Username</b></label>
                        <input type="email" class="form-control" id="inputEmail" name="email" placeholder="Enter Email or Username">
                    </div>
                    <div class="col-md-12">
                        <label for="inputPassword" class="form-label"><b>Password</b></label>
                        <input type="password" class="form-control" name="pass" id="inputPassword" placeholder="Password">
                    </div>
                    <div class="row lowerPart">
                        <div class="col-md-12">
                            <p class="forgotPW"><a href="#forgotPass"><b>Forgot Password</b></a></p>
                            <input type="submit" name="login" value="Login" class="btn btn-primary">
                            <p class="createAcc">Don't have an account? <a href="signup.php"><b>Sign up</b></a></p>
                        </div>
                    </div>
                </form>
            </div>
            <div class="rightSide">
                <div class="col-md-12 images">
                    <img src=<?php echo "$sidePicture" ?> alt="sidepic">
                </div>
            </div>
        </div>
    </div>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</html>
<?php
require_once "gad_portal.php";
include 'variables.php';
include 'insertingLogs.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the user's IP address
    $ipAddress = $_SERVER['REMOTE_ADDR'];
    if ($ipAddress == '::1') {
        $ipAddress = '127.0.0.1'; // localhost for testing
    }

    // Check for failed login attempts in the last 15 minutes
    $cooldownPeriod = 15; // minutes
    $maxAttempts = 5; 
    $currentTime = date('Y-m-d H:i:s');

    // Count failed attempts in the last 15 minutes
    $query = "SELECT COUNT(*) AS attempt_count FROM login_attempts WHERE ip_add = ? AND attempt_time > DATE_SUB(?, INTERVAL ? MINUTE)";
    $stmt = $con->prepare($query);
    $stmt->bind_param("ssi", $ipAddress, $currentTime, $cooldownPeriod);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $attemptCount = $row['attempt_count'];

    if ($attemptCount >= $maxAttempts) {
        echo '<div class="alert alert-danger">Too many failed login attempts. Please try again after ' . $cooldownPeriod . ' minutes.</div>';
    } else {
        // Verify reCAPTCHA
        $recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';
        $secretKey = '6Ldtlh8rAAAAAHmSjUzeB3HLtHGZAY6AAZKTx4KC'; 
        $verifyResponse = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$secretKey&response=$recaptchaResponse");
        $responseData = json_decode($verifyResponse);

        if (!$responseData->success) {
            echo '<div class="alert alert-danger">Please complete the reCAPTCHA.</div>';
        } else {
            // Logic for login
            $input = trim($_POST['email']); 
            $password = trim($_POST['pass']);

            $select = mysqli_query($con, "SELECT * FROM accounts_tbl WHERE email = '$input' OR username = '$input'");
            if ($select && mysqli_num_rows($select) > 0) {
                $user = mysqli_fetch_assoc($select);
                $hashedPassword = $user['pass'];

                if (password_verify($password, $hashedPassword)) {
                    session_start();
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_position'] = $user['position'];
                    $_SESSION['user_username'] = $user['username'];

                    // Fetch department and campus
                    $_SESSION['user_department'] = $user['department'];
                    $_SESSION['user_campus'] = $user['campus'];

                    // Log user login
                    insertLog($_SESSION['user_username'], "User Login", date('Y-m-d H:i:s'));

                    // Redirect based on user position
                    if ($user['position'] === 'Director') {
                        header("Location: /capstone/Users/director.php");
                    } elseif ($user['position'] === 'Technical Assistant') {
                        header("Location: /capstone/Users/TA.php");
                    } elseif ($user['position'] === 'Focal Person') {
                        header("Location: /capstone/Users/focalPerson.php");
                    } else {
                        echo "Invalid position.";
                    }
                    exit();
                } else {
                    echo '<div class="alert alert-danger">Invalid password.</div>';
                    // Log the failed attempt
                    $insertQuery = "INSERT INTO login_attempts (ip_add, attempt_time) VALUES (?, ?)";
                    $stmt = $con->prepare($insertQuery);
                    $stmt->bind_param("ss", $ipAddress, $currentTime);
                    $stmt->execute();
                }
            } else {
                echo '<div class="alert alert-danger">Invalid email or username.</div>';
                // Log the failed attempt
                $insertQuery = "INSERT INTO login_attempts (ip_add, attempt_time) VALUES (?, ?)";
                $stmt = $con->prepare($insertQuery);
                $stmt->bind_param("ss", $ipAddress, $currentTime);
                $stmt->execute();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
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
            <?php
               echo '<form action="" method="POST" class="row">
                    <div class="col-md-12">
                        <label for="inputEmail" class="form-label"><b>Email or Username</b></label>
                        <input type="text" class="form-control" id="inputEmail" name="email" placeholder="Enter Email or Username">                    
                    </div>
                    <div class="col-md-12">
                        <label for="inputPassword" class="form-label"><b>Password</b></label>
                        <input type="password" class="form-control" name="pass" id="inputPassword" placeholder="Password">
                    </div>
                    <div class="col-md-12">
                        <div class="g-recaptcha" data-sitekey="6Ldtlh8rAAAAAM6CbfFcO66F4VsODtgXiws_p-Gp"></div>
                        <br/>
                        <input type="submit" name="login" value="Login" class="btn btn-primary">
                    </div>
                </form> ';
            ?>
                    <!-- <div class="row lowerPart">
                        <div class="col-md-12">
                            <p class="forgotPW"><a href="#forgotPass"><b>Forgot Password</b></a></p>
                            
                            <p class="createAcc">Don't have an account? <a href="signup.php"><b>Sign up</b></a></p>
                        </div>
                    </div> -->
                
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
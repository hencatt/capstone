<?php
require_once "./phpFunctions/gad_portal.php";
include_once './phpFunctions/variables.php';
include_once './phpFunctions/insertingLogs.php';

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

        // if (!$responseData->success) {
        //     echo '<div class="alert alert-danger">Please complete the reCAPTCHA.</div>';
        // } else { ## else start

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
                $_SESSION['user_fname'] = $user['fname'];
                $_SESSION['user_lname'] = $user['lname'];
                $_SESSION['fullname'] = $_SESSION['user_fname'] . " " . $_SESSION['user_lname'];

                // Fetch department and campus
                $_SESSION['user_department'] = $user['department'];
                $_SESSION['user_campus'] = $user['campus'];

                // Log user login
                insertLog($_SESSION['fullname'], "User Login", date('Y-m-d H:i:s'));

                // Redirect based on user position
                switch ($_SESSION['user_position']) {
                    case "Director":
                        header("Location: ./Users/director.php");
                        break;
                    case "Focal Person":
                        header("Location: ./Users/focalPerson.php");
                        break;
                    case "Technical Assistant":
                        header("Location: ./Users/TA.php");
                        break;
                    case "Researcher":
                        header("Location: ./Users/researchView.php");
                        break;
                    case "RET Chair":
                        header("Location: ./Users/events.php");
                        break;
                    case "Panel":
                        header("Location: ./Users/researchApproval.php");
                        break;
                    default:
                        echo "Invalid Position";
                        break;
                }
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
        // } ## else ennd
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https:/     /fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="variables.php" type="php">
</head>

<body>
    <style>
        body {
            font-family: "Poppins", sans-serif;
            margin: 0;
            background-color: white;
        }

        @import url("https://fonts.googleapis.com/css2?family=Poppins&display=swap");

        svg {
            font-family: "Poppins", sans-serif;
            width: 100%;
            height: 100%;
        }

        svg text {
            animation: stroke 5s infinite alternate;
            stroke-width: 2;
            stroke: #9725A0;
            font-size: 60px;
        }

        @keyframes stroke {
            0% {
                fill: rgba(204, 86, 203, 0);
                stroke: rgba(151, 37, 160, 1);
                stroke-dashoffset: 25%;
                stroke-dasharray: 0 50%;
                stroke-width: 2;
            }

            70% {
                fill: rgba(204, 86, 203, 0);
                stroke: rgba(151, 37, 160, 1);
            }

            80% {
                fill: rgba(204, 86, 203, 0);
                stroke: rgba(151, 37, 160, 1);
                stroke-width: 3;
            }

            100% {
                fill: rgba(204, 86, 203, 1);
                stroke: rgba(151, 37, 160, 0);
                stroke-dashoffset: -25%;
                stroke-dasharray: 50% 0;
                stroke-width: 0;
            }
        }

        .wrapper {
            background-color: #FFFFFF;
        }

        .wrapperMain {
            position: relative;
        }
    </style>
    <div class="container" style="overflow-y: hidden;">
        <!-- <div class="row">
            <div class="col-md-10 col-lg-8 col-xl-6 headerNEUST">
                <img src=<?php echo "$neustLogo" ?> alt="neustlogo">
                <h5>NUEVA ECIJA UNIVERSITY OF SCIENCE AND TECHNOLOGY</h5>
                <img src="assets/GADLogo.jpg" alt="">
            </div>
        </div> -->

        <div class="wrapperMain">

            <div style="
           position:fixed;
            top: 50%;
            left: 50%;
            transform: translate(-65%, -55%);
            ">
                <div class="row" style="
                    overflow-y: hidden;
                    filter: drop-shadow(1px 8px 10px); border-radius: 10px;
                    background: linear-gradient(135deg, #FFA9C1, #9C8CFF);
                    width: 1000px;
                    transform: translate(9.5rem, 2rem);
                    ">
                    <div class="col">
                        <div class="row d-flex justify-content-center align-items-center">
                            <div class="col" style="text-align: center;">
                                <img src="/capstone/assets/recreateSVG.svg" alt="" style="
                                width: 400px;
                                ">
                            </div>
                            <div class="col-6 d-flex flex-column justify-content-center"
                                style="background-color: white; padding:100px; border-radius:10px">
                                <div class="row">
                                    <div class="col">
                                        <div class="wrapper">
                                            <svg>
                                                <text x="50%" y="50%" dy=".35em" text-anchor="middle">
                                                    GAD Portal
                                                </text>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                                <form action="" method="POST">
                                    <div class="row mt-3">
                                        <div class="col">
                                            <label for="inputEmail" class="form-label"><b>Email or Username</b></label>
                                            <input type="text" class="form-control" id="inputEmail" name="email"
                                                placeholder="Enter Email or Username">
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col">
                                            <label for="inputPassword" class="form-label"><b>Password</b></label>
                                            <input type="password" class="form-control" name="pass" id="inputPassword"
                                                placeholder="Password">
                                        </div>
                                    </div>
                                    <div class="row mt-5">
                                        <div class="col d-flex flex-column gap-3">
                                            <div class="g-recaptcha"
                                                data-sitekey="6Ldtlh8rAAAAAM6CbfFcO66F4VsODtgXiws_p-Gp"></div>
                                            <input type="submit" name="login" value="Login" class="btn btn-primary">
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</html>
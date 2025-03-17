<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'variables.php'?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEUST GAD Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/designSignUp.css" type="text/css">
</head>
<body>
    
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-lg-6 col-xl-4 headerNEUST">
                <img src=<?php echo "$neustLogo"?> alt="">
                <h5>NUEVA ECIJA UNIVERSITY OF SCIENCE AND TECHNOLOGY</h5>
            </div>
        </div>
        <div class="row">
            <div class="contents">
                <div class="col">
                    <h1>Create Account</h1>
                </div>
            </div>
        </div>

        
        <div class="mainContent">
            <div class="leftSide">
                <form action="" class="row">
                    <div class="col-md-6">
                        <label for="inputFname" class="form-label"><b>First Name</b></label>
                        <input type="text" class="form-control" id="inputFname" placeholder="First Name">
                    </div>
                    <div class="col-md-6">
                        <label for="inputLname" class="form-label"><b>Last Name</b></label>
                        <input type="text" class="form-control" id="inputLname" placeholder="Last Name">
                    </div>
                    <div class="col-md-12">
                        <label for="inputEmail" class="form-label"><b>Email</b></label>
                        <input type="email" class="form-control" id="inputEmail" placeholder="name@gmail.com">
                    </div>
                    <div class="col-md-12">
                        <label for="inputPassword" class="form-label"><b>Password</b></label>
                        <input type="password" class="form-control" id="inputPassword" placeholder="********">
                    </div>
                    <div class="col-md-12">
                        <label for="inputConfirmPassword" class="form-label"><b>Confirm Password</b></label>
                        <input type="password" class="form-control" id="inputConfirmPassword" placeholder="********">
                    </div>
                </form>
            </div>

                <!-- insert pic here -->
                <div class="rightSide">
                    <div class="col-md-12 images">
                            <h1>(images/video)</h1>
                            <img src="" alt="">
                    </div>
                </div>
        </div>
                <div class="row lowerPart">
                    <div class="col-md-6">
                        <button type="button" class="btn btn-warning btn-md btn-block"><b>Create Account</b></button>
                        <p class="createAcc">Already have an account? <a href="index.php"><b>Log in</b></a></p>
                    </div>
                </div>
        </div>
    </div>

</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</html>

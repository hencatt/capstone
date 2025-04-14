<?php
    include '../variables.php';
    include '../gad_portal.php';

    // Prevent browser caching
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Pragma: no-cache");
    header("Expires: 0");

    if (isset($_POST['addItem'])) {
        $itemName = htmlspecialchars($_POST['name']);
        $itemDesc = htmlspecialchars($_POST['desc']);
        $itemStatus = htmlspecialchars($_POST['status']);
    
        $itemCheck = "SELECT itemName FROM inventory_tbl WHERE itemName = '$itemName'";
        $scanResult = $con->query($itemCheck);
    
        if (isset($_FILES['fileToUpload']) && $_FILES['fileToUpload']['error'] === UPLOAD_ERR_OK) {
            $mime = mime_content_type($_FILES['fileToUpload']['tmp_name']);
            $imageData = file_get_contents($_FILES['fileToUpload']['tmp_name']);
            $itemImg = "data:$mime;base64," . base64_encode($imageData); // ✅ THIS LINE FIXED
    
            if ($scanResult->num_rows > 0) {
                echo "<script>alert('Item Already Exist!')</script>";
            } else {
                $sql = "INSERT INTO inventory_tbl (itemName, itemDesc, itemImage, itemStatus) 
                        VALUES ('$itemName','$itemDesc','$itemImg','$itemStatus')";
    
                if ($con->query($sql)) {
                    echo "<script>alert('Item Added!')</script>";
                } else {
                    echo "Error: " . $con->error;
                }
            }
        } else {
            echo "<script>alert('Error Uploading File')</script>";
        }
    
        $con->close();
    }
    

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <link rel="stylesheet" href="css/focalPerson.css" type="text/css">
    <!-- font link -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <!-- icon link -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />

    <title>Focal Person</title>
</head>
<body>
        <!-- Left Sidebar -->
        <div class="row">
            <div class="col sidebar">
                <div class="row mt-lg-3 topLeft">
                    <div class="col neustLogo">
                        <img src="../assets/neust_logo-1-1799093319.png" alt="NeustLogo" class="logo">
                    </div>
                    <div class="col-lg-8">
                        <label>NEUST GAD Portal</label>
                    </div>
                </div>
                <div class="sidebarOptions">
                    <label class="category">Home</label>
                        <li id="active"><a href="#" class="categoryItem">
                            <span class="material-symbols-outlined">dashboard</span>    
                            Dashboard</a></li>
                    <label class="category">General</label>
                        <li><a href="#" class="categoryItem">
                            <span class="material-symbols-outlined">groups</span>     
                            Employees</a></li>
                        <li><a href="#" class="categoryItem">
                            <span class="material-symbols-outlined">inventory_2</span>  
                            Inventory</a></li>
                        <li><a href="#" class="categoryItem">
                        <span class="material-symbols-outlined">event</span>  
                        Events</a></li>
                    <label class="category">Settings</label>
                        <li><a href="#" class="categoryItem">
                        <span class="material-symbols-outlined">person</span>  
                        Account</a></li>
                        <li><a href="../logout.php?logout=true" class="categoryItem">
                        <span class="material-symbols-outlined">logout</span>  
                        Logout</a></li>
                </div>
            </div>
            <!-- Main Contents -->

            <div class="col-lg-10 col-sm-8 col-xs-6 mt-lg-3 mainContent">
                <div class="row">
                    <div class="mainTop col-lg-6">
                        <div class="search-container">
                            <input type="text" class="search-bar" placeholder="Search">
                            <span class="material-symbols-outlined">search</span>
                        </div>
                    </div>
                    <div class="col d-flex flex-row">
                        <div class="notif">       
                            <span class="material-symbols-outlined">notifications</span>
                        </div> 
                        <div class="logout">
                            <span class="material-symbols-outlined"><a href="../logout.php?logout=true">logout</a></span>
                        </div>
                    </div>
                </div>
                
                <!-- main overview -->
                <div class="row overview mt-lg-5 mb-lg-5">
                    <div class="col-lg-6 firCol">
                        <h6>Number of Items</h6><br>
                        <h4 class="itemText">
                            <?php
                            $con = new mysqli("localhost", "root", "", "gad_portal");
                                $sql = "SELECT id, itemName, itemDesc, itemImage, itemStatus FROM inventory_tbl";
                                $result = $con->query($sql);
                                echo $result->num_rows;
                            ?>
                        </h4>
                    </div>
                    <div class="col secCol">
                        <h6>Total Item Claims</h6><br>
                        <h4 class="itemText">
                        <?php
                                $sql = "SELECT id, itemName, itemDesc, itemImage, itemStatus FROM inventory_tbl WHERE itemStatus = 'confirmed'";
                                $result = $con->query($sql);
                                echo $result->num_rows;
                            ?>
                        </h4>    
                    </div>
                </div>
                <div class="row preview">
                    <div class="col">
                        <!-- inventory overview -->
                        <h1>Inventory</h1>
                    </div>
                    <div class="col-lg-2">
                            <button class="btn btn-success"
                            data-bs-toggle="modal" data-bs-target="#addItem"
                            >Add Item</button>
                    </div>
                    <table class="table">
                        <thead>
                            <tr>
                                <th scope="col">Item Name</th>
                                <th scope="col">ID</th>
                                <th scope="col">Description</th>
                                <th scope="col">Image</th>
                                <th scope="col">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            
                                <?php
                                $con = new mysqli("localhost", "root", "", "gad_portal");
                                    $sql = "SELECT id, itemName, itemDesc, itemImage, itemStatus FROM inventory_tbl";
                                    $result = $con->query($sql);

                                    if ($result->num_rows > 0) {
                                        while($row = $result->fetch_assoc()) {
                                            echo '<tr>';
                                            echo '<td scope="row">' . htmlspecialchars($row['itemName']) . '</td>';
                                            echo '<td scope="row">' . htmlspecialchars($row['id']) . '</td>';
                                            echo '<td scope="row">' . htmlspecialchars($row['itemDesc']) . '</td>';
                                            echo '<td scope="row"><img src="' . $row['itemImage'] . '" width="50" alt="Item image"></td>';
                                            echo '<td scope="row">' . htmlspecialchars($row['itemStatus']) . '</td>';
                                            echo '</tr>';
                                        }
                                    } else {
                                        echo "0 results";
                                    }                                    
                                      $con->close();
                                ?>
                            
                        </tbody>
                    </table>
                </div>
            </div>
        </div>


        <!-- addItemModal -->
        <div class="modal fade" id="addItem" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="staticBackdropLabel">Add Item</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="" method="POST" enctype="multipart/form-data">
                            <label for="itemName">Item Name:</label><br>    
                            <input type="text" name="name" id="itemName" placeholder="Item Name">
                            <br><br>
                            <label for="itemDesc">Description:</label><br> 
                            <textarea name="desc" id="itemDesc" placeholder="Enter text here..."></textarea>
                            <br><br>
                            <label for="itemStatus">Status:</label><br> 
                            <select name="status" id="itemStatus">
                                <option value="pending">pending</option>
                                <option value="confirmed">confirmed</option>
                            </select>
                            <br><br>
                            Image:<br> 
                            <input type="file" name="fileToUpload" id="fileToUpload">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" name="addItem">Save</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</html>

<?php
// error_reporting(E_ERROR | E_PARSE);
include '../variables.php';
include '../gad_portal.php';
include '../insertingLogs.php';

session_start();

// GEGET CURRENT USER PARA MAREADY KUNG SAAN IBABALIK NA DASHBOARD
$currentId = $_SESSION['user_id'];
$con = new mysqli("localhost", "root", "", "gad_portal");
$sql = "SELECT fname, lname, username, email, position, department, campus FROM accounts_tbl WHERE id = '$currentId'";
$result = $con->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $currentFname = htmlspecialchars($row["fname"]);
        $currentLname = htmlspecialchars($row["lname"]);
        $currentEmail = htmlspecialchars($row["email"]);
        $currentUsername = htmlspecialchars($row['username']);
        $currentUser = htmlspecialchars($row['fname']) . " " . htmlspecialchars($row['lname']);
        $currentPosition = htmlspecialchars($row['position']);
        $currentCampus = htmlspecialchars_decode($row['campus']);
        $currentDepartment = htmlspecialchars($row['department']);
    }
} else {
    echo "<script>console.log('No UserID Found')</script>";
}


// // Prevent browser caching
// header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
// header("Pragma: no-cache");
// header("Expires: 0");
// add item function
if (isset($_POST['addItem'])) {
    $itemName = htmlspecialchars($_POST['name']);
    $itemQuant = htmlspecialchars($_POST['quantity']);
    $itemDesc = htmlspecialchars($_POST['desc']);
    // $itemStatus = htmlspecialchars($_POST['status']);
    $itemSize = htmlspecialchars($_POST['size']);
    $itemCategory = htmlspecialchars($_POST['category']);

    $itemCheck = "SELECT itemName FROM inventory_tbl WHERE itemName = '$itemName'";
    $scanResult = $con->query($itemCheck);

    if (isset($_FILES['fileToUpload']) && $_FILES['fileToUpload']['error'] === UPLOAD_ERR_OK) {
        $mime = mime_content_type($_FILES['fileToUpload']['tmp_name']);
        $imageData = file_get_contents($_FILES['fileToUpload']['tmp_name']);
        $itemImg = "data:$mime;base64," . base64_encode($imageData); // ✅ THIS LINE FIXED

        if ($scanResult->num_rows > 0) {
            echo "<script>alert('Item Already Exist!')</script>";
        } else {
            $sql = "INSERT INTO inventory_tbl (itemName, itemDesc, itemImage, itemQuantity, itemSize, itemCategory) 
                        VALUES ('$itemName','$itemDesc','$itemImg','$itemQuant','$itemSize','$itemCategory')";

            if ($con->query($sql)) {
                //insert log
                insertLog($currentUsername, "Added (" . $itemName . ") Item", date('Y-m-d H:i:s'));
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


// EDIT FUNCTION
if (isset($_POST['updateItem'])) {
    $id = htmlspecialchars($_POST['itemID']);
    $newName = htmlspecialchars($_POST['updateName']);
    $newQty = htmlspecialchars($_POST['updateQuantity']);
    $newDesc = htmlspecialchars($_POST['updateDesc']);
    $newSize = htmlspecialchars($_POST['updateSize']);
    $newCategory = htmlspecialchars($_POST['updateCategory']);
    // $newStatus = htmlspecialchars($_POST['updateStatus']);

    if (isset($_FILES['UpdateFileToUpload']) && $_FILES['UpdateFileToUpload']['error'] === UPLOAD_ERR_OK) {
        $mime = mime_content_type($_FILES['UpdateFileToUpload']['tmp_name']);
        $imageData = file_get_contents($_FILES['UpdateFileToUpload']['tmp_name']);
        $itemImg = "data:$mime;base64," . base64_encode($imageData);

        $sql = "UPDATE inventory_tbl 
                        SET itemName = ?, itemQuantity = ?, itemDesc = ?, itemImage = ?, itemSize = ?, itemCategory = ?
                        WHERE id = ?";

        $stmt = $con->prepare($sql);
        $stmt->bind_param("sissssi", $newName, $newQty, $newDesc, $itemImg, $newSize, $newCategory, $id);
    } else {
        $sql = "UPDATE inventory_tbl
                        SET itemName = ?, itemQuantity = ?, itemDesc = ?, itemSize = ?, itemCategory = ?
                        WHERE id = ?";

        $stmt = $con->prepare($sql);
        $stmt->bind_param("sisssi", $newName, $newQty, $newDesc, $newSize, $newCategory, $id);
    }

    if ($stmt->execute()) {
        //insert log
        insertLog($currentUsername, "Updated (" . $newName . ") Item", date('Y-m-d H:i:s'));

        echo "<script>alert('Item updated successfully!');</script>";
    } else {
        echo "<script>alert('Failed to update item.');</script>";
    }

    $stmt->close();
    $con->close();
}

// DELETE FUNCTION
if (isset($_POST['deleteItem'])) {
    $id = htmlspecialchars($_POST['itemID']);
    $itemName = htmlspecialchars($_POST['itemName']);

    $sql = 'DELETE FROM inventory_tbl WHERE id = ?';
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        //delete log
        insertLog($currentUsername, "Deleted " . $itemName . " Item", date('Y-m-d H:i:s'));
        echo "<script>alert('Delete Successful')</script>";
    } else {
        echo "<script>alert('Error deleting: Please try again')</script>";
    }

    $stmt->close();
    $con->close();
}

// RESET FILTER FUNCTION



?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <link rel="stylesheet" href="css/techAss.css">
    <!-- font link -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">
    <!-- icon link -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />

    <title>Technical Assistant</title>

    <!-- jQuery Link -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>


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
                <li id="active"><a href="TA.php" class="categoryItem">
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
                <li><a href="modifyAccount.php" class="categoryItem">
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
                <div class="mainTop col-lg-6 d-flex flex-row align-items-center">
                    <div class="search-container">
                        <input type="text" class="search-bar" placeholder="Search">
                        <span class="material-symbols-outlined">search</span>
                    </div>
                </div>
                <div class="col d-flex flex-row align-items-center">
                    <div class="notif">
                        <span class="material-symbols-outlined">notifications</span>
                    </div>
                    <div class="logout">
                        <span class="material-symbols-outlined"><a href="../logout.php?logout=true">logout</a></span>
                    </div>
                </div>
                <div class="col d-flex flex-row align-items-center">
                    <span class="material-symbols-outlined">
                        account_circle
                    </span>
                    <label class="currentUserName"><?php echo $currentUser ?></label>
                </div>
            </div>

            <!-- main overview -->
            <div class="row overview mt-lg-5 mb-lg-5">
                <div class="col-lg-6 firCol">
                    <h6>Number of Items</h6><br>
                    <h4 class="itemText">
                        <?php
                        $con = new mysqli("localhost", "root", "", "gad_portal");
                        $sql = "SELECT id, itemName, itemDesc, itemImage FROM inventory_tbl";
                        $result = $con->query($sql);
                        echo $result->num_rows;
                        $con->close();

                        ?>
                    </h4>
                </div>
                <div class="col secCol">
                    <h6>Info Here</h6><br>
                    <h4 class="itemText">
                        <?php
                        $con = new mysqli("localhost", "root", "", "gad_portal");
                        $sql = "SELECT id, itemName, itemDesc, itemImage FROM inventory_tbl";
                        $result = $con->query($sql);
                        echo $result->num_rows;
                        $con->close();

                        ?>
                    </h4>
                </div>
            </div>
            <!-- EMPLOYEE AND BELOW ROW -->
            <div class="row">
                <!-- EMPLOYEE AND BELOW COLUMN -->
                <div class="col-lg-10">
                    <div class="row">
                        <div class="col">
                            <h1>Employee</h1>
                        </div>
                    </div>
                    <!-- SEARCH BARS, FILTERS ETC ROWS -->
                    <div class="row align-items-center">
                        <div class="col-lg-6">
                            <!-- <div class="search-container">
                                <input type="text" class="search-bar" placeholder="Search" id="searchEmployeeInput">
                                <span class="material-symbols-outlined">search</span>
                            </div> -->
                        </div>
                        <div class="col">
                            <div class="row">
                                <div class="col d-flex flex-row justify-content-between">
                                    <h6 class="filterLabel fst-italic">Department</h6>
                                    <h6 class="filterLabel fst-italic">Campus</h6>
                                    <h6 class="filterLabel fst-italic">Size</h6>
                                    <h6 class="filterLabel fst-italic">Gender</h6>
                                </div>
                            </div>
                            <!-- FILTER ROW -->
                            <div class="row">
                                <div class="col d-flex flex-row justify-content-end gap-3">
                                    <!-- dept filter -->
                                    <select name="filterDept" id="filterDept" class="form-select">
                                        <option value="" disabled>Filter Department</option>
                                        <option value="None" selected>None</option>
                                        <option value="Show All">Show All</option>
                                        <option value="CPADM">CPADM</option>
                                        <option value="CMBT">CMBT - BA, HM</option>
                                        <option value="CoArch">CoArch</option>
                                        <option value="CoEd">CoEd</option>
                                        <option value="Crim">Crim</option>
                                        <option value="COE">COE</option>
                                        <option value="CICT">CICT</option>
                                        <option value="IPE">IPE</option>
                                        <option value="LHS">LHS</option>
                                        <option value="CIT">CIT</option>
                                        <option value="CAS">CAS</option>
                                        <option value="IOLL">IOLL</option>
                                        <option value="CON">CON</option>
                                        <option value="GS">GS</option>
                                        <option value="NTP">NTP</option>
                                    </select>

                                    <!-- Campus Filter -->
                                    <select name="filterCampus" id="filterCampus" class="form-select">
                                        <option value="" disabled>Filter Campus</option>
                                        <option value="None" selected>None</option>
                                        <option value="Show All">Show All</option>
                                        <option value="Sumacab">Sumacab</option>
                                        <option value="Gen. Tinio">Gen. Tinio</option>
                                        <option value="San Isidro">San Isidro</option>
                                        <option value="Atate">Atate</option>
                                        <option value="Fort Magsaysay">Fort Magsaysay</option>
                                        <option value="Gabaldon">Gabaldon</option>

                                        <!-- and many more -->
                                    </select>

                                    <!-- Size filter -->
                                    <select name="filterSize" id="filterSize" class="form-select">
                                        <option value="" disabled>Filter Size</option>
                                        <option value="None" selected>None</option>
                                        <option value="Show All">Show All</option>
                                        <option value="S">Small</option>
                                        <option value="M">Medium</option>
                                        <option value="L">Large</option>
                                        <option value="XL">Extra Large</option>
                                        <option value="XXL">Double XL</option>
                                        <option value="XXXL">Triple XL</option>
                                        <option value="4XL">4XL</option>
                                        <!-- and many more -->
                                    </select>

                                    <!-- Gender filter -->
                                    <select name="filterGender" id="filterGender" class="form-select">
                                        <option value="" disabled>Filter Gender</option>
                                        <option value="None" selected>None</option>
                                        <option value="Show All">Show All</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                        <option value="LGBTQIA+">LGBTQIA+</option>
                                        <option value="Other">Other</option>
                                        <!-- and many more -->
                                    </select>
                                </div>
                            </div>
                            <div class="row mt-lg-2">
                                <div class="col d-flex flex-row justify-content-end gap-3 align-items-center">
                                    <label for="checkboxShowSummary" class="form-check-label">Show Summary</label>
                                    <input type="checkbox" id="checkboxShowSummary" name="checkboxShowSummary"
                                        class="form-check-input" value="Show Summary">
                                </div>
                                <div class="col d-flex flex-row justify-content-end align-items-center gap-3">
                                    <button type="submit" name="resetFilter" id="resetFilter"
                                        class="btn btn-outline-danger">Reset Filters</button>
                                    <button type="submit" name="saveFilter" id="saveFilter"
                                        class="btn btn-outline-success">Save Filters</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <div class="table-responsive">
                                <table class="table table-striped table-sm" id="employeeTable">
                                    <thead>
                                        <tr>
                                            <th>Full Name</th>
                                            <th>Email</th>
                                            <th>Contact No</th>
                                            <th>Department</th>
                                            <th>Campus</th>

                                        </tr>
                                    </thead>
                                    <tbody id="employeeTableBody">
                                        <?php
                                        $con = new mysqli("localhost", "root", "", "gad_portal");
                                        if ($con->connect_error) {
                                            die("Connection failed: " . $con->connect_error);
                                        }
                                        // Query to join employee_info and employee_tbl with full name
                                        $sql = "SELECT
                                                    CONCAT(ei.fname, ' ', ei.m_initial, '. ', ei.lname) AS full_name,
                                                    et.email,
                                                    et.contact_no,
                                                    et.department,
                                                    et.campus
                                                FROM employee_info ei
                                                INNER JOIN employee_tbl et ON ei.id = et.id";
                                        $result = $con->query($sql);
                                        if ($result->num_rows > 0) {
                                            // Fetch and display each row
                                            while ($row = $result->fetch_assoc()) {
                                                echo "<tr>";
                                                echo "<td>" . htmlspecialchars($row['full_name']) . "</td>";
                                                echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                                                echo "<td>" . htmlspecialchars($row['contact_no']) . "</td>";
                                                echo "<td>" . htmlspecialchars($row['department']) . "</td>";
                                                echo "<td>" . htmlspecialchars($row['campus']) . "</td>";
                                                echo "</tr>";
                                            }
                                        } else {
                                            echo "<tr><td colspan='5'>No data available</td></tr>";
                                        }
                                        $con->close();
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>



                    <div class="row overview mt-lg-5 mb-lg-5">
                        <div class="col-lg-6 firCol">
                            <h6>Number of Items</h6><br>
                            <h4 class="itemText">
                                <?php
                                $con = new mysqli("localhost", "root", "", "gad_portal");
                                $sql = "SELECT id, itemName, itemDesc, itemImage FROM inventory_tbl";
                                $result = $con->query($sql);
                                echo $result->num_rows;
                                $con->close();

                                ?>
                            </h4>
                        </div>
                        <div class="col secCol">
                            <h6>Info Here</h6><br>
                            <h4 class="itemText">
                                <?php
                                $con = new mysqli("localhost", "root", "", "gad_portal");
                                $sql = "SELECT id, itemName, itemDesc, itemImage FROM inventory_tbl";
                                $result = $con->query($sql);
                                echo $result->num_rows;
                                $con->close();

                                ?>
                            </h4>
                        </div>
                    </div>
                    <div class="row preview">
                        <div class="col-lg-9">
                            <!-- inventory overview -->
                            <h1>Inventory</h1>
                        </div>
                        <div class="col d-flex align-items-center justify-content-end">
                            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addItem">Add
                                Item</button>
                        </div>
                        <div class="row">
                            <div class="col">
                                <table class="table table-sm table-striped">
                                    <thead>
                                        <tr>
                                            <th scope="col">Item Name</th>
                                            <th scope="col">ID</th>
                                            <th scope="col">Quantity</th>
                                            <th scope="col">Size</th>
                                            <th scope="col">Description</th>
                                            <th scope="col">Image</th>
                                            <th scope="col">Status</th>
                                            <th scope="col">Category</th>
                                            <th scope="col"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $con = new mysqli("localhost", "root", "", "gad_portal");
                                        $sql = "SELECT id, itemName, itemDesc, itemImage, itemQuantity, itemSize, itemCategory FROM inventory_tbl";
                                        $result = $con->query($sql);

                                        if ($result->num_rows > 0) {
                                            while ($row = $result->fetch_assoc()) {
                                                $itemStatus = "";
                                                if (htmlspecialchars($row['itemQuantity']) <= 0) {
                                                    $itemStatus = 'Out of Stock';
                                                } else {
                                                    $itemStatus = 'In Stock';
                                                }
                                                echo '<tr>';
                                                echo '<td scope="row">' . htmlspecialchars($row['itemName']) . '</td>';
                                                echo '<td scope="row">' . htmlspecialchars($row['id']) . '</td>';
                                                echo '<td scope="row">' . htmlspecialchars($row['itemQuantity']) . '</td>';
                                                echo '<td scope="row">' . htmlspecialchars($row['itemSize']) . '</td>';
                                                echo '<td scope="row">' . htmlspecialchars($row['itemDesc']) . '</td>';
                                                echo '<td scope="row"><img src="' . $row['itemImage'] . '" width="30" alt="Item image"></td>';
                                                echo '<td scope="row">' . $itemStatus . '</td>';
                                                echo '<td scope="row">' . htmlspecialchars($row['itemCategory']) . '</td>';
                                                echo '<td scope="row">' .

                                                    '<button data-bs-toggle="modal" data-bs-target="#editItem' . htmlspecialchars($row['id']) . '">
                                                            <span class="material-symbols-outlined">edit</span>
                                                            </button>'
                                                    .

                                                    '<button data-bs-toggle="modal" data-bs-target="#deleteItem' . htmlspecialchars($row['id']) . '">
                                                            <span class="material-symbols-outlined">delete</span>
                                                            </button>
                                                            ' .

                                                    '</td>';
                                                echo '</tr>';


                                                // EDIT MODAL
                                                echo '
                                                                <div class="modal fade" id="editItem' . htmlspecialchars($row['id']) . '" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                                                    <div class="modal-dialog">
                                                                        <div class="modal-content">
                                                                            <div class="modal-header">
                                                                                <h1 class="modal-title fs-5">Edit Item</h1>
                                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                            </div>
                                                                            <div class="modal-body">
                                                                                <form action="" method="POST" enctype="multipart/form-data">
                                                                                    <input type="hidden" name="itemID" value="' . htmlspecialchars($row['id']) . '">
                                                                                    <input type="hidden" name="oldItemName" value="' . htmlspecialchars($row['itemName']) . '">
                                                                                    <label>Name:</label><br>    
                                                                                    <input value="' . htmlspecialchars($row['itemName']) . '" disabled="true" class="form-control">
                                                                                    <label>Change to:</label>
                                                                                    <input type="text" name="updateName" id="updateItemName" placeholder="Enter Updated Name" class="form-control" value="' . htmlspecialchars($row['itemName']) . '">
                                                                                    <br>

                                                                                    <label>Quantity:</label><br>    
                                                                                    <input value="' . htmlspecialchars($row['itemQuantity']) . '" disabled="true" class="form-control">
                                                                                    <label>Change to:</label>
                                                                                    <input type="number" name="updateQuantity" id="updateItemQuantity" placeholder="Updated Quantity" class="form-control" value="' . htmlspecialchars($row['itemQuantity']) . '">
                                                                                    <br>

                                                                                    <label>Size:</label><br>    
                                                                                    <input value="' . htmlspecialchars($row['itemSize']) . '" disabled="true" class="form-control">
                                                                                    <label>Change to:</label>
                                                                                    <select name="updateSize" id="updateItemSize" class="form-control">
                                                                                        <option value="-" disabled ' . ($row['itemSize'] === '-' ? 'selected' : '') . '>Select Size</option>
                                                                                        <option value="S" ' . ($row['itemSize'] === 'S' ? 'selected' : '') . '>S</option>
                                                                                        <option value="M" ' . ($row['itemSize'] === 'M' ? 'selected' : '') . '>M</option>
                                                                                        <option value="L" ' . ($row['itemSize'] === 'L' ? 'selected' : '') . '>L</option>
                                                                                        <option value="XL" ' . ($row['itemSize'] === 'XL' ? 'selected' : '') . '>XL</option>
                                                                                        <option value="XXL" ' . ($row['itemSize'] === 'XXL' ? 'selected' : '') . '>XXL</option>
                                                                                        <option value="XXXL" ' . ($row['itemSize'] === 'XXXL' ? 'selected' : '') . '>XXXL</option>
                                                                                        <option value="4XL" ' . ($row['itemSize'] === '4XL' ? 'selected' : '') . '>4XL</option>
                                                                                        <option value="5XL" ' . ($row['itemSize'] === '5XL' ? 'selected' : '') . '>5XL</option>
                                                                                        <option value="-" ' . ($row['itemSize'] === '-' ? 'selected' : '') . '>N/A</option>
                                                                                    </select>
                                                                                    <br>

                                                                                    <label>Category</label><br>
                                                                                    <input type="text" value="' . htmlspecialchars($row['itemCategory']) . '" disabled class="form-control">
                                                                                    <label>Change to:</label>       
                                                                                    <select name="updateCategory" id="updateItemCategory" class="form-control">
                                                                                        <option value="-" disabled ' . ($row['itemCategory'] === '-' ? 'selected' : '') . '>Select Category</option>
                                                                                        <option value="Women" ' . ($row['itemCategory'] === 'Women' ? 'selected' : '') . '>Women</option>
                                                                                        <option value="Men" ' . ($row['itemCategory'] === 'Men' ? 'selected' : '') . '>Men</option>
                                                                                        <option value="LGBTQIA+" ' . ($row['itemCategory'] === 'LGBTQIA+' ? 'selected' : '') . '>LGBTQIA+</option>
                                                                                        <option value="Education" ' . ($row['itemCategory'] === 'Education' ? 'selected' : '') . '>Education</option>
                                                                                        <option value="PWD" ' . ($row['itemCategory'] === 'PWD' ? 'selected' : '') . '>PWD</option>
                                                                                        <option value="Everyone" ' . ($row['itemCategory'] === 'Everyone' ? 'selected' : '') . '>Everyone</option>
                                                                                        <option value="-" ' . ($row['itemCategory'] === '-' ? 'selected' : '') . '>N/A</option>
                                                                                    </select>
                                                                                    <br>

                                                                                    <label>Image:</label><br> 
                                                                                    <img src="' . htmlspecialchars($row['itemImage']) . '" alt="Item Image" width="100"><br>
                                                                                    <label>Change Image</label>
                                                                                    <input type="file" name="UpdateFileToUpload" class="form-control">
                                                                                    <input type="hidden" name="itemID" value="' . htmlspecialchars($row['id']) . '">
                                                                                    <br>

                                                                                    <label>Description:</label><br> 
                                                                                    <textarea class="form-control" disabled="true">' . htmlspecialchars($row['itemDesc']) . '</textarea>
                                                                                    <label>Change to:</label>
                                                                                    <textarea class="form-control" name="updateDesc" id="updateItemDesc" placeholder"Enter updated text here...">' . htmlspecialchars($row['itemDesc']) . '</textarea>
                                                                                    
                                                                            </div>
                                                                            <div class="modal-footer">
                                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                                <button type="submit" class="btn btn-primary" name="updateItem">Save Changes</button>
                                                                                </form>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>';

                                                // delete modal
                                                echo '
                                                                <div class="modal fade" id="deleteItem' . htmlspecialchars($row['id']) . '" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                                                    <div class="modal-dialog" role="document">
                                                                        <div class="modal-content">
                                                                        <div class="modal-header">
                                                                            <h1 class="modal-title fs-5">Delete ' . htmlspecialchars($row['itemName']) . '</h1>
                                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                        </div>
                                                                        <div class="modal-body">
                                                                        <form method="POST">
                                                                            <input type="hidden" name="itemID" value="' . htmlspecialchars($row['id']) . '">
                                                                            <input type="hidden" name="itemName" value="' . htmlspecialchars($row['itemName']) . '">
                                                                            Are you sure you want to delete ' . htmlspecialchars($row['itemName']) . '?
                                                                        </div>
                                                                        <div class="modal-footer">
                                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                                <button type="submit" class="btn btn-primary" name="deleteItem">Yes</button>
                                                                        </form>
                                                                        </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            ';
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
                </div>
                <!-- END EMPLOYE AND BELOW COLUMN -->
            </div>
            <!-- END EMPLOYE AND BELOW ROW -->
        </div>


        <!-- addItemModal -->
        <div class="modal fade" id="addItem" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
            aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="staticBackdropLabel">Add Item</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="" method="POST" enctype="multipart/form-data">
                            <label for="itemName">Item Name:</label><br>
                            <input type="text" name="name" id="itemName" placeholder="(e.g. T-shirt)"
                                class="form-control">
                            <br><br>
                            <label for="itemName">Item Quantity:</label><br>
                            <input type="number" name="quantity" id="itemQuantity" placeholder="(e.g. 100)"
                                class="form-control">
                            <br><br>
                            <label for="itemSize">Item Size:</label><br>
                            <select name="size" id="itemSize" class="form-control">
                                <option value="-" disabled selected>Select Size</option>
                                <option value="S">S</option>
                                <option value="M">M</option>
                                <option value="L">L</option>
                                <option value="XL">XL</option>
                                <option value="XXL">XXL</option>
                                <option value="XXXL">XXXL</option>
                                <option value="4XL">4XL</option>
                                <option value="5XL">5XL</option>
                                <option value="-">N/A</option>
                            </select>
                            <br><br>
                            <label for="itemDesc">Description:</label><br>
                            <textarea name="desc" id="itemDesc" placeholder="Enter item description here..."
                                class="form-control"></textarea>
                            <br><br>

                            <label for="itemCategory">Category:</label><br>
                            <select name="category" id="itemCategory" class="form-control">
                                <option value="-" disabled selected>Select Category</option>
                                <option value="Women">Women</option>
                                <option value="Men">Men</option>
                                <option value="LGBTQIA+">LGBTQIA+</option>
                                <option value="Education">Education</option>
                                <option value="PWD">PWD</option>
                                <option value="Everyone">Everyone</option>
                                <option value="-">N/A</option>

                            </select>
                            <br><br>

                            <!-- <label for="itemStatus">Status:</label><br> 
                                <select name="status" id="itemStatus" class="form-control">
                                    <option value="" disabled selected>Select Status</option>
                                    <option value="pending">Pending</option>
                                    <option value="confirmed">Confirmed</option>
                                </select>
                                <br><br>
                                -->
                            Image:<br>
                            <input type="file" name="fileToUpload" id="fileToUpload" class="form-control">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" name="addItem">Add Item</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- editItemModal -->










    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq"
        crossorigin="anonymous"></script>

    <!-- SEARCH FUNCTION FOR EMPLOYEE SEARCH FUNCTION -->
    <script>
        $(document).ready(function () {
            $("#searchEmployeeInput").keyup(function () {
                var employeeName = $(this).val();

                console.log("Searching for: " + employeeName);

                $.post("../searchFunction.php", {
                    employeeSearch: employeeName
                }, function (data, status) {
                    $("#employeeTableBody").html(data);
                })
            });
        });
    </script>

    <!-- RESET FILTER -->
    <?php
echo <<<EOD
<script>
    $(document).ready(function () {

        var currentPos = "{$currentPosition}";

        $("#resetFilter").click(function () {
            if(currentPos !== "Director"){
            $("#filterSize").val("None").change();
            $("#filterGender").val("None").change();
            }else{
            $("#filterCampus").val("None").change();
            $("#filterSize").val("None").change();
            $("#filterDept").val("None").change();
            $("#filterGender").val("None").change();
            }
        });
    });
</script>
EOD;
?>


    <!-- SAVE FILTER FUNCTION -->
    <script>
        $(document).ready(function () {

            var CBshowSummary = $('#checkboxShowSummary');



            $('#saveFilter').click(function () {
                var campusFilter = $('#filterCampus').val();
                var deptFilter = $('#filterDept').val();
                var sizeFilter = $('#filterSize').val();
                var genderFilter = $('#filterGender').val();

                if (CBshowSummary.is(':checked')) {
                    showSum = "yes";
                } else {
                    showSum = "no";
                }
                console.log("currently posting: " + campusFilter + ', ' + deptFilter + ', ' + sizeFilter);
                console.log("show summary?: " + showSum);


                $.post("../filterFunction.php", {
                    campusFilter: campusFilter,
                    deptFilter: deptFilter,
                    sizeFilter: sizeFilter,
                    genderFilter: genderFilter,
                    showSummary: showSum
                }, function (data, status) {
                    $("#employeeTable").parent().html(data);
                });
            });
        });
    </script>

    <?php
    echo <<<EOD
<script>
    $(document).ready(function () {
        if ("{$currentPosition}" === "Technical Assistant" || "{$currentPosition}" === "Focal Person") {
            $('#filterDept').val("{$currentDepartment}").change();
            $('#filterCampus').val("{$currentCampus}").change();

            $('#filterDept').prop("disabled", true);
            $('#filterCampus').prop("disabled", true);
        }
    });
</script>
EOD;
    ?>



</body>


</html>
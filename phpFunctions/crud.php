<?php
require_once 'gad_portal.php';

// =====================================================================
//                             ANNOUNCEMENT FUNCTIONS
// =====================================================================

function createAnnouncements($announceId, $currentUser)
{
    $con = con();
    if (isset($_POST["$announceId"])) {
        // echo '<pre>';
        // print_r($_POST);
        // echo '</pre>';
        $announceTitle = $_POST["inputAnnouncementTitle"];
        $announceDescription = $_POST["inputAnnouncementDescription"];
        $announceDate = $_POST["inputAnnouncementDate"];
        $announceProposal = $_POST['inputProposal'];
        $announceAcceptance = $_POST['inputAcceptance'];
        $announcePresentation = $_POST['inputPresentationDate'];
        $announceCategory = $_POST['inputCategory'];

        $sql = "INSERT INTO announcement_tbl (announceTitle, announceDesc, announceDate, category, proposalDate, acceptanceDate, presentationDate) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("sssssss", $announceTitle, $announceDescription, $announceDate, $announceCategory, $announceProposal, $announceAcceptance, $announcePresentation);
        if ($stmt->execute()) {
            insertLog($currentUser, "Announced", date('Y-m-d H:i:s'));
            alertSuccess("Announced!", $announceTitle . " announced successfully!");
        } else {
            alertError("Error", "There's been an error announcing");
        }
    }
}




// =====================================================================
//                             INVENTORY FUNCTIONS
// =====================================================================

function createItemInventory($addItemId, $currentUser)
{
    $con = con();
    if (isset($_POST["$addItemId"])) {
        $itemName = htmlspecialchars($_POST['name']);
        $itemQuant = htmlspecialchars($_POST['quantity']);
        $itemDesc = htmlspecialchars($_POST['desc']);
        // $itemStatus = htmlspecialchars($_POST['status']);
        $itemSize = htmlspecialchars($_POST['size']);
        $itemCategory = htmlspecialchars($_POST['category']);

        $itemCheck = "SELECT itemName FROM inventory_tbl WHERE itemName = '$itemName'";
        $scanResult = $con->query($itemCheck);

        // initiate update para sa fileUpload even if theres no upload
        function addItem($con, $itemName, $itemQuant, $itemDesc, $itemSize, $itemCategory, $itemCheck, $itemImg, $currentUser, $scanResult)
        {
            if ($scanResult->num_rows > 0) {
                alertError("Already Exist", "This item already exists!");
            } else {
                $sql = "INSERT INTO inventory_tbl (itemName, itemDesc, itemImage, itemQuantity, itemSize, itemCategory) 
                        VALUES ('$itemName','$itemDesc','$itemImg','$itemQuant','$itemSize','$itemCategory')";

                if ($con->query($sql)) {
                    //insert log
                    insertLog($currentUser, "Added (" . $itemName . ") Item", date('Y-m-d H:i:s'));
                    alertSuccess("Added", "Successfully added this item!");
                } else {
                    alertError("Error", "Please try again");
                }
            }
        }

        if (isset($_FILES['fileToUpload']) && $_FILES['fileToUpload']['error'] === UPLOAD_ERR_OK) {

            $mime = mime_content_type($_FILES['fileToUpload']['tmp_name']);
            $imageData = file_get_contents($_FILES['fileToUpload']['tmp_name']);
            $itemImg = "data:$mime;base64," . base64_encode($imageData); // ✅ THIS LINE FIXED

            addItem($con, $itemName, $itemQuant, $itemDesc, $itemSize, $itemCategory, $itemCheck, $itemImg, $currentUser, $scanResult);
        } elseif (isset($_FILES['fileToUpload']) && $_FILES['fileToUpload']['error'] === 4) {
            $mime = "";

            $itemImg = "";
            addItem($con, $itemName, $itemQuant, $itemDesc, $itemSize, $itemCategory, $itemCheck, $itemImg, $currentUser, $scanResult);
        } else {
            alertError("Error", "Error uploading file");
        }

        $con->close();
    }
}

function editItemInventory($updateId, $currentUser)
{
    $con = con();
    if (isset($_POST["$updateId"])) {
        $id = htmlspecialchars($_POST['itemID']);
        $newName = htmlspecialchars($_POST['updateName']);
        $newQty = htmlspecialchars($_POST['updateQuantity']);
        $newDesc = htmlspecialchars($_POST['updateDesc']);
        $newSize = htmlspecialchars($_POST['updateSize']);
        $newCategory = htmlspecialchars($_POST['updateCategory']);

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
            insertLog($currentUser, "Updated (" . $newName . ") Item", date('Y-m-d H:i:s'));
            alertSuccess("Success", "Item Updated Successfully!");
        } else {
            alertError("Error", "Failed to update item");
        }

        $stmt->close();
        $con->close();
    }
}

function deleteItemInventory($deleteId, $currentUser)
{
    $con = con();
    if (isset($_POST["$deleteId"])) {
        $id = htmlspecialchars($_POST['itemID']);
        $itemName = htmlspecialchars($_POST['itemName']);

        $sql = 'DELETE FROM inventory_tbl WHERE id = ?';
        $stmt = $con->prepare($sql);
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            insertLog($currentUser, "Deleted " . $itemName . " Item", date('Y-m-d H:i:s'));
            alertSuccess("Deleted", "Item deleted successfully");
        } else {
            alertError("Error", "Failed deleting item");
        }

        $stmt->close();
        $con->close();
    }
}

function updateItemInventory($updateId, $currentUser)
{
    $con = con();
    if (isset($_POST["$updateId"])) {
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
            insertLog($currentUser, "Updated (" . $newName . ") Item", date('Y-m-d H:i:s'));

            alertSuccess("Success", "Item Updated Successfully!");
        } else {
            alertError("Error", "Failed to update item");
        }

        $stmt->close();
        $con->close();
    }
}




// =====================================================================
//                             EMPLOYEE FUNCTIONS
// =====================================================================

function createEmployeeFocalPerson($addEmployeeId, $currentUser)
{
    $con = con();
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST["$addEmployeeId"])) {
        $fname = htmlspecialchars($_POST['fname']);
        $m_initial = htmlspecialchars($_POST['m_initial']);
        $lname = htmlspecialchars($_POST['lname']);
        $address = htmlspecialchars($_POST['address']);
        $birthday = htmlspecialchars($_POST['birthday']);
        $marital_status = htmlspecialchars($_POST['marital_status']);
        $sex = htmlspecialchars($_POST['sex']);
        $gender = htmlspecialchars($_POST['gender']);
        $priority_status = htmlspecialchars($_POST['priority_status']);
        $size = htmlspecialchars($_POST['size']);
        $campus = htmlspecialchars($_POST['campus']);
        $email = htmlspecialchars($_POST['email']);
        $contact_no = htmlspecialchars($_POST['contact_no']);
        $department = htmlspecialchars($_POST['department']);
        $status = htmlspecialchars($_POST['status']);

        $sql_info = "INSERT INTO employee_info (fname, m_initial, lname, address, birthday, marital_status, sex, gender, priority_status, size) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_info = $con->prepare($sql_info);
        $stmt_info->bind_param("ssssssssss", $fname, $m_initial, $lname, $address, $birthday, $marital_status, $sex, $gender, $priority_status, $size);

        if ($stmt_info->execute()) {
            $employee_id = $con->insert_id;

            $sql_tbl = "INSERT INTO employee_tbl (id, email, contact_no, department, campus, status) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt_tbl = $con->prepare($sql_tbl);
            $stmt_tbl->bind_param("isssss", $employee_id, $email, $contact_no, $department, $campus, $status);

            if ($stmt_tbl->execute()) {
                insertLog($currentUser, "Added New Employee", date('Y-m-d H:i:s'));
                alertSuccess("Added", "Employee Added!");
                header("Location: " . $_SERVER['PHP_SELF']);
                $stmt_tbl->close();
                $stmt_info->close();
                exit();
            } else {
                die("Error executing statement for employee_tbl: " . $stmt_tbl->error);
            }
        } else {
            die("Error executing statement for employee_info: " . $stmt_info->error);
        }
    }
}

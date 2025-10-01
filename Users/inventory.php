<?php
require_once 'includes.php';

session_start();

checkUser($_SESSION['user_id'], $_SESSION['user_username']);
$user = getUser();
$currentUser = $user['fullname'];
$currentDepartment = $user['department'];
$currentCampus = $user['campus'];
$currentPosition = $user['position'];
doubleCheck($currentPosition);

// add item function
createItemInventory("addItem", $currentUser);

// EDIT FUNCTION
updateItemInventory("updateItem", $currentUser);

// DELETE FUNCTION
deleteItemInventory("deleteItem", $currentUser);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?= headerLinks("Inventory"); ?>
</head>

<body>
    <?= addDelay("inventory", $currentUser, $currentPosition); ?>

    <div class="row everything">
        <div class="col sidebar" id="sidebar">
            <?php sidebar("inventory", $currentPosition);
            ?>
        </div>

        <!-- MAIN CONTENT -->
        <div class="col-10 mt-lg-3 mainContent">
            <?php echo topbar("$currentUser", "$currentPosition", "inventory") ?>
            <div id="contents">
                <div class="row mt-5">
                    <div class="col">
                        <h1>Inventory</h1>
                    </div>
                </div>
                <div class="row mt-4 d-flex justify-content-end">
                    <div class="col-2" id="inventoryFilters">
                        <!-- TODO CATEGORY FUNCTION -->
                        
                    </div>
                </div>
                <div class="row mt-3 tableOverview">
                    <div class="col" id="inventoryTable">

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq"
        crossorigin="anonymous"></script>

    <script>
        $(document).ready(function () {
            const position = <?= json_encode($currentPosition) ?>;
            const campus = <?= json_encode($currentCampus) ?>;
            const dept = <?= json_encode($currentDepartment) ?>;
            $('#inventoryTable').load("./reusableHTML/inventoryTable.php");
            $('#inventoryFilters').load("./reusableHTML/inventoryFilterButton.php");
        })
    </script>

</body>

</html>
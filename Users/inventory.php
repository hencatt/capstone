<?php
require_once 'includes.php';

session_start();

checkUser($_SESSION['user_id']);
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

    <div class="row everything">
        <div class="col sidebar" id="sidebar">
            <?php sidebar("inventory", $currentPosition);
            ?>
        </div>

        <!-- MAIN CONTENT -->
        <div class="col-10 mt-lg-3 mainContent">
            <?php echo topbar("$currentUser", "$currentPosition", "inventory") ?>
            <div id="contents">
                <div class="row mt-4">
                    <div class="col">
                        <h1>Inventory</h1>
                    </div>
                </div>
                <div class="row" id="inventoryButtonsRow">
                    <div class="col d-flex justify-content-end" id="inventoryButtons">
                        <!-- BUTTONS HERE -->
                    </div>
                </div>
                <div class="row mt-2 d-flex justify-content-between align-items-center">
                    <div class="col-3 d-flex align-items-center justify-content-start gap-1">
                        <input type="text" placeholder="Search" name="searchBar" id="searchBar" class="form-control">
                        <button type="button" class="btn btn-secondary" name="searchBtn" id="searchBtn"> <span
                                class="material-symbols-outlined">
                                search
                            </span></button>
                    </div>
                    <!-- <div class="col-2" id="inventoryFilters">
          

                    </div> -->
                </div>



                <div class="row mt-3 tableOverview">
                    <div class="col" id="inventoryTable">
                        <?php include("./reusableHTML/inventoryTable.php"); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq"
        crossorigin="anonymous"></script>

    <?php include('../phpFunctions/alerts.php'); ?>


    <script>
        // Add this to your existing script section in inventory.php
        // Add this to your existing script section in inventory.php
        $(document).ready(function () {
            const position = <?= json_encode($currentPosition) ?>;
            const campus = <?= json_encode($currentCampus) ?>;
            const dept = <?= json_encode($currentDepartment) ?>;

            const searchBtn = $("#searchBtn");
            const searchBar = $("#searchBar");
            const inventoryButtonRow = $("#inventoryButtonsRow");

            if (position === "Technical Assistant") {
                $('#inventoryButtons').load("./reusableHTML/inventoryButtons.php", function () {
                    $("#viewMoreBtn").hide();
                });
            } else {
                inventoryButtonRow.hide();
            }

            // Search function
            function searchInventory() {
                const searchTerm = searchBar.val().trim();

                $.ajax({
                    url: './searchInventory.php',
                    type: 'GET',
                    data: { search: searchTerm },
                    beforeSend: function () {
                        $('#inventoryTable').html('<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>');
                    },
                    success: function (response) {
                        $('#inventoryTable').html(response);
                    },
                    error: function (xhr, status, error) {
                        console.error('Search error:', error);
                        $('#inventoryTable').html('<div class="alert alert-danger">Error loading results. Please try again.</div>');
                    }
                });
            }

            // Trigger search on button click
            searchBtn.on("click", function () {
                searchInventory();
            });

            // Optional: Trigger search on Enter key press
            searchBar.on("keypress", function (e) {
                if (e.which === 13) { // Enter key
                    e.preventDefault();
                    searchInventory();
                }
            });

            // Optional: Clear search and reload all items
            searchBar.on("input", function () {
                if ($(this).val().trim() === "") {
                    searchInventory(); // Reload all items when search is cleared
                }
            });
        });
    </script>

</body>

</html>
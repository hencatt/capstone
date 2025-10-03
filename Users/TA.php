<?php
// error_reporting(E_ERROR | E_PARSE);
require_once 'includes.php';

session_start();

checkUser($_SESSION['user_id'], $_SESSION['user_username']);
doubleCheck("Technical Assistant");

if ($_SESSION['user_position'] !== 'Technical Assistant') {
    if ($_SESSION['user_position'] === 'Director') {
        header("Location: director.php");
    } elseif ($_SESSION['user_position'] === 'Focal Person') {
        header("Location: focalPerson.php");
    } else {
        header("Location: ../index.php");
    }
    exit();
}
// GEGET CURRENT USER PARA MAREADY KUNG SAAN IBABALIK NA DASHBOARD
$user = getUser();
$currentUser = $user['fullname'];
$currentPosition = $user['position'];
$currentDepartment = $user['department'];
$currentCampus = $user['campus'];


// // Prevent browser caching
// header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
// header("Pragma: no-cache");
// header("Expires: 0");


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
    <?= headerLinks($currentPosition) ?>
</head>

<body>
    

    <!-- Left Sidebar -->
    <div class="row everything">
        <div class="col sidebar">
            <?php sidebar("dashboard", $currentPosition) ?>
        </div>
        <!-- Main Contents -->

        <div class="col-10 mt-3 mainContent">
            <?php topbar($currentUser, $currentPosition, "dashboard") ?>

            <div id="contents">
                <!-- main overview -->
                <div class="row overview mt-5 mb-lg-5">
                    <div class="col-3 summaryOverview">
                        <h6>Total Numbers of Employee</h6><br>
                        <h4 class="itemText">
                            <?php
                            $con = newCon();
                            $sql = "SELECT id FROM employee_tbl";
                            $result = $con->query($sql);
                            echo $result->num_rows;
                            ?>
                        </h4>
                    </div>
                    <div class="col">
                        <!-- <h6>Info Here</h6><br>
                        <h4 class="itemText">
                            <?php
                            $con = newCon();
                            $sql = "SELECT id, itemName, itemDesc, itemImage FROM inventory_tbl";
                            $result = $con->query($sql);
                            echo $result->num_rows;
                            $con->close();

                            ?>
                        </h4> -->
                    </div>
                </div>
                <!-- EMPLOYEE AND BELOW ROW -->
                <div class="tableOverview">
                    <div class="row">
                        <!-- EMPLOYEE AND BELOW COLUMN -->
                        <div class="col">
                            <div class="row">
                                <div class="col">
                                    <h1>Employee</h1>
                                </div>
                                <div class="col d-flex justify-content-end">
                                    <a href="./employees.php"><button class="btn btn-outline-primary">View
                                            More</button></a>
                                </div>
                            </div>
                            <!-- SEARCH BARS, FILTERS ETC ROWS -->
                            <!-- FILTER ROW -->
                            <div class="row">
                                <div class="col d-flex flex-row justify-content-end align-items-center gap-3"
                                    id="filters">
                                </div>
                            </div>
                            <div class="row mt-2" id="filterButtons">
                            </div>
                        </div>`
                    </div>
                    <div class="row mt-2" style="max-height: 200px; overflow-y: auto;">
                        <div class="col">
                            <div class="table-responsive">
                                <table class="table table-striped table-sm" id="employeeTable">
                                </table>
                            </div>
                        </div>
                    </div>
                </div>



                <div class="row overview mt-lg-5 mb-lg-5">
                    <div class="col-3 summaryOverview">
                        <h6>Number of Items</h6><br>
                        <h4 class="itemText">
                            <?php
                            $con = newCon();
                            $sql = "SELECT id, itemName, itemDesc, itemImage FROM inventory_tbl";
                            $result = $con->query($sql);
                            echo $result->num_rows;
                            $con->close();

                            ?>
                        </h4>
                    </div>
                    <div class="col secCol">
                        <!-- <h6>Info Here</h6><br>
                                <h4 class="itemText">
                                    <?php
                                    $con = newCon();
                                    $sql = "SELECT id, itemName, itemDesc, itemImage FROM inventory_tbl";
                                    $result = $con->query($sql);
                                    echo $result->num_rows;
                                    $con->close();

                                    ?>
                                </h4> -->
                    </div>
                </div>
                <div class="row tableOverview">
                    <div class="col">
                        <!-- inventory overview -->
                        <h1>Inventory</h1>
                    </div>
                    <div class="col d-flex align-items-center gap-3 justify-content-end">
                        <a href="./inventory.php"><button class="btn btn-outline-primary">View More</button></a>
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addItem">Add
                            Item</button>
                    </div>

                    <div class="row mt-5 table-responsive">
                        <div style="max-height: 200px; overflow-y: auto;">
                            <div class="col" id="inventoryTable">
                                <?php include("./reusableHTML/inventoryTable.php"); ?>
                            </div>
                        </div>
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
                        <input type="text" name="name" id="itemName" placeholder="(e.g. T-shirt)" class="form-control"
                            required>
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
            const position = <?= json_encode($currentPosition) ?>;
            const campus = <?= json_encode($currentCampus) ?>;
            const dept = <?= json_encode($currentDepartment) ?>;

            // $('#inventoryTable').load("./reusableHTML/inventoryTable.php");
            $('#inventoryFilters').load("./reusableHTML/inventoryFilterButton.php")
            $('#filters').load("./reusableHTML/filters.php", function () {
                $('#filterButtons').load("./reusableHTML/filtersButton.php", function () {
                    filterFunction("#checkboxShowSummary", "#filterCampus", "#filterDept", "#filterSize", "#filterGender", position, "#employeeTable", "no", "filter");
                    restrictDeptAndCampus(position, dept, campus, "#filterDept", "#filterCampus");
                    resetFilterFunction(position);

                })
            })

        });
    </script>
</body>

</html>
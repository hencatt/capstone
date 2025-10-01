<?php
require_once 'includes.php';

session_start();

// GEGET CURRENT USER PARA MAREADY KUNG SAAN IBABALIK NA DASHBOARD
$user = getUser();
$currentUser = $user['fullname'];
$currentPosition = $user['position'];
$currentDepartment = $user['department'];
$currentCampus = $user['campus'];

checkUser($_SESSION['user_id'], $_SESSION['user_username']);
doubleCheck("Focal Person");

$sql = "SELECT department, campus FROM accounts_tbl WHERE id = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $_SESSION['user_department'] = $row['department'];
    $_SESSION['user_campus'] = $row['campus'];
}

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Prevent browser caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

// Add Item Functionality
createItemInventory("addItem", $currentUser);

// EDIT FUNCTION
editItemInventory("editItem", $currentUser);

// DELETE FUNCTION
deleteItemInventory("deleteItem", $currentUser);

// Add Employee Functionality
createEmployeeFocalPerson("add_employee", $currentUser);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?= headerLinks($currentPosition) ?>
</head>

<body>
    <?php addDelay("dashboard", $currentUser, $currentPosition) ?>
    <!-- Left Sidebar -->
    <div class="row everything">
        <div class="col sidebar">
            <?php echo sidebar("dashboard", $currentPosition) ?>
        </div>
        <!-- Main Contents -->
        <div class="col-10 mt-3 mainContent">
            <?php echo topbar($currentUser, $currentPosition, "dashboard") ?>
            <div id="contents">
                <!-- main overview -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="row mt-lg-5 mb-lg-5">
                            <div class="col-3 summaryOverview">
                                <h6>Total Numbers of Employee</h6><br>
                                <h4 class="itemText">
                                    <?php
                                    $con = newCon();
                                    $sql = "SELECT * FROM employee_tbl WHERE department = '$currentDepartment' AND campus = '$currentCampus'";
                                    $result = $con->query($sql);
                                    echo $result->num_rows;
                                    ?>
                                </h4>
                            </div>
                            <div class="col">
                                <!-- <h6>Info here</h6><br>
                                <h4 class="itemText">
                                    Number
                                </h4> -->
                            </div>
                            <div class="col">
                                <!-- <h6>Info here</h6><br>
                                <h4 class="itemText">
                                    Number
                                </h4> -->
                            </div>
                            <div class="col">
                                <!-- <h6>info here</h6><br>
                                <h4 class="itemText">
                                    Number
                                </h4> -->
                            </div>
                            <div class="col">
                                <!-- <h6>info here</h6><br>
                                <h4 class="itemText">
                                    Number
                                </h4> -->
                            </div>
                        </div>

                        <!-- ====================================================================== -->
                        <!-- ====================================================================== -->
                        <!-- EMPLOYEE AND BELOW ROW -->
                        <div class="tableOverview">
                            <div class="row">
                                <!-- EMPLOYE AND BELOW COLUMN -->
                                <div class="col">
                                    <div class="row">
                                        <div class="col">
                                            <h1>Employee</h1>
                                        </div>
                                        <div class="col d-flex justify-content-end align-items-center gap-3">
                                            <a href="./employees.php"><button class="btn btn-outline-primary">View
                                                    More</button></a>
                                            <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                                data-bs-target="#add_emp_modal">
                                                Add Employee
                                                <span class="material-symbols-outlined">add</span>
                                            </button>
                                        </div>
                                    </div>
                                    <!-- SEARCH BARS, FILTERS ETC ROWS -->
                                    <div class="col">
                                        <!-- FILTER ROW -->
                                        <div class="row">
                                            <div class="col d-flex align-items-center justify-content-end gap-3"
                                                id="filters">
                                            </div>
                                        </div>
                                        <div class="row mt-2" id="filterButtons">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-2" style="max-height:200px; overflow-y: auto;">
                                <div class="col table-responsive">
                                    <table class="table table-striped" id="employeeTable">
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
                        <div class="col d-flex justify-content-end">
                            <a href="./inventory.php"><button class="btn btn-outline-primary">View More</button></a>
                        </div>

                        <!-- <div class="col d-flex align-items-center justify-content-end">
                                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addItem">Add
                                            Item
                                            <span class="material-symbols-outlined">add</span>
                                        </button>
                                    </div> -->



                        <div class="row mt-3 tableOverview">
                            <div style="max-height: 200px; overflow-y: auto;">
                                <div class="col" id="inventoryTable">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- END EMPLOYE AND BELOW COLUMN -->
            </div>
            <!-- END EMPLOYE AND BELOW ROW -->
        </div>
    </div>

    <!-- Add Employee Modal -->
    <div class="modal fade" id="add_emp_modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="addEmployeeModalLabel" aria-hidden="true">

        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title" id="addEmployeeModalLabel">Add Employee</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post" class="form_add_emp" novalidate>
                        <!-- Employee Info -->
                        <h5>Employee Information</h5>
                        <div class="mb-3">
                            <label for="fname" class="form-label">First Name</label>
                            <input type="text" name="fname" id="fname" class="form-control"
                                placeholder="Enter First Name" required>
                        </div>
                        <div class="mb-3">
                            <label for="m_initial" class="form-label">Middle Initial</label>
                            <input type="text" name="m_initial" id="m_initial" class="form-control"
                                placeholder="Enter Middle Initial">
                        </div>
                        <div class="mb-3">
                            <label for="lname" class="form-label">Last Name</label>
                            <input type="text" name="lname" id="lname" class="form-control"
                                placeholder="Enter Last Name" required>
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <input type="text" name="address" id="address" class="form-control"
                                placeholder="Enter Address" required>
                        </div>
                        <div class="mb-3">
                            <label for="birthday" class="form-label">Date of Birth</label>
                            <input type="date" name="birthday" id="birthday" class="form-control"
                                pattern="\d{4}-\d{2}-\d{2}" required>
                        </div>
                        <div class="mb-3">
                            <label for="marital_status" class="form-label">Marital Status</label>
                            <select name="marital_status" id="marital_status" class="form-select" required>
                                <option value="" disabled selected>Select Marital Status</option>
                                <option value="Single">Single</option>
                                <option value="Married">Married</option>
                                <option value="Widowed">Widowed</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="sex" class="form-label">Sex</label>
                            <select name="sex" id="sex" class="form-select" required>
                                <option value="" disabled selected>Select Sex</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="gender" class="form-label">Gender</label>
                            <select name="gender" id="gender" class="form-select" required>
                                <option value="" disabled selected>Select Sex</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="LGBTQIA+">LGBTQIA+</option>
                                <option value="Others">Others</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="priority_status" class="form-label">Priority Status</label>
                            <select name="priority_status" id="priority_status" class="form-select">
                                <option value="" disabled selected>Select Priority Status</option>
                                <option value="PWD">PWD</option>
                                <option value="Senior Citizen">Senior Citizen</option>
                                <option value="None">None</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="size" class="form-label">Shirt size</label>
                            <select name="size" id="size" class="form-select">
                                <option value="" disabled selected>Select Size</option>
                                <option value="S">S</option>
                                <option value="M">M</option>
                                <option value="L">L</option>
                                <option value="XL">XL</option>
                                <option value="2XL">2XL</option>
                                <option value="3XL">3XL</option>
                                <option value="4XL">4XL</option>

                            </select>
                        </div>

                        <!-- Employee Table -->
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" id="email" class="form-control" placeholder="Enter Email"
                                required>
                        </div>
                        <div class="mb-3">
                            <label for="contact_no" class="form-label">Contact No</label>
                            <input type="text" name="contact_no" id="contact_no" class="form-control"
                                placeholder="Enter Contact Number" required>
                        </div>
                        <div class="mb-3">
                            <label for="department" class="form-label">Department</label>
                            <select name="department" id="department" class="form-select" required>
                                <option value="" disabled>Select Department</option>
                                <option value="<?= $currentDepartment ?>" selected><?= $currentDepartment ?></option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="campus" class="form-label">Campus</label>
                            <select name="campus" id="campus" class="form-select" required>
                                <option value="" disabled>Select Campus</option>
                                <option value="<?= $currentCampus ?>" selected><?= $currentCampus ?></option>
                            </select>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" name="add_employee" class="btn btn-primary">Add
                                Employee</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- addItemModal -->
    <!-- <div class="modal fade" id="addItem" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
                    aria-labelledby="staticBackdropLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h1 class="modal-title fs-5" id="staticBackdropLabel">Add Item</h1>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
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
                                                //tanggal to
                                     <label for="itemStatus">Status:</label><br> 
                                <select name="status" id="itemStatus" class="form-control">
                                    <option value="" disabled selected>Select Status</option>
                                    <option value="pending">Pending</option>
                                    <option value="confirmed">Confirmed</option>
                                </select>
                                <br><br>
                               //tanggal dito
                                   Image:<br>
                                    <input type="file" name="fileToUpload" id="fileToUpload" class="form-control">
                            </div>
                            
                        </div>
                    </div>
                </div> -->
    </div>

    <!-- editItemModal -->


</body>
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
                $("#employeeTableBody").parent().html(data);
            })
        });
    });
</script>

<!-- SAVE FILTER FUNCTION -->
<script>
    $(document).ready(function () {
        const position = <?= json_encode($currentPosition) ?>;
        const campus = <?= json_encode($currentCampus) ?>;
        const dept = <?= json_encode($currentDepartment) ?>;
        $('#inventoryTable').load("./reusableHTML/inventoryTable.php");
        $('#inventoryFilters').load("./reusableHTML/inventoryFilterButton.php");
        $('#filters').load("reusableHTML/filters.php", function () {
            $('#filterButtons').load("./reusableHTML/filtersButton.php", function () {
                $('#department').prop('disabled', true);
                $('#campus').prop('disabled', true);
                filterFunction("#checkboxShowSummary", "#filterCampus", "#filterDept", "#filterSize", "#filterGender", position, "#employeeTable", "no", "filter");
                resetFilterFunction(position);
                restrictDeptAndCampus(position, dept, campus, "#filterDept", "#filterCampus");
            })
        })

    });
</script>

</html>
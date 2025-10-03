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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['saveInfo'])) {
    $fname        = $_POST['inputFname'] ?? '';
    $mname        = $_POST['inputMname'] ?? '';
    $lname        = $_POST['inputLname'] ?? '';
    $email        = $_POST['inputEmail'] ?? '';
    $contact_no   = $_POST['inputContact'] ?? '';
    $department   = $_POST['inputDepartment'] ?? '';
    $campus       = $_POST['inputCampus'] ?? '';
    $status       = 'Active';

    $street   = $_POST['inputStAddress'] ?? '';
    $city     = $_POST['inputCity'] ?? '';
    $province = $_POST['inputProvince'] ?? '';
    $address  = trim($street . ', ' . $city . ', ' . $province, ', ');

    $birthdate      = $_POST['inputBirthdate'] ?? '';
    $marital_status = $_POST['inputMaritalStatus'] ?? '';
    $sex            = $_POST['inputSex'] ?? '';

    $gender = (isset($_POST['inputGender']) && $_POST['inputGender'] === 'LGBTQIA+') 
                ? ($_POST['otherGender'] ?? '') 
                : ($_POST['inputGender'] ?? '');

    $size            = $_POST['inputSize'] ?? '';
    $income          = $_POST['inputIncome'] ?? '';
    $priority_status = $_POST['inputPriority'] ?? '';
    $childrenNum     = isset($_POST['inputChildrenNum']) ? (int)$_POST['inputChildrenNum'] : 0;
    $concern = !empty($_POST['inputConcern']) ? $_POST['inputConcern'] : 'N/A';

    if (empty($email)) {
        echo "<script>alert('❌ Email is required!');</script>";
        exit();
    }

    // --- INSERT employee_tbl
    $stmt_emp = $con->prepare("INSERT INTO employee_tbl 
        (email, contact_no, department, campus, status) 
        VALUES (?, ?, ?, ?, ?)");
    $stmt_emp->bind_param("sssss", $email, $contact_no, $department, $campus, $status);

    if ($stmt_emp->execute()) {
        $employee_id = $con->insert_id;

        // --- INSERT employee_info
        $stmt_info = $con->prepare("INSERT INTO employee_info 
            (fname, m_initial, lname, address, birthday, marital_status, sex, gender, priority_status, size, income, employee_id, children_num, concern) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt_info->bind_param(
            "sssssssssssiis", 
            $fname,
            $mname,
            $lname,
            $address,
            $birthdate,
            $marital_status,
            $sex,
            $gender,
            $priority_status,
            $size,
            $income,
            $employee_id,
            $childrenNum,
            $concern
        );

        if ($stmt_info->execute()) {
            echo "<script>alert('✅ Employee added successfully!');</script>";
        } else {
            echo "<script>alert('❌ Insert employee_info failed: " . addslashes($stmt_info->error) . "');</script>";
        }
    } else {
        echo "<script>alert('❌ Insert employee_tbl failed: " . addslashes($stmt_emp->error) . "');</script>";
    }
}
    
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
                                                    <button type="button" class="btn btn-success" id="addEmployeeBtn">
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
                                    <?php include("./reusableHTML/inventoryTable.php"); ?>
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
<?php require('./reusableHTML/personalInfoModal.php'); ?>

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

        // $('#inventoryTable').load("./reusableHTML/inventoryTable.php");
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Modal logic
    const addEmployeeBtn = document.getElementById('addEmployeeBtn');
    const modal = document.getElementById('modal');
    const closeBtns = modal.querySelectorAll('.close-btn, #cancelInfo');

    if (addEmployeeBtn && modal) {
        addEmployeeBtn.addEventListener('click', function() {
            modal.classList.add('open');
            document.body.style.overflow = 'hidden';
        });
    }

    closeBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            modal.classList.remove('open');
            document.body.style.overflow = '';
        });
    });

    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.classList.remove('open');
            document.body.style.overflow = '';
        }
    });

    // jQuery logic for gender and child options
    $(function() {
        const genderSelect = $("#inputGender");
        const otherGender = $("#otherGender");

        genderSelect.on("change", function () {
            if ($(this).val() === "LGBTQIA+") {
                otherGender.show().attr("required", true);
            } else {
                otherGender.hide().val("").removeAttr("required");
            }
        });

        function toggleChildOptions() {
            const checkedChild = $('input[name="inputChildren"]:checked').val();
            if (checkedChild === "No") {
                $("#childrenNum").val("");
                $("#childrenNumCol").hide();
                $("#childConcern").val("");
                $("#childConcernCol").hide();
            } else {
                $("#childrenNumCol").show();
                $("#childConcernCol").show();
            }
        }

        toggleChildOptions();
        $('input[name="inputChildren"]').on('change', function () {
            toggleChildOptions();
        });
    });
});
</script>




</html>
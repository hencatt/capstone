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


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?= headerLinks("Employees"); ?>
</head>

<body>
    <?= addDelay("employees", $currentUser, $currentPosition); ?>

    <div class="row everything">
        <div class="col sidebar" id="sidebar">
            <?php sidebar("employees", $currentPosition);
            ?>
        </div>

        <!-- MAIN CONTENT -->
        <div class="col-10 mt-lg-3 mainContent">
            <?php echo topbar("$currentUser", "$currentPosition", "employees") ?>
            <div id="contents">
                <div class="row mt-5">
                    <div class="col">
                        <h1>Employees</h1>
                    </div>
                </div>
                <!-- FiltersHere -->
                <div class="row">
                    <div class="col d-flex flex-row justify-content-end align-items-center gap-3" id="filters">
                    </div>
                </div>
                <div class="row mt-3" id="filterButton">
                    <!-- FILTER BUTTONS HERE -->
                </div>
                <!-- TableHere -->
                <div class="row mt-3 tableOverview">
                    <div class="col" id="showEmployeeTable">
                        <!-- TABLES HERE -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            const position = <?= json_encode($currentPosition) ?>;
            const campus = <?= json_encode($currentCampus) ?>;
            const dept = <?= json_encode($currentDepartment) ?>;

            // Load filters, table, buttons first
            $('#filters').load("reusableHTML/filters.php", function() {
                $('#showEmployeeTable').load("reusableHTML/employeeTable.php", function() {
                    $('#filterButton').load("reusableHTML/filtersButton.php", function() {
                        // Now everything exists → safe to run
                        filterFunction("#checkboxShowSummary", "#filterCampus", "#filterDept", "#filterSize", "#filterGender", position, "#employeeTable", "no", "filter");
                        resetFilterFunction(position);
                        restrictDeptAndCampus(position, dept, campus, "#filterDept", "#filterCampus");
                    });
                });
            });
        });
    </script>
</body>

</html>
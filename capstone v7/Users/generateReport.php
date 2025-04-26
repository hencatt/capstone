<?php
include("../gad_portal.php");
include("../insertingLogs.php");
include("../filterFunction.php");
require("../fpdf/fpdf.php");


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

if($currentPosition === "Director"){
    $dashboard = "director.php";
}elseif($currentPosition === "Technical Assistant"){
    $dashboard = "TA.php";
}elseif($currentPosition === "Focal Person"){
    $dashboard = "focalPerson.php";
}

?>

<?php
if (isset($_POST['btnGeneratePDF'])) {
    insertLog($currentUsername, "Generated Report", date('Y-m-d H:i:s'));
}
?>





<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <link rel="stylesheet" href="css/generateReport.css">
    <!-- font link -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">
    <!-- icon link -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />

    <title>Generate Report</title>

    <!-- jQuery Link -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <!-- html2pfg -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"
        integrity="sha512-GsLlZN/3F2ErC5ifS5QtgpiJtWd43JWSuIgh7mbzZ8zBps+dvLusV+eNQATqgA/HdeKFVgA5v3S/cIrLF7QnIg=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
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
                <li id="active">
                    <a href="<?= $dashboard ?>" class="categoryItem">
                        <span class="material-symbols-outlined">dashboard</span>
                        Dashboard
                    </a>
                </li>
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
                <li><a href="generateReport.php" class="categoryItem">
                        <span class="material-symbols-outlined">description</span>
                        Generate Report</a></li>
                <li><a href="modifyAccount.php" class="categoryItem">
                        <span class="material-symbols-outlined">person</span>
                        Account</a></li>
                <li><a href="../logout.php?logout=true" class="categoryItem">
                        <span class="material-symbols-outlined">logout</span>
                        Logout</a></li>
            </div>
        </div>
        <!-- Main CONTENTS -->
        <div class="col-lg-10 col-sm-8 col-xs-6 mt-lg-3 mainContent">
            <div class="row">
                <h1>Generate Report</h1>
                <div class="row mt-5">
                    <div class="col">
                        <h3>Employee</h3>
                    </div>
                    <div class="col">
                    </div>
                    <!-- EMPLOYEE FILTERS COLUMN -->
                    <div class="row mt-3">
                        <div class="col-lg-8 d-flex flex-row align-items-center justify-content-start gap-3">
                            <div class="col" id="toggleFilterDepartment">
                                <div class="row">
                                    <h6>Department</h6>
                                    <div class="col">
                                        <select name="filterDepartment" id="filterDepartment" class="form-select">
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
                                    </div>
                                </div>
                            </div>
                            <div class="col" id="toggleFilterCampus">
                                <div class="row">
                                    <h6>Campus</h6>
                                    <div class="col">
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
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col" id="toggleFilterGender">
                                <div class="row">
                                    <h6>Gender</h6>
                                    <div class="col">
                                        <select name="filterGender" id="filterGender" class="form-select">
                                            <option value="" disabled>Filter Gender</option>
                                            <option value="None" selected>None</option>
                                            <option value="Show All">Show All</option>
                                            <option value="Male">Male</option>
                                            <option value="Female">Female</option>
                                            <option value="LGBTQIA+">LGBTQIA+</option>
                                            <option value="Other">Other</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col" id="toggleFilterSize">
                                <div class="row">
                                    <h6>Size</h6>
                                    <div class="col">
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
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-8 d-flex flex-row align-items-center justify-content-start gap-3">

                    </div>
                </div>
                <!-- DISPLAY FILTER BUTTON-->
                <div class="row mt-4">
                    <div class="col d-flex flex-row justify-content-start align-items-center gap-5">
                        <button type="submit" id="btnGeneratePDF" name="btnGeneratePDF" class="btn btn-success">Generate
                            PDF</button>
                    </div>
                    <div class="col d-flex flex-row justify-content-end align-items-center gap-3">
                        <div id="makeReceiptDIV">
                            <label for="checkboxReceipt" class="form-check-label">Make Receipt</label>
                            <input type="checkbox" name="checkboxReceipt" id="checkboxReceipt" value="Make Receipt"
                                class="form-check-input">
                        </div>
                        <label for="checkboxShowSummary" class="form-check-label">Show Summary</label>
                        <input type="checkbox" name="checkboxShowSummary" id="checkboxShowSummary" value="Show Summary"
                            class="form-check-input">
                    </div>
                    <div class="col">
                        <button class="btn btn-secondary" type="button" name="displayResult" id="displayResult">Display
                            Result</button>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col">

                        <form action="generateReport.php" id="dapatPrint">

                            <div class="table-responsive" id="generatePDF">
                                <table id="employeeTable">
                                </table>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- filter toggles employee show -->
    <?php
    if ($currentPosition === "Director") {
        echo <<<EOD
    <script>
    $(document).ready(function(){


    });
    </script>
    EOD;
    } elseif ($currentPosition === "Focal Person") {
        echo <<<EOD
            <script>
    $(document).ready(function(){

        $('#makeReceiptDIV').hide();

        let fixedDepartment = "{$currentDepartment}";
        let fixedCampus = "{$currentCampus}";

        $('#filterDepartment').val(fixedDepartment).change();
        $('#filterCampus').val(fixedCampus).change();
        
        $('#filterDepartment').prop("disabled", true);
        $('#filterCampus').prop("disabled", true);

    });
    </script>
    EOD;
    }
    ?>

<?php
echo <<<EOD
    <script>
        $(document).ready(function () {
            var CBshowSummary = $('#checkboxShowSummary');
            var displayResult = $('#displayResult');
            var pdfBTN = $('#btnGeneratePDF');
            var receiptBTN = $('#checkboxReceipt');
            var pos = '{$currentPosition}';
            var generate = "report";

            pdfBTN.hide();

            displayResult.click(function () {
                pdfBTN.show();

                var campusFilter = $('#filterCampus').val();
                var deptFilter = $('#filterDepartment').val();
                var sizeFilter = $('#filterSize').val();
                var genderFilter = $('#filterGender').val();
                var showSum = CBshowSummary.is(':checked') ? "yes" : "no";
                var showRec = receiptBTN.is(':checked') ? "yes" : "no";
                var position = pos;

                console.log("currently posting: " + campusFilter + ', ' + deptFilter + ', ' + sizeFilter);
                console.log("show summary? :" + showSum);

                $.post("../filterFunction.php", {
                    deptFilter: deptFilter,
                    campusFilter: campusFilter,
                    sizeFilter: sizeFilter,
                    genderFilter: genderFilter,
                    showSummary: showSum,
                    showReceipt: showRec,
                    currentPosition: pos,
                    whatGenerate: generate
                }, function (data, status) {
                    $("#employeeTable").parent().html(data);
                });
            });
        });
    </script>
EOD;
    ?>

    <script>
        var pdfBTN = document.getElementById('btnGeneratePDF');

        pdfBTN.addEventListener('click', function () {
            var element = document.getElementById('generatePDF');
            var opt = {
                margin: 0.5,
                filename: 'myfile.pdf',
                image: { type: 'jpeg', quality: 1 },
                html2canvas: { scale: 2 },
                jsPDF: { unit: 'in', format: 'legal', orientation: 'landscape' }
            };

            html2pdf().set(opt).from(element).save();
        });
    </script>
</body>
</html>
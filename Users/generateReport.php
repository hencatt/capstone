<?php
require_once 'includes.php';
session_start();

checkUser($_SESSION['user_id'], $_SESSION['user_username']);

// GEGET CURRENT USER PARA MAREADY KUNG SAAN IBABALIK NA DASHBOARD
$user = getUser();
$currentUser = $user['fullname'];
$currentPosition = $user['position'];
$currentDepartment = $user['department'];
$currentCampus = $user['campus'];


if ($currentPosition === "Director") {
    $dashboard = "director.php";
} elseif ($currentPosition === "Technical Assistant") {
    $dashboard = "TA.php";
} elseif ($currentPosition === "Focal Person") {
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
    <?= headerLinks("Generate Report") ?>
</head>

<body>
    <style>
        #generatePDF {
            margin: 0;
            padding: 20px;
            width: 100%;
            box-sizing: border-box;
            background: white;
            border-radius: 25px;
        }
    </style>

    <?php addDelay("report", $currentUser, $currentPosition) ?>
    <!-- Left Sidebar -->
    <div class="row everything">
        <div class="col sidebar">

            <?php echo sidebar("report", $currentPosition) ?>

        </div>
        <!-- Main CONTENTS -->
        <div class="col-lg-10 col-sm-8 col-xs-6 mt-lg-3 mainContent">
            <?php echo topbar("$currentUser", "$currentPosition", "report") ?>
            <div id="contents">
                <div class="row mt-5">
                    <h1>Generate Report <span class="material-symbols-outlined">
                            article
                        </span></h1>
                    <div class="row mt-3">
                        <div class="col">
                            <h3>Employee</h3>
                        </div>
                        <div class="col">
                        </div>
                        <!-- EMPLOYEE FILTERS COLUMN -->
                        <div class="row mt-3">
                            <div class="col d-flex flex-row align-items-center justify-content-start gap-3">
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
                    <div class="row mt-4 d-flex flex-row justify-content-start align-items-center">
                        <div class="col-2">
                            <label for="orientation" id="orientationLabel" class="form-select-label">Orientation</label>
                            <select name="orientation" id="orientation" class="form-select">
                                <option value="portrait">Portrait</option>
                                <option value="landscape" selected>Landscpae</option>
                            </select>
                        </div>
                        <div class="col-1">
                            <label for="scale" id="scaleLabel" class="form-select-label">Quality</label>
                            <select name="scale" id="scale" class="form-select">
                                <option value="1">x1</option>
                                <option value="2" selected>x2</option>
                                <option value="3">x3</option>
                            </select>
                        </div>
                        <div class="col-2">
                            <label for="size" id="sizeLabel" class="form-select-label">Size</label>
                            <select name="size" id="size" class="form-select">
                                <option value="letter">Letter</option>
                                <option value="a4" selected>A4</option>
                                <option value="legal">Legal</option>
                            </select>
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
                    </div>
                    <div class="row mt-3">
                        <div class="col-2">
                            <button type="submit" id="btnGeneratePDF" name="btnGeneratePDF" class="btn btn-success">Generate
                                PDF</button>
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
    </div>

    <!-- filter toggles employee show -->
    <?php
    if ($currentPosition === "Focal Person") {
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


    <script>
        const position = <?= json_encode($currentPosition) ?>;
        const dept = <?= json_encode($currentDepartment) ?>;
        const campus = <?= json_encode($currentCampus) ?>;

        const filter_gender = $('#filterGender');
        const filter_dept = $('#filterDepartment');
        const filter_campus = $('#filterCampus');
        const filter_size = $('#filterSize');


        function hideElement() {
            if (filter_gender.val() === "None" && filter_dept.val() === "None" && filter_campus.val() === "None" && filter_size.val() === "None") {
                $('#btnGeneratePDF').hide();
                $('#generatePDF').hide();
            }
        }


        $(document).ready(function() {
            $('#btnGeneratePDF').hide();
            $('#generatePDF').hide();
            generateReportFilter(
                position,
                "#btnGeneratePDF",
                "#generatePDF",
                "#filterCampus",
                "#filterDepartment",
                "#filterSize",
                "#filterGender",
                "#checkboxShowSummary",
                "#checkboxReceipt",
                "#employeeTable"
            );
            hideElement();
            filter_gender.add(filter_dept).add(filter_campus).add(filter_size).on("change", hideElement);
        });
    </script>


    <script>
        const sizeSelect = document.getElementById("size");
        const scaleSelect = document.getElementById("scale");
        const orientationSelect = document.getElementById("orientation");

        document.getElementById('btnGeneratePDF').addEventListener('click', () => {
            const element = document.getElementById('generatePDF');

            const opt = {
                margin: 0, // Remove all outer margins
                filename: `Report.pdf`,
                image: {
                    type: 'jpeg',
                    quality: 0.98
                },
                html2canvas: {
                    scale: `${scaleSelect.value}`,
                    scrollY: 0 // 🔥 Important: prevents scroll offset bug
                },
                jsPDF: {
                    unit: 'pt',
                    format: `${sizeSelect.value}`,
                    orientation: `${orientationSelect.value}`
                }
            };

            html2pdf().set(opt).from(element).save();
        });
    </script>
</body>

</html>
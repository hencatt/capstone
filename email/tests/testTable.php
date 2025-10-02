<?php
// error_reporting(E_ERROR | E_PARSE);
include '../variables.php';
include '../gad_portal.php';
include '../insertingLogs.php';
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

    <div class="row">
        <div class="col d-flex flex-column align-items-center">

            <div class="row">
                <div class="col-2">
                    <img src="../assets/neust_logo-1-1799093319.png" style="width: 125px" alt="">
                </div>
                <div class="col" style="font-family: 'Times New Roman'; color: #003464;">
                    <h6><b>Republic of the Philippines</b></h6>
                    <h4>NUEVA ECIJA UNIVERSITY OF SCIENCE AND TECHNOLOGY</h4>
                    <h6><b>Cabanatuan City, Nueva Ecija, Philippines</b></h6>
                    <h6><b>ISO 9001:2015 CERTIFIED</b></h6>
                </div>
                <div class="col-2">
                    <img src="../assets/GADLogo.jpg" style="width: 125px" alt="">
                </div>
            </div>
            
        </div>
        <div class="row">
                <div class="div" style="height: 20px; background-color: #003464; width: 10px"></div>
            </div>
        <div class="row">
                <div class="div" style="height: 20px; background-color:rgb(235, 159, 46); width: 10px"></div>
            </div>

    </div>


<table class="table table-striped table-sm" id="employeeTable">
                                    <thead>
                                        <tr>
                                            <th>Full Name</th>
                                            <th>Email</th>
                                            <th>Contact No</th>
                                            <th>Department</th>
                                            <th>Campus</th>
                                            <th>Signature</th>

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
                                                echo "<td>gggg</td>";
                                                echo "</tr>";
                                            }
                                        } else {
                                            echo "<tr><td colspan='5'>No data available</td></tr>";
                                        }
                                        $con->close();
                                        ?>
                                    </tbody>
                                </table>

                                <div class="row pt-5">
                                    <div class="col"></div>
                                    <div class="col d-flex flex-column gap-1 align-items-center justify-content-center">
                                        <div style="height: 3px; background-color: black; width:300px"></div>
                                        <h6>Signature Over Printed Name</h6>
                                    </div>
                                </div>

</body>
</html>
<?php

$con = new mysqli("localhost", "root", "", "gad_portal");

// Check connection
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

// FOR EMPLOYEE TABLE
// FILTER DEPARTMENT
if (isset($_POST['filterByDept'])) {
    $deptFilter = trim($_POST['filterByDept']);

    // First, check if "Show All" is selected
    if ($deptFilter === "Show All") {
        // Show all records
        $sql = "SELECT 
                    CONCAT(ei.fname, ' ', ei.m_initial, '. ', ei.lname) AS full_name, 
                    et.email, 
                    et.contact_no, 
                    et.department, 
                    et.status 
                FROM employee_info ei
                INNER JOIN employee_tbl et ON ei.id = et.id";

        $result = $con->query($sql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['full_name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                echo "<td>" . htmlspecialchars($row['contact_no']) . "</td>";
                echo "<td>" . htmlspecialchars($row['department']) . "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='5'>No data available</td></tr>";
        }
    }

    // If not "Show All", proceed with filtered query
    else if (!empty($deptFilter)) {
        $sql = "SELECT 
                    CONCAT(ei.fname, ' ', ei.m_initial, '. ', ei.lname) AS full_name, 
                    et.email, 
                    et.contact_no, 
                    et.department, 
                    et.status 
                FROM employee_info ei
                INNER JOIN employee_tbl et ON ei.id = et.id
                WHERE et.department = ?";

        $stmt = $con->prepare($sql);
        $stmt->bind_param("s", $deptFilter);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['full_name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                echo "<td>" . htmlspecialchars($row['contact_no']) . "</td>";
                echo "<td>" . htmlspecialchars($row['department']) . "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='5'>No matching records found</td></tr>";
        }

        $stmt->close();
    }
}  //END OF EMPLOYEE TABLE

// FILTER DEPARTMENT
if (isset($_POST['filterBySize'])) {
    $sizeFilter = trim($_POST['filterBySize']);

    // First, check if "Show All" is selected
    if ($sizeFilter === "Show All") {
        // Show all records
        $sql = "SELECT 
                    CONCAT(ei.fname, ' ', ei.m_initial, '. ', ei.lname) AS full_name, 
                    et.email, 
                    et.contact_no, 
                    et.department, 
                    et.status 
                FROM employee_info ei
                INNER JOIN employee_tbl et ON ei.id = et.id";

        $result = $con->query($sql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['full_name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                echo "<td>" . htmlspecialchars($row['contact_no']) . "</td>";
                echo "<td>" . htmlspecialchars($row['department']) . "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='5'>No data available</td></tr>";
        }
    }

    // If not "Show All", proceed with filtered query
    else if (!empty($sizeFilter)) {
        $sql = "SELECT 
                    CONCAT(ei.fname, ' ', ei.m_initial, '. ', ei.lname) AS full_name, 
                    et.email, 
                    et.contact_no, 
                    et.department, 
                    ei.size
                FROM employee_info ei
                INNER JOIN employee_tbl et ON ei.id = et.id
                WHERE ei.size = ?";

        $stmt = $con->prepare($sql);
        $stmt->bind_param("s", $sizeFilter);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['full_name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                echo "<td>" . htmlspecialchars($row['contact_no']) . "</td>";
                echo "<td>" . htmlspecialchars($row['department']) . "</td>";
                echo "<td>" . htmlspecialchars($row['size']) . "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='5'>No matching records found</td></tr>";
        }

        $stmt->close();
    }
}  //END OF EMPLOYEE TABLE

// FILTER DEPARTMENT
if (isset($_POST['filterByCampus'])) {
    $campusFilter = trim($_POST['filterByCampus']);

    // First, check if "Show All" is selected
    if ($campusFilter === "Show All") {
        // Show all records
        $sql = "SELECT 
                    CONCAT(ei.fname, ' ', ei.m_initial, '. ', ei.lname) AS full_name, 
                    et.email, 
                    et.contact_no, 
                    et.department
                FROM employee_info ei
                INNER JOIN employee_tbl et ON ei.id = et.id";

        $result = $con->query($sql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['full_name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                echo "<td>" . htmlspecialchars($row['contact_no']) . "</td>";
                echo "<td>" . htmlspecialchars($row['department']) . "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='5'>No data available</td></tr>";
        }
    }

    // If not "Show All", proceed with filtered query
    else if (!empty($campusFilter)) {
        $sql = "SELECT 
                    CONCAT(ei.fname, ' ', ei.m_initial, '. ', ei.lname) AS full_name, 
                    et.email, 
                    et.contact_no, 
                    et.department, 
                    et.campus
                FROM employee_info ei
                INNER JOIN employee_tbl et ON ei.id = et.id
                WHERE et.campus = ?";

        $stmt = $con->prepare($sql);
        $stmt->bind_param("s", $campusFilter);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['full_name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                echo "<td>" . htmlspecialchars($row['contact_no']) . "</td>";
                echo "<td>" . htmlspecialchars($row['department']) . "</td>";
                echo "<td>" . htmlspecialchars($row['campus']) . "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='5'>No matching records found</td></tr>";
        }

        $stmt->close();
    }
}  //END OF EMPLOYEE TABLE

// FILTER GENDER
if (isset($_POST['filterByGender'])) {
    $genderFilter = trim($_POST['filterByGender']);

    // First, check if "Show All" is selected
    if ($genderFilter === "Show All") {
        // Show all records
        $sql = "SELECT 
                    CONCAT(ei.fname, ' ', ei.m_initial, '. ', ei.lname) AS full_name, 
                    et.email, 
                    et.contact_no, 
                    et.department
                FROM employee_info ei
                INNER JOIN employee_tbl et ON ei.id = et.id";

        $result = $con->query($sql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['full_name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                echo "<td>" . htmlspecialchars($row['contact_no']) . "</td>";
                echo "<td>" . htmlspecialchars($row['department']) . "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='5'>No data available</td></tr>";
        }
    }

    // If not "Show All", proceed with filtered query
    else if (!empty($genderFilter)) {
        $sql = "SELECT 
                    CONCAT(ei.fname, ' ', ei.m_initial, '. ', ei.lname) AS full_name, 
                    et.email, 
                    et.contact_no, 
                    et.department, 
                    ei.gender
                FROM employee_info ei
                INNER JOIN employee_tbl et ON ei.id = et.id
                WHERE ei.gender = ?";

        $stmt = $con->prepare($sql);
        $stmt->bind_param("s", $genderFilter);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['full_name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                echo "<td>" . htmlspecialchars($row['contact_no']) . "</td>";
                echo "<td>" . htmlspecialchars($row['department']) . "</td>";
                echo "<td>" . htmlspecialchars($row['gender']) . "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='5'>No matching records found</td></tr>";
        }

        $stmt->close();
    }
}  //END OF EMPLOYEE TABLE

$con->close();
?>
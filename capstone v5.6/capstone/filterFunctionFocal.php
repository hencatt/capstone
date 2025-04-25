<?php
session_start();

// Initialize the database connection
$con = new mysqli("localhost", "root", "", "gad_portal");
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

// Get the logged-in user's department and campus
$currentDepartment = $_SESSION['user_department'] ?? "None";
$currentCampus = $_SESSION['user_campus'] ?? "None";

if (isset($_POST['sizeFilter'], $_POST['genderFilter'])) {
    $size   = $_POST['sizeFilter'];
    $gender = $_POST['genderFilter'];

    // Base query with department and campus filter
    $sql = "SELECT 
                CONCAT(ei.fname, ' ', ei.m_initial, '. ', ei.lname) AS full_name, 
                et.email, 
                et.contact_no, 
                et.department, 
                et.campus, 
                ei.size, 
                ei.gender 
            FROM employee_info ei
            INNER JOIN employee_tbl et ON ei.id = et.id
            WHERE et.department = '$currentDepartment' AND et.campus = '$currentCampus'";

    // Additional filters
    if ($size !== "None" && $size !== "Show All") {
        $size = $con->real_escape_string($size);
        $sql .= " AND ei.size = '$size'";
    }
    if ($gender !== "None" && $gender !== "Show All") {
        $gender = $con->real_escape_string($gender);
        $sql .= " AND ei.gender = '$gender'";
    }

    $result = $con->query($sql);

    // Table output
    echo '<table class="table table-striped table-sm" id="employeeTable">';
    echo '<thead><tr>';
    echo '<th>Full Name</th>';

    // Dynamically adjust table headers based on filters
    if ($size === "None" && $gender === "None") {
        echo '<th>Email</th><th>Contact No</th><th>Department</th><th>Campus</th>';
    } else {
        if ($size !== "None") echo '<th>Size</th>';
        if ($gender !== "None") echo '<th>Gender</th>';
    }

    echo '</tr></thead><tbody id="employeeTableBody">';

    // Dynamically adjust table rows based on filters
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($row['full_name']) . '</td>';

            if ($size === "None" && $gender === "None") {
                echo '<td>' . htmlspecialchars($row['email']) . '</td>';
                echo '<td>' . htmlspecialchars($row['contact_no']) . '</td>';
                echo '<td>' . htmlspecialchars($row['department']) . '</td>';
                echo '<td>' . htmlspecialchars($row['campus']) . '</td>';
            } else {
                if ($size !== "None") echo '<td>' . htmlspecialchars($row['size']) . '</td>';
                if ($gender !== "None") echo '<td>' . htmlspecialchars($row['gender']) . '</td>';
            }

            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="6">No matching records found</td></tr>';
    }

    echo '</tbody></table>';

    // Display counts in a table
    echo '<div class="row mt-4">';
    echo '<div class="table-responsive">';

    // Total rows
    echo '<table class="table table-bordered table-hover table-sm">';
    echo '<thead><tr><th colspan="2">Summary</th></tr></thead>';
    echo '<tbody>';
    echo '<tr><td>Total Rows</td><td>' . ($result ? $result->num_rows : 0) . '</td></tr>';
    echo '</tbody>';
    echo '</table>';

    // Gender counts
    echo '<table class="table table-bordered table-hover table-sm mt-3">';
    echo '<thead><tr><th colspan="4">Gender Counts</th></tr></thead>';
    echo '<tbody>';
    echo '<tr><th>Male</th><th>Female</th><th>LGBTQIA+</th><th>Others</th></tr>';
    echo '<tr>';
    $genders = ['Male' => 0, 'Female' => 0, 'LGBTQIA+' => 0, 'Others' => 0];
    $genderCountSql = "SELECT ei.gender, COUNT(*) as count
                       FROM employee_info ei
                       INNER JOIN employee_tbl et ON ei.id = et.id
                       WHERE et.department = '$currentDepartment' AND et.campus = '$currentCampus'";
    if ($size !== "None" && $size !== "Show All") {
        $genderCountSql .= " AND ei.size = '$size'";
    }
    $genderCountSql .= " GROUP BY ei.gender";
    $genderCountResult = $con->query($genderCountSql);
    if ($genderCountResult && $genderCountResult->num_rows > 0) {
        while ($row = $genderCountResult->fetch_assoc()) {
            $genders[$row['gender']] = $row['count'];
        }
    }
    foreach ($genders as $count) {
        echo '<td>' . $count . '</td>';
    }
    echo '</tr>';
    echo '</tbody>';
    echo '</table>';

    // Size counts
    echo '<table class="table table-bordered table-hover table-sm mt-3">';
    echo '<thead><tr><th colspan="7">Size Counts</th></tr></thead>';
    echo '<tbody>';
    echo '<tr><th>S</th><th>M</th><th>L</th><th>XL</th><th>2XL</th><th>3XL</th><th>4XL</th></tr>';
    echo '<tr>';
    $sizes = ['S' => 0, 'M' => 0, 'L' => 0, 'XL' => 0, '2XL' => 0, '3XL' => 0, '4XL' => 0];
    $sizeCountSql = "SELECT ei.size, COUNT(*) as count
                     FROM employee_info ei
                     INNER JOIN employee_tbl et ON ei.id = et.id
                     WHERE et.department = '$currentDepartment' AND et.campus = '$currentCampus'";
    if ($gender !== "None" && $gender !== "Show All") {
        $sizeCountSql .= " AND ei.gender = '$gender'";
    }
    $sizeCountSql .= " GROUP BY ei.size";
    $sizeCountResult = $con->query($sizeCountSql);
    if ($sizeCountResult && $sizeCountResult->num_rows > 0) {
        while ($row = $sizeCountResult->fetch_assoc()) {
            $sizes[$row['size']] = $row['count'];
        }
    }
    foreach ($sizes as $count) {
        echo '<td>' . $count . '</td>';
    }
    echo '</tr>';
    echo '</tbody>';
    echo '</table>';

    echo '</div>';
    echo '</div>';

    $con->close();
}
?>
<?php
// SALAMAT CHATGPT
if (isset($_POST['campusFilter'], $_POST['deptFilter'], $_POST['sizeFilter'], $_POST['genderFilter'])) {
    $campus = $_POST['campusFilter'];
    $dept   = $_POST['deptFilter'];
    $size   = $_POST['sizeFilter'];
    $gender = $_POST['genderFilter'];

    $con = new mysqli("localhost", "root", "", "gad_portal");

    if ($con->connect_error) {
        die("Connection failed: " . $con->connect_error);
    }

    // Check if no filters are selected
    $noFilters = ($campus === "None" && $dept === "None" && $size === "None" && $gender === "None");

    // Dynamically build SELECT fields
    $sql = "SELECT CONCAT(ei.fname, ' ', ei.m_initial, '. ', ei.lname) AS full_name";

    if ($noFilters) {
        $sql .= ", et.email, et.contact_no, et.department";
    } else {
        if ($campus !== "None") $sql .= ", et.campus";
        if ($dept !== "None")   $sql .= ", et.department";
        if ($size !== "None")   $sql .= ", ei.size";
        if ($gender !== "None") $sql .= ", ei.gender";
    }

    $sql .= " FROM employee_info ei
              INNER JOIN employee_tbl et ON ei.id = et.id
              WHERE 1=1";

    // Build WHERE conditions for specific filters (not Show All)
    if ($campus !== "None" && $campus !== "Show All") {
        $campus = $con->real_escape_string($campus);
        $sql .= " AND et.campus = '$campus'";
    }
    if ($dept !== "None" && $dept !== "Show All") {
        $dept = $con->real_escape_string($dept);
        $sql .= " AND et.department = '$dept'";
    }
    if ($size !== "None" && $size !== "Show All") {
        $size = $con->real_escape_string($size);
        $sql .= " AND ei.size = '$size'";
    }
    if ($gender !== "None" && $gender !== "Show All") {
        $gender = $con->real_escape_string($gender);
        $sql .= " AND ei.gender = '$gender'";
    }

    $result = $con->query($sql);

    // Gender count query
    $genderCountSql = "SELECT ei.gender, COUNT(*) as count
                       FROM employee_info ei
                       INNER JOIN employee_tbl et ON ei.id = et.id
                       WHERE 1=1";

    // Apply the same filters as the main query
    if ($campus !== "None" && $campus !== "Show All") {
        $genderCountSql .= " AND et.campus = '$campus'";
    }
    if ($dept !== "None" && $dept !== "Show All") {
        $genderCountSql .= " AND et.department = '$dept'";
    }
    if ($size !== "None" && $size !== "Show All") {
        $genderCountSql .= " AND ei.size = '$size'";
    }
    if ($gender !== "None" && $gender !== "Show All") {
        $genderCountSql .= " AND ei.gender = '$gender'";
    }

    $genderCountSql .= " GROUP BY ei.gender";
    $genderCountResult = $con->query($genderCountSql);

    // Size count query
    $sizeCountSql = "SELECT ei.size, COUNT(*) as count
                     FROM employee_info ei
                     INNER JOIN employee_tbl et ON ei.id = et.id
                     WHERE 1=1";

    // Apply the same filters as the main query
    if ($campus !== "None" && $campus !== "Show All") {
        $sizeCountSql .= " AND et.campus = '$campus'";
    }
    if ($dept !== "None" && $dept !== "Show All") {
        $sizeCountSql .= " AND et.department = '$dept'";
    }
    if ($size !== "None" && $size !== "Show All") {
        $sizeCountSql .= " AND ei.size = '$size'";
    }
    if ($gender !== "None" && $gender !== "Show All") {
        $sizeCountSql .= " AND ei.gender = '$gender'";
    }

    $sizeCountSql .= " GROUP BY ei.size";
    $sizeCountResult = $con->query($sizeCountSql);

    // Table output
    echo '<table class="table table-striped table-sm" id="employeeTable">';
    echo '<thead><tr>';
    echo '<th>Full Name</th>';

    if ($noFilters) {
        echo '<th>Email</th><th>Contact No</th><th>Department</th>';
    } else {
        if ($campus !== "None") echo '<th>Campus</th>';
        if ($dept !== "None")   echo '<th>Department</th>';
        if ($size !== "None")   echo '<th>Size</th>';
        if ($gender !== "None") echo '<th>Gender</th>';
    }

    echo '</tr></thead><tbody id="employeeTableBody">';

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($row['full_name']) . '</td>';

            if ($noFilters) {
                echo '<td>' . htmlspecialchars($row['email']) . '</td>';
                echo '<td>' . htmlspecialchars($row['contact_no']) . '</td>';
                echo '<td>' . htmlspecialchars($row['department']) . '</td>';
            } else {
                if ($campus !== "None") echo '<td>' . htmlspecialchars($row['campus']) . '</td>';
                if ($dept !== "None")   echo '<td>' . htmlspecialchars($row['department']) . '</td>';
                if ($size !== "None")   echo '<td>' . htmlspecialchars($row['size']) . '</td>';
                if ($gender !== "None") echo '<td>' . htmlspecialchars($row['gender']) . '</td>';
            }

            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="6">No matching records found</td></tr>
        ';
    }

    echo '</tbody></table>';

    // Display counts in a table
    echo '
    <div class="row mt-4">
    <div class="table-responsive">
    ';

    // Total rows
    echo '<table class="table table-bordered table-hover table-sm">';
    echo '<thead><tr><th colspan="2">Summary</th></tr></thead>';
    echo '<tbody>';
    echo '<tr><td colspan="2">Total Rows</td><td>' . ($result ? $result->num_rows : 0) . '</td></tr>';
    echo '</tbody>';
    echo '</table>
    ';

    // Gender counts
    echo '<table class="table table-bordered table-hover table-sm mt-3">';
    echo '<thead><tr><th colspan="2">Gender Counts</th></tr></thead>';
    echo '<tbody>';
    echo '<tr><th>Male</th><th>Female</th><th>LGBTQIA+</th><th>Others</th></tr>';
    echo '<tr>';
    $genders = ['Male' => 0, 'Female' => 0, 'LGBTQIA+' => 0, 'Others' => 0];
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
    echo '<thead><tr><th colspan="5">Size Counts</th></tr></thead>';
    echo '<tbody>';
    echo '<tr><th>S</th><th>M</th><th>L</th><th>XL</th><th>2XL</th><th>3XL</th><th>4XL</th></tr>';
    echo '<tr>';
    $sizes = ['S' => 0, 'M' => 0, 'L' => 0, 'XL' => 0, '2XL' => 0, '3XL' => 0, '4XL' => 0];
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

    echo '</div>
    </div>
    ';

    $con->close();
}

?>


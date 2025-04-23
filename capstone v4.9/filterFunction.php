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
        echo '<tr><td colspan="6">No matching records found</td></tr>';
    }

    echo '</tbody></table>';

    // Row count display
    echo '<div class="text-end mt-2 fw-bold">';
    echo 'Total rows: ' . ($result ? $result->num_rows : 0);
    echo '</div>';

    $con->close();

}
?>

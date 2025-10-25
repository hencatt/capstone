<?php
require_once '../../phpFunctions/gad_portal.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    http_response_code(403); // Forbidden
    die("Unauthorized access");
}


?>

<div class="table-responsive">
    <table class="table table-striped table-sm" id="employeeTable">
        <thead>
            <tr>
                <th colspan="2">Full Name</th>
                <th>Department</th>
                <th>Campus</th>
            </tr>
        </thead>
        <tbody id="employeeTableBody">
            <?php
            $con = newCon();
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
                    echo "<td colspan='2'>" . htmlspecialchars($row['full_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['department']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['campus']) . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='5'>No data available</td></tr>";
            }
            $con->close();
            ?>
        </tbody>
    </table>
</div>
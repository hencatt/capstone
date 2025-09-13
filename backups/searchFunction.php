<?php

$con = new mysqli("localhost", "root", "", "gad_portal");

// Check connection
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

// FOR EMPLOYEE TABLE
if (isset($_POST['employeeSearch'])) {
    $search = trim($_POST['employeeSearch']);

    if (!empty($search)) {
        // Add wildcard for partial match
        $searchLike = '%' . $search . '%';

        $sql = "SELECT 
                    CONCAT(ei.fname, ' ', ei.m_initial, '. ', ei.lname) AS full_name, 
                    et.email, 
                    et.contact_no, 
                    et.department, 
                    et.status 
                FROM employee_info ei
                INNER JOIN employee_tbl et ON ei.id = et.id
                WHERE CONCAT(ei.fname, ' ', ei.m_initial, '. ', ei.lname) LIKE ?
                    OR et.email LIKE ?
                    OR et.contact_no LIKE ?
                    OR et.department LIKE ?
                    OR et.status LIKE ?";

        $stmt = $con->prepare($sql);
        $stmt->bind_param("sssss", $searchLike, $searchLike, $searchLike, $searchLike, $searchLike);
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
    } else {
        // Query to join employee_info and employee_tbl with full name
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
            // Fetch and display each row
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

        $con->close();

    }
}   //END OF EMPLOYEE TABLE



$con->close();
?>
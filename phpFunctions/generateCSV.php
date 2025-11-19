<?php
require_once "./gad_portal.php";

if (isset($_POST['reportType'])) {
    $reportType = $_POST['reportType'];
    $campus = $_POST['campusFilter'] ?? 'None';
    $dept = $_POST['deptFilter'] ?? 'None';
    $size = $_POST['sizeFilter'] ?? 'None';
    $gender = $_POST['genderFilter'] ?? 'None';
    $showSummary = $_POST['showSummary'] ?? 'no';
    $search = $_POST['searchQuery'] ?? null;

    $con = newCon();

    if ($con->connect_error) {
        die("Connection failed: " . $con->connect_error);
    }

    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $reportType . '_report_' . date('Y-m-d_H-i-s') . '.csv"');
    header('Pragma: no-cache');
    header('Expires: 0');

    // Create output stream
    $output = fopen('php://output', 'w');

    // Add BOM for Excel UTF-8 compatibility
    fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

    if ($reportType === 'employee') {
        // Generate Employee CSV
        generateEmployeeCSV($output, $con, $campus, $dept, $size, $gender, $showSummary, $search);
    } elseif ($reportType === 'inventory') {
        // Generate Inventory CSV
        generateInventoryCSV($output, $con, $_POST['item_category'] ?? 'None');
    } elseif ($reportType === 'received_items') {
        // Generate Received Items CSV
        generateReceivedItemsCSV($output, $con, $_POST);
    }

    fclose($output);
    $con->close();
    exit();
}

function generateEmployeeCSV($output, $con, $campus, $dept, $size, $gender, $showSummary, $search) {
    // Check if no filters are selected
    $noFilters = ($campus === "None" && $dept === "None" && $size === "None" && $gender === "None");

    // Build SQL query
    $sql = "SELECT CONCAT(ei.fname, ' ', ei.lname) AS full_name, ei.id AS emp_id, et.email";

    if ($noFilters) {
        $sql .= ", et.campus, et.contact_no, et.department";
    } else {
        if ($campus !== "None") $sql .= ", et.campus";
        if ($dept !== "None") $sql .= ", et.department";
        if ($size !== "None") $sql .= ", ei.size";
        if ($gender !== "None") $sql .= ", ei.gender";
    }

    $sql .= " FROM employee_info ei
              INNER JOIN employee_tbl et ON ei.id = et.id
              WHERE et.status = 'Active'";

    if (!empty($search)) {
        $search = $con->real_escape_string($search);
        $sql .= " AND (
            ei.fname LIKE '%$search%' OR
            ei.m_initial LIKE '%$search%' OR
            ei.lname LIKE '%$search%' OR
            CONCAT(ei.fname, ' ', ei.m_initial, '. ', ei.lname) LIKE '%$search%'
        )";
    }

    // Apply filters
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

    // Write CSV headers
    $headers = ['Full Name', 'Email'];
    if ($noFilters) {
        $headers = array_merge($headers, ['Campus', 'Contact No', 'Department']);
    } else {
        if ($campus !== "None") $headers[] = 'Campus';
        if ($dept !== "None") $headers[] = 'Department';
        if ($size !== "None") $headers[] = 'Size';
        if ($gender !== "None") $headers[] = 'Gender';
    }
    fputcsv($output, $headers);

    // Write data rows
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $csvRow = [$row['full_name'], $row['email']];
            
            if ($noFilters) {
                $csvRow[] = $row['campus'];
                $csvRow[] = $row['contact_no'];
                $csvRow[] = $row['department'];
            } else {
                if ($campus !== "None") $csvRow[] = $row['campus'];
                if ($dept !== "None") $csvRow[] = $row['department'];
                if ($size !== "None") $csvRow[] = $row['size'];
                if ($gender !== "None") $csvRow[] = $row['gender'];
            }
            
            fputcsv($output, $csvRow);
        }
    }

    // Add summary if requested
    if ($showSummary === 'yes') {
        fputcsv($output, []); // Empty row
        fputcsv($output, ['SUMMARY']);
        fputcsv($output, []); // Empty row

        // Total count
        $countSql = "SELECT COUNT(*) as total FROM employee_info ei
                     INNER JOIN employee_tbl et ON ei.id = et.id
                     WHERE et.status = 'Active'";
        
        if ($campus !== "None" && $campus !== "Show All") {
            $countSql .= " AND et.campus = '$campus'";
        }
        if ($dept !== "None" && $dept !== "Show All") {
            $countSql .= " AND et.department = '$dept'";
        }
        if ($size !== "None" && $size !== "Show All") {
            $countSql .= " AND ei.size = '$size'";
        }
        if ($gender !== "None" && $gender !== "Show All") {
            $countSql .= " AND ei.gender = '$gender'";
        }

        $countResult = $con->query($countSql);
        $totalRecords = $countResult->fetch_assoc()['total'];
        fputcsv($output, ['Total Records', $totalRecords]);
        fputcsv($output, []); // Empty row

        // Gender counts
        $genderCountSql = "SELECT ei.gender, COUNT(*) as count
                           FROM employee_info ei
                           INNER JOIN employee_tbl et ON ei.id = et.id
                           WHERE et.status = 'Active'";
        
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

        fputcsv($output, ['Gender Counts']);
        fputcsv($output, ['Male', 'Female', 'LGBTQIA+', 'Others']);
        
        $genders = ['Male' => 0, 'Female' => 0, 'LGBTQIA+' => 0, 'Others' => 0];
        if ($genderCountResult && $genderCountResult->num_rows > 0) {
            while ($row = $genderCountResult->fetch_assoc()) {
                $genders[$row['gender']] = $row['count'];
            }
        }
        fputcsv($output, array_values($genders));
        fputcsv($output, []); // Empty row

        // Size counts
        $sizeCountSql = "SELECT ei.size, COUNT(*) as count
                         FROM employee_info ei
                         INNER JOIN employee_tbl et ON ei.id = et.id
                         WHERE et.status = 'Active'";
        
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

        fputcsv($output, ['Size Counts']);
        fputcsv($output, ['S', 'M', 'L', 'XL', '2XL', '3XL', '4XL']);
        
        $sizes = ['S' => 0, 'M' => 0, 'L' => 0, 'XL' => 0, '2XL' => 0, '3XL' => 0, '4XL' => 0];
        if ($sizeCountResult && $sizeCountResult->num_rows > 0) {
            while ($row = $sizeCountResult->fetch_assoc()) {
                $sizes[$row['size']] = $row['count'];
            }
        }
        fputcsv($output, array_values($sizes));
    }
}

function generateInventoryCSV($output, $con, $category) {
    // Build SQL query
    $sql = "SELECT itemName, itemQuantity, itemSize, itemDesc, itemCategory 
            FROM inventory_tbl";

    if ($category !== "None") {
        $sql .= " WHERE itemCategory = ?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("s", $category);
    } else {
        $stmt = $con->prepare($sql);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    // Write CSV headers
    fputcsv($output, ['Item Name', 'Quantity', 'Size', 'Description', 'Status', 'Category']);

    // Write data rows
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $status = ($row['itemQuantity'] <= 0) ? 'Out of Stock' : 'In Stock';
            fputcsv($output, [
                $row['itemName'],
                $row['itemQuantity'],
                $row['itemSize'],
                $row['itemDesc'],
                $status,
                $row['itemCategory']
            ]);
        }
    }

    $stmt->close();
}

function generateReceivedItemsCSV($output, $con, $postData) {
    $name = $postData['name'] ?? '';
    $itemId = $postData['itemId'] ?? '';
    $received = $postData['received'] ?? '';
    $distributed = $postData['distributed'] ?? '';

    // Initialize itemName variable
    $itemName = 'Unknown Item';
    
    // Get item name from ID
    if (!empty($itemId)) {
        $stmt = $con->prepare("SELECT itemName FROM inventory_tbl WHERE id = ?");
        $stmt->bind_param("i", $itemId);
        $stmt->execute();
        $stmt->bind_result($itemName);
        $stmt->fetch();
        $stmt->close();
    }

    $remaining = $received - $distributed;

    // Write CSV headers
    fputcsv($output, ['Received Items Report']);
    fputcsv($output, []); // Empty row
    fputcsv($output, ['Name', $name]);
    fputcsv($output, ['Item', $itemName]);
    fputcsv($output, []); // Empty row
    fputcsv($output, ['Received', 'Distributed', 'Remaining']);
    fputcsv($output, [$received, $distributed, $remaining]);
}
?>
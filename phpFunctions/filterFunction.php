<?php
require_once "gad_portal.php";

if (isset($_POST['searchQuery'], $_POST['campusFilter'], $_POST['deptFilter'], $_POST['sizeFilter'], $_POST['genderFilter'], $_POST['showSummary'], $_POST['showReceipt'], $_POST['whatGenerate'], $_POST['currentPosition'])) {
    $campus = $_POST['campusFilter'];
    $dept = $_POST['deptFilter'];
    $size = $_POST['sizeFilter'];
    $gender = $_POST['genderFilter'];
    $summary = $_POST['showSummary'];
    $receipt = $_POST['showReceipt'];
    $position = $_POST['currentPosition'];
    $generate = $_POST['whatGenerate'];

    $search = $_POST['searchQuery'];


    $con = newCon();

    if ($con->connect_error) {
        die("Connection failed: " . $con->connect_error);
    }

    // Check if no filters are selected
    $noFilters = ($campus === "None" && $dept === "None" && $size === "None" && $gender === "None");

    // Dynamically build SELECT fields
    $sql = "SELECT CONCAT(ei.fname, ' ', ei.m_initial, '. ', ei.lname) AS full_name, ei.id AS emp_id";

    if ($noFilters) {
        $sql .= ", et.campus, et.email, et.contact_no, et.department";
    } else {
        if ($campus !== "None")
            $sql .= ", et.campus";
        if ($dept !== "None")
            $sql .= ", et.department";
        if ($size !== "None")
            $sql .= ", ei.size";
        if ($gender !== "None")
            $sql .= ", ei.gender";
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

    if ($receipt === "yes") {
        echo <<<EOD
        <div class="row mt-3">
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
                <div class="div" style="height: 20px; background-color: #003464; width="></div>
            </div>
        <div class="row">
                <div class="div" style="height: 20px; background-color:rgb(235, 159, 46); width="></div>
            </div>

    </div>
    EOD;
    }

    // Table output
    echo '<table class="table table-striped table-sm" id="employeeTable">';
    echo '<thead><tr>';
    echo '<th>Full Name</th>';

    if ($noFilters) {
        echo '<th>Campus</th><th>Department</th>';
    } else {
        if ($campus !== "None")
            echo '<th>Campus</th>';
        if ($dept !== "None")
            echo '<th>Department</th>';
        if ($size !== "None")
            echo '<th>Size</th>';
        if ($gender !== "None")
            echo '<th>Gender</th>';
    }
    if ($generate === "report" && $position === "Focal Person") {
        echo '<th>Signature</th>';
    }

    if ($generate !== "report") {
        // ==================================
        // ADD EXTRA HEADER HERE
        echo '<th>Actions</th>';
        // ==================================
    }

    echo '</tr></thead><tbody id="employeeTableBody">';

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($row['full_name']) . '</td>';

            if ($noFilters) {
                echo '<td>' . htmlspecialchars($row['campus']) . '</td>';
                echo '<td>' . htmlspecialchars($row['department']) . '</td>';
                if ($generate === "report" && $position === "Focal Person") {
                    echo '<td></td>';
                }
            } else {
                if ($campus !== "None")
                    echo '<td>' . htmlspecialchars($row['campus']) . '</td>';
                if ($dept !== "None")
                    echo '<td>' . htmlspecialchars($row['department']) . '</td>';
                if ($size !== "None")
                    echo '<td>' . htmlspecialchars($row['size']) . '</td>';
                if ($gender !== "None")
                    echo '<td>' . htmlspecialchars($row['gender']) . '</td>';
            }
            if ($generate === "report" && $position === "Focal Person") {
                echo '<td></td>';
            }


            if ($generate !== "report") {
                // ==================================
                // ADD EXTRA BUTTONS / MORE HERE
                $idAttr = htmlspecialchars($row['emp_id']);
                echo '<td>
                    <button type="button" class="btn btn-outline-primary btn-sm view-btn me-1"
                        data-id="' . $idAttr . '" data-bs-toggle="modal" data-bs-target="#viewEmployeeModal"
                        title="View Details">
                        <i class="fas fa-eye"></i> View
                    </button>

                    <button type="button"
                                    class="btn btn-outline-success btn-sm editEmployeeBtn"
                                    data-id="' . $idAttr . '" title="Edit Record">
                                <i class="fas fa-edit"></i> Edit
                    </button>

                    <button type="button" class="btn btn-outline-danger btn-sm delete-btn"
                        data-id="' . $idAttr . '" title="Delete Record">
                        <i class="fas fa-trash"></i> Delete
                    </button>

                    <button type="button" class="btn btn-outline-secondary btn-sm assignBtn">
                    Assign
                    </button>

                </td>';


                echo file_get_contents(__DIR__ . '/../Users/reusableHTML/viewEmployeeModal.php');

                echo <<<JS
<script>
$(document).off('click', '.view-btn').on('click', '.view-btn', function() {
    const id = $(this).data('id');
    if (!id) {
        alert('No employee ID found');
        return;
    }

    $.post('../phpFunctions/getEmployeeDetails.php', { id: id }, function(resp) {
        console.log("Server Response:", resp);

        if (!resp || resp.error) {
            alert(resp ? resp.error : 'Failed to load details');
            return;
        }

        // Populate fields
        $('#v_full_name').text((resp.fname || '') + ' ' + (resp.m_initial ? resp.m_initial + '. ' : '') + (resp.lname || ''));
        $('#v_email').text(resp.email || '');
        $('#v_contact').text(resp.contact_no || '');
        $('#v_department').text(resp.department || '');
        $('#v_campus').text(resp.campus || '');
        $('#v_address').text(resp.address || '');
        $('#v_birthday').text(resp.birthday || '');
        $('#v_marital_status').text(resp.marital_status || '');
        $('#v_sex').text(resp.sex || '');
        $('#v_gender').text(resp.gender || '');
        $('#v_priority_status').text(resp.priority_status || '');
        $('#v_size').text(resp.size || '');
        $('#v_income').text(resp.income || '');
        $('#v_children_num').text(resp.children_num || '');
        $('#v_concern').text(resp.concern || '');
    }, 'json').fail(function() {
        alert('Request failed');
    });
});

$(document).off('click', '.delete-btn').on('click', '.delete-btn', function() {
    const id = $(this).data('id');
    if (!id) return;

    if (!confirm('Delete this record?')) return;

    $.post('../phpFunctions/deleteEmployee.php', { id: id }, function(resp) {
        if (resp && resp.success) {
            alert(resp.message);
            // Instead of reloading, remove the row:
            $(`button.delete-btn[data-id='\${id}']`).closest('tr').fadeOut(300, function(){ $(this).remove(); });
        } else {
            alert(resp && resp.error ? resp.error : 'Delete failed');
        }
    }, 'json').fail(() => alert('Delete request failed'));
});
</script>
JS;
            }



            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="6">No matching records found</td></tr>
        ';
    }

    echo '</tbody></table>';

    // Display counts in a table
    if ($summary === "yes") {
        echo '
    <div class="row mt-4">
    <div class="table-responsive">
    ';

        // Total rows
        echo '<table class="table table-bordered table-hover table-sm">';
        echo '<thead><tr><th colspan="2">Summary</th></tr></thead>';
        echo '<tbody>';
        echo '<tr><td>Total Rows</td><td>' . ($result ? $result->num_rows : 0) . '</td></tr>';
        echo '</tbody>';
        echo '</table>
    ';

        // Gender counts
        echo '<table class="table table-bordered table-hover table-sm mt-3">';
        echo '<thead><tr><th colspan="4">Gender Counts</th></tr></thead>';
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
        echo '<thead><tr><th colspan="7">Size Counts</th></tr></thead>';
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
    }

    if ($receipt === "yes") {
        echo <<<EOD
    <div class="row py-5" style="background-color: white;">
        <div class="col"></div>
        <div class="col d-flex flex-column gap-1 align-items-center justify-content-center">
            <div style="height: 3px; background-color: black; width:300px"></div>
            <h6>Signature Over Printed Name</h6>
        </div>
    </div>
    EOD;
    }

    $con->close();
}

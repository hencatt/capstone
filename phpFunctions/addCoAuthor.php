<?php
require_once '../phpFunctions/gad_portal.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    http_response_code(403); // Forbidden
    die("Unauthorized access");
}


?>

<div style="position: sticky; top:0; background-color:white; z-index: 10; padding: 22px 30px">
    <div class="row">
        <div class="col d-flex justify-content-end">
            <button class="btn btn-outline-secondary btn-sm" id="closeCoAuthorModal">
                <span class="material-symbols-outlined">
                    close
                </span>
            </button>
        </div>
    </div>
    <div class="row mt-4">
        <div class="col">
            <input type="search" class="form-control" placeholder="search..." id="inputSearch" name="inputSearch">
        </div>
    </div>
</div>
<div class="row mt-3" style="padding: 0px 30px;">
    <div class="col">
        <!-- loop here -->
        <?php
        function structure($userFullName, $fname, $mname, $lname)
        {
                        echo <<<EOD
                <div class="row employeeRow" 
                    data-fname="$fname" 
                    data-mname="$mname" 
                    data-lname="$lname" 
                    style="
                    cursor:pointer;
                    padding-top: 15px;
                    padding-bottom: 20px;  
                    border-bottom: 1px solid black;
                    "
                    onmouseover="this.style.backgroundColor='#6565653a';"
                    onmouseout="this.style.backgroundColor='';"
                    ">
                    <div class="col-2 d-flex align-items-center justify-content-center">
                        <span class="material-symbols-outlined">account_circle</span>
                    </div>
                    <div class="col d-flex align-items-center">
                        $userFullName
                    </div>
                </div>
            EOD;
        }


        $con = newCon();
        if ($con->connect_error) {
            die("Connection failed: " . $con->connect_error);
        }
        // Query to join employee_info and employee_tbl with full name
        $sql = "SELECT
                CONCAT(ei.fname, ' ', ei.m_initial, '. ', ei.lname) AS full_name,
                fname,
                m_initial,
                lname
            FROM employee_info ei
            INNER JOIN employee_tbl et ON ei.id = et.id";
        $result = $con->query($sql);
        if ($result->num_rows > 0) {
            // Fetch and display each row
            while ($row = $result->fetch_assoc()) {

                structure(htmlspecialchars($row['full_name']), htmlspecialchars($row['fname']), htmlspecialchars($row['m_initial']), htmlspecialchars($row['lname']));
            }
        }
        ?>

    </div>
</div>
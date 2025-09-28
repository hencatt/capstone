<?php
require_once "gad_portal.php";

date_default_timezone_set("Asia/Manila");

$date = date("Y-m-d");

$sql = "SELECT announceTitle, announceDate 
        FROM announcement_tbl 
        ORDER BY ABS(DATEDIFF(announceDate, '$date')) 
        LIMIT 1";
$result = $con->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();

    // Compare dates without escaping
    if ($row["announceDate"] < $date) {
        $sql = "SELECT announceTitle, announceDate 
                FROM announcement_tbl 
                ORDER BY ABS(DATEDIFF(announceDate, '$date')) 
                LIMIT 1,1";
        $result = $con->query($sql);
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
        }
    }

    // Escape only when storing for display
    $closestEventDate = htmlspecialchars($row["announceDate"]);
    $closestEventTitle = htmlspecialchars($row["announceTitle"]);
} else {
    $closestEventDate = "0-0-0";
    $closestEventTitle = "";
}

// to make the date more readable
$explodeClosestDate = explode("-", $closestEventDate);
$explodeCurrentDate = explode("-", $date);

switch ($explodeClosestDate[1]) {
    case "01":
        $explodeClosestDate[1] = "Jan";
        break;
    case "02":
        $explodeClosestDate[1] = "Feb";
        break;
    case "03":
        $explodeClosestDate[1] = "Mar";
        break;
    case "04":
        $explodeClosestDate[1] = "Apr";
        break;
    case "05":
        $explodeClosestDate[1] = "May";
        break;
    case "06":
        $explodeClosestDate[1] = "June";
        break;
    case "07":
        $explodeClosestDate[1] = "July";
        break;
    case "08":
        $explodeClosestDate[1] = "Aug";
        break;
    case "09":
        $explodeClosestDate[1] = "Sept";
        break;
    case "10":
        $explodeClosestDate[1] = "Oct";
        break;
    case "11":
        $explodeClosestDate[1] = "Nov";
        break;
    case "12":
        $explodeClosestDate[1] = "Dec";
        break;
}

$formattedClosestDate = $explodeClosestDate[1] . ". " . $explodeClosestDate[2] . ", " . $explodeClosestDate[0];
$formattedCurrentDate = $explodeCurrentDate[1] . ". " . $explodeCurrentDate[2] . ", " . $explodeCurrentDate[0];

function topbar($user, $role, $location, $pageTitle = null)
{
    if ($GLOBALS['closestEventDate'] < $GLOBALS['date']) {
        $eventStatus = "<label>No upcoming events</label>";
    } elseif ($GLOBALS['closestEventDate'] != $GLOBALS['date']) {
        $eventStatus = "<label>Event: <b>{$GLOBALS['closestEventTitle']}</b> on {$GLOBALS['formattedClosestDate']}</label>";
    } else {
        $eventStatus = "<label>Event: <b>{$GLOBALS['closestEventTitle']}</b></label>";
    }

    $backgroundColor = "white";

    echo
    <<<EOD
        
        <div class="row gap-5" id="topBar">
                    <div class="col-6 d-flex flex-row align-items-center">
    EOD;

    $separatorSymbol = ">";

    switch ($location) {
        case "dashboard":
            $category = "Home";
            $locationLabel = "Dashboard";
            break;
        case "events":
            $category = "General";
            $locationLabel = "Events";
            break;
        case "eventDetails":
            $category = "General";
            $locationLabel = $pageTitle ?? "Event Details";
            break;
        case "researchDetails":
            $category = "General";
            $locationLabel = $pageTitle ?? "Research Details";
            break;
        case "report":
            $category = "Settings";
            $locationLabel = "Generate Report";
            break;
        case "researchSubmit":
            $category = "General";
            $locationLabel = "Submit Research";
            break;
        case "researchView":
            $category = "General";
            $locationLabel = "View Researches";
            break;
        case "announcement":
            $category = "General";
            $locationLabel = "Announcement";
            break;
        case "loading":
            $category = "";
            $locationLabel = "";
            $separatorSymbol = "";
            break;
        case "logs":
            $category = "General";
            $locationLabel = "View Logs";
            break;
        case "employees":
            $category = "General";
            $locationLabel = "Employees";
            break;
        case "inventory":
            $category = "General";
            $locationLabel = "Inventory";
            break;
        default:
            $category = "";
            $locationLabel = "";
            break;
    }
    if ($location === "eventDetails" && !is_null($pageTitle)) {
        echo '<label>' . $category . ' ' . $separatorSymbol . ' <a href="events.php" 
        style="cursor: pointer;"
        onmouseover="this.style.textDecoration=`underline`"
        onmouseout="this.style.textDecoration=`none`">Events</a> ' . $separatorSymbol . ' <b style="text-decoration: underline;">' . $locationLabel . '</b></label>';
    } else if ($location === "researchDetails" && !is_null($pageTitle)) {
        echo '<label>' . $category . ' ' . $separatorSymbol . ' <a href="researchView.php" 
        style="cursor: pointer;"
        onmouseover="this.style.textDecoration=`underline`"
        onmouseout="this.style.textDecoration=`none`">View Researches</a> ' . $separatorSymbol . ' <b style="text-decoration: underline;">' . $locationLabel . '</b></label>';
    } else {
        echo '<label>' . $category . ' ' . $separatorSymbol . ' <b style="text-decoration: underline;">' . $locationLabel . '</b></label>';
    }

    echo
    <<<EOD
                    </div>
                    <div class="col-1 d-flex flex-row align-items-center justify-content-center"
                    style="
                    
                    "
                    >
                    
                        <div class="wrapper">
                            <button id="notifyBtn" class="btn btn-outline">
                                    <span class="material-symbols-outlined">notifications</span>
                            </button>
                            <div class="notifications">
                                $eventStatus
                            </div>
                        </div>
                        
                    </div>
                    <div class="col-auto d-flex flex-row align-items-center justify-content-center" id="profile">
                        <a href="/capstone/Users/modifyAccount.php">
                            <div class="row align-items-center justify-content-center gap-3">
                                <div class="col-1">
                                    <span class="material-symbols-outlined" id="notifIcon">account_circle</span>
                                </div>
                                <div class="col d-flex flex-column align-items-start justify-content-start">
                                    <label class="fw-semibold">$user</label>
                                    <label class="fw-light fst-italic">$role</label>
                                </div>
                            </div>
                        </a>
                    </div>
            </div>
    EOD;
}


function sidebar($active, $role)
{
    $dashboardOption = "";
    $employeeOption = "";
    $inventoryOption = "";
    $eventsOption = "";
    $researchOption = "";
    $reportOption = "";
    $accountOption = "";
    $logoutOption = "";
    $researchViewOption = "";
    $announcementOption = "";
    $viewLogsOption = "";

    $sidebarHighlight = "background-color:rgba(145, 152, 255, 0.66); border-radius:10px;";
    $dashboardStyle = "";
    $eventStyle = "";
    $researchStyle = "";
    $employeeStyle = "";
    $inventoryStyle = "";
    $accountStyle = "";
    $reportStyle = "";
    $researchViewStyle = "";
    $announcementStyle = "";
    $viewLogsStyle = "";

    $destination = "#";

    switch ($active) {
        case "dashboard":
            $dashboardOption = "active";
            $dashboardStyle = $sidebarHighlight;
            break;
        case "employees":
            $employeeOption = "active";
            $employeeStyle = $sidebarHighlight;
            break;
        case "inventory":
            $inventoryOption = "active";
            $inventoryStyle = $sidebarHighlight;
            break;
        case "events":
            $eventsOption = "active";
            $eventStyle = $sidebarHighlight;
            break;
        case "research";
            $researchOption = "active";
            $researchStyle = $sidebarHighlight;
            break;
        case "account";
            $accountOption = "active";
            $accountStyle = $sidebarHighlight;
            break;
        case "report":
            $reportOption = "active";
            $reportStyle = $sidebarHighlight;
            break;
        case "researchView":
            $researchViewOption = "active";
            $researchViewStyle = $sidebarHighlight;
            break;
        case "announcement":
            $announcementOption = "active";
            $announcementStyle = $sidebarHighlight;
            break;
        case "viewLogs":
            $viewLogsOption = "active";
            $viewLogsStyle = $sidebarHighlight;
    };

    if ($role === "Director") {
        $destination = "director.php";
    } else if ($role === "Focal Person") {
        $destination = "focalPerson.php";
    } else if ($role === "Technical Assistant") {
        $destination = "TA.php";
    } else if ($role === "RET Chair") {
        $destination = "retChair.php";
    } else {
        $destination = "../index.php";
    }

    // SIDEBAR SOURCE / MAP
    $sidebar = [
        "wrapperTop" => '
        <div class="row mt-5 ms-2 sidebarOptions">
                <div class="col d-flex flex-column gap-1">
        ',

        "logo" => '
        <div class="row mt-lg-3 topLeft">
                            <div class="col d-flex justify-content-center neustLogo" style="cursor: pointer;">
                                <!--
                                    <img alt="GADLogo" class="logo" src="../assets/recreate.png">
                                -->
                                <img alt="GADLogo" class="logo" src="../assets/maleFemaleD.png">
                            </div>
                            <div class="Title" style="text-align: center;" style="cursor: pointer;">
                                <br>
                                <a href="' . $destination . '" style="color: white;">
                                    <h4>GAD Portal</h4>
                                </a>
                            </div>
                    </div>
        ',

        "dashboard" => '
        <a href="' . $destination . '" class="categoryItem"id="' . $dashboardOption . '" style="' . $dashboardStyle . '"><li>
                            <span class="material-symbols-outlined">dashboard</span>
                            Dashboard</li></a>
        ',

        "announcement" => '
        <a href="announcement.php" class="categoryItem" id="' . $announcementOption . '" style="' . $announcementStyle . '"><li >
                            <span class="material-symbols-outlined">
                            campaign</span>
                            Announcement</li></a>
        ',

        "employees" => '
        <a href="employees.php" class="categoryItem" id="' . $employeeOption . '" style="' . $employeeStyle . '"><li>
                            <span class="material-symbols-outlined">groups</span>
                            Employees
                            </li></a>
        ',

        "inventory" => '
        <a href="inventory.php" class="categoryItem" id="' . $inventoryOption . '" style="' . $inventoryStyle . '"><li>
                            <span class="material-symbols-outlined">inventory_2</span>
                            Inventory
                            </li></a>
        ',

        "events" => '
        <a href="events.php" class="categoryItem" id="' . $eventsOption . '" style="' . $eventStyle . '"><li>
                            <span class="material-symbols-outlined">event</span>
                            Events
                            </li></a>
        ',

        "researchView" => '
        <a href="researchView.php" class="categoryItem" id="' . $researchViewOption . '" style="' . $researchViewStyle . '"><li>
                            <span class="material-symbols-outlined">
                            visibility</span>
                            View Researches
                            </li></a>
        ',

        "researchSubmit" => '
        <a href="submitResearch.php" class="categoryItem" id="' . $researchOption . '" style="' . $researchStyle . '"><li>
                            <span class="material-symbols-outlined">menu_book</span>
                            Research
                            </li></a>
        ',

        "logs" => '
        <a href="viewLogs.php" class="categoryItem" id="' . $viewLogsOption . '" style="' . $viewLogsStyle . '"><li >
                            <span class="material-symbols-outlined">
                            overview
                            </span>
                            View Logs</li></a>
        ',

        "report" => '
        <a href="generateReport.php" class="categoryItem" id="' . $reportOption . '" style="' . $reportStyle . '"><li>
                            <span class="material-symbols-outlined">description</span>
                            Generate Report
                            </li></a>
        ',

        "logout" => '
        <a href="../logout.php?logout=true" class="categoryItem" id="' . $logoutOption . '"><li>
                            <span class="material-symbols-outlined">logout</span>
                            Logout
                            </li></a>
        ',

        "wrapperBottom" => '
        </div>
            </div>
        ',

        "category.home" => '
        <label class="category">Home</label>
        ',

        "category.general" => '
        <label class="category">General</label>
        ',

        "category.settings" => '
        <label class="category">Settings</label>
        ',

    ];

    // Focal Person Sidebar
    if ($role === "Focal Person") {
        echo $sidebar['logo'];
        echo $sidebar['wrapperTop'];
        echo $sidebar['category.home'];
        echo $sidebar['dashboard'];
        echo $sidebar['category.general'];
        echo $sidebar['employees'];
        echo $sidebar['inventory'];
        echo $sidebar['events'];
        echo $sidebar['researchView'];
        echo $sidebar['category.settings'];
        echo $sidebar['report'];
        echo $sidebar['logout'];
        echo $sidebar['wrapperBottom'];
    }

    // RET CHAIR SIDEBAR
    if ($role === "RET Chair") {
        echo $sidebar['logo'];
        echo $sidebar['wrapperTop'];
        echo $sidebar['category.home'];
        // echo $sidebar['dashboard'];
        // echo $sidebar['category.general'];
        echo $sidebar['announcement'];
        echo $sidebar['events'];
        // echo $sidebar['researchView'];
        echo $sidebar['category.settings'];
        echo $sidebar['logout'];
        echo $sidebar['wrapperBottom'];
    }


    // Director and TechnicalAssistant Sidebar
    if ($role === "Director" || $role === "Technical Assistant") {
        echo $sidebar['logo'];
        echo $sidebar['wrapperTop'];
        echo $sidebar['category.home'];
        echo $sidebar['dashboard'];
        echo $sidebar['category.general'];
        echo $sidebar['announcement'];
        echo $sidebar['employees'];
        echo $sidebar['inventory'];
        echo $sidebar['events'];
        echo $sidebar['researchView'];
        echo $sidebar['logs'];
        echo $sidebar['category.settings'];
        echo $sidebar['report'];
        echo $sidebar['logout'];
        echo $sidebar['wrapperBottom'];
    }

    if ($role === "Researcher") {
        echo $sidebar['logo'];
        echo $sidebar['wrapperTop'];
        echo $sidebar['category.general'];
        echo $sidebar['events'];
        echo $sidebar['researchSubmit'];
        echo $sidebar['researchView'];
        echo $sidebar['category.settings'];
        echo $sidebar['logout'];
        echo $sidebar['wrapperBottom'];
    }
};

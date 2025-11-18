<?php
require_once 'includes.php';

session_start();

// Prevent browser caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

checkUser($_SESSION['user_id']);
doubleCheck("Director");
// Returns to login if not director

$user = getUser();
$currentUser = $user['fullname'];
$currentPosition = $user['position'];
$currentDepartment = $user['department'];
$currentCampus = $user['campus'];


$message = ''; // Initialize a message variable

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['fname'])) {
        // Add Account Logic
        $fname = $_POST['fname'];
        $lname = $_POST['lname'];
        $email = $_POST['email'];
        $username = $_POST['username'];
        $password = password_hash($_POST['pass'], PASSWORD_DEFAULT);
        $position = $_POST['pos'];
        $department = $_POST['dept'];
        $campus = $_POST['campus'];

        $conn = newCon();

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Check if the email already exists
        $checkEmailQuery = "SELECT email FROM accounts_tbl WHERE email = '$email'";
        $result = $conn->query($checkEmailQuery);

        if ($result->num_rows > 0) {
            echo "<script>alert('Error: The email address is already in use.');</script>";
        } else {
            // Insert the new account
            $sql = "INSERT INTO accounts_tbl (fname, lname, email, username, pass, position, department, campus, date_created, is_active) 
                        VALUES ('$fname', '$lname', '$email', '$username', '$password', '$position' , '$department', '$campus', NOW(), 1)";

            if ($conn->query($sql)) {
                // Log insert

                echo "<script>alert('Account Created!');</script>";
            } else {
                echo "<script>alert('Error: " . $conn->error . "');</script>";
            }
        }

        $conn->close();
    } elseif (isset($_POST['id'])) {
        // Deactivate User Logic
        $userId = $_POST['id'];

        $conn = newCon();

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $sql = "UPDATE accounts_tbl SET is_active = 0 WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);

        if ($stmt->execute()) {
            // insert log
            insertLog($currentUser, "Created New Account", date('Y-m-d H:i:s'));

            echo "User deactivated successfully.";
        } else {
            echo "Error: " . $conn->error;
        }

        $stmt->close();
        $conn->close();
    }
    // Update Account Logic
    if (isset($_POST['update_account'])) {
        $userId = $_POST['edit_id'];
        $fname = $_POST['edit_fname'];
        $lname = $_POST['edit_lname'];
        $username = $_POST['edit_username'];
        $email = $_POST['edit_email'];
        $password = !empty($_POST['edit_password']) ? password_hash($_POST['edit_password'], PASSWORD_DEFAULT) : null;
        $position = $_POST['edit_position'];
        $department = $_POST['edit_department'];
        $campus = $_POST['edit_campus'];

        $conn = newCon();

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        if ($password) {
            $sql = "UPDATE accounts_tbl SET fname = ?, lname = ?, username = ?, email = ?, pass = ?, position = ?, department = ?, campus = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssssi", $fname, $lname, $username, $email, $password, $position, $department, $campus, $userId);
        } else {
            $sql = "UPDATE accounts_tbl SET fname = ?, lname = ?, username = ?, email = ?, position = ?, department = ?, campus = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssssi", $fname, $lname, $username, $email, $position, $department, $campus, $userId);
        }

        if ($stmt->execute()) {
            echo "<script>alert('Account updated successfully!');</script>";
        } else {
            echo "<script>alert('Error updating account: " . $conn->error . "');</script>";
        }

        $stmt->close();
        $conn->close();

        // Redirect to the same page to prevent form resubmission
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

$sql = 'SELECT * FROM employee_tbl';
$result = $con->query($sql);
$totalEmployee = $result->num_rows;

$totalMale = "";
$totalFemale = "";
$totalLGBT = "";

function getGender($gender)
{
    $con = newCon();

    $stmt = $con->prepare("SELECT COUNT(*) as total FROM employee_info WHERE gender = ?");
    $stmt->bind_param("s", $gender);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    return $result['total'];
}

$totalMale = getGender("Male");
$totalFemale = getGender("Female");
$totalLGBT = getGender("LGBTQIA+");

function getAge($fromAge, $toAge)
{
    $con = newCon();

    $stmt = $con->prepare("SELECT COUNT(*) as total FROM employee_info WHERE TIMESTAMPDIFF(YEAR, birthday, CURDATE()) BETWEEN ? AND ?");
    $stmt->bind_param("ii", $fromAge, $toAge);
    $stmt->execute();

    $result = $stmt->get_result()->fetch_assoc();
    return $result["total"];
}

$age18_24 = getAge(18, 24);
$age25_34 = getAge(25, 34);
$age35_44 = getAge(35, 44);
$age45_54 = getAge(35, 44);
$age55_64 = getAge(35, 44);
$age65_abv = getAge(65, 125);

function getDepartmentBreakdown($campus = '__all__')
{
    $con = newCon();

    $baseSql = "SELECT
            COALESCE(t.department, 'Unknown') AS department,
            COUNT(DISTINCT t.id) AS total_employees,
            COUNT(DISTINCT CASE WHEN ei.gender = 'Male' THEN t.id END) AS male_total,
            COUNT(DISTINCT CASE WHEN ei.gender = 'Female' THEN t.id END) AS female_total,
            COUNT(DISTINCT CASE WHEN ei.gender = 'LGBTQIA+' THEN t.id END) AS lgbt_total,
            COUNT(DISTINCT CASE WHEN t.inactive_date IS NOT NULL THEN t.id END) AS retire_count

            FROM employee_tbl t
            LEFT JOIN employee_info ei ON ei.employee_id = t.id";

    if ($campus && $campus !== '__all__') {
        $sql = $baseSql . " WHERE t.campus = ? GROUP BY t.department ORDER BY total_employees DESC";
        $stmt = $con->prepare($sql);
        if ($stmt === false) {
            error_log("getDepartmentBreakdown prepare error: " . $con->error . " -- SQL: " . $sql);
            return false;
        }
        $stmt->bind_param('s', $campus);
    } else {
        $sql = $baseSql . " GROUP BY t.department ORDER BY total_employees DESC";
        $stmt = $con->prepare($sql);
        if ($stmt === false) {
            error_log("getDepartmentBreakdown prepare error: " . $con->error . " -- SQL: " . $sql);
            return false;
        }
    }

    if (!$stmt->execute()) {
        error_log("getDepartmentBreakdown execute error: " . $stmt->error . " -- SQL: " . $sql);
        $stmt->close();
        return false;
    }

    $result = $stmt->get_result();
    if ($result === false) {
        error_log("getDepartmentBreakdown get_result error: " . $stmt->error . " -- SQL: " . $sql);
        $stmt->close();
        return false;
    }

    $stmt->close();
    return $result;
}

// Respect optional campus filter from query string
$selectedCampus = isset($_GET['campus']) ? $_GET['campus'] : '__all__';
$departmentData = getDepartmentBreakdown($selectedCampus);

// Build list of campuses for the campus filter
$campusList = [];
$conCampus = newCon();
if ($conCampus) {
    $rs = $conCampus->query("SELECT DISTINCT campus FROM employee_tbl WHERE campus IS NOT NULL AND campus <> '' ORDER BY campus");
    if ($rs) {
        while ($r = $rs->fetch_assoc()) {
            $campusList[] = $r['campus'];
        }
        $rs->close();
    }
    $conCampus->close();
}

// Build PHP arrays for the department chart so json_encode() outputs valid JS arrays
$dept = [];
$total = [];
$male = [];
$female = [];
$lgbt = [];
$senior = [];
if ($departmentData && $departmentData instanceof mysqli_result) {
    // rewind if possible (in case result was iterated earlier)
    // mysqli_result doesn't support rewind, so only fetch remaining rows
    while ($row = $departmentData->fetch_assoc()) {
        $dept[] = $row['department'] ?? 'Unknown';
        $total[] = (int) ($row['total_employees'] ?? 0);
        $male[] = (int) ($row['male_total'] ?? 0);
        $female[] = (int) ($row['female_total'] ?? 0);
        $lgbt[] = (int) ($row['lgbt_total'] ?? 0);
        $senior[] = (int) ($row['retire_count'] ?? 0);
    }
}

// Calculate total retirement count from aggregated data
$retire_count = array_sum($senior);
function getEvent()
{
    $con = newCon();
    $currentDate = date("Y-m-d");
    $stmt = $con->prepare("SELECT announceTitle, announceDesc, announceDate, category FROM announcement_tbl WHERE announceDate >= '$currentDate' ORDER BY announceDate DESC LIMIT 3");
    if ($stmt === false) {
        error_log('getEvent prepare error: ' . $con->error);
        return '';
    }

    if (!$stmt->execute()) {
        error_log('getEvent execute error: ' . $stmt->error);
        $stmt->close();
        return '';
    }

    $result = $stmt->get_result();
    if ($result === false) {
        error_log('getEvent get_result error: ' . $stmt->error);
        $stmt->close();
        return '';
    }

    $stmt->close();
    return $result;


}
$eventLists = getEvent();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php echo headerLinks($currentPosition) ?>
</head>

<body>

    <div class="row everything">
        <div class="col sidebar" id="sidebar">
            <?php sidebar("dashboard", $currentPosition);
            ?>
        </div>

        <!-- Right side, main content -->
        <div class="col-10 mt-lg-3 mainContent">
            <?php echo topbar("$currentUser", "$currentPosition", "dashboard") ?>
            <div id="contents">
                <div class="row d-flex flex-row align-items-center justify-content-center gap-3 mt-3">
                    <div class="col summaryOverview">
                        <h6>Total Employees</h6><br>
                        <h6 class="itemText"><?= $totalEmployee ?></h6>
                    </div>
                    <div class="col summaryOverview">
                        <h6>Total Retirees</h6><br>
                        <h6 class="itemText"><?= $retire_count ?></h6>
                    </div>
                    <!--<div class="col summaryOverview">
                        <h6>New Hires</h6><br>
                        <h6 class="itemText">(number)</h6>
                    </div>
                </div>-->
                    <div class="row mt-3 d-flex flex-row align-items-center justify-content-center gap-3">
                        <div class="col-5 d-flex justify-content-center "
                            style="background-color:white; border-radius: 10px;">
                            <!-- <h4>Gender Distribution</h4> -->
                            <!-- <canvas id="genderGraph"></canvas> -->
                            <div id="genderChart" style="margin: auto;"></div>
                        </div>
                        <div class="col d-flex justify-content-center"
                            style="background-color:white; border-radius: 10px;">
                            <h4 style="margin-top: 1rem">Events</h4><br />
                            <!-- <canvas height="300px" id="ageGraph"></canvas> -->
                            <div class="announcement-list">
                                <br />
                                <div>
                                    <?php
                                    $max = 5;
                                    $shown = 0;

                                    if (is_string($eventLists)) {
                                        echo '<div class="alert alert-info">' . $eventLists . '</div>';
                                    } elseif ($eventLists && $eventLists instanceof mysqli_result) {
                                        while ($row = $eventLists->fetch_assoc()) {
                                            if ($shown >= $max)
                                                break;
                                            $shown++;

                                            $id = (int) ($row['id'] ?? 0);
                                            $title = htmlspecialchars($row['announceTitle'] ?? 'No title');
                                            $desc = htmlspecialchars($row['announceDesc'] ?? '');
                                            $rawDate = $row['announceDate'] ?? null;
                                            $date = $rawDate ? date('j F Y', strtotime($rawDate)) : '';
                                            $tag = htmlspecialchars($row['category'] ?? 'Event');
                                            ?>

                                            <div class="card  mb-2 " style="margin-top: 1rem; left: -5rem; width: auto;">
                                                <div class="row g-0 align-items-center">
                                                    <div class="col-auto p-2">
                                                        <div class="date-badge text-center">
                                                            <div class="year"><?= date('Y', strtotime($rawDate ?: 'now')) ?>
                                                            </div>
                                                            <div class="day"><?= date('j', strtotime($rawDate ?: 'now')) ?>
                                                            </div>
                                                            <div class="month"><?= date('F', strtotime($rawDate ?: 'now')) ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col">
                                                        <div class="card-body py-3 w-100">
                                                            <div class="d-flex">
                                                                <div class="flex-grow-1">
                                                                    <h5 class="card-title mb-1"><?= $title ?></h5>
                                                                    <p class="card-text mb-1 text-muted"><?= $desc ?></p>
                                                                    <span
                                                                        class="badge bg-secondary rounded-pill"><?= $tag ?></span>
                                                                </div>
                                                                <div class="ms-3 align-self-start">
                                                                    <a href="events.php?id=<?= $id ?>"
                                                                        class="text-primary text-decoration-none">View More</a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <?php
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col"
                        style="background-color:white; border-radius: 10px; padding:1rem; margin-top:1rem; overflow-x: auto;">
                        <div id="deptChart"></div>
                    </div>


                </div>
                <script>
                    //  Forces the browser to reload the page when going back
                    window.addEventListener("pageshow", function (event) {
                        if (event.persisted || (window.performance && window.performance.navigation.type === 2)) {
                            window.location.reload();
                        }
                    });

                    // Edit script
                    document.addEventListener('DOMContentLoaded', () => {
                        const editIcons = document.querySelectorAll('.edit-icon');
                        const editModal = document.querySelector('#edit_account_modal');
                        const closeEditModalButton = document.querySelector('#close_edit_account');

                        const editIdInput = document.querySelector('#edit_id');
                        const editFnameInput = document.querySelector('#edit_fname');
                        const editLnameInput = document.querySelector('#edit_lname');
                        const editUsernameInput = document.querySelector('#edit_username');
                        const editEmailInput = document.querySelector('#edit_email');
                        const editPasswordInput = document.querySelector('#edit_password');
                        const editPositionSelect = document.querySelector('#edit_position');
                        const editDepartmentSelect = document.querySelector('#edit_department');

                        // Open the modal and populate fields
                        editIcons.forEach(icon => {
                            icon.addEventListener('click', () => {
                                const userId = icon.getAttribute('data-id');

                                // Fetch user details via AJAX
                                fetch(`get_user_details.php?id=${userId}`)
                                    .then(response => response.json())
                                    .then(data => {
                                        // Populate the modal fields
                                        editIdInput.value = data.id;
                                        editUsernameInput.value = data.username;
                                        editEmailInput.value = data.email;
                                        editPasswordInput.value = ''; // Leave password empty for security
                                        editPositionSelect.value = data.position;
                                        editDepartmentSelect.value = data.department;

                                        // Show the modal
                                        editModal.style.display = 'flex';
                                    })
                                    .catch(error => console.error('Error fetching user details:', error));
                            });
                        });

                        // Close the modal
                        if (closeEditModalButton) {
                            closeEditModalButton.addEventListener('click', () => {
                                editModal.style.display = 'none';
                            });
                        }
                    });

                    // Graph Charts
                    // Increased deptHeight so the department names and grouped bars are readable
                    const deptHeight = 700;
                    const deptWidth = 1200;
                    const genderChartHeight = 400;
                    const genderChartWidth = 400;

                    const graphMargin = { t: 0, b: 0, l: 0, r: 0 };

                    const departments = <?= json_encode($dept) ?>;
                    const total = <?= json_encode($total) ?>;
                    const male = <?= json_encode($male) ?>;
                    const female = <?= json_encode($female) ?>;
                    const lgbt = <?= json_encode($lgbt) ?>;
                    const senior = <?= json_encode($senior) ?>;



                    // reduce clutter: prepare total labels (hide zeros) and hide legend entries with zero totals
                    // palette for categories (Total, Male, Female, LGBTQIA+, Senior Citizen)
                    const categoryColors = ['#2E86FF', '#90D7FF', '#ff5fcaff', '#d603f6ff', '#FF6F4D'];

                    // luminance helper for contrast-aware text colors
                    function hexLuminance(hex) {
                        hex = String(hex).replace('#', '');
                        if (hex.length === 3) hex = hex.split('').map(h => h + h).join('');
                        const r = parseInt(hex.substr(0, 2), 16) / 255;
                        const g = parseInt(hex.substr(2, 2), 16) / 255;
                        const b = parseInt(hex.substr(4, 2), 16) / 255;
                        const srgb = [r, g, b].map(v => (v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.155, 2.4)));
                        return 0.2126 * srgb[0] + 0.7152 * srgb[1] + 0.0722 * srgb[2];
                    }

                    const categoryTextColors = categoryColors.map(c => (hexLuminance(c) > 0.179 ? '#000' : '#fff'));

                    function getAllDeptTraces() {
                        const categories = ['Total Employees', 'Male', 'Female', 'LGBTQIA+', 'Senior Citizen'];
                        const allArrays = [total, male, female, lgbt, senior];
                        return categories.map((cat, i) => ({
                            x: departments,
                            y: allArrays[i].map(v => Number(v) || 0),
                            name: cat,
                            type: 'bar',
                            marker: { color: categoryColors[i] },
                            //text: allArrays[i].map(v => (v > 0 ? String(v) : '')),
                            textposition: 'auto',
                            width: 0.4,
                            textfont: { size: 14, color: categoryTextColors[i] },
                            hovertemplate: `${cat}: %{y}`
                        }));
                    }

                    const deptLayout = {
                        title: { text: 'Employee Count', x: 0.5, xanchor: 'center' },
                        barmode: 'group', // use 'group' for multiple traces
                        // place legend horizontally under the chart
                        legend: { orientation: 'h', x: 0.5, xanchor: 'center', y: -0.20, yanchor: 'top' },
                        // ensure enough bottom margin so legend and rotated x-labels fit
                        margin: { t: 50, l: 50, r: 30, b: 160 },
                        xaxis: { tickangle: -45, automargin: true },
                        yaxis: { title: 'Number of Employees', dtick: 1, tickformat: 'd', showgrid: true, gridcolor: '#eee' },
                        bargap: 0.65,
                        bargroupgap: 0.6,
                        height: Math.max(deptHeight, 300),
                        width: Math.max(deptWidth, (departments.length || 1) * 1)
                    };


                    const deptChart = document.getElementById("deptChart");
                    // build the grouped traces used for the All Departments view
                    const deptData = getAllDeptTraces();

                    const chartConfig = { responsive: true };

                    // department filter element (populated server-side)
                    const deptFilter = document.getElementById('deptFilter');

                    // Return a copy of deptData filtered to a single department, or full data when '__all__'
                    // When a single department is selected we render the five categories as separate x-axis
                    // categories so there are visible gaps between bars (instead of grouped traces).
                    function getFilteredData(selected) {
                        if (!selected || selected === '__all__') return deptData;
                        const idx = departments.indexOf(selected);
                        if (idx === -1) return deptData;

                        // categories and values for the selected department
                        const categories = ['Total', 'Male', 'Female', 'LGBTQIA+', 'Senior Citizen'];
                        const values = [Number(total[idx]) || 0, Number(male[idx]) || 0, Number(female[idx]) || 0, Number(lgbt[idx]) || 0, Number(senior[idx]) || 0];

                        // Use per-bar colors to roughly match the grouped view
                        const colors = ['#636EFA', '#00bfffff', '#FF00AE', '#22fdbbff', '#888888'];

                        const trace = {
                            x: categories,
                            y: values,
                            type: 'bar',
                            marker: { color: colors, line: { width: 1 } },
                            text: values.map(v => (v > 0 ? String(v) : '')),
                            textposition: 'auto',
                            textfont: { size: 18, color: '#000' },
                            hoverinfo: 'x+y'
                        };

                        return [trace];
                    }

                    // Return a layout tailored for a filtered (single-department) view or the default layout
                    function getFilteredLayout(selected) {
                        if (!selected || selected === '__all__') return deptLayout;

                        // shallow clone to avoid mutating the global layout
                        const l = JSON.parse(JSON.stringify(deptLayout));
                        // increase gap so individual category bars have visible space between them
                        l.bargap = 0.65;
                        l.bargroupgap = 0.35;
                        // reduce bottom margin since x labels are short
                        l.margin = Object.assign({}, l.margin, { b: 100 });
                        // ensure xaxis has automargin for rotated labels
                        l.xaxis = Object.assign({}, l.xaxis, { tickangle: -45, automargin: true });
                        return l;
                    }

                    // Put Plotly plotting into initCharts so we can lazy-load Plotly if it's missing
                    function initCharts() {
                        try {
                            console.log('DEPT data', { departments: departments, total: total, male: male, female: female, lgbt: lgbt, senior: senior });
                            if (!departments || departments.length === 0) {
                                console.warn('No department data to plot');
                            } else {
                                const initialSelection = (deptFilter && deptFilter.value) ? deptFilter.value : '__all__';
                                const initialData = getFilteredData(initialSelection);
                                Plotly.newPlot(deptChart, initialData, getFilteredLayout(initialSelection), chartConfig);

                                // attach change handler to the select to update chart when user picks a department
                                if (deptFilter) {
                                    deptFilter.addEventListener('change', (e) => {
                                        try {
                                            const newData = getFilteredData(e.target.value);
                                            Plotly.react(deptChart, newData, getFilteredLayout(e.target.value), chartConfig);
                                        } catch (err) {
                                            console.error('Failed to update department chart:', err);
                                        }
                                    });
                                    // campus filter reloads the page with campus query param so server-side data updates
                                    const campusFilter = document.getElementById('campusFilter');
                                    if (campusFilter) {
                                        campusFilter.addEventListener('change', (ev) => {
                                            try {
                                                const campus = ev.target.value;
                                                const params = new URLSearchParams(window.location.search);
                                                if (!campus || campus === '__all__') {
                                                    params.delete('campus');
                                                } else {
                                                    params.set('campus', campus);
                                                }
                                                // preserve other params and apply
                                                const q = params.toString();
                                                window.location.search = q ? `?${q}` : '';
                                            } catch (err) {
                                                console.error('Failed to change campus filter', err);
                                            }
                                        });
                                    }
                                }
                            }
                        } catch (err) {
                            console.error('Plotly dept chart error:', err);
                        }

                        try {
                            console.log('GENDER data', { totalMale: totalMale, totalFemale: totalFemale, totalLGBT: totalLGBT });
                            if (!genderChart) {
                                console.warn('genderChart element not found');
                            } else {
                                Plotly.newPlot(genderChart, genderData, genderDataLayout, chartConfig);
                            }
                        } catch (err) {
                            console.error('Plotly gender chart error:', err);
                        }
                    }

                    // If Plotly isn't loaded, load it dynamically then init; otherwise init immediately
                    if (typeof Plotly === 'undefined') {
                        console.warn('Plotly not found, loading from CDN...');
                        const s = document.createElement('script');
                        s.src = 'https://cdn.plot.ly/plotly-latest.min.js';
                        s.onload = initCharts;
                        s.onerror = function (e) { console.error('Failed to load Plotly script', e); };
                        document.head.appendChild(s);
                    } else {
                        initCharts();
                    }

                    // const ageChart = document.getElementById("ageGraph").getContext('2d');
                    // const chartAge = new Chart(ageChart, {
                    //     type: "bar",
                    //     data: {
                    //         labels: ["18-24", "25-34", "35-44", "45-54", "55-64", "65+"],
                    //         datasets: [{
                    //             label: "Total",
                    //             data: [
                    //                 age18_24,
                    //                 age25_34,
                    //                 age35_44,
                    //                 age45_54,
                    //                 age55_64,
                    //                 age65_abv
                    //             ],
                    //             backgroundColor: "#FF00AE"
                    //         }]
                    //     },
                    //     options: {
                    //         indexAxis: 'x',
                    //         scales: {
                    //             y: {
                    //                 beginAtZero: true
                    //             }
                    //         },
                    //         plugins: {
                    //             title: {
                    //                 display: false,
                    //                 text: 'Age Distribution',
                    //             },
                    //             legend: {
                    //                 display: true,
                    //                 position: 'right',
                    //             }
                    //         }
                    //     }
                    // }); */


                    const totalMale = <?= $totalMale ?>;
                    const totalFemale = <?= $totalFemale ?>;
                    const totalLGBT = <?= $totalLGBT ?>;

                    const genderChart = document.getElementById("genderChart");

                    var genderData = [{
                        values: [totalMale, totalFemale, totalLGBT],
                        labels: ["Male", "Female", "LGBTQIA+"],
                        type: 'pie',
                        name: 'Gender Distribution',
                        hoverinfo: 'label+percent+name',
                        textinfo: "label+percent",
                        textposition: "inside",
                        automargin: true,
                        hole: .4
                    }];

                    var genderDataLayout = {
                        title: {
                            text: "Gender Distribution",
                            font: { size: 16 },
                            y: 0.98
                        },
                        colorway: ["#00bfffff", "#FF00AE", "#22fdbbff"],
                        height: genderChartHeight,
                        width: genderChartWidth,
                        margin: { t: 60, b: 20, l: 20, r: 20 },
                        showlegend: true,
                        xaxis: { scaleanchor: "y", scaleratio: 1 },
                        yaxis: { scaleanchor: "x", scaleratio: 1 }
                    }

                    // Only attempt to draw the gender chart immediately if Plotly is already loaded.
                    // If Plotly is not present yet, the dynamic loader will call initCharts() after loading.
                    if (typeof Plotly !== 'undefined') {
                        try {
                            console.log('GENDER data', { totalMale: totalMale, totalFemale: totalFemale, totalLGBT: totalLGBT });
                            if (!genderChart) {
                                console.warn('genderChart element not found');
                            } else {
                                Plotly.newPlot(genderChart, genderData, genderDataLayout, chartConfig);
                            }
                        } catch (err) {
                            console.error('Plotly gender chart error:', err);
                        }
                    } else {
                        console.log('Plotly not loaded yet; gender chart will be initialized by initCharts() after the library loads.');
                    }

                    // const genderChart = document.getElementById("genderGraph").getContext('2d');
                    // const gradient = genderChart.createLinearGradient(0, 0, 0, 400); // top to bottom
                    // gradient.addColorStop(0, "#FF00AE");
                    // gradient.addColorStop(1, "#0062ff");

                    // const chartGender = new Chart(genderChart, {
                    //     type: "doughnut",
                    //     data: {
                    //         labels: ["Male", "Female", "LGBTQIA+"],
                    //         datasets: [{
                    //             label: "Total",
                    //             data: [
                    //                 totalMale,
                    //                 totalFemale,
                    //                 totalLGBT
                    //             ],
                    //             backgroundColor: [
                    //                 "#00bfffff",
                    //                 "#FF00AE",
                    //                 gradient,
                    //             ]
                    //         }]
                    //     },
                    //     options: {
                    //         plugins: {
                    //             title: {
                    //                 display: false,
                    //                 text: 'Gender Distribution',
                    //             },
                    //             legend: {
                    //                 display: true,
                    //                 position: 'right',
                    //             }
                    //         }
                    //     }
                    // });
                </script>
</body>


</html>
<?php
require_once 'includes.php';

session_start();

// Prevent browser caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

checkUser($_SESSION['user_id'], $_SESSION['user_username']);
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
$totalLGBT =  getGender("LGBTQIA+");

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

$a
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
                        <h6 class="itemText"><?= $age18_24 ?></h6>
                    </div>
                    <div class="col summaryOverview">
                        <h6>Total Retirees</h6><br>
                        <h6 class="itemText"><?= $age25_34 ?></h6>
                    </div>
                    <div class="col summaryOverview">
                        <h6>New Hires</h6><br>
                        <h6 class="itemText">(number)</h6>
                    </div>
                </div>
                <div class="row mt-3 d-flex flex-row align-items-center justify-content-center gap-3">
                    <div class="col-5 d-flex justify-content-center " style="background-color:white; border-radius: 10px;">
                        <!-- <h4>Gender Distribution</h4> -->
                        <!-- <canvas id="genderGraph"></canvas> -->
                        <div id="genderChart"></div>
                    </div>
                    <div class="col d-flex justify-content-center" style="background-color:white; border-radius: 10px;">
                        <!-- <h4>Age Distribution</h4> -->
                        <!-- <canvas height="300px" id="ageGraph"></canvas> -->
                        <div id="ageChart"></div>
                    </div>
                </div>
                <div class="row mt-3 d-flex flex-row align-items-center justify-content-center gap-3">
                    <div class="col">
                        <h4>Retirement Forecast</h4>
                        <div id="retirementGraph"></div>
                    </div>
                    <div class="col">
                        <h4>Diversity Metrics</h4>
                        <div id="diversityGraph"></div>
                    </div>
                </div>

            </div>
        </div>


    </div>
    <script>
        //  Forces the browser to reload the page when going back
        window.addEventListener("pageshow", function(event) {
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
        const ageChartHeight = 400;
        const ageChartWidth = 600;
        const genderChartHeight = 400;
        const genderChartWidth = 400;

        const graphMargin = 'margin: {"t": 0, "b": 0, "l": 0, "r": 0}';
        const age18_24 = <?= $age18_24 ?>;
        const age25_34 = <?= $age25_34 ?>;
        const age35_44 = <?= $age35_44 ?>;
        const age45_54 = <?= $age45_54 ?>;
        const age55_64 = <?= $age55_64 ?>;
        const age65_abv = <?= $age65_abv ?>;

        const ageChart = document.getElementById("ageChart");

        const chartConfig = {
            responsive: true
        };

        var ageData = [{
            x: ["18-24", "25-34", "35-44", "45-54", "55-64", "65+"],
            y: [age18_24, age25_34, age35_44, age45_54, age55_64, age65_abv],
            type: 'bar',
        }];

        var ageDataLayout = {
            title: {
                text: "Age Distribution"
            },
            colorway: ["#0073ffff"],
            height: ageChartHeight,
            width: ageChartWidth,
            margin: graphMargin,
        }

        Plotly.newPlot(ageChart, ageData, ageDataLayout, chartConfig);

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
        // });

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
            automargin: true,
            hole: .4,

        }];

        var genderDataLayout = {
            title: {
                text: "Gender Distribution"
            },
            colorway: ["#00bfffff", "#FF00AE", "#22fdbbff"],
            height: genderChartHeight,
            width: genderChartWidth,
            margin: graphMargin,
        }

        Plotly.newPlot(genderChart, genderData, genderDataLayout, chartConfig);

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
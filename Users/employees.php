<?php
require_once 'includes.php';
// require_once '../phpFunctions/email.php';

session_start();

checkUser($_SESSION['user_id'], $_SESSION['user_username']);
$user = getUser();
$currentUser = $user['fullname'];
$currentDepartment = $user['department'];
$currentCampus = $user['campus'];
$currentPosition = $user['position'];
doubleCheck($currentPosition);


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['fname'])) {
        // Add Account Logic
        $fname = $_POST['fname'];
        $lname = $_POST['lname'];
        $email = $_POST['email'];
        $username = $_POST['username'];
        $plainPassword = $_POST['pass'];
        $password = password_hash($plainPassword, PASSWORD_DEFAULT);
        $position = $_POST['pos'];
        $department = $_POST['dept'];
        $campus = $_POST['campus'];

        $conn = new mysqli('localhost', 'root', '', 'gad_portal');

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
                echo "<script>alert('Account Created!');</script>";

                // Send credentials email to the new user
                // sendUserCredentials($email, $username, $plainPassword);
            } else {
                echo "<script>alert('Error: " . $conn->error . "');</script>";
            }
        }

        $conn->close();
    } elseif (isset($_POST['id'])) {
        // Deactivate User Logic
        $userId = $_POST['id'];

        $conn = new mysqli('localhost', 'root', '', 'gad_portal');

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $sql = "UPDATE accounts_tbl SET is_active = 0 WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);

        if ($stmt->execute()) {
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

        $conn = new mysqli('localhost', 'root', '', 'gad_portal');

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        if ($password) {
            $sql = "UPDATE accounts_tbl SET fname = ?, lname = ?, username = ?, email = ?, pass = ?, position = ?, department = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssssi", $fname, $lname, $username, $email, $password, $position, $department, $userId);
        } else {
            $sql = "UPDATE accounts_tbl SET fname = ?, lname = ?, username = ?, email = ?, position = ?, department = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssi", $fname, $lname, $username, $email, $position, $department, $userId);
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


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?= headerLinks("Employees"); ?>
</head>

<body>
    <style>
        /* Modal container */
        .modals {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            /* Semi-transparent background */
            display: none;
            /* Hidden by default */
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        /* Modal content */
        .modal_add_account {
            background-color: #fff;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            width: 400px;
            max-width: 90%;
        }


        .modal_add_account .modal_title h2 {
            margin: 0 0 1rem;
            font-size: 1.8rem;
            font-weight: bold;
            text-align: center;
            color: #333;
        }

        .form_add_account {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .form_add_account input,
        .form_add_account select {
            padding: 0.8rem;
            font-size: 1rem;
            border: 1px solid #ccc;
            border-radius: 4px;
            outline: none;
            transition: border-color 0.3s ease;
        }

        .form_add_account input:focus,
        .form_add_account select:focus {
            border-color: #007bff;
        }

        .form_add_account input::placeholder {
            color: #aaa;
            font-size: 0.9rem;
        }

        .buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 1rem;
        }

        .buttons button {
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s ease;
        }

        .buttons button[type="submit"] {
            background-color: #28a745;
            color: #fff;
        }

        .buttons button[type="submit"]:hover {
            background-color: #218838;
        }

        .buttons .add_btn_close {
            background-color: #dc3545;
            color: #fff;
        }

        .buttons .add_btn_close:hover {
            background-color: #c82333;
        }

        .modal_view_all {
            background-color: #fff;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            width: 80%;
            max-width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }

        .modal_edit_account {
            background-color: #fff;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            width: 400px;
            max-width: 90%;
        }

        .modal_edit_account .modal_title h2 {
            margin: 0 0 1rem;
            font-size: 1.8rem;
            font-weight: bold;
            text-align: center;
            color: #333;
        }

        .form_edit_account {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .form_edit_account input,
        .form_edit_account select {
            padding: 0.8rem;
            font-size: 1rem;
            border: 1px solid #ccc;
            border-radius: 4px;
            outline: none;
            transition: border-color 0.3s ease;
        }

        .form_edit_account input:focus,
        .form_edit_account select:focus {
            border-color: #007bff;
        }
    </style>
    <?= addDelay("employees", $currentUser, $currentPosition); ?>

    <div class="row everything">
        <div class="col sidebar" id="sidebar">
            <?php sidebar("employees", $currentPosition);
            ?>
        </div>

        <!-- MAIN CONTENT -->
        <div class="col-10 mt-lg-3 mainContent">
            <?php echo topbar("$currentUser", "$currentPosition", "employees") ?>
            <div id="contents">
                <div class="row mt-5">
                    <div class="col">
                        <h1>Employees</h1>
                    </div>
                </div>
                <?php if ($currentPosition === "Director" || $currentPosition === "Focal Person") {
                    echo ' 
                        <div class="row mt-3 d-flex justify-content-end">
                            <div class="col-2">
                                <button id="add_account" class="btn btn-outline-success">
                                Add Account
                                <ion-icon name="add-outline" class="add-icon"></ion-icon>
                                </button>
                            </div>
                        </div>
                    ';
                } ?>
                <!-- FiltersHere -->
                <div class="row mt-3">
                    <div class="col d-flex flex-row justify-content-end align-items-center gap-3" id="filters">
                    </div>
                </div>
                <div class="row mt-3" id="filterButton">
                    <!-- FILTER BUTTONS HERE -->
                </div>
                <!-- TableHere -->
                <div class="row mt-3 tableOverview">
                    <div class="col" id="showEmployeeTable">
                        <!-- TABLES HERE -->
                    </div>
                </div>
            </div>
        </div>
    </div>



    <!-- Modals -->
    <div class="modals" id="add_account_modal" style="display: none;">
        <div class="modal_add_account">
            <div class="modal_title">
                <h2>Add Account</h2>
            </div>
            <!-- position -->
            <?php
            echo '
                    <form method="post" class="form_add_account" novalidate>
                        <input type="text" name="fname" placeholder="First Name" required>
                        <input type="text" name="lname" placeholder="Last Name" required>
                        <input type="email" name="email" placeholder="Email" required>
                        <input type="text" name="username" placeholder="Username" required>
                        <input type="password" name="pass" placeholder="Password" required>
                        <select name="pos" id="position" required>
                        ';
            if ($currentPosition === "Director") {
                echo '
                            <option value="Director">Director</option>
                            <option value="Technical Assistant">Technical Assistant</option>
                            <option value="Focal Person">Focal Person</option>
                            <option value="Panel">Panel</option>
                            <option value="RET Chair">RET Chair</option>
                        </select>';
            } else {
                echo '
                            <option value="Researcher">Researcher</option>
                        </select>';
            }

            echo '
                        <!-- department -->
                        <select name="dept" id="department" required>';


            if ($currentPosition === "Director") {
                echo '<option value="" disabled selected>Select Department</option>
                            <option value="CPADM">CPADM</option>
                            <option value="CMBT">CMBT - BA, HM</option>
                            <option value="CoArch">CoArch</option>
                            <option value="CoEd">CoEd</option>
                            <option value="Crim">Crim</option>
                            <option value="COE">COE</option>
                            <option value="CICT">CICT</option>
                            <option value="IPE">IPE</option>
                            <option value="LHS">LHS</option>
                            <option value="CIT">CIT</option>
                            <option value="CAS">CAS</option>
                            <option value="IOLL">IOLL</option>
                            <option value="CON">CON</option>
                            <option value="GS">GS</option>';
            } else {
                echo '
                <option value=' . $currentDepartment . ' selected>' . $currentDepartment . '</option>
                ';
            }

            echo '
                        </select>
                        <select name="campus" id="campus" required>';

            if ($currentPosition === "Director") {
                echo '
                            <option value="" disabled selected>Select Campus</option>
                            <option value="Sumacab">Sumacab</option>
                            <option value="GT">Gen. Tinio</option>
                            <option value="San Isidro">San Isidro</option>
                            <option value="Gabaldon">Gabaldon</option>
                            <option value="Atate">Atate</option>
                            <option value="Fort Magsaysay">Fort Magsaysay</option>';
            } else {
                echo '
                <option value=' . $currentCampus . '>' . $currentCampus . '</option>
                ';
            }

            echo '
                        </select>
                        <div class="buttons">
                            <button type="submit">Add</button>
                            <button type="button" class="add_btn_close" id="close_add_account">Close</button>
                        </div>
                    </form>'
                ?>
        </div>
    </div>




    <!-- Edit Account Modal -->
    <div class="modals" id="edit_account_modal" style="display: none;">
        <div class="modal_edit_account">
            <div class="modal_title">
                <h2>Edit Account</h2>
            </div>
            <form method="post" class="form_edit_account" novalidate>
                <input type="text" name="edit_fname" id="edit_fname" placeholder="First Name" required>
                <input type="text" name="edit_lname" id="edit_lname" placeholder="Last Name" required>
                <input type="hidden" name="edit_id" id="edit_id">
                <input type="text" name="edit_username" id="edit_username" placeholder="Username" required>
                <input type="email" name="edit_email" id="edit_email" placeholder="Email" required>
                <input type="password" name="edit_password" id="edit_password" placeholder="Password">
                <select name="edit_position" id="edit_position" required>
                    <option value="Director">Director</option>
                    <option value="Technical Assistant">Technical Assistant</option>
                    <option value="Focal Person">Focal Person</option>
                </select>
                <select name="edit_department" id="edit_department" required>
                    <option value="CPADM">CPADM</option>
                    <option value="CMBT">CMBT - BA, HM</option>
                    <option value="CoArch">CoArch</option>
                    <option value="CoEd">CoEd</option>
                    <option value="Crim">Crim</option>
                    <option value="COE">COE</option>
                    <option value="CICT">CICT</option>
                    <option value="IPE">IPE</option>
                    <option value="LHS">LHS</option>
                    <option value="CIT">CIT</option>
                    <option value="CAS">CAS</option>
                    <option value="IOLL">IOLL</option>
                    <option value="CON">CON</option>
                    <option value="GS">GS</option>
                    <option value="SIC">SIC</option>
                    <option value="Gabaldon">Gabaldon</option>
                    <option value="Atate">Atate</option>
                    <option value="FMC">FMC</option>
                    <option value="NTP">NTP</option>
                    <option value="Research">Research</option>
                    <option value="Extension">Extension</option>
                    <option value="Student Organization">Student Organization</option>
                    <option value="Publication">Publication</option>
                    <option value="VAWC">VAWC</option>
                </select>
                <div class="buttons">
                    <button type="submit" name="update_account">Update</button>
                    <button type="button" class="add_btn_close" id="close_edit_account">Close</button>
                </div>
            </form>
        </div>
    </div>



    <script>
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
                    fetch(`../get_user_details.php?id=${userId}`)
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
    </script>
    <script>
        $(document).ready(function () {
            const position = <?= json_encode($currentPosition) ?>;
            const campus = <?= json_encode($currentCampus) ?>;
            const dept = <?= json_encode($currentDepartment) ?>;

            // Load filters, table, buttons first
            $('#filters').load("./reusableHTML/filters.php", function () {
                $('#showEmployeeTable').load("./reusableHTML/employeeTable.php", function () {
                    $('#filterButton').load("./reusableHTML/filtersButton.php", function () {
                        // Now everything exists → safe to run
                        filterFunction("#checkboxShowSummary", "#filterCampus", "#filterDept", "#filterSize", "#filterGender", position, "#employeeTable", "no", "filter");
                        resetFilterFunction(position);
                        restrictDeptAndCampus(position, dept, campus, "#filterDept", "#filterCampus");
                    });
                });
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <scrip nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js">
        </script>
        <script>
            console.log('script.js is loaded');
            document.addEventListener('DOMContentLoaded', () => {
                // Add Account Modal
                const addAccountButton = document.querySelector('#add_account');
                const addAccountModal = document.querySelector('#add_account_modal');
                const closeAddAccountButton = document.querySelector('#close_add_account');

                if (addAccountButton) {
                    addAccountButton.addEventListener('click', () => {
                        addAccountModal.style.display = 'flex';
                    });
                }

                if (closeAddAccountButton) {
                    closeAddAccountButton.addEventListener('click', () => {
                        addAccountModal.style.display = 'none';
                    });
                }

                // View All Modal
                const viewAllButton = document.querySelector('#view_all');
                const viewAllModal = document.querySelector('#view_all_modal');
                const closeViewAllButton = document.querySelector('#close_view_all');

                if (viewAllButton) {
                    viewAllButton.addEventListener('click', () => {
                        viewAllModal.style.display = 'flex';
                    });
                }

                if (closeViewAllButton) {
                    closeViewAllButton.addEventListener('click', () => {
                        viewAllModal.style.display = 'none';
                    });
                }
            });

            document.addEventListener('DOMContentLoaded', () => {
                const deactivateIcons = document.querySelectorAll('.deactivate-icon');

                deactivateIcons.forEach(icon => {
                    icon.addEventListener('click', () => {
                        const userId = icon.getAttribute('data-id');
                        const userRow = icon.closest('tr'); // Get the table row containing the icon

                        if (confirm('Are you sure you want to deactivate this user?')) {
                            fetch('../deactivate_user.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded',
                                },
                                body: `id=${userId}`,
                            })
                                .then(response => response.text())
                                .then(data => {
                                    alert(data);

                                    // Remove the row from the table if the deactivation is successful
                                    if (data.includes('User deactivated successfully')) {
                                        userRow.remove();
                                    } else {
                                        alert('Failed to deactivate the user.');
                                    }
                                })
                                .catch(error => {
                                    console.error('Error:', error);
                                    alert('An error occurred while deactivating the user.');
                                });
                        }
                    });
                });
            });

            document.addEventListener('DOMContentLoaded', () => {
                const editIcons = document.querySelectorAll('.edit-icon');
                const editModal = document.querySelector('#edit_account_modal');
                const closeEditModalButton = document.querySelector('#close_edit_account');

                const editIdInput = document.querySelector('#edit_id');
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
                        fetch(`../get_user_details.php?id=${userId}`)
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
        </script>
</body>

</html>
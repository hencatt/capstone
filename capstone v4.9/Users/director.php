<?php
    include '../gad_portal.php';
    include '../variables.php';
    include '../insertingLogs.php';
    session_start(); 

    // Prevent browser caching
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Pragma: no-cache");
    header("Expires: 0");

    // Check if the user is logged in
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../index.php");
        exit();
    }

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
                $sql = "INSERT INTO accounts_tbl (fname, lname, email, username, pass, position, department, date_created, is_active) 
                        VALUES ('$fname', '$lname', '$email', '$username', '$password', '$position' , '$department', NOW(), 1)";

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

        $conn = new mysqli('localhost', 'root', '', 'gad_portal');

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $sql = "UPDATE accounts_tbl SET is_active = 0 WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);

        if ($stmt->execute()) {
            // insert log
            insertLog($_SESSION(['user_username']), "Created New Account", date('Y-m-d H:i:s'));

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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEUST GAD Portal</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- fonts link -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <!-- external css -->
    <link rel="stylesheet" href="../Users/css/director.css" type="text/css">
    <!-- icon link -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
    

</head>
<body>
    <div class="layout">
        <!-- Left Sidebar -->
        <div class="left-side">
            <div class="sidebar">
                <div class="logo">
                    <img src="../assets/neust_logo-1-1799093319.png" alt="NEUST Logo">
                    <h1>GAD PORTAL</h1>
                </div>
                <div class="sidebarOptions">
                    <label class="category">Home</label>
                        <li id="active"><a href="#" class="categoryItem">
                            <span class="material-symbols-outlined">dashboard</span>    
                            Dashboard</a></li>
                    <label class="category">General</label>
                        <li><a href="#" class="categoryItem">
                            <span class="material-symbols-outlined">groups</span>     
                            Users</a></li>
                        <li><a href="#" class="categoryItem">
                            <span class="material-symbols-outlined">inventory_2</span>  
                            Inventory</a></li>
                        <li><a href="#" class="categoryItem">
                        <spaASDn class="material-symbols-outlined">event</spaASDn>  
                        Events</a></li>
                        <li id="active"><a href="#" class="categoryItem">
                            <span class="material-symbols-outlined">groups_2</span>    
                            Employee</a></li>
                    <label class="category">Settings</label>
                        <li><a href="#" class="categoryItem">
                        <span class="material-symbols-outlined">person</span>  
                        Account</a></li>
                        <li><a href="../logout.php?logout=true" class="categoryItem">
                        <span class="material-symbols-outlined">logout</span>  
                        Logout</a></li>

                </div>
            </div>
        </div>

        <!-- Right side, main content -->
        <div class="right-side">
            <div class="header">
                <div class="search-container">
                    <ion-icon name="search-outline" class="search-icon"></ion-icon>
                    <input type="text" class="search-bar" placeholder="Search">
                </div>
                <div class="header-icons">
                    <img src="#" alt="Profile" class="profile-image">
                    <span class="material-symbols-outlined">
                        notifications
                    </span>

                    <!-- external css for ion-icons doesn't work for some reason -->
                    <!-- <div class="logout-container">
                        <a href="../logout.php?logout=true" class="logout-link" 
                        style="text-decoration: none;
                         color: black;">
                            <ion-icon name="log-out-outline" class="logout-icon" 
                            style="
                            display:flex;
                            width: 1.7rem; 
                            height:1.7rem; 
                            cursor:pointer; 
                            transition: 0.3s ease; 
                            color: black;
                            align-items: center;
                            font-style: normal;"
                            >
                            </ion-icon>
                        </a>
                    </div>                -->
                    
                    
                </div>
            </div>
            <div class="content">
                <h1>Dashboard</h1>
                <!-- <div class="card-container">
                    <div class="card">
                        <h2>Card 1</h2>
                        <p>Content for card 1.</p>
                    </div>
                    <div class="card">
                        <h2>Card 2</h2>
                        <p>Content for card 2.</p>
                    </div>
                    <div class="card">
                        <h2>Card 3</h2>
                        <p>Content for card 3.</p>
                    </div>
                    <div class="card">
                        <h2>Card 4</h2>
                        <p>Content for card 4.</p>
                    </div>
                    <div class="card">
                        <h2>Card 5</h2>
                        <p>Content for card 5.</p>
                    </div>
                    <div class="card">
                        <h2>Card 6</h2>
                        <p>Content for card 6.</p>
                    </div>
                    <div class="card">
                        <h2>Card 7</h2>
                        <p>Content for card 7.</p>
                    </div>
                    <div class="card">
                        <h2>Card 8</h2>
                        <p>Content for card 8.</p>
                    </div>
                </div> -->

                
            </div>

            <div class="users">
                <div class="add-account-container">
                    <button id="add_account" class="add-account-btn">
                        Add Account
                        <ion-icon name="add-outline" class="add-icon"></ion-icon>
                    </button>
                </div>

                
                <table class="users-table">
                    <h1>Users</h1>
                    <thead>
                        <tr>
                            <td>Name</td>
                            <td>Email</td>
                            <td>Username</td>
                            <td>Position</td>
                            <td>Department</td>
                            <td></td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        include '../gad_portal.php'; // Include the database connection

                        $sql = "SELECT id, fname, lname, email, username, position, department FROM accounts_tbl WHERE is_active = 1";
                        $stmt = $con->prepare($sql);

                        if ($stmt) {
                            $stmt->execute();
                            $result = $stmt->get_result();

                            if ($result->num_rows > 0) {
                                // showing the data in the table
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['fname'] . " " . $row['lname']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['position']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['department']) . "</td>";
                                    echo "<td><span class='edit-icon material-symbols-outlined' data-id='" . $row['id'] . "'>edit</span></td>";
                                    echo "<td><span class='deactivate-icon material-symbols-outlined' data-id='" . $row['id'] . "'>delete</span></td>";
                                    
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5'>No data available</td></tr>";
                            }

                            $stmt->close();
                        } else {
                            echo "Error preparing statement: " . $con->error;
                        }

                        $con->close();
                        ?>
                    </tbody>
                    
                </table>
                <div class="view-all-container">
                    <button id="view_all" class="view-all-btn">View All</button>
                </div>
            </div>

            




            <div class="footer"></div>

<!-- Modals -->
            <div class="modals" id="add_account_modal" style="display: none;">
                <div class="modal_add_account">
                    <div class="modal_title">
                        <h2>Add Account</h2>
                    </div>
                    <!-- position -->
                     <?php 
                        echo'
                    <form method="post" class="form_add_account" novalidate>
                        <input type="text" name="fname" placeholder="First Name" required>
                        <input type="text" name="lname" placeholder="Last Name" required>
                        <input type="email" name="email" placeholder="Email" required>
                        <input type="text" name="username" placeholder="Username" required>
                        <input type="password" name="pass" placeholder="Password" required>
                        <select name="pos" id="position" required>
                            <option value="Director">Director</option>
                            <option value="Technical Assistant">Technical Assistant</option>
                            <option value="Focal Person">Focal Person</option>
                        </select>

                        <!-- department -->
                        <select name="dept" id="department" required>
                            <option value="" disabled selected>Select Department</option>
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
                        </select>
                        <div class="buttons">
                            <button type="submit">Add</button>
                            <button type="button" class="add_btn_close" id="close_add_account">Close</button>
                        </div>
                    </form>'
                    ?>
                </div>
            </div>

        <!-- View All Modal -->
            <div class="modals" id="view_all_modal" style="display: none;">
                <div class="modal_view_all">
                    <div class="modal_title">
                        <h2>All Users</h2>
                    </div>
                    <table class="users-table">
                        <thead>
                            <tr>
                                <td>Name</td>
                                <td>Email</td>
                                <td>Username</td>
                                <td>Position</td>
                                <td>Department</td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $conn = new mysqli('localhost', 'root', '', 'gad_portal');

                            if ($conn->connect_error) {
                                die("Connection failed: " . $conn->connect_error);
                            }

                            $sql = "SELECT id, fname, lname, email, username, position, department FROM accounts_tbl WHERE is_active = 1";
                            $result = $conn->query($sql);

                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['fname'] . " " . $row['lname']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['position']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['department']) . "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='4'>No data available</td></tr>";
                            }

                            $conn->close();
                            ?>
                        </tbody>
                    </table>
                    <div class="buttons">
                        <button type="button" class="add_btn_close" id="close_view_all">Close</button>
                    </div>
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


        </div>     
    </div>  
</body>

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


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<scrip nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>


<script src="script.js"></script>


</html>
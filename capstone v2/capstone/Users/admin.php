<?php 
    include '../gad_portal.php';
    include '../variables.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEUST GAD Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/admin.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://cdn.jsdelivr.net/npm/ionicons@5.5.2/dist/css/ionicons.min.css" rel="stylesheet">
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
                <ul class="menu">
                    <li><a href="#" class="menu-item"><i class="icon">🏠</i> Dashboard</a></li>
                    <li><a href="#" class="menu-item"><i class="icon">🔄</i> UI Elements</a></li>
                    <li><a href="#" class="menu-item"><i class="icon">📊</i> Tables</a></li>
                    <li><a href="#" class="menu-item"><i class="icon">📝</i> Forms</a></li>
                    <li><a href="#" class="menu-item"><i class="icon">💳</i> Cards</a></li>
                    <li><a href="#" class="menu-item"><i class="icon">📦</i> Modal</a></li>
                    <li><a href="#" class="menu-item"><i class="icon">📄</i> Blank</a></li>
                </ul>
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
                    <ion-icon name="notifications-outline" class="notification-icon"></ion-icon>
                    <!-- profile image -->
                    <img src="#" alt="Profile" class="profile-image">
                </div>
            </div>
            <div class="content">
                <h1>Dashboard</h1>
                <div class="card-container">
                    <!-- try lang hehe -->
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
                </div>

                <div class="add-account-container">
                    <button class="add-account-btn">
                        Add Account
                        <ion-icon name="add-outline" class="add-icon"></ion-icon>
                    </button>
                </div>

                
                <table class="users-table">
                    <thead>
                        <td>Name</td>
                        <td>Email</td>
                        <td>Position</td>
                        <td>Department</td>
                    </thead>
                    <tbody>
                        <?php

                        $conn = new mysqli('localhost', 'root', '', 'gad_portal');


                        if ($conn->connect_error) {
                            die("Connection failed: " . $conn->connect_error);
                        }

                        // Query to fetch data from accounts_tbl
                        $sql = "SELECT fname, lname, email, position, department FROM accounts_tbl";
                        $result = $conn->query($sql);

                        // Check if there are results
                        if ($result->num_rows > 0) {
                            // 
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['fname'] . " " . $row['lname']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['email']) . "</td>";
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

                    
                    
                    
            </div>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</html>
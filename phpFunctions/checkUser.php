<?php
require_once 'gad_portal.php';

function checkUser($userId)
{
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_position'])) {
        header("Location: ../index.php");
        exit();
    }
    $con = con();

    if (!$con) {
        die("Connection failed: " . mysqli_connect_error());
    }

    $sql = "SELECT * FROM accounts_tbl WHERE id = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows < 1) {
        header("Location: ../index.php");
        exit();
    }

    $stmt->close();
    $con->close();
}

function doubleCheck($role)
{
    $currentUserPosition = $_SESSION["user_position"];

    if ($role != $currentUserPosition) {
        header("Location: /capstone/index.php");
        echo "<script>alert('Please log in again')</script>";
        exit();
    }
}

function setUser()
{
    $currentId = $_SESSION['user_id'];
    $con = newCon();
    $sql = "SELECT fname, lname, email, position, department, campus FROM accounts_tbl WHERE id = '$currentId'";
    return $con->query($sql);
}

function getUser()
{
    $currentId = $_SESSION['user_id'];
    $result = setUser();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Split position by comma to support multiple roles
        $positions = array_map('trim', explode(',', $row['position']));

        return [
            "id" => $currentId,
            "fname" => htmlspecialchars($row["fname"]),
            "lname" => htmlspecialchars($row["lname"]),
            "email" => htmlspecialchars($row["email"]),
            "fullname" => htmlspecialchars($row['fname']) . " " . htmlspecialchars($row['lname']),
            "position" => htmlspecialchars($positions[0]), // Primary position
            "position2" => isset($positions[1]) ? htmlspecialchars($positions[1]) : null, // Secondary position
            "all_positions" => $positions, // Array of all positions
            "campus" => htmlspecialchars_decode($row['campus']),
            "department" => htmlspecialchars($row['department']),
        ];
    } else {
        echo "<script>console.log('No UserID Found')</script>";
    }
}

/**
 * Check if user has a specific role (supports multi-role)
 * @param string $role - The role to check for
 * @return bool
 */
function hasRole($role)
{
    $user = getUser();
    if (!$user)
        return false;

    return in_array($role, $user['all_positions']);
}
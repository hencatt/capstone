<?php
require_once 'gad_portal.php';

function checkUser($userId, $username)
{

    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_position'])) {
        header("Location: ../index.php");
        exit();
    }
    $con = con();

    if (!$con) {
        die("Connection failed: " . mysqli_connect_error());
    }

    $sql = "SELECT * FROM accounts_tbl WHERE id = ? AND username = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("is", $userId, $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows < 1) {
        echo "<script>alert('Please log in again')</script>";
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
    $sql = "SELECT fname, lname, username, email, position, department, campus FROM accounts_tbl WHERE id = '$currentId'";
    return $con->query($sql);
}

function getUser()
{
    $currentId = $_SESSION['user_id'];
    $result = setUser();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return [
            "id" => $currentId,
            "fname" => htmlspecialchars($row["fname"]),
            "lname" => htmlspecialchars($row["lname"]),
            "email" => htmlspecialchars($row["email"]),
            "username" => htmlspecialchars($row['username']),
            "fullname" => htmlspecialchars($row['fname']) . " " . htmlspecialchars($row['lname']),
            "position" => htmlspecialchars($row['position']),
            "campus" => htmlspecialchars_decode($row['campus']),
            "department" => htmlspecialchars($row['department']),
        ];
    } else {
        echo "<script>console.log('No UserID Found')</script>";
    }
}

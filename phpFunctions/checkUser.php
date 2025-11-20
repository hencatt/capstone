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
        // alertError("Error", "Please log in again");
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

function setUserExtraInfo()
{
    $currentId = $_SESSION['user_id'];
    $con = newCon();
    $sql = "SELECT m_initial, address, birthday, sex, gender, size FROM employee_info WHERE id = '$currentId'";
    return $con->query($sql);
}
function getUser()
{
    $currentId = $_SESSION['user_id'];
    $result = setUser();
    $result2 = setUserExtraInfo();

    $user = ["id" => $currentId];

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $user = array_merge($user, [
            "fname" => htmlspecialchars($row["fname"]),
            "lname" => htmlspecialchars($row["lname"]),
            "fullname" => htmlspecialchars($row['fname']) . " " . htmlspecialchars($row['lname']),
            "email" => htmlspecialchars($row['email']),
            "position" => htmlspecialchars($row['position']),
            "campus" => htmlspecialchars_decode($row['campus']),
            "department" => htmlspecialchars($row['department']),
        ]);
    } else {
        echo "<script>console.log('No UserID Found in accounts_tbl')</script>";
    }

    if ($result2 && $result2->num_rows > 0) {
        $row2 = $result2->fetch_assoc();
        $user = array_merge($user, [
            "mname" => htmlspecialchars($row2["m_initial"]),
            "address" => htmlspecialchars($row2["address"]),
            "birthday" => htmlspecialchars($row2["birthday"]),
            "sex" => htmlspecialchars($row2["sex"]),
            "gender" => htmlspecialchars($row2["gender"]),
            "size" => htmlspecialchars($row2["size"]),
        ]);
    } else {
        echo "<script>console.log('No UserID Found in employee_info')</script>";
    }

    return $user;
}


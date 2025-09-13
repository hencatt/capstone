<?php
require_once '../phpFunctions/gad_portal.php';

$con = new mysqli("localhost", "root", ""); // adjust creds if needed

// 1. Create DB if not exists
$dbName = "gad_portal";
$sql = "CREATE DATABASE IF NOT EXISTS `$dbName` 
        CHARACTER SET utf8mb4 
        COLLATE utf8mb4_general_ci";
if ($con->query($sql)) {
    echo "Database '$dbName' checked/created successfully.<br>";
} else {
    die("Error creating DB: " . $con->error);
}

// select the database
$con->select_db($dbName);

// 2. Tables with utf8mb4
$tables = [
    "accounts_tbl" => "
    CREATE TABLE IF NOT EXISTS `accounts_tbl` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `email` varchar(60) NOT NULL,
      `username` varchar(100) NOT NULL,
      `pass` text NOT NULL,
      `fname` varchar(60) NOT NULL,
      `lname` varchar(60) NOT NULL,
      `position` varchar(50) DEFAULT NULL,
      `department` varchar(100) NOT NULL,
      `campus` varchar(100) NOT NULL,
      `date_created` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
      `is_active` int(2) NOT NULL DEFAULT 1,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    ",

    "announcement_tbl" => "
    CREATE TABLE IF NOT EXISTS `announcement_tbl` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `announceTitle` varchar(45) NOT NULL,
      `announceDesc` text NOT NULL,
      `announceDate` date NOT NULL,
      `category` enum('Holiday','Event','Research Event') NOT NULL,
      `proposalDate` date DEFAULT NULL,
      `acceptanceDate` date DEFAULT NULL,
      `presentationDate` date DEFAULT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    ",

    "employee_info" => "
    CREATE TABLE IF NOT EXISTS `employee_info` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `fname` varchar(100) NOT NULL,
      `m_initial` varchar(10) NOT NULL,
      `lname` varchar(100) NOT NULL,
      `address` varchar(100) NOT NULL,
      `birthday` date NOT NULL,
      `marital_status` varchar(100) NOT NULL,
      `sex` enum('Male','Female') NOT NULL,
      `gender` varchar(100) NOT NULL,
      `priority_status` varchar(20) DEFAULT NULL,
      `size` varchar(50) NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    ",

    "employee_tbl" => "
    CREATE TABLE IF NOT EXISTS `employee_tbl` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `email` varchar(100) NOT NULL,
      `contact_no` varchar(20) NOT NULL,
      `department` varchar(100) NOT NULL,
      `campus` varchar(100) NOT NULL,
      `status` varchar(10) NOT NULL DEFAULT 'Active',
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    ",

    "inventory_tbl" => "
    CREATE TABLE IF NOT EXISTS `inventory_tbl` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `itemName` varchar(45) NOT NULL,
      `itemDesc` varchar(100) NOT NULL,
      `itemImage` mediumblob DEFAULT NULL,
      `itemQuantity` int(3) DEFAULT NULL,
      `itemSize` enum('S','M','L','XL','XXL','XXXL','4XL','5XL','-') DEFAULT '-',
      `itemCategory` enum('Everyone','Women','Men','LGBTQIA+','PWD','Education','-') DEFAULT '-',
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    ",

    "login_attempts" => "
    CREATE TABLE IF NOT EXISTS `login_attempts` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `ip_add` varchar(45) NOT NULL,
      `attempt_time` datetime NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    ",

    "logs" => "
    CREATE TABLE IF NOT EXISTS `logs` (
      `log_id` int(11) NOT NULL AUTO_INCREMENT,
      `username` varchar(45) NOT NULL,
      `activity` varchar(100) NOT NULL,
      `log_date` datetime NOT NULL,
      PRIMARY KEY (`log_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    ",

    "research_tbl" => "
    CREATE TABLE IF NOT EXISTS `research_tbl` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `research_title` varchar(255) NOT NULL,
      `author` varchar(100) NOT NULL,
      `co_author` varchar(300) NOT NULL,
      `date_started` date NOT NULL,
      `date_completed` date NOT NULL,
      `file` longblob NOT NULL,
      `description` varchar(500) NOT NULL,
      `status` enum('Completed','Approved','Pending','Rejected') NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    "
];

// 3. Execute all
foreach ($tables as $name => $query) {
    if ($con->query($query)) {
        echo "Table '$name' checked/created successfully.<br>";
    } else {
        echo "Error with $name: " . $con->error . "<br>";
    }
}

$con->close();
?>

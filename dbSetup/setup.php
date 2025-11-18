<?php
// =============================
//  GAD PORTAL - CLEAN DB SETUP
//  1-to-1 Employee <-> Account
// =============================

require_once '../phpFunctions/gad_portal.php';

$con = new mysqli("localhost", "root", "");
$dbName = "gad_portal";

// 1. Create the database
$sql = "CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci";
if (!$con->query($sql))
  die("DB Error: " . $con->error);
$con->select_db($dbName);

echo "Database ready.<br>";

// -----------------------------
// TABLE CREATION (IN ORDER)
// -----------------------------

$tables = [

  // 1. EMPLOYEE TABLE (MUST COME FIRST)
  "employee_tbl" => "
CREATE TABLE IF NOT EXISTS `employee_tbl` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `contact_no` varchar(20) NOT NULL,
  `department` varchar(100) NOT NULL,
  `campus` varchar(100) NOT NULL,
  `status` varchar(10) NOT NULL DEFAULT 'Active',
  `inactive_date` DATE NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
",

  // 2. ACCOUNTS TABLE (USES EMPLOYEE ID)
  "accounts_tbl" => "
CREATE TABLE IF NOT EXISTS `accounts_tbl` (
  `id` int(11) NOT NULL,
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
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_account_employee` FOREIGN KEY (`id`) REFERENCES `employee_tbl` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
",

  // 3. EMPLOYEE INFO
  "employee_info" => "
CREATE TABLE IF NOT EXISTS `employee_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fname` varchar(100) NOT NULL,
  `m_initial` varchar(10) NOT NULL,
  `lname` varchar(100) NOT NULL,
  `address` varchar(255) NOT NULL,
  `birthday` date NOT NULL,
  `marital_status` varchar(100) NOT NULL,
  `sex` enum('Male','Female') NOT NULL,
  `gender` varchar(100) NOT NULL,
  `priority_status` varchar(20) DEFAULT NULL,
  `size` varchar(50) NOT NULL,
  `income` varchar(50) DEFAULT NULL,
  `children_num` int DEFAULT NULL,
  `concern` text DEFAULT NULL,
  `employee_id` int(11) NULL,
  PRIMARY KEY (`id`),
  KEY `fk_employeeInfo_employeeTbl` (`employee_id`),
  CONSTRAINT `fk_employeeInfo_employeeTbl` FOREIGN KEY (`employee_id`) REFERENCES `employee_tbl` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
",

  // 4. ANNOUNCEMENTS
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

  // 5. INVENTORY
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

  // 6. LOGIN ATTEMPTS
  "login_attempts" => "
CREATE TABLE IF NOT EXISTS `login_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip_add` varchar(45) NOT NULL,
  `attempt_time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
",

  // 7. LOGS
  "logs" => "
CREATE TABLE IF NOT EXISTS `logs` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(45) NOT NULL,
  `activity` varchar(100) NOT NULL,
  `log_date` datetime NOT NULL,
  PRIMARY KEY (`log_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
",

  // 8. RESEARCH
  "research_tbl" => "
CREATE TABLE IF NOT EXISTS `research_tbl` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `research_title` varchar(255) NOT NULL,
  `author` varchar(100) NOT NULL,
  `research_email` VARCHAR(100) NOT NULL,
  `co_author` varchar(300) NOT NULL,
  `date_started` date NOT NULL,
  `date_completed` date NOT NULL,
  `file` longblob NOT NULL,
  `description` varchar(500) NOT NULL,
  `status` enum('Approved','Pending','Rejected') NOT NULL DEFAULT 'Pending',
  `date_submitted` date DEFAULT NULL,
  `research_agenda` varchar(100) DEFAULT NULL,
  `research_sdg` varchar(100) DEFAULT NULL,
  `research_category` enum('Proposal','Completed') NOT NULL DEFAULT 'Proposal',
  `research_grant` enum('Yes','No') NOT NULL DEFAULT 'No',
  `research_grant_times` int(2) NOT NULL DEFAULT 0,
  `research_resubmission_status` enum('Yes', 'No') NOT NULL DEFAULT 'No',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
",

  // 9. COMMENTS
  "comments_tbl" => "
CREATE TABLE IF NOT EXISTS `comments_tbl` (
  `comment_id` int(11) NOT NULL AUTO_INCREMENT,
  `research_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `commentor_name` varchar(100) NOT NULL,
  `comment_datetime` datetime NOT NULL,
  PRIMARY KEY (`comment_id`),
  CONSTRAINT `fk_research` FOREIGN KEY (`research_id`) REFERENCES `research_tbl` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
",

  // 10. VOTES
  "votes_tbl" => "
CREATE TABLE IF NOT EXISTS `votes_tbl` (
  `vote_id` int(11) NOT NULL AUTO_INCREMENT,
  `vote` ENUM('Approve','Reject') NOT NULL,
  `voter_name` VARCHAR(45) NOT NULL,
  `voter_datetime` DATETIME NOT NULL,
  `research_id` INT(11) NOT NULL,
  `panel_id` INT(11) NOT NULL,
  PRIMARY KEY (`vote_id`),
  KEY `fk_votes_research` (`research_id`),
  KEY `fk_votes_panel` (`panel_id`),
  CONSTRAINT `fk_votes_research` FOREIGN KEY (`research_id`) REFERENCES `research_tbl` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_votes_panel` FOREIGN KEY (`panel_id`) REFERENCES `accounts_tbl` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
",

  // 11. EVENT PANEL ASSIGNMENTS
  "event_panel_tbl" => "
CREATE TABLE IF NOT EXISTS `event_panel_tbl` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `eventId` int(11) NOT NULL,
  `panelId` int(11) NOT NULL,
  `assignedBy` varchar(255) NOT NULL,
  `assignedDate` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_event_panel_event` (`eventId`),
  KEY `fk_event_panel_account` (`panelId`),
  CONSTRAINT `fk_event_panel_event` FOREIGN KEY (`eventId`) REFERENCES `announcement_tbl` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_event_panel_account` FOREIGN KEY (`panelId`) REFERENCES `accounts_tbl` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
"
];

// Execute everything
foreach ($tables as $name => $query) {
  if ($con->query($query)) {
    echo "Table '$name' created OK.<br>";
  } else {
    echo "Error in $name: " . $con->error . "<br>";
  }
}

$con->close();
echo "<br>All tables ready.";
?>
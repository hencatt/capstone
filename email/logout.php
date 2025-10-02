<?php
require_once 'phpFunctions\insertingLogs.php';

session_start(); // Start the session

// Prevent browser caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Destroy the session
if (isset($_GET['logout'])) {
    insertLog($_SESSION['fullname'], "User Logout", date('Y-m-d H:i:s'));
    session_unset(); // Unset all session variables
    session_destroy(); // Destroy the session
    header("Location: index.php"); // Redirect to the login page
    exit();
}
?>
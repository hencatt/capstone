<?php
require_once 'includes.php';
session_start();

// Get search term if provided
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';

include('./reusableHTML/inventoryTable.php');
?>
<?php
/**
 * Check if Account Exists
 * Checks if an account already exists for a given email
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);
header('Content-Type: application/json; charset=utf-8');

require_once 'gad_portal.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

$con = newCon();
if ($con->connect_error) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email)) {
        echo json_encode(['error' => 'Email is required']);
        exit;
    }
    
    // Check if account exists
    $stmt = $con->prepare("SELECT id, position FROM accounts_tbl WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $account = $result->fetch_assoc();
        echo json_encode([
            'exists' => true,
            'account_id' => $account['id'],
            'position' => $account['position']
        ]);
    } else {
        echo json_encode(['exists' => false]);
    }
    
    $stmt->close();
    $con->close();
} else {
    echo json_encode(['error' => 'Invalid request method']);
}
?>
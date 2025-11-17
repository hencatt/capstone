<?php
/**
 * Get Account Details
 * Fetches user account information for editing
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);
header('Content-Type: application/json; charset=utf-8');

require_once 'gad_portal.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

$conn = newCon();
if ($conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request method']);
    exit;
}

try {
    // Get and validate account ID
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    
    if ($id <= 0) {
        throw new Exception('Invalid account ID');
    }

    // Prepare and execute query
    $stmt = $conn->prepare("
        SELECT 
            id,
            username,
            email,
            fname,
            lname,
            position,
            department,
            campus,
            is_active,
            date_created
        FROM accounts_tbl 
        WHERE id = ?
    ");
    
    if (!$stmt) {
        throw new Exception('Database prepare failed: ' . $conn->error);
    }
    
    $stmt->bind_param("i", $id);
    
    if (!$stmt->execute()) {
        throw new Exception('Query execution failed: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Account not found');
    }
    
    $account = $result->fetch_assoc();
    $stmt->close();
    
    // Return account data (excluding password for security)
    echo json_encode([
        'id' => $account['id'],
        'username' => $account['username'],
        'email' => $account['email'],
        'fname' => $account['fname'],
        'lname' => $account['lname'],
        'position' => $account['position'],
        'department' => $account['department'],
        'campus' => $account['campus'],
        'is_active' => $account['is_active'],
        'date_created' => $account['date_created']
    ]);
    
} catch (Exception $e) {
    // Log error for debugging
    error_log("Get Account Details Error: " . $e->getMessage());
    
    echo json_encode([
        'error' => $e->getMessage()
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>
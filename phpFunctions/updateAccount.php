<?php
/**
 * Update Account Handler
 * Handles updating user account information including optional password change and multi-role support
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);
header('Content-Type: application/json; charset=utf-8');

require_once 'gad_portal.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

$conn = newCon();
if ($conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request method']);
    exit;
}

try {
    // Sanitize and validate input
    $id = isset($_POST['acc_id']) ? intval($_POST['acc_id']) : 0;
    $username = trim($_POST['acc_username'] ?? '');
    $email = trim($_POST['acc_email'] ?? '');
    $fname = trim($_POST['acc_fname'] ?? '');
    $lname = trim($_POST['acc_lname'] ?? '');

    // UPDATED: Handle multi-role position (comes as comma-separated string from frontend)
    $position = trim($_POST['acc_position'] ?? '');

    $department = trim($_POST['acc_department'] ?? '');
    $campus = trim($_POST['acc_campus'] ?? '');
    $password = trim($_POST['acc_password'] ?? '');

    // Validate required fields
    if ($id <= 0) {
        throw new Exception('Invalid account ID');
    }

    if (empty($username) || empty($email) || empty($fname) || empty($lname)) {
        throw new Exception('Username, Email, First Name, and Last Name are required');
    }

    if (empty($position)) {
        throw new Exception('At least one position is required');
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }

    // Check if email already exists for another user
    $checkEmail = $conn->prepare("SELECT id FROM accounts_tbl WHERE email = ? AND id != ?");
    $checkEmail->bind_param("si", $email, $id);
    $checkEmail->execute();
    $checkEmail->store_result();

    if ($checkEmail->num_rows > 0) {
        throw new Exception('Email already exists for another account');
    }
    $checkEmail->close();

    // Check if username already exists for another user
    $checkUsername = $conn->prepare("SELECT id FROM accounts_tbl WHERE username = ? AND id != ?");
    $checkUsername->bind_param("si", $username, $id);
    $checkUsername->execute();
    $checkUsername->store_result();

    if ($checkUsername->num_rows > 0) {
        throw new Exception('Username already exists for another account');
    }
    $checkUsername->close();

    // Validate password if provided
    if (!empty($password)) {
        if (strlen($password) < 8) {
            throw new Exception('Password must be at least 8 characters long');
        }

        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $password)) {
            throw new Exception('Password must contain at least one uppercase letter, one lowercase letter, and one number');
        }
    }

    // Prepare update statement
    if (!empty($password)) {
        // Update with new password
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE accounts_tbl 
                SET username = ?, 
                    email = ?, 
                    pass = ?, 
                    fname = ?, 
                    lname = ?, 
                    position = ?, 
                    department = ?, 
                    campus = ?
                WHERE id = ?";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception('Database prepare failed: ' . $conn->error);
        }

        $stmt->bind_param(
            "ssssssssi",
            $username,
            $email,
            $hashed,
            $fname,
            $lname,
            $position,
            $department,
            $campus,
            $id
        );
    } else {
        // Update without changing password
        $sql = "UPDATE accounts_tbl 
                SET username = ?, 
                    email = ?, 
                    fname = ?, 
                    lname = ?, 
                    position = ?, 
                    department = ?, 
                    campus = ?
                WHERE id = ?";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception('Database prepare failed: ' . $conn->error);
        }

        $stmt->bind_param(
            "sssssssi",
            $username,
            $email,
            $fname,
            $lname,
            $position,
            $department,
            $campus,
            $id
        );
    }

    // Execute the update
    if (!$stmt->execute()) {
        throw new Exception('Database update failed: ' . $stmt->error);
    }

    // Check if any rows were affected
    if ($stmt->affected_rows === 0) {
        $checkExists = $conn->prepare("SELECT id FROM accounts_tbl WHERE id = ?");
        $checkExists->bind_param("i", $id);
        $checkExists->execute();
        $checkExists->store_result();

        if ($checkExists->num_rows === 0) {
            throw new Exception('Account not found');
        }
        $checkExists->close();

        echo json_encode([
            'success' => true,
            'message' => 'No changes were made to the account',
            'no_changes' => true
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'message' => 'Account updated successfully with position(s): ' . $position,
            'updated_id' => $id
        ]);
    }

    $stmt->close();

} catch (Exception $e) {
    error_log("Update Account Error: " . $e->getMessage());

    echo json_encode([
        'error' => $e->getMessage()
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>
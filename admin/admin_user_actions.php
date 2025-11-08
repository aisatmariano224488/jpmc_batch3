<?php
// Start the session
// session_start();

// // Check if user is logged in
// if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
//     header("Location: admin_login.php");
//     exit;
// }

require_once '../includes/db_connection.php';

// Response array
$response = ['success' => false, 'message' => ''];

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    switch ($action) {
        case 'add':
            // Add new user
            addUser($conn);
            break;
            
        case 'edit':
            // Edit existing user
            editUser($conn);
            break;
            
        case 'delete':
            // Delete user
            deleteUser($conn);
            break;
            
        case 'get':
            // Get user details
            getUserDetails($conn);
            break;
            
        default:
            $response['message'] = 'Invalid action';
            break;
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
exit;

// Function to add a new user
function addUser($conn) {
    global $response;
    
    // Get form data
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $status = isset($_POST['status']) ? (int)$_POST['status'] : 1;
    
    // Validate form data
    if (empty($name) || empty($email) || empty($password)) {
        $response['message'] = 'Please fill all required fields';
        return;
    }
    
    // Check if email already exists
    $checkSql = "SELECT id FROM admin_users WHERE email = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        $response['message'] = 'Email already exists';
        return;
    }
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new user
    $sql = "INSERT INTO admin_users (name, email, password, is_active, created_at) VALUES (?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $name, $email, $hashedPassword, $status);
    
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'User added successfully';
    } else {
        $response['message'] = 'Error: ' . $stmt->error;
    }
}

// Function to edit an existing user
function editUser($conn) {
    global $response;
    
    // Get form data
    $userId = isset($_POST['userId']) ? (int)$_POST['userId'] : 0;
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $status = isset($_POST['status']) ? (int)$_POST['status'] : 1;
    
    // Validate form data
    if (empty($userId) || empty($name) || empty($email)) {
        $response['message'] = 'Please fill all required fields';
        return;
    }
    
    // Check if email already exists (for another user)
    $checkSql = "SELECT id FROM admin_users WHERE email = ? AND id != ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("si", $email, $userId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        $response['message'] = 'Email already exists for another user';
        return;
    }
    
    // Update user
    if (!empty($password)) {
        // Update with new password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE admin_users SET name = ?, email = ?, password = ?, is_active = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssii", $name, $email, $hashedPassword, $status, $userId);
    } else {
        // Update without changing password
        $sql = "UPDATE admin_users SET name = ?, email = ?, is_active = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssii", $name, $email, $status, $userId);
    }
    
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'User updated successfully';
    } else {
        $response['message'] = 'Error: ' . $stmt->error;
    }
}

// Function to delete a user
function deleteUser($conn) {
    global $response;
    
    // Get user ID
    $userId = isset($_POST['userId']) ? (int)$_POST['userId'] : 0;
    
    // Validate user ID
    if (empty($userId)) {
        $response['message'] = 'Invalid user ID';
        return;
    }
    
    // Delete user
    $sql = "DELETE FROM admin_users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'User deleted successfully';
    } else {
        $response['message'] = 'Error: ' . $stmt->error;
    }
}

// Function to get user details
function getUserDetails($conn) {
    global $response;
    
    // Get user ID
    $userId = isset($_POST['userId']) ? (int)$_POST['userId'] : 0;
    
    // Validate user ID
    if (empty($userId)) {
        $response['message'] = 'Invalid user ID';
        return;
    }
    
    // Get user details
    $sql = "SELECT id, name, email, is_active FROM admin_users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $response['success'] = true;
        $response['user'] = $result->fetch_assoc();
    } else {
        $response['message'] = 'User not found';
    }
}
?>
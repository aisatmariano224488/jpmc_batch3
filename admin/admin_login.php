<?php
// Start session
// session_start();

// // Check if already logged in
// if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
//     header("Location: admin.php");
//     exit;
// }

// Helper function to mask email
function maskEmail($email) {
    $parts = explode('@', $email);
    if (count($parts) == 2) {
        $name = $parts[0];
        $domain = $parts[1];
        
        if (strlen($name) <= 2) {
            $maskedName = str_repeat('*', strlen($name));
        } else {
            $maskedName = substr($name, 0, 2) . str_repeat('*', strlen($name) - 2);
        }
        
        return $maskedName . '@' . $domain;
    }
    return $email;
}

// Add Composer autoload for Brevo SDK
require_once __DIR__ . '/../vendor/autoload.php';

use Brevo\Client\Api\TransactionalEmailsApi;
use Brevo\Client\Configuration;
use Brevo\Client\Model\SendSmtpEmail;

// Initialize variables
$error = '';
$success = '';
$username = '';
$show_otp = false;
$step = 'login'; // login, otp

// Process form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once '../includes/db_connection.php';
    
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        if ($action == 'login') {
            // Step 1: Validate credentials and send OTP
            $username = trim($_POST['username']);
            $password = $_POST['password'];
            
            // Check user credentials
            $stmt = $conn->prepare("SELECT id, name, email, password, is_active FROM admin_users WHERE email = ? LIMIT 1");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                if ($row['is_active'] != 1) {
                    $error = "Account is inactive. Please contact the administrator.";
                } elseif (password_verify($password, $row['password'])) {
                    // Credentials valid, generate and send OTP
                    $otp_code = sprintf("%06d", mt_rand(100000, 999999));
                    $expires_at = date('Y-m-d H:i:s', strtotime('+10 minutes'));
                    
                    // Store OTP in database
                    $otp_sql = "INSERT INTO admin_otp (admin_id, email, otp_code, expires_at) VALUES (?, ?, ?, ?)";
                    $otp_stmt = $conn->prepare($otp_sql);
                    $otp_stmt->bind_param("isss", $row['id'], $row['email'], $otp_code, $expires_at);
                    
                    if ($otp_stmt->execute()) {
                        // Send OTP via Brevo
                        try {
                            $brevo_api_key = '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';
                            
                            $email_content = "
                            <html>
                            <head>
                                <style>
                                    body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f5f7fa; }
                                    .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 12px; padding: 30px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
                                    .header { text-align: center; margin-bottom: 30px; }
                                    .logo { width: 80px; height: 80px; margin: 0 auto 20px; }
                                    .otp-box { background: linear-gradient(135deg, #1976d2 0%, #d32f2f 100%); color: white; padding: 20px; border-radius: 8px; text-align: center; margin: 20px 0; }
                                    .otp-code { font-size: 32px; font-weight: bold; letter-spacing: 5px; margin: 10px 0; }
                                    .footer { text-align: center; margin-top: 30px; color: #666; font-size: 14px; }
                                </style>
                            </head>
                            <body>
                                <div class='container'>
                                    <div class='header'>
                                        <h1 style='color: #1976d2; margin: 0;'>Admin Login Verification</h1>
                                        <p style='color: #666; margin: 10px 0 0 0;'>James Polymers Admin Portal</p>
                                    </div>
                                    
                                    <p>Hello <strong>" . htmlspecialchars($row['name']) . "</strong>,</p>
                                    
                                    <p>You have requested to log into the James Polymers Admin Portal. Please use the following One-Time Password (OTP) to complete your login:</p>
                                    
                                    <div class='otp-box'>
                                        <div>Your OTP Code:</div>
                                        <div class='otp-code'>" . $otp_code . "</div>
                                        <div style='font-size: 14px; margin-top: 10px;'>This code expires in 10 minutes</div>
                                    </div>
                                    
                                    <p><strong>Security Notice:</strong></p>
                                    <ul style='color: #666;'>
                                        <li>This OTP is valid for 10 minutes only</li>
                                        <li>Do not share this code with anyone</li>
                                        <li>If you didn't request this login, please contact your IT administrator immediately</li>
                                    </ul>
                                    
                                    <div class='footer'>
                                        <p><strong>James Polymers Manufacturing Corporation</strong><br>
                                        Admin Security System<br>
                                        &copy; " . date('Y') . " All rights reserved.</p>
                                    </div>
                                </div>
                            </body>
                            </html>
                            ";
                            
                            $config = Configuration::getDefaultConfiguration()->setApiKey('api-key', $brevo_api_key);
                            $apiInstance = new TransactionalEmailsApi(new GuzzleHttp\Client(), $config);
                            
                            $sendSmtpEmail = new SendSmtpEmail([
                                'subject' => 'Admin Login OTP - James Polymers',
                                'htmlContent' => $email_content,
                                'sender' => ['name' => 'JPMC', 'email' => 'jamespolymersmanufacturingcorp@gmail.com'],
                                'to' => [['email' => $row['email'], 'name' => $row['name']]]
                            ]);
                            
                            $result = $apiInstance->sendTransacEmail($sendSmtpEmail);
                            
                            // Store user data in session for OTP verification
                            $_SESSION['otp_admin_id'] = $row['id'];
                            $_SESSION['otp_admin_email'] = $row['email'];
                            $_SESSION['otp_admin_name'] = $row['name'];
                            
                            $show_otp = true;
                            $step = 'otp';
                            $success = "OTP has been sent to your registered email address. Please check your inbox.";
                            
                        } catch (Exception $e) {
                            $error = "Failed to send OTP. Please try again. Error: " . $e->getMessage();
                            // Log the error for debugging
                            error_log("OTP Email Error: " . $e->getMessage());
                        }
                    } else {
                        $error = "Failed to generate OTP. Please try again.";
                    }
                    
                    $otp_stmt->close();
                } else {
                    $error = "Invalid email or password.";
                }
            } else {
                $error = "Invalid email or password.";
            }
            
            $stmt->close();
            
        } elseif ($action == 'verify_otp') {
            // Step 2: Verify OTP and complete login
            $otp_code = trim($_POST['otp_code']);
            
            // Clean the OTP code - remove any non-numeric characters
            $otp_code = preg_replace('/[^0-9]/', '', $otp_code);
            
            // Validate OTP format
            if (strlen($otp_code) !== 6) {
                $error = "Please enter a valid 6-digit OTP.";
                $show_otp = true;
                $step = 'otp';
            } else {
            
            if (isset($_SESSION['otp_admin_id'])) {
                $admin_id = $_SESSION['otp_admin_id'];
                
                // Debug: Check what we're looking for
                error_log("Looking for OTP - Admin ID: $admin_id, OTP Code: $otp_code");
                
                // Verify OTP with better timezone handling
                $current_time = date('Y-m-d H:i:s');
                $otp_sql = "SELECT id, expires_at, created_at FROM admin_otp WHERE admin_id = ? AND otp_code = ? AND expires_at > ? AND used = 0 ORDER BY id DESC LIMIT 1";
                $otp_stmt = $conn->prepare($otp_sql);
                $otp_stmt->bind_param("iss", $admin_id, $otp_code, $current_time);
                $otp_stmt->execute();
                $otp_result = $otp_stmt->get_result();
                
                // Debug: Log what we found
                error_log("OTP Query - Current time: $current_time, Results found: " . $otp_result->num_rows);
                
                if ($otp_result->num_rows > 0) {
                    // OTP is valid, mark as used
                    $otp_row = $otp_result->fetch_assoc();
                    $update_otp_sql = "UPDATE admin_otp SET used = 1 WHERE id = ?";
                    $update_otp_stmt = $conn->prepare($update_otp_sql);
                    $update_otp_stmt->bind_param("i", $otp_row['id']);
                    $update_otp_stmt->execute();
                    $update_otp_stmt->close();
                    
                    // Complete login
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_id'] = $_SESSION['otp_admin_id'];
                    $_SESSION['admin_name'] = $_SESSION['otp_admin_name'];
                    $_SESSION['admin_email'] = $_SESSION['otp_admin_email'];
                    
                    // Clean up OTP session data
                    unset($_SESSION['otp_admin_id']);
                    unset($_SESSION['otp_admin_email']);
                    unset($_SESSION['otp_admin_name']);
                    
                    // Update last login
                    $update_sql = "UPDATE admin_users SET last_login = NOW() WHERE id = ?";
                    $update_stmt = $conn->prepare($update_sql);
                    $update_stmt->bind_param("i", $admin_id);
                    $update_stmt->execute();
                    $update_stmt->close();
                    
                    header("Location: admin.php");
                    exit;
                } else {
                    // Debug: Check if there are any OTP records for this admin at all
                    $debug_sql = "SELECT id, otp_code, expires_at, used, created_at FROM admin_otp WHERE admin_id = ? ORDER BY id DESC LIMIT 3";
                    $debug_stmt = $conn->prepare($debug_sql);
                    $debug_stmt->bind_param("i", $admin_id);
                    $debug_stmt->execute();
                    $debug_result = $debug_stmt->get_result();
                    
                    error_log("=== OTP DEBUG INFO ===");
                    error_log("Looking for Admin ID: $admin_id, OTP Code: '$otp_code'");
                    error_log("Current time: $current_time");
                    error_log("Recent OTP records for this admin:");
                    
                    while ($debug_row = $debug_result->fetch_assoc()) {
                        $is_expired = (strtotime($debug_row['expires_at']) < strtotime($current_time)) ? 'YES' : 'NO';
                        error_log("ID: {$debug_row['id']}, Code: '{$debug_row['otp_code']}', Expires: {$debug_row['expires_at']}, Used: {$debug_row['used']}, Created: {$debug_row['created_at']}, Expired: $is_expired");
                    }
                    error_log("======================");
                    $debug_stmt->close();
                    
                    $error = "Invalid or expired OTP. Please try again.";
                    $show_otp = true;
                    $step = 'otp';
                }
                
                $otp_stmt->close();
            } else {
                $error = "Session expired. Please start the login process again.";
                $step = 'login';
            }
            } // Close the OTP format validation if block
            
        } elseif ($action == 'resend_otp') {
            // Resend OTP
            if (isset($_SESSION['otp_admin_id'])) {
                $admin_id = $_SESSION['otp_admin_id'];
                $email = $_SESSION['otp_admin_email'];
                $name = $_SESSION['otp_admin_name'];
                
                // Generate new OTP
                $otp_code = sprintf("%06d", mt_rand(100000, 999999));
                $expires_at = date('Y-m-d H:i:s', strtotime('+10 minutes'));
                
                // Store new OTP
                $otp_sql = "INSERT INTO admin_otp (admin_id, email, otp_code, expires_at) VALUES (?, ?, ?, ?)";
                $otp_stmt = $conn->prepare($otp_sql);
                $otp_stmt->bind_param("isss", $admin_id, $email, $otp_code, $expires_at);
                
                if ($otp_stmt->execute()) {
                    // Send new OTP via email
                    try {
                        $brevo_api_key = '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';
                        
                        $email_content = "
                        <html>
                        <head>
                            <style>
                                body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f5f7fa; }
                                .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 12px; padding: 30px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
                                .header { text-align: center; margin-bottom: 30px; }
                                .logo { width: 80px; height: 80px; margin: 0 auto 20px; }
                                .otp-box { background: linear-gradient(135deg, #1976d2 0%, #d32f2f 100%); color: white; padding: 20px; border-radius: 8px; text-align: center; margin: 20px 0; }
                                .otp-code { font-size: 32px; font-weight: bold; letter-spacing: 5px; margin: 10px 0; }
                                .footer { text-align: center; margin-top: 30px; color: #666; font-size: 14px; }
                            </style>
                        </head>
                        <body>
                            <div class='container'>
                                <div class='header'>
                                    <h1 style='color: #1976d2; margin: 0;'>Admin Login Verification (Resent)</h1>
                                    <p style='color: #666; margin: 10px 0 0 0;'>James Polymers Admin Portal</p>
                                </div>
                                
                                <p>Hello <strong>" . htmlspecialchars($name) . "</strong>,</p>
                                
                                <p>You have requested a new OTP for the James Polymers Admin Portal. Please use the following One-Time Password (OTP) to complete your login:</p>
                                
                                <div class='otp-box'>
                                    <div>Your New OTP Code:</div>
                                    <div class='otp-code'>" . $otp_code . "</div>
                                    <div style='font-size: 14px; margin-top: 10px;'>This code expires in 10 minutes</div>
                                </div>
                                
                                <p><strong>Security Notice:</strong></p>
                                <ul style='color: #666;'>
                                    <li>This OTP is valid for 10 minutes only</li>
                                    <li>Do not share this code with anyone</li>
                                    <li>If you didn't request this login, please contact your IT administrator immediately</li>
                                </ul>
                                
                                <div class='footer'>
                                    <p><strong>James Polymers Manufacturing Corporation</strong><br>
                                    Admin Security System<br>
                                    &copy; " . date('Y') . " All rights reserved.</p>
                                </div>
                            </div>
                        </body>
                        </html>
                        ";
                        
                        $config = Configuration::getDefaultConfiguration()->setApiKey('api-key', $brevo_api_key);
                        $apiInstance = new TransactionalEmailsApi(new GuzzleHttp\Client(), $config);
                        
                        $sendSmtpEmail = new SendSmtpEmail([
                            'subject' => 'Admin Login OTP (Resent) - James Polymers',
                            'htmlContent' => $email_content,
                            'sender' => ['name' => 'JPMC', 'email' => 'jamespolymersmanufacturingcorp@gmail.com'],
                            'to' => [['email' => $email, 'name' => $name]]
                        ]);
                        
                        $result = $apiInstance->sendTransacEmail($sendSmtpEmail);
                        
                        $success = "New OTP has been sent to your email address.";
                        $show_otp = true;
                        $step = 'otp';
                        
                    } catch (Exception $e) {
                        $error = "Failed to resend OTP. Please try again. Error: " . $e->getMessage();
                        error_log("Resend OTP Email Error: " . $e->getMessage());
                        $show_otp = true;
                        $step = 'otp';
                    }
                } else {
                    $error = "Failed to generate new OTP. Please try again.";
                    $show_otp = true;
                    $step = 'otp';
                }
                
                $otp_stmt->close();
            } else {
                $error = "Session expired. Please start the login process again.";
                $step = 'login';
            }
        }
    }
    
    $conn->close();
}

// Check if we should show OTP form (for page refresh scenarios)
if (isset($_SESSION['otp_admin_id']) && !isset($_POST['action'])) {
    $show_otp = true;
    $step = 'otp';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | James Polymers</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#1976d2', // blue
                        secondary: '#d32f2f', // red
                        dark: '#222222',
                        light: '#f5f5f5'
                    }
                }
            }
        }
    </script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Loading Screen Styles */
        #loadingScreen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: #000;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            transition: opacity 0.5s ease-out;
        }
        
        #loadingScreen.fade-out {
            opacity: 0;
            pointer-events: none;
        }
        
        .loading-video {
            position: absolute;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            object-fit: cover;
            z-index: 10000;
        }
        
        .loading-text {
            color: white;
            font-size: 1.5rem;
            font-weight: 600;
            text-align: center;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }
        
        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-top: 4px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-top: 20px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .loading-logo {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }
        
        .loading-logo img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: contain;
        }

        /* Skip Button Styles */
        .skip-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50px;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            z-index: 10001;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }
        
        .skip-btn:hover {
            background: rgba(0, 0, 0, 0.9);
            border-color: rgba(255, 255, 255, 0.6);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        }
        
        .skip-btn i {
            font-size: 16px;
        }
        
        .skip-btn span {
            letter-spacing: 0.5px;
        }

        /* Hide main content initially */
        .main-content {
            opacity: 0;
            transition: opacity 0.5s ease-in;
        }
        
        .main-content.show {
            opacity: 1;
        }

        html, body { height: 100%; }
        body { 
            min-height: 100vh; 
            background: #f5f7fa;
            overflow-x: hidden;
        }
        .split-bg {
            background: linear-gradient(135deg, #1976d2 0%, #d32f2f 100%);
        }
        .login-split {
            min-height: 100vh;
            display: flex;
            flex-direction: row;
            position: relative;
        }
        .login-left {
            flex: 1;
            color: #fff;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 3rem 2rem;
            background: linear-gradient(135deg, #1976d2 0%, #d32f2f 100%);
            position: relative;
            overflow: hidden;
        }
        .login-left::before {
            content: '';
            position: absolute;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 60%);
            animation: rotate 20s linear infinite;
        }
        .login-left .welcome {
            animation: fadeInLeft 1.2s;
            position: relative;
            z-index: 2;
        }
        .login-left .logo {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            box-shadow: 0 8px 32px rgba(25, 118, 210, 0.15);
            animation: floatLogo 3s ease-in-out infinite;
            position: relative;
            z-index: 2;
            transition: transform 0.3s ease;
        }
        .login-left .logo:hover {
            transform: scale(1.1) rotate(5deg);
        }
        .login-left svg {
            margin-top: 2rem;
            width: 90%;
            max-width: 340px;
            animation: fadeInUp 1.5s;
            position: relative;
            z-index: 2;
        }
        .login-right {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fff;
            min-width: 350px;
            animation: fadeInRight 1.2s;
            position: relative;
        }
        .login-card {
            width: 100%;
            max-width: 400px;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 10px 30px rgba(25, 118, 210, 0.10), 0 1.5px 6px rgba(211, 47, 47, 0.08);
            padding: 2.5rem 2rem;
            animation: fadeIn 1.5s, cardPop 0.7s cubic-bezier(.4,2,.3,1);
            position: relative;
            overflow: hidden;
        }
        .login-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(
                45deg,
                transparent,
                rgba(255, 255, 255, 0.1),
                transparent
            );
            transform: rotate(45deg);
            animation: shine 3s infinite;
        }
        .login-card h1 {
            color: #1976d2;
            font-weight: 800;
            position: relative;
        }
        .login-card .login-btn {
            background: linear-gradient(90deg, #1976d2 60%, #d32f2f 100%);
            color: #fff;
            font-weight: 700;
            border: none;
            border-radius: 8px;
            height: 48px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .login-card .login-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(
                90deg,
                transparent,
                rgba(255, 255, 255, 0.2),
                transparent
            );
            transition: 0.5s;
        }
        .login-card .login-btn:hover::before {
            left: 100%;
        }
        .login-card .login-btn:hover {
            box-shadow: 0 5px 15px rgba(25, 118, 210, 0.25);
            transform: translateY(-2px) scale(1.03);
            background: linear-gradient(90deg, #d32f2f 0%, #1976d2 100%);
        }
        .form-input {
            position: relative;
            margin-bottom: 22px;
            transition: transform 0.3s ease;
        }
        .form-input:focus-within {
            transform: translateY(-2px);
        }
        .form-input i {
            position: absolute;
            top: 50%;
            left: 16px;
            transform: translateY(-50%);
            color: #1976d2;
            z-index: 2;
            font-size: 1.1rem;
            transition: color 0.3s ease;
        }
        .form-input:focus-within i {
            color: #d32f2f;
        }
        .form-input input {
            padding-left: 45px;
            padding-right: 40px;
            height: 48px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .form-input input:focus {
            box-shadow: 0 0 0 3px rgba(25, 118, 210, 0.1);
        }
        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        @keyframes shine {
            0% { transform: translateX(-100%) rotate(45deg); }
            100% { transform: translateX(100%) rotate(45deg); }
        }
        .login-footer {
            margin-top: 2.5rem;
            text-align: center;
            color: #888;
            font-size: 0.95rem;
            opacity: 0.85;
        }
        .login-footer .quote {
            font-style: italic;
            color: #1976d2;
            font-size: 1.05rem;
            margin-bottom: 0.5rem;
            display: block;
        }
        
        /* 2FA Specific Styles */
        .animated-error {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: none;
            animation: slideInFromTop 0.5s ease-out;
            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
        }
        
        .animated-success {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: none;
            animation: slideInFromTop 0.5s ease-out;
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
        }
        
        .letter-spacing-wide {
            letter-spacing: 0.3em;
        }
        
        #otp_code {
            font-size: 1.5rem !important;
            text-align: center;
            letter-spacing: 0.5em;
            font-weight: bold;
        }
        
        #otp_code:focus {
            box-shadow: 0 0 0 3px rgba(25, 118, 210, 0.2);
            border-color: #1976d2;
        }
        
        .otp-timer {
            background: linear-gradient(135deg, #ff6b6b, #ee5a52);
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-weight: bold;
            display: inline-block;
            margin-top: 10px;
            animation: pulse 2s infinite;
        }
        
        @keyframes slideInFromTop {
            0% {
                opacity: 0;
                transform: translateY(-20px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }
        
        /* Loading spinner for buttons */
        .spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
            margin-right: 8px;
        }
        
        /* Responsive design for mobile */
        @media (max-width: 768px) {
            .login-split {
                flex-direction: column;
            }
            .login-left {
                min-height: 40vh;
                padding: 2rem 1rem;
            }
            .login-right {
                min-height: 60vh;
                padding: 1rem;
            }
            .login-card {
                padding: 2rem 1.5rem;
                margin: 1rem;
                max-width: none;
            }
            #otp_code {
                font-size: 1.2rem !important;
                letter-spacing: 0.3em;
            }
        }
    </style>
</head>
<body>
    <!-- Loading Screen -->
    <div id="loadingScreen">
        <video class="loading-video" autoplay muted loop playsinline>
            <source src="../assets/video/logo_animation.mp4" type="video/mp4">
            Your browser does not support the video tag.
        </video>
        <!-- Skip Button -->
        <button id="skipButton" class="skip-btn" onclick="skipLoadingScreen()">
            <i class="fas fa-forward"></i>
            <span>Skip</span>
        </button>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="login-split">
            <!-- Left Side -->
            <div class="login-left">
                <div class="logo shadow-lg">
                    <img src="../assets/img/logo-whitebg.png" alt="James Polymers Logo" style="width: 180px; height: 180px; border-radius: 50%; object-fit: contain; background: transparent; display: block; margin: 0 auto;" />
                </div>
                <div class="welcome text-center mt-2">
                    <h2 class="text-3xl font-extrabold mb-2 tracking-tight">Welcome Back!</h2>
                    <p class="text-lg font-medium mb-4">James Polymers Admin Portal</p>
                    <p class="text-base text-blue-100 mb-2">Empowering Excellence in Polymer Solutions</p>
                </div>
                <!-- Enhanced SVG Illustration -->
                <svg viewBox="0 0 400 220" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <linearGradient id="grad1" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" style="stop-color:#1976d2;stop-opacity:0.2" />
                            <stop offset="100%" style="stop-color:#d32f2f;stop-opacity:0.2" />
                        </linearGradient>
                        <filter id="glow">
                            <feGaussianBlur stdDeviation="2" result="coloredBlur"/>
                            <feMerge>
                                <feMergeNode in="coloredBlur"/>
                                <feMergeNode in="SourceGraphic"/>
                            </feMerge>
                        </filter>
                    </defs>
                    <ellipse cx="200" cy="200" rx="180" ry="20" fill="#fff" fill-opacity="0.12"/>
                    <rect x="60" y="60" width="280" height="100" rx="30" fill="url(#grad1)" filter="url(#glow)"/>
                    <circle cx="120" cy="120" r="40" fill="#1976d2" fill-opacity="0.18" filter="url(#glow)"/>
                    <circle cx="280" cy="120" r="40" fill="#d32f2f" fill-opacity="0.18" filter="url(#glow)"/>
                    <rect x="170" y="90" width="60" height="60" rx="18" fill="#fff" fill-opacity="0.18" filter="url(#glow)"/>
                    <path d="M120 120 L280 120" stroke="#fff" stroke-opacity="0.2" stroke-width="2" stroke-dasharray="5,5">
                        <animate attributeName="stroke-dashoffset" from="0" to="20" dur="1s" repeatCount="indefinite"/>
                    </path>
                </svg>
            </div>
            <!-- Right Side -->
            <div class="login-right">
                <div class="login-card" id="loginCard">
                    <?php if ($step == 'login'): ?>
                    <!-- Login Form -->
                    <h1 class="text-2xl font-bold mb-2">Admin Login</h1>
                    <p class="text-gray-500 mb-4">Enter your credentials to access the admin panel</p>
                    <div id="clientError" style="display:none;"></div>
                    <?php if (!empty($error)): ?>
                    <div class="alert alert-danger animated-error" role="alert">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <?php echo $error; ?>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($success)): ?>
                    <div class="alert alert-success animated-success" role="alert">
                        <i class="fas fa-check-circle mr-2"></i>
                        <?php echo $success; ?>
                    </div>
                    <?php endif; ?>
                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" id="loginForm" autocomplete="off" onsubmit="return validateLogin(event)">
                        <input type="hidden" name="action" value="login">
                        <div class="form-input">
                            <i class="fas fa-user"></i>
                            <input type="email" name="username" id="username" placeholder="Email" value="<?php echo htmlspecialchars($username); ?>" class="w-full border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" required autocomplete="username">
                        </div>
                        <div class="form-input">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="password" id="password" placeholder="Password" class="w-full border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent pr-12" required autocomplete="current-password">
                            <span class="show-hide" onclick="togglePassword()" style="right: 45px; top: 50%; transform: translateY(-50%); position: absolute; cursor: pointer; z-index: 2;">
                                <i class="fas fa-eye" id="toggleIcon"></i>
                            </span>
                        </div>
                        <div class="flex items-center justify-between mb-6">
                            <label class="flex items-center">
                                <input type="checkbox" class="form-checkbox h-4 w-4 text-primary">
                                <span class="ml-2 text-sm text-gray-600">Remember me</span>
                            </label>
                            <a href="#" class="text-sm text-primary hover:text-secondary">Forgot password?</a>
                        </div>
                        <button type="submit" class="login-btn w-full mt-2" id="loginBtn">
                            <span id="loginBtnText">Continue with 2FA</span>
                        </button>
                    </form>
                    
                    <?php elseif ($step == 'otp'): ?>
                    <!-- OTP Verification Form -->
                    <div class="text-center mb-4">
                        <div class="bg-primary bg-opacity-10 w-20 h-20 rounded-full flex items-center justify-center mb-4 mx-auto">
                            <i class="fas fa-shield-alt text-primary text-3xl"></i>
                        </div>
                        <h1 class="text-2xl font-bold mb-2 text-primary">Two-Factor Authentication</h1>
                        <p class="text-gray-500 mb-4">Enter the 6-digit OTP sent to your email</p>
                        <p class="text-sm text-gray-600">
                            <i class="fas fa-envelope mr-1"></i>
                            <?php echo isset($_SESSION['otp_admin_email']) ? maskEmail($_SESSION['otp_admin_email']) : ''; ?>
                        </p>
                    </div>
                    
                    <?php if (!empty($error)): ?>
                    <div class="alert alert-danger animated-error" role="alert">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <?php echo $error; ?>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($success)): ?>
                    <div class="alert alert-success animated-success" role="alert">
                        <i class="fas fa-check-circle mr-2"></i>
                        <?php echo $success; ?>
                    </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" id="otpForm" autocomplete="off" onsubmit="return validateOTP(event)">
                        <input type="hidden" name="action" value="verify_otp">
                        <div class="form-input">
                            <i class="fas fa-key"></i>
                            <input type="text" name="otp_code" id="otp_code" placeholder="Enter 6-digit OTP" 
                                   class="w-full border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent text-center text-2xl font-mono letter-spacing-wide" 
                                   required maxlength="6" pattern="[0-9]{6}" autocomplete="one-time-code"
                                   oninput="this.value=this.value.replace(/[^0-9]/g,''); if(this.value.length === 6) document.getElementById('verifyBtn').focus();">
                        </div>
                        
                        <div class="flex flex-col gap-3 mt-6">
                            <button type="submit" class="login-btn w-full" id="verifyBtn">
                                <span id="verifyBtnText">
                                    <i class="fas fa-check mr-2"></i>Verify & Login
                                </span>
                            </button>
                            
                            <div class="flex gap-2">
                                <button type="button" onclick="resendOTP()" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-3 px-4 rounded-lg transition duration-300" id="resendBtn">
                                    <i class="fas fa-redo mr-2"></i>
                                    <span id="resendBtnText">Resend OTP</span>
                                </button>
                                
                                <button type="button" onclick="backToLogin()" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-3 px-4 rounded-lg transition duration-300">
                                    <i class="fas fa-arrow-left mr-2"></i>Back
                                </button>
                            </div>
                        </div>
                    </form>
                    
                    <!-- OTP Timer -->
                    <div class="text-center mt-4">
                        <p class="text-sm text-gray-600">
                            <i class="fas fa-clock mr-1"></i>
                            OTP expires in: <span id="otpTimer" class="font-mono font-bold text-red-600">10:00</span>
                        </p>
                    </div>
                    <?php endif; ?>
                    
                    <div class="text-center mt-6">
                        <a href="../index.php" class="text-sm text-gray-600 hover:text-primary">
                            <i class="fas fa-arrow-left mr-2"></i> Back to Website
                        </a>
                    </div>
                    <div class="login-footer mt-8">
                        <span class="quote">"Quality is never an accident; it is always the result of intelligent effort."</span>
                        &copy; <?php echo date('Y'); ?> James Polymers. All rights reserved.
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Skip loading screen function
    function skipLoadingScreen() {
        const loadingScreen = document.getElementById('loadingScreen');
        const mainContent = document.querySelector('.main-content');
        
        // Fade out loading screen immediately
        loadingScreen.classList.add('fade-out');
        
        // Show main content
        mainContent.classList.add('show');
        
        // Remove loading screen after fade out
        setTimeout(function() {
            loadingScreen.style.display = 'none';
        }, 500);
    }

    // Loading screen functionality
    document.addEventListener('DOMContentLoaded', function() {
        const loadingScreen = document.getElementById('loadingScreen');
        const mainContent = document.querySelector('.main-content');
        const video = document.querySelector('.loading-video');
        
        // Check if user is coming from logout
        const urlParams = new URLSearchParams(window.location.search);
        const fromLogout = urlParams.get('from') === 'logout';
        
        // Check if we're in OTP step (PHP variable passed to JavaScript)
        const currentStep = '<?php echo $step; ?>';
        
        if (fromLogout || currentStep === 'otp') {
            // Skip loading screen if coming from logout or already in OTP step
            loadingScreen.style.display = 'none';
            mainContent.classList.add('show');
        } else {
            // Show loading screen for at least 8 seconds (only on fresh page load)
            setTimeout(function() {
                // Fade out loading screen
                loadingScreen.classList.add('fade-out');
                
                // Show main content
                mainContent.classList.add('show');
                
                // Remove loading screen after fade out
                setTimeout(function() {
                    loadingScreen.style.display = 'none';
                }, 500);
            }, 8000);
            
            // If video ends before 8 seconds, wait for the full 8 seconds
            video.addEventListener('ended', function() {
                // Video will loop, so this won't trigger unless there's an issue
            });
        }
    });

    // Show/hide password toggle
    function togglePassword() {
        var pwd = document.getElementById('password');
        var icon = document.getElementById('toggleIcon');
        if (pwd.type === 'password') {
            pwd.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            pwd.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    // Client-side validation and loading spinner
    function validateLogin(e) {
        var email = document.getElementById('username').value.trim();
        var pwd = document.getElementById('password').value;
        var errorDiv = document.getElementById('clientError');
        errorDiv.style.display = 'none';
        errorDiv.innerHTML = '';
        var valid = true;
        var errorMsg = '';
        // Email format validation
        var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!email) {
            errorMsg = 'Email is required.';
            valid = false;
        } else if (!emailPattern.test(email)) {
            errorMsg = 'Please enter a valid email address.';
            valid = false;
        } else if (!pwd) {
            errorMsg = 'Password is required.';
            valid = false;
        }
        if (!valid) {
            errorDiv.innerHTML = '<div class="animated-error">' + errorMsg + '</div>';
            errorDiv.style.display = 'block';
            return false;
        }
        // Show loading spinner on button
        var btn = document.getElementById('loginBtn');
        var btnText = document.getElementById('loginBtnText');
        btn.disabled = true;
        btnText.innerHTML = '<span class="spinner"></span>Sending OTP...';
        return true;
    }

    // OTP validation function
    function validateOTP(e) {
        var otpCode = document.getElementById('otp_code').value.trim();
        var errorDiv = document.getElementById('clientError');
        if (errorDiv) {
            errorDiv.style.display = 'none';
            errorDiv.innerHTML = '';
        }
        
        if (!otpCode) {
            if (errorDiv) {
                errorDiv.innerHTML = '<div class="animated-error">OTP is required.</div>';
                errorDiv.style.display = 'block';
            }
            return false;
        }
        
        if (otpCode.length !== 6 || !/^\d{6}$/.test(otpCode)) {
            if (errorDiv) {
                errorDiv.innerHTML = '<div class="animated-error">Please enter a valid 6-digit OTP.</div>';
                errorDiv.style.display = 'block';
            }
            return false;
        }
        
        // Show loading spinner
        var btn = document.getElementById('verifyBtn');
        var btnText = document.getElementById('verifyBtnText');
        btn.disabled = true;
        btnText.innerHTML = '<span class="spinner"></span>Verifying...';
        return true;
    }

    // Resend OTP function
    function resendOTP() {
        var btn = document.getElementById('resendBtn');
        var btnText = document.getElementById('resendBtnText');
        
        btn.disabled = true;
        btnText.innerHTML = '<span class="spinner"></span>Sending...';
        
        // Create form to resend OTP
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = window.location.href;
        
        var actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'resend_otp';
        form.appendChild(actionInput);
        
        document.body.appendChild(form);
        form.submit();
    }

    // Back to login function
    function backToLogin() {
        // Clear session and redirect
        window.location.href = 'clear_otp_session.php';
    }

    // OTP Timer functionality
    function startOTPTimer() {
        var timerElement = document.getElementById('otpTimer');
        if (!timerElement) return;
        
        var timeLeft = 600; // 10 minutes in seconds
        
        function updateTimer() {
            var minutes = Math.floor(timeLeft / 60);
            var seconds = timeLeft % 60;
            
            timerElement.textContent = 
                (minutes < 10 ? '0' : '') + minutes + ':' + 
                (seconds < 10 ? '0' : '') + seconds;
            
            if (timeLeft <= 60) {
                timerElement.style.color = '#dc3545';
                timerElement.style.fontWeight = 'bold';
            } else if (timeLeft <= 180) {
                timerElement.style.color = '#fd7e14';
            }
            
            if (timeLeft <= 0) {
                timerElement.textContent = 'EXPIRED';
                timerElement.style.color = '#dc3545';
                clearInterval(timerInterval);
                
                // Disable OTP input and show message
                var otpInput = document.getElementById('otp_code');
                var verifyBtn = document.getElementById('verifyBtn');
                if (otpInput) otpInput.disabled = true;
                if (verifyBtn) verifyBtn.disabled = true;
                
                alert('OTP has expired. Please request a new one.');
            } else {
                timeLeft--;
            }
        }
        
        updateTimer();
        var timerInterval = setInterval(updateTimer, 1000);
    }

    // Auto-focus and format OTP input
    function setupOTPInput() {
        var otpInput = document.getElementById('otp_code');
        if (otpInput) {
            otpInput.focus();
            
            // Auto-submit when 6 digits entered
            otpInput.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '');
                if (this.value.length === 6) {
                    setTimeout(function() {
                        document.getElementById('verifyBtn').focus();
                    }, 100);
                }
            });
            
            // Handle paste
            otpInput.addEventListener('paste', function(e) {
                e.preventDefault();
                var paste = (e.clipboardData || window.clipboardData).getData('text');
                var numbers = paste.replace(/[^0-9]/g, '');
                if (numbers.length >= 6) {
                    this.value = numbers.substring(0, 6);
                    setTimeout(function() {
                        document.getElementById('verifyBtn').focus();
                    }, 100);
                }
            });
        }
    }

    // Add particle background effect and initialize features
    document.addEventListener('DOMContentLoaded', function() {
        const loginLeft = document.querySelector('.login-left');
        for (let i = 0; i < 50; i++) {
            const particle = document.createElement('div');
            particle.className = 'particle';
            particle.style.cssText = `
                position: absolute;
                width: ${Math.random() * 5 + 2}px;
                height: ${Math.random() * 5 + 2}px;
                background: rgba(255, 255, 255, ${Math.random() * 0.3});
                border-radius: 50%;
                left: ${Math.random() * 100}%;
                top: ${Math.random() * 100}%;
                pointer-events: none;
                animation: float ${Math.random() * 10 + 5}s linear infinite;
            `;
            loginLeft.appendChild(particle);
        }
        
        // Initialize OTP features if on OTP step
        if (document.getElementById('otp_code')) {
            setupOTPInput();
            startOTPTimer();
        }
    });

    // Add floating animation for particles
    const style = document.createElement('style');
    style.textContent = `
        @keyframes float {
            0% { transform: translateY(0) translateX(0); }
            50% { transform: translateY(-20px) translateX(10px); }
            100% { transform: translateY(0) translateX(0); }
        }
    `;
    document.head.appendChild(style);
    </script>
</body>
</html>
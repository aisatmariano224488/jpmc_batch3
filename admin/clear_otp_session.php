<?php
// // Start session
// session_start();

// Clear OTP session data
unset($_SESSION['otp_admin_id']);
unset($_SESSION['otp_admin_email']);
unset($_SESSION['otp_admin_name']);

// Redirect back to login page
header("Location: admin_login.php");
exit;
?>

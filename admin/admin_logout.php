<?php
// // Start session
// session_start();

// Unset all session variables
$_SESSION = array();

// // Destroy the session
// session_destroy();

// Redirect to login page with logout parameter to skip loading screen
header("Location: admin_login.php?from=logout");
exit;
?>
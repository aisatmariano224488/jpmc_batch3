<?php
// Database credentials for the 'counter' database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "";

// Create connection
$conn_counter = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn_counter->connect_error) {
    die("Connection failed: " . $conn_counter->connect_error);
}

// Set charset
$conn_counter->set_charset("utf8");
?>

<?php
// Database credentials
$servername = "localhost";
$username = "u637871113_jamespolymers"; 
$password = "j@m3sP0lymers!@@"; 
$dbname = "u637871113_jpmc";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8");
?>
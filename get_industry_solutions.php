<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "JPMC";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['error' => "Connection failed: " . $conn->connect_error]));
}

header('Content-Type: application/json');

if (!isset($_GET['industry_id'])) {
    die(json_encode(['error' => 'Industry ID not provided']));
}

$industryId = intval($_GET['industry_id']);

// Get all solutions for this industry
$sql = "SELECT solution FROM industry_solutions WHERE industry_id = $industryId";
$result = $conn->query($sql);

$solutions = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $solutions[] = $row['solution'];
    }
}

// Get industry details
$sql = "SELECT * FROM industries WHERE id = $industryId";
$result = $conn->query($sql);
$industry = $result->fetch_assoc();

echo json_encode([
    'industry' => $industry,
    'solutions' => $solutions
]);

$conn->close();

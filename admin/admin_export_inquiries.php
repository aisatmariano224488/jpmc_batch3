<?php
// Start the session
// session_start();

// // Check if user is logged in
// if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
//     // Redirect to login page if not logged in or not an admin
//     header("Location: admin_login.php");
//     exit();
// }

// Include database connection
include '../includes/db_connection.php';

// Validate database connection
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=inquiries_export_' . date('Y-m-d') . '.csv');
header('Pragma: no-cache');
header('Expires: 0');

// Create a file pointer connected to the output stream
$output = fopen('php://output', 'w');

// Add BOM to fix UTF-8 in Excel
fputs($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

// Set column headers
fputcsv($output, array('ID', 'Name', 'Email', 'Phone', 'Company', 'Subject', 'Message', 'Date Submitted', 'Status'));

// Get data from database with filters
$query = "SELECT * FROM inquiries";
$where_clauses = [];

// Apply status filter
if (isset($_GET['status']) && !empty($_GET['status'])) {
    $status = mysqli_real_escape_string($conn, $_GET['status']);
    $where_clauses[] = "status = '$status'";
}

// Apply date range filter
if (isset($_GET['date_range']) && !empty($_GET['date_range'])) {
    $date_range = $_GET['date_range'];
    
    switch($date_range) {
        case 'today':
            $where_clauses[] = "DATE(date_submitted) = CURDATE()";
            break;
        case 'week':
            $where_clauses[] = "YEARWEEK(date_submitted, 1) = YEARWEEK(CURDATE(), 1)";
            break;
        case 'month':
            $where_clauses[] = "YEAR(date_submitted) = YEAR(CURDATE()) AND MONTH(date_submitted) = MONTH(CURDATE())";
            break;
    }
}

// Apply search filter
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $where_clauses[] = "(name LIKE '%$search%' OR email LIKE '%$search%' OR subject LIKE '%$search%' OR message LIKE '%$search%')";
}

// Add WHERE clause if any filters are set
if (!empty($where_clauses)) {
    $query .= " WHERE " . implode(" AND ", $where_clauses);
}

$query .= " ORDER BY date_submitted DESC";

// Execute query with error handling
$result = $conn->query($query);
if (!$result) {
    die("Query failed: " . $conn->error);
}

// Check if there are any results
if ($result->num_rows > 0) {
    // Loop over the rows, outputting them
    while ($row = $result->fetch_assoc()) {
        // Clean and prepare data for CSV
        $csv_row = array(
            $row['id'],
            html_entity_decode($row['name'], ENT_QUOTES, 'UTF-8'),
            $row['email'],
            $row['phone'],
            html_entity_decode($row['company'], ENT_QUOTES, 'UTF-8'),
            html_entity_decode($row['subject'], ENT_QUOTES, 'UTF-8'),
            html_entity_decode($row['message'], ENT_QUOTES, 'UTF-8'),
            $row['date_submitted'],
            $row['status']
        );
        fputcsv($output, $csv_row);
    }
} else {
    // If no results, output a message
    fputcsv($output, array('No records found'));
}

// Close the output stream
fclose($output);

// Close database connection
$conn->close();
exit();
?>
<?php
require_once 'includes/db_connection.php';

// Function to get page configuration
function get_page_config($page_name)
{
    global $conn;

    $sql = "SELECT * FROM page_configs WHERE page_name = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $page_name);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }

    // Return default configuration if page not found
    return array(
        'header_bg' => 'images/sustainability/header.png',
        'header_title' => strtoupper(str_replace('_', ' ', $page_name)),
        'header_overlay' => 'rgba(37,80,200,0.38)',
        'left_badge' => 'images/sustainability/beslogo.png',
        'right_badge' => 'images/sustainability/beslogo.png',
        'coming_soon' => 'images/sustainability/comingsoon.jfif'
    );
}

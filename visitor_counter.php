<?php
session_start();

// Configuration: Path to counter file
define('COUNTER_FILE', __DIR__ . '/counter.txt');
define('COUNTER_START_VALUE', 15000); // Fallback if file doesn't exist

// Initialize counter file if it doesn't exist
if (!file_exists(COUNTER_FILE)) {
    file_put_contents(COUNTER_FILE, COUNTER_START_VALUE);
}

// Increment once per session
if (!isset($_SESSION['visitor_counted'])) {
    // Read current count
    $counter = (int)file_get_contents(COUNTER_FILE);
    
    // Increment
    $counter++;
    
    // Save back to file
    file_put_contents(COUNTER_FILE, $counter);
    
    // Mark as counted in this session
    $_SESSION['visitor_counted'] = true;
}

// Read current count for display
$counter = (int)file_get_contents(COUNTER_FILE);
?>
<?php
session_start();

// Configuration: Path to counter file
define('COUNTER_FILE', __DIR__ . '/counter.txt');
define('COUNTER_START_VALUE', 15000); // Fallback if file doesn't exist
define('MONTHLY_FILE', __DIR__ . '/monthly_counter.json');
define('YEAR_FILE', __DIR__ . '/counter_year.txt');

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
    
    // Update monthly counter
    $currentMonth = (int)date('n') - 1; // 0-11 for array index
    $currentYear = date('Y');
    
    // Load or initialize monthly data
    if (file_exists(MONTHLY_FILE)) {
        $monthlyData = json_decode(file_get_contents(MONTHLY_FILE), true);
    } else {
        $monthlyData = array_fill(0, 12, 0);
    }
    
    // Check if it's a new year and reset if needed
    if (file_exists(YEAR_FILE)) {
        $savedYear = file_get_contents(YEAR_FILE);
        if ($savedYear != $currentYear) {
            $monthlyData = array_fill(0, 12, 0);
            file_put_contents(YEAR_FILE, $currentYear);
        }
    } else {
        file_put_contents(YEAR_FILE, $currentYear);
    }
    
    // Increment current month
    $monthlyData[$currentMonth]++;
    file_put_contents(MONTHLY_FILE, json_encode($monthlyData));
}

// Read current count for display
$counter = (int)file_get_contents(COUNTER_FILE);
?>
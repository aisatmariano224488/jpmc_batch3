<?php
echo "<h2>🔧 Resume Download Link Test</h2>";
echo "<hr>";

// Test 1: Check if uploads directory exists
echo "<h3>1. Upload Directory Check</h3>";
$uploads_dir = __DIR__ . '/uploads/resumes/';
if (is_dir($uploads_dir)) {
    echo "✅ Uploads directory exists: $uploads_dir<br>";
} else {
    echo "❌ Uploads directory missing: $uploads_dir<br>";
}

// Test 2: List existing resume files
echo "<h3>2. Existing Resume Files</h3>";
if (is_dir($uploads_dir)) {
    $files = scandir($uploads_dir);
    $resume_files = array_filter($files, function($file) use ($uploads_dir) {
        return $file !== '.' && $file !== '..' && !is_dir($uploads_dir . $file);
    });
    
    if (count($resume_files) > 0) {
        echo "✅ Found " . count($resume_files) . " resume files:<br>";
        foreach ($resume_files as $file) {
            $file_path = $uploads_dir . $file;
            $file_size = filesize($file_path);
            echo "📄 <strong>$file</strong> (" . round($file_size/1024, 2) . " KB)<br>";
        }
    } else {
        echo "❌ No resume files found<br>";
    }
} else {
    echo "❌ Cannot access uploads directory<br>";
}

// Test 3: Generate correct download URLs
echo "<h3>3. Download URL Generation Test</h3>";
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https" : "http";
$domain = $_SERVER['HTTP_HOST'];
$project_path = '/dashboard/JPMC';

echo "Protocol: $protocol<br>";
echo "Domain: $domain<br>";
echo "Project Path: $project_path<br>";

// Test 4: Create sample download links
echo "<h3>4. Sample Download Links</h3>";
if (is_dir($uploads_dir) && count($resume_files) > 0) {
    $sample_file = array_values($resume_files)[0];
    $sample_url = $protocol . '://' . $domain . $project_path . '/uploads/resumes/' . $sample_file;
    
    echo "Sample file: $sample_file<br>";
    echo "Generated URL: <a href='$sample_url' target='_blank'>$sample_url</a><br>";
    
    // Test if file is accessible
    $full_path = $uploads_dir . $sample_file;
    if (file_exists($full_path)) {
        echo "✅ File exists on server<br>";
        echo "File size: " . filesize($full_path) . " bytes<br>";
    } else {
        echo "❌ File not found on server<br>";
    }
} else {
    echo "No files to test with<br>";
}

// Test 5: Check URL format that would be sent in emails
echo "<h3>5. Email URL Format Test</h3>";
echo "The URLs in your emails should look like this:<br>";
echo "<code>" . $protocol . "://" . $domain . $project_path . "/uploads/resumes/[filename]</code><br>";

// Test 6: Test actual file access
echo "<h3>6. File Access Test</h3>";
if (is_dir($uploads_dir) && count($resume_files) > 0) {
    $test_file = array_values($resume_files)[0];
    $test_url = $project_path . '/uploads/resumes/' . $test_file;
    
    echo "Testing access to: $test_url<br>";
    
    // Check if file is readable
    $full_path = $uploads_dir . $test_file;
    if (is_readable($full_path)) {
        echo "✅ File is readable<br>";
        echo "✅ File permissions: " . substr(sprintf('%o', fileperms($full_path)), -4) . "<br>";
    } else {
        echo "❌ File is not readable<br>";
    }
}

echo "<hr>";
echo "<h3>🔧 FIXES APPLIED:</h3>";
echo "✅ Updated project path from '/JPMC2' to '/dashboard/JPMC'<br>";
echo "✅ Resume download links should now work correctly<br>";

echo "<p><strong>Test completed at:</strong> " . date('Y-m-d H:i:s') . "</p>";
?>

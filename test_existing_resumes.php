<?php
echo "<h2>📄 Test Existing Resume Files</h2>";
echo "<hr>";

$uploads_dir = __DIR__ . '/uploads/resumes/';
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https" : "http";
$domain = $_SERVER['HTTP_HOST'];
$project_path = '/dashboard/JPMC';

echo "<h3>Available Resume Files:</h3>";

if (is_dir($uploads_dir)) {
    $files = scandir($uploads_dir);
    $resume_files = array_filter($files, function($file) {
        return $file !== '.' && $file !== '..' && !is_dir($uploads_dir . $file);
    });
    
    if (count($resume_files) > 0) {
        echo "<table border='1' cellpadding='10' cellspacing='0'>";
        echo "<tr><th>File Name</th><th>Size</th><th>Download Link</th><th>Status</th></tr>";
        
        foreach ($resume_files as $file) {
            $file_path = $uploads_dir . $file;
            $file_size = filesize($file_path);
            $download_url = $protocol . '://' . $domain . $project_path . '/uploads/resumes/' . $file;
            
            echo "<tr>";
            echo "<td>$file</td>";
            echo "<td>" . round($file_size/1024, 2) . " KB</td>";
            echo "<td><a href='$download_url' target='_blank'>Download</a></td>";
            
            if (file_exists($file_path) && is_readable($file_path)) {
                echo "<td style='color: green;'>✅ Accessible</td>";
            } else {
                echo "<td style='color: red;'>❌ Not Accessible</td>";
            }
            
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "No resume files found.";
    }
} else {
    echo "❌ Uploads directory not found.";
}

echo "<hr>";
echo "<h3>🔧 URL Format:</h3>";
echo "Base URL: <code>$protocol://$domain$project_path/uploads/resumes/</code><br>";
echo "Example: <code>$protocol://$domain$project_path/uploads/resumes/filename.pdf</code><br>";

echo "<p><strong>Test completed at:</strong> " . date('Y-m-d H:i:s') . "</p>";
?>

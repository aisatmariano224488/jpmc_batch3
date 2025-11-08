<?php
// Database credentials
$servername = "localhost";
$username = "root"; 
$password = ""; 
$dbname = "jpmc";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h2>Setting up Headline Articles Table</h2>";

// Create headline_articles table
$create_table_sql = "
CREATE TABLE IF NOT EXISTS `headline_articles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `image_path` varchar(500) NOT NULL,
  `date` date NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

if ($conn->query($create_table_sql) === TRUE) {
    echo "<p style='color: green;'>✓ Table 'headline_articles' created successfully or already exists</p>";
} else {
    echo "<p style='color: red;'>✗ Error creating table: " . $conn->error . "</p>";
}

// Check if default headline article exists
$check_sql = "SELECT COUNT(*) as count FROM headline_articles";
$result = $conn->query($check_sql);
$row = $result->fetch_assoc();

if ($row['count'] == 0) {
    // Insert default headline article
    $insert_sql = "
    INSERT INTO `headline_articles` (`title`, `description`, `image_path`, `date`, `is_active`) VALUES
    ('JPMC Achieves Major Innovation Breakthrough in Sustainable Polymer Technology', 
    'James Polymers Manufacturing Corporation (JPMC) has successfully developed a revolutionary eco-friendly polymer solution that reduces environmental impact by 60% while maintaining superior performance standards. This breakthrough positions JPMC as a leader in sustainable manufacturing innovation.',
    'assets/img/sustainability/Background1.jpg',
    '2024-12-15',
    1);
    ";
    
    if ($conn->query($insert_sql) === TRUE) {
        echo "<p style='color: green;'>✓ Default headline article inserted successfully</p>";
    } else {
        echo "<p style='color: red;'>✗ Error inserting default headline article: " . $conn->error . "</p>";
    }
} else {
    echo "<p style='color: blue;'>ℹ Headline articles already exist in the database</p>";
}

// Create headlines directory if it doesn't exist
$headlines_dir = 'assets/img/headlines/';
if (!file_exists($headlines_dir)) {
    if (mkdir($headlines_dir, 0777, true)) {
        echo "<p style='color: green;'>✓ Headlines directory created successfully</p>";
    } else {
        echo "<p style='color: red;'>✗ Error creating headlines directory</p>";
    }
} else {
    echo "<p style='color: blue;'>ℹ Headlines directory already exists</p>";
}

$conn->close();

echo "<h3>Setup Complete!</h3>";
echo "<p>You can now:</p>";
echo "<ul>";
echo "<li>Access the admin panel at: <a href='admin/admin_headline_articles.php'>Manage Headline Articles</a></li>";
echo "<li>View the news page at: <a href='news_events.php'>News & Events</a></li>";
echo "<li>Edit headline content directly in phpMyAdmin in the 'headline_articles' table</li>";
echo "</ul>";

echo "<h4>Database Table Structure:</h4>";
echo "<ul>";
echo "<li><strong>id</strong> - Auto-increment primary key</li>";
echo "<li><strong>title</strong> - Headline article title</li>";
echo "<li><strong>description</strong> - Article description/content</li>";
echo "<li><strong>image_path</strong> - Path to the headline image</li>";
echo "<li><strong>date</strong> - Publication date</li>";
echo "<li><strong>is_active</strong> - Whether this headline is currently active (only one can be active)</li>";
echo "<li><strong>created_at</strong> - When the record was created</li>";
echo "<li><strong>updated_at</strong> - When the record was last updated</li>";
echo "</ul>";
?>



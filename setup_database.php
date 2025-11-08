<?php
// Database Setup Script for JPMC Chatbot
// This script will create the database and tables automatically

echo "<h2>JPMC Database Setup</h2>";

// Database credentials (same as in db_connection.php)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "jpmc";

try {
    // Create connection without specifying database first
    $conn = new mysqli($servername, $username, $password);

    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    echo "<p>✅ Connected to MySQL server successfully</p>";

    // Create database
    $sql = "CREATE DATABASE IF NOT EXISTS $dbname";
    if ($conn->query($sql) === TRUE) {
        echo "<p>✅ Database '$dbname' created successfully or already exists</p>";
    } else {
        throw new Exception("Error creating database: " . $conn->error);
    }

    // Select the database
    $conn->select_db($dbname);
    echo "<p>✅ Database '$dbname' selected</p>";

    // Create faqs table
    $sql = "CREATE TABLE IF NOT EXISTS faqs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        question TEXT NOT NULL,
        answer TEXT NOT NULL,
        category VARCHAR(100) DEFAULT 'General',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";

    if ($conn->query($sql) === TRUE) {
        echo "<p>✅ Table 'faqs' created successfully or already exists</p>";
    } else {
        throw new Exception("Error creating table: " . $conn->error);
    }

    // Check if table already has data
    $result = $conn->query("SELECT COUNT(*) as count FROM faqs");
    $row = $result->fetch_assoc();
    $existingCount = $row['count'];

    if ($existingCount == 0) {
        // Insert sample FAQ data
        $sampleData = [
            // General Information
            ['What is injection molding?', 'Injection molding is a manufacturing process where molten polymer material is injected into a mold cavity under high pressure. The material cools and solidifies, taking the shape of the mold cavity. This process is widely used for producing plastic parts in high volumes with excellent precision and repeatability.', 'General Information'],
            ['How does injection molding work?', 'The injection molding process consists of four main stages: 1) Clamping - The mold is securely closed, 2) Injection - Molten plastic is injected into the mold cavity, 3) Cooling - The plastic cools and solidifies, 4) Ejection - The finished part is ejected from the mold. This cycle repeats continuously for mass production.', 'General Information'],
            ['What materials can be used in injection molding?', 'Common materials include ABS (Acrylonitrile Butadiene Styrene), Polypropylene (PP), Polyethylene (PE), Polycarbonate (PC), Nylon (PA), and many others. Material selection depends on factors like strength requirements, temperature resistance, chemical resistance, and cost considerations.', 'General Information'],
            ['What are the advantages of injection molding?', 'Key advantages include: high production rates, excellent part consistency and repeatability, complex part geometries, minimal material waste, ability to use multiple materials and colors, and cost-effectiveness for large production runs. The process also allows for excellent surface finish and dimensional accuracy.', 'General Information'],

            // Technical Details
            ['What is the typical cycle time for injection molding?', 'Cycle times vary widely depending on part size, material, wall thickness, and complexity. Small parts might have cycle times of 10-30 seconds, while larger or more complex parts can take 1-5 minutes. Factors affecting cycle time include cooling time (usually the longest phase), injection time, and mold opening/closing time.', 'Technical Details'],
            ['How do I calculate the cost of injection molding?', 'Injection molding costs include: 1) Tooling costs (mold design and fabrication), 2) Material costs per part, 3) Machine time costs, 4) Labor costs, and 5) Overhead costs. Tooling is a significant upfront investment ($5,000-$100,000+) but becomes cost-effective with high production volumes (1,000+ parts).', 'Technical Details'],
            ['What are the key process parameters?', 'Critical process parameters include: 1) Melt temperature (varies by material), 2) Injection pressure (typically 500-2,000 bar), 3) Hold pressure and time, 4) Cooling time and temperature, 5) Mold temperature, and 6) Injection speed. These parameters must be carefully controlled and monitored for consistent part quality.', 'Technical Details'],
            ['How do I design parts for injection molding?', 'Design considerations include: uniform wall thickness (ideally 1-4mm), adequate draft angles (1-3°), proper gate placement, avoiding sharp corners, designing for easy ejection, and considering material shrinkage. Good design practices help reduce defects and improve production efficiency.', 'Technical Details'],

            // Quality & Standards
            ['What are common injection molding defects?', 'Common defects include: 1) Flow marks (surface imperfections), 2) Sink marks (depressions), 3) Warping (distortion), 4) Short shots (incomplete filling), 5) Flash (excess material), 6) Voids (air pockets), and 7) Burn marks. Each defect has specific causes and prevention methods.', 'Quality & Standards'],
            ['How do I prevent injection molding defects?', 'Prevention strategies include: proper material drying, optimal process parameters, good mold design, regular maintenance, quality control procedures, and operator training. Specific defects require targeted solutions - for example, sink marks can be reduced by proper gate design and cooling optimization.', 'Quality & Standards'],
            ['What quality control measures are used?', 'Quality control includes: 1) Process parameter monitoring, 2) Regular part inspection (dimensions, appearance, weight), 3) Material testing and certification, 4) Mold maintenance schedules, 5) Statistical process control (SPC), and 6) Final product testing. Documentation and traceability are essential for quality assurance.', 'Quality & Standards'],
            ['How do I maintain consistent part quality?', 'Maintain consistency through: 1) Regular process parameter monitoring, 2) Consistent material handling and drying, 3) Regular mold maintenance and cleaning, 4) Operator training and standard operating procedures, 5) Quality control checkpoints, and 6) Continuous improvement processes based on data analysis.', 'Quality & Standards'],

            // Process Optimization
            ['How can I reduce cycle time?', 'Cycle time reduction strategies include: 1) Optimizing cooling system design, 2) Using materials with faster cooling properties, 3) Reducing part wall thickness where possible, 4) Optimizing gate size and placement, 5) Using hot runner systems, 6) Implementing efficient mold design, and 7) Regular equipment maintenance.', 'Process Optimization'],
            ['What is the difference between hot and cold runner systems?', 'Hot runner systems keep the plastic molten in the mold, reducing waste and cycle time but increasing initial cost. Cold runner systems allow the plastic to solidify in the runner, creating more waste but are less expensive. Hot runners are preferred for high-volume production where material savings justify the higher upfront cost.', 'Process Optimization'],
            ['How do I optimize material usage?', 'Material optimization strategies include: 1) Proper gate design to minimize waste, 2) Using hot runner systems, 3) Optimizing part design for minimal material usage, 4) Recycling runner and sprue material, 5) Proper material handling to prevent contamination, and 6) Regular process optimization to reduce defects and rework.', 'Process Optimization'],
            ['What maintenance is required for injection molding machines?', 'Regular maintenance includes: 1) Daily cleaning and inspection, 2) Weekly lubrication and safety checks, 3) Monthly hydraulic system maintenance, 4) Quarterly electrical system inspection, 5) Annual comprehensive machine inspection, and 6) Preventive maintenance based on manufacturer recommendations. Proper maintenance extends equipment life and ensures consistent performance.', 'Process Optimization'],

            // Troubleshooting
            ['What causes short shots in injection molding?', 'Short shots occur when the mold cavity is not completely filled. Common causes include: insufficient injection pressure, low material temperature, blocked gates, inadequate venting, material contamination, and mold design issues. Solutions involve adjusting process parameters, cleaning the mold, and ensuring proper material flow.', 'Troubleshooting'],
            ['How do I fix warping issues?', 'Warping is caused by uneven cooling and internal stresses. Solutions include: 1) Optimizing cooling system design for uniform temperature distribution, 2) Adjusting cooling time and temperature, 3) Improving part design for better cooling balance, 4) Using materials with lower shrinkage, and 5) Implementing proper ejection procedures to prevent distortion.', 'Troubleshooting'],
            ['What causes sink marks and how do I prevent them?', 'Sink marks are depressions caused by material shrinkage during cooling. Prevention methods include: 1) Designing uniform wall thickness, 2) Optimizing gate placement and size, 3) Using proper hold pressure and time, 4) Implementing adequate cooling, and 5) Choosing materials with lower shrinkage rates. Good design is crucial for preventing sink marks.', 'Troubleshooting'],
            ['How do I resolve flow marks on parts?', 'Flow marks are surface imperfections caused by material flow issues. Solutions include: 1) Increasing injection speed and pressure, 2) Optimizing material temperature, 3) Improving gate design and placement, 4) Ensuring proper material drying, 5) Using materials with better flow properties, and 6) Optimizing mold temperature for the specific material.', 'Troubleshooting']
        ];

        $stmt = $conn->prepare("INSERT INTO faqs (question, answer, category) VALUES (?, ?, ?)");

        foreach ($sampleData as $data) {
            $stmt->bind_param("sss", $data[0], $data[1], $data[2]);
            $stmt->execute();
        }

        echo "<p>✅ Sample FAQ data inserted successfully</p>";
        $stmt->close();
    } else {
        echo "<p>ℹ️ Table already contains $existingCount FAQ records</p>";
    }

    // Create indexes for better performance
    $conn->query("CREATE INDEX IF NOT EXISTS idx_category ON faqs(category)");
    $conn->query("CREATE INDEX IF NOT EXISTS idx_created_at ON faqs(created_at)");
    echo "<p>✅ Database indexes created</p>";

    // Show final statistics
    $result = $conn->query("SELECT COUNT(*) as total FROM faqs");
    $row = $result->fetch_assoc();
    echo "<p><strong>Total FAQs in database: {$row['total']}</strong></p>";

    $result = $conn->query("SELECT category, COUNT(*) as count FROM faqs GROUP BY category");
    echo "<h3>FAQ Categories:</h3><ul>";
    while ($row = $result->fetch_assoc()) {
        echo "<li><strong>{$row['category']}:</strong> {$row['count']} questions</li>";
    }
    echo "</ul>";

    echo "<p><strong>🎉 Database setup completed successfully!</strong></p>";
    echo "<p>Your chatbot is now ready to use with the database FAQs.</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>

<style>
    body {
        font-family: Arial, sans-serif;
        margin: 20px;
    }

    h2 {
        color: #2563eb;
    }

    p {
        margin: 10px 0;
    }

    ul {
        margin: 10px 0;
        padding-left: 20px;
    }

    li {
        margin: 5px 0;
    }
</style>
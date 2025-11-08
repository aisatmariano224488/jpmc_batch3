<?php
echo "<h2>📝 Form Submission Test</h2>";
echo "<hr>";

// Test 1: Check if database connection works
echo "<h3>1. Database Connection Test</h3>";
try {
    require_once 'includes/db_connection.php';
    echo "✅ Database connection successful<br>";
    
    // Test if we can query the database
    $test_query = "SELECT COUNT(*) as count FROM inquiries";
    $result = $conn->query($test_query);
    if ($result) {
        $row = $result->fetch_assoc();
        echo "✅ Database queries working<br>";
        echo "Total inquiries in database: " . $row['count'] . "<br>";
    }
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
}

// Test 2: Check if Brevo API key is valid
echo "<h3>2. Brevo API Key Test</h3>";
$brevo_api_key = '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';
echo "API Key: " . substr($brevo_api_key, 0, 20) . "...<br>";

// Test 3: Simulate a contact form submission
echo "<h3>3. Simulate Contact Form Submission</h3>";
try {
    require_once 'vendor/autoload.php';
    
    $config = \Brevo\Client\Configuration::getDefaultConfiguration()->setApiKey('api-key', $brevo_api_key);
    $apiInstance = new \Brevo\Client\Api\TransactionalEmailsApi(new \GuzzleHttp\Client(), $config);
    
    // Simulate the exact email that would be sent from contact form
    $email_content = "
    <html>
    <body>
        <h2>New Contact Form Submission</h2>
        <p><strong>Name:</strong> Test User</p>
        <p><strong>Email:</strong> test@example.com</p>
        <p><strong>Phone:</strong> 123-456-7890</p>
        <p><strong>Company:</strong> Test Company</p>
        <p><strong>Position:</strong> Test Position</p>
        <p><strong>Address:</strong> Test Address</p>
        <p><strong>Subject:</strong> Test Subject</p>
        <p><strong>Priority:</strong> Medium</p>
        <p><strong>Message:</strong> This is a test message from the contact form.</p>
        <p><strong>Date:</strong> " . date('Y-m-d H:i:s') . "</p>
    </body>
    </html>
    ";
    
    $sendSmtpEmail = new \Brevo\Client\Model\SendSmtpEmail([
        'subject' => 'New Contact Form Submission: Test Subject',
        'sender' => ['name' => 'JPMC', 'email' => 'danielrossevia@gmail.com'],
        'to' => [['email' => 'danielrossevia@gmail.com', 'name' => 'Daniel Rossevia']],
        'htmlContent' => $email_content
    ]);
    
    $result = $apiInstance->sendTransacEmail($sendSmtpEmail);
    echo "✅ Contact form email sent successfully!<br>";
    echo "Result: " . json_encode($result) . "<br>";
    
} catch (Exception $e) {
    echo "❌ Contact form email failed: " . $e->getMessage() . "<br>";
    echo "Error details: " . $e->getTraceAsString() . "<br>";
}

// Test 4: Check if forms are being processed
echo "<h3>4. Form Processing Test</h3>";
echo "To test if forms are working:<br>";
echo "1. Go to your contact page: <a href='contact.php' target='_blank'>Contact Form</a><br>";
echo "2. Fill out the form and submit it<br>";
echo "3. Check if you receive an email<br>";
echo "4. Check the database for new entries<br>";

// Test 5: Check recent database entries
echo "<h3>5. Recent Database Entries</h3>";
try {
    $recent_inquiries = "SELECT * FROM inquiries ORDER BY date_submitted DESC LIMIT 5";
    $result = $conn->query($recent_inquiries);
    
    if ($result && $result->num_rows > 0) {
        echo "✅ Recent inquiries found:<br>";
        echo "<table border='1' cellpadding='5' cellspacing='0'>";
        echo "<tr><th>Name</th><th>Email</th><th>Subject</th><th>Date</th><th>Status</th></tr>";
        
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
            echo "<td>" . htmlspecialchars($row['subject']) . "</td>";
            echo "<td>" . $row['date_submitted'] . "</td>";
            echo "<td>" . $row['status'] . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "❌ No recent inquiries found in database<br>";
    }
} catch (Exception $e) {
    echo "❌ Error checking database: " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<h3>🔧 TROUBLESHOOTING STEPS:</h3>";
echo "1. <strong>Check your email:</strong> danielrossevia@gmail.com (including spam folder)<br>";
echo "2. <strong>Test the contact form:</strong> Submit a real form to see if it works<br>";
echo "3. <strong>Check Brevo account:</strong> Verify API key and credits<br>";
echo "4. <strong>Check database:</strong> See if form submissions are being saved<br>";

echo "<p><strong>Test completed at:</strong> " . date('Y-m-d H:i:s') . "</p>";
?>

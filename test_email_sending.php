<?php
echo "<h2>📧 Email Sending Test</h2>";
echo "<hr>";

// Test 1: Check if Brevo SDK loads
echo "<h3>1. Brevo SDK Test</h3>";
try {
    require_once 'vendor/autoload.php';
    echo "✅ Brevo SDK loaded successfully<br>";
} catch (Exception $e) {
    echo "❌ Error loading Brevo SDK: " . $e->getMessage() . "<br>";
    exit;
}

// Test 2: Test Brevo API with detailed error handling
echo "<h3>2. Brevo API Test</h3>";
try {
    $brevo_api_key = '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';
    
    $config = \Brevo\Client\Configuration::getDefaultConfiguration()->setApiKey('api-key', $brevo_api_key);
    $apiInstance = new \Brevo\Client\Api\TransactionalEmailsApi(new \GuzzleHttp\Client(), $config);
    
    echo "✅ Brevo API configured<br>";
    echo "API Key: " . substr($brevo_api_key, 0, 20) . "...<br>";
    
    // Test 3: Send a test email
    echo "<h3>3. Sending Test Email</h3>";
    
    $test_email = new \Brevo\Client\Model\SendSmtpEmail([
        'subject' => 'JPMC Email Test - ' . date('H:i:s'),
        'sender' => ['name' => 'JPMC Test', 'email' => 'danielrossevia@gmail.com'],
        'to' => [['email' => 'danielrossevia@gmail.com', 'name' => 'Daniel Rossevia']],
        'htmlContent' => '
        <html>
        <body style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
            <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; border-left: 4px solid #007bff;">
                <h2 style="color: #2c3e50; margin-top: 0;">🎉 JPMC Email System Test</h2>
                <p><strong>Test Time:</strong> ' . date('Y-m-d H:i:s') . '</p>
                <p><strong>Test Type:</strong> Brevo API Email Test</p>
                <p><strong>Status:</strong> ✅ Email sent successfully</p>
                <p>If you receive this email, your JPMC email system is working correctly!</p>
                <hr style="margin: 20px 0;">
                <p style="color: #666; font-size: 14px;">
                    This is a test email to verify that the email system is functioning properly.
                    You should now receive emails from contact forms and career applications.
                </p>
            </div>
        </body>
        </html>
        '
    ]);
    
    echo "Attempting to send email...<br>";
    $result = $apiInstance->sendTransacEmail($test_email);
    echo "✅ Email sent successfully!<br>";
    echo "Result: " . json_encode($result) . "<br>";
    
} catch (Exception $e) {
    echo "❌ Brevo API Error: " . $e->getMessage() . "<br>";
    echo "Error Code: " . $e->getCode() . "<br>";
    echo "Error Details: " . $e->getTraceAsString() . "<br>";
    
    // Check if it's an API key issue
    if (strpos($e->getMessage(), 'api-key') !== false) {
        echo "<br><strong>⚠️ API Key Issue:</strong> The Brevo API key might be invalid or expired.<br>";
    }
    if (strpos($e->getMessage(), 'credits') !== false) {
        echo "<br><strong>⚠️ Credits Issue:</strong> Your Brevo account might be out of credits.<br>";
    }
    if (strpos($e->getMessage(), 'network') !== false) {
        echo "<br><strong>⚠️ Network Issue:</strong> Cannot connect to Brevo servers.<br>";
    }
}

// Test 4: Test contact form email (simulate)
echo "<h3>4. Contact Form Email Test</h3>";
try {
    $contact_email = new \Brevo\Client\Model\SendSmtpEmail([
        'subject' => 'New Contact Form Submission: Test Subject',
        'sender' => ['name' => 'JPMC', 'email' => 'danielrossevia@gmail.com'],
        'to' => [['email' => 'danielrossevia@gmail.com', 'name' => 'Daniel Rossevia']],
        'htmlContent' => '
        <html>
        <body>
            <h2>New Contact Form Submission</h2>
            <p><strong>Name:</strong> Test User</p>
            <p><strong>Email:</strong> test@example.com</p>
            <p><strong>Phone:</strong> 123-456-7890</p>
            <p><strong>Subject:</strong> Test Subject</p>
            <p><strong>Message:</strong> This is a test message from the contact form.</p>
            <p><strong>Date:</strong> ' . date('Y-m-d H:i:s') . '</p>
        </body>
        </html>
        '
    ]);
    
    $result = $apiInstance->sendTransacEmail($contact_email);
    echo "✅ Contact form email sent successfully!<br>";
    
} catch (Exception $e) {
    echo "❌ Contact form email failed: " . $e->getMessage() . "<br>";
}

// Test 5: Test career application email (simulate)
echo "<h3>5. Career Application Email Test</h3>";
try {
    $career_email = new \Brevo\Client\Model\SendSmtpEmail([
        'subject' => 'New Careers Application: Test Position',
        'sender' => ['name' => 'JPMC', 'email' => 'danielrossevia@gmail.com'],
        'to' => [['email' => 'danielrossevia@gmail.com', 'name' => 'Daniel Rossevia']],
        'htmlContent' => '
        <html>
        <body>
            <h2>New Careers Application</h2>
            <p><strong>Position:</strong> Test Position</p>
            <p><strong>Name:</strong> Test Applicant</p>
            <p><strong>Email:</strong> test@example.com</p>
            <p><strong>Phone:</strong> 123-456-7890</p>
            <p><strong>Resume:</strong> <a href="http://localhost/dashboard/JPMC/uploads/resumes/test.pdf">Download</a></p>
        </body>
        </html>
        '
    ]);
    
    $result = $apiInstance->sendTransacEmail($career_email);
    echo "✅ Career application email sent successfully!<br>";
    
} catch (Exception $e) {
    echo "❌ Career application email failed: " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<h3>📧 CHECK YOUR EMAIL NOW!</h3>";
echo "1. Check your INBOX: danielrossevia@gmail.com<br>";
echo "2. Check your SPAM/JUNK folder<br>";
echo "3. Look for emails with subjects containing 'JPMC' or 'Test'<br>";
echo "4. If you don't receive emails, the issue might be:<br>";
echo "   - Brevo API key is invalid/expired<br>";
echo "   - Brevo account has no credits<br>";
echo "   - Network connectivity issues<br>";
echo "   - Gmail is blocking the emails<br>";

echo "<p><strong>Test completed at:</strong> " . date('Y-m-d H:i:s') . "</p>";
?>

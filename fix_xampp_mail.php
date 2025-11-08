<?php
echo "<h2>🔧 XAMPP Mail Configuration Fix</h2>";
echo "<hr>";

// Method 1: Configure PHP to use Gmail SMTP
echo "<h3>1. Configuring PHP Mail Settings</h3>";

// Set SMTP settings
ini_set('SMTP', 'smtp.gmail.com');
ini_set('smtp_port', '587');
ini_set('sendmail_from', 'danielrossevia@gmail.com');

echo "✅ SMTP settings configured<br>";
echo "SMTP Server: smtp.gmail.com<br>";
echo "SMTP Port: 587<br>";
echo "From Email: danielrossevia@gmail.com<br>";

// Method 2: Test with Brevo API (which should work)
echo "<h3>2. Testing Brevo API (Recommended Method)</h3>";

try {
    require_once 'vendor/autoload.php';
    
    $brevo_api_key = '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';
    
    $config = \Brevo\Client\Configuration::getDefaultConfiguration()->setApiKey('api-key', $brevo_api_key);
    $apiInstance = new \Brevo\Client\Api\TransactionalEmailsApi(new \GuzzleHttp\Client(), $config);
    
    echo "✅ Brevo API configured successfully<br>";
    
    // Send test email via Brevo
    $test_email = new \Brevo\Client\Model\SendSmtpEmail([
        'subject' => 'JPMC Email Test - ' . date('H:i:s'),
        'sender' => ['name' => 'JPMC System', 'email' => 'danielrossevia@gmail.com'],
        'to' => [['email' => 'danielrossevia@gmail.com', 'name' => 'Daniel Rossevia']],
        'htmlContent' => '
        <html>
        <body>
            <h2>🎉 Email System Test</h2>
            <p>This email was sent via Brevo API at ' . date('Y-m-d H:i:s') . '</p>
            <p>If you receive this, the email system is working!</p>
            <p><strong>Test Type:</strong> Brevo API Test</p>
        </body>
        </html>
        '
    ]);
    
    $result = $apiInstance->sendTransacEmail($test_email);
    echo "✅ Test email sent via Brevo API!<br>";
    echo "📧 Check your email: danielrossevia@gmail.com<br>";
    
} catch (Exception $e) {
    echo "❌ Brevo API Error: " . $e->getMessage() . "<br>";
}

// Method 3: Test PHP mail with new settings
echo "<h3>3. Testing PHP Mail with New Settings</h3>";

$to = 'danielrossevia@gmail.com';
$subject = 'PHP Mail Test (Fixed) - ' . date('H:i:s');
$message = 'This is a test email sent via PHP mail() function with SMTP configuration at ' . date('Y-m-d H:i:s');
$headers = 'From: danielrossevia@gmail.com' . "\r\n" .
           'Reply-To: danielrossevia@gmail.com' . "\r\n" .
           'Content-Type: text/html; charset=UTF-8' . "\r\n";

if (mail($to, $subject, $message, $headers)) {
    echo "✅ PHP mail() function worked with new settings!<br>";
    echo "📧 Check your email: danielrossevia@gmail.com<br>";
} else {
    echo "❌ PHP mail() function still failed<br>";
    echo "This is normal - XAMPP needs additional mail server setup<br>";
    echo "✅ But Brevo API should work fine!<br>";
}

echo "<hr>";
echo "<h3>📧 CHECK YOUR EMAIL NOW!</h3>";
echo "1. Check your INBOX<br>";
echo "2. Check your SPAM/JUNK folder<br>";
echo "3. Look for emails with subject 'JPMC Email Test'<br>";
echo "4. If you see the Brevo test email, the system is working!<br>";

echo "<p><strong>Test completed at:</strong> " . date('Y-m-d H:i:s') . "</p>";
?>

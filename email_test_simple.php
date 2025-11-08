<?php
echo "<h2>🔧 Simple Email Test - No Errors</h2>";
echo "<hr>";

// Test 1: Check if we can load Brevo
echo "<h3>1. Brevo SDK Test</h3>";
try {
    require_once 'vendor/autoload.php';
    echo "✅ Autoloader loaded<br>";
    
    if (class_exists('Brevo\Client\Api\TransactionalEmailsApi')) {
        echo "✅ Brevo SDK classes available<br>";
    } else {
        echo "❌ Brevo SDK classes NOT found<br>";
    }
} catch (Exception $e) {
    echo "❌ Error loading Brevo: " . $e->getMessage() . "<br>";
}

// Test 2: Test Brevo API
echo "<h3>2. Brevo API Test</h3>";
try {
    $brevo_api_key = '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';
    
    $config = \Brevo\Client\Configuration::getDefaultConfiguration()->setApiKey('api-key', $brevo_api_key);
    $apiInstance = new \Brevo\Client\Api\TransactionalEmailsApi(new \GuzzleHttp\Client(), $config);
    
    echo "✅ Brevo API configured successfully<br>";
    
    // Send test email
    $test_email = new \Brevo\Client\Model\SendSmtpEmail([
        'subject' => 'JPMC Test Email - ' . date('H:i:s'),
        'sender' => ['name' => 'JPMC Test', 'email' => 'danielrossevia@gmail.com'],
        'to' => [['email' => 'danielrossevia@gmail.com', 'name' => 'Daniel Rossevia']],
        'htmlContent' => '<h2>Test Email</h2><p>This is a test email sent at ' . date('Y-m-d H:i:s') . '</p>'
    ]);
    
    $result = $apiInstance->sendTransacEmail($test_email);
    echo "✅ Test email sent via Brevo API!<br>";
    echo "📧 Check your email: danielrossevia@gmail.com<br>";
    
} catch (Exception $e) {
    echo "❌ Brevo API Error: " . $e->getMessage() . "<br>";
}

// Test 3: Basic PHP mail
echo "<h3>3. PHP Mail Test</h3>";
try {
    $to = 'danielrossevia@gmail.com';
    $subject = 'PHP Mail Test - ' . date('H:i:s');
    $message = 'This is a test email sent via PHP mail() function at ' . date('Y-m-d H:i:s');
    $headers = 'From: danielrossevia@gmail.com' . "\r\n";
    
    if (mail($to, $subject, $message, $headers)) {
        echo "✅ PHP mail() function worked!<br>";
        echo "📧 Check your email: danielrossevia@gmail.com<br>";
    } else {
        echo "❌ PHP mail() function failed<br>";
    }
} catch (Exception $e) {
    echo "❌ PHP mail error: " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<h3>📧 IMPORTANT: Check Your Email!</h3>";
echo "1. Check your INBOX<br>";
echo "2. Check your SPAM/JUNK folder<br>";
echo "3. Check ALL MAIL folder<br>";
echo "4. Search for 'JPMC' or 'Test' in Gmail<br>";

echo "<p><strong>Test completed at:</strong> " . date('Y-m-d H:i:s') . "</p>";
?>

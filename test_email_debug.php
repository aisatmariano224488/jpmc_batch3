<?php
require_once 'vendor/autoload.php';

use Brevo\Client\Api\TransactionalEmailsApi;
use Brevo\Client\Configuration;
use Brevo\Client\Model\SendSmtpEmail;

echo "<h2>Email System Debug Test</h2>";
echo "<hr>";

// Test 1: Check if Brevo SDK is loaded
echo "<h3>1. Brevo SDK Status</h3>";
if (class_exists('Brevo\Client\Api\TransactionalEmailsApi')) {
    echo "✅ Brevo SDK is loaded successfully<br>";
} else {
    echo "❌ Brevo SDK is NOT loaded<br>";
}

// Test 2: Check API Key
echo "<h3>2. API Key Configuration</h3>";
$brevo_api_key = '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';
echo "API Key: " . substr($brevo_api_key, 0, 20) . "...<br>";

// Test 3: Test Brevo API Connection
echo "<h3>3. Testing Brevo API Connection</h3>";
try {
    $config = Configuration::getDefaultConfiguration()->setApiKey('api-key', $brevo_api_key);
    $apiInstance = new TransactionalEmailsApi(new GuzzleHttp\Client(), $config);
    echo "✅ Brevo API configuration successful<br>";
    
    // Test 4: Send a test email
    echo "<h3>4. Sending Test Email</h3>";
    
    $test_email_content = "
    <html>
    <body>
        <h2>Test Email from JPMC System</h2>
        <p>This is a test email to verify that the email system is working correctly.</p>
        <p><strong>Timestamp:</strong> " . date('Y-m-d H:i:s') . "</p>
        <p><strong>Test Type:</strong> System Debug Test</p>
        <p>If you receive this email, the system is working properly!</p>
    </body>
    </html>
    ";
    
    $sendSmtpEmail = new SendSmtpEmail([
        'subject' => 'JPMC Email System Test - ' . date('Y-m-d H:i:s'),
        'sender' => ['name' => 'JPMC Test', 'email' => 'danielrossevia@gmail.com'],
        'to' => [['email' => 'danielrossevia@gmail.com', 'name' => 'Daniel Rossevia']],
        'htmlContent' => $test_email_content
    ]);
    
    $result = $apiInstance->sendTransacEmail($sendSmtpEmail);
    echo "✅ Test email sent successfully!<br>";
    echo "Result: " . json_encode($result) . "<br>";
    
} catch (Exception $e) {
    echo "❌ Error sending test email: " . $e->getMessage() . "<br>";
    echo "Error details: " . $e->getTraceAsString() . "<br>";
}

// Test 5: Check PHP mail function
echo "<h3>5. PHP Mail Function Test</h3>";
if (function_exists('mail')) {
    echo "✅ PHP mail() function is available<br>";
    
    // Test basic mail function
    $to = 'danielrossevia@gmail.com';
    $subject = 'PHP Mail Function Test';
    $message = 'This is a test using PHP mail() function.';
    $headers = 'From: danielrossevia@gmail.com' . "\r\n" .
               'Reply-To: danielrossevia@gmail.com' . "\r\n" .
               'X-Mailer: PHP/' . phpversion();
    
    $mail_result = mail($to, $subject, $message, $headers);
    if ($mail_result) {
        echo "✅ PHP mail() function test sent successfully<br>";
    } else {
        echo "❌ PHP mail() function test failed<br>";
        echo "Error: " . error_get_last()['message'] . "<br>";
    }
} else {
    echo "❌ PHP mail() function is NOT available<br>";
}

// Test 6: Check server configuration
echo "<h3>6. Server Configuration</h3>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Server: " . $_SERVER['SERVER_SOFTWARE'] . "<br>";
echo "Operating System: " . php_uname() . "<br>";

// Test 7: Check if emails might be in spam
echo "<h3>7. Troubleshooting Tips</h3>";
echo "🔍 <strong>Please check the following:</strong><br>";
echo "1. Check your SPAM/JUNK folder in danielrossevia@gmail.com<br>";
echo "2. Check if Gmail is blocking emails from jamespolymersmanufacturingcorp@gmail.com<br>";
echo "3. Verify that the Brevo API key is valid and has sufficient credits<br>";
echo "4. Check if your server can make outbound HTTPS requests to api.brevo.com<br>";

echo "<hr>";
echo "<p><strong>Test completed at:</strong> " . date('Y-m-d H:i:s') . "</p>";
?>

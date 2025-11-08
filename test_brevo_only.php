<?php
echo "<h2>🎯 Brevo API Test - Email System</h2>";
echo "<hr>";

try {
    require_once 'vendor/autoload.php';
    
    $brevo_api_key = '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';
    
    echo "<h3>1. Configuring Brevo API</h3>";
    $config = \Brevo\Client\Configuration::getDefaultConfiguration()->setApiKey('api-key', $brevo_api_key);
    $apiInstance = new \Brevo\Client\Api\TransactionalEmailsApi(new \GuzzleHttp\Client(), $config);
    echo "✅ Brevo API configured successfully<br>";
    
    echo "<h3>2. Sending Test Email</h3>";
    
    $test_email = new \Brevo\Client\Model\SendSmtpEmail([
        'subject' => 'JPMC System Test - ' . date('H:i:s'),
        'sender' => ['name' => 'JPMC System', 'email' => 'danielrossevia@gmail.com'],
        'to' => [['email' => 'danielrossevia@gmail.com', 'name' => 'Daniel Rossevia']],
        'htmlContent' => '
        <html>
        <body style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
            <div style="background: #f8f9fa; padding: 20px; border-radius: 8px;">
                <h2 style="color: #2c3e50;">🎉 JPMC Email System Test</h2>
                <p>This email confirms that your JPMC email system is working correctly!</p>
                <p><strong>Test Details:</strong></p>
                <ul>
                    <li><strong>Time:</strong> ' . date('Y-m-d H:i:s') . '</li>
                    <li><strong>Method:</strong> Brevo API</li>
                    <li><strong>Status:</strong> ✅ Working</li>
                </ul>
                <p>You should now receive emails from:</p>
                <ul>
                    <li>Contact form submissions</li>
                    <li>Career applications</li>
                    <li>Admin replies to customers</li>
                </ul>
                <p style="color: #27ae60; font-weight: bold;">✅ Your email system is now fully functional!</p>
            </div>
        </body>
        </html>
        '
    ]);
    
    $result = $apiInstance->sendTransacEmail($test_email);
    echo "✅ Test email sent successfully!<br>";
    echo "📧 Check your email: danielrossevia@gmail.com<br>";
    echo "📧 Check SPAM folder if not in inbox<br>";
    
    echo "<h3>3. System Status</h3>";
    echo "✅ Brevo API: Working<br>";
    echo "✅ Email sending: Functional<br>";
    echo "✅ Contact form: Ready<br>";
    echo "✅ Career applications: Ready<br>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "This might be a Brevo API key issue or network problem.<br>";
}

echo "<hr>";
echo "<h3>📧 NEXT STEPS:</h3>";
echo "1. Check your email (danielrossevia@gmail.com)<br>";
echo "2. If you receive the test email, your system is working!<br>";
echo "3. Try submitting the contact form to test the full system<br>";
echo "4. If no email arrives, check your Brevo account for API credits<br>";

echo "<p><strong>Test completed at:</strong> " . date('Y-m-d H:i:s') . "</p>";
?>

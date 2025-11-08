<?php
echo "<h2>Simple Mail Test</h2>";

// Test 1: Basic PHP mail function
$to = 'danielrossevia@gmail.com';
$subject = 'Simple Test from JPMC - ' . date('H:i:s');
$message = 'This is a simple test email sent at ' . date('Y-m-d H:i:s');
$headers = 'From: danielrossevia@gmail.com' . "\r\n" .
           'Reply-To: danielrossevia@gmail.com' . "\r\n" .
           'X-Mailer: PHP/' . phpversion();

echo "<h3>Testing PHP mail() function...</h3>";

if (mail($to, $subject, $message, $headers)) {
    echo "✅ PHP mail() function executed successfully!<br>";
    echo "📧 Check your email: danielrossevia@gmail.com<br>";
    echo "📧 Check SPAM folder if you don't see it in inbox<br>";
} else {
    echo "❌ PHP mail() function failed<br>";
    echo "This might be a server configuration issue<br>";
}

echo "<hr>";
echo "<p><strong>Test completed at:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>Note:</strong> If this works, the issue is with Brevo API. If this doesn't work, the issue is with your server's mail configuration.</p>";
?>

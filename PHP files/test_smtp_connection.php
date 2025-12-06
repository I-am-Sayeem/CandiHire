<?php
// test_smtp_connection.php - Test SMTP connection with detailed debugging

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>SMTP Connection Test</h2>";

// SMTP Configuration
$smtp_config = [
    'host' => 'smtp.gmail.com',
    'port' => 587,
    'username' => 'candihiree@gmail.com',
    'password' => 'qelu dbdp xbdc hyqy',
    'encryption' => 'tls'
];

echo "<h3>1. Basic Connection Test</h3>";

// Test basic socket connection
$smtp = @fsockopen($smtp_config['host'], $smtp_config['port'], $errno, $errstr, 10);

if (!$smtp) {
    echo "❌ <strong>Connection Failed:</strong> $errstr ($errno)<br>";
    echo "This usually means:<br>";
    echo "- Your server/firewall blocks port 587<br>";
    echo "- You're on localhost and can't reach external SMTP servers<br>";
    echo "- Network connectivity issues<br><br>";
    
    echo "<h3>Alternative Solutions:</h3>";
    echo "1. <strong>Use local handler:</strong> Switch back to file-based emails for development<br>";
    echo "2. <strong>Use PHPMailer:</strong> More reliable SMTP library<br>";
    echo "3. <strong>Use external service:</strong> SendGrid, Mailgun, etc.<br>";
    echo "4. <strong>Test on live server:</strong> Localhost often blocks SMTP<br><br>";
    
    echo "<h3>Quick Fix - Switch to Local Mode:</h3>";
    echo "Change this line in Interview.php:<br>";
    echo "<code>fetch('interview_email_handler_smtp.php', {</code><br>";
    echo "To:<br>";
    echo "<code>fetch('interview_email_handler_local.php', {</code><br>";
    
    exit;
}

echo "✅ <strong>Connection Successful!</strong> Connected to {$smtp_config['host']}:{$smtp_config['port']}<br>";

// Read initial response
$response = fgets($smtp, 515);
echo "Server response: " . htmlspecialchars($response) . "<br>";

if (substr($response, 0, 3) != '220') {
    echo "❌ <strong>Unexpected server response:</strong> " . htmlspecialchars($response) . "<br>";
    fclose($smtp);
    exit;
}

echo "<h3>2. EHLO Test</h3>";
fputs($smtp, "EHLO " . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "\r\n");
$response = fgets($smtp, 515);
echo "EHLO response: " . htmlspecialchars($response) . "<br>";

echo "<h3>3. STARTTLS Test</h3>";
fputs($smtp, "STARTTLS\r\n");
$response = fgets($smtp, 515);
echo "STARTTLS response: " . htmlspecialchars($response) . "<br>";

if (substr($response, 0, 3) != '220') {
    echo "❌ <strong>TLS Failed:</strong> " . htmlspecialchars($response) . "<br>";
    fclose($smtp);
    exit;
}

echo "✅ <strong>TLS Started Successfully!</strong><br>";

// Enable TLS
$tls_result = stream_socket_enable_crypto($smtp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
if (!$tls_result) {
    echo "❌ <strong>TLS Encryption Failed</strong><br>";
    fclose($smtp);
    exit;
}

echo "✅ <strong>TLS Encryption Enabled!</strong><br>";

// Send EHLO again after TLS
fputs($smtp, "EHLO " . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "\r\n");
$response = fgets($smtp, 515);
echo "Post-TLS EHLO response: " . htmlspecialchars($response) . "<br>";

echo "<h3>4. Authentication Test</h3>";
fputs($smtp, "AUTH LOGIN\r\n");
$response = fgets($smtp, 515);
echo "AUTH LOGIN response: " . htmlspecialchars($response) . "<br>";

if (substr($response, 0, 3) != '334') {
    echo "❌ <strong>AUTH LOGIN Failed:</strong> " . htmlspecialchars($response) . "<br>";
    fclose($smtp);
    exit;
}

// Send username
fputs($smtp, base64_encode($smtp_config['username']) . "\r\n");
$response = fgets($smtp, 515);
echo "Username response: " . htmlspecialchars($response) . "<br>";

if (substr($response, 0, 3) != '334') {
    echo "❌ <strong>Username Rejected:</strong> " . htmlspecialchars($response) . "<br>";
    fclose($smtp);
    exit;
}

// Send password
fputs($smtp, base64_encode($smtp_config['password']) . "\r\n");
$response = fgets($smtp, 515);
echo "Password response: " . htmlspecialchars($response) . "<br>";

if (substr($response, 0, 3) != '235') {
    echo "❌ <strong>Authentication Failed:</strong> " . htmlspecialchars($response) . "<br>";
    echo "This usually means:<br>";
    echo "- App password is incorrect<br>";
    echo "- 2FA is not enabled on Gmail<br>";
    echo "- App password was not generated properly<br>";
    fclose($smtp);
    exit;
}

echo "✅ <strong>Authentication Successful!</strong><br>";

// Close connection
fputs($smtp, "QUIT\r\n");
fclose($smtp);

echo "<h3>✅ All Tests Passed!</h3>";
echo "Your SMTP configuration is working correctly.<br>";
echo "The issue might be in the email sending logic.<br><br>";

echo "<h3>Next Steps:</h3>";
echo "1. Check the browser console for detailed error messages<br>";
echo "2. Check server error logs<br>";
echo "3. Try sending a test email<br>";
?>

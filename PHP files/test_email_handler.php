<?php
// test_email_handler.php - Test the email handler functionality

echo "<h2>Testing Email Handler</h2>";

// Test 1: Check if file exists
echo "<h3>1. File Check</h3>";
if (file_exists('interview_email_handler.php')) {
    echo "✅ interview_email_handler.php exists<br>";
} else {
    echo "❌ interview_email_handler.php not found<br>";
}

// Test 2: Check PHP configuration
echo "<h3>2. PHP Configuration</h3>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Mail function available: " . (function_exists('mail') ? '✅ Yes' : '❌ No') . "<br>";
echo "JSON functions available: " . (function_exists('json_encode') ? '✅ Yes' : '❌ No') . "<br>";

// Test 3: Test JSON input handling
echo "<h3>3. JSON Input Test</h3>";
$testData = [
    'candidates' => [
        ['name' => 'Test User', 'email' => 'test@example.com']
    ],
    'positionName' => 'Test Position',
    'companyName' => 'Test Company',
    'interviewDate' => '2024-12-20',
    'interviewTime' => '14:00',
    'meetingLink' => 'https://zoom.us/j/123456789'
];

echo "Test data prepared:<br>";
echo "<pre>" . json_encode($testData, JSON_PRETTY_PRINT) . "</pre>";

// Test 4: Check server configuration
echo "<h3>4. Server Configuration</h3>";
echo "Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "<br>";
echo "Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Unknown') . "<br>";
echo "Script Name: " . ($_SERVER['SCRIPT_NAME'] ?? 'Unknown') . "<br>";

// Test 5: Test email function directly
echo "<h3>5. Email Function Test</h3>";
$testEmail = 'test@example.com';
$testSubject = 'Test Email from CandiHire';
$testMessage = 'This is a test email to verify the mail function works.';
$testHeaders = [
    'From: CandiHire Team <candihiree@gmail.com>',
    'Content-Type: text/plain; charset=UTF-8',
    'MIME-Version: 1.0',
    'X-Mailer: PHP/' . phpversion()
];

echo "Attempting to send test email...<br>";
$mailResult = @mail($testEmail, $testSubject, $testMessage, implode("\r\n", $testHeaders));
echo "Mail function result: " . ($mailResult ? '✅ Success' : '❌ Failed') . "<br>";

// Test 6: Check file permissions
echo "<h3>6. File Permissions</h3>";
$files = ['interview_email_handler.php', 'test_email_handler.php'];
foreach ($files as $file) {
    if (file_exists($file)) {
        $perms = fileperms($file);
        echo "$file permissions: " . substr(sprintf('%o', $perms), -4) . "<br>";
    }
}

echo "<h3>7. Error Log Check</h3>";
echo "Last error: " . (error_get_last()['message'] ?? 'No errors') . "<br>";

echo "<hr>";
echo "<h3>Next Steps:</h3>";
echo "1. If mail function returns 'Failed', check your server's mail configuration<br>";
echo "2. Make sure your server can send emails (SMTP settings)<br>";
echo "3. Check if you're running on localhost (mail might not work on localhost)<br>";
echo "4. Try testing on a live server instead of localhost<br>";
?>

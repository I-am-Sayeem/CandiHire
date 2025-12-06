<?php
// test_recording_system.php - Test the exam recording system
require_once 'Database.php';
require_once 'exam_recording_handler.php';

echo "<h1>Exam Recording System Test</h1>";

try {
    // Test database connection
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        throw new Exception('Database connection not available');
    }
    
    echo "<p style='color: green;'>✓ Database connection successful</p>";
    
    // Test creating exam recordings table
    $createTableSQL = file_get_contents('create_exam_recordings_table.sql');
    $pdo->exec($createTableSQL);
    echo "<p style='color: green;'>✓ Exam recordings table created successfully</p>";
    
    // Test recording handler
    $handler = new ExamRecordingHandler();
    echo "<p style='color: green;'>✓ Recording handler initialized successfully</p>";
    
    // Test with sample data
    $testAttemptId = 1;
    $testCandidateId = 1;
    $testExamId = 1;
    
    // Test starting recording
    $result = $handler->startRecording($testAttemptId, $testCandidateId, $testExamId);
    if ($result['success']) {
        echo "<p style='color: green;'>✓ Recording session started successfully</p>";
        echo "<p>Screen Recording ID: " . $result['screen_recording_id'] . "</p>";
        echo "<p>Webcam Recording ID: " . $result['webcam_recording_id'] . "</p>";
        
        // Test stopping recording
        $stopResult = $handler->stopRecording($result['screen_recording_id']);
        if ($stopResult['success']) {
            echo "<p style='color: green;'>✓ Screen recording stopped successfully</p>";
        } else {
            echo "<p style='color: red;'>✗ Failed to stop screen recording: " . $stopResult['error'] . "</p>";
        }
        
        $stopResult2 = $handler->stopRecording($result['webcam_recording_id']);
        if ($stopResult2['success']) {
            echo "<p style='color: green;'>✓ Webcam recording stopped successfully</p>";
        } else {
            echo "<p style='color: red;'>✗ Failed to stop webcam recording: " . $stopResult2['error'] . "</p>";
        }
        
    } else {
        echo "<p style='color: red;'>✗ Failed to start recording: " . $result['error'] . "</p>";
    }
    
    // Test getting recordings
    $recordingsResult = $handler->getAttemptRecordings($testAttemptId);
    if ($recordingsResult['success']) {
        echo "<p style='color: green;'>✓ Retrieved recordings successfully</p>";
        echo "<p>Number of recordings: " . count($recordingsResult['recordings']) . "</p>";
    } else {
        echo "<p style='color: red;'>✗ Failed to get recordings: " . $recordingsResult['error'] . "</p>";
    }
    
    echo "<h2>Test Complete</h2>";
    echo "<p>The recording system has been tested. Check the database for the exam_recordings table and test data.</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
    echo "<p>Stack trace: " . $e->getTraceAsString() . "</p>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Recording System Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1, h2 { color: #333; }
        p { margin: 10px 0; }
    </style>
</head>
<body>
    <h2>JavaScript Recording Test</h2>
    <p>To test the JavaScript recording functionality:</p>
    <ol>
        <li>Open the browser's developer console (F12)</li>
        <li>Navigate to the take_exam.php page</li>
        <li>Check for any JavaScript errors</li>
        <li>Verify that the recording system initializes properly</li>
    </ol>
    
    <h2>Browser Compatibility</h2>
    <p>The recording system requires:</p>
    <ul>
        <li>Modern browser with MediaRecorder API support</li>
        <li>HTTPS connection (required for screen capture)</li>
        <li>User permission for camera and screen recording</li>
    </ul>
    
    <h2>Security Considerations</h2>
    <p>Important security notes:</p>
    <ul>
        <li>Recordings are stored in the database as BLOB data</li>
        <li>Consider implementing access controls for viewing recordings</li>
        <li>Recordings should be encrypted for sensitive exams</li>
        <li>Implement data retention policies</li>
    </ul>
</body>
</html>

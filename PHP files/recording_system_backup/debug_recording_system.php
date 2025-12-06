<?php
// debug_recording_system.php - Debug the recording system
require_once 'Database.php';
require_once 'exam_recording_handler.php';

echo "<h1>Recording System Debug</h1>";

try {
    // Check database connection
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        throw new Exception('Database connection not available');
    }
    
    echo "<p style='color: green;'>✓ Database connection successful</p>";
    
    // Check if exam_recordings table exists
    $checkTable = $pdo->query("SHOW TABLES LIKE 'exam_recordings'");
    if ($checkTable->rowCount() > 0) {
        echo "<p style='color: green;'>✓ exam_recordings table exists</p>";
        
        // Check table structure
        $describe = $pdo->query("DESCRIBE exam_recordings");
        $columns = $describe->fetchAll(PDO::FETCH_ASSOC);
        echo "<p style='color: green;'>✓ Table has " . count($columns) . " columns</p>";
    } else {
        echo "<p style='color: red;'>✗ exam_recordings table does not exist</p>";
    }
    
    // Test recording handler
    try {
        $handler = new ExamRecordingHandler();
        echo "<p style='color: green;'>✓ Recording handler initialized successfully</p>";
        
        // Test with sample data
        $testAttemptId = 1;
        $testCandidateId = 1;
        $testExamId = 1;
        
        // Check if we have any exam attempts
        $attemptCheck = $pdo->query("SELECT COUNT(*) as count FROM exam_attempts");
        $attemptCount = $attemptCheck->fetch(PDO::FETCH_ASSOC)['count'];
        echo "<p>Total exam attempts in database: " . $attemptCount . "</p>";
        
        if ($attemptCount > 0) {
            // Get a real attempt ID
            $realAttempt = $pdo->query("SELECT AttemptID, CandidateID, ExamID FROM exam_attempts LIMIT 1");
            $realAttemptData = $realAttempt->fetch(PDO::FETCH_ASSOC);
            
            if ($realAttemptData) {
                $testAttemptId = $realAttemptData['AttemptID'];
                $testCandidateId = $realAttemptData['CandidateID'];
                $testExamId = $realAttemptData['ExamID'];
                echo "<p>Using real attempt data: AttemptID={$testAttemptId}, CandidateID={$testCandidateId}, ExamID={$testExamId}</p>";
            }
        }
        
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
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Recording handler error: " . $e->getMessage() . "</p>";
    }
    
    // Check if we're on HTTPS
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        echo "<p style='color: green;'>✓ HTTPS is enabled</p>";
    } else {
        echo "<p style='color: red;'>✗ HTTPS is not enabled (required for screen recording)</p>";
    }
    
    // Check browser compatibility
    echo "<h2>Browser Compatibility Check</h2>";
    echo "<p>Open browser console and run this JavaScript to check compatibility:</p>";
    echo "<pre><code>";
    echo "// Check for MediaRecorder support\n";
    echo "if (typeof MediaRecorder !== 'undefined') {\n";
    echo "    console.log('✓ MediaRecorder API supported');\n";
    echo "} else {\n";
    echo "    console.log('✗ MediaRecorder API not supported');\n";
    echo "}\n\n";
    echo "// Check for getUserMedia support\n";
    echo "if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {\n";
    echo "    console.log('✓ getUserMedia API supported');\n";
    echo "} else {\n";
    echo "    console.log('✗ getUserMedia API not supported');\n";
    echo "}\n\n";
    echo "// Check for getDisplayMedia support\n";
    echo "if (navigator.mediaDevices && navigator.mediaDevices.getDisplayMedia) {\n";
    echo "    console.log('✓ getDisplayMedia API supported');\n";
    echo "} else {\n";
    echo "    console.log('✗ getDisplayMedia API not supported');\n";
    echo "}\n";
    echo "</code></pre>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
    echo "<p>Stack trace: " . $e->getTraceAsString() . "</p>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Recording System Debug</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1, h2 { color: #333; }
        p { margin: 10px 0; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 4px; overflow-x: auto; }
        code { font-family: monospace; }
    </style>
</head>
<body>
    <h2>Common Issues and Solutions</h2>
    
    <h3>1. "Failed to start recording" Error</h3>
    <p>This error can occur due to several reasons:</p>
    <ul>
        <li><strong>Missing attempt ID:</strong> The exam attempt must be created before recording starts</li>
        <li><strong>Browser permissions:</strong> User must grant camera and screen recording permissions</li>
        <li><strong>HTTPS required:</strong> Screen recording requires HTTPS connection</li>
        <li><strong>Browser compatibility:</strong> Some browsers don't support MediaRecorder API</li>
    </ul>
    
    <h3>2. Debug Steps</h3>
    <ol>
        <li>Open browser developer console (F12)</li>
        <li>Navigate to the exam page</li>
        <li>Check for JavaScript errors in the console</li>
        <li>Verify that the recording system is initializing</li>
        <li>Check if attempt ID is being passed correctly</li>
    </ol>
    
    <h3>3. Quick Fix</h3>
    <p>If you're still having issues, try this simplified version:</p>
    <pre><code>
// Add this to the exam page to debug
console.log('Attempt ID:', document.querySelector('[data-attempt-id]')?.dataset.attemptId);
console.log('Candidate ID:', document.querySelector('[data-candidate-id]')?.dataset.candidateId);
console.log('Exam ID:', document.querySelector('[data-exam-id]')?.dataset.examId);
    </code></pre>
</body>
</html>

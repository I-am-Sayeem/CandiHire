<?php
// test_recording_page.php - Simple test page for recording system
require_once 'Database.php';
require_once 'session_manager.php';

// Create a test attempt ID
$testAttemptId = 1;
$testCandidateId = 1;
$testExamId = 1;

// Check if we have real data
try {
    if (isset($pdo) && $pdo instanceof PDO) {
        $attemptCheck = $pdo->query("SELECT AttemptID, CandidateID, ExamID FROM exam_attempts LIMIT 1");
        $realAttempt = $attemptCheck->fetch(PDO::FETCH_ASSOC);
        
        if ($realAttempt) {
            $testAttemptId = $realAttempt['AttemptID'];
            $testCandidateId = $realAttempt['CandidateID'];
            $testExamId = $realAttempt['ExamID'];
        }
    }
} catch (Exception $e) {
    // Use default values
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recording System Test</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg-primary: #0d1117;
            --bg-secondary: #161b22;
            --bg-tertiary: #21262d;
            --text-primary: #c9d1d9;
            --text-secondary: #8b949e;
            --accent: #58a6ff;
            --accent-hover: #79c0ff;
            --border: #30363d;
            --success: #3fb950;
            --danger: #f85149;
            --warning: #d29922;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Noto Sans', Helvetica, Arial, sans-serif;
        }

        body {
            background: var(--bg-primary);
            color: var(--text-primary);
            line-height: 1.6;
            min-height: 100vh;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: var(--bg-secondary);
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 30px;
            border: 1px solid var(--border);
            text-align: center;
        }

        .header h1 {
            color: var(--accent);
            margin-bottom: 10px;
        }

        .test-section {
            background: var(--bg-secondary);
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 20px;
            border: 1px solid var(--border);
        }

        .test-section h2 {
            color: var(--text-primary);
            margin-bottom: 20px;
        }

        .status-indicator {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            margin: 10px 0;
        }

        .status-indicator.recording {
            background: rgba(248, 81, 73, 0.2);
            color: var(--danger);
        }

        .status-indicator.stopped {
            background: rgba(139, 148, 158, 0.2);
            color: var(--text-secondary);
        }

        .test-button {
            background: var(--accent);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            margin: 10px 5px;
            transition: all 0.3s;
        }

        .test-button:hover {
            background: var(--accent-hover);
        }

        .test-button:disabled {
            background: var(--text-secondary);
            cursor: not-allowed;
        }

        .error-message, .warning-message {
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            display: none;
        }

        .error-message {
            background: var(--danger);
            color: white;
        }

        .warning-message {
            background: var(--warning);
            color: white;
        }

        .info-box {
            background: var(--bg-tertiary);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
        }

        .info-box h3 {
            color: var(--accent);
            margin-bottom: 10px;
        }

        .info-box p {
            color: var(--text-secondary);
            margin: 5px 0;
        }

        .video-preview {
            width: 100%;
            max-width: 400px;
            height: 300px;
            background: var(--bg-tertiary);
            border: 1px solid var(--border);
            border-radius: 8px;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-video"></i> Recording System Test</h1>
            <p>Test the exam recording functionality</p>
        </div>

        <div class="test-section">
            <h2>Recording Status</h2>
            <div class="status-indicator stopped" id="recording-status">
                <i class="fas fa-video-slash"></i> Not Recording
            </div>
            
            <div class="error-message" id="recording-error" style="display: none;"></div>
            <div class="warning-message" id="recording-warning" style="display: none;"></div>
        </div>

        <div class="test-section">
            <h2>Test Controls</h2>
            <button class="test-button" id="start-recording">
                <i class="fas fa-play"></i> Start Recording
            </button>
            <button class="test-button" id="stop-recording" disabled>
                <i class="fas fa-stop"></i> Stop Recording
            </button>
            <button class="test-button" id="test-permissions">
                <i class="fas fa-camera"></i> Test Permissions
            </button>
        </div>

        <div class="test-section">
            <h2>Video Preview</h2>
            <video id="webcam-preview" class="video-preview" autoplay muted></video>
            <p style="color: var(--text-secondary); font-size: 0.9rem;">
                Webcam preview will appear here when recording starts
            </p>
        </div>

        <div class="info-box">
            <h3>Test Information</h3>
            <p><strong>Attempt ID:</strong> <?php echo $testAttemptId; ?></p>
            <p><strong>Candidate ID:</strong> <?php echo $testCandidateId; ?></p>
            <p><strong>Exam ID:</strong> <?php echo $testExamId; ?></p>
            <p><strong>Protocol:</strong> <?php echo $_SERVER['HTTPS'] ? 'HTTPS' : 'HTTP'; ?></p>
            <p><strong>Hostname:</strong> <?php echo $_SERVER['HTTP_HOST']; ?></p>
        </div>

        <div class="info-box">
            <h3>Browser Compatibility</h3>
            <p id="compatibility-info">Checking browser compatibility...</p>
        </div>
    </div>

    <script src="exam_recording.js"></script>
    <script>
        // Test page specific functionality
        let recordingSystem = null;
        let webcamStream = null;

        // Check browser compatibility
        function checkCompatibility() {
            const info = document.getElementById('compatibility-info');
            let compatibility = [];

            if (typeof MediaRecorder !== 'undefined') {
                compatibility.push('✓ MediaRecorder API');
            } else {
                compatibility.push('✗ MediaRecorder API');
            }

            if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                compatibility.push('✓ getUserMedia API');
            } else {
                compatibility.push('✗ getUserMedia API');
            }

            if (navigator.mediaDevices && navigator.mediaDevices.getDisplayMedia) {
                compatibility.push('✓ getDisplayMedia API');
            } else {
                compatibility.push('✗ getDisplayMedia API');
            }

            info.innerHTML = compatibility.join('<br>');
        }

        // Test permissions
        async function testPermissions() {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({
                    video: true,
                    audio: true
                });
                
                const preview = document.getElementById('webcam-preview');
                preview.srcObject = stream;
                webcamStream = stream;
                
                showMessage('Permissions granted successfully!', 'success');
            } catch (error) {
                showMessage('Permission denied: ' + error.message, 'error');
            }
        }

        // Start recording
        async function startRecording() {
            try {
                if (!recordingSystem) {
                    recordingSystem = new ExamRecordingSystem(
                        <?php echo $testAttemptId; ?>,
                        <?php echo $testCandidateId; ?>,
                        <?php echo $testExamId; ?>
                    );
                }

                document.getElementById('start-recording').disabled = true;
                document.getElementById('stop-recording').disabled = false;
                
                showMessage('Recording started!', 'success');
            } catch (error) {
                showMessage('Failed to start recording: ' + error.message, 'error');
                document.getElementById('start-recording').disabled = false;
            }
        }

        // Stop recording
        async function stopRecording() {
            try {
                if (recordingSystem) {
                    await recordingSystem.stopRecording();
                }

                document.getElementById('start-recording').disabled = false;
                document.getElementById('stop-recording').disabled = true;
                
                showMessage('Recording stopped!', 'success');
            } catch (error) {
                showMessage('Error stopping recording: ' + error.message, 'error');
            }
        }

        // Show message
        function showMessage(message, type) {
            const errorDiv = document.getElementById('recording-error');
            const warningDiv = document.getElementById('recording-warning');
            
            errorDiv.style.display = 'none';
            warningDiv.style.display = 'none';
            
            if (type === 'error') {
                errorDiv.textContent = message;
                errorDiv.style.display = 'block';
            } else if (type === 'warning') {
                warningDiv.textContent = message;
                warningDiv.style.display = 'block';
            }
        }

        // Event listeners
        document.getElementById('start-recording').addEventListener('click', startRecording);
        document.getElementById('stop-recording').addEventListener('click', stopRecording);
        document.getElementById('test-permissions').addEventListener('click', testPermissions);

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            checkCompatibility();
        });

        // Cleanup on page unload
        window.addEventListener('beforeunload', function() {
            if (webcamStream) {
                webcamStream.getTracks().forEach(track => track.stop());
            }
            if (recordingSystem && recordingSystem.isRecording) {
                recordingSystem.stopRecording();
            }
        });
    </script>
</body>
</html>

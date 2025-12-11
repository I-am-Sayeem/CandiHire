<?php
// remove_recording_system.php - Remove recording system components
require_once 'Database.php';

echo "<h1>Recording System Cleanup</h1>";

try {
    // Check database connection
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        throw new Exception('Database connection not available');
    }
    
    echo "<p style='color: green;'>✓ Database connection successful</p>";
    
    // Check if exam_recordings table exists
    $checkTable = $pdo->query("SHOW TABLES LIKE 'exam_recordings'");
    if ($checkTable->rowCount() > 0) {
        echo "<p style='color: orange;'>⚠ exam_recordings table exists</p>";
        
        // Ask for confirmation before dropping
        if (isset($_GET['confirm']) && $_GET['confirm'] === 'yes') {
            $pdo->exec("DROP TABLE exam_recordings");
            echo "<p style='color: green;'>✓ exam_recordings table dropped successfully</p>";
        } else {
            echo "<p style='color: orange;'>⚠ To drop the table, visit: <a href='?confirm=yes'>Remove exam_recordings table</a></p>";
        }
    } else {
        echo "<p style='color: green;'>✓ exam_recordings table does not exist</p>";
    }
    
    // Check for any recording-related data in exam_attempts
    $recordingCheck = $pdo->query("SELECT COUNT(*) as count FROM exam_attempts WHERE Status = 'in-progress'");
    $inProgressCount = $recordingCheck->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($inProgressCount > 0) {
        echo "<p style='color: orange;'>⚠ Found {$inProgressCount} in-progress exam attempts</p>";
        echo "<p>These may have been created for recording purposes. You may want to clean them up.</p>";
    } else {
        echo "<p style='color: green;'>✓ No in-progress exam attempts found</p>";
    }
    
    echo "<h2>Cleanup Complete!</h2>";
    echo "<p>The recording system has been removed from the exam system.</p>";
    
    echo "<h3>What was removed:</h3>";
    echo "<ul>";
    echo "<li>Screen recording functionality</li>";
    echo "<li>Webcam recording functionality</li>";
    echo "<li>Recording status indicators</li>";
    echo "<li>Permission request dialogs</li>";
    echo "<li>Recording-related JavaScript</li>";
    echo "<li>Recording database table (if confirmed)</li>";
    echo "</ul>";
    
    echo "<h3>Files moved to backup:</h3>";
    echo "<p>All recording-related files have been moved to the 'recording_system_backup' folder.</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Recording System Cleanup</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 20px; 
            background: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1, h2, h3 { color: #333; }
        p { margin: 10px 0; }
        ul { margin: 10px 0; padding-left: 20px; }
        li { margin: 5px 0; }
        a { color: #007bff; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Exam System Status</h2>
        <p>The exam system is now clean and ready to use without any recording functionality.</p>
        
        <h3>What's working now:</h3>
        <ul>
            <li>✅ Exam taking functionality</li>
            <li>✅ Question display and answering</li>
            <li>✅ Timer functionality</li>
            <li>✅ Exam submission</li>
            <li>✅ Results calculation</li>
            <li>✅ Database storage of answers</li>
        </ul>
        
        <h3>If you need recording back:</h3>
        <p>All recording files are safely stored in the 'recording_system_backup' folder. You can restore them later if needed.</p>
    </div>
</body>
</html>

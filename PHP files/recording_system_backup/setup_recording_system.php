<?php
// setup_recording_system.php - Setup script for the exam recording system
require_once 'Database.php';

echo "<h1>Exam Recording System Setup</h1>";

try {
    // Check database connection
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        throw new Exception('Database connection not available');
    }
    
    echo "<p style='color: green;'>✓ Database connection successful</p>";
    
    // Create exam recordings table
    $createTableSQL = file_get_contents('create_exam_recordings_table.sql');
    if ($createTableSQL === false) {
        throw new Exception('Could not read create_exam_recordings_table.sql');
    }
    
    $pdo->exec($createTableSQL);
    echo "<p style='color: green;'>✓ Exam recordings table created successfully</p>";
    
    // Check if table was created
    $checkTable = $pdo->query("SHOW TABLES LIKE 'exam_recordings'");
    if ($checkTable->rowCount() > 0) {
        echo "<p style='color: green;'>✓ Table verification successful</p>";
    } else {
        throw new Exception('Table creation verification failed');
    }
    
    // Test basic functionality
    $testQuery = $pdo->query("DESCRIBE exam_recordings");
    $columns = $testQuery->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p style='color: green;'>✓ Table structure verified (" . count($columns) . " columns)</p>";
    
    // Check required functions
    $requiredFiles = [
        'exam_recording_handler.php',
        'exam_recording.js',
        'view_exam_recordings.php',
        'test_recording_system.php'
    ];
    
    $allFilesExist = true;
    foreach ($requiredFiles as $file) {
        if (file_exists($file)) {
            echo "<p style='color: green;'>✓ {$file} exists</p>";
        } else {
            echo "<p style='color: red;'>✗ {$file} missing</p>";
            $allFilesExist = false;
        }
    }
    
    if ($allFilesExist) {
        echo "<p style='color: green;'>✓ All required files present</p>";
    }
    
    // Check permissions
    $uploadDir = 'uploads';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
        echo "<p style='color: green;'>✓ Created uploads directory</p>";
    } else {
        echo "<p style='color: green;'>✓ Uploads directory exists</p>";
    }
    
    if (is_writable($uploadDir)) {
        echo "<p style='color: green;'>✓ Uploads directory is writable</p>";
    } else {
        echo "<p style='color: orange;'>⚠ Uploads directory is not writable</p>";
    }
    
    // Check HTTPS
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        echo "<p style='color: green;'>✓ HTTPS is enabled (required for screen recording)</p>";
    } else {
        echo "<p style='color: red;'>✗ HTTPS is not enabled (required for screen recording)</p>";
    }
    
    // Check PHP version
    $phpVersion = phpversion();
    if (version_compare($phpVersion, '7.4.0', '>=')) {
        echo "<p style='color: green;'>✓ PHP version {$phpVersion} is supported</p>";
    } else {
        echo "<p style='color: orange;'>⚠ PHP version {$phpVersion} may have compatibility issues</p>";
    }
    
    // Check required PHP extensions
    $requiredExtensions = ['pdo', 'pdo_mysql', 'json', 'fileinfo'];
    $missingExtensions = [];
    
    foreach ($requiredExtensions as $ext) {
        if (extension_loaded($ext)) {
            echo "<p style='color: green;'>✓ {$ext} extension loaded</p>";
        } else {
            echo "<p style='color: red;'>✗ {$ext} extension missing</p>";
            $missingExtensions[] = $ext;
        }
    }
    
    if (empty($missingExtensions)) {
        echo "<p style='color: green;'>✓ All required PHP extensions are loaded</p>";
    }
    
    echo "<h2>Setup Complete!</h2>";
    echo "<p>The exam recording system has been successfully set up.</p>";
    
    echo "<h3>Next Steps:</h3>";
    echo "<ol>";
    echo "<li>Ensure your server is configured with HTTPS</li>";
    echo "<li>Test the system by running: <code>test_recording_system.php</code></li>";
    echo "<li>Access the admin panel at: <code>view_exam_recordings.php</code></li>";
    echo "<li>Test the recording functionality during an exam</li>";
    echo "</ol>";
    
    echo "<h3>Important Notes:</h3>";
    echo "<ul>";
    echo "<li>Screen recording requires HTTPS and user permission</li>";
    echo "<li>Recordings are stored in the database as BLOB data</li>";
    echo "<li>Consider implementing data retention policies</li>";
    echo "<li>Monitor database size as recordings can be large</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Setup failed: " . $e->getMessage() . "</p>";
    echo "<p>Please check the error and try again.</p>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Recording System Setup</title>
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
        code { 
            background: #f4f4f4; 
            padding: 2px 6px; 
            border-radius: 3px; 
            font-family: monospace;
        }
        ol, ul { margin: 10px 0; padding-left: 20px; }
        li { margin: 5px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Browser Compatibility Check</h2>
        <p>To test browser compatibility, open the browser console and run:</p>
        <pre><code>
// Check for MediaRecorder support
if (typeof MediaRecorder !== 'undefined') {
    console.log('✓ MediaRecorder API supported');
} else {
    console.log('✗ MediaRecorder API not supported');
}

// Check for getUserMedia support
if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
    console.log('✓ getUserMedia API supported');
} else {
    console.log('✗ getUserMedia API not supported');
}

// Check for getDisplayMedia support
if (navigator.mediaDevices && navigator.mediaDevices.getDisplayMedia) {
    console.log('✓ getDisplayMedia API supported');
} else {
    console.log('✗ getDisplayMedia API not supported');
}
        </code></pre>
        
        <h2>Security Recommendations</h2>
        <ul>
            <li>Implement proper access controls for viewing recordings</li>
            <li>Consider encrypting recording data before storage</li>
            <li>Set up automatic cleanup of old recordings</li>
            <li>Monitor database size and performance</li>
            <li>Ensure compliance with privacy regulations</li>
        </ul>
    </div>
</body>
</html>

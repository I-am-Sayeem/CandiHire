<?php
// setup_messaging_system.php - Setup script for the messaging system
require_once 'database_config.php';

echo "<h2>Setting up Messaging System</h2>";

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, DB_OPTIONS);
    echo "<p style='color: green;'>✓ Database connection successful</p>";
    
    // Read and execute the SQL file
    $sql = file_get_contents('create_messaging_tables.sql');
    
    if ($sql === false) {
        throw new Exception("Could not read create_messaging_tables.sql file");
    }
    
    // Split SQL into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    $successCount = 0;
    $errorCount = 0;
    
    foreach ($statements as $statement) {
        if (empty($statement) || strpos($statement, '--') === 0) {
            continue; // Skip empty statements and comments
        }
        
        try {
            $pdo->exec($statement);
            $successCount++;
            echo "<p style='color: green;'>✓ Executed: " . substr($statement, 0, 50) . "...</p>";
        } catch (PDOException $e) {
            $errorCount++;
            echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
            echo "<p style='color: red;'>Statement: " . substr($statement, 0, 100) . "...</p>";
        }
    }
    
    echo "<h3>Setup Summary</h3>";
    echo "<p style='color: green;'>✓ Successful statements: $successCount</p>";
    echo "<p style='color: red;'>✗ Failed statements: $errorCount</p>";
    
    if ($errorCount === 0) {
        echo "<h3 style='color: green;'>Messaging System Setup Complete!</h3>";
        echo "<p>The messaging system has been successfully set up with the following features:</p>";
        echo "<ul>";
        echo "<li>✓ Messages table for storing all messages</li>";
        echo "<li>✓ Conversations table for tracking conversation threads</li>";
        echo "<li>✓ Message attachments table for future file sharing</li>";
        echo "<li>✓ Message read status table for read receipts</li>";
        echo "<li>✓ Sample data for testing</li>";
        echo "</ul>";
        echo "<p><strong>Next steps:</strong></p>";
        echo "<ul>";
        echo "<li>Test the messaging system by logging in as a candidate and company</li>";
        echo "<li>Try sending messages between users</li>";
        echo "<li>Check that message buttons appear on job posts and candidate profiles</li>";
        echo "</ul>";
    } else {
        echo "<h3 style='color: red;'>Setup completed with errors</h3>";
        echo "<p>Please check the error messages above and fix any issues.</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Setup failed: " . $e->getMessage() . "</p>";
    echo "<p>Please check your database configuration and try again.</p>";
}
?>

<style>
body {
    font-family: Arial, sans-serif;
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
    background: #f5f5f5;
}

h2, h3 {
    color: #333;
}

p {
    margin: 5px 0;
}

ul {
    margin: 10px 0;
    padding-left: 20px;
}

li {
    margin: 5px 0;
}
</style>

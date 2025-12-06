<?php
// Script to fix ScheduleID constraint issue
require_once 'database_config.php';

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, DB_OPTIONS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Fixing ScheduleID constraint issue...\n";
    
    // Check current column definition
    $stmt = $pdo->query("SHOW COLUMNS FROM exam_attempts LIKE 'ScheduleID'");
    $column = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($column) {
        echo "Current ScheduleID column definition: {$column['Type']}, Null: {$column['Null']}\n";
        
        // Make ScheduleID nullable
        $pdo->exec("ALTER TABLE exam_attempts MODIFY COLUMN ScheduleID INT(11) NULL");
        echo "✓ Made ScheduleID column nullable.\n";
        
        // Verify the change
        $stmt = $pdo->query("SHOW COLUMNS FROM exam_attempts LIKE 'ScheduleID'");
        $column = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "Updated ScheduleID column definition: {$column['Type']}, Null: {$column['Null']}\n";
    } else {
        echo "ScheduleID column not found in exam_attempts table.\n";
    }
    
    echo "ScheduleID constraint fix completed successfully!\n";
    
} catch (Exception $e) {
    echo "Error fixing ScheduleID constraint: " . $e->getMessage() . "\n";
    exit(1);
}
?>

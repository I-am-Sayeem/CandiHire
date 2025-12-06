<?php
// setup_cv_processing_tables.php - Setup CV processing database tables
require_once 'Database.php';

header('Content-Type: application/json');

if (!$pdo) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

try {
    // Read the SQL file
    $sqlFile = 'create_cv_processing_table.sql';
    
    if (!file_exists($sqlFile)) {
        throw new Exception('SQL file not found: ' . $sqlFile);
    }
    
    $sql = file_get_contents($sqlFile);
    
    if ($sql === false) {
        throw new Exception('Failed to read SQL file');
    }
    
    // Split SQL into individual statements
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            return !empty($stmt) && !preg_match('/^--/', $stmt);
        }
    );
    
    $executed = 0;
    $errors = [];
    
    foreach ($statements as $statement) {
        try {
            if (!empty($statement)) {
                $pdo->exec($statement);
                $executed++;
            }
        } catch (PDOException $e) {
            // Table might already exist, which is okay
            if (strpos($e->getMessage(), 'already exists') === false) {
                $errors[] = $e->getMessage();
            }
        }
    }
    
    if (empty($errors)) {
        echo json_encode([
            'success' => true,
            'message' => "Successfully executed $executed SQL statements",
            'executed' => $executed
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Some errors occurred during setup',
            'errors' => $errors,
            'executed' => $executed
        ]);
    }
    
} catch (Exception $e) {
    error_log("Setup CV processing tables error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Setup failed: ' . $e->getMessage()
    ]);
}
?>

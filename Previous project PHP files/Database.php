<?php
// Database.php - Main database connection file
// This file should ONLY establish a reusable PDO connection.
// Do not echo/exit here, as it will break any including scripts.

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

// Load database configuration
require_once 'database_config.php';

$pdo = null;
$pdoErrorMessage = null;

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, DB_OPTIONS);
} catch (PDOException $e) {
    // Expose error to including script via $pdoErrorMessage if needed
    $pdo = null;
    $pdoErrorMessage = $e->getMessage();
    
    // Log error for debugging (optional)
    if (function_exists('error_log')) {
        error_log("Database connection failed: " . $e->getMessage());
    }
}

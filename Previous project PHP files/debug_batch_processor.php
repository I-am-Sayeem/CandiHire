<?php
// Debug script for batch CV processor
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== Debug Batch CV Processor ===\n\n";

// Test database connection
require_once 'Database.php';
echo "Database connection: " . ($pdo ? "OK" : "FAILED") . "\n";

// Test session manager
require_once 'session_manager.php';
echo "Session manager loaded: OK\n";

// Test CV processing handler
require_once 'cv_processing_handler.php';
echo "CV processing handler loaded: OK\n";

// Test batch processor
echo "Testing batch processor...\n";

// Simulate a POST request
$_POST['action'] = 'process_batch';
$_POST['jobPosition'] = 'Software Engineer';
$_POST['experienceLevel'] = 'mid';
$_POST['requiredSkills'] = 'JavaScript,React,Node.js';
$_POST['customCriteria'] = 'Web development experience';
$_POST['minMatchPercentage'] = '0';

// Simulate file upload
$_FILES['cvs'] = [
    'name' => ['test_cv.pdf'],
    'type' => ['application/pdf'],
    'tmp_name' => ['CVs/Ava_Khan_CV.pdf'],
    'error' => [UPLOAD_ERR_OK],
    'size' => [2268]
];

// Simulate session
$_SESSION['user_type'] = 'company';
$_SESSION['company_id'] = 1;
$_SESSION['company_name'] = 'Test Company';

echo "Simulated data set up\n";

// Test if functions exist
$functions = [
    'processBatchCVs',
    'processIndividualCV',
    'validateCVFile',
    'getBatchProcessingStatus',
    'getBatchCandidates',
    'applyBatchFilters',
    'exportSelectedCandidates',
    'sendJsonResponse'
];

foreach ($functions as $func) {
    echo "Function $func exists: " . (function_exists($func) ? "YES" : "NO") . "\n";
}

// Test CV processing functions
$cvFunctions = [
    'extractTextFromPDF',
    'parseStructuredCV',
    'calculateMatchPercentage',
    'splitIntoSections',
    'processContactSection',
    'processSummarySection',
    'processExperienceSection',
    'processEducationSection',
    'processSkillsSection'
];

echo "\nCV Processing Functions:\n";
foreach ($cvFunctions as $func) {
    echo "Function $func exists: " . (function_exists($func) ? "YES" : "NO") . "\n";
}

echo "\n=== Debug Complete ===\n";
?>

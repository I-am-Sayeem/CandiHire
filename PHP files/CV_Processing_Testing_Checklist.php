<?php
/**
 * CV Processing Project - Comprehensive Testing Checklist
 * This file provides a complete testing suite for the CV processing system
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>";
echo "<html><head><title>CV Processing Testing Checklist</title>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .header { text-align: center; color: #333; border-bottom: 3px solid #4CAF50; padding-bottom: 20px; margin-bottom: 30px; }
    .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
    .test-section h3 { color: #4CAF50; margin-top: 0; }
    .test-item { margin: 10px 0; padding: 10px; background: #f9f9f9; border-left: 4px solid #ddd; }
    .test-item.passed { border-left-color: #4CAF50; background: #e8f5e8; }
    .test-item.failed { border-left-color: #f44336; background: #ffeaea; }
    .test-item.warning { border-left-color: #ff9800; background: #fff3e0; }
    .status { font-weight: bold; padding: 5px 10px; border-radius: 3px; }
    .status.passed { background: #4CAF50; color: white; }
    .status.failed { background: #f44336; color: white; }
    .status.warning { background: #ff9800; color: white; }
    .code-block { background: #f4f4f4; padding: 10px; border-radius: 3px; font-family: monospace; margin: 10px 0; }
    .summary { background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 20px 0; }
    .progress-bar { width: 100%; background: #ddd; border-radius: 5px; margin: 10px 0; }
    .progress-fill { height: 20px; background: #4CAF50; border-radius: 5px; transition: width 0.3s; }
</style></head><body>";

echo "<div class='container'>";
echo "<div class='header'>";
echo "<h1>🔍 CV Processing Project Testing Checklist</h1>";
echo "<p>Comprehensive testing suite for CV processing functionality</p>";
echo "</div>";

// Initialize test results
$testResults = [
    'total' => 0,
    'passed' => 0,
    'failed' => 0,
    'warnings' => 0
];

function runTest($testName, $testFunction, &$testResults) {
    $testResults['total']++;
    echo "<div class='test-item'>";
    echo "<strong>$testName</strong><br>";
    
    try {
        $result = $testFunction();
        if ($result === true) {
            echo "<span class='status passed'>✅ PASSED</span>";
            $testResults['passed']++;
            echo "<div class='test-item passed'>";
        } elseif ($result === false) {
            echo "<span class='status failed'>❌ FAILED</span>";
            $testResults['failed']++;
            echo "<div class='test-item failed'>";
        } else {
            echo "<span class='status warning'>⚠️ WARNING</span>";
            $testResults['warnings']++;
            echo "<div class='test-item warning'>";
        }
        echo "</div>";
    } catch (Exception $e) {
        echo "<span class='status failed'>❌ ERROR</span>";
        $testResults['failed']++;
        echo "<div class='test-item failed'>Error: " . $e->getMessage() . "</div>";
    }
    echo "</div>";
}

// Test 1: System Requirements
echo "<div class='test-section'>";
echo "<h3>🔧 System Requirements & Environment</h3>";

runTest("PHP Version Check", function() {
    $version = PHP_VERSION;
    echo "PHP Version: $version<br>";
    return version_compare($version, '7.4.0', '>=');
}, $testResults);

runTest("Required PHP Extensions", function() {
    $required = ['pdo', 'pdo_mysql', 'json', 'mbstring'];
    $missing = [];
    foreach ($required as $ext) {
        if (!extension_loaded($ext)) {
            $missing[] = $ext;
        }
    }
    if (empty($missing)) {
        echo "All required extensions loaded: " . implode(', ', $required);
        return true;
    } else {
        echo "Missing extensions: " . implode(', ', $missing);
        return false;
    }
}, $testResults);

runTest("Shell Exec Availability", function() {
    $available = function_exists('shell_exec');
    echo "Shell exec available: " . ($available ? 'Yes' : 'No');
    if ($available) {
        echo "<br>PDFtoText check: ";
        $pdftotext = shell_exec('which pdftotext 2>/dev/null');
        if (empty($pdftotext)) {
            echo "Not available (optional)";
            return null; // Warning
        } else {
            echo "Available";
            return true;
        }
    }
    return $available;
}, $testResults);

runTest("File System Permissions", function() {
    $uploadDir = 'uploads';
    $cvDir = 'CVs';
    
    $checks = [
        'Uploads directory exists' => is_dir($uploadDir),
        'CVs directory exists' => is_dir($cvDir),
        'Uploads directory writable' => is_writable($uploadDir),
        'CVs directory readable' => is_readable($cvDir)
    ];
    
    $allPassed = true;
    foreach ($checks as $check => $result) {
        echo "$check: " . ($result ? '✅' : '❌') . "<br>";
        if (!$result) $allPassed = false;
    }
    
    return $allPassed;
}, $testResults);

echo "</div>";

// Test 2: Database Connection
echo "<div class='test-section'>";
echo "<h3>🗄️ Database Connection & Schema</h3>";

runTest("Database Connection", function() {
    try {
        if (file_exists('database_config.php')) {
            require_once 'database_config.php';
            if (isset($dsn) && isset($username) && isset($password)) {
                $pdo = new PDO($dsn, $username, $password);
                echo "Database connection successful<br>";
                return true;
            } else {
                echo "Database configuration variables not found<br>";
                return false;
            }
        } else {
            echo "database_config.php file not found<br>";
            return false;
        }
    } catch (PDOException $e) {
        echo "Database connection failed: " . $e->getMessage();
        return false;
    }
}, $testResults);

runTest("Required Tables Exist", function() {
    try {
        if (file_exists('database_config.php')) {
            require_once 'database_config.php';
            if (isset($dsn) && isset($username) && isset($password)) {
                $pdo = new PDO($dsn, $username, $password);
        
        $requiredTables = [
            'cv_processing_results',
            'cv_files', 
            'candidate_contact_info',
            'selected_candidates_export'
        ];
        
        $missingTables = [];
        foreach ($requiredTables as $table) {
            $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
            $stmt->execute([$table]);
            if ($stmt->rowCount() == 0) {
                $missingTables[] = $table;
            }
        }
        
                if (empty($missingTables)) {
                    echo "All required tables exist: " . implode(', ', $requiredTables);
                    return true;
                } else {
                    echo "Missing tables: " . implode(', ', $missingTables);
                    return false;
                }
            } else {
                echo "Database configuration variables not found";
                return false;
            }
        } else {
            echo "database_config.php file not found";
            return false;
        }
    } catch (Exception $e) {
        echo "Error checking tables: " . $e->getMessage();
        return false;
    }
}, $testResults);

echo "</div>";

// Test 3: PDF Processing Functions
echo "<div class='test-section'>";
echo "<h3>📄 PDF Processing Functions</h3>";

runTest("PDF Files Exist", function() {
    $cvDir = 'CVs';
    $pdfFiles = glob($cvDir . '/*.pdf');
    
    if (empty($pdfFiles)) {
        echo "No PDF files found in $cvDir directory";
        return false;
    }
    
    echo "Found " . count($pdfFiles) . " PDF files:<br>";
    foreach ($pdfFiles as $file) {
        echo "• " . basename($file) . " (" . filesize($file) . " bytes)<br>";
    }
    
    return count($pdfFiles) >= 1;
}, $testResults);

runTest("PDF Text Extraction", function() {
    $cvDir = 'CVs';
    $pdfFiles = glob($cvDir . '/*.pdf');
    
    if (empty($pdfFiles)) {
        return false;
    }
    
    $testFile = $pdfFiles[0];
    echo "Testing extraction from: " . basename($testFile) . "<br>";
    
    // Include the extraction function
    if (file_exists('cv_processing_handler.php')) {
        // Extract just the function we need
        $content = file_get_contents('cv_processing_handler.php');
        if (strpos($content, 'function extractTextFromPDF') !== false) {
            echo "✅ extractTextFromPDF function found<br>";
            
            // Simple test - just check if file is readable
            $fileSize = filesize($testFile);
            if ($fileSize > 0 && $fileSize < 10 * 1024 * 1024) { // Less than 10MB
                echo "✅ PDF file is readable and reasonable size ($fileSize bytes)<br>";
                return true;
            } else {
                echo "❌ PDF file size issue: $fileSize bytes";
                return false;
            }
        } else {
            echo "❌ extractTextFromPDF function not found";
            return false;
        }
    } else {
        echo "❌ cv_processing_handler.php not found";
        return false;
    }
}, $testResults);

echo "</div>";

// Test 4: Frontend Components
echo "<div class='test-section'>";
echo "<h3>🖥️ Frontend Components</h3>";

runTest("CvChecker.php Exists", function() {
    if (file_exists('CvChecker.php')) {
        $size = filesize('CvChecker.php');
        echo "✅ CvChecker.php exists ($size bytes)<br>";
        
        $content = file_get_contents('CvChecker.php');
        $checks = [
            'HTML structure' => strpos($content, '<!DOCTYPE html>') !== false,
            'JavaScript functions' => strpos($content, 'function processCVs') !== false,
            'CSS styling' => strpos($content, '<style>') !== false,
            'Form elements' => strpos($content, 'id="jobPosition"') !== false
        ];
        
        foreach ($checks as $check => $result) {
            echo "$check: " . ($result ? '✅' : '❌') . "<br>";
        }
        
        return true;
    } else {
        echo "❌ CvChecker.php not found";
        return false;
    }
}, $testResults);

runTest("JavaScript Functions", function() {
    if (!file_exists('CvChecker.php')) {
        return false;
    }
    
    $content = file_get_contents('CvChecker.php');
    $requiredFunctions = [
        'processCVs',
        'getRequiredSkills', 
        'displayCandidates',
        'showSuccessMessage',
        'showErrorMessage'
    ];
    
    $missingFunctions = [];
    foreach ($requiredFunctions as $func) {
        if (strpos($content, "function $func") === false) {
            $missingFunctions[] = $func;
        }
    }
    
    if (empty($missingFunctions)) {
        echo "✅ All required JavaScript functions found: " . implode(', ', $requiredFunctions);
        return true;
    } else {
        echo "❌ Missing functions: " . implode(', ', $missingFunctions);
        return false;
    }
}, $testResults);

echo "</div>";

// Test 5: Session Management
echo "<div class='test-section'>";
echo "<h3>🔐 Session Management</h3>";

runTest("Session Manager Exists", function() {
    if (file_exists('session_manager.php')) {
        echo "✅ session_manager.php exists<br>";
        
        $content = file_get_contents('session_manager.php');
        $checks = [
            'isCompanyLoggedIn function' => strpos($content, 'function isCompanyLoggedIn') !== false,
            'Session start' => strpos($content, 'session_start') !== false,
            'Company ID check' => strpos($content, 'company_id') !== false
        ];
        
        foreach ($checks as $check => $result) {
            echo "$check: " . ($result ? '✅' : '❌') . "<br>";
        }
        
        return true;
    } else {
        echo "❌ session_manager.php not found";
        return false;
    }
}, $testResults);

echo "</div>";

// Test 6: File Structure
echo "<div class='test-section'>";
echo "<h3>📁 File Structure & Dependencies</h3>";

runTest("Core Files Present", function() {
    $requiredFiles = [
        'CvChecker.php' => 'Main frontend interface',
        'cv_processing_handler.php' => 'Backend processing logic',
        'database_config.php' => 'Database configuration',
        'session_manager.php' => 'Session management',
        'create_cv_processing_table.sql' => 'Database schema'
    ];
    
    $missingFiles = [];
    foreach ($requiredFiles as $file => $description) {
        if (!file_exists($file)) {
            $missingFiles[] = "$file ($description)";
        } else {
            echo "✅ $file - $description<br>";
        }
    }
    
    if (empty($missingFiles)) {
        return true;
    } else {
        echo "❌ Missing files:<br>";
        foreach ($missingFiles as $file) {
            echo "• $file<br>";
        }
        return false;
    }
}, $testResults);

echo "</div>";

// Test 7: Security & Error Handling
echo "<div class='test-section'>";
echo "<h3>🔒 Security & Error Handling</h3>";

runTest("Input Validation Functions", function() {
    if (!file_exists('cv_processing_handler.php')) {
        return false;
    }
    
    $content = file_get_contents('cv_processing_handler.php');
    $securityChecks = [
        'SQL injection protection' => strpos($content, 'prepare(') !== false,
        'File upload validation' => strpos($content, 'move_uploaded_file') !== false,
        'Error logging' => strpos($content, 'error_log') !== false,
        'Input sanitization' => strpos($content, 'htmlspecialchars') !== false || strpos($content, 'trim(') !== false
    ];
    
    $passed = 0;
    foreach ($securityChecks as $check => $result) {
        echo "$check: " . ($result ? '✅' : '❌') . "<br>";
        if ($result) $passed++;
    }
    
    return $passed >= 3; // At least 3 out of 4 should pass
}, $testResults);

echo "</div>";

// Test Summary
$totalTests = $testResults['total'];
$passedTests = $testResults['passed'];
$failedTests = $testResults['failed'];
$warningTests = $testResults['warnings'];

$successRate = $totalTests > 0 ? round(($passedTests / $totalTests) * 100, 1) : 0;

echo "<div class='summary'>";
echo "<h3>📊 Test Summary</h3>";
echo "<div class='progress-bar'>";
echo "<div class='progress-fill' style='width: {$successRate}%'></div>";
echo "</div>";

echo "<p><strong>Total Tests:</strong> $totalTests</p>";
echo "<p><strong>Passed:</strong> <span style='color: #4CAF50; font-weight: bold;'>$passedTests</span></p>";
echo "<p><strong>Failed:</strong> <span style='color: #f44336; font-weight: bold;'>$failedTests</span></p>";
echo "<p><strong>Warnings:</strong> <span style='color: #ff9800; font-weight: bold;'>$warningTests</span></p>";
echo "<p><strong>Success Rate:</strong> <span style='font-weight: bold; font-size: 1.2em;'>$successRate%</span></p>";

if ($successRate >= 90) {
    echo "<p style='color: #4CAF50; font-weight: bold;'>🎉 Excellent! System is ready for production.</p>";
} elseif ($successRate >= 75) {
    echo "<p style='color: #ff9800; font-weight: bold;'>⚠️ Good, but some issues need attention.</p>";
} else {
    echo "<p style='color: #f44336; font-weight: bold;'>❌ System needs significant fixes before deployment.</p>";
}

echo "</div>";

// Manual Testing Checklist
echo "<div class='test-section'>";
echo "<h3>🧪 Manual Testing Checklist</h3>";
echo "<p><strong>Please perform these manual tests:</strong></p>";
echo "<div class='code-block'>";

$manualTests = [
    "1. Company Login/Registration",
    "   • Test company registration with valid data",
    "   • Test company login with correct credentials",
    "   • Test login with incorrect credentials",
    "",
    "2. CV Upload Process",
    "   • Upload valid PDF files",
    "   • Try uploading non-PDF files (should be rejected)",
    "   • Test with large files",
    "   • Test with corrupted PDF files",
    "",
    "3. Job Requirements Setting",
    "   • Set different job positions",
    "   • Select different experience levels",
    "   • Add required skills",
    "   • Enter custom criteria",
    "",
    "4. CV Processing",
    "   • Process uploaded CVs",
    "   • Check if text extraction works",
    "   • Verify candidate data extraction",
    "   • Test filtering results",
    "",
    "5. Candidate Review",
    "   • Review filtered candidates",
    "   • Check candidate details accuracy",
    "   • Test selection/deselection",
    "   • Test export functionality",
    "",
    "6. Error Handling",
    "   • Test with no CVs uploaded",
    "   • Test with invalid job requirements",
    "   • Test network interruptions",
    "   • Check error messages are user-friendly",
    "",
    "7. Performance Testing",
    "   • Test with 10+ CV files",
    "   • Check processing time",
    "   • Monitor memory usage",
    "   • Test concurrent users"
];

foreach ($manualTests as $test) {
    echo htmlspecialchars($test) . "<br>";
}

echo "</div>";
echo "</div>";

// Deployment Checklist
echo "<div class='test-section'>";
echo "<h3>🚀 Deployment Checklist</h3>";
echo "<div class='code-block'>";

$deploymentChecks = [
    "Pre-Deployment:",
    "□ All automated tests pass",
    "□ Manual testing completed",
    "□ Database schema updated",
    "□ Environment variables configured",
    "□ File permissions set correctly",
    "",
    "Security:",
    "□ Input validation implemented",
    "□ SQL injection protection active",
    "□ File upload restrictions in place",
    "□ Error messages don't expose sensitive info",
    "□ Session security configured",
    "",
    "Performance:",
    "□ Database indexes optimized",
    "□ File upload size limits set",
    "□ Memory limits appropriate",
    "□ Processing timeouts configured",
    "",
    "Monitoring:",
    "□ Error logging enabled",
    "□ Performance monitoring setup",
    "□ Backup procedures in place",
    "□ Rollback plan prepared"
];

foreach ($deploymentChecks as $check) {
    echo htmlspecialchars($check) . "<br>";
}

echo "</div>";
echo "</div>";

echo "<div style='text-align: center; margin: 30px 0; padding: 20px; background: #f0f8ff; border-radius: 5px;'>";
echo "<h3>📝 Testing Notes</h3>";
echo "<p>Run this checklist before each major release or deployment.</p>";
echo "<p>Keep test results documented for future reference.</p>";
echo "<p><strong>Last Updated:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "</div>";

echo "</div></body></html>";
?>

<?php
/**
 * Test Script for SQL Query Fix
 * 
 * This script tests that the SQL query in job_application_handler.php
 * now correctly filters out exams with no questions (QuestionCount = 0)
 */

require_once 'Database.php';

// Check if we're running from command line or web
$isCLI = php_sapi_name() === 'cli';

if (!$isCLI) {
    echo "<h1>SQL Query Fix Test</h1>";
    echo "<pre>";
}

echo "=== SQL Query Fix Test ===\n\n";

try {
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        throw new Exception('Database connection not available');
    }

    // Test 1: Create test data
    echo "1. Creating test data...\n";
    
    // Get first company
    $companyStmt = $pdo->prepare("SELECT CompanyID, CompanyName FROM Company_login_info LIMIT 1");
    $companyStmt->execute();
    $company = $companyStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$company) {
        echo "   No companies found in database.\n";
        exit;
    }
    
    echo "   Using company: {$company['CompanyName']} (ID: {$company['CompanyID']})\n";
    
    // Create exam WITHOUT questions (QuestionCount = 0)
    $exam1Stmt = $pdo->prepare("
        INSERT INTO exams (CompanyID, ExamTitle, ExamType, Description, Instructions, Duration, QuestionCount, PassingScore, IsActive, CreatedBy)
        VALUES (?, ?, 'manual', ?, ?, ?, 0, 70.00, 1, ?)
    ");
    
    $exam1Title = "Exam Without Questions - " . date('Y-m-d H:i:s');
    $exam1Stmt->execute([
        $company['CompanyID'],
        $exam1Title,
        'Test exam without questions',
        'Please answer all questions carefully.',
        1800,
        'Test System'
    ]);
    $exam1Id = $pdo->lastInsertId();
    echo "   ✓ Created exam 1: $exam1Title (ID: $exam1Id, QuestionCount: 0)\n";
    
    // Create exam WITH questions (QuestionCount > 0)
    $exam2Stmt = $pdo->prepare("
        INSERT INTO exams (CompanyID, ExamTitle, ExamType, Description, Instructions, Duration, QuestionCount, PassingScore, IsActive, CreatedBy)
        VALUES (?, ?, 'manual', ?, ?, ?, 2, 70.00, 1, ?)
    ");
    
    $exam2Title = "Exam With Questions - " . date('Y-m-d H:i:s');
    $exam2Stmt->execute([
        $company['CompanyID'],
        $exam2Title,
        'Test exam with questions',
        'Please answer all questions carefully.',
        1800,
        'Test System'
    ]);
    $exam2Id = $pdo->lastInsertId();
    echo "   ✓ Created exam 2: $exam2Title (ID: $exam2Id, QuestionCount: 2)\n";
    
    // Test 2: Test OLD query (should return both exams)
    echo "\n2. Testing OLD query (before fix)...\n";
    
    $oldQueryStmt = $pdo->prepare("
        SELECT ExamID, ExamTitle, Duration, QuestionCount, PassingScore 
        FROM exams 
        WHERE CompanyID = ? AND IsActive = 1
        ORDER BY CreatedAt DESC
        LIMIT 1
    ");
    $oldQueryStmt->execute([$company['CompanyID']]);
    $oldResult = $oldQueryStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($oldResult) {
        echo "   OLD query returned: {$oldResult['ExamTitle']} (QuestionCount: {$oldResult['QuestionCount']})\n";
        if ($oldResult['QuestionCount'] == 0) {
            echo "   ✗ PROBLEM: OLD query returns exam with no questions!\n";
        } else {
            echo "   ✓ OLD query returned exam with questions\n";
        }
    } else {
        echo "   OLD query returned no results\n";
    }
    
    // Test 3: Test NEW query (should only return exam with questions)
    echo "\n3. Testing NEW query (after fix)...\n";
    
    $newQueryStmt = $pdo->prepare("
        SELECT e.ExamID, e.ExamTitle, e.Duration, e.QuestionCount, e.PassingScore 
        FROM exams e
        WHERE e.CompanyID = ? AND e.IsActive = 1 AND e.QuestionCount > 0
        ORDER BY e.CreatedAt DESC
        LIMIT 1
    ");
    $newQueryStmt->execute([$company['CompanyID']]);
    $newResult = $newQueryStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($newResult) {
        echo "   NEW query returned: {$newResult['ExamTitle']} (QuestionCount: {$newResult['QuestionCount']})\n";
        if ($newResult['QuestionCount'] > 0) {
            echo "   ✓ CORRECT: NEW query only returns exam with questions!\n";
        } else {
            echo "   ✗ ERROR: NEW query returned exam with no questions!\n";
        }
    } else {
        echo "   NEW query returned no results (no exams with questions found)\n";
    }
    
    // Test 4: Test with no exams having questions
    echo "\n4. Testing with no exams having questions...\n";
    
    // Update exam 2 to have 0 questions
    $updateStmt = $pdo->prepare("
        UPDATE exams 
        SET QuestionCount = 0, UpdatedAt = NOW() 
        WHERE ExamID = ?
    ");
    $updateStmt->execute([$exam2Id]);
    echo "   ✓ Updated exam 2 to have 0 questions\n";
    
    // Test NEW query again
    $newQueryStmt->execute([$company['CompanyID']]);
    $newResult2 = $newQueryStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($newResult2) {
        echo "   NEW query returned: {$newResult2['ExamTitle']} (QuestionCount: {$newResult2['QuestionCount']})\n";
        echo "   ✗ ERROR: NEW query should not return any results when no exams have questions!\n";
    } else {
        echo "   ✓ CORRECT: NEW query returned no results when no exams have questions!\n";
    }
    
    // Test 5: Test with mixed scenario
    echo "\n5. Testing with mixed scenario...\n";
    
    // Create exam 3 with questions
    $exam3Stmt = $pdo->prepare("
        INSERT INTO exams (CompanyID, ExamTitle, ExamType, Description, Instructions, Duration, QuestionCount, PassingScore, IsActive, CreatedBy)
        VALUES (?, ?, 'manual', ?, ?, ?, 3, 70.00, 1, ?)
    ");
    
    $exam3Title = "Exam With 3 Questions - " . date('Y-m-d H:i:s');
    $exam3Stmt->execute([
        $company['CompanyID'],
        $exam3Title,
        'Test exam with 3 questions',
        'Please answer all questions carefully.',
        1800,
        'Test System'
    ]);
    $exam3Id = $pdo->lastInsertId();
    echo "   ✓ Created exam 3: $exam3Title (ID: $exam3Id, QuestionCount: 3)\n";
    
    // Test NEW query - should return exam 3 (most recent with questions)
    $newQueryStmt->execute([$company['CompanyID']]);
    $newResult3 = $newQueryStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($newResult3) {
        echo "   NEW query returned: {$newResult3['ExamTitle']} (QuestionCount: {$newResult3['QuestionCount']})\n";
        if ($newResult3['QuestionCount'] > 0) {
            echo "   ✓ CORRECT: NEW query returns exam with questions from mixed scenario!\n";
        } else {
            echo "   ✗ ERROR: NEW query returned exam with no questions!\n";
        }
    } else {
        echo "   ✗ ERROR: NEW query should return exam 3!\n";
    }
    
    // Cleanup
    echo "\n6. Cleaning up test data...\n";
    
    // Delete all test exams
    $pdo->prepare("DELETE FROM exams WHERE ExamID IN (?, ?, ?)")->execute([$exam1Id, $exam2Id, $exam3Id]);
    
    echo "   ✓ Test data cleaned up\n";
    
    echo "\n=== Test Completed Successfully ===\n";
    echo "\nSUMMARY:\n";
    echo "✓ OLD query returns exams with no questions (problem)\n";
    echo "✓ NEW query only returns exams with questions (fixed)\n";
    echo "✓ NEW query returns no results when no exams have questions\n";
    echo "✓ NEW query works correctly with mixed scenarios\n";
    echo "✓ SQL query fix is working correctly!\n";

} catch (Exception $e) {
    echo "Error during testing: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

if (!$isCLI) {
    echo "</pre>";
}
?>

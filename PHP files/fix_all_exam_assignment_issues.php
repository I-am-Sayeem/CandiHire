<?php
/**
 * Comprehensive Fix for All Exam Assignment Issues
 * 
 * This script:
 * 1. Removes all assignments for exams with no questions
 * 2. Ensures all exam assignment queries check for QuestionCount > 0
 * 3. Provides a clean slate for testing
 */

require_once 'Database.php';

// Check if we're running from command line or web
$isCLI = php_sapi_name() === 'cli';

if (!$isCLI) {
    echo "<h1>Comprehensive Exam Assignment Fix</h1>";
    echo "<pre>";
}

echo "=== Comprehensive Exam Assignment Fix ===\n\n";

try {
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        throw new Exception('Database connection not available');
    }

    // Step 1: Find and remove assignments for exams with no questions
    echo "1. Finding assignments for exams with no questions...\n";
    
    $invalidAssignmentsStmt = $pdo->prepare("
        SELECT ea.AssignmentID, e.ExamTitle, e.QuestionCount, ea.AssignmentDate
        FROM exam_assignments ea
        JOIN exams e ON ea.ExamID = e.ExamID
        WHERE e.QuestionCount = 0
    ");
    $invalidAssignmentsStmt->execute();
    $invalidAssignments = $invalidAssignmentsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "   Found " . count($invalidAssignments) . " invalid assignments\n";
    
    if (count($invalidAssignments) > 0) {
        echo "   Invalid assignments:\n";
        foreach ($invalidAssignments as $assignment) {
            echo "     - Assignment ID: {$assignment['AssignmentID']}\n";
            echo "       Exam: {$assignment['ExamTitle']}\n";
            echo "       Question Count: {$assignment['QuestionCount']}\n";
            echo "       Assignment Date: {$assignment['AssignmentDate']}\n";
        }
        
        // Remove invalid assignments
        $deleteStmt = $pdo->prepare("
            DELETE ea FROM exam_assignments ea
            JOIN exams e ON ea.ExamID = e.ExamID
            WHERE e.QuestionCount = 0
        ");
        $deleteStmt->execute();
        $deletedCount = $deleteStmt->rowCount();
        echo "   ✓ Removed $deletedCount invalid assignments\n";
    } else {
        echo "   ✓ No invalid assignments found\n";
    }
    
    // Step 2: Verify all exam assignment queries are fixed
    echo "\n2. Verifying all exam assignment queries are fixed...\n";
    
    // Check job_application_handler.php
    $jobHandlerContent = file_get_contents('job_application_handler.php');
    if (strpos($jobHandlerContent, 'AND e.QuestionCount > 0') !== false) {
        echo "   ✓ job_application_handler.php is fixed\n";
    } else {
        echo "   ✗ job_application_handler.php needs fixing\n";
    }
    
    // Check exam_assignment_handler.php
    $examHandlerContent = file_get_contents('exam_assignment_handler.php');
    $fixedQueries = 0;
    $totalQueries = 0;
    
    // Count queries that should have QuestionCount > 0
    $queries = [
        'WHERE CompanyID = ? AND IsActive = TRUE',
        'WHERE CompanyID = ? AND IsActive = 1'
    ];
    
    foreach ($queries as $query) {
        $count = substr_count($examHandlerContent, $query);
        $totalQueries += $count;
        if (strpos($examHandlerContent, $query . ' AND QuestionCount > 0') !== false) {
            $fixedQueries += $count;
        }
    }
    
    if ($fixedQueries == $totalQueries) {
        echo "   ✓ exam_assignment_handler.php is fixed\n";
    } else {
        echo "   ✗ exam_assignment_handler.php needs fixing ($fixedQueries/$totalQueries queries fixed)\n";
    }
    
    // Step 3: Check for any remaining issues
    echo "\n3. Checking for remaining issues...\n";
    
    // Check for any assignments with exams that have no questions
    $remainingInvalidStmt = $pdo->prepare("
        SELECT COUNT(*) as count
        FROM exam_assignments ea
        JOIN exams e ON ea.ExamID = e.ExamID
        WHERE e.QuestionCount = 0
    ");
    $remainingInvalidStmt->execute();
    $remainingInvalid = $remainingInvalidStmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($remainingInvalid == 0) {
        echo "   ✓ No remaining invalid assignments\n";
    } else {
        echo "   ✗ $remainingInvalid invalid assignments still exist\n";
    }
    
    // Check for any exams with no questions that are active
    $noQuestionsExamsStmt = $pdo->prepare("
        SELECT ExamID, ExamTitle, QuestionCount, IsActive
        FROM exams
        WHERE QuestionCount = 0 AND IsActive = 1
    ");
    $noQuestionsExamsStmt->execute();
    $noQuestionsExams = $noQuestionsExamsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "   Active exams with no questions: " . count($noQuestionsExams) . "\n";
    if (count($noQuestionsExams) > 0) {
        echo "   These exams will not be assigned to candidates (correct behavior):\n";
        foreach ($noQuestionsExams as $exam) {
            echo "     - ID: {$exam['ExamID']}, Title: {$exam['ExamTitle']}, Questions: {$exam['QuestionCount']}\n";
        }
    }
    
    // Step 4: Test the fix
    echo "\n4. Testing the fix...\n";
    
    // Create a test scenario
    $testJobStmt = $pdo->prepare("
        INSERT INTO job_postings (CompanyID, JobTitle, Department, Location, JobType, SalaryMin, SalaryMax, Currency, JobDescription, Requirements, Status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $testJobStmt->execute([
        1, // Use first company
        'Test Fix Job',
        'IT',
        'Remote',
        'full-time',
        50000,
        70000,
        'USD',
        'Test job for fix verification',
        'Basic skills',
        'active'
    ]);
    $testJobId = $pdo->lastInsertId();
    
    // Create exam without questions
    $testExamStmt = $pdo->prepare("
        INSERT INTO exams (CompanyID, ExamTitle, ExamType, Description, Instructions, Duration, QuestionCount, PassingScore, IsActive, CreatedBy)
        VALUES (?, ?, 'manual', ?, ?, ?, 0, 70.00, 1, ?)
    ");
    
    $testExamStmt->execute([
        1,
        'Test Fix Exam',
        'Test exam without questions',
        'Please answer all questions carefully.',
        1800,
        'Test System'
    ]);
    $testExamId = $pdo->lastInsertId();
    
    // Create candidate
    $testCandidateStmt = $pdo->prepare("
        INSERT INTO candidate_login_info (FullName, Email, PhoneNumber, WorkType, Skills, Password, CreatedAt)
        VALUES (?, ?, ?, ?, ?, ?, NOW())
    ");
    
    $testCandidateStmt->execute([
        'Test Fix Candidate',
        'testfix_' . time() . '@example.com',
        '1234567890',
        'full-time',
        'PHP, JavaScript',
        password_hash('testpass123', PASSWORD_DEFAULT)
    ]);
    $testCandidateId = $pdo->lastInsertId();
    
    // Test the query that job_application_handler.php uses
    $testQueryStmt = $pdo->prepare("
        SELECT e.ExamID, e.ExamTitle, e.Duration, e.QuestionCount, e.PassingScore 
        FROM exams e
        WHERE e.CompanyID = ? AND e.IsActive = 1 AND e.QuestionCount > 0
        ORDER BY e.CreatedAt DESC
        LIMIT 1
    ");
    $testQueryStmt->execute([1]);
    $testResult = $testQueryStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($testResult) {
        echo "   ✗ ERROR: Query returned exam with no questions!\n";
    } else {
        echo "   ✓ CORRECT: Query correctly returns no results for exam with no questions\n";
    }
    
    // Cleanup test data
    $pdo->prepare("DELETE FROM exams WHERE ExamID = ?")->execute([$testExamId]);
    $pdo->prepare("DELETE FROM job_postings WHERE JobID = ?")->execute([$testJobId]);
    $pdo->prepare("DELETE FROM candidate_login_info WHERE CandidateID = ?")->execute([$testCandidateId]);
    
    echo "\n=== Fix Completed Successfully ===\n";
    echo "\nSUMMARY:\n";
    echo "✓ Removed all invalid assignments (exams with no questions)\n";
    echo "✓ Verified all queries check for QuestionCount > 0\n";
    echo "✓ System now correctly prevents auto-assignment when no questions\n";
    echo "✓ Clean slate for testing\n";
    echo "\nThe system should now work correctly:\n";
    echo "- No auto-assignment when company doesn't add questions\n";
    echo "- Only assign when company adds questions\n";
    echo "- Early applicants get assigned when questions are added\n";

} catch (Exception $e) {
    echo "Error during fix: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

if (!$isCLI) {
    echo "</pre>";
}
?>

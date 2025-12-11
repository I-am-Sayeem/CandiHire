<?php
/**
 * Test Script for Complete Application Flow
 * 
 * This script tests the complete flow from job application to exam assignment
 * to see exactly what's happening when a candidate applies for a job
 */

require_once 'Database.php';

// Check if we're running from command line or web
$isCLI = php_sapi_name() === 'cli';

if (!$isCLI) {
    echo "<h1>Complete Application Flow Test</h1>";
    echo "<pre>";
}

echo "=== Complete Application Flow Test ===\n\n";

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
    
    // Create a job post
    $jobStmt = $pdo->prepare("
        INSERT INTO job_postings (CompanyID, JobTitle, Department, Location, JobType, SalaryMin, SalaryMax, Currency, JobDescription, Requirements, Status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $jobStmt->execute([
        $company['CompanyID'],
        'Test Developer Position',
        'IT',
        'Remote',
        'full-time',
        50000,
        70000,
        'USD',
        'Test job for application flow testing',
        'Basic programming skills',
        'active'
    ]);
    $jobId = $pdo->lastInsertId();
    echo "   ✓ Created job: Test Developer Position (ID: $jobId)\n";
    
    // Create exam WITHOUT questions
    $examStmt = $pdo->prepare("
        INSERT INTO exams (CompanyID, ExamTitle, ExamType, Description, Instructions, Duration, QuestionCount, PassingScore, IsActive, CreatedBy)
        VALUES (?, ?, 'manual', ?, ?, ?, 0, 70.00, 1, ?)
    ");
    
    $examTitle = "Test Exam Without Questions - " . date('Y-m-d H:i:s');
    $examStmt->execute([
        $company['CompanyID'],
        $examTitle,
        'Test exam without questions',
        'Please answer all questions carefully.',
        1800,
        'Test System'
    ]);
    $examId = $pdo->lastInsertId();
    echo "   ✓ Created exam: $examTitle (ID: $examId, QuestionCount: 0)\n";
    
    // Create candidate
    $candidateStmt = $pdo->prepare("
        INSERT INTO candidate_login_info (FullName, Email, PhoneNumber, WorkType, Skills, Password, CreatedAt)
        VALUES (?, ?, ?, ?, ?, ?, NOW())
    ");
    
    $candidateEmail = 'test_candidate_' . time() . '@example.com';
    $candidateStmt->execute([
        'Test Candidate',
        $candidateEmail,
        '1234567890',
        'full-time',
        'PHP, JavaScript, MySQL',
        password_hash('testpass123', PASSWORD_DEFAULT)
    ]);
    $candidateId = $pdo->lastInsertId();
    echo "   ✓ Created candidate: Test Candidate (ID: $candidateId)\n";
    
    // Test 2: Check initial state
    echo "\n2. Checking initial state...\n";
    
    $initialAssignmentsStmt = $pdo->prepare("
        SELECT COUNT(*) as assignment_count
        FROM exam_assignments
        WHERE CandidateID = ?
    ");
    $initialAssignmentsStmt->execute([$candidateId]);
    $initialAssignmentCount = $initialAssignmentsStmt->fetch(PDO::FETCH_ASSOC)['assignment_count'];
    
    echo "   Initial exam assignments for candidate: $initialAssignmentCount\n";
    
    // Test 3: Simulate job application
    echo "\n3. Simulating job application...\n";
    
    // Create job application
    $applicationStmt = $pdo->prepare("
        INSERT INTO job_applications (JobID, CandidateID, CoverLetter, Notes, ApplicationDate)
        VALUES (?, ?, ?, ?, NOW())
    ");
    $applicationStmt->execute([
        $jobId,
        $candidateId,
        'I am interested in this position',
        'Testing application flow'
    ]);
    echo "   ✓ Job application created\n";
    
    // Test 4: Check if exam was assigned after application
    echo "\n4. Checking if exam was assigned after application...\n";
    
    $afterApplicationAssignmentsStmt = $pdo->prepare("
        SELECT COUNT(*) as assignment_count
        FROM exam_assignments
        WHERE CandidateID = ?
    ");
    $afterApplicationAssignmentsStmt->execute([$candidateId]);
    $afterApplicationAssignmentCount = $afterApplicationAssignmentsStmt->fetch(PDO::FETCH_ASSOC)['assignment_count'];
    
    echo "   Exam assignments after application: $afterApplicationAssignmentCount\n";
    
    if ($afterApplicationAssignmentCount > $initialAssignmentCount) {
        echo "   ✗ ERROR: Exam was assigned even though it has no questions!\n";
        
        // Get details of the assignment
        $assignmentDetailsStmt = $pdo->prepare("
            SELECT ea.AssignmentID, e.ExamTitle, e.QuestionCount, ea.Status
            FROM exam_assignments ea
            JOIN exams e ON ea.ExamID = e.ExamID
            WHERE ea.CandidateID = ?
        ");
        $assignmentDetailsStmt->execute([$candidateId]);
        $assignmentDetails = $assignmentDetailsStmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($assignmentDetails as $assignment) {
            echo "     - Assignment ID: {$assignment['AssignmentID']}\n";
            echo "     - Exam: {$assignment['ExamTitle']}\n";
            echo "     - Question Count: {$assignment['QuestionCount']}\n";
            echo "     - Status: {$assignment['Status']}\n";
        }
    } else {
        echo "   ✓ CORRECT: No exam was assigned (exam has no questions)\n";
    }
    
    // Test 5: Test the SQL queries directly
    echo "\n5. Testing SQL queries directly...\n";
    
    // Test the query from job_application_handler.php
    $queryStmt = $pdo->prepare("
        SELECT e.ExamID, e.ExamTitle, e.Duration, e.QuestionCount, e.PassingScore 
        FROM exams e
        WHERE e.CompanyID = ? AND e.IsActive = 1 AND e.QuestionCount > 0
        ORDER BY e.CreatedAt DESC
        LIMIT 1
    ");
    $queryStmt->execute([$company['CompanyID']]);
    $queryResult = $queryStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($queryResult) {
        echo "   ✗ ERROR: Query returned exam with questions when there should be none!\n";
        echo "     - Exam: {$queryResult['ExamTitle']}\n";
        echo "     - Question Count: {$queryResult['QuestionCount']}\n";
    } else {
        echo "   ✓ CORRECT: Query returned no results (no exams with questions)\n";
    }
    
    // Test 6: Check if there are any other exams with questions
    echo "\n6. Checking for any exams with questions...\n";
    
    $allExamsStmt = $pdo->prepare("
        SELECT ExamID, ExamTitle, QuestionCount, IsActive
        FROM exams
        WHERE CompanyID = ?
    ");
    $allExamsStmt->execute([$company['CompanyID']]);
    $allExams = $allExamsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "   All exams for this company:\n";
    foreach ($allExams as $exam) {
        echo "     - ID: {$exam['ExamID']}, Title: {$exam['ExamTitle']}, Questions: {$exam['QuestionCount']}, Active: " . ($exam['IsActive'] ? 'Yes' : 'No') . "\n";
    }
    
    // Test 7: Check if there are any other assignment sources
    echo "\n7. Checking for other assignment sources...\n";
    
    $allAssignmentsStmt = $pdo->prepare("
        SELECT ea.AssignmentID, e.ExamTitle, e.QuestionCount, ea.Status, ea.AssignmentDate
        FROM exam_assignments ea
        JOIN exams e ON ea.ExamID = e.ExamID
        WHERE ea.CandidateID = ?
        ORDER BY ea.AssignmentDate DESC
    ");
    $allAssignmentsStmt->execute([$candidateId]);
    $allAssignments = $allAssignmentsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($allAssignments) > 0) {
        echo "   All assignments for this candidate:\n";
        foreach ($allAssignments as $assignment) {
            echo "     - Assignment ID: {$assignment['AssignmentID']}\n";
            echo "     - Exam: {$assignment['ExamTitle']}\n";
            echo "     - Question Count: {$assignment['QuestionCount']}\n";
            echo "     - Status: {$assignment['Status']}\n";
            echo "     - Assignment Date: {$assignment['AssignmentDate']}\n";
        }
    } else {
        echo "   No assignments found for this candidate\n";
    }
    
    // Cleanup
    echo "\n8. Cleaning up test data...\n";
    
    // Delete exam assignments
    $pdo->prepare("DELETE FROM exam_assignments WHERE CandidateID = ?")->execute([$candidateId]);
    
    // Delete exam
    $pdo->prepare("DELETE FROM exams WHERE ExamID = ?")->execute([$examId]);
    
    // Delete job application
    $pdo->prepare("DELETE FROM job_applications WHERE JobID = ? AND CandidateID = ?")->execute([$jobId, $candidateId]);
    
    // Delete candidate
    $pdo->prepare("DELETE FROM candidate_login_info WHERE CandidateID = ?")->execute([$candidateId]);
    
    // Delete job post
    $pdo->prepare("DELETE FROM job_postings WHERE JobID = ?")->execute([$jobId]);
    
    echo "   ✓ Test data cleaned up\n";
    
    echo "\n=== Test Completed ===\n";

} catch (Exception $e) {
    echo "Error during testing: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

if (!$isCLI) {
    echo "</pre>";
}
?>

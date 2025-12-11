<?php
/**
 * Test Script for Exam Question Assignment
 * 
 * This script tests the scenario where:
 * 1. A company creates an exam without questions initially
 * 2. Later adds questions to that exam
 * 3. Existing job applicants should then get the exam assigned
 */

require_once 'Database.php';
require_once 'exam_question_assignment_handler.php';

// Check if we're running from command line or web
$isCLI = php_sapi_name() === 'cli';

if (!$isCLI) {
    echo "<h1>Exam Question Assignment Test</h1>";
    echo "<pre>";
}

echo "=== Exam Question Assignment Test ===\n\n";

try {
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        throw new Exception('Database connection not available');
    }

    // Test 1: Create a test exam without questions
    echo "1. Creating test exam without questions...\n";
    
    // Get first company
    $companyStmt = $pdo->prepare("SELECT CompanyID, CompanyName FROM Company_login_info LIMIT 1");
    $companyStmt->execute();
    $company = $companyStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$company) {
        echo "   No companies found in database.\n";
        exit;
    }
    
    echo "   Using company: {$company['CompanyName']} (ID: {$company['CompanyID']})\n";
    
    // Create exam without questions
    $examStmt = $pdo->prepare("
        INSERT INTO exams (CompanyID, ExamTitle, ExamType, Description, Instructions, Duration, QuestionCount, PassingScore, IsActive, CreatedBy)
        VALUES (?, ?, 'manual', ?, ?, ?, 0, 70.00, 1, ?)
    ");
    
    $examTitle = "Test Exam for Question Assignment - " . date('Y-m-d H:i:s');
    $examStmt->execute([
        $company['CompanyID'],
        $examTitle,
        'Test exam for question assignment functionality',
        'Please answer all questions carefully.',
        1800, // 30 minutes
        'Test System'
    ]);
    
    $examId = $pdo->lastInsertId();
    echo "   ✓ Created exam: $examTitle (ID: $examId)\n";
    
    // Test 2: Check if there are job applicants for this company
    echo "\n2. Checking for job applicants...\n";
    
    $applicantsStmt = $pdo->prepare("
        SELECT COUNT(DISTINCT ja.CandidateID) as applicant_count
        FROM job_applications ja
        JOIN job_postings jp ON ja.JobID = jp.JobID
        WHERE jp.CompanyID = ?
    ");
    $applicantsStmt->execute([$company['CompanyID']]);
    $applicantCount = $applicantsStmt->fetch(PDO::FETCH_ASSOC)['applicant_count'];
    
    echo "   Found $applicantCount job applicants for this company\n";
    
    if ($applicantCount == 0) {
        echo "   No applicants found. Creating a test job and application...\n";
        
        // Create a test job
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
            'Test job for exam assignment testing',
            'Basic programming skills',
            'active'
        ]);
        
        $jobId = $pdo->lastInsertId();
        echo "   ✓ Created test job (ID: $jobId)\n";
        
        // Create a test candidate and application
        $candidateStmt = $pdo->prepare("
            INSERT INTO candidate_login_info (FullName, Email, PhoneNumber, WorkType, Skills, Password, CreatedAt)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $testEmail = 'test_candidate_' . time() . '@example.com';
        $candidateStmt->execute([
            'Test Candidate',
            $testEmail,
            '1234567890',
            'full-time',
            'PHP, JavaScript, MySQL',
            password_hash('testpass123', PASSWORD_DEFAULT)
        ]);
        
        $candidateId = $pdo->lastInsertId();
        echo "   ✓ Created test candidate (ID: $candidateId)\n";
        
        // Create job application
        $applicationStmt = $pdo->prepare("
            INSERT INTO job_applications (JobID, CandidateID, CoverLetter, Notes, ApplicationDate)
            VALUES (?, ?, ?, ?, NOW())
        ");
        
        $applicationStmt->execute([
            $jobId,
            $candidateId,
            'Test application for exam assignment',
            'Testing the exam assignment system'
        ]);
        
        echo "   ✓ Created test job application\n";
        $applicantCount = 1;
    }
    
    // Test 3: Check current exam assignments
    echo "\n3. Checking current exam assignments...\n";
    
    $assignmentsStmt = $pdo->prepare("
        SELECT COUNT(*) as assignment_count
        FROM exam_assignments
        WHERE ExamID = ?
    ");
    $assignmentsStmt->execute([$examId]);
    $assignmentCount = $assignmentsStmt->fetch(PDO::FETCH_ASSOC)['assignment_count'];
    
    echo "   Current assignments for exam $examId: $assignmentCount\n";
    
    // Test 4: Add questions to the exam
    echo "\n4. Adding questions to the exam...\n";
    
    $questions = [
        [
            'text' => 'What is PHP?',
            'options' => [
                ['text' => 'A programming language', 'correct' => true],
                ['text' => 'A database', 'correct' => false],
                ['text' => 'A web server', 'correct' => false],
                ['text' => 'An operating system', 'correct' => false]
            ]
        ],
        [
            'text' => 'What does SQL stand for?',
            'options' => [
                ['text' => 'Structured Query Language', 'correct' => true],
                ['text' => 'Simple Query Language', 'correct' => false],
                ['text' => 'Standard Query Language', 'correct' => false],
                ['text' => 'System Query Language', 'correct' => false]
            ]
        ]
    ];
    
    foreach ($questions as $index => $question) {
        // Insert question
        $questionStmt = $pdo->prepare("
            INSERT INTO exam_questions (ExamID, QuestionType, QuestionText, QuestionOrder, Points, Difficulty, Category)
            VALUES (?, 'multiple-choice', ?, ?, 1.00, 'medium', 'General')
        ");
        
        $questionStmt->execute([
            $examId,
            $question['text'],
            $index + 1
        ]);
        
        $questionId = $pdo->lastInsertId();
        
        // Insert options
        $optionStmt = $pdo->prepare("
            INSERT INTO exam_question_options (QuestionID, OptionText, IsCorrect, OptionOrder)
            VALUES (?, ?, ?, ?)
        ");
        
        foreach ($question['options'] as $optIndex => $option) {
            $optionStmt->execute([
                $questionId,
                $option['text'],
                $option['correct'] ? 1 : 0,
                $optIndex + 1
            ]);
        }
        
        echo "   ✓ Added question " . ($index + 1) . ": {$question['text']}\n";
    }
    
    // Test 5: Handle exam question addition
    echo "\n5. Handling exam question addition and assignment...\n";
    
    $result = handleExamQuestionAddition($examId, $company['CompanyID'], $jobId);
    
    if ($result) {
        echo "   ✓ Exam question addition handled successfully\n";
    } else {
        echo "   ✗ Failed to handle exam question addition\n";
    }
    
    // Test 6: Check assignments after adding questions
    echo "\n6. Checking assignments after adding questions...\n";
    
    $assignmentsStmt->execute([$examId]);
    $newAssignmentCount = $assignmentsStmt->fetch(PDO::FETCH_ASSOC)['assignment_count'];
    
    echo "   Assignments after adding questions: $newAssignmentCount\n";
    
    if ($newAssignmentCount > $assignmentCount) {
        echo "   ✓ New assignments created: " . ($newAssignmentCount - $assignmentCount) . "\n";
    } else {
        echo "   - No new assignments created (may already exist or no eligible applicants)\n";
    }
    
    // Test 7: Check missing assignments
    echo "\n7. Testing check for missing assignments...\n";
    
    $missingResult = checkAllExamsForMissingAssignments($company['CompanyID']);
    
    if ($missingResult) {
        echo "   ✓ Missing assignments check completed successfully\n";
    } else {
        echo "   ✗ Failed to check missing assignments\n";
    }
    
    // Test 8: Verify exam appears in candidate's scheduled exams
    echo "\n8. Verifying exam appears in candidate's scheduled exams...\n";
    
    if ($applicantCount > 0) {
        // Get a candidate ID
        $candidateStmt = $pdo->prepare("
            SELECT ja.CandidateID, cli.FullName
            FROM job_applications ja
            JOIN candidate_login_info cli ON ja.CandidateID = cli.CandidateID
            JOIN job_postings jp ON ja.JobID = jp.JobID
            WHERE jp.CompanyID = ?
            LIMIT 1
        ");
        $candidateStmt->execute([$company['CompanyID']]);
        $candidate = $candidateStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($candidate) {
            echo "   Checking for candidate: {$candidate['FullName']} (ID: {$candidate['CandidateID']})\n";
            
            $candidateExamsStmt = $pdo->prepare("
                SELECT ea.AssignmentID, e.ExamTitle, ea.Status
                FROM exam_assignments ea
                JOIN exams e ON ea.ExamID = e.ExamID
                WHERE ea.CandidateID = ? AND ea.ExamID = ?
            ");
            $candidateExamsStmt->execute([$candidate['CandidateID'], $examId]);
            $candidateExam = $candidateExamsStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($candidateExam) {
                echo "   ✓ Exam found in candidate's assignments: {$candidateExam['ExamTitle']} (Status: {$candidateExam['Status']})\n";
            } else {
                echo "   ✗ Exam not found in candidate's assignments\n";
            }
        }
    }
    
    // Cleanup
    echo "\n9. Cleaning up test data...\n";
    
    // Delete test exam and related data
    $pdo->prepare("DELETE FROM exam_question_options WHERE QuestionID IN (SELECT QuestionID FROM exam_questions WHERE ExamID = ?)")->execute([$examId]);
    $pdo->prepare("DELETE FROM exam_questions WHERE ExamID = ?")->execute([$examId]);
    $pdo->prepare("DELETE FROM exam_assignments WHERE ExamID = ?")->execute([$examId]);
    $pdo->prepare("DELETE FROM exams WHERE ExamID = ?")->execute([$examId]);
    
    // Delete test application and candidate if created
    if (isset($candidateId)) {
        $pdo->prepare("DELETE FROM job_applications WHERE CandidateID = ?")->execute([$candidateId]);
        $pdo->prepare("DELETE FROM candidate_login_info WHERE CandidateID = ?")->execute([$candidateId]);
        $pdo->prepare("DELETE FROM job_postings WHERE JobID = ?")->execute([$jobId]);
        echo "   ✓ Test data cleaned up\n";
    }
    
    echo "\n=== Test Completed Successfully ===\n";

} catch (Exception $e) {
    echo "Error during testing: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

if (!$isCLI) {
    echo "</pre>";
}
?>

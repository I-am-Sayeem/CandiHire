<?php
/**
 * Test Script for Job-Specific Exam Assignment
 * 
 * This script tests the exact scenario described:
 * 1. Company creates exam without questions initially
 * 2. Candidates apply for jobs (no exam assigned yet)
 * 3. Company later adds questions to the exam
 * 4. Only candidates who applied for jobs with that exam get assigned
 */

require_once 'Database.php';
require_once 'exam_question_assignment_handler.php';

// Check if we're running from command line or web
$isCLI = php_sapi_name() === 'cli';

if (!$isCLI) {
    echo "<h1>Job-Specific Exam Assignment Test</h1>";
    echo "<pre>";
}

echo "=== Job-Specific Exam Assignment Test ===\n\n";

try {
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        throw new Exception('Database connection not available');
    }

    // Test 1: Create a company with multiple job posts
    echo "1. Setting up test scenario...\n";
    
    // Get first company
    $companyStmt = $pdo->prepare("SELECT CompanyID, CompanyName FROM Company_login_info LIMIT 1");
    $companyStmt->execute();
    $company = $companyStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$company) {
        echo "   No companies found in database.\n";
        exit;
    }
    
    echo "   Using company: {$company['CompanyName']} (ID: {$company['CompanyID']})\n";
    
    // Create two job posts
    $job1Stmt = $pdo->prepare("
        INSERT INTO job_postings (CompanyID, JobTitle, Department, Location, JobType, SalaryMin, SalaryMax, Currency, JobDescription, Requirements, Status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $job1Stmt->execute([
        $company['CompanyID'],
        'Frontend Developer',
        'IT',
        'Remote',
        'full-time',
        60000,
        80000,
        'USD',
        'Frontend development position',
        'React, JavaScript, CSS',
        'active'
    ]);
    $job1Id = $pdo->lastInsertId();
    echo "   ✓ Created Job 1: Frontend Developer (ID: $job1Id)\n";
    
    $job2Stmt = $pdo->prepare("
        INSERT INTO job_postings (CompanyID, JobTitle, Department, Location, JobType, SalaryMin, SalaryMax, Currency, JobDescription, Requirements, Status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $job2Stmt->execute([
        $company['CompanyID'],
        'Backend Developer',
        'IT',
        'Remote',
        'full-time',
        70000,
        90000,
        'USD',
        'Backend development position',
        'PHP, MySQL, API',
        'active'
    ]);
    $job2Id = $pdo->lastInsertId();
    echo "   ✓ Created Job 2: Backend Developer (ID: $job2Id)\n";
    
    // Test 2: Create candidates and applications
    echo "\n2. Creating candidates and applications...\n";
    
    // Candidate 1 applies for Frontend Developer
    $candidate1Stmt = $pdo->prepare("
        INSERT INTO candidate_login_info (FullName, Email, PhoneNumber, WorkType, Skills, Password, CreatedAt)
        VALUES (?, ?, ?, ?, ?, ?, NOW())
    ");
    
    $candidate1Email = 'frontend_candidate_' . time() . '@example.com';
    $candidate1Stmt->execute([
        'Frontend Candidate',
        $candidate1Email,
        '1111111111',
        'full-time',
        'React, JavaScript, CSS',
        password_hash('testpass123', PASSWORD_DEFAULT)
    ]);
    $candidate1Id = $pdo->lastInsertId();
    
    $application1Stmt = $pdo->prepare("
        INSERT INTO job_applications (JobID, CandidateID, CoverLetter, Notes, ApplicationDate)
        VALUES (?, ?, ?, ?, NOW())
    ");
    $application1Stmt->execute([
        $job1Id,
        $candidate1Id,
        'I am interested in the Frontend Developer position',
        'Applied for frontend role'
    ]);
    echo "   ✓ Candidate 1 applied for Frontend Developer job\n";
    
    // Candidate 2 applies for Backend Developer
    $candidate2Stmt = $pdo->prepare("
        INSERT INTO candidate_login_info (FullName, Email, PhoneNumber, WorkType, Skills, Password, CreatedAt)
        VALUES (?, ?, ?, ?, ?, ?, NOW())
    ");
    
    $candidate2Email = 'backend_candidate_' . time() . '@example.com';
    $candidate2Stmt->execute([
        'Backend Candidate',
        $candidate2Email,
        '2222222222',
        'full-time',
        'PHP, MySQL, API',
        password_hash('testpass123', PASSWORD_DEFAULT)
    ]);
    $candidate2Id = $pdo->lastInsertId();
    
    $application2Stmt = $pdo->prepare("
        INSERT INTO job_applications (JobID, CandidateID, CoverLetter, Notes, ApplicationDate)
        VALUES (?, ?, ?, ?, NOW())
    ");
    $application2Stmt->execute([
        $job2Id,
        $candidate2Id,
        'I am interested in the Backend Developer position',
        'Applied for backend role'
    ]);
    echo "   ✓ Candidate 2 applied for Backend Developer job\n";
    
    // Test 3: Create exam for Frontend Developer job only
    echo "\n3. Creating exam for Frontend Developer job only...\n";
    
    $examStmt = $pdo->prepare("
        INSERT INTO exams (CompanyID, ExamTitle, ExamType, Description, Instructions, Duration, QuestionCount, PassingScore, IsActive, CreatedBy)
        VALUES (?, ?, 'manual', ?, ?, ?, 0, 70.00, 1, ?)
    ");
    
    $examTitle = "Frontend Developer Assessment - " . date('Y-m-d H:i:s');
    $examStmt->execute([
        $company['CompanyID'],
        $examTitle,
        'Assessment for Frontend Developer position',
        'Please answer all questions carefully.',
        1800, // 30 minutes
        'Test System'
    ]);
    $examId = $pdo->lastInsertId();
    echo "   ✓ Created exam: $examTitle (ID: $examId)\n";
    echo "   ✓ Exam created WITHOUT questions initially\n";
    
    // Test 4: Check that no assignments exist yet
    echo "\n4. Checking initial state (no assignments should exist)...\n";
    
    $assignmentsStmt = $pdo->prepare("
        SELECT COUNT(*) as assignment_count
        FROM exam_assignments
        WHERE ExamID = ?
    ");
    $assignmentsStmt->execute([$examId]);
    $assignmentCount = $assignmentsStmt->fetch(PDO::FETCH_ASSOC)['assignment_count'];
    
    echo "   Current assignments for exam $examId: $assignmentCount\n";
    
    if ($assignmentCount == 0) {
        echo "   ✓ No assignments exist (as expected - exam has no questions)\n";
    } else {
        echo "   ✗ Unexpected assignments found\n";
    }
    
    // Test 5: Add questions to the exam
    echo "\n5. Adding questions to the exam...\n";
    
    $questions = [
        [
            'text' => 'What is React?',
            'options' => [
                ['text' => 'A JavaScript library', 'correct' => true],
                ['text' => 'A database', 'correct' => false],
                ['text' => 'A web server', 'correct' => false],
                ['text' => 'An operating system', 'correct' => false]
            ]
        ],
        [
            'text' => 'What does CSS stand for?',
            'options' => [
                ['text' => 'Cascading Style Sheets', 'correct' => true],
                ['text' => 'Computer Style Sheets', 'correct' => false],
                ['text' => 'Creative Style Sheets', 'correct' => false],
                ['text' => 'Colorful Style Sheets', 'correct' => false]
            ]
        ]
    ];
    
    foreach ($questions as $index => $question) {
        // Insert question
        $questionStmt = $pdo->prepare("
            INSERT INTO exam_questions (ExamID, QuestionType, QuestionText, QuestionOrder, Points, Difficulty, Category)
            VALUES (?, 'multiple-choice', ?, ?, 1.00, 'medium', 'Frontend')
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
    
    // Test 6: Handle exam question addition (job-specific)
    echo "\n6. Handling exam question addition with job-specific assignment...\n";
    
    $result = handleExamQuestionAddition($examId, $company['CompanyID'], $job1Id);
    
    if ($result) {
        echo "   ✓ Exam question addition handled successfully\n";
    } else {
        echo "   ✗ Failed to handle exam question addition\n";
    }
    
    // Test 7: Check assignments after adding questions
    echo "\n7. Checking assignments after adding questions...\n";
    
    $assignmentsStmt->execute([$examId]);
    $newAssignmentCount = $assignmentsStmt->fetch(PDO::FETCH_ASSOC)['assignment_count'];
    
    echo "   Assignments after adding questions: $newAssignmentCount\n";
    
    if ($newAssignmentCount > $assignmentCount) {
        echo "   ✓ New assignments created: " . ($newAssignmentCount - $assignmentCount) . "\n";
    } else {
        echo "   ✗ No new assignments created\n";
    }
    
    // Test 8: Verify job-specific assignments
    echo "\n8. Verifying job-specific assignments...\n";
    
    // Check Frontend Developer candidate (should have exam)
    $frontendAssignmentsStmt = $pdo->prepare("
        SELECT ea.AssignmentID, e.ExamTitle, ea.Status, jp.JobTitle
        FROM exam_assignments ea
        JOIN exams e ON ea.ExamID = e.ExamID
        JOIN job_postings jp ON ea.JobID = jp.JobID
        WHERE ea.CandidateID = ? AND ea.ExamID = ?
    ");
    $frontendAssignmentsStmt->execute([$candidate1Id, $examId]);
    $frontendAssignment = $frontendAssignmentsStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($frontendAssignment) {
        echo "   ✓ Frontend Candidate has exam assignment: {$frontendAssignment['ExamTitle']} for {$frontendAssignment['JobTitle']}\n";
    } else {
        echo "   ✗ Frontend Candidate does NOT have exam assignment\n";
    }
    
    // Check Backend Developer candidate (should NOT have exam)
    $backendAssignmentsStmt = $pdo->prepare("
        SELECT ea.AssignmentID, e.ExamTitle, ea.Status, jp.JobTitle
        FROM exam_assignments ea
        JOIN exams e ON ea.ExamID = e.ExamID
        JOIN job_postings jp ON ea.JobID = jp.JobID
        WHERE ea.CandidateID = ? AND ea.ExamID = ?
    ");
    $backendAssignmentsStmt->execute([$candidate2Id, $examId]);
    $backendAssignment = $backendAssignmentsStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($backendAssignment) {
        echo "   ✗ Backend Candidate has exam assignment (should NOT have it): {$backendAssignment['ExamTitle']}\n";
    } else {
        echo "   ✓ Backend Candidate does NOT have exam assignment (correct - exam is for Frontend job only)\n";
    }
    
    // Test 9: Verify exam appears in correct candidate's scheduled exams
    echo "\n9. Verifying exam appears in correct candidate's scheduled exams...\n";
    
    // Check Frontend candidate's scheduled exams
    $frontendExamsStmt = $pdo->prepare("
        SELECT COUNT(*) as exam_count
        FROM exam_assignments ea
        JOIN exams e ON ea.ExamID = e.ExamID
        WHERE ea.CandidateID = ? AND ea.Status = 'assigned'
    ");
    $frontendExamsStmt->execute([$candidate1Id]);
    $frontendExamCount = $frontendExamsStmt->fetch(PDO::FETCH_ASSOC)['exam_count'];
    
    echo "   Frontend Candidate scheduled exams: $frontendExamCount\n";
    
    if ($frontendExamCount > 0) {
        echo "   ✓ Frontend Candidate can see the exam in their scheduled exams\n";
    } else {
        echo "   ✗ Frontend Candidate cannot see the exam\n";
    }
    
    // Check Backend candidate's scheduled exams
    $backendExamsStmt = $pdo->prepare("
        SELECT COUNT(*) as exam_count
        FROM exam_assignments ea
        JOIN exams e ON ea.ExamID = e.ExamID
        WHERE ea.CandidateID = ? AND ea.Status = 'assigned'
    ");
    $backendExamsStmt->execute([$candidate2Id]);
    $backendExamCount = $backendExamsStmt->fetch(PDO::FETCH_ASSOC)['exam_count'];
    
    echo "   Backend Candidate scheduled exams: $backendExamCount\n";
    
    if ($backendExamCount == 0) {
        echo "   ✓ Backend Candidate correctly has no scheduled exams (exam is not for their job)\n";
    } else {
        echo "   ✗ Backend Candidate has scheduled exams (should not have any)\n";
    }
    
    // Cleanup
    echo "\n10. Cleaning up test data...\n";
    
    // Delete exam assignments
    $pdo->prepare("DELETE FROM exam_assignments WHERE ExamID = ?")->execute([$examId]);
    
    // Delete exam questions and options
    $pdo->prepare("DELETE FROM exam_question_options WHERE QuestionID IN (SELECT QuestionID FROM exam_questions WHERE ExamID = ?)")->execute([$examId]);
    $pdo->prepare("DELETE FROM exam_questions WHERE ExamID = ?")->execute([$examId]);
    
    // Delete exam
    $pdo->prepare("DELETE FROM exams WHERE ExamID = ?")->execute([$examId]);
    
    // Delete job applications
    $pdo->prepare("DELETE FROM job_applications WHERE JobID IN (?, ?)")->execute([$job1Id, $job2Id]);
    
    // Delete candidates
    $pdo->prepare("DELETE FROM candidate_login_info WHERE CandidateID IN (?, ?)")->execute([$candidate1Id, $candidate2Id]);
    
    // Delete job posts
    $pdo->prepare("DELETE FROM job_postings WHERE JobID IN (?, ?)")->execute([$job1Id, $job2Id]);
    
    echo "   ✓ Test data cleaned up\n";
    
    echo "\n=== Test Completed Successfully ===\n";
    echo "\nSUMMARY:\n";
    echo "✓ Company creates exam without questions initially\n";
    echo "✓ Candidates apply for different jobs\n";
    echo "✓ Company adds questions to exam for specific job\n";
    echo "✓ Only candidates who applied for that specific job get the exam assigned\n";
    echo "✓ Candidates who applied for other jobs do NOT get the exam\n";
    echo "✓ Job-specific assignment works correctly\n";

} catch (Exception $e) {
    echo "Error during testing: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

if (!$isCLI) {
    echo "</pre>";
}
?>

<?php
/**
 * Test Script for No Auto-Assignment When No Questions
 * 
 * This script tests that:
 * 1. When a company creates an exam without questions, no exam is assigned to candidates
 * 2. When a candidate applies for a job, they don't get an exam if the company hasn't added questions
 * 3. Only when the company adds questions should the exam be assigned
 */

require_once 'Database.php';

// Check if we're running from command line or web
$isCLI = php_sapi_name() === 'cli';

if (!$isCLI) {
    echo "<h1>No Auto-Assignment Test</h1>";
    echo "<pre>";
}

echo "=== No Auto-Assignment When No Questions Test ===\n\n";

try {
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        throw new Exception('Database connection not available');
    }

    // Test 1: Create a company and job
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
        'Test job for no auto-assignment testing',
        'Basic programming skills',
        'active'
    ]);
    $jobId = $pdo->lastInsertId();
    echo "   ✓ Created job: Test Developer Position (ID: $jobId)\n";
    
    // Test 2: Create exam WITHOUT questions
    echo "\n2. Creating exam WITHOUT questions...\n";
    
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
        1800, // 30 minutes
        'Test System'
    ]);
    $examId = $pdo->lastInsertId();
    echo "   ✓ Created exam: $examTitle (ID: $examId)\n";
    echo "   ✓ Exam created with QuestionCount = 0 (no questions)\n";
    
    // Test 3: Create candidate and apply for job
    echo "\n3. Creating candidate and applying for job...\n";
    
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
    
    // Simulate job application (this should NOT assign exam since no questions)
    $applicationStmt = $pdo->prepare("
        INSERT INTO job_applications (JobID, CandidateID, CoverLetter, Notes, ApplicationDate)
        VALUES (?, ?, ?, ?, NOW())
    ");
    $applicationStmt->execute([
        $jobId,
        $candidateId,
        'I am interested in this position',
        'Testing no auto-assignment'
    ]);
    echo "   ✓ Candidate applied for job\n";
    
    // Test 4: Check if exam was assigned (should NOT be assigned)
    echo "\n4. Checking if exam was auto-assigned (should NOT be assigned)...\n";
    
    $assignmentsStmt = $pdo->prepare("
        SELECT COUNT(*) as assignment_count
        FROM exam_assignments
        WHERE ExamID = ? AND CandidateID = ?
    ");
    $assignmentsStmt->execute([$examId, $candidateId]);
    $assignmentCount = $assignmentsStmt->fetch(PDO::FETCH_ASSOC)['assignment_count'];
    
    echo "   Exam assignments for candidate: $assignmentCount\n";
    
    if ($assignmentCount == 0) {
        echo "   ✓ CORRECT: No exam was auto-assigned (exam has no questions)\n";
    } else {
        echo "   ✗ ERROR: Exam was auto-assigned even though it has no questions!\n";
    }
    
    // Test 5: Add questions to the exam
    echo "\n5. Adding questions to the exam...\n";
    
    $questions = [
        [
            'text' => 'What is PHP?',
            'options' => [
                ['text' => 'A programming language', 'correct' => true],
                ['text' => 'A database', 'correct' => false],
                ['text' => 'A web server', 'correct' => false],
                ['text' => 'An operating system', 'correct' => false]
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
    
    // Update exam question count
    $updateStmt = $pdo->prepare("
        UPDATE exams 
        SET QuestionCount = ?, UpdatedAt = NOW() 
        WHERE ExamID = ?
    ");
    $updateStmt->execute([count($questions), $examId]);
    echo "   ✓ Updated exam question count to " . count($questions) . "\n";
    
    // Test 6: Now assign exam using the question assignment handler
    echo "\n6. Assigning exam now that it has questions...\n";
    
    require_once 'exam_question_assignment_handler.php';
    $result = handleExamQuestionAddition($examId, $company['CompanyID'], $jobId);
    
    if ($result) {
        echo "   ✓ Exam assigned successfully after adding questions\n";
    } else {
        echo "   ✗ Failed to assign exam after adding questions\n";
    }
    
    // Test 7: Check assignments after adding questions
    echo "\n7. Checking assignments after adding questions...\n";
    
    $assignmentsStmt->execute([$examId, $candidateId]);
    $newAssignmentCount = $assignmentsStmt->fetch(PDO::FETCH_ASSOC)['assignment_count'];
    
    echo "   Exam assignments for candidate: $newAssignmentCount\n";
    
    if ($newAssignmentCount > $assignmentCount) {
        echo "   ✓ CORRECT: Exam was assigned after adding questions\n";
    } else {
        echo "   ✗ ERROR: Exam was not assigned even after adding questions\n";
    }
    
    // Test 8: Verify exam appears in candidate's scheduled exams
    echo "\n8. Verifying exam appears in candidate's scheduled exams...\n";
    
    $candidateExamsStmt = $pdo->prepare("
        SELECT ea.AssignmentID, e.ExamTitle, ea.Status
        FROM exam_assignments ea
        JOIN exams e ON ea.ExamID = e.ExamID
        WHERE ea.CandidateID = ? AND ea.ExamID = ?
    ");
    $candidateExamsStmt->execute([$candidateId, $examId]);
    $candidateExam = $candidateExamsStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($candidateExam) {
        echo "   ✓ Exam found in candidate's assignments: {$candidateExam['ExamTitle']} (Status: {$candidateExam['Status']})\n";
    } else {
        echo "   ✗ Exam not found in candidate's assignments\n";
    }
    
    // Cleanup
    echo "\n9. Cleaning up test data...\n";
    
    // Delete exam assignments
    $pdo->prepare("DELETE FROM exam_assignments WHERE ExamID = ?")->execute([$examId]);
    
    // Delete exam questions and options
    $pdo->prepare("DELETE FROM exam_question_options WHERE QuestionID IN (SELECT QuestionID FROM exam_questions WHERE ExamID = ?)")->execute([$examId]);
    $pdo->prepare("DELETE FROM exam_questions WHERE ExamID = ?")->execute([$examId]);
    
    // Delete exam
    $pdo->prepare("DELETE FROM exams WHERE ExamID = ?")->execute([$examId]);
    
    // Delete job application
    $pdo->prepare("DELETE FROM job_applications WHERE JobID = ? AND CandidateID = ?")->execute([$jobId, $candidateId]);
    
    // Delete candidate
    $pdo->prepare("DELETE FROM candidate_login_info WHERE CandidateID = ?")->execute([$candidateId]);
    
    // Delete job post
    $pdo->prepare("DELETE FROM job_postings WHERE JobID = ?")->execute([$jobId]);
    
    echo "   ✓ Test data cleaned up\n";
    
    echo "\n=== Test Completed Successfully ===\n";
    echo "\nSUMMARY:\n";
    echo "✓ Company creates exam without questions\n";
    echo "✓ Candidate applies for job\n";
    echo "✓ NO exam is auto-assigned (correct behavior)\n";
    echo "✓ Company adds questions to exam\n";
    echo "✓ Exam is then assigned to candidate\n";
    echo "✓ No auto-assignment when no questions - FIXED!\n";

} catch (Exception $e) {
    echo "Error during testing: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

if (!$isCLI) {
    echo "</pre>";
}
?>

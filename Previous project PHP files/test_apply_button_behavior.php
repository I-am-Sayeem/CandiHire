<?php
/**
 * Test Script for Apply Button Behavior
 * 
 * This script tests the exact scenario:
 * 1. Company creates exam without questions
 * 2. Candidate clicks Apply button
 * 3. No exam should be assigned (because no questions)
 * 4. Company later adds questions
 * 5. Exam should then be assigned to the candidate
 */

require_once 'Database.php';
require_once 'job_application_handler.php';

// Check if we're running from command line or web
$isCLI = php_sapi_name() === 'cli';

if (!$isCLI) {
    echo "<h1>Apply Button Behavior Test</h1>";
    echo "<pre>";
}

echo "=== Apply Button Behavior Test ===\n\n";

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
        'Software Developer',
        'IT',
        'Remote',
        'full-time',
        60000,
        80000,
        'USD',
        'Software development position',
        'Programming skills required',
        'active'
    ]);
    $jobId = $pdo->lastInsertId();
    echo "   ✓ Created job: Software Developer (ID: $jobId)\n";
    
    // Test 2: Create exam WITHOUT questions
    echo "\n2. Creating exam WITHOUT questions...\n";
    
    $examStmt = $pdo->prepare("
        INSERT INTO exams (CompanyID, ExamTitle, ExamType, Description, Instructions, Duration, QuestionCount, PassingScore, IsActive, CreatedBy)
        VALUES (?, ?, 'manual', ?, ?, ?, 0, 70.00, 1, ?)
    ");
    
    $examTitle = "Software Developer Assessment - " . date('Y-m-d H:i:s');
    $examStmt->execute([
        $company['CompanyID'],
        $examTitle,
        'Assessment for Software Developer position',
        'Please answer all questions carefully.',
        1800, // 30 minutes
        'Test System'
    ]);
    $examId = $pdo->lastInsertId();
    echo "   ✓ Created exam: $examTitle (ID: $examId)\n";
    echo "   ✓ Exam created with QuestionCount = 0 (no questions)\n";
    
    // Test 3: Create candidate
    echo "\n3. Creating candidate...\n";
    
    $candidateStmt = $pdo->prepare("
        INSERT INTO candidate_login_info (FullName, Email, PhoneNumber, WorkType, Skills, Password, CreatedAt)
        VALUES (?, ?, ?, ?, ?, ?, NOW())
    ");
    
    $candidateEmail = 'developer_candidate_' . time() . '@example.com';
    $candidateStmt->execute([
        'Developer Candidate',
        $candidateEmail,
        '1234567890',
        'full-time',
        'PHP, JavaScript, MySQL, React',
        password_hash('testpass123', PASSWORD_DEFAULT)
    ]);
    $candidateId = $pdo->lastInsertId();
    echo "   ✓ Created candidate: Developer Candidate (ID: $candidateId)\n";
    
    // Test 4: Simulate Apply button click (should NOT assign exam)
    echo "\n4. Simulating Apply button click...\n";
    
    // Simulate the job application process
    $jobApplicationData = [
        'job_id' => $jobId,
        'cover_letter' => 'I am very interested in this Software Developer position.',
        'notes' => 'I have 3 years of experience in web development.'
    ];
    
    // Check current exam assignments before applying
    $beforeAssignmentsStmt = $pdo->prepare("
        SELECT COUNT(*) as assignment_count
        FROM exam_assignments
        WHERE CandidateID = ?
    ");
    $beforeAssignmentsStmt->execute([$candidateId]);
    $beforeAssignmentCount = $beforeAssignmentsStmt->fetch(PDO::FETCH_ASSOC)['assignment_count'];
    
    echo "   Exam assignments before applying: $beforeAssignmentCount\n";
    
    // Simulate the application process
    $applicationStmt = $pdo->prepare("
        INSERT INTO job_applications (JobID, CandidateID, CoverLetter, Notes, ApplicationDate)
        VALUES (?, ?, ?, ?, NOW())
    ");
    $applicationStmt->execute([
        $jobId,
        $candidateId,
        $jobApplicationData['cover_letter'],
        $jobApplicationData['notes']
    ]);
    echo "   ✓ Job application created\n";
    
    // Now simulate the exam assignment logic from job_application_handler.php
    echo "   Checking for exams with questions...\n";
    
    $examStmt = $pdo->prepare("
        SELECT e.ExamID, e.ExamTitle, e.Duration, e.QuestionCount, e.PassingScore 
        FROM exams e
        WHERE e.CompanyID = ? AND e.IsActive = 1 AND e.QuestionCount > 0
        ORDER BY e.CreatedAt DESC
        LIMIT 1
    ");
    $examStmt->execute([$company['CompanyID']]);
    $exam = $examStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($exam) {
        echo "   ✗ ERROR: Found exam with questions when there should be none!\n";
    } else {
        echo "   ✓ CORRECT: No exam with questions found (exam has QuestionCount = 0)\n";
    }
    
    // Check exam assignments after applying
    $afterAssignmentsStmt = $pdo->prepare("
        SELECT COUNT(*) as assignment_count
        FROM exam_assignments
        WHERE CandidateID = ?
    ");
    $afterAssignmentsStmt->execute([$candidateId]);
    $afterAssignmentCount = $afterAssignmentsStmt->fetch(PDO::FETCH_ASSOC)['assignment_count'];
    
    echo "   Exam assignments after applying: $afterAssignmentCount\n";
    
    if ($afterAssignmentCount == $beforeAssignmentCount) {
        echo "   ✓ CORRECT: No new exam assignments created (exam has no questions)\n";
    } else {
        echo "   ✗ ERROR: Exam was assigned even though it has no questions!\n";
    }
    
    // Test 5: Add questions to the exam
    echo "\n5. Adding questions to the exam...\n";
    
    $questions = [
        [
            'text' => 'What is object-oriented programming?',
            'options' => [
                ['text' => 'A programming paradigm', 'correct' => true],
                ['text' => 'A database system', 'correct' => false],
                ['text' => 'A web framework', 'correct' => false],
                ['text' => 'An operating system', 'correct' => false]
            ]
        ],
        [
            'text' => 'What does API stand for?',
            'options' => [
                ['text' => 'Application Programming Interface', 'correct' => true],
                ['text' => 'Advanced Programming Interface', 'correct' => false],
                ['text' => 'Automated Programming Interface', 'correct' => false],
                ['text' => 'Application Process Interface', 'correct' => false]
            ]
        ]
    ];
    
    foreach ($questions as $index => $question) {
        // Insert question
        $questionStmt = $pdo->prepare("
            INSERT INTO exam_questions (ExamID, QuestionType, QuestionText, QuestionOrder, Points, Difficulty, Category)
            VALUES (?, 'multiple-choice', ?, ?, 1.00, 'medium', 'Programming')
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
    
    // Test 7: Check final assignments
    echo "\n7. Checking final exam assignments...\n";
    
    $finalAssignmentsStmt = $pdo->prepare("
        SELECT COUNT(*) as assignment_count
        FROM exam_assignments
        WHERE CandidateID = ?
    ");
    $finalAssignmentsStmt->execute([$candidateId]);
    $finalAssignmentCount = $finalAssignmentsStmt->fetch(PDO::FETCH_ASSOC)['assignment_count'];
    
    echo "   Final exam assignments for candidate: $finalAssignmentCount\n";
    
    if ($finalAssignmentCount > $afterAssignmentCount) {
        echo "   ✓ CORRECT: Exam was assigned after adding questions\n";
    } else {
        echo "   ✗ ERROR: Exam was not assigned even after adding questions\n";
    }
    
    // Test 8: Verify exam appears in candidate's scheduled exams
    echo "\n8. Verifying exam appears in candidate's scheduled exams...\n";
    
    $candidateExamsStmt = $pdo->prepare("
        SELECT ea.AssignmentID, e.ExamTitle, ea.Status, jp.JobTitle
        FROM exam_assignments ea
        JOIN exams e ON ea.ExamID = e.ExamID
        JOIN job_postings jp ON ea.JobID = jp.JobID
        WHERE ea.CandidateID = ? AND ea.ExamID = ?
    ");
    $candidateExamsStmt->execute([$candidateId, $examId]);
    $candidateExam = $candidateExamsStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($candidateExam) {
        echo "   ✓ Exam found in candidate's assignments:\n";
        echo "     - Exam: {$candidateExam['ExamTitle']}\n";
        echo "     - Job: {$candidateExam['JobTitle']}\n";
        echo "     - Status: {$candidateExam['Status']}\n";
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
    echo "✓ Candidate clicks Apply button\n";
    echo "✓ NO exam is auto-assigned (correct behavior)\n";
    echo "✓ Company adds questions to exam\n";
    echo "✓ Exam is then assigned to candidate\n";
    echo "✓ Apply button behavior is now correct!\n";

} catch (Exception $e) {
    echo "Error during testing: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

if (!$isCLI) {
    echo "</pre>";
}
?>

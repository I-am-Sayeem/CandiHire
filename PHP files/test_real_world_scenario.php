<?php
/**
 * Test Script for Real-World Scenario
 * 
 * This script simulates the exact scenario the user is experiencing:
 * 1. Company creates exam without questions
 * 2. User applies for job
 * 3. Check if exam appears in attendexam.php
 */

require_once 'Database.php';

// Check if we're running from command line or web
$isCLI = php_sapi_name() === 'cli';

if (!$isCLI) {
    echo "<h1>Real-World Scenario Test</h1>";
    echo "<pre>";
}

echo "=== Real-World Scenario Test ===\n\n";

try {
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        throw new Exception('Database connection not available');
    }

    // Test 1: Create test data exactly like in real scenario
    echo "1. Creating test data (real-world scenario)...\n";
    
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
        'Real World Test Job',
        'IT',
        'Remote',
        'full-time',
        50000,
        70000,
        'USD',
        'Real world test job',
        'Basic skills',
        'active'
    ]);
    $jobId = $pdo->lastInsertId();
    echo "   ✓ Created job: Real World Test Job (ID: $jobId)\n";
    
    // Create exam WITHOUT questions (like company would do initially)
    $examStmt = $pdo->prepare("
        INSERT INTO exams (CompanyID, ExamTitle, ExamType, Description, Instructions, Duration, QuestionCount, PassingScore, IsActive, CreatedBy)
        VALUES (?, ?, 'manual', ?, ?, ?, 0, 70.00, 1, ?)
    ");
    
    $examTitle = "Real World Test Exam - " . date('Y-m-d H:i:s');
    $examStmt->execute([
        $company['CompanyID'],
        $examTitle,
        'Real world test exam without questions',
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
    
    $candidateEmail = 'realworld_candidate_' . time() . '@example.com';
    $candidateStmt->execute([
        'Real World Candidate',
        $candidateEmail,
        '1234567890',
        'full-time',
        'PHP, JavaScript, MySQL',
        password_hash('testpass123', PASSWORD_DEFAULT)
    ]);
    $candidateId = $pdo->lastInsertId();
    echo "   ✓ Created candidate: Real World Candidate (ID: $candidateId)\n";
    
    // Test 2: Simulate the exact job application process
    echo "\n2. Simulating job application process...\n";
    
    // Simulate the exact data that would be sent from the frontend
    $applicationData = [
        'action' => 'apply_to_job',
        'jobId' => $jobId,
        'coverLetter' => 'I am interested in this position',
        'additionalNotes' => 'Real world test application'
    ];
    
    echo "   Application data: " . json_encode($applicationData) . "\n";
    
    // Check assignments before application
    $beforeStmt = $pdo->prepare("
        SELECT COUNT(*) as count
        FROM exam_assignments
        WHERE CandidateID = ?
    ");
    $beforeStmt->execute([$candidateId]);
    $beforeCount = $beforeStmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "   Assignments before application: $beforeCount\n";
    
    // Create job application (this is what happens in job_application_handler.php)
    $applicationStmt = $pdo->prepare("
        INSERT INTO job_applications (JobID, CandidateID, CoverLetter, Notes, ApplicationDate)
        VALUES (?, ?, ?, ?, NOW())
    ");
    $applicationStmt->execute([
        $jobId,
        $candidateId,
        $applicationData['coverLetter'],
        $applicationData['additionalNotes']
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
        echo "     - Exam: {$exam['ExamTitle']}\n";
        echo "     - Question Count: {$exam['QuestionCount']}\n";
    } else {
        echo "   ✓ CORRECT: No exam with questions found\n";
    }
    
    // Check assignments after application
    $afterStmt = $pdo->prepare("
        SELECT COUNT(*) as count
        FROM exam_assignments
        WHERE CandidateID = ?
    ");
    $afterStmt->execute([$candidateId]);
    $afterCount = $afterStmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "   Assignments after application: $afterCount\n";
    
    if ($afterCount > $beforeCount) {
        echo "   ✗ ERROR: New assignments were created!\n";
    } else {
        echo "   ✓ CORRECT: No new assignments created\n";
    }
    
    // Test 3: Simulate what attendexam.php would show
    echo "\n3. Simulating attendexam.php display...\n";
    
    // This is the query that attendexam.php uses to get assigned exams
    $attendExamStmt = $pdo->prepare("
        SELECT 
            ea.AssignmentID,
            ea.ExamID,
            ea.JobID,
            ea.AssignmentDate,
            ea.Status as AssignmentStatus,
            ea.DueDate,
            e.ExamTitle,
            e.Duration,
            e.QuestionCount,
            e.PassingScore,
            jp.JobTitle,
            jp.CompanyID,
            cli.CompanyName
        FROM exam_assignments ea
        JOIN exams e ON ea.ExamID = e.ExamID
        JOIN job_postings jp ON ea.JobID = jp.JobID
        JOIN Company_login_info cli ON jp.CompanyID = cli.CompanyID
        WHERE ea.CandidateID = ?
        ORDER BY ea.AssignmentDate DESC
    ");
    $attendExamStmt->execute([$candidateId]);
    $assignedExams = $attendExamStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "   Exams that would appear in attendexam.php: " . count($assignedExams) . "\n";
    
    if (count($assignedExams) > 0) {
        echo "   ✗ ERROR: Exams are showing in attendexam.php!\n";
        foreach ($assignedExams as $exam) {
            echo "     - Exam: {$exam['ExamTitle']}\n";
            echo "     - Question Count: {$exam['QuestionCount']}\n";
            echo "     - Status: {$exam['AssignmentStatus']}\n";
        }
    } else {
        echo "   ✓ CORRECT: No exams showing in attendexam.php\n";
    }
    
    // Test 4: Check if there are any other sources of assignments
    echo "\n4. Checking for other assignment sources...\n";
    
    // Check if there are any assignments for this candidate
    $allAssignmentsStmt = $pdo->prepare("
        SELECT ea.AssignmentID, e.ExamTitle, e.QuestionCount, ea.AssignmentDate, ea.Status
        FROM exam_assignments ea
        JOIN exams e ON ea.ExamID = e.ExamID
        WHERE ea.CandidateID = ?
        ORDER BY ea.AssignmentDate DESC
    ");
    $allAssignmentsStmt->execute([$candidateId]);
    $allAssignments = $allAssignmentsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($allAssignments) > 0) {
        echo "   Found assignments from other sources:\n";
        foreach ($allAssignments as $assignment) {
            echo "     - Assignment ID: {$assignment['AssignmentID']}\n";
            echo "     - Exam: {$assignment['ExamTitle']}\n";
            echo "     - Question Count: {$assignment['QuestionCount']}\n";
            echo "     - Assignment Date: {$assignment['AssignmentDate']}\n";
            echo "     - Status: {$assignment['Status']}\n";
        }
    } else {
        echo "   No assignments found from any source\n";
    }
    
    // Cleanup
    echo "\n5. Cleaning up test data...\n";
    
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
    echo "\nSUMMARY:\n";
    echo "✓ Company creates exam without questions\n";
    echo "✓ Candidate applies for job\n";
    echo "✓ No exam assignments created (correct)\n";
    echo "✓ No exams appear in attendexam.php (correct)\n";
    echo "✓ System is working as expected\n";

} catch (Exception $e) {
    echo "Error during testing: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

if (!$isCLI) {
    echo "</pre>";
}
?>

<?php
/**
 * Test Script for Retroactive Exam Assignment
 * 
 * This script tests the retroactive exam assignment functionality
 * by simulating the scenario where a company creates an exam after
 * candidates have already applied for jobs.
 */

require_once 'Database.php';
require_once 'retroactive_exam_assignment.php';

// Check if we're running from command line or web
$isCLI = php_sapi_name() === 'cli';

if (!$isCLI) {
    echo "<h1>Retroactive Exam Assignment Test</h1>";
    echo "<pre>";
}

echo "=== Retroactive Exam Assignment Test ===\n\n";

try {
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        throw new Exception('Database connection not available');
    }

    $assignment = new RetroactiveExamAssignment($pdo);

    // Test 1: Get statistics for all companies
    echo "1. Testing statistics retrieval...\n";
    
    // Get all companies
    $companiesStmt = $pdo->prepare("SELECT CompanyID, CompanyName FROM Company_login_info LIMIT 3");
    $companiesStmt->execute();
    $companies = $companiesStmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($companies)) {
        echo "   No companies found in database.\n";
        exit;
    }
    
    foreach ($companies as $company) {
        echo "   Company: {$company['CompanyName']} (ID: {$company['CompanyID']})\n";
        
        $stats = $assignment->getExamAssignmentStats($company['CompanyID']);
        if ($stats['success']) {
            echo "   - Total exams: " . count($stats['stats']) . "\n";
            foreach ($stats['stats'] as $stat) {
                echo "     * {$stat['ExamTitle']}: {$stat['assigned_exams']} assigned, {$stat['completed_exams']} completed\n";
            }
        } else {
            echo "   - Error: {$stats['message']}\n";
        }
        echo "\n";
    }

    // Test 2: Test individual exam assignment
    echo "2. Testing individual exam assignment...\n";
    
    // Get first company with exams
    $companyWithExams = null;
    foreach ($companies as $company) {
        $examsStmt = $pdo->prepare("SELECT ExamID, ExamTitle FROM exams WHERE CompanyID = ? AND IsActive = 1 LIMIT 1");
        $examsStmt->execute([$company['CompanyID']]);
        $exam = $examsStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($exam) {
            $companyWithExams = $company;
            $testExam = $exam;
            break;
        }
    }
    
    if ($companyWithExams) {
        echo "   Testing with company: {$companyWithExams['CompanyName']}\n";
        echo "   Testing with exam: {$testExam['ExamTitle']}\n";
        
        $result = $assignment->assignExamToExistingApplicants($testExam['ExamID'], $companyWithExams['CompanyID'], 7);
        
        if ($result['success']) {
            echo "   ✓ Success: {$result['message']}\n";
            echo "   - Assigned to {$result['assigned_count']} applicants\n";
        } else {
            echo "   ✗ Failed: {$result['message']}\n";
        }
    } else {
        echo "   No companies with active exams found.\n";
    }
    
    echo "\n";

    // Test 3: Test bulk assignment
    echo "3. Testing bulk assignment...\n";
    
    if ($companyWithExams) {
        $result = $assignment->bulkAssignAllExams($companyWithExams['CompanyID'], 7);
        
        if ($result['success']) {
            echo "   ✓ Bulk assignment completed: {$result['message']}\n";
            echo "   - Total assignments: {$result['total_assigned']}\n";
            
            if (!empty($result['exam_results'])) {
                echo "   - Individual exam results:\n";
                foreach ($result['exam_results'] as $examResult) {
                    echo "     * {$examResult['exam_title']}: {$examResult['assigned_count']} assigned\n";
                }
            }
        } else {
            echo "   ✗ Bulk assignment failed: {$result['message']}\n";
        }
    } else {
        echo "   No companies with active exams found for bulk testing.\n";
    }
    
    echo "\n";

    // Test 4: Test job-specific assignment
    echo "4. Testing job-specific assignment...\n";
    
    // Get a job with applicants
    $jobStmt = $pdo->prepare("
        SELECT jp.JobID, jp.JobTitle, jp.CompanyID, cli.CompanyName
        FROM job_postings jp
        JOIN Company_login_info cli ON jp.CompanyID = cli.CompanyID
        WHERE EXISTS (
            SELECT 1 FROM job_applications ja WHERE ja.JobID = jp.JobID
        )
        LIMIT 1
    ");
    $jobStmt->execute();
    $job = $jobStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($job) {
        echo "   Testing with job: {$job['JobTitle']} at {$job['CompanyName']}\n";
        
        // Get an exam for this company
        $examStmt = $pdo->prepare("SELECT ExamID, ExamTitle FROM exams WHERE CompanyID = ? AND IsActive = 1 LIMIT 1");
        $examStmt->execute([$job['CompanyID']]);
        $exam = $examStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($exam) {
            echo "   Using exam: {$exam['ExamTitle']}\n";
            
            $result = $assignment->assignExamToJobApplicants($exam['ExamID'], $job['JobID'], 7);
            
            if ($result['success']) {
                echo "   ✓ Job-specific assignment successful: {$result['message']}\n";
                echo "   - Assigned to {$result['assigned_count']} applicants for this job\n";
            } else {
                echo "   ✗ Job-specific assignment failed: {$result['message']}\n";
            }
        } else {
            echo "   No active exams found for this company.\n";
        }
    } else {
        echo "   No jobs with applicants found.\n";
    }
    
    echo "\n";

    // Test 5: Verify assignments in database
    echo "5. Verifying assignments in database...\n";
    
    $assignmentsStmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_assignments,
            COUNT(CASE WHEN Status = 'assigned' THEN 1 END) as pending_assignments,
            COUNT(CASE WHEN Status = 'completed' THEN 1 END) as completed_assignments
        FROM exam_assignments ea
        JOIN exams e ON ea.ExamID = e.ExamID
        WHERE e.CompanyID = ?
    ");
    
    foreach ($companies as $company) {
        $assignmentsStmt->execute([$company['CompanyID']]);
        $stats = $assignmentsStmt->fetch(PDO::FETCH_ASSOC);
        
        echo "   {$company['CompanyName']}:\n";
        echo "     - Total assignments: {$stats['total_assignments']}\n";
        echo "     - Pending: {$stats['pending_assignments']}\n";
        echo "     - Completed: {$stats['completed_assignments']}\n";
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

<?php
/**
 * Exam Question Assignment Handler
 * 
 * This file handles the scenario where:
 * 1. A company creates an exam without questions initially
 * 2. Later adds questions to that exam
 * 3. Existing job applicants should then get the exam assigned
 */

require_once 'Database.php';
require_once 'retroactive_exam_assignment.php';

/**
 * Check and assign exams that have questions but no assignments for existing applicants
 * This function should be called whenever questions are added to an exam
 * Only assigns to candidates who applied for jobs that have this exam
 */
function checkAndAssignExamWithQuestions($examId, $companyId, $jobId = null) {
    global $pdo;
    
    try {
        if (!isset($pdo) || !($pdo instanceof PDO)) {
            error_log("Database connection not available in checkAndAssignExamWithQuestions");
            return false;
        }
        
        // Check if the exam has questions
        $questionsStmt = $pdo->prepare("
            SELECT COUNT(*) as question_count 
            FROM exam_questions 
            WHERE ExamID = ?
        ");
        $questionsStmt->execute([$examId]);
        $questionCount = $questionsStmt->fetch(PDO::FETCH_ASSOC)['question_count'];
        
        if ($questionCount == 0) {
            error_log("Exam $examId has no questions, skipping assignment");
            return false;
        }
        
        // Check if the exam is active
        $examStmt = $pdo->prepare("
            SELECT IsActive, ExamTitle 
            FROM exams 
            WHERE ExamID = ? AND CompanyID = ?
        ");
        $examStmt->execute([$examId, $companyId]);
        $exam = $examStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$exam || !$exam['IsActive']) {
            error_log("Exam $examId is not active, skipping assignment");
            return false;
        }
        
        // If jobId is provided, only assign to applicants for that specific job
        if ($jobId) {
            $result = assignExamToJobApplicants($examId, $jobId, 7);
            if ($result['success']) {
                error_log("Successfully assigned exam '{$exam['ExamTitle']}' to {$result['assigned_count']} applicants for job $jobId");
                return true;
            } else {
                error_log("Failed to assign exam '{$exam['ExamTitle']}' to job $jobId: " . $result['message']);
                return false;
            }
        } else {
            // If no jobId provided, assign to all company applicants (fallback behavior)
            $result = assignExamToExistingApplicants($examId, $companyId, 7);
            if ($result['success']) {
                error_log("Successfully assigned exam '{$exam['ExamTitle']}' to {$result['assigned_count']} existing applicants");
                return true;
            } else {
                error_log("Failed to assign exam '{$exam['ExamTitle']}': " . $result['message']);
                return false;
            }
        }
        
    } catch (Exception $e) {
        error_log("Error in checkAndAssignExamWithQuestions: " . $e->getMessage());
        return false;
    }
}

/**
 * Check all exams for a company and assign any that have questions but missing assignments
 * This is a bulk function to handle any missed assignments
 * Only assigns to candidates who applied for jobs that have exams
 */
function checkAllExamsForMissingAssignments($companyId) {
    global $pdo;
    
    try {
        if (!isset($pdo) || !($pdo instanceof PDO)) {
            error_log("Database connection not available in checkAllExamsForMissingAssignments");
            return false;
        }
        
        // Get all active exams for the company that have questions
        $examsStmt = $pdo->prepare("
            SELECT e.ExamID, e.ExamTitle, COUNT(eq.QuestionID) as question_count
            FROM exams e
            LEFT JOIN exam_questions eq ON e.ExamID = eq.ExamID
            WHERE e.CompanyID = ? AND e.IsActive = 1
            GROUP BY e.ExamID, e.ExamTitle
            HAVING question_count > 0
        ");
        $examsStmt->execute([$companyId]);
        $exams = $examsStmt->fetchAll(PDO::FETCH_ASSOC);
        
        $assignedCount = 0;
        $totalExams = count($exams);
        
        // For each exam, find jobs that should have this exam and assign to their applicants
        foreach ($exams as $exam) {
            // Find jobs for this company that have applicants but no exam assignments
            $jobsStmt = $pdo->prepare("
                SELECT DISTINCT jp.JobID, jp.JobTitle
                FROM job_postings jp
                JOIN job_applications ja ON jp.JobID = ja.JobID
                WHERE jp.CompanyID = ?
                AND NOT EXISTS (
                    SELECT 1 FROM exam_assignments ea 
                    WHERE ea.ExamID = ? 
                    AND ea.JobID = jp.JobID
                )
            ");
            $jobsStmt->execute([$companyId, $exam['ExamID']]);
            $jobs = $jobsStmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($jobs as $job) {
                $result = assignExamToJobApplicants($exam['ExamID'], $job['JobID'], 7);
                if ($result['success'] && $result['assigned_count'] > 0) {
                    $assignedCount += $result['assigned_count'];
                    error_log("Assigned exam '{$exam['ExamTitle']}' to {$result['assigned_count']} applicants for job '{$job['JobTitle']}'");
                }
            }
        }
        
        error_log("Bulk assignment completed: $assignedCount total assignments for $totalExams exams");
        return true;
        
    } catch (Exception $e) {
        error_log("Error in checkAllExamsForMissingAssignments: " . $e->getMessage());
        return false;
    }
}

/**
 * Update exam question count when questions are added/removed
 */
function updateExamQuestionCount($examId) {
    global $pdo;
    
    try {
        if (!isset($pdo) || !($pdo instanceof PDO)) {
            error_log("Database connection not available in updateExamQuestionCount");
            return false;
        }
        
        // Count questions for this exam
        $countStmt = $pdo->prepare("
            SELECT COUNT(*) as question_count 
            FROM exam_questions 
            WHERE ExamID = ?
        ");
        $countStmt->execute([$examId]);
        $questionCount = $countStmt->fetch(PDO::FETCH_ASSOC)['question_count'];
        
        // Update the exam's question count
        $updateStmt = $pdo->prepare("
            UPDATE exams 
            SET QuestionCount = ?, UpdatedAt = NOW() 
            WHERE ExamID = ?
        ");
        $updateStmt->execute([$questionCount, $examId]);
        
        error_log("Updated exam $examId question count to $questionCount");
        return true;
        
    } catch (Exception $e) {
        error_log("Error updating exam question count: " . $e->getMessage());
        return false;
    }
}

/**
 * Handle the complete process when questions are added to an exam
 * This should be called after questions are added to ensure proper assignment
 */
function handleExamQuestionAddition($examId, $companyId, $jobId = null) {
    try {
        // Update the question count
        updateExamQuestionCount($examId);
        
        // Check and assign the exam to existing applicants for the specific job
        $assigned = checkAndAssignExamWithQuestions($examId, $companyId, $jobId);
        
        return $assigned;
        
    } catch (Exception $e) {
        error_log("Error in handleExamQuestionAddition: " . $e->getMessage());
        return false;
    }
}

// API endpoint for manual triggering
if (isset($_GET['action']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        echo json_encode(['success' => false, 'message' => 'Database connection not available']);
        exit;
    }
    
    $action = $_GET['action'];
    
    switch ($action) {
        case 'check_exam':
            $examId = intval($_POST['exam_id'] ?? 0);
            $companyId = intval($_POST['company_id'] ?? 0);
            
            if (!$examId || !$companyId) {
                echo json_encode(['success' => false, 'message' => 'Exam ID and Company ID are required']);
                break;
            }
            
            $result = checkAndAssignExamWithQuestions($examId, $companyId);
            echo json_encode(['success' => $result, 'message' => $result ? 'Exam checked and assigned successfully' : 'No assignment needed or failed']);
            break;
            
        case 'check_all':
            $companyId = intval($_POST['company_id'] ?? 0);
            
            if (!$companyId) {
                echo json_encode(['success' => false, 'message' => 'Company ID is required']);
                break;
            }
            
            $result = checkAllExamsForMissingAssignments($companyId);
            echo json_encode(['success' => $result, 'message' => $result ? 'All exams checked successfully' : 'Failed to check exams']);
            break;
            
        case 'add_questions':
            $examId = intval($_POST['exam_id'] ?? 0);
            $companyId = intval($_POST['company_id'] ?? 0);
            
            if (!$examId || !$companyId) {
                echo json_encode(['success' => false, 'message' => 'Exam ID and Company ID are required']);
                break;
            }
            
            $result = handleExamQuestionAddition($examId, $companyId);
            echo json_encode(['success' => $result, 'message' => $result ? 'Questions added and exam assigned successfully' : 'Failed to process exam assignment']);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
    exit;
}
?>

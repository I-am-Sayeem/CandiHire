<?php
/**
 * Retroactive Exam Assignment System
 * 
 * This file handles assigning exams to existing job applicants when:
 * 1. A company creates a new exam after candidates have already applied
 * 2. A company activates an exam that was previously inactive
 * 3. Manual bulk assignment of exams to existing applicants
 */

require_once 'Database.php';

class RetroactiveExamAssignment {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Assign an exam to all existing applicants for a specific company
     * 
     * @param int $examId The exam to assign
     * @param int $companyId The company that owns the exam
     * @param int $dueDateDays Number of days from now to set as due date (default: 7)
     * @return array Result with success status and details
     */
    public function assignExamToExistingApplicants($examId, $companyId, $dueDateDays = 7) {
        try {
            // Validate exam exists and belongs to company
            $examStmt = $this->pdo->prepare("
                SELECT ExamID, ExamTitle, Duration, QuestionCount, PassingScore, IsActive
                FROM exams 
                WHERE ExamID = ? AND CompanyID = ? AND IsActive = 1
            ");
            $examStmt->execute([$examId, $companyId]);
            $exam = $examStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$exam) {
                return [
                    'success' => false,
                    'message' => 'Exam not found or not active',
                    'assigned_count' => 0
                ];
            }
            
            // Get all job postings for this company
            $jobsStmt = $this->pdo->prepare("
                SELECT JobID, JobTitle, Department
                FROM job_postings 
                WHERE CompanyID = ?
            ");
            $jobsStmt->execute([$companyId]);
            $jobs = $jobsStmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($jobs)) {
                return [
                    'success' => true,
                    'message' => 'No job postings found for this company',
                    'assigned_count' => 0
                ];
            }
            
            $jobIds = array_column($jobs, 'JobID');
            $placeholders = str_repeat('?,', count($jobIds) - 1) . '?';
            
            // Get all existing applicants for this company's jobs who don't already have this exam assigned
            $applicantsStmt = $this->pdo->prepare("
                SELECT DISTINCT ja.CandidateID, ja.JobID, cli.FullName, cli.Email
                FROM job_applications ja
                JOIN candidate_login_info cli ON ja.CandidateID = cli.CandidateID
                WHERE ja.JobID IN ($placeholders)
                AND NOT EXISTS (
                    SELECT 1 FROM exam_assignments ea 
                    WHERE ea.ExamID = ? 
                    AND ea.CandidateID = ja.CandidateID 
                    AND ea.JobID = ja.JobID
                )
            ");
            
            $params = array_merge($jobIds, [$examId]);
            $applicantsStmt->execute($params);
            $applicants = $applicantsStmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($applicants)) {
                return [
                    'success' => true,
                    'message' => 'No new applicants found to assign exam to',
                    'assigned_count' => 0
                ];
            }
            
            // Calculate due date
            $dueDate = date('Y-m-d', strtotime("+$dueDateDays days"));
            
            // Start transaction
            $this->pdo->beginTransaction();
            
            $assignedCount = 0;
            $errors = [];
            
            foreach ($applicants as $applicant) {
                try {
                    // Insert exam assignment
                    $assignmentStmt = $this->pdo->prepare("
                        INSERT INTO exam_assignments (ExamID, CandidateID, JobID, DueDate, Status, AssignmentDate)
                        VALUES (?, ?, ?, ?, 'assigned', NOW())
                    ");
                    
                    $assignmentStmt->execute([
                        $examId,
                        $applicant['CandidateID'],
                        $applicant['JobID'],
                        $dueDate
                    ]);
                    
                    $assignedCount++;
                    
                    // Log the assignment
                    error_log("Retroactive exam assignment: Exam {$examId} assigned to candidate {$applicant['CandidateID']} for job {$applicant['JobID']}");
                    
                } catch (Exception $e) {
                    $errors[] = "Failed to assign exam to candidate {$applicant['FullName']} (ID: {$applicant['CandidateID']}): " . $e->getMessage();
                    error_log("Error assigning exam to candidate {$applicant['CandidateID']}: " . $e->getMessage());
                }
            }
            
            // Commit transaction
            $this->pdo->commit();
            
            $result = [
                'success' => true,
                'message' => "Exam '{$exam['ExamTitle']}' assigned to $assignedCount existing applicants",
                'assigned_count' => $assignedCount,
                'exam_title' => $exam['ExamTitle'],
                'total_applicants' => count($applicants)
            ];
            
            if (!empty($errors)) {
                $result['warnings'] = $errors;
            }
            
            return $result;
            
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            
            error_log("Error in retroactive exam assignment: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to assign exam to existing applicants: ' . $e->getMessage(),
                'assigned_count' => 0
            ];
        }
    }
    
    /**
     * Assign an exam to all existing applicants for a specific job
     * 
     * @param int $examId The exam to assign
     * @param int $jobId The specific job to assign the exam for
     * @param int $dueDateDays Number of days from now to set as due date (default: 7)
     * @return array Result with success status and details
     */
    public function assignExamToJobApplicants($examId, $jobId, $dueDateDays = 7) {
        try {
            // Validate exam exists and is active
            $examStmt = $this->pdo->prepare("
                SELECT ExamID, ExamTitle, Duration, QuestionCount, PassingScore, IsActive
                FROM exams 
                WHERE ExamID = ? AND IsActive = 1
            ");
            $examStmt->execute([$examId]);
            $exam = $examStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$exam) {
                return [
                    'success' => false,
                    'message' => 'Exam not found or not active',
                    'assigned_count' => 0
                ];
            }
            
            // Get job details
            $jobStmt = $this->pdo->prepare("
                SELECT JobID, JobTitle, CompanyID, Department
                FROM job_postings 
                WHERE JobID = ?
            ");
            $jobStmt->execute([$jobId]);
            $job = $jobStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$job) {
                return [
                    'success' => false,
                    'message' => 'Job not found',
                    'assigned_count' => 0
                ];
            }
            
            // Get all existing applicants for this job who don't already have this exam assigned
            $applicantsStmt = $this->pdo->prepare("
                SELECT DISTINCT ja.CandidateID, cli.FullName, cli.Email
                FROM job_applications ja
                JOIN candidate_login_info cli ON ja.CandidateID = cli.CandidateID
                WHERE ja.JobID = ?
                AND NOT EXISTS (
                    SELECT 1 FROM exam_assignments ea 
                    WHERE ea.ExamID = ? 
                    AND ea.CandidateID = ja.CandidateID 
                    AND ea.JobID = ja.JobID
                )
            ");
            
            $applicantsStmt->execute([$jobId, $examId]);
            $applicants = $applicantsStmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($applicants)) {
                return [
                    'success' => true,
                    'message' => 'No new applicants found to assign exam to',
                    'assigned_count' => 0
                ];
            }
            
            // Calculate due date
            $dueDate = date('Y-m-d', strtotime("+$dueDateDays days"));
            
            // Start transaction
            $this->pdo->beginTransaction();
            
            $assignedCount = 0;
            $errors = [];
            
            foreach ($applicants as $applicant) {
                try {
                    // Insert exam assignment
                    $assignmentStmt = $this->pdo->prepare("
                        INSERT INTO exam_assignments (ExamID, CandidateID, JobID, DueDate, Status, AssignmentDate)
                        VALUES (?, ?, ?, ?, 'assigned', NOW())
                    ");
                    
                    $assignmentStmt->execute([
                        $examId,
                        $applicant['CandidateID'],
                        $jobId,
                        $dueDate
                    ]);
                    
                    $assignedCount++;
                    
                    // Log the assignment
                    error_log("Retroactive exam assignment: Exam {$examId} assigned to candidate {$applicant['CandidateID']} for job {$jobId}");
                    
                } catch (Exception $e) {
                    $errors[] = "Failed to assign exam to candidate {$applicant['FullName']} (ID: {$applicant['CandidateID']}): " . $e->getMessage();
                    error_log("Error assigning exam to candidate {$applicant['CandidateID']}: " . $e->getMessage());
                }
            }
            
            // Commit transaction
            $this->pdo->commit();
            
            $result = [
                'success' => true,
                'message' => "Exam '{$exam['ExamTitle']}' assigned to $assignedCount applicants for job '{$job['JobTitle']}'",
                'assigned_count' => $assignedCount,
                'exam_title' => $exam['ExamTitle'],
                'job_title' => $job['JobTitle'],
                'total_applicants' => count($applicants)
            ];
            
            if (!empty($errors)) {
                $result['warnings'] = $errors;
            }
            
            return $result;
            
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            
            error_log("Error in retroactive exam assignment for job: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to assign exam to job applicants: ' . $e->getMessage(),
                'assigned_count' => 0
            ];
        }
    }
    
    /**
     * Get statistics about exam assignments for a company
     * 
     * @param int $companyId The company ID
     * @return array Statistics about exam assignments
     */
    public function getExamAssignmentStats($companyId) {
        try {
            $statsStmt = $this->pdo->prepare("
                SELECT 
                    e.ExamID,
                    e.ExamTitle,
                    e.IsActive,
                    COUNT(DISTINCT jp.JobID) as total_jobs,
                    COUNT(DISTINCT ja.CandidateID) as total_applicants,
                    COUNT(DISTINCT ea.AssignmentID) as assigned_exams,
                    COUNT(DISTINCT CASE WHEN ea.Status = 'completed' THEN ea.AssignmentID END) as completed_exams,
                    COUNT(DISTINCT CASE WHEN ea.Status = 'assigned' THEN ea.AssignmentID END) as pending_exams
                FROM exams e
                LEFT JOIN job_postings jp ON e.CompanyID = jp.CompanyID
                LEFT JOIN job_applications ja ON jp.JobID = ja.JobID
                LEFT JOIN exam_assignments ea ON e.ExamID = ea.ExamID AND ja.CandidateID = ea.CandidateID AND ja.JobID = ea.JobID
                WHERE e.CompanyID = ?
                GROUP BY e.ExamID, e.ExamTitle, e.IsActive
                ORDER BY e.CreatedAt DESC
            ");
            
            $statsStmt->execute([$companyId]);
            $stats = $statsStmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'success' => true,
                'stats' => $stats
            ];
            
        } catch (Exception $e) {
            error_log("Error getting exam assignment stats: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to get exam assignment statistics: ' . $e->getMessage(),
                'stats' => []
            ];
        }
    }
    
    /**
     * Bulk assign all active exams to existing applicants for a company
     * 
     * @param int $companyId The company ID
     * @param int $dueDateDays Number of days from now to set as due date (default: 7)
     * @return array Result with success status and details
     */
    public function bulkAssignAllExams($companyId, $dueDateDays = 7) {
        try {
            // Get all active exams for the company
            $examsStmt = $this->pdo->prepare("
                SELECT ExamID, ExamTitle
                FROM exams 
                WHERE CompanyID = ? AND IsActive = 1
                ORDER BY CreatedAt DESC
            ");
            $examsStmt->execute([$companyId]);
            $exams = $examsStmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($exams)) {
                return [
                    'success' => true,
                    'message' => 'No active exams found for this company',
                    'total_assigned' => 0,
                    'exam_results' => []
                ];
            }
            
            $totalAssigned = 0;
            $examResults = [];
            
            foreach ($exams as $exam) {
                $result = $this->assignExamToExistingApplicants($exam['ExamID'], $companyId, $dueDateDays);
                $examResults[] = [
                    'exam_id' => $exam['ExamID'],
                    'exam_title' => $exam['ExamTitle'],
                    'assigned_count' => $result['assigned_count'],
                    'success' => $result['success'],
                    'message' => $result['message']
                ];
                
                if ($result['success']) {
                    $totalAssigned += $result['assigned_count'];
                }
            }
            
            return [
                'success' => true,
                'message' => "Bulk assignment completed. Total assignments: $totalAssigned",
                'total_assigned' => $totalAssigned,
                'exam_results' => $examResults
            ];
            
        } catch (Exception $e) {
            error_log("Error in bulk exam assignment: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to perform bulk exam assignment: ' . $e->getMessage(),
                'total_assigned' => 0,
                'exam_results' => []
            ];
        }
    }
}

// Standalone function for easy integration
function assignExamToExistingApplicants($examId, $companyId, $dueDateDays = 7) {
    global $pdo;
    
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        return [
            'success' => false,
            'message' => 'Database connection not available',
            'assigned_count' => 0
        ];
    }
    
    $assignment = new RetroactiveExamAssignment($pdo);
    return $assignment->assignExamToExistingApplicants($examId, $companyId, $dueDateDays);
}

// Standalone function for job-specific assignment
function assignExamToJobApplicants($examId, $jobId, $dueDateDays = 7) {
    global $pdo;
    
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        return [
            'success' => false,
            'message' => 'Database connection not available',
            'assigned_count' => 0
        ];
    }
    
    $assignment = new RetroactiveExamAssignment($pdo);
    return $assignment->assignExamToJobApplicants($examId, $jobId, $dueDateDays);
}

// API endpoint for manual triggering
if (isset($_GET['action']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        echo json_encode(['success' => false, 'message' => 'Database connection not available']);
        exit;
    }
    
    $assignment = new RetroactiveExamAssignment($pdo);
    $action = $_GET['action'];
    
    switch ($action) {
        case 'assign_to_company':
            $examId = intval($_POST['exam_id'] ?? 0);
            $companyId = intval($_POST['company_id'] ?? 0);
            $dueDateDays = intval($_POST['due_date_days'] ?? 7);
            
            if (!$examId || !$companyId) {
                echo json_encode(['success' => false, 'message' => 'Exam ID and Company ID are required']);
                exit;
            }
            
            $result = $assignment->assignExamToExistingApplicants($examId, $companyId, $dueDateDays);
            echo json_encode($result);
            break;
            
        case 'assign_to_job':
            $examId = intval($_POST['exam_id'] ?? 0);
            $jobId = intval($_POST['job_id'] ?? 0);
            $dueDateDays = intval($_POST['due_date_days'] ?? 7);
            
            if (!$examId || !$jobId) {
                echo json_encode(['success' => false, 'message' => 'Exam ID and Job ID are required']);
                exit;
            }
            
            $result = $assignment->assignExamToJobApplicants($examId, $jobId, $dueDateDays);
            echo json_encode($result);
            break;
            
        case 'bulk_assign':
            $companyId = intval($_POST['company_id'] ?? 0);
            $dueDateDays = intval($_POST['due_date_days'] ?? 7);
            
            if (!$companyId) {
                echo json_encode(['success' => false, 'message' => 'Company ID is required']);
                exit;
            }
            
            $result = $assignment->bulkAssignAllExams($companyId, $dueDateDays);
            echo json_encode($result);
            break;
            
        case 'get_stats':
            $companyId = intval($_POST['company_id'] ?? 0);
            
            if (!$companyId) {
                echo json_encode(['success' => false, 'message' => 'Company ID is required']);
                exit;
            }
            
            $result = $assignment->getExamAssignmentStats($companyId);
            echo json_encode($result);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
    exit;
}
?>

<?php
require_once 'Database.php';
require_once 'session_manager.php';

header('Content-Type: application/json');

// Log application attempts
error_log("Job application handler called - Method: " . $_SERVER['REQUEST_METHOD']);

// Check if candidate is logged in
if (!isCandidateLoggedIn()) {
    error_log("Candidate not logged in");
    echo json_encode(['success' => false, 'message' => 'Please login to apply for jobs']);
    exit;
}

$sessionCandidateId = getCurrentCandidateId();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        echo json_encode(['success' => false, 'message' => 'Invalid request data']);
        exit;
    }
    
    $action = $input['action'] ?? '';
    
    switch ($action) {
        case 'apply_to_job':
            applyToJob($input);
            break;
        case 'get_application_status':
            getApplicationStatus($input);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

function applyToJob($data) {
    global $pdo, $sessionCandidateId;
    
    // Simple lock mechanism to prevent duplicate calls
    static $processingApplications = [];
    static $processedRequests = [];
    
    $requestId = $data['requestId'] ?? null;
    if ($requestId && isset($processedRequests[$requestId])) {
        error_log("Request already processed: " . $requestId);
        echo json_encode(['success' => false, 'message' => 'Request already processed']);
        return;
    }
    
    $lockKey = $sessionCandidateId . '_' . $data['jobId'];
    if (isset($processingApplications[$lockKey])) {
        error_log("Application already being processed for candidate " . $sessionCandidateId . " and job " . $data['jobId']);
        echo json_encode(['success' => false, 'message' => 'Application is already being processed']);
        return;
    }
    
    $processingApplications[$lockKey] = true;
    if ($requestId) {
        $processedRequests[$requestId] = true;
    }
    
    try {
        // Check if PDO connection exists
        if (!isset($pdo) || !($pdo instanceof PDO)) {
            error_log("PDO connection not available");
            echo json_encode(['success' => false, 'message' => 'Database connection error']);
            return;
        }
        
        $jobId = $data['jobId'] ?? null;
        $coverLetter = $data['coverLetter'] ?? '';
        $additionalNotes = $data['additionalNotes'] ?? '';
        
        error_log("Processing application for job ID: " . $jobId . " by candidate: " . $sessionCandidateId);
        
        if (!$jobId) {
            error_log("Job ID is missing");
            echo json_encode(['success' => false, 'message' => 'Job ID is required']);
            return;
        }
        
        // Check if already applied
        try {
            $checkStmt = $pdo->prepare("SELECT ApplicationID FROM job_applications WHERE JobID = ? AND CandidateID = ?");
            $checkStmt->execute([$jobId, $sessionCandidateId]);
            if ($checkStmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'You have already applied for this job']);
                return;
            }
        } catch (Exception $e) {
            error_log("Error checking existing application: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Database error while checking application']);
            return;
        }
        
        // Get job details
        try {
            $jobStmt = $pdo->prepare("SELECT JobID, CompanyID, Department FROM job_postings WHERE JobID = ?");
            $jobStmt->execute([$jobId]);
            $job = $jobStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$job) {
                error_log("Job not found for ID: " . $jobId);
                echo json_encode(['success' => false, 'message' => 'Job not found']);
                return;
            }
            
            error_log("Job found for ID: " . $jobId);
        } catch (Exception $e) {
            error_log("Error getting job details: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Database error while fetching job details']);
            return;
        }
        
        // Start transaction
        try {
            $pdo->beginTransaction();
            error_log("Transaction started");
            
            // Insert job application
            $applicationStmt = $pdo->prepare("
                INSERT INTO job_applications (JobID, CandidateID, CoverLetter, Notes) 
                VALUES (?, ?, ?, ?)
            ");
            $applicationStmt->execute([$jobId, $sessionCandidateId, $coverLetter, $additionalNotes]);
            $applicationId = $pdo->lastInsertId();
            
            error_log("Application created with ID: " . $applicationId);
        } catch (Exception $e) {
            error_log("Error in transaction start/application insert: " . $e->getMessage());
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            
            // Check if it's a duplicate application error
            if (strpos($e->getMessage(), 'Duplicate entry') !== false || strpos($e->getMessage(), 'unique_application') !== false) {
                echo json_encode(['success' => false, 'message' => 'You have already applied for this job']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Database error while creating application']);
            }
            return;
        }
        
        // Check if there are exams for this job's department - assign only ONE exam per job
        // Only assign if exam has questions (QuestionCount > 0)
        try {
            $examStmt = $pdo->prepare("
                SELECT e.ExamID, e.ExamTitle, e.Duration, e.QuestionCount, e.PassingScore 
                FROM exams e
                WHERE e.CompanyID = ? AND e.IsActive = 1 AND e.QuestionCount > 0
                ORDER BY e.CreatedAt DESC
                LIMIT 1
            ");
            $examStmt->execute([$job['CompanyID']]);
            $exam = $examStmt->fetch(PDO::FETCH_ASSOC);
            
            $assignedExams = [];
            
            if ($exam) {
                error_log("Found exam with questions for company: " . $exam['ExamTitle'] . " (Questions: " . $exam['QuestionCount'] . ")");
                
                try {
                    // Check if exam is already assigned to this candidate for this job
                    $checkAssignmentStmt = $pdo->prepare("
                        SELECT AssignmentID FROM exam_assignments 
                        WHERE ExamID = ? AND CandidateID = ? AND JobID = ?
                    ");
                    $checkAssignmentStmt->execute([$exam['ExamID'], $sessionCandidateId, $jobId]);
                    
                    if ($checkAssignmentStmt->fetch()) {
                        error_log("Exam " . $exam['ExamID'] . " already assigned to candidate " . $sessionCandidateId . " for job " . $jobId);
                    } else {
                        $assignmentStmt = $pdo->prepare("
                            INSERT INTO exam_assignments (ExamID, CandidateID, JobID, DueDate) 
                            VALUES (?, ?, ?, DATE_ADD(CURDATE(), INTERVAL 7 DAY))
                        ");
                        $assignmentStmt->execute([
                            $exam['ExamID'], 
                            $sessionCandidateId, 
                            $jobId
                        ]);
                        
                        $assignedExams[] = [
                            'examId' => $exam['ExamID'],
                            'examTitle' => $exam['ExamTitle'],
                            'duration' => $exam['Duration'],
                            'totalQuestions' => $exam['QuestionCount'],
                            'passingScore' => $exam['PassingScore']
                        ];
                        
                        error_log("Exam assigned: " . $exam['ExamTitle'] . " (ID: " . $exam['ExamID'] . ")");
                    }
                } catch (Exception $e) {
                    // Check if it's a duplicate exam assignment error
                    if (strpos($e->getMessage(), 'Duplicate entry') !== false || strpos($e->getMessage(), 'unique_assignment') !== false) {
                        error_log("Exam " . $exam['ExamID'] . " already assigned to candidate " . $sessionCandidateId . " for job " . $jobId);
                    } else {
                        error_log("Error assigning exam " . $exam['ExamID'] . ": " . $e->getMessage());
                    }
                }
            } else {
                error_log("No exam with questions found for company " . $job['CompanyID'] . " - no exam will be assigned");
            }
        } catch (Exception $e) {
            error_log("Error fetching exams: " . $e->getMessage());
            // Don't fail the application if exam assignment fails
            $assignedExams = [];
        }
        
        // Commit transaction - application count is automatically updated by database trigger
        try {
            $pdo->commit();
            error_log("Transaction committed successfully");
            
            // Verify the application count was updated by trigger
            $verifyStmt = $pdo->prepare("SELECT ApplicationCount FROM job_postings WHERE JobID = ?");
            $verifyStmt->execute([$jobId]);
            $newCount = $verifyStmt->fetchColumn();
            error_log("Application count for JobID $jobId after trigger: $newCount");
            
            echo json_encode([
                'success' => true, 
                'message' => 'Application submitted successfully!',
                'applicationId' => $applicationId,
                'assignedExams' => $assignedExams,
                'examCount' => count($assignedExams)
            ]);
        } catch (Exception $e) {
            error_log("Error in final transaction operations: " . $e->getMessage());
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            echo json_encode(['success' => false, 'message' => 'Database error while finalizing application']);
        }
        
    } catch (Exception $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Error applying to job: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to submit application: ' . $e->getMessage()]);
    } finally {
        // Release the lock
        unset($processingApplications[$lockKey]);
    }
}

function getApplicationStatus($data) {
    global $pdo, $sessionCandidateId;
    
    try {
        // Check if PDO connection exists
        if (!isset($pdo) || !($pdo instanceof PDO)) {
            error_log("PDO connection not available in getApplicationStatus");
            echo json_encode(['success' => false, 'message' => 'Database connection error']);
            return;
        }
        
        $stmt = $pdo->prepare("
            SELECT ja.*, jp.JobTitle, jp.Department, cli.CompanyName,
                   COUNT(ea.AssignmentID) as assigned_exams,
                   COUNT(CASE WHEN ea.Status = 'completed' THEN 1 END) as completed_exams
            FROM job_applications ja
            JOIN job_postings jp ON ja.JobID = jp.JobID
            JOIN Company_login_info cli ON jp.CompanyID = cli.CompanyID
            LEFT JOIN exam_assignments ea ON ja.JobID = ea.JobID AND ja.CandidateID = ea.CandidateID
            WHERE ja.CandidateID = ?
            GROUP BY ja.ApplicationID
            ORDER BY ja.ApplicationDate DESC
        ");
        $stmt->execute([$sessionCandidateId]);
        $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        error_log("Found " . count($applications) . " applications for candidate " . $sessionCandidateId);
        echo json_encode(['success' => true, 'applications' => $applications]);
        
    } catch (Exception $e) {
        error_log("Error getting application status: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to load application status: ' . $e->getMessage()]);
    }
}
?>
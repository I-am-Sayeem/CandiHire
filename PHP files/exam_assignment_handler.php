<?php
// exam_assignment_handler.php - Handle exam assignments when candidates apply for jobs
require_once 'Database.php';
require_once 'session_manager.php';

// Only execute API logic if this file is accessed directly (not included)
if (basename($_SERVER['SCRIPT_NAME']) === basename(__FILE__)) {
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');

    // Handle preflight OPTIONS request
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }

    // Handle GET requests for getting exams
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $action = $_GET['action'] ?? '';
        
        if ($action === 'get_exams') {
            getExamsForJob();
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }
        exit;
    }

    // Handle POST requests for exam assignments
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'assign_exam') {
            assignExamToCandidate();
        } elseif ($action === 'auto_assign_exam') {
            autoAssignExamToCandidate();
        } elseif ($action === 'assign_exam_to_all_applicants') {
            assignExamToAllApplicants();
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }
        exit;
    }
}

// Function to get exams for a specific job post
function getExamsForJob() {
    global $pdo;
    
    try {
        if (!isset($pdo) || !($pdo instanceof PDO)) {
            throw new Exception('Database connection not available');
        }
        
        $jobPostId = $_GET['job_post_id'] ?? null;
        if (!$jobPostId) {
            echo json_encode(['success' => false, 'message' => 'Job post ID is required']);
            return;
        }
        
        // Get company ID from session
        if (!isCompanyLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
            return;
        }
        
        $companyId = getCurrentCompanyId();
        
        // Get exams for this company
        $stmt = $pdo->prepare("
            SELECT 
                ExamID,
                ExamTitle,
                Description,
                Duration,
                QuestionCount,
                PassingScore,
                IsActive,
                CreatedAt
            FROM exams 
            WHERE CompanyID = ? 
            AND IsActive = TRUE
            ORDER BY CreatedAt DESC
        ");
        
        $stmt->execute([$companyId]);
        $exams = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'exams' => $exams
        ]);
        
    } catch (Exception $e) {
        error_log("Error getting exams for job: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Server error occurred']);
    }
}

// Function to auto-assign exam to candidate
function autoAssignExamToCandidate() {
    global $pdo;
    
    try {
        if (!isset($pdo) || !($pdo instanceof PDO)) {
            throw new Exception('Database connection not available');
        }
        
        // Get company ID from session
        if (!isCompanyLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
            return;
        }
        
        $companyId = getCurrentCompanyId();
        $candidateId = $_POST['candidate_id'] ?? null;
        $jobPostId = $_POST['job_post_id'] ?? null;
        
        if (!$candidateId || !$jobPostId) {
            echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
            return;
        }
        
        // Verify job belongs to company
        $jobStmt = $pdo->prepare("SELECT JobID, JobTitle, Department FROM job_postings WHERE JobID = ? AND CompanyID = ?");
        $jobStmt->execute([$jobPostId, $companyId]);
        $job = $jobStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$job) {
            echo json_encode(['success' => false, 'message' => 'Job not found or unauthorized']);
            return;
        }
        
        // Check if candidate already applied for this job
        $applicationStmt = $pdo->prepare("
            SELECT ApplicationID 
            FROM job_applications 
            WHERE CandidateID = ? AND JobID = ?
        ");
        $applicationStmt->execute([$candidateId, $jobPostId]);
        
        if ($applicationStmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Candidate already applied for this position in your company']);
            return;
        }
        
        // Check if candidate already has an exam assigned for this job
        $existingStmt = $pdo->prepare("
            SELECT AssignmentID 
            FROM exam_assignments 
            WHERE CandidateID = ? AND JobID = ?
        ");
        $existingStmt->execute([$candidateId, $jobPostId]);
        
        if ($existingStmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Exam already assigned to this candidate for this job']);
            return;
        }
        
        // Find an available exam for this company (only if it has questions)
        $examStmt = $pdo->prepare("
            SELECT ExamID, ExamTitle, Duration, QuestionCount, PassingScore
            FROM exams 
            WHERE CompanyID = ? 
            AND IsActive = TRUE
            AND QuestionCount > 0
            ORDER BY CreatedAt DESC 
            LIMIT 1
        ");
        $examStmt->execute([$companyId]);
        $exam = $examStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$exam) {
            // No exam found - create a default exam using the legacy function
            $result = assignExamToCandidateLegacy($candidateId, $jobPostId, $companyId);
            if ($result['success']) {
                echo json_encode(['success' => true, 'message' => 'Default exam created and assigned successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'No exams available and failed to create default exam']);
            }
            return;
        }
        
        // Assign the found exam to candidate
        $assignmentStmt = $pdo->prepare("
            INSERT INTO exam_assignments (ExamID, CandidateID, JobID, AssignmentDate, Status, DueDate) 
            VALUES (?, ?, ?, NOW(), 'assigned', DATE_ADD(NOW(), INTERVAL 7 DAY))
        ");
        
        $result = $assignmentStmt->execute([$exam['ExamID'], $candidateId, $jobPostId]);
        
        if ($result) {
            echo json_encode([
                'success' => true, 
                'message' => 'Exam "' . $exam['ExamTitle'] . '" assigned successfully',
                'exam_title' => $exam['ExamTitle']
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to assign exam']);
        }
        
    } catch (Exception $e) {
        error_log("Error auto-assigning exam: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Server error occurred']);
    }
}

// Function to assign exam to candidate (manual selection)
function assignExamToCandidate() {
    global $pdo;
    
    try {
        if (!isset($pdo) || !($pdo instanceof PDO)) {
            throw new Exception('Database connection not available');
        }
        
        // Get company ID from session
        if (!isCompanyLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
            return;
        }
        
        $companyId = getCurrentCompanyId();
        $candidateId = $_POST['candidate_id'] ?? null;
        $jobPostId = $_POST['job_post_id'] ?? null;
        $examId = $_POST['exam_id'] ?? null;
        
        if (!$candidateId || !$jobPostId || !$examId) {
            echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
            return;
        }
        
        // Verify job belongs to company
        $jobStmt = $pdo->prepare("SELECT JobID FROM job_postings WHERE JobID = ? AND CompanyID = ?");
        $jobStmt->execute([$jobPostId, $companyId]);
        if (!$jobStmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Job not found or unauthorized']);
            return;
        }
        
        // Verify exam belongs to company
        $examStmt = $pdo->prepare("SELECT ExamID FROM exams WHERE ExamID = ? AND CompanyID = ?");
        $examStmt->execute([$examId, $companyId]);
        if (!$examStmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Exam not found or unauthorized']);
            return;
        }
        
        // Check if candidate already applied for this job
        $applicationStmt = $pdo->prepare("
            SELECT ApplicationID 
            FROM job_applications 
            WHERE CandidateID = ? AND JobID = ?
        ");
        $applicationStmt->execute([$candidateId, $jobPostId]);
        
        if ($applicationStmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Candidate already applied for this position in your company']);
            return;
        }
        
        // Check if exam is already assigned to this candidate for this job
        $existingStmt = $pdo->prepare("
            SELECT AssignmentID 
            FROM exam_assignments 
            WHERE ExamID = ? AND CandidateID = ? AND JobID = ?
        ");
        $existingStmt->execute([$examId, $candidateId, $jobPostId]);
        
        if ($existingStmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Exam already assigned to this candidate for this job']);
            return;
        }
        
        // Assign exam to candidate
        $assignmentStmt = $pdo->prepare("
            INSERT INTO exam_assignments (ExamID, CandidateID, JobID, AssignmentDate, Status, DueDate) 
            VALUES (?, ?, ?, NOW(), 'assigned', DATE_ADD(NOW(), INTERVAL 7 DAY))
        ");
        
        $result = $assignmentStmt->execute([$examId, $candidateId, $jobPostId]);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Exam assigned successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to assign exam']);
        }
        
    } catch (Exception $e) {
        error_log("Error assigning exam: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Server error occurred']);
    }
}

// Legacy function - kept for backward compatibility
function assignExamToCandidateLegacy($candidateId, $jobId, $companyId) {
    global $pdo;
    
    try {
        if (!isset($pdo) || !($pdo instanceof PDO)) {
            error_log("PDO connection not available in assignExamToCandidate");
            return [
                'success' => false,
                'message' => 'Database connection error'
            ];
        }
        
        // Get job details to determine department
        $jobStmt = $pdo->prepare("
            SELECT JobTitle, Department, CompanyID 
            FROM job_postings 
            WHERE JobID = ? AND CompanyID = ?
        ");
        $jobStmt->execute([$jobId, $companyId]);
        $job = $jobStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$job) {
            throw new Exception('Job not found');
        }
        
        // Map job title to department for exam selection
        $department = $job['Department'] ?? 'Software Engineering';
        
        // Find an exam for this department and company (only if it has questions)
        $examStmt = $pdo->prepare("
            SELECT ExamID, ExamTitle, Duration, QuestionCount, PassingScore
            FROM exams 
            WHERE CompanyID = ? 
            AND IsActive = TRUE
            AND QuestionCount > 0
            ORDER BY CreatedAt DESC 
            LIMIT 1
        ");
        $examStmt->execute([$companyId]);
        $exam = $examStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$exam) {
            // No exam found for this company, create a default auto-generated exam
            $exam = createDefaultExam($companyId, $department, $job['JobTitle']);
        }
        
        // Check if candidate already applied for this job
        $applicationStmt = $pdo->prepare("
            SELECT ApplicationID 
            FROM job_applications 
            WHERE CandidateID = ? AND JobID = ?
        ");
        $applicationStmt->execute([$candidateId, $jobId]);
        
        if ($applicationStmt->fetch()) {
            return [
                'success' => false,
                'message' => 'Candidate already applied for this position in your company'
            ];
        }
        
        // Check if exam is already assigned to this candidate for this job
        $existingStmt = $pdo->prepare("
            SELECT AssignmentID 
            FROM exam_assignments 
            WHERE ExamID = ? AND CandidateID = ? AND JobID = ?
        ");
        $existingStmt->execute([$exam['ExamID'], $candidateId, $jobId]);
        
        if ($existingStmt->fetch()) {
            // Exam already assigned
            return [
                'success' => true,
                'message' => 'Exam already assigned',
                'exam_id' => $exam['ExamID']
            ];
        }
        
        // Assign exam to candidate
        $assignmentStmt = $pdo->prepare("
            INSERT INTO exam_assignments (ExamID, CandidateID, JobID, AssignmentDate, Status, DueDate) 
            VALUES (?, ?, ?, NOW(), 'assigned', DATE_ADD(NOW(), INTERVAL 7 DAY))
        ");
        
        $assignmentStmt->execute([
            $exam['ExamID'],
            $candidateId,
            $jobId
        ]);
        
        // Exam assignment completed successfully
        
        return [
            'success' => true,
            'message' => 'Exam assigned successfully',
            'exam_id' => $exam['ExamID'],
            'exam_title' => $exam['ExamTitle']
        ];
        
    } catch (Exception $e) {
        error_log("Error assigning exam: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Failed to assign exam: ' . $e->getMessage()
        ];
    }
}

function createDefaultExam($companyId, $department, $jobTitle) {
    global $pdo;
    
    try {
        if (!isset($pdo) || !($pdo instanceof PDO)) {
            error_log("PDO connection not available in createDefaultExam");
            throw new Exception('Database connection not available');
        }
        
        // Start transaction
        $pdo->beginTransaction();
        
        // Create exam
        $examStmt = $pdo->prepare("
            INSERT INTO exams (CompanyID, ExamTitle, Description, Instructions, Duration, QuestionCount, PassingScore, CreatedBy) 
            VALUES (?, ?, ?, ?, 3600, 20, 70.00, 'System')
        ");
        
        $examTitle = $jobTitle . ' Assessment';
        $description = 'Auto-generated assessment for ' . $jobTitle . ' position';
        $instructions = 'Please read each question carefully and select the best answer. You have 60 minutes to complete this assessment.';
        
        $examStmt->execute([
            $companyId,
            $examTitle,
            $description,
            $instructions
        ]);
        
        $examId = $pdo->lastInsertId();
        
        // Get questions from question bank
        $questionBankStmt = $pdo->prepare("
            SELECT QuestionBankID as QuestionID, QuestionText, Difficulty, Department as Category, OptionA, OptionB, OptionC, OptionD, CorrectOption
            FROM question_banks
            WHERE Department = ?
            ORDER BY RAND()
            LIMIT 20
        ");
        
        $questionBankStmt->execute([$department]);
        $questions = $questionBankStmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($questions) < 20) {
            // If not enough questions, get from any department
            $fallbackStmt = $pdo->prepare("
                SELECT QuestionBankID as QuestionID, QuestionText, Difficulty, Department as Category, OptionA, OptionB, OptionC, OptionD, CorrectOption
                FROM question_banks
                ORDER BY RAND()
                LIMIT 20
            ");
            $fallbackStmt->execute();
            $questions = $fallbackStmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        // Insert questions into exam
        $questionStmt = $pdo->prepare("
            INSERT INTO exam_questions (ExamID, QuestionText, QuestionOrder, Difficulty, Category) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $optionStmt = $pdo->prepare("
            INSERT INTO exam_question_options (QuestionID, OptionText, IsCorrect, OptionOrder) 
            VALUES (?, ?, ?, ?)
        ");
        
        foreach ($questions as $index => $question) {
            // Insert question
            $questionStmt->execute([
                $examId,
                $question['QuestionText'],
                $index + 1,
                $question['Difficulty'],
                $question['Category']
            ]);
            
            $examQuestionId = $pdo->lastInsertId();
            
            // Create options from the question_banks data
            $options = [
                ['OptionText' => $question['OptionA'], 'IsCorrect' => ($question['CorrectOption'] === 'A' ? 1 : 0), 'OptionOrder' => 1],
                ['OptionText' => $question['OptionB'], 'IsCorrect' => ($question['CorrectOption'] === 'B' ? 1 : 0), 'OptionOrder' => 2],
                ['OptionText' => $question['OptionC'], 'IsCorrect' => ($question['CorrectOption'] === 'C' ? 1 : 0), 'OptionOrder' => 3],
                ['OptionText' => $question['OptionD'], 'IsCorrect' => ($question['CorrectOption'] === 'D' ? 1 : 0), 'OptionOrder' => 4]
            ];
            
            // Insert options
            foreach ($options as $option) {
                $optionStmt->execute([
                    $examQuestionId,
                    $option['OptionText'],
                    $option['IsCorrect'],
                    $option['OptionOrder']
                ]);
            }
        }
        
        // Update question count
        $updateStmt = $pdo->prepare("
            UPDATE exams 
            SET QuestionCount = ? 
            WHERE ExamID = ?
        ");
        $updateStmt->execute([count($questions), $examId]);
        
        // Commit transaction
        $pdo->commit();
        
        return [
            'ExamID' => $examId,
            'ExamTitle' => $examTitle,
            'Duration' => 3600,
            'QuestionCount' => count($questions),
            'PassingScore' => 70.00
        ];
        
    } catch (Exception $e) {
        if (isset($pdo)) {
            $pdo->rollBack();
        }
        throw $e;
    }
}

// Function to get assigned exams for a candidate
function getAssignedExams($candidateId) {
    global $pdo;
    
    try {
        if (!isset($pdo) || !($pdo instanceof PDO)) {
            error_log("PDO connection not available in getAssignedExams");
            return [];
        }
        
        $stmt = $pdo->prepare("
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
        
        $stmt->execute([$candidateId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (Exception $e) {
        error_log("Error getting assigned exams: " . $e->getMessage());
        return [];
    }
}

// Function to get completed exams for a candidate
function getCompletedExams($candidateId) {
    global $pdo;
    
    try {
        if (!isset($pdo) || !($pdo instanceof PDO)) {
            error_log("PDO connection not available in getCompletedExams");
            return [];
        }
        
        $stmt = $pdo->prepare("
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
                cli.CompanyName,
                ea.Score,
                ea.CorrectAnswers,
                ea.TotalQuestions,
                ea.TimeSpent,
                ea.CompletedAt
            FROM exam_assignments ea
            JOIN exams e ON ea.ExamID = e.ExamID
            JOIN job_postings jp ON ea.JobID = jp.JobID
            JOIN Company_login_info cli ON jp.CompanyID = cli.CompanyID
            WHERE ea.CandidateID = ? AND ea.Status = 'completed'
            ORDER BY ea.CompletedAt DESC
        ");
        
        $stmt->execute([$candidateId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (Exception $e) {
        error_log("Error getting completed exams: " . $e->getMessage());
        return [];
    }
}

// Function to update exam assignment with results
function updateExamAssignmentResults($assignmentId, $score, $correctAnswers, $totalQuestions, $timeSpent) {
    global $pdo;
    
    try {
        if (!isset($pdo) || !($pdo instanceof PDO)) {
            error_log("PDO connection not available in updateExamAssignmentResults");
            return false;
        }
        
        $stmt = $pdo->prepare("
            UPDATE exam_assignments 
            SET Status = 'completed', 
                Score = ?, 
                CorrectAnswers = ?, 
                TotalQuestions = ?, 
                TimeSpent = ?, 
                CompletedAt = NOW(),
                UpdatedAt = NOW()
            WHERE AssignmentID = ?
        ");
        
        return $stmt->execute([$score, $correctAnswers, $totalQuestions, $timeSpent, $assignmentId]);
        
    } catch (Exception $e) {
        error_log("Error updating exam assignment results: " . $e->getMessage());
        return false;
    }
}

// Function to assign exam to all existing applicants for a job
function assignExamToAllApplicants() {
    global $pdo;
    
    try {
        if (!isset($pdo) || !($pdo instanceof PDO)) {
            throw new Exception('Database connection not available');
        }
        
        // Get company ID from session
        if (!isCompanyLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
            return;
        }
        
        $companyId = getCurrentCompanyId();
        $jobPostId = $_POST['job_post_id'] ?? null;
        $examId = $_POST['exam_id'] ?? null;
        
        if (!$jobPostId || !$examId) {
            echo json_encode(['success' => false, 'message' => 'Missing required parameters: job_post_id and exam_id']);
            return;
        }
        
        // Verify job belongs to company
        $jobStmt = $pdo->prepare("SELECT JobID, JobTitle, Department FROM job_postings WHERE JobID = ? AND CompanyID = ?");
        $jobStmt->execute([$jobPostId, $companyId]);
        $job = $jobStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$job) {
            echo json_encode(['success' => false, 'message' => 'Job not found or unauthorized']);
            return;
        }
        
        // Verify exam belongs to company
        $examStmt = $pdo->prepare("SELECT ExamID, ExamTitle, Duration, QuestionCount, PassingScore FROM exams WHERE ExamID = ? AND CompanyID = ? AND IsActive = TRUE");
        $examStmt->execute([$examId, $companyId]);
        $exam = $examStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$exam) {
            echo json_encode(['success' => false, 'message' => 'Exam not found or unauthorized']);
            return;
        }
        
        // Get all applicants for this job who don't already have an exam assigned
        $applicantsStmt = $pdo->prepare("
            SELECT DISTINCT ja.CandidateID, cli.FullName, cli.Email
            FROM job_applications ja
            JOIN candidate_login_info cli ON ja.CandidateID = cli.CandidateID
            LEFT JOIN exam_assignments ea ON ja.CandidateID = ea.CandidateID AND ja.JobID = ea.JobID
            WHERE ja.JobID = ? AND ea.AssignmentID IS NULL
        ");
        $applicantsStmt->execute([$jobPostId]);
        $applicants = $applicantsStmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($applicants)) {
            echo json_encode([
                'success' => true, 
                'message' => 'No new applicants found to assign exam to',
                'assigned_count' => 0
            ]);
            return;
        }
        
        // Assign exam to all applicants
        $assignmentStmt = $pdo->prepare("
            INSERT INTO exam_assignments (ExamID, CandidateID, JobID, AssignmentDate, Status, DueDate) 
            VALUES (?, ?, ?, NOW(), 'assigned', DATE_ADD(NOW(), INTERVAL 7 DAY))
        ");
        
        $assignedCount = 0;
        $failedAssignments = [];
        
        foreach ($applicants as $applicant) {
            try {
                $result = $assignmentStmt->execute([
                    $examId, 
                    $applicant['CandidateID'], 
                    $jobPostId
                ]);
                
                if ($result) {
                    $assignedCount++;
                } else {
                    $failedAssignments[] = $applicant['FullName'];
                }
            } catch (Exception $e) {
                error_log("Error assigning exam to candidate {$applicant['CandidateID']}: " . $e->getMessage());
                $failedAssignments[] = $applicant['FullName'];
            }
        }
        
        $message = "Exam '{$exam['ExamTitle']}' assigned to {$assignedCount} applicant(s)";
        if (!empty($failedAssignments)) {
            $message .= ". Failed to assign to: " . implode(', ', $failedAssignments);
        }
        
        echo json_encode([
            'success' => true,
            'message' => $message,
            'assigned_count' => $assignedCount,
            'total_applicants' => count($applicants),
            'failed_assignments' => $failedAssignments
        ]);
        
    } catch (Exception $e) {
        error_log("Error assigning exam to all applicants: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Server error occurred while assigning exams']);
    }
}
?>

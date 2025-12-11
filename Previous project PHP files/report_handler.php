<?php
// report_handler.php - Handle job post and candidate post reports
session_start();
require_once 'Database.php';
require_once 'session_manager.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check database connection
if (!$pdo) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed. Please try again later.']);
    exit;
}

// Check if user is logged in (candidate or company)
$isCandidateLoggedIn = isCandidateLoggedIn();
$isCompanyLoggedIn = isCompanyLoggedIn();

if (!$isCandidateLoggedIn && !$isCompanyLoggedIn) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to submit a report.']);
    exit;
}

// Get user ID and type
$userId = null;
$userType = null;

if ($isCandidateLoggedIn) {
    $userId = getCurrentCandidateId();
    $userType = 'candidate';
} elseif ($isCompanyLoggedIn) {
    $userId = getCurrentCompanyId();
    $userType = 'company';
}

if (!$userId) {
    echo json_encode(['success' => false, 'message' => 'Unable to identify user. Please login again.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        echo json_encode(['success' => false, 'message' => 'Invalid request data.']);
        exit;
    }
    
    $action = $input['action'] ?? '';
    
    if ($action === 'report_job') {
        try {
            // Validate required fields
            $jobId = $input['jobId'] ?? '';
            $companyId = $input['companyId'] ?? '';
            $jobTitle = $input['jobTitle'] ?? '';
            $companyName = $input['companyName'] ?? '';
            $reason = $input['reason'] ?? '';
            $description = $input['description'] ?? '';
            $contact = $input['contact'] ?? '';
            
            if (empty($jobId) || empty($companyId) || empty($reason) || empty($description)) {
                echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
                exit;
            }
            
            // Check if job post exists
            $stmt = $pdo->prepare("SELECT JobID FROM job_postings WHERE JobID = ? AND CompanyID = ?");
            $stmt->execute([$jobId, $companyId]);
            if (!$stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Job post not found.']);
                exit;
            }
            
            // Check if user has already reported this job post
            $stmt = $pdo->prepare("
                SELECT ComplaintID FROM complaints 
                WHERE UserID = ? AND UserType = 'candidate' 
                AND Subject LIKE ? AND Status = 'pending'
            ");
            $stmt->execute([$candidateId, "%Job Report: $jobTitle%"]);
            if ($stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'You have already reported this job post.']);
                exit;
            }
            
            // Create complaint record
            $subject = "Job Report: $jobTitle";
            $fullDescription = "Job Post Report\n\n";
            $fullDescription .= "Job Title: $jobTitle\n";
            $fullDescription .= "Company: $companyName\n";
            $fullDescription .= "Job ID: $jobId\n";
            $fullDescription .= "Company ID: $companyId\n";
            $fullDescription .= "Report Reason: " . ucfirst(str_replace('_', ' ', $reason)) . "\n\n";
            $fullDescription .= "Description:\n$description\n\n";
            if (!empty($contact)) {
                $fullDescription .= "Reporter Contact: $contact\n";
            }
            $fullDescription .= "Reported by Candidate ID: $candidateId\n";
            $fullDescription .= "Report Date: " . date('Y-m-d H:i:s');
            
            $stmt = $pdo->prepare("
                INSERT INTO complaints (
                    UserID, UserType, Subject, Description, 
                    ComplaintDate, Status, Priority, 
                    ResolutionDetails, ResolutionDate
                ) VALUES (?, 'candidate', ?, ?, NOW(), 'pending', 'medium', NULL, NULL)
            ");
            
            $stmt->execute([$candidateId, $subject, $fullDescription]);
            
            echo json_encode([
                'success' => true, 
                'message' => 'Report submitted successfully. Thank you for helping keep our platform safe.'
            ]);
            
        } catch (Exception $e) {
            error_log("Error processing job report: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            echo json_encode([
                'success' => false, 
                'message' => 'An error occurred while processing your report.'
            ]);
        }
    } elseif ($action === 'report_candidate') {
        try {
            // Validate required fields
            $candidateId = $input['candidateId'] ?? '';
            $candidateName = $input['candidateName'] ?? '';
            $jobTitle = $input['jobTitle'] ?? '';
            $reason = $input['reason'] ?? '';
            $description = $input['description'] ?? '';
            $contact = $input['contact'] ?? '';
            $companyId = $input['companyId'] ?? '';
            
            if (empty($candidateId) || empty($reason) || empty($description)) {
                echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
                exit;
            }
            
            // Check if candidate exists
            $stmt = $pdo->prepare("SELECT CandidateID FROM candidate_login_info WHERE CandidateID = ?");
            $stmt->execute([$candidateId]);
            if (!$stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Candidate not found.']);
                exit;
            }
            
            // Check if company has already reported this candidate post
            $stmt = $pdo->prepare("
                SELECT ComplaintID FROM complaints 
                WHERE UserID = ? AND UserType = 'company' 
                AND Subject LIKE ? AND Status = 'pending'
            ");
            $stmt->execute([$userId, "%Candidate Report: $candidateName%"]);
            if ($stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'You have already reported this candidate post.']);
                exit;
            }
            
            // Create complaint record
            $subject = "Candidate Report: $candidateName";
            $fullDescription = "Candidate Post Report\n\n";
            $fullDescription .= "Candidate Name: $candidateName\n";
            $fullDescription .= "Job Title: $jobTitle\n";
            $fullDescription .= "Candidate ID: $candidateId\n";
            $fullDescription .= "Company ID: $companyId\n";
            $fullDescription .= "Report Reason: " . ucfirst(str_replace('_', ' ', $reason)) . "\n\n";
            $fullDescription .= "Description:\n$description\n\n";
            if (!empty($contact)) {
                $fullDescription .= "Reporter Contact: $contact\n";
            }
            $fullDescription .= "Reported by Company ID: $userId\n";
            $fullDescription .= "Report Date: " . date('Y-m-d H:i:s');
            
            $stmt = $pdo->prepare("
                INSERT INTO complaints (
                    UserID, UserType, Subject, Description, 
                    ComplaintDate, Status, Priority, 
                    ResolutionDetails, ResolutionDate
                ) VALUES (?, 'company', ?, ?, NOW(), 'pending', 'medium', NULL, NULL)
            ");
            
            $stmt->execute([$userId, $subject, $fullDescription]);
            
            echo json_encode([
                'success' => true, 
                'message' => 'Report submitted successfully. Thank you for helping keep our platform safe.'
            ]);
            
        } catch (Exception $e) {
            error_log("Error processing candidate report: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            echo json_encode([
                'success' => false, 
                'message' => 'An error occurred while processing your report.'
            ]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>

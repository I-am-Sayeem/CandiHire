<?php
// interview_schedule_handler.php - Handle interview scheduling from company side
require_once 'session_manager.php';
require_once 'Database.php';

// Check if company is logged in
if (!isCompanyLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid JSON input');
    }
    
    // Validate required fields
    $requiredFields = ['interviewMethod', 'positionId', 'candidateIds', 'interviewDate', 'interviewTime', 'companyId'];
    foreach ($requiredFields as $field) {
        if (!isset($input[$field]) || empty($input[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }
    
    // Get company ID from session
    $companyId = getCurrentCompanyId();
    
    // Validate that the company ID matches
    if ($input['companyId'] != $companyId) {
        throw new Exception('Company ID mismatch');
    }
    
    // Extract data
    $interviewMethod = $input['interviewMethod'];
    $meetingLink = $input['meetingLink'] ?? '';
    $interviewLocation = $input['interviewLocation'] ?? '';
    $positionId = $input['positionId'];
    $positionName = $input['positionName'] ?? '';
    $candidateIds = $input['candidateIds'];
    $candidateNames = $input['candidateNames'] ?? [];
    $candidateEmails = $input['candidateEmails'] ?? [];
    $interviewDate = $input['interviewDate'];
    $interviewTime = $input['interviewTime'];
    
    // Validate interview method
    if (!in_array($interviewMethod, ['virtual', 'onsite'])) {
        throw new Exception('Invalid interview method');
    }
    
    // Validate that meeting link is provided for virtual interviews
    if ($interviewMethod === 'virtual' && empty($meetingLink)) {
        throw new Exception('Meeting link is required for virtual interviews');
    }
    
    // Validate that location is provided for onsite interviews
    if ($interviewMethod === 'onsite' && empty($interviewLocation)) {
        throw new Exception('Interview location is required for onsite interviews');
    }
    
    // Start transaction
    if (!$pdo) {
        throw new Exception('Database connection not available');
    }
    $pdo->beginTransaction();
    
    try {
        // Create interview records for each candidate
        $interviewIds = [];
        
        foreach ($candidateIds as $index => $candidateId) {
            // Get job title for interview title
            $stmt = $pdo->prepare("SELECT JobTitle FROM job_postings WHERE JobID = ?");
            $stmt->execute([$positionId]);
            $job = $stmt->fetch(PDO::FETCH_ASSOC);
            $interviewTitle = $job ? $job['JobTitle'] . ' - Interview' : 'Interview';
            
            // Insert interview record
            $stmt = $pdo->prepare("
                INSERT INTO interviews (
                    CandidateID,
                    CompanyID, 
                    JobID, 
                    InterviewTitle,
                    InterviewType,
                    InterviewMode,
                    Platform,
                    MeetingLink,
                    ScheduledDate, 
                    ScheduledTime, 
                    Location,
                    Status,
                    CreatedAt
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'scheduled', NOW())
            ");
            
            $location = $interviewMethod === 'virtual' ? 'Online' : $interviewLocation;
            $platform = $interviewMethod === 'virtual' ? 'Virtual Meeting' : 'In-person';
            $interviewType = 'technical'; // Default to technical interview
            $storedMeetingLink = $interviewMethod === 'virtual' ? $meetingLink : '';
            
            $stmt->execute([
                $candidateId,
                $companyId,
                $positionId,
                $interviewTitle,
                $interviewType,
                $interviewMethod,
                $platform,
                $storedMeetingLink,
                $interviewDate,
                $interviewTime,
                $location
            ]);
            
            $interviewId = $pdo->lastInsertId();
            $interviewIds[] = $interviewId;
            
            // Send notification email to candidate (optional)
            $candidateName = $candidateNames[$index] ?? 'Candidate';
            $candidateEmail = $candidateEmails[$index] ?? '';
            
            // You can add email notification logic here if needed
            // sendInterviewNotification($candidateEmail, $candidateName, $interviewData);
        }
        
        // Commit transaction
        $pdo->commit();
        
        // Return success response
        echo json_encode([
            'success' => true,
            'message' => 'Interview scheduled successfully',
            'interviewIds' => $interviewIds,
            'interviewCount' => count($candidateIds)
        ]);
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        throw $e;
    }
    
} catch (Exception $e) {
    error_log("Interview scheduling error: " . $e->getMessage());
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>

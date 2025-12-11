<?php
// check_application_status.php - Check if candidate has applied for a job post
require_once 'session_manager.php';
require_once 'Database.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        throw new Exception('Method not allowed. Expected GET, got: ' . $_SERVER['REQUEST_METHOD']);
    }
    
    // Get parameters
    $candidateId = $_GET['candidate_id'] ?? null;
    $jobPostId = $_GET['job_post_id'] ?? null;
    
    if (!$candidateId || !$jobPostId) {
        throw new Exception('Missing required parameters: candidate_id and job_post_id');
    }
    
    // Validate IDs are numeric
    if (!is_numeric($candidateId) || !is_numeric($jobPostId)) {
        throw new Exception('Invalid ID format');
    }
    
    // Check if candidate has applied for this job
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as application_count 
        FROM job_applications 
        WHERE CandidateID = ? AND JobPostID = ?
    ");
    
    $stmt->execute([$candidateId, $jobPostId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $hasApplied = $result['application_count'] > 0;
    
    // Return response
    echo json_encode([
        'success' => true,
        'hasApplied' => $hasApplied,
        'candidateId' => $candidateId,
        'jobPostId' => $jobPostId
    ]);
    
} catch (Exception $e) {
    error_log("Application status check error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage(),
        'hasApplied' => false
    ]);
}
?>

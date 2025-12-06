<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once 'Database.php';
require_once 'session_manager.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    if (!isset($pdo) || !$pdo instanceof PDO) {
        throw new Exception('Database connection not available');
    }

    $companyId = getCurrentCompanyId();
    if (!$companyId) {
        echo json_encode(['success' => false, 'message' => 'Company not logged in']);
        exit;
    }

    $action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_STRING);

    if ($action === 'get_job_posts') {
        // Get all job posts for this company
        $stmt = $pdo->prepare("
            SELECT JobID, JobTitle, Department, Location, JobType, PostedDate, Status
            FROM job_postings
            WHERE CompanyID = ?
            ORDER BY PostedDate DESC
        ");
        $stmt->execute([$companyId]);
        $jobPosts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'jobPosts' => $jobPosts
        ]);

    } elseif ($action === 'get_applications') {
        $jobId = filter_input(INPUT_GET, 'jobId', FILTER_VALIDATE_INT);

        if (!$jobId) {
            echo json_encode(['success' => false, 'message' => 'Job ID required']);
            exit;
        }

        // Verify the job belongs to this company
        $verifyStmt = $pdo->prepare("SELECT JobID FROM job_postings WHERE JobID = ? AND CompanyID = ?");
        $verifyStmt->execute([$jobId, $companyId]);
        if (!$verifyStmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Job not found or access denied']);
            exit;
        }

        // Get applications for this job
        $stmt = $pdo->prepare("
            SELECT
                ja.ApplicationID, ja.ApplicationDate, ja.Status, ja.CoverLetter,
                c.CandidateID, c.FullName, c.Email, c.PhoneNumber, c.Location,
                c.Skills, c.ProfilePicture, c.WorkType,
                jp.JobTitle, jp.Department, jp.Location as JobLocation
            FROM job_applications ja
            JOIN candidate_login_info c ON ja.CandidateID = c.CandidateID
            JOIN job_postings jp ON ja.JobID = jp.JobID
            WHERE ja.JobID = ?
            ORDER BY ja.ApplicationDate DESC
        ");
        $stmt->execute([$jobId]);
        $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'applications' => $applications
        ]);

    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }

} catch (Exception $e) {
    error_log('Error in job_applications_handler.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>

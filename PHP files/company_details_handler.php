<?php
// company_details_handler.php - API endpoint to get company details

error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once 'Database.php';
require_once 'session_manager.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
    if (!isset($pdo) || !$pdo instanceof PDO) {
        throw new Exception('Database connection not available');
    }

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Get company ID from query parameter
        $companyId = isset($_GET['companyId']) ? (int)$_GET['companyId'] : null;
        
        if (!$companyId) {
            echo json_encode(['success' => false, 'message' => 'Company ID is required']);
            exit;
        }

        // Get company details
        $stmt = $pdo->prepare("
            SELECT 
                CompanyID,
                CompanyName,
                Industry,
                CompanySize,
                Email,
                PhoneNumber,
                CompanyDescription,
                Website,
                Logo,
                Address,
                City,
                State,
                Country,
                PostalCode,
                CreatedAt
            FROM Company_login_info 
            WHERE CompanyID = ? AND IsActive = 1
        ");
        
        $stmt->execute([$companyId]);
        $company = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$company) {
            echo json_encode(['success' => false, 'message' => 'Company not found']);
            exit;
        }

        // Get company statistics
        $statsStmt = $pdo->prepare("
            SELECT 
                COUNT(DISTINCT jp.JobID) as totalJobs,
                COUNT(DISTINCT ja.ApplicationID) as totalApplications,
                COUNT(DISTINCT i.InterviewID) as totalInterviews,
                COUNT(DISTINCT e.ExamID) as totalExams,
                AVG(jp.ApplicationCount) as avgApplicationsPerJob
            FROM Company_login_info cli
            LEFT JOIN job_postings jp ON cli.CompanyID = jp.CompanyID
            LEFT JOIN job_applications ja ON jp.JobID = ja.JobID
            LEFT JOIN interviews i ON cli.CompanyID = i.CompanyID
            LEFT JOIN exams e ON cli.CompanyID = e.ExamID
            WHERE cli.CompanyID = ?
        ");
        
        $statsStmt->execute([$companyId]);
        $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

        // Get recent job postings (last 5)
        $recentJobsStmt = $pdo->prepare("
            SELECT 
                JobID,
                JobTitle,
                Department,
                Location,
                JobType,
                PostedDate,
                ApplicationCount
            FROM job_postings 
            WHERE CompanyID = ? AND Status = 'active'
            ORDER BY PostedDate DESC 
            LIMIT 5
        ");
        
        $recentJobsStmt->execute([$companyId]);
        $recentJobs = $recentJobsStmt->fetchAll(PDO::FETCH_ASSOC);

        // Format the response
        $response = [
            'success' => true,
            'company' => [
                'id' => $company['CompanyID'],
                'name' => $company['CompanyName'],
                'industry' => $company['Industry'],
                'size' => $company['CompanySize'],
                'email' => $company['Email'],
                'phone' => $company['PhoneNumber'],
                'description' => $company['CompanyDescription'],
                'website' => $company['Website'],
                'logo' => $company['Logo'],
                'address' => $company['Address'],
                'city' => $company['City'],
                'state' => $company['State'],
                'country' => $company['Country'],
                'postalCode' => $company['PostalCode'],
                'joinedDate' => $company['CreatedAt']
            ],
            'statistics' => [
                'totalJobs' => (int)$stats['totalJobs'],
                'totalApplications' => (int)$stats['totalApplications'],
                'totalInterviews' => (int)$stats['totalInterviews'],
                'totalExams' => (int)$stats['totalExams'],
                'avgApplicationsPerJob' => round($stats['avgApplicationsPerJob'] ?? 0, 1)
            ],
            'recentJobs' => array_map(function($job) {
                return [
                    'id' => $job['JobID'],
                    'title' => $job['JobTitle'],
                    'department' => $job['Department'],
                    'location' => $job['Location'],
                    'type' => $job['JobType'],
                    'postedDate' => $job['PostedDate'],
                    'applicationCount' => (int)$job['ApplicationCount']
                ];
            }, $recentJobs)
        ];

        echo json_encode($response);

    } else {
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }

} catch (Exception $e) {
    error_log('Error in company_details_handler.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error occurred']);
}
?>

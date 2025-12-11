<?php
// job_posting_handler.php - Handle job posting CRUD operations

error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once 'Database.php';
require_once 'session_manager.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Check if company is logged in
if (!isCompanyLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$companyId = getCurrentCompanyId();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'create_job':
            createJobPost();
            break;
        case 'update_job':
            updateJobPost();
            break;
        case 'delete_job':
            deleteJobPost();
            break;
        case 'get_job':
            getJobPost();
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? 'list_jobs';
    
    switch ($action) {
        case 'list_jobs':
            listJobPosts();
            break;
        case 'get_job':
            getJobPost();
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
}

function createJobPost() {
    global $pdo, $companyId;
    
    try {
        if (!$pdo) {
            throw new Exception('Database connection not available');
        }
        
        // Validate required fields
        $requiredFields = ['jobTitle', 'jobDescription', 'jobType', 'location'];
        foreach ($requiredFields as $field) {
            if (empty($_POST[$field])) {
                echo json_encode(['success' => false, 'message' => "Field '$field' is required"]);
                return;
            }
        }
        
        // Prepare data
        $jobTitle = trim($_POST['jobTitle']);
        $department = trim($_POST['department'] ?? '');
        $jobDescription = trim($_POST['jobDescription']);
        $requirements = trim($_POST['requirements'] ?? '');
        $responsibilities = trim($_POST['responsibilities'] ?? '');
        $skills = trim($_POST['skills'] ?? '');
        $location = trim($_POST['location']);
        $jobType = $_POST['jobType'];
        $salaryMin = !empty($_POST['salaryMin']) ? floatval($_POST['salaryMin']) : null;
        $salaryMax = !empty($_POST['salaryMax']) ? floatval($_POST['salaryMax']) : null;
        $currency = $_POST['currency'] ?? 'USD';
        $experienceLevel = $_POST['experienceLevel'] ?? 'mid';
        $educationLevel = $_POST['educationLevel'] ?? 'bachelor';
        $closingDate = !empty($_POST['closingDate']) ? $_POST['closingDate'] : null;
        
        // Insert job posting
        $stmt = $pdo->prepare("
            INSERT INTO job_postings (
                CompanyID, JobTitle, Department, JobDescription, Requirements, 
                Responsibilities, Skills, Location, JobType, SalaryMin, SalaryMax, 
                Currency, ExperienceLevel, EducationLevel, Status, PostedDate, 
                ClosingDate, ApplicationCount
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW(), ?, 0)
        ");
        
        $result = $stmt->execute([
            $companyId, $jobTitle, $department, $jobDescription, $requirements,
            $responsibilities, $skills, $location, $jobType, $salaryMin, $salaryMax,
            $currency, $experienceLevel, $educationLevel, $closingDate
        ]);
        
        if ($result) {
            $jobId = $pdo->lastInsertId();
            echo json_encode([
                'success' => true, 
                'message' => 'Job posted successfully!',
                'jobId' => $jobId
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create job posting']);
        }
        
    } catch (Exception $e) {
        error_log("Error creating job post: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Server error occurred']);
    }
}

function updateJobPost() {
    global $pdo, $companyId;
    
    try {
        if (!$pdo) {
            throw new Exception('Database connection not available');
        }
        
        $jobId = intval($_POST['jobId'] ?? 0);
        if (!$jobId) {
            echo json_encode(['success' => false, 'message' => 'Job ID is required']);
            return;
        }
        
        // Verify job belongs to company
        $verifyStmt = $pdo->prepare("SELECT JobID FROM job_postings WHERE JobID = ? AND CompanyID = ?");
        $verifyStmt->execute([$jobId, $companyId]);
        if (!$verifyStmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Job not found or unauthorized']);
            return;
        }
        
        // Prepare data
        $jobTitle = trim($_POST['jobTitle']);
        $department = trim($_POST['department'] ?? '');
        $jobDescription = trim($_POST['jobDescription']);
        $requirements = trim($_POST['requirements'] ?? '');
        $responsibilities = trim($_POST['responsibilities'] ?? '');
        $skills = trim($_POST['skills'] ?? '');
        $location = trim($_POST['location']);
        $jobType = $_POST['jobType'];
        $salaryMin = !empty($_POST['salaryMin']) ? floatval($_POST['salaryMin']) : null;
        $salaryMax = !empty($_POST['salaryMax']) ? floatval($_POST['salaryMax']) : null;
        $currency = $_POST['currency'] ?? 'USD';
        $experienceLevel = $_POST['experienceLevel'] ?? 'mid';
        $educationLevel = $_POST['educationLevel'] ?? 'bachelor';
        $status = $_POST['status'] ?? 'active';
        $closingDate = !empty($_POST['closingDate']) ? $_POST['closingDate'] : null;
        
        // Update job posting
        $stmt = $pdo->prepare("
            UPDATE job_postings SET 
                JobTitle = ?, Department = ?, JobDescription = ?, Requirements = ?,
                Responsibilities = ?, Skills = ?, Location = ?, JobType = ?, 
                SalaryMin = ?, SalaryMax = ?, Currency = ?, ExperienceLevel = ?,
                EducationLevel = ?, Status = ?, ClosingDate = ?, UpdatedAt = NOW()
            WHERE JobID = ? AND CompanyID = ?
        ");
        
        $result = $stmt->execute([
            $jobTitle, $department, $jobDescription, $requirements,
            $responsibilities, $skills, $location, $jobType, $salaryMin, $salaryMax,
            $currency, $experienceLevel, $educationLevel, $status, $closingDate,
            $jobId, $companyId
        ]);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Job updated successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update job posting']);
        }
        
    } catch (Exception $e) {
        error_log("Error updating job post: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Server error occurred']);
    }
}

function deleteJobPost() {
    global $pdo, $companyId;
    
    try {
        if (!$pdo) {
            throw new Exception('Database connection not available');
        }
        
        $jobId = intval($_POST['jobId'] ?? 0);
        if (!$jobId) {
            echo json_encode(['success' => false, 'message' => 'Job ID is required']);
            return;
        }
        
        // Verify job belongs to company
        $verifyStmt = $pdo->prepare("SELECT JobID FROM job_postings WHERE JobID = ? AND CompanyID = ?");
        $verifyStmt->execute([$jobId, $companyId]);
        if (!$verifyStmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Job not found or unauthorized']);
            return;
        }
        
        // Delete job posting (cascade will handle related records)
        $stmt = $pdo->prepare("DELETE FROM job_postings WHERE JobID = ? AND CompanyID = ?");
        $result = $stmt->execute([$jobId, $companyId]);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Job deleted successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete job posting']);
        }
        
    } catch (Exception $e) {
        error_log("Error deleting job post: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Server error occurred']);
    }
}

function getJobPost() {
    global $pdo, $companyId;
    
    try {
        if (!$pdo) {
            throw new Exception('Database connection not available');
        }
        
        $jobId = intval($_GET['jobId'] ?? $_POST['jobId'] ?? 0);
        if (!$jobId) {
            echo json_encode(['success' => false, 'message' => 'Job ID is required']);
            return;
        }
        
        $stmt = $pdo->prepare("
            SELECT 
                j.*,
                c.CompanyName,
                c.Industry,
                c.Logo
            FROM job_postings j
            JOIN Company_login_info c ON j.CompanyID = c.CompanyID
            WHERE j.JobID = ? AND j.CompanyID = ?
        ");
        
        $stmt->execute([$jobId, $companyId]);
        $job = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($job) {
            echo json_encode(['success' => true, 'job' => $job]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Job not found']);
        }
        
    } catch (Exception $e) {
        error_log("Error getting job post: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Server error occurred']);
    }
}

function listJobPosts() {
    global $pdo, $companyId;
    
    try {
        if (!$pdo) {
            throw new Exception('Database connection not available');
        }
        
        $limit = intval($_GET['limit'] ?? 10);
        $offset = intval($_GET['offset'] ?? 0);
        $search = trim($_GET['search'] ?? '');
        $status = $_GET['status'] ?? 'all';
        
        // Build query
        $whereConditions = ["j.CompanyID = ?"];
        $params = [$companyId];
        
        if (!empty($search)) {
            $whereConditions[] = "(j.JobTitle LIKE ? OR j.Department LIKE ? OR j.Skills LIKE ? OR j.JobDescription LIKE ?)";
            $searchParam = "%$search%";
            $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam]);
        }
        
        if ($status !== 'all') {
            $whereConditions[] = "j.Status = ?";
            $params[] = $status;
        }
        
        $whereClause = implode(' AND ', $whereConditions);
        
        $stmt = $pdo->prepare("
            SELECT 
                j.*,
                c.CompanyName,
                c.Industry,
                c.Logo,
                COUNT(ja.ApplicationID) as ApplicationCount
            FROM job_postings j
            JOIN Company_login_info c ON j.CompanyID = c.CompanyID
            LEFT JOIN job_applications ja ON j.JobID = ja.JobID
            WHERE $whereClause
            GROUP BY j.JobID
            ORDER BY j.PostedDate DESC
            LIMIT ? OFFSET ?
        ");
        
        $params[] = $limit;
        $params[] = $offset;
        $stmt->execute($params);
        $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get total count
        $countStmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM job_postings j
            WHERE $whereClause
        ");
        $countParams = array_slice($params, 0, -2);
        $countStmt->execute($countParams);
        $totalJobs = $countStmt->fetchColumn();
        
        echo json_encode([
            'success' => true,
            'jobs' => $jobs,
            'total' => $totalJobs,
            'hasMore' => ($offset + $limit) < $totalJobs
        ]);
        
    } catch (Exception $e) {
        error_log("Error listing job posts: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Server error occurred']);
    }
}
?>

<?php
// company_job_posts_handler.php - Handle company job posts retrieval for candidate feed

error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once 'Database.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Handle company job posts retrieval for candidate feed
    try {
        if (!isset($pdo) || !$pdo instanceof PDO) {
            throw new Exception('Database connection not available');
        }

        $limit = $_GET['limit'] ?? 10;
        $offset = $_GET['offset'] ?? 0;

        // Build dynamic query with search and filters
        $whereConditions = ["j.Status = 'active'"];
        $params = [];
        
        // Search functionality
        $search = $_GET['search'] ?? '';
        if (!empty($search)) {
            $whereConditions[] = "(j.JobTitle LIKE ? OR c.CompanyName LIKE ? OR j.Skills LIKE ? OR j.JobDescription LIKE ?)";
            $searchParam = "%$search%";
            $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam]);
        }
        
        // Company filter
        $company = $_GET['company'] ?? '';
        if (!empty($company)) {
            $whereConditions[] = "c.CompanyName LIKE ?";
            $params[] = "%$company%";
        }
        
        // Location filter
        $location = $_GET['location'] ?? '';
        if (!empty($location)) {
            $whereConditions[] = "j.Location LIKE ?";
            $params[] = "%$location%";
        }
        
        // Job type filter
        $jobType = $_GET['jobType'] ?? '';
        if (!empty($jobType)) {
            $whereConditions[] = "j.JobType = ?";
            $params[] = $jobType;
        }
        
        // Experience level filter
        $experience = $_GET['experience'] ?? '';
        if (!empty($experience)) {
            $whereConditions[] = "j.ExperienceLevel = ?";
            $params[] = $experience;
        }
        
        // Skills filter
        $skills = $_GET['skills'] ?? '';
        if (!empty($skills)) {
            $skillsArray = array_map('trim', explode(',', $skills));
            $skillsConditions = [];
            foreach ($skillsArray as $skill) {
                $skillsConditions[] = "j.Skills LIKE ?";
                $params[] = "%$skill%";
            }
            if (!empty($skillsConditions)) {
                $whereConditions[] = "(" . implode(' OR ', $skillsConditions) . ")";
            }
        }
        
        // Salary filter
        $salary = $_GET['salary'] ?? '';
        if (!empty($salary)) {
            switch ($salary) {
                case '0-50000':
                    $whereConditions[] = "j.SalaryMax <= 50000";
                    break;
                case '50000-80000':
                    $whereConditions[] = "j.SalaryMin >= 50000 AND j.SalaryMax <= 80000";
                    break;
                case '80000-120000':
                    $whereConditions[] = "j.SalaryMin >= 80000 AND j.SalaryMax <= 120000";
                    break;
                case '120000-200000':
                    $whereConditions[] = "j.SalaryMin >= 120000 AND j.SalaryMax <= 200000";
                    break;
                case '200000+':
                    $whereConditions[] = "j.SalaryMin >= 200000";
                    break;
            }
        }
        
        $whereClause = implode(' AND ', $whereConditions);
        
        $stmt = $pdo->prepare("
            SELECT 
                j.JobID,
                j.JobTitle,
                j.Department,
                j.JobDescription,
                j.Requirements,
                j.Responsibilities,
                j.Skills,
                j.Location,
                j.JobType,
                j.SalaryMin,
                j.SalaryMax,
                j.Currency,
                j.ExperienceLevel,
                j.EducationLevel,
                j.Status,
                j.PostedDate,
                j.ClosingDate,
                j.ApplicationCount,
                c.CompanyName,
                c.Industry,
                c.CompanySize,
                c.Logo,
                c.CompanyID
            FROM job_postings j
            JOIN Company_login_info c ON j.CompanyID = c.CompanyID
            WHERE $whereClause
            ORDER BY j.PostedDate DESC
            LIMIT ? OFFSET ?
        ");
        
        $params[] = $limit;
        $params[] = $offset;
        $stmt->execute($params);
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Debug: Log application counts
        error_log("Job posts retrieved: " . count($posts));
        foreach ($posts as $post) {
            error_log("Job {$post['JobID']} ({$post['JobTitle']}): {$post['ApplicationCount']} applications");
        }

        // Get total count for pagination
        $countStmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM job_postings j
            JOIN Company_login_info c ON j.CompanyID = c.CompanyID
            WHERE $whereClause
        ");
        $countParams = array_slice($params, 0, -2); // Remove limit and offset
        $countStmt->execute($countParams);
        $totalPosts = $countStmt->fetchColumn();

        $response = [
            'success' => true,
            'posts' => $posts,
            'total' => $totalPosts,
            'hasMore' => ($offset + $limit) < $totalPosts
        ];
        
        // Debug: Log response
        error_log("API Response: " . json_encode($response));
        
        echo json_encode($response);

    } catch (Exception $e) {
        error_log("Error in company_job_posts_handler.php: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Server error']);
    }
}
?>

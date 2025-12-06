<?php
// job_seeking_handler.php - Handle job seeking post creation and retrieval

error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once 'Database.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle job seeking post operations
    try {
        if (!isset($pdo) || !$pdo instanceof PDO) {
            throw new Exception('Database connection not available');
        }

        $json_input = file_get_contents('php://input');
        if (empty($json_input)) {
            echo json_encode(['success' => false, 'message' => 'No data received']);
            exit;
        }

        $input = json_decode($json_input, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
            exit;
        }

        $action = $input['action'] ?? '';

        if ($action === 'delete_post') {
            // Handle post deletion
            $postId = $input['postId'] ?? null;
            $candidateId = $input['candidateId'] ?? null;

            if (!$postId || !$candidateId) {
                echo json_encode(['success' => false, 'message' => 'Post ID and Candidate ID are required']);
                exit;
            }

            // Verify the post belongs to the candidate
            $verifyStmt = $pdo->prepare("SELECT PostID FROM job_seeking_posts WHERE PostID = ? AND CandidateID = ?");
            $verifyStmt->execute([$postId, $candidateId]);
            
            if (!$verifyStmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Post not found or you do not have permission to delete it']);
                exit;
            }

            // Delete the post
            $deleteStmt = $pdo->prepare("DELETE FROM job_seeking_posts WHERE PostID = ? AND CandidateID = ?");
            $success = $deleteStmt->execute([$postId, $candidateId]);

            if ($success) {
                echo json_encode(['success' => true, 'message' => 'Post deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete post']);
            }

        } elseif ($action === 'update_post') {
            // Handle post update
            $postId = $input['postId'] ?? null;
            $candidateId = $input['candidateId'] ?? null;

            if (!$postId || !$candidateId) {
                echo json_encode(['success' => false, 'message' => 'Post ID and Candidate ID are required']);
                exit;
            }

            // Validate required fields for update
            $required_fields = ['jobTitle', 'careerGoal', 'keySkills', 'education', 'contactInfo'];
            $missing = [];
            foreach ($required_fields as $field) {
                if (empty(trim($input[$field] ?? ''))) {
                    $missing[] = $field;
                }
            }
            
            if (!empty($missing)) {
                echo json_encode(['success' => false, 'message' => 'Missing required fields: ' . implode(', ', $missing)]);
                exit;
            }

            // Verify the post belongs to the candidate
            $verifyStmt = $pdo->prepare("SELECT PostID FROM job_seeking_posts WHERE PostID = ? AND CandidateID = ?");
            $verifyStmt->execute([$postId, $candidateId]);
            
            if (!$verifyStmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Post not found or you do not have permission to update it']);
                exit;
            }

            // Update the post
            $stmt = $pdo->prepare("
                UPDATE job_seeking_posts 
                SET 
                    JobTitle = ?,
                    CareerGoal = ?,
                    KeySkills = ?,
                    Experience = ?,
                    Education = ?,
                    SoftSkills = ?,
                    ValueToEmployer = ?,
                    ContactInfo = ?,
                    UpdatedAt = NOW()
                WHERE PostID = ? AND CandidateID = ?
            ");
            
            $success = $stmt->execute([
                trim($input['jobTitle']),
                trim($input['careerGoal']),
                trim($input['keySkills']),
                trim($input['experience'] ?? ''),
                trim($input['education']),
                trim($input['softSkills'] ?? ''),
                trim($input['valueToEmployer'] ?? ''),
                trim($input['contactInfo']),
                $postId,
                $candidateId
            ]);
            
            if ($success) {
                echo json_encode(['success' => true, 'message' => 'Post updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update post']);
            }

        } else {
            // Handle job seeking post creation
            // Validate required fields
            $required_fields = ['candidateId', 'jobTitle', 'careerGoal', 'keySkills', 'education', 'contactInfo'];
            $missing = [];
            foreach ($required_fields as $field) {
                if (empty(trim($input[$field] ?? ''))) {
                    $missing[] = $field;
                }
            }
            
            if (!empty($missing)) {
                echo json_encode(['success' => false, 'message' => 'Missing required fields: ' . implode(', ', $missing)]);
                exit;
            }

            // Check for duplicate request (prevent double submission)
            $requestId = $input['requestId'] ?? null;
            $clientSubmissionId = $input['clientSubmissionId'] ?? null;
            
            if ($requestId || $clientSubmissionId) {
                // Simple in-memory cache to prevent duplicate requests within 30 seconds
                session_start();
                $processedRequests = $_SESSION['processed_job_seeking_requests'] ?? [];
                $processedClientIds = $_SESSION['processed_client_submission_ids'] ?? [];
                
                // Check both request ID and client submission ID
                if (in_array($requestId, $processedRequests) || in_array($clientSubmissionId, $processedClientIds)) {
                    error_log("WARNING: Duplicate submission detected - RequestID: $requestId, ClientID: $clientSubmissionId");
                    echo json_encode(['success' => false, 'message' => 'Duplicate request detected. Please wait before submitting again.']);
                    exit;
                }
                
                // Add both IDs to processed lists
                if ($requestId) {
                    $processedRequests[] = $requestId;
                    // Keep only last 100 requests to prevent memory issues
                    if (count($processedRequests) > 100) {
                        $processedRequests = array_slice($processedRequests, -100);
                    }
                    $_SESSION['processed_job_seeking_requests'] = $processedRequests;
                }
                
                if ($clientSubmissionId) {
                    $processedClientIds[] = $clientSubmissionId;
                    // Keep only last 100 client IDs to prevent memory issues
                    if (count($processedClientIds) > 100) {
                        $processedClientIds = array_slice($processedClientIds, -100);
                    }
                    $_SESSION['processed_client_submission_ids'] = $processedClientIds;
                }
            }

            $candidateId = $input['candidateId'];
            $jobTitle = trim($input['jobTitle']);
            $careerGoal = trim($input['careerGoal']);
            $keySkills = trim($input['keySkills']);
            $experience = trim($input['experience'] ?? '');
            $education = trim($input['education']);
            $softSkills = trim($input['softSkills'] ?? '');
            $valueToEmployer = trim($input['valueToEmployer'] ?? '');
            $contactInfo = trim($input['contactInfo']);
            
            // Log the client submission ID for tracking
            $clientSubmissionId = $input['clientSubmissionId'] ?? 'unknown';
            error_log("DEBUG: Processing job seeking post creation for candidate $candidateId with client ID: $clientSubmissionId");

            // Allow multiple job seeking posts per candidate
            // Removed duplicate check to allow multiple posts

            // Insert job seeking post into database
            $stmt = $pdo->prepare("
                INSERT INTO job_seeking_posts 
                (CandidateID, JobTitle, CareerGoal, KeySkills, Experience, Education, SoftSkills, ValueToEmployer, ContactInfo, CreatedAt) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $success = $stmt->execute([
                $candidateId, 
                $jobTitle, 
                $careerGoal, 
                $keySkills, 
                $experience, 
                $education, 
                $softSkills, 
                $valueToEmployer, 
                $contactInfo
            ]);
            
            if ($success) {
                $postId = $pdo->lastInsertId();
                
                // Debug: Log the created post
                error_log("DEBUG: Created job seeking post with ID: $postId for candidate: $candidateId");
                
                echo json_encode([
                    'success' => true, 
                    'message' => 'Job seeking post created successfully',
                    'postId' => $postId
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to create job seeking post']);
            }
        }

    } catch (Exception $e) {
        error_log("Error in job_seeking_handler.php: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Server error']);
    }
} 
elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Handle job seeking post retrieval
    try {
        if (!isset($pdo) || !$pdo instanceof PDO) {
            throw new Exception('Database connection not available');
        }

        $candidateId = $_GET['candidateId'] ?? null;
        $limit = $_GET['limit'] ?? 10;
        $offset = $_GET['offset'] ?? 0;
        $myPosts = $_GET['myPosts'] ?? false;

        // Build query based on whether we want all posts or just candidate's posts
        if ($myPosts && $candidateId) {
            // Get only the candidate's posts
            $stmt = $pdo->prepare("
                SELECT 
                    j.PostID,
                    j.JobTitle,
                    j.CareerGoal,
                    j.KeySkills,
                    j.Experience,
                    j.Education,
                    j.SoftSkills,
                    j.ValueToEmployer,
                    j.ContactInfo,
                    j.Status,
                    j.CreatedAt,
                    j.Views,
                    j.Applications,
                    c.FullName,
                    c.Email,
                    c.Skills as ProfileSkills,
                    c.ProfilePicture,
                    c.CandidateID
                FROM job_seeking_posts j
                JOIN candidate_login_info c ON j.CandidateID = c.CandidateID
                WHERE j.CandidateID = ?
                ORDER BY j.CreatedAt DESC
                LIMIT ? OFFSET ?
            ");
            
            $stmt->execute([$candidateId, $limit, $offset]);
            $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Debug: Log the posts being returned
            error_log("DEBUG: Posts for candidate $candidateId: " . json_encode($posts));
            
            // Debug: Check for actual database duplicates
            $debugStmt = $pdo->prepare("
                SELECT PostID, COUNT(*) as count 
                FROM job_seeking_posts 
                WHERE CandidateID = ? 
                GROUP BY PostID 
                HAVING COUNT(*) > 1
            ");
            $debugStmt->execute([$candidateId]);
            $duplicates = $debugStmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($duplicates)) {
                error_log("WARNING: Found duplicate PostIDs in database for candidate $candidateId: " . json_encode($duplicates));
            }
            
            // Debug: Check for duplicate PostIDs in result set
            $postIds = array_column($posts, 'PostID');
            $uniquePostIds = array_unique($postIds);
            if (count($postIds) !== count($uniquePostIds)) {
                error_log("WARNING: Duplicate PostIDs found in result set for candidate $candidateId: " . json_encode($postIds));
                
                // Remove duplicates by PostID (keep the first occurrence)
                $seen = [];
                $posts = array_filter($posts, function($post) use (&$seen) {
                    if (in_array($post['PostID'], $seen)) {
                        return false;
                    }
                    $seen[] = $post['PostID'];
                    return true;
                });
                
                error_log("DEBUG: Removed duplicates, now have " . count($posts) . " unique posts");
            }

            // Get total count for pagination
            $countStmt = $pdo->prepare("SELECT COUNT(*) FROM job_seeking_posts WHERE CandidateID = ?");
            $countStmt->execute([$candidateId]);
            $totalPosts = $countStmt->fetchColumn();
        } else {
            // Get all active job seeking posts with search and filters
            $whereConditions = ["j.Status = 'active'"];
            $params = [];
            
            // Search functionality
            $search = $_GET['search'] ?? '';
            if (!empty($search)) {
                $whereConditions[] = "(j.JobTitle LIKE ? OR j.KeySkills LIKE ? OR c.FullName LIKE ? OR j.CareerGoal LIKE ?)";
                $searchParam = "%$search%";
                $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam]);
            }
            
            // Skills filter
            $skills = $_GET['skills'] ?? '';
            if (!empty($skills)) {
                $skillsArray = array_map('trim', explode(',', $skills));
                $skillsConditions = [];
                foreach ($skillsArray as $skill) {
                    $skillsConditions[] = "j.KeySkills LIKE ?";
                    $params[] = "%$skill%";
                }
                if (!empty($skillsConditions)) {
                    $whereConditions[] = "(" . implode(' OR ', $skillsConditions) . ")";
                }
            }
            
            // Location filter
            $location = $_GET['location'] ?? '';
            if (!empty($location)) {
                $whereConditions[] = "c.Location LIKE ?";
                $params[] = "%$location%";
            }
            
            // Experience filter
            $experience = $_GET['experience'] ?? '';
            if (!empty($experience)) {
                $experienceYears = [
                    'entry' => [0, 2],
                    'mid' => [2, 5],
                    'senior' => [5, 100]
                ];
                
                if (isset($experienceYears[$experience])) {
                    $minYears = $experienceYears[$experience][0];
                    $maxYears = $experienceYears[$experience][1];
                    $whereConditions[] = "(j.Experience LIKE ? OR j.Experience LIKE ?)";
                    $params[] = "%$minYears%";
                    $params[] = "%$maxYears%";
                }
            }
            
            // Education filter
            $education = $_GET['education'] ?? '';
            if (!empty($education)) {
                $whereConditions[] = "j.Education LIKE ?";
                $params[] = "%$education%";
            }
            
            $whereClause = implode(' AND ', $whereConditions);
            
            $stmt = $pdo->prepare("
                SELECT 
                    j.PostID,
                    j.JobTitle,
                    j.CareerGoal,
                    j.KeySkills,
                    j.Experience,
                    j.Education,
                    j.SoftSkills,
                    j.ValueToEmployer,
                    j.ContactInfo,
                    j.Status,
                    j.CreatedAt,
                    j.Views,
                    j.Applications,
                    c.FullName,
                    c.Email,
                    c.Skills as ProfileSkills,
                    c.Location,
                    c.ProfilePicture,
                    c.CandidateID
                FROM job_seeking_posts j
                JOIN candidate_login_info c ON j.CandidateID = c.CandidateID
                WHERE $whereClause
                ORDER BY j.CreatedAt DESC
                LIMIT ? OFFSET ?
            ");
            
            $params[] = $limit;
            $params[] = $offset;
            $stmt->execute($params);
            $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Get total count for pagination
            $countStmt = $pdo->prepare("
                SELECT COUNT(*) 
                FROM job_seeking_posts j
                JOIN candidate_login_info c ON j.CandidateID = c.CandidateID
                WHERE $whereClause
            ");
            $countParams = array_slice($params, 0, -2); // Remove limit and offset
            $countStmt->execute($countParams);
            $totalPosts = $countStmt->fetchColumn();
        }

        echo json_encode([
            'success' => true,
            'posts' => $posts,
            'total' => $totalPosts,
            'hasMore' => ($offset + $limit) < $totalPosts
        ]);

    } catch (Exception $e) {
        error_log("Error in job_seeking_handler.php: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Server error']);
    }
}
?>

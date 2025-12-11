<?php
// post_handler.php - Handle post creation and retrieval

error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once 'Database.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle post creation
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

        // Validate required fields
        if (empty(trim($input['content'] ?? ''))) {
            echo json_encode(['success' => false, 'message' => 'Post content is required']);
            exit;
        }

        $candidateId = $input['candidateId'] ?? null;
        if (!$candidateId) {
            echo json_encode(['success' => false, 'message' => 'Candidate ID is required']);
            exit;
        }

        $content = trim($input['content']);
        $postType = $input['postType'] ?? 'general'; // general, job_seeking, update

        // Insert post into database
        $stmt = $pdo->prepare("
            INSERT INTO candidate_posts 
            (CandidateID, Content, PostType, CreatedAt) 
            VALUES (?, ?, ?, NOW())
        ");
        
        $success = $stmt->execute([$candidateId, $content, $postType]);
        
        if ($success) {
            $postId = $pdo->lastInsertId();
            echo json_encode([
                'success' => true, 
                'message' => 'Post created successfully',
                'postId' => $postId
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create post']);
        }

    } catch (Exception $e) {
        error_log("Error in post_handler.php: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Server error']);
    }
} 
elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Handle post retrieval
    try {
        if (!isset($pdo) || !$pdo instanceof PDO) {
            throw new Exception('Database connection not available');
        }

        $candidateId = $_GET['candidateId'] ?? null;
        $limit = $_GET['limit'] ?? 10;
        $offset = $_GET['offset'] ?? 0;

        // Get posts with candidate information
        $stmt = $pdo->prepare("
            SELECT 
                p.PostID,
                p.Content,
                p.PostType,
                p.CreatedAt,
                c.FullName,
                c.Email,
                c.Skills
            FROM candidate_posts p
            JOIN candidate_login_info c ON p.CandidateID = c.CandidateID
            ORDER BY p.CreatedAt DESC
            LIMIT ? OFFSET ?
        ");
        
        $stmt->execute([$limit, $offset]);
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get total count for pagination
        $countStmt = $pdo->query("SELECT COUNT(*) FROM candidate_posts");
        $totalPosts = $countStmt->fetchColumn();

        echo json_encode([
            'success' => true,
            'posts' => $posts,
            'total' => $totalPosts,
            'hasMore' => ($offset + $limit) < $totalPosts
        ]);

    } catch (Exception $e) {
        error_log("Error in post_handler.php: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Server error']);
    }
}
?>

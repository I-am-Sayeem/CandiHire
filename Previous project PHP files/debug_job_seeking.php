<?php
// debug_job_seeking.php - Simple debug script to test job seeking posts

require_once 'Database.php';

header('Content-Type: application/json');

try {
    if (!isset($pdo) || !$pdo instanceof PDO) {
        throw new Exception('Database connection not available');
    }

    // Get candidate ID from URL parameter
    $candidateId = $_GET['candidateId'] ?? null;
    
    if (!$candidateId) {
        echo json_encode(['error' => 'Candidate ID required']);
        exit;
    }

    // Query to get all posts for this candidate
    $stmt = $pdo->prepare("
        SELECT 
            PostID,
            JobTitle,
            CareerGoal,
            KeySkills,
            Experience,
            Education,
            SoftSkills,
            ValueToEmployer,
            ContactInfo,
            Status,
            CreatedAt,
            Views,
            Applications
        FROM job_seeking_posts 
        WHERE CandidateID = ? 
        ORDER BY CreatedAt DESC
    ");
    
    $stmt->execute([$candidateId]);
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Check for duplicates
    $postIds = array_column($posts, 'PostID');
    $uniquePostIds = array_unique($postIds);
    $hasDuplicates = count($postIds) !== count($uniquePostIds);

    // Get detailed info about each post
    $postDetails = [];
    foreach ($posts as $post) {
        $postDetails[] = [
            'PostID' => $post['PostID'],
            'JobTitle' => $post['JobTitle'],
            'CreatedAt' => $post['CreatedAt'],
            'Status' => $post['Status']
        ];
    }

    // Add cleanup option if duplicates exist
    $cleanupAction = $_GET['action'] ?? '';
    if ($cleanupAction === 'cleanup' && $hasDuplicates) {
        // Keep only the first occurrence of each duplicate (by PostID)
        $keepPostIds = array_values($uniquePostIds);
        
        // Delete duplicate posts (keep the first occurrence)
        $deleteStmt = $pdo->prepare("
            DELETE FROM job_seeking_posts 
            WHERE CandidateID = ? 
            AND PostID NOT IN (" . implode(',', array_fill(0, count($keepPostIds), '?')) . ")
        ");
        
        $params = array_merge([$candidateId], $keepPostIds);
        $deleteStmt->execute($params);
        
        echo json_encode([
            'success' => true,
            'message' => 'Cleanup completed',
            'candidateId' => $candidateId,
            'deletedPosts' => count($posts) - count($keepPostIds),
            'remainingPosts' => count($keepPostIds)
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'candidateId' => $candidateId,
            'totalPosts' => count($posts),
            'uniquePosts' => count($uniquePostIds),
            'hasDuplicates' => $hasDuplicates,
            'postIds' => $postIds,
            'uniquePostIds' => array_values($uniquePostIds),
            'posts' => $postDetails,
            'cleanupUrl' => "debug_job_seeking.php?candidateId=$candidateId&action=cleanup"
        ]);
    }

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>

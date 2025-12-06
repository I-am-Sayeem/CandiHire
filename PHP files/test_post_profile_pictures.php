<?php
// test_post_profile_pictures.php - Test script to verify profile pictures in posts

require_once 'Database.php';

header('Content-Type: application/json');

try {
    if (!isset($pdo) || !$pdo instanceof PDO) {
        throw new Exception('Database connection not available');
    }

    // Test the same query used in job_seeking_handler.php
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
        WHERE j.Status = 'active'
        ORDER BY j.CreatedAt DESC
        LIMIT 5
    ");
    
    $stmt->execute();
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Check profile pictures in posts
    $postsWithPictures = 0;
    $postsWithoutPictures = 0;
    $samplePosts = [];

    foreach ($posts as $post) {
        if (!empty($post['ProfilePicture'])) {
            $postsWithPictures++;
        } else {
            $postsWithoutPictures++;
        }
        
        // Collect sample data
        $samplePosts[] = [
            'postId' => $post['PostID'],
            'candidateName' => $post['FullName'],
            'candidateId' => $post['CandidateID'],
            'hasProfilePicture' => !empty($post['ProfilePicture']),
            'profilePicturePath' => $post['ProfilePicture'],
            'postTitle' => $post['JobTitle'],
            'createdAt' => $post['CreatedAt']
        ];
    }

    // Test profile pictures in candidate_login_info table
    $stmt = $pdo->prepare("
        SELECT 
            CandidateID, 
            FullName, 
            ProfilePicture,
            UpdatedAt
        FROM candidate_login_info 
        WHERE IsActive = 1 
        ORDER BY UpdatedAt DESC 
        LIMIT 10
    ");
    $stmt->execute();
    $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $candidatesWithPictures = 0;
    $candidatesWithoutPictures = 0;
    $sampleCandidates = [];

    foreach ($candidates as $candidate) {
        if (!empty($candidate['ProfilePicture'])) {
            $candidatesWithPictures++;
        } else {
            $candidatesWithoutPictures++;
        }
        
        $sampleCandidates[] = [
            'candidateId' => $candidate['CandidateID'],
            'fullName' => $candidate['FullName'],
            'hasProfilePicture' => !empty($candidate['ProfilePicture']),
            'profilePicturePath' => $candidate['ProfilePicture'],
            'lastUpdated' => $candidate['UpdatedAt']
        ];
    }

    echo json_encode([
        'success' => true,
        'message' => 'Profile pictures in posts test completed',
        'test_results' => [
            'posts_analysis' => [
                'total_posts_checked' => count($posts),
                'posts_with_profile_pictures' => $postsWithPictures,
                'posts_without_profile_pictures' => $postsWithoutPictures,
                'sample_posts' => array_slice($samplePosts, 0, 3)
            ],
            'candidates_analysis' => [
                'total_candidates_checked' => count($candidates),
                'candidates_with_profile_pictures' => $candidatesWithPictures,
                'candidates_without_profile_pictures' => $candidatesWithoutPictures,
                'sample_candidates' => array_slice($sampleCandidates, 0, 3)
            ],
            'file_system_check' => [
                'uploads_profiles_exists' => is_dir('uploads/profiles'),
                'uploads_profiles_writable' => is_dir('uploads/profiles') && is_writable('uploads/profiles'),
                'profile_files_count' => is_dir('uploads/profiles') ? count(array_diff(scandir('uploads/profiles'), ['.', '..', '.htaccess'])) : 0
            ]
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Test failed: ' . $e->getMessage()
    ]);
}
?>

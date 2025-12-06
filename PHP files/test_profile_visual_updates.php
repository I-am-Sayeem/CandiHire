<?php
// test_profile_visual_updates.php - Test script to verify profile visual updates

require_once 'Database.php';

header('Content-Type: application/json');

try {
    if (!isset($pdo) || !$pdo instanceof PDO) {
        throw new Exception('Database connection not available');
    }

    // Test candidate profile data
    $stmt = $pdo->prepare("
        SELECT 
            CandidateID, FullName, ProfilePicture, Location, Summary, 
            LinkedIn, GitHub, Portfolio, UpdatedAt
        FROM candidate_login_info 
        WHERE IsActive = 1 
        ORDER BY UpdatedAt DESC 
        LIMIT 5
    ");
    $stmt->execute();
    $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Test company profile data
    $stmt = $pdo->prepare("
        SELECT 
            CompanyID, CompanyName, Logo, Website, Address, City, 
            State, Country, UpdatedAt
        FROM Company_login_info 
        WHERE IsActive = 1 
        ORDER BY UpdatedAt DESC 
        LIMIT 5
    ");
    $stmt->execute();
    $companies = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Test file upload directories
    $uploadDirs = [
        'uploads/profiles' => is_dir('uploads/profiles') && is_writable('uploads/profiles'),
        'uploads/logos' => is_dir('uploads/logos') && is_writable('uploads/logos')
    ];

    // Check for existing profile pictures and logos
    $profilePictures = [];
    $companyLogos = [];
    
    if (is_dir('uploads/profiles')) {
        $profilePictures = array_diff(scandir('uploads/profiles'), ['.', '..', '.htaccess']);
    }
    
    if (is_dir('uploads/logos')) {
        $companyLogos = array_diff(scandir('uploads/logos'), ['.', '..', '.htaccess']);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Profile visual updates test completed',
        'test_results' => [
            'candidates_with_profiles' => count($candidates),
            'companies_with_profiles' => count($companies),
            'upload_directories_ok' => $uploadDirs,
            'profile_pictures_found' => count($profilePictures),
            'company_logos_found' => count($companyLogos),
            'sample_candidates' => array_map(function($c) {
                return [
                    'id' => $c['CandidateID'],
                    'name' => $c['FullName'],
                    'has_picture' => !empty($c['ProfilePicture']),
                    'picture_path' => $c['ProfilePicture'],
                    'last_updated' => $c['UpdatedAt']
                ];
            }, array_slice($candidates, 0, 3)),
            'sample_companies' => array_map(function($c) {
                return [
                    'id' => $c['CompanyID'],
                    'name' => $c['CompanyName'],
                    'has_logo' => !empty($c['Logo']),
                    'logo_path' => $c['Logo'],
                    'last_updated' => $c['UpdatedAt']
                ];
            }, array_slice($companies, 0, 3))
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Test failed: ' . $e->getMessage()
    ]);
}
?>

<?php
// test_profile_update.php - Test file to verify profile update functionality

require_once 'Database.php';

header('Content-Type: application/json');

try {
    if (!isset($pdo) || !$pdo instanceof PDO) {
        throw new Exception('Database connection not available');
    }

    // Test database connection
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM candidate_login_info");
    $stmt->execute();
    $candidateCount = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM Company_login_info");
    $stmt->execute();
    $companyCount = $stmt->fetch(PDO::FETCH_ASSOC);

    // Test candidate profile update (without file upload)
    $testCandidateId = 1; // Assuming candidate with ID 1 exists
    $testData = [
        'fullName' => 'Test Candidate Updated',
        'phoneNumber' => '+1234567890',
        'workType' => 'full-time',
        'location' => 'Test City, Test Country',
        'skills' => 'PHP, JavaScript, MySQL',
        'summary' => 'Test professional summary',
        'linkedin' => 'https://linkedin.com/in/testcandidate',
        'github' => 'https://github.com/testcandidate',
        'portfolio' => 'https://testcandidate.com'
    ];

    // Check if candidate exists
    $stmt = $pdo->prepare("SELECT * FROM candidate_login_info WHERE CandidateID = ?");
    $stmt->execute([$testCandidateId]);
    $candidate = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($candidate) {
        // Update candidate profile
        $updateFields = [];
        $updateValues = [];
        
        foreach ($testData as $field => $value) {
            $dbField = '';
            switch ($field) {
                case 'fullName': $dbField = 'FullName'; break;
                case 'phoneNumber': $dbField = 'PhoneNumber'; break;
                case 'workType': $dbField = 'WorkType'; break;
                case 'location': $dbField = 'Location'; break;
                case 'skills': $dbField = 'Skills'; break;
                case 'summary': $dbField = 'Summary'; break;
                case 'linkedin': $dbField = 'LinkedIn'; break;
                case 'github': $dbField = 'GitHub'; break;
                case 'portfolio': $dbField = 'Portfolio'; break;
            }
            
            if ($dbField) {
                $updateFields[] = "$dbField = ?";
                $updateValues[] = $value;
            }
        }
        
        $updateFields[] = "UpdatedAt = NOW()";
        $updateValues[] = $testCandidateId;
        
        $sql = "UPDATE candidate_login_info SET " . implode(', ', $updateFields) . " WHERE CandidateID = ?";
        $stmt = $pdo->prepare($sql);
        $success = $stmt->execute($updateValues);
        
        echo json_encode([
            'success' => true,
            'message' => 'Database connection successful',
            'candidate_count' => $candidateCount['count'],
            'company_count' => $companyCount['count'],
            'test_candidate_exists' => $candidate ? true : false,
            'test_update_success' => $success,
            'test_data' => $testData
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'message' => 'Database connection successful',
            'candidate_count' => $candidateCount['count'],
            'company_count' => $companyCount['count'],
            'test_candidate_exists' => false,
            'note' => 'No candidate with ID 1 found for testing'
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . $e->getMessage()
    ]);
}
?>

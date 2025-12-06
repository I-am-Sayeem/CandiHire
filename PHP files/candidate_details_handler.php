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

    $candidateId = filter_input(INPUT_GET, 'candidateId', FILTER_VALIDATE_INT);

    if (!$candidateId) {
        echo json_encode(['success' => false, 'message' => 'Invalid candidate ID']);
        exit;
    }

    // Fetch candidate details
    $stmt = $pdo->prepare("
        SELECT
            c.CandidateID, c.FullName, c.Email, c.PhoneNumber, c.Location,
            c.Skills, c.ProfilePicture, c.Summary, c.WorkType, c.CreatedAt,
            c.Education, c.Institute, c.LinkedIn, c.GitHub, c.Portfolio
        FROM candidate_login_info c
        WHERE c.CandidateID = ?
    ");
    $stmt->execute([$candidateId]);
    $candidate = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$candidate) {
        echo json_encode(['success' => false, 'message' => 'Candidate not found']);
        exit;
    }

    // Fetch candidate's work experience
    $experienceStmt = $pdo->prepare("
        SELECT JobTitle, Company, StartDate, EndDate, Description, Location
        FROM candidate_experience
        WHERE CandidateID = ?
        ORDER BY StartDate DESC
    ");
    $experienceStmt->execute([$candidateId]);
    $experiences = $experienceStmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch candidate's education
    $educationStmt = $pdo->prepare("
        SELECT Degree, Institution, StartYear, EndYear, GPA, Location
        FROM candidate_education
        WHERE CandidateID = ?
        ORDER BY EndYear DESC
    ");
    $educationStmt->execute([$candidateId]);
    $educations = $educationStmt->fetchAll(PDO::FETCH_ASSOC);


    echo json_encode([
        'success' => true,
        'candidate' => [
            'id' => $candidate['CandidateID'],
            'name' => $candidate['FullName'],
            'email' => $candidate['Email'],
            'phone' => $candidate['PhoneNumber'],
            'location' => $candidate['Location'],
            'skills' => $candidate['Skills'],
            'summary' => $candidate['Summary'],
            'workType' => $candidate['WorkType'],
            'profilePicture' => $candidate['ProfilePicture'],
            'joinedDate' => $candidate['CreatedAt'],
            'education' => $candidate['Education'],
            'institute' => $candidate['Institute'],
            'linkedin' => $candidate['LinkedIn'],
            'github' => $candidate['GitHub'],
            'portfolio' => $candidate['Portfolio']
        ],
        'experiences' => $experiences,
        'educations' => $educations
    ]);

} catch (Exception $e) {
    error_log('Error in candidate_details_handler.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>

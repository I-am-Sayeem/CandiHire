<?php
// candidate_profile_handler.php - Handle candidate profile updates

error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once 'Database.php';
require_once 'session_manager.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Get candidate profile data
    try {
        if (!isset($pdo) || !$pdo instanceof PDO) {
            throw new Exception('Database connection not available');
        }

        $candidateId = $_GET['candidateId'] ?? null;
        
        if (!$candidateId) {
            echo json_encode(['success' => false, 'message' => 'Candidate ID required']);
            exit;
        }

        $stmt = $pdo->prepare("
            SELECT 
                CandidateID, FullName, Email, PhoneNumber, WorkType, Skills,
                ProfilePicture, Location, Summary, LinkedIn, GitHub, Portfolio,
                Education, Institute, YearsOfExperience, CreatedAt, UpdatedAt
            FROM candidate_login_info 
            WHERE CandidateID = ? AND IsActive = 1
        ");
        $stmt->execute([$candidateId]);
        $candidate = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$candidate) {
            echo json_encode(['success' => false, 'message' => 'Candidate not found']);
            exit;
        }

        echo json_encode(['success' => true, 'candidate' => $candidate]);

    } catch (Exception $e) {
        error_log("Error in candidate_profile_handler.php (GET): " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Server error']);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update candidate profile
    try {
        if (!isset($pdo) || !$pdo instanceof PDO) {
            throw new Exception('Database connection not available');
        }

        // Handle both JSON and FormData requests
        $input = [];
        
        // Debug logging
        error_log("Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'Not set'));
        error_log("Request method: " . $_SERVER['REQUEST_METHOD']);
        error_log("POST data: " . print_r($_POST, true));
        error_log("FILES data: " . print_r($_FILES, true));
        
        if ($_SERVER['CONTENT_TYPE'] && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
            // Handle JSON request
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
        } else {
            // Handle FormData request
            $input = $_POST;
        }

        error_log("Processed input: " . print_r($input, true));
        $candidateId = $input['candidateId'] ?? null;
        if (!$candidateId) {
            echo json_encode(['success' => false, 'message' => 'Candidate ID required']);
            exit;
        }

        // Validate candidate exists and is active
        $check = $pdo->prepare("SELECT CandidateID FROM candidate_login_info WHERE CandidateID = ? AND IsActive = 1");
        $check->execute([$candidateId]);
        if ($check->rowCount() === 0) {
            echo json_encode(['success' => false, 'message' => 'Candidate not found']);
            exit;
        }

        // Prepare update fields
        $updateFields = [];
        $updateValues = [];

        // Handle file upload for profile picture
        $profilePicture = null;
        if (isset($_FILES['profilePicture']) && $_FILES['profilePicture']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/profiles/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $fileExtension = strtolower(pathinfo($_FILES['profilePicture']['name'], PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (in_array($fileExtension, $allowedExtensions)) {
                // Check file size (max 5MB)
                $maxFileSize = 5 * 1024 * 1024; // 5MB
                if ($_FILES['profilePicture']['size'] <= $maxFileSize) {
                    $fileName = 'candidate_' . $candidateId . '_' . time() . '.' . $fileExtension;
                    $filePath = $uploadDir . $fileName;
                    
                    if (move_uploaded_file($_FILES['profilePicture']['tmp_name'], $filePath)) {
                        $profilePicture = $filePath;
                        $updateFields[] = "ProfilePicture = ?";
                        $updateValues[] = $profilePicture;
                    }
                } else {
                    echo json_encode(['success' => false, 'message' => 'Profile picture file is too large. Maximum size is 5MB.']);
                    exit;
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, JPEG, PNG, and GIF files are allowed.']);
                exit;
            }
        }

        // Handle text fields
        $allowedFields = [
            'fullName', 'phoneNumber', 'workType', 'skills', 'location', 
            'summary', 'linkedin', 'github', 'portfolio', 'education', 'institute', 'yearsOfExperience'
        ];

        foreach ($allowedFields as $field) {
            if (isset($input[$field])) {
                $value = trim($input[$field]);
                
                // Map field names to database columns
                $dbField = '';
                switch ($field) {
                    case 'fullName': $dbField = 'FullName'; break;
                    case 'phoneNumber': $dbField = 'PhoneNumber'; break;
                    case 'workType': $dbField = 'WorkType'; break;
                    case 'skills': $dbField = 'Skills'; break;
                    case 'location': $dbField = 'Location'; break;
                    case 'summary': $dbField = 'Summary'; break;
                    case 'linkedin': $dbField = 'LinkedIn'; break;
                    case 'github': $dbField = 'GitHub'; break;
                    case 'portfolio': $dbField = 'Portfolio'; break;
                    case 'education': $dbField = 'Education'; break;
                    case 'institute': $dbField = 'Institute'; break;
                    case 'yearsOfExperience': $dbField = 'YearsOfExperience'; break;
                }

                if ($dbField) {
                    // Special validation for years of experience
                    if ($field === 'yearsOfExperience') {
                        $value = intval($value);
                        if ($value < 0 || $value > 50) {
                            echo json_encode(['success' => false, 'message' => 'Years of experience must be between 0 and 50']);
                            exit;
                        }
                    }
                    
                    $updateFields[] = "$dbField = ?";
                    $updateValues[] = $value;
                }
            }
        }

        if (empty($updateFields)) {
            echo json_encode(['success' => false, 'message' => 'No valid fields to update']);
            exit;
        }

        // Add UpdatedAt field
        $updateFields[] = "UpdatedAt = NOW()";
        $updateValues[] = $candidateId;

        $sql = "UPDATE candidate_login_info SET " . implode(', ', $updateFields) . " WHERE CandidateID = ?";
        $stmt = $pdo->prepare($sql);
        $success = $stmt->execute($updateValues);

        if ($success) {
            // Get updated profile data
            $stmt = $pdo->prepare("
                SELECT FullName, ProfilePicture FROM candidate_login_info 
                WHERE CandidateID = ?
            ");
            $stmt->execute([$candidateId]);
            $updatedProfile = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Update session if we have the full name
            if ($updatedProfile && isset($updatedProfile['FullName'])) {
                session_start();
                $_SESSION['candidate_name'] = $updatedProfile['FullName'];
            }
            
            echo json_encode([
                'success' => true, 
                'message' => 'Profile updated successfully',
                'profilePicture' => $updatedProfile['ProfilePicture'],
                'fullName' => $updatedProfile['FullName']
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update profile']);
        }

    } catch (Exception $e) {
        error_log("Error in candidate_profile_handler.php (POST): " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Server error']);
    }
}
?>

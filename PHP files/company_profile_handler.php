<?php
// company_profile_handler.php - Handle company profile updates

error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once 'Database.php';
require_once 'session_manager.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Get company profile data
    try {
        if (!isset($pdo) || !$pdo instanceof PDO) {
            throw new Exception('Database connection not available');
        }

        $companyId = $_GET['companyId'] ?? null;
        
        if (!$companyId) {
            echo json_encode(['success' => false, 'message' => 'Company ID required']);
            exit;
        }

        $stmt = $pdo->prepare("
            SELECT 
                CompanyID, CompanyName, Industry, CompanySize, Email, PhoneNumber,
                CompanyDescription, Website, Logo, Address, City, State, Country, PostalCode,
                CreatedAt, UpdatedAt
            FROM Company_login_info 
            WHERE CompanyID = ? AND IsActive = 1
        ");
        $stmt->execute([$companyId]);
        $company = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$company) {
            echo json_encode(['success' => false, 'message' => 'Company not found']);
            exit;
        }

        echo json_encode(['success' => true, 'company' => $company]);

    } catch (Exception $e) {
        error_log("Error in company_profile_handler.php (GET): " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Server error']);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update company profile
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
        $companyId = $input['companyId'] ?? null;
        if (!$companyId) {
            echo json_encode(['success' => false, 'message' => 'Company ID required']);
            exit;
        }

        // Validate company exists and is active
        $check = $pdo->prepare("SELECT CompanyID FROM Company_login_info WHERE CompanyID = ? AND IsActive = 1");
        $check->execute([$companyId]);
        if ($check->rowCount() === 0) {
            echo json_encode(['success' => false, 'message' => 'Company not found']);
            exit;
        }

        // Prepare update fields
        $updateFields = [];
        $updateValues = [];

        // Handle file upload for logo
        $logo = null;
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/logos/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $fileExtension = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (in_array($fileExtension, $allowedExtensions)) {
                // Check file size (max 5MB)
                $maxFileSize = 5 * 1024 * 1024; // 5MB
                if ($_FILES['logo']['size'] <= $maxFileSize) {
                    $fileName = 'company_' . $companyId . '_' . time() . '.' . $fileExtension;
                    $filePath = $uploadDir . $fileName;
                    
                    if (move_uploaded_file($_FILES['logo']['tmp_name'], $filePath)) {
                        $logo = $filePath;
                        $updateFields[] = "Logo = ?";
                        $updateValues[] = $logo;
                    }
                } else {
                    echo json_encode(['success' => false, 'message' => 'Logo file is too large. Maximum size is 5MB.']);
                    exit;
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, JPEG, PNG, and GIF files are allowed.']);
                exit;
            }
        }

        // Handle text fields
        $allowedFields = [
            'companyName', 'industry', 'companySize', 'phoneNumber', 
            'companyDescription', 'website', 'address', 'city', 'state', 'country', 'postalCode'
        ];

        foreach ($allowedFields as $field) {
            if (isset($input[$field])) {
                $value = trim($input[$field]);
                
                // Map field names to database columns
                $dbField = '';
                switch ($field) {
                    case 'companyName': $dbField = 'CompanyName'; break;
                    case 'industry': $dbField = 'Industry'; break;
                    case 'companySize': $dbField = 'CompanySize'; break;
                    case 'phoneNumber': $dbField = 'PhoneNumber'; break;
                    case 'companyDescription': $dbField = 'CompanyDescription'; break;
                    case 'website': $dbField = 'Website'; break;
                    case 'address': $dbField = 'Address'; break;
                    case 'city': $dbField = 'City'; break;
                    case 'state': $dbField = 'State'; break;
                    case 'country': $dbField = 'Country'; break;
                    case 'postalCode': $dbField = 'PostalCode'; break;
                }

                if ($dbField) {
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
        $updateValues[] = $companyId;

        $sql = "UPDATE Company_login_info SET " . implode(', ', $updateFields) . " WHERE CompanyID = ?";
        $stmt = $pdo->prepare($sql);
        $success = $stmt->execute($updateValues);

        if ($success) {
            // Get updated profile data
            $stmt = $pdo->prepare("
                SELECT CompanyName, Logo FROM Company_login_info 
                WHERE CompanyID = ?
            ");
            $stmt->execute([$companyId]);
            $updatedProfile = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Update session if we have the company name
            if ($updatedProfile && isset($updatedProfile['CompanyName'])) {
                session_start();
                $_SESSION['company_name'] = $updatedProfile['CompanyName'];
            }
            
            echo json_encode([
                'success' => true, 
                'message' => 'Profile updated successfully',
                'logo' => $updatedProfile['Logo'],
                'companyName' => $updatedProfile['CompanyName']
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update profile']);
        }

    } catch (Exception $e) {
        error_log("Error in company_profile_handler.php (POST): " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Server error']);
    }
}
?>

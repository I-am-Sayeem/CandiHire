<?php
// CV Processing Handler - Handles EXTERNAL CV files from candidates outside the CandiHire platform
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Start output buffering to catch any unexpected output
ob_start();

require_once 'session_manager.php';
require_once 'Database.php';

header('Content-Type: application/json');

// Function to send clean JSON response
function sendJsonResponse($data) {
    // Clear any output buffer content
    ob_clean();
    
    // Ensure we have valid JSON
    $json = json_encode($data);
    if ($json === false) {
        $json = json_encode(['success' => false, 'message' => 'JSON encoding failed']);
    }
    
    echo $json;
    exit;
}

function debugSystem() {
    $debug = [
        'success' => true,
        'php_version' => phpversion(),
        'upload_dir_exists' => file_exists('uploads/cv_processing/'),
        'upload_dir_writable' => is_writable('uploads/cv_processing/'),
        'shell_exec_available' => function_exists('shell_exec'),
        'pdftotext_available' => false,
        'pdf_extraction_methods' => [
            'method_1' => 'Enhanced regex-based PDF text extraction (Primary)',
            'method_2' => 'pdftotext command line tool (if available)',
            'method_3' => 'Pattern-based text extraction (Fallback)'
        ]
    ];
    
    // Check if pdftotext is available
    if ($debug['shell_exec_available']) {
        $pdftotextCheck = shell_exec('which pdftotext 2>/dev/null');
        $debug['pdftotext_available'] = !empty($pdftotextCheck);
    }
    
    sendJsonResponse($debug);
}

// Check database connection
if (!$pdo) {
    error_log("CV Processing: Database connection failed");
    sendJsonResponse(['success' => false, 'message' => 'Database connection failed. Please check your database configuration.']);
}

// Check if company is logged in
if (!isCompanyLoggedIn()) {
    // Additional check for debugging
    $debugInfo = [
        'session_id' => session_id(),
        'company_id' => $_SESSION['company_id'] ?? 'not set',
        'user_type' => $_SESSION['user_type'] ?? 'not set',
        'session_data' => $_SESSION
    ];
    error_log("CV Processing Auth Debug: " . json_encode($debugInfo));
    sendJsonResponse(['success' => false, 'message' => 'Not authorized. Please log in as a company.', 'debug' => $debugInfo]);
}

// Ensure required tables exist
try {
    $stmt = $pdo->prepare("SHOW TABLES LIKE 'cv_processing_results'");
    $stmt->execute();
    if ($stmt->rowCount() === 0) {
        error_log("CV Processing: Required tables not found, attempting to create them");
        
        // Try to create tables automatically
        $sqlFile = 'create_cv_processing_table.sql';
        if (file_exists($sqlFile)) {
            $sql = file_get_contents($sqlFile);
            $statements = array_filter(
                array_map('trim', explode(';', $sql)),
                function($stmt) {
                    return !empty($stmt) && !preg_match('/^--/', $stmt);
                }
            );
            
            foreach ($statements as $statement) {
                if (!empty($statement)) {
                    try {
                        $pdo->exec($statement);
                    } catch (PDOException $e) {
                        // Table might already exist, which is okay
                        if (strpos($e->getMessage(), 'already exists') === false) {
                            error_log("CV Processing: Error creating table: " . $e->getMessage());
                        }
                    }
                }
            }
            
            // Check again if tables were created
            $stmt = $pdo->prepare("SHOW TABLES LIKE 'cv_processing_results'");
            $stmt->execute();
            if ($stmt->rowCount() === 0) {
                sendJsonResponse(['success' => false, 'message' => 'Failed to create CV processing tables. Please run setup_cv_processing_tables.php manually.']);
            }
        } else {
            sendJsonResponse(['success' => false, 'message' => 'CV processing tables not found and setup file missing.']);
        }
    }
} catch (Exception $e) {
    error_log("CV Processing: Database table check failed: " . $e->getMessage());
    sendJsonResponse(['success' => false, 'message' => 'Database table check failed: ' . $e->getMessage()]);
}

$companyId = getCurrentCompanyId();
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Debug logging
error_log("CV Processing Debug - Action: " . $action);
error_log("CV Processing Debug - Company ID: " . ($companyId ?: 'null'));
error_log("CV Processing Debug - POST data: " . print_r($_POST, true));

try {
    if (empty($action)) {
        sendJsonResponse(['success' => false, 'message' => 'No action specified']);
    }
    
    // Debug action
    if ($action === 'debug') {
        sendJsonResponse([
            'success' => true,
            'message' => 'Debug info',
            'companyId' => $companyId,
            'sessionData' => $_SESSION,
            'postData' => $_POST,
            'getData' => $_GET
        ]);
    }
    
    switch ($action) {
        case 'debug':
            debugSystem();
            break;
        case 'upload_cvs':
            handleCVUpload($companyId);
            break;
        case 'process_cvs':
            handleCVProcessing($companyId);
            break;
        case 'apply_filters':
            handleFilterApplication($companyId);
            break;
        case 'get_processing_status':
            getProcessingStatus($companyId);
            break;
        case 'get_candidates':
            getCandidates($companyId);
            break;
        case 'export_selected':
            exportSelectedCandidates($companyId);
            break;
        case 'test_pdf_extraction':
            testPDFExtraction($companyId);
            break;
        default:
            sendJsonResponse(['success' => false, 'message' => 'Invalid action: ' . $action]);
    }
} catch (Exception $e) {
    error_log("CV Processing Error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    sendJsonResponse(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
} catch (Error $e) {
    error_log("CV Processing Fatal Error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    sendJsonResponse(['success' => false, 'message' => 'Fatal error: ' . $e->getMessage()]);
}

function handleCVUpload($companyId) {
    global $pdo;
    
    if (!$pdo) {
        sendJsonResponse(['success' => false, 'message' => 'Database connection failed']);
    }
    
    if (!isset($_FILES['cvs']) || empty($_FILES['cvs']['name'][0])) {
        sendJsonResponse(['success' => false, 'message' => 'No files uploaded']);
    }
    
    // Validate input data
    $jobPosition = trim($_POST['jobPosition'] ?? '');
    $experienceLevel = $_POST['experienceLevel'] ?? 'any';
    $requiredSkills = trim($_POST['requiredSkills'] ?? '');
    $customCriteria = trim($_POST['customCriteria'] ?? '');
    
    // Validate job position
    if (empty($jobPosition)) {
        sendJsonResponse(['success' => false, 'message' => 'Job position is required']);
    }
    
    // Validate experience level
    $validExperienceLevels = ['any', 'entry', 'mid', 'senior', 'lead'];
    if (!in_array($experienceLevel, $validExperienceLevels)) {
        sendJsonResponse(['success' => false, 'message' => 'Invalid experience level']);
    }
    
    
    // Sanitize input data
    $jobPosition = htmlspecialchars($jobPosition, ENT_QUOTES, 'UTF-8');
    $requiredSkills = htmlspecialchars($requiredSkills, ENT_QUOTES, 'UTF-8');
    $customCriteria = htmlspecialchars($customCriteria, ENT_QUOTES, 'UTF-8');
    
    // Create processing record
    $stmt = $pdo->prepare("
        INSERT INTO cv_processing_results 
        (CompanyID, JobPosition, ExperienceLevel, RequiredSkills, CustomCriteria) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$companyId, $jobPosition, $experienceLevel, $requiredSkills, $customCriteria]);
    $processingId = $pdo->lastInsertId();
    
    // Create upload directory
    $uploadDir = "uploads/cv_processing/{$companyId}/{$processingId}/";
    if (!file_exists($uploadDir)) {
        $created = mkdir($uploadDir, 0777, true);
        if (!$created) {
            error_log("CV Processing: Failed to create upload directory: " . $uploadDir);
            sendJsonResponse(['success' => false, 'message' => 'Failed to create upload directory. Please check file permissions.']);
        }
    }
    
    // Ensure directory is writable
    if (!is_writable($uploadDir)) {
        error_log("CV Processing: Upload directory is not writable: " . $uploadDir);
        sendJsonResponse(['success' => false, 'message' => 'Upload directory is not writable. Please check file permissions.']);
    }
    
    $uploadedFiles = [];
    $fileCount = count($_FILES['cvs']['name']);
    
    for ($i = 0; $i < $fileCount; $i++) {
        if ($_FILES['cvs']['error'][$i] === UPLOAD_ERR_OK) {
            $originalName = $_FILES['cvs']['name'][$i];
            $fileSize = $_FILES['cvs']['size'][$i];
            $tmpName = $_FILES['cvs']['tmp_name'][$i];
            $fileExtension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            
            // Enhanced file validation
            $maxFileSize = 10 * 1024 * 1024; // 10MB limit
            $allowedExtensions = ['pdf'];
            $allowedMimeTypes = ['application/pdf'];
            
            // Check file extension
            if (!in_array($fileExtension, $allowedExtensions)) {
                error_log("File '{$originalName}' rejected - invalid extension: {$fileExtension}");
                continue;
            }
            
            // Check file size
            if ($fileSize > $maxFileSize) {
                error_log("File '{$originalName}' rejected - size too large: {$fileSize} bytes");
                continue;
            }
            
            // Check MIME type (with fallback if finfo is not available)
            $mimeType = null;
            if (function_exists('finfo_open')) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($finfo, $tmpName);
                finfo_close($finfo);
            } else {
                // Fallback: use file extension for MIME type detection
                $mimeType = 'application/pdf'; // Since we only allow PDF files
            }
            
            if ($mimeType && !in_array($mimeType, $allowedMimeTypes)) {
                error_log("File '{$originalName}' rejected - invalid MIME type: {$mimeType}");
                continue;
            }
            
            // Additional security: check file header for PDF
            $handle = fopen($tmpName, 'rb');
            $header = fread($handle, 4);
            fclose($handle);
            
            if (substr($header, 0, 4) !== '%PDF') {
                error_log("File '{$originalName}' failed PDF header validation. Header: " . bin2hex($header));
                // For testing purposes, allow files without proper PDF header
                // In production, you might want to be more strict
                if (strpos($originalName, 'test_') === false) {
                    continue;
                }
            }
            
            // Generate secure filename
            $sanitizedName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
            $storedName = uniqid() . '_' . $sanitizedName;
            $filePath = $uploadDir . $storedName;
            
            $fileMoved = false;
            
            // Try move_uploaded_file first (for real uploads)
            if (move_uploaded_file($tmpName, $filePath)) {
                $fileMoved = true;
            } else {
                // Fallback: use copy for testing or when move_uploaded_file fails
                if (copy($tmpName, $filePath)) {
                    $fileMoved = true;
                } else {
                    $error = error_get_last();
                    error_log("CV Processing: Failed to move/copy file from $tmpName to $filePath. Error: " . ($error['message'] ?? 'Unknown error'));
                    continue; // Skip this file and continue with the next one
                }
            }
            
            if ($fileMoved) {
                // Set proper file permissions
                chmod($filePath, 0644);
                
                // Store file info in database
                try {
                $stmt = $pdo->prepare("
                    INSERT INTO cv_files 
                    (ProcessingID, OriginalFileName, StoredFileName, FilePath, FileSize) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $processingId, 
                    $originalName, 
                    $storedName, 
                    $filePath, 
                    $fileSize
                ]);
                } catch (PDOException $e) {
                    error_log("CV Processing: Failed to store file info in database: " . $e->getMessage());
                    // Remove the uploaded file since we can't track it in database
                    unlink($filePath);
                    continue; // Skip this file and continue with the next one
                }
                
                $uploadedFiles[] = [
                    'originalName' => $originalName,
                    'storedName' => $storedName,
                    'filePath' => $filePath
                ];
            }
        }
    }
    
    // Check if any files were uploaded successfully
    if (empty($uploadedFiles)) {
        error_log("CV Processing: No files were uploaded successfully");
        sendJsonResponse(['success' => false, 'message' => 'No files were uploaded successfully. Please check file format and permissions.']);
    }
    
    sendJsonResponse([
        'success' => true, 
        'message' => count($uploadedFiles) . ' CV file(s) uploaded successfully',
        'processingId' => $processingId,
        'uploadedFiles' => $uploadedFiles
    ]);
}

function handleCVProcessing($companyId) {
    global $pdo;
    
    if (!$pdo) {
        sendJsonResponse(['success' => false, 'message' => 'Database connection failed']);
    }
    
    $processingId = $_POST['processingId'] ?? null;
    
    if (!$processingId) {
        sendJsonResponse(['success' => false, 'message' => 'Processing ID required']);
    }
    
    // Update status to processing
    $stmt = $pdo->prepare("UPDATE cv_processing_results SET Status = 'processing' WHERE ProcessingID = ? AND CompanyID = ?");
    $stmt->execute([$processingId, $companyId]);
    
    // Get processing requirements
    try {
    $stmt = $pdo->prepare("
        SELECT * FROM cv_processing_results 
        WHERE ProcessingID = ? AND CompanyID = ?
    ");
    $stmt->execute([$processingId, $companyId]);
    $processing = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$processing) {
            error_log("CV Processing: Processing record not found for ID: $processingId, Company: $companyId");
        sendJsonResponse(['success' => false, 'message' => 'Processing record not found']);
    }
    
    // Get uploaded files
    $stmt = $pdo->prepare("SELECT * FROM cv_files WHERE ProcessingID = ?");
    $stmt->execute([$processingId]);
    $files = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        if (empty($files)) {
            error_log("CV Processing: No files found for processing ID: $processingId");
            sendJsonResponse(['success' => false, 'message' => 'No files found for processing']);
        }
    } catch (PDOException $e) {
        error_log("CV Processing: Database error in handleCVProcessing: " . $e->getMessage());
        sendJsonResponse(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    
    // Process CV files
    $processedCandidates = [];
    
    error_log("CV Processing: Starting to process " . count($files) . " files");
    
    foreach ($files as $file) {
        error_log("CV Processing: Processing file: " . $file['OriginalFileName']);
        error_log("CV Processing: File path: " . $file['FilePath']);
        error_log("CV Processing: File exists: " . (file_exists($file['FilePath']) ? 'YES' : 'NO'));
        
        // Extract actual candidate information from PDF
        $candidateInfo = extractCVData($file, $processing);
        
        // Debug logging
        error_log("CV Processing: Extracted data for " . $file['OriginalFileName'] . ": " . json_encode($candidateInfo));
        
        if ($candidateInfo && !empty($candidateInfo['name'])) {
            try {
                // Store candidate contact info (backward compatible)
                try {
                    // Try with new columns first
                    $stmt = $pdo->prepare("
                        INSERT INTO candidate_contact_info 
                        (ProcessingID, FileID, CandidateName, Email, Phone, LinkedIn, Location, University, Education, ExperienceYears, Experience, Skills, Summary, CustomCriteria, MatchPercentage, FullExtractedText, ExtractionStatus, ProcessingDate) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                    ");
                    $stmt->execute([
                        $processingId,
                        $file['FileID'],
                        $candidateInfo['name'],
                        $candidateInfo['email'],
                        $candidateInfo['phone'],
                        $candidateInfo['linkedin'],
                        $candidateInfo['location'],
                        $candidateInfo['university'] ?? '',
                        $candidateInfo['education'] ?? '',
                        $candidateInfo['experienceYears'],
                        $candidateInfo['experience'] ?? '',
                        $candidateInfo['skills'],
                        $candidateInfo['summary'] ?? '',
                        $candidateInfo['customCriteria'] ?? '',
                        $candidateInfo['matchPercentage'],
                        $candidateInfo['fullText'] ?? '',
                        $candidateInfo['extractionStatus'] ?? 'success'
                    ]);
                } catch (PDOException $e) {
                    // If new columns don't exist, use original schema
                    error_log("CV Processing: New columns not available, using original schema: " . $e->getMessage());
            $stmt = $pdo->prepare("
                INSERT INTO candidate_contact_info 
                (ProcessingID, FileID, CandidateName, Email, Phone, LinkedIn, Location, University, Education, ExperienceYears, Experience, Skills, Summary, CustomCriteria, MatchPercentage) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $processingId,
                $file['FileID'],
                $candidateInfo['name'],
                $candidateInfo['email'],
                $candidateInfo['phone'],
                $candidateInfo['linkedin'],
                $candidateInfo['location'],
                $candidateInfo['university'] ?? '',
                $candidateInfo['education'] ?? '',
                $candidateInfo['experienceYears'],
                $candidateInfo['experience'] ?? '',
                $candidateInfo['skills'],
                $candidateInfo['summary'] ?? '',
                $candidateInfo['customCriteria'] ?? '',
                $candidateInfo['matchPercentage']
            ]);
                }
            
            $processedCandidates[] = $candidateInfo;
            } catch (PDOException $e) {
                error_log("CV Processing: Failed to insert candidate data: " . $e->getMessage());
                // Continue processing other files even if one fails
                continue;
            }
        } else {
            error_log("CV Processing: Failed to extract candidate data from file: " . $file['OriginalFileName']);
        }
        
        // Update file status
        $stmt = $pdo->prepare("UPDATE cv_files SET Status = 'processed' WHERE FileID = ?");
        $stmt->execute([$file['FileID']]);
    }
    
    // Check if any candidates were processed successfully
    if (empty($processedCandidates)) {
        error_log("CV Processing: No candidates were processed successfully");
        
        // Update processing status to failed
        $stmt = $pdo->prepare("UPDATE cv_processing_results SET Status = 'failed' WHERE ProcessingID = ?");
        $stmt->execute([$processingId]);
        
        sendJsonResponse(['success' => false, 'message' => 'Failed to process any CV files. Please check file format and try again.']);
    }
    
    // Update processing status to completed
    $stmt = $pdo->prepare("UPDATE cv_processing_results SET Status = 'completed' WHERE ProcessingID = ?");
    $stmt->execute([$processingId]);
    
    sendJsonResponse([
        'success' => true,
        'message' => 'CVs processed successfully. Found ' . count($processedCandidates) . ' candidate(s).',
        'candidates' => $processedCandidates
    ]);
}

function extractCVData($file, $processing) {
    error_log("CV Processing: Starting CV data extraction for: " . $file['OriginalFileName']);
    
    // Extract text from PDF file (full content from start to end)
    $pdfText = extractTextFromPDF($file['FilePath']);
    
    $candidateData = null;
    $extractionStatus = 'failed';
    
    if (!empty($pdfText)) {
        error_log("CV Processing: PDF text extraction successful, parsing candidate data...");
        // Extract candidate information from the text
        $candidateData = parseCVText($pdfText, $file['OriginalFileName']);
        $extractionStatus = 'success';
        
        // Store the full extracted text in the database for reference
        $candidateData['fullText'] = $pdfText;
        $candidateData['extractionStatus'] = $extractionStatus;
    } else {
        error_log("CV Processing: PDF text extraction failed for: " . $file['OriginalFileName']);
    }
    
    // If text extraction failed or returned empty data, try to extract minimal data from filename
    if (!$candidateData || empty($candidateData['name'])) {
        error_log("CV Processing: PDF text extraction failed for: " . $file['OriginalFileName'] . ", using filename-based extraction");
        
        // Try to extract at least the name from filename
        $candidateData = [
            'name' => extractNameFromFileName($file['OriginalFileName']),
            'email' => '',
            'phone' => '',
            'linkedin' => '',
            'location' => '',
            'university' => '',
            'education' => '',
            'experienceYears' => 0,
            'experience' => '',
            'skills' => '',
            'summary' => 'CV text extraction failed - unable to parse PDF content',
            'customCriteria' => '',
            'fullText' => $pdfText ?? '',
            'extractionStatus' => 'failed'
        ];
    }
    
    // Ensure candidateData is never null
    if (!$candidateData) {
        error_log("CV Processing: Critical error - candidateData is null for: " . $file['OriginalFileName']);
        $candidateData = [
            'name' => extractNameFromFileName($file['OriginalFileName']),
            'email' => '',
            'phone' => '',
            'linkedin' => '',
            'location' => '',
            'university' => '',
            'education' => '',
            'experienceYears' => 0,
            'experience' => '',
            'skills' => '',
            'summary' => 'Critical error in CV processing',
            'customCriteria' => '',
            'fullText' => '',
            'extractionStatus' => 'failed'
        ];
    }
    
    // Calculate match percentage based on actual requirements
    $matchPercentage = calculateMatchPercentage($candidateData, $processing);
    
    // Add match percentage to candidate data
    $candidateData['matchPercentage'] = $matchPercentage;
    
    return $candidateData;
}

function extractTextFromPDF($filePath) {
    $fullPath = $filePath;
    
    if (!file_exists($fullPath)) {
        error_log("CV Processing: PDF file not found: " . $fullPath);
        return '';
    }
    
    $fileSize = filesize($fullPath);
    if ($fileSize > 50 * 1024 * 1024) {
        error_log("CV Processing: PDF file too large: " . $fileSize . " bytes");
        return '';
    }
    
    error_log("CV Processing: Starting PDF extraction for: " . basename($fullPath));
    error_log("CV Processing: File size: " . $fileSize . " bytes");
    
    // Method 1: Try pdftotext command (most reliable)
    if (function_exists('shell_exec')) {
        $escapedPath = escapeshellarg($fullPath);
        $text = shell_exec("pdftotext -layout $escapedPath - 2>/dev/null");
        if (!empty(trim($text))) {
            error_log("CV Processing: Successfully extracted " . strlen($text) . " characters using pdftotext");
            return trim($text);
        } else {
            error_log("CV Processing: pdftotext returned empty or failed");
            // Check if pdftotext is available
            $pdftotextCheck = shell_exec('which pdftotext 2>/dev/null');
            if (empty($pdftotextCheck)) {
                error_log("CV Processing: pdftotext command not available");
            } else {
                error_log("CV Processing: pdftotext available but extraction failed");
            }
        }
    } else {
        error_log("CV Processing: shell_exec not available");
    }
    
    // Method 2: Read PDF content directly
    $content = @file_get_contents($fullPath);
    if ($content === false) {
        error_log("CV Processing: Failed to read PDF file");
        return '';
    }
    
    error_log("CV Processing: PDF file size: " . strlen($content) . " bytes");
    
    $text = '';
    
    // Extract all text objects from PDF
    // Look for text between BT (Begin Text) and ET (End Text) operators
    if (preg_match_all('/BT\s*(.*?)\s*ET/s', $content, $btMatches)) {
        error_log("CV Processing: Found " . count($btMatches[1]) . " BT/ET text blocks");
        foreach ($btMatches[1] as $textBlock) {
            $extracted = extractTextFromPDFBlock($textBlock);
            if (!empty($extracted)) {
                $text .= $extracted . ' ';
            }
        }
    } else {
        error_log("CV Processing: No BT/ET text blocks found");
    }
    
    // Extract text from parentheses (Tj operator)
    if (preg_match_all('/\(((?:[^()\\\\]|\\\\.|\\([^)]*\\))*)\)\s*Tj/s', $content, $tjMatches)) {
        error_log("CV Processing: Found " . count($tjMatches[1]) . " Tj operator text strings");
        foreach ($tjMatches[1] as $match) {
            $decoded = decodePDFString($match);
            if (strlen($decoded) > 1) {
                $text .= $decoded . ' ';
            }
        }
    } else {
        error_log("CV Processing: No Tj operator text found");
    }
    
    // Extract text from arrays (TJ operator)
    if (preg_match_all('/\[((?:[^\[\]\\\\]|\\\\.|\\[[^\]]*\\])*)\]\s*TJ/s', $content, $tjArrayMatches)) {
        error_log("CV Processing: Found " . count($tjArrayMatches[1]) . " TJ operator text arrays");
        foreach ($tjArrayMatches[1] as $match) {
            // Extract strings from array
            if (preg_match_all('/\(((?:[^()\\\\]|\\\\.)*)\\)/', $match, $arrayStrings)) {
                foreach ($arrayStrings[1] as $str) {
                    $decoded = decodePDFString($str);
                    if (strlen($decoded) > 1) {
                        $text .= $decoded . ' ';
                    }
                }
            }
        }
    } else {
        error_log("CV Processing: No TJ operator text arrays found");
    }
    
    // Clean extracted text
    $text = cleanExtractedText($text);
    
    if (!empty(trim($text))) {
        error_log("CV Processing: Extracted " . strlen($text) . " characters from PDF content");
        return trim($text);
    } else {
        error_log("CV Processing: No text extracted from PDF content methods");
    }
    
    // Fallback: Try to extract any readable text
    $text = extractReadableText($content);
    
    if (!empty(trim($text))) {
        error_log("CV Processing: Fallback extraction got " . strlen($text) . " characters");
        return trim($text);
    }
    
    error_log("CV Processing: All extraction methods failed for " . basename($fullPath));
    return '';
}

// Helper function to extract text from PDF text block
function extractTextFromPDFBlock($block) {
    $text = '';
    
    // Extract text from Tj operator (single string)
    if (preg_match_all('/\(((?:[^()\\\\]|\\\\.)*)\\)\s*Tj/', $block, $matches)) {
        foreach ($matches[1] as $match) {
            $text .= decodePDFString($match) . ' ';
        }
    }
    
    // Extract text from TJ operator (array of strings)
    if (preg_match_all('/\[((?:[^\[\]])*)\]\s*TJ/', $block, $matches)) {
        foreach ($matches[1] as $match) {
            if (preg_match_all('/\(((?:[^()\\\\]|\\\\.)*)\\)/', $match, $strings)) {
                foreach ($strings[1] as $str) {
                    $text .= decodePDFString($str) . ' ';
                }
            }
        }
    }
    
    return $text;
}

// Improved PDF string decoder
function decodePDFString($str) {
    // Handle PDF escape sequences
    $replacements = [
        '\\n' => "\n",
        '\\r' => "\r",
        '\\t' => "\t",
        '\\b' => "\b",
        '\\f' => "\f",
        '\\(' => '(',
        '\\)' => ')',
        '\\\\' => '\\',
    ];
    
    foreach ($replacements as $search => $replace) {
        $str = str_replace($search, $replace, $str);
    }
    
    // Handle octal escape sequences (\nnn)
    $str = preg_replace_callback('/\\\\([0-7]{1,3})/', function($matches) {
        return chr(octdec($matches[1]));
    }, $str);
    
    return $str;
}

// Extract readable text as fallback
function extractReadableText($content) {
    $text = '';
    $lines = explode("\n", $content);
    
    foreach ($lines as $line) {
        // Look for lines with readable ASCII text (at least 3 chars)
        if (preg_match('/[A-Za-z]{3,}/', $line)) {
            // Extract only the readable parts
            if (preg_match_all('/[A-Za-z][A-Za-z0-9\s@\.\-_,;:]{2,}/', $line, $matches)) {
                foreach ($matches[0] as $match) {
                    $cleaned = trim($match);
                    if (strlen($cleaned) >= 3) {
                        $text .= $cleaned . ' ';
                    }
                }
            }
        }
    }
    
    return $text;
}

// Helper function to decode ASCII85 data
function decodeASCII85($data) {
    try {
        // Remove whitespace and line breaks
        $data = preg_replace('/\s+/', '', $data);
        
        // Remove the ~> end marker if present
        $data = rtrim($data, '~>');
        
        if (empty($data)) {
            return false;
        }
        
        // ASCII85 decoding
        $result = '';
        $len = strlen($data);
        
        for ($i = 0; $i < $len; $i += 5) {
            $group = substr($data, $i, 5);
            $groupLen = strlen($group);
            
            // Pad with 'u' if necessary
            while (strlen($group) < 5) {
                $group .= 'u';
            }
            
            // Convert ASCII85 to binary
            $value = 0;
            for ($j = 0; $j < 5; $j++) {
                $char = ord($group[$j]) - 33;
                if ($char < 0 || $char > 84) {
                    continue 2; // Skip invalid characters
                }
                $value = $value * 85 + $char;
            }
            
            // Convert to 4 bytes
            $bytes = [
                ($value >> 24) & 0xFF,
                ($value >> 16) & 0xFF,
                ($value >> 8) & 0xFF,
                $value & 0xFF
            ];
            
            // Add only the bytes that correspond to original data
            for ($k = 0; $k < $groupLen - 1; $k++) {
                $result .= chr($bytes[$k]);
            }
        }
        
        return $result;
        
    } catch (Exception $e) {
        error_log("CV Processing: ASCII85 decode failed: " . $e->getMessage());
        return false;
    }
}

// Helper function to decompress FlateDecode data
function decompressFlate($data) {
    try {
    if (function_exists('gzuncompress')) {
        $decompressed = @gzuncompress($data);
        if ($decompressed !== false) {
            return $decompressed;
        }
    }
    
    if (function_exists('gzinflate')) {
        $decompressed = @gzinflate($data);
        if ($decompressed !== false) {
            return $decompressed;
        }
        }
    } catch (Exception $e) {
        error_log("CV Processing: Decompression failed: " . $e->getMessage());
    }
    
    return $data; // Return original if decompression fails
}

// Helper function to extract text from stream
function extractTextFromStream($stream) {
    $text = '';
    
    // First, try to extract readable text directly
    if (preg_match_all('/[a-zA-Z0-9@\.\-_\+\(\)\[\]\/\:\s]{3,}/', $stream, $readableMatches)) {
        foreach ($readableMatches[0] as $match) {
            $cleanMatch = trim($match);
            if (strlen($cleanMatch) > 2 && !preg_match('/^[0-9\s]+$/', $cleanMatch)) {
                $text .= $cleanMatch . ' ';
            }
        }
    }
    
    // Extract text from BT...ET blocks
    preg_match_all('/BT\s*(.*?)\s*ET/s', $stream, $btMatches);
    foreach ($btMatches[1] as $block) {
        $text .= extractTextFromBlock($block);
    }
    
    // Extract text from Tj and TJ operators
    preg_match_all('/\((.*?)\)\s*Tj|\[(.*?)\]\s*TJ/', $stream, $textMatches);
    for ($i = 1; $i <= 2; $i++) {
        foreach ($textMatches[$i] as $textMatch) {
            if (!empty($textMatch)) {
                $decoded = decodePDFText($textMatch);
                if (strlen($decoded) > 1) {
                    $text .= $decoded . ' ';
                }
            }
        }
    }
    
    // Look for text patterns in the stream
    if (preg_match_all('/[A-Za-z][A-Za-z0-9\s@\.\-_\+\(\)\[\]\/\:]{2,50}/', $stream, $patternMatches)) {
        foreach ($patternMatches[0] as $match) {
            $cleanMatch = trim($match);
            if (strlen($cleanMatch) > 2) {
                $text .= $cleanMatch . ' ';
            }
        }
    }
    
    return $text;
}

// Helper function to extract text from block
function extractTextFromBlock($block) {
    $text = '';
    
    // Extract text from various PDF operators
    preg_match_all('/\((.*?)\)\s*Tj|\[(.*?)\]\s*TJ|\((.*?)\)\s*Tj\s*Tf|\((.*?)\)\s*Tj\s*Tm/', $block, $textMatches);
    
    for ($i = 1; $i <= 4; $i++) {
        foreach ($textMatches[$i] as $textMatch) {
            if (!empty($textMatch)) {
                $text .= decodePDFText($textMatch) . ' ';
            }
        }
    }
    
    return $text;
}

// Helper function to extract text from content
function extractTextFromContent($content) {
    $text = '';
    
    // Look for text in parentheses
    preg_match_all('/\((.*?)\)/', $content, $parenMatches);
    foreach ($parenMatches[1] as $textMatch) {
        if (!empty($textMatch) && strlen($textMatch) > 2) {
            $text .= decodePDFText($textMatch) . ' ';
        }
    }
    
    // Look for text in brackets
    preg_match_all('/\[(.*?)\]/', $content, $bracketMatches);
    foreach ($bracketMatches[1] as $textMatch) {
        if (!empty($textMatch) && strlen($textMatch) > 2) {
            $text .= decodePDFText($textMatch) . ' ';
        }
    }
    
    return $text;
}

// Helper function to decode PDF text (handles common PDF encoding issues)
function decodePDFText($text) {
    // Remove common PDF escape sequences
    $text = str_replace(['\\n', '\\r', '\\t'], [' ', ' ', ' '], $text);
    
    // Decode common PDF text encodings
    $text = str_replace('\\040', ' ', $text);
    $text = str_replace('\\050', '(', $text);
    $text = str_replace('\\051', ')', $text);
    $text = str_replace('\\133', '[', $text);
    $text = str_replace('\\135', ']', $text);
    
    // Remove backslashes
    $text = stripslashes($text);
    
    // Clean up whitespace
    $text = preg_replace('/\s+/', ' ', trim($text));
    
    return $text;
}

// Helper function to clean extracted text
function cleanExtractedText($text) {
    // Remove excessive whitespace
    $text = preg_replace('/\s+/', ' ', $text);
    
    // Remove common PDF artifacts
    $text = preg_replace('/[^\w\s@\.\-_\+\(\)\[\]]/', ' ', $text);
    
    // Remove single characters and numbers that are likely artifacts
    $words = explode(' ', $text);
    $cleanWords = [];
    
    foreach ($words as $word) {
        $word = trim($word);
        if (strlen($word) > 1 && !preg_match('/^\d+$/', $word)) {
            $cleanWords[] = $word;
        }
    }
    
    return implode(' ', $cleanWords);
}

function parseCVText($text, $fileName) {
    error_log("CV Processing: Parsing CV text from: " . $fileName);
    error_log("CV Processing: Text length: " . strlen($text));
    
    // Normalize text
    $text = normalizeText($text);
    
    // Initialize with fallback name from filename
    $candidateData = [
        'name' => extractNameFromFileName($fileName),
        'email' => '',
        'phone' => '',
        'linkedin' => '',
        'location' => '',
        'university' => '',
        'education' => '',
        'experienceYears' => 0,
        'experience' => '',
        'skills' => '',
        'summary' => '',
        'customCriteria' => ''
    ];
    
    // Extract name (try multiple patterns)
    $name = extractCandidateName($text);
    if ($name) {
        $candidateData['name'] = $name;
        error_log("CV Processing: Extracted name: " . $name);
    }
    
    // Extract email
    if (preg_match('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/i', $text, $emailMatch)) {
        $candidateData['email'] = strtolower(trim($emailMatch[0]));
        error_log("CV Processing: Extracted email: " . $candidateData['email']);
    }
    
    // Extract phone (multiple patterns)
    $phonePatterns = [
        '/\+?1?[-.\s]?\(?([0-9]{3})\)?[-.\s]?([0-9]{3})[-.\s]?([0-9]{4})/',
        '/\((\d{3})\)\s*(\d{3})-(\d{4})/',
        '/(\d{3})[-.\s](\d{3})[-.\s](\d{4})/',
        '/\+\d{1,3}[-.\s]?\d{1,4}[-.\s]?\d{1,4}[-.\s]?\d{1,9}/'
    ];
    
    foreach ($phonePatterns as $pattern) {
        if (preg_match($pattern, $text, $phoneMatch)) {
            $candidateData['phone'] = trim($phoneMatch[0]);
            error_log("CV Processing: Extracted phone: " . $candidateData['phone']);
            break;
        }
    }
    
    // Extract LinkedIn
    if (preg_match('/(https?:\/\/)?(www\.)?linkedin\.com\/in\/[a-zA-Z0-9-]+\/?/i', $text, $linkedinMatch)) {
        $candidateData['linkedin'] = $linkedinMatch[0];
        if (!preg_match('/^https?:\/\//', $candidateData['linkedin'])) {
            $candidateData['linkedin'] = 'https://' . $candidateData['linkedin'];
        }
        error_log("CV Processing: Extracted LinkedIn: " . $candidateData['linkedin']);
    }
    
    // Extract location
    if (preg_match('/([A-Z][a-zA-Z\s]+),\s*([A-Z]{2})/i', $text, $locationMatch)) {
        $candidateData['location'] = trim($locationMatch[0]);
        error_log("CV Processing: Extracted location: " . $candidateData['location']);
    }
    
    // Extract skills (both from common skills and Skills section)
    $candidateData['skills'] = extractSkills($text);
    if (!empty($candidateData['skills'])) {
        error_log("CV Processing: Extracted skills: " . $candidateData['skills']);
    }
    
    // Extract experience years
    $expPatterns = [
        '/(\d+)\+?\s*years?\s+(?:of\s+)?(?:experience|exp)/i',
        '/(?:experience|exp)[:\s]+(\d+)\+?\s*years?/i',
        '/(\d+)\s*yrs?\s+(?:experience|exp)/i'
    ];
    
    foreach ($expPatterns as $pattern) {
        if (preg_match($pattern, $text, $expMatch)) {
            $candidateData['experienceYears'] = intval($expMatch[1]);
            error_log("CV Processing: Extracted experience years: " . $candidateData['experienceYears']);
            break;
        }
    }
    
    // Extract summary
    $summaryPatterns = [
        '/(?:professional\s+)?summary[:\s]+([^.]{50,300})/i',
        '/(?:objective|profile)[:\s]+([^.]{50,300})/i',
        '/about\s+(?:me|myself)[:\s]+([^.]{50,300})/i'
    ];
    
    foreach ($summaryPatterns as $pattern) {
        if (preg_match($pattern, $text, $summaryMatch)) {
            $candidateData['summary'] = trim($summaryMatch[1]);
            error_log("CV Processing: Extracted summary: " . substr($candidateData['summary'], 0, 100) . "...");
            break;
        }
    }
    
    // Extract education
    $candidateData['education'] = extractEducation($text);
    $candidateData['university'] = extractUniversity($text);
    
    error_log("CV Processing: Complete extraction for " . $fileName);
    return $candidateData;
}

// Normalize text
function normalizeText($text) {
    // Remove multiple spaces
    $text = preg_replace('/\s+/', ' ', $text);
    // Remove special characters that might interfere with parsing
    $text = preg_replace('/[^\x20-\x7E\n\r]/', '', $text);
    return trim($text);
}

// Extract candidate name
function extractCandidateName($text) {
    $lines = explode("\n", $text);
    
    // Check first 5 lines for name
    foreach (array_slice($lines, 0, 5) as $line) {
        $line = trim($line);
        
        // Skip empty lines and common headers
        if (empty($line) || 
            preg_match('/(resume|cv|curriculum|vitae|contact|email|phone)/i', $line)) {
            continue;
        }
        
        // Pattern: FirstName LastName (possibly with middle name/initial)
        if (preg_match('/^([A-Z][a-z]+(?:\s+[A-Z]\.?)?\s+[A-Z][a-z]+)$/', $line, $match)) {
            return $match[1];
        }
        
        // Pattern: FIRSTNAME LASTNAME (all caps)
        if (preg_match('/^([A-Z]+\s+[A-Z]+)$/', $line, $match)) {
            return ucwords(strtolower($match[1]));
        }
    }
    
    // Fallback: search anywhere in first 1000 chars
    $firstPart = substr($text, 0, 1000);
    if (preg_match('/\b([A-Z][a-z]{2,}\s+[A-Z][a-z]{2,})\b/', $firstPart, $match)) {
        $name = $match[1];
        // Verify it's not a common non-name phrase
        $nonNames = ['Dear Sir', 'Dear Madam', 'Cover Letter', 'Reference Available'];
        if (!in_array($name, $nonNames)) {
            return $name;
        }
    }
    
    return null;
}

// Extract skills
function extractSkills($text) {
    $allSkills = [];
    
    // Common technical skills
    $commonSkills = [
        'JavaScript', 'Python', 'Java', 'React', 'Node.js', 'SQL', 'MongoDB',
        'Vue.js', 'Angular', 'TypeScript', 'AWS', 'Docker', 'Kubernetes',
        'Django', 'Flask', 'PostgreSQL', 'Redis', 'Git', 'Linux', 'HTML',
        'CSS', 'PHP', 'C++', 'C#', 'Ruby', 'Go', 'Swift', 'Kotlin',
        'Machine Learning', 'AI', 'Data Science', 'Analytics', 'DevOps'
    ];
    
    // Find common skills in text
    foreach ($commonSkills as $skill) {
        if (stripos($text, $skill) !== false) {
            $allSkills[] = $skill;
        }
    }
    
    // Extract from Skills section
    if (preg_match('/skills?[:\s]+([^\n]{10,500})/i', $text, $skillsMatch)) {
        $skillsText = $skillsMatch[1];
        // Split by common delimiters
        $extractedSkills = preg_split('/[,;|•·]/', $skillsText);
        foreach ($extractedSkills as $skill) {
            $skill = trim($skill);
            if (strlen($skill) > 2 && strlen($skill) < 50 && !in_array($skill, $allSkills)) {
                $allSkills[] = $skill;
            }
        }
    }
    
    return implode(', ', array_unique($allSkills));
}

// Extract education
function extractEducation($text) {
    $degreePatterns = [
        '/(Bachelor|B\.?S\.?|B\.?A\.?|B\.?E\.?|B\.?Tech)[\s\w]*?(?:in|of)?\s*([^,\n]{5,50})/i',
        '/(Master|M\.?S\.?|M\.?A\.?|M\.?E\.?|M\.?Tech|MBA)[\s\w]*?(?:in|of)?\s*([^,\n]{5,50})/i',
        '/(PhD|Ph\.?D\.?|Doctorate)[\s\w]*?(?:in|of)?\s*([^,\n]{5,50})/i'
    ];
    
    foreach ($degreePatterns as $pattern) {
        if (preg_match($pattern, $text, $match)) {
            return trim($match[0]);
        }
    }
    
    return '';
}

// Extract university
function extractUniversity($text) {
    $universities = [
        'MIT', 'Stanford', 'Harvard', 'Berkeley', 'Carnegie Mellon',
        'University of', 'College of', 'Institute of Technology'
    ];
    
    foreach ($universities as $uni) {
        if (stripos($text, $uni) !== false) {
            // Extract full university name
            if (preg_match('/(' . preg_quote($uni, '/') . '[^,\n]{0,50})/i', $text, $match)) {
                return trim($match[1]);
            }
        }
    }
    
    return '';
}

function calculateMatchPercentage($candidateData, $processing) {
    $matchScore = 0;
    $totalScore = 100;
    
    // Job position match (20 points)
    $jobPosition = strtolower($processing['JobPosition'] ?? '');
    $skillsText = is_array($candidateData['skills']) ? implode(' ', $candidateData['skills']) : $candidateData['skills'];
    $candidateText = strtolower($candidateData['summary'] . ' ' . $skillsText);
    
    if (!empty($jobPosition)) {
        $positionKeywords = explode(' ', $jobPosition);
        $matchedKeywords = 0;
        foreach ($positionKeywords as $keyword) {
            if (strlen($keyword) > 3 && stripos($candidateText, $keyword) !== false) {
                $matchedKeywords++;
            }
        }
        $positionMatch = ($matchedKeywords / count($positionKeywords)) * 20;
        $matchScore += $positionMatch;
    } else {
        $matchScore += 20; // Full points if no specific position
    }
    
    // Skills match (40 points)
    $requiredSkills = $processing['RequiredSkills'] ?? '';
    if (!empty($requiredSkills)) {
        $requiredSkillsArray = array_map('trim', explode(',', $requiredSkills));
        $matchedSkills = 0;
        foreach ($requiredSkillsArray as $skill) {
            $candidateSkillsArray = is_array($candidateData['skills']) ? $candidateData['skills'] : explode(',', $candidateData['skills']);
            if (in_array($skill, $candidateSkillsArray) || 
                stripos($candidateText, $skill) !== false) {
                $matchedSkills++;
            }
        }
        $skillsMatch = ($matchedSkills / count($requiredSkillsArray)) * 40;
        $matchScore += $skillsMatch;
    } else {
        $matchScore += 40; // Full points if no specific skills required
    }
    
    // Add some randomness to avoid identical scores
    $matchScore += rand(-5, 5);
    
    // Experience level match (20 points)
    $requiredExperience = $processing['ExperienceLevel'] ?? '';
    $candidateExperience = $candidateData['experienceYears'];
    
    switch ($requiredExperience) {
        case 'any':
            $matchScore += 20; // Full points for any experience level
            break;
        case 'entry':
            $matchScore += ($candidateExperience <= 2) ? 20 : max(0, 20 - ($candidateExperience - 2) * 5);
            break;
        case 'mid':
            $matchScore += ($candidateExperience >= 2 && $candidateExperience <= 5) ? 20 : max(0, 20 - abs($candidateExperience - 3.5) * 4);
            break;
        case 'senior':
            $matchScore += ($candidateExperience >= 5 && $candidateExperience <= 8) ? 20 : max(0, 20 - abs($candidateExperience - 6.5) * 3);
            break;
        case 'lead':
            $matchScore += ($candidateExperience >= 7) ? 20 : max(0, 20 - (7 - $candidateExperience) * 3);
            break;
        default:
            $matchScore += 20;
    }
    
    // Custom criteria match (20 points)
    $customCriteria = $processing['CustomCriteria'] ?? '';
    if (!empty($customCriteria)) {
        $criteriaKeywords = explode(' ', $customCriteria);
        $matchedCriteria = 0;
        foreach ($criteriaKeywords as $keyword) {
            if (strlen($keyword) > 3 && stripos($candidateText, $keyword) !== false) {
                $matchedCriteria++;
            }
        }
        $criteriaMatch = ($matchedCriteria / count($criteriaKeywords)) * 20;
        $matchScore += $criteriaMatch;
    } else {
        $matchScore += 20; // Full points if no custom criteria
    }
    
    // Ensure match percentage is between 0 and 100
    return max(0, min(100, round($matchScore)));
}

// Helper function to extract name from CV file name
function extractNameFromFileName($fileName) {
    // Remove file extension
    $cleanName = pathinfo($fileName, PATHINFO_FILENAME);
    
    // Replace underscores, dashes, and dots with spaces first
    $cleanName = preg_replace('/[_\-\.]+/', ' ', $cleanName);
    
    // Remove common CV file prefixes and suffixes (case insensitive)
    $cleanName = preg_replace('/\b(resume|cv|curriculum|vitae)\b/i', '', $cleanName);
    $cleanName = preg_replace('/\b(jan|feb|mar|apr|may|jun|jul|aug|sep|oct|nov|dec)\b/i', '', $cleanName);
    $cleanName = preg_replace('/\b(202[0-9]|202[0-9])\b/', '', $cleanName);
    
    // Remove extra spaces and trim
    $cleanName = preg_replace('/\s+/', ' ', trim($cleanName));
    
    // If the name is too short or empty, generate a fallback
    if (strlen($cleanName) < 3) {
        $cleanName = 'Candidate_' . substr(md5($fileName), 0, 6);
    }
    
    return ucwords($cleanName);
}

// Helper function to generate email from name
function generateEmailFromName($name) {
    $cleanName = strtolower(preg_replace('/[^a-zA-Z\s]/', '', $name));
    $nameParts = explode(' ', $cleanName);
    
    if (count($nameParts) >= 2) {
        $email = $nameParts[0] . '.' . $nameParts[1] . '@email.com';
    } else {
        $email = $nameParts[0] . '@email.com';
    }
    
    return $email;
}

// Helper function to generate phone number
function generatePhoneNumber() {
    // Generate more varied phone numbers
    $areaCodes = [212, 415, 206, 512, 617, 312, 213, 303, 305, 503, 404, 214];
    $areaCode = $areaCodes[array_rand($areaCodes)];
    return '+1 (' . $areaCode . ') ' . rand(200, 999) . '-' . rand(1000, 9999);
}

// Helper function to generate LinkedIn URL
function generateLinkedInFromName($name) {
    $cleanName = strtolower(preg_replace('/[^a-zA-Z\s]/', '', $name));
    $nameParts = explode(' ', $cleanName);
    
    if (count($nameParts) >= 2) {
        $linkedin = 'https://linkedin.com/in/' . $nameParts[0] . $nameParts[1];
    } else {
        $linkedin = 'https://linkedin.com/in/' . $nameParts[0];
    }
    
    return $linkedin;
}

// Helper function to generate location
function generateLocation() {
    $locations = [
        'New York, NY', 'San Francisco, CA', 'Seattle, WA', 'Austin, TX',
        'Boston, MA', 'Chicago, IL', 'Los Angeles, CA', 'Denver, CO',
        'Miami, FL', 'Portland, OR', 'Atlanta, GA', 'Dallas, TX'
    ];
    
    return $locations[array_rand($locations)];
}

// Helper function to generate university
function generateUniversity() {
    $universities = [
        'MIT', 'Stanford University', 'Harvard University', 'UC Berkeley',
        'Carnegie Mellon University', 'Georgia Tech', 'University of California',
        'University of Texas', 'University of Michigan', 'University of Illinois',
        'Cornell University', 'Princeton University', 'Yale University',
        'Columbia University', 'University of Washington', 'Purdue University'
    ];
    return $universities[array_rand($universities)];
}

// Helper function to generate skills
function generateSkills($requiredSkills) {
    $allSkills = [
        'JavaScript', 'Python', 'Java', 'React', 'Node.js', 'SQL', 'MongoDB',
        'Vue.js', 'Angular', 'TypeScript', 'AWS', 'Docker', 'Kubernetes',
        'Django', 'Flask', 'PostgreSQL', 'Redis', 'Git', 'Linux', 'HTML',
        'CSS', 'PHP', 'C++', 'C#', 'Ruby', 'Go', 'Swift', 'Kotlin',
        'Machine Learning', 'AI', 'Data Science', 'Analytics', 'DevOps',
        'Frontend', 'Backend', 'Full Stack', 'Mobile Development', 'REST API',
        'GraphQL', 'Microservices', 'CI/CD', 'Jenkins', 'Terraform'
    ];
    
    // If required skills are specified, include most of them (80% chance)
    $skills = [];
    if (!empty($requiredSkills)) {
        $requiredArray = array_map('trim', explode(',', $requiredSkills));
        // Include 2-4 required skills (higher chance of including them)
        $numRequired = min(rand(2, min(4, count($requiredArray))), count($requiredArray));
        if ($numRequired > 0) {
            $selectedRequired = array_rand(array_flip($requiredArray), $numRequired);
            if (!is_array($selectedRequired)) {
                $selectedRequired = [$selectedRequired];
            }
            $skills = array_merge($skills, $selectedRequired);
        }
    }
    
    // Add random additional skills (4-8 skills total)
    $randomCount = max(3, 7 - count($skills));
    $availableSkills = array_diff($allSkills, $skills);
    if (count($availableSkills) > 0) {
        $randomSkills = array_rand(array_flip($availableSkills), min($randomCount, count($availableSkills)));
        if (!is_array($randomSkills)) {
            $randomSkills = [$randomSkills];
        }
        $skills = array_merge($skills, $randomSkills);
    }
    
    return implode(', ', array_unique($skills));
}

// Helper function to generate realistic candidate data when PDF extraction fails
function generateRealisticCandidateData($fileName, $requiredSkills = '') {
    try {
    // Extract name from filename
    $name = extractNameFromFileName($fileName);
    
    // Generate realistic data
    $firstName = explode(' ', $name)[0];
    $lastName = explode(' ', $name)[1] ?? 'Smith';
    
    $experienceYears = rand(1, 8);
    $experienceLevel = getExperienceLevel($experienceYears);
    
        $candidateData = [
        'name' => $name,
        'email' => generateEmail($firstName, $lastName),
        'phone' => generatePhoneNumber(),
        'linkedin' => 'https://linkedin.com/in/' . strtolower($firstName . $lastName),
        'location' => generateLocation(),
        'university' => generateUniversity(),
        'education' => generateEducation(),
        'experienceYears' => $experienceYears,
        'experience' => generateExperienceDescription($experienceYears, $requiredSkills),
        'skills' => generateSkills($requiredSkills),
        'summary' => generateSummary($experienceYears, $requiredSkills),
        'customCriteria' => ''
    ];
        
        error_log("CV Processing: Generated realistic candidate data for: " . $fileName);
        return $candidateData;
        
    } catch (Exception $e) {
        error_log("CV Processing: Error generating realistic candidate data: " . $e->getMessage());
        
        // Fallback minimal data
        return [
            'name' => 'Candidate_' . substr(md5($fileName), 0, 6),
            'email' => 'candidate@email.com',
            'phone' => '+1 (555) 123-4567',
            'linkedin' => 'https://linkedin.com/in/candidate',
            'location' => 'Unknown Location',
            'university' => 'Unknown University',
            'education' => 'Bachelor Degree',
            'experienceYears' => 2,
            'experience' => 'Software development experience',
            'skills' => 'Programming, Development',
            'summary' => 'Experienced software developer',
        'customCriteria' => ''
    ];
    }
}

// Helper function to determine experience level
function getExperienceLevel($years) {
    if ($years <= 2) return 'entry';
    if ($years <= 5) return 'mid';
    if ($years <= 10) return 'senior';
    return 'lead';
}

// Helper function to generate realistic email
function generateEmail($firstName, $lastName) {
    $domains = ['gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com', 'company.com'];
    $domain = $domains[array_rand($domains)];
    $variations = [
        strtolower($firstName . '.' . $lastName),
        strtolower($firstName . $lastName),
        strtolower($firstName . '_' . $lastName),
        strtolower($firstName . substr($lastName, 0, 1))
    ];
    $email = $variations[array_rand($variations)];
    return $email . '@' . $domain;
}

// Helper function to generate education
function generateEducation() {
    $degrees = [
        'Bachelor of Computer Science',
        'Bachelor of Software Engineering',
        'Bachelor of Information Technology',
        'Master of Computer Science',
        'Bachelor of Engineering in Computer Science',
        'Bachelor of Science in Computer Science',
        'Master of Software Engineering'
    ];
    return $degrees[array_rand($degrees)];
}

// Helper function to generate experience description
function generateExperienceDescription($years, $requiredSkills) {
    $roles = [
        'Software Developer',
        'Full Stack Developer',
        'Frontend Developer',
        'Backend Developer',
        'Web Developer',
        'Software Engineer',
        'Mobile Developer',
        'DevOps Engineer'
    ];
    
    $role = $roles[array_rand($roles)];
    $technologies = !empty($requiredSkills) ? $requiredSkills : 'modern web technologies';
    
    return "$role with $years years of experience in $technologies, software development, and creating scalable applications.";
}

// Helper function to generate summary
function generateSummary($years, $requiredSkills) {
    $summaries = [
        "Experienced software developer with $years years of expertise in building robust applications and solving complex technical challenges.",
        "Passionate developer with $years years of experience in modern technologies and a strong foundation in software engineering principles.",
        "Results-driven professional with $years years of experience in software development, focusing on clean code and efficient solutions.",
        "Skilled developer with $years years of experience in various programming languages and frameworks, committed to continuous learning."
    ];
    
    return $summaries[array_rand($summaries)];
}

function matchesRequirements($candidate, $processing) {
    $matches = true;
    $rejectionReasons = [];
    
    // Debug logging
    error_log("CV Processing: Checking candidate: " . ($candidate['CandidateName'] ?? 'Unknown'));
    error_log("CV Processing: Required experience: " . ($processing['ExperienceLevel'] ?? 'any'));
    error_log("CV Processing: Candidate experience: " . ($candidate['ExperienceYears'] ?? 0));
    
    // Check job position relevance (NEW REQUIREMENT)
    $jobPosition = strtolower($processing['JobPosition'] ?? '');
    if (!empty($jobPosition)) {
        $candidateText = strtolower(($candidate['Summary'] ?? '') . ' ' . ($candidate['Experience'] ?? '') . ' ' . ($candidate['Skills'] ?? ''));
        
        // Check if job position keywords appear in candidate's profile
        $positionKeywords = array_filter(array_map('trim', explode(' ', $jobPosition)));
        $matchedPositionKeywords = 0;
        
        foreach ($positionKeywords as $keyword) {
            if (strlen($keyword) > 2 && strpos($candidateText, $keyword) !== false) {
                $matchedPositionKeywords++;
            }
        }
        
        // Require at least 30% of job position keywords to match
        $requiredPositionMatches = ceil(count($positionKeywords) * 0.3);
        
        if ($matchedPositionKeywords < $requiredPositionMatches) {
            $reason = "Job position mismatch - only $matchedPositionKeywords/" . count($positionKeywords) . " position keywords matched (need at least $requiredPositionMatches)";
            error_log("CV Processing: $reason");
            $rejectionReasons[] = $reason;
            $matches = false;
        } else {
            error_log("CV Processing: Job position match - $matchedPositionKeywords/" . count($positionKeywords) . " keywords matched");
        }
    }
    
    // Check experience level (MORE FLEXIBLE)
    $requiredExperience = $processing['ExperienceLevel'] ?? 'any';
    $candidateExperience = intval($candidate['ExperienceYears'] ?? 0);
    
    if ($requiredExperience !== 'any') {
        $experienceMatch = false;
        switch ($requiredExperience) {
            case 'entry':
                // Entry level: 0-2 years (strict)
                $experienceMatch = ($candidateExperience >= 0 && $candidateExperience <= 2);
                break;
            case 'mid':
                // Mid level: 3-6 years (strict)
                $experienceMatch = ($candidateExperience >= 3 && $candidateExperience <= 6);
                break;
            case 'senior':
                // Senior level: 7-12 years (strict)
                $experienceMatch = ($candidateExperience >= 7 && $candidateExperience <= 12);
                break;
            case 'lead':
                // Lead level: 13+ years (strict)
                $experienceMatch = ($candidateExperience >= 13);
                break;
            default:
                // If unknown level, require at least some experience
                $experienceMatch = ($candidateExperience > 0);
        }
    
        if (!$experienceMatch) {
            $reason = "Experience mismatch - Required: $requiredExperience, Candidate: $candidateExperience";
            error_log("CV Processing: $reason");
            $rejectionReasons[] = $reason;
            $matches = false;
        } else {
            error_log("CV Processing: Experience match - Required: $requiredExperience, Candidate: $candidateExperience");
        }
    }
    
    // Check skills (MORE FLEXIBLE - if provided, candidate should have at least one matching skill)
    $requiredSkills = $processing['RequiredSkills'] ?? '';
    if (!empty($requiredSkills)) {
        $requiredSkillsArray = array_map('trim', explode(',', $requiredSkills));
        $candidateSkills = $candidate['Skills'] ?? '';
        $candidateSkillsArray = array_map('trim', explode(',', $candidateSkills));
        
        error_log("CV Processing: Required skills: " . json_encode($requiredSkillsArray));
        error_log("CV Processing: Candidate skills: " . $candidateSkills);
        
        $hasMatchingSkill = false;
        $matchedSkills = [];
        
        foreach ($requiredSkillsArray as $requiredSkill) {
            $requiredSkillLower = strtolower(trim($requiredSkill));
            
            // Check for exact match
            if (in_array($requiredSkillLower, array_map('strtolower', $candidateSkillsArray))) {
                $hasMatchingSkill = true;
                $matchedSkills[] = $requiredSkill;
                continue;
            }
            
            // Check for partial match (case-insensitive)
            foreach ($candidateSkillsArray as $candidateSkill) {
                $candidateSkillLower = strtolower(trim($candidateSkill));
                if (strpos($candidateSkillLower, $requiredSkillLower) !== false || 
                    strpos($requiredSkillLower, $candidateSkillLower) !== false) {
                    $hasMatchingSkill = true;
                    $matchedSkills[] = $requiredSkill;
                break;
            }
            }
            
            if ($hasMatchingSkill) break;
        }
        
        if (!$hasMatchingSkill) {
            $reason = "No matching skills found - Required: " . implode(', ', $requiredSkillsArray);
            error_log("CV Processing: $reason");
            $rejectionReasons[] = $reason;
            $matches = false;
        } else {
            error_log("CV Processing: Matched skills: " . json_encode($matchedSkills));
        }
    }
    
    if (!$matches) {
        return false;
    }
    
    // Check custom criteria (OPTIONAL - if provided, should match in candidate's summary or experience)
    $customCriteria = $processing['CustomCriteria'] ?? '';
    if (!empty($customCriteria)) {
        $candidateText = strtolower(($candidate['Summary'] ?? '') . ' ' . ($candidate['Experience'] ?? '') . ' ' . ($candidate['Skills'] ?? ''));
        $customCriteriaLower = strtolower($customCriteria);
        
        error_log("CV Processing: Custom criteria: " . $customCriteria);
        error_log("CV Processing: Candidate text: " . substr($candidateText, 0, 200) . "...");
        
        // Split custom criteria into keywords
        $keywords = array_filter(array_map('trim', explode(' ', $customCriteriaLower)));
        $matchedKeywords = 0;
        
        foreach ($keywords as $keyword) {
            if (strlen($keyword) > 2 && strpos($candidateText, $keyword) !== false) {
                $matchedKeywords++;
                error_log("CV Processing: Matched keyword: " . $keyword);
            }
        }
        
        error_log("CV Processing: Matched $matchedKeywords out of " . count($keywords) . " keywords");
        
        // Require at least 50% of keywords to match (more strict)
        $requiredMatchPercentage = 0.5;
        $requiredMatches = ceil(count($keywords) * $requiredMatchPercentage);
        
        if ($matchedKeywords < $requiredMatches) {
            $reason = "Custom criteria mismatch - only $matchedKeywords/" . count($keywords) . " keywords matched (need at least $requiredMatches)";
            error_log("CV Processing: $reason");
            $rejectionReasons[] = $reason;
            $matches = false;
        } else {
            error_log("CV Processing: Custom criteria match - $matchedKeywords/" . count($keywords) . " keywords matched");
        }
    }
    
    if ($matches) {
        error_log("CV Processing: Candidate " . ($candidate['CandidateName'] ?? 'Unknown') . " MATCHES requirements");
    } else {
        error_log("CV Processing: Candidate " . ($candidate['CandidateName'] ?? 'Unknown') . " REJECTED - Reasons: " . implode('; ', $rejectionReasons));
    }
    
    return $matches;
}

function handleFilterApplication($companyId) {
    global $pdo;
    
    if (!$pdo) {
        sendJsonResponse(['success' => false, 'message' => 'Database connection failed']);
    }
    
    $processingId = $_POST['processingId'] ?? null;
    
    if (!$processingId) {
        sendJsonResponse(['success' => false, 'message' => 'Processing ID required']);
    }
    
    // Get job requirements from processing record
    $stmt = $pdo->prepare("SELECT * FROM cv_processing_results WHERE ProcessingID = ?");
    $stmt->execute([$processingId]);
    $processing = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$processing) {
        sendJsonResponse(['success' => false, 'message' => 'Processing record not found']);
    }
    
    // Get all candidates for this processing
    $stmt = $pdo->prepare("
        SELECT cci.*, cf.OriginalFileName 
        FROM candidate_contact_info cci
        JOIN cv_files cf ON cci.FileID = cf.FileID
        WHERE cci.ProcessingID = ?
        ORDER BY cci.MatchPercentage DESC
    ");
    $stmt->execute([$processingId]);
    $allCandidates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Filter candidates based on requirements
    $filteredCandidates = [];
    foreach ($allCandidates as $candidate) {
        if (matchesRequirements($candidate, $processing)) {
            $filteredCandidates[] = $candidate;
        }
    }
    
    sendJsonResponse([
        'success' => true,
        'candidates' => $filteredCandidates,
        'totalCandidates' => count($allCandidates),
        'filteredCandidates' => count($filteredCandidates)
    ]);
}

function getProcessingStatus($companyId) {
    global $pdo;
    
    if (!$pdo) {
        sendJsonResponse(['success' => false, 'message' => 'Database connection failed']);
    }
    
    $processingId = $_GET['processingId'] ?? null;
    
    if (!$processingId) {
        sendJsonResponse(['success' => false, 'message' => 'Processing ID required']);
    }
    
    $stmt = $pdo->prepare("
        SELECT Status, ProcessedDate 
        FROM cv_processing_results 
        WHERE ProcessingID = ? AND CompanyID = ?
    ");
    $stmt->execute([$processingId, $companyId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$result) {
        sendJsonResponse(['success' => false, 'message' => 'Processing record not found']);
    }
    
    sendJsonResponse([
        'success' => true,
        'status' => $result['Status'],
        'processedDate' => $result['ProcessedDate']
    ]);
}

function getCandidates($companyId) {
    global $pdo;
    
    if (!$pdo) {
        sendJsonResponse(['success' => false, 'message' => 'Database connection failed']);
    }
    
    $processingId = $_GET['processingId'] ?? null;
    
    if (!$processingId) {
        sendJsonResponse(['success' => false, 'message' => 'Processing ID required']);
    }
    
    // Get job requirements from processing record
    $stmt = $pdo->prepare("SELECT * FROM cv_processing_results WHERE ProcessingID = ?");
    $stmt->execute([$processingId]);
    $processing = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$processing) {
        sendJsonResponse(['success' => false, 'message' => 'Processing record not found']);
    }
    
    // Get all candidates for this processing
    $stmt = $pdo->prepare("
        SELECT cci.*, cf.OriginalFileName 
        FROM candidate_contact_info cci
        JOIN cv_files cf ON cci.FileID = cf.FileID
        WHERE cci.ProcessingID = ?
        ORDER BY cci.MatchPercentage DESC
    ");
    $stmt->execute([$processingId]);
    $allCandidates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Filter candidates based on requirements
    $filteredCandidates = [];
    foreach ($allCandidates as $candidate) {
        if (matchesRequirements($candidate, $processing)) {
            $filteredCandidates[] = $candidate;
        }
    }
    
    sendJsonResponse([
        'success' => true,
        'candidates' => $filteredCandidates,
        'totalCandidates' => count($allCandidates),
        'filteredCandidates' => count($filteredCandidates)
    ]);
}

function exportSelectedCandidates($companyId) {
    global $pdo;
    
    if (!$pdo) {
        sendJsonResponse(['success' => false, 'message' => 'Database connection failed']);
    }
    
    $processingId = $_POST['processingId'] ?? null;
    $selectedCandidates = $_POST['selectedCandidates'] ?? [];
    $jobPosition = $_POST['jobPosition'] ?? 'Unknown Position';
    
    if (!$processingId || empty($selectedCandidates)) {
        sendJsonResponse(['success' => false, 'message' => 'No candidates selected for export']);
    }
    
    // Get selected candidates details
    $placeholders = str_repeat('?,', count($selectedCandidates) - 1) . '?';
    $stmt = $pdo->prepare("
        SELECT cci.*, cf.OriginalFileName 
        FROM candidate_contact_info cci
        JOIN cv_files cf ON cci.FileID = cf.FileID
        WHERE cci.ContactID IN ($placeholders)
    ");
    $stmt->execute($selectedCandidates);
    $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Generate PDF
    $pdfContent = generateCandidatePDF($candidates, $jobPosition);
    
    // Save PDF file
    $exportDir = "uploads/cv_exports/{$companyId}/";
    if (!file_exists($exportDir)) {
        mkdir($exportDir, 0777, true);
    }
    
    $fileName = 'SelectedCandidates_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $jobPosition) . '_' . date('Y-m-d_H-i-s') . '.pdf';
    $filePath = $exportDir . $fileName;
    
    file_put_contents($filePath, $pdfContent);
    
    // Record export in database
    $stmt = $pdo->prepare("
        INSERT INTO selected_candidates_export 
        (ProcessingID, JobPosition, ExportFileName, ExportFilePath, CandidateCount) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$processingId, $jobPosition, $fileName, $filePath, count($candidates)]);
    
    sendJsonResponse([
        'success' => true,
        'message' => 'Export completed successfully',
        'fileName' => $fileName,
        'filePath' => $filePath,
        'downloadUrl' => $filePath
    ]);
}

function generateCandidatePDF($candidates, $jobPosition) {
    // Simple PDF generation (in real implementation, use a proper PDF library like TCPDF or FPDF)
    $html = "
    <!DOCTYPE html>
    <html>
    <head>
        <title>Selected Candidates - $jobPosition</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .header { text-align: center; margin-bottom: 30px; }
            .candidate { margin-bottom: 25px; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
            .candidate-name { font-size: 18px; font-weight: bold; color: #333; margin-bottom: 10px; }
            .contact-info { margin-bottom: 8px; }
            .skills { margin-top: 10px; }
            .match-percentage { float: right; background: #4CAF50; color: white; padding: 5px 10px; border-radius: 15px; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='header'>
            <h1>Selected Candidates</h1>
            <h2>Position: $jobPosition</h2>
            <p>Generated on: " . date('Y-m-d H:i:s') . "</p>
        </div>
    ";
    
    foreach ($candidates as $candidate) {
        $html .= "
        <div class='candidate'>
            <div class='match-percentage'>{$candidate['MatchPercentage']}% Match</div>
            <div class='candidate-name'>{$candidate['CandidateName']}</div>
            <div class='contact-info'><strong>Email:</strong> {$candidate['Email']}</div>
            <div class='contact-info'><strong>Phone:</strong> {$candidate['Phone']}</div>
            <div class='contact-info'><strong>Location:</strong> {$candidate['Location']}</div>
            <div class='contact-info'><strong>Experience:</strong> {$candidate['ExperienceYears']} years</div>
            <div class='contact-info'><strong>LinkedIn:</strong> {$candidate['LinkedIn']}</div>
            <div class='skills'><strong>Skills:</strong> {$candidate['Skills']}</div>
        </div>
        ";
    }
    
    $html .= "</body></html>";
    
    // For now, return HTML content (in real implementation, convert to PDF)
    return $html;
}

// Diagnostic function to test PDF extraction
function testPDFExtraction($companyId) {
    if (!isCompanyLoggedIn()) {
        sendJsonResponse(['success' => false, 'message' => 'Not authorized. Please log in as a company.']);
    }
    
    $testFile = $_POST['testFile'] ?? '';
    if (empty($testFile) || !file_exists($testFile)) {
        sendJsonResponse(['success' => false, 'message' => 'Test file not found: ' . $testFile]);
    }
    
    error_log("CV Processing: Testing PDF extraction for: " . $testFile);
    
    $extracted = extractTextFromPDF($testFile);
    
    sendJsonResponse([
        'success' => true,
        'fileSize' => filesize($testFile),
        'extractedLength' => strlen($extracted),
        'extractedText' => substr($extracted, 0, 500), // First 500 chars
        'methods' => [
            'shell_exec_available' => function_exists('shell_exec'),
            'pdftotext_available' => !empty(shell_exec('which pdftotext 2>/dev/null'))
        ],
        'debug' => [
            'file_exists' => file_exists($testFile),
            'file_readable' => is_readable($testFile),
            'file_size' => filesize($testFile)
        ]
    ]);
}
?>



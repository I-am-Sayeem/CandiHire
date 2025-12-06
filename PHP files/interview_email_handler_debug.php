<?php
// interview_email_handler_debug.php - Debug version with better error reporting

// Enable error reporting and logging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Log the request
error_log("Email handler called with method: " . $_SERVER['REQUEST_METHOD']);
error_log("Content type: " . ($_SERVER['CONTENT_TYPE'] ?? 'not set'));

$response = ['debug' => true];

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed. Expected POST, got: ' . $_SERVER['REQUEST_METHOD']);
    }
    
    // Get raw input
    $rawInput = file_get_contents('php://input');
    error_log("Raw input: " . $rawInput);
    
    if (empty($rawInput)) {
        throw new Exception('No input data received');
    }
    
    // Decode JSON
    $input = json_decode($rawInput, true);
    error_log("Decoded input: " . print_r($input, true));
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON: ' . json_last_error_msg());
    }
    
    // Validate required fields
    $requiredFields = ['candidates', 'positionName', 'companyName', 'interviewDate', 'interviewTime', 'meetingLink'];
    foreach ($requiredFields as $field) {
        if (!isset($input[$field]) || empty($input[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }
    
    $candidates = $input['candidates'];
    $positionName = $input['positionName'];
    $companyName = $input['companyName'];
    $interviewDate = $input['interviewDate'];
    $interviewTime = $input['interviewTime'];
    $meetingLink = $input['meetingLink'];
    
    if (!is_array($candidates) || empty($candidates)) {
        throw new Exception('No candidates selected');
    }
    
    $response['data_received'] = [
        'candidates_count' => count($candidates),
        'positionName' => $positionName,
        'companyName' => $companyName,
        'interviewDate' => $interviewDate,
        'interviewTime' => $interviewTime,
        'meetingLink' => $meetingLink
    ];
    
    // Check if mail function is available
    if (!function_exists('mail')) {
        throw new Exception('Mail function is not available on this server');
    }
    
    // Format date and time
    $formattedDate = date('l, F j, Y', strtotime($interviewDate));
    $formattedTime = date('g:i A', strtotime($interviewTime));
    
    // Email configuration
    $fromEmail = 'candihiree@gmail.com';
    $fromName = 'CandiHire Team';
    
    $sentEmails = [];
    $failedEmails = [];
    
    // Test with just one candidate first
    $testCandidate = $candidates[0];
    $candidateEmail = $testCandidate['email'];
    $candidateName = $testCandidate['name'];
    
    // Generate simple email content for testing
    $subject = "Interview Invitation - $positionName Position";
    $emailBody = "Dear $candidateName,\n\nCongratulations! We are pleased to invite you to an interview for the $positionName position at $companyName.\n\nInterview Details:\n- Position: $positionName\n- Date: $formattedDate\n- Time: $formattedTime\n- Meeting Link: $meetingLink\n\nBest regards,\nCandiHire Team";
    
    // Prepare headers
    $headers = [
        'From: ' . $fromName . ' <' . $fromEmail . '>',
        'Reply-To: ' . $fromEmail,
        'Content-Type: text/plain; charset=UTF-8',
        'MIME-Version: 1.0',
        'X-Mailer: PHP/' . phpversion()
    ];
    
    error_log("Attempting to send email to: $candidateEmail");
    error_log("Subject: $subject");
    error_log("Headers: " . implode("\r\n", $headers));
    
    // Send email
    $mailSent = mail($candidateEmail, $subject, $emailBody, implode("\r\n", $headers));
    
    if ($mailSent) {
        $sentEmails[] = [
            'candidate' => $candidateName,
            'email' => $candidateEmail
        ];
        $response['success'] = true;
        $response['message'] = 'Test email sent successfully';
    } else {
        $failedEmails[] = [
            'candidate' => $candidateName,
            'email' => $candidateEmail,
            'error' => 'Mail function returned false'
        ];
        $response['success'] = false;
        $response['message'] = 'Mail function returned false';
    }
    
    $response['sent'] = count($sentEmails);
    $response['failed'] = count($failedEmails);
    $response['sentEmails'] = $sentEmails;
    $response['failedEmails'] = $failedEmails;
    
} catch (Exception $e) {
    error_log("Email handler error: " . $e->getMessage());
    $response['success'] = false;
    $response['message'] = 'Error: ' . $e->getMessage();
    $response['error_details'] = [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ];
}

// Always return response
echo json_encode($response, JSON_PRETTY_PRINT);
?>

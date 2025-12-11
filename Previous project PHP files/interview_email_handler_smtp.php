<?php
// interview_email_handler_smtp.php - Production version with SMTP support

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// SMTP Configuration (Update these with your email service settings)
$smtp_config = [
    'host' => 'smtp.gmail.com',           // Gmail SMTP server
    'port' => 587,                        // Gmail SMTP port
    'username' => 'candihiree@gmail.com', // Your Gmail address
    'password' => 'qelu dbdp xbdc hyqy',    // Gmail App Password
    'encryption' => 'tls',                // Encryption type
    'from_email' => 'candihiree@gmail.com',
    'from_name' => 'CandiHire Team'
];

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed. Expected POST, got: ' . $_SERVER['REQUEST_METHOD']);
    }
    
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
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
    
    // Format date and time
    $formattedDate = date('l, F j, Y', strtotime($interviewDate));
    $formattedTime = date('g:i A', strtotime($interviewTime));
    
    $sentEmails = [];
    $failedEmails = [];
    
    // Process each candidate
    foreach ($candidates as $candidate) {
        if (!isset($candidate['email']) || !isset($candidate['name'])) {
            $failedEmails[] = [
                'candidate' => $candidate['name'] ?? 'Unknown',
                'error' => 'Missing email or name'
            ];
            continue;
        }
        
        $candidateEmail = $candidate['email'];
        $candidateName = $candidate['name'];
        
        // Generate email content
        $subject = "Interview Invitation - $positionName Position";
        $emailBody = generateEmailBody($candidateName, $positionName, $companyName, $formattedDate, $formattedTime, $meetingLink);
        
        // Try to send email using SMTP
        $emailSent = sendEmailSMTP($candidateEmail, $candidateName, $subject, $emailBody, $smtp_config);
        
        if ($emailSent) {
            $sentEmails[] = [
                'candidate' => $candidateName,
                'email' => $candidateEmail
            ];
        } else {
            $failedEmails[] = [
                'candidate' => $candidateName,
                'email' => $candidateEmail,
                'error' => 'Failed to send via SMTP'
            ];
        }
    }
    
    // Return response
    $response = [
        'success' => true,
        'message' => 'Interview invitations processed',
        'sent' => count($sentEmails),
        'failed' => count($failedEmails),
        'sentEmails' => $sentEmails,
        'failedEmails' => $failedEmails
    ];
    
    if (count($failedEmails) > 0) {
        $response['message'] .= ' (' . count($failedEmails) . ' failed)';
    }
    
    echo json_encode($response);
    
} catch (Exception $e) {
    error_log("Interview email error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

function sendEmailSMTP($to, $toName, $subject, $body, $config) {
    // Simple SMTP implementation using fsockopen
    $smtp = fsockopen($config['host'], $config['port'], $errno, $errstr, 30);
    
    if (!$smtp) {
        error_log("SMTP Error: $errstr ($errno)");
        return false;
    }
    
    // Read initial response
    $response = fgets($smtp, 515);
    if (substr($response, 0, 3) != '220') {
        error_log("SMTP Error: $response");
        fclose($smtp);
        return false;
    }
    
    // Send EHLO command
    fputs($smtp, "EHLO " . $_SERVER['HTTP_HOST'] . "\r\n");
    $response = fgets($smtp, 515);
    
    // Start TLS if required
    if ($config['encryption'] == 'tls') {
        fputs($smtp, "STARTTLS\r\n");
        $response = fgets($smtp, 515);
        if (substr($response, 0, 3) != '220') {
            error_log("TLS Error: $response");
            fclose($smtp);
            return false;
        }
        stream_socket_enable_crypto($smtp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
        
        // Send EHLO again after TLS
        fputs($smtp, "EHLO " . $_SERVER['HTTP_HOST'] . "\r\n");
        $response = fgets($smtp, 515);
    }
    
    // Authenticate
    fputs($smtp, "AUTH LOGIN\r\n");
    $response = fgets($smtp, 515);
    if (substr($response, 0, 3) != '334') {
        error_log("AUTH Error: $response");
        fclose($smtp);
        return false;
    }
    
    fputs($smtp, base64_encode($config['username']) . "\r\n");
    $response = fgets($smtp, 515);
    if (substr($response, 0, 3) != '334') {
        error_log("Username Error: $response");
        fclose($smtp);
        return false;
    }
    
    fputs($smtp, base64_encode($config['password']) . "\r\n");
    $response = fgets($smtp, 515);
    if (substr($response, 0, 3) != '235') {
        error_log("Password Error: $response");
        fclose($smtp);
        return false;
    }
    
    // Send MAIL FROM
    fputs($smtp, "MAIL FROM: <" . $config['from_email'] . ">\r\n");
    $response = fgets($smtp, 515);
    if (substr($response, 0, 3) != '250') {
        error_log("MAIL FROM Error: $response");
        fclose($smtp);
        return false;
    }
    
    // Send RCPT TO
    fputs($smtp, "RCPT TO: <$to>\r\n");
    $response = fgets($smtp, 515);
    if (substr($response, 0, 3) != '250') {
        error_log("RCPT TO Error: $response");
        fclose($smtp);
        return false;
    }
    
    // Send DATA
    fputs($smtp, "DATA\r\n");
    $response = fgets($smtp, 515);
    if (substr($response, 0, 3) != '354') {
        error_log("DATA Error: $response");
        fclose($smtp);
        return false;
    }
    
    // Send email headers and body
    $headers = "From: " . $config['from_name'] . " <" . $config['from_email'] . ">\r\n";
    $headers .= "To: $toName <$to>\r\n";
    $headers .= "Subject: $subject\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
    
    fputs($smtp, $headers . "\r\n" . $body . "\r\n.\r\n");
    $response = fgets($smtp, 515);
    if (substr($response, 0, 3) != '250') {
        error_log("Email Send Error: $response");
        fclose($smtp);
        return false;
    }
    
    // Send QUIT
    fputs($smtp, "QUIT\r\n");
    fclose($smtp);
    
    return true;
}

function generateEmailBody($candidateName, $positionName, $companyName, $formattedDate, $formattedTime, $meetingLink) {
    return "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Interview Invitation</title>
        <style>
            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Noto Sans', Helvetica, Arial, sans-serif;
                line-height: 1.6;
                color: #333;
                max-width: 600px;
                margin: 0 auto;
                padding: 20px;
                background-color: #f6f8fa;
            }
            .email-container {
                background-color: #ffffff;
                border-radius: 8px;
                padding: 30px;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            }
            .header {
                text-align: center;
                margin-bottom: 30px;
                padding-bottom: 20px;
                border-bottom: 2px solid #58a6ff;
            }
            .logo {
                font-size: 24px;
                font-weight: bold;
                margin-bottom: 10px;
            }
            .candi {
                color: #58a6ff;
            }
            .hire {
                color: #f59e0b;
            }
            .interview-details {
                background-color: #f6f8fa;
                border-radius: 6px;
                padding: 20px;
                margin: 20px 0;
                border-left: 4px solid #58a6ff;
            }
            .interview-details h4 {
                color: #58a6ff;
                margin-bottom: 15px;
                margin-top: 0;
            }
            .interview-details p {
                margin: 8px 0;
            }
            .meeting-link {
                background-color: #58a6ff;
                color: white;
                padding: 12px 20px;
                border-radius: 6px;
                text-decoration: none;
                display: inline-block;
                margin: 10px 0;
                font-weight: 600;
            }
            .meeting-link:hover {
                background-color: #4a8bdf;
            }
            .footer {
                margin-top: 30px;
                padding-top: 20px;
                border-top: 1px solid #e1e4e8;
                color: #6a737d;
                font-size: 14px;
            }
            .candi-footer {
                color: #58a6ff;
                font-weight: bold;
            }
            .hire-footer {
                color: #f59e0b;
                font-weight: bold;
            }
        </style>
    </head>
    <body>
        <div class='email-container'>
            <div class='header'>
                <div class='logo'>
                    <span class='candi'>Candi</span><span class='hire'>Hire</span>
                </div>
                <h1 style='color: #58a6ff; margin: 0;'>Interview Invitation</h1>
            </div>
            
            <p>Dear <strong>$candidateName</strong>,</p>
            
            <p>Congratulations! We are pleased to invite you to an interview for the <strong>$positionName</strong> position at <strong>$companyName</strong>.</p>
            
            <div class='interview-details'>
                <h4>Interview Details:</h4>
                <p><strong>Position:</strong> $positionName</p>
                <p><strong>Date:</strong> $formattedDate</p>
                <p><strong>Time:</strong> $formattedTime</p>
                <p><strong>Meeting Link:</strong></p>
                <a href='$meetingLink' class='meeting-link' target='_blank'>Join Interview Meeting</a>
            </div>
            
            <p>Please ensure you have a stable internet connection and a quiet environment for the interview. We recommend testing the meeting link beforehand.</p>
            
            <p>If you have any questions or need to reschedule, please contact us as soon as possible.</p>
            
            <p>We look forward to speaking with you!</p>
            
            <div class='footer'>
                <p>Best regards,<br>
                HR Team<br>
                <span class='candi-footer'>Candi</span><span class='hire-footer'>Hire</span></p>
                
                <p style='font-size: 12px; margin-top: 20px;'>
                    This email was sent from <strong>candihiree@gmail.com</strong><br>
                    Please do not reply directly to this email.
                </p>
            </div>
        </div>
    </body>
    </html>
    ";
}
?>

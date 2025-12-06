<?php
// AIMatching.php - AI-Powered Candidate Matching System

error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once 'Database.php';
require_once 'session_manager.php';

// Check if user is logged in as company
if (!isCompanyLoggedIn()) {
    header('Location: Login&Signup.php');
    exit;
}

$company_id = getCurrentCompanyId();
$companyName = $_SESSION['company_name'] ?? 'Company';

// Load company data from database if not in session
$companyLogo = null;
if (!isset($_SESSION['company_name']) || $_SESSION['company_name'] === 'Company') {
    try {
        if (isset($pdo) && $pdo instanceof PDO) {
            $stmt = $pdo->prepare("SELECT CompanyName, Logo FROM Company_login_info WHERE CompanyID = ?");
            $stmt->execute([$company_id]);
            $company_data = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($company_data) {
                $companyName = $company_data['CompanyName'];
                $companyLogo = $company_data['Logo'];
                $_SESSION['company_name'] = $companyName;
            }
        }
    } catch (Exception $e) {
        error_log("Error loading company data: " . $e->getMessage());
    }
} else {
    // Load logo even if name is in session
    try {
        if (isset($pdo) && $pdo instanceof PDO) {
            $stmt = $pdo->prepare("SELECT Logo FROM Company_login_info WHERE CompanyID = ?");
            $stmt->execute([$company_id]);
            $company_data = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($company_data && $company_data['Logo']) {
                $companyLogo = $company_data['Logo'];
            }
        }
    } catch (Exception $e) {
        error_log("Error loading company logo: " . $e->getMessage());
    }
}

// Get company information
try {
    if (!$pdo) {
        throw new Exception('Database connection not available');
    }
    $company_stmt = $pdo->prepare("SELECT CompanyName, Industry, CompanySize FROM Company_login_info WHERE CompanyID = ?");
    $company_stmt->execute([$company_id]);
    $company = $company_stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Error fetching company info: " . $e->getMessage());
    $company = null;
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    // Check session for AJAX requests
    if (!isCompanyLoggedIn()) {
        echo json_encode(['success' => false, 'message' => 'Session expired. Please login again.', 'redirect' => 'Login&Signup.php']);
        exit;
    }
    
    switch ($_POST['action']) {
        case 'search_candidates':
            handleCandidateSearch();
            break;
        case 'send_interview_invitation':
            handleInterviewInvitation();
            break;
        case 'get_candidate_details':
            handleGetCandidateDetails();
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    exit;
}

function handleCandidateSearch() {
    global $pdo;
    
    try {
        // Double-check session is still valid
        if (!isCompanyLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Session expired. Please login again.', 'redirect' => 'Login&Signup.php']);
            return;
        }
        
        if (!$pdo) {
            throw new Exception('Database connection not available');
        }
        
        $education = trim($_POST['education'] ?? '');
        $experience = trim($_POST['experience'] ?? '');
        $skills = trim($_POST['skills'] ?? '');
        $location = trim($_POST['location'] ?? '');
        
        // Build dynamic query
        $where_conditions = [];
        $params = [];
        
        if (!empty($education)) {
            $where_conditions[] = "(Education LIKE ? OR Institute LIKE ?)";
            $params[] = "%$education%";
            $params[] = "%$education%";
        }
        
        if (!empty($skills)) {
            $skill_array = explode(',', $skills);
            $skill_conditions = [];
            foreach ($skill_array as $skill) {
                $trimmed_skill = trim($skill);
                // Use FIND_IN_SET for exact comma-separated value matching
                // This ensures "java" matches "java" but not "JavaScript"
                // AND logic ensures ALL specified skills must be present
                $skill_conditions[] = "FIND_IN_SET(?, Skills) > 0";
                $params[] = $trimmed_skill;
            }
            if (!empty($skill_conditions)) {
                // Use AND logic to require ALL skills instead of ANY skills
                $where_conditions[] = "(" . implode(' AND ', $skill_conditions) . ")";
            }
        }
        
        if (!empty($location)) {
            $where_conditions[] = "Location LIKE ?";
            $params[] = "%$location%";
        }
        
        if (!empty($experience)) {
            $exp_years = intval($experience);
            $where_conditions[] = "YearsOfExperience >= ?";
            $params[] = $exp_years;
        }
        
        
        $where_clause = !empty($where_conditions) ? "WHERE " . implode(' AND ', $where_conditions) : "";
        
        // If no search conditions, return all candidates
        if (empty($where_conditions)) {
            $where_clause = "WHERE IsActive = 1";
        }
        
        $query = "
            SELECT 
                CandidateID,
                FullName,
                Email,
                PhoneNumber,
                WorkType,
                Skills,
                Location,
                Education,
                Institute,
                YearsOfExperience,
                CreatedAt
            FROM candidate_login_info 
            $where_clause
            ORDER BY YearsOfExperience DESC, CreatedAt DESC
            LIMIT 50
        ";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Process candidates
        foreach ($candidates as &$candidate) {
            $candidate['skills_array'] = explode(',', $candidate['Skills']);
        }
        
        echo json_encode([
            'success' => true,
            'candidates' => $candidates,
            'total_found' => count($candidates)
        ]);
        
    } catch (Exception $e) {
        error_log("Error in candidate search: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Search failed. Please try again.']);
    }
}


function calculateCandidateExperience($created_at) {
    $created_date = new DateTime($created_at);
    $now = new DateTime();
    $diff = $now->diff($created_date);
    return $diff->y;
}

function handleInterviewInvitation() {
    global $pdo, $company_id;
    
    try {
        // Double-check session is still valid
        if (!isCompanyLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Session expired. Please login again.', 'redirect' => 'Login&Signup.php']);
            return;
        }
        
        if (!$pdo) {
            throw new Exception('Database connection not available');
        }
        
        $candidate_id = intval($_POST['candidate_id']);
        $interview_title = trim($_POST['interview_title'] ?? '');
        $interview_date = $_POST['interview_date'];
        $interview_time = $_POST['interview_time'];
        $interview_mode = $_POST['interview_mode'] ?? 'virtual';
        $meeting_link = trim($_POST['meeting_link'] ?? '');
        $location = trim($_POST['location'] ?? '');
        
        // Validate inputs
        if (!$candidate_id || !$interview_title || !$interview_date || !$interview_time) {
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            return;
        }
        
        // Get candidate details
        $candidate_stmt = $pdo->prepare("SELECT FullName, Email FROM candidate_login_info WHERE CandidateID = ?");
        $candidate_stmt->execute([$candidate_id]);
        $candidate = $candidate_stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$candidate) {
            echo json_encode(['success' => false, 'message' => 'Candidate not found']);
            return;
        }
        
        // Get company details
        $company_stmt = $pdo->prepare("SELECT CompanyName, Email FROM Company_login_info WHERE CompanyID = ?");
        $company_stmt->execute([$company_id]);
        $company = $company_stmt->fetch(PDO::FETCH_ASSOC);
        
        // Insert interview invitation using interviews table
        $stmt = $pdo->prepare("
            INSERT INTO interviews 
            (CompanyID, CandidateID, InterviewTitle, InterviewMode, MeetingLink, Location, ScheduledDate, ScheduledTime, Status, CreatedAt) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'scheduled', NOW())
        ");
        
        $result = $stmt->execute([
            $company_id,
            $candidate_id,
            $interview_title,
            $interview_mode,
            $meeting_link,
            $location,
            $interview_date,
            $interview_time
        ]);
        
        if ($result) {
            // Send email notification
            $email_sent = sendInterviewEmail($candidate, $company, $interview_title, $interview_date, $interview_time, $interview_mode, $meeting_link, $location);
            
            echo json_encode([
                'success' => true,
                'message' => 'Interview invitation sent successfully!',
                'email_sent' => $email_sent,
                'candidate_email' => $candidate['Email']
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to send invitation']);
        }
        
    } catch (Exception $e) {
        error_log("Error sending interview invitation: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to send invitation']);
    }
}

function sendInterviewEmail($candidate, $company, $title, $date, $time, $mode, $meeting_link, $location) {
    try {
        $to = $candidate['Email'];
        $subject = "Interview Invitation from " . $company['CompanyName'];
        
        $body = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .header { background: linear-gradient(135deg, #1e88e5, #0d47a1); color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; }
                .interview-details { background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0; }
                .footer { background: #f8f9fa; padding: 15px; text-align: center; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h2>Interview Invitation</h2>
                <p>You have been invited for an interview!</p>
            </div>
            
            <div class='content'>
                <p>Dear " . htmlspecialchars($candidate['FullName']) . ",</p>
                
                <p>We are pleased to invite you for an interview with <strong>" . htmlspecialchars($company['CompanyName']) . "</strong>.</p>
                
                <div class='interview-details'>
                    <h3>Interview Details:</h3>
                    <p><strong>Title:</strong> " . htmlspecialchars($title) . "</p>
                    <p><strong>Date:</strong> " . htmlspecialchars($date) . "</p>
                    <p><strong>Time:</strong> " . htmlspecialchars($time) . "</p>
                    <p><strong>Mode:</strong> " . ucfirst(htmlspecialchars($mode)) . "</p>
                    " . (!empty($meeting_link) ? "<p><strong>Meeting Link:</strong> <a href='" . htmlspecialchars($meeting_link) . "' target='_blank'>Join Meeting</a></p>" : "") . "
                    " . (!empty($location) ? "<p><strong>Location:</strong> " . htmlspecialchars($location) . "</p>" : "") . "
                </div>
                
                <p>Please confirm your attendance by replying to this email or contacting us directly.</p>
                
                <p>We look forward to meeting you!</p>
                
                <p>Best regards,<br>
                " . htmlspecialchars($company['CompanyName']) . " Team</p>
            </div>
            
            <div class='footer'>
                <p>This is an automated message from CandiHire Platform</p>
            </div>
        </body>
        </html>
        ";
        
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: CandiHire Platform <noreply@candihire.com>',
            'Reply-To: ' . $company['Email'],
            'X-Mailer: PHP/' . phpversion()
        ];
        
        return mail($to, $subject, $body, implode("\r\n", $headers));
        
    } catch (Exception $e) {
        error_log("Error sending email: " . $e->getMessage());
        return false;
    }
}

function handleGetCandidateDetails() {
    global $pdo;
    
    try {
        // Double-check session is still valid
        if (!isCompanyLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Session expired. Please login again.', 'redirect' => 'Login&Signup.php']);
            return;
        }
        
        if (!$pdo) {
            throw new Exception('Database connection not available');
        }
        
        $candidate_id = intval($_POST['candidate_id']);
        
        $stmt = $pdo->prepare("
            SELECT 
                CandidateID,
                FullName,
                Email,
                PhoneNumber,
                WorkType,
                Skills,
                Location,
                Education,
                Institute,
                YearsOfExperience,
                CreatedAt
            FROM candidate_login_info 
            WHERE CandidateID = ?
        ");
        
        $stmt->execute([$candidate_id]);
        $candidate = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($candidate) {
            $candidate['skills_array'] = explode(',', $candidate['Skills']);
            echo json_encode(['success' => true, 'candidate' => $candidate]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Candidate not found']);
        }
        
    } catch (Exception $e) {
        error_log("Error fetching candidate details: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to fetch candidate details']);
    }
}

// Note: Using existing 'interviews' table from integrated schema
// No need to create interview_invitations table as interviews table handles this functionality
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Matching - CandiHire</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg-primary: #0d1117;
            --bg-secondary: #161b22;
            --bg-tertiary: #21262d;
            --text-primary: #c9d1d9;
            --text-secondary: #8b949e;
            --accent-1: #58a6ff;
            --accent-2: #f59e0b;
            --accent-hover: #79c0ff;
            --border: #30363d;
            --success: #3fb950;
            --danger: #f85149;
        }

        /* Light Mode Variables */
        [data-theme="light"] {
            --bg-primary: #ffffff;
            --bg-secondary: #f6f8fa;
            --bg-tertiary: #eaeef2;
            --text-primary: #24292f;
            --text-secondary: #656d76;
            --accent-1: #0969da;
            --accent-2: #f59e0b;
            --accent-hover: #0860ca;
            --border: #d1d9e0;
            --success: #1a7f37;
            --danger: #f85149;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Noto Sans', Helvetica, Arial, sans-serif;
        }

        body {
            background-color: var(--bg-primary);
            color: var(--text-primary);
            line-height: 1.5;
        }

        .container {
            display: flex;
            min-height: 100vh;
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Left Navigation */
        .left-nav {
            width: 280px;
            background-color: var(--bg-secondary);
            padding: 20px 15px;
            border-right: 1px solid var(--border);
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
        }

        .logo {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            padding: 0 10px;
            letter-spacing: 0.5px;
        }

        .logo .candi {
            color: var(--accent-1);
        }

        .logo .hire {
            color: var(--accent-2);
            margin-left: -2px;
        }

        .nav-section {
            margin-bottom: 25px;
        }

        .nav-section-title {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            color: var(--text-secondary);
            margin-bottom: 10px;
            padding: 0 10px;
            letter-spacing: 0.5px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 12px 15px;
            margin-bottom: 6px;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.2s ease, color 0.2s ease, transform 0.2s ease;
            font-size: 15px;
            will-change: transform, background-color, color;
        }

        .nav-item {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            transform: translateZ(0);
            will-change: transform, background-color, box-shadow;
        }

        .nav-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(88, 166, 255, 0.1), transparent);
            transition: left 0.6s ease;
        }

        .nav-item:hover {
            background: linear-gradient(135deg, var(--accent-1), var(--accent-hover));
            color: white;
            transform: translateX(5px) scale(1.02);
            box-shadow: 0 8px 25px rgba(88, 166, 255, 0.3);
        }

        .nav-item:hover::before {
            left: 100%;
        }

        .nav-item.active {
            background-color: var(--bg-tertiary);
            color: var(--accent-1);
            font-weight: 500;
        }

        .nav-item i {
            font-size: 20px;
            width: 24px;
            text-align: center;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            padding: 20px;
            max-width: 1000px;
            margin: 0 auto;
        }

        .page-header {
            margin-bottom: 30px;
        }

        .page-title {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 10px;
            color: var(--accent-1);
        }

        .page-subtitle {
            color: var(--text-secondary);
            font-size: 16px;
        }

        /* Search Form */
        .search-form {
            background-color: var(--bg-secondary);
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 30px;
            border: 1px solid var(--border);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .form-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 20px;
            color: var(--text-primary);
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 8px;
            color: var(--text-primary);
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            background-color: var(--bg-primary);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 12px 16px;
            color: var(--text-primary);
            font-size: 15px;
            transition: border-color 0.2s ease;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--accent-1);
        }

        .form-group input::placeholder,
        .form-group textarea::placeholder {
            color: var(--text-secondary);
        }

        .search-btn {
            background: linear-gradient(135deg, var(--accent-1), #0d47a1);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 12px 30px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(88, 166, 255, 0.3);
        }

        .search-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(88, 166, 255, 0.4);
        }

        .search-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        /* Results Section */
        .results-section {
            margin-top: 30px;
        }

        .results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .results-count {
            font-size: 18px;
            font-weight: 600;
            color: var(--text-primary);
        }

        .loading {
            text-align: center;
            padding: 40px;
            color: var(--text-secondary);
        }

        .loading i {
            font-size: 24px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @keyframes slideInRight {
            0% { 
                transform: translateX(100%); 
                opacity: 0; 
            }
            100% { 
                transform: translateX(0); 
                opacity: 1; 
            }
        }

        @keyframes slideOutRight {
            0% { 
                transform: translateX(0); 
                opacity: 1; 
            }
            100% { 
                transform: translateX(100%); 
                opacity: 0; 
            }
        }

        /* Candidate Cards */
        .candidate-card {
            background-color: var(--bg-secondary);
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 20px;
            border: 1px solid var(--border);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .candidate-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .candidate-header {
            display: flex;
            align-items: center;
            margin-bottom: 16px;
        }

        .candidate-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background-color: var(--accent-1);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 16px;
            font-weight: bold;
            font-size: 22px;
            color: white;
        }

        .candidate-info {
            flex: 1;
        }

        .candidate-info h3 {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .candidate-info p {
            color: var(--text-secondary);
            font-size: 14px;
        }

        .candidate-match {
            background-color: var(--bg-tertiary);
            border-radius: 8px;
            padding: 8px 16px;
            font-weight: 600;
            font-size: 16px;
            color: var(--accent-1);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .candidate-skills {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 16px;
        }

        .skill-tag {
            background-color: var(--bg-tertiary);
            padding: 6px 12px;
            border-radius: 16px;
            font-size: 13px;
            border: 1px solid var(--border);
            transition: all 0.2s;
        }

        .skill-tag:hover {
            background-color: var(--accent-1);
            color: white;
            border-color: var(--accent-1);
        }

        .candidate-actions {
            display: flex;
            gap: 12px;
        }

        .btn {
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.2s;
        }

        .btn-primary {
            background-color: var(--accent-1);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--accent-hover);
            transform: translateY(-1px);
        }

        .btn-secondary {
            background-color: transparent;
            color: var(--accent-1);
            border: 1px solid var(--accent-1);
        }

        .btn-secondary:hover {
            background-color: var(--accent-1);
            color: white;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background-color: var(--bg-secondary);
            margin: 5% auto;
            padding: 30px;
            border-radius: 12px;
            width: 90%;
            max-width: 600px;
            border: 1px solid var(--border);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .modal-title {
            font-size: 24px;
            font-weight: 600;
            color: var(--text-primary);
        }

        .close {
            color: var(--text-secondary);
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.2s;
        }

        .close:hover {
            color: var(--text-primary);
        }

        .modal-body {
            margin-bottom: 20px;
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }

        /* Theme Toggle Button */
        .theme-toggle-btn {
            width: 100%;
            background: linear-gradient(135deg, var(--bg-tertiary), var(--bg-secondary));
            color: var(--text-primary);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 12px 16px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            position: relative;
            overflow: hidden;
            backdrop-filter: blur(10px);
            margin-bottom: 10px;
        }

        .theme-toggle-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(88, 166, 255, 0.1), transparent);
            transition: left 0.6s ease;
        }

        .theme-toggle-btn:hover {
            background: linear-gradient(135deg, var(--accent-1), var(--accent-hover));
            color: white;
            border-color: var(--accent-1);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(88, 166, 255, 0.3);
        }

        .theme-toggle-btn:hover::before {
            left: 100%;
        }

        .theme-toggle-btn i {
            transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            font-size: 16px;
        }

        .theme-toggle-btn:hover i {
            transform: rotate(360deg) scale(1.1);
        }

        .theme-toggle-btn:active {
            transform: translateY(-1px);
        }

        .theme-toggle-btn[data-theme="dark"] i {
            color: #ffd700;
            text-shadow: 0 0 10px rgba(255, 215, 0, 0.3);
        }

        .theme-toggle-btn[data-theme="light"] i {
            color: #ffa500;
            text-shadow: 0 0 10px rgba(255, 165, 0, 0.3);
        }

        /* Logout Button */
        .logout-container {
            margin-top: 20px;
            padding: 0 10px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .logout-btn {
            width: 100%;
            background-color: var(--danger);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 10px 14px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.2s;
        }

        .logout-btn:hover {
            background-color: #d03f39;
            transform: translateY(-1px);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .left-nav {
                display: none;
            }
            .main-content {
                padding: 10px;
            }
            .form-grid {
                grid-template-columns: 1fr;
            }
            .candidate-header {
                flex-direction: column;
                text-align: center;
            }
            .candidate-actions {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Left Navigation -->
        <div class="left-nav">
            <div class="logo">
                <span class="candi">Candi</span><span class="hire">Hire</span>
            </div>
            
            <!-- Welcome Section -->
            <div class="welcome-section" style="background: var(--bg-tertiary); padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid var(--border);">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div id="companyLogo" style="width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 16px; <?php echo $companyLogo ? 'background-image: url(' . $companyLogo . '); background-size: cover; background-position: center;' : 'background: linear-gradient(135deg, var(--accent-2), #e67e22);'; ?>">
                        <?php echo $companyLogo ? '' : strtoupper(substr($companyName, 0, 1)); ?>
                    </div>
                    <div>
                        <div style="color: var(--text-primary); font-weight: 600; font-size: 14px;">Welcome back!</div>
                        <div id="companyNameDisplay" style="color: var(--text-secondary); font-size: 12px;"><?php echo htmlspecialchars($companyName); ?></div>
                    </div>
                </div>
                <button id="editProfileBtn" 
                    style="background: var(--accent-2); 
                           color: white; 
                           border: none; 
                           border-radius: 6px; 
                           padding: 8px 12px;
                           font-size: 12px; 
                           cursor: pointer; 
                           margin-top: 10px; 
                           width: 100%; 
                           transition: background 0.2s;">
                    <i class="fas fa-building" style="margin-right: 6px;"></i>Edit Profile
                </button>
            </div>
            
            <!-- Main Menu Section -->
            <div class="nav-section">
                <div class="nav-section-title">Main menu</div>
                <div class="nav-item" onclick="window.location.href='JobPost.php'">
                    <i class="fas fa-briefcase"></i>
                    <span>Job Posts</span>
                </div>
                <div class="nav-item" onclick="window.location.href='CvChecker.php'">
                    <i class="fas fa-file-alt"></i>
                    <span>CV Checker</span>
                </div>
                <div class="nav-item" onclick="window.location.href='CompanyDashboard.php'">
                    <i class="fas fa-users"></i>
                    <span>Candidate Feed</span>
                </div>
            </div>
            
            <!-- Recruitment Section -->
            <div class="nav-section">
                <div class="nav-section-title">Recruitment</div>
                <div class="nav-item" onclick="window.location.href='CreateExam.php'">
                    <i class="fas fa-pencil-alt"></i>
                    <span>Create Exam</span>
                </div>
                <div class="nav-item" onclick="window.location.href='Interview.php'">
                    <i class="fas fa-user-tie"></i>
                    <span>Interviews</span>
                </div>
                <div class="nav-item" onclick="window.location.href='company_applications.php'">
                    <i class="fas fa-clipboard-list"></i>
                    <span>View Applications</span>
                </div>
                <div class="nav-item" onclick="window.location.href='company_mcq_results.php'">
                    <i class="fas fa-chart-bar"></i>
                    <span>View MCQ Results</span>
                </div>
                <div class="nav-item active">
                    <i class="fas fa-robot"></i>
                    <span>AI Matching</span>
                </div>
            </div>

            <!-- Logout -->
            <div class="logout-container">
                <button id="themeToggleBtn" class="theme-toggle-btn" title="Switch to Light Mode">
                    <i class="fas fa-moon-stars" id="themeIcon"></i>
                    <span id="themeText">Dark Mode</span>
                </button>
                <button id="logoutBtn" class="logout-btn"><i class="fas fa-sign-out-alt" style="margin-right:8px;"></i>Logout</button>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="page-header">
                <h1 class="page-title">AI Candidate Matching</h1>
                <p class="page-subtitle">Find the perfect candidates using our AI-powered matching system</p>
            </div>

            <!-- Search Form -->
            <div class="search-form">
                <h2 class="form-title">Search Requirements</h2>
                <form id="searchForm">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="education">Education/Institution</label>
                            <input type="text" id="education" name="education" placeholder="e.g., Computer Science, MIT, Bachelor's">
                        </div>
                        <div class="form-group">
                            <label for="experience">Minimum Years of Experience</label>
                            <input type="number" id="experience" name="experience" placeholder="e.g., 3 (minimum required)" min="0" max="50">
                            <small style="color: var(--text-secondary); font-size: 12px; margin-top: 4px; display: block;">
                                <i class="fas fa-info-circle"></i> Shows candidates with this experience or more
                            </small>
                        </div>
                        <div class="form-group">
                            <label for="skills">Required Skills (comma-separated)</label>
                            <input type="text" id="skills" name="skills" placeholder="e.g., React, Python, SQL, AWS">
                            <small style="color: var(--text-secondary); font-size: 12px; margin-top: 4px; display: block;">
                                <i class="fas fa-info-circle"></i> Candidate must have ALL specified skills
                            </small>
                        </div>
                        <div class="form-group">
                            <label for="location">Location Preference</label>
                            <input type="text" id="location" name="location" placeholder="e.g., Remote, New York, San Francisco">
                        </div>
                    </div>
                    <button type="submit" class="search-btn">
                        <i class="fas fa-search"></i> Find Candidates
                    </button>
                </form>
            </div>

            <!-- Results Section -->
            <div class="results-section" id="resultsSection" style="display: none;">
                <div class="results-header">
                    <div class="results-count" id="resultsCount">0 candidates found</div>
                </div>
                <div id="candidatesList"></div>
            </div>

            <!-- Loading -->
            <div class="loading" id="loading" style="display: none;">
                <i class="fas fa-spinner"></i>
                <p>Searching for candidates...</p>
            </div>
        </div>
    </div>

    <!-- Candidate Details Modal -->
    <div id="candidateDetailsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Candidate Details</h2>
                <span class="close" onclick="closeCandidateDetailsModal()">&times;</span>
            </div>
            <div class="modal-body" id="candidateDetailsContent">
                <!-- Candidate details will be loaded here -->
            </div>
                   <div class="modal-footer">
                       <button type="button" class="btn btn-secondary" onclick="closeCandidateDetailsModal()">Close</button>
                   </div>
        </div>
    </div>

    <!-- Interview Invitation Modal -->
    <div id="interviewModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Send Interview Invitation</h2>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <div class="modal-body">
                <form id="interviewForm">
                    <input type="hidden" id="selectedCandidateId" name="candidate_id">
                    <div class="form-group">
                        <label for="interviewTitle">Interview Title</label>
                        <input type="text" id="interviewTitle" name="interview_title" placeholder="e.g., Technical Interview, HR Interview" required>
                    </div>
                    <div class="form-group">
                        <label for="interviewDate">Interview Date</label>
                        <input type="date" id="interviewDate" name="interview_date" required>
                    </div>
                    <div class="form-group">
                        <label for="interviewTime">Interview Time</label>
                        <input type="time" id="interviewTime" name="interview_time" required>
                    </div>
                    <div class="form-group">
                        <label for="interviewMode">Interview Mode</label>
                        <select id="interviewMode" name="interview_mode" required>
                            <option value="virtual">Virtual (Online)</option>
                            <option value="onsite">On-site</option>
                        </select>
                    </div>
                    <div class="form-group" id="meetingLinkGroup" style="display: none;">
                        <label for="meetingLink">Meeting Link</label>
                        <input type="url" id="meetingLink" name="meeting_link" placeholder="https://zoom.us/j/123456789">
                    </div>
                    <div class="form-group" id="locationGroup" style="display: none;">
                        <label for="location">Location</label>
                        <input type="text" id="location" name="location" placeholder="Office address or meeting room">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="sendInterviewInvitation()">Send Invitation</button>
            </div>
        </div>
    </div>

    <!-- Company Profile Edit Popup -->
    <div id="companyProfileEditPopup" class="popup-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.7); z-index: 10000; backdrop-filter: blur(5px);">
        <div class="popup-content" style="background: var(--bg-secondary); border-radius: 16px; padding: 30px; width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto; border: 1px solid var(--border); box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3); margin: 5% auto;">
            <div class="popup-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; padding-bottom: 15px; border-bottom: 1px solid var(--border);">
                <div class="popup-title" style="font-size: 24px; font-weight: 600; color: var(--accent-2); display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-building"></i>
                    Edit Company Profile
                </div>
                <button class="popup-close" onclick="closeCompanyProfileEditPopup()" style="background: none; border: none; color: var(--text-secondary); font-size: 24px; cursor: pointer; padding: 5px; border-radius: 50%; transition: all 0.2s;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="companyProfileEditForm" enctype="multipart/form-data">
                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="companyLogoFile" style="display: block; color: var(--text-primary); font-weight: 500; margin-bottom: 8px; font-size: 14px;">Company Logo</label>
                    <input type="file" id="companyLogoFile" name="logo" accept="image/*" style="width: 100%; background: var(--bg-primary); border: 1px solid var(--border); border-radius: 8px; padding: 12px 16px; color: var(--text-primary); font-size: 14px; transition: border-color 0.2s; box-sizing: border-box;">
                    <div id="currentCompanyLogo" style="margin-top: 10px; display: none;">
                        <img id="companyLogoPreview" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 2px solid var(--border);" />
                    </div>
                </div>
                
                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="companyName" style="display: block; color: var(--text-primary); font-weight: 500; margin-bottom: 8px; font-size: 14px;">Company Name *</label>
                    <input type="text" id="companyName" name="companyName" required style="width: 100%; background: var(--bg-primary); border: 1px solid var(--border); border-radius: 8px; padding: 12px 16px; color: var(--text-primary); font-size: 14px; transition: border-color 0.2s; box-sizing: border-box;">
                </div>
                
                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="industry" style="display: block; color: var(--text-primary); font-weight: 500; margin-bottom: 8px; font-size: 14px;">Industry *</label>
                    <input type="text" id="industry" name="industry" required placeholder="e.g., Technology, Healthcare, Finance" style="width: 100%; background: var(--bg-primary); border: 1px solid var(--border); border-radius: 8px; padding: 12px 16px; color: var(--text-primary); font-size: 14px; transition: border-color 0.2s; box-sizing: border-box;">
                </div>
                
                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="companySize" style="display: block; color: var(--text-primary); font-weight: 500; margin-bottom: 8px; font-size: 14px;">Company Size *</label>
                    <select id="companySize" name="companySize" required style="width: 100%; background: var(--bg-primary); border: 1px solid var(--border); border-radius: 8px; padding: 12px 16px; color: var(--text-primary); font-size: 14px; transition: border-color 0.2s; box-sizing: border-box; cursor: pointer;">
                        <option value="">Select Company Size</option>
                        <option value="1-10">1-10 employees</option>
                        <option value="11-50">11-50 employees</option>
                        <option value="51-200">51-200 employees</option>
                        <option value="201-500">201-500 employees</option>
                        <option value="501-1000">501-1000 employees</option>
                        <option value="1000+">1000+ employees</option>
                    </select>
                </div>
                
                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="phoneNumber" style="display: block; color: var(--text-primary); font-weight: 500; margin-bottom: 8px; font-size: 14px;">Phone Number</label>
                    <input type="tel" id="phoneNumber" name="phoneNumber" placeholder="+1 (555) 123-4567" style="width: 100%; background: var(--bg-primary); border: 1px solid var(--border); border-radius: 8px; padding: 12px 16px; color: var(--text-primary); font-size: 14px; transition: border-color 0.2s; box-sizing: border-box;">
                </div>
                
                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="website" style="display: block; color: var(--text-primary); font-weight: 500; margin-bottom: 8px; font-size: 14px;">Website</label>
                    <input type="url" id="website" name="website" placeholder="https://www.yourcompany.com" style="width: 100%; background: var(--bg-primary); border: 1px solid var(--border); border-radius: 8px; padding: 12px 16px; color: var(--text-primary); font-size: 14px; transition: border-color 0.2s; box-sizing: border-box;">
                </div>
                
                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="companyDescription" style="display: block; color: var(--text-primary); font-weight: 500; margin-bottom: 8px; font-size: 14px;">Company Description *</label>
                    <textarea id="companyDescription" name="companyDescription" required placeholder="Describe your company, its mission, and what makes it unique" style="width: 100%; background: var(--bg-primary); border: 1px solid var(--border); border-radius: 8px; padding: 12px 16px; color: var(--text-primary); font-size: 14px; transition: border-color 0.2s; box-sizing: border-box; resize: vertical; min-height: 80px;"></textarea>
                </div>
                
                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="address" style="display: block; color: var(--text-primary); font-weight: 500; margin-bottom: 8px; font-size: 14px;">Address</label>
                    <textarea id="address" name="address" placeholder="Street address, building number" style="width: 100%; background: var(--bg-primary); border: 1px solid var(--border); border-radius: 8px; padding: 12px 16px; color: var(--text-primary); font-size: 14px; transition: border-color 0.2s; box-sizing: border-box; resize: vertical;"></textarea>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="city" style="display: block; color: var(--text-primary); font-weight: 500; margin-bottom: 8px; font-size: 14px;">City</label>
                        <input type="text" id="city" name="city" placeholder="City" style="width: 100%; background: var(--bg-primary); border: 1px solid var(--border); border-radius: 8px; padding: 12px 16px; color: var(--text-primary); font-size: 14px; transition: border-color 0.2s; box-sizing: border-box;">
                    </div>
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="state" style="display: block; color: var(--text-primary); font-weight: 500; margin-bottom: 8px; font-size: 14px;">State/Province</label>
                        <input type="text" id="state" name="state" placeholder="State or Province" style="width: 100%; background: var(--bg-primary); border: 1px solid var(--border); border-radius: 8px; padding: 12px 16px; color: var(--text-primary); font-size: 14px; transition: border-color 0.2s; box-sizing: border-box;">
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="country" style="display: block; color: var(--text-primary); font-weight: 500; margin-bottom: 8px; font-size: 14px;">Country</label>
                        <input type="text" id="country" name="country" placeholder="Country" style="width: 100%; background: var(--bg-primary); border: 1px solid var(--border); border-radius: 8px; padding: 12px 16px; color: var(--text-primary); font-size: 14px; transition: border-color 0.2s; box-sizing: border-box;">
                    </div>
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="postalCode" style="display: block; color: var(--text-primary); font-weight: 500; margin-bottom: 8px; font-size: 14px;">Postal Code</label>
                        <input type="text" id="postalCode" name="postalCode" placeholder="ZIP/Postal Code" style="width: 100%; background: var(--bg-primary); border: 1px solid var(--border); border-radius: 8px; padding: 12px 16px; color: var(--text-primary); font-size: 14px; transition: border-color 0.2s; box-sizing: border-box;">
                    </div>
                </div>
                
                <div class="form-actions" style="display: flex; gap: 15px; justify-content: flex-end; margin-top: 30px; padding-top: 20px; border-top: 1px solid var(--border);">
                    <button type="button" class="btn btn-secondary" onclick="closeCompanyProfileEditPopup()" style="padding: 12px 24px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 14px; transition: all 0.2s; display: flex; align-items: center; gap: 8px; background: var(--bg-tertiary); color: var(--text-primary); border: 1px solid var(--border);">
                        <i class="fas fa-times"></i>
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary" id="submitCompanyProfileUpdate" style="padding: 12px 24px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 14px; transition: all 0.2s; display: flex; align-items: center; gap: 8px; background: var(--accent-2); color: white;">
                        <i class="fas fa-save"></i>
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Theme Management Functions
        function initializeTheme() {
            try {
                // Get saved theme or default to dark
                const savedTheme = localStorage.getItem('candihire-theme') || 'dark';
                applyTheme(savedTheme);
                updateThemeButton(savedTheme);
            } catch (error) {
                console.error('Error initializing theme:', error);
            }
        }

        function applyTheme(theme) {
            try {
                document.documentElement.setAttribute('data-theme', theme);
                localStorage.setItem('candihire-theme', theme);
                
                console.log('Theme applied:', theme);
            } catch (error) {
                console.error('Error applying theme:', error);
            }
        }

        function toggleTheme() {
            try {
                console.log('Toggle theme clicked!');
                const currentTheme = document.documentElement.getAttribute('data-theme');
                const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                console.log('Switching from', currentTheme, 'to', newTheme);
                
                applyTheme(newTheme);
                updateThemeButton(newTheme);
                
                // Add smooth transition effect
                document.body.style.transition = 'background-color 0.3s ease, color 0.3s ease';
                setTimeout(() => {
                    document.body.style.transition = '';
                }, 300);
                
                console.log('Theme switched to:', newTheme);
            } catch (error) {
                console.error('Error toggling theme:', error);
            }
        }

        function updateThemeButton(theme) {
            try {
                const themeIcon = document.getElementById('themeIcon');
                const themeText = document.getElementById('themeText');
                const themeToggleBtn = document.getElementById('themeToggleBtn');
                
                if (!themeIcon || !themeText || !themeToggleBtn) {
                    console.error('Theme button elements not found');
                    return;
                }
                
                // Add theme data attribute for CSS styling
                themeToggleBtn.setAttribute('data-theme', theme);
                
                if (theme === 'dark') {
                    themeIcon.className = 'fas fa-moon-stars';
                    themeText.textContent = 'Light Mode';
                    themeToggleBtn.title = 'Switch to Light Mode';
                } else {
                    themeIcon.className = 'fas fa-moon-stars';
                    themeText.textContent = 'Dark Mode';
                    themeToggleBtn.title = 'Switch to Dark Mode';
                }
            } catch (error) {
                console.error('Error updating theme button:', error);
            }
        }

        function setupThemeToggle() {
            try {
                const themeToggleBtn = document.getElementById('themeToggleBtn');
                if (themeToggleBtn) {
                    themeToggleBtn.addEventListener('click', toggleTheme);
                    console.log('Theme toggle button event listener added');
                } else {
                    console.error('Theme toggle button not found');
                }
            } catch (error) {
                console.error('Error setting up theme toggle:', error);
            }
        }

        // Company Profile Management
        let currentCompanyId = <?php echo json_encode($company_id); ?>;

        // Setup company profile editing
        function setupCompanyProfileEditing() {
            console.log('Setting up company profile editing...');
            
            const editProfileBtn = document.getElementById('editProfileBtn');
            const profilePopup = document.getElementById('companyProfileEditPopup');
            const profileForm = document.getElementById('companyProfileEditForm');
            
            if (!editProfileBtn || !profilePopup || !profileForm) {
                console.error('Company profile editing elements not found');
                return;
            }
            
            // Open profile edit popup
            editProfileBtn.addEventListener('click', function() {
                console.log('Opening company profile edit popup');
                loadCurrentCompanyProfile();
                profilePopup.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            });
            
            // Handle form submission
            profileForm.addEventListener('submit', function(e) {
                e.preventDefault();
                updateCompanyProfile();
            });
            
            // Handle logo preview
            const logoInput = document.getElementById('companyLogoFile');
            if (logoInput) {
                logoInput.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const preview = document.getElementById('companyLogoPreview');
                            const currentLogo = document.getElementById('currentCompanyLogo');
                            if (preview && currentLogo) {
                                preview.src = e.target.result;
                                currentLogo.style.display = 'block';
                            }
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }
            
            // Close popup when clicking outside
            profilePopup.addEventListener('click', function(e) {
                if (e.target === profilePopup) {
                    closeCompanyProfileEditPopup();
                }
            });
            
            console.log('Company profile editing setup complete');
        }

        // Load company profile data
        function loadCompanyProfile() {
            console.log('Loading company profile data...');
            
            if (!currentCompanyId) {
                console.error('No company ID available');
                return;
            }
            
            fetch(`company_profile_handler.php?companyId=${currentCompanyId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const company = data.company;
                        console.log('Company profile data loaded:', company);
                        
                        // Update display
                        document.getElementById('companyNameDisplay').textContent = company.CompanyName || 'Company';
                        document.getElementById('companyLogo').textContent = (company.CompanyName || 'C').charAt(0).toUpperCase();
                        
                        // Handle logo if exists
                        if (company.Logo) {
                            const logo = document.getElementById('companyLogo');
                            logo.style.backgroundImage = `url(${company.Logo})`;
                            logo.style.backgroundSize = 'cover';
                            logo.style.backgroundPosition = 'center';
                            logo.textContent = '';
                        }
                    } else {
                        console.error('Failed to load company profile:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error loading company profile:', error);
                });
        }

        // Load current company profile for editing
        function loadCurrentCompanyProfile() {
            console.log('Loading current company profile for editing...');
            
            if (!currentCompanyId) {
                console.error('No company ID available');
                return;
            }
            
            fetch(`company_profile_handler.php?companyId=${currentCompanyId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const company = data.company;
                        console.log('Company profile data loaded for editing:', company);
                        
                        // Populate form fields
                        document.getElementById('companyName').value = company.CompanyName || '';
                        document.getElementById('industry').value = company.Industry || '';
                        document.getElementById('companySize').value = company.CompanySize || '';
                        document.getElementById('phoneNumber').value = company.PhoneNumber || '';
                        document.getElementById('website').value = company.Website || '';
                        document.getElementById('companyDescription').value = company.CompanyDescription || '';
                        document.getElementById('address').value = company.Address || '';
                        document.getElementById('city').value = company.City || '';
                        document.getElementById('state').value = company.State || '';
                        document.getElementById('country').value = company.Country || '';
                        document.getElementById('postalCode').value = company.PostalCode || '';
                        
                        // Handle logo
                        if (company.Logo) {
                            const preview = document.getElementById('companyLogoPreview');
                            const currentLogo = document.getElementById('currentCompanyLogo');
                            if (preview && currentLogo) {
                                preview.src = company.Logo;
                                currentLogo.style.display = 'block';
                            }
                        }
                    } else {
                        console.error('Failed to load company profile:', data.message);
                        showErrorMessage('Failed to load company profile data');
                    }
                })
                .catch(error => {
                    console.error('Error loading company profile:', error);
                    showErrorMessage('Network error loading company profile');
                });
        }

        // Update company profile
        function updateCompanyProfile() {
            console.log('Updating company profile...');
            
            const form = document.getElementById('companyProfileEditForm');
            const submitBtn = document.getElementById('submitCompanyProfileUpdate');
            const formData = new FormData(form);
            
            // Add company ID
            formData.append('companyId', currentCompanyId);
            
            // Disable button and show loading
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            
            fetch('company_profile_handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeCompanyProfileEditPopup();
                    showSuccessMessage('Company profile updated successfully!');
                    
                    // Update the display with server response data
                    if (data.companyName) {
                        document.getElementById('companyNameDisplay').textContent = data.companyName;
                        
                        // Update logo text or image
                        const logo = document.getElementById('companyLogo');
                        if (data.logo) {
                            // Show company logo
                            logo.style.backgroundImage = `url(${data.logo})`;
                            logo.style.backgroundSize = 'cover';
                            logo.style.backgroundPosition = 'center';
                            logo.textContent = '';
                        } else {
                            // Show initials
                            logo.style.backgroundImage = '';
                            logo.style.background = 'linear-gradient(135deg, var(--accent-2), #e67e22)';
                            logo.textContent = data.companyName.charAt(0).toUpperCase();
                        }
                    }
                } else {
                    showErrorMessage(data.message || 'Failed to update company profile');
                }
            })
            .catch(error => {
                console.error('Error updating company profile:', error);
                showErrorMessage('Network error. Please try again.');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-save"></i> Save Changes';
            });
        }

        // Close company profile edit popup
        function closeCompanyProfileEditPopup() {
            const popup = document.getElementById('companyProfileEditPopup');
            const form = document.getElementById('companyProfileEditForm');
            
            popup.style.display = 'none';
            document.body.style.overflow = 'auto';
            form.reset();
            
            // Hide logo preview
            const currentLogo = document.getElementById('currentCompanyLogo');
            if (currentLogo) {
                currentLogo.style.display = 'none';
            }
        }

        // Utility functions for notifications
        function showSuccessMessage(message) {
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: var(--success);
                color: white;
                padding: 15px 20px;
                border-radius: 8px;
                z-index: 10001;
                font-weight: 500;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            `;
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }

        function showSuccessNotification(message) {
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: linear-gradient(135deg, var(--success), #2d7d32);
                color: white;
                padding: 20px 25px;
                border-radius: 12px;
                z-index: 10001;
                font-weight: 500;
                box-shadow: 0 8px 25px rgba(63, 185, 80, 0.3);
                border: 1px solid rgba(255, 255, 255, 0.2);
                max-width: 400px;
                display: flex;
                align-items: center;
                gap: 12px;
                animation: slideInRight 0.3s ease-out;
            `;
            
            notification.innerHTML = `
                <div style="font-size: 24px; color: #4caf50;">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div style="flex: 1;">
                    <div style="font-weight: 600; margin-bottom: 4px; font-size: 16px;">Success!</div>
                    <div style="font-size: 14px; opacity: 0.9;">${message}</div>
                </div>
                <button onclick="this.parentElement.remove()" style="
                    background: none; 
                    border: none; 
                    color: white; 
                    font-size: 18px; 
                    cursor: pointer; 
                    padding: 4px; 
                    border-radius: 4px;
                    transition: background 0.2s;
                " onmouseover="this.style.background='rgba(255,255,255,0.2)'" onmouseout="this.style.background='none'">
                    <i class="fas fa-times"></i>
                </button>
            `;
            
            document.body.appendChild(notification);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.style.animation = 'slideOutRight 0.3s ease-in';
                    setTimeout(() => notification.remove(), 300);
                }
            }, 5000);
        }

        function showErrorMessage(message) {
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: var(--danger);
                color: white;
                padding: 15px 20px;
                border-radius: 8px;
                z-index: 10001;
                font-weight: 500;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            `;
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 5000);
        }

        // Initialize everything when DOM is loaded
        function initializeApp() {
            console.log('Initializing AIMatching app...');
            
            initializeTheme();
            setupThemeToggle();
            setupCompanyProfileEditing();
            loadCompanyProfile();
            
            // Search form handling
            const searchForm = document.getElementById('searchForm');
            if (searchForm) {
                searchForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    searchCandidates();
                });
            }
            
            console.log('AIMatching app initialized successfully');
        }

        // Initialize when DOM is loaded
        document.addEventListener('DOMContentLoaded', initializeApp);

        function searchCandidates() {
            const formData = new FormData(document.getElementById('searchForm'));
            const searchData = {};
            
            for (let [key, value] of formData.entries()) {
                searchData[key] = value;
            }

            // Show loading
            document.getElementById('loading').style.display = 'block';
            document.getElementById('resultsSection').style.display = 'none';

            // Send search request
            const formDataToSend = new FormData();
            formDataToSend.append('action', 'search_candidates');
            for (let key in searchData) {
                formDataToSend.append(key, searchData[key]);
            }

            fetch('AIMatching.php', {
                method: 'POST',
                body: formDataToSend
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('loading').style.display = 'none';
                
                if (data.success) {
                    displayCandidates(data.candidates, searchData);
                    document.getElementById('resultsCount').textContent = `${data.total_found} candidates found`;
                    document.getElementById('resultsSection').style.display = 'block';
                } else {
                    if (data.redirect) {
                        // Session expired, redirect to login
                        alert('Session expired. Please login again.');
                        window.location.href = data.redirect;
                    } else {
                        alert('Search failed: ' + data.message);
                    }
                }
            })
            .catch(error => {
                document.getElementById('loading').style.display = 'none';
                console.error('Error:', error);
                alert('An error occurred while searching');
            });
        }

        function displayCandidates(candidates, searchCriteria = {}) {
            const candidatesList = document.getElementById('candidatesList');
            candidatesList.innerHTML = '';

            if (candidates.length === 0) {
                candidatesList.innerHTML = '<div class="loading"><p>No candidates found matching your criteria.</p></div>';
                return;
            }

            candidates.forEach(candidate => {
                const candidateCard = document.createElement('div');
                candidateCard.className = 'candidate-card';
                
                const initials = candidate.FullName.split(' ').map(name => name[0]).join('').toUpperCase();
                const skillsHtml = candidate.skills_array.map(skill => 
                    `<div class="skill-tag">${skill.trim()}</div>`
                ).join('');
                
                // Check if candidate meets minimum experience requirement
                const minExperience = parseInt(searchCriteria.experience) || 0;
                const candidateExp = parseInt(candidate.YearsOfExperience) || 0;
                const meetsExperienceReq = candidateExp >= minExperience;
                const experienceIndicator = minExperience > 0 ? 
                    `<span style="color: ${meetsExperienceReq ? 'var(--success)' : 'var(--danger)'}; font-size: 12px; margin-left: 8px;">
                        <i class="fas ${meetsExperienceReq ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
                        ${meetsExperienceReq ? 'Meets minimum' : 'Below minimum'}
                    </span>` : '';

                candidateCard.innerHTML = `
                    <div class="candidate-header">
                        <div class="candidate-avatar">${initials}</div>
                        <div class="candidate-info">
                            <h3>${candidate.FullName}</h3>
                            <p>${candidate.WorkType.replace('-', ' ').toUpperCase()} • ${candidate.YearsOfExperience || 0} years experience${experienceIndicator}</p>
                            ${candidate.Location ? `<p style="color: var(--text-secondary); font-size: 13px; margin-top: 4px;"><i class="fas fa-map-marker-alt"></i> ${candidate.Location}</p>` : ''}
                        </div>
                    </div>
                    <div class="candidate-skills">
                        ${skillsHtml}
                    </div>
                    ${candidate.Education ? `<div style="margin: 12px 0; padding: 8px 12px; background: var(--bg-tertiary); border-radius: 6px; font-size: 13px; color: var(--text-secondary);">
                        <i class="fas fa-graduation-cap"></i> ${candidate.Education}${candidate.Institute ? ` - ${candidate.Institute}` : ''}
                    </div>` : ''}
                    <div class="candidate-actions">
                        <button class="btn btn-primary" onclick="viewCandidateDetails(${candidate.CandidateID})">
                            <i class="fas fa-eye"></i> View Details
                        </button>
                        <button class="btn btn-secondary" onclick="openInterviewModal(${candidate.CandidateID})">
                            <i class="fas fa-calendar-plus"></i> Send Interview Invite
                        </button>
                    </div>
                `;

                candidatesList.appendChild(candidateCard);
            });
        }

        function viewCandidateDetails(candidateId) {
            const formData = new FormData();
            formData.append('action', 'get_candidate_details');
            formData.append('candidate_id', candidateId);

            fetch('AIMatching.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const candidate = data.candidate;
                    displayCandidateDetailsPopup(candidate);
                } else {
                    if (data.redirect) {
                        // Session expired, redirect to login
                        alert('Session expired. Please login again.');
                        window.location.href = data.redirect;
                    } else {
                        alert('Failed to fetch candidate details');
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while fetching candidate details');
            });
        }

        function displayCandidateDetailsPopup(candidate) {
            const modal = document.getElementById('candidateDetailsModal');
            const content = document.getElementById('candidateDetailsContent');
            
            const skillsHtml = candidate.skills_array.map(skill => 
                `<div class="skill-tag">${skill.trim()}</div>`
            ).join('');

            const initials = candidate.FullName.split(' ').map(name => name[0]).join('').toUpperCase();
            
            content.innerHTML = `
                <div style="display: flex; align-items: center; margin-bottom: 20px; padding: 20px; background: var(--bg-tertiary); border-radius: 12px;">
                    <div style="width: 80px; height: 80px; border-radius: 50%; background: var(--accent-1); display: flex; align-items: center; justify-content: center; margin-right: 20px; font-weight: bold; font-size: 28px; color: white;">
                        ${initials}
                    </div>
                    <div>
                        <h3 style="margin: 0 0 8px 0; font-size: 24px; color: var(--text-primary);">${candidate.FullName}</h3>
                        <p style="margin: 0; color: var(--text-secondary); font-size: 16px;">${candidate.WorkType.replace('-', ' ').toUpperCase()} • ${candidate.YearsOfExperience || 0} years experience</p>
                        ${candidate.Location ? `<p style="margin: 4px 0 0 0; color: var(--text-secondary); font-size: 14px;"><i class="fas fa-map-marker-alt"></i> ${candidate.Location}</p>` : ''}
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div>
                        <h4 style="color: var(--text-primary); margin-bottom: 8px; font-size: 16px;"><i class="fas fa-envelope"></i> Contact Information</h4>
                        <p style="margin: 4px 0; color: var(--text-secondary);"><strong>Email:</strong> ${candidate.Email}</p>
                        <p style="margin: 4px 0; color: var(--text-secondary);"><strong>Phone:</strong> ${candidate.PhoneNumber}</p>
                    </div>
                    <div>
                        <h4 style="color: var(--text-primary); margin-bottom: 8px; font-size: 16px;"><i class="fas fa-graduation-cap"></i> Education</h4>
                        <p style="margin: 4px 0; color: var(--text-secondary);"><strong>Degree:</strong> ${candidate.Education || 'Not specified'}</p>
                        <p style="margin: 4px 0; color: var(--text-secondary);"><strong>Institute:</strong> ${candidate.Institute || 'Not specified'}</p>
                    </div>
                </div>

                <div style="margin-bottom: 20px;">
                    <h4 style="color: var(--text-primary); margin-bottom: 12px; font-size: 16px;"><i class="fas fa-tools"></i> Skills</h4>
                    <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                        ${skillsHtml}
                    </div>
                </div>

                <div style="padding: 15px; background: var(--bg-tertiary); border-radius: 8px; border-left: 4px solid var(--accent-1);">
                    <p style="margin: 0; color: var(--text-secondary); font-size: 14px;">
                        <i class="fas fa-calendar"></i> <strong>Member since:</strong> ${new Date(candidate.CreatedAt).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}
                    </p>
                </div>
            `;
            
            // Store candidate ID for interview invitation
            window.selectedCandidateForInterview = candidate.CandidateID;
            
            modal.style.display = 'block';
        }

        function closeCandidateDetailsModal() {
            document.getElementById('candidateDetailsModal').style.display = 'none';
        }

        function openInterviewFromDetails() {
            closeCandidateDetailsModal();
            if (window.selectedCandidateForInterview) {
                openInterviewModal(window.selectedCandidateForInterview);
            }
        }

        function openInterviewModal(candidateId) {
            document.getElementById('selectedCandidateId').value = candidateId;
            document.getElementById('interviewModal').style.display = 'block';
            
            // Set minimum date to today
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('interviewDate').min = today;
            
            // Setup interview mode change handler
            setupInterviewModeHandler();
        }
        
        function setupInterviewModeHandler() {
            const interviewMode = document.getElementById('interviewMode');
            const meetingLinkGroup = document.getElementById('meetingLinkGroup');
            const locationGroup = document.getElementById('locationGroup');
            
            function toggleFields() {
                const mode = interviewMode.value;
                
                // Hide all groups first
                meetingLinkGroup.style.display = 'none';
                locationGroup.style.display = 'none';
                
                // Show relevant groups based on mode
                if (mode === 'virtual') {
                    meetingLinkGroup.style.display = 'block';
                } else if (mode === 'onsite') {
                    locationGroup.style.display = 'block';
                }
            }
            
            // Remove existing event listeners
            interviewMode.removeEventListener('change', toggleFields);
            // Add new event listener
            interviewMode.addEventListener('change', toggleFields);
            
            // Initialize fields
            toggleFields();
        }

        function closeModal() {
            document.getElementById('interviewModal').style.display = 'none';
        }

        function sendInterviewInvitation() {
            const formData = new FormData(document.getElementById('interviewForm'));
            formData.append('action', 'send_interview_invitation');

            fetch('AIMatching.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccessNotification('Interview Scheduled! Successfully sent invitation');
                    closeModal();
                    // Stay on current page - no redirect
                } else {
                    if (data.redirect) {
                        // Session expired, redirect to login
                        showErrorMessage('Session expired. Please login again.');
                        setTimeout(() => window.location.href = data.redirect, 2000);
                    } else {
                        showErrorMessage('Failed to send invitation: ' + data.message);
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showErrorMessage('An error occurred while sending the invitation');
            });
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const interviewModal = document.getElementById('interviewModal');
            const candidateDetailsModal = document.getElementById('candidateDetailsModal');
            
            if (event.target === interviewModal) {
                closeModal();
            }
            if (event.target === candidateDetailsModal) {
                closeCandidateDetailsModal();
            }
        }

       // Simple Navigation without Page Transitions
document.querySelectorAll('.nav-item').forEach(item => {
    item.addEventListener('click', function(e) {
        e.preventDefault();
        
        const href = this.getAttribute('data-href');
        if (href) {
            // Don't navigate if already on current page
            if (href === window.location.pathname.split('/').pop()) {
                return;
            }
            
            // Navigate immediately
            window.location.href = href;
        } else {
            // Handle non-navigation items (like active state toggling)
            document.querySelectorAll('.nav-item').forEach(navItem => {
                navItem.classList.remove('active');
            });
            this.classList.add('active');
        }
    });
});

        // Logout functionality
        document.getElementById('logoutBtn').addEventListener('click', function() {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = 'Login&Signup.php';
            }
        });
    </script>
</body>
</html>


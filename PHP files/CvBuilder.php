<?php
// CV Builder functionality
require_once 'session_manager.php';

// Check if candidate is logged in
if (!isCandidateLoggedIn()) {
    header('Location: Login&Signup.php');
    exit;
}

// Get candidate ID and name from session
$sessionCandidateId = getCurrentCandidateId();
$candidateName = $_SESSION['candidate_name'] ?? 'User';
$candidateProfilePicture = null;

// Load candidate profile picture if available
require_once 'Database.php';
try {
    if (isset($pdo) && $pdo instanceof PDO) {
        $stmt = $pdo->prepare("SELECT ProfilePicture FROM candidate_login_info WHERE CandidateID = ?");
        $stmt->execute([$sessionCandidateId]);
        $candidate = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($candidate && $candidate['ProfilePicture']) {
            $candidateProfilePicture = $candidate['ProfilePicture'];
        }
    }
} catch (Exception $e) {
    error_log("Error loading candidate profile picture: " . $e->getMessage());
}

if ($_POST) {
    $firstName = $_POST['firstName'] ?? '';
    $lastName = $_POST['lastName'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    $summary = $_POST['summary'] ?? '';
    
    // Process experience data
    $experiences = [];
    if (isset($_POST['jobTitle']) && is_array($_POST['jobTitle'])) {
        for ($i = 0; $i < count($_POST['jobTitle']); $i++) {
            if (!empty($_POST['jobTitle'][$i]) || !empty($_POST['company'][$i])) {
                $experiences[] = [
                    'title' => $_POST['jobTitle'][$i] ?? '',
                    'company' => $_POST['company'][$i] ?? '',
                    'startDate' => $_POST['startDate'][$i] ?? '',
                    'endDate' => $_POST['endDate'][$i] ?? '',
                    'description' => $_POST['jobDescription'][$i] ?? '',
                    'location' => $_POST['jobLocation'][$i] ?? ''
                ];
            }
        }
    }
    
    // Process education data
    $education = [];
    if (isset($_POST['degree']) && is_array($_POST['degree'])) {
        for ($i = 0; $i < count($_POST['degree']); $i++) {
            if (!empty($_POST['degree'][$i]) || !empty($_POST['institution'][$i])) {
                $education[] = [
                    'degree' => $_POST['degree'][$i] ?? '',
                    'institution' => $_POST['institution'][$i] ?? '',
                    'startYear' => $_POST['eduStartYear'][$i] ?? '',
                    'endYear' => $_POST['eduEndYear'][$i] ?? '',
                    'gpa' => $_POST['gpa'][$i] ?? '',
                    'location' => $_POST['eduLocation'][$i] ?? '',
                    'coursework' => $_POST['coursework'][$i] ?? ''
                ];
            }
        }
    }
    
    // Process skills data
    $skills = [
        'programmingLanguages' => $_POST['programmingLanguages'] ?? '',
        'frameworks' => $_POST['frameworks'] ?? '',
        'databases' => $_POST['databases'] ?? '',
        'tools' => $_POST['tools'] ?? '',
        'softSkills' => $_POST['softSkills'] ?? '',
        'languages' => $_POST['languages'] ?? '',
        'certifications' => $_POST['certifications'] ?? ''
    ];
    
    // Process projects data
    $projects = [];
    if (isset($_POST['projectName']) && is_array($_POST['projectName'])) {
        for ($i = 0; $i < count($_POST['projectName']); $i++) {
            if (!empty($_POST['projectName'][$i]) || !empty($_POST['projectDescription'][$i])) {
                $projects[] = [
                    'name' => $_POST['projectName'][$i] ?? '',
                    'role' => $_POST['projectRole'][$i] ?? '',
                    'startDate' => $_POST['projectStartDate'][$i] ?? '',
                    'endDate' => $_POST['projectEndDate'][$i] ?? '',
                    'description' => $_POST['projectDescription'][$i] ?? '',
                    'technologies' => $_POST['projectTechnologies'][$i] ?? '',
                    'url' => $_POST['projectUrl'][$i] ?? ''
                ];
            }
        }
    }
    
    // In a real application, you would save this data to a database
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CV Builder - CandiHire</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg-primary: #0d1117;
            --bg-secondary: #161b22;
            --bg-tertiary: #21262d;
            --text-primary: #c9d1d9;
            --text-secondary: #8b949e;
            --accent: #58a6ff;
            --accent-hover: #79c0ff;
            --accent-secondary: #f59e0b;
            --border: #30363d;
            --success: #3fb950;
            --danger: #f85149;
        }

        /* Light Theme */
        [data-theme="light"] {
            --bg-primary: #ffffff;
            --bg-secondary: #f6f8fa;
            --bg-tertiary: #eaeef2;
            --text-primary: #24292f;
            --text-secondary: #656d76;
            --accent: #0969da;
            --accent-hover: #0860ca;
            --accent-secondary: #f59e0b;
            --border: #d1d9e0;
            --success: #1a7f37;
            --danger: #d1242f;
            --danger-hover: #b91c26;
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
            gap: 12px;
            padding: 0 10px;
        }

        .candi {
            color: var(--accent);
        }

        .hire {
            color: var(--accent-secondary);
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
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 15px;
            text-decoration: none;
            color: var(--text-primary);
            will-change: transform, background-color;
            position: relative;
            overflow: hidden;
            transform: translateZ(0);
        }

        .nav-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(88, 166, 255, 0.1), transparent);
            transition: left 0.5s ease;
        }

        .nav-item:hover {
            transform: translateX(4px);
        }

        .nav-item:hover::before {
            left: 100%;
        }

        .nav-item.active {
            background-color: var(--bg-tertiary);
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
        }

        /* Welcome Section */
        .welcome-section {
            background: linear-gradient(135deg, var(--bg-secondary), var(--bg-tertiary));
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 24px;
            border: 1px solid var(--border);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .welcome-content {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .welcome-avatar {
            flex-shrink: 0;
        }

        .avatar-placeholder {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent), var(--accent-hover));
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(88, 166, 255, 0.3);
        }

        .avatar-initials {
            font-size: 24px;
            font-weight: bold;
            color: white;
        }

        .welcome-text {
            flex: 1;
        }

        .welcome-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--text-primary);
            margin: 0 0 4px 0;
        }

        .welcome-name {
            font-size: 14px;
            color: var(--text-secondary);
            margin: 0;
        }

        .edit-profile-btn {
            background: linear-gradient(135deg, var(--accent), var(--accent-hover));
            color: white;
            border: none;
            border-radius: 12px;
            padding: 12px 20px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 12px rgba(88, 166, 255, 0.3);
        }

        .edit-profile-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(88, 166, 255, 0.4);
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border);
        }

        .page-title {
            font-size: 36px;
            font-weight: bold;
            color: var(--accent);
        }

        .header-actions {
            display: flex;
            gap: 12px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            will-change: transform, box-shadow;
        }

        .btn-secondary {
            background-color: var(--bg-tertiary);
            color: var(--text-primary);
            border: 1px solid var(--border);
        }

        .btn-secondary:hover {
            background-color: var(--bg-secondary);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .btn-primary {
            background-color: var(--accent);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--accent-hover);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(88, 166, 255, 0.3);
        }

        /* CV Builder Form */
        .cv-builder {
            background-color: var(--bg-secondary);
            border-radius: 12px;
            border: 1px solid var(--border);
            overflow: hidden;
        }

        .cv-tabs {
            display: flex;
            background-color: var(--bg-tertiary);
            border-bottom: 1px solid var(--border);
        }

        .cv-tab {
            flex: 1;
            padding: 16px 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            font-weight: 500;
            border-bottom: 3px solid transparent;
        }

        .cv-tab.active {
            background-color: var(--bg-secondary);
            color: var(--accent);
            border-bottom-color: var(--accent);
        }

        .cv-tab:hover:not(.active) {
            background-color: var(--bg-secondary);
        }

        .tab-content {
            display: block;
        }

        .cv-form {
            padding: 30px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 25px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-label {
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text-primary);
            font-size: 14px;
        }

        .form-input,
        .form-textarea {
            background-color: var(--bg-primary);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 12px;
            color: var(--text-primary);
            font-size: 15px;
            transition: border-color 0.2s;
        }

        .form-input:focus,
        .form-textarea:focus {
            outline: none;
            border-color: var(--accent);
        }

        .form-textarea {
            resize: vertical;
            min-height: 120px;
        }

        .file-upload {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .file-input {
            display: none;
        }

        .file-button {
            background-color: var(--bg-tertiary);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 10px 16px;
            cursor: pointer;
            transition: background-color 0.2s;
            font-size: 14px;
        }

        .file-button:hover {
            background-color: var(--bg-primary);
        }

        .file-status {
            color: var(--text-secondary);
            font-size: 14px;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            padding-top: 20px;
            border-top: 1px solid var(--border);
        }

        .success-message {
            background-color: rgba(63, 185, 80, 0.1);
            border: 1px solid var(--success);
            color: var(--success);
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Entry sections */
        .entry-section {
            margin-bottom: 30px;
            padding: 20px;
            background-color: var(--bg-primary);
            border-radius: 8px;
            border: 1px solid var(--border);
        }

        .entry-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border);
        }

        .entry-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--text-primary);
        }

        .btn-remove {
            background-color: var(--danger);
            color: white;
            padding: 6px 12px;
            font-size: 12px;
        }

        .btn-remove:hover {
            background-color: #da3633;
        }

        .btn-add {
            margin-top: 15px;
            width: 100%;
        }

        /* Right Sidebar */
        .right-sidebar {
            width: 320px;
            padding: 20px;
            border-left: 1px solid var(--border);
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
        }

        /* Logout Button */
        .logout-container {
            margin-top: 20px;
            padding: 0 10px;
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
            background: linear-gradient(135deg, var(--accent), var(--accent-hover));
            color: white;
            border-color: var(--accent);
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

        /* Theme transition effects */
        .theme-toggle-btn[data-theme="dark"] i {
            color: #ffd700;
            text-shadow: 0 0 10px rgba(255, 215, 0, 0.3);
        }

        .theme-toggle-btn[data-theme="light"] i {
            color: #ffa500;
            text-shadow: 0 0 10px rgba(255, 165, 0, 0.3);
        }

        .sidebar-section {
            background-color: var(--bg-secondary);
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 20px;
            border: 1px solid var(--border);
        }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--border);
            color: var(--text-primary);
        }

        .cv-tip {
            padding: 12px 0;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .cv-tip:last-child {
            border-bottom: none;
        }

        .tip-icon {
            color: var(--accent);
            margin-top: 2px;
        }

        .tip-content h4 {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .tip-content p {
            font-size: 13px;
            color: var(--text-secondary);
            line-height: 1.4;
        }

        /* Download Success Animation */
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        /* Popup Overlay */
        .popup-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 10000;
            backdrop-filter: blur(5px);
        }

        .popup-content {
            background: var(--bg-secondary);
            border-radius: 16px;
            padding: 30px;
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
            border: 1px solid var(--border);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            animation: popupSlideIn 0.3s ease-out;
        }

        .popup-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border);
        }

        .popup-title {
            font-size: 24px;
            font-weight: 600;
            color: var(--accent);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .popup-close {
            background: none;
            border: none;
            color: var(--text-secondary);
            font-size: 24px;
            cursor: pointer;
            padding: 5px;
            border-radius: 50%;
            transition: all 0.2s;
        }

        .popup-close:hover {
            background: var(--bg-tertiary);
            color: var(--text-primary);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            color: var(--text-primary);
            font-weight: 500;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            background: var(--bg-primary);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 12px 16px;
            color: var(--text-primary);
            font-size: 14px;
            transition: border-color 0.2s;
            box-sizing: border-box;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(88, 166, 255, 0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }

        .form-group input::placeholder,
        .form-group textarea::placeholder {
            color: var(--text-secondary);
        }

        .form-group select option {
            background: var(--bg-primary);
            color: var(--text-primary);
        }

        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid var(--border);
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: var(--accent);
            color: white;
        }

        .btn-primary:hover {
            background: var(--accent-hover);
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: var(--bg-tertiary);
            color: var(--text-primary);
            border: 1px solid var(--border);
        }

        .btn-secondary:hover {
            background: var(--bg-primary);
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none !important;
        }

        @keyframes popupSlideIn {
            from {
                opacity: 0;
                transform: scale(0.9) translateY(-20px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .right-sidebar {
                display: none;
            }
        }

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
            .page-header {
                flex-direction: column;
                gap: 16px;
                align-items: stretch;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Left Navigation -->
        <div class="left-nav">
            <div class="logo">
               <span class="candiHire">
  <span class="candi">Candi</span><span class="hire">Hire</span>
</span>
            </div>
            
            <!-- Welcome Section -->
            <div class="welcome-section" style="background: var(--bg-tertiary); padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid var(--border);">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div id="candidateAvatar" style="width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 16px; <?php echo $candidateProfilePicture ? 'background-image: url(' . $candidateProfilePicture . '); background-size: cover; background-position: center;' : 'background: linear-gradient(135deg, var(--accent), var(--accent-secondary));'; ?>">
                        <?php echo $candidateProfilePicture ? '' : strtoupper(substr($candidateName, 0, 1)); ?>
                    </div>
                    <div>
                        <div style="color: var(--text-primary); font-weight: 600; font-size: 14px;">Welcome back!</div>
                        <div id="candidateNameDisplay" style="color: var(--text-secondary); font-size: 12px;"><?php echo htmlspecialchars($candidateName); ?></div>
                    </div>
                </div>
                <button id="editProfileBtn" style="background: var(--accent); color: white; border: none; border-radius: 6px; padding: 8px 12px; font-size: 12px; cursor: pointer; margin-top: 10px; width: 100%; transition: background 0.2s;" onmouseover="this.style.background='var(--accent-hover)'" onmouseout="this.style.background='var(--accent)'">
                    <i class="fas fa-user-edit" style="margin-right: 6px;"></i>Edit Profile
                </button>
            </div>
            
            <!-- Main Menu Section -->
            <div class="nav-section">
                <div class="nav-section-title">Main menu</div>
                <div class="nav-item" data-href="CandidateDashboard.php">
                    <i class="fas fa-home"></i>
                    <span>News feed</span>
                </div>
                <div class="nav-item active" data-href="CvBuilder.php">
                    <i class="fas fa-file-alt"></i>
                    <span>CV builder</span>
                </div>
                <div class="nav-item" data-href="applicationstatus.php">
                    <i class="fas fa-clipboard-list"></i>
                    <span>Application status</span>
                </div>
            </div>
            
            <!-- Interviews & Exams Section -->
            <div class="nav-section">
                <div class="nav-section-title">Interviews & Exams</div>
                <div class="nav-item" data-href="interview_schedule.php">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Interview schedule</span>
                </div>
                <div class="nav-item" data-href="attendexam.php">
                    <i class="fas fa-pencil-alt"></i>
                    <span>Attend Exam</span>
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
            <!-- Page Header -->
            <div class="page-header">
                <h1 class="page-title">CV Builder</h1>
            </div>


            <!-- CV Builder Form -->
            <div class="cv-builder">
                <div class="cv-tabs">
                    <div class="cv-tab active" onclick="switchTab('personal', this)">Personal Info</div>
                    <div class="cv-tab" onclick="switchTab('experience', this)">Experience</div>
                    <div class="cv-tab" onclick="switchTab('education', this)">Education</div>
                    <div class="cv-tab" onclick="switchTab('skills', this)">Skills</div>
                    <div class="cv-tab" onclick="switchTab('projects', this)">Projects</div>
                </div>

                <form class="cv-form" method="POST">
                    <div id="personal-tab" class="tab-content">
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">First Name</label>
                                <input type="text" name="firstName" class="form-input" value="<?php echo $firstName ?? 'John'; ?>" placeholder="Enter your first name">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Last Name</label>
                                <input type="text" name="lastName" class="form-input" value="<?php echo $lastName ?? 'Doe'; ?>" placeholder="Enter your last name">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-input" value="<?php echo $email ?? 'john.doe@example.com'; ?>" placeholder="Enter your email address">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Phone</label>
                                <input type="tel" name="phone" class="form-input" value="<?php echo $phone ?? '+1 (555) 123-4567'; ?>" placeholder="Enter your phone number">
                            </div>
                            <div class="form-group full-width">
                                <label class="form-label">Address</label>
                                <input type="text" name="address" class="form-input" value="<?php echo $address ?? '123 Main St, City, Country'; ?>" placeholder="Enter your full address">
                            </div>
                            <div class="form-group full-width">
                                <label class="form-label">Professional Summary</label>
                                <textarea name="summary" class="form-textarea" placeholder="Write a brief summary of your professional background and career objectives"><?php echo $summary ?? 'Experienced full stack developer with 5+ years of experience in MEAN stack. Proven track record of delivering high-quality web applications and leading development teams.'; ?></textarea>
                            </div>
                            <div class="form-group full-width">
                                <label class="form-label">Profile Picture</label>
                                <div class="file-upload">
                                    <input type="file" id="profile-picture" class="file-input" accept="image/*">
                                    <label for="profile-picture" class="file-button">
                                        <i class="fas fa-upload"></i>
                                        Choose File
                                    </label>
                                    <span class="file-status">No file chosen</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="experience-tab" class="tab-content" style="display: none;">
                        <div id="experience-container">
                            <div class="entry-section">
                                <div class="entry-header">
                                    <div class="entry-title">Experience #1</div>
                                    <button type="button" class="btn btn-remove" onclick="removeEntry(this)">
                                        <i class="fas fa-trash"></i>
                                        Remove
                                    </button>
                                </div>
                                <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Job Title</label>
                                <input type="text" name="jobTitle[]" class="form-input" value="<?php echo $experiences[0]['title'] ?? 'Senior Frontend Developer'; ?>" placeholder="Enter your job title">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Company</label>
                                <input type="text" name="company[]" class="form-input" value="<?php echo $experiences[0]['company'] ?? 'Tech Innovations Ltd.'; ?>" placeholder="Enter company name">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Start Date</label>
                                <input type="date" name="startDate[]" class="form-input" value="<?php echo $experiences[0]['startDate'] ?? '2021-03-15'; ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">End Date</label>
                                <input type="date" name="endDate[]" class="form-input" value="<?php echo $experiences[0]['endDate'] ?? '2024-01-30'; ?>">
                            </div>
                                    <div class="form-group full-width">
                                        <label class="form-label">Job Description</label>
                                        <textarea name="jobDescription[]" class="form-textarea" placeholder="Describe your responsibilities and achievements"><?php echo $experiences[0]['description'] ?? '• Led a team of 5 developers in building scalable web applications
• Implemented modern frontend frameworks resulting in 40% performance improvement
• Collaborated with UX designers to create responsive user interfaces
• Mentored junior developers and conducted code reviews'; ?></textarea>
                                    </div>
                                    <div class="form-group full-width">
                                        <label class="form-label">Location</label>
                                        <input type="text" name="jobLocation[]" class="form-input" value="<?php echo $experiences[0]['location'] ?? 'San Francisco, CA'; ?>" placeholder="Job location">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-secondary btn-add" onclick="addExperience()">
                            <i class="fas fa-plus"></i>
                            Add Another Experience
                        </button>
                    </div>

                    <div id="education-tab" class="tab-content" style="display: none;">
                        <div id="education-container">
                            <div class="entry-section">
                                <div class="entry-header">
                                    <div class="entry-title">Education #1</div>
                                    <button type="button" class="btn btn-remove" onclick="removeEntry(this)">
                                        <i class="fas fa-trash"></i>
                                        Remove
                                    </button>
                                </div>
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label class="form-label">Degree</label>
                                        <input type="text" name="degree[]" class="form-input" value="<?php echo $education[0]['degree'] ?? 'Bachelor of Science in Computer Science'; ?>" placeholder="Enter your degree">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Institution</label>
                                        <input type="text" name="institution[]" class="form-input" value="<?php echo $education[0]['institution'] ?? 'Stanford University'; ?>" placeholder="Enter institution name">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Start Year</label>
                                        <input type="number" name="eduStartYear[]" class="form-input" value="<?php echo $education[0]['startYear'] ?? '2016'; ?>" placeholder="2020" min="1980" max="2030">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">End Year</label>
                                        <input type="number" name="eduEndYear[]" class="form-input" value="<?php echo $education[0]['endYear'] ?? '2020'; ?>" placeholder="2024" min="1980" max="2030">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">GPA</label>
                                        <input type="text" name="gpa[]" class="form-input" value="<?php echo $education[0]['gpa'] ?? '3.8/4.0'; ?>" placeholder="3.5/4.0">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Location</label>
                                        <input type="text" name="eduLocation[]" class="form-input" value="<?php echo $education[0]['location'] ?? 'Stanford, CA'; ?>" placeholder="Institution location">
                                    </div>
                                    <div class="form-group full-width">
                                        <label class="form-label">Relevant Coursework</label>
                                        <textarea name="coursework[]" class="form-textarea" placeholder="List relevant courses, projects, or achievements"><?php echo $education[0]['coursework'] ?? 'Data Structures and Algorithms, Software Engineering, Database Systems, Web Development, Machine Learning, Computer Networks, Operating Systems'; ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-secondary btn-add" onclick="addEducation()">
                            <i class="fas fa-plus"></i>
                            Add Another Education
                        </button>
                    </div>

                    <div id="skills-tab" class="tab-content" style="display: none;">
                        <div class="form-grid">
                            <div class="form-group full-width">
                                <label class="form-label">Programming Languages</label>
                                <input type="text" name="programmingLanguages" class="form-input" value="<?php echo $skills['programmingLanguages'] ?? 'JavaScript, TypeScript, Python, Java, C++'; ?>" placeholder="e.g., JavaScript, Python, Java">
                            </div>
                            <div class="form-group full-width">
                                <label class="form-label">Frameworks & Libraries</label>
                                <input type="text" name="frameworks" class="form-input" value="<?php echo $skills['frameworks'] ?? 'React, Node.js, Express, Angular, Vue.js'; ?>" placeholder="e.g., React, Angular, Django">
                            </div>
                            <div class="form-group full-width">
                                <label class="form-label">Databases</label>
                                <input type="text" name="databases" class="form-input" value="<?php echo $skills['databases'] ?? 'MongoDB, PostgreSQL, MySQL, Redis'; ?>" placeholder="e.g., MongoDB, MySQL, PostgreSQL">
                            </div>
                            <div class="form-group full-width">
                                <label class="form-label">Tools & Technologies</label>
                                <input type="text" name="tools" class="form-input" value="<?php echo $skills['tools'] ?? 'Git, Docker, AWS, Jenkins, Webpack'; ?>" placeholder="e.g., Git, Docker, AWS, Jenkins">
                            </div>
                            <div class="form-group full-width">
                                <label class="form-label">Soft Skills</label>
                                <input type="text" name="softSkills" class="form-input" value="<?php echo $skills['softSkills'] ?? 'Team Leadership, Problem Solving, Communication, Project Management'; ?>" placeholder="e.g., Leadership, Communication, Problem Solving">
                            </div>
                            <div class="form-group full-width">
                                <label class="form-label">Languages</label>
                                <input type="text" name="languages" class="form-input" value="<?php echo $skills['languages'] ?? 'English (Native), Spanish (Fluent), French (Conversational)'; ?>" placeholder="e.g., English (Native), Spanish (Fluent)">
                            </div>
                            <div class="form-group full-width">
                                <label class="form-label">Certifications</label>
                                <textarea name="certifications" class="form-textarea" placeholder="List your professional certifications"><?php echo $skills['certifications'] ?? '• AWS Certified Solutions Architect - Associate (2023)
• Google Cloud Professional Developer (2022)
• Scrum Master Certification (2021)
• MongoDB Certified Developer (2020)'; ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div id="projects-tab" class="tab-content" style="display: none;">
                        <div id="projects-container">
                            <div class="entry-section">
                                <div class="entry-header">
                                    <div class="entry-title">Project #1</div>
                                    <button type="button" class="btn btn-remove" onclick="removeEntry(this)">
                                        <i class="fas fa-trash"></i>
                                        Remove
                                    </button>
                                </div>
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label class="form-label">Project Name</label>
                                        <input type="text" name="projectName[]" class="form-input" value="<?php echo $projects[0]['name'] ?? 'E-Commerce Platform'; ?>" placeholder="Enter project name">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Role</label>
                                        <input type="text" name="projectRole[]" class="form-input" value="<?php echo $projects[0]['role'] ?? 'Lead Developer'; ?>" placeholder="Your role in the project">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Start Date</label>
                                        <input type="date" name="projectStartDate[]" class="form-input" value="<?php echo $projects[0]['startDate'] ?? '2023-01-15'; ?>">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">End Date</label>
                                        <input type="date" name="projectEndDate[]" class="form-input" value="<?php echo $projects[0]['endDate'] ?? '2023-08-30'; ?>">
                                    </div>
                                    <div class="form-group full-width">
                                        <label class="form-label">Project Description</label>
                                        <textarea name="projectDescription[]" class="form-textarea" placeholder="Describe the project, your contributions, and outcomes"><?php echo $projects[0]['description'] ?? 'Developed a full-stack e-commerce platform serving 10,000+ users
• Built responsive frontend using React and TypeScript
• Implemented RESTful APIs with Node.js and Express
• Integrated payment gateway and inventory management system
• Achieved 99.9% uptime and 40% improvement in page load times'; ?></textarea>
                                    </div>
                                    <div class="form-group full-width">
                                        <label class="form-label">Technologies Used</label>
                                        <input type="text" name="projectTechnologies[]" class="form-input" value="<?php echo $projects[0]['technologies'] ?? 'React, Node.js, MongoDB, Stripe API, AWS'; ?>" placeholder="Technologies and tools used">
                                    </div>
                                    <div class="form-group full-width">
                                        <label class="form-label">Project URL (Optional)</label>
                                        <input type="url" name="projectUrl[]" class="form-input" value="<?php echo $projects[0]['url'] ?? 'https://github.com/johndoe/ecommerce-platform'; ?>" placeholder="Project or demo URL">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-secondary btn-add" onclick="addProject()">
                            <i class="fas fa-plus"></i>
                            Add Another Project
                        </button>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="previewCV()">
                            <i class="fas fa-eye"></i>
                            Preview CV
                        </button>
                        <button type="button" class="btn btn-primary" onclick="downloadCV()">
                            <i class="fas fa-download"></i>
                            Download CV
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Right Sidebar -->
        <div class="right-sidebar">
            <!-- CV Building Tips -->
            <div class="sidebar-section">
                <div class="section-title">CV Building Tips</div>
                <div class="cv-tip">
                    <i class="fas fa-lightbulb tip-icon"></i>
                    <div class="tip-content">
                        <h4>Keep it concise</h4>
                        <p>Aim for 1-2 pages maximum. Recruiters spend only 6 seconds scanning each CV.</p>
                    </div>
                </div>
                <div class="cv-tip">
                    <i class="fas fa-target tip-icon"></i>
                    <div class="tip-content">
                        <h4>Tailor for each job</h4>
                        <p>Customize your CV for each position by highlighting relevant skills and experience.</p>
                    </div>
                </div>
                <div class="cv-tip">
                    <i class="fas fa-chart-line tip-icon"></i>
                    <div class="tip-content">
                        <h4>Use action verbs</h4>
                        <p>Start bullet points with strong action verbs like "developed", "managed", "created".</p>
                    </div>
                </div>
                <div class="cv-tip">
                    <i class="fas fa-spell-check tip-icon"></i>
                    <div class="tip-content">
                        <h4>Proofread carefully</h4>
                        <p>Check for spelling and grammar errors. Ask someone else to review your CV.</p>
                    </div>
                </div>
                <div class="cv-tip">
                    <i class="fas fa-file-pdf tip-icon"></i>
                    <div class="tip-content">
                        <h4>Save as PDF</h4>
                        <p>Always save and send your CV as a PDF to preserve formatting.</p>
                    </div>
                </div>
            </div>

            <!-- CV Templates -->
            <div class="sidebar-section">
                <div class="section-title">CV Templates</div>
                <div class="cv-tip">
                    <i class="fas fa-file-alt tip-icon"></i>
                    <div class="tip-content">
                        <h4>Modern Template</h4>
                        <p>Clean and professional design perfect for tech roles</p>
                    </div>
                </div>
                <div class="cv-tip">
                    <i class="fas fa-briefcase tip-icon"></i>
                    <div class="tip-content">
                        <h4>Executive Template</h4>
                        <p>Sophisticated layout for senior management positions</p>
                    </div>
                </div>
                <div class="cv-tip">
                    <i class="fas fa-paint-brush tip-icon"></i>
                    <div class="tip-content">
                        <h4>Creative Template</h4>
                        <p>Eye-catching design for creative and design roles</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Profile Edit Popup -->
    <div id="profileEditPopup" class="popup-overlay" style="display: none;">
        <div class="popup-content">
            <div class="popup-header">
                <div class="popup-title">
                    <i class="fas fa-user-edit"></i>
                    Edit Profile
                </div>
                <button class="popup-close" onclick="closeProfileEditPopup()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="profileEditForm" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="profilePicture">Profile Picture</label>
                    <input type="file" id="profilePicture" name="profilePicture" accept="image/*">
                    <div id="currentProfilePicture" style="margin-top: 10px; display: none;">
                        <img id="profilePicturePreview" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 2px solid var(--border);" />
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="fullName">Full Name *</label>
                    <input type="text" id="fullName" name="fullName" required>
                </div>
                
                <div class="form-group">
                    <label for="phoneNumber">Phone Number *</label>
                    <input type="tel" id="phoneNumber" name="phoneNumber" required>
                </div>
                
                <div class="form-group">
                    <label for="workType">Work Type *</label>
                    <select id="workType" name="workType" required>
                        <option value="">Select Work Type</option>
                        <option value="full-time">Full-time</option>
                        <option value="part-time">Part-time</option>
                        <option value="contract">Contract</option>
                        <option value="freelance">Freelance</option>
                        <option value="internship">Internship</option>
                        <option value="fresher">Fresher</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="yearsOfExperience">Years of Experience</label>
                    <input type="number" id="yearsOfExperience" name="yearsOfExperience" 
                           placeholder="Enter years of experience" min="0" max="50" 
                           style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px; background-color: var(--bg-secondary); color: var(--text-primary); font-size: 14px;">
                </div>
                
                <div class="form-group">
                    <label for="location">Location</label>
                    <input type="text" id="location" name="location" placeholder="City, Country">
                </div>
                
                <div class="form-group">
                    <label for="skills">Skills</label>
                    <textarea id="skills" name="skills" placeholder="List your key skills separated by commas"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="summary">Professional Summary</label>
                    <textarea id="summary" name="summary" placeholder="Brief description about yourself and your professional background"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="linkedin">LinkedIn Profile</label>
                    <input type="url" id="linkedin" name="linkedin" placeholder="https://linkedin.com/in/yourprofile">
                </div>
                
                <div class="form-group">
                    <label for="github">GitHub Profile</label>
                    <input type="url" id="github" name="github" placeholder="https://github.com/yourusername">
                </div>
                
                <div class="form-group">
                    <label for="portfolio">Portfolio Website</label>
                    <input type="url" id="portfolio" name="portfolio" placeholder="https://yourportfolio.com">
                </div>
                
                <div class="form-group">
                    <label for="education">Education/Degree</label>
                    <input type="text" id="education" name="education" placeholder="e.g., Bachelor's in Computer Science">
                </div>
                
                <div class="form-group">
                    <label for="institute">Institute/University</label>
                    <input type="text" id="institute" name="institute" placeholder="e.g., MIT, Stanford University">
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeProfileEditPopup()">
                        <i class="fas fa-times"></i>
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary" id="submitProfileUpdate">
                        <i class="fas fa-save"></i>
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Tab switching functionality
        function switchTab(tabName, clickedEl) {
            document.querySelectorAll('.cv-tab').forEach(tab => {
                tab.classList.remove('active');
            });
            if (clickedEl) {
                clickedEl.classList.add('active');
            }
            document.querySelectorAll('.tab-content').forEach(content => {
                content.style.display = 'none';
            });
            var target = document.getElementById(tabName + '-tab');
            if (target) {
                target.style.display = 'block';
            }
        }

        // File upload functionality
        document.getElementById('profile-picture').addEventListener('change', function(e) {
            const fileStatus = document.querySelector('.file-status');
            const fileInput = e.target;
            
            if (fileInput.files.length > 0) {
                const file = fileInput.files[0];
                fileStatus.textContent = file.name;
                
                // Create preview of the image
                const reader = new FileReader();
                reader.onload = function(e) {
                    // Store the image data for CV generation
                    fileInput.dataset.imageData = e.target.result;
                    
                    // Show preview in the form
                    let preview = document.querySelector('.profile-picture-preview');
                    if (!preview) {
                        preview = document.createElement('div');
                        preview.className = 'profile-picture-preview';
                        preview.style.cssText = `
                            margin-top: 10px;
                            text-align: center;
                        `;
                        fileInput.parentNode.appendChild(preview);
                    }
                    
                    preview.innerHTML = `
                        <img src="${e.target.result}" alt="Profile Picture Preview" style="
                            width: 100px;
                            height: 100px;
                            object-fit: cover;
                            border-radius: 8px;
                            border: 2px solid var(--border);
                        ">
                        <div style="margin-top: 5px; font-size: 12px; color: var(--text-secondary);">
                            Profile picture preview
                        </div>
                    `;
                };
                reader.readAsDataURL(file);
            } else {
                fileStatus.textContent = 'No file chosen';
                fileInput.dataset.imageData = '';
                
                // Remove preview
                const preview = document.querySelector('.profile-picture-preview');
                if (preview) {
                    preview.remove();
                }
            }
        });

        // Collect CV form data into a structured object
        function collectCvData() {
            var data = {};
            data.firstName = (document.querySelector('input[name="firstName"]') || { value: '' }).value.trim();
            data.lastName = (document.querySelector('input[name="lastName"]') || { value: '' }).value.trim();
            data.email = (document.querySelector('input[name="email"]') || { value: '' }).value.trim();
            data.phone = (document.querySelector('input[name="phone"]') || { value: '' }).value.trim();
            data.address = (document.querySelector('input[name="address"]') || { value: '' }).value.trim();
            data.summary = (document.querySelector('textarea[name="summary"]') || { value: '' }).value.trim();
            
            // Get profile picture data
            const profilePictureInput = document.getElementById('profile-picture');
            data.profilePicture = profilePictureInput ? profilePictureInput.dataset.imageData || '' : '';

            data.experiences = [];
            document.querySelectorAll('#experience-container .entry-section').forEach(function(entry) {
                var item = {
                    title: (entry.querySelector('input[name="jobTitle[]"]') || { value: '' }).value.trim(),
                    company: (entry.querySelector('input[name="company[]"]') || { value: '' }).value.trim(),
                    startDate: (entry.querySelector('input[name="startDate[]"]') || { value: '' }).value.trim(),
                    endDate: (entry.querySelector('input[name="endDate[]"]') || { value: '' }).value.trim(),
                    description: (entry.querySelector('textarea[name="jobDescription[]"]') || { value: '' }).value.trim(),
                    location: (entry.querySelector('input[name="jobLocation[]"]') || { value: '' }).value.trim()
                };
                if (item.title || item.company || item.description) {
                    data.experiences.push(item);
                }
            });

            data.education = [];
            document.querySelectorAll('#education-container .entry-section').forEach(function(entry) {
                var item = {
                    degree: (entry.querySelector('input[name="degree[]"]') || { value: '' }).value.trim(),
                    institution: (entry.querySelector('input[name="institution[]"]') || { value: '' }).value.trim(),
                    startYear: (entry.querySelector('input[name="eduStartYear[]"]') || { value: '' }).value.trim(),
                    endYear: (entry.querySelector('input[name="eduEndYear[]"]') || { value: '' }).value.trim(),
                    gpa: (entry.querySelector('input[name="gpa[]"]') || { value: '' }).value.trim(),
                    location: (entry.querySelector('input[name="eduLocation[]"]') || { value: '' }).value.trim(),
                    coursework: (entry.querySelector('textarea[name="coursework[]"]') || { value: '' }).value.trim()
                };
                if (item.degree || item.institution) {
                    data.education.push(item);
                }
            });

            data.skills = {
                programmingLanguages: (document.querySelector('input[name="programmingLanguages"]') || { value: '' }).value.trim(),
                frameworks: (document.querySelector('input[name="frameworks"]') || { value: '' }).value.trim(),
                databases: (document.querySelector('input[name="databases"]') || { value: '' }).value.trim(),
                tools: (document.querySelector('input[name="tools"]') || { value: '' }).value.trim(),
                softSkills: (document.querySelector('input[name="softSkills"]') || { value: '' }).value.trim(),
                languages: (document.querySelector('input[name="languages"]') || { value: '' }).value.trim(),
                certifications: (document.querySelector('textarea[name="certifications"]') || { value: '' }).value.trim()
            };

            data.projects = [];
            document.querySelectorAll('#projects-container .entry-section').forEach(function(entry) {
                var item = {
                    name: (entry.querySelector('input[name="projectName[]"]') || { value: '' }).value.trim(),
                    role: (entry.querySelector('input[name="projectRole[]"]') || { value: '' }).value.trim(),
                    startDate: (entry.querySelector('input[name="projectStartDate[]"]') || { value: '' }).value.trim(),
                    endDate: (entry.querySelector('input[name="projectEndDate[]"]') || { value: '' }).value.trim(),
                    description: (entry.querySelector('textarea[name="projectDescription[]"]') || { value: '' }).value.trim(),
                    technologies: (entry.querySelector('input[name="projectTechnologies[]"]') || { value: '' }).value.trim(),
                    url: (entry.querySelector('input[name="projectUrl[]"]') || { value: '' }).value.trim()
                };
                if (item.name || item.description) {
                    data.projects.push(item);
                }
            });

            return data;
        }

        function buildCvHtml(data) {
            function esc(str) { return (str || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
            function section(title, body) { if (!body) return ''; return '<div class="cv-section">\n  <div class="cv-section-title">' + esc(title) + '</div>\n  <div class="cv-section-body">' + body + '</div>\n</div>'; }

            var fullName = [data.firstName, data.lastName].filter(Boolean).join(' ');
            var contactLine = [data.email, data.phone, data.address].filter(Boolean).join(' • ');

            var summaryHtml = data.summary ? '<p>' + esc(data.summary) + '</p>' : '';

            var expHtml = (data.experiences || []).map(function(e){
                var header = [esc(e.title), esc(e.company)].filter(Boolean).join(' • ');
                var dates = [esc(e.startDate), esc(e.endDate)].filter(Boolean).join(' - ');
                var sub = [dates, esc(e.location)].filter(Boolean).join(' • ');
                var desc = e.description ? '<div class="cv-item-desc">' + esc(e.description).replace(/\n/g,'<br>') + '</div>' : '';
                return '<div class="cv-item">\n  <div class="cv-item-header">' + header + '</div>\n' + (sub? '  <div class="cv-item-sub">' + sub + '</div>\n':'') + desc + '\n</div>';
            }).join('');

            var eduHtml = (data.education || []).map(function(ed){
                var header = [esc(ed.degree), esc(ed.institution)].filter(Boolean).join(' • ');
                var dates = [esc(ed.startYear), esc(ed.endYear)].filter(Boolean).join(' - ');
                var sub = [dates, esc(ed.location)].filter(Boolean).join(' • ');
                var gpa = ed.gpa ? '<div class="cv-item-sub">GPA: ' + esc(ed.gpa) + '</div>' : '';
                var coursework = ed.coursework ? '<div class="cv-item-desc"><strong>Coursework:</strong> ' + esc(ed.coursework) + '</div>' : '';
                return '<div class="cv-item">\n  <div class="cv-item-header">' + header + '</div>\n' + (sub? '  <div class="cv-item-sub">' + sub + '</div>\n':'') + gpa + coursework + '\n</div>';
            }).join('');

            function listLine(label, value) { if (!value) return ''; return '<div class="cv-list-line"><span class="lbl">' + label + ':</span> ' + esc(value) + '</div>'; }
            var skillsHtml = [
                listLine('Programming', data.skills && data.skills.programmingLanguages),
                listLine('Frameworks', data.skills && data.skills.frameworks),
                listLine('Databases', data.skills && data.skills.databases),
                listLine('Tools', data.skills && data.skills.tools),
                listLine('Soft Skills', data.skills && data.skills.softSkills),
                listLine('Languages', data.skills && data.skills.languages),
                data.skills && data.skills.certifications ? '<div class="cv-list-line"><span class="lbl">Certifications:</span><br>' + esc(data.skills.certifications).replace(/\n/g,'<br>') + '</div>' : ''
            ].filter(Boolean).join('');

            var projHtml = (data.projects || []).map(function(p){
                var header = [esc(p.name), esc(p.role)].filter(Boolean).join(' • ');
                var dates = [esc(p.startDate), esc(p.endDate)].filter(Boolean).join(' - ');
                var sub = [dates, esc(p.technologies)].filter(Boolean).join(' • ');
                var url = p.url ? '<div class="cv-item-sub">' + esc(p.url) + '</div>' : '';
                var desc = p.description ? '<div class="cv-item-desc">' + esc(p.description).replace(/\n/g,'<br>') + '</div>' : '';
                return '<div class="cv-item">\n  <div class="cv-item-header">' + header + '</div>\n' + (sub? '  <div class="cv-item-sub">' + sub + '</div>\n':'') + url + desc + '\n</div>';
            }).join('');

            var html = '<!DOCTYPE html>\n<html>\n<head>\n<meta charset="UTF-8">\n<title>CV - ' + (fullName || 'Candidate') + '</title>\n'
                + '<style>\n'
                + 'body{font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',\'Noto Sans\',Helvetica,Arial,sans-serif;margin:0;padding:32px;color:#111;background:#fff;}\n'
                + '.cv{max-width:900px;margin:0 auto;}\n'
                + '.cv-header{display:flex;justify-content:space-between;align-items:flex-start;border-bottom:2px solid #222;padding-bottom:8px;margin-bottom:18px;}\n'
                + '.cv-name{font-size:28px;font-weight:700;color:#111;}\n'
                + '.cv-contact{font-size:13px;color:#333;text-align:right;white-space:pre-wrap;}\n'
                + '.cv-profile-pic{width:80px;height:80px;border-radius:8px;object-fit:cover;border:2px solid #ddd;margin-left:20px;}\n'
                + '.cv-section{margin-top:18px;}\n'
                + '.cv-section-title{font-size:16px;font-weight:700;color:#222;text-transform:uppercase;letter-spacing:.6px;border-left:4px solid #58a6ff;padding-left:8px;margin-bottom:10px;}\n'
                + '.cv-item{margin-bottom:10px;}\n'
                + '.cv-item-header{font-weight:600;color:#111;}\n'
                + '.cv-item-sub{font-size:12px;color:#555;margin-top:2px;}\n'
                + '.cv-item-desc{font-size:13px;color:#222;margin-top:6px;line-height:1.5;}\n'
                + '.cv-list-line{font-size:13px;color:#222;margin:4px 0;}\n'
                + '.cv-list-line .lbl{font-weight:600;color:#111;}\n'
                + '@media print { .no-print{ display:none !important; } body{ padding:0; } }\n'
                + '</style>\n</head>\n<body>\n<div class="cv">\n'
                + '<div class="no-print" style="display:flex;justify-content:flex-end;gap:8px;margin-bottom:8px;">\n'
                + '  <button onclick="window.print()" style="background:#58a6ff;color:#fff;border:none;border-radius:6px;padding:8px 12px;font-weight:600;cursor:pointer;">Download PDF</button>\n'
                + '  <button onclick="window.close()" style="background:#eee;color:#111;border:none;border-radius:6px;padding:8px 12px;font-weight:600;cursor:pointer;">Close</button>\n'
                + '</div>\n'
                + '<div class="cv-header">\n  <div>\n    <div class="cv-name">' + (fullName || 'Candidate') + '</div>\n    <div class="cv-contact">' + contactLine + '</div>\n  </div>\n' 
                + (data.profilePicture ? '  <img src="' + data.profilePicture + '" alt="Profile Picture" class="cv-profile-pic">\n' : '')
                + '</div>\n'
                + section('Professional Summary', summaryHtml)
                + section('Experience', expHtml)
                + section('Education', eduHtml)
                + section('Skills', skillsHtml)
                + section('Projects', projHtml)
                + '</div>\n</body>\n</html>';

            return html;
        }

        // Track if action is in progress
        var actionInProgress = false;

        // Profile editing functionality
        function setupProfileEditing() {
            console.log('Setting up profile editing...');
            
            const editProfileBtn = document.getElementById('editProfileBtn');
            const profilePopup = document.getElementById('profileEditPopup');
            const profileForm = document.getElementById('profileEditForm');
            
            if (!editProfileBtn || !profilePopup || !profileForm) {
                console.error('Profile editing elements not found');
                return;
            }
            
            // Open profile edit popup
            editProfileBtn.addEventListener('click', function() {
                console.log('Opening profile edit popup');
                loadCurrentProfile();
                profilePopup.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            });
            
            // Handle form submission
            profileForm.addEventListener('submit', function(e) {
                e.preventDefault();
                updateProfile();
            });
            
            // Handle profile picture preview
            const profilePictureInput = document.getElementById('profilePicture');
            if (profilePictureInput) {
                profilePictureInput.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const preview = document.getElementById('profilePicturePreview');
                            const currentPicture = document.getElementById('currentProfilePicture');
                            if (preview && currentPicture) {
                                preview.src = e.target.result;
                                currentPicture.style.display = 'block';
                            }
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }
            
            // Close popup when clicking outside
            profilePopup.addEventListener('click', function(e) {
                if (e.target === profilePopup) {
                    closeProfileEditPopup();
                }
            });
            
            console.log('Profile editing setup complete');
        }

        // Load current profile data
        function loadCurrentProfile() {
            console.log('Loading current profile data...');
            
            const candidateId = <?php echo json_encode($sessionCandidateId); ?>;
            
            fetch(`candidate_profile_handler.php?candidateId=${candidateId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const candidate = data.candidate;
                        console.log('Profile data loaded:', candidate);
                        
                        // Populate form fields
                        document.getElementById('fullName').value = candidate.FullName || '';
                        document.getElementById('phoneNumber').value = candidate.PhoneNumber || '';
                        document.getElementById('workType').value = candidate.WorkType || '';
                        document.getElementById('yearsOfExperience').value = candidate.YearsOfExperience || 0;
                        document.getElementById('location').value = candidate.Location || '';
                        document.getElementById('skills').value = candidate.Skills || '';
                        document.getElementById('summary').value = candidate.Summary || '';
                        document.getElementById('linkedin').value = candidate.LinkedIn || '';
                        document.getElementById('github').value = candidate.GitHub || '';
                        document.getElementById('portfolio').value = candidate.Portfolio || '';
                        document.getElementById('education').value = candidate.Education || '';
                        document.getElementById('institute').value = candidate.Institute || '';
                        
                        // Handle profile picture
                        if (candidate.ProfilePicture) {
                            const preview = document.getElementById('profilePicturePreview');
                            const currentPicture = document.getElementById('currentProfilePicture');
                            if (preview && currentPicture) {
                                preview.src = candidate.ProfilePicture;
                                currentPicture.style.display = 'block';
                            }
                        }
                    } else {
                        console.error('Failed to load profile:', data.message);
                        showErrorMessage('Failed to load profile data');
                    }
                })
                .catch(error => {
                    console.error('Error loading profile:', error);
                    showErrorMessage('Network error loading profile');
                });
        }

        // Update profile
        function updateProfile() {
            console.log('Updating profile...');
            
            const form = document.getElementById('profileEditForm');
            const submitBtn = document.getElementById('submitProfileUpdate');
            const formData = new FormData(form);
            
            // Add candidate ID
            const candidateId = <?php echo json_encode($sessionCandidateId); ?>;
            formData.append('candidateId', candidateId);
            
            // Disable button and show loading
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            
            fetch('candidate_profile_handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeProfileEditPopup();
                    showSuccessMessage('Profile updated successfully!');
                    
                    // Update the display with server response data
                    if (data.fullName) {
                        document.getElementById('candidateNameDisplay').textContent = data.fullName;
                        
                        // Update avatar text or image
                        const avatar = document.getElementById('candidateAvatar');
                        if (data.profilePicture) {
                            // Show profile picture
                            avatar.style.backgroundImage = `url(${data.profilePicture})`;
                            avatar.style.backgroundSize = 'cover';
                            avatar.style.backgroundPosition = 'center';
                            avatar.textContent = '';
                        } else {
                            // Show initials
                            avatar.style.backgroundImage = '';
                            avatar.style.background = 'linear-gradient(135deg, var(--accent), var(--accent-secondary))';
                            avatar.textContent = data.fullName.charAt(0).toUpperCase();
                        }
                    }
                } else {
                    showErrorMessage(data.message || 'Failed to update profile');
                }
            })
            .catch(error => {
                console.error('Error updating profile:', error);
                showErrorMessage('Network error. Please try again.');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-save"></i> Save Changes';
            });
        }

        // Close profile edit popup
        function closeProfileEditPopup() {
            const popup = document.getElementById('profileEditPopup');
            const form = document.getElementById('profileEditForm');
            
            popup.style.display = 'none';
            document.body.style.overflow = 'auto';
            form.reset();
            
            // Hide profile picture preview
            const currentPicture = document.getElementById('currentProfilePicture');
            if (currentPicture) {
                currentPicture.style.display = 'none';
            }
        }

        // Show success message
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
                animation: slideIn 0.3s ease-out;
            `;
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }

        // Show error message
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
                animation: slideIn 0.3s ease-out;
            `;
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 5000);
        }

        function previewCV() {
            if (actionInProgress) {
                alert('Please wait, an action is already in progress...');
                return;
            }
            
            if (!validateForm()) return;
            
            actionInProgress = true;
            
            try {
                var data = collectCvData();
                var html = buildCvHtml(data);
                var win = window.open('', '_blank');
                if (!win) { 
                    alert('Pop-up blocked. Please allow pop-ups.'); 
                    actionInProgress = false;
                    return; 
                }
                win.document.open();
                win.document.write(html);
                win.document.close();
                
                // Show success message
                showDownloadSuccess('CV Preview opened successfully!');
            } catch (error) {
                alert('Error generating preview: ' + error.message);
            } finally {
                // Reset flag after a delay
                setTimeout(function() {
                    actionInProgress = false;
                }, 2000);
            }
        }

        function downloadCV() {
            if (actionInProgress) {
                alert('Please wait, an action is already in progress...');
                return;
            }
            
            if (!validateForm()) return;
            
            actionInProgress = true;
            
            try {
                var data = collectCvData();
                var html = buildCvHtml(data);
                
                // Open CV in new window and automatically trigger print dialog for PDF
                var win = window.open('', '_blank', 'width=800,height=600');
                if (!win) { 
                    alert('Pop-up blocked. Please allow pop-ups for PDF download.'); 
                    actionInProgress = false;
                    return; 
                }
                
                win.document.open();
                win.document.write(html);
                win.document.close();
                
                // Wait for content to load, then automatically trigger print dialog
                win.onload = function() { 
                    win.focus(); 
                    // Auto-trigger print dialog immediately
                    setTimeout(function() {
                        win.print();
                        // Close the window after a delay
                        setTimeout(function() {
                            win.close();
                        }, 2000);
                    }, 100);
                };
                
                // Show success message
                showDownloadSuccess('PDF download initiated!');
            } catch (error) {
                alert('Error generating PDF: ' + error.message);
            } finally {
                // Reset flag after a delay
                setTimeout(function() {
                    actionInProgress = false;
                }, 3000);
            }
        }
        
        function showDownloadSuccess(message) {
            // Create a temporary success message
            var successDiv = document.createElement('div');
            successDiv.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: var(--success);
                color: white;
                padding: 12px 20px;
                border-radius: 8px;
                font-weight: 600;
                z-index: 10000;
                box-shadow: 0 4px 12px rgba(0,0,0,0.2);
                animation: slideInRight 0.3s ease-out;
            `;
            successDiv.textContent = message;
            document.body.appendChild(successDiv);
            
            // Remove after 3 seconds
            setTimeout(function() {
                successDiv.remove();
            }, 3000);
        }

        // Enhanced Navigation with Smooth Page Transitions
        document.querySelectorAll('.nav-item').forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                
                const href = this.getAttribute('data-href');
                if (href) {
                    // Don't navigate if already on current page
                    if (href === window.location.pathname.split('/').pop()) {
                        return;
                    }
                    
                    // Add loading animation to clicked item
                    this.style.opacity = '0.7';
                    this.style.transform = 'translateX(5px) scale(0.98)';
                    
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

        // Form validation for preview and download
        function validateForm() {
            const requiredFields = ['firstName', 'lastName', 'email'];
            let isValid = true;
            
            requiredFields.forEach(field => {
                const input = document.querySelector(`input[name="${field}"]`);
                if (!input.value.trim()) {
                    isValid = false;
                    input.style.borderColor = 'var(--danger)';
                } else {
                    input.style.borderColor = 'var(--border)';
                }
            });
            
            if (!isValid) {
                alert('Please fill in all required fields (First Name, Last Name, Email)');
                return false;
            }
            return true;
        }

        // Add Experience Entry
        function addExperience() {
            const container = document.getElementById('experience-container');
            const count = container.children.length + 1;
            
            const newEntry = document.createElement('div');
            newEntry.className = 'entry-section';
            newEntry.innerHTML = `
                <div class="entry-header">
                    <div class="entry-title">Experience #${count}</div>
                    <button type="button" class="btn btn-remove" onclick="removeEntry(this)">
                        <i class="fas fa-trash"></i>
                        Remove
                    </button>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Job Title</label>
                        <input type="text" name="jobTitle[]" class="form-input" placeholder="Enter your job title">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Company</label>
                        <input type="text" name="company[]" class="form-input" placeholder="Enter company name">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="startDate[]" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">End Date</label>
                        <input type="date" name="endDate[]" class="form-input">
                    </div>
                    <div class="form-group full-width">
                        <label class="form-label">Job Description</label>
                        <textarea name="jobDescription[]" class="form-textarea" placeholder="Describe your responsibilities and achievements"></textarea>
                    </div>
                    <div class="form-group full-width">
                        <label class="form-label">Location</label>
                        <input type="text" name="jobLocation[]" class="form-input" placeholder="Job location">
                    </div>
                </div>
            `;
            
            container.appendChild(newEntry);
        }

        // Add Education Entry
        function addEducation() {
            const container = document.getElementById('education-container');
            const count = container.children.length + 1;
            
            const newEntry = document.createElement('div');
            newEntry.className = 'entry-section';
            newEntry.innerHTML = `
                <div class="entry-header">
                    <div class="entry-title">Education #${count}</div>
                    <button type="button" class="btn btn-remove" onclick="removeEntry(this)">
                        <i class="fas fa-trash"></i>
                        Remove
                    </button>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Degree</label>
                        <input type="text" name="degree[]" class="form-input" placeholder="Enter your degree">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Institution</label>
                        <input type="text" name="institution[]" class="form-input" placeholder="Enter institution name">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Start Year</label>
                        <input type="number" name="eduStartYear[]" class="form-input" placeholder="2020" min="1980" max="2030">
                    </div>
                    <div class="form-group">
                        <label class="form-label">End Year</label>
                        <input type="number" name="eduEndYear[]" class="form-input" placeholder="2024" min="1980" max="2030">
                    </div>
                    <div class="form-group">
                        <label class="form-label">GPA</label>
                        <input type="text" name="gpa[]" class="form-input" placeholder="3.5/4.0">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Location</label>
                        <input type="text" name="eduLocation[]" class="form-input" placeholder="Institution location">
                    </div>
                    <div class="form-group full-width">
                        <label class="form-label">Relevant Coursework</label>
                        <textarea name="coursework[]" class="form-textarea" placeholder="List relevant courses, projects, or achievements"></textarea>
                    </div>
                </div>
            `;
            
            container.appendChild(newEntry);
        }

        // Add Project Entry
        function addProject() {
            const container = document.getElementById('projects-container');
            const count = container.children.length + 1;
            
            const newEntry = document.createElement('div');
            newEntry.className = 'entry-section';
            newEntry.innerHTML = `
                <div class="entry-header">
                    <div class="entry-title">Project #${count}</div>
                    <button type="button" class="btn btn-remove" onclick="removeEntry(this)">
                        <i class="fas fa-trash"></i>
                        Remove
                    </button>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Project Name</label>
                        <input type="text" name="projectName[]" class="form-input" placeholder="Enter project name">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Role</label>
                        <input type="text" name="projectRole[]" class="form-input" placeholder="Your role in the project">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="projectStartDate[]" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">End Date</label>
                        <input type="date" name="projectEndDate[]" class="form-input">
                    </div>
                    <div class="form-group full-width">
                        <label class="form-label">Project Description</label>
                        <textarea name="projectDescription[]" class="form-textarea" placeholder="Describe the project, your contributions, and outcomes"></textarea>
                    </div>
                    <div class="form-group full-width">
                        <label class="form-label">Technologies Used</label>
                        <input type="text" name="projectTechnologies[]" class="form-input" placeholder="Technologies and tools used">
                    </div>
                    <div class="form-group full-width">
                        <label class="form-label">Project URL (Optional)</label>
                        <input type="url" name="projectUrl[]" class="form-input" placeholder="Project or demo URL">
                    </div>
                </div>
            `;
            
            container.appendChild(newEntry);
        }

        // Remove Entry
        function removeEntry(button) {
            const entry = button.closest('.entry-section');
            entry.remove();
        }

        // Theme Management
        function initializeTheme() {
            // Get saved theme or default to dark
            const savedTheme = localStorage.getItem('candihire-theme') || 'dark';
            applyTheme(savedTheme);
            updateThemeButton(savedTheme);
        }

        function applyTheme(theme) {
            document.documentElement.setAttribute('data-theme', theme);
            localStorage.setItem('candihire-theme', theme);
        }

        function toggleTheme() {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            applyTheme(newTheme);
            updateThemeButton(newTheme);
            
            // Add smooth transition effect
            document.body.style.transition = 'background-color 0.3s ease, color 0.3s ease';
            setTimeout(() => {
                document.body.style.transition = '';
            }, 300);
        }

        function updateThemeButton(theme) {
            const themeIcon = document.getElementById('themeIcon');
            const themeText = document.getElementById('themeText');
            const themeToggleBtn = document.getElementById('themeToggleBtn');
            
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
        }

        function setupThemeToggle() {
            const themeToggleBtn = document.getElementById('themeToggleBtn');
            if (themeToggleBtn) {
                themeToggleBtn.addEventListener('click', toggleTheme);
            }
        }

        // Initialize theme when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            initializeTheme();
            setupThemeToggle();
            setupProfileEditing();
        });
    </script>
    <script>
        // Logout functionality parity with News Feed
        (function(){
            var btn = document.getElementById('logoutBtn');
            if (btn) {
                btn.addEventListener('click', function(){
                    window.location.href = 'Login&Signup.php';
                });
            }
        })();
    </script>
</body>
</html>
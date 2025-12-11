<?php
// CompanyDashboard.php - Dashboard for companies
require_once 'session_manager.php';

// Check if company is logged in
if (!isCompanyLoggedIn()) {
    header('Location: Login&Signup.php');
    exit;
}

// Get company ID from session
$sessionCompanyId = getCurrentCompanyId();
$companyName = $_SESSION['company_name'] ?? 'Company';

// Load company data from database if not in session
$companyLogo = null;
if (!isset($_SESSION['company_name']) || $_SESSION['company_name'] === 'Company') {
    require_once 'Database.php';
    try {
        if (isset($pdo) && $pdo instanceof PDO) {
            $stmt = $pdo->prepare("SELECT CompanyName, Logo FROM Company_login_info WHERE CompanyID = ?");
            $stmt->execute([$sessionCompanyId]);
            $company = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($company) {
                $companyName = $company['CompanyName'];
                $companyLogo = $company['Logo'];
                $_SESSION['company_name'] = $companyName;
            }
        }
    } catch (Exception $e) {
        error_log("Error loading company data: " . $e->getMessage());
    }
} else {
    // Load logo even if name is in session
    require_once 'Database.php';
    try {
        if (isset($pdo) && $pdo instanceof PDO) {
            $stmt = $pdo->prepare("SELECT Logo FROM Company_login_info WHERE CompanyID = ?");
            $stmt->execute([$sessionCompanyId]);
            $company = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($company && $company['Logo']) {
                $companyLogo = $company['Logo'];
            }
        }
    } catch (Exception $e) {
        error_log("Error loading company logo: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CandiHire - Professional Networking Platform</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            /* Dark Theme (Default) */
            --bg-primary: #0d1117;
            --bg-secondary: #161b22;
            --bg-tertiary: #21262d;
            --text-primary: #c9d1d9;
            --text-secondary: #8b949e;
            --accent-1: #58a6ff; /* Candi color */
            --accent-2: #f59e0b; /* Hire color */
            --accent-hover: #79c0ff;
            --border: #30363d;
            --success: #3fb950;
            --danger: #f85149;
            --danger-hover: #da3633;
            --info: #58a6ff;
            --warning: #f59e0b;
        }

        /* Light Theme */
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
            --danger: #d1242f;
            --danger-hover: #b91c26;
            --info: #0969da;
            --warning: #9a6700;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Noto Sans', Helvetica, Arial, sans-serif;
        }

        /* Super Smooth Theme Transitions */
        * {
            transition: background-color 0.4s cubic-bezier(0.4, 0, 0.2, 1), 
                       color 0.4s cubic-bezier(0.4, 0, 0.2, 1), 
                       border-color 0.4s cubic-bezier(0.4, 0, 0.2, 1),
                       box-shadow 0.3s cubic-bezier(0.4, 0, 0.2, 1),
                       transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Disable transitions for elements that shouldn't animate */
        .theme-toggle-btn i,
        .loading-spinner,
        .fa-spin {
            transition: none !important;
        }

        /* Enhanced Card Animations */
        .job-card, .candidate-card, .company-post-card {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            transform: translateZ(0);
            will-change: transform, box-shadow;
        }

        .job-card:hover, .candidate-card:hover, .company-post-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 
                0 20px 40px rgba(0, 0, 0, 0.1),
                0 0 0 1px rgba(88, 166, 255, 0.1);
        }

        /* Smooth Button Animations */
        .btn, .contact-btn, .view-profile-btn, .create-job-btn {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            transform: translateZ(0);
            will-change: transform, box-shadow, background;
        }

        .btn:hover, .contact-btn:hover, .view-profile-btn:hover, .create-job-btn:hover {
            transform: translateY(-3px) scale(1.05);
        }

        /* Navigation Item Animations */
        .nav-item {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
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

        .nav-item:hover::before {
            left: 100%;
        }

        .nav-item:hover {
            transform: translateX(5px);
            background: linear-gradient(135deg, var(--accent-1), var(--accent-hover));
            color: white;
        }

        /* Form Input Animations */
        .form-group input, .form-group textarea, .form-group select {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            transform: translateZ(0);
        }

        .form-group input:focus, .form-group textarea:focus, .form-group select:focus {
            transform: scale(1.02);
            box-shadow: 
                0 0 0 3px rgba(88, 166, 255, 0.1),
                0 8px 25px rgba(88, 166, 255, 0.15);
        }

        /* Skill Tag Animations */
        .skill-tag {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            transform: translateZ(0);
        }

        .skill-tag:hover {
            transform: translateY(-2px) scale(1.1);
            box-shadow: 0 4px 15px rgba(88, 166, 255, 0.3);
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

        .nav-item:hover {
            background: linear-gradient(135deg, var(--accent-1), var(--accent-hover));
            color: white;
            transform: translateX(5px);
            box-shadow: 0 4px 15px rgba(88, 166, 255, 0.3);
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
            max-width: 680px;
            margin: 0 auto;
        }

        /* Candidate Cards */
        .candidate-card {
            background-color: var(--bg-secondary);
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
            border: 1px solid var(--border);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s, box-shadow 0.2s;
            will-change: transform, box-shadow;
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

        .candidate-content {
            margin-bottom: 20px;
        }

        .candidate-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 12px;
            color: var(--accent-1);
        }

        .candidate-description {
            font-size: 15px;
            margin-bottom: 16px;
            line-height: 1.6;
        }

        .candidate-skills {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }

        .skill-tag {
            background-color: var(--bg-tertiary);
            padding: 8px 16px;
            border-radius: 16px;
            font-size: 14px;
            border: 1px solid var(--border);
            transition: all 0.2s;
        }

        .skill-tag:hover {
            background-color: var(--accent-1);
            color: white;
            border-color: var(--accent-1);
        }

        /* Enhanced candidate post styles */
        .candidate-position {
            color: var(--text-secondary);
            font-size: 14px;
            margin-bottom: 8px;
        }

        .post-meta {
            display: flex;
            flex-direction: column;
            gap: 4px;
            margin-top: 8px;
        }

        .post-date {
            font-size: 12px;
            color: var(--text-secondary);
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .post-info-item {
            font-size: 12px;
            color: var(--text-secondary);
            display: flex;
            align-items: center;
            gap: 4px;
            margin-bottom: 4px;
        }

        .post-info-item i {
            width: 12px;
            color: var(--accent-2);
        }

        .skills-section,
        .soft-skills-section,
        .value-section,
        .contact-section {
            margin-bottom: 16px;
        }

        .skills-section h4,
        .soft-skills-section h4,
        .value-section h4,
        .contact-section h4 {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--text-primary);
        }

        .soft-skills-container {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .soft-skill-tag {
            background: linear-gradient(135deg, var(--accent-2), #e67e22);
            color: white;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 500;
            border: none;
        }

        .value-content,
        .contact-content {
            font-size: 13px;
            line-height: 1.5;
            color: var(--text-secondary);
            background: var(--bg-tertiary);
            padding: 10px;
            border-radius: 6px;
            border-left: 3px solid var(--accent-2);
            margin: 0;
        }

        .candidate-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .contact-btn {
            background-color: var(--accent-1);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            cursor: pointer;
            font-weight: 600;
            font-size: 15px;
            transition: transform 0.2s, background-color 0.2s, box-shadow 0.2s;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .contact-btn:hover {
            background-color: var(--accent-hover);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .view-profile-btn {
            background-color: transparent;
            color: var(--accent-1);
            border: 1px solid var(--accent-1);
            border-radius: 8px;
            padding: 10px 20px;
            cursor: pointer;
            font-weight: 600;
            font-size: 15px;
            transition: background-color 0.2s, color 0.2s, transform 0.2s;
        }

        .view-profile-btn:hover {
            background-color: var(--accent-1);
            color: white;
            transform: translateY(-2px);
        }

        .report-btn {
            background-color: transparent;
            color: var(--warning);
            border: 1px solid var(--warning);
            border-radius: 8px;
            padding: 10px 20px;
            cursor: pointer;
            font-weight: 600;
            font-size: 15px;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .report-btn:hover {
            background-color: var(--warning);
            color: white;
            transform: translateY(-2px);
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

        .sidebar-section {
            background-color: var(--bg-secondary);
            border-radius: 12px;
            padding: 18px;
            margin-bottom: 24px;
            border: 1px solid var(--border);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .section-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 18px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--border);
            color: var(--text-primary);
        }

        .trending-skill {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid var(--border);
        }

        .trending-skill:last-child {
            border-bottom: none;
        }

        .skill-name {
            font-weight: 500;
            font-size: 15px;
        }

        .skill-demand {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .demand-bar {
            width: 90px;
            height: 8px;
            background-color: var(--bg-tertiary);
            border-radius: 4px;
            overflow: hidden;
        }

        .demand-fill {
            height: 100%;
            background-color: var(--accent-1);
        }

        .demand-level {
            font-size: 13px;
            font-weight: 500;
        }

        .high-demand {
            color: var(--success);
        }

        .medium-demand {
            color: var(--accent-2);
        }

        .low-demand {
            color: var(--danger);
        }


        /* Logout Button */
        .logout-container {
            margin-top: 20px;
            padding: 0 10px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        /* Professional Theme Toggle Button */
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

        /* Theme transition effects */
        .theme-toggle-btn[data-theme="dark"] i {
            color: #ffd700;
            text-shadow: 0 0 10px rgba(255, 215, 0, 0.3);
        }

        .theme-toggle-btn[data-theme="light"] i {
            color: #ffa500;
            text-shadow: 0 0 10px rgba(255, 165, 0, 0.3);
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
            background-color: var(--danger-hover);
            transform: translateY(-1px);
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
            color: var(--accent-2);
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
            border-color: var(--accent-2);
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
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
            background: var(--accent-2);
            color: white;
        }

        .btn-primary:hover {
            background: #e67e22;
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

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(100%);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }


        /* Responsive popup */
        @media (max-width: 768px) {
            .popup-content {
                width: 95%;
                padding: 20px;
                margin: 10px;
            }
            
            .popup-title {
                font-size: 20px;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .job-header {
                flex-direction: column;
                gap: 10px;
            }
            
            .job-actions {
                justify-content: stretch;
            }
            
            .job-actions .btn {
                flex: 1;
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
                <button id="editCompanyProfileBtn" style="background: var(--accent-2); color: white; border: none; border-radius: 6px; padding: 8px 12px; font-size: 12px; cursor: pointer; margin-top: 10px; width: 100%; transition: background 0.2s;" onmouseover="this.style.background='#e67e22'" onmouseout="this.style.background='var(--accent-2)'">
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
                <div class="nav-item active">
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
                <div class="nav-item" onclick="window.location.href='AIMatching.php'">
                    <i class="fas fa-robot"></i>
                    <span>AI Matching</span>
                </div>
            </div>

            <!-- Logout -->
            <div class="logout-container">
                <button id="themeToggleBtn" class="theme-toggle-btn" title="Switch to Light Mode">
                    <i class="fas fa-moon-stars" id="themeIcon"></i>
                    <span id="themeText">Light Mode</span>
                </button>
                <button id="logoutBtn" class="logout-btn"><i class="fas fa-sign-out-alt" style="margin-right:8px;"></i>Logout</button>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            
            <!-- Search and Filter Section -->
            <div class="search-filter-section" style="background: var(--bg-secondary); padding: 20px; border-radius: 12px; margin-bottom: 20px; border: 1px solid var(--border);">
                <div style="display: flex; gap: 15px; align-items: center; margin-bottom: 15px;">
                    <div class="search-box" style="flex: 1; position: relative;">
                        <i class="fas fa-search" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--text-secondary);"></i>
                        <input type="text" id="candidateSearchInput" placeholder="Search by position, skills, or candidate name..." style="width: 100%; padding: 12px 15px 12px 45px; border: 1px solid var(--border); border-radius: 8px; background: var(--bg-primary); color: var(--text-primary); font-size: 14px;">
                    </div>
                    <button id="advancedFilterBtn" style="background: var(--accent-2); color: white; border: none; border-radius: 8px; padding: 12px 16px; cursor: pointer; font-weight: 600; display: flex; align-items: center; gap: 8px;">
                        <i class="fas fa-filter"></i>
                        <span>Advanced Filter</span>
                    </button>
                    <button id="clearFiltersBtn" style="background: var(--text-secondary); color: white; border: none; border-radius: 8px; padding: 12px 16px; cursor: pointer; font-weight: 600; display: flex; align-items: center; gap: 8px;">
                        <i class="fas fa-times"></i>
                        <span>Clear</span>
                    </button>
                </div>
                
                <!-- Advanced Filter Panel -->
                <div id="advancedFilterPanel" style="display: none; border-top: 1px solid var(--border); padding-top: 15px;">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                        <div>
                            <label style="display: block; margin-bottom: 5px; color: var(--text-primary); font-weight: 600;">Skills:</label>
                            <input type="text" id="skillsFilter" placeholder="e.g., React, Python, SQL" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border); border-radius: 6px; background: var(--bg-primary); color: var(--text-primary);">
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 5px; color: var(--text-primary); font-weight: 600;">Location:</label>
                            <input type="text" id="locationFilter" placeholder="e.g., Remote, New York" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border); border-radius: 6px; background: var(--bg-primary); color: var(--text-primary);">
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 5px; color: var(--text-primary); font-weight: 600;">Experience Level:</label>
                            <select id="experienceFilter" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border); border-radius: 6px; background: var(--bg-primary); color: var(--text-primary);">
                                <option value="">All Levels</option>
                                <option value="entry">Entry Level</option>
                                <option value="mid">Mid Level</option>
                                <option value="senior">Senior Level</option>
                            </select>
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 5px; color: var(--text-primary); font-weight: 600;">Education:</label>
                            <select id="educationFilter" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border); border-radius: 6px; background: var(--bg-primary); color: var(--text-primary);">
                                <option value="">All Education</option>
                                <option value="high-school">High School</option>
                                <option value="associate">Associate</option>
                                <option value="bachelor">Bachelor's</option>
                                <option value="master">Master's</option>
                                <option value="phd">PhD</option>
                            </select>
                        </div>
                    </div>
                    <div style="margin-top: 15px; text-align: right;">
                        <button id="applyFiltersBtn" style="background: var(--accent-1); color: white; border: none; border-radius: 6px; padding: 10px 20px; cursor: pointer; font-weight: 600;">
                            Apply Filters
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Loading indicator -->
            <div id="loadingIndicator" style="text-align: center; padding: 40px; color: var(--text-secondary);">
                <i class="fas fa-spinner fa-spin" style="font-size: 24px; margin-right: 10px;"></i>
                Loading candidate posts...
            </div>
            
            <!-- Candidate posts container -->
            <div id="candidatePostsContainer">
                <!-- Candidate posts will be loaded here -->
            </div>
            
            <!-- No posts message -->
            <div id="noPostsMessage" style="display: none; text-align: center; padding: 40px; color: var(--text-secondary);">
                <i class="fas fa-users" style="font-size: 48px; margin-bottom: 16px; opacity: 0.5;"></i>
                <h3>No candidate posts available</h3>
                <p>There are currently no active candidate job-seeking posts.</p>
            </div>
        </div>

        <!-- Company Profile Edit Popup -->
        <div id="companyProfileEditPopup" class="popup-overlay" style="display: none;">
            <div class="popup-content">
                <div class="popup-header">
                    <div class="popup-title">
                        <i class="fas fa-building"></i>
                        Edit Company Profile
                    </div>
                    <button class="popup-close" onclick="closeCompanyProfileEditPopup()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <form id="companyProfileEditForm" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="companyLogoFile">Company Logo</label>
                        <input type="file" id="companyLogoFile" name="logo" accept="image/*">
                        <div id="currentCompanyLogo" style="margin-top: 10px; display: none;">
                            <img id="companyLogoPreview" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 2px solid var(--border);" />
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="companyName">Company Name *</label>
                        <input type="text" id="companyName" name="companyName" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="industry">Industry *</label>
                        <input type="text" id="industry" name="industry" required placeholder="e.g., Technology, Healthcare, Finance">
                    </div>
                    
                    <div class="form-group">
                        <label for="companySize">Company Size *</label>
                        <select id="companySize" name="companySize" required>
                            <option value="">Select Company Size</option>
                            <option value="1-10">1-10 employees</option>
                            <option value="11-50">11-50 employees</option>
                            <option value="51-200">51-200 employees</option>
                            <option value="201-500">201-500 employees</option>
                            <option value="501-1000">501-1000 employees</option>
                            <option value="1000+">1000+ employees</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="phoneNumber">Phone Number</label>
                        <input type="tel" id="phoneNumber" name="phoneNumber" placeholder="+1 (555) 123-4567">
                    </div>
                    
                    <div class="form-group">
                        <label for="website">Website</label>
                        <input type="url" id="website" name="website" placeholder="https://www.yourcompany.com">
                    </div>
                    
                    <div class="form-group">
                        <label for="companyDescription">Company Description *</label>
                        <textarea id="companyDescription" name="companyDescription" required placeholder="Describe your company, its mission, and what makes it unique"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea id="address" name="address" placeholder="Street address, building number"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="city">City</label>
                        <input type="text" id="city" name="city" placeholder="City">
                    </div>
                    
                    <div class="form-group">
                        <label for="state">State/Province</label>
                        <input type="text" id="state" name="state" placeholder="State or Province">
                    </div>
                    
                    <div class="form-group">
                        <label for="country">Country</label>
                        <input type="text" id="country" name="country" placeholder="Country">
                    </div>
                    
                    <div class="form-group">
                        <label for="postalCode">Postal Code</label>
                        <input type="text" id="postalCode" name="postalCode" placeholder="ZIP/Postal Code">
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeCompanyProfileEditPopup()">
                            <i class="fas fa-times"></i>
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-primary" id="submitCompanyProfileUpdate">
                            <i class="fas fa-save"></i>
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>


        <!-- Right Sidebar -->
        <div class="right-sidebar">
            <!-- Messaging System -->
            <div class="sidebar-section">
                <div class="section-title">Messages</div>
                <div id="messagingSidebar">
                    <?php include 'messaging_ui.php'; ?>
                </div>
            </div>

        </div>

        <!-- Invite for Exam Popup -->
        <div id="inviteExamPopup" class="popup-overlay" style="display: none;">
            <div class="popup-content">
                <div class="popup-header">
                    <div class="popup-title">
                        <i class="fas fa-pencil-alt"></i>
                        Invite Candidate for Exam
                    </div>
                    <button class="popup-close" onclick="closeInviteExamPopup()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <div class="invite-exam-content">
                    <div class="candidate-info-section" style="background: var(--bg-tertiary); padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid var(--border);">
                        <h4 style="margin: 0 0 10px 0; color: var(--text-primary);">Selected Candidate</h4>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div id="selectedCandidateAvatar" style="width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 16px; background: linear-gradient(135deg, var(--accent-1), var(--accent-hover));"></div>
                            <div>
                                <div id="selectedCandidateName" style="font-weight: 600; color: var(--text-primary);"></div>
                                <div id="selectedCandidatePosition" style="font-size: 12px; color: var(--text-secondary);"></div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="jobPostSelect">Select Job Post *</label>
                        <select id="jobPostSelect" required>
                            <option value="">Choose a job post...</option>
                        </select>
                    </div>

                    <div id="applicationStatus" style="display: none; margin-bottom: 20px;">
                        <div id="alreadyAppliedMessage" style="background: var(--warning); color: white; padding: 12px; border-radius: 8px; font-weight: 500; display: flex; align-items: center; gap: 8px;">
                            <i class="fas fa-info-circle"></i>
                            <span>This candidate has already applied for this position.</span>
                        </div>
                    </div>

                    <div id="examStatus" style="display: none; margin-bottom: 20px;">
                        <div id="examStatusMessage" style="background: var(--info); color: white; padding: 12px; border-radius: 8px; font-weight: 500; display: flex; align-items: center; gap: 8px;">
                            <i class="fas fa-info-circle"></i>
                            <span>Exam will be automatically assigned when you send the invitation.</span>
                        </div>
                    </div>


                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeInviteExamPopup()">
                            <i class="fas fa-times"></i>
                            Cancel
                        </button>
                        <button type="button" class="btn btn-primary" id="sendExamInviteBtn" disabled>
                            <i class="fas fa-paper-plane"></i>
                            Send Exam Invitation
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Report Candidate Post Popup -->
        <div id="reportCandidatePopup" class="popup-overlay" style="display: none;">
            <div class="popup-content" style="max-width: 600px;">
                <div class="popup-header">
                    <div class="popup-title">
                        <i class="fas fa-flag"></i>
                        Report Candidate Post
                    </div>
                    <button class="popup-close" onclick="closeReportCandidatePopup()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <form id="reportCandidateForm">
                    <input type="hidden" id="reportCandidateId" name="candidateId">
                    
                    <div class="form-group">
                        <label for="reportCandidateName">Candidate Name</label>
                        <input type="text" id="reportCandidateName" name="candidateName" readonly style="background: var(--bg-tertiary); color: var(--text-secondary);">
                    </div>
                    
                    <div class="form-group">
                        <label for="reportJobTitle">Job Title</label>
                        <input type="text" id="reportJobTitle" name="jobTitle" readonly style="background: var(--bg-tertiary); color: var(--text-secondary);">
                    </div>
                    
                    <div class="form-group">
                        <label for="reportReason">Reason for Report *</label>
                        <select id="reportReason" name="reason" required>
                            <option value="">Select a reason</option>
                            <option value="inappropriate_content">Inappropriate Content</option>
                            <option value="misleading_information">Misleading Information</option>
                            <option value="spam">Spam</option>
                            <option value="fake_profile">Fake Profile</option>
                            <option value="discriminatory">Discriminatory Language</option>
                            <option value="scam">Potential Scam</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="reportDescription">Description *</label>
                        <textarea id="reportDescription" name="description" placeholder="Please provide details about why you're reporting this candidate post..." required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="reportContact">Your Contact (Optional)</label>
                        <input type="text" id="reportContact" name="contact" placeholder="Email or phone number for follow-up">
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeReportCandidatePopup()">
                            <i class="fas fa-times"></i>
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-danger" id="submitCandidateReport">
                            <i class="fas fa-flag"></i>
                            Submit Report
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include 'message_button_helper.php'; ?>
    <?php include 'message_popup.php'; ?>
    
    <script>
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
        const logoutBtn = document.getElementById('logoutBtn');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', function() {
                window.location.href = 'Login&Signup.php';
            });
        }



        // Company Profile editing functionality
        let currentCompanyId = <?php echo json_encode($sessionCompanyId); ?>;
        window.currentUserId = currentCompanyId;
        window.currentUserType = 'company';

        // Initialize company dashboard
        function initializeCompanyDashboard() {
            console.log('Initializing company dashboard...');
            console.log('Company ID from session:', currentCompanyId);
            
            setupCompanyProfileEditing();
            loadCompanyProfile();
            // Initialize messaging system
            messagingSystem.initialize(currentCompanyId, 'company');
            console.log('Company dashboard initialization complete');
        }

        // Setup company profile editing
        function setupCompanyProfileEditing() {
            console.log('Setting up company profile editing...');
            
            const editProfileBtn = document.getElementById('editCompanyProfileBtn');
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
                animation: slideIn 0.3s ease-out;
            `;
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 3000);
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
                animation: slideIn 0.3s ease-out;
            `;
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 5000);
        }


        // Load candidate posts for company dashboard
        function loadCandidatePosts() {
            console.log('Loading candidate posts for company dashboard...');
            
            // Build query parameters
            const params = new URLSearchParams();
            params.append('action', 'get_all_posts');
            
            if (currentSearchTerm) {
                params.append('search', currentSearchTerm);
            }
            
            if (currentFilters.skills) {
                params.append('skills', currentFilters.skills);
            }
            
            if (currentFilters.location) {
                params.append('location', currentFilters.location);
            }
            
            if (currentFilters.experience) {
                params.append('experience', currentFilters.experience);
            }
            
            if (currentFilters.education) {
                params.append('education', currentFilters.education);
            }
            
            fetch(`job_seeking_handler.php?${params.toString()}`)
                .then(response => response.json())
                .then(data => {
                    console.log('Candidate posts loaded:', data);
                    hideLoadingIndicator();
                    
                    if (data.success && data.posts && data.posts.length > 0) {
                        displayCandidatePosts(data.posts);
                    } else {
                        showNoPostsMessage();
                    }
                })
                .catch(error => {
                    console.error('Error loading candidate posts:', error);
                    hideLoadingIndicator();
                    showErrorMessage('Failed to load candidate posts');
                });
        }

        // Display candidate posts
        function displayCandidatePosts(posts) {
            const container = document.getElementById('candidatePostsContainer');
            container.innerHTML = '';
            
            posts.forEach(post => {
                const postElement = createCandidatePostElement(post);
                container.appendChild(postElement);
            });
        }

        // Create candidate post element
        function createCandidatePostElement(post) {
            const postDiv = document.createElement('div');
            postDiv.className = 'candidate-card';
            
            // Get initials from name
            const initials = post.FullName.split(' ').map(name => name[0]).join('').toUpperCase();
            
            // Create avatar HTML - use profile picture if available, otherwise use initials
            let avatarHtml = '';
            if (post.ProfilePicture) {
                avatarHtml = `<div class="candidate-avatar" style="background-image: url('${post.ProfilePicture}'); background-size: cover; background-position: center; color: transparent;">${initials}</div>`;
            } else {
                avatarHtml = `<div class="candidate-avatar">${initials}</div>`;
            }
            
            // Split skills into array for display
            const skillsArray = post.KeySkills ? post.KeySkills.split(',').map(skill => skill.trim()) : [];
            const skillsHtml = skillsArray.map(skill => `<div class="skill-tag">${skill}</div>`).join('');
            
            // Format date
            const postDate = new Date(post.CreatedAt).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
            
            // Create location display
            const locationHtml = post.Location ? `<div class="post-info-item"><i class="fas fa-map-marker-alt"></i> ${post.Location}</div>` : '';
            
            // Create experience display
            const experienceHtml = post.Experience ? `<div class="post-info-item"><i class="fas fa-briefcase"></i> ${post.Experience}</div>` : '';
            
            // Create education display
            const educationHtml = post.Education ? `<div class="post-info-item"><i class="fas fa-graduation-cap"></i> ${post.Education}</div>` : '';
            
            // Create soft skills display
            const softSkillsArray = post.SoftSkills ? post.SoftSkills.split(',').map(skill => skill.trim()) : [];
            const softSkillsHtml = softSkillsArray.length > 0 ? 
                `<div class="soft-skills-section">
                    <h4><i class="fas fa-heart" style="color: var(--accent-2);"></i> Soft Skills</h4>
                    <div class="soft-skills-container">
                        ${softSkillsArray.map(skill => `<span class="soft-skill-tag">${skill}</span>`).join('')}
                    </div>
                </div>` : '';
            
            // Create value to employer section
            const valueHtml = post.ValueToEmployer ? 
                `<div class="value-section">
                    <h4><i class="fas fa-star" style="color: var(--accent-2);"></i> Value to Employer</h4>
                    <p class="value-content">${post.ValueToEmployer}</p>
                </div>` : '';
            
            // Create contact info section
            const contactHtml = post.ContactInfo ? 
                `<div class="contact-section">
                    <h4><i class="fas fa-phone" style="color: var(--accent-2);"></i> Contact Information</h4>
                    <p class="contact-content">${post.ContactInfo}</p>
                </div>` : '';
            
            postDiv.innerHTML = `
                <div class="candidate-header">
                    ${avatarHtml}
                    <div class="candidate-info">
                        <h3>${post.FullName}</h3>
                        <p class="candidate-position">${post.JobTitle}</p>
                        <div class="post-meta">
                            <span class="post-date"><i class="fas fa-calendar"></i> Posted ${postDate}</span>
                            ${locationHtml}
                        </div>
                    </div>
                </div>
                
                <div class="candidate-content">
                    <div class="candidate-title">${post.JobTitle}</div>
                    
                    <div class="candidate-description">
                        <h4><i class="fas fa-bullseye" style="color: var(--accent-2);"></i> Career Goal</h4>
                        <p>${post.CareerGoal || 'No career goal provided.'}</p>
                    </div>
                    
                    ${experienceHtml}
                    ${educationHtml}
                    
                    ${skillsHtml ? `
                        <div class="skills-section">
                            <h4><i class="fas fa-code" style="color: var(--accent-2);"></i> Technical Skills</h4>
                            <div class="candidate-skills">${skillsHtml}</div>
                        </div>
                    ` : ''}
                    
                    ${softSkillsHtml}
                    ${valueHtml}
                    ${contactHtml}
                </div>
                
                <div class="candidate-actions">
                        <button class="contact-btn" onclick="openMessageDialog(${post.CandidateID}, ${post.CandidateID}, 'candidate', '${post.FullName}', '${post.ProfilePicture || ''}')" title="Message this candidate">
                            <i class="fas fa-comment" style="margin-right: 6px;"></i>Message
                        </button>
                    <button class="contact-btn" onclick="inviteForExam(${post.CandidateID}, '${post.FullName}', '${post.JobTitle}')">
                        <i class="fas fa-pencil-alt" style="margin-right: 6px;"></i>Invite for Exam
                    </button>
                    <button class="report-btn" onclick="reportCandidatePost(${post.CandidateID}, '${post.FullName}', '${post.JobTitle}')" title="Report this candidate post">
                        <i class="fas fa-flag" style="margin-right: 6px;"></i>Report
                    </button>
                </div>
            `;
            
            return postDiv;
        }

        // Invite candidate for exam function
        function inviteForExam(candidateId, name, jobTitle) {
            // Store current candidate data
            window.currentCandidateId = candidateId;
            window.currentCandidateName = name;
            window.currentCandidateJobTitle = jobTitle;
            
            // Update candidate info in popup
            document.getElementById('selectedCandidateName').textContent = name;
            document.getElementById('selectedCandidatePosition').textContent = jobTitle;
            document.getElementById('selectedCandidateAvatar').textContent = name.charAt(0).toUpperCase();
            
            // Load job posts for this company
            loadJobPosts();
            
            // Show popup
            document.getElementById('inviteExamPopup').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        // Load job posts for the company
        function loadJobPosts() {
            const jobPostSelect = document.getElementById('jobPostSelect');
            jobPostSelect.innerHTML = '<option value="">Loading job posts...</option>';
            
            fetch('job_posting_handler.php?action=list_jobs')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.jobs) {
                        jobPostSelect.innerHTML = '<option value="">Choose a job post...</option>';
                        
                        data.jobs.forEach(job => {
                            const option = document.createElement('option');
                            option.value = job.JobID;
                            option.textContent = `${job.JobTitle} - ${job.Location} (${job.JobType})`;
                            option.dataset.jobTitle = job.JobTitle;
                            jobPostSelect.appendChild(option);
                        });
                        
                        if (data.jobs.length === 0) {
                            jobPostSelect.innerHTML = '<option value="">No job posts available</option>';
                        }
                    } else {
                        jobPostSelect.innerHTML = '<option value="">Error loading job posts</option>';
                    }
                })
                .catch(error => {
                    console.error('Error loading job posts:', error);
                    jobPostSelect.innerHTML = '<option value="">Error loading job posts</option>';
                });
        }

        // Check if exams are available for the company and show status
        function checkExamAvailability(jobPostId) {
            const examStatus = document.getElementById('examStatus');
            const sendBtn = document.getElementById('sendExamInviteBtn');
            
            fetch(`exam_assignment_handler.php?action=get_exams&job_post_id=${jobPostId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.exams && data.exams.length > 0) {
                        // Exams available - show info message and enable button
                        examStatus.style.display = 'block';
                        sendBtn.disabled = false;
                        sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Send Exam Invitation';
                    } else {
                        // No exams available - show warning and disable button
                        examStatus.style.display = 'block';
                        document.getElementById('examStatusMessage').innerHTML = '<i class="fas fa-exclamation-triangle"></i><span>No exams available. Please create an exam first.</span>';
                        document.getElementById('examStatusMessage').style.background = 'var(--warning)';
                        sendBtn.disabled = true;
                        sendBtn.innerHTML = '<i class="fas fa-ban"></i> No Exams Available';
                    }
                })
                .catch(error => {
                    console.error('Error checking exam availability:', error);
                    examStatus.style.display = 'block';
                    document.getElementById('examStatusMessage').innerHTML = '<i class="fas fa-exclamation-triangle"></i><span>Error checking exam availability.</span>';
                    document.getElementById('examStatusMessage').style.background = 'var(--danger)';
                    sendBtn.disabled = true;
                });
        }

        // Check if candidate has already applied for the selected job
        function checkApplicationStatus(candidateId, jobPostId) {
            fetch(`check_application_status.php?candidate_id=${candidateId}&job_post_id=${jobPostId}`)
                .then(response => response.json())
                .then(data => {
                    const statusDiv = document.getElementById('applicationStatus');
                    const sendBtn = document.getElementById('sendExamInviteBtn');
                    
                    if (data.success && data.hasApplied) {
                        statusDiv.style.display = 'block';
                        sendBtn.disabled = true;
                        sendBtn.innerHTML = '<i class="fas fa-info-circle"></i> Candidate Already Applied';
                    } else {
                        statusDiv.style.display = 'none';
                        sendBtn.disabled = false;
                        sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Send Exam Invitation';
                    }
                })
                .catch(error => {
                    console.error('Error checking application status:', error);
                    // On error, allow sending invitation
                    document.getElementById('applicationStatus').style.display = 'none';
                    document.getElementById('sendExamInviteBtn').disabled = false;
                });
        }

        // Close invite exam popup
        function closeInviteExamPopup() {
            document.getElementById('inviteExamPopup').style.display = 'none';
            document.body.style.overflow = 'auto';
            
            // Reset form
            document.getElementById('jobPostSelect').value = '';
            document.getElementById('examStatus').style.display = 'none';
            document.getElementById('applicationStatus').style.display = 'none';
            document.getElementById('sendExamInviteBtn').disabled = true;
            
            // Reset exam status message
            document.getElementById('examStatusMessage').innerHTML = '<i class="fas fa-info-circle"></i><span>Exam will be automatically assigned when you send the invitation.</span>';
            document.getElementById('examStatusMessage').style.background = 'var(--info)';
        }

        // Send exam invitation with auto-assignment
        function sendExamInvitation() {
            const candidateId = window.currentCandidateId;
            const jobPostId = document.getElementById('jobPostSelect').value;
            
            if (!candidateId || !jobPostId) {
                showErrorMessage('Please select a job post');
                return;
            }
            
            const sendBtn = document.getElementById('sendExamInviteBtn');
            const originalContent = sendBtn.innerHTML;
            
            sendBtn.disabled = true;
            sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Assigning Exam...';
            
            const formData = new FormData();
            formData.append('action', 'auto_assign_exam');
            formData.append('candidate_id', candidateId);
            formData.append('job_post_id', jobPostId);
            
            fetch('exam_assignment_handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccessMessage('Exam assigned successfully! The candidate will receive the exam invitation.');
                    closeInviteExamPopup();
                } else {
                    showErrorMessage(data.message || 'Failed to assign exam');
                }
            })
            .catch(error => {
                console.error('Error assigning exam:', error);
                showErrorMessage('Network error. Please try again.');
            })
            .finally(() => {
                sendBtn.disabled = false;
                sendBtn.innerHTML = originalContent;
            });
        }

        // Hide loading indicator
        function hideLoadingIndicator() {
            document.getElementById('loadingIndicator').style.display = 'none';
        }

        // Show no posts message
        function showNoPostsMessage() {
            document.getElementById('noPostsMessage').style.display = 'block';
        }

        // Search and filter functionality
        let currentSearchTerm = '';
        let currentFilters = {};

        // Setup search and filter event listeners
        function setupSearchAndFilters() {
            const searchInput = document.getElementById('candidateSearchInput');
            const advancedFilterBtn = document.getElementById('advancedFilterBtn');
            const clearFiltersBtn = document.getElementById('clearFiltersBtn');
            const applyFiltersBtn = document.getElementById('applyFiltersBtn');
            const advancedFilterPanel = document.getElementById('advancedFilterPanel');

            // Search input event
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    currentSearchTerm = this.value.trim();
                    debounceSearch();
                });
            }

            // Advanced filter toggle
            if (advancedFilterBtn && advancedFilterPanel) {
                advancedFilterBtn.addEventListener('click', function() {
                    const isVisible = advancedFilterPanel.style.display !== 'none';
                    advancedFilterPanel.style.display = isVisible ? 'none' : 'block';
                    this.innerHTML = isVisible ? 
                        '<i class="fas fa-filter"></i><span>Advanced Filter</span>' : 
                        '<i class="fas fa-filter"></i><span>Hide Filter</span>';
                });
            }

            // Clear filters
            if (clearFiltersBtn) {
                clearFiltersBtn.addEventListener('click', function() {
                    clearAllFilters();
                });
            }

            // Apply filters
            if (applyFiltersBtn) {
                applyFiltersBtn.addEventListener('click', function() {
                    applyAdvancedFilters();
                });
            }
        }

        // Debounced search function
        let searchTimeout;
        function debounceSearch() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                loadCandidatePosts();
            }, 300);
        }

        // Clear all filters
        function clearAllFilters() {
            currentSearchTerm = '';
            currentFilters = {};
            
            // Clear input fields
            document.getElementById('candidateSearchInput').value = '';
            document.getElementById('skillsFilter').value = '';
            document.getElementById('locationFilter').value = '';
            document.getElementById('experienceFilter').value = '';
            document.getElementById('educationFilter').value = '';
            
            // Hide advanced filter panel
            const advancedFilterPanel = document.getElementById('advancedFilterPanel');
            const advancedFilterBtn = document.getElementById('advancedFilterBtn');
            if (advancedFilterPanel && advancedFilterBtn) {
                advancedFilterPanel.style.display = 'none';
                advancedFilterBtn.innerHTML = '<i class="fas fa-filter"></i><span>Advanced Filter</span>';
            }
            
            // Reload posts
            loadCandidatePosts();
        }

        // Apply advanced filters
        function applyAdvancedFilters() {
            currentFilters = {
                skills: document.getElementById('skillsFilter').value.trim(),
                location: document.getElementById('locationFilter').value.trim(),
                experience: document.getElementById('experienceFilter').value,
                education: document.getElementById('educationFilter').value
            };
            
            loadCandidatePosts();
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
            if (themeToggleBtn) {
                themeToggleBtn.setAttribute('data-theme', theme);
            }
            
            if (theme === 'dark') {
                themeIcon.className = 'fas fa-moon-stars';
                themeText.textContent = 'Light Mode';
                if (themeToggleBtn) {
                    themeToggleBtn.title = 'Switch to Light Mode';
                }
            } else {
                themeIcon.className = 'fas fa-moon-stars';
                themeText.textContent = 'Dark Mode';
                if (themeToggleBtn) {
                    themeToggleBtn.title = 'Switch to Dark Mode';
                }
            }
        }

        function setupThemeToggle() {
            const themeToggleBtn = document.getElementById('themeToggleBtn');
            if (themeToggleBtn) {
                themeToggleBtn.addEventListener('click', toggleTheme);
            }
        }


        // Setup invite exam popup event listeners
        function setupInviteExamPopup() {
            // Job post selection change
            document.getElementById('jobPostSelect').addEventListener('change', function() {
                const jobPostId = this.value;
                const candidateId = window.currentCandidateId;
                
                if (jobPostId) {
                    // Check exam availability for this company
                    checkExamAvailability(jobPostId);
                    
                    // Check application status
                    if (candidateId) {
                        checkApplicationStatus(candidateId, jobPostId);
                    }
                } else {
                    // Reset status
                    document.getElementById('examStatus').style.display = 'none';
                    document.getElementById('applicationStatus').style.display = 'none';
                    document.getElementById('sendExamInviteBtn').disabled = true;
                }
            });
            
            
            // Send exam invitation button
            document.getElementById('sendExamInviteBtn').addEventListener('click', sendExamInvitation);
            
            // Close popup when clicking outside
            document.getElementById('inviteExamPopup').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeInviteExamPopup();
                }
            });
        }

        // Report candidate post functionality
        function reportCandidatePost(candidateId, candidateName, jobTitle) {
            console.log('Opening report popup for candidate:', candidateId);
            
            document.getElementById('reportCandidateId').value = candidateId;
            document.getElementById('reportCandidateName').value = candidateName;
            document.getElementById('reportJobTitle').value = jobTitle;
            
            document.getElementById('reportCandidateForm').reset();
            document.getElementById('reportCandidateId').value = candidateId;
            document.getElementById('reportCandidateName').value = candidateName;
            document.getElementById('reportJobTitle').value = jobTitle;
            
            const popup = document.getElementById('reportCandidatePopup');
            popup.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeReportCandidatePopup() {
            const popup = document.getElementById('reportCandidatePopup');
            const form = document.getElementById('reportCandidateForm');
            
            popup.style.display = 'none';
            document.body.style.overflow = 'auto';
            form.reset();
        }

        function setupReportCandidate() {
            console.log('Setting up report candidate functionality...');
            
            const reportForm = document.getElementById('reportCandidateForm');
            const reportPopup = document.getElementById('reportCandidatePopup');
            
            if (!reportForm || !reportPopup) {
                console.error('Report candidate elements not found');
                return;
            }
            
            reportForm.addEventListener('submit', function(e) {
                e.preventDefault();
                submitCandidateReport();
            });
            
            reportPopup.addEventListener('click', function(e) {
                if (e.target === reportPopup) {
                    closeReportCandidatePopup();
                }
            });
            
            console.log('Report candidate functionality setup complete');
        }

        function submitCandidateReport() {
            console.log('Submitting candidate report...');
            
            const form = document.getElementById('reportCandidateForm');
            const submitBtn = document.getElementById('submitCandidateReport');
            const formData = new FormData(form);
            
            if (submitBtn.disabled) {
                console.log('Report already being submitted, ignoring duplicate request');
                return;
            }
            
            const reportData = {
                action: 'report_candidate',
                candidateId: formData.get('candidateId'),
                candidateName: formData.get('candidateName'),
                jobTitle: formData.get('jobTitle'),
                reason: formData.get('reason'),
                description: formData.get('description'),
                contact: formData.get('contact'),
                companyId: currentCompanyId,
                requestId: Date.now() + '_' + Math.random().toString(36).substr(2, 9)
            };
            
            console.log('Report data:', reportData);
            
            if (!reportData.candidateId) {
                showErrorMessage('Candidate ID is missing. Please try again.');
                return;
            }
            
            if (!reportData.reason) {
                showErrorMessage('Please select a reason for reporting.');
                return;
            }
            
            if (!reportData.description || reportData.description.trim() === '') {
                showErrorMessage('Please provide a description of the issue.');
                return;
            }
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
            
            fetch('report_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(reportData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeReportCandidatePopup();
                    showSuccessMessage('Report submitted successfully! Thank you for helping keep our platform safe.');
                } else {
                    showErrorMessage(data.message || 'Failed to submit report');
                }
            })
            .catch(error => {
                console.error('Error submitting report:', error);
                showErrorMessage('Network error. Please try again.');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-flag"></i> Submit Report';
            });
        }

        // Initialize company dashboard when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            initializeTheme();
            initializeCompanyDashboard();
            setupSearchAndFilters();
            setupThemeToggle();
            setupInviteExamPopup();
            setupReportCandidate();
            loadCandidatePosts();
        });
    </script>
</body>
</html>
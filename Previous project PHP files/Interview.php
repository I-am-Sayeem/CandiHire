<?php
// Interview.php - Interview Management for Companies
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
    <title>CandiHire - Interview Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
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
            --warning: #d29922;
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
            --warning: #d29922;
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

        .nav-item:hover {
            background-color: var(--bg-tertiary);
            transform: translateX(2px);
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
            margin-bottom: 8px;
            color: var(--text-primary);
        }

        .page-subtitle {
            color: var(--text-secondary);
            font-size: 16px;
        }

        /* Interview Controls */
        .interview-controls {
            background-color: var(--bg-secondary);
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
            border: 1px solid var(--border);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .controls-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .controls-title {
            font-size: 20px;
            font-weight: 600;
            color: var(--accent-1);
        }

        .interview-status {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
        }

        .status-ready {
            background-color: rgba(63, 185, 80, 0.2);
            color: var(--success);
            border: 1px solid rgba(63, 185, 80, 0.3);
        }

        .status-ready i {
            animation: blink 4s infinite ease-in-out;
            color: #00ff00;
            text-shadow: 0 0 12px rgba(0, 255, 0, 0.8);
        }

        @keyframes blink {
            0% {
                opacity: 1;
                transform: scale(1);
                text-shadow: 0 0 12px rgba(0, 255, 0, 0.8);
            }
            25% {
                opacity: 0.8;
                transform: scale(0.95);
                text-shadow: 0 0 8px rgba(0, 255, 0, 0.6);
            }
            50% {
                opacity: 0.4;
                transform: scale(0.85);
                text-shadow: 0 0 4px rgba(0, 255, 0, 0.3);
            }
            75% {
                opacity: 0.8;
                transform: scale(0.95);
                text-shadow: 0 0 8px rgba(0, 255, 0, 0.6);
            }
            100% {
                opacity: 1;
                transform: scale(1);
                text-shadow: 0 0 12px rgba(0, 255, 0, 0.8);
            }
        }

        .status-in-progress {
            background-color: rgba(210, 153, 34, 0.2);
            color: var(--warning);
            border: 1px solid rgba(210, 153, 34, 0.3);
        }

        .status-completed {
            background-color: rgba(88, 166, 255, 0.2);
            color: var(--accent-1);
            border: 1px solid rgba(88, 166, 255, 0.3);
        }

        .candidate-selector {
            margin-bottom: 20px;
        }

        .selector-label {
            font-size: 16px;
            font-weight: 500;
            margin-bottom: 12px;
            color: var(--text-primary);
        }

        .candidate-dropdown {
            width: 100%;
            background-color: var(--bg-primary);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 12px 16px;
            color: var(--text-primary);
            font-size: 15px;
            cursor: pointer;
            transition: border-color 0.2s;
        }

        .candidate-dropdown:focus {
            outline: none;
            border-color: var(--accent-1);
        }

        .candidate-dropdown option {
            background-color: var(--bg-primary);
            color: var(--text-primary);
        }

        /* Form Sections */
        .form-section {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 16px;
            font-weight: 500;
            margin-bottom: 8px;
            color: var(--text-primary);
        }

        .form-input, .form-select {
            width: 100%;
            background-color: var(--bg-primary);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 12px 16px;
            color: var(--text-primary);
            font-size: 15px;
            transition: border-color 0.2s;
        }

        .form-input:focus, .form-select:focus {
            outline: none;
            border-color: var(--accent-1);
        }

        .form-input::placeholder {
            color: var(--text-secondary);
        }

        .form-select option {
            background-color: var(--bg-primary);
            color: var(--text-primary);
        }

        /* Candidate List */
        .candidate-list {
            background-color: var(--bg-primary);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 16px;
            max-height: 200px;
            overflow-y: auto;
        }

        .candidate-list-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }

        .candidate-list-actions {
            display: flex;
            gap: 8px;
        }

        .btn-small {
            padding: 8px 12px;
            font-size: 13px;
            border-radius: 6px;
        }

        .btn-small i {
            font-size: 12px;
        }

        .candidate-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px;
            margin-bottom: 8px;
            background-color: var(--bg-secondary);
            border-radius: 6px;
            border: 1px solid var(--border);
            transition: all 0.2s;
        }

        .candidate-item:last-child {
            margin-bottom: 0;
        }

        .candidate-item:hover {
            background-color: var(--bg-tertiary);
            border-color: var(--accent-1);
        }

        .candidate-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .candidate-avatar-small {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background-color: var(--accent-1);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
            color: white;
        }

        .candidate-details h4 {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 2px;
        }

        .candidate-details p {
            color: var(--text-secondary);
            font-size: 12px;
        }

        .candidate-checkbox {
            width: 18px;
            height: 18px;
            accent-color: var(--accent-1);
        }

        /* Date Time Row */
        .datetime-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .datetime-group {
            display: flex;
            flex-direction: column;
        }

        .interview-actions {
            display: flex;
            gap: 16px;
            margin-top: 20px;
        }

        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            font-size: 15px;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background-color: var(--accent-1);
            color: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .btn-primary:hover {
            background-color: var(--accent-hover);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .btn-secondary {
            background-color: transparent;
            color: var(--accent-1);
            border: 1px solid var(--accent-1);
        }

        .btn-secondary:hover {
            background-color: var(--accent-1);
            color: white;
            transform: translateY(-2px);
        }

        .btn-success {
            background-color: var(--success);
            color: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .btn-success:hover {
            background-color: #2ea043;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .btn-danger {
            background-color: var(--danger);
            color: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .btn-danger:hover {
            background-color: #d03f39;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        /* Interview Interface */
        .interview-interface {
            background-color: var(--bg-secondary);
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
            border: 1px solid var(--border);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            display: none;
        }

        .interview-interface.active {
            display: block;
        }

        .interview-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--border);
        }

        .candidate-info {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .candidate-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background-color: var(--accent-1);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 22px;
            color: white;
        }

        .candidate-details h3 {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .candidate-details p {
            color: var(--text-secondary);
            font-size: 14px;
        }

        .interview-timer {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background-color: var(--bg-tertiary);
            border-radius: 20px;
            font-weight: 600;
            color: var(--accent-1);
        }

        .interview-content {
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 24px;
        }

        .interview-main {
            background-color: var(--bg-primary);
            border-radius: 8px;
            padding: 20px;
            border: 1px solid var(--border);
        }

        .question-section {
            margin-bottom: 24px;
        }

        .question-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }

        .question-number {
            background-color: var(--accent-1);
            color: white;
            padding: 6px 12px;
            border-radius: 16px;
            font-size: 14px;
            font-weight: 600;
        }

        .question-timer {
            color: var(--warning);
            font-size: 14px;
            font-weight: 500;
        }

        .question-text {
            font-size: 18px;
            font-weight: 500;
            margin-bottom: 16px;
            line-height: 1.6;
        }

        .answer-section {
            margin-bottom: 20px;
        }

        .answer-label {
            font-size: 16px;
            font-weight: 500;
            margin-bottom: 12px;
            color: var(--text-primary);
        }

        .answer-textarea {
            width: 100%;
            background-color: var(--bg-secondary);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 16px;
            color: var(--text-primary);
            font-size: 15px;
            resize: vertical;
            min-height: 120px;
            transition: border-color 0.2s;
        }

        .answer-textarea:focus {
            outline: none;
            border-color: var(--accent-1);
        }

        .rating-section {
            margin-bottom: 20px;
        }

        .rating-label {
            font-size: 16px;
            font-weight: 500;
            margin-bottom: 12px;
            color: var(--text-primary);
        }

        .rating-scale {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .rating-option {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            padding: 12px;
            border-radius: 8px;
            transition: background-color 0.2s;
        }

        .rating-option:hover {
            background-color: var(--bg-tertiary);
        }

        .rating-option.selected {
            background-color: rgba(88, 166, 255, 0.2);
            border: 1px solid var(--accent-1);
        }

        .rating-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 2px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            transition: all 0.2s;
        }

        .rating-option.selected .rating-circle {
            background-color: var(--accent-1);
            color: white;
            border-color: var(--accent-1);
        }

        .rating-text {
            font-size: 12px;
            color: var(--text-secondary);
            text-align: center;
        }

        .interview-sidebar {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .sidebar-section {
            background-color: var(--bg-primary);
            border-radius: 8px;
            padding: 16px;
            border: 1px solid var(--border);
        }

        .sidebar-title {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 12px;
            color: var(--text-primary);
        }

        .question-list {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .question-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 12px;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.2s;
            font-size: 14px;
        }

        .question-item:hover {
            background-color: var(--bg-tertiary);
        }

        .question-item.active {
            background-color: rgba(88, 166, 255, 0.2);
            color: var(--accent-1);
        }

        .question-item.completed {
            background-color: rgba(63, 185, 80, 0.2);
            color: var(--success);
        }

        .question-status {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: var(--border);
        }

        .question-item.active .question-status {
            background-color: var(--accent-1);
        }

        .question-item.completed .question-status {
            background-color: var(--success);
        }

        .notes-section textarea {
            width: 100%;
            background-color: var(--bg-secondary);
            border: 1px solid var(--border);
            border-radius: 6px;
            padding: 12px;
            color: var(--text-primary);
            font-size: 14px;
            resize: vertical;
            min-height: 100px;
        }

        .notes-section textarea:focus {
            outline: none;
            border-color: var(--accent-1);
        }

        /* Results Summary */
        .results-summary {
            background-color: var(--bg-secondary);
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
            border: 1px solid var(--border);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            display: none;
        }

        .results-summary.active {
            display: block;
        }

        .results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .results-title {
            font-size: 20px;
            font-weight: 600;
            color: var(--accent-1);
        }

        .overall-score {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            background-color: var(--bg-primary);
            border-radius: 8px;
            border: 1px solid var(--border);
        }

        .score-circle {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background-color: var(--success);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: 700;
            color: white;
        }

        .score-details h4 {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .score-details p {
            color: var(--text-secondary);
            font-size: 14px;
        }

        .results-breakdown {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-top: 20px;
        }

        .breakdown-item {
            background-color: var(--bg-primary);
            padding: 16px;
            border-radius: 8px;
            border: 1px solid var(--border);
        }

        .breakdown-title {
            font-size: 14px;
            font-weight: 500;
            color: var(--text-secondary);
            margin-bottom: 8px;
        }

        .breakdown-score {
            font-size: 24px;
            font-weight: 700;
            color: var(--accent-1);
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
        @media (max-width: 1200px) {
            .interview-content {
                grid-template-columns: 1fr;
            }
            .interview-sidebar {
                order: -1;
            }
        }

        @media (max-width: 768px) {
            .left-nav {
                display: none;
            }
            .main-content {
                padding: 10px;
            }
            .interview-actions {
                flex-direction: column;
            }
            .results-breakdown {
                grid-template-columns: 1fr;
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
                <div class="nav-item active">
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
            <div class="page-header">
                <h1 class="page-title">Interview Management</h1>
                <p class="page-subtitle">Conduct interviews with candidates who have completed MCQ tests</p>
            </div>


            <!-- Interview Setup -->
            <div class="interview-controls">
                <div class="controls-header">
                    <h2 class="controls-title">Interview Setup</h2>
                    <div class="interview-status status-ready">
                        <i class="fas fa-circle"></i>
                        <span>Ready to Schedule</span>
                    </div>
                </div>
                
                <!-- Interview Method Section -->
                <div class="form-section">
                    <label class="form-label">Interview Method:</label>
                    <select class="form-select" id="interviewMethod">
                        <option value="">Select interview method...</option>
                        <option value="virtual">Virtual (Zoom/Google Meet)</option>
                        <option value="onsite">Onsite (In-person)</option>
                    </select>
                </div>

                <!-- Meeting Link Section (only shown for virtual interviews) -->
                <div class="form-section" id="meetingLinkSection" style="display: none;">
                    <label class="form-label">Meeting Link (Zoom/Google Meet):</label>
                    <input type="url" class="form-input" id="meetingLink" placeholder="https://zoom.us/j/123456789 or https://meet.google.com/abc-defg-hij">
                </div>

                <!-- Location Section (only shown for onsite interviews) -->
                <div class="form-section" id="locationSection" style="display: none;">
                    <label class="form-label">Interview Location:</label>
                    <input type="text" class="form-input" id="interviewLocation" placeholder="e.g., Company Office, Building A, Room 201, 123 Main Street, City">
                </div>

                <!-- Position Selection -->
                <div class="form-section">
                    <label class="form-label">Select the job position that company is posted:</label>
                    <select class="form-select" id="positionSelect">
                        <option value="">Choose a position...</option>
                        <!-- Job positions will be populated dynamically from company's posted jobs -->
                    </select>
                </div>

                <!-- Candidate Email List -->
                <div class="form-section" id="candidateListSection" style="display: none;">
                    <div class="candidate-list-header">
                        <label class="form-label">Candidates for Selected Position:</label>
                        <div class="candidate-list-actions">
                            <button class="btn btn-secondary btn-small" id="selectAllBtn">
                                <i class="fas fa-check-square"></i>
                                Select All
                            </button>
                            <button class="btn btn-secondary btn-small" id="deselectAllBtn" style="display: none;">
                                <i class="fas fa-square"></i>
                                Deselect All
                            </button>
                        </div>
                    </div>
                    <div class="candidate-list" id="candidateList">
                        <!-- Candidate emails will be populated here -->
                    </div>
                </div>

                <!-- Date and Time Selection -->
                <div class="form-section" id="datetimeSection" style="display: none;">
                    <div class="datetime-row">
                        <div class="datetime-group">
                            <label class="form-label">Interview Date:</label>
                            <input type="date" class="form-input" id="interviewDate" min="">
                        </div>
                        <div class="datetime-group">
                            <label class="form-label">Interview Time:</label>
                            <input type="time" class="form-input" id="interviewTime">
                        </div>
                    </div>
                </div>

                <!-- Schedule Interview Button -->
                <div class="interview-actions" id="scheduleInterviewSection" style="display: none;">
                    <button class="btn btn-primary" id="scheduleInterviewBtn">
                        <i class="fas fa-calendar-check"></i>
                        Schedule Interview
                    </button>
                </div>
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
        // Interview scheduling state management
        let selectedPosition = null;
        let selectedCandidates = [];
        let interviewMethod = '';
        let meetingLink = '';
        let interviewLocation = '';
        let interviewDate = '';
        let interviewTime = '';

        // Sample candidate data organized by position
        const candidatesByPosition = {
            'frontend-developer': [
                { id: 'john-doe', name: 'John Doe', email: 'john.doe@email.com', avatar: 'JD', score: 92 },
                { id: 'alice-wilson', name: 'Alice Wilson', email: 'alice.wilson@email.com', avatar: 'AW', score: 88 },
                { id: 'bob-chen', name: 'Bob Chen', email: 'bob.chen@email.com', avatar: 'BC', score: 85 }
            ],
            'fullstack-developer': [
                { id: 'jane-smith', name: 'Jane Smith', email: 'jane.smith@email.com', avatar: 'JS', score: 87 },
                { id: 'mike-johnson', name: 'Mike Johnson', email: 'mike.johnson@email.com', avatar: 'MJ', score: 90 },
                { id: 'sarah-davis', name: 'Sarah Davis', email: 'sarah.davis@email.com', avatar: 'SD', score: 83 }
            ],
            'backend-developer': [
                { id: 'mike-wilson', name: 'Mike Wilson', email: 'mike.wilson@email.com', avatar: 'MW', score: 89 },
                { id: 'david-lee', name: 'David Lee', email: 'david.lee@email.com', avatar: 'DL', score: 86 },
                { id: 'emma-brown', name: 'Emma Brown', email: 'emma.brown@email.com', avatar: 'EB', score: 91 }
            ],
            'ui-ux-designer': [
                { id: 'sarah-johnson', name: 'Sarah Johnson', email: 'sarah.johnson@email.com', avatar: 'SJ', score: 85 },
                { id: 'lisa-garcia', name: 'Lisa Garcia', email: 'lisa.garcia@email.com', avatar: 'LG', score: 88 },
                { id: 'tom-martinez', name: 'Tom Martinez', email: 'tom.martinez@email.com', avatar: 'TM', score: 82 }
            ],
            'devops-engineer': [
                { id: 'david-brown', name: 'David Brown', email: 'david.brown@email.com', avatar: 'DB', score: 91 },
                { id: 'alex-taylor', name: 'Alex Taylor', email: 'alex.taylor@email.com', avatar: 'AT', score: 87 },
                { id: 'maria-anderson', name: 'Maria Anderson', email: 'maria.anderson@email.com', avatar: 'MA', score: 89 }
            ],
            'data-scientist': [
                { id: 'james-miller', name: 'James Miller', email: 'james.miller@email.com', avatar: 'JM', score: 88 },
                { id: 'anna-thomas', name: 'Anna Thomas', email: 'anna.thomas@email.com', avatar: 'AT', score: 90 },
                { id: 'kevin-jackson', name: 'Kevin Jackson', email: 'kevin.jackson@email.com', avatar: 'KJ', score: 85 }
            ],
            'mobile-developer': [
                { id: 'rachel-white', name: 'Rachel White', email: 'rachel.white@email.com', avatar: 'RW', score: 87 },
                { id: 'daniel-harris', name: 'Daniel Harris', email: 'daniel.harris@email.com', avatar: 'DH', score: 89 },
                { id: 'olivia-clark', name: 'Olivia Clark', email: 'olivia.clark@email.com', avatar: 'OC', score: 84 }
            ]
        };

        // Company Profile Management
        let currentCompanyId = <?php echo json_encode($sessionCompanyId); ?>;
        let companyJobPositions = [];

        // Load company's posted job positions
        function loadCompanyJobPositions() {
            console.log('Loading company job positions...');
            
            if (!currentCompanyId) {
                console.error('No company ID available');
                return;
            }
            
            // Use the existing endpoint with company filter
            fetch(`company_job_posts_handler.php?company=${encodeURIComponent('<?php echo htmlspecialchars($companyName); ?>')}&limit=100`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.posts) {
                        // Filter to only show jobs from current company
                        companyJobPositions = data.posts.filter(job => job.CompanyID == currentCompanyId);
                        populatePositionDropdown();
                        console.log('Company job positions loaded:', companyJobPositions);
                    } else {
                        console.error('Failed to load job positions:', data.message);
                        showErrorMessage('Failed to load job positions');
                    }
                })
                .catch(error => {
                    console.error('Error loading job positions:', error);
                    showErrorMessage('Network error loading job positions');
                });
        }

        // Populate position dropdown with company's posted jobs
        function populatePositionDropdown() {
            const positionSelect = document.getElementById('positionSelect');
            
            // Clear existing options except the first one
            positionSelect.innerHTML = '<option value="">Choose a position...</option>';
            
            if (companyJobPositions.length === 0) {
                positionSelect.innerHTML += '<option value="" disabled>No job positions posted yet</option>';
                return;
            }
            
            // Add company's job positions
            companyJobPositions.forEach(job => {
                const option = document.createElement('option');
                option.value = job.JobID;
                option.textContent = job.JobTitle;
                option.dataset.positionType = job.JobTitle.toLowerCase().replace(/\s+/g, '-');
                positionSelect.appendChild(option);
            });
        }

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

        // Initialize the application
        document.addEventListener('DOMContentLoaded', function() {
            initializeEventListeners();
            setMinDate();
            initializeTheme();
            setupThemeToggle();
            setupCompanyProfileEditing();
            loadCompanyProfile();
            loadCompanyJobPositions();
        });

        function initializeEventListeners() {
            // Interview method selection
            document.getElementById('interviewMethod').addEventListener('change', handleInterviewMethodChange);
            
            // Position selection
            document.getElementById('positionSelect').addEventListener('change', handlePositionSelection);
            
            // Meeting link input
            document.getElementById('meetingLink').addEventListener('input', handleMeetingLinkChange);
            
            // Interview location input
            document.getElementById('interviewLocation').addEventListener('input', handleLocationChange);
            
            // Date and time inputs
            document.getElementById('interviewDate').addEventListener('change', handleDateChange);
            document.getElementById('interviewTime').addEventListener('change', handleTimeChange);
            
            // Interview scheduling actions
            document.getElementById('scheduleInterviewBtn').addEventListener('click', scheduleInterview);
            
            // Select All/Deselect All buttons
            document.getElementById('selectAllBtn').addEventListener('click', selectAllCandidates);
            document.getElementById('deselectAllBtn').addEventListener('click', deselectAllCandidates);
            
            
            // Logout
            document.getElementById('logoutBtn').addEventListener('click', function() {
                window.location.href = 'Login&Signup.php';
            });
        }

        function setMinDate() {
            const today = new Date();
            const tomorrow = new Date(today);
            tomorrow.setDate(tomorrow.getDate() + 1);
            const minDate = tomorrow.toISOString().split('T')[0];
            document.getElementById('interviewDate').min = minDate;
        }

        function handleInterviewMethodChange(event) {
            interviewMethod = event.target.value;
            const meetingLinkSection = document.getElementById('meetingLinkSection');
            const locationSection = document.getElementById('locationSection');
            
            if (interviewMethod === 'virtual') {
                meetingLinkSection.style.display = 'block';
                locationSection.style.display = 'none';
                interviewLocation = ''; // Clear location when switching to virtual
                document.getElementById('interviewLocation').value = '';
            } else if (interviewMethod === 'onsite') {
                meetingLinkSection.style.display = 'none';
                locationSection.style.display = 'block';
                meetingLink = ''; // Clear meeting link when switching to onsite
                document.getElementById('meetingLink').value = '';
            } else {
                meetingLinkSection.style.display = 'none';
                locationSection.style.display = 'none';
                meetingLink = '';
                interviewLocation = '';
                document.getElementById('meetingLink').value = '';
                document.getElementById('interviewLocation').value = '';
            }
            
            updateScheduleInterviewButton();
        }

        function handlePositionSelection(event) {
            selectedPosition = event.target.value;
            const candidateListSection = document.getElementById('candidateListSection');
            const candidateList = document.getElementById('candidateList');
            
            if (selectedPosition) {
                // Find the selected job position
                const selectedJob = companyJobPositions.find(job => job.JobID == selectedPosition);
                
                if (selectedJob) {
                    // Load candidates for this specific job position
                    loadCandidatesForJob(selectedJob.JobID);
                    
                    // Show candidate list section
                    candidateListSection.style.display = 'block';
                    
                    // Show date/time section
                    document.getElementById('datetimeSection').style.display = 'block';
                }
            } else {
                candidateListSection.style.display = 'none';
                document.getElementById('datetimeSection').style.display = 'none';
                document.getElementById('scheduleInterviewSection').style.display = 'none';
            }
            
            updateScheduleInterviewButton();
        }

        // Load candidates who applied for a specific job position
        function loadCandidatesForJob(jobId) {
            console.log('Loading candidates for job ID:', jobId);
            
            const candidateList = document.getElementById('candidateList');
            candidateList.innerHTML = '<div style="text-align: center; color: var(--text-secondary); padding: 20px;"><i class="fas fa-spinner fa-spin"></i> Loading candidates who passed the MCQ exam...</div>';
            
            // Fetch real candidates who passed the MCQ exam for this job
            fetch(`get_job_candidates.php?jobId=${jobId}`)
                .then(response => {
                    console.log('Response status:', response.status);
                    return response.json();
                })
                .then(data => {
                    console.log('API Response:', data);
                    candidateList.innerHTML = '';
                    
                    if (data.success && data.candidates && data.candidates.length > 0) {
                        data.candidates.forEach(candidate => {
                            console.log('Processing candidate:', candidate);
                            const candidateItem = createCandidateItem(candidate);
                            candidateList.appendChild(candidateItem);
                        });
                        
                        console.log(`Loaded ${data.candidates.length} candidates who passed the MCQ exam`);
                    } else {
                        console.log('No candidates found or API error:', data);
                        
                        // Show detailed debug information
                        let debugInfo = '';
                        if (data.debug_info) {
                            debugInfo = `
                                <div style="font-size: 10px; margin-top: 10px; padding: 10px; background: var(--bg-tertiary); border-radius: 6px; text-align: left;">
                                    <strong>Debug Information:</strong><br>
                                    Job ID: ${data.debug_info.job_id}<br>
                                    Company ID: ${data.debug_info.company_id}<br>
                                    Job Applications Found: ${data.debug_info.job_applications_found}<br>
                                    Exam Assignments Found: ${data.debug_info.exam_assignments_found}<br>
                                    Candidates with Exam Scores: ${data.debug_info.candidates_with_exam_scores}<br>
                                    Simple Query Candidates: ${data.debug_info.simple_query_candidates}
                                </div>
                            `;
                        }
                        
                        candidateList.innerHTML = `
                            <div style="text-align: center; color: var(--text-secondary); padding: 20px;">
                                <i class="fas fa-info-circle" style="margin-bottom: 10px; font-size: 24px;"></i>
                                <div>No candidates have passed the MCQ exam for this position yet.</div>
                                <div style="font-size: 12px; margin-top: 5px; color: var(--text-secondary);">
                                    Candidates need to score 60% or higher to be eligible for interviews.
                                </div>
                                ${debugInfo}
                            </div>
                        `;
                    }
                    
                    // Update select all buttons
                    updateSelectAllButtons();
                })
                .catch(error => {
                    console.error('Error loading candidates:', error);
                    candidateList.innerHTML = `
                        <div style="text-align: center; color: var(--danger); padding: 20px;">
                            <i class="fas fa-exclamation-triangle" style="margin-bottom: 10px; font-size: 24px;"></i>
                            <div>Failed to load candidates</div>
                            <div style="font-size: 12px; margin-top: 5px;">Please try again later</div>
                            <div style="font-size: 10px; margin-top: 5px; color: var(--text-secondary); font-style: italic;">
                                Error: ${error.message}
                            </div>
                        </div>
                    `;
                    showErrorMessage('Failed to load candidates for this position');
                });
        }

        function createCandidateItem(candidate) {
            const item = document.createElement('div');
            item.className = 'candidate-item';
            
            // Create avatar initials from candidate name
            const avatar = candidate.name ? candidate.name.split(' ').map(n => n[0]).join('').toUpperCase() : 'C';
            
            // Format exam score with pass/fail indicator
            let scoreText = '';
            let scoreColor = 'var(--text-secondary)';
            
            if (candidate.examScore !== null && candidate.examScore !== undefined) {
                scoreText = `${candidate.examScore}%`;
                if (candidate.passed) {
                    scoreColor = 'var(--success)';
                    scoreText += ' ✓';
                } else {
                    scoreColor = 'var(--danger)';
                    scoreText += ' ✗';
                }
            } else if (candidate.examStatus === 'assigned') {
                scoreText = 'Exam Assigned';
                scoreColor = 'var(--warning)';
            } else if (candidate.examStatus === 'in-progress') {
                scoreText = 'Exam In Progress';
                scoreColor = 'var(--accent-1)';
            } else if (candidate.examStatus === 'completed') {
                scoreText = 'Exam Completed';
                scoreColor = 'var(--accent-1)';
            } else {
                scoreText = 'No Exam Assigned';
                scoreColor = 'var(--text-secondary)';
            }
            
            // Format application date
            const applicationDate = candidate.applicationDate ? 
                new Date(candidate.applicationDate).toLocaleDateString() : 'Unknown';
            
            item.innerHTML = `
                <div class="candidate-info">
                    <div class="candidate-avatar-small">${avatar}</div>
                    <div class="candidate-details">
                        <h4>${candidate.name || 'Unknown'}</h4>
                        <p>${candidate.email || 'No email'}</p>
                        <p style="font-size: 12px; color: var(--text-secondary); margin-top: 2px;">
                            Applied: ${applicationDate} • 
                            <span style="color: ${scoreColor}; font-weight: 500;">MCQ Score: ${scoreText}</span>
                        </p>
                    </div>
                </div>
                    <input type="checkbox" class="candidate-checkbox" 
                           data-candidate-id="${candidate.candidateId}" 
                           data-candidate-email="${candidate.email}" 
                           data-candidate-name="${candidate.name}">
            `;
            
            // Add checkbox event listener
            const checkbox = item.querySelector('.candidate-checkbox');
            checkbox.addEventListener('change', handleCandidateSelection);
            
                // All candidates shown have passed the exam, so they're all selectable
                // No visual indicators needed since all candidates are eligible
            
            return item;
        }

        function handleCandidateSelection(event) {
            const candidateId = event.target.dataset.candidateId;
            const candidateEmail = event.target.dataset.candidateEmail;
            const candidateName = event.target.dataset.candidateName;
            
            if (event.target.checked) {
                selectedCandidates.push({
                    id: candidateId,
                    email: candidateEmail,
                    name: candidateName
                });
            } else {
                selectedCandidates = selectedCandidates.filter(c => c.id !== candidateId);
            }
            
            updateScheduleInterviewButton();
            updateSelectAllButtons();
        }

        function selectAllCandidates() {
            const checkboxes = document.querySelectorAll('.candidate-checkbox');
            selectedCandidates = [];
            
            checkboxes.forEach(checkbox => {
                checkbox.checked = true;
                selectedCandidates.push({
                    id: checkbox.dataset.candidateId,
                    email: checkbox.dataset.candidateEmail,
                    name: checkbox.dataset.candidateName
                });
            });
            
            updateScheduleInterviewButton();
            updateSelectAllButtons();
        }

        function deselectAllCandidates() {
            const checkboxes = document.querySelectorAll('.candidate-checkbox');
            
            checkboxes.forEach(checkbox => {
                checkbox.checked = false;
            });
            
            selectedCandidates = [];
            updateScheduleInterviewButton();
            updateSelectAllButtons();
        }

        function updateSelectAllButtons() {
            const selectAllBtn = document.getElementById('selectAllBtn');
            const deselectAllBtn = document.getElementById('deselectAllBtn');
            const totalCandidates = document.querySelectorAll('.candidate-checkbox').length;
            
            if (selectedCandidates.length === 0) {
                selectAllBtn.style.display = 'flex';
                deselectAllBtn.style.display = 'none';
            } else if (selectedCandidates.length === totalCandidates) {
                selectAllBtn.style.display = 'none';
                deselectAllBtn.style.display = 'flex';
            } else {
                selectAllBtn.style.display = 'flex';
                deselectAllBtn.style.display = 'flex';
            }
        }

        function handleMeetingLinkChange(event) {
            meetingLink = event.target.value;
            updateScheduleInterviewButton();
        }

        function handleLocationChange(event) {
            interviewLocation = event.target.value;
            updateScheduleInterviewButton();
        }

        function handleDateChange(event) {
            interviewDate = event.target.value;
            updateScheduleInterviewButton();
        }

        function handleTimeChange(event) {
            interviewTime = event.target.value;
            updateScheduleInterviewButton();
        }

        function updateScheduleInterviewButton() {
            const scheduleInterviewSection = document.getElementById('scheduleInterviewSection');
            const isVirtualInterview = interviewMethod === 'virtual';
            const isOnsiteInterview = interviewMethod === 'onsite';
            
            // Check requirements based on interview method
            const hasMeetingLink = !isVirtualInterview || meetingLink; // Meeting link only required for virtual interviews
            const hasLocation = !isOnsiteInterview || interviewLocation; // Location only required for onsite interviews
            const canSchedule = interviewMethod && hasMeetingLink && hasLocation && selectedPosition && selectedCandidates.length > 0 && interviewDate && interviewTime;
            
            if (canSchedule) {
                scheduleInterviewSection.style.display = 'flex';
            } else {
                scheduleInterviewSection.style.display = 'none';
            }
        }




        function validateForm() {
            if (!interviewMethod) {
                alert('Please select an interview method');
                return false;
            }
            
            if (interviewMethod === 'virtual' && !meetingLink) {
                alert('Please enter a meeting link for virtual interviews');
                return false;
            }
            
            if (interviewMethod === 'onsite' && !interviewLocation) {
                alert('Please enter an interview location for onsite interviews');
                return false;
            }
            
            if (!selectedPosition) {
                alert('Please select a position');
                return false;
            }
            
            if (selectedCandidates.length === 0) {
                alert('Please select at least one candidate');
                return false;
            }
            
            if (!interviewDate) {
                alert('Please select an interview date');
                return false;
            }
            
            if (!interviewTime) {
                alert('Please select an interview time');
                return false;
            }
            
            return true;
        }

        function scheduleInterview() {
            if (!validateForm()) return;
            
            const scheduleBtn = document.getElementById('scheduleInterviewBtn');
            const originalText = scheduleBtn.innerHTML;
            
            scheduleBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Scheduling...';
            scheduleBtn.disabled = true;
            
            // Prepare interview data for submission
            const interviewData = {
                interviewMethod: interviewMethod,
                meetingLink: meetingLink,
                interviewLocation: interviewLocation,
                positionId: selectedPosition,
                positionName: document.getElementById('positionSelect').selectedOptions[0].text,
                candidateIds: selectedCandidates.map(c => c.id),
                candidateNames: selectedCandidates.map(c => c.name),
                candidateEmails: selectedCandidates.map(c => c.email),
                interviewDate: interviewDate,
                interviewTime: interviewTime,
                companyId: currentCompanyId
            };
            
            // Submit interview data to server
            fetch('interview_schedule_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(interviewData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccessMessage('Interview Scheduled! Successfully sent invitation');
                    
                    // Reset form after successful scheduling
                    resetForm();
                    
                    // Stay on the same page - no redirect needed
                    // The form will be reset and ready for next scheduling
                    
                    // Restore button
                    scheduleBtn.innerHTML = originalText;
                    scheduleBtn.disabled = false;
                } else {
                    showErrorMessage(data.message || 'Failed to schedule interview');
                    
                    // Restore button
                    scheduleBtn.innerHTML = originalText;
                    scheduleBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error scheduling interview:', error);
                showErrorMessage('Network error. Please try again.');
                
                // Restore button
                scheduleBtn.innerHTML = originalText;
                scheduleBtn.disabled = false;
            });
        }

        function resetForm() {
            document.getElementById('interviewMethod').value = '';
            document.getElementById('meetingLink').value = '';
            document.getElementById('interviewLocation').value = '';
            document.getElementById('positionSelect').value = '';
            document.getElementById('interviewDate').value = '';
            document.getElementById('interviewTime').value = '';
            
            document.getElementById('meetingLinkSection').style.display = 'none';
            document.getElementById('locationSection').style.display = 'none';
            document.getElementById('candidateListSection').style.display = 'none';
            document.getElementById('datetimeSection').style.display = 'none';
            document.getElementById('scheduleInterviewSection').style.display = 'none';
            
            // Reset select all buttons
            document.getElementById('selectAllBtn').style.display = 'flex';
            document.getElementById('deselectAllBtn').style.display = 'none';
            
            selectedPosition = null;
            selectedCandidates = [];
            interviewMethod = '';
            meetingLink = '';
            interviewLocation = '';
            interviewDate = '';
            interviewTime = '';
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

    </script>
</body>
</html>

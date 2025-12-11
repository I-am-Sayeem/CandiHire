<?php
// CvChecker.php - CV Checker for Companies
require_once 'session_manager.php';

// Check if company is logged in
if (!isCompanyLoggedIn()) {
    header('Location: Login&Signup.php');
    exit;
}

// Get company ID from session
$sessionCompanyId = getCurrentCompanyId();
$companyName = $_SESSION['company_name'] ?? 'Company';

// Load company data from database
$companyLogo = null;
require_once 'Database.php';

// Ensure upload directory exists
$uploadDir = 'uploads/cv_processing/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Check and create CV processing tables if they don't exist
function ensureCVProcessingTables() {
    global $pdo;
    
    if (!$pdo) return false;
    
    try {
        // Check if cv_processing_results table exists
        $stmt = $pdo->prepare("SHOW TABLES LIKE 'cv_processing_results'");
        $stmt->execute();
        
        if ($stmt->rowCount() === 0) {
            // Create tables using the SQL file
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
                        $pdo->exec($statement);
                    }
                }
                error_log("CV processing tables created successfully");
            }
        }
        return true;
    } catch (Exception $e) {
        error_log("Error ensuring CV processing tables: " . $e->getMessage());
        return false;
    }
}

try {
    if (isset($pdo) && $pdo instanceof PDO) {
        // Ensure CV processing tables exist
        ensureCVProcessingTables();
        
        // Always try to get fresh data from database
        $stmt = $pdo->prepare("SELECT CompanyName, Logo FROM Company_login_info WHERE CompanyID = ?");
        $stmt->execute([$sessionCompanyId]);
        $company = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($company) {
            $companyName = $company['CompanyName'];
            $companyLogo = $company['Logo'];
            $_SESSION['company_name'] = $companyName;
        } else {
            // Fallback if no company found
            $companyName = $_SESSION['company_name'] ?? 'Company';
        }
    } else {
        // Fallback if database connection failed
        $companyName = $_SESSION['company_name'] ?? 'Company';
        error_log("Database connection not available in CvChecker.php");
    }
} catch (Exception $e) {
    error_log("Error loading company data: " . $e->getMessage());
    $companyName = $_SESSION['company_name'] ?? 'Company';
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CV Checker - CandiHire</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg-primary: #0d1117;
            --bg-secondary: #161b22;
            --bg-tertiary: #21262d;
            --bg-hover: #21262d;
            --text-primary: #c9d1d9;
            --text-secondary: #8b949e;
            --accent-1: #58a6ff;
            --accent-2: #f59e0b;
            --accent-hover: #79c0ff;
            --success: #3fb950;
            --danger: #f85149;
            --border: #30363d;
            --shadow: rgba(0, 0, 0, 0.25);
        }

        /* Light Theme */
        [data-theme="light"] {
            --bg-primary: #ffffff;
            --bg-secondary: #f6f8fa;
            --bg-tertiary: #eaeef2;
            --bg-hover: #eaeef2;
            --text-primary: #24292f;
            --text-secondary: #656d76;
            --accent-1: #0969da;
            --accent-2: #f59e0b;
            --accent-hover: #0860ca;
            --success: #1a7f37;
            --danger: #d1242f;
            --border: #d1d9e0;
            --shadow: rgba(0, 0, 0, 0.1);
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
            line-height: 1.6;
            overflow-x: hidden;
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
            z-index: 10;
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
            transition: all 0.2s;
            font-size: 15px;
        }

        .nav-item:hover {
            background-color: var(--bg-tertiary);
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
            display: flex;
            flex-direction: column;
        }

        /* CV Checker Container */
        .cv-checker-container {
            display: flex;
            gap: 20px;
            flex: 1;
            animation: fadeIn 0.5s ease-out;
        }

        /* Left Panel - Upload CVs */
        .left-panel {
            flex: 1;
            background-color: var(--bg-secondary);
            border-radius: 16px;
            padding: 24px;
            border: 1px solid var(--border);
            box-shadow: 0 8px 32px var(--shadow);
            display: flex;
            flex-direction: column;
            animation: slideInLeft 0.6s ease-out;
        }

        .panel-title {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 24px;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .panel-title i {
            color: var(--accent-1);
        }

        .upload-area {
            flex: 1;
            border: 2px dashed var(--border);
            border-radius: 12px;
            padding: 40px 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .upload-area::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(88, 166, 255, 0.1) 0%, rgba(88, 166, 255, 0) 70%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .upload-area:hover {
            border-color: var(--accent-1);
            transform: translateY(-4px);
            box-shadow: 0 10px 20px rgba(88, 166, 255, 0.2);
        }

        .upload-area:hover::before {
            opacity: 1;
        }

        .upload-icon {
            font-size: 48px;
            color: var(--accent-1);
            margin-bottom: 16px;
            animation: float 3s ease-in-out infinite;
        }

        .upload-text {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text-primary);
        }

        .upload-subtext {
            font-size: 14px;
            color: var(--text-secondary);
            margin-bottom: 24px;
        }

        .process-btn {
            background: linear-gradient(135deg, var(--accent-1), var(--accent-hover));
            color: white;
            border: none;
            border-radius: 10px;
            padding: 14px 28px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(88, 166, 255, 0.3);
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 30px;
        }

        .process-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(88, 166, 255, 0.4);
        }

        .process-btn:active {
            transform: translateY(0);
        }

        /* Middle Panel - Candidates */
        .middle-panel {
            flex: 1.5;
            display: flex;
            flex-direction: column;
            gap: 20px;
            animation: slideInUp 0.7s ease-out;
        }

        .candidate-section {
            background-color: var(--bg-secondary);
            border-radius: 16px;
            padding: 24px;
            border: 1px solid var(--border);
            box-shadow: 0 8px 32px var(--shadow);
            flex: 1;
            display: flex;
            flex-direction: column;
            transition: all 0.3s ease;
        }

        .candidate-section:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 36px var(--shadow);
        }

        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 20px;
            font-weight: 600;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title i {
            color: var(--accent-1);
        }

        .candidate-count {
            font-size: 12px;
            font-weight: 500;
            color: var(--accent-1);
            background-color: var(--bg-tertiary);
            padding: 2px 8px;
            border-radius: 12px;
            border: 1px solid var(--accent-1);
            margin-left: auto;
        }

        .threshold-badge {
            background-color: var(--bg-tertiary);
            color: var(--success);
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            border: 1px solid var(--border);
        }

        .empty-state {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            text-align: center;
            color: var(--text-secondary);
        }

        .empty-icon {
            font-size: 64px;
            margin-bottom: 16px;
            opacity: 0.5;
        }

        .empty-text {
            font-size: 18px;
            font-weight: 500;
        }

        /* Loading Spinner */
        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 4px solid var(--border);
            border-top: 4px solid var(--accent-1);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Candidate Cards */
        .candidate-card {
            background-color: var(--bg-tertiary);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 12px;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
        }

        .candidate-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            border-color: var(--accent-1);
        }

        .candidate-card.selected {
            background-color: rgba(88, 166, 255, 0.1);
            border-color: var(--accent-1);
        }

        .candidate-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }

        .candidate-name {
            font-size: 16px;
            font-weight: 600;
            color: var(--text-primary);
        }

        .match-percentage {
            background: linear-gradient(135deg, var(--accent-1), var(--accent-hover));
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .candidate-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            font-size: 14px;
            color: var(--text-secondary);
        }

        .candidate-detail {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .candidate-detail i {
            color: var(--accent-1);
            width: 16px;
        }

        .candidate-skills {
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid var(--border);
        }

        .skills-label {
            font-size: 12px;
            color: var(--text-secondary);
            margin-bottom: 6px;
        }

        .skills-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .skill-tag {
            background-color: var(--bg-primary);
            color: var(--text-primary);
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            border: 1px solid var(--border);
        }

        /* Contact Info Card */
        .contact-info-card {
            background-color: var(--bg-tertiary);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 20px;
            margin: 10px 0;
        }

        .contact-header {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border);
        }

        .contact-name {
            font-size: 20px;
            font-weight: 600;
            color: var(--text-primary);
            margin: 0 0 5px 0;
        }

        .contact-status {
            font-size: 12px;
            color: var(--accent-1);
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .contact-details-grid {
            display: grid;
            gap: 15px;
            margin-bottom: 20px;
        }

        .contact-detail {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background-color: var(--bg-primary);
            border-radius: 8px;
            border: 1px solid var(--border);
        }

        .contact-icon {
            width: 40px;
            height: 40px;
            background-color: var(--accent-1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 16px;
        }

        .contact-content {
            flex: 1;
        }

        .contact-label {
            font-size: 12px;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .contact-value {
            font-size: 14px;
            color: var(--text-primary);
            font-weight: 500;
        }

        .linkedin-link {
            color: var(--accent-1);
            text-decoration: none;
            font-weight: 500;
        }

        .linkedin-link:hover {
            text-decoration: underline;
        }

        .contact-skills {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid var(--border);
        }

        .skills-header {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 10px;
        }

        .no-skills {
            color: var(--text-secondary);
            font-style: italic;
            font-size: 14px;
        }

        .contact-summary {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid var(--border);
        }

        .summary-text {
            color: var(--text-primary);
            font-size: 14px;
            line-height: 1.5;
            background-color: var(--bg-primary);
            padding: 12px;
            border-radius: 8px;
            border: 1px solid var(--border);
            margin-top: 8px;
        }

        /* File List */
        .file-item {
            background-color: var(--bg-tertiary);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .file-icon {
            color: var(--danger);
            font-size: 20px;
        }

        .file-name {
            flex: 1;
            color: var(--text-primary);
            font-size: 14px;
        }

        .file-size {
            color: var(--text-secondary);
            font-size: 12px;
        }

        /* Right Panel - Requirements */
        .right-panel {
            flex: 1;
            background-color: var(--bg-secondary);
            border-radius: 16px;
            padding: 24px;
            border: 1px solid var(--border);
            box-shadow: 0 8px 32px var(--shadow);
            animation: slideInRight 0.6s ease-out;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text-primary);
        }

        .form-input, .form-select, .form-textarea {
            width: 100%;
            background-color: var(--bg-tertiary);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 12px 16px;
            color: var(--text-primary);
            font-size: 15px;
            transition: all 0.3s ease;
        }

        .form-textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-input:focus, .form-select:focus, .form-textarea:focus {
            outline: none;
            border-color: var(--accent-1);
            box-shadow: 0 0 0 3px rgba(88, 166, 255, 0.2);
        }

        .skills-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }

        .skill-tag {
            background-color: var(--bg-tertiary);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 8px 16px;
            font-size: 14px;
            font-weight: 500;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
        }

        .skill-tag:hover {
            background-color: var(--accent-1);
            color: white;
            border-color: var(--accent-1);
            transform: translateY(-2px);
        }

        .skill-tag i {
            cursor: pointer;
            font-size: 12px;
        }

        .add-skill {
            background-color: transparent;
            border: 1px dashed var(--border);
            border-radius: 20px;
            padding: 8px 16px;
            font-size: 14px;
            color: var(--text-secondary);
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .add-skill:hover {
            border-color: var(--accent-1);
            color: var(--accent-1);
        }

        .apply-filters-btn {
            width: 100%;
            background: linear-gradient(135deg, var(--accent-1), var(--accent-hover));
            color: white;
            border: none;
            border-radius: 10px;
            padding: 14px 20px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(88, 166, 255, 0.3);
            margin-top: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .apply-filters-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(88, 166, 255, 0.4);
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

        /* Animations */
        .nav-item, .candidate-section, .left-panel, .right-panel, .tip-card, .interview-card, .skill-tag, .apply-filters-btn, .process-btn {
            will-change: transform, box-shadow, background-color, color, opacity;
        }

        .nav-item { transition: background-color 0.2s ease, color 0.2s ease, transform 0.2s ease; }
        .nav-item:hover { transform: translateX(2px); }

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-10px);
            }
        }

        /* Popup Styles */
        .popup-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
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
            margin: 5% auto;
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

        .btn-secondary {
            background: var(--bg-tertiary);
            color: var(--text-primary);
            border: 1px solid var(--border);
        }

        .btn-secondary:hover {
            background: var(--bg-hover);
        }

        .btn-primary {
            background: var(--accent-2);
            color: white;
        }

        .btn-primary:hover {
            background: #e67e22;
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .cv-checker-container {
                flex-direction: column;
            }
            
            .left-panel, .right-panel {
                flex: none;
            }
        }

        @media (max-width: 768px) {
            .left-nav {
                display: none;
            }
            .main-content {
                padding: 10px;
            }
            .cv-checker-container {
                gap: 15px;
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
                <div class="nav-item active">
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
            <div class="cv-checker-container">
                <!-- Left Panel - Upload CVs -->
                <div class="left-panel">
                    <h2 class="panel-title">
                        <i class="fas fa-cloud-upload-alt"></i>
                        Upload External CVs
                    </h2>
                    
                    <div class="upload-area" id="uploadArea">
                        <i class="fas fa-file-upload upload-icon"></i>
                        <div class="upload-text">Drag & Drop CVs Here</div>
                        <div class="upload-subtext">Upload external candidate CVs (PDF files)</div>
                        <input type="file" id="cvFiles" multiple accept=".pdf" style="display: none;">
                    </div>
                    
                    <!-- Uploaded Files List -->
                    <div id="uploadedFilesList" style="margin-top: 20px; display: none;">
                        <h4 style="color: var(--text-primary); margin-bottom: 10px;">Uploaded Files:</h4>
                        <div id="filesList"></div>
                    </div>
                    
                    <!-- Loading Animation -->
                    <div id="loadingAnimation" style="display: none; text-align: center; margin: 20px 0;">
                        <div class="loading-spinner"></div>
                        <p style="color: var(--text-secondary); margin-top: 10px;">Processing CVs...</p>
                    </div>
                    
                    <button class="process-btn" id="processBtn" disabled>
                        <i class="fas fa-cogs"></i>
                        Process CVs
                    </button>
                    
                    <button class="process-btn" id="debugBtn" style="margin-top: 10px; background: linear-gradient(135deg, var(--accent-2), #e67e22);">
                        <i class="fas fa-bug"></i>
                        Debug Connection
                    </button>
                </div>

                <!-- Middle Panel - Candidates -->
                <div class="middle-panel">
                    <!-- Filtered Candidates -->
                    <div class="candidate-section">
                        <div class="section-header">
                            <h3 class="section-title">
                                <i class="fas fa-filter"></i>
                                Filtered Candidates
                                <span id="candidateCount" class="candidate-count">(0)</span>
                            </h3>
                        </div>
                        
                        <div id="filteredCandidatesList" class="candidates-list">
                            <div class="empty-state">
                                <i class="fas fa-user-slash empty-icon"></i>
                                <div class="empty-text">No candidates found</div>
                            </div>
                        </div>
                    </div>

                    <!-- Candidate Contact Info -->
                    <div class="candidate-section">
                        <div class="section-header">
                            <h3 class="section-title">
                                <i class="fas fa-address-card"></i>
                                Candidate Contact Info
                            </h3>
                        </div>
                        
                        <div id="candidateContactInfo" class="candidates-list">
                            <div class="empty-state">
                                <i class="fas fa-user empty-icon"></i>
                                <div class="empty-text">Click on a candidate to view contact info</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Panel - Requirements -->
                <div class="right-panel">
                    <h2 class="panel-title">
                        <i class="fas fa-clipboard-list"></i>
                        Job Requirements
                    </h2>
                    <p style="color: var(--text-secondary); font-size: 14px; margin-bottom: 20px;">
                        Set requirements to match external candidates against your job opening
                    </p>
                    
                    <form id="requirementsForm">
                        <div class="form-group">
                            <label class="form-label" for="jobPosition">Job Position</label>
                            <input type="text" id="jobPosition" class="form-input" placeholder="e.g. Frontend Developer">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="experienceLevel">Experience Level</label>
                            <select id="experienceLevel" class="form-select">
                                <option value="any" selected>Any Experience</option>
                                <option value="entry">Entry Level (0-2 years)</option>
                                <option value="mid">Mid Level (3-5 years)</option>
                                <option value="senior">Senior Level (6+ years)</option>
                                <option value="lead">Lead/Principal (8+ years)</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="requiredSkills">Required Skills</label>
                            <div class="skills-container" id="skillsContainer">
                                <div class="skill-tag">
                                    JavaScript
                                    <i class="fas fa-times"></i>
                                </div>
                                <div class="skill-tag">
                                    React
                                    <i class="fas fa-times"></i>
                                </div>
                                <div class="skill-tag">
                                    Node.js
                                    <i class="fas fa-times"></i>
                                </div>
                                <div class="add-skill" id="addSkillBtn">
                                    <i class="fas fa-plus"></i> Add Skill
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="customCriteria">Custom Criteria</label>
                            <textarea id="customCriteria" class="form-textarea" placeholder="Enter any additional criteria or requirements for candidates..."></textarea>
                        </div>
                        
                        
                        <button type="button" class="apply-filters-btn">
                            <i class="fas fa-refresh"></i>
                            Refresh Candidates
                        </button>
                    </form>
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

        // Navigation item functionality
        document.querySelectorAll('.nav-item').forEach(item => {
            item.addEventListener('click', function() {
                document.querySelectorAll('.nav-item').forEach(navItem => {
                    navItem.classList.remove('active');
                });
                this.classList.add('active');
                
                // Navigate to different pages
                const navText = this.querySelector('span').textContent;
                if (navText === 'Job Posts') {
                    window.location.href = 'JobPost.php';
                } else if (navText === 'Candidate Feed') {
                    window.location.href = 'CompanyDashboard.php';
                } else if (navText === 'Exams') {
                    window.location.href = 'CreateExam.php';
                } else if (navText === 'Interviews') {
                    window.location.href = 'Interview.php';
                } else if (navText === 'View Applications') {
                    window.location.href = 'company_applications.php';
                } else if (navText === 'AI Matching') {
                    window.location.href = 'AIMatching.php';
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

        // Upload area functionality - moved to initializeCVProcessing() to avoid duplicates
        
        // Skills functionality
        const skillsContainer = document.getElementById('skillsContainer');
        const addSkillBtn = document.getElementById('addSkillBtn');
        
        addSkillBtn.addEventListener('click', () => {
            const skillName = prompt('Enter skill name:');
            if (skillName && skillName.trim() !== '') {
                const skillTag = document.createElement('div');
                skillTag.className = 'skill-tag';
                skillTag.innerHTML = `
                    ${skillName.trim()}
                    <i class="fas fa-times"></i>
                `;
                
                // Add remove functionality
                skillTag.querySelector('i').addEventListener('click', () => {
                    skillTag.style.transform = 'scale(0)';
                    skillTag.style.opacity = '0';
                    setTimeout(() => skillTag.remove(), 300);
                });
                
                skillsContainer.insertBefore(skillTag, addSkillBtn);
                
                // Add animation
                skillTag.style.transform = 'scale(0)';
                skillTag.style.opacity = '0';
                setTimeout(() => {
                    skillTag.style.transform = 'scale(1)';
                    skillTag.style.opacity = '1';
                }, 10);
            }
        });
        
        // Remove skill functionality
        document.querySelectorAll('.skill-tag i').forEach(icon => {
            icon.addEventListener('click', function() {
                const skillTag = this.parentElement;
                skillTag.style.transform = 'scale(0)';
                skillTag.style.opacity = '0';
                setTimeout(() => skillTag.remove(), 300);
            });
        });
        
        
        // Apply filters button
        const applyFiltersBtn = document.querySelector('.apply-filters-btn');
        
        applyFiltersBtn.addEventListener('click', () => {
            // Add processing animation
            applyFiltersBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Applying Filters...';
            applyFiltersBtn.disabled = true;
            
            // Simulate filtering
            setTimeout(() => {
                applyFiltersBtn.innerHTML = '<i class="fas fa-check-circle"></i> Filters Applied!';
                applyFiltersBtn.style.background = 'linear-gradient(135deg, var(--success), #3aa8c9)';
                
                setTimeout(() => {
                    applyFiltersBtn.innerHTML = '<i class="fas fa-refresh"></i> Refresh Candidates';
                    applyFiltersBtn.style.background = '';
                    applyFiltersBtn.disabled = false;
                }, 2000);
            }, 1500);
        });

        // Company Profile Management
        let currentCompanyId = <?php echo json_encode($sessionCompanyId); ?>;

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
                e.stopPropagation();
                updateCompanyProfile();
            });
            
            // Handle logo preview
            const logoInput = document.getElementById('companyLogoFile');
            if (logoInput) {
                logoInput.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        // Validate file type
                        if (!file.type.startsWith('image/')) {
                            showErrorMessage('Please select a valid image file');
                            e.target.value = '';
                            return;
                        }
                        
                        // Validate file size (5MB limit)
                        if (file.size > 5 * 1024 * 1024) {
                            showErrorMessage('Image file size must be less than 5MB');
                            e.target.value = '';
                            return;
                        }
                        
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const preview = document.getElementById('companyLogoPreview');
                            const currentLogo = document.getElementById('currentCompanyLogo');
                            if (preview && currentLogo) {
                                preview.src = e.target.result;
                                currentLogo.style.display = 'block';
                            }
                        };
                        reader.onerror = function() {
                            showErrorMessage('Error reading image file');
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
                showErrorMessage('Company ID not found. Please log in again.');
                return;
            }
            
            fetch(`company_profile_handler.php?companyId=${currentCompanyId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        const company = data.company;
                        console.log('Company profile data loaded:', company);
                        
                        // Update display
                        const nameDisplay = document.getElementById('companyNameDisplay');
                        const logo = document.getElementById('companyLogo');
                        
                        if (nameDisplay) {
                            nameDisplay.textContent = company.CompanyName || 'Company';
                        }
                        
                        if (logo) {
                            if (company.Logo) {
                                logo.style.backgroundImage = `url(${company.Logo})`;
                                logo.style.backgroundSize = 'cover';
                                logo.style.backgroundPosition = 'center';
                                logo.textContent = '';
                            } else {
                                logo.style.backgroundImage = '';
                                logo.style.background = 'linear-gradient(135deg, var(--accent-2), #e67e22)';
                                logo.textContent = (company.CompanyName || 'C').charAt(0).toUpperCase();
                            }
                        }
                    } else {
                        console.error('Failed to load company profile:', data.message);
                        showErrorMessage(data.message || 'Failed to load company profile');
                    }
                })
                .catch(error => {
                    console.error('Error loading company profile:', error);
                    showErrorMessage('Network error loading company profile. Please try again.');
                });
        }

        // Load current company profile for editing
        function loadCurrentCompanyProfile() {
            console.log('Loading current company profile for editing...');
            
            if (!currentCompanyId) {
                console.error('No company ID available');
                showErrorMessage('Company ID not found. Please log in again.');
                return;
            }
            
            fetch(`company_profile_handler.php?companyId=${currentCompanyId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        const company = data.company;
                        console.log('Company profile data loaded for editing:', company);
                        
                        // Populate form fields with null checks
                        const fields = {
                            'companyName': company.CompanyName || '',
                            'industry': company.Industry || '',
                            'companySize': company.CompanySize || '',
                            'phoneNumber': company.PhoneNumber || '',
                            'website': company.Website || '',
                            'companyDescription': company.CompanyDescription || '',
                            'address': company.Address || '',
                            'city': company.City || '',
                            'state': company.State || '',
                            'country': company.Country || '',
                            'postalCode': company.PostalCode || ''
                        };
                        
                        Object.entries(fields).forEach(([fieldId, value]) => {
                            const element = document.getElementById(fieldId);
                            if (element) {
                                element.value = value;
                            }
                        });
                        
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
                        showErrorMessage(data.message || 'Failed to load company profile data');
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

        // CV Processing Variables
        let uploadedFiles = [];
        let currentProcessingId = null;
        let allCandidates = [];

        // CV Processing Functions
        function initializeCVProcessing() {
            const uploadArea = document.getElementById('uploadArea');
            const cvFilesInput = document.getElementById('cvFiles');
            const processBtn = document.getElementById('processBtn');
            const applyFiltersBtn = document.querySelector('.apply-filters-btn');
            
            if (!uploadArea || !cvFilesInput || !processBtn) {
                console.error('Required CV processing elements not found');
                return;
            }

            // File upload handling
            uploadArea.addEventListener('click', () => cvFilesInput.click());
            uploadArea.addEventListener('dragover', handleDragOver);
            uploadArea.addEventListener('drop', handleDrop);
            cvFilesInput.addEventListener('change', handleFileSelect);

            // Process CVs button
            processBtn.addEventListener('click', processCVs);
            
            // Debug button
            const debugBtn = document.getElementById('debugBtn');
            if (debugBtn) {
                debugBtn.addEventListener('click', debugConnection);
            }

            // Apply filters button
            if (applyFiltersBtn) {
                applyFiltersBtn.addEventListener('click', applyFilters);
            }

            // Export functionality removed - no longer needed

        }

        function handleDragOver(e) {
            e.preventDefault();
            e.stopPropagation();
            e.currentTarget.style.borderColor = 'var(--accent-1)';
            e.currentTarget.style.backgroundColor = 'rgba(88, 166, 255, 0.1)';
        }

        function handleDrop(e) {
            e.preventDefault();
            e.stopPropagation();
            e.currentTarget.style.borderColor = 'var(--border)';
            e.currentTarget.style.backgroundColor = 'transparent';
            
            const files = Array.from(e.dataTransfer.files).filter(file => 
                file.type === 'application/pdf'
            );
            
            if (files.length > 0) {
                handleFiles(files);
            }
        }

        function handleFileSelect(e) {
            const files = Array.from(e.target.files).filter(file => 
                file.type === 'application/pdf'
            );
            
            if (files.length > 0) {
                handleFiles(files);
            }
        }

        function handleFiles(files) {
            const validFiles = [];
            const maxFileSize = 10 * 1024 * 1024; // 10MB
            const allowedTypes = ['application/pdf'];
            
            files.forEach(file => {
                // Validate file type
                if (!allowedTypes.includes(file.type)) {
                    showErrorMessage(`File "${file.name}" is not a PDF file`);
                    return;
                }
                
                // Validate file size
                if (file.size > maxFileSize) {
                    showErrorMessage(`File "${file.name}" is too large. Maximum size is 10MB`);
                    return;
                }
                
                // Check for duplicate files
                const isDuplicate = uploadedFiles.some(existingFile => 
                    existingFile.name === file.name && existingFile.size === file.size
                );
                
                if (isDuplicate) {
                    showErrorMessage(`File "${file.name}" is already uploaded`);
                    return;
                }
                
                validFiles.push(file);
            });
            
            if (validFiles.length > 0) {
                uploadedFiles = [...uploadedFiles, ...validFiles];
                displayUploadedFiles();
                document.getElementById('processBtn').disabled = false;
                showSuccessMessage(`${validFiles.length} file(s) added successfully`);
            }
        }

        function displayUploadedFiles() {
            const filesList = document.getElementById('filesList');
            const uploadedFilesList = document.getElementById('uploadedFilesList');
            
            if (uploadedFiles.length === 0) {
                uploadedFilesList.style.display = 'none';
                return;
            }

            uploadedFilesList.style.display = 'block';
            filesList.innerHTML = '';

            uploadedFiles.forEach((file, index) => {
                const fileItem = document.createElement('div');
                fileItem.className = 'file-item';
                fileItem.innerHTML = `
                    <i class="fas fa-file-pdf file-icon"></i>
                    <span class="file-name">${file.name}</span>
                    <span class="file-size">${(file.size / 1024 / 1024).toFixed(2)} MB</span>
                    <button onclick="removeFile(${index})" style="background: none; border: none; color: var(--danger); cursor: pointer;">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                filesList.appendChild(fileItem);
            });
        }

        function removeFile(index) {
            uploadedFiles.splice(index, 1);
            displayUploadedFiles();
            
            if (uploadedFiles.length === 0) {
                document.getElementById('processBtn').disabled = true;
            }
        }

        async function processCVs() {
            if (uploadedFiles.length === 0) {
                showErrorMessage('Please upload CV files first');
                return;
            }

            const jobPosition = document.getElementById('jobPosition').value;
            const experienceLevel = document.getElementById('experienceLevel').value;
            const requiredSkills = getRequiredSkills();
            const customCriteria = document.getElementById('customCriteria').value;

            if (!jobPosition) {
                showErrorMessage('Please fill in Job Position');
                return;
            }

            // Show loading animation
            document.getElementById('loadingAnimation').style.display = 'block';
            document.getElementById('processBtn').disabled = true;

            try {
                // Upload files
                const formData = new FormData();
                uploadedFiles.forEach(file => {
                    formData.append('cvs[]', file);
                });
                formData.append('action', 'upload_cvs');
                formData.append('jobPosition', jobPosition);
                formData.append('experienceLevel', experienceLevel);
                formData.append('requiredSkills', requiredSkills);
                formData.append('customCriteria', customCriteria);

                const uploadResponse = await fetch('cv_processing_handler.php', {
                    method: 'POST',
                    body: formData
                });

                if (!uploadResponse.ok) {
                    const errorText = await uploadResponse.text();
                    console.error('Upload error response:', errorText);
                    throw new Error(`HTTP error! status: ${uploadResponse.status} - ${errorText}`);
                }

                const responseText = await uploadResponse.text();
                console.log('Raw response:', responseText);
                
                let uploadResult;
                try {
                    uploadResult = JSON.parse(responseText);
                } catch (parseError) {
                    console.error('JSON parse error:', parseError);
                    console.error('Response text:', responseText);
                    throw new Error('Invalid JSON response from server');
                }

                if (!uploadResult.success) {
                    throw new Error(uploadResult.message || 'Upload failed');
                }

                currentProcessingId = uploadResult.processingId;

                // Process CVs
                const processFormData = new FormData();
                processFormData.append('action', 'process_cvs');
                processFormData.append('processingId', currentProcessingId);

                const processResponse = await fetch('cv_processing_handler.php', {
                    method: 'POST',
                    body: processFormData
                });

                if (!processResponse.ok) {
                    throw new Error(`HTTP error! status: ${processResponse.status}`);
                }

                const processResponseText = await processResponse.text();
                console.log('Process response:', processResponseText);
                
                let processResult;
                try {
                    processResult = JSON.parse(processResponseText);
                } catch (parseError) {
                    console.error('JSON parse error:', parseError);
                    console.error('Response text:', processResponseText);
                    throw new Error('Invalid JSON response from server');
                }

                if (!processResult.success) {
                    throw new Error(processResult.message || 'Processing failed');
                }

                console.log('Process result:', processResult);
                allCandidates = processResult.candidates;
                console.log('All candidates:', allCandidates);
                displayCandidates(allCandidates);

                showSuccessMessage('CVs processed successfully!');
                
            } catch (error) {
                console.error('Detailed error:', {
                    message: error.message,
                    stack: error.stack,
                    uploadedFiles: uploadedFiles.length,
                    processingId: currentProcessingId
                });
                showErrorMessage('Error processing CVs: ' + error.message);
            } finally {
                document.getElementById('loadingAnimation').style.display = 'none';
                document.getElementById('processBtn').disabled = false;
            }
        }

        function getRequiredSkills() {
            const skillTags = document.querySelectorAll('.skill-tag');
            const skills = [];
            
            skillTags.forEach(tag => {
                // Skip the "Add Skill" button
                if (tag.classList.contains('add-skill')) return;
                
                // Get text content and clean it
                let skillText = tag.textContent || tag.innerText;
                // Remove the × icon text
                skillText = skillText.replace(/×/g, '').trim();
                
                if (skillText && skillText.length > 0) {
                    skills.push(skillText);
                }
            });
            
            console.log('Extracted skills:', skills); // Debug log
            return skills.join(',');
        }

        function displayCandidates(candidates) {
            console.log('Displaying candidates:', candidates);
            const filteredCandidatesList = document.getElementById('filteredCandidatesList');
            const candidateCount = document.getElementById('candidateCount');
            
            if (!filteredCandidatesList || !candidateCount) {
                console.error('Required elements not found');
                return;
            }
            
            // Check if candidates is actually an array
            if (!Array.isArray(candidates)) {
                console.error('Candidates is not an array:', candidates);
                candidateCount.textContent = '(0)';
                filteredCandidatesList.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-user-slash empty-icon"></i>
                        <div class="empty-text">Invalid candidate data</div>
                    </div>
                `;
                return;
            }
            
            // Update candidate count
            candidateCount.textContent = `(${candidates.length})`;
            
            if (candidates.length === 0) {
                console.log('No candidates to display');
                filteredCandidatesList.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-user-slash empty-icon"></i>
                        <div class="empty-text">No candidates found</div>
                    </div>
                `;
                return;
            }

            filteredCandidatesList.innerHTML = '';
            candidates.forEach(candidate => {
                const candidateCard = createCandidateCard(candidate);
                filteredCandidatesList.appendChild(candidateCard);
            });
        }

        function createCandidateCard(candidate) {
            const card = document.createElement('div');
            card.className = 'candidate-card';
            card.dataset.contactId = candidate.ContactID || candidate.contactId;
            
            const skills = candidate.Skills || candidate.skills || '';
            const skillsArray = Array.isArray(skills) ? skills : (typeof skills === 'string' ? skills.split(',').map(s => s.trim()).filter(s => s) : []);
            
            // Use the extracted/generated candidate name
            const candidateName = candidate.CandidateName || candidate.name || 'Unknown Candidate';
            
            card.innerHTML = `
                <div class="candidate-header">
                    <div class="candidate-name">${candidateName}</div>
                </div>
                <div class="candidate-details">
                    <div class="candidate-detail">
                        <i class="fas fa-envelope"></i>
                        <span>${candidate.Email || candidate.email || 'N/A'}</span>
                    </div>
                    <div class="candidate-detail">
                        <i class="fas fa-phone"></i>
                        <span>${candidate.Phone || candidate.phone || 'N/A'}</span>
                    </div>
                    <div class="candidate-detail">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>${candidate.Location || candidate.location || 'N/A'}</span>
                    </div>
                    <div class="candidate-detail">
                        <i class="fas fa-briefcase"></i>
                        <span>${candidate.ExperienceYears || candidate.experienceYears || 0} years</span>
                    </div>
                </div>
                <div class="candidate-skills">
                    <div class="skills-label">Skills:</div>
                    <div class="skills-tags">
                        ${skillsArray.map(skill => `<span class="skill-tag">${skill}</span>`).join('')}
                    </div>
                </div>
            `;

            // Add click handler to show contact info
            card.addEventListener('click', () => showCandidateContactInfo(candidate));

            return card;
        }

        function showCandidateContactInfo(candidate) {
            const contactInfoContainer = document.getElementById('candidateContactInfo');
            
            if (!contactInfoContainer) {
                console.error('Contact info container not found');
                return;
            }

            const skills = candidate.Skills || candidate.skills || '';
            const skillsArray = Array.isArray(skills) ? skills : (typeof skills === 'string' ? skills.split(',').map(s => s.trim()).filter(s => s) : []);
            
            // Use the extracted/generated candidate name
            const candidateName = candidate.CandidateName || candidate.name || 'Unknown Candidate';
            
            contactInfoContainer.innerHTML = `
                <div class="contact-info-card">
                    <div class="contact-header">
                        <h4 class="contact-name">${candidateName}</h4>
                        <div class="contact-status">Contact Information</div>
                    </div>
                    
                    <div class="contact-details-grid">
                        <div class="contact-detail">
                            <div class="contact-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="contact-content">
                                <div class="contact-label">Email</div>
                                <div class="contact-value">${candidate.Email || candidate.email || 'Not provided'}</div>
                            </div>
                        </div>
                        
                        <div class="contact-detail">
                            <div class="contact-icon">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div class="contact-content">
                                <div class="contact-label">Phone</div>
                                <div class="contact-value">${candidate.Phone || candidate.phone || 'Not provided'}</div>
                            </div>
                        </div>
                        
                        <div class="contact-detail">
                            <div class="contact-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div class="contact-content">
                                <div class="contact-label">Location</div>
                                <div class="contact-value">${candidate.Location || candidate.location || 'Not provided'}</div>
                            </div>
                        </div>
                        
                        <div class="contact-detail">
                            <div class="contact-icon">
                                <i class="fas fa-briefcase"></i>
                            </div>
                            <div class="contact-content">
                                <div class="contact-label">Experience</div>
                                <div class="contact-value">${candidate.ExperienceYears || candidate.experienceYears || 0} years</div>
                            </div>
                        </div>
                        
                        <div class="contact-detail">
                            <div class="contact-icon">
                                <i class="fas fa-file-pdf"></i>
                            </div>
                            <div class="contact-content">
                                <div class="contact-label">CV File</div>
                                <div class="contact-value">${candidate.OriginalFileName || 'Unknown'}</div>
                            </div>
                        </div>
                        
                        ${candidate.LinkedIn || candidate.linkedin ? `
                        <div class="contact-detail">
                            <div class="contact-icon">
                                <i class="fab fa-linkedin"></i>
                            </div>
                            <div class="contact-content">
                                <div class="contact-label">LinkedIn</div>
                                <div class="contact-value">
                                    <a href="${candidate.LinkedIn || candidate.linkedin}" target="_blank" class="linkedin-link">
                                        View Profile
                                    </a>
                                </div>
                            </div>
                        </div>
                        ` : ''}
                    </div>
                    
                    ${candidate.Summary || candidate.summary ? `
                    <div class="contact-summary">
                        <div class="skills-header">Summary</div>
                        <div class="summary-text">${candidate.Summary || candidate.summary}</div>
                    </div>
                    ` : ''}
                    
                    <div class="contact-skills">
                        <div class="skills-header">Skills & Expertise</div>
                        <div class="skills-container">
                            ${skillsArray.length > 0 ? 
                                skillsArray.map(skill => `<span class="skill-tag">${skill}</span>`).join('') : 
                                '<span class="no-skills">No skills listed</span>'
                            }
                        </div>
                    </div>
                </div>
            `;
        }

        async function applyFilters() {
            if (!currentProcessingId) {
                showErrorMessage('Please process CVs first');
                return;
            }

            try {
                const url = `cv_processing_handler.php?action=get_candidates&processingId=${currentProcessingId}`;

                const response = await fetch(url, {
                    method: 'GET'
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const responseText = await response.text();
                console.log('Get candidates response:', responseText);
                
                let result;
                try {
                    result = JSON.parse(responseText);
                } catch (parseError) {
                    console.error('JSON parse error:', parseError);
                    console.error('Response text:', responseText);
                    throw new Error('Invalid JSON response from server');
                }

                if (result.success) {
                    allCandidates = result.candidates;
                    displayCandidates(allCandidates);
                    
                    // Show filtering information
                    const totalCandidates = result.totalCandidates || 0;
                    const filteredCandidates = result.filteredCandidates || allCandidates.length;
                    
                    if (totalCandidates > 0) {
                        showSuccessMessage(`Filtered ${filteredCandidates} out of ${totalCandidates} candidates based on requirements!`);
                    } else {
                        showSuccessMessage('Candidates refreshed successfully!');
                    }
                } else {
                    showErrorMessage(result.message);
                }
            } catch (error) {
                console.error('Error getting candidates:', error);
                showErrorMessage('Error refreshing candidates');
            }
        }

        // Debug connection function
        async function debugConnection() {
            try {
                const formData = new FormData();
                formData.append('action', 'debug');

                const response = await fetch('cv_processing_handler.php', {
                    method: 'POST',
                    body: formData
                });

                const responseText = await response.text();
                console.log('Debug response:', responseText);
                
                if (response.ok) {
                    try {
                        const result = JSON.parse(responseText);
                        console.log('Debug result:', result);
                        showSuccessMessage('Debug successful - check console for details');
                    } catch (parseError) {
                        console.error('JSON parse error:', parseError);
                        showErrorMessage('Debug failed - invalid JSON response');
                    }
                } else {
                    console.error('HTTP error:', response.status, responseText);
                    showErrorMessage(`Debug failed - HTTP ${response.status}`);
                }
            } catch (error) {
                console.error('Debug error:', error);
                showErrorMessage('Debug failed: ' + error.message);
            }
        }

        // Initialize theme when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            initializeTheme();
            setupThemeToggle();
            setupCompanyProfileEditing();
            loadCompanyProfile();
            initializeCVProcessing();
        });
    </script>
</body>
</html>

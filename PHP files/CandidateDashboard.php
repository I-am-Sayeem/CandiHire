<?php
// CandidateDashboard.php - Dashboard for candidates
require_once 'session_manager.php';

// Check if candidate is logged in
if (!isCandidateLoggedIn()) {
    header('Location: Login&Signup.php');
    exit;
}

// Get candidate ID from session
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
            --accent: #58a6ff; /* Candi color */
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
            --accent: #0969da;
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
        .fa-spin,
        .warning-icon,
        .warning-icon i {
            transition: none !important;
        }
        
        /* Loading posts indicator */
        .loading-posts {
            text-align: center;
            padding: 2rem;
            color: var(--text-secondary);
            font-size: 1.1rem;
        }
        
        .loading-posts i {
            margin-right: 0.5rem;
            color: var(--accent);
        }

        /* Enhanced Card Animations */
        .job-card, .candidate-card, .my-post-item {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            transform: translateZ(0);
            will-change: transform, box-shadow;
        }

        .job-card:hover, .candidate-card:hover, .my-post-item:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 
                0 20px 40px rgba(0, 0, 0, 0.1),
                0 0 0 1px rgba(88, 166, 255, 0.1);
        }

        /* Smooth Button Animations */
        .btn, .job-seeking-btn, .contact-btn, .view-profile-btn {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            transform: translateZ(0);
            will-change: transform, box-shadow, background;
        }

        .btn:hover, .job-seeking-btn:hover, .contact-btn:hover, .view-profile-btn:hover {
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
            color: var(--accent);
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
            transition: all 0.3s ease;
            font-size: 15px;
            will-change: transform, background-color;
        }

        .nav-item:hover {
            transform: translateX(4px);
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
            max-width: 680px;
            margin: 0 auto;
        }

        .post-creator {
            background-color: var(--bg-secondary);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
            border: 1px solid var(--border);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .post-creator textarea {
            width: 100%;
            background-color: var(--bg-primary);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 14px;
            color: var(--text-primary);
            resize: none;
            margin-bottom: 16px;
            font-size: 15px;
            min-height: 100px;
            transition: border-color 0.2s;
        }

        .post-creator textarea:focus {
            outline: none;
            border-color: var(--accent);
        }

        .post-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .post-options {
            display: flex;
            gap: 24px;
        }

        .post-option {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--text-secondary);
            cursor: pointer;
            font-size: 14px;
            transition: color 0.2s;
        }

        .post-option:hover {
            color: var(--accent);
        }

        .post-btn {
            background-color: var(--accent);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 10px 24px;
            cursor: pointer;
            font-weight: 600;
            font-size: 15px;
            transition: all 0.2s;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .post-btn:hover {
            background-color: var(--accent-hover);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        /* Job Seeking Styles */
        .job-seeking-intro {
            margin-bottom: 20px;
            text-align: center;
        }

        .job-seeking-intro h3 {
            color: var(--accent);
            margin-bottom: 8px;
            font-size: 18px;
        }

        .job-seeking-intro p {
            color: var(--text-secondary);
            font-size: 14px;
            line-height: 1.5;
        }

        .job-seeking-btn {
            background: linear-gradient(135deg, var(--accent-2), #e67e22);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 15px 30px;
            cursor: pointer;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
            display: flex;
            align-items: center;
            gap: 10px;
            justify-content: center;
            width: 100%;
        }

        .job-seeking-btn:hover {
            background: linear-gradient(135deg, #e67e22, #d35400);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(245, 158, 11, 0.4);
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
            font-size: 16px;
            cursor: pointer;
            padding: 4px;
            border-radius: 50%;
            transition: all 0.2s;
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .popup-close:hover {
            background: var(--bg-tertiary);
            color: var(--text-primary);
            transform: scale(1.05);
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

        .btn-danger {
            background: var(--danger);
            color: white;
        }

        .btn-danger:hover {
            background: var(--danger-hover);
            transform: translateY(-1px);
        }

        /* Professional Delete Confirmation Modal */
        .delete-confirmation-modal {
            z-index: 10000;
            backdrop-filter: blur(8px);
            background: rgba(0, 0, 0, 0.6);
            animation: fadeInBackdrop 0.3s ease-out;
        }

        .delete-confirmation-popup {
            background: var(--bg-primary);
            border: 1px solid var(--border);
            border-radius: 20px;
            box-shadow: 
                0 25px 50px -12px rgba(0, 0, 0, 0.25),
                0 0 0 1px rgba(255, 255, 255, 0.05);
            animation: slideInScale 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            transform-origin: center;
            overflow: hidden;
            position: relative;
        }

        .delete-confirmation-popup::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--danger), #ff6b6b, var(--danger));
            animation: shimmer 2s infinite;
        }

        .delete-confirmation-body {
            text-align: center;
            padding: 40px 30px 30px;
            position: relative;
        }

        .warning-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, var(--danger), #ff4757);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            font-size: 40px;
            color: white;
            position: relative;
            animation: dangerPulse 2s infinite;
            box-shadow: 
                0 0 0 0 rgba(248, 81, 73, 0.4),
                0 10px 30px rgba(248, 81, 73, 0.3);
        }

        .warning-icon::before {
            content: '';
            position: absolute;
            top: -10px;
            left: -10px;
            right: -10px;
            bottom: -10px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--danger), #ff4757);
            opacity: 0.2;
            animation: ripple 2s infinite;
        }

        .warning-icon i {
            position: relative;
            z-index: 1;
            animation: shake 0.5s ease-in-out infinite alternate;
        }

        .delete-confirmation-body h3 {
            color: var(--text-primary);
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 16px;
            letter-spacing: -0.5px;
        }

        .delete-confirmation-body p {
            color: var(--text-secondary);
            font-size: 16px;
            line-height: 1.7;
            margin-bottom: 24px;
            max-width: 400px;
            margin-left: auto;
            margin-right: auto;
        }

        .warning-text {
            background: linear-gradient(135deg, rgba(248, 81, 73, 0.1), rgba(255, 71, 87, 0.05));
            border: 1px solid rgba(248, 81, 73, 0.2);
            border-radius: 12px;
            padding: 18px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--danger);
            font-size: 14px;
            margin-top: 24px;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }

        .warning-text:hover {
            background: linear-gradient(135deg, rgba(248, 81, 73, 0.15), rgba(255, 71, 87, 0.1));
            border-color: rgba(248, 81, 73, 0.3);
            transform: translateY(-1px);
        }

        .warning-text i {
            font-size: 18px;
            flex-shrink: 0;
            animation: bounce 2s infinite;
        }

        /* Enhanced Button Styles */
        .form-actions {
            padding: 0 30px 30px;
            display: flex;
            gap: 12px;
            justify-content: center;
        }

        .btn {
            min-width: 140px;
            padding: 14px 24px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 15px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn-danger {
            background: linear-gradient(135deg, var(--danger), #ff4757);
            color: white;
            border: none;
            box-shadow: 0 4px 15px rgba(248, 81, 73, 0.4);
        }

        .btn-danger:hover {
            background: linear-gradient(135deg, #ff4757, var(--danger));
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(248, 81, 73, 0.6);
        }

        .btn-danger:active {
            transform: translateY(0);
            box-shadow: 0 4px 15px rgba(248, 81, 73, 0.4);
        }

        .btn-secondary {
            background: var(--bg-tertiary);
            color: var(--text-primary);
            border: 1px solid var(--border);
        }

        .btn-secondary:hover {
            background: var(--bg-secondary);
            border-color: var(--accent);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        /* Enhanced Animations */
        @keyframes fadeInBackdrop {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideInScale {
            0% {
                opacity: 0;
                transform: scale(0.8) translateY(-30px);
            }
            100% {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        @keyframes dangerPulse {
            0%, 100% {
                transform: scale(1);
                box-shadow: 
                    0 0 0 0 rgba(248, 81, 73, 0.4),
                    0 10px 30px rgba(248, 81, 73, 0.3);
            }
            50% {
                transform: scale(1.05);
                box-shadow: 
                    0 0 0 10px rgba(248, 81, 73, 0),
                    0 15px 40px rgba(248, 81, 73, 0.4);
            }
        }

        @keyframes ripple {
            0% {
                transform: scale(1);
                opacity: 0.2;
            }
            100% {
                transform: scale(1.3);
                opacity: 0;
            }
        }

        @keyframes shake {
            0% { transform: translateX(0); }
            100% { transform: translateX(2px); }
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-3px);
            }
            60% {
                transform: translateY(-2px);
            }
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        @keyframes fadeOutBackdrop {
            from { opacity: 1; }
            to { opacity: 0; }
        }

        @keyframes slideOutScale {
            0% {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
            100% {
                opacity: 0;
                transform: scale(0.8) translateY(-30px);
            }
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

        /* Company Details Popup Styles */
        .company-header {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
            padding: 20px;
            background: var(--bg-tertiary);
            border-radius: 12px;
            border: 1px solid var(--border);
        }

        .company-logo {
            width: 80px;
            height: 80px;
            border-radius: 12px;
            background: var(--accent);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            font-weight: bold;
            color: white;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            flex-shrink: 0;
        }

        .company-info h2 {
            color: var(--accent);
            margin: 0 0 8px 0;
            font-size: 28px;
            font-weight: 700;
        }

        .company-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 10px;
        }

        .company-meta-item {
            display: flex;
            align-items: center;
            gap: 6px;
            color: var(--text-secondary);
            font-size: 14px;
        }

        .company-meta-item i {
            color: var(--accent);
            width: 16px;
        }

        .company-description {
            background: var(--bg-primary);
            padding: 20px;
            border-radius: 8px;
            border: 1px solid var(--border);
            margin-bottom: 25px;
            line-height: 1.6;
            color: var(--text-primary);
        }

        .company-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }

        .stat-card {
            background: var(--bg-primary);
            padding: 20px;
            border-radius: 8px;
            border: 1px solid var(--border);
            text-align: center;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: var(--accent);
            margin-bottom: 5px;
        }

        .stat-label {
            color: var(--text-secondary);
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .company-section {
            margin-bottom: 25px;
        }

        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--accent);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .contact-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
        }

        .contact-item {
            background: var(--bg-primary);
            padding: 15px;
            border-radius: 8px;
            border: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .contact-item i {
            color: var(--accent);
            font-size: 18px;
            width: 20px;
        }

        .contact-item-content {
            flex: 1;
        }

        .contact-label {
            font-size: 12px;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }

        .contact-value {
            color: var(--text-primary);
            font-weight: 500;
        }

        .contact-value a {
            color: var(--accent);
            text-decoration: none;
        }

        .contact-value a:hover {
            text-decoration: underline;
        }

        .recent-jobs {
            background: var(--bg-primary);
            border-radius: 8px;
            border: 1px solid var(--border);
            overflow: hidden;
        }

        .job-item {
            padding: 15px 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background-color 0.2s ease;
        }

        .job-item:last-child {
            border-bottom: none;
        }

        .job-item:hover {
            background: var(--bg-secondary);
        }

        .job-info h4 {
            color: var(--accent);
            margin: 0 0 5px 0;
            font-size: 16px;
        }

        .job-meta {
            display: flex;
            gap: 15px;
            color: var(--text-secondary);
            font-size: 13px;
        }

        .job-stats {
            text-align: right;
            color: var(--text-secondary);
            font-size: 12px;
        }

        .job-applications {
            color: var(--accent);
            font-weight: 600;
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

            .company-header {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }

            .company-info h2 {
                font-size: 24px;
            }

            .company-meta {
                justify-content: center;
            }

            .company-stats {
                grid-template-columns: repeat(2, 1fr);
            }

            .contact-info {
                grid-template-columns: 1fr;
            }

            .job-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .job-meta {
                flex-wrap: wrap;
            }
        }

        /* Posts */
        .post {
            background-color: var(--bg-secondary);
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
            border: 1px solid var(--border);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            will-change: transform, box-shadow;
        }

        /* Job Posting Styles */
        .job-posting {
            margin-top: 15px;
        }

        .job-title {
            font-size: 20px;
            font-weight: 600;
            color: var(--accent);
            margin-bottom: 8px;
        }

        .job-company {
            color: var(--accent-2);
            font-weight: 500;
            margin-bottom: 15px;
            font-size: 16px;
        }

        .job-posting p {
            margin-bottom: 12px;
            line-height: 1.6;
        }

        .job-posting strong {
            color: var(--text-primary);
            font-weight: 600;
        }

        .job-skills {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 15px;
        }

        .skill-tag {
            background: linear-gradient(135deg, var(--accent), var(--accent-2));
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .post:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        .post-header {
            display: flex;
            align-items: center;
            margin-bottom: 16px;
        }

        .post-avatar {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background-color: var(--accent);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 14px;
            font-weight: bold;
            font-size: 20px;
            color: white;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        .post-info h3 {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 2px;
        }

        .post-info p {
            color: var(--text-secondary);
            font-size: 13px;
        }

        .post-content {
            margin-bottom: 16px;
            font-size: 15px;
        }

        .job-posting {
            background-color: var(--bg-primary);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 18px;
            margin-top: 14px;
        }

        .job-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--accent);
        }

        .job-company {
            margin-bottom: 14px;
            color: var(--text-secondary);
            font-size: 15px;
        }

        .job-skills {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 18px;
        }

        .skill-tag {
            background-color: var(--bg-tertiary);
            padding: 6px 14px;
            border-radius: 16px;
            font-size: 13px;
            border: 1px solid var(--border);
            transition: all 0.3s ease;
            will-change: transform, background-color, border-color;
        }

        .skill-tag:hover {
            background-color: var(--accent);
            color: white;
            border-color: var(--accent);
            transform: translateY(-2px) scale(1.05);
        }

        .apply-btn {
            background-color: var(--success);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            cursor: pointer;
            font-weight: 600;
            font-size: 15px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            will-change: transform, box-shadow;
        }

        .apply-btn:hover {
            background-color: #2ea043;
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
        }

        .post-stats {
            display: flex;
            justify-content: space-between;
            padding: 14px 0;
            border-top: 1px solid var(--border);
            margin-top: 16px;
        }

        .post-actions-btn {
            display: flex;
            gap: 28px;
            align-items: center;
        }

        .action-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--text-secondary);
            cursor: pointer;
            transition: color 0.2s;
            font-size: 14px;
            padding: 4px 0;
        }

        .action-btn:hover {
            color: var(--accent);
        }

        .action-btn.liked {
            color: var(--danger);
        }

        .action-btn.report-btn {
            color: var(--warning);
        }

        .action-btn.report-btn:hover {
            color: #ff6b35;
            transform: translateY(-1px);
        }

        .post-stats-right {
            margin-left: auto;
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

        .skill-popularity {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .popularity-bar {
            width: 90px;
            height: 8px;
            background-color: var(--bg-tertiary);
            border-radius: 4px;
            overflow: hidden;
        }

        .popularity-fill {
            height: 100%;
            background-color: var(--accent);
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
                    <div id="candidateAvatar" style="width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 16px; <?php echo $candidateProfilePicture ? 'background-image: url(' . $candidateProfilePicture . '); background-size: cover; background-position: center;' : 'background: linear-gradient(135deg, var(--accent), var(--accent-2));'; ?>">
                        <?php echo $candidateProfilePicture ? '' : strtoupper(substr($candidateName, 0, 1)); ?>
                    </div>
                    <div>
                        <div style="color: var(--text-primary); font-weight: 600; font-size: 14px;">Welcome back!</div>
                        <div id="candidateNameDisplay" style="color: var(--text-secondary); font-size: 12px;"><?php echo htmlspecialchars($candidateName); ?></div>
                    </div>
                </div>
                <button id="editProfileBtn" 
    style="background: var(--accent); 
           color: white; 
           border: none; 
           border-radius: 6px; 
           padding: 8px 12px;
           font-size: 12px; 
           cursor: pointer; 
           margin-top: 10px; 
           width: 100%; 
           transition: background 0.2s;">
    <i class="fas fa-user-edit" style="margin-right: 6px;"></i>Edit Profile
</button>
            </div>
            
            <!-- Main Menu Section -->
            <div class="nav-section">
                <div class="nav-section-title">Main menu</div>
                <div class="nav-item active" data-href="CandidateDashboard.php">
                    <i class="fas fa-home"></i>
                    <span>News feed</span>
                </div>
                <div class="nav-item" data-href="CvBuilder.php">
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
                    <button id="refreshPostsBtn" class="btn btn-secondary" style="margin-left: auto;" onclick="refreshPosts()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                    <div class="search-box" style="flex: 1; position: relative;">
                        <i class="fas fa-search" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--text-secondary);"></i>
                        <input type="text" id="jobSearchInput" placeholder="Search by job title, company, or skills..." style="width: 100%; padding: 12px 15px 12px 45px; border: 1px solid var(--border); border-radius: 8px; background: var(--bg-primary); color: var(--text-primary); font-size: 14px;">
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
                            <label style="display: block; margin-bottom: 5px; color: var(--text-primary); font-weight: 600;">Company:</label>
                            <input type="text" id="companyFilter" placeholder="e.g., Google, Microsoft" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border); border-radius: 6px; background: var(--bg-primary); color: var(--text-primary);">
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 5px; color: var(--text-primary); font-weight: 600;">Location:</label>
                            <input type="text" id="locationFilter" placeholder="e.g., Remote, New York" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border); border-radius: 6px; background: var(--bg-primary); color: var(--text-primary);">
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 5px; color: var(--text-primary); font-weight: 600;">Job Type:</label>
                            <select id="jobTypeFilter" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border); border-radius: 6px; background: var(--bg-primary); color: var(--text-primary);">
                                <option value="">All Types</option>
                                <option value="full-time">Full Time</option>
                                <option value="part-time">Part Time</option>
                                <option value="contract">Contract</option>
                                <option value="freelance">Freelance</option>
                                <option value="internship">Internship</option>
                            </select>
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 5px; color: var(--text-primary); font-weight: 600;">Experience Level:</label>
                            <select id="experienceFilter" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border); border-radius: 6px; background: var(--bg-primary); color: var(--text-primary);">
                                <option value="">All Levels</option>
                                <option value="entry">Entry Level</option>
                                <option value="mid">Mid Level</option>
                                <option value="senior">Senior Level</option>
                                <option value="lead">Lead</option>
                                <option value="executive">Executive</option>
                            </select>
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 5px; color: var(--text-primary); font-weight: 600;">Skills:</label>
                            <input type="text" id="skillsFilter" placeholder="e.g., React, Python, SQL" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border); border-radius: 6px; background: var(--bg-primary); color: var(--text-primary);">
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 5px; color: var(--text-primary); font-weight: 600;">Salary Range:</label>
                            <select id="salaryFilter" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border); border-radius: 6px; background: var(--bg-primary); color: var(--text-primary);">
                                <option value="">Any Salary</option>
                                <option value="0-50000">$0 - $50,000</option>
                                <option value="50000-80000">$50,000 - $80,000</option>
                                <option value="80000-120000">$80,000 - $120,000</option>
                                <option value="120000-200000">$120,000 - $200,000</option>
                                <option value="200000+">$200,000+</option>
                            </select>
                        </div>
                    </div>
                    <div style="margin-top: 15px; text-align: right;">
                        <button id="applyFiltersBtn" style="background: var(--accent); color: white; border: none; border-radius: 6px; padding: 10px 20px; cursor: pointer; font-weight: 600;">
                            Apply Filters
                        </button>
                    </div>
                </div>
            </div>

            <!-- Job Seeking Creator -->
            <div class="post-creator">
                <div class="job-seeking-intro">
                <h3>Job Opportunities</h3>
                <p>Browse job postings from companies and apply to positions that match your skills and interests.</p>
                </div>
                <div class="post-actions">
                    <button class="job-seeking-btn" id="createJobSeekingPost">
                        <i class="fas fa-user-plus"></i>
                        Create Job Seeking Profile
                    </button>
                    <button class="job-seeking-btn" id="viewMyPosts" style="background: linear-gradient(135deg, #28a745, #20c997);">
                        <i class="fas fa-eye"></i>
                        View My Posts
                    </button>
                </div>
            </div>

            <!-- Posts will be dynamically loaded here -->
                    </div>

        <!-- Job Seeking Post Popup -->
        <div id="jobSeekingPopup" class="popup-overlay" style="display: none;">
            <div class="popup-content">
                <div class="popup-header">
                    <div class="popup-title">
                        <i class="fas fa-briefcase"></i>
                        Create Job Seeking Post
                </div>
                    <button class="popup-close" onclick="closeJobSeekingPopup()">
                        <i class="fas fa-times"></i>
                    </button>
                        </div>
                
                <form id="jobSeekingForm">
                    <div class="form-group">
                        <label for="jobTitle">Job Title *</label>
                        <input type="text" id="jobTitle" name="jobTitle" placeholder="e.g., Software Engineer, Web Developer, Data Analyst" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="careerGoal">Career Goal / Objective *</label>
                        <textarea id="careerGoal" name="careerGoal" placeholder="Why are you applying? What do you want to achieve?" required></textarea>
                </div>
                    
                    <div class="form-group">
                        <label for="keySkills">Key Skills *</label>
                        <textarea id="keySkills" name="keySkills" placeholder="e.g., programming, database management, problem-solving" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="experience">Experience / Projects</label>
                        <textarea id="experience" name="experience" placeholder="Any relevant background? (if fresher, mention academic projects or internships)"></textarea>
                </div>
                    
                    <div class="form-group">
                        <label for="education">Education *</label>
                        <input type="text" id="education" name="education" placeholder="e.g., B.Sc. in Computer Science and Engineering" required>
            </div>

                    <div class="form-group">
                        <label for="softSkills">Soft Skills / Personal Traits</label>
                        <textarea id="softSkills" name="softSkills" placeholder="Teamwork, communication, adaptability, eagerness to learn"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="valueToEmployer">Value to Employer</label>
                        <textarea id="valueToEmployer" name="valueToEmployer" placeholder="How you will contribute to the company (e.g., help build scalable applications, improve efficiency)"></textarea>
                </div>
                    
                    <div class="form-group">
                        <label for="contactInfo">Contact Information *</label>
                        <textarea id="contactInfo" name="contactInfo" placeholder="How can they reach you? (phone, email, LinkedIn)" required></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeJobSeekingPopup()">
                            <i class="fas fa-times"></i>
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-primary" id="submitJobSeekingPost">
                            <i class="fas fa-paper-plane"></i>
                            Create Post
                        </button>
                </div>
                </form>
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

        <!-- Edit Job Seeking Post Popup -->
        <div id="editJobSeekingPopup" class="popup-overlay" style="display: none;">
            <div class="popup-content">
                <div class="popup-header">
                    <div class="popup-title">
                        <i class="fas fa-edit"></i>
                        Edit Job Seeking Post
                    </div>
                    <button class="popup-close" onclick="closeEditJobSeekingPopup()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <form id="editJobSeekingForm">
                    <input type="hidden" id="editPostId" name="postId">
                    <div class="form-group">
                        <label for="editJobTitle">Job Title *</label>
                        <input type="text" id="editJobTitle" name="jobTitle" placeholder="e.g., Software Engineer, Web Developer, Data Analyst" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="editCareerGoal">Career Goal / Objective *</label>
                        <textarea id="editCareerGoal" name="careerGoal" placeholder="Why are you applying? What do you want to achieve?" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="editKeySkills">Key Skills *</label>
                        <textarea id="editKeySkills" name="keySkills" placeholder="e.g., programming, database management, problem-solving" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="editExperience">Experience / Projects</label>
                        <textarea id="editExperience" name="experience" placeholder="Any relevant background? (if fresher, mention academic projects or internships)"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="editEducation">Education *</label>
                        <input type="text" id="editEducation" name="education" placeholder="e.g., B.Sc. in Computer Science and Engineering" required>
                    </div>

                    <div class="form-group">
                        <label for="editSoftSkills">Soft Skills / Personal Traits</label>
                        <textarea id="editSoftSkills" name="softSkills" placeholder="Teamwork, communication, adaptability, eagerness to learn"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="editValueToEmployer">Value to Employer</label>
                        <textarea id="editValueToEmployer" name="valueToEmployer" placeholder="How you will contribute to the company (e.g., help build scalable applications, improve efficiency)"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="editContactInfo">Contact Information *</label>
                        <textarea id="editContactInfo" name="contactInfo" placeholder="How can they reach you? (phone, email, LinkedIn)" required></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeEditJobSeekingPopup()">
                            <i class="fas fa-times"></i>
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-primary" id="submitEditJobSeekingPost">
                            <i class="fas fa-save"></i>
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Job Application Popup -->
        <div id="jobApplicationPopup" class="popup-overlay" style="display: none;">
            <div class="popup-content" style="max-width: 600px;">
                <div class="popup-header">
                    <div class="popup-title">
                        <i class="fas fa-paper-plane"></i>
                        Apply for Job
                    </div>
                    <button class="popup-close" onclick="closeJobApplicationPopup()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <form id="jobApplicationForm">
                    <input type="hidden" id="applicationJobId" name="jobId">
                    
                    <div class="form-group">
                        <label for="applicationCoverLetter">Cover Letter *</label>
                        <textarea id="applicationCoverLetter" name="coverLetter" placeholder="Tell the employer why you're the right fit for this position..." required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="applicationNotes">Additional Notes</label>
                        <textarea id="applicationNotes" name="additionalNotes" placeholder="Any additional information you'd like to share..."></textarea>
                    </div>
                    
                    <div class="application-info" style="background: var(--bg-tertiary); padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid var(--border);">
                        <h4 style="color: var(--accent); margin-bottom: 10px; display: flex; align-items: center; gap: 8px;">
                            <i class="fas fa-info-circle"></i>
                            What happens next?
                        </h4>
                        <ul style="margin: 0; padding-left: 20px; color: var(--text-secondary); line-height: 1.6;">
                            <li>Your application will be reviewed by the company</li>
                            <li>If there are exams for this position, they will be automatically assigned to you</li>
                            <li>You can track your application status in the "Application Status" section</li>
                            <li>You'll be notified of any updates via email</li>
                        </ul>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeJobApplicationPopup()">
                            <i class="fas fa-times"></i>
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-primary" id="submitApplication">
                            <i class="fas fa-paper-plane"></i>
                            Submit Application
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Company Details Popup -->
        <div id="companyDetailsPopup" class="popup-overlay" style="display: none;">
            <div class="popup-content" style="max-width: 800px; max-height: 90vh; overflow-y: auto;">
                <div class="popup-header">
                    <div class="popup-title">
                        <i class="fas fa-building"></i>
                        <span id="companyDetailsTitle">Company Details</span>
                    </div>
                    <button class="popup-close" onclick="closeCompanyDetailsPopup()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <div id="companyDetailsContent">
                    <!-- Company details will be loaded here -->
                    <div class="loading-company" style="text-align: center; padding: 40px; color: var(--text-secondary);">
                        <i class="fas fa-spinner fa-spin" style="font-size: 24px; margin-bottom: 10px;"></i>
                        <div>Loading company details...</div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeCompanyDetailsPopup()">
                        <i class="fas fa-times"></i>
                        Close
                    </button>
                </div>
            </div>
        </div>

        <!-- Report Job Post Popup -->
        <div id="reportJobPopup" class="popup-overlay" style="display: none;">
            <div class="popup-content" style="max-width: 600px;">
                <div class="popup-header">
                    <div class="popup-title">
                        <i class="fas fa-flag"></i>
                        Report Job Post
                    </div>
                    <button class="popup-close" onclick="closeReportJobPopup()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <form id="reportJobForm">
                    <input type="hidden" id="reportJobId" name="jobId">
                    <input type="hidden" id="reportCompanyId" name="companyId">
                    
                    <div class="form-group">
                        <label for="reportJobTitle">Job Title</label>
                        <input type="text" id="reportJobTitle" name="jobTitle" readonly style="background: var(--bg-tertiary); color: var(--text-secondary);">
                    </div>
                    
                    <div class="form-group">
                        <label for="reportCompanyName">Company</label>
                        <input type="text" id="reportCompanyName" name="companyName" readonly style="background: var(--bg-tertiary); color: var(--text-secondary);">
                    </div>
                    
                    <div class="form-group">
                        <label for="reportReason">Reason for Report *</label>
                        <select id="reportReason" name="reason" required>
                            <option value="">Select a reason</option>
                            <option value="inappropriate_content">Inappropriate Content</option>
                            <option value="misleading_information">Misleading Information</option>
                            <option value="spam">Spam</option>
                            <option value="fake_job">Fake Job Posting</option>
                            <option value="discriminatory">Discriminatory Language</option>
                            <option value="scam">Potential Scam</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="reportDescription">Description *</label>
                        <textarea id="reportDescription" name="description" placeholder="Please provide details about why you're reporting this job post..." required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="reportContact">Your Contact (Optional)</label>
                        <input type="text" id="reportContact" name="contact" placeholder="Email or phone number for follow-up">
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeReportJobPopup()">
                            <i class="fas fa-times"></i>
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-danger" id="submitReport">
                            <i class="fas fa-flag"></i>
                            Submit Report
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
    </div>

    <?php include 'message_button_helper.php'; ?>
    <?php include 'message_popup.php'; ?>
    
    <script>
        // Global variables
        let currentCandidateId = <?php echo json_encode($sessionCandidateId); ?>;
        window.currentUserId = currentCandidateId;
        window.currentUserType = 'candidate';
        let posts = [];
        let myPosts = []; // Store my job seeking posts
        let currentOffset = 0;
        const POSTS_PER_PAGE = 10;
        let isSubmittingJobSeekingPost = false; // Global flag to prevent double submission

        // Initialize dashboard
        function initializeDashboard() {
            console.log('Initializing dashboard...');
            
            // Get candidate ID from URL parameter first
            const urlParams = new URLSearchParams(window.location.search);
            currentCandidateId = urlParams.get('candidateId');
            
            console.log('Candidate ID from URL:', currentCandidateId);
            
            // If not in URL, get from PHP session (passed to JavaScript)
            if (!currentCandidateId) {
                currentCandidateId = <?php echo json_encode($sessionCandidateId); ?>;
                console.log('Candidate ID from session:', currentCandidateId);
                
                // Update URL to include candidate ID for consistency
                if (currentCandidateId && !urlParams.has('candidateId')) {
                    const newUrl = new URL(window.location);
                    newUrl.searchParams.set('candidateId', currentCandidateId);
                    window.history.replaceState({}, '', newUrl);
                    console.log('Updated URL with candidate ID');
                }
            }
            
            if (!currentCandidateId) {
                console.error('Candidate ID not found in URL or session');
                alert('Unable to identify candidate. Please login again.');
                window.location.href = 'Login&Signup.php';
                return;
            }
            
            // Load initial posts
            loadPosts();
            
            // Initialize messaging system
            messagingSystem.initialize(<?php echo json_encode($sessionCandidateId); ?>, 'candidate');
            
            // Setup job seeking post creation
            setupJobSeekingPostCreation();
            
            // Setup view my posts functionality
            setupViewMyPosts();
            
            
            console.log('Dashboard initialization complete');
        }


        // Load posts from server
        function loadPosts(reset = false) {
            if (reset) {
                currentOffset = 0;
                posts = [];
                
                // Show loading indicator
                const postsContainer = document.getElementById('postsContainer');
                if (postsContainer) {
                    postsContainer.innerHTML = '<div class="loading-posts"><i class="fas fa-spinner fa-spin"></i> Loading posts...</div>';
                }
            }
            
            // Build query parameters
            const params = new URLSearchParams();
            params.append('limit', POSTS_PER_PAGE);
            params.append('offset', currentOffset);
            
            if (currentSearchTerm) {
                params.append('search', currentSearchTerm);
            }
            
            if (currentFilters.company) {
                params.append('company', currentFilters.company);
            }
            
            if (currentFilters.location) {
                params.append('location', currentFilters.location);
            }
            
            if (currentFilters.jobType) {
                params.append('jobType', currentFilters.jobType);
            }
            
            if (currentFilters.experience) {
                params.append('experience', currentFilters.experience);
            }
            
            if (currentFilters.skills) {
                params.append('skills', currentFilters.skills);
            }
            
            if (currentFilters.salary) {
                params.append('salary', currentFilters.salary);
            }
            
            // Add cache busting parameter
            params.append('_t', Date.now());
            
            fetch(`company_job_posts_handler.php?${params.toString()}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log('Posts data received:', data.posts.map(p => ({JobID: p.JobID, ApplicationCount: p.ApplicationCount})));
                        if (reset) {
                            posts = data.posts;
                        } else {
                            posts = posts.concat(data.posts);
                        }
                        
                        // Check which jobs have been applied to
                        checkAppliedJobs().then(() => {
                            renderPosts();
                        });
                        currentOffset += POSTS_PER_PAGE;
                    } else {
                        console.error('Failed to load posts:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error loading posts:', error);
                });
        }

        // Render posts to the DOM
        function renderPosts() {
            const mainContent = document.querySelector('.main-content');
            const postCreator = document.querySelector('.post-creator');
            
            // Remove existing posts (keep post creator)
            const existingPosts = mainContent.querySelectorAll('.post');
            existingPosts.forEach(post => post.remove());
            
            // Render each post
            posts.forEach(post => {
                const postElement = createPostElement(post);
                mainContent.insertBefore(postElement, postCreator.nextSibling);
            });
        }

        // Create company job post element
        function createPostElement(post) {
            const postDiv = document.createElement('div');
            postDiv.className = 'post';
            postDiv.dataset.jobId = post.JobID;
            
            // Get initials from company name (fallback)
            const initials = post.CompanyName.split(' ').map(name => name[0]).join('').toUpperCase();
            
            // Create avatar HTML - use company logo if available, otherwise use initials
            let avatarHtml = '';
            if (post.Logo) {
                avatarHtml = `<div class="post-avatar" style="background-image: url('${post.Logo}'); background-size: cover; background-position: center; color: transparent;">${initials}</div>`;
            } else {
                avatarHtml = `<div class="post-avatar">${initials}</div>`;
            }
            
            // Format time
            const postTime = formatTimeAgo(post.PostedDate);
            
            // Get skills if available
            const skills = post.Skills ? post.Skills.split(',').slice(0, 5) : [];
            const skillsHtml = skills.map(skill => `<div class="skill-tag">${skill.trim()}</div>`).join('');
            
            // Format salary range
            let salaryHtml = '';
            if (post.SalaryMin && post.SalaryMax) {
                const currency = post.Currency || 'USD';
                const minSalary = new Intl.NumberFormat('en-US', { style: 'currency', currency: currency }).format(post.SalaryMin);
                const maxSalary = new Intl.NumberFormat('en-US', { style: 'currency', currency: currency }).format(post.SalaryMax);
                salaryHtml = `<p><strong>Salary Range:</strong> ${minSalary} - ${maxSalary}</p>`;
            }
            
            // Format job type
            const jobType = post.JobType ? post.JobType.replace('-', ' ').toUpperCase() : '';
            
            postDiv.innerHTML = `
                <div class="post-header">
                    ${avatarHtml}
                    <div class="post-info">
                        <h3>${post.CompanyName}</h3>
                        <p>Posted ${postTime}</p>
                    </div>
                </div>
                <div class="post-content">
                    <div class="job-posting">
                        <div class="job-title">${escapeHtml(post.JobTitle)}</div>
                        <div class="job-company">${post.Department ? escapeHtml(post.Department) : ''} • ${jobType}</div>
                        <p><strong>Location:</strong> ${escapeHtml(post.Location || 'Not specified')}</p>
                        ${post.JobDescription ? `<p><strong>Description:</strong> ${escapeHtml(post.JobDescription)}</p>` : ''}
                        ${post.Requirements ? `<p><strong>Requirements:</strong> ${escapeHtml(post.Requirements)}</p>` : ''}
                        ${post.Responsibilities ? `<p><strong>Responsibilities:</strong> ${escapeHtml(post.Responsibilities)}</p>` : ''}
                        ${salaryHtml}
                        ${skillsHtml ? `<div class="job-skills">${skillsHtml}</div>` : ''}
                    </div>
                </div>
                <div class="post-stats">
                    <div class="post-actions-btn">
                        <div class="action-btn ${post.isApplied ? 'applied' : ''}" onclick="applyToJob(${post.JobID})" style="${post.isApplied ? 'pointer-events: none;' : ''}">
                            <i class="fas ${post.isApplied ? 'fa-check' : 'fa-paper-plane'}"></i>
                            <span>${post.isApplied ? 'Applied' : 'Apply'}</span>
                        </div>
                        <div class="action-btn" onclick="openMessageDialog(${post.CompanyID}, ${post.CompanyID}, 'company', '${escapeHtml(post.CompanyName)}', '${post.CompanyLogo || ''}')" title="Message this company">
                            <i class="fas fa-comment"></i>
                            <span>Message</span>
                        </div>
                        <div class="action-btn" onclick="viewCompanyProfile(${post.CompanyID})">
                            <i class="fas fa-building"></i>
                            <span>Company</span>
                        </div>
                        <div class="action-btn report-btn" onclick="reportJobPost(${post.JobID}, '${escapeHtml(post.JobTitle)}', ${post.CompanyID}, '${escapeHtml(post.CompanyName)}')" title="Report this job post">
                            <i class="fas fa-flag"></i>
                            <span>Report</span>
                        </div>
                    </div>
                    <div class="post-stats-right">
                        <span class="views-count">${post.ApplicationCount || 0} applications</span>
                        <!-- Debug: ${JSON.stringify({JobID: post.JobID, ApplicationCount: post.ApplicationCount})} -->
                    </div>
                </div>
            `;
            
            return postDiv;
        }

        // Setup job seeking post creation
        function setupJobSeekingPostCreation() {
            console.log('Setting up job seeking post creation...');
            
            const jobSeekingBtn = document.getElementById('createJobSeekingPost');
            const popup = document.getElementById('jobSeekingPopup');
            const form = document.getElementById('jobSeekingForm');
            
            console.log('Elements found:', {
                button: !!jobSeekingBtn,
                popup: !!popup,
                form: !!form
            });
            
            if (!jobSeekingBtn || !popup || !form) {
                console.error('Required elements not found for job seeking post creation');
                return;
            }
            
            // Check if already initialized to prevent duplicate event listeners
            if (form.dataset.initialized === 'true') {
                console.log('Job seeking form already initialized, skipping...');
                return;
            }
            
            // Open popup when button is clicked
            jobSeekingBtn.addEventListener('click', function() {
                console.log('Job seeking button clicked');
                
                // Close any existing my posts modal first
                const existingModal = document.querySelector('.my-posts-modal');
                if (existingModal) {
                    existingModal.remove();
                    document.body.style.overflow = 'auto';
                }
                
                popup.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            });
            
            // Handle form submission
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Additional check before calling createJobSeekingPost
                if (isSubmittingJobSeekingPost) {
                    console.log('Form submission blocked - already processing');
                    return;
                }
                
                createJobSeekingPost();
            });
            
            // Close popup when clicking outside
            popup.addEventListener('click', function(e) {
                if (e.target === popup) {
                    closeJobSeekingPopup();
                }
            });
            
            // Mark as initialized to prevent duplicate setup
            form.dataset.initialized = 'true';
            
            console.log('Job seeking post creation setup complete');
        }

        // Setup view my posts functionality
        function setupViewMyPosts() {
            console.log('Setting up view my posts functionality...');
            
            const viewMyPostsBtn = document.getElementById('viewMyPosts');
            
            if (!viewMyPostsBtn) {
                console.error('View my posts button not found');
                return;
            }
            
            // Check if already initialized to prevent duplicate event listeners
            if (viewMyPostsBtn.dataset.initialized === 'true') {
                console.log('View my posts button already initialized, skipping...');
                return;
            }
            
            viewMyPostsBtn.addEventListener('click', function() {
                console.log('View my posts button clicked');
                
                // Prevent multiple rapid clicks
                if (viewMyPostsBtn.disabled) {
                    return;
                }
                
                // Show loading state
                const originalContent = viewMyPostsBtn.innerHTML;
                viewMyPostsBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
                viewMyPostsBtn.disabled = true;
                
                // Close job seeking popup if it's open
                const jobSeekingPopup = document.getElementById('jobSeekingPopup');
                if (jobSeekingPopup && jobSeekingPopup.style.display === 'flex') {
                    jobSeekingPopup.style.display = 'none';
                    document.body.style.overflow = 'auto';
                }
                
                // Add small delay to ensure proper cleanup
                setTimeout(() => {
                    loadMyPosts();
                    // Reset button after loading
                    setTimeout(() => {
                        viewMyPostsBtn.innerHTML = originalContent;
                        viewMyPostsBtn.disabled = false;
                    }, 500);
                }, 100);
            });
            
            // Mark as initialized to prevent duplicate setup
            viewMyPostsBtn.dataset.initialized = 'true';
            
            console.log('View my posts functionality setup complete');
        }


        // Load my posts (posts from current candidate)
        function loadMyPosts() {
            console.log('Loading my posts...');
            
            // Close any existing modal first to prevent duplicates
            closeMyPostsModal();
            
            fetch(`job_seeking_handler.php?candidateId=${currentCandidateId}&myPosts=true&limit=50&offset=0`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        myPosts = data.posts; // Store in global variable
                        renderMyPosts(data.posts);
                    } else {
                        console.error('Failed to load my posts:', data.message);
                        showErrorMessage('Failed to load your posts');
                    }
                })
                .catch(error => {
                    console.error('Error loading my posts:', error);
                    showErrorMessage('Network error loading your posts');
                });
        }

        // Render my posts in a modal or section
        function renderMyPosts(posts) {
            if (posts.length === 0) {
                showInfoMessage('You haven\'t created any job seeking posts yet.');
                return;
            }

            // Ensure any existing modal is removed first
            const existingModal = document.querySelector('.my-posts-modal');
            if (existingModal) {
                existingModal.remove();
            }

            // Create modal for my posts
            const modal = document.createElement('div');
            modal.className = 'popup-overlay my-posts-modal';
            modal.style.display = 'flex';
            modal.innerHTML = `
                <div class="popup-content" style="max-width: 800px;">
                    <div class="popup-header">
                        <div class="popup-title">
                            <i class="fas fa-user"></i>
                            My Job Seeking Posts (${posts.length})
                        </div>
                        <button class="popup-close" onclick="closeMyPostsModal()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div id="myPostsList" style="max-height: 400px; overflow-y: auto;">
                        ${posts.map(post => createMyPostElement(post)).join('')}
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeMyPostsModal()">
                            <i class="fas fa-times"></i>
                            Close
                        </button>
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
            document.body.style.overflow = 'hidden';
            
            // Add click outside to close functionality
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeMyPostsModal();
                }
            });
        }

        // Create element for my post
        function createMyPostElement(post) {
            const postTime = formatTimeAgo(post.CreatedAt);
            const skills = post.KeySkills ? post.KeySkills.split(',').slice(0, 3) : [];
            const skillsHtml = skills.map(skill => `<span class="skill-tag" style="font-size: 11px; padding: 2px 8px;">${skill.trim()}</span>`).join('');
            
            return `
                <div class="my-post-item" data-post-id="${post.PostID}" style="border: 1px solid var(--border); border-radius: 8px; padding: 15px; margin-bottom: 15px; background: var(--bg-primary);">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 10px;">
                        <div>
                            <h4 style="color: var(--accent); margin: 0 0 5px 0;">${escapeHtml(post.JobTitle)}</h4>
                            <p style="color: var(--text-secondary); margin: 0; font-size: 12px;">Posted ${postTime}</p>
                        </div>
                        <div style="display: flex; gap: 5px; align-items: center;">
                            <span style="background: var(--success); color: white; padding: 2px 8px; border-radius: 12px; font-size: 11px; text-transform: uppercase;">${post.Status}</span>
                            <button onclick="event.stopPropagation(); editMyPost(${post.PostID})" style="background: var(--accent); color: white; border: none; border-radius: 4px; padding: 4px 8px; font-size: 11px; cursor: pointer; display: flex; align-items: center; gap: 4px;" title="Edit Post">
                                <i class="fas fa-edit"></i>
                                <span>Edit</span>
                            </button>
                            <button onclick="event.stopPropagation(); deleteMyPost(${post.PostID})" style="background: var(--danger); color: white; border: none; border-radius: 4px; padding: 4px 8px; font-size: 11px; cursor: pointer; display: flex; align-items: center; gap: 4px;" title="Delete Post">
                                <i class="fas fa-trash"></i>
                                <span>Delete</span>
                            </button>
                        </div>
                    </div>
                    <p style="margin: 5px 0; color: var(--text-primary); font-size: 14px;">${escapeHtml(post.Education)}</p>
                    <p style="margin: 5px 0; color: var(--text-secondary); font-size: 13px; line-height: 1.4;">${escapeHtml(post.CareerGoal.substring(0, 100))}${post.CareerGoal.length > 100 ? '...' : ''}</p>
                    ${skillsHtml ? `<div style="margin-top: 8px;">${skillsHtml}</div>` : ''}
                </div>
            `;
        }

        // Edit my post
        function editMyPost(postId) {
            console.log('Editing post:', postId);
            console.log('Available my posts:', myPosts);
            
            // Close the current "My Job Seeking Posts" popup first
            closeMyPostsModal();
            
            // Find the post data in myPosts array
            const post = myPosts.find(p => p.PostID == postId);
            if (!post) {
                console.error('Post not found in myPosts array. PostID:', postId);
                showErrorMessage('Post not found');
                return;
            }
            
            // Load post data into edit form
            loadPostDataForEdit(post);
            
            // Show edit popup
            const editPopup = document.getElementById('editJobSeekingPopup');
            editPopup.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        // Load post data into edit form
        function loadPostDataForEdit(post) {
            document.getElementById('editPostId').value = post.PostID;
            document.getElementById('editJobTitle').value = post.JobTitle || '';
            document.getElementById('editCareerGoal').value = post.CareerGoal || '';
            document.getElementById('editKeySkills').value = post.KeySkills || '';
            document.getElementById('editExperience').value = post.Experience || '';
            document.getElementById('editEducation').value = post.Education || '';
            document.getElementById('editSoftSkills').value = post.SoftSkills || '';
            document.getElementById('editValueToEmployer').value = post.ValueToEmployer || '';
            document.getElementById('editContactInfo').value = post.ContactInfo || '';
        }

        // Close edit job seeking popup
        function closeEditJobSeekingPopup() {
            const popup = document.getElementById('editJobSeekingPopup');
            const form = document.getElementById('editJobSeekingForm');
            
            popup.style.display = 'none';
            document.body.style.overflow = 'auto';
            form.reset();
        }

        // Setup edit job seeking functionality
        function setupEditJobSeeking() {
            const editForm = document.getElementById('editJobSeekingForm');
            const editPopup = document.getElementById('editJobSeekingPopup');
            
            if (!editForm || !editPopup) {
                console.error('Edit job seeking elements not found');
                return;
            }
            
            // Handle form submission
            editForm.addEventListener('submit', function(e) {
                e.preventDefault();
                updateJobSeekingPost();
            });
            
            // Close popup when clicking outside
            editPopup.addEventListener('click', function(e) {
                if (e.target === editPopup) {
                    closeEditJobSeekingPopup();
                }
            });
        }

        // Update job seeking post
        function updateJobSeekingPost() {
            console.log('Updating job seeking post...');
            
            const form = document.getElementById('editJobSeekingForm');
            const submitBtn = document.getElementById('submitEditJobSeekingPost');
            const formData = new FormData(form);
            
            // Prevent multiple submissions
            if (submitBtn.disabled) {
                return;
            }
            
            // Disable button and show loading
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
            
            const postData = {
                action: 'update_post',
                postId: formData.get('postId'),
                jobTitle: formData.get('jobTitle'),
                careerGoal: formData.get('careerGoal'),
                keySkills: formData.get('keySkills'),
                experience: formData.get('experience'),
                education: formData.get('education'),
                softSkills: formData.get('softSkills'),
                valueToEmployer: formData.get('valueToEmployer'),
                contactInfo: formData.get('contactInfo'),
                candidateId: currentCandidateId
            };
            
            fetch('job_seeking_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(postData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeEditJobSeekingPopup();
                    showSuccessMessage('Job seeking post updated successfully!');
                    // Reload the modal to show updated list
                    loadMyPosts();
                } else {
                    showErrorMessage(data.message || 'Failed to update job seeking post');
                }
            })
            .catch(error => {
                console.error('Error updating job seeking post:', error);
                showErrorMessage('Network error. Please try again.');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-save"></i> Save Changes';
            });
        }

        // Delete my post with confirmation popup
        function deleteMyPost(postId) {
            showDeleteConfirmationPopup(postId);
        }

        // Show delete confirmation popup with enhanced animations
        function showDeleteConfirmationPopup(postId) {
            const modal = document.createElement('div');
            modal.className = 'popup-overlay delete-confirmation-modal';
            modal.style.display = 'flex';
            modal.innerHTML = `
                <div class="popup-content delete-confirmation-popup" style="max-width: 480px;">
                    <div class="popup-header" style="padding: 25px 30px 0;">
                        <div class="popup-title" style="display: flex; align-items: center; gap: 12px;">
                            <i class="fas fa-exclamation-triangle" style="color: var(--danger); font-size: 24px;"></i>
                            <span style="font-size: 22px; font-weight: 700;">Confirm Deletion</span>
                        </div>
                        <button class="popup-close" onclick="closeDeleteConfirmationPopup()" style="background: none; border: none; color: var(--text-secondary); font-size: 20px; cursor: pointer; padding: 5px; border-radius: 50%; transition: all 0.3s ease;">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="delete-confirmation-body">
                        <div class="warning-icon">
                            <i class="fas fa-trash-can"></i>
                        </div>
                        <h3>Delete Job Seeking Post</h3>
                        <p>Are you sure you want to permanently delete this job seeking post? This action cannot be undone and will remove all associated data.</p>
                        <div class="warning-text">
                            <i class="fas fa-shield-exclamation"></i>
                            <span><strong>Warning:</strong> This will permanently remove your post from the platform and cannot be recovered.</span>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeDeleteConfirmationPopup()">
                            <i class="fas fa-arrow-left"></i>
                            Cancel
                        </button>
                        <button type="button" class="btn btn-danger" onclick="confirmDeletePost(${postId})">
                            <i class="fas fa-trash-can-arrow-up"></i>
                            Delete Post
                        </button>
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
            document.body.style.overflow = 'hidden';
            
            // Add enhanced click outside to close functionality
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeDeleteConfirmationPopup();
                }
            });
            
            // Add keyboard support
            const handleKeyPress = (e) => {
                if (e.key === 'Escape') {
                    closeDeleteConfirmationPopup();
                }
            };
            document.addEventListener('keydown', handleKeyPress);
            
            // Store the keydown handler for cleanup
            modal._keydownHandler = handleKeyPress;
        }

        // Close delete confirmation popup with smooth animation
        function closeDeleteConfirmationPopup() {
            const modal = document.querySelector('.delete-confirmation-modal');
            if (modal) {
                // Remove keyboard event listener
                if (modal._keydownHandler) {
                    document.removeEventListener('keydown', modal._keydownHandler);
                }
                
                // Add exit animation
                modal.style.animation = 'fadeOutBackdrop 0.3s ease-in';
                const popup = modal.querySelector('.delete-confirmation-popup');
                if (popup) {
                    popup.style.animation = 'slideOutScale 0.3s ease-in';
                }
                
                // Remove after animation completes
                setTimeout(() => {
                    if (modal.parentNode) {
                        modal.remove();
                    }
                    document.body.style.overflow = 'auto';
                }, 300);
            }
        }

        // Confirm and execute delete
        function confirmDeletePost(postId) {
            console.log('Deleting post:', postId);
            
            // Show loading state
            const deleteBtn = document.querySelector('.btn-danger');
            const originalText = deleteBtn.innerHTML;
            deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';
            deleteBtn.disabled = true;
            
            fetch('job_seeking_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'delete_post',
                    postId: postId,
                    candidateId: currentCandidateId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeDeleteConfirmationPopup();
                    showSuccessMessage('Post deleted successfully');
                    // Reload the modal to show updated list
                    loadMyPosts();
                } else {
                    showErrorMessage(data.message || 'Failed to delete post');
                    // Reset button
                    deleteBtn.innerHTML = originalText;
                    deleteBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error deleting post:', error);
                showErrorMessage('Network error while deleting post');
                // Reset button
                deleteBtn.innerHTML = originalText;
                deleteBtn.disabled = false;
            });
        }

        // Close my posts modal
        function closeMyPostsModal() {
            // Remove all existing my-posts modals (in case there are multiple)
            const modals = document.querySelectorAll('.my-posts-modal');
            modals.forEach(modal => {
                modal.remove();
            });
            document.body.style.overflow = 'auto';
        }

        // Create new job seeking post
        function createJobSeekingPost() {
            // Global flag check - prevent multiple submissions
            if (isSubmittingJobSeekingPost) {
                console.log('Job seeking post submission already in progress, ignoring duplicate request');
                return;
            }
            
            const form = document.getElementById('jobSeekingForm');
            const submitBtn = document.getElementById('submitJobSeekingPost');
            const formData = new FormData(form);
            
            // Prevent duplicate submissions
            if (submitBtn.disabled) {
                console.log('Submit button already disabled, ignoring duplicate request');
                return;
            }
            
            // Set global flag
            isSubmittingJobSeekingPost = true;
            
            // Disable button and show loading
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating...';
            
            // Add unique client-side identifier
            const clientSubmissionId = 'client_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            
            const postData = {
                candidateId: currentCandidateId,
                jobTitle: formData.get('jobTitle'),
                careerGoal: formData.get('careerGoal'),
                keySkills: formData.get('keySkills'),
                experience: formData.get('experience'),
                education: formData.get('education'),
                softSkills: formData.get('softSkills'),
                valueToEmployer: formData.get('valueToEmployer'),
                contactInfo: formData.get('contactInfo'),
                requestId: Date.now() + '_' + Math.random().toString(36).substr(2, 9),
                clientSubmissionId: clientSubmissionId
            };
            
            fetch('job_seeking_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(postData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeJobSeekingPopup();
                    loadPosts(true); // Reload posts
                    showSuccessMessage('Job seeking post created successfully!');
                } else {
                    showErrorMessage(data.message || 'Failed to create job seeking post');
                }
            })
            .catch(error => {
                console.error('Error creating job seeking post:', error);
                showErrorMessage('Network error. Please try again.');
            })
            .finally(() => {
                // Reset global flag and button state
                isSubmittingJobSeekingPost = false;
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Create Post';
            });
        }

        // Close job seeking popup
        function closeJobSeekingPopup() {
            const popup = document.getElementById('jobSeekingPopup');
            const form = document.getElementById('jobSeekingForm');
            
            popup.style.display = 'none';
            document.body.style.overflow = 'auto';
            form.reset();
            
            // Reset global submission flag when popup is closed
            isSubmittingJobSeekingPost = false;
        }

        // Show success message
        function showSuccessMessage(message) {
            // Create a temporary success notification
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
            // Create a temporary error notification
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

        // Show info message
        function showInfoMessage(message) {
            // Create a temporary info notification
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: var(--info);
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
            }, 4000);
        }

        // Contact candidate
        function contactCandidate(postId) {
            // Find the post data in myPosts array
            const post = myPosts.find(p => p.PostID == postId);
            if (post) {
                // Extract contact information
                const contactInfo = post.ContactInfo;
                if (contactInfo) {
                    // Copy contact info to clipboard
                    navigator.clipboard.writeText(contactInfo).then(() => {
                        showSuccessMessage('Contact information copied to clipboard!');
                    }).catch(() => {
                        // Fallback: show contact info in alert
                        alert(`Contact Information:\n${contactInfo}`);
                    });
                } else {
                    showErrorMessage('No contact information available');
                }
            }
        }

        // View candidate profile
        function viewProfile(candidateId) {
            // Redirect to candidate profile page
            window.open(`CandidateDashboard.php?candidateId=${candidateId}`, '_blank');
        }

        // Share post
        function sharePost(postId) {
            const post = myPosts.find(p => p.PostID == postId);
            if (post) {
                const shareText = `Check out this job seeking post from ${post.FullName}: ${post.JobTitle}`;
                const shareUrl = window.location.href;
                
                if (navigator.share) {
                    navigator.share({
                        title: 'Job Seeking Post',
                        text: shareText,
                        url: shareUrl
                    });
                } else {
                    // Fallback: copy to clipboard
                    navigator.clipboard.writeText(`${shareText}\n${shareUrl}`).then(() => {
                        showSuccessMessage('Post link copied to clipboard!');
                    }).catch(() => {
                        showErrorMessage('Failed to share post');
                    });
                }
            }
        }

        // Utility functions
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function formatTimeAgo(dateString) {
            const now = new Date();
            const postDate = new Date(dateString);
            const diffInSeconds = Math.floor((now - postDate) / 1000);
            
            if (diffInSeconds < 60) {
                return 'just now';
            } else if (diffInSeconds < 3600) {
                const minutes = Math.floor(diffInSeconds / 60);
                return `${minutes} minute${minutes > 1 ? 's' : ''} ago`;
            } else if (diffInSeconds < 86400) {
                const hours = Math.floor(diffInSeconds / 3600);
                return `${hours} hour${hours > 1 ? 's' : ''} ago`;
            } else {
                const days = Math.floor(diffInSeconds / 86400);
                return `${days} day${days > 1 ? 's' : ''} ago`;
            }
        }

        // Post interaction functions
        function likePost(postId) {
            console.log('Like post:', postId);
            // TODO: Implement like functionality
        }

        function commentPost(postId) {
            console.log('Comment on post:', postId);
            // TODO: Implement comment functionality
        }




        // Logout functionality
        const logoutBtn = document.getElementById('logoutBtn');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', function() {
                // Perform logout and navigate immediately
                fetch('logout.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    }
                })
                .then(() => {
                    window.location.href = 'Login&Signup.php';
                })
                .catch(() => {
                    // Fallback if logout endpoint doesn't exist
                    window.location.href = 'Login&Signup.php';
                });
            });
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
            
            fetch(`candidate_profile_handler.php?candidateId=${currentCandidateId}`)
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
            formData.append('candidateId', currentCandidateId);
            
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
                            avatar.style.background = 'linear-gradient(135deg, var(--accent), var(--accent-2))';
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

        // Refresh posts function
        function refreshPosts() {
            console.log('Manually refreshing posts...');
            
            // Show loading state on refresh button
            const refreshBtn = document.getElementById('refreshPostsBtn');
            if (refreshBtn) {
                const originalContent = refreshBtn.innerHTML;
                refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Refreshing...';
                refreshBtn.disabled = true;
                
                // Reset button after refresh
                setTimeout(() => {
                    refreshBtn.innerHTML = originalContent;
                    refreshBtn.disabled = false;
                }, 2000);
            }
            
            currentOffset = 0;
            posts = [];
            loadPosts(true);
        }

        // Check which jobs have been applied to
        function checkAppliedJobs() {
            return fetch('job_application_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'get_application_status'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.applications) {
                    const appliedJobIds = data.applications.map(app => app.JobID);
                    posts.forEach(post => {
                        post.isApplied = appliedJobIds.includes(post.JobID);
                    });
                }
            })
            .catch(error => {
                console.error('Error checking applied jobs:', error);
            });
        }

        // Apply to job function
        function applyToJob(jobId) {
            console.log('Opening application popup for job:', jobId);
            
            // Find the apply button for this job and check if already applied
            const applyButtons = document.querySelectorAll(`[onclick="applyToJob(${jobId})"]`);
            const firstButton = applyButtons[0];
            
            if (firstButton && firstButton.innerHTML.includes('Applied')) {
                console.log('Already applied to this job');
                showInfoMessage('You have already applied to this job.');
                return;
            }
            
            // Disable button temporarily to prevent multiple clicks
            applyButtons.forEach(btn => {
                btn.style.pointerEvents = 'none';
                btn.style.opacity = '0.6';
                const originalContent = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Processing...</span>';
                
                // Re-enable after a short delay
                setTimeout(() => {
                    btn.style.pointerEvents = 'auto';
                    btn.style.opacity = '1';
                    btn.innerHTML = originalContent;
                }, 2000);
            });
            
            // Set the job ID in the form
            document.getElementById('applicationJobId').value = jobId;
            
            // Clear the form
            document.getElementById('jobApplicationForm').reset();
            document.getElementById('applicationJobId').value = jobId;
            
            // Show the popup
            const popup = document.getElementById('jobApplicationPopup');
            popup.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        // View company profile function
        function viewCompanyProfile(companyId) {
            console.log('Opening company profile for ID:', companyId);
            showCompanyDetailsPopup(companyId);
        }

        // Show company details popup
        function showCompanyDetailsPopup(companyId) {
            const popup = document.getElementById('companyDetailsPopup');
            const content = document.getElementById('companyDetailsContent');
            const title = document.getElementById('companyDetailsTitle');
            
            // Show popup with loading state
            popup.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            
            // Reset content to loading state
            content.innerHTML = `
                <div class="loading-company" style="text-align: center; padding: 40px; color: var(--text-secondary);">
                    <i class="fas fa-spinner fa-spin" style="font-size: 24px; margin-bottom: 10px;"></i>
                    <div>Loading company details...</div>
                </div>
            `;
            
            // Fetch company details
            fetch(`company_details_handler.php?companyId=${companyId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        title.textContent = data.company.name;
                        renderCompanyDetails(data);
                    } else {
                        showCompanyError(data.message || 'Failed to load company details');
                    }
                })
                .catch(error => {
                    console.error('Error loading company details:', error);
                    showCompanyError('Network error loading company details');
                });
        }

        // Render company details
        function renderCompanyDetails(data) {
            const content = document.getElementById('companyDetailsContent');
            const company = data.company;
            const stats = data.statistics;
            const recentJobs = data.recentJobs;
            
            // Get company initials for logo fallback
            const initials = company.name.split(' ').map(name => name[0]).join('').toUpperCase();
            
            // Format join date
            const joinDate = new Date(company.joinedDate).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            
            // Build contact info
            const contactInfo = [];
            if (company.email) {
                contactInfo.push(`
                    <div class="contact-item">
                        <i class="fas fa-envelope"></i>
                        <div class="contact-item-content">
                            <div class="contact-label">Email</div>
                            <div class="contact-value">
                                <a href="mailto:${company.email}">${company.email}</a>
                            </div>
                        </div>
                    </div>
                `);
            }
            if (company.phone) {
                contactInfo.push(`
                    <div class="contact-item">
                        <i class="fas fa-phone"></i>
                        <div class="contact-item-content">
                            <div class="contact-label">Phone</div>
                            <div class="contact-value">${company.phone}</div>
                        </div>
                    </div>
                `);
            }
            if (company.website) {
                const websiteUrl = company.website.startsWith('http') ? company.website : `https://${company.website}`;
                contactInfo.push(`
                    <div class="contact-item">
                        <i class="fas fa-globe"></i>
                        <div class="contact-item-content">
                            <div class="contact-label">Website</div>
                            <div class="contact-value">
                                <a href="${websiteUrl}" target="_blank" rel="noopener noreferrer">${company.website}</a>
                            </div>
                        </div>
                    </div>
                `);
            }
            if (company.address || company.city || company.state || company.country) {
                const addressParts = [company.address, company.city, company.state, company.country].filter(Boolean);
                contactInfo.push(`
                    <div class="contact-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <div class="contact-item-content">
                            <div class="contact-label">Address</div>
                            <div class="contact-value">${addressParts.join(', ')}</div>
                        </div>
                    </div>
                `);
            }
            
            // Build recent jobs
            const recentJobsHtml = recentJobs.map(job => {
                const postedDate = new Date(job.postedDate).toLocaleDateString('en-US', {
                    month: 'short',
                    day: 'numeric'
                });
                return `
                    <div class="job-item">
                        <div class="job-info">
                            <h4>${escapeHtml(job.title)}</h4>
                            <div class="job-meta">
                                ${job.department ? `<span>${escapeHtml(job.department)}</span>` : ''}
                                ${job.location ? `<span>• ${escapeHtml(job.location)}</span>` : ''}
                                <span>• ${job.type.replace('-', ' ').toUpperCase()}</span>
                                <span>• ${postedDate}</span>
                            </div>
                        </div>
                        <div class="job-stats">
                            <div class="job-applications">${job.applicationCount} applications</div>
                        </div>
                    </div>
                `;
            }).join('');
            
            content.innerHTML = `
                <div class="company-header">
                    <div class="company-logo" style="${company.logo ? `background-image: url('${company.logo}'); color: transparent;` : ''}">
                        ${company.logo ? '' : initials}
                    </div>
                    <div class="company-info">
                        <h2>${escapeHtml(company.name)}</h2>
                        <div class="company-meta">
                            ${company.industry ? `
                                <div class="company-meta-item">
                                    <i class="fas fa-industry"></i>
                                    <span>${escapeHtml(company.industry)}</span>
                                </div>
                            ` : ''}
                            ${company.size ? `
                                <div class="company-meta-item">
                                    <i class="fas fa-users"></i>
                                    <span>${company.size} employees</span>
                                </div>
                            ` : ''}
                            <div class="company-meta-item">
                                <i class="fas fa-calendar-plus"></i>
                                <span>Joined ${joinDate}</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                ${company.description ? `
                    <div class="company-description">
                        ${escapeHtml(company.description)}
                    </div>
                ` : ''}
                
                <div class="company-section">
                    <div class="section-title">
                        <i class="fas fa-chart-bar"></i>
                        Company Statistics
                    </div>
                    <div class="company-stats">
                        <div class="stat-card">
                            <div class="stat-number">${stats.totalJobs}</div>
                            <div class="stat-label">Active Jobs</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number">${stats.totalApplications}</div>
                            <div class="stat-label">Total Applications</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number">${stats.totalInterviews}</div>
                            <div class="stat-label">Interviews Conducted</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number">${stats.totalExams}</div>
                            <div class="stat-label">Exams Created</div>
                        </div>
                    </div>
                </div>
                
                ${contactInfo.length > 0 ? `
                    <div class="company-section">
                        <div class="section-title">
                            <i class="fas fa-address-book"></i>
                            Contact Information
                        </div>
                        <div class="contact-info">
                            ${contactInfo.join('')}
                        </div>
                    </div>
                ` : ''}
                
                ${recentJobs.length > 0 ? `
                    <div class="company-section">
                        <div class="section-title">
                            <i class="fas fa-briefcase"></i>
                            Recent Job Postings
                        </div>
                        <div class="recent-jobs">
                            ${recentJobsHtml}
                        </div>
                    </div>
                ` : ''}
            `;
        }

        // Show company error
        function showCompanyError(message) {
            const content = document.getElementById('companyDetailsContent');
            content.innerHTML = `
                <div style="text-align: center; padding: 40px; color: var(--danger);">
                    <i class="fas fa-exclamation-triangle" style="font-size: 48px; margin-bottom: 20px;"></i>
                    <h3 style="margin: 0 0 10px 0; color: var(--danger);">Error Loading Company Details</h3>
                    <p style="color: var(--text-secondary); margin: 0;">${message}</p>
                </div>
            `;
        }

        // Close company details popup
        function closeCompanyDetailsPopup() {
            const popup = document.getElementById('companyDetailsPopup');
            popup.style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        // Report job post function
        function reportJobPost(jobId, jobTitle, companyId, companyName) {
            console.log('Opening report popup for job:', jobId);
            
            // Set the job details in the form
            document.getElementById('reportJobId').value = jobId;
            document.getElementById('reportCompanyId').value = companyId;
            document.getElementById('reportJobTitle').value = jobTitle;
            document.getElementById('reportCompanyName').value = companyName;
            
            // Clear the form
            document.getElementById('reportJobForm').reset();
            document.getElementById('reportJobId').value = jobId;
            document.getElementById('reportCompanyId').value = companyId;
            document.getElementById('reportJobTitle').value = jobTitle;
            document.getElementById('reportCompanyName').value = companyName;
            
            // Show the popup
            const popup = document.getElementById('reportJobPopup');
            popup.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        // Close report job popup
        function closeReportJobPopup() {
            const popup = document.getElementById('reportJobPopup');
            const form = document.getElementById('reportJobForm');
            
            popup.style.display = 'none';
            document.body.style.overflow = 'auto';
            form.reset();
        }

        // Setup report job functionality
        function setupReportJob() {
            console.log('Setting up report job functionality...');
            
            const reportForm = document.getElementById('reportJobForm');
            const reportPopup = document.getElementById('reportJobPopup');
            
            if (!reportForm || !reportPopup) {
                console.error('Report job elements not found');
                return;
            }
            
            // Handle form submission
            reportForm.addEventListener('submit', function(e) {
                e.preventDefault();
                submitJobReport();
            });
            
            // Close popup when clicking outside
            reportPopup.addEventListener('click', function(e) {
                if (e.target === reportPopup) {
                    closeReportJobPopup();
                }
            });
            
            console.log('Report job functionality setup complete');
        }

        // Submit job report
        function submitJobReport() {
            console.log('Submitting job report...');
            
            const form = document.getElementById('reportJobForm');
            const submitBtn = document.getElementById('submitReport');
            const formData = new FormData(form);
            
            // Prevent multiple submissions
            if (submitBtn.disabled) {
                console.log('Report already being submitted, ignoring duplicate request');
                return;
            }
            
            const reportData = {
                action: 'report_job',
                jobId: formData.get('jobId'),
                companyId: formData.get('companyId'),
                jobTitle: formData.get('jobTitle'),
                companyName: formData.get('companyName'),
                reason: formData.get('reason'),
                description: formData.get('description'),
                contact: formData.get('contact'),
                candidateId: currentCandidateId,
                requestId: Date.now() + '_' + Math.random().toString(36).substr(2, 9)
            };
            
            console.log('Report data:', reportData);
            
            // Validate required fields
            if (!reportData.jobId) {
                showErrorMessage('Job ID is missing. Please try again.');
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
            
            // Disable button and show loading
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
                    closeReportJobPopup();
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

        // Search and filter functionality
        let currentSearchTerm = '';
        let currentFilters = {};

        // Setup search and filter event listeners
        function setupSearchAndFilters() {
            const searchInput = document.getElementById('jobSearchInput');
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
                loadPosts(true);
            }, 300);
        }

        // Clear all filters
        function clearAllFilters() {
            currentSearchTerm = '';
            currentFilters = {};
            
            // Clear input fields
            document.getElementById('jobSearchInput').value = '';
            document.getElementById('companyFilter').value = '';
            document.getElementById('locationFilter').value = '';
            document.getElementById('jobTypeFilter').value = '';
            document.getElementById('experienceFilter').value = '';
            document.getElementById('skillsFilter').value = '';
            document.getElementById('salaryFilter').value = '';
            
            // Hide advanced filter panel
            const advancedFilterPanel = document.getElementById('advancedFilterPanel');
            const advancedFilterBtn = document.getElementById('advancedFilterBtn');
            if (advancedFilterPanel && advancedFilterBtn) {
                advancedFilterPanel.style.display = 'none';
                advancedFilterBtn.innerHTML = '<i class="fas fa-filter"></i><span>Advanced Filter</span>';
            }
            
            // Reload posts
            loadPosts(true);
        }

        // Apply advanced filters
        function applyAdvancedFilters() {
            currentFilters = {
                company: document.getElementById('companyFilter').value.trim(),
                location: document.getElementById('locationFilter').value.trim(),
                jobType: document.getElementById('jobTypeFilter').value,
                experience: document.getElementById('experienceFilter').value,
                skills: document.getElementById('skillsFilter').value.trim(),
                salary: document.getElementById('salaryFilter').value
            };
            
            loadPosts(true);
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

        // Close job application popup
        function closeJobApplicationPopup() {
            const popup = document.getElementById('jobApplicationPopup');
            const form = document.getElementById('jobApplicationForm');
            
            popup.style.display = 'none';
            document.body.style.overflow = 'auto';
            form.reset();
        }

        // Setup job application functionality
        function setupJobApplication() {
            console.log('Setting up job application functionality...');
            
            const applicationForm = document.getElementById('jobApplicationForm');
            const applicationPopup = document.getElementById('jobApplicationPopup');
            
            console.log('Form element:', applicationForm);
            console.log('Popup element:', applicationPopup);
            
            if (!applicationForm || !applicationPopup) {
                console.error('Job application elements not found');
                console.error('Form found:', !!applicationForm);
                console.error('Popup found:', !!applicationPopup);
                return;
            }
            
            // Handle form submission (only one event listener)
            applicationForm.addEventListener('submit', function(e) {
                console.log('Form submit event triggered');
                e.preventDefault();
                submitJobApplication();
            });
            
            // Close popup when clicking outside
            applicationPopup.addEventListener('click', function(e) {
                if (e.target === applicationPopup) {
                    closeJobApplicationPopup();
                }
            });
        }

        // Setup company details popup functionality
        function setupCompanyDetailsPopup() {
            console.log('Setting up company details popup functionality...');
            
            const companyPopup = document.getElementById('companyDetailsPopup');
            
            if (!companyPopup) {
                console.error('Company details popup not found');
                return;
            }
            
            // Close popup when clicking outside
            companyPopup.addEventListener('click', function(e) {
                if (e.target === companyPopup) {
                    closeCompanyDetailsPopup();
                }
            });
            
            console.log('Company details popup functionality setup complete');
        }

        // Submit job application
        function submitJobApplication() {
            console.log('Submitting job application...');
            
            const form = document.getElementById('jobApplicationForm');
            const submitBtn = document.getElementById('submitApplication');
            
            if (!form) {
                console.error('Form not found!');
                showErrorMessage('Form not found. Please refresh the page.');
                return;
            }
            
            if (!submitBtn) {
                console.error('Submit button not found!');
                showErrorMessage('Submit button not found. Please refresh the page.');
                return;
            }
            
            // Prevent multiple submissions
            if (submitBtn.disabled) {
                console.log('Application already being submitted, ignoring duplicate request');
                return;
            }
            
            // Add timestamp to prevent duplicate requests
            const timestamp = Date.now();
            if (window.lastApplicationTime && (timestamp - window.lastApplicationTime) < 5000) {
                console.log('Application submitted too recently, ignoring duplicate request');
                showErrorMessage('Please wait before submitting another application.');
                return;
            }
            window.lastApplicationTime = timestamp;
            
            const formData = new FormData(form);
            
            const applicationData = {
                action: 'apply_to_job',
                jobId: formData.get('jobId'),
                coverLetter: formData.get('coverLetter'),
                additionalNotes: formData.get('additionalNotes'),
                requestId: timestamp + '_' + Math.random().toString(36).substr(2, 9)
            };
            
            console.log('Application data:', applicationData);
            
            // Validate required fields
            if (!applicationData.jobId) {
                showErrorMessage('Job ID is missing. Please try again.');
                return;
            }
            
            if (!applicationData.coverLetter || applicationData.coverLetter.trim() === '') {
                showErrorMessage('Cover letter is required.');
                return;
            }
            
            // Disable button and show loading
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
            
            fetch('job_application_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(applicationData)
            })
            .then(response => {
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                return response.text(); // Get raw response first
            })
            .then(text => {
                console.log('Raw response:', text);
                
                try {
                    const data = JSON.parse(text);
                    console.log('Parsed response:', data);
                    
                   if (data.success) {
                       closeJobApplicationPopup();
                       showSuccessMessage('Application submitted successfully!');
                       
                       // Update the apply button to show success state
                       const jobId = document.getElementById('applicationJobId').value;
                       const applyButtons = document.querySelectorAll(`[onclick="applyToJob(${jobId})"]`);
                       applyButtons.forEach(btn => {
                           btn.innerHTML = '<i class="fas fa-check"></i><span>Applied</span>';
                           btn.style.pointerEvents = 'none';
                       });
                       
                       // Show exam assignment info if exams were assigned
                       if (data.assignedExams && data.assignedExams.length > 0) {
                           const examInfo = data.assignedExams.map(exam => 
                               `${exam.examTitle} (${exam.totalQuestions} questions, ${Math.floor(exam.duration/60)} minutes)`
                           ).join('\n');
                           
                           showInfoMessage(`Great! ${data.examCount} exam(s) have been assigned to you:\n\n${examInfo}\n\nYou can take these exams in the "Attend Exam" section.`);
                       }
                       
                       // Update the application status locally without reloading all posts
                       // The application count will be updated when posts are next loaded naturally
                   } else {
                       showErrorMessage(data.message || 'Failed to submit application');
                   }
                } catch (parseError) {
                    console.error('JSON parse error:', parseError);
                    console.error('Response text:', text);
                    showErrorMessage('Invalid response from server. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error submitting application:', error);
                showErrorMessage('Network error: ' + error.message);
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Submit Application';
            });
        }

        // Debug function to test application
        function testApplication() {
            console.log('Testing application functionality...');
            
            // Test if elements exist
            const form = document.getElementById('jobApplicationForm');
            const popup = document.getElementById('jobApplicationPopup');
            const submitBtn = document.getElementById('submitApplication');
            
            console.log('Form exists:', !!form);
            console.log('Popup exists:', !!popup);
            console.log('Submit button exists:', !!submitBtn);
            
            if (form && popup && submitBtn) {
                console.log('All elements found - application should work');
                showSuccessMessage('Application system is ready!');
            } else {
                console.error('Missing elements - application will not work');
                showErrorMessage('Application system not ready. Missing elements.');
            }
        }

        // Initialize dashboard when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            initializeTheme();
            initializeDashboard();
            setupProfileEditing();
            // setupJobSeekingPostCreation(); // Already called in initializeDashboard()
            setupJobApplication();
            setupCompanyDetailsPopup();
            setupEditJobSeeking();
            setupSearchAndFilters();
            setupThemeToggle();
            setupReportJob();
            loadPosts(true);
            
            // Application system is now ready
            console.log('Application system initialized successfully');
        });

    </script>
</body>
</html>
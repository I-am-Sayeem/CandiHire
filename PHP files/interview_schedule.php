<?php
// Interview Schedule functionality
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

// Load real interview data from database
$upcomingInterviews = [];
$pastInterviews = [];

try {
    if (isset($pdo) && $pdo instanceof PDO) {
        // Get upcoming interviews (scheduled and in-progress)
        $stmt = $pdo->prepare("
            SELECT 
                i.InterviewID,
                i.InterviewTitle,
                i.InterviewType,
                i.InterviewMode,
                i.Platform,
                i.MeetingLink,
                i.ScheduledDate,
                i.ScheduledTime,
                i.Location,
                i.Status,
                i.Notes,
                co.CompanyName,
                co.Email as CompanyEmail,
                jp.JobTitle
            FROM interviews i
            JOIN Company_login_info co ON i.CompanyID = co.CompanyID
            LEFT JOIN job_postings jp ON i.JobID = jp.JobID
            WHERE i.CandidateID = ? 
            AND i.Status IN ('scheduled', 'in-progress')
            AND (i.ScheduledDate > CURDATE() OR (i.ScheduledDate = CURDATE() AND i.ScheduledTime > CURTIME()))
            ORDER BY i.ScheduledDate ASC, i.ScheduledTime ASC
        ");
        $stmt->execute([$sessionCandidateId]);
        $upcomingInterviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get past interviews (completed, cancelled, rescheduled)
        $stmt = $pdo->prepare("
            SELECT 
                i.InterviewID,
                i.InterviewTitle,
                i.InterviewType,
                i.InterviewMode,
                i.Platform,
                i.MeetingLink,
                i.ScheduledDate,
                i.ScheduledTime,
                i.Location,
                i.Status,
                i.Notes,
                i.Feedback,
                i.Rating,
                co.CompanyName,
                co.Email as CompanyEmail,
                jp.JobTitle
            FROM interviews i
            JOIN Company_login_info co ON i.CompanyID = co.CompanyID
            LEFT JOIN job_postings jp ON i.JobID = jp.JobID
            WHERE i.CandidateID = ? 
            AND (i.Status IN ('completed', 'cancelled', 'rescheduled') 
                 OR (i.ScheduledDate < CURDATE()) 
                 OR (i.ScheduledDate = CURDATE() AND i.ScheduledTime < CURTIME()))
            ORDER BY i.ScheduledDate DESC, i.ScheduledTime DESC
        ");
        $stmt->execute([$sessionCandidateId]);
        $pastInterviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    error_log("Error loading interview data: " . $e->getMessage());
    // Keep empty arrays if there's an error
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interview Schedule - CandiHire</title>
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

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Noto Sans', Helvetica, Arial, sans-serif; }
        body { background-color: var(--bg-primary); color: var(--text-primary); line-height: 1.5; }
        .container { display: flex; min-height: 100vh; max-width: 1400px; margin: 0 auto; }

        .left-nav { width: 280px; background-color: var(--bg-secondary); padding: 20px 15px; border-right: 1px solid var(--border); position: sticky; top: 0; height: 100vh; overflow-y: auto; }
        .logo {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0 10px;
        }

        .logo .candi {
            color: var(--accent);
        }

        .logo .hire {
            color: var(--accent-secondary);
            margin-left: -2px;
        }
        .nav-section { margin-bottom: 25px; }
        .nav-section-title { font-size: 12px; font-weight: 600; text-transform: uppercase; color: var(--text-secondary); margin-bottom: 10px; padding: 0 10px; letter-spacing: 0.5px; }
        .nav-item { display: flex; align-items: center; gap: 15px; padding: 12px 15px; margin-bottom: 6px; border-radius: 8px; cursor: pointer; transition: background-color 0.2s ease, color 0.2s ease, transform 0.2s ease; font-size: 15px; text-decoration: none; color: var(--text-primary); will-change: transform, background-color, color; }
        .nav-item:hover { background-color: var(--bg-tertiary); color: var(--text-primary); transform: translateX(2px); }
        .nav-item.active { background-color: var(--bg-tertiary); font-weight: 500; }
        .nav-item i { font-size: 20px; width: 24px; text-align: center; }

        .logout-container { margin-top: 20px; padding: 0 10px; }
        .logout-btn { width: 100%; background-color: var(--danger); color: white; border: none; border-radius: 8px; padding: 10px 14px; cursor: pointer; font-weight: 600; font-size: 14px; transition: all 0.2s; }
        .logout-btn:hover { background-color: #d03f39; transform: translateY(-1px); }

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

        .main-content { flex: 1; padding: 20px; }

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
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 1px solid var(--border); }
        .page-title { font-size: 36px; font-weight: bold; color: var(--accent); }
        .header-actions { display: flex; gap: 12px; }
        .btn { padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer; font-weight: 500; font-size: 14px; transition: background-color 0.2s, color 0.2s, transform 0.2s, box-shadow 0.2s; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; will-change: transform, background-color, color; }
        .btn-secondary { background-color: var(--bg-tertiary); color: var(--text-primary); border: 1px solid var(--border); }
        .btn-secondary:hover { background-color: var(--bg-secondary); transform: translateY(-1px); }
        .btn-primary { background-color: var(--accent); color: white; }
        .btn-primary:hover { background-color: var(--accent-hover); transform: translateY(-1px); box-shadow: 0 4px 10px rgba(0,0,0,0.2); }

        .schedule-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .section { background-color: var(--bg-secondary); border-radius: 12px; border: 1px solid var(--border); overflow: hidden; }
        .section-header { padding: 16px 20px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
        .section-title { font-size: 20px; font-weight: 600; color: var(--text-primary); }
        .section-body { padding: 20px; }

        .interview-card { background-color: var(--bg-primary); border: 1px solid var(--border); border-radius: 8px; padding: 16px; margin-bottom: 14px; transition: transform .2s, box-shadow .2s; will-change: transform, box-shadow; }
        .interview-card:hover { transform: translateY(-2px); box-shadow: 0 8px 16px rgba(0,0,0,.25); }
        
        .interview-card.interview-expiring {
            border-left: 4px solid var(--accent-secondary);
            background: linear-gradient(135deg, var(--bg-primary), rgba(245, 158, 11, 0.1));
            animation: pulse-warning 2s infinite;
        }

        .interview-card.interview-expired {
            border-left: 4px solid var(--danger);
            background: linear-gradient(135deg, var(--bg-primary), rgba(248, 81, 73, 0.1));
            opacity: 0.7;
        }

        @keyframes pulse-warning {
            0% { box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(245, 158, 11, 0); }
            100% { box-shadow: 0 0 0 0 rgba(245, 158, 11, 0); }
        }
        .interview-title { font-size: 18px; font-weight: 600; margin-bottom: 6px; }
        .interview-company { font-size: 14px; color: var(--text-secondary); margin-bottom: 10px; display:flex; align-items:center; gap:8px; }
        .detail-row { display:grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 8px; }
        .detail { display:flex; gap:8px; font-size:14px; color: var(--text-primary); align-items:center; }
        .detail i { color: var(--text-secondary); width: 16px; }
        .card-actions { display:flex; gap:10px; margin-top: 10px; }
        .btn-danger { background-color: var(--danger); color:white; }
        .btn-danger:hover { background-color: #da3633; }
        .btn-success { background-color: var(--success); color:white; }
        .btn-success:hover { background-color: #2da04e; }

        /* Modal */
        .modal { display:none; position:fixed; inset:0; background:rgba(0,0,0,.7); z-index:1000; justify-content:center; align-items:center; }
        .modal-content { background:var(--bg-secondary); border:1px solid var(--border); border-radius:12px; width:90%; max-width:560px; overflow:hidden; }
        .modal-header { padding:16px 20px; border-bottom:1px solid var(--border); display:flex; justify-content:space-between; align-items:center; }
        .modal-title { font-size:20px; font-weight:600; }
        .close-btn { background:none; border:none; color:var(--text-secondary); font-size:22px; cursor:pointer; }
        .modal-body { padding:20px; }
        .form-row { display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:12px; }
        .form-group { display:flex; flex-direction:column; gap:6px; }
        .form-group label { font-size:13px; color:var(--text-secondary); }
        .form-input, .form-select, .form-textarea { background:var(--bg-primary); color:var(--text-primary); border:1px solid var(--border); border-radius:8px; padding:10px; font-size:14px; }
        .form-textarea { min-height:90px; resize:vertical; }

        /* Toast */
        .toast { position:fixed; bottom:20px; right:20px; background:var(--bg-secondary); border:1px solid var(--border); color:var(--text-primary); border-radius:8px; padding:12px 16px; display:flex; gap:8px; align-items:center; box-shadow:0 4px 12px rgba(0,0,0,.2); opacity:0; transform:translateY(20px); transition:all .3s; z-index:1100; }
        .toast.show { opacity:1; transform:translateY(0); }

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

        /* Calendar Styles */
        #interviewCalendar {
            max-width: 700px;
            margin: 0 auto;
            width: 100%;
        }

        .calendar-header {
            background-color: var(--bg-tertiary);
            padding: 12px 10px;
            text-align: center;
            font-weight: 600;
            color: var(--text-primary);
            border-bottom: 1px solid var(--border);
            font-size: 14px;
        }

        .calendar-day {
            background-color: var(--bg-primary);
            padding: 10px 6px;
            min-height: 55px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            position: relative;
            font-size: 14px;
            font-weight: 500;
            border: 1px solid var(--border);
        }

        .calendar-day:hover {
            background-color: var(--bg-secondary);
        }

        .calendar-day.has-interview {
            background-color: var(--success);
            color: white;
        }

        .calendar-day.has-conflict {
            background-color: var(--danger);
            color: white;
        }

        .calendar-day.other-month {
            color: var(--text-secondary);
            background-color: var(--bg-secondary);
        }

        .calendar-day.today {
            border: 2px solid var(--accent);
            font-weight: 700;
        }
        
        .calendar-navigation {
            background-color: var(--bg-secondary);
            border-radius: 8px;
            margin-bottom: 15px;
        }
        
        .calendar-grid {
            background-color: var(--bg-secondary);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 1px;
        }
        
        @media (max-width: 768px) {
            #interviewCalendar {
                max-width: 100%;
                padding: 0 10px;
            }
            
            .calendar-day {
                min-height: 45px;
                padding: 8px 4px;
                font-size: 12px;
            }
            
            .calendar-header {
                padding: 10px 6px;
                font-size: 12px;
            }
            
            .calendar-navigation h3 {
                font-size: 16px !important;
            }
        }
        
        @media (max-width: 480px) {
            .calendar-day {
                min-height: 40px;
                padding: 6px 2px;
                font-size: 11px;
            }
            
            .calendar-header {
                padding: 8px 4px;
                font-size: 11px;
            }
        }

        .interview-details-popup {
            background-color: var(--bg-secondary);
            border-radius: 8px;
            padding: 20px;
            border: 1px solid var(--border);
        }

        .interview-detail-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid var(--border);
        }

        .interview-detail-item:last-child {
            border-bottom: none;
        }

        .interview-detail-label {
            font-weight: 600;
            color: var(--text-secondary);
            font-size: 14px;
        }

        .interview-detail-value {
            color: var(--text-primary);
            font-size: 14px;
        }

        .conflict-interviews {
            margin-top: 15px;
        }

        .conflict-interview {
            background-color: var(--bg-primary);
            border: 1px solid var(--danger);
            border-radius: 6px;
            padding: 12px;
            margin-bottom: 8px;
        }

        .conflict-interview h4 {
            color: var(--danger);
            margin-bottom: 8px;
            font-size: 14px;
        }

        .conflict-interview p {
            margin: 4px 0;
            font-size: 12px;
            color: var(--text-secondary);
        }

        @media (max-width: 1024px) { .schedule-grid { grid-template-columns: 1fr; } }
        @media (max-width: 768px) { .left-nav { display:none; } .main-content { padding:10px; } }
    </style>
</head>
<body>
    <div class="container">
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
            
            <div class="nav-section">
                <div class="nav-section-title">Main menu</div>
                <a href="CandidateDashboard.php" class="nav-item">
                    <i class="fas fa-home"></i>
                    <span>News feed</span>
                </a>
                <a href="CvBuilder.php" class="nav-item">
                    <i class="fas fa-file-alt"></i>
                    <span>CV builder</span>
                </a>
                <a href="applicationstatus.php" class="nav-item">
                    <i class="fas fa-clipboard-list"></i>
                    <span>Application status</span>
                </a>
            </div>
            <div class="nav-section">
                <div class="nav-section-title">Interviews & Exams</div>
                <a href="interview_schedule.php" class="nav-item active">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Interview schedule</span>
                </a>
                <a href="attendexam.php" class="nav-item">
                    <i class="fas fa-pencil-alt"></i>
                    <span>Attend Exam</span>
                </a>
            </div>
            <div class="logout-container">
                <button id="themeToggleBtn" class="theme-toggle-btn" title="Switch to Light Mode">
                    <i class="fas fa-moon-stars" id="themeIcon"></i>
                    <span id="themeText">Dark Mode</span>
                </button>
                <button id="logoutBtn" class="logout-btn"><i class="fas fa-sign-out-alt" style="margin-right:8px;"></i>Logout</button>
            </div>
        </div>

        <div class="main-content">
            <div class="page-header">
                <h1 class="page-title">Interview Schedule</h1>
                <div class="header-actions">
                    <button class="btn btn-secondary" onclick="window.location.reload()"><i class="fas fa-sync-alt"></i> Refresh</button>
                </div>
            </div>

            <div class="schedule-grid">
                <div class="section">
                    <div class="section-header">
                        <div class="section-title">Upcoming Interviews</div>
                    </div>
                    <div class="section-body" id="upcomingContainer">
                        <?php if (empty($upcomingInterviews)): ?>
                        <div style="text-align: center; color: var(--text-secondary); padding: 40px;">
                            <i class="fas fa-calendar-times" style="font-size: 48px; margin-bottom: 16px; opacity: 0.5;"></i>
                            <div style="font-size: 18px; margin-bottom: 8px;">No Upcoming Interviews</div>
                            <div style="font-size: 14px;">You don't have any scheduled interviews at the moment.</div>
                        </div>
                        <?php else: ?>
                        <?php foreach ($upcomingInterviews as $iv): ?>
                        <div class="interview-card" data-id="<?php echo $iv['InterviewID']; ?>">
                            <div class="interview-title"><?php echo htmlspecialchars($iv['InterviewTitle']); ?></div>
                            <div class="interview-company"><i class="fas fa-building"></i><?php echo htmlspecialchars($iv['CompanyName']); ?></div>
                            <div class="detail-row">
                                <div class="detail"><i class="fas fa-calendar"></i><?php echo date('F j, Y', strtotime($iv['ScheduledDate'])); ?></div>
                                <div class="detail"><i class="fas fa-clock"></i><?php echo date('g:i A', strtotime($iv['ScheduledTime'])); ?></div>
                                <div class="detail"><i class="fas fa-user-clock"></i><?php echo $iv['InterviewMode'] === 'virtual' ? 'Virtual • Virtual Meeting' : 'On-site • In-person'; ?></div>
                                <div class="detail"><i class="fas fa-map-marker-alt"></i><?php echo $iv['InterviewMode'] === 'virtual' ? 'Online' : htmlspecialchars($iv['Location']); ?></div>
                            </div>
                            <?php if (!empty($iv['Notes'])): ?>
                            <div class="detail"><i class="fas fa-sticky-note"></i><?php echo htmlspecialchars($iv['Notes']); ?></div>
                            <?php endif; ?>
                            <?php if (!empty($iv['MeetingLink']) && $iv['InterviewMode'] === 'virtual'): ?>
                            <div class="detail">
                                <i class="fas fa-link"></i>
                                <a href="<?php echo htmlspecialchars($iv['MeetingLink']); ?>" target="_blank" style="color: var(--accent); text-decoration: none;">
                                    Join Meeting
                                </a>
                            </div>
                            <?php endif; ?>
                            <div class="detail"><i class="fas fa-envelope"></i><?php echo htmlspecialchars($iv['CompanyEmail']); ?></div>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="section">
                    <div class="section-header">
                        <div class="section-title">Past Interviews</div>
                    </div>
                    <div class="section-body" id="pastContainer">
                        <?php if (empty($pastInterviews)): ?>
                        <div style="text-align: center; color: var(--text-secondary); padding: 40px;">
                            <i class="fas fa-history" style="font-size: 48px; margin-bottom: 16px; opacity: 0.5;"></i>
                            <div style="font-size: 18px; margin-bottom: 8px;">No Past Interviews</div>
                            <div style="font-size: 14px;">Your interview history will appear here once you have completed interviews.</div>
                        </div>
                        <?php else: ?>
                        <?php foreach ($pastInterviews as $iv): ?>
                        <div class="interview-card" data-id="<?php echo $iv['InterviewID']; ?>">
                            <div class="interview-title"><?php echo htmlspecialchars($iv['InterviewTitle']); ?></div>
                            <div class="interview-company"><i class="fas fa-building"></i><?php echo htmlspecialchars($iv['CompanyName']); ?></div>
                            <div class="detail-row">
                                <div class="detail"><i class="fas fa-calendar"></i><?php echo date('F j, Y', strtotime($iv['ScheduledDate'])); ?></div>
                                <div class="detail"><i class="fas fa-clock"></i><?php echo date('g:i A', strtotime($iv['ScheduledTime'])); ?></div>
                                <div class="detail"><i class="fas fa-info-circle"></i><?php echo ucfirst($iv['Status']); ?></div>
                                <?php if (!empty($iv['Rating'])): ?>
                                <div class="detail"><i class="fas fa-star"></i>Rating: <?php echo $iv['Rating']; ?>/5</div>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($iv['Feedback'])): ?>
                            <div class="detail"><i class="fas fa-comment"></i>Feedback: <?php echo htmlspecialchars($iv['Feedback']); ?></div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Interview Calendar Section -->
            <div class="section" style="margin-top: 32px;">
                <div class="section-header">
                    <div class="section-title">
                        <i class="fas fa-calendar-alt" style="margin-right: 8px;"></i>
                        Interview Calendar
                    </div>
                    <div style="font-size: 14px; color: var(--text-secondary); margin-top: 8px;">
                        <span style="color: var(--danger); font-weight: 600;">●</span> Conflict dates &nbsp;&nbsp;
                        <span style="color: var(--success); font-weight: 600;">●</span> Available dates &nbsp;&nbsp;
                        <span style="color: var(--accent); font-weight: 600;">●</span> Today
                    </div>
                </div>
                <div class="section-body">
                    <div id="interviewCalendar"></div>
                    <div id="calendarDetails" style="margin-top: 24px; display: none;">
                        <div class="interview-details-popup">
                            <div id="detailsContent"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="toast" id="toast"><i class="fas fa-info-circle"></i><span id="toastMsg">Saved</span></div>

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
        (function(){
        'use strict';
        
        // Toast functionality for notifications
        const toast = document.getElementById('toast');
        const toastMsg = document.getElementById('toastMsg');

        function showToast(msg, type = 'info') {
            if (toast && toastMsg) {
                toastMsg.textContent = msg;
                toast.classList.add('show');
                setTimeout(() => toast.classList.remove('show'), 2500);
            }
        }
        
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
        
        // Make function globally accessible
        window.closeProfileEditPopup = closeProfileEditPopup;

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
        
        // Logout
        (function(){ var b=document.getElementById('logoutBtn'); if(b){ b.addEventListener('click', function(){ window.location.href = 'Login&Signup.php'; }); } })();


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


        // Calendar functionality
        let currentMonth = new Date().getMonth();
        let currentYear = new Date().getFullYear();
        let allInterviews = [];
        let upcomingInterviews = [];
        let pastInterviews = [];
        
        try {
            allInterviews = <?php 
                $mergedInterviews = array_merge($upcomingInterviews, $pastInterviews);
                echo json_encode($mergedInterviews, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
            ?>;
            upcomingInterviews = <?php echo json_encode($upcomingInterviews, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
            pastInterviews = <?php echo json_encode($pastInterviews, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
        } catch (error) {
            console.error('Error loading interview data:', error);
            allInterviews = [];
            upcomingInterviews = [];
            pastInterviews = [];
        }
        
        // Debug: Log the interviews data
        console.log('All interviews:', allInterviews);
        console.log('Upcoming interviews:', <?php echo json_encode($upcomingInterviews); ?>);
        console.log('Past interviews:', <?php echo json_encode($pastInterviews); ?>);
        console.log('Calendar element exists:', document.getElementById('interviewCalendar') !== null);

        window.generateCalendar = function(year, month) {
            console.log('Generating calendar for:', year, month);
            const calendar = document.getElementById('interviewCalendar');
            console.log('Calendar element:', calendar);
            
            if (!calendar) {
                console.error('Calendar element not found!');
                return;
            }
            
            const firstDay = new Date(year, month, 1);
            const lastDay = new Date(year, month + 1, 0);
            const daysInMonth = lastDay.getDate();
            const startingDayOfWeek = firstDay.getDay();
            
            const monthNames = [
                'January', 'February', 'March', 'April', 'May', 'June',
                'July', 'August', 'September', 'October', 'November', 'December'
            ];
            
            const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
            
            let calendarHTML = `
                <div class="calendar-navigation" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding: 0 10px;">
                    <button class="btn btn-secondary btn-small" onclick="previousMonth()" style="padding: 8px 12px;">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <h3 style="margin: 0; color: var(--text-primary); font-size: 18px; font-weight: 600;">${monthNames[month]} ${year}</h3>
                    <button class="btn btn-secondary btn-small" onclick="nextMonth()" style="padding: 8px 12px;">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
                <div class="calendar-grid">
            `;
            
            // Add day headers
            dayNames.forEach(day => {
                calendarHTML += `<div class="calendar-header">${day}</div>`;
            });
            
            // Add empty cells for days before the first day of the month
            for (let i = 0; i < startingDayOfWeek; i++) {
                calendarHTML += `<div class="calendar-day other-month">${new Date(year, month, i - startingDayOfWeek + 1).getDate()}</div>`;
            }
            
            // Add days of the month
            for (let day = 1; day <= daysInMonth; day++) {
                const dateString = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                const dayInterviews = getInterviewsForDate(dateString);
                const hasConflict = dayInterviews.length > 1;
                const hasInterview = dayInterviews.length > 0;
                const isToday = new Date().toDateString() === new Date(year, month, day).toDateString();
                
                let className = 'calendar-day';
                if (hasConflict) className += ' has-conflict';
                else if (hasInterview) className += ' has-interview';
                if (isToday) className += ' today';
                
                calendarHTML += `
                    <div class="${className}" onclick="showDateDetails('${dateString}', ${hasConflict})">
                        ${day}
                    </div>
                `;
            }
            
            // Add empty cells for days after the last day of the month
            const totalCells = startingDayOfWeek + daysInMonth;
            const remainingCells = 42 - totalCells; // 6 weeks * 7 days
            for (let i = 1; i <= remainingCells; i++) {
                calendarHTML += `<div class="calendar-day other-month">${i}</div>`;
            }
            
            calendarHTML += '</div>';
            calendar.innerHTML = calendarHTML;
            
            console.log('Calendar generated successfully');
        }

        window.getInterviewsForDate = function(dateString) {
            return allInterviews.filter(interview => interview.ScheduledDate === dateString);
        };

        window.showDateDetails = function(dateString, hasConflict) {
            const dayInterviews = getInterviewsForDate(dateString);
            const detailsDiv = document.getElementById('calendarDetails');
            const contentDiv = document.getElementById('detailsContent');
            
            if (dayInterviews.length === 0) {
                detailsDiv.style.display = 'none';
                return;
            }
            
            let detailsHTML = `<h3 style="margin-bottom: 15px; color: var(--text-primary);">${new Date(dateString).toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}</h3>`;
            
            if (hasConflict) {
                detailsHTML += `<div class="conflict-interviews">`;
                detailsHTML += `<h4 style="color: var(--danger); margin-bottom: 10px;"><i class="fas fa-exclamation-triangle"></i> Conflict Detected - Multiple Interviews Scheduled</h4>`;
                
                dayInterviews.forEach(interview => {
                    detailsHTML += `
                        <div class="conflict-interview">
                            <h4>${interview.InterviewTitle}</h4>
                            <p><strong>Company:</strong> ${interview.CompanyName}</p>
                            <p><strong>Time:</strong> ${new Date(interview.ScheduledDate + 'T' + interview.ScheduledTime).toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' })}</p>
                            <p><strong>Mode:</strong> ${interview.InterviewMode}</p>
                            ${interview.MeetingLink ? `<p><strong>Meeting Link:</strong> <a href="${interview.MeetingLink}" target="_blank" style="color: var(--accent);">Join Meeting</a></p>` : ''}
                        </div>
                    `;
                });
                
                detailsHTML += `</div>`;
            } else {
                const interview = dayInterviews[0];
                detailsHTML += `
                    <div class="interview-detail-item">
                        <span class="interview-detail-label">Interview Title:</span>
                        <span class="interview-detail-value">${interview.InterviewTitle}</span>
                    </div>
                    <div class="interview-detail-item">
                        <span class="interview-detail-label">Company:</span>
                        <span class="interview-detail-value">${interview.CompanyName}</span>
                    </div>
                    <div class="interview-detail-item">
                        <span class="interview-detail-label">Time:</span>
                        <span class="interview-detail-value">${new Date(interview.ScheduledDate + 'T' + interview.ScheduledTime).toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' })}</span>
                    </div>
                    <div class="interview-detail-item">
                        <span class="interview-detail-label">Mode:</span>
                        <span class="interview-detail-value">${interview.InterviewMode}</span>
                    </div>
                    <div class="interview-detail-item">
                        <span class="interview-detail-label">Platform:</span>
                        <span class="interview-detail-value">${interview.Platform}</span>
                    </div>
                    <div class="interview-detail-item">
                        <span class="interview-detail-label">Location:</span>
                        <span class="interview-detail-value">${interview.Location}</span>
                    </div>
                    ${interview.MeetingLink ? `
                    <div class="interview-detail-item">
                        <span class="interview-detail-label">Meeting Link:</span>
                        <span class="interview-detail-value">
                            <a href="${interview.MeetingLink}" target="_blank" style="color: var(--accent);">Join Meeting</a>
                        </span>
                    </div>
                    ` : ''}
                    ${interview.Notes ? `
                    <div class="interview-detail-item">
                        <span class="interview-detail-label">Notes:</span>
                        <span class="interview-detail-value">${interview.Notes}</span>
                    </div>
                    ` : ''}
                `;
            }
            
            contentDiv.innerHTML = detailsHTML;
            detailsDiv.style.display = 'block';
        }

        // Make navigation functions global
        window.previousMonth = function() {
            currentMonth--;
            if (currentMonth < 0) {
                currentMonth = 11;
                currentYear--;
            }
            generateCalendar(currentYear, currentMonth);
        };

        window.nextMonth = function() {
            currentMonth++;
            if (currentMonth > 11) {
                currentMonth = 0;
                currentYear++;
            }
            generateCalendar(currentYear, currentMonth);
        };

        // Auto-refresh functionality to remove past interviews
        function checkAndRemovePastInterviews() {
            const currentDateTime = new Date();
            console.log('Checking for expired interviews at:', currentDateTime.toLocaleString());
            
            // Use the actual interview data from PHP instead of parsing HTML
            allInterviews.forEach(interview => {
                // Only check upcoming interviews (those that should be in the upcoming section)
                const upcomingInterviewIds = upcomingInterviews.map(up => up.InterviewID);
                if (!upcomingInterviewIds.includes(interview.InterviewID)) {
                    return; // Skip if not an upcoming interview
                }
                
                // Create interview datetime from the raw data
                const interviewDateTime = new Date(interview.ScheduledDate + 'T' + interview.ScheduledTime);
                
                // Calculate time difference in minutes
                const timeDiff = interviewDateTime - currentDateTime;
                const timeDiffMinutes = Math.floor(timeDiff / (1000 * 60));
                
                // Find the corresponding card in the DOM
                const card = document.querySelector(`.interview-card[data-id="${interview.InterviewID}"]`);
                if (!card) return;
                
                // Remove existing status classes
                card.classList.remove('interview-expiring', 'interview-expired');
                
                // If interview time has passed, fade out the card
                if (interviewDateTime < currentDateTime) {
                    card.classList.add('interview-expired');
                    card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                    card.style.opacity = '0';
                    card.style.transform = 'translateX(-100%)';
                    
                    console.log(`Interview "${interview.InterviewTitle}" has expired, removing from upcoming section`);
                    
                    setTimeout(() => {
                        card.remove();
                        // Refresh the page after a short delay to update the server-side data
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    }, 500);
                } else if (timeDiffMinutes <= 30 && timeDiffMinutes > 0) {
                    // If interview is within 30 minutes, add warning styling
                    card.classList.add('interview-expiring');
                    console.log(`Interview "${interview.InterviewTitle}" expiring in ${timeDiffMinutes} minutes`);
                    
                    // Show notification for interviews expiring soon
                    if (timeDiffMinutes <= 15 && !card.hasAttribute('notification-shown')) {
                        card.setAttribute('notification-shown', 'true');
                        showInterviewNotification(`Interview "${interview.InterviewTitle}" starts in ${timeDiffMinutes} minutes!`);
                    }
                }
            });
        }
        
        // Show interview notification
        function showInterviewNotification(message) {
            // Create notification element
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: linear-gradient(135deg, var(--accent-secondary), #f59e0b);
                color: white;
                padding: 15px 20px;
                border-radius: 8px;
                z-index: 10000;
                font-weight: 500;
                box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
                animation: slideInNotification 0.3s ease-out;
                max-width: 300px;
                border-left: 4px solid #d97706;
            `;
            
            notification.innerHTML = `
                <div style="display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-clock" style="font-size: 18px;"></i>
                    <span>${message}</span>
                </div>
            `;
            
            // Add CSS animation
            if (!document.getElementById('notification-styles')) {
                const style = document.createElement('style');
                style.id = 'notification-styles';
                style.textContent = `
                    @keyframes slideInNotification {
                        from {
                            transform: translateX(100%);
                            opacity: 0;
                        }
                        to {
                            transform: translateX(0);
                            opacity: 1;
                        }
                    }
                `;
                document.head.appendChild(style);
            }
            
            document.body.appendChild(notification);
            
            // Remove notification after 5 seconds
            setTimeout(() => {
                notification.style.animation = 'slideInNotification 0.3s ease-out reverse';
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.remove();
                    }
                }, 300);
            }, 5000);
        }
        
        // Initialize theme when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded, initializing calendar...');
            console.log('Current year:', currentYear, 'Current month:', currentMonth);
            console.log('All interviews length:', allInterviews.length);
            
            initializeTheme();
            setupThemeToggle();
            setupProfileEditing();
            
            // Ensure calendar is generated
            setTimeout(function() {
                generateCalendar(currentYear, currentMonth);
            }, 100);
            
            // Check for past interviews every 30 seconds for better responsiveness
            setInterval(checkAndRemovePastInterviews, 30000);
            
            // Initial check
            setTimeout(checkAndRemovePastInterviews, 2000);
        });
        
        })();
    </script>
</body>
</html>

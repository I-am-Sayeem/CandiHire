<?php
// company_mcq_results.php - Company view of MCQ exam results
require_once 'session_manager.php';
require_once 'Database.php';

// Check if company is logged in
if (!isCompanyLoggedIn()) {
    header('Location: Login&Signup.php');
    exit;
}

// Get company ID from session
$sessionCompanyId = getCurrentCompanyId();
$companyName = $_SESSION['company_name'] ?? 'Company';

// Load company data
$companyLogo = null;
try {
    if (isset($pdo) && $pdo instanceof PDO) {
        $stmt = $pdo->prepare("SELECT CompanyName, Logo FROM Company_login_info WHERE CompanyID = ?");
        $stmt->execute([$sessionCompanyId]);
        $company = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($company) {
            $companyName = $company['CompanyName'];
            $companyLogo = $company['Logo'];
        }
    }
} catch (Exception $e) {
    error_log("Error loading company data: " . $e->getMessage());
}

// Get all job positions for this company
$jobPositions = [];
try {
    if (isset($pdo) && $pdo instanceof PDO) {
        $stmt = $pdo->prepare("
            SELECT DISTINCT JobID, JobTitle, Department 
            FROM job_postings 
            WHERE CompanyID = ? 
            ORDER BY JobTitle
        ");
        $stmt->execute([$sessionCompanyId]);
        $jobPositions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    error_log("Error loading job positions: " . $e->getMessage());
}

    // Get selected position from URL
    $selectedJobId = $_GET['job_id'] ?? null;
    $selectedPosition = null;
    $examResults = [];

    if ($selectedJobId && isset($pdo) && $pdo instanceof PDO) {
        // Get job details
        try {
            $stmt = $pdo->prepare("SELECT JobTitle, Department FROM job_postings WHERE JobID = ? AND CompanyID = ?");
            $stmt->execute([$selectedJobId, $sessionCompanyId]);
            $selectedPosition = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error loading selected position: " . $e->getMessage());
        }
    
        // Get exam results for this position
        if ($selectedPosition) {
            try {
                $stmt = $pdo->prepare("
                SELECT 
                    ea.AssignmentID,
                    ea.ExamID,
                    ea.CandidateID,
                    ea.AssignmentDate,
                    ea.Status,
                    ea.DueDate,
                    e.ExamTitle,
                    e.QuestionCount,
                    e.PassingScore,
                    cli.FullName as CandidateName,
                    cli.Email as CandidateEmail,
                    cli.PhoneNumber,
                    cli.WorkType,
                    cli.Location,
                    cli.Skills,
                    jp.JobTitle,
                    ea.Score,
                    ea.CorrectAnswers,
                    ea.TotalQuestions,
                    ea.TimeSpent,
                    ea.CompletedAt
                FROM exam_assignments ea
                JOIN exams e ON ea.ExamID = e.ExamID
                JOIN job_postings jp ON ea.JobID = jp.JobID
                JOIN candidate_login_info cli ON ea.CandidateID = cli.CandidateID
                WHERE ea.JobID = ? AND jp.CompanyID = ?
                ORDER BY ea.CompletedAt DESC, ea.AssignmentDate DESC
            ");
                $stmt->execute([$selectedJobId, $sessionCompanyId]);
                $examResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                error_log("Error loading exam results: " . $e->getMessage());
            }
        }
    }

// Function to get status color
function getStatusColor($status) {
    switch ($status) {
        case 'assigned':
            return '#f59e0b';
        case 'completed':
            return '#3fb950';
        case 'failed':
            return '#f85149';
        default:
            return '#8b949e';
    }
}

// Function to get score color
function getScoreColor($score, $passingScore) {
    if (!$score) return '#8b949e';
    $scoreValue = floatval($score);
    $passingValue = floatval($passingScore);
    return $scoreValue >= $passingValue ? '#3fb950' : '#f85149';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MCQ Results - CandiHire</title>
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
            --warning: #d29922;
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
            --warning: #9a6700;
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
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            will-change: transform, background-color, box-shadow;
            font-size: 15px;
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
            transition: left 0.6s ease;
        }

        .nav-item:hover {
            background: linear-gradient(135deg, var(--accent), var(--accent-hover));
            color: white;
            transform: translateX(5px) scale(1.02);
            box-shadow: 0 8px 25px rgba(88, 166, 255, 0.3);
        }

        .nav-item:hover::before {
            left: 100%;
        }

        .nav-item.active {
            background-color: var(--bg-tertiary);
            color: var(--accent);
            font-weight: 500;
        }

        .nav-item i {
            font-size: 20px;
            width: 24px;
            text-align: center;
        }

        /* Welcome Section */
        .welcome-section {
            background: var(--bg-tertiary);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid var(--border);
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
    color: white !important;
}

.theme-toggle-btn:active {
    transform: translateY(-1px);
}

/* IMPORTANT: Icon colors based on theme */
button#themeToggleBtn[data-theme="dark"] #themeIcon {
    color: #ffd700 !important;
    text-shadow: 0 0 10px rgba(255, 215, 0, 0.3);
}

button#themeToggleBtn[data-theme="light"] #themeIcon {
    color: #ffa500 !important;
    text-shadow: 0 0 10px rgba(255, 165, 0, 0.3);
}

        /* Ensure theme icon specifically inherits color */
        #themeIcon {
            color: inherit !important;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            padding: 20px;
            display: flex;
            flex-direction: column;
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

        /* Position Selector */
        .position-selector {
            background-color: var(--bg-secondary);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid var(--border);
        }

        .selector-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 15px;
            color: var(--text-primary);
        }

        .position-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 15px;
        }

        .position-card {
            background-color: var(--bg-tertiary);
            border-radius: 8px;
            padding: 15px;
            border: 2px solid var(--border);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .position-card:hover {
            border-color: var(--accent);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(88, 166, 255, 0.2);
        }

        .position-card.selected {
            border-color: var(--accent);
            background-color: rgba(88, 166, 255, 0.1);
        }

        .position-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 5px;
        }

        .position-department {
            font-size: 14px;
            color: var(--text-secondary);
        }

        /* Results Section */
        .results-section {
            background-color: var(--bg-secondary);
            border-radius: 12px;
            border: 1px solid var(--border);
            overflow: hidden;
        }

        .section-header {
            padding: 20px;
            border-bottom: 1px solid var(--border);
            background-color: var(--bg-tertiary);
        }

        .section-title {
            font-size: 20px;
            font-weight: 600;
            color: var(--text-primary);
        }

        .section-body {
            padding: 20px;
        }

        .results-grid {
            display: grid;
            gap: 15px;
        }

        .result-card {
            background-color: var(--bg-tertiary);
            border-radius: 8px;
            padding: 20px;
            border: 1px solid var(--border);
            transition: all 0.3s ease;
        }

        .result-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .result-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .candidate-info {
            flex: 1;
        }

        .candidate-name {
            font-size: 18px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 5px;
        }

        .candidate-details {
            font-size: 14px;
            color: var(--text-secondary);
            margin-bottom: 3px;
        }

        .exam-info {
            text-align: right;
        }

        .exam-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--accent);
            margin-bottom: 5px;
        }

        .exam-date {
            font-size: 14px;
            color: var(--text-secondary);
        }

        .result-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }

        .stat-item {
            text-align: center;
            padding: 10px;
            background-color: var(--bg-primary);
            border-radius: 6px;
            border: 1px solid var(--border);
        }

        .stat-label {
            font-size: 12px;
            color: var(--text-secondary);
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-value {
            font-size: 18px;
            font-weight: 600;
            color: var(--text-primary);
        }

        .result-status {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 15px;
            border-top: 1px solid var(--border);
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            gap: 6px;
        }

        .status-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background-color: currentColor;
        }

        .score-badge {
            font-size: 16px;
            font-weight: 600;
            padding: 8px 16px;
            border-radius: 20px;
            color: white;
        }

        .result-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .btn-small {
            padding: 6px 12px;
            font-size: 12px;
        }

        .btn-success {
            background-color: var(--success);
            color: white;
        }

        .btn-success:hover {
            background-color: #2da04e;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(63, 185, 80, 0.3);
        }

        .btn-danger {
            background-color: var(--danger);
            color: white;
        }

        .btn-danger:hover {
            background-color: #d03f39;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(248, 81, 73, 0.3);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-secondary);
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 20px;
            color: var(--text-secondary);
        }

        .empty-state h3 {
            font-size: 20px;
            margin-bottom: 10px;
            color: var(--text-primary);
        }

        .empty-state p {
            font-size: 14px;
            line-height: 1.6;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .container {
                flex-direction: column;
            }
            
            .left-nav {
                width: 100%;
                height: auto;
                position: relative;
            }
            
            .position-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            }
        }

        @media (max-width: 768px) {
            .left-nav {
                display: none;
            }
            
            .main-content {
                padding: 10px;
            }
            
            .page-header {
                flex-direction: column;
                gap: 16px;
                align-items: stretch;
            }
            
            .header-actions {
                flex-direction: column;
            }
            
            .result-stats {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .result-header {
                flex-direction: column;
                gap: 15px;
            }
            
            .exam-info {
                text-align: left;
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
            <div class="welcome-section">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div id="companyLogo" style="width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 16px; <?php echo $companyLogo ? 'background-image: url(' . $companyLogo . '); background-size: cover; background-position: center;' : 'background: linear-gradient(135deg, var(--accent-secondary), #e67e22);'; ?>">
                        <?php echo $companyLogo ? '' : strtoupper(substr($companyName, 0, 1)); ?>
                    </div>
                    <div>
                        <div style="color: var(--text-primary); font-weight: 600; font-size: 14px;">Welcome back!</div>
                        <div id="companyNameDisplay" style="color: var(--text-secondary); font-size: 12px;"><?php echo htmlspecialchars($companyName); ?></div>
                    </div>
                </div>
                <button id="editProfileBtn" 
                    style="background: var(--accent-secondary); 
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
                <div class="nav-item active">
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
            <!-- Page Header -->
            <div class="page-header">
                <h1 class="page-title">MCQ Results</h1>
                <div class="header-actions">
                    <button class="btn btn-secondary" onclick="window.location.reload()">
                        <i class="fas fa-sync-alt"></i>
                        Refresh
                    </button>
                </div>
            </div>

            <!-- Position Selector -->
            <div class="position-selector">
                <h2 class="selector-title">Select Job Position</h2>
                <?php if (empty($jobPositions)): ?>
                    <div class="empty-state">
                        <i class="fas fa-briefcase"></i>
                        <h3>No Job Positions</h3>
                        <p>You haven't posted any job positions yet. Create a job posting to start receiving applications and exam results.</p>
                        <a href="JobPost.php" class="btn btn-primary" style="margin-top: 15px;">
                            <i class="fas fa-plus"></i>
                            Create Job Posting
                        </a>
                    </div>
                <?php else: ?>
                    <div class="position-grid">
                        <?php foreach ($jobPositions as $position): ?>
                            <div class="position-card <?php echo ($selectedJobId == $position['JobID']) ? 'selected' : ''; ?>" 
                                 onclick="window.location.href='?job_id=<?php echo $position['JobID']; ?>'">
                                <div class="position-title"><?php echo htmlspecialchars($position['JobTitle']); ?></div>
                                <div class="position-department"><?php echo htmlspecialchars($position['Department']); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Results Section -->
            <?php if ($selectedJobId && $selectedPosition): ?>
                <div class="results-section">
                    <div class="section-header">
                        <h2 class="section-title">
                            Exam Results - <?php echo htmlspecialchars($selectedPosition['JobTitle']); ?>
                        </h2>
                    </div>
                    <div class="section-body">
                        <?php if (empty($examResults)): ?>
                            <div class="empty-state">
                                <i class="fas fa-clipboard-list"></i>
                                <h3>No Exam Results</h3>
                                <p>No candidates have taken exams for this position yet. Exam results will appear here once candidates complete their assessments.</p>
                            </div>
                        <?php else: ?>
                            <div class="results-grid">
                                <?php foreach ($examResults as $result): ?>
                                    <div class="result-card">
                                        <div class="result-header">
                                            <div class="candidate-info">
                                                <div class="candidate-name"><?php echo htmlspecialchars($result['CandidateName']); ?></div>
                                                <div class="candidate-details">
                                                    <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($result['CandidateEmail']); ?>
                                                </div>
                                                <?php if ($result['PhoneNumber']): ?>
                                                    <div class="candidate-details">
                                                        <i class="fas fa-phone"></i> <?php echo htmlspecialchars($result['PhoneNumber']); ?>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if ($result['Location']): ?>
                                                    <div class="candidate-details">
                                                        <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($result['Location']); ?>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if ($result['WorkType']): ?>
                                                    <div class="candidate-details">
                                                        <i class="fas fa-briefcase"></i> <?php echo ucfirst($result['WorkType']); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="exam-info">
                                                <div class="exam-title"><?php echo htmlspecialchars($result['ExamTitle']); ?></div>
                                                <div class="exam-date">
                                                    <?php if ($result['CompletedAt']): ?>
                                                        Completed: <?php echo date('M j, Y g:i A', strtotime($result['CompletedAt'])); ?>
                                                    <?php else: ?>
                                                        Assigned: <?php echo date('M j, Y', strtotime($result['AssignmentDate'])); ?>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="result-stats">
                                            <div class="stat-item">
                                                <div class="stat-label">Score</div>
                                                <div class="stat-value" style="color: <?php echo getScoreColor($result['Score'], $result['PassingScore']); ?>;">
                                                    <?php echo $result['Score'] ? number_format($result['Score'], 1) . '%' : 'N/A'; ?>
                                                </div>
                                            </div>
                                            <div class="stat-item">
                                                <div class="stat-label">Correct</div>
                                                <div class="stat-value"><?php echo $result['CorrectAnswers'] . '/' . $result['TotalQuestions']; ?></div>
                                            </div>
                                            <div class="stat-item">
                                                <div class="stat-label">Passing Score</div>
                                                <div class="stat-value"><?php echo $result['PassingScore']; ?>%</div>
                                            </div>
                                            <?php if ($result['TimeSpent']): ?>
                                                <div class="stat-item">
                                                    <div class="stat-label">Time Spent</div>
                                                    <div class="stat-value"><?php echo gmdate('H:i:s', $result['TimeSpent']); ?></div>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <div class="result-status">
                                            <span class="status-badge" style="color: <?php echo getStatusColor($result['Status']); ?>; border: 1px solid <?php echo getStatusColor($result['Status']); ?>;">
                                                <span class="status-dot"></span>
                                                <?php echo ucfirst($result['Status']); ?>
                                            </span>
                                            <?php if ($result['Score']): ?>
                                                <span class="score-badge" style="background-color: <?php echo getScoreColor($result['Score'], $result['PassingScore']); ?>;">
                                                    <?php echo number_format($result['Score'], 1); ?>%
                                                </span>
                                            <?php endif; ?>
                                        </div>

                                        <?php if ($result['Skills']): ?>
                                            <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid var(--border);">
                                                <div style="font-size: 12px; color: var(--text-secondary); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;">Skills</div>
                                                <div style="font-size: 14px; color: var(--text-primary);"><?php echo htmlspecialchars($result['Skills']); ?></div>
                                            </div>
                                        <?php endif; ?>

                                        <div class="result-actions">
                                            <?php if ($result['Status'] === 'completed'): ?>
                                                <?php if ($result['Score'] >= $result['PassingScore']): ?>
                                                    <span class="status-badge" style="color: var(--success); border: 1px solid var(--success);">
                                                        <i class="fas fa-check"></i>
                                                        Passed
                                                    </span>
                                                <?php else: ?>
                                                    <span class="status-badge" style="color: var(--danger); border: 1px solid var(--danger);">
                                                        <i class="fas fa-times"></i>
                                                        Failed
                                                    </span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="status-badge" style="color: var(--warning); border: 1px solid var(--warning);">
                                                    <i class="fas fa-clock"></i>
                                                    Pending
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php elseif ($selectedJobId): ?>
                <div class="results-section">
                    <div class="section-header">
                        <h2 class="section-title">Position Not Found</h2>
                    </div>
                    <div class="section-body">
                        <div class="empty-state">
                            <i class="fas fa-exclamation-triangle"></i>
                            <h3>Position Not Found</h3>
                            <p>The selected job position could not be found or you don't have permission to view it.</p>
                            <button class="btn btn-primary" onclick="window.location.href='company_mcq_results.php'" style="margin-top: 15px;">
                                <i class="fas fa-arrow-left"></i>
                                Back to Positions
                            </button>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="results-section">
                    <div class="section-header">
                        <h2 class="section-title">Select a Position</h2>
                    </div>
                    <div class="section-body">
                        <div class="empty-state">
                            <i class="fas fa-mouse-pointer"></i>
                            <h3>Choose a Job Position</h3>
                            <p>Select a job position from the list above to view MCQ exam results for candidates who applied for that position.</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Company Profile Edit Popup -->
    <div id="companyProfileEditPopup" class="popup-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.7); z-index: 10000; backdrop-filter: blur(5px);">
        <div class="popup-content" style="background: var(--bg-secondary); border-radius: 16px; padding: 30px; width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto; border: 1px solid var(--border); box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3); margin: 5% auto;">
            <div class="popup-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; padding-bottom: 15px; border-bottom: 1px solid var(--border);">
                <div class="popup-title" style="font-size: 24px; font-weight: 600; color: var(--accent-secondary); display: flex; align-items: center; gap: 10px;">
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
                    <button type="submit" class="btn btn-primary" id="submitCompanyProfileUpdate" style="padding: 12px 24px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 14px; transition: all 0.2s; display: flex; align-items: center; gap: 8px; background: var(--accent-secondary); color: white;">
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
            
            document.body.style.transition = 'background-color 0.3s ease, color 0.3s ease';
            setTimeout(() => {
                document.body.style.transition = '';
            }, 300);
        }

        function updateThemeButton(theme) {
            const themeIcon = document.getElementById('themeIcon');
            const themeText = document.getElementById('themeText');
            const themeToggleBtn = document.getElementById('themeToggleBtn');
            
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
            
            // Ensure icon color matches text color
            if (themeIcon) {
                themeIcon.style.color = 'inherit';
            }
        }

        function setupThemeToggle() {
            const themeToggleBtn = document.getElementById('themeToggleBtn');
            if (themeToggleBtn) {
                themeToggleBtn.addEventListener('click', toggleTheme);
            }
        }

        // Logout functionality
        document.getElementById('logoutBtn').addEventListener('click', function() {
            window.location.href = 'logout.php';
        });

        // Removed viewCandidateDetails and contactCandidate functions as buttons were removed

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
                        // Update the company name in the profile section
                        const companyNameElement = document.querySelector('.company-name');
                        if (companyNameElement) {
                            companyNameElement.textContent = data.companyName;
                        }
                        
                        // Update logo if provided
                        const logo = document.querySelector('.company-logo');
                        if (data.logo && logo) {
                            // Show company logo
                            logo.style.backgroundImage = `url(${data.logo})`;
                            logo.style.backgroundSize = 'cover';
                            logo.style.backgroundPosition = 'center';
                            logo.textContent = '';
                        } else if (logo) {
                            // Show initials
                            logo.style.backgroundImage = '';
                            logo.style.background = 'linear-gradient(135deg, var(--accent-secondary), #e67e22)';
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

        // Simple Navigation without Page Transitions
        document.querySelectorAll('.nav-item').forEach(item => {
            item.addEventListener('click', function(e) {
                // Check if this is a navigation link (has onclick with window.location)
                const onclickAttr = this.getAttribute('onclick');
                if (onclickAttr && onclickAttr.includes('window.location.href')) {
                    e.preventDefault();
                    
                    // Extract URL from onclick attribute and navigate immediately
                    const urlMatch = onclickAttr.match(/'([^']+)'/);
                    if (urlMatch && urlMatch[1]) {
                        window.location.href = urlMatch[1];
                    }
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

        // Initialize theme when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            initializeTheme();
            setupThemeToggle();
            setupCompanyProfileEditing();
        });
    </script>
</body>
</html>

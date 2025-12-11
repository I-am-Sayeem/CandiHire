<?php
// JobPost.php - Job Posts Management for Companies
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
    <title>CandiHire - Job Posts</title>
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
            --shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
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
            --shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
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
            will-change: transform, background-color, color;
            font-size: 15px;
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
            max-width: 100%;
        }

        /* Job Posts Header */
        .job-posts-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            flex-wrap: wrap;
            gap: 16px;
        }

        .search-filter-container {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
            flex: 1;
        }

        .search-box {
            position: relative;
            flex: 1;
            min-width: 250px;
        }

        .search-box input {
            width: 100%;
            background-color: var(--bg-secondary);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 10px 16px 10px 40px;
            color: var(--text-primary);
            font-size: 15px;
        }

        .search-box i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
        }


        .header-actions {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .post-new-job-btn {
            background-color: var(--accent-1);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            cursor: pointer;
            font-weight: 600;
            font-size: 15px;
            transition: background-color 0.2s, transform 0.2s, box-shadow 0.2s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .post-new-job-btn:hover { background-color: var(--accent-hover); transform: translateY(-2px); box-shadow: 0 4px 10px rgba(0,0,0,0.2); }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background-color: var(--bg-secondary);
            border-radius: 8px;
            border: 1px solid var(--border);
        }

        .user-profile i {
            font-size: 20px;
            color: var(--text-secondary);
        }

        /* Job Posts Table */
        .job-posts-table-container {
            background-color: var(--bg-secondary);
            border-radius: 12px;
            border: 1px solid var(--border);
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .job-posts-table {
            width: 100%;
            border-collapse: collapse;
        }

        .job-posts-table th {
            background-color: var(--bg-tertiary);
            padding: 16px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .job-posts-table td {
            padding: 16px;
            border-top: 1px solid var(--border);
            font-size: 15px;
        }

        .job-posts-table tbody tr { transition: background-color 0.2s ease; }
        .job-posts-table tbody tr:hover { background-color: rgba(88, 166, 255, 0.05); }

        .job-title {
            font-weight: 600;
            color: var(--accent-1);
        }

        .department {
            color: var(--text-secondary);
        }


        .posted-date {
            color: var(--text-secondary);
        }

        .applications-count {
            text-align: center;
        }

        .application-count-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            background: var(--accent-1);
            color: white;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            transition: all 0.2s ease;
        }


        .application-count-badge.empty {
            background: var(--bg-tertiary);
            color: var(--text-secondary);
        }

        .actions {
            display: flex;
            gap: 8px;
        }

        .action-btn {
            background: none;
            border: none;
            color: var(--text-secondary);
            cursor: pointer;
            padding: 6px 8px;
            border-radius: 4px;
            transition: background-color 0.2s, color 0.2s, transform 0.2s;
        }

        .action-btn:hover { background-color: var(--bg-tertiary); color: var(--accent-1); transform: translateY(-1px); }

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
            .job-posts-table {
                font-size: 14px;
            }
            .job-posts-table th,
            .job-posts-table td {
                padding: 12px 8px;
            }
            .actions {
                flex-direction: column;
                gap: 4px;
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
                <div class="nav-item active">
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
            <!-- Job Posts Header -->
            <div class="job-posts-header">
                <div class="search-filter-container">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Search job posts...">
                    </div>
                </div>
                <div class="header-actions">
                    <button class="post-new-job-btn">
                        <i class="fas fa-plus"></i>
                        Post New Job
                    </button>
                    <div class="user-profile">
                        <i class="fas fa-user-circle"></i>
                        <span><?php echo htmlspecialchars($companyName); ?></span>
                    </div>
                </div>
            </div>

            <!-- Loading indicator -->
            <div id="loadingIndicator" style="text-align: center; padding: 40px; color: var(--text-secondary);">
                <i class="fas fa-spinner fa-spin" style="font-size: 24px; margin-right: 10px;"></i>
                Loading job posts...
            </div>

            <!-- Job Posts Table -->
            <div class="job-posts-table-container" id="jobPostsTableContainer" style="display: none;">
                <table class="job-posts-table" id="jobPostsTable">
                    <thead>
                        <tr>
                            <th>Job Title</th>
                            <th>Department</th>
                            <th>Posted Date</th>
                            <th>Applications</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="jobPostsTableBody">
                        <!-- Job posts will be loaded here -->
                    </tbody>
                </table>
            </div>

            <!-- No jobs message -->
            <div id="noJobsMessage" style="display: none; text-align: center; padding: 40px; color: var(--text-secondary);">
                <i class="fas fa-briefcase" style="font-size: 48px; margin-bottom: 16px; opacity: 0.5;"></i>
                <h3>No job posts found</h3>
                <p>You haven't posted any jobs yet. Click "Post New Job" to get started!</p>
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

    <!-- Job Post Modal -->
    <div id="jobPostModal" class="popup-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.7); z-index: 10000; backdrop-filter: blur(5px);">
        <div class="popup-content" style="background: var(--bg-secondary); border-radius: 16px; padding: 30px; width: 90%; max-width: 800px; max-height: 90vh; overflow-y: auto; border: 1px solid var(--border); box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3); margin: 5% auto;">
            <div class="popup-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; padding-bottom: 15px; border-bottom: 1px solid var(--border);">
                <div class="popup-title" style="font-size: 24px; font-weight: 600; color: var(--accent-2); display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-briefcase"></i>
                    <span id="jobModalTitle">Add New Job Post</span>
                </div>
                <button class="popup-close" onclick="closeJobPostModal()" style="background: none; border: none; color: var(--text-secondary); font-size: 24px; cursor: pointer; padding: 5px; border-radius: 50%; transition: all 0.2s;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="jobPostForm" enctype="multipart/form-data">
                <input type="hidden" id="jobId" name="jobId">
                <input type="hidden" name="action" id="jobAction" value="create_job">
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="jobTitle" style="display: block; color: var(--text-primary); font-weight: 500; margin-bottom: 8px; font-size: 14px;">Job Title *</label>
                        <input type="text" id="jobTitle" name="jobTitle" required placeholder="e.g., Senior Software Developer" style="width: 100%; background: var(--bg-primary); border: 1px solid var(--border); border-radius: 8px; padding: 12px 16px; color: var(--text-primary); font-size: 14px; transition: border-color 0.2s; box-sizing: border-box;">
                    </div>
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="department" style="display: block; color: var(--text-primary); font-weight: 500; margin-bottom: 8px; font-size: 14px;">Department</label>
                        <input type="text" id="department" name="department" placeholder="e.g., Engineering, Marketing" style="width: 100%; background: var(--bg-primary); border: 1px solid var(--border); border-radius: 8px; padding: 12px 16px; color: var(--text-primary); font-size: 14px; transition: border-color 0.2s; box-sizing: border-box;">
                    </div>
                </div>
                
                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="jobDescription" style="display: block; color: var(--text-primary); font-weight: 500; margin-bottom: 8px; font-size: 14px;">Job Description *</label>
                    <textarea id="jobDescription" name="jobDescription" required rows="4" placeholder="Describe the role, responsibilities, and what makes this position exciting..." style="width: 100%; background: var(--bg-primary); border: 1px solid var(--border); border-radius: 8px; padding: 12px 16px; color: var(--text-primary); font-size: 14px; transition: border-color 0.2s; box-sizing: border-box; resize: vertical; min-height: 80px;"></textarea>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="requirements" style="display: block; color: var(--text-primary); font-weight: 500; margin-bottom: 8px; font-size: 14px;">Requirements</label>
                        <textarea id="requirements" name="requirements" rows="3" placeholder="List the required qualifications, experience, and skills..." style="width: 100%; background: var(--bg-primary); border: 1px solid var(--border); border-radius: 8px; padding: 12px 16px; color: var(--text-primary); font-size: 14px; transition: border-color 0.2s; box-sizing: border-box; resize: vertical;"></textarea>
                    </div>
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="responsibilities" style="display: block; color: var(--text-primary); font-weight: 500; margin-bottom: 8px; font-size: 14px;">Responsibilities</label>
                        <textarea id="responsibilities" name="responsibilities" rows="3" placeholder="Outline the key responsibilities and daily tasks..." style="width: 100%; background: var(--bg-primary); border: 1px solid var(--border); border-radius: 8px; padding: 12px 16px; color: var(--text-primary); font-size: 14px; transition: border-color 0.2s; box-sizing: border-box; resize: vertical;"></textarea>
                    </div>
                </div>
                
                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="skills" style="display: block; color: var(--text-primary); font-weight: 500; margin-bottom: 8px; font-size: 14px;">Required Skills</label>
                    <input type="text" id="skills" name="skills" placeholder="e.g., JavaScript, React, Node.js, Python, SQL (comma-separated)" style="width: 100%; background: var(--bg-primary); border: 1px solid var(--border); border-radius: 8px; padding: 12px 16px; color: var(--text-primary); font-size: 14px; transition: border-color 0.2s; box-sizing: border-box;">
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="location" style="display: block; color: var(--text-primary); font-weight: 500; margin-bottom: 8px; font-size: 14px;">Location *</label>
                        <input type="text" id="location" name="location" required placeholder="e.g., New York, Remote, San Francisco" style="width: 100%; background: var(--bg-primary); border: 1px solid var(--border); border-radius: 8px; padding: 12px 16px; color: var(--text-primary); font-size: 14px; transition: border-color 0.2s; box-sizing: border-box;">
                    </div>
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="jobType" style="display: block; color: var(--text-primary); font-weight: 500; margin-bottom: 8px; font-size: 14px;">Job Type *</label>
                        <select id="jobType" name="jobType" required style="width: 100%; background: var(--bg-primary); border: 1px solid var(--border); border-radius: 8px; padding: 12px 16px; color: var(--text-primary); font-size: 14px; transition: border-color 0.2s; box-sizing: border-box; cursor: pointer;">
                            <option value="">Select Job Type</option>
                            <option value="full-time">Full-time</option>
                            <option value="part-time">Part-time</option>
                            <option value="contract">Contract</option>
                            <option value="freelance">Freelance</option>
                            <option value="internship">Internship</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="currency" style="display: block; color: var(--text-primary); font-weight: 500; margin-bottom: 8px; font-size: 14px;">Currency</label>
                        <select id="currency" name="currency" style="width: 100%; background: var(--bg-primary); border: 1px solid var(--border); border-radius: 8px; padding: 12px 16px; color: var(--text-primary); font-size: 14px; transition: border-color 0.2s; box-sizing: border-box; cursor: pointer;">
                            <option value="USD">USD</option>
                            <option value="EUR">EUR</option>
                            <option value="GBP">GBP</option>
                            <option value="BDT">BDT</option>
                        </select>
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="salaryMin" style="display: block; color: var(--text-primary); font-weight: 500; margin-bottom: 8px; font-size: 14px;">Minimum Salary</label>
                        <input type="number" id="salaryMin" name="salaryMin" placeholder="e.g., 50000" min="0" style="width: 100%; background: var(--bg-primary); border: 1px solid var(--border); border-radius: 8px; padding: 12px 16px; color: var(--text-primary); font-size: 14px; transition: border-color 0.2s; box-sizing: border-box;">
                    </div>
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="salaryMax" style="display: block; color: var(--text-primary); font-weight: 500; margin-bottom: 8px; font-size: 14px;">Maximum Salary</label>
                        <input type="number" id="salaryMax" name="salaryMax" placeholder="e.g., 80000" min="0" style="width: 100%; background: var(--bg-primary); border: 1px solid var(--border); border-radius: 8px; padding: 12px 16px; color: var(--text-primary); font-size: 14px; transition: border-color 0.2s; box-sizing: border-box;">
                    </div>
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="experienceLevel" style="display: block; color: var(--text-primary); font-weight: 500; margin-bottom: 8px; font-size: 14px;">Experience Level</label>
                        <select id="experienceLevel" name="experienceLevel" style="width: 100%; background: var(--bg-primary); border: 1px solid var(--border); border-radius: 8px; padding: 12px 16px; color: var(--text-primary); font-size: 14px; transition: border-color 0.2s; box-sizing: border-box; cursor: pointer;">
                            <option value="entry">Entry Level</option>
                            <option value="mid" selected>Mid Level</option>
                            <option value="senior">Senior Level</option>
                            <option value="lead">Lead Level</option>
                            <option value="executive">Executive</option>
                        </select>
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="educationLevel" style="display: block; color: var(--text-primary); font-weight: 500; margin-bottom: 8px; font-size: 14px;">Education Level</label>
                        <select id="educationLevel" name="educationLevel" style="width: 100%; background: var(--bg-primary); border: 1px solid var(--border); border-radius: 8px; padding: 12px 16px; color: var(--text-primary); font-size: 14px; transition: border-color 0.2s; box-sizing: border-box; cursor: pointer;">
                            <option value="high-school">High School</option>
                            <option value="associate">Associate</option>
                            <option value="bachelor" selected>Bachelor's</option>
                            <option value="master">Master's</option>
                            <option value="phd">PhD</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="closingDate" style="display: block; color: var(--text-primary); font-weight: 500; margin-bottom: 8px; font-size: 14px;">Application Deadline</label>
                        <input type="date" id="closingDate" name="closingDate" style="width: 100%; background: var(--bg-primary); border: 1px solid var(--border); border-radius: 8px; padding: 12px 16px; color: var(--text-primary); font-size: 14px; transition: border-color 0.2s; box-sizing: border-box;">
                    </div>
                </div>
                
                <div class="form-actions" style="display: flex; gap: 15px; justify-content: flex-end; margin-top: 30px; padding-top: 20px; border-top: 1px solid var(--border);">
                    <button type="button" class="btn btn-secondary" onclick="closeJobPostModal()" style="padding: 12px 24px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 14px; transition: all 0.2s; display: flex; align-items: center; gap: 8px; background: var(--bg-tertiary); color: var(--text-primary); border: 1px solid var(--border);">
                        <i class="fas fa-times"></i>
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary" id="submitJobPost" style="padding: 12px 24px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 14px; transition: all 0.2s; display: flex; align-items: center; gap: 8px; background: var(--accent-2); color: white;">
                        <i class="fas fa-save"></i>
                        <span id="submitJobText">Post Job</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Exam Creation Notification Popup -->
    <div id="examCreationNotificationPopup" class="popup-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.7); z-index: 10001; backdrop-filter: blur(5px);">
        <div class="popup-content" style="background: var(--bg-secondary); border-radius: 16px; padding: 30px; width: 90%; max-width: 500px; border: 1px solid var(--border); box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3); margin: 10% auto; text-align: center;">
            <div class="popup-header" style="margin-bottom: 25px;">
                <div class="popup-title" style="font-size: 24px; font-weight: 600; color: var(--accent-1); display: flex; align-items: center; justify-content: center; gap: 10px; margin-bottom: 15px;">
                    <i class="fas fa-graduation-cap" style="font-size: 28px;"></i>
                    Create Exam Questions
                </div>
                <p style="color: var(--text-secondary); font-size: 16px; line-height: 1.5;">
                    Your job has been posted successfully! Now create exam questions to assess candidates.
                </p>
            </div>
            
            <div class="notification-actions" style="display: flex; gap: 15px; justify-content: center; margin-top: 30px;">
                <button type="button" class="btn btn-secondary" onclick="closeExamCreationNotification()" style="padding: 12px 24px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 14px; transition: all 0.2s; display: flex; align-items: center; gap: 8px; background: var(--bg-tertiary); color: var(--text-primary); border: 1px solid var(--border);">
                    <i class="fas fa-times"></i>
                    Later
                </button>
                <button type="button" class="btn btn-primary" id="createExamNowBtn" style="padding: 12px 24px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 14px; transition: all 0.2s; display: flex; align-items: center; gap: 8px; background: var(--accent-1); color: white;">
                    <i class="fas fa-pencil-alt"></i>
                    Create Exam Now
                </button>
            </div>
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

        // Action buttons functionality
        document.querySelectorAll('.action-btn').forEach(button => {
            button.addEventListener('click', function() {
                const action = this.getAttribute('title');
                const jobTitle = this.closest('tr').querySelector('.job-title').textContent;
                
                if (action === 'View') {
                    alert(`Viewing details for: ${jobTitle}`);
                } else if (action === 'Edit') {
                    alert(`Editing job: ${jobTitle}`);
                } else if (action === 'Repost') {
                    alert(`Reposting job: ${jobTitle}`);
                }
            });
        });

        // Post New Job button functionality
        document.querySelector('.post-new-job-btn').addEventListener('click', function() {
            openJobPostModal();
        });

        // Search functionality
        document.querySelector('.search-box input').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('.job-posts-table tbody tr');
            
            rows.forEach(row => {
                const jobTitle = row.querySelector('.job-title').textContent.toLowerCase();
                const department = row.querySelector('.department').textContent.toLowerCase();
                
                if (jobTitle.includes(searchTerm) || department.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });


        // Job Post Management
        let currentJobId = null;

        // Job post modal functions
        function openJobPostModal(jobId = null) {
            currentJobId = jobId;
            const modal = document.getElementById('jobPostModal');
            const modalTitle = document.getElementById('jobModalTitle');
            const submitBtn = document.getElementById('submitJobText');
            const actionInput = document.getElementById('jobAction');
            
            if (jobId) {
                modalTitle.textContent = 'Edit Job Post';
                submitBtn.textContent = 'Update Job';
                actionInput.value = 'update_job';
                loadJobForEdit(jobId);
            } else {
                modalTitle.textContent = 'Add New Job Post';
                submitBtn.textContent = 'Post Job';
                actionInput.value = 'create_job';
                document.getElementById('jobPostForm').reset();
            }
            
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeJobPostModal() {
            const modal = document.getElementById('jobPostModal');
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
            document.getElementById('jobPostForm').reset();
            currentJobId = null;
        }

        // Exam Creation Notification Functions
        function showExamCreationNotification(jobId) {
            const popup = document.getElementById('examCreationNotificationPopup');
            popup.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            
            // Store job ID for later use
            popup.setAttribute('data-job-id', jobId);
        }

        function closeExamCreationNotification() {
            const popup = document.getElementById('examCreationNotificationPopup');
            popup.style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        // Handle "Create Exam Now" button click
        document.getElementById('createExamNowBtn').addEventListener('click', function() {
            const popup = document.getElementById('examCreationNotificationPopup');
            const jobId = popup.getAttribute('data-job-id');
            
            // Close the popup
            closeExamCreationNotification();
            
            // Redirect to exam creation page with job ID parameter
            window.location.href = `CreateExam.php?jobId=${jobId}`;
        });

        // Load job for editing
        function loadJobForEdit(jobId) {
            fetch(`job_posting_handler.php?action=get_job&jobId=${jobId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const job = data.job;
                        document.getElementById('jobId').value = job.JobID;
                        document.getElementById('jobTitle').value = job.JobTitle || '';
                        document.getElementById('department').value = job.Department || '';
                        document.getElementById('jobDescription').value = job.JobDescription || '';
                        document.getElementById('requirements').value = job.Requirements || '';
                        document.getElementById('responsibilities').value = job.Responsibilities || '';
                        document.getElementById('skills').value = job.Skills || '';
                        document.getElementById('location').value = job.Location || '';
                        document.getElementById('jobType').value = job.JobType || '';
                        document.getElementById('currency').value = job.Currency || 'USD';
                        document.getElementById('salaryMin').value = job.SalaryMin || '';
                        document.getElementById('salaryMax').value = job.SalaryMax || '';
                        document.getElementById('experienceLevel').value = job.ExperienceLevel || 'mid';
                        document.getElementById('educationLevel').value = job.EducationLevel || 'bachelor';
                        document.getElementById('closingDate').value = job.ClosingDate || '';
                    } else {
                        showErrorMessage('Failed to load job details');
                    }
                })
                .catch(error => {
                    console.error('Error loading job:', error);
                    showErrorMessage('Network error loading job details');
                });
        }

        // Job post form submission
        document.getElementById('jobPostForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = document.getElementById('submitJobPost');
            const submitText = document.getElementById('submitJobText');
            
            // Disable button and show loading
            submitBtn.disabled = true;
            submitText.textContent = currentJobId ? 'Updating...' : 'Posting...';
            
            fetch('job_posting_handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeJobPostModal();
                    showSuccessMessage(data.message);
                    loadJobPosts(); // Refresh job posts list
                    
                    // Show exam creation popup for new job posts (not updates)
                    if (!currentJobId && data.jobId) {
                        showExamCreationNotification(data.jobId);
                    }
                } else {
                    showErrorMessage(data.message || 'Failed to save job post');
                }
            })
            .catch(error => {
                console.error('Error saving job post:', error);
                showErrorMessage('Network error. Please try again.');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitText.textContent = currentJobId ? 'Update Job' : 'Post Job';
            });
        });

        // Load job posts
        function loadJobPosts() {
            console.log('Loading job posts...');
            
            fetch('job_posting_handler.php?action=list_jobs')
                .then(response => response.json())
                .then(data => {
                    console.log('Job posts loaded:', data);
                    hideLoadingIndicator();
                    
                    if (data.success && data.jobs && data.jobs.length > 0) {
                        displayJobPosts(data.jobs);
                        document.getElementById('noJobsMessage').style.display = 'none';
                    } else {
                        document.getElementById('jobPostsTableContainer').style.display = 'none';
                        document.getElementById('noJobsMessage').style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Error loading job posts:', error);
                    hideLoadingIndicator();
                    showErrorMessage('Failed to load job posts');
                });
        }

        // Display job posts
        function displayJobPosts(jobs) {
            const tbody = document.getElementById('jobPostsTableBody');
            tbody.innerHTML = '';
            
            jobs.forEach(job => {
                const row = createJobPostRow(job);
                tbody.appendChild(row);
            });
            
            document.getElementById('jobPostsTableContainer').style.display = 'block';
        }

        // Create job post table row
        function createJobPostRow(job) {
            const row = document.createElement('tr');
            
            // Format posted date
            const postedDate = new Date(job.PostedDate).toLocaleDateString();
            
            // Format applications count
            const applicationsCount = job.ApplicationCount || 0;
            const applicationsText = applicationsCount === 0 ? 'No applications' : 
                                   applicationsCount === 1 ? '1 application' : 
                                   `${applicationsCount} applications`;
            
            row.innerHTML = `
                <td class="job-title">${job.JobTitle}</td>
                <td class="department">${job.Department || 'N/A'}</td>
                <td class="posted-date">${postedDate}</td>
                <td class="applications-count">
                    <span class="application-count-badge ${applicationsCount === 0 ? 'empty' : ''}">
                        <i class="fas fa-users" style="font-size: 10px;"></i>
                        ${applicationsCount}
                    </span>
                </td>
                <td class="actions">
                    <button class="action-btn" title="Edit" onclick="editJobPost(${job.JobID})"><i class="fas fa-edit"></i></button>
                    <button class="action-btn" title="Delete" onclick="deleteJobPost(${job.JobID})"><i class="fas fa-trash"></i></button>
                </td>
            `;
            
            return row;
        }

        // Job post actions
        function editJobPost(jobId) {
            openJobPostModal(jobId);
        }

        function deleteJobPost(jobId) {
            if (confirm('Are you sure you want to delete this job post? This action cannot be undone.')) {
                const formData = new FormData();
                formData.append('action', 'delete_job');
                formData.append('jobId', jobId);
                
                fetch('job_posting_handler.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showSuccessMessage(data.message);
                        loadJobPosts();
                    } else {
                        showErrorMessage(data.message || 'Failed to delete job post');
                    }
                })
                .catch(error => {
                    console.error('Error deleting job post:', error);
                    showErrorMessage('Network error. Please try again.');
                });
            }
        }


        // Utility functions
        function hideLoadingIndicator() {
            document.getElementById('loadingIndicator').style.display = 'none';
        }

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

        // Close modal when clicking outside
        document.getElementById('jobPostModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeJobPostModal();
            }
        });

        // Close exam creation notification when clicking outside
        document.getElementById('examCreationNotificationPopup').addEventListener('click', function(e) {
            if (e.target === this) {
                closeExamCreationNotification();
            }
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

        // Initialize theme when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            initializeTheme();
            setupThemeToggle();
            setupCompanyProfileEditing();
            loadCompanyProfile();
            loadJobPosts();
        });
    </script>
</body>
</html>
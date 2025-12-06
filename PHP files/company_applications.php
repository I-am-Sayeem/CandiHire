<?php
// company_applications.php - View job applications for companies
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
    <title>Job Applications - CandiHire</title>
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
            max-width: 100%;
        }

        .page-header {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border);
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

        /* Applications Table */
        .applications-table-container {
            background-color: var(--bg-secondary);
            border-radius: 12px;
            border: 1px solid var(--border);
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .applications-table {
            width: 100%;
            border-collapse: collapse;
        }

        .applications-table th {
            background-color: var(--bg-tertiary);
            padding: 16px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .applications-table td {
            padding: 16px;
            border-top: 1px solid var(--border);
            font-size: 15px;
        }

        .applications-table tbody tr {
            transition: background-color 0.2s ease;
        }

        .applications-table tbody tr:hover {
            background-color: rgba(88, 166, 255, 0.05);
        }

        .candidate-name {
            font-weight: 600;
            color: var(--accent-1);
        }

        .job-title {
            color: var(--text-secondary);
        }

        .application-date {
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

        .action-btn:hover {
            background-color: var(--bg-tertiary);
            color: var(--accent-1);
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
            .applications-table {
                font-size: 14px;
            }
            .applications-table th,
            .applications-table td {
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
                <div class="nav-item active">
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
            <!-- Page Header -->
            <div class="page-header">
                <h1 class="page-title">Job Applications</h1>
                <p class="page-subtitle">Review and manage applications for your job posts</p>
            </div>

            <!-- Job Position Selector -->
            <div class="job-selector-section" style="background: var(--bg-secondary); padding: 20px; border-radius: 12px; margin-bottom: 20px; border: 1px solid var(--border);">
                <div style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
                    <div style="flex: 1; min-width: 300px;">
                        <label style="display: block; margin-bottom: 8px; color: var(--text-primary); font-weight: 600; font-size: 14px;">Select Job Position</label>
                        <select id="jobPositionSelect" style="width: 100%; padding: 12px 16px; border: 1px solid var(--border); border-radius: 8px; background: var(--bg-primary); color: var(--text-primary); font-size: 14px; cursor: pointer;">
                            <option value="">Choose a job position to view applications...</option>
                        </select>
                    </div>
                    <div style="display: flex; gap: 10px; align-items: end;">
                        <button id="loadApplicationsBtn" style="background: var(--accent-1); color: white; border: none; border-radius: 8px; padding: 12px 20px; cursor: pointer; font-weight: 600; font-size: 14px; transition: all 0.2s;" disabled>
                            <i class="fas fa-search" style="margin-right: 6px;"></i>Load Applications
                        </button>
                        <button id="clearSelectionBtn" style="background: var(--text-secondary); color: white; border: none; border-radius: 8px; padding: 12px 16px; cursor: pointer; font-weight: 600; font-size: 14px; transition: all 0.2s;" disabled>
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Loading Indicator -->
            <div id="loadingIndicator" style="display: none; text-align: center; padding: 40px; color: var(--text-secondary);">
                <i class="fas fa-spinner fa-spin" style="font-size: 24px; margin-right: 10px;"></i>
                Loading applications...
            </div>

            <!-- Applications Table -->
            <div id="applicationsTableContainer" class="applications-table-container" style="display: none;">
                <table class="applications-table">
                    <thead>
                        <tr>
                            <th>Candidate</th>
                            <th>Application Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="applicationsTableBody">
                        <!-- Applications will be loaded here dynamically -->
                    </tbody>
                </table>
            </div>

            <!-- No Applications Message -->
            <div id="noApplicationsMessage" style="display: none; text-align: center; padding: 40px; color: var(--text-secondary);">
                <i class="fas fa-users" style="font-size: 48px; margin-bottom: 16px; opacity: 0.5;"></i>
                <h3>No Applications Found</h3>
                <p>No candidates have applied for the selected job position yet.</p>
            </div>

            <!-- Select Job Message -->
            <div id="selectJobMessage" style="text-align: center; padding: 40px; color: var(--text-secondary);">
                <i class="fas fa-briefcase" style="font-size: 48px; margin-bottom: 16px; opacity: 0.5;"></i>
                <h3>Select a Job Position</h3>
                <p>Choose a job position from the dropdown above to view applications.</p>
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

    <!-- Candidate Details Popup -->
    <div id="candidateDetailsPopup" class="popup-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.7); z-index: 10000; backdrop-filter: blur(5px);">
        <div class="popup-content" style="background: var(--bg-secondary); border-radius: 16px; padding: 30px; width: 90%; max-width: 900px; max-height: 90vh; overflow-y: auto; border: 1px solid var(--border); box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3); margin: 2% auto;">
            <div class="popup-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; padding-bottom: 15px; border-bottom: 1px solid var(--border);">
                <div class="popup-title" style="font-size: 24px; font-weight: 600; color: var(--accent-1); display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-user"></i>
                    <span id="candidateDetailsTitle">Candidate Details</span>
                </div>
                <button class="popup-close" onclick="closeCandidateDetailsPopup()" style="background: none; border: none; color: var(--text-secondary); font-size: 24px; cursor: pointer; padding: 5px; border-radius: 50%; transition: all 0.2s;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div id="candidateDetailsContent">
                <!-- Candidate details will be loaded here -->
                <div class="loading-candidate" style="text-align: center; padding: 40px; color: var(--text-secondary);">
                    <i class="fas fa-spinner fa-spin" style="font-size: 24px; margin-bottom: 10px;"></i>
                    <div>Loading candidate details...</div>
                </div>
            </div>

            <div class="form-actions" style="display: flex; gap: 15px; justify-content: flex-end; margin-top: 30px; padding-top: 20px; border-top: 1px solid var(--border);">
                <button type="button" class="btn btn-secondary" onclick="closeCandidateDetailsPopup()" style="padding: 12px 24px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 14px; transition: all 0.2s; display: flex; align-items: center; gap: 8px; background: var(--bg-tertiary); color: var(--text-primary); border: 1px solid var(--border);">
                    <i class="fas fa-times"></i>
                    Close
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

        // Navigation item functionality
        document.querySelectorAll('.nav-item').forEach(item => {
            item.addEventListener('click', function() {
                document.querySelectorAll('.nav-item').forEach(navItem => {
                    navItem.classList.remove('active');
                });
                this.classList.add('active');
            });
        });

        // Logout functionality
        const logoutBtn = document.getElementById('logoutBtn');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', function() {
                window.location.href = 'Login&Signup.php';
            });
        }

        // Job Applications Management
        let currentJobId = null;
        let currentApplications = [];

        // Load job positions on page load
        function loadJobPositions() {
            console.log('Loading job positions...');
            
            fetch('job_applications_handler.php?action=get_job_posts')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log('Job positions loaded:', data.jobPosts);
                        populateJobSelect(data.jobPosts);
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

        // Populate job position dropdown
        function populateJobSelect(jobPosts) {
            const select = document.getElementById('jobPositionSelect');
            select.innerHTML = '<option value="">Choose a job position to view applications...</option>';
            
            jobPosts.forEach(job => {
                const option = document.createElement('option');
                option.value = job.JobID;
                option.textContent = `${job.JobTitle} - ${job.Department} (${job.Location})`;
                option.dataset.jobTitle = job.JobTitle;
                option.dataset.department = job.Department;
                option.dataset.location = job.Location;
                option.dataset.jobType = job.JobType;
                select.appendChild(option);
            });
        }

        // Load applications for selected job
        function loadApplications(jobId) {
            console.log('Loading applications for job ID:', jobId);
            
            showLoadingIndicator();
            
            fetch(`job_applications_handler.php?action=get_applications&jobId=${jobId}`)
                .then(response => response.json())
                .then(data => {
                    hideLoadingIndicator();
                    
                    if (data.success) {
                        console.log('Applications loaded:', data.applications);
                        currentApplications = data.applications;
                        displayApplications(data.applications);
                    } else {
                        console.error('Failed to load applications:', data.message);
                        showErrorMessage(data.message || 'Failed to load applications');
                        showNoApplicationsMessage();
                    }
                })
                .catch(error => {
                    console.error('Error loading applications:', error);
                    hideLoadingIndicator();
                    showErrorMessage('Network error loading applications');
                    showNoApplicationsMessage();
                });
        }

        // Display applications in table
        function displayApplications(applications) {
            const container = document.getElementById('applicationsTableContainer');
            const tbody = document.getElementById('applicationsTableBody');
            
            if (applications.length === 0) {
                showNoApplicationsMessage();
                return;
            }
            
            tbody.innerHTML = '';
            
            applications.forEach(application => {
                const row = createApplicationRow(application);
                tbody.appendChild(row);
            });
            
            showApplicationsTable();
        }

        // Create application row
        function createApplicationRow(application) {
            const row = document.createElement('tr');
            
            // Get initials from name
            const initials = application.FullName.split(' ').map(name => name[0]).join('').toUpperCase();
            
            // Create avatar HTML
            let avatarHtml = '';
            if (application.ProfilePicture) {
                avatarHtml = `<div class="candidate-avatar" style="width: 40px; height: 40px; border-radius: 50%; background-image: url('${application.ProfilePicture}'); background-size: cover; background-position: center; display: inline-flex; align-items: center; justify-content: center; margin-right: 12px; color: transparent;">${initials}</div>`;
            } else {
                avatarHtml = `<div class="candidate-avatar" style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, var(--accent-1), var(--accent-hover)); display: inline-flex; align-items: center; justify-content: center; margin-right: 12px; color: white; font-weight: bold; font-size: 16px;">${initials}</div>`;
            }
            
            // Format application date
            const applicationDate = new Date(application.ApplicationDate).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
            
            row.innerHTML = `
                <td>
                    <div style="display: flex; align-items: center;">
                        ${avatarHtml}
                        <div>
                            <div class="candidate-name">${escapeHtml(application.FullName)}</div>
                            <div style="color: var(--text-secondary); font-size: 14px;">${escapeHtml(application.Email)}</div>
                        </div>
                    </div>
                </td>
                <td class="application-date">${applicationDate}</td>
                <td class="actions">
                    <button class="action-btn" title="View Details" onclick="viewCandidateDetails(${application.CandidateID}, '${escapeHtml(application.FullName)}')">
                        <i class="fas fa-user"></i>
                    </button>
                </td>
            `;
            
            return row;
        }


        // View candidate details
        function viewCandidateDetails(candidateId, candidateName) {
            console.log('Viewing candidate details for ID:', candidateId);
            showCandidateDetailsPopup(candidateId, candidateName);
        }

        // Show candidate details popup
        function showCandidateDetailsPopup(candidateId, candidateName) {
            const popup = document.getElementById('candidateDetailsPopup');
            const title = document.getElementById('candidateDetailsTitle');
            const content = document.getElementById('candidateDetailsContent');
            
            title.textContent = `Candidate Details - ${candidateName}`;
            content.innerHTML = `
                <div class="loading-candidate" style="text-align: center; padding: 40px; color: var(--text-secondary);">
                    <i class="fas fa-spinner fa-spin" style="font-size: 24px; margin-bottom: 10px;"></i>
                    <div>Loading candidate details...</div>
                </div>
            `;
            
            popup.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            
            // Load candidate details
            loadCandidateDetails(candidateId);
        }

        // Load candidate details from API
        function loadCandidateDetails(candidateId) {
            fetch(`candidate_details_handler.php?candidateId=${candidateId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        renderCandidateDetails(data);
                    } else {
                        showCandidateError(data.message || 'Failed to load candidate details');
                    }
                })
                .catch(error => {
                    console.error('Error loading candidate details:', error);
                    showCandidateError('Network error loading candidate details');
                });
        }

        // Render candidate details
        function renderCandidateDetails(data) {
            const content = document.getElementById('candidateDetailsContent');
            const candidate = data.candidate;
            const experiences = data.experiences || [];
            const educations = data.educations || [];
            
            // Get initials from name
            const initials = candidate.name.split(' ').map(name => name[0]).join('').toUpperCase();
            
            // Create avatar HTML
            let avatarHtml = '';
            if (candidate.profilePicture) {
                avatarHtml = `<div class="candidate-avatar" style="width: 80px; height: 80px; border-radius: 50%; background-image: url('${candidate.profilePicture}'); background-size: cover; background-position: center; display: inline-flex; align-items: center; justify-content: center; color: transparent;">${initials}</div>`;
            } else {
                avatarHtml = `<div class="candidate-avatar" style="width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, var(--accent-1), var(--accent-hover)); display: inline-flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 32px;">${initials}</div>`;
            }
            
            // Split skills into arrays
            const skills = candidate.skills ? candidate.skills.split(',').map(skill => skill.trim()) : [];
            
            content.innerHTML = `
                <div class="candidate-header" style="display: flex; align-items: center; gap: 20px; margin-bottom: 30px; padding: 20px; background: var(--bg-tertiary); border-radius: 12px; border: 1px solid var(--border);">
                    ${avatarHtml}
                    <div style="flex: 1;">
                        <h2 style="font-size: 24px; font-weight: 600; margin-bottom: 8px; color: var(--accent-1);">${escapeHtml(candidate.name)}</h2>
                        <div style="color: var(--text-secondary); font-size: 16px; margin-bottom: 4px;">
                            <i class="fas fa-envelope" style="margin-right: 8px;"></i>${escapeHtml(candidate.email)}
                        </div>
                        <div style="color: var(--text-secondary); font-size: 16px; margin-bottom: 4px;">
                            <i class="fas fa-phone" style="margin-right: 8px;"></i>${escapeHtml(candidate.phone || 'Not provided')}
                        </div>
                        <div style="color: var(--text-secondary); font-size: 16px;">
                            <i class="fas fa-map-marker-alt" style="margin-right: 8px;"></i>${escapeHtml(candidate.location || 'Not provided')}
                        </div>
                    </div>
                </div>
                
                ${candidate.summary ? `
                    <div style="background: var(--bg-tertiary); padding: 20px; border-radius: 8px; border: 1px solid var(--border); margin-bottom: 20px;">
                        <h3 style="font-size: 18px; font-weight: 600; margin-bottom: 15px; color: var(--accent-1);">
                            <i class="fas fa-user" style="margin-right: 8px;"></i>Summary
                        </h3>
                        <p style="color: var(--text-primary); line-height: 1.6;">${escapeHtml(candidate.summary)}</p>
                    </div>
                ` : ''}
                
                ${(candidate.education || candidate.institute) ? `
                    <div style="background: var(--bg-tertiary); padding: 20px; border-radius: 8px; border: 1px solid var(--border); margin-bottom: 20px;">
                        <h3 style="font-size: 18px; font-weight: 600; margin-bottom: 15px; color: var(--accent-1);">
                            <i class="fas fa-graduation-cap" style="margin-right: 8px;"></i>Education Information
                        </h3>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            ${candidate.education ? `
                                <div style="padding: 12px; background: var(--bg-primary); border-radius: 6px; border: 1px solid var(--border);">
                                    <div style="color: var(--text-secondary); font-size: 12px; font-weight: 600; text-transform: uppercase; margin-bottom: 4px;">Education/Degree</div>
                                    <div style="color: var(--text-primary); font-size: 14px; font-weight: 500;">${escapeHtml(candidate.education)}</div>
                                </div>
                            ` : ''}
                            ${candidate.institute ? `
                                <div style="padding: 12px; background: var(--bg-primary); border-radius: 6px; border: 1px solid var(--border);">
                                    <div style="color: var(--text-secondary); font-size: 12px; font-weight: 600; text-transform: uppercase; margin-bottom: 4px;">Institute/University</div>
                                    <div style="color: var(--text-primary); font-size: 14px; font-weight: 500;">${escapeHtml(candidate.institute)}</div>
                                </div>
                            ` : ''}
                        </div>
                    </div>
                ` : ''}
                
                ${(candidate.linkedin || candidate.github || candidate.portfolio) ? `
                    <div style="background: var(--bg-tertiary); padding: 20px; border-radius: 8px; border: 1px solid var(--border); margin-bottom: 20px;">
                        <h3 style="font-size: 18px; font-weight: 600; margin-bottom: 15px; color: var(--accent-1);">
                            <i class="fas fa-link" style="margin-right: 8px;"></i>Professional Links
                        </h3>
                        <div style="display: flex; flex-wrap: wrap; gap: 15px;">
                            ${candidate.linkedin ? `
                                <a href="${escapeHtml(candidate.linkedin)}" target="_blank" rel="noopener noreferrer" style="display: flex; align-items: center; gap: 8px; padding: 10px 16px; background: #0077b5; color: white; text-decoration: none; border-radius: 6px; font-size: 14px; font-weight: 500; transition: all 0.2s; box-shadow: 0 2px 4px rgba(0, 119, 181, 0.2);" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(0, 119, 181, 0.3)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 4px rgba(0, 119, 181, 0.2)'">
                                    <i class="fab fa-linkedin"></i>
                                    LinkedIn
                                </a>
                            ` : ''}
                            ${candidate.github ? `
                                <a href="${escapeHtml(candidate.github)}" target="_blank" rel="noopener noreferrer" style="display: flex; align-items: center; gap: 8px; padding: 10px 16px; background: #333; color: white; text-decoration: none; border-radius: 6px; font-size: 14px; font-weight: 500; transition: all 0.2s; box-shadow: 0 2px 4px rgba(51, 51, 51, 0.2);" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(51, 51, 51, 0.3)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 4px rgba(51, 51, 51, 0.2)'">
                                    <i class="fab fa-github"></i>
                                    GitHub
                                </a>
                            ` : ''}
                            ${candidate.portfolio ? `
                                <a href="${escapeHtml(candidate.portfolio)}" target="_blank" rel="noopener noreferrer" style="display: flex; align-items: center; gap: 8px; padding: 10px 16px; background: var(--accent-1); color: white; text-decoration: none; border-radius: 6px; font-size: 14px; font-weight: 500; transition: all 0.2s; box-shadow: 0 2px 4px rgba(88, 166, 255, 0.2);" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(88, 166, 255, 0.3)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 4px rgba(88, 166, 255, 0.2)'">
                                    <i class="fas fa-globe"></i>
                                    Portfolio
                                </a>
                            ` : ''}
                        </div>
                    </div>
                ` : ''}
                
                ${skills.length > 0 ? `
                    <div style="background: var(--bg-tertiary); padding: 20px; border-radius: 8px; border: 1px solid var(--border); margin-bottom: 20px;">
                        <h3 style="font-size: 18px; font-weight: 600; margin-bottom: 15px; color: var(--accent-1);">
                            <i class="fas fa-code" style="margin-right: 8px;"></i>Skills
                        </h3>
                        <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                            ${skills.map(skill => `<span style="background: var(--accent-1); color: white; padding: 6px 12px; border-radius: 16px; font-size: 14px; font-weight: 500;">${escapeHtml(skill)}</span>`).join('')}
                        </div>
                    </div>
                ` : ''}
                
                ${experiences.length > 0 ? `
                    <div style="background: var(--bg-tertiary); padding: 20px; border-radius: 8px; border: 1px solid var(--border); margin-bottom: 20px;">
                        <h3 style="font-size: 18px; font-weight: 600; margin-bottom: 15px; color: var(--accent-1);">
                            <i class="fas fa-briefcase" style="margin-right: 8px;"></i>Work Experience
                        </h3>
                        <div style="max-height: 300px; overflow-y: auto;">
                            ${experiences.map(exp => `
                                <div style="padding: 15px; border-bottom: 1px solid var(--border); margin-bottom: 10px;">
                                    <div style="font-weight: 600; color: var(--text-primary); font-size: 16px; margin-bottom: 5px;">${escapeHtml(exp.JobTitle)}</div>
                                    <div style="color: var(--accent-1); font-weight: 500; margin-bottom: 5px;">${escapeHtml(exp.Company)}</div>
                                    <div style="color: var(--text-secondary); font-size: 14px; margin-bottom: 5px;">
                                        ${escapeHtml(exp.StartDate)} - ${escapeHtml(exp.EndDate || 'Present')}
                                        ${exp.Location ? ` • ${escapeHtml(exp.Location)}` : ''}
                                    </div>
                                    ${exp.Description ? `<div style="color: var(--text-primary); font-size: 14px; line-height: 1.5; margin-top: 8px;">${escapeHtml(exp.Description)}</div>` : ''}
                                </div>
                            `).join('')}
                        </div>
                    </div>
                ` : ''}
                
                ${educations.length > 0 ? `
                    <div style="background: var(--bg-tertiary); padding: 20px; border-radius: 8px; border: 1px solid var(--border); margin-bottom: 20px;">
                        <h3 style="font-size: 18px; font-weight: 600; margin-bottom: 15px; color: var(--accent-1);">
                            <i class="fas fa-graduation-cap" style="margin-right: 8px;"></i>Education
                        </h3>
                        <div style="max-height: 300px; overflow-y: auto;">
                            ${educations.map(edu => `
                                <div style="padding: 15px; border-bottom: 1px solid var(--border); margin-bottom: 10px;">
                                    <div style="font-weight: 600; color: var(--text-primary); font-size: 16px; margin-bottom: 5px;">${escapeHtml(edu.Degree)}</div>
                                    <div style="color: var(--accent-1); font-weight: 500; margin-bottom: 5px;">${escapeHtml(edu.Institution)}</div>
                                    <div style="color: var(--text-secondary); font-size: 14px; margin-bottom: 5px;">
                                        ${escapeHtml(edu.StartYear)} - ${escapeHtml(edu.EndYear)}
                                        ${edu.Location ? ` • ${escapeHtml(edu.Location)}` : ''}
                                        ${edu.GPA ? ` • GPA: ${escapeHtml(edu.GPA)}` : ''}
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                ` : ''}
                
            `;
        }

        // Show candidate error
        function showCandidateError(message) {
            const content = document.getElementById('candidateDetailsContent');
            content.innerHTML = `
                <div style="text-align: center; padding: 40px; color: var(--danger);">
                    <i class="fas fa-exclamation-triangle" style="font-size: 48px; margin-bottom: 16px;"></i>
                    <h3>Error Loading Candidate Details</h3>
                    <p>${escapeHtml(message)}</p>
                </div>
            `;
        }

        // Close candidate details popup
        function closeCandidateDetailsPopup() {
            const popup = document.getElementById('candidateDetailsPopup');
            popup.style.display = 'none';
            document.body.style.overflow = 'auto';
        }


        // UI state management
        function showLoadingIndicator() {
            document.getElementById('loadingIndicator').style.display = 'block';
            document.getElementById('applicationsTableContainer').style.display = 'none';
            document.getElementById('noApplicationsMessage').style.display = 'none';
            document.getElementById('selectJobMessage').style.display = 'none';
        }

        function hideLoadingIndicator() {
            document.getElementById('loadingIndicator').style.display = 'none';
        }

        function showApplicationsTable() {
            document.getElementById('applicationsTableContainer').style.display = 'block';
            document.getElementById('noApplicationsMessage').style.display = 'none';
            document.getElementById('selectJobMessage').style.display = 'none';
        }

        function showNoApplicationsMessage() {
            document.getElementById('noApplicationsMessage').style.display = 'block';
            document.getElementById('applicationsTableContainer').style.display = 'none';
            document.getElementById('selectJobMessage').style.display = 'none';
        }

        function showSelectJobMessage() {
            document.getElementById('selectJobMessage').style.display = 'block';
            document.getElementById('applicationsTableContainer').style.display = 'none';
            document.getElementById('noApplicationsMessage').style.display = 'none';
        }

        // Utility function to escape HTML
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

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

        // Setup event listeners
        function setupEventListeners() {
            // Job position selector
            const jobSelect = document.getElementById('jobPositionSelect');
            const loadBtn = document.getElementById('loadApplicationsBtn');
            const clearBtn = document.getElementById('clearSelectionBtn');
            
            jobSelect.addEventListener('change', function() {
                const jobId = this.value;
                currentJobId = jobId;
                
                if (jobId) {
                    loadBtn.disabled = false;
                    clearBtn.disabled = false;
                } else {
                    loadBtn.disabled = true;
                    clearBtn.disabled = true;
                    showSelectJobMessage();
                }
            });
            
            loadBtn.addEventListener('click', function() {
                if (currentJobId) {
                    loadApplications(currentJobId);
                }
            });
            
            clearBtn.addEventListener('click', function() {
                jobSelect.value = '';
                currentJobId = null;
                loadBtn.disabled = true;
                clearBtn.disabled = true;
                showSelectJobMessage();
            });
            
            // Candidate details popup close on outside click
            const candidatePopup = document.getElementById('candidateDetailsPopup');
            candidatePopup.addEventListener('click', function(e) {
                if (e.target === candidatePopup) {
                    closeCandidateDetailsPopup();
                }
            });
        }

        // Initialize theme when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            initializeTheme();
            setupThemeToggle();
            setupCompanyProfileEditing();
            loadCompanyProfile();
            setupEventListeners();
            loadJobPositions();
        });
    </script>
</body>
</html>

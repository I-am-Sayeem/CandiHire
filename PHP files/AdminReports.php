<?php
// AdminReports.php - System Reports and Analytics
session_start();
require_once 'admin_session_manager.php';
require_once 'Database.php';

// Check if admin is logged in
requireAdminLogin();

// Get report data
$reportData = getReportData($pdo);
$adminUsername = getCurrentAdminUsername();

// Handle export requests
if (isset($_GET['export']) && isset($_GET['type'])) {
    handleExport($pdo, $_GET['export'], $_GET['type']);
    exit;
}

function getReportData($pdo) {
    $data = [];
    
    try {
        // User Statistics
        $data['users'] = [
            'total_candidates' => getTotalCandidates($pdo),
            'total_companies' => getTotalCompanies($pdo),
            'new_candidates_this_month' => getNewCandidatesThisMonth($pdo),
            'new_companies_this_month' => getNewCompaniesThisMonth($pdo),
            'active_candidates' => getActiveCandidates($pdo),
            'candidate_growth' => getCandidateGrowthData($pdo),
            'company_growth' => getCompanyGrowthData($pdo)
        ];
        
        // Job Statistics
        $data['jobs'] = [
            'total_jobs' => getTotalJobs($pdo),
            'active_jobs' => getActiveJobs($pdo),
            'jobs_this_month' => getJobsThisMonth($pdo),
            'job_types' => getJobTypeDistribution($pdo),
            'top_industries' => getTopIndustries($pdo),
            'salary_ranges' => getSalaryRangeDistribution($pdo)
        ];
        
        // Application Statistics
        $data['applications'] = [
            'total_applications' => getTotalApplications($pdo),
            'applications_this_month' => getApplicationsThisMonth($pdo),
            'application_status' => getApplicationStatusDistribution($pdo),
            'top_applied_jobs' => getTopAppliedJobs($pdo),
            'application_trends' => getApplicationTrends($pdo)
        ];
        
        // Exam Statistics
        $data['exams'] = [
            'total_exams' => getTotalExams($pdo),
            'completed_exams' => getCompletedExams($pdo),
            'exam_completion_rate' => getExamCompletionRate($pdo),
            'average_scores' => getAverageExamScores($pdo),
            'exam_performance' => getExamPerformanceData($pdo)
        ];
        
        // Interview Statistics
        $data['interviews'] = [
            'total_interviews' => getTotalInterviews($pdo),
            'scheduled_interviews' => getScheduledInterviews($pdo),
            'completed_interviews' => getCompletedInterviews($pdo),
            'interview_success_rate' => getInterviewSuccessRate($pdo),
            'interview_modes' => getInterviewModeDistribution($pdo)
        ];
        
        // System Performance
        $data['system'] = [
            'database_size' => getDatabaseSize($pdo),
            'storage_usage' => getStorageUsage(),
            'recent_activity' => getRecentSystemActivity($pdo),
            'error_logs' => getRecentErrorLogs()
        ];
        
    } catch (Exception $e) {
        error_log("Error generating report data: " . $e->getMessage());
        $data = ['error' => 'Failed to generate report data'];
    }
    
    return $data;
}

// Database query functions
function getTotalCandidates($pdo) {
    $stmt = $pdo->query("SELECT COUNT(*) FROM candidate_login_info");
    return $stmt->fetchColumn();
}

function getTotalCompanies($pdo) {
    $stmt = $pdo->query("SELECT COUNT(*) FROM Company_login_info");
    return $stmt->fetchColumn();
}

function getNewCandidatesThisMonth($pdo) {
    $stmt = $pdo->query("SELECT COUNT(*) FROM candidate_login_info WHERE MONTH(CreatedAt) = MONTH(CURRENT_DATE()) AND YEAR(CreatedAt) = YEAR(CURRENT_DATE())");
    return $stmt->fetchColumn();
}

function getNewCompaniesThisMonth($pdo) {
    $stmt = $pdo->query("SELECT COUNT(*) FROM Company_login_info WHERE MONTH(CreatedAt) = MONTH(CURRENT_DATE()) AND YEAR(CreatedAt) = YEAR(CURRENT_DATE())");
    return $stmt->fetchColumn();
}

function getActiveCandidates($pdo) {
    $stmt = $pdo->query("SELECT COUNT(*) FROM candidate_login_info WHERE IsActive = 1");
    return $stmt->fetchColumn();
}

function getCandidateGrowthData($pdo) {
    $stmt = $pdo->query("
        SELECT 
            DATE_FORMAT(CreatedAt, '%Y-%m') as month,
            COUNT(*) as count
        FROM candidate_login_info 
        WHERE CreatedAt >= DATE_SUB(CURRENT_DATE(), INTERVAL 12 MONTH)
        GROUP BY DATE_FORMAT(CreatedAt, '%Y-%m')
        ORDER BY month
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getCompanyGrowthData($pdo) {
    $stmt = $pdo->query("
        SELECT 
            DATE_FORMAT(CreatedAt, '%Y-%m') as month,
            COUNT(*) as count
        FROM Company_login_info 
        WHERE CreatedAt >= DATE_SUB(CURRENT_DATE(), INTERVAL 12 MONTH)
        GROUP BY DATE_FORMAT(CreatedAt, '%Y-%m')
        ORDER BY month
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTotalJobs($pdo) {
    $stmt = $pdo->query("SELECT COUNT(*) FROM job_postings");
    return $stmt->fetchColumn();
}

function getActiveJobs($pdo) {
    $stmt = $pdo->query("SELECT COUNT(*) FROM job_postings WHERE Status = 'active'");
    return $stmt->fetchColumn();
}

function getJobsThisMonth($pdo) {
    $stmt = $pdo->query("SELECT COUNT(*) FROM job_postings WHERE MONTH(PostedDate) = MONTH(CURRENT_DATE()) AND YEAR(PostedDate) = YEAR(CURRENT_DATE())");
    return $stmt->fetchColumn();
}

function getJobTypeDistribution($pdo) {
    $stmt = $pdo->query("
        SELECT JobType, COUNT(*) as count 
        FROM job_postings 
        GROUP BY JobType 
        ORDER BY count DESC
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTopIndustries($pdo) {
    $stmt = $pdo->query("
        SELECT Industry, COUNT(*) as count 
        FROM Company_login_info 
        WHERE Industry IS NOT NULL 
        GROUP BY Industry 
        ORDER BY count DESC 
        LIMIT 10
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getSalaryRangeDistribution($pdo) {
    $stmt = $pdo->query("
        SELECT 
            CASE 
                WHEN SalaryMin < 30000 THEN 'Under $30k'
                WHEN SalaryMin < 50000 THEN '$30k - $50k'
                WHEN SalaryMin < 75000 THEN '$50k - $75k'
                WHEN SalaryMin < 100000 THEN '$75k - $100k'
                WHEN SalaryMin < 150000 THEN '$100k - $150k'
                ELSE 'Over $150k'
            END as salary_range,
            COUNT(*) as count
        FROM job_postings 
        WHERE SalaryMin IS NOT NULL
        GROUP BY salary_range
        ORDER BY MIN(SalaryMin)
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTotalApplications($pdo) {
    $stmt = $pdo->query("SELECT COUNT(*) FROM job_applications");
    return $stmt->fetchColumn();
}

function getApplicationsThisMonth($pdo) {
    $stmt = $pdo->query("SELECT COUNT(*) FROM job_applications WHERE MONTH(ApplicationDate) = MONTH(CURRENT_DATE()) AND YEAR(ApplicationDate) = YEAR(CURRENT_DATE())");
    return $stmt->fetchColumn();
}

function getApplicationStatusDistribution($pdo) {
    $stmt = $pdo->query("
        SELECT Status, COUNT(*) as count 
        FROM job_applications 
        GROUP BY Status 
        ORDER BY count DESC
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTopAppliedJobs($pdo) {
    $stmt = $pdo->query("
        SELECT 
            jp.JobTitle,
            co.CompanyName,
            COUNT(ja.ApplicationID) as application_count
        FROM job_postings jp
        JOIN Company_login_info co ON jp.CompanyID = co.CompanyID
        LEFT JOIN job_applications ja ON jp.JobID = ja.JobID
        GROUP BY jp.JobID
        ORDER BY application_count DESC
        LIMIT 10
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getApplicationTrends($pdo) {
    $stmt = $pdo->query("
        SELECT 
            DATE_FORMAT(ApplicationDate, '%Y-%m') as month,
            COUNT(*) as count
        FROM job_applications 
        WHERE ApplicationDate >= DATE_SUB(CURRENT_DATE(), INTERVAL 12 MONTH)
        GROUP BY DATE_FORMAT(ApplicationDate, '%Y-%m')
        ORDER BY month
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTotalExams($pdo) {
    $stmt = $pdo->query("SELECT COUNT(*) FROM exams");
    return $stmt->fetchColumn();
}

function getCompletedExams($pdo) {
    $stmt = $pdo->query("SELECT COUNT(*) FROM exam_attempts WHERE Status = 'completed'");
    return $stmt->fetchColumn();
}

function getExamCompletionRate($pdo) {
    $total = getTotalExams($pdo);
    $completed = getCompletedExams($pdo);
    return $total > 0 ? round(($completed / $total) * 100, 2) : 0;
}

function getAverageExamScores($pdo) {
    $stmt = $pdo->query("
        SELECT 
            AVG(Score) as average_score,
            MIN(Score) as min_score,
            MAX(Score) as max_score
        FROM exam_attempts 
        WHERE Status = 'completed' AND Score IS NOT NULL
    ");
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getExamPerformanceData($pdo) {
    $stmt = $pdo->query("
        SELECT 
            e.ExamTitle,
            COUNT(ea.AttemptID) as total_attempts,
            AVG(ea.Score) as average_score,
            COUNT(CASE WHEN ea.Score >= 70 THEN 1 END) as passed_attempts
        FROM exams e
        LEFT JOIN exam_attempts ea ON e.ExamID = ea.ExamID
        WHERE ea.Status = 'completed'
        GROUP BY e.ExamID
        ORDER BY total_attempts DESC
        LIMIT 10
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTotalInterviews($pdo) {
    $stmt = $pdo->query("SELECT COUNT(*) FROM interviews");
    return $stmt->fetchColumn();
}

function getScheduledInterviews($pdo) {
    $stmt = $pdo->query("SELECT COUNT(*) FROM interviews WHERE Status = 'scheduled'");
    return $stmt->fetchColumn();
}

function getCompletedInterviews($pdo) {
    $stmt = $pdo->query("SELECT COUNT(*) FROM interviews WHERE Status = 'completed'");
    return $stmt->fetchColumn();
}

function getInterviewSuccessRate($pdo) {
    $total = getTotalInterviews($pdo);
    $completed = getCompletedInterviews($pdo);
    return $total > 0 ? round(($completed / $total) * 100, 2) : 0;
}

function getInterviewModeDistribution($pdo) {
    $stmt = $pdo->query("
        SELECT InterviewMode, COUNT(*) as count 
        FROM interviews 
        GROUP BY InterviewMode 
        ORDER BY count DESC
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getDatabaseSize($pdo) {
    $stmt = $pdo->query("
        SELECT 
            ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'DB Size in MB'
        FROM information_schema.tables 
        WHERE table_schema = DATABASE()
    ");
    return $stmt->fetchColumn();
}

function getStorageUsage() {
    $uploadDir = 'uploads/';
    $cvDir = 'CVs/';
    
    $uploadSize = 0;
    $cvSize = 0;
    
    if (is_dir($uploadDir)) {
        $uploadSize = getDirSize($uploadDir);
    }
    
    if (is_dir($cvDir)) {
        $cvSize = getDirSize($cvDir);
    }
    
    return [
        'uploads' => round($uploadSize / 1024 / 1024, 2),
        'cvs' => round($cvSize / 1024 / 1024, 2),
        'total' => round(($uploadSize + $cvSize) / 1024 / 1024, 2)
    ];
}

function getDirSize($dir) {
    $size = 0;
    if (is_dir($dir)) {
        foreach (glob(rtrim($dir, '/') . '/*', GLOB_NOSORT) as $each) {
            $size += is_file($each) ? filesize($each) : getDirSize($each);
        }
    }
    return $size;
}

function getRecentSystemActivity($pdo) {
    $stmt = $pdo->query("
        SELECT 
            'job_application' as type,
            CONCAT(c.FullName, ' applied for ', jp.JobTitle) as description,
            ja.ApplicationDate as date
        FROM job_applications ja
        JOIN candidate_login_info c ON ja.CandidateID = c.CandidateID
        JOIN job_postings jp ON ja.JobID = jp.JobID
        WHERE ja.ApplicationDate >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        
        UNION ALL
        
        SELECT 
            'job_post' as type,
            CONCAT(co.CompanyName, ' posted ', jp.JobTitle) as description,
            jp.PostedDate as date
        FROM job_postings jp
        JOIN Company_login_info co ON jp.CompanyID = co.CompanyID
        WHERE jp.PostedDate >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        
        ORDER BY date DESC
        LIMIT 20
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getRecentErrorLogs() {
    $logFile = 'error_logs/system_errors.log';
    $logs = [];
    
    if (file_exists($logFile)) {
        $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $logs = array_slice(array_reverse($lines), 0, 10);
    }
    
    return $logs;
}

function handleExport($pdo, $reportType, $format) {
    $data = getReportData($pdo);
    
    if ($format === 'csv') {
        exportToCSV($data, $reportType);
    } elseif ($format === 'pdf') {
        exportToPDF($data, $reportType);
    }
}

function exportToCSV($data, $reportType) {
    $filename = "candihire_report_{$reportType}_" . date('Y-m-d_H-i-s') . ".csv";
    
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    
    switch ($reportType) {
        case 'users':
            fputcsv($output, ['Month', 'New Candidates', 'New Companies']);
            foreach ($data['users']['candidate_growth'] as $row) {
                fputcsv($output, [$row['month'], $row['count'], 0]);
            }
            break;
        case 'applications':
            fputcsv($output, ['Month', 'Applications']);
            foreach ($data['applications']['application_trends'] as $row) {
                fputcsv($output, [$row['month'], $row['count']]);
            }
            break;
    }
    
    fclose($output);
}

function exportToPDF($data, $reportType) {
    // Simple PDF generation - in production, use a proper PDF library
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="candihire_report_' . $reportType . '_' . date('Y-m-d') . '.pdf"');
    
    // This is a placeholder - implement proper PDF generation
    echo "PDF export functionality would be implemented here with a proper PDF library like TCPDF or FPDF";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Reports - CandiHire Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            --warning: #d29922;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Noto Sans', Helvetica, Arial, sans-serif;
        }

        body {
            background: var(--bg-primary);
            color: var(--text-primary);
            line-height: 1.6;
            min-height: 100vh;
        }

        .admin-header {
            background: var(--bg-secondary);
            border-bottom: 1px solid var(--border);
            padding: 15px 0;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .admin-title {
            font-size: 1.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--accent-1), var(--accent-2));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .admin-user {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-info {
            text-align: right;
        }

        .user-name {
            font-weight: 600;
            color: var(--text-primary);
        }

        .user-role {
            font-size: 0.8rem;
            color: var(--text-secondary);
        }

        .back-btn {
            background: var(--bg-tertiary);
            color: var(--text-primary);
            border: 1px solid var(--border);
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .back-btn:hover {
            background: var(--accent-1);
            color: white;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        .reports-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .reports-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        .export-buttons {
            display: flex;
            gap: 10px;
        }

        .export-btn {
            background: var(--accent-1);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .export-btn:hover {
            background: var(--accent-hover);
            transform: translateY(-1px);
        }

        .export-btn.secondary {
            background: var(--bg-tertiary);
            color: var(--text-primary);
            border: 1px solid var(--border);
        }

        .export-btn.secondary:hover {
            background: var(--accent-1);
            color: white;
        }

        .reports-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .report-card {
            background: var(--bg-secondary);
            border-radius: 12px;
            padding: 25px;
            border: 1px solid var(--border);
            transition: all 0.3s ease;
        }

        .report-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border);
        }

        .card-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-icon {
            color: var(--accent-1);
            font-size: 1.1rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .stat-item {
            text-align: center;
        }

        .stat-value {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 0.8rem;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .chart-container {
            position: relative;
            height: 300px;
            margin-top: 20px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .data-table th,
        .data-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }

        .data-table th {
            background: var(--bg-tertiary);
            color: var(--text-primary);
            font-weight: 600;
            font-size: 0.9rem;
        }

        .data-table td {
            color: var(--text-secondary);
        }

        .data-table tr:hover {
            background: var(--bg-tertiary);
        }

        .progress-bar {
            width: 100%;
            height: 8px;
            background: var(--bg-tertiary);
            border-radius: 4px;
            overflow: hidden;
            margin: 5px 0;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--accent-1), var(--accent-2));
            transition: width 0.3s ease;
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .status-badge.success {
            background: rgba(63, 185, 80, 0.2);
            color: var(--success);
        }

        .status-badge.warning {
            background: rgba(210, 153, 34, 0.2);
            color: var(--warning);
        }

        .status-badge.danger {
            background: rgba(248, 81, 73, 0.2);
            color: var(--danger);
        }

        .no-data {
            text-align: center;
            color: var(--text-secondary);
            padding: 40px 20px;
            font-style: italic;
        }

        .loading {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px;
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid var(--border);
            border-top: 4px solid var(--accent-1);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @media (max-width: 768px) {
            .reports-grid {
                grid-template-columns: 1fr;
            }
            
            .reports-header {
                flex-direction: column;
                gap: 20px;
                align-items: flex-start;
            }
            
            .export-buttons {
                width: 100%;
                justify-content: flex-start;
            }
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <div class="header-content">
            <div class="admin-title">
                <i class="fas fa-chart-bar"></i> System Reports
            </div>
            <div class="admin-user">
                <div class="user-info">
                    <div class="user-name"><?php echo htmlspecialchars($adminUsername); ?></div>
                    <div class="user-role">System Administrator</div>
                </div>
                <a href="AdminDashboard.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="reports-header">
            <h1 class="reports-title">System Analytics & Reports</h1>
            <div class="export-buttons">
                <a href="?export=users&type=csv" class="export-btn">
                    <i class="fas fa-download"></i> Export Users CSV
                </a>
                <a href="?export=applications&type=csv" class="export-btn secondary">
                    <i class="fas fa-file-csv"></i> Export Applications CSV
                </a>
            </div>
        </div>

        <?php if (isset($reportData['error'])): ?>
            <div class="report-card">
                <div class="no-data">
                    <i class="fas fa-exclamation-triangle" style="font-size: 2rem; margin-bottom: 10px; color: var(--danger);"></i>
                    <p>Error loading report data: <?php echo htmlspecialchars($reportData['error']); ?></p>
                </div>
            </div>
        <?php else: ?>
            <div class="reports-grid">
                <!-- User Statistics -->
                <div class="report-card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-users card-icon"></i>
                            User Statistics
                        </h3>
                    </div>
                    <div class="stats-grid">
                        <div class="stat-item">
                            <div class="stat-value"><?php echo number_format($reportData['users']['total_candidates']); ?></div>
                            <div class="stat-label">Total Candidates</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value"><?php echo number_format($reportData['users']['total_companies']); ?></div>
                            <div class="stat-label">Total Companies</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value"><?php echo number_format($reportData['users']['new_candidates_this_month']); ?></div>
                            <div class="stat-label">New This Month</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value"><?php echo number_format($reportData['users']['active_candidates']); ?></div>
                            <div class="stat-label">Active Users</div>
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="userGrowthChart"></canvas>
                    </div>
                </div>

                <!-- Job Statistics -->
                <div class="report-card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-briefcase card-icon"></i>
                            Job Statistics
                        </h3>
                    </div>
                    <div class="stats-grid">
                        <div class="stat-item">
                            <div class="stat-value"><?php echo number_format($reportData['jobs']['total_jobs']); ?></div>
                            <div class="stat-label">Total Jobs</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value"><?php echo number_format($reportData['jobs']['active_jobs']); ?></div>
                            <div class="stat-label">Active Jobs</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value"><?php echo number_format($reportData['jobs']['jobs_this_month']); ?></div>
                            <div class="stat-label">This Month</div>
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="jobTypeChart"></canvas>
                    </div>
                </div>

                <!-- Application Statistics -->
                <div class="report-card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-file-alt card-icon"></i>
                            Application Statistics
                        </h3>
                    </div>
                    <div class="stats-grid">
                        <div class="stat-item">
                            <div class="stat-value"><?php echo number_format($reportData['applications']['total_applications']); ?></div>
                            <div class="stat-label">Total Applications</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value"><?php echo number_format($reportData['applications']['applications_this_month']); ?></div>
                            <div class="stat-label">This Month</div>
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="applicationTrendsChart"></canvas>
                    </div>
                </div>

                <!-- Exam Statistics -->
                <div class="report-card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-clipboard-list card-icon"></i>
                            Exam Statistics
                        </h3>
                    </div>
                    <div class="stats-grid">
                        <div class="stat-item">
                            <div class="stat-value"><?php echo number_format($reportData['exams']['total_exams']); ?></div>
                            <div class="stat-label">Total Exams</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value"><?php echo number_format($reportData['exams']['completed_exams']); ?></div>
                            <div class="stat-label">Completed</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value"><?php echo $reportData['exams']['exam_completion_rate']; ?>%</div>
                            <div class="stat-label">Completion Rate</div>
                        </div>
                    </div>
                    <?php if (!empty($reportData['exams']['average_scores'])): ?>
                        <div style="margin-top: 15px;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                                <span>Average Score</span>
                                <span><?php echo round($reportData['exams']['average_scores']['average_score'], 1); ?>%</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?php echo $reportData['exams']['average_scores']['average_score']; ?>%"></div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Interview Statistics -->
                <div class="report-card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-handshake card-icon"></i>
                            Interview Statistics
                        </h3>
                    </div>
                    <div class="stats-grid">
                        <div class="stat-item">
                            <div class="stat-value"><?php echo number_format($reportData['interviews']['total_interviews']); ?></div>
                            <div class="stat-label">Total Interviews</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value"><?php echo number_format($reportData['interviews']['scheduled_interviews']); ?></div>
                            <div class="stat-label">Scheduled</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value"><?php echo $reportData['interviews']['interview_success_rate']; ?>%</div>
                            <div class="stat-label">Success Rate</div>
                        </div>
                    </div>
                </div>

                <!-- System Performance -->
                <div class="report-card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-server card-icon"></i>
                            System Performance
                        </h3>
                    </div>
                    <div class="stats-grid">
                        <div class="stat-item">
                            <div class="stat-value"><?php echo $reportData['system']['database_size']; ?> MB</div>
                            <div class="stat-label">Database Size</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value"><?php echo $reportData['system']['storage_usage']['total']; ?> MB</div>
                            <div class="stat-label">Storage Used</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Applied Jobs Table -->
            <div class="report-card" style="margin-top: 30px;">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-trophy card-icon"></i>
                        Most Applied Jobs
                    </h3>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Job Title</th>
                            <th>Company</th>
                            <th>Applications</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($reportData['applications']['top_applied_jobs'])): ?>
                            <?php foreach ($reportData['applications']['top_applied_jobs'] as $job): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($job['JobTitle']); ?></td>
                                    <td><?php echo htmlspecialchars($job['CompanyName']); ?></td>
                                    <td>
                                        <span class="status-badge success"><?php echo $job['application_count']; ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="no-data">No data available</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Recent Activity -->
            <div id="recent-activity" class="report-card" style="margin-top: 30px;">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-clock card-icon"></i>
                        Recent System Activity
                    </h3>
                </div>
                <div style="max-height: 400px; overflow-y: auto;">
                    <?php if (!empty($reportData['system']['recent_activity'])): ?>
                        <?php foreach ($reportData['system']['recent_activity'] as $activity): ?>
                            <div style="display: flex; align-items: center; gap: 15px; padding: 15px 0; border-bottom: 1px solid var(--border);">
                                <div style="width: 40px; height: 40px; border-radius: 50%; background: var(--bg-tertiary); display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-<?php echo $activity['type'] === 'job_application' ? 'file-alt' : 'briefcase'; ?>" style="color: var(--accent-1);"></i>
                                </div>
                                <div style="flex: 1;">
                                    <div style="font-weight: 600; color: var(--text-primary); margin-bottom: 3px;">
                                        <?php echo htmlspecialchars($activity['description']); ?>
                                    </div>
                                    <div style="font-size: 0.9rem; color: var(--text-secondary);">
                                        <?php echo date('M j, Y g:i A', strtotime($activity['date'])); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-data">No recent activity</div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // User Growth Chart
        const userGrowthCtx = document.getElementById('userGrowthChart').getContext('2d');
        new Chart(userGrowthCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($reportData['users']['candidate_growth'], 'month')); ?>,
                datasets: [{
                    label: 'Candidates',
                    data: <?php echo json_encode(array_column($reportData['users']['candidate_growth'], 'count')); ?>,
                    borderColor: '#58a6ff',
                    backgroundColor: 'rgba(88, 166, 255, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: {
                            color: '#c9d1d9'
                        }
                    }
                },
                scales: {
                    x: {
                        ticks: {
                            color: '#8b949e'
                        },
                        grid: {
                            color: '#30363d'
                        }
                    },
                    y: {
                        ticks: {
                            color: '#8b949e'
                        },
                        grid: {
                            color: '#30363d'
                        }
                    }
                }
            }
        });

        // Job Type Chart
        const jobTypeCtx = document.getElementById('jobTypeChart').getContext('2d');
        new Chart(jobTypeCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_column($reportData['jobs']['job_types'], 'JobType')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($reportData['jobs']['job_types'], 'count')); ?>,
                    backgroundColor: [
                        '#58a6ff',
                        '#f59e0b',
                        '#3fb950',
                        '#f85149',
                        '#d29922'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: {
                            color: '#c9d1d9'
                        }
                    }
                }
            }
        });

        // Application Trends Chart
        const applicationTrendsCtx = document.getElementById('applicationTrendsChart').getContext('2d');
        new Chart(applicationTrendsCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($reportData['applications']['application_trends'], 'month')); ?>,
                datasets: [{
                    label: 'Applications',
                    data: <?php echo json_encode(array_column($reportData['applications']['application_trends'], 'count')); ?>,
                    backgroundColor: 'rgba(88, 166, 255, 0.8)',
                    borderColor: '#58a6ff',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: {
                            color: '#c9d1d9'
                        }
                    }
                },
                scales: {
                    x: {
                        ticks: {
                            color: '#8b949e'
                        },
                        grid: {
                            color: '#30363d'
                        }
                    },
                    y: {
                        ticks: {
                            color: '#8b949e'
                        },
                        grid: {
                            color: '#30363d'
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>

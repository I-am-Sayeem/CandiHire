<?php
// AdminDashboard.php - Admin Dashboard
session_start();
require_once 'admin_session_manager.php';
require_once 'Database.php';

// Check if admin is logged in
requireAdminLogin();

// Get system statistics
$stats = getSystemStats($pdo);
$recentActivity = getRecentActivity($pdo);
$adminUsername = getCurrentAdminUsername();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - CandiHire</title>
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
            --warning: #d29922;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Noto Sans', Helvetica, Arial, sans-serif;
        }

        body {
            background: #0d1117;
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
            max-width: 1200px;
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

        .logout-btn {
            background: var(--danger);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            background: #dc2626;
            transform: translateY(-1px);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: var(--bg-secondary);
            border-radius: 12px;
            padding: 25px;
            border: 1px solid var(--border);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .stat-title {
            font-size: 0.9rem;
            color: var(--text-secondary);
            font-weight: 500;
        }

        .stat-icon {
            font-size: 1.5rem;
            color: var(--accent-1);
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 5px;
        }

        .stat-change {
            font-size: 0.8rem;
            color: var(--success);
        }

        .main-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }

        .content-card {
            background: var(--bg-secondary);
            border-radius: 12px;
            padding: 25px;
            border: 1px solid var(--border);
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
        }

        .view-all-btn {
            background: transparent;
            color: var(--accent-1);
            border: 1px solid var(--accent-1);
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.8rem;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .view-all-btn:hover {
            background: var(--accent-1);
            color: white;
        }

        .activity-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px 0;
            border-bottom: 1px solid var(--border);
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }

        .activity-icon.job_application {
            background: rgba(88, 166, 255, 0.1);
            color: var(--accent-1);
        }

        .activity-icon.job_post {
            background: rgba(63, 185, 80, 0.1);
            color: var(--success);
        }

        .activity-content {
            flex: 1;
        }

        .activity-title {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 3px;
        }

        .activity-description {
            font-size: 0.9rem;
            color: var(--text-secondary);
        }

        .activity-time {
            font-size: 0.8rem;
            color: var(--text-secondary);
        }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }

        .action-btn {
            background: var(--bg-tertiary);
            color: var(--text-primary);
            border: 1px solid var(--border);
            padding: 15px 20px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 500;
        }

        .action-btn:hover {
            background: var(--accent-1);
            color: white;
            transform: translateY(-2px);
        }

        .nav-menu {
            background: var(--bg-secondary);
            border-radius: 12px;
            padding: 20px;
            border: 1px solid var(--border);
        }

        .nav-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 15px;
        }

        .nav-item {
            display: block;
            padding: 12px 15px;
            color: var(--text-secondary);
            text-decoration: none;
            border-radius: 8px;
            margin-bottom: 5px;
            transition: all 0.3s ease;
        }

        .nav-item:hover {
            background: var(--bg-tertiary);
            color: var(--text-primary);
        }

        .nav-item.active {
            background: var(--accent-1);
            color: white;
        }

        @media (max-width: 768px) {
            .main-content {
                grid-template-columns: 1fr;
            }
            
            .dashboard-grid {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            }
            
            .header-content {
                flex-direction: column;
                gap: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <div class="header-content">
            <div class="admin-title">
                <i class="fas fa-shield-alt"></i> CandiHire Admin Panel
            </div>
            <div class="admin-user">
                <div class="user-info">
                    <div class="user-name"><?php echo htmlspecialchars($adminUsername); ?></div>
                    <div class="user-role">System Administrator</div>
                </div>
                <button class="logout-btn" onclick="adminLogout()">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="dashboard-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-title">Total Candidates</span>
                    <i class="fas fa-users stat-icon"></i>
                </div>
                <div class="stat-value"><?php echo number_format($stats['candidates']); ?></div>
                <div class="stat-change">+12% from last month</div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-title">Total Companies</span>
                    <i class="fas fa-building stat-icon"></i>
                </div>
                <div class="stat-value"><?php echo number_format($stats['companies']); ?></div>
                <div class="stat-change">+8% from last month</div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-title">Job Posts</span>
                    <i class="fas fa-briefcase stat-icon"></i>
                </div>
                <div class="stat-value"><?php echo number_format($stats['job_posts']); ?></div>
                <div class="stat-change"><?php echo number_format($stats['active_jobs']); ?> active</div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-title">Applications</span>
                    <i class="fas fa-file-alt stat-icon"></i>
                </div>
                <div class="stat-value"><?php echo number_format($stats['applications']); ?></div>
                <div class="stat-change">+25% from last month</div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-title">Exams Created</span>
                    <i class="fas fa-clipboard-list stat-icon"></i>
                </div>
                <div class="stat-value"><?php echo number_format($stats['exams']); ?></div>
                <div class="stat-change"><?php echo number_format($stats['exam_assignments']); ?> assigned</div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-title">Completed Exams</span>
                    <i class="fas fa-check-circle stat-icon"></i>
                </div>
                <div class="stat-value"><?php echo number_format($stats['completed_exams']); ?></div>
                <div class="stat-change">+18% completion rate</div>
            </div>
        </div>

        <div class="main-content">
            <div class="content-card">
                <div class="card-header">
                    <h3 class="card-title">Recent Activity</h3>
                    <a href="AdminReports.php#recent-activity" class="view-all-btn">View All</a>
                </div>
                <div class="activity-list">
                    <?php if (empty($recentActivity)): ?>
                        <div style="text-align: center; color: var(--text-secondary); padding: 20px;">
                            <i class="fas fa-inbox" style="font-size: 2rem; margin-bottom: 10px;"></i>
                            <p>No recent activity</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recentActivity as $activity): ?>
                            <div class="activity-item">
                                <div class="activity-icon <?php echo $activity['type']; ?>">
                                    <i class="fas fa-<?php echo $activity['type'] === 'job_application' ? 'file-alt' : 'briefcase'; ?>"></i>
                                </div>
                                <div class="activity-content">
                                    <div class="activity-title"><?php echo htmlspecialchars($activity['user_name']); ?></div>
                                    <div class="activity-description"><?php echo htmlspecialchars($activity['description']); ?></div>
                                </div>
                                <div class="activity-time">
                                    <?php echo date('M j, Y', strtotime($activity['date'])); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="nav-menu">
                <div class="nav-title">Admin Navigation</div>
                <a href="AdminUsers.php" class="nav-item">
                    <i class="fas fa-users"></i> Manage Users
                </a>
                <a href="AdminJobs.php" class="nav-item">
                    <i class="fas fa-briefcase"></i> Manage Job Posts
                </a>
                <a href="AdminComplaints.php" class="nav-item">
                    <i class="fas fa-exclamation-triangle"></i> Handle Complaints
                </a>
                <a href="AdminReports.php" class="nav-item">
                    <i class="fas fa-chart-bar"></i> System Reports
                </a>
                <a href="AdminSettings.php" class="nav-item">
                    <i class="fas fa-cog"></i> System Settings
                </a>
            </div>
        </div>

    </div>

    <script>
        function adminLogout() {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = 'admin_logout.php';
            }
        }
    </script>
</body>
</html>

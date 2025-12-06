<?php
// AdminJobs.php - Job Posts Management Page
session_start();
require_once 'admin_session_manager.php';
require_once 'Database.php';

// Check if admin is logged in
requireAdminLogin();

// Check database connection
if (!$pdo) {
    die("Database connection failed. Please try again later.");
}

// Check if required tables exist
try {
    $pdo->query("SELECT 1 FROM job_postings LIMIT 1");
} catch (PDOException $e) {
    die("Required database tables not found. Please check your database setup.");
}

// Handle job actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $job_id = $_POST['job_id'] ?? '';
    
    // Validate input
    if (empty($action) || empty($job_id)) {
        $error_message = "Missing required parameters.";
    } elseif (!in_array($action, ['delete'])) {
        $error_message = "Invalid action.";
    } elseif (!is_numeric($job_id) || $job_id <= 0) {
        $error_message = "Invalid job ID.";
    } else {
        try {
            if ($action === 'delete') {
                $stmt = $pdo->prepare("DELETE FROM job_postings WHERE JobID = ?");
                if ($stmt && $stmt->execute([$job_id])) {
                    $success_message = "Job post deleted successfully.";
                } else {
                    $error_message = "Failed to delete job post.";
                }
            }
        } catch (PDOException $e) {
            error_log("Database error in job action: " . $e->getMessage());
            $error_message = "Database error occurred. Please try again later.";
        } catch (Exception $e) {
            error_log("General error in job action: " . $e->getMessage());
            $error_message = "An unexpected error occurred. Please try again later.";
        }
    }
}

// Get job posts data
$job_posts = [];
$stats = [];

try {
    // Get job posts with company info
    $stmt = $pdo->query("
        SELECT jp.*, cli.CompanyName, cli.Email as CompanyEmail,
               (SELECT COUNT(*) FROM job_applications WHERE JobID = jp.JobID) as application_count
        FROM job_postings jp
        JOIN Company_login_info cli ON jp.CompanyID = cli.CompanyID
        ORDER BY jp.CreatedAt DESC
    ");
    $job_posts = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    
    // Get statistics
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM job_postings");
    $stats['total'] = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC)['total'] : 0;
    
    $stmt = $pdo->query("SELECT COUNT(*) as active FROM job_postings WHERE Status = 'active'");
    $stats['active'] = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC)['active'] : 0;
    
    $stmt = $pdo->query("SELECT COUNT(*) as applications FROM job_applications");
    $stats['applications'] = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC)['applications'] : 0;
    
} catch (PDOException $e) {
    error_log("Database error fetching job posts: " . $e->getMessage());
    $job_posts = [];
    $stats = ['total' => 0, 'active' => 0, 'applications' => 0];
    $error_message = "Failed to load job data. Please try again later.";
} catch (Exception $e) {
    error_log("General error fetching job posts: " . $e->getMessage());
    $job_posts = [];
    $stats = ['total' => 0, 'active' => 0, 'applications' => 0];
    $error_message = "An unexpected error occurred. Please try again later.";
}

$adminUsername = getCurrentAdminUsername();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Management - CandiHire Admin</title>
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

        .back-btn {
            background: transparent;
            color: var(--text-secondary);
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
            background: var(--bg-tertiary);
            color: var(--text-primary);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        .page-header {
            margin-bottom: 30px;
        }

        .page-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 10px;
            color: var(--text-primary);
        }

        .page-subtitle {
            color: var(--text-secondary);
            font-size: 1rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: var(--bg-secondary);
            border-radius: 12px;
            padding: 20px;
            border: 1px solid var(--border);
            text-align: center;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--accent-1);
            margin-bottom: 5px;
        }

        .stat-label {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        .filters {
            background: var(--bg-secondary);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 30px;
            border: 1px solid var(--border);
        }

        .filter-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            align-items: end;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
        }

        .filter-label {
            font-size: 0.9rem;
            color: var(--text-secondary);
            margin-bottom: 5px;
        }

        .filter-select {
            padding: 8px 12px;
            border: 1px solid var(--border);
            border-radius: 6px;
            background: var(--bg-tertiary);
            color: var(--text-primary);
            font-size: 0.9rem;
        }

        .filter-btn {
            background: var(--accent-1);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .filter-btn:hover {
            background: var(--accent-hover);
        }

        .data-table {
            background: var(--bg-secondary);
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid var(--border);
        }

        .table-header {
            background: var(--bg-tertiary);
            padding: 20px;
            border-bottom: 1px solid var(--border);
        }

        .table-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th {
            background: var(--bg-tertiary);
            color: var(--text-primary);
            font-weight: 600;
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }

        .table td {
            padding: 15px;
            border-bottom: 1px solid var(--border);
            color: var(--text-primary);
        }

        .table tr:hover {
            background: var(--bg-tertiary);
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-active {
            background: rgba(63, 185, 80, 0.1);
            color: var(--success);
        }

        .status-inactive {
            background: rgba(248, 81, 73, 0.1);
            color: var(--danger);
        }


        .action-buttons {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.8rem;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .btn-primary {
            background: var(--accent-1);
            color: white;
        }

        .btn-primary:hover {
            background: var(--accent-hover);
        }

        .btn-warning {
            background: var(--warning);
            color: white;
        }

        .btn-warning:hover {
            background: #b7791f;
        }

        .btn-danger {
            background: var(--danger);
            color: white;
        }

        .btn-danger:hover {
            background: #dc2626;
        }

        .btn-success {
            background: var(--success);
            color: white;
        }

        .btn-success:hover {
            background: #2ea043;
        }

        .message {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .message.success {
            background: rgba(63, 185, 80, 0.1);
            color: var(--success);
            border: 1px solid var(--success);
        }

        .message.error {
            background: rgba(248, 81, 73, 0.1);
            color: var(--danger);
            border: 1px solid var(--danger);
        }

        .job-title {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 3px;
        }

        .job-company {
            font-size: 0.9rem;
            color: var(--text-secondary);
        }

        .job-meta {
            font-size: 0.8rem;
            color: var(--text-secondary);
            margin-top: 3px;
        }

        .salary {
            font-weight: 600;
            color: var(--accent-1);
        }

        @media (max-width: 768px) {
            .table {
                font-size: 0.9rem;
            }
            
            .table th,
            .table td {
                padding: 10px 8px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .filter-row {
                grid-template-columns: 1fr;
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
            <a href="AdminDashboard.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <div class="container">
        <div class="page-header">
            <h1 class="page-title">Job Posts Management</h1>
            <p class="page-subtitle">Manage all job postings in the system</p>
        </div>

        <?php if (isset($success_message)): ?>
            <div class="message success">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="message error">
                <i class="fas fa-exclamation-triangle"></i>
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo $stats['total'] ?? 0; ?></div>
                <div class="stat-label">Total Jobs</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $stats['active'] ?? 0; ?></div>
                <div class="stat-label">Active Jobs</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $stats['applications'] ?? 0; ?></div>
                <div class="stat-label">Total Applications</div>
            </div>
        </div>

        <div class="filters">
            <div class="filter-row">
                <div class="filter-group">
                    <label class="filter-label">Status</label>
                    <select class="filter-select" id="statusFilter">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label">Department</label>
                    <select class="filter-select" id="departmentFilter">
                        <option value="">All Departments</option>
                        <option value="Software Engineering">Software Engineering</option>
                        <option value="Data Science">Data Science</option>
                        <option value="Product Management">Product Management</option>
                        <option value="Design">Design</option>
                        <option value="DevOps">DevOps</option>
                        <option value="Quality Assurance">Quality Assurance</option>
                        <option value="Business">Business</option>
                        <option value="Marketing">Marketing</option>
                        <option value="Human Resources">Human Resources</option>
                        <option value="Sales">Sales</option>
                        <option value="Finance">Finance</option>
                    </select>
                </div>
                <div class="filter-group">
                    <button class="filter-btn" onclick="applyFilters()">
                        <i class="fas fa-filter"></i> Apply Filters
                    </button>
                </div>
            </div>
        </div>

        <div class="data-table">
            <div class="table-header">
                <h3 class="table-title">Job Posts List</h3>
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th>Job Title</th>
                        <th>Company</th>
                        <th>Department</th>
                        <th>Location</th>
                        <th>Salary</th>
                        <th>Applications</th>
                        <th>Status</th>
                        <th>Posted</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($job_posts as $job): ?>
                        <tr>
                            <td>
                                <div class="job-title">
                                    <?php echo htmlspecialchars($job['JobTitle']); ?>
                                </div>
                                <div class="job-meta"><?php echo htmlspecialchars($job['JobType']); ?></div>
                            </td>
                            <td>
                                <div class="job-company"><?php echo htmlspecialchars($job['CompanyName']); ?></div>
                            </td>
                            <td><?php echo htmlspecialchars($job['Department']); ?></td>
                            <td><?php echo htmlspecialchars($job['Location']); ?></td>
                            <td>
                                <?php if ($job['SalaryMin'] && $job['SalaryMax']): ?>
                                    <span class="salary">
                                        <?php echo $job['Currency'] . ' ' . number_format($job['SalaryMin']) . ' - ' . number_format($job['SalaryMax']); ?>
                                    </span>
                                <?php else: ?>
                                    <span style="color: var(--text-secondary);">Not specified</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $job['application_count']; ?></td>
                            <td>
                                <span class="status-badge status-<?php echo $job['Status']; ?>">
                                    <?php echo ucfirst($job['Status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M j, Y', strtotime($job['CreatedAt'])); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this job post? This action cannot be undone.');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="job_id" value="<?php echo $job['JobID']; ?>">
                                        <button type="submit" class="btn btn-danger">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function applyFilters() {
            const status = document.getElementById('statusFilter').value;
            const department = document.getElementById('departmentFilter').value;
            
            // Simple client-side filtering (in a real app, this would be server-side)
            const rows = document.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                let show = true;
                
                if (status) {
                    const statusBadge = row.querySelector('.status-badge');
                    if (statusBadge && !statusBadge.classList.contains(`status-${status}`)) {
                        show = false;
                    }
                }
                
                if (department) {
                    const deptCell = row.cells[2];
                    if (deptCell && !deptCell.textContent.includes(department)) {
                        show = false;
                    }
                }
                
                row.style.display = show ? '' : 'none';
            });
        }
    </script>
</body>
</html>

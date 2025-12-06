<?php
// AdminUsers.php - User Management Page
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
    $pdo->query("SELECT 1 FROM candidate_login_info LIMIT 1");
    $pdo->query("SELECT 1 FROM Company_login_info LIMIT 1");
} catch (PDOException $e) {
    die("Required database tables not found. Please check your database setup.");
}

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $user_id = $_POST['user_id'] ?? '';
    $user_type = $_POST['user_type'] ?? '';
    
    // Validate input
    if (empty($action) || empty($user_id) || empty($user_type)) {
        $error_message = "Missing required parameters.";
    } elseif (!in_array($action, ['delete'])) {
        $error_message = "Invalid action.";
    } elseif (!in_array($user_type, ['candidate', 'company'])) {
        $error_message = "Invalid user type.";
    } elseif (!is_numeric($user_id) || $user_id <= 0) {
        $error_message = "Invalid user ID.";
    } else {
        try {
            if ($user_type === 'candidate') {
                $stmt = $pdo->prepare("DELETE FROM candidate_login_info WHERE CandidateID = ?");
                if ($stmt && $stmt->execute([$user_id])) {
                    $success_message = "Candidate deleted successfully.";
                } else {
                    $error_message = "Failed to delete candidate.";
                }
            } elseif ($user_type === 'company') {
                $stmt = $pdo->prepare("DELETE FROM Company_login_info WHERE CompanyID = ?");
                if ($stmt && $stmt->execute([$user_id])) {
                    $success_message = "Company deleted successfully.";
                } else {
                    $error_message = "Failed to delete company.";
                }
            } else {
                $error_message = "Invalid user type.";
            }
        } catch (PDOException $e) {
            error_log("Database error in user action: " . $e->getMessage());
            $error_message = "Database error occurred. Please try again later.";
        } catch (Exception $e) {
            error_log("General error in user action: " . $e->getMessage());
            $error_message = "An unexpected error occurred. Please try again later.";
        }
    }
}

// Get users data
$candidates = [];
$companies = [];

try {
    // Get candidates
    $stmt = $pdo->query("
        SELECT CandidateID, FullName, Email, PhoneNumber as Phone, 
               CASE WHEN IsActive = 1 THEN 'active' ELSE 'inactive' END as Status, 
               CreatedAt, 
               (SELECT COUNT(*) FROM job_applications WHERE CandidateID = cli.CandidateID) as application_count
        FROM candidate_login_info cli
        ORDER BY CreatedAt DESC
    ");
    $candidates = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    
    // Get companies
    $stmt = $pdo->query("
        SELECT CompanyID, CompanyName, Email, PhoneNumber as Phone, 
               CASE WHEN IsActive = 1 THEN 'active' ELSE 'inactive' END as Status, 
               CreatedAt,
               (SELECT COUNT(*) FROM job_postings WHERE CompanyID = cli.CompanyID) as job_count
        FROM Company_login_info cli
        ORDER BY CreatedAt DESC
    ");
    $companies = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    
} catch (PDOException $e) {
    error_log("Database error fetching users: " . $e->getMessage());
    $candidates = [];
    $companies = [];
    $error_message = "Failed to load user data. Please try again later.";
} catch (Exception $e) {
    error_log("General error fetching users: " . $e->getMessage());
    $candidates = [];
    $companies = [];
    $error_message = "An unexpected error occurred. Please try again later.";
}

$adminUsername = getCurrentAdminUsername();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - CandiHire Admin</title>
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

        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            border-bottom: 1px solid var(--border);
        }

        .tab {
            padding: 12px 24px;
            background: transparent;
            border: none;
            color: var(--text-secondary);
            cursor: pointer;
            font-size: 1rem;
            font-weight: 500;
            transition: all 0.3s ease;
            border-bottom: 2px solid transparent;
        }

        .tab.active {
            color: var(--accent-1);
            border-bottom-color: var(--accent-1);
        }

        .tab:hover {
            color: var(--text-primary);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
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

        .search-section {
            background: var(--bg-secondary);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid var(--border);
            display: flex;
            gap: 20px;
            align-items: center;
            flex-wrap: wrap;
        }

        .search-bar {
            position: relative;
            flex: 1;
            min-width: 300px;
        }

        .search-bar i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
        }

        .search-bar input {
            width: 100%;
            padding: 10px 12px 10px 40px;
            background: var(--bg-tertiary);
            border: 1px solid var(--border);
            border-radius: 8px;
            color: var(--text-primary);
            font-size: 0.9rem;
        }

        .search-bar input:focus {
            outline: none;
            border-color: var(--accent-1);
            box-shadow: 0 0 0 2px rgba(88, 166, 255, 0.2);
        }

        .filter-options {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .filter-options select {
            padding: 10px 12px;
            background: var(--bg-tertiary);
            border: 1px solid var(--border);
            border-radius: 8px;
            color: var(--text-primary);
            font-size: 0.9rem;
            cursor: pointer;
        }

        .filter-options select:focus {
            outline: none;
            border-color: var(--accent-1);
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent-1), var(--accent-2));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 1.1rem;
            margin-right: 12px;
        }

        .user-info {
            display: flex;
            align-items: center;
        }

        .user-details {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 2px;
        }

        .user-email {
            font-size: 0.85rem;
            color: var(--text-secondary);
        }

        .no-results {
            text-align: center;
            padding: 40px 20px;
            color: var(--text-secondary);
        }

        .no-results i {
            font-size: 3rem;
            margin-bottom: 15px;
            opacity: 0.5;
        }

        .application-count, .job-count {
            background: var(--accent-1);
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
            min-width: 24px;
            text-align: center;
            display: inline-block;
        }

        .user-avatar i {
            font-size: 1rem;
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
                gap: 4px;
            }
            
            .search-section {
                flex-direction: column;
                align-items: stretch;
            }
            
            .search-bar {
                min-width: auto;
            }
            
            .filter-options {
                justify-content: space-between;
            }
            
            .user-info {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .user-avatar {
                margin-right: 0;
                margin-bottom: 8px;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
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
            <h1 class="page-title">User Management</h1>
            <p class="page-subtitle">Manage candidates and companies in the system</p>
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
                <div class="stat-value"><?php echo count($candidates); ?></div>
                <div class="stat-label">Total Candidates</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo count($companies); ?></div>
                <div class="stat-label">Total Companies</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo count(array_filter($candidates, fn($c) => $c['Status'] === 'active')); ?></div>
                <div class="stat-label">Active Candidates</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo count(array_filter($companies, fn($c) => $c['Status'] === 'active')); ?></div>
                <div class="stat-label">Active Companies</div>
            </div>
        </div>

        <div class="tabs">
            <button class="tab active" onclick="switchTab('candidates')">
                <i class="fas fa-users"></i> Candidates (<?php echo count($candidates); ?>)
            </button>
            <button class="tab" onclick="switchTab('companies')">
                <i class="fas fa-building"></i> Companies (<?php echo count($companies); ?>)
            </button>
        </div>

        <!-- Search and Filter Section -->
        <div class="search-section">
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Search users..." onkeyup="filterUsers()">
            </div>
            <div class="filter-options">
                <select id="statusFilter" onchange="filterUsers()">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
                <select id="sortFilter" onchange="sortUsers()">
                    <option value="newest">Newest First</option>
                    <option value="oldest">Oldest First</option>
                    <option value="name">Name A-Z</option>
                </select>
            </div>
        </div>

        <div id="candidates" class="tab-content active">
            <div class="data-table">
                <div class="table-header">
                    <h3 class="table-title">Candidates List</h3>
                </div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Applications</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($candidates as $candidate): ?>
                            <tr data-user-type="candidate" data-status="<?php echo $candidate['Status']; ?>" data-name="<?php echo strtolower(htmlspecialchars($candidate['FullName'])); ?>" data-email="<?php echo strtolower(htmlspecialchars($candidate['Email'])); ?>">
                                <td>
                                    <div class="user-info">
                                        <div class="user-avatar">
                                            <?php echo strtoupper(substr($candidate['FullName'], 0, 1)); ?>
                                        </div>
                                        <div class="user-details">
                                            <div class="user-name"><?php echo htmlspecialchars($candidate['FullName']); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($candidate['Phone'] ?? 'N/A'); ?></td>
                                <td>
                                    <span class="application-count"><?php echo $candidate['application_count']; ?></span>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo $candidate['Status']; ?>">
                                        <?php echo ucfirst($candidate['Status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($candidate['CreatedAt'])); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this candidate? This action cannot be undone.');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="user_id" value="<?php echo $candidate['CandidateID']; ?>">
                                            <input type="hidden" name="user_type" value="candidate">
                                            <button type="submit" class="btn btn-danger" title="Delete">
                                                <i class="fas fa-trash"></i>
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

        <div id="companies" class="tab-content">
            <div class="data-table">
                <div class="table-header">
                    <h3 class="table-title">Companies List</h3>
                </div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Company Name</th>
                            <th>Phone</th>
                            <th>Job Posts</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($companies as $company): ?>
                            <tr data-user-type="company" data-status="<?php echo $company['Status']; ?>" data-name="<?php echo strtolower(htmlspecialchars($company['CompanyName'])); ?>" data-email="<?php echo strtolower(htmlspecialchars($company['Email'])); ?>">
                                <td>
                                    <div class="user-info">
                                        <div class="user-avatar">
                                            <i class="fas fa-building"></i>
                                        </div>
                                        <div class="user-details">
                                            <div class="user-name"><?php echo htmlspecialchars($company['CompanyName']); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($company['Phone'] ?? 'N/A'); ?></td>
                                <td>
                                    <span class="job-count"><?php echo $company['job_count']; ?></span>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo $company['Status']; ?>">
                                        <?php echo ucfirst($company['Status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($company['CreatedAt'])); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this company? This action cannot be undone.');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="user_id" value="<?php echo $company['CompanyID']; ?>">
                                            <input type="hidden" name="user_type" value="company">
                                            <button type="submit" class="btn btn-danger" title="Delete">
                                                <i class="fas fa-trash"></i>
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
    </div>

    <script>
        function switchTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            
            // Remove active class from all tabs
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Show selected tab content
            document.getElementById(tabName).classList.add('active');
            
            // Add active class to clicked tab
            event.target.classList.add('active');
            
            // Reset filters when switching tabs
            document.getElementById('searchInput').value = '';
            document.getElementById('statusFilter').value = '';
            document.getElementById('sortFilter').value = 'newest';
            filterUsers();
        }

        function filterUsers() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const statusFilter = document.getElementById('statusFilter').value;
            const currentTab = document.querySelector('.tab-content.active').id;
            
            const rows = document.querySelectorAll(`#${currentTab} tbody tr`);
            let visibleCount = 0;
            
            rows.forEach(row => {
                const name = row.getAttribute('data-name') || '';
                const email = row.getAttribute('data-email') || '';
                const status = row.getAttribute('data-status') || '';
                const userType = row.getAttribute('data-user-type') || '';
                
                let show = true;
                
                // Search filter
                if (searchTerm && !name.includes(searchTerm)) {
                    show = false;
                }
                
                // Status filter
                if (statusFilter && status !== statusFilter) {
                    show = false;
                }
                
                row.style.display = show ? '' : 'none';
                if (show) visibleCount++;
            });
            
            // Show no results message if needed
            let noResultsMsg = document.querySelector(`#${currentTab} .no-results`);
            if (visibleCount === 0) {
                if (!noResultsMsg) {
                    const tbody = document.querySelector(`#${currentTab} tbody`);
                    tbody.innerHTML += `
                        <tr class="no-results">
                            <td colspan="6">
                                <div class="no-results">
                                    <i class="fas fa-search"></i>
                                    <h3>No users found</h3>
                                    <p>Try adjusting your search or filter criteria</p>
                                </div>
                            </td>
                        </tr>
                    `;
                }
            } else {
                if (noResultsMsg) {
                    noResultsMsg.remove();
                }
            }
        }

        function sortUsers() {
            const sortBy = document.getElementById('sortFilter').value;
            const currentTab = document.querySelector('.tab-content.active').id;
            const tbody = document.querySelector(`#${currentTab} tbody`);
            const rows = Array.from(tbody.querySelectorAll('tr:not(.no-results)'));
            
            rows.sort((a, b) => {
                let aVal, bVal;
                
                switch (sortBy) {
                    case 'name':
                        aVal = a.getAttribute('data-name') || '';
                        bVal = b.getAttribute('data-name') || '';
                        return aVal.localeCompare(bVal);
                    case 'oldest':
                        aVal = new Date(a.cells[4].textContent);
                        bVal = new Date(b.cells[4].textContent);
                        return aVal - bVal;
                    case 'newest':
                    default:
                        aVal = new Date(a.cells[4].textContent);
                        bVal = new Date(b.cells[4].textContent);
                        return bVal - aVal;
                }
            });
            
            // Clear tbody and re-append sorted rows
            tbody.innerHTML = '';
            rows.forEach(row => tbody.appendChild(row));
        }

        // Initialize filters on page load
        document.addEventListener('DOMContentLoaded', function() {
            filterUsers();
        });
    </script>
</body>
</html>

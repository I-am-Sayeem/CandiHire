<?php
// AdminComplaints.php - Complaints Management Page
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
    $pdo->query("SELECT 1 FROM complaints LIMIT 1");
} catch (PDOException $e) {
    die("Required database tables not found. Please check your database setup.");
}

// Handle complaint actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $complaint_id = $_POST['complaint_id'] ?? '';
    $resolution = $_POST['resolution'] ?? '';
    
    // Validate input
    if (empty($action) || empty($complaint_id)) {
        $error_message = "Missing required parameters.";
    } elseif (!in_array($action, ['resolve', 'reopen', 'close', 'in_progress'])) {
        $error_message = "Invalid action.";
    } elseif (!is_numeric($complaint_id) || $complaint_id <= 0) {
        $error_message = "Invalid complaint ID.";
    } elseif ($action === 'resolve' && empty($resolution)) {
        $error_message = "Resolution details are required.";
    } else {
        try {
            if ($action === 'resolve') {
                $stmt = $pdo->prepare("
                    UPDATE complaints 
                    SET Status = 'resolved', 
                        ResolutionDetails = ?, 
                        ResolvedBy = ?, 
                        ResolutionDate = NOW() 
                    WHERE ComplaintID = ?
                ");
                if ($stmt && $stmt->execute([$resolution, 1, $complaint_id])) {
                    $success_message = "Complaint resolved successfully.";
                } else {
                    $error_message = "Failed to resolve complaint.";
                }
            } elseif ($action === 'reopen') {
                $stmt = $pdo->prepare("
                    UPDATE complaints 
                    SET Status = 'pending', 
                        ResolutionDetails = NULL, 
                        ResolvedBy = NULL, 
                        ResolutionDate = NULL 
                    WHERE ComplaintID = ?
                ");
                if ($stmt && $stmt->execute([$complaint_id])) {
                    $success_message = "Complaint reopened successfully.";
                } else {
                    $error_message = "Failed to reopen complaint.";
                }
            } elseif ($action === 'close') {
                $stmt = $pdo->prepare("
                    UPDATE complaints 
                    SET Status = 'closed' 
                    WHERE ComplaintID = ?
                ");
                if ($stmt && $stmt->execute([$complaint_id])) {
                    $success_message = "Complaint closed successfully.";
                } else {
                    $error_message = "Failed to close complaint.";
                }
            } elseif ($action === 'in_progress') {
                $stmt = $pdo->prepare("
                    UPDATE complaints 
                    SET Status = 'in-progress' 
                    WHERE ComplaintID = ?
                ");
                if ($stmt && $stmt->execute([$complaint_id])) {
                    $success_message = "Complaint marked as in progress.";
                } else {
                    $error_message = "Failed to update complaint status.";
                }
            }
        } catch (PDOException $e) {
            error_log("Database error in complaint action: " . $e->getMessage());
            $error_message = "Database error occurred. Please try again later.";
        } catch (Exception $e) {
            error_log("General error in complaint action: " . $e->getMessage());
            $error_message = "An unexpected error occurred. Please try again later.";
        }
    }
}

// Get complaints data
$complaints = [];
$stats = [];

try {
    // Get complaints with user info
    $stmt = $pdo->query("
        SELECT c.*, 
               COALESCE(cli.FullName, cli_comp.CompanyName) as UserName,
               COALESCE(cli.Email, cli_comp.Email) as UserEmail,
               CASE 
                   WHEN c.UserType = 'candidate' THEN 'Candidate'
                   WHEN c.UserType = 'company' THEN 'Company'
                   ELSE 'Unknown'
               END as UserTypeDisplay
        FROM complaints c
        LEFT JOIN candidate_login_info cli ON c.UserID = cli.CandidateID AND c.UserType = 'candidate'
        LEFT JOIN Company_login_info cli_comp ON c.UserID = cli_comp.CompanyID AND c.UserType = 'company'
        ORDER BY c.ComplaintDate DESC
    ");
    $complaints = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    
    // Get statistics
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM complaints");
    $stats['total'] = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC)['total'] : 0;
    
    $stmt = $pdo->query("SELECT COUNT(*) as pending FROM complaints WHERE Status = 'pending'");
    $stats['pending'] = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC)['pending'] : 0;
    
    $stmt = $pdo->query("SELECT COUNT(*) as in_progress FROM complaints WHERE Status = 'in-progress'");
    $stats['in_progress'] = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC)['in_progress'] : 0;
    
    $stmt = $pdo->query("SELECT COUNT(*) as resolved FROM complaints WHERE Status = 'resolved'");
    $stats['resolved'] = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC)['resolved'] : 0;
    
    $stmt = $pdo->query("SELECT COUNT(*) as closed FROM complaints WHERE Status = 'closed'");
    $stats['closed'] = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC)['closed'] : 0;
    
} catch (PDOException $e) {
    error_log("Database error fetching complaints: " . $e->getMessage());
    $complaints = [];
    $stats = ['total' => 0, 'pending' => 0, 'in_progress' => 0, 'resolved' => 0, 'closed' => 0];
    $error_message = "Failed to load complaint data. Please try again later.";
} catch (Exception $e) {
    error_log("General error fetching complaints: " . $e->getMessage());
    $complaints = [];
    $stats = ['total' => 0, 'pending' => 0, 'in_progress' => 0, 'resolved' => 0, 'closed' => 0];
    $error_message = "An unexpected error occurred. Please try again later.";
}

$adminUsername = getCurrentAdminUsername();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complaints Management - CandiHire Admin</title>
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

        .status-pending {
            background: rgba(248, 81, 73, 0.1);
            color: var(--danger);
        }

        .status-in-progress {
            background: rgba(88, 166, 255, 0.1);
            color: var(--accent);
        }

        .status-resolved {
            background: rgba(63, 185, 80, 0.1);
            color: var(--success);
        }

        .status-closed {
            background: rgba(139, 148, 158, 0.1);
            color: var(--text-secondary);
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

        .btn-success {
            background: var(--success);
            color: white;
        }

        .btn-success:hover {
            background: #2ea043;
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

        .complaint-title {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 3px;
        }

        .complaint-description {
            font-size: 0.9rem;
            color: var(--text-secondary);
            margin-top: 5px;
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .complaint-user {
            font-size: 0.9rem;
            color: var(--text-secondary);
        }

        .complaint-date {
            font-size: 0.8rem;
            color: var(--text-secondary);
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background: var(--bg-secondary);
            margin: 5% auto;
            padding: 30px;
            border-radius: 12px;
            width: 90%;
            max-width: 600px;
            border: 1px solid var(--border);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border);
        }

        .modal-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        .close {
            color: var(--text-secondary);
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: var(--text-primary);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-primary);
        }

        .form-textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--border);
            border-radius: 6px;
            background: var(--bg-tertiary);
            color: var(--text-primary);
            font-size: 0.9rem;
            resize: vertical;
            min-height: 100px;
        }

        .form-textarea:focus {
            outline: none;
            border-color: var(--accent-1);
        }

        .modal-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 20px;
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

        .complaint-detail-section {
            margin-bottom: 20px;
        }

        .complaint-detail-section h4 {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .alert {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
        }

        .alert i {
            font-size: 16px;
        }

        /* Scrollbar styling for modal */
        #complaintDetails::-webkit-scrollbar {
            width: 8px;
        }

        #complaintDetails::-webkit-scrollbar-track {
            background: var(--bg-tertiary);
            border-radius: 4px;
        }

        #complaintDetails::-webkit-scrollbar-thumb {
            background: var(--accent);
            border-radius: 4px;
        }

        #complaintDetails::-webkit-scrollbar-thumb:hover {
            background: var(--accent-hover);
        }

        /* Smooth scrolling */
        #complaintDetails {
            scroll-behavior: smooth;
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
            <h1 class="page-title">Complaints Management</h1>
            <p class="page-subtitle">Handle user complaints and resolve issues</p>
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
                <div class="stat-label">Total Complaints</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $stats['pending'] ?? 0; ?></div>
                <div class="stat-label">Pending</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $stats['in_progress'] ?? 0; ?></div>
                <div class="stat-label">In Progress</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $stats['resolved'] ?? 0; ?></div>
                <div class="stat-label">Resolved</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $stats['closed'] ?? 0; ?></div>
                <div class="stat-label">Closed</div>
            </div>
        </div>

        <div class="filters">
            <div class="filter-row">
                <div class="filter-group">
                    <label class="filter-label">Status</label>
                    <select class="filter-select" id="statusFilter">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="in-progress">In Progress</option>
                        <option value="resolved">Resolved</option>
                        <option value="closed">Closed</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label">User Type</label>
                    <select class="filter-select" id="userTypeFilter">
                        <option value="">All Types</option>
                        <option value="candidate">Candidate</option>
                        <option value="company">Company</option>
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
                <h3 class="table-title">Complaints List</h3>
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th>Complaint</th>
                        <th>User</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($complaints as $complaint): ?>
                        <tr>
                            <td>
                                <div class="complaint-title">
                                    <?php echo htmlspecialchars($complaint['Subject']); ?>
                                    <?php if (strpos($complaint['Subject'], 'Job Report:') === 0): ?>
                                        <span class="report-badge" style="background: var(--warning); color: white; padding: 2px 8px; border-radius: 12px; font-size: 10px; margin-left: 8px;">JOB REPORT</span>
                                    <?php elseif (strpos($complaint['Subject'], 'Candidate Report:') === 0): ?>
                                        <span class="report-badge" style="background: var(--accent); color: white; padding: 2px 8px; border-radius: 12px; font-size: 10px; margin-left: 8px;">CANDIDATE REPORT</span>
                                    <?php endif; ?>
                                </div>
                                <div class="complaint-description">
                                    <?php 
                                    $description = $complaint['Description'];
                                    // Truncate long descriptions
                                    if (strlen($description) > 150) {
                                        $description = substr($description, 0, 150) . '...';
                                    }
                                    echo htmlspecialchars($description); 
                                    ?>
                                </div>
                            </td>
                            <td>
                                <div class="complaint-user">
                                    <?php echo htmlspecialchars($complaint['UserName']); ?>
                                    <div style="font-size: 0.8rem; color: var(--text-secondary);">
                                        <?php echo $complaint['UserTypeDisplay']; ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="status-badge status-<?php echo $complaint['Status']; ?>">
                                    <?php echo ucfirst($complaint['Status']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="complaint-date">
                                    <?php echo date('M j, Y', strtotime($complaint['ComplaintDate'])); ?>
                                </div>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn btn-primary" onclick="viewComplaint(<?php echo $complaint['ComplaintID']; ?>)">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                    <?php if ($complaint['Status'] === 'pending'): ?>
                                        <button class="btn btn-info" onclick="markInProgress(<?php echo $complaint['ComplaintID']; ?>)">
                                            <i class="fas fa-clock"></i> In Progress
                                        </button>
                                        <button class="btn btn-success" onclick="resolveComplaint(<?php echo $complaint['ComplaintID']; ?>)">
                                            <i class="fas fa-check"></i> Resolve
                                        </button>
                                    <?php elseif ($complaint['Status'] === 'in-progress'): ?>
                                        <button class="btn btn-success" onclick="resolveComplaint(<?php echo $complaint['ComplaintID']; ?>)">
                                            <i class="fas fa-check"></i> Resolve
                                        </button>
                                    <?php elseif ($complaint['Status'] === 'resolved'): ?>
                                        <button class="btn btn-warning" onclick="reopenComplaint(<?php echo $complaint['ComplaintID']; ?>)">
                                            <i class="fas fa-undo"></i> Reopen
                                        </button>
                                    <?php endif; ?>
                                    <?php if ($complaint['Status'] !== 'closed'): ?>
                                        <button class="btn btn-danger" onclick="closeComplaint(<?php echo $complaint['ComplaintID']; ?>)">
                                            <i class="fas fa-times"></i> Close
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- View Complaint Modal -->
    <div id="viewModal" class="modal">
        <div class="modal-content" style="max-width: 800px; max-height: 90vh; display: flex; flex-direction: column;">
            <div class="modal-header">
                <h3 class="modal-title">Complaint Details</h3>
                <span class="close" onclick="closeViewModal()">&times;</span>
            </div>
            <div class="modal-body" id="complaintDetails" style="flex: 1; overflow-y: auto; padding: 20px; max-height: calc(90vh - 120px);">
                <!-- Complaint details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeViewModal()">Close</button>
            </div>
        </div>
    </div>

    <!-- Resolution Modal -->
    <div id="resolutionModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Resolve Complaint</h3>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <form id="resolutionForm" method="POST">
                <input type="hidden" name="action" value="resolve">
                <input type="hidden" name="complaint_id" id="complaintId">
                <div class="form-group">
                    <label class="form-label">Resolution Details</label>
                    <textarea name="resolution" class="form-textarea" placeholder="Enter resolution details..." required></textarea>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-warning" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn btn-success">Resolve Complaint</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function applyFilters() {
            const status = document.getElementById('statusFilter').value;
            const userType = document.getElementById('userTypeFilter').value;
            
            const rows = document.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                let show = true;
                
                if (status) {
                    const statusBadge = row.querySelector('.status-badge');
                    if (statusBadge && !statusBadge.classList.contains(`status-${status}`)) {
                        show = false;
                    }
                }
                
                if (userType) {
                    const userCell = row.cells[1];
                    if (userCell && !userCell.textContent.toLowerCase().includes(userType)) {
                        show = false;
                    }
                }
                
                row.style.display = show ? '' : 'none';
            });
        }

        function viewComplaint(complaintId) {
            // Find the complaint data from the table
            const rows = document.querySelectorAll('tbody tr');
            let complaintData = null;
            
            rows.forEach(row => {
                const viewButton = row.querySelector(`button[onclick="viewComplaint(${complaintId})"]`);
                if (viewButton) {
                    const cells = row.cells;
                    const fullSubject = cells[0].querySelector('.complaint-title').textContent;
                    complaintData = {
                        subject: fullSubject.replace('JOB REPORT', '').replace('CANDIDATE REPORT', '').trim(),
                        fullSubject: fullSubject,
                        user: cells[1].querySelector('.complaint-user').textContent.trim(),
                        userType: cells[1].querySelector('div').textContent.trim(),
                        status: cells[2].querySelector('.status-badge').textContent.trim(),
                        created: cells[3].textContent.trim(),
                        description: cells[0].querySelector('.complaint-description').textContent.trim()
                    };
                }
            });
            
            if (complaintData) {
                // Format the complaint details
                const isJobReport = complaintData.fullSubject.includes('Job Report:') || complaintData.fullSubject.includes('JOB REPORT');
                const isCandidateReport = complaintData.fullSubject.includes('Candidate Report:') || complaintData.fullSubject.includes('CANDIDATE REPORT');
                
                let reportInfo = '';
                if (isJobReport) {
                    reportInfo = '<div class="alert alert-info" style="background: rgba(245, 158, 11, 0.1); border: 1px solid var(--warning); color: var(--warning); padding: 10px; border-radius: 6px; margin-bottom: 15px;"><i class="fas fa-flag"></i> This is a Job Post Report</div>';
                } else if (isCandidateReport) {
                    reportInfo = '<div class="alert alert-info" style="background: rgba(88, 166, 255, 0.1); border: 1px solid var(--accent); color: var(--accent); padding: 10px; border-radius: 6px; margin-bottom: 15px;"><i class="fas fa-user"></i> This is a Candidate Post Report</div>';
                }
                
                const detailsHtml = `
                    ${reportInfo}
                    <div class="complaint-detail-section">
                        <h4 style="color: var(--accent); margin-bottom: 10px;">Subject</h4>
                        <p style="background: var(--bg-secondary); padding: 12px; border-radius: 6px; margin-bottom: 20px;">${complaintData.subject}</p>
                    </div>
                    
                    <div class="complaint-detail-section">
                        <h4 style="color: var(--accent); margin-bottom: 10px;">Description</h4>
                        <div style="background: var(--bg-secondary); padding: 12px; border-radius: 6px; margin-bottom: 20px; white-space: pre-wrap; line-height: 1.6;">${complaintData.description}</div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                        <div class="complaint-detail-section">
                            <h4 style="color: var(--accent); margin-bottom: 10px;">Reporter</h4>
                            <p style="background: var(--bg-secondary); padding: 12px; border-radius: 6px; margin: 0;">${complaintData.user}</p>
                            <small style="color: var(--text-secondary); margin-top: 5px; display: block;">${complaintData.userType}</small>
                        </div>
                        
                        <div class="complaint-detail-section">
                            <h4 style="color: var(--accent); margin-bottom: 10px;">Status</h4>
                            <span class="status-badge status-${complaintData.status.toLowerCase().replace(' ', '-')}" style="padding: 8px 16px; font-size: 14px;">${complaintData.status}</span>
                        </div>
                    </div>
                    
                    <div class="complaint-detail-section">
                        <h4 style="color: var(--accent); margin-bottom: 10px;">Created</h4>
                        <p style="background: var(--bg-secondary); padding: 12px; border-radius: 6px; margin: 0;">${complaintData.created}</p>
                    </div>
                `;
                
                document.getElementById('complaintDetails').innerHTML = detailsHtml;
                document.getElementById('viewModal').style.display = 'block';
            } else {
                alert('Complaint details not found');
            }
        }

        function closeViewModal() {
            document.getElementById('viewModal').style.display = 'none';
        }

        function resolveComplaint(complaintId) {
            document.getElementById('complaintId').value = complaintId;
            document.getElementById('resolutionModal').style.display = 'block';
        }

        function markInProgress(complaintId) {
            if (confirm('Are you sure you want to mark this complaint as in progress?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="in_progress">
                    <input type="hidden" name="complaint_id" value="${complaintId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function reopenComplaint(complaintId) {
            if (confirm('Are you sure you want to reopen this complaint?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="reopen">
                    <input type="hidden" name="complaint_id" value="${complaintId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function closeComplaint(complaintId) {
            if (confirm('Are you sure you want to close this complaint?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="close">
                    <input type="hidden" name="complaint_id" value="${complaintId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function closeModal() {
            document.getElementById('resolutionModal').style.display = 'none';
            document.getElementById('resolutionForm').reset();
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('resolutionModal');
            if (event.target === modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>

<?php
/**
 * Bulk Exam Assignment Interface
 * 
 * This file provides an admin interface for companies to:
 * 1. View exam assignment statistics
 * 2. Manually assign exams to existing applicants
 * 3. Bulk assign all active exams to existing applicants
 */

require_once 'session_manager.php';
require_once 'Database.php';
require_once 'retroactive_exam_assignment.php';

// Check if company is logged in
if (!isCompanyLoggedIn()) {
    header('Location: Login&Signup.php');
    exit;
}

$sessionCompanyId = getCurrentCompanyId();
$companyName = $_SESSION['company_name'] ?? 'Company';

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    $assignment = new RetroactiveExamAssignment($pdo);
    
    switch ($_POST['action']) {
        case 'get_stats':
            $result = $assignment->getExamAssignmentStats($sessionCompanyId);
            echo json_encode($result);
            break;
            
        case 'assign_exam':
            $examId = intval($_POST['exam_id'] ?? 0);
            $dueDateDays = intval($_POST['due_date_days'] ?? 7);
            
            if (!$examId) {
                echo json_encode(['success' => false, 'message' => 'Exam ID is required']);
                break;
            }
            
            $result = $assignment->assignExamToExistingApplicants($examId, $sessionCompanyId, $dueDateDays);
            echo json_encode($result);
            break;
            
        case 'bulk_assign':
            $dueDateDays = intval($_POST['due_date_days'] ?? 7);
            $result = $assignment->bulkAssignAllExams($sessionCompanyId, $dueDateDays);
            echo json_encode($result);
            break;
            
        case 'check_missing':
            require_once 'exam_question_assignment_handler.php';
            $result = checkAllExamsForMissingAssignments($sessionCompanyId);
            echo json_encode(['success' => $result, 'message' => $result ? 'Missing assignments checked and created' : 'No missing assignments found or error occurred']);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
    exit;
}

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

// Load exams for this company
$exams = [];
try {
    if (isset($pdo) && $pdo instanceof PDO) {
        $stmt = $pdo->prepare("
            SELECT ExamID, ExamTitle, ExamType, IsActive, CreatedAt, 
                   COUNT(DISTINCT ea.AssignmentID) as assigned_count,
                   COUNT(DISTINCT CASE WHEN ea.Status = 'completed' THEN ea.AssignmentID END) as completed_count
            FROM exams e
            LEFT JOIN exam_assignments ea ON e.ExamID = ea.ExamID
            WHERE e.CompanyID = ?
            GROUP BY e.ExamID, e.ExamTitle, e.ExamType, e.IsActive, e.CreatedAt
            ORDER BY e.CreatedAt DESC
        ");
        $stmt->execute([$sessionCompanyId]);
        $exams = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    error_log("Error loading exams: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulk Exam Assignment - CandiHire</title>
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
            --accent-hover: #0860ca;
            --accent-secondary: #f59e0b;
            --border: #d1d9e0;
            --success: #1a7f37;
            --danger: #d1242f;
            --warning: #bf8700;
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
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border);
        }

        .header h1 {
            font-size: 28px;
            font-weight: bold;
            color: var(--accent);
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background-color: var(--bg-tertiary);
            color: var(--text-primary);
            text-decoration: none;
            border-radius: 8px;
            border: 1px solid var(--border);
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            background-color: var(--bg-secondary);
            transform: translateY(-2px);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background-color: var(--bg-secondary);
            border-radius: 12px;
            padding: 20px;
            border: 1px solid var(--border);
            text-align: center;
        }

        .stat-icon {
            font-size: 32px;
            margin-bottom: 10px;
        }

        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: var(--accent);
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 14px;
            color: var(--text-secondary);
        }

        .main-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        .section {
            background-color: var(--bg-secondary);
            border-radius: 12px;
            padding: 25px;
            border: 1px solid var(--border);
        }

        .section-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 20px;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .exam-list {
            max-height: 400px;
            overflow-y: auto;
        }

        .exam-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            margin-bottom: 10px;
            background-color: var(--bg-primary);
            border-radius: 8px;
            border: 1px solid var(--border);
        }

        .exam-info h4 {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 5px;
            color: var(--text-primary);
        }

        .exam-meta {
            font-size: 12px;
            color: var(--text-secondary);
        }

        .exam-actions {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            font-size: 12px;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-primary {
            background-color: var(--accent);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--accent-hover);
            transform: translateY(-1px);
        }

        .btn-success {
            background-color: var(--success);
            color: white;
        }

        .btn-success:hover {
            background-color: #2da04e;
            transform: translateY(-1px);
        }

        .btn-warning {
            background-color: var(--warning);
            color: white;
        }

        .btn-warning:hover {
            background-color: #d97706;
            transform: translateY(-1px);
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none !important;
        }

        .bulk-actions {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid var(--border);
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: var(--text-primary);
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border);
            border-radius: 6px;
            background-color: var(--bg-primary);
            color: var(--text-primary);
            font-size: 14px;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(88, 166, 255, 0.1);
        }

        .loading {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 8px;
            color: white;
            font-weight: 500;
            z-index: 1000;
            transform: translateX(100%);
            transition: transform 0.3s ease;
        }

        .toast.show {
            transform: translateX(0);
        }

        .toast.success {
            background-color: var(--success);
        }

        .toast.error {
            background-color: var(--danger);
        }

        .toast.warning {
            background-color: var(--warning);
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 500;
        }

        .status-active {
            background-color: rgba(63, 185, 80, 0.2);
            color: var(--success);
        }

        .status-inactive {
            background-color: rgba(139, 148, 158, 0.2);
            color: var(--text-secondary);
        }

        @media (max-width: 768px) {
            .main-content {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-tasks"></i> Bulk Exam Assignment</h1>
            <a href="CompanyDashboard.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                Back to Dashboard
            </a>
        </div>

        <!-- Statistics -->
        <div class="stats-grid" id="statsGrid">
            <div class="stat-card">
                <div class="stat-icon" style="color: var(--accent);">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div class="stat-value" id="totalExams">-</div>
                <div class="stat-label">Total Exams</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="color: var(--success);">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-value" id="activeExams">-</div>
                <div class="stat-label">Active Exams</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="color: var(--warning);">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-value" id="totalAssignments">-</div>
                <div class="stat-label">Total Assignments</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="color: var(--accent);">
                    <i class="fas fa-trophy"></i>
                </div>
                <div class="stat-value" id="completedAssignments">-</div>
                <div class="stat-label">Completed</div>
            </div>
        </div>

        <div class="main-content">
            <!-- Individual Exam Assignment -->
            <div class="section">
                <h2 class="section-title">
                    <i class="fas fa-user-plus"></i>
                    Assign Individual Exam
                </h2>
                
                <div class="exam-list">
                    <?php if (empty($exams)): ?>
                        <div style="text-align: center; padding: 40px; color: var(--text-secondary);">
                            <i class="fas fa-exclamation-circle" style="font-size: 48px; margin-bottom: 15px; opacity: 0.5;"></i>
                            <p>No exams found. Create exams first to assign them to applicants.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($exams as $exam): ?>
                            <div class="exam-item">
                                <div class="exam-info">
                                    <h4><?php echo htmlspecialchars($exam['ExamTitle']); ?></h4>
                                    <div class="exam-meta">
                                        <span class="status-badge <?php echo $exam['IsActive'] ? 'status-active' : 'status-inactive'; ?>">
                                            <?php echo $exam['IsActive'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                        <span style="margin-left: 10px;">
                                            <?php echo $exam['assigned_count']; ?> assigned, <?php echo $exam['completed_count']; ?> completed
                                        </span>
                                    </div>
                                </div>
                                <div class="exam-actions">
                                    <button class="btn btn-primary assign-exam-btn" 
                                            data-exam-id="<?php echo $exam['ExamID']; ?>"
                                            data-exam-title="<?php echo htmlspecialchars($exam['ExamTitle']); ?>"
                                            <?php echo !$exam['IsActive'] ? 'disabled' : ''; ?>>
                                        <i class="fas fa-plus"></i>
                                        Assign
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Bulk Assignment -->
            <div class="section">
                <h2 class="section-title">
                    <i class="fas fa-layer-group"></i>
                    Bulk Assignment
                </h2>
                
                <div class="form-group">
                    <label for="bulkDueDate">Due Date (days from now)</label>
                    <input type="number" id="bulkDueDate" value="7" min="1" max="30">
                </div>
                
                <div class="bulk-actions">
                    <button class="btn btn-success" id="bulkAssignBtn" style="width: 100%; margin-bottom: 10px;">
                        <i class="fas fa-magic"></i>
                        Assign All Active Exams to Existing Applicants
                    </button>
                    <button class="btn btn-warning" id="checkMissingBtn" style="width: 100%;">
                        <i class="fas fa-search"></i>
                        Check for Missing Assignments
                    </button>
                </div>
                
                <div style="margin-top: 20px; padding: 15px; background-color: var(--bg-primary); border-radius: 8px; border: 1px solid var(--border);">
                    <h4 style="margin-bottom: 10px; color: var(--text-primary);">
                        <i class="fas fa-info-circle"></i> How it works
                    </h4>
                    <ul style="font-size: 14px; color: var(--text-secondary); line-height: 1.6;">
                        <li><strong>Assign All:</strong> Assigns all active exams to existing job applicants</li>
                        <li><strong>Check Missing:</strong> Finds exams with questions that are missing assignments</li>
                        <li>Skips applicants who already have the exam assigned</li>
                        <li>Sets due date based on your selection</li>
                        <li>Only affects applicants who applied before the exam was created</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div class="toast" id="toast"></div>

    <script>
        // Load statistics on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadStats();
        });

        // Load statistics
        function loadStats() {
            fetch('bulk_exam_assignment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=get_stats'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateStatsDisplay(data.stats);
                } else {
                    showToast('Failed to load statistics', 'error');
                }
            })
            .catch(error => {
                console.error('Error loading stats:', error);
                showToast('Error loading statistics', 'error');
            });
        }

        // Update statistics display
        function updateStatsDisplay(stats) {
            const totalExams = stats.length;
            const activeExams = stats.filter(stat => stat.IsActive == 1).length;
            const totalAssignments = stats.reduce((sum, stat) => sum + parseInt(stat.assigned_exams || 0), 0);
            const completedAssignments = stats.reduce((sum, stat) => sum + parseInt(stat.completed_exams || 0), 0);

            document.getElementById('totalExams').textContent = totalExams;
            document.getElementById('activeExams').textContent = activeExams;
            document.getElementById('totalAssignments').textContent = totalAssignments;
            document.getElementById('completedAssignments').textContent = completedAssignments;
        }

        // Individual exam assignment
        document.addEventListener('click', function(e) {
            if (e.target.closest('.assign-exam-btn')) {
                const btn = e.target.closest('.assign-exam-btn');
                const examId = btn.dataset.examId;
                const examTitle = btn.dataset.examTitle;
                
                if (btn.disabled) {
                    showToast('Cannot assign inactive exam', 'warning');
                    return;
                }
                
                assignExam(examId, examTitle, btn);
            }
        });

        // Assign individual exam
        function assignExam(examId, examTitle, btn) {
            const originalContent = btn.innerHTML;
            btn.innerHTML = '<span class="loading"></span> Assigning...';
            btn.disabled = true;

            fetch('bulk_exam_assignment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=assign_exam&exam_id=${examId}&due_date_days=7`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(`Exam "${examTitle}" assigned to ${data.assigned_count} applicants`, 'success');
                    loadStats(); // Refresh statistics
                } else {
                    showToast(data.message || 'Failed to assign exam', 'error');
                }
            })
            .catch(error => {
                console.error('Error assigning exam:', error);
                showToast('Error assigning exam', 'error');
            })
            .finally(() => {
                btn.innerHTML = originalContent;
                btn.disabled = false;
            });
        }

        // Check missing assignments
        document.getElementById('checkMissingBtn').addEventListener('click', function() {
            if (!confirm('This will check for exams that have questions but are missing assignments for existing applicants. Continue?')) {
                return;
            }

            const btn = this;
            const originalContent = btn.innerHTML;
            btn.innerHTML = '<span class="loading"></span> Checking...';
            btn.disabled = true;

            fetch('bulk_exam_assignment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=check_missing'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    loadStats(); // Refresh statistics
                } else {
                    showToast(data.message || 'Failed to check missing assignments', 'error');
                }
            })
            .catch(error => {
                console.error('Error checking missing assignments:', error);
                showToast('Error checking missing assignments', 'error');
            })
            .finally(() => {
                btn.innerHTML = originalContent;
                btn.disabled = false;
            });
        });

        // Bulk assignment
        document.getElementById('bulkAssignBtn').addEventListener('click', function() {
            const dueDateDays = document.getElementById('bulkDueDate').value;
            
            if (!confirm(`This will assign all active exams to existing applicants with a due date of ${dueDateDays} days from now. Continue?`)) {
                return;
            }

            const btn = this;
            const originalContent = btn.innerHTML;
            btn.innerHTML = '<span class="loading"></span> Processing...';
            btn.disabled = true;

            fetch('bulk_exam_assignment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=bulk_assign&due_date_days=${dueDateDays}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(`Bulk assignment completed! Total assignments: ${data.total_assigned}`, 'success');
                    loadStats(); // Refresh statistics
                } else {
                    showToast(data.message || 'Failed to perform bulk assignment', 'error');
                }
            })
            .catch(error => {
                console.error('Error in bulk assignment:', error);
                showToast('Error in bulk assignment', 'error');
            })
            .finally(() => {
                btn.innerHTML = originalContent;
                btn.disabled = false;
            });
        });

        // Show toast notification
        function showToast(message, type = 'info') {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.className = `toast ${type} show`;
            
            setTimeout(() => {
                toast.classList.remove('show');
            }, 5000);
        }
    </script>
</body>
</html>

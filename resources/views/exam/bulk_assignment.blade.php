<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Bulk Exam Assignment - CandiHire</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/bulk_exam_assignment.css') }}">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-tasks"></i> Bulk Exam Assignment</h1>
            <a href="{{ url('company/dashboard') }}" class="back-btn">
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
                <div class="stat-value" id="totalExams">{{ $stats['totalExams'] ?? 0 }}</div>
                <div class="stat-label">Total Exams</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="color: var(--success);">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-value" id="activeExams">{{ $stats['activeExams'] ?? 0 }}</div>
                <div class="stat-label">Active Exams</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="color: var(--warning);">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-value" id="totalAssignments">{{ $stats['totalAssignments'] ?? 0 }}</div>
                <div class="stat-label">Total Assignments</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="color: var(--accent);">
                    <i class="fas fa-trophy"></i>
                </div>
                <div class="stat-value" id="completedAssignments">{{ $stats['completedAssignments'] ?? 0 }}</div>
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
                    @forelse ($exams as $exam)
                        <div class="exam-item">
                            <div class="exam-info">
                                <h4>{{ $exam['ExamTitle'] }}</h4>
                                <div class="exam-meta">
                                    <span class="status-badge {{ $exam['IsActive'] ? 'status-active' : 'status-inactive' }}">
                                        {{ $exam['IsActive'] ? 'Active' : 'Inactive' }}
                                    </span>
                                    <span style="margin-left: 10px;">
                                        {{ $exam['assigned_count'] }} assigned, {{ $exam['completed_count'] }} completed
                                    </span>
                                </div>
                            </div>
                            <div class="exam-actions">
                                <button class="btn btn-primary assign-exam-btn" 
                                        data-exam-id="{{ $exam['ExamID'] }}"
                                        data-exam-title="{{ $exam['ExamTitle'] }}"
                                        {{ !$exam['IsActive'] ? 'disabled' : '' }}>
                                    <i class="fas fa-plus"></i>
                                    Assign
                                </button>
                            </div>
                        </div>
                    @empty
                        <div style="text-align: center; padding: 40px; color: var(--text-secondary);">
                            <i class="fas fa-exclamation-circle" style="font-size: 48px; margin-bottom: 15px; opacity: 0.5;"></i>
                            <p>No exams found. Create exams first to assign them to applicants.</p>
                        </div>
                    @endforelse
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
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Note: loadStats is handled server-side via blade variables initially, but can be refreshed

        function updateStatsDisplay(stats) {
            document.getElementById('totalExams').textContent = stats.totalExams;
            document.getElementById('activeExams').textContent = stats.activeExams;
            document.getElementById('totalAssignments').textContent = stats.totalAssignments;
            document.getElementById('completedAssignments').textContent = stats.completedAssignments;
        }

        function refreshStats() {
             fetch('{{ url("exams/stats") }}')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateStatsDisplay(data.stats);
                    }
                });
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

            fetch('{{ url("exams/assign") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    exam_id: examId,
                    due_date_days: 7
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(`Exam "${examTitle}" assigned to ${data.assigned_count} applicants`, 'success');
                    refreshStats();
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

            fetch('{{ url("exams/check-missing") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    refreshStats();
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

            fetch('{{ url("exams/bulk-assign") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    due_date_days: dueDateDays
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(`Bulk assignment completed! Total assignments: ${data.total_assigned}`, 'success');
                    refreshStats();
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

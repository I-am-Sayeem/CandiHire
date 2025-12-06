<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complaints Management - CandiHire Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/AdminSettings.css') }}">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="admin-header">
        <div class="header-content">
            <div class="admin-title">
                <i class="fas fa-shield-alt"></i> CandiHire Admin Panel
            </div>
            <div class="admin-user">
                 <div class="user-info">
                    <div class="user-name">{{ $adminUsername ?? 'Admin' }}</div>
                    <div class="user-role">System Administrator</div>
                </div>
                <a href="{{ url('admin/dashboard') }}" class="back-btn">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="page-header">
            <h1 class="page-title">Complaints Management</h1>
            <p class="page-subtitle">Handle user complaints and resolve issues</p>
        </div>

        @if (session('success_message') || session('error_message'))
            <div class="message {{ session('success_message') ? 'success' : 'error' }}">
                <i class="fas fa-{{ session('success_message') ? 'check-circle' : 'exclamation-triangle' }}"></i>
                {{ session('success_message') ?? session('error_message') }}
            </div>
        @endif

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value">{{ $stats['total'] ?? 0 }}</div>
                <div class="stat-label">Total Complaints</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ $stats['pending'] ?? 0 }}</div>
                <div class="stat-label">Pending</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ $stats['in_progress'] ?? 0 }}</div>
                <div class="stat-label">In Progress</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ $stats['resolved'] ?? 0 }}</div>
                <div class="stat-label">Resolved</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ $stats['closed'] ?? 0 }}</div>
                <div class="stat-label">Closed</div>
            </div>
        </div>

        <div class="filters">
            <div class="filter-row">
                <div class="filter-group">
                    <label class="filter-label">Status</label>
                    <select class="filter-select" id="statusFilter" onchange="applyFilters()">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="in-progress">In Progress</option>
                        <option value="resolved">Resolved</option>
                        <option value="closed">Closed</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label">User Type</label>
                    <select class="filter-select" id="userTypeFilter" onchange="applyFilters()">
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
            <div style="overflow-x: auto;">
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
                    <tbody id="complaintsTableBody">
                        @forelse ($complaints as $complaint)
                            <tr class="complaint-row" data-status="{{ strtolower($complaint['Status']) }}" data-usertype="{{ strtolower($complaint['UserType']) }}">
                                <td>
                                    <div class="complaint-title">
                                        {{ $complaint['Subject'] }}
                                        @if (strpos($complaint['Subject'], 'Job Report:') === 0)
                                            <span class="report-badge" style="background: var(--warning); color: white; padding: 2px 8px; border-radius: 12px; font-size: 10px; margin-left: 8px;">JOB REPORT</span>
                                        @elseif (strpos($complaint['Subject'], 'Candidate Report:') === 0)
                                            <span class="report-badge" style="background: var(--accent); color: white; padding: 2px 8px; border-radius: 12px; font-size: 10px; margin-left: 8px;">CANDIDATE REPORT</span>
                                        @endif
                                    </div>
                                    <div class="complaint-description">
                                        {{ Str::limit($complaint['Description'], 150) }}
                                    </div>
                                </td>
                                <td>
                                    <div class="complaint-user">
                                        {{ $complaint['UserName'] }}
                                        <div style="font-size: 0.8rem; color: var(--text-secondary);">
                                            {{ $complaint['UserTypeDisplay'] }}
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="status-badge status-{{ strtolower($complaint['Status']) }}">
                                        {{ ucfirst($complaint['Status']) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="complaint-date">
                                        {{ \Carbon\Carbon::parse($complaint['ComplaintDate'])->format('M j, Y') }}
                                    </div>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-primary" onclick="viewComplaint({{ htmlspecialchars(json_encode($complaint)) }})">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="text-align: center; color: var(--text-secondary);">No complaints found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Complaint Detail Modal -->
    <div id="complaintModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Complaint Details</h3>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            
            <div id="complaintDetails" style="max-height: 60vh; overflow-y: auto;">
                <!-- Content will be populated by JS -->
            </div>

            <form id="actionForm" method="POST" action="{{ route('admin.complaints.update') }}" style="margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--border);">
                @csrf
                <input type="hidden" name="complaint_id" id="modalComplaintId">
                
                <div id="resolutionField" style="display: none;">
                    <div class="form-group">
                        <label class="form-label">Resolution Details</label>
                        <textarea name="resolution" class="form-textarea" placeholder="Enter details about how this complaint was resolved..."></textarea>
                    </div>
                </div>

                <div class="modal-actions" id="modalActions">
                    <!-- Buttons will be populated by JS based on status -->
                </div>
            </form>
        </div>
    </div>

    <script>
        function applyFilters() {
            const status = document.getElementById('statusFilter').value.toLowerCase();
            const userType = document.getElementById('userTypeFilter').value.toLowerCase();
            const rows = document.querySelectorAll('.complaint-row');

            rows.forEach(row => {
                const rowStatus = row.dataset.status;
                const rowUserType = row.dataset.usertype;
                const statusMatch = !status || rowStatus === status;
                const userTypeMatch = !userType || rowUserType === userType;

                if (statusMatch && userTypeMatch) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        function viewComplaint(complaint) {
            const modal = document.getElementById('complaintModal');
            const details = document.getElementById('complaintDetails');
            const actions = document.getElementById('modalActions');
            const resolutionField = document.getElementById('resolutionField');
            const form = document.getElementById('actionForm');
            
            document.getElementById('modalComplaintId').value = complaint.ComplaintID;
            
            // Populate details
            details.innerHTML = `
                <div class="complaint-detail-section">
                    <h4>Subject</h4>
                    <p style="color: var(--text-secondary)">${complaint.Subject}</p>
                </div>
                <div class="complaint-detail-section">
                    <h4>Description</h4>
                    <p style="white-space: pre-wrap; color: var(--text-secondary)">${complaint.Description}</p>
                </div>
                <div class="complaint-detail-section" style="display: flex; gap: 20px;">
                    <div>
                        <h4>Submitted By</h4>
                        <p style="color: var(--text-secondary)">${complaint.UserName} (${complaint.UserTypeDisplay})</p>
                    </div>
                    <div>
                        <h4>Date</h4>
                        <p style="color: var(--text-secondary)">${formatDate(complaint.ComplaintDate)}</p>
                    </div>
                    <div>
                        <h4>Status</h4>
                        <span class="status-badge status-${complaint.Status.toLowerCase()}">${complaint.Status}</span>
                    </div>
                </div>
                ${complaint.ResolutionDetails ? `
                    <div class="complaint-detail-section" style="background: rgba(63, 185, 80, 0.1); padding: 15px; border-radius: 6px; border: 1px solid var(--success);">
                        <h4 style="color: var(--success); margin-top: 0;">Resolution</h4>
                        <p style="color: var(--text-secondary)">${complaint.ResolutionDetails}</p>
                        <p style="font-size: 0.8rem; color: var(--text-secondary); margin-top: 5px;">Resolved on ${formatDate(complaint.ResolutionDate)}</p>
                    </div>
                ` : ''}
            `;

            // Setup actions based on status
            actions.innerHTML = '';
            resolutionField.style.display = 'none';
            
            if (complaint.Status === 'pending') {
                actions.innerHTML = `
                    <button type="submit" name="action" value="in_progress" class="btn btn-primary">Mark In Progress</button>
                    ${createResolveButton()}
                    <button type="submit" name="action" value="close" class="btn btn-danger">Close Without Resolution</button>
                `;
            } else if (complaint.Status === 'in-progress') {
                actions.innerHTML = `
                    ${createResolveButton()}
                    <button type="submit" name="action" value="close" class="btn btn-danger">Close Without Resolution</button>
                `;
            } else if (complaint.Status === 'resolved' || complaint.Status === 'closed') {
                actions.innerHTML = `
                    <button type="submit" name="action" value="reopen" class="btn btn-warning">Reopen Complaint</button>
                `;
            }

            modal.style.display = 'block';
        }

        function createResolveButton() {
            return `<button type="button" class="btn btn-success" onclick="showResolutionInput()">Resolve Complaint</button>`;
        }

        function showResolutionInput() {
            document.getElementById('resolutionField').style.display = 'block';
            document.getElementById('modalActions').innerHTML = `
                <button type="button" class="btn btn-secondary" onclick="hideResolutionInput()">Cancel</button>
                <button type="submit" name="action" value="resolve" class="btn btn-success">Submit Resolution</button>
            `;
        }

        function hideResolutionInput() {
            document.getElementById('resolutionField').style.display = 'none';
            // Re-render original buttons (simplified: ideally should pass complaint obj again or store state)
            // For now, just closing modal to reset state is easier/safer or re-trigger viewComplaint with stored data
            closeModal(); 
        }

        function closeModal() {
            document.getElementById('complaintModal').style.display = 'none';
        }

        function formatDate(dateString) {
            if (!dateString) return 'N/A';
            const options = { year: 'numeric', month: 'short', day: 'numeric' };
            return new Date(dateString).toLocaleDateString('en-US', options);
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('complaintModal');
            if (event.target === modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>

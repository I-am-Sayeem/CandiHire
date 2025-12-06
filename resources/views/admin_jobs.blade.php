<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Management - CandiHire Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/JobPost.css') }}">
</head>
<body>
    <div class="admin-header">
        <div class="header-content">
            <div class="admin-title">
                <i class="fas fa-shield-alt"></i> CandiHire Admin Panel
            </div>
            <a href="{{ url('admin/dashboard') }}" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <div class="container">
        <div class="page-header">
            <h1 class="page-title">Job Posts Management</h1>
            <p class="page-subtitle">Manage all job postings in the system</p>
        </div>

        @if (session('success_message'))
            <div class="message success">
                <i class="fas fa-check-circle"></i>
                {{ session('success_message') }}
            </div>
        @endif

        @if (session('error_message'))
            <div class="message error">
                <i class="fas fa-exclamation-triangle"></i>
                {{ session('error_message') }}
            </div>
        @endif

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value">{{ $stats['total'] ?? 0 }}</div>
                <div class="stat-label">Total Jobs</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ $stats['active'] ?? 0 }}</div>
                <div class="stat-label">Active Jobs</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ $stats['applications'] ?? 0 }}</div>
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
                    @foreach ($job_posts as $job)
                        <tr>
                            <td>
                                <div class="job-title">
                                    {{ $job['JobTitle'] }}
                                </div>
                                <div class="job-meta">{{ $job['JobType'] }}</div>
                            </td>
                            <td>
                                <div class="job-company">{{ $job['CompanyName'] }}</div>
                            </td>
                            <td>{{ $job['Department'] }}</td>
                            <td>{{ $job['Location'] }}</td>
                            <td>
                                @if (!empty($job['SalaryMin']) && !empty($job['SalaryMax']))
                                    <span class="salary">
                                        {{ $job['Currency'] . ' ' . number_format($job['SalaryMin']) . ' - ' . number_format($job['SalaryMax']) }}
                                    </span>
                                @else
                                    <span style="color: var(--text-secondary);">Not specified</span>
                                @endif
                            </td>
                            <td>{{ $job['application_count'] }}</td>
                            <td>
                                <span class="status-badge status-{{ strtolower($job['Status']) }}">
                                    {{ ucfirst($job['Status']) }}
                                </span>
                            </td>
                            <td>{{ date('M j, Y', strtotime($job['CreatedAt'])) }}</td>
                            <td>
                                <div class="action-buttons">
                                    <form action="{{ route('admin.jobs.delete') }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this job post? This action cannot be undone.');">
                                        @csrf
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="job_id" value="{{ $job['JobID'] }}">
                                        <button type="submit" class="btn btn-danger">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
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

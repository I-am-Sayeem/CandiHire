<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - CandiHire</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/AdminDashboard.css') }}">
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
                <form action="{{ route('admin.logout') }}" method="POST" id="logout-form" style="display: inline;">
                    @csrf
                    <button type="submit" class="logout-btn" onclick="return confirm('Are you sure you want to logout?')">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </form>
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
                <div class="stat-value">{{ number_format($stats['candidates'] ?? 0) }}</div>
                <div class="stat-change">+12% from last month</div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-title">Total Companies</span>
                    <i class="fas fa-building stat-icon"></i>
                </div>
                <div class="stat-value">{{ number_format($stats['companies'] ?? 0) }}</div>
                <div class="stat-change">+8% from last month</div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-title">Job Posts</span>
                    <i class="fas fa-briefcase stat-icon"></i>
                </div>
                <div class="stat-value">{{ number_format($stats['job_posts'] ?? 0) }}</div>
                <div class="stat-change">{{ number_format($stats['active_jobs'] ?? 0) }} active</div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-title">Applications</span>
                    <i class="fas fa-file-alt stat-icon"></i>
                </div>
                <div class="stat-value">{{ number_format($stats['applications'] ?? 0) }}</div>
                <div class="stat-change">+25% from last month</div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-title">Exams Created</span>
                    <i class="fas fa-clipboard-list stat-icon"></i>
                </div>
                <div class="stat-value">{{ number_format($stats['exams'] ?? 0) }}</div>
                <div class="stat-change">{{ number_format($stats['exam_assignments'] ?? 0) }} assigned</div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-title">Completed Exams</span>
                    <i class="fas fa-check-circle stat-icon"></i>
                </div>
                <div class="stat-value">{{ number_format($stats['completed_exams'] ?? 0) }}</div>
                <div class="stat-change">+18% completion rate</div>
            </div>
        </div>

        <div class="main-content">
            <div class="content-card">
                <div class="card-header">
                    <h3 class="card-title">Recent Activity</h3>
                    <a href="{{ url('admin/reports') }}#recent-activity" class="view-all-btn">View All</a>
                </div>
                <div class="activity-list">
                    @if (empty($recentActivity))
                        <div style="text-align: center; color: var(--text-secondary); padding: 20px;">
                            <i class="fas fa-inbox" style="font-size: 2rem; margin-bottom: 10px;"></i>
                            <p>No recent activity</p>
                        </div>
                    @else
                        @foreach ($recentActivity as $activity)
                            <div class="activity-item">
                                <div class="activity-icon {{ $activity['type'] }}">
                                    <i class="fas fa-{{ $activity['type'] === 'job_application' ? 'file-alt' : 'briefcase' }}"></i>
                                </div>
                                <div class="activity-content">
                                    <div class="activity-title">{{ $activity['user_name'] }}</div>
                                    <div class="activity-description">{{ $activity['description'] }}</div>
                                </div>
                                <div class="activity-time">
                                    {{ date('M j, Y', strtotime($activity['date'])) }}
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>

            <div class="nav-menu">
                <div class="nav-title">Admin Navigation</div>
                <a href="{{ url('admin/users') }}" class="nav-item">
                    <i class="fas fa-users"></i> Manage Users
                </a>
                <a href="{{ url('admin/jobs') }}" class="nav-item">
                    <i class="fas fa-briefcase"></i> Manage Job Posts
                </a>
                <a href="{{ url('admin/complaints') }}" class="nav-item">
                    <i class="fas fa-exclamation-triangle"></i> Handle Complaints
                </a>
                <a href="{{ url('admin/reports') }}" class="nav-item">
                    <i class="fas fa-chart-bar"></i> System Reports
                </a>
                <a href="{{ url('admin/settings') }}" class="nav-item">
                    <i class="fas fa-cog"></i> System Settings
                </a>
            </div>
        </div>

    </div>
</body>
</html>

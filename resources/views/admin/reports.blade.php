<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Reports - CandiHire Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/exam_results.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="admin-header">
        <div class="header-content">
            <div class="admin-title">
                <i class="fas fa-chart-bar"></i> System Reports
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
        <div class="reports-header">
            <h1 class="reports-title">Analytics Dashboard</h1>
            <div class="export-buttons">
                <!-- Placeholder links for export -->
                <a href="#" class="export-btn secondary">
                    <i class="fas fa-file-csv"></i> Export CSV
                </a>
                <a href="#" class="export-btn">
                    <i class="fas fa-file-pdf"></i> Export PDF
                </a>
            </div>
        </div>

        <div class="reports-grid">
            <!-- User Statistics -->
            <div class="report-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-users card-icon"></i> User Growth
                    </h3>
                    <div class="stats-grid">
                        <div class="stat-item">
                            <div class="stat-value">{{ $reportData['users']['total_candidates'] ?? 0 }}</div>
                            <div class="stat-label">Candidates</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">{{ $reportData['users']['total_companies'] ?? 0 }}</div>
                            <div class="stat-label">Companies</div>
                        </div>
                    </div>
                </div>
                <div>
                   <p style="color: var(--text-secondary);">New this month:</p>
                   <div style="display: flex; gap: 15px; margin-top: 10px;">
                       <div>
                           <strong style="color: var(--success);">+{{ $reportData['users']['new_candidates_this_month'] ?? 0 }}</strong> Candidates
                       </div>
                       <div>
                           <strong style="color: var(--accent-1);">+{{ $reportData['users']['new_companies_this_month'] ?? 0 }}</strong> Companies
                       </div>
                   </div>
                </div>
            </div>

            <!-- Job Statistics -->
            <div class="report-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-briefcase card-icon"></i> Jobs & Applications
                    </h3>
                </div>
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-value">{{ $reportData['jobs']['active_jobs'] ?? 0 }}</div>
                        <div class="stat-label">Active Jobs</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">{{ $reportData['applications']['total_applications'] ?? 0 }}</div>
                        <div class="stat-label">Total Applications</div>
                    </div>
                </div>
                <div>
                     <p style="color: var(--text-secondary); margin-bottom: 5px;">Top Applied Jobs:</p>
                     @if (!empty($reportData['applications']['top_applied_jobs']))
                        <table class="data-table" style="margin-top: 0; font-size: 0.85rem;">
                            <tbody>
                                @foreach (array_slice($reportData['applications']['top_applied_jobs'], 0, 3) as $job)
                                    <tr>
                                        <td>{{ Str::limit($job['JobTitle'], 20) }}</td>
                                        <td style="text-align: right;">{{ $job['application_count'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                     @else
                        <p class="no-data" style="padding: 10px;">No application data available.</p>
                     @endif
                </div>
            </div>

             <!-- Exam Statistics -->
             <div class="report-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-edit card-icon"></i> Exam Performance
                    </h3>
                </div>
                 <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-value">{{ $reportData['exams']['completed_exams'] ?? 0 }}</div>
                        <div class="stat-label">Completed</div>
                    </div>
                     <div class="stat-item">
                        <div class="stat-value">{{ number_format($reportData['exams']['average_scores']['average_score'] ?? 0, 1) }}%</div>
                        <div class="stat-label">Avg Score</div>
                    </div>
                </div>
                 @if (!empty($reportData['exams']['exam_performance']))
                    <div style="margin-top: 15px;">
                        <p style="color: var(--text-secondary); margin-bottom: 5px;">Top Exams by Attempts:</p>
                         <table class="data-table" style="margin-top: 0; font-size: 0.85rem;">
                            <tbody>
                                @foreach (array_slice($reportData['exams']['exam_performance'], 0, 3) as $exam)
                                    <tr>
                                        <td>{{ Str::limit($exam['ExamTitle'], 20) }}</td>
                                        <td style="text-align: right;">{{ $exam['total_attempts'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                 @endif
            </div>
            
             <!-- System Health -->
             <div class="report-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-server card-icon"></i> System Health
                    </h3>
                </div>
                 <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-value">{{ $reportData['system']['database_size'] ?? 0 }} MB</div>
                        <div class="stat-label">DB Size</div>
                    </div>
                     <div class="stat-item">
                        <div class="stat-value">{{ $reportData['system']['storage_usage']['total'] ?? 0 }} MB</div>
                        <div class="stat-label">Storage</div>
                    </div>
                </div>
                <div style="margin-top: 15px;">
                    <p style="color: var(--text-secondary); margin-bottom: 5px;">Storage Breakdown:</p>
                    <div style="display: flex; justify-content: space-between; font-size: 0.9rem; margin-bottom: 5px;">
                        <span>Uploads</span>
                        <span>{{ $reportData['system']['storage_usage']['uploads'] ?? 0 }} MB</span>
                    </div>
                    <div class="progress-bar">
                        @php
                            $total = $reportData['system']['storage_usage']['total'] > 0 ? $reportData['system']['storage_usage']['total'] : 1;
                            $uploadPercent = ($reportData['system']['storage_usage']['uploads'] / $total) * 100;
                        @endphp
                        <div class="progress-fill" style="width: {{ $uploadPercent }}%;"></div>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 0.9rem; margin-top: 10px; margin-bottom: 5px;">
                        <span>CVs</span>
                        <span>{{ $reportData['system']['storage_usage']['cvs'] ?? 0 }} MB</span>
                    </div>
                     <div class="progress-bar">
                        @php
                            $cvPercent = ($reportData['system']['storage_usage']['cvs'] / $total) * 100;
                        @endphp
                        <div class="progress-fill" style="width: {{ $cvPercent }}%; background: var(--accent-2);"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="report-card">
            <div class="card-header">
                <h3 class="card-title">Recent System Activity</h3>
            </div>
            <div style="overflow-x: auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Description</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($reportData['system']['recent_activity'] ?? [] as $activity)
                            <tr>
                                <td>
                                    <span class="status-badge {{ $activity['type'] == 'job_application' ? 'success' : 'warning' }}">
                                        {{ str_replace('_', ' ', ucfirst($activity['type'])) }}
                                    </span>
                                </td>
                                <td>{{ $activity['description'] }}</td>
                                <td>{{ \Carbon\Carbon::parse($activity['date'])->diffForHumans() }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="no-data">No recent activity found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>

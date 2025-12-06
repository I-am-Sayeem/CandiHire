<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MCQ Results - CandiHire</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/CompanyDashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/company_mcq_results.css') }}">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container">
        <!-- Left Navigation -->
        <div class="left-nav">
             <div class="logo">
                <span class="candi">Candi</span><span class="hire">Hire</span>
            </div>
            
            <!-- Welcome Section -->
            <div class="welcome-section" style="background: var(--bg-tertiary); padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid var(--border);">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div id="companyLogo" style="width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 16px; {{ $companyLogo ? 'background-image: url(' . $companyLogo . '); background-size: cover; background-position: center;' : 'background: linear-gradient(135deg, var(--accent-secondary), #e67e22);' }}">
                        {{ $companyLogo ? '' : strtoupper(substr($companyName, 0, 1)) }}
                    </div>
                    <div>
                        <div style="color: var(--text-primary); font-weight: 600; font-size: 14px;">Welcome back!</div>
                        <div id="companyNameDisplay" style="color: var(--text-secondary); font-size: 12px;">{{ $companyName }}</div>
                    </div>
                </div>
                 <button id="editCompanyProfileBtn" style="background: var(--accent-secondary); color: white; border: none; border-radius: 6px; padding: 8px 12px; font-size: 12px; cursor: pointer; margin-top: 10px; width: 100%; transition: background 0.2s;" onmouseover="this.style.background='#e67e22'" onmouseout="this.style.background='var(--accent-secondary)'">
                    <i class="fas fa-building" style="margin-right: 6px;"></i>Edit Profile
                </button>
            </div>
            
            <!-- Main Menu Section -->
            <div class="nav-section">
                <div class="nav-section-title">Main menu</div>
                <div class="nav-item" onclick="window.location.href='{{ url('job-posts') }}'">
                    <i class="fas fa-briefcase"></i>
                    <span>Job Posts</span>
                </div>
                <div class="nav-item" onclick="window.location.href='{{ url('cv-checker') }}'">
                    <i class="fas fa-file-alt"></i>
                    <span>CV Checker</span>
                </div>
                <div class="nav-item" onclick="window.location.href='{{ url('company/dashboard') }}'">
                    <i class="fas fa-users"></i>
                    <span>Candidate Feed</span>
                </div>
            </div>
            
            <!-- Recruitment Section -->
            <div class="nav-section">
                <div class="nav-section-title">Recruitment</div>
                <div class="nav-item" onclick="window.location.href='{{ url('create-exam') }}'">
                    <i class="fas fa-pencil-alt"></i>
                    <span>Create Exam</span>
                </div>
                <div class="nav-item" onclick="window.location.href='{{ url('interviews') }}'">
                    <i class="fas fa-user-tie"></i>
                    <span>Interviews</span>
                </div>
                <div class="nav-item" onclick="window.location.href='{{ url('company/applications') }}'">
                    <i class="fas fa-clipboard-list"></i>
                    <span>View Applications</span>
                </div>
                <div class="nav-item active" onclick="window.location.href='{{ url('company/mcq-results') }}'">
                    <i class="fas fa-chart-bar"></i>
                    <span>View MCQ Results</span>
                </div>
                <div class="nav-item" onclick="window.location.href='{{ url('ai-matching') }}'">
                    <i class="fas fa-robot"></i>
                    <span>AI Matching</span>
                </div>
            </div>

             <!-- Logout -->
            <div class="logout-container">
                <button id="themeToggleBtn" class="theme-toggle-btn" title="Switch to Light Mode">
                    <i class="fas fa-moon-stars" id="themeIcon"></i>
                    <span id="themeText">Light Mode</span>
                </button>
                <button id="logoutBtn" class="logout-btn" onclick="window.location.href='{{ route('logout') }}'"><i class="fas fa-sign-out-alt" style="margin-right:8px;"></i>Logout</button>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Page Header -->
            <div class="page-header">
                <h1 class="page-title">MCQ Exam Results</h1>
                 <div class="header-actions">
                    <button class="btn btn-secondary" onclick="window.location.href='{{ url('create-exam') }}'">
                        <i class="fas fa-plus"></i> Create New Exam
                    </button>
                    <button class="btn btn-secondary" onclick="window.print()">
                        <i class="fas fa-print"></i> Print Report
                    </button>
                </div>
            </div>

            <!-- Position Selector -->
            @if(!isset($selectedJobId))
            <div class="position-selector">
                <div class="selector-title">Select Job Position</div>
                <div class="position-grid">
                    @if(isset($jobPositions) && count($jobPositions) > 0)
                        @foreach($jobPositions as $job)
                            <div class="position-card" onclick="window.location.href='{{ url('company/mcq-results') }}?job_id={{ $job['JobID'] }}'">
                                <div class="position-title">{{ $job['JobTitle'] }}</div>
                                <div class="position-department">{{ $job['Department'] }}</div>
                            </div>
                        @endforeach
                    @else
                         <div class="empty-state">
                            <i class="fas fa-clipboard-list"></i>
                            <h3>No Active Job Postings</h3>
                            <p>You need to create job postings before you can view exam results.</p>
                            <button class="btn btn-primary" style="margin-top: 15px;" onclick="window.location.href='{{ url('job-posts') }}'">
                                Go to Job Posts
                            </button>
                         </div>
                    @endif
                </div>
            </div>
            @endif

             <!-- Results Table -->
            @if(isset($selectedJobId))
                <div class="header-actions" style="margin-bottom: 20px;">
                    <button class="btn btn-secondary" onclick="window.location.href='{{ url('company/mcq-results') }}'">
                        <i class="fas fa-arrow-left"></i> Back to Positions
                    </button>
                    @if(isset($selectedPosition))
                    <div style="font-size: 18px; font-weight: 600; color: var(--text-primary); margin-left: 10px;">
                        Results for: <span style="color: var(--accent);">{{ $selectedPosition['JobTitle'] }}</span>
                    </div>
                    @endif
                </div>

                <div class="results-section">
                    <div class="section-header">
                        <div class="section-title">Candidate Evaluation Reports</div>
                    </div>
                    <div class="section-body">
                         @if(isset($examResults) && count($examResults) > 0)
                            <div class="results-grid">
                            @foreach($examResults as $result)
                                <div class="result-card">
                                    <div class="result-header">
                                        <div class="candidate-info">
                                            <div class="candidate-name">{{ $result['CandidateName'] }}</div>
                                            <div class="candidate-details"><i class="fas fa-envelope" style="width: 20px;"></i> {{ $result['CandidateEmail'] }}</div>
                                            <div class="candidate-details"><i class="fas fa-phone" style="width: 20px;"></i> {{ $result['PhoneNumber'] }}</div>
                                        </div>
                                        <div class="exam-info">
                                            <div class="exam-title">{{ $result['ExamTitle'] }}</div>
                                            <div class="exam-date">
                                                Completed: {{ \Carbon\Carbon::parse($result['CompletedAt'])->format('M d, Y H:i') }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="result-stats">
                                        <div class="stat-item">
                                            <div class="stat-label">Score</div>
                                            <div class="stat-value" style="color: {{ $result['Score'] >= $result['PassingScore'] ? 'var(--success)' : 'var(--danger)' }}">
                                                {{ $result['Score'] }}%
                                            </div>
                                        </div>
                                        <div class="stat-item">
                                            <div class="stat-label">Correct</div>
                                            <div class="stat-value">{{ $result['CorrectAnswers'] }}/{{ $result['TotalQuestions'] }}</div>
                                        </div>
                                        <div class="stat-item">
                                            <div class="stat-label">Time</div>
                                            <div class="stat-value">{{ $result['TimeSpent'] }}m</div>
                                        </div>
                                        <div class="stat-item">
                                            <div class="stat-label">Result</div>
                                            <div class="stat-value">
                                                @if($result['Score'] >= $result['PassingScore'])
                                                    <span style="color: var(--success)">PASSED</span>
                                                @else
                                                    <span style="color: var(--danger)">FAILED</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="result-status">
                                         <div class="status-badge" style="background-color: rgba(63, 185, 80, 0.1); color: var(--success); border: 1px solid rgba(63, 185, 80, 0.2);">
                                            <span class="status-dot"></span> Completed
                                        </div>
                                        <div class="result-actions">
                                             <button class="btn btn-small btn-secondary" onclick="alert('Viewing detailed report for {{ $result['CandidateName'] }}')">
                                                <i class="fas fa-file-alt"></i> View Report
                                            </button>
                                            <button class="btn btn-small btn-primary" onclick="window.location.href='{{ url('company/applications') }}?candidate_id={{ $result['CandidateID'] }}'">
                                                <i class="fas fa-user"></i> Profile
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            </div>
                         @else
                             <div class="empty-state">
                                <i class="fas fa-clipboard-check"></i>
                                <h3>No Exam Results Yet</h3>
                                <p>Candidates haven't completed any exams for this position yet.</p>
                             </div>
                         @endif
                    </div>
                </div>
            @endif
        </div>
    </div>


    <!-- Company Profile Edit Popup -->
    <div id="companyProfileEditPopup" class="popup-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.7); z-index: 10000; backdrop-filter: blur(5px);">
        <div class="popup-content" style="background: var(--bg-secondary); border-radius: 16px; padding: 30px; width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto; border: 1px solid var(--border); box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3); margin: 5% auto;">
            <div class="popup-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; padding-bottom: 15px; border-bottom: 1px solid var(--border);">
                <div class="popup-title" style="font-size: 24px; font-weight: 600; color: var(--accent-secondary); display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-building"></i>
                    Edit Company Profile
                </div>
                <button class="popup-close" onclick="closeCompanyProfileEditPopup()" style="background: none; border: none; color: var(--text-secondary); font-size: 24px; cursor: pointer; padding: 5px; border-radius: 50%; transition: all 0.2s;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
             <p class="text-secondary" style="text-align: center; color: var(--text-secondary);">Profile editing is managed in the main dashboard.</p>
        </div>
    </div>


    <script>
        // Theme Toggle
        const themeToggleBtn = document.getElementById('themeToggleBtn');
        themeToggleBtn.addEventListener('click', () => {
             const html = document.documentElement;
             const currentTheme = html.getAttribute('data-theme');
             const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
             html.setAttribute('data-theme', newTheme);
             document.getElementById('themeText').innerText = newTheme === 'dark' ? 'Light Mode' : 'Dark Mode';
        });

        // Modal Functions
        function closeCompanyProfileEditPopup() {
            document.getElementById('companyProfileEditPopup').style.display = 'none';
        }

        document.getElementById('editCompanyProfileBtn').addEventListener('click', () => {
             document.getElementById('companyProfileEditPopup').style.display = 'flex';
        });

        console.log('Company MCQ Results Page Loaded');
    </script>
</body>
</html>

<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Applications - CandiHire</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/CompanyDashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/company_applications.css') }}">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container">
        <!-- Left Navigation (Company Dashboard Sidebar) -->
        <div class="left-nav">
            <div class="logo">
                <span class="candi">Candi</span><span class="hire">Hire</span>
            </div>
            
            <!-- Welcome Section -->
            <div class="welcome-section" style="background: var(--bg-tertiary); padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid var(--border);">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div id="companyLogo" style="width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 16px; {{ isset($companyLogo) && $companyLogo ? 'background-image: url(' . $companyLogo . '); background-size: cover; background-position: center;' : 'background: linear-gradient(135deg, var(--accent-2), #e67e22);' }}">
                        {{ isset($companyLogo) && $companyLogo ? '' : strtoupper(substr($companyName ?? 'C', 0, 1)) }}
                    </div>
                    <div>
                        <div style="color: var(--text-primary); font-weight: 600; font-size: 14px;">Welcome back!</div>
                        <div id="companyNameDisplay" style="color: var(--text-secondary); font-size: 12px;">{{ $companyName ?? 'Company' }}</div>
                    </div>
                </div>
            </div>
            
            <!-- Main Menu Section -->
            <div class="nav-section">
                <div class="nav-section-title">Main menu</div>
                <a href="{{ url('/company/jobs') }}" class="nav-item">
                    <i class="fas fa-briefcase"></i>
                    <span>Job Posts</span>
                </a>
                <a href="{{ url('/cv/checker') }}" class="nav-item">
                    <i class="fas fa-file-alt"></i>
                    <span>CV Checker</span>
                </a>
                <a href="{{ url('/company/dashboard') }}" class="nav-item">
                    <i class="fas fa-users"></i>
                    <span>Candidate Feed</span>
                </a>
            </div>
            
            <!-- Recruitment Section -->
            <div class="nav-section">
                <div class="nav-section-title">Recruitment</div>
                <a href="{{ url('/company/exams/create') }}" class="nav-item">
                    <i class="fas fa-pencil-alt"></i>
                    <span>Create Exam</span>
                </a>
                <a href="{{ url('/company/interviews') }}" class="nav-item">
                    <i class="fas fa-user-tie"></i>
                    <span>Interviews</span>
                </a>
                <a href="{{ url('/company/applications') }}" class="nav-item active">
                    <i class="fas fa-clipboard-list"></i>
                    <span>View Applications</span>
                </a>
                <a href="{{ url('/company/mcq-results') }}" class="nav-item">
                    <i class="fas fa-chart-bar"></i>
                    <span>View MCQ Results</span>
                </a>
                <a href="{{ url('/company/ai-matching') }}" class="nav-item">
                    <i class="fas fa-robot"></i>
                    <span>AI Matching</span>
                </a>
            </div>

            <!-- Logout -->
            <div class="logout-container">
                <button id="themeToggleBtn" class="theme-toggle-btn" title="Switch to Light Mode">
                    <i class="fas fa-moon-stars" id="themeIcon"></i>
                    <span id="themeText">Light Mode</span>
                </button>
                <a href="{{ url('/logout') }}" class="logout-btn" style="text-decoration: none; display: flex; justify-content: center; align-items: center;"><i class="fas fa-sign-out-alt" style="margin-right:8px;"></i>Logout</a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Page Header -->
            <div class="page-header">
                <h1 class="page-title">Job Applications</h1>
                <p class="page-subtitle">Review and manage applications for your job posts</p>
            </div>

            <!-- Job Position Selector -->
            <div class="job-selector-section">
                <div style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
                    <div style="flex: 1; min-width: 300px;">
                        <label class="job-selector-label">Select Job Position</label>
                        <select id="jobPositionSelect">
                            <option value="">Choose a job position to view applications...</option>
                             @if(isset($jobPosts))
                                @foreach($jobPosts as $job)
                                    <option value="{{ $job['id'] }}">{{ $job['title'] }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div style="display: flex; gap: 10px; align-items: end;">
                        <button id="loadApplicationsBtn" disabled>
                            <i class="fas fa-search" style="margin-right: 6px;"></i>Load Applications
                        </button>
                        <button id="clearSelectionBtn" disabled>
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Loading Indicator -->
            <div id="loadingIndicator" style="display: none; text-align: center; padding: 40px; color: var(--text-secondary);">
                <i class="fas fa-spinner fa-spin" style="font-size: 24px; margin-right: 10px;"></i>
                Loading applications...
            </div>

            <!-- Applications Table -->
            <div id="applicationsTableContainer" class="applications-table-container" style="display: none;">
                <table class="applications-table">
                    <thead>
                        <tr>
                            <th>Candidate</th>
                            <th>Application Date</th>
                            <th>Status Detail</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="applicationsTableBody">
                        <!-- Applications will be loaded here dynamically -->
                         <!-- Example Row for Verification -->
                         {{-- 
                        <tr>
                             <td>
                                <div class="candidate-name">John Doe</div>
                                <div class="job-title">Software Engineer</div>
                            </td>
                             <td class="application-date">2023-10-27</td>
                             <td>Pending</td>
                             <td>
                                 <div class="actions">
                                     <button class="action-btn" title="View Details"><i class="fas fa-eye"></i></button>
                                 </div>
                             </td>
                        </tr>
                        --}}
                    </tbody>
                </table>
            </div>

            <!-- No Applications Message -->
            <div id="noApplicationsMessage" style="display: none; text-align: center; padding: 40px; color: var(--text-secondary);">
                <i class="fas fa-users" style="font-size: 48px; margin-bottom: 16px; opacity: 0.5;"></i>
                <h3>No Applications Found</h3>
                <p>No candidates have applied for the selected job position yet.</p>
            </div>

            <!-- Select Job Message -->
            <div id="selectJobMessage" style="text-align: center; padding: 40px; color: var(--text-secondary);">
                <i class="fas fa-briefcase" style="font-size: 48px; margin-bottom: 16px; opacity: 0.5;"></i>
                <h3>Select a Job Position</h3>
                <p>Choose a job position from the dropdown above to view applications.</p>
            </div>
        </div>
    </div>

    <!-- Company Profile Edit Popup -->
    <div id="companyProfileEditPopup" class="popup-overlay" style="display: none;">
        <div class="popup-content">
            <div class="popup-header">
                <div class="popup-title">
                    <i class="fas fa-building"></i>
                    Edit Company Profile
                </div>
                <button class="popup-close" onclick="closeCompanyProfileEditPopup()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <p class="text-secondary" style="text-align: center;">Profile editing is managed in the main dashboard.</p>
        </div>
    </div>

    <!-- Candidate Details Popup -->
    <div id="candidateDetailsPopup" class="popup-overlay" style="display: none;">
        <div class="popup-content">
             <div class="popup-header">
                <div class="popup-title">
                    <i class="fas fa-user"></i>
                    <span id="candidateDetailsTitle">Candidate Details</span>
                </div>
                <button class="popup-close" onclick="closeCandidateDetailsPopup()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
             <div id="candidateDetailsContent">
                 <!-- Loaded dynamically -->
            </div>
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
        function closeCandidateDetailsPopup() {
            document.getElementById('candidateDetailsPopup').style.display = 'none';
        }

        document.getElementById('editCompanyProfileBtn').addEventListener('click', () => {
             document.getElementById('companyProfileEditPopup').style.display = 'flex';
        });

        // Job Selector Logic
        const jobSelect = document.getElementById('jobPositionSelect');
        const loadBtn = document.getElementById('loadApplicationsBtn');
        const clearBtn = document.getElementById('clearSelectionBtn');
        const applicationsTableContainer = document.getElementById('applicationsTableContainer');
        const selectJobMessage = document.getElementById('selectJobMessage');
        const noApplicationsMessage = document.getElementById('noApplicationsMessage');
        const loadingIndicator = document.getElementById('loadingIndicator');

        jobSelect.addEventListener('change', function() {
            if (this.value) {
                loadBtn.disabled = false;
                clearBtn.disabled = false;
            } else {
                loadBtn.disabled = true;
                clearBtn.disabled = true;
                resetView();
            }
        });

        clearBtn.addEventListener('click', () => {
            jobSelect.value = "";
            loadBtn.disabled = true;
            clearBtn.disabled = true;
            resetView();
        });

        function resetView() {
            applicationsTableContainer.style.display = 'none';
            noApplicationsMessage.style.display = 'none';
            selectJobMessage.style.display = 'block';
        }

        loadBtn.addEventListener('click', () => {
             const jobId = jobSelect.value;
             if(!jobId) return;

             // Simulate Loading
             selectJobMessage.style.display = 'none';
             applicationsTableContainer.style.display = 'none';
             noApplicationsMessage.style.display = 'none';
             loadingIndicator.style.display = 'block';

             // Mock Fetch - Replace with actual AJAX call
             setTimeout(() => {
                 loadingIndicator.style.display = 'none';
                 // Here you would fetch data from server
                 // For now, let's show the table if a job is selected
                 if (jobId) {
                      applicationsTableContainer.style.display = 'block';
                      // Populate table logic would go here
                 } else {
                      noApplicationsMessage.style.display = 'block';
                 }
             }, 1000);
        });

        console.log('Company Applications Page Loaded');
    </script>
</body>
</html>

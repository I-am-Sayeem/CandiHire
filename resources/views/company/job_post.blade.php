<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CandiHire - Job Posts</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/CompanyDashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/JobPost.css') }}">
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
                    <div id="companyLogo" style="width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 16px; {{ $companyLogo ? 'background-image: url(' . $companyLogo . '); background-size: cover; background-position: center;' : 'background: linear-gradient(135deg, var(--accent-2), #e67e22);' }}">
                        {{ $companyLogo ? '' : strtoupper(substr($companyName, 0, 1)) }}
                    </div>
                    <div>
                        <div style="color: var(--text-primary); font-weight: 600; font-size: 14px;">Welcome back!</div>
                        <div id="companyNameDisplay" style="color: var(--text-secondary); font-size: 12px;">{{ $companyName }}</div>
                    </div>
                </div>
                <button id="editCompanyProfileBtn" style="background: var(--accent-2); color: white; border: none; border-radius: 6px; padding: 8px 12px; font-size: 12px; cursor: pointer; margin-top: 10px; width: 100%; transition: background 0.2s;" onmouseover="this.style.background='#e67e22'" onmouseout="this.style.background='var(--accent-2)'">
                    <i class="fas fa-building" style="margin-right: 6px;"></i>Edit Profile
                </button>
            </div>
            
            <!-- Main Menu Section -->
            <div class="nav-section">
                <div class="nav-section-title">Main menu</div>
                <div class="nav-item active" onclick="window.location.href='{{ url('job-posts') }}'">
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
                <div class="nav-item" onclick="window.location.href='{{ url('company/mcq-results') }}'">
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
            <!-- Job Posts Header -->
            <div class="job-posts-header">
                <div class="search-filter-container">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="jobSearchInput" placeholder="Search job posts...">
                    </div>
                </div>
                <div class="header-actions">
                    <button class="post-new-job-btn" id="postNewJobBtn">
                        <i class="fas fa-plus"></i>
                        Post New Job
                    </button>
                    <div class="user-profile">
                        <i class="fas fa-user-circle"></i>
                        <span>{{ $companyName }}</span>
                    </div>
                </div>
            </div>

            <!-- Loading indicator -->
            <div id="loadingIndicator" style="text-align: center; padding: 40px; color: var(--text-secondary); display: none;">
                <i class="fas fa-spinner fa-spin" style="font-size: 24px; margin-right: 10px;"></i>
                Loading job posts...
            </div>

            <!-- Job Posts Table -->
            <div class="job-posts-table-container" id="jobPostsTableContainer">
                @if(isset($jobPosts) && count($jobPosts) > 0)
                <table class="job-posts-table" id="jobPostsTable">
                    <thead>
                        <tr>
                            <th>Job Title</th>
                            <th>Department</th>
                            <th>Posted Date</th>
                            <th>Applications</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="jobPostsTableBody">
                         @foreach($jobPosts as $job)
                            <tr>
                                <td>
                                    <div class="job-title">{{ $job['title'] }}</div>
                                    <div class="job-meta">{{ $job['type'] }} • {{ $job['location'] }}</div>
                                </td>
                                <td class="department">{{ $job['department'] }}</td>
                                <td class="posted-date">{{ $job['posted_date'] }}</td>
                                <td class="applications-count">
                                    <span class="application-count-badge {{ $job['applications'] == 0 ? 'empty' : '' }}">
                                        {{ $job['applications'] }} New
                                    </span>
                                </td>
                                <td>
                                    <div class="actions">
                                        <button class="action-btn" title="Edit"><i class="fas fa-edit"></i></button>
                                        <button class="action-btn" title="View"><i class="fas fa-eye"></i></button>
                                        <button class="action-btn" title="Delete" style="color: var(--danger);"><i class="fas fa-trash"></i></button>
                                    </div>
                                </td>
                            </tr>
                         @endforeach
                    </tbody>
                </table>
                @else
                 <!-- No jobs message -->
                <div id="noJobsMessage" style="text-align: center; padding: 40px; color: var(--text-secondary);">
                    <i class="fas fa-briefcase" style="font-size: 48px; margin-bottom: 16px; opacity: 0.5;"></i>
                    <h3>No job posts found</h3>
                    <p>You haven't posted any jobs yet. Click "Post New Job" to get started!</p>
                </div>
                @endif
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
            <!-- ... Form Content same as Dashboard ... -->
             <p class="text-secondary" style="text-align: center;">Profile editing is managed in the main dashboard.</p>
        </div>
    </div>

    <!-- Job Post Modal -->
    <div id="jobPostModal" class="popup-overlay" style="display: none;">
        <div class="popup-content" style="max-width: 800px;">
            <div class="popup-header">
                <div class="popup-title">
                    <i class="fas fa-briefcase"></i>
                    <span id="jobModalTitle">Add New Job Post</span>
                </div>
                <button class="popup-close" onclick="closeJobPostModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="jobPostForm">
                @csrf
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div class="form-group">
                        <label for="jobTitle">Job Title *</label>
                        <input type="text" id="jobTitle" name="title" required placeholder="e.g., Senior Software Developer">
                    </div>
                    <div class="form-group">
                        <label for="department">Department</label>
                        <input type="text" id="department" name="department" placeholder="e.g., Engineering">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="jobDescription">Job Description *</label>
                    <textarea id="jobDescription" name="description" required rows="4" placeholder="Describe the role..."></textarea>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                     <div class="form-group">
                        <label for="location">Location *</label>
                        <input type="text" id="location" name="location" required placeholder="e.g., Remote">
                    </div>
                     <div class="form-group">
                        <label for="jobType">Job Type *</label>
                        <select id="jobType" name="type" required>
                             <option value="Full-time">Full-time</option>
                             <option value="Part-time">Part-time</option>
                             <option value="Contract">Contract</option>
                             <option value="Internship">Internship</option>
                        </select>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeJobPostModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Job Post</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Modal Functions
        function closeCompanyProfileEditPopup() {
            document.getElementById('companyProfileEditPopup').style.display = 'none';
        }

        function closeJobPostModal() {
            document.getElementById('jobPostModal').style.display = 'none';
        }

        document.getElementById('editCompanyProfileBtn').addEventListener('click', () => {
             document.getElementById('companyProfileEditPopup').style.display = 'flex';
        });

        document.getElementById('postNewJobBtn').addEventListener('click', () => {
             document.getElementById('jobPostModal').style.display = 'flex';
        });

        // Theme Toggle
        const themeToggleBtn = document.getElementById('themeToggleBtn');
        themeToggleBtn.addEventListener('click', () => {
             const html = document.documentElement;
             const currentTheme = html.getAttribute('data-theme');
             const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
             html.setAttribute('data-theme', newTheme);
             document.getElementById('themeText').innerText = newTheme === 'dark' ? 'Light Mode' : 'Dark Mode';
        });

        console.log('Job Post Page Loaded');
    </script>
</body>
</html>

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
                <a href="{{ url('/company/jobs') }}" class="nav-item active">
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
                <a href="{{ url('/company/applications') }}" class="nav-item">
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
                    <span id="themeText">Light Mode</span>
                </button>
                <a href="{{ url('/logout') }}" class="logout-btn" style="text-decoration: none; display: flex; justify-content: center; align-items: center;"><i class="fas fa-sign-out-alt" style="margin-right:8px;"></i>Logout</a>
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
                @php
                    $pendingJobs = collect($jobPosts)->where('status', 'Pending');
                @endphp
                
                @if($pendingJobs->count() > 0)
                <div style="background: linear-gradient(135deg, #f39c12, #e67e22); color: white; padding: 15px 20px; border-radius: 12px; margin-bottom: 20px; display: flex; align-items: center; gap: 15px; box-shadow: 0 4px 15px rgba(243, 156, 18, 0.3);">
                    <i class="fas fa-exclamation-triangle" style="font-size: 24px;"></i>
                    <div style="flex: 1;">
                        <div style="font-weight: 600; font-size: 16px;">{{ $pendingJobs->count() }} Job(s) Pending Exam Creation</div>
                        <div style="font-size: 13px; opacity: 0.9;">These jobs are not visible to candidates until you create exams for them.</div>
                    </div>
                    <a href="{{ url('/company/exams/create') }}" style="background: white; color: #e67e22; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; display: flex; align-items: center; gap: 8px;">
                        <i class="fas fa-plus"></i> Create Exam
                    </a>
                </div>
                @endif
                
                @if(isset($jobPosts) && count($jobPosts) > 0)
                <table class="job-posts-table" id="jobPostsTable">
                    <thead>
                        <tr>
                            <th>Job Title</th>
                            <th>Department</th>
                            <th>Status</th>
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
                                <td>
                                    @if($job['status'] === 'Pending')
                                        <span style="background: linear-gradient(135deg, #f39c12, #e67e22); color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; gap: 5px;">
                                            <i class="fas fa-clock"></i> Pending Exam
                                        </span>
                                    @elseif($job['status'] === 'Active')
                                        <span style="background: linear-gradient(135deg, var(--success), #2ea043); color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; gap: 5px;">
                                            <i class="fas fa-check-circle"></i> Active
                                        </span>
                                    @elseif($job['status'] === 'Closed')
                                        <span style="background: linear-gradient(135deg, var(--danger), #d03f39); color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; gap: 5px;">
                                            <i class="fas fa-times-circle"></i> Closed
                                        </span>
                                    @else
                                        <span style="background: var(--bg-tertiary); color: var(--text-secondary); padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">
                                            {{ $job['status'] }}
                                        </span>
                                    @endif
                                </td>
                                <td class="posted-date">{{ $job['posted_date'] }}</td>
                                <td class="applications-count">
                                    <span class="application-count-badge {{ $job['applications'] == 0 ? 'empty' : '' }}">
                                        {{ $job['applications'] }} New
                                    </span>
                                </td>
                                <td>
                                    <div class="actions">
                                        @if($job['status'] === 'Pending')
                                            <a href="{{ url('/company/exams/create?job_id=' . $job['id'] . '&department=' . urlencode($job['department'])) }}" class="action-btn" title="Create Exam" style="color: #f39c12; text-decoration: none;">
                                                <i class="fas fa-clipboard-question"></i>
                                            </a>
                                        @endif
                                        <button class="action-btn" title="Edit" onclick="editJobPost({{ $job['id'] }})"><i class="fas fa-edit"></i></button>
                                        <button class="action-btn" title="View" onclick="viewJobPost({{ $job['id'] }})"><i class="fas fa-eye"></i></button>
                                        <button class="action-btn" title="Delete" style="color: var(--danger);" onclick="deleteJobPost({{ $job['id'] }})"><i class="fas fa-trash"></i></button>
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

    <!-- Job View Modal -->
    <div id="jobViewModal" class="popup-overlay" style="display: none;">
        <div class="popup-content" style="max-width: 700px;">
            <div class="popup-header">
                <div class="popup-title">
                    <i class="fas fa-briefcase"></i>
                    <span id="viewJobTitle">Job Details</span>
                </div>
                <button class="popup-close" onclick="closeJobViewModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="job-view-content">
                <div class="job-detail-section">
                    <div class="job-detail-row">
                        <strong>Department:</strong> <span id="viewDepartment">-</span>
                    </div>
                    <div class="job-detail-row">
                        <strong>Location:</strong> <span id="viewLocation">-</span>
                    </div>
                    <div class="job-detail-row">
                        <strong>Job Type:</strong> <span id="viewJobType">-</span>
                    </div>
                    <div class="job-detail-row">
                        <strong>Salary:</strong> <span id="viewSalary">-</span>
                    </div>
                    <div class="job-detail-row">
                        <strong>Experience Level:</strong> <span id="viewExperience">-</span>
                    </div>
                    <div class="job-detail-row">
                        <strong>Education Level:</strong> <span id="viewEducation">-</span>
                    </div>
                    <div class="job-detail-row">
                        <strong>Application Deadline:</strong> <span id="viewDeadline">-</span>
                    </div>
                </div>
                
                <div class="job-detail-section">
                    <h4><i class="fas fa-file-alt"></i> Description</h4>
                    <p id="viewDescription">-</p>
                </div>
                
                <div class="job-detail-section">
                    <h4><i class="fas fa-list-check"></i> Requirements</h4>
                    <p id="viewRequirements">-</p>
                </div>
                
                <div class="job-detail-section">
                    <h4><i class="fas fa-tasks"></i> Responsibilities</h4>
                    <p id="viewResponsibilities">-</p>
                </div>
                
                <div class="job-detail-section">
                    <h4><i class="fas fa-tools"></i> Skills</h4>
                    <p id="viewSkills">-</p>
                </div>
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="closeJobViewModal()">
                    <i class="fas fa-times"></i> Close
                </button>
                <button type="button" class="btn btn-primary" onclick="editFromViewModal()">
                    <i class="fas fa-edit"></i> Edit Job
                </button>
            </div>
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
                <input type="hidden" id="jobId" name="jobId">
                <input type="hidden" name="action" id="jobAction" value="create_job">
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div class="form-group">
                        <label for="jobTitle">Job Title *</label>
                        <input type="text" id="jobTitle" name="jobTitle" required placeholder="e.g., Senior Software Developer">
                    </div>
                    <div class="form-group">
                        <label for="department">Department</label>
                        <input type="text" id="department" name="department" placeholder="e.g., Engineering, Marketing">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="jobDescription">Job Description *</label>
                    <textarea id="jobDescription" name="jobDescription" required rows="4" placeholder="Describe the role, responsibilities, and what makes this position exciting..."></textarea>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div class="form-group">
                        <label for="requirements">Requirements</label>
                        <textarea id="requirements" name="requirements" rows="3" placeholder="List the required qualifications, experience, and skills..."></textarea>
                    </div>
                    <div class="form-group">
                        <label for="responsibilities">Responsibilities</label>
                        <textarea id="responsibilities" name="responsibilities" rows="3" placeholder="Outline the key responsibilities and daily tasks..."></textarea>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="skills">Required Skills</label>
                    <input type="text" id="skills" name="skills" placeholder="e.g., JavaScript, React, Node.js, Python, SQL (comma-separated)">
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div class="form-group">
                        <label for="location">Location *</label>
                        <input type="text" id="location" name="location" required placeholder="e.g., New York, Remote">
                    </div>
                    <div class="form-group">
                        <label for="jobType">Job Type *</label>
                        <select id="jobType" name="jobType" required>
                            <option value="">Select Job Type</option>
                            <option value="full-time">Full-time</option>
                            <option value="part-time">Part-time</option>
                            <option value="contract">Contract</option>
                            <option value="freelance">Freelance</option>
                            <option value="internship">Internship</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="currency">Currency</label>
                        <select id="currency" name="currency">
                            <option value="USD">USD</option>
                            <option value="EUR">EUR</option>
                            <option value="GBP">GBP</option>
                            <option value="BDT">BDT</option>
                        </select>
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div class="form-group">
                        <label for="salaryMin">Minimum Salary</label>
                        <input type="number" id="salaryMin" name="salaryMin" placeholder="e.g., 50000" min="0">
                    </div>
                    <div class="form-group">
                        <label for="salaryMax">Maximum Salary</label>
                        <input type="number" id="salaryMax" name="salaryMax" placeholder="e.g., 80000" min="0">
                    </div>
                    <div class="form-group">
                        <label for="experienceLevel">Experience Level</label>
                        <select id="experienceLevel" name="experienceLevel">
                            <option value="entry">Entry Level</option>
                            <option value="mid" selected>Mid Level</option>
                            <option value="senior">Senior Level</option>
                            <option value="lead">Lead Level</option>
                            <option value="executive">Executive</option>
                        </select>
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div class="form-group">
                        <label for="educationLevel">Education Level</label>
                        <select id="educationLevel" name="educationLevel">
                            <option value="high-school">High School</option>
                            <option value="associate">Associate</option>
                            <option value="bachelor" selected>Bachelor's</option>
                            <option value="master">Master's</option>
                            <option value="phd">PhD</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="closingDate">Application Deadline</label>
                        <input type="date" id="closingDate" name="closingDate">
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeJobPostModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="submitJobPost">
                        <i class="fas fa-save" style="margin-right: 5px;"></i>
                        <span id="submitJobText">Post Job</span>
                    </button>
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

        document.getElementById('postNewJobBtn').addEventListener('click', () => {
             document.getElementById('jobPostModal').style.display = 'flex';
             // Reset form for new job
             document.getElementById('jobPostForm').reset();
             document.getElementById('jobId').value = '';
             document.getElementById('jobAction').value = 'create_job';
             document.getElementById('jobModalTitle').textContent = 'Add New Job Post';
             document.getElementById('submitJobText').textContent = 'Post Job';
        });

        // Job Post Form Submission
        document.getElementById('jobPostForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = document.getElementById('submitJobPost');
            const submitText = document.getElementById('submitJobText');
            const originalText = submitText.textContent;
            const jobId = document.getElementById('jobId').value;
            const isUpdate = jobId && jobId !== '';
            
            // Show loading
            submitBtn.disabled = true;
            submitText.textContent = 'Saving...';
            
            const formData = new FormData(this);
            
            // Choose endpoint based on create or update
            const url = isUpdate 
                ? `{{ url('/api/company/job/update') }}/${jobId}`
                : '{{ url("/api/company/job/store") }}';
            
            fetch(url, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeJobPostModal();
                    showSuccessMessage(isUpdate ? 'Job updated successfully!' : 'Job posted successfully!');
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    showErrorMessage(data.message || 'Failed to save job post');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showErrorMessage('An error occurred while saving the job post');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitText.textContent = originalText;
            });
        });

        // Theme Toggle Functions
        function initializeTheme() {
            const savedTheme = localStorage.getItem('candihire-theme') || 'dark';
            applyTheme(savedTheme);
            updateThemeButton(savedTheme);
        }

        function applyTheme(theme) {
            document.documentElement.setAttribute('data-theme', theme);
            localStorage.setItem('candihire-theme', theme);
        }

        function toggleTheme() {
            document.body.style.transition = 'background-color 0.3s ease, color 0.3s ease';
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            applyTheme(newTheme);
            updateThemeButton(newTheme);
        }

        function updateThemeButton(theme) {
            const text = document.getElementById('themeText');
            if (theme === 'dark') {
                text.textContent = 'Light Mode';
            } else {
                text.textContent = 'Dark Mode';
            }
        }

        function setupThemeToggle() {
            const btn = document.getElementById('themeToggleBtn');
            if (btn) btn.addEventListener('click', toggleTheme);
        }

        // Job Action Functions
        function editJobPost(jobId) {
            // Fetch job data and populate the modal
            fetch(`{{ url('/api/company/job') }}/${jobId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const job = data.job;
                        document.getElementById('jobId').value = job.JobID;
                        document.getElementById('jobAction').value = 'update_job';
                        document.getElementById('jobTitle').value = job.JobTitle || '';
                        document.getElementById('department').value = job.Department || '';
                        document.getElementById('jobDescription').value = job.JobDescription || '';
                        document.getElementById('requirements').value = job.Requirements || '';
                        document.getElementById('responsibilities').value = job.Responsibilities || '';
                        document.getElementById('skills').value = job.Skills || '';
                        document.getElementById('location').value = job.Location || '';
                        document.getElementById('jobType').value = job.JobType || '';
                        document.getElementById('currency').value = job.Currency || 'USD';
                        document.getElementById('salaryMin').value = job.SalaryMin || '';
                        document.getElementById('salaryMax').value = job.SalaryMax || '';
                        document.getElementById('experienceLevel').value = job.ExperienceLevel || 'mid';
                        document.getElementById('educationLevel').value = job.EducationLevel || 'bachelor';
                        document.getElementById('closingDate').value = job.ClosingDate || '';
                        
                        document.getElementById('jobModalTitle').textContent = 'Edit Job Post';
                        document.getElementById('submitJobText').textContent = 'Update Job';
                        document.getElementById('jobPostModal').style.display = 'flex';
                    } else {
                        showErrorMessage('Failed to load job details');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showErrorMessage('Network error loading job details');
                });
        }

        let currentViewJobId = null;

        function viewJobPost(jobId) {
            currentViewJobId = jobId;
            // Fetch job data and show in the view modal
            fetch(`{{ url('/api/company/job') }}/${jobId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const job = data.job;
                        document.getElementById('viewJobTitle').textContent = job.JobTitle || 'Job Details';
                        document.getElementById('viewDepartment').textContent = job.Department || 'N/A';
                        document.getElementById('viewLocation').textContent = job.Location || 'N/A';
                        document.getElementById('viewJobType').textContent = job.JobType || 'N/A';
                        
                        // Format salary
                        let salary = 'Not specified';
                        if (job.SalaryMin || job.SalaryMax) {
                            const currency = job.Currency || 'USD';
                            salary = `${currency} ${job.SalaryMin || 0} - ${job.SalaryMax || 0}`;
                        }
                        document.getElementById('viewSalary').textContent = salary;
                        
                        document.getElementById('viewExperience').textContent = job.ExperienceLevel || 'N/A';
                        document.getElementById('viewEducation').textContent = job.EducationLevel || 'N/A';
                        document.getElementById('viewDeadline').textContent = job.ClosingDate || 'No deadline';
                        document.getElementById('viewDescription').textContent = job.JobDescription || 'No description';
                        document.getElementById('viewRequirements').textContent = job.Requirements || 'No requirements listed';
                        document.getElementById('viewResponsibilities').textContent = job.Responsibilities || 'No responsibilities listed';
                        document.getElementById('viewSkills').textContent = job.Skills || 'No specific skills required';
                        
                        document.getElementById('jobViewModal').style.display = 'flex';
                    } else {
                        showErrorMessage('Failed to load job details');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showErrorMessage('Network error loading job details');
                });
        }

        function closeJobViewModal() {
            document.getElementById('jobViewModal').style.display = 'none';
        }

        function editFromViewModal() {
            const jobId = currentViewJobId;
            document.getElementById('jobViewModal').style.display = 'none';
            currentViewJobId = null;
            if (jobId) {
                editJobPost(jobId);
            }
        }

        function deleteJobPost(jobId) {
            if (confirm('Are you sure you want to delete this job post? This action cannot be undone.')) {
                fetch(`{{ url('/api/company/job/delete') }}/${jobId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showSuccessMessage(data.message || 'Job deleted successfully!');
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        showErrorMessage(data.message || 'Failed to delete job post');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showErrorMessage('Network error. Please try again.');
                });
            }
        }

        // Notification helpers
        function showSuccessMessage(message) {
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed; top: 20px; right: 20px;
                background: var(--success); color: white;
                padding: 15px 20px; border-radius: 8px;
                z-index: 10001; font-weight: 500;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            `;
            notification.textContent = message;
            document.body.appendChild(notification);
            setTimeout(() => notification.remove(), 3000);
        }

        function showErrorMessage(message) {
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed; top: 20px; right: 20px;
                background: var(--danger); color: white;
                padding: 15px 20px; border-radius: 8px;
                z-index: 10001; font-weight: 500;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            `;
            notification.textContent = message;
            document.body.appendChild(notification);
            setTimeout(() => notification.remove(), 5000);
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            initializeTheme();
            setupThemeToggle();
        });

        console.log('Job Post Page Loaded');
    </script>
</body>
</html>

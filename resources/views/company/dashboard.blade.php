<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CandiHire - Professional Networking Platform</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/CompanyDashboard.css') }}">
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
                    <div id="companyLogo" style="width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 16px; {{ $companyLogo ? 'background-image: url(' . $companyLogo . '); background-size: cover; background-position: center;' : 'background: linear-gradient(135deg, var(--accent-2), #e67e22);' }}">
                        {{ $companyLogo ? '' : strtoupper(substr($companyName ?? 'C', 0, 1)) }}
                    </div>
                    <div>
                        <div style="color: var(--text-primary); font-weight: 600; font-size: 14px;">Welcome back!</div>
                        <div id="companyNameDisplay" style="color: var(--text-secondary); font-size: 12px;">{{ $companyName ?? 'Company' }}</div>
                    </div>
                </div>
                <button id="editCompanyProfileBtn" style="background: var(--accent-2); color: white; border: none; border-radius: 6px; padding: 8px 12px; font-size: 12px; cursor: pointer; margin-top: 10px; width: 100%; transition: background 0.2s;">
                    <i class="fas fa-building" style="margin-right: 6px;"></i>Edit Profile
                </button>
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
                <a href="{{ url('/company/dashboard') }}" class="nav-item active">
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
                    <i class="fas fa-moon-stars" id="themeIcon"></i>
                    <span id="themeText">Light Mode</span>
                </button>
                <a href="{{ url('/logout') }}" class="logout-btn" style="text-decoration: none; display: flex; justify-content: center; align-items: center;"><i class="fas fa-sign-out-alt" style="margin-right:8px;"></i>Logout</a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            
            <!-- Search and Filter Section -->
            <div class="search-filter-section" style="background: var(--bg-secondary); padding: 20px; border-radius: 12px; margin-bottom: 20px; border: 1px solid var(--border);">
                <div style="display: flex; gap: 15px; align-items: center; margin-bottom: 15px;">
                    <div class="search-box" style="flex: 1; position: relative;">
                        <i class="fas fa-search" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--text-secondary);"></i>
                        <input type="text" id="candidateSearchInput" placeholder="Search by position, skills, or candidate name..." style="width: 100%; padding: 12px 15px 12px 45px; border: 1px solid var(--border); border-radius: 8px; background: var(--bg-primary); color: var(--text-primary); font-size: 14px;">
                    </div>
                    <button id="advancedFilterBtn" style="background: var(--accent-2); color: white; border: none; border-radius: 8px; padding: 12px 16px; cursor: pointer; font-weight: 600; display: flex; align-items: center; gap: 8px;">
                        <i class="fas fa-filter"></i>
                        <span>Advanced Filter</span>
                    </button>
                    <button id="clearFiltersBtn" style="background: var(--text-secondary); color: white; border: none; border-radius: 8px; padding: 12px 16px; cursor: pointer; font-weight: 600; display: flex; align-items: center; gap: 8px;">
                        <i class="fas fa-times"></i>
                        <span>Clear</span>
                    </button>
                </div>
                
                <!-- Advanced Filter Panel -->
                <div id="advancedFilterPanel" style="display: none; border-top: 1px solid var(--border); padding-top: 15px;">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                        <div>
                            <label style="display: block; margin-bottom: 5px; color: var(--text-primary); font-weight: 600;">Skills:</label>
                            <input type="text" id="skillsFilter" placeholder="e.g., React, Python, SQL" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border); border-radius: 6px; background: var(--bg-primary); color: var(--text-primary);">
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 5px; color: var(--text-primary); font-weight: 600;">Location:</label>
                            <input type="text" id="locationFilter" placeholder="e.g., Remote, New York" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border); border-radius: 6px; background: var(--bg-primary); color: var(--text-primary);">
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 5px; color: var(--text-primary); font-weight: 600;">Experience Level:</label>
                            <select id="experienceFilter" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border); border-radius: 6px; background: var(--bg-primary); color: var(--text-primary);">
                                <option value="">All Levels</option>
                                <option value="entry">Entry Level</option>
                                <option value="mid">Mid Level</option>
                                <option value="senior">Senior Level</option>
                            </select>
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 5px; color: var(--text-primary); font-weight: 600;">Education:</label>
                            <select id="educationFilter" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border); border-radius: 6px; background: var(--bg-primary); color: var(--text-primary);">
                                <option value="">All Education</option>
                                <option value="high-school">High School</option>
                                <option value="associate">Associate</option>
                                <option value="bachelor">Bachelor's</option>
                                <option value="master">Master's</option>
                                <option value="phd">PhD</option>
                            </select>
                        </div>
                    </div>
                    <div style="margin-top: 15px; text-align: right;">
                        <button id="applyFiltersBtn" style="background: var(--accent-1); color: white; border: none; border-radius: 6px; padding: 10px 20px; cursor: pointer; font-weight: 600;">
                            Apply Filters
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Loading indicator -->
            <div id="loadingIndicator" style="text-align: center; padding: 40px; color: var(--text-secondary);">
                <i class="fas fa-spinner fa-spin" style="font-size: 24px; margin-right: 10px;"></i>
                Loading candidate posts...
            </div>
            
            <!-- Candidate posts container -->
            <div id="candidatePostsContainer">
                <!-- Candidate posts will be loaded here -->
                 @forelse($candidatePosts ?? [] as $post)
                    <!-- Placeholder loop for server-side rendering, though JS usually handles this in the original -->
                    <div class="candidate-card">
                         <!-- Simplified Post Structure for server-render -->
                        <div class="candidate-header">
                            <div class="candidate-avatar">{{ strtoupper(substr($post['name'] ?? 'U', 0, 1)) }}</div>
                            <div class="candidate-info">
                                <h3>{{ $post['name'] ?? 'Candidate Name' }}</h3>
                                <p>{{ $post['title'] ?? 'Job Seeker' }}</p>
                            </div>
                        </div>
                        <div class="candidate-content">
                             <p>{{ $post['description'] ?? 'Looking for opportunities...' }}</p>
                        </div>
                    </div>
                 @empty
                    <!-- If no server data, JS will likely fetch it -->
                 @endforelse
            </div>
            
            <!-- No posts message -->
            <div id="noPostsMessage" style="display: none; text-align: center; padding: 40px; color: var(--text-secondary);">
                <i class="fas fa-users" style="font-size: 48px; margin-bottom: 16px; opacity: 0.5;"></i>
                <h3>No candidate posts available</h3>
                <p>There are currently no active candidate job-seeking posts.</p>
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
                
                <form id="companyProfileEditForm" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="companyLogoFile">Company Logo</label>
                        <input type="file" id="companyLogoFile" name="logo" accept="image/*">
                        <div id="currentCompanyLogo" style="margin-top: 10px; display: none;">
                            <img id="companyLogoPreview" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 2px solid var(--border);" />
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="companyName">Company Name *</label>
                        <input type="text" id="companyName" name="companyName" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="industry">Industry *</label>
                        <input type="text" id="industry" name="industry" required placeholder="e.g., Technology, Healthcare, Finance">
                    </div>
                    
                    <div class="form-group">
                        <label for="companySize">Company Size *</label>
                        <select id="companySize" name="companySize" required>
                            <option value="">Select Company Size</option>
                            <option value="1-10">1-10 employees</option>
                            <option value="11-50">11-50 employees</option>
                            <option value="51-200">51-200 employees</option>
                            <option value="201-500">201-500 employees</option>
                            <option value="501-1000">501-1000 employees</option>
                            <option value="1000+">1000+ employees</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="phoneNumber">Phone Number</label>
                        <input type="tel" id="phoneNumber" name="phoneNumber" placeholder="+1 (555) 123-4567">
                    </div>
                    
                    <div class="form-group">
                        <label for="website">Website</label>
                        <input type="url" id="website" name="website" placeholder="https://www.yourcompany.com">
                    </div>
                    
                    <div class="form-group">
                        <label for="companyDescription">Company Description *</label>
                        <textarea id="companyDescription" name="companyDescription" required placeholder="Describe your company, its mission, and what makes it unique"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea id="address" name="address" placeholder="Street address, building number"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="city">City</label>
                        <input type="text" id="city" name="city" placeholder="City">
                    </div>
                    
                    <div class="form-group">
                        <label for="state">State/Province</label>
                        <input type="text" id="state" name="state" placeholder="State or Province">
                    </div>
                    
                    <div class="form-group">
                        <label for="country">Country</label>
                        <input type="text" id="country" name="country" placeholder="Country">
                    </div>
                    
                    <div class="form-group">
                        <label for="postalCode">Postal Code</label>
                        <input type="text" id="postalCode" name="postalCode" placeholder="ZIP/Postal Code">
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeCompanyProfileEditPopup()">
                            <i class="fas fa-times"></i>
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-primary" id="submitCompanyProfileUpdate">
                            <i class="fas fa-save"></i>
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>


        <!-- Right Sidebar -->
        <div class="right-sidebar">
            <!-- Messaging System -->
            <div class="sidebar-section">
                <div class="section-title">Messages</div>
                <div id="messagingSidebar">
                    <!-- Messaging UI Placeholder -->
                <div id="messagingSidebar">
                    <!-- Messaging UI - Now opens in new page -->
                    <!-- Message Button Component -->
                    <button id="openMessagingBtn" class="message-btn" onclick="window.location.href='{{ route('messages.index') }}'">
                        <i class="fas fa-comments"></i>
                        Messages
                        <span id="unreadCount" class="unread-badge" style="display: none;">0</span>
                    </button>
                    
                    <style>
                        .message-btn {
                            width: 100%;
                            background: linear-gradient(135deg, var(--accent-1), var(--accent-hover));
                            color: white;
                            border: none;
                            border-radius: 12px;
                            padding: 16px;
                            font-size: 16px;
                            font-weight: 600;
                            cursor: pointer;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            gap: 12px;
                            transition: all 0.3s ease;
                            box-shadow: 0 4px 15px rgba(88, 166, 255, 0.3);
                            position: relative;
                        }
                        
                        .message-btn:hover {
                            transform: translateY(-2px);
                            box-shadow: 0 6px 20px rgba(88, 166, 255, 0.4);
                        }
                        
                        .unread-badge {
                            background: var(--danger);
                            color: white;
                            font-size: 12px;
                            font-weight: bold;
                            padding: 2px 8px;
                            border-radius: 10px;
                            margin-left: auto;
                            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
                        }
                    </style>
                </div>
                </div>
            </div>
        </div>

        <!-- Invite for Exam Popup -->
        <div id="inviteExamPopup" class="popup-overlay" style="display: none;">
            <div class="popup-content">
                <div class="popup-header">
                    <div class="popup-title">
                        <i class="fas fa-pencil-alt"></i>
                        Invite Candidate for Exam
                    </div>
                    <button class="popup-close" onclick="closeInviteExamPopup()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <div class="invite-exam-content">
                    <div class="candidate-info-section" style="background: var(--bg-tertiary); padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid var(--border);">
                        <h4 style="margin: 0 0 10px 0; color: var(--text-primary);">Selected Candidate</h4>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div id="selectedCandidateAvatar" style="width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 16px; background: linear-gradient(135deg, var(--accent-1), var(--accent-hover));"></div>
                            <div>
                                <div id="selectedCandidateName" style="font-weight: 600; color: var(--text-primary);"></div>
                                <div id="selectedCandidatePosition" style="font-size: 12px; color: var(--text-secondary);"></div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="jobPostSelect">Select Job Post *</label>
                        <select id="jobPostSelect" required>
                            <option value="">Choose a job post...</option>
                        </select>
                    </div>

                    <div id="applicationStatus" style="display: none; margin-bottom: 20px;">
                        <div id="alreadyAppliedMessage" style="background: var(--warning); color: white; padding: 12px; border-radius: 8px; font-weight: 500; display: flex; align-items: center; gap: 8px;">
                            <i class="fas fa-info-circle"></i>
                            <span>This candidate has already applied for this position.</span>
                        </div>
                    </div>

                    <div id="examStatus" style="display: none; margin-bottom: 20px;">
                        <div id="examStatusMessage" style="background: var(--info); color: white; padding: 12px; border-radius: 8px; font-weight: 500; display: flex; align-items: center; gap: 8px;">
                            <i class="fas fa-info-circle"></i>
                            <span>Exam will be automatically assigned when you send the invitation.</span>
                        </div>
                    </div>


                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeInviteExamPopup()">
                            <i class="fas fa-times"></i>
                            Cancel
                        </button>
                        <button type="button" class="btn btn-primary" id="sendExamInviteBtn" disabled>
                            <i class="fas fa-paper-plane"></i>
                            Send Exam Invitation
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Report Candidate Post Popup -->
        <div id="reportCandidatePopup" class="popup-overlay" style="display: none;">
             <div class="popup-content" style="max-width: 600px;">
                <div class="popup-header">
                    <div class="popup-title">
                        <i class="fas fa-flag"></i>
                        Report Candidate Post
                    </div>
                    <button class="popup-close" onclick="closeReportCandidatePopup()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <form id="reportCandidateForm">
                    <input type="hidden" id="reportCandidateId" name="candidateId">
                    
                    <div class="form-group">
                        <label for="reportCandidateName">Candidate Name</label>
                        <input type="text" id="reportCandidateName" name="candidateName" readonly style="background: var(--bg-tertiary); color: var(--text-secondary);">
                    </div>
                    
                    <div class="form-group">
                        <label for="reportJobTitle">Job Title</label>
                        <input type="text" id="reportJobTitle" name="jobTitle" readonly style="background: var(--bg-tertiary); color: var(--text-secondary);">
                    </div>
                    
                    <div class="form-group">
                        <label for="reportReason">Reason for Report *</label>
                        <select id="reportReason" name="reason" required>
                            <option value="">Select a reason</option>
                            <option value="inappropriate_content">Inappropriate Content</option>
                            <option value="misleading_information">Misleading Information</option>
                            <option value="spam">Spam</option>
                            <option value="fake_profile">Fake Profile</option>
                            <option value="discriminatory">Discriminatory Language</option>
                            <option value="scam">Potential Scam</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="reportDescription">Description *</label>
                        <textarea id="reportDescription" name="description" placeholder="Please provide details about why you're reporting this candidate post..." required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="reportContact">Your Contact (Optional)</label>
                        <input type="text" id="reportContact" name="contact" placeholder="Email or phone number for follow-up">
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeReportCandidatePopup()">
                            <i class="fas fa-times"></i>
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-danger" id="submitCandidateReport">
                            <i class="fas fa-flag"></i>
                            Submit Report
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        // Company Profile editing functionality
        let currentCompanyId = {{ $companyId ?? 'null' }};

        function closeCompanyProfileEditPopup() {
            document.getElementById('companyProfileEditPopup').style.display = 'none';
        }

        function closeInviteExamPopup() {
             document.getElementById('inviteExamPopup').style.display = 'none';
             document.body.style.overflow = 'auto';
        }
        
        function closeReportCandidatePopup() {
            document.getElementById('reportCandidatePopup').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        // Theme Toggle
        const themeToggleBtn = document.getElementById('themeToggleBtn');
        themeToggleBtn.addEventListener('click', () => {
             const html = document.documentElement;
             const currentTheme = html.getAttribute('data-theme');
             const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
             html.setAttribute('data-theme', newTheme);
             document.getElementById('themeText').innerText = newTheme === 'dark' ? 'Light Mode' : 'Dark Mode';
        });

        // Setup profile editing buttons
        document.getElementById('editCompanyProfileBtn').addEventListener('click', () => {
            // Fetch current company profile data and populate form
            fetch(`{{ url('/api/company-profile') }}/${currentCompanyId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.company) {
                        const c = data.company;
                        // Populate form fields with existing data
                        document.getElementById('companyName').value = c.CompanyName || '';
                        document.getElementById('industry').value = c.Industry || '';
                        document.getElementById('companySize').value = c.CompanySize || '';
                        document.getElementById('phoneNumber').value = c.PhoneNumber || '';
                        document.getElementById('website').value = c.Website || '';
                        document.getElementById('companyDescription').value = c.CompanyDescription || '';
                        document.getElementById('address').value = c.Address || '';
                        document.getElementById('city').value = c.City || '';
                        document.getElementById('state').value = c.State || '';
                        document.getElementById('country').value = c.Country || '';
                        document.getElementById('postalCode').value = c.PostalCode || '';
                        
                        // Show current logo if exists
                        const currentLogo = document.getElementById('currentCompanyLogo');
                        if (currentLogo && c.Logo) {
                            currentLogo.src = c.Logo;
                            currentLogo.style.display = 'block';
                        }
                    }
                    // Show popup after populating
                    document.getElementById('companyProfileEditPopup').style.display = 'flex';
                })
                .catch(error => {
                    console.error('Error loading company profile:', error);
                    // Still show popup even if fetch fails
                    document.getElementById('companyProfileEditPopup').style.display = 'flex';
                });
        });

        // Initialize (Mocking JS load)
        document.addEventListener('DOMContentLoaded', () => {
             setTimeout(() => {
                 document.getElementById('loadingIndicator').style.display = 'none';
                 // If no posts were server rendered, maybe show no post message or fetch via AJAX
                 if(document.getElementById('candidatePostsContainer').children.length === 0) {
                     // document.getElementById('noPostsMessage').style.display = 'block';
                 }
             }, 500);
        });

        // Company Profile Form Submission
        document.getElementById('companyProfileEditForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = document.getElementById('submitCompanyProfileUpdate');
            const originalText = submitBtn.innerHTML;
            
            // Show loading
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            
            const formData = new FormData(this);
            
            fetch('{{ url("/api/company-profile/update") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeCompanyProfileEditPopup();
                    showSuccessMessage('Company profile updated successfully!');
                    
                    // Update the display
                    if (data.companyName) {
                        document.getElementById('companyNameDisplay').textContent = data.companyName;
                        
                        // Update logo
                        const logo = document.getElementById('companyLogo');
                        if (data.logo) {
                            logo.style.backgroundImage = `url(${data.logo})`;
                            logo.style.backgroundSize = 'cover';
                            logo.style.backgroundPosition = 'center';
                            logo.textContent = '';
                        } else {
                            logo.style.backgroundImage = '';
                            logo.style.background = 'linear-gradient(135deg, var(--accent-2), #e67e22)';
                            logo.textContent = data.companyName.charAt(0).toUpperCase();
                        }
                    }
                } else {
                    showErrorMessage(data.message || 'Failed to update company profile');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showErrorMessage('Network error. Please try again.');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });

        // Notification functions
        function showSuccessMessage(message) {
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: var(--success);
                color: white;
                padding: 15px 20px;
                border-radius: 8px;
                z-index: 10001;
                font-weight: 500;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            `;
            notification.textContent = message;
            document.body.appendChild(notification);
            setTimeout(() => notification.remove(), 3000);
        }

        function showErrorMessage(message) {
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: var(--danger);
                color: white;
                padding: 15px 20px;
                border-radius: 8px;
                z-index: 10001;
                font-weight: 500;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            `;
            notification.textContent = message;
            document.body.appendChild(notification);
            setTimeout(() => notification.remove(), 3000);
        }

        // ============================================================
        // Advanced Filter Functionality
        // ============================================================
        let currentSearchTerm = '';
        let currentFilters = {
            skills: '',
            location: '',
            experience: '',
            education: ''
        };

        // Toggle Advanced Filter Panel
        document.getElementById('advancedFilterBtn').addEventListener('click', function() {
            const panel = document.getElementById('advancedFilterPanel');
            if (panel.style.display === 'none' || panel.style.display === '') {
                panel.style.display = 'block';
                this.style.background = 'var(--accent-1)';
            } else {
                panel.style.display = 'none';
                this.style.background = 'var(--accent-2)';
            }
        });

        // Search Input Handler
        document.getElementById('candidateSearchInput').addEventListener('keyup', function(e) {
            currentSearchTerm = this.value;
            if (e.key === 'Enter') {
                loadCandidatePosts();
            }
        });

        // Apply Filters Button
        document.getElementById('applyFiltersBtn').addEventListener('click', function() {
            currentFilters.skills = document.getElementById('skillsFilter').value;
            currentFilters.location = document.getElementById('locationFilter').value;
            currentFilters.experience = document.getElementById('experienceFilter').value;
            currentFilters.education = document.getElementById('educationFilter').value;
            loadCandidatePosts();
        });

        // Clear Filters Button
        document.getElementById('clearFiltersBtn').addEventListener('click', function() {
            document.getElementById('candidateSearchInput').value = '';
            document.getElementById('skillsFilter').value = '';
            document.getElementById('locationFilter').value = '';
            document.getElementById('experienceFilter').value = '';
            document.getElementById('educationFilter').value = '';
            currentSearchTerm = '';
            currentFilters = { skills: '', location: '', experience: '', education: '' };
            loadCandidatePosts();
        });

        // Load Candidate Posts with Filters
        function loadCandidatePosts() {
            const container = document.getElementById('candidatePostsContainer');
            const loading = document.getElementById('loadingIndicator');
            
            // Show loading
            if (loading) loading.style.display = 'flex';
            container.innerHTML = '';
            
            // Build query parameters
            const params = new URLSearchParams();
            if (currentSearchTerm) params.append('search', currentSearchTerm);
            if (currentFilters.skills) params.append('skills', currentFilters.skills);
            if (currentFilters.location) params.append('location', currentFilters.location);
            if (currentFilters.experience) params.append('experience', currentFilters.experience);
            if (currentFilters.education) params.append('education', currentFilters.education);
            
            fetch(`{{ url('/api/job-seeking-posts') }}?${params.toString()}`)
                .then(response => response.json())
                .then(data => {
                    if (loading) loading.style.display = 'none';
                    
                    if (data.success && data.posts && data.posts.length > 0) {
                        displayCandidatePosts(data.posts);
                    } else {
                        container.innerHTML = `
                            <div style="text-align: center; padding: 40px; color: var(--text-secondary);">
                                <i class="fas fa-users" style="font-size: 48px; margin-bottom: 16px; opacity: 0.5;"></i>
                                <h3>No candidates found</h3>
                                <p>Try adjusting your search or filters</p>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error loading candidates:', error);
                    if (loading) loading.style.display = 'none';
                    showErrorMessage('Failed to load candidate posts');
                });
        }

        // Display candidate posts
        function displayCandidatePosts(posts) {
            const container = document.getElementById('candidatePostsContainer');
            container.innerHTML = '';
            
            posts.forEach(post => {
                const postElement = createCandidatePostElement(post);
                container.appendChild(postElement);
            });
        }

        // Create candidate post element (Matches Legacy Logic)
        function createCandidatePostElement(post) {
            const postDiv = document.createElement('div');
            postDiv.className = 'candidate-card';
            
            // Get initials from name
            const initials = (post.FullName || 'U').split(' ').map(name => name[0]).join('').toUpperCase();
            
            // Create avatar HTML - use profile picture if available, otherwise use initials
            let avatarHtml = '';
            if (post.ProfilePicture) {
                // Determine if ProfilePicture is a full URL or needs asset()
                const profilePicUrl = post.ProfilePicture.startsWith('http') ? post.ProfilePicture : `{{ asset('') }}${post.ProfilePicture}`;
                avatarHtml = `<div class="candidate-avatar" style="background-image: url('${profilePicUrl}'); background-size: cover; background-position: center; color: transparent;">${initials}</div>`;
            } else {
                avatarHtml = `<div class="candidate-avatar">${initials}</div>`;
            }
            
            // Split skills into array for display
            const skillsArray = post.KeySkills ? post.KeySkills.split(',').map(skill => skill.trim()) : [];
            const skillsHtml = skillsArray.map(skill => `<div class="skill-tag">${skill}</div>`).join('');
            
            // Format date
            const postDate = new Date(post.CreatedAt).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
            
            // Create location display
            const locationHtml = post.Location ? `<div class="post-info-item"><i class="fas fa-map-marker-alt"></i> ${post.Location}</div>` : '';
            
            // Create experience display
            const experienceHtml = post.Experience ? `<div class="post-info-item"><i class="fas fa-briefcase"></i> ${post.Experience}</div>` : '';
            
            // Create education display
            const educationHtml = post.Education ? `<div class="post-info-item"><i class="fas fa-graduation-cap"></i> ${post.Education}</div>` : '';
            
            // Create soft skills display
            const softSkillsArray = post.SoftSkills ? post.SoftSkills.split(',').map(skill => skill.trim()) : [];
            const softSkillsHtml = softSkillsArray.length > 0 ? 
                `<div class="soft-skills-section">
                    <h4><i class="fas fa-heart" style="color: var(--accent-2);"></i> Soft Skills</h4>
                    <div class="soft-skills-container">
                        ${softSkillsArray.map(skill => `<span class="soft-skill-tag">${skill}</span>`).join('')}
                    </div>
                </div>` : '';
            
            // Create value to employer section
            const valueHtml = post.ValueToEmployer ? 
                `<div class="value-section">
                    <h4><i class="fas fa-star" style="color: var(--accent-2);"></i> Value to Employer</h4>
                    <p class="value-content">${post.ValueToEmployer}</p>
                </div>` : '';
            
            // Create contact info section
            const contactHtml = post.ContactInfo ? 
                `<div class="contact-section">
                    <h4><i class="fas fa-phone" style="color: var(--accent-2);"></i> Contact Information</h4>
                    <p class="contact-content">${post.ContactInfo}</p>
                </div>` : '';
            
            postDiv.innerHTML = `
                <div class="candidate-header">
                    ${avatarHtml}
                    <div class="candidate-info">
                        <h3>${post.FullName}</h3>
                        <p class="candidate-position">${post.JobTitle}</p>
                        <div class="post-meta">
                            <span class="post-date"><i class="fas fa-calendar"></i> Posted ${postDate}</span>
                            ${locationHtml}
                        </div>
                    </div>
                </div>
                
                <div class="candidate-content">
                    <div class="candidate-title">${post.JobTitle}</div>
                    
                    <div class="candidate-description">
                        <h4><i class="fas fa-bullseye" style="color: var(--accent-2);"></i> Career Goal</h4>
                        <p>${post.CareerGoal || 'No career goal provided.'}</p>
                    </div>
                    
                    ${experienceHtml}
                    ${educationHtml}
                    
                    ${skillsHtml ? `
                        <div class="skills-section">
                            <h4><i class="fas fa-code" style="color: var(--accent-2);"></i> Technical Skills</h4>
                            <div class="candidate-skills">${skillsHtml}</div>
                        </div>
                    ` : ''}
                    
                    ${softSkillsHtml}
                    ${valueHtml}
                    ${contactHtml}
                </div>
                
                <div class="candidate-actions">
                    <button class="contact-btn" onclick="openMessageDialog(${post.CandidateID}, ${post.CandidateID}, 'candidate', '${(post.FullName || '').replace(/'/g, "\\'")}', '${post.ProfilePicture || ''}')" title="Message this candidate">
                        <i class="fas fa-comment" style="margin-right: 6px;"></i>Message
                    </button>
                    <button class="contact-btn" onclick="inviteForExam(${post.CandidateID}, '${(post.FullName || '').replace(/'/g, "\\'")}', '${(post.JobTitle || '').replace(/'/g, "\\'")}')">
                        <i class="fas fa-pencil-alt" style="margin-right: 6px;"></i>Invite for Exam
                    </button>
                    <button class="report-btn" onclick="reportCandidatePost(${post.CandidateID}, '${(post.FullName || '').replace(/'/g, "\\'")}', '${(post.JobTitle || '').replace(/'/g, "\\'")}')" title="Report this candidate post">
                        <i class="fas fa-flag" style="margin-right: 6px;"></i>Report
                    </button>
                </div>
            `;
            
            return postDiv;
        }

        // ============================================================
        // Invite for Exam Functionality
        // ============================================================
        
        window.currentCandidateId = null;

        // Invite candidate for exam function
        function inviteForExam(candidateId, name, jobTitle) {
            // Store current candidate data
            window.currentCandidateId = candidateId;
             // Update candidate info in popup
            document.getElementById('selectedCandidateName').textContent = name;
            document.getElementById('selectedCandidatePosition').textContent = jobTitle;
            document.getElementById('selectedCandidateAvatar').textContent = name.charAt(0).toUpperCase();
            
            // Load job posts for this company
            loadJobPosts();
            
            // Show popup
            document.getElementById('inviteExamPopup').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        // Load job posts for the company
        function loadJobPosts() {
            const jobPostSelect = document.getElementById('jobPostSelect');
            jobPostSelect.innerHTML = '<option value="">Loading job posts...</option>';
            
            fetch('{{ url("/api/company/jobs-list") }}')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.jobs) {
                        jobPostSelect.innerHTML = '<option value="">Choose a job post...</option>';
                        
                        data.jobs.forEach(job => {
                            const option = document.createElement('option');
                            option.value = job.JobID;
                            option.textContent = `${job.JobTitle} - ${job.Location} (${job.JobType})`;
                            option.dataset.jobTitle = job.JobTitle;
                            jobPostSelect.appendChild(option);
                        });
                        
                        if (data.jobs.length === 0) {
                            jobPostSelect.innerHTML = '<option value="">No job posts available</option>';
                        }
                    } else {
                        jobPostSelect.innerHTML = '<option value="">Error loading job posts</option>';
                    }
                })
                .catch(error => {
                    console.error('Error loading job posts:', error);
                    jobPostSelect.innerHTML = '<option value="">Error loading job posts</option>';
                });
        }

        // Check if exams are available for the company and show status
        function checkExamAvailability(jobPostId) {
            const examStatus = document.getElementById('examStatus');
            const sendBtn = document.getElementById('sendExamInviteBtn');
            
            fetch(`{{ url("/api/exams/check-availability") }}?job_post_id=${jobPostId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.exams && data.exams.length > 0) {
                        // Exams available - show info message and enable button
                        examStatus.style.display = 'block';
                        sendBtn.disabled = false;
                        sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Send Exam Invitation';
                    } else {
                        // No exams available - show warning and disable button
                        examStatus.style.display = 'block';
                        document.getElementById('examStatusMessage').innerHTML = '<i class="fas fa-exclamation-triangle"></i><span>No exams available. Please create an exam first.</span>';
                        document.getElementById('examStatusMessage').style.background = 'var(--warning)';
                        sendBtn.disabled = true;
                        sendBtn.innerHTML = '<i class="fas fa-ban"></i> No Exams Available';
                    }
                })
                .catch(error => {
                    console.error('Error checking exam availability:', error);
                    examStatus.style.display = 'block';
                    document.getElementById('examStatusMessage').innerHTML = '<i class="fas fa-exclamation-triangle"></i><span>Error checking exam availability.</span>';
                    document.getElementById('examStatusMessage').style.background = 'var(--danger)';
                    sendBtn.disabled = true;
                });
        }

        // Check if candidate has already applied for the selected job
        function checkApplicationStatus(candidateId, jobPostId) {
            fetch(`{{ url("/api/applications/check-status") }}?candidate_id=${candidateId}&job_post_id=${jobPostId}`)
                .then(response => response.json())
                .then(data => {
                    const statusDiv = document.getElementById('applicationStatus');
                    const sendBtn = document.getElementById('sendExamInviteBtn');
                    
                    if (data.success && data.hasApplied) {
                        statusDiv.style.display = 'block';
                        sendBtn.disabled = true;
                        sendBtn.innerHTML = '<i class="fas fa-info-circle"></i> Candidate Already Applied';
                    } else {
                        statusDiv.style.display = 'none';
                        sendBtn.disabled = false;
                        sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Send Exam Invitation';
                    }
                })
                .catch(error => {
                    console.error('Error checking application status:', error);
                    // On error, allow sending invitation
                    document.getElementById('applicationStatus').style.display = 'none';
                    document.getElementById('sendExamInviteBtn').disabled = false;
                });
        }

        // Send exam invitation with auto-assignment
        function sendExamInvitation() {
            const candidateId = window.currentCandidateId;
            const jobPostId = document.getElementById('jobPostSelect').value;
            
            if (!candidateId || !jobPostId) {
                showErrorMessage('Please select a job post');
                return;
            }
            
            const sendBtn = document.getElementById('sendExamInviteBtn');
            const originalContent = sendBtn.innerHTML;
            
            sendBtn.disabled = true;
            sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Assigning Exam...';
            
            const formData = new FormData();
            formData.append('candidate_id', candidateId);
            formData.append('job_post_id', jobPostId);
            
            fetch('{{ url("/api/exams/invite") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccessMessage('Exam assigned successfully! The candidate will receive the exam invitation.');
                    closeInviteExamPopup();
                } else {
                    showErrorMessage(data.message || 'Failed to assign exam');
                }
            })
            .catch(error => {
                console.error('Error assigning exam:', error);
                showErrorMessage('Network error. Please try again.');
            })
            .finally(() => {
                sendBtn.disabled = false;
                sendBtn.innerHTML = originalContent;
            });
        }

        // ============================================================
        // Report Candidate Functionality
        // ============================================================

        function reportCandidatePost(candidateId, candidateName, jobTitle) {
            console.log('Opening report popup for candidate:', candidateId);
            
            document.getElementById('reportCandidateId').value = candidateId;
            document.getElementById('reportCandidateName').value = candidateName;
            document.getElementById('reportJobTitle').value = jobTitle;
            
            document.getElementById('reportCandidateForm').reset();
            document.getElementById('reportCandidateId').value = candidateId;
            document.getElementById('reportCandidateName').value = candidateName;
            document.getElementById('reportJobTitle').value = jobTitle;
            
            const popup = document.getElementById('reportCandidatePopup');
            popup.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function submitCandidateReport() {
            console.log('Submitting candidate report...');
            
            const form = document.getElementById('reportCandidateForm');
            const submitBtn = document.getElementById('submitCandidateReport');
            const formData = new FormData(form);
            
            if (submitBtn.disabled) {
                console.log('Report already being submitted, ignoring duplicate request');
                return;
            }
            
            const reason = formData.get('reason');
             if (!reason) {
                showErrorMessage('Please select a reason for reporting.');
                return;
            }
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
            
            fetch('{{ url("/api/reports/candidate") }}', {
                method: 'POST',
                 headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeReportCandidatePopup();
                    showSuccessMessage('Report submitted successfully! Thank you for helping keep our platform safe.');
                } else {
                    showErrorMessage(data.message || 'Failed to submit report');
                }
            })
            .catch(error => {
                console.error('Error submitting report:', error);
                showErrorMessage('Network error. Please try again.');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-flag"></i> Submit Report';
            });
        }
        
        // Setup invite exam popup event listeners
        function setupInviteExamPopup() {
            // Job post selection change
            document.getElementById('jobPostSelect').addEventListener('change', function() {
                const jobPostId = this.value;
                const candidateId = window.currentCandidateId;
                
                if (jobPostId) {
                    // Check exam availability for this company
                    checkExamAvailability(jobPostId);
                    
                    // Check application status
                    if (candidateId) {
                        checkApplicationStatus(candidateId, jobPostId);
                    }
                } else {
                    // Reset status
                    document.getElementById('examStatus').style.display = 'none';
                    document.getElementById('applicationStatus').style.display = 'none';
                    document.getElementById('sendExamInviteBtn').disabled = true;
                }
            });
            
             // Create close function if it's not global
             window.closeInviteExamPopup = function() {
                document.getElementById('inviteExamPopup').style.display = 'none';
                document.body.style.overflow = 'auto';
            };

            
            // Send exam invitation button
            document.getElementById('sendExamInviteBtn').addEventListener('click', sendExamInvitation);
            
            // Close popup when clicking outside
            document.getElementById('inviteExamPopup').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeInviteExamPopup();
                }
            });
        }

        function setupReportCandidate() {
             const reportForm = document.getElementById('reportCandidateForm');
             const reportPopup = document.getElementById('reportCandidatePopup');
             
             if(reportForm) {
                 reportForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    submitCandidateReport();
                });
             }
             
             if(reportPopup) {
                 reportPopup.addEventListener('click', function(e) {
                    if (e.target === reportPopup) {
                        closeReportCandidatePopup();
                    }
                });
             }
        }

        document.addEventListener('DOMContentLoaded', function() {
            setupInviteExamPopup();
            setupReportCandidate();
            
            // We need to call loadCandidatePosts LAST to ensure everything is ready
            loadCandidatePosts();
        });

        console.log('Company Dashboard Loaded');
    </script>
    <!-- Message Popup Modal -->
    <div id="messagePopup" class="message-popup-overlay" style="display: none;">
        <div class="message-popup-content">
            <div class="message-popup-header">
                <div class="message-popup-title">
                    <i class="fas fa-comment"></i>
                    Send Message
                </div>
                <button class="message-popup-close" onclick="closeMessagePopup()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="message-popup-body">
                <div class="message-recipient-info" id="messageRecipientInfo">
                    <div class="recipient-avatar" id="recipientAvatar"></div>
                    <div class="recipient-details">
                        <div class="recipient-name" id="recipientName"></div>
                        <div class="recipient-type" id="recipientType"></div>
                    </div>
                </div>
                
                <form id="messagePopupForm">
                    <input type="hidden" id="popupRecipientId" name="recipient_id">
                    <input type="hidden" id="popupRecipientType" name="recipient_type">
                    
                    <div class="form-group">
                        <label for="popupMessage">Your Message *</label>
                        <textarea 
                            id="popupMessage" 
                            name="message" 
                            placeholder="Type your message here..."
                            required
                            rows="4"
                        ></textarea>
                    </div>
                    
                    <div class="message-popup-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeMessagePopup()">
                            <i class="fas fa-times"></i>
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-primary" id="sendMessageBtn">
                            <i class="fas fa-paper-plane"></i>
                            Send Message
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
    /* Message Popup Styles */
    .message-popup-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.7); display: flex; justify-content: center; align-items: center; z-index: 10000; backdrop-filter: blur(5px); }
    .message-popup-content { background: var(--bg-secondary); border-radius: 16px; width: 90%; max-width: 500px; max-height: 90vh; overflow-y: auto; border: 1px solid var(--border); box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3); animation: messagePopupSlideIn 0.3s ease-out; }
    .message-popup-header { display: flex; justify-content: space-between; align-items: center; padding: 20px 25px; border-bottom: 1px solid var(--border); background: var(--bg-tertiary); border-radius: 16px 16px 0 0; }
    .message-popup-title { font-size: 20px; font-weight: 600; color: var(--text-primary); display: flex; align-items: center; gap: 10px; }
    .message-popup-close { background: none; border: none; color: var(--text-secondary); font-size: 20px; cursor: pointer; padding: 8px; border-radius: 50%; transition: all 0.2s; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; }
    .message-popup-close:hover { background: var(--bg-primary); color: var(--text-primary); transform: scale(1.1); }
    .message-popup-body { padding: 25px; }
    .message-recipient-info { display: flex; align-items: center; gap: 15px; padding: 15px; background: var(--bg-primary); border-radius: 12px; border: 1px solid var(--border); margin-bottom: 20px; }
    .recipient-avatar { width: 50px; height: 50px; border-radius: 50%; background: var(--accent-2); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 18px; flex-shrink: 0; }
    .recipient-details { flex: 1; }
    .recipient-name { font-weight: 600; font-size: 16px; color: var(--text-primary); margin-bottom: 4px; }
    .recipient-type { font-size: 14px; color: var(--text-secondary); text-transform: capitalize; }
    .message-popup-actions { display: flex; gap: 12px; justify-content: flex-end; margin-top: 25px; padding-top: 20px; border-top: 1px solid var(--border); }
    @keyframes messagePopupSlideIn { from { opacity: 0; transform: scale(0.9) translateY(-20px); } to { opacity: 1; transform: scale(1) translateY(0); } }
    </style>

    <script>
    // MESSAGING SYSTEM & POPUP LOGIC

    class MessagingSystem {
        constructor() {
            // These window variables should be set by the main script already or we fallback
            this.currentUserId = window.currentUserId;
            this.currentUserType = window.currentUserType;
            this.initializeEventListeners();
            this.loadUnreadCount();
            
            // Auto refresh count
            setInterval(() => this.loadUnreadCount(), 30000);
        }

        initializeEventListeners() {}

        async loadUnreadCount() {
            try {
                const response = await fetch('{{ route("api.messages.unread-count") }}');
                const data = await response.json();
                
                if (data.success) {
                    const badge = document.getElementById('unreadCount');
                    if (data.unread_count > 0) {
                        badge.textContent = data.unread_count;
                        badge.style.display = 'inline-block';
                    } else {
                        badge.style.display = 'none';
                    }
                }
            } catch (error) {
                console.error('Error loading unread count:', error);
            }
        }
    }

    // Initialize Messaging System
    const messagingSystem = new MessagingSystem();

    // Alias openMessageDialog to our new function to support card buttons
    window.openMessageDialog = function(receiverId, unused1, unused2, receiverName, receiverAvatar) {
        openMessagePopup(receiverId, 'candidate', receiverName, receiverAvatar);
    };

    function openMessagePopup(recipientId, recipientType, recipientName, recipientAvatar = null) {
        document.getElementById('popupRecipientId').value = recipientId;
        document.getElementById('popupRecipientType').value = recipientType;
        document.getElementById('recipientName').textContent = recipientName;
        document.getElementById('recipientType').textContent = recipientType;
        
        const avatarElement = document.getElementById('recipientAvatar');
        if (recipientAvatar) {
            avatarElement.style.backgroundImage = `url('${recipientAvatar}')`;
            avatarElement.style.backgroundSize = 'cover';
            avatarElement.style.backgroundPosition = 'center';
            avatarElement.textContent = '';
        } else {
            avatarElement.style.backgroundImage = '';
            avatarElement.style.background = 'var(--accent-2)';
            avatarElement.textContent = recipientName.charAt(0).toUpperCase();
        }
        
        document.getElementById('popupMessage').value = '';
        document.getElementById('messagePopup').style.display = 'flex';
        document.body.style.overflow = 'hidden';
        setTimeout(() => document.getElementById('popupMessage').focus(), 100);
    }

    function closeMessagePopup() {
        document.getElementById('messagePopup').style.display = 'none';
        document.body.style.overflow = 'auto';
        document.getElementById('messagePopupForm').reset();
    }

    document.getElementById('messagePopupForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const recipientId = document.getElementById('popupRecipientId').value;
        const recipientType = document.getElementById('popupRecipientType').value;
        const message = document.getElementById('popupMessage').value.trim();
        
        if (!message) {
            showErrorMessage('Please enter a message');
            return;
        }
        
        const sendBtn = document.getElementById('sendMessageBtn');
        const originalContent = sendBtn.innerHTML;
        
        sendBtn.disabled = true;
        sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
        
        try {
            const formData = new FormData();
            formData.append('receiver_id', recipientId);
            formData.append('receiver_type', recipientType);
            formData.append('message', message);

            const response = await fetch('{{ route("api.messages.send") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                showSuccessMessage('Message sent successfully!');
                closeMessagePopup();
            } else {
                showErrorMessage('Failed to send message: ' + (data.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error sending message:', error);
            showErrorMessage('Network error. Please try again.');
        } finally {
            sendBtn.disabled = false;
            sendBtn.innerHTML = originalContent;
        }
    });
    </script>
</body>
</html>

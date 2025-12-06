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
                <div class="nav-item" onclick="window.location.href='{{ url('job-posts') }}'">
                    <i class="fas fa-briefcase"></i>
                    <span>Job Posts</span>
                </div>
                <div class="nav-item" onclick="window.location.href='{{ url('cv-checker') }}'">
                    <i class="fas fa-file-alt"></i>
                    <span>CV Checker</span>
                </div>
                <div class="nav-item active">
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
                    <p class="text-secondary">Messaging system loading...</p>
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
        }
        
        function closeReportCandidatePopup() {
            document.getElementById('reportCandidatePopup').style.display = 'none';
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
             document.getElementById('companyProfileEditPopup').style.display = 'flex';
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

        console.log('Company Dashboard Loaded');
    </script>
</body>
</html>

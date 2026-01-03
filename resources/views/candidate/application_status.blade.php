@php
    // Helper function for status color (migrated from PHP)
    if (!function_exists('getStatusColor')) {
        function getStatusColor($status) {
            switch (strtolower($status)) {
                case 'submitted':
                case 'application submitted':
                case 'applied':
                    return '#8b949e';
                case 'company_invited':
                case 'company invited':
                    return '#79c0ff';
                case 'exam_assigned':
                case 'mcq exam assigned':
                    return '#58a6ff';
                case 'exam_in_progress':
                case 'mcq exam in progress':
                    return '#f59e0b';
                case 'exam_passed':
                case 'mcq exam passed':
                    return '#3fb950';
                case 'waiting_interview':
                case 'waiting for interview call':
                    return '#f59e0b';
                case 'exam_failed':
                case 'mcq exam failed':
                case 'rejected':
                    return '#f85149';
                case 'interview_scheduled':
                case 'called for interview':
                    return '#f59e0b';
                case 'interview_in_progress':
                    return '#f59e0b';
                case 'interview_completed':
                    return '#3fb950';
                case 'under-review':
                case 'under review':
                case 'in review':
                    return '#58a6ff';
                case 'shortlisted':
                    return '#79c0ff';
                case 'interview-scheduled':
                case 'interview scheduled':
                    return '#f59e0b';
                case 'interviewed':
                    return '#f59e0b';
                case 'offer-extended':
                case 'offer extended':
                    return '#3fb950';
                case 'accepted':
                    return '#3fb950';
                case 'withdrawn':
                    return '#f85149';
                default:
                    return '#8b949e';
            }
        }
    }
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Status - CandiHire</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/applicationstatus.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <div class="container">
        <!-- Left Navigation -->
        <div class="left-nav">
            <div class="logo">
              <span class="candiHire">
                  <span class="candi">Candi</span><span class="hire">Hire</span>
              </span>
            </div>
            
            <!-- Welcome Section -->
            <div class="welcome-section" style="background: var(--bg-tertiary); padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid var(--border);">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div id="candidateAvatar" style="width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 16px; {{ $candidateProfilePicture ? 'background-image: url(' . $candidateProfilePicture . '); background-size: cover; background-position: center;' : 'background: linear-gradient(135deg, var(--accent), var(--accent-secondary));' }}">
                        {{ $candidateProfilePicture ? '' : strtoupper(substr($candidateName, 0, 1)) }}
                    </div>
                    <div>
                        <div style="color: var(--text-primary); font-weight: 600; font-size: 14px;">Welcome back!</div>
                        <div id="candidateNameDisplay" style="color: var(--text-secondary); font-size: 12px;">{{ $candidateName }}</div>
                    </div>
                </div>
            </div>
            
            <!-- Main Menu Section -->
            <div class="nav-section">
                <div class="nav-section-title">Main menu</div>
                <a href="{{ url('/candidate/dashboard') }}" class="nav-item">
                    <i class="fas fa-home"></i>
                    <span>News feed</span>
                </a>
                <a href="{{ url('/cv/builder') }}" class="nav-item">
                    <i class="fas fa-file-alt"></i>
                    <span>CV builder</span>
                </a>
                <a href="{{ url('/candidate/applications') }}" class="nav-item active">
                    <i class="fas fa-clipboard-list"></i>
                    <span>Application status</span>
                </a>
            </div>
            
            <!-- Interviews & Exams Section -->
            <div class="nav-section">
                <div class="nav-section-title">Interviews & Exams</div>
                <a href="{{ url('/interview/schedule') }}" class="nav-item">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Interview schedule</span>
                </a>
                <a href="{{ url('/exam/attend') }}" class="nav-item">
                    <i class="fas fa-pencil-alt"></i>
                    <span>Attend Exam</span>
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
                <h1 class="page-title">Recent Applications</h1>
                <div class="header-actions">
                    <div class="search-bar">
                        <input type="text" class="search-input" placeholder="Search applications..." id="searchInput">
                        <i class="fas fa-search search-icon"></i>
                    </div>
                </div>
            </div>

            <!-- Sort Options -->
            <div class="sort-options">
                <button class="sort-btn active" data-sort="date" data-order="desc">Newest First</button>
                <button class="sort-btn" data-sort="date" data-order="asc">Oldest First</button>
                <button class="sort-btn" data-sort="status" data-order="asc">Status (A-Z)</button>
                <button class="sort-btn" data-sort="company" data-order="asc">Company (A-Z)</button>
            </div>

            <!-- Applications Container -->
            <div class="applications-container" id="applicationsContainer">
                @foreach ($applications as $application)
                <div class="application-card" data-id="{{ $application['id'] }}" data-status="{{ strtolower($application['status']) }}" data-company="{{ strtolower($application['company']) }}" data-date="{{ $application['applicationDate'] }}">
                    <div class="card-header">
                        <h2 class="job-title">{{ $application['jobTitle'] }}</h2>
                        <div class="company-name">
                            <i class="fas fa-building"></i>
                            {{ $application['company'] }}
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="application-details">
                            <div class="detail-item">
                                <div class="detail-label">Application Date</div>
                                <div class="detail-value">{{ date('F j, Y', strtotime($application['applicationDate'])) }}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Salary Range</div>
                                <div class="detail-value">{{ $application['salaryRange'] }}</div>
                            </div>
                            @if (isset($application['isCompanyInvited']) && $application['isCompanyInvited'])
                            <div class="detail-item">
                                <div class="detail-label">Type</div>
                                <div class="detail-value" style="color: #79c0ff; font-weight: 600;">
                                    <i class="fas fa-handshake"></i> Company Invitation
                                </div>
                            </div>
                            @endif
                        </div>

                        @if (!empty($application['statusFlow']))
                        <div class="status-history">
                            <h3 class="status-history-title">Application Progress</h3>
                            <div class="timeline">
                                @foreach ($application['statusFlow'] as $index => $status)
                                @php 
                                // Determine CSS class based on status
                                $timelineClass = 'timeline-item';
                                if (isset($status['status_key']) && $status['status_key'] === 'rejected') {
                                    $timelineClass .= ' rejected-status';
                                } elseif ($status['completed']) {
                                    $timelineClass .= ' completed-status';
                                } else {
                                    $timelineClass .= ' current-status';
                                }
                                @endphp
                                <div class="{{ $timelineClass }}">
                                    <div class="timeline-content" style="position: relative;">
                                        <div class="timeline-status" style="color: {{ getStatusColor($status['status']) }}; display: flex; align-items: center; gap: 8px;">
                                            @if ($status['completed'])
                                                <i class="fas fa-check-circle" style="color: #3fb950;"></i>
                                            @else
                                                <i class="fas fa-clock" style="color: #f59e0b;"></i>
                                            @endif
                                            {{ $status['status'] }}
                                        </div>
                                        <div class="timeline-date">{{ date('F j, Y', strtotime($status['date'])) }}</div>
                                        @if (!empty($status['notes']))
                                        <div class="timeline-notes" style="font-size: 12px; color: var(--text-secondary); margin-top: 4px;">
                                            {{ $status['notes'] }}
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <div class="card-actions">
                            <button class="btn btn-primary view-details-btn" data-id="{{ $application['id'] }}">
                                <i class="fas fa-eye"></i>
                                View Details
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Right Sidebar -->
        <div class="right-sidebar" id="rightSidebar">
            <!-- Filters Section -->
            <div class="sidebar-section">
                <div class="section-title">Filter Applications</div>
                <div class="filter-group">
                    <label class="filter-label">Status</label>
                    <select class="filter-select" id="statusFilter">
                        <option value="">All Statuses</option>
                        <option value="application submitted">Application Submitted</option>
                        <option value="under review">Under Review</option>
                        <option value="in review">In Review</option>
                        <option value="interview scheduled">Interview Scheduled</option>
                        <option value="offer extended">Offer Extended</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label">Date Range</label>
                    <select class="filter-select" id="dateFilter">
                        <option value="">All Time</option>
                        <option value="7">Last 7 Days</option>
                        <option value="30">Last 30 Days</option>
                        <option value="90">Last 90 Days</option>
                    </select>
                </div>
                <button class="btn btn-primary" id="applyFiltersBtn" style="width: 100%; margin-top: 10px;">
                    Apply Filters
                </button>
                <button class="btn btn-secondary" id="clearFiltersBtn" style="width: 100%; margin-top: 10px;">
                    Clear Filters
                </button>
            </div>


            <!-- Application Tips -->
            <div class="sidebar-section">
                <div class="section-title">Application Tips</div>
                <div class="tip-item">
                    <i class="fas fa-lightbulb tip-icon"></i>
                    <div class="tip-content">
                        <h4>Follow Up</h4>
                        <p>Send a polite follow-up email if you haven't heard back within 7-10 days.</p>
                    </div>
                </div>
                <div class="tip-item">
                    <i class="fas fa-file-alt tip-icon"></i>
                    <div class="tip-content">
                        <h4>Tailor Your CV</h4>
                        <p>Customize your CV for each application to highlight relevant skills.</p>
                    </div>
                </div>
                <div class="tip-item">
                    <i class="fas fa-comments tip-icon"></i>
                    <div class="tip-content">
                        <h4>Prepare for Interviews</h4>
                        <p>Research the company and practice common interview questions.</p>
                    </div>
                </div>
                <div class="tip-item">
                    <i class="fas fa-network-wired tip-icon"></i>
                    <div class="tip-content">
                        <h4>Network</h4>
                        <p>Connect with employees at the company through professional networks.</p>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Overlay for mobile sidebar -->
    <div class="overlay" id="overlay"></div>

    <!-- Application Details Modal -->
    <div class="modal" id="detailsModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="modalJobTitle">Job Title</h2>
                <button class="close-btn" id="closeModalBtn">&times;</button>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Content will be dynamically inserted here -->
            </div>
        </div>
    </div>


    <!-- Notification Toast -->
    <div class="toast" id="toast">
        <i class="toast-icon fas"></i>
        <div class="toast-message">        </div>
    </div>

    <!-- Profile Edit Popup -->
    <div id="profileEditPopup" class="popup-overlay" style="display: none;">
        <div class="popup-content">
            <div class="popup-header">
                <div class="popup-title">
                    <i class="fas fa-user-edit"></i>
                    Edit Profile
                </div>
                <button class="popup-close" onclick="closeProfileEditPopup()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="profileEditForm" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label for="profilePicture">Profile Picture</label>
                    <input type="file" id="profilePicture" name="profilePicture" accept="image/*">
                    <div id="currentProfilePicture" style="margin-top: 10px; display: none;">
                        <img id="profilePicturePreview" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 2px solid var(--border);" />
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="fullName">Full Name *</label>
                    <input type="text" id="fullName" name="fullName" required>
                </div>
                
                <div class="form-group">
                    <label for="phoneNumber">Phone Number *</label>
                    <input type="tel" id="phoneNumber" name="phoneNumber" required>
                </div>
                
                <div class="form-group">
                    <label for="workType">Work Type *</label>
                    <select id="workType" name="workType" required>
                        <option value="">Select Work Type</option>
                        <option value="full-time">Full-time</option>
                        <option value="part-time">Part-time</option>
                        <option value="contract">Contract</option>
                        <option value="freelance">Freelance</option>
                        <option value="internship">Internship</option>
                        <option value="fresher">Fresher</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="yearsOfExperience">Years of Experience</label>
                    <input type="number" id="yearsOfExperience" name="yearsOfExperience" 
                           placeholder="Enter years of experience" min="0" max="50" 
                           style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px; background-color: var(--bg-secondary); color: var(--text-primary); font-size: 14px;">
                </div>
                
                <div class="form-group">
                    <label for="location">Location</label>
                    <input type="text" id="location" name="location" placeholder="City, Country">
                </div>
                
                <div class="form-group">
                    <label for="skills">Skills</label>
                    <textarea id="skills" name="skills" placeholder="List your key skills separated by commas"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="summary">Professional Summary</label>
                    <textarea id="summary" name="summary" placeholder="Brief description about yourself and your professional background"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="linkedin">LinkedIn Profile</label>
                    <input type="url" id="linkedin" name="linkedin" placeholder="https://linkedin.com/in/yourprofile">
                </div>
                
                <div class="form-group">
                    <label for="github">GitHub Profile</label>
                    <input type="url" id="github" name="github" placeholder="https://github.com/yourusername">
                </div>
                
                <div class="form-group">
                    <label for="portfolio">Portfolio Website</label>
                    <input type="url" id="portfolio" name="portfolio" placeholder="https://yourportfolio.com">
                </div>
                
                <div class="form-group">
                    <label for="education">Education/Degree</label>
                    <input type="text" id="education" name="education" placeholder="e.g., Bachelor's in Computer Science">
                </div>
                
                <div class="form-group">
                    <label for="institute">Institute/University</label>
                    <input type="text" id="institute" name="institute" placeholder="e.g., MIT, Stanford University">
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeProfileEditPopup()">
                        <i class="fas fa-times"></i>
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary" id="submitProfileUpdate">
                        <i class="fas fa-save"></i>
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>


    <script>
        // Profile editing functionality
        function setupProfileEditing() {
            console.log('Setting up profile editing...');
            
            const editProfileBtn = document.getElementById('editProfileBtn');
            const profilePopup = document.getElementById('profileEditPopup');
            const profileForm = document.getElementById('profileEditForm');
            
            if (!editProfileBtn || !profilePopup || !profileForm) {
                console.error('Profile editing elements not found');
                return;
            }
            
            // Open profile edit popup
            editProfileBtn.addEventListener('click', function() {
                console.log('Opening profile edit popup');
                loadCurrentProfile();
                profilePopup.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            });
            
            // Handle form submission
            profileForm.addEventListener('submit', function(e) {
                e.preventDefault();
                updateProfile();
            });
            
            // Handle profile picture preview
            const profilePictureInput = document.getElementById('profilePicture');
            if (profilePictureInput) {
                profilePictureInput.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const preview = document.getElementById('profilePicturePreview');
                            const currentPicture = document.getElementById('currentProfilePicture');
                            if (preview && currentPicture) {
                                preview.src = e.target.result;
                                currentPicture.style.display = 'block';
                            }
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }
            
            // Close popup when clicking outside
            profilePopup.addEventListener('click', function(e) {
                if (e.target === profilePopup) {
                    closeProfileEditPopup();
                }
            });
            
            console.log('Profile editing setup complete');
        }

        // Load current profile data
        function loadCurrentProfile() {
            console.log('Loading current profile data...');
            
            const candidateId = {{ json_encode($sessionCandidateId ?? '') }};
            
            // Update to use Laravel route if available, otherwise mock or keep original
            // fetch(`candidate_profile_handler.php?candidateId=${candidateId}`)
            fetch(`{{ url('api/candidate/profile') }}?candidateId=${candidateId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const candidate = data.candidate;
                        console.log('Profile data loaded:', candidate);
                        
                        // Populate form fields
                        document.getElementById('fullName').value = candidate.FullName || '';
                        document.getElementById('phoneNumber').value = candidate.PhoneNumber || '';
                        document.getElementById('workType').value = candidate.WorkType || '';
                        document.getElementById('yearsOfExperience').value = candidate.YearsOfExperience || 0;
                        document.getElementById('location').value = candidate.Location || '';
                        document.getElementById('skills').value = candidate.Skills || '';
                        document.getElementById('summary').value = candidate.Summary || '';
                        document.getElementById('linkedin').value = candidate.LinkedIn || '';
                        document.getElementById('github').value = candidate.GitHub || '';
                        document.getElementById('portfolio').value = candidate.Portfolio || '';
                        document.getElementById('education').value = candidate.Education || '';
                        document.getElementById('institute').value = candidate.Institute || '';
                        
                        // Handle profile picture
                        if (candidate.ProfilePicture) {
                            const preview = document.getElementById('profilePicturePreview');
                            const currentPicture = document.getElementById('currentProfilePicture');
                            if (preview && currentPicture) {
                                preview.src = candidate.ProfilePicture;
                                currentPicture.style.display = 'block';
                            }
                        }
                    } else {
                        console.error('Failed to load profile:', data.message);
                        showErrorMessage('Failed to load profile data');
                    }
                })
                .catch(error => {
                    console.error('Error loading profile:', error);
                    // For demo purposes if API fails
                    console.warn("Using mock data for demo.");
                });
        }

        // Update profile
        function updateProfile() {
            console.log('Updating profile...');
            
            const form = document.getElementById('profileEditForm');
            const submitBtn = document.getElementById('submitProfileUpdate');
            const formData = new FormData(form);
            
            // Add candidate ID
            const candidateId = {{ json_encode($sessionCandidateId ?? '') }};
            formData.append('candidateId', candidateId);
            
            // Disable button and show loading
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            
            // fetch('candidate_profile_handler.php', {
            fetch('{{ url("api/candidate/profile/update") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeProfileEditPopup();
                    showSuccessMessage('Profile updated successfully!');
                    
                    // Update the display with server response data
                    if (data.fullName) {
                        document.getElementById('candidateNameDisplay').textContent = data.fullName;
                        
                        // Update avatar text or image
                        const avatar = document.getElementById('candidateAvatar');
                        if (data.profilePicture) {
                            // Show profile picture
                            avatar.style.backgroundImage = `url(${data.profilePicture})`;
                            avatar.style.backgroundSize = 'cover';
                            avatar.style.backgroundPosition = 'center';
                            avatar.textContent = '';
                        } else {
                            // Show initials
                            avatar.style.backgroundImage = '';
                            avatar.style.background = 'linear-gradient(135deg, var(--accent), var(--accent-secondary))';
                            avatar.textContent = data.fullName.charAt(0).toUpperCase();
                        }
                    }
                } else {
                    showErrorMessage(data.message || 'Failed to update profile');
                }
            })
            .catch(error => {
                console.error('Error updating profile:', error);
                showErrorMessage('Network error. Please try again.');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-save"></i> Save Changes';
            });
        }

        // Close profile edit popup
        function closeProfileEditPopup() {
            const popup = document.getElementById('profileEditPopup');
            const form = document.getElementById('profileEditForm');
            
            popup.style.display = 'none';
            document.body.style.overflow = 'auto';
            form.reset();
            
            // Hide profile picture preview
            const currentPicture = document.getElementById('currentProfilePicture');
            if (currentPicture) {
                currentPicture.style.display = 'none';
            }
        }

        // Show success message
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
                animation: slideIn 0.3s ease-out;
            `;
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }

        // Show error message
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
                animation: slideIn 0.3s ease-out;
            `;
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 5000);
        }
        
        // Application data (in a real app, this would come from an API)
        const applications = {!! json_encode($applications) !!};

        // DOM Elements
        const applicationsContainer = document.getElementById('applicationsContainer');
        const searchInput = document.getElementById('searchInput');
        const rightSidebar = document.getElementById('rightSidebar');
        const overlay = document.getElementById('overlay');
        const statusFilter = document.getElementById('statusFilter');
        const dateFilter = document.getElementById('dateFilter');
        const applyFiltersBtn = document.getElementById('applyFiltersBtn');
        const clearFiltersBtn = document.getElementById('clearFiltersBtn');
        const detailsModal = document.getElementById('detailsModal');
        const closeModalBtn = document.getElementById('closeModalBtn');
        const modalJobTitle = document.getElementById('modalJobTitle');
        const modalBody = document.getElementById('modalBody');
        const toast = document.getElementById('toast');
        const sortButtons = document.querySelectorAll('.sort-btn');

        // JavaScript version of getStatusColor for modal rendering
        function getStatusColor(status) {
            const statusLower = (status || '').toLowerCase();
            switch (statusLower) {
                case 'submitted':
                case 'application submitted':
                case 'applied':
                    return '#8b949e';
                case 'company_invited':
                case 'company invited':
                    return '#79c0ff';
                case 'exam_assigned':
                case 'mcq exam assigned':
                    return '#58a6ff';
                case 'exam_in_progress':
                case 'mcq exam in progress':
                    return '#f59e0b';
                case 'exam_passed':
                case 'mcq exam passed':
                    return '#3fb950';
                case 'waiting_interview':
                case 'waiting for interview call':
                    return '#f59e0b';
                case 'exam_failed':
                case 'mcq exam failed':
                case 'rejected':
                    return '#f85149';
                case 'interview_scheduled':
                case 'called for interview':
                case 'interview scheduled':
                    return '#f59e0b';
                case 'under-review':
                case 'under review':
                case 'in review':
                    return '#58a6ff';
                case 'shortlisted':
                    return '#79c0ff';
                case 'offer-extended':
                case 'offer extended':
                case 'accepted':
                    return '#3fb950';
                case 'withdrawn':
                    return '#f85149';
                default:
                    return '#8b949e';
            }
        }


        // Search functionality
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            filterApplications();
        });

        // Filter functionality
        applyFiltersBtn.addEventListener('click', function() {
            filterApplications();
            showToast('Filters applied successfully', 'success');
            
            // Hide sidebar on mobile after applying filters
            if (window.innerWidth <= 1200) {
                rightSidebar.classList.remove('show');
                overlay.classList.remove('show');
            }
        });

        clearFiltersBtn.addEventListener('click', function() {
            statusFilter.value = '';
            dateFilter.value = '';
            searchInput.value = '';
            filterApplications();
            showToast('Filters cleared', 'info');
        });



        // Sort functionality
        sortButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Update active state
                sortButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                
                // Get sort parameters
                const sortBy = this.getAttribute('data-sort');
                const sortOrder = this.getAttribute('data-order');
                
                // Sort applications
                sortApplications(sortBy, sortOrder);
            });
        });

        // View details buttons
        document.addEventListener('click', function(e) {
            if (e.target.closest('.view-details-btn')) {
                const btn = e.target.closest('.view-details-btn');
                const applicationId = parseInt(btn.getAttribute('data-id'));
                showApplicationDetails(applicationId);
            }
        });


        // Modal close functionality
        closeModalBtn.addEventListener('click', function() {
            detailsModal.style.display = 'none';
        });

        // Close modal when clicking outside
        window.addEventListener('click', function(e) {
            if (e.target === detailsModal) {
                detailsModal.style.display = 'none';
            }
        });

        // Filter applications function
        function filterApplications() {
            const searchTerm = searchInput.value.toLowerCase();
            const statusValue = statusFilter.value.toLowerCase();
            const dateValue = dateFilter.value;
            
            const cards = document.querySelectorAll('.application-card');
            
            cards.forEach(card => {
                const title = card.querySelector('.job-title').textContent.toLowerCase();
                const company = card.getAttribute('data-company');
                const status = card.getAttribute('data-status');
                const date = new Date(card.getAttribute('data-date'));
                
                // Check if card matches search term
                const matchesSearch = searchTerm === '' || 
                    title.includes(searchTerm) || 
                    company.includes(searchTerm);
                
                // Check if card matches status filter
                const matchesStatus = statusValue === '' || status === statusValue;
                
                // Check if card matches date filter
                let matchesDate = true;
                if (dateValue !== '') {
                    const daysDiff = Math.floor((new Date() - date) / (1000 * 60 * 60 * 24));
                    matchesDate = daysDiff <= parseInt(dateValue);
                }
                
                // Show or hide card based on filters
                if (matchesSearch && matchesStatus && matchesDate) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        // Sort applications function
        function sortApplications(sortBy, sortOrder) {
            const cards = Array.from(document.querySelectorAll('.application-card'));
            
            // Debug: Check for duplicate cards
            const cardIds = cards.map(card => card.getAttribute('data-id'));
            const uniqueIds = [...new Set(cardIds)];
            if (cardIds.length !== uniqueIds.length) {
                console.warn('Duplicate application cards found in DOM:', cardIds.length - uniqueIds.length, 'duplicates');
                // Remove duplicates by keeping only the first occurrence of each ID
                const seenIds = new Set();
                const uniqueCards = cards.filter(card => {
                    const id = card.getAttribute('data-id');
                    if (seenIds.has(id)) {
                        card.remove(); // Remove duplicate
                        return false;
                    }
                    seenIds.add(id);
                    return true;
                });
                cards.length = 0;
                cards.push(...uniqueCards);
            }
            
            cards.sort((a, b) => {
                let aValue, bValue;
                
                switch (sortBy) {
                    case 'date':
                        aValue = new Date(a.getAttribute('data-date'));
                        bValue = new Date(b.getAttribute('data-date'));
                        break;
                    case 'status':
                        aValue = a.getAttribute('data-status');
                        bValue = b.getAttribute('data-status');
                        break;
                    case 'company':
                        aValue = a.getAttribute('data-company');
                        bValue = b.getAttribute('data-company');
                        break;
                    default:
                        return 0;
                }
                
                if (sortOrder === 'asc') {
                    return aValue > bValue ? 1 : -1;
                } else {
                    return aValue < bValue ? 1 : -1;
                }
            });
            
            // Reorder cards in the DOM
            cards.forEach(card => {
                applicationsContainer.appendChild(card);
            });
        }

        // Show application details function
        function showApplicationDetails(applicationId) {
            // applications is now an object/array from Blade
            // Ensure we handle numeric comparisons correctly
            const application = Object.values(applications).find(app => parseInt(app.id) === applicationId);
            
            if (!application) return;
            
            modalJobTitle.textContent = application.jobTitle;
            
            // Note: Template literals in JS here are fine since it's client-side rendering of modal content
            modalBody.innerHTML = `
                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="detail-label">Company</div>
                        <div class="detail-value">${application.company}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Location</div>
                        <div class="detail-value">${application.location}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Job Type</div>
                        <div class="detail-value">${application.jobType}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Salary Range</div>
                        <div class="detail-value">${application.salaryRange}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Application Date</div>
                        <div class="detail-value">${new Date(application.applicationDate).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Contact Person</div>
                        <div class="detail-value">${application.contactPerson}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Contact Email</div>
                        <div class="detail-value">${application.contactEmail}</div>
                    </div>
                </div>
                
                <div class="candidate-info">
                    <h3>Your Profile Information</h3>
                    <div class="detail-grid">
                        ${application.candidateEducation ? `
                        <div class="detail-item">
                            <div class="detail-label">Education</div>
                            <div class="detail-value">${application.candidateEducation}</div>
                        </div>
                        ` : ''}
                        ${application.candidateInstitute ? `
                        <div class="detail-item">
                            <div class="detail-label">Institute</div>
                            <div class="detail-value">${application.candidateInstitute}</div>
                        </div>
                        ` : ''}
                        ${application.candidateSkills ? `
                        <div class="detail-item">
                            <div class="detail-label">Skills</div>
                            <div class="detail-value">${application.candidateSkills}</div>
                        </div>
                        ` : ''}
                        ${application.candidateSummary ? `
                        <div class="detail-item full-width">
                            <div class="detail-label">Professional Summary</div>
                            <div class="detail-value">${application.candidateSummary}</div>
                        </div>
                        ` : ''}
                    </div>
                </div>
                
                ${application.coverLetter ? `
                <div class="cover-letter-section" style="margin-top: 20px; padding: 15px; background: var(--bg-tertiary); border-radius: 8px; border-left: 3px solid var(--accent);">
                    <h3 style="color: var(--accent); margin-bottom: 10px;"><i class="fas fa-file-alt" style="margin-right: 8px;"></i>Your Cover Letter</h3>
                    <p style="color: var(--text-secondary); line-height: 1.6; white-space: pre-wrap;">${application.coverLetter}</p>
                </div>
                ` : ''}
                
                ${application.notes ? `
                <div class="notes-section" style="margin-top: 15px; padding: 15px; background: var(--bg-tertiary); border-radius: 8px; border-left: 3px solid var(--accent-2);">
                    <h3 style="color: var(--accent-2); margin-bottom: 10px;"><i class="fas fa-sticky-note" style="margin-right: 8px;"></i>Additional Notes</h3>
                    <p style="color: var(--text-secondary); line-height: 1.6; white-space: pre-wrap;">${application.notes}</p>
                </div>
                ` : ''}
                
                ${application.jobDescription ? `
                <div class="job-description" style="margin-top: 20px;">
                    <h3 style="margin-bottom: 10px;">Job Description</h3>
                    <p style="color: var(--text-secondary); line-height: 1.6;">${application.jobDescription}</p>
                </div>
                ` : ''}
                
                ${application.requirements ? `
                <div class="requirements-section" style="margin-top: 15px;">
                    <h3 style="margin-bottom: 10px;">Requirements</h3>
                    <p style="color: var(--text-secondary); line-height: 1.6;">${application.requirements}</p>
                </div>
                ` : ''}
                
                <div class="status-history">
                    <h3 class="status-history-title">Application Progress</h3>
                    <div class="timeline">
                        ${application.statusFlow ? application.statusFlow.map(status => {
                            // Determine CSS class based on status
                            let timelineClass = 'timeline-item';
                            if (status.status_key === 'rejected') {
                                timelineClass += ' rejected-status';
                            } else if (status.completed) {
                                timelineClass += ' completed-status';
                            } else {
                                timelineClass += ' current-status';
                            }
                            
                            return `
                                <div class="${timelineClass}">
                                    <div class="timeline-content">
                                        <div class="timeline-status" style="color: ${getStatusColor(status.status)}; display: flex; align-items: center; gap: 8px;">
                                            ${status.completed ? '<i class="fas fa-check-circle" style="color: #3fb950;"></i>' : '<i class="fas fa-clock" style="color: #f59e0b;"></i>'}
                                            ${status.status}
                                        </div>
                                        <div class="timeline-date">${new Date(status.date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}</div>
                                        ${status.notes ? `<div class="timeline-notes" style="font-size: 12px; color: var(--text-secondary); margin-top: 4px;">${status.notes}</div>` : ''}
                                    </div>
                                </div>
                            `;
                        }).join('') : ''}
                    </div>
                </div>
                
                <div class="card-actions">
                    <button class="btn btn-primary" onclick="window.location.href='mailto:${application.contactEmail}?subject=Regarding my application for ${application.jobTitle}'">
                        <i class="fas fa-envelope"></i>
                        Contact Employer
                    </button>
                </div>
            `;
            
            detailsModal.style.display = 'flex';
        }


        // Show toast notification function
        function showToast(message, type = 'info') {
            const toastEl = document.getElementById('toast');
            const toastMessage = toastEl.querySelector('.toast-message');
            const toastIcon = toastEl.querySelector('.toast-icon');
            
            // Set message
            toastMessage.textContent = message;
            
            // Set type and icon
            toastEl.className = 'toast show';
            toastEl.classList.add(type);
            
            switch (type) {
                case 'success':
                    toastIcon.className = 'toast-icon fas fa-check-circle';
                    break;
                case 'error':
                    toastIcon.className = 'toast-icon fas fa-exclamation-circle';
                    break;
                case 'info':
                default:
                    toastIcon.className = 'toast-icon fas fa-info-circle';
                    break;
            }
            
            // Hide toast after 3 seconds
            setTimeout(() => {
                toastEl.classList.remove('show');
            }, 3000);
        }

        // Helper function to get status color (replicated from PHP for JS use)
        function getStatusColor(status) {
            // Map PHP status keys to colors or use existing switch
            // Ensure consistency with blade/php version
            const s = status.toLowerCase();
            switch (s) {
                case 'submitted':
                case 'application submitted':
                case 'applied':
                    return '#8b949e';
                case 'company_invited':
                case 'company invited':
                    return '#79c0ff';
                case 'exam_assigned':
                case 'mcq exam assigned':
                    return '#58a6ff';
                case 'exam_in_progress':
                case 'mcq exam in progress':
                    return '#f59e0b';
                case 'exam_passed':
                case 'mcq exam passed':
                    return '#3fb950';
                case 'waiting_interview':
                case 'waiting for interview call':
                    return '#f59e0b';
                case 'exam_failed':
                case 'mcq exam failed':
                case 'rejected':
                    return '#f85149';
                case 'interview_scheduled':
                case 'called for interview':
                case 'interview scheduled':
                    return '#f59e0b';
                case 'interview_in_progress':
                case 'interview in progress':
                    return '#f59e0b';
                case 'interview_completed':
                case 'interview completed':
                    return '#3fb950';
                case 'under-review':
                case 'under review':
                case 'in review':
                    return '#58a6ff';
                case 'shortlisted':
                    return '#79c0ff';
                case 'interviewed':
                    return '#f59e0b';
                case 'offer-extended':
                case 'offer extended':
                    return '#3fb950';
                case 'accepted':
                    return '#3fb950';
                case 'withdrawn':
                    return '#f85149';
                default:
                    return '#8b949e';
            }
        }

        // Initialize the page
        document.addEventListener('DOMContentLoaded', function() {
            // Set up initial sort
            sortApplications('date', 'desc');
        });

        // Theme Management
        function initializeTheme() {
            // Get saved theme or default to dark
            const savedTheme = localStorage.getItem('candihire-theme') || 'dark';
            applyTheme(savedTheme);
            updateThemeButton(savedTheme);
        }

        function applyTheme(theme) {
            document.documentElement.setAttribute('data-theme', theme);
            localStorage.setItem('candihire-theme', theme);
        }

        function toggleTheme() {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            applyTheme(newTheme);
            updateThemeButton(newTheme);
            
            // Add smooth transition effect
            document.body.style.transition = 'background-color 0.3s ease, color 0.3s ease';
            setTimeout(() => {
                document.body.style.transition = '';
            }, 300);
        }

        function updateThemeButton(theme) {
            const themeIcon = document.getElementById('themeIcon');
            const themeText = document.getElementById('themeText');
            const themeToggleBtn = document.getElementById('themeToggleBtn');
            
            // Add theme data attribute for CSS styling
            if (themeToggleBtn) {
                themeToggleBtn.setAttribute('data-theme', theme);
            }
            
            if (theme === 'dark') {
                themeIcon.className = 'fas fa-moon-stars';
                themeText.textContent = 'Light Mode';
                if (themeToggleBtn) {
                    themeToggleBtn.title = 'Switch to Light Mode';
                }
            } else {
                themeIcon.className = 'fas fa-moon-stars';
                themeText.textContent = 'Dark Mode';
                if (themeToggleBtn) {
                    themeToggleBtn.title = 'Switch to Dark Mode';
                }
            }
        }

        function setupThemeToggle() {
            const themeToggleBtn = document.getElementById('themeToggleBtn');
            if (themeToggleBtn) {
                themeToggleBtn.addEventListener('click', toggleTheme);
            }
        }


        
        // Auto-refresh functionality
        function setupAutoRefresh() {
            // Refresh data every 30 seconds
            setInterval(() => {
                checkForStatusUpdates();
                // Also check for duplicates during auto-refresh
                removeDuplicateCards();
            }, 30000);
        }
        
        // Check for status updates
        function checkForStatusUpdates() {
            // This would typically check for new notifications or status changes
            // For now, we'll just refresh the applications data silently
            // fetch('applicationstatus.php', {
            fetch('{{ url("api/candidate/applications/check-updates") }}', { 
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    action: 'check_updates'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.hasUpdates) {
                    showToast('Application status updated!', 'info');
                    // Optionally reload the page to show updates
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                }
            })
            .catch(error => {
                console.error('Error checking for updates:', error);
            });
        }

        // Check for and remove duplicate application cards
        function removeDuplicateCards() {
            const cards = document.querySelectorAll('.application-card');
            const seenIds = new Set();
            let duplicatesRemoved = 0;
            
            cards.forEach(card => {
                const id = card.getAttribute('data-id');
                if (seenIds.has(id)) {
                    console.log(`Removing duplicate card with ID: ${id}`);
                    card.remove();
                    duplicatesRemoved++;
                } else {
                    seenIds.add(id);
                }
            });
            
            if (duplicatesRemoved > 0) {
                console.log(`Removed ${duplicatesRemoved} duplicate application cards`);
                // Show a toast notification to user
                showToast(`Removed ${duplicatesRemoved} duplicate application(s)`, 'info');
            }
        }
        

        // Initialize theme when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            initializeTheme();
            setupThemeToggle();
            setupProfileEditing();
            setupAutoRefresh();
            
            // Check for duplicates after a short delay to ensure all content is loaded
            // Run duplicate removal multiple times to catch any dynamically added duplicates
            setTimeout(removeDuplicateCards, 100);
            setTimeout(removeDuplicateCards, 500);
            setTimeout(removeDuplicateCards, 1000);
        });
    </script>
</body>
</html>

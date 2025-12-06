@php
    function getStatusColor($status) {
        switch ($status) {
            case 'Scheduled':
                return '#f59e0b';
            case 'Completed':
                return '#3fb950';
            case 'Failed':
                return '#f85149';
            default:
                return '#8b949e';
        }
    }

    function getScoreColor($score, $passingScore) {
        // Remove % symbol if present
        $scoreValue = intval(str_replace('%', '', $score));
        $passingValue = intval(str_replace('%', '', $passingScore));
        
        return $scoreValue >= $passingValue ? '#3fb950' : '#f85149';
    }
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attend Exam - CandiHire</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/attendexam.css') }}">
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
                <button id="editProfileBtn" style="background: var(--accent); color: white; border: none; border-radius: 6px; padding: 8px 12px; font-size: 12px; cursor: pointer; margin-top: 10px; width: 100%; transition: background 0.2s;" onmouseover="this.style.background='var(--accent-hover)'" onmouseout="this.style.background='var(--accent)'">
                    <i class="fas fa-user-edit" style="margin-right: 6px;"></i>Edit Profile
                </button>
            </div>
            
            <!-- Main Menu Section -->
            <div class="nav-section">
                <div class="nav-section-title">Main menu</div>
                <a href="{{ route('candidate.dashboard') }}" class="nav-item">
                    <i class="fas fa-home"></i>
                    <span>News feed</span>
                </a>
                <a href="{{ url('candidate/cv-builder') }}" class="nav-item">
                    <i class="fas fa-file-alt"></i>
                    <span>CV builder</span>
                </a>
                <a href="{{ url('candidate/application-status') }}" class="nav-item">
                    <i class="fas fa-clipboard-list"></i>
                    <span>Application status</span>
                </a>
            </div>
            
            <!-- Interviews & Exams Section -->
            <div class="nav-section">
                <div class="nav-section-title">Interviews & Exams</div>
                <a href="{{ url('candidate/interview-schedule') }}" class="nav-item">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Interview schedule</span>
                </a>
                <a href="{{ url('candidate/attend-exam') }}" class="nav-item active">
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
                <form action="{{ route('candidate.logout') }}" method="POST" id="logout-form">
                    @csrf
                    <button type="submit" id="logoutBtn" class="logout-btn"><i class="fas fa-sign-out-alt" style="margin-right:8px;"></i>Logout</button>
                </form>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Page Header -->
            <div class="page-header">
                <h1 class="page-title">Attend Exam</h1>
                <div class="header-actions">
                    <button class="btn btn-primary" id="refreshBtn">
                        <i class="fas fa-sync-alt"></i>
                        Refresh
                    </button>
                </div>
            </div>

            <!-- Content Grid -->
            <div class="content-grid">
                <!-- Left Column - Scheduled Exams -->
                <div class="left-column">
                    <div class="content-section">
                        <div class="section-header">
                            <h2 class="section-title">Scheduled Exams <span class="exam-count">({{ count($scheduledExams) }})</span></h2>
                        </div>
                        <div class="section-body">
                            @if (isset($examLoadError))
                            <div class="error-message" style="text-align: center; padding: 40px 20px; color: var(--danger);">
                                <i class="fas fa-exclamation-triangle" style="font-size: 48px; margin-bottom: 20px; opacity: 0.7;"></i>
                                <h3 style="margin-bottom: 10px; color: var(--danger);">Error Loading Exams</h3>
                                <p>{{ $examLoadError }}</p>
                                <button class="btn btn-primary" onclick="window.location.reload()" style="margin-top: 15px;">
                                    <i class="fas fa-refresh"></i> Refresh Page
                                </button>
                            </div>
                            @elseif (empty($scheduledExams))
                            <div class="no-exams-message" style="text-align: center; padding: 40px 20px; color: var(--text-secondary);">
                                <i class="fas fa-calendar-times" style="font-size: 48px; margin-bottom: 20px; opacity: 0.5;"></i>
                                <h3 style="margin-bottom: 10px; color: var(--text-primary);">No Scheduled Exams</h3>
                                <p>You don't have any scheduled exams at the moment. Check back later or contact your recruiter for exam assignments.</p>
                            </div>
                            @else
                            @foreach ($scheduledExams as $exam)
                            <div class="exam-card" data-id="{{ $exam['id'] }}">
                                <h3 class="exam-title">{{ $exam['examTitle'] }}</h3>
                                <div class="exam-company">
                                    <i class="fas fa-building"></i>
                                    {{ $exam['company'] }} - {{ $exam['jobPosition'] }}
                                </div>
                                <div class="exam-details">
                                    <div class="exam-detail">
                                        <i class="fas fa-calendar"></i>
                                        <span class="exam-detail-label">Date:</span>
                                        <span>{{ date('F j, Y', strtotime($exam['examDate'])) }}</span>
                                    </div>
                                    <div class="exam-detail">
                                        <i class="fas fa-hourglass-half"></i>
                                        <span class="exam-detail-label">Duration:</span>
                                        <span>{{ $exam['duration'] }}</span>
                                    </div>
                                    <div class="exam-detail">
                                        <i class="fas fa-question-circle"></i>
                                        <span class="exam-detail-label">Questions:</span>
                                        <span>{{ $exam['questionCount'] }}</span>
                                    </div>
                                </div>
                                <div class="exam-status">
                                    <span class="status-badge" style="color: {{ getStatusColor($exam['status']) }}; border: 1px solid {{ getStatusColor($exam['status']) }};">
                                        <span class="status-dot"></span>
                                        {{ $exam['status'] }}
                                    </span>
                                    <span class="exam-detail">
                                        <span class="exam-detail-label">Passing Score:</span>
                                        <span>{{ $exam['passingScore'] }}</span>
                                    </span>
                                </div>
                                <div class="exam-actions">
                                    <button class="btn btn-primary btn-small start-exam-btn" data-id="{{ $exam['id'] }}">
                                        <i class="fas fa-play"></i>
                                        Start Exam
                                    </button>
                                    <button class="btn btn-secondary btn-small view-exam-btn" data-id="{{ $exam['id'] }}">
                                        <i class="fas fa-eye"></i>
                                        View Details
                                    </button>
                                </div>
                            </div>
                            @endforeach
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Right Column - Completed Exams -->
                <div class="right-column">
                    <div class="content-section">
                        <div class="section-header">
                            <h2 class="section-title">Completed Exams <span class="exam-count">({{ count($completedExams) }})</span></h2>
                        </div>
                        <div class="section-body">
                            @if (empty($completedExams))
                            <div class="no-exams-message" style="text-align: center; padding: 40px 20px; color: var(--text-secondary);">
                                <i class="fas fa-check-circle" style="font-size: 48px; margin-bottom: 20px; opacity: 0.5;"></i>
                                <h3 style="margin-bottom: 10px; color: var(--text-primary);">No Completed Exams</h3>
                                <p>You haven't completed any exams yet. Start by taking your scheduled exams!</p>
                            </div>
                            @else
                            @foreach ($completedExams as $exam)
                            <div class="exam-card" data-id="{{ $exam['id'] }}">
                                <h3 class="exam-title">{{ $exam['examTitle'] }}</h3>
                                <div class="exam-company">
                                    <i class="fas fa-building"></i>
                                    {{ $exam['company'] }} - {{ $exam['jobPosition'] }}
                                </div>
                                <div class="exam-details">
                                    <div class="exam-detail">
                                        <i class="fas fa-calendar"></i>
                                        <span class="exam-detail-label">Date:</span>
                                        <span>{{ date('F j, Y', strtotime($exam['examDate'])) }}</span>
                                    </div>
                                    <div class="exam-detail">
                                        <i class="fas fa-hourglass-half"></i>
                                        <span class="exam-detail-label">Duration:</span>
                                        <span>{{ $exam['duration'] }}</span>
                                    </div>
                                    <div class="exam-detail">
                                        <i class="fas fa-trophy"></i>
                                        <span class="exam-detail-label">Passing Score:</span>
                                        <span>{{ $exam['passingScore'] }}</span>
                                    </div>
                                </div>
                                <div class="exam-status">
                                    <span class="status-badge" style="color: {{ getStatusColor($exam['status']) }}; border: 1px solid {{ getStatusColor($exam['status']) }};">
                                        <span class="status-dot"></span>
                                        {{ $exam['status'] }}
                                    </span>
                                    <span class="score-badge" style="color: {{ getScoreColor($exam['score'], $exam['passingScore']) }};">
                                        Score: {{ isset($exam['score']) ? $exam['score'] : 'N/A' }}
                                    </span>
                                </div>
                                <div class="exam-actions">
                                    <button class="btn btn-secondary btn-small view-exam-btn" data-id="{{ $exam['id'] }}">
                                        <i class="fas fa-eye"></i>
                                        View Details
                                    </button>
                                </div>
                            </div>
                            @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Exam Details Modal -->
    <div class="modal" id="detailsModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="modalExamTitle">Exam Details</h2>
                <button class="close-btn" id="closeModalBtn">&times;</button>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Content will be dynamically inserted here -->
            </div>
        </div>
    </div>


    <!-- Confirmation Dialog -->
    <div class="confirmation-dialog" id="confirmationDialog">
        <div class="confirmation-content">
            <h3 class="confirmation-title" id="confirmationTitle">Confirm Action</h3>
            <p class="confirmation-message" id="confirmationMessage">Are you sure you want to proceed?</p>
            <div class="confirmation-actions">
                <button class="btn btn-secondary" id="cancelConfirmationBtn">Cancel</button>
                <button class="btn btn-primary" id="confirmActionBtn">Confirm</button>
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
            
            // const candidateId = @json($sessionCandidateId ?? '');
            // In Laravel, user is auth()->id() or stored in session, we use the route to get it
            
            fetch(`{{ url('api/candidate/profile') }}`)
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
                    showErrorMessage('Network error loading profile');
                });
        }

        // Update profile
        function updateProfile() {
            console.log('Updating profile...');
            
            const form = document.getElementById('profileEditForm');
            const submitBtn = document.getElementById('submitProfileUpdate');
            const formData = new FormData(form);
            
            // Disable button and show loading
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            
            // Add CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            fetch('{{ url("api/candidate/profile/update") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': csrfToken
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
        
        // Exam data
        const scheduledExams = @json($scheduledExams);
        const completedExams = @json($completedExams);
        
        // Debug: Log exam data
        console.log('Scheduled Exams:', scheduledExams);
        console.log('Completed Exams:', completedExams);
        console.log('Total Scheduled Exams:', scheduledExams.length);
        console.log('Total Completed Exams:', completedExams.length);
        
        // Debug: Check if buttons exist
        setTimeout(() => {
            const startButtons = document.querySelectorAll('.start-exam-btn');
            const viewButtons = document.querySelectorAll('.view-exam-btn');
            const examCards = document.querySelectorAll('.exam-card');
            
            console.log('Exam Cards found:', examCards.length);
            console.log('Start Exam Buttons found:', startButtons.length);
            console.log('View Details Buttons found:', viewButtons.length);
            
            startButtons.forEach((btn, index) => {
                console.log(`Start Button ${index}:`, btn, 'Visible:', btn.offsetParent !== null);
            });
        }, 1000);
        
        // Combine all exams for easier access
        const allExams = [...scheduledExams, ...completedExams];
        
        // DOM Elements
        const detailsModal = document.getElementById('detailsModal');
        const closeModalBtn = document.getElementById('closeModalBtn');
        const modalExamTitle = document.getElementById('modalExamTitle');
        const modalBody = document.getElementById('modalBody');
        const toast = document.getElementById('toast');
        
        // Confirmation Dialog Elements
        const confirmationDialog = document.getElementById('confirmationDialog');
        const confirmationTitle = document.getElementById('confirmationTitle');
        const confirmationMessage = document.getElementById('confirmationMessage');
        const cancelConfirmationBtn = document.getElementById('cancelConfirmationBtn');
        const confirmActionBtn = document.getElementById('confirmActionBtn');
        
        // Button Elements
        const refreshBtn = document.getElementById('refreshBtn');
        
        // Refresh Button
        refreshBtn.addEventListener('click', function() {
            // Add loading state to button
            const originalContent = refreshBtn.innerHTML;
            refreshBtn.innerHTML = '<span class="loading-indicator"></span> Refreshing...';
            refreshBtn.disabled = true;
            
            // Simulate refreshing the page data
            setTimeout(() => {
                refreshBtn.innerHTML = originalContent;
                refreshBtn.disabled = false;
                showToast('Exam list refreshed', 'success');
                
                // In a real application, this would fetch fresh data from the server or reload
                window.location.reload();
            }, 1500);
        });

        // Enhanced scrollbar functionality
        function initializeScrollbars() {
            const sectionBodies = document.querySelectorAll('.section-body');
            
            sectionBodies.forEach(section => {
                // Create scroll-to-top button
                const scrollToTopBtn = document.createElement('button');
                scrollToTopBtn.className = 'scroll-to-top';
                scrollToTopBtn.innerHTML = '<i class="fas fa-chevron-up"></i>';
                scrollToTopBtn.title = 'Scroll to top';
                section.appendChild(scrollToTopBtn);
                
                // Add scroll event listener
                section.addEventListener('scroll', function() {
                    const isScrolled = this.scrollTop > 0;
                    
                    // Show/hide scroll-to-top button
                    scrollToTopBtn.classList.toggle('visible', isScrolled);
                });
                
                // Scroll to top functionality
                scrollToTopBtn.addEventListener('click', function() {
                    section.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                });
            });
        }

        // Initialize scrollbars when page loads
        initializeScrollbars();
        
        
        // View exam details buttons
        document.addEventListener('click', function(e) {
            const viewBtn = e.target.closest('.view-exam-btn');
            if (viewBtn) {
                e.preventDefault();
                e.stopPropagation();
                const examId = parseInt(viewBtn.getAttribute('data-id'));
                if (!isNaN(examId)) {
                    showExamDetails(examId);
                } else {
                    console.error('Invalid exam ID for view button');
                }
            }
        });
        
        // Start exam buttons
        document.addEventListener('click', function(e) {
            const startBtn = e.target.closest('.start-exam-btn');
            if (startBtn) {
                e.preventDefault();
                e.stopPropagation();
                const examId = parseInt(startBtn.getAttribute('data-id'));
                if (!isNaN(examId)) {
                    confirmStartExam(examId);
                } else {
                    console.error('Invalid exam ID for start button');
                }
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
            if (e.target === confirmationDialog) {
                confirmationDialog.style.display = 'none';
            }
        });
        
        // Confirmation dialog functionality
        cancelConfirmationBtn.addEventListener('click', function() {
            confirmationDialog.style.display = 'none';
        });
        
        confirmActionBtn.addEventListener('click', function() {
            // Execute the confirmed action
            if (confirmActionBtn.getAttribute('data-action') === 'startExam') {
                const examId = parseInt(confirmActionBtn.getAttribute('data-exam-id'));
                startExam(examId);
            }
            
            confirmationDialog.style.display = 'none';
        });
        
        // Show exam details function
        function showExamDetails(examId) {
            const exam = allExams.find(exam => exam.id === examId);
            
            if (!exam) return;
            
            modalExamTitle.textContent = exam.examTitle;
            
            const isScheduled = scheduledExams.some(e => e.id === examId);
            
            modalBody.innerHTML = `
                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="detail-label">Exam Title</div>
                        <div class="detail-value">${exam.examTitle}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Company</div>
                        <div class="detail-value">${exam.company}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Position</div>
                        <div class="detail-value">${exam.jobPosition}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Exam Date</div>
                        <div class="detail-value">${new Date(exam.examDate).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Duration</div>
                        <div class="detail-value">${exam.duration}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Questions</div>
                        <div class="detail-value">${exam.questionCount}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Passing Score</div>
                        <div class="detail-value">${exam.passingScore}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Status</div>
                        <div class="detail-value">
                            <span class="status-badge" style="color: ${getStatusColor(exam.status)}; border: 1px solid ${getStatusColor(exam.status)};">
                                <span class="status-dot"></span>
                                ${exam.status}
                            </span>
                        </div>
                    </div>
                    ${exam.score ? `
                    <div class="detail-item">
                        <div class="detail-label">Your Score</div>
                        <div class="detail-value" style="color: ${getScoreColor(exam.score, exam.passingScore)};">
                            ${exam.score}
                        </div>
                    </div>
                    ` : ''}
                </div>
                
                ${exam.examInstructions ? `
                <div class="exam-instructions">
                    <h3>Exam Instructions</h3>
                    <p>${exam.examInstructions}</p>
                </div>
                ` : ''}
                
                ${exam.examResults ? `
                <div class="exam-results">
                    <h3>Exam Results</h3>
                    <p>${exam.examResults}</p>
                </div>
                ` : ''}
                
                <div class="card-actions">
                    ${isScheduled ? `
                    <button class="btn btn-primary start-exam-btn" data-id="${exam.id}">
                        <i class="fas fa-play"></i>
                        Start Exam
                    </button>
                    ` : `
                    <button class="btn btn-secondary" onclick="window.location.href='{{ url("candidate/exam-results") }}?exam_id=${exam.id}'">
                        <i class="fas fa-chart-bar"></i>
                        View Results
                    </button>
                    `}
                    <button class="btn btn-secondary" onclick="window.location.href='mailto:contact@${exam.company.toLowerCase().replace(' ', '')}.com?subject=Question about ${exam.examTitle}'">
                        <i class="fas fa-envelope"></i>
                        Contact Support
                    </button>
                </div>
            `;
            
            detailsModal.style.display = 'flex';
        }
        
        // Confirm start exam function
        function confirmStartExam(examId) {
            const exam = scheduledExams.find(exam => exam.id === examId);
            
            if (!exam) return;
            
            // Start exam directly without system check in this simplified version
            // In a real app, you might want to show a modal confirmation first
            startExam(examId);
        }
        
        // Start exam function
        function startExam(examId) {
            const exam = scheduledExams.find(exam => exam.id === examId);
            
            if (!exam) {
                console.error('Exam not found:', examId);
                showToast('Exam not found. Please refresh the page.', 'error');
                return;
            }
            
            // Check if assignment ID exists
            if (!exam.assignmentId) {
                console.error('Assignment ID missing for exam:', examId);
                showToast('Exam assignment information is missing. Please contact support.', 'error');
                return;
            }
            
            // Add loading state to button
            const startBtn = document.querySelector(`.start-exam-btn[data-id="${examId}"]`);
            if (startBtn) {
                const originalContent = startBtn.innerHTML;
                startBtn.innerHTML = '<span class="loading-indicator"></span> Starting...';
                startBtn.disabled = true;
            }
            
            // Close modal if open
            if (detailsModal) {
                detailsModal.style.display = 'none';
            }
            
            // Redirect to exam taking page
            try {
                // Assuming route for taking exam
                window.location.href = `{{ url('candidate/take-exam') }}?exam_id=${examId}&assignment_id=${exam.assignmentId}`;
            } catch (error) {
                console.error('Error redirecting to exam:', error);
                showToast('Unable to start exam. Please try again.', 'error');
                
                // Restore button state
                if (startBtn) {
                    startBtn.innerHTML = originalContent;
                    startBtn.disabled = false;
                }
            }
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
            switch (status) {
                case 'Scheduled':
                    return '#f59e0b';
                case 'Completed':
                    return '#3fb950';
                case 'Failed':
                    return '#f85149';
                default:
                    return '#8b949e';
            }
        }
        
        // Helper function to get score color (replicated from PHP for JS use)
        function getScoreColor(score, passingScore) {
            // Remove % symbol if present
            const scoreValue = parseInt(score.toString().replace('%', ''));
            const passingValue = parseInt(passingScore.toString().replace('%', ''));
            
            return scoreValue >= passingValue ? '#3fb950' : '#f85149';
        }

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
            
            // Initialize theme on load
            initializeTheme();
        }
        
        // Setup everything when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            setupThemeToggle();
            setupProfileEditing();
        });

    </script>
</body>
</html>

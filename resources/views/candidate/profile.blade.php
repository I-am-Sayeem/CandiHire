@php
    function getStatusColor($status) {
        $colors = [
            'Active' => '#3fb950',
            'Inactive' => '#f85149',
            'Pending' => '#dbab09'
        ];
        return $colors[$status] ?? '#8b949e';
    }
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candidate Profile - CandiHire</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/CandidateDashboard.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .profile-header {
            background-color: var(--bg-secondary);
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 24px;
            border: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 24px;
            flex-wrap: wrap;
        }
        
        .profile-avatar-large {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 4px solid var(--bg-tertiary);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            color: white;
            background: linear-gradient(135deg, var(--accent), var(--accent-hover));
        }
        
        .profile-info {
            flex: 1;
        }
        
        .profile-name {
            font-size: 28px;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 8px;
        }
        
        .profile-role {
            font-size: 16px;
            color: var(--accent);
            margin-bottom: 12px;
            font-weight: 500;
        }
        
        .profile-location {
            color: var(--text-secondary);
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 16px;
        }
        
        .profile-stats {
            display: flex;
            gap: 24px;
            margin-top: 10px;
        }
        
        .stat-item {
            display: flex;
            flex-direction: column;
        }
        
        .stat-value {
            font-size: 18px;
            font-weight: 700;
            color: var(--text-primary);
        }
        
        .stat-label {
            font-size: 12px;
            color: var(--text-secondary);
            text-transform: uppercase;
        }
        
        .profile-actions {
            display: flex;
            gap: 12px;
            align-self: flex-start;
        }
        
        .section-card {
            background-color: var(--bg-secondary);
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
            border: 1px solid var(--border);
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border);
        }
        
        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .skills-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .skill-tag {
            background-color: var(--bg-tertiary);
            color: var(--text-primary);
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 14px;
            border: 1px solid var(--border);
            transition: all 0.2s;
        }
        
        .skill-tag:hover {
            border-color: var(--accent);
            color: var(--accent);
            transform: translateY(-2px);
        }
        
        .detail-row {
            display: flex;
            margin-bottom: 16px;
            border-bottom: 1px solid var(--border);
            padding-bottom: 16px;
        }
        
        .detail-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .detail-label {
            width: 150px;
            color: var(--text-secondary);
            font-size: 14px;
            flex-shrink: 0;
        }
        
        .detail-value {
            color: var(--text-primary);
            font-size: 14px;
            flex: 1;
        }
        
        .detail-value a {
            color: var(--accent);
            text-decoration: none;
        }
        
        .detail-value a:hover {
            text-decoration: underline;
        }
        
        .bio-text {
            color: var(--text-secondary);
            line-height: 1.6;
            font-size: 15px;
        }
    </style>
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
                    <div id="candidateAvatar" style="width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 16px; {{ $candidate['ProfilePicture'] ? 'background-image: url(' . $candidate['ProfilePicture'] . '); background-size: cover; background-position: center;' : 'background: linear-gradient(135deg, var(--accent), var(--accent-2));' }}">
                        {{ $candidate['ProfilePicture'] ? '' : strtoupper(substr($candidate['FullName'], 0, 1)) }}
                    </div>
                    <div>
                        <div style="color: var(--text-primary); font-weight: 600; font-size: 14px;">Welcome back!</div>
                        <div id="candidateNameDisplay" style="color: var(--text-secondary); font-size: 12px;">{{ $candidate['FullName'] }}</div>
                    </div>
                </div>
            </div>
            
            <!-- Main Menu Section -->
            <div class="nav-section">
                <div class="nav-section-title">Main menu</div>
                <a href="{{ route('candidate.dashboard') }}" class="nav-item">
                    <i class="fas fa-home"></i>
                    <span>News feed</span>
                </a>
                <a href="{{ url('candidate/profile') }}" class="nav-item active">
                    <i class="fas fa-user-circle"></i>
                    <span>My Profile</span>
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
                <a href="{{ url('candidate/attend-exam') }}" class="nav-item">
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
        <div class="main-content" style="max-width: 900px;">
            
            <!-- Profile Header -->
            <div class="profile-header">
                <div class="profile-avatar-large" style="{{ $candidate['ProfilePicture'] ? 'background-image: url(' . $candidate['ProfilePicture'] . ');' : '' }}">
                    {{ $candidate['ProfilePicture'] ? '' : strtoupper(substr($candidate['FullName'], 0, 1)) }}
                </div>
                <div class="profile-info">
                    <div class="profile-name">{{ $candidate['FullName'] }}</div>
                    <div class="profile-role">
                        {{ $candidate['WorkType'] ? ucfirst($candidate['WorkType']) : 'Job Seeker' }}
                        @if($candidate['YearsOfExperience'])
                         • {{ $candidate['YearsOfExperience'] }} Years Exp.
                        @endif
                    </div>
                    <div class="profile-location">
                        <i class="fas fa-map-marker-alt"></i>
                        {{ $candidate['Location'] ?? 'Location not set' }}
                    </div>
                    
                    <div class="profile-stats">
                        <div class="stat-item">
                            <span class="stat-value">{{ explode(',', $candidate['Skills'] ?? '') ? count(array_filter(explode(',', $candidate['Skills'] ?? ''))) : 0 }}</span>
                            <span class="stat-label">Skills</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-value">{{ $candidate['Portfolio'] ? 1 : 0 }}</span>
                            <span class="stat-label">Portfolio</span>
                        </div>
                    </div>
                </div>
                <div class="profile-actions">
                    <button class="btn btn-primary" id="editProfileBtn">
                        <i class="fas fa-edit"></i> Edit Profile
                    </button>
                </div>
            </div>
            
            <div class="content-grid" style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
                <!-- Left Column -->
                <div class="profile-left">
                    <!-- About Section -->
                    <div class="section-card">
                        <div class="section-header">
                            <div class="section-title">
                                <i class="fas fa-user"></i> About Me
                            </div>
                        </div>
                        <div class="section-body bio-text">
                            {{ $candidate['Summary'] ?? 'No professional summary added yet. Click "Edit Profile" to add your summary.' }}
                        </div>
                    </div>
                    
                    <!-- Education Section -->
                    <div class="section-card">
                        <div class="section-header">
                            <div class="section-title">
                                <i class="fas fa-graduation-cap"></i> Education
                            </div>
                        </div>
                        <div class="section-body">
                            @if($candidate['Education'] || $candidate['Institute'])
                            <div class="detail-row">
                                <div class="detail-label">Degree</div>
                                <div class="detail-value">{{ $candidate['Education'] ?? 'Not specified' }}</div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label">Institute</div>
                                <div class="detail-value">{{ $candidate['Institute'] ?? 'Not specified' }}</div>
                            </div>
                            @else
                                <p class="text-secondary">No education details added.</p>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Skills Section -->
                    <div class="section-card">
                        <div class="section-header">
                            <div class="section-title">
                                <i class="fas fa-tools"></i> Skills
                            </div>
                        </div>
                        <div class="section-body">
                            <div class="skills-grid">
                                @if(!empty($candidate['Skills']))
                                    @foreach(explode(',', $candidate['Skills']) as $skill)
                                        @if(trim($skill))
                                            <span class="skill-tag">{{ trim($skill) }}</span>
                                        @endif
                                    @endforeach
                                @else
                                    <p class="text-secondary">No skills listed.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Right Column -->
                <div class="profile-right">
                    <!-- Contact Info -->
                    <div class="section-card">
                        <div class="section-header">
                            <div class="section-title">
                                <i class="fas fa-address-card"></i> Contact info
                            </div>
                        </div>
                        <div class="section-body">
                            <div class="detail-row">
                                <div class="detail-label"><i class="fas fa-envelope"></i> Email</div>
                                <div class="detail-value" style="word-break: break-all;">{{ $candidate['Email'] }}</div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label"><i class="fas fa-phone"></i> Phone</div>
                                <div class="detail-value">{{ $candidate['PhoneNumber'] ?? 'Not set' }}</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Social Links -->
                    <div class="section-card">
                        <div class="section-header">
                            <div class="section-title">
                                <i class="fas fa-share-alt"></i> Web Presence
                            </div>
                        </div>
                        <div class="section-body">
                            @if($candidate['LinkedIn'])
                            <div class="detail-row">
                                <div class="detail-label"><i class="fab fa-linkedin"></i> LinkedIn</div>
                                <div class="detail-value"><a href="{{ $candidate['LinkedIn'] }}" target="_blank">View Profile</a></div>
                            </div>
                            @endif
                            
                            @if($candidate['GitHub'])
                            <div class="detail-row">
                                <div class="detail-label"><i class="fab fa-github"></i> GitHub</div>
                                <div class="detail-value"><a href="{{ $candidate['GitHub'] }}" target="_blank">View Profile</a></div>
                            </div>
                            @endif
                            
                            @if($candidate['Portfolio'])
                            <div class="detail-row">
                                <div class="detail-label"><i class="fas fa-globe"></i> Portfolio</div>
                                <div class="detail-value"><a href="{{ $candidate['Portfolio'] }}" target="_blank">Visit Site</a></div>
                            </div>
                            @endif
                            
                            @if(!$candidate['LinkedIn'] && !$candidate['GitHub'] && !$candidate['Portfolio'])
                                <p class="text-secondary">No social links added.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>

    <!-- Notification Toast -->
    <div class="toast" id="toast" style="position: fixed; bottom: 20px; right: 20px; background: var(--bg-secondary); border: 1px solid var(--border); padding: 15px 25px; border-radius: 8px; box-shadow: 0 5px 15px rgba(0,0,0,0.2); transform: translateY(100px); opacity: 0; transition: all 0.3s ease; z-index: 10001;">
        <i class="toast-icon fas" style="margin-right: 10px;"></i>
        <span class="toast-message" style="color: var(--text-primary);"></span>
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
                </div>
                
                <div class="form-group">
                    <label for="fullName">Full Name *</label>
                    <input type="text" id="fullName" name="fullName" value="{{ $candidate['FullName'] }}" required>
                </div>
                
                <div class="form-group">
                    <label for="phoneNumber">Phone Number *</label>
                    <input type="tel" id="phoneNumber" name="phoneNumber" value="{{ $candidate['PhoneNumber'] }}" required>
                </div>
                
                <div class="form-group">
                    <label for="workType">Work Type *</label>
                    <select id="workType" name="workType" required>
                        <option value="">Select Work Type</option>
                        <option value="full-time" {{ ($candidate['WorkType'] ?? '') == 'full-time' ? 'selected' : '' }}>Full-time</option>
                        <option value="part-time" {{ ($candidate['WorkType'] ?? '') == 'part-time' ? 'selected' : '' }}>Part-time</option>
                        <option value="contract" {{ ($candidate['WorkType'] ?? '') == 'contract' ? 'selected' : '' }}>Contract</option>
                        <option value="freelance" {{ ($candidate['WorkType'] ?? '') == 'freelance' ? 'selected' : '' }}>Freelance</option>
                        <option value="internship" {{ ($candidate['WorkType'] ?? '') == 'internship' ? 'selected' : '' }}>Internship</option>
                        <option value="fresher" {{ ($candidate['WorkType'] ?? '') == 'fresher' ? 'selected' : '' }}>Fresher</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="yearsOfExperience">Years of Experience</label>
                    <input type="number" id="yearsOfExperience" name="yearsOfExperience" 
                           value="{{ $candidate['YearsOfExperience'] ?? 0 }}"
                           min="0" max="50">
                </div>
                
                <div class="form-group">
                    <label for="location">Location</label>
                    <input type="text" id="location" name="location" value="{{ $candidate['Location'] ?? '' }}" placeholder="City, Country">
                </div>
                
                <div class="form-group">
                    <label for="skills">Skills</label>
                    <textarea id="skills" name="skills" placeholder="List your key skills separated by commas">{{ $candidate['Skills'] ?? '' }}</textarea>
                </div>
                
                <div class="form-group">
                    <label for="summary">Professional Summary</label>
                    <textarea id="summary" name="summary" placeholder="Brief description about yourself">{{ $candidate['Summary'] ?? '' }}</textarea>
                </div>
                
                <div class="form-group">
                    <label for="linkedin">LinkedIn Profile</label>
                    <input type="url" id="linkedin" name="linkedin" value="{{ $candidate['LinkedIn'] ?? '' }}" placeholder="https://linkedin.com/in/yourprofile">
                </div>
                
                <div class="form-group">
                    <label for="github">GitHub Profile</label>
                    <input type="url" id="github" name="github" value="{{ $candidate['GitHub'] ?? '' }}" placeholder="https://github.com/yourusername">
                </div>
                
                <div class="form-group">
                    <label for="portfolio">Portfolio Website</label>
                    <input type="url" id="portfolio" name="portfolio" value="{{ $candidate['Portfolio'] ?? '' }}" placeholder="https://yourportfolio.com">
                </div>
                
                <div class="form-group">
                    <label for="education">Education/Degree</label>
                    <input type="text" id="education" name="education" value="{{ $candidate['Education'] ?? '' }}" placeholder="e.g., Bachelor's in Computer Science">
                </div>
                
                <div class="form-group">
                    <label for="institute">Institute/University</label>
                    <input type="text" id="institute" name="institute" value="{{ $candidate['Institute'] ?? '' }}" placeholder="e.g., MIT, Stanford University">
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeProfileEditPopup()">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary" id="submitProfileUpdate">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Theme Management
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
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            applyTheme(newTheme);
            updateThemeButton(newTheme);
        }

        function updateThemeButton(theme) {
            const themeIcon = document.getElementById('themeIcon');
            const themeText = document.getElementById('themeText');
            
            if (theme === 'dark') {
                themeIcon.className = 'fas fa-moon-stars';
                themeText.textContent = 'Light Mode';
            } else {
                themeIcon.className = 'fas fa-moon-stars';
                themeText.textContent = 'Dark Mode';
            }
        }

        const themeToggleBtn = document.getElementById('themeToggleBtn');
        if (themeToggleBtn) {
            themeToggleBtn.addEventListener('click', toggleTheme);
        }
        
        initializeTheme();

        // Profile Editing Logic
        const editProfileBtn = document.getElementById('editProfileBtn');
        const profilePopup = document.getElementById('profileEditPopup');
        const profileForm = document.getElementById('profileEditForm');
        
        if (editProfileBtn) {
            editProfileBtn.addEventListener('click', function() {
                profilePopup.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            });
        }
        
        function closeProfileEditPopup() {
            profilePopup.style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        // Close when clicking outside
        window.addEventListener('click', function(e) {
            if (e.target === profilePopup) {
                closeProfileEditPopup();
            }
        });

        // Form Submission
        if (profileForm) {
            profileForm.addEventListener('submit', function(e) {
                e.preventDefault();
                updateProfile();
            });
        }

        function updateProfile() {
            const submitBtn = document.getElementById('submitProfileUpdate');
            const formData = new FormData(profileForm);
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            
            // Add CSRF token header
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
                    showToast('Profile updated successfully!', 'success');
                    // Reload to show changes
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    showToast(data.message || 'Failed to update profile', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Network error. Please try again.', 'error');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-save"></i> Save Changes';
            });
        }
        
        // Toast Notification
        function showToast(message, type = 'info') {
            const toast = document.getElementById('toast');
            const messageEl = toast.querySelector('.toast-message');
            const iconEl = toast.querySelector('.toast-icon');
            
            messageEl.textContent = message;
            toast.className = 'toast show'; // reset class
            
            if (type === 'success') {
                iconEl.className = 'toast-icon fas fa-check-circle';
                iconEl.style.color = 'var(--success)';
            } else if (type === 'error') {
                iconEl.className = 'toast-icon fas fa-exclamation-circle';
                iconEl.style.color = 'var(--danger)';
            } else {
                iconEl.className = 'toast-icon fas fa-info-circle';
                iconEl.style.color = 'var(--accent)';
            }
            
            toast.style.transform = 'translateY(0)';
            toast.style.opacity = '1';
            
            setTimeout(() => {
                toast.style.transform = 'translateY(100px)';
                toast.style.opacity = '0';
            }, 3000);
        }
    </script>
</body>
</html>

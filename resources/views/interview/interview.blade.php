<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CandiHire - Interview Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/Interview.css') }}">
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
                <a href="{{ url('/company/interviews') }}" class="nav-item active">
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

        <div class="main-content">
            <div class="page-header">
                <h1 class="page-title">Interview Management</h1>
                <p class="page-subtitle">Schedule and conduct interviews with candidates</p>
            </div>

            <div class="interview-controls">
                <div class="controls-header">
                    <h2 class="controls-title">Schedule New Interview</h2>
                </div>

                <div class="form-section">
                    <label class="form-label" for="positionSelect">Select Job Position</label>
                    <select id="positionSelect" class="form-select">
                        <option value="">-- Select a Position --</option>
                        <!-- Options will be populated via JS -->
                    </select>
                </div>

                <div id="candidateListSection" class="form-section" style="display: none;">
                    <label class="form-label">Select Candidates</label>
                    <div class="candidate-list-header">
                        <div class="candidate-list-actions">
                            <button id="selectAllBtn" class="btn btn-secondary btn-small">
                                <i class="fas fa-check-square"></i> Select All
                            </button>
                            <button id="deselectAllBtn" class="btn btn-secondary btn-small" style="display: none;">
                                <i class="far fa-square"></i> Deselect All
                            </button>
                        </div>
                    </div>
                    <div id="candidateList" class="candidate-list">
                        <!-- Candidates will be listed here -->
                    </div>
                </div>

                <div id="datetimeSection" class="form-section" style="display: none;">
                     <div class="datetime-row">
                        <div class="datetime-group">
                            <label class="form-label" for="interviewDate">Date</label>
                            <input type="date" id="interviewDate" class="form-input">
                        </div>
                        <div class="datetime-group">
                            <label class="form-label" for="interviewTime">Time</label>
                            <input type="time" id="interviewTime" class="form-input">
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <label class="form-label" for="interviewMethod">Interview Method</label>
                    <select id="interviewMethod" class="form-select">
                        <option value="">-- Select Method --</option>
                        <option value="virtual">Virtual (Online)</option>
                        <option value="onsite">On-site (In Person)</option>
                    </select>
                </div>

                <div id="meetingLinkSection" class="form-section" style="display: none;">
                    <label class="form-label" for="meetingLink">Meeting Link</label>
                    <input type="url" id="meetingLink" class="form-input" placeholder="e.g. https://meet.google.com/abc-defg-hij">
                </div>

                <div id="locationSection" class="form-section" style="display: none;">
                    <label class="form-label" for="interviewLocation">Interview Location</label>
                    <input type="text" id="interviewLocation" class="form-input" placeholder="e.g. Conference Room A, 123 Business St">
                </div>

                <div id="scheduleInterviewSection" class="interview-actions" style="display: none;">
                    <button id="scheduleInterviewBtn" class="btn btn-primary">
                        <i class="fas fa-calendar-check"></i> Schedule Interview
                    </button>
                </div>
            </div>

            <!-- Scheduled Interviews Section -->
            <div class="scheduled-interviews" style="margin-top: 24px;">
                <div class="controls-header" style="background-color: var(--bg-secondary); border-radius: 12px 12px 0 0; padding: 20px 24px; border: 1px solid var(--border); border-bottom: none;">
                    <h2 class="controls-title"><i class="fas fa-calendar-alt" style="margin-right: 8px;"></i>Scheduled Interviews</h2>
                    <span style="color: var(--text-secondary); font-size: 14px;">{{ count($interviews) }} interview(s)</span>
                </div>
                
                @if(count($interviews) > 0)
                <div class="interviews-list" style="background-color: var(--bg-secondary); border: 1px solid var(--border); border-radius: 0 0 12px 12px; padding: 16px;">
                    @foreach($interviews as $interview)
                    <div class="interview-card" style="background-color: var(--bg-primary); border: 1px solid var(--border); border-radius: 8px; padding: 16px; margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center; transition: all 0.2s;">
                        <div style="display: flex; align-items: center; gap: 16px;">
                            <div style="width: 48px; height: 48px; border-radius: 50%; background: linear-gradient(135deg, var(--accent-1), var(--accent-2)); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 16px;">
                                {{ strtoupper(substr($interview['CandidateName'], 0, 2)) }}
                            </div>
                            <div>
                                <h4 style="font-size: 16px; font-weight: 600; margin-bottom: 4px;">{{ $interview['CandidateName'] }}</h4>
                                <p style="color: var(--text-secondary); font-size: 14px; margin-bottom: 2px;">{{ $interview['JobTitle'] }}</p>
                                <p style="color: var(--text-secondary); font-size: 12px;">
                                    <i class="fas fa-envelope" style="margin-right: 4px;"></i>{{ $interview['CandidateEmail'] }}
                                </p>
                            </div>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-size: 14px; font-weight: 500; color: var(--text-primary); margin-bottom: 4px;">
                                <i class="fas fa-calendar" style="margin-right: 4px; color: var(--accent-1);"></i>
                                {{ \Carbon\Carbon::parse($interview['ScheduledDate'])->format('M d, Y') }}
                            </div>
                            <div style="font-size: 14px; color: var(--text-secondary); margin-bottom: 4px;">
                                <i class="fas fa-clock" style="margin-right: 4px;"></i>
                                {{ \Carbon\Carbon::parse($interview['ScheduledTime'])->format('h:i A') }}
                            </div>
                            <div style="display: flex; align-items: center; gap: 8px; justify-content: flex-end;">
                                @if($interview['InterviewMode'] === 'virtual')
                                    <span style="background-color: rgba(88, 166, 255, 0.2); color: var(--accent-1); padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                                        <i class="fas fa-video"></i> Virtual
                                    </span>
                                    @if($interview['MeetingLink'])
                                    <a href="{{ $interview['MeetingLink'] }}" target="_blank" style="color: var(--accent-1); font-size: 12px; text-decoration: none;">
                                        <i class="fas fa-external-link-alt"></i> Join
                                    </a>
                                    @endif
                                @else
                                    <span style="background-color: rgba(245, 158, 11, 0.2); color: var(--warning); padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                                        <i class="fas fa-building"></i> On-site
                                    </span>
                                @endif
                                <span style="background-color: {{ $interview['Status'] === 'Scheduled' ? 'rgba(63, 185, 80, 0.2)' : 'rgba(139, 148, 158, 0.2)' }}; color: {{ $interview['Status'] === 'Scheduled' ? 'var(--success)' : 'var(--text-secondary)' }}; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                                    {{ $interview['Status'] }}
                                </span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div style="background-color: var(--bg-secondary); border: 1px solid var(--border); border-radius: 0 0 12px 12px; padding: 40px; text-align: center;">
                    <i class="fas fa-calendar-times" style="font-size: 48px; color: var(--text-secondary); margin-bottom: 16px;"></i>
                    <p style="color: var(--text-secondary); font-size: 16px;">No interviews scheduled yet.</p>
                    <p style="color: var(--text-secondary); font-size: 14px;">Select a job position and candidates above to schedule interviews.</p>
                </div>
                @endif
            </div>
            
            <div id="interviewInterface" class="interview-interface">
                <div class="interview-header">
                    <div class="candidate-info">
                        <div class="candidate-avatar" id="activeCandidateAvatar">JD</div>
                        <div class="candidate-details">
                            <h3 id="activeCandidateName">John Doe</h3>
                            <p id="activeCandidatePosition">Software Engineer</p>
                        </div>
                    </div>
                    <div class="interview-timer">
                        <i class="fas fa-clock"></i> <span id="timerDisplay">00:00:00</span>
                    </div>
                </div>

                <div class="interview-content">
                    <div class="interview-main">
                        <div class="question-section">
                            <div class="question-header">
                                <span class="question-number">Question 1</span>
                                <span class="question-timer">2:00</span>
                            </div>
                            <div class="question-text">
                                Tell me about a challenging project you worked on and how you overcame technical obstacles.
                            </div>
                        </div>

                        <div class="answer-section">
                            <label class="answer-label">Candidate's Answer / Notes</label>
                            <textarea class="answer-textarea" placeholder="Take notes on the candidate's answer here..."></textarea>
                        </div>

                        <div class="rating-section">
                            <label class="rating-label">Rating</label>
                            <div class="rating-scale">
                                <div class="rating-option" data-rating="1">
                                    <div class="rating-circle">1</div>
                                    <span class="rating-text">Poor</span>
                                </div>
                                <div class="rating-option" data-rating="2">
                                    <div class="rating-circle">2</div>
                                    <span class="rating-text">Fair</span>
                                </div>
                                <div class="rating-option" data-rating="3">
                                    <div class="rating-circle">3</div>
                                    <span class="rating-text">Good</span>
                                </div>
                                <div class="rating-option" data-rating="4">
                                    <div class="rating-circle">4</div>
                                    <span class="rating-text">Very Good</span>
                                </div>
                                <div class="rating-option" data-rating="5">
                                    <div class="rating-circle">5</div>
                                    <span class="rating-text">Excellent</span>
                                </div>
                            </div>
                        </div>

                        <div class="interview-actions">
                            <button class="btn btn-secondary">Previous</button>
                            <button class="btn btn-primary">Next Question</button>
                        </div>
                    </div>

                    <div class="interview-sidebar">
                        <div class="sidebar-section">
                            <h4 class="sidebar-title">Questions</h4>
                            <div class="question-list">
                                <div class="question-item active">
                                    <span class="question-status"></span>
                                    <span>Introduction</span>
                                </div>
                                <div class="question-item">
                                    <span class="question-status"></span>
                                    <span>Technical Experience</span>
                                </div>
                                <div class="question-item">
                                    <span class="question-status"></span>
                                    <span>Problem Solving</span>
                                </div>
                                <div class="question-item">
                                    <span class="question-status"></span>
                                    <span>Culture Fit</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden form for company profile edit if needed, based on PHP legacy code, but simplified for conversion focus on Interview features -->

    <script>
        // Global variables state
        let currentCompanyId = "{{ session('company_id') }}"; // Assuming Laravel session
        let companyJobPositions = [];
        let selectedPosition = null;
        let selectedCandidates = [];
        let interviewMethod = '';
        let meetingLink = '';
        let interviewLocation = '';
        let interviewDate = '';
        let interviewTime = '';

        document.addEventListener('DOMContentLoaded', function() {
            initializeEventListeners();
            setMinDate();
            initializeTheme();
            setupThemeToggle();
            loadCompanyJobPositions();
        });

        function initializeEventListeners() {
            // Interview method selection
            document.getElementById('interviewMethod').addEventListener('change', handleInterviewMethodChange);
            
            // Position selection
            document.getElementById('positionSelect').addEventListener('change', handlePositionSelection);
            
            // Meeting link input
            document.getElementById('meetingLink').addEventListener('input', handleMeetingLinkChange);
            
            // Interview location input
            document.getElementById('interviewLocation').addEventListener('input', handleLocationChange);
            
            // Date and time inputs
            document.getElementById('interviewDate').addEventListener('change', handleDateChange);
            document.getElementById('interviewTime').addEventListener('change', handleTimeChange);
            
            // Interview scheduling actions
            document.getElementById('scheduleInterviewBtn').addEventListener('click', scheduleInterview);
            
            // Select All/Deselect All buttons
            document.getElementById('selectAllBtn').addEventListener('click', selectAllCandidates);
            document.getElementById('deselectAllBtn').addEventListener('click', deselectAllCandidates);
        }

        function setMinDate() {
            const today = new Date();
            const tomorrow = new Date(today);
            tomorrow.setDate(tomorrow.getDate() + 1);
            const minDate = tomorrow.toISOString().split('T')[0];
            document.getElementById('interviewDate').min = minDate;
        }

        // Load job positions from API
        async function loadCompanyJobPositions() {
            try {
                console.log('Loading job positions...');
                const response = await fetch('/api/company/job-positions');
                const data = await response.json();
                
                if (data.success) {
                    companyJobPositions = data.jobs;
                    const select = document.getElementById('positionSelect');
                    
                    // Clear existing options except default
                    while (select.options.length > 1) {
                        select.remove(1);
                    }

                    data.jobs.forEach(job => {
                        const option = document.createElement('option');
                        option.value = job.JobID;
                        option.textContent = job.JobTitle;
                        select.appendChild(option);
                    });
                    
                    if (data.jobs.length === 0) {
                        select.innerHTML = '<option value="">-- No Active Job Positions --</option>';
                    }
                } else {
                    console.error('Failed to load job positions:', data.message);
                }
            } catch (error) {
                console.error('Error loading job positions:', error);
            }
        }

        function handleInterviewMethodChange(event) {
            interviewMethod = event.target.value;
            const meetingLinkSection = document.getElementById('meetingLinkSection');
            const locationSection = document.getElementById('locationSection');
            
            if (interviewMethod === 'virtual') {
                meetingLinkSection.style.display = 'block';
                locationSection.style.display = 'none';
                interviewLocation = ''; 
                document.getElementById('interviewLocation').value = '';
            } else if (interviewMethod === 'onsite') {
                meetingLinkSection.style.display = 'none';
                locationSection.style.display = 'block';
                meetingLink = ''; 
                document.getElementById('meetingLink').value = '';
            } else {
                meetingLinkSection.style.display = 'none';
                locationSection.style.display = 'none';
                meetingLink = '';
                interviewLocation = '';
                document.getElementById('meetingLink').value = '';
                document.getElementById('interviewLocation').value = '';
            }
            
            updateScheduleInterviewButton();
        }

        function handlePositionSelection(event) {
            selectedPosition = event.target.value;
            const candidateListSection = document.getElementById('candidateListSection');
            
            if (selectedPosition) {
                loadCandidatesForJob(selectedPosition);
                candidateListSection.style.display = 'block';
                document.getElementById('datetimeSection').style.display = 'block';
            } else {
                candidateListSection.style.display = 'none';
                document.getElementById('datetimeSection').style.display = 'none';
                document.getElementById('scheduleInterviewSection').style.display = 'none';
            }
            
            updateScheduleInterviewButton();
        }

        async function loadCandidatesForJob(jobId) {
            const candidateList = document.getElementById('candidateList');
            candidateList.innerHTML = '<div style="text-align: center; padding: 20px;"><i class="fas fa-spinner fa-spin"></i> Loading candidates...</div>';
            
            try {
                const response = await fetch(`/api/company/candidates/${jobId}`);
                const data = await response.json();

                candidateList.innerHTML = '';
                if (data.success && data.candidates.length > 0) {
                    data.candidates.forEach(candidate => {
                        const item = createCandidateItem(candidate);
                        candidateList.appendChild(item);
                    });
                } else {
                    candidateList.innerHTML = '<div style="text-align: center; padding: 20px; color: var(--text-secondary);"><i class="fas fa-user-slash" style="font-size: 24px; margin-bottom: 10px;"></i><br>No candidates have applied to this position yet.</div>';
                }
                updateSelectAllButtons();
            } catch (error) {
                console.error('Error loading candidates:', error);
                candidateList.innerHTML = '<div style="text-align: center; padding: 20px; color: var(--danger);">Error loading candidates. Please try again.</div>';
            }
        }

        function createCandidateItem(candidate) {
            const item = document.createElement('div');
            item.className = 'candidate-item';
            const avatar = candidate.name ? candidate.name.split(' ').map(n => n[0]).join('').toUpperCase() : 'C';
            
            item.innerHTML = `
                <div class="candidate-info">
                    <div class="candidate-avatar-small">${avatar}</div>
                    <div class="candidate-details">
                        <h4>${candidate.name}</h4>
                        <p>${candidate.email}</p>
                        <p style="font-size: 12px; color: var(--text-secondary);">Score: <span style="color: var(--success);">${candidate.examScore}%</span></p>
                    </div>
                </div>
                <input type="checkbox" class="candidate-checkbox" 
                       data-candidate-id="${candidate.candidateId}" 
                       data-candidate-email="${candidate.email}" 
                       data-candidate-name="${candidate.name}">
            `;
            
            const checkbox = item.querySelector('.candidate-checkbox');
            checkbox.addEventListener('change', handleCandidateSelection);
            
            return item;
        }

        function handleCandidateSelection(event) {
            const id = event.target.dataset.candidateId;
            const email = event.target.dataset.candidateEmail;
            const name = event.target.dataset.candidateName;
            
            if (event.target.checked) {
                selectedCandidates.push({ id, email, name });
            } else {
                selectedCandidates = selectedCandidates.filter(c => c.id !== id);
            }
            updateScheduleInterviewButton();
            updateSelectAllButtons();
        }

        function selectAllCandidates() {
            const checkboxes = document.querySelectorAll('.candidate-checkbox');
            selectedCandidates = [];
            checkboxes.forEach(cb => {
                cb.checked = true;
                selectedCandidates.push({
                    id: cb.dataset.candidateId,
                    email: cb.dataset.candidateEmail,
                    name: cb.dataset.candidateName
                });
            });
            updateScheduleInterviewButton();
            updateSelectAllButtons();
        }

        function deselectAllCandidates() {
            const checkboxes = document.querySelectorAll('.candidate-checkbox');
            checkboxes.forEach(cb => cb.checked = false);
            selectedCandidates = [];
            updateScheduleInterviewButton();
            updateSelectAllButtons();
        }

        function updateSelectAllButtons() {
            const selectAllBtn = document.getElementById('selectAllBtn');
            const deselectAllBtn = document.getElementById('deselectAllBtn');
            const total = document.querySelectorAll('.candidate-checkbox').length;
            
            if (selectedCandidates.length === 0) {
                selectAllBtn.style.display = 'flex';
                deselectAllBtn.style.display = 'none';
            } else if (selectedCandidates.length === total && total > 0) {
                selectAllBtn.style.display = 'none';
                deselectAllBtn.style.display = 'flex';
            } else {
                selectAllBtn.style.display = 'flex';
                deselectAllBtn.style.display = 'flex';
            }
        }

        function handleMeetingLinkChange(e) {
            meetingLink = e.target.value;
            updateScheduleInterviewButton();
        }

        function handleLocationChange(e) {
            interviewLocation = e.target.value;
            updateScheduleInterviewButton();
        }

        function handleDateChange(e) {
            interviewDate = e.target.value;
            updateScheduleInterviewButton();
        }

        function handleTimeChange(e) {
            interviewTime = e.target.value;
            updateScheduleInterviewButton();
        }

        function updateScheduleInterviewButton() {
            const section = document.getElementById('scheduleInterviewSection');
            const isVirtual = interviewMethod === 'virtual';
            const isOnsite = interviewMethod === 'onsite';
            
            const hasLink = !isVirtual || meetingLink;
            const hasLoc = !isOnsite || interviewLocation;
            const canSchedule = interviewMethod && hasLink && hasLoc && selectedPosition && selectedCandidates.length > 0 && interviewDate && interviewTime;
            
            section.style.display = canSchedule ? 'flex' : 'none';
        }

        async function scheduleInterview() {
            const btn = document.getElementById('scheduleInterviewBtn');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Scheduling...';
            btn.disabled = true;

            const data = {
                interviewMethod,
                meetingLink,
                interviewLocation,
                positionId: selectedPosition,
                candidateIds: selectedCandidates.map(c => c.id),
                interviewDate,
                interviewTime
            };

            try {
                const response = await fetch('/api/company/interviews/schedule', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();
                
                if (result.success) {
                    showNotification(result.message, 'success');
                    resetForm();
                    // Reload page to show new interview in the list
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    showNotification(result.message || 'Failed to schedule interview', 'error');
                }
            } catch (error) {
                console.error('Error scheduling interview:', error);
                showNotification('Network error. Please try again.', 'error');
            } finally {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        }

        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 16px 24px;
                border-radius: 8px;
                color: white;
                font-weight: 500;
                z-index: 10000;
                animation: slideIn 0.3s ease;
                background-color: ${type === 'success' ? 'var(--success)' : type === 'error' ? 'var(--danger)' : 'var(--accent-1)'};
            `;
            notification.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i> ${message}`;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.animation = 'fadeOut 0.3s ease';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }

        function resetForm() {
            document.getElementById('interviewMethod').value = '';
            document.getElementById('meetingLink').value = '';
            document.getElementById('interviewLocation').value = '';
            document.getElementById('positionSelect').value = '';
            document.getElementById('interviewDate').value = '';
            document.getElementById('interviewTime').value = '';
            
            document.getElementById('meetingLinkSection').style.display = 'none';
            document.getElementById('locationSection').style.display = 'none';
            document.getElementById('candidateListSection').style.display = 'none';
            document.getElementById('datetimeSection').style.display = 'none';
            document.getElementById('scheduleInterviewSection').style.display = 'none';
            
            deselectAllCandidates();
        }

        // Theme Logic
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

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            initializeTheme();
            setupThemeToggle();
        });
    </script>
</body>
</html>

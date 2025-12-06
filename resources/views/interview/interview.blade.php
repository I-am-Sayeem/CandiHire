@extends('layouts.app')

@section('content')
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
            
            <div class="nav-section">
                <div class="nav-section-title">Overview</div>
                <div class="nav-item" onclick="window.location.href='{{ url('company/dashboard') }}'">
                    <i class="fas fa-th-large"></i>
                    <span>Dashboard</span>
                </div>
                <div class="nav-item" onclick="window.location.href='{{ url('job-post') }}'">
                    <i class="fas fa-briefcase"></i>
                    <span>Post a Job</span>
                </div>
                <div class="nav-item" onclick="window.location.href='{{ url('company/applications') }}'">
                    <i class="fas fa-file-alt"></i>
                    <span>Applications</span>
                </div>
                <div class="nav-item active">
                    <i class="fas fa-user-tie"></i>
                    <span>Interviews</span>
                </div>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">Assessment</div>
                <div class="nav-item" onclick="window.location.href='{{ url('create-exam') }}'">
                    <i class="fas fa-file-contract"></i>
                    <span>Create Exam</span>
                </div>
                <div class="nav-item" onclick="window.location.href='{{ url('exam-review') }}'">
                    <i class="fas fa-clipboard-check"></i>
                    <span>Review Exams</span>
                </div>
                <div class="nav-item" onclick="window.location.href='{{ url('company/mcq-results') }}'">
                   <i class="fas fa-poll"></i>
                   <span>MCQ Results</span>
               </div>
            </div>
            
             <div class="nav-section">
                <div class="nav-section-title">Settings</div>
                <!-- Theme toggle will be handled by JS -->
                <div class="nav-item" id="themeToggleBtn">
                    <i class="fas fa-moon" id="themeIcon"></i>
                    <span id="themeText">Dark Mode</span>
                </div>
                 <div class="nav-item" onclick="window.location.href='{{ route('logout') }}'">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </div>
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

            <!-- Existing Interviews List could go here if part of the original requirement, 
                 but the PHP file provided focused on scheduling logic and 'interview interface' hiding/showing. 
                 The PHP file had placeholders for conducting an interview but they were set to display:none initially. 
                 I'll preserve the 'Interview Interface' hidden block. -->
            
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

        // Mock loading job positions - Replace with actual AJAX call to Laravel route
        function loadCompanyJobPositions() {
             // Simulating API call
             console.log('Loading job positions...');
             // In a real scenario: fetch('/api/company/jobs') ...
             // For now, let's mock some data to demonstrate functionality as per conversion task style
             const mockJobs = [
                 { JobID: 1, JobTitle: 'Software Engineer' },
                 { JobID: 2, JobTitle: 'Product Manager' },
                 { JobID: 3, JobTitle: 'UX Designer' }
             ];
             
             companyJobPositions = mockJobs;
             const select = document.getElementById('positionSelect');
             
             // Clear existing options except default
             while (select.options.length > 1) {
                select.remove(1);
             }

             mockJobs.forEach(job => {
                 const option = document.createElement('option');
                 option.value = job.JobID;
                 option.textContent = job.JobTitle;
                 select.appendChild(option);
             });
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

        function loadCandidatesForJob(jobId) {
            const candidateList = document.getElementById('candidateList');
            candidateList.innerHTML = '<div style="text-align: center; padding: 20px;"><i class="fas fa-spinner fa-spin"></i> Loading candidates...</div>';
            
            // Mock candidates for demonstration
            setTimeout(() => {
                const mockCandidates = [
                    { candidateId: 101, name: 'Alice Smith', email: 'alice@example.com', applicationDate: '2023-10-25', examScore: 85, passed: true },
                    { candidateId: 102, name: 'Bob Johnson', email: 'bob@example.com', applicationDate: '2023-10-26', examScore: 92, passed: true },
                    { candidateId: 103, name: 'Charlie Brown', email: 'charlie@example.com', applicationDate: '2023-10-27', examScore: 78, passed: true }
                ];

                candidateList.innerHTML = '';
                if (mockCandidates.length > 0) {
                    mockCandidates.forEach(candidate => {
                        const item = createCandidateItem(candidate);
                        candidateList.appendChild(item);
                    });
                } else {
                    candidateList.innerHTML = '<div style="text-align: center; padding: 20px;">No candidates found.</div>';
                }
                updateSelectAllButtons();
            }, 500);
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

        function scheduleInterview() {
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

            // Simulating API call
            console.log('Scheduling interview with data:', data);
            
            setTimeout(() => {
                alert('Interview Scheduled Successfully! Invitations sent.');
                resetForm();
                btn.innerHTML = originalText;
                btn.disabled = false;
            }, 1000);
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
            const icon = document.getElementById('themeIcon');
            const text = document.getElementById('themeText');
            if (theme === 'dark') {
                icon.className = 'fas fa-sun';
                text.textContent = 'Light Mode';
            } else {
                icon.className = 'fas fa-moon';
                text.textContent = 'Dark Mode';
            }
        }

        function setupThemeToggle() {
            const btn = document.getElementById('themeToggleBtn');
            if (btn) btn.addEventListener('click', toggleTheme);
        }
    </script>
</body>
</html>
@endsection

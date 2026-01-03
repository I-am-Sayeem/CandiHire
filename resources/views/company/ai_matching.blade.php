<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>AI Matching - CandiHire</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/AIMatching.css') }}">
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
                <a href="{{ url('/company/ai-matching') }}" class="nav-item active">
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
            <div class="page-header">
                <h1 class="page-title">AI Candidate Matching</h1>
                <p class="page-subtitle">Find the perfect candidates using our advanced AI-powered search engine.</p>
            </div>

            <!-- Search Form -->
            <div class="search-form">
                <div class="form-title">
                    <i class="fas fa-sliders-h"></i> Search Criteria
                </div>
                <form id="matchingForm">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="skills">Skills (comma separated)</label>
                            <input type="text" id="skills" name="skills" placeholder="e.g. Java, Python, React">
                        </div>
                        <div class="form-group">
                            <label for="location">Location</label>
                            <input type="text" id="location" name="location" placeholder="e.g. Dhaka">
                        </div>
                        <div class="form-group">
                            <label for="experience">Minimum Experience (Years)</label>
                            <input type="number" id="experience" name="experience" min="0" placeholder="e.g. 2">
                        </div>
                        <div class="form-group">
                            <label for="education">Education / Institute</label>
                            <input type="text" id="education" name="education" placeholder="e.g. BUET, Computer Science">
                        </div>
                    </div>
                    <button type="submit" class="search-btn">
                        <i class="fas fa-search"></i> Find Candidates
                    </button>
                </form>
            </div>

            <!-- Results Section -->
            <div class="results-section">
                <div class="results-header">
                    <div class="results-count">
                        <span id="resultCount">0</span> candidates found
                    </div>
                </div>

                <div id="resultsContainer">
                    <!-- Results will be loaded here -->
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <p>Enter your criteria above to find matching candidates.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Candidate Details / Interview Modal -->
    <div class="modal-overlay" id="candidateModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="modalCandidateName">Candidate Details</h2>
                <button class="close-modal" id="closeModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div id="modalBody">
                <!-- Content injected dynamically -->
            </div>

            <div id="interviewFormSection" style="margin-top: 30px; display: none; border-top: 1px solid var(--border); padding-top: 20px;">
                <h3 style="margin-bottom: 20px; font-size: 18px;">Schedule Interview</h3>
                <form id="scheduleInterviewForm">
                    <input type="hidden" id="interviewCandidateId" name="candidate_id">
                    
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label>Interview Title</label>
                        <input type="text" name="interview_title" required placeholder="e.g. Technical Interview Round 1" value="Technical Interview">
                    </div>
                    
                    <div class="form-grid" style="grid-template-columns: 1fr 1fr; margin-bottom: 15px;">
                        <div class="form-group">
                            <label>Date</label>
                            <input type="date" name="interview_date" required min="{{ date('Y-m-d') }}">
                        </div>
                        <div class="form-group">
                            <label>Time</label>
                            <input type="time" name="interview_time" required>
                        </div>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label>Mode</label>
                        <select name="interview_mode" id="interviewMode">
                            <option value="virtual">Virtual (Online)</option>
                            <option value="in_person">In Person</option>
                            <option value="phone">Phone Call</option>
                        </select>
                    </div>
                    
                    <div class="form-group" id="meetingLinkGroup" style="margin-bottom: 15px;">
                        <label>Meeting Link</label>
                        <input type="url" name="meeting_link" placeholder="https://meet.google.com/...">
                    </div>
                    
                    <div class="form-group" id="locationGroup" style="display: none; margin-bottom: 15px;">
                        <label>Office Location</label>
                        <textarea name="location" rows="2" placeholder="Office address..."></textarea>
                    </div>
                    
                    <button type="submit" class="search-btn" style="width: 100%;">Schedule Interview</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div class="toast" id="toast" style="position: fixed; top: 20px; right: 20px; padding: 15px 20px; background: var(--secondary); color: white; border-radius: 8px; transform: translateX(150%); transition: transform 0.3s; z-index: 2000;">
        <span id="toastMessage"></span>
    </div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        $(document).ready(function() {
            // Check for mode selection change
            $('#interviewMode').change(function() {
                if ($(this).val() === 'in_person') {
                    $('#meetingLinkGroup').hide();
                    $('#locationGroup').show();
                    $('input[name="meeting_link"]').prop('required', false);
                    $('textarea[name="location"]').prop('required', true);
                } else {
                    $('#meetingLinkGroup').show();
                    $('#locationGroup').hide();
                    $('textarea[name="location"]').prop('required', false);
                    // meeting link not strictly required for phone, but good for virtual
                     if ($(this).val() === 'virtual') {
                         $('input[name="meeting_link"]').prop('required', true);
                     } else {
                         $('input[name="meeting_link"]').prop('required', false);
                     }
                }
            });

            // Handle Search
            $('#matchingForm').on('submit', function(e) {
                e.preventDefault();
                const btn = $(this).find('.search-btn');
                const originalText = btn.html();
                
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Searching...');
                $('#resultsContainer').html('<div class="loading"><i class="fas fa-spinner fa-spin"></i><p> analyzing candidate profiles...</p></div>');

                $.ajax({
                    url: '{{ url("ai-matching/search") }}',
                    method: 'POST',
                    data: $(this).serialize(),
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                    success: function(response) {
                        if (response.success) {
                            displayResults(response.candidates);
                            $('#resultCount').text(response.total_found);
                        } else {
                            showToast(response.message, 'error');
                        }
                    },
                    error: function() {
                        showToast('Search failed. Please try again.', 'error');
                        $('#resultsContainer').html('<div class="empty-state"><p>An error occurred. Please try again.</p></div>');
                    },
                    complete: function() {
                        btn.prop('disabled', false).html(originalText);
                    }
                });
            });

            // Toggle Modal
            $('#closeModal').click(function() {
                $('#candidateModal').removeClass('open');
            });
            
            // Close modal on outside click
            $('#candidateModal').click(function(e) {
                if (e.target === this) {
                    $(this).removeClass('open');
                }
            });

            // Handle Interview Form Submit
            $('#scheduleInterviewForm').on('submit', function(e) {
                e.preventDefault();
                const btn = $(this).find('button[type="submit"]');
                const originalText = btn.html();
                
                btn.prop('disabled', true).text('Scheduling...');

                $.ajax({
                    url: '{{ url("ai-matching/invite") }}',
                    method: 'POST',
                    data: $(this).serialize(),
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                    success: function(response) {
                        if (response.success) {
                            showToast('Interview scheduled successfully!', 'success');
                            $('#candidateModal').removeClass('open');
                            $('#scheduleInterviewForm')[0].reset();
                        } else {
                            showToast(response.message || 'Failed to schedule', 'error');
                        }
                    },
                    error: function() {
                        showToast('Failed to schedule interview', 'error');
                    },
                    complete: function() {
                        btn.prop('disabled', false).html(originalText);
                    }
                });
            });
        });

        function displayResults(candidates) {
            const container = $('#resultsContainer');
            container.empty();

            if (candidates.length === 0) {
                container.html(`
                    <div class="empty-state">
                        <div class="empty-icon"><i class="fas fa-user-slash"></i></div>
                        <p>No candidates found matching your criteria.</p>
                    </div>
                `);
                return;
            }

            candidates.forEach(candidate => {
                // Mock match percentage based on skills overlap (simplified logic)
                const searchSkills = $('#skills').val().toLowerCase().split(',').map(s => s.trim()).filter(s => s);
                let matchScore = 70; // Base score
                if (searchSkills.length > 0 && candidate.skills_array) {
                     const candidateSkills = candidate.skills_array.map(s => s.toLowerCase().trim());
                     const matches = searchSkills.filter(s => candidateSkills.some(cs => cs.includes(s)));
                     matchScore += (matches.length / searchSkills.length) * 30; // Add up to 30% based on skill match
                }
                matchScore = Math.min(Math.round(matchScore), 99);

                const card = `
                    <div class="candidate-card">
                        <div class="candidate-header">
                            <div class="candidate-avatar">
                                ${candidate.FullName.charAt(0).toUpperCase()}
                            </div>
                            <div class="candidate-info">
                                <h3>${candidate.FullName}</h3>
                                <p><i class="fas fa-briefcase"></i> ${candidate.experience_years || 0} Years Experience • ${candidate.Location || 'Remote'}</p>
                                <p><i class="fas fa-graduation-cap"></i> ${candidate.Education || 'N/A'}</p>
                            </div>
                            <div class="candidate-match">
                                <i class="fas fa-bolt"></i> ${matchScore}% Match
                            </div>
                        </div>
                        
                        <div class="candidate-skills">
                            ${(candidate.skills_array || []).map(skill => `<span class="skill-tag">${skill}</span>`).join('')}
                        </div>
                        
                        <div class="candidate-actions">
                            <button class="action-btn" onclick="viewCandidate(${candidate.CandidateID})">
                                <i class="fas fa-eye"></i> View Profile
                            </button>
                            <button class="action-btn primary" onclick="openInterviewModal(${candidate.CandidateID}, '${candidate.FullName.replace(/'/g, "\\'")}')">
                                <i class="fas fa-calendar-check"></i> Invite to Interview
                            </button>
                        </div>
                    </div>
                `;
                container.append(card);
            });
        }

        function viewCandidate(id) {
            // Setup simple details view in modal
             $.ajax({
                url: '{{ url("ai-matching/details") }}',
                method: 'POST',
                data: { candidate_id: id },
                headers: { 'X-CSRF-TOKEN': csrfToken },
                success: function(response) {
                    if (response.success) {
                        const c = response.candidate;
                        $('#modalCandidateName').text(c.FullName);
                        $('#modalBody').html(`
                            <div style="line-height: 1.8; color: var(--text-secondary);">
                                <p><strong>Email:</strong> ${c.Email}</p>
                                <p><strong>Phone:</strong> ${c.PhoneNumber || 'N/A'}</p>
                                <p><strong>Location:</strong> ${c.Location || 'N/A'}</p>
                                <p><strong>Experience:</strong> ${c.YearsOfExperience || 0} Years</p>
                                <p><strong>Skills:</strong> ${c.Skills || 'None listed'}</p>
                                <hr style="border-color: var(--border); margin: 20px 0;">
                                <p><strong>Education:</strong> ${c.Education}</p>
                                <p><strong>Institute:</strong> ${c.Institute}</p>
                            </div>
                        `);
                        $('#interviewFormSection').hide(); // Hide interview form initially
                         // Add a button to show interview form from details
                        $('#modalBody').append(`
                             <button class="search-btn" style="margin-top: 20px; width: 100%;" onclick="$('#interviewFormSection').slideDown(); $(this).hide();">
                                Schedule Interview
                             </button>
                        `);
                        
                        // Set hidden input for interview form just in case
                        $('#interviewCandidateId').val(c.CandidateID);

                        $('#candidateModal').addClass('open');
                    }
                }
            });
        }

        function openInterviewModal(id, name) {
            $('#modalCandidateName').text('Schedule Interview: ' + name);
            $('#modalBody').html(''); // Clear detailed profile info
            $('#interviewCandidateId').val(id);
            $('#interviewFormSection').show();
            $('#candidateModal').addClass('open');
        }

        function showToast(message, type = 'success') {
            const toast = $('#toast');
            $('#toastMessage').text(message);
            toast.css('background', type === 'success' ? 'var(--success)' : 'var(--danger)');
            toast.css('transform', 'translateX(0)');
            setTimeout(() => {
                toast.css('transform', 'translateX(150%)');
            }, 3000);
        }

        // Theme Toggle Functions
        function initializeTheme() {
            const savedTheme = localStorage.getItem('candihire-theme') || 'dark';
            document.documentElement.setAttribute('data-theme', savedTheme);
            updateThemeButton(savedTheme);
        }

        function updateThemeButton(theme) {
            const icon = document.getElementById('themeIcon');
            const text = document.getElementById('themeText');
            const btn = document.getElementById('themeToggleBtn');
            
            if (btn) btn.setAttribute('data-theme', theme);
            
            if (theme === 'dark') {
                icon.className = 'fas fa-moon-stars';
                text.textContent = 'Light Mode';
            } else {
                icon.className = 'fas fa-moon-stars';
                text.textContent = 'Dark Mode';
            }
        }

        function toggleTheme() {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('candihire-theme', newTheme);
            updateThemeButton(newTheme);
            
            document.body.style.transition = 'background-color 0.3s ease, color 0.3s ease';
            setTimeout(() => {
                document.body.style.transition = '';
            }, 300);
        }

        // Initialize theme on load
        document.addEventListener('DOMContentLoaded', function() {
            initializeTheme();
            
            const themeBtn = document.getElementById('themeToggleBtn');
            if (themeBtn) {
                themeBtn.addEventListener('click', toggleTheme);
            }
        });
    </script>
</body>
</html>

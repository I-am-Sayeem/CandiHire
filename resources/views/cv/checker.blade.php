<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CV Checker - CandiHire</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/CvChecker.css') }}">
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
                <a href="{{ url('/cv/checker') }}" class="nav-item active">
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
                    <i class="fas fa-moon-stars" id="themeIcon"></i>
                    <span id="themeText">Light Mode</span>
                </button>
                <a href="{{ url('/logout') }}" class="logout-btn" style="text-decoration: none; display: flex; justify-content: center; align-items: center;"><i class="fas fa-sign-out-alt" style="margin-right:8px;"></i>Logout</a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="cv-checker-container">
                <!-- Left Panel: Upload -->
                <div class="left-panel">
                    <div class="panel-title">
                        <i class="fas fa-cloud-upload-alt"></i>
                        Upload CVs
                    </div>
                    
                    <div class="upload-area" id="dropZone">
                        <i class="fas fa-file-pdf upload-icon"></i>
                        <div class="upload-text">Drag & Drop CVs here</div>
                        <div class="upload-subtext">or click to browse PDF files</div>
                        <input type="file" id="fileInput" multiple accept="application/pdf" style="display: none;">
                    </div>

                    <div id="uploadedFilesList" style="margin-top: 20px; display: none;">
                        <h4 style="margin-bottom: 10px; color: var(--text-primary);">Files to Process:</h4>
                        <div id="filesList"></div>
                    </div>

                    <button class="process-btn" id="processBtn" onclick="processCVs()" disabled>
                        <i class="fas fa-cogs"></i> Process CVs
                    </button>
                    
                     <div id="loadingAnimation" style="display: none; text-align: center; margin-top: 20px;">
                        <div class="loading-spinner"></div>
                        <p style="margin-top: 10px; color: var(--text-secondary);">Analyzing resumes...</p>
                    </div>
                </div>

                <!-- Middle Panel: Candidates -->
                <div class="middle-panel">
                    <div class="candidate-section">
                        <div class="section-header">
                            <div class="section-title">
                                <i class="fas fa-users"></i>
                                Candidates Found
                                <span class="candidate-count" id="candidateCount">(0)</span>
                            </div>
                        </div>

                        <div id="filteredCandidatesList">
                            <div class="empty-state">
                                <i class="fas fa-user-friends empty-icon"></i>
                                <div class="empty-text">Upload resumes to see candidates</div>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Info (Hidden by default, shown on selection) -->
                    <div id="candidateContactInfo"></div>
                </div>

                <!-- Right Panel: Requirements -->
                <div class="right-panel">
                    <div class="panel-title">
                        <i class="fas fa-clipboard-check"></i>
                        Job Requirements
                    </div>

                    <div class="form-group">
                        <label class="form-label">Job Position</label>
                        <input type="text" id="jobPosition" class="form-input" placeholder="e.g. Software Engineer">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Experience Level</label>
                        <select id="experienceLevel" class="form-select">
                            <option value="entry">Entry Level (0-2 years)</option>
                            <option value="mid">Mid Level (3-5 years)</option>
                            <option value="senior">Senior Level (5+ years)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Required Skills</label>
                        <div class="skills-input-container">
                             <input type="text" id="skillInput" class="form-input" placeholder="Type and press Enter">
                        </div>
                        <div class="skills-container" id="skillsContainer">
                            <!-- Skills added here -->
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Custom Criteria (Optional)</label>
                        <textarea id="customCriteria" class="form-textarea" placeholder="Any specific requirements..."></textarea>
                    </div>

                    <button class="apply-filters-btn" onclick="applyFilters()">
                        <i class="fas fa-filter"></i> Apply Filters
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" style="position: fixed; top: 20px; right: 20px; padding: 15px 20px; border-radius: 8px; color: white; transform: translateX(150%); transition: transform 0.3s; z-index: 2000;">
        <span id="toastMessage"></span>
    </div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        let uploadedFiles = [];
        let currentProcessingId = null;

        // Drag & Drop Handling
        function setupDragAndDrop() {
            const dropZone = document.getElementById('dropZone');
            const fileInput = document.getElementById('fileInput');

            dropZone.addEventListener('click', () => fileInput.click());

            fileInput.addEventListener('change', handleFileSelect);

            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, preventDefaults, false);
            });

            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }

            ['dragenter', 'dragover'].forEach(eventName => {
                dropZone.addEventListener(eventName, () => dropZone.classList.add('highlight'), false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, () => dropZone.classList.remove('highlight'), false);
            });

            dropZone.addEventListener('drop', handleDrop, false);
        }

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            handleFiles(Array.from(files));
        }

        function handleFileSelect(e) {
            handleFiles(Array.from(e.target.files));
        }

        function handleFiles(files) {
            const pdfFiles = files.filter(file => file.type === 'application/pdf');
            if (pdfFiles.length === 0) {
                showToast('Please upload PDF files only.', 'error');
                return;
            }

            uploadedFiles = [...uploadedFiles, ...pdfFiles];
            updateFileList();
            
            if (uploadedFiles.length > 0) {
                document.getElementById('processBtn').disabled = false;
            }
        }

        function updateFileList() {
            const list = document.getElementById('filesList');
            const container = document.getElementById('uploadedFilesList');
            
            if (uploadedFiles.length === 0) {
                container.style.display = 'none';
                return;
            }

            container.style.display = 'block';
            list.innerHTML = '';
            
            uploadedFiles.forEach((file, index) => {
                const item = document.createElement('div');
                item.className = 'file-item';
                item.innerHTML = `
                    <i class="fas fa-file-pdf file-icon"></i>
                    <span class="file-name">${file.name}</span>
                    <span class="file-size">${(file.size / 1024 / 1024).toFixed(2)} MB</span>
                    <button class="popup-close" onclick="removeFile(${index})" style="font-size: 16px; margin-left:10px;"><i class="fas fa-times"></i></button>
                `;
                list.appendChild(item);
            });
        }

        function removeFile(index) {
            uploadedFiles.splice(index, 1);
            updateFileList();
            if (uploadedFiles.length === 0) {
                document.getElementById('processBtn').disabled = true;
            }
        }

        // Skills Input Handling
        function setupSkillsInput() {
            const input = document.getElementById('skillInput');
            const container = document.getElementById('skillsContainer');

            input.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const skill = this.value.trim();
                    if (skill) {
                        addSkillToken(skill);
                         this.value = '';
                    }
                }
            });

            function addSkillToken(skill) {
                const tag = document.createElement('span');
                tag.className = 'skill-tag';
                tag.innerHTML = `${skill} <i class="fas fa-times" onclick="this.parentElement.remove()"></i>`;
                container.appendChild(tag);
            }
        }

        function getRequiredSkills() {
            const tags = document.querySelectorAll('#skillsContainer .skill-tag');
            return Array.from(tags).map(tag => tag.innerText.trim());
        }

        // Process Logic
        async function processCVs() {
            const jobPosition = document.getElementById('jobPosition').value;
            if (!jobPosition) {
                showToast('Please enter a job position.', 'error');
                return;
            }
            
            if (uploadedFiles.length === 0) {
                showToast('Please upload CV files first.', 'error');
                return;
            }
            
            document.getElementById('loadingAnimation').style.display = 'block';
            document.getElementById('processBtn').disabled = true;

            try {
                // Step 1: Upload files
                const formData = new FormData();
                uploadedFiles.forEach(file => {
                    formData.append('cvs[]', file);
                });
                formData.append('jobPosition', jobPosition);
                formData.append('experienceLevel', document.getElementById('experienceLevel').value);
                formData.append('requiredSkills', getRequiredSkills().join(','));
                formData.append('customCriteria', document.getElementById('customCriteria').value);

                const uploadResponse = await fetch('/api/cv/upload', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: formData
                });

                const uploadResult = await uploadResponse.json();
                
                if (!uploadResult.success) {
                    throw new Error(uploadResult.message || 'Upload failed');
                }

                currentProcessingId = uploadResult.processingId;
                showToast(`${uploadResult.uploadedFiles.length} files uploaded. Processing...`, 'success');

                // Step 2: Process CVs
                const processResponse = await fetch('/api/cv/process', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        processingId: currentProcessingId
                    })
                });

                const processResult = await processResponse.json();
                
                if (!processResult.success) {
                    throw new Error(processResult.message || 'Processing failed');
                }

                // Display candidates
                displayCandidates(processResult.candidates);
                showToast(processResult.message, 'success');
                
            } catch (error) {
                console.error('CV Processing Error:', error);
                showToast(error.message || 'An error occurred', 'error');
            } finally {
                document.getElementById('loadingAnimation').style.display = 'none';
                document.getElementById('processBtn').disabled = false;
            }
        }
        
        async function applyFilters() {
            if (!currentProcessingId) {
                showToast('Please process CVs first.', 'error');
                return;
            }
            
            const btn = document.querySelector('.apply-filters-btn');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Applying...';
            btn.disabled = true;
            
            try {
                const response = await fetch('/api/cv/filter', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        processingId: currentProcessingId,
                        requiredSkills: getRequiredSkills().join(','),
                        experienceLevel: document.getElementById('experienceLevel').value,
                        minMatch: 0
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    displayCandidates(result.candidates);
                    showToast('Filters applied successfully!', 'success');
                } else {
                    showToast(result.message || 'Filter failed', 'error');
                }
            } catch (error) {
                console.error('Filter Error:', error);
                showToast('An error occurred while filtering.', 'error');
            } finally {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        }

        function displayCandidates(candidates) {
            const list = document.getElementById('filteredCandidatesList');
            const count = document.getElementById('candidateCount');
            
            count.innerText = `(${candidates.length})`;
            list.innerHTML = '';
            
            if (candidates.length === 0) {
                 list.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-user-slash empty-icon"></i>
                        <div class="empty-text">No candidates found</div>
                    </div>`;
                 return;
            }

            candidates.forEach(c => {
                const card = document.createElement('div');
                card.className = 'candidate-card';
                const skills = Array.isArray(c.skills) ? c.skills : [];
                const skillsHtml = skills.length > 0 
                    ? skills.slice(0, 5).map(s => `<span class="skill-tag">${s}</span>`).join('') 
                    : '<span style="color: var(--text-secondary); font-size: 12px;">No skills detected</span>';
                
                card.innerHTML = `
                    <div class="candidate-header">
                        <div class="candidate-name">${c.name || 'Unknown'}</div>
                        <div class="match-percentage" style="background: ${c.match >= 70 ? 'linear-gradient(135deg, var(--success), #2ea043)' : c.match >= 40 ? 'linear-gradient(135deg, var(--accent-1), var(--accent-hover))' : 'linear-gradient(135deg, var(--danger), #d03f39)'}">${c.match || 0}% Match</div>
                    </div>
                    <div class="candidate-details">
                         <div class="candidate-detail"><i class="fas fa-briefcase"></i> ${c.experienceYears || 0} Years</div>
                         <div class="candidate-detail"><i class="fas fa-map-marker-alt"></i> ${c.location || 'Unknown'}</div>
                    </div>
                    <div class="candidate-skills">
                        <div class="skills-tags">
                            ${skillsHtml}
                            ${skills.length > 5 ? `<span class="skill-tag" style="background: var(--accent-1); color: white;">+${skills.length - 5} more</span>` : ''}
                        </div>
                    </div>
                `;
                card.onclick = () => showCandidateDetails(c);
                list.appendChild(card);
            });
        }
        
        function showCandidateDetails(c) {
             const container = document.getElementById('candidateContactInfo');
             const skills = Array.isArray(c.skills) ? c.skills : [];
             
             container.innerHTML = `
                <div class="contact-info-card" style="animation: slideInUp 0.3s ease-out;">
                    <div class="contact-header">
                        <h4 class="contact-name">${c.name || 'Unknown Candidate'}</h4>
                        <div class="contact-status" style="display: flex; align-items: center; gap: 8px;">
                            <span style="background: ${c.match >= 70 ? 'var(--success)' : c.match >= 40 ? 'var(--accent-1)' : 'var(--danger)'}; color: white; padding: 2px 8px; border-radius: 12px; font-size: 11px;">${c.match || 0}% Match</span>
                            <span style="color: ${c.extractionStatus === 'success' ? 'var(--success)' : 'var(--warning)'}; font-size: 11px;">
                                <i class="fas fa-${c.extractionStatus === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i>
                                ${c.extractionStatus === 'success' ? 'Parsed Successfully' : 'Partial Data'}
                            </span>
                        </div>
                    </div>
                    <div class="contact-details-grid">
                        <div class="contact-detail">
                            <div class="contact-icon"><i class="fas fa-envelope"></i></div>
                            <div class="contact-content">
                                <div class="contact-label">Email</div>
                                <div class="contact-value">${c.email || 'Not found'}</div>
                            </div>
                        </div>
                         <div class="contact-detail">
                            <div class="contact-icon"><i class="fas fa-phone"></i></div>
                            <div class="contact-content">
                                <div class="contact-label">Phone</div>
                                <div class="contact-value">${c.phone || 'Not found'}</div>
                            </div>
                        </div>
                        ${c.linkedin ? `
                        <div class="contact-detail">
                            <div class="contact-icon" style="background: #0077b5;"><i class="fab fa-linkedin-in"></i></div>
                            <div class="contact-content">
                                <div class="contact-label">LinkedIn</div>
                                <div class="contact-value"><a href="${c.linkedin}" target="_blank" style="color: var(--accent-1);">View Profile</a></div>
                            </div>
                        </div>` : ''}
                        <div class="contact-detail">
                            <div class="contact-icon"><i class="fas fa-briefcase"></i></div>
                            <div class="contact-content">
                                <div class="contact-label">Experience</div>
                                <div class="contact-value">${c.experienceYears || 0} Years</div>
                            </div>
                        </div>
                        ${c.education ? `
                        <div class="contact-detail">
                            <div class="contact-icon"><i class="fas fa-graduation-cap"></i></div>
                            <div class="contact-content">
                                <div class="contact-label">Education</div>
                                <div class="contact-value">${c.education}</div>
                            </div>
                        </div>` : ''}
                        <div class="contact-detail">
                            <div class="contact-icon"><i class="fas fa-file-pdf"></i></div>
                            <div class="contact-content">
                                <div class="contact-label">Source File</div>
                                <div class="contact-value">${c.fileName || 'Unknown'}</div>
                            </div>
                        </div>
                    </div>
                    ${skills.length > 0 ? `
                    <div class="contact-skills" style="margin-top: 15px; padding-top: 15px; border-top: 1px solid var(--border);">
                        <div class="skills-header" style="font-size: 14px; font-weight: 600; margin-bottom: 10px;">Skills</div>
                        <div class="skills-tags">
                            ${skills.map(s => `<span class="skill-tag">${s}</span>`).join('')}
                        </div>
                    </div>` : ''}
                    ${c.summary ? `
                    <div class="contact-summary" style="margin-top: 15px; padding-top: 15px; border-top: 1px solid var(--border);">
                        <div class="skills-header" style="font-size: 14px; font-weight: 600; margin-bottom: 10px;">Summary</div>
                        <div style="color: var(--text-primary); font-size: 14px; line-height: 1.5; background-color: var(--bg-primary); padding: 12px; border-radius: 8px; border: 1px solid var(--border);">
                            ${c.summary}
                        </div>
                    </div>` : ''}
                </div>
             `;
        }

        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            document.getElementById('toastMessage').innerText = message;
            toast.style.backgroundColor = type === 'success' ? 'var(--success)' : 'var(--danger)';
            toast.style.transform = 'translateX(0)';
            setTimeout(() => {
                toast.style.transform = 'translateX(150%)';
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
            setupDragAndDrop();
            setupSkillsInput();
            initializeTheme();
            
            const themeBtn = document.getElementById('themeToggleBtn');
            if (themeBtn) {
                themeBtn.addEventListener('click', toggleTheme);
            }
        });
    </script>
</body>
</html>

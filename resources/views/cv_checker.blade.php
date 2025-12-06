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
            
            <div class="nav-section">
                <div class="nav-section-title">Overview</div>
                <div class="nav-item" onclick="window.location.href='{{ url('company/dashboard') }}'">
                    <i class="fas fa-th-large"></i>
                    <span>Dashboard</span>
                </div>
                <div class="nav-item" onclick="window.location.href='{{ url('company/applications') }}'">
                    <i class="fas fa-file-alt"></i>
                    <span>Applications</span>
                </div>
                <div class="nav-item" onclick="window.location.href='{{ url('company/exams') }}'">
                    <i class="fas fa-clipboard-list"></i>
                    <span>Exams</span>
                </div>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">Recruitment</div>
                <div class="nav-item " onclick="window.location.href='{{ url('ai-matching') }}'">
                    <i class="fas fa-robot"></i>
                    <span>AI Matching</span>
                </div>
                <div class="nav-item active">
                    <i class="fas fa-check-double"></i>
                    <span>CV Checker</span>
                </div>
                 <div class="nav-item" onclick="window.location.href='{{ url('company/interviews') }}'">
                    <i class="fas fa-video"></i>
                    <span>Interviews</span>
                </div>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">Account</div>
                <div class="nav-item" onclick="window.location.href='{{ url('company/profile') }}'">
                    <i class="fas fa-building"></i>
                    <span>Company Profile</span>
                </div>
                 <form action="{{ route('logout') }}" method="POST" id="logout-form">
                    @csrf
                    <div class="nav-item" onclick="document.getElementById('logout-form').submit()">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </div>
                </form>
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

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            setupDragAndDrop();
            setupSkillsInput();
        });

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
        function processCVs() {
            const jobPosition = document.getElementById('jobPosition').value;
             if (!jobPosition) {
                showToast('Please enter a job position.', 'error');
                return;
            }
            
            document.getElementById('loadingAnimation').style.display = 'block';
            document.getElementById('processBtn').disabled = true;

            // Simulate parsing and processing logic via generic backend route interaction or direct mock
            // In migration, we assume either we point to the old handler logic or a new route.
            // For now, let's simulate a success to demonstrate the UI flow as requested by the user objective pattern.
            
            setTimeout(() => {
                // Mock results for now as backend logic porting is separate
                const mockCandidates = uploadedFiles.map((file, i) => ({
                    id: i + 1,
                    name: "Candidate " + (i + 1),
                    email: "candidate" + (i + 1) + "@example.com",
                    phone: "+1234567890",
                    location: "Dhaka",
                    experienceYears: Math.floor(Math.random() * 5) + 1,
                    skills: ["PHP", "JavaScript", "HTML"],
                    match: Math.floor(Math.random() * 40) + 60,
                    fileName: file.name
                }));
                
                displayCandidates(mockCandidates);
                document.getElementById('loadingAnimation').style.display = 'none';
                document.getElementById('processBtn').disabled = false;
                currentProcessingId = 'mock_id_' + Date.now();
                showToast('CVs processed successfully!', 'success');
            }, 2000);
        }
        
        function applyFilters() {
            if(!currentProcessingId) {
                 showToast('Please process CVs first.', 'error');
                 return;
            }
            // In a real app, this would re-fetch/filter based on current criteria.
            showToast('Filters applied!', 'success');
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
                card.innerHTML = `
                    <div class="candidate-header">
                        <div class="candidate-name">${c.name}</div>
                        <div class="match-percentage">${c.match}% Match</div>
                    </div>
                    <div class="candidate-details">
                         <div class="candidate-detail"><i class="fas fa-briefcase"></i> ${c.experienceYears} Years</div>
                         <div class="candidate-detail"><i class="fas fa-map-marker-alt"></i> ${c.location}</div>
                    </div>
                    <div class="candidate-skills">
                        <div class="skills-tags">
                            ${c.skills.map(s => `<span class="skill-tag">${s}</span>`).join('')}
                        </div>
                    </div>
                `;
                card.onclick = () => showCandidateDetails(c);
                list.appendChild(card);
            });
        }
        
        function showCandidateDetails(c) {
             const container = document.getElementById('candidateContactInfo');
             container.innerHTML = `
                <div class="contact-info-card" style="animation: slideInUp 0.3s ease-out;">
                    <div class="contact-header">
                        <h4 class="contact-name">${c.name}</h4>
                        <div class="contact-status">Contact Information</div>
                    </div>
                    <div class="contact-details-grid">
                        <div class="contact-detail">
                            <div class="contact-icon"><i class="fas fa-envelope"></i></div>
                            <div class="contact-content">
                                <div class="contact-label">Email</div>
                                <div class="contact-value">${c.email}</div>
                            </div>
                        </div>
                         <div class="contact-detail">
                            <div class="contact-icon"><i class="fas fa-phone"></i></div>
                            <div class="contact-content">
                                <div class="contact-label">Phone</div>
                                <div class="contact-value">${c.phone}</div>
                            </div>
                        </div>
                        <div class="contact-detail">
                            <div class="contact-icon"><i class="fas fa-file-pdf"></i></div>
                            <div class="contact-content">
                                <div class="contact-label">Source File</div>
                                <div class="contact-value">${c.fileName}</div>
                            </div>
                        </div>
                    </div>
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
    </script>
</body>
</html>

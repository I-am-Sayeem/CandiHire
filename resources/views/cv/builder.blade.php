<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CV Builder - CandiHire</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/CvBuilder.css') }}">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
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
                    <div id="candidateAvatar" style="width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 16px; {{ $candidateProfilePicture ? 'background-image: url(' . asset($candidateProfilePicture) . '); background-size: cover; background-position: center;' : 'background: linear-gradient(135deg, var(--accent), var(--accent-2));' }}">
                        {{ $candidateProfilePicture ? '' : strtoupper(substr($candidateName ?? 'U', 0, 1)) }}
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
                <a href="{{ url('/cv/builder') }}" class="nav-item active">
                    <i class="fas fa-file-alt"></i>
                    <span>CV builder</span>
                </a>
                <a href="{{ url('/candidate/applications') }}" class="nav-item">
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
            <div class="page-header">
                <h1 class="page-title">CV Builder</h1>
                <div class="header-actions">
                    <button class="btn btn-secondary" onclick="previewCV()">
                        <i class="fas fa-eye"></i> Preview
                    </button>
                    <button class="btn btn-primary" onclick="generatePDF()">
                        <i class="fas fa-download"></i> Download PDF
                    </button>
                </div>
            </div>

            <div class="cv-builder">
                <!-- Tabs -->
                <div class="cv-tabs">
                    <div class="cv-tab active" onclick="switchTab('personal')">Personal Info</div>
                    <div class="cv-tab" onclick="switchTab('experience')">Experience</div>
                    <div class="cv-tab" onclick="switchTab('education')">Education</div>
                    <div class="cv-tab" onclick="switchTab('skills')">Skills</div>
                    <div class="cv-tab" onclick="switchTab('projects')">Projects</div>
                </div>

                <!-- Form -->
                <form id="cvForm" class="cv-form">
                    <!-- Personal Info Tab -->
                    <div id="personal" class="tab-content">
                        <div class="form-grid">
                            <!-- Profile Picture Upload -->
                            <div class="form-group full-width" style="display: flex; align-items: center; gap: 20px; margin-bottom: 20px;">
                                <div id="profilePicPreview" style="width: 100px; height: 100px; border-radius: 50%; background: var(--bg-tertiary); border: 2px solid var(--border); display: flex; align-items: center; justify-content: center; overflow: hidden;">
                                    <i class="fas fa-user" style="font-size: 40px; color: var(--text-secondary);"></i>
                                </div>
                                <div style="flex: 1;">
                                    <label class="form-label">Profile Picture</label>
                                    <input type="file" id="profilePictureInput" accept="image/*" style="display: none;">
                                    <button type="button" onclick="document.getElementById('profilePictureInput').click()" class="btn btn-secondary" style="margin-top: 5px;">
                                        <i class="fas fa-upload"></i> Upload Photo
                                    </button>
                                    <p style="font-size: 12px; color: var(--text-secondary); margin-top: 5px;">Recommended: Square image, max 2MB</p>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">First Name</label>
                                <input type="text" name="firstName" class="form-input" placeholder="John" value="{{ auth()->user()->first_name ?? '' }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Last Name</label>
                                <input type="text" name="lastName" class="form-input" placeholder="Doe" value="{{ auth()->user()->last_name ?? '' }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-input" placeholder="john@example.com" value="{{ auth()->user()->email ?? '' }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Phone</label>
                                <input type="tel" name="phone" class="form-input" placeholder="+880 1..." value="{{ auth()->user()->phone ?? '' }}">
                            </div>
                            <div class="form-group full-width">
                                <label class="form-label">Address</label>
                                <input type="text" name="address" class="form-input" placeholder="123 Main St, City, Country" value="{{ auth()->user()->address ?? '' }}">
                            </div>
                            <div class="form-group full-width">
                                <label class="form-label">Professional Summary / Objective</label>
                                <textarea name="summary" class="form-textarea" placeholder="Write a brief summary of your professional background and career objectives..."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Experience Tab -->
                    <div id="experience" class="tab-content" style="display: none;">
                        <div id="experience-container">
                            <!-- Helper function to add default entry -->
                        </div>
                        <button type="button" class="btn btn-secondary btn-add" onclick="addExperience()">
                            <i class="fas fa-plus"></i> Add Experience
                        </button>
                    </div>

                    <!-- Education Tab -->
                    <div id="education" class="tab-content" style="display: none;">
                        <div id="education-container">
                             <!-- Helper function to add default entry -->
                        </div>
                        <button type="button" class="btn btn-secondary btn-add" onclick="addEducation()">
                            <i class="fas fa-plus"></i> Add Education
                        </button>
                    </div>

                    <!-- Skills Tab -->
                    <div id="skills" class="tab-content" style="display: none;">
                        <div class="form-grid">
                            <div class="form-group full-width">
                                <label class="form-label">Programming Languages</label>
                                <input type="text" name="programmingLanguages" class="form-input" placeholder="Java, Python, C++, etc.">
                            </div>
                            <div class="form-group full-width">
                                <label class="form-label">Frameworks & Libraries</label>
                                <input type="text" name="frameworks" class="form-input" placeholder="React, Laravel, Django, etc.">
                            </div>
                            <div class="form-group full-width">
                                <label class="form-label">Tools & Technologies</label>
                                <input type="text" name="tools" class="form-input" placeholder="Git, Docker, AWS, etc.">
                            </div>
                             <div class="form-group full-width">
                                <label class="form-label">Soft Skills</label>
                                <input type="text" name="softSkills" class="form-input" placeholder="Leadership, Communication, Teamwork">
                            </div>
                        </div>
                    </div>

                    <!-- Projects Tab -->
                    <div id="projects" class="tab-content" style="display: none;">
                         <div id="projects-container">
                              <!-- Helper function to add default entry -->
                         </div>
                        <button type="button" class="btn btn-secondary btn-add" onclick="addProject()">
                            <i class="fas fa-plus"></i> Add Project
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Right Sidebar Tips -->
        <div class="right-sidebar">
            <div class="sidebar-section">
                <h3 class="section-title">CV Tips</h3>
                <div class="cv-tip">
                    <div class="tip-icon"><i class="fas fa-lightbulb"></i></div>
                    <div class="tip-content">
                        <h4>Keep it Relevant</h4>
                        <p>Tailor your CV to the specific job you're applying for. Highlight relevant skills and experience.</p>
                    </div>
                </div>
                 <div class="cv-tip">
                    <div class="tip-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="tip-content">
                        <h4>Use Action Verbs</h4>
                        <p>Start bullet points with strong action verbs like "Developed", "Led", "Created", etc.</p>
                    </div>
                </div>
                <div class="cv-tip">
                    <div class="tip-icon"><i class="fas fa-chart-line"></i></div>
                    <div class="tip-content">
                        <h4>Quantify Achievements</h4>
                        <p>Use numbers to show impact (e.g., "Increased sales by 20%").</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Profile Edit Popup -->
    <div id="profileEditPopup" class="popup-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 9999; overflow-y: auto;">
        <div class="popup-content" style="background: var(--bg-secondary); border-radius: 12px; max-width: 600px; margin: 50px auto; padding: 30px; position: relative; border: 1px solid var(--border);">
            <div class="popup-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <div class="popup-title" style="font-size: 20px; font-weight: 600; color: var(--text-primary);">
                    <i class="fas fa-user-edit"></i>
                    Edit Profile
                </div>
                <button class="popup-close" onclick="closeProfileEditPopup()" style="background: none; border: none; color: var(--text-secondary); font-size: 20px; cursor: pointer;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="profileEditForm" enctype="multipart/form-data">
                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="profilePicture" style="display: block; margin-bottom: 5px; color: var(--text-primary);">Profile Picture</label>
                    <input type="file" id="profilePicture" name="profilePicture" accept="image/*" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px; background: var(--bg-primary); color: var(--text-primary);">
                </div>
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="fullName" style="display: block; margin-bottom: 5px; color: var(--text-primary);">Full Name *</label>
                    <input type="text" id="fullName" name="fullName" required style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px; background: var(--bg-primary); color: var(--text-primary);">
                </div>
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="phoneNumber" style="display: block; margin-bottom: 5px; color: var(--text-primary);">Phone Number *</label>
                    <input type="tel" id="phoneNumber" name="phoneNumber" required style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px; background: var(--bg-primary); color: var(--text-primary);">
                </div>
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="location" style="display: block; margin-bottom: 5px; color: var(--text-primary);">Location</label>
                    <input type="text" id="location" name="location" placeholder="City, Country" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px; background: var(--bg-primary); color: var(--text-primary);">
                </div>
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="skills" style="display: block; margin-bottom: 5px; color: var(--text-primary);">Skills</label>
                    <textarea id="skills" name="skills" placeholder="List your key skills separated by commas" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px; background: var(--bg-primary); color: var(--text-primary); min-height: 80px;"></textarea>
                </div>
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="summary" style="display: block; margin-bottom: 5px; color: var(--text-primary);">Professional Summary</label>
                    <textarea id="summary" name="summary" placeholder="Brief description about yourself" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px; background: var(--bg-primary); color: var(--text-primary); min-height: 80px;"></textarea>
                </div>
                
                <div class="form-actions" style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                    <button type="button" class="btn btn-secondary" onclick="closeProfileEditPopup()" style="padding: 10px 20px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-tertiary); color: var(--text-primary); cursor: pointer;">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary" id="submitProfileUpdate" style="padding: 10px 20px; border-radius: 8px; border: none; background: var(--accent); color: white; cursor: pointer;">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Initialize with one empty field for relevant sections if empty
        document.addEventListener('DOMContentLoaded', function() {
            addExperience();
            addEducation();
            addProject();
            initializeTheme();
            
             // Setup Toggle Listener
             const themeBtn = document.getElementById('themeToggleBtn');
             if(themeBtn) {
                 themeBtn.addEventListener('click', toggleTheme);
             }
             
             // Setup Edit Profile Button
             const editProfileBtn = document.getElementById('editProfileBtn');
             if(editProfileBtn) {
                 editProfileBtn.addEventListener('click', openProfileEditPopup);
             }
             
             // Setup Profile Picture Upload Handler
             const profilePicInput = document.getElementById('profilePictureInput');
             if (profilePicInput) {
                 profilePicInput.addEventListener('change', function(e) {
                     const file = e.target.files[0];
                     if (file) {
                         if (file.size > 2 * 1024 * 1024) {
                             showToast('Image too large. Maximum 2MB allowed.', 'error');
                             return;
                         }
                         const reader = new FileReader();
                         reader.onload = function(e) {
                             const previewDiv = document.getElementById('profilePicPreview');
                             previewDiv.innerHTML = '<img src="' + e.target.result + '" style="width: 100%; height: 100%; object-fit: cover;">';
                             previewDiv.dataset.imageData = e.target.result;
                         };
                         reader.readAsDataURL(file);
                     }
                 });
             }
        });
        
        // Profile Edit Popup Functions
        function openProfileEditPopup() {
            document.getElementById('profileEditPopup').style.display = 'block';
            document.body.style.overflow = 'hidden';
        }
        
        function closeProfileEditPopup() {
            document.getElementById('profileEditPopup').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        function switchTab(tabId) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(el => el.style.display = 'none');
            document.querySelectorAll('.cv-tab').forEach(el => el.classList.remove('active'));
            
            // Show selected tab
            document.getElementById(tabId).style.display = 'block';
            
            // Highlight tab button (simple logic to find index or matching text)
            const tabs = document.querySelectorAll('.cv-tab');
            if (tabId === 'personal') tabs[0].classList.add('active');
            if (tabId === 'experience') tabs[1].classList.add('active');
            if (tabId === 'education') tabs[2].classList.add('active');
            if (tabId === 'skills') tabs[3].classList.add('active');
            if (tabId === 'projects') tabs[4].classList.add('active');
        }

        // Add Experience Entry
        function addExperience() {
            const container = document.getElementById('experience-container');
            const count = container.children.length + 1;
            
            const newEntry = document.createElement('div');
            newEntry.className = 'entry-section';
            newEntry.innerHTML = `
                <div class="entry-header">
                    <div class="entry-title">Experience #${count}</div>
                    <button type="button" class="btn btn-remove" onclick="this.closest('.entry-section').remove()">
                        <i class="fas fa-trash"></i> Remove
                    </button>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Job Title</label>
                        <input type="text" name="jobTitle[]" class="form-input" placeholder="Software Engineer">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Company</label>
                        <input type="text" name="company[]" class="form-input" placeholder="Tech Corp">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="startDate[]" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">End Date</label>
                        <input type="date" name="endDate[]" class="form-input">
                    </div>
                    <div class="form-group full-width">
                        <label class="form-label">Description</label>
                        <textarea name="jobDescription[]" class="form-textarea" placeholder="Responsibilities and achievements..."></textarea>
                    </div>
                </div>
            `;
            container.appendChild(newEntry);
        }

        // Add Education Entry
        function addEducation() {
            const container = document.getElementById('education-container');
            const count = container.children.length + 1;
            
            const newEntry = document.createElement('div');
            newEntry.className = 'entry-section';
            newEntry.innerHTML = `
                <div class="entry-header">
                    <div class="entry-title">Education #${count}</div>
                    <button type="button" class="btn btn-remove" onclick="this.closest('.entry-section').remove()">
                        <i class="fas fa-trash"></i> Remove
                    </button>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Degree</label>
                        <input type="text" name="degree[]" class="form-input" placeholder="B.Sc in Computer Science">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Institution</label>
                        <input type="text" name="institution[]" class="form-input" placeholder="University Name">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Start Year</label>
                        <input type="number" name="eduStartYear[]" class="form-input" placeholder="2018">
                    </div>
                    <div class="form-group">
                        <label class="form-label">End Year</label>
                        <input type="number" name="eduEndYear[]" class="form-input" placeholder="2022">
                    </div>
                </div>
            `;
            container.appendChild(newEntry);
        }

        // Add Project Entry
        function addProject() {
            const container = document.getElementById('projects-container');
            const count = container.children.length + 1;
            
            const newEntry = document.createElement('div');
            newEntry.className = 'entry-section';
            newEntry.innerHTML = `
                <div class="entry-header">
                    <div class="entry-title">Project #${count}</div>
                    <button type="button" class="btn btn-remove" onclick="this.closest('.entry-section').remove()">
                        <i class="fas fa-trash"></i> Remove
                    </button>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Project Name</label>
                        <input type="text" name="projectName[]" class="form-input" placeholder="Project Name">
                    </div>
                     <div class="form-group">
                        <label class="form-label">Role</label>
                        <input type="text" name="projectRole[]" class="form-input" placeholder="Lead Developer">
                    </div>
                    <div class="form-group full-width">
                        <label class="form-label">Description</label>
                        <textarea name="projectDescription[]" class="form-textarea" placeholder="Project details..."></textarea>
                    </div>
                     <div class="form-group full-width">
                        <label class="form-label">Technologies</label>
                        <input type="text" name="projectTechnologies[]" class="form-input" placeholder="Laravel, MySQL, Vue.js">
                    </div>
                </div>
            `;
            container.appendChild(newEntry);
        }

        function generatePDF() {
            try {
                var data = collectCvData();
                var html = buildCvHtml(data);
                
                // Create filename from name
                var fullName = [data.firstName, data.lastName].filter(Boolean).join('_') || 'my_cv';
                
                // Open in new window and trigger print for PDF
                var win = window.open('', '_blank', 'width=900,height=700');
                if (!win) { 
                    alert('Pop-up blocked. Please allow pop-ups to download PDF.'); 
                    return; 
                }
                
                win.document.open();
                win.document.write(html);
                win.document.close();
                
                // Wait for content to load then trigger print
                win.onload = function() {
                    setTimeout(function() {
                        win.focus();
                        win.print();
                    }, 500);
                };
                
                showToast('PDF download dialog opened!', 'success');
            } catch (error) {
                console.error('Error generating PDF:', error);
                showToast('Error generating PDF: ' + error.message, 'error');
            }
        }
        
        // Collect all CV data from the form
        function collectCvData() {
            var data = {};
            data.firstName = (document.querySelector('input[name="firstName"]') || { value: '' }).value.trim();
            data.lastName = (document.querySelector('input[name="lastName"]') || { value: '' }).value.trim();
            data.email = (document.querySelector('input[name="email"]') || { value: '' }).value.trim();
            data.phone = (document.querySelector('input[name="phone"]') || { value: '' }).value.trim();
            data.address = (document.querySelector('input[name="address"]') || { value: '' }).value.trim();
            data.summary = (document.querySelector('textarea[name="summary"]') || { value: '' }).value.trim();
            
            // Get profile picture
            var profilePicPreview = document.getElementById('profilePicPreview');
            data.profilePicture = profilePicPreview ? (profilePicPreview.dataset.imageData || '') : '';

            // Get experiences
            data.experiences = [];
            document.querySelectorAll('#experience-container .entry-section').forEach(function(entry) {
                var item = {
                    title: (entry.querySelector('input[name="jobTitle[]"]') || { value: '' }).value.trim(),
                    company: (entry.querySelector('input[name="company[]"]') || { value: '' }).value.trim(),
                    startDate: (entry.querySelector('input[name="startDate[]"]') || { value: '' }).value.trim(),
                    endDate: (entry.querySelector('input[name="endDate[]"]') || { value: '' }).value.trim(),
                    description: (entry.querySelector('textarea[name="jobDescription[]"]') || { value: '' }).value.trim()
                };
                if (item.title || item.company || item.description) {
                    data.experiences.push(item);
                }
            });

            // Get education
            data.education = [];
            document.querySelectorAll('#education-container .entry-section').forEach(function(entry) {
                var item = {
                    degree: (entry.querySelector('input[name="degree[]"]') || { value: '' }).value.trim(),
                    institution: (entry.querySelector('input[name="institution[]"]') || { value: '' }).value.trim(),
                    startYear: (entry.querySelector('input[name="eduStartYear[]"]') || { value: '' }).value.trim(),
                    endYear: (entry.querySelector('input[name="eduEndYear[]"]') || { value: '' }).value.trim()
                };
                if (item.degree || item.institution) {
                    data.education.push(item);
                }
            });

            // Get skills
            data.skills = {
                programmingLanguages: (document.querySelector('input[name="programmingLanguages"]') || { value: '' }).value.trim(),
                frameworks: (document.querySelector('input[name="frameworks"]') || { value: '' }).value.trim(),
                tools: (document.querySelector('input[name="tools"]') || { value: '' }).value.trim(),
                softSkills: (document.querySelector('input[name="softSkills"]') || { value: '' }).value.trim()
            };

            // Get projects
            data.projects = [];
            document.querySelectorAll('#projects-container .entry-section').forEach(function(entry) {
                var item = {
                    name: (entry.querySelector('input[name="projectName[]"]') || { value: '' }).value.trim(),
                    role: (entry.querySelector('input[name="projectRole[]"]') || { value: '' }).value.trim(),
                    description: (entry.querySelector('textarea[name="projectDescription[]"]') || { value: '' }).value.trim(),
                    technologies: (entry.querySelector('input[name="projectTechnologies[]"]') || { value: '' }).value.trim()
                };
                if (item.name || item.description) {
                    data.projects.push(item);
                }
            });

            return data;
        }

        // Build HTML for CV preview - Professional Two-Column Layout
        function buildCvHtml(data) {
            function esc(str) { return (str || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
            
            var fullName = [data.firstName, data.lastName].filter(Boolean).join(' ') || 'Your Name';
            
            // Profile picture HTML
            var profilePicHtml = data.profilePicture 
                ? '<img src="' + data.profilePicture + '" alt="Profile" class="profile-pic">'
                : '<div class="profile-pic-placeholder"><span>' + (data.firstName ? data.firstName.charAt(0).toUpperCase() : 'U') + '</span></div>';
            
            // Contact info for sidebar
            var contactHtml = '';
            if (data.phone) contactHtml += '<div class="contact-item"><i>📞</i> ' + esc(data.phone) + '</div>';
            if (data.email) contactHtml += '<div class="contact-item"><i>📧</i> ' + esc(data.email) + '</div>';
            if (data.address) contactHtml += '<div class="contact-item"><i>📍</i> ' + esc(data.address) + '</div>';
            
            // Skills for sidebar
            var skillsList = [];
            if (data.skills && data.skills.programmingLanguages) skillsList.push('<div class="skill-category"><strong>Programming:</strong><br>' + esc(data.skills.programmingLanguages) + '</div>');
            if (data.skills && data.skills.frameworks) skillsList.push('<div class="skill-category"><strong>Frameworks:</strong><br>' + esc(data.skills.frameworks) + '</div>');
            if (data.skills && data.skills.tools) skillsList.push('<div class="skill-category"><strong>Tools:</strong><br>' + esc(data.skills.tools) + '</div>');
            if (data.skills && data.skills.softSkills) skillsList.push('<div class="skill-category"><strong>Soft Skills:</strong><br>' + esc(data.skills.softSkills) + '</div>');
            var skillsHtml = skillsList.join('');
            
            // Education for right column
            var eduHtml = (data.education || []).map(function(ed){
                var header = esc(ed.degree) || 'Degree';
                var institution = ed.institution ? ' - ' + esc(ed.institution) : '';
                var dates = [ed.startYear, ed.endYear].filter(Boolean).join(' - ');
                return '<div class="edu-item"><div class="edu-title">' + header + institution + '</div>' + 
                       (dates ? '<div class="edu-dates">' + esc(dates) + '</div>' : '') + '</div>';
            }).join('');
            
            // Experience for right column
            var expHtml = (data.experiences || []).map(function(e){
                var title = esc(e.title) || 'Position';
                var company = e.company ? ' at ' + esc(e.company) : '';
                var dates = [e.startDate, e.endDate].filter(Boolean).join(' - ');
                var desc = e.description ? '<div class="exp-desc">' + esc(e.description).replace(/\n/g,'<br>') + '</div>' : '';
                return '<div class="exp-item"><div class="exp-title">' + title + company + '</div>' + 
                       (dates ? '<div class="exp-dates">' + esc(dates) + '</div>' : '') + desc + '</div>';
            }).join('');
            
            // Projects for right column
            var projHtml = (data.projects || []).map(function(p){
                var name = esc(p.name) || 'Project';
                var role = p.role ? ' (' + esc(p.role) + ')' : '';
                var tech = p.technologies ? '<div class="proj-tech"><em>' + esc(p.technologies) + '</em></div>' : '';
                var desc = p.description ? '<div class="proj-desc">' + esc(p.description).replace(/\n/g,'<br>') + '</div>' : '';
                return '<div class="proj-item"><div class="proj-title">' + name + role + '</div>' + tech + desc + '</div>';
            }).join('');

            var html = '<!DOCTYPE html>\n<html>\n<head>\n<meta charset="UTF-8">\n<title>CV - ' + esc(fullName) + '</title>\n'
                + '<style>\n'
                + '* { margin: 0; padding: 0; box-sizing: border-box; }\n'
                + 'body { font-family: "Segoe UI", Arial, sans-serif; background: #f0f0f0; }\n'
                + '.cv-container { display: flex; max-width: 900px; margin: 20px auto; box-shadow: 0 5px 30px rgba(0,0,0,0.2); }\n'
                + '.sidebar { width: 280px; background: linear-gradient(135deg, #1a5276 0%, #154360 100%); color: white; padding: 0; }\n'
                + '.sidebar-content { padding: 30px 25px; }\n'
                + '.profile-pic-container { text-align: center; padding: 30px 25px; background: rgba(0,0,0,0.1); }\n'
                + '.profile-pic { width: 140px; height: 140px; border-radius: 50%; object-fit: cover; border: 4px solid rgba(255,255,255,0.3); }\n'
                + '.profile-pic-placeholder { width: 140px; height: 140px; border-radius: 50%; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; margin: 0 auto; border: 4px solid rgba(255,255,255,0.3); }\n'
                + '.profile-pic-placeholder span { font-size: 60px; color: rgba(255,255,255,0.8); }\n'
                + '.sidebar-name { font-size: 22px; font-weight: 700; margin-top: 15px; text-align: center; }\n'
                + '.sidebar-section { margin-bottom: 25px; }\n'
                + '.sidebar-title { font-size: 14px; font-weight: 700; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 15px; padding-bottom: 8px; border-bottom: 2px solid rgba(255,255,255,0.3); }\n'
                + '.contact-item { font-size: 13px; margin-bottom: 10px; display: flex; align-items: flex-start; gap: 8px; }\n'
                + '.contact-item i { width: 16px; }\n'
                + '.skill-category { font-size: 13px; margin-bottom: 12px; line-height: 1.5; }\n'
                + '.main-content { flex: 1; background: white; padding: 40px; }\n'
                + '.main-section { margin-bottom: 30px; }\n'
                + '.main-title { font-size: 20px; font-weight: 700; color: #1a5276; margin-bottom: 15px; padding-bottom: 8px; border-bottom: 2px solid #1a5276; text-transform: uppercase; letter-spacing: 1px; }\n'
                + '.objective-text { font-size: 14px; line-height: 1.7; color: #444; text-align: justify; }\n'
                + '.edu-item, .exp-item, .proj-item { margin-bottom: 18px; }\n'
                + '.edu-title, .exp-title, .proj-title { font-size: 15px; font-weight: 600; color: #333; }\n'
                + '.edu-dates, .exp-dates { font-size: 13px; color: #666; margin-top: 3px; }\n'
                + '.exp-desc, .proj-desc { font-size: 13px; color: #555; margin-top: 8px; line-height: 1.6; }\n'
                + '.proj-tech { font-size: 12px; color: #1a5276; margin-top: 4px; }\n'
                + '.no-print { margin-bottom: 20px; text-align: right; }\n'
                + '.no-print button { padding: 10px 20px; margin-left: 10px; border: none; border-radius: 5px; cursor: pointer; font-weight: 600; }\n'
                + '.btn-print { background: #1a5276; color: white; }\n'
                + '.btn-close { background: #6c757d; color: white; }\n'
                + '@page { size: A4; margin: 0; }\n'
                + '@media print { \n'
                + '  * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; color-adjust: exact !important; }\n'
                + '  html, body { margin: 0 !important; padding: 0 !important; width: 100%; height: 100%; }\n'
                + '  body { background: white !important; }\n'
                + '  .cv-container { box-shadow: none; margin: 0; max-width: 100%; width: 100%; min-height: 100vh; }\n'
                + '  .sidebar { background: linear-gradient(135deg, #1a5276 0%, #154360 100%) !important; color: white !important; }\n'
                + '  .profile-pic-container { background: rgba(0,0,0,0.1) !important; }\n'
                + '  .profile-pic-placeholder { background: rgba(255,255,255,0.2) !important; }\n'
                + '  .main-content { background: white !important; }\n'
                + '  .main-title { color: #1a5276 !important; border-bottom-color: #1a5276 !important; }\n'
                + '  .no-print { display: none !important; }\n'
                + '}\n'
                + '</style>\n</head>\n<body>\n'
                + '<div class="no-print">\n'
                + '  <button class="btn-print" onclick="window.print()">🖨️ Print / Save PDF</button>\n'
                + '  <button class="btn-close" onclick="window.close()">✕ Close</button>\n'
                + '</div>\n'
                + '<div class="cv-container">\n'
                + '  <div class="sidebar">\n'
                + '    <div class="profile-pic-container">\n'
                + '      ' + profilePicHtml + '\n'
                + '      <div class="sidebar-name">' + esc(fullName) + '</div>\n'
                + '    </div>\n'
                + '    <div class="sidebar-content">\n'
                + (contactHtml ? '      <div class="sidebar-section">\n        <div class="sidebar-title">Contact</div>\n        ' + contactHtml + '\n      </div>\n' : '')
                + (skillsHtml ? '      <div class="sidebar-section">\n        <div class="sidebar-title">Skills</div>\n        ' + skillsHtml + '\n      </div>\n' : '')
                + '    </div>\n'
                + '  </div>\n'
                + '  <div class="main-content">\n'
                + (data.summary ? '    <div class="main-section">\n      <div class="main-title">Objective</div>\n      <div class="objective-text">' + esc(data.summary).replace(/\n/g,'<br>') + '</div>\n    </div>\n' : '')
                + (eduHtml ? '    <div class="main-section">\n      <div class="main-title">Education</div>\n      ' + eduHtml + '\n    </div>\n' : '')
                + (expHtml ? '    <div class="main-section">\n      <div class="main-title">Experience</div>\n      ' + expHtml + '\n    </div>\n' : '')
                + (projHtml ? '    <div class="main-section">\n      <div class="main-title">Projects</div>\n      ' + projHtml + '\n    </div>\n' : '')
                + '  </div>\n'
                + '</div>\n'
                + '</body>\n</html>';

            return html;
        }
        
        function previewCV() {
            try {
                var data = collectCvData();
                var html = buildCvHtml(data);
                var win = window.open('', '_blank');
                if (!win) { 
                    alert('Pop-up blocked. Please allow pop-ups for this site.'); 
                    return; 
                }
                win.document.open();
                win.document.write(html);
                win.document.close();
                
                showToast('CV Preview opened successfully!', 'success');
            } catch (error) {
                console.error('Error generating preview:', error);
                showToast('Error generating preview: ' + error.message, 'error');
            }
        }

        function showToast(message, type = 'success') {
            // Simple toast implementation
            const toast = document.createElement('div');
            toast.className = 'success-message';
            toast.style.position = 'fixed';
            toast.style.top = '20px';
            toast.style.right = '20px';
            toast.style.zIndex = '9999';
            toast.style.backgroundColor = type === 'success' ? 'var(--bg-tertiary)' : 'var(--danger)';
            toast.style.color = 'var(--text-primary)';
            toast.style.border = '1px solid var(--border)';
            toast.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i> ${message}`;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        }

        // Theme Functionality
         function initializeTheme() {
            const savedTheme = localStorage.getItem('candihire-theme') || 'dark';
            applyTheme(savedTheme);
        }

        function applyTheme(theme) {
            document.documentElement.setAttribute('data-theme', theme);
            localStorage.setItem('candihire-theme', theme);
            updateThemeButton(theme);
        }

        function toggleTheme() {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            applyTheme(newTheme);
            
            // Add smooth transition effect
            document.body.style.transition = 'background-color 0.3s ease, color 0.3s ease';
            setTimeout(() => {
                document.body.style.transition = '';
            }, 300);
        }

        function updateThemeButton(theme) {
            const btn = document.getElementById('themeToggleBtn');
            const icon = document.getElementById('themeIcon');
            const text = document.getElementById('themeText');
            
            // Add theme data attribute for CSS styling
            if (btn) {
                btn.setAttribute('data-theme', theme);
            }
            
            if (theme === 'dark') {
                icon.className = 'fas fa-moon-stars';
                text.textContent = 'Light Mode';
                if (btn) {
                    btn.title = 'Switch to Light Mode';
                }
            } else {
                icon.className = 'fas fa-moon-stars';
                text.textContent = 'Dark Mode';
                if (btn) {
                    btn.title = 'Switch to Dark Mode';
                }
            }
        }

        // ============================================================
        //                    PROFILE EDITING
        // ============================================================
        let currentCandidateId = {{ $sessionCandidateId ?? 'null' }};

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
            
            fetch(`{{ url('/api/candidate-profile') }}?candidateId=${currentCandidateId}`)
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
                        
                        // Handle profile picture
                        if (candidate.ProfilePicture) {
                            const preview = document.getElementById('profilePicturePreview');
                            const currentPicture = document.getElementById('currentProfilePicture');
                            if (preview && currentPicture) {
                                preview.src = '{{ url("") }}/' + candidate.ProfilePicture;
                                currentPicture.style.display = 'block';
                            }
                        }
                    } else {
                        console.error('Failed to load profile:', data.message);
                        showToast('Failed to load profile data', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error loading profile:', error);
                    showToast('Network error loading profile', 'error');
                });
        }

        // Update profile
        function updateProfile() {
            console.log('Updating profile...');
            
            const form = document.getElementById('profileEditForm');
            const submitBtn = document.getElementById('submitProfileUpdate');
            const formData = new FormData(form);
            
            // Add candidate ID
            formData.append('candidateId', currentCandidateId);
            
            // Disable button and show loading
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            
            fetch('{{ url("/api/candidate-profile") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeProfileEditPopup();
                    showToast('Profile updated successfully!', 'success');
                    
                    // Update the display with server response data
                    if (data.fullName) {
                        const nameDisplay = document.getElementById('candidateNameDisplay');
                        if (nameDisplay) {
                            nameDisplay.textContent = data.fullName;
                        }
                        
                        // Update avatar
                        const avatar = document.getElementById('candidateAvatar');
                        if (avatar) {
                            if (data.profilePicture) {
                                avatar.style.backgroundImage = `url({{ url('') }}/${data.profilePicture})`;
                                avatar.style.backgroundSize = 'cover';
                                avatar.style.backgroundPosition = 'center';
                                avatar.textContent = '';
                            } else {
                                avatar.style.backgroundImage = '';
                                avatar.style.background = 'linear-gradient(135deg, var(--accent), var(--accent-2))';
                                avatar.textContent = data.fullName.charAt(0).toUpperCase();
                            }
                        }
                    }
                } else {
                    showToast(data.message || 'Failed to update profile', 'error');
                }
            })
            .catch(error => {
                console.error('Error updating profile:', error);
                showToast('Network error. Please try again.', 'error');
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
            
            if (popup) popup.style.display = 'none';
            document.body.style.overflow = 'auto';
            if (form) form.reset();
            
            // Hide profile picture preview
            const currentPicture = document.getElementById('currentProfilePicture');
            if (currentPicture) {
                currentPicture.style.display = 'none';
            }
        }

        // Initialize profile editing on page load
        document.addEventListener('DOMContentLoaded', function() {
            setupProfileEditing();
        });
    </script>

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
                           placeholder="Enter years of experience" min="0" max="50">
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
</body>
</html>

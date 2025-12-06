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
                    <div id="candidateAvatar" style="width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 16px; {{ $candidateProfilePicture ? 'background-image: url(' . $candidateProfilePicture . '); background-size: cover; background-position: center;' : 'background: linear-gradient(135deg, var(--accent), var(--accent-2));' }}">
                        {{ $candidateProfilePicture ? '' : strtoupper(substr($candidateName, 0, 1)) }}
                    </div>
                    <div>
                        <div style="color: var(--text-primary); font-weight: 600; font-size: 14px;">Welcome back!</div>
                        <div id="candidateNameDisplay" style="color: var(--text-secondary); font-size: 12px;">{{ $candidateName }}</div>
                    </div>
                </div>
                <button id="editProfileBtn" 
    style="background: var(--accent); 
           color: white; 
           border: none; 
           border-radius: 6px; 
           padding: 8px 12px;
           font-size: 12px; 
           cursor: pointer; 
           margin-top: 10px; 
           width: 100%; 
           transition: background 0.2s;">
    <i class="fas fa-user-edit" style="margin-right: 6px;"></i>Edit Profile
</button>
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
                <a href="{{ url('/logout') }}" class="logout-btn"><i class="fas fa-sign-out-alt" style="margin-right:8px;"></i>Logout</a>
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
                                <label class="form-label">Professional Summary</label>
                                <textarea name="summary" class="form-textarea" placeholder="Write a brief summary of your professional background and goals..."></textarea>
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
            const element = document.getElementById('cvForm');
            const opt = {
                margin: 1,
                filename: 'my-cv.pdf',
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2 },
                jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' }
            };
            
            // Basic client-side PDF generation
            html2pdf().set(opt).from(element).save().then(function() {
                 showToast('PDF downloaded successfully!', 'success');
            }).catch(function(error) {
                 showToast('Error generating PDF.', 'error');
                 console.error(error);
            });
        }
        
        function previewCV() {
             // Mock preview
             alert("Preview functionality would open a modal with the formatted CV.");
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
        }

        function updateThemeButton(theme) {
            const btn = document.getElementById('themeToggleBtn');
            const icon = document.getElementById('themeIcon');
            const text = document.getElementById('themeText');
            
            if (theme === 'dark') {
                icon.className = 'fas fa-moon';
                text.textContent = 'Dark Mode';
            } else {
                icon.className = 'fas fa-sun';
                text.textContent = 'Light Mode';
            }
        }
    </script>
</body>
</html>

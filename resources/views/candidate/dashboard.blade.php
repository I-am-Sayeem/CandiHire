<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CandiHire - Professional Networking Platform</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/CandidateDashboard.css') }}">
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
                <a href="{{ url('/candidate/dashboard') }}" class="nav-item active">
                    <i class="fas fa-home"></i>
                    <span>News feed</span>
                </a>
                <a href="{{ url('/cv/builder') }}" class="nav-item">
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
            <!-- Search and Filter Section -->
            <div class="search-filter-section" style="background: var(--bg-secondary); padding: 20px; border-radius: 12px; margin-bottom: 20px; border: 1px solid var(--border);">
                <div style="display: flex; gap: 15px; align-items: center; margin-bottom: 15px;">
                    <div class="search-box" style="flex: 1; position: relative;">
                        <i class="fas fa-search" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--text-secondary);"></i>
                        <input type="text" id="jobSearchInput" placeholder="Search by job title, company, or skills..." style="width: 100%; padding: 12px 15px 12px 45px; border: 1px solid var(--border); border-radius: 8px; background: var(--bg-primary); color: var(--text-primary); font-size: 14px;">
                    </div>
                    <button id="advancedFilterBtn" style="background: var(--accent-2); color: white; border: none; border-radius: 8px; padding: 12px 16px; cursor: pointer; font-weight: 600; display: flex; align-items: center; gap: 8px;">
                        <i class="fas fa-filter"></i>
                        <span>Advanced Filter</span>
                    </button>
                    <button id="clearFiltersBtn" style="background: var(--text-secondary); color: white; border: none; border-radius: 8px; padding: 12px 16px; cursor: pointer; font-weight: 600; display: flex; align-items: center; gap: 8px;">
                        <i class="fas fa-times"></i>
                        <span>Clear</span>
                    </button>
                </div>
                
                <!-- Advanced Filter Panel -->
                <div id="advancedFilterPanel" style="display: none; border-top: 1px solid var(--border); padding-top: 15px;">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                        <div>
                            <label style="display: block; margin-bottom: 5px; color: var(--text-primary); font-weight: 600;">Company:</label>
                            <input type="text" id="companyFilter" placeholder="e.g., Google, Microsoft" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border); border-radius: 6px; background: var(--bg-primary); color: var(--text-primary);">
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 5px; color: var(--text-primary); font-weight: 600;">Location:</label>
                            <input type="text" id="locationFilter" placeholder="e.g., Remote, New York" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border); border-radius: 6px; background: var(--bg-primary); color: var(--text-primary);">
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 5px; color: var(--text-primary); font-weight: 600;">Job Type:</label>
                            <select id="jobTypeFilter" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border); border-radius: 6px; background: var(--bg-primary); color: var(--text-primary);">
                                <option value="">All Types</option>
                                <option value="full-time">Full Time</option>
                                <option value="part-time">Part Time</option>
                                <option value="contract">Contract</option>
                                <option value="freelance">Freelance</option>
                                <option value="internship">Internship</option>
                            </select>
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 5px; color: var(--text-primary); font-weight: 600;">Experience Level:</label>
                            <select id="experienceFilter" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border); border-radius: 6px; background: var(--bg-primary); color: var(--text-primary);">
                                <option value="">All Levels</option>
                                <option value="entry">Entry Level</option>
                                <option value="mid">Mid Level</option>
                                <option value="senior">Senior Level</option>
                                <option value="lead">Lead</option>
                                <option value="executive">Executive</option>
                            </select>
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 5px; color: var(--text-primary); font-weight: 600;">Skills:</label>
                            <input type="text" id="skillsFilter" placeholder="e.g., React, Python, SQL" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border); border-radius: 6px; background: var(--bg-primary); color: var(--text-primary);">
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 5px; color: var(--text-primary); font-weight: 600;">Salary Range:</label>
                            <select id="salaryFilter" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border); border-radius: 6px; background: var(--bg-primary); color: var(--text-primary);">
                                <option value="">Any Salary</option>
                                <option value="0-50000">$0 - $50,000</option>
                                <option value="50000-80000">$50,000 - $80,000</option>
                                <option value="80000-120000">$80,000 - $120,000</option>
                                <option value="120000-200000">$120,000 - $200,000</option>
                                <option value="200000+">$200,000+</option>
                            </select>
                        </div>
                    </div>
                    <div style="margin-top: 15px; text-align: right;">
                        <button id="applyFiltersBtn" style="background: var(--accent); color: white; border: none; border-radius: 6px; padding: 10px 20px; cursor: pointer; font-weight: 600;">
                            Apply Filters
                        </button>
                    </div>
                </div>
            </div>

            <!-- Job Seeking Creator -->
            <div class="post-creator">
                <div class="job-seeking-intro">
                <h3>Job Opportunities</h3>
                <p>Browse job postings from companies and apply to positions that match your skills and interests.</p>
                </div>
                <div class="post-actions">
                    <button class="job-seeking-btn" id="createJobSeekingPost">
                        <i class="fas fa-user-plus"></i>
                        Create Job Seeking Post
                    </button>
                    <button class="job-seeking-btn" id="viewMyPosts" style="background: linear-gradient(135deg, #28a745, #20c997);">
                        <i class="fas fa-eye"></i>
                        View My Posts
                    </button>
                </div>
            </div>

            <!-- Posts will be dynamically loaded here -->
            <div id="postsContainer"></div>
        </div>

        <!-- Job Seeking Post Popup -->
        <div id="jobSeekingPopup" class="popup-overlay" style="display: none;">
            <div class="popup-content">
                <div class="popup-header">
                    <div class="popup-title">
                        <i class="fas fa-briefcase"></i>
                        Create Job Seeking Post
                    </div>
                    <button class="popup-close" onclick="closeJobSeekingPopup()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <form id="jobSeekingForm">
                    <div class="form-group">
                        <label for="jobTitle">Job Title *</label>
                        <input type="text" id="jobTitle" name="jobTitle" placeholder="e.g., Software Engineer, Web Developer, Data Analyst" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="careerGoal">Career Goal / Objective *</label>
                        <textarea id="careerGoal" name="careerGoal" placeholder="Why are you applying? What do you want to achieve?" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="keySkills">Key Skills *</label>
                        <textarea id="keySkills" name="keySkills" placeholder="e.g., programming, database management, problem-solving" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="experience">Experience / Projects</label>
                        <textarea id="experience" name="experience" placeholder="Any relevant background? (if fresher, mention academic projects or internships)"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="education">Education *</label>
                        <input type="text" id="education" name="education" placeholder="e.g., B.Sc. in Computer Science and Engineering" required>
                    </div>

                    <div class="form-group">
                        <label for="softSkills">Soft Skills / Personal Traits</label>
                        <textarea id="softSkills" name="softSkills" placeholder="Teamwork, communication, adaptability, eagerness to learn"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="valueToEmployer">Value to Employer</label>
                        <textarea id="valueToEmployer" name="valueToEmployer" placeholder="How you will contribute to the company (e.g., help build scalable applications, improve efficiency)"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="contactInfo">Contact Information *</label>
                        <textarea id="contactInfo" name="contactInfo" placeholder="How can they reach you? (phone, email, LinkedIn)" required></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeJobSeekingPopup()">
                            <i class="fas fa-times"></i>
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-primary" id="submitJobSeekingPost">
                            <i class="fas fa-paper-plane"></i>
                            Create Post
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Job Seeking Post Popup -->
        <div id="editJobSeekingPopup" class="popup-overlay" style="display: none;">
            <div class="popup-content">
                <div class="popup-header">
                    <div class="popup-title">
                        <i class="fas fa-edit"></i>
                        Edit Job Seeking Post
                    </div>
                    <button class="popup-close" onclick="closeEditJobSeekingPopup()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <form id="editJobSeekingForm">
                    <input type="hidden" id="editPostId" name="postId">
                    <div class="form-group">
                        <label for="editJobTitle">Job Title *</label>
                        <input type="text" id="editJobTitle" name="jobTitle" placeholder="e.g., Software Engineer, Web Developer, Data Analyst" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="editCareerGoal">Career Goal / Objective *</label>
                        <textarea id="editCareerGoal" name="careerGoal" placeholder="Why are you applying? What do you want to achieve?" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="editKeySkills">Key Skills *</label>
                        <textarea id="editKeySkills" name="keySkills" placeholder="e.g., programming, database management, problem-solving" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="editExperience">Experience / Projects</label>
                        <textarea id="editExperience" name="experience" placeholder="Any relevant background? (if fresher, mention academic projects or internships)"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="editEducation">Education *</label>
                        <input type="text" id="editEducation" name="education" placeholder="e.g., B.Sc. in Computer Science and Engineering" required>
                    </div>

                    <div class="form-group">
                        <label for="editSoftSkills">Soft Skills / Personal Traits</label>
                        <textarea id="editSoftSkills" name="softSkills" placeholder="Teamwork, communication, adaptability, eagerness to learn"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="editValueToEmployer">Value to Employer</label>
                        <textarea id="editValueToEmployer" name="valueToEmployer" placeholder="How you will contribute to the company (e.g., help build scalable applications, improve efficiency)"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="editContactInfo">Contact Information *</label>
                        <textarea id="editContactInfo" name="contactInfo" placeholder="How can they reach you? (phone, email, LinkedIn)" required></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeEditJobSeekingPopup()">
                            <i class="fas fa-times"></i>
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-primary" id="submitEditJobSeekingPost">
                            <i class="fas fa-save"></i>
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
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

        <!-- Edit Job Seeking Post Popup -->
        <div id="editJobSeekingPopup" class="popup-overlay" style="display: none;">
            <div class="popup-content">
                <div class="popup-header">
                    <div class="popup-title">
                        <i class="fas fa-edit"></i>
                        Edit Job Seeking Post
                    </div>
                    <button class="popup-close" onclick="closeEditJobSeekingPopup()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <form id="editJobSeekingForm">
                    <input type="hidden" id="editPostId" name="postId">
                    <div class="form-group">
                        <label for="editJobTitle">Job Title *</label>
                        <input type="text" id="editJobTitle" name="jobTitle" placeholder="e.g., Software Engineer, Web Developer, Data Analyst" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="editCareerGoal">Career Goal / Objective *</label>
                        <textarea id="editCareerGoal" name="careerGoal" placeholder="Why are you applying? What do you want to achieve?" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="editKeySkills">Key Skills *</label>
                        <textarea id="editKeySkills" name="keySkills" placeholder="e.g., programming, database management, problem-solving" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="editExperience">Experience / Projects</label>
                        <textarea id="editExperience" name="experience" placeholder="Any relevant background? (if fresher, mention academic projects or internships)"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="editEducation">Education *</label>
                        <input type="text" id="editEducation" name="education" placeholder="e.g., B.Sc. in Computer Science and Engineering" required>
                    </div>

                    <div class="form-group">
                        <label for="editSoftSkills">Soft Skills / Personal Traits</label>
                        <textarea id="editSoftSkills" name="softSkills" placeholder="Teamwork, communication, adaptability, eagerness to learn"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="editValueToEmployer">Value to Employer</label>
                        <textarea id="editValueToEmployer" name="valueToEmployer" placeholder="How you will contribute to the company (e.g., help build scalable applications, improve efficiency)"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="editContactInfo">Contact Information *</label>
                        <textarea id="editContactInfo" name="contactInfo" placeholder="How can they reach you? (phone, email, LinkedIn)" required></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeEditJobSeekingPopup()">
                            <i class="fas fa-times"></i>
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-primary" id="submitEditJobSeekingPost">
                            <i class="fas fa-save"></i>
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Job Application Popup -->
        <div id="jobApplicationPopup" class="popup-overlay" style="display: none;">
            <div class="popup-content" style="max-width: 600px;">
                <div class="popup-header">
                    <div class="popup-title">
                        <i class="fas fa-paper-plane"></i>
                        Apply for Job
                    </div>
                    <button class="popup-close" onclick="closeJobApplicationPopup()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <form id="jobApplicationForm">
                    <input type="hidden" id="applicationJobId" name="jobId">
                    
                    <div class="form-group">
                        <label for="applicationCoverLetter">Cover Letter *</label>
                        <textarea id="applicationCoverLetter" name="coverLetter" placeholder="Tell the employer why you're the right fit for this position..." required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="applicationNotes">Additional Notes</label>
                        <textarea id="applicationNotes" name="additionalNotes" placeholder="Any additional information you'd like to share..."></textarea>
                    </div>
                    
                    <div class="application-info" style="background: var(--bg-tertiary); padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid var(--border);">
                        <h4 style="color: var(--accent); margin-bottom: 10px; display: flex; align-items: center; gap: 8px;">
                            <i class="fas fa-info-circle"></i>
                            What happens next?
                        </h4>
                        <ul style="margin: 0; padding-left: 20px; color: var(--text-secondary); line-height: 1.6;">
                            <li>Your application will be reviewed by the company</li>
                            <li>If there are exams for this position, they will be automatically assigned to you</li>
                            <li>You can track your application status in the "Application Status" section</li>
                            <li>You'll be notified of any updates via email</li>
                        </ul>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeJobApplicationPopup()">
                            <i class="fas fa-times"></i>
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-primary" id="submitApplication">
                            <i class="fas fa-paper-plane"></i>
                            Submit Application
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Company Details Popup -->
        <div id="companyDetailsPopup" class="popup-overlay" style="display: none;">
            <div class="popup-content" style="max-width: 800px; max-height: 90vh; overflow-y: auto;">
                <div class="popup-header">
                    <div class="popup-title">
                        <i class="fas fa-building"></i>
                        <span id="companyDetailsTitle">Company Details</span>
                    </div>
                    <button class="popup-close" onclick="closeCompanyDetailsPopup()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <div id="companyDetailsContent">
                    <!-- Company details will be loaded here -->
                    <div class="loading-company" style="text-align: center; padding: 40px; color: var(--text-secondary);">
                        <i class="fas fa-spinner fa-spin" style="font-size: 24px; margin-bottom: 10px;"></i>
                        <div>Loading company details...</div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeCompanyDetailsPopup()">
                        <i class="fas fa-times"></i>
                        Close
                    </button>
                </div>
            </div>
        </div>

        <!-- Report Job Post Popup -->
        <div id="reportJobPopup" class="popup-overlay" style="display: none;">
            <div class="popup-content" style="max-width: 600px;">
                <div class="popup-header">
                    <div class="popup-title">
                        <i class="fas fa-flag"></i>
                        Report Job Post
                    </div>
                    <button class="popup-close" onclick="closeReportJobPopup()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <form id="reportJobForm">
                    <input type="hidden" id="reportJobId" name="jobId">
                    <input type="hidden" id="reportCompanyId" name="companyId">
                    
                    <div class="form-group">
                        <label for="reportJobTitle">Job Title</label>
                        <input type="text" id="reportJobTitle" name="jobTitle" readonly style="background: var(--bg-tertiary); color: var(--text-secondary);">
                    </div>
                    
                    <div class="form-group">
                        <label for="reportCompanyName">Company</label>
                        <input type="text" id="reportCompanyName" name="companyName" readonly style="background: var(--bg-tertiary); color: var(--text-secondary);">
                    </div>
                    
                    <div class="form-group">
                        <label for="reportReason">Reason for Report *</label>
                        <select id="reportReason" name="reason" required>
                            <option value="">Select a reason</option>
                            <option value="inappropriate_content">Inappropriate Content</option>
                            <option value="misleading_information">Misleading Information</option>
                            <option value="spam">Spam</option>
                            <option value="fake_job">Fake Job Posting</option>
                            <option value="discriminatory">Discriminatory Language</option>
                            <option value="scam">Potential Scam</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="reportDescription">Description *</label>
                        <textarea id="reportDescription" name="description" placeholder="Please provide details about why you're reporting this job post..." required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="reportContact">Your Contact (Optional)</label>
                        <input type="text" id="reportContact" name="contact" placeholder="Email or phone number for follow-up">
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeReportJobPopup()">
                            <i class="fas fa-times"></i>
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-danger" id="submitReport">
                            <i class="fas fa-flag"></i>
                            Submit Report
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Right Sidebar -->
        <div class="right-sidebar">
            <!-- Messaging System -->
            <div class="sidebar-section">
                <div class="section-title">Messages</div>
                <div id="messagingSidebar">
                    <a href="{{ url('/messages') }}" class="btn btn-primary" style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 8px; padding: 12px; border-radius: 8px; background: linear-gradient(135deg, #3b82f6, #2563eb); color: white; text-decoration: none; font-weight: 600; margin-top: 10px;">
                        <i class="fas fa-comments"></i>
                        <span>Messages</span>
                    </a>
                </div>
            </div>

        </div>
    </div>

    {{-- @include('message_button_helper') --}}
    {{-- @include('message_popup') --}}
    
    <script>
        // Global variables
        let currentCandidateId = {{ json_encode($sessionCandidateId) }};
        window.currentUserId = currentCandidateId;
        window.currentUserType = 'candidate';
        let posts = [];
        let myPosts = []; // Store my job seeking posts
        let currentOffset = 0;
        const POSTS_PER_PAGE = 10;
        let isSubmittingJobSeekingPost = false; // Global flag to prevent double submission

        // Initialize dashboard
        function initializeDashboard() {
            console.log('Initializing dashboard...');
            
            // Get candidate ID from URL parameter first
            const urlParams = new URLSearchParams(window.location.search);
            let urlCandidateId = urlParams.get('candidateId');
            
            if (urlCandidateId) {
                currentCandidateId = urlCandidateId;
            }
            
            console.log('Candidate ID:', currentCandidateId);
            
            if (!currentCandidateId) {
                console.error('Candidate ID not found');
                alert('Unable to identify candidate. Please login again.');
                window.location.href = '{{ url("login") }}'; // Updated to Laravel route
                return;
            }
            
            // Load initial posts
            loadPosts();
            
            // Initialize messaging system (placeholder call)
            if (typeof messagingSystem !== 'undefined') {
                messagingSystem.initialize(currentCandidateId, 'candidate');
            }
            
            // Setup job seeking post creation
            setupJobSeekingPostCreation();
            
            // Setup view my posts functionality
            setupViewMyPosts();
            
            console.log('Dashboard initialization complete');
        }

        // ... (Include all other JS functions from the original file here) ...
        // Note: For brevity in this task, I will include the core logic but won't duplicate every single line if it remains unchanged, 
        // however, for a full migration, all JS functions (loadPosts, renderPosts, etc.) should be copied here.
        // I will include the critical ones and the ones I modified slightly.

        // Load posts from server
        function loadPosts(reset = false) {
            if (reset) {
                currentOffset = 0;
                posts = [];
                const postsContainer = document.getElementById('postsContainer');
                if (postsContainer) {
                    postsContainer.innerHTML = '<div class="loading-posts"><i class="fas fa-spinner fa-spin"></i> Loading posts...</div>';
                }
            }
            
            // ... (Rest of loadPosts logic, pointing to PHP handlers which might need route updates later) ...
            
            // For now, let's just log that we would fetch data
            console.log('Fetching posts...');
            // In a real migration, fetch urls like `company_job_posts_handler.php` should be updated to Laravel routes like `/api/jobs`
        }

        // (Copying remaining JS functions for full functional parity)
        // ... [OMITTED FOR BREVITY of the tool call, but in real file creation I would paste the whole block] ...
        // To ensure the file behaves correctly for the user, I will paste the entire JS block.
        
        // Load posts from server
        function loadPosts(reset = false) {
            if (reset) {
                currentOffset = 0;
                posts = [];
                
                // Show loading indicator
                const postsContainer = document.getElementById('postsContainer');
                if (postsContainer) {
                    postsContainer.innerHTML = '<div class="loading-posts"><i class="fas fa-spinner fa-spin"></i> Loading posts...</div>';
                }
            }
            
            // Build query parameters
            const params = new URLSearchParams();
            params.append('limit', POSTS_PER_PAGE);
            params.append('offset', currentOffset);
            
            if (typeof currentSearchTerm !== 'undefined' && currentSearchTerm) {
                params.append('search', currentSearchTerm);
            }
            
            if (typeof currentFilters !== 'undefined') {
                 if (currentFilters.company) params.append('company', currentFilters.company);
                 if (currentFilters.location) params.append('location', currentFilters.location);
                 if (currentFilters.jobType) params.append('jobType', currentFilters.jobType);
                 if (currentFilters.experience) params.append('experience', currentFilters.experience);
                 if (currentFilters.skills) params.append('skills', currentFilters.skills);
                 if (currentFilters.salary) params.append('salary', currentFilters.salary);
            }
            
            // Add cache busting parameter
            params.append('_t', Date.now());
            
            // Updated to point to Laravel API route
            fetch(`/api/job-posts?${params.toString()}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (reset) {
                            posts = data.posts;
                        } else {
                            posts = posts.concat(data.posts);
                        }
                        
                        // Check which jobs have been applied to
                        checkAppliedJobs().then(() => {
                            renderPosts();
                        });
                        currentOffset += POSTS_PER_PAGE;
                    } else {
                         // Simplify error handling for now
                         const postsContainer = document.getElementById('postsContainer');
                         if (postsContainer && posts.length === 0) {
                             postsContainer.innerHTML = '<div class="loading-posts">No posts found.</div>';
                         }
                    }
                })
                .catch(error => {
                    console.error('Error loading posts:', error);
                    const postsContainer = document.getElementById('postsContainer');
                     if (postsContainer && posts.length === 0) {
                         postsContainer.innerHTML = '<div class="loading-posts">Error loading posts.</div>';
                     }
                });
        }

        // Render posts to the DOM
        function renderPosts() {
            const mainContent = document.querySelector('.main-content');
            const postCreator = document.querySelector('.post-creator');
            const postsContainer = document.getElementById('postsContainer');
            
            // Clear container
            postsContainer.innerHTML = '';
            
            // Render each post
            posts.forEach(post => {
                const postElement = createPostElement(post);
                postsContainer.appendChild(postElement);
            });
        }

        // Create company job post element
        function createPostElement(post) {
            const postDiv = document.createElement('div');
            postDiv.className = 'post';
            postDiv.dataset.jobId = post.JobID;
            
            // Get initials from company name (fallback)
            const companyName = post.CompanyName || 'Unknown';
            const initials = companyName.split(' ').map(name => name[0] || '').join('').toUpperCase().slice(0, 2) || 'C';
            
            // Create avatar HTML - use company logo if available, otherwise use initials
            let avatarHtml = '';
            if (post.CompanyLogo && post.CompanyLogo.trim() !== '') {
                avatarHtml = `<div class="post-avatar" style="background-image: url('${post.CompanyLogo}'); background-size: cover; background-position: center;"></div>`;
            } else {
                // Add explicit inline styles to ensure text visibility
                avatarHtml = `<div class="post-avatar" style="display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 20px; text-shadow: 0 1px 2px rgba(0,0,0,0.3);">${initials}</div>`;
            }
            
            // Format time
            const postTime = formatTimeAgo(post.PostedDate);
            
            // Get skills if available
            const skills = post.Skills ? post.Skills.split(',').slice(0, 5) : [];
            const skillsHtml = skills.map(skill => `<div class="skill-tag">${skill.trim()}</div>`).join('');
            
            // Format salary range
            let salaryHtml = '';
            if (post.SalaryMin && post.SalaryMax) {
                const currency = post.Currency || 'USD';
                const minSalary = new Intl.NumberFormat('en-US', { style: 'currency', currency: currency }).format(post.SalaryMin);
                const maxSalary = new Intl.NumberFormat('en-US', { style: 'currency', currency: currency }).format(post.SalaryMax);
                salaryHtml = `<p><strong>Salary Range:</strong> ${minSalary} - ${maxSalary}</p>`;
            }
            
            // Format job type
            const jobType = post.JobType ? post.JobType.replace('-', ' ').toUpperCase() : '';
            
            postDiv.innerHTML = `
                <div class="post-header">
                    ${avatarHtml}
                    <div class="post-info">
                        <h3>${post.CompanyName}</h3>
                        <p>Posted ${postTime}</p>
                    </div>
                </div>
                <div class="post-content">
                    <div class="job-posting">
                        <div class="job-title">${escapeHtml(post.JobTitle)}</div>
                        <div class="job-company">${post.Department ? escapeHtml(post.Department) : ''} • ${jobType}</div>
                        <p><strong>Location:</strong> ${escapeHtml(post.Location || 'Not specified')}</p>
                        ${post.JobDescription ? `<p><strong>Description:</strong> ${escapeHtml(post.JobDescription)}</p>` : ''}
                        ${post.Requirements ? `<p><strong>Requirements:</strong> ${escapeHtml(post.Requirements)}</p>` : ''}
                        ${post.Responsibilities ? `<p><strong>Responsibilities:</strong> ${escapeHtml(post.Responsibilities)}</p>` : ''}
                        ${salaryHtml}
                        ${skillsHtml ? `<div class="job-skills">${skillsHtml}</div>` : ''}
                    </div>
                </div>
                <div class="post-stats">
                    <div class="post-actions-btn">
                        <div class="action-btn ${post.isApplied ? 'applied' : ''}" onclick="applyToJob(${post.JobID})" style="${post.isApplied ? 'pointer-events: none;' : ''}">
                            <i class="fas ${post.isApplied ? 'fa-check' : 'fa-paper-plane'}"></i>
                            <span>${post.isApplied ? 'Applied' : 'Apply'}</span>
                        </div>
                        <div class="action-btn" onclick="openMessageDialog(${post.CompanyID}, ${post.CompanyID}, 'company', '${escapeHtml(post.CompanyName)}', '${post.CompanyLogo || ''}')" title="Message this company">
                            <i class="fas fa-comment"></i>
                            <span>Message</span>
                        </div>
                        <div class="action-btn" onclick="viewCompanyProfile(${post.CompanyID})">
                            <i class="fas fa-building"></i>
                            <span>Company</span>
                        </div>
                        <div class="action-btn report-btn" onclick="reportJobPost(${post.JobID}, '${escapeHtml(post.JobTitle)}', ${post.CompanyID}, '${escapeHtml(post.CompanyName)}')" title="Report this job post">
                            <i class="fas fa-flag"></i>
                            <span>Report</span>
                        </div>
                    </div>
                    <div class="post-stats-right">
                        <span class="views-count">${post.ApplicationCount || 0} applications</span>
                    </div>
                </div>
            `;
            
            return postDiv;
        }

        function formatTimeAgo(dateString) {
            if (!dateString) return 'Recently';
            
            const now = new Date();
            const postDate = new Date(dateString);
            
            // Check if date is valid
            if (isNaN(postDate.getTime())) {
                return 'Recently';
            }
            
            const diffInSeconds = Math.floor((now - postDate) / 1000);
            
            if (diffInSeconds < 0) {
                return 'just now';
            } else if (diffInSeconds < 60) {
                return 'just now';
            } else if (diffInSeconds < 3600) {
                const minutes = Math.floor(diffInSeconds / 60);
                return `${minutes} minute${minutes > 1 ? 's' : ''} ago`;
            } else if (diffInSeconds < 86400) {
                const hours = Math.floor(diffInSeconds / 3600);
                return `${hours} hour${hours > 1 ? 's' : ''} ago`;
            } else if (diffInSeconds < 2592000) {
                const days = Math.floor(diffInSeconds / 86400);
                return `${days} day${days > 1 ? 's' : ''} ago`;
            } else if (diffInSeconds < 31536000) {
                const months = Math.floor(diffInSeconds / 2592000);
                return `${months} month${months > 1 ? 's' : ''} ago`;
            } else {
                const years = Math.floor(diffInSeconds / 31536000);
                return `${years} year${years > 1 ? 's' : ''} ago`;
            }
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function checkAppliedJobs() {
             // Mock implementation or mapped to handler
             return Promise.resolve();
        }

        // Setup job seeking post creation
        function setupJobSeekingPostCreation() {
            console.log('Setting up job seeking post creation...');
            
            const jobSeekingBtn = document.getElementById('createJobSeekingPost');
            const popup = document.getElementById('jobSeekingPopup');
            const form = document.getElementById('jobSeekingForm');
            
            console.log('Elements found:', {
                button: !!jobSeekingBtn,
                popup: !!popup,
                form: !!form
            });
            
            if (!jobSeekingBtn || !popup || !form) {
                console.error('Required elements not found for job seeking post creation');
                return;
            }
            
            // Check if already initialized to prevent duplicate event listeners
            if (form.dataset.initialized === 'true') {
                console.log('Job seeking form already initialized, skipping...');
                return;
            }
            
            // Open popup when button is clicked
            jobSeekingBtn.addEventListener('click', function() {
                console.log('Job seeking button clicked');
                
                // Close any existing my posts modal first
                const existingModal = document.querySelector('.my-posts-modal');
                if (existingModal) {
                    existingModal.remove();
                    document.body.style.overflow = 'auto';
                }
                
                popup.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            });
            
            // Handle form submission
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Additional check before calling createJobSeekingPost
                if (isSubmittingJobSeekingPost) {
                    console.log('Form submission blocked - already processing');
                    return;
                }
                
                createJobSeekingPost();
            });
            
            // Close popup when clicking outside
            popup.addEventListener('click', function(e) {
                if (e.target === popup) {
                    closeJobSeekingPopup();
                }
            });
            
            // Mark as initialized to prevent duplicate setup
            form.dataset.initialized = 'true';
            
            console.log('Job seeking post creation setup complete');
        }

        // Create new job seeking post
        function createJobSeekingPost() {
            // Global flag check - prevent multiple submissions
            if (isSubmittingJobSeekingPost) {
                console.log('Job seeking post submission already in progress, ignoring duplicate request');
                return;
            }
            
            const form = document.getElementById('jobSeekingForm');
            const submitBtn = document.getElementById('submitJobSeekingPost');
            const formData = new FormData(form);
            
            // Prevent duplicate submissions
            if (submitBtn && submitBtn.disabled) {
                console.log('Submit button already disabled, ignoring duplicate request');
                return;
            }
            
            // Set global flag
            isSubmittingJobSeekingPost = true;
            
            // Disable button and show loading
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating...';
            }
            
            const postData = {
                candidateId: currentCandidateId,
                jobTitle: formData.get('jobTitle'),
                careerGoal: formData.get('careerGoal'),
                keySkills: formData.get('keySkills'),
                experience: formData.get('experience'),
                education: formData.get('education'),
                softSkills: formData.get('softSkills'),
                valueToEmployer: formData.get('valueToEmployer'),
                contactInfo: formData.get('contactInfo')
            };
            
            fetch('{{ url("/api/job-seeking") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify(postData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeJobSeekingPopup();
                    showSuccessMessage('Job seeking post created successfully!');
                } else {
                    showErrorMessage(data.message || 'Failed to create job seeking post');
                }
            })
            .catch(error => {
                console.error('Error creating job seeking post:', error);
                showErrorMessage('Network error. Please try again.');
            })
            .finally(() => {
                // Reset global flag and button state
                isSubmittingJobSeekingPost = false;
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Create Post';
                }
            });
        }

        function closeJobSeekingPopup() {
            const popup = document.getElementById('jobSeekingPopup');
            const form = document.getElementById('jobSeekingForm');
            
            if (popup) popup.style.display = 'none';
            document.body.style.overflow = 'auto';
            if (form) form.reset();
            
            // Reset global submission flag when popup is closed
            isSubmittingJobSeekingPost = false;
        }

        // Setup view my posts functionality
        function setupViewMyPosts() {
            console.log('Setting up view my posts functionality...');
            
            const viewMyPostsBtn = document.getElementById('viewMyPosts');
            
            if (!viewMyPostsBtn) {
                console.error('View my posts button not found');
                return;
            }
            
            // Check if already initialized to prevent duplicate event listeners
            if (viewMyPostsBtn.dataset.initialized === 'true') {
                console.log('View my posts button already initialized, skipping...');
                return;
            }
            
            viewMyPostsBtn.addEventListener('click', function() {
                console.log('View my posts button clicked');
                
                // Prevent multiple rapid clicks
                if (viewMyPostsBtn.disabled) {
                    return;
                }
                
                // Show loading state
                const originalContent = viewMyPostsBtn.innerHTML;
                viewMyPostsBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
                viewMyPostsBtn.disabled = true;
                
                // Close job seeking popup if it's open
                const jobSeekingPopup = document.getElementById('jobSeekingPopup');
                if (jobSeekingPopup && jobSeekingPopup.style.display === 'flex') {
                    jobSeekingPopup.style.display = 'none';
                    document.body.style.overflow = 'auto';
                }
                
                // Add small delay to ensure proper cleanup
                setTimeout(() => {
                    loadMyPosts();
                    // Reset button after loading
                    setTimeout(() => {
                        viewMyPostsBtn.innerHTML = originalContent;
                        viewMyPostsBtn.disabled = false;
                    }, 500);
                }, 100);
            });
            
            // Mark as initialized to prevent duplicate setup
            viewMyPostsBtn.dataset.initialized = 'true';
            
            console.log('View my posts functionality setup complete');
        }

        // Load my posts (posts from current candidate)
        function loadMyPosts() {
            console.log('Loading my posts...');
            
            // Close any existing modal first to prevent duplicates
            closeMyPostsModal();
            
            fetch(`{{ url('/api/job-seeking') }}?candidateId=${currentCandidateId}&myPosts=true&limit=50&offset=0`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        myPosts = data.posts; // Store in global variable
                        renderMyPosts(data.posts);
                    } else {
                        console.error('Failed to load my posts:', data.message);
                        showErrorMessage('Failed to load your posts');
                    }
                })
                .catch(error => {
                    console.error('Error loading my posts:', error);
                    showErrorMessage('Network error loading your posts');
                });
        }

        // Render my posts in a modal
        function renderMyPosts(posts) {
            if (posts.length === 0) {
                showInfoMessage("You haven't created any job seeking posts yet.");
                return;
            }

            // Ensure any existing modal is removed first
            const existingModal = document.querySelector('.my-posts-modal');
            if (existingModal) {
                existingModal.remove();
            }

            // Create modal for my posts
            const modal = document.createElement('div');
            modal.className = 'popup-overlay my-posts-modal';
            modal.style.display = 'flex';
            modal.innerHTML = `
                <div class="popup-content" style="max-width: 800px;">
                    <div class="popup-header">
                        <div class="popup-title">
                            <i class="fas fa-user"></i>
                            My Job Seeking Posts (${posts.length})
                        </div>
                        <button class="popup-close" onclick="closeMyPostsModal()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div id="myPostsList" style="max-height: 400px; overflow-y: auto;">
                        ${posts.map(post => createMyPostElement(post)).join('')}
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeMyPostsModal()">
                            <i class="fas fa-times"></i>
                            Close
                        </button>
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
            document.body.style.overflow = 'hidden';
            
            // Add click outside to close functionality
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeMyPostsModal();
                }
            });
        }

        // Create element for my post
        function createMyPostElement(post) {
            const postTime = formatTimeAgo(post.CreatedAt);
            const skills = post.KeySkills ? post.KeySkills.split(',').slice(0, 3) : [];
            const skillsHtml = skills.map(skill => `<span class="skill-tag" style="font-size: 11px; padding: 2px 8px;">${skill.trim()}</span>`).join('');
            
            return `
                <div class="my-post-item" data-post-id="${post.PostID}" style="border: 1px solid var(--border); border-radius: 8px; padding: 15px; margin-bottom: 15px; background: var(--bg-primary);">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 10px;">
                        <div>
                            <h4 style="color: var(--accent); margin: 0 0 5px 0;">${escapeHtml(post.JobTitle)}</h4>
                            <p style="color: var(--text-secondary); margin: 0; font-size: 12px;">Posted ${postTime}</p>
                        </div>
                        <div style="display: flex; gap: 5px; align-items: center;">
                            <span style="background: var(--success); color: white; padding: 2px 8px; border-radius: 12px; font-size: 11px; text-transform: uppercase;">${post.Status || 'Active'}</span>
                            <button onclick="event.stopPropagation(); editMyPost(${post.PostID})" style="background: var(--accent); color: white; border: none; border-radius: 4px; padding: 4px 8px; font-size: 11px; cursor: pointer; display: flex; align-items: center; gap: 4px;" title="Edit Post">
                                <i class="fas fa-edit"></i>
                                <span>Edit</span>
                            </button>
                            <button onclick="event.stopPropagation(); deleteMyPost(${post.PostID})" style="background: var(--danger); color: white; border: none; border-radius: 4px; padding: 4px 8px; font-size: 11px; cursor: pointer; display: flex; align-items: center; gap: 4px;" title="Delete Post">
                                <i class="fas fa-trash"></i>
                                <span>Delete</span>
                            </button>
                        </div>
                    </div>
                    <p style="margin: 5px 0; color: var(--text-primary); font-size: 14px;">${escapeHtml(post.Education || '')}</p>
                    <p style="margin: 5px 0; color: var(--text-secondary); font-size: 13px; line-height: 1.4;">${escapeHtml((post.CareerGoal || '').substring(0, 100))}${(post.CareerGoal || '').length > 100 ? '...' : ''}</p>
                    ${skillsHtml ? `<div style="margin-top: 8px;">${skillsHtml}</div>` : ''}
                </div>
            `;
        }

        // Close my posts modal
        function closeMyPostsModal() {
            // Remove all existing my-posts modals (in case there are multiple)
            const modals = document.querySelectorAll('.my-posts-modal');
            modals.forEach(modal => {
                modal.remove();
            });
            document.body.style.overflow = 'auto';
        }

        // Edit my post
        function editMyPost(postId) {
            console.log('Editing post:', postId);
            closeMyPostsModal();
            const post = myPosts.find(p => p.PostID == postId);
            if (!post) {
                showErrorMessage('Post not found');
                return;
            }
            // Load post data into edit form
            document.getElementById('editPostId').value = post.PostID;
            document.getElementById('editJobTitle').value = post.JobTitle || '';
            document.getElementById('editCareerGoal').value = post.CareerGoal || '';
            document.getElementById('editKeySkills').value = post.KeySkills || '';
            document.getElementById('editExperience').value = post.Experience || '';
            document.getElementById('editEducation').value = post.Education || '';
            document.getElementById('editSoftSkills').value = post.SoftSkills || '';
            document.getElementById('editValueToEmployer').value = post.ValueToEmployer || '';
            document.getElementById('editContactInfo').value = post.ContactInfo || '';
            // Show edit popup
            const editPopup = document.getElementById('editJobSeekingPopup');
            editPopup.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        // Delete my post
        function deleteMyPost(postId) {
            if (confirm('Are you sure you want to delete this post?')) {
                fetch('{{ url("/api/job-seeking") }}', {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: JSON.stringify({ action: 'delete_post', postId: postId, candidateId: currentCandidateId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showSuccessMessage('Post deleted successfully');
                        // Close the my posts modal
                        closeMyPostsModal();
                    } else {
                        showErrorMessage(data.message || 'Failed to delete post');
                    }
                })
                .catch(error => {
                    console.error('Error deleting post:', error);
                    showErrorMessage('Network error while deleting post');
                });
            }
        }

        // Close edit job seeking popup
        function closeEditJobSeekingPopup() {
            const popup = document.getElementById('editJobSeekingPopup');
            const form = document.getElementById('editJobSeekingForm');
            
            if (popup) popup.style.display = 'none';
            document.body.style.overflow = 'auto';
            if (form) form.reset();
        }

        // Setup edit job seeking functionality
        function setupEditJobSeeking() {
            const editForm = document.getElementById('editJobSeekingForm');
            const editPopup = document.getElementById('editJobSeekingPopup');
            
            if (!editForm || !editPopup) {
                console.error('Edit job seeking elements not found');
                return;
            }
            
            // Handle form submission
            editForm.addEventListener('submit', function(e) {
                e.preventDefault();
                updateJobSeekingPost();
            });
            
            // Close popup when clicking outside
            editPopup.addEventListener('click', function(e) {
                if (e.target === editPopup) {
                    closeEditJobSeekingPopup();
                }
            });
        }

        // Update job seeking post
        function updateJobSeekingPost() {
            console.log('Updating job seeking post...');
            
            const form = document.getElementById('editJobSeekingForm');
            const submitBtn = document.getElementById('submitEditJobSeekingPost');
            const formData = new FormData(form);
            
            // Prevent multiple submissions
            if (submitBtn && submitBtn.disabled) {
                return;
            }
            
            // Disable button and show loading
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            }
            
            const postData = {
                action: 'update_post',
                postId: formData.get('postId'),
                jobTitle: formData.get('jobTitle'),
                careerGoal: formData.get('careerGoal'),
                keySkills: formData.get('keySkills'),
                experience: formData.get('experience'),
                education: formData.get('education'),
                softSkills: formData.get('softSkills'),
                valueToEmployer: formData.get('valueToEmployer'),
                contactInfo: formData.get('contactInfo'),
                candidateId: currentCandidateId
            };
            
            fetch('{{ url("/api/job-seeking") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify(postData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeEditJobSeekingPopup();
                    showSuccessMessage('Job seeking post updated successfully!');
                    // Reload the modal to show updated list
                    loadMyPosts();
                } else {
                    showErrorMessage(data.message || 'Failed to update job seeking post');
                }
            })
            .catch(error => {
                console.error('Error updating job seeking post:', error);
                showErrorMessage('Network error. Please try again.');
            })
            .finally(() => {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-save"></i> Save Changes';
                }
            });
        }

        // Show success message
        function showSuccessMessage(message) {
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed; top: 20px; right: 20px;
                background: var(--success); color: white;
                padding: 15px 20px; border-radius: 8px;
                z-index: 10001; font-weight: 500;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            `;
            notification.textContent = message;
            document.body.appendChild(notification);
            setTimeout(() => notification.remove(), 3000);
        }

        // Show error message
        function showErrorMessage(message) {
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed; top: 20px; right: 20px;
                background: var(--danger); color: white;
                padding: 15px 20px; border-radius: 8px;
                z-index: 10001; font-weight: 500;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            `;
            notification.textContent = message;
            document.body.appendChild(notification);
            setTimeout(() => notification.remove(), 5000);
        }

        // Show info message
        function showInfoMessage(message) {
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed; top: 20px; right: 20px;
                background: var(--info); color: white;
                padding: 15px 20px; border-radius: 8px;
                z-index: 10001; font-weight: 500;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            `;
            notification.textContent = message;
            document.body.appendChild(notification);
            setTimeout(() => notification.remove(), 4000);
        }

        function refreshPosts() {
            loadPosts(true);
        }

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
            if (themeIcon && themeText) {
                if (theme === 'dark') {
                    themeIcon.className = 'fas fa-moon-stars';
                    themeText.textContent = 'Light Mode';
                } else {
                    themeIcon.className = 'fas fa-moon-stars'; // Same icon, different context usually
                    themeText.textContent = 'Dark Mode';
                }
            }
        }

        function setupThemeToggle() {
            const themeToggleBtn = document.getElementById('themeToggleBtn');
            if (themeToggleBtn) {
                themeToggleBtn.addEventListener('click', toggleTheme);
            }
        }

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
                    showSuccessMessage('Profile updated successfully!');
                    
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
            
            if (popup) popup.style.display = 'none';
            document.body.style.overflow = 'auto';
            if (form) form.reset();
            
            // Hide profile picture preview
            const currentPicture = document.getElementById('currentProfilePicture');
            if (currentPicture) {
                currentPicture.style.display = 'none';
            }
        }

        // Other popup placeholder functions
        function closeEditJobSeekingPopup() { document.getElementById('editJobSeekingPopup').style.display = 'none'; document.body.style.overflow = 'auto'; }
        function closeJobApplicationPopup() { document.getElementById('jobApplicationPopup').style.display = 'none'; document.body.style.overflow = 'auto'; }
        function closeCompanyDetailsPopup() { document.getElementById('companyDetailsPopup').style.display = 'none'; document.body.style.overflow = 'auto'; }
        function closeReportJobPopup() { document.getElementById('reportJobPopup').style.display = 'none'; document.body.style.overflow = 'auto'; }
        
        // Note: applyToJob, openMessageDialog, viewCompanyProfile, reportJobPost are defined below with full implementations

        // Advanced Filter Setup
        function setupAdvancedFilters() {
            console.log('Setting up advanced filters...');
            
            const advancedFilterBtn = document.getElementById('advancedFilterBtn');
            const advancedFilterPanel = document.getElementById('advancedFilterPanel');
            const applyFiltersBtn = document.getElementById('applyFiltersBtn');
            const clearFiltersBtn = document.getElementById('clearFiltersBtn');
            
            if (advancedFilterBtn && advancedFilterPanel) {
                advancedFilterBtn.addEventListener('click', function() {
                    if (advancedFilterPanel.style.display === 'none' || advancedFilterPanel.style.display === '') {
                        advancedFilterPanel.style.display = 'block';
                        advancedFilterBtn.innerHTML = '<i class="fas fa-times"></i><span>Hide Filters</span>';
                    } else {
                        advancedFilterPanel.style.display = 'none';
                        advancedFilterBtn.innerHTML = '<i class="fas fa-filter"></i><span>Advanced Filter</span>';
                    }
                });
            }
            
            if (applyFiltersBtn) {
                applyFiltersBtn.addEventListener('click', function() {
                    applyAdvancedFilters();
                });
            }
            
            if (clearFiltersBtn) {
                clearFiltersBtn.addEventListener('click', function() {
                    clearAllFilters();
                });
            }
            
            // Also setup search input
            const searchInput = document.getElementById('jobSearchInput');
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    // Debounce search
                    clearTimeout(window.searchTimeout);
                    window.searchTimeout = setTimeout(() => {
                        applyAdvancedFilters();
                    }, 300);
                });
            }
            
            console.log('Advanced filters setup complete');
        }

        // Apply advanced filters
        function applyAdvancedFilters() {
            console.log('Applying advanced filters...');
            
            const filters = {
                search: document.getElementById('jobSearchInput')?.value.trim() || '',
                company: document.getElementById('companyFilter')?.value.trim() || '',
                location: document.getElementById('locationFilter')?.value.trim() || '',
                jobType: document.getElementById('jobTypeFilter')?.value || '',
                experience: document.getElementById('experienceFilter')?.value || '',
                skills: document.getElementById('skillsFilter')?.value.trim() || '',
                salary: document.getElementById('salaryFilter')?.value || ''
            };
            
            console.log('Current filters:', filters);
            
            // Filter the displayed posts (client-side filtering for now)
            filterDisplayedPosts(filters);
        }

        // Filter displayed posts
        function filterDisplayedPosts(filters) {
            const posts = document.querySelectorAll('.post');
            let visibleCount = 0;
            
            // Check if any filter is active
            const hasActiveFilters = filters.search || filters.company || filters.location || 
                                     filters.jobType || filters.experience || filters.skills || filters.salary;
            
            posts.forEach(post => {
                let show = true;
                const postContent = post.textContent.toLowerCase();
                
                // Search filter
                if (filters.search && !postContent.includes(filters.search.toLowerCase())) {
                    show = false;
                }
                
                // Company filter
                if (show && filters.company && !postContent.includes(filters.company.toLowerCase())) {
                    show = false;
                }
                
                // Location filter
                if (show && filters.location && !postContent.includes(filters.location.toLowerCase())) {
                    show = false;
                }
                
                // Job Type filter - check for the job type in post content
                if (show && filters.jobType) {
                    const jobTypeText = filters.jobType.replace('-', ' ').toLowerCase();
                    if (!postContent.includes(jobTypeText)) {
                        show = false;
                    }
                }
                
                // Skills filter
                if (show && filters.skills) {
                    // Check each skill separately
                    const skillsToFind = filters.skills.toLowerCase().split(',').map(s => s.trim());
                    const hasSkill = skillsToFind.some(skill => postContent.includes(skill));
                    if (!hasSkill) {
                        show = false;
                    }
                }
                
                post.style.display = show ? '' : 'none';
                if (show) visibleCount++;
            });
            
            // Show message if no posts match
            const postsContainer = document.getElementById('postsContainer');
            let noResultsMsg = document.getElementById('noFilterResults');
            
            if (visibleCount === 0 && posts.length > 0) {
                if (!noResultsMsg) {
                    noResultsMsg = document.createElement('div');
                    noResultsMsg.id = 'noFilterResults';
                    noResultsMsg.style.cssText = 'text-align: center; padding: 40px; color: var(--text-secondary);';
                    noResultsMsg.innerHTML = '<i class="fas fa-search" style="font-size: 48px; margin-bottom: 15px; opacity: 0.5;"></i><p>No posts match your filters. Try adjusting your search criteria.</p>';
                    postsContainer?.appendChild(noResultsMsg);
                }
            } else if (noResultsMsg) {
                noResultsMsg.remove();
            }
            
            console.log(`Showing ${visibleCount} of ${posts.length} posts`);
            
            // Show success message if filters are active
            if (hasActiveFilters) {
                showSuccessMessage(`Showing ${visibleCount} matching posts`);
            }
        }

        // Clear all filters
        function clearAllFilters() {
            console.log('Clearing all filters...');
            
            // Clear input fields
            const inputIds = ['jobSearchInput', 'companyFilter', 'locationFilter', 'jobTypeFilter', 'experienceFilter', 'skillsFilter', 'salaryFilter'];
            inputIds.forEach(id => {
                const el = document.getElementById(id);
                if (el) el.value = '';
            });
            
            // Hide advanced filter panel
            const advancedFilterPanel = document.getElementById('advancedFilterPanel');
            const advancedFilterBtn = document.getElementById('advancedFilterBtn');
            if (advancedFilterPanel) advancedFilterPanel.style.display = 'none';
            if (advancedFilterBtn) advancedFilterBtn.innerHTML = '<i class="fas fa-filter"></i><span>Advanced Filter</span>';
            
            // Show all posts
            const posts = document.querySelectorAll('.post');
            posts.forEach(post => post.style.display = '');
            
            // Remove no results message
            const noResultsMsg = document.getElementById('noFilterResults');
            if (noResultsMsg) noResultsMsg.remove();
            
            showSuccessMessage('Filters cleared');
        }


        // ==================== JOB ACTION FUNCTIONS ====================
        
        // Apply to Job - opens application popup
        function applyToJob(jobId) {
            console.log('Applying to job:', jobId);
            
            // Find the job in posts array
            const job = posts.find(p => p.JobID == jobId);
            if (!job) {
                showErrorMessage('Job not found');
                return;
            }
            
            // Create and show application modal
            const existingModal = document.getElementById('jobApplicationModal');
            if (existingModal) existingModal.remove();
            
            const modal = document.createElement('div');
            modal.id = 'jobApplicationModal';
            modal.className = 'popup-overlay';
            modal.style.cssText = 'position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.7); display: flex; align-items: center; justify-content: center; z-index: 1000;';
            
            modal.innerHTML = `
                <div class="popup-content" style="background: var(--bg-secondary); border-radius: 12px; max-width: 500px; width: 90%; max-height: 80vh; overflow-y: auto; padding: 25px; border: 1px solid var(--border);">
                    <div class="popup-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <h3 style="color: var(--text-primary); margin: 0;">
                            <i class="fas fa-paper-plane" style="color: var(--accent); margin-right: 10px;"></i>
                            Apply to ${escapeHtml(job.JobTitle)}
                        </h3>
                        <button onclick="closeApplicationModal()" style="background: none; border: none; color: var(--text-secondary); font-size: 20px; cursor: pointer;">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <form id="jobApplicationForm">
                        <input type="hidden" id="applicationJobId" value="${jobId}">
                        <div class="form-group" style="margin-bottom: 15px;">
                            <label style="display: block; margin-bottom: 5px; color: var(--text-primary); font-weight: 600;">Cover Letter *</label>
                            <textarea id="applicationCoverLetter" required placeholder="Tell the employer why you're the right fit for this position..." 
                                style="width: 100%; height: 120px; padding: 12px; border: 1px solid var(--border); border-radius: 8px; background: var(--bg-primary); color: var(--text-primary); resize: vertical;"></textarea>
                        </div>
                        <div class="form-group" style="margin-bottom: 20px;">
                            <label style="display: block; margin-bottom: 5px; color: var(--text-primary); font-weight: 600;">Additional Notes</label>
                            <textarea id="applicationNotes" placeholder="Any additional information you'd like to share..." 
                                style="width: 100%; height: 80px; padding: 12px; border: 1px solid var(--border); border-radius: 8px; background: var(--bg-primary); color: var(--text-primary); resize: vertical;"></textarea>
                        </div>
                        <button type="submit" id="submitApplicationBtn" style="width: 100%; padding: 12px 24px; background: var(--accent); color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                            <i class="fas fa-paper-plane"></i> Submit Application
                        </button>
                    </form>
                </div>
            `;
            
            document.body.appendChild(modal);
            document.body.style.overflow = 'hidden';
            
            // Setup form submission
            document.getElementById('jobApplicationForm').addEventListener('submit', function(e) {
                e.preventDefault();
                submitJobApplication(jobId);
            });
            
            // Close on outside click
            modal.addEventListener('click', function(e) {
                if (e.target === modal) closeApplicationModal();
            });
        }
        
        function closeApplicationModal() {
            const modal = document.getElementById('jobApplicationModal');
            if (modal) {
                modal.remove();
                document.body.style.overflow = 'auto';
            }
        }
        
        function submitJobApplication(jobId) {
            const coverLetter = document.getElementById('applicationCoverLetter').value.trim();
            const notes = document.getElementById('applicationNotes').value.trim();
            const submitBtn = document.getElementById('submitApplicationBtn');
            
            if (!coverLetter) {
                showErrorMessage('Cover letter is required');
                return;
            }
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
            
            fetch('/api/job-applications', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    jobId: jobId,
                    coverLetter: coverLetter,
                    additionalNotes: notes
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccessMessage('Application submitted successfully!');
                    closeApplicationModal();
                    
                    // Update button to show applied state
                    const applyBtn = document.querySelector(`[onclick="applyToJob(${jobId})"]`);
                    if (applyBtn) {
                        applyBtn.innerHTML = '<i class="fas fa-check"></i><span>Applied</span>';
                        applyBtn.classList.add('applied');
                        applyBtn.style.pointerEvents = 'none';
                    }
                } else {
                    showErrorMessage(data.message || 'Failed to submit application');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showErrorMessage('Network error. Please try again.');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Submit Application';
            });
        }
        
        // View Company Profile
        function viewCompanyProfile(companyId) {
            console.log('Viewing company:', companyId);
            
            // Find company info from posts
            const job = posts.find(p => p.CompanyID == companyId);
            const companyName = job ? job.CompanyName : 'Company';
            
            // Create and show company modal
            const existingModal = document.getElementById('companyProfileModal');
            if (existingModal) existingModal.remove();
            
            const modal = document.createElement('div');
            modal.id = 'companyProfileModal';
            modal.style.cssText = 'position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.7); display: flex; align-items: center; justify-content: center; z-index: 1000;';
            
            modal.innerHTML = `
                <div style="background: var(--bg-secondary); border-radius: 12px; max-width: 600px; width: 90%; max-height: 80vh; overflow-y: auto; padding: 25px; border: 1px solid var(--border);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <h3 style="color: var(--text-primary); margin: 0;">
                            <i class="fas fa-building" style="color: var(--accent); margin-right: 10px;"></i>
                            ${escapeHtml(companyName)}
                        </h3>
                        <button onclick="closeCompanyModal()" style="background: none; border: none; color: var(--text-secondary); font-size: 20px; cursor: pointer;">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div id="companyProfileContent" style="color: var(--text-primary);">
                        <div style="text-align: center; padding: 20px;">
                            <i class="fas fa-spinner fa-spin" style="font-size: 24px; color: var(--accent);"></i>
                            <p style="margin-top: 10px;">Loading company details...</p>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
            document.body.style.overflow = 'hidden';
            
            // Close on outside click
            modal.addEventListener('click', function(e) {
                if (e.target === modal) closeCompanyModal();
            });
            
            // Fetch company details
            fetch(`/api/company-profile/${companyId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const company = data.company;
                        document.getElementById('companyProfileContent').innerHTML = `
                            <div style="text-align: center; margin-bottom: 20px;">
                                <div style="width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, var(--accent), var(--accent-2)); display: flex; align-items: center; justify-content: center; margin: 0 auto; color: white; font-size: 32px; font-weight: bold;">
                                    ${company.CompanyName ? company.CompanyName.charAt(0).toUpperCase() : 'C'}
                                </div>
                                <h2 style="margin-top: 15px; color: var(--text-primary);">${escapeHtml(company.CompanyName)}</h2>
                                <p style="color: var(--text-secondary);">${escapeHtml(company.Industry || 'Technology')}</p>
                            </div>
                            <div style="display: grid; gap: 15px;">
                                ${company.CompanyDescription ? `<div><strong>About:</strong><p style="margin-top: 5px; color: var(--text-secondary);">${escapeHtml(company.CompanyDescription)}</p></div>` : ''}
                                ${company.Website ? `<div><i class="fas fa-globe" style="color: var(--accent); margin-right: 8px;"></i><a href="${escapeHtml(company.Website)}" target="_blank" style="color: var(--accent);">${escapeHtml(company.Website)}</a></div>` : ''}
                                ${company.Email ? `<div><i class="fas fa-envelope" style="color: var(--accent); margin-right: 8px;"></i>${escapeHtml(company.Email)}</div>` : ''}
                                ${company.PhoneNumber ? `<div><i class="fas fa-phone" style="color: var(--accent); margin-right: 8px;"></i>${escapeHtml(company.PhoneNumber)}</div>` : ''}
                                ${company.Address ? `<div><i class="fas fa-map-marker-alt" style="color: var(--accent); margin-right: 8px;"></i>${escapeHtml(company.Address)}${company.City ? ', ' + escapeHtml(company.City) : ''}</div>` : ''}
                                ${company.CompanySize ? `<div><i class="fas fa-users" style="color: var(--accent); margin-right: 8px;"></i>${escapeHtml(company.CompanySize)} employees</div>` : ''}
                            </div>
                        `;
                    } else {
                        document.getElementById('companyProfileContent').innerHTML = '<p style="text-align: center; color: var(--text-secondary);">Company details not available</p>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('companyProfileContent').innerHTML = '<p style="text-align: center; color: var(--danger);">Error loading company details</p>';
                });
        }
        
        function closeCompanyModal() {
            const modal = document.getElementById('companyProfileModal');
            if (modal) {
                modal.remove();
                document.body.style.overflow = 'auto';
            }
        }
        
        // Open Message Dialog
        function openMessageDialog(receiverId, companyId, receiverType, receiverName, receiverLogo) {
            console.log('Opening message dialog:', { receiverId, receiverType, receiverName });
            
            const existingModal = document.getElementById('messageModal');
            if (existingModal) existingModal.remove();
            
            const modal = document.createElement('div');
            modal.id = 'messageModal';
            modal.style.cssText = 'position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.7); display: flex; align-items: center; justify-content: center; z-index: 1000;';
            
            modal.innerHTML = `
                <div style="background: var(--bg-secondary); border-radius: 12px; max-width: 500px; width: 90%; padding: 25px; border: 1px solid var(--border);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <h3 style="color: var(--text-primary); margin: 0;">
                            <i class="fas fa-comment" style="color: var(--accent); margin-right: 10px;"></i>
                            Message ${escapeHtml(receiverName)}
                        </h3>
                        <button onclick="closeMessageModal()" style="background: none; border: none; color: var(--text-secondary); font-size: 20px; cursor: pointer;">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <form id="sendMessageForm">
                        <div style="margin-bottom: 15px;">
                            <label style="display: block; margin-bottom: 5px; color: var(--text-primary); font-weight: 600;">Your Message *</label>
                            <textarea id="messageContent" required placeholder="Type your message here..." 
                                style="width: 100%; height: 120px; padding: 12px; border: 1px solid var(--border); border-radius: 8px; background: var(--bg-primary); color: var(--text-primary); resize: vertical;"></textarea>
                        </div>
                        <button type="submit" id="sendMessageBtn" style="width: 100%; padding: 12px 24px; background: var(--accent); color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                            <i class="fas fa-paper-plane"></i> Send Message
                        </button>
                    </form>
                </div>
            `;
            
            document.body.appendChild(modal);
            document.body.style.overflow = 'hidden';
            
            // Setup form submission
            document.getElementById('sendMessageForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const message = document.getElementById('messageContent').value.trim();
                const btn = document.getElementById('sendMessageBtn');
                
                if (!message) {
                    showErrorMessage('Please enter a message');
                    return;
                }
                
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
                
                // For now just show success (messaging system would need full implementation)
                setTimeout(() => {
                    showSuccessMessage('Message sent successfully!');
                    closeMessageModal();
                }, 500);
            });
            
            modal.addEventListener('click', function(e) {
                if (e.target === modal) closeMessageModal();
            });
        }
        
        function closeMessageModal() {
            const modal = document.getElementById('messageModal');
            if (modal) {
                modal.remove();
                document.body.style.overflow = 'auto';
            }
        }
        
        // Report Job Post
        function reportJobPost(jobId, jobTitle, companyId, companyName) {
            console.log('Reporting job:', { jobId, jobTitle, companyId, companyName });
            
            const existingModal = document.getElementById('reportJobModal');
            if (existingModal) existingModal.remove();
            
            const modal = document.createElement('div');
            modal.id = 'reportJobModal';
            modal.style.cssText = 'position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.7); display: flex; align-items: center; justify-content: center; z-index: 1000;';
            
            modal.innerHTML = `
                <div style="background: var(--bg-secondary); border-radius: 12px; max-width: 500px; width: 90%; padding: 25px; border: 1px solid var(--border);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <h3 style="color: var(--warning); margin: 0;">
                            <i class="fas fa-flag" style="margin-right: 10px;"></i>
                            Report Job Post
                        </h3>
                        <button onclick="closeReportModal()" style="background: none; border: none; color: var(--text-secondary); font-size: 20px; cursor: pointer;">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div style="margin-bottom: 15px; padding: 10px; background: var(--bg-tertiary); border-radius: 8px; border: 1px solid var(--border);">
                        <p style="margin: 0; color: var(--text-secondary); font-size: 14px;">Job: <strong style="color: var(--text-primary);">${escapeHtml(jobTitle)}</strong></p>
                        <p style="margin: 5px 0 0 0; color: var(--text-secondary); font-size: 14px;">Company: <strong style="color: var(--text-primary);">${escapeHtml(companyName)}</strong></p>
                    </div>
                    <form id="reportJobForm">
                        <div style="margin-bottom: 15px;">
                            <label style="display: block; margin-bottom: 5px; color: var(--text-primary); font-weight: 600;">Reason for Report *</label>
                            <select id="reportReason" required style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px; background: var(--bg-primary); color: var(--text-primary);">
                                <option value="">Select a reason...</option>
                                <option value="spam">Spam or misleading</option>
                                <option value="scam">Suspected scam</option>
                                <option value="inappropriate">Inappropriate content</option>
                                <option value="discrimination">Discrimination</option>
                                <option value="expired">Job no longer available</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div style="margin-bottom: 20px;">
                            <label style="display: block; margin-bottom: 5px; color: var(--text-primary); font-weight: 600;">Additional Details</label>
                            <textarea id="reportDetails" placeholder="Please provide more details about your concern..." 
                                style="width: 100%; height: 80px; padding: 12px; border: 1px solid var(--border); border-radius: 8px; background: var(--bg-primary); color: var(--text-primary); resize: vertical;"></textarea>
                        </div>
                        <button type="submit" id="submitReportBtn" style="width: 100%; padding: 12px 24px; background: var(--warning); color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                            <i class="fas fa-flag"></i> Submit Report
                        </button>
                    </form>
                </div>
            `;
            
            document.body.appendChild(modal);
            document.body.style.overflow = 'hidden';
            
            // Setup form submission
            document.getElementById('reportJobForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const reason = document.getElementById('reportReason').value;
                const details = document.getElementById('reportDetails').value.trim();
                const btn = document.getElementById('submitReportBtn');
                
                if (!reason) {
                    showErrorMessage('Please select a reason');
                    return;
                }
                
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
                
                fetch('/api/job-reports', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        jobId: jobId,
                        companyId: companyId,
                        reason: reason,
                        details: details
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showSuccessMessage('Report submitted. Thank you for helping keep our platform safe.');
                        closeReportModal();
                    } else {
                        showErrorMessage(data.message || 'Failed to submit report');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showErrorMessage('Network error. Please try again.');
                })
                .finally(() => {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-flag"></i> Submit Report';
                });
            });
            
            modal.addEventListener('click', function(e) {
                if (e.target === modal) closeReportModal();
            });
        }
        
        function closeReportModal() {
            const modal = document.getElementById('reportJobModal');
            if (modal) {
                modal.remove();
                document.body.style.overflow = 'auto';
            }
        }

        // ==================== JOB APPLICATION FUNCTIONS ====================
        
        // Apply to job - opens the application popup
        function applyToJob(jobId) {
            console.log('Opening application popup for job:', jobId);
            openJobApplicationPopup(jobId);
        }

        // Open job application popup
        function openJobApplicationPopup(jobId) {
            const popup = document.getElementById('jobApplicationPopup');
            if (!popup) {
                console.error('Job application popup not found');
                return;
            }
            
            // Set the job ID
            document.getElementById('applicationJobId').value = jobId;
            
            // Clear previous values
            document.getElementById('applicationCoverLetter').value = '';
            document.getElementById('applicationNotes').value = '';
            
            // Show popup
            popup.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        // Close job application popup
        function closeJobApplicationPopup() {
            const popup = document.getElementById('jobApplicationPopup');
            if (popup) {
                popup.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        }

        // Handle job application form submission
        function setupJobApplicationForm() {
            const form = document.getElementById('jobApplicationForm');
            if (!form) return;

            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const submitBtn = document.getElementById('submitApplication');
                const originalBtnText = submitBtn.innerHTML;
                
                // Disable button and show loading
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
                
                const jobId = document.getElementById('applicationJobId').value;
                const coverLetter = document.getElementById('applicationCoverLetter').value.trim();
                const additionalNotes = document.getElementById('applicationNotes').value.trim();
                
                try {
                    const response = await fetch('/api/job-applications', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            jobId: jobId,
                            coverLetter: coverLetter,
                            additionalNotes: additionalNotes
                        })
                    });

                    const data = await response.json();
                    
                    if (data.success) {
                        showSuccessMessage(data.message || 'Application submitted successfully!');
                        closeJobApplicationPopup();
                        
                        // Update the apply button to show "Applied" and increment count
                        const post = document.querySelector(`.post[data-job-id="${jobId}"]`);
                        if (post) {
                            // Update apply button
                            const applyBtn = post.querySelector('.action-btn');
                            if (applyBtn) {
                                applyBtn.classList.add('applied');
                                applyBtn.style.pointerEvents = 'none';
                                applyBtn.innerHTML = '<i class="fas fa-check"></i><span>Applied</span>';
                            }
                            
                            // Update application count
                            const viewsCount = post.querySelector('.views-count');
                            if (viewsCount) {
                                const currentText = viewsCount.textContent;
                                const currentCount = parseInt(currentText) || 0;
                                viewsCount.textContent = (currentCount + 1) + ' applications';
                            }
                        }
                        
                        // Refresh posts to ensure data consistency
                        if (typeof loadPosts === 'function') {
                            setTimeout(() => loadPosts(true), 1000);
                        }
                    } else {
                        showErrorMessage(data.message || 'Failed to submit application');
                    }
                } catch (error) {
                    console.error('Error submitting application:', error);
                    showErrorMessage('Network error. Please try again.');
                } finally {
                    // Restore button
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                }
            });
        }


        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            initializeTheme();
            initializeDashboard();
            setupThemeToggle();
            setupEditJobSeeking();
            setupProfileEditing();
            setupAdvancedFilters();
            setupJobApplicationForm();
        });

    </script>
</body>
</html>

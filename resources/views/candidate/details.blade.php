<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candidate Details - CandiHire</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/CandidateDashboard.css') }}">
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
            background: linear-gradient(135deg, var(--accent), var(--accent-2));
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
        }
        
        .timeline-item {
            position: relative;
            padding-left: 20px;
            margin-bottom: 20px;
            border-left: 2px solid var(--border);
        }
        
        .timeline-item:last-child {
            margin-bottom: 0;
            border-left-color: transparent; /* Hide line for last item if desired, or keep it */
        }
        
        .timeline-dot {
            position: absolute;
            left: -6px;
            top: 0;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background-color: var(--accent);
        }
        
        .timeline-content h4 {
            margin: 0 0 5px 0;
            font-size: 16px;
            color: var(--text-primary);
        }
        
        .timeline-content h5 {
            margin: 0 0 5px 0;
            font-size: 14px;
            font-weight: 500;
            color: var(--text-secondary);
        }
        
        .timeline-date {
            font-size: 12px;
            color: var(--text-secondary);
            margin-bottom: 8px;
            font-style: italic;
        }
        
        .timeline-desc {
            font-size: 14px;
            color: var(--text-secondary);
            line-height: 1.5;
        }
        
        .detail-row {
            display: flex;
            margin-bottom: 12px;
        }
        
        .detail-label {
            width: 120px;
            color: var(--text-secondary);
            font-size: 14px;
            flex-shrink: 0;
        }
        
        .detail-value {
            color: var(--text-primary);
            font-size: 14px;
            word-break: break-all;
        }
        
        .detail-value a {
            color: var(--accent);
            text-decoration: none;
        }
        
        .detail-value a:hover {
            text-decoration: underline;
        }
        
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--text-primary);
            text-decoration: none;
            margin-bottom: 20px;
            font-weight: 500;
            transition: color 0.2s;
        }
        
        .back-btn:hover {
            color: var(--accent);
        }
    </style>
</head>
<body>
    <div class="container" style="display: block; padding-top: 20px;">
        <!-- Navigation Bar (Simplified for Details View) -->
        <div style="max-width: 1000px; margin: 0 auto; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
            <div class="logo" style="margin-bottom: 0;">
                <span class="candiHire">
                  <span class="candi">Candi</span><span class="hire">Hire</span>
                </span>
            </div>
            <div>
                 <button id="themeToggleBtn" class="theme-toggle-btn" style="width: auto; padding: 8px 16px; margin-bottom: 0;" title="Switch Theme">
                    <i class="fas fa-moon-stars" id="themeIcon"></i>
                </button>
            </div>
        </div>

        <div style="max-width: 1000px; margin: 0 auto;">
            <a href="javascript:history.back()" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back
            </a>
            
            <!-- Profile Header -->
            <div class="profile-header">
                <div class="profile-avatar-large" style="{{ $candidate['profilePicture'] ? 'background-image: url(' . $candidate['profilePicture'] . ');' : '' }}">
                    {{ $candidate['profilePicture'] ? '' : strtoupper(substr($candidate['name'], 0, 1)) }}
                </div>
                <div class="profile-info">
                    <h1 class="profile-name">{{ $candidate['name'] }}</h1>
                    <div class="profile-role">
                         {{ $candidate['workType'] ? ucfirst($candidate['workType']) : 'Job Seeker' }}
                    </div>
                    <div class="profile-location">
                        <i class="fas fa-map-marker-alt"></i>
                        {{ $candidate['location'] ?? 'Location not specified' }}
                    </div>
                    
                    <div class="detail-row" style="margin-bottom: 5px;">
                        <i class="fas fa-envelope" style="width: 20px; color: var(--text-secondary);"></i>
                        <span style="color: var(--text-primary);">{{ $candidate['email'] }}</span>
                    </div>
                    @if($candidate['phone'])
                    <div class="detail-row">
                        <i class="fas fa-phone" style="width: 20px; color: var(--text-secondary);"></i>
                        <span style="color: var(--text-primary);">{{ $candidate['phone'] }}</span>
                    </div>
                    @endif
                </div>
                
                <div style="display: flex; flex-direction: column; gap: 10px;">
                     @if($candidate['linkedin'])
                        <a href="{{ $candidate['linkedin'] }}" target="_blank" class="btn btn-secondary" style="text-decoration: none; justify-content: center;">
                            <i class="fab fa-linkedin"></i> LinkedIn
                        </a>
                    @endif
                    @if($candidate['github'])
                        <a href="{{ $candidate['github'] }}" target="_blank" class="btn btn-secondary" style="text-decoration: none; justify-content: center;">
                            <i class="fab fa-github"></i> GitHub
                        </a>
                    @endif
                    @if($candidate['portfolio'])
                        <a href="{{ $candidate['portfolio'] }}" target="_blank" class="btn btn-secondary" style="text-decoration: none; justify-content: center;">
                            <i class="fas fa-globe"></i> Portfolio
                        </a>
                    @endif
                    <a href="mailto:{{ $candidate['email'] }}" class="btn btn-primary" style="text-decoration: none; justify-content: center;">
                        <i class="fas fa-envelope"></i> Contact
                    </a>
                </div>
            </div>
            
            <div class="content-grid" style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
                <!-- Left Column -->
                <div>
                    <!-- About Section -->
                    <div class="section-card">
                        <div class="section-header">
                            <div class="section-title">
                                <i class="fas fa-user-circle"></i> About
                            </div>
                        </div>
                        <div class="section-body" style="color: var(--text-secondary); line-height: 1.6;">
                            {{ $candidate['summary'] ?? 'No summary provided.' }}
                        </div>
                    </div>
                    
                    <!-- Experience Section -->
                    <div class="section-card">
                        <div class="section-header">
                            <div class="section-title">
                                <i class="fas fa-briefcase"></i> Work Experience
                            </div>
                        </div>
                        <div class="section-body">
                            @if(empty($experiences))
                                <p style="color: var(--text-secondary);">No work experience listed.</p>
                            @else
                                @foreach($experiences as $exp)
                                <div class="timeline-item">
                                    <div class="timeline-dot"></div>
                                    <div class="timeline-content">
                                        <h4>{{ $exp['JobTitle'] }}</h4>
                                        <h5>{{ $exp['Company'] }}</h5>
                                        <div class="timeline-date">
                                            {{ date('M Y', strtotime($exp['StartDate'])) }} - 
                                            {{ $exp['EndDate'] ? date('M Y', strtotime($exp['EndDate'])) : 'Present' }}
                                        </div>
                                        <div class="timeline-desc">
                                            {{ $exp['Description'] }}
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            @endif
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
                            @if(empty($educations) && !$candidate['education'] && !$candidate['institute'])
                                <p style="color: var(--text-secondary);">No education details listed.</p>
                            @else
                                <!-- Main Profile Education (if distinct) -->
                                @if($candidate['education'] || $candidate['institute'])
                                 <div class="timeline-item">
                                    <div class="timeline-dot"></div>
                                    <div class="timeline-content">
                                        <h4>{{ $candidate['education'] }}</h4>
                                        <h5>{{ $candidate['institute'] }}</h5>
                                    </div>
                                </div>
                                @endif

                                <!-- Detailed Education List -->
                                @foreach($educations as $edu)
                                <div class="timeline-item">
                                    <div class="timeline-dot"></div>
                                    <div class="timeline-content">
                                        <h4>{{ $edu['Degree'] }}</h4>
                                        <h5>{{ $edu['Institution'] }}</h5>
                                        <div class="timeline-date">
                                            {{ $edu['StartYear'] }} - {{ $edu['EndYear'] }}
                                        </div>
                                        @if($edu['GPA'])
                                        <div class="timeline-desc">GPA: {{ $edu['GPA'] }}</div>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
                
                <!-- Right Column -->
                <div>
                    <!-- Skills Section -->
                    <div class="section-card">
                        <div class="section-header">
                            <div class="section-title">
                                <i class="fas fa-tools"></i> Skills
                            </div>
                        </div>
                        <div class="skills-grid">
                            @if(!empty($candidate['skills']))
                                @foreach(explode(',', $candidate['skills']) as $skill)
                                    @if(trim($skill))
                                        <span class="skill-tag">{{ trim($skill) }}</span>
                                    @endif
                                @endforeach
                            @else
                                <p style="color: var(--text-secondary);">No skills listed.</p>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Details Card -->
                    <div class="section-card">
                         <div class="section-header">
                            <div class="section-title">
                                <i class="fas fa-info-circle"></i> Info
                            </div>
                        </div>
                        <div class="section-body">
                            <div class="detail-row">
                                <span class="detail-label">Joined</span>
                                <span class="detail-value">{{ date('M d, Y', strtotime($candidate['joinedDate'])) }}</span>
                            </div>
                            <!-- Add more meta details here if available -->
                        </div>
                    </div>
                </div>
            </div>
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
            if (theme === 'dark') {
                themeIcon.className = 'fas fa-moon-stars';
            } else {
                themeIcon.className = 'fas fa-moon-stars'; // Using same icon for simplicity or change if needed
            }
        }

        document.getElementById('themeToggleBtn').addEventListener('click', toggleTheme);
        initializeTheme();
    </script>
</body>
</html>

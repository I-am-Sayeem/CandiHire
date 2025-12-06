@extends('layouts.app')

@section('content')
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interview Schedule - CandiHire</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/interview_schedule.css') }}">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container">
         <!-- Left Navigation -->
         <div class="left-nav">
            <div class="logo">
                <span class="candi">Candi</span><span class="hire">Hire</span>
            </div>
            
            <div class="welcome-section" style="background: var(--bg-tertiary); padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid var(--border);">
                 <div style="display: flex; align-items: center; gap: 10px;">
                     <div id="candidateAvatar" class="avatar-placeholder">
                         <span class="avatar-initials">{{ strtoupper(substr(session('candidate_name', 'User'), 0, 1)) }}</span>
                     </div>
                     <div>
                         <div style="color: var(--text-primary); font-weight: 600; font-size: 14px;">Welcome back!</div>
                         <div id="candidateNameDisplay" style="color: var(--text-secondary); font-size: 12px;">{{ session('candidate_name', 'User') }}</div>
                     </div>
                 </div>
                 <button id="editProfileBtn" class="edit-profile-btn" style="width: 100%; margin-top: 10px;">
                     <i class="fas fa-user-edit"></i> Edit Profile
                 </button>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">Main menu</div>
                <div class="nav-item" onclick="window.location.href='{{ url('candidate/dashboard') }}'">
                    <i class="fas fa-home"></i>
                    <span>News feed</span>
                </div>
                <div class="nav-item" onclick="window.location.href='{{ url('cv-builder') }}'">
                    <i class="fas fa-file-alt"></i>
                    <span>CV builder</span>
                </div>
                <div class="nav-item" onclick="window.location.href='{{ url('application-status') }}'">
                    <i class="fas fa-clipboard-list"></i>
                    <span>Application status</span>
                </div>
            </div>
             <div class="nav-section">
                <div class="nav-section-title">Interviews & Exams</div>
                <div class="nav-item active">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Interview schedule</span>
                </div>
                <div class="nav-item" onclick="window.location.href='{{ url('attend-exam') }}'">
                    <i class="fas fa-pencil-alt"></i>
                    <span>Attend Exam</span>
                </div>
            </div>
            
             <div class="logout-container">
                 <button id="themeToggleBtn" class="theme-toggle-btn" title="Switch to Light Mode">
                     <i class="fas fa-moon" id="themeIcon"></i>
                     <span id="themeText">Dark Mode</span>
                 </button>
                 <button id="logoutBtn" class="logout-btn" onclick="window.location.href='{{ route('logout') }}'">
                     <i class="fas fa-sign-out-alt" style="margin-right:8px;"></i>Logout
                 </button>
            </div>
        </div>

        <div class="main-content">
            <div class="page-header">
                <h1 class="page-title">Interview Schedule</h1>
                <div class="header-actions">
                    <button class="btn btn-secondary" onclick="window.location.reload()"><i class="fas fa-sync-alt"></i> Refresh</button>
                </div>
            </div>

            <div class="schedule-grid">
                <!-- Upcoming Interviews -->
                <div class="section">
                    <div class="section-header">
                        <div class="section-title">Upcoming Interviews</div>
                    </div>
                    <div class="section-body" id="upcomingContainer">
                         <!-- Content injected via JS or simulated server-side rendering -->
                         <div style="text-align: center; color: var(--text-secondary); padding: 40px;" id="noUpcomingMsg">
                            <i class="fas fa-calendar-times" style="font-size: 48px; margin-bottom: 16px; opacity: 0.5;"></i>
                            <div style="font-size: 18px; margin-bottom: 8px;">No Upcoming Interviews</div>
                            <div style="font-size: 14px;">You don't have any scheduled interviews at the moment.</div>
                        </div>
                    </div>
                </div>

                <!-- Calendar Section -->
                <div class="section">
                    <div class="section-header">
                        <div class="section-title">Calendar View</div>
                    </div>
                     <div class="section-body">
                        <div class="calendar-navigation" style="display: flex; justify-content: space-between; align-items: center; padding: 10px 15px;">
                            <button onclick="previousMonth()" class="btn btn-secondary btn-small"><i class="fas fa-chevron-left"></i></button>
                            <h3 id="calendarMonthYear" style="margin: 0; font-size: 18px;">Month Year</h3>
                            <button onclick="nextMonth()" class="btn btn-secondary btn-small"><i class="fas fa-chevron-right"></i></button>
                        </div>
                        <div id="interviewCalendar">
                            <div class="calendar-grid" id="calendarGrid">
                                <!-- Calendar days generated by JS -->
                            </div>
                        </div>
                        <div id="calendarDetails" class="interview-details-popup" style="display: none; margin-top: 20px;">
                            <div id="detailsContent"></div>
                        </div>
                    </div>
                </div>

                <!-- Past Interviews -->
                 <div class="section">
                    <div class="section-header">
                        <div class="section-title">Past Interviews</div>
                    </div>
                    <div class="section-body" id="pastContainer">
                        <div style="text-align: center; color: var(--text-secondary); padding: 40px;" id="noPastMsg">
                            <i class="fas fa-history" style="font-size: 48px; margin-bottom: 16px; opacity: 0.5;"></i>
                             <div style="font-size: 18px; margin-bottom: 8px;">No Past Interviews</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Use PHP variables for data
        const upcomingInterviews = @json($upcomingInterviews ?? []);
        const pastInterviews = @json($pastInterviews ?? []);
        const allInterviews = [...upcomingInterviews, ...pastInterviews];

        let currentDate = new Date();
        let currentMonth = currentDate.getMonth();
        let currentYear = currentDate.getFullYear();

        document.addEventListener('DOMContentLoaded', function() {
            initializeTheme();
            setupThemeToggle();
            
            // Mock data if empty (for demonstration/preview)
            if (allInterviews.length === 0) {
                 // Nothing to mock, let the empty states show or uncomment below to test
                 /* 
                 const mockUpcoming = [
                    {
                        InterviewID: 1,
                        InterviewTitle: "Senior Dev Interview", 
                        CompanyName: "Tech Corp",
                        ScheduledDate: new Date().toISOString().split('T')[0], // Today
                        ScheduledTime: "14:00:00",
                        InterviewMode: "Virtual",
                        Platform: "Zoom",
                        MeetingLink: "https://zoom.us/j/123",
                        Location: "N/A"
                    }
                 ];
                 upcomingInterviews.push(...mockUpcoming);
                 allInterviews.push(...mockUpcoming);
                 */
            }

            renderInterviews();
            generateCalendar(currentYear, currentMonth);
            
            // Periodic check for expired interviews
            setInterval(checkAndRemovePastInterviews, 30000);
        });

        function renderInterviews() {
            const upcomingContainer = document.getElementById('upcomingContainer');
            const pastContainer = document.getElementById('pastContainer');
            
            if (upcomingInterviews.length > 0) {
                document.getElementById('noUpcomingMsg').style.display = 'none';
                upcomingInterviews.forEach(iv => {
                    const card = createInterviewCard(iv);
                    upcomingContainer.appendChild(card);
                });
            }

            if (pastInterviews.length > 0) {
                 document.getElementById('noPastMsg').style.display = 'none';
                 pastInterviews.forEach(iv => {
                    const card = createInterviewCard(iv, true);
                    pastContainer.appendChild(card);
                });
            }
        }

        function createInterviewCard(iv, isPast = false) {
             const div = document.createElement('div');
             div.className = 'interview-card';
             div.setAttribute('data-id', iv.InterviewID);
             
             // Format time
             const timeString = new Date(iv.ScheduledDate + 'T' + iv.ScheduledTime).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
             const dateString = new Date(iv.ScheduledDate).toLocaleDateString([], {weekday: 'short', month: 'short', day: 'numeric'});

             div.innerHTML = `
                <div class="interview-title">${iv.InterviewTitle}</div>
                <div class="interview-company"><i class="fas fa-building"></i> ${iv.CompanyName}</div>
                <div class="detail-row">
                     <div class="detail"><i class="far fa-calendar"></i> ${dateString}</div>
                     <div class="detail"><i class="far fa-clock"></i> ${timeString}</div>
                </div>
                <div class="detail-row">
                     <div class="detail"><i class="fas fa-video"></i> ${iv.InterviewMode}</div>
                     <div class="detail"><i class="fas fa-map-marker-alt"></i> ${iv.Location || 'Online'}</div>
                </div>
                ${!isPast && iv.MeetingLink ? `
                <div class="card-actions">
                     <a href="${iv.MeetingLink}" target="_blank" class="btn btn-primary" style="width:100%; justify-content:center;">
                         <i class="fas fa-video"></i> Join Meeting
                     </a>
                </div>` : ''}
                ${isPast && iv.Status ? `
                <div style="margin-top:10px; font-size:12px; font-weight:bold; color: var(--text-secondary);">
                    Status: ${iv.Status}
                </div>` : ''}
             `;
             return div;
        }

        function generateCalendar(year, month) {
            const calendarGrid = document.getElementById('calendarGrid');
            const calendarMonthYear = document.getElementById('calendarMonthYear');
            
            calendarGrid.innerHTML = ''; // Clear existing
            
            const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
            calendarMonthYear.textContent = `${monthNames[month]} ${year}`;
            
            // Add day headers
            const days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
            days.forEach(day => {
                const header = document.createElement('div');
                header.className = 'calendar-header';
                header.textContent = day;
                calendarGrid.appendChild(header);
            });

            const firstDay = new Date(year, month, 1).getDay();
            const daysInMonth = new Date(year, month + 1, 0).getDate();
            
            // Empty slots for previous month
            for (let i = 0; i < firstDay; i++) {
                const empty = document.createElement('div');
                empty.className = 'calendar-day other-month';
                calendarGrid.appendChild(empty);
            }
            
            // Actual days
            for (let day = 1; day <= daysInMonth; day++) {
                const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                const cell = document.createElement('div');
                cell.className = 'calendar-day';
                cell.textContent = day;
                
                // Highlight today
                const todayStr = new Date().toISOString().split('T')[0];
                if (dateStr === todayStr) cell.classList.add('today');
                
                // Check conflicts and interviews
                const dayInterviews = getInterviewsForDate(dateStr);
                if (dayInterviews.length > 0) {
                    if (dayInterviews.length > 1) {
                        cell.classList.add('has-conflict');
                        cell.title = `Conflict: ${dayInterviews.length} interviews`;
                    } else {
                        cell.classList.add('has-interview');
                    }
                    cell.onclick = () => showDateDetails(dateStr, dayInterviews.length > 1);
                }
                
                calendarGrid.appendChild(cell);
            }
        }
        
        function previousMonth() {
            currentMonth--;
            if (currentMonth < 0) { currentMonth = 11; currentYear--; }
            generateCalendar(currentYear, currentMonth);
        }
        
        function nextMonth() {
            currentMonth++;
            if (currentMonth > 11) { currentMonth = 0; currentYear++; }
            generateCalendar(currentYear, currentMonth);
        }

        function getInterviewsForDate(dateString) {
            return allInterviews.filter(iv => iv.ScheduledDate === dateString);
        }

        function showDateDetails(dateString, hasConflict) {
             const dayInterviews = getInterviewsForDate(dateString);
             const detailsDiv = document.getElementById('calendarDetails');
             const contentDiv = document.getElementById('detailsContent');
             
             if (dayInterviews.length === 0) {
                 detailsDiv.style.display = 'none';
                 return;
             }
             
             let html = `<h4 style="margin-bottom:10px;">${new Date(dateString).toDateString()}</h4>`;
             if (hasConflict) {
                 html += `<div style="color:var(--danger); margin-bottom:10px;"><i class="fas fa-exclamation-triangle"></i> Multiple interviews scheduled</div>`;
             }
             
             dayInterviews.forEach(iv => {
                 html += `
                    <div class="interview-detail-item" style="display:block; margin-bottom:10px; border-bottom:1px solid var(--border); padding-bottom:10px;">
                        <div style="font-weight:bold;">${iv.InterviewTitle}</div>
                        <div style="font-size:12px; color:var(--text-secondary);">${iv.CompanyName}</div>
                        <div style="font-size:12px;">${iv.ScheduledTime} - ${iv.InterviewMode}</div>
                    </div>
                 `;
             });
             
             contentDiv.innerHTML = html;
             detailsDiv.style.display = 'block';
        }

        function checkAndRemovePastInterviews() {
             const now = new Date();
             
             upcomingInterviews.forEach((iv, index) => {
                 const ivDate = new Date(iv.ScheduledDate + 'T' + iv.ScheduledTime);
                 if (ivDate < now) {
                     // Move logic: simpler to just reload or simulate movement in DOM
                     // For pure frontend simulation without real backend state change in this static view:
                     const card = document.querySelector(`.interview-card[data-id="${iv.InterviewID}"]`);
                     if (card && card.parentElement.id === 'upcomingContainer') {
                         card.classList.add('interview-expired');
                         setTimeout(() => {
                             card.remove();
                             // Append to past if we wanted to fully simulate, but reload is safer for sync
                             // window.location.reload(); 
                         }, 1000);
                     }
                 }
             });
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

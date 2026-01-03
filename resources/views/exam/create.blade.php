<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create MCQ Exam - CandiHire</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/CreateExam.css') }}">
    <!-- Reusing basic variables if needed, though CreateExam.css defines its own root variables -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-clipboard-question"></i> Create MCQ Exam</h1>
            <p>Design comprehensive assessments for your job positions</p>
        </div>

        <a href="{{ url('company/dashboard') }}" class="back-btn">
            <i class="fas fa-arrow-left"></i>
            Back to Dashboard
        </a>

        <div class="main-card">
            <div class="step-header">
                <div class="step-number">1</div>
                <div class="step-title">Select Job Post</div>
            </div>

            @if (!isset($jobPosts) || count($jobPosts) === 0)
                <div class="no-posts-message">
                    <div class="message-content">
                        <i class="fas fa-exclamation-triangle message-icon" style="color: var(--warning);"></i>
                        <div>
                            <strong>No Active Job Posts Found!</strong><br>
                            You need to create job posts first before creating exams. Go to your dashboard to post jobs.
                        </div>
                    </div>
                    <a href="{{ url('job-posts') }}" class="proceed-btn">
                        <i class="fas fa-plus"></i> Create Job Posts
                    </a>
                </div>
            @else
                @php
                    $pendingJobs = collect($jobPosts)->where('Status', 'Pending');
                @endphp
                
                @if($pendingJobs->count() > 0 && isset($selectedJobId))
                <div style="background: linear-gradient(135deg, #f39c12, #e67e22); color: white; padding: 15px 20px; border-radius: 12px; margin-bottom: 20px; display: flex; align-items: center; gap: 15px; box-shadow: 0 4px 15px rgba(243, 156, 18, 0.3);">
                    <i class="fas fa-exclamation-triangle" style="font-size: 24px;"></i>
                    <div style="flex: 1;">
                        <div style="font-weight: 600; font-size: 16px;">This Job is Pending!</div>
                        <div style="font-size: 13px; opacity: 0.9;">Create an exam below to activate this job and make it visible to candidates.</div>
                    </div>
                </div>
                @endif
                
                <div class="form-group">
                    <label for="position" class="form-label">
                        <i class="fas fa-briefcase"></i> Choose the job post for this exam
                    </label>
                    <select id="position" class="form-select" required>
                        <option value="">Select a job post...</option>
                        @foreach ($jobPosts as $job)
                            @php
                                $salaryRange = $job['SalaryMin'] && $job['SalaryMax'] ? 
                                    $job['Currency'] . ' ' . number_format($job['SalaryMin']) . ' - ' . number_format($job['SalaryMax']) : 
                                    'Salary not specified';
                                $isSelected = (isset($selectedJobId) && $selectedJobId == $job['JobID']) ? 'selected' : '';
                                $statusLabel = ($job['Status'] ?? '') === 'Pending' ? ' ⚠️ PENDING - Needs Exam' : '';
                            @endphp
                            <option value="{{ $job['JobID'] }}" 
                                    data-department="{{ $job['Department'] }}"
                                    data-location="{{ $job['Location'] }}"
                                    data-type="{{ $job['JobType'] }}"
                                    data-salary="{{ $salaryRange }}"
                                    data-status="{{ $job['Status'] ?? 'Active' }}"
                                    {{ $isSelected }}>
                                {{ $job['JobTitle'] }} - {{ $job['Department'] }}{{ $statusLabel }}
                            </option>
                        @endforeach
                    </select>
                </div>


                <div class="job-details" id="jobDetails" style="display: none;">
                    <div class="job-info-card">
                        <h4><i class="fas fa-info-circle"></i> Job Details</h4>
                        <div class="job-info-grid">
                            <div class="info-item">
                                <span class="info-label">Department:</span>
                                <span class="info-value" id="jobDepartment">-</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Location:</span>
                                <span class="info-value" id="jobLocation">-</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Type:</span>
                                <span class="info-value" id="jobType">-</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Salary:</span>
                                <span class="info-value" id="jobSalary">-</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="choice-section" id="choiceSection" style="display: none;">
                <div class="choice-title">
                    <i class="fas fa-route"></i> How would you like to create the exam questions?
                </div>

                <div class="choice-options">
                    <div class="choice-card" data-choice="manual">
                        <div class="choice-icon">
                            <i class="fas fa-edit"></i>
                        </div>
                        <div class="choice-label">Manual Creation</div>
                        <div class="choice-description">
                            Create your own custom questions with complete control over content and difficulty
                        </div>
                    </div>

                    <div class="choice-card" data-choice="auto">
                        <div class="choice-icon">
                            <i class="fas fa-magic"></i>
                        </div>
                        <div class="choice-label">Auto Generation</div>
                        <div class="choice-description">
                            Let our AI system generate relevant questions based on the selected position
                        </div>
                    </div>
                </div>

                <div class="selection-message" id="manualMessage">
                    <div class="message-content">
                        <i class="fas fa-check-circle message-icon"></i>
                        <div>
                            <strong>You have selected Manual Creation!</strong><br>
                            You will need to submit your own questions for the test. Create engaging MCQ questions with multiple options.
                        </div>
                    </div>
                    <a href="#" class="proceed-btn" id="manualProceedBtn">
                        <i class="fas fa-arrow-right"></i> Tap here to submit questions
                    </a>
                </div>

                <div class="selection-message" id="autoMessage">
                    <div class="message-content">
                        <i class="fas fa-check-circle message-icon"></i>
                        <div>
                            <strong>You have selected Auto Generation!</strong><br>
                            We will create appropriate questions for this position automatically based on industry standards.
                        </div>
                    </div>
                    <a href="#" class="proceed-btn" id="autoProceedBtn">
                        <i class="fas fa-arrow-right"></i> Configure auto generation
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        const positionSelect = document.getElementById('position');
        const choiceSection = document.getElementById('choiceSection');
        const choiceCards = document.querySelectorAll('.choice-card');
        const manualMessage = document.getElementById('manualMessage');
        const autoMessage = document.getElementById('autoMessage');
        const manualProceedBtn = document.getElementById('manualProceedBtn');
        const autoProceedBtn = document.getElementById('autoProceedBtn');

        if (positionSelect) {
            // Show choice section when position is selected
            positionSelect.addEventListener('change', function() {
                if (this.value) {
                    // Show job details
                    const selectedOption = this.options[this.selectedIndex];
                    const jobDetails = document.getElementById('jobDetails');
                    
                    if (jobDetails) {
                        document.getElementById('jobDepartment').textContent = selectedOption.dataset.department || '-';
                        document.getElementById('jobLocation').textContent = selectedOption.dataset.location || '-';
                        document.getElementById('jobType').textContent = selectedOption.dataset.type || '-';
                        document.getElementById('jobSalary').textContent = selectedOption.dataset.salary || '-';
                        jobDetails.style.display = 'block';
                    }
                    
                    choiceSection.style.display = 'block';
                    choiceSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    
                    // Reset any previous selections
                    choiceCards.forEach(card => card.classList.remove('selected'));
                    manualMessage.classList.remove('show');
                    autoMessage.classList.remove('show');
                } else {
                    choiceSection.style.display = 'none';
                    const jobDetails = document.getElementById('jobDetails');
                    if (jobDetails) {
                        jobDetails.style.display = 'none';
                    }
                }
            });

            // Auto-select job if value is pre-set (e.g. from server-side rendering or query param emulation)
            if (positionSelect.value) {
                positionSelect.dispatchEvent(new Event('change'));
            }
        }

        // Handle choice selection
        choiceCards.forEach(card => {
            card.addEventListener('click', function() {
                const choice = this.dataset.choice;
                const selectedPosition = positionSelect.value;
                
                if (!selectedPosition) {
                    alert('Please select a position first!');
                    return;
                }

                // Remove previous selections
                choiceCards.forEach(c => c.classList.remove('selected'));
                manualMessage.classList.remove('show');
                autoMessage.classList.remove('show');
                
                // Add selection to clicked card
                this.classList.add('selected');
                
                // Show appropriate message
                if (choice === 'manual') {
                    manualMessage.classList.add('show');
                    // Update the link with job ID and department
                    const selectedOption = positionSelect.options[positionSelect.selectedIndex];
                    const department = selectedOption.dataset.department;
                    // Use Laravel route placeholder
                    manualProceedBtn.href = `{{ url('company/exams/manual-creation') }}?job_id=${selectedPosition}&department=${encodeURIComponent(department)}`;
                } else {
                    autoMessage.classList.add('show');
                    // Update the link with job ID and department
                    const selectedOption = positionSelect.options[positionSelect.selectedIndex];
                    const department = selectedOption.dataset.department;
                    // Use Laravel route placeholder
                    autoProceedBtn.href = `{{ url('company/exams/auto-creation') }}?job_id=${selectedPosition}&department=${encodeURIComponent(department)}`;
                }
            });
            
            card.addEventListener('mouseenter', function() {
                if (!this.classList.contains('selected')) {
                    this.style.transform = 'translateY(-5px)';
                }
            });
            
            card.addEventListener('mouseleave', function() {
                if (!this.classList.contains('selected')) {
                    this.style.transform = 'translateY(0)';
                }
            });
        });

        console.log('Create Exam Page Loaded');
    </script>
</body>
</html>

<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auto Exam Creation - CandiHire</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/AutoExamCreation.css') }}">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-magic"></i> Auto Exam Creation</h1>
            <p>Generate exam questions automatically based on industry standards</p>
            <div class="position-badge">
                <i class="fas fa-briefcase"></i> {{ $positionDisplay ?? 'Selected Position' }}
            </div>
        </div>

        <a href="{{ url('create-exam') }}" class="back-btn">
            <i class="fas fa-arrow-left"></i>
            Back to Exam Type Selection
        </a>

        <div class="validation-message" id="validationMessage"></div>
        <div class="success-message" id="successMessage"></div>

        @if(session('error'))
        <div class="validation-message" style="display: block;">
            {{ session('error') }}
        </div>
        @endif

        <div class="info-card">
            <div class="info-title">
                <i class="fas fa-info-circle"></i>
                How Auto Generation Works
            </div>
            <div class="info-text">
                Our AI system will automatically select relevant questions from our comprehensive question bank for the <strong>{{ $department ?? 'General' }}</strong> department. 
                Questions are randomly selected to ensure variety and cover different difficulty levels and topics within your field.
            </div>
        </div>

        <form id="examForm" method="POST" action="{{ url('exams/auto-generate') }}">
            @csrf
            <input type="hidden" name="job_id" value="{{ $jobId ?? '' }}">
            <input type="hidden" name="department" value="{{ $department ?? '' }}">
            
            <div class="main-card">
                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-cog"></i>
                        Exam Configuration
                    </div>
                    
                    <div class="form-group">
                        <label for="examTitle" class="form-label">
                            <i class="fas fa-heading"></i> Exam Title
                        </label>
                        <input type="text" id="examTitle" name="examTitle" class="form-input" 
                               value="{{ $positionDisplay ?? '' }} Assessment" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="examDuration" class="form-label">
                                <i class="fas fa-clock"></i> Exam Duration (minutes)
                            </label>
                            <input type="number" id="examDuration" name="examDuration" class="form-input" 
                                   min="15" max="180" value="60" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="questionCount" class="form-label">
                                <i class="fas fa-list-ol"></i> Number of Questions
                            </label>
                            <select id="questionCount" name="questionCount" class="form-select" required>
                                <option value="10">10 Questions (15-20 minutes)</option>
                                <option value="20" selected>20 Questions (30-40 minutes)</option>
                                <option value="30">30 Questions (45-60 minutes)</option>
                                <option value="50">50 Questions (75-90 minutes)</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="passingScore" class="form-label">
                                <i class="fas fa-trophy"></i> Passing Score (%)
                            </label>
                            <select id="passingScore" name="passingScore" class="form-select" required>
                                <option value="60">60% (Basic)</option>
                                <option value="70" selected>70% (Standard)</option>
                                <option value="80">80% (Advanced)</option>
                                <option value="90">90% (Expert)</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="difficulty" class="form-label">
                                <i class="fas fa-chart-line"></i> Difficulty Level
                            </label>
                            <select id="difficulty" name="difficulty" class="form-select">
                                <option value="mixed" selected>Mixed (Recommended)</option>
                                <option value="easy">Easy</option>
                                <option value="medium">Medium</option>
                                <option value="hard">Hard</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="btn-actions">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-magic"></i> Generate Exam
                </button>
            </div>
        </form>
    </div>

    <script>
        // Form validation and submission
        document.getElementById('examForm').addEventListener('submit', function(e) {
            const validationMessage = document.getElementById('validationMessage');
            const successMessage = document.getElementById('successMessage');
            
            // Hide previous messages
            validationMessage.style.display = 'none';
            successMessage.style.display = 'none';
            
            // Validate form
            const title = document.getElementById('examTitle').value.trim();
            const duration = document.getElementById('examDuration').value;
            const questionCount = document.getElementById('questionCount').value;
            const passingScore = document.getElementById('passingScore').value;
            
            if (!title || !duration || !questionCount || !passingScore) {
                e.preventDefault();
                validationMessage.textContent = 'Please fill in all exam configuration fields.';
                validationMessage.style.display = 'block';
                return;
            }
            
            // If all validation passes, show loading message
            successMessage.textContent = 'Generating exam questions... Please wait.';
            successMessage.style.display = 'block';
        });


        // Update duration based on question count
        document.getElementById('questionCount').addEventListener('change', function() {
            const questionCount = parseInt(this.value);
            const durationInput = document.getElementById('examDuration');
            
            // Calculate recommended duration (2-3 minutes per question)
            const recommendedDuration = Math.ceil(questionCount * 2.5);
            durationInput.value = Math.min(recommendedDuration, 180);
        });
    </script>
</body>
</html>

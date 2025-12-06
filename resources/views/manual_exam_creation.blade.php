<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manual Exam Creation - CandiHire</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/ManualExamCreation.css') }}">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-edit"></i> Manual Exam Creation</h1>
            <p>Create custom MCQ questions for your assessment</p>
            <div class="position-badge" id="positionBadge">
                <i class="fas fa-briefcase"></i> 
                {{ $position ?? 'Selected Position' }}
            </div>
        </div>

        <a href="{{ url('create-exam') }}" class="back-btn">
            <i class="fas fa-arrow-left"></i>
            Back to Exam Type Selection
        </a>

        <div class="progress-bar">
            <div class="progress-fill" id="progressFill"></div>
        </div>

        <div class="validation-message" id="validationMessage"></div>
        <div class="success-message" id="successMessage"></div>

        @if(session('error'))
        <div class="validation-message" style="display: block;">
            {{ session('error') }}
        </div>
        @endif

        <form id="examForm" method="POST" action="{{ url('exams/manual-store') }}">
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
                        <label for="quizTitle" class="form-label">
                            <i class="fas fa-heading"></i> Quiz Title
                        </label>
                        <input type="text" id="quizTitle" name="quizTitle" class="form-input" placeholder="Enter the quiz title..." required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="examDuration" class="form-label">
                                <i class="fas fa-clock"></i> Exam Duration (minutes)
                            </label>
                            <input type="number" id="examDuration" name="examDuration" class="form-input" min="1" max="300" placeholder="60" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="passingMark" class="form-label">
                                <i class="fas fa-trophy"></i> Passing Mark (%)
                            </label>
                            <input type="number" id="passingMark" name="passingMark" class="form-input" min="1" max="100" placeholder="70" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="totalQuestions" class="form-label">
                                <i class="fas fa-list-ol"></i> Total Questions
                            </label>
                            <input type="number" id="totalQuestions" name="totalQuestions" class="form-input" min="1" max="50" placeholder="10" readonly>
                        </div>
                    </div>
                </div>
            </div>

            <div class="main-card">
                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-question-circle"></i>
                        Questions
                    </div>
                    
                    <div id="questionsContainer">
                        <!-- Questions will be dynamically added here -->
                    </div>
                    
                    <div class="btn-actions">
                        <button type="button" id="addQuestionBtn" class="btn btn-secondary">
                            <i class="fas fa-plus"></i> Add Question
                        </button>
                    </div>
                </div>
            </div>

            <div class="btn-actions">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Save Exam
                </button>
            </div>
        </form>
    </div>

    <script>
        let questionCount = 0;
        let currentQuestions = [];

        // Add first question on page load
        document.addEventListener('DOMContentLoaded', function() {
            addQuestion();
        });

        function addQuestion() {
            questionCount++;
            const questionsContainer = document.getElementById('questionsContainer');
            
            const questionHtml = `
                <div class="question-container" data-question="${questionCount}">
                    <div class="question-header">
                        <span class="question-number">Question ${questionCount}</span>
                        ${questionCount > 1 ? `<button type="button" class="remove-question" onclick="removeQuestion(${questionCount})">
                            <i class="fas fa-trash"></i> Remove
                        </button>` : ''}
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-question"></i> Question Text
                        </label>
                        <textarea class="form-textarea" name="question_${questionCount}" placeholder="Enter your question here..." required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-list"></i> Answer Options
                        </label>
                        <div class="options-grid">
                            <div class="option-group">
                                <span class="option-label">A.</span>
                                <input type="radio" name="correct_${questionCount}" value="A" class="option-radio" required>
                                <input type="text" name="option_${questionCount}_A" class="option-input" placeholder="Option A" required>
                            </div>
                            <div class="option-group">
                                <span class="option-label">B.</span>
                                <input type="radio" name="correct_${questionCount}" value="B" class="option-radio" required>
                                <input type="text" name="option_${questionCount}_B" class="option-input" placeholder="Option B" required>
                            </div>
                            <div class="option-group">
                                <span class="option-label">C.</span>
                                <input type="radio" name="correct_${questionCount}" value="C" class="option-radio" required>
                                <input type="text" name="option_${questionCount}_C" class="option-input" placeholder="Option C" required>
                            </div>
                            <div class="option-group">
                                <span class="option-label">D.</span>
                                <input type="radio" name="correct_${questionCount}" value="D" class="option-radio" required>
                                <input type="text" name="option_${questionCount}_D" class="option-input" placeholder="Option D" required>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            questionsContainer.insertAdjacentHTML('beforeend', questionHtml);
            updateQuestionCount();
            updateProgress();
        }

        function removeQuestion(questionNumber) {
            const questionElement = document.querySelector(`[data-question="${questionNumber}"]`);
            if (questionElement) {
                questionElement.remove();
                questionCount--;
                renumberQuestions();
                updateQuestionCount();
                updateProgress();
            }
        }

        function renumberQuestions() {
            const questions = document.querySelectorAll('.question-container');
            questions.forEach((question, index) => {
                const newNumber = index + 1;
                question.setAttribute('data-question', newNumber);
                
                // Update question number display
                const numberSpan = question.querySelector('.question-number');
                numberSpan.textContent = `Question ${newNumber}`;
                
                // Update form field names
                const textarea = question.querySelector('textarea');
                textarea.name = `question_${newNumber}`;
                
                const radios = question.querySelectorAll('input[type="radio"]');
                radios.forEach(radio => {
                    radio.name = `correct_${newNumber}`;
                });
                
                const textInputs = question.querySelectorAll('.option-input');
                textInputs.forEach((input, optionIndex) => {
                    const optionLetter = ['A', 'B', 'C', 'D'][optionIndex];
                    input.name = `option_${newNumber}_${optionLetter}`;
                });
                
                // Update remove button
                const removeBtn = question.querySelector('.remove-question');
                if (removeBtn) {
                    removeBtn.setAttribute('onclick', `removeQuestion(${newNumber})`);
                }
            });
            
            questionCount = questions.length;
        }

        function updateQuestionCount() {
            document.getElementById('totalQuestions').value = questionCount;
        }

        function updateProgress() {
            // Calculate progress based on filled fields
            const totalFields = questionCount * 6; // question + 4 options + correct answer
            const filledFields = document.querySelectorAll('input:valid, textarea:valid').length;
            const progress = Math.min((filledFields / totalFields) * 100, 100);
            document.getElementById('progressFill').style.width = progress + '%';
        }

        // Add question button event
        document.getElementById('addQuestionBtn').addEventListener('click', addQuestion);

        // Form validation and submission
        document.getElementById('examForm').addEventListener('submit', function(e) {
            const validationMessage = document.getElementById('validationMessage');
            const successMessage = document.getElementById('successMessage');
            
            // Hide previous messages
            validationMessage.style.display = 'none';
            successMessage.style.display = 'none';
            
            // Validate form
            const title = document.getElementById('quizTitle').value.trim();
            const duration = document.getElementById('examDuration').value;
            const passingMark = document.getElementById('passingMark').value;
            
            if (!title || !duration || !passingMark) {
                e.preventDefault();
                validationMessage.textContent = 'Please fill in all exam configuration fields.';
                validationMessage.style.display = 'block';
                return;
            }
            
            // Validate questions
            const questions = document.querySelectorAll('.question-container');
            for (let i = 0; i < questions.length; i++) {
                const questionNum = i + 1;
                const questionText = questions[i].querySelector('textarea').value.trim();
                const correctAnswer = questions[i].querySelector('input[type="radio"]:checked');
                const options = questions[i].querySelectorAll('.option-input');
                
                if (!questionText) {
                    e.preventDefault();
                    validationMessage.textContent = `Please fill in the text for Question ${questionNum}.`;
                    validationMessage.style.display = 'block';
                    return;
                }
                
                if (!correctAnswer) {
                    e.preventDefault();
                    validationMessage.textContent = `Please select the correct answer for Question ${questionNum}.`;
                    validationMessage.style.display = 'block';
                    return;
                }
                
                let emptyOptions = 0;
                options.forEach(option => {
                    if (!option.value.trim()) emptyOptions++;
                });
                
                if (emptyOptions > 0) {
                    e.preventDefault();
                    validationMessage.textContent = `Please fill in all options for Question ${questionNum}.`;
                    validationMessage.style.display = 'block';
                    return;
                }
            }
            
            // If all validation passes, show loading message
            successMessage.textContent = 'Creating exam... Please wait.';
            successMessage.style.display = 'block';
        });


        // Update progress on input change
        document.addEventListener('input', updateProgress);
        document.addEventListener('change', updateProgress);
    </script>
</body>
</html>

<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Generated Exam - CandiHire</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/ExamReview.css') }}">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-eye"></i> Review Generated Exam</h1>
            <p>Review and customize your automatically generated questions</p>
        </div>

        <a href="{{ url('exams/auto-creation') }}" class="back-btn">
            <i class="fas fa-arrow-left"></i>
            Back to Auto Generation
        </a>

        <div class="exam-info" id="examInfo">
            <div class="info-card">
                <div class="info-label">Position</div>
                <div class="info-value">{{ str_replace('-', ' ', $position ?? 'Software Engineer') }}</div>
            </div>
            <div class="info-card">
                <div class="info-label">Questions</div>
                <div class="info-value">{{ $count ?? 10 }}</div>
            </div>
            <div class="info-card">
                <div class="info-label">Duration</div>
                <div class="info-value">{{ $duration ?? 60 }} min</div>
            </div>
            <div class="info-card">
                <div class="info-label">Passing Mark</div>
                <div class="info-value">{{ $passing ?? 70 }}%</div>
            </div>
            <div class="info-card">
                <div class="info-label">Difficulty</div>
                <div class="info-value">{{ ucfirst($difficulty ?? 'Medium') }}</div>
            </div>
        </div>

        <div class="action-bar">
            <div class="action-group">
                <button class="btn btn-warning" id="regenerateBtn">
                    <i class="fas fa-sync-alt"></i> Regenerate All
                </button>
                <button class="btn btn-secondary" id="addQuestionBtn">
                    <i class="fas fa-plus"></i> Add Question
                </button>
            </div>
            <div class="action-group">
                <button class="btn btn-secondary" id="previewBtn">
                    <i class="fas fa-eye"></i> Preview
                </button>
                <form action="{{ url('exams/store-auto') }}" method="POST" id="saveExamForm" style="display:inline;">
                    @csrf
                    <input type="hidden" name="position" value="{{ $position ?? '' }}">
                    <input type="hidden" name="count" value="{{ $count ?? '' }}">
                    <input type="hidden" name="duration" value="{{ $duration ?? '' }}">
                    <input type="hidden" name="passing" value="{{ $passing ?? '' }}">
                    <input type="hidden" name="difficulty" value="{{ $difficulty ?? '' }}">
                    <input type="hidden" name="questions_json" id="questionsJson">
                    <button type="button" class="btn btn-success" id="saveExamBtn">
                        <i class="fas fa-save"></i> Save Exam
                    </button>
                </form>
            </div>
        </div>

        <div id="questionsContainer">
            <!-- Questions will be populated by JavaScript -->
        </div>

        <div class="empty-state" id="emptyState" style="display: none;">
            <div class="empty-icon">
                <i class="fas fa-question-circle"></i>
            </div>
            <div class="empty-text">No questions generated yet</div>
            <button class="btn btn-primary" onclick="generateSampleQuestions()">
                <i class="fas fa-magic"></i> Generate Sample Questions
            </button>
        </div>
    </div>

    <!-- Regenerate Modal -->
    <div class="regenerate-modal" id="regenerateModal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="modal-title">Regenerate All Questions?</div>
                <div class="modal-text">
                    This will replace all current questions with new ones. Any edits you've made will be lost.
                </div>
            </div>
            <div class="modal-actions">
                <button class="btn btn-secondary" id="cancelRegenerate">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button class="btn btn-warning" id="confirmRegenerate">
                    <i class="fas fa-sync-alt"></i> Regenerate
                </button>
            </div>
        </div>
    </div>

    <script>
        // Use Blade variables to initialize exam data
        let examData = {
            questions: [],
            position: '{{ $position ?? "software-engineer" }}',
            difficulty: '{{ $difficulty ?? "medium" }}',
            count: {{ $count ?? 10 }},
            duration: {{ $duration ?? 60 }},
            passing: {{ $passing ?? 70 }}
        };

        let editingQuestionId = null;

        // Sample questions for different positions (Ported from PHP)
        const sampleQuestions = {
            'software-engineer': [
                {
                    id: 1,
                    text: "What is the time complexity of searching in a balanced binary search tree?",
                    options: ["O(1)", "O(log n)", "O(n)", "O(n²)"],
                    correct: 1
                },
                {
                    id: 2,
                    text: "Which design pattern is used to ensure a class has only one instance?",
                    options: ["Factory", "Observer", "Singleton", "Adapter"],
                    correct: 2
                },
                {
                    id: 3,
                    text: "What does SQL stand for?",
                    options: ["Structured Query Language", "Simple Query Language", "System Query Language", "Standard Query Language"],
                    correct: 0
                },
                {
                    id: 4,
                    text: "Which HTTP method is idempotent?",
                    options: ["POST", "GET", "PATCH", "All of the above"],
                    correct: 1
                },
                {
                    id: 5,
                    text: "What is the main purpose of version control systems?",
                    options: ["Code compilation", "Performance optimization", "Track changes and collaboration", "Database management"],
                    correct: 2
                }
            ],
            // Add other roles as needed...
             'frontend-developer': [
                {
                    id: 1,
                    text: "Which CSS property is used to make text bold?",
                    options: ["font-style", "font-weight", "text-decoration", "font-variant"],
                    correct: 1
                },
                {
                    id: 2,
                    text: "What does DOM stand for?",
                    options: ["Document Object Model", "Data Object Model", "Dynamic Object Model", "Display Object Model"],
                    correct: 0
                }
            ]
        };

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            generateSampleQuestions();
            setupEventListeners();
        });

        function generateSampleQuestions() {
            // Get appropriate sample questions based on position
            const positionKey = examData.position.toLowerCase();
            // Fallback to software-engineer if key not found
            let sourceQuestions = sampleQuestions[positionKey] || sampleQuestions['software-engineer'];
            
            // Mock random generation: shuffle and slice
            let questions = [...sourceQuestions].sort(() => 0.5 - Math.random());
            questions = questions.slice(0, examData.count);
            
            // If requested count is more than available samples, duplicate/mock them
            while(questions.length < examData.count) {
                 const baseQ = sourceQuestions[questions.length % sourceQuestions.length];
                 questions.push({
                     ...baseQ,
                     id: questions.length + 1,
                     text: baseQ.text + " (Variant " + (Math.floor(questions.length / sourceQuestions.length) + 1) + ")"
                 });
            }

            examData.questions = questions;
            displayQuestions();
        }

        function displayQuestions() {
            const container = document.getElementById('questionsContainer');
            const emptyState = document.getElementById('emptyState');
            
            if (examData.questions.length === 0) {
                container.innerHTML = '';
                emptyState.style.display = 'block';
                return;
            }
            
            emptyState.style.display = 'none';
            
            container.innerHTML = examData.questions.map((question, index) => `
                <div class="question-card" data-question-id="${question.id}">
                    <div class="question-header">
                        <span class="question-number">Question ${index + 1}</span>
                        <div class="question-actions">
                            <button class="edit-btn" onclick="toggleEdit(${question.id})">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="delete-btn" onclick="deleteQuestion(${question.id})">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </div>
                    <div class="question-content">
                        <div class="question-text">${question.text}</div>
                        <div class="options-container">
                            ${question.options.map((option, optionIndex) => `
                                <div class="option-item ${optionIndex === question.correct ? 'correct' : ''}">
                                    <span class="option-label">${String.fromCharCode(65 + optionIndex)}</span>
                                    <span class="option-text">${option}</span>
                                    ${optionIndex === question.correct ? '<span class="correct-indicator"><i class="fas fa-check"></i></span>' : ''}
                                </div>
                            `).join('')}
                        </div>
                    </div>
                    <div class="edit-form" id="edit-form-${question.id}">
                        <div class="form-group">
                            <label class="form-label">Question Text</label>
                            <textarea class="form-textarea" id="edit-text-${question.id}" rows="3">${question.text}</textarea>
                        </div>
                        <div class="options-edit-grid">
                            ${question.options.map((option, optionIndex) => `
                                <div class="option-edit-group">
                                    <input type="radio" name="edit-correct-${question.id}" value="${optionIndex}" 
                                           class="option-radio" ${optionIndex === question.correct ? 'checked' : ''}>
                                    <input type="text" class="form-input" id="edit-option-${question.id}-${optionIndex}" 
                                           value="${option}" placeholder="Option ${String.fromCharCode(65 + optionIndex)}">
                                </div>
                            `).join('')}
                        </div>
                        <div style="display: flex; gap: 10px; justify-content: flex-end;">
                            <button class="btn btn-secondary" onclick="cancelEdit(${question.id})">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                            <button class="btn btn-success" onclick="saveEdit(${question.id})">
                                <i class="fas fa-save"></i> Save
                            </button>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        function setupEventListeners() {
            // Regenerate button
            document.getElementById('regenerateBtn').addEventListener('click', function() {
                document.getElementById('regenerateModal').classList.add('show');
            });

            // Modal actions
            document.getElementById('cancelRegenerate').addEventListener('click', function() {
                document.getElementById('regenerateModal').classList.remove('show');
            });

            document.getElementById('confirmRegenerate').addEventListener('click', function() {
                document.getElementById('regenerateModal').classList.remove('show');
                generateSampleQuestions();
                alert('Questions regenerated successfully!'); // Simplified notification
            });

            // Save exam button
            document.getElementById('saveExamBtn').addEventListener('click', function() {
                // Populate the hidden input with JSON data
                document.getElementById('questionsJson').value = JSON.stringify(examData.questions);
                document.getElementById('saveExamForm').submit();
            });
            
             // Preview button
            document.getElementById('previewBtn').addEventListener('click', function() {
                alert('Preview functionality will show how the exam appears to candidates.');
            });
             
             // Add Question Btn (Mock functionality)
             document.getElementById('addQuestionBtn').addEventListener('click', function() {
                 const newId = Math.max(...examData.questions.map(q => q.id), 0) + 1;
                 examData.questions.push({
                     id: newId,
                     text: "New Question",
                     options: ["Option A", "Option B", "Option C", "Option D"],
                     correct: 0
                 });
                 displayQuestions();
                 // Immediately open edit for the new question
                 setTimeout(() => toggleEdit(newId), 100);
             });
        }

        function toggleEdit(questionId) {
            const editForm = document.getElementById(`edit-form-${questionId}`);
            const isCurrentlyEditing = editForm.classList.contains('show');
            
            // Close any other edit forms
            document.querySelectorAll('.edit-form.show').forEach(form => {
                form.classList.remove('show');
            });
            
            if (!isCurrentlyEditing) {
                editForm.classList.add('show');
                editingQuestionId = questionId;
            } else {
                editingQuestionId = null;
            }
        }

        function cancelEdit(questionId) {
            document.getElementById(`edit-form-${questionId}`).classList.remove('show');
            editingQuestionId = null;
        }

        function saveEdit(questionId) {
             const questionIndex = examData.questions.findIndex(q => q.id === questionId);
             if (questionIndex === -1) return;

             const text = document.getElementById(`edit-text-${questionId}`).value;
             const options = [];
             let correct = 0;

             for(let i=0; i<4; i++) {
                 options.push(document.getElementById(`edit-option-${questionId}-${i}`).value);
                 if(document.querySelector(`input[name="edit-correct-${questionId}"][value="${i}"]`).checked) {
                     correct = i;
                 }
             }

             // Update data
             examData.questions[questionIndex].text = text;
             examData.questions[questionIndex].options = options;
             examData.questions[questionIndex].correct = correct;

             displayQuestions(); // Re-render to show changes
        }
        
        function deleteQuestion(questionId) {
            if(confirm('Are you sure you want to delete this question?')) {
                examData.questions = examData.questions.filter(q => q.id !== questionId);
                displayQuestions();
            }
        }
    </script>
</body>
</html>

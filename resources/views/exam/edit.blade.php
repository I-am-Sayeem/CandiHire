<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Exam - {{ $exam->ExamTitle ?? 'CandiHire' }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/ExamReview.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-edit"></i> Edit Exam Questions</h1>
            <p>Review and customize your exam questions before assigning to candidates</p>
        </div>

        <a href="{{ url('company/dashboard') }}" class="back-btn">
            <i class="fas fa-arrow-left"></i>
            Back to Dashboard
        </a>

        @if(session('success'))
        <div class="success-message" style="display: block;">
            {{ session('success') }}
        </div>
        @endif

        <div class="exam-info">
            <div class="info-card">
                <div class="info-label">Exam Title</div>
                <div class="info-value">{{ $exam->ExamTitle ?? 'N/A' }}</div>
            </div>
            <div class="info-card">
                <div class="info-label">Questions</div>
                <div class="info-value">{{ $exam->QuestionCount ?? 0 }}</div>
            </div>
            <div class="info-card">
                <div class="info-label">Duration</div>
                <div class="info-value">{{ floor(($exam->Duration ?? 0) / 60) }} min</div>
            </div>
            <div class="info-card">
                <div class="info-label">Passing Score</div>
                <div class="info-value">{{ $exam->PassingScore ?? 70 }}%</div>
            </div>
            <div class="info-card">
                <div class="info-label">Type</div>
                <div class="info-value">{{ ucfirst($exam->ExamType ?? 'Manual') }}</div>
            </div>
        </div>

        <form id="examEditForm" method="POST" action="{{ url('company/exams/' . $exam->ExamID . '/update') }}">
            @csrf
            
            <div id="questionsContainer">
                @forelse($questions as $index => $question)
                <div class="question-card" data-question-id="{{ $question->QuestionID }}">
                    <div class="question-header">
                        <span class="question-number">Question {{ $index + 1 }}</span>
                        <button type="button" class="delete-btn" onclick="deleteQuestion({{ $question->QuestionID }})">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                    <div class="question-content">
                        <div class="form-group">
                            <label class="form-label">Question Text</label>
                            <textarea name="questions[{{ $question->QuestionID }}][text]" class="form-textarea" rows="3" required>{{ $question->QuestionText }}</textarea>
                        </div>
                        <div class="options-edit-grid">
                            @foreach($question->options as $optIndex => $option)
                            <div class="option-edit-group">
                                <input type="radio" 
                                       name="questions[{{ $question->QuestionID }}][correct]" 
                                       value="{{ $option->OptionID }}" 
                                       class="option-radio" 
                                       {{ $option->IsCorrect ? 'checked' : '' }}>
                                <input type="text" 
                                       name="questions[{{ $question->QuestionID }}][options][{{ $option->OptionID }}]" 
                                       class="form-input" 
                                       value="{{ $option->OptionText }}" 
                                       placeholder="Option {{ chr(65 + $optIndex) }}"
                                       required>
                                @if($option->IsCorrect)
                                <span class="correct-indicator"><i class="fas fa-check"></i></span>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @empty
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-question-circle"></i>
                    </div>
                    <div class="empty-text">No questions found for this exam</div>
                </div>
                @endforelse
            </div>

            <div class="action-bar" style="margin-top: 20px;">
                <div class="action-group">
                    <button type="button" class="btn btn-secondary" onclick="addNewQuestion()">
                        <i class="fas fa-plus"></i> Add Question
                    </button>
                </div>
                <div class="action-group">
                    <a href="{{ url('company/dashboard') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </div>
            </div>
        </form>
    </div>

    <style>
        .success-message {
            background: var(--success, #3fb950);
            color: white;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .question-card {
            background: var(--bg-secondary, #161b22);
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
            border: 1px solid var(--border, #30363d);
        }
        
        .question-card.new-question {
            border-color: var(--accent-1, #58a6ff);
            animation: fadeIn 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .question-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border, #30363d);
        }
        
        .question-number {
            background: linear-gradient(135deg, var(--accent-1, #58a6ff), var(--accent-2, #f59e0b));
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 600;
        }
        
        .delete-btn {
            background: var(--danger, #f85149);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }
        
        .delete-btn:hover {
            background: #dc2626;
            transform: translateY(-2px);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-primary, #c9d1d9);
        }
        
        .form-textarea, .form-input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid var(--border, #30363d);
            border-radius: 8px;
            background: var(--bg-tertiary, #21262d);
            color: var(--text-primary, #c9d1d9);
            font-size: 1rem;
            transition: all 0.2s;
        }
        
        .form-textarea:focus, .form-input:focus {
            outline: none;
            border-color: var(--accent-1, #58a6ff);
            box-shadow: 0 0 0 3px rgba(88, 166, 255, 0.1);
        }
        
        .options-edit-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .option-edit-group {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px;
            background: var(--bg-primary, #0d1117);
            border-radius: 8px;
            border: 1px solid var(--border, #30363d);
        }
        
        .option-radio {
            width: 20px;
            height: 20px;
            cursor: pointer;
            accent-color: var(--success, #3fb950);
        }
        
        .correct-indicator {
            color: var(--success, #3fb950);
            margin-left: auto;
        }
        
        .action-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            background: var(--bg-secondary, #161b22);
            border-radius: 12px;
            border: 1px solid var(--border, #30363d);
        }
        
        .action-group {
            display: flex;
            gap: 10px;
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-secondary {
            background: var(--bg-tertiary, #21262d);
            color: var(--text-primary, #c9d1d9);
            border: 1px solid var(--border, #30363d);
        }
        
        .btn-success {
            background: var(--success, #3fb950);
            color: white;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25);
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: var(--bg-secondary, #161b22);
            border-radius: 12px;
            border: 1px solid var(--border, #30363d);
        }
        
        .empty-icon {
            font-size: 3rem;
            color: var(--text-secondary, #8b949e);
            margin-bottom: 15px;
        }
        
        .empty-text {
            color: var(--text-secondary, #8b949e);
            font-size: 1.1rem;
        }
        
        @media (max-width: 768px) {
            .options-edit-grid {
                grid-template-columns: 1fr;
            }
            
            .action-bar {
                flex-direction: column;
                gap: 15px;
            }
        }
    </style>

    <script>
        let newQuestionCount = 0;
        
        function addNewQuestion() {
            newQuestionCount++;
            const container = document.getElementById('questionsContainer');
            const questionCount = container.querySelectorAll('.question-card').length + 1;
            const newId = 'new_' + newQuestionCount;
            
            const questionHtml = `
                <div class="question-card new-question" data-question-id="${newId}">
                    <div class="question-header">
                        <span class="question-number">New Question ${questionCount}</span>
                        <button type="button" class="delete-btn" onclick="removeNewQuestion('${newId}')">
                            <i class="fas fa-trash"></i> Remove
                        </button>
                    </div>
                    <div class="question-content">
                        <div class="form-group">
                            <label class="form-label">Question Text</label>
                            <textarea name="new_questions[${newId}][text]" class="form-textarea" rows="3" placeholder="Enter your question here..." required></textarea>
                        </div>
                        <div class="options-edit-grid">
                            <div class="option-edit-group">
                                <input type="radio" name="new_questions[${newId}][correct]" value="0" class="option-radio" checked>
                                <input type="text" name="new_questions[${newId}][options][0]" class="form-input" placeholder="Option A" required>
                                <span class="correct-indicator"><i class="fas fa-check"></i></span>
                            </div>
                            <div class="option-edit-group">
                                <input type="radio" name="new_questions[${newId}][correct]" value="1" class="option-radio">
                                <input type="text" name="new_questions[${newId}][options][1]" class="form-input" placeholder="Option B" required>
                            </div>
                            <div class="option-edit-group">
                                <input type="radio" name="new_questions[${newId}][correct]" value="2" class="option-radio">
                                <input type="text" name="new_questions[${newId}][options][2]" class="form-input" placeholder="Option C" required>
                            </div>
                            <div class="option-edit-group">
                                <input type="radio" name="new_questions[${newId}][correct]" value="3" class="option-radio">
                                <input type="text" name="new_questions[${newId}][options][3]" class="form-input" placeholder="Option D" required>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            container.insertAdjacentHTML('beforeend', questionHtml);
            
            // Scroll to new question
            const newCard = container.lastElementChild;
            newCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
            
            // Focus on the question text
            newCard.querySelector('textarea').focus();
            
            // Add radio change listeners to new question
            newCard.querySelectorAll('.option-radio').forEach(radio => {
                radio.addEventListener('change', handleRadioChange);
            });
        }
        
        function removeNewQuestion(newId) {
            const card = document.querySelector(`[data-question-id="${newId}"]`);
            if (card) {
                card.remove();
            }
        }
        
        function deleteQuestion(questionId) {
            if (confirm('Are you sure you want to delete this question?')) {
                const card = document.querySelector(`[data-question-id="${questionId}"]`);
                if (card) {
                    card.remove();
                    // Add hidden input to track deleted questions
                    const form = document.getElementById('examEditForm');
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'deleted_questions[]';
                    input.value = questionId;
                    form.appendChild(input);
                }
            }
        }
        
        function handleRadioChange() {
            // Remove all check indicators in this question
            const card = this.closest('.question-card');
            card.querySelectorAll('.correct-indicator').forEach(ind => ind.remove());
            
            // Add indicator to selected option
            const indicator = document.createElement('span');
            indicator.className = 'correct-indicator';
            indicator.innerHTML = '<i class="fas fa-check"></i>';
            this.closest('.option-edit-group').appendChild(indicator);
        }
        
        // Highlight correct option when radio is changed
        document.querySelectorAll('.option-radio').forEach(radio => {
            radio.addEventListener('change', handleRadioChange);
        });
    </script>
</body>
</html>


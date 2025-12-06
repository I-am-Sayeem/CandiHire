<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Generated Exam - CandiHire</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg-primary: #0d1117;
            --bg-secondary: #161b22;
            --bg-tertiary: #21262d;
            --text-primary: #c9d1d9;
            --text-secondary: #8b949e;
            --accent-1: #58a6ff;
            --accent-2: #f59e0b;
            --accent-hover: #79c0ff;
            --border: #30363d;
            --success: #3fb950;
            --danger: #f85149;
            --warning: #d29922;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Noto Sans', Helvetica, Arial, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #0c1445 0%, #1a237e 50%, #283593 100%);
            color: var(--text-primary);
            line-height: 1.6;
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px 0;
        }

        .header h1 {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 10px;
            background: linear-gradient(135deg, var(--accent-1), var(--accent-2));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .header p {
            font-size: 1rem;
            color: var(--text-secondary);
        }

        .exam-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }

        .info-card {
            background: var(--bg-secondary);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 15px;
            text-align: center;
        }

        .info-label {
            color: var(--text-secondary);
            font-size: 0.9rem;
            margin-bottom: 5px;
        }

        .info-value {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--accent-1);
        }

        .back-btn {
            background: transparent;
            color: var(--text-secondary);
            border: 1px solid var(--border);
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
        }

        .back-btn:hover {
            background: var(--bg-tertiary);
            color: var(--text-primary);
        }

        .action-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .action-group {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 10px 16px;
            border: none;
            border-radius: 6px;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            will-change: transform, box-shadow;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--accent-1), var(--accent-2));
            color: white;
        }

        .btn-secondary {
            background: var(--bg-tertiary);
            color: var(--text-primary);
            border: 1px solid var(--border);
        }

        .btn-success {
            background: var(--success);
            color: white;
        }

        .btn-warning {
            background: var(--warning);
            color: white;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.25);
        }

        .question-card {
            background: var(--bg-secondary);
            border: 1px solid var(--border);
            border-radius: 12px;
            margin-bottom: 20px;
            overflow: hidden;
            transition: all 0.3s ease;
            will-change: transform, box-shadow, border-color;
        }

        .question-card:hover {
            border-color: var(--accent-1);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(88, 166, 255, 0.15);
        }

        .question-header {
            background: var(--bg-tertiary);
            padding: 15px 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .question-number {
            background: linear-gradient(135deg, var(--accent-1), var(--accent-2));
            color: white;
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .question-actions {
            display: flex;
            gap: 8px;
        }

        .edit-btn, .delete-btn {
            background: transparent;
            border: 1px solid var(--border);
            color: var(--text-secondary);
            padding: 6px 10px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.8rem;
            transition: all 0.3s ease;
            will-change: transform, background-color, border-color;
        }

        .edit-btn:hover {
            background: var(--accent-1);
            color: white;
            border-color: var(--accent-1);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(88, 166, 255, 0.3);
        }

        .delete-btn:hover {
            background: var(--danger);
            color: white;
            border-color: var(--danger);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(248, 81, 73, 0.3);
        }

        .question-content {
            padding: 20px;
        }

        .question-text {
            font-size: 1.1rem;
            font-weight: 500;
            margin-bottom: 15px;
            line-height: 1.5;
        }

        .options-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .option-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px;
            background: var(--bg-tertiary);
            border-radius: 6px;
            border: 1px solid var(--border);
        }

        .option-item.correct {
            background: rgba(63, 185, 80, 0.1);
            border-color: var(--success);
        }

        .option-label {
            background: var(--accent-1);
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: 600;
            font-size: 0.8rem;
            min-width: 20px;
            text-align: center;
        }

        .option-item.correct .option-label {
            background: var(--success);
        }

        .option-text {
            flex: 1;
        }

        .correct-indicator {
            color: var(--success);
            font-size: 0.9rem;
        }

        .edit-form {
            display: none;
            background: var(--bg-primary);
            padding: 20px;
            border-top: 1px solid var(--border);
        }

        .edit-form.show {
            display: block;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            color: var(--text-primary);
            font-size: 0.9rem;
        }

        .form-textarea, .form-input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--border);
            border-radius: 6px;
            background: var(--bg-tertiary);
            color: var(--text-primary);
            font-size: 0.9rem;
            resize: vertical;
        }

        .form-textarea:focus, .form-input:focus {
            outline: none;
            border-color: var(--accent-1);
        }

        .options-edit-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 15px;
        }

        .option-edit-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .option-radio {
            width: 16px;
            height: 16px;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-secondary);
        }

        .empty-icon {
            font-size: 4rem;
            margin-bottom: 20px;
        }

        .empty-text {
            font-size: 1.2rem;
            margin-bottom: 30px;
        }

        .regenerate-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .regenerate-modal.show {
            display: flex;
        }

        .modal-content {
            background: var(--bg-secondary);
            border-radius: 12px;
            padding: 30px;
            max-width: 500px;
            width: 90%;
            border: 1px solid var(--border);
        }

        .modal-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .modal-icon {
            font-size: 3rem;
            color: var(--warning);
            margin-bottom: 15px;
        }

        .modal-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .modal-text {
            color: var(--text-secondary);
            margin-bottom: 25px;
        }

        .modal-actions {
            display: flex;
            gap: 10px;
            justify-content: center;
        }

        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }
            
            .options-container, .options-edit-grid {
                grid-template-columns: 1fr;
            }
            
            .action-bar {
                flex-direction: column;
                align-items: stretch;
            }
            
            .action-group {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-eye"></i> Review Generated Exam</h1>
            <p>Review and customize your automatically generated questions</p>
        </div>

        <a href="AutoExamCreation.php" class="back-btn">
            <i class="fas fa-arrow-left"></i>
            Back to Auto Generation
        </a>

        <div class="exam-info" id="examInfo">
            <!-- Exam info will be populated by JavaScript -->
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
                <button class="btn btn-success" id="saveExamBtn">
                    <i class="fas fa-save"></i> Save Exam
                </button>
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
        let examData = {
            questions: [],
            position: '',
            difficulty: '',
            count: 0,
            duration: 0,
            passing: 0
        };

        let editingQuestionId = null;

        // Get parameters from URL
        const urlParams = new URLSearchParams(window.location.search);
        examData.position = urlParams.get('position') || 'Software Engineer';
        examData.difficulty = urlParams.get('difficulty') || 'medium';
        examData.count = parseInt(urlParams.get('count')) || 10;
        examData.duration = parseInt(urlParams.get('duration')) || 60;
        examData.passing = parseInt(urlParams.get('passing')) || 70;

        // Sample questions for different positions
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
                },
                {
                    id: 3,
                    text: "Which JavaScript method adds an element to the end of an array?",
                    options: ["push()", "pop()", "shift()", "unshift()"],
                    correct: 0
                },
                {
                    id: 4,
                    text: "What is the default display property of a div element?",
                    options: ["inline", "block", "inline-block", "flex"],
                    correct: 1
                },
                {
                    id: 5,
                    text: "Which HTML5 element is used for drawing graphics?",
                    options: ["<svg>", "<canvas>", "<graphics>", "<draw>"],
                    correct: 1
                }
            ],
            'data-scientist': [
                {
                    id: 1,
                    text: "What is the primary goal of supervised learning?",
                    options: ["Find hidden patterns", "Predict outcomes from labeled data", "Reduce dimensionality", "Generate new data"],
                    correct: 1
                },
                {
                    id: 2,
                    text: "Which Python library is commonly used for data manipulation?",
                    options: ["NumPy", "Pandas", "Matplotlib", "Scikit-learn"],
                    correct: 1
                },
                {
                    id: 3,
                    text: "What does SQL's GROUP BY clause do?",
                    options: ["Sorts data", "Filters rows", "Groups rows with same values", "Joins tables"],
                    correct: 2
                },
                {
                    id: 4,
                    text: "Which metric is used to evaluate classification models?",
                    options: ["Mean Squared Error", "Accuracy", "R-squared", "Mean Absolute Error"],
                    correct: 1
                },
                {
                    id: 5,
                    text: "What is overfitting in machine learning?",
                    options: ["Model is too simple", "Model performs well on training but poorly on test data", "Model has high bias", "Model converges slowly"],
                    correct: 1
                }
            ]
        };

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            displayExamInfo();
            generateSampleQuestions();
            setupEventListeners();
        });

        function displayExamInfo() {
            const examInfo = document.getElementById('examInfo');
            examInfo.innerHTML = `
                <div class="info-card">
                    <div class="info-label">Position</div>
                    <div class="info-value">${examData.position.replace('-', ' ').replace(/\b\w/g, l => l.toUpperCase())}</div>
                </div>
                <div class="info-card">
                    <div class="info-label">Questions</div>
                    <div class="info-value">${examData.count}</div>
                </div>
                <div class="info-card">
                    <div class="info-label">Duration</div>
                    <div class="info-value">${examData.duration} min</div>
                </div>
                <div class="info-card">
                    <div class="info-label">Passing Mark</div>
                    <div class="info-value">${examData.passing}%</div>
                </div>
                <div class="info-card">
                    <div class="info-label">Difficulty</div>
                    <div class="info-value">${examData.difficulty.charAt(0).toUpperCase() + examData.difficulty.slice(1)}</div>
                </div>
            `;
        }

        function generateSampleQuestions() {
            // Get appropriate sample questions based on position
            const positionKey = examData.position.toLowerCase();
            let questions = sampleQuestions[positionKey] || sampleQuestions['software-engineer'];
            
            // Limit to requested count
            questions = questions.slice(0, examData.count);
            
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
                showNotification('Questions regenerated successfully!', 'success');
            });

            // Add question button
            document.getElementById('addQuestionBtn').addEventListener('click', addNewQuestion);

            // Preview button
            document.getElementById('previewBtn').addEventListener('click', function() {
                alert('Preview functionality will show how the exam appears to candidates.');
            });

            // Save exam button
            document.getElementById('saveExamBtn').addEventListener('click', function() {
                showNotification('Exam saved successfully! Redirecting to dashboard...', 'success');
                setTimeout(() => {
                    window.location.href = 'CompanyDashboard.php';
                }, 2000);
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
            const question = examData.questions.find(q => q.id === questionId);
            if (!question) return;

            // Get new values
            const newText = document.getElementById(`edit-text-${questionId}`).value.trim();
            const newOptions = [];
            let newCorrect = 0;

            for (let i = 0; i < 4; i++) {
                const optionValue = document.getElementById(`edit-option-${questionId}-${i}`).value.trim();
                if (!optionValue) {
                    alert('Please fill in all options.');
                    return;
                }
                newOptions.push(optionValue);
                
                const radio = document.querySelector(`input[name="edit-correct-${questionId}"][value="${i}"]`);
                if (radio && radio.checked) {
                    newCorrect = i;
                }
            }

            if (!newText) {
                alert('Please fill in the question text.');
                return;
            }

            // Update question
            question.text = newText;
            question.options = newOptions;
            question.correct = newCorrect;

            // Close edit form and refresh display
            cancelEdit(questionId);
            displayQuestions();
            showNotification('Question updated successfully!', 'success');
        }

        function deleteQuestion(questionId) {
            if (confirm('Are you sure you want to delete this question?')) {
                examData.questions = examData.questions.filter(q => q.id !== questionId);
                displayQuestions();
                showNotification('Question deleted successfully!', 'success');
            }
        }

        function addNewQuestion() {
            const newId = Math.max(...examData.questions.map(q => q.id), 0) + 1;
            const newQuestion = {
                id: newId,
                text: "Enter your question here...",
                options: ["Option A", "Option B", "Option C", "Option D"],
                correct: 0
            };

            examData.questions.push(newQuestion);
            displayQuestions();
            
            // Automatically open edit mode for the new question
            setTimeout(() => toggleEdit(newId), 100);
        }

        function showNotification(message, type = 'info') {
            // Create a simple notification (you could enhance this with a proper notification system)
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: ${type === 'success' ? 'var(--success)' : 'var(--accent-1)'};
                color: white;
                padding: 15px 20px;
                border-radius: 8px;
                z-index: 1000;
                font-weight: 500;
                box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            `;
            notification.textContent = message;
            document.body.appendChild(notification);

            setTimeout(() => {
                notification.remove();
            }, 3000);
        }
    </script>
</body>
</html>

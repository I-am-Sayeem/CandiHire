<?php
// ManualExamCreation.php - Manual Exam Creation with Database Integration
require_once 'session_manager.php';

// Check if company is logged in
if (!isCompanyLoggedIn()) {
    header('Location: Login&Signup.php');
    exit;
}

// Get company ID from session
$sessionCompanyId = getCurrentCompanyId();
$companyName = $_SESSION['company_name'] ?? 'Company';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'Database.php';
    
    try {
        if (isset($pdo) && $pdo instanceof PDO) {
            // Start transaction
            $pdo->beginTransaction();
            
            // Insert exam
            $examStmt = $pdo->prepare("
                INSERT INTO exams (CompanyID, ExamTitle, ExamType, Description, Instructions, Duration, QuestionCount, PassingScore, MaxAttempts, CreatedBy) 
                VALUES (?, ?, 'manual', ?, ?, ?, ?, ?, 1, ?)
            ");
            
                   $examStmt->execute([
                       $sessionCompanyId,
                       $_POST['quizTitle'],
                       'Manual exam created for ' . $_POST['department'],
                       'Please read each question carefully and select the best answer.',
                       $_POST['examDuration'] * 60, // Convert to seconds
                       $_POST['totalQuestions'],
                       $_POST['passingMark'],
                       $companyName
                   ]);
            
            $examId = $pdo->lastInsertId();
            
            // Insert questions
            $questionStmt = $pdo->prepare("
                INSERT INTO exam_questions (ExamID, QuestionType, QuestionText, QuestionOrder, Points, Difficulty, Category) 
                VALUES (?, 'multiple-choice', ?, ?, 1.00, 'medium', ?)
            ");
            
            $optionStmt = $pdo->prepare("
                INSERT INTO exam_question_options (QuestionID, OptionText, IsCorrect, OptionOrder) 
                VALUES (?, ?, ?, ?)
            ");
            
            $questionNumber = 1;
            foreach ($_POST as $key => $value) {
                if (strpos($key, 'question_') === 0) {
                    $questionNum = substr($key, 9);
                    $questionText = $value;
                    
                    // Insert question
                           $questionStmt->execute([
                               $examId,
                               $questionText,
                               $questionNumber,
                               $_POST['department']
                           ]);
                    
                    $questionId = $pdo->lastInsertId();
                    
                    // Insert options
                    $correctAnswer = $_POST["correct_{$questionNum}"];
                    $options = ['A', 'B', 'C', 'D'];
                    
                    foreach ($options as $index => $option) {
                        $optionText = $_POST["option_{$questionNum}_{$option}"];
                        $isCorrect = ($option === $correctAnswer) ? 1 : 0;
                        
                        $optionStmt->execute([
                            $questionId,
                            $optionText,
                            $isCorrect,
                            $index + 1
                        ]);
                    }
                    
                    $questionNumber++;
                }
            }
            
            // Commit transaction
            $pdo->commit();
            
            // Handle exam question addition and assignment
            require_once 'exam_question_assignment_handler.php';
            $assigned = handleExamQuestionAddition($examId, $sessionCompanyId, $jobId);
            if ($assigned) {
                error_log("Exam questions added and assigned to job applicants successfully");
            } else {
                error_log("Failed to assign exam to job applicants after adding questions");
            }
            
            // Redirect with success message
            header('Location: CompanyDashboard.php?exam_created=1');
            exit;
            
        } else {
            throw new Exception('Database connection not available');
        }
        
    } catch (Exception $e) {
        if (isset($pdo)) {
            $pdo->rollBack();
        }
        error_log("Error creating manual exam: " . $e->getMessage());
        $errorMessage = "Failed to create exam. Please try again.";
    }
}

// Get job ID and department from URL
$jobId = $_GET['job_id'] ?? '';
$department = $_GET['department'] ?? '';

// Load job details from database
$jobDetails = null;
if ($jobId) {
    try {
        if (isset($pdo) && $pdo instanceof PDO) {
            $stmt = $pdo->prepare("SELECT JobTitle, Department, Location, JobType, SalaryMin, SalaryMax, Currency FROM job_postings WHERE JobID = ? AND CompanyID = ?");
            $stmt->execute([$jobId, $sessionCompanyId]);
            $jobDetails = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    } catch (Exception $e) {
        error_log("Error loading job details: " . $e->getMessage());
    }
}

$positionDisplay = $jobDetails ? $jobDetails['JobTitle'] : 'Selected Position';

// Function to assign exam to all existing applicants for a job
function assignExamToAllApplicantsForJob($examId, $jobId, $pdo) {
    try {
        // Get all applicants for this job who don't already have an exam assigned
        $applicantsStmt = $pdo->prepare("
            SELECT DISTINCT ja.CandidateID, cli.FullName, cli.Email
            FROM job_applications ja
            JOIN candidate_login_info cli ON ja.CandidateID = cli.CandidateID
            LEFT JOIN exam_assignments ea ON ja.CandidateID = ea.CandidateID AND ja.JobID = ea.JobID
            WHERE ja.JobID = ? AND ea.AssignmentID IS NULL
        ");
        $applicantsStmt->execute([$jobId]);
        $applicants = $applicantsStmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($applicants)) {
            error_log("No new applicants found to assign exam to for job ID: $jobId");
            return;
        }
        
        // Assign exam to all applicants
        $assignmentStmt = $pdo->prepare("
            INSERT INTO exam_assignments (ExamID, CandidateID, JobID, AssignmentDate, Status, DueDate) 
            VALUES (?, ?, ?, NOW(), 'assigned', DATE_ADD(NOW(), INTERVAL 7 DAY))
        ");
        
        $assignedCount = 0;
        foreach ($applicants as $applicant) {
            try {
                $result = $assignmentStmt->execute([
                    $examId, 
                    $applicant['CandidateID'], 
                    $jobId
                ]);
                
                if ($result) {
                    $assignedCount++;
                    error_log("Exam assigned to candidate: {$applicant['FullName']} (ID: {$applicant['CandidateID']})");
                }
            } catch (Exception $e) {
                error_log("Error assigning exam to candidate {$applicant['CandidateID']}: " . $e->getMessage());
            }
        }
        
        error_log("Exam ID $examId assigned to $assignedCount applicants for job ID $jobId");
        
    } catch (Exception $e) {
        error_log("Error assigning exam to all applicants: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manual Exam Creation - CandiHire</title>
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
            background: #0d1117;
            color: var(--text-primary);
            line-height: 1.6;
            min-height: 100vh;
        }

        .container {
            max-width: 1000px;
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

        .position-badge {
            display: inline-block;
            background: rgba(88, 166, 255, 0.1);
            color: var(--accent-1);
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
            margin-top: 10px;
            border: 1px solid var(--accent-1);
        }

        .main-card {
            background: var(--bg-secondary);
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            border: 1px solid var(--border);
            margin-bottom: 20px;
        }

        .form-section {
            margin-bottom: 30px;
        }

        .section-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--accent-1);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-primary);
        }

        .form-input, .form-textarea {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid var(--border);
            border-radius: 8px;
            background: var(--bg-tertiary);
            color: var(--text-primary);
            font-size: 1rem;
            transition: all 0.2s;
            resize: vertical;
        }

        .form-input:focus, .form-textarea:focus {
            outline: none;
            border-color: var(--accent-1);
            box-shadow: 0 0 0 3px rgba(88, 166, 255, 0.1);
        }

        .form-textarea {
            min-height: 100px;
        }

        .question-container {
            background: var(--bg-tertiary);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
            position: relative;
        }

        .question-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 20px;
        }

        .question-number {
            background: linear-gradient(135deg, var(--accent-1), var(--accent-2));
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .remove-question {
            background: var(--danger);
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.8rem;
            transition: all 0.3s ease;
            will-change: transform, background-color;
        }

        .remove-question:hover {
            background: #dc2626;
            transform: scale(1.05) translateY(-1px);
            box-shadow: 0 4px 12px rgba(248, 81, 73, 0.3);
        }

        .options-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }

        .option-group {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px;
            background: var(--bg-secondary);
            border-radius: 8px;
            border: 1px solid var(--border);
        }

        .option-radio {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .option-input {
            flex: 1;
            background: transparent;
            border: none;
            color: var(--text-primary);
            font-size: 0.95rem;
        }

        .option-input:focus {
            outline: none;
        }

        .option-label {
            font-weight: 500;
            color: var(--accent-1);
            min-width: 20px;
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

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25);
        }

        .btn-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
            flex-wrap: wrap;
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

        .progress-bar {
            background: var(--bg-tertiary);
            height: 6px;
            border-radius: 3px;
            margin-bottom: 20px;
            overflow: hidden;
        }

        .progress-fill {
            background: linear-gradient(135deg, var(--accent-1), var(--accent-2));
            height: 100%;
            border-radius: 3px;
            transition: width 0.3s;
            width: 0%;
        }

        .validation-message {
            background: var(--danger);
            color: white;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
        }

        .success-message {
            background: var(--success);
            color: white;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
        }

        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }
            
            .main-card {
                padding: 20px;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .options-grid {
                grid-template-columns: 1fr;
            }
            
            .btn-actions {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-edit"></i> Manual Exam Creation</h1>
            <p>Create custom MCQ questions for your assessment</p>
            <div class="position-badge" id="positionBadge">
                <i class="fas fa-briefcase"></i> Loading Position...
            </div>
        </div>

        <a href="CreateExam.php" class="back-btn">
            <i class="fas fa-arrow-left"></i>
            Back to Exam Type Selection
        </a>

        <div class="progress-bar">
            <div class="progress-fill" id="progressFill"></div>
        </div>

        <div class="validation-message" id="validationMessage"></div>
        <div class="success-message" id="successMessage"></div>

        <?php if (isset($errorMessage)): ?>
        <div class="validation-message" style="display: block;">
            <?php echo htmlspecialchars($errorMessage); ?>
        </div>
        <?php endif; ?>

        <form id="examForm" method="POST" action="">
            <input type="hidden" name="job_id" value="<?php echo htmlspecialchars($jobId); ?>">
            <input type="hidden" name="department" value="<?php echo htmlspecialchars($department); ?>">
            
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

        // Get position from URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        const position = urlParams.get('position');
        
        // Update position badge
        if (position) {
            document.getElementById('positionBadge').innerHTML = `
                <i class="fas fa-briefcase"></i> ${position.replace('-', ' ').replace(/\b\w/g, l => l.toUpperCase())}
            `;
        }

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

        // Initial progress update
        updateProgress();
    </script>
</body>
</html>

<?php
// take_exam.php - Exam taking interface
require_once 'session_manager.php';
require_once 'Database.php';

// Check if candidate is logged in
if (!isCandidateLoggedIn()) {
    header('Location: Login&Signup.php');
    exit;
}

// Get candidate ID from session
$sessionCandidateId = getCurrentCandidateId();

// Get exam ID from URL
$examId = $_GET['exam_id'] ?? null;
$assignmentId = $_GET['assignment_id'] ?? null;

if (!$examId || !$assignmentId) {
    header('Location: attendexam.php?error=invalid_exam');
    exit;
}

try {
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        throw new Exception('Database connection not available');
    }
    
    // Get exam details
    $examStmt = $pdo->prepare("
        SELECT 
            e.ExamID,
            e.ExamTitle,
            e.Description,
            e.Instructions,
            e.Duration,
            e.QuestionCount,
            e.PassingScore,
            jp.JobTitle,
            cli.CompanyName,
            ea.AssignmentID,
            ea.Status as AssignmentStatus,
            ea.DueDate
        FROM exams e
        JOIN exam_assignments ea ON e.ExamID = ea.ExamID
        JOIN job_postings jp ON ea.JobID = jp.JobID
        JOIN Company_login_info cli ON jp.CompanyID = cli.CompanyID
        WHERE e.ExamID = ? AND ea.AssignmentID = ? AND ea.CandidateID = ?
    ");
    
    $examStmt->execute([$examId, $assignmentId, $sessionCandidateId]);
    $exam = $examStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$exam) {
        header('Location: attendexam.php?error=exam_not_found');
        exit;
    }
    
    // Check if exam is already completed
    if ($exam['AssignmentStatus'] === 'completed') {
        header('Location: attendexam.php?error=exam_completed');
        exit;
    }
    
    // Check if exam is expired
    if ($exam['DueDate'] && strtotime($exam['DueDate']) < time()) {
        header('Location: attendexam.php?error=exam_expired');
        exit;
    }
    
    // Get exam questions
    $questionsStmt = $pdo->prepare("
        SELECT 
            q.QuestionID,
            q.QuestionText,
            q.QuestionOrder,
            q.Points,
            q.Difficulty,
            q.Category
        FROM exam_questions q
        WHERE q.ExamID = ?
        ORDER BY q.QuestionOrder
    ");
    
    $questionsStmt->execute([$examId]);
    $questions = $questionsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get options for each question
    $questionsWithOptions = [];
    foreach ($questions as $question) {
        $optionsStmt = $pdo->prepare("
            SELECT 
                OptionID,
                OptionText,
                OptionOrder
            FROM exam_question_options
            WHERE QuestionID = ?
            ORDER BY OptionOrder
        ");
        
        $optionsStmt->execute([$question['QuestionID']]);
        $options = $optionsStmt->fetchAll(PDO::FETCH_ASSOC);
        
        $questionsWithOptions[] = [
            'question' => $question,
            'options' => $options
        ];
    }
    
} catch (Exception $e) {
    error_log("Error loading exam: " . $e->getMessage());
    header('Location: attendexam.php?error=load_error');
    exit;
}


// Handle exam submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();
        
        // Get schedule ID (if exists)
        $scheduleStmt = $pdo->prepare("
            SELECT ScheduleID FROM exam_schedules 
            WHERE ExamID = ? AND CandidateID = ?
        ");
        $scheduleStmt->execute([$examId, $sessionCandidateId]);
        $schedule = $scheduleStmt->fetch(PDO::FETCH_ASSOC);
        
        $scheduleId = $schedule ? $schedule['ScheduleID'] : null;
        
        // Create exam attempt
        $attemptStmt = $pdo->prepare("
            INSERT INTO exam_attempts 
            (ScheduleID, CandidateID, ExamID, StartTime, Status, TotalQuestions) 
            VALUES (?, ?, ?, NOW(), 'completed', ?)
        ");
        
        $attemptStmt->execute([
            $scheduleId,
            $sessionCandidateId,
            $examId,
            count($questions)
        ]);
        
        $attemptId = $pdo->lastInsertId();
        
        // Process answers
        $correctAnswers = 0;
        $totalQuestions = count($questions);
        
        foreach ($questions as $question) {
            $questionId = $question['QuestionID'];
            $selectedOptionId = $_POST["question_{$questionId}"] ?? null;
            
            if ($selectedOptionId) {
                // Get correct answer
                $correctStmt = $pdo->prepare("
                    SELECT OptionID FROM exam_question_options 
                    WHERE QuestionID = ? AND IsCorrect = 1
                ");
                $correctStmt->execute([$questionId]);
                $correctOption = $correctStmt->fetch(PDO::FETCH_ASSOC);
                
                $isCorrect = ($selectedOptionId == $correctOption['OptionID']) ? 1 : 0;
                if ($isCorrect) $correctAnswers++;
                
                // Insert answer
                $answerStmt = $pdo->prepare("
                    INSERT INTO exam_answers 
                    (AttemptID, QuestionID, SelectedOptionID, IsCorrect, PointsEarned) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                
                $pointsEarned = $isCorrect ? $question['Points'] : 0;
                $answerStmt->execute([
                    $attemptId,
                    $questionId,
                    $selectedOptionId,
                    $isCorrect,
                    $pointsEarned
                ]);
            }
        }
        
        // Calculate score
        $score = ($correctAnswers / $totalQuestions) * 100;
        
        // Update attempt with results
        $updateAttemptStmt = $pdo->prepare("
            UPDATE exam_attempts 
            SET EndTime = NOW(), Score = ?, CorrectAnswers = ?, TimeSpent = TIMESTAMPDIFF(SECOND, StartTime, NOW())
            WHERE AttemptID = ?
        ");
        $updateAttemptStmt->execute([$score, $correctAnswers, $attemptId]);
        
        // Also update the assignment with the calculated time spent
        $timeSpentStmt = $pdo->prepare("
            SELECT TIMESTAMPDIFF(SECOND, StartTime, NOW()) as calculatedTimeSpent 
            FROM exam_attempts 
            WHERE AttemptID = ?
        ");
        $timeSpentStmt->execute([$attemptId]);
        $timeData = $timeSpentStmt->fetch(PDO::FETCH_ASSOC);
        $calculatedTimeSpent = $timeData['calculatedTimeSpent'] ?? 0;
        
        // Update assignment status with results
        require_once 'exam_assignment_handler.php';
        $updateSuccess = updateExamAssignmentResults(
            $assignmentId, 
            $score, 
            $correctAnswers, 
            $totalQuestions, 
            $calculatedTimeSpent
        );
        
        if (!$updateSuccess) {
            throw new Exception('Failed to update exam assignment results');
        }
        
        $pdo->commit();
        
        // Redirect to results page
        header("Location: exam_results.php?attempt_id={$attemptId}");
        exit;
        
    } catch (Exception $e) {
        if (isset($pdo)) {
            $pdo->rollBack();
        }
        error_log("Error submitting exam: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        $errorMessage = "Failed to submit exam: " . $e->getMessage() . ". Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Take Exam - CandiHire</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg-primary: #0d1117;
            --bg-secondary: #161b22;
            --bg-tertiary: #21262d;
            --text-primary: #c9d1d9;
            --text-secondary: #8b949e;
            --accent: #58a6ff;
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
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }

        .exam-header {
            background: var(--bg-secondary);
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 30px;
            border: 1px solid var(--border);
            text-align: center;
        }

        .exam-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 10px;
            color: var(--accent);
        }

        .exam-meta {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .meta-item {
            background: var(--bg-tertiary);
            padding: 15px;
            border-radius: 8px;
            border: 1px solid var(--border);
        }

        .meta-label {
            font-size: 0.9rem;
            color: var(--text-secondary);
            margin-bottom: 5px;
        }

        .meta-value {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        .timer {
            position: fixed;
            top: 20px;
            right: 20px;
            background: var(--danger);
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            font-size: 1.2rem;
            font-weight: 700;
            z-index: 1000;
            box-shadow: 0 4px 12px rgba(248, 81, 73, 0.3);
        }

        .exam-form {
            background: var(--bg-secondary);
            border-radius: 16px;
            padding: 30px;
            border: 1px solid var(--border);
        }

        .question-container {
            margin-bottom: 40px;
            padding: 25px;
            background: var(--bg-tertiary);
            border-radius: 12px;
            border: 1px solid var(--border);
        }

        .question-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .question-number {
            background: var(--accent);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .question-difficulty {
            background: var(--bg-secondary);
            color: var(--text-secondary);
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 0.8rem;
            text-transform: uppercase;
            border: 1px solid var(--border);
        }

        .question-text {
            font-size: 1.1rem;
            line-height: 1.6;
            margin-bottom: 20px;
            color: var(--text-primary);
        }

        .options-container {
            display: grid;
            gap: 12px;
        }

        .option-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 15px;
            background: var(--bg-secondary);
            border: 2px solid var(--border);
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .option-item:hover {
            border-color: var(--accent);
            background: var(--bg-primary);
        }

        .option-item.selected {
            border-color: var(--accent);
            background: rgba(88, 166, 255, 0.1);
        }

        .option-radio {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }

        .option-text {
            flex: 1;
            font-size: 1rem;
            color: var(--text-primary);
        }

        .submit-section {
            background: var(--bg-tertiary);
            padding: 25px;
            border-radius: 12px;
            border: 1px solid var(--border);
            text-align: center;
            margin-top: 30px;
        }

        .submit-btn {
            background: var(--success);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .submit-btn:hover {
            background: #2da04e;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(63, 185, 80, 0.3);
        }

        .submit-btn:disabled {
            background: var(--text-secondary);
            cursor: not-allowed;
            transform: none;
        }

        .warning-message {
            background: var(--warning);
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 600;
        }

        .error-message {
            background: var(--danger);
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }


        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }
            
            .exam-meta {
                grid-template-columns: 1fr;
            }
            
            .timer {
                position: relative;
                top: auto;
                right: auto;
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if (isset($errorMessage)): ?>
        <div class="error-message">
            <?php echo htmlspecialchars($errorMessage); ?>
        </div>
        <?php endif; ?>

        <div class="exam-header">
            <h1 class="exam-title"><?php echo htmlspecialchars($exam['ExamTitle']); ?></h1>
            <p style="color: var(--text-secondary); margin-bottom: 20px;">
                <?php echo htmlspecialchars($exam['Description']); ?>
            </p>
            
            <div class="exam-meta">
                <div class="meta-item">
                    <div class="meta-label">Company</div>
                    <div class="meta-value"><?php echo htmlspecialchars($exam['CompanyName']); ?></div>
                </div>
                <div class="meta-item">
                    <div class="meta-label">Position</div>
                    <div class="meta-value"><?php echo htmlspecialchars($exam['JobTitle']); ?></div>
                </div>
                <div class="meta-item">
                    <div class="meta-label">Duration</div>
                    <div class="meta-value"><?php echo gmdate('H:i', $exam['Duration']); ?> minutes</div>
                </div>
                <div class="meta-item">
                    <div class="meta-label">Questions</div>
                    <div class="meta-value"><?php echo count($questionsWithOptions); ?></div>
                </div>
                <div class="meta-item">
                    <div class="meta-label">Passing Score</div>
                    <div class="meta-value"><?php echo $exam['PassingScore']; ?>%</div>
                </div>
                <div class="meta-item">
                    <div class="meta-label">Due Date</div>
                    <div class="meta-value"><?php echo date('M j, Y', strtotime($exam['DueDate'])); ?></div>
                </div>
            </div>
        </div>

        <div class="warning-message">
            <i class="fas fa-exclamation-triangle"></i>
            Please ensure you have a stable internet connection. Do not close this window or navigate away during the exam.
        </div>


        <div class="timer" id="timer">
            <i class="fas fa-clock"></i>
            <span id="timeRemaining"><?php echo gmdate('H:i:s', $exam['Duration']); ?></span>
        </div>

        <form id="examForm" method="POST" action="">
            <div class="exam-form">
                <?php foreach ($questionsWithOptions as $index => $questionData): ?>
                <div class="question-container">
                    <div class="question-header">
                        <span class="question-number">Question <?php echo $index + 1; ?></span>
                        <span class="question-difficulty"><?php echo ucfirst($questionData['question']['Difficulty']); ?></span>
                    </div>
                    
                    <div class="question-text">
                        <?php 
                        $questionText = $questionData['question']['QuestionText'];
                        // Remove any "(Question X)" text at the end
                        $questionText = preg_replace('/\s*\(Question\s+\d+\)\s*$/', '', $questionText);
                        echo htmlspecialchars($questionText); 
                        ?>
                    </div>
                    
                    <div class="options-container">
                        <?php foreach ($questionData['options'] as $option): ?>
                        <label class="option-item">
                            <input type="radio" 
                                   name="question_<?php echo $questionData['question']['QuestionID']; ?>" 
                                   value="<?php echo $option['OptionID']; ?>" 
                                   class="option-radio"
                                   required>
                            <span class="option-text"><?php echo htmlspecialchars($option['OptionText']); ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <div class="submit-section">
                    <p style="margin-bottom: 20px; color: var(--text-secondary);">
                        Please review your answers before submitting. Once submitted, you cannot change your answers.
                    </p>
                    <button type="submit" class="submit-btn" id="submitBtn">
                        <i class="fas fa-check-circle"></i>
                        Submit Exam
                    </button>
                </div>
            </div>
        </form>
    </div>

    <script>
        // Timer functionality
        let timeRemaining = <?php echo $exam['Duration']; ?>; // in seconds
        const timerElement = document.getElementById('timeRemaining');
        const submitBtn = document.getElementById('submitBtn');
        
        function updateTimer() {
            const hours = Math.floor(timeRemaining / 3600);
            const minutes = Math.floor((timeRemaining % 3600) / 60);
            const seconds = timeRemaining % 60;
            
            timerElement.textContent = 
                String(hours).padStart(2, '0') + ':' +
                String(minutes).padStart(2, '0') + ':' +
                String(seconds).padStart(2, '0');
            
            if (timeRemaining <= 0) {
                // Auto-submit when time runs out
                submitBtn.click();
                return;
            }
            
            timeRemaining--;
        }
        
        // Update timer every second
        const timerInterval = setInterval(updateTimer, 1000);
        
        // Handle option selection
        document.querySelectorAll('.option-item').forEach(item => {
            item.addEventListener('click', function() {
                const radio = this.querySelector('input[type="radio"]');
                radio.checked = true;
                
                // Update visual state
                this.parentElement.querySelectorAll('.option-item').forEach(opt => {
                    opt.classList.remove('selected');
                });
                this.classList.add('selected');
            });
        });
        
        // Form submission confirmation
        document.getElementById('examForm').addEventListener('submit', function(e) {
            // Count unanswered questions by checking each question group
            const questionContainers = document.querySelectorAll('.question-container');
            let unansweredQuestions = 0;
            
            questionContainers.forEach(container => {
                const radioButtons = container.querySelectorAll('input[type="radio"]');
                const hasAnswer = Array.from(radioButtons).some(radio => radio.checked);
                if (!hasAnswer) {
                    unansweredQuestions++;
                }
            });
            
            if (unansweredQuestions > 0) {
                if (!confirm(`You have ${unansweredQuestions} unanswered questions. Are you sure you want to submit?`)) {
                    e.preventDefault();
                    return;
                }
            }
            
            // Disable submit button to prevent double submission
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
            
            // Clear timer
            clearInterval(timerInterval);
        });
        
        // Prevent page refresh/close during exam
        window.addEventListener('beforeunload', function(e) {
            e.preventDefault();
            e.returnValue = 'Are you sure you want to leave? Your progress will be lost.';
        });
        
        // Start timer
        updateTimer();
    </script>
</body>
</html>

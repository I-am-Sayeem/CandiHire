<?php
// exam_results.php - Display exam results to candidates
require_once 'session_manager.php';
require_once 'Database.php';

// Check if candidate is logged in
if (!isCandidateLoggedIn()) {
    header('Location: Login&Signup.php');
    exit;
}

// Get candidate ID from session
$sessionCandidateId = getCurrentCandidateId();

// Get attempt ID from URL
$attemptId = $_GET['attempt_id'] ?? null;

if (!$attemptId) {
    header('Location: attendexam.php?error=invalid_attempt');
    exit;
}

try {
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        throw new Exception('Database connection not available');
    }
    
    // Get exam attempt details
    $stmt = $pdo->prepare("
        SELECT 
            ea.AttemptID,
            ea.ExamID,
            ea.CandidateID,
            ea.StartTime,
            ea.EndTime,
            ea.Score,
            ea.CorrectAnswers,
            ea.TotalQuestions,
            ea.TimeSpent,
            e.ExamTitle,
            e.PassingScore,
            jp.JobTitle,
            cli.CompanyName
        FROM exam_attempts ea
        JOIN exams e ON ea.ExamID = e.ExamID
        JOIN exam_assignments ea2 ON ea.ExamID = ea2.ExamID AND ea.CandidateID = ea2.CandidateID
        JOIN job_postings jp ON ea2.JobID = jp.JobID
        JOIN Company_login_info cli ON jp.CompanyID = cli.CompanyID
        WHERE ea.AttemptID = ? AND ea.CandidateID = ?
    ");
    
    $stmt->execute([$attemptId, $sessionCandidateId]);
    $attempt = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$attempt) {
        header('Location: attendexam.php?error=attempt_not_found');
        exit;
    }
    
    // Get detailed answers
    $answersStmt = $pdo->prepare("
        SELECT 
            q.QuestionText,
            qo.OptionText as SelectedOption,
            qo2.OptionText as CorrectOption,
            ea.IsCorrect,
            ea.PointsEarned,
            q.Points as TotalPoints
        FROM exam_answers ea
        JOIN exam_questions q ON ea.QuestionID = q.QuestionID
        LEFT JOIN exam_question_options qo ON ea.SelectedOptionID = qo.OptionID
        LEFT JOIN exam_question_options qo2 ON q.QuestionID = qo2.QuestionID AND qo2.IsCorrect = 1
        WHERE ea.AttemptID = ?
        ORDER BY q.QuestionOrder
    ");
    
    $answersStmt->execute([$attemptId]);
    $answers = $answersStmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    error_log("Error loading exam results: " . $e->getMessage());
    header('Location: attendexam.php?error=load_error');
    exit;
}

// Function to get score color
function getScoreColor($score, $passingScore) {
    $scoreValue = floatval($score);
    $passingValue = floatval($passingScore);
    return $scoreValue >= $passingValue ? '#3fb950' : '#f85149';
}

// Function to get status text
function getStatusText($score, $passingScore) {
    $scoreValue = floatval($score);
    $passingValue = floatval($passingScore);
    return $scoreValue >= $passingValue ? 'PASSED' : 'FAILED';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Results - CandiHire</title>
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

        /* Light Theme */
        [data-theme="light"] {
            --bg-primary: #ffffff;
            --bg-secondary: #f6f8fa;
            --bg-tertiary: #eaeef2;
            --text-primary: #24292f;
            --text-secondary: #656d76;
            --accent: #0969da;
            --accent-hover: #0860ca;
            --border: #d1d9e0;
            --success: #1a7f37;
            --danger: #d1242f;
            --warning: #9a6700;
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
            padding: 20px;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
        }

        .results-header {
            background: var(--bg-secondary);
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 30px;
            border: 1px solid var(--border);
            text-align: center;
        }

        .results-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            color: var(--accent);
        }

        .exam-info {
            font-size: 1.2rem;
            color: var(--text-secondary);
            margin-bottom: 20px;
        }

        .score-display {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 30px;
            margin-bottom: 20px;
        }

        .score-circle {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            position: relative;
        }

        .score-percentage {
            font-size: 2rem;
        }

        .score-label {
            font-size: 0.9rem;
            opacity: 0.8;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 12px 24px;
            border-radius: 25px;
            font-size: 1.1rem;
            font-weight: 600;
            gap: 8px;
        }

        .status-badge.passed {
            background-color: var(--success);
            color: white;
        }

        .status-badge.failed {
            background-color: var(--danger);
            color: white;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: var(--bg-tertiary);
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            border: 1px solid var(--border);
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--accent);
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 0.9rem;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .answers-section {
            background: var(--bg-secondary);
            border-radius: 16px;
            padding: 30px;
            border: 1px solid var(--border);
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 20px;
            color: var(--text-primary);
        }

        .answer-item {
            background: var(--bg-tertiary);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid var(--border);
        }

        .answer-item:last-child {
            margin-bottom: 0;
        }

        .question-text {
            font-size: 1.1rem;
            font-weight: 500;
            margin-bottom: 15px;
            color: var(--text-primary);
        }

        .answer-options {
            display: grid;
            gap: 10px;
        }

        .option-item {
            padding: 12px 16px;
            border-radius: 8px;
            border: 2px solid var(--border);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .option-item.correct {
            border-color: var(--success);
            background-color: rgba(63, 185, 80, 0.1);
        }

        .option-item.incorrect {
            border-color: var(--danger);
            background-color: rgba(248, 81, 73, 0.1);
        }

        .option-item.selected {
            border-color: var(--accent);
            background-color: rgba(88, 166, 255, 0.1);
        }

        .option-icon {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 600;
        }

        .option-icon.correct {
            background-color: var(--success);
            color: white;
        }

        .option-icon.incorrect {
            background-color: var(--danger);
            color: white;
        }

        .option-icon.selected {
            background-color: var(--accent);
            color: white;
        }

        .actions {
            text-align: center;
            margin-top: 30px;
        }

        .btn {
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin: 0 10px;
        }

        .btn-primary {
            background-color: var(--accent);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--accent-hover);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(88, 166, 255, 0.3);
        }

        .btn-secondary {
            background-color: var(--bg-tertiary);
            color: var(--text-primary);
            border: 1px solid var(--border);
        }

        .btn-secondary:hover {
            background-color: var(--bg-primary);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        @media (max-width: 768px) {
            .container {
                padding: 0 10px;
            }
            
            .score-display {
                flex-direction: column;
                gap: 20px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .actions {
                display: flex;
                flex-direction: column;
                gap: 15px;
            }
            
            .btn {
                margin: 0;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="results-header">
            <h1 class="results-title">Exam Results</h1>
            <div class="exam-info">
                <?php echo htmlspecialchars($attempt['ExamTitle']); ?> - <?php echo htmlspecialchars($attempt['CompanyName']); ?>
            </div>
            
            <div class="score-display">
                <div class="score-circle" style="background-color: <?php echo getScoreColor($attempt['Score'], $attempt['PassingScore']); ?>; color: white;">
                    <div class="score-percentage"><?php echo number_format($attempt['Score'], 1); ?>%</div>
                    <div class="score-label">Your Score</div>
                </div>
                
                <div class="status-badge <?php echo getStatusText($attempt['Score'], $attempt['PassingScore']) === 'PASSED' ? 'passed' : 'failed'; ?>">
                    <i class="fas fa-<?php echo getStatusText($attempt['Score'], $attempt['PassingScore']) === 'PASSED' ? 'check-circle' : 'times-circle'; ?>"></i>
                    <?php echo getStatusText($attempt['Score'], $attempt['PassingScore']); ?>
                </div>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo $attempt['CorrectAnswers']; ?></div>
                <div class="stat-label">Correct Answers</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $attempt['TotalQuestions']; ?></div>
                <div class="stat-label">Total Questions</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $attempt['PassingScore']; ?>%</div>
                <div class="stat-label">Passing Score</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php 
                    $timeSpent = $attempt['TimeSpent'] ?? 0;
                    
                    // If TimeSpent is stored as seconds (numeric value)
                    if ($timeSpent > 0 && is_numeric($timeSpent)) {
                        echo gmdate('H:i:s', $timeSpent);
                    } else {
                        // Calculate time spent from StartTime and EndTime
                        if ($attempt['StartTime'] && $attempt['EndTime']) {
                            $startTime = new DateTime($attempt['StartTime']);
                            $endTime = new DateTime($attempt['EndTime']);
                            $timeDiff = $endTime->diff($startTime);
                            
                            // Format the time difference
                            $totalSeconds = ($timeDiff->h * 3600) + ($timeDiff->i * 60) + $timeDiff->s;
                            echo gmdate('H:i:s', $totalSeconds);
                        } else {
                            echo 'N/A';
                        }
                    }
                ?></div>
                <div class="stat-label">Time Spent</div>
            </div>
        </div>

        <div class="answers-section">
            <h2 class="section-title">Answer Review</h2>
            
            <?php foreach ($answers as $index => $answer): ?>
                <div class="answer-item">
                    <div class="question-text">
                        <strong>Question <?php echo $index + 1; ?>:</strong> <?php echo htmlspecialchars($answer['QuestionText']); ?>
                    </div>
                    
                    <div class="answer-options">
                        <div class="option-item <?php echo $answer['IsCorrect'] ? 'correct' : 'incorrect'; ?>">
                            <div class="option-icon <?php echo $answer['IsCorrect'] ? 'correct' : 'incorrect'; ?>">
                                <i class="fas fa-<?php echo $answer['IsCorrect'] ? 'check' : 'times'; ?>"></i>
                            </div>
                            <div>
                                <strong>Your Answer:</strong> <?php echo htmlspecialchars($answer['SelectedOption']); ?>
                                <?php if (!$answer['IsCorrect']): ?>
                                    <br><strong>Correct Answer:</strong> <?php echo htmlspecialchars($answer['CorrectOption']); ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="actions">
            <a href="attendexam.php" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i>
                Back to Exams
            </a>
            <a href="CandidateDashboard.php" class="btn btn-secondary">
                <i class="fas fa-home"></i>
                Dashboard
            </a>
        </div>
    </div>

    <script>
        // Add some animation effects
        document.addEventListener('DOMContentLoaded', function() {
            // Animate score circle
            const scoreCircle = document.querySelector('.score-circle');
            if (scoreCircle) {
                scoreCircle.style.transform = 'scale(0)';
                scoreCircle.style.transition = 'transform 0.5s ease-out';
                setTimeout(() => {
                    scoreCircle.style.transform = 'scale(1)';
                }, 300);
            }
            
            // Animate stat cards
            const statCards = document.querySelectorAll('.stat-card');
            statCards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                setTimeout(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, 500 + (index * 100));
            });
        });
    </script>
</body>
</html>
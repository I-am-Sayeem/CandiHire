<?php
// AutoExamCreation.php - Auto Exam Creation with Database Integration
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
                VALUES (?, ?, 'auto-generated', ?, ?, ?, ?, ?, 1, ?)
            ");
            
            $examStmt->execute([
                $sessionCompanyId,
                $_POST['examTitle'],
                'Auto-generated exam for ' . $_POST['department'],
                'Please read each question carefully and select the best answer. This exam was automatically generated based on industry standards.',
                $_POST['examDuration'] * 60, // Convert to seconds
                $_POST['questionCount'],
                $_POST['passingScore'],
                $companyName
            ]);
            
            $examId = $pdo->lastInsertId();
            
            // Get questions from question bank based on department
            $department = $_POST['department'];
            $questionCount = $_POST['questionCount'];
            
            $questionBankStmt = $pdo->prepare("
                SELECT QuestionBankID as QuestionID, QuestionText, Difficulty, Department as Category, 
                       OptionA, OptionB, OptionC, OptionD, CorrectOption
                FROM question_banks
                WHERE Department = ?
                ORDER BY RAND()
                LIMIT ?
            ");
            
            $questionBankStmt->execute([$department, $questionCount]);
            $questions = $questionBankStmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($questions) < $questionCount) {
                // Try to find questions from related departments
                $relatedDepartments = [];
                
                // Map specific positions to broader departments
                $departmentMapping = [
                    'Software Engineering' => ['Software Engineering', 'Data Science', 'DevOps'],
                    'Data Science' => ['Data Science', 'Software Engineering'],
                    'Product Management' => ['Product Management', 'Business'],
                    'Design' => ['Design', 'Product Management'],
                    'DevOps' => ['DevOps', 'Software Engineering'],
                    'Quality Assurance' => ['Quality Assurance', 'Software Engineering'],
                    'Business' => ['Business', 'Product Management'],
                    'Marketing' => ['Marketing', 'Business'],
                    'Human Resources' => ['Human Resources', 'Business'],
                    'Sales' => ['Sales', 'Marketing'],
                    'Finance' => ['Finance', 'Business']
                ];
                
                if (isset($departmentMapping[$department])) {
                    $relatedDepartments = $departmentMapping[$department];
                } else {
                    // If no specific mapping, try to find questions from any department
                    $relatedDepartments = ['Software Engineering', 'Data Science', 'Product Management', 'Design', 'DevOps', 'Quality Assurance', 'Business', 'Marketing', 'Human Resources', 'Sales', 'Finance'];
                }
                
                // Try to get questions from related departments
                $remainingQuestions = $questionCount - count($questions);
                $questionsPerDept = ceil($remainingQuestions / count($relatedDepartments));
                
                foreach ($relatedDepartments as $relatedDept) {
                    if (count($questions) >= $questionCount) break;
                    
                    $relatedStmt = $pdo->prepare("
                        SELECT QuestionBankID as QuestionID, QuestionText, Difficulty, Department as Category, 
                               OptionA, OptionB, OptionC, OptionD, CorrectOption
                        FROM question_banks
                        WHERE Department = ?
                        ORDER BY RAND()
                        LIMIT ?
                    ");
                    $relatedStmt->execute([$relatedDept, $questionsPerDept]);
                    $relatedQuestions = $relatedStmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    $questions = array_merge($questions, $relatedQuestions);
                }
            }

            if (count($questions) < $questionCount) {
                throw new Exception("Not enough questions available for this department. Please add more questions to the question bank. Available: " . count($questions) . ", Required: " . $questionCount);
            }
            
            // Insert questions into exam
            $questionStmt = $pdo->prepare("
                INSERT INTO exam_questions (ExamID, QuestionType, QuestionText, QuestionOrder, Points, Difficulty, Category) 
                VALUES (?, 'multiple-choice', ?, ?, 1.00, ?, ?)
            ");
            
            $optionStmt = $pdo->prepare("
                INSERT INTO exam_question_options (QuestionID, OptionText, IsCorrect, OptionOrder) 
                VALUES (?, ?, ?, ?)
            ");
            
            foreach ($questions as $index => $question) {
                // Insert question
                $questionStmt->execute([
                    $examId,
                    $question['QuestionText'],
                    $index + 1,
                    $question['Difficulty'],
                    $question['Category']
                ]);
                
                $examQuestionId = $pdo->lastInsertId();
                
                // Get options for this question from the question_banks table
                $options = [
                    ['OptionText' => $question['OptionA'], 'IsCorrect' => ($question['CorrectOption'] === 'A' ? 1 : 0), 'OptionOrder' => 1],
                    ['OptionText' => $question['OptionB'], 'IsCorrect' => ($question['CorrectOption'] === 'B' ? 1 : 0), 'OptionOrder' => 2],
                    ['OptionText' => $question['OptionC'], 'IsCorrect' => ($question['CorrectOption'] === 'C' ? 1 : 0), 'OptionOrder' => 3],
                    ['OptionText' => $question['OptionD'], 'IsCorrect' => ($question['CorrectOption'] === 'D' ? 1 : 0), 'OptionOrder' => 4]
                ];
                
                // Insert options
                foreach ($options as $option) {
                    $optionStmt->execute([
                        $examQuestionId,
                        $option['OptionText'],
                        $option['IsCorrect'],
                        $option['OptionOrder']
                    ]);
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
            header('Location: CompanyDashboard.php?exam_created=1&type=auto');
            exit;
            
        } else {
            throw new Exception('Database connection not available');
        }
        
    } catch (Exception $e) {
        if (isset($pdo)) {
            $pdo->rollBack();
        }
        error_log("Error creating auto exam: " . $e->getMessage());
        $errorMessage = "Failed to create exam: " . $e->getMessage();
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

// Map position to department
$positionToDepartment = [
    'software-engineer' => 'Software Engineering',
    'frontend-developer' => 'Software Engineering',
    'backend-developer' => 'Software Engineering',
    'fullstack-developer' => 'Software Engineering',
    'data-scientist' => 'Data Science',
    'data-analyst' => 'Data Science',
    'machine-learning-engineer' => 'Data Science',
    'product-manager' => 'Product Management',
    'product-owner' => 'Product Management',
    'ui-ux-designer' => 'Design',
    'graphic-designer' => 'Design',
    'devops-engineer' => 'DevOps',
    'site-reliability-engineer' => 'DevOps',
    'qa-engineer' => 'Quality Assurance',
    'test-automation-engineer' => 'Quality Assurance',
    'business-analyst' => 'Business',
    'project-manager' => 'Business',
    'digital-marketer' => 'Marketing',
    'content-marketer' => 'Marketing',
    'hr-specialist' => 'Human Resources',
    'recruiter' => 'Human Resources',
    'sales-representative' => 'Sales',
    'accountant' => 'Finance',
    'financial-analyst' => 'Finance'
];

$department = $positionToDepartment[$position] ?? 'Software Engineering';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auto Exam Creation - CandiHire</title>
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
            max-width: 800px;
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
            grid-template-columns: 1fr 1fr;
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

        .form-input, .form-textarea, .form-select {
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

        .form-input:focus, .form-textarea:focus, .form-select:focus {
            outline: none;
            border-color: var(--accent-1);
            box-shadow: 0 0 0 3px rgba(88, 166, 255, 0.1);
        }

        .form-textarea {
            min-height: 100px;
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

        .info-card {
            background: var(--bg-tertiary);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .info-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--accent-1);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .info-text {
            color: var(--text-secondary);
            line-height: 1.5;
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
            <h1><i class="fas fa-magic"></i> Auto Exam Creation</h1>
            <p>Generate exam questions automatically based on industry standards</p>
            <div class="position-badge">
                <i class="fas fa-briefcase"></i> <?php echo htmlspecialchars($positionDisplay); ?>
            </div>
        </div>

        <a href="CreateExam.php" class="back-btn">
            <i class="fas fa-arrow-left"></i>
            Back to Exam Type Selection
        </a>

        <div class="validation-message" id="validationMessage"></div>
        <div class="success-message" id="successMessage"></div>

        <?php if (isset($errorMessage)): ?>
        <div class="validation-message" style="display: block;">
            <?php echo htmlspecialchars($errorMessage); ?>
        </div>
        <?php endif; ?>

        <div class="info-card">
            <div class="info-title">
                <i class="fas fa-info-circle"></i>
                How Auto Generation Works
            </div>
            <div class="info-text">
                Our AI system will automatically select relevant questions from our comprehensive question bank for the <strong><?php echo htmlspecialchars($department); ?></strong> department. 
                Questions are randomly selected to ensure variety and cover different difficulty levels and topics within your field.
            </div>
        </div>

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
                        <label for="examTitle" class="form-label">
                            <i class="fas fa-heading"></i> Exam Title
                        </label>
                        <input type="text" id="examTitle" name="examTitle" class="form-input" 
                               value="<?php echo htmlspecialchars($positionDisplay); ?> Assessment" required>
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
<?php
// CreateExam.php - Create MCQ Exam for Companies
require_once 'session_manager.php';

// Check if company is logged in
if (!isCompanyLoggedIn()) {
    header('Location: Login&Signup.php');
    exit;
}

// Get company ID from session
$sessionCompanyId = getCurrentCompanyId();
$companyName = $_SESSION['company_name'] ?? 'Company';

// Get jobId from URL parameter if available (from job posting notification)
$selectedJobId = isset($_GET['jobId']) ? intval($_GET['jobId']) : null;

// Load company data and job posts from database
$companyLogo = null;
$jobPosts = [];

require_once 'Database.php';
try {
    if (isset($pdo) && $pdo instanceof PDO) {
        // Load company data
        $stmt = $pdo->prepare("SELECT CompanyName, Logo FROM Company_login_info WHERE CompanyID = ?");
        $stmt->execute([$sessionCompanyId]);
        $company = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($company) {
            $companyName = $company['CompanyName'];
            $companyLogo = $company['Logo'];
            $_SESSION['company_name'] = $companyName;
        }
        
        // Load company's job posts
        $stmt = $pdo->prepare("
            SELECT JobID, JobTitle, Department, Location, JobType, SalaryMin, SalaryMax, Currency, Status, CreatedAt 
            FROM job_postings 
            WHERE CompanyID = ? AND Status = 'active'
            ORDER BY CreatedAt DESC
        ");
        $stmt->execute([$sessionCompanyId]);
        $jobPosts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    error_log("Error loading company data: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create MCQ Exam - CandiHire</title>
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
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
            padding: 20px 0;
        }

        .header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            background: linear-gradient(135deg, var(--accent-1), var(--accent-2));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .header p {
            font-size: 1.1rem;
            color: var(--text-secondary);
        }

        .main-card {
            background: var(--bg-secondary);
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            border: 1px solid var(--border);
            margin-bottom: 30px;
        }

        .step-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border);
        }

        .step-number {
            background: linear-gradient(135deg, var(--accent-1), var(--accent-2));
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.1rem;
        }

        .step-title {
            font-size: 1.4rem;
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-primary);
        }

        .form-select, .form-input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid var(--border);
            border-radius: 8px;
            background: var(--bg-tertiary);
            color: var(--text-primary);
            font-size: 1rem;
            transition: all 0.2s;
        }

        .form-select:focus, .form-input:focus {
            outline: none;
            border-color: var(--accent-1);
            box-shadow: 0 0 0 3px rgba(88, 166, 255, 0.1);
        }

        .choice-section {
            margin-top: 30px;
            padding: 20px;
            background: var(--bg-tertiary);
            border-radius: 12px;
            border: 1px solid var(--border);
        }

        .choice-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 20px;
            text-align: center;
        }

        .choice-options {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .choice-card {
            background: var(--bg-secondary);
            border: 2px solid var(--border);
            border-radius: 12px;
            padding: 25px 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            will-change: transform, box-shadow, border-color;
        }

        .choice-card:hover {
            border-color: var(--accent-1);
            transform: translateY(-4px);
            box-shadow: 0 12px 30px rgba(88, 166, 255, 0.2);
        }

        .choice-card.selected {
            border-color: var(--accent-1);
            background: rgba(88, 166, 255, 0.1);
        }

        .choice-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
            color: var(--accent-1);
        }

        .choice-label {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .choice-description {
            font-size: 0.9rem;
            color: var(--text-secondary);
            line-height: 1.4;
        }

        .selection-message {
            background: var(--bg-primary);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
            display: none;
        }

        .selection-message.show {
            display: block;
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .message-content {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
        }

        .message-icon {
            color: var(--success);
            font-size: 1.2rem;
        }

        .proceed-btn {
            background: linear-gradient(135deg, var(--accent-1), var(--accent-2));
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            will-change: transform, box-shadow;
        }

        .proceed-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(88, 166, 255, 0.4);
        }

        .back-btn {
            background: transparent;
            color: var(--text-secondary);
            border: 1px solid var(--border);
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
            will-change: transform, background-color, color;
        }

        .back-btn:hover {
            background: var(--bg-tertiary);
            color: var(--text-primary);
            transform: translateX(-2px);
        }

        .no-posts-message {
            background: var(--bg-primary);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
        }

        .job-details {
            margin-top: 20px;
            animation: slideIn 0.3s ease-out;
        }

        .job-info-card {
            background: var(--bg-tertiary);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 20px;
        }

        .job-info-card h4 {
            color: var(--accent-1);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .job-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .info-label {
            font-size: 0.9rem;
            color: var(--text-secondary);
            font-weight: 500;
        }

        .info-value {
            font-size: 1rem;
            color: var(--text-primary);
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }
            
            .main-card {
                padding: 25px;
            }
            
            .choice-options {
                grid-template-columns: 1fr;
            }
            
            .header h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-clipboard-question"></i> Create MCQ Exam</h1>
            <p>Design comprehensive assessments for your job positions</p>
        </div>

        <a href="CompanyDashboard.php" class="back-btn">
            <i class="fas fa-arrow-left"></i>
            Back to Dashboard
        </a>

        <div class="main-card">
            <div class="step-header">
                <div class="step-number">1</div>
                <div class="step-title">Select Job Post</div>
            </div>

            <?php if (empty($jobPosts)): ?>
                <div class="no-posts-message">
                    <div class="message-content">
                        <i class="fas fa-exclamation-triangle message-icon" style="color: var(--warning);"></i>
                        <div>
                            <strong>No Active Job Posts Found!</strong><br>
                            You need to create job posts first before creating exams. Go to your dashboard to post jobs.
                        </div>
                    </div>
                    <a href="CompanyDashboard.php" class="proceed-btn">
                        <i class="fas fa-plus"></i> Create Job Posts
                    </a>
                </div>
            <?php else: ?>
                <div class="form-group">
                    <label for="position" class="form-label">
                        <i class="fas fa-briefcase"></i> Choose the job post for this exam
                    </label>
                    <select id="position" class="form-select" required>
                        <option value="">Select a job post...</option>
                        <?php foreach ($jobPosts as $job): ?>
                            <?php 
                            $salaryRange = $job['SalaryMin'] && $job['SalaryMax'] ? 
                                $job['Currency'] . ' ' . number_format($job['SalaryMin']) . ' - ' . number_format($job['SalaryMax']) : 
                                'Salary not specified';
                            $isSelected = ($selectedJobId && $selectedJobId == $job['JobID']) ? 'selected' : '';
                            ?>
                            <option value="<?php echo htmlspecialchars($job['JobID']); ?>" 
                                    data-department="<?php echo htmlspecialchars($job['Department']); ?>"
                                    data-location="<?php echo htmlspecialchars($job['Location']); ?>"
                                    data-type="<?php echo htmlspecialchars($job['JobType']); ?>"
                                    data-salary="<?php echo htmlspecialchars($salaryRange); ?>"
                                    <?php echo $isSelected; ?>>
                                <?php echo htmlspecialchars($job['JobTitle']); ?> - <?php echo htmlspecialchars($job['Department']); ?>
                            </option>
                        <?php endforeach; ?>
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
            <?php endif; ?>

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
                    <a href="ManualExamCreation.php" class="proceed-btn" id="manualProceedBtn">
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
                    <a href="AutoExamCreation.php" class="proceed-btn" id="autoProceedBtn">
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
                    manualProceedBtn.href = `ManualExamCreation.php?job_id=${selectedPosition}&department=${encodeURIComponent(department)}`;
                } else {
                    autoMessage.classList.add('show');
                    // Update the link with job ID and department
                    const selectedOption = positionSelect.options[positionSelect.selectedIndex];
                    const department = selectedOption.dataset.department;
                    autoProceedBtn.href = `AutoExamCreation.php?job_id=${selectedPosition}&department=${encodeURIComponent(department)}`;
                }
            });
        });

        // Add some interactive effects
        choiceCards.forEach(card => {
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

        // Auto-select job if jobId is provided in URL
        document.addEventListener('DOMContentLoaded', function() {
            const selectedJobId = <?php echo json_encode($selectedJobId); ?>;
            if (selectedJobId && positionSelect.value) {
                // Trigger change event to show job details and choice section
                positionSelect.dispatchEvent(new Event('change'));
            }
        });
    </script>
</body>
</html>

<?php
// Attend Exam functionality
require_once 'session_manager.php';

// Check if candidate is logged in
if (!isCandidateLoggedIn()) {
    header('Location: Login&Signup.php');
    exit;
}

// Get candidate ID and name from session
$sessionCandidateId = getCurrentCandidateId();
$candidateName = $_SESSION['candidate_name'] ?? 'User';
$candidateProfilePicture = null;

// Load candidate profile picture if available
require_once 'Database.php';
try {
    if (isset($pdo) && $pdo instanceof PDO) {
        $stmt = $pdo->prepare("SELECT ProfilePicture FROM candidate_login_info WHERE CandidateID = ?");
        $stmt->execute([$sessionCandidateId]);
        $candidate = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($candidate && $candidate['ProfilePicture']) {
            $candidateProfilePicture = $candidate['ProfilePicture'];
        }
    } else {
        error_log("Database connection not available for profile picture loading");
    }
} catch (Exception $e) {
    error_log("Error loading candidate profile picture: " . $e->getMessage());
}

// Load exam data from database
require_once 'exam_assignment_handler.php';

$scheduledExams = [];
$completedExams = [];

try {
    // Check database connection first
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        throw new Exception("Database connection not available");
    }
    
    // Get assigned exams (scheduled)
    $assignedExams = getAssignedExams($sessionCandidateId);
    
    // Debug: Log the raw assigned exams data
    error_log("Raw assigned exams data: " . print_r($assignedExams, true));
    error_log("Number of assigned exams: " . count($assignedExams));
    
    foreach ($assignedExams as $exam) {
        $examData = [
            'id' => $exam['ExamID'],
            'examTitle' => $exam['ExamTitle'],
            'examDate' => date('Y-m-d', strtotime($exam['AssignmentDate'])),
            'examTime' => '10:00',
            'duration' => gmdate('H:i', $exam['Duration']) . ' minutes',
            'questionCount' => $exam['QuestionCount'],
            'passingScore' => $exam['PassingScore'] . '%',
            'status' => ucfirst($exam['AssignmentStatus']),
            'company' => $exam['CompanyName'],
            'jobPosition' => $exam['JobTitle'],
            'examInstructions' => 'Please read each question carefully and select the best answer. Complete the exam within the allotted time.',
            'assignmentId' => $exam['AssignmentID'],
            'dueDate' => $exam['DueDate'],
            'applicationStatus' => ucfirst($exam['ApplicationStatus']),
            'score' => $exam['Score'],
            'completedAt' => $exam['CompletedAt']
        ];
        
        // Debug: Log each exam's status
        error_log("Exam ID: " . $exam['ExamID'] . ", ApplicationStatus: " . $exam['ApplicationStatus'] . ", AssignmentStatus: " . $exam['AssignmentStatus']);
        
        // Categorize based on status - only include applied job exams and NON-COMPLETED exams
        if ($exam['ApplicationStatus'] !== 'unapplied') {
            if ($exam['AssignmentStatus'] !== 'completed') {
                // Only add NON-completed exams to scheduled exams
                // Completed exams will be handled by getCompletedExams() separately
                $examData['status'] = 'Scheduled';
                $scheduledExams[] = $examData;
                error_log("Added to scheduled exams: " . $exam['ExamTitle']);
            } else {
                error_log("Skipped completed exam from assigned exams (will be handled by getCompletedExams): " . $exam['ExamTitle']);
            }
        } else {
            error_log("Skipped unapplied exam: " . $exam['ExamTitle']);
        }
    }
    
    // Get completed exams (separate from assigned exams to avoid duplicates)
    $completedExamsData = getCompletedExams($sessionCandidateId);
    
    foreach ($completedExamsData as $exam) {
        // Show ALL completed exams, regardless of passing score
        $status = 'Completed';
        $scoreColor = '#3fb950';
        
        $completedExams[] = [
            'id' => $exam['ExamID'],
            'examTitle' => $exam['ExamTitle'],
            'examDate' => date('Y-m-d', strtotime($exam['CompletedAt'])),
            'examTime' => date('H:i', strtotime($exam['CompletedAt'])),
            'duration' => gmdate('H:i', $exam['Duration']) . ' minutes',
            'questionCount' => $exam['QuestionCount'],
            'passingScore' => $exam['PassingScore'] . '%',
            'status' => $status,
            'score' => $exam['Score'] . '%',
            'company' => $exam['CompanyName'],
            'jobPosition' => $exam['JobTitle'],
            'examInstructions' => 'This exam has been completed. Review your results below.',
            'examResults' => generateExamResults($exam),
            'timeSpent' => $exam['TimeSpent'],
            'correctAnswers' => $exam['CorrectAnswers'],
            'totalQuestions' => $exam['TotalQuestions']
        ];
        
        // Debug: Log completed exam data
        error_log("Completed exam data: " . print_r($completedExams[count($completedExams)-1], true));
    }
    
    // Check for newly assigned exams (assigned in the last 24 hours)
    $newlyAssignedCount = 0;
    try {
        $newExamsStmt = $pdo->prepare("
            SELECT COUNT(*) as new_count
            FROM exam_assignments ea
            JOIN exams e ON ea.ExamID = e.ExamID
            WHERE ea.CandidateID = ? 
            AND ea.AssignmentDate >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
            AND ea.AssignmentStatus = 'assigned'
        ");
        $newExamsStmt->execute([$sessionCandidateId]);
        $newExamsResult = $newExamsStmt->fetch(PDO::FETCH_ASSOC);
        $newlyAssignedCount = $newExamsResult['new_count'] ?? 0;
    } catch (Exception $e) {
        error_log("Error checking for newly assigned exams: " . $e->getMessage());
    }
    
    // Debug: Log final counts
    error_log("Final scheduled exams count: " . count($scheduledExams));
    error_log("Final completed exams count: " . count($completedExams));
    error_log("Newly assigned exams count: " . $newlyAssignedCount);
    
} catch (Exception $e) {
    error_log("Error loading exam data: " . $e->getMessage());
    // Don't fallback to sample data - show empty state instead
    $scheduledExams = [];
    $completedExams = [];
    
    // Show error message to user
    $examLoadError = "Unable to load exam data. Please refresh the page or contact support if the problem persists.";
}

// No sample data - only show real exams

// Only add sample completed exams if no completed exams exist at all
if (empty($completedExams)) {
    // No sample completed exams - only show real completed exams
}

// Function to generate exam results text
function generateExamResults($exam) {
    $score = $exam['Score'];
    $passingScore = $exam['PassingScore'];
    $correctAnswers = $exam['CorrectAnswers'];
    $totalQuestions = $exam['TotalQuestions'];
    $timeSpent = $exam['TimeSpent'];
    
    $result = "You scored {$score}% on this assessment ";
    
    if ($score >= $passingScore) {
        $result .= "(PASSED). ";
    } else {
        $result .= "(FAILED - Required: {$passingScore}%). ";
    }
    
    $result .= "You answered {$correctAnswers} out of {$totalQuestions} questions correctly. ";
    
    if ($timeSpent && $timeSpent > 0) {
        $timeFormatted = gmdate('H:i:s', $timeSpent);
        $result .= "Time taken: {$timeFormatted}. ";
    } else {
        // If TimeSpent is 0, show a default message
        $result .= "Time taken: Not recorded. ";
    }
    
    if ($score >= 90) {
        $result .= "Excellent performance! You demonstrated mastery of the subject matter.";
    } elseif ($score >= 80) {
        $result .= "Good performance! You have a solid understanding of the concepts.";
    } elseif ($score >= 70) {
        $result .= "Satisfactory performance. Consider reviewing areas where you missed questions.";
    } else {
        $result .= "Consider reviewing the study materials and retaking the assessment.";
    }
    
    return $result;
}



// Function to get status color
function getStatusColor($status) {
    switch ($status) {
        case 'Scheduled':
            return '#f59e0b';
        case 'Completed':
            return '#3fb950';
        case 'Failed':
            return '#f85149';
        default:
            return '#8b949e';
    }
}

// Function to get score color
function getScoreColor($score, $passingScore) {
    // Remove % symbol if present
    $scoreValue = intval(str_replace('%', '', $score));
    $passingValue = intval(str_replace('%', '', $passingScore));
    
    return $scoreValue >= $passingValue ? '#3fb950' : '#f85149';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attend Exam - CandiHire</title>
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
            --accent-secondary: #f59e0b;
            --border: #30363d;
            --success: #3fb950;
            --danger: #f85149;
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
            --accent-secondary: #f59e0b;
            --border: #d1d9e0;
            --success: #1a7f37;
            --danger: #d1242f;
            --danger-hover: #b91c26;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Noto Sans', Helvetica, Arial, sans-serif;
        }

        body {
            background-color: var(--bg-primary);
            color: var(--text-primary);
            line-height: 1.5;
        }

        .container {
            display: flex;
            min-height: 100vh;
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Left Navigation */
        .left-nav {
            width: 280px;
            background-color: var(--bg-secondary);
            padding: 20px 15px;
            border-right: 1px solid var(--border);
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
        }

        .logo {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0 10px;
        }

        .candi {
            color: var(--accent);
        }

        .hire {
            color: var(--accent-secondary);
            margin-left: -2px;
        }

        .nav-section {
            margin-bottom: 25px;
        }

        .nav-section-title {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            color: var(--text-secondary);
            margin-bottom: 10px;
            padding: 0 10px;
            letter-spacing: 0.5px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 12px 15px;
            margin-bottom: 6px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 15px;
            text-decoration: none;
            color: var(--text-primary);
            will-change: transform, background-color;
        }

        .nav-item:hover {
            background-color: var(--bg-tertiary);
            color: var(--text-primary);
            transform: translateX(4px);
        }

        .nav-item.active {
            background-color: var(--bg-tertiary);
            font-weight: 500;
        }

        .nav-item i {
            font-size: 20px;
            width: 24px;
            text-align: center;
        }

        /* Logout Button */
        .logout-container {
            margin-top: 20px;
            padding: 0 10px;
        }

        .logout-btn {
            width: 100%;
            background-color: var(--danger);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 10px 14px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.2s;
        }

        .logout-btn:hover {
            background-color: #d03f39;
            transform: translateY(-1px);
        }

        /* Theme Toggle Button */
        .theme-toggle-btn {
            width: 100%;
            background: linear-gradient(135deg, var(--bg-tertiary), var(--bg-secondary));
            color: var(--text-primary);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 12px 16px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            position: relative;
            overflow: hidden;
            backdrop-filter: blur(10px);
            margin-bottom: 10px;
        }

        .theme-toggle-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(88, 166, 255, 0.1), transparent);
            transition: left 0.6s ease;
        }

        .theme-toggle-btn:hover {
            background: linear-gradient(135deg, var(--accent), var(--accent-hover));
            color: white;
            border-color: var(--accent);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(88, 166, 255, 0.3);
        }

        .theme-toggle-btn:hover::before {
            left: 100%;
        }

        .theme-toggle-btn i {
            transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            font-size: 16px;
        }

        .theme-toggle-btn:hover i {
            transform: rotate(360deg) scale(1.1);
        }

        .theme-toggle-btn:active {
            transform: translateY(-1px);
        }

        /* Theme transition effects */
        .theme-toggle-btn[data-theme="dark"] i {
            color: #ffd700;
            text-shadow: 0 0 10px rgba(255, 215, 0, 0.3);
        }

        .theme-toggle-btn[data-theme="light"] i {
            color: #ffa500;
            text-shadow: 0 0 10px rgba(255, 165, 0, 0.3);
        }

        /* Main Content */
        .main-content {
            flex: 1;
            padding: 20px;
            display: flex;
            flex-direction: column;
        }

        /* Welcome Section */
        .welcome-section {
            background: linear-gradient(135deg, var(--bg-secondary), var(--bg-tertiary));
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 24px;
            border: 1px solid var(--border);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .welcome-content {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .welcome-avatar {
            flex-shrink: 0;
        }

        .avatar-placeholder {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent), var(--accent-hover));
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(88, 166, 255, 0.3);
        }

        .avatar-initials {
            font-size: 24px;
            font-weight: bold;
            color: white;
        }

        .welcome-text {
            flex: 1;
        }

        .welcome-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--text-primary);
            margin: 0 0 4px 0;
        }

        .welcome-name {
            font-size: 14px;
            color: var(--text-secondary);
            margin: 0;
        }

        .edit-profile-btn {
            background: linear-gradient(135deg, var(--accent), var(--accent-hover));
            color: white;
            border: none;
            border-radius: 12px;
            padding: 12px 20px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 12px rgba(88, 166, 255, 0.3);
        }

        .edit-profile-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(88, 166, 255, 0.4);
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border);
        }

        .page-title {
            font-size: 36px;
            font-weight: bold;
            color: var(--accent);
        }

        .header-actions {
            display: flex;
            gap: 12px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            will-change: transform, box-shadow;
        }

        .btn-secondary {
            background-color: var(--bg-tertiary);
            color: var(--text-primary);
            border: 1px solid var(--border);
        }

        .btn-secondary:hover {
            background-color: var(--bg-secondary);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
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

        .btn-success {
            background-color: var(--success);
            color: white;
        }

        .btn-success:hover {
            background-color: #2da04e;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(63, 185, 80, 0.3);
        }

        /* Content Sections */
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            flex: 1;
            max-width: 1200px;
            margin: 0 auto;
        }

        .left-column, .right-column {
            display: flex;
            flex-direction: column;
            gap: 20px;
            min-height: 600px;
        }

        .content-section {
            background-color: var(--bg-secondary);
            border-radius: 12px;
            border: 1px solid var(--border);
            overflow: hidden;
            flex: 1;
        }

        .section-header {
            padding: 15px 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .section-title {
            font-size: 20px;
            font-weight: 600;
            color: var(--text-primary);
        }

        .exam-count {
            font-size: 14px;
            font-weight: 400;
            color: var(--accent);
            background: var(--accent-light);
            padding: 2px 8px;
            border-radius: 12px;
            margin-left: 8px;
        }

        .section-body {
            padding: 20px;
            max-height: 500px;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: var(--accent) var(--bg-tertiary);
            scroll-behavior: smooth;
            border-radius: 0 0 12px 12px;
            position: relative;
        }

        /* Enhanced custom scrollbar for webkit browsers */
        .section-body::-webkit-scrollbar {
            width: 14px !important;
        }

        .section-body::-webkit-scrollbar-track {
            background: #f1f5f9 !important;
            border-radius: 8px;
            margin: 2px;
            border: 2px solid #e2e8f0;
        }

        .section-body::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #3b82f6, #2563eb) !important;
            border-radius: 8px;
            transition: all 0.3s ease;
            border: 2px solid #f1f5f9;
            box-shadow: 0 2px 4px rgba(59, 130, 246, 0.3);
        }

        .section-body::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, var(--accent-hover), var(--accent));
            transform: scale(1.05);
            box-shadow: 0 4px 8px rgba(88, 166, 255, 0.3);
        }

        .section-body::-webkit-scrollbar-thumb:active {
            background: var(--accent-hover);
            transform: scale(0.95);
        }

        /* Scrollbar corner */
        .section-body::-webkit-scrollbar-corner {
            background: var(--bg-tertiary);
        }

        /* Enhanced scroll indicator - removed */

        /* Content section styling */
        .content-section {
            position: relative;
        }

        /* Scroll to top button */
        .scroll-to-top {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background: var(--accent);
            color: white;
            border: none;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            opacity: 0;
            transform: translateY(10px);
            transition: all 0.3s ease;
            z-index: 10;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        .scroll-to-top:hover {
            background: var(--accent-hover);
            transform: translateY(0) scale(1.1);
        }

        .scroll-to-top.visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* Enhanced scrollbar for Firefox */
        .section-body {
            scrollbar-width: auto;
            scrollbar-color: var(--accent) var(--bg-tertiary);
        }

        /* Exam Cards */
        .exam-card {
            background-color: var(--bg-primary);
            border-radius: 8px;
            border: 1px solid var(--border);
            padding: 20px;
            margin-bottom: 20px;
            transition: transform 0.2s, box-shadow 0.2s;
            min-height: 240px;
            max-height: 320px;
            overflow: hidden;
        }

        .exam-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .exam-card:last-child {
            margin-bottom: 0;
        }

        .exam-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text-primary);
        }

        .exam-company {
            font-size: 14px;
            color: var(--text-secondary);
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .exam-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 16px;
        }

        .exam-detail {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }

        .exam-detail i {
            color: var(--text-secondary);
            width: 16px;
        }

        .exam-detail-label {
            color: var(--text-secondary);
        }

        .exam-status {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid var(--border);
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            gap: 6px;
        }

        .status-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background-color: currentColor;
        }

        .score-badge {
            font-size: 14px;
            font-weight: 600;
        }

        .exam-actions {
            display: flex;
            gap: 8px;
            margin-top: 16px;
            visibility: visible !important;
            opacity: 1 !important;
        }

        .btn-small {
            padding: 6px 12px;
            font-size: 12px;
            display: inline-flex !important;
            visibility: visible !important;
            opacity: 1 !important;
        }

        .start-exam-btn {
            background-color: var(--accent) !important;
            color: white !important;
            border: none !important;
            display: inline-flex !important;
            visibility: visible !important;
            opacity: 1 !important;
            position: relative !important;
            z-index: 10 !important;
            pointer-events: auto !important;
        }

        .start-exam-btn:hover {
            background-color: var(--accent-hover) !important;
            transform: translateY(-2px) !important;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2) !important;
        }



        /* Exam Details Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: var(--bg-secondary);
            border-radius: 12px;
            width: 90%;
            max-width: 700px;
            max-height: 90vh;
            overflow-y: auto;
            border: 1px solid var(--border);
        }

        .modal-header {
            padding: 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title {
            font-size: 24px;
            font-weight: 600;
            color: var(--text-primary);
        }

        .close-btn {
            background: none;
            border: none;
            color: var(--text-secondary);
            font-size: 24px;
            cursor: pointer;
            transition: color 0.2s;
        }

        .close-btn:hover {
            color: var(--text-primary);
        }

        .modal-body {
            padding: 20px;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
        }

        .detail-label {
            font-size: 12px;
            color: var(--text-secondary);
            margin-bottom: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .detail-value {
            font-size: 16px;
            font-weight: 500;
            color: var(--text-primary);
        }

        .exam-instructions {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid var(--border);
        }

        .exam-instructions h3 {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--text-primary);
        }

        .exam-instructions p {
            font-size: 14px;
            color: var(--text-secondary);
            line-height: 1.5;
        }

        .exam-results {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid var(--border);
        }

        .exam-results h3 {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--text-primary);
        }

        .exam-results p {
            font-size: 14px;
            color: var(--text-secondary);
            line-height: 1.5;
            background-color: var(--bg-primary);
            border-radius: 8px;
            padding: 15px;
            border: 1px solid var(--border);
        }

        /* System Check */
        .system-check {
            background-color: var(--bg-primary);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid var(--border);
        }

        .system-check-title {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 15px;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .check-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid var(--border);
        }

        .check-item:last-child {
            border-bottom: none;
        }

        .check-name {
            font-size: 14px;
            color: var(--text-primary);
        }

        .check-status {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 14px;
            font-weight: 500;
        }

        .check-status i {
            font-size: 12px;
        }

        .status-success {
            color: var(--success);
        }

        .status-error {
            color: var(--danger);
        }

        /* Notification Toast */
        .toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: var(--bg-secondary);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 15px 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            transform: translateY(100px);
            opacity: 0;
            transition: all 0.3s ease;
            z-index: 1001;
        }

        .toast.show {
            transform: translateY(0);
            opacity: 1;
        }

        .toast-icon {
            font-size: 20px;
        }

        .toast.success .toast-icon {
            color: var(--success);
        }

        .toast.error .toast-icon {
            color: var(--danger);
        }

        .toast.info .toast-icon {
            color: var(--accent);
        }

        .toast-message {
            font-size: 14px;
            color: var(--text-primary);
        }

        /* Confirmation Dialog */
        .confirmation-dialog {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            z-index: 1002;
            display: none;
            justify-content: center;
            align-items: center;
        }

        .confirmation-content {
            background-color: var(--bg-secondary);
            border-radius: 12px;
            width: 90%;
            max-width: 500px;
            padding: 25px;
            border: 1px solid var(--border);
        }

        .confirmation-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 15px;
            color: var(--text-primary);
        }

        .confirmation-message {
            font-size: 16px;
            color: var(--text-secondary);
            margin-bottom: 20px;
            line-height: 1.5;
        }

        .confirmation-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        /* Loading Indicator */
        .loading-indicator {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Popup Overlay */
        .popup-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 10000;
            backdrop-filter: blur(5px);
        }

        .popup-content {
            background: var(--bg-secondary);
            border-radius: 16px;
            padding: 30px;
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
            border: 1px solid var(--border);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            animation: popupSlideIn 0.3s ease-out;
        }

        .popup-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border);
        }

        .popup-title {
            font-size: 24px;
            font-weight: 600;
            color: var(--accent);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .popup-close {
            background: none;
            border: none;
            color: var(--text-secondary);
            font-size: 24px;
            cursor: pointer;
            padding: 5px;
            border-radius: 50%;
            transition: all 0.2s;
        }

        .popup-close:hover {
            background: var(--bg-tertiary);
            color: var(--text-primary);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            color: var(--text-primary);
            font-weight: 500;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            background: var(--bg-primary);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 12px 16px;
            color: var(--text-primary);
            font-size: 14px;
            transition: border-color 0.2s;
            box-sizing: border-box;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(88, 166, 255, 0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }

        .form-group input::placeholder,
        .form-group textarea::placeholder {
            color: var(--text-secondary);
        }

        .form-group select option {
            background: var(--bg-primary);
            color: var(--text-primary);
        }

        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid var(--border);
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: var(--accent);
            color: white;
        }

        .btn-primary:hover {
            background: var(--accent-hover);
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: var(--bg-tertiary);
            color: var(--text-primary);
            border: 1px solid var(--border);
        }

        .btn-secondary:hover {
            background: var(--bg-primary);
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none !important;
        }

        @keyframes popupSlideIn {
            from {
                opacity: 0;
                transform: scale(0.9) translateY(-20px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .content-grid {
                max-width: 100%;
                margin: 0;
            }
        }

        @media (max-width: 1024px) {
            .content-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .left-column, .right-column {
                min-height: 400px;
            }
        }

        @media (max-width: 768px) {
            .left-nav {
                display: none;
            }
            .main-content {
                padding: 15px;
            }
            .page-header {
                flex-direction: column;
                gap: 16px;
                align-items: stretch;
            }
            .header-actions {
                flex-direction: column;
            }
            .exam-details {
                grid-template-columns: 1fr;
                gap: 8px;
            }
            .detail-grid {
                grid-template-columns: 1fr;
            }
            
            /* Adjust scrollbar for mobile */
            .section-body {
                max-height: 300px;
            }
            
            .section-body::-webkit-scrollbar {
                width: 6px;
            }
            
            .exam-card {
                padding: 15px;
                min-height: 200px;
                max-height: 280px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Left Navigation -->
        <div class="left-nav">
            <div class="logo">
                <span class="candiHire">
  <span class="candi">Candi</span><span class="hire">Hire</span>
</span>
            </div>
            
            <!-- Welcome Section -->
            <div class="welcome-section" style="background: var(--bg-tertiary); padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid var(--border);">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div id="candidateAvatar" style="width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 16px; <?php echo $candidateProfilePicture ? 'background-image: url(' . $candidateProfilePicture . '); background-size: cover; background-position: center;' : 'background: linear-gradient(135deg, var(--accent), var(--accent-secondary));'; ?>">
                        <?php echo $candidateProfilePicture ? '' : strtoupper(substr($candidateName, 0, 1)); ?>
                    </div>
                    <div>
                        <div style="color: var(--text-primary); font-weight: 600; font-size: 14px;">Welcome back!</div>
                        <div id="candidateNameDisplay" style="color: var(--text-secondary); font-size: 12px;"><?php echo htmlspecialchars($candidateName); ?></div>
                    </div>
                </div>
                <button id="editProfileBtn" style="background: var(--accent); color: white; border: none; border-radius: 6px; padding: 8px 12px; font-size: 12px; cursor: pointer; margin-top: 10px; width: 100%; transition: background 0.2s;" onmouseover="this.style.background='var(--accent-hover)'" onmouseout="this.style.background='var(--accent)'">
                    <i class="fas fa-user-edit" style="margin-right: 6px;"></i>Edit Profile
                </button>
            </div>
            
            <!-- Main Menu Section -->
            <div class="nav-section">
                <div class="nav-section-title">Main menu</div>
                <a href="CandidateDashboard.php" class="nav-item">
                    <i class="fas fa-home"></i>
                    <span>News feed</span>
                </a>
                <a href="CvBuilder.php" class="nav-item">
                    <i class="fas fa-file-alt"></i>
                    <span>CV builder</span>
                </a>
                <a href="applicationstatus.php" class="nav-item">
                    <i class="fas fa-clipboard-list"></i>
                    <span>Application status</span>
                </a>
            </div>
            
            <!-- Interviews & Exams Section -->
            <div class="nav-section">
                <div class="nav-section-title">Interviews & Exams</div>
                <a href="interview_schedule.php" class="nav-item">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Interview schedule</span>
                </a>
                <a href="attendexam.php" class="nav-item active">
                    <i class="fas fa-pencil-alt"></i>
                    <span>Attend Exam</span>
                </a>
            </div>

            <!-- Logout -->
            <div class="logout-container">
                <button id="themeToggleBtn" class="theme-toggle-btn" title="Switch to Light Mode">
                    <i class="fas fa-moon-stars" id="themeIcon"></i>
                    <span id="themeText">Light Mode</span>
                </button>
                <button id="logoutBtn" class="logout-btn"><i class="fas fa-sign-out-alt" style="margin-right:8px;"></i>Logout</button>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Page Header -->
            <div class="page-header">
                <h1 class="page-title">Attend Exam</h1>
                <div class="header-actions">
                    <button class="btn btn-primary" id="refreshBtn">
                        <i class="fas fa-sync-alt"></i>
                        Refresh
                    </button>
                </div>
            </div>

            <!-- Content Grid -->
            <div class="content-grid">
                <!-- Left Column - Scheduled Exams -->
                <div class="left-column">
                    <div class="content-section">
                        <div class="section-header">
                            <h2 class="section-title">Scheduled Exams <span class="exam-count">(<?php echo count($scheduledExams); ?>)</span></h2>
                        </div>
                        <div class="section-body">
                            <?php if (isset($examLoadError)): ?>
                            <div class="error-message" style="text-align: center; padding: 40px 20px; color: var(--danger);">
                                <i class="fas fa-exclamation-triangle" style="font-size: 48px; margin-bottom: 20px; opacity: 0.7;"></i>
                                <h3 style="margin-bottom: 10px; color: var(--danger);">Error Loading Exams</h3>
                                <p><?php echo htmlspecialchars($examLoadError); ?></p>
                                <button class="btn btn-primary" onclick="window.location.reload()" style="margin-top: 15px;">
                                    <i class="fas fa-refresh"></i> Refresh Page
                                </button>
                            </div>
                            <?php elseif (empty($scheduledExams)): ?>
                            <div class="no-exams-message" style="text-align: center; padding: 40px 20px; color: var(--text-secondary);">
                                <i class="fas fa-calendar-times" style="font-size: 48px; margin-bottom: 20px; opacity: 0.5;"></i>
                                <h3 style="margin-bottom: 10px; color: var(--text-primary);">No Scheduled Exams</h3>
                                <p>You don't have any scheduled exams at the moment. Check back later or contact your recruiter for exam assignments.</p>
                            </div>
                            <?php else: ?>
                            <?php foreach ($scheduledExams as $exam): ?>
                            <div class="exam-card" data-id="<?php echo $exam['id']; ?>">
                                <h3 class="exam-title"><?php echo $exam['examTitle']; ?></h3>
                                <div class="exam-company">
                                    <i class="fas fa-building"></i>
                                    <?php echo $exam['company']; ?> - <?php echo $exam['jobPosition']; ?>
                                </div>
                                <div class="exam-details">
                                    <div class="exam-detail">
                                        <i class="fas fa-calendar"></i>
                                        <span class="exam-detail-label">Date:</span>
                                        <span><?php echo date('F j, Y', strtotime($exam['examDate'])); ?></span>
                                    </div>
                                    <div class="exam-detail">
                                        <i class="fas fa-hourglass-half"></i>
                                        <span class="exam-detail-label">Duration:</span>
                                        <span><?php echo $exam['duration']; ?></span>
                                    </div>
                                    <div class="exam-detail">
                                        <i class="fas fa-question-circle"></i>
                                        <span class="exam-detail-label">Questions:</span>
                                        <span><?php echo $exam['questionCount']; ?></span>
                                    </div>
                                </div>
                                <div class="exam-status">
                                    <span class="status-badge" style="color: <?php echo getStatusColor($exam['status']); ?>; border: 1px solid <?php echo getStatusColor($exam['status']); ?>;">
                                        <span class="status-dot"></span>
                                        <?php echo $exam['status']; ?>
                                    </span>
                                    <span class="exam-detail">
                                        <span class="exam-detail-label">Passing Score:</span>
                                        <span><?php echo $exam['passingScore']; ?></span>
                                    </span>
                                </div>
                                <div class="exam-actions">
                                    <button class="btn btn-primary btn-small start-exam-btn" data-id="<?php echo $exam['id']; ?>">
                                        <i class="fas fa-play"></i>
                                        Start Exam
                                    </button>
                                    <button class="btn btn-secondary btn-small view-exam-btn" data-id="<?php echo $exam['id']; ?>">
                                        <i class="fas fa-eye"></i>
                                        View Details
                                    </button>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Completed Exams -->
                <div class="right-column">
                    <div class="content-section">
                        <div class="section-header">
                            <h2 class="section-title">Completed Exams <span class="exam-count">(<?php echo count($completedExams); ?>)</span></h2>
                        </div>
                        <div class="section-body">
                            <?php if (empty($completedExams)): ?>
                            <div class="no-exams-message" style="text-align: center; padding: 40px 20px; color: var(--text-secondary);">
                                <i class="fas fa-check-circle" style="font-size: 48px; margin-bottom: 20px; opacity: 0.5;"></i>
                                <h3 style="margin-bottom: 10px; color: var(--text-primary);">No Completed Exams</h3>
                                <p>You haven't completed any exams yet. Start by taking your scheduled exams!</p>
                            </div>
                            <?php else: ?>
                            <?php foreach ($completedExams as $exam): ?>
                            <div class="exam-card" data-id="<?php echo $exam['id']; ?>">
                                <h3 class="exam-title"><?php echo $exam['examTitle']; ?></h3>
                                <div class="exam-company">
                                    <i class="fas fa-building"></i>
                                    <?php echo $exam['company']; ?> - <?php echo $exam['jobPosition']; ?>
                                </div>
                                <div class="exam-details">
                                    <div class="exam-detail">
                                        <i class="fas fa-calendar"></i>
                                        <span class="exam-detail-label">Date:</span>
                                        <span><?php echo date('F j, Y', strtotime($exam['examDate'])); ?></span>
                                    </div>
                                    <div class="exam-detail">
                                        <i class="fas fa-hourglass-half"></i>
                                        <span class="exam-detail-label">Duration:</span>
                                        <span><?php echo $exam['duration']; ?></span>
                                    </div>
                                    <div class="exam-detail">
                                        <i class="fas fa-trophy"></i>
                                        <span class="exam-detail-label">Passing Score:</span>
                                        <span><?php echo $exam['passingScore']; ?></span>
                                    </div>
                                </div>
                                <div class="exam-status">
                                    <span class="status-badge" style="color: <?php echo getStatusColor($exam['status']); ?>; border: 1px solid <?php echo getStatusColor($exam['status']); ?>;">
                                        <span class="status-dot"></span>
                                        <?php echo $exam['status']; ?>
                                    </span>
                                    <span class="score-badge" style="color: <?php echo getScoreColor($exam['score'], $exam['passingScore']); ?>;">
                                        Score: <?php echo isset($exam['score']) ? $exam['score'] : 'N/A'; ?>
                                    </span>
                                </div>
                                <div class="exam-actions">
                                    <button class="btn btn-secondary btn-small view-exam-btn" data-id="<?php echo $exam['id']; ?>">
                                        <i class="fas fa-eye"></i>
                                        View Details
                                    </button>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Exam Details Modal -->
    <div class="modal" id="detailsModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="modalExamTitle">Exam Details</h2>
                <button class="close-btn" id="closeModalBtn">&times;</button>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Content will be dynamically inserted here -->
            </div>
        </div>
    </div>


    <!-- Confirmation Dialog -->
    <div class="confirmation-dialog" id="confirmationDialog">
        <div class="confirmation-content">
            <h3 class="confirmation-title" id="confirmationTitle">Confirm Action</h3>
            <p class="confirmation-message" id="confirmationMessage">Are you sure you want to proceed?</p>
            <div class="confirmation-actions">
                <button class="btn btn-secondary" id="cancelConfirmationBtn">Cancel</button>
                <button class="btn btn-primary" id="confirmActionBtn">Confirm</button>
            </div>
        </div>
    </div>

    <!-- Notification Toast -->
    <div class="toast" id="toast">
        <i class="toast-icon fas"></i>
        <div class="toast-message">        </div>
    </div>

    <!-- Profile Edit Popup -->
    <div id="profileEditPopup" class="popup-overlay" style="display: none;">
        <div class="popup-content">
            <div class="popup-header">
                <div class="popup-title">
                    <i class="fas fa-user-edit"></i>
                    Edit Profile
                </div>
                <button class="popup-close" onclick="closeProfileEditPopup()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="profileEditForm" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="profilePicture">Profile Picture</label>
                    <input type="file" id="profilePicture" name="profilePicture" accept="image/*">
                    <div id="currentProfilePicture" style="margin-top: 10px; display: none;">
                        <img id="profilePicturePreview" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 2px solid var(--border);" />
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="fullName">Full Name *</label>
                    <input type="text" id="fullName" name="fullName" required>
                </div>
                
                <div class="form-group">
                    <label for="phoneNumber">Phone Number *</label>
                    <input type="tel" id="phoneNumber" name="phoneNumber" required>
                </div>
                
                <div class="form-group">
                    <label for="workType">Work Type *</label>
                    <select id="workType" name="workType" required>
                        <option value="">Select Work Type</option>
                        <option value="full-time">Full-time</option>
                        <option value="part-time">Part-time</option>
                        <option value="contract">Contract</option>
                        <option value="freelance">Freelance</option>
                        <option value="internship">Internship</option>
                        <option value="fresher">Fresher</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="yearsOfExperience">Years of Experience</label>
                    <input type="number" id="yearsOfExperience" name="yearsOfExperience" 
                           placeholder="Enter years of experience" min="0" max="50" 
                           style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px; background-color: var(--bg-secondary); color: var(--text-primary); font-size: 14px;">
                </div>
                
                <div class="form-group">
                    <label for="location">Location</label>
                    <input type="text" id="location" name="location" placeholder="City, Country">
                </div>
                
                <div class="form-group">
                    <label for="skills">Skills</label>
                    <textarea id="skills" name="skills" placeholder="List your key skills separated by commas"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="summary">Professional Summary</label>
                    <textarea id="summary" name="summary" placeholder="Brief description about yourself and your professional background"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="linkedin">LinkedIn Profile</label>
                    <input type="url" id="linkedin" name="linkedin" placeholder="https://linkedin.com/in/yourprofile">
                </div>
                
                <div class="form-group">
                    <label for="github">GitHub Profile</label>
                    <input type="url" id="github" name="github" placeholder="https://github.com/yourusername">
                </div>
                
                <div class="form-group">
                    <label for="portfolio">Portfolio Website</label>
                    <input type="url" id="portfolio" name="portfolio" placeholder="https://yourportfolio.com">
                </div>
                
                <div class="form-group">
                    <label for="education">Education/Degree</label>
                    <input type="text" id="education" name="education" placeholder="e.g., Bachelor's in Computer Science">
                </div>
                
                <div class="form-group">
                    <label for="institute">Institute/University</label>
                    <input type="text" id="institute" name="institute" placeholder="e.g., MIT, Stanford University">
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeProfileEditPopup()">
                        <i class="fas fa-times"></i>
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary" id="submitProfileUpdate">
                        <i class="fas fa-save"></i>
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Profile editing functionality
        function setupProfileEditing() {
            console.log('Setting up profile editing...');
            
            const editProfileBtn = document.getElementById('editProfileBtn');
            const profilePopup = document.getElementById('profileEditPopup');
            const profileForm = document.getElementById('profileEditForm');
            
            if (!editProfileBtn || !profilePopup || !profileForm) {
                console.error('Profile editing elements not found');
                return;
            }
            
            // Open profile edit popup
            editProfileBtn.addEventListener('click', function() {
                console.log('Opening profile edit popup');
                loadCurrentProfile();
                profilePopup.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            });
            
            // Handle form submission
            profileForm.addEventListener('submit', function(e) {
                e.preventDefault();
                updateProfile();
            });
            
            // Handle profile picture preview
            const profilePictureInput = document.getElementById('profilePicture');
            if (profilePictureInput) {
                profilePictureInput.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const preview = document.getElementById('profilePicturePreview');
                            const currentPicture = document.getElementById('currentProfilePicture');
                            if (preview && currentPicture) {
                                preview.src = e.target.result;
                                currentPicture.style.display = 'block';
                            }
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }
            
            // Close popup when clicking outside
            profilePopup.addEventListener('click', function(e) {
                if (e.target === profilePopup) {
                    closeProfileEditPopup();
                }
            });
            
            console.log('Profile editing setup complete');
        }

        // Load current profile data
        function loadCurrentProfile() {
            console.log('Loading current profile data...');
            
            const candidateId = <?php echo json_encode($sessionCandidateId); ?>;
            
            fetch(`candidate_profile_handler.php?candidateId=${candidateId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const candidate = data.candidate;
                        console.log('Profile data loaded:', candidate);
                        
                        // Populate form fields
                        document.getElementById('fullName').value = candidate.FullName || '';
                        document.getElementById('phoneNumber').value = candidate.PhoneNumber || '';
                        document.getElementById('workType').value = candidate.WorkType || '';
                        document.getElementById('yearsOfExperience').value = candidate.YearsOfExperience || 0;
                        document.getElementById('location').value = candidate.Location || '';
                        document.getElementById('skills').value = candidate.Skills || '';
                        document.getElementById('summary').value = candidate.Summary || '';
                        document.getElementById('linkedin').value = candidate.LinkedIn || '';
                        document.getElementById('github').value = candidate.GitHub || '';
                        document.getElementById('portfolio').value = candidate.Portfolio || '';
                        document.getElementById('education').value = candidate.Education || '';
                        document.getElementById('institute').value = candidate.Institute || '';
                        
                        // Handle profile picture
                        if (candidate.ProfilePicture) {
                            const preview = document.getElementById('profilePicturePreview');
                            const currentPicture = document.getElementById('currentProfilePicture');
                            if (preview && currentPicture) {
                                preview.src = candidate.ProfilePicture;
                                currentPicture.style.display = 'block';
                            }
                        }
                    } else {
                        console.error('Failed to load profile:', data.message);
                        showErrorMessage('Failed to load profile data');
                    }
                })
                .catch(error => {
                    console.error('Error loading profile:', error);
                    showErrorMessage('Network error loading profile');
                });
        }

        // Update profile
        function updateProfile() {
            console.log('Updating profile...');
            
            const form = document.getElementById('profileEditForm');
            const submitBtn = document.getElementById('submitProfileUpdate');
            const formData = new FormData(form);
            
            // Add candidate ID
            const candidateId = <?php echo json_encode($sessionCandidateId); ?>;
            formData.append('candidateId', candidateId);
            
            // Disable button and show loading
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            
            fetch('candidate_profile_handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeProfileEditPopup();
                    showSuccessMessage('Profile updated successfully!');
                    
                    // Update the display with server response data
                    if (data.fullName) {
                        document.getElementById('candidateNameDisplay').textContent = data.fullName;
                        
                        // Update avatar text or image
                        const avatar = document.getElementById('candidateAvatar');
                        if (data.profilePicture) {
                            // Show profile picture
                            avatar.style.backgroundImage = `url(${data.profilePicture})`;
                            avatar.style.backgroundSize = 'cover';
                            avatar.style.backgroundPosition = 'center';
                            avatar.textContent = '';
                        } else {
                            // Show initials
                            avatar.style.backgroundImage = '';
                            avatar.style.background = 'linear-gradient(135deg, var(--accent), var(--accent-secondary))';
                            avatar.textContent = data.fullName.charAt(0).toUpperCase();
                        }
                    }
                } else {
                    showErrorMessage(data.message || 'Failed to update profile');
                }
            })
            .catch(error => {
                console.error('Error updating profile:', error);
                showErrorMessage('Network error. Please try again.');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-save"></i> Save Changes';
            });
        }

        // Close profile edit popup
        function closeProfileEditPopup() {
            const popup = document.getElementById('profileEditPopup');
            const form = document.getElementById('profileEditForm');
            
            popup.style.display = 'none';
            document.body.style.overflow = 'auto';
            form.reset();
            
            // Hide profile picture preview
            const currentPicture = document.getElementById('currentProfilePicture');
            if (currentPicture) {
                currentPicture.style.display = 'none';
            }
        }

        // Show success message
        function showSuccessMessage(message) {
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: var(--success);
                color: white;
                padding: 15px 20px;
                border-radius: 8px;
                z-index: 10001;
                font-weight: 500;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
                animation: slideIn 0.3s ease-out;
            `;
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }

        // Show error message
        function showErrorMessage(message) {
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: var(--danger);
                color: white;
                padding: 15px 20px;
                border-radius: 8px;
                z-index: 10001;
                font-weight: 500;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
                animation: slideIn 0.3s ease-out;
            `;
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 5000);
        }
        
        // Exam data (in a real app, this would come from an API)
        const scheduledExams = <?php echo json_encode($scheduledExams); ?>;
        const completedExams = <?php echo json_encode($completedExams); ?>;
        
        // Debug: Log exam data
        console.log('Scheduled Exams:', scheduledExams);
        console.log('Completed Exams:', completedExams);
        console.log('Total Scheduled Exams:', scheduledExams.length);
        console.log('Total Completed Exams:', completedExams.length);
        
        // Debug: Check if buttons exist
        setTimeout(() => {
            const startButtons = document.querySelectorAll('.start-exam-btn');
            const viewButtons = document.querySelectorAll('.view-exam-btn');
            const examCards = document.querySelectorAll('.exam-card');
            
            console.log('Exam Cards found:', examCards.length);
            console.log('Start Exam Buttons found:', startButtons.length);
            console.log('View Details Buttons found:', viewButtons.length);
            
            startButtons.forEach((btn, index) => {
                console.log(`Start Button ${index}:`, btn, 'Visible:', btn.offsetParent !== null);
            });
        }, 1000);
        
        // Combine all exams for easier access
        const allExams = [...scheduledExams, ...completedExams];
        
        // DOM Elements
        const detailsModal = document.getElementById('detailsModal');
        const closeModalBtn = document.getElementById('closeModalBtn');
        const modalExamTitle = document.getElementById('modalExamTitle');
        const modalBody = document.getElementById('modalBody');
        const toast = document.getElementById('toast');
        
        // Confirmation Dialog Elements
        const confirmationDialog = document.getElementById('confirmationDialog');
        const confirmationTitle = document.getElementById('confirmationTitle');
        const confirmationMessage = document.getElementById('confirmationMessage');
        const cancelConfirmationBtn = document.getElementById('cancelConfirmationBtn');
        const confirmActionBtn = document.getElementById('confirmActionBtn');
        
        // Button Elements
        const refreshBtn = document.getElementById('refreshBtn');
        
        // Refresh Button
        refreshBtn.addEventListener('click', function() {
            // Add loading state to button
            const originalContent = refreshBtn.innerHTML;
            refreshBtn.innerHTML = '<span class="loading-indicator"></span> Refreshing...';
            refreshBtn.disabled = true;
            
            // Simulate refreshing the page data
            setTimeout(() => {
                refreshBtn.innerHTML = originalContent;
                refreshBtn.disabled = false;
                showToast('Exam list refreshed', 'success');
                
                // In a real application, this would fetch fresh data from the server
            }, 1500);
        });

        // Enhanced scrollbar functionality
        function initializeScrollbars() {
            const sectionBodies = document.querySelectorAll('.section-body');
            
            sectionBodies.forEach(section => {
                // Create scroll-to-top button
                const scrollToTopBtn = document.createElement('button');
                scrollToTopBtn.className = 'scroll-to-top';
                scrollToTopBtn.innerHTML = '<i class="fas fa-chevron-up"></i>';
                scrollToTopBtn.title = 'Scroll to top';
                section.appendChild(scrollToTopBtn);
                
                // Add scroll event listener
                section.addEventListener('scroll', function() {
                    const isScrolled = this.scrollTop > 0;
                    
                    // Show/hide scroll-to-top button
                    scrollToTopBtn.classList.toggle('visible', isScrolled);
                });
                
                // Scroll to top functionality
                scrollToTopBtn.addEventListener('click', function() {
                    section.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                });
                
                // Scroll initialization complete
            });
        }

        // Initialize scrollbars when page loads
        initializeScrollbars();
        
        
        // View exam details buttons
        document.addEventListener('click', function(e) {
            const viewBtn = e.target.closest('.view-exam-btn');
            if (viewBtn) {
                e.preventDefault();
                e.stopPropagation();
                const examId = parseInt(viewBtn.getAttribute('data-id'));
                if (!isNaN(examId)) {
                    showExamDetails(examId);
                } else {
                    console.error('Invalid exam ID for view button');
                }
            }
        });
        
        // Start exam buttons
        document.addEventListener('click', function(e) {
            const startBtn = e.target.closest('.start-exam-btn');
            if (startBtn) {
                e.preventDefault();
                e.stopPropagation();
                const examId = parseInt(startBtn.getAttribute('data-id'));
                if (!isNaN(examId)) {
                    confirmStartExam(examId);
                } else {
                    console.error('Invalid exam ID for start button');
                }
            }
        });
        
        
        // Modal close functionality
        closeModalBtn.addEventListener('click', function() {
            detailsModal.style.display = 'none';
        });
        
        // Close modal when clicking outside
        window.addEventListener('click', function(e) {
            if (e.target === detailsModal) {
                detailsModal.style.display = 'none';
            }
            if (e.target === confirmationDialog) {
                confirmationDialog.style.display = 'none';
            }
        });
        
        // Confirmation dialog functionality
        cancelConfirmationBtn.addEventListener('click', function() {
            confirmationDialog.style.display = 'none';
        });
        
        confirmActionBtn.addEventListener('click', function() {
            // Execute the confirmed action
            if (confirmActionBtn.getAttribute('data-action') === 'startExam') {
                const examId = parseInt(confirmActionBtn.getAttribute('data-exam-id'));
                startExam(examId);
            }
            
            confirmationDialog.style.display = 'none';
        });
        
        // Show exam details function
        function showExamDetails(examId) {
            const exam = allExams.find(exam => exam.id === examId);
            
            if (!exam) return;
            
            modalExamTitle.textContent = exam.examTitle;
            
            const isScheduled = scheduledExams.some(e => e.id === examId);
            
            modalBody.innerHTML = `
                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="detail-label">Exam Title</div>
                        <div class="detail-value">${exam.examTitle}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Company</div>
                        <div class="detail-value">${exam.company}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Position</div>
                        <div class="detail-value">${exam.jobPosition}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Exam Date</div>
                        <div class="detail-value">${new Date(exam.examDate).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Duration</div>
                        <div class="detail-value">${exam.duration}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Questions</div>
                        <div class="detail-value">${exam.questionCount}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Passing Score</div>
                        <div class="detail-value">${exam.passingScore}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Status</div>
                        <div class="detail-value">
                            <span class="status-badge" style="color: ${getStatusColor(exam.status)}; border: 1px solid ${getStatusColor(exam.status)};">
                                <span class="status-dot"></span>
                                ${exam.status}
                            </span>
                        </div>
                    </div>
                    ${exam.score ? `
                    <div class="detail-item">
                        <div class="detail-label">Your Score</div>
                        <div class="detail-value" style="color: ${getScoreColor(exam.score, exam.passingScore)};">
                            ${exam.score}
                        </div>
                    </div>
                    ` : ''}
                </div>
                
                ${exam.examInstructions ? `
                <div class="exam-instructions">
                    <h3>Exam Instructions</h3>
                    <p>${exam.examInstructions}</p>
                </div>
                ` : ''}
                
                ${exam.examResults ? `
                <div class="exam-results">
                    <h3>Exam Results</h3>
                    <p>${exam.examResults}</p>
                </div>
                ` : ''}
                
                <div class="card-actions">
                    ${isScheduled ? `
                    <button class="btn btn-primary start-exam-btn" data-id="${exam.id}">
                        <i class="fas fa-play"></i>
                        Start Exam
                    </button>
                    ` : `
                    <button class="btn btn-secondary" onclick="window.location.href='exam_results.php?exam_id=${exam.id}'">
                        <i class="fas fa-chart-bar"></i>
                        View Results
                    </button>
                    `}
                    <button class="btn btn-secondary" onclick="window.location.href='mailto:contact@${exam.company.toLowerCase().replace(' ', '')}.com?subject=Question about ${exam.examTitle}'">
                        <i class="fas fa-envelope"></i>
                        Contact Support
                    </button>
                </div>
            `;
            
            detailsModal.style.display = 'flex';
        }
        
        // Confirm start exam function
        function confirmStartExam(examId) {
            const exam = scheduledExams.find(exam => exam.id === examId);
            
            if (!exam) return;
            
            // Start exam directly without system check
            startExam(examId);
        }
        
        // Start exam function
        function startExam(examId) {
            const exam = scheduledExams.find(exam => exam.id === examId);
            
            if (!exam) {
                console.error('Exam not found:', examId);
                showToast('Exam not found. Please refresh the page.', 'error');
                return;
            }
            
            // Check if assignment ID exists
            if (!exam.assignmentId) {
                console.error('Assignment ID missing for exam:', examId);
                showToast('Exam assignment information is missing. Please contact support.', 'error');
                return;
            }
            
            // Add loading state to button
            const startBtn = document.querySelector(`.start-exam-btn[data-id="${examId}"]`);
            if (startBtn) {
                const originalContent = startBtn.innerHTML;
                startBtn.innerHTML = '<span class="loading-indicator"></span> Starting...';
                startBtn.disabled = true;
            }
            
            // Close modal if open
            if (detailsModal) {
                detailsModal.style.display = 'none';
            }
            
            // Redirect to exam taking page
            try {
                window.location.href = `take_exam.php?exam_id=${examId}&assignment_id=${exam.assignmentId}`;
            } catch (error) {
                console.error('Error redirecting to exam:', error);
                showToast('Unable to start exam. Please try again.', 'error');
                
                // Restore button state
                if (startBtn) {
                    startBtn.innerHTML = originalContent;
                    startBtn.disabled = false;
                }
            }
        }
        
        // Review exam function
        
        // Show toast notification function
        function showToast(message, type = 'info') {
            const toastEl = document.getElementById('toast');
            const toastMessage = toastEl.querySelector('.toast-message');
            const toastIcon = toastEl.querySelector('.toast-icon');
            
            // Set message
            toastMessage.textContent = message;
            
            // Set type and icon
            toastEl.className = 'toast show';
            toastEl.classList.add(type);
            
            switch (type) {
                case 'success':
                    toastIcon.className = 'toast-icon fas fa-check-circle';
                    break;
                case 'error':
                    toastIcon.className = 'toast-icon fas fa-exclamation-circle';
                    break;
                case 'info':
                default:
                    toastIcon.className = 'toast-icon fas fa-info-circle';
                    break;
            }
            
            // Hide toast after 3 seconds
            setTimeout(() => {
                toastEl.classList.remove('show');
            }, 3000);
        }
        
        // Helper function to get status color (replicated from PHP for JS use)
        function getStatusColor(status) {
            switch (status) {
                case 'Scheduled':
                    return '#f59e0b';
                case 'Completed':
                    return '#3fb950';
                case 'Failed':
                    return '#f85149';
                default:
                    return '#8b949e';
            }
        }
        
        // Helper function to get score color (replicated from PHP for JS use)
        function getScoreColor(score, passingScore) {
            // Remove % symbol if present
            const scoreValue = parseInt(score.toString().replace('%', ''));
            const passingValue = parseInt(passingScore.toString().replace('%', ''));
            
            return scoreValue >= passingValue ? '#3fb950' : '#f85149';
        }
        // Logout functionality parity with other pages
        (function(){
            var btn = document.getElementById('logoutBtn');
            if (btn) {
                btn.addEventListener('click', function(){
                    window.location.href = 'Login&Signup.php';
                });
            }
        })();

        // Theme Management
        function initializeTheme() {
            // Get saved theme or default to dark
            const savedTheme = localStorage.getItem('candihire-theme') || 'dark';
            applyTheme(savedTheme);
            updateThemeButton(savedTheme);
        }

        function applyTheme(theme) {
            document.documentElement.setAttribute('data-theme', theme);
            localStorage.setItem('candihire-theme', theme);
        }

        function toggleTheme() {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            applyTheme(newTheme);
            updateThemeButton(newTheme);
            
            // Add smooth transition effect
            document.body.style.transition = 'background-color 0.3s ease, color 0.3s ease';
            setTimeout(() => {
                document.body.style.transition = '';
            }, 300);
        }

        function updateThemeButton(theme) {
            const themeIcon = document.getElementById('themeIcon');
            const themeText = document.getElementById('themeText');
            const themeToggleBtn = document.getElementById('themeToggleBtn');
            
            // Add theme data attribute for CSS styling
            if (themeToggleBtn) {
                themeToggleBtn.setAttribute('data-theme', theme);
            }
            
            if (theme === 'dark') {
                themeIcon.className = 'fas fa-moon-stars';
                themeText.textContent = 'Light Mode';
                if (themeToggleBtn) {
                    themeToggleBtn.title = 'Switch to Light Mode';
                }
            } else {
                themeIcon.className = 'fas fa-moon-stars';
                themeText.textContent = 'Dark Mode';
                if (themeToggleBtn) {
                    themeToggleBtn.title = 'Switch to Dark Mode';
                }
            }
        }

        function setupThemeToggle() {
            const themeToggleBtn = document.getElementById('themeToggleBtn');
            if (themeToggleBtn) {
                themeToggleBtn.addEventListener('click', toggleTheme);
            }
        }

        // Initialize theme when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            initializeTheme();
            setupThemeToggle();
            setupProfileEditing();
            
            // Check for newly assigned exams and show notification
            <?php if ($newlyAssignedCount > 0): ?>
            setTimeout(function() {
                showToast('You have <?php echo $newlyAssignedCount; ?> newly assigned exam<?php echo $newlyAssignedCount > 1 ? 's' : ''; ?>! Check the scheduled exams section.', 'info');
            }, 2000);
            <?php endif; ?>
        });
    </script>
</body>
</html>
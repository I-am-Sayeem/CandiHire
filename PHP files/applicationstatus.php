<?php
// Application Status functionality
require_once 'session_manager.php';

// Check if candidate is logged in
if (!isCandidateLoggedIn()) {
    header('Location: Login&Signup.php');
    exit;
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
    
    header('Content-Type: application/json');
    
    switch ($action) {
        case 'check_updates':
            checkForUpdates();
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
    exit;
}





function checkForUpdates() {
    global $pdo, $sessionCandidateId;
    
    try {
        if (!isset($pdo) || !($pdo instanceof PDO)) {
            echo json_encode(['success' => false, 'message' => 'Database connection error']);
            return;
        }
        
        // Check for recent status changes in the last 5 minutes
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as updateCount
            FROM application_status_history ash
            JOIN job_applications ja ON ash.ApplicationID = ja.ApplicationID
            WHERE ja.CandidateID = ? 
            AND ash.StatusDate >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)
            AND ash.UpdatedBy != 'Candidate'
        ");
        $stmt->execute([$sessionCandidateId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $hasUpdates = $result['updateCount'] > 0;
        
        echo json_encode(['success' => true, 'hasUpdates' => $hasUpdates]);
        
    } catch (Exception $e) {
        error_log("Error checking for updates: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to check for updates']);
    }
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
    }
} catch (Exception $e) {
    error_log("Error loading candidate profile picture: " . $e->getMessage());
}

// Fetch real application data from database
$applications = [];
$statusHistory = [];

try {
    if (isset($pdo) && $pdo instanceof PDO) {
        // Get all applications for the current candidate with job and company details
        // Using GROUP BY to ensure no duplicates even if there are multiple related records
        $stmt = $pdo->prepare("
            SELECT 
                ja.ApplicationID as id,
                ja.JobID as JobID,
                ja.ApplicationDate as applicationDate,
                ja.Status as status,
                ja.CoverLetter as coverLetter,
                ja.Notes as notes,
                ja.ContactPerson as contactPerson,
                ja.ContactEmail as contactEmail,
                ja.SalaryExpectation as salaryExpectation,
                ja.AvailabilityDate as availabilityDate,
                jp.JobTitle as jobTitle,
                jp.JobDescription as jobDescription,
                jp.Requirements as requirements,
                jp.Responsibilities as responsibilities,
                jp.Location as location,
                jp.JobType as jobType,
                jp.SalaryMin as salaryMin,
                jp.SalaryMax as salaryMax,
                jp.Currency as currency,
                cli.CompanyName as company,
                cli.Email as companyEmail,
                cli.PhoneNumber as companyPhone,
                cli.Address as companyAddress,
                cli.City as companyCity,
                cli.State as companyState,
                cli.Country as companyCountry,
                cli.Website as companyWebsite,
                cli.Logo as companyLogo,
                cli_candidate.Education as candidateEducation,
                cli_candidate.Institute as candidateInstitute,
                cli_candidate.Skills as candidateSkills,
                cli_candidate.Summary as candidateSummary
            FROM job_applications ja
            JOIN job_postings jp ON ja.JobID = jp.JobID
            JOIN Company_login_info cli ON jp.CompanyID = cli.CompanyID
            JOIN candidate_login_info cli_candidate ON ja.CandidateID = cli_candidate.CandidateID
            WHERE ja.CandidateID = ?
            GROUP BY ja.ApplicationID
            ORDER BY ja.ApplicationDate DESC
        ");
        $stmt->execute([$sessionCandidateId]);
        $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Remove any potential duplicates by ApplicationID (safety measure)
        $uniqueApplications = [];
        $seenIds = [];
        
        foreach ($applications as $application) {
            if (!in_array($application['id'], $seenIds)) {
                $uniqueApplications[] = $application;
                $seenIds[] = $application['id'];
            } else {
                // Log duplicate found for debugging
                error_log("Duplicate application found: ApplicationID " . $application['id'] . " for candidate " . $sessionCandidateId);
            }
        }
        
        // Log duplicate count for debugging
        $duplicateCount = count($applications) - count($uniqueApplications);
        if ($duplicateCount > 0) {
            error_log("Removed " . $duplicateCount . " duplicate applications for candidate " . $sessionCandidateId);
        }
        
        $applications = $uniqueApplications;
        
        // Get status history for each application
        if (!empty($applications)) {
            $applicationIds = array_column($applications, 'id');
            $placeholders = str_repeat('?,', count($applicationIds) - 1) . '?';
            
            $historyStmt = $pdo->prepare("
                SELECT 
                    ApplicationID,
                    Status as status,
                    StatusDate as date,
                    Notes as notes,
                    UpdatedBy as updatedBy
                FROM application_status_history
                WHERE ApplicationID IN ($placeholders)
                ORDER BY StatusDate ASC
            ");
            $historyStmt->execute($applicationIds);
            $historyData = $historyStmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Group status history by application ID
            foreach ($historyData as $history) {
                $statusHistory[$history['ApplicationID']][] = [
                    'status' => $history['status'],
                    'date' => $history['date'],
                    'notes' => $history['notes'],
                    'updatedBy' => $history['updatedBy']
                ];
            }
        }
        
        // Process applications to add status history and format data
        foreach ($applications as &$application) {
            // Add status history
            $application['statusHistory'] = $statusHistory[$application['id']] ?? [];
            
            // Determine comprehensive application status based on the flow
            $application = determineApplicationStatus($application, $sessionCandidateId);
            
            // Format salary range
            if ($application['salaryMin'] && $application['salaryMax']) {
                $application['salaryRange'] = $application['currency'] . ' ' . 
                    number_format($application['salaryMin']) . ' - ' . 
                    number_format($application['salaryMax']);
            } else {
                $application['salaryRange'] = 'Not specified';
            }
            
            // Set contact person and email if not set
            if (empty($application['contactPerson'])) {
                $application['contactPerson'] = 'HR Department';
            }
            if (empty($application['contactEmail'])) {
                $application['contactEmail'] = $application['companyEmail'];
            }
        }
        
        error_log("Loaded " . count($applications) . " applications for candidate " . $sessionCandidateId);
    }
} catch (Exception $e) {
    error_log("Error loading applications: " . $e->getMessage());
    $applications = [];
}


// Function to determine comprehensive application status
function determineApplicationStatus($application, $candidateId) {
    global $pdo;
    
    try {
        $applicationId = $application['id'];
        $jobId = $application['JobID'] ?? null;
        
        // Initialize status flow
        $statusFlow = [];
        $currentStatus = 'submitted';
        $isCompanyInvited = false;
        
        // 1. Check if this is a company invitation (different from candidate application)
        if (!$pdo) {
            return $application;
        }
        
        $inviteCheck = $pdo->prepare("
            SELECT COUNT(*) as invite_count 
            FROM application_status_history 
            WHERE ApplicationID = ? AND Status = 'company_invited' 
            AND UpdatedBy != 'Candidate'
        ");
        $inviteCheck->execute([$applicationId]);
        $inviteResult = $inviteCheck->fetch(PDO::FETCH_ASSOC);
        
        if ($inviteResult['invite_count'] > 0) {
            $isCompanyInvited = true;
            $currentStatus = 'company_invited';
            $statusFlow[] = [
                'status' => 'Company Invited',
                'date' => $application['applicationDate'],
                'notes' => 'Company invited you for this position',
                'updatedBy' => 'Company',
                'completed' => true
            ];
        } else {
            $statusFlow[] = [
                'status' => 'Applied',
                'date' => $application['applicationDate'],
                'notes' => 'Application submitted successfully',
                'updatedBy' => 'Candidate',
                'completed' => true
            ];
        }
        
        // 2. Check for MCQ Exam Status - Always check if exam is assigned
        $examStatus = checkMCQExamStatus($applicationId, $candidateId, $jobId);
        if ($examStatus) {
            $statusFlow[] = $examStatus;
            $currentStatus = $examStatus['status_key'];
            
            // Add post-exam status based on exam result
            if ($examStatus['status_key'] === 'exam_passed') {
                // Check if interview is already scheduled
                $interviewCheck = $pdo->prepare("
                    SELECT InterviewID, ScheduledDate, Status 
                    FROM interviews 
                    WHERE CandidateID = ? AND JobID = ?
                    ORDER BY ScheduledDate DESC 
                    LIMIT 1
                ");
                $interviewCheck->execute([$candidateId, $jobId]);
                $interviewResult = $interviewCheck->fetch(PDO::FETCH_ASSOC);
                
                if (!$interviewResult) {
                    // No interview scheduled yet, add waiting status
                    $statusFlow[] = [
                        'status' => 'Waiting for Interview Call',
                        'date' => $examStatus['date'],
                        'notes' => 'MCQ Exam completed successfully - Awaiting interview scheduling',
                        'updatedBy' => 'System',
                        'completed' => false,
                        'status_key' => 'waiting_interview'
                    ];
                    $currentStatus = 'waiting_interview';
                }
            } else if ($examStatus['status_key'] === 'exam_failed') {
                // Add rejected status after failed exam
                $statusFlow[] = [
                    'status' => 'Rejected',
                    'date' => $examStatus['date'],
                    'notes' => 'Application rejected due to failed MCQ exam',
                    'updatedBy' => 'System',
                    'completed' => true,
                    'status_key' => 'rejected'
                ];
                $currentStatus = 'rejected';
            }
        } else {
            // Check if there's an exam assignment even without schedule
            $assignmentCheck = $pdo->prepare("
                SELECT ea.AssignmentID, ea.AssignmentDate, e.ExamTitle
                FROM exam_assignments ea
                JOIN exams e ON ea.ExamID = e.ExamID
                WHERE ea.CandidateID = ? AND ea.JobID = ?
                LIMIT 1
            ");
            $assignmentCheck->execute([$candidateId, $jobId]);
            $assignmentResult = $assignmentCheck->fetch(PDO::FETCH_ASSOC);
            
            if ($assignmentResult) {
                $statusFlow[] = [
                    'status' => 'MCQ Exam Assigned',
                    'date' => $assignmentResult['AssignmentDate'],
                    'notes' => $assignmentResult['ExamTitle'] . ' - Assignment created',
                    'updatedBy' => 'System',
                    'completed' => false,
                    'status_key' => 'exam_assigned'
                ];
                $currentStatus = 'exam_assigned';
            }
        }
        
        // 3. Check for Interview Status
        $interviewStatus = checkInterviewStatus($applicationId, $candidateId, $jobId);
        if ($interviewStatus) {
            $statusFlow[] = $interviewStatus;
            $currentStatus = $interviewStatus['status_key'];
        }
        
        // Update application with comprehensive status
        $application['statusFlow'] = $statusFlow;
        $application['currentStatus'] = $currentStatus;
        $application['isCompanyInvited'] = $isCompanyInvited;
        
        return $application;
        
    } catch (Exception $e) {
        error_log("Error determining application status: " . $e->getMessage());
        return $application;
    }
}

// Function to check MCQ Exam Status
function checkMCQExamStatus($applicationId, $candidateId, $jobId) {
    global $pdo;
    
    try {
        if (!$pdo) {
            return null;
        }
        
        // First check if there's an exam assignment
        $assignmentCheck = $pdo->prepare("
            SELECT ea.AssignmentID, ea.ExamID, ea.AssignmentDate, ea.DueDate, ea.Status as AssignmentStatus,
                   ea.Score, ea.CompletedAt, e.ExamTitle, e.PassingScore, e.Duration
            FROM exam_assignments ea
            JOIN exams e ON ea.ExamID = e.ExamID
            WHERE ea.CandidateID = ? AND ea.JobID = ?
            ORDER BY ea.AssignmentDate DESC
            LIMIT 1
        ");
        $assignmentCheck->execute([$candidateId, $jobId]);
        $assignmentResult = $assignmentCheck->fetch(PDO::FETCH_ASSOC);
        
        if ($assignmentResult) {
            // First check if the assignment itself is completed (direct completion status)
            if ($assignmentResult['AssignmentStatus'] === 'completed') {
                $score = $assignmentResult['Score'] ?? 0;
                $passingScore = $assignmentResult['PassingScore'];
                $completedAt = $assignmentResult['CompletedAt'] ?? $assignmentResult['AssignmentDate'];
                
                if ($score >= $passingScore) {
                    return [
                        'status' => 'MCQ Exam Passed',
                        'date' => $completedAt,
                        'notes' => "Score: {$score}% (Passing: {$passingScore}%)",
                        'updatedBy' => 'System',
                        'completed' => true,
                        'status_key' => 'exam_passed'
                    ];
                } else {
                    // Failed exam - application should be rejected
                    updateApplicationStatus($applicationId, 'rejected', 'Failed MCQ exam');
                    return [
                        'status' => 'MCQ Exam Failed',
                        'date' => $completedAt,
                        'notes' => "Score: {$score}% (Required: {$passingScore}%)",
                        'updatedBy' => 'System',
                        'completed' => true,
                        'status_key' => 'exam_failed'
                    ];
                }
            }
            
            // Check if there's an exam schedule for this assignment
            $scheduleCheck = $pdo->prepare("
                SELECT es.ScheduleID, es.Status as ScheduleStatus, es.ScheduledDate, es.ScheduledTime,
                       ea_attempt.Score, ea_attempt.Status as AttemptStatus
                FROM exam_schedules es
                LEFT JOIN exam_attempts ea_attempt ON es.ScheduleID = ea_attempt.ScheduleID
                WHERE es.ExamID = ? AND es.CandidateID = ? AND es.JobID = ?
                ORDER BY es.ScheduledDate DESC
                LIMIT 1
            ");
            $scheduleCheck->execute([$assignmentResult['ExamID'], $candidateId, $jobId]);
            $scheduleResult = $scheduleCheck->fetch(PDO::FETCH_ASSOC);
            
            if ($scheduleResult) {
                // Exam is scheduled, check the attempt status
                if ($scheduleResult['AttemptStatus'] === 'completed') {
                    // Exam is finished, check if passed or failed
                    $score = $scheduleResult['Score'] ?? 0;
                    $passingScore = $assignmentResult['PassingScore'];
                    
                    if ($score >= $passingScore) {
                        return [
                            'status' => 'MCQ Exam Passed',
                            'date' => date('Y-m-d H:i:s'),
                            'notes' => "Score: {$score}% (Passing: {$passingScore}%)",
                            'updatedBy' => 'System',
                            'completed' => true,
                            'status_key' => 'exam_passed'
                        ];
                    } else {
                        // Failed exam - application should be rejected
                        updateApplicationStatus($applicationId, 'rejected', 'Failed MCQ exam');
                        return [
                            'status' => 'MCQ Exam Failed',
                            'date' => date('Y-m-d H:i:s'),
                            'notes' => "Score: {$score}% (Required: {$passingScore}%)",
                            'updatedBy' => 'System',
                            'completed' => true,
                            'status_key' => 'exam_failed'
                        ];
                    }
                } else if ($scheduleResult['AttemptStatus'] === 'in-progress') {
                    return [
                        'status' => 'MCQ Exam In Progress',
                        'date' => $scheduleResult['ScheduledDate'],
                        'notes' => 'Currently taking the exam',
                        'updatedBy' => 'System',
                        'completed' => false,
                        'status_key' => 'exam_in_progress'
                    ];
                } else {
                    return [
                        'status' => 'MCQ Exam Assigned',
                        'date' => $scheduleResult['ScheduledDate'],
                        'notes' => $assignmentResult['ExamTitle'],
                        'updatedBy' => 'System',
                        'completed' => false,
                        'status_key' => 'exam_assigned'
                    ];
                }
            } else {
                // Exam is assigned but not yet scheduled - create a schedule
                $scheduledDate = date('Y-m-d', strtotime('+1 day'));
                $scheduledTime = '10:00:00';
                
                try {
                    $scheduleStmt = $pdo->prepare("
                        INSERT INTO exam_schedules (ExamID, CandidateID, JobID, ScheduledDate, ScheduledTime, Status, Duration)
                        VALUES (?, ?, ?, ?, ?, 'scheduled', ?)
                    ");
                    $scheduleStmt->execute([
                        $assignmentResult['ExamID'],
                        $candidateId,
                        $jobId,
                        $scheduledDate,
                        $scheduledTime,
                        $assignmentResult['Duration']
                    ]);
                    
                    return [
                        'status' => 'MCQ Exam Assigned',
                        'date' => $scheduledDate,
                        'notes' => $assignmentResult['ExamTitle'] . ' - Scheduled for ' . date('M j, Y', strtotime($scheduledDate)),
                        'updatedBy' => 'System',
                        'completed' => false,
                        'status_key' => 'exam_assigned'
                    ];
                } catch (Exception $e) {
                    error_log("Error creating exam schedule: " . $e->getMessage());
                    return [
                        'status' => 'MCQ Exam Assigned',
                        'date' => $assignmentResult['AssignmentDate'],
                        'notes' => $assignmentResult['ExamTitle'] . ' - Scheduling in progress',
                        'updatedBy' => 'System',
                        'completed' => false,
                        'status_key' => 'exam_assigned'
                    ];
                }
            }
        }
        
        return null;
        
    } catch (Exception $e) {
        error_log("Error checking MCQ exam status: " . $e->getMessage());
        return null;
    }
}

// Function to check Interview Status
function checkInterviewStatus($applicationId, $candidateId, $jobId) {
    global $pdo;
    
    try {
        if (!$pdo) {
            return null;
        }
        
        // Check if interview is scheduled
        $interviewCheck = $pdo->prepare("
            SELECT InterviewID, InterviewTitle, ScheduledDate, ScheduledTime, 
                   Status, InterviewType, InterviewMode
            FROM interviews
            WHERE CandidateID = ? AND JobID = ?
            ORDER BY ScheduledDate DESC, ScheduledTime DESC
            LIMIT 1
        ");
        $interviewCheck->execute([$candidateId, $jobId]);
        $interviewResult = $interviewCheck->fetch(PDO::FETCH_ASSOC);
        
        if ($interviewResult) {
            $status = strtolower($interviewResult['Status']);
            
            if ($status === 'completed') {
                return [
                    'status' => 'Interview Completed',
                    'date' => $interviewResult['ScheduledDate'],
                    'notes' => $interviewResult['InterviewTitle'] . ' - Awaiting feedback',
                    'updatedBy' => 'Company',
                    'completed' => true,
                    'status_key' => 'interview_completed'
                ];
            } else if ($status === 'in-progress') {
                return [
                    'status' => 'Interview In Progress',
                    'date' => $interviewResult['ScheduledDate'],
                    'notes' => $interviewResult['InterviewTitle'],
                    'updatedBy' => 'Company',
                    'completed' => false,
                    'status_key' => 'interview_in_progress'
                ];
            } else if ($status === 'scheduled') {
                return [
                    'status' => 'Called for Interview',
                    'date' => $interviewResult['ScheduledDate'],
                    'notes' => $interviewResult['InterviewTitle'] . ' - ' . $interviewResult['InterviewMode'],
                    'updatedBy' => 'Company',
                    'completed' => false,
                    'status_key' => 'interview_scheduled'
                ];
            }
        }
        
        return null;
        
    } catch (Exception $e) {
        error_log("Error checking interview status: " . $e->getMessage());
        return null;
    }
}

// Function to update application status
function updateApplicationStatus($applicationId, $status, $notes) {
    global $pdo;
    
    try {
        if (!$pdo) {
            return;
        }
        
        // Update main application status
        $updateStmt = $pdo->prepare("UPDATE job_applications SET Status = ?, UpdatedAt = NOW() WHERE ApplicationID = ?");
        $updateStmt->execute([$status, $applicationId]);
        
        // Add to status history
        $historyStmt = $pdo->prepare("
            INSERT INTO application_status_history (ApplicationID, Status, StatusDate, Notes, UpdatedBy) 
            VALUES (?, ?, NOW(), ?, 'System')
        ");
        $historyStmt->execute([$applicationId, $status, $notes]);
        
    } catch (Exception $e) {
        error_log("Error updating application status: " . $e->getMessage());
    }
}

// Function to get status color
function getStatusColor($status) {
    switch (strtolower($status)) {
        case 'submitted':
        case 'application submitted':
        case 'applied':
            return '#8b949e';
        case 'company_invited':
        case 'company invited':
            return '#79c0ff';
        case 'exam_assigned':
        case 'mcq exam assigned':
            return '#58a6ff';
        case 'exam_in_progress':
        case 'mcq exam in progress':
            return '#f59e0b';
        case 'exam_passed':
        case 'mcq exam passed':
            return '#3fb950';
        case 'waiting_interview':
        case 'waiting for interview call':
            return '#f59e0b';
        case 'exam_failed':
        case 'mcq exam failed':
        case 'rejected':
            return '#f85149';
        case 'interview_scheduled':
        case 'called for interview':
            return '#f59e0b';
        case 'interview_in_progress':
            return '#f59e0b';
        case 'interview_completed':
            return '#3fb950';
        case 'under-review':
        case 'under review':
        case 'in review':
            return '#58a6ff';
        case 'shortlisted':
            return '#79c0ff';
        case 'interview-scheduled':
        case 'interview scheduled':
            return '#f59e0b';
        case 'interviewed':
            return '#f59e0b';
        case 'offer-extended':
        case 'offer extended':
            return '#3fb950';
        case 'accepted':
            return '#3fb950';
        case 'withdrawn':
            return '#f85149';
        default:
            return '#8b949e';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Status - CandiHire</title>
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

        /* Main Content */
        .main-content {
            flex: 1;
            padding: 20px;
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

        .search-bar {
            position: relative;
            flex: 1;
            max-width: 400px;
        }

        .search-input {
            width: 100%;
            background-color: var(--bg-secondary);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 10px 40px 10px 15px;
            color: var(--text-primary);
            font-size: 14px;
        }

        .search-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
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

        .btn-danger {
            background-color: var(--danger);
            color: white;
        }

        .btn-danger:hover {
            background-color: #da3633;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(248, 81, 73, 0.3);
        }

        /* Application Cards */
        .applications-container {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .application-card {
            background-color: var(--bg-secondary);
            border-radius: 12px;
            border: 1px solid var(--border);
            overflow: hidden;
            transition: all 0.3s ease;
            will-change: transform, box-shadow;
        }

        .application-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.25);
        }

        .card-header {
            padding: 20px;
            border-bottom: 1px solid var(--border);
        }

        .job-title {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 6px;
            color: var(--text-primary);
        }

        .company-name {
            font-size: 16px;
            color: var(--text-secondary);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .card-body {
            padding: 20px;
        }

        .application-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
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

        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
            gap: 6px;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: currentColor;
        }

        .status-history {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid var(--border);
        }

        .status-history-title {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 15px;
            color: var(--text-primary);
        }

        .timeline {
            position: relative;
            padding-left: 30px;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 8px;
            top: 0;
            height: 100%;
            width: 2px;
            background-color: var(--border);
        }

        .timeline-item {
            position: relative;
            margin-bottom: 20px;
        }

        .timeline-item:last-child {
            margin-bottom: 0;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -30px;
            top: 6px;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background-color: var(--bg-primary);
            border: 2px solid;
            z-index: 1;
        }

        .timeline-content {
            background-color: var(--bg-primary);
            border-radius: 8px;
            padding: 12px 16px;
            border: 1px solid var(--border);
        }

        /* Blinking animation for current/pending status */
        .timeline-item.current-status::before {
            animation: statusBlink 2s infinite;
            background-color: #58a6ff;
            border-color: #58a6ff;
        }

        /* Stable status for completed/final statuses */
        .timeline-item.completed-status::before {
            animation: none;
        }

        /* Stable status for rejected (final status) */
        .timeline-item.rejected-status::before {
            animation: none;
            background-color: #f85149;
            border-color: #f85149;
        }

        @keyframes statusBlink {
            0%, 50% {
                opacity: 1;
                transform: scale(1);
            }
            25%, 75% {
                opacity: 0.3;
                transform: scale(0.8);
            }
        }

        .timeline-status {
            font-weight: 600;
            margin-bottom: 4px;
        }

        .timeline-date {
            font-size: 13px;
            color: var(--text-secondary);
        }

        .card-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid var(--border);
        }

        /* Application Details Modal */
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

        .candidate-info {
            margin-top: 20px;
            padding: 20px;
            background-color: var(--bg-secondary);
            border-radius: 8px;
            border: 1px solid var(--border);
        }

        .candidate-info h3 {
            color: var(--text-primary);
            margin-bottom: 15px;
            font-size: 16px;
            font-weight: 600;
        }

        .detail-item.full-width {
            grid-column: 1 / -1;
        }

        .job-description {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid var(--border);
        }

        .job-description h3 {
            margin-bottom: 10px;
            color: var(--text-primary);
        }

        /* Right Sidebar */
        .right-sidebar {
            width: 320px;
            padding: 20px;
            border-left: 1px solid var(--border);
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
            transition: transform 0.3s ease;
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

        .sidebar-section {
            background-color: var(--bg-secondary);
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 20px;
            border: 1px solid var(--border);
        }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--border);
            color: var(--text-primary);
        }

        .tip-item {
            padding: 12px 0;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .tip-item:last-child {
            border-bottom: none;
        }

        .tip-icon {
            color: var(--accent);
            margin-top: 2px;
        }

        .tip-content h4 {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .tip-content p {
            font-size: 13px;
            color: var(--text-secondary);
            line-height: 1.4;
        }

        /* Filter Section */
        .filter-group {
            margin-bottom: 15px;
        }

        .filter-label {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text-primary);
        }

        .filter-select {
            width: 100%;
            background-color: var(--bg-primary);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 10px 12px;
            color: var(--text-primary);
            font-size: 14px;
        }

        /* Sort Options */
        .sort-options {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .sort-btn {
            padding: 8px 15px;
            background-color: var(--bg-secondary);
            border: 1px solid var(--border);
            border-radius: 6px;
            color: var(--text-secondary);
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .sort-btn:hover {
            color: var(--text-primary);
            border-color: var(--accent);
        }

        .sort-btn.active {
            background-color: var(--bg-tertiary);
            color: var(--accent);
            border-color: var(--accent);
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

        /* Job Listings for New Application Modal */
        .job-listings {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .job-listing-card {
            background-color: var(--bg-primary);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 15px;
            transition: all 0.2s;
        }

        .job-listing-card:hover {
            border-color: var(--accent);
        }

        .job-listing-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .job-listing-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--text-primary);
        }

        .job-listing-company {
            font-size: 14px;
            color: var(--text-secondary);
            margin-bottom: 5px;
        }

        .job-listing-details {
            display: flex;
            gap: 15px;
            margin-bottom: 10px;
            flex-wrap: wrap;
        }

        .job-listing-detail {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 14px;
            color: var(--text-secondary);
        }

        .job-listing-description {
            font-size: 14px;
            color: var(--text-secondary);
            margin-bottom: 15px;
            line-height: 1.4;
        }

        .job-listing-actions {
            display: flex;
            justify-content: flex-end;
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
            .right-sidebar {
                position: fixed;
                right: 0;
                top: 0;
                height: 100vh;
                z-index: 999;
                transform: translateX(100%);
            }
            
            .right-sidebar.show {
                transform: translateX(0);
            }
            
            .overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 998;
            }
            
            .overlay.show {
                display: block;
            }
        }

        @media (max-width: 768px) {
            .left-nav {
                display: none;
            }
            .main-content {
                padding: 10px;
            }
            .page-header {
                flex-direction: column;
                gap: 16px;
                align-items: stretch;
            }
            .header-actions {
                flex-direction: column;
            }
            .search-bar {
                max-width: 100%;
            }
            .application-details {
                flex-direction: column;
            }
            .card-actions {
                flex-direction: column;
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
                <a href="applicationstatus.php" class="nav-item active">
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
                <a href="attendexam.php" class="nav-item">
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
                <h1 class="page-title">Recent Applications</h1>
                <div class="header-actions">
                    <div class="search-bar">
                        <input type="text" class="search-input" placeholder="Search applications..." id="searchInput">
                        <i class="fas fa-search search-icon"></i>
                    </div>
                </div>
            </div>

            <!-- Sort Options -->
            <div class="sort-options">
                <button class="sort-btn active" data-sort="date" data-order="desc">Newest First</button>
                <button class="sort-btn" data-sort="date" data-order="asc">Oldest First</button>
                <button class="sort-btn" data-sort="status" data-order="asc">Status (A-Z)</button>
                <button class="sort-btn" data-sort="company" data-order="asc">Company (A-Z)</button>
            </div>

            <!-- Applications Container -->
            <div class="applications-container" id="applicationsContainer">
                <?php foreach ($applications as $application): ?>
                <div class="application-card" data-id="<?php echo $application['id']; ?>" data-status="<?php echo strtolower($application['status']); ?>" data-company="<?php echo strtolower($application['company']); ?>" data-date="<?php echo $application['applicationDate']; ?>">
                    <div class="card-header">
                        <h2 class="job-title"><?php echo $application['jobTitle']; ?></h2>
                        <div class="company-name">
                            <i class="fas fa-building"></i>
                            <?php echo $application['company']; ?>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="application-details">
                            <div class="detail-item">
                                <div class="detail-label">Application Date</div>
                                <div class="detail-value"><?php echo date('F j, Y', strtotime($application['applicationDate'])); ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Salary Range</div>
                                <div class="detail-value"><?php echo $application['salaryRange']; ?></div>
                            </div>
                            <?php if (isset($application['isCompanyInvited']) && $application['isCompanyInvited']): ?>
                            <div class="detail-item">
                                <div class="detail-label">Type</div>
                                <div class="detail-value" style="color: #79c0ff; font-weight: 600;">
                                    <i class="fas fa-handshake"></i> Company Invitation
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($application['statusFlow'])): ?>
                        <div class="status-history">
                            <h3 class="status-history-title">Application Progress</h3>
                            <div class="timeline">
                                <?php foreach ($application['statusFlow'] as $index => $status): ?>
                                <?php 
                                // Determine CSS class based on status
                                $timelineClass = 'timeline-item';
                                if ($status['status_key'] === 'rejected') {
                                    $timelineClass .= ' rejected-status';
                                } elseif ($status['completed']) {
                                    $timelineClass .= ' completed-status';
                                } else {
                                    $timelineClass .= ' current-status';
                                }
                                ?>
                                <div class="<?php echo $timelineClass; ?>">
                                    <div class="timeline-content" style="position: relative;">
                                        <div class="timeline-status" style="color: <?php echo getStatusColor($status['status']); ?>; display: flex; align-items: center; gap: 8px;">
                                            <?php if ($status['completed']): ?>
                                                <i class="fas fa-check-circle" style="color: #3fb950;"></i>
                                            <?php else: ?>
                                                <i class="fas fa-clock" style="color: #f59e0b;"></i>
                                            <?php endif; ?>
                                            <?php echo $status['status']; ?>
                                        </div>
                                        <div class="timeline-date"><?php echo date('F j, Y', strtotime($status['date'])); ?></div>
                                        <?php if (!empty($status['notes'])): ?>
                                        <div class="timeline-notes" style="font-size: 12px; color: var(--text-secondary); margin-top: 4px;">
                                            <?php echo htmlspecialchars($status['notes']); ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="card-actions">
                            <button class="btn btn-primary view-details-btn" data-id="<?php echo $application['id']; ?>">
                                <i class="fas fa-eye"></i>
                                View Details
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Right Sidebar -->
        <div class="right-sidebar" id="rightSidebar">
            <!-- Filters Section -->
            <div class="sidebar-section">
                <div class="section-title">Filter Applications</div>
                <div class="filter-group">
                    <label class="filter-label">Status</label>
                    <select class="filter-select" id="statusFilter">
                        <option value="">All Statuses</option>
                        <option value="application submitted">Application Submitted</option>
                        <option value="under review">Under Review</option>
                        <option value="in review">In Review</option>
                        <option value="interview scheduled">Interview Scheduled</option>
                        <option value="offer extended">Offer Extended</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label">Date Range</label>
                    <select class="filter-select" id="dateFilter">
                        <option value="">All Time</option>
                        <option value="7">Last 7 Days</option>
                        <option value="30">Last 30 Days</option>
                        <option value="90">Last 90 Days</option>
                    </select>
                </div>
                <button class="btn btn-primary" id="applyFiltersBtn" style="width: 100%; margin-top: 10px;">
                    Apply Filters
                </button>
                <button class="btn btn-secondary" id="clearFiltersBtn" style="width: 100%; margin-top: 10px;">
                    Clear Filters
                </button>
            </div>


            <!-- Application Tips -->
            <div class="sidebar-section">
                <div class="section-title">Application Tips</div>
                <div class="tip-item">
                    <i class="fas fa-lightbulb tip-icon"></i>
                    <div class="tip-content">
                        <h4>Follow Up</h4>
                        <p>Send a polite follow-up email if you haven't heard back within 7-10 days.</p>
                    </div>
                </div>
                <div class="tip-item">
                    <i class="fas fa-file-alt tip-icon"></i>
                    <div class="tip-content">
                        <h4>Tailor Your CV</h4>
                        <p>Customize your CV for each application to highlight relevant skills.</p>
                    </div>
                </div>
                <div class="tip-item">
                    <i class="fas fa-comments tip-icon"></i>
                    <div class="tip-content">
                        <h4>Prepare for Interviews</h4>
                        <p>Research the company and practice common interview questions.</p>
                    </div>
                </div>
                <div class="tip-item">
                    <i class="fas fa-network-wired tip-icon"></i>
                    <div class="tip-content">
                        <h4>Network</h4>
                        <p>Connect with employees at the company through professional networks.</p>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Overlay for mobile sidebar -->
    <div class="overlay" id="overlay"></div>

    <!-- Application Details Modal -->
    <div class="modal" id="detailsModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="modalJobTitle">Job Title</h2>
                <button class="close-btn" id="closeModalBtn">&times;</button>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Content will be dynamically inserted here -->
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
        
        // Application data (in a real app, this would come from an API)
        const applications = <?php echo json_encode($applications); ?>;

        // DOM Elements
        const applicationsContainer = document.getElementById('applicationsContainer');
        const searchInput = document.getElementById('searchInput');
        const rightSidebar = document.getElementById('rightSidebar');
        const overlay = document.getElementById('overlay');
        const statusFilter = document.getElementById('statusFilter');
        const dateFilter = document.getElementById('dateFilter');
        const applyFiltersBtn = document.getElementById('applyFiltersBtn');
        const clearFiltersBtn = document.getElementById('clearFiltersBtn');
        const detailsModal = document.getElementById('detailsModal');
        const closeModalBtn = document.getElementById('closeModalBtn');
        const modalJobTitle = document.getElementById('modalJobTitle');
        const modalBody = document.getElementById('modalBody');
        const toast = document.getElementById('toast');
        const sortButtons = document.querySelectorAll('.sort-btn');


        // Search functionality
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            filterApplications();
        });

        // Filter functionality
        applyFiltersBtn.addEventListener('click', function() {
            filterApplications();
            showToast('Filters applied successfully', 'success');
            
            // Hide sidebar on mobile after applying filters
            if (window.innerWidth <= 1200) {
                rightSidebar.classList.remove('show');
                overlay.classList.remove('show');
            }
        });

        clearFiltersBtn.addEventListener('click', function() {
            statusFilter.value = '';
            dateFilter.value = '';
            searchInput.value = '';
            filterApplications();
            showToast('Filters cleared', 'info');
        });



        // Sort functionality
        sortButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Update active state
                sortButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                
                // Get sort parameters
                const sortBy = this.getAttribute('data-sort');
                const sortOrder = this.getAttribute('data-order');
                
                // Sort applications
                sortApplications(sortBy, sortOrder);
            });
        });

        // View details buttons
        document.addEventListener('click', function(e) {
            if (e.target.closest('.view-details-btn')) {
                const btn = e.target.closest('.view-details-btn');
                const applicationId = parseInt(btn.getAttribute('data-id'));
                showApplicationDetails(applicationId);
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
        });

        // Filter applications function
        function filterApplications() {
            const searchTerm = searchInput.value.toLowerCase();
            const statusValue = statusFilter.value.toLowerCase();
            const dateValue = dateFilter.value;
            
            const cards = document.querySelectorAll('.application-card');
            
            cards.forEach(card => {
                const title = card.querySelector('.job-title').textContent.toLowerCase();
                const company = card.getAttribute('data-company');
                const status = card.getAttribute('data-status');
                const date = new Date(card.getAttribute('data-date'));
                
                // Check if card matches search term
                const matchesSearch = searchTerm === '' || 
                    title.includes(searchTerm) || 
                    company.includes(searchTerm);
                
                // Check if card matches status filter
                const matchesStatus = statusValue === '' || status === statusValue;
                
                // Check if card matches date filter
                let matchesDate = true;
                if (dateValue !== '') {
                    const daysDiff = Math.floor((new Date() - date) / (1000 * 60 * 60 * 24));
                    matchesDate = daysDiff <= parseInt(dateValue);
                }
                
                // Show or hide card based on filters
                if (matchesSearch && matchesStatus && matchesDate) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        // Sort applications function
        function sortApplications(sortBy, sortOrder) {
            const cards = Array.from(document.querySelectorAll('.application-card'));
            
            // Debug: Check for duplicate cards
            const cardIds = cards.map(card => card.getAttribute('data-id'));
            const uniqueIds = [...new Set(cardIds)];
            if (cardIds.length !== uniqueIds.length) {
                console.warn('Duplicate application cards found in DOM:', cardIds.length - uniqueIds.length, 'duplicates');
                // Remove duplicates by keeping only the first occurrence of each ID
                const seenIds = new Set();
                const uniqueCards = cards.filter(card => {
                    const id = card.getAttribute('data-id');
                    if (seenIds.has(id)) {
                        card.remove(); // Remove duplicate
                        return false;
                    }
                    seenIds.add(id);
                    return true;
                });
                cards.length = 0;
                cards.push(...uniqueCards);
            }
            
            cards.sort((a, b) => {
                let aValue, bValue;
                
                switch (sortBy) {
                    case 'date':
                        aValue = new Date(a.getAttribute('data-date'));
                        bValue = new Date(b.getAttribute('data-date'));
                        break;
                    case 'status':
                        aValue = a.getAttribute('data-status');
                        bValue = b.getAttribute('data-status');
                        break;
                    case 'company':
                        aValue = a.getAttribute('data-company');
                        bValue = b.getAttribute('data-company');
                        break;
                    default:
                        return 0;
                }
                
                if (sortOrder === 'asc') {
                    return aValue > bValue ? 1 : -1;
                } else {
                    return aValue < bValue ? 1 : -1;
                }
            });
            
            // Reorder cards in the DOM
            cards.forEach(card => {
                applicationsContainer.appendChild(card);
            });
        }

        // Show application details function
        function showApplicationDetails(applicationId) {
            const application = applications.find(app => app.id === applicationId);
            
            if (!application) return;
            
            modalJobTitle.textContent = application.jobTitle;
            
            modalBody.innerHTML = `
                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="detail-label">Company</div>
                        <div class="detail-value">${application.company}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Location</div>
                        <div class="detail-value">${application.location}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Job Type</div>
                        <div class="detail-value">${application.jobType}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Salary Range</div>
                        <div class="detail-value">${application.salaryRange}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Application Date</div>
                        <div class="detail-value">${new Date(application.applicationDate).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Contact Person</div>
                        <div class="detail-value">${application.contactPerson}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Contact Email</div>
                        <div class="detail-value">${application.contactEmail}</div>
                    </div>
                </div>
                
                <div class="candidate-info">
                    <h3>Your Profile Information</h3>
                    <div class="detail-grid">
                        ${application.candidateEducation ? `
                        <div class="detail-item">
                            <div class="detail-label">Education</div>
                            <div class="detail-value">${application.candidateEducation}</div>
                        </div>
                        ` : ''}
                        ${application.candidateInstitute ? `
                        <div class="detail-item">
                            <div class="detail-label">Institute</div>
                            <div class="detail-value">${application.candidateInstitute}</div>
                        </div>
                        ` : ''}
                        ${application.candidateSkills ? `
                        <div class="detail-item">
                            <div class="detail-label">Skills</div>
                            <div class="detail-value">${application.candidateSkills}</div>
                        </div>
                        ` : ''}
                        ${application.candidateSummary ? `
                        <div class="detail-item full-width">
                            <div class="detail-label">Professional Summary</div>
                            <div class="detail-value">${application.candidateSummary}</div>
                        </div>
                        ` : ''}
                    </div>
                </div>
                
                <div class="job-description">
                    <h3>Job Description</h3>
                    <p>${application.jobDescription}</p>
                </div>
                
                <div class="status-history">
                    <h3 class="status-history-title">Application Progress</h3>
                    <div class="timeline">
                        ${application.statusFlow ? application.statusFlow.map(status => {
                            // Determine CSS class based on status
                            let timelineClass = 'timeline-item';
                            if (status.status_key === 'rejected') {
                                timelineClass += ' rejected-status';
                            } else if (status.completed) {
                                timelineClass += ' completed-status';
                            } else {
                                timelineClass += ' current-status';
                            }
                            
                            return `
                                <div class="${timelineClass}">
                                    <div class="timeline-content">
                                        <div class="timeline-status" style="color: ${getStatusColor(status.status)}; display: flex; align-items: center; gap: 8px;">
                                            ${status.completed ? '<i class="fas fa-check-circle" style="color: #3fb950;"></i>' : '<i class="fas fa-clock" style="color: #f59e0b;"></i>'}
                                            ${status.status}
                                        </div>
                                        <div class="timeline-date">${new Date(status.date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}</div>
                                        ${status.notes ? `<div class="timeline-notes" style="font-size: 12px; color: var(--text-secondary); margin-top: 4px;">${status.notes}</div>` : ''}
                                    </div>
                                </div>
                            `;
                        }).join('') : ''}
                    </div>
                </div>
                
                <div class="card-actions">
                    <button class="btn btn-primary" onclick="window.location.href='mailto:${application.contactEmail}?subject=Regarding my application for ${application.jobTitle}'">
                        <i class="fas fa-envelope"></i>
                        Contact Employer
                    </button>
                </div>
            `;
            
            detailsModal.style.display = 'flex';
        }


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
                case 'Application Submitted':
                case 'Applied':
                    return '#8b949e';
                case 'Company Invited':
                    return '#79c0ff';
                case 'MCQ Exam Assigned':
                    return '#58a6ff';
                case 'MCQ Exam In Progress':
                    return '#f59e0b';
                case 'MCQ Exam Passed':
                    return '#3fb950';
                case 'Waiting for Interview Call':
                    return '#f59e0b';
                case 'Called for Interview':
                case 'Interview Scheduled':
                    return '#f59e0b';
                case 'Interview In Progress':
                    return '#f59e0b';
                case 'Interview Completed':
                    return '#3fb950';
                case 'MCQ Exam Failed':
                case 'Rejected':
                case 'Withdrawn':
                    return '#f85149';
                case 'Offer Extended':
                    return '#3fb950';
                default:
                    return '#8b949e';
            }
        }

        // Initialize the page
        document.addEventListener('DOMContentLoaded', function() {
            // Set up initial sort
            sortApplications('date', 'desc');
        });

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


        
        // Auto-refresh functionality
        function setupAutoRefresh() {
            // Refresh data every 30 seconds
            setInterval(() => {
                checkForStatusUpdates();
                // Also check for duplicates during auto-refresh
                removeDuplicateCards();
            }, 30000);
        }
        
        // Check for status updates
        function checkForStatusUpdates() {
            // This would typically check for new notifications or status changes
            // For now, we'll just refresh the applications data silently
            fetch('applicationstatus.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'check_updates'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.hasUpdates) {
                    showToast('Application status updated!', 'info');
                    // Optionally reload the page to show updates
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                }
            })
            .catch(error => {
                console.error('Error checking for updates:', error);
            });
        }

        // Check for and remove duplicate application cards
        function removeDuplicateCards() {
            const cards = document.querySelectorAll('.application-card');
            const seenIds = new Set();
            let duplicatesRemoved = 0;
            
            cards.forEach(card => {
                const id = card.getAttribute('data-id');
                if (seenIds.has(id)) {
                    console.log(`Removing duplicate card with ID: ${id}`);
                    card.remove();
                    duplicatesRemoved++;
                } else {
                    seenIds.add(id);
                }
            });
            
            if (duplicatesRemoved > 0) {
                console.log(`Removed ${duplicatesRemoved} duplicate application cards`);
                // Show a toast notification to user
                showToast(`Removed ${duplicatesRemoved} duplicate application(s)`, 'info');
            }
        }
        

        // Initialize theme when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            initializeTheme();
            setupThemeToggle();
            setupProfileEditing();
            setupAutoRefresh();
            
            // Check for duplicates after a short delay to ensure all content is loaded
            // Run duplicate removal multiple times to catch any dynamically added duplicates
            setTimeout(removeDuplicateCards, 100);
            setTimeout(removeDuplicateCards, 500);
            setTimeout(removeDuplicateCards, 1000);
        });
    </script>
    <script>
        // Logout functionality parity with News Feed
        (function(){
            var btn = document.getElementById('logoutBtn');
            if (btn) {
                btn.addEventListener('click', function(){
                    window.location.href = 'Login&Signup.php';
                });
            }
        })();
    </script>
</body>
</html>
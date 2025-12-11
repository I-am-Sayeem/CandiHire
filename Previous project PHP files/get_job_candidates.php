<?php
// get_job_candidates.php - Get candidates who passed MCQ exam for a specific job
require_once 'Database.php';
require_once 'session_manager.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Check if company is logged in
if (!isCompanyLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Company not logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        if (!isset($pdo) || !$pdo instanceof PDO) {
            throw new Exception('Database connection not available');
        }

        $jobId = $_GET['jobId'] ?? '';
        $companyId = getCurrentCompanyId();

        if (empty($jobId)) {
            echo json_encode(['success' => false, 'message' => 'Job ID is required']);
            exit;
        }

        // Verify that the job belongs to the current company
        $jobCheckStmt = $pdo->prepare("SELECT JobID FROM job_postings WHERE JobID = ? AND CompanyID = ?");
        $jobCheckStmt->execute([$jobId, $companyId]);
        if (!$jobCheckStmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Job not found or access denied']);
            exit;
        }

        // First, let's check what data we have in the database
        $debugStmt = $pdo->prepare("
            SELECT 
                ja.JobID,
                ja.CandidateID,
                ja.Status,
                c.FullName,
                c.Email,
                c.PhoneNumber
            FROM job_applications ja
            JOIN candidate_login_info c ON ja.CandidateID = c.CandidateID
            WHERE ja.JobID = ?
            LIMIT 5
        ");
        $debugStmt->execute([$jobId]);
        $debugResults = $debugStmt->fetchAll(PDO::FETCH_ASSOC);
        error_log("Job applications for job " . $jobId . ": " . json_encode($debugResults));

        // Check exam assignments
        $examDebugStmt = $pdo->prepare("
            SELECT 
                ea.CandidateID,
                ea.JobID,
                ea.Score,
                ea.CompletedAt,
                ea.Status
            FROM exam_assignments ea
            WHERE ea.JobID = ?
            LIMIT 5
        ");
        $examDebugStmt->execute([$jobId]);
        $examDebugResults = $examDebugStmt->fetchAll(PDO::FETCH_ASSOC);
        error_log("Exam assignments for job " . $jobId . ": " . json_encode($examDebugResults));

        // Get candidates who applied for this job and PASSED the exam (60% or higher)
        $stmt = $pdo->prepare("
            SELECT DISTINCT
                cli.CandidateID,
                cli.FullName,
                cli.Email,
                cli.PhoneNumber,
                ja.ApplicationDate,
                ja.Status as ApplicationStatus,
                ea.Score as ExamScore,
                ea.CompletedAt,
                ea.Status as ExamStatus,
                e.PassingScore,
                1 as Passed -- All candidates in this result have passed
            FROM exam_assignments ea
            JOIN exams e ON ea.ExamID = e.ExamID
            JOIN job_postings jp ON ea.JobID = jp.JobID
            JOIN candidate_login_info cli ON ea.CandidateID = cli.CandidateID
            LEFT JOIN job_applications ja ON ea.CandidateID = ja.CandidateID AND ea.JobID = ja.JobID
            WHERE ea.JobID = ? 
            AND jp.CompanyID = ?
            AND ea.Score IS NOT NULL 
            AND ea.Score >= COALESCE(e.PassingScore, 60)
            AND ea.Status = 'completed'
            ORDER BY ea.Score DESC, ea.CompletedAt DESC
        ");

        $stmt->execute([$jobId, $companyId]);
        $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Debug: Log the query results
        error_log("Found " . count($candidates) . " candidates for job ID: " . $jobId);
        if (count($candidates) > 0) {
            error_log("Sample candidate data: " . json_encode($candidates[0]));
        } else {
            // Try a simpler query to see if we can get any candidates
            $simpleStmt = $pdo->prepare("
                SELECT DISTINCT
                    c.CandidateID,
                    c.FullName,
                    c.Email,
                    ja.ApplicationDate,
                    ja.Status as ApplicationStatus
                FROM job_applications ja
                JOIN candidate_login_info c ON ja.CandidateID = c.CandidateID
                WHERE ja.JobID = ?
                LIMIT 10
            ");
            $simpleStmt->execute([$jobId]);
            $simpleCandidates = $simpleStmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Simple query found " . count($simpleCandidates) . " candidates: " . json_encode($simpleCandidates));
        }

        // Format candidate data
        $formattedCandidates = [];
        foreach ($candidates as $candidate) {
            $formattedCandidates[] = [
                'candidateId' => $candidate['CandidateID'],
                'name' => $candidate['FullName'],
                'email' => $candidate['Email'],
                'phone' => $candidate['PhoneNumber'],
                'examScore' => $candidate['ExamScore'] ? (int)$candidate['ExamScore'] : null,
                'passed' => (bool)$candidate['Passed'],
                'applicationDate' => $candidate['ApplicationDate'],
                'applicationStatus' => $candidate['ApplicationStatus'],
                'examStatus' => $candidate['ExamStatus'],
                'examCompletedAt' => $candidate['CompletedAt']
            ];
        }

        $response = [
            'success' => true,
            'candidates' => $formattedCandidates,
            'count' => count($formattedCandidates),
            'debug_info' => [
                'job_id' => $jobId,
                'company_id' => $companyId,
                'job_applications_found' => count($debugResults),
                'exam_assignments_found' => count($examDebugResults),
                'candidates_with_exam_scores' => count($candidates),
                'simple_query_candidates' => isset($simpleCandidates) ? count($simpleCandidates) : 0
            ]
        ];

        echo json_encode($response);

    } catch (Exception $e) {
        error_log("Error in get_job_candidates.php: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>

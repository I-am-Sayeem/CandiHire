<?php
require_once 'Database.php';

echo "=== Checking Existing Exam Assignments ===\n\n";

try {
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        throw new Exception('Database connection not available');
    }

    // Check total assignments
    $totalStmt = $pdo->prepare("SELECT COUNT(*) as count FROM exam_assignments");
    $totalStmt->execute();
    $totalCount = $totalStmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "Total exam assignments: $totalCount\n\n";

    if ($totalCount > 0) {
        // Get recent assignments
        $recentStmt = $pdo->prepare("
            SELECT ea.AssignmentID, ea.CandidateID, ea.JobID, ea.AssignmentDate, ea.Status,
                   e.ExamTitle, e.QuestionCount, e.IsActive,
                   cli.FullName as CandidateName,
                   jp.JobTitle
            FROM exam_assignments ea
            JOIN exams e ON ea.ExamID = e.ExamID
            JOIN candidate_login_info cli ON ea.CandidateID = cli.CandidateID
            JOIN job_postings jp ON ea.JobID = jp.JobID
            ORDER BY ea.AssignmentDate DESC
            LIMIT 10
        ");
        $recentStmt->execute();
        $recentAssignments = $recentStmt->fetchAll(PDO::FETCH_ASSOC);

        echo "Recent assignments:\n";
        foreach ($recentAssignments as $assignment) {
            echo "- Assignment ID: {$assignment['AssignmentID']}\n";
            echo "  Candidate: {$assignment['CandidateName']} (ID: {$assignment['CandidateID']})\n";
            echo "  Job: {$assignment['JobTitle']} (ID: {$assignment['JobID']})\n";
            echo "  Exam: {$assignment['ExamTitle']} (Questions: {$assignment['QuestionCount']})\n";
            echo "  Status: {$assignment['Status']}\n";
            echo "  Assignment Date: {$assignment['AssignmentDate']}\n";
            echo "  Exam Active: " . ($assignment['IsActive'] ? 'Yes' : 'No') . "\n";
            echo "\n";
        }

        // Check for assignments with exams that have no questions
        $noQuestionsStmt = $pdo->prepare("
            SELECT COUNT(*) as count
            FROM exam_assignments ea
            JOIN exams e ON ea.ExamID = e.ExamID
            WHERE e.QuestionCount = 0
        ");
        $noQuestionsStmt->execute();
        $noQuestionsCount = $noQuestionsStmt->fetch(PDO::FETCH_ASSOC)['count'];
        echo "Assignments with exams that have no questions: $noQuestionsCount\n\n";

        if ($noQuestionsCount > 0) {
            echo "These assignments should not exist (exams with no questions):\n";
            $noQuestionsDetailsStmt = $pdo->prepare("
                SELECT ea.AssignmentID, e.ExamTitle, e.QuestionCount, ea.AssignmentDate
                FROM exam_assignments ea
                JOIN exams e ON ea.ExamID = e.ExamID
                WHERE e.QuestionCount = 0
                ORDER BY ea.AssignmentDate DESC
            ");
            $noQuestionsDetailsStmt->execute();
            $noQuestionsDetails = $noQuestionsDetailsStmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($noQuestionsDetails as $assignment) {
                echo "- Assignment ID: {$assignment['AssignmentID']}\n";
                echo "  Exam: {$assignment['ExamTitle']}\n";
                echo "  Question Count: {$assignment['QuestionCount']}\n";
                echo "  Assignment Date: {$assignment['AssignmentDate']}\n";
                echo "\n";
            }
        }
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>

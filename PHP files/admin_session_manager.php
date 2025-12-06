<?php
// admin_session_manager.php - Admin Session Management

// Check if admin is logged in
function isAdminLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

// Get current admin ID
function getCurrentAdminId() {
    return $_SESSION['admin_id'] ?? null;
}

// Get current admin username
function getCurrentAdminUsername() {
    return $_SESSION['admin_username'] ?? 'Admin';
}

// Require admin login - redirect if not logged in
function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        header('Location: admin_login_handler.php');
        exit;
    }
}

// Admin logout
function adminLogout() {
    session_start();
    unset($_SESSION['admin_logged_in']);
    unset($_SESSION['admin_id']);
    unset($_SESSION['admin_username']);
    session_destroy();
    header('Location: admin_login_handler.php');
    exit;
}

// Get system statistics
function getSystemStats($pdo) {
    $stats = [];
    
    try {
        // Count candidates
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM candidate_login_info");
        $stats['candidates'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Count companies
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM Company_login_info");
        $stats['companies'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Count job posts
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM job_postings");
        $stats['job_posts'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Count active job posts
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM job_postings WHERE Status = 'active'");
        $stats['active_jobs'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Count job applications
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM job_applications");
        $stats['applications'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Count exams
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM exams");
        $stats['exams'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Count exam assignments
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM exam_assignments");
        $stats['exam_assignments'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Count completed exams
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM exam_assignments WHERE Status = 'completed'");
        $stats['completed_exams'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
    } catch (Exception $e) {
        error_log("Error getting system stats: " . $e->getMessage());
        $stats = [
            'candidates' => 0,
            'companies' => 0,
            'job_posts' => 0,
            'active_jobs' => 0,
            'applications' => 0,
            'exams' => 0,
            'exam_assignments' => 0,
            'completed_exams' => 0
        ];
    }
    
    return $stats;
}

// Get recent activity
function getRecentActivity($pdo) {
    $activities = [];
    
    try {
        // Recent job applications
        $stmt = $pdo->query("
            SELECT 'job_application' as type, ja.ApplicationDate as date, 
                   cli.FullName as user_name, jp.JobTitle as description
            FROM job_applications ja
            JOIN candidate_login_info cli ON ja.CandidateID = cli.CandidateID
            JOIN job_postings jp ON ja.JobID = jp.JobID
            ORDER BY ja.ApplicationDate DESC
            LIMIT 5
        ");
        $activities = array_merge($activities, $stmt->fetchAll(PDO::FETCH_ASSOC));
        
        // Recent job posts
        $stmt = $pdo->query("
            SELECT 'job_post' as type, jp.CreatedAt as date,
                   cli.CompanyName as user_name, jp.JobTitle as description
            FROM job_postings jp
            JOIN Company_login_info cli ON jp.CompanyID = cli.CompanyID
            ORDER BY jp.CreatedAt DESC
            LIMIT 5
        ");
        $activities = array_merge($activities, $stmt->fetchAll(PDO::FETCH_ASSOC));
        
        // Sort by date
        usort($activities, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });
        
        return array_slice($activities, 0, 10);
        
    } catch (Exception $e) {
        error_log("Error getting recent activity: " . $e->getMessage());
        return [];
    }
}
?>

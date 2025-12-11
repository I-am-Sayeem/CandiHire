<?php
// session_manager.php - Session management for both candidate and company login

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// =============================================
// COMPANY SESSION FUNCTIONS
// =============================================

// Check if company is logged in
function isCompanyLoggedIn() {
    return isset($_SESSION['company_id']) && !empty($_SESSION['company_id']) && 
           (isset($_SESSION['user_type']) ? $_SESSION['user_type'] === 'company' : true);
}

// Get current company ID
function getCurrentCompanyId() {
    return $_SESSION['company_id'] ?? null;
}

// Set company session
function setCompanySession($companyId, $companyName) {
    $_SESSION['company_id'] = $companyId;
    $_SESSION['company_name'] = $companyName;
    $_SESSION['user_type'] = 'company';
}

// Clear company session
function clearCompanySession() {
    unset($_SESSION['company_id']);
    unset($_SESSION['company_name']);
    unset($_SESSION['user_type']);
    session_destroy();
}

// Redirect if not logged in as company
function requireCompanyLogin($redirectTo = 'Login&Signup.php') {
    if (!isCompanyLoggedIn()) {
        header('Location: ' . $redirectTo);
        exit;
    }
}

// =============================================
// CANDIDATE SESSION FUNCTIONS
// =============================================

// Check if candidate is logged in
function isCandidateLoggedIn() {
    return isset($_SESSION['candidate_id']) && !empty($_SESSION['candidate_id']) && $_SESSION['user_type'] === 'candidate';
}

// Get current candidate ID
function getCurrentCandidateId() {
    return $_SESSION['candidate_id'] ?? null;
}

// Set candidate session
function setCandidateSession($candidateId, $fullName) {
    $_SESSION['candidate_id'] = $candidateId;
    $_SESSION['candidate_name'] = $fullName;
    $_SESSION['user_type'] = 'candidate';
}

// Clear candidate session
function clearCandidateSession() {
    unset($_SESSION['candidate_id']);
    unset($_SESSION['candidate_name']);
    unset($_SESSION['user_type']);
    session_destroy();
}

// Redirect if not logged in as candidate
function requireCandidateLogin($redirectTo = 'Login&Signup.php') {
    if (!isCandidateLoggedIn()) {
        header('Location: ' . $redirectTo);
        exit;
    }
}

// =============================================
// GENERAL SESSION FUNCTIONS
// =============================================

// Check if any user is logged in
function isUserLoggedIn() {
    return isCompanyLoggedIn() || isCandidateLoggedIn();
}

// Get current user type
function getCurrentUserType() {
    return $_SESSION['user_type'] ?? null;
}

// Clear all sessions
function clearAllSessions() {
    session_unset();
    session_destroy();
}

// Redirect if not logged in (any user type)
function requireLogin($redirectTo = 'Login&Signup.php') {
    if (!isUserLoggedIn()) {
        header('Location: ' . $redirectTo);
        exit;
    }
}
?>

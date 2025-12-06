<?php
// logout.php - Handle user logout

require_once 'session_manager.php';

// Clear all sessions
clearAllSessions();

// Return success response
header('Content-Type: application/json');
echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
?>

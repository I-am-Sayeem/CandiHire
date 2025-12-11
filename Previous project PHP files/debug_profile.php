<?php
// debug_profile.php - Debug endpoint to test profile functionality

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

echo json_encode([
    'success' => true,
    'message' => 'Debug endpoint working',
    'request_method' => $_SERVER['REQUEST_METHOD'],
    'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'Not set',
    'post_data' => $_POST,
    'files_data' => $_FILES,
    'timestamp' => date('Y-m-d H:i:s')
]);
?>

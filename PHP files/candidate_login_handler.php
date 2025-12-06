<?php
// candidate_login_handler.php

error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once 'Database.php'; // Provides $pdo
require_once 'session_manager.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    if (!isset($pdo) || !$pdo instanceof PDO) {
        throw new Exception('Database connection not available');
    }

    $json_input = file_get_contents('php://input');
    if (empty($json_input)) {
        echo json_encode(['success' => false, 'message' => 'No data received']);
        exit;
    }

    $input = json_decode($json_input, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
        exit;
    }

    $email = strtolower(trim($input['email'] ?? ''));
    $password = $input['password'] ?? '';

    if (!$email || !$password) {
        echo json_encode(['success' => false, 'message' => 'Email and password are required']);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT CandidateID, Password, FullName FROM candidate_login_info WHERE Email = ? LIMIT 1");
    $stmt->execute([$email]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
        exit;
    }

    $hash = $row['Password'] ?? '';
    if (!is_string($hash) || !password_verify($password, $hash)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
        exit;
    }

    // Set candidate session
    setCandidateSession($row['CandidateID'], $row['FullName']);

    echo json_encode([
        'success' => true,
        'message' => 'Welcome back, ' . ($row['FullName'] ?? 'User') . '! Login successful.',
        'candidateId' => $row['CandidateID'],
        'fullName' => $row['FullName'] ?? null
    ]);

} catch (Exception $e) {
    error_log("Error in candidate_login_handler.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error']);
}



<?php
// candidate_reg_handler.php

error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once 'Database.php'; // This must define $pdo

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
    // Ensure PDO exists
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

    // Required fields
    $required_fields = ['fullName', 'email', 'phoneNumber', 'workType', 'password', 'confirmPassword'];
    $missing = [];
    foreach ($required_fields as $f) {
        if (empty(trim($input[$f] ?? ''))) {
            $missing[] = $f;
        }
    }
    if ($missing) {
        echo json_encode(['success' => false, 'message' => 'Missing: ' . implode(', ', $missing)]);
        exit;
    }

    // Collect inputs
    $fullName = trim($input['fullName']);
    $email = strtolower(trim($input['email']));
    $phone = trim($input['phoneNumber']);
    $workType = trim($input['workType']);
    $skills = $input['skills'] ?? '';
    $password = $input['password'];
    $confirmPassword = $input['confirmPassword'];
    

    // Validations
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        exit;
    }

    if ($password !== $confirmPassword) {
        echo json_encode(['success' => false, 'message' => 'Passwords do not match']);
        exit;
    }

    if (strlen($password) < 8) {
        echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters']);
        exit;
    }

    $allowed = ['full-time','part-time','contract','freelance','internship','fresher'];
    if (!in_array($workType, $allowed)) {
        echo json_encode(['success' => false, 'message' => 'Invalid work type']);
        exit;
    }

    // Check if email already exists in candidate table
    $check = $pdo->prepare("SELECT CandidateID FROM candidate_login_info WHERE Email = ?");
    $check->execute([$email]);
    if ($check->rowCount() > 0) {
        echo json_encode(['success' => false, 'message' => 'This email is already registered as a candidate. Please use a different email or try logging in.']);
        exit;
    }
    
    // Check if email already exists in company table
    $checkCompany = $pdo->prepare("SELECT CompanyID FROM Company_login_info WHERE Email = ?");
    $checkCompany->execute([$email]);
    if ($checkCompany->rowCount() > 0) {
        echo json_encode(['success' => false, 'message' => 'This email is already registered as a company. Please use a different email or try logging in.']);
        exit;
    }

    // Insert candidate without email verification
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("
        INSERT INTO candidate_login_info 
        (FullName, Email, PhoneNumber, WorkType, Skills, Password, CreatedAt)
        VALUES (?, ?, ?, ?, ?, ?, NOW())
    ");
    $ok = $stmt->execute([$fullName, $email, $phone, $workType, $skills, $hashedPassword]);

    if ($ok) {
        $id = $pdo->lastInsertId();
        echo json_encode([
            'success' => true,
            'message' => 'Account created successfully!',
            'candidateId' => $id
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Account creation failed']);
    }

} catch (Exception $e) {
    error_log("Error in candidate_reg_handler.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error']);
}

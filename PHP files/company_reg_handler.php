<?php
// company_reg_handler.php

error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once 'Database.php'; // Provides $pdo

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

    // Required payload fields (frontend keys)
    $required = ['companyName','industry','companySize','email','phoneNumber','companyDescription','password','confirmPassword'];
    $missing = [];
    foreach ($required as $f) {
        if (empty(trim($input[$f] ?? ''))) { $missing[] = $f; }
    }
    if ($missing) {
        echo json_encode(['success' => false, 'message' => 'Missing: ' . implode(', ', $missing)]);
        exit;
    }

    $companyName = trim($input['companyName']);
    $industry = trim($input['industry']);
    $companySize = trim($input['companySize']);
    $email = strtolower(trim($input['email']));
    $phoneNumber = trim($input['phoneNumber']);
    $companyDescription = trim($input['companyDescription']);
    $password = $input['password'];
    $confirmPassword = $input['confirmPassword'];
    

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

    // Optional: basic allow-list checks (adjust as needed)
    $allowedSizes = ['1-10','11-50','51-200','201-500','501-1000','1000+'];
    if ($companySize && !in_array($companySize, $allowedSizes)) {
        // Allow arbitrary too, but you can enforce by uncommenting next lines
        // echo json_encode(['success' => false, 'message' => 'Invalid company size']);
        // exit;
    }

    // Check if email already exists in company table
    $check = $pdo->prepare("SELECT 1 FROM Company_login_info WHERE Email = ? LIMIT 1");
    $check->execute([$email]);
    if ($check->fetchColumn()) {
        echo json_encode(['success' => false, 'message' => 'This email is already registered as a company. Please use a different email or try logging in.']);
        exit;
    }
    
    // Check if email already exists in candidate table
    $checkCandidate = $pdo->prepare("SELECT 1 FROM candidate_login_info WHERE Email = ? LIMIT 1");
    $checkCandidate->execute([$email]);
    if ($checkCandidate->fetchColumn()) {
        echo json_encode(['success' => false, 'message' => 'This email is already registered as a candidate. Please use a different email or try logging in.']);
        exit;
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO Company_login_info 
        (CompanyName, Industry, CompanySize, Email, PhoneNumber, CompanyDescription, Password) 
        VALUES (?, ?, ?, ?, ?, ?, ?)");
    $ok = $stmt->execute([
        $companyName,
        $industry,
        $companySize,
        $email,
        $phoneNumber,
        $companyDescription,
        $hashedPassword
    ]);

    if ($ok) {
        $id = $pdo->lastInsertId();
        echo json_encode([
            'success' => true,
            'message' => 'Company account created successfully!',
            'companyId' => $id
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Company account creation failed']);
    }

} catch (Exception $e) {
    error_log('Error in company_reg_handler.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error']);
}



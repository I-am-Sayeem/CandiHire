<?php
// notification_helper.php - Server-side notification helper

class NotificationHelper {
    
    /**
     * Generate standardized JSON response for notifications
     */
    public static function response($success, $message, $data = null) {
        $response = [
            'success' => $success,
            'message' => $message
        ];
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        return json_encode($response);
    }
    
    /**
     * Send success response
     */
    public static function success($message, $data = null) {
        return self::response(true, $message, $data);
    }
    
    /**
     * Send error response
     */
    public static function error($message, $data = null) {
        return self::response(false, $message, $data);
    }
    
    /**
     * Send warning response
     */
    public static function warning($message, $data = null) {
        return self::response(false, $message, $data);
    }
    
    /**
     * Send info response
     */
    public static function info($message, $data = null) {
        return self::response(true, $message, $data);
    }
    
    /**
     * Common success messages
     */
    public static function accountCreated($type = 'candidate') {
        $messages = [
            'candidate' => 'Congratulations! Your account has been created successfully. Welcome to CandiHire!',
            'company' => 'Congratulations! Your company account has been created successfully. Welcome to CandiHire!'
        ];
        return $messages[$type] ?? $messages['candidate'];
    }
    
    /**
     * Common error messages
     */
    public static function emailExists() {
        return 'This email is already registered. Please use a different email or try logging in.';
    }
    
    public static function loginFailed() {
        return 'Invalid email or password. Please check your credentials and try again.';
    }
    
    public static function serverError() {
        return 'A server error occurred. Please try again later or contact support if the problem persists.';
    }
    
    public static function networkError() {
        return 'Network error occurred. Please check your connection and try again.';
    }
    
    /**
     * Validation messages
     */
    public static function requiredFields($fields = []) {
        if (empty($fields)) {
            return 'Please fill in all required fields.';
        }
        return 'Missing required fields: ' . implode(', ', $fields);
    }
    
    public static function passwordMismatch() {
        return 'Passwords do not match. Please make sure both password fields are identical.';
    }
    
    public static function passwordTooShort($minLength = 8) {
        return "Password must be at least {$minLength} characters long.";
    }
    
    public static function invalidEmail() {
        return 'Please enter a valid email address.';
    }
    
    /**
     * Login success messages
     */
    public static function loginSuccess($name = null, $type = 'user') {
        if ($name) {
            return "Welcome back, {$name}! Login successful.";
        }
        return 'Login successful. Welcome back!';
    }
    
    /**
     * Redirect messages
     */
    public static function redirecting($destination = 'dashboard') {
        return "Redirecting to {$destination}...";
    }
    
    /**
     * Log notification for debugging
     */
    public static function log($message, $type = 'info', $context = []) {
        $logMessage = "[{$type}] " . date('Y-m-d H:i:s') . " - {$message}";
        if (!empty($context)) {
            $logMessage .= " | Context: " . json_encode($context);
        }
        error_log($logMessage);
    }
}

// Convenience functions for global use
function notification_success($message, $data = null) {
    echo NotificationHelper::success($message, $data);
}

function notification_error($message, $data = null) {
    echo NotificationHelper::error($message, $data);
}

function notification_warning($message, $data = null) {
    echo NotificationHelper::warning($message, $data);
}

function notification_info($message, $data = null) {
    echo NotificationHelper::info($message, $data);
}
?>

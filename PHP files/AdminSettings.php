<?php
// AdminSettings.php - System Settings Management
session_start();
require_once 'admin_session_manager.php';
require_once 'Database.php';

// Check if admin is logged in
requireAdminLogin();

$adminUsername = getCurrentAdminUsername();
$message = '';
$messageType = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'update_general':
            $message = updateGeneralSettings($pdo, $_POST);
            $messageType = $message['success'] ? 'success' : 'error';
            $message = $message['message'];
            break;
            
        case 'update_email':
            $message = updateEmailSettings($pdo, $_POST);
            $messageType = $message['success'] ? 'success' : 'error';
            $message = $message['message'];
            break;
            
        case 'update_security':
            $message = updateSecuritySettings($pdo, $_POST);
            $messageType = $message['success'] ? 'success' : 'error';
            $message = $message['message'];
            break;
            
        case 'update_exam':
            $message = updateExamSettings($pdo, $_POST);
            $messageType = $message['success'] ? 'success' : 'error';
            $message = $message['message'];
            break;
            
        case 'update_notification':
            $message = updateNotificationSettings($pdo, $_POST);
            $messageType = $message['success'] ? 'success' : 'error';
            $message = $message['message'];
            break;
            
        case 'reset_settings':
            $message = resetAllSettings($pdo);
            $messageType = $message['success'] ? 'success' : 'error';
            $message = $message['message'];
            break;
    }
}

// Get current settings
$settings = getSystemSettings($pdo);

function getSystemSettings($pdo) {
    $settings = [];
    
    try {
        $stmt = $pdo->query("SELECT SettingKey, SettingValue, Description, Category FROM system_settings ORDER BY Category, SettingKey");
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($results as $row) {
            $settings[$row['Category']][$row['SettingKey']] = [
                'value' => $row['SettingValue'],
                'description' => $row['Description']
            ];
        }
    } catch (Exception $e) {
        error_log("Error fetching system settings: " . $e->getMessage());
    }
    
    return $settings;
}

function updateGeneralSettings($pdo, $data) {
    try {
        $settings = [
            'site_name' => $data['site_name'] ?? '',
            'site_description' => $data['site_description'] ?? '',
            'site_keywords' => $data['site_keywords'] ?? '',
            'admin_email' => $data['admin_email'] ?? '',
            'timezone' => $data['timezone'] ?? 'UTC',
            'date_format' => $data['date_format'] ?? 'Y-m-d',
            'time_format' => $data['time_format'] ?? 'H:i:s',
            'currency' => $data['currency'] ?? 'USD',
            'language' => $data['language'] ?? 'en'
        ];
        
        foreach ($settings as $key => $value) {
            updateSetting($pdo, $key, $value, 'general');
        }
        
        return ['success' => true, 'message' => 'General settings updated successfully'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error updating general settings: ' . $e->getMessage()];
    }
}

function updateEmailSettings($pdo, $data) {
    try {
        $settings = [
            'smtp_host' => $data['smtp_host'] ?? '',
            'smtp_port' => $data['smtp_port'] ?? '587',
            'smtp_username' => $data['smtp_username'] ?? '',
            'smtp_password' => $data['smtp_password'] ?? '',
            'smtp_encryption' => $data['smtp_encryption'] ?? 'tls',
            'from_email' => $data['from_email'] ?? '',
            'from_name' => $data['from_name'] ?? '',
            'email_signature' => $data['email_signature'] ?? ''
        ];
        
        foreach ($settings as $key => $value) {
            updateSetting($pdo, $key, $value, 'email');
        }
        
        return ['success' => true, 'message' => 'Email settings updated successfully'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error updating email settings: ' . $e->getMessage()];
    }
}

function updateSecuritySettings($pdo, $data) {
    try {
        $settings = [
            'password_min_length' => $data['password_min_length'] ?? '8',
            'password_require_special' => $data['password_require_special'] ?? '1',
            'session_timeout' => $data['session_timeout'] ?? '3600',
            'max_login_attempts' => $data['max_login_attempts'] ?? '5',
            'lockout_duration' => $data['lockout_duration'] ?? '900',
            'require_email_verification' => $data['require_email_verification'] ?? '1',
            'enable_2fa' => $data['enable_2fa'] ?? '0',
            'allowed_file_types' => $data['allowed_file_types'] ?? 'pdf,doc,docx,jpg,png',
            'max_file_size' => $data['max_file_size'] ?? '10485760'
        ];
        
        foreach ($settings as $key => $value) {
            updateSetting($pdo, $key, $value, 'security');
        }
        
        return ['success' => true, 'message' => 'Security settings updated successfully'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error updating security settings: ' . $e->getMessage()];
    }
}

function updateExamSettings($pdo, $data) {
    try {
        $settings = [
            'exam_timeout' => $data['exam_timeout'] ?? '3600',
            'default_exam_duration' => $data['default_exam_duration'] ?? '60',
            'max_exam_attempts' => $data['max_exam_attempts'] ?? '3',
            'exam_auto_submit' => $data['exam_auto_submit'] ?? '1',
            'show_correct_answers' => $data['show_correct_answers'] ?? '1',
            'randomize_questions' => $data['randomize_questions'] ?? '1',
            'randomize_options' => $data['randomize_options'] ?? '1',
            'passing_score' => $data['passing_score'] ?? '70'
        ];
        
        foreach ($settings as $key => $value) {
            updateSetting($pdo, $key, $value, 'exams');
        }
        
        return ['success' => true, 'message' => 'Exam settings updated successfully'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error updating exam settings: ' . $e->getMessage()];
    }
}

function updateNotificationSettings($pdo, $data) {
    try {
        $settings = [
            'interview_reminder_hours' => $data['interview_reminder_hours'] ?? '24',
            'exam_reminder_hours' => $data['exam_reminder_hours'] ?? '2',
            'application_notification' => $data['application_notification'] ?? '1',
            'interview_notification' => $data['interview_notification'] ?? '1',
            'exam_notification' => $data['exam_notification'] ?? '1',
            'system_maintenance_notification' => $data['system_maintenance_notification'] ?? '1',
            'email_notifications' => $data['email_notifications'] ?? '1',
            'push_notifications' => $data['push_notifications'] ?? '0'
        ];
        
        foreach ($settings as $key => $value) {
            updateSetting($pdo, $key, $value, 'notifications');
        }
        
        return ['success' => true, 'message' => 'Notification settings updated successfully'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error updating notification settings: ' . $e->getMessage()];
    }
}

function updateSetting($pdo, $key, $value, $category) {
    $stmt = $pdo->prepare("
        INSERT INTO system_settings (SettingKey, SettingValue, Category, Description) 
        VALUES (?, ?, ?, ?) 
        ON DUPLICATE KEY UPDATE 
        SettingValue = VALUES(SettingValue),
        UpdatedAt = CURRENT_TIMESTAMP
    ");
    
    $description = getSettingDescription($key);
    $stmt->execute([$key, $value, $category, $description]);
}

function getSettingDescription($key) {
    $descriptions = [
        'site_name' => 'Website name displayed in headers and titles',
        'site_description' => 'Meta description for SEO',
        'site_keywords' => 'Meta keywords for SEO',
        'admin_email' => 'Primary administrator email address',
        'timezone' => 'Default timezone for the system',
        'date_format' => 'Default date format (PHP format)',
        'time_format' => 'Default time format (PHP format)',
        'currency' => 'Default currency code',
        'language' => 'Default language code',
        'smtp_host' => 'SMTP server hostname',
        'smtp_port' => 'SMTP server port number',
        'smtp_username' => 'SMTP authentication username',
        'smtp_password' => 'SMTP authentication password',
        'smtp_encryption' => 'SMTP encryption method (tls/ssl)',
        'from_email' => 'Default sender email address',
        'from_name' => 'Default sender name',
        'email_signature' => 'Default email signature',
        'password_min_length' => 'Minimum password length requirement',
        'password_require_special' => 'Require special characters in passwords',
        'session_timeout' => 'Session timeout in seconds',
        'max_login_attempts' => 'Maximum failed login attempts before lockout',
        'lockout_duration' => 'Account lockout duration in seconds',
        'require_email_verification' => 'Require email verification for new accounts',
        'enable_2fa' => 'Enable two-factor authentication',
        'allowed_file_types' => 'Comma-separated list of allowed file extensions',
        'max_file_size' => 'Maximum file upload size in bytes',
        'exam_timeout' => 'Default exam timeout in seconds',
        'default_exam_duration' => 'Default exam duration in minutes',
        'max_exam_attempts' => 'Maximum exam attempts allowed',
        'exam_auto_submit' => 'Automatically submit exam when time expires',
        'show_correct_answers' => 'Show correct answers after exam completion',
        'randomize_questions' => 'Randomize question order in exams',
        'randomize_options' => 'Randomize answer options in exams',
        'passing_score' => 'Default passing score percentage',
        'interview_reminder_hours' => 'Hours before interview to send reminder',
        'exam_reminder_hours' => 'Hours before exam to send reminder',
        'application_notification' => 'Enable application status notifications',
        'interview_notification' => 'Enable interview notifications',
        'exam_notification' => 'Enable exam notifications',
        'system_maintenance_notification' => 'Enable system maintenance notifications',
        'email_notifications' => 'Enable email notifications',
        'push_notifications' => 'Enable push notifications'
    ];
    
    return $descriptions[$key] ?? 'System setting';
}

function resetAllSettings($pdo) {
    try {
        // Reset to default values
        $defaultSettings = [
            ['site_name', 'CandiHire', 'general', 'Website name'],
            ['site_description', 'Professional Job Portal Platform', 'general', 'Meta description for SEO'],
            ['admin_email', 'admin@candihire.com', 'general', 'Primary administrator email address'],
            ['timezone', 'UTC', 'general', 'Default timezone for the system'],
            ['currency', 'USD', 'general', 'Default currency code'],
            ['language', 'en', 'general', 'Default language code'],
            ['smtp_port', '587', 'email', 'SMTP server port number'],
            ['smtp_encryption', 'tls', 'email', 'SMTP encryption method'],
            ['password_min_length', '8', 'security', 'Minimum password length requirement'],
            ['session_timeout', '3600', 'security', 'Session timeout in seconds'],
            ['max_login_attempts', '5', 'security', 'Maximum failed login attempts before lockout'],
            ['lockout_duration', '900', 'security', 'Account lockout duration in seconds'],
            ['require_email_verification', '1', 'security', 'Require email verification for new accounts'],
            ['allowed_file_types', 'pdf,doc,docx,jpg,png', 'security', 'Comma-separated list of allowed file extensions'],
            ['max_file_size', '10485760', 'security', 'Maximum file upload size in bytes'],
            ['exam_timeout', '3600', 'exams', 'Default exam timeout in seconds'],
            ['default_exam_duration', '60', 'exams', 'Default exam duration in minutes'],
            ['max_exam_attempts', '3', 'exams', 'Maximum exam attempts allowed'],
            ['exam_auto_submit', '1', 'exams', 'Automatically submit exam when time expires'],
            ['show_correct_answers', '1', 'exams', 'Show correct answers after exam completion'],
            ['randomize_questions', '1', 'exams', 'Randomize question order in exams'],
            ['randomize_options', '1', 'exams', 'Randomize answer options in exams'],
            ['passing_score', '70', 'exams', 'Default passing score percentage'],
            ['interview_reminder_hours', '24', 'notifications', 'Hours before interview to send reminder'],
            ['exam_reminder_hours', '2', 'notifications', 'Hours before exam to send reminder'],
            ['application_notification', '1', 'notifications', 'Enable application status notifications'],
            ['interview_notification', '1', 'notifications', 'Enable interview notifications'],
            ['exam_notification', '1', 'notifications', 'Enable exam notifications'],
            ['system_maintenance_notification', '1', 'notifications', 'Enable system maintenance notifications'],
            ['email_notifications', '1', 'notifications', 'Enable email notifications']
        ];
        
        // Clear existing settings
        $pdo->query("DELETE FROM system_settings");
        
        // Insert default settings
        $stmt = $pdo->prepare("
            INSERT INTO system_settings (SettingKey, SettingValue, Category, Description) 
            VALUES (?, ?, ?, ?)
        ");
        
        foreach ($defaultSettings as $setting) {
            $stmt->execute($setting);
        }
        
        return ['success' => true, 'message' => 'All settings have been reset to default values'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error resetting settings: ' . $e->getMessage()];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Settings - CandiHire Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg-primary: #0d1117;
            --bg-secondary: #161b22;
            --bg-tertiary: #21262d;
            --text-primary: #c9d1d9;
            --text-secondary: #8b949e;
            --accent-1: #58a6ff;
            --accent-2: #f59e0b;
            --accent-hover: #79c0ff;
            --border: #30363d;
            --success: #3fb950;
            --danger: #f85149;
            --warning: #d29922;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Noto Sans', Helvetica, Arial, sans-serif;
        }

        body {
            background: var(--bg-primary);
            color: var(--text-primary);
            line-height: 1.6;
            min-height: 100vh;
        }

        .admin-header {
            background: var(--bg-secondary);
            border-bottom: 1px solid var(--border);
            padding: 15px 0;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .admin-title {
            font-size: 1.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--accent-1), var(--accent-2));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .admin-user {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-info {
            text-align: right;
        }

        .user-name {
            font-weight: 600;
            color: var(--text-primary);
        }

        .user-role {
            font-size: 0.8rem;
            color: var(--text-secondary);
        }

        .back-btn {
            background: var(--bg-tertiary);
            color: var(--text-primary);
            border: 1px solid var(--border);
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .back-btn:hover {
            background: var(--accent-1);
            color: white;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        .settings-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .settings-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        .reset-btn {
            background: var(--danger);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .reset-btn:hover {
            background: #dc2626;
            transform: translateY(-1px);
        }

        .message {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .message.success {
            background: rgba(63, 185, 80, 0.1);
            border: 1px solid var(--success);
            color: var(--success);
        }

        .message.error {
            background: rgba(248, 81, 73, 0.1);
            border: 1px solid var(--danger);
            color: var(--danger);
        }

        .settings-tabs {
            display: flex;
            gap: 5px;
            margin-bottom: 30px;
            background: var(--bg-secondary);
            padding: 5px;
            border-radius: 8px;
            border: 1px solid var(--border);
        }

        .tab-btn {
            flex: 1;
            padding: 12px 20px;
            background: transparent;
            color: var(--text-secondary);
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .tab-btn.active {
            background: var(--accent-1);
            color: white;
        }

        .tab-btn:hover:not(.active) {
            background: var(--bg-tertiary);
            color: var(--text-primary);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .settings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 25px;
        }

        .settings-card {
            background: var(--bg-secondary);
            border-radius: 12px;
            padding: 25px;
            border: 1px solid var(--border);
            transition: all 0.3s ease;
        }

        .settings-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .card-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border);
        }

        .card-icon {
            color: var(--accent-1);
            font-size: 1.2rem;
        }

        .card-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-primary);
        }

        .form-input {
            width: 100%;
            padding: 12px 15px;
            background: var(--bg-tertiary);
            border: 1px solid var(--border);
            border-radius: 6px;
            color: var(--text-primary);
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--accent-1);
            box-shadow: 0 0 0 3px rgba(88, 166, 255, 0.1);
        }

        .form-select {
            width: 100%;
            padding: 12px 15px;
            background: var(--bg-tertiary);
            border: 1px solid var(--border);
            border-radius: 6px;
            color: var(--text-primary);
            font-size: 0.9rem;
            cursor: pointer;
        }

        .form-textarea {
            width: 100%;
            padding: 12px 15px;
            background: var(--bg-tertiary);
            border: 1px solid var(--border);
            border-radius: 6px;
            color: var(--text-primary);
            font-size: 0.9rem;
            min-height: 100px;
            resize: vertical;
        }

        .form-checkbox {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }

        .checkbox-input {
            width: 18px;
            height: 18px;
            accent-color: var(--accent-1);
        }

        .checkbox-label {
            color: var(--text-primary);
            font-size: 0.9rem;
        }

        .form-description {
            font-size: 0.8rem;
            color: var(--text-secondary);
            margin-top: 5px;
            font-style: italic;
        }

        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid var(--border);
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: var(--accent-1);
            color: white;
        }

        .btn-primary:hover {
            background: var(--accent-hover);
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: var(--bg-tertiary);
            color: var(--text-primary);
            border: 1px solid var(--border);
        }

        .btn-secondary:hover {
            background: var(--accent-1);
            color: white;
        }

        .btn-danger {
            background: var(--danger);
            color: white;
        }

        .btn-danger:hover {
            background: #dc2626;
            transform: translateY(-1px);
        }

        .settings-info {
            background: var(--bg-secondary);
            border-radius: 12px;
            padding: 20px;
            border: 1px solid var(--border);
            margin-bottom: 30px;
        }

        .info-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .info-text {
            color: var(--text-secondary);
            line-height: 1.6;
        }

        @media (max-width: 768px) {
            .settings-grid {
                grid-template-columns: 1fr;
            }
            
            .settings-header {
                flex-direction: column;
                gap: 20px;
                align-items: flex-start;
            }
            
            .settings-tabs {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <div class="header-content">
            <div class="admin-title">
                <i class="fas fa-cog"></i> System Settings
            </div>
            <div class="admin-user">
                <div class="user-info">
                    <div class="user-name"><?php echo htmlspecialchars($adminUsername); ?></div>
                    <div class="user-role">System Administrator</div>
                </div>
                <a href="AdminDashboard.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="settings-header">
            <h1 class="settings-title">System Configuration</h1>
            <button class="reset-btn" onclick="resetAllSettings()">
                <i class="fas fa-undo"></i> Reset to Defaults
            </button>
        </div>

        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="settings-info">
            <div class="info-title">
                <i class="fas fa-info-circle"></i>
                Settings Management
            </div>
            <div class="info-text">
                Configure system-wide settings for your CandiHire platform. Changes are applied immediately and affect all users. 
                Use the tabs below to navigate between different setting categories.
            </div>
        </div>

        <div class="settings-tabs">
            <button class="tab-btn active" onclick="showTab('general')">
                <i class="fas fa-globe"></i> General
            </button>
            <button class="tab-btn" onclick="showTab('email')">
                <i class="fas fa-envelope"></i> Email
            </button>
            <button class="tab-btn" onclick="showTab('security')">
                <i class="fas fa-shield-alt"></i> Security
            </button>
            <button class="tab-btn" onclick="showTab('exams')">
                <i class="fas fa-clipboard-list"></i> Exams
            </button>
            <button class="tab-btn" onclick="showTab('notifications')">
                <i class="fas fa-bell"></i> Notifications
            </button>
        </div>

        <!-- General Settings -->
        <div id="general" class="tab-content active">
            <form method="POST" action="">
                <input type="hidden" name="action" value="update_general">
                <div class="settings-grid">
                    <div class="settings-card">
                        <div class="card-header">
                            <i class="fas fa-globe card-icon"></i>
                            <h3 class="card-title">Site Information</h3>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Site Name</label>
                            <input type="text" name="site_name" class="form-input" 
                                   value="<?php echo htmlspecialchars($settings['general']['site_name']['value'] ?? 'CandiHire'); ?>">
                            <div class="form-description">Website name displayed in headers and titles</div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Site Description</label>
                            <textarea name="site_description" class="form-textarea"><?php echo htmlspecialchars($settings['general']['site_description']['value'] ?? ''); ?></textarea>
                            <div class="form-description">Meta description for SEO purposes</div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Site Keywords</label>
                            <input type="text" name="site_keywords" class="form-input" 
                                   value="<?php echo htmlspecialchars($settings['general']['site_keywords']['value'] ?? ''); ?>">
                            <div class="form-description">Comma-separated keywords for SEO</div>
                        </div>
                    </div>

                    <div class="settings-card">
                        <div class="card-header">
                            <i class="fas fa-cog card-icon"></i>
                            <h3 class="card-title">System Configuration</h3>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Admin Email</label>
                            <input type="email" name="admin_email" class="form-input" 
                                   value="<?php echo htmlspecialchars($settings['general']['admin_email']['value'] ?? 'admin@candihire.com'); ?>">
                            <div class="form-description">Primary administrator email address</div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Timezone</label>
                            <select name="timezone" class="form-select">
                                <option value="UTC" <?php echo ($settings['general']['timezone']['value'] ?? 'UTC') === 'UTC' ? 'selected' : ''; ?>>UTC</option>
                                <option value="America/New_York" <?php echo ($settings['general']['timezone']['value'] ?? '') === 'America/New_York' ? 'selected' : ''; ?>>Eastern Time</option>
                                <option value="America/Chicago" <?php echo ($settings['general']['timezone']['value'] ?? '') === 'America/Chicago' ? 'selected' : ''; ?>>Central Time</option>
                                <option value="America/Denver" <?php echo ($settings['general']['timezone']['value'] ?? '') === 'America/Denver' ? 'selected' : ''; ?>>Mountain Time</option>
                                <option value="America/Los_Angeles" <?php echo ($settings['general']['timezone']['value'] ?? '') === 'America/Los_Angeles' ? 'selected' : ''; ?>>Pacific Time</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Currency</label>
                            <select name="currency" class="form-select">
                                <option value="USD" <?php echo ($settings['general']['currency']['value'] ?? 'USD') === 'USD' ? 'selected' : ''; ?>>USD - US Dollar</option>
                                <option value="EUR" <?php echo ($settings['general']['currency']['value'] ?? '') === 'EUR' ? 'selected' : ''; ?>>EUR - Euro</option>
                                <option value="GBP" <?php echo ($settings['general']['currency']['value'] ?? '') === 'GBP' ? 'selected' : ''; ?>>GBP - British Pound</option>
                                <option value="CAD" <?php echo ($settings['general']['currency']['value'] ?? '') === 'CAD' ? 'selected' : ''; ?>>CAD - Canadian Dollar</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Language</label>
                            <select name="language" class="form-select">
                                <option value="en" <?php echo ($settings['general']['language']['value'] ?? 'en') === 'en' ? 'selected' : ''; ?>>English</option>
                                <option value="es" <?php echo ($settings['general']['language']['value'] ?? '') === 'es' ? 'selected' : ''; ?>>Spanish</option>
                                <option value="fr" <?php echo ($settings['general']['language']['value'] ?? '') === 'fr' ? 'selected' : ''; ?>>French</option>
                                <option value="de" <?php echo ($settings['general']['language']['value'] ?? '') === 'de' ? 'selected' : ''; ?>>German</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save General Settings
                    </button>
                </div>
            </form>
        </div>

        <!-- Email Settings -->
        <div id="email" class="tab-content">
            <form method="POST" action="">
                <input type="hidden" name="action" value="update_email">
                <div class="settings-grid">
                    <div class="settings-card">
                        <div class="card-header">
                            <i class="fas fa-server card-icon"></i>
                            <h3 class="card-title">SMTP Configuration</h3>
                        </div>
                        <div class="form-group">
                            <label class="form-label">SMTP Host</label>
                            <input type="text" name="smtp_host" class="form-input" 
                                   value="<?php echo htmlspecialchars($settings['email']['smtp_host']['value'] ?? ''); ?>">
                            <div class="form-description">SMTP server hostname (e.g., smtp.gmail.com)</div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">SMTP Port</label>
                            <input type="number" name="smtp_port" class="form-input" 
                                   value="<?php echo htmlspecialchars($settings['email']['smtp_port']['value'] ?? '587'); ?>">
                            <div class="form-description">SMTP server port (587 for TLS, 465 for SSL)</div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">SMTP Username</label>
                            <input type="text" name="smtp_username" class="form-input" 
                                   value="<?php echo htmlspecialchars($settings['email']['smtp_username']['value'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">SMTP Password</label>
                            <input type="password" name="smtp_password" class="form-input" 
                                   value="<?php echo htmlspecialchars($settings['email']['smtp_password']['value'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Encryption</label>
                            <select name="smtp_encryption" class="form-select">
                                <option value="tls" <?php echo ($settings['email']['smtp_encryption']['value'] ?? 'tls') === 'tls' ? 'selected' : ''; ?>>TLS</option>
                                <option value="ssl" <?php echo ($settings['email']['smtp_encryption']['value'] ?? '') === 'ssl' ? 'selected' : ''; ?>>SSL</option>
                                <option value="none" <?php echo ($settings['email']['smtp_encryption']['value'] ?? '') === 'none' ? 'selected' : ''; ?>>None</option>
                            </select>
                        </div>
                    </div>

                    <div class="settings-card">
                        <div class="card-header">
                            <i class="fas fa-envelope card-icon"></i>
                            <h3 class="card-title">Email Settings</h3>
                        </div>
                        <div class="form-group">
                            <label class="form-label">From Email</label>
                            <input type="email" name="from_email" class="form-input" 
                                   value="<?php echo htmlspecialchars($settings['email']['from_email']['value'] ?? ''); ?>">
                            <div class="form-description">Default sender email address</div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">From Name</label>
                            <input type="text" name="from_name" class="form-input" 
                                   value="<?php echo htmlspecialchars($settings['email']['from_name']['value'] ?? 'CandiHire'); ?>">
                            <div class="form-description">Default sender name</div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email Signature</label>
                            <textarea name="email_signature" class="form-textarea"><?php echo htmlspecialchars($settings['email']['email_signature']['value'] ?? ''); ?></textarea>
                            <div class="form-description">Default signature for outgoing emails</div>
                        </div>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Email Settings
                    </button>
                </div>
            </form>
        </div>

        <!-- Security Settings -->
        <div id="security" class="tab-content">
            <form method="POST" action="">
                <input type="hidden" name="action" value="update_security">
                <div class="settings-grid">
                    <div class="settings-card">
                        <div class="card-header">
                            <i class="fas fa-lock card-icon"></i>
                            <h3 class="card-title">Password Security</h3>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Minimum Password Length</label>
                            <input type="number" name="password_min_length" class="form-input" min="6" max="32"
                                   value="<?php echo htmlspecialchars($settings['security']['password_min_length']['value'] ?? '8'); ?>">
                        </div>
                        <div class="form-checkbox">
                            <input type="checkbox" name="password_require_special" class="checkbox-input" value="1"
                                   <?php echo ($settings['security']['password_require_special']['value'] ?? '1') === '1' ? 'checked' : ''; ?>>
                            <label class="checkbox-label">Require special characters in passwords</label>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Session Timeout (seconds)</label>
                            <input type="number" name="session_timeout" class="form-input" min="300" max="86400"
                                   value="<?php echo htmlspecialchars($settings['security']['session_timeout']['value'] ?? '3600'); ?>">
                        </div>
                    </div>

                    <div class="settings-card">
                        <div class="card-header">
                            <i class="fas fa-shield-alt card-icon"></i>
                            <h3 class="card-title">Account Security</h3>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Max Login Attempts</label>
                            <input type="number" name="max_login_attempts" class="form-input" min="3" max="10"
                                   value="<?php echo htmlspecialchars($settings['security']['max_login_attempts']['value'] ?? '5'); ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Lockout Duration (seconds)</label>
                            <input type="number" name="lockout_duration" class="form-input" min="300" max="3600"
                                   value="<?php echo htmlspecialchars($settings['security']['lockout_duration']['value'] ?? '900'); ?>">
                        </div>
                        <div class="form-checkbox">
                            <input type="checkbox" name="require_email_verification" class="checkbox-input" value="1"
                                   <?php echo ($settings['security']['require_email_verification']['value'] ?? '1') === '1' ? 'checked' : ''; ?>>
                            <label class="checkbox-label">Require email verification for new accounts</label>
                        </div>
                        <div class="form-checkbox">
                            <input type="checkbox" name="enable_2fa" class="checkbox-input" value="1"
                                   <?php echo ($settings['security']['enable_2fa']['value'] ?? '0') === '1' ? 'checked' : ''; ?>>
                            <label class="checkbox-label">Enable two-factor authentication</label>
                        </div>
                    </div>

                    <div class="settings-card">
                        <div class="card-header">
                            <i class="fas fa-upload card-icon"></i>
                            <h3 class="card-title">File Upload Security</h3>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Allowed File Types</label>
                            <input type="text" name="allowed_file_types" class="form-input" 
                                   value="<?php echo htmlspecialchars($settings['security']['allowed_file_types']['value'] ?? 'pdf,doc,docx,jpg,png'); ?>">
                            <div class="form-description">Comma-separated list of allowed file extensions</div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Max File Size (bytes)</label>
                            <input type="number" name="max_file_size" class="form-input" min="1048576" max="52428800"
                                   value="<?php echo htmlspecialchars($settings['security']['max_file_size']['value'] ?? '10485760'); ?>">
                            <div class="form-description">Maximum file upload size in bytes (10MB = 10485760)</div>
                        </div>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Security Settings
                    </button>
                </div>
            </form>
        </div>

        <!-- Exam Settings -->
        <div id="exams" class="tab-content">
            <form method="POST" action="">
                <input type="hidden" name="action" value="update_exam">
                <div class="settings-grid">
                    <div class="settings-card">
                        <div class="card-header">
                            <i class="fas fa-clock card-icon"></i>
                            <h3 class="card-title">Exam Timing</h3>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Default Exam Timeout (seconds)</label>
                            <input type="number" name="exam_timeout" class="form-input" min="300" max="14400"
                                   value="<?php echo htmlspecialchars($settings['exams']['exam_timeout']['value'] ?? '3600'); ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Default Exam Duration (minutes)</label>
                            <input type="number" name="default_exam_duration" class="form-input" min="15" max="240"
                                   value="<?php echo htmlspecialchars($settings['exams']['default_exam_duration']['value'] ?? '60'); ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Max Exam Attempts</label>
                            <input type="number" name="max_exam_attempts" class="form-input" min="1" max="10"
                                   value="<?php echo htmlspecialchars($settings['exams']['max_exam_attempts']['value'] ?? '3'); ?>">
                        </div>
                        <div class="form-checkbox">
                            <input type="checkbox" name="exam_auto_submit" class="checkbox-input" value="1"
                                   <?php echo ($settings['exams']['exam_auto_submit']['value'] ?? '1') === '1' ? 'checked' : ''; ?>>
                            <label class="checkbox-label">Automatically submit exam when time expires</label>
                        </div>
                    </div>

                    <div class="settings-card">
                        <div class="card-header">
                            <i class="fas fa-cog card-icon"></i>
                            <h3 class="card-title">Exam Behavior</h3>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Default Passing Score (%)</label>
                            <input type="number" name="passing_score" class="form-input" min="0" max="100"
                                   value="<?php echo htmlspecialchars($settings['exams']['passing_score']['value'] ?? '70'); ?>">
                        </div>
                        <div class="form-checkbox">
                            <input type="checkbox" name="show_correct_answers" class="checkbox-input" value="1"
                                   <?php echo ($settings['exams']['show_correct_answers']['value'] ?? '1') === '1' ? 'checked' : ''; ?>>
                            <label class="checkbox-label">Show correct answers after exam completion</label>
                        </div>
                        <div class="form-checkbox">
                            <input type="checkbox" name="randomize_questions" class="checkbox-input" value="1"
                                   <?php echo ($settings['exams']['randomize_questions']['value'] ?? '1') === '1' ? 'checked' : ''; ?>>
                            <label class="checkbox-label">Randomize question order in exams</label>
                        </div>
                        <div class="form-checkbox">
                            <input type="checkbox" name="randomize_options" class="checkbox-input" value="1"
                                   <?php echo ($settings['exams']['randomize_options']['value'] ?? '1') === '1' ? 'checked' : ''; ?>>
                            <label class="checkbox-label">Randomize answer options in exams</label>
                        </div>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Exam Settings
                    </button>
                </div>
            </form>
        </div>

        <!-- Notification Settings -->
        <div id="notifications" class="tab-content">
            <form method="POST" action="">
                <input type="hidden" name="action" value="update_notification">
                <div class="settings-grid">
                    <div class="settings-card">
                        <div class="card-header">
                            <i class="fas fa-bell card-icon"></i>
                            <h3 class="card-title">Reminder Settings</h3>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Interview Reminder (hours before)</label>
                            <input type="number" name="interview_reminder_hours" class="form-input" min="1" max="168"
                                   value="<?php echo htmlspecialchars($settings['notifications']['interview_reminder_hours']['value'] ?? '24'); ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Exam Reminder (hours before)</label>
                            <input type="number" name="exam_reminder_hours" class="form-input" min="1" max="24"
                                   value="<?php echo htmlspecialchars($settings['notifications']['exam_reminder_hours']['value'] ?? '2'); ?>">
                        </div>
                    </div>

                    <div class="settings-card">
                        <div class="card-header">
                            <i class="fas fa-toggle-on card-icon"></i>
                            <h3 class="card-title">Notification Types</h3>
                        </div>
                        <div class="form-checkbox">
                            <input type="checkbox" name="application_notification" class="checkbox-input" value="1"
                                   <?php echo ($settings['notifications']['application_notification']['value'] ?? '1') === '1' ? 'checked' : ''; ?>>
                            <label class="checkbox-label">Application status notifications</label>
                        </div>
                        <div class="form-checkbox">
                            <input type="checkbox" name="interview_notification" class="checkbox-input" value="1"
                                   <?php echo ($settings['notifications']['interview_notification']['value'] ?? '1') === '1' ? 'checked' : ''; ?>>
                            <label class="checkbox-label">Interview notifications</label>
                        </div>
                        <div class="form-checkbox">
                            <input type="checkbox" name="exam_notification" class="checkbox-input" value="1"
                                   <?php echo ($settings['notifications']['exam_notification']['value'] ?? '1') ? 'checked' : ''; ?>>
                            <label class="checkbox-label">Exam notifications</label>
                        </div>
                        <div class="form-checkbox">
                            <input type="checkbox" name="system_maintenance_notification" class="checkbox-input" value="1"
                                   <?php echo ($settings['notifications']['system_maintenance_notification']['value'] ?? '1') === '1' ? 'checked' : ''; ?>>
                            <label class="checkbox-label">System maintenance notifications</label>
                        </div>
                    </div>

                    <div class="settings-card">
                        <div class="card-header">
                            <i class="fas fa-envelope card-icon"></i>
                            <h3 class="card-title">Delivery Methods</h3>
                        </div>
                        <div class="form-checkbox">
                            <input type="checkbox" name="email_notifications" class="checkbox-input" value="1"
                                   <?php echo ($settings['notifications']['email_notifications']['value'] ?? '1') === '1' ? 'checked' : ''; ?>>
                            <label class="checkbox-label">Email notifications</label>
                        </div>
                        <div class="form-checkbox">
                            <input type="checkbox" name="push_notifications" class="checkbox-input" value="1"
                                   <?php echo ($settings['notifications']['push_notifications']['value'] ?? '0') === '1' ? 'checked' : ''; ?>>
                            <label class="checkbox-label">Push notifications (browser)</label>
                        </div>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Notification Settings
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showTab(tabName) {
            // Hide all tab contents
            const tabContents = document.querySelectorAll('.tab-content');
            tabContents.forEach(content => content.classList.remove('active'));
            
            // Remove active class from all tab buttons
            const tabButtons = document.querySelectorAll('.tab-btn');
            tabButtons.forEach(button => button.classList.remove('active'));
            
            // Show selected tab content
            document.getElementById(tabName).classList.add('active');
            
            // Add active class to clicked button
            event.target.classList.add('active');
        }

        function resetAllSettings() {
            if (confirm('Are you sure you want to reset all settings to their default values? This action cannot be undone.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '';
                
                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'reset_settings';
                
                form.appendChild(actionInput);
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Form validation
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    const requiredFields = form.querySelectorAll('input[required], select[required], textarea[required]');
                    let isValid = true;
                    
                    requiredFields.forEach(field => {
                        if (!field.value.trim()) {
                            isValid = false;
                            field.style.borderColor = 'var(--danger)';
                        } else {
                            field.style.borderColor = 'var(--border)';
                        }
                    });
                    
                    if (!isValid) {
                        e.preventDefault();
                        alert('Please fill in all required fields.');
                    }
                });
            });
        });
    </script>
</body>
</html>

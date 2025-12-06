<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Settings - CandiHire Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/AdminSettings.css') }}">
</head>
<body>
    <div class="admin-header">
        <div class="header-content">
            <div class="admin-title">
                <i class="fas fa-cog"></i> System Settings
            </div>
            <div class="admin-user">
                <div class="user-info">
                    <div class="user-name">{{ $adminUsername ?? 'Admin' }}</div>
                    <div class="user-role">System Administrator</div>
                </div>
                <a href="{{ url('admin/dashboard') }}" class="back-btn">
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

        @if(session('message'))
            <div class="message {{ session('messageType') == 'success' ? 'success' : 'error' }}">
                <i class="fas fa-{{ session('messageType') == 'success' ? 'check-circle' : 'exclamation-triangle' }}"></i>
                {{ session('message') }}
            </div>
        @endif

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
                @csrf
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
                                   value="{{ $settings['general']['site_name']['value'] ?? 'CandiHire' }}">
                            <div class="form-description">Website name displayed in headers and titles</div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Site Description</label>
                            <textarea name="site_description" class="form-textarea">{{ $settings['general']['site_description']['value'] ?? '' }}</textarea>
                            <div class="form-description">Meta description for SEO purposes</div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Site Keywords</label>
                            <input type="text" name="site_keywords" class="form-input" 
                                   value="{{ $settings['general']['site_keywords']['value'] ?? '' }}">
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
                                   value="{{ $settings['general']['admin_email']['value'] ?? 'admin@candihire.com' }}">
                            <div class="form-description">Primary administrator email address</div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Timezone</label>
                            <select name="timezone" class="form-select">
                                <option value="UTC" {{ ($settings['general']['timezone']['value'] ?? 'UTC') === 'UTC' ? 'selected' : '' }}>UTC</option>
                                <option value="America/New_York" {{ ($settings['general']['timezone']['value'] ?? '') === 'America/New_York' ? 'selected' : '' }}>Eastern Time</option>
                                <option value="America/Chicago" {{ ($settings['general']['timezone']['value'] ?? '') === 'America/Chicago' ? 'selected' : '' }}>Central Time</option>
                                <option value="America/Denver" {{ ($settings['general']['timezone']['value'] ?? '') === 'America/Denver' ? 'selected' : '' }}>Mountain Time</option>
                                <option value="America/Los_Angeles" {{ ($settings['general']['timezone']['value'] ?? '') === 'America/Los_Angeles' ? 'selected' : '' }}>Pacific Time</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Currency</label>
                            <select name="currency" class="form-select">
                                <option value="USD" {{ ($settings['general']['currency']['value'] ?? 'USD') === 'USD' ? 'selected' : '' }}>USD - US Dollar</option>
                                <option value="EUR" {{ ($settings['general']['currency']['value'] ?? '') === 'EUR' ? 'selected' : '' }}>EUR - Euro</option>
                                <option value="GBP" {{ ($settings['general']['currency']['value'] ?? '') === 'GBP' ? 'selected' : '' }}>GBP - British Pound</option>
                                <option value="CAD" {{ ($settings['general']['currency']['value'] ?? '') === 'CAD' ? 'selected' : '' }}>CAD - Canadian Dollar</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Language</label>
                            <select name="language" class="form-select">
                                <option value="en" {{ ($settings['general']['language']['value'] ?? 'en') === 'en' ? 'selected' : '' }}>English</option>
                                <option value="es" {{ ($settings['general']['language']['value'] ?? '') === 'es' ? 'selected' : '' }}>Spanish</option>
                                <option value="fr" {{ ($settings['general']['language']['value'] ?? '') === 'fr' ? 'selected' : '' }}>French</option>
                                <option value="de" {{ ($settings['general']['language']['value'] ?? '') === 'de' ? 'selected' : '' }}>German</option>
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
                @csrf
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
                                   value="{{ $settings['email']['smtp_host']['value'] ?? '' }}">
                            <div class="form-description">SMTP server hostname (e.g., smtp.gmail.com)</div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">SMTP Port</label>
                            <input type="number" name="smtp_port" class="form-input" 
                                   value="{{ $settings['email']['smtp_port']['value'] ?? '587' }}">
                            <div class="form-description">SMTP server port (587 for TLS, 465 for SSL)</div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">SMTP Username</label>
                            <input type="text" name="smtp_username" class="form-input" 
                                   value="{{ $settings['email']['smtp_username']['value'] ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">SMTP Password</label>
                            <input type="password" name="smtp_password" class="form-input" 
                                   value="{{ $settings['email']['smtp_password']['value'] ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Encryption</label>
                            <select name="smtp_encryption" class="form-select">
                                <option value="tls" {{ ($settings['email']['smtp_encryption']['value'] ?? 'tls') === 'tls' ? 'selected' : '' }}>TLS</option>
                                <option value="ssl" {{ ($settings['email']['smtp_encryption']['value'] ?? '') === 'ssl' ? 'selected' : '' }}>SSL</option>
                                <option value="none" {{ ($settings['email']['smtp_encryption']['value'] ?? '') === 'none' ? 'selected' : '' }}>None</option>
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
                                   value="{{ $settings['email']['from_email']['value'] ?? '' }}">
                            <div class="form-description">Default sender email address</div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">From Name</label>
                            <input type="text" name="from_name" class="form-input" 
                                   value="{{ $settings['email']['from_name']['value'] ?? 'CandiHire' }}">
                            <div class="form-description">Default sender name</div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email Signature</label>
                            <textarea name="email_signature" class="form-textarea">{{ $settings['email']['email_signature']['value'] ?? '' }}</textarea>
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
                @csrf
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
                                   value="{{ $settings['security']['password_min_length']['value'] ?? '8' }}">
                        </div>
                        <div class="form-checkbox">
                            <input type="checkbox" name="password_require_special" class="checkbox-input" value="1"
                                   {{ ($settings['security']['password_require_special']['value'] ?? '1') === '1' ? 'checked' : '' }}>
                            <label class="checkbox-label">Require special characters in passwords</label>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Session Timeout (seconds)</label>
                            <input type="number" name="session_timeout" class="form-input" min="300" max="86400"
                                   value="{{ $settings['security']['session_timeout']['value'] ?? '3600' }}">
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
                                   value="{{ $settings['security']['max_login_attempts']['value'] ?? '5' }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Lockout Duration (seconds)</label>
                            <input type="number" name="lockout_duration" class="form-input" min="300" max="3600"
                                   value="{{ $settings['security']['lockout_duration']['value'] ?? '900' }}">
                        </div>
                        <div class="form-checkbox">
                            <input type="checkbox" name="require_email_verification" class="checkbox-input" value="1"
                                   {{ ($settings['security']['require_email_verification']['value'] ?? '1') === '1' ? 'checked' : '' }}>
                            <label class="checkbox-label">Require email verification for new accounts</label>
                        </div>
                        <div class="form-checkbox">
                            <input type="checkbox" name="enable_2fa" class="checkbox-input" value="1"
                                   {{ ($settings['security']['enable_2fa']['value'] ?? '0') === '1' ? 'checked' : '' }}>
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
                                   value="{{ $settings['security']['allowed_file_types']['value'] ?? 'pdf,doc,docx,jpg,png' }}">
                            <div class="form-description">Comma-separated list of allowed file extensions</div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Max File Size (bytes)</label>
                            <input type="number" name="max_file_size" class="form-input" min="1048576" max="52428800"
                                   value="{{ $settings['security']['max_file_size']['value'] ?? '10485760' }}">
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
                @csrf
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
                                   value="{{ $settings['exams']['exam_timeout']['value'] ?? '3600' }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Default Exam Duration (minutes)</label>
                            <input type="number" name="default_exam_duration" class="form-input" min="15" max="240"
                                   value="{{ $settings['exams']['default_exam_duration']['value'] ?? '60' }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Max Exam Attempts</label>
                            <input type="number" name="max_exam_attempts" class="form-input" min="1" max="10"
                                   value="{{ $settings['exams']['max_exam_attempts']['value'] ?? '3' }}">
                        </div>
                        <div class="form-checkbox">
                            <input type="checkbox" name="exam_auto_submit" class="checkbox-input" value="1"
                                   {{ ($settings['exams']['exam_auto_submit']['value'] ?? '1') === '1' ? 'checked' : '' }}>
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
                                   value="{{ $settings['exams']['passing_score']['value'] ?? '70' }}">
                        </div>
                        <div class="form-checkbox">
                            <input type="checkbox" name="show_correct_answers" class="checkbox-input" value="1"
                                   {{ ($settings['exams']['show_correct_answers']['value'] ?? '1') === '1' ? 'checked' : '' }}>
                            <label class="checkbox-label">Show correct answers after exam completion</label>
                        </div>
                        <div class="form-checkbox">
                            <input type="checkbox" name="randomize_questions" class="checkbox-input" value="1"
                                   {{ ($settings['exams']['randomize_questions']['value'] ?? '1') === '1' ? 'checked' : '' }}>
                            <label class="checkbox-label">Randomize question order in exams</label>
                        </div>
                        <div class="form-checkbox">
                            <input type="checkbox" name="randomize_options" class="checkbox-input" value="1"
                                   {{ ($settings['exams']['randomize_options']['value'] ?? '1') === '1' ? 'checked' : '' }}>
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
                @csrf
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
                                   value="{{ $settings['notifications']['interview_reminder_hours']['value'] ?? '24' }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Exam Reminder (hours before)</label>
                            <input type="number" name="exam_reminder_hours" class="form-input" min="1" max="24"
                                   value="{{ $settings['notifications']['exam_reminder_hours']['value'] ?? '2' }}">
                        </div>
                    </div>

                    <div class="settings-card">
                        <div class="card-header">
                            <i class="fas fa-toggle-on card-icon"></i>
                            <h3 class="card-title">Notification Types</h3>
                        </div>
                        <div class="form-checkbox">
                            <input type="checkbox" name="application_notification" class="checkbox-input" value="1"
                                   {{ ($settings['notifications']['application_notification']['value'] ?? '1') === '1' ? 'checked' : '' }}>
                            <label class="checkbox-label">Application status notifications</label>
                        </div>
                        <div class="form-checkbox">
                            <input type="checkbox" name="interview_notification" class="checkbox-input" value="1"
                                   {{ ($settings['notifications']['interview_notification']['value'] ?? '1') === '1' ? 'checked' : '' }}>
                            <label class="checkbox-label">Interview notifications</label>
                        </div>
                        <div class="form-checkbox">
                            <input type="checkbox" name="exam_notification" class="checkbox-input" value="1"
                                   {{ ($settings['notifications']['exam_notification']['value'] ?? '1') ? 'checked' : '' }}>
                            <label class="checkbox-label">Exam notifications</label>
                        </div>
                        <div class="form-checkbox">
                            <input type="checkbox" name="system_maintenance_notification" class="checkbox-input" value="1"
                                   {{ ($settings['notifications']['system_maintenance_notification']['value'] ?? '1') === '1' ? 'checked' : '' }}>
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
                                   {{ ($settings['notifications']['email_notifications']['value'] ?? '1') === '1' ? 'checked' : '' }}>
                            <label class="checkbox-label">Email notifications</label>
                        </div>
                        <div class="form-checkbox">
                            <input type="checkbox" name="push_notifications" class="checkbox-input" value="1"
                                   {{ ($settings['notifications']['push_notifications']['value'] ?? '0') === '1' ? 'checked' : '' }}>
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
            if (event) {
                event.target.closest('.tab-btn').classList.add('active');
            }
        }

        function resetAllSettings() {
            if (confirm('Are you sure you want to reset all settings to their default values? This action cannot be undone.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '';
                
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = '{{ csrf_token() }}';
                form.appendChild(csrfInput);

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

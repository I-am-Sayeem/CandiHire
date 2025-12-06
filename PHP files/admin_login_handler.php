<?php
// admin_login_handler.php - Admin Login Handler
session_start();

// Hardcoded admin credentials
$admin_username = 'Admin@candihire';
$admin_password = 'Admin123';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($username === $admin_username && $password === $admin_password) {
        // Set admin session
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $admin_username;
        $_SESSION['admin_id'] = 'admin_001';
        
        // Redirect to admin dashboard
        header('Location: AdminDashboard.php');
        exit;
    } else {
        $error_message = 'Invalid admin credentials. Please try again.';
    }
}

// If already logged in, redirect to dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: AdminDashboard.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - CandiHire</title>
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
            background: #0d1117;
            color: var(--text-primary);
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            background: var(--bg-secondary);
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            border: 1px solid var(--border);
            width: 100%;
            max-width: 400px;
        }

        .admin-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .admin-header h1 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 10px;
            background: linear-gradient(135deg, var(--accent-1), var(--accent-2));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .admin-header p {
            color: var(--text-secondary);
            font-size: 0.9rem;
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
            padding: 12px 16px;
            border: 2px solid var(--border);
            border-radius: 8px;
            background: var(--bg-tertiary);
            color: var(--text-primary);
            font-size: 1rem;
            transition: all 0.2s;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--accent-1);
            box-shadow: 0 0 0 3px rgba(88, 166, 255, 0.1);
        }

        .btn {
            width: 100%;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--accent-1), var(--accent-2));
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(88, 166, 255, 0.4);
        }

        .btn-secondary {
            background: transparent;
            color: var(--text-secondary);
            border: 1px solid var(--border);
            margin-top: 15px;
        }

        .btn-secondary:hover {
            background: var(--bg-tertiary);
            color: var(--text-primary);
        }

        .error-message {
            background: var(--danger);
            color: white;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }

        .admin-badge {
            display: inline-block;
            background: rgba(248, 81, 73, 0.1);
            color: var(--danger);
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="admin-header">
            <h1><i class="fas fa-shield-alt"></i> Admin Panel</h1>
            <p>System Administration Access</p>
            <span class="admin-badge">ADMIN ONLY</span>
        </div>

        <?php if (isset($error_message)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-triangle"></i>
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="username" class="form-label">
                    <i class="fas fa-user"></i> Admin Username
                </label>
                <input type="text" id="username" name="username" class="form-input" 
                       placeholder="Enter admin username" required>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">
                    <i class="fas fa-lock"></i> Password
                </label>
                <input type="password" id="password" name="password" class="form-input" 
                       placeholder="Enter admin password" required>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-sign-in-alt"></i> Login as Admin
            </button>
        </form>

        <a href="Login&Signup.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Main Login
        </a>
    </div>
</body>
</html>

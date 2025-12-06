<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - CandiHire</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/admin_login_handler.css') }}">
</head>
<body>
    <div class="login-container">
        <div class="admin-header">
            <h1><i class="fas fa-shield-alt"></i> Admin Panel</h1>
            <p>System Administration Access</p>
            <span class="admin-badge">ADMIN ONLY</span>
        </div>

        @if(isset($error_message))
            <div class="error-message">
                <i class="fas fa-exclamation-triangle"></i>
                {{ $error_message }}
            </div>
        @endif

        <form method="POST" action="">
            @csrf
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

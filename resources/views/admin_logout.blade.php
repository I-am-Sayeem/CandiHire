<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logged Out - CandiHire Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/AdminSettings.css') }}">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: var(--bg-primary);
        }
        .logout-container {
            background: var(--bg-secondary);
            padding: 40px;
            border-radius: 12px;
            text-align: center;
            border: 1px solid var(--border);
            max-width: 400px;
            width: 100%;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .logout-icon {
            font-size: 3rem;
            color: var(--accent-1);
            margin-bottom: 20px;
        }
        .logout-title {
            font-size: 1.5rem;
            color: var(--text-primary);
            margin-bottom: 10px;
            font-weight: 700;
        }
        .logout-message {
            color: var(--text-secondary);
            margin-bottom: 30px;
        }
        .btn-full {
            width: 100%;
            justify-content: center;
        }
    </style>
</head>
<body>
    <div class="logout-container">
        <div class="logout-icon">
            <i class="fas fa-sign-out-alt"></i>
        </div>
        <h1 class="logout-title">Successfully Logged Out</h1>
        <p class="logout-message">
            You have been securely logged out of the admin panel. 
            Thank you for using CandiHire.
        </p>
        
        <a href="{{ url('admin/login') }}" class="btn btn-primary btn-full">
            <i class="fas fa-sign-in-alt"></i> Return to Login
        </a>
    </div>
</body>
</html>

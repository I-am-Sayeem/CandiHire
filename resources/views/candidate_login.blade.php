<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candidate Login - CandiHire</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/Login_Signup.css') }}">
    <script src="{{ asset('js/notification_system.js') }}"></script>
    <style>
        /* Override generic styles to center the login form for this dedicated page */
        .main-content {
            justify-content: center;
        }
        .content-left {
            display: none; /* Hide the marketing text for this dedicated login page */
        }
        .auth-container {
            width: 100%;
            max-width: 450px;
        }
    </style>
</head>
<body>
    <div class="background-container" id="backgroundContainer">
        <div class="grid-pattern"></div>
    </div>
    
    <div class="header">
        <div class="container">
            <nav class="nav">
                <div class="logo">
                    <div class="logo-icon">
                        <img src="https://z-cdn-media.chatglm.cn/files/679b0056-b12f-411d-8a44-f81eb30411fa_CandiHire%20Logo.png?auth_key=1789498881-6b01dceb81c849e4b1fb0447e1d05a91-0-027ec66914332736d7f6859adb370481" alt="CandiHire Logo" onerror="this.style.display='none'">
                    </div>
                    <div class="logo-text">
                        <h1><span>Candi</span><span class="hire">Hire</span></h1>
                        <p>FIND MATCH HIRE</p>
                    </div>
                </div>
                <ul class="nav-menu">
                    <li><a href="{{ url('/') }}">HOME</a></li>
                </ul>
            </nav>
        </div>
    </div>

    <div class="container">
        <div class="main-content">
            <div class="auth-container">
                <div class="login-section" style="display: block;">
                    <div class="login-header">
                        <div class="login-title">Candidate Login</div>
                        <div class="login-subtitle">Sign in to your CandiHire candidate account</div>
                    </div>
                    
                    <form id="candidateLoginForm" method="POST" action="">
                        @csrf
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" name="email" placeholder="Enter your email address" required>
                        </div>
                        <div class="form-group">
                            <label>Password</label>
                            <input type="password" name="password" placeholder="Enter your password" required>
                        </div>
                        <div class="form-options">
                            <div class="checkbox-group">
                                <input type="checkbox" id="keepSignedIn" name="remember">
                                <label for="keepSignedIn">Keep me signed in</label>
                            </div>
                            <a href="#" class="forgot-link">Forgot Password?</a>
                        </div>
                        <button type="submit" class="sign-in-btn">
                            <i class="fas fa-lock"></i>
                            Secure Sign In
                        </button>
                    </form>

                    <div class="divider">or continue with</div>
                    
                    <div class="sso-section">
                        <div class="sso-buttons">
                            <a href="#" class="sso-btn" title="Google"><i class="fab fa-google"></i></a>
                            <a href="#" class="sso-btn" title="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                        </div>
                    </div>

                    <div class="create-account">
                        New to CandiHire? <a href="{{ url('register') }}">Create an Account</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Use existing notification system if available, otherwise simple alerts
        document.getElementById('candidateLoginForm').addEventListener('submit', function(e) {
            // This is a placeholder for the actual form submission logic
            // Since this is just the view conversion, we keep the form structure.
            // In a full implementation, this would post to a Laravel route.
        });

        // Background animation (copied from original to maintain visual consistency)
        (function() {
            try {
                const container = document.getElementById('backgroundContainer');
                if (container && CSS.supports('animation', 'test')) {
                    const orbCount = 8;
                    for (let i = 0; i < orbCount; i++) {
                        const orb = document.createElement('div');
                        orb.classList.add('gradient-orb', ['small', 'medium', 'large'][Math.floor(Math.random() * 3)]);
                        orb.style.left = (Math.random() * 120 - 10) + '%';
                        orb.style.animationDelay = (Math.random() * 15) + 's';
                        container.appendChild(orb);
                    }
                }
            } catch (e) { console.warn('Background animation failed', e); }
        })();
    </script>
</body>
</html>

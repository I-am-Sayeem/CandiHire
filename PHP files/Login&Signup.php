

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CandiHire - Find Match Hire</title>
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #0c1445 0%, #1a237e 50%, #283593 100%);
            min-height: 100vh;
            color: white;
            overflow-x: hidden;
            position: relative;
        }
        
        /* Professional Background Animations */
        .background-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: -1;
            pointer-events: none;
        }
        
        /* Subtle gradient orbs */
        .gradient-orb {
            position: absolute;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(30, 136, 229, 0.1), rgba(13, 71, 161, 0.05));
            animation: floatOrb linear infinite;
            filter: blur(40px);
        }
        
        .gradient-orb.large {
            width: 400px;
            height: 400px;
            animation-duration: 25s;
        }
        
        .gradient-orb.medium {
            width: 250px;
            height: 250px;
            animation-duration: 20s;
        }
        
        .gradient-orb.small {
            width: 150px;
            height: 150px;
            animation-duration: 15s;
        }
        
        @keyframes floatOrb {
            0% {
                transform: translateY(100vh) translateX(-10%) rotate(0deg);
                opacity: 0;
            }
            15% {
                opacity: 1;
            }
            85% {
                opacity: 1;
            }
            100% {
                transform: translateY(-100px) translateX(10%) rotate(360deg);
                opacity: 0;
            }
        }
        
        /* Geometric grid pattern */
        .grid-pattern {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                linear-gradient(rgba(30, 136, 229, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(30, 136, 229, 0.03) 1px, transparent 1px);
            background-size: 50px 50px;
            animation: gridMove 30s linear infinite;
        }
        
        @keyframes gridMove {
            0% {
                transform: translate(0, 0);
            }
            100% {
                transform: translate(50px, 50px);
            }
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
            position: relative;
            z-index: 1;
        }
        
        /* Header */
        .header {
            background: rgba(12, 20, 69, 0.95);
            padding: 15px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            position: sticky;
            top: 0;
            z-index: 100;
            backdrop-filter: blur(20px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            animation: slideDown 0.8s cubic-bezier(0.22, 0.61, 0.36, 1);
        }
        
        .nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-shrink: 0;
            animation: fadeInLeft 0.8s cubic-bezier(0.22, 0.61, 0.36, 1);
        }
        
        .logo-icon {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            transition: all 0.3s cubic-bezier(0.22, 0.61, 0.36, 1);
        }
        
        .logo-icon img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        
        .logo:hover .logo-icon {
            transform: scale(1.05);
            box-shadow: 0 6px 12px rgba(30, 136, 229, 0.4);
        }
        
        .logo-text h1 {
            font-size: 26px;
            font-weight: bold;
            line-height: 1;
        }
        
        .logo-text h1 span {
            color: #1e88e5;
        }
        
        .logo-text h1 .hire {
            color: #f59e0b;
            margin-left: -2px;
        }
        
        .logo-text p {
            font-size: 11px;
            color: #94a3b8;
            margin-top: 2px;
            letter-spacing: 1px;
        }
        
        .nav-menu {
            display: flex;
            list-style: none;
            gap: 35px;
            align-items: center;
            animation: fadeIn 1s cubic-bezier(0.22, 0.61, 0.36, 1);
        }
        
        .nav-menu a {
            color: #94a3b8;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s cubic-bezier(0.22, 0.61, 0.36, 1);
            padding: 8px 0;
            position: relative;
        }
        
        .nav-menu a:hover {
            color: white;
        }
        
        .nav-menu a::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: #1e88e5;
            transition: width 0.3s cubic-bezier(0.22, 0.61, 0.36, 1);
        }
        
        .nav-menu a:hover::after {
            width: 100%;
        }
        
        .search-section {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-shrink: 0;
            animation: fadeInRight 0.8s cubic-bezier(0.22, 0.61, 0.36, 1);
        }
        
        .search-box {
            position: relative;
        }
        
        .search-box input {
            background: rgba(12, 20, 69, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            padding: 12px 45px 12px 16px;
            color: white;
            font-size: 14px;
            width: 320px;
            transition: all 0.3s cubic-bezier(0.22, 0.61, 0.36, 1);
        }
        
        .search-box input:focus {
            outline: none;
            border-color: #1e88e5;
            box-shadow: 0 0 0 3px rgba(30, 136, 229, 0.1);
        }
        
        .search-box input::placeholder {
            color: #94a3b8;
        }
        
        .search-btn {
            background: linear-gradient(135deg, #1e88e5, #0d47a1);
            border: none;
            border-radius: 10px;
            padding: 12px 24px;
            color: white;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.22, 0.61, 0.36, 1);
            box-shadow: 0 4px 12px rgba(30, 136, 229, 0.3);
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .search-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.2);
            transition: left 0.5s cubic-bezier(0.22, 0.61, 0.36, 1);
        }
        
        .search-btn:hover::before {
            left: 100%;
        }
        
        .search-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(30, 136, 229, 0.4);
        }
        
        /* Main Content */
        .main-content {
            display: grid;
            grid-template-columns: 1fr 400px;
            align-items: start;
            gap: 80px;
            min-height: calc(100vh - 90px);
            padding: 80px 0;
        }
        
        .content-left {
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding-top: 40px;
        }
        
        .platform-tag {
            background: rgba(30, 136, 229, 0.15);
            border: 1px solid rgba(30, 136, 229, 0.3);
            border-radius: 25px;
            padding: 10px 20px;
            font-size: 13px;
            color: #90caf9;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
            margin-bottom: 35px;
            width: fit-content;
            animation: fadeInUp 0.8s cubic-bezier(0.22, 0.61, 0.36, 1) 0.2s both;
            transition: all 0.3s cubic-bezier(0.22, 0.61, 0.36, 1);
        }
        
        .platform-tag:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(30, 136, 229, 0.3);
        }
        
        .main-heading {
            font-size: 52px;
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 35px;
            letter-spacing: -0.02em;
            animation: fadeInUp 1.2s cubic-bezier(0.25, 0.46, 0.45, 0.94) 0.3s both;
        }
        
        .highlight-blue {
            color: #1e88e5;
            position: relative;
            display: inline-block;
        }
        
        .highlight-blue::after {
            content: '';
            position: absolute;
            bottom: 5px;
            left: 0;
            width: 100%;
            height: 8px;
            background: rgba(30, 136, 229, 0.2);
            z-index: -1;
            border-radius: 4px;
        }
        
        .highlight-orange {
            color: #f59e0b;
            position: relative;
            display: inline-block;
        }
        
        .highlight-orange::after {
            content: '';
            position: absolute;
            bottom: 5px;
            left: 0;
            width: 100%;
            height: 8px;
            background: rgba(245, 158, 11, 0.2);
            z-index: -1;
            border-radius: 4px;
        }
        
        .description {
            font-size: 17px;
            line-height: 1.7;
            color: #cbd5e1;
            margin-bottom: 50px;
            max-width: 580px;
            animation: fadeInUp 1.2s cubic-bezier(0.25, 0.46, 0.45, 0.94) 0.5s both;
        }
        
        .features {
            display: flex;
            flex-direction: column;
            gap: 20px;
            animation: fadeInUp 1.1s cubic-bezier(0.22, 0.61, 0.36, 1) 0.8s both;
        }
        
        .feature {
            display: flex;
            align-items: center;
            gap: 15px;
            color: #cbd5e1;
            font-size: 15px;
            padding: 12px 0;
            transition: all 0.3s cubic-bezier(0.22, 0.61, 0.36, 1);
            position: relative;
            overflow: hidden;
        }
        
        .feature::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(30, 136, 229, 0.1), transparent);
            transition: left 0.6s cubic-bezier(0.22, 0.61, 0.36, 1);
        }
        
        .feature:hover::before {
            left: 100%;
        }
        
        .feature:hover {
            transform: translateX(10px);
            color: white;
        }
        
        .feature-icon {
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, #1e88e5, #0d47a1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: bold;
            color: white;
            flex-shrink: 0;
            box-shadow: 0 3px 8px rgba(30, 136, 229, 0.3);
            transition: all 0.3s cubic-bezier(0.22, 0.61, 0.36, 1);
        }
        
        .feature:hover .feature-icon {
            transform: scale(1.1);
            box-shadow: 0 5px 12px rgba(30, 136, 229, 0.5);
        }
        
        /* Auth Container */
        .auth-container {
            position: relative;
        }
        
        /* Login Form */
        .login-section {
            background: rgba(12, 20, 69, 0.95);
            border-radius: 24px;
            padding: 45px;
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            position: relative;
            top: -2px;
            animation: fadeInUp 1.2s cubic-bezier(0.22, 0.61, 0.36, 1);
            transition: all 0.3s cubic-bezier(0.22, 0.61, 0.36, 1);
        }
        
        .login-section:hover {
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.4);
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 35px;
        }
        
        .login-title {
            background: linear-gradient(135deg, #1e88e5, #0d47a1);
            color: white;
            padding: 14px 28px;
            border-radius: 30px;
            font-size: 17px;
            font-weight: 700;
            display: inline-block;
            margin-bottom: 18px;
            box-shadow: 0 4px 12px rgba(30, 136, 229, 0.3);
            transition: all 0.3s cubic-bezier(0.22, 0.61, 0.36, 1);
        }
        
        .login-title:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(30, 136, 229, 0.4);
        }
        
        .login-subtitle {
            color: #94a3b8;
            font-size: 15px;
            line-height: 1.5;
        }
        
        .form-group {
            margin-bottom: 25px;
            animation: fadeInUp 1.3s cubic-bezier(0.22, 0.61, 0.36, 1);
        }
        
        .form-group label {
            display: block;
            color: #cbd5e1;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 10px;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            background: rgba(12, 20, 69, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            padding: 15px 18px;
            color: white;
            font-size: 15px;
            transition: all 0.3s cubic-bezier(0.22, 0.61, 0.36, 1);
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #1e88e5;
            box-shadow: 0 0 0 3px rgba(30, 136, 229, 0.1);
        }
        
        .form-group input::placeholder,
        .form-group textarea::placeholder {
            color: #64748b;
        }
        
        .form-group select option {
            background: #0c1445;
            color: white;
        }
        
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            font-size: 14px;
            animation: fadeInUp 1.4s cubic-bezier(0.22, 0.61, 0.36, 1);
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .checkbox-group input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #1e88e5;
        }
        
        .checkbox-group label {
            margin: 0;
            color: #cbd5e1;
            cursor: pointer;
        }
        
        .forgot-link {
            color: #1e88e5;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s cubic-bezier(0.22, 0.61, 0.36, 1);
        }
        
        .forgot-link:hover {
            color: #0d47a1;
            text-decoration: underline;
        }
        
        .sign-in-btn,
        .sign-up-btn {
            width: 100%;
            background: linear-gradient(135deg, #1e88e5, #0d47a1);
            border: none;
            border-radius: 12px;
            padding: 18px;
            color: white;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.22, 0.61, 0.36, 1);
            margin-bottom: 25px;
            box-shadow: 0 6px 20px rgba(30, 136, 229, 0.3);
            position: relative;
            overflow: hidden;
            animation: fadeInUp 1.5s cubic-bezier(0.22, 0.61, 0.36, 1);
            will-change: transform, box-shadow;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .sign-in-btn::before,
        .sign-up-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.2);
            transition: left 0.5s cubic-bezier(0.22, 0.61, 0.36, 1);
        }
        
        .sign-in-btn:hover::before,
        .sign-up-btn:hover::before {
            left: 100%;
        }
        
        .sign-in-btn:hover,
        .sign-up-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(30, 136, 229, 0.5);
        }
        
        /* Admin Login Button */
        .admin-login-section {
            position: absolute;
            bottom: 20px;
            right: 20px;
            z-index: 10;
        }
        
        .admin-login-btn {
            display: inline-block;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            padding: 6px 12px;
            font-size: 10px;
            font-weight: 400;
            transition: all 0.3s cubic-bezier(0.22, 0.61, 0.36, 1);
            box-shadow: 0 2px 8px rgba(245, 158, 11, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.1);
            animation: fadeInUp 1.7s cubic-bezier(0.22, 0.61, 0.36, 1);
        }
        
        .admin-login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(245, 158, 11, 0.5);
            background: linear-gradient(135deg, #d97706, #b45309);
        }
        
        .admin-login-btn i {
            margin-right: 4px;
        }
        
        .divider {
            text-align: center;
            color: #64748b;
            font-size: 14px;
            margin: 25px 0;
            position: relative;
            animation: fadeInUp 1.6s cubic-bezier(0.22, 0.61, 0.36, 1);
        }
        
        .divider::before,
        .divider::after {
            content: '';
            position: absolute;
            top: 50%;
            width: 45%;
            height: 1px;
            background: rgba(255, 255, 255, 0.1);
        }
        
        .divider::before {
            left: 0;
        }
        
        .divider::after {
            right: 0;
        }
        
        .create-account,
        .back-to-login {
            text-align: center;
            margin-bottom: 25px;
            animation: fadeInUp 1.7s cubic-bezier(0.22, 0.61, 0.36, 1);
        }
        
        .create-account a,
        .back-to-login a {
            color: #1e88e5;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s cubic-bezier(0.22, 0.61, 0.36, 1);
        }
        
        .create-account a:hover,
        .back-to-login a:hover {
            color: #0d47a1;
            text-decoration: underline;
        }
        
        .sso-section {
            text-align: center;
            animation: fadeInUp 1.8s cubic-bezier(0.22, 0.61, 0.36, 1);
        }
        
        .sso-title {
            color: #64748b;
            font-size: 14px;
            margin-bottom: 18px;
        }
        
        .sso-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
        }
        
        .sso-btn {
            width: 50px;
            height: 50px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.22, 0.61, 0.36, 1);
            text-decoration: none;
            font-size: 18px;
            font-weight: bold;
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .sso-btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.5s, height 0.5s;
        }
        
        .sso-btn:hover::before {
            width: 100px;
            height: 100px;
        }
        
        .sso-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        /* Sign Up Form */
        .signup-section {
            background: rgba(12, 20, 69, 0.95);
            border-radius: 24px;
            padding: 45px;
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            position: sticky;
            top: 120px;
            display: none;
            animation: fadeInUp 1.2s cubic-bezier(0.22, 0.61, 0.36, 1);
            transition: all 0.3s cubic-bezier(0.22, 0.61, 0.36, 1);
        }
        
        .signup-section:hover {
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.4);
        }
        
        .signup-section.show {
            display: block;
        }
        
        .signup-tabs {
            display: flex;
            margin-bottom: 30px;
            border-radius: 12px;
            background: rgba(12, 20, 69, 0.8);
            padding: 5px;
        }
        
        .signup-tab {
            flex: 1;
            padding: 12px;
            text-align: center;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s cubic-bezier(0.22, 0.61, 0.36, 1);
        }
        
        .signup-tab.active {
            background: linear-gradient(135deg, #1e88e5, #0d47a1);
            color: white;
            box-shadow: 0 4px 12px rgba(30, 136, 229, 0.3);
        }
        
        .signup-tab:not(.active) {
            color: #94a3b8;
        }
        
        .signup-tab:not(.active):hover {
            color: white;
            background: rgba(30, 136, 229, 0.1);
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
            animation: fadeIn 0.5s ease;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .skills-container {
            position: relative;
        }
        
        .skills-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 10px;
        }
        
        .skill-tag {
            background: rgba(30, 136, 229, 0.2);
            border: 1px solid rgba(30, 136, 229, 0.3);
            border-radius: 20px;
            padding: 6px 12px;
            font-size: 13px;
            color: #90caf9;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .skill-tag .remove-skill {
            cursor: pointer;
            font-weight: bold;
            color: #ef4444;
        }
        
        /* New Sections Styles */
        
        /* Features Section */
        .features-section {
            padding: 100px 0;
            background: linear-gradient(135deg, #0c1445 0%, #1a237e 50%, #283593 100%);
            position: relative;
            overflow: hidden;
            scroll-margin-top: 100px;
        }
        
        .features-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="1" fill="rgba(255,255,255,0.03)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.5;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
            margin-top: 60px;
            position: relative;
            z-index: 1;
        }
        
        .feature-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 40px 30px;
            text-align: center;
            transition: all 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            position: relative;
            overflow: hidden;
        }
        
        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transition: left 0.6s ease;
        }
        
        .feature-card:hover::before {
            left: 100%;
        }
        
        .feature-card:hover {
            transform: translateY(-15px) scale(1.02);
            box-shadow: 0 25px 50px rgba(30, 136, 229, 0.3);
            border-color: rgba(30, 136, 229, 0.4);
        }
        
        .feature-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #1e88e5, #0d47a1);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            font-size: 32px;
            color: white;
            transition: all 0.3s ease;
        }
        
        .feature-card:hover .feature-icon {
            transform: scale(1.1) rotate(5deg);
        }
        
        .feature-card h3 {
            color: white;
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 15px;
        }
        
        .feature-card p {
            color: #94a3b8;
            line-height: 1.6;
            margin-bottom: 20px;
        }
        
        .feature-highlight {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }
        
        /* Resources Section */
        .resources-section {
            padding: 100px 0;
            background: #0f172a;
            position: relative;
        }
        
        .resources-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 40px;
            margin-top: 60px;
        }
        
        .resource-category {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            padding: 40px 30px;
            transition: all 0.3s ease;
        }
        
        .resource-category:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
            border-color: rgba(30, 136, 229, 0.3);
        }
        
        .category-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #1e88e5, #0d47a1);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 25px;
            font-size: 24px;
            color: white;
        }
        
        .resource-category h3 {
            color: white;
            font-size: 22px;
            font-weight: 600;
            margin-bottom: 20px;
        }
        
        .resource-category ul {
            list-style: none;
            padding: 0;
        }
        
        .resource-category li {
            margin-bottom: 12px;
        }
        
        .resource-category a {
            color: #94a3b8;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: color 0.3s ease;
        }
        
        .resource-category a:hover {
            color: #1e88e5;
        }
        
        .resource-category a i {
            font-size: 16px;
            width: 20px;
        }
        
        /* About Section */
        .about-section {
            padding: 100px 0;
            background: linear-gradient(135deg, #1e237e 0%, #0c1445 100%);
            position: relative;
            scroll-margin-top: 100px; /* Add scroll margin for better positioning */
        }
        
        .about-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 80px;
            align-items: center;
        }
        
        .about-description {
            color: #94a3b8;
            font-size: 18px;
            line-height: 1.8;
            margin-bottom: 40px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 30px;
            margin-bottom: 40px;
        }
        
        .stat-item {
            text-align: center;
            padding: 20px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .stat-number {
            font-size: 36px;
            font-weight: bold;
            color: #1e88e5;
            margin-bottom: 8px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .stat-number::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(30, 136, 229, 0.3), transparent);
            transition: left 0.6s ease;
        }
        
        .stat-item:hover .stat-number::before {
            left: 100%;
        }
        
        .stat-item:hover .stat-number {
            transform: scale(1.1);
            color: #0d47a1;
        }
        
        .stat-number.animating {
            animation: pulse 0.5s ease-in-out;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .stat-label {
            color: #94a3b8;
            font-size: 14px;
        }
        
        .mission-statement {
            background: rgba(255, 255, 255, 0.03);
            padding: 30px;
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .mission-statement h4 {
            color: white;
            font-size: 20px;
            margin-bottom: 15px;
        }
        
        .mission-statement p {
            color: #94a3b8;
            line-height: 1.6;
        }
        
        .team-info {
            background: rgba(255, 255, 255, 0.03);
            padding: 30px;
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            margin-top: 30px;
        }
        
        .team-info h4 {
            color: #1e88e5;
            font-size: 20px;
            margin-bottom: 15px;
            text-align: center;
        }
        
        .team-members p {
            color: #cbd5e1;
            font-size: 16px;
            margin-bottom: 15px;
            text-align: center;
        }
        
        .team-members ul {
            list-style: none;
            padding: 0;
            margin: 0;
            text-align: center;
        }
        
        .team-members li {
            color: #94a3b8;
            font-size: 15px;
            margin-bottom: 8px;
            padding: 8px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .team-members li:last-child {
            border-bottom: none;
        }
        
        .about-visual {
            display: flex;
            flex-direction: column;
            gap: 25px;
        }
        
        .visual-card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            padding: 30px;
            transition: all 0.3s ease;
        }
        
        .visual-card:hover {
            transform: translateX(10px);
            border-color: rgba(30, 136, 229, 0.3);
        }
        
        .card-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .card-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: white;
        }
        
        .card-header h4 {
            color: white;
            font-size: 18px;
            margin: 0;
        }
        
        .visual-card p {
            color: #94a3b8;
            line-height: 1.6;
            margin: 0;
        }
        
        /* Support Section */
        .support-section {
            padding: 100px 0;
            background: #0f172a;
            position: relative;
            scroll-margin-top: 100px;
        }
        
        .support-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
            margin-top: 60px;
        }
        
        .support-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            padding: 40px 30px;
            text-align: center;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .support-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(135deg, #1e88e5, #0d47a1);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }
        
        .support-card:hover::before {
            transform: scaleX(1);
        }
        
        .support-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
        }
        
        .support-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #1e88e5, #0d47a1);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            font-size: 28px;
            color: white;
        }
        
        .support-card h3 {
            color: white;
            font-size: 22px;
            font-weight: 600;
            margin-bottom: 15px;
        }
        
        .support-card p {
            color: #94a3b8;
            line-height: 1.6;
            margin-bottom: 25px;
        }
        
        .support-action {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }
        
        .support-btn {
            background: linear-gradient(135deg, #1e88e5, #0d47a1);
            border: none;
            border-radius: 8px;
            padding: 12px 24px;
            color: white;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .support-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(30, 136, 229, 0.3);
        }
        
        .support-status, .support-time, .support-hours, .support-duration {
            font-size: 12px;
            color: #94a3b8;
        }
        
        .support-status.online {
            color: #10b981;
        }
        
        .support-faq {
            margin-top: 80px;
        }
        
        .support-faq h3 {
            color: white;
            font-size: 28px;
            text-align: center;
            margin-bottom: 40px;
        }
        
        .faq-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
        }
        
        .faq-item {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 25px;
        }
        
        .faq-item h4 {
            color: white;
            font-size: 16px;
            margin-bottom: 10px;
        }
        
        .faq-item p {
            color: #94a3b8;
            line-height: 1.6;
            margin: 0;
        }
        
        /* Section Headers */
        .section-header {
            text-align: center;
            margin-bottom: 60px;
        }
        
        .section-tag {
            display: inline-block;
            background: rgba(30, 136, 229, 0.1);
            color: #1e88e5;
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 20px;
            border: 1px solid rgba(30, 136, 229, 0.2);
        }
        
        .section-title {
            color: white;
            font-size: 48px;
            font-weight: 700;
            margin-bottom: 20px;
            line-height: 1.2;
        }
        
        .section-subtitle {
            color: #94a3b8;
            font-size: 18px;
            line-height: 1.6;
            max-width: 600px;
            margin: 0 auto;
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .main-content {
                grid-template-columns: 1fr 350px;
                gap: 60px;
            }
            .main-heading {
                font-size: 45px;
            }
            .login-section,
            .signup-section {
                padding: 35px;
            }
        }
        
        @media (max-width: 1024px) {
            .main-content {
                grid-template-columns: 1fr;
                gap: 50px;
                text-align: center;
                justify-items: center;
                align-items: center;
            }
            .content-left {
                padding-top: 0;
            }
            .login-section,
            .signup-section {
                width: 100%;
                max-width: 400px;
                position: static;
            }
            .main-heading {
                font-size: 40px;
            }
            .nav-menu {
                gap: 25px;
            }
            .features {
                flex-direction: row;
                flex-wrap: wrap;
                justify-content: center;
                gap: 30px 40px;
            }
        }
        
        @media (max-width: 768px) {
            .nav {
                flex-wrap: wrap;
                gap: 15px;
            }
            .nav-menu {
                display: none;
            }
            .search-section {
                order: 3;
                width: 100%;
                justify-content: center;
            }
            .search-box input {
                width: 250px;
            }
            .main-content {
                padding: 40px 0;
            }
            .main-heading {
                font-size: 32px;
            }
            .login-section,
            .signup-section {
                padding: 30px;
                margin: 0 10px;
            }
            .features {
                flex-direction: column;
                gap: 15px;
            }
            .container {
                padding: 0 15px;
            }
            .form-row {
                grid-template-columns: 1fr;
            }
            
            /* New Sections Responsive */
            .section-title {
                font-size: 32px;
            }
            .features-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            .resources-grid {
                grid-template-columns: 1fr;
                gap: 30px;
            }
            .about-content {
                grid-template-columns: 1fr;
                gap: 40px;
            }
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 20px;
            }
            .support-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            .faq-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            .feature-card, .resource-category, .support-card {
                padding: 30px 20px;
            }
        }
        
        @media (max-width: 480px) {
            .main-heading {
                font-size: 28px;
            }
            .description {
                font-size: 16px;
            }
            .login-section,
            .signup-section {
                padding: 25px;
            }
            .search-box input {
                width: 200px;
            }
        }
        
        /* Enhanced animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }
        
        @keyframes fadeInLeft {
            from {
                opacity: 0;
                transform: translateX(-50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        @keyframes fadeInRight {
            from {
                opacity: 0;
                transform: translateX(50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Staggered animations for features */
        .feature:nth-child(1) {
            animation: fadeInUp 1.0s cubic-bezier(0.25, 0.46, 0.45, 0.94) 0.7s both;
        }
        .feature:nth-child(2) {
            animation: fadeInUp 1.0s cubic-bezier(0.25, 0.46, 0.45, 0.94) 0.9s both;
        }
        .feature:nth-child(3) {
            animation: fadeInUp 1.0s cubic-bezier(0.25, 0.46, 0.45, 0.94) 1.1s both;
        }
        
        /* Cross-browser compatibility improvements */
        .login-section,
        .signup-section {
            -webkit-backdrop-filter: blur(20px);
            backdrop-filter: blur(20px);
        }
        
        .header {
            -webkit-backdrop-filter: blur(20px);
            backdrop-filter: blur(20px);
        }
    </style>
    <script src="notification_system.js"></script>
</head>
<body>
    <!-- Professional Background Animations -->
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
                    <li><a href="#features">FEATURES</a></li>
                    <li><a href="#about">ABOUT US</a></li>
                    <li><a href="#support">SUPPORT</a></li>
                </ul>
            </nav>
        </div>
    </div>
    
    <div class="container">
        <div class="main-content">
            <div class="content-left">
                <h1 class="main-heading">
                    Where companies<br>
                    <span class="highlight-blue">Find Talent</span> & <span class="highlight-orange">Talent Find</span><br>
                    Careers
                </h1>
                <p class="description">
                    CandiHire connects companies and candidates in one streamlined platform. Post or find jobs, 
                    showcase projects, build and rate CVs, take skill tests, and match instantly with the right opportunities. 
                    Apply to multiple companies in one click, track your status in real time, and schedule interviews 
                    without conflicts—all with AI-powered precision.
                </p>
            </div>
            
            <div class="auth-container">
                <!-- Login Section -->
                <div class="login-section" id="loginSection">
                    <div class="login-header">
                        <div class="login-title">Secure Access Portal</div>
                        <div class="login-subtitle">Sign in to your CandiHire account</div>
                    </div>
                    <form id="loginForm">
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
                                <input type="checkbox" id="keepSignedIn">
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
                    <div class="create-account">
                        New to CandiHire? <a href="#" id="showSignup">Create A New Account</a>
                    </div>
                    <div class="admin-login-section">
                        <a href="admin_login_handler.php" class="admin-login-btn">
                            <i class="fas fa-shield-alt"></i>
                            Admin Login
                        </a>
                    </div>
                    <div class="sso-section">
                        <div class="sso-title">Single Sign-On Options</div>
                        <div class="sso-buttons">
                            <a href="#" class="sso-btn" title="Google">
                                <i class="fab fa-google"></i>
                            </a>
                            <a href="#" class="sso-btn" title="LinkedIn">
                                <i class="fab fa-linkedin-in"></i>
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Sign Up Section -->
                <div class="signup-section" id="signupSection">
                    <div class="login-header">
                        <div class="login-title">Join CandiHire</div>
                        <div class="login-subtitle">Create your account to get started</div>
                    </div>
                    
                    <div class="signup-tabs">
                        <div class="signup-tab active" data-tab="candidate">Candidate</div>
                        <div class="signup-tab" data-tab="company">Company</div>
                    </div>
                    
                    <!-- Candidate Sign Up Form -->
                    <div class="tab-content active" id="candidate-tab">
                        <form id="candidateForm">
                            <div class="form-group">
                                <label>Full Name</label>
                              <input type="text" name="fullName" placeholder="Enter your full name" required>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Email Address</label>
                                   <input type="email" name="email" placeholder="Enter your email" required>
                                </div>
                                <div class="form-group">
                                    <label>Phone Number</label>
                                    <input type="tel" name="phoneNumber" placeholder="Enter your phone number" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Work Type</label>
                                <select name="workType" required>
                                    <option value="" disabled selected>Select work type</option>
                                    <option value="full-time">Full Time</option>
                                    <option value="part-time">Part Time</option>
                                    <option value="contract">Contract</option>
                                    <option value="freelance">Freelance</option>
                                    <option value="internship">Internship</option>
                                  
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Skills</label>
                                <div class="skills-container">
                                    <input type="text" id="skillInput" placeholder="Type a skill and press Enter">
                                    <input type="hidden" name="skills" id="skillsInput">
                                    <div class="skills-list" id="skillsList"></div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Password</label>
                              <input type="password" name="password" placeholder="Create a strong password" required>
                            </div>
                            <div class="form-group">
                                <label>Confirm Password</label>
                                <input type="password" name="confirmPassword" placeholder="Confirm your password" required>
                            </div>
                            <button type="submit" class="sign-up-btn">
                                <i class="fas fa-user-plus"></i>
                                Create Candidate Account
                            </button>
                        </form>
                    </div>
                    
                    <!-- Company Sign Up Form -->
                    <div class="tab-content" id="company-tab">
                        <form id="companyForm">
                            <div class="form-group">
                                <label>Company Name</label>
                                <input type="text" name="companyName" placeholder="Enter your company name" required>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Industry</label>
                                    <select name="industry" required>
                                        <option value="" disabled selected>Select industry</option>
                                        <option value="technology">Technology</option>
                                        <option value="healthcare">Healthcare</option>
                                        <option value="finance">Finance</option>
                                        <option value="education">Education</option>
                                        <option value="retail">Retail</option>
                                        <option value="manufacturing">Manufacturing</option>
                                        <option value="consulting">Consulting</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Company Size</label>
                                    <select name="companySize" required>
                                        <option value="" disabled selected>Select company size</option>
                                        <option value="1-10">1-10 employees</option>
                                        <option value="11-50">11-50 employees</option>
                                        <option value="51-200">51-200 employees</option>
                                        <option value="201-500">201-500 employees</option>
                                        <option value="501-1000">501-1000 employees</option>
                                        <option value="1000+">1000+ employees</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Email Address</label>
                                    <input type="email" name="email" placeholder="Enter company email" required>
                                </div>
                                <div class="form-group">
                                    <label>Phone Number</label>
                                    <input type="tel" name="phoneNumber" placeholder="Enter company phone" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Company Description</label>
                                <textarea name="companyDescription" rows="3" placeholder="Brief description of your company"></textarea>
                            </div>
                            <div class="form-group">
                                <label>Password</label>
                                <input type="password" name="password" placeholder="Create a strong password" required>
                            </div>
                            <div class="form-group">
                                <label>Confirm Password</label>
                                <input type="password" name="confirmPassword" placeholder="Confirm your password" required>
                            </div>
                            <button type="submit" class="sign-up-btn">
                                <i class="fas fa-building"></i>
                                Create Company Account
                            </button>
                        </form>
                    </div>
                    
                    <div class="back-to-login">
                        Already have an account? <a href="#" id="showLogin">Sign in</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <section id="features" class="features-section">
        <div class="container">
            <div class="section-header">
                <div class="section-tag">✨ FEATURES</div>
                <h2 class="section-title">Powerful Tools for Modern Recruitment</h2>
                <p class="section-subtitle">Discover the comprehensive features that make CandiHire the ultimate recruitment platform</p>
            </div>
            <div class="features-grid">
                <div class="feature-card" data-aos="fade-up" data-aos-delay="100">
                    <div class="feature-icon">
                        <i class="fas fa-brain"></i>
                    </div>
                    <h3>AI-Powered Matching</h3>
                    <p>Advanced algorithms match candidates with perfect job opportunities based on skills, experience, and preferences.</p>
                    <div class="feature-highlight">95% Match Accuracy</div>
                </div>
                <div class="feature-card" data-aos="fade-up" data-aos-delay="200">
                    <div class="feature-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <h3>Smart CV Builder</h3>
                    <p>Create professional resumes with our AI-assisted builder. Get instant feedback and optimization suggestions.</p>
                    <div class="feature-highlight">ATS Optimized</div>
                </div>
                <div class="feature-card" data-aos="fade-up" data-aos-delay="300">
                    <div class="feature-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3>Real Time Application Tracking</h3>
                    <p>Track your job applications in real-time with detailed status updates and progress monitoring.</p>
                    <div class="feature-highlight">Live Updates</div>
                </div>
                <div class="feature-card" data-aos="fade-up" data-aos-delay="400">
                    <div class="feature-icon">
                        <i class="fas fa-question-circle"></i>
                    </div>
                    <h3>MCQ Exam System</h3>
                    <p>Comprehensive multiple choice question system for skill assessment and evaluation.</p>
                    <div class="feature-highlight">Auto Grading</div>
                </div>
                <div class="feature-card" data-aos="fade-up" data-aos-delay="500">
                    <div class="feature-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <h3>CV Checker</h3>
                    <p>Advanced CV analysis and optimization tool to improve your resume's ATS compatibility and effectiveness.</p>
                    <div class="feature-highlight">AI Powered</div>
                </div>
                <div class="feature-card" data-aos="fade-up" data-aos-delay="600">
                    <div class="feature-icon">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <h3>Mobile Optimized</h3>
                    <p>Access all features on any device with our responsive design and native mobile experience.</p>
                    <div class="feature-highlight">Cross-Platform</div>
                </div>
            </div>
        </div>
    </section>


    <!-- About Us Section -->
    <section id="about" class="about-section">
        <div class="container">
            <div class="about-content">
                <div class="about-text" data-aos="fade-right">
                    <div class="section-tag">🏢 ABOUT US</div>
                    <h2 class="section-title">Revolutionizing Recruitment</h2>
                    <p class="about-description">
                        CandiHire was born from a simple vision: to make recruitment effortless, efficient, and equitable. 
                        We believe that the right talent should meet the right opportunity, and technology should make 
                        that connection seamless.
                    </p>
                    <div class="stats-grid">
                        <div class="stat-item" data-aos="zoom-in" data-aos-delay="100">
                            <div class="stat-number" data-count="2000000">0</div>
                            <div class="stat-label">Active Users</div>
                        </div>
                        <div class="stat-item" data-aos="zoom-in" data-aos-delay="200">
                            <div class="stat-number" data-count="1500000">0</div>
                            <div class="stat-label">Companies</div>
                        </div>
                        <div class="stat-item" data-aos="zoom-in" data-aos-delay="300">
                            <div class="stat-number" data-count="5000000">0</div>
                            <div class="stat-label">Jobs Posted</div>
                        </div>
                        <div class="stat-item" data-aos="zoom-in" data-aos-delay="400">
                            <div class="stat-number" data-count="98">0</div>
                            <div class="stat-label">Success Rate</div>
                        </div>
                    </div>
                    <div class="mission-statement">
                        <h4>Our Mission</h4>
                        <p>To bridge the gap between talent and opportunity through innovative technology, 
                        creating meaningful connections that drive success for both candidates and companies.</p>
                    </div>
                    <div class="team-info">
                        <h4><strong>Created by Team Shinrai</strong></h4>
                        <div class="team-members">
                            <p><strong>Team Members:</strong></p>
                            <ul>
                                <li>Sayeem Mahmood</li>
                                <li>Tamimul Mufid</li>
                                <li>Redwanul Haque Peash</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="about-visual" data-aos="fade-left">
                    <div class="visual-card">
                        <div class="card-header">
                            <div class="card-icon">
                                <i class="fas fa-rocket"></i>
                            </div>
                            <h4>Innovation First</h4>
                        </div>
                        <p>We continuously innovate to stay ahead of recruitment trends, 
                        incorporating the latest in AI, machine learning, and user experience design.</p>
                    </div>
                    <div class="visual-card">
                        <div class="card-header">
                            <div class="card-icon">
                                <i class="fas fa-heart"></i>
                            </div>
                            <h4>User-Centric</h4>
                        </div>
                        <p>Every feature is designed with our users in mind, ensuring intuitive 
                        experiences that make recruitment a pleasure, not a chore.</p>
                    </div>
                    <div class="visual-card">
                        <div class="card-header">
                            <div class="card-icon">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <h4>Secure & Reliable</h4>
                        </div>
                        <p>Your data security is our priority. We implement enterprise-grade 
                        security measures to protect your information and privacy.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Support Section -->
    <section id="support" class="support-section">
        <div class="container">
            <div class="section-header">
                <div class="section-tag">🛠️ SUPPORT</div>
                <h2 class="section-title">We're Here to Help</h2>
                <p class="section-subtitle">Get the support you need, when you need it</p>
            </div>
            <div class="support-grid">
                <div class="support-card" data-aos="fade-up" data-aos-delay="100">
                    <div class="support-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h3>24/7 Live Support</h3>
                    <p>Our dedicated support team is available around the clock to help you with any questions or issues.</p>
                    <div class="support-action">
                        <button class="support-btn">Start Chat</button>
                        <span class="support-status online">Online Now</span>
                    </div>
                </div>
                <div class="support-card" data-aos="fade-up" data-aos-delay="200">
                    <div class="support-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <h3>Email Support</h3>
                    <p>Send us detailed queries and receive comprehensive responses within 24 hours.</p>
                    <div class="support-action">
                        <button class="support-btn">Send Email</button>
                        <span class="support-time">Response: <24h</span>
                    </div>
                </div>
                <div class="support-card" data-aos="fade-up" data-aos-delay="300">
                    <div class="support-icon">
                        <i class="fas fa-phone"></i>
                    </div>
                    <h3>Phone Support</h3>
                    <p>Speak directly with our support specialists for immediate assistance with urgent matters.</p>
                    <div class="support-action">
                        <button class="support-btn">Call Now</button>
                        <span class="support-hours">Mon-Fri: 9AM-6PM</span>
                    </div>
                </div>
                <div class="support-card" data-aos="fade-up" data-aos-delay="400">
                    <div class="support-icon">
                        <i class="fas fa-video"></i>
                    </div>
                    <h3>Video Consultation</h3>
                    <p>Schedule a personalized video call with our experts for in-depth guidance and training.</p>
                    <div class="support-action">
                        <button class="support-btn">Schedule Call</button>
                        <span class="support-duration">30-60 min sessions</span>
                    </div>
                </div>
            </div>
            <div class="support-faq">
                <h3>Frequently Asked Questions</h3>
                <div class="faq-grid">
                    <div class="faq-item" data-aos="fade-right" data-aos-delay="100">
                        <h4>How do I get started with CandiHire?</h4>
                        <p>Simply create an account, complete your profile, and start exploring opportunities or posting jobs.</p>
                    </div>
                    <div class="faq-item" data-aos="fade-right" data-aos-delay="200">
                        <h4>Is there a free trial available?</h4>
                        <p>It is completely free! We offer full access to all features for both candidates and companies at no cost.</p>
                    </div>
                    <div class="faq-item" data-aos="fade-right" data-aos-delay="300">
                        <h4>How does the AI matching work?</h4>
                        <p>Our AI analyzes skills, experience, preferences, and job requirements to find the perfect matches.</p>
                    </div>
                    <div class="faq-item" data-aos="fade-right" data-aos-delay="400">
                        <h4>Does CandiHire share data with external companies?</h4>
                        <p>No, we do not share your data with any external companies. Your privacy and data security are our top priorities.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        // Enhanced cross-browser compatibility and error handling
        (function() {
            'use strict';
            
            // Feature detection for modern JavaScript features
            const hasQuerySelector = document.querySelector;
            const hasAddEventListener = window.addEventListener;
            
            if (!hasQuerySelector || !hasAddEventListener) {
                console.warn('Browser compatibility issue detected');
                return;
            }

            // Professional background animation system with error handling
            function createProfessionalBackground() {
                try {
                    const container = document.getElementById('backgroundContainer');
                    if (!container) return;
                    
                    // Create subtle gradient orbs
                    function createGradientOrbs() {
                        const orbCount = 8;
                        for (let i = 0; i < orbCount; i++) {
                            const orb = document.createElement('div');
                            orb.classList.add('gradient-orb');
                            
                            const sizes = ['small', 'medium', 'large'];
                            const size = sizes[Math.floor(Math.random() * sizes.length)];
                            orb.classList.add(size);
                            
                            const startX = Math.random() * 120 - 10;
                            const delay = Math.random() * 15;
                            
                            orb.style.left = startX + '%';
                            orb.style.animationDelay = delay + 's';
                            
                            container.appendChild(orb);
                        }
                    }
                    
                    // Only create background if animation is supported
                    if (CSS.supports('animation', 'test')) {
                        createGradientOrbs();
                    }
                } catch (error) {
                    console.warn('Background animation creation failed:', error);
                }
            }

            // Enhanced form switching with better error handling
            function setupFormSwitching() {
                const showSignupLink = document.getElementById('showSignup');
                const showLoginLink = document.getElementById('showLogin');
                const loginSection = document.getElementById('loginSection');
                const signupSection = document.getElementById('signupSection');
                
                if (!showSignupLink || !showLoginLink || !loginSection || !signupSection) {
                    console.error('Required form elements not found');
                    return;
                }

                // Show signup form
                showSignupLink.addEventListener('click', function(e) {
                    e.preventDefault();
                    try {
                        loginSection.style.display = 'none';
                        signupSection.style.display = 'block';
                        signupSection.classList.add('show');
                    } catch (error) {
                        console.error('Error showing signup form:', error);
                    }
                });

                // Show login form
                showLoginLink.addEventListener('click', function(e) {
                    e.preventDefault();
                    try {
                        signupSection.style.display = 'none';
                        signupSection.classList.remove('show');
                        loginSection.style.display = 'block';
                    } catch (error) {
                        console.error('Error showing login form:', error);
                    }
                });
            }

            // Enhanced tab switching
            function setupTabSwitching() {
                const tabs = document.querySelectorAll('.signup-tab');
                const tabContents = document.querySelectorAll('.tab-content');
                
                if (!tabs.length || !tabContents.length) return;

                tabs.forEach(function(tab) {
                    tab.addEventListener('click', function() {
                        try {
                            // Remove active class from all tabs and content
                            tabs.forEach(function(t) {
                                t.classList.remove('active');
                            });
                            tabContents.forEach(function(c) {
                                c.classList.remove('active');
                            });
                            
                            // Add active class to clicked tab
                            this.classList.add('active');
                            
                            // Show corresponding content
                            const tabId = this.getAttribute('data-tab');
                            const targetContent = document.getElementById(tabId + '-tab');
                            if (targetContent) {
                                targetContent.classList.add('active');
                            }
                        } catch (error) {
                            console.error('Error switching tabs:', error);
                        }
                    });
                });
            }

            // Enhanced skills functionality
function setupSkillsInput() {
    const skillInput = document.getElementById('skillInput');
    const skillsList = document.getElementById('skillsList');
    
    if (!skillInput || !skillsList) return;
    
    // Move skills array to proper scope
    let skills = [];

    skillInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter' || e.keyCode === 13) {
            e.preventDefault();
            const skill = this.value.trim();
            if (skill && skills.indexOf(skill) === -1) {
                skills.push(skill);
                updateSkillsList();
                this.value = '';
            }
        }
    });

    function updateSkillsList() {
        try {
            skillsList.innerHTML = '';
            skills.forEach(function(skill) {
                const skillTag = document.createElement('div');
                skillTag.className = 'skill-tag';
                skillTag.innerHTML = skill + ' <span class="remove-skill" data-skill="' + skill + '">×</span>';
                skillsList.appendChild(skillTag);
            });

            // Update hidden input with comma-separated skills
            const hiddenSkillsInput = document.getElementById('skillsInput');
            if (hiddenSkillsInput) {
                hiddenSkillsInput.value = skills.join(',');
            }

            // Add event listeners to remove buttons
            const removeButtons = skillsList.querySelectorAll('.remove-skill');
            removeButtons.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const skillToRemove = this.getAttribute('data-skill');
                    const index = skills.indexOf(skillToRemove);
                    if (index > -1) {
                        skills.splice(index, 1);
                        updateSkillsList();
                    }
                });
            });
        } catch (error) {
            console.error('Error updating skills list:', error);
        }
    }
}




            // Enhanced form handling with better validation
function setupFormHandling() {
    const candidateForm = document.getElementById('candidateForm');
    const loginForm = document.getElementById('loginForm');

    if (candidateForm) {
        candidateForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            try {
                // Collect form data
                const formData = new FormData(this);
                const data = {
                    fullName: formData.get('fullName'),
                    email: formData.get('email'),
                    phoneNumber: formData.get('phoneNumber'),
                    workType: formData.get('workType'),
                    skills: document.getElementById('skillsInput').value || '',
                    password: formData.get('password'),
                    confirmPassword: formData.get('confirmPassword')
                };
                
                // Client-side validation
                if (!data.fullName || !data.email || !data.phoneNumber || 
                    !data.workType || !data.password || !data.confirmPassword) {
                    showError('Please fill in all required fields');
                    return;
                }
                
                if (data.password !== data.confirmPassword) {
                    showError('Passwords do not match!');
                    return;
                }
                
                if (data.password.length < 8) {
                    showError('Password must be at least 8 characters long');
                    return;
                }

                const submitBtn = this.querySelector('.sign-up-btn');
                if (submitBtn) {
                    const originalText = submitBtn.innerHTML;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Account...';
                    submitBtn.disabled = true;

                    // Debug: Check if the PHP file exists first
                    console.log('Attempting to fetch: ./candidate_reg_handler.php');
                    
                    // Send data to PHP backend - using correct filename
                    fetch('./candidate_reg_handler.php', {  // Updated to match your actual filename
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(data)
                    })
                    .then(response => {
                        console.log('Response status:', response.status);
                        console.log('Response URL:', response.url);
                        
                        if (response.status === 404) {
                            throw new Error('PHP file not found. Check if candidate_reg_handler.php exists in your project folder.');
                        }
                        
                        // Try to get response text first to see what's actually returned
                        return response.text().then(text => {
                            console.log('Raw response:', text);
                            
                            // Check if response starts with HTML (error page)
                            if (text.trim().toLowerCase().startsWith('<!doctype') || 
                                text.trim().toLowerCase().startsWith('<html')) {
                                throw new Error('Received HTML error page instead of JSON. File might not exist or server error occurred.');
                            }
                            
                            try {
                                return JSON.parse(text);
                            } catch (e) {
                                throw new Error('Invalid JSON response: ' + text.substring(0, 100) + '...');
                            }
                        });
                    })
                    .then(result => {
                        console.log('Parsed result:', result);
                        if (result.success && result.candidateId) {
                            // Show success popup message
                            showSuccessPopup('Account Created Successfully!', 'Your account has been created successfully. You will be redirected to the login page.');
                            
                            // Reset button text immediately
                            submitBtn.innerHTML = originalText;
                            submitBtn.disabled = false;
                            
                            // Redirect to login page after 3 seconds
                            setTimeout(() => {
                                window.location.href = 'Login&Signup.php';
                            }, 3000);
                        } else {
                            showError(result && result.message ? result.message : 'Registration failed. Please try again.');
                            // Reset button on error
                            submitBtn.innerHTML = originalText;
                            submitBtn.disabled = false;
                        }
                    })
                    .catch(error => {
                        console.error('Detailed error:', error);
                        showError('Network error: ' + error.message + '. Please check your connection and try again.');
                        // Reset button on error
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    });
                }
            } catch (error) {
                console.error('Candidate form error:', error);
                showError('An error occurred. Please try again.');
            }
        });
    }
    
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();

            try {
                const formData = new FormData(this);
                const payload = {
                    email: formData.get('email'),
                    password: formData.get('password')
                };

                if (!payload.email || !payload.password) {
                    showError('Please enter both email and password');
                    return;
                }

                const submitBtn = this.querySelector('.sign-in-btn');
                const originalText = submitBtn ? submitBtn.innerHTML : '';
                if (submitBtn) {
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Signing in...';
                    submitBtn.disabled = true;
                }

                fetch('./candidate_login_handler.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(payload)
                })
                .then(response => response.text().then(text => {
                    if (!response.ok) {
                        throw new Error('HTTP ' + response.status);
                    }
                    try { return JSON.parse(text); } catch (_) {
                        throw new Error('Invalid JSON: ' + text.substring(0, 120) + '...');
                    }
                }))
                .then(result => {
                    if (result && result.success && result.candidateId) {
                        showSuccess('Login successful! Redirecting to dashboard...');
                        setTimeout(() => {
                            window.location.href = 'CandidateDashboard.php?candidateId=' + encodeURIComponent(result.candidateId);
                        }, 1500);
                    } else {
                        // Try company login if candidate login failed
                        return fetch('./company_login_handler.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify(payload)
                        }).then(response => response.text().then(text => {
                            if (!response.ok) {
                                throw new Error('HTTP ' + response.status);
                            }
                            try { return JSON.parse(text); } catch (_) {
                                throw new Error('Invalid JSON: ' + text.substring(0, 120) + '...');
                            }
                        }));
                    }
                })
                .then(result => {
                    if (result && result.success && result.companyId) {
                        showSuccess('Login successful! Redirecting to company dashboard...');
                        setTimeout(() => {
                            window.location.href = 'CompanyDashboard.php?companyId=' + encodeURIComponent(result.companyId);
                        }, 1500);
                    } else {
                        showError(result && result.message ? result.message : 'Invalid email or password. Please try again.');
                    }
                })
                .catch(err => {
                    console.error('Login error:', err);
                    showError('Network error: ' + err.message + '. Please check your connection.');
                })
                .finally(() => {
                    if (submitBtn) {
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    }
                });
            } catch (error) {
                console.error('Login form error:', error);
                showError('An error occurred. Please try again.');
            }
        });
    }
    
    // Company form handling
    const companyForm = document.getElementById('companyForm');
    if (companyForm) {
        companyForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            try {
                const formData = new FormData(this);
                const data = {
                    companyName: formData.get('companyName'),
                    industry: formData.get('industry'),
                    companySize: formData.get('companySize'),
                    email: formData.get('email'),
                    phoneNumber: formData.get('phoneNumber'),
                    companyDescription: formData.get('companyDescription'),
                    password: formData.get('password'),
                    confirmPassword: formData.get('confirmPassword')
                };
                
                // Client-side validation
                if (!data.companyName || !data.industry || !data.companySize || 
                    !data.email || !data.phoneNumber || !data.password || !data.confirmPassword) {
                    showError('Please fill in all required fields');
                    return;
                }
                
                if (data.password !== data.confirmPassword) {
                    showError('Passwords do not match!');
                    return;
                }
                
                if (data.password.length < 8) {
                    showError('Password must be at least 8 characters long');
                    return;
                }

                const submitBtn = this.querySelector('.sign-up-btn');
                if (submitBtn) {
                    const originalText = submitBtn.innerHTML;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Account...';
                    submitBtn.disabled = true;

                    fetch('./company_reg_handler.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(data)
                    })
                    .then(response => response.json())
                    .then(result => {
                        if (result.success && result.companyId) {
                            // Show success popup message
                            showSuccessPopup('Company Account Created Successfully!', 'Your company account has been created successfully. You will be redirected to the login page.');
                            
                            // Reset button text immediately
                            submitBtn.innerHTML = originalText;
                            submitBtn.disabled = false;
                            
                            // Redirect to login page after 3 seconds
                            setTimeout(() => {
                                window.location.href = 'Login&Signup.php';
                            }, 3000);
                        } else {
                            showError(result && result.message ? result.message : 'Registration failed. Please try again.');
                            // Reset button on error
                            submitBtn.innerHTML = originalText;
                            submitBtn.disabled = false;
                        }
                    })
                    .catch(error => {
                        console.error('Company form error:', error);
                        showError('Network error: ' + error.message + '. Please check your connection and try again.');
                        // Reset button on error
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    });
                }
            } catch (error) {
                console.error('Company form error:', error);
                showError('An error occurred. Please try again.');
            }
        });
    }
}
            function setupSearchFunctionality() {
                const searchBtn = document.querySelector('.search-btn');
                const searchInput = document.querySelector('.search-box input');
                
                if (!searchBtn || !searchInput) return;

                searchBtn.addEventListener('click', function() {
                    const searchTerm = searchInput.value.trim();
                    if (searchTerm) {
                        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Searching...';
                        this.disabled = true;
                        
                        const self = this;
                        setTimeout(function() {
                            showInfo('Search functionality would be implemented here for: ' + searchTerm);
                            self.innerHTML = '<i class="fas fa-search"></i> Search';
                            self.disabled = false;
                        }, 1000);
                    }
                });

                searchInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter' || e.keyCode === 13) {
                        searchBtn.click();
                    }
                });
            }

            // Notification functions
            function showSuccessPopup(title, message) {
                // Create popup overlay
                const overlay = document.createElement('div');
                overlay.style.cssText = `
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0, 0, 0, 0.5);
                    z-index: 10000;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                `;
                
                // Create popup content
                const popup = document.createElement('div');
                popup.style.cssText = `
                    background: white;
                    padding: 30px;
                    border-radius: 12px;
                    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
                    max-width: 400px;
                    text-align: center;
                    animation: popupSlideIn 0.3s ease-out;
                `;
                
                popup.innerHTML = `
                    <div style="color: #28a745; font-size: 48px; margin-bottom: 15px;">✓</div>
                    <h3 style="color: #333; margin: 0 0 15px 0; font-size: 20px;">${title}</h3>
                    <p style="color: #666; margin: 0 0 20px 0; line-height: 1.5;">${message}</p>
                    <button onclick="this.closest('.popup-overlay').remove()" style="
                        background: #28a745;
                        color: white;
                        border: none;
                        padding: 10px 20px;
                        border-radius: 6px;
                        cursor: pointer;
                        font-size: 14px;
                    ">OK</button>
                `;
                
                overlay.className = 'popup-overlay';
                overlay.appendChild(popup);
                document.body.appendChild(overlay);
                
                // Auto-remove after 5 seconds
                setTimeout(() => {
                    if (overlay.parentNode) {
                        overlay.remove();
                    }
                }, 5000);
            }
            
            function showSuccess(message) {
                // Success messages removed - no popup display
            }
            
            function showError(message) {
                // Error messages disabled - no popup display
                console.log('Error:', message);
            }
            
            function showInfo(message) {
                // Info messages removed - no popup display
            }
            
            // Add CSS animations
            const style = document.createElement('style');
            style.textContent = `
                
                @keyframes slideInLeft {
                    from {
                        transform: translateX(-100%);
                        opacity: 0;
                    }
                    to {
                        transform: translateX(0);
                        opacity: 1;
                    }
                }
                
                @keyframes slideOutLeft {
                    from {
                        transform: translateX(0);
                        opacity: 1;
                    }
                    to {
                        transform: translateX(-100%);
                        opacity: 0;
                    }
                }
                
                @keyframes popupSlideIn {
                    from { transform: scale(0.8); opacity: 0; }
                    to { transform: scale(1); opacity: 1; }
                }
                @keyframes slideIn {
                    from { transform: translateX(100%); opacity: 0; }
                    to { transform: translateX(0); opacity: 1; }
                }
                @keyframes slideOut {
                    from { transform: translateX(0); opacity: 1; }
                    to { transform: translateX(100%); opacity: 0; }
                }
            `;
            document.head.appendChild(style);

            // Smooth scrolling for navigation links
            function setupSmoothScrolling() {
                const navLinks = document.querySelectorAll('.nav-menu a[href^="#"]');
                
                navLinks.forEach(link => {
                    link.addEventListener('click', function(e) {
                        e.preventDefault();
                        
                        const targetId = this.getAttribute('href');
                        const targetSection = document.querySelector(targetId);
                        
                        if (targetSection) {
                            // Get header height more accurately
                            const header = document.querySelector('.header');
                            const headerHeight = header ? header.offsetHeight : 80;
                            
                            // Calculate target position with proper offset
                            const targetPosition = targetSection.offsetTop - headerHeight - 30;
                            
                            // Ensure we don't scroll to negative position
                            const finalPosition = Math.max(0, targetPosition);
                            
                            window.scrollTo({
                                top: finalPosition,
                                behavior: 'smooth'
                            });
                            
                            // Update active nav item
                            navLinks.forEach(nav => nav.classList.remove('active'));
                            this.classList.add('active');
                        }
                    });
                });
            }

            // Intersection Observer for animations
            function setupScrollAnimations() {
                const observerOptions = {
                    threshold: 0.1,
                    rootMargin: '0px 0px -50px 0px'
                };

                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            entry.target.style.opacity = '1';
                            entry.target.style.transform = 'translateY(0)';
                            
                            // Trigger counter animation for about section
                            if (entry.target.classList.contains('about-section')) {
                                animateCounters();
                            }
                        }
                    });
                }, observerOptions);

                // Observe all animated elements
                const animatedElements = document.querySelectorAll('[data-aos]');
                animatedElements.forEach(el => {
                    el.style.opacity = '0';
                    el.style.transform = 'translateY(50px)';
                    el.style.transition = 'opacity 1.0s cubic-bezier(0.25, 0.46, 0.45, 0.94), transform 1.0s cubic-bezier(0.25, 0.46, 0.45, 0.94)';
                    observer.observe(el);
                });

                // Also observe sections for counter animation
                const sections = document.querySelectorAll('section');
                sections.forEach(section => {
                    observer.observe(section);
                });
            }

            // Parallax effect for sections
            function setupParallaxEffects() {
                window.addEventListener('scroll', () => {
                    const scrolled = window.pageYOffset;
                    const parallaxElements = document.querySelectorAll('.features-section, .about-section');
                    
                    parallaxElements.forEach(element => {
                        const speed = 0.5;
                        const yPos = -(scrolled * speed);
                        element.style.transform = `translateY(${yPos}px)`;
                    });
                });
            }

            // Counter animation for statistics
            function animateCounters() {
                const counters = document.querySelectorAll('.stat-number[data-count]');
                
                counters.forEach((counter, index) => {
                    const target = parseInt(counter.getAttribute('data-count'));
                    const duration = 2000; // 2 seconds
                    const increment = target / (duration / 16); // 60fps
                    let current = 0;
                    
                    // Add delay for staggered animation
                    setTimeout(() => {
                        counter.classList.add('animating');
                        
                        const updateCounter = () => {
                            if (current < target) {
                                current += increment;
                                const displayValue = Math.floor(current);
                                
                                // Format numbers with commas
                                if (target >= 1000) {
                                    counter.textContent = displayValue.toLocaleString() + '+';
                                } else {
                                    counter.textContent = displayValue + '%';
                                }
                                
                                requestAnimationFrame(updateCounter);
                            } else {
                                // Final value
                                if (target >= 1000) {
                                    counter.textContent = target.toLocaleString() + '+';
                                } else {
                                    counter.textContent = target + '%';
                                }
                                counter.classList.remove('animating');
                            }
                        };
                        
                        updateCounter();
                    }, index * 200); // 200ms delay between each counter
                });
            }

            // Initialize all functionality when DOM is ready
            function initializeApp() {
                try {
                    setupFormSwitching();
                    setupTabSwitching();
                    setupSkillsInput();
                    setupFormHandling();
                    setupSmoothScrolling();
                    setupScrollAnimations();
                    setupParallaxEffects();
                    setupSearchFunctionality();
                    createProfessionalBackground();
                } catch (error) {
                    console.error('App initialization error:', error);
                }
            }

            // Wait for DOM to be ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initializeApp);
            } else {
                initializeApp();
            }

            // Fallback for older browsers
            if (window.addEventListener) {
                window.addEventListener('load', function() {
                    if (typeof initializeApp === 'function') {
                        initializeApp();
                    }
                });
            }

        })();
    </script>
</body>
</html>
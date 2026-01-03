<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CandiHire - Find Match Hire</title>
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/Login_Signup.css') }}">
    <script src="{{ asset('js/notification_system.js') }}"></script>
</head>
<body>
    <!-- Hero Landing Section with Logo Background -->
    <section class="hero-landing" id="heroLanding">
        <div class="hero-background">
            <div class="logo-watermark">
                <img src="https://z-cdn-media.chatglm.cn/files/679b0056-b12f-411d-8a44-f81eb30411fa_CandiHire%20Logo.png?auth_key=1789498881-6b01dceb81c849e4b1fb0447e1d05a91-0-027ec66914332736d7f6859adb370481" alt="CandiHire Logo">
            </div>
            <div class="animated-particles" id="particlesContainer"></div>
            <div class="gradient-overlay"></div>
        </div>
        <div class="hero-content">
            <div class="hero-logo-animated">
                <img src="https://z-cdn-media.chatglm.cn/files/679b0056-b12f-411d-8a44-f81eb30411fa_CandiHire%20Logo.png?auth_key=1789498881-6b01dceb81c849e4b1fb0447e1d05a91-0-027ec66914332736d7f6859adb370481" alt="CandiHire Logo" class="hero-logo-img">
            </div>
            <h1 class="hero-title">
                <span class="title-candi">Candi</span><span class="title-hire">Hire</span>
            </h1>
            <p class="hero-tagline">FIND • MATCH • HIRE</p>
            <p class="hero-subtitle">Where Talent Meets Opportunity</p>
        </div>
        <div class="scroll-indicator" onclick="scrollToContent()">
            <div class="scroll-text">Scroll Down</div>
            <div class="scroll-arrow">
                <i class="fas fa-chevron-down"></i>
                <i class="fas fa-chevron-down"></i>
                <i class="fas fa-chevron-down"></i>
            </div>
        </div>
    </section>

    <!-- Main Content Wrapper (everything after hero) -->
    <div class="main-wrapper" id="mainContent">
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
                    <form id="loginForm" method="POST" action="{{ url('/login') }}">
                        @csrf
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" name="email" placeholder="Enter your email address" required>
                        </div>
                        <div class="form-group">
                            <label>Password</label>
                            <div class="password-wrapper">
                                <input type="password" name="password" id="loginPassword" placeholder="Enter your password" required>
                                <i class="fas fa-eye password-toggle" id="togglePassword"></i>
                            </div>
                        </div>
                        <div class="form-options">
                            <div class="checkbox-group">
                                <input type="checkbox" id="keepSignedIn">
                                <label for="keepSignedIn">Keep me signed in</label>
                            </div>
                            <a href="#" class="forgot-link">Forgot Password?</a>
                        </div>
                        @if(session('error'))
                            <div style="color: #ff4444; margin-bottom: 15px; text-align: center;">{{ session('error') }}</div>
                        @endif
                        @if(session('success'))
                            <div style="color: #44ff44; margin-bottom: 15px; text-align: center;">{{ session('success') }}</div>
                        @endif
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
                        <a href="{{ url('/admin/login') }}" class="admin-login-btn">
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
                        <form id="candidateForm" method="POST" action="{{ url('/register/candidate') }}">
                            @csrf
                            <div class="form-group">
                                <label>Full Name</label>
                              <input type="text" name="FullName" placeholder="Enter your full name" required>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Email Address</label>
                                   <input type="email" name="Email" placeholder="Enter your email" required>
                                </div>
                                <div class="form-group">
                                    <label>Phone Number</label>
                                    <input type="tel" name="PhoneNumber" placeholder="Enter your phone number">
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Work Type</label>
                                <select name="WorkType">
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
                                <input type="text" name="Skills" placeholder="Enter your skills (comma separated)">
                            </div>
                            <div class="form-group">
                                <label>Password</label>
                              <input type="password" name="Password" placeholder="Create a strong password" required>
                            </div>
                            <div class="form-group">
                                <label>Confirm Password</label>
                                <input type="password" name="Password_confirmation" placeholder="Confirm your password" required>
                            </div>
                            @if($errors->any())
                                <div style="color: #ff4444; margin-bottom: 15px;">
                                    @foreach($errors->all() as $error)
                                        <div>{{ $error }}</div>
                                    @endforeach
                                </div>
                            @endif
                            <button type="submit" class="sign-up-btn">
                                <i class="fas fa-user-plus"></i>
                                Create Candidate Account
                            </button>
                        </form>
                    </div>
                    
                    <!-- Company Sign Up Form -->
                    <div class="tab-content" id="company-tab">
                        <form id="companyForm" method="POST" action="{{ url('/register/company') }}">
                            @csrf
                            <div class="form-group">
                                <label>Company Name</label>
                                <input type="text" name="CompanyName" placeholder="Enter your company name" required>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Industry</label>
                                    <select name="Industry">
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
                                    <select name="CompanySize">
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
                                    <input type="email" name="Email" placeholder="Enter company email" required>
                                </div>
                                <div class="form-group">
                                    <label>Phone Number</label>
                                    <input type="tel" name="PhoneNumber" placeholder="Enter company phone">
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Company Description</label>
                                <textarea name="Description" rows="3" placeholder="Brief description of your company"></textarea>
                            </div>
                            <div class="form-group">
                                <label>Password</label>
                                <input type="password" name="Password" placeholder="Create a strong password" required>
                            </div>
                            <div class="form-group">
                                <label>Confirm Password</label>
                                <input type="password" name="Password_confirmation" placeholder="Confirm your password" required>
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

    <div style="height: 50px; width: 100%; clear: both;"></div>

    <!-- Features Section -->
    <section id="features" class="features-section" style="position: relative; z-index: 5; clear: both; background: linear-gradient(135deg, #0c1445 0%, #1a237e 50%, #283593 100%); margin-top: 400px;">
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
    <section id="about" class="about-section" style="position: relative; z-index: 5; clear: both; background: linear-gradient(135deg, #1e237e 0%, #0c1445 100%);">
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
    </div><!-- End main-wrapper -->

    <style>
        /* Main wrapper to separate hero from content */
        .main-wrapper {
            position: relative;
            z-index: 50;
            background: linear-gradient(180deg, #0a0e14 0%, #0d1117 5%, #0d1117 100%);
            min-height: 100vh;
            box-shadow: 0 -20px 60px rgba(0, 0, 0, 0.8);
        }
        
        /* Ensure header stays on top within main wrapper */
        .main-wrapper .header {
            position: sticky;
            top: 0;
            z-index: 100;
            background: linear-gradient(90deg, rgba(12, 20, 69, 0.95) 0%, rgba(26, 35, 126, 0.95) 100%);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        /* Ensure main wrapper handles background */
        .main-wrapper {
            background: linear-gradient(135deg, #0c1445 0%, #1a237e 50%, #283593 100%);
            position: relative;
            display: flex;
            flex-direction: column;
            width: 100%;
            overflow: visible;
        }

        /* Fix main content layout and Z-INDEX */
        .main-wrapper .main-content {
            padding-top: 40px;
            padding-bottom: 80px; /* Add extra breathing room bottom */
            height: auto;
            min-height: auto;
            align-items: center; 
            display: flex; 
            justify-content: space-between;
            gap: 50px;
            position: relative; /* Required for z-index */
            z-index: 20; /* Ensure this is HIGHER than features section */
        }

        /* Ensure content left takes space */
        .content-left {
            flex: 1;
            padding-right: 20px;
            position: relative;
            z-index: 21;
        }

        /* Ensure auth container doesn't shrink */
        .auth-container {
            width: 400px;
            flex-shrink: 0;
            position: relative;
            z-index: 22; 
        }

        /* Features section should flow naturally */
        .features-section {
            position: relative;
            z-index: 5; /* Lower than main content (20) */
            background: #0d1117; 
            margin-top: 0;
            clear: both;
        }
        
        /* Responsive fixes */
        @media (max-width: 1024px) {
            .main-wrapper .main-content {
                flex-direction: column;
                text-align: center;
                gap: 60px;
                padding-top: 60px; /* More space on mobile to clear header */
            }
            
            .auth-container {
                width: 100%;
                max-width: 450px;
            }
            
            .content-left {
                padding-right: 0;
            }
        }
    </style>

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
    // Candidate and Company forms now use standard Laravel POST submission
    // JavaScript handlers disabled - forms submit directly to Laravel routes:
    // - Candidate: POST /register/candidate
    // - Company: POST /register/company
    
    // Login form now uses standard form submission - JavaScript handler disabled
    // The form uses POST action to /login with CSRF token
}

            function setupPasswordToggle() {
                const togglePassword = document.querySelector('#togglePassword');
                const password = document.querySelector('#loginPassword');

                if (!togglePassword || !password) return;

                togglePassword.addEventListener('click', function (e) {
                    // toggle the type attribute
                    const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                    password.setAttribute('type', type);
                    
                    // toggle the eye slash icon
                    this.classList.toggle('fa-eye');
                    this.classList.toggle('fa-eye-slash');
                });
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
                    setupPasswordToggle();
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

    <!-- Hero Section Styles -->
    <style>
        /* Hero Landing Section */
        .hero-landing {
            position: relative;
            width: 100%;
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            z-index: 100;
        }
        
        .hero-background {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
        }
        
        .logo-watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 60vw;
            height: 60vw;
            max-width: 600px;
            max-height: 600px;
            opacity: 0.08;
            animation: logoFloat 8s ease-in-out infinite, logoPulse 4s ease-in-out infinite;
        }
        
        .logo-watermark img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            filter: blur(2px);
        }
        
        .gradient-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(ellipse at 20% 80%, rgba(88, 166, 255, 0.15) 0%, transparent 50%),
                radial-gradient(ellipse at 80% 20%, rgba(245, 158, 11, 0.15) 0%, transparent 50%),
                radial-gradient(ellipse at 40% 40%, rgba(139, 92, 246, 0.1) 0%, transparent 40%),
                linear-gradient(135deg, rgba(12, 20, 69, 0.9) 0%, rgba(26, 35, 126, 0.9) 50%, rgba(40, 53, 147, 0.9) 100%);
            z-index: 2;
        }
        
        .animated-particles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 3;
            pointer-events: none;
        }
        
        .particle {
            position: absolute;
            width: 6px;
            height: 6px;
            background: linear-gradient(135deg, #58a6ff, #f59e0b);
            border-radius: 50%;
            opacity: 0;
            animation: particleFloat 15s linear infinite;
        }
        
        .particle:nth-child(even) {
            background: linear-gradient(135deg, #f59e0b, #8b5cf6);
        }
        
        .hero-content {
            position: relative;
            z-index: 10;
            text-align: center;
            padding: 20px;
        }
        
        .hero-logo-animated {
            width: 150px;
            height: 150px;
            margin: 0 auto 30px;
            animation: logoEnter 1.5s ease-out, logoBreathe 3s ease-in-out infinite 1.5s;
        }
        
        .hero-logo-img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            filter: drop-shadow(0 10px 30px rgba(88, 166, 255, 0.4));
        }
        
        .hero-title {
            font-size: clamp(3rem, 10vw, 6rem);
            font-weight: 800;
            margin: 0;
            letter-spacing: -2px;
            animation: titleReveal 1s ease-out 0.3s both;
        }
        
        .title-candi {
            color: #ffffff;
            text-shadow: 0 4px 20px rgba(255, 255, 255, 0.3);
        }
        
        .title-hire {
            background: linear-gradient(135deg, #58a6ff, #f59e0b);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: gradientShift 5s ease-in-out infinite;
            background-size: 200% 200%;
        }
        
        .hero-tagline {
            font-size: clamp(1rem, 3vw, 1.5rem);
            color: #58a6ff;
            letter-spacing: 8px;
            font-weight: 600;
            margin: 15px 0;
            animation: fadeInUp 1s ease-out 0.6s both;
        }
        
        .hero-subtitle {
            font-size: clamp(1rem, 2.5vw, 1.3rem);
            color: rgba(255, 255, 255, 0.7);
            margin: 10px 0 0;
            animation: fadeInUp 1s ease-out 0.9s both;
        }
        
        .scroll-indicator {
            position: absolute;
            bottom: 40px;
            left: 0;
            right: 0;
            margin: 0 auto;
            width: fit-content;
            z-index: 10;
            cursor: pointer;
            text-align: center;
            animation: fadeInUp 1s ease-out 1.2s both;
            transition: transform 0.3s ease;
        }
        
        .scroll-indicator:hover {
            transform: scale(1.1);
        }
        
        .scroll-text {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 15px;
        }
        
        .scroll-arrow {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0;
        }
        
        .scroll-arrow i {
            color: #58a6ff;
            font-size: 1.2rem;
            animation: scrollBounce 1.5s ease-in-out infinite;
            opacity: 0.6;
        }
        
        .scroll-arrow i:nth-child(1) {
            animation-delay: 0s;
        }
        
        .scroll-arrow i:nth-child(2) {
            animation-delay: 0.15s;
            opacity: 0.4;
        }
        
        .scroll-arrow i:nth-child(3) {
            animation-delay: 0.3s;
            opacity: 0.2;
        }
        
        /* Hero Animations */
        @keyframes logoFloat {
            0%, 100% { transform: translate(-50%, -50%) rotate(0deg) scale(1); }
            25% { transform: translate(-50%, -52%) rotate(2deg) scale(1.02); }
            50% { transform: translate(-50%, -50%) rotate(0deg) scale(1.05); }
            75% { transform: translate(-50%, -48%) rotate(-2deg) scale(1.02); }
        }
        
        @keyframes logoPulse {
            0%, 100% { opacity: 0.08; }
            50% { opacity: 0.12; }
        }
        
        @keyframes logoEnter {
            0% { transform: scale(0) rotate(-180deg); opacity: 0; }
            100% { transform: scale(1) rotate(0deg); opacity: 1; }
        }
        
        @keyframes logoBreathe {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        @keyframes titleReveal {
            0% { opacity: 0; transform: translateY(30px); letter-spacing: 20px; }
            100% { opacity: 1; transform: translateY(0); letter-spacing: -2px; }
        }
        
        @keyframes fadeInUp {
            0% { opacity: 0; transform: translateY(20px); }
            100% { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes gradientShift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }
        
        @keyframes scrollBounce {
            0%, 100% { transform: translateY(0); opacity: 0.6; }
            50% { transform: translateY(8px); opacity: 1; }
        }
        
        @keyframes particleFloat {
            0% { 
                transform: translateY(100vh) rotate(0deg); 
                opacity: 0; 
            }
            10% { opacity: 0.8; }
            90% { opacity: 0.8; }
            100% { 
                transform: translateY(-100vh) rotate(720deg); 
                opacity: 0; 
            }
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .hero-logo-animated {
                width: 100px;
                height: 100px;
            }
            
            .scroll-indicator {
                bottom: 20px;
            }
            
            .logo-watermark {
                width: 80vw;
                height: 80vw;
            }
        }
    </style>

    <!-- Hero Section JavaScript -->
    <script>
        // Create animated particles
        function createParticles() {
            const container = document.getElementById('particlesContainer');
            if (!container) return;
            
            const particleCount = 30;
            
            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                particle.style.left = Math.random() * 100 + '%';
                particle.style.animationDelay = Math.random() * 15 + 's';
                particle.style.animationDuration = (15 + Math.random() * 10) + 's';
                particle.style.width = (4 + Math.random() * 4) + 'px';
                particle.style.height = particle.style.width;
                container.appendChild(particle);
            }
        }
        
        // Smooth scroll to main content
        function scrollToContent() {
            const heroSection = document.getElementById('heroLanding');
            if (heroSection) {
                const scrollTarget = heroSection.offsetHeight;
                window.scrollTo({
                    top: scrollTarget,
                    behavior: 'smooth'
                });
            }
        }
        
        // Hide hero on scroll (optional parallax effect)
        function setupHeroParallax() {
            const hero = document.getElementById('heroLanding');
            if (!hero) return;
            
            window.addEventListener('scroll', function() {
                const scrolled = window.pageYOffset;
                const heroHeight = hero.offsetHeight;
                
                if (scrolled < heroHeight) {
                    const opacity = 1 - (scrolled / heroHeight);
                    const scale = 1 + (scrolled / heroHeight) * 0.2;
                    
                    hero.style.opacity = opacity;
                    hero.querySelector('.hero-content').style.transform = `scale(${scale}) translateY(${scrolled * 0.5}px)`;
                }
            });
        }
        
        // Initialize hero animations
        document.addEventListener('DOMContentLoaded', function() {
            createParticles();
            setupHeroParallax();
        });
    </script>
</body>
</html>

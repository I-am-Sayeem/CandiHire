<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candidate Registration - CandiHire</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/Login_Signup.css') }}">
    <script src="{{ asset('js/notification_system.js') }}"></script>
    <style>
        /* Override generic styles to center the form */
        .main-content {
            justify-content: center;
        }
        .content-left {
            display: none;
        }
        .auth-container {
            width: 100%;
            max-width: 550px; /* Slightly wider for registration form */
            margin: 0 auto;
        }
        .signup-section {
            display: block !important; /* Force show */
            opacity: 1 !important;
            transform: none !important;
        }
        .signup-tabs {
            display: none; /* Hide tabs since this is dedicated candidate page */
        }
        .tab-content {
            display: block !important; /* Force show content */
            padding-top: 20px;
        }
        .login-header {
            margin-bottom: 20px;
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
                <div class="signup-section">
                    <div class="login-header">
                        <div class="login-title">Join as Candidate</div>
                        <div class="login-subtitle">Create your candidate profile and start applying</div>
                    </div>
                    
                    <form id="candidateRegisterForm" method="POST" action="">
                        @csrf
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
                                <option value="fresher">Fresher</option>
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
                    
                    <div class="back-to-login">
                        Already have an account? <a href="{{ url('login') }}">Sign in</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Skills Input Logic
        document.addEventListener('DOMContentLoaded', function() {
            const skillInput = document.getElementById('skillInput');
            const skillsList = document.getElementById('skillsList');
            const hiddenSkillsInput = document.getElementById('skillsInput');
            
            if (!skillInput || !skillsList) return;
            
            let skills = [];

            skillInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.keyCode === 13) {
                    e.preventDefault();
                    const skill = this.value.trim();
                    if (skill && !skills.includes(skill)) {
                        skills.push(skill);
                        updateSkillsList();
                        this.value = '';
                    }
                }
            });

            function updateSkillsList() {
                skillsList.innerHTML = '';
                skills.forEach(function(skill) {
                    const skillTag = document.createElement('div');
                    skillTag.className = 'skill-tag';
                    skillTag.innerHTML = `${skill} <span class="remove-skill" data-skill="${skill}">×</span>`;
                    skillsList.appendChild(skillTag);
                });

                if (hiddenSkillsInput) {
                    hiddenSkillsInput.value = skills.join(',');
                }

                // Re-attach removal event listeners
                document.querySelectorAll('.remove-skill').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const skillToRemove = this.getAttribute('data-skill');
                        skills = skills.filter(s => s !== skillToRemove);
                        updateSkillsList();
                    });
                });
            }
        });

        // Background Animation
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

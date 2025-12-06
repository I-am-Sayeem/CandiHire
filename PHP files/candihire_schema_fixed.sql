-- =============================================
-- CandiHire Database Schema (Fixed Version)
-- =============================================

-- Database Creation
CREATE DATABASE IF NOT EXISTS candihire CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE candihire;

-- =============================================
-- 1. CANDIDATE LOGIN INFO TABLE
-- =============================================
CREATE TABLE candidate_login_info (
    CandidateID INT AUTO_INCREMENT PRIMARY KEY,
    FullName VARCHAR(255) NOT NULL,
    Email VARCHAR(255) UNIQUE NOT NULL,
    PhoneNumber VARCHAR(20) NOT NULL,
    WorkType ENUM('full-time', 'part-time', 'contract', 'freelance', 'internship', 'fresher') NOT NULL,
    Skills TEXT,
    Password VARCHAR(255) NOT NULL,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    IsActive BOOLEAN DEFAULT TRUE,
    ProfilePicture VARCHAR(500),
    Location VARCHAR(255),
    Summary TEXT,
    LinkedIn VARCHAR(500),
    GitHub VARCHAR(500),
    Portfolio VARCHAR(500),
    YearsOfExperience INT DEFAULT 0
);

-- =============================================
-- 2. COMPANY LOGIN INFO TABLE
-- =============================================
CREATE TABLE Company_login_info (
    CompanyID INT AUTO_INCREMENT PRIMARY KEY,
    CompanyName VARCHAR(255) NOT NULL,
    Industry VARCHAR(100),
    CompanySize ENUM('1-10', '11-50', '51-200', '201-500', '501-1000', '1000+'),
    Email VARCHAR(255) UNIQUE NOT NULL,
    PhoneNumber VARCHAR(20),
    CompanyDescription TEXT,
    Password VARCHAR(255) NOT NULL,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    IsActive BOOLEAN DEFAULT TRUE,
    Website VARCHAR(500),
    Logo VARCHAR(500),
    Address TEXT,
    City VARCHAR(100),
    State VARCHAR(100),
    Country VARCHAR(100),
    PostalCode VARCHAR(20)
);

-- =============================================
-- 3. CV/RESUME DATA TABLE
-- =============================================
CREATE TABLE candidate_cv_data (
    CvID INT AUTO_INCREMENT PRIMARY KEY,
    CandidateID INT NOT NULL,
    FirstName VARCHAR(100),
    LastName VARCHAR(100),
    Email VARCHAR(255),
    Phone VARCHAR(20),
    Address TEXT,
    Summary TEXT,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (CandidateID) REFERENCES candidate_login_info(CandidateID) ON DELETE CASCADE
);

-- =============================================
-- 4. WORK EXPERIENCE TABLE
-- =============================================
CREATE TABLE candidate_experience (
    ExperienceID INT AUTO_INCREMENT PRIMARY KEY,
    CandidateID INT NOT NULL,
    JobTitle VARCHAR(255),
    Company VARCHAR(255),
    StartDate DATE,
    EndDate DATE,
    Description TEXT,
    Location VARCHAR(255),
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (CandidateID) REFERENCES candidate_login_info(CandidateID) ON DELETE CASCADE
);

-- =============================================
-- 5. EDUCATION TABLE
-- =============================================
CREATE TABLE candidate_education (
    EducationID INT AUTO_INCREMENT PRIMARY KEY,
    CandidateID INT NOT NULL,
    Degree VARCHAR(255),
    Institution VARCHAR(255),
    StartYear YEAR,
    EndYear YEAR,
    GPA DECIMAL(3,2),
    Location VARCHAR(255),
    Coursework TEXT,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (CandidateID) REFERENCES candidate_login_info(CandidateID) ON DELETE CASCADE
);

-- =============================================
-- 6. SKILLS TABLE
-- =============================================
CREATE TABLE candidate_skills (
    SkillID INT AUTO_INCREMENT PRIMARY KEY,
    CandidateID INT NOT NULL,
    ProgrammingLanguages TEXT,
    Frameworks TEXT,
    `Databases` TEXT,
    Tools TEXT,
    SoftSkills TEXT,
    Languages TEXT,
    Certifications TEXT,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (CandidateID) REFERENCES candidate_login_info(CandidateID) ON DELETE CASCADE
);

-- =============================================
-- 7. PROJECTS TABLE
-- =============================================
CREATE TABLE candidate_projects (
    ProjectID INT AUTO_INCREMENT PRIMARY KEY,
    CandidateID INT NOT NULL,
    ProjectName VARCHAR(255),
    Role VARCHAR(255),
    StartDate DATE,
    EndDate DATE,
    Description TEXT,
    Technologies TEXT,
    ProjectUrl VARCHAR(500),
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (CandidateID) REFERENCES candidate_login_info(CandidateID) ON DELETE CASCADE
);

-- =============================================
-- 8. JOB POSTINGS TABLE
-- =============================================
CREATE TABLE job_postings (
    JobID INT AUTO_INCREMENT PRIMARY KEY,
    CompanyID INT NOT NULL,
    JobTitle VARCHAR(255) NOT NULL,
    Department VARCHAR(100),
    JobDescription TEXT,
    Requirements TEXT,
    Responsibilities TEXT,
    Skills TEXT,
    Location VARCHAR(255),
    JobType ENUM('full-time', 'part-time', 'contract', 'freelance', 'internship') NOT NULL,
    SalaryMin DECIMAL(10,2),
    SalaryMax DECIMAL(10,2),
    Currency VARCHAR(3) DEFAULT 'USD',
    ExperienceLevel ENUM('entry', 'mid', 'senior', 'lead', 'executive'),
    EducationLevel ENUM('high-school', 'associate', 'bachelor', 'master', 'phd'),
    Status ENUM('active', 'closed', 'draft', 'paused') DEFAULT 'active',
    PostedDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ClosingDate DATE,
    ApplicationCount INT DEFAULT 0,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (CompanyID) REFERENCES Company_login_info(CompanyID) ON DELETE CASCADE
);

-- =============================================
-- 9. JOB APPLICATIONS TABLE
-- =============================================
CREATE TABLE job_applications (
    ApplicationID INT AUTO_INCREMENT PRIMARY KEY,
    CandidateID INT NOT NULL,
    JobID INT NOT NULL,
    ApplicationDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    Status ENUM('submitted', 'under-review', 'shortlisted', 'interview-scheduled', 'interviewed', 'offer-extended', 'accepted', 'rejected', 'withdrawn') DEFAULT 'submitted',
    CoverLetter TEXT,
    ResumePath VARCHAR(500),
    Notes TEXT,
    ContactPerson VARCHAR(255),
    ContactEmail VARCHAR(255),
    SalaryExpectation DECIMAL(10,2),
    AvailabilityDate DATE,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (CandidateID) REFERENCES candidate_login_info(CandidateID) ON DELETE CASCADE,
    FOREIGN KEY (JobID) REFERENCES job_postings(JobID) ON DELETE CASCADE,
    UNIQUE KEY unique_application (CandidateID, JobID)
);

-- =============================================
-- 10. APPLICATION STATUS HISTORY TABLE
-- =============================================
CREATE TABLE application_status_history (
    StatusHistoryID INT AUTO_INCREMENT PRIMARY KEY,
    ApplicationID INT NOT NULL,
    Status VARCHAR(50) NOT NULL,
    StatusDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    Notes TEXT,
    UpdatedBy VARCHAR(100),
    FOREIGN KEY (ApplicationID) REFERENCES job_applications(ApplicationID) ON DELETE CASCADE
);

-- =============================================
-- 11. EXAMS TABLE
-- =============================================
CREATE TABLE exams (
    ExamID INT AUTO_INCREMENT PRIMARY KEY,
    CompanyID INT NOT NULL,
    ExamTitle VARCHAR(255) NOT NULL,
    ExamType ENUM('auto-generated', 'manual', 'mcq', 'coding', 'mixed') NOT NULL,
    Description TEXT,
    Instructions TEXT,
    Duration INT NOT NULL,
    QuestionCount INT DEFAULT 0,
    PassingScore DECIMAL(5,2) DEFAULT 70.00,
    MaxAttempts INT DEFAULT 1,
    IsActive BOOLEAN DEFAULT TRUE,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CreatedBy VARCHAR(100),
    FOREIGN KEY (CompanyID) REFERENCES Company_login_info(CompanyID) ON DELETE CASCADE
);

-- =============================================
-- 12. EXAM QUESTIONS TABLE
-- =============================================
CREATE TABLE exam_questions (
    QuestionID INT AUTO_INCREMENT PRIMARY KEY,
    ExamID INT NOT NULL,
    QuestionType ENUM('multiple-choice', 'true-false', 'coding', 'essay', 'fill-blank') NOT NULL,
    QuestionText TEXT NOT NULL,
    QuestionOrder INT NOT NULL,
    Points DECIMAL(5,2) DEFAULT 1.00,
    Difficulty ENUM('easy', 'medium', 'hard') DEFAULT 'medium',
    Category VARCHAR(100),
    Tags TEXT,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (ExamID) REFERENCES exams(ExamID) ON DELETE CASCADE
);

-- =============================================
-- 13. EXAM QUESTION OPTIONS TABLE
-- =============================================
CREATE TABLE exam_question_options (
    OptionID INT AUTO_INCREMENT PRIMARY KEY,
    QuestionID INT NOT NULL,
    OptionText TEXT NOT NULL,
    IsCorrect BOOLEAN DEFAULT FALSE,
    OptionOrder INT NOT NULL,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (QuestionID) REFERENCES exam_questions(QuestionID) ON DELETE CASCADE
);

-- =============================================
-- 14. EXAM SCHEDULES TABLE
-- =============================================
CREATE TABLE exam_schedules (
    ScheduleID INT AUTO_INCREMENT PRIMARY KEY,
    ExamID INT NOT NULL,
    CandidateID INT,
    JobID INT,
    ScheduledDate DATE NOT NULL,
    ScheduledTime TIME NOT NULL,
    Status ENUM('scheduled', 'in-progress', 'completed', 'cancelled', 'expired') DEFAULT 'scheduled',
    Duration INT,
    AttemptsUsed INT DEFAULT 0,
    MaxAttempts INT DEFAULT 1,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (ExamID) REFERENCES exams(ExamID) ON DELETE CASCADE,
    FOREIGN KEY (CandidateID) REFERENCES candidate_login_info(CandidateID) ON DELETE CASCADE,
    FOREIGN KEY (JobID) REFERENCES job_postings(JobID) ON DELETE CASCADE
);

-- =============================================
-- 15. EXAM ATTEMPTS TABLE
-- =============================================
CREATE TABLE exam_attempts (
    AttemptID INT AUTO_INCREMENT PRIMARY KEY,
    ScheduleID INT NOT NULL,
    CandidateID INT NOT NULL,
    ExamID INT NOT NULL,
    StartTime TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    EndTime TIMESTAMP NULL,
    Status ENUM('in-progress', 'completed', 'abandoned', 'timeout') DEFAULT 'in-progress',
    Score DECIMAL(5,2),
    TotalQuestions INT DEFAULT 0,
    CorrectAnswers INT DEFAULT 0,
    TimeSpent INT,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (ScheduleID) REFERENCES exam_schedules(ScheduleID) ON DELETE CASCADE,
    FOREIGN KEY (CandidateID) REFERENCES candidate_login_info(CandidateID) ON DELETE CASCADE,
    FOREIGN KEY (ExamID) REFERENCES exams(ExamID) ON DELETE CASCADE
);

-- =============================================
-- 16. EXAM ANSWERS TABLE
-- =============================================
CREATE TABLE exam_answers (
    AnswerID INT AUTO_INCREMENT PRIMARY KEY,
    AttemptID INT NOT NULL,
    QuestionID INT NOT NULL,
    AnswerText TEXT,
    SelectedOptionID INT,
    IsCorrect BOOLEAN,
    PointsEarned DECIMAL(5,2) DEFAULT 0.00,
    TimeSpent INT,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (AttemptID) REFERENCES exam_attempts(AttemptID) ON DELETE CASCADE,
    FOREIGN KEY (QuestionID) REFERENCES exam_questions(QuestionID) ON DELETE CASCADE,
    FOREIGN KEY (SelectedOptionID) REFERENCES exam_question_options(OptionID) ON DELETE SET NULL
);

-- =============================================
-- 17. INTERVIEWS TABLE
-- =============================================
CREATE TABLE interviews (
    InterviewID INT AUTO_INCREMENT PRIMARY KEY,
    CandidateID INT NOT NULL,
    CompanyID INT NOT NULL,
    JobID INT,
    InterviewTitle VARCHAR(255) NOT NULL,
    InterviewType ENUM('technical', 'hr', 'behavioral', 'panel', 'final') NOT NULL,
    InterviewMode ENUM('virtual', 'onsite', 'phone') NOT NULL,
    Platform VARCHAR(100),
    ScheduledDate DATE NOT NULL,
    ScheduledTime TIME NOT NULL,
    Duration INT DEFAULT 60,
    Location TEXT,
    InterviewerName VARCHAR(255),
    InterviewerEmail VARCHAR(255),
    InterviewerPhone VARCHAR(20),
    Status ENUM('scheduled', 'in-progress', 'completed', 'cancelled', 'rescheduled') DEFAULT 'scheduled',
    Notes TEXT,
    Feedback TEXT,
    Rating DECIMAL(3,2),
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (CandidateID) REFERENCES candidate_login_info(CandidateID) ON DELETE CASCADE,
    FOREIGN KEY (CompanyID) REFERENCES Company_login_info(CompanyID) ON DELETE CASCADE,
    FOREIGN KEY (JobID) REFERENCES job_postings(JobID) ON DELETE SET NULL
);

-- =============================================
-- 18. AI MATCHING RESULTS TABLE
-- =============================================
CREATE TABLE ai_matching_results (
    MatchID INT AUTO_INCREMENT PRIMARY KEY,
    CandidateID INT NOT NULL,
    JobID INT NOT NULL,
    CompanyID INT NOT NULL,
    MatchPercentage DECIMAL(5,2) NOT NULL,
    SkillsMatch DECIMAL(5,2),
    ExperienceMatch DECIMAL(5,2),
    EducationMatch DECIMAL(5,2),
    LocationMatch DECIMAL(5,2),
    SalaryMatch DECIMAL(5,2),
    MatchFactors TEXT,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (CandidateID) REFERENCES candidate_login_info(CandidateID) ON DELETE CASCADE,
    FOREIGN KEY (JobID) REFERENCES job_postings(JobID) ON DELETE CASCADE,
    FOREIGN KEY (CompanyID) REFERENCES Company_login_info(CompanyID) ON DELETE CASCADE,
    UNIQUE KEY unique_match (CandidateID, JobID)
);

-- =============================================
-- 19. CV CHECKER RESULTS TABLE
-- =============================================
CREATE TABLE cv_checker_results (
    CheckID INT AUTO_INCREMENT PRIMARY KEY,
    CandidateID INT NOT NULL,
    CvFilePath VARCHAR(500) NOT NULL,
    CheckDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    OverallScore DECIMAL(5,2),
    GrammarScore DECIMAL(5,2),
    FormatScore DECIMAL(5,2),
    ContentScore DECIMAL(5,2),
    Suggestions TEXT,
    Errors TEXT,
    Warnings TEXT,
    Improvements TEXT,
    Status ENUM('processing', 'completed', 'failed') DEFAULT 'processing',
    FOREIGN KEY (CandidateID) REFERENCES candidate_login_info(CandidateID) ON DELETE CASCADE
);

-- =============================================
-- 20. SESSIONS TABLE
-- =============================================
CREATE TABLE user_sessions (
    SessionID INT AUTO_INCREMENT PRIMARY KEY,
    UserID INT NOT NULL,
    UserType ENUM('candidate', 'company') NOT NULL,
    SessionToken VARCHAR(255) UNIQUE NOT NULL,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ExpiresAt TIMESTAMP NOT NULL,
    IsActive BOOLEAN DEFAULT TRUE,
    IPAddress VARCHAR(45),
    UserAgent TEXT
);

-- =============================================
-- 21. NOTIFICATIONS TABLE
-- =============================================
CREATE TABLE notifications (
    NotificationID INT AUTO_INCREMENT PRIMARY KEY,
    UserID INT NOT NULL,
    UserType ENUM('candidate', 'company') NOT NULL,
    Title VARCHAR(255) NOT NULL,
    Message TEXT NOT NULL,
    Type ENUM('info', 'success', 'warning', 'error', 'interview', 'exam', 'application') NOT NULL,
    IsRead BOOLEAN DEFAULT FALSE,
    ActionUrl VARCHAR(500),
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ReadAt TIMESTAMP NULL
);

-- =============================================
-- 22. SYSTEM SETTINGS TABLE
-- =============================================
CREATE TABLE system_settings (
    SettingID INT AUTO_INCREMENT PRIMARY KEY,
    SettingKey VARCHAR(100) UNIQUE NOT NULL,
    SettingValue TEXT,
    Description TEXT,
    Category VARCHAR(50),
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =============================================
-- INDEXES FOR PERFORMANCE
-- =============================================

-- Candidate indexes
CREATE INDEX idx_candidate_email ON candidate_login_info(Email);
CREATE INDEX idx_candidate_skills ON candidate_login_info(Skills(255));
CREATE INDEX idx_candidate_worktype ON candidate_login_info(WorkType);
CREATE INDEX idx_candidate_created ON candidate_login_info(CreatedAt);

-- Company indexes
CREATE INDEX idx_company_email ON Company_login_info(Email);
CREATE INDEX idx_company_industry ON Company_login_info(Industry);
CREATE INDEX idx_company_size ON Company_login_info(CompanySize);

-- Job posting indexes
CREATE INDEX idx_job_company ON job_postings(CompanyID);
CREATE INDEX idx_job_status ON job_postings(Status);
CREATE INDEX idx_job_type ON job_postings(JobType);
CREATE INDEX idx_job_skills ON job_postings(Skills(255));
CREATE INDEX idx_job_posted ON job_postings(PostedDate);

-- Application indexes
CREATE INDEX idx_application_candidate ON job_applications(CandidateID);
CREATE INDEX idx_application_job ON job_applications(JobID);
CREATE INDEX idx_application_status ON job_applications(Status);
CREATE INDEX idx_application_date ON job_applications(ApplicationDate);

-- Exam indexes
CREATE INDEX idx_exam_company ON exams(CompanyID);
CREATE INDEX idx_exam_type ON exams(ExamType);
CREATE INDEX idx_exam_active ON exams(IsActive);

-- Interview indexes
CREATE INDEX idx_interview_candidate ON interviews(CandidateID);
CREATE INDEX idx_interview_company ON interviews(CompanyID);
CREATE INDEX idx_interview_date ON interviews(ScheduledDate);
CREATE INDEX idx_interview_status ON interviews(Status);

-- AI Matching indexes
CREATE INDEX idx_match_candidate ON ai_matching_results(CandidateID);
CREATE INDEX idx_match_job ON ai_matching_results(JobID);
CREATE INDEX idx_match_percentage ON ai_matching_results(MatchPercentage);

-- =============================================
-- SAMPLE DATA INSERTION
-- =============================================

-- Insert sample system settings
INSERT INTO system_settings (SettingKey, SettingValue, Description, Category) VALUES
('site_name', 'CandiHire', 'Website name', 'general'),
('max_file_size', '10485760', 'Maximum file upload size in bytes', 'uploads'),
('allowed_file_types', 'pdf,doc,docx', 'Allowed file types for uploads', 'uploads'),
('exam_timeout', '3600', 'Default exam timeout in seconds', 'exams'),
('interview_reminder_hours', '24', 'Hours before interview to send reminder', 'notifications');

-- =============================================
-- TRIGGERS FOR AUTOMATIC UPDATES
-- =============================================

-- Update application count when new application is submitted
DELIMITER //
CREATE TRIGGER update_job_application_count 
AFTER INSERT ON job_applications
FOR EACH ROW
BEGIN
    UPDATE job_postings 
    SET ApplicationCount = ApplicationCount + 1 
    WHERE JobID = NEW.JobID;
END//
DELIMITER ;

-- Update application count when application is deleted
DELIMITER //
CREATE TRIGGER decrease_job_application_count 
AFTER DELETE ON job_applications
FOR EACH ROW
BEGIN
    UPDATE job_postings 
    SET ApplicationCount = ApplicationCount - 1 
    WHERE JobID = OLD.JobID;
END//
DELIMITER ;

-- =============================================
-- VIEWS FOR COMMON QUERIES
-- =============================================

-- View for candidate dashboard data
CREATE VIEW candidate_dashboard_view AS
SELECT 
    c.CandidateID,
    c.FullName,
    c.Email,
    c.WorkType,
    c.Skills,
    c.CreatedAt,
    COUNT(DISTINCT ja.ApplicationID) as TotalApplications,
    COUNT(DISTINCT i.InterviewID) as TotalInterviews,
    COUNT(DISTINCT ea.AttemptID) as TotalExams
FROM candidate_login_info c
LEFT JOIN job_applications ja ON c.CandidateID = ja.CandidateID
LEFT JOIN interviews i ON c.CandidateID = i.CandidateID
LEFT JOIN exam_attempts ea ON c.CandidateID = ea.CandidateID
GROUP BY c.CandidateID;

-- View for company dashboard data
CREATE VIEW company_dashboard_view AS
SELECT 
    co.CompanyID,
    co.CompanyName,
    co.Industry,
    co.CompanySize,
    COUNT(DISTINCT jp.JobID) as TotalJobs,
    COUNT(DISTINCT ja.ApplicationID) as TotalApplications,
    COUNT(DISTINCT i.InterviewID) as TotalInterviews,
    COUNT(DISTINCT e.ExamID) as TotalExams
FROM Company_login_info co
LEFT JOIN job_postings jp ON co.CompanyID = jp.CompanyID
LEFT JOIN job_applications ja ON jp.JobID = ja.JobID
LEFT JOIN interviews i ON co.CompanyID = i.CompanyID
LEFT JOIN exams e ON co.CompanyID = e.CompanyID
GROUP BY co.CompanyID;

-- View for job applications with details
CREATE VIEW job_applications_view AS
SELECT 
    ja.ApplicationID,
    ja.ApplicationDate,
    ja.Status,
    c.FullName as CandidateName,
    c.Email as CandidateEmail,
    c.PhoneNumber as CandidatePhone,
    jp.JobTitle,
    co.CompanyName,
    jp.Location,
    jp.JobType,
    jp.SalaryMin,
    jp.SalaryMax
FROM job_applications ja
JOIN candidate_login_info c ON ja.CandidateID = c.CandidateID
JOIN job_postings jp ON ja.JobID = jp.JobID
JOIN Company_login_info co ON jp.CompanyID = co.CompanyID;

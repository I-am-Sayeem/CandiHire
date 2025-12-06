-- =============================================
-- Additional Tables for Exam System
-- =============================================

-- Question Bank for Auto-Generated Questions
CREATE TABLE question_bank (
    QuestionID INT AUTO_INCREMENT PRIMARY KEY,
    Department VARCHAR(100) NOT NULL,
    QuestionType ENUM('multiple-choice', 'true-false') NOT NULL,
    QuestionText TEXT NOT NULL,
    Difficulty ENUM('easy', 'medium', 'hard') DEFAULT 'medium',
    Category VARCHAR(100),
    Tags TEXT,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_department (Department),
    INDEX idx_difficulty (Difficulty),
    INDEX idx_category (Category)
);

-- Question Bank Options
CREATE TABLE question_bank_options (
    OptionID INT AUTO_INCREMENT PRIMARY KEY,
    QuestionID INT NOT NULL,
    OptionText TEXT NOT NULL,
    IsCorrect BOOLEAN DEFAULT FALSE,
    OptionOrder INT NOT NULL,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (QuestionID) REFERENCES question_bank(QuestionID) ON DELETE CASCADE
);

-- Exam Assignment to Candidates
CREATE TABLE exam_assignments (
    AssignmentID INT AUTO_INCREMENT PRIMARY KEY,
    ExamID INT NOT NULL,
    CandidateID INT NOT NULL,
    JobID INT NOT NULL,
    AssignmentDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    Status ENUM('assigned', 'in-progress', 'completed', 'expired') DEFAULT 'assigned',
    DueDate DATETIME,
    AttemptsUsed INT DEFAULT 0,
    MaxAttempts INT DEFAULT 1,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (ExamID) REFERENCES exams(ExamID) ON DELETE CASCADE,
    FOREIGN KEY (CandidateID) REFERENCES candidate_login_info(CandidateID) ON DELETE CASCADE,
    FOREIGN KEY (JobID) REFERENCES job_postings(JobID) ON DELETE CASCADE,
    UNIQUE KEY unique_assignment (ExamID, CandidateID, JobID)
);

-- Department to Job Position Mapping
CREATE TABLE department_positions (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    Department VARCHAR(100) NOT NULL,
    Position VARCHAR(100) NOT NULL,
    Description TEXT,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_dept_position (Department, Position)
);

-- Insert department-position mappings
INSERT INTO department_positions (Department, Position, Description) VALUES
('Software Engineering', 'Software Engineer', 'Full-stack software development'),
('Software Engineering', 'Frontend Developer', 'Frontend web development'),
('Software Engineering', 'Backend Developer', 'Backend API and server development'),
('Software Engineering', 'Full Stack Developer', 'Both frontend and backend development'),
('Data Science', 'Data Scientist', 'Data analysis and machine learning'),
('Data Science', 'Data Analyst', 'Data analysis and reporting'),
('Data Science', 'Machine Learning Engineer', 'ML model development and deployment'),
('Product Management', 'Product Manager', 'Product strategy and roadmap'),
('Product Management', 'Product Owner', 'Product requirements and backlog management'),
('Design', 'UI/UX Designer', 'User interface and experience design'),
('Design', 'Graphic Designer', 'Visual design and branding'),
('DevOps', 'DevOps Engineer', 'Infrastructure and deployment automation'),
('DevOps', 'Site Reliability Engineer', 'System reliability and monitoring'),
('Quality Assurance', 'QA Engineer', 'Software testing and quality assurance'),
('Quality Assurance', 'Test Automation Engineer', 'Automated testing development'),
('Business', 'Business Analyst', 'Business requirements analysis'),
('Business', 'Project Manager', 'Project planning and execution'),
('Marketing', 'Digital Marketer', 'Digital marketing and campaigns'),
('Marketing', 'Content Marketer', 'Content creation and marketing'),
('Human Resources', 'HR Specialist', 'Human resources management'),
('Human Resources', 'Recruiter', 'Talent acquisition'),
('Sales', 'Sales Representative', 'Sales and customer acquisition'),
('Finance', 'Accountant', 'Financial accounting and reporting'),
('Finance', 'Financial Analyst', 'Financial analysis and planning');

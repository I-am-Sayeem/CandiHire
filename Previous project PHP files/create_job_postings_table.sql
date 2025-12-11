-- =============================================
-- Job Postings Table Creation Script
-- =============================================
-- This script creates the job_postings table if it doesn't exist
-- and ensures all necessary fields are present

USE candihire;

-- Create job_postings table if it doesn't exist
CREATE TABLE IF NOT EXISTS job_postings (
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
    Status ENUM('active', 'inactive', 'closed', 'draft') DEFAULT 'active',
    PostedDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ClosingDate DATE,
    ApplicationCount INT DEFAULT 0,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (CompanyID) REFERENCES Company_login_info(CompanyID) ON DELETE CASCADE,
    INDEX idx_company_job (CompanyID),
    INDEX idx_job_status (Status),
    INDEX idx_job_type (JobType),
    INDEX idx_job_location (Location),
    INDEX idx_job_posted_date (PostedDate),
    INDEX idx_job_skills (Skills(255))
);

-- Create job_applications table if it doesn't exist
CREATE TABLE IF NOT EXISTS job_applications (
    ApplicationID INT AUTO_INCREMENT PRIMARY KEY,
    CandidateID INT NOT NULL,
    JobID INT NOT NULL,
    ApplicationDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    Status ENUM('submitted', 'under-review', 'shortlisted', 'interview-scheduled', 'interviewed', 'offer-extended', 'accepted', 'rejected', 'withdrawn') DEFAULT 'submitted',
    CoverLetter TEXT,
    ResumePath VARCHAR(500),
    Notes TEXT,
    AppliedVia ENUM('website', 'email', 'referral', 'other') DEFAULT 'website',
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (CandidateID) REFERENCES candidate_login_info(CandidateID) ON DELETE CASCADE,
    FOREIGN KEY (JobID) REFERENCES job_postings(JobID) ON DELETE CASCADE,
    UNIQUE KEY unique_application (CandidateID, JobID),
    INDEX idx_application_candidate (CandidateID),
    INDEX idx_application_job (JobID),
    INDEX idx_application_status (Status),
    INDEX idx_application_date (ApplicationDate)
);

-- Create triggers to automatically update application count
DELIMITER //

-- Trigger to increment application count when new application is submitted
CREATE TRIGGER IF NOT EXISTS update_job_application_count 
AFTER INSERT ON job_applications
FOR EACH ROW
BEGIN
    UPDATE job_postings 
    SET ApplicationCount = ApplicationCount + 1 
    WHERE JobID = NEW.JobID;
END//

-- Trigger to decrement application count when application is deleted
CREATE TRIGGER IF NOT EXISTS decrease_job_application_count 
AFTER DELETE ON job_applications
FOR EACH ROW
BEGIN
    UPDATE job_postings 
    SET ApplicationCount = ApplicationCount - 1 
    WHERE JobID = OLD.JobID;
END//

DELIMITER ;

-- Insert sample job postings for testing (optional)
INSERT IGNORE INTO job_postings (
    CompanyID, JobTitle, Department, JobDescription, Requirements, 
    Responsibilities, Skills, Location, JobType, SalaryMin, SalaryMax, 
    Currency, ExperienceLevel, EducationLevel, Status, ClosingDate
) VALUES 
(
    1, 
    'Senior Software Developer', 
    'Engineering', 
    'We are looking for a passionate Senior Software Developer to join our growing team. You will be responsible for developing high-quality software solutions and leading technical projects.',
    'Bachelor\'s degree in Computer Science or related field, 5+ years of experience in software development, Strong knowledge of modern programming languages',
    'Design and develop software applications, Lead technical projects, Mentor junior developers, Collaborate with cross-functional teams',
    'JavaScript, React, Node.js, Python, SQL, AWS, Docker',
    'San Francisco, CA',
    'full-time',
    120000,
    160000,
    'USD',
    'senior',
    'bachelor',
    'active',
    DATE_ADD(NOW(), INTERVAL 30 DAY)
),
(
    1,
    'Frontend Developer',
    'Engineering',
    'Join our frontend team to build beautiful and responsive user interfaces that our users love.',
    '3+ years of frontend development experience, Strong knowledge of HTML, CSS, JavaScript, Experience with modern frameworks',
    'Develop responsive web applications, Optimize application performance, Collaborate with designers and backend developers',
    'HTML, CSS, JavaScript, React, Vue.js, TypeScript, Webpack',
    'Remote',
    'full-time',
    80000,
    110000,
    'USD',
    'mid',
    'bachelor',
    'active',
    DATE_ADD(NOW(), INTERVAL 45 DAY)
),
(
    1,
    'Marketing Manager',
    'Marketing',
    'We need a creative Marketing Manager to lead our marketing initiatives and drive brand awareness.',
    'Bachelor\'s degree in Marketing or related field, 4+ years of marketing experience, Strong analytical skills',
    'Develop marketing strategies, Manage marketing campaigns, Analyze market trends, Coordinate with sales team',
    'Digital Marketing, SEO, SEM, Social Media, Analytics, Content Marketing',
    'New York, NY',
    'full-time',
    70000,
    95000,
    'USD',
    'mid',
    'bachelor',
    'active',
    DATE_ADD(NOW(), INTERVAL 20 DAY)
);

-- Create a view for job applications with details
CREATE OR REPLACE VIEW job_applications_view AS
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

-- Create a view for company job statistics
CREATE OR REPLACE VIEW company_job_stats AS
SELECT 
    co.CompanyID,
    co.CompanyName,
    COUNT(DISTINCT jp.JobID) as TotalJobs,
    COUNT(DISTINCT CASE WHEN jp.Status = 'active' THEN jp.JobID END) as ActiveJobs,
    COUNT(DISTINCT ja.ApplicationID) as TotalApplications,
    AVG(jp.SalaryMin) as AvgMinSalary,
    AVG(jp.SalaryMax) as AvgMaxSalary
FROM Company_login_info co
LEFT JOIN job_postings jp ON co.CompanyID = jp.CompanyID
LEFT JOIN job_applications ja ON jp.JobID = ja.JobID
GROUP BY co.CompanyID, co.CompanyName;

-- Show table structure
DESCRIBE job_postings;
DESCRIBE job_applications;

-- Show sample data
SELECT 'Job Postings Sample Data:' as Info;
SELECT JobID, JobTitle, CompanyID, JobType, Location, Status, PostedDate FROM job_postings LIMIT 5;

SELECT 'Job Applications Sample Data:' as Info;
SELECT ApplicationID, CandidateID, JobID, Status, ApplicationDate FROM job_applications LIMIT 5;

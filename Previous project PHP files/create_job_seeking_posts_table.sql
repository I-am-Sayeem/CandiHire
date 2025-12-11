-- Create job_seeking_posts table for CandiHire platform
-- This table stores job seeking posts created by candidates

CREATE TABLE IF NOT EXISTS job_seeking_posts (
    PostID INT AUTO_INCREMENT PRIMARY KEY,
    CandidateID INT NOT NULL,
    JobTitle VARCHAR(255) NOT NULL COMMENT 'Position the candidate is seeking',
    CareerGoal TEXT NOT NULL COMMENT 'Career objective and goals',
    KeySkills TEXT NOT NULL COMMENT 'Main skills and competencies',
    Experience TEXT COMMENT 'Relevant experience and projects',
    Education VARCHAR(255) NOT NULL COMMENT 'Educational background',
    SoftSkills TEXT COMMENT 'Personal traits and soft skills',
    ValueToEmployer TEXT COMMENT 'How candidate will contribute to employer',
    ContactInfo TEXT NOT NULL COMMENT 'Contact information for employers',
    Status ENUM('active', 'inactive', 'filled') DEFAULT 'active' COMMENT 'Post status',
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'When post was created',
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last update time',
    Views INT DEFAULT 0 COMMENT 'Number of times post was viewed',
    Applications INT DEFAULT 0 COMMENT 'Number of applications received',
    
    -- Foreign key constraint
    FOREIGN KEY (CandidateID) REFERENCES candidate_login_info(CandidateID) ON DELETE CASCADE,
    
    -- Additional constraints
    CONSTRAINT chk_job_title_length CHECK (CHAR_LENGTH(JobTitle) >= 3),
    CONSTRAINT chk_career_goal_length CHECK (CHAR_LENGTH(CareerGoal) >= 10),
    CONSTRAINT chk_key_skills_length CHECK (CHAR_LENGTH(KeySkills) >= 5),
    CONSTRAINT chk_education_length CHECK (CHAR_LENGTH(Education) >= 5),
    CONSTRAINT chk_contact_info_length CHECK (CHAR_LENGTH(ContactInfo) >= 10),
    CONSTRAINT chk_views_positive CHECK (Views >= 0),
    CONSTRAINT chk_applications_positive CHECK (Applications >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Job seeking posts created by candidates';

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS idx_job_seeking_candidate ON job_seeking_posts(CandidateID);
CREATE INDEX IF NOT EXISTS idx_job_seeking_created ON job_seeking_posts(CreatedAt);
CREATE INDEX IF NOT EXISTS idx_job_seeking_status ON job_seeking_posts(Status);
CREATE INDEX IF NOT EXISTS idx_job_seeking_job_title ON job_seeking_posts(JobTitle);
CREATE INDEX IF NOT EXISTS idx_job_seeking_views ON job_seeking_posts(Views);
CREATE INDEX IF NOT EXISTS idx_job_seeking_applications ON job_seeking_posts(Applications);

-- Create composite index for active posts by candidate
CREATE INDEX IF NOT EXISTS idx_job_seeking_active_candidate ON job_seeking_posts(CandidateID, Status);

-- Create full-text search index for job title and skills
CREATE FULLTEXT INDEX IF NOT EXISTS idx_job_seeking_search ON job_seeking_posts(JobTitle, KeySkills, CareerGoal, Experience, SoftSkills, ValueToEmployer);

-- Insert sample data (optional - for testing)
INSERT INTO job_seeking_posts (
    CandidateID, 
    JobTitle, 
    CareerGoal, 
    KeySkills, 
    Experience, 
    Education, 
    SoftSkills, 
    ValueToEmployer, 
    ContactInfo
) VALUES 
(
    1, 
    'Software Engineer', 
    'Seeking a challenging role in software development where I can apply my technical skills and contribute to innovative projects. Looking to grow in a collaborative environment and work on cutting-edge technologies.',
    'JavaScript, React, Node.js, Python, MySQL, Git, RESTful APIs, Agile Development',
    '2 years of experience in web development. Built e-commerce platforms and mobile-responsive websites. Led a team of 3 developers in a startup project.',
    'B.Sc. in Computer Science and Engineering',
    'Teamwork, Communication, Problem-solving, Adaptability, Leadership, Time Management',
    'I can help build scalable web applications, improve code quality, mentor junior developers, and contribute to technical decision-making. My experience in full-stack development will help accelerate project delivery.',
    'Email: john.doe@email.com, Phone: +1-555-0123, LinkedIn: linkedin.com/in/johndoe'
),
(
    2, 
    'Data Analyst', 
    'Passionate about turning data into actionable insights. Looking for opportunities to work with large datasets and help organizations make data-driven decisions.',
    'Python, SQL, R, Tableau, Power BI, Excel, Statistics, Machine Learning',
    '1 year internship in data analysis. Created dashboards and reports for business stakeholders. Worked with customer data to identify trends and patterns.',
    'B.Sc. in Statistics and Data Science',
    'Analytical Thinking, Attention to Detail, Communication, Critical Thinking, Presentation Skills',
    'I can help analyze business data, create insightful reports, identify trends and patterns, and provide recommendations for business improvement based on data insights.',
    'Email: jane.smith@email.com, Phone: +1-555-0456, LinkedIn: linkedin.com/in/janesmith'
);

-- Create a view for active job seeking posts with candidate information
CREATE OR REPLACE VIEW v_active_job_seeking_posts AS
SELECT 
    j.PostID,
    j.CandidateID,
    j.JobTitle,
    j.CareerGoal,
    j.KeySkills,
    j.Experience,
    j.Education,
    j.SoftSkills,
    j.ValueToEmployer,
    j.ContactInfo,
    j.CreatedAt,
    j.Views,
    j.Applications,
    c.FullName,
    c.Email,
    c.Phone,
    c.WorkType,
    c.Skills as ProfileSkills
FROM job_seeking_posts j
JOIN candidate_login_info c ON j.CandidateID = c.CandidateID
WHERE j.Status = 'active'
ORDER BY j.CreatedAt DESC;

-- Create a view for job seeking statistics
CREATE OR REPLACE VIEW v_job_seeking_stats AS
SELECT 
    COUNT(*) as total_posts,
    COUNT(CASE WHEN Status = 'active' THEN 1 END) as active_posts,
    COUNT(CASE WHEN Status = 'inactive' THEN 1 END) as inactive_posts,
    COUNT(CASE WHEN Status = 'filled' THEN 1 END) as filled_posts,
    AVG(Views) as avg_views,
    AVG(Applications) as avg_applications,
    MAX(CreatedAt) as latest_post_date
FROM job_seeking_posts;

-- Create a trigger to update the UpdatedAt timestamp
DELIMITER //
CREATE TRIGGER tr_job_seeking_posts_update
    BEFORE UPDATE ON job_seeking_posts
    FOR EACH ROW
BEGIN
    SET NEW.UpdatedAt = CURRENT_TIMESTAMP;
END//
DELIMITER ;

-- Create a trigger to log post views (optional)
DELIMITER //
CREATE TRIGGER tr_job_seeking_posts_view_increment
    AFTER UPDATE ON job_seeking_posts
    FOR EACH ROW
BEGIN
    -- This trigger can be used to increment view count when a post is viewed
    -- Implementation depends on your view tracking logic
END//
DELIMITER ;

-- Grant permissions (adjust as needed for your setup)
-- GRANT SELECT, INSERT, UPDATE, DELETE ON job_seeking_posts TO 'your_app_user'@'localhost';
-- GRANT SELECT ON v_active_job_seeking_posts TO 'your_app_user'@'localhost';
-- GRANT SELECT ON v_job_seeking_stats TO 'your_app_user'@'localhost';

-- Show table structure
DESCRIBE job_seeking_posts;

-- Show indexes
SHOW INDEX FROM job_seeking_posts;

-- Show views
SHOW FULL TABLES WHERE Table_type = 'VIEW';

-- SQL Schema Updates for exam_assignments table
-- These changes were applied to support exam result tracking

-- Add columns for storing exam results
ALTER TABLE exam_assignments 
ADD COLUMN IF NOT EXISTS Score DECIMAL(5,2) NULL,
ADD COLUMN IF NOT EXISTS CorrectAnswers INT NULL,
ADD COLUMN IF NOT EXISTS TotalQuestions INT NULL,
ADD COLUMN IF NOT EXISTS TimeSpent INT NULL,
ADD COLUMN IF NOT EXISTS CompletedAt TIMESTAMP NULL;

-- Update the Status enum to include more options
ALTER TABLE exam_assignments 
MODIFY COLUMN Status ENUM('assigned', 'in-progress', 'completed', 'expired', 'failed') DEFAULT 'assigned';

-- Add indexes for better performance on result queries
CREATE INDEX IF NOT EXISTS idx_exam_assignments_status ON exam_assignments(Status);
CREATE INDEX IF NOT EXISTS idx_exam_assignments_candidate ON exam_assignments(CandidateID);
CREATE INDEX IF NOT EXISTS idx_exam_assignments_job ON exam_assignments(JobID);
CREATE INDEX IF NOT EXISTS idx_exam_assignments_completed ON exam_assignments(CompletedAt);

-- Final structure of exam_assignments table after updates:
/*
CREATE TABLE exam_assignments (
    AssignmentID INT AUTO_INCREMENT PRIMARY KEY,
    ExamID INT NOT NULL,
    CandidateID INT NOT NULL,
    JobID INT NOT NULL,
    AssignmentDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    Status ENUM('assigned', 'in-progress', 'completed', 'expired', 'failed') DEFAULT 'assigned',
    DueDate DATETIME,
    AttemptsUsed INT DEFAULT 0,
    MaxAttempts INT DEFAULT 1,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- NEW COLUMNS ADDED:
    Score DECIMAL(5,2) NULL,                    -- Exam score percentage
    CorrectAnswers INT NULL,                    -- Number of correct answers
    TotalQuestions INT NULL,                    -- Total questions in exam
    TimeSpent INT NULL,                         -- Time spent in seconds
    CompletedAt TIMESTAMP NULL,                 -- When exam was completed
    
    FOREIGN KEY (ExamID) REFERENCES exams(ExamID) ON DELETE CASCADE,
    FOREIGN KEY (CandidateID) REFERENCES candidate_login_info(CandidateID) ON DELETE CASCADE,
    FOREIGN KEY (JobID) REFERENCES job_postings(JobID) ON DELETE CASCADE,
    UNIQUE KEY unique_assignment (ExamID, CandidateID, JobID)
);
*/

-- =============================================
-- JOB APPLICATIONS AND EXAM ASSIGNMENT TABLES
-- =============================================

-- Job Applications Table
CREATE TABLE IF NOT EXISTS job_applications (
    ApplicationID INT AUTO_INCREMENT PRIMARY KEY,
    JobID INT NOT NULL,
    CandidateID INT NOT NULL,
    ApplicationDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ApplicationStatus ENUM('pending', 'under-review', 'shortlisted', 'rejected', 'hired') DEFAULT 'pending',
    CoverLetter TEXT,
    ResumePath VARCHAR(500),
    AdditionalNotes TEXT,
    AppliedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (JobID) REFERENCES job_postings(JobID) ON DELETE CASCADE,
    FOREIGN KEY (CandidateID) REFERENCES candidate_login_info(CandidateID) ON DELETE CASCADE,
    UNIQUE KEY unique_application (JobID, CandidateID)
);

-- Exam Assignments Table (for tracking which exams are assigned to candidates)
CREATE TABLE IF NOT EXISTS exam_assignments (
    AssignmentID INT AUTO_INCREMENT PRIMARY KEY,
    ExamID INT NOT NULL,
    CandidateID INT NOT NULL,
    JobID INT NOT NULL,
    ApplicationID INT NOT NULL,
    AssignmentDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    DueDate DATE,
    AssignmentStatus ENUM('assigned', 'in-progress', 'completed', 'expired', 'cancelled') DEFAULT 'assigned',
    Score DECIMAL(5,2) NULL,
    CompletedAt TIMESTAMP NULL,
    FOREIGN KEY (ExamID) REFERENCES exams(ExamID) ON DELETE CASCADE,
    FOREIGN KEY (CandidateID) REFERENCES candidate_login_info(CandidateID) ON DELETE CASCADE,
    FOREIGN KEY (JobID) REFERENCES job_postings(JobID) ON DELETE CASCADE,
    FOREIGN KEY (ApplicationID) REFERENCES job_applications(ApplicationID) ON DELETE CASCADE,
    UNIQUE KEY unique_exam_assignment (ExamID, CandidateID, JobID)
);

-- Indexes for better performance (created after tables exist)
-- These will be created in a separate step

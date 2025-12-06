-- CV Processing Results Table
-- This table stores the results of CV processing and filtering
-- NOTE: This system processes EXTERNAL CVs from candidates who are NOT part of the CandiHire platform

CREATE TABLE IF NOT EXISTS cv_processing_results (
    ProcessingID INT AUTO_INCREMENT PRIMARY KEY,
    CompanyID INT NOT NULL,
    JobPosition VARCHAR(255) NOT NULL,
    ExperienceLevel ENUM('any', 'entry', 'mid', 'senior', 'lead') NOT NULL,
    RequiredSkills TEXT,
    CustomCriteria TEXT,
    -- MinMatchPercentage removed - no longer used
    ProcessedDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    Status ENUM('processing', 'completed', 'failed') DEFAULT 'processing',
    FOREIGN KEY (CompanyID) REFERENCES Company_login_info(CompanyID) ON DELETE CASCADE
);

-- CV Files Table
-- This table stores information about uploaded CV files
CREATE TABLE IF NOT EXISTS cv_files (
    FileID INT AUTO_INCREMENT PRIMARY KEY,
    ProcessingID INT NOT NULL,
    OriginalFileName VARCHAR(255) NOT NULL,
    StoredFileName VARCHAR(255) NOT NULL,
    FilePath VARCHAR(500) NOT NULL,
    FileSize INT NOT NULL,
    UploadDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    Status ENUM('uploaded', 'processed', 'failed') DEFAULT 'uploaded',
    FOREIGN KEY (ProcessingID) REFERENCES cv_processing_results(ProcessingID) ON DELETE CASCADE
);

-- Candidate Contact Information Table
-- This table stores extracted contact information from EXTERNAL CVs
-- These candidates are NOT registered users of the CandiHire platform
CREATE TABLE IF NOT EXISTS candidate_contact_info (
    ContactID INT AUTO_INCREMENT PRIMARY KEY,
    ProcessingID INT NOT NULL,
    FileID INT NOT NULL,
    CandidateName VARCHAR(255) NOT NULL,
    Email VARCHAR(255),
    Phone VARCHAR(50),
    LinkedIn VARCHAR(500),
    Location VARCHAR(255),
    University VARCHAR(255),
    Education VARCHAR(255),
    ExperienceYears INT,
    Experience TEXT,
    Skills TEXT,
    Summary TEXT,
    CustomCriteria TEXT,
    MatchPercentage INT DEFAULT 0,
    IsSelected BOOLEAN DEFAULT FALSE,
    CreatedDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ProcessingID) REFERENCES cv_processing_results(ProcessingID) ON DELETE CASCADE,
    FOREIGN KEY (FileID) REFERENCES cv_files(FileID) ON DELETE CASCADE
);

-- Selected Candidates Export Table
-- This table stores information about exported candidate lists
CREATE TABLE IF NOT EXISTS selected_candidates_export (
    ExportID INT AUTO_INCREMENT PRIMARY KEY,
    ProcessingID INT NOT NULL,
    JobPosition VARCHAR(255) NOT NULL,
    ExportFileName VARCHAR(255) NOT NULL,
    ExportFilePath VARCHAR(500) NOT NULL,
    CandidateCount INT NOT NULL,
    ExportDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ProcessingID) REFERENCES cv_processing_results(ProcessingID) ON DELETE CASCADE
);

-- Indexes for better performance
CREATE INDEX idx_processing_company ON cv_processing_results(CompanyID);
CREATE INDEX idx_processing_date ON cv_processing_results(ProcessedDate);
CREATE INDEX idx_files_processing ON cv_files(ProcessingID);
CREATE INDEX idx_contact_processing ON candidate_contact_info(ProcessingID);
CREATE INDEX idx_contact_selected ON candidate_contact_info(IsSelected);
CREATE INDEX idx_export_processing ON selected_candidates_export(ProcessingID);

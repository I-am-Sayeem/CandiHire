-- =============================================
-- EXAM RECORDINGS TABLE
-- =============================================
-- This table stores screen recordings and webcam recordings for exam attempts

CREATE TABLE exam_recordings (
    RecordingID INT AUTO_INCREMENT PRIMARY KEY,
    AttemptID INT NOT NULL,
    CandidateID INT NOT NULL,
    ExamID INT NOT NULL,
    RecordingType ENUM('screen', 'webcam', 'combined') NOT NULL,
    RecordingData LONGBLOB NOT NULL,
    FileName VARCHAR(255) NOT NULL,
    FileSize INT NOT NULL,
    MimeType VARCHAR(100) NOT NULL,
    Duration INT NOT NULL, -- Duration in seconds
    StartTime TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    EndTime TIMESTAMP NULL,
    Status ENUM('recording', 'completed', 'failed', 'processing') DEFAULT 'recording',
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (AttemptID) REFERENCES exam_attempts(AttemptID) ON DELETE CASCADE,
    FOREIGN KEY (CandidateID) REFERENCES candidate_login_info(CandidateID) ON DELETE CASCADE,
    FOREIGN KEY (ExamID) REFERENCES exams(ExamID) ON DELETE CASCADE,
    INDEX idx_attempt_id (AttemptID),
    INDEX idx_candidate_id (CandidateID),
    INDEX idx_exam_id (ExamID),
    INDEX idx_recording_type (RecordingType),
    INDEX idx_status (Status)
);

-- Add indexes for better performance
CREATE INDEX idx_exam_recordings_attempt_type ON exam_recordings(AttemptID, RecordingType);
CREATE INDEX idx_exam_recordings_candidate_exam ON exam_recordings(CandidateID, ExamID);

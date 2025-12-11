-- Create complaints table for admin panel
CREATE TABLE IF NOT EXISTS complaints (
    ComplaintID INT AUTO_INCREMENT PRIMARY KEY,
    UserID INT NOT NULL,
    UserType ENUM('candidate', 'company') NOT NULL,
    Subject VARCHAR(255) NOT NULL,
    Description TEXT NOT NULL,
    ComplaintDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    Status ENUM('pending', 'resolved', 'in-progress', 'closed') DEFAULT 'pending',
    Priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    ResolvedBy INT NULL,
    ResolutionDetails TEXT NULL,
    ResolutionDate TIMESTAMP NULL,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user (UserID, UserType),
    INDEX idx_status (Status),
    INDEX idx_priority (Priority),
    INDEX idx_created (CreatedAt)
);

-- Insert some sample complaints for testing
INSERT INTO complaints (UserID, UserType, Subject, Description, Priority, Status) VALUES
(1, 'candidate', 'Unable to apply for jobs', 'I am getting an error when trying to apply for jobs. The application form is not submitting properly.', 'high', 'pending'),
(2, 'company', 'Exam creation issues', 'The exam creation feature is not working correctly. I cannot add questions to my exam.', 'medium', 'pending'),
(1, 'candidate', 'Profile picture upload problem', 'I cannot upload my profile picture. The upload button is not responding.', 'low', 'resolved'),
(3, 'company', 'Dashboard loading slowly', 'The company dashboard is taking too long to load. This is affecting our workflow.', 'medium', 'pending'),
(2, 'company', 'Email notifications not working', 'We are not receiving email notifications for new job applications.', 'high', 'pending');

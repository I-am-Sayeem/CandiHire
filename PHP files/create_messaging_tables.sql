-- Messaging System Database Tables
-- This file creates the necessary tables for the messaging system

-- Messages table to store all messages
CREATE TABLE IF NOT EXISTS messages (
    MessageID INT AUTO_INCREMENT PRIMARY KEY,
    SenderID INT NOT NULL,
    SenderType ENUM('candidate', 'company') NOT NULL,
    ReceiverID INT NOT NULL,
    ReceiverType ENUM('candidate', 'company') NOT NULL,
    Subject VARCHAR(255) DEFAULT NULL,
    Message TEXT NOT NULL,
    IsRead BOOLEAN DEFAULT FALSE,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_sender (SenderID, SenderType),
    INDEX idx_receiver (ReceiverID, ReceiverType),
    INDEX idx_created_at (CreatedAt)
);

-- Conversations table to track conversation threads
CREATE TABLE IF NOT EXISTS conversations (
    ConversationID INT AUTO_INCREMENT PRIMARY KEY,
    Participant1ID INT NOT NULL,
    Participant1Type ENUM('candidate', 'company') NOT NULL,
    Participant2ID INT NOT NULL,
    Participant2Type ENUM('candidate', 'company') NOT NULL,
    LastMessageID INT DEFAULT NULL,
    LastMessageAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_conversation (Participant1ID, Participant1Type, Participant2ID, Participant2Type),
    INDEX idx_participant1 (Participant1ID, Participant1Type),
    INDEX idx_participant2 (Participant2ID, Participant2Type),
    INDEX idx_last_message (LastMessageAt),
    FOREIGN KEY (LastMessageID) REFERENCES messages(MessageID) ON DELETE SET NULL
);

-- Message attachments table (optional - for future file sharing)
CREATE TABLE IF NOT EXISTS message_attachments (
    AttachmentID INT AUTO_INCREMENT PRIMARY KEY,
    MessageID INT NOT NULL,
    FileName VARCHAR(255) NOT NULL,
    FilePath VARCHAR(500) NOT NULL,
    FileSize INT NOT NULL,
    MimeType VARCHAR(100) NOT NULL,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (MessageID) REFERENCES messages(MessageID) ON DELETE CASCADE
);

-- Message read status table for tracking read receipts
CREATE TABLE IF NOT EXISTS message_read_status (
    ReadStatusID INT AUTO_INCREMENT PRIMARY KEY,
    MessageID INT NOT NULL,
    UserID INT NOT NULL,
    UserType ENUM('candidate', 'company') NOT NULL,
    ReadAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_read_status (MessageID, UserID, UserType),
    FOREIGN KEY (MessageID) REFERENCES messages(MessageID) ON DELETE CASCADE
);

-- Insert sample data for testing (optional)
-- This can be removed in production
INSERT INTO messages (SenderID, SenderType, ReceiverID, ReceiverType, Subject, Message) VALUES
(1, 'candidate', 1, 'company', 'Job Application Inquiry', 'Hello, I am interested in the Software Developer position. Could you please provide more details about the role?'),
(1, 'company', 1, 'candidate', 'Re: Job Application Inquiry', 'Thank you for your interest! The position involves full-stack development with React and Node.js. Would you like to schedule an interview?'),
(1, 'candidate', 1, 'company', 'Re: Job Application Inquiry', 'Yes, I would love to schedule an interview. What times work best for you?'),
(1, 'company', 1, 'candidate', 'Re: Job Application Inquiry', 'How about next Tuesday at 2 PM? Please let me know if that works for you.');

-- Create conversations for the sample messages
INSERT INTO conversations (Participant1ID, Participant1Type, Participant2ID, Participant2Type, LastMessageID, LastMessageAt) VALUES
(1, 'candidate', 1, 'company', 4, NOW());

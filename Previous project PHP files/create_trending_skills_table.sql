-- Create trending_skills table for storing dynamic trending skills data
-- This table will be updated every 24 hours with fresh data from APIs

CREATE TABLE IF NOT EXISTS trending_skills (
    id INT AUTO_INCREMENT PRIMARY KEY,
    skill_name VARCHAR(255) NOT NULL,
    popularity INT NOT NULL CHECK (popularity >= 0 AND popularity <= 100),
    trend ENUM('up', 'down', 'stable') DEFAULT 'stable',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes for better performance
    INDEX idx_skill_name (skill_name),
    INDEX idx_popularity (popularity DESC),
    INDEX idx_updated_at (updated_at),
    
    -- Ensure unique skill names
    UNIQUE KEY unique_skill (skill_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
COMMENT='Stores trending skills data updated every 24 hours from various APIs';

-- Insert initial fallback data
INSERT INTO trending_skills (skill_name, popularity, trend) VALUES 
('React', 95, 'up'),
('JavaScript', 90, 'up'),
('Python', 85, 'up'),
('Node.js', 80, 'up'),
('AWS', 75, 'up'),
('Docker', 70, 'up'),
('Kubernetes', 65, 'up'),
('Machine Learning', 60, 'up'),
('TypeScript', 55, 'up'),
('GraphQL', 50, 'up')
ON DUPLICATE KEY UPDATE 
    popularity = VALUES(popularity),
    trend = VALUES(trend),
    updated_at = CURRENT_TIMESTAMP;

-- Show table structure
DESCRIBE trending_skills;

-- Show initial data
SELECT * FROM trending_skills ORDER BY popularity DESC;

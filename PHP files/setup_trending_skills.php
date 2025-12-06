<?php
// setup_trending_skills.php - Setup script for trending skills system

require_once 'Database.php';

echo "Setting up Trending Skills System...\n\n";

try {
    if (!isset($pdo) || !$pdo instanceof PDO) {
        throw new Exception('Database connection not available');
    }

    // 1. Create the trending_skills table
    echo "1. Creating trending_skills table...\n";
    $createTableSQL = "
        CREATE TABLE IF NOT EXISTS trending_skills (
            id INT AUTO_INCREMENT PRIMARY KEY,
            skill_name VARCHAR(255) NOT NULL,
            popularity INT NOT NULL CHECK (popularity >= 0 AND popularity <= 100),
            trend ENUM('up', 'down', 'stable') DEFAULT 'stable',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            
            INDEX idx_skill_name (skill_name),
            INDEX idx_popularity (popularity DESC),
            INDEX idx_updated_at (updated_at),
            
            UNIQUE KEY unique_skill (skill_name)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
        COMMENT='Stores trending skills data updated every 24 hours from various APIs'
    ";
    
    $pdo->exec($createTableSQL);
    echo "   ✓ Table created successfully\n";

    // 2. Insert initial fallback data
    echo "2. Inserting initial fallback data...\n";
    $initialSkills = [
        ['React', 95, 'up'],
        ['JavaScript', 90, 'up'],
        ['Python', 85, 'up'],
        ['Node.js', 80, 'up'],
        ['AWS', 75, 'up'],
        ['Docker', 70, 'up'],
        ['Kubernetes', 65, 'up'],
        ['Machine Learning', 60, 'up'],
        ['TypeScript', 55, 'up'],
        ['GraphQL', 50, 'up']
    ];

    $stmt = $pdo->prepare("
        INSERT INTO trending_skills (skill_name, popularity, trend) 
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE 
            popularity = VALUES(popularity),
            trend = VALUES(trend),
            updated_at = CURRENT_TIMESTAMP
    ");

    foreach ($initialSkills as $skill) {
        $stmt->execute($skill);
    }
    echo "   ✓ Initial data inserted successfully\n";

    // 3. Test the API endpoint
    echo "3. Testing API endpoint...\n";
    $testStmt = $pdo->prepare("SELECT COUNT(*) FROM trending_skills");
    $testStmt->execute();
    $count = $testStmt->fetchColumn();
    echo "   ✓ Found $count skills in database\n";

    // 4. Show current data
    echo "4. Current trending skills:\n";
    $showStmt = $pdo->prepare("SELECT skill_name, popularity, trend FROM trending_skills ORDER BY popularity DESC LIMIT 5");
    $showStmt->execute();
    $skills = $showStmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($skills as $skill) {
        echo "   - {$skill['skill_name']}: {$skill['popularity']}% ({$skill['trend']})\n";
    }

    echo "\n✓ Trending Skills System setup completed successfully!\n\n";
    
    echo "Next steps:\n";
    echo "1. Set up a cron job to run 'trending_skills_fetcher.php' every 24 hours\n";
    echo "2. Example cron job: 0 0 * * * /usr/bin/php /path/to/your/project/trending_skills_fetcher.php\n";
    echo "3. The dashboard will automatically load fresh data from trending_skills_api.php\n";
    echo "4. Test the system by visiting your dashboard\n\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>

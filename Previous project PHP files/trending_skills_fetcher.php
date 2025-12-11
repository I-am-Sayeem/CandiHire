<?php
// trending_skills_fetcher.php - Fetch trending skills data from various APIs
// This script can be run via cron job every 24 hours

require_once 'Database.php';

// Configuration
$config = [
    'cache_duration' => 24 * 60 * 60, // 24 hours in seconds
    'max_skills' => 10,
    'fallback_skills' => [
        ['name' => 'React', 'popularity' => 95, 'trend' => 'up'],
        ['name' => 'JavaScript', 'popularity' => 90, 'trend' => 'up'],
        ['name' => 'Python', 'popularity' => 85, 'trend' => 'up'],
        ['name' => 'Node.js', 'popularity' => 80, 'trend' => 'up'],
        ['name' => 'AWS', 'popularity' => 75, 'trend' => 'up'],
        ['name' => 'Docker', 'popularity' => 70, 'trend' => 'up'],
        ['name' => 'Kubernetes', 'popularity' => 65, 'trend' => 'up'],
        ['name' => 'Machine Learning', 'popularity' => 60, 'trend' => 'up'],
        ['name' => 'TypeScript', 'popularity' => 55, 'trend' => 'up'],
        ['name' => 'GraphQL', 'popularity' => 50, 'trend' => 'up']
    ]
];

try {
    if (!isset($pdo) || !$pdo instanceof PDO) {
        throw new Exception('Database connection not available');
    }

    // Check if we have recent data
    $checkStmt = $pdo->prepare("SELECT * FROM trending_skills ORDER BY updated_at DESC LIMIT 1");
    $checkStmt->execute();
    $lastUpdate = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if ($lastUpdate && (time() - strtotime($lastUpdate['updated_at'])) < $config['cache_duration']) {
        echo "Skills data is still fresh. Last updated: " . $lastUpdate['updated_at'] . "\n";
        exit(0);
    }

    echo "Fetching new trending skills data...\n";

    // Fetch trending skills from multiple sources
    $trendingSkills = fetchTrendingSkills($config);

    // Store in database
    storeTrendingSkills($trendingSkills, $pdo);

    echo "Successfully updated trending skills data!\n";

} catch (Exception $e) {
    error_log("Error in trending_skills_fetcher.php: " . $e->getMessage());
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

/**
 * Fetch trending skills from various sources
 */
function fetchTrendingSkills($config) {
    $skills = [];
    
    // Source 1: GitHub Trending (for programming languages and frameworks)
    $skills = array_merge($skills, fetchGitHubTrending());
    
    // Source 2: Stack Overflow Tags (for popular technologies)
    $skills = array_merge($skills, fetchStackOverflowTrending());
    
    // Source 3: Job board APIs (simulated data for demo)
    $skills = array_merge($skills, fetchJobBoardTrending());
    
    // If we couldn't fetch any data, use fallback
    if (empty($skills)) {
        echo "Using fallback skills data\n";
        return $config['fallback_skills'];
    }
    
    // Normalize and rank skills
    return normalizeAndRankSkills($skills, $config['max_skills']);
}

/**
 * Fetch trending data from GitHub (simulated - would use GitHub API in production)
 */
function fetchGitHubTrending() {
    // In a real implementation, you would use the GitHub API
    // For demo purposes, we'll simulate this with current trending technologies
    return [
        ['name' => 'React', 'popularity' => 95, 'source' => 'github', 'trend' => 'up'],
        ['name' => 'Vue.js', 'popularity' => 85, 'source' => 'github', 'trend' => 'up'],
        ['name' => 'Next.js', 'popularity' => 80, 'source' => 'github', 'trend' => 'up'],
        ['name' => 'TypeScript', 'popularity' => 75, 'source' => 'github', 'trend' => 'up'],
        ['name' => 'Python', 'popularity' => 90, 'source' => 'github', 'trend' => 'up'],
        ['name' => 'Rust', 'popularity' => 70, 'source' => 'github', 'trend' => 'up'],
        ['name' => 'Go', 'popularity' => 65, 'source' => 'github', 'trend' => 'up'],
        ['name' => 'TensorFlow', 'popularity' => 60, 'source' => 'github', 'trend' => 'up']
    ];
}

/**
 * Fetch trending data from Stack Overflow (simulated)
 */
function fetchStackOverflowTrending() {
    // In a real implementation, you would use Stack Overflow API
    return [
        ['name' => 'JavaScript', 'popularity' => 92, 'source' => 'stackoverflow', 'trend' => 'up'],
        ['name' => 'Python', 'popularity' => 88, 'source' => 'stackoverflow', 'trend' => 'up'],
        ['name' => 'Java', 'popularity' => 82, 'source' => 'stackoverflow', 'trend' => 'stable'],
        ['name' => 'C#', 'popularity' => 78, 'source' => 'stackoverflow', 'trend' => 'stable'],
        ['name' => 'Docker', 'popularity' => 75, 'source' => 'stackoverflow', 'trend' => 'up'],
        ['name' => 'Kubernetes', 'popularity' => 68, 'source' => 'stackoverflow', 'trend' => 'up'],
        ['name' => 'AWS', 'popularity' => 85, 'source' => 'stackoverflow', 'trend' => 'up'],
        ['name' => 'Machine Learning', 'popularity' => 72, 'source' => 'stackoverflow', 'trend' => 'up']
    ];
}

/**
 * Fetch trending data from job boards (simulated)
 */
function fetchJobBoardTrending() {
    // In a real implementation, you would use job board APIs like Indeed, LinkedIn, etc.
    return [
        ['name' => 'React', 'popularity' => 90, 'source' => 'jobboard', 'trend' => 'up'],
        ['name' => 'Node.js', 'popularity' => 80, 'source' => 'jobboard', 'trend' => 'up'],
        ['name' => 'AWS', 'popularity' => 85, 'source' => 'jobboard', 'trend' => 'up'],
        ['name' => 'Docker', 'popularity' => 75, 'source' => 'jobboard', 'trend' => 'up'],
        ['name' => 'Kubernetes', 'popularity' => 70, 'source' => 'jobboard', 'trend' => 'up'],
        ['name' => 'GraphQL', 'popularity' => 60, 'source' => 'jobboard', 'trend' => 'up'],
        ['name' => 'Machine Learning', 'popularity' => 65, 'source' => 'jobboard', 'trend' => 'up'],
        ['name' => 'DevOps', 'popularity' => 78, 'source' => 'jobboard', 'trend' => 'up']
    ];
}

/**
 * Normalize and rank skills from multiple sources
 */
function normalizeAndRankSkills($skills, $maxSkills) {
    $skillMap = [];
    
    // Combine data from multiple sources
    foreach ($skills as $skill) {
        $name = $skill['name'];
        $popularity = $skill['popularity'];
        
        if (!isset($skillMap[$name])) {
            $skillMap[$name] = [
                'name' => $name,
                'popularity' => 0,
                'sources' => 0,
                'trend' => $skill['trend'] ?? 'stable'
            ];
        }
        
        // Average popularity across sources
        $skillMap[$name]['popularity'] = ($skillMap[$name]['popularity'] * $skillMap[$name]['sources'] + $popularity) / ($skillMap[$name]['sources'] + 1);
        $skillMap[$name]['sources']++;
    }
    
    // Sort by popularity and limit results
    uasort($skillMap, function($a, $b) {
        return $b['popularity'] - $a['popularity'];
    });
    
    return array_slice($skillMap, 0, $maxSkills);
}

/**
 * Store trending skills in database
 */
function storeTrendingSkills($skills, $pdo) {
    try {
        // Clear existing data
        $pdo->exec("DELETE FROM trending_skills");
        
        // Insert new data
        $stmt = $pdo->prepare("
            INSERT INTO trending_skills (skill_name, popularity, trend, created_at, updated_at) 
            VALUES (?, ?, ?, NOW(), NOW())
        ");
        
        foreach ($skills as $skill) {
            $stmt->execute([
                $skill['name'],
                round($skill['popularity']),
                $skill['trend']
            ]);
        }
        
        echo "Stored " . count($skills) . " trending skills in database\n";
        
    } catch (Exception $e) {
        throw new Exception("Failed to store trending skills: " . $e->getMessage());
    }
}
?>

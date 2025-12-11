<?php
// trending_skills_api.php - API endpoint to serve trending skills data

require_once 'Database.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

try {
    if (!isset($pdo) || !$pdo instanceof PDO) {
        throw new Exception('Database connection not available');
    }

    // Get trending skills from database
    $stmt = $pdo->prepare("
        SELECT skill_name, popularity, trend, updated_at 
        FROM trending_skills 
        ORDER BY popularity DESC 
        LIMIT 10
    ");
    
    $stmt->execute();
    $skills = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format data for frontend
    $formattedSkills = array_map(function($skill) {
        return [
            'name' => $skill['skill_name'],
            'popularity' => (int)$skill['popularity'],
            'trend' => $skill['trend'],
            'lastUpdated' => $skill['updated_at']
        ];
    }, $skills);

    // Get last update time
    $lastUpdateStmt = $pdo->prepare("SELECT MAX(updated_at) as last_update FROM trending_skills");
    $lastUpdateStmt->execute();
    $lastUpdate = $lastUpdateStmt->fetchColumn();

    echo json_encode([
        'success' => true,
        'skills' => $formattedSkills,
        'lastUpdate' => $lastUpdate,
        'totalSkills' => count($formattedSkills)
    ]);

} catch (Exception $e) {
    error_log("Error in trending_skills_api.php: " . $e->getMessage());
    
    // Return fallback data if database fails
    $fallbackSkills = [
        ['name' => 'React', 'popularity' => 95, 'trend' => 'up'],
        ['name' => 'JavaScript', 'popularity' => 90, 'trend' => 'up'],
        ['name' => 'Python', 'popularity' => 85, 'trend' => 'up'],
        ['name' => 'Node.js', 'popularity' => 80, 'trend' => 'up'],
        ['name' => 'AWS', 'popularity' => 75, 'trend' => 'up']
    ];
    
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'skills' => $fallbackSkills,
        'lastUpdate' => null,
        'totalSkills' => count($fallbackSkills),
        'fallback' => true
    ]);
}
?>

<?php
// database_setup.php - Database initialization and verification script

// Load database configuration
require_once 'database_config.php';

// Function to create a direct database connection
function createDatabaseConnection() {
    try {
        $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, DB_OPTIONS);
        return $pdo;
    } catch (PDOException $e) {
        return [
            'error' => true,
            'message' => $e->getMessage()
        ];
    }
}

// Function to check if database exists and is accessible
function checkDatabaseConnection() {
    $connection = createDatabaseConnection();
    
    if (isset($connection['error'])) {
        return [
            'success' => false,
            'message' => 'Database connection failed: ' . $connection['message']
        ];
    }
    
    try {
        // Test basic connection
        $stmt = $connection->query("SELECT 1 as test");
        $result = $stmt->fetch();
        if ($result && $result['test'] == 1) {
            return [
                'success' => true,
                'message' => 'Database connection successful'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Database query test failed'
            ];
        }
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'Database query failed: ' . $e->getMessage()
        ];
    }
}

// Function to verify required tables exist
function verifyTables() {
    $connection = createDatabaseConnection();
    
    if (isset($connection['error'])) {
        return [
            'success' => false,
            'message' => 'Database connection failed: ' . $connection['message']
        ];
    }
    
    $requiredTables = [
        'candidate_login_info',
        'Company_login_info',
        'candidate_cv_data',
        'candidate_experience',
        'candidate_education',
        'candidate_skills',
        'candidate_projects',
        'job_postings',
        'job_applications',
        'application_status_history',
        'exams',
        'exam_questions',
        'exam_question_options',
        'exam_schedules',
        'exam_attempts',
        'exam_answers',
        'interviews',
        'ai_matching_results',
        'cv_checker_results',
        'user_sessions',
        'notifications',
        'system_settings'
    ];
    
    $missingTables = [];
    $existingTables = [];
    
    try {
        foreach ($requiredTables as $table) {
            $stmt = $connection->prepare("SHOW TABLES LIKE ?");
            $stmt->execute([$table]);
            if ($stmt->fetch()) {
                $existingTables[] = $table;
            } else {
                $missingTables[] = $table;
            }
        }
        
        if (empty($missingTables)) {
            return [
                'success' => true,
                'message' => 'All required tables exist',
                'tables_found' => count($requiredTables),
                'existing_tables' => $existingTables
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Missing tables: ' . implode(', ', $missingTables),
                'missing_tables' => $missingTables,
                'existing_tables' => $existingTables,
                'total_required' => count($requiredTables),
                'total_existing' => count($existingTables)
            ];
        }
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'Error checking tables: ' . $e->getMessage()
        ];
    }
}

// Function to check table structure
function checkTableStructure($tableName) {
    $connection = createDatabaseConnection();
    
    if (isset($connection['error'])) {
        return [
            'success' => false,
            'table' => $tableName,
            'message' => 'Database connection failed: ' . $connection['message']
        ];
    }
    
    try {
        $stmt = $connection->prepare("DESCRIBE `$tableName`");
        $stmt->execute();
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'success' => true,
            'table' => $tableName,
            'columns' => $columns,
            'column_count' => count($columns)
        ];
    } catch (PDOException $e) {
        return [
            'success' => false,
            'table' => $tableName,
            'message' => 'Error describing table: ' . $e->getMessage()
        ];
    }
}

// Function to get MySQL server information
function getMySQLInfo() {
    $connection = createDatabaseConnection();
    
    if (isset($connection['error'])) {
        return [
            'success' => false,
            'message' => 'Database connection failed: ' . $connection['message']
        ];
    }
    
    try {
        $info = [];
        
        // Get MySQL version
        $stmt = $connection->query("SELECT VERSION() as version");
        $version = $stmt->fetch();
        $info['mysql_version'] = $version['version'];
        
        // Get database name
        $stmt = $connection->query("SELECT DATABASE() as db_name");
        $db = $stmt->fetch();
        $info['database_name'] = $db['db_name'];
        
        // Get character set
        $stmt = $connection->query("SELECT @@character_set_database as charset");
        $charset = $stmt->fetch();
        $info['character_set'] = $charset['charset'];
        
        // Get collation
        $stmt = $connection->query("SELECT @@collation_database as collation");
        $collation = $stmt->fetch();
        $info['collation'] = $collation['collation'];
        
        return [
            'success' => true,
            'mysql_info' => $info
        ];
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'Error getting MySQL info: ' . $e->getMessage()
        ];
    }
}

// Function to test database without schema
function testMySQLConnection() {
    try {
        $pdo = new PDO(DB_DSN_NO_DB, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        
        $stmt = $pdo->query("SELECT VERSION() as version");
        $version = $stmt->fetch();
        
        return [
            'success' => true,
            'message' => 'MySQL server connection successful',
            'version' => $version['version'],
            'config' => [
                'host' => DB_HOST,
                'user' => DB_USER,
                'database' => DB_NAME
            ]
        ];
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'MySQL server connection failed: ' . $e->getMessage(),
            'config' => [
                'host' => DB_HOST,
                'user' => DB_USER,
                'database' => DB_NAME
            ]
        ];
    }
}

// Main execution
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Content-Type: application/json');
    
    $action = $_GET['action'] ?? 'check_connection';
    
    switch ($action) {
        case 'check_connection':
            echo json_encode(checkDatabaseConnection());
            break;
            
        case 'test_mysql':
            echo json_encode(testMySQLConnection());
            break;
            
        case 'mysql_info':
            echo json_encode(getMySQLInfo());
            break;
            
        case 'verify_tables':
            echo json_encode(verifyTables());
            break;
            
        case 'check_structure':
            $table = $_GET['table'] ?? '';
            if ($table) {
                echo json_encode(checkTableStructure($table));
            } else {
                echo json_encode(['success' => false, 'message' => 'Table name required']);
            }
            break;
            
        case 'full_check':
            $mysqlTest = testMySQLConnection();
            $connectionCheck = checkDatabaseConnection();
            if ($connectionCheck['success']) {
                $tableCheck = verifyTables();
                $mysqlInfo = getMySQLInfo();
                echo json_encode([
                    'mysql_server' => $mysqlTest,
                    'connection' => $connectionCheck,
                    'tables' => $tableCheck,
                    'mysql_info' => $mysqlInfo
                ]);
            } else {
                echo json_encode([
                    'mysql_server' => $mysqlTest,
                    'connection' => $connectionCheck
                ]);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup - CandiHire</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            margin: 10px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            border: none;
            cursor: pointer;
        }
        .button:hover {
            background-color: #0056b3;
        }
        .result {
            margin-top: 20px;
            padding: 15px;
            border-radius: 4px;
            white-space: pre-wrap;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .info {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🗄️ Database Setup & Verification</h1>
        
        <p>Use the buttons below to check your database setup:</p>
        
        <div style="text-align: center;">
            <button class="button" onclick="testMySQL()">Test MySQL Server</button>
            <button class="button" onclick="checkConnection()">Check Database</button>
            <button class="button" onclick="getMySQLInfo()">MySQL Info</button>
            <button class="button" onclick="verifyTables()">Verify Tables</button>
            <button class="button" onclick="fullCheck()">Full Check</button>
        </div>
        
        <div id="result"></div>
    </div>

    <script>
        function showResult(data, type = 'info') {
            const resultDiv = document.getElementById('result');
            resultDiv.className = `result ${type}`;
            resultDiv.textContent = JSON.stringify(data, null, 2);
        }
        
        async function testMySQL() {
            try {
                const response = await fetch('?action=test_mysql');
                const data = await response.json();
                showResult(data, data.success ? 'success' : 'error');
            } catch (error) {
                showResult({success: false, message: 'Request failed: ' + error.message}, 'error');
            }
        }
        
        async function checkConnection() {
            try {
                const response = await fetch('?action=check_connection');
                const data = await response.json();
                showResult(data, data.success ? 'success' : 'error');
            } catch (error) {
                showResult({success: false, message: 'Request failed: ' + error.message}, 'error');
            }
        }
        
        async function getMySQLInfo() {
            try {
                const response = await fetch('?action=mysql_info');
                const data = await response.json();
                showResult(data, data.success ? 'success' : 'error');
            } catch (error) {
                showResult({success: false, message: 'Request failed: ' + error.message}, 'error');
            }
        }
        
        async function verifyTables() {
            try {
                const response = await fetch('?action=verify_tables');
                const data = await response.json();
                showResult(data, data.success ? 'success' : 'error');
            } catch (error) {
                showResult({success: false, message: 'Request failed: ' + error.message}, 'error');
            }
        }
        
        async function fullCheck() {
            try {
                const response = await fetch('?action=full_check');
                const data = await response.json();
                showResult(data, 'info');
            } catch (error) {
                showResult({success: false, message: 'Request failed: ' + error.message}, 'error');
            }
        }
        
        // Auto-run MySQL test on page load
        window.onload = function() {
            testMySQL();
        };
    </script>
</body>
</html>

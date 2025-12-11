<?php
// database_config.php - Database configuration file
// Modify these settings according to your MySQL server setup

// Database connection settings
define('DB_HOST', '127.0.0.1');           // MySQL server host
define('DB_NAME', 'candihire');           // Database name
define('DB_USER', 'root');                // MySQL username
define('DB_PASS', '');                    // MySQL password (empty for XAMPP default)
define('DB_CHARSET', 'utf8mb4');          // Character set
define('DB_COLLATE', 'utf8mb4_unicode_ci'); // Collation

// PDO connection options
define('DB_OPTIONS', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET . " COLLATE " . DB_COLLATE
]);

// Connection string
define('DB_DSN', 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET);

// Alternative connection string for testing (without database)
define('DB_DSN_NO_DB', 'mysql:host=' . DB_HOST . ';charset=' . DB_CHARSET);

// Common MySQL server configurations
$mysql_configs = [
    'xampp' => [
        'host' => '127.0.0.1',
        'user' => 'root',
        'pass' => '',
        'port' => 3306
    ],
    'wamp' => [
        'host' => '127.0.0.1',
        'user' => 'root',
        'pass' => '',
        'port' => 3306
    ],
    'mamp' => [
        'host' => '127.0.0.1',
        'user' => 'root',
        'pass' => 'root',
        'port' => 8889
    ],
    'custom' => [
        'host' => '127.0.0.1',
        'user' => 'your_username',
        'pass' => 'your_password',
        'port' => 3306
    ]
];

// Function to get current config
function getCurrentDBConfig() {
    return [
        'host' => DB_HOST,
        'database' => DB_NAME,
        'username' => DB_USER,
        'password' => DB_PASS,
        'charset' => DB_CHARSET,
        'collation' => DB_COLLATE
    ];
}

// Function to test connection with custom credentials
function testCustomConnection($host, $user, $pass, $database = null) {
    try {
        $dsn = $database ? 
            "mysql:host=$host;dbname=$database;charset=utf8mb4" : 
            "mysql:host=$host;charset=utf8mb4";
            
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        
        return [
            'success' => true,
            'message' => 'Connection successful',
            'connection' => $pdo
        ];
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'Connection failed: ' . $e->getMessage()
        ];
    }
}
?>

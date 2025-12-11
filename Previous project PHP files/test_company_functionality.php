<?php
// test_company_functionality.php - Test script for company features
require_once 'Database.php';
require_once 'session_manager.php';

echo "<h1>CandiHire Company Functionality Test</h1>\n";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;} .pass{color:green;} .fail{color:red;} .info{color:blue;} .section{margin:20px 0;padding:15px;border:1px solid #ccc;border-radius:5px;}</style>\n";

// Test 1: Database Connection
echo "<div class='section'>\n";
echo "<h2>1. Database Connection Test</h2>\n";
if (isset($pdo) && $pdo instanceof PDO) {
    echo "<p class='pass'>✓ Database connection successful</p>\n";
} else {
    echo "<p class='fail'>✗ Database connection failed</p>\n";
}
echo "</div>\n";

// Test 2: Company Table Structure
echo "<div class='section'>\n";
echo "<h2>2. Company Table Structure Test</h2>\n";
try {
    if (isset($pdo) && $pdo instanceof PDO) {
        $stmt = $pdo->query("DESCRIBE Company_login_info");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $requiredColumns = ['CompanyID', 'CompanyName', 'Email', 'Password', 'Industry', 'CompanySize', 'PhoneNumber', 'CompanyDescription', 'Logo', 'Website', 'Address', 'City', 'State', 'Country', 'PostalCode'];
        $existingColumns = array_column($columns, 'Field');
        
        echo "<p class='info'>Found " . count($existingColumns) . " columns in Company_login_info table</p>\n";
        
        $missingColumns = [];
        foreach ($requiredColumns as $required) {
            if (!in_array($required, $existingColumns)) {
                $missingColumns[] = $required;
            }
        }
        
        if (empty($missingColumns)) {
            echo "<p class='pass'>✓ All required columns present</p>\n";
        } else {
            echo "<p class='fail'>✗ Missing columns: " . implode(', ', $missingColumns) . "</p>\n";
        }
    }
} catch (Exception $e) {
    echo "<p class='fail'>✗ Error checking table structure: " . $e->getMessage() . "</p>\n";
}
echo "</div>\n";

// Test 3: Session Manager Functions
echo "<div class='section'>\n";
echo "<h2>3. Session Manager Functions Test</h2>\n";

// Test if functions exist
$sessionFunctions = ['isCompanyLoggedIn', 'getCurrentCompanyId', 'setCompanySession', 'clearCompanySession', 'requireCompanyLogin'];

foreach ($sessionFunctions as $function) {
    if (function_exists($function)) {
        echo "<p class='pass'>✓ Function {$function} exists</p>\n";
    } else {
        echo "<p class='fail'>✗ Function {$function} missing</p>\n";
    }
}
echo "</div>\n";

// Test 4: Company Registration Handler
echo "<div class='section'>\n";
echo "<h2>4. Company Registration Handler Test</h2>\n";
if (file_exists('company_reg_handler.php')) {
    echo "<p class='pass'>✓ company_reg_handler.php exists</p>\n";
    
    // Check if it has required functionality
    $content = file_get_contents('company_reg_handler.php');
    $requiredFeatures = ['json_decode', 'password_hash', 'PDO', 'Email validation', 'Password validation'];
    
    foreach ($requiredFeatures as $feature) {
        if (strpos($content, $feature) !== false || strpos($content, strtolower($feature)) !== false) {
            echo "<p class='pass'>✓ Contains {$feature}</p>\n";
        } else {
            echo "<p class='fail'>✗ Missing {$feature}</p>\n";
        }
    }
} else {
    echo "<p class='fail'>✗ company_reg_handler.php missing</p>\n";
}
echo "</div>\n";

// Test 5: Company Login Handler
echo "<div class='section'>\n";
echo "<h2>5. Company Login Handler Test</h2>\n";
if (file_exists('company_login_handler.php')) {
    echo "<p class='pass'>✓ company_login_handler.php exists</p>\n";
    
    $content = file_get_contents('company_login_handler.php');
    $requiredFeatures = ['password_verify', 'setCompanySession', 'PDO', 'JSON response'];
    
    foreach ($requiredFeatures as $feature) {
        if (strpos($content, $feature) !== false) {
            echo "<p class='pass'>✓ Contains {$feature}</p>\n";
        } else {
            echo "<p class='fail'>✗ Missing {$feature}</p>\n";
        }
    }
} else {
    echo "<p class='fail'>✗ company_login_handler.php missing</p>\n";
}
echo "</div>\n";

// Test 6: Company Profile Handler
echo "<div class='section'>\n";
echo "<h2>6. Company Profile Handler Test</h2>\n";
if (file_exists('company_profile_handler.php')) {
    echo "<p class='pass'>✓ company_profile_handler.php exists</p>\n";
    
    $content = file_get_contents('company_profile_handler.php');
    $requiredFeatures = ['GET', 'POST', 'FormData', 'file upload', 'Logo'];
    
    foreach ($requiredFeatures as $feature) {
        if (strpos($content, $feature) !== false || strpos($content, strtolower($feature)) !== false) {
            echo "<p class='pass'>✓ Contains {$feature}</p>\n";
        } else {
            echo "<p class='fail'>✗ Missing {$feature}</p>\n";
        }
    }
} else {
    echo "<p class='fail'>✗ company_profile_handler.php missing</p>\n";
}
echo "</div>\n";

// Test 7: Company Dashboard Files
echo "<div class='section'>\n";
echo "<h2>7. Company Dashboard Files Test</h2>\n";
$dashboardFiles = [
    'CompanyDashboard.php' => 'Main company dashboard',
    'JobPost.php' => 'Job posting management',
    'AIMatching.php' => 'AI candidate matching',
    'company_applications.php' => 'Company applications view'
];

foreach ($dashboardFiles as $file => $description) {
    if (file_exists($file)) {
        echo "<p class='pass'>✓ {$file} exists ({$description})</p>\n";
        
        // Check for session management
        $content = file_get_contents($file);
        if (strpos($content, 'session_manager.php') !== false) {
            echo "<p class='pass'>  → Has session management</p>\n";
        } else {
            echo "<p class='fail'>  → Missing session management</p>\n";
        }
        
        // Check for navigation
        if (strpos($content, 'nav-item') !== false) {
            echo "<p class='pass'>  → Has navigation menu</p>\n";
        } else {
            echo "<p class='fail'>  → Missing navigation menu</p>\n";
        }
    } else {
        echo "<p class='fail'>✗ {$file} missing ({$description})</p>\n";
    }
}
echo "</div>\n";

// Test 8: Upload Directories
echo "<div class='section'>\n";
echo "<h2>8. Upload Directories Test</h2>\n";
$uploadDirs = ['uploads', 'uploads/profiles', 'uploads/logos'];

foreach ($uploadDirs as $dir) {
    if (is_dir($dir)) {
        echo "<p class='pass'>✓ Directory {$dir} exists</p>\n";
        
        // Check if writable
        if (is_writable($dir)) {
            echo "<p class='pass'>  → Directory is writable</p>\n";
        } else {
            echo "<p class='fail'>  → Directory is not writable</p>\n";
        }
    } else {
        echo "<p class='fail'>✗ Directory {$dir} missing</p>\n";
    }
}

// Check .htaccess file
if (file_exists('uploads/.htaccess')) {
    echo "<p class='pass'>✓ .htaccess file exists in uploads directory</p>\n";
} else {
    echo "<p class='fail'>✗ .htaccess file missing in uploads directory</p>\n";
}
echo "</div>\n";

// Test 9: Navigation Consistency
echo "<div class='section'>\n";
echo "<h2>9. Navigation Consistency Test</h2>\n";
$navFiles = ['CompanyDashboard.php', 'JobPost.php', 'AIMatching.php', 'company_applications.php'];
$expectedNavItems = ['JobPost.php', 'CvChecker.php', 'CompanyDashboard.php', 'CreateExam.php', 'interview_schedule.php', 'company_applications.php', 'AIMatching.php'];

foreach ($navFiles as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $missingNav = [];
        
        foreach ($expectedNavItems as $navItem) {
            if (strpos($content, $navItem) === false) {
                $missingNav[] = $navItem;
            }
        }
        
        if (empty($missingNav)) {
            echo "<p class='pass'>✓ {$file} has all navigation items</p>\n";
        } else {
            echo "<p class='fail'>✗ {$file} missing navigation items: " . implode(', ', $missingNav) . "</p>\n";
        }
    }
}
echo "</div>\n";

// Test 10: CSS and Styling
echo "<div class='section'>\n";
echo "<h2>10. CSS and Styling Test</h2>\n";
foreach ($navFiles as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        
        // Check for CSS variables
        if (strpos($content, '--bg-primary') !== false) {
            echo "<p class='pass'>✓ {$file} uses CSS variables</p>\n";
        } else {
            echo "<p class='fail'>✗ {$file} missing CSS variables</p>\n";
        }
        
        // Check for responsive design
        if (strpos($content, '@media') !== false) {
            echo "<p class='pass'>✓ {$file} has responsive design</p>\n";
        } else {
            echo "<p class='fail'>✗ {$file} missing responsive design</p>\n";
        }
    }
}
echo "</div>\n";

// Summary
echo "<div class='section'>\n";
echo "<h2>Test Summary</h2>\n";
echo "<p class='info'>Company functionality test completed. Check results above for any issues that need to be addressed.</p>\n";
echo "<p class='info'>All company files should now have:</p>\n";
echo "<ul>\n";
echo "<li>✓ Proper session management</li>\n";
echo "<li>✓ Consistent navigation</li>\n";
echo "<li>✓ Authentication checks</li>\n";
echo "<li>✓ Profile editing functionality</li>\n";
echo "<li>✓ Proper file upload handling</li>\n";
echo "<li>✓ Responsive design</li>\n";
echo "</ul>\n";
echo "</div>\n";
?>

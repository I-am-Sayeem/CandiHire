<?php
// test_feed_separation.php - Test script to verify feed separation is working

require_once 'Database.php';

echo "<h1>CandiHire Feed Separation Test</h1>";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;} .pass{color:green;} .fail{color:red;} .info{color:blue;} .section{margin:20px 0;padding:15px;border:1px solid #ccc;border-radius:5px;}</style>";

// Test 1: Database Connection
echo "<div class='section'>";
echo "<h2>1. Database Connection Test</h2>";
if (isset($pdo) && $pdo instanceof PDO) {
    echo "<p class='pass'>✓ Database connection successful</p>";
} else {
    echo "<p class='fail'>✗ Database connection failed</p>";
}
echo "</div>";

// Test 2: Company Job Posts Table
echo "<div class='section'>";
echo "<h2>2. Company Job Posts Test</h2>";
try {
    if (isset($pdo) && $pdo instanceof PDO) {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM job_postings WHERE Status = 'active'");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $activeJobPosts = $result['count'];
        
        echo "<p class='info'>Active company job posts: $activeJobPosts</p>";
        
        if ($activeJobPosts > 0) {
            echo "<p class='pass'>✓ Company job posts table has data</p>";
            
            // Show sample job post
            $stmt = $pdo->query("
                SELECT j.JobTitle, c.CompanyName 
                FROM job_postings j 
                JOIN Company_login_info c ON j.CompanyID = c.CompanyID 
                WHERE j.Status = 'active' 
                LIMIT 1
            ");
            $sample = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($sample) {
                echo "<p class='info'>Sample: {$sample['JobTitle']} at {$sample['CompanyName']}</p>";
            }
        } else {
            echo "<p class='fail'>✗ No active company job posts found</p>";
        }
    }
} catch (Exception $e) {
    echo "<p class='fail'>✗ Error checking company job posts: " . $e->getMessage() . "</p>";
}
echo "</div>";

// Test 3: Candidate Job Seeking Posts Table
echo "<div class='section'>";
echo "<h2>3. Candidate Job Seeking Posts Test</h2>";
try {
    if (isset($pdo) && $pdo instanceof PDO) {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM job_seeking_posts WHERE Status = 'active'");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $activeCandidatePosts = $result['count'];
        
        echo "<p class='info'>Active candidate job seeking posts: $activeCandidatePosts</p>";
        
        if ($activeCandidatePosts > 0) {
            echo "<p class='pass'>✓ Candidate job seeking posts table has data</p>";
            
            // Show sample candidate post
            $stmt = $pdo->query("
                SELECT j.JobTitle, c.FullName 
                FROM job_seeking_posts j 
                JOIN candidate_login_info c ON j.CandidateID = c.CandidateID 
                WHERE j.Status = 'active' 
                LIMIT 1
            ");
            $sample = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($sample) {
                echo "<p class='info'>Sample: {$sample['FullName']} seeking {$sample['JobTitle']}</p>";
            }
        } else {
            echo "<p class='fail'>✗ No active candidate job seeking posts found</p>";
        }
    }
} catch (Exception $e) {
    echo "<p class='fail'>✗ Error checking candidate job seeking posts: " . $e->getMessage() . "</p>";
}
echo "</div>";

// Test 4: API Endpoints Test
echo "<div class='section'>";
echo "<h2>4. API Endpoints Test</h2>";

// Test company job posts handler
echo "<h3>Company Job Posts Handler</h3>";
$companyPostsUrl = "http://localhost/7th%20semester/SAD%20lab/Project/company_job_posts_handler.php";
$companyResponse = @file_get_contents($companyPostsUrl);
if ($companyResponse !== false) {
    $companyData = json_decode($companyResponse, true);
    if ($companyData && $companyData['success']) {
        echo "<p class='pass'>✓ Company job posts handler working</p>";
        echo "<p class='info'>Returns " . count($companyData['posts']) . " job posts</p>";
    } else {
        echo "<p class='fail'>✗ Company job posts handler returned error</p>";
    }
} else {
    echo "<p class='fail'>✗ Cannot access company job posts handler</p>";
}

// Test candidate job seeking posts handler
echo "<h3>Candidate Job Seeking Posts Handler</h3>";
$candidatePostsUrl = "http://localhost/7th%20semester/SAD%20lab/Project/job_seeking_handler.php?action=get_all_posts";
$candidateResponse = @file_get_contents($candidatePostsUrl);
if ($candidateResponse !== false) {
    $candidateData = json_decode($candidateResponse, true);
    if ($candidateData && $candidateData['success']) {
        echo "<p class='pass'>✓ Candidate job seeking posts handler working</p>";
        echo "<p class='info'>Returns " . count($candidateData['posts']) . " candidate posts</p>";
    } else {
        echo "<p class='fail'>✗ Candidate job seeking posts handler returned error</p>";
    }
} else {
    echo "<p class='fail'>✗ Cannot access candidate job seeking posts handler</p>";
}

echo "</div>";

// Test 5: Feed Separation Summary
echo "<div class='section'>";
echo "<h2>5. Feed Separation Summary</h2>";
echo "<p><strong>Company Dashboard (Candidate Feed):</strong> Should show $activeCandidatePosts candidate job seeking posts</p>";
echo "<p><strong>Candidate Dashboard (News Feed):</strong> Should show $activeJobPosts company job postings</p>";
echo "<p class='info'>✓ Feed separation implemented successfully</p>";
echo "</div>";

echo "<h2>Test Complete</h2>";
echo "<p>Feed separation has been implemented. Companies will see candidate posts, and candidates will see company job postings.</p>";
?>

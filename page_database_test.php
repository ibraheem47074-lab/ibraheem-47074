<?php
// Page Database Connection Test
echo "<!DOCTYPE html>
<html>
<head>
    <title>Page Database Connection Test</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <style>
        .test-result { margin: 10px 0; padding: 15px; border-radius: 5px; }
        .success { background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .warning { background-color: #fff3cd; border: 1px solid #ffeaa7; color: #856404; }
        .error { background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        .info { background-color: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; }
    </style>
</head>
<body>
<div class='container mt-5'>
    <h2 class='text-center mb-4'>Page Database Connection Test</h2>";

// Test database connection first
try {
    require_once 'config/database.php';
    echo "<div class='test-result success'>â Database connection established</div>";
} catch (Exception $e) {
    echo "<div class='test-result error'>â Database connection failed: " . $e->getMessage() . "</div>";
    echo "</div></body></html>";
    exit;
}

// Test key pages
$pages = [
    'index.php' => 'Home Page',
    'news.php' => 'News Page',
    'live.php' => 'Live Streaming Page',
    'category.php' => 'Category Page',
    'admin/index.php' => 'Admin Panel',
    'contact.php' => 'Contact Page',
    'search.php' => 'Search Page'
];

echo "<h3>Page Database Integration Tests</h3>";

foreach ($pages as $page => $description) {
    echo "<div class='card mb-3'>
            <div class='card-header'>
                <h5>$description</h5>
            </div>
            <div class='card-body'>";
    
    if (file_exists($page)) {
        echo "<div class='test-result success'>â File exists: $page</div>";
        
        // Check file content for database integration
        $content = file_get_contents($page);
        
        // Check for database config inclusion
        if (strpos($content, 'config/database.php') !== false) {
            echo "<div class='test-result success'>â Database config included</div>";
        } elseif (strpos($content, 'database.php') !== false) {
            echo "<div class='test-result success'>â Database file referenced</div>";
        } else {
            echo "<div class='test-result warning'>â No database config found</div>";
        }
        
        // Check for database queries
        if (strpos($content, 'mysqli_query') !== false) {
            echo "<div class='test-result success'>â Contains database queries</div>";
        } else {
            echo "<div class='test-result info'>â No database queries detected</div>";
        }
        
        // Test actual database functionality by simulating page load
        try {
            // Capture output
            ob_start();
            include_once $page;
            $output = ob_get_clean();
            
            if (!empty($output)) {
                echo "<div class='test-result success'>â Page loads without database errors</div>";
            }
        } catch (Exception $e) {
            echo "<div class='test-result error'>â Page error: " . $e->getMessage() . "</div>";
        } catch (Error $e) {
            echo "<div class='test-result error'>â Fatal error: " . $e->getMessage() . "</div>";
        }
        
    } else {
        echo "<div class='test-result error'>â File missing: $page</div>";
    }
    
    echo "</div></div>";
}

// Test critical database tables
echo "<h3>Critical Database Tables Test</h3>";

$critical_tables = [
    'news' => 'News articles',
    'users' => 'User accounts',
    'categories' => 'News categories',
    'channels' => 'Live channels',
    'settings' => 'Site settings',
    'admin' => 'Admin users'
];

foreach ($critical_tables as $table => $description) {
    $result = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
    
    if (mysqli_num_rows($result) > 0) {
        $count_result = mysqli_query($conn, "SELECT COUNT(*) as count FROM `$table`");
        $count = mysqli_fetch_assoc($count_result)['count'];
        
        echo "<div class='test-result success'>â $table exists ($count records) - $description</div>";
    } else {
        echo "<div class='test-result error'>â $table missing - $description</div>";
    }
}

// Test database functionality
echo "<h3>Database Functionality Tests</h3>";

// Test SELECT query
try {
    $test_query = mysqli_query($conn, "SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = DATABASE()");
    $row = mysqli_fetch_assoc($test_query);
    echo "<div class='test-result success'>â SELECT query works: {$row['count']} tables found</div>";
} catch (Exception $e) {
    echo "<div class='test-result error'>â SELECT query failed: " . $e->getMessage() . "</div>";
}

// Test INSERT query (temporary)
try {
    mysqli_query($conn, "CREATE TEMPORARY TABLE test_connection (id INT)");
    mysqli_query($conn, "INSERT INTO test_connection (id) VALUES (1)");
    mysqli_query($conn, "DROP TEMPORARY TABLE test_connection");
    echo "<div class='test-result success'>â INSERT/CREATE operations work</div>";
} catch (Exception $e) {
    echo "<div class='test-result error'>â INSERT/CREATE failed: " . $e->getMessage() . "</div>";
}

// Test connection performance
$start_time = microtime(true);
for ($i = 0; $i < 5; $i++) {
    mysqli_query($conn, "SELECT 1");
}
$end_time = microtime(true);
$execution_time = ($end_time - $start_time) * 1000;

echo "<div class='test-result info'>â Query performance: " . number_format($execution_time, 2) . "ms for 5 queries</div>";

echo "<div class='text-center mt-4'>
        <a href='index.php' class='btn btn-primary btn-lg me-2'>Back to Home</a>
        <a href='check_database_status.php' class='btn btn-info btn-lg me-2'>Database Status</a>
        <a href='admin/' class='btn btn-secondary btn-lg'>Admin Panel</a>
      </div>";

echo "</div></body></html>";
?>

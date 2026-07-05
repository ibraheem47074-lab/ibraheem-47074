<?php
// Database Connection Test Script
echo "<h2>Database Connection Test</h2>";

// Test 1: Direct MySQL Connection
echo "<h3>Test 1: Direct MySQL Connection</h3>";
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'pk_live_news';

$conn = mysqli_connect($host, $user, $pass, $dbname);
if ($conn) {
    echo "<p style='color:green'>â Direct connection successful</p>";
    echo "<p>Connected to database: $dbname</p>";
    echo "<p>MySQL version: " . mysqli_get_server_info($conn) . "</p>";
    
    // Test basic query
    $result = mysqli_query($conn, "SELECT 1 as test");
    if ($result) {
        echo "<p style='color:green'>â Basic query test passed</p>";
    } else {
        echo "<p style='color:red'>â Basic query test failed</p>";
    }
    
    mysqli_close($conn);
} else {
    echo "<p style='color:red'>â Direct connection failed: " . mysqli_connect_error() . "</p>";
}

// Test 2: Config-based Connection
echo "<h3>Test 2: Config-based Connection</h3>";
try {
    require_once 'config/database.php';
    echo "<p style='color:green'>â Config file loaded successfully</p>";
    
    if (isset($conn) && $conn) {
        echo "<p style='color:green'>â Database connection established via config</p>";
        
        // Test with a simple query
        $test_query = mysqli_query($conn, "SELECT COUNT(*) as table_count FROM information_schema.tables WHERE table_schema = '$dbname'");
        if ($test_query) {
            $row = mysqli_fetch_assoc($test_query);
            echo "<p>Tables found in database: " . $row['table_count'] . "</p>";
        }
        
        // Check main tables
        $main_tables = ['news', 'users', 'categories', 'channels', 'settings'];
        foreach ($main_tables as $table) {
            $result = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
            if (mysqli_num_rows($result) > 0) {
                echo "<p style='color:green'>â Table '$table' exists</p>";
            } else {
                echo "<p style='color:orange'>â Table '$table' missing</p>";
            }
        }
        
    } else {
        echo "<p style='color:red'>â Config connection failed</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'>â Config error: " . $e->getMessage() . "</p>";
}

// Test 3: Environment Configuration
echo "<h3>Test 3: Environment Configuration</h3>";
try {
    require_once 'config/env.php';
    echo "<p style='color:green'>â Environment loader loaded</p>";
    
    // Check environment variables
    $env_vars = ['DB_HOST', 'DB_USER', 'DB_NAME', 'SITE_URL'];
    foreach ($env_vars as $var) {
        if (defined($var)) {
            echo "<p style='color:green'>â $var: " . constant($var) . "</p>";
        } else {
            echo "<p style='color:orange'>â $var not defined</p>";
        }
    }
} catch (Exception $e) {
    echo "<p style='color:red'>â Environment error: " . $e->getMessage() . "</p>";
}

// Test 4: Page Integration Tests
echo "<h3>Test 4: Page Integration Tests</h3>";
$test_pages = [
    'index.php' => 'Main page',
    'news.php' => 'News page',
    'admin/index.php' => 'Admin panel',
    'live.php' => 'Live streaming',
    'category.php' => 'Categories'
];

foreach ($test_pages as $page => $description) {
    if (file_exists($page)) {
        echo "<p style='color:green'>â $description ($page) - File exists</p>";
        
        // Check if file includes database config
        $content = file_get_contents($page);
        if (strpos($content, 'config/database.php') !== false || strpos($content, 'database.php') !== false) {
            echo "<p style='color:green'>â $description - Database config included</p>";
        } else {
            echo "<p style='color:orange'>â $description - Database config not found</p>";
        }
    } else {
        echo "<p style='color:red'>â $description ($page) - File missing</p>";
    }
}

// Test 5: Database Performance
echo "<h3>Test 5: Database Performance</h3>";
if (isset($conn) && $conn) {
    $start_time = microtime(true);
    
    // Test multiple queries
    for ($i = 0; $i < 10; $i++) {
        mysqli_query($conn, "SELECT 1");
    }
    
    $end_time = microtime(true);
    $execution_time = ($end_time - $start_time) * 1000; // Convert to milliseconds
    
    echo "<p>Query performance: " . number_format($execution_time, 2) . "ms for 10 queries</p>";
    
    if ($execution_time < 100) {
        echo "<p style='color:green'>â Performance: Excellent</p>";
    } elseif ($execution_time < 500) {
        echo "<p style='color:orange'>â Performance: Good</p>";
    } else {
        echo "<p style='color:red'>â Performance: Needs optimization</p>";
    }
}

// Test 6: Connection Limits
echo "<h3>Test 6: Connection Limits</h3>";
if (isset($conn) && $conn) {
    $result = mysqli_query($conn, "SHOW VARIABLES LIKE 'max_connections'");
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        echo "<p>Max connections: " . $row['Value'] . "</p>";
    }
    
    $result = mysqli_query($conn, "SHOW STATUS LIKE 'Threads_connected'");
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        echo "<p>Current connections: " . $row['Value'] . "</p>";
    }
}

echo "<h3>Test Summary</h3>";
echo "<p><strong>Database Host:</strong> $host</p>";
echo "<p><strong>Database Name:</strong> $dbname</p>";
echo "<p><strong>PHP Version:</strong> " . PHP_VERSION . "</p>";
echo "<p><strong>MySQL Extension:</strong> " . (extension_loaded('mysqli') ? 'Loaded' : 'Not Loaded') . "</p>";
echo "<p><strong>Test Time:</strong> " . date('Y-m-d H:i:s') . "</p>";

echo "<p><a href='index.php'>Return to Home</a></p>";
?>

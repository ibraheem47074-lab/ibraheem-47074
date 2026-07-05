<?php
echo "<h1>PK Live News - Simple Diagnostic</h1>";

// Test 1: Basic PHP functionality
echo "<h2>1. PHP Test</h2>";
echo "<div>PHP Version: " . PHP_VERSION . "</div>";
echo "<div>Current Directory: " . __DIR__ . "</div>";
echo "<div>Document Root: " . $_SERVER['DOCUMENT_ROOT'] ?? 'Not available' . "</div>";

// Test 2: File existence
echo "<h2>2. File Check</h2>";
$files_to_check = [
    'config/database.php' => 'Database config',
    'includes/language_functions.php' => 'Language functions',
    'includes/header.php' => 'Header file',
    'index.php' => 'Main index',
    'simple_setup.php' => 'Setup script'
];

foreach ($files_to_check as $file => $description) {
    $exists = file_exists($file);
    echo "<div style='color: " . ($exists ? 'green' : 'red') . ";'>✓ " . $description . ": " . ($exists ? 'EXISTS' : 'MISSING') . "</div>";
}

// Test 3: Database connection (basic)
echo "<h2>3. Database Connection Test</h2>";
try {
    // Test without database first
    $conn_basic = mysqli_connect('localhost', 'root', '');
    if ($conn_basic) {
        echo "<div style='color: green;'>✓ Basic MySQL connection successful</div>";
        
        // Test database creation
        $db_name = 'pk_live_news';
        $create_db = "CREATE DATABASE IF NOT EXISTS `$db_name`";
        if (mysqli_query($conn_basic, $create_db)) {
            echo "<div style='color: green;'>✓ Database '$db_name' ready</div>";
            
            // Test database selection
            if (mysqli_select_db($conn_basic, $db_name)) {
                echo "<div style='color: green;'>✓ Database '$db_name' selected</div>";
                
                // Test table creation
                $test_table = "CREATE TABLE IF NOT EXISTS test_table (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )";
                if (mysqli_query($conn_basic, $test_table)) {
                    echo "<div style='color: green;'>✓ Can create tables</div>";
                    
                    // Clean up test table
                    mysqli_query($conn_basic, "DROP TABLE IF EXISTS test_table");
                } else {
                    echo "<div style='color: red;'>✗ Cannot create tables: " . mysqli_error($conn_basic) . "</div>";
                }
            } else {
                echo "<div style='color: red;'>✗ Cannot select database '$db_name'</div>";
            }
        } else {
            echo "<div style='color: red;'>✗ Cannot create database: " . mysqli_error($conn_basic) . "</div>";
        }
        mysqli_close($conn_basic);
    } else {
        echo "<div style='color: red;'>✗ MySQL connection failed: " . mysqli_connect_error() . "</div>";
    }
} catch (Exception $e) {
    echo "<div style='color: red;'>✗ Database error: " . $e->getMessage() . "</div>";
}

// Test 4: Include test
echo "<h2>4. Include Test</h2>";
try {
    if (file_exists('config/database.php')) {
        include 'config/database.php';
        echo "<div style='color: green;'>✓ Database config included successfully</div>";
        
        if (isset($conn) && $conn) {
            echo "<div style='color: green;'>✓ Database connection variable available</div>";
        } else {
            echo "<div style='color: orange;'>⚠ Database connection variable not set</div>";
        }
    } else {
        echo "<div style='color: red;'>✗ Cannot include database config (file missing)</div>";
    }
} catch (Exception $e) {
    echo "<div style='color: red;'>✗ Include error: " . $e->getMessage() . "</div>";
}

// Test 5: Session test
echo "<h2>5. Session Test</h2>";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
echo "<div>Session Status: " . session_status() . "</div>";
echo "<div>Session ID: " . session_id() . "</div>";

// Test 6: Error reporting
echo "<h2>6. Error Reporting</h2>";
echo "<div>Error Reporting: " . (error_reporting() ? 'Enabled' : 'Disabled') . "</div>";
echo "<div>Display Errors: " . (ini_get('display_errors') ? 'On' : 'Off') . "</div>";

// Test 7: Memory and limits
echo "<h2>7. PHP Limits</h2>";
echo "<div>Memory Limit: " . ini_get('memory_limit') . "</div>";
echo "<div>Max Execution Time: " . ini_get('max_execution_time') . "s</div>";
echo "<div>Upload Max Filesize: " . ini_get('upload_max_filesize') . "</div>";

// Test 8: Web server info
echo "<h2>8. Web Server Info</h2>";
echo "<div>Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "</div>";
echo "<div>Server Port: " . ($_SERVER['SERVER_PORT'] ?? 'Unknown') . "</div>";
echo "<div>Request URI: " . ($_SERVER['REQUEST_URI'] ?? 'Unknown') . "</div>";
echo "<div>HTTP Host: " . ($_SERVER['HTTP_HOST'] ?? 'Unknown') . "</div>";

// Test 9: Simple working page
echo "<h2>9. Simple Page Test</h2>";
echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 4px;'>";
echo "<strong>Basic HTML Test:</strong><br>";
echo "<a href='?test=1'>Test Link 1</a> | ";
echo "<a href='?test=2'>Test Link 2</a> | ";
echo "<a href='?test=reset'>Reset</a><br><br>";

if (isset($_GET['test'])) {
    echo "<div style='color: blue;'>✓ Link clicked! Test parameter: " . $_GET['test'] . "</div>";
}

if (isset($_GET['reset'])) {
    echo "<div style='color: green;'>✓ Page reset successful</div>";
}

echo "<form method='POST' style='margin-top: 10px;'>";
echo "<input type='text' name='test_input' placeholder='Type something here'>";
echo "<button type='submit'>Submit</button>";
echo "</form>";

if (isset($_POST['test_input'])) {
    echo "<div style='color: green;'>✓ Form submitted: " . htmlspecialchars($_POST['test_input']) . "</div>";
}

echo "</div>";

// Recommendations
echo "<h2>🔧 Recommendations</h2>";
echo "<div style='background: #f0f8ff; padding: 15px; border-radius: 4px; margin: 20px 0;'>";

echo "<strong>If pages won't open, check these:</strong><br><br>";

echo "1. <strong>Web Server:</strong> Make sure Apache/Nginx is running<br>";
echo "2. <strong>PHP:</strong> Verify PHP is installed and working<br>";
echo "3. <strong>File Permissions:</strong> Check if files have read permissions<br>";
echo "4. <strong>URL:</strong> Try accessing via http://localhost/pk-live-news/<br>";
echo "5. <strong>Error Logs:</strong> Check web server error logs<br>";
echo "6. <strong>.htaccess:</strong> Temporarily rename .htaccess if it exists<br>";

echo "<br><strong>Quick Fixes:</strong><br>";
echo "• Restart your web server (Apache/Nginx)<br>";
echo "• Check if port 80/443 is accessible<br>";
echo "• Clear browser cache and cookies<br>";
echo "• Try a different browser<br>";
echo "• Check Windows firewall settings<br>";

echo "</div>";

echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
h1, h2 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 5px; }
div { margin: 5px 0; padding: 5px; }
a { color: #007bff; text-decoration: none; }
a:hover { text-decoration: underline; }
input, button { padding: 5px 10px; margin: 2px; }
</style>";
?>

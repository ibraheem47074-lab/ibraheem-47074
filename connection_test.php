<?php
// Comprehensive Website Connection Test
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>
<html>
<head>
    <title>PK Live News - Connection Test Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .success { background-color: #d4edda; border-color: #c3e6cb; color: #155724; }
        .error { background-color: #f8d7da; border-color: #f5c6cb; color: #721c24; }
        .warning { background-color: #fff3cd; border-color: #ffeaa7; color: #856404; }
        .info { background-color: #d1ecf1; border-color: #bee5eb; color: #0c5460; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; }
        .test-result { margin: 10px 0; padding: 10px; border-radius: 3px; }
    </style>
</head>
<body>
    <h1>PK Live News - Comprehensive Connection Test</h1>
    <p>Generated on: " . date('Y-m-d H:i:s') . "</p>";

// Test 1: Database Connection
echo "<div class='test-section'>
    <h2>1. Database Connection Test</h2>";

try {
    // Load environment
    require_once 'config/env.php';
    require_once 'config/settings.php';
    
    // Test database connection
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn) {
        echo "<div class='test-result success'>Database Connection: SUCCESS</div>";
        echo "<p>Connected to: " . DB_HOST . " / " . DB_NAME . "</p>";
        
        // Test basic query
        $result = mysqli_query($conn, "SELECT 1 as test");
        if ($result) {
            echo "<div class='test-result success'>Basic Query Test: PASSED</div>";
        }
        
        // Check important tables
        $tables = ['articles', 'users', 'categories', 'site_settings'];
        foreach ($tables as $table) {
            $result = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
            if (mysqli_num_rows($result) > 0) {
                echo "<div class='test-result success'>Table '$table': EXISTS</div>";
            } else {
                echo "<div class='test-result error'>Table '$table': MISSING</div>";
            }
        }
        
        // Check character set
        $result = mysqli_query($conn, "SHOW VARIABLES LIKE 'character_set_database'");
        $row = mysqli_fetch_assoc($result);
        echo "<div class='test-result info'>Database Character Set: " . $row['Value'] . "</div>";
        
    } else {
        echo "<div class='test-result error'>Database Connection: FAILED - " . mysqli_connect_error() . "</div>";
    }
} catch (Exception $e) {
    echo "<div class='test-result error'>Database Connection Error: " . $e->getMessage() . "</div>";
}

echo "</div>";

// Test 2: File System Permissions
echo "<div class='test-section'>
    <h2>2. File System Permissions Test</h2>";

$paths = [
    'uploads/' => 'Upload Directory',
    'uploads/avatars/' => 'Avatar Uploads',
    'uploads/categories/' => 'Category Images',
    'uploads/ads/' => 'Advertisement Images',
    'cache/' => 'Cache Directory',
    'logs/' => 'Logs Directory',
    'config/' => 'Config Directory'
];

foreach ($paths as $path => $description) {
    if (is_dir($path)) {
        if (is_writable($path)) {
            echo "<div class='test-result success'>$description ($path): WRITABLE</div>";
        } else {
            echo "<div class='test-result error'>$description ($path): NOT WRITABLE</div>";
        }
    } else {
        echo "<div class='test-result warning'>$description ($path): DOES NOT EXIST</div>";
    }
}

echo "</div>";

// Test 3: API Endpoints
echo "<div class='test-section'>
    <h2>3. API Endpoints Test</h2>";

$api_files = [
    'api/breaking-news.php' => 'Breaking News API',
    'api/countries_with_news.php' => 'Countries API',
    'api/weather.php' => 'Weather API',
    'api/bookmarks.php' => 'Bookmarks API'
];

foreach ($api_files as $file => $description) {
    if (file_exists($file)) {
        echo "<div class='test-result success'>$description: FILE EXISTS</div>";
        
        // Check for syntax errors
        $output = [];
        $return_code = 0;
        exec("php -l $file 2>&1", $output, $return_code);
        
        if ($return_code === 0) {
            echo "<div class='test-result success'>$description: SYNTAX OK</div>";
        } else {
            echo "<div class='test-result error'>$description: SYNTAX ERROR - " . implode(', ', $output) . "</div>";
        }
    } else {
        echo "<div class='test-result error'>$description: FILE MISSING</div>";
    }
}

echo "</div>";

// Test 4: RSS Feed Configuration
echo "<div class='test-section'>
    <h2>4. RSS Feed Configuration Test</h2>";

// Check RSS files
$rss_files = ['rss.php', 'rss_news.php', 'import_rss.php'];
foreach ($rss_files as $file) {
    if (file_exists($file)) {
        echo "<div class='test-result success'>RSS File '$file': EXISTS</div>";
    } else {
        echo "<div class='test-result warning'>RSS File '$file': MISSING</div>";
    }
}

// Check cron job setup
echo "<div class='test-result info'>Checking RSS cron job setup...</div>";
if (file_exists('logs/cron_import.log')) {
    echo "<div class='test-result success'>Cron log file exists</div>";
    $log_size = filesize('logs/cron_import.log');
    echo "<div class='test-result info'>Log file size: " . $log_size . " bytes</div>";
} else {
    echo "<div class='test-result warning'>Cron log file not found</div>";
}

echo "</div>";

// Test 5: Live Streaming Configuration
echo "<div class='test-section'>
    <h2>5. Live Streaming Configuration Test</h2>";

if (is_dir('uploads/channels/')) {
    $channel_files = glob('uploads/channels/*.html');
    echo "<div class='test-result success'>Channel directory exists with " . count($channel_files) . " channel files</div>";
    
    // Check a few channel files
    foreach (array_slice($channel_files, 0, 3) as $file) {
        echo "<div class='test-result info'>Channel: " . basename($file) . "</div>";
    }
} else {
    echo "<div class='test-result warning'>Channel directory not found</div>";
}

echo "</div>";

// Test 6: Weather API Integration
echo "<div class='test-section'>
    <h2>6. Weather API Integration Test</h2>";

if (file_exists('cache/weather_cache.json')) {
    echo "<div class='test-result success'>Weather cache file exists</div>";
    
    $weather_data = json_decode(file_get_contents('cache/weather_cache.json'), true);
    if ($weather_data && isset($weather_data['last_updated'])) {
        echo "<div class='test-result info'>Last weather update: " . $weather_data['last_updated'] . "</div>";
        
        // Check if data is recent (within last hour)
        $last_update = strtotime($weather_data['last_updated']);
        $time_diff = time() - $last_update;
        
        if ($time_diff < 3600) {
            echo "<div class='test-result success'>Weather data is recent</div>";
        } else {
            echo "<div class='test-result warning'>Weather data is outdated</div>";
        }
    }
} else {
    echo "<div class='test-result warning'>Weather cache file not found</div>";
}

echo "</div>";

// Test 7: Configuration Files
echo "<div class='test-section'>
    <h2>7. Configuration Files Test</h2>";

$config_files = [
    'config/database.php' => 'Database Config',
    'config/settings.php' => 'Settings Config',
    'config/env.php' => 'Environment Config',
    'config/helpers.php' => 'Helper Functions',
    '.htaccess' => 'Apache Configuration',
    'index.php' => 'Main Index File'
];

foreach ($config_files as $file => $description) {
    if (file_exists($file)) {
        echo "<div class='test-result success'>$description: EXISTS</div>";
        
        // Check syntax for PHP files
        if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
            $output = [];
            $return_code = 0;
            exec("php -l $file 2>&1", $output, $return_code);
            
            if ($return_code === 0) {
                echo "<div class='test-result success'>$description: SYNTAX OK</div>";
            } else {
                echo "<div class='test-result error'>$description: SYNTAX ERROR</div>";
                echo "<pre>" . implode("\n", $output) . "</pre>";
            }
        }
    } else {
        echo "<div class='test-result error'>$description: MISSING</div>";
    }
}

echo "</div>";

// Test 8: PHP Configuration
echo "<div class='test-section'>
    <h2>8. PHP Configuration Test</h2>";

$php_requirements = [
    'memory_limit' => '128M',
    'max_execution_time' => '30',
    'post_max_size' => '8M',
    'upload_max_filesize' => '5M',
    'max_input_vars' => '1000'
];

foreach ($php_requirements as $setting => $recommended) {
    $current = ini_get($setting);
    echo "<div class='test-result info'>$setting: $current (Recommended: $recommended)</div>";
}

// Check required extensions
$required_extensions = ['mysqli', 'json', 'curl', 'gd', 'mbstring'];
foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<div class='test-result success'>Extension '$ext': LOADED</div>";
    } else {
        echo "<div class='test-result error'>Extension '$ext': NOT LOADED</div>";
    }
}

echo "</div>";

// Test 9: Security Configuration
echo "<div class='test-section'>
    <h2>9. Security Configuration Test</h2>";

if (file_exists('.htaccess')) {
    echo "<div class='test-result success'>.htaccess file exists</div>";
    
    $htaccess_content = file_get_contents('.htaccess');
    if (strpos($htaccess_content, 'RewriteEngine') !== false) {
        echo "<div class='test-result success'>URL rewriting configured</div>";
    }
    
    if (strpos($htaccess_content, 'php_flag') !== false) {
        echo "<div class='test-result info'>PHP flags configured in .htaccess</div>";
    }
} else {
    echo "<div class='test-result warning'>.htaccess file not found</div>";
}

// Check error reporting settings
if (ini_get('display_errors')) {
    echo "<div class='test-result warning'>Error display is ON (should be OFF in production)</div>";
} else {
    echo "<div class='test-result success'>Error display is OFF</div>";
}

echo "</div>";

// Test 10: External Service Connections
echo "<div class='test-section'>
    <h2>10. External Service Connections Test</h2>";

// Test cURL functionality
if (function_exists('curl_init')) {
    echo "<div class='test-result success'>cURL is available</div>";
    
    // Test a simple HTTP request
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://www.google.com');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    // PHP 8.5+ automatically handles cURL resource cleanup
    
    if ($http_code === 200) {
        echo "<div class='test-result success'>External HTTP requests: WORKING</div>";
    } else {
        echo "<div class='test-result error'>External HTTP requests: FAILED (HTTP Code: $http_code)</div>";
    }
} else {
    echo "<div class='test-result error'>cURL is not available</div>";
}

echo "</div>";

echo "<div class='test-section info'>
    <h2>Test Summary</h2>
    <p>This comprehensive test has checked:</p>
    <ul>
        <li>Database connectivity and table structure</li>
        <li>File system permissions and directory structure</li>
        <li>API endpoint availability and syntax</li>
        <li>RSS feed configuration</li>
        <li>Live streaming setup</li>
        <li>Weather API integration</li>
        <li>Configuration file integrity</li>
        <li>PHP configuration and extensions</li>
        <li>Security settings</li>
        <li>External service connectivity</li>
    </ul>
    <p><strong>Note:</strong> Address any ERROR or WARNING items above to ensure optimal website performance.</p>
</div>

</body>
</html>";
?>

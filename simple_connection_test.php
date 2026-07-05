<?php
// Simple Connection Test - No HTML, just output
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== PK Live News Connection Test ===\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n\n";

// Test 1: Database Connection
echo "1. DATABASE CONNECTION TEST\n";
echo "---------------------------\n";

try {
    // Check if config files exist
    if (!file_exists('config/database.php')) {
        echo "ERROR: config/database.php not found\n";
    } else {
        require_once 'config/database.php';
        echo "SUCCESS: Database config loaded\n";
        
        if (isset($conn) && $conn) {
            echo "SUCCESS: Database connected\n";
            
            // Test query
            $result = mysqli_query($conn, "SELECT VERSION() as version");
            if ($result) {
                $row = mysqli_fetch_assoc($result);
                echo "MySQL Version: " . $row['version'] . "\n";
            }
            
            // Check tables
            $tables = ['articles', 'users', 'categories', 'site_settings'];
            foreach ($tables as $table) {
                $result = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
                if (mysqli_num_rows($result) > 0) {
                    echo "Table '$table': EXISTS\n";
                } else {
                    echo "Table '$table': MISSING\n";
                }
            }
        } else {
            echo "ERROR: Database connection failed\n";
        }
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 2: File Permissions
echo "2. FILE PERMISSIONS TEST\n";
echo "------------------------\n";

$paths = ['uploads/', 'cache/', 'logs/', 'config/'];
foreach ($paths as $path) {
    if (is_dir($path)) {
        $writable = is_writable($path) ? "WRITABLE" : "NOT WRITABLE";
        echo "$path: EXISTS - $writable\n";
    } else {
        echo "$path: MISSING\n";
    }
}

echo "\n";

// Test 3: PHP Configuration
echo "3. PHP CONFIGURATION TEST\n";
echo "-------------------------\n";

echo "PHP Version: " . PHP_VERSION . "\n";
echo "Memory Limit: " . ini_get('memory_limit') . "\n";
echo "Max Execution Time: " . ini_get('max_execution_time') . "\n";
echo "Upload Max Filesize: " . ini_get('upload_max_filesize') . "\n";

$extensions = ['mysqli', 'json', 'curl', 'gd', 'mbstring'];
foreach ($extensions as $ext) {
    $loaded = extension_loaded($ext) ? "LOADED" : "NOT LOADED";
    echo "Extension $ext: $loaded\n";
}

echo "\n";

// Test 4: Critical Files
echo "4. CRITICAL FILES TEST\n";
echo "----------------------\n";

$files = [
    'index.php' => 'Main Index',
    '.htaccess' => 'Apache Config',
    'api/breaking-news.php' => 'Breaking News API',
    'rss.php' => 'RSS Feed',
    'admin/index.php' => 'Admin Panel'
];

foreach ($files as $file => $desc) {
    if (file_exists($file)) {
        echo "$desc ($file): EXISTS\n";
    } else {
        echo "$desc ($file): MISSING\n";
    }
}

echo "\n";

// Test 5: External Connections
echo "5. EXTERNAL CONNECTIONS TEST\n";
echo "----------------------------\n";

if (function_exists('curl_init')) {
    echo "cURL: AVAILABLE\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://httpbin.org/get');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($ch) {
        curl_close($ch);
    }
    
    if ($http_code === 200) {
        echo "External HTTP: WORKING\n";
    } else {
        echo "External HTTP: FAILED (Code: $http_code)\n";
    }
} else {
    echo "cURL: NOT AVAILABLE\n";
}

echo "\n=== TEST COMPLETE ===\n";
?>

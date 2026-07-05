<?php
// Error Diagnostic Tool for PK Live News
// This script helps identify and diagnose common issues

echo "<h1>PK Live News - Error Diagnostic Tool</h1>";

// 1. Check PHP Configuration
echo "<h2>1. PHP Configuration</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Error Reporting: " . (ini_get('display_errors') ? 'ON' : 'OFF') . "<br>";
echo "Error Log: " . ini_get('error_log') . "<br>";
echo "Memory Limit: " . ini_get('memory_limit') . "<br>";
echo "Max Execution Time: " . ini_get('max_execution_time') . "s<br>";

// 2. Check Database Connection
echo "<h2>2. Database Connection</h2>";
try {
    require_once 'config/database.php';
    if (isset($conn) && $conn) {
        echo "<span style='color: green;'>✓ Database connection successful</span><br>";
        
        // Check if articles table exists
        $result = mysqli_query($conn, "SHOW TABLES LIKE 'articles'");
        if (mysqli_num_rows($result) > 0) {
            echo "<span style='color: green;'>✓ Articles table exists</span><br>";
            
            // Check table structure
            $columns = mysqli_query($conn, "SHOW COLUMNS FROM articles");
            echo "<h3>Articles Table Structure:</h3>";
            echo "<table border='1'><tr><th>Column</th><th>Type</th><th>Null</th><th>Key</th></tr>";
            while ($row = mysqli_fetch_assoc($columns)) {
                echo "<tr><td>{$row['Field']}</td><td>{$row['Type']}</td><td>{$row['Null']}</td><td>{$row['Key']}</td></tr>";
            }
            echo "</table>";
        } else {
            echo "<span style='color: red;'>✗ Articles table not found</span><br>";
        }
    } else {
        echo "<span style='color: red;'>✗ Database connection failed</span><br>";
    }
} catch (Exception $e) {
    echo "<span style='color: red;'>✗ Database error: " . $e->getMessage() . "</span><br>";
}

// 3. Check Critical Files
echo "<h2>3. Critical Files Check</h2>";
$critical_files = [
    'config/database.php',
    'config/env.php',
    'config/settings.php',
    'index.php',
    'admin/index.php'
];

foreach ($critical_files as $file) {
    if (file_exists($file)) {
        echo "<span style='color: green;'>✓ $file exists</span><br>";
    } else {
        echo "<span style='color: red;'>✗ $file missing</span><br>";
    }
}

// 4. Check Directory Permissions
echo "<h2>4. Directory Permissions</h2>";
$directories = [
    'uploads/',
    'logs/',
    'cache/',
    'assets/images/'
];

foreach ($directories as $dir) {
    if (is_dir($dir)) {
        if (is_writable($dir)) {
            echo "<span style='color: green;'>✓ $dir is writable</span><br>";
        } else {
            echo "<span style='color: orange;'>⚠ $dir exists but not writable</span><br>";
        }
    } else {
        echo "<span style='color: red;'>✗ $dir not found</span><br>";
    }
}

// 5. Check Recent PHP Errors
echo "<h2>5. Recent PHP Errors (Last 10)</h2>";
$error_log = 'logs/php_errors.log';
if (file_exists($error_log)) {
    $errors = file($error_log);
    $recent_errors = array_slice($errors, -10);
    echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ccc;'>";
    foreach ($recent_errors as $error) {
        echo htmlspecialchars($error);
    }
    echo "</pre>";
} else {
    echo "<span style='color: orange;'>No error log found</span><br>";
}

// 6. Test RSS Feed Function
echo "<h2>6. RSS Feed Test</h2>";
$test_urls = [
    'https://arynews.tv/en/feed/',
    'http://feeds.bbci.co.uk/news/world/south_asia/rss.xml',
    'https://www.dawn.com/feed/rss/pakistan'
];

foreach ($test_urls as $url) {
    $context = stream_context_create([
        'http' => [
            'timeout' => 10,
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        ]
    ]);
    
    $content = @file_get_contents($url, false, $context);
    if ($content) {
        $xml = @simplexml_load_string($content);
        if ($xml !== false) {
            echo "<span style='color: green;'>✓ $url - Valid RSS</span><br>";
        } else {
            echo "<span style='color: orange;'>⚠ $url - Invalid XML format</span><br>";
        }
    } else {
        echo "<span style='color: red;'>✗ $url - Failed to fetch</span><br>";
    }
}

// 7. Environment Check
echo "<h2>7. Environment Variables</h2>";
if (file_exists('.env')) {
    echo "<span style='color: green;'>✓ .env file exists</span><br>";
    
    // Use multiple methods to load environment
    $env_loaded = false;
    
    // Method 1: Try parse_ini_file first (most reliable)
    $env_content = @parse_ini_file('.env');
    if ($env_content !== false) {
        echo "✓ Using parse_ini_file() method<br>";
        echo "DB_HOST: " . ($env_content['DB_HOST'] ?? 'Not set') . "<br>";
        echo "DB_NAME: " . ($env_content['DB_NAME'] ?? 'Not set') . "<br>";
        echo "SITE_URL: " . ($env_content['SITE_URL'] ?? 'Not set') . "<br>";
        echo "DB_USER: " . ($env_content['DB_USER'] ?? 'Not set') . "<br>";
        echo "DB_PASS: " . ($env_content['DB_PASS'] ?? 'Not set') . "<br>";
        $env_loaded = true;
    }
    
    // Method 2: Try original env loader
    if (!$env_loaded && file_exists('config/env.php')) {
        try {
            require_once 'config/env.php';
            EnvLoader::load();
            echo "✓ Using original EnvLoader method<br>";
            echo "DB_HOST: " . (getenv('DB_HOST') ?: 'Not set') . "<br>";
            echo "DB_NAME: " . (getenv('DB_NAME') ?: 'Not set') . "<br>";
            echo "SITE_URL: " . (getenv('SITE_URL') ?: 'Not set') . "<br>";
            echo "DB_USER: " . (getenv('DB_USER') ?: 'Not set') . "<br>";
            echo "DB_PASS: " . (getenv('DB_PASS') ?: 'Not set') . "<br>";
            $env_loaded = true;
        } catch (Exception $e) {
            echo "<span style='color: orange;'>⚠ EnvLoader failed: " . $e->getMessage() . "</span><br>";
        }
    }
    
    // Method 3: Manual parsing as last resort
    if (!$env_loaded) {
        echo "✓ Using manual parsing method<br>";
        $manual_env = [];
        if (file_exists('.env')) {
            $lines = file('.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line) || strpos($line, '#') === 0) continue;
                
                if (strpos($line, '=') !== false) {
                    list($key, $value) = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value);
                    
                    // Handle quoted values
                    if (($value[0] === '"' && $value[-1] === '"') || 
                        ($value[0] === "'" && $value[-1] === "'")) {
                        $value = substr($value, 1, -1);
                    }
                    
                    $manual_env[$key] = $value;
                }
            }
            
            echo "DB_HOST: " . ($manual_env['DB_HOST'] ?? 'Not set') . "<br>";
            echo "DB_NAME: " . ($manual_env['DB_NAME'] ?? 'Not set') . "<br>";
            echo "SITE_URL: " . ($manual_env['SITE_URL'] ?? 'Not set') . "<br>";
            echo "DB_USER: " . ($manual_env['DB_USER'] ?? 'Not set') . "<br>";
            echo "DB_PASS: " . ($manual_env['DB_PASS'] ?? 'Not set') . "<br>";
            $env_loaded = true;
        }
    }
    
    if (!$env_loaded) {
        echo "<span style='color: red;'>✗ All environment loading methods failed</span><br>";
    }
} else {
    echo "<span style='color: red;'>✗ .env file missing</span><br>";
}

echo "<h2>Diagnostic Complete</h2>";
echo "<p><small>Run this script regularly to monitor your site's health</small></p>";
?>

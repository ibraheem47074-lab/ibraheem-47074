<?php
// Hosting Configuration Test for PK Live News
// This file tests database connection and environment setup

echo "<!DOCTYPE html>
<html>
<head>
    <title>PK Live News - Hosting Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .info { color: blue; }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 3px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>PK Live News - Hosting Configuration Test</h1>
    
    <div class='test-section'>
        <h2>Environment Configuration</h2>";

// Test environment loading
try {
    require_once 'config/env.php';
    echo "<p class='success'>Environment loader loaded successfully</p>";
    
    // Check if .env file exists
    if (file_exists('.env')) {
        echo "<p class='success'>.env file found</p>";
    } else {
        echo "<p class='error'>.env file not found</p>";
    }
    
    // Display environment variables (without sensitive data)
    echo "<h3>Environment Variables:</h3>";
    echo "<pre>";
    echo "DB_HOST: " . (defined('DB_HOST') ? DB_HOST : 'Not defined') . "\n";
    echo "DB_USER: " . (defined('DB_USER') ? DB_USER : 'Not defined') . "\n";
    echo "DB_NAME: " . (defined('DB_NAME') ? DB_NAME : 'Not defined') . "\n";
    echo "SITE_URL: " . (defined('SITE_URL') ? SITE_URL : 'Not defined') . "\n";
    echo "APP_ENV: " . (defined('APP_ENV') ? APP_ENV : 'Not defined') . "\n";
    echo "</pre>";
    
} catch (Exception $e) {
    echo "<p class='error'>Environment loading failed: " . $e->getMessage() . "</p>";
}

echo "</div>";

echo "<div class='test-section'>
    <h2>Database Connection Test</h2>";

// Test database connection
try {
    require_once 'config/database.php';
    echo "<p class='success'>Database configuration loaded</p>";
    
    if (isset($conn) && $conn instanceof mysqli) {
        if ($conn->query("SELECT 1")) {
            echo "<p class='success'>Database connection successful!</p>";
            
            // Get database info
            $server_info = $conn->server_info;
            $host_info = $conn->host_info;
            
            echo "<h3>Connection Details:</h3>";
            echo "<pre>";
            echo "MySQL Server Info: " . $server_info . "\n";
            echo "Host Info: " . $host_info . "\n";
            echo "Database Name: " . DB_NAME . "\n";
            echo "Connected User: " . DB_USER . "\n";
            echo "</pre>";
            
            // Test basic query
            $result = $conn->query("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = '" . DB_NAME . "'");
            if ($result) {
                $row = $result->fetch_assoc();
                echo "<p class='info'>Database contains " . $row['count'] . " tables</p>";
            }
            
        } else {
            echo "<p class='error'>Database connection failed: Unable to ping server</p>";
        }
    } else {
        echo "<p class='error'>Database connection not established</p>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>Database connection failed: " . $e->getMessage() . "</p>";
}

echo "</div>";

echo "<div class='test-section'>
    <h2>File System Test</h2>";

// Test file permissions
$test_dirs = ['uploads', 'logs', 'cache'];
foreach ($test_dirs as $dir) {
    if (is_dir($dir)) {
        if (is_writable($dir)) {
            echo "<p class='success'>$dir directory is writable</p>";
        } else {
            echo "<p class='error'>$dir directory is not writable</p>";
        }
    } else {
        echo "<p class='info'>$dir directory does not exist</p>";
    }
}

// Test .htaccess
if (file_exists('.htaccess')) {
    echo "<p class='success'>.htaccess file found</p>";
} else {
    echo "<p class='error'>.htaccess file not found</p>";
}

echo "</div>";

echo "<div class='test-section'>
    <h2>PHP Configuration</h2>";

echo "<h3>PHP Version and Settings:</h3>";
echo "<pre>";
echo "PHP Version: " . phpversion() . "\n";
echo "Memory Limit: " . ini_get('memory_limit') . "\n";
echo "Max Execution Time: " . ini_get('max_execution_time') . "\n";
echo "Upload Max Filesize: " . ini_get('upload_max_filesize') . "\n";
echo "Post Max Size: " . ini_get('post_max_size') . "\n";
echo "Max Input Vars: " . ini_get('max_input_vars') . "\n";
echo "Display Errors: " . (ini_get('display_errors') ? 'On' : 'Off') . "\n";
echo "Error Reporting: " . ini_get('error_reporting') . "\n";
echo "</pre>";

echo "</div>";

echo "<div class='test-section'>
    <h2>Server Information</h2>";

echo "<h3>Server Details:</h3>";
echo "<pre>";
echo "Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "\n";
echo "Server Name: " . $_SERVER['SERVER_NAME'] . "\n";
echo "Server Port: " . $_SERVER['SERVER_PORT'] . "\n";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "Script Name: " . $_SERVER['SCRIPT_NAME'] . "\n";
echo "Request URI: " . $_SERVER['REQUEST_URI'] . "\n";
echo "HTTPS: " . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'On' : 'Off') . "\n";
echo "</pre>";

echo "</div>";

echo "<div class='test-section'>
    <h2>Recommendations</h2>";
echo "<ul>";
echo "<li>Delete this file after testing is complete for security</li>";
echo "<li>Ensure all directories have proper permissions (755 for directories, 644 for files)</li>";
echo "<li>Set up SSL certificate for HTTPS</li>";
echo "<li>Configure email settings in .env file</li>";
echo "<li>Set up regular backups</li>";
echo "</ul>";
echo "</div>";

echo "</body></html>";
?>

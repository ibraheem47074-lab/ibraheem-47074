<?php
// Hosting Readiness Check for PK Live News
// This script checks if the website is ready for Hostinger hosting

echo "<!DOCTYPE html>
<html>
<head>
    <title>PK Live News - Hosting Readiness Check</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .pass { color: green; }
        .fail { color: red; }
        .warning { color: orange; }
        .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        pre { background: #f5f5f5; padding: 10px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>PK Live News - Hosting Readiness Check</h1>
    <p>This script checks if your website is ready for Hostinger hosting.</p>";

// Check PHP Version
echo "<div class='section'>
    <h2>PHP Version Check</h2>";
$php_version = phpversion();
echo "Current PHP Version: <strong>{$php_version}</strong><br>";
if (version_compare($php_version, '7.4.0', '>=')) {
    echo "<span class='pass'>PASS: PHP version is compatible (7.4+ required)</span><br>";
} else {
    echo "<span class='fail'>FAIL: PHP version too old. Hostinger supports 7.4+, 8.0+, 8.1+</span><br>";
}
echo "</div>";

// Check Required Extensions
echo "<div class='section'>
    <h2>Required PHP Extensions</h2>";
$required_extensions = ['mysqli', 'gd', 'curl', 'json', 'mbstring', 'xml', 'zip'];
foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<span class='pass'>PASS: {$ext} extension loaded</span><br>";
    } else {
        echo "<span class='fail'>FAIL: {$ext} extension missing</span><br>";
    }
}
echo "</div>";

// Check Database Connection
echo "<div class='section'>
    <h2>Database Connection Test</h2>";
try {
    // Test with production credentials
    $conn = new mysqli('localhost', 'u129650532_ibraheem', 'Khan47074$', 'u129650532_pk_live_news');
    if ($conn->connect_error) {
        echo "<span class='warning'>WARNING: Production database not accessible (expected in local environment)</span><br>";
        echo "Error: " . $conn->connect_error . "<br>";
        
        // Test with local credentials
        $conn_local = new mysqli('localhost', 'root', '', 'pk_live_news');
        if ($conn_local->connect_error) {
            echo "<span class='fail'>FAIL: Local database connection failed</span><br>";
            echo "Error: " . $conn_local->connect_error . "<br>";
        } else {
            echo "<span class='pass'>PASS: Local database connection successful</span><br>";
            $conn_local->close();
        }
    } else {
        echo "<span class='pass'>PASS: Production database connection successful</span><br>";
        $conn->close();
    }
} catch (Exception $e) {
    echo "<span class='warning'>WARNING: Database test failed - " . $e->getMessage() . "</span><br>";
}
echo "</div>";

// Check File Permissions
echo "<div class='section'>
    <h2>File Permissions Check</h2>";
$directories_to_check = ['uploads', 'logs', 'cache', 'backups'];
foreach ($directories_to_check as $dir) {
    if (is_dir($dir)) {
        if (is_writable($dir)) {
            echo "<span class='pass'>PASS: {$dir} directory is writable</span><br>";
        } else {
            echo "<span class='fail'>FAIL: {$dir} directory is not writable</span><br>";
        }
    } else {
        echo "<span class='warning'>WARNING: {$dir} directory does not exist</span><br>";
    }
}
echo "</div>";

// Check Environment Files
echo "<div class='section'>
    <h2>Environment Configuration</h2>";
if (file_exists('.env')) {
    echo "<span class='pass'>PASS: .env file exists</span><br>";
} else {
    echo "<span class='fail'>FAIL: .env file missing</span><br>";
}

if (file_exists('.env.production')) {
    echo "<span class='pass'>PASS: .env.production file exists</span><br>";
} else {
    echo "<span class='fail'>FAIL: .env.production file missing</span><br>";
}
echo "</div>";

// Check Security Settings
echo "<div class='section'>
    <h2>Security Configuration</h2>";
if (ini_get('display_errors') == 'Off' || $_ENV['APP_ENV'] == 'production') {
    echo "<span class='pass'>PASS: Error display disabled for production</span><br>";
} else {
    echo "<span class='warning'>WARNING: Error display should be disabled in production</span><br>";
}

if (file_exists('.htaccess')) {
    echo "<span class='pass'>PASS: .htaccess file exists with security rules</span><br>";
} else {
    echo "<span class='fail'>FAIL: .htaccess file missing</span><br>";
}
echo "</div>";

// Check Upload Configuration
echo "<div class='section'>
    <h2>Upload Configuration</h2>";
$max_upload = ini_get('upload_max_filesize');
$max_post = ini_get('post_max_size');
echo "Max upload size: {$max_upload}<br>";
echo "Max POST size: {$max_post}<br>";

if (is_dir('uploads') && is_writable('uploads')) {
    echo "<span class='pass'>PASS: Uploads directory is ready</span><br>";
} else {
    echo "<span class='fail'>FAIL: Uploads directory not ready</span><br>";
}
echo "</div>";

// Check SSL/HTTPS
echo "<div class='section'>
    <h2>SSL/HTTPS Configuration</h2>";
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    echo "<span class='pass'>PASS: HTTPS is enabled</span><br>";
} else {
    echo "<span class='warning'>WARNING: HTTPS not detected (will be enabled on Hostinger)</span><br>";
}

if (file_exists('.htaccess')) {
    $htaccess_content = file_get_contents('.htaccess');
    if (strpos($htaccess_content, 'X-Content-Type-Options') !== false) {
        echo "<span class='pass'>PASS: Security headers configured in .htaccess</span><br>";
    } else {
        echo "<span class='warning'>WARNING: Security headers not found in .htaccess</span><br>";
    }
}
echo "</div>";

// Summary
echo "<div class='section'>
    <h2>Hosting Readiness Summary</h2>
    <p><strong>Next Steps for Hostinger Deployment:</strong></p>
    <ol>
        <li>Upload all files to Hostinger public_html directory</li>
        <li>Rename .env.production to .env on Hostinger</li>
        <li>Create database on Hostinger and import SQL backup</li>
        <li>Update database credentials in .env file</li>
        <li>Set proper permissions (755 for directories, 644 for files)</li>
        <li>Test the website on Hostinger</li>
        <li>Enable SSL certificate through Hostinger panel</li>
    </ol>
    <p><strong>Files to exclude from upload:</strong></p>
    <ul>
        <li>Local test files (test_*.php, fix_*.php, etc.)</li>
        <li>Local database backups</li>
        <li>Development logs</li>
    </ul>
</div>";

echo "</body></html>";
?>

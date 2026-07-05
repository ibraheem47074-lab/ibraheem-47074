<?php
// Comprehensive System Health Check
require_once 'config/database.php';

echo "<pre>";
echo "=== PK Live News System Health Check ===\n\n";

// 1. Database Connection Test
echo "1. DATABASE CONNECTION\n";
echo "Status: " . ($conn ? "✓ Connected" : "✗ Failed") . "\n";
echo "Database: " . DB_NAME . "\n";
echo "Host: " . DB_HOST . "\n\n";

// 2. Core Tables Check
echo "2. CORE TABLES CHECK\n";
$required_tables = ['users', 'articles', 'news', 'categories', 'polls', 'affiliate_products', 'comments'];
foreach ($required_tables as $table) {
    $result = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
    $status = mysqli_num_rows($result) > 0 ? "✓" : "✗";
    echo "$status $table\n";
}
echo "\n";

// 3. Required Columns Check
echo "3. REQUIRED COLUMNS CHECK\n";

// Check users table for image column
$result = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'image'");
$status = mysqli_num_rows($result) > 0 ? "✓" : "✗";
echo "$status users.image\n";

// Check articles table for new columns
$articles_columns = ['image_type', 'source_name'];
foreach ($articles_columns as $column) {
    $result = mysqli_query($conn, "SHOW COLUMNS FROM articles LIKE '$column'");
    $status = mysqli_num_rows($result) > 0 ? "✓" : "✗";
    echo "$status articles.$column\n";
}

// Check polls table for user_id column
$result = mysqli_query($conn, "SHOW COLUMNS FROM polls LIKE 'user_id'");
$status = mysqli_num_rows($result) > 0 ? "✓" : "✗";
echo "$status polls.user_id\n";
echo "\n";

// 4. PHP Configuration Check
echo "4. PHP CONFIGURATION\n";
echo "PHP Version: " . phpversion() . "\n";
echo "Memory Limit: " . ini_get('memory_limit') . "\n";
echo "Max Execution Time: " . ini_get('max_execution_time') . "s\n";
echo "Upload Max Filesize: " . ini_get('upload_max_filesize') . "\n";
echo "Post Max Size: " . ini_get('post_max_size') . "\n";

// Check required extensions
$required_extensions = ['mysqli', 'gd', 'curl', 'json', 'mbstring'];
foreach ($required_extensions as $ext) {
    $status = extension_loaded($ext) ? "✓" : "✗";
    echo "$status $ext extension\n";
}
echo "\n";

// 5. File Permissions Check
echo "5. FILE PERMISSIONS\n";
$directories = ['uploads/', 'uploads/news/', 'uploads/avatars/', 'uploads/categories/', 'logs/'];
foreach ($directories as $dir) {
    $path = __DIR__ . '/' . $dir;
    if (is_dir($path)) {
        $writable = is_writable($path) ? "✓" : "✗";
        echo "$writable $dir (writable)\n";
    } else {
        echo "! $dir (missing)\n";
    }
}
echo "\n";

// 6. Critical Files Check
echo "6. CRITICAL FILES CHECK\n";
$critical_files = [
    'config/database.php',
    'includes/header.php',
    'index.php',
    'admin/add-news.php',
    'api/submit-comment.php'
];

foreach ($critical_files as $file) {
    $path = __DIR__ . '/' . $file;
    $status = file_exists($path) ? "✓" : "✗";
    echo "$status $file\n";
}
echo "\n";

// 7. Security Check
echo "7. SECURITY CHECK\n";

// Check if error display is off in production
$error_display = ini_get('display_errors');
$security_status = ($error_display == 'Off' || APP_ENV == 'development') ? "✓" : "⚠";
echo "$security_status Error display settings\n";

// Check for secure session settings
$session_secure = ini_get('session.cookie_secure');
$session_httponly = ini_get('session.cookie_httponly');
echo "$security_status Session security (secure: $session_secure, httponly: $session_httponly)\n\n";

// 8. Recent Error Log Check
echo "8. RECENT ERRORS CHECK\n";
$error_log = __DIR__ . '/logs/php_errors.log';
if (file_exists($error_log)) {
    $lines = file($error_log);
    $recent_lines = array_slice($lines, -10); // Last 10 lines
    if (!empty($recent_lines)) {
        echo "Last 10 error entries:\n";
        foreach ($recent_lines as $line) {
            echo trim($line) . "\n";
        }
    } else {
        echo "✓ No recent errors found\n";
    }
} else {
    echo "✓ No error log file\n";
}
echo "\n";

// 9. Test Database Queries
echo "9. DATABASE QUERY TESTS\n";

// Test basic queries
try {
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM users");
    $row = mysqli_fetch_assoc($result);
    echo "✓ Users table query: {$row['count']} users\n";
} catch (Exception $e) {
    echo "✗ Users table query failed: " . $e->getMessage() . "\n";
}

try {
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM articles");
    $row = mysqli_fetch_assoc($result);
    echo "✓ Articles table query: {$row['count']} articles\n";
} catch (Exception $e) {
    echo "✗ Articles table query failed: " . $e->getMessage() . "\n";
}

try {
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM news");
    $row = mysqli_fetch_assoc($result);
    echo "✓ News table query: {$row['count']} news items\n";
} catch (Exception $e) {
    echo "✗ News table query failed: " . $e->getMessage() . "\n";
}

echo "\n";

// 10. API Endpoints Check
echo "10. API ENDPOINTS CHECK\n";
$api_endpoints = [
    'api/breaking-news.php',
    'api/submit-comment.php',
    'api/load-news.php',
    'api/get-comments.php'
];

foreach ($api_endpoints as $endpoint) {
    $path = __DIR__ . '/' . $endpoint;
    $status = file_exists($path) ? "✓" : "✗";
    echo "$status $endpoint\n";
}
echo "\n";

echo "=== Health Check Complete ===\n";
echo "</pre>";
?>

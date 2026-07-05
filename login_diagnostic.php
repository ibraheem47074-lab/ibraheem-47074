<?php
// Comprehensive Login Diagnostic
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>PK Live News - Login Diagnostic Report</h1>";
echo "<h2>Generated: " . date('Y-m-d H:i:s') . "</h2>";

// 1. Server Configuration
echo "<h2>1. Server Configuration</h2>";
echo "PHP Version: " . PHP_VERSION . "<br>";
echo "Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "<br>";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "Current URL: " . $_SERVER['REQUEST_URI'] . "<br>";

// 2. Required Extensions
echo "<h2>2. PHP Extensions</h2>";
$required_extensions = ['mysqli', 'session', 'json'];
foreach ($required_extensions as $ext) {
    $status = extension_loaded($ext) ? '<span style="color: green;">Available</span>' : '<span style="color: red;">Missing</span>';
    echo "$ext: $status<br>";
}

// 3. File Structure Check
echo "<h2>3. File Structure Check</h2>";
$files_to_check = [
    'admin/login.php',
    'config/database.php',
    'config/env.php',
    'config/settings.php',
    '.env'
];

foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        $size = filesize($file);
        echo "<span style='color: green;'>$file exists ($size bytes)</span><br>";
    } else {
        echo "<span style='color: red;'>$file MISSING</span><br>";
    }
}

// 4. Database Connection Test
echo "<h2>4. Database Connection Test</h2>";
try {
    require_once 'config/env.php';
    
    echo "Database Host: " . DB_HOST . "<br>";
    echo "Database User: " . DB_USER . "<br>";
    echo "Database Name: " . DB_NAME . "<br>";
    
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if (!$conn) {
        echo "<span style='color: red;'>Connection Failed: " . mysqli_connect_error() . "</span><br>";
        echo "Error Code: " . mysqli_connect_errno() . "<br>";
    } else {
        echo "<span style='color: green;'>Connection Successful!</span><br>";
        
        // Check if users table exists
        $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'users'");
        if (mysqli_num_rows($table_check) > 0) {
            echo "<span style='color: green;'>Users table exists</span><br>";
            
            // Count active users
            $count_query = "SELECT COUNT(*) as count FROM users WHERE status = 'active'";
            $result = mysqli_query($conn, $count_query);
            $count = mysqli_fetch_assoc($result);
            echo "Active users: " . $count['count'] . "<br>";
            
            // Show user roles
            $roles_query = "SELECT role, COUNT(*) as count FROM users GROUP BY role";
            $roles_result = mysqli_query($conn, $roles_query);
            echo "User roles:<br>";
            while ($role = mysqli_fetch_assoc($roles_result)) {
                echo "- " . $role['role'] . ": " . $role['count'] . "<br>";
            }
        } else {
            echo "<span style='color: red;'>Users table does not exist!</span><br>";
        }
        
        mysqli_close($conn);
    }
} catch (Exception $e) {
    echo "<span style='color: red;'>Database Error: " . $e->getMessage() . "</span><br>";
}

// 5. Session Configuration
echo "<h2>5. Session Configuration</h2>";
echo "Session Status: " . session_status() . "<br>";
echo "Session ID: " . session_id() . "<br>";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    echo "Session started<br>";
}

// 6. Login File Syntax Check
echo "<h2>6. Login File Analysis</h2>";
if (file_exists('admin/login.php')) {
    $login_content = file_get_contents('admin/login.php');
    
    // Check for required functions
    $required_functions = ['is_logged_in', 'is_admin', 'is_editor', 'is_reporter', 'redirect', 'clean_input'];
    foreach ($required_functions as $func) {
        if (strpos($login_content, $func) !== false) {
            echo "<span style='color: green;'>Function '$func' found</span><br>";
        } else {
            echo "<span style='color: red;'>Function '$func' NOT found</span><br>";
        }
    }
    
    // Check for security issues
    if (strpos($login_content, 'password_verify') !== false) {
        echo "<span style='color: green;'>Password verification found</span><br>";
    } else {
        echo "<span style='color: red;'>Password verification NOT found</span><br>";
    }
    
    if (strpos($login_content, 'mysqli_prepare') !== false) {
        echo "<span style='color: green;'>Prepared statements found</span><br>";
    } else {
        echo "<span style='color: red;'>Prepared statements NOT found</span><br>";
    }
}

// 7. URL Rewrite Check
echo "<h2>7. URL Rewrite Check</h2>";
if (file_exists('.htaccess')) {
    echo "<span style='color: green;'>.htaccess file exists</span><br>";
    $htaccess_content = file_get_contents('.htaccess');
    echo ".htaccess size: " . strlen($htaccess_content) . " bytes<br>";
} else {
    echo "<span style='color: red;'>.htaccess file missing</span><br>";
}

// 8. Permissions Check
echo "<h2>8. Directory Permissions</h2>";
$dirs_to_check = ['admin/', 'config/', 'uploads/', 'cache/'];
foreach ($dirs_to_check as $dir) {
    if (is_dir($dir)) {
        $writable = is_writable($dir) ? '<span style="color: green;">Writable</span>' : '<span style="color: red;">Not Writable</span>';
        echo "$dir: $writable<br>";
    } else {
        echo "<span style='color: red;'>$dir does not exist</span><br>";
    }
}

echo "<h2>9. Recommendations</h2>";
echo "<ol>";
echo "<li>Ensure XAMPP Apache and MySQL services are running</li>";
echo "<li>Check database credentials in .env file</li>";
echo "<li>Verify users table exists in database</li>";
echo "<li>Ensure proper file permissions</li>";
echo "<li>Check for syntax errors in PHP files</li>";
echo "</ol>";

echo "<h2>10. Next Steps</h2>";
echo "<p>If all checks pass but login still doesn't work:</p>";
echo "<ul>";
echo "<li>Check Apache error logs</li>";
echo "<li>Test database connection manually</li>";
echo "<li>Verify session configuration</li>";
echo "<li>Check for conflicting .htaccess rules</li>";
echo "</ul>";
?>

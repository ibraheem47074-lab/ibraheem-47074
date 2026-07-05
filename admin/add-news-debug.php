<?php
// PK Live News - Debug Add News Page
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Debug Add News - PK Live News</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css' rel='stylesheet'>
    <style>
        body { background: #f8f9fa; padding: 20px; }
        .debug-container { max-width: 1200px; margin: 0 auto; }
        .card { border: none; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .error { color: #dc3545; font-weight: bold; }
        .success { color: #28a745; font-weight: bold; }
        .warning { color: #ffc107; font-weight: bold; }
        .code-block { background: #000; color: #00ff00; padding: 15px; border-radius: 5px; font-family: monospace; }
    </style>
</head>
<body>
    <div class='debug-container'>
        <div class='card'>
            <div class='card-header bg-danger text-white'>
                <h3 class='mb-0'>Debug: Add News Page Issues</h3>
            </div>
            <div class='card-body'>";

echo "<h4>Step 1: Basic PHP Check</h4>";
echo "<p>PHP Version: " . PHP_VERSION . "</p>";
echo "<p>Memory Limit: " . ini_get('memory_limit') . "</p>";
echo "<p>Max Execution Time: " . ini_get('max_execution_time') . "s</p>";
echo "<p>Display Errors: " . (ini_get('display_errors') ? 'ON' : 'OFF') . "</p>";

echo "<h4>Step 2: Required Files Check</h4>";

$required_files = [
    '../config/database.php' => 'Database Config',
    '../includes/admin-header.php' => 'Admin Header',
    '../includes/admin-footer.php' => 'Admin Footer'
];

foreach ($required_files as $file => $description) {
    if (file_exists($file)) {
        $readable = is_readable($file);
        echo "<p class='success'>$description: " . ($readable ? 'Readable' : 'Not readable') . "</p>";
    } else {
        echo "<p class='error'>$description: File not found</p>";
    }
}

echo "<h4>Step 3: Database Connection Test</h4>";

try {
    require_once '../config/database.php';
    echo "<p class='success'>Database connection: OK</p>";
    
    // Test categories table
    $cat_query = "SELECT COUNT(*) as count FROM categories";
    $cat_result = mysqli_query($conn, $cat_query);
    $cat_count = mysqli_fetch_assoc($cat_result)['count'];
    echo "<p class='success'>Categories in database: $cat_count</p>";
    
    // Test news table structure
    $news_columns = mysqli_query($conn, "DESCRIBE news");
    echo "<details><summary>News Table Structure</summary>";
    echo "<table class='table table-sm'><thead><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr></thead><tbody>";
    while ($column = mysqli_fetch_assoc($news_columns)) {
        echo "<tr><td>{$column['Field']}</td><td>{$column['Type']}</td><td>{$column['Null']}</td><td>{$column['Key']}</td></tr>";
    }
    echo "</tbody></table></details>";
    
} catch (Exception $e) {
    echo "<p class='error'>Database connection failed: " . $e->getMessage() . "</p>";
    echo "<div class='code-block'>" . $e->getTraceAsString() . "</div>";
}

echo "<h4>Step 4: Session and Authentication Check</h4>";

// Check if session is already active
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "<p class='success'>Session: Already active</p>";
} else {
    echo "<p class='warning'>Session: Not active</p>";
    session_start();
}

if (!function_exists('is_logged_in')) {
    echo "<p class='error'>is_logged_in() function not found</p>";
} else {
    echo "<p class='success'>is_logged_in() function: Available</p>";
    $logged_in = is_logged_in();
    echo "<p>User logged in: " . ($logged_in ? 'Yes' : 'No') . "</p>";
}

if (!function_exists('is_admin')) {
    echo "<p class='error'>is_admin() function not found</p>";
} else {
    echo "<p class='success'>is_admin() function: Available</p>";
    $is_admin = is_admin();
    echo "<p>User is admin: " . ($is_admin ? 'Yes' : 'No') . "</p>";
}

if (!function_exists('is_editor')) {
    echo "<p class='error'>is_editor() function not found</p>";
} else {
    echo "<p class='success'>is_editor() function: Available</p>";
    $is_editor = is_editor();
    echo "<p>User is editor: " . ($is_editor ? 'Yes' : 'No') . "</p>";
}

echo "<h4>Step 5: Required Functions Check</h4>";

$required_functions = ['clean_input', 'redirect', 'mysqli_prepare', 'mysqli_stmt_bind_param'];

foreach ($required_functions as $func) {
    if (function_exists($func)) {
        echo "<p class='success'>$func: Available</p>";
    } else {
        echo "<p class='error'>$func: Missing</p>";
    }
}

echo "<h4>Step 6: Upload Directories Check</h4>";

$upload_dirs = [
    '../uploads/news/' => 'News Images',
    '../uploads/news/videos/' => 'News Videos'
];

foreach ($upload_dirs as $dir => $description) {
    if (is_dir($dir)) {
        $writable = is_writable($dir);
        echo "<p class='success'>$description: " . ($writable ? 'Writable' : 'Not writable') . "</p>";
    } else {
        echo "<p class='warning'>$description: Directory missing (will be created)</p>";
        if (mkdir($dir, 0755, true)) {
            echo "<p class='success'>$description: Created successfully</p>";
        }
    }
}

echo "<h4>Step 7: PHP Error Log Check</h4>";

$error_log = ini_get('error_log');
echo "<p>PHP Error Log: $error_log</p>";

if (file_exists('../logs/php_errors.log')) {
    $log_content = file_get_contents('../logs/php_errors.log');
    $recent_errors = array_slice(explode("\n", $log_content), -10);
    echo "<details><summary>Recent PHP Errors (Last 10)</summary>";
    echo "<div class='code-block'>" . implode("\n", array_map('htmlspecialchars', $recent_errors)) . "</div>";
    echo "</details>";
} else {
    echo "<p class='warning'>No PHP error log found</p>";
}

echo "<h4>Step 8: Try Loading Original Add News</h4>";

echo "<div class='alert alert-info'>
    <h5>Testing Original Add News Page:</h5>
    <p>Click below to try loading the original add-news.php with error reporting enabled:</p>
    <a href='add-news.php?debug=1' class='btn btn-primary' target='_blank'>
        <i class='fas fa-bug me-2'></i>Test Original Add News
    </a>
</div>";

echo "<h4>Step 9: Common Issues & Solutions</h4>";

echo "<div class='row'>
    <div class='col-md-6'>
        <div class='card'>
            <div class='card-header bg-warning text-dark'>
                <h6>Common Issue: White Screen</h6>
            </div>
            <div class='card-body'>
                <ul>
                    <li><strong>Syntax Error:</strong> Missing semicolon, bracket mismatch</li>
                    <li><strong>Fatal Error:</strong> Calling undefined function</li>
                    <li><strong>Memory Limit:</strong> Script exceeds memory limit</li>
                    <li><strong>Include Error:</strong> Missing required files</li>
                </ul>
            </div>
        </div>
    </div>
    <div class='col-md-6'>
        <div class='card'>
            <div class='card-header bg-info text-white'>
                <h6>Quick Fixes</h6>
            </div>
            <div class='card-body'>
                <ul>
                    <li>Check PHP error logs</li>
                    <li>Verify file permissions</li>
                    <li>Test with error reporting ON</li>
                    <li>Check database connection</li>
                    <li>Verify all includes exist</li>
                </ul>
            </div>
        </div>
    </div>
</div>";

echo "<div class='text-center mt-4'>
    <a href='add-news.php' class='btn btn-primary me-2'>
        <i class='fas fa-arrow-left me-2'></i>Try Add News Again
    </a>
    <a href='manage-news-test-guide.php' class='btn btn-secondary'>
        <i class='fas fa-clipboard-list me-2'></i>Test Guide
    </a>
</div>";

echo "
            </div>
        </div>
    </div>
    
    <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>
</body>
</html>";
?>

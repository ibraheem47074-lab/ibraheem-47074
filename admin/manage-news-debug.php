<?php
// PK Live News - Debug Manage News Page
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Debug Manage News - PK Live News</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <style>
        body { background: #f8f9fa; padding: 20px; }
        .debug-container { max-width: 1200px; margin: 0 auto; }
        .card { border: none; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .error { color: #dc3545; }
        .success { color: #28a745; }
        .warning { color: #ffc107; }
        .code-block { background: #f8f9fa; padding: 15px; border-radius: 5px; font-family: monospace; }
    </style>
</head>
<body>
    <div class='debug-container'>
        <div class='card'>
            <div class='card-header bg-warning text-dark'>
                <h3 class='mb-0'>Debug: Manage News Page Issues</h3>
            </div>
            <div class='card-body'>";

echo "<h4>Step 1: Database Connection Test</h4>";

try {
    require_once '../config/database.php';
    echo "<p class='success'>Database connection: OK</p>";
    
    // Test basic query
    $test_query = "SELECT COUNT(*) as count FROM news";
    $result = mysqli_query($conn, $test_query);
    $count = mysqli_fetch_assoc($result)['count'];
    echo "<p class='success'>News articles in database: $count</p>";
    
} catch (Exception $e) {
    echo "<p class='error'>Database connection failed: " . $e->getMessage() . "</p>";
    echo "<div class='code-block'>" . $e->getTraceAsString() . "</div>";
}

echo "<h4>Step 2: Session and Authentication Check</h4>";

// Only start session if not already active
if (session_status() === PHP_SESSION_NONE) {
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

echo "<h4>Step 3: Required Functions Check</h4>";

$required_functions = ['clean_input', 'redirect', 'mysqli_prepare', 'mysqli_stmt_bind_param', 'mysqli_stmt_execute'];

foreach ($required_functions as $func) {
    if (function_exists($func)) {
        echo "<p class='success'>$func: Available</p>";
    } else {
        echo "<p class='error'>$func: Missing</p>";
    }
}

echo "<h4>Step 4: Database Tables Check</h4>";

$tables_to_check = ['news', 'categories', 'users', 'comments'];

foreach ($tables_to_check as $table) {
    $check_query = "SHOW TABLES LIKE '$table'";
    $result = mysqli_query($conn, $check_query);
    if (mysqli_num_rows($result) > 0) {
        echo "<p class='success'>Table $table: Exists</p>";
        
        // Check table structure
        $desc_query = "DESCRIBE $table";
        $desc_result = mysqli_query($conn, $desc_query);
        echo "<details><summary>Structure of $table</summary>";
        echo "<table class='table table-sm'><thead><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr></thead><tbody>";
        while ($row = mysqli_fetch_assoc($desc_result)) {
            echo "<tr><td>{$row['Field']}</td><td>{$row['Type']}</td><td>{$row['Null']}</td><td>{$row['Key']}</td></tr>";
        }
        echo "</tbody></table></details>";
    } else {
        echo "<p class='error'>Table $table: Missing</p>";
    }
}

echo "<h4>Step 5: Sample News Query Test</h4>";

try {
    $sample_query = "SELECT n.*, c.name as category_name, u.name as author_name 
                     FROM news n 
                     LEFT JOIN categories c ON n.category_id = c.id 
                     LEFT JOIN users u ON n.author_id = u.id 
                     ORDER BY n.created_at DESC 
                     LIMIT 5";
    
    $result = mysqli_query($conn, $sample_query);
    
    if ($result) {
        $count = mysqli_num_rows($result);
        echo "<p class='success'>Sample query executed: $count rows returned</p>";
        
        if ($count > 0) {
            echo "<table class='table table-sm'><thead><tr><th>ID</th><th>Title</th><th>Category</th><th>Author</th><th>Status</th></tr></thead><tbody>";
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr>
                    <td>{$row['id']}</td>
                    <td>" . htmlspecialchars(substr($row['title'], 0, 50)) . "...</td>
                    <td>" . htmlspecialchars($row['category_name'] ?? 'N/A') . "</td>
                    <td>" . htmlspecialchars($row['author_name'] ?? 'N/A') . "</td>
                    <td>" . htmlspecialchars($row['status'] ?? 'N/A') . "</td>
                </tr>";
            }
            echo "</tbody></table>";
        }
    } else {
        echo "<p class='error'>Sample query failed: " . mysqli_error($conn) . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>Sample query error: " . $e->getMessage() . "</p>";
}

echo "<h4>Step 6: File Permissions Check</h4>";

$files_to_check = [
    '../config/database.php' => 'Database config',
    '../includes/admin-header.php' => 'Admin header',
    '../includes/admin-footer.php' => 'Admin footer'
];

foreach ($files_to_check as $file => $description) {
    if (file_exists($file)) {
        $readable = is_readable($file);
        echo "<p class='" . ($readable ? 'success' : 'error') . "'>$description: " . ($readable ? 'Readable' : 'Not readable') . "</p>";
    } else {
        echo "<p class='error'>$description: File not found</p>";
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

echo "<h4>Step 8: Fix Recommendations</h4>";

echo "<div class='alert alert-info'>
    <h5>Based on the debug results:</h5>
    <ul>
        <li>If database connection fails, check config/database.php</li>
        <li>If authentication functions missing, check includes files</li>
        <li>If tables missing, run database setup scripts</li>
        <li>If permissions issue, check file ownership</li>
    </ul>
</div>";

echo "<div class='text-center mt-4'>
    <a href='manage-news.php' class='btn btn-primary'>Try Manage News Again</a>
    <a href='../index.php' class='btn btn-secondary'>Go to Homepage</a>
</div>";

echo "
            </div>
        </div>
    </div>
    
    <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>
</body>
</html>";
?>

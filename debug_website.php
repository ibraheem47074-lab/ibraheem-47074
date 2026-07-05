<?php
// Simple diagnostic script
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>PK Live News - Website Diagnostic</h1>";

// Check database connection
echo "<h2>Database Connection</h2>";
try {
    require_once 'config/database.php';
    if (isset($conn) && $conn) {
        echo "<p style='color: green;'>✓ Database connection successful</p>";
        
        // Check if news table exists and has data
        $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'news'");
        if (mysqli_num_rows($table_check) > 0) {
            echo "<p style='color: green;'>✓ News table exists</p>";
            
            $count_query = "SELECT COUNT(*) as total FROM news";
            $count_result = mysqli_query($conn, $count_query);
            $total_news = mysqli_fetch_assoc($count_result)['total'];
            echo "<p style='color: green;'>✓ News table has $total_news articles</p>";
            
            // Check for published news
            $published_query = "SELECT COUNT(*) as total FROM news WHERE status = 'published'";
            $published_result = mysqli_query($conn, $published_query);
            $published_news = mysqli_fetch_assoc($published_result)['total'];
            echo "<p style='color: green;'>✓ $published_news published articles</p>";
            
        } else {
            echo "<p style='color: red;'>✗ News table not found</p>";
        }
        
        // Check categories table
        $cat_check = mysqli_query($conn, "SHOW TABLES LIKE 'categories'");
        if (mysqli_num_rows($cat_check) > 0) {
            echo "<p style='color: green;'>✓ Categories table exists</p>";
        } else {
            echo "<p style='color: red;'>✗ Categories table not found</p>";
        }
        
    } else {
        echo "<p style='color: red;'>✗ Database connection failed</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Database error: " . $e->getMessage() . "</p>";
}

// Check required files
echo "<h2>Required Files</h2>";
$required_files = [
    'index.php' => 'Main page',
    'includes/header.php' => 'Header component',
    'includes/footer.php' => 'Footer component',
    'config/database.php' => 'Database config',
    'assets/css/style.css' => 'Main stylesheet'
];

foreach ($required_files as $file => $description) {
    if (file_exists($file)) {
        echo "<p style='color: green;'>✓ $description ($file)</p>";
    } else {
        echo "<p style='color: red;'>✗ $description ($file) - MISSING</p>";
    }
}

// Check PHP version
echo "<h2>PHP Environment</h2>";
echo "<p>PHP Version: " . PHP_VERSION . "</p>";
echo "<p>Memory Limit: " . ini_get('memory_limit') . "</p>";
echo "<p>Max Execution Time: " . ini_get('max_execution_time') . "s</p>";

// Check if required extensions are loaded
$required_extensions = ['mysqli', 'json', 'curl', 'gd'];
foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<p style='color: green;'>✓ $ext extension loaded</p>";
    } else {
        echo "<p style='color: red;'>✗ $ext extension NOT loaded</p>";
    }
}

// Check sample data
echo "<h2>Sample Data Check</h2>";
if (isset($conn) && $conn) {
    try {
        $sample_query = "SELECT title, status, created_at FROM news ORDER BY created_at DESC LIMIT 5";
        $sample_result = mysqli_query($conn, $sample_query);
        
        if (mysqli_num_rows($sample_result) > 0) {
            echo "<p style='color: green;'>✓ Sample articles found:</p>";
            echo "<ul>";
            while ($row = mysqli_fetch_assoc($sample_result)) {
                echo "<li>" . htmlspecialchars($row['title']) . " (" . $row['status'] . ")</li>";
            }
            echo "</ul>";
        } else {
            echo "<p style='color: orange;'>⚠ No articles found in database</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Error checking sample data: " . $e->getMessage() . "</p>";
    }
}

echo "<h2>Next Steps</h2>";
echo "<p>If you see red marks above, those are the issues to fix.</p>";
echo "<p>If everything is green but you still can't see content, the issue might be:</p>";
echo "<ul>";
echo "<li>1. XAMPP services (Apache/MySQL) are not running</li>";
echo "<li>2. Database has no data</li>";
echo "<li>3. File permissions issues</li>";
echo "<li>4. URL rewriting problems (.htaccess)</li>";
echo "</ul>";

echo "<p><a href='index.php'>Go to Homepage</a> | <a href='admin/'>Admin Panel</a></p>";
?>

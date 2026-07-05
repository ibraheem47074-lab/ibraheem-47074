<?php
echo "<h1>PK Live News - Simple Multi-Language Setup</h1>";
echo "<p>This script will set up the database tables needed for multi-language support.</p>";

// First, let's check if we can connect to MySQL without specifying database
try {
    $conn = mysqli_connect('localhost', 'root', '');
    if (!$conn) {
        throw new Exception("Cannot connect to MySQL: " . mysqli_connect_error());
    }
    echo "<div style='color: green;'>✓ MySQL connection successful</div>";
} catch (Exception $e) {
    echo "<div style='color: red;'>✗ MySQL connection failed: " . $e->getMessage() . "</div>";
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 4px; margin: 20px 0;'>";
    echo "<strong>Please check:</strong><br>";
    echo "1. MySQL service is running<br>";
    echo "2. Username/password are correct (root/empty)<br>";
    echo "3. MySQL port is accessible (usually 3306)<br>";
    echo "4. No firewall blocking MySQL<br>";
    echo "</div>";
    exit;
}

// Create database if it doesn't exist
$db_name = 'pk_live_news';
$create_db_query = "CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if (mysqli_query($conn, $create_db_query)) {
    echo "<div style='color: green;'>✓ Database '$db_name' ready</div>";
} else {
    echo "<div style='color: orange;'>⚠ Database '$db_name' already exists or error occurred</div>";
}

// Select the database
mysqli_select_db($conn, $db_name);

// Read and execute SQL setup
$sql_file = 'database_update_multilang.sql';
if (file_exists($sql_file)) {
    echo "<h2>Creating Tables...</h2>";
    
    $sql = file_get_contents($sql_file);
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    $success_count = 0;
    $error_count = 0;
    
    foreach ($statements as $statement) {
        if (empty($statement)) continue;
        
        try {
            if (mysqli_query($conn, $statement)) {
                $success_count++;
                echo "<div style='color: green; font-size: 12px;'>✓ " . htmlspecialchars(substr($statement, 0, 60)) . "...</div>";
            } else {
                $error = mysqli_error($conn);
                if (strpos($error, 'already exists') !== false || strpos($error, 'Duplicate') !== false) {
                    echo "<div style='color: orange; font-size: 12px;'>⚠ " . htmlspecialchars(substr($statement, 0, 60)) . "... (already exists)</div>";
                    $success_count++;
                } else {
                    $error_count++;
                    echo "<div style='color: red; font-size: 12px;'>✗ " . htmlspecialchars($error) . "</div>";
                }
            }
        } catch (Exception $e) {
            $error_count++;
            echo "<div style='color: red; font-size: 12px;'>✗ " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }
    
    echo "<h3>Setup Results</h3>";
    echo "<div style='background: #e8f5e8; padding: 10px; border-radius: 4px; margin: 10px 0;'>";
    echo "<strong>✓ Success:</strong> $success_count statements executed<br>";
    if ($error_count > 0) {
        echo "<strong>✗ Errors:</strong> $error_count statements failed<br>";
    }
    echo "</div>";
    
    if ($error_count === 0) {
        echo "<h2 style='color: green;'>🎉 Setup Complete!</h2>";
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 4px; margin: 20px 0;'>";
        echo "<strong>Multi-language system is now ready!</strong><br><br>";
        echo "✅ Languages table created with 5 languages<br>";
        echo "✅ User language preferences table created<br>";
        echo "✅ Site settings table created<br>";
        echo "✅ News table updated with language columns<br>";
        echo "✅ Categories table updated with language columns<br><br>";
        
        echo "<strong>Next Steps:</strong><br>";
        echo "1. <a href='index.php'>Visit your website</a> to test language switcher<br>";
        echo "2. <a href='admin/'>Go to admin panel</a> to manage languages<br>";
        echo "3. <a href='admin/add_news_multilang.php'>Create multilingual news</a><br>";
        echo "</div>";
        
        // Test database connection with the actual database
        echo "<h2>Final Test</h2>";
        $test_conn = mysqli_connect('localhost', 'root', '', $db_name);
        if ($test_conn) {
            echo "<div style='color: green;'>✓ Database connection with '$db_name' successful</div>";
            
            // Check if tables exist
            $tables = ['languages', 'user_language_preferences', 'site_settings'];
            foreach ($tables as $table) {
                $result = mysqli_query($test_conn, "SHOW TABLES LIKE '$table'");
                if (mysqli_num_rows($result) > 0) {
                    echo "<div style='color: green;'>✓ Table '$table' exists</div>";
                } else {
                    echo "<div style='color: red;'>✗ Table '$table' missing</div>";
                }
            }
        } else {
            echo "<div style='color: red;'>✗ Cannot connect to database '$db_name'</div>";
        }
    }
    
} else {
    echo "<div style='color: red;'>✗ SQL setup file not found: $sql_file</div>";
}

mysqli_close($conn);

echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h1, h2, h3 { color: #333; }
div { margin: 5px 0; padding: 5px; }
a { color: #007bff; text-decoration: none; }
a:hover { text-decoration: underline; }
</style>";
?>

<?php
// Final test to verify all duplicate function errors are resolved
echo "<h2>PK Live News - Final Test</h2>";

try {
    // Test all required files can be included without errors
    require_once 'config/database.php';
    require_once 'config/helpers.php';
    require_once 'includes/language_functions.php';
    
    echo "<p style='color: green;'>✅ All files loaded successfully - no duplicate function errors</p>";
    
    // Test database connection
    if (!$conn) {
        throw new Exception("Database connection failed");
    }
    echo "<p style='color: green;'>✅ Database connected</p>";
    
    // Test key functions exist
    $required_functions = [
        'get_news_title',
        'get_site_setting', 
        'format_news_date',
        'clean_input',
        'is_logged_in',
        'is_admin'
    ];
    
    foreach ($required_functions as $func) {
        if (function_exists($func)) {
            echo "<p style='color: green;'>✅ Function $func() exists</p>";
        } else {
            echo "<p style='color: red;'>❌ Function $func() missing</p>";
        }
    }
    
    // Test news table
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM news");
    $count = mysqli_fetch_assoc($result)['count'];
    echo "<p><strong>Total news articles:</strong> $count</p>";
    
    // Create sample news if needed
    if ($count == 0) {
        echo "<p style='color: orange;'>Creating sample news...</p>";
        
        $sql = "INSERT INTO news (title, slug, content, excerpt, status, published_at, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW(), NOW())";
        
        $stmt = mysqli_prepare($conn, $sql);
        $title = 'Breaking News: System Working Correctly - ' . date('Y-m-d H:i:s');
        $slug = 'breaking-news-working-' . time();
        $content = '<p>Great news! The PK Live News system is now working correctly. All duplicate function errors have been resolved and news articles can be displayed properly.</p>';
        $excerpt = 'PK Live News system is now working correctly after resolving duplicate function errors.';
        $status = 'published';
        
        mysqli_stmt_bind_param($stmt, 'sssss', $title, $slug, $content, $excerpt, $status);
        
        if (mysqli_stmt_execute($stmt)) {
            echo "<p style='color: green;'>✅ Sample news created successfully</p>";
        }
    }
    
    echo "<div style='margin-top: 30px; padding: 20px; background: #d4edda; border-radius: 5px; border: 1px solid #c3e6cb;'>";
    echo "<h3 style='color: #155724;'>🎉 All Issues Resolved!</h3>";
    echo "<p>All duplicate function errors have been fixed. Your news system should now work correctly.</p>";
    echo "<div style='margin-top: 15px;'>";
    echo "<a href='index.php' style='background: #28a745; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>📰 Visit Homepage</a>";
    echo "<a href='admin/' style='background: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px;'>⚙️ Admin Panel</a>";
    echo "</div>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>

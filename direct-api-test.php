<?php
// Direct test of the API
echo "<h1>Direct API Test</h1>";

// Test the API directly
echo "<h2>Testing API directly:</h2>";
ob_start();
include 'api/news-performance.php';
$api_output = ob_get_clean();

echo "<pre>" . htmlspecialchars($api_output) . "</pre>";

// Also test if we can parse it as JSON
echo "<h2>JSON Parse Test:</h2>";
$data = json_decode($api_output, true);
if ($data) {
    echo "<p style='color: green;'>✅ JSON is valid</p>";
    if (isset($data['success']) && $data['success']) {
        echo "<p style='color: green;'>✅ API reports success</p>";
        if (isset($data['data']['total_stats'])) {
            $stats = $data['data']['total_stats'];
            echo "<ul>";
            echo "<li>Total Sources: " . $stats['total_sources'] . "</li>";
            echo "<li>Total Articles: " . $stats['total_articles'] . "</li>";
            echo "<li>Total Views: " . $stats['total_views'] . "</li>";
            echo "</ul>";
        }
    } else {
        echo "<p style='color: red;'>❌ API reports error: " . ($data['error'] ?? 'Unknown') . "</p>";
    }
} else {
    echo "<p style='color: red;'>❌ JSON is invalid</p>";
    echo "<p>Last JSON error: " . json_last_error_msg() . "</p>";
}

// Test database connection separately
echo "<h2>Database Test:</h2>";
try {
    $conn = mysqli_connect('localhost', 'root', '', 'pk_live_news');
    if ($conn) {
        echo "<p style='color: green;'>✅ Database connection successful</p>";
        
        // Check tables
        $tables = ['channels', 'news', 'categories'];
        foreach ($tables as $table) {
            $result = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
            $exists = mysqli_num_rows($result) > 0;
            echo "<li>Table '$table': " . ($exists ? "✅ EXISTS" : "❌ NOT FOUND") . "</li>";
            
            if ($exists) {
                $count = mysqli_query($conn, "SELECT COUNT(*) as count FROM $table");
                $row = mysqli_fetch_assoc($count);
                echo " (Records: " . $row['count'] . ")";
            }
        }
        mysqli_close($conn);
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database error: " . $e->getMessage() . "</p>";
}
?>

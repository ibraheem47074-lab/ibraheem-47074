<?php
require_once 'config/database.php';

echo "<h2>Debug Index Page Like Issues</h2>";

// 1. Check if post_likes table exists
echo "<h3>1. Table Status</h3>";
$result = mysqli_query($conn, 'SHOW TABLES LIKE "post_likes"');
if(mysqli_num_rows($result) > 0) {
    echo "✅ post_likes table exists<br>";
    
    // Get total records
    $count = mysqli_query($conn, 'SELECT COUNT(*) as count FROM post_likes');
    $row = mysqli_fetch_assoc($count);
    echo "📊 Total records: " . $row['count'] . "<br>";
    
    // Show sample data
    $sample = mysqli_query($conn, 'SELECT * FROM post_likes LIMIT 3');
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>News ID</th><th>User ID</th><th>IP Address</th><th>User Agent</th></tr>";
    while($row = mysqli_fetch_assoc($sample)) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['news_id'] . "</td>";
        echo "<td>" . ($row['user_id'] ?? 'NULL') . "</td>";
        echo "<td>" . $row['ip_address'] . "</td>";
        echo "<td>" . substr($row['user_agent'] ?? '', 0, 30) . "...</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "❌ post_likes table does not exist<br>";
}

// 2. Test the exact query used in index.php
echo "<h3>2. Index Query Test</h3>";
$index_query = "SELECT n.*, c.name as category_name, u.name as author_name,
                (SELECT COUNT(*) FROM comments WHERE news_id = n.id AND status = 'approved') as comment_count,
                COALESCE((SELECT COUNT(*) FROM post_likes WHERE news_id = n.id), 0) as likes_count
                FROM news n 
                LEFT JOIN categories c ON n.category_id = c.id 
                LEFT JOIN users u ON n.author_id = u.id 
                WHERE n.status = 'published' AND (n.published_at <= NOW() OR n.published_at IS NULL) 
                ORDER BY n.created_at DESC LIMIT 3";

$test_result = mysqli_query($conn, $index_query);
if($test_result) {
    echo "✅ Index query executed successfully<br>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>News ID</th><th>Title</th><th>Likes Count</th><th>Comment Count</th></tr>";
    while($row = mysqli_fetch_assoc($test_result)) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . substr($row['title'] ?? 'No title', 0, 40) . "...</td>";
        echo "<td>" . $row['likes_count'] . "</td>";
        echo "<td>" . $row['comment_count'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "❌ Index query failed: " . mysqli_error($conn) . "<br>";
}

// 3. Test API endpoint directly
echo "<h3>3. API Endpoint Test</h3>";
$test_news_id = 1;

// First check if this news exists
$news_check = mysqli_query($conn, "SELECT id, title FROM news WHERE id = $test_news_id");
if(mysqli_num_rows($news_check) > 0) {
    $news = mysqli_fetch_assoc($news_check);
    echo "Testing with news: " . $news['title'] . "<br>";
    
    // Simulate API call
    echo "<p><strong>Testing API call...</strong></p>";
    
    // Create test session
    session_start();
    $_SERVER['REQUEST_METHOD'] = 'POST';
    
    // Capture API output
    ob_start();
    
    // Simulate POST data
    $json_data = json_encode(['news_id' => $test_news_id]);
    file_put_contents('php://input', $json_data);
    
    // Include API (this might not work perfectly in this context)
    try {
        include 'api/toggle-like.php';
        $api_output = ob_get_clean();
        echo "API Output: " . htmlspecialchars($api_output) . "<br>";
        
        $response = json_decode($api_output, true);
        if($response && isset($response['success'])) {
            echo "✅ API returned success: " . ($response['success'] ? 'YES' : 'NO') . "<br>";
            if(isset($response['likes_count'])) {
                echo "✅ Likes count: " . $response['likes_count'] . "<br>";
            }
        } else {
            echo "❌ API response format issue<br>";
        }
    } catch (Exception $e) {
        ob_get_clean();
        echo "❌ API test failed: " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ No news found with ID $test_news_id<br>";
}

// 4. Check JavaScript console errors simulation
echo "<h3>4. JavaScript Debugging Tips</h3>";
echo "<p>To debug JavaScript issues:</p>";
echo "<ul>";
echo "<li>Open browser console (F12)</li>";
echo "<li>Click a like button</li>";
echo "<li>Look for console.log messages</li>";
echo "<li>Check for any JavaScript errors</li>";
echo "<li>Verify the API response format</li>";
echo "</ul>";

echo "<h3>5. Common Issues & Solutions</h3>";
echo "<ul>";
echo "<li><strong>API not found:</strong> Check file path and permissions</li>";
echo "<li><strong>No response:</strong> Check network tab in browser dev tools</li>";
echo "<li><strong>Count not updating:</strong> Check element selectors in updateLikeSummary function</li>";
echo "<li><strong>Database errors:</strong> Check post_likes table structure</li>";
echo "</ul>";

mysqli_close($conn);
?>

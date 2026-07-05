<?php
// Final test to verify like system is working
require_once 'config/database.php';
require_once 'config/helpers.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo "<h2>Final Like System Test</h2>";

// Test 1: Verify all components are working
echo "<h3>✅ System Components Check</h3>";

// Check database connection
if ($conn) {
    echo "✅ Database connection: OK<br>";
} else {
    echo "❌ Database connection: FAILED<br>";
}

// Check post_likes table
$table_check = mysqli_query($conn, 'SHOW TABLES LIKE "post_likes"');
if (mysqli_num_rows($table_check) > 0) {
    echo "✅ post_likes table: EXISTS<br>";
} else {
    echo "❌ post_likes table: MISSING<br>";
}

// Check API file
if (file_exists('api/toggle-like.php')) {
    echo "✅ API file: EXISTS<br>";
} else {
    echo "❌ API file: MISSING<br>";
}

// Test 2: Test a complete like cycle
echo "<h3>🔄 Complete Like Cycle Test</h3>";

// Get a test news article
$news_query = "SELECT id, title FROM news WHERE status = 'published' ORDER BY RAND() LIMIT 1";
$news_result = mysqli_query($conn, $news_query);

if (mysqli_num_rows($news_result) > 0) {
    $news = mysqli_fetch_assoc($news_result);
    $test_news_id = $news['id'];
    
    echo "Testing with: <strong>" . htmlspecialchars($news['title']) . "</strong> (ID: $test_news_id)<br><br>";
    
    // Get initial like count
    $initial_count_query = "SELECT COUNT(*) as count FROM post_likes WHERE news_id = ?";
    $stmt = mysqli_prepare($conn, $initial_count_query);
    mysqli_stmt_bind_param($stmt, 'i', $test_news_id);
    mysqli_stmt_execute($stmt);
    $initial_result = mysqli_stmt_get_result($stmt);
    $initial_likes = mysqli_fetch_assoc($initial_result)['count'];
    
    echo "Initial likes: <strong>$initial_likes</strong><br>";
    
    // Simulate API request
    echo "<br><strong>Simulating API request...</strong><br>";
    
    // Prepare data like the JavaScript would send
    $news_id = $test_news_id;
    $user_id = is_logged_in() ? $_SESSION['user_id'] : null;
    $ip_address = $_SERVER['REMOTE_ADDR'];
    
    // Guest handling
    if ($user_id === null) {
        if (!isset($_SESSION['guest_identifier'])) {
            $_SESSION['guest_identifier'] = 'guest_' . uniqid() . '_' . md5($ip_address);
        }
        $guest_identifier = $_SESSION['guest_identifier'];
    }
    
    // Check if already liked
    if ($user_id === null) {
        $check_query = "SELECT id FROM post_likes WHERE news_id = ? AND user_id IS NULL AND user_agent = ?";
        $stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($stmt, 'is', $news_id, $guest_identifier);
    } else {
        $check_query = "SELECT id FROM post_likes WHERE news_id = ? AND user_id = ?";
        $stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($stmt, 'ii', $news_id, $user_id);
    }
    mysqli_stmt_execute($stmt);
    $existing_like = mysqli_stmt_get_result($stmt);
    
    $action = '';
    if (mysqli_num_rows($existing_like) > 0) {
        // Unlike
        $action = 'unliked';
        if ($user_id === null) {
            $delete_query = "DELETE FROM post_likes WHERE news_id = ? AND user_id IS NULL AND user_agent = ?";
            $stmt = mysqli_prepare($conn, $delete_query);
            mysqli_stmt_bind_param($stmt, 'is', $news_id, $guest_identifier);
        } else {
            $delete_query = "DELETE FROM post_likes WHERE news_id = ? AND user_id = ?";
            $stmt = mysqli_prepare($conn, $delete_query);
            mysqli_stmt_bind_param($stmt, 'ii', $news_id, $user_id);
        }
        mysqli_stmt_execute($stmt);
        echo "Action: <strong>UNLIKE</strong><br>";
    } else {
        // Like
        $action = 'liked';
        if ($user_id === null) {
            $insert_query = "INSERT INTO post_likes (news_id, user_id, ip_address, user_agent) VALUES (?, NULL, ?, ?)";
            $stmt = mysqli_prepare($conn, $insert_query);
            mysqli_stmt_bind_param($stmt, 'iss', $news_id, $ip_address, $guest_identifier);
        } else {
            $insert_query = "INSERT INTO post_likes (news_id, user_id, ip_address, user_agent) VALUES (?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $insert_query);
            mysqli_stmt_bind_param($stmt, 'iiss', $news_id, $user_id, $ip_address, $_SERVER['HTTP_USER_AGENT']);
        }
        mysqli_stmt_execute($stmt);
        echo "Action: <strong>LIKE</strong><br>";
    }
    
    // Get final count
    $final_count_query = "SELECT COUNT(*) as count FROM post_likes WHERE news_id = ?";
    $stmt = mysqli_prepare($conn, $final_count_query);
    mysqli_stmt_bind_param($stmt, 'i', $test_news_id);
    mysqli_stmt_execute($stmt);
    $final_result = mysqli_stmt_get_result($stmt);
    $final_likes = mysqli_fetch_assoc($final_result)['count'];
    
    echo "Final likes: <strong>$final_likes</strong><br>";
    
    // Show the API response format
    echo "<br><strong>API Response Format:</strong><br>";
    $api_response = [
        'success' => true,
        'action' => $action,
        'likes_count' => $final_likes,
        'message' => $action === 'liked' ? 'Post liked' : 'Post unliked'
    ];
    echo "<pre>" . json_encode($api_response, JSON_PRETTY_PRINT) . "</pre>";
    
    // Test index query format
    echo "<br><strong>Index Query Test:</strong><br>";
    $index_query = "SELECT n.*, COALESCE((SELECT COUNT(*) FROM post_likes WHERE news_id = n.id), 0) as likes_count FROM news n WHERE n.id = ?";
    $stmt = mysqli_prepare($conn, $index_query);
    mysqli_stmt_bind_param($stmt, 'i', $test_news_id);
    mysqli_stmt_execute($stmt);
    $index_result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($index_result)) {
        echo "Index query returns: <strong>" . $row['likes_count'] . "</strong> likes<br>";
        echo "Match with database: <strong>" . ($row['likes_count'] == $final_likes ? '✅ YES' : '❌ NO') . "</strong><br>";
    }
    
} else {
    echo "❌ No published news articles found for testing<br>";
}

// Test 3: JavaScript integration test
echo "<h3>🌐 JavaScript Integration Test</h3>";
echo "<p>To test the complete like system:</p>";
echo "<ol>";
echo "<li>Open <a href='index.php'>index.php</a> in browser</li>";
echo "<li>Open F12 developer console</li>";
echo "<li>Click any like button</li>";
echo "<li>Check console for: <code>API Response: {...}</code></li>";
echo "<li>Check console for: <code>Updating like summary for news ID: X with count: Y</code></li>";
echo "<li>Verify the like count updates immediately on the page</li>";
echo "</ol>";

echo "<h3>🎯 Expected Results</h3>";
echo "<ul>";
echo "<li>✅ Like button shows loading spinner during API call</li>";
echo "<li>✅ Console shows successful API response</li>";
echo "<li>✅ Like count updates immediately in all locations</li>";
echo "<li>✅ Like summary shows/hides based on count</li>";
echo "<li>✅ Button state changes (filled/unfilled thumb)</li>";
echo "</ul>";

mysqli_close($conn);
?>

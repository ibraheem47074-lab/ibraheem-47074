<?php
// Simple test to isolate like counting issues
require_once 'config/database.php';
require_once 'config/helpers.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo "<h2>Simple Like Test</h2>";

// Test 1: Check current session
echo "<h3>1. Session Status</h3>";
echo "User logged in: " . (is_logged_in() ? 'YES' : 'NO') . "<br>";
if (is_logged_in()) {
    echo "User ID: " . $_SESSION['user_id'] . "<br>";
    echo "User Role: " . $_SESSION['user_role'] . "<br>";
}

// Test 2: Pick a news article to test with
echo "<h3>2. Test News Article</h3>";
$news_query = "SELECT id, title FROM news WHERE status = 'published' ORDER BY id DESC LIMIT 1";
$news_result = mysqli_query($conn, $news_query);

if (mysqli_num_rows($news_result) > 0) {
    $news = mysqli_fetch_assoc($news_result);
    $test_news_id = $news['id'];
    echo "Testing with: " . htmlspecialchars($news['title']) . " (ID: $test_news_id)<br>";
    
    // Test 3: Check current likes
    echo "<h3>3. Current Like Count</h3>";
    $likes_query = "SELECT COUNT(*) as count FROM post_likes WHERE news_id = ?";
    $stmt = mysqli_prepare($conn, $likes_query);
    mysqli_stmt_bind_param($stmt, 'i', $test_news_id);
    mysqli_stmt_execute($stmt);
    $likes_result = mysqli_stmt_get_result($stmt);
    $current_likes = mysqli_fetch_assoc($likes_result)['count'];
    echo "Current likes: $current_likes<br>";
    
    // Test 4: Simulate a like action
    echo "<h3>4. Simulate Like Action</h3>";
    
    $user_id = is_logged_in() ? $_SESSION['user_id'] : null;
    $ip_address = $_SERVER['REMOTE_ADDR'];
    
    // For guest users, create a session identifier
    if ($user_id === null) {
        if (!isset($_SESSION['guest_identifier'])) {
            $_SESSION['guest_identifier'] = 'guest_' . uniqid() . '_' . md5($ip_address);
        }
        $guest_identifier = $_SESSION['guest_identifier'];
        
        // Check if guest already liked
        $check_query = "SELECT id FROM post_likes WHERE news_id = ? AND user_id IS NULL AND user_agent = ?";
        $stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($stmt, 'is', $test_news_id, $guest_identifier);
    } else {
        // Check if user already liked
        $check_query = "SELECT id FROM post_likes WHERE news_id = ? AND user_id = ?";
        $stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($stmt, 'ii', $test_news_id, $user_id);
    }
    
    mysqli_stmt_execute($stmt);
    $existing_like = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($existing_like) > 0) {
        echo "User already liked this post - will unlike<br>";
        
        // Unlike
        if ($user_id === null) {
            $delete_query = "DELETE FROM post_likes WHERE news_id = ? AND user_id IS NULL AND user_agent = ?";
            $stmt = mysqli_prepare($conn, $delete_query);
            mysqli_stmt_bind_param($stmt, 'is', $test_news_id, $guest_identifier);
        } else {
            $delete_query = "DELETE FROM post_likes WHERE news_id = ? AND user_id = ?";
            $stmt = mysqli_prepare($conn, $delete_query);
            mysqli_stmt_bind_param($stmt, 'ii', $test_news_id, $user_id);
        }
        
        if (mysqli_stmt_execute($stmt)) {
            echo "✅ Successfully unliked<br>";
            $action = 'unliked';
        } else {
            echo "❌ Error unliking: " . mysqli_error($conn) . "<br>";
        }
    } else {
        echo "User hasn't liked yet - will like<br>";
        
        // Like
        if ($user_id === null) {
            $insert_query = "INSERT INTO post_likes (news_id, user_id, ip_address, user_agent) VALUES (?, NULL, ?, ?)";
            $stmt = mysqli_prepare($conn, $insert_query);
            mysqli_stmt_bind_param($stmt, 'iss', $test_news_id, $ip_address, $guest_identifier);
        } else {
            $insert_query = "INSERT INTO post_likes (news_id, user_id, ip_address, user_agent) VALUES (?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $insert_query);
            mysqli_stmt_bind_param($stmt, 'iiss', $test_news_id, $user_id, $ip_address, $_SERVER['HTTP_USER_AGENT']);
        }
        
        if (mysqli_stmt_execute($stmt)) {
            echo "✅ Successfully liked<br>";
            $action = 'liked';
        } else {
            echo "❌ Error liking: " . mysqli_error($conn) . "<br>";
        }
    }
    
    // Test 5: Check new like count
    echo "<h3>5. New Like Count</h3>";
    $new_likes_query = "SELECT COUNT(*) as count FROM post_likes WHERE news_id = ?";
    $stmt = mysqli_prepare($conn, $new_likes_query);
    mysqli_stmt_bind_param($stmt, 'i', $test_news_id);
    mysqli_stmt_execute($stmt);
    $new_likes_result = mysqli_stmt_get_result($stmt);
    $new_likes = mysqli_fetch_assoc($new_likes_result)['count'];
    echo "New likes: $new_likes<br>";
    
    echo "<p><strong>Change: $current_likes → $new_likes (Action: $action)</strong></p>";
    
    // Test 6: Test the index query format
    echo "<h3>6. Index Query Format Test</h3>";
    $index_query = "SELECT n.*, COALESCE((SELECT COUNT(*) FROM post_likes WHERE news_id = n.id), 0) as likes_count FROM news n WHERE n.id = ?";
    $stmt = mysqli_prepare($conn, $index_query);
    mysqli_stmt_bind_param($stmt, 'i', $test_news_id);
    mysqli_stmt_execute($stmt);
    $index_result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($index_result)) {
        echo "Index query result: " . $row['likes_count'] . " likes<br>";
        echo "✅ Index query matches database count: " . ($row['likes_count'] == $new_likes ? 'YES' : 'NO') . "<br>";
    }
    
} else {
    echo "❌ No published news articles found<br>";
}

echo "<h3>7. Test API Response Format</h3>";
echo "<p>The API should return JSON like this:</p>";
echo "<pre>";
echo json_encode([
    'success' => true,
    'action' => 'liked',
    'likes_count' => $new_likes ?? 0,
    'message' => 'Post liked'
]);
echo "</pre>";

mysqli_close($conn);
?>

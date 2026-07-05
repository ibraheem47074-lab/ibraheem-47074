<?php
/**
 * Comprehensive Comment System Testing Suite
 * Tests all comment functionality including submission, display, moderation, and threaded comments
 */

require_once 'config/database.php';

echo "<h1>Comprehensive Comment System Testing Suite</h1>";
echo "<p>This tool tests all aspects of the comment system including submission, display, moderation, and replies.</p>";

// Test results storage
$test_results = [];

// Function to run a test and store results
function run_test($test_name, $test_function) {
    global $test_results;
    
    echo "<h3>Testing: $test_name</h3>";
    
    try {
        $result = $test_function();
        if ($result['success']) {
            echo "<p style='color: green;'>â PASS: " . $result['message'] . "</p>";
            $test_results[$test_name] = ['status' => 'PASS', 'message' => $result['message']];
        } else {
            echo "<p style='color: red;'>â FAIL: " . $result['message'] . "</p>";
            $test_results[$test_name] = ['status' => 'FAIL', 'message' => $result['message']];
        }
        
        if (isset($result['details'])) {
            echo "<p><small>Details: " . $result['details'] . "</small></p>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>â ERROR: " . $e->getMessage() . "</p>";
        $test_results[$test_name] = ['status' => 'ERROR', 'message' => $e->getMessage()];
    }
    
    echo "<hr>";
}

// Test 1: Database Schema
function test_database_schema() {
    global $conn;
    
    // Check if comments table exists
    $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'comments'");
    if (mysqli_num_rows($table_check) == 0) {
        return ['success' => false, 'message' => 'Comments table does not exist'];
    }
    
    // Check required columns
    $required_columns = ['id', 'news_id', 'user_id', 'parent_id', 'name', 'email', 'comment', 'status', 'created_at'];
    $columns_query = "SHOW COLUMNS FROM comments";
    $columns_result = mysqli_query($conn, $columns_query);
    $existing_columns = [];
    
    while ($row = mysqli_fetch_assoc($columns_result)) {
        $existing_columns[] = $row['Field'];
    }
    
    $missing_columns = array_diff($required_columns, $existing_columns);
    if (!empty($missing_columns)) {
        return ['success' => false, 'message' => 'Missing columns: ' . implode(', ', $missing_columns)];
    }
    
    // Check foreign key constraints
    $constraints_query = "SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_NAME = 'comments' AND CONSTRAINT_NAME != 'PRIMARY'";
    $constraints_result = mysqli_query($conn, $constraints_query);
    $constraints = [];
    
    while ($row = mysqli_fetch_assoc($constraints_result)) {
        $constraints[] = $row['CONSTRAINT_NAME'];
    }
    
    return ['success' => true, 'message' => 'Database schema is correct', 'details' => 'Found ' . count($constraints) . ' foreign key constraints'];
}

// Test 2: Comment Submission API
function test_comment_submission() {
    global $conn;
    
    // Get a sample news article
    $news_query = "SELECT id, title FROM news WHERE status = 'published' LIMIT 1";
    $news_result = mysqli_query($conn, $news_query);
    
    if (mysqli_num_rows($news_result) == 0) {
        return ['success' => false, 'message' => 'No published news articles found for testing'];
    }
    
    $news = mysqli_fetch_assoc($news_result);
    $news_id = $news['id'];
    
    // Test API call
    $base_url = 'http://' . $_SERVER['HTTP_HOST'];
    $script_path = dirname($_SERVER['PHP_SELF']);
    $api_url = $base_url . $script_path . '/api/submit-comment.php';
    $test_data = [
        'news_id' => $news_id,
        'comment' => 'Test comment from comprehensive test suite - ' . date('Y-m-d H:i:s'),
        'parent_id' => null
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($test_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);
    
    if ($curl_error) {
        return ['success' => false, 'message' => 'CURL Error: ' . $curl_error];
    }
    
    if ($http_code !== 200) {
        return ['success' => false, 'message' => 'HTTP Error: ' . $http_code];
    }
    
    $result = json_decode($response, true);
    if (!$result || !isset($result['success'])) {
        return ['success' => false, 'message' => 'Invalid API response', 'details' => $response];
    }
    
    if (!$result['success']) {
        return ['success' => false, 'message' => 'API returned error: ' . $result['message']];
    }
    
    return ['success' => true, 'message' => 'Comment submission successful', 'details' => 'Comment ID: ' . $result['comment_id']];
}

// Test 3: Comment Retrieval API
function test_comment_retrieval() {
    global $conn;
    
    // Check if get-comments API exists
    $api_file = __DIR__ . '/api/get-comments.php';
    if (!file_exists($api_file)) {
        return ['success' => false, 'message' => 'get-comments.php API file missing'];
    }
    
    // Get a news article with comments
    $news_query = "SELECT n.id, n.title FROM news n 
                  LEFT JOIN comments c ON n.id = c.news_id 
                  WHERE n.status = 'published' 
                  GROUP BY n.id 
                  HAVING COUNT(c.id) > 0 
                  LIMIT 1";
    $news_result = mysqli_query($conn, $news_query);
    
    if (mysqli_num_rows($news_result) == 0) {
        return ['success' => false, 'message' => 'No news articles with comments found for testing'];
    }
    
    $news = mysqli_fetch_assoc($news_result);
    $news_id = $news['id'];
    
    // Test API call
    $base_url = 'http://' . $_SERVER['HTTP_HOST'];
    $script_path = dirname($_SERVER['PHP_SELF']);
    $api_url = $base_url . $script_path . '/api/get-comments.php?news_id=' . $news_id;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);
    
    if ($curl_error) {
        return ['success' => false, 'message' => 'CURL Error: ' . $curl_error];
    }
    
    if ($http_code !== 200) {
        return ['success' => false, 'message' => 'HTTP Error: ' . $http_code];
    }
    
    $result = json_decode($response, true);
    if (!$result || !isset($result['success'])) {
        return ['success' => false, 'message' => 'Invalid API response', 'details' => $response];
    }
    
    if (!$result['success']) {
        return ['success' => false, 'message' => 'API returned error: ' . $result['message']];
    }
    
    $comment_count = isset($result['comments']) ? count($result['comments']) : 0;
    return ['success' => true, 'message' => 'Comment retrieval successful', 'details' => "Retrieved $comment_count comments"];
}

// Test 4: Threaded Comments (Replies)
function test_threaded_comments() {
    global $conn;
    
    // Get a parent comment
    $parent_query = "SELECT id, news_id FROM comments WHERE parent_id IS NULL LIMIT 1";
    $parent_result = mysqli_query($conn, $parent_query);
    
    if (mysqli_num_rows($parent_result) == 0) {
        return ['success' => false, 'message' => 'No parent comments found for testing threaded comments'];
    }
    
    $parent = mysqli_fetch_assoc($parent_result);
    $parent_id = $parent['id'];
    $news_id = $parent['news_id'];
    
    // Test reply submission
    $base_url = 'http://' . $_SERVER['HTTP_HOST'];
    $script_path = dirname($_SERVER['PHP_SELF']);
    $api_url = $base_url . $script_path . '/api/submit-comment.php';
    $reply_data = [
        'news_id' => $news_id,
        'comment' => 'Test reply from comprehensive test suite - ' . date('Y-m-d H:i:s'),
        'parent_id' => $parent_id
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($reply_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);
    
    if ($curl_error) {
        return ['success' => false, 'message' => 'CURL Error: ' . $curl_error];
    }
    
    if ($http_code !== 200) {
        return ['success' => false, 'message' => 'HTTP Error: ' . $http_code];
    }
    
    $result = json_decode($response, true);
    if (!$result || !isset($result['success'])) {
        return ['success' => false, 'message' => 'Invalid API response', 'details' => $response];
    }
    
    if (!$result['success']) {
        return ['success' => false, 'message' => 'Reply submission failed: ' . $result['message']];
    }
    
    // Verify reply is linked to parent
    $reply_id = $result['comment_id'];
    $verify_query = "SELECT parent_id FROM comments WHERE id = ?";
    $stmt = mysqli_prepare($conn, $verify_query);
    mysqli_stmt_bind_param($stmt, 'i', $reply_id);
    mysqli_stmt_execute($stmt);
    $verify_result = mysqli_stmt_get_result($stmt);
    $reply_data = mysqli_fetch_assoc($verify_result);
    
    if ($reply_data['parent_id'] != $parent_id) {
        return ['success' => false, 'message' => 'Reply not properly linked to parent comment'];
    }
    
    return ['success' => true, 'message' => 'Threaded comments working', 'details' => "Reply ID: $reply_id linked to Parent ID: $parent_id"];
}

// Test 5: Comment Moderation
function test_comment_moderation() {
    global $conn;
    
    // Get a pending comment
    $pending_query = "SELECT id FROM comments WHERE status = 'pending' LIMIT 1";
    $pending_result = mysqli_query($conn, $pending_query);
    
    if (mysqli_num_rows($pending_result) == 0) {
        // Create a pending comment for testing
        $news_query = "SELECT id FROM news WHERE status = 'published' LIMIT 1";
        $news_result = mysqli_query($conn, $news_query);
        
        if (mysqli_num_rows($news_result) == 0) {
            return ['success' => false, 'message' => 'No news articles found to create test comment'];
        }
        
        $news_id = mysqli_fetch_assoc($news_result)['id'];
        
        $insert_query = "INSERT INTO comments (news_id, name, email, comment, status) VALUES (?, ?, ?, ?, 'pending')";
        $stmt = mysqli_prepare($conn, $insert_query);
        $name = 'Test Moderation User';
        $email = 'moderation@test.com';
        $comment = 'Test comment for moderation testing';
        mysqli_stmt_bind_param($stmt, 'isss', $news_id, $name, $email, $comment);
        
        if (!mysqli_stmt_execute($stmt)) {
            return ['success' => false, 'message' => 'Failed to create test comment for moderation'];
        }
        
        $comment_id = mysqli_insert_id($conn);
    } else {
        $comment_id = mysqli_fetch_assoc($pending_result)['id'];
    }
    
    // Test approval
    $approve_query = "UPDATE comments SET status = 'approved' WHERE id = ?";
    $stmt = mysqli_prepare($conn, $approve_query);
    mysqli_stmt_bind_param($stmt, 'i', $comment_id);
    
    if (!mysqli_stmt_execute($stmt)) {
        return ['success' => false, 'message' => 'Failed to approve comment'];
    }
    
    // Verify status change
    $verify_query = "SELECT status FROM comments WHERE id = ?";
    $stmt = mysqli_prepare($conn, $verify_query);
    mysqli_stmt_bind_param($stmt, 'i', $comment_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $status = mysqli_fetch_assoc($result)['status'];
    
    if ($status !== 'approved') {
        return ['success' => false, 'message' => 'Comment status not updated correctly'];
    }
    
    return ['success' => true, 'message' => 'Comment moderation working', 'details' => "Comment ID: $comment_id approved successfully"];
}

// Test 6: Comment Statistics
function test_comment_statistics() {
    global $conn;
    
    // Check if stored procedure exists
    $procedure_check = mysqli_query($conn, "SHOW PROCEDURE STATUS WHERE Name = 'GetCommentStats'");
    
    if (mysqli_num_rows($procedure_check) == 0) {
        return ['success' => false, 'message' => 'GetCommentStats stored procedure not found'];
    }
    
    // Get a news article with comments
    $news_query = "SELECT id FROM news WHERE status = 'published' LIMIT 1";
    $news_result = mysqli_query($conn, $news_query);
    
    if (mysqli_num_rows($news_result) == 0) {
        return ['success' => false, 'message' => 'No news articles found for statistics testing'];
    }
    
    $news_id = mysqli_fetch_assoc($news_result)['id'];
    
    // Test stored procedure
    $stmt = mysqli_prepare($conn, "CALL GetCommentStats(?)");
    mysqli_stmt_bind_param($stmt, 'i', $news_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (!$result) {
        return ['success' => false, 'message' => 'Failed to execute GetCommentStats procedure'];
    }
    
    $stats = mysqli_fetch_assoc($result);
    
    if (!isset($stats['total_comments'])) {
        return ['success' => false, 'message' => 'Invalid statistics returned from procedure'];
    }
    
    return ['success' => true, 'message' => 'Comment statistics working', 'details' => "Total comments: " . $stats['total_comments'] . ", Approved: " . $stats['approved_comments']];
}

// Test 7: Comment Likes System
function test_comment_likes() {
    global $conn;
    
    // Check if comment_likes table exists
    $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'comment_likes'");
    if (mysqli_num_rows($table_check) == 0) {
        return ['success' => false, 'message' => 'comment_likes table does not exist'];
    }
    
    // Get a comment to test likes on
    $comment_query = "SELECT id FROM comments LIMIT 1";
    $comment_result = mysqli_query($conn, $comment_query);
    
    if (mysqli_num_rows($comment_result) == 0) {
        return ['success' => false, 'message' => 'No comments found for testing likes'];
    }
    
    $comment_id = mysqli_fetch_assoc($comment_result)['id'];
    
    // Test adding a like
    $like_query = "INSERT INTO comment_likes (comment_id, ip_address, like_type) VALUES (?, ?, 'like')";
    $stmt = mysqli_prepare($conn, $like_query);
    $ip_address = '127.0.0.1';
    mysqli_stmt_bind_param($stmt, 'is', $comment_id, $ip_address);
    
    if (!mysqli_stmt_execute($stmt)) {
        return ['success' => false, 'message' => 'Failed to add comment like'];
    }
    
    // Verify like was added and comment count updated
    $verify_query = "SELECT likes_count FROM comments WHERE id = ?";
    $stmt = mysqli_prepare($conn, $verify_query);
    mysqli_stmt_bind_param($stmt, 'i', $comment_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $likes_count = mysqli_fetch_assoc($result)['likes_count'];
    
    if ($likes_count < 1) {
        return ['success' => false, 'message' => 'Comment likes count not updated'];
    }
    
    // Clean up test like
    $delete_query = "DELETE FROM comment_likes WHERE comment_id = ? AND ip_address = ?";
    $stmt = mysqli_prepare($conn, $delete_query);
    mysqli_stmt_bind_param($stmt, 'is', $comment_id, $ip_address);
    mysqli_stmt_execute($stmt);
    
    return ['success' => true, 'message' => 'Comment likes system working', 'details' => "Like added and count updated to: $likes_count"];
}

// Run all tests
echo "<h2>Running All Comment System Tests</h2>";

run_test("Database Schema", "test_database_schema");
run_test("Comment Submission API", "test_comment_submission");
run_test("Comment Retrieval API", "test_comment_retrieval");
run_test("Threaded Comments (Replies)", "test_threaded_comments");
run_test("Comment Moderation", "test_comment_moderation");
run_test("Comment Statistics", "test_comment_statistics");
run_test("Comment Likes System", "test_comment_likes");

// Display test summary
echo "<h2>Test Summary</h2>";

$total_tests = count($test_results);
$passed_tests = 0;
$failed_tests = 0;

echo "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%;'>";
echo "<tr style='background: #f0f0f0;'><th>Test Name</th><th>Status</th><th>Message</th></tr>";

foreach ($test_results as $test_name => $result) {
    $status_color = $result['status'] === 'PASS' ? 'green' : ($result['status'] === 'FAIL' ? 'red' : 'orange');
    echo "<tr>";
    echo "<td>" . htmlspecialchars($test_name) . "</td>";
    echo "<td style='color: $status_color; font-weight: bold;'>" . $result['status'] . "</td>";
    echo "<td>" . htmlspecialchars($result['message']) . "</td>";
    echo "</tr>";
    
    if ($result['status'] === 'PASS') {
        $passed_tests++;
    } else {
        $failed_tests++;
    }
}

echo "</table>";

echo "<h3>Results: $passed_tests/$total_tests tests passed</h3>";

if ($failed_tests > 0) {
    echo "<p style='color: red;'><strong>Some tests failed. Please review the failures above and fix any issues.</strong></p>";
} else {
    echo "<p style='color: green;'><strong>All tests passed! The comment system is working correctly.</strong></p>";
}

// Quick action buttons
echo "<div style='margin-top: 30px;'>";
echo "<form method='post' style='display: inline-block; margin-right: 10px;'>";
echo "<input type='submit' name='run_all_tests' value='Run All Tests Again' style='background: #007bff; color: white; padding: 10px 20px; border: none; cursor: pointer;'>";
echo "</form>";

echo "<a href='fix_comment_system.php' style='display: inline-block; margin-right: 10px;'>";
echo "<button style='background: #28a745; color: white; padding: 10px 20px; border: none; cursor: pointer;'>Comment System Diagnostics</button>";
echo "</a>";

echo "<a href='update_comments_schema.php' style='display: inline-block;'>";
echo "<button style='background: #ffc107; color: black; padding: 10px 20px; border: none; cursor: pointer;'>Update Schema</button>";
echo "</a>";
echo "</div>";

echo "<p><small>This comprehensive test suite validates all aspects of the comment system including database structure, API endpoints, functionality, and advanced features.</small></p>";
?>

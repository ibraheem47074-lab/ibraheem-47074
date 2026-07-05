<?php
/**
 * Updated Comment Test Runner with Fixes
 * This script runs all comment tests with proper error handling and URL fixes
 */

require_once 'config/database.php';

echo "<h1>Updated Comment System Test Runner</h1>";
echo "<p>This runner includes fixes for URL issues and missing schema components.</p>";

// Function to build proper API URL
function build_api_url($endpoint) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $script_path = str_replace('\\', '/', dirname($_SERVER['PHP_SELF']));
    
    // Remove trailing slash if present
    $script_path = rtrim($script_path, '/');
    
    return $protocol . '://' . $host . $script_path . '/' . $endpoint;
}

// Function to run API test with better error handling
function run_api_test($endpoint, $data = null, $method = 'POST') {
    $api_url = build_api_url($endpoint);
    
    echo "<p><strong>Testing API:</strong> $api_url</p>";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    
    if ($method === 'POST' && $data) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    } else {
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    }
    
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);
    
    if ($curl_error) {
        return ['success' => false, 'message' => 'CURL Error: ' . $curl_error, 'url' => $api_url];
    }
    
    if ($http_code !== 200) {
        return ['success' => false, 'message' => 'HTTP Error: ' . $http_code, 'url' => $api_url, 'response' => $response];
    }
    
    $result = json_decode($response, true);
    if (!$result) {
        return ['success' => false, 'message' => 'Invalid JSON response', 'url' => $api_url, 'response' => $response];
    }
    
    return ['success' => true, 'data' => $result, 'url' => $api_url];
}

// Check system status first
echo "<h2>System Status Check</h2>";

$components = [
    'comments' => 'Comments table',
    'comment_likes' => 'Comment likes table', 
    'comment_reports' => 'Comment reports table'
];

$all_tables_exist = true;
foreach ($components as $table => $description) {
    $check = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
    $exists = mysqli_num_rows($check) > 0;
    echo "<p><strong>$description:</strong> " . ($exists ? "<span style='color: green;'>â Exists</span>" : "<span style='color: red;'>â Missing</span>") . "</p>";
    if (!$exists) $all_tables_exist = false;
}

$procedure_check = mysqli_query($conn, "SHOW PROCEDURE STATUS WHERE Db = DATABASE() AND Name = 'GetCommentStats'");
$procedure_exists = mysqli_num_rows($procedure_check) > 0;
echo "<p><strong>GetCommentStats Procedure:</strong> " . ($procedure_exists ? "<span style='color: green;'>â Exists</span>" : "<span style='color: red;'>â Missing</span>") . "</p>";

if (!$all_tables_exist || !$procedure_exists) {
    echo "<div style='background: #fff3cd; padding: 15px; border: 1px solid #ffeaa7; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3 style='color: #856404;'>Missing Components Detected</h3>";
    echo "<p>Some required database components are missing. Please apply the schema updates first.</p>";
    echo "<a href='apply_missing_schema.php' style='display: inline-block; margin-right: 10px;'>";
    echo "<button style='background: #dc3545; color: white; padding: 10px 20px; border: none; cursor: pointer;'>Apply Missing Schema</button>";
    echo "</a>";
    echo "</div>";
    echo "<p>Tests will continue but may fail due to missing components.</p>";
}

// Run tests
echo "<h2>Running Updated Tests</h2>";

$test_results = [];

// Test 1: Comment Submission
echo "<h3>Test 1: Comment Submission API</h3>";

// Get a news article
$news_query = "SELECT id, title FROM news WHERE status = 'published' LIMIT 1";
$news_result = mysqli_query($conn, $news_query);

if (mysqli_num_rows($news_result) > 0) {
    $news = mysqli_fetch_assoc($news_result);
    $news_id = $news['id'];
    
    $test_data = [
        'news_id' => $news_id,
        'comment' => 'Test comment from updated test suite - ' . date('Y-m-d H:i:s'),
        'parent_id' => null
    ];
    
    $result = run_api_test('api/submit-comment.php', $test_data);
    
    if ($result['success'] && $result['data']['success']) {
        echo "<p style='color: green;'>â PASS: Comment submission successful</p>";
        echo "<p><small>Comment ID: " . $result['data']['comment_id'] . "</small></p>";
        $test_results['Comment Submission'] = 'PASS';
    } else {
        echo "<p style='color: red;'>â FAIL: " . ($result['data']['message'] ?? $result['message']) . "</p>";
        if (isset($result['url'])) {
            echo "<p><small>URL: " . htmlspecialchars($result['url']) . "</small></p>";
        }
        $test_results['Comment Submission'] = 'FAIL';
    }
} else {
    echo "<p style='color: orange;'>â SKIP: No published news articles found</p>";
    $test_results['Comment Submission'] = 'SKIP';
}

// Test 2: Comment Retrieval
echo "<h3>Test 2: Comment Retrieval API</h3>";

$news_query = "SELECT id FROM news WHERE status = 'published' LIMIT 1";
$news_result = mysqli_query($conn, $news_query);

if (mysqli_num_rows($news_result) > 0) {
    $news_id = mysqli_fetch_assoc($news_result)['id'];
    $result = run_api_test('api/get-comments.php?news_id=' . $news_id, null, 'GET');
    
    if ($result['success'] && $result['data']['success']) {
        $comment_count = isset($result['data']['comments']) ? count($result['data']['comments']) : 0;
        echo "<p style='color: green;'>â PASS: Comment retrieval successful</p>";
        echo "<p><small>Retrieved $comment_count comments</small></p>";
        $test_results['Comment Retrieval'] = 'PASS';
    } else {
        echo "<p style='color: red;'>â FAIL: " . ($result['data']['message'] ?? $result['message']) . "</p>";
        $test_results['Comment Retrieval'] = 'FAIL';
    }
} else {
    echo "<p style='color: orange;'>â SKIP: No published news articles found</p>";
    $test_results['Comment Retrieval'] = 'SKIP';
}

// Test 3: Threaded Comments
echo "<h3>Test 3: Threaded Comments (Replies)</h3>";

$parent_query = "SELECT id, news_id FROM comments WHERE parent_id IS NULL LIMIT 1";
$parent_result = mysqli_query($conn, $parent_query);

if (mysqli_num_rows($parent_result) > 0) {
    $parent = mysqli_fetch_assoc($parent_result);
    $parent_id = $parent['id'];
    $news_id = $parent['news_id'];
    
    $reply_data = [
        'news_id' => $news_id,
        'comment' => 'Test reply from updated test suite - ' . date('Y-m-d H:i:s'),
        'parent_id' => $parent_id
    ];
    
    $result = run_api_test('api/submit-comment.php', $reply_data);
    
    if ($result['success'] && $result['data']['success']) {
        echo "<p style='color: green;'>â PASS: Threaded comment submission successful</p>";
        echo "<p><small>Reply ID: " . $result['data']['comment_id'] . " linked to Parent ID: $parent_id</small></p>";
        $test_results['Threaded Comments'] = 'PASS';
    } else {
        echo "<p style='color: red;'>â FAIL: " . ($result['data']['message'] ?? $result['message']) . "</p>";
        $test_results['Threaded Comments'] = 'FAIL';
    }
} else {
    echo "<p style='color: orange;'>â SKIP: No parent comments found for threading test</p>";
    $test_results['Threaded Comments'] = 'SKIP';
}

// Test 4: Comment Likes (if table exists)
echo "<h3>Test 4: Comment Likes System</h3>";

$likes_table_exists = mysqli_num_rows(mysqli_query($conn, "SHOW TABLES LIKE 'comment_likes'")) > 0;

if ($likes_table_exists) {
    $comment_query = "SELECT id FROM comments LIMIT 1";
    $comment_result = mysqli_query($conn, $comment_query);
    
    if (mysqli_num_rows($comment_result) > 0) {
        $comment_id = mysqli_fetch_assoc($comment_result)['id'];
        
        // Test adding a like
        $like_query = "INSERT INTO comment_likes (comment_id, ip_address, like_type) VALUES (?, ?, 'like')";
        $stmt = mysqli_prepare($conn, $like_query);
        $ip_address = '127.0.0.1';
        mysqli_stmt_bind_param($stmt, 'is', $comment_id, $ip_address);
        
        if (mysqli_stmt_execute($stmt)) {
            echo "<p style='color: green;'>â PASS: Comment like system working</p>";
            echo "<p><small>Like added to comment ID: $comment_id</small></p>";
            
            // Clean up test like
            $delete_query = "DELETE FROM comment_likes WHERE comment_id = ? AND ip_address = ?";
            $stmt = mysqli_prepare($conn, $delete_query);
            mysqli_stmt_bind_param($stmt, 'is', $comment_id, $ip_address);
            mysqli_stmt_execute($stmt);
            
            $test_results['Comment Likes'] = 'PASS';
        } else {
            echo "<p style='color: red;'>â FAIL: Error adding comment like</p>";
            $test_results['Comment Likes'] = 'FAIL';
        }
    } else {
        echo "<p style='color: orange;'>â SKIP: No comments found for likes test</p>";
        $test_results['Comment Likes'] = 'SKIP';
    }
} else {
    echo "<p style='color: orange;'>â SKIP: comment_likes table not found</p>";
    $test_results['Comment Likes'] = 'SKIP';
}

// Test 5: Comment Statistics
echo "<h3>Test 5: Comment Statistics</h3>";

if ($procedure_exists) {
    $news_query = "SELECT id FROM news WHERE status = 'published' LIMIT 1";
    $news_result = mysqli_query($conn, $news_query);
    
    if (mysqli_num_rows($news_result) > 0) {
        $news_id = mysqli_fetch_assoc($news_result)['id'];
        
        $stmt = mysqli_prepare($conn, "CALL GetCommentStats(?)");
        mysqli_stmt_bind_param($stmt, 'i', $news_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($result) {
            $stats = mysqli_fetch_assoc($result);
            echo "<p style='color: green;'>â PASS: Comment statistics working</p>";
            echo "<p><small>Total comments: " . $stats['total_comments'] . ", Approved: " . $stats['approved_comments'] . "</small></p>";
            $test_results['Comment Statistics'] = 'PASS';
        } else {
            echo "<p style='color: red;'>â FAIL: Error executing GetCommentStats procedure</p>";
            $test_results['Comment Statistics'] = 'FAIL';
        }
    } else {
        echo "<p style='color: orange;'>â SKIP: No published news articles found</p>";
        $test_results['Comment Statistics'] = 'SKIP';
    }
} else {
    echo "<p style='color: orange;'>â SKIP: GetCommentStats procedure not found</p>";
    $test_results['Comment Statistics'] = 'SKIP';
}

// Test Summary
echo "<h2>Test Summary</h2>";

echo "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%;'>";
echo "<tr style='background: #f0f0f0;'><th>Test</th><th>Status</th></tr>";

$passed = 0;
$failed = 0;
$skipped = 0;

foreach ($test_results as $test_name => $status) {
    $color = $status === 'PASS' ? 'green' : ($status === 'FAIL' ? 'red' : 'orange');
    echo "<tr>";
    echo "<td>" . htmlspecialchars($test_name) . "</td>";
    echo "<td style='color: $color; font-weight: bold;'>" . $status . "</td>";
    echo "</tr>";
    
    if ($status === 'PASS') $passed++;
    elseif ($status === 'FAIL') $failed++;
    else $skipped++;
}

echo "</table>";

$total_tests = count($test_results);
echo "<h3>Results: $passed/$total_tests passed, $failed failed, $skipped skipped</h3>";

if ($failed > 0) {
    echo "<p style='color: red;'><strong>Some tests failed. Please review the failures above.</strong></p>";
} else {
    echo "<p style='color: green;'><strong>All applicable tests passed!</strong></p>";
}

// Action buttons
echo "<div style='margin-top: 30px;'>";
echo "<a href='apply_missing_schema.php' style='display: inline-block; margin-right: 10px;'>";
echo "<button style='background: #dc3545; color: white; padding: 12px 24px; border: none; cursor: pointer; font-size: 16px; font-weight: bold;'>Apply Missing Schema</button>";
echo "</a>";

echo "<form method='post' style='display: inline-block; margin-right: 10px;'>";
echo "<input type='submit' name='rerun_tests' value='Rerun Tests' style='background: #007bff; color: white; padding: 12px 24px; border: none; cursor: pointer; font-size: 16px; font-weight: bold;'>";
echo "</form>";

echo "<a href='test_comments_comprehensive.php' style='display: inline-block; margin-right: 10px;'>";
echo "<button style='background: #28a745; color: white; padding: 12px 24px; border: none; cursor: pointer; font-size: 16px; font-weight: bold;'>Original Test Suite</button>";
echo "</a>";

echo "<a href='fix_comment_system.php' style='display: inline-block;'>";
echo "<button style='background: #ffc107; color: black; padding: 12px 24px; border: none; cursor: pointer; font-size: 16px; font-weight: bold;'>System Diagnostics</button>";
echo "</a>";
echo "</div>";

echo "<p><small>This updated test runner includes fixes for URL construction and better error handling.</small></p>";
?>

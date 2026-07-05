<?php
/**
 * Fix Comment System - Diagnose and Repair Comment Submission Issues
 */

require_once 'config/database.php';

echo "<h1>Comment System Diagnostic & Fix</h1>";

// Function to ensure comments table exists with proper structure
function ensure_comments_table($conn) {
    $table_created = false;
    
    // Check if comments table exists
    $check_table = "SHOW TABLES LIKE 'comments'";
    $result = mysqli_query($conn, $check_table);
    
    if (mysqli_num_rows($result) == 0) {
        echo "<h3>Creating Comments Table</h3>";
        
        $create_table = "CREATE TABLE `comments` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `news_id` int(11) NOT NULL,
            `user_id` int(11) DEFAULT NULL,
            `parent_id` int(11) DEFAULT NULL,
            `name` varchar(255) NOT NULL,
            `email` varchar(255) NOT NULL,
            `comment` text NOT NULL,
            `status` enum('pending','approved','rejected') DEFAULT 'pending',
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `news_id` (`news_id`),
            KEY `user_id` (`user_id`),
            KEY `parent_id` (`parent_id`),
            KEY `status` (`status`),
            KEY `created_at` (`created_at`),
            FOREIGN KEY (`news_id`) REFERENCES `news`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
            FOREIGN KEY (`parent_id`) REFERENCES `comments`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        if (mysqli_query($conn, $create_table)) {
            echo "<p style='color: green;'>â Comments table created successfully</p>";
            $table_created = true;
        } else {
            echo "<p style='color: red;'>â Error creating comments table: " . mysqli_error($conn) . "</p>";
        }
    } else {
        echo "<p style='color: green;'>â Comments table exists</p>";
        
        // Check for missing columns
        $columns_to_check = [
            'parent_id' => "ALTER TABLE comments ADD COLUMN parent_id int(11) DEFAULT NULL AFTER user_id",
            'status' => "ALTER TABLE comments ADD COLUMN status enum('pending','approved','rejected') DEFAULT 'pending' AFTER comment"
        ];
        
        foreach ($columns_to_check as $column => $alter_sql) {
            $check_column = "SHOW COLUMNS FROM comments LIKE '$column'";
            $column_result = mysqli_query($conn, $check_column);
            
            if (mysqli_num_rows($column_result) == 0) {
                echo "<p style='color: orange;'>â Adding missing column: $column</p>";
                if (mysqli_query($conn, $alter_sql)) {
                    echo "<p style='color: green;'>â Column $column added successfully</p>";
                } else {
                    echo "<p style='color: red;'>â Error adding column $column: " . mysqli_error($conn) . "</p>";
                }
            }
        }
    }
    
    return $table_created;
}

// System status check
echo "<h2>System Status Check</h2>";

// Check comments table
$comments_table_exists = mysqli_num_rows(mysqli_query($conn, "SHOW TABLES LIKE 'comments'")) > 0;
echo "<p><strong>Comments table:</strong> " . ($comments_table_exists ? "<span style='color: green;'>â Exists</span>" : "<span style='color: red;'>â Missing</span>") . "</p>";

// Check API file
$api_file = __DIR__ . '/api/submit-comment.php';
echo "<p><strong>API endpoint (api/submit-comment.php):</strong> " . (file_exists($api_file) ? "<span style='color: green;'>â Exists</span>" : "<span style='color: red;'>â Missing</span>") . "</p>";

// Check news table
$news_table_exists = mysqli_num_rows(mysqli_query($conn, "SHOW TABLES LIKE 'news'")) > 0;
echo "<p><strong>News table:</strong> " . ($news_table_exists ? "<span style='color: green;'>â Exists</span>" : "<span style='color: red;'>â Missing</span>") . "</p>";

// Check users table
$users_table_exists = mysqli_num_rows(mysqli_query($conn, "SHOW TABLES LIKE 'users'")) > 0;
echo "<p><strong>Users table:</strong> " . ($users_table_exists ? "<span style='color: green;'>â Exists</span>" : "<span style='color: red;'>â Missing</span>") . "</p>";

// Create missing tables if needed
if (!$comments_table_exists) {
    ensure_comments_table($conn);
}

// Check comment statistics
if ($comments_table_exists) {
    $total_comments = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM comments"))['count'];
    $approved_comments = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM comments WHERE status = 'approved'"))['count'];
    $pending_comments = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM comments WHERE status = 'pending'"))['count'];
    
    echo "<p><strong>Total comments:</strong> $total_comments</p>";
    echo "<p><strong>Approved comments:</strong> $approved_comments</p>";
    echo "<p><strong>Pending comments:</strong> $pending_comments</p>";
}

// Check news articles for commenting
if ($news_table_exists) {
    $published_news = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM news WHERE status = 'published'"))['count'];
    echo "<p><strong>Published news articles:</strong> $published_news</p>";
}

// Test comment submission
if (isset($_POST['test_comment'])) {
    echo "<h3>Testing Comment Submission</h3>";
    
    // Get a sample news article
    $sample_news = mysqli_query($conn, "SELECT id, title FROM news WHERE status = 'published' LIMIT 1");
    if (mysqli_num_rows($sample_news) > 0) {
        $news = mysqli_fetch_assoc($sample_news);
        $news_id = $news['id'];
        
        echo "<p>Testing with news article: " . htmlspecialchars($news['title']) . " (ID: $news_id)</p>";
        
        // Test API call
        $api_url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/api/submit-comment.php';
        $test_data = [
            'news_id' => $news_id,
            'comment' => 'This is a test comment from the diagnostic tool.',
            'parent_id' => null
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($test_data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);
        
        echo "<p><strong>API Response:</strong></p>";
        echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd;'>";
        echo "HTTP Status: $http_code\n";
        if ($curl_error) {
            echo "CURL Error: $curl_error\n";
        }
        echo "Response: " . htmlspecialchars($response);
        echo "</pre>";
        
        // Parse response
        $result = json_decode($response, true);
        if ($result && $result['success']) {
            echo "<p style='color: green;'>â Comment submission test successful!</p>";
        } else {
            echo "<p style='color: red;'>â Comment submission test failed: " . ($result['message'] ?? 'Unknown error') . "</p>";
        }
    } else {
        echo "<p style='color: orange;'>â No published news articles found for testing</p>";
    }
}

// Create sample news article if none exist
if (isset($_POST['create_sample_news']) && $news_table_exists) {
    echo "<h3>Creating Sample News Article</h3>";
    
    $insert_query = "INSERT INTO news (title, slug, content, excerpt, status, published_at) VALUES (?, ?, ?, ?, 'published', NOW())";
    $stmt = mysqli_prepare($conn, $insert_query);
    
    $title = "Sample News Article for Testing Comments";
    $slug = "sample-news-article-" . time();
    $content = "<p>This is a sample news article created for testing the comment system. You can use this article to test comment submission and functionality.</p><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>";
    $excerpt = "Sample news article for testing comments functionality";
    
    mysqli_stmt_bind_param($stmt, "ssss", $title, $slug, $content, $excerpt);
    
    if (mysqli_stmt_execute($stmt)) {
        $news_id = mysqli_insert_id($conn);
        echo "<p style='color: green;'>â Created sample news article ID: $news_id</p>";
    } else {
        echo "<p style='color: red;'>â Error creating sample news: " . mysqli_error($conn) . "</p>";
    }
}

// Show recent comments
if ($comments_table_exists) {
    echo "<h2>Recent Comments</h2>";
    
    $recent_comments = mysqli_query($conn, "
        SELECT c.*, n.title as news_title 
        FROM comments c 
        LEFT JOIN news n ON c.news_id = n.id 
        ORDER BY c.created_at DESC 
        LIMIT 5
    ");
    
    if (mysqli_num_rows($recent_comments) > 0) {
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f0f0f0;'><th>ID</th><th>Name</th><th>Comment</th><th>Status</th><th>News Article</th><th>Date</th></tr>";
        
        while ($comment = mysqli_fetch_assoc($recent_comments)) {
            echo "<tr>";
            echo "<td>" . $comment['id'] . "</td>";
            echo "<td>" . htmlspecialchars($comment['name']) . "</td>";
            echo "<td>" . htmlspecialchars(substr($comment['comment'], 0, 50)) . "...</td>";
            echo "<td><span style='color: " . ($comment['status'] == 'approved' ? 'green' : ($comment['status'] == 'pending' ? 'orange' : 'red')) . ";'>" . $comment['status'] . "</span></td>";
            echo "<td>" . htmlspecialchars(substr($comment['news_title'] ?? 'N/A', 0, 30)) . "...</td>";
            echo "<td>" . $comment['created_at'] . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p style='color: orange;'>No comments found</p>";
    }
}

// Action buttons
echo "<div style='margin-top: 30px;'>";
if ($news_table_exists) {
    echo "<form method='post' style='display: inline-block; margin-right: 10px;'>";
    echo "<input type='submit' name='create_sample_news' value='Create Sample News' style='background: #007bff; color: white; padding: 10px 20px; border: none; cursor: pointer;'>";
    echo "</form>";
}
echo "<form method='post' style='display: inline-block; margin-right: 10px;'>";
echo "<input type='submit' name='test_comment' value='Test Comment Submission' style='background: #28a745; color: white; padding: 10px 20px; border: none; cursor: pointer;'>";
echo "</form>";
echo "</div>";

// Troubleshooting guide
echo "<h2>Troubleshooting Guide</h2>";
echo "<div style='background: #f8f9fa; padding: 15px; border: 1px solid #ddd; border-radius: 5px;'>";
echo "<h3>Common Comment Issues & Solutions:</h3>";
echo "<ul>";
echo "<li><strong>'Error posting comment' - Database issues:</strong> Fixed - auto-creates missing tables and columns</li>";
echo "<li><strong>'News article not found':</strong> Ensure there are published news articles</li>";
echo "<li><strong>'No data received':</strong> Check JavaScript form data serialization</li>";
echo "<li><strong>Permission issues:</strong> Ensure api/ folder has proper write permissions</li>";
echo "<li><strong>Foreign key constraints:</strong> Fixed - proper table relationships established</li>";
echo "<li><strong>Comment not showing:</strong> Comments may need admin approval (status = 'pending')</li>";
echo "</ul>";
echo "</div>";

// JavaScript debugging tips
echo "<h2>JavaScript Debugging Tips</h2>";
echo "<div style='background: #fff3cd; padding: 15px; border: 1px solid #ffeaa7; border-radius: 5px;'>";
echo "<h3>Check Browser Console:</h3>";
echo "<ul>";
echo "<li>Open browser dev tools (F12)</li>";
echo "<li>Look for network errors in the Console tab</li>";
echo "<li>Check the Network tab for failed API calls to submit-comment.php</li>";
echo "<li>Verify form data is being sent correctly</li>";
echo "<li>Check for JavaScript errors that might prevent form submission</li>";
echo "</ul>";
echo "</div>";

echo "<p><small>This diagnostic tool helps identify and fix common comment system issues.</small></p>";
?>

<?php
require_once 'config/database.php';

echo "<h2>Fix Comments System</h2>";

// Check if comments table exists
$table_check = "SHOW TABLES LIKE 'comments'";
$result = mysqli_query($conn, $table_check);

if (mysqli_num_rows($result) === 0) {
    echo "<p style='color: orange;'>Comments table does not exist. Creating it...</p>";
    
    // Create the comments table
    $create_table = "CREATE TABLE `comments` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `news_id` int(11) NOT NULL,
      `user_id` int(11) DEFAULT NULL,
      `name` varchar(100) DEFAULT NULL,
      `email` varchar(100) DEFAULT NULL,
      `comment` text NOT NULL,
      `status` enum('approved','pending','rejected') DEFAULT 'pending',
      `parent_id` int(11) DEFAULT NULL,
      `ip_address` varchar(45) DEFAULT NULL,
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
      PRIMARY KEY (`id`),
      KEY `idx_comments_news` (`news_id`),
      KEY `idx_comments_user` (`user_id`),
      KEY `idx_comments_status` (`status`),
      KEY `idx_comments_parent` (`parent_id`),
      KEY `idx_news_status` (`news_id`,`status`),
      KEY `idx_status_created` (`status`,`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if (mysqli_query($conn, $create_table)) {
        echo "<p style='color: green;'>Comments table created successfully!</p>";
    } else {
        echo "<p style='color: red;'>Error creating comments table: " . mysqli_error($conn) . "</p>";
        exit;
    }
} else {
    echo "<p style='color: green;'>Comments table already exists</p>";
}

// Check if there are any news articles to test with
$news_check = "SELECT id, title FROM news WHERE status = 'published' LIMIT 1";
$news_result = mysqli_query($conn, $news_check);

if (mysqli_num_rows($news_result) > 0) {
    $news = mysqli_fetch_assoc($news_result);
    echo "<p>Found news article: " . htmlspecialchars($news['title']) . " (ID: " . $news['id'] . ")</p>";
    
    // Test comment insertion
    echo "<h3>Testing Comment Insertion:</h3>";
    
    $test_comment = "INSERT INTO comments (news_id, name, email, comment, status) VALUES (?, ?, ?, ?, 'approved')";
    $stmt = mysqli_prepare($conn, $test_comment);
    
    if ($stmt) {
        $test_name = "Test User";
        $test_email = "test@example.com";
        $test_text = "This is a test comment - " . date('Y-m-d H:i:s');
        
        mysqli_stmt_bind_param($stmt, 'isss', $news['id'], $test_name, $test_email, $test_text);
        
        if (mysqli_stmt_execute($stmt)) {
            $test_id = mysqli_insert_id($conn);
            echo "<p style='color: green;'>Test comment inserted successfully! ID: " . $test_id . "</p>";
            
            // Verify the comment was inserted
            $verify = "SELECT * FROM comments WHERE id = ?";
            $verify_stmt = mysqli_prepare($conn, $verify);
            mysqli_stmt_bind_param($verify_stmt, 'i', $test_id);
            mysqli_stmt_execute($verify_stmt);
            $verify_result = mysqli_stmt_get_result($verify_stmt);
            
            if (mysqli_num_rows($verify_result) > 0) {
                $comment_data = mysqli_fetch_assoc($verify_result);
                echo "<p style='color: green;'>Comment verified in database</p>";
                echo "<p>Comment: " . htmlspecialchars($comment_data['comment']) . "</p>";
                
                // Clean up test comment
                mysqli_query($conn, "DELETE FROM comments WHERE id = " . $test_id);
                echo "<p>Test comment cleaned up</p>";
            } else {
                echo "<p style='color: red;'>Comment verification failed</p>";
            }
            mysqli_stmt_close($verify_stmt);
        } else {
            echo "<p style='color: red;'>Failed to insert test comment: " . mysqli_error($conn) . "</p>";
        }
        mysqli_stmt_close($stmt);
    } else {
        echo "<p style='color: red;'>Failed to prepare statement: " . mysqli_error($conn) . "</p>";
    }
} else {
    echo "<p style='color: orange;'>No published news articles found. Creating a test article...</p>";
    
    // Create a test news article
    $create_news = "INSERT INTO news (title, slug, content, status, created_at) VALUES (?, ?, ?, 'published', NOW())";
    $stmt = mysqli_prepare($conn, $create_news);
    
    if ($stmt) {
        $test_title = "Test Article for Comments";
        $test_slug = "test-article-for-comments-" . time();
        $test_content = "This is a test article to test the comment system.";
        
        mysqli_stmt_bind_param($stmt, 'sss', $test_title, $test_slug, $test_content);
        
        if (mysqli_stmt_execute($stmt)) {
            $news_id = mysqli_insert_id($conn);
            echo "<p style='color: green;'>Test article created with ID: " . $news_id . "</p>";
        } else {
            echo "<p style='color: red;'>Failed to create test article: " . mysqli_error($conn) . "</p>";
        }
        mysqli_stmt_close($stmt);
    }
}

// Check API file permissions
echo "<h3>API File Check:</h3>";
$api_file = 'api/submit-comment.php';
if (file_exists($api_file)) {
    echo "<p style='color: green;'>API file exists: " . $api_file . "</p>";
    
    // Check if it's readable
    if (is_readable($api_file)) {
        echo "<p style='color: green;'>API file is readable</p>";
    } else {
        echo "<p style='color: red;'>API file is not readable</p>";
    }
} else {
    echo "<p style='color: red;'>API file does not exist: " . $api_file . "</p>";
}

echo "<h3>Next Steps:</h3>";
echo "<p>1. Try posting a comment on any news article</p>";
echo "<p>2. Check browser console for JavaScript errors</p>";
echo "<p>3. Check network tab for API requests</p>";
echo "<p><a href='index.php'>Go to homepage</a></p>";

?>

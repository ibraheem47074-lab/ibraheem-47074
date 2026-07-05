<?php
// Quick fix for post_likes table error
require_once 'config/database.php';

echo "<h2>Quick Fix: Post Likes Table</h2>";

// Direct table creation
$sql = "CREATE TABLE IF NOT EXISTS post_likes (
    id INT(11) NOT NULL AUTO_INCREMENT,
    news_id INT(11) NOT NULL,
    user_id INT(11) DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent TEXT DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_news_id (news_id),
    KEY idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (mysqli_query($conn, $sql)) {
    echo "<p style='color:green; font-size:18px'>â POST_LIKES TABLE CREATED SUCCESSFULLY!</p>";
    echo "<p>The fatal error should now be fixed.</p>";
    
    // Test query
    $test = mysqli_query($conn, "SELECT COUNT(*) as count FROM post_likes");
    $result = mysqli_fetch_assoc($test);
    echo "<p>Current likes in database: " . $result['count'] . "</p>";
    
    echo "<p><a href='index.php' style='font-size:20px; color:blue;'>â Test Index Page Now</a></p>";
    
} else {
    echo "<p style='color:red'>â Error: " . mysqli_error($conn) . "</p>";
}

echo "<p><a href='check_database_status.php'>Check Database Status</a></p>";
?>

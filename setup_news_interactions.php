<?php
/**
 * Create News Interaction Tables
 */

require_once 'config/database.php';

echo "<h1>Creating News Interaction Tables</h1>\n";

// Create news_likes table
echo "<h3>Creating news_likes table...</h3>\n";

$likesTableSQL = "CREATE TABLE IF NOT EXISTS news_likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    news_id INT NOT NULL,
    user_id INT NULL,
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (news_id) REFERENCES news(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_news_user (news_id, user_id),
    INDEX idx_news_ip (news_id, ip_address),
    INDEX idx_created_at (created_at),
    UNIQUE KEY unique_user_like (news_id, user_id),
    UNIQUE KEY unique_ip_like (news_id, ip_address, user_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (mysqli_query($conn, $likesTableSQL)) {
    echo "<p style='color: green;'>✓ news_likes table created successfully</p>\n";
} else {
    echo "<p style='color: red;'>✗ Error creating news_likes table: " . mysqli_error($conn) . "</p>\n";
}

// Create news_shares table
echo "<h3>Creating news_shares table...</h3>\n";

$sharesTableSQL = "CREATE TABLE IF NOT EXISTS news_shares (
    id INT AUTO_INCREMENT PRIMARY KEY,
    news_id INT NOT NULL,
    platform ENUM('facebook', 'twitter', 'whatsapp', 'linkedin', 'telegram', 'email', 'copy', 'unknown') DEFAULT 'unknown',
    user_id INT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (news_id) REFERENCES news(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_news_platform (news_id, platform),
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (mysqli_query($conn, $sharesTableSQL)) {
    echo "<p style='color: green;'>✓ news_shares table created successfully</p>\n";
} else {
    echo "<p style='color: red;'>✗ Error creating news_shares table: " . mysqli_error($conn) . "</p>\n";
}

// Update existing news records to ensure they have proper counts
echo "<h3>Updating news records...</h3>\n";

$updateSQL = "UPDATE news SET 
    likes_count = COALESCE(likes_count, 0),
    share_count = COALESCE(share_count, 0),
    comment_count = COALESCE(comment_count, 0),
    views = COALESCE(views, 0)
    WHERE likes_count IS NULL OR share_count IS NULL OR comment_count IS NULL OR views IS NULL";

if (mysqli_query($conn, $updateSQL)) {
    $affected = mysqli_affected_rows($conn);
    echo "<p style='color: green;'>✓ Updated $affected news records with proper counts</p>\n";
} else {
    echo "<p style='color: orange;'>⚠ No updates needed or error: " . mysqli_error($conn) . "</p>\n";
}

// Test the API
echo "<h3>Testing News Interactions API...</h3>\n";

$testQuery = "SELECT id, title FROM news ORDER BY id DESC LIMIT 1";
$result = mysqli_query($conn, $testQuery);

if ($result && $row = mysqli_fetch_assoc($result)) {
    $newsId = $row['id'];
    
    echo "<p>Testing with article: " . htmlspecialchars($row['title']) . "</p>\n";
    
    // Test the API
    $ch = curl_init();
    $url = 'http://localhost/PK-LIVE%20NEWS/api/news_interactions.php';
    $data = ['action' => 'get_stats', 'news_id' => $newsId];
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $result = json_decode($response, true);
        if ($result['success']) {
            echo "<div style='background: #e8f5e8; padding: 10px; border-left: 4px solid #28a745;'>\n";
            echo "<p><strong>✅ API Test Successful!</strong></p>\n";
            echo "<p>Likes: {$result['likes']}</p>\n";
            echo "<p>Comments: {$result['comments']}</p>\n";
            echo "<p>Shares: {$result['shares']}</p>\n";
            echo "<p>Views: {$result['views']}</p>\n";
            echo "</div>\n";
        } else {
            echo "<p style='color: orange;'>⚠ API returned error: " . htmlspecialchars($result['message']) . "</p>\n";
        }
    } else {
        echo "<p style='color: red;'>✗ API test failed with HTTP code: $httpCode</p>\n";
    }
} else {
    echo "<p style='color: orange;'>⚠ No news articles found for testing</p>\n";
}

echo "<h2>🎯 Setup Complete!</h2>\n";
echo "<div style='background: #f0f8ff; padding: 15px; border-left: 4px solid #007cba;'>\n";
echo "<h4>✅ What was created:</h4>\n";
echo "<ul>\n";
echo "<li>✅ news_likes table - for tracking article likes</li>\n";
echo "<li>✅ news_shares table - for tracking article shares</li>\n";
echo "<li>✅ news_interactions.php API - for real-time interactions</li>\n";
echo "<li>✅ Updated news records with proper counts</li>\n";
echo "</ul>\n";
echo "<p><strong>Real-time interaction system is now ready!</strong></p>\n";
echo "</div>\n";

echo "<p><a href='news.php' target='_blank' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Test News Page</a> | <a href='index.php' target='_blank' style='background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go Home</a></p>\n";
?>

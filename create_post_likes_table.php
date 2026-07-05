<?php
require_once 'config/database.php';

echo "<h2>Create Post Likes Table</h2>";

// Check if table already exists
$check_query = "SHOW TABLES LIKE 'post_likes'";
$result = mysqli_query($conn, $check_query);

if (mysqli_num_rows($result) > 0) {
    echo "<p style='color:green'>â post_likes table already exists</p>";
    
    // Show table structure
    $structure = mysqli_query($conn, "DESCRIBE post_likes");
    echo "<h3>Table Structure:</h3>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    
    while ($row = mysqli_fetch_assoc($structure)) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Show record count
    $count = mysqli_query($conn, "SELECT COUNT(*) as count FROM post_likes");
    $row = mysqli_fetch_assoc($count);
    echo "<p>Total records: " . $row['count'] . "</p>";
    
} else {
    echo "<p style='color:orange'>â Creating post_likes table...</p>";
    
    // Create the table
    $create_query = "CREATE TABLE `post_likes` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `news_id` int(11) NOT NULL,
      `user_id` int(11) DEFAULT NULL,
      `ip_address` varchar(45) DEFAULT NULL,
      `user_agent` text DEFAULT NULL,
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      PRIMARY KEY (`id`),
      KEY `news_id` (`news_id`),
      KEY `user_id` (`user_id`),
      KEY `idx_news_id` (`news_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    if (mysqli_query($conn, $create_query)) {
        echo "<p style='color:green'>â post_likes table created successfully</p>";
        
        // Add some sample data for testing
        $sample_query = "INSERT INTO post_likes (news_id, user_id, ip_address) VALUES 
                        (1, NULL, '127.0.0.1'),
                        (2, NULL, '127.0.0.1'),
                        (1, 1, '192.168.1.1')";
        
        if (mysqli_query($conn, $sample_query)) {
            echo "<p style='color:green'>â Sample data added</p>";
        }
        
    } else {
        echo "<p style='color:red'>â Error creating table: " . mysqli_error($conn) . "</p>";
    }
}

// Test the query that was failing in index.php
echo "<h3>Test Query from index.php</h3>";
$test_query = "SELECT n.*, (SELECT COUNT(*) FROM post_likes WHERE news_id = n.id) as likes_count FROM news n LIMIT 5";

$test_result = mysqli_query($conn, $test_query);
if ($test_result) {
    echo "<p style='color:green'>â Query test passed - no more errors!</p>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>News ID</th><th>Title</th><th>Likes Count</th></tr>";
    
    while ($row = mysqli_fetch_assoc($test_result)) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . substr($row['title'] ?? 'No title', 0, 50) . "...</td>";
        echo "<td>" . $row['likes_count'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color:red'>â Query test failed: " . mysqli_error($conn) . "</p>";
}

echo "<p><a href='index.php'>Test Index Page</a> | <a href='check_database_status.php'>Database Status</a></p>";
?>

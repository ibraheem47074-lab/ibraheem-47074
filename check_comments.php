<?php
require_once 'config/database.php';

echo "<h2>Comments System Check</h2>";

// Check if comments table exists
$table_check = "SHOW TABLES LIKE 'comments'";
$result = mysqli_query($conn, $table_check);

if (mysqli_num_rows($result) > 0) {
    echo "<p style='color: green;'>Comments table exists</p>";
    
    // Check table structure
    $structure = mysqli_query($conn, "DESCRIBE comments");
    echo "<h3>Comments Table Structure:</h3>";
    echo "<table border='1'>";
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
    
    // Check if there are any comments
    $count_query = "SELECT COUNT(*) as count FROM comments";
    $count_result = mysqli_query($conn, $count_query);
    $count = mysqli_fetch_assoc($count_result);
    echo "<p>Total comments in database: " . $count['count'] . "</p>";
    
    // Test comment insertion
    echo "<h3>Testing Comment Insertion:</h3>";
    
    // First check if we have a news article to test with
    $news_check = "SELECT id, title FROM news WHERE status = 'published' LIMIT 1";
    $news_result = mysqli_query($conn, $news_check);
    
    if (mysqli_num_rows($news_result) > 0) {
        $news = mysqli_fetch_assoc($news_result);
        echo "<p>Testing with news article: " . $news['title'] . " (ID: " . $news['id'] . ")</p>";
        
        // Try to insert a test comment
        $test_comment = "INSERT INTO comments (news_id, name, email, comment, status) VALUES (?, ?, ?, ?, 'approved')";
        $stmt = mysqli_prepare($conn, $test_comment);
        
        if ($stmt) {
            $test_name = "Test User";
            $test_email = "test@example.com";
            $test_text = "This is a test comment - " . date('Y-m-d H:i:s');
            
            mysqli_stmt_bind_param($stmt, 'isss', $news['id'], $test_name, $test_email, $test_text);
            
            if (mysqli_stmt_execute($stmt)) {
                echo "<p style='color: green;'>Test comment inserted successfully</p>";
                $test_id = mysqli_insert_id($conn);
                echo "<p>Test comment ID: " . $test_id . "</p>";
                
                // Clean up test comment
                mysqli_query($conn, "DELETE FROM comments WHERE id = " . $test_id);
                echo "<p>Test comment cleaned up</p>";
            } else {
                echo "<p style='color: red;'>Failed to insert test comment: " . mysqli_error($conn) . "</p>";
            }
            mysqli_stmt_close($stmt);
        } else {
            echo "<p style='color: red;'>Failed to prepare statement: " . mysqli_error($conn) . "</p>";
        }
    } else {
        echo "<p style='color: orange;'>No published news articles found to test with</p>";
    }
    
} else {
    echo "<p style='color: red;'>Comments table does not exist</p>";
    
    // Create the table
    echo "<h3>Creating Comments Table:</h3>";
    $create_table = "CREATE TABLE IF NOT EXISTS comments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        news_id INT NOT NULL,
        user_id INT NULL,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        comment TEXT NOT NULL,
        status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
        parent_id INT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (news_id) REFERENCES news(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
        FOREIGN KEY (parent_id) REFERENCES comments(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    
    if (mysqli_query($conn, $create_table)) {
        echo "<p style='color: green;'>Comments table created successfully</p>";
    } else {
        echo "<p style='color: red;'>Failed to create comments table: " . mysqli_error($conn) . "</p>";
    }
}

// Check API file exists
echo "<h3>API File Check:</h3>";
if (file_exists('api/submit-comment.php')) {
    echo "<p style='color: green;'>api/submit-comment.php exists</p>";
} else {
    echo "<p style='color: red;'>api/submit-comment.php does not exist</p>";
}

// Check database connection
echo "<h3>Database Connection:</h3>";
if ($conn) {
    echo "<p style='color: green;'>Database connection successful</p>";
    echo "<p>Database: " . DB_NAME . "</p>";
    echo "<p>Host: " . DB_HOST . "</p>";
} else {
    echo "<p style='color: red;'>Database connection failed</p>";
}

?>

<?php
require_once 'config/database.php';

echo "Checking Comments Table Structure\n";
echo "===================================\n\n";

// Check if comments table exists
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'comments'");
if (mysqli_num_rows($table_check) == 0) {
    echo "❌ Comments table does not exist. Creating it...\n";
    
    // Create comments table
    $create_table = "CREATE TABLE comments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        news_id INT NOT NULL,
        user_id INT NULL,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        comment TEXT NOT NULL,
        status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (news_id) REFERENCES news(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
        INDEX idx_news_id (news_id),
        INDEX idx_status (status),
        INDEX idx_created_at (created_at)
    )";
    
    if (mysqli_query($conn, $create_table)) {
        echo "✅ Comments table created successfully\n";
    } else {
        echo "❌ Error creating comments table: " . mysqli_error($conn) . "\n";
        exit(1);
    }
} else {
    echo "✅ Comments table already exists\n";
    
    // Show table structure
    echo "\nTable Structure:\n";
    $structure = mysqli_query($conn, "DESCRIBE comments");
    while ($row = mysqli_fetch_assoc($structure)) {
        echo "- {$row['Field']}: {$row['Type']} ({$row['Null']})\n";
    }
    
    // Check for sample data
    $count_query = "SELECT COUNT(*) as total FROM comments";
    $result = mysqli_query($conn, $count_query);
    $count = mysqli_fetch_assoc($result);
    echo "\nTotal comments: " . $count['total'] . "\n";
    
    // Show recent comments
    $recent_query = "SELECT c.*, n.title as news_title FROM comments c 
                    LEFT JOIN news n ON c.news_id = n.id 
                    ORDER BY c.created_at DESC LIMIT 5";
    $recent_result = mysqli_query($conn, $recent_query);
    
    if (mysqli_num_rows($recent_result) > 0) {
        echo "\nRecent Comments:\n";
        while ($comment = mysqli_fetch_assoc($recent_result)) {
            echo "- ID: {$comment['id']}, News: {$comment['news_title']}, Status: {$comment['status']}\n";
        }
    }
}

echo "\nChecking comment submission API...\n";

// Test comment submission
$test_news_id = 1;
$test_comment = "This is a test comment";

// First check if there's a news article
$news_check = "SELECT id, title FROM news WHERE status = 'published' LIMIT 1";
$news_result = mysqli_query($conn, $news_check);

if (mysqli_num_rows($news_result) > 0) {
    $news = mysqli_fetch_assoc($news_result);
    echo "✅ Found news article: {$news['title']} (ID: {$news['id']})\n";
    
    // Test inserting a comment
    $test_insert = "INSERT INTO comments (news_id, name, email, comment, status) VALUES (?, ?, ?, ?, 'approved')";
    $stmt = mysqli_prepare($conn, $test_insert);
    
    if ($stmt) {
        $test_name = "Test User";
        $test_email = "test@example.com";
        
        mysqli_stmt_bind_param($stmt, 'isss', $news['id'], $test_name, $test_email, $test_comment);
        
        if (mysqli_stmt_execute($stmt)) {
            echo "✅ Test comment inserted successfully\n";
        } else {
            echo "❌ Error inserting test comment: " . mysqli_stmt_error($stmt) . "\n";
        }
    } else {
        echo "❌ Error preparing test statement: " . mysqli_error($conn) . "\n";
    }
} else {
    echo "❌ No published news articles found\n";
}

echo "\nDone!\n";
?>

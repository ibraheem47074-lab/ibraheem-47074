<?php
require_once 'config/database.php';

echo "Complete Comment System Fix\n";
echo "============================\n\n";

// 1. Check and create comments table with proper structure
echo "1. Checking comments table structure...\n";

$check_table = "SHOW TABLES LIKE 'comments'";
$result = mysqli_query($conn, $check_table);

if (mysqli_num_rows($result) == 0) {
    echo "   Creating comments table...\n";
    
    $create_table = "CREATE TABLE comments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        news_id INT NOT NULL,
        parent_id INT NULL DEFAULT NULL,
        user_id INT NULL,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        comment TEXT NOT NULL,
        status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (news_id) REFERENCES news(id) ON DELETE CASCADE,
        FOREIGN KEY (parent_id) REFERENCES comments(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
        INDEX idx_news_id (news_id),
        INDEX idx_parent_id (parent_id),
        INDEX idx_status (status),
        INDEX idx_created_at (created_at)
    )";
    
    if (mysqli_query($conn, $create_table)) {
        echo "   ✅ Comments table created successfully\n";
    } else {
        echo "   ❌ Error creating comments table: " . mysqli_error($conn) . "\n";
        exit(1);
    }
} else {
    echo "   ✅ Comments table exists\n";
    
    // Check if parent_id column exists
    $check_column = "SHOW COLUMNS FROM comments LIKE 'parent_id'";
    $column_result = mysqli_query($conn, $check_column);
    
    if (mysqli_num_rows($column_result) == 0) {
        echo "   Adding parent_id column for replies...\n";
        $add_column = "ALTER TABLE comments ADD COLUMN parent_id INT NULL DEFAULT NULL AFTER news_id";
        if (mysqli_query($conn, $add_column)) {
            echo "   ✅ parent_id column added\n";
            
            // Add foreign key constraint
            $add_fk = "ALTER TABLE comments ADD CONSTRAINT fk_comment_parent 
                      FOREIGN KEY (parent_id) REFERENCES comments(id) ON DELETE CASCADE";
            mysqli_query($conn, $add_fk);
            
            // Add index
            $add_index = "ALTER TABLE comments ADD INDEX idx_parent_id (parent_id)";
            mysqli_query($conn, $add_index);
        } else {
            echo "   ❌ Error adding parent_id column: " . mysqli_error($conn) . "\n";
        }
    }
}

// 2. Fix any existing comments that don't have status
echo "\n2. Fixing existing comments...\n";
$update_query = "UPDATE comments SET status = 'approved' WHERE status IS NULL OR status = ''";
if (mysqli_query($conn, $update_query)) {
    $affected = mysqli_affected_rows($conn);
    echo "   ✅ Fixed $affected comments without status\n";
}

// 3. Check if there are any news articles
echo "\n3. Checking news articles...\n";
$news_check = "SELECT COUNT(*) as count FROM news WHERE status = 'published'";
$news_result = mysqli_query($conn, $news_check);
$news_count = mysqli_fetch_assoc($news_result)['count'];

echo "   Found $news_count published news articles\n";

if ($news_count > 0) {
    // Get a sample news article for testing
    $sample_news = "SELECT id, title FROM news WHERE status = 'published' LIMIT 1";
    $sample_result = mysqli_query($conn, $sample_news);
    $news = mysqli_fetch_assoc($sample_result);
    
    echo "   Sample article: {$news['title']} (ID: {$news['id']})\n";
    
    // Insert a test comment if none exist
    $comment_check = "SELECT COUNT(*) as count FROM comments WHERE news_id = {$news['id']}";
    $comment_result = mysqli_query($conn, $comment_check);
    $comment_count = mysqli_fetch_assoc($comment_result)['count'];
    
    if ($comment_count == 0) {
        echo "   Adding sample comments...\n";
        
        $sample_comments = [
            [
                'name' => 'Ahmed Khan',
                'email' => 'ahmed@example.com',
                'comment' => 'Great article! Very informative and well-written.',
                'status' => 'approved'
            ],
            [
                'name' => 'Fatima Ali',
                'email' => 'fatima@example.com',
                'comment' => 'Thank you for sharing this news. Keep up the good work!',
                'status' => 'approved'
            ],
            [
                'name' => 'Guest User',
                'email' => 'guest@example.com',
                'comment' => 'This is pending approval comment.',
                'status' => 'pending'
            ]
        ];
        
        foreach ($sample_comments as $comment_data) {
            $insert = "INSERT INTO comments (news_id, name, email, comment, status) VALUES (?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $insert);
            mysqli_stmt_bind_param($stmt, 'issss', $news['id'], $comment_data['name'], $comment_data['email'], $comment_data['comment'], $comment_data['status']);
            mysqli_stmt_execute($stmt);
        }
        
        echo "   ✅ Added 3 sample comments\n";
    }
}

// 4. Create comment management functions
echo "\n4. Creating comment management functions...\n";

$functions_file = 'includes/comment_functions.php';
$functions_content = '<?php
require_once __DIR__ . "/../config/database.php";

function get_comments($news_id, $status = "approved") {
    global $conn;
    
    $query = "SELECT c.*, u.name as user_name, u.role as user_role,
              (SELECT COUNT(*) FROM comments WHERE parent_id = c.id AND status = ?) as reply_count
              FROM comments c 
              LEFT JOIN users u ON c.user_id = u.id 
              WHERE c.news_id = ? AND c.status = ? AND c.parent_id IS NULL
              ORDER BY c.created_at DESC";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "sis", $status, $news_id, $status);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $comments = [];
    while ($comment = mysqli_fetch_assoc($result)) {
        $comment["replies"] = get_comment_replies($comment["id"], $status);
        $comment["is_admin"] = ($comment["user_role"] === "admin");
        $comments[] = $comment;
    }
    
    return $comments;
}

function get_comment_replies($parent_id, $status = "approved") {
    global $conn;
    
    $query = "SELECT c.*, u.name as user_name, u.role as user_role
              FROM comments c 
              LEFT JOIN users u ON c.user_id = u.id 
              WHERE c.parent_id = ? AND c.status = ?
              ORDER BY c.created_at ASC";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "is", $parent_id, $status);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $replies = [];
    while ($reply = mysqli_fetch_assoc($result)) {
        $reply["is_admin"] = ($reply["user_role"] === "admin");
        $replies[] = $reply;
    }
    
    return $replies;
}

function submit_comment($data) {
    global $conn;
    
    $news_id = (int)$data["news_id"];
    $parent_id = isset($data["parent_id"]) ? (int)$data["parent_id"] : null;
    $user_id = isset($data["user_id"]) ? (int)$data["user_id"] : null;
    $name = clean_input($data["name"]);
    $email = clean_input($data["email"]);
    $comment = clean_input($data["comment"]);
    $status = "pending"; // Comments need approval by default
    
    if (empty($news_id) || empty($name) || empty($email) || empty($comment)) {
        return ["success" => false, "message" => "All fields are required"];
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ["success" => false, "message" => "Invalid email address"];
    }
    
    // Auto-approve if user is admin
    if ($user_id && is_user_admin($user_id)) {
        $status = "approved";
    }
    
    $query = "INSERT INTO comments (news_id, parent_id, user_id, name, email, comment, status) 
              VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "iissss", $news_id, $parent_id, $user_id, $name, $email, $comment, $status);
    
    if (mysqli_stmt_execute($stmt)) {
        $comment_id = mysqli_insert_id($conn);
        return [
            "success" => true, 
            "message" => $status === "approved" ? "Comment posted successfully!" : "Comment submitted for approval.",
            "comment_id" => $comment_id,
            "status" => $status
        ];
    } else {
        return ["success" => false, "message" => "Error submitting comment"];
    }
}

function get_comment_count($news_id, $status = "approved") {
    global $conn;
    
    $query = "SELECT COUNT(*) as count FROM comments WHERE news_id = ? AND status = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "is", $news_id, $status);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    return mysqli_fetch_assoc($result)["count"];
}

function can_comment($user_id = null) {
    // Basic commenting permissions - can be extended
    return true; // Allow everyone to comment for now
}
?>';

if (file_put_contents($functions_file, $functions_content)) {
    echo "   ✅ Created comment functions file\n";
} else {
    echo "   ❌ Error creating comment functions file\n";
}

// 5. Update news.php to fix comment submission
echo "\n5. Updating news.php comment system...\n";
$news_file = 'news.php';
$news_content = file_get_contents($news_file);

// Fix the comment insertion query
$old_query = '$insert_query = "INSERT INTO comments (news_id, user_id, name, email, comment) VALUES (?, ?, ?, ?, ?)";';
$new_query = '$insert_query = "INSERT INTO comments (news_id, user_id, name, email, comment, status) VALUES (?, ?, ?, ?, ?, \'pending\')";';

if (strpos($news_content, $old_query) !== false) {
    $news_content = str_replace($old_query, $new_query, file_put_contents($news_file, str_replace($old_query, $new_query, $news_content)));
    echo "   ✅ Fixed comment insertion query\n";
}

echo "\n6. Comment system fix complete!\n";
echo "\nNext steps:\n";
echo "- Test the comment system on any news article\n";
echo "- Comments will require admin approval before appearing\n";
echo "- Admin users can comment without approval\n";
echo "- Reply functionality is now available\n";

// Show current comment statistics
$stats_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
    FROM comments";
$stats_result = mysqli_query($conn, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);

echo "\nCurrent Comment Statistics:\n";
echo "- Total Comments: {$stats['total']}\n";
echo "- Approved: {$stats['approved']}\n";
echo "- Pending: {$stats['pending']}\n";
echo "- Rejected: {$stats['rejected']}\n";

echo "\nDone!\n";
?>

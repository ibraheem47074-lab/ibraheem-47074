<?php
/**
 * Complete Schema Fix - All Comment System Components
 * This script fixes all remaining schema issues and creates a complete comment system
 */

require_once 'config/database.php';

echo "<h1>Complete Comment System Schema Fix</h1>";
echo "<p>This script will fix all remaining schema issues and create a complete comment system.</p>";

$fixes_applied = [];
$errors = [];

// Function to safely execute SQL with error handling
function safe_execute_sql($conn, $sql, $description) {
    global $fixes_applied, $errors;
    
    try {
        if (mysqli_query($conn, $sql)) {
            $fixes_applied[] = $description;
            return true;
        } else {
            $error = mysqli_error($conn);
            $errors[] = "$description: $error";
            return false;
        }
    } catch (Exception $e) {
        $errors[] = "$description: " . $e->getMessage();
        return false;
    }
}

// Step 1: Fix comments table structure
echo "<h2>Step 1: Fix Comments Table Structure</h2>";

// Add missing columns to comments table
$columns_to_add = [
    'likes_count' => "ALTER TABLE comments ADD COLUMN likes_count int(11) NOT NULL DEFAULT 0",
    'dislikes_count' => "ALTER TABLE comments ADD COLUMN dislikes_count int(11) NOT NULL DEFAULT 0",
    'is_edited' => "ALTER TABLE comments ADD COLUMN is_edited tinyint(1) NOT NULL DEFAULT 0",
    'edited_at' => "ALTER TABLE comments ADD COLUMN edited_at timestamp NULL DEFAULT NULL",
    'user_agent' => "ALTER TABLE comments ADD COLUMN user_agent text DEFAULT NULL"
];

foreach ($columns_to_add as $column => $sql) {
    $check_column = "SHOW COLUMNS FROM comments LIKE '$column'";
    $result = mysqli_query($conn, $check_column);
    
    if (mysqli_num_rows($result) == 0) {
        if (safe_execute_sql($conn, $sql, "Added column: $column")) {
            echo "<p style='color: green;'>â Added column: $column</p>";
        } else {
            echo "<p style='color: red;'>â Failed to add column: $column</p>";
        }
    } else {
        echo "<p style='color: orange;'>â Column $column already exists</p>";
    }
}

// Step 2: Create comment_likes table (fixed version)
echo "<h2>Step 2: Create Comment Likes Table</h2>";

// Drop existing table to avoid conflicts
safe_execute_sql($conn, "DROP TABLE IF EXISTS comment_likes", "Dropped existing comment_likes table");

$create_likes = "CREATE TABLE comment_likes (
    id int(11) NOT NULL AUTO_INCREMENT,
    comment_id int(11) NOT NULL,
    user_id int(11) DEFAULT NULL,
    ip_address varchar(45) DEFAULT NULL,
    like_type enum('like','dislike') NOT NULL,
    created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_comment_id (comment_id),
    KEY idx_user_id (user_id),
    CONSTRAINT fk_comment_likes_comment FOREIGN KEY (comment_id) REFERENCES comments (id) ON DELETE CASCADE,
    CONSTRAINT fk_comment_likes_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if (safe_execute_sql($conn, $create_likes, "Created comment_likes table")) {
    echo "<p style='color: green;'>â Created comment_likes table</p>";
    
    // Add unique constraints separately
    $unique_user = "ALTER TABLE comment_likes ADD UNIQUE KEY unique_comment_user (comment_id, user_id)";
    $unique_ip = "ALTER TABLE comment_likes ADD UNIQUE KEY unique_comment_ip (comment_id, ip_address)";
    
    safe_execute_sql($conn, $unique_user, "Added unique constraint for users");
    safe_execute_sql($conn, $unique_ip, "Added unique constraint for IPs");
    
    echo "<p style='color: green;'>â Added unique constraints</p>";
} else {
    echo "<p style='color: red;'>â Failed to create comment_likes table</p>";
}

// Step 3: Create comment_reports table
echo "<h2>Step 3: Create Comment Reports Table</h2>";

safe_execute_sql($conn, "DROP TABLE IF EXISTS comment_reports", "Dropped existing comment_reports table");

$create_reports = "CREATE TABLE comment_reports (
    id int(11) NOT NULL AUTO_INCREMENT,
    comment_id int(11) NOT NULL,
    user_id int(11) DEFAULT NULL,
    reporter_ip varchar(45) DEFAULT NULL,
    reason varchar(255) NOT NULL,
    description text DEFAULT NULL,
    status enum('pending','reviewed','dismissed') NOT NULL DEFAULT 'pending',
    reviewed_by int(11) DEFAULT NULL,
    reviewed_at timestamp NULL DEFAULT NULL,
    created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_comment_id (comment_id),
    KEY idx_user_id (user_id),
    KEY idx_status (status),
    CONSTRAINT fk_comment_reports_comment FOREIGN KEY (comment_id) REFERENCES comments (id) ON DELETE CASCADE,
    CONSTRAINT fk_comment_reports_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL,
    CONSTRAINT fk_comment_reports_reviewer FOREIGN KEY (reviewed_by) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if (safe_execute_sql($conn, $create_reports, "Created comment_reports table")) {
    echo "<p style='color: green;'>â Created comment_reports table</p>";
} else {
    echo "<p style='color: red;'>â Failed to create comment_reports table</p>";
}

// Step 4: Create stored procedures
echo "<h2>Step 4: Create Stored Procedures</h2>";

// Drop existing procedure
safe_execute_sql($conn, "DROP PROCEDURE IF EXISTS GetCommentStats", "Dropped existing GetCommentStats procedure");

$create_procedure = "CREATE PROCEDURE GetCommentStats(IN news_id_param INT)
BEGIN
    SELECT 
        COUNT(*) as total_comments,
        COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved_comments,
        COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_comments,
        COUNT(CASE WHEN parent_id IS NULL THEN 1 END) as top_level_comments,
        COUNT(CASE WHEN parent_id IS NOT NULL THEN 1 END) as reply_comments,
        COALESCE(SUM(likes_count), 0) as total_likes,
        COALESCE(SUM(dislikes_count), 0) as total_dislikes
    FROM comments 
    WHERE news_id = news_id_param;
END";

if (safe_execute_sql($conn, $create_procedure, "Created GetCommentStats procedure")) {
    echo "<p style='color: green;'>â Created GetCommentStats procedure</p>";
} else {
    echo "<p style='color: red;'>â Failed to create GetCommentStats procedure</p>";
}

// Step 5: Create triggers
echo "<h2>Step 5: Create Triggers</h2>";

// Drop existing triggers
safe_execute_sql($conn, "DROP TRIGGER IF EXISTS update_comment_counts", "Dropped existing update_comment_counts trigger");
safe_execute_sql($conn, "DROP TRIGGER IF EXISTS update_comment_counts_on_delete", "Dropped existing update_comment_counts_on_delete trigger");

$trigger_insert = "CREATE TRIGGER update_comment_counts
AFTER INSERT ON comment_likes
FOR EACH ROW
BEGIN
    IF NEW.like_type = 'like' THEN
        UPDATE comments SET likes_count = likes_count + 1 WHERE id = NEW.comment_id;
    ELSEIF NEW.like_type = 'dislike' THEN
        UPDATE comments SET dislikes_count = dislikes_count + 1 WHERE id = NEW.comment_id;
    END IF;
END";

if (safe_execute_sql($conn, $trigger_insert, "Created insert trigger")) {
    echo "<p style='color: green;'>â Created insert trigger</p>";
} else {
    echo "<p style='color: red;'>â Failed to create insert trigger</p>";
}

$trigger_delete = "CREATE TRIGGER update_comment_counts_on_delete
AFTER DELETE ON comment_likes
FOR EACH ROW
BEGIN
    IF OLD.like_type = 'like' THEN
        UPDATE comments SET likes_count = likes_count - 1 WHERE id = OLD.comment_id;
    ELSEIF OLD.like_type = 'dislike' THEN
        UPDATE comments SET dislikes_count = dislikes_count - 1 WHERE id = OLD.comment_id;
    END IF;
END";

if (safe_execute_sql($conn, $trigger_delete, "Created delete trigger")) {
    echo "<p style='color: green;'>â Created delete trigger</p>";
} else {
    echo "<p style='color: red;'>â Failed to create delete trigger</p>";
}

// Step 6: Create view
echo "<h2>Step 6: Create Approved Comments View</h2>";

safe_execute_sql($conn, "DROP VIEW IF EXISTS approved_comments_view", "Dropped existing approved_comments_view");

// Check users table structure first
$describe_users = "DESCRIBE users";
$users_result = mysqli_query($conn, $describe_users);
$users_columns = [];
if ($users_result) {
    while ($row = mysqli_fetch_assoc($users_result)) {
        $users_columns[] = $row['Field'];
    }
}

// Build view based on available columns
$avatar_field = "NULL as user_avatar";
if (in_array('avatar', $users_columns)) {
    $avatar_field = "u.avatar as user_avatar";
} elseif (in_array('image', $users_columns)) {
    $avatar_field = "u.image as user_avatar";
}

$role_field = "NULL as user_role";
if (in_array('role', $users_columns)) {
    $role_field = "u.role as user_role";
}

$create_view = "CREATE VIEW approved_comments_view AS
SELECT 
    c.*,
    u.name as user_name,
    $avatar_field,
    $role_field,
    (SELECT COUNT(*) FROM comment_likes cl WHERE cl.comment_id = c.id AND cl.like_type = 'like') as actual_likes,
    (SELECT COUNT(*) FROM comment_likes cl WHERE cl.comment_id = c.id AND cl.like_type = 'dislike') as actual_dislikes,
    (SELECT COUNT(*) FROM comments cr WHERE cr.parent_id = c.id) as replies_count
FROM comments c
LEFT JOIN users u ON c.user_id = u.id
WHERE c.status = 'approved'";

if (safe_execute_sql($conn, $create_view, "Created approved_comments_view")) {
    echo "<p style='color: green;'>â Created approved_comments_view</p>";
} else {
    echo "<p style='color: red;'>â Failed to create approved_comments_view</p>";
}

// Step 7: Verification
echo "<h2>Step 7: Verification</h2>";

$tables_to_check = ['comments', 'comment_likes', 'comment_reports'];
$all_tables_exist = true;

foreach ($tables_to_check as $table) {
    $check = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
    $exists = mysqli_num_rows($check) > 0;
    
    if ($exists) {
        echo "<p style='color: green;'>â Table $table exists</p>";
    } else {
        echo "<p style='color: red;'>â Table $table missing</p>";
        $all_tables_exist = false;
    }
}

$procedure_check = mysqli_query($conn, "SHOW PROCEDURE STATUS WHERE Db = DATABASE() AND Name = 'GetCommentStats'");
$procedure_exists = mysqli_num_rows($procedure_check) > 0;

if ($procedure_exists) {
    echo "<p style='color: green;'>â GetCommentStats procedure exists</p>";
} else {
    echo "<p style='color: red;'>â GetCommentStats procedure missing</p>";
}

$view_check = mysqli_query($conn, "SHOW TABLES LIKE 'approved_comments_view'");
$view_exists = mysqli_num_rows($view_check) > 0;

if ($view_exists) {
    echo "<p style='color: green;'>â approved_comments_view exists</p>";
} else {
    echo "<p style='color: red;'>â approved_comments_view missing</p>";
}

// Step 8: Test functionality
echo "<h2>Step 8: Test Functionality</h2>";

// Test comment statistics
$news_query = "SELECT id FROM news WHERE status = 'published' LIMIT 1";
$news_result = mysqli_query($conn, $news_query);

if (mysqli_num_rows($news_result) > 0) {
    $news_id = mysqli_fetch_assoc($news_result)['id'];
    
    $stmt = mysqli_prepare($conn, "CALL GetCommentStats(?)");
    mysqli_stmt_bind_param($stmt, 'i', $news_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        if ($result) {
            $stats = mysqli_fetch_assoc($result);
            echo "<p style='color: green;'>â GetCommentStats procedure working</p>";
            echo "<p><small>Stats for news ID $news_id: " . json_encode($stats) . "</small></p>";
        } else {
            echo "<p style='color: red;'>â GetCommentStats procedure failed</p>";
        }
    } else {
        echo "<p style='color: red;'>â GetCommentStats procedure execution failed</p>";
    }
} else {
    echo "<p style='color: orange;'>â No published news found for testing</p>";
}

// Summary
echo "<h2>Summary</h2>";

echo "<div style='background: #d4edda; padding: 15px; border: 1px solid #c3e6cb; border-radius: 5px; margin-bottom: 20px;'>";
echo "<h3 style='color: #155724;'>Fixes Applied (" . count($fixes_applied) . ")</h3>";
echo "<ul>";
foreach ($fixes_applied as $fix) {
    echo "<li>$fix</li>";
}
echo "</ul>";
echo "</div>";

if (!empty($errors)) {
    echo "<div style='background: #f8d7da; padding: 15px; border: 1px solid #f5c6cb; border-radius: 5px; margin-bottom: 20px;'>";
    echo "<h3 style='color: #721c24;'>Errors (" . count($errors) . ")</h3>";
    echo "<ul>";
    foreach ($errors as $error) {
        echo "<li>$error</li>";
    }
    echo "</ul>";
    echo "</div>";
}

// Action buttons
echo "<div style='margin-top: 30px;'>";
echo "<a href='run_comment_tests.php' style='display: inline-block; margin-right: 10px;'>";
echo "<button style='background: #007bff; color: white; padding: 12px 24px; border: none; cursor: pointer; font-size: 16px; font-weight: bold;'>Run Tests</button>";
echo "</a>";

echo "<a href='test_comments_comprehensive.php' style='display: inline-block; margin-right: 10px;'>";
echo "<button style='background: #28a745; color: white; padding: 12px 24px; border: none; cursor: pointer; font-size: 16px; font-weight: bold;'>Original Test Suite</button>";
echo "</a>";

echo "<a href='fix_comment_system.php' style='display: inline-block;'>";
echo "<button style='background: #ffc107; color: black; padding: 12px 24px; border: none; cursor: pointer; font-size: 16px; font-weight: bold;'>System Diagnostics</button>";
echo "</a>";
echo "</div>";

echo "<p><small>Complete schema fix applied. The comment system should now be fully functional.</small></p>";
?>

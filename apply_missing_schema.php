<?php
/**
 * Apply Missing Schema Components
 * This script applies the missing comment_likes table and stored procedures
 */

require_once 'config/database.php';

echo "<h1>Apply Missing Schema Components</h1>";

// Apply comment_likes table
echo "<h2>Creating comment_likes Table</h2>";

$create_likes_table = "CREATE TABLE IF NOT EXISTS `comment_likes` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `comment_id` int(11) NOT NULL,
    `user_id` int(11) DEFAULT NULL,
    `ip_address` varchar(45) DEFAULT NULL,
    `like_type` enum('like','dislike') NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_comment_like` (`comment_id`, `user_id`, `ip_address`),
    KEY `idx_comment_id` (`comment_id`),
    KEY `idx_user_id` (`user_id`),
    CONSTRAINT `fk_comment_likes_comment` FOREIGN KEY (`comment_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_comment_likes_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if (mysqli_query($conn, $create_likes_table)) {
    echo "<p style='color: green;'>â comment_likes table created successfully</p>";
} else {
    echo "<p style='color: red;'>â Error creating comment_likes table: " . mysqli_error($conn) . "</p>";
}

// Apply comment_reports table
echo "<h2>Creating comment_reports Table</h2>";

$create_reports_table = "CREATE TABLE IF NOT EXISTS `comment_reports` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `comment_id` int(11) NOT NULL,
    `user_id` int(11) DEFAULT NULL,
    `reporter_ip` varchar(45) DEFAULT NULL,
    `reason` varchar(255) NOT NULL,
    `description` text DEFAULT NULL,
    `status` enum('pending','reviewed','dismissed') NOT NULL DEFAULT 'pending',
    `reviewed_by` int(11) DEFAULT NULL,
    `reviewed_at` timestamp NULL DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_comment_id` (`comment_id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_status` (`status`),
    CONSTRAINT `fk_comment_reports_comment` FOREIGN KEY (`comment_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_comment_reports_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_comment_reports_reviewer` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if (mysqli_query($conn, $create_reports_table)) {
    echo "<p style='color: green;'>â comment_reports table created successfully</p>";
} else {
    echo "<p style='color: red;'>â Error creating comment_reports table: " . mysqli_error($conn) . "</p>";
}

// Apply GetCommentStats stored procedure
echo "<h2>Creating GetCommentStats Stored Procedure</h2>";

// Drop existing procedure if it exists
mysqli_query($conn, "DROP PROCEDURE IF EXISTS `GetCommentStats`");

$create_procedure = "CREATE PROCEDURE `GetCommentStats`(IN news_id_param INT)
BEGIN
    SELECT 
        COUNT(*) as total_comments,
        COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved_comments,
        COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_comments,
        COUNT(CASE WHEN parent_id IS NULL THEN 1 END) as top_level_comments,
        COUNT(CASE WHEN parent_id IS NOT NULL THEN 1 END) as reply_comments,
        SUM(likes_count) as total_likes,
        SUM(dislikes_count) as total_dislikes
    FROM comments 
    WHERE news_id = news_id_param;
END";

if (mysqli_query($conn, $create_procedure)) {
    echo "<p style='color: green;'>â GetCommentStats stored procedure created successfully</p>";
} else {
    echo "<p style='color: red;'>â Error creating GetCommentStats stored procedure: " . mysqli_error($conn) . "</p>";
}

// Apply triggers for like count updates
echo "<h2>Creating Triggers for Like Count Updates</h2>";

// Drop existing triggers
mysqli_query($conn, "DROP TRIGGER IF EXISTS `update_comment_counts`");
mysqli_query($conn, "DROP TRIGGER IF EXISTS `update_comment_counts_on_delete`");

$create_trigger_insert = "CREATE TRIGGER `update_comment_counts`
AFTER INSERT ON `comment_likes`
FOR EACH ROW
BEGIN
    IF NEW.like_type = 'like' THEN
        UPDATE comments SET likes_count = likes_count + 1 WHERE id = NEW.comment_id;
    ELSEIF NEW.like_type = 'dislike' THEN
        UPDATE comments SET dislikes_count = dislikes_count + 1 WHERE id = NEW.comment_id;
    END IF;
END";

if (mysqli_query($conn, $create_trigger_insert)) {
    echo "<p style='color: green;'>â update_comment_counts trigger created successfully</p>";
} else {
    echo "<p style='color: red;'>â Error creating update_comment_counts trigger: " . mysqli_error($conn) . "</p>";
}

$create_trigger_delete = "CREATE TRIGGER `update_comment_counts_on_delete`
AFTER DELETE ON `comment_likes`
FOR EACH ROW
BEGIN
    IF OLD.like_type = 'like' THEN
        UPDATE comments SET likes_count = likes_count - 1 WHERE id = OLD.comment_id;
    ELSEIF OLD.like_type = 'dislike' THEN
        UPDATE comments SET dislikes_count = dislikes_count - 1 WHERE id = OLD.comment_id;
    END IF;
END";

if (mysqli_query($conn, $create_trigger_delete)) {
    echo "<p style='color: green;'>â update_comment_counts_on_delete trigger created successfully</p>";
} else {
    echo "<p style='color: red;'>â Error creating update_comment_counts_on_delete trigger: " . mysqli_error($conn) . "</p>";
}

// Create view for approved comments
echo "<h2>Creating approved_comments_view</h2>";

// Drop existing view
mysqli_query($conn, "DROP VIEW IF EXISTS `approved_comments_view`");

// First check what columns exist in users table
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

$create_view = "CREATE VIEW `approved_comments_view` AS
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

if (mysqli_query($conn, $create_view)) {
    echo "<p style='color: green;'>â approved_comments_view created successfully</p>";
} else {
    echo "<p style='color: red;'>â Error creating approved_comments_view: " . mysqli_error($conn) . "</p>";
}

// Add missing columns to comments table if needed
echo "<h2>Checking Comments Table Structure</h2>";

$columns_to_add = [
    'likes_count' => "ALTER TABLE comments ADD COLUMN likes_count int(11) NOT NULL DEFAULT 0",
    'dislikes_count' => "ALTER TABLE comments ADD COLUMN dislikes_count int(11) NOT NULL DEFAULT 0",
    'is_edited' => "ALTER TABLE comments ADD COLUMN is_edited tinyint(1) NOT NULL DEFAULT 0",
    'edited_at' => "ALTER TABLE comments ADD COLUMN edited_at timestamp NULL DEFAULT NULL",
    'user_agent' => "ALTER TABLE comments ADD COLUMN user_agent text DEFAULT NULL"
];

foreach ($columns_to_add as $column => $alter_sql) {
    $check_column = "SHOW COLUMNS FROM comments LIKE '$column'";
    $column_result = mysqli_query($conn, $check_column);
    
    if (mysqli_num_rows($column_result) == 0) {
        echo "<p style='color: orange;'>â Adding missing column: $column</p>";
        if (mysqli_query($conn, $alter_sql)) {
            echo "<p style='color: green;'>â Column $column added successfully</p>";
        } else {
            echo "<p style='color: red;'>â Error adding column $column: " . mysqli_error($conn) . "</p>";
        }
    } else {
        echo "<p style='color: green;'>â Column $column already exists</p>";
    }
}

// Verify all components are created
echo "<h2>Verification</h2>";

$tables_to_check = ['comments', 'comment_likes', 'comment_reports'];
foreach ($tables_to_check as $table) {
    $check = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
    $exists = mysqli_num_rows($check) > 0;
    echo "<p><strong>$table:</strong> " . ($exists ? "<span style='color: green;'>â Exists</span>" : "<span style='color: red;'>â Missing</span>") . "</p>";
}

$procedure_check = mysqli_query($conn, "SHOW PROCEDURE STATUS WHERE Db = DATABASE() AND Name = 'GetCommentStats'");
$procedure_exists = mysqli_num_rows($procedure_check) > 0;
echo "<p><strong>GetCommentStats Procedure:</strong> " . ($procedure_exists ? "<span style='color: green;'>â Exists</span>" : "<span style='color: red;'>â Missing</span>") . "</p>";

$view_check = mysqli_query($conn, "SHOW TABLES LIKE 'approved_comments_view'");
$view_exists = mysqli_num_rows($view_check) > 0;
echo "<p><strong>approved_comments_view:</strong> " . ($view_exists ? "<span style='color: green;'>â Exists</span>" : "<span style='color: red;'>â Missing</span>") . "</p>";

// Action buttons
echo "<div style='margin-top: 30px;'>";
echo "<a href='test_comments_comprehensive.php' style='display: inline-block; margin-right: 10px;'>";
echo "<button style='background: #007bff; color: white; padding: 12px 24px; border: none; cursor: pointer; font-size: 16px; font-weight: bold;'>Run Comprehensive Tests</button>";
echo "</a>";

echo "<a href='apply_schema_and_test.php' style='display: inline-block; margin-right: 10px;'>";
echo "<button style='background: #28a745; color: white; padding: 12px 24px; border: none; cursor: pointer; font-size: 16px; font-weight: bold;'>Apply Full Schema</button>";
echo "</a>";

echo "<a href='fix_comment_system.php' style='display: inline-block;'>";
echo "<button style='background: #ffc107; color: black; padding: 12px 24px; border: none; cursor: pointer; font-size: 16px; font-weight: bold;'>System Diagnostics</button>";
echo "</a>";
echo "</div>";

echo "<p><small>This script applies all missing schema components needed for the complete comment system.</small></p>";
?>

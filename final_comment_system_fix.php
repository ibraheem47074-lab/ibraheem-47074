<?php
/**
 * Final Comment System Fix - Complete Solution
 * This script provides the complete fix for all comment system issues
 */

require_once 'config/database.php';

echo "<h1>Final Comment System Fix</h1>";
echo "<p>This script provides a complete solution for all comment system issues.</p>";

$success_count = 0;
$error_count = 0;

// Function to execute and track results
function execute_and_track($conn, $sql, $description, $critical = false) {
    global $success_count, $error_count;
    
    try {
        if (mysqli_query($conn, $sql)) {
            $success_count++;
            echo "<p style='color: green;'>â $description</p>";
            return true;
        } else {
            $error = mysqli_error($conn);
            $error_count++;
            echo "<p style='color: " . ($critical ? 'red' : 'orange') . ";'>â $description: $error</p>";
            return false;
        }
    } catch (Exception $e) {
        $error_count++;
        echo "<p style='color: " . ($critical ? 'red' : 'orange') . ";'>â $description: " . $e->getMessage() . "</p>";
        return false;
    }
}

// Step 1: Check users table structure
echo "<h2>Step 1: Analyze Users Table</h2>";

$users_columns = [];
$describe_users = "DESCRIBE users";
$result = mysqli_query($conn, $describe_users);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $users_columns[] = $row['Field'];
    }
    echo "<p style='color: green;'>â Users table analyzed - found " . count($users_columns) . " columns</p>";
} else {
    echo "<p style='color: red;'>â Failed to analyze users table</p>";
    $error_count++;
}

// Step 2: Fix comments table
echo "<h2>Step 2: Fix Comments Table</h2>";

$columns_to_add = [
    'likes_count' => "ALTER TABLE comments ADD COLUMN likes_count int(11) NOT NULL DEFAULT 0",
    'dislikes_count' => "ALTER TABLE comments ADD COLUMN dislikes_count int(11) NOT NULL DEFAULT 0",
    'is_edited' => "ALTER TABLE comments ADD COLUMN is_edited tinyint(1) NOT NULL DEFAULT 0",
    'edited_at' => "ALTER TABLE comments ADD COLUMN edited_at timestamp NULL DEFAULT NULL",
    'user_agent' => "ALTER TABLE comments ADD COLUMN user_agent text DEFAULT NULL"
];

foreach ($columns_to_add as $column => $sql) {
    if (!in_array($column, $users_columns)) {
        $check_column = "SHOW COLUMNS FROM comments LIKE '$column'";
        $column_result = mysqli_query($conn, $check_column);
        
        if (mysqli_num_rows($column_result) == 0) {
            execute_and_track($conn, $sql, "Added column: $column");
        } else {
            echo "<p style='color: orange;'>â Column $column already exists</p>";
        }
    }
}

// Step 3: Create comment_likes table (fixed)
echo "<h2>Step 3: Create Comment Likes Table</h2>";

execute_and_track($conn, "DROP TABLE IF EXISTS comment_likes", "Dropped existing comment_likes table");

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

if (execute_and_track($conn, $create_likes, "Created comment_likes table", true)) {
    // Add unique constraints separately
    execute_and_track($conn, "ALTER TABLE comment_likes ADD UNIQUE KEY unique_comment_user (comment_id, user_id)", "Added user unique constraint");
    execute_and_track($conn, "ALTER TABLE comment_likes ADD UNIQUE KEY unique_comment_ip (comment_id, ip_address)", "Added IP unique constraint");
}

// Step 4: Create comment_reports table
echo "<h2>Step 4: Create Comment Reports Table</h2>";

execute_and_track($conn, "DROP TABLE IF EXISTS comment_reports", "Dropped existing comment_reports table");

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

execute_and_track($conn, $create_reports, "Created comment_reports table", true);

// Step 5: Create stored procedure
echo "<h2>Step 5: Create GetCommentStats Procedure</h2>";

execute_and_track($conn, "DROP PROCEDURE IF EXISTS GetCommentStats", "Dropped existing GetCommentStats procedure");

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

execute_and_track($conn, $create_procedure, "Created GetCommentStats procedure", true);

// Step 6: Create triggers
echo "<h2>Step 6: Create Triggers</h2>";

execute_and_track($conn, "DROP TRIGGER IF EXISTS update_comment_counts", "Dropped existing update_comment_counts trigger");
execute_and_track($conn, "DROP TRIGGER IF EXISTS update_comment_counts_on_delete", "Dropped existing update_comment_counts_on_delete trigger");

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

execute_and_track($conn, $trigger_insert, "Created insert trigger", true);

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

execute_and_track($conn, $trigger_delete, "Created delete trigger", true);

// Step 7: Create view with dynamic column handling
echo "<h2>Step 7: Create Approved Comments View</h2>";

execute_and_track($conn, "DROP VIEW IF EXISTS approved_comments_view", "Dropped existing approved_comments_view");

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

execute_and_track($conn, $create_view, "Created approved_comments_view", true);

// Step 8: Final verification
echo "<h2>Step 8: Final Verification</h2>";

$required_components = [
    'comments' => 'Comments table',
    'comment_likes' => 'Comment likes table',
    'comment_reports' => 'Comment reports table',
    'approved_comments_view' => 'Approved comments view'
];

$all_good = true;
foreach ($required_components as $component => $description) {
    $check = mysqli_query($conn, "SHOW TABLES LIKE '$component'");
    $exists = mysqli_num_rows($check) > 0;
    
    if ($exists) {
        echo "<p style='color: green;'>â $description exists</p>";
    } else {
        echo "<p style='color: red;'>â $description missing</p>";
        $all_good = false;
    }
}

// Check stored procedure
$procedure_check = mysqli_query($conn, "SHOW PROCEDURE STATUS WHERE Db = DATABASE() AND Name = 'GetCommentStats'");
$procedure_exists = mysqli_num_rows($procedure_check) > 0;

if ($procedure_exists) {
    echo "<p style='color: green;'>â GetCommentStats procedure exists</p>";
} else {
    echo "<p style='color: red;'>â GetCommentStats procedure missing</p>";
    $all_good = false;
}

// Step 9: Test functionality
echo "<h2>Step 9: Test Functionality</h2>";

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
            echo "<p><small>Stats: " . json_encode($stats) . "</small></p>";
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

$total_operations = $success_count + $error_count;
$success_rate = $total_operations > 0 ? round(($success_count / $total_operations) * 100, 1) : 0;

echo "<div style='background: #e3f2fd; padding: 15px; border: 1px solid #2196f3; border-radius: 5px; margin-bottom: 20px;'>";
echo "<h3 style='color: #1976d2;'>Final Results</h3>";
echo "<p><strong>Total Operations:</strong> $total_operations</p>";
echo "<p><strong>Successful:</strong> $success_count ($success_rate%)</p>";
echo "<p><strong>Failed:</strong> $error_count</p>";

if ($all_good && $error_count === 0) {
    echo "<p style='color: green; font-size: 18px; font-weight: bold;'>â COMPLETE SUCCESS! All components created successfully.</p>";
} else {
    echo "<p style='color: orange; font-size: 18px; font-weight: bold;'>â PARTIAL SUCCESS. Some components may need attention.</p>";
}
echo "</div>";

// Action buttons
echo "<div style='margin-top: 30px; text-align: center;'>";
echo "<a href='run_comment_tests.php' style='display: inline-block; margin: 0 10px;'>";
echo "<button style='background: #007bff; color: white; padding: 15px 30px; border: none; cursor: pointer; font-size: 18px; font-weight: bold; border-radius: 5px;'>Run Comprehensive Tests</button>";
echo "</a>";

echo "<a href='test_comments_comprehensive.php' style='display: inline-block; margin: 0 10px;'>";
echo "<button style='background: #28a745; color: white; padding: 15px 30px; border: none; cursor: pointer; font-size: 18px; font-weight: bold; border-radius: 5px;'>Original Test Suite</button>";
echo "</a>";

echo "<a href='fix_comment_system.php' style='display: inline-block; margin: 0 10px;'>";
echo "<button style='background: #ffc107; color: black; padding: 15px 30px; border: none; cursor: pointer; font-size: 18px; font-weight: bold; border-radius: 5px;'>System Diagnostics</button>";
echo "</a>";
echo "</div>";

echo "<hr>";
echo "<h3>Next Steps</h3>";
echo "<ol>";
echo "<li>Run the comprehensive tests to verify all functionality</li>";
echo "<li>Test comment submission on actual news articles</li>";
echo "<li>Test threaded comments (replies)</li>";
echo "<li>Test comment moderation in admin panel</li>";
echo "<li>Test like/dislike functionality</li>";
echo "</ol>";

echo "<p><small>This complete fix addresses all known issues with the comment system.</small></p>";
?>

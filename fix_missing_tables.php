<?php
// Quick Fix for Missing Tables
require_once 'config/database.php';

echo "<h2>Creating Missing Tables for Advanced User Management</h2>";

$queries = [
    // Create user_permissions table
    "CREATE TABLE IF NOT EXISTS `user_permissions` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) NOT NULL,
        `permission` varchar(100) NOT NULL,
        `granted_by` int(11) DEFAULT NULL,
        `granted_at` datetime DEFAULT CURRENT_TIMESTAMP,
        `expires_at` datetime DEFAULT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `user_permission` (`user_id`, `permission`),
        KEY `idx_user_permissions_user_id` (`user_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    // Create user_activity_log table
    "CREATE TABLE IF NOT EXISTS `user_activity_log` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) NOT NULL,
        `action` varchar(100) NOT NULL,
        `details` text DEFAULT NULL,
        `ip_address` varchar(45) DEFAULT NULL,
        `user_agent` text DEFAULT NULL,
        `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `idx_user_activity_user_id` (`user_id`),
        KEY `idx_user_activity_action` (`action`),
        KEY `idx_user_activity_created_at` (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    // Create user_achievements table
    "CREATE TABLE IF NOT EXISTS `user_achievements` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) NOT NULL,
        `achievement_type` varchar(50) NOT NULL,
        `achievement_title` varchar(200) NOT NULL,
        `achievement_description` text DEFAULT NULL,
        `achievement_icon` varchar(100) DEFAULT NULL,
        `earned_at` datetime DEFAULT CURRENT_TIMESTAMP,
        `points` int(11) DEFAULT 0,
        PRIMARY KEY (`id`),
        KEY `idx_user_achievements_user_id` (`user_id`),
        KEY `idx_user_achievements_type` (`achievement_type`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    // Create user_ratings table
    "CREATE TABLE IF NOT EXISTS `user_ratings` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `rated_user_id` int(11) NOT NULL,
        `rater_user_id` int(11) NOT NULL,
        `rating` decimal(3,2) DEFAULT 0.00,
        `review` text DEFAULT NULL,
        `rating_type` enum('article_quality','professionalism','timeliness','accuracy') DEFAULT 'article_quality',
        `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `user_rating_unique` (`rated_user_id`, `rater_user_id`, `rating_type`),
        KEY `idx_user_ratings_rated_user` (`rated_user_id`),
        KEY `idx_user_ratings_rating` (`rating`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    // Create user_work_schedule table
    "CREATE TABLE IF NOT EXISTS `user_work_schedule` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) NOT NULL,
        `day_of_week` enum('monday','tuesday','wednesday','thursday','friday','saturday','sunday') NOT NULL,
        `start_time` time DEFAULT NULL,
        `end_time` time DEFAULT NULL,
        `is_available` tinyint(1) DEFAULT 1,
        `notes` text DEFAULT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `user_schedule_unique` (`user_id`, `day_of_week`),
        KEY `idx_user_work_schedule_user_id` (`user_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
];

$alter_queries = [
    // Add missing columns to users table
    "ALTER TABLE `users` ADD COLUMN `department` enum('editorial','reporting','technical','management','marketing','multimedia') DEFAULT NULL AFTER `role`",
    "ALTER TABLE `users` ADD COLUMN `specialization` varchar(100) DEFAULT NULL AFTER `department`",
    "ALTER TABLE `users` ADD COLUMN `experience_level` enum('junior','intermediate','senior','expert','lead') DEFAULT 'junior' AFTER `specialization`",
    "ALTER TABLE `users` ADD COLUMN `skills` text DEFAULT NULL AFTER `experience_level`",
    "ALTER TABLE `users` ADD COLUMN `social_links` text DEFAULT NULL AFTER `skills`",
    "ALTER TABLE `users` ADD COLUMN `last_login` datetime DEFAULT NULL AFTER `updated_at`",
    "ALTER TABLE `users` ADD COLUMN `login_count` int(11) DEFAULT 0 AFTER `last_login`",
    "ALTER TABLE `users` ADD COLUMN `profile_views` int(11) DEFAULT 0 AFTER `login_count`",
    "ALTER TABLE `users` ADD COLUMN `articles_published` int(11) DEFAULT 0 AFTER `profile_views`",
    "ALTER TABLE `users` ADD COLUMN `is_featured` tinyint(1) DEFAULT 0 AFTER `articles_published`",
    "ALTER TABLE `users` ADD COLUMN `verification_status` enum('unverified','verified','premium') DEFAULT 'unverified' AFTER `is_featured`",
    "ALTER TABLE `users` ADD COLUMN `preferred_language` varchar(10) DEFAULT 'en' AFTER `verification_status`",
    "ALTER TABLE `users` ADD COLUMN `timezone` varchar(50) DEFAULT 'Asia/Karachi' AFTER `preferred_language`",
    "ALTER TABLE `users` ADD COLUMN `notification_preferences` text DEFAULT NULL AFTER `timezone`",
    "ALTER TABLE `users` ADD COLUMN `working_hours` varchar(100) DEFAULT NULL AFTER `notification_preferences`"
];

$success = 0;
$errors = 0;

// Execute CREATE TABLE queries
foreach ($queries as $index => $query) {
    try {
        if (mysqli_query($conn, $query)) {
            echo "<p style='color: green;'>✓ Created table " . ($index + 1) . "</p>";
            $success++;
        } else {
            $error = mysqli_error($conn);
            if (strpos($error, 'already exists') !== false) {
                echo "<p style='color: blue;'>ℹ Table " . ($index + 1) . " already exists</p>";
                $success++;
            } else {
                echo "<p style='color: red;'>✗ Error creating table " . ($index + 1) . ": $error</p>";
                $errors++;
            }
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Exception: " . $e->getMessage() . "</p>";
        $errors++;
    }
}

// Execute ALTER TABLE queries
foreach ($alter_queries as $index => $query) {
    try {
        if (mysqli_query($conn, $query)) {
            echo "<p style='color: green;'>✓ Added column " . ($index + 1) . "</p>";
            $success++;
        } else {
            $error = mysqli_error($conn);
            if (strpos($error, 'Duplicate column name') !== false || strpos($error, 'already exists') !== false) {
                echo "<p style='color: blue;'>ℹ Column " . ($index + 1) . " already exists</p>";
                $success++;
            } else {
                echo "<p style='color: red;'>✗ Error adding column " . ($index + 1) . ": $error</p>";
                $errors++;
            }
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Exception: " . $e->getMessage() . "</p>";
        $errors++;
    }
}

echo "<hr>";
echo "<h3>Setup Summary</h3>";
echo "<p><strong>Successful operations:</strong> $success</p>";
echo "<p><strong>Failed operations:</strong> $errors</p>";

if ($errors === 0) {
    echo "<p style='color: green; font-size: 1.2em; font-weight: bold;'>✓ All tables and columns created successfully!</p>";
    echo "<p>You can now access the advanced profile system:</p>";
    echo "<ul>";
    echo "<li><a href='admin/advanced-profile.php'>Advanced Profile</a></li>";
    echo "<li><a href='admin/user-management.php'>User Management</a></li>";
    echo "<li><a href='admin/profile-view.php'>Profile View</a></li>";
    echo "</ul>";
} else {
    echo "<p style='color: red;'>Some operations failed. Please check the errors above.</p>";
}

// Test the tables
echo "<h3>Testing Database Connection</h3>";
try {
    $test_query = "SELECT COUNT(*) as count FROM users";
    $result = mysqli_query($conn, $test_query);
    $row = mysqli_fetch_assoc($result);
    echo "<p style='color: green;'>✓ Users table accessible: {$row['count']} users found</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error accessing users table: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='admin/login.php'>Go to Admin Login</a></p>";
?>

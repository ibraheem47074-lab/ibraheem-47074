-- Advanced User Management System for PK Live News
-- This script adds enhanced user roles and profile capabilities

-- First, let's enhance the existing users table
ALTER TABLE `users` 
ADD COLUMN `department` enum('editorial','reporting','technical','management','marketing','multimedia') DEFAULT NULL AFTER `role`,
ADD COLUMN `specialization` varchar(100) DEFAULT NULL AFTER `department`,
ADD COLUMN `experience_level` enum('junior','intermediate','senior','expert','lead') DEFAULT 'junior' AFTER `specialization`,
ADD COLUMN `skills` text DEFAULT NULL AFTER `experience_level`,
ADD COLUMN `social_links` text DEFAULT NULL AFTER `skills`,
ADD COLUMN `last_login` datetime DEFAULT NULL AFTER `updated_at`,
ADD COLUMN `login_count` int(11) DEFAULT 0 AFTER `last_login`,
ADD COLUMN `profile_views` int(11) DEFAULT 0 AFTER `login_count`,
ADD COLUMN `articles_published` int(11) DEFAULT 0 AFTER `profile_views`,
ADD COLUMN `is_featured` tinyint(1) DEFAULT 0 AFTER `articles_published`,
ADD COLUMN `verification_status` enum('unverified','verified','premium') DEFAULT 'unverified' AFTER `is_featured`,
ADD COLUMN `preferred_language` varchar(10) DEFAULT 'en' AFTER `verification_status`,
ADD COLUMN `timezone` varchar(50) DEFAULT 'Asia/Karachi' AFTER `preferred_language`,
ADD COLUMN `notification_preferences` text DEFAULT NULL AFTER `timezone`,
ADD COLUMN `working_hours` varchar(100) DEFAULT NULL AFTER `notification_preferences`;

-- Create user_permissions table
CREATE TABLE IF NOT EXISTS `user_permissions` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `permission` varchar(100) NOT NULL,
    `granted_by` int(11) DEFAULT NULL,
    `granted_at` datetime DEFAULT CURRENT_TIMESTAMP,
    `expires_at` datetime DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `user_permission` (`user_id`, `permission`),
    KEY `idx_user_permissions_user_id` (`user_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create user_activity_log table
CREATE TABLE IF NOT EXISTS `user_activity_log` (
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
    KEY `idx_user_activity_created_at` (`created_at`),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create user_achievements table
CREATE TABLE IF NOT EXISTS `user_achievements` (
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
    KEY `idx_user_achievements_type` (`achievement_type`),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create user_ratings table
CREATE TABLE IF NOT EXISTS `user_ratings` (
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
    KEY `idx_user_ratings_rating` (`rating`),
    FOREIGN KEY (`rated_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`rater_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create user_work_schedule table
CREATE TABLE IF NOT EXISTS `user_work_schedule` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `day_of_week` enum('monday','tuesday','wednesday','thursday','friday','saturday','sunday') NOT NULL,
    `start_time` time DEFAULT NULL,
    `end_time` time DEFAULT NULL,
    `is_available` tinyint(1) DEFAULT 1,
    `notes` text DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `user_schedule_unique` (`user_id`, `day_of_week`),
    KEY `idx_user_work_schedule_user_id` (`user_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default permissions for different roles
INSERT IGNORE INTO `user_permissions` (`user_id`, `permission`, `granted_by`) VALUES
-- Admin permissions (will be assigned dynamically when admin users exist)
(0, 'manage_users', 0),
(0, 'manage_content', 0),
(0, 'manage_system', 0),
(0, 'view_analytics', 0),
(0, 'manage_ads', 0),
(0, 'manage_live_streams', 0),
(0, 'approve_content', 0),
(0, 'delete_content', 0),
(0, 'manage_categories', 0),
(0, 'manage_tags', 0),

-- Editor permissions
(0, 'edit_content', 0),
(0, 'publish_content', 0),
(0, 'review_content', 0),
(0, 'manage_reporters', 0),
(0, 'schedule_content', 0),
(0, 'view_reports', 0),

-- Reporter permissions
(0, 'create_content', 0),
(0, 'upload_media', 0),
(0, 'submit_content', 0),
(0, 'view_own_stats', 0);

-- Create advanced role definitions
CREATE TABLE IF NOT EXISTS `advanced_roles` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `role_name` varchar(50) NOT NULL,
    `role_display_name` varchar(100) NOT NULL,
    `role_description` text DEFAULT NULL,
    `role_level` int(11) DEFAULT 0,
    `department` varchar(50) DEFAULT NULL,
    `permissions` text DEFAULT NULL,
    `is_active` tinyint(1) DEFAULT 1,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `role_name_unique` (`role_name`),
    KEY `idx_advanced_roles_level` (`role_level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert advanced roles
INSERT IGNORE INTO `advanced_roles` (`role_name`, `role_display_name`, `role_description`, `role_level`, `department`, `permissions`) VALUES
('super_admin', 'Super Administrator', 'Full system access with all privileges', 100, 'management', 'all'),
('admin', 'Administrator', 'System administration and user management', 90, 'management', 'manage_users,manage_system,view_analytics,manage_content'),
('senior_editor', 'Senior Editor', 'Senior editorial oversight and content management', 80, 'editorial', 'edit_content,publish_content,review_content,manage_reporters,schedule_content,view_reports,approve_content'),
('editor', 'Editor', 'Content editing and publishing', 70, 'editorial', 'edit_content,publish_content,review_content,schedule_content'),
('associate_editor', 'Associate Editor', 'Assisting with content management', 60, 'editorial', 'edit_content,review_content,schedule_content'),
('senior_reporter', 'Senior Reporter', 'Experienced news reporting and mentoring', 50, 'reporting', 'create_content,upload_media,submit_content,view_own_stats,mentor_juniors'),
('reporter', 'Reporter', 'News content creation and reporting', 40, 'reporting', 'create_content,upload_media,submit_content,view_own_stats'),
('junior_reporter', 'Junior Reporter', 'Entry-level reporting and content creation', 30, 'reporting', 'create_content,upload_media,submit_content,view_own_stats'),
('multimedia_producer', 'Multimedia Producer', 'Video and multimedia content production', 45, 'multimedia', 'create_content,upload_media,manage_live_streams,submit_content'),
('technical_editor', 'Technical Editor', 'Technical content and system maintenance', 65, 'technical', 'edit_content,manage_system,view_analytics'),
('content_analyst', 'Content Analyst', 'Analytics and performance analysis', 55, 'editorial', 'view_analytics,view_reports,analyze_content'),
('social_media_manager', 'Social Media Manager', 'Social media content and engagement', 35, 'marketing', 'create_content,upload_media,submit_content,manage_social'),
('freelancer', 'Freelance Contributor', 'Part-time content contribution', 20, 'editorial', 'create_content,upload_media,submit_content,view_own_stats');

-- Update existing users to have advanced roles
UPDATE `users` SET 
    `experience_level` = CASE 
        WHEN `role` = 'admin' THEN 'expert'
        WHEN `role` = 'editor' THEN 'senior'
        WHEN `role` = 'reporter' THEN 'intermediate'
        ELSE 'junior'
    END,
    `department` = CASE 
        WHEN `role` = 'admin' THEN 'management'
        WHEN `role` = 'editor' THEN 'editorial'
        WHEN `role` = 'reporter' THEN 'reporting'
        ELSE 'editorial'
    END,
    `verification_status` = CASE 
        WHEN `role` = 'admin' THEN 'verified'
        WHEN `role` = 'editor' THEN 'verified'
        ELSE 'unverified'
    END;

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS idx_users_role_status ON users(role, status);
CREATE INDEX IF NOT EXISTS idx_users_department ON users(department);
CREATE INDEX IF NOT EXISTS idx_users_experience_level ON users(experience_level);
CREATE INDEX IF NOT EXISTS idx_users_verification_status ON users(verification_status);
CREATE INDEX IF NOT EXISTS idx_users_featured ON users(is_featured);

-- Display completion message
SELECT 'Advanced User Management System setup completed!' as message;

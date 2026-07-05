-- PK Live News - Comments System Schema Update
-- This script updates the comments table structure and ensures all necessary components

-- Drop existing comments table if it exists to start fresh
DROP TABLE IF EXISTS `comments`;

-- Create optimized comments table with full functionality
CREATE TABLE `comments` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `news_id` int(11) NOT NULL,
    `user_id` int(11) DEFAULT NULL,
    `parent_id` int(11) DEFAULT NULL,
    `name` varchar(255) NOT NULL,
    `email` varchar(255) NOT NULL,
    `comment` text NOT NULL,
    `status` enum('pending','approved','rejected','spam') NOT NULL DEFAULT 'pending',
    `ip_address` varchar(45) DEFAULT NULL,
    `user_agent` text DEFAULT NULL,
    `likes_count` int(11) NOT NULL DEFAULT 0,
    `dislikes_count` int(11) NOT NULL DEFAULT 0,
    `is_edited` tinyint(1) NOT NULL DEFAULT 0,
    `edited_at` timestamp NULL DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_news_id` (`news_id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_parent_id` (`parent_id`),
    KEY `idx_status` (`status`),
    KEY `idx_created_at` (`created_at`),
    KEY `idx_news_status` (`news_id`, `status`),
    CONSTRAINT `fk_comments_news` FOREIGN KEY (`news_id`) REFERENCES `news` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_comments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_comments_parent` FOREIGN KEY (`parent_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create comment likes table for rating system
CREATE TABLE IF NOT EXISTS `comment_likes` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create comment reports table for moderation
CREATE TABLE IF NOT EXISTS `comment_reports` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample data for testing
INSERT INTO `comments` (`news_id`, `user_id`, `parent_id`, `name`, `email`, `comment`, `status`, `ip_address`) 
SELECT 
    id as news_id, 
    NULL as user_id, 
    NULL as parent_id, 
    'Test User' as name, 
    'test@example.com' as email, 
    CONCAT('This is a test comment for article: ', LEFT(title, 50)) as comment,
    'approved' as status,
    '127.0.0.1' as ip_address
FROM `news` 
WHERE status = 'published' 
LIMIT 5;

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS `idx_comments_news_created` ON `comments` (`news_id`, `created_at` DESC);
CREATE INDEX IF NOT EXISTS `idx_comments_parent_created` ON `comments` (`parent_id`, `created_at` ASC);

-- Create view for approved comments with user info
CREATE OR REPLACE VIEW `approved_comments_view` AS
SELECT 
    c.*,
    u.name as user_name,
    u.avatar as user_avatar,
    u.role as user_role,
    (SELECT COUNT(*) FROM comment_likes cl WHERE cl.comment_id = c.id AND cl.like_type = 'like') as actual_likes,
    (SELECT COUNT(*) FROM comment_likes cl WHERE cl.comment_id = c.id AND cl.like_type = 'dislike') as actual_dislikes,
    (SELECT COUNT(*) FROM comments cr WHERE cr.parent_id = c.id) as replies_count
FROM comments c
LEFT JOIN users u ON c.user_id = u.id
WHERE c.status = 'approved';

-- Create stored procedure for comment statistics
DELIMITER //
CREATE PROCEDURE IF NOT EXISTS `GetCommentStats`(IN news_id_param INT)
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
END //
DELIMITER ;

-- Create trigger to update comment counts
DELIMITER //
CREATE TRIGGER IF NOT EXISTS `update_comment_counts`
AFTER INSERT ON `comment_likes`
FOR EACH ROW
BEGIN
    IF NEW.like_type = 'like' THEN
        UPDATE comments SET likes_count = likes_count + 1 WHERE id = NEW.comment_id;
    ELSEIF NEW.like_type = 'dislike' THEN
        UPDATE comments SET dislikes_count = dislikes_count + 1 WHERE id = NEW.comment_id;
    END IF;
END //
DELIMITER ;

-- Create trigger for unlike/dislike removal
DELIMITER //
CREATE TRIGGER IF NOT EXISTS `update_comment_counts_on_delete`
AFTER DELETE ON `comment_likes`
FOR EACH ROW
BEGIN
    IF OLD.like_type = 'like' THEN
        UPDATE comments SET likes_count = likes_count - 1 WHERE id = OLD.comment_id;
    ELSEIF OLD.like_type = 'dislike' THEN
        UPDATE comments SET dislikes_count = dislikes_count - 1 WHERE id = OLD.comment_id;
    END IF;
END //
DELIMITER ;

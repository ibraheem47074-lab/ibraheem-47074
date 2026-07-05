-- Create news_likes table for tracking likes
CREATE TABLE IF NOT EXISTS `news_likes` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `news_id` int(11) NOT NULL,
    `user_id` int(11) DEFAULT NULL,
    `ip_address` varchar(45) DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_news_user` (`news_id`, `user_id`),
    KEY `idx_news_ip` (`news_id`, `ip_address`),
    KEY `idx_created_at` (`created_at`),
    FOREIGN KEY (`news_id`) REFERENCES `news`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create news_shares table for tracking shares
CREATE TABLE IF NOT EXISTS `news_shares` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `news_id` int(11) NOT NULL,
    `platform` varchar(50) NOT NULL DEFAULT 'unknown',
    `user_id` int(11) DEFAULT NULL,
    `ip_address` varchar(45) DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_news_platform` (`news_id`, `platform`),
    KEY `idx_news_user` (`news_id`, `user_id`),
    KEY `idx_created_at` (`created_at`),
    FOREIGN KEY (`news_id`) REFERENCES `news`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add missing columns to news table if they don't exist
ALTER TABLE `news` 
ADD COLUMN IF NOT EXISTS `likes_count` int(11) NOT NULL DEFAULT 0,
ADD COLUMN IF NOT EXISTS `share_count` int(11) NOT NULL DEFAULT 0,
ADD COLUMN IF NOT EXISTS `comment_count` int(11) NOT NULL DEFAULT 0;

-- Update existing news records to have default values
UPDATE `news` SET `likes_count` = 0 WHERE `likes_count` IS NULL;
UPDATE `news` SET `share_count` = 0 WHERE `share_count` IS NULL;
UPDATE `news` SET `comment_count` = 0 WHERE `comment_count` IS NULL;

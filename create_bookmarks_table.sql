-- Create bookmarks table
CREATE TABLE `bookmarks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `news_id` int(11) NOT NULL,
  `folder` varchar(50) DEFAULT 'default',
  `notes` text DEFAULT NULL,
  `is_private` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_news` (`user_id`,`news_id`),
  KEY `idx_bookmarks_user` (`user_id`),
  KEY `idx_bookmarks_news` (`news_id`),
  KEY `idx_bookmarks_folder` (`folder`),
  KEY `idx_bookmarks_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

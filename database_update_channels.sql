-- Create channels table for live streaming system
CREATE TABLE IF NOT EXISTS `channels` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `category` enum('news','sports','entertainment','business','technology','international') NOT NULL DEFAULT 'news',
    `stream_url` text,
    `stream_type` enum('youtube','hls','rtmp','iframe') NOT NULL DEFAULT 'youtube',
    `thumbnail` varchar(500),
    `description` text,
    `status` enum('live','offline','scheduled') NOT NULL DEFAULT 'offline',
    `viewer_count` int(11) DEFAULT 0,
    `language` varchar(10) DEFAULT 'en',
    `country` varchar(50) DEFAULT 'PK',
    `sort_order` int(11) DEFAULT 0,
    `is_featured` tinyint(1) DEFAULT 0,
    `schedule_time` datetime NULL,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `category` (`category`),
    KEY `status` (`status`),
    KEY `is_featured` (`is_featured`),
    KEY `sort_order` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample channels for demo
INSERT INTO `channels` (`name`, `category`, `stream_url`, `stream_type`, `thumbnail`, `description`, `status`, `is_featured`) VALUES
('PK News Live', 'news', 'https://www.youtube.com/embed/jfKfPfyJRdk', 'youtube', 'uploads/channels/pk-news-live.jpg', '24/7 breaking news and current affairs coverage', 'live', 1),
('Sports Central', 'sports', 'https://www.youtube.com/embed/jfKfPfyJRdk', 'youtube', 'uploads/channels/sports-central.jpg', 'Live sports coverage and analysis', 'live', 1),
('Entertainment Tonight', 'entertainment', 'https://www.youtube.com/embed/jfKfPfyJRdk', 'youtube', 'uploads/channels/entertainment-tonight.jpg', 'Celebrity news and entertainment updates', 'offline', 0),
('Business Daily', 'business', 'https://www.youtube.com/embed/jfKfPfyJRdk', 'youtube', 'uploads/channels/business-daily.jpg', 'Market updates and business news', 'scheduled', 0),
('Tech Talk', 'technology', 'https://www.youtube.com/embed/jfKfPfyJRdk', 'youtube', 'uploads/channels/tech-talk.jpg', 'Latest technology news and reviews', 'offline', 0),
('World Report', 'international', 'https://www.youtube.com/embed/jfKfPfyJRdk', 'youtube', 'uploads/channels/world-report.jpg', 'International news and analysis', 'live', 0);

-- Create live_chat table for real-time chat
CREATE TABLE IF NOT EXISTS `live_chat` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `channel_id` int(11) NOT NULL,
    `username` varchar(100) NOT NULL,
    `message` text NOT NULL,
    `timestamp` timestamp DEFAULT CURRENT_TIMESTAMP,
    `is_deleted` tinyint(1) DEFAULT 0,
    PRIMARY KEY (`id`),
    KEY `channel_id` (`channel_id`),
    KEY `timestamp` (`timestamp`),
    FOREIGN KEY (`channel_id`) REFERENCES `channels`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create channel_schedule table for scheduled programming
CREATE TABLE IF NOT EXISTS `channel_schedule` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `channel_id` int(11) NOT NULL,
    `program_title` varchar(255) NOT NULL,
    `description` text,
    `start_time` datetime NOT NULL,
    `end_time` datetime NOT NULL,
    `is_recurring` tinyint(1) DEFAULT 0,
    `recurring_days` varchar(20) NULL, -- comma-separated days: 1,2,3,4,5 for Mon-Fri
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `channel_id` (`channel_id`),
    KEY `start_time` (`start_time`),
    FOREIGN KEY (`channel_id`) REFERENCES `channels`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample schedule data
INSERT INTO `channel_schedule` (`channel_id`, `program_title`, `description`, `start_time`, `end_time`, `is_recurring`, `recurring_days`) VALUES
(1, 'Morning News Bulletin', 'Start your day with comprehensive news coverage', '2026-03-19 07:00:00', '2026-03-19 09:00:00', 1, '1,2,3,4,5'),
(1, 'Breaking News Live', 'Real-time coverage of developing stories', '2026-03-19 14:00:00', '2026-03-19 16:00:00', 1, '1,2,3,4,5'),
(1, 'Evening News Wrap', 'Complete roundup of the day''s events', '2026-03-19 20:00:00', '2026-03-19 21:00:00', 1, '1,2,3,4,5'),
(2, 'Live Cricket Match', 'Coverage of today''s cricket match', '2026-03-19 15:00:00', '2026-03-19 19:00:00', 0, NULL),
(2, 'Sports Highlights', 'Daily sports recap and analysis', '2026-03-19 22:00:00', '2026-03-19 23:00:00', 1, '1,2,3,4,5,6,7');

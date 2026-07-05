-- PK Live News Database Backup
-- Generated on: 2026-04-08 01:22:48
-- MySQL Version: 10.4.32-MariaDB

-- Table structure for `bookmarks`
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



-- Table structure for `categories`
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `name_ur` varchar(255) DEFAULT NULL,
  `name_hi` varchar(255) DEFAULT NULL,
  `name_zh` varchar(255) DEFAULT NULL,
  `name_ps` varchar(255) DEFAULT NULL,
  `color` varchar(7) DEFAULT '#007bff',
  `icon` varchar(50) DEFAULT 'fas fa-newspaper',
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `description_ur` text DEFAULT NULL,
  `description_hi` text DEFAULT NULL,
  `description_zh` text DEFAULT NULL,
  `description_ps` text DEFAULT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `parent_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_categories_status` (`status`),
  KEY `idx_status_slug` (`status`,`slug`),
  KEY `fk_category_parent` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `categories` VALUES ('1', 'Politics', NULL, NULL, NULL, NULL, '#007bff', 'fas fa-newspaper', 'politics', 'Politics', NULL, NULL, NULL, NULL, NULL, NULL, '', 'active', '2026-04-08 00:35:33', '2026-04-08 00:35:33', NULL;


-- Table structure for `channel_schedule`
CREATE TABLE `channel_schedule` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `channel_id` int(11) NOT NULL,
  `program_title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `is_recurring` tinyint(1) DEFAULT 0,
  `recurring_days` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `channel_id` (`channel_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `channel_schedule` VALUES ('1', '1', 'Morning News Bulletin', 'Start your day with comprehensive news coverage', '2026-03-19 07:00:00', '2026-03-19 09:00:00', '1', '1,2,3,4,5', '2026-04-07 22:09:23';
INSERT INTO `channel_schedule` VALUES ('2', '1', 'Breaking News Live', 'Real-time coverage of developing stories', '2026-03-19 14:00:00', '2026-03-19 16:00:00', '1', '1,2,3,4,5', '2026-04-07 22:09:23';
INSERT INTO `channel_schedule` VALUES ('3', '2', 'Sports Highlights', 'Daily sports recap and analysis', '2026-03-19 22:00:00', '2026-03-19 23:00:00', '1', '1,2,3,4,5,6,7', '2026-04-07 22:09:23';


-- Table structure for `channels`
CREATE TABLE `channels` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `category` enum('news','sports','entertainment','business','technology','international') NOT NULL DEFAULT 'news',
  `stream_url` text DEFAULT NULL,
  `stream_type` enum('youtube','hls','rtmp','iframe') NOT NULL DEFAULT 'youtube',
  `thumbnail` varchar(500) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` enum('live','offline','scheduled') NOT NULL DEFAULT 'offline',
  `viewer_count` int(11) DEFAULT 0,
  `language` varchar(10) DEFAULT 'en',
  `country` varchar(50) DEFAULT 'PK',
  `sort_order` int(11) DEFAULT 0,
  `is_featured` tinyint(1) DEFAULT 0,
  `schedule_time` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `channels` VALUES ('1', 'PK News Live', 'news', 'https://www.youtube.com/embed/jfKfPfyJRdk', 'youtube', NULL, '24/7 breaking news and current affairs coverage', 'live', '0', 'en', 'PK', '0', '1', NULL, '2026-04-07 22:09:23', '2026-04-07 22:09:23';
INSERT INTO `channels` VALUES ('2', 'Sports Central', 'sports', 'https://www.youtube.com/embed/jfKfPfyJRdk', 'youtube', NULL, 'Live sports coverage and analysis', 'live', '0', 'en', 'PK', '0', '1', NULL, '2026-04-07 22:09:23', '2026-04-07 22:09:23';
INSERT INTO `channels` VALUES ('3', 'Entertainment Tonight', 'entertainment', 'https://www.youtube.com/embed/jfKfPfyJRdk', 'youtube', NULL, 'Celebrity news and entertainment updates', 'offline', '0', 'en', 'PK', '0', '0', NULL, '2026-04-07 22:09:23', '2026-04-07 22:09:23';


-- Table structure for `comments`
CREATE TABLE `comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `news_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `comment` text NOT NULL,
  `status` enum('approved','pending','rejected') DEFAULT 'pending',
  `parent_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_comments_news` (`news_id`),
  KEY `idx_comments_user` (`user_id`),
  KEY `idx_comments_status` (`status`),
  KEY `idx_comments_parent` (`parent_id`),
  KEY `idx_news_status` (`news_id`,`status`),
  KEY `idx_status_created` (`status`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



-- Table structure for `live_chat`
CREATE TABLE `live_chat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `channel_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_deleted` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- Table structure for `live_stream`
CREATE TABLE `live_stream` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `stream_url` varchar(500) NOT NULL,
  `stream_key` varchar(255) DEFAULT NULL,
  `embed_code` text DEFAULT NULL,
  `status` enum('offline','online','scheduled') DEFAULT 'offline',
  `schedule_time` timestamp NULL DEFAULT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `multi_camera_config` longtext DEFAULT NULL COMMENT 'Configuration for multiple cameras',
  `overlay_config` longtext DEFAULT NULL COMMENT 'Overlay configuration and settings',
  `active_camera` int(11) DEFAULT 1 COMMENT 'Currently active camera (1-based index)',
  `camera_count` int(11) DEFAULT 1 COMMENT 'Total number of cameras configured',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



-- Table structure for `news`
CREATE TABLE `news` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
  `excerpt` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `image_type` varchar(20) DEFAULT 'manual',
  `video_url` varchar(255) DEFAULT NULL,
  `video_path` varchar(255) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `author_id` int(11) DEFAULT NULL,
  `featured_image` varchar(255) DEFAULT NULL,
  `status` enum('draft','published','featured','archived') DEFAULT 'draft',
  `news_type` varchar(50) DEFAULT 'manual',
  `is_breaking` tinyint(1) DEFAULT 0,
  `featured` tinyint(1) DEFAULT 0,
  `breaking_news` tinyint(1) DEFAULT 0,
  `views` int(11) DEFAULT 0,
  `likes_count` int(11) DEFAULT 0,
  `engagement_score` decimal(10,2) DEFAULT 0.00,
  `share_count` int(11) DEFAULT 0,
  `comment_count` int(11) DEFAULT 0,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `meta_keywords` varchar(255) DEFAULT NULL,
  `published_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `source_url` varchar(500) DEFAULT NULL COMMENT 'Original source URL for scraped articles',
  `source_name` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `sentiment_score` decimal(3,2) DEFAULT 0.00,
  `sentiment_label` varchar(20) DEFAULT 'neutral',
  `urgency` varchar(20) DEFAULT 'medium',
  `credibility_status` enum('PENDING','ANALYZED','REVIEWED') DEFAULT 'PENDING',
  `auto_flagged` tinyint(1) DEFAULT 0,
  `time_status` enum('new','recent','older') DEFAULT 'older',
  `company_name` varchar(255) DEFAULT NULL,
  `job_location` varchar(255) DEFAULT NULL,
  `salary` varchar(255) DEFAULT NULL,
  `last_date_to_apply` date DEFAULT NULL,
  `job_type` enum('Full-time','Part-time','Contract','Freelance','Internship') DEFAULT NULL,
  `apply_url` varchar(500) DEFAULT NULL,
  `requirements` text DEFAULT NULL,
  `is_job_posting` tinyint(1) DEFAULT 0,
  `is_channel_job` tinyint(1) DEFAULT 0 COMMENT 'Flag for channel-specific job postings',
  `channel_name` varchar(50) DEFAULT NULL COMMENT 'Channel name for specialized jobs',
  PRIMARY KEY (`id`),
  KEY `slug` (`slug`),
  KEY `category_id` (`category_id`),
  KEY `author_id` (`author_id`),
  KEY `status` (`status`),
  KEY `published_at` (`published_at`),
  KEY `featured` (`featured`),
  KEY `breaking_news` (`breaking_news`),
  KEY `news_type` (`news_type`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `news` VALUES ('1', 'Cricket', 'ricket', 'sport gala 2025', 'sport gala 2025...', 'uploads/news/img_69d55cc02b900_1775590592.jpg', 'manual', '', '', '1', '1', NULL, 'published', 'manual', '0', '0', '0', '0', '0', '0.00', '0', '0', NULL, NULL, NULL, '2026-04-08 00:36:32', NULL, NULL, '2026-04-08 00:36:32', '2026-04-08 00:36:32', '0.00', 'neutral', 'medium', 'PENDING', '0', 'older', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', NULL;


-- Table structure for `news_analytics`
CREATE TABLE `news_analytics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `news_id` int(11) NOT NULL,
  `views` int(11) DEFAULT 0,
  `unique_visitors` int(11) DEFAULT 0,
  `avg_read_time` int(11) DEFAULT 0,
  `date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_news_date` (`news_id`,`date`),
  KEY `idx_analytics_news` (`news_id`),
  KEY `idx_analytics_date` (`date`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `news_analytics` VALUES ('1', '1', '208', '175', '70', '2026-04-08', '2026-04-08 01:03:02';


-- Table structure for `news_sources`
CREATE TABLE `news_sources` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT 'Name of news source',
  `url` varchar(500) NOT NULL COMMENT 'Main URL of news source',
  `rss_url` varchar(500) DEFAULT NULL COMMENT 'RSS feed URL',
  `type` enum('rss','scrape') NOT NULL DEFAULT 'rss' COMMENT 'Import type',
  `category_id` int(11) DEFAULT NULL COMMENT 'Default category ID',
  `scrape_frequency` int(11) NOT NULL DEFAULT 60 COMMENT 'Scraping frequency in minutes',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active' COMMENT 'Source status',
  `last_scraped` timestamp NULL DEFAULT NULL COMMENT 'Last successful scrape',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_type` (`type`),
  KEY `idx_last_scraped` (`last_scraped`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `news_sources` VALUES ('1', 'BBC News', 'https://www.bbc.com/news', 'https://feeds.bbci.co.uk/news/rss.xml', 'rss', '1', '60', 'active', NULL, '2026-04-08 01:10:18', '2026-04-08 01:10:18';
INSERT INTO `news_sources` VALUES ('2', 'CNN News', 'https://www.cnn.com', 'https://rss.cnn.com/rss/edition.rss', 'rss', '1', '60', 'active', NULL, '2026-04-08 01:10:18', '2026-04-08 01:10:18';
INSERT INTO `news_sources` VALUES ('3', 'Reuters News', 'https://www.reuters.com', 'https://feeds.reuters.com/reuters/topNews', 'rss', '1', '60', 'active', NULL, '2026-04-08 01:10:18', '2026-04-08 01:10:18';
INSERT INTO `news_sources` VALUES ('4', 'Al Jazeera', 'https://www.aljazeera.com', 'https://www.aljazeera.com/xml/rss/all.xml', 'rss', '1', '60', 'active', NULL, '2026-04-08 01:10:18', '2026-04-08 01:10:18';
INSERT INTO `news_sources` VALUES ('5', 'Associated Press', 'https://apnews.com', 'https://feeds.apnews.com/rss/apf-topnews', 'rss', '1', '60', 'active', NULL, '2026-04-08 01:10:18', '2026-04-08 01:10:18';
INSERT INTO `news_sources` VALUES ('6', 'Fox News', 'https://www.foxnews.com', 'https://www.foxnews.com/about/rss/feedburner/foxnews/latest', 'rss', '1', '60', 'active', NULL, '2026-04-08 01:10:18', '2026-04-08 01:10:18';
INSERT INTO `news_sources` VALUES ('7', 'The Guardian', 'https://www.theguardian.com', 'https://www.theguardian.com/world/rss', 'rss', '1', '60', 'active', NULL, '2026-04-08 01:10:18', '2026-04-08 01:10:18';
INSERT INTO `news_sources` VALUES ('8', 'NBC News', 'https://www.nbcnews.com', 'https://www.nbcnews.com/id/3032091/device/rss/rss.xml', 'rss', '1', '60', 'active', NULL, '2026-04-08 01:10:18', '2026-04-08 01:10:18';
INSERT INTO `news_sources` VALUES ('9', 'CBS News', 'https://www.cbsnews.com', 'https://www.cbsnews.com/rss/live/rss.rss', 'rss', '1', '60', 'active', NULL, '2026-04-08 01:10:18', '2026-04-08 01:10:18';
INSERT INTO `news_sources` VALUES ('10', 'NPR News', 'https://www.npr.org', 'https://feeds.npr.org/1001/rss.xml', 'rss', '1', '60', 'active', NULL, '2026-04-08 01:10:18', '2026-04-08 01:10:18';
INSERT INTO `news_sources` VALUES ('11', 'Dawn News', 'https://www.dawn.com', 'https://www.dawn.com/feed/', 'rss', '1', '60', 'active', NULL, '2026-04-08 01:10:18', '2026-04-08 01:10:18';
INSERT INTO `news_sources` VALUES ('12', 'Geo News', 'https://www.geo.tv', 'https://www.geo.tv/rss/feed/', 'rss', '1', '60', 'active', NULL, '2026-04-08 01:10:18', '2026-04-08 01:10:18';
INSERT INTO `news_sources` VALUES ('13', 'ARY News', 'https://arynews.tv', 'https://arynews.tv/feed/', 'rss', '1', '60', 'active', NULL, '2026-04-08 01:10:18', '2026-04-08 01:10:18';
INSERT INTO `news_sources` VALUES ('14', 'Express Tribune', 'https://tribune.com.pk', 'https://tribune.com.pk/feed/', 'rss', '1', '60', 'active', NULL, '2026-04-08 01:10:18', '2026-04-08 01:10:18';
INSERT INTO `news_sources` VALUES ('15', 'The News International', 'https://www.thenews.com.pk', 'https://www.thenews.com.pk/rss/1/1', 'rss', '1', '60', 'active', NULL, '2026-04-08 01:10:18', '2026-04-08 01:10:18';
INSERT INTO `news_sources` VALUES ('16', 'BBC World News', 'https://www.bbc.com/news/world', 'https://feeds.bbci.co.uk/news/world/rss.xml', 'rss', '1', '30', 'active', NULL, '2026-04-08 01:11:02', '2026-04-08 01:11:02';
INSERT INTO `news_sources` VALUES ('17', 'CNN World', 'https://www.cnn.com/world', 'https://rss.cnn.com/rss/edition_world.rss', 'rss', '1', '30', 'active', NULL, '2026-04-08 01:11:02', '2026-04-08 01:11:02';
INSERT INTO `news_sources` VALUES ('18', 'Reuters World', 'https://www.reuters.com/world', 'https://feeds.reuters.com/reuters/worldNews', 'rss', '1', '30', 'active', NULL, '2026-04-08 01:11:02', '2026-04-08 01:11:02';
INSERT INTO `news_sources` VALUES ('19', 'The New York Times', 'https://www.nytimes.com', 'https://rss.nytimes.com/services/xml/rss/nyt/HomePage.xml', 'rss', '1', '30', 'active', NULL, '2026-04-08 01:11:02', '2026-04-08 01:11:02';
INSERT INTO `news_sources` VALUES ('20', 'Washington Post', 'https://www.washingtonpost.com', 'https://www.washingtonpost.com/world/rss/', 'rss', '1', '30', 'active', NULL, '2026-04-08 01:11:02', '2026-04-08 01:11:02';
INSERT INTO `news_sources` VALUES ('21', 'Bloomberg', 'https://www.bloomberg.com', 'https://feeds.bloomberg.com/markets/news.rss', 'rss', '1', '30', 'active', NULL, '2026-04-08 01:11:02', '2026-04-08 01:11:02';
INSERT INTO `news_sources` VALUES ('22', 'Financial Times', 'https://www.ft.com', 'https://www.ft.com/rss/home', 'rss', '1', '30', 'active', NULL, '2026-04-08 01:11:02', '2026-04-08 01:11:02';
INSERT INTO `news_sources` VALUES ('23', 'CNBC', 'https://www.cnbc.com', 'https://www.cnbc.com/id/100003114/device/rss/rss.html', 'rss', '1', '30', 'active', NULL, '2026-04-08 01:11:02', '2026-04-08 01:11:02';
INSERT INTO `news_sources` VALUES ('24', 'ABC News', 'https://abcnews.go.com', 'https://abcnews.go.com/xml/rss/abc_us_topstories.xml', 'rss', '1', '30', 'active', NULL, '2026-04-08 01:11:02', '2026-04-08 01:11:02';
INSERT INTO `news_sources` VALUES ('25', 'PBS NewsHour', 'https://www.pbs.org/newshour', 'https://www.pbs.org/newshour/rss/feed', 'rss', '1', '30', 'active', NULL, '2026-04-08 01:11:02', '2026-04-08 01:11:02';
INSERT INTO `news_sources` VALUES ('26', 'Deutsche Welle (DW)', 'https://www.dw.com', 'https://www.dw.com/en/rss/top-stories', 'rss', '1', '30', 'active', NULL, '2026-04-08 01:11:02', '2026-04-08 01:11:02';
INSERT INTO `news_sources` VALUES ('27', 'France 24', 'https://www.france24.com', 'https://www.france24.com/en/rss', 'rss', '1', '30', 'active', NULL, '2026-04-08 01:11:02', '2026-04-08 01:11:02';
INSERT INTO `news_sources` VALUES ('28', 'RT News', 'https://www.rt.com', 'https://www.rt.com/rss/', 'rss', '1', '30', 'active', NULL, '2026-04-08 01:11:02', '2026-04-08 01:11:02';
INSERT INTO `news_sources` VALUES ('29', 'CGTN', 'https://news.cgtn.com', 'https://news.cgtn.com/rss', 'rss', '1', '30', 'active', NULL, '2026-04-08 01:11:02', '2026-04-08 01:11:02';
INSERT INTO `news_sources` VALUES ('30', 'NDTV', 'https://www.ndtv.com', 'https://feeds.ndtv.com/ndtv/rss/top-stories.xml', 'rss', '1', '30', 'active', NULL, '2026-04-08 01:11:02', '2026-04-08 01:11:02';
INSERT INTO `news_sources` VALUES ('31', 'Times of India', 'https://timesofindia.indiatimes.com', 'https://timesofindia.indiatimes.com/rssfeedstopstories.cms', 'rss', '1', '30', 'active', NULL, '2026-04-08 01:11:02', '2026-04-08 01:11:02';
INSERT INTO `news_sources` VALUES ('32', 'Hindustan Times', 'https://www.hindustantimes.com', 'https://www.hindustantimes.com/rss/topnews/rssfeed.xml', 'rss', '1', '30', 'active', NULL, '2026-04-08 01:11:02', '2026-04-08 01:11:02';
INSERT INTO `news_sources` VALUES ('33', 'Pakistan Today', 'https://www.pakistantoday.com.pk', 'https://www.pakistantoday.com.pk/feed/', 'rss', '1', '30', 'active', NULL, '2026-04-08 01:11:02', '2026-04-08 01:11:02';
INSERT INTO `news_sources` VALUES ('34', 'Dunya News', 'https://www.dunyanews.tv', 'https://www.dunyanews.tv/rss.xml', 'rss', '1', '30', 'active', NULL, '2026-04-08 01:11:02', '2026-04-08 01:11:02';
INSERT INTO `news_sources` VALUES ('35', 'Samaa TV', 'https://www.samaa.tv', 'https://www.samaa.tv/feed/', 'rss', '1', '30', 'active', NULL, '2026-04-08 01:11:02', '2026-04-08 01:11:02';
INSERT INTO `news_sources` VALUES ('36', '24 News HD', 'https://www.24news.tv', 'https://www.24news.tv/feed/', 'rss', '1', '30', 'active', NULL, '2026-04-08 01:11:02', '2026-04-08 01:11:02';
INSERT INTO `news_sources` VALUES ('37', 'BBC Urdu', 'https://www.bbc.com/urdu', 'https://www.bbc.com/urdu/rss', 'rss', '1', '30', 'active', NULL, '2026-04-08 01:11:02', '2026-04-08 01:11:02';
INSERT INTO `news_sources` VALUES ('38', 'VOA Urdu', 'https://www.voaurdu.com', 'https://www.voaurdu.com/a/rss', 'rss', '1', '30', 'active', NULL, '2026-04-08 01:11:02', '2026-04-08 01:11:02';
INSERT INTO `news_sources` VALUES ('39', 'RFE/RL Urdu', 'https://urdu.rferl.org', 'https://urdu.rferl.org/rss', 'rss', '1', '30', 'active', NULL, '2026-04-08 01:11:02', '2026-04-08 01:11:02';


-- Table structure for `news_tags`
CREATE TABLE `news_tags` (
  `news_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  PRIMARY KEY (`news_id`,`tag_id`),
  KEY `idx_news_tags_news` (`news_id`),
  KEY `idx_news_tags_tag` (`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `news_tags` VALUES ('1', '6';
INSERT INTO `news_tags` VALUES ('1', '7';
INSERT INTO `news_tags` VALUES ('1', '10';
INSERT INTO `news_tags` VALUES ('1', '15';


-- Table structure for `page_views`
CREATE TABLE `page_views` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `page_type` enum('home','category','article','search','other') DEFAULT 'other',
  `page_id` int(11) DEFAULT NULL,
  `page_url` varchar(500) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_page_views_type` (`page_type`),
  KEY `idx_page_views_date` (`created_at`),
  KEY `idx_page_views_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



-- Table structure for `poll_options`
CREATE TABLE `poll_options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `poll_id` int(11) NOT NULL,
  `option_text` varchar(255) NOT NULL,
  `votes` int(11) DEFAULT 0,
  `order_position` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `poll_id` (`poll_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `poll_options` VALUES ('1', '1', 'Option 1', '1', '0';
INSERT INTO `poll_options` VALUES ('2', '1', 'Option 2', '0', '0';


-- Table structure for `poll_votes`
CREATE TABLE `poll_votes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `poll_id` int(11) NOT NULL,
  `option_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `voted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `poll_id` (`poll_id`),
  KEY `option_id` (`option_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `poll_votes` VALUES ('1', '1', '1', NULL, '::1', '2026-04-07 22:01:00';


-- Table structure for `polls`
CREATE TABLE `polls` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `question` varchar(255) NOT NULL,
  `status` enum('active','inactive','closed') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `description` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `ends_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_polls_status` (`status`),
  KEY `idx_polls_created` (`created_at`),
  KEY `created_by` (`created_by`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `polls` VALUES ('1', 'Test Poll', 'active', '2026-04-07 21:29:23', NULL, NULL, NULL;


-- Table structure for `post_likes`
CREATE TABLE `post_likes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `news_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `news_id` (`news_id`),
  KEY `user_id` (`user_id`),
  KEY `idx_news_id` (`news_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- Table structure for `rss_import_log`
CREATE TABLE `rss_import_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `source_id` int(11) DEFAULT NULL,
  `articles_imported` int(11) DEFAULT 0,
  `status` enum('success','error','partial') DEFAULT 'success',
  `error_message` text DEFAULT NULL,
  `import_time` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_source_id` (`source_id`),
  KEY `idx_import_time` (`import_time`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



-- Table structure for `settings`
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- Table structure for `site_settings`
CREATE TABLE `site_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('text','number','boolean','json') DEFAULT 'text',
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `site_settings` VALUES ('1', 'site_name', 'PK Live News', 'text', '', '2026-04-07 08:40:28', '2026-04-07 08:40:28';
INSERT INTO `site_settings` VALUES ('2', 'site_description', 'Latest news and updates from Pakistan', 'text', '', '2026-04-07 08:40:28', '2026-04-07 08:40:28';
INSERT INTO `site_settings` VALUES ('3', 'posts_per_page', '10', 'text', '', '2026-04-07 08:40:28', '2026-04-07 08:40:28';
INSERT INTO `site_settings` VALUES ('4', 'maintenance_mode', 'off', 'text', '', '2026-04-07 08:40:28', '2026-04-07 08:40:28';
INSERT INTO `site_settings` VALUES ('5', 'show_trending_news', 'on', 'text', '', '2026-04-07 08:40:28', '2026-04-07 08:40:28';
INSERT INTO `site_settings` VALUES ('6', 'show_ads', 'on', 'text', '', '2026-04-07 08:40:28', '2026-04-07 08:40:28';
INSERT INTO `site_settings` VALUES ('7', 'default_language', 'en', 'text', '', '2026-04-07 08:40:28', '2026-04-07 08:40:28';
INSERT INTO `site_settings` VALUES ('8', 'contact_email', 'contact@pklivenews.com', 'text', '', '2026-04-07 08:40:28', '2026-04-07 08:40:28';
INSERT INTO `site_settings` VALUES ('9', 'social_media_links', '{\"facebook\":\"\",\"twitter\":\"\",\"instagram\":\"\",\"youtube\":\"\"}', 'text', '', '2026-04-07 08:40:28', '2026-04-07 08:40:28';
INSERT INTO `site_settings` VALUES ('10', 'seo_meta_description', 'PK Live News - Your trusted source for latest news', 'text', '', '2026-04-07 08:40:28', '2026-04-07 08:40:28';
INSERT INTO `site_settings` VALUES ('11', 'seo_keywords', 'news, pakistan, breaking news, current affairs', 'text', '', '2026-04-07 08:40:28', '2026-04-07 08:40:28';
INSERT INTO `site_settings` VALUES ('12', 'cache_duration', '3600', 'text', '', '2026-04-07 08:40:28', '2026-04-07 08:40:28';
INSERT INTO `site_settings` VALUES ('13', 'enable_comments', 'on', 'text', '', '2026-04-07 08:40:28', '2026-04-07 08:40:28';
INSERT INTO `site_settings` VALUES ('14', 'enable_rss', 'on', 'text', '', '2026-04-07 08:40:28', '2026-04-07 08:40:28';
INSERT INTO `site_settings` VALUES ('15', 'theme_color', '#007bff', 'text', '', '2026-04-07 08:40:28', '2026-04-07 08:40:28';
INSERT INTO `site_settings` VALUES ('16', 'logo_path', 'assets/images/logo.png', 'text', '', '2026-04-07 08:40:28', '2026-04-07 08:40:28';


-- Table structure for `system_settings`
CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`),
  KEY `updated_by` (`updated_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



-- Table structure for `tag_cloud`
;

INSERT INTO `tag_cloud` VALUES ('7', 'Health', 'health', '1', '10';
INSERT INTO `tag_cloud` VALUES ('10', 'Pakistan', 'pakistan', '1', '10';
INSERT INTO `tag_cloud` VALUES ('6', 'Entertainment', 'entertainment', '1', '10';
INSERT INTO `tag_cloud` VALUES ('15', 'Football', 'football', '1', '10';
INSERT INTO `tag_cloud` VALUES ('11', 'COVID-19', 'covid-19', '0', '0';
INSERT INTO `tag_cloud` VALUES ('14', 'Cricket', 'cricket', '0', '0';
INSERT INTO `tag_cloud` VALUES ('1', 'Breaking News', 'breaking-news', '0', '0';
INSERT INTO `tag_cloud` VALUES ('17', 'Weather', 'weather', '0', '0';
INSERT INTO `tag_cloud` VALUES ('4', 'Technology', 'technology', '0', '0';
INSERT INTO `tag_cloud` VALUES ('20', 'Security', 'security', '0', '0';
INSERT INTO `tag_cloud` VALUES ('13', 'Elections', 'elections', '0', '0';
INSERT INTO `tag_cloud` VALUES ('16', 'Science', 'science', '0', '0';
INSERT INTO `tag_cloud` VALUES ('3', 'Sports', 'sports', '0', '0';
INSERT INTO `tag_cloud` VALUES ('19', 'Social Media', 'social-media', '0', '0';
INSERT INTO `tag_cloud` VALUES ('9', 'International', 'international', '0', '0';
INSERT INTO `tag_cloud` VALUES ('12', 'Economy', 'economy', '0', '0';
INSERT INTO `tag_cloud` VALUES ('2', 'Politics', 'politics', '0', '0';
INSERT INTO `tag_cloud` VALUES ('18', 'Climate', 'climate', '0', '0';
INSERT INTO `tag_cloud` VALUES ('5', 'Business', 'business', '0', '0';
INSERT INTO `tag_cloud` VALUES ('8', 'Education', 'education', '0', '0';


-- Table structure for `tags`
CREATE TABLE `tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `slug` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_tags_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `tags` VALUES ('1', 'Breaking News', 'breaking-news', '2026-04-08 01:06:25';
INSERT INTO `tags` VALUES ('2', 'Politics', 'politics', '2026-04-08 01:06:25';
INSERT INTO `tags` VALUES ('3', 'Sports', 'sports', '2026-04-08 01:06:25';
INSERT INTO `tags` VALUES ('4', 'Technology', 'technology', '2026-04-08 01:06:25';
INSERT INTO `tags` VALUES ('5', 'Business', 'business', '2026-04-08 01:06:25';
INSERT INTO `tags` VALUES ('6', 'Entertainment', 'entertainment', '2026-04-08 01:06:25';
INSERT INTO `tags` VALUES ('7', 'Health', 'health', '2026-04-08 01:06:25';
INSERT INTO `tags` VALUES ('8', 'Education', 'education', '2026-04-08 01:06:25';
INSERT INTO `tags` VALUES ('9', 'International', 'international', '2026-04-08 01:06:25';
INSERT INTO `tags` VALUES ('10', 'Pakistan', 'pakistan', '2026-04-08 01:06:25';
INSERT INTO `tags` VALUES ('11', 'COVID-19', 'covid-19', '2026-04-08 01:06:25';
INSERT INTO `tags` VALUES ('12', 'Economy', 'economy', '2026-04-08 01:06:25';
INSERT INTO `tags` VALUES ('13', 'Elections', 'elections', '2026-04-08 01:06:25';
INSERT INTO `tags` VALUES ('14', 'Cricket', 'cricket', '2026-04-08 01:06:25';
INSERT INTO `tags` VALUES ('15', 'Football', 'football', '2026-04-08 01:06:25';
INSERT INTO `tags` VALUES ('16', 'Science', 'science', '2026-04-08 01:06:25';
INSERT INTO `tags` VALUES ('17', 'Weather', 'weather', '2026-04-08 01:06:25';
INSERT INTO `tags` VALUES ('18', 'Climate', 'climate', '2026-04-08 01:06:25';
INSERT INTO `tags` VALUES ('19', 'Social Media', 'social-media', '2026-04-08 01:06:25';
INSERT INTO `tags` VALUES ('20', 'Security', 'security', '2026-04-08 01:06:25';


-- Table structure for `user_analytics`
CREATE TABLE `user_analytics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `page_url` varchar(500) DEFAULT NULL,
  `referrer` varchar(500) DEFAULT NULL,
  `time_on_page` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_analytics_user` (`user_id`),
  KEY `idx_user_analytics_session` (`session_id`),
  KEY `idx_user_analytics_date` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



-- Table structure for `users`
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','editor','reporter') DEFAULT 'reporter',
  `department` enum('editorial','reporting','technical','management','marketing','multimedia') DEFAULT NULL,
  `specialization` varchar(100) DEFAULT NULL,
  `experience_level` enum('junior','intermediate','senior','expert','lead') DEFAULT 'junior',
  `skills` text DEFAULT NULL,
  `social_links` text DEFAULT NULL,
  `status` enum('active','blocked') DEFAULT 'active',
  `bio` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_login` datetime DEFAULT NULL,
  `login_count` int(11) DEFAULT 0,
  `profile_views` int(11) DEFAULT 0,
  `articles_published` int(11) DEFAULT 0,
  `is_featured` tinyint(1) DEFAULT 0,
  `verification_status` enum('unverified','verified','premium') DEFAULT 'unverified',
  `preferred_language` varchar(10) DEFAULT 'en',
  `timezone` varchar(50) DEFAULT 'Asia/Karachi',
  `notification_preferences` text DEFAULT NULL,
  `working_hours` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_users_email` (`email`),
  KEY `idx_role_status` (`role`,`status`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `users` VALUES ('1', 'Admin', 'admin@pklivenews.com', NULL, '03300394061', '$2y$10$z7tXexbwC2AXXApILrMJ8u6HMa73qCJwSidPQUSWezVivifrTlepu', 'admin', NULL, NULL, 'junior', NULL, NULL, 'active', NULL, NULL, '2026-04-07 22:02:25', '2026-04-08 00:17:43', NULL, '0', '0', '0', '0', 'unverified', 'en', 'Asia/Karachi', NULL, NULL;



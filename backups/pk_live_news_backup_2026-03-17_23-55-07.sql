-- PK Live News Database Backup
-- Generated on: 2026-03-17 23:55:07
-- MySQL Version: 10.4.32-MariaDB

-- Table structure for `active_news_sources`
;

INSERT INTO `active_news_sources` VALUES ('35', 'BBC News', 'https://www.bbc.com/news', '3', 'https://feeds.bbci.co.uk/news/rss.xml', NULL, NULL, NULL, NULL, NULL, 'active', '2026-03-13 06:11:38', '30', '0', '13', 'Invalid RSS format', NULL, '2026-03-10 16:51:13', '2026-03-17 05:27:17', 'International', 'Due for scraping';
INSERT INTO `active_news_sources` VALUES ('36', 'Al Jazeera', 'https://www.aljazeera.com', '3', 'https://www.aljazeera.com/xml/rss/all.xml', NULL, NULL, NULL, NULL, NULL, 'active', '2026-03-13 05:30:00', '60', '0', '15', 'Invalid RSS format', NULL, '2026-03-10 16:51:13', '2026-03-16 20:33:00', 'International', 'Due for scraping';
INSERT INTO `active_news_sources` VALUES ('37', 'The Guardian', 'https://www.theguardian.com/world', '3', 'https://www.theguardian.com/world/rss', NULL, NULL, NULL, NULL, NULL, 'active', '2026-03-13 05:28:00', '60', '0', '9', 'Invalid RSS format', NULL, '2026-03-10 16:51:13', '2026-03-16 20:32:57', 'International', 'Due for scraping';
INSERT INTO `active_news_sources` VALUES ('39', 'CNN', 'https://www.cnn.com', '3', 'http://rss.cnn.com/rss/edition.rss', NULL, NULL, NULL, NULL, NULL, 'active', '2026-03-13 06:03:17', '30', '0', '8', 'Invalid RSS format', NULL, '2026-03-11 04:43:58', '2026-03-16 20:32:47', 'International', 'Due for scraping';
INSERT INTO `active_news_sources` VALUES ('40', 'ARY News', 'https://arynews.tv', '94', 'https://arynews.tv/feed/', NULL, NULL, NULL, NULL, NULL, 'active', '2026-03-13 05:29:02', '60', '0', '7', 'Invalid RSS format', NULL, '2026-03-11 04:43:58', '2026-03-16 20:32:50', 'Pakistan', 'Due for scraping';
INSERT INTO `active_news_sources` VALUES ('295', 'financialcontent', 'https://markets.financialcontent.com', '9', 'https://markets.financialcontent.com', NULL, NULL, NULL, NULL, NULL, 'active', NULL, '5', '0', '245', 'Failed to fetch RSS feed', NULL, '2026-03-11 20:40:19', '2026-03-17 04:55:40', 'World', 'Never scraped';
INSERT INTO `active_news_sources` VALUES ('296', 'Reuters', 'https://www.reuters.com', NULL, 'https://www.reuters.com/rssFeed', NULL, NULL, NULL, NULL, NULL, 'active', NULL, '60', '0', '101', 'Failed to fetch RSS feed', NULL, '2026-03-12 16:56:57', '2026-03-17 04:55:40', NULL, 'Never scraped';
INSERT INTO `active_news_sources` VALUES ('297', 'Fox News', 'https://www.foxnews.com', NULL, 'https://feeds.foxnews.com/foxnews/latest', NULL, NULL, NULL, NULL, NULL, 'active', '2026-03-13 05:26:01', '60', '0', '3', 'Invalid RSS format', NULL, '2026-03-12 16:56:57', '2026-03-17 05:29:12', NULL, 'Due for scraping';
INSERT INTO `active_news_sources` VALUES ('375', 'CNN News', 'http://rss.cnn.com/rss/edition.rss', '1', 'http://rss.cnn.com/rss/edition.rss', NULL, NULL, NULL, NULL, NULL, 'active', '2026-03-17 23:34:35', '60', '0', '4', 'Failed to fetch RSS feed', NULL, '2026-03-13 06:40:03', '2026-03-17 23:34:35', 'Politics', 'Recently scraped';
INSERT INTO `active_news_sources` VALUES ('376', 'Geo News', 'https://www.geo.tv/rss/feed/1', '1', 'https://www.geo.tv/rss/feed/1', NULL, NULL, NULL, NULL, NULL, 'active', NULL, '60', '0', '73', 'Failed to fetch RSS feed', NULL, '2026-03-13 06:40:03', '2026-03-17 04:55:40', 'Politics', 'Never scraped';
INSERT INTO `active_news_sources` VALUES ('377', 'CBS News', 'http://feeds.cbsnews.com/CBSNewsMain', '1', 'http://feeds.cbsnews.com/CBSNewsMain', NULL, NULL, NULL, NULL, NULL, 'active', '2026-03-17 23:33:57', '60', '0', '0', NULL, NULL, '2026-03-17 05:27:10', '2026-03-17 23:33:57', 'Politics', 'Recently scraped';
INSERT INTO `active_news_sources` VALUES ('378', 'NPR News', 'https://feeds.npr.org/1001/rss.xml', '1', 'https://feeds.npr.org/1001/rss.xml', NULL, NULL, NULL, NULL, NULL, 'active', '2026-03-17 23:34:49', '60', '0', '0', NULL, NULL, '2026-03-17 05:27:26', '2026-03-17 23:34:49', 'Politics', 'Recently scraped';
INSERT INTO `active_news_sources` VALUES ('379', 'Google News', 'https://news.google.com/rss', '1', 'https://news.google.com/rss', NULL, NULL, NULL, NULL, NULL, 'active', '2026-03-17 23:34:48', '60', '0', '0', NULL, NULL, '2026-03-17 05:27:31', '2026-03-17 23:34:48', 'Politics', 'Recently scraped';
INSERT INTO `active_news_sources` VALUES ('380', 'Yahoo News', 'https://news.yahoo.com/rss', '1', 'https://news.yahoo.com/rss', NULL, NULL, NULL, NULL, NULL, 'active', '2026-03-17 23:34:55', '60', '0', '0', NULL, NULL, '2026-03-17 05:27:34', '2026-03-17 23:34:55', 'Politics', 'Recently scraped';


-- Table structure for `advertisements`
CREATE TABLE `advertisements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `code` text DEFAULT NULL,
  `position` enum('header','sidebar','footer','popup') NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `clicks` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `advertisements` VALUES ('3', 'Love Mother', 'uploads/ads/69adaaa0ab59c.jpg', '', 'footer', 'active', '2026-03-08', '2026-03-11', '0', '2026-03-08 21:58:08';


-- Table structure for `ai_training_data`
CREATE TABLE `ai_training_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `news_id` int(11) NOT NULL,
  `human_verified_label` enum('REAL','FAKE','MISLEADING','SATIRE','UNCLEAR') NOT NULL,
  `human_confidence` decimal(5,2) DEFAULT NULL,
  `ai_predicted_label` enum('REAL','FAKE','MISLEADING','SATIRE','UNCLEAR') DEFAULT NULL,
  `ai_confidence` decimal(5,2) DEFAULT NULL,
  `content_features` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`content_features`)),
  `linguistic_features` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`linguistic_features`)),
  `source_features` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`source_features`)),
  `metadata_features` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata_features`)),
  `training_set` enum('TRAIN','VALIDATION','TEST') DEFAULT 'TRAIN',
  `label_source` varchar(50) DEFAULT NULL,
  `label_date` datetime DEFAULT current_timestamp(),
  `model_version` varchar(20) DEFAULT NULL,
  `prediction_accuracy` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_news_id` (`news_id`),
  KEY `idx_human_verified_label` (`human_verified_label`),
  KEY `idx_training_set` (`training_set`),
  KEY `idx_model_version` (`model_version`),
  CONSTRAINT `ai_training_data_ibfk_1` FOREIGN KEY (`news_id`) REFERENCES `news` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- Table structure for `alert_categories`
CREATE TABLE `alert_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `alert_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_alert_category` (`alert_id`,`category_id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `alert_categories_ibfk_1` FOREIGN KEY (`alert_id`) REFERENCES `breaking_news_alerts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `alert_categories_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- Table structure for `alert_delivery_log`
CREATE TABLE `alert_delivery_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `alert_id` int(11) NOT NULL,
  `subscription_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `delivery_status` enum('pending','sent','delivered','failed','clicked') DEFAULT 'pending',
  `error_message` text DEFAULT NULL,
  `response_data` text DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `clicked_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `idx_alert_status` (`alert_id`,`delivery_status`),
  KEY `idx_subscription` (`subscription_id`,`delivery_status`),
  KEY `idx_delivery_status` (`delivery_status`,`created_at`),
  CONSTRAINT `alert_delivery_log_ibfk_1` FOREIGN KEY (`alert_id`) REFERENCES `breaking_news_alerts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `alert_delivery_log_ibfk_2` FOREIGN KEY (`subscription_id`) REFERENCES `push_subscriptions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `alert_delivery_log_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- Table structure for `bookmark_folders`
CREATE TABLE `bookmark_folders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `color` varchar(7) DEFAULT '#007bff',
  `is_default` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_folder` (`user_id`,`name`),
  KEY `idx_bookmark_folders_user` (`user_id`),
  CONSTRAINT `bookmark_folders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `bookmark_folders` VALUES ('1', '1', 'Favorites', 'My favorite news articles', '#dc3545', '1', '2026-03-06 04:03:35';
INSERT INTO `bookmark_folders` VALUES ('2', '1', 'Read Later', 'Articles to read later', '#ffc107', '0', '2026-03-06 04:03:35';
INSERT INTO `bookmark_folders` VALUES ('3', '1', 'Important', 'Important news and updates', '#28a745', '0', '2026-03-06 04:03:35';
INSERT INTO `bookmark_folders` VALUES ('4', '1', 'Research', 'Articles for research purposes', '#6f42c1', '0', '2026-03-06 04:03:35';


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
  KEY `idx_bookmarks_created` (`created_at`),
  CONSTRAINT `bookmarks_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bookmarks_ibfk_2` FOREIGN KEY (`news_id`) REFERENCES `news` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- Table structure for `breaking_news_alerts`
CREATE TABLE `breaking_news_alerts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `news_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `alert_type` enum('breaking','urgent','update') DEFAULT 'breaking',
  `priority` enum('low','medium','high','critical') DEFAULT 'high',
  `target_audience` enum('all','subscribers','registered','custom') DEFAULT 'all',
  `send_push` tinyint(1) DEFAULT 1,
  `send_email` tinyint(1) DEFAULT 0,
  `send_sms` tinyint(1) DEFAULT 0,
  `send_mobile` tinyint(1) DEFAULT 1,
  `status` enum('pending','sending','completed','failed','cancelled') DEFAULT 'pending',
  `total_sent` int(11) DEFAULT 0,
  `total_delivered` int(11) DEFAULT 0,
  `total_failed` int(11) DEFAULT 0,
  `scheduled_at` timestamp NULL DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_status_priority` (`status`,`priority`),
  KEY `idx_news_id` (`news_id`),
  KEY `idx_scheduled_at` (`scheduled_at`),
  CONSTRAINT `breaking_news_alerts_ibfk_1` FOREIGN KEY (`news_id`) REFERENCES `news` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `breaking_news_alerts` VALUES ('1', '455', 'Alert', 'bad news', 'urgent', 'high', 'all', '1', '1', '0', '1', 'cancelled', '0', '0', '0', '2026-03-11 20:46:00', NULL, '2026-03-11 20:46:31', '2026-03-16 20:36:35';
INSERT INTO `breaking_news_alerts` VALUES ('2', '504', 'Mizile fire from west', 'dkbchueyvb ', 'update', 'high', 'all', '1', '1', '0', '1', 'cancelled', '0', '0', '0', '2026-03-12 09:43:00', NULL, '2026-03-12 09:43:17', '2026-03-16 20:36:32';


-- Table structure for `categories`
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `color` varchar(7) DEFAULT '#007bff',
  `slug` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `image` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_categories_slug` (`slug`),
  KEY `idx_categories_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=96 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `categories` VALUES ('1', 'Politics', '#dc3545', 'politics', 'Political news and updates', 'active', '2026-03-04 10:28:28', NULL;
INSERT INTO `categories` VALUES ('2', 'Sports', '#28a745', 'sports', 'Sports news and match results', 'inactive', '2026-03-04 10:28:28', NULL;
INSERT INTO `categories` VALUES ('3', 'International', '#007bff', 'international', 'International news and events', 'active', '2026-03-04 10:28:28', NULL;
INSERT INTO `categories` VALUES ('4', 'Technology', '#17a2b8', 'technology', 'Technology news and gadgets', 'inactive', '2026-03-04 10:28:28', NULL;
INSERT INTO `categories` VALUES ('5', 'Entertainment', '#6f42c1', 'entertainment', 'Entertainment and celebrity news', 'inactive', '2026-03-04 10:28:28', NULL;
INSERT INTO `categories` VALUES ('6', 'Business', '#ffc107', 'business', 'Business and financial news', 'inactive', '2026-03-04 10:28:28', NULL;
INSERT INTO `categories` VALUES ('7', 'Health', '#fd7e14', 'health', 'Health and medical news', 'inactive', '2026-03-06 15:26:45', NULL;
INSERT INTO `categories` VALUES ('8', 'Science', '#007bff', 'science', 'Science and research news', 'inactive', '2026-03-06 15:26:45', NULL;
INSERT INTO `categories` VALUES ('9', 'World', '#007bff', 'world', 'International news and events', 'inactive', '2026-03-06 15:26:45', NULL;
INSERT INTO `categories` VALUES ('10', 'CNN', '#007bff', 'cnn', 'CNN', 'inactive', '2026-03-09 19:37:25', NULL;
INSERT INTO `categories` VALUES ('11', 'CNN World News', '#007bff', 'cnn-world', 'CNN World News - International news and global stories from CNN', 'active', '2026-03-09 20:07:02', NULL;
INSERT INTO `categories` VALUES ('12', 'CNN Politics', '#dc3545', 'cnn-politics', 'CNN Politics - Political news, analysis, and coverage from CNN', 'active', '2026-03-09 20:07:02', NULL;
INSERT INTO `categories` VALUES ('13', 'BBC World News', '#007bff', 'bbc-world', 'BBC World News - International news and global stories from BBC', 'active', '2026-03-09 21:00:57', NULL;
INSERT INTO `categories` VALUES ('14', 'BBC Politics', '#dc3545', 'bbc-politics', 'BBC Politics - Political news, analysis, and coverage from BBC', 'active', '2026-03-09 21:00:57', NULL;
INSERT INTO `categories` VALUES ('15', 'BBC Business', '#ffc107', 'bbc-business', 'BBC Business - Business news, markets, and financial coverage from BBC', 'inactive', '2026-03-09 21:00:57', NULL;
INSERT INTO `categories` VALUES ('16', 'BBC Technology', '#17a2b8', 'bbc-technology', 'BBC Technology - Tech news, gadgets, and innovation from BBC', 'active', '2026-03-09 21:00:57', NULL;
INSERT INTO `categories` VALUES ('17', 'BBC Entertainment', '#6f42c1', 'bbc-entertainment', 'BBC Entertainment - Celebrity news, movies, and entertainment from BBC', 'active', '2026-03-09 21:00:57', NULL;
INSERT INTO `categories` VALUES ('18', 'BBC Sports', '#28a745', 'bbc-sports', 'BBC Sports - Sports news, scores, and coverage from BBC', 'active', '2026-03-09 21:00:57', NULL;
INSERT INTO `categories` VALUES ('19', 'CNN US News', '#007bff', 'cnn-us-news', 'CNN US News - Latest news and stories from across the United States', 'active', '2026-03-09 21:31:39', NULL;
INSERT INTO `categories` VALUES ('20', 'CNN International', '#007bff', 'cnn-international', 'CNN International - Global news and international coverage from CNN', 'active', '2026-03-09 21:31:39', NULL;
INSERT INTO `categories` VALUES ('21', 'CNN Opinion', '#007bff', 'cnn-opinion', 'CNN Opinion - Commentary, editorials, and analysis from CNN contributors', 'inactive', '2026-03-09 21:31:39', NULL;
INSERT INTO `categories` VALUES ('22', 'CNN Travel', '#007bff', 'cnn-travel', 'CNN Travel - Travel news, destinations, and tourism updates from CNN', 'inactive', '2026-03-09 21:31:39', NULL;
INSERT INTO `categories` VALUES ('23', 'CNN Style', '#007bff', 'cnn-style', 'CNN Style - Fashion, design, and lifestyle coverage from CNN', 'inactive', '2026-03-09 21:31:39', NULL;
INSERT INTO `categories` VALUES ('24', 'CNN Arts', '#007bff', 'cnn-arts', 'CNN Arts - Arts and culture news, exhibitions, and reviews from CNN', 'inactive', '2026-03-09 21:31:39', NULL;
INSERT INTO `categories` VALUES ('25', 'CNN Weather', '#007bff', 'cnn-weather', 'CNN Weather - Weather forecasts, climate news, and severe weather updates', 'inactive', '2026-03-09 21:31:39', NULL;
INSERT INTO `categories` VALUES ('26', 'CNN Environment', '#007bff', 'cnn-environment', 'CNN Environment - Environmental news, climate change, and sustainability coverage', 'active', '2026-03-09 21:31:39', NULL;
INSERT INTO `categories` VALUES ('27', 'CNN Education', '#20c997', 'cnn-education', 'CNN Education - Education news, learning resources, and academic coverage', 'inactive', '2026-03-09 21:31:39', NULL;
INSERT INTO `categories` VALUES ('28', 'CNN Media', '#6f42c1', 'cnn-media', 'CNN Media - Media industry news, journalism, and broadcasting coverage', 'active', '2026-03-09 21:31:39', NULL;
INSERT INTO `categories` VALUES ('29', 'CNN Security', '#007bff', 'cnn-security', 'CNN Security - National security, cybersecurity, and defense news', 'inactive', '2026-03-09 21:31:39', NULL;
INSERT INTO `categories` VALUES ('30', 'CNN Justice', '#007bff', 'cnn-justice', 'CNN Justice - Legal news, court cases, and criminal justice coverage', 'inactive', '2026-03-09 21:31:39', NULL;
INSERT INTO `categories` VALUES ('31', 'CNN Economy', '#ffc107', 'cnn-economy', 'CNN Economy - Economic news, markets, and financial analysis from CNN', 'inactive', '2026-03-09 21:31:39', NULL;
INSERT INTO `categories` VALUES ('32', 'CNN Energy', '#007bff', 'cnn-energy', 'CNN Energy - Energy news, oil, gas, renewable energy, and power sector coverage', 'inactive', '2026-03-09 21:31:39', NULL;
INSERT INTO `categories` VALUES ('33', 'CNN Autos', '#007bff', 'cnn-autos', 'CNN Autos - Automotive news, car reviews, and transportation coverage', 'inactive', '2026-03-09 21:31:39', NULL;
INSERT INTO `categories` VALUES ('34', 'CNN Real Estate', '#007bff', 'cnn-real-estate', 'CNN Real Estate - Housing market, property news, and real estate coverage', 'inactive', '2026-03-09 21:31:39', NULL;
INSERT INTO `categories` VALUES ('35', 'CNN Food', '#007bff', 'cnn-food', 'CNN Food - Food news, recipes, restaurants, and culinary coverage', 'inactive', '2026-03-09 21:31:39', NULL;
INSERT INTO `categories` VALUES ('36', 'CNN Wellness', '#007bff', 'cnn-wellness', 'CNN Wellness - Health tips, fitness, mental health, and wellness coverage', 'inactive', '2026-03-09 21:31:39', NULL;
INSERT INTO `categories` VALUES ('37', 'CNN Tech Culture', '#e83e8c', 'cnn-tech-culture', 'CNN Tech Culture - Technology trends, digital culture, and innovation coverage', 'inactive', '2026-03-09 21:31:39', NULL;
INSERT INTO `categories` VALUES ('38', 'CNN Space', '#007bff', 'cnn-space', 'CNN Space - Space exploration, NASA, astronomy, and space science news', 'inactive', '2026-03-09 21:31:39', NULL;
INSERT INTO `categories` VALUES ('39', 'ARY News', '#007bff', 'ary', 'ARY Digital News - Pakistan\'s leading news channel', 'inactive', '2026-03-09 22:12:00', NULL;
INSERT INTO `categories` VALUES ('40', 'ARY World', '#007bff', 'ary-world', 'International news from ARY', 'inactive', '2026-03-09 22:12:00', NULL;
INSERT INTO `categories` VALUES ('41', 'ARY Politics', '#dc3545', 'ary-politics', 'Political news and analysis from ARY', 'inactive', '2026-03-09 22:12:00', NULL;
INSERT INTO `categories` VALUES ('43', 'ARY Technology', '#17a2b8', 'ary-technology', 'Technology news and updates from ARY', 'inactive', '2026-03-09 22:12:00', NULL;
INSERT INTO `categories` VALUES ('45', 'ARY Sports', '#28a745', 'ary-sports', 'Sports news coverage from ARY', 'inactive', '2026-03-09 22:12:00', NULL;
INSERT INTO `categories` VALUES ('46', 'ARY Pakistan', '#007bff', 'ary-pakistan', 'National news from Pakistan', 'inactive', '2026-03-09 22:12:00', NULL;
INSERT INTO `categories` VALUES ('94', 'Pakistan', '#007bff', '', 'Pakistani news and current affairs', 'active', '2026-03-11 18:54:19', NULL;


-- Table structure for `category_analytics`
CREATE TABLE `category_analytics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `total_articles` int(11) DEFAULT 0,
  `total_views` int(11) DEFAULT 0,
  `total_shares` int(11) DEFAULT 0,
  `avg_engagement` decimal(5,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_category_date` (`category_id`,`date`),
  KEY `idx_category_analytics_date` (`date`),
  KEY `idx_category_analytics_views` (`total_views`),
  CONSTRAINT `category_analytics_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- Table structure for `comments`
CREATE TABLE `comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `news_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `comment` text NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `idx_comments_news` (`news_id`),
  KEY `idx_comments_status` (`status`),
  CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`news_id`) REFERENCES `news` (`id`) ON DELETE CASCADE,
  CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- Table structure for `content_patterns`
CREATE TABLE `content_patterns` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pattern_name` varchar(100) NOT NULL,
  `pattern_type` enum('LINGUISTIC','STRUCTURAL','SEMANTIC','EMOTIONAL','PROPAGANDA') NOT NULL,
  `pattern_regex` text DEFAULT NULL,
  `pattern_keywords` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`pattern_keywords`)),
  `pattern_description` text DEFAULT NULL,
  `false_positive_rate` decimal(5,2) DEFAULT NULL,
  `detection_rate` decimal(5,2) DEFAULT NULL,
  `confidence_weight` decimal(5,2) DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1,
  `severity_level` enum('LOW','MEDIUM','HIGH') DEFAULT 'MEDIUM',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `pattern_name` (`pattern_name`),
  KEY `idx_pattern_type` (`pattern_type`),
  KEY `idx_active` (`active`),
  KEY `idx_severity_level` (`severity_level`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `content_patterns` VALUES ('1', 'Sensationalist Words', 'EMOTIONAL', NULL, '[\"shocking\", \"unbelievable\", \"incredible\", \"amazing\", \"miracle\"]', 'Detects sensationalist language', NULL, NULL, '0.80', '1', '', '2026-03-16 21:02:52', '2026-03-16 21:02:52';
INSERT INTO `content_patterns` VALUES ('2', 'Clickbait Patterns', 'STRUCTURAL', NULL, '[\"you won\\u0027t believe\", \"what happens next\", \"the truth about\"]', 'Identifies clickbait headlines', NULL, NULL, '0.90', '1', '', '2026-03-16 21:02:52', '2026-03-16 21:02:52';
INSERT INTO `content_patterns` VALUES ('3', 'Urgency Indicators', 'EMOTIONAL', NULL, '[\"urgent\", \"breaking\", \"immediate\", \"critical\"]', 'Detects false urgency', NULL, NULL, '0.70', '1', '', '2026-03-16 21:02:52', '2026-03-16 21:02:52';
INSERT INTO `content_patterns` VALUES ('4', 'Conspiracy Language', 'PROPAGANDA', NULL, '[\"they don\\u0027t want you to know\", \"hidden truth\", \"cover up\"]', 'Identifies conspiracy theory language', NULL, NULL, '0.85', '1', '', '2026-03-16 21:02:52', '2026-03-16 21:02:52';
INSERT INTO `content_patterns` VALUES ('5', 'Unverified Claims', 'SEMANTIC', NULL, '[\"sources say\", \"experts believe\", \"studies show\"]', 'Flags unverified source claims', NULL, NULL, '0.60', '1', '', '2026-03-16 21:02:52', '2026-03-16 21:02:52';
INSERT INTO `content_patterns` VALUES ('6', 'Emotional Manipulation', 'EMOTIONAL', NULL, '[\"heartbreaking\", \"terrifying\", \"outrageous\"]', 'Detects emotionally manipulative language', NULL, NULL, '0.75', '1', '', '2026-03-16 21:02:52', '2026-03-16 21:02:52';


-- Table structure for `deployment_alerts`
CREATE TABLE `deployment_alerts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `deployment_id` int(11) NOT NULL,
  `alert_type` enum('stream_down','low_bitrate','high_latency','connection_lost','server_error') NOT NULL,
  `severity` enum('low','medium','high','critical') DEFAULT 'medium',
  `message` text NOT NULL,
  `resolved` tinyint(1) DEFAULT 0,
  `resolved_at` datetime DEFAULT NULL,
  `resolved_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_alerts_deployment` (`deployment_id`),
  KEY `idx_alerts_resolved` (`resolved`),
  KEY `idx_alerts_severity` (`severity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- Table structure for `deployment_cameras`
CREATE TABLE `deployment_cameras` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `deployment_id` int(11) NOT NULL,
  `camera_number` int(11) NOT NULL,
  `camera_name` varchar(255) NOT NULL,
  `stream_url` varchar(500) DEFAULT NULL,
  `stream_key` varchar(255) DEFAULT NULL,
  `position` enum('main','picture_in_picture','side_by_side','grid') DEFAULT 'side_by_side',
  `quality` enum('auto','240p','360p','480p','720p','1080p') DEFAULT 'auto',
  `is_active` tinyint(1) DEFAULT 1,
  `priority` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_camera` (`deployment_id`,`camera_number`),
  KEY `idx_cameras_active` (`is_active`),
  KEY `idx_cameras_priority` (`priority`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- Table structure for `deployment_criteria`
CREATE TABLE `deployment_criteria` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `criteria_name` varchar(255) NOT NULL,
  `criteria_type` enum('technical','content','operational','safety','regulatory') NOT NULL,
  `description` text DEFAULT NULL,
  `check_type` enum('automatic','manual','conditional') DEFAULT 'automatic',
  `validation_rule` varchar(500) DEFAULT NULL,
  `threshold_value` decimal(10,2) DEFAULT NULL,
  `comparison_operator` enum('equals','greater_than','less_than','greater_equal','less_equal','not_equals') DEFAULT 'equals',
  `is_active` tinyint(1) DEFAULT 1,
  `is_required` tinyint(1) DEFAULT 1,
  `priority_level` enum('low','medium','high','critical') DEFAULT 'medium',
  `check_frequency` enum('once','every_minute','every_5_minutes','every_15_minutes','every_hour') DEFAULT 'once',
  `timeout_minutes` int(11) DEFAULT 30,
  `depends_on_criteria` int(11) DEFAULT NULL,
  `notify_on_failure` tinyint(1) DEFAULT 1,
  `notification_emails` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `depends_on_criteria` (`depends_on_criteria`),
  KEY `created_by` (`created_by`),
  KEY `idx_criteria_type` (`criteria_type`),
  KEY `idx_criteria_active` (`is_active`),
  KEY `idx_criteria_priority` (`priority_level`),
  CONSTRAINT `deployment_criteria_ibfk_1` FOREIGN KEY (`depends_on_criteria`) REFERENCES `deployment_criteria` (`id`) ON DELETE SET NULL,
  CONSTRAINT `deployment_criteria_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `deployment_criteria` VALUES ('1', 'Stream Health Check', 'technical', 'Verify that the primary stream is accessible and healthy', 'automatic', 'stream_health_check', NULL, 'equals', '1', '1', 'critical', 'once', '30', NULL, '1', NULL, NULL, '2026-03-09 10:44:07', '2026-03-09 10:44:07';
INSERT INTO `deployment_criteria` VALUES ('2', 'Backup Stream Available', 'technical', 'Ensure backup stream is configured and accessible', 'automatic', 'backup_stream_check', NULL, 'equals', '1', '0', 'high', 'once', '30', NULL, '1', NULL, NULL, '2026-03-09 10:44:07', '2026-03-09 10:44:07';
INSERT INTO `deployment_criteria` VALUES ('3', 'Bandwidth Test', 'technical', 'Verify sufficient bandwidth for streaming quality', 'automatic', 'bandwidth_test', NULL, 'equals', '1', '1', 'high', 'once', '30', NULL, '1', NULL, NULL, '2026-03-09 10:44:07', '2026-03-09 10:44:07';
INSERT INTO `deployment_criteria` VALUES ('4', 'Latency Check', 'technical', 'Stream latency should be below 5 seconds', 'automatic', 'latency_check', NULL, 'equals', '1', '1', 'medium', 'once', '30', NULL, '1', NULL, NULL, '2026-03-09 10:44:07', '2026-03-09 10:44:07';
INSERT INTO `deployment_criteria` VALUES ('5', 'Audio Quality', 'technical', 'Audio levels should be within acceptable range', 'automatic', 'audio_quality_check', NULL, 'equals', '1', '1', 'medium', 'once', '30', NULL, '1', NULL, NULL, '2026-03-09 10:44:07', '2026-03-09 10:44:07';
INSERT INTO `deployment_criteria` VALUES ('6', 'Content Review', 'content', 'Content must be reviewed and approved', 'manual', 'content_approval', NULL, 'equals', '1', '1', 'critical', 'once', '30', NULL, '1', NULL, NULL, '2026-03-09 10:44:07', '2026-03-09 10:44:07';
INSERT INTO `deployment_criteria` VALUES ('7', 'Title and Description Complete', 'content', 'Stream title and description must be filled', 'automatic', 'metadata_complete', NULL, 'equals', '1', '1', 'medium', 'once', '30', NULL, '1', NULL, NULL, '2026-03-09 10:44:07', '2026-03-09 10:44:07';
INSERT INTO `deployment_criteria` VALUES ('8', 'Thumbnail Uploaded', 'content', 'Stream thumbnail must be uploaded', 'automatic', 'thumbnail_exists', NULL, 'equals', '1', '0', 'low', 'once', '30', NULL, '1', NULL, NULL, '2026-03-09 10:44:07', '2026-03-09 10:44:07';
INSERT INTO `deployment_criteria` VALUES ('9', 'Category Assigned', 'content', 'Stream must be assigned to a category', 'automatic', 'category_assigned', NULL, 'equals', '1', '1', 'medium', 'once', '30', NULL, '1', NULL, NULL, '2026-03-09 10:44:07', '2026-03-09 10:44:07';
INSERT INTO `deployment_criteria` VALUES ('10', 'Staff Available', 'operational', 'Required technical staff must be available', 'manual', 'staff_availability', NULL, 'equals', '1', '1', 'high', 'once', '30', NULL, '1', NULL, NULL, '2026-03-09 10:44:07', '2026-03-09 10:44:07';
INSERT INTO `deployment_criteria` VALUES ('11', 'Equipment Check', 'operational', 'All required equipment must be tested', 'manual', 'equipment_test', NULL, 'equals', '1', '1', 'high', 'once', '30', NULL, '1', NULL, NULL, '2026-03-09 10:44:07', '2026-03-09 10:44:07';
INSERT INTO `deployment_criteria` VALUES ('12', 'Backup Power', 'operational', 'Backup power systems must be operational', 'manual', 'backup_power_check', NULL, 'equals', '1', '0', 'medium', 'once', '30', NULL, '1', NULL, NULL, '2026-03-09 10:44:07', '2026-03-09 10:44:07';
INSERT INTO `deployment_criteria` VALUES ('13', 'Monitoring Setup', 'operational', 'Monitoring and alerting systems must be active', 'automatic', 'monitoring_active', NULL, 'equals', '1', '1', 'medium', 'once', '30', NULL, '1', NULL, NULL, '2026-03-09 10:44:07', '2026-03-09 10:44:07';
INSERT INTO `deployment_criteria` VALUES ('14', 'Safety Clearance', 'safety', 'Location safety inspection completed', 'manual', 'safety_inspection', NULL, 'equals', '1', '1', 'critical', 'once', '30', NULL, '1', NULL, NULL, '2026-03-09 10:44:07', '2026-03-09 10:44:07';
INSERT INTO `deployment_criteria` VALUES ('15', 'Emergency Contacts', 'safety', 'Emergency contact information updated', 'manual', 'emergency_contacts', NULL, 'equals', '1', '1', 'medium', 'once', '30', NULL, '1', NULL, NULL, '2026-03-09 10:44:07', '2026-03-09 10:44:07';
INSERT INTO `deployment_criteria` VALUES ('16', 'Weather Conditions', 'safety', 'Weather conditions suitable for outdoor streaming', 'conditional', 'weather_check', NULL, 'equals', '1', '0', 'high', 'once', '30', NULL, '1', NULL, NULL, '2026-03-09 10:44:07', '2026-03-09 10:44:07';
INSERT INTO `deployment_criteria` VALUES ('18', 'Content Rating', 'regulatory', 'Content rating properly assigned', 'manual', 'content_rating', NULL, 'equals', '1', '1', 'medium', 'once', '30', NULL, '1', NULL, NULL, '2026-03-09 10:44:07', '2026-03-09 10:44:07';
INSERT INTO `deployment_criteria` VALUES ('19', 'Copyright Clearance', 'regulatory', 'All content copyright cleared', 'manual', 'copyright_check', NULL, 'equals', '1', '1', 'high', 'once', '30', NULL, '1', NULL, NULL, '2026-03-09 10:44:07', '2026-03-09 10:44:07';
INSERT INTO `deployment_criteria` VALUES ('20', 'Stream Health Check', 'technical', 'Verify that the primary stream is accessible and healthy', 'automatic', 'stream_health_check', NULL, 'equals', '1', '1', 'critical', 'once', '30', NULL, '1', NULL, NULL, '2026-03-09 11:02:27', '2026-03-09 11:02:27';
INSERT INTO `deployment_criteria` VALUES ('22', 'Bandwidth Test', 'technical', 'Verify sufficient bandwidth for streaming quality', 'automatic', 'bandwidth_test', NULL, 'equals', '1', '1', 'high', 'once', '30', NULL, '1', NULL, NULL, '2026-03-09 11:02:27', '2026-03-09 11:02:27';
INSERT INTO `deployment_criteria` VALUES ('23', 'Latency Check', 'technical', 'Stream latency should be below 5 seconds', 'automatic', 'latency_check', NULL, 'equals', '1', '1', 'medium', 'once', '30', NULL, '1', NULL, NULL, '2026-03-09 11:02:27', '2026-03-09 11:02:27';
INSERT INTO `deployment_criteria` VALUES ('24', 'Audio Quality', 'technical', 'Audio levels should be within acceptable range', 'automatic', 'audio_quality_check', NULL, 'equals', '1', '1', 'medium', 'once', '30', NULL, '1', NULL, NULL, '2026-03-09 11:02:27', '2026-03-09 11:02:27';
INSERT INTO `deployment_criteria` VALUES ('25', 'Content Review', 'content', 'Content must be reviewed and approved', 'manual', 'content_approval', NULL, 'equals', '1', '1', 'critical', 'once', '30', NULL, '1', NULL, NULL, '2026-03-09 11:02:27', '2026-03-09 11:02:27';
INSERT INTO `deployment_criteria` VALUES ('26', 'Title and Description Complete', 'content', 'Stream title and description must be filled', 'automatic', 'metadata_complete', NULL, 'equals', '1', '1', 'medium', 'once', '30', NULL, '1', NULL, NULL, '2026-03-09 11:02:27', '2026-03-09 11:02:27';
INSERT INTO `deployment_criteria` VALUES ('28', 'Category Assigned', 'content', 'Stream must be assigned to a category', 'automatic', 'category_assigned', NULL, 'equals', '1', '1', 'medium', 'once', '30', NULL, '1', NULL, NULL, '2026-03-09 11:02:27', '2026-03-09 11:02:27';
INSERT INTO `deployment_criteria` VALUES ('29', 'Staff Available', 'operational', 'Required technical staff must be available', 'manual', 'staff_availability', NULL, 'equals', '1', '1', 'high', 'once', '30', NULL, '1', NULL, NULL, '2026-03-09 11:02:27', '2026-03-09 11:02:27';
INSERT INTO `deployment_criteria` VALUES ('30', 'Equipment Check', 'operational', 'All required equipment must be tested', 'manual', 'equipment_test', NULL, 'equals', '1', '1', 'high', 'once', '30', NULL, '1', NULL, NULL, '2026-03-09 11:02:27', '2026-03-09 11:02:27';
INSERT INTO `deployment_criteria` VALUES ('31', 'Backup Power', 'operational', 'Backup power systems must be operational', 'manual', 'backup_power_check', NULL, 'equals', '1', '0', 'medium', 'once', '30', NULL, '1', NULL, NULL, '2026-03-09 11:02:27', '2026-03-09 11:02:27';
INSERT INTO `deployment_criteria` VALUES ('32', 'Monitoring Setup', 'operational', 'Monitoring and alerting systems must be active', 'automatic', 'monitoring_active', NULL, 'equals', '1', '1', 'medium', 'once', '30', NULL, '1', NULL, NULL, '2026-03-09 11:02:27', '2026-03-09 11:02:27';
INSERT INTO `deployment_criteria` VALUES ('34', 'Emergency Contacts', 'safety', 'Emergency contact information updated', 'manual', 'emergency_contacts', NULL, 'equals', '1', '1', 'medium', 'once', '30', NULL, '1', NULL, NULL, '2026-03-09 11:02:27', '2026-03-09 11:02:27';
INSERT INTO `deployment_criteria` VALUES ('35', 'Weather Conditions', 'safety', 'Weather conditions suitable for outdoor streaming', 'conditional', 'weather_check', NULL, 'equals', '1', '0', 'high', 'once', '30', NULL, '1', NULL, NULL, '2026-03-09 11:02:27', '2026-03-09 11:02:27';
INSERT INTO `deployment_criteria` VALUES ('36', 'Broadcast License', 'regulatory', 'Valid broadcast license for content type', 'manual', 'license_check', NULL, 'equals', '1', '1', 'critical', 'once', '30', NULL, '1', NULL, NULL, '2026-03-09 11:02:27', '2026-03-09 11:02:27';
INSERT INTO `deployment_criteria` VALUES ('37', 'Content Rating', 'regulatory', 'Content rating properly assigned', 'manual', 'content_rating', NULL, 'equals', '1', '1', 'medium', 'once', '30', NULL, '1', NULL, NULL, '2026-03-09 11:02:27', '2026-03-09 11:02:27';
INSERT INTO `deployment_criteria` VALUES ('38', 'Copyright Clearance', 'regulatory', 'All content copyright cleared', 'manual', 'copyright_check', NULL, 'equals', '1', '1', 'high', 'once', '30', NULL, '1', NULL, NULL, '2026-03-09 11:02:27', '2026-03-09 11:02:27';


-- Table structure for `deployment_criteria_checks`
CREATE TABLE `deployment_criteria_checks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `deployment_id` int(11) NOT NULL,
  `criteria_id` int(11) NOT NULL,
  `check_status` enum('pending','passed','failed','skipped','warning') DEFAULT 'pending',
  `check_value` varchar(255) DEFAULT NULL,
  `check_result` text DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `checked_by` int(11) DEFAULT NULL,
  `check_timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_deployment_criteria_check` (`deployment_id`,`criteria_id`),
  KEY `criteria_id` (`criteria_id`),
  KEY `checked_by` (`checked_by`),
  KEY `idx_checks_deployment` (`deployment_id`),
  KEY `idx_checks_status` (`check_status`),
  KEY `idx_checks_timestamp` (`check_timestamp`),
  CONSTRAINT `deployment_criteria_checks_ibfk_1` FOREIGN KEY (`deployment_id`) REFERENCES `live_deployments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `deployment_criteria_checks_ibfk_2` FOREIGN KEY (`criteria_id`) REFERENCES `deployment_criteria` (`id`) ON DELETE CASCADE,
  CONSTRAINT `deployment_criteria_checks_ibfk_3` FOREIGN KEY (`checked_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- Table structure for `deployment_feedback`
CREATE TABLE `deployment_feedback` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `deployment_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `feedback_type` enum('quality','performance','content','technical','general') DEFAULT 'general',
  `rating` tinyint(1) DEFAULT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `feedback_text` text DEFAULT NULL,
  `video_quality` enum('excellent','good','fair','poor') DEFAULT NULL,
  `audio_quality` enum('excellent','good','fair','poor') DEFAULT NULL,
  `stream_stability` enum('stable','occasional_buffering','frequent_buffering','unwatchable') DEFAULT NULL,
  `loading_speed` enum('instant','fast','moderate','slow') DEFAULT NULL,
  `device_type` enum('desktop','mobile','tablet','tv') DEFAULT 'desktop',
  `browser` varchar(100) DEFAULT NULL,
  `operating_system` varchar(100) DEFAULT NULL,
  `connection_type` enum('wifi','mobile','ethernet','unknown') DEFAULT 'unknown',
  `country` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `referrer` varchar(500) DEFAULT NULL,
  `watch_duration` int(11) DEFAULT 0,
  `status` enum('pending','reviewed','resolved','dismissed') DEFAULT 'pending',
  `priority` enum('low','medium','high','critical') DEFAULT 'medium',
  `assigned_to` int(11) DEFAULT NULL,
  `admin_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_feedback_deployment` (`deployment_id`),
  KEY `idx_feedback_user` (`user_id`),
  KEY `idx_feedback_type` (`feedback_type`),
  KEY `idx_feedback_status` (`status`),
  KEY `idx_feedback_priority` (`priority`),
  KEY `idx_feedback_created` (`created_at`),
  KEY `idx_feedback_rating` (`rating`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- Table structure for `deployment_logs`
CREATE TABLE `deployment_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `deployment_id` int(11) NOT NULL,
  `log_type` enum('info','warning','error','success') DEFAULT 'info',
  `message` text NOT NULL,
  `details` text DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_logs_deployment` (`deployment_id`),
  KEY `idx_logs_type` (`log_type`),
  KEY `idx_logs_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- Table structure for `deployment_settings`
CREATE TABLE `deployment_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `deployment_settings` VALUES ('1', 'max_concurrent_streams', '10', NULL, '2026-03-08 04:18:43';
INSERT INTO `deployment_settings` VALUES ('2', 'default_bitrate', '2500', NULL, '2026-03-08 04:18:43';
INSERT INTO `deployment_settings` VALUES ('3', 'default_quality', '720p', NULL, '2026-03-08 04:18:43';
INSERT INTO `deployment_settings` VALUES ('4', 'auto_failover', '1', NULL, '2026-03-08 04:18:43';
INSERT INTO `deployment_settings` VALUES ('5', 'recording_enabled', '1', NULL, '2026-03-08 04:18:43';
INSERT INTO `deployment_settings` VALUES ('6', 'monitoring_enabled', '1', NULL, '2026-03-08 04:18:43';
INSERT INTO `deployment_settings` VALUES ('7', 'alert_threshold', '5', NULL, '2026-03-08 04:18:43';
INSERT INTO `deployment_settings` VALUES ('8', 'backup_enabled', '1', NULL, '2026-03-08 04:18:43';
INSERT INTO `deployment_settings` VALUES ('9', 'cdn_provider', 'cloudflare', NULL, '2026-03-08 04:18:43';


-- Table structure for `deployment_stats`
CREATE TABLE `deployment_stats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `deployment_id` int(11) NOT NULL,
  `viewer_count` int(11) DEFAULT 0,
  `bandwidth_kbps` int(11) DEFAULT 0,
  `cpu_usage` decimal(5,2) DEFAULT 0.00,
  `memory_usage` decimal(5,2) DEFAULT 0.00,
  `latency_ms` int(11) DEFAULT 0,
  `packet_loss` decimal(5,2) DEFAULT 0.00,
  `recorded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_stats_deployment` (`deployment_id`),
  KEY `idx_stats_recorded` (`recorded_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- Table structure for `deployment_templates`
CREATE TABLE `deployment_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `template_name` varchar(255) NOT NULL,
  `template_type` enum('single','multi_camera','scheduled','emergency') DEFAULT 'single',
  `description` text DEFAULT NULL,
  `default_quality` enum('auto','240p','360p','480p','720p','1080p','4K') DEFAULT '720p',
  `default_bitrate` int(11) DEFAULT 2500,
  `default_fps` int(11) DEFAULT 30,
  `auto_failover` tinyint(1) DEFAULT 1,
  `recording_enabled` tinyint(1) DEFAULT 1,
  `chat_enabled` tinyint(1) DEFAULT 1,
  `settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`settings`)),
  `status` enum('active','inactive') DEFAULT 'active',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_templates_type` (`template_type`),
  KEY `idx_templates_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `deployment_templates` VALUES ('1', 'Basic Single Stream', 'single', 'Standard single camera streaming setup', '', '2500', '30', '1', '1', '1', '{\"auto_start\":false,\"priority\":\"medium\",\"auto_failover\":true,\"analytics_enabled\":true}', 'active', NULL, '2026-03-08 04:18:43', '2026-03-08 04:18:43';
INSERT INTO `deployment_templates` VALUES ('2', 'Multi-Camera Production', 'multi_camera', 'Professional multi-camera setup with switching', '', '5000', '30', '1', '1', '1', '{\"auto_start\":false,\"priority\":\"medium\",\"auto_failover\":true,\"analytics_enabled\":true}', 'active', NULL, '2026-03-08 04:18:43', '2026-03-08 04:18:43';
INSERT INTO `deployment_templates` VALUES ('3', 'Emergency Broadcast', 'emergency', 'Quick deployment for breaking news coverage', '', '1500', '25', '1', '1', '1', '{\"auto_start\":true,\"priority\":\"critical\",\"auto_failover\":true,\"analytics_enabled\":true}', 'active', NULL, '2026-03-08 04:18:43', '2026-03-08 04:18:43';
INSERT INTO `deployment_templates` VALUES ('4', 'Scheduled News Program', 'scheduled', 'Pre-planned news program with scheduling', '', '4000', '30', '1', '1', '1', '{\"auto_start\":false,\"priority\":\"medium\",\"auto_failover\":true,\"analytics_enabled\":true}', 'active', NULL, '2026-03-08 04:18:43', '2026-03-08 04:18:43';
INSERT INTO `deployment_templates` VALUES ('5', 'Basic Single Stream', 'single', 'Standard single camera streaming setup', '', '2500', '30', '1', '1', '1', '{\"auto_start\":false,\"priority\":\"medium\",\"auto_failover\":true,\"analytics_enabled\":true}', 'active', NULL, '2026-03-08 04:26:15', '2026-03-08 04:26:15';
INSERT INTO `deployment_templates` VALUES ('6', 'Multi-Camera Production', 'multi_camera', 'Professional multi-camera setup with switching', '', '5000', '30', '1', '1', '1', '{\"auto_start\":false,\"priority\":\"medium\",\"auto_failover\":true,\"analytics_enabled\":true}', 'active', NULL, '2026-03-08 04:26:15', '2026-03-08 04:26:15';
INSERT INTO `deployment_templates` VALUES ('7', 'Emergency Broadcast', 'emergency', 'Quick deployment for breaking news coverage', '', '1500', '25', '1', '1', '1', '{\"auto_start\":true,\"priority\":\"critical\",\"auto_failover\":true,\"analytics_enabled\":true}', 'active', NULL, '2026-03-08 04:26:15', '2026-03-08 04:26:15';
INSERT INTO `deployment_templates` VALUES ('8', 'Scheduled News Program', 'scheduled', 'Pre-planned news program with scheduling', '', '4000', '30', '1', '1', '1', '{\"auto_start\":false,\"priority\":\"medium\",\"auto_failover\":true,\"analytics_enabled\":true}', 'active', NULL, '2026-03-08 04:26:15', '2026-03-08 04:26:15';
INSERT INTO `deployment_templates` VALUES ('9', 'Basic Single Stream', 'single', 'Standard single camera streaming setup', '', '2500', '30', '1', '1', '1', '{\"auto_start\":false,\"priority\":\"medium\",\"auto_failover\":true,\"analytics_enabled\":true}', 'active', NULL, '2026-03-08 05:45:34', '2026-03-08 05:45:34';
INSERT INTO `deployment_templates` VALUES ('10', 'Multi-Camera Production', 'multi_camera', 'Professional multi-camera setup with switching', '', '5000', '30', '1', '1', '1', '{\"auto_start\":false,\"priority\":\"medium\",\"auto_failover\":true,\"analytics_enabled\":true}', 'active', NULL, '2026-03-08 05:45:34', '2026-03-08 05:45:34';
INSERT INTO `deployment_templates` VALUES ('11', 'Emergency Broadcast', 'emergency', 'Quick deployment for breaking news coverage', '', '1500', '25', '1', '1', '1', '{\"auto_start\":true,\"priority\":\"critical\",\"auto_failover\":true,\"analytics_enabled\":true}', 'active', NULL, '2026-03-08 05:45:34', '2026-03-08 05:45:34';
INSERT INTO `deployment_templates` VALUES ('12', 'Scheduled News Program', 'scheduled', 'Pre-planned news program with scheduling', '', '4000', '30', '1', '1', '1', '{\"auto_start\":false,\"priority\":\"medium\",\"auto_failover\":true,\"analytics_enabled\":true}', 'active', NULL, '2026-03-08 05:45:34', '2026-03-08 05:45:34';
INSERT INTO `deployment_templates` VALUES ('13', 'Basic Single Stream', 'single', 'Standard single camera streaming setup', '', '2500', '30', '1', '1', '1', '{\"auto_start\":false,\"priority\":\"medium\",\"auto_failover\":true,\"analytics_enabled\":true}', 'active', NULL, '2026-03-08 06:13:19', '2026-03-08 06:13:19';
INSERT INTO `deployment_templates` VALUES ('14', 'Multi-Camera Production', 'multi_camera', 'Professional multi-camera setup with switching', '', '5000', '30', '1', '1', '1', '{\"auto_start\":false,\"priority\":\"medium\",\"auto_failover\":true,\"analytics_enabled\":true}', 'active', NULL, '2026-03-08 06:13:19', '2026-03-08 06:13:19';
INSERT INTO `deployment_templates` VALUES ('15', 'Emergency Broadcast', 'emergency', 'Quick deployment for breaking news coverage', '', '1500', '25', '1', '1', '1', '{\"auto_start\":true,\"priority\":\"critical\",\"auto_failover\":true,\"analytics_enabled\":true}', 'active', NULL, '2026-03-08 06:13:19', '2026-03-08 06:13:19';
INSERT INTO `deployment_templates` VALUES ('16', 'Scheduled News Program', 'scheduled', 'Pre-planned news program with scheduling', '', '4000', '30', '1', '1', '1', '{\"auto_start\":false,\"priority\":\"medium\",\"auto_failover\":true,\"analytics_enabled\":true}', 'active', NULL, '2026-03-08 06:13:19', '2026-03-08 06:13:19';


-- Table structure for `deployment_tests`
CREATE TABLE `deployment_tests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `deployment_id` int(11) NOT NULL,
  `test_type` varchar(50) NOT NULL,
  `test_results` text DEFAULT NULL,
  `status` enum('passed','failed','running') DEFAULT 'running',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_tests_deployment` (`deployment_id`),
  KEY `idx_tests_type` (`test_type`),
  KEY `idx_tests_status` (`status`),
  KEY `idx_tests_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- Table structure for `edition_categories`
CREATE TABLE `edition_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `slug` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `color` varchar(7) DEFAULT '#dc3545',
  `icon` varchar(50) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_edition_categories_slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `edition_categories` VALUES ('1', 'Morning Edition', 'morning', 'Daily morning news updates', '#28a745', 'fa-sun', 'active', '2026-03-06 03:40:45';
INSERT INTO `edition_categories` VALUES ('2', 'Evening Edition', 'evening', 'Daily evening news summary', '#6f42c1', 'fa-moon', 'active', '2026-03-06 03:40:45';
INSERT INTO `edition_categories` VALUES ('3', 'Breaking News', 'breaking', 'Urgent breaking news updates', '#dc3545', 'fa-exclamation-triangle', 'active', '2026-03-06 03:40:45';
INSERT INTO `edition_categories` VALUES ('4', 'Special Edition', 'special', 'Special coverage and reports', '#fd7e14', 'fa-star', 'active', '2026-03-06 03:40:45';
INSERT INTO `edition_categories` VALUES ('5', 'Weekend Edition', 'weekend', 'Weekend news and features', '#20c997', 'fa-calendar-week', 'active', '2026-03-06 03:40:45';
INSERT INTO `edition_categories` VALUES ('6', 'Regional Edition', 'regional', 'Regional news and updates', '#17a2b8', 'fa-map-marker-alt', 'active', '2026-03-06 03:40:45';


-- Table structure for `edition_schedule`
CREATE TABLE `edition_schedule` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `edition_type` enum('morning','evening','breaking','special','weekend','regional') NOT NULL,
  `schedule_time` time NOT NULL,
  `days_of_week` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`days_of_week`)),
  `auto_publish` tinyint(1) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_edition_schedule_type` (`edition_type`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `edition_schedule` VALUES ('1', 'morning', '06:00:00', '[\"monday\", \"tuesday\", \"wednesday\", \"thursday\", \"friday\", \"saturday\", \"sunday\"]', '1', 'active', '2026-03-06 03:40:45', '2026-03-06 03:40:45';
INSERT INTO `edition_schedule` VALUES ('2', 'evening', '18:00:00', '[\"monday\", \"tuesday\", \"wednesday\", \"thursday\", \"friday\", \"saturday\", \"sunday\"]', '1', 'active', '2026-03-06 03:40:45', '2026-03-06 03:40:45';
INSERT INTO `edition_schedule` VALUES ('3', 'breaking', '00:00:00', '[\"monday\", \"tuesday\", \"wednesday\", \"thursday\", \"friday\", \"saturday\", \"sunday\"]', '0', 'active', '2026-03-06 03:40:45', '2026-03-06 03:40:45';
INSERT INTO `edition_schedule` VALUES ('4', 'special', '12:00:00', '[\"monday\", \"tuesday\", \"wednesday\", \"thursday\", \"friday\"]', '0', 'active', '2026-03-06 03:40:45', '2026-03-06 03:40:45';
INSERT INTO `edition_schedule` VALUES ('5', 'weekend', '09:00:00', '[\"saturday\", \"sunday\"]', '1', 'active', '2026-03-06 03:40:45', '2026-03-06 03:40:45';
INSERT INTO `edition_schedule` VALUES ('6', 'regional', '08:00:00', '[\"monday\", \"tuesday\", \"wednesday\", \"thursday\", \"friday\"]', '0', 'active', '2026-03-06 03:40:45', '2026-03-06 03:40:45';


-- Table structure for `edition_templates`
CREATE TABLE `edition_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `template_type` enum('morning','evening','breaking','special','weekend','regional') NOT NULL,
  `template_content` text NOT NULL,
  `css_styles` text DEFAULT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_edition_templates_type` (`template_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- Table structure for `email_queue`
CREATE TABLE `email_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `to_email` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `template` varchar(100) DEFAULT NULL,
  `variables` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`variables`)),
  `priority` enum('low','medium','high') DEFAULT 'medium',
  `attempts` int(11) DEFAULT 0,
  `max_attempts` int(11) DEFAULT 3,
  `sent_at` timestamp NULL DEFAULT NULL,
  `next_attempt_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_email_queue_priority` (`priority`),
  KEY `idx_email_queue_attempts` (`attempts`),
  KEY `idx_email_queue_next_attempt` (`next_attempt_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- Table structure for `fact_check_references`
CREATE TABLE `fact_check_references` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `news_id` int(11) NOT NULL,
  `reference_url` varchar(1000) NOT NULL,
  `reference_title` varchar(500) DEFAULT NULL,
  `reference_source` varchar(255) DEFAULT NULL,
  `reference_type` enum('FACT_CHECK','OFFICIAL_STATEMENT','EXPERT_ANALYSIS','SCIENTIFIC_STUDY','NEWS_CORROBORATION','DENUNCIATION') DEFAULT 'FACT_CHECK',
  `reference_credibility` decimal(5,2) DEFAULT NULL,
  `relevance_score` decimal(5,2) DEFAULT NULL,
  `verification_status` enum('VERIFIED','DISPUTED','DEBUNKED','UNVERIFIED') DEFAULT 'UNVERIFIED',
  `content_similarity` decimal(5,2) DEFAULT NULL,
  `factual_agreement` decimal(5,2) DEFAULT NULL,
  `publish_date` date DEFAULT NULL,
  `author` varchar(255) DEFAULT NULL,
  `extracted_date` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_news_id` (`news_id`),
  KEY `idx_reference_type` (`reference_type`),
  KEY `idx_verification_status` (`verification_status`),
  CONSTRAINT `fact_check_references_ibfk_1` FOREIGN KEY (`news_id`) REFERENCES `news` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- Table structure for `fake_news_alerts`
CREATE TABLE `fake_news_alerts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `news_id` int(11) NOT NULL,
  `alert_type` enum('LOW_CREDIBILITY','SOURCE_UNVERIFIED','SENSATIONALISM','PROPAGANDA','CLICKBAIT','MANIPULATION') NOT NULL,
  `severity` enum('INFO','WARNING','CRITICAL') DEFAULT 'WARNING',
  `message` text NOT NULL,
  `details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`details`)),
  `status` enum('ACTIVE','REVIEWED','RESOLVED','DISMISSED') DEFAULT 'ACTIVE',
  `assigned_to` int(11) DEFAULT NULL,
  `reviewed_by` int(11) DEFAULT NULL,
  `review_notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `reviewed_at` datetime DEFAULT NULL,
  `resolved_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_news_id` (`news_id`),
  KEY `idx_alert_type` (`alert_type`),
  KEY `idx_severity` (`severity`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `fake_news_alerts_ibfk_1` FOREIGN KEY (`news_id`) REFERENCES `news` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- Table structure for `feedback_analytics`
CREATE TABLE `feedback_analytics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `deployment_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `total_feedback` int(11) DEFAULT 0,
  `average_rating` decimal(3,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_analytics` (`deployment_id`,`date`),
  KEY `idx_analytics_deployment` (`deployment_id`),
  KEY `idx_analytics_date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- Table structure for `feedback_categories`
CREATE TABLE `feedback_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(100) NOT NULL,
  `category_description` text DEFAULT NULL,
  `category_color` varchar(7) DEFAULT '#007bff',
  `icon` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_categories_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `feedback_categories` VALUES ('1', 'Video Quality', 'Issues related to video clarity, resolution, and visual quality', '#dc3545', 'fas fa-video', '1', '2026-03-08 04:18:43', '2026-03-08 04:18:43';
INSERT INTO `feedback_categories` VALUES ('2', 'Audio Quality', 'Issues related to sound, audio clarity, and volume', '#fd7e14', 'fas fa-volume-up', '1', '2026-03-08 04:18:43', '2026-03-08 04:18:43';
INSERT INTO `feedback_categories` VALUES ('3', 'Stream Performance', 'Buffering, loading, and connectivity issues', '#ffc107', 'fas fa-wifi', '1', '2026-03-08 04:18:43', '2026-03-08 04:18:43';
INSERT INTO `feedback_categories` VALUES ('4', 'Content', 'Feedback about the content being streamed', '#28a745', 'fas fa-newspaper', '1', '2026-03-08 04:18:43', '2026-03-08 04:18:43';
INSERT INTO `feedback_categories` VALUES ('5', 'User Interface', 'Feedback about the player interface and user experience', '#17a2b8', 'fas fa-desktop', '1', '2026-03-08 04:18:43', '2026-03-08 04:18:43';
INSERT INTO `feedback_categories` VALUES ('6', 'General', 'General feedback and suggestions', '#6c757d', 'fas fa-comment', '1', '2026-03-08 04:18:43', '2026-03-08 04:18:43';
INSERT INTO `feedback_categories` VALUES ('7', 'Video Quality', 'Issues related to video clarity, resolution, and visual quality', '#dc3545', 'fas fa-video', '1', '2026-03-08 04:26:15', '2026-03-08 04:26:15';
INSERT INTO `feedback_categories` VALUES ('8', 'Audio Quality', 'Issues related to sound, audio clarity, and volume', '#fd7e14', 'fas fa-volume-up', '1', '2026-03-08 04:26:15', '2026-03-08 04:26:15';
INSERT INTO `feedback_categories` VALUES ('9', 'Stream Performance', 'Buffering, loading, and connectivity issues', '#ffc107', 'fas fa-wifi', '1', '2026-03-08 04:26:15', '2026-03-08 04:26:15';
INSERT INTO `feedback_categories` VALUES ('10', 'Content', 'Feedback about the content being streamed', '#28a745', 'fas fa-newspaper', '1', '2026-03-08 04:26:15', '2026-03-08 04:26:15';
INSERT INTO `feedback_categories` VALUES ('11', 'User Interface', 'Feedback about the player interface and user experience', '#17a2b8', 'fas fa-desktop', '1', '2026-03-08 04:26:15', '2026-03-08 04:26:15';
INSERT INTO `feedback_categories` VALUES ('12', 'General', 'General feedback and suggestions', '#6c757d', 'fas fa-comment', '1', '2026-03-08 04:26:15', '2026-03-08 04:26:15';
INSERT INTO `feedback_categories` VALUES ('13', 'Video Quality', 'Issues related to video clarity, resolution, and visual quality', '#dc3545', 'fas fa-video', '1', '2026-03-08 05:45:34', '2026-03-08 05:45:34';
INSERT INTO `feedback_categories` VALUES ('14', 'Audio Quality', 'Issues related to sound, audio clarity, and volume', '#fd7e14', 'fas fa-volume-up', '1', '2026-03-08 05:45:34', '2026-03-08 05:45:34';
INSERT INTO `feedback_categories` VALUES ('15', 'Stream Performance', 'Buffering, loading, and connectivity issues', '#ffc107', 'fas fa-wifi', '1', '2026-03-08 05:45:34', '2026-03-08 05:45:34';
INSERT INTO `feedback_categories` VALUES ('16', 'Content', 'Feedback about the content being streamed', '#28a745', 'fas fa-newspaper', '1', '2026-03-08 05:45:34', '2026-03-08 05:45:34';
INSERT INTO `feedback_categories` VALUES ('17', 'User Interface', 'Feedback about the player interface and user experience', '#17a2b8', 'fas fa-desktop', '1', '2026-03-08 05:45:34', '2026-03-08 05:45:34';
INSERT INTO `feedback_categories` VALUES ('18', 'General', 'General feedback and suggestions', '#6c757d', 'fas fa-comment', '1', '2026-03-08 05:45:34', '2026-03-08 05:45:34';
INSERT INTO `feedback_categories` VALUES ('19', 'Video Quality', 'Issues related to video clarity, resolution, and visual quality', '#dc3545', 'fas fa-video', '1', '2026-03-08 06:13:19', '2026-03-08 06:13:19';
INSERT INTO `feedback_categories` VALUES ('20', 'Audio Quality', 'Issues related to sound, audio clarity, and volume', '#fd7e14', 'fas fa-volume-up', '1', '2026-03-08 06:13:19', '2026-03-08 06:13:19';
INSERT INTO `feedback_categories` VALUES ('21', 'Stream Performance', 'Buffering, loading, and connectivity issues', '#ffc107', 'fas fa-wifi', '1', '2026-03-08 06:13:19', '2026-03-08 06:13:19';
INSERT INTO `feedback_categories` VALUES ('22', 'Content', 'Feedback about the content being streamed', '#28a745', 'fas fa-newspaper', '1', '2026-03-08 06:13:19', '2026-03-08 06:13:19';
INSERT INTO `feedback_categories` VALUES ('23', 'User Interface', 'Feedback about the player interface and user experience', '#17a2b8', 'fas fa-desktop', '1', '2026-03-08 06:13:19', '2026-03-08 06:13:19';
INSERT INTO `feedback_categories` VALUES ('24', 'General', 'General feedback and suggestions', '#6c757d', 'fas fa-comment', '1', '2026-03-08 06:13:19', '2026-03-08 06:13:19';


-- Table structure for `feedback_notifications`
CREATE TABLE `feedback_notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `feedback_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `notification_type` enum('new_feedback','response_added','status_changed') DEFAULT 'new_feedback',
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_notifications_user` (`user_id`),
  KEY `idx_notifications_read` (`is_read`),
  KEY `idx_notifications_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- Table structure for `feedback_responses`
CREATE TABLE `feedback_responses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `feedback_id` int(11) NOT NULL,
  `responder_id` int(11) NOT NULL,
  `response_text` text NOT NULL,
  `response_type` enum('comment','resolution','request_info') DEFAULT 'comment',
  `is_public` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_responses_feedback` (`feedback_id`),
  KEY `idx_responses_responder` (`responder_id`),
  KEY `idx_responses_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- Table structure for `feedback_tag_relations`
CREATE TABLE `feedback_tag_relations` (
  `feedback_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  PRIMARY KEY (`feedback_id`,`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- Table structure for `feedback_tags`
CREATE TABLE `feedback_tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tag_name` varchar(50) NOT NULL,
  `tag_color` varchar(7) DEFAULT '#6c757d',
  `usage_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `tag_name` (`tag_name`),
  KEY `idx_tags_usage` (`usage_count`),
  KEY `idx_tags_name` (`tag_name`)
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `feedback_tags` VALUES ('1', 'buffering', '#6c757d', '0', '2026-03-08 04:18:43';
INSERT INTO `feedback_tags` VALUES ('2', 'lag', '#6c757d', '0', '2026-03-08 04:18:43';
INSERT INTO `feedback_tags` VALUES ('3', 'quality', '#6c757d', '0', '2026-03-08 04:18:43';
INSERT INTO `feedback_tags` VALUES ('4', 'audio', '#6c757d', '0', '2026-03-08 04:18:43';
INSERT INTO `feedback_tags` VALUES ('5', 'video', '#6c757d', '0', '2026-03-08 04:18:43';
INSERT INTO `feedback_tags` VALUES ('6', 'crash', '#6c757d', '0', '2026-03-08 04:18:43';
INSERT INTO `feedback_tags` VALUES ('7', 'feature_request', '#6c757d', '0', '2026-03-08 04:18:43';
INSERT INTO `feedback_tags` VALUES ('8', 'bug', '#6c757d', '0', '2026-03-08 04:18:43';
INSERT INTO `feedback_tags` VALUES ('9', 'suggestion', '#6c757d', '0', '2026-03-08 04:18:43';
INSERT INTO `feedback_tags` VALUES ('10', 'complaint', '#6c757d', '0', '2026-03-08 04:18:43';
INSERT INTO `feedback_tags` VALUES ('11', 'praise', '#6c757d', '0', '2026-03-08 04:18:43';
INSERT INTO `feedback_tags` VALUES ('12', 'urgent', '#6c757d', '0', '2026-03-08 04:18:43';


-- Table structure for `generated_images`
CREATE TABLE `generated_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `news_id` int(11) NOT NULL,
  `original_url` varchar(500) DEFAULT NULL,
  `local_path` varchar(255) NOT NULL,
  `prompt` text DEFAULT NULL,
  `provider` enum('openai','stability','replicate','placeholder') DEFAULT 'openai',
  `generation_time` decimal(8,3) DEFAULT NULL COMMENT 'Time taken to generate in seconds',
  `file_size` int(11) DEFAULT NULL COMMENT 'File size in bytes',
  `dimensions` varchar(20) DEFAULT NULL COMMENT 'Image dimensions like 1024x1024',
  `status` enum('pending','completed','failed') DEFAULT 'pending',
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_news_id` (`news_id`),
  KEY `idx_status` (`status`),
  KEY `idx_provider` (`provider`),
  CONSTRAINT `generated_images_ibfk_1` FOREIGN KEY (`news_id`) REFERENCES `news` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- Table structure for `heatmap_config`
CREATE TABLE `heatmap_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `config_key` varchar(100) NOT NULL,
  `config_value` text NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_config` (`config_key`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `heatmap_config` VALUES ('1', 'update_interval', '30', 'Update interval in seconds', '2026-03-11 17:34:30', '2026-03-11 17:34:30';
INSERT INTO `heatmap_config` VALUES ('2', 'max_heat_score', '100.00', 'Maximum possible heat score', '2026-03-11 17:34:30', '2026-03-11 17:34:30';
INSERT INTO `heatmap_config` VALUES ('3', 'view_weight', '1.0', 'Weight for view count in heat score calculation', '2026-03-11 17:34:30', '2026-03-11 17:34:30';
INSERT INTO `heatmap_config` VALUES ('4', 'interaction_weight', '2.0', 'Weight for interaction count in heat score calculation', '2026-03-11 17:34:30', '2026-03-11 17:34:30';
INSERT INTO `heatmap_config` VALUES ('5', 'share_weight', '3.0', 'Weight for share count in heat score calculation', '2026-03-11 17:34:30', '2026-03-11 17:34:30';
INSERT INTO `heatmap_config` VALUES ('6', 'comment_weight', '2.5', 'Weight for comment count in heat score calculation', '2026-03-11 17:34:30', '2026-03-11 17:34:30';
INSERT INTO `heatmap_config` VALUES ('7', 'time_decay_hours', '24', 'Hours after which heat score starts to decay', '2026-03-11 17:34:30', '2026-03-11 17:34:30';
INSERT INTO `heatmap_config` VALUES ('8', 'enabled_regions', '[\"PK\", \"US\", \"GB\", \"AE\", \"SA\", \"IN\"]', 'List of enabled country codes for tracking', '2026-03-11 17:34:30', '2026-03-11 17:34:30';


-- Table structure for `image_generation_queue`
CREATE TABLE `image_generation_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `news_id` int(11) NOT NULL,
  `priority` enum('low','medium','high') DEFAULT 'medium',
  `provider` enum('openai','stability','replicate','placeholder') DEFAULT 'openai',
  `style` varchar(100) DEFAULT 'realistic journalistic news photo',
  `status` enum('pending','processing','completed','failed') DEFAULT 'pending',
  `attempts` int(11) DEFAULT 0,
  `max_attempts` int(11) DEFAULT 3,
  `error_message` text DEFAULT NULL,
  `scheduled_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `processed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_status_priority` (`status`,`priority`),
  KEY `idx_news_id` (`news_id`),
  CONSTRAINT `image_generation_queue_ibfk_1` FOREIGN KEY (`news_id`) REFERENCES `news` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- Table structure for `image_generation_stats`
;



-- Table structure for `image_settings`
CREATE TABLE `image_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `image_settings` VALUES ('1', 'openai_api_key', '', 'OpenAI API key for DALL-E image generation', '2026-03-13 06:00:21';
INSERT INTO `image_settings` VALUES ('2', 'stability_api_key', '', 'Stability AI API key for image generation', '2026-03-13 06:00:21';
INSERT INTO `image_settings` VALUES ('3', 'replicate_api_key', '', 'Replicate API key for image generation', '2026-03-13 06:00:21';
INSERT INTO `image_settings` VALUES ('4', 'default_provider', 'openai', 'Default AI provider for image generation', '2026-03-13 06:00:21';
INSERT INTO `image_settings` VALUES ('5', 'default_style', 'realistic journalistic news photo', 'Default style for generated images', '2026-03-13 06:00:21';
INSERT INTO `image_settings` VALUES ('6', 'max_queue_size', '100', 'Maximum number of items in image generation queue', '2026-03-13 06:00:21';
INSERT INTO `image_settings` VALUES ('7', 'rate_limit_delay', '1', 'Delay between API calls in seconds', '2026-03-13 06:00:21';
INSERT INTO `image_settings` VALUES ('8', 'auto_generate_rss', 'false', 'Automatically generate images for RSS imports', '2026-03-13 06:00:21';
INSERT INTO `image_settings` VALUES ('9', 'image_quality', 'standard', 'Image quality setting (standard/hd)', '2026-03-13 06:00:21';
INSERT INTO `image_settings` VALUES ('10', 'image_size', '1024x1024', 'Default image dimensions', '2026-03-13 06:00:21';


-- Table structure for `live_comments`
CREATE TABLE `live_comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `stream_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `username` varchar(100) NOT NULL,
  `comment` text NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'approved',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `idx_comments_stream` (`stream_id`),
  KEY `idx_comments_status` (`status`),
  KEY `idx_comments_created` (`created_at`),
  CONSTRAINT `live_comments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `live_comments_ibfk_2` FOREIGN KEY (`stream_id`) REFERENCES `live_stream` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- Table structure for `live_deployments`
CREATE TABLE `live_deployments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `deployment_name` varchar(255) NOT NULL,
  `stream_id` int(11) DEFAULT NULL,
  `deployment_type` enum('single','multi_camera','scheduled','emergency') DEFAULT 'single',
  `status` enum('preparing','testing','ready','live','ended','failed') DEFAULT 'preparing',
  `priority` enum('low','medium','high','critical') DEFAULT 'medium',
  `primary_stream_url` varchar(500) DEFAULT NULL,
  `backup_stream_url` varchar(500) DEFAULT NULL,
  `stream_key` varchar(255) DEFAULT NULL,
  `rtmp_url` varchar(500) DEFAULT NULL,
  `hls_url` varchar(500) DEFAULT NULL,
  `embed_code` text DEFAULT NULL,
  `auto_start` tinyint(1) DEFAULT 0,
  `auto_failover` tinyint(1) DEFAULT 1,
  `recording_enabled` tinyint(1) DEFAULT 1,
  `chat_enabled` tinyint(1) DEFAULT 1,
  `analytics_enabled` tinyint(1) DEFAULT 1,
  `stream_quality` enum('auto','240p','360p','480p','720p','1080p','4K') DEFAULT 'auto',
  `bitrate` int(11) DEFAULT 2500,
  `fps` int(11) DEFAULT 30,
  `scheduled_start` datetime DEFAULT NULL,
  `scheduled_end` datetime DEFAULT NULL,
  `actual_start` datetime DEFAULT NULL,
  `actual_end` datetime DEFAULT NULL,
  `duration_minutes` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `tags` text DEFAULT NULL,
  `health_check_url` varchar(500) DEFAULT NULL,
  `monitoring_enabled` tinyint(1) DEFAULT 1,
  `alert_threshold` int(11) DEFAULT 5,
  `peak_viewers` int(11) DEFAULT 0,
  `total_views` int(11) DEFAULT 0,
  `avg_watch_time` int(11) DEFAULT 0,
  `bandwidth_used` bigint(20) DEFAULT 0,
  `server_region` varchar(100) DEFAULT NULL,
  `cdn_provider` varchar(100) DEFAULT NULL,
  `stream_protocol` enum('rtmp','hls','dash','webrtc') DEFAULT 'rtmp',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_deployments_status` (`status`),
  KEY `idx_deployments_scheduled` (`scheduled_start`),
  KEY `idx_deployments_priority` (`priority`),
  KEY `idx_deployments_type` (`deployment_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- Table structure for `live_stream`
CREATE TABLE `live_stream` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `stream_url` varchar(500) NOT NULL,
  `embed_code` text DEFAULT NULL,
  `status` enum('offline','online','scheduled') DEFAULT 'offline',
  `schedule_time` timestamp NULL DEFAULT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `multi_camera_config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Configuration for multiple cameras' CHECK (json_valid(`multi_camera_config`)),
  `overlay_config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Overlay configuration and settings' CHECK (json_valid(`overlay_config`)),
  `active_camera` int(11) DEFAULT 1 COMMENT 'Currently active camera (1-based index)',
  `camera_count` int(11) DEFAULT 1 COMMENT 'Total number of cameras configured',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- Table structure for `live_viewer_stats`
CREATE TABLE `live_viewer_stats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `stream_id` int(11) NOT NULL,
  `viewer_count` int(11) DEFAULT 0,
  `peak_viewers` int(11) DEFAULT 0,
  `avg_duration` int(11) DEFAULT 0,
  `recorded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_stats_stream` (`stream_id`),
  KEY `idx_stats_recorded` (`recorded_at`),
  CONSTRAINT `fk_stats_stream` FOREIGN KEY (`stream_id`) REFERENCES `live_stream` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- Table structure for `live_viewers`
CREATE TABLE `live_viewers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` varchar(255) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `stream_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `last_activity` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_viewers_active` (`is_active`),
  KEY `idx_viewers_last_activity` (`last_activity`),
  KEY `idx_viewers_stream` (`stream_id`),
  KEY `idx_viewers_session` (`session_id`,`stream_id`),
  KEY `fk_viewers_user` (`user_id`),
  CONSTRAINT `fk_viewers_stream` FOREIGN KEY (`stream_id`) REFERENCES `live_stream` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_viewers_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- Table structure for `mobile_delivery_log`
CREATE TABLE `mobile_delivery_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `alert_id` int(11) NOT NULL,
  `devices_sent` int(11) DEFAULT 0,
  `devices_delivered` int(11) DEFAULT 0,
  `devices_failed` int(11) DEFAULT 0,
  `response_data` text DEFAULT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_alert_id` (`alert_id`),
  KEY `idx_mobile_delivery_alert` (`alert_id`,`sent_at`),
  CONSTRAINT `mobile_delivery_log_ibfk_1` FOREIGN KEY (`alert_id`) REFERENCES `breaking_news_alerts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- Table structure for `mobile_devices`
CREATE TABLE `mobile_devices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(500) NOT NULL,
  `platform` enum('android','ios') NOT NULL,
  `device_id` varchar(255) DEFAULT NULL,
  `app_version` varchar(50) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `last_seen` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `idx_token` (`token`(255)),
  KEY `idx_platform_active` (`platform`,`is_active`),
  KEY `idx_user_active` (`user_id`,`is_active`),
  KEY `idx_mobile_devices_active` (`is_active`,`platform`),
  CONSTRAINT `mobile_devices_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- Table structure for `news`
CREATE TABLE `news` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `excerpt` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `video_url` varchar(255) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `author_id` int(11) DEFAULT NULL,
  `status` enum('draft','published','featured') DEFAULT 'draft',
  `news_type` varchar(50) DEFAULT 'manual',
  `is_breaking` tinyint(1) DEFAULT 0,
  `views` int(11) DEFAULT 0,
  `likes_count` int(11) DEFAULT 0,
  `engagement_score` decimal(10,2) DEFAULT 0.00,
  `share_count` int(11) DEFAULT 0,
  `comment_count` int(11) DEFAULT 0,
  `published_at` timestamp NULL DEFAULT NULL,
  `source_url` varchar(500) DEFAULT NULL COMMENT 'Original source URL for scraped articles',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `sentiment_score` decimal(3,2) DEFAULT 0.00,
  `sentiment_label` varchar(20) DEFAULT 'neutral',
  `time_status` enum('new','recent','older') DEFAULT 'older',
  `image_generated_at` timestamp NULL DEFAULT NULL COMMENT 'When the AI image was generated',
  `image_provider` varchar(50) DEFAULT NULL COMMENT 'Which AI provider generated the image',
  `image_prompt` text DEFAULT NULL COMMENT 'The prompt used to generate the image',
  `last_credibility_check` datetime DEFAULT NULL,
  `credibility_status` enum('CHECKED','PENDING','FLAGGED','REVIEW_REQUIRED') DEFAULT 'PENDING',
  `credibility_score` decimal(5,2) DEFAULT NULL,
  `url_slug` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  UNIQUE KEY `url_slug` (`url_slug`),
  KEY `author_id` (`author_id`),
  KEY `idx_news_status` (`status`),
  KEY `idx_news_category` (`category_id`),
  KEY `idx_news_published` (`published_at`),
  KEY `idx_news_engagement` (`engagement_score`),
  KEY `idx_news_share_count` (`share_count`),
  KEY `idx_news_comment_count` (`comment_count`),
  KEY `idx_source_url` (`source_url`(255)),
  KEY `idx_published_at` (`published_at`),
  KEY `idx_status_published` (`status`,`published_at`),
  KEY `idx_category_status` (`category_id`,`status`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_news_credibility_status` (`credibility_status`),
  KEY `idx_news_last_credibility_check` (`last_credibility_check`),
  CONSTRAINT `news_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `news_ibfk_2` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=567 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `news` VALUES ('410', 'Sport Gala 2025', 'sport-gala-2025', 'This my first sport gala,, i really enjoy this....', 'This my first sport gala,, i really enjoy this....', 'uploads/news/69b04222d518e.jpg', '0', '13', '1', 'published', 'manual', '1', '5', '0', '0.00', '0', '0', '0000-00-00 00:00:00', NULL, '2026-03-10 21:09:06', '2026-03-11 20:50:26', '0.58', '0', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('411', 'Mother Day', 'mother-day', '<p>Love with Mother Its, create Your Life. Good day Enjoy..&nbsp;</p>', 'Love with Mother Its, create Your Life. Good day Enjoy..', 'uploads/news/69b0452b38f08.jpg', '0', '20', '1', 'published', 'manual', '0', '4', '0', '0.00', '0', '0', '0000-00-00 00:00:00', NULL, '2026-03-10 21:22:03', '2026-03-12 07:00:45', '1.00', '0', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('412', 'UK warship HMS Dragon departs for eastern Mediterranean', 'uk-warship-hms-dragon-departs-for-eastern-mediterranean', 'The Type 45 destroyer\'s main role will be protecting RAF Akrotiri, which was hit with an Iranian-made drone.', 'The Type 45 destroyer\'s main role will be protecting RAF Akrotiri, which was hit with an Iranian-made drone.', '', NULL, '1', '1', 'published', 'manual', '0', '1', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://www.bbc.com/news/articles/c0k1yyg475ko?at_medium=RSS&at_campaign=rss', '2026-03-11 04:44:57', '2026-03-11 17:02:57', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('413', 'Steve Rosenberg: Russia seeks diplomatic and economic gains from Iran war', 'steve-rosenberg-russia-seeks-diplomatic-and-economic-gains-from-iran-war', 'President Putin pits himself as a potential mediator but that\'s not an easy sell, writes the BBC\'s Russia editor.', 'President Putin pits himself as a potential mediator but that\'s not an easy sell, writes the BBC\'s Russia editor.', '', NULL, '1', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://www.bbc.com/news/articles/c4gjyg0djvmo?at_medium=RSS&at_campaign=rss', '2026-03-11 04:44:57', '2026-03-11 12:10:01', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('414', 'Hereditary peers to be removed from Lords as bill passes', 'hereditary-peers-to-be-removed-from-lords-as-bill-passes', 'The bill abolishes the 92 seats reserved for peers who inherit their titles through their families.', 'The bill abolishes the 92 seats reserved for peers who inherit their titles through their families.', '', NULL, '1', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://www.bbc.com/news/articles/cdxg76rgdp7o?at_medium=RSS&at_campaign=rss', '2026-03-11 04:44:57', '2026-03-11 12:10:41', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('415', 'Brazil’s Jair Bolsonaro seeks court approval for visit from Trump official', 'brazil-s-jair-bolsonaro-seeks-court-approval-for-visit-from-trump-official', 'Lawyers for Bolsonaro have petitioned for Trump adviser Darren Beattie to meet the ex-president in a Brasilia prison.', 'Lawyers for Bolsonaro have petitioned for Trump adviser Darren Beattie to meet the ex-president in a Brasilia prison.', '', NULL, '1', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://www.aljazeera.com/news/2026/3/10/brazils-jair-bolsonaro-seeks-court-approval-for-visit-from-trump-official?traffic_source=rss', '2026-03-11 04:44:59', '2026-03-11 11:54:28', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('416', 'Democrats say White House offers no clarity on Iran war goals after 11 days', 'democrats-say-white-house-offers-no-clarity-on-iran-war-goals-after-11-days', 'Party members decry \'disturbing\' lack of clarity following latest classified briefing on war justification and aims.', 'Party members decry \'disturbing\' lack of clarity following latest classified briefing on war justification and aims.', '', NULL, '1', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://www.aljazeera.com/news/2026/3/10/democrats-say-white-house-still-giving-no-clarity-on-iran-war-after-eleven-days?traffic_source=rss', '2026-03-11 04:44:59', '2026-03-11 11:54:50', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('417', 'Late Yamal penalty earns Barcelona draw at Newcastle in Champions League', 'late-yamal-penalty-earns-barcelona-draw-at-newcastle-in-champions-league', 'Barcelona needed Lamine Yamal\'s penalty with the last kick of the game to cancel out Harvey Barnes\'s opener.', 'Barcelona needed Lamine Yamal\'s penalty with the last kick of the game to cancel out Harvey Barnes\'s opener.', '', NULL, '1', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://www.aljazeera.com/sports/2026/3/10/late-yamal-penalty-earns-barcelona-draw-at-newcastle-in-champions-league?traffic_source=rss', '2026-03-11 04:44:59', '2026-03-11 12:08:16', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('418', 'Villagers on Príncipe, the ‘African Galapagos’, to be paid for protecting the ecosystem', 'villagers-on-príncipe-the-african-galapagos-to-be-paid-for-protecting-the-ecosystem', 'A billionaire is funding a sustainable development project on the west African island that makes the local population stewards of its futureAt the crumbling colonial farm buildings in Porto Real, agricultural worker Kimilson Lima, 43, has signed the agreement and he’s happy. “With this money we can have a proper floor in the house,” he said. “And an inside toilet.”Lima is part of a ground-breaking experiment on the West African island of Príncipe, where villagers who agree to follow an environmental protection code will reap a quarterly dividend. To date nearly 3,000 have joined the Faya Foundation’s project, more than 60% of the adult population. The first payment of €816 (£708) has just been delivered, a large amount of money on the island. “This will be truly transformative, both for nature and for the people,” said the president of the self-governing region, Felipe Nascimento. Continue reading...', 'A billionaire is funding a sustainable development project on the west African island that makes the local population stewards of its futureAt the ...', '', NULL, '1', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://www.theguardian.com/travel/2026/mar/10/principe-africa-villagers-to-be-paid-protect-ecosystem-african-galapagos', '2026-03-11 04:45:01', '2026-03-11 11:48:17', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('419', 'Descendants of Zimbabwe resistance heroes urge UK to locate looted skulls', 'descendants-of-zimbabwe-resistance-heroes-urge-uk-to-locate-looted-skulls', 'Relatives call on institutions to help them find remains of ancestors who led fight against British colonisers in 1890s• Which human remains are held in UK museums – and where?Descendants of freedom fighters executed and beheaded in southern Africa by colonial British forces have called on the Natural History Museum in London and the University of Cambridge to help them find their ancestors’ looted skulls.Zimbabwean descendants of the first chimurenga heroes, who led an uprising against British colonisers in the 1890s, have long believed the museum and university hold several of the skulls. Continue reading...', 'Relatives call on institutions to help them find remains of ancestors who led fight against British colonisers in 1890s• Which human remains are ...', '', NULL, '1', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://www.theguardian.com/world/2026/mar/07/descendants-of-zimbabwe-resistance-heroes-urge-uk-to-locate-looted-skulls', '2026-03-11 04:45:01', '2026-03-11 11:48:27', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('420', 'Weight-loss jab could be made for $3 a month, study finds', 'weight-loss-jab-could-be-made-for-3-a-month-study-finds', 'Cheap semaglutide, the drug in Ozempic and Wegovy, could help millions with diabetes and obesity in 160 countriesWeight-loss jabs such as Wegovy could be made for just $3 a month, according to new analysis, potentially making the treatment available to millions in poorer countries as patents expire.More than a billion people live with obesity worldwide, with rates rising fast in lower-income nations as they shift to westernised diets and more sedentary lifestyles. Continue reading...', 'Cheap semaglutide, the drug in Ozempic and Wegovy, could help millions with diabetes and obesity in 160 countriesWeight-loss jabs such as Wegovy co...', '', NULL, '1', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://www.theguardian.com/global-development/2026/mar/06/generic-drugs-weight-loss-semaglutide-ozempic-wegovy-diabetes-obesity-study', '2026-03-11 04:45:01', '2026-03-11 11:52:19', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('421', 'Trump pleads not guilty to 34 felony counts', 'trump-pleads-not-guilty-to-34-felony-counts', '', '', '', NULL, NULL, '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://edition.cnn.com/webview/politics/live-news/trump-indictment-stormy-daniels-news-04-03-23/index.html', '2026-03-11 04:46:07', '2026-03-11 10:50:57', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('422', 'Haberman reveals why Trump attacked judge and his family in speech', 'haberman-reveals-why-trump-attacked-judge-and-his-family-in-speech', 'CNN political contributor Maggie Haberman explains the reasoning behind Donald Trump\'s attacks on the judge and his family during a speech at his Mar-a-Lago resort after he was arraigned on felony charges.', 'CNN political contributor Maggie Haberman explains the reasoning behind Donald Trump\'s attacks on the judge and his family during a speech at his M...', '', NULL, NULL, '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://www.cnn.com/videos/politics/2023/04/05/maggie-haberman-donald-trump-speech-indictment-reaction-sot-cnntm-vpx.cnn', '2026-03-11 04:46:07', '2026-03-11 11:47:47', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('423', 'What to know about the Trump indictment on the eve of his court appearance', 'what-to-know-about-the-trump-indictment-on-the-eve-of-his-court-appearance', '', '', '', NULL, NULL, '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://www.cnn.com/collections/intl-trump-040223/', '2026-03-11 04:46:07', '2026-03-11 11:48:10', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('424', 'Russian consulate in Iran’s Isfahan damaged in strikes, spokeswoman says', 'russian-consulate-in-iran-s-isfahan-damaged-in-strikes-spokeswoman-says', 'Russia’s consulate in the Iranian city of Isfahan was damaged ​in shelling earlier this week, Russian ‌Foreign Ministry spokeswoman Maria Zakharova said on Tuesday. An attack on a diplomatic representation ​was a “blatant violation” of international ​conventions and all sides should observe ⁠the “inviolability of diplomatic sites”, she ​said. “On March 8, in the Iranian […]', 'Russia’s consulate in the Iranian city of Isfahan was damaged ​in shelling earlier this week, Russian ‌Foreign Ministry spokeswoman Maria Zak...', '', NULL, NULL, '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://arynews.tv/consulate-of-russia-damage-in-isfahan-raises-concerns/', '2026-03-11 04:46:12', '2026-03-11 10:50:38', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('425', 'Boeing signs $289 million Israel contract for 5,000 smart bombs', 'boeing-signs-289-million-israel-contract-for-5-000-smart-bombs', 'Boeing has signed a new $289 million contract ​with Israel to deliver ​as many as 5,000 new ⁠air-launched smart bombs, a ​source told Reuters on Tuesday. The ​new contract is not related to the ongoing US.-Israel air strikes on ​Iran, with deliveries not ​scheduled to start for 36 months, Bloomberg ‌News ⁠reported earlier, citing a […]', 'Boeing has signed a new $289 million contract ​with Israel to deliver ​as many as 5,000 new ⁠air-launched smart bombs, a ​source told Reute...', '', NULL, NULL, '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://arynews.tv/israel-to-receive-thousands-of-new-smart-bombs/', '2026-03-11 04:46:12', '2026-03-11 10:50:45', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('426', 'Government approves Met request to ban Al Quds Day march', 'government-approves-met-request-to-ban-al-quds-day-march', 'The government grants a request from the Metropolitan Police to ban a march due to take place in London on Sunday.', 'The government grants a request from the Metropolitan Police to ban a march due to take place in London on Sunday.', '', NULL, '1', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://www.bbc.com/news/articles/cm28gmlvedro?at_medium=RSS&at_campaign=rss', '2026-03-11 10:20:27', '2026-03-11 10:50:26', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('427', 'University tuition fees system is a \'mess\', says Clegg', 'university-tuition-fees-system-is-a-mess-says-clegg', 'The ex-deputy PM told the BBC he would \"take on the chin\" any criticism surrounding his involvement', 'The ex-deputy PM told the BBC he would \"take on the chin\" any criticism surrounding his involvement', '', NULL, '1', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://www.bbc.com/news/articles/c1e9yyg23dyo?at_medium=RSS&at_campaign=rss', '2026-03-11 10:20:27', '2026-03-11 10:50:30', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('428', 'Beirut building ablaze after apparent Israeli strike', 'beirut-building-ablaze-after-apparent-israeli-strike', 'A residential building in Beirut was seen with flames erupting from a hole in its side after an apparent Israeli strike.', 'A residential building in Beirut was seen with flames erupting from a hole in its side after an apparent Israeli strike.', '', NULL, '1', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://www.aljazeera.com/video/newsfeed/2026/3/11/beirut-building-ablaze-after-apparent-israeli-strike?traffic_source=rss', '2026-03-11 10:20:31', '2026-03-11 10:50:15', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('429', 'Qatar Minister of State says ‘regional countries are not an enemy of Iran’', 'qatar-minister-of-state-says-regional-countries-are-not-an-enemy-of-iran', 'Mohammed bin Abdulaziz al-Khulaifi also says Qatar and Oman cannot act as mediators while under attack.', 'Mohammed bin Abdulaziz al-Khulaifi also says Qatar and Oman cannot act as mediators while under attack.', '', NULL, '1', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://www.aljazeera.com/news/2026/3/11/qatars-foreign-minister-says-keeping-strait-of-hormuz-open-is-critical?traffic_source=rss', '2026-03-11 10:20:31', '2026-03-11 10:50:19', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('430', 'Bam Adebayo scores 83 points, passes Kobe Bryant for second-most in NBA', 'bam-adebayo-scores-83-points-passes-kobe-bryant-for-second-most-in-nba', 'Miami Heat player\'s historic night is second behind the famous Wilt Chamberlain, who scored 100 points back in 1962.', 'Miami Heat player\'s historic night is second behind the famous Wilt Chamberlain, who scored 100 points back in 1962.', '', NULL, '1', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://www.aljazeera.com/sports/2026/3/11/bam-adebayo-scores-83-points-passes-kobe-bryant-for-second-most-in-nba?traffic_source=rss', '2026-03-11 10:20:31', '2026-03-11 10:50:22', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('431', 'Villagers on Príncipe, the ‘African Galapagos’, to be paid for protecting the ecosystem', 'villagers-on-príncipe-the-african-galapagos-to-be-paid-for-protecting-the-ecosystem-1773206447', 'A billionaire is funding a sustainable development project on the west African island that makes the local population stewards of its futureAt the crumbling colonial farm buildings in Porto Real, agricultural worker Kimilson Lima, 43, has signed the agreement and he’s happy. “With this money we can have a proper floor in the house,” he said. “And an inside toilet.”Lima is part of a ground-breaking experiment on the West African island of Príncipe, where villagers who agree to follow an environmental protection code will reap a quarterly dividend. To date nearly 3,000 have joined the Faya Foundation’s project, more than 60% of the adult population. The first payment of €816 (£708) has just been delivered, a large amount of money on the island. “This will be truly transformative, both for nature and for the people,” said the president of the self-governing region, Felipe Nascimento. Continue reading...', 'A billionaire is funding a sustainable development project on the west African island that makes the local population stewards of its futureAt the ...', '', NULL, '1', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://www.theguardian.com/travel/2026/mar/10/principe-africa-villagers-to-be-paid-protect-ecosystem-african-galapagos', '2026-03-11 10:20:47', '2026-03-11 10:50:12', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('432', 'White House denies US military escorted oil tanker in Strait of Hormuz', 'white-house-denies-us-military-escorted-oil-tanker-in-strait-of-hormuz', 'WASHINGTON: The White House has said that the United States (US) Navy has not escorted any oil tankers through the Strait of Hormuz so far. During a press briefing, a spokesperson for the White House Karoline Leavitt was asked why Chris Wright had deleted a social media post related to the issue. The spokesperson said […]', 'WASHINGTON: The White House has said that the United States (US) Navy has not escorted any oil tankers through the Strait of Hormuz so far. During ...', '', NULL, NULL, '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://arynews.tv/white-house-denies-us-military-escorted-oil-tanker-in-hormuz/', '2026-03-11 10:21:01', '2026-03-11 10:49:58', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('433', 'Iran war deals double blow to Indian airlines already hit by Pakistan airspace ban', 'iran-war-deals-double-blow-to-indian-airlines-already-hit-by-pakistan-airspace-ban', 'Airspace restrictions in the Middle East amid the Iran war have dealt another blow to Indian airlines, which count the region as ​a crucial corridor for flights to Europe and the US since Pakistan banned Indian carriers from its airspace last year. As war in the ‌Middle East forces flight rescheduling and re-routing, Indian airlines […]', 'Airspace restrictions in the Middle East amid the Iran war have dealt another blow to Indian airlines, which count the region as ​a crucial corri...', '', NULL, NULL, '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://arynews.tv/iran-war-deals-double-blow-to-indian-airlines-facing-pakistan-ban/', '2026-03-11 10:21:01', '2026-03-11 10:50:04', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('434', 'Israel says Iran hacked security cameras', 'israel-says-iran-hacked-security-cameras', 'Israel’s cybersecurity directorate said it had identified “dozens of Iranian breaches into security cameras for espionage purposes” since the start of the war in the Middle East, urging the public to be vigilant. “The directorate is working to alert hundreds of camera owners and calls on the public to change their passwords and update their […]', 'Israel’s cybersecurity directorate said it had identified “dozens of Iranian breaches into security cameras for espionage purposes” since the...', '', NULL, NULL, '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://arynews.tv/israel-says-iran-hacked-security-cameras/', '2026-03-11 10:21:01', '2026-03-11 10:50:08', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('435', 'LeBron, NBA social media react to Bam Adebayo’s historic 83-point game', 'lebron-nba-social-media-react-to-bam-adebayo-s-historic-83-point-game', 'Star NBA players like LeBron James take to social media to praise the Miami player\'s incredible scoring achievement.', 'Star NBA players like LeBron James take to social media to praise the Miami player\'s incredible scoring achievement.', '', NULL, '1', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://www.aljazeera.com/sports/2026/3/11/lebron-nba-social-media-react-to-bam-adebayos-historic-83-point-game?traffic_source=rss', '2026-03-11 11:37:29', '2026-03-11 11:47:26', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('436', '‘The other side’ of IITs: Student suicides haunt India’s top tech schools', 'the-other-side-of-iits-student-suicides-haunt-india-s-top-tech-schools', 'Nearly 160 deaths recorded across the premier engineering colleges in past two decades - 69 of them in last five years.', 'Nearly 160 deaths recorded across the premier engineering colleges in past two decades - 69 of them in last five years.', '', NULL, '1', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://www.aljazeera.com/news/2026/3/11/the-other-side-of-iits-student-suicides-haunt-indias-top-tech-schools?traffic_source=rss', '2026-03-11 11:37:29', '2026-03-11 11:47:31', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('437', 'The ‘orphan pearl’: Inside Kharg, the beating heart of Iran’s oil empire', 'the-orphan-pearl-inside-kharg-the-beating-heart-of-iran-s-oil-empire', 'On Iran\'s \'Forbidden Island\' of Kharg, ancient ruins sit beside the nerve centre of the nation\'s oil empire.', 'On Iran\'s \'Forbidden Island\' of Kharg, ancient ruins sit beside the nerve centre of the nation\'s oil empire.', '', NULL, '1', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://www.aljazeera.com/features/2026/3/11/the-orphan-pearl-inside-kharg-the-beating-heart-of-irans-oil-empire?traffic_source=rss', '2026-03-11 11:37:29', '2026-03-11 11:47:36', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('438', 'Prison sentences for pair who attacked gay men hailed as sign of hope for Kenya’s LGBTQ+ community', 'prison-sentences-for-pair-who-attacked-gay-men-hailed-as-sign-of-hope-for-kenya-s-lgbtq-community', 'The perpetrators were jailed for 15 years for robbery with violence in the east African country, where homophobic attacks are increasingThe sentencing of two people who attacked and robbed two gay men in Kenya has been hailed by LGBTQ+ rights advocates as a breakthrough and a sign of hope for the country’s queer community. “Abel Meli & Another” were sentenced to 15 years in prison for robbery with violence on 3 March at Milimani law courts in Nairobi.The ruling is a rare example of justice being served for the queer community in Kenya. Njeri Gateru, the executive director of the National Gay and Lesbian Human Rights Commission, an independent human rights institution working towards equality for sexual and gender minorities in Kenya, said: “A lot is going against [the queer community] with the existence of the criminal laws and prevailing homophobic attitudes, but some of us still trust that we can find justice, so this case encourages us.” Continue reading...', 'The perpetrators were jailed for 15 years for robbery with violence in the east African country, where homophobic attacks are increasingThe sentenc...', '', NULL, '1', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://www.theguardian.com/global-development/2026/mar/11/prison-sentences-attacked-gay-men-hope-kenya-lgbtq-community', '2026-03-11 11:37:33', '2026-03-11 11:47:02', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('439', 'Villagers on Príncipe, the ‘African Galapagos’, to be paid for protecting the ecosystem', 'villagers-on-príncipe-the-african-galapagos-to-be-paid-for-protecting-the-ecosystem-1773211053', 'A billionaire is funding a sustainable development project on the west African island that makes the local population stewards of its futureAt the crumbling colonial farm buildings in Porto Real, agricultural worker Kimilson Lima, 43, has signed the agreement and he’s happy. “With this money we can have a proper floor in the house,” he said. “And an inside toilet.”Lima is part of a ground-breaking experiment on the West African island of Príncipe, where villagers who agree to follow an environmental protection code will reap a quarterly dividend. To date nearly 3,000 have joined the Faya Foundation’s project, more than 60% of the adult population. The first payment of €816 (£708) has just been delivered, a large amount of money on the island. “This will be truly transformative, both for nature and for the people,” said the president of the self-governing region, Felipe Nascimento. Continue reading...', 'A billionaire is funding a sustainable development project on the west African island that makes the local population stewards of its futureAt the ...', '', NULL, '1', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://www.theguardian.com/travel/2026/mar/10/principe-africa-villagers-to-be-paid-protect-ecosystem-african-galapagos', '2026-03-11 11:37:33', '2026-03-11 11:47:13', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('440', 'Viral video: Girl stops closing lift doors to rescue children', 'viral-video-girl-stops-closing-lift-doors-to-rescue-children', 'In a dramatic moment captured on CCTV, a young girl displayed remarkable presence of mind by using her body to stop a lift door from closing, preventing two children from being trapped inside. The video shows several children standing inside an elevator and holding the doors open, apparently waiting for someone to arrive. At one […]', 'In a dramatic moment captured on CCTV, a young girl displayed remarkable presence of mind by using her body to stop a lift door from closing, preve...', '', NULL, NULL, '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://arynews.tv/viral-video-girl-stops-closing-lift-doors-to-rescue-children/', '2026-03-11 11:37:38', '2026-03-11 11:46:09', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('441', 'Britney Spears’s mugshot not to be release in public', 'britney-spears-s-mugshot-not-to-be-release-in-public', 'Britney Spears’ mugshot from her DUI will not be released in public. According to the Ventura County Sheriff’s Office, the photo doesn’t meet the criteria for public release. A department official told TMZ on Tuesday that the office only releases mugshots for violent crimes or when a suspect is considered a public threat, neither of which applies […]', 'Britney Spears’ mugshot from her DUI will not be released in public. According to the Ventura County Sheriff’s Office, the photo doesn’t meet...', '', NULL, NULL, '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://arynews.tv/britney-spearss-mugshot-not-to-be-release-in-public/', '2026-03-11 11:37:38', '2026-03-11 11:46:19', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('442', 'Ample LPG supplies reach Pakistan as four vessels dock at Port Qasim', 'ample-lpg-supplies-reach-pakistan-as-four-vessels-dock-at-port-qasim', 'KARACHI: Pakistan currently has adequate reserves of liquefied petroleum gas (LPG), with multiple shipments arriving at Port Qasim in Karachi, ensuring stability in the country’s supply chain. Officials confirmed that four LPG vessels have anchored at the port in recent days, significantly boosting available stocks. One ship carrying approximately 11,000 metric tonnes of LPG has […]', 'KARACHI: Pakistan currently has adequate reserves of liquefied petroleum gas (LPG), with multiple shipments arriving at Port Qasim in Karachi, ensu...', '', NULL, NULL, '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://arynews.tv/lpg-supplies-reach-pakistan-as-four-vessels-dock-at-port-qasim/', '2026-03-11 11:37:38', '2026-03-11 11:46:38', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('443', 'University tuition fees system is a \'mess\', says Sir Nick Clegg', 'university-tuition-fees-system-is-a-mess-says-sir-nick-clegg', 'The ex-deputy PM told the BBC he would \"take on the chin\" any criticism surrounding his involvement', 'The ex-deputy PM told the BBC he would \"take on the chin\" any criticism surrounding his involvement', '', NULL, '1', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://www.bbc.com/news/articles/c1e9yyg23dyo?at_medium=RSS&at_campaign=rss', '2026-03-11 11:37:42', '2026-03-11 12:06:06', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('444', '4 day week, fewer car trips in Philippines as Iran fallout bites', '4-day-week-fewer-car-trips-in-philippines-as-iran-fallout-bites', 'Rising fuel prices are pushing more people to use public transport and prompting a four-day work week in Manila.', 'Rising fuel prices are pushing more people to use public transport and prompting a four-day work week in Manila.', '', NULL, '1', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://www.aljazeera.com/video/newsfeed/2026/3/11/4-day-week-fewer-car-trips-in-philippines-as-iran-fallout-bites?traffic_source=rss', '2026-03-11 16:48:14', '2026-03-11 17:01:31', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('445', 'UN aid worker killed in DR Congo’s rebel-held Goma', 'un-aid-worker-killed-in-dr-congo-s-rebel-held-goma', 'France\'s president said a French aid worker with UNICEF was killed, after M23 rebels said \'combat drone\' hit the city.', 'France\'s president said a French aid worker with UNICEF was killed, after M23 rebels said \'combat drone\' hit the city.', '', NULL, '1', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://www.aljazeera.com/news/2026/3/11/un-aid-worker-killed-in-dr-congos-rebel-held-goma?traffic_source=rss', '2026-03-11 16:48:14', '2026-03-11 17:01:25', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('446', 'Iranian female footballer changes mind on Australian asylum offer', 'iranian-female-footballer-changes-mind-on-australian-asylum-offer', 'One member of the Iranian women’s football team offered asylum by Australia has decided to return home to Iran.', 'One member of the Iranian women’s football team offered asylum by Australia has decided to return home to Iran.', '', NULL, '1', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://www.aljazeera.com/video/newsfeed/2026/3/11/iranian-female-footballer-changes-mind-on-australian-asylum-offer?traffic_source=rss', '2026-03-11 16:48:14', '2026-03-11 17:01:38', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('447', 'French aid worker among three killed in drone attack in eastern DRC, M23 rebels say', 'french-aid-worker-among-three-killed-in-drone-attack-in-eastern-drc-m23-rebels-say', 'Attack on residential part of M23-controlled city of Goma blamed by rebel group on governmentAt least three people, including a French humanitarian worker for the UN children’s agency, were killed in a drone attack in Goma early on Wednesday morning, a spokesperson for the M23 rebel group has said.The attack happened at about 4am in a residential neighbourhood in the city, which has been under M23 occupation since January 2025. Continue reading...', 'Attack on residential part of M23-controlled city of Goma blamed by rebel group on governmentAt least three people, including a French humanitarian...', '', NULL, '1', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://www.theguardian.com/world/2026/mar/11/drone-attack-goma-eastern-democratic-republic-of-the-congo-m23-rebels', '2026-03-11 16:48:19', '2026-03-11 17:01:12', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('448', 'Experts fear ‘unethical’ vaccine trial in Africa is ‘prototype’ for US studies under RFK Jr', 'experts-fear-unethical-vaccine-trial-in-africa-is-prototype-for-us-studies-under-rfk-jr', 'Danish researchers whose work on effects of vaccines has been called into question are at center of US vaccine policyNew details are leading experts to fear that an “unethical” vaccine trial in Guinea-Bissau is the “prototype” for studies under Robert F Kennedy Jr, secretary of the US department of health and human services (HHS) and longtime vaccine critic.At the center of US vaccine policy is an unlikely set of Danish researchers whose work on the health effects of vaccines has been called into question. The study in Guinea-Bissau would have looked at the overall health effects of giving hepatitis B vaccines by only vaccinating half of the newborns in the study at birth despite an 18% prevalence rate in adults of the illness, which can lead to serious and sometimes fatal health consequences. Continue reading...', 'Danish researchers whose work on effects of vaccines has been called into question are at center of US vaccine policyNew details are leading expert...', '', NULL, '1', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://www.theguardian.com/us-news/2026/mar/11/rfk-vaccine-trials-guinea-bissau', '2026-03-11 16:48:19', '2026-03-11 17:01:19', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('449', 'Japan to release part of oil reserves ahead of IEA-led decision', 'japan-to-release-part-of-oil-reserves-ahead-of-iea-led-decision', 'TOKYO, March 11:  Japan plans to release 15 days\' worth of private-sector ​oil reserves and one month\'s worth of state ‌oil reserves', 'TOKYO, March 11:  Japan plans to release 15 days\' worth of private-sector ​oil reserves and one month\'s worth of state ‌oil reserves', '', NULL, NULL, '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://arynews.tv/japan-to-release-part-of-oil-reserves-ahead-of-iea-led-decision/', '2026-03-11 16:48:35', '2026-03-11 17:00:53', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('450', 'Epstein files held by FBI compromised by foreign hacker', 'epstein-files-held-by-fbi-compromised-by-foreign-hacker', 'A foreign hacker compromised files relating to the FBI’s investigation of Jeffrey Epstein during a break-in at the bureau’s NY Field Office', 'A foreign hacker compromised files relating to the FBI’s investigation of Jeffrey Epstein during a break-in at the bureau’s NY Field Office', '', NULL, NULL, '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://arynews.tv/epstein-files-held-by-fbi-compromised-by-foreign-hacker/', '2026-03-11 16:48:35', '2026-03-11 17:01:00', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('451', 'China warns US AI military use can create ‘Terminator’ like dystopian world', 'china-warns-us-ai-military-use-can-create-terminator-like-dystopian-world', 'BEIJING: China warned the United States on Wednesday that the excessive use of artificial intelligence in its military could plunge the world into a “Terminator”-like dystopian future. US President Donald Trump’s administration has sought the unconditional use of AI startups in the military. The Pentagon has confirmed Elon Musk’s Grok system is cleared for use […]', 'BEIJING: China warned the United States on Wednesday that the excessive use of artificial intelligence in its military could plunge the world into ...', '', NULL, NULL, '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://arynews.tv/china-warns-us-ai-military-use-create-terminator-like-world/', '2026-03-11 16:48:35', '2026-03-11 17:01:06', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('452', 'Mortgage rates rise and deals pulled over Iran war turmoil', 'mortgage-rates-rise-and-deals-pulled-over-iran-war-turmoil', 'Average mortgage rates hit highest since last August in the biggest upheaval since the mini-Budget.', 'Average mortgage rates hit highest since last August in the biggest upheaval since the mini-Budget.', '', NULL, '1', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://www.bbc.com/news/articles/c24dm9vgrz7o?at_medium=RSS&at_campaign=rss', '2026-03-11 16:49:54', '2026-03-11 17:00:34', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('453', 'March organised by Iran-linked group banned after police request', 'march-organised-by-iran-linked-group-banned-after-police-request', 'Organisers say it is a peaceful pro-Palestinian event but it has been criticised for representing the Iranian regime.', 'Organisers say it is a peaceful pro-Palestinian event but it has been criticised for representing the Iranian regime.', '', NULL, '1', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://www.bbc.com/news/articles/cm28gmlvedro?at_medium=RSS&at_campaign=rss', '2026-03-11 16:49:54', '2026-03-11 17:00:39', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('454', 'Police hunting for suspect after teen girl stabbed at high school', 'police-hunting-for-suspect-after-teen-girl-stabbed-at-high-school', 'The BBC is told students have been placed under lockdown conditions in the school buildings.', 'The BBC is told students have been placed under lockdown conditions in the school buildings.', '', NULL, '1', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://www.bbc.com/news/articles/c15x1wnd7yqo?at_medium=RSS&at_campaign=rss', '2026-03-11 16:49:54', '2026-03-11 17:00:47', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('455', 'Boy, 16, arrested after girl stabbed at high school', 'boy-16-arrested-after-girl-stabbed-at-high-school', 'The BBC is told students have been placed under lockdown conditions in the school buildings.', 'The BBC is told students have been placed under lockdown conditions in the school buildings.', '', NULL, '1', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://www.bbc.com/news/articles/c15x1wnd7yqo?at_medium=RSS&at_campaign=rss', '2026-03-11 17:20:28', '2026-03-12 17:05:16', '0.00', 'neutral', 'recent', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('456', 'The Aldi-style disruptors who could be about to shake up the vets market', 'the-aldi-style-disruptors-who-could-be-about-to-shake-up-the-vets-market', 'As pet owners complain of rising prices, independent practices want to take on the big chains.', 'As pet owners complain of rising prices, independent practices want to take on the big chains.', '', NULL, '1', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://www.bbc.com/news/articles/cj6dw3r09x0o?at_medium=RSS&at_campaign=rss', '2026-03-11 17:20:28', '2026-03-12 17:05:16', '0.00', 'neutral', 'recent', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('457', 'First Iraq footballers granted visas by Mexico for FIFA World Cup qualifier', 'first-iraq-footballers-granted-visas-by-mexico-for-fifa-world-cup-qualifier', 'Mexico issues visas to some Iraq footballers so they can play in FIFA World Cup 2026 qualifier in Monterrey.', 'Mexico issues visas to some Iraq footballers so they can play in FIFA World Cup 2026 qualifier in Monterrey.', '', NULL, '1', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://www.aljazeera.com/sports/2026/3/11/first-iraq-footballers-granted-visas-by-mexico-for-fifa-world-cup-qualifier?traffic_source=rss', '2026-03-11 17:48:56', '2026-03-12 17:05:16', '0.00', 'neutral', 'recent', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('458', 'UK home secretary bans al-Quds march in London after Met Police request', 'uk-home-secretary-bans-al-quds-march-in-london-after-met-police-request', 'The police requested the ban citing public disorder risks, while the organisers decide to hold a static protest instead.', 'The police requested the ban citing public disorder risks, while the organisers decide to hold a static protest instead.', '', NULL, '1', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://www.aljazeera.com/news/2026/3/11/uk-home-secretary-bans-al-quds-march-in-london-after-met-police-request?traffic_source=rss', '2026-03-11 17:48:56', '2026-03-12 17:05:16', '0.00', 'neutral', 'recent', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('459', '‘Siren economy’: Why tactical wins fail to bring Israel strategic safety', 'siren-economy-why-tactical-wins-fail-to-bring-israel-strategic-safety', 'Analyst refers to \'security achievement gap\' of tactical assassinations that fail to bring Israel security.', 'Analyst refers to \'security achievement gap\' of tactical assassinations that fail to bring Israel security.', '', NULL, '1', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://www.aljazeera.com/features/2026/3/11/siren-economy-why-tactical-wins-fail-to-bring-israel-strategic-safety?traffic_source=rss', '2026-03-11 17:48:56', '2026-03-12 17:05:16', '0.00', 'neutral', 'recent', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('460', 'Iran cannot participate in the FIFA World Cup, sports minister says', 'iran-cannot-participate-in-the-fifa-world-cup-sports-minister-says', 'Iran says \'under no circumstances\' will it participate following the US-Israeli attacks that killed its supreme leader.', 'Iran says \'under no circumstances\' will it participate following the US-Israeli attacks that killed its supreme leader.', '', NULL, '1', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://www.aljazeera.com/sports/2026/3/11/iran-cannot-participate-in-the-fifa-world-cup-sports-minister-says?traffic_source=rss', '2026-03-11 18:54:02', '2026-03-12 17:05:16', '0.00', 'neutral', 'recent', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('461', 'Funeral held for top Iranian commanders killed in US-Israeli strikes', 'funeral-held-for-top-iranian-commanders-killed-in-us-israeli-strikes', 'Thousands of people gathered in Tehran to mourn top Iranian military officials killed by US-Israeli strikes.', 'Thousands of people gathered in Tehran to mourn top Iranian military officials killed by US-Israeli strikes.', '', NULL, '1', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://www.aljazeera.com/video/newsfeed/2026/3/11/funeral-held-for-top-iranian-commanders-killed-in-us-israeli-strikes?traffic_source=rss', '2026-03-11 18:54:02', '2026-03-12 17:05:16', '0.00', 'neutral', 'recent', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('462', 'Attacks from all sides: Why Iraq was dragged into US-Israel war on Iran', 'attacks-from-all-sides-why-iraq-was-dragged-into-us-israel-war-on-iran', 'Iraq has seen attacks from both sides of the conflict: Iran and its proxies on the one side and the US on the other.', 'Iraq has seen attacks from both sides of the conflict: Iran and its proxies on the one side and the US on the other.', '', NULL, '1', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://www.aljazeera.com/news/2026/3/11/attacks-from-all-sides-why-iraq-was-dragged-into-us-israel-war-on-iran?traffic_source=rss', '2026-03-11 18:54:02', '2026-03-12 17:05:16', '0.00', 'neutral', 'recent', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('463', 'At least 65 Nigerian soldiers killed in jihadist raids in country’s north-east', 'at-least-65-nigerian-soldiers-killed-in-jihadist-raids-in-country-s-north-east', 'Gunmen from Islamic State West Africa Province overran four military bases and abducted 300 civilians, say reportsAt least 65 Nigerian soldiers have been killed in jihadist raids across the country’s north-east in the last two weeks, as the west African state battles to contain one of the world’s deadliest terror groups.On 5 and 6 March, gunmen from Islamic State West Africa Province (Iswap) overran four military bases in Borno state, the epicentre of the insurgency. Nigerian daily the Punch reported that about 40 soldiers were killed in total in these attacks. Continue reading...', 'Gunmen from Islamic State West Africa Province overran four military bases and abducted 300 civilians, say reportsAt least 65 Nigerian soldiers hav...', '', NULL, '3', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://www.theguardian.com/world/2026/mar/11/nigerian-soldiers-killed-jihadist-raids-north-east', '2026-03-11 18:54:30', '2026-03-12 17:05:16', '0.00', 'neutral', 'recent', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('464', 'SUPARCO predicts likely date for Eid ul Fitr 2026 in Pakistan', 'suparco-predicts-likely-date-for-eid-ul-fitr-2026-in-pakistan', 'ISLAMABAD: The Pakistan Space and Upper Atmosphere Research Commission (SUPARCO) has indicated that Eid ul Fitr 2026 in Pakistan is likely to fall on March 21, based on astronomical calculations regarding the Shawwal moon. According to SUPARCO, the Shawwal moon will be born on March 19 at 6:23am. By sunset on the same day, the […]', 'ISLAMABAD: The Pakistan Space and Upper Atmosphere Research Commission (SUPARCO) has indicated that Eid ul Fitr 2026 in Pakistan is likely to fall ...', '', NULL, '94', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://arynews.tv/eid-ul-fitr-2026-date-in-pakistan/', '2026-03-11 18:54:47', '2026-03-12 17:05:16', '0.00', 'neutral', 'recent', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('465', 'Security concerns force closure of key roads in Karachi', 'security-concerns-force-closure-of-key-roads-in-karachi', 'KARACHI: Several key roads in Karachi were closed again on Wednesday due to security concerns, causing traffic diversions across major routes in the city, ARY News reported. According to traffic authorities, both tracks of MT Khan Road have been closed for traffic, while both sides of Mai Kolachi Road have also been shut, disrupting the […]', 'KARACHI: Several key roads in Karachi were closed again on Wednesday due to security concerns, causing traffic diversions across major routes in th...', '', NULL, '94', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://arynews.tv/karachi-traffic-updates-on-major-road-closures/', '2026-03-11 18:54:47', '2026-03-12 17:05:16', '0.00', 'neutral', 'recent', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('466', 'NADRA cancels 4.2 million CNICs after record verification', 'nadra-cancels-4-2-million-cnics-after-record-verification', 'ISLAMABAD: The National Database and Registration Authority (NADRA) has completed a major verification exercise aimed at improving the accuracy and integrity of Pakistan’s national identity system by reconciling civil registration records with the national citizen database. According to NADRA, the reconciliation process between the Civil Registration System and the National Citizen Database has been completed, […]', 'ISLAMABAD: The National Database and Registration Authority (NADRA) has completed a major verification exercise aimed at improving the accuracy and...', '', NULL, '94', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://arynews.tv/nadra-cancels-4-2-million-cnics/', '2026-03-11 18:54:47', '2026-03-12 17:05:16', '0.00', 'neutral', 'recent', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('467', 'Boy, 16, arrested after girl is stabbed at school', 'boy-16-arrested-after-girl-is-stabbed-at-school', 'Sources tell the BBC that pupils had to hide under their desks as the incident unfolded.', 'Sources tell the BBC that pupils had to hide under their desks as the incident unfolded.', '', NULL, '3', '1', 'published', 'manual', '0', '2', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://www.bbc.com/news/articles/c15x1wnd7yqo?at_medium=RSS&at_campaign=rss', '2026-03-11 18:57:12', '2026-03-12 17:05:16', '0.00', 'neutral', 'recent', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('468', 'Inside the Russian explosives plot that sent incendiary parcels to the UK', 'inside-the-russian-explosives-plot-that-sent-incendiary-parcels-to-the-uk', 'Aleksandr Suranovas, charged with carrying out an act of terrorism for Russia, speaks to the BBC.', 'Aleksandr Suranovas, charged with carrying out an act of terrorism for Russia, speaks to the BBC.', '', NULL, '3', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://www.bbc.com/news/articles/cpd83zwqlvno?at_medium=RSS&at_campaign=rss', '2026-03-11 18:57:12', '2026-03-12 17:05:16', '0.00', 'neutral', 'recent', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('469', 'IEA proposes release of 400m barrels of oil from strategic reserves', 'iea-proposes-release-of-400m-barrels-of-oil-from-strategic-reserves', 'This is a breaking news story.', 'This is a breaking news story.', '', NULL, '3', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://www.aljazeera.com/news/2026/3/11/iea-proposes-release-of-400m-barrels-of-oil-from-strategic-reserves?traffic_source=rss', '2026-03-11 19:55:15', '2026-03-12 17:05:16', '0.00', 'neutral', 'recent', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('470', '‘Nothing changes’: Four decades in power, Congo’s Nguesso seeks a new term', 'nothing-changes-four-decades-in-power-congo-s-nguesso-seeks-a-new-term', 'Is Brazzaville\'s stability a result of gradual democratic consolidation or carefully organised political continuity?', 'Is Brazzaville\'s stability a result of gradual democratic consolidation or carefully organised political continuity?', '', NULL, '3', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://www.aljazeera.com/features/2026/3/11/nothing-changes-four-decades-in-power-congos-nguesso-seeks-a-new-term?traffic_source=rss', '2026-03-11 19:55:15', '2026-03-12 17:05:16', '0.00', 'neutral', 'recent', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('471', 'What happened in Ukraine missile attack in Russia’s Bryansk?', 'what-happened-in-ukraine-missile-attack-in-russia-s-bryansk', 'A Ukrainian missile attack on Russia’s Bryansk killed 6 civilians and injured 37 others, according to local officials.', 'A Ukrainian missile attack on Russia’s Bryansk killed 6 civilians and injured 37 others, according to local officials.', '', NULL, '3', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://www.aljazeera.com/video/newsfeed/2026/3/11/what-happened-in-ukraine-missile-attack-in-russias-bryansk?traffic_source=rss', '2026-03-11 19:55:15', '2026-03-12 17:05:16', '0.00', 'neutral', 'recent', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('472', 'Rana takes five wickets as Bangladesh crush Pakistan in ODI opener', 'rana-takes-five-wickets-as-bangladesh-crush-pakistan-in-odi-opener', 'Bangladesh pace sensation Nahid Rana claimed his first five-wicket haul in one-day internationals to lead his team to a crushing eight-wicket win over Pakistan in their opening match on Wednesday. Rana returned figures of 5-24 to skittle out Pakistan for 114, a total Bangladesh overhauled in 15.1 overs of the 50-over contest in Mirpur to […]', 'Bangladesh pace sensation Nahid Rana claimed his first five-wicket haul in one-day internationals to lead his team to a crushing eight-wicket win o...', '', NULL, '94', '1', 'published', 'manual', '0', '1', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://arynews.tv/rana-takes-five-wickets-as-bangladesh-crush-pakistan-in-odi-opener/', '2026-03-11 19:56:48', '2026-03-12 17:05:16', '0.00', 'neutral', 'recent', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('473', 'Which country sent Pakistan the most remittances in February 2026?', 'which-country-sent-pakistan-the-most-remittances-in-february-2026', 'KARACHI: Pakistan received the highest amount of workers’ remittances from the United Arab Emirates (UAE) in February 2026, according to data released by the State Bank of Pakistan (SBP), ARY News reported. The central bank said overseas Pakistanis sent $696.24 million from the UAE during February, making it the largest source of remittances for the […]', 'KARACHI: Pakistan received the highest amount of workers’ remittances from the United Arab Emirates (UAE) in February 2026, according to data rel...', '', NULL, '94', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://arynews.tv/pakistan-remittances-in-february-2026/', '2026-03-11 19:56:48', '2026-03-12 17:05:16', '0.00', 'neutral', 'recent', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('474', 'US military confirms use of ‘advanced AI tools’ in war against Iran', 'us-military-confirms-use-of-advanced-ai-tools-in-war-against-iran', 'Admiral Brad Cooper says artificial intelligence is helping process data, but humans are making final decisions.', 'Admiral Brad Cooper says artificial intelligence is helping process data, but humans are making final decisions.', '', NULL, '3', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://www.aljazeera.com/news/2026/3/11/us-military-confirms-use-of-advanced-ai-tools-in-war-against-iran?traffic_source=rss', '2026-03-11 20:58:05', '2026-03-12 17:05:16', '0.00', 'neutral', 'recent', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('475', 'Oil facilities in Oman’s Salalah port ablaze after drone strikes', 'oil-facilities-in-oman-s-salalah-port-ablaze-after-drone-strikes', 'Drones struck oil storage facilities in Oman’s ⁠Salalah port, as local authorities say they\'re responding to a big fire.', 'Drones struck oil storage facilities in Oman’s ⁠Salalah port, as local authorities say they\'re responding to a big fire.', '', NULL, '3', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://www.aljazeera.com/video/newsfeed/2026/3/11/oil-facilities-in-omans-salalah-port-ablaze-after-drone-strikes?traffic_source=rss', '2026-03-11 20:58:05', '2026-03-12 17:05:16', '0.00', 'neutral', 'recent', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('476', 'Could the US deploy troops to Iran, and how could that play out?', 'could-the-us-deploy-troops-to-iran-and-how-could-that-play-out', 'Experts say Iran’s vast, mountainous terrain would make invasion difficult but a small, precise mission is possible.', 'Experts say Iran’s vast, mountainous terrain would make invasion difficult but a small, precise mission is possible.', '', NULL, '3', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://www.aljazeera.com/news/2026/3/11/could-the-us-deploy-troops-to-iran-and-how-could-that-play-out?traffic_source=rss', '2026-03-11 20:58:05', '2026-03-12 17:05:16', '0.00', 'neutral', 'recent', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('477', 'French aid worker among three killed in dronestrike in east DRC, M23 rebels say', 'french-aid-worker-among-three-killed-in-dronestrike-in-east-drc-m23-rebels-say', 'Rebel group blames government for attack on residential area of M23-controlled city of GomaThree people including a French UN aid worker have been killed in a drone attack in Goma, a spokesperson for the M23 rebel group has said.The attack took place at about 4am on Wednesday in the upmarket residential neighbourhood of Himbi in the city, which has been under M23 occupation since January 2025. Continue reading...', 'Rebel group blames government for attack on residential area of M23-controlled city of GomaThree people including a French UN aid worker have been ...', '', NULL, '3', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://www.theguardian.com/world/2026/mar/11/drone-attack-goma-eastern-democratic-republic-of-the-congo-m23-rebels', '2026-03-11 20:59:23', '2026-03-12 17:05:16', '0.00', 'neutral', 'recent', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('478', 'Employees stranded outside will not be considered absent in Kuwait', 'employees-stranded-outside-will-not-be-considered-absent-in-kuwait', 'KUWAIT CITY: The Civil Service Commission (CSC) announced that employees stranded outside Kuwait due to the closure of airspace and the exceptional circumstances in the region will be exempt from the regulations concerning absence from work, reports Al-Seyassah daily. In an official statement, CSC explained that this period will be considered actual working time until […]', 'KUWAIT CITY: The Civil Service Commission (CSC) announced that employees stranded outside Kuwait due to the closure of airspace and the exceptional...', '', NULL, '94', '1', 'published', 'manual', '0', '2', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://arynews.tv/employees-stranded-outside-will-not-be-considered-absent-in-kuwait/', '2026-03-11 21:00:00', '2026-03-12 17:05:16', '0.00', 'neutral', 'recent', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('479', 'Iran threatens to target banks linked to US, Israel', 'iran-threatens-to-target-banks-linked-to-us-israel', 'DUBAI: Iran will target economic and banking ​interests linked to the U.S. ‌and Israel in the region, after an attack on an Iranian bank, a ​spokesperson for Tehran’s Khatam al-Anbiya military command headquarters said ​on Wednesday. An administrative building linked to ⁠Bank Sepah, one of the ​country’s largest public banks and ​with historical links to […]', 'DUBAI: Iran will target economic and banking ​interests linked to the U.S. ‌and Israel in the region, after an attack on an Iranian bank, a ​...', '', NULL, '94', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://arynews.tv/iran-to-target-israel-and-us-linked-banks-in-region/', '2026-03-11 21:00:00', '2026-03-12 17:05:16', '0.00', 'neutral', 'recent', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('480', 'Iran tells world oil prices could reach $200 a barrel', 'iran-tells-world-oil-prices-could-reach-200-a-barrel', 'DUBAI: Iran’s military command said on Wednesday the world should be prepared for oil to hit $200 a barrel, as three more ships came under attack in the blockaded Gulf. Iran fired at Israel and targets across the Middle East on Wednesday, demonstrating it can still fight back ​and disrupt energy supplies despite what the […]', 'DUBAI: Iran’s military command said on Wednesday the world should be prepared for oil to hit $200 a barrel, as three more ships came under attack...', '', NULL, '94', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://arynews.tv/iran-says-oil-prices-could-reach-200-dollar-per-barrel/', '2026-03-11 21:00:00', '2026-03-12 17:05:16', '0.00', 'neutral', 'recent', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('481', 'Prison inmate charged with Huntley murder appears in court', 'prison-inmate-charged-with-huntley-murder-appears-in-court', 'Ian Huntley had been serving a life sentence with a minimum term of 40 years for the murders of 10-year-olds Holly Wells and Jessica Chapman.', 'Ian Huntley had been serving a life sentence with a minimum term of 40 years for the murders of 10-year-olds Holly Wells and Jessica Chapman.', '', NULL, '3', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://www.bbc.com/news/articles/cr5l9d1dv9eo?at_medium=RSS&at_campaign=rss', '2026-03-11 21:39:42', '2026-03-12 17:05:16', '0.00', 'neutral', 'recent', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('482', 'Alternative to HRT for menopausal hot flushes now on NHS', 'alternative-to-hrt-for-menopausal-hot-flushes-now-on-nhs', 'The non-hormonal daily pill could benefit 500,000 women for whom HRT is not suitable.', 'The non-hormonal daily pill could benefit 500,000 women for whom HRT is not suitable.', '', NULL, '3', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://www.bbc.com/news/articles/cn0znqkqkdro?at_medium=RSS&at_campaign=rss', '2026-03-11 21:39:42', '2026-03-12 17:05:16', '0.00', 'neutral', 'recent', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('483', 'At least 17 killed after drone strikes school in Sudan', 'at-least-17-killed-after-drone-strikes-school-in-sudan', 'Strike in Shukeiri killed schoolgirls, teachers and healthcare workers in latest incident in three-year warAt least 17 people, most of them schoolgirls, were killed on Wednesday when an explosive-laden drone blamed on Sudan’s paramilitary Rapid Support Forces struck a secondary school and a health care centre.At least 10 people were wounded in the strike in the village of Shukeiri in the White Nile province, according to Dr Musa al-Majeri, director of Douiem hospital, the nearest major medical facility to the village. Continue reading...', 'Strike in Shukeiri killed schoolgirls, teachers and healthcare workers in latest incident in three-year warAt least 17 people, most of them schoolg...', '', NULL, '3', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://www.theguardian.com/world/2026/mar/11/at-least-17-killed-after-drone-strikes-school-in-sudan', '2026-03-12 05:31:38', '2026-03-12 17:05:16', '0.00', 'neutral', 'recent', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('484', 'UN Security Council demands Iran halt attacks on Gulf states', 'un-security-council-demands-iran-halt-attacks-on-gulf-states', 'The UN Security Council on Wednesday called for Iran to halt its attacks on Gulf states, in a resolution that did not mention US or Israeli strikes on Iran, prompting Tehran’s ambassador to decry a “blatant misuse” of the international body. The resolution, passed by 13 votes with two abstentions, “demands the immediate cessation of all […]', 'The UN Security Council on Wednesday called for Iran to halt its attacks on Gulf states, in a resolution that did not mention US or Israeli strikes...', '', NULL, '94', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://arynews.tv/un-calls-for-halt-to-irans-attacks-on-gulf-states/', '2026-03-12 05:36:48', '2026-03-12 17:05:16', '0.00', 'neutral', 'recent', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('485', 'Trump on Iran: We won, but don’t want to leave early', 'trump-on-iran-we-won-but-don-t-want-to-leave-early', 'President Donald Trump said on ‌Wednesday that “we won” the Iran war but that the United States will stay in ​the fight to finish the ​job. “You never like to say too ⁠early you won. We won,” ​Trump told a campaign-style rally in Hebron, Kentucky. “In ​the first hour it was over.” He said the […]', 'President Donald Trump said on ‌Wednesday that “we won” the Iran war but that the United States will stay in ​the fight to finish the ​jo...', '', NULL, '94', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://arynews.tv/trump-declares-victory-in-the-iran-war/', '2026-03-12 05:36:48', '2026-03-12 17:05:16', '0.00', 'neutral', 'recent', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('486', 'Trump administration estimates Iran war cost at over $11 billion in six days', 'trump-administration-estimates-iran-war-cost-at-over-11-billion-in-six-days', 'Officials from ​President Donald Trump’s administration estimated during a congressional briefing this week that the ‌first six days of the war on Iran had cost the United States at least $11.3 billion, a source familiar with the matter said on Wednesday. That figure, from a closed-door briefing for senators on Tuesday, ​did not include the […]', 'Officials from ​President Donald Trump’s administration estimated during a congressional briefing this week that the ‌first six days of the w...', '', NULL, '94', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://arynews.tv/war-costs-billions-dollars-spent-in-just-days/', '2026-03-12 05:36:48', '2026-03-12 17:05:16', '0.00', 'neutral', 'recent', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('487', 'Qatar PM urges resilience and unity amid Iran strikes', 'qatar-pm-urges-resilience-and-unity-amid-iran-strikes', 'Qatar’s Prime Minister calls for strengthening the country’s ability to withstand “hardship” amid regional war.', 'Qatar’s Prime Minister calls for strengthening the country’s ability to withstand “hardship” amid regional war.', '', NULL, '3', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://www.aljazeera.com/video/newsfeed/2026/3/12/qatar-pm-urges-resilience-and-unity-amid-iran-strikes?traffic_source=rss', '2026-03-12 05:45:05', '2026-03-12 17:05:16', '0.00', 'neutral', 'recent', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('488', 'Eight Arab, Islamic countries condemn Israeli closure of Al-Aqsa Mosque', 'eight-arab-islamic-countries-condemn-israeli-closure-of-al-aqsa-mosque', 'For the past 12 days, Israel has closed Al-Aqsa Mosque and restricted movement in the Old City of Jerusalem.', 'For the past 12 days, Israel has closed Al-Aqsa Mosque and restricted movement in the Old City of Jerusalem.', '', NULL, '3', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://www.aljazeera.com/news/2026/3/12/eight-arab-islamic-countries-condemn-israeli-closure-of-al-aqsa-mosque?traffic_source=rss', '2026-03-12 05:45:05', '2026-03-12 17:05:16', '0.00', 'neutral', 'recent', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('489', 'Iran war live: Oil tankers hit in Iraq, Tehran sets 3 conditions for peace', 'iran-war-live-oil-tankers-hit-in-iraq-tehran-sets-3-conditions-for-peace', 'Iran demands recognition of its rights, war reparations and guarantees against future aggression to end the war.', 'Iran demands recognition of its rights, war reparations and guarantees against future aggression to end the war.', '', NULL, '3', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://www.aljazeera.com/news/liveblog/2026/3/12/iran-war-live-oil-tankers-hit-in-iraq-tehran-sets-3-conditions-for-peace?traffic_source=rss', '2026-03-12 05:45:05', '2026-03-12 17:05:16', '0.00', 'neutral', 'recent', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('490', '\'Even under missiles we carry on living\' - how young Iranians are coping with war', 'even-under-missiles-we-carry-on-living-how-young-iranians-are-coping-with-war', 'Iranians say they are sheltering at home and rarely venturing out on near-empty streets as the US-Israeli bombing campaign continues.', 'Iranians say they are sheltering at home and rarely venturing out on near-empty streets as the US-Israeli bombing campaign continues.', '', NULL, '3', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://www.bbc.com/news/articles/c1mj1pyrzryo?at_medium=RSS&at_campaign=rss', '2026-03-12 05:45:16', '2026-03-12 17:05:16', '0.00', 'neutral', 'recent', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('491', 'Starmer was warned of \'reputational risk\' over Mandelson\'s links with Epstein, files show', 'starmer-was-warned-of-reputational-risk-over-mandelson-s-links-with-epstein-files-show', 'Documents also suggest the peer explored the possibility of a £500,000 severance payment after being sacked as US ambassador.', 'Documents also suggest the peer explored the possibility of a £500,000 severance payment after being sacked as US ambassador.', '', NULL, '3', '1', 'published', 'manual', '0', '0', '1', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://www.bbc.com/news/articles/cd70lgywgqqo?at_medium=RSS&at_campaign=rss', '2026-03-12 05:45:16', '2026-03-12 17:05:16', '0.00', 'neutral', 'recent', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('492', 'Chris Mason: Some nuggets but no huge revelations in first batch of Mandelson files', 'chris-mason-some-nuggets-but-no-huge-revelations-in-first-batch-of-mandelson-files', '<p data-t=\"{&quot;n&quot;:&quot;blueLinks&quot;,&quot;t&quot;:13,&quot;a&quot;:&quot;click&quot;,&quot;b&quot;:76}\">This first digital document drop about Prime Minister Sir Keir Starmer\'s decision to appoint Lord Peter Mandelson as ambassador to Washington is interesting, but not explosive.</p>\r\n<p data-t=\"{&quot;n&quot;:&quot;blueLinks&quot;,&quot;t&quot;:13,&quot;a&quot;:&quot;click&quot;,&quot;b&quot;:76}\">There are noteworthy nuggets,&nbsp;<a href=\"https://www.bbc.co.uk/news/articles/cm2rg8z6p1vo?xtor=AL-72-%5Bpartner%5D-%5Bmicrosoft%5D-%5Blink%5D-%5Bnews%5D-%5Bbizdev%5D-%5Bisapi%5D\" target=\"_blank\" rel=\"noopener\" data-t=\"{&quot;n&quot;:&quot;destination&quot;,&quot;t&quot;:13,&quot;a&quot;:&quot;click&quot;,&quot;b&quot;:1,&quot;c.t&quot;:7}\">as we set out here</a>, and the revelations about his payoff will be, to many, enraging.</p>\r\n<div class=\"intra-article-module\" data-t=\"{&quot;n&quot;:&quot;intraArticle&quot;,&quot;t&quot;:13}\">\r\n<div class=\"articlePageIntraArticleFullWidth ad-slot-placeholder left-image-intra-ad full-bleed-image-intra-ad intra-ad-rm-bg intra-ad-rm-h collapsed\" data-ad-index=\"0\">\r\n<div class=\"left-image-intra-ad full-bleed-image-intra-ad\">\r\n<div class=\"responsive-views-ad-container\">&nbsp;</div>\r\n</div>\r\n</div>\r\n</div>\r\n<p data-t=\"{&quot;n&quot;:&quot;blueLinks&quot;,&quot;t&quot;:13,&quot;a&quot;:&quot;click&quot;,&quot;b&quot;:76}\">Government, like any other institution, likes to present its public self as carefully packaged, shiny and ready for the shop window.</p>\r\n<p data-t=\"{&quot;n&quot;:&quot;blueLinks&quot;,&quot;t&quot;:13,&quot;a&quot;:&quot;click&quot;,&quot;b&quot;:76}\">The administrative factory floor, from which those public-facing decisions emerge, is rarely exposed to such sunlight.</p>\r\n<p data-t=\"{&quot;n&quot;:&quot;blueLinks&quot;,&quot;t&quot;:13,&quot;a&quot;:&quot;click&quot;,&quot;b&quot;:76}\">But it is with all this.</p>\r\n<p data-t=\"{&quot;n&quot;:&quot;blueLinks&quot;,&quot;t&quot;:13,&quot;a&quot;:&quot;click&quot;,&quot;b&quot;:76}\">The central political argument here, where the prime minister and Lord Mandelson are at odds, is whether the former ambassador lied to Downing Street about the nature and extent of his friendship with the late convicted sex offender Jeffrey Epstein.</p>\r\n<p data-t=\"{&quot;n&quot;:&quot;blueLinks&quot;,&quot;t&quot;:13,&quot;a&quot;:&quot;click&quot;,&quot;b&quot;:76}\">Yes he did, says Sir Keir. No I didn\'t, says the peer.</p>\r\n<p class=\"continue-read-break\" data-t=\"{&quot;n&quot;:&quot;blueLinks&quot;,&quot;t&quot;:13,&quot;a&quot;:&quot;click&quot;,&quot;b&quot;:76}\">But the&nbsp;<a href=\"https://assets.publishing.service.gov.uk/media/69b13135cdd628b29e3495f8/V1_FINAL.pdf\" target=\"_blank\" rel=\"noopener\" data-t=\"{&quot;n&quot;:&quot;destination&quot;,&quot;t&quot;:13,&quot;a&quot;:&quot;click&quot;,&quot;b&quot;:1,&quot;c.t&quot;:7}\">147 pages we have waded</a>&nbsp;through do not verify either claim.</p>\r\n<div class=\"continue-reading-slot\">\r\n<div id=\"continuousReadingContainer\" class=\"article-cont-read-container\">\r\n<div class=\"article-cont-read-button-container\" data-t=\"{&quot;n&quot;:&quot;readMore&quot;}\"><button class=\"control\" name=\"Continue reading\" value=\"\"><span class=\"content\"><img src=\"https://assets.msn.com/staticsb/statics/latest/views/icons/textExpand_filled.svg\" alt=\"Expand article logo\" aria-hidden=\"true\">&nbsp;&nbsp;Continue reading</span></button></div>\r\n</div>\r\n</div>\r\n<p>&nbsp;</p>\r\n<p data-t=\"{&quot;n&quot;:&quot;blueLinks&quot;,&quot;t&quot;:13,&quot;a&quot;:&quot;click&quot;,&quot;b&quot;:76}\">To be clear, they were never likely to as the Chief Secretary to the Prime Minister, Darren Jones, had&nbsp;<a href=\"https://hansard.parliament.uk/Commons/2026-02-23/debates/74A9204D-3B80-40C6-8A9D-C48D80714E91/LordMandelsonGovernmentResponseToHumbleAddress\" target=\"_blank\" rel=\"noopener\" data-t=\"{&quot;n&quot;:&quot;destination&quot;,&quot;t&quot;:13,&quot;a&quot;:&quot;click&quot;,&quot;b&quot;:1,&quot;c.t&quot;:7}\">warned last month</a> that a \"subset of this first tranche of documents is subject to an ongoing Metropolitan Police investigation. That includes correspondence between No. 10 and Lord Peter Mandelson, in which a number of follow-up questions were asked\".</p>', 'This first digital document drop about the prime minister&#039;s decision to appoint Lord Mandelson as US ambassador is interesting, but not explosive.', 'uploads/news/69b22fd5e7c22.jpeg', '', '3', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://www.bbc.com/news/articles/c627x7g2l4vo?at_medium=RSS&at_campaign=rss', '2026-03-12 05:45:16', '2026-03-12 17:05:16', '0.00', 'neutral', 'recent', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('493', 'Iran’s authorities warn against protests as Israel threatens Basij forces', 'iran-s-authorities-warn-against-protests-as-israel-threatens-basij-forces', '<p>Police chief says protesters seen as acting at the behest of the US and Israel will be treated as enemies.</p>', 'Police chief says protesters seen as acting at the behest of the US and Israel will be treated as enemies.', 'uploads/news/69b22bd5c65f3.webp', '', '3', '1', 'published', 'manual', '1', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://www.aljazeera.com/news/2026/3/12/irans-authorities-warn-against-protests-as-israel-threatens-basij-forces?traffic_source=rss', '2026-03-12 06:57:34', '2026-03-12 17:05:16', '0.00', 'neutral', 'recent', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('494', 'Oil prices rise despite deal to release record amount of reserves', 'oil-prices-rise-despite-deal-to-release-record-amount-of-reserves', '<div class=\"sc-9cd5bb24-0 lmzibM\" data-component=\"text-block\">\r\n<div class=\"sc-cd6075cf-0 DQtHs\">\r\n<div class=\"sc-82b3c53b-0 IAFLu\">\r\n<p class=\"sc-9a00e533-0 eZyhnA\">Oil prices continued to rise on Thursday despite major countries agreeing to&nbsp;<a class=\"sc-f9178328-0 iCaRzc\" href=\"https://www.bbc.com/news/articles/cly093xxlzzo\" target=\"_self\">release a record amount of oil from their emergency reserves</a>&nbsp;as they try to curb the impact of the Iran war.</p>\r\n</div>\r\n</div>\r\n</div>\r\n<div class=\"sc-9cd5bb24-0 lmzibM\" data-component=\"text-block\">\r\n<div class=\"sc-cd6075cf-0 DQtHs\">\r\n<div class=\"sc-82b3c53b-0 IAFLu\">\r\n<p class=\"sc-9a00e533-0 eZyhnA\">Brent crude was 6.9% higher at $98.33 (&pound;73.43) a barrel in morning trading in Asia even after all 32 members of the International Energy Agency\'s (IEA) said they will release 400 million barrels in response to supply concerns.</p>\r\n</div>\r\n</div>\r\n</div>\r\n<div class=\"sc-9cd5bb24-0 lmzibM\" data-component=\"text-block\">\r\n<div class=\"sc-cd6075cf-0 DQtHs\">\r\n<div class=\"sc-82b3c53b-0 IAFLu\">\r\n<p class=\"sc-9a00e533-0 eZyhnA\">On Wednesday, Iran warned that oil could reach $200 a barrel as its attacks on ships intensify in the Strait of Hormuz, a key waterway for energy shipments.</p>\r\n</div>\r\n</div>\r\n</div>\r\n<div class=\"sc-9cd5bb24-0 lmzibM\" data-component=\"text-block\">\r\n<div class=\"sc-cd6075cf-0 DQtHs\">\r\n<div class=\"sc-82b3c53b-0 IAFLu\">\r\n<p class=\"sc-9a00e533-0 eZyhnA\">An Islamic Revolutionary Guard Corps (IRGC) spokesperson said any vessel linked to the US, Israel or their allies will be targeted.</p>\r\n</div>\r\n</div>\r\n</div>', 'It comes as Iranian attacks on ships intensify in the crucial Strait of Hormuz waterway.', 'uploads/news/69b22c952fbe8.webp', '', '3', '1', 'published', 'manual', '1', '2', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://www.bbc.com/news/articles/c1w5141vx53o?at_medium=RSS&at_campaign=rss', '2026-03-12 07:44:46', '2026-03-12 17:05:16', '0.00', 'neutral', 'recent', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('495', 'Who bombed the Iranian girls’ school, killing more than 170? What we know', 'who-bombed-the-iranian-girls-school-killing-more-than-170-what-we-know', '<p data-t=\"{&quot;n&quot;:&quot;blueLinks&quot;,&quot;t&quot;:13,&quot;a&quot;:&quot;click&quot;,&quot;b&quot;:76}\">As the United States-Israeli war on Iran closes in on two weeks, one specific attack stands out as the bloodiest incident of the conflict so far.</p>\r\n<p data-t=\"{&quot;n&quot;:&quot;blueLinks&quot;,&quot;t&quot;:13,&quot;a&quot;:&quot;click&quot;,&quot;b&quot;:76}\">On February 28, during the opening hours of the assault on Iran, a missile struck a girls&rsquo; school in southern Iran, killing more than 170 people &ndash; most of them schoolgirls.</p>\r\n<p data-t=\"{&quot;n&quot;:&quot;blueLinks&quot;,&quot;t&quot;:13,&quot;a&quot;:&quot;click&quot;,&quot;b&quot;:76}\">Since then, Israel and the US have tried to distance themselves from the attack, even as evidence mounts that the US was responsible for the killings. To critics, the bombing of the school has become emblematic of the horrors of the war that the US and Israel have unleashed, and that Iran has responded to by launching thousands of missiles and drones not just at Israel and US facilities across the region, but also at Gulf neighbours who have tried hard to not get sucked into the conflict.</p>\r\n<p data-t=\"{&quot;n&quot;:&quot;blueLinks&quot;,&quot;t&quot;:13,&quot;a&quot;:&quot;click&quot;,&quot;b&quot;:76}\">So what do we know about the totemic incident that has shaped, for many, the early days of the war?</p>\r\n<h2 id=\"what-happened-in-the-iran-school-strike\" class=\"article-sub-heading\">What happened in the Iran school strike?</h2>\r\n<p data-t=\"{&quot;n&quot;:&quot;blueLinks&quot;,&quot;t&quot;:13,&quot;a&quot;:&quot;click&quot;,&quot;b&quot;:76}\">The girls&rsquo; school, Shajareh Tayyebeh, was located in the city of Minab, near a base belonging to the Islamic Revolutionary Guard Corps (IRGC).</p>\r\n<p data-t=\"{&quot;n&quot;:&quot;blueLinks&quot;,&quot;t&quot;:13,&quot;a&quot;:&quot;click&quot;,&quot;b&quot;:76}\">It was hit by a missile on February 28 at approximately 10:45am local time (07:15 GMT), a peak hour for classroom activity. The blast destroyed the two-storey building, causing the roof to collapse on students and teachers inside.</p>\r\n<div class=\"intra-article-module\" data-t=\"{&quot;n&quot;:&quot;intraArticle&quot;,&quot;t&quot;:13}\">\r\n<div class=\"articlePageIntraArticleFullWidth ad-slot-placeholder left-image-intra-ad full-bleed-image-intra-ad intra-ad-rm-bg intra-ad-rm-h collapsed\" data-ad-index=\"1\">\r\n<div class=\"left-image-intra-ad full-bleed-image-intra-ad\">\r\n<div class=\"responsive-views-ad-container\">&nbsp;</div>\r\n</div>\r\n</div>\r\n</div>\r\n<p data-t=\"{&quot;n&quot;:&quot;blueLinks&quot;,&quot;t&quot;:13,&quot;a&quot;:&quot;click&quot;,&quot;b&quot;:76}\">At least 170 people, most of them children, were killed. Dozens of others were injured.</p>\r\n<p data-t=\"{&quot;n&quot;:&quot;blueLinks&quot;,&quot;t&quot;:13,&quot;a&quot;:&quot;click&quot;,&quot;b&quot;:76}\">The school is located in Minab, in Iran&rsquo;s strategic Hormozgan province, which overlooks the Strait of Hormuz and hosts several IRGC naval facilities.</p>\r\n<p class=\"continue-read-break\" data-t=\"{&quot;n&quot;:&quot;blueLinks&quot;,&quot;t&quot;:13,&quot;a&quot;:&quot;click&quot;,&quot;b&quot;:76}\">While Iran immediately attributed the strike to the US-Israel coalition, both nations denied responsibility.</p>\r\n<p data-t=\"{&quot;n&quot;:&quot;blueLinks&quot;,&quot;t&quot;:13,&quot;a&quot;:&quot;click&quot;,&quot;b&quot;:76}\">Satellite images showed the school intact earlier that morning. US and Israeli air raids had begun across Minab and other parts of Hormozgan that morning.</p>\r\n<p>&nbsp;</p>', 'The attack on Shajareh Tayyebeh Elementary School quickly became a focal point of concern over civilian casualties.', 'uploads/news/69b22ddfe246d.jpeg', '', '3', '1', 'published', 'manual', '0', '2', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://www.aljazeera.com/news/2026/3/12/who-bombed-the-iranian-girls-school-killing-more-than-170-what-we-know?traffic_source=rss', '2026-03-12 07:59:06', '2026-03-12 17:05:16', '0.00', 'neutral', 'recent', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('496', 'Israeli strike hits Beirut waterfront near tents of displaced', 'israeli-strike-hits-beirut-waterfront-near-tents-of-displaced', 'At least six people are dead from an Israeli strike on Beirut’s Ramlet al-Baida seafront.', 'At least six people are dead from an Israeli strike on Beirut’s Ramlet al-Baida seafront.', '', NULL, '3', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://www.aljazeera.com/video/newsfeed/2026/3/12/israeli-strike-hits-beirut-waterfront-near-tents-of-displaced?traffic_source=rss', '2026-03-12 07:59:06', '2026-03-12 17:05:16', '0.00', 'neutral', 'recent', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('497', 'Oil hits $100 a barrel despite deal to release record amount of reserves', 'oil-hits-100-a-barrel-despite-deal-to-release-record-amount-of-reserves', '<p>It comes as Iranian attacks on ships intensify in the crucial Strait of Hormuz waterway.</p>', 'It comes as Iranian attacks on ships intensify in the crucial Strait of Hormuz waterway.', 'uploads/news/69b24f778edc7.webp', '', '3', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://www.bbc.com/news/articles/c1w5141vx53o?at_medium=RSS&at_campaign=rss', '2026-03-12 08:15:47', '2026-03-12 17:05:16', '0.00', 'neutral', 'recent', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('498', 'Kylie Kelce jokes about viral meme about Donna Kelce’s home renovation', 'kylie-kelce-jokes-about-viral-meme-about-donna-kelce-s-home-renovation', '<p>Kylie Kelce has cleared up the story behind the viral meme about her mother-in-law Donna Kelce&rsquo;s home renovation home in Orlando, Florida, using her signature dry humor to address the internet frenzy. The 33-year-old media personality shared a video through the social media account for her podcast Not Gonna Lie, jokingly breaking down the &ldquo;groundbreaking [&hellip;]</p>', 'Kylie Kelce has cleared up the story behind the viral meme about her mother-in-law Donna Kelce’s home renovation home in Orlando, Florida, using ...', 'uploads/news/69b24cd4ac540.webp', '', '94', '1', 'published', 'manual', '1', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://arynews.tv/kylie-kelce-jokes-about-viral-meme-about-donna-kelces-home-renovation/', '2026-03-12 09:28:45', '2026-03-12 17:05:16', '0.00', 'neutral', 'recent', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('499', 'Veteran actor Asim Bukhari passes away at 76', 'veteran-actor-asim-bukhari-passes-away-at-76', '<p>Veteran Pakistani actor Asim Bukhari has passed away at the age of 76 after battling multiple health issues, leaving fans and the entertainment industry in mourning. According to reports, the senior actor died on Wednesday, March 11, after suffering from prolonged kidney and heart problems. He had been hospitalized for around two weeks before his [&hellip;]</p>', 'Veteran Pakistani actor Asim Bukhari has passed away at the age of 76 after battling multiple health issues, leaving fans and the entertainment ind...', 'uploads/news/69b24d5831d66.jpeg', '', '94', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://arynews.tv/veteran-actor-asim-bukhari-passes-away-at-76/', '2026-03-12 09:28:45', '2026-03-12 17:05:16', '0.00', 'neutral', 'recent', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('500', 'Oil prices jump despite strategic reserve release', 'oil-prices-jump-despite-strategic-reserve-release', '<p>A record release of oil from strategic reserves by IEA nations failed to ease concerns about the impact from the Middle East war, with crude prices pushing further higher and stocks sliding on Wednesday. The move to release oil stocks came as Iran said it was ready for a long war of attrition that would [&hellip;]</p>', 'A record release of oil from strategic reserves by IEA nations failed to ease concerns about the impact from the Middle East war, with crude prices...', 'uploads/news/69b24dfdf0c80.jpg', '', '94', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://arynews.tv/oil-prices-jump-despite-strategic-reserve-release/', '2026-03-12 09:28:45', '2026-03-12 17:05:16', '0.00', 'neutral', 'recent', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('501', 'Five vessels attacked amid reports of Iranian drone boats, sea mines', 'five-vessels-attacked-amid-reports-of-iranian-drone-boats-sea-mines', 'Two oil tankers hit by explosive-laden boats in Iraqi waters amid reports Iran may be using unmanned surface vessels.', 'Two oil tankers hit by explosive-laden boats in Iraqi waters amid reports Iran may be using unmanned surface vessels.', '', NULL, '3', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://www.aljazeera.com/news/2026/3/12/five-vessels-attacked-amid-reports-of-iranian-drone-boats-sea-mines?traffic_source=rss', '2026-03-12 09:28:50', '2026-03-12 17:05:16', '0.00', 'neutral', 'recent', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('502', 'How is feminism being used to justify war in Iran?', 'how-is-feminism-being-used-to-justify-war-in-iran', 'Some supporters of the US-Israeli war on Iran are using the treatment of women in the country as a justification.', 'Some supporters of the US-Israeli war on Iran are using the treatment of women in the country as a justification.', '', NULL, '3', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://www.aljazeera.com/video/newsfeed/2026/3/12/how-is-feminism-being-used-to-justify-war-in-iran?traffic_source=rss', '2026-03-12 09:28:50', '2026-03-12 17:05:16', '0.00', 'neutral', 'recent', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('503', 'Fire erupts at Gaza camp after Israeli attack hits tents', 'fire-erupts-at-gaza-camp-after-israeli-attack-hits-tents', 'Fire broke out at tents sheltering displaced Palestinians in Gaza’s Al-Ansar refugee camp after an Israeli strike.', 'Fire broke out at tents sheltering displaced Palestinians in Gaza’s Al-Ansar refugee camp after an Israeli strike.', '', NULL, '3', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://www.aljazeera.com/video/newsfeed/2026/3/12/fire-erupts-at-gaza-camp-after-israeli-attack-hits-tents?traffic_source=rss', '2026-03-12 09:28:50', '2026-03-12 17:05:16', '0.00', 'neutral', 'recent', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('504', 'Epstein used modelling agent to recruit girls, Brazilian women tell BBC', 'epstein-used-modelling-agent-to-recruit-girls-brazilian-women-tell-bbc', '<p>Modelling agent used businesses to recruit girls and arrange US visas to visit Jeffrey Epstein, Brazilian women tell BBC.</p>', 'Modelling agent used businesses to recruit girls and arrange US visas to visit Jeffrey Epstein, Brazilian women tell BBC.', 'uploads/news/69b243cfd6fab.webp', '', '3', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://www.bbc.com/news/articles/cr4576v66kno?at_medium=RSS&at_campaign=rss', '2026-03-12 09:29:49', '2026-03-12 17:05:16', '0.00', 'neutral', 'recent', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('505', 'Hundreds of GPs tell BBC they have never refused a sick note over mental health concerns', 'hundreds-of-gps-tell-bbc-they-have-never-refused-a-sick-note-over-mental-health-concerns', '<p>The number of fit notes issued has been rising, with more than 11.2m approved in England last year.</p>', 'The number of fit notes issued has been rising, with more than 11.2m approved in England last year.', 'uploads/news/69b250a753240.webp', '', '3', '1', 'published', 'manual', '0', '1', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://www.bbc.com/news/articles/c20lew24kngo?at_medium=RSS&at_campaign=rss', '2026-03-12 09:29:49', '2026-03-17 23:46:25', '0.00', 'neutral', 'recent', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('506', 'Hugh Jackman goes live with his performance in NYC', 'hugh-jackman-goes-live-with-his-performance-in-nyc', 'Hugh Jackman put his showman credentials to good use. Hugh Jackman performed a live set at Rupert Murdoch’s 95th birthday party in New York and announced a fresh slate of theatrical productions in the same week. On Saturday, the X-Men star sang from The Greatest Showman alongside covers of Fly Me to the Moon and New […]', 'Hugh Jackman put his showman credentials to good use. Hugh Jackman performed a live set at Rupert Murdoch’s 95th birthday party in New York and a...', '', NULL, '94', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://arynews.tv/hugh-jackman-goes-live-with-his-performance-in-nyc/', '2026-03-12 10:30:30', '2026-03-12 17:05:16', '0.00', 'neutral', 'recent', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('507', 'Billie Eilish eyes big-screen acting debut as Esther Greenwood in The Bell Jar', 'billie-eilish-eyes-big-screen-acting-debut-as-esther-greenwood-in-the-bell-jar', 'Billie Eilish is reportedly preparing to take a major step into Hollywood, as she is in talks to make her big-screen acting debut in director Sarah Polley’s upcoming adaptation of the classic novel The Bell Jar. Multiple reports on March 11 indicated that the 24-year-old singer is being considered for the lead role of Esther […]', 'Billie Eilish is reportedly preparing to take a major step into Hollywood, as she is in talks to make her big-screen acting debut in director Sarah...', '', NULL, '94', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://arynews.tv/billie-eilish-eyes-big-screen-acting-debut-as-esther-greenwood-in-the-bell-jar/', '2026-03-12 10:30:30', '2026-03-12 17:05:16', '0.00', 'neutral', 'recent', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('508', 'FBI bulletin warned of possible Iran retaliation on California targets', 'fbi-bulletin-warned-of-possible-iran-retaliation-on-california-targets', 'The FBI warned law enforcement agencies last month of ​the possibility that Tehran might try to retaliate for any US strikes on Iran by launching surprise drone attacks in California, ‌according to a security bulletin seen by Reuters. The confidential alert, issued by the FBI through the multi-agency Los Angeles Joint Regional Intelligence Center, surfaced […]', 'The FBI warned law enforcement agencies last month of ​the possibility that Tehran might try to retaliate for any US strikes on Iran by launching...', '', NULL, '94', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://arynews.tv/fbi-warns-of-possible-iran-retaliation-on-california-targets/', '2026-03-12 10:30:30', '2026-03-12 17:05:16', '0.00', 'neutral', 'recent', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('509', 'China’s key NPC meeting comes to a close as lower growth target set', 'china-s-key-npc-meeting-comes-to-a-close-as-lower-growth-target-set', 'The National People\'s Congress signals firm stance against corruption as China\'s 15th five-year plan is approved.', 'The National People\'s Congress signals firm stance against corruption as China\'s 15th five-year plan is approved.', '', NULL, '3', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://www.aljazeera.com/news/2026/3/12/chinas-key-npc-meeting-comes-to-a-close-as-lower-growth-target-set?traffic_source=rss', '2026-03-12 10:32:49', '2026-03-12 17:05:16', '0.00', 'neutral', 'recent', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('510', 'There is only one person’ who can decide to end the war on Iran', 'there-is-only-one-person-who-can-decide-to-end-the-war-on-iran', 'Israeli journalist and writer, Gideon Levy, says the decision to end the Iran war rests only with US President Trump.', 'Israeli journalist and writer, Gideon Levy, says the decision to end the Iran war rests only with US President Trump.', '', NULL, '3', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://www.aljazeera.com/video/quotable/2026/3/12/there-is-only-one-person-who-can-decide-to-end-the-war-on-iran?traffic_source=rss', '2026-03-12 10:32:49', '2026-03-12 17:05:16', '0.00', 'neutral', 'recent', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('511', 'Why historic oil reserves release may do little to bring down rising prices', 'why-historic-oil-reserves-release-may-do-little-to-bring-down-rising-prices', 'Oil prices continue to surge despite the International Energy Agency\'s plans to release 400m barrels into the market.', 'Oil prices continue to surge despite the International Energy Agency\'s plans to release 400m barrels into the market.', '', NULL, '3', '1', 'published', 'manual', '0', '1', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://www.aljazeera.com/economy/2026/3/12/why-historic-oil-reserves-release-may-do-little-to-bring-down-rising-prices?traffic_source=rss', '2026-03-12 10:32:49', '2026-03-12 17:05:16', '0.00', 'neutral', 'recent', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('512', '‘Invasive’ AI-led mass surveillance in Africa violating freedoms, warn experts', 'invasive-ai-led-mass-surveillance-in-africa-violating-freedoms-warn-experts', 'Countries across the continent have spent more than $2bn on Chinese tracking technology that is not ‘necessary or proportionate’, new report findsThe rapid expansion of AI-powered mass-surveillance systems across Africa is violating citizens’ right to privacy and having a chilling effect on society, according to experts on human rights and emerging technologies.At least $2bn (£1.5bn) has been spent by 11 African governments on Chinese-built surveillance technology that recognises faces and monitors movements, according to a new report by the Institute of Development Studies, which warns that national security is being used to justify implementing these systems with little regulation. Continue reading...', 'Countries across the continent have spent more than $2bn on Chinese tracking technology that is not ‘necessary or proportionate’, new report fi...', '', NULL, '3', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://www.theguardian.com/global-development/2026/mar/12/invasive-ai-led-mass-surveillance-in-africa-violating-freedoms-warn-experts', '2026-03-12 16:39:58', '2026-03-12 17:24:00', '0.00', 'neutral', 'new', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('513', 'Middle East war displaces three million inside Iran: UN', 'middle-east-war-displaces-three-million-inside-iran-un', 'GENEVA: Up to 3.2 million people have been displaced inside Iran since the Middle East war erupted, the United Nations refugee agency said Thursday. “Between 600,000 and one million Iranian households are now temporarily displaced inside Iran as a result of the ongoing conflict, according to preliminary assessments,” said Ayaki Ito, who heads UNHCR’s emergency […]', 'GENEVA: Up to 3.2 million people have been displaced inside Iran since the Middle East war erupted, the United Nations refugee agency said Thursday...', '', NULL, '94', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://arynews.tv/middle-east-war-displaces-three-million-inside-iran-un/', '2026-03-12 16:40:15', '2026-03-12 17:24:23', '0.00', 'neutral', 'new', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('514', 'King Charles invited to 150th anniversary cricket Test in Melbourne', 'king-charles-invited-to-150th-anniversary-cricket-test-in-melbourne', 'King Charles and global cricket royalty have been invited to celebrate the 150th anniversary of the first Test match in Melbourne, officials said Thursday, with ticket sales through the roof a year before a ball is bowled. Australia will play England under lights at the Melbourne Cricket Ground to celebrate the landmark from March 11-15 […]', 'King Charles and global cricket royalty have been invited to celebrate the 150th anniversary of the first Test match in Melbourne, officials said T...', '', NULL, '94', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://arynews.tv/king-charles-invited-to-150th-anniversary-cricket-test-in-melbourne/', '2026-03-12 16:40:15', '2026-03-12 17:24:29', '0.00', 'neutral', 'new', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('515', 'Iran to allow India-flagged tankers through Hormuz: source', 'iran-to-allow-india-flagged-tankers-through-hormuz-source', 'Iran to allow India-flagged tankers through Hormuz: source', 'Iran to allow India-flagged tankers through Hormuz: source', '', NULL, '94', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://arynews.tv/iran-to-allow-india-flagged-tankers-through-hormuz-source/', '2026-03-12 16:40:15', '2026-03-12 17:24:35', '0.00', 'neutral', 'new', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('516', 'The war of signals: How Russia and China help Iran see the battlefield', 'the-war-of-signals-how-russia-and-china-help-iran-see-the-battlefield', 'Electronic warfare and intelligence sharing are eroding decades of US-Israeli dominance in the Gulf.', 'Electronic warfare and intelligence sharing are eroding decades of US-Israeli dominance in the Gulf.', '', NULL, '3', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://www.aljazeera.com/opinions/2026/3/12/the-war-of-signals-how-russia-and-china-help-iran-see-the-battlefield?traffic_source=rss', '2026-03-12 16:41:15', '2026-03-12 17:24:06', '0.00', 'neutral', 'new', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('517', 'Dubai high-rise building damaged in overnight drone hit', 'dubai-high-rise-building-damaged-in-overnight-drone-hit', 'Video shows damage to a Dubai high-rise building hit by a drone.', 'Video shows damage to a Dubai high-rise building hit by a drone.', '', NULL, '3', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://www.aljazeera.com/video/newsfeed/2026/3/12/dubai-high-rise-building-damaged-in-overnight-drone-hit?traffic_source=rss', '2026-03-12 16:41:15', '2026-03-12 17:24:11', '0.00', 'neutral', 'new', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('518', 'Chile’s new far-right president is sworn in', 'chile-s-new-far-right-president-is-sworn-in', 'Chile’s new president Jose Antonio Kast has been sworn in, marking a sharp shift to the right in the country’s politics.', 'Chile’s new president Jose Antonio Kast has been sworn in, marking a sharp shift to the right in the country’s politics.', '', NULL, '3', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://www.aljazeera.com/video/newsfeed/2026/3/12/chiles-new-far-right-president-is-sworn-in?traffic_source=rss', '2026-03-12 16:41:15', '2026-03-12 17:24:18', '0.00', 'neutral', 'new', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('519', 'Lloyds, Bank of Scotland and Halifax apps showed customers other users\' transactions', 'lloyds-bank-of-scotland-and-halifax-apps-showed-customers-other-users-transactions', 'The Lloyds Banking Group customers reported being able to view payments and charges from other sources.', 'The Lloyds Banking Group customers reported being able to view payments and charges from other sources.', '', NULL, '3', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://www.bbc.com/news/articles/c4g23npxpwgo?at_medium=RSS&at_campaign=rss', '2026-03-12 16:41:46', '2026-03-12 17:23:46', '0.00', 'neutral', 'new', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('520', 'Minister defends PM\'s handling of Mandelson appointment', 'minister-defends-pm-s-handling-of-mandelson-appointment', 'Documents show Sir Keir Starmer was warned the peer\'s relationship with Jeffrey Epstein posed a \"reputational risk\".', 'Documents show Sir Keir Starmer was warned the peer\'s relationship with Jeffrey Epstein posed a \"reputational risk\".', '', NULL, '3', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://www.bbc.com/news/articles/c875nzlv7ddo?at_medium=RSS&at_campaign=rss', '2026-03-12 16:41:46', '2026-03-12 17:23:52', '0.00', 'neutral', 'new', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('521', 'Illegal alien\'s violent tussle with federal officer leads to multiple charges after suspected Biden-era entry', 'illegal-alien-s-violent-tussle-with-federal-officer-leads-to-multiple-charges-after-suspected-biden-era-entry', 'The incident comes as Senate lawmakers remain sharply divided over how to proceed with fully funding DHS as the agency&apos;s shutdown neared the 30-day mark.', 'The incident comes as Senate lawmakers remain sharply divided over how to proceed with fully funding DHS as the agency&apos;s shutdown neared the 3...', '', NULL, NULL, '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '2026-03-13 05:27:36', 'https://www.foxnews.com/politics/illegal-aliens-violent-tussle-federal-officer-leads-multiple-charges-after-suspected-biden-era-entry', '2026-03-13 05:26:01', '2026-03-13 05:27:36', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('522', 'White House balks at new CBS staffer over Liz Cheney ties', 'white-house-balks-at-new-cbs-staffer-over-liz-cheney-ties', 'The White House was irked by the news that CBS News hired a top staffer for ex-Republican Rep. Liz Cheney, a staunch critic of President Donald Trump.', 'The White House was irked by the news that CBS News hired a top staffer for ex-Republican Rep. Liz Cheney, a staunch critic of President Donald Trump.', '', NULL, NULL, '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '2026-03-13 05:27:36', 'https://www.foxnews.com/media/white-house-balks-new-cbs-staffer-over-liz-cheney-ties', '2026-03-13 05:26:01', '2026-03-13 05:27:36', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('523', 'California lawmakers demand reform as another serial child molester gets parole despite 355-year sentence', 'california-lawmakers-demand-reform-as-another-serial-child-molester-gets-parole-despite-355-year-sentence', 'Gregory Vogelsang, convicted of molesting six boys, was granted early release. GOP leaders demand Gavin Newsom fire the hand-picked parole board.', 'Gregory Vogelsang, convicted of molesting six boys, was granted early release. GOP leaders demand Gavin Newsom fire the hand-picked parole board.', '', NULL, NULL, '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '2026-03-13 05:27:36', 'https://www.foxnews.com/us/california-lawmakers-demand-reform-another-serial-child-molester-gets-parole-355-year-sentence', '2026-03-13 05:26:01', '2026-03-13 05:27:36', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('524', 'Eswatini says it received more ‘third country’ deportees as part of deal with Trump administration', 'eswatini-says-it-received-more-third-country-deportees-as-part-of-deal-with-trump-administration', 'Two deportees sent to Eswatini were from Somalia, one was from Sudan and one was from TanzaniaThe government of Eswatini announced on Thursday it received four more “third country” deportees from the United States, as part of the Trump administration’s multimillion-dollar deal with the small African nation.Now, a total of 19 deportees from the US have been sent to Eswatini when they hail from other countries, amid the Trump administration’s continued anti-immigrant crackdown and changes to immigration policy. Continue reading...', 'Two deportees sent to Eswatini were from Somalia, one was from Sudan and one was from TanzaniaThe government of Eswatini announced on Thursday it r...', '', NULL, '3', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '2026-03-13 05:28:50', 'https://www.theguardian.com/us-news/2026/mar/12/trump-immigration-deportations-eswatini', '2026-03-13 05:28:00', '2026-03-13 05:28:50', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('525', 'UK government axes flagship global health project', 'uk-government-axes-flagship-global-health-project', 'Programme which supports schemes in six African countries was previously hailed as vital protection for Britain against future pandemicsA flagship health project in Africa, which UK ministers said would play a vital role in protecting Britain from future pandemic threats, is being axed due to aid cuts, the Guardian can reveal.The Global Health Workforce Programme (GHWP) which supported development and training for healthcare staff in six African countries, will close at the end of the month, the Foreign, Commonwealth and Development Office (FCDO) said. Continue reading...', 'Programme which supports schemes in six African countries was previously hailed as vital protection for Britain against future pandemicsA flagship ...', '', NULL, '3', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '2026-03-13 05:28:50', 'https://www.theguardian.com/global-development/2026/mar/12/uk-government-axes-flagship-global-health-project', '2026-03-13 05:28:00', '2026-03-13 05:28:50', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('526', 'PM Shehbaz Sharif meets Mohammed bin Salman in Jeddah', 'pm-shehbaz-sharif-meets-mohammed-bin-salman-in-jeddah', 'The Prime Minister of Pakistan, Shehbaz Sharif, held a restricted meeting with Crown Prince Mohammed bin Salman bin Abdulaziz Al Saud, Crown Prince and Prime Minister of the Kingdom of Saudi Arabia, in Jeddah today. Deputy Prime Minister & Foreign Minister Senator Mohammad Ishaq Dar and Chief of Army Staff & Chief of Defence Forces […]', 'The Prime Minister of Pakistan, Shehbaz Sharif, held a restricted meeting with Crown Prince Mohammed bin Salman bin Abdulaziz Al Saud, Crown Prince...', '', NULL, '94', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '2026-03-13 05:50:47', 'https://arynews.tv/shehbaz-sharif-expresses-support-for-saudi-arabia/', '2026-03-13 05:29:02', '2026-03-13 05:50:47', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('527', 'Pakistan urges restraint amid escalating Middle East tensions', 'pakistan-urges-restraint-amid-escalating-middle-east-tensions', 'ISLAMABAD: Pakistan says it is engaging regional and international partners to promote restraint and diplomacy as hostilities escalate in the Middle East. Speaking at the weekly briefing in Islamabad, Foreign Office spokesperson of Pakistan, Ambassador Tahir Andrabi, said Pakistan had consistently called for respect for sovereignty, adherence to international law and renewed dialogue to prevent […]', 'ISLAMABAD: Pakistan says it is engaging regional and international partners to promote restraint and diplomacy as hostilities escalate in the Middl...', '', NULL, '94', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '2026-03-13 05:50:47', 'https://arynews.tv/pakistan-diplomatic-efforts-amid-middle-east-tensions/', '2026-03-13 05:29:02', '2026-03-13 05:50:47', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('528', 'Iran war unsettles India’s packaged water makers', 'iran-war-unsettles-india-s-packaged-water-makers', 'The Iran war is rattling India’s $5 billion packaged water market just ahead of the sweltering ‌summer season. One of the world’s fastest growing bottled water markets is seeing some manufacturers hike prices for distributors, as supply disruptions linked to the war fuel higher costs in everything from plastic bottles to caps, labels and cardboard […]', 'The Iran war is rattling India’s $5 billion packaged water market just ahead of the sweltering ‌summer season. One of the world’s fastest gro...', '', NULL, '94', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '2026-03-13 05:50:47', 'https://arynews.tv/india-packaged-water-market-faces-challenges/', '2026-03-13 05:29:02', '2026-03-13 05:50:47', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('529', 'Michigan synagogue ramming an ‘act of targeted violence’: FBI', 'michigan-synagogue-ramming-an-act-of-targeted-violence-fbi', 'The FBI says it is treating a vehicle ramming at a Michigan synagogue as an attack on the Jewish community.', 'The FBI says it is treating a vehicle ramming at a Michigan synagogue as an attack on the Jewish community.', '', NULL, '3', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '2026-03-13 05:43:36', 'https://www.aljazeera.com/video/newsfeed/2026/3/13/michigan-synagogue-ramming-an-act-of-targeted-violence-fbi?traffic_source=rss', '2026-03-13 05:30:00', '2026-03-13 05:43:36', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('530', 'Iran war live: Trump says war going ‘well’ as Gulf under wave of attacks', 'iran-war-live-trump-says-war-going-well-as-gulf-under-wave-of-attacks', 'Israeli strikes kill dozens more in Lebanon, including 5 children among nine people killed in attack on village of Arki.', 'Israeli strikes kill dozens more in Lebanon, including 5 children among nine people killed in attack on village of Arki.', '', NULL, '3', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '2026-03-13 05:50:47', 'https://www.aljazeera.com/news/liveblog/2026/3/13/iran-war-live-trump-says-war-going-well-as-gulf-under-wave-of-attacks?traffic_source=rss', '2026-03-13 05:30:00', '2026-03-13 05:50:47', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('531', 'UN fact-finding mission warns of continued human rights abuses in Venezuela', 'un-fact-finding-mission-warns-of-continued-human-rights-abuses-in-venezuela', 'In an address to the UN Human Rights Council, experts warned that the \'machinery\' of repression was \'mutating\'.', 'In an address to the UN Human Rights Council, experts warned that the \'machinery\' of repression was \'mutating\'.', '', NULL, '3', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '2026-03-13 05:50:47', 'https://www.aljazeera.com/news/2026/3/12/un-fact-finding-mission-warns-of-continued-human-rights-abuses-in-venezuela?traffic_source=rss', '2026-03-13 05:30:00', '2026-03-13 05:50:47', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('533', 'What on earth is going on with the oil price?', 'what-on-earth-is-going-on-with-the-oil-price', 'Oil price moves have made headlines since the Iran conflict started - but why have there been such sharp swings?', 'Oil price moves have made headlines since the Iran conflict started - but why have there been such sharp swings?', '', NULL, '3', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '2026-03-13 05:43:36', 'https://www.bbc.com/news/articles/ce3g49w5zxwo?at_medium=RSS&at_campaign=rss', '2026-03-13 05:34:02', '2026-03-13 05:43:36', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('537', 'Wary allies show there\'s no quick fix to Trump\'s Iran crisis', 'wary-allies-show-there-s-no-quick-fix-to-trump-s-iran-crisis', 'European leaders are hesitant to help Trump secure the Strait of Hormuz, but they know inaction on the Iran war isn\'t really an option.', 'European leaders are hesitant to help Trump secure the Strait of Hormuz, but they know inaction on the Iran war isn\'t really an option....', NULL, NULL, '1', '1', 'published', 'manual', '0', '1', '0', '0.00', '0', '0', '2026-03-16 20:12:25', 'https://www.bbc.com/news/articles/c8r17plnvy3o?at_medium=RSS&at_campaign=rss', '2026-03-16 20:42:39', '2026-03-17 12:21:24', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('538', 'What are the symptoms of meningitis and is there a vaccine?', 'what-are-the-symptoms-of-meningitis-and-is-there-a-vaccine', 'Two people have died following an outbreak of meningitis, including one student at the University of Kent.', 'Two people have died following an outbreak of meningitis, including one student at the University of Kent....', NULL, NULL, '1', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '2026-03-16 19:59:08', 'https://www.bbc.com/news/articles/c7432klgyldo?at_medium=RSS&at_campaign=rss', '2026-03-16 20:42:39', '2026-03-16 20:42:39', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('539', 'Starmer announces £53m to help households most hit by rising heating oil costs', 'starmer-announces-53m-to-help-households-most-hit-by-rising-heating-oil-costs', 'The money will be for \"vulnerable\" households who have faced a sharp rise in energy bills since the outbreak of the US-Israeli war with Iran.', 'The money will be for \"vulnerable\" households who have faced a sharp rise in energy bills since the outbreak of the US-Israeli war with Iran....', NULL, NULL, '1', '1', 'published', 'manual', '0', '2', '0', '0.00', '0', '0', '2026-03-16 20:34:58', 'https://www.bbc.com/news/articles/cp9mgpzn901o?at_medium=RSS&at_campaign=rss', '2026-03-16 20:42:39', '2026-03-17 23:40:25', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('540', 'Key Oscars moments as snubbed Chalamet becomes butt of jokes', 'key-oscars-moments-as-snubbed-chalamet-becomes-butt-of-jokes', 'Here\'s what happened inside the winners room and other insights from the biggest night in Hollywood.', 'Here\'s what happened inside the winners room and other insights from the biggest night in Hollywood....', NULL, NULL, '1', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '2026-03-16 15:32:03', 'https://www.bbc.com/news/articles/cp9mg90k78eo?at_medium=RSS&at_campaign=rss', '2026-03-16 20:43:55', '2026-03-16 20:43:55', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('541', 'Elusive nightjar birds making remarkable comeback, conservationists say', 'elusive-nightjar-birds-making-remarkable-comeback-conservationists-say', 'An ecological survey has found 109 nightjar territories in the lowland heaths of east Hampshire.', 'An ecological survey has found 109 nightjar territories in the lowland heaths of east Hampshire....', NULL, NULL, '1', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '2026-03-16 16:13:19', 'https://www.bbc.com/news/articles/c7952d4dwd8o?at_medium=RSS&at_campaign=rss', '2026-03-16 20:43:55', '2026-03-16 20:43:55', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('542', 'Schoolgirl \'traumatised\' after being wrongly sent to England for abortion', 'schoolgirl-traumatised-after-being-wrongly-sent-to-england-for-abortion', 'The teenager could have been treated in Northern Ireland, but was sent to London due to confusion over the services available.', 'The teenager could have been treated in Northern Ireland, but was sent to London due to confusion over the services available....', NULL, NULL, '1', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '2026-03-16 14:47:33', 'https://www.bbc.com/news/articles/cp32kwe2280o?at_medium=RSS&at_campaign=rss', '2026-03-16 20:43:55', '2026-03-16 20:43:55', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('543', 'Travelodge changes policy after attacker given room key', 'travelodge-changes-policy-after-attacker-given-room-key', 'The woman was attacked by Kyran Smith, who was given a key to her hotel room by staff.', 'The woman was attacked by Kyran Smith, who was given a key to her hotel room by staff....', NULL, NULL, '1', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '2026-03-16 13:27:29', 'https://www.bbc.com/news/articles/c4g01m82vn2o?at_medium=RSS&at_campaign=rss', '2026-03-16 20:48:41', '2026-03-16 20:48:41', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('544', 'Meta and TikTok let harmful content rise after evidence outrage drove engagement, say whistleblowers', 'meta-and-tiktok-let-harmful-content-rise-after-evidence-outrage-drove-engagement-say-whistleblowers', 'Companies allowed more harmful content on user’s feeds, knowing their algorithms ran on outrage, BBC hears.', 'Companies allowed more harmful content on user’s feeds, knowing their algorithms ran on outrage, BBC hears....', NULL, NULL, '1', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '2026-03-16 11:10:55', 'https://www.bbc.com/news/articles/cqj9kgxqjwjo?at_medium=RSS&at_campaign=rss', '2026-03-16 20:48:41', '2026-03-16 20:48:41', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('545', 'Three arrests after man found dead in wheelie bin', 'three-arrests-after-man-found-dead-in-wheelie-bin', 'Two men and a woman are detained on suspicion of murder following overnight arrests in Blackpool.', 'Two men and a woman are detained on suspicion of murder following overnight arrests in Blackpool....', NULL, NULL, '1', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '2026-03-16 19:42:28', 'https://www.bbc.com/news/articles/c5yk16v2lyro?at_medium=RSS&at_campaign=rss', '2026-03-16 20:48:41', '2026-03-16 20:48:41', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('546', 'Nursery worker sentenced to 30 years for raping children', 'nursery-worker-sentenced-to-30-years-for-raping-children', 'Nathan Bennett\'s abuse of two and three-year-old boys is \"every parent\'s nightmare\", a court hears.', 'Nathan Bennett\'s abuse of two and three-year-old boys is \"every parent\'s nightmare\", a court hears....', NULL, NULL, '1', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '2026-03-16 18:28:34', 'https://www.bbc.com/news/articles/ckgldr4mm8eo?at_medium=RSS&at_campaign=rss', '2026-03-16 20:52:24', '2026-03-16 20:52:24', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('547', 'From Creed to Sinners: Michael B Jordan\'s road to Oscars recognition', 'from-creed-to-sinners-michael-b-jordan-s-road-to-oscars-recognition', 'The 39-year-old has spent more than two decades acting and picked up the award for best actor at the Oscars.', 'The 39-year-old has spent more than two decades acting and picked up the award for best actor at the Oscars....', NULL, NULL, '1', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '2026-03-16 16:14:54', 'https://www.bbc.com/news/articles/cwyk0n1znpko?at_medium=RSS&at_campaign=rss', '2026-03-16 20:52:24', '2026-03-16 20:52:24', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('548', 'See the winners list in full', 'see-the-winners-list-in-full', 'Find out which films and stars have won the famous golden statuettes at the ceremony in Los Angeles.', 'Find out which films and stars have won the famous golden statuettes at the ceremony in Los Angeles....', NULL, NULL, '1', '1', 'published', 'manual', '0', '0', '0', '0.00', '0', '0', '2026-03-16 08:43:48', 'https://www.bbc.com/news/articles/c33jkvmzl4ko?at_medium=RSS&at_campaign=rss', '2026-03-16 20:52:24', '2026-03-16 20:52:24', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('550', 'Injuries, blown tyres and repair bills - frustration over \'spike\' in potholes', 'injuries-blown-tyres-and-repair-bills-frustration-over-spike-in-potholes', 'One woman who tripped over a \"dangerous\" pothole says she fears for the safety of other road users.', 'One woman who tripped over a \"dangerous\" pothole says she fears for the safety of other road users....', NULL, NULL, '1', '1', 'published', 'manual', '0', '2', '0', '0.00', '0', '0', '2026-03-16 11:11:28', 'https://www.bbc.com/news/articles/c363yylxjrxo?at_medium=RSS&at_campaign=rss', '2026-03-16 20:55:10', '2026-03-17 17:34:37', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('551', 'How US groups are driving a new generation of anti-abortion activism in the UK', 'how-us-groups-are-driving-a-new-generation-of-anti-abortion-activism-in-the-uk', 'The killing of Charlie Kirk galvanised a transatlantic campaign against abortion. But will it succeed in shifting Britain\'s pro-choice consensus?', 'The killing of Charlie Kirk galvanised a transatlantic campaign against abortion. But will it succeed in shifting Britain\'s pro-choice consensus?...', NULL, NULL, '1', '1', 'published', 'manual', '0', '1', '0', '0.00', '0', '0', '2026-03-16 05:31:48', 'https://www.bbc.com/news/articles/cx2dl5j0w23o?at_medium=RSS&at_campaign=rss', '2026-03-16 20:55:10', '2026-03-17 23:12:29', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('552', 'Legal analyst on Attorney General William Barr\'s testimony at House hearing', 'legal-analyst-on-attorney-general-william-barr-s-testimony-at-house-hearing', 'Attorney General William Bar appeared before the House Judiciary Committee for the first time Tuesday as protests continue to play out across the country. CBS News legal analyst Kim Wehle joined CBSN to discuss the impact of Barr\'s statements.\n\n<p><em><strong>Source:</strong> <a href=\"https://www.cbsnews.com/video/attorney-general-william-barr-testimony-at-house-hearing/\" target=\"_blank\" rel=\"noopener\">CBS News</a></em></p>\n\n<p><strong><a href=\"https://www.cbsnews.com/video/attorney-general-william-barr-testimony-at-house-hearing/\" target=\"_blank\" rel=\"noopener\">Read full story on CBS News</a></strong></p>', 'Attorney General William Bar appeared before the House Judiciary Committee for the first time Tuesday as protests continue to play out across the country. CBS News legal analyst Kim Wehle joined CBSN...', '', NULL, '1', '1', 'published', 'rss_import', '0', '0', '0', '0.00', '0', '0', '2020-07-29 12:26:33', 'https://www.cbsnews.com/video/attorney-general-william-barr-testimony-at-house-hearing/', '2026-03-17 23:33:56', '2026-03-17 23:52:35', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('553', 'As COVID-19 deaths rise, new controversy over hydroxychloroquine', 'as-covid-19-deaths-rise-new-controversy-over-hydroxychloroquine', 'Coronavirus cases and deaths keep rising in many states. CBS News\' Laura Podesta reports on the latest, and Dr. Dara Kass, an ER doctor and Yahoo News medical contributor, joined CBSN to discuss the latest figures, concerns about COVID-19\'s impact on the heart, and the controversy surrounding the drug hydroxychloroquine.\n\n<p><em><strong>Source:</strong> <a href=\"https://www.cbsnews.com/video/us-death-toll-from-coronavirus-nears-150000-hydroxychloroquine-heart/\" target=\"_blank\" rel=\"noopener\">CBS News</a></em></p>\n\n<p><strong><a href=\"https://www.cbsnews.com/video/us-death-toll-from-coronavirus-nears-150000-hydroxychloroquine-heart/\" target=\"_blank\" rel=\"noopener\">Read full story on CBS News</a></strong></p>', 'Coronavirus cases and deaths keep rising in many states. CBS News\' Laura Podesta reports on the latest, and Dr. Dara Kass, an ER doctor and Yahoo News medical contributor, joined CBSN to discuss the...', '', NULL, '1', '1', 'published', 'rss_import', '0', '0', '0', '0.00', '0', '0', '2020-07-29 12:25:05', 'https://www.cbsnews.com/video/us-death-toll-from-coronavirus-nears-150000-hydroxychloroquine-heart/', '2026-03-17 23:33:56', '2026-03-17 23:53:46', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('554', 'Hollywood faces financial crisis as studios delay release of summer blockbusters', 'hollywood-faces-financial-crisis-as-studios-delay-release-of-summer-blockbusters', 'The pandemic is wreaking havoc in Hollywood as most movie studios have paused production indefinitely. With studios repeatedly postponing the release of summer blockbusters, the entertainment industry is facing its biggest financial crisis yet. Axios media reporter Sara Fischer joins CBSN\'s Elaine Quijano with the details.\n\n<p><em><strong>Source:</strong> <a href=\"https://www.cbsnews.com/video/hollywood-faces-financial-crisis-as-studios-delay-release-of-summer-blockbusters/\" target=\"_blank\" rel=\"noopener\">CBS News</a></em></p>\n\n<p><strong><a href=\"https://www.cbsnews.com/video/hollywood-faces-financial-crisis-as-studios-delay-release-of-summer-blockbusters/\" target=\"_blank\" rel=\"noopener\">Read full story on CBS News</a></strong></p>', 'The pandemic is wreaking havoc in Hollywood as most movie studios have paused production indefinitely. With studios repeatedly postponing the release of summer blockbusters, the entertainment...', '', NULL, '1', '1', 'published', 'rss_import', '0', '0', '0', '0.00', '0', '0', '2020-07-29 12:15:03', 'https://www.cbsnews.com/video/hollywood-faces-financial-crisis-as-studios-delay-release-of-summer-blockbusters/', '2026-03-17 23:33:56', '2026-03-17 23:54:36', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('555', 'Lawmakers bid final farewell to late Congressman John Lewis', 'lawmakers-bid-final-farewell-to-late-congressman-john-lewis', 'The late civil rights icon and Congressman John Lewis left the U.S. Capitol for the final time. He will be buried in Atlanta after a funeral at the historic Ebenezer Baptist Church. CBS News political contributor Antjuan Seawright joins CBSN to discuss Lewsi\' legacy and how to continue in the fight for equality.\n\n<p><em><strong>Source:</strong> <a href=\"https://www.cbsnews.com/video/lawmakers-bid-final-farewell-to-late-congressman-john-lewis/\" target=\"_blank\" rel=\"noopener\">CBS News</a></em></p>\n\n<p><strong><a href=\"https://www.cbsnews.com/video/lawmakers-bid-final-farewell-to-late-congressman-john-lewis/\" target=\"_blank\" rel=\"noopener\">Read full story on CBS News</a></strong></p>', 'The late civil rights icon and Congressman John Lewis left the U.S. Capitol for the final time. He will be buried in Atlanta after a funeral at the historic Ebenezer Baptist Church. CBS News...', '', NULL, '1', '1', 'published', 'rss_import', '0', '0', '0', '0.00', '0', '0', '2020-07-29 12:15:03', 'https://www.cbsnews.com/video/lawmakers-bid-final-farewell-to-late-congressman-john-lewis/', '2026-03-17 23:33:56', '2026-03-17 23:54:12', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('556', 'Tech CEOs to testify at congressional antitrust hearing', 'tech-ceos-to-testify-at-congressional-antitrust-hearing', 'The CEOs of Facebook, Amazon, Google, and Apple are testifying on Capitol Hill today as lawmakers conduct an antitrust probe into the companies. CNET executive editor Roger Cheng joins CBSN to discuss what\'s at stake.\n\n<p><em><strong>Source:</strong> <a href=\"https://www.cbsnews.com/video/tech-ceos-to-testify-congress-antitrust-hearing/\" target=\"_blank\" rel=\"noopener\">CBS News</a></em></p>\n\n<p><strong><a href=\"https://www.cbsnews.com/video/tech-ceos-to-testify-congress-antitrust-hearing/\" target=\"_blank\" rel=\"noopener\">Read full story on CBS News</a></strong></p>', 'The CEOs of Facebook, Amazon, Google, and Apple are testifying on Capitol Hill today as lawmakers conduct an antitrust probe into the companies. CNET executive editor Roger Cheng joins CBSN to...', '', NULL, '1', '1', 'published', 'rss_import', '0', '0', '0', '0.00', '0', '0', '2020-07-29 12:15:02', 'https://www.cbsnews.com/video/tech-ceos-to-testify-congress-antitrust-hearing/', '2026-03-17 23:33:57', '2026-03-17 23:52:13', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('557', 'Middle East crisis live: Trump continues criticism of Nato allies over resistance to get involved in war on Iran - The Guardian', 'middle-east-crisis-live-trump-continues-criticism-of-nato-allies-over-resistance-to-get-involved-in-war-on-iran-the-guardian', '<ol><li><a href=\"https://news.google.com/rss/articles/CBMi6AFBVV95cUxQTDRXZmp2NV93VjdiNXFHX1hVT3RUOVZZaENaX2g2NmNGOFFTeDZFb3ZYdnVrQjE0YmdtaTZxc0JJQ2l4UzFFQTh5a2UwTm5xd0hOZDJfZzJWdS1MUjZkNVM0OTU3cm1OQnozUGc2WU5BakhCVVBzUkZGMEQyOU5JeV9nYm44V0hNeDZzUmtMSHQxZVVvZlA5OUlFVE5qYkd3dDVJRXRCR3o1ME5rSDlLOEwzX1V2QUJGYlJncFZDZWt6cXJKX0hMbUczV1V1b0hMRElIU0RHemxEU205YU5sbDZza2lwamQ2?oc=5\" target=\"_blank\">Middle East crisis live: Trump continues criticism of Nato allies over resistance to get involved in war on Iran</a>&nbsp;&nbsp;<font color=\"#6f6f6f\">The Guardian</font></li><li><a href=\"https://news.google.com/rss/articles/CBMivgFBVV95cUxQbUtXWnY5QlFvUlU4V1QwTGlRZV9MMGpKN2Z4RGpzalNxN0RxNzJvRWM4UVNody1vcnNFbWgzWnpUYW5ZdFdFLXZvT2ctOENKeGFZNlg1ZkNHNllQbEkyc1MxdlUzOG81ZGxfQjdQSTZyTGJWZFRVZnpFVDc3Vzd0MXg0MF9BY0NfQjhIRXdTRHpja1lLWTl1SjdhSnVYc0t2RnZyS3pHWmRMYmljeERUNGhUQUZpME9PX0RiRVN30gHDAUFVX3lxTE5SQWdhOHFuenA4U2VqdnhvSldQMXRPNkE4T1M5SUJKbVhKWW02U1ZTSWwtc3NPaXRUYlJRal94d1E2QUE1c05VWXhNZWkza2lQUmswcnpEZXNlUlZudmptd1dfUWxPX2ZKZ2RZbkExTnZfY0dwVlZLVC03cW4tQ3FieUpib0dDYXk0S3QyYVQwUUk3NlQyTGhPX2lIT3dyUFBFbk1xNmx6eUJXZjd0MXJzYmhlV2xEd2hHbmFxOUNRTkFxVQ?oc=5\" target=\"_blank\">Iran war updates: Trump chastises nations for lack of Hormuz ‘enthusiasm’</a>&nbsp;&nbsp;<font color=\"#6f6f6f\">Al Jazeera</font></li><li><a href=\"https://news.google.com/rss/articles/CBMiSEFVX3lxTE9fY1hhUG5YOG85VlJqSTFLNEg5aW96dFZiZkVVT3pQVFNmT0Z5bjVZcVRRVE5GNHNCRy15cUpSbzZQV3Z4UmotTNIBTkFVX3lxTE9XaVp4WF9PVXF1WE1QcWhfeER2a3FjcXZBbXdKSEdibnEwMVFHQnpKSFJLNFJKaWZPaFBPYndyNkMxcWdZMjB0b2xiSGExZw?oc=5\" target=\"_blank\">Trump upset as key US partners shun call for Hormuz warship escorts</a>&nbsp;&nbsp;<font color=\"#6f6f6f\">Dawn</font></li><li><a href=\"https://news.google.com/rss/articles/CBMieEFVX3lxTE5HNGtIbzNoSUpDVkZvU0VsaWdvVVlfU0xRcXpvNHpCYmtoMXZKQmxtVnpCV3NiU0JMOFVfZTBuYVNpUnZfRWlFck5LS0NCb0ozNG5kdi1zbDl0OUZmaGVWaW01WnlRd0pUNjdDVTVyVlpaU3pvUEU0Nw?oc=5\" target=\"_blank\">Day 17 of Middle East conflict — Trump criticizes nations not committing to assist with Hormuz strait crisis</a>&nbsp;&nbsp;<font color=\"#6f6f6f\">CNN</font></li><li><a href=\"https://news.google.com/rss/articles/CBMicEFVX3lxTE9uNWFyQXhSdzNYWUdGQzNsTjBzUEtCb0NMRE5oUnBSUnlNdG12TWg3TkJvcDNVQ2lEU0NZY3dOaDNhdUk0TEhaNFpwSDBwQXExbGVGWURrV2NaM0dDMHZJaFF1dWk3d0tvWGJkclNKQy0?oc=5\" target=\"_blank\">Donald Trump warns Nato faces ‘very bad future’ if allies fail to help US in Iran</a>&nbsp;&nbsp;<font color=\"#6f6f6f\">Financial Times</font></li></ol>\n\n<p><em><strong>Source:</strong> <a href=\"https://news.google.com/rss/articles/CBMi6AFBVV95cUxQTDRXZmp2NV93VjdiNXFHX1hVT3RUOVZZaENaX2g2NmNGOFFTeDZFb3ZYdnVrQjE0YmdtaTZxc0JJQ2l4UzFFQTh5a2UwTm5xd0hOZDJfZzJWdS1MUjZkNVM0OTU3cm1OQnozUGc2WU5BakhCVVBzUkZGMEQyOU5JeV9nYm44V0hNeDZzUmtMSHQxZVVvZlA5OUlFVE5qYkd3dDVJRXRCR3o1ME5rSDlLOEwzX1V2QUJGYlJncFZDZWt6cXJKX0hMbUczV1V1b0hMRElIU0RHemxEU205YU5sbDZza2lwamQ2?oc=5\" target=\"_blank\" rel=\"noopener\">Google News</a></em></p>\n\n<p><strong><a href=\"https://news.google.com/rss/articles/CBMi6AFBVV95cUxQTDRXZmp2NV93VjdiNXFHX1hVT3RUOVZZaENaX2g2NmNGOFFTeDZFb3ZYdnVrQjE0YmdtaTZxc0JJQ2l4UzFFQTh5a2UwTm5xd0hOZDJfZzJWdS1MUjZkNVM0OTU3cm1OQnozUGc2WU5BakhCVVBzUkZGMEQyOU5JeV9nYm44V0hNeDZzUmtMSHQxZVVvZlA5OUlFVE5qYkd3dDVJRXRCR3o1ME5rSDlLOEwzX1V2QUJGYlJncFZDZWt6cXJKX0hMbUczV1V1b0hMRElIU0RHemxEU205YU5sbDZza2lwamQ2?oc=5\" target=\"_blank\" rel=\"noopener\">Read full story on Google News</a></strong></p>', 'Middle East crisis live: Trump continues criticism of Nato allies over resistance to get involved in war on Iran&nbsp;&nbsp;The GuardianIran war updates: Trump chastises nations for lack of Hormuz...', '', NULL, '1', '1', 'published', 'rss_import', '0', '0', '0', '0.00', '0', '0', '2026-03-17 16:39:00', 'https://news.google.com/rss/articles/CBMi6AFBVV95cUxQTDRXZmp2NV93VjdiNXFHX1hVT3RUOVZZaENaX2g2NmNGOFFTeDZFb3ZYdnVrQjE0YmdtaTZxc0JJQ2l4UzFFQTh5a2UwTm5xd0hOZDJfZzJWdS1MUjZkNVM0OTU3cm1OQnozUGc2WU5BakhCVVBzUkZGMEQyOU5JeV9nYm44V0hNeDZzUmtMSHQxZVVvZlA5OUlFVE5qYkd3dDVJRXRCR3o1ME5rSDlLOEwzX1V2QUJGYlJncFZDZWt6cXJKX0hMbUczV1V1b0hMRElIU0RHemxEU205YU5sbDZza2lwamQ2?oc=5', '2026-03-17 23:34:48', '2026-03-17 23:44:47', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('558', 'Israel Claims Killing Of Iran\'s Wartime \'Khamenei\'. Who Was Ali Larijani? - NDTV', 'israel-claims-killing-of-iran-s-wartime-khamenei-who-was-ali-larijani-ndtv', '<ol><li><a href=\"https://news.google.com/rss/articles/CBMihwJBVV95cUxPV1pfVjZXd1R5amZDQV9SRnMtREYxelhURXV5YW9DbHFVY0F4LTc0aVhjN19PQ1h2QkxjdzJyQXY1Tk1vLUJrQ2c4MmVNRGJPX1cza00tQVh5eDdFX003QUhfMjBldHhJbDJuQWVFQ1BtczVLMWg3MExIOWRlNjV4OVVVSFV6WmtyenVsSkpDNm9XeEJYWFdqVDFORm5aU2NhcXpfeUVNR0tnQkZseFdfdzh6cG53WlI3dEQ2dV8zY1ZhbExKclIzTXEtam9YUDl1Uko3ZlF5Ny1aRlJ2Z2E1eEdxUUhnSjhCc1JPX054RjFxaVRVSzR3OFdRRTlLR0kwQ09RUGNBMA?oc=5\" target=\"_blank\">Israel Claims Killing Of Iran\'s Wartime \'Khamenei\'. Who Was Ali Larijani?</a>&nbsp;&nbsp;<font color=\"#6f6f6f\">NDTV</font></li><li><a href=\"https://news.google.com/rss/articles/CBMiwgFBVV95cUxNYXp0TnBPNzRiRlRHM29HZm9LWFNKcV9Cc25LeHdXdDYzVHRDZzA1THVVNUx0U0VDRWlEQ0NnTlhhU0RyMGdlUzF6aVdTcFBKZkpwRjZYOHl2Rl9ueWl5MTZSbHI0MDhjQTRDQ1JYcW5DbUdtaHhGSi1HOTBoN3JNS0NVUC1WQUx6Yjllc3U3c1ZGY2VPQjhuYTFGcWRJSTdaNFZ4SjNPTzl0MmpnNkR3R09SOTRYdXdaZFFPSEFBUTNQUdIBxwFBVV95cUxQTzdYX1B5SnpBUTRUNVlkcUs3clBJSFo5NjhQMFZXNXFDUWNRVEJjYmM2MTJpaGYxVlpnYkJ1ZHpNS1JhT1l0WTR4TnNOSlUwTmVTUDVMYmtaY3RJbHJMWWxIOU1WN1B5d2R0THFPYkhFVk04TXhSOUdnQkdJTXRVN3EzdER2ZU5ub3NienFXWGtOLUFTWHhfTUs3d1VfMUVvWlZVXzBnNURIZHpRU0NyeVQ0aTFYN1NwMTVWc3IxTWg2Z1lMWjBz?oc=5\" target=\"_blank\">Iran war live: Israel says Iran’s security chief Ali Larijani killed</a>&nbsp;&nbsp;<font color=\"#6f6f6f\">Al Jazeera</font></li><li><a href=\"https://news.google.com/rss/articles/CBMisAFBVV95cUxOUEg5OU5CQkZSYng5Nlo1VW8yUHhIc1JOU2dRY0pZYlotdTFSZ1RQRXVxN21KbDA3Vm0wb1pGV1hDT3V6cndRY1kzbUhpcUFFaURQNUFkT25uYkJyYTR2TWJ6YlFkRDYybWFIbTBnN1RpQ0JkZEhTWEJoLVlfOW52UzVRcHpEaENvMFFYUmdIM3lLRzRFVmZ0MHpjU3AxTGNZU1k4RnZQa1JSTVBzV3dhaw?oc=5\" target=\"_blank\">Pivotal Iran leader Ali Larijani killed in airstrike, Israel says</a>&nbsp;&nbsp;<font color=\"#6f6f6f\">The Guardian</font></li><li><a href=\"https://news.google.com/rss/articles/CBMiuwFBVV95cUxOTVhHMjJPUHhnUVdGdEtLWTZ3MUgwanJydGkyZkJ5bWI3WnFKVS0zREVmRGF4RzBhU1FUT1VEWjJLUXZ5VFBRaUtzdGtONGY3MHdlZDV2SkU5Yzc4cWEwdmJuY2sxSVl5NEtITXpRMFotTHltQkRIX2ZobHZVQ1NmZFp2bExPbjhuZW1WUm1WLW5DMlBkVjVNYjhWMWE4emR4MnhNb3k4ajlVU1l3YWtuQnY4eng4bWI0Zkhv?oc=5\" target=\"_blank\">Iran rejects de-escalation offers; Israel says it kills Iranian security chief</a>&nbsp;&nbsp;<font color=\"#6f6f6f\">Reuters</font></li><li><a href=\"https://news.google.com/rss/articles/CBMi0AFBVV95cUxQUU0xVWhNNkpQWFZPZ2pwZ2p0dXo1X0JQNDlYYTVyS0FLMU9HOFZrc01kSGxNWmFneDBuSzE2M0ltdnB3VnZFYjFxRm9nOFRPbGl4ZjBjQ2hGTjljU2xRRlY1NXpwT3lySzhhWGkyc2ZXOTdfWjI1TnVUZ3pHNmpQSllzeFB4SXVJU1lWT3ZUNFFjZHpZaVgxY0ZrVlZVSHB0Vk1OaDcxV0o2WEdMNF9BUFJ2S0hTeV9LTVQ1em54SFZvWUhSUHE2WG1NMVNLck1M0gFOQVVfeXFMTjJadjRaQ1ZndHBHU0ZlZGRfV3I0WkVmYkNualdWN1N4TF82Q054QmFiTF9fX0l0amhaSktrblFoamxRaExxdVFIeEpseVJB?oc=5\" target=\"_blank\">War Diary Day 18: Diplomatic space appears narrower amid Israeli claim of killing Iran’s security chief</a>&nbsp;&nbsp;<font color=\"#6f6f6f\">Dawn</font></li></ol>\n\n<p><em><strong>Source:</strong> <a href=\"https://news.google.com/rss/articles/CBMihwJBVV95cUxPV1pfVjZXd1R5amZDQV9SRnMtREYxelhURXV5YW9DbHFVY0F4LTc0aVhjN19PQ1h2QkxjdzJyQXY1Tk1vLUJrQ2c4MmVNRGJPX1cza00tQVh5eDdFX003QUhfMjBldHhJbDJuQWVFQ1BtczVLMWg3MExIOWRlNjV4OVVVSFV6WmtyenVsSkpDNm9XeEJYWFdqVDFORm5aU2NhcXpfeUVNR0tnQkZseFdfdzh6cG53WlI3dEQ2dV8zY1ZhbExKclIzTXEtam9YUDl1Uko3ZlF5Ny1aRlJ2Z2E1eEdxUUhnSjhCc1JPX054RjFxaVRVSzR3OFdRRTlLR0kwQ09RUGNBMA?oc=5\" target=\"_blank\" rel=\"noopener\">Google News</a></em></p>\n\n<p><strong><a href=\"https://news.google.com/rss/articles/CBMihwJBVV95cUxPV1pfVjZXd1R5amZDQV9SRnMtREYxelhURXV5YW9DbHFVY0F4LTc0aVhjN19PQ1h2QkxjdzJyQXY1Tk1vLUJrQ2c4MmVNRGJPX1cza00tQVh5eDdFX003QUhfMjBldHhJbDJuQWVFQ1BtczVLMWg3MExIOWRlNjV4OVVVSFV6WmtyenVsSkpDNm9XeEJYWFdqVDFORm5aU2NhcXpfeUVNR0tnQkZseFdfdzh6cG53WlI3dEQ2dV8zY1ZhbExKclIzTXEtam9YUDl1Uko3ZlF5Ny1aRlJ2Z2E1eEdxUUhnSjhCc1JPX054RjFxaVRVSzR3OFdRRTlLR0kwQ09RUGNBMA?oc=5\" target=\"_blank\" rel=\"noopener\">Read full story on Google News</a></strong></p>', 'Israel Claims Killing Of Iran\'s Wartime \'Khamenei\'. Who Was Ali Larijani?&nbsp;&nbsp;NDTVIran war live: Israel says Iran’s security chief Ali Larijani killed&nbsp;&nbsp;Al JazeeraPivotal Iran...', '', NULL, '1', '1', 'published', 'rss_import', '0', '0', '0', '0.00', '0', '0', '2026-03-17 13:59:38', 'https://news.google.com/rss/articles/CBMihwJBVV95cUxPV1pfVjZXd1R5amZDQV9SRnMtREYxelhURXV5YW9DbHFVY0F4LTc0aVhjN19PQ1h2QkxjdzJyQXY1Tk1vLUJrQ2c4MmVNRGJPX1cza00tQVh5eDdFX003QUhfMjBldHhJbDJuQWVFQ1BtczVLMWg3MExIOWRlNjV4OVVVSFV6WmtyenVsSkpDNm9XeEJYWFdqVDFORm5aU2NhcXpfeUVNR0tnQkZseFdfdzh6cG53WlI3dEQ2dV8zY1ZhbExKclIzTXEtam9YUDl1Uko3ZlF5Ny1aRlJ2Z2E1eEdxUUhnSjhCc1JPX054RjFxaVRVSzR3OFdRRTlLR0kwQ09RUGNBMA?oc=5', '2026-03-17 23:34:48', '2026-03-17 23:46:42', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('559', 'Afghanistan: Pakistan air strike kills at least 100 at Kabul drug rehab centre - BBC', 'afghanistan-pakistan-air-strike-kills-at-least-100-at-kabul-drug-rehab-centre-bbc', '<ol><li><a href=\"https://news.google.com/rss/articles/CBMiWkFVX3lxTE8teDhMT016TW80TUFjeGdwSVB3bDdNeE1BNHJQOF9hTlZaejFZbV91ZlQ3VTJxNGVXREc0N05CNm11MHR5b2ZJWjRscWlWOEwwOWRUcWFoQjVVQQ?oc=5\" target=\"_blank\">Afghanistan: Pakistan air strike kills at least 100 at Kabul drug rehab centre</a>&nbsp;&nbsp;<font color=\"#6f6f6f\">BBC</font></li><li><a href=\"https://news.google.com/rss/articles/CBMitgFBVV95cUxPcDFNa1RLM3hZZUxsQ1YyMHJxZmhEWHBBa0ROVXZNaGFDYzUtMmNqVnhMTTAxWlpPWEdvMFR5clRSVHkxdEVMZVpTOEFURmIxN3E2NWRkVnBVNmFQcHRQbnVQMGlxZWtBbW9uel9Zby01WTEwcFJneXNVeW9EZk1tb1JCb21CdmRaX3dkc09GXzcwcUNhbE5nYmJNZHNnR0dKcnlWZnRFbEhQOXF1UUZHYWd0bGFGUdIBuwFBVV95cUxNRE04cVNIYURoeDNZeFpaNG5ENDNrLS1xVWFrU25OU2thRFRBQktYMHNvR1hxMDl3VjhYS1hoSmpJcmswbHhNLW82YUNtclB2akJYQnBCNC1jUTdDY1dpT3JwYmF5ZU9jTnBOSmhEdHZTWEpVVW82aUo1OWVIWjB5VDctNm94eURaU1ltbDY4RHZBalF1TWx0WFJ5LTJIbncwM3lzbl81bDJDdWxST0VXNFdVZ0ZyZjR3NlUw?oc=5\" target=\"_blank\">Afghanistan accuses Pakistan of killing 400 in attack on Kabul hospital</a>&nbsp;&nbsp;<font color=\"#6f6f6f\">Al Jazeera</font></li><li><a href=\"https://news.google.com/rss/articles/CBMiSEFVX3lxTE5KUzhLcEJGM1NPZUFNeVBtVzdueS1VSmpjbWVIUUN0c3YxYjB6ckxLNmZwZklHQmIxeE1zeFdLb2V4R1hkczJvQ9IBTkFVX3lxTE9SNFFCODJKVjYxX3lJLUhNM3FqRm44MHVhS1ZkMENOOHR6RlJOcGpEb2ZNejhoWlpvOEF1WWZ0X1NWejNvS0tEVUVDZm9XZw?oc=5\" target=\"_blank\">Information ministry rubbishes Afghan Taliban’s claims of hospital being hit in Kabul</a>&nbsp;&nbsp;<font color=\"#6f6f6f\">Dawn</font></li><li><a href=\"https://news.google.com/rss/articles/CBMiswFBVV95cUxOTE9TcEgyNnZHcWVlMUZlR0dlcXJyOERiMXRfTURzeEpRUk1TbFpvbmxoWWFacWp3ZW1ibnpTbVNxVXZSYW1vRVNzR0Z6UUdublR3OGZQUzdHdGZodGM0MTJaZkxpX2xVMUhoRTM1M2dFUVRTYnR1ekFndkZ5ZnhFMmlkbTZOQjRiSk5Ib1NfZTFucWROb01xTTY3d3hlTnRzeDMxWVZ1MklfNUpvelowanQ4cw?oc=5\" target=\"_blank\">From sponsor to enemy: What\'s behind Pakistan\'s attack on Afghan Taliban?</a>&nbsp;&nbsp;<font color=\"#6f6f6f\">Reuters</font></li><li><a href=\"https://news.google.com/rss/articles/CBMihAFBVV95cUxOTnVLUHhYTFdBZFFIWFRuNHFDcENYOFdXSnhFRWNjYnZmRmpTd2hPOGZXdy1WYno0TDVqVHRkeWpLbi16WHpJMHlSY3NNdmVCdzY0eldIRkxsOU5GaWpyNFdMN3F4UkZDdFpNZ0lFN0c0MENHVHRJNFlyVTdqVWc3azBtRGM?oc=5\" target=\"_blank\">Afghanistan says 400 killed in Pakistani strike on Kabul hospital</a>&nbsp;&nbsp;<font color=\"#6f6f6f\">Financial Times</font></li></ol>\n\n<p><em><strong>Source:</strong> <a href=\"https://news.google.com/rss/articles/CBMiWkFVX3lxTE8teDhMT016TW80TUFjeGdwSVB3bDdNeE1BNHJQOF9hTlZaejFZbV91ZlQ3VTJxNGVXREc0N05CNm11MHR5b2ZJWjRscWlWOEwwOWRUcWFoQjVVQQ?oc=5\" target=\"_blank\" rel=\"noopener\">Google News</a></em></p>\n\n<p><strong><a href=\"https://news.google.com/rss/articles/CBMiWkFVX3lxTE8teDhMT016TW80TUFjeGdwSVB3bDdNeE1BNHJQOF9hTlZaejFZbV91ZlQ3VTJxNGVXREc0N05CNm11MHR5b2ZJWjRscWlWOEwwOWRUcWFoQjVVQQ?oc=5\" target=\"_blank\" rel=\"noopener\">Read full story on Google News</a></strong></p>', 'Afghanistan: Pakistan air strike kills at least 100 at Kabul drug rehab centre&nbsp;&nbsp;BBCAfghanistan accuses Pakistan of killing 400 in attack on Kabul hospital&nbsp;&nbsp;Al JazeeraInformation...', '', NULL, '1', '1', 'published', 'rss_import', '0', '0', '0', '0.00', '0', '0', '2026-03-17 12:38:49', 'https://news.google.com/rss/articles/CBMiWkFVX3lxTE8teDhMT016TW80TUFjeGdwSVB3bDdNeE1BNHJQOF9hTlZaejFZbV91ZlQ3VTJxNGVXREc0N05CNm11MHR5b2ZJWjRscWlWOEwwOWRUcWFoQjVVQQ?oc=5', '2026-03-17 23:34:48', '2026-03-17 23:44:54', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('560', 'Deputy PM reviews fuel stocks, expresses satisfaction over supply - RADIO PAKISTAN', 'deputy-pm-reviews-fuel-stocks-expresses-satisfaction-over-supply-radio-pakistan', '<ol><li><a href=\"https://news.google.com/rss/articles/CBMiogFBVV95cUxPd2QtWVVuVXhoYVJMaG14RmZBUGdvOHROU29HUXRQZ0NMM2dyQk9TMHdpQ25BSHFSd3pTd05rME9zLURFdnZsNVlPeFEzaDR4d0g5SktjV2k4c2ZyZWVoamdNa1VHNUlWQi1MTzZCWHpSOWlLalNaTm5tNGdFYVR4SnFkSGF6UFI1RVRpM05FTHhPUWJrektPS0JkbkZ4cGpTV1E?oc=5\" target=\"_blank\">Deputy PM reviews fuel stocks, expresses satisfaction over supply</a>&nbsp;&nbsp;<font color=\"#6f6f6f\">RADIO PAKISTAN</font></li><li><a href=\"https://news.google.com/rss/articles/CBMiSEFVX3lxTE50b2VTWFY1eUoxVFJGcVVsdG43cXNYM0M1ZEZYM2hGbnlHbFN2M1FVRjBXTVVwYUlYeWFaa3Z3djVFR2lCa091V9IBTkFVX3lxTE9zaDhRS0U2Z1lmVGVGNFMxZ3RJNDRZNDV5M1VJRERmclJsWWh5XzFBa1l1ekZYOW9hNFJvNExJSmZZU19RNEo0UkY1WkpGZw?oc=5\" target=\"_blank\">Risk to stability</a>&nbsp;&nbsp;<font color=\"#6f6f6f\">Dawn</font></li><li><a href=\"https://news.google.com/rss/articles/CBMiugFBVV95cUxObzdBMDhNMVNqVzBBb00xVXRmT3VoX19rT1EzMGdOaURQek9wWkdtOS04WE1rcmtDUDA4LUlXVE1YMG1Bc3p4RFFnQU5vUU5oTGtSVjVEb2lLaEpnbU9nZG1RTWp5RHd6Q20zODFJeXRRaUpGSlJDME1VdU1ETHJZTi14OXd5R0NqZTUzdTQyajVjXzUyNVQ3SGozdHJ1aDlCanVpMzFUbkJVREx2UEZFY3NoQnlUek5vV2fSAcIBQVVfeXFMTkUtUVZqbzdna3RfN3JTc3VUS2pta1B1cG13YzNEYkFpU2JHYXZhTTBKQklQS01ZR05RM2lYeTUzZDNyQ0pxb3hCaVFJeW9ORUlfNzlJTl9ZS3hnWEM1UFdzVzZCcEJGRTB2NFBaMHlSSFB0cnlpcVNkQjkyNGotajRmRzNDNTIwdlVhM0tmRXl1cUlIU3NyWXdlTFpLdWg3a1BwY1pNNEFIbG1sU1ZnMUhMaFAzV1B1SWlKTFRsQ2ZxS1E?oc=5\" target=\"_blank\">Pakistan has petrol for 27 days and diesel reserves for 21 days, Senate panel informed</a>&nbsp;&nbsp;<font color=\"#6f6f6f\">The Express Tribune</font></li><li><a href=\"https://news.google.com/rss/articles/CBMiX0FVX3lxTE1BeXdVVzFBTm9UeS1NS1ZyZFdfeGJsV08tV0Q4SmM1MFE3U084N1g4bldqa3NqMlh0bk9fZDU2Y2pWNjM0YWJTUWQtamJBXzY3NGZxb19tZFU2OUpvOWcw?oc=5\" target=\"_blank\">Rising oil prices jolt Pakistan\'s fragile economy</a>&nbsp;&nbsp;<font color=\"#6f6f6f\">Geo News</font></li><li><a href=\"https://news.google.com/rss/articles/CBMioAFBVV95cUxQTHFDaFYzOGp4VU5rV2tWWXNscWtTX0JqVk1zamxfRGlXNHEwV0NQT2V3MFgtd1N5S2swZlRPcTViOXN3d3FrVzVobmVUcXJzQ2VpbURoTkZhZmwtU0NaT05vUXpxLUNORWJYSWo1VV96NVZxQ2dCZWFKR3ZabXBXUjlYcWhjQ3NydGkyYkVWeS1veUVVYm5pOS1Nc29kZ1hY0gFWQVVfeXFMT1JkcHQxRkxnVHMzbDNFTGpadlJYOHE4N2tUWWdzcW9IR19zMTdfN0hoWUNqaWpXV1pqYUlhTzVBdGNDWnhydGI5TGJSNDNXbWtyOEFJOXc?oc=5\" target=\"_blank\">ME conflict: FPCCI urges govt to help protect trade, industry</a>&nbsp;&nbsp;<font color=\"#6f6f6f\">Business Recorder</font></li></ol>\n\n<p><em><strong>Source:</strong> <a href=\"https://news.google.com/rss/articles/CBMiogFBVV95cUxPd2QtWVVuVXhoYVJMaG14RmZBUGdvOHROU29HUXRQZ0NMM2dyQk9TMHdpQ25BSHFSd3pTd05rME9zLURFdnZsNVlPeFEzaDR4d0g5SktjV2k4c2ZyZWVoamdNa1VHNUlWQi1MTzZCWHpSOWlLalNaTm5tNGdFYVR4SnFkSGF6UFI1RVRpM05FTHhPUWJrektPS0JkbkZ4cGpTV1E?oc=5\" target=\"_blank\" rel=\"noopener\">Google News</a></em></p>\n\n<p><strong><a href=\"https://news.google.com/rss/articles/CBMiogFBVV95cUxPd2QtWVVuVXhoYVJMaG14RmZBUGdvOHROU29HUXRQZ0NMM2dyQk9TMHdpQ25BSHFSd3pTd05rME9zLURFdnZsNVlPeFEzaDR4d0g5SktjV2k4c2ZyZWVoamdNa1VHNUlWQi1MTzZCWHpSOWlLalNaTm5tNGdFYVR4SnFkSGF6UFI1RVRpM05FTHhPUWJrektPS0JkbkZ4cGpTV1E?oc=5\" target=\"_blank\" rel=\"noopener\">Read full story on Google News</a></strong></p>', 'Deputy PM reviews fuel stocks, expresses satisfaction over supply&nbsp;&nbsp;RADIO PAKISTANRisk to stability&nbsp;&nbsp;DawnPakistan has petrol for 27 days and diesel reserves for 21 days, Senate...', '', NULL, '1', '1', 'published', 'rss_import', '0', '1', '0', '0.00', '0', '0', '2026-03-17 17:29:06', 'https://news.google.com/rss/articles/CBMiogFBVV95cUxPd2QtWVVuVXhoYVJMaG14RmZBUGdvOHROU29HUXRQZ0NMM2dyQk9TMHdpQ25BSHFSd3pTd05rME9zLURFdnZsNVlPeFEzaDR4d0g5SktjV2k4c2ZyZWVoamdNa1VHNUlWQi1MTzZCWHpSOWlLalNaTm5tNGdFYVR4SnFkSGF6UFI1RVRpM05FTHhPUWJrektPS0JkbkZ4cGpTV1E?oc=5', '2026-03-17 23:34:48', '2026-03-17 23:45:25', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('561', 'Iran’s supreme leader rejects ceasefire proposals with US - Daily Times', 'iran-s-supreme-leader-rejects-ceasefire-proposals-with-us-daily-times', '<a href=\"https://news.google.com/rss/articles/CBMilgFBVV95cUxQdnF0UWhEN2lmTlVpQVFRckU0RmY2MFNzb3I0bGZ5NVltbXFIc25rdlNWMXFsWGx5WVRwQXo4QzA3eUtGM3J4UVE4LXVTdHdMNlAwVnNDWTFwR0tFbHlvY20zLWZnZFY1ZnAyM1JxV0FGWVpSeHdxbWRDdUlSM01tVzN1ZklFX1NNWDBjYW9Dcjg0eF9kRnc?oc=5\" target=\"_blank\">Iran’s supreme leader rejects ceasefire proposals with US</a>&nbsp;&nbsp;<font color=\"#6f6f6f\">Daily Times</font><strong><a href=\"https://news.google.com/stories/CAAqNggKIjBDQklTSGpvSmMzUnZjbmt0TXpZd1NoRUtEd2pucjViU0VCRW5uR2N3Q0d3ZmNDZ0FQAQ?hl=en-PK&gl=PK&ceid=PK:en&oc=5\" target=\"_blank\">View Full coverage on Google News</a></strong>\n\n<p><em><strong>Source:</strong> <a href=\"https://news.google.com/rss/articles/CBMilgFBVV95cUxQdnF0UWhEN2lmTlVpQVFRckU0RmY2MFNzb3I0bGZ5NVltbXFIc25rdlNWMXFsWGx5WVRwQXo4QzA3eUtGM3J4UVE4LXVTdHdMNlAwVnNDWTFwR0tFbHlvY20zLWZnZFY1ZnAyM1JxV0FGWVpSeHdxbWRDdUlSM01tVzN1ZklFX1NNWDBjYW9Dcjg0eF9kRnc?oc=5\" target=\"_blank\" rel=\"noopener\">Google News</a></em></p>\n\n<p><strong><a href=\"https://news.google.com/rss/articles/CBMilgFBVV95cUxQdnF0UWhEN2lmTlVpQVFRckU0RmY2MFNzb3I0bGZ5NVltbXFIc25rdlNWMXFsWGx5WVRwQXo4QzA3eUtGM3J4UVE4LXVTdHdMNlAwVnNDWTFwR0tFbHlvY20zLWZnZFY1ZnAyM1JxV0FGWVpSeHdxbWRDdUlSM01tVzN1ZklFX1NNWDBjYW9Dcjg0eF9kRnc?oc=5\" target=\"_blank\" rel=\"noopener\">Read full story on Google News</a></strong></p>', 'Iran’s supreme leader rejects ceasefire proposals with US&nbsp;&nbsp;Daily TimesView Full coverage on Google News', '', NULL, '1', '1', 'published', 'rss_import', '0', '0', '0', '0.00', '0', '0', '2026-03-17 11:27:43', 'https://news.google.com/rss/articles/CBMilgFBVV95cUxQdnF0UWhEN2lmTlVpQVFRckU0RmY2MFNzb3I0bGZ5NVltbXFIc25rdlNWMXFsWGx5WVRwQXo4QzA3eUtGM3J4UVE4LXVTdHdMNlAwVnNDWTFwR0tFbHlvY20zLWZnZFY1ZnAyM1JxV0FGWVpSeHdxbWRDdUlSM01tVzN1ZklFX1NNWDBjYW9Dcjg0eF9kRnc?oc=5', '2026-03-17 23:34:48', '2026-03-17 23:42:48', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('562', 'She\'s won 24 Paralympic medals. But Oksana Masters wants to talk about times she lost', 'she-s-won-24-paralympic-medals-but-oksana-masters-wants-to-talk-about-times-she-lost', 'Oksana Masters leaves Italy with five new para Nordic skiing medals, extending her reign as the most decorated U.S. Winter Paralympian. She competes in summer sports too and is already eyeing LA 2028.\n\n<p><em><strong>Source:</strong> <a href=\"https://www.npr.org/2026/03/17/nx-s1-5749163/oksana-masters-2026-winter-paralympics-medals\" target=\"_blank\" rel=\"noopener\">NPR News</a></em></p>\n\n<p><strong><a href=\"https://www.npr.org/2026/03/17/nx-s1-5749163/oksana-masters-2026-winter-paralympics-medals\" target=\"_blank\" rel=\"noopener\">Read full story on NPR News</a></strong></p>', 'Oksana Masters leaves Italy with five new para Nordic skiing medals, extending her reign as the most decorated U.S. Winter Paralympian. She competes in summer sports too and is already eyeing LA 2028.', '', NULL, '1', '1', 'published', 'rss_import', '0', '4', '0', '0.00', '0', '0', '2026-03-17 14:19:20', 'https://www.npr.org/2026/03/17/nx-s1-5749163/oksana-masters-2026-winter-paralympics-medals', '2026-03-17 23:34:49', '2026-03-17 23:38:17', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('563', 'Ukraine strings nets over cities as killer drones turn streets into war zones', 'ukraine-strings-nets-over-cities-as-killer-drones-turn-streets-into-war-zones', '<p>In eastern Ukraine, white nylon nets now stretch over roads and city streets, a low-tech defense against deadly FPV drones that dominate the battlefield and threaten civilians near the front line.</p>\r\n<p><em><strong>Source:</strong> <a href=\"https://www.npr.org/2026/03/17/nx-s1-5743446/russia-ukraine-war-nets-drones\" target=\"_blank\" rel=\"noopener\">NPR News</a></em></p>\r\n<p><strong><a href=\"https://www.npr.org/2026/03/17/nx-s1-5743446/russia-ukraine-war-nets-drones\" target=\"_blank\" rel=\"noopener\">Read full story on NPR News</a></strong></p>', 'In eastern Ukraine, white nylon nets now stretch over roads and city streets, a low-tech defense against deadly FPV drones that dominate the battlefield and threaten civilians near the front line.', 'uploads/news/69b9a00fae2ca.jpg', '', '1', '1', 'published', 'rss_import', '1', '2', '0', '0.00', '0', '0', '0000-00-00 00:00:00', 'https://www.npr.org/2026/03/17/nx-s1-5743446/russia-ukraine-war-nets-drones', '2026-03-17 23:34:49', '2026-03-17 23:49:17', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('564', 'Sparse evidence for cannabis to treat mental health conditions highlights research gap', 'sparse-evidence-for-cannabis-to-treat-mental-health-conditions-highlights-research-gap', 'A new analysis represents the largest effort yet to systematically parse all the data from high-quality clinical trials on cannabis and mental health. The evidence is lacking.\n\n<p><em><strong>Source:</strong> <a href=\"https://www.npr.org/2026/03/17/nx-s1-5750489/cannabis-research-mental-health\" target=\"_blank\" rel=\"noopener\">NPR News</a></em></p>\n\n<p><strong><a href=\"https://www.npr.org/2026/03/17/nx-s1-5750489/cannabis-research-mental-health\" target=\"_blank\" rel=\"noopener\">Read full story on NPR News</a></strong></p>', 'A new analysis represents the largest effort yet to systematically parse all the data from high-quality clinical trials on cannabis and mental health. The evidence is lacking.', '', NULL, '1', '1', 'published', 'rss_import', '0', '0', '0', '0.00', '0', '0', '2026-03-17 12:57:21', 'https://www.npr.org/2026/03/17/nx-s1-5750489/cannabis-research-mental-health', '2026-03-17 23:34:49', '2026-03-17 23:42:31', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('565', 'The Postal Service may be out of cash in 2027 without Congress\' help, postmaster says', 'the-postal-service-may-be-out-of-cash-in-2027-without-congress-help-postmaster-says', 'The U.S. Postal Service\'s leader says it is set to run out of money in less than a year and may have to stop deliveries because of declining mail volume and what USPS sees as burdensome requirements.\n\n<p><em><strong>Source:</strong> <a href=\"https://www.npr.org/2026/03/17/nx-s1-5750419/usps-running-out-of-money-postal-service-david-steiner\" target=\"_blank\" rel=\"noopener\">NPR News</a></em></p>\n\n<p><strong><a href=\"https://www.npr.org/2026/03/17/nx-s1-5750419/usps-running-out-of-money-postal-service-david-steiner\" target=\"_blank\" rel=\"noopener\">Read full story on NPR News</a></strong></p>', 'The U.S. Postal Service\'s leader says it is set to run out of money in less than a year and may have to stop deliveries because of declining mail volume and what USPS sees as burdensome requirements.', '', NULL, '1', '1', 'published', 'rss_import', '0', '0', '0', '0.00', '0', '0', '2026-03-17 10:31:59', 'https://www.npr.org/2026/03/17/nx-s1-5750419/usps-running-out-of-money-postal-service-david-steiner', '2026-03-17 23:34:49', '2026-03-17 23:42:27', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;
INSERT INTO `news` VALUES ('566', 'Joe Kent, a top counterterrorism official, resigns citing Iran war', 'joe-kent-a-top-counterterrorism-official-resigns-citing-iran-war', 'Kent said he \"cannot in good conscience\" back the Iran war. In his resignation letter, he says Iran \"posed no imminent threat to our nation.\"\n\n<p><em><strong>Source:</strong> <a href=\"https://www.npr.org/2026/03/17/nx-s1-5750426/joe-kent-counterterrorism-official-resigns-trump\" target=\"_blank\" rel=\"noopener\">NPR News</a></em></p>\n\n<p><strong><a href=\"https://www.npr.org/2026/03/17/nx-s1-5750426/joe-kent-counterterrorism-official-resigns-trump\" target=\"_blank\" rel=\"noopener\">Read full story on NPR News</a></strong></p>', 'Kent said he \"cannot in good conscience\" back the Iran war. In his resignation letter, he says Iran \"posed no imminent threat to our nation.\"', '', NULL, '1', '1', 'published', 'rss_import', '0', '0', '0', '0.00', '0', '0', '2026-03-17 10:29:26', 'https://www.npr.org/2026/03/17/nx-s1-5750426/joe-kent-counterterrorism-official-resigns-trump', '2026-03-17 23:34:49', '2026-03-17 23:42:18', '0.00', 'neutral', 'older', NULL, NULL, NULL, NULL, 'PENDING', NULL, NULL;


-- Table structure for `news_analytics`
CREATE TABLE `news_analytics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `news_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `views` int(11) DEFAULT 0,
  `unique_views` int(11) DEFAULT 0,
  `shares` int(11) DEFAULT 0,
  `comments` int(11) DEFAULT 0,
  `avg_read_time` int(11) DEFAULT 0,
  `bounce_rate` decimal(5,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_news_date` (`news_id`,`date`),
  KEY `idx_analytics_news` (`news_id`),
  KEY `idx_analytics_date` (`date`),
  KEY `idx_analytics_views` (`views`),
  CONSTRAINT `news_analytics_ibfk_1` FOREIGN KEY (`news_id`) REFERENCES `news` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- Table structure for `news_credibility_analysis`
CREATE TABLE `news_credibility_analysis` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `news_id` int(11) NOT NULL,
  `analysis_date` datetime DEFAULT current_timestamp(),
  `credibility_score` decimal(5,2) NOT NULL,
  `confidence_level` decimal(5,2) NOT NULL,
  `title_credibility` decimal(5,2) DEFAULT NULL,
  `content_credibility` decimal(5,2) DEFAULT NULL,
  `source_credibility` decimal(5,2) DEFAULT NULL,
  `factual_accuracy` decimal(5,2) DEFAULT NULL,
  `sensationalism_score` decimal(5,2) DEFAULT NULL,
  `emotional_manipulation` decimal(5,2) DEFAULT NULL,
  `clickbait_score` decimal(5,2) DEFAULT NULL,
  `propaganda_indicators` decimal(5,2) DEFAULT NULL,
  `grammar_score` decimal(5,2) DEFAULT NULL,
  `readability_score` decimal(5,2) DEFAULT NULL,
  `factual_density` decimal(5,2) DEFAULT NULL,
  `source_verified` tinyint(1) DEFAULT 0,
  `source_reputation_score` decimal(5,2) DEFAULT NULL,
  `cross_reference_count` int(11) DEFAULT 0,
  `analysis_method` varchar(50) DEFAULT 'AI_MULTIMODEL',
  `processing_time_ms` int(11) DEFAULT NULL,
  `ai_model_version` varchar(20) DEFAULT NULL,
  `risk_level` enum('LOW','MEDIUM','HIGH','CRITICAL') DEFAULT 'LOW',
  `content_category` enum('VERIFIED','LIKELY_TRUE','UNVERIFIED','LIKELY_FALSE','FALSE') DEFAULT 'UNVERIFIED',
  `requires_review` tinyint(1) DEFAULT 0,
  `auto_flagged` tinyint(1) DEFAULT 0,
  `manual_review_score` decimal(5,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_news_id` (`news_id`),
  KEY `idx_credibility_score` (`credibility_score`),
  KEY `idx_risk_level` (`risk_level`),
  KEY `idx_analysis_date` (`analysis_date`),
  KEY `idx_requires_review` (`requires_review`),
  CONSTRAINT `news_credibility_analysis_ibfk_1` FOREIGN KEY (`news_id`) REFERENCES `news` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- Table structure for `news_editions`
CREATE TABLE `news_editions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `news_id` int(11) NOT NULL,
  `edition_name` varchar(100) NOT NULL,
  `edition_type` enum('morning','evening','breaking','special','weekend','regional') NOT NULL,
  `content` text DEFAULT NULL,
  `additional_images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`additional_images`)),
  `priority` int(11) DEFAULT 0,
  `status` enum('draft','published','archived') DEFAULT 'draft',
  `published_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_news_editions_news_id` (`news_id`),
  KEY `idx_news_editions_type` (`edition_type`),
  KEY `idx_news_editions_status` (`status`),
  KEY `idx_news_editions_published` (`published_at`),
  CONSTRAINT `news_editions_ibfk_1` FOREIGN KEY (`news_id`) REFERENCES `news` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `news_editions` VALUES ('3', '411', 'War', 'breaking', '', NULL, '2', 'published', '2026-03-11 21:26:00', '2026-03-11 21:28:39', '2026-03-11 21:28:39';
INSERT INTO `news_editions` VALUES ('4', '411', 'War', 'breaking', '', NULL, '2', 'published', '2026-03-11 21:26:00', '2026-03-11 21:29:29', '2026-03-11 21:29:29';


-- Table structure for `news_heatmap`
CREATE TABLE `news_heatmap` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `news_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `view_count` int(11) NOT NULL DEFAULT 0,
  `interaction_count` int(11) NOT NULL DEFAULT 0,
  `share_count` int(11) NOT NULL DEFAULT 0,
  `comment_count` int(11) NOT NULL DEFAULT 0,
  `heat_score` decimal(10,2) NOT NULL DEFAULT 0.00,
  `location_country` varchar(100) DEFAULT NULL,
  `location_city` varchar(100) DEFAULT NULL,
  `hour_of_day` tinyint(2) NOT NULL,
  `day_of_week` tinyint(1) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_news_id` (`news_id`),
  KEY `idx_category_id` (`category_id`),
  KEY `idx_heat_score` (`heat_score`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_location` (`location_country`,`location_city`),
  KEY `idx_time` (`hour_of_day`,`day_of_week`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `news_heatmap` VALUES ('1', '410', NULL, '4', '0', '0', '0', '4.00', 'PK', NULL, '17', '3', '2026-03-11 17:34:30', '2026-03-11 17:55:14';
INSERT INTO `news_heatmap` VALUES ('3', '410', NULL, '1', '0', '0', '0', '1.00', 'PK', NULL, '17', '3', '2026-03-11 17:39:16', '2026-03-11 17:39:16';
INSERT INTO `news_heatmap` VALUES ('10', '410', NULL, '1', '0', '0', '0', '1.00', 'PK', NULL, '17', '3', '2026-03-11 17:40:34', '2026-03-11 17:40:34';
INSERT INTO `news_heatmap` VALUES ('11', '410', NULL, '1', '0', '0', '0', '1.00', 'PK', NULL, '17', '3', '2026-03-11 17:41:32', '2026-03-11 17:41:32';
INSERT INTO `news_heatmap` VALUES ('12', '410', NULL, '1', '0', '0', '0', '1.00', 'PK', NULL, '17', '3', '2026-03-11 17:41:33', '2026-03-11 17:41:33';
INSERT INTO `news_heatmap` VALUES ('13', '410', NULL, '1', '0', '0', '0', '1.00', 'PK', NULL, '17', '3', '2026-03-11 17:41:33', '2026-03-11 17:41:33';
INSERT INTO `news_heatmap` VALUES ('14', '410', NULL, '1', '0', '0', '0', '1.00', 'PK', NULL, '17', '3', '2026-03-11 17:41:36', '2026-03-11 17:41:36';
INSERT INTO `news_heatmap` VALUES ('15', '467', '3', '2', '0', '0', '0', '2.00', 'PK', NULL, '19', '3', '2026-03-11 19:02:03', '2026-03-11 19:33:15';
INSERT INTO `news_heatmap` VALUES ('16', '472', '94', '1', '0', '0', '0', '1.00', 'PK', NULL, '20', '3', '2026-03-11 20:12:17', '2026-03-11 20:12:17';
INSERT INTO `news_heatmap` VALUES ('17', '410', '13', '1', '0', '0', '0', '1.00', 'PK', NULL, '20', '3', '2026-03-11 20:50:26', '2026-03-11 20:50:26';
INSERT INTO `news_heatmap` VALUES ('18', '478', '94', '2', '0', '0', '0', '2.00', 'PK', NULL, '21', '3', '2026-03-11 21:14:12', '2026-03-11 21:15:18';


-- Table structure for `news_sources`
CREATE TABLE `news_sources` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT 'Name of the news source',
  `url` varchar(500) NOT NULL COMMENT 'Main URL of the news source',
  `category_id` int(11) DEFAULT NULL COMMENT 'Default category for scraped news',
  `rss_url` varchar(500) DEFAULT NULL COMMENT 'RSS feed URL if available',
  `scraping_selector` text DEFAULT NULL COMMENT 'CSS selector for content scraping',
  `title_selector` varchar(255) DEFAULT NULL COMMENT 'CSS selector for article titles',
  `content_selector` varchar(255) DEFAULT NULL COMMENT 'CSS selector for article content',
  `image_selector` varchar(255) DEFAULT NULL COMMENT 'CSS selector for article images',
  `link_selector` varchar(255) DEFAULT NULL COMMENT 'CSS selector for article links',
  `status` enum('active','inactive','error') DEFAULT 'active' COMMENT 'Source status',
  `last_scraped` datetime DEFAULT NULL COMMENT 'Last successful scrape time',
  `scrape_frequency` int(11) DEFAULT 60 COMMENT 'Scraping frequency in minutes',
  `total_articles` int(11) DEFAULT 0 COMMENT 'Total articles scraped',
  `error_count` int(11) DEFAULT 0 COMMENT 'Consecutive error count',
  `last_error` text DEFAULT NULL COMMENT 'Last error message',
  `settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Additional scraping settings' CHECK (json_valid(`settings`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `priority` int(11) DEFAULT 0,
  `type` enum('rss','website','api') DEFAULT 'rss',
  `total_articles_scraped` int(11) DEFAULT 0,
  `auto_generate_images` tinyint(1) DEFAULT 0 COMMENT 'Automatically generate images for RSS imports',
  `image_provider` enum('openai','stability','replicate','placeholder') DEFAULT 'openai',
  `image_style` varchar(100) DEFAULT 'realistic journalistic news photo' COMMENT 'Style for AI image generation',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_url` (`url`),
  KEY `idx_category_id` (`category_id`),
  KEY `idx_status` (`status`),
  KEY `idx_last_scraped` (`last_scraped`),
  KEY `idx_name` (`name`),
  KEY `idx_error_count` (`error_count`),
  KEY `idx_news_sources_status` (`status`),
  KEY `idx_news_sources_last_scraped` (`last_scraped`),
  KEY `idx_news_sources_priority` (`priority`),
  CONSTRAINT `fk_news_sources_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=381 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='External news sources for web scraping';

INSERT INTO `news_sources` VALUES ('35', 'BBC News', 'https://www.bbc.com/news', '3', 'https://feeds.bbci.co.uk/news/rss.xml', NULL, NULL, NULL, NULL, NULL, 'active', '2026-03-13 06:11:38', '30', '0', '13', 'Invalid RSS format', NULL, '2026-03-10 16:51:13', '2026-03-17 05:27:17', '0', 'rss', '0', '0', 'openai', 'realistic journalistic news photo';
INSERT INTO `news_sources` VALUES ('36', 'Al Jazeera', 'https://www.aljazeera.com', '3', 'https://www.aljazeera.com/xml/rss/all.xml', NULL, NULL, NULL, NULL, NULL, 'active', '2026-03-13 05:30:00', '60', '0', '15', 'Invalid RSS format', NULL, '2026-03-10 16:51:13', '2026-03-16 20:33:00', '0', 'rss', '0', '0', 'openai', 'realistic journalistic news photo';
INSERT INTO `news_sources` VALUES ('37', 'The Guardian', 'https://www.theguardian.com/world', '3', 'https://www.theguardian.com/world/rss', NULL, NULL, NULL, NULL, NULL, 'active', '2026-03-13 05:28:00', '60', '0', '9', 'Invalid RSS format', NULL, '2026-03-10 16:51:13', '2026-03-16 20:32:57', '0', 'rss', '0', '0', 'openai', 'realistic journalistic news photo';
INSERT INTO `news_sources` VALUES ('39', 'CNN', 'https://www.cnn.com', '3', 'http://rss.cnn.com/rss/edition.rss', NULL, NULL, NULL, NULL, NULL, 'active', '2026-03-13 06:03:17', '30', '0', '8', 'Invalid RSS format', NULL, '2026-03-11 04:43:58', '2026-03-16 20:32:47', '0', 'rss', '0', '0', 'openai', 'realistic journalistic news photo';
INSERT INTO `news_sources` VALUES ('40', 'ARY News', 'https://arynews.tv', '94', 'https://arynews.tv/feed/', NULL, NULL, NULL, NULL, NULL, 'active', '2026-03-13 05:29:02', '60', '0', '7', 'Invalid RSS format', NULL, '2026-03-11 04:43:58', '2026-03-16 20:32:50', '0', 'rss', '0', '0', 'openai', 'realistic journalistic news photo';
INSERT INTO `news_sources` VALUES ('295', 'financialcontent', 'https://markets.financialcontent.com', '9', 'https://markets.financialcontent.com', NULL, NULL, NULL, NULL, NULL, 'active', NULL, '5', '0', '245', 'Failed to fetch RSS feed', NULL, '2026-03-11 20:40:19', '2026-03-17 04:55:40', '0', 'rss', '0', '0', 'openai', 'realistic journalistic news photo';
INSERT INTO `news_sources` VALUES ('296', 'Reuters', 'https://www.reuters.com', NULL, 'https://www.reuters.com/rssFeed', NULL, NULL, NULL, NULL, NULL, 'active', NULL, '60', '0', '101', 'Failed to fetch RSS feed', NULL, '2026-03-12 16:56:57', '2026-03-17 04:55:40', '0', 'rss', '0', '0', 'openai', 'realistic journalistic news photo';
INSERT INTO `news_sources` VALUES ('297', 'Fox News', 'https://www.foxnews.com', NULL, 'https://feeds.foxnews.com/foxnews/latest', NULL, NULL, NULL, NULL, NULL, 'active', '2026-03-13 05:26:01', '60', '0', '3', 'Invalid RSS format', NULL, '2026-03-12 16:56:57', '2026-03-17 05:29:12', '0', 'rss', '0', '0', 'openai', 'realistic journalistic news photo';
INSERT INTO `news_sources` VALUES ('375', 'CNN News', 'http://rss.cnn.com/rss/edition.rss', '1', 'http://rss.cnn.com/rss/edition.rss', NULL, NULL, NULL, NULL, NULL, 'active', '2026-03-17 23:34:35', '60', '0', '4', 'Failed to fetch RSS feed', NULL, '2026-03-13 06:40:03', '2026-03-17 23:34:35', '0', 'rss', '0', '0', 'openai', 'realistic journalistic news photo';
INSERT INTO `news_sources` VALUES ('376', 'Geo News', 'https://www.geo.tv/rss/feed/1', '1', 'https://www.geo.tv/rss/feed/1', NULL, NULL, NULL, NULL, NULL, 'active', NULL, '60', '0', '73', 'Failed to fetch RSS feed', NULL, '2026-03-13 06:40:03', '2026-03-17 04:55:40', '0', 'rss', '0', '0', 'openai', 'realistic journalistic news photo';
INSERT INTO `news_sources` VALUES ('377', 'CBS News', 'http://feeds.cbsnews.com/CBSNewsMain', '1', 'http://feeds.cbsnews.com/CBSNewsMain', NULL, NULL, NULL, NULL, NULL, 'active', '2026-03-17 23:33:57', '60', '0', '0', NULL, NULL, '2026-03-17 05:27:10', '2026-03-17 23:33:57', '0', 'rss', '0', '0', 'openai', 'realistic journalistic news photo';
INSERT INTO `news_sources` VALUES ('378', 'NPR News', 'https://feeds.npr.org/1001/rss.xml', '1', 'https://feeds.npr.org/1001/rss.xml', NULL, NULL, NULL, NULL, NULL, 'active', '2026-03-17 23:34:49', '60', '0', '0', NULL, NULL, '2026-03-17 05:27:26', '2026-03-17 23:34:49', '0', 'rss', '0', '0', 'openai', 'realistic journalistic news photo';
INSERT INTO `news_sources` VALUES ('379', 'Google News', 'https://news.google.com/rss', '1', 'https://news.google.com/rss', NULL, NULL, NULL, NULL, NULL, 'active', '2026-03-17 23:34:48', '60', '0', '0', NULL, NULL, '2026-03-17 05:27:31', '2026-03-17 23:34:48', '0', 'rss', '0', '0', 'openai', 'realistic journalistic news photo';
INSERT INTO `news_sources` VALUES ('380', 'Yahoo News', 'https://news.yahoo.com/rss', '1', 'https://news.yahoo.com/rss', NULL, NULL, NULL, NULL, NULL, 'active', '2026-03-17 23:34:55', '60', '0', '0', NULL, NULL, '2026-03-17 05:27:34', '2026-03-17 23:34:55', '0', 'rss', '0', '0', 'openai', 'realistic journalistic news photo';


-- Table structure for `news_tags`
CREATE TABLE `news_tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `news_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_news_tag` (`news_id`,`tag_id`),
  KEY `idx_news_tags_news` (`news_id`),
  KEY `idx_news_tags_tag` (`tag_id`),
  CONSTRAINT `news_tags_ibfk_1` FOREIGN KEY (`news_id`) REFERENCES `news` (`id`) ON DELETE CASCADE,
  CONSTRAINT `news_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- Table structure for `notification_preferences`
CREATE TABLE `notification_preferences` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `news_notifications` tinyint(1) DEFAULT 1,
  `comment_notifications` tinyint(1) DEFAULT 1,
  `system_notifications` tinyint(1) DEFAULT 1,
  `email_notifications` tinyint(1) DEFAULT 1,
  `push_notifications` tinyint(1) DEFAULT 0,
  `frequency` enum('immediate','hourly','daily','weekly') DEFAULT 'immediate',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_preferences` (`user_id`),
  CONSTRAINT `notification_preferences_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `notification_preferences` VALUES ('1', '1', '1', '1', '1', '1', '0', 'immediate', '2026-03-06 04:03:53', '2026-03-06 04:03:53';


-- Table structure for `notifications`
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `type` enum('news','comment','system','reminder','promotion') NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `related_id` int(11) DEFAULT NULL,
  `url` varchar(500) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `is_email_sent` tinyint(1) DEFAULT 0,
  `priority` enum('low','medium','high','urgent') DEFAULT 'medium',
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `read_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_notifications_user` (`user_id`),
  KEY `idx_notifications_type` (`type`),
  KEY `idx_notifications_read` (`is_read`),
  KEY `idx_notifications_created` (`created_at`),
  KEY `idx_notifications_priority` (`priority`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `notifications` VALUES ('2', NULL, 'news', 'Good', 'Boys', NULL, NULL, '1', '0', 'medium', NULL, '2026-03-08 09:47:19', NULL;


-- Table structure for `overlay_templates`
CREATE TABLE `overlay_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `template_type` enum('news','sports','breaking','weather','interview','custom') DEFAULT 'news',
  `html_template` text NOT NULL,
  `css_styles` text DEFAULT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `overlay_templates` VALUES ('1', 'Breaking News Banner', 'Animated breaking news banner', 'breaking', '<div class=\"breaking-banner\"><div class=\"breaking-icon\">????</div><div class=\"breaking-text\">BREAKING NEWS</div><div class=\"breaking-title\">{{title}}</div></div>', '.breaking-banner{background:linear-gradient(90deg,#ff0000,#cc0000);color:white;padding:10px 20px;position:absolute;top:0;left:0;right:0;z-index:1001;display:flex;align-items:center;font-weight:bold;animation:slideIn 0.5s ease-out}.breaking-icon{font-size:24px;margin-right:10px;animation:pulse 1s infinite}.breaking-text{margin-right:20px;letter-spacing:2px}.breaking-title{flex:1}@keyframes slideIn{from{transform:translateY(-100%)}to{transform:translateY(0)}}@keyframes pulse{0%,100%{opacity:1}50%{opacity:0.5}}', '1', '2026-03-05 14:58:50';
INSERT INTO `overlay_templates` VALUES ('2', 'Live Badge', 'Animated LIVE indicator', 'news', '<div class=\"live-badge\"><div class=\"live-dot\"></div><span>LIVE</span></div>', '.live-badge{position:absolute;top:20px;right:20px;background:#ff0000;color:white;padding:8px 16px;border-radius:20px;font-weight:bold;z-index:1002;display:flex;align-items:center;animation:blink 2s infinite}.live-dot{width:8px;height:8px;background:white;border-radius:50%;margin-right:8px;animation:pulse 1s infinite}@keyframes blink{0%,100%{opacity:1}50%{opacity:0.7}}@keyframes pulse{0%,100%{transform:scale(1)}50%{transform:scale(1.2)}}', '1', '2026-03-05 14:58:50';
INSERT INTO `overlay_templates` VALUES ('3', 'News Ticker', 'Scrolling news ticker at bottom', 'news', '<div class=\"news-ticker\"><div class=\"ticker-content\">{{content}}</div></div>', '.news-ticker{position:absolute;bottom:0;left:0;right:0;background:rgba(0,0,0,0.8);color:white;padding:8px 0;z-index:1000;overflow:hidden}.ticker-content{display:inline-block;white-space:nowrap;animation:scroll 30s linear infinite;padding-left:100%}@keyframes scroll{0%{transform:translateX(0)}100%{transform:translateX(-100%)}}', '1', '2026-03-05 14:58:50';
INSERT INTO `overlay_templates` VALUES ('4', 'Weather Overlay', 'Weather information overlay', 'weather', '<div class=\"weather-overlay\"><div class=\"weather-icon\">{{icon}}</div><div class=\"weather-info\"><div class=\"temperature\">{{temp}}°C</div><div class=\"condition\">{{condition}}</div></div></div>', '.weather-overlay{position:absolute;top:20px;left:20px;background:rgba(0,0,0,0.7);color:white;padding:15px;border-radius:10px;z-index:1000;display:flex;align-items:center}.weather-icon{font-size:32px;margin-right:10px}.weather-info .temperature{font-size:24px;font-weight:bold}.weather-info .condition{font-size:14px;opacity:0.8}', '0', '2026-03-05 14:58:50';
INSERT INTO `overlay_templates` VALUES ('5', 'Score Board', 'Sports score overlay', 'sports', '<div class=\"scoreboard\"><div class=\"team team-home\"><div class=\"team-name\">{{home_team}}</div><div class=\"team-score\">{{home_score}}</div></div><div class=\"vs\">VS</div><div class=\"team team-away\"><div class=\"team-name\">{{away_team}}</div><div class=\"team-score\">{{away_score}}</div></div></div>', '.scoreboard{position:absolute;top:80px;right:20px;background:rgba(0,0,0,0.8);color:white;padding:15px 20px;border-radius:10px;z-index:1000;display:flex;align-items:center;gap:20px}.team{text-align:center}.team-name{font-size:14px;margin-bottom:5px}.team-score{font-size:24px;font-weight:bold}.vs{font-weight:bold;opacity:0.7}', '0', '2026-03-05 14:58:50';


-- Table structure for `poll_options`
CREATE TABLE `poll_options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `poll_id` int(11) NOT NULL,
  `option_text` varchar(255) NOT NULL,
  `votes` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `poll_id` (`poll_id`),
  CONSTRAINT `poll_options_ibfk_1` FOREIGN KEY (`poll_id`) REFERENCES `polls` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- Table structure for `poll_votes`
CREATE TABLE `poll_votes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `poll_id` int(11) NOT NULL,
  `option_id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_vote` (`poll_id`,`ip_address`),
  KEY `option_id` (`option_id`),
  CONSTRAINT `poll_votes_ibfk_1` FOREIGN KEY (`poll_id`) REFERENCES `polls` (`id`) ON DELETE CASCADE,
  CONSTRAINT `poll_votes_ibfk_2` FOREIGN KEY (`option_id`) REFERENCES `poll_options` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- Table structure for `polls`
CREATE TABLE `polls` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `question` varchar(255) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_polls_status` (`status`),
  KEY `idx_polls_created` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- Table structure for `popular_articles`
CREATE TABLE `popular_articles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `news_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `total_views` int(11) DEFAULT 0,
  `total_shares` int(11) DEFAULT 0,
  `total_comments` int(11) DEFAULT 0,
  `engagement_score` decimal(10,2) DEFAULT 0.00,
  `rank_position` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_popular_news_date` (`news_id`,`date`),
  KEY `idx_popular_date` (`date`),
  KEY `idx_popular_score` (`engagement_score`),
  CONSTRAINT `popular_articles_ibfk_1` FOREIGN KEY (`news_id`) REFERENCES `news` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



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
  CONSTRAINT `post_likes_ibfk_1` FOREIGN KEY (`news_id`) REFERENCES `news` (`id`) ON DELETE CASCADE,
  CONSTRAINT `post_likes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `post_likes` VALUES ('3', '492', '1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0', '2026-03-12 06:57:53';
INSERT INTO `post_likes` VALUES ('4', '448', '1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0', '2026-03-12 07:11:50';
INSERT INTO `post_likes` VALUES ('6', '494', '1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0', '2026-03-12 09:38:32';
INSERT INTO `post_likes` VALUES ('7', '494', '5', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0', '2026-03-12 10:11:17';
INSERT INTO `post_likes` VALUES ('8', '515', '1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0', '2026-03-12 17:25:09';
INSERT INTO `post_likes` VALUES ('9', '530', '1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0', '2026-03-13 06:06:09';
INSERT INTO `post_likes` VALUES ('10', '537', '1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0', '2026-03-16 20:49:42';


-- Table structure for `push_subscriptions`
CREATE TABLE `push_subscriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `endpoint` varchar(500) NOT NULL,
  `p256dh_key` varchar(255) NOT NULL,
  `auth_key` varchar(255) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `device_type` enum('desktop','mobile','tablet') DEFAULT 'desktop',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_endpoint` (`endpoint`(255)),
  KEY `idx_user_active` (`user_id`,`is_active`),
  KEY `idx_subscriptions_active` (`is_active`,`device_type`),
  CONSTRAINT `push_subscriptions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- Table structure for `reading_history`
CREATE TABLE `reading_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `news_id` int(11) NOT NULL,
  `read_time` int(11) DEFAULT 0,
  `progress` int(11) DEFAULT 0,
  `last_position` text DEFAULT NULL,
  `is_completed` tinyint(1) DEFAULT 0,
  `read_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_news_read` (`user_id`,`news_id`),
  KEY `idx_reading_history_user` (`user_id`),
  KEY `idx_reading_history_news` (`news_id`),
  KEY `idx_reading_history_read_at` (`read_at`),
  CONSTRAINT `reading_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reading_history_ibfk_2` FOREIGN KEY (`news_id`) REFERENCES `news` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- Table structure for `regional_analytics`
CREATE TABLE `regional_analytics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `country` varchar(100) NOT NULL,
  `city` varchar(100) DEFAULT NULL,
  `news_count` int(11) NOT NULL DEFAULT 0,
  `total_views` int(11) NOT NULL DEFAULT 0,
  `avg_heat_score` decimal(10,2) NOT NULL DEFAULT 0.00,
  `top_category_id` int(11) DEFAULT NULL,
  `last_activity` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_country` (`country`),
  KEY `idx_city` (`city`),
  KEY `idx_news_count` (`news_count`),
  KEY `idx_last_activity` (`last_activity`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `regional_analytics` VALUES ('1', 'PK', NULL, '1', '1', '1.00', NULL, '2026-03-11 17:39:16';
INSERT INTO `regional_analytics` VALUES ('2', 'PK', NULL, '1', '1', '1.00', NULL, '2026-03-11 17:40:34';
INSERT INTO `regional_analytics` VALUES ('3', 'PK', NULL, '1', '1', '1.00', NULL, '2026-03-11 17:41:32';
INSERT INTO `regional_analytics` VALUES ('4', 'PK', NULL, '1', '1', '1.00', NULL, '2026-03-11 17:41:33';
INSERT INTO `regional_analytics` VALUES ('5', 'PK', NULL, '1', '1', '1.00', NULL, '2026-03-11 17:41:33';
INSERT INTO `regional_analytics` VALUES ('6', 'PK', NULL, '1', '1', '1.00', NULL, '2026-03-11 17:41:36';
INSERT INTO `regional_analytics` VALUES ('7', 'PK', NULL, '1', '1', '2.00', NULL, '2026-03-11 17:54:19';
INSERT INTO `regional_analytics` VALUES ('8', 'PK', NULL, '1', '1', '3.00', NULL, '2026-03-11 17:54:39';
INSERT INTO `regional_analytics` VALUES ('9', 'PK', NULL, '1', '1', '4.00', NULL, '2026-03-11 17:55:14';
INSERT INTO `regional_analytics` VALUES ('10', 'PK', NULL, '1', '1', '1.00', NULL, '2026-03-11 19:02:03';
INSERT INTO `regional_analytics` VALUES ('11', 'PK', NULL, '1', '1', '1.00', NULL, '2026-03-11 19:02:03';
INSERT INTO `regional_analytics` VALUES ('12', 'PK', NULL, '1', '1', '2.00', NULL, '2026-03-11 19:33:15';
INSERT INTO `regional_analytics` VALUES ('13', 'PK', NULL, '1', '1', '1.00', NULL, '2026-03-11 20:12:17';
INSERT INTO `regional_analytics` VALUES ('14', 'PK', NULL, '1', '1', '1.00', NULL, '2026-03-11 20:12:17';
INSERT INTO `regional_analytics` VALUES ('15', 'PK', NULL, '1', '1', '1.00', NULL, '2026-03-11 20:50:26';
INSERT INTO `regional_analytics` VALUES ('16', 'PK', NULL, '1', '1', '1.00', NULL, '2026-03-11 20:50:26';
INSERT INTO `regional_analytics` VALUES ('17', 'PK', NULL, '1', '1', '1.00', NULL, '2026-03-11 21:14:12';
INSERT INTO `regional_analytics` VALUES ('18', 'PK', NULL, '1', '1', '1.00', NULL, '2026-03-11 21:14:12';
INSERT INTO `regional_analytics` VALUES ('19', 'PK', NULL, '1', '1', '2.00', NULL, '2026-03-11 21:15:18';


-- Table structure for `scraping_reports`
CREATE TABLE `scraping_reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `report_data` text NOT NULL COMMENT 'JSON report data',
  `sources_processed` int(11) DEFAULT 0,
  `articles_found` int(11) DEFAULT 0,
  `articles_imported` int(11) DEFAULT 0,
  `duplicates_skipped` int(11) DEFAULT 0,
  `errors` int(11) DEFAULT 0,
  `duration_seconds` decimal(8,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_scraping_reports_created` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `scraping_reports` VALUES ('1', '{\"sources_processed\":12,\"articles_found\":0,\"articles_imported\":0,\"duplicates_skipped\":0,\"errors\":0,\"details\":[{\"source_name\":\"BBC News Pakistan\",\"articles_found\":0,\"articles_imported\":0,\"duplicates_skipped\":0,\"errors\":[\"HTTP Error: 404 - Failed to fetch content from http:\\/\\/feeds.bbci.co.uk\\/news\\/world\\/south_asia\\/rss.xml\"]},{\"source_name\":\"Dawn News Pakistan\",\"articles_found\":0,\"articles_imported\":0,\"duplicates_skipped\":0,\"errors\":[\"Invalid RSS feed: URL does not return valid XML\\/RSS content\"]},{\"source_name\":\"Geo News Pakistan\",\"articles_found\":0,\"articles_imported\":0,\"duplicates_skipped\":0,\"errors\":[\"Invalid RSS feed: URL does not return valid XML\\/RSS content\"]},{\"source_name\":\"ARY News Pakistan\",\"articles_found\":0,\"articles_imported\":0,\"duplicates_skipped\":0,\"errors\":[\"HTTP Error: 404 - Failed to fetch content from https:\\/\\/arynews.tv\\/en\\/feed\\/\"]},{\"source_name\":\"The Express Tribune\",\"articles_found\":0,\"articles_imported\":0,\"duplicates_skipped\":0,\"errors\":[\"Invalid RSS feed: URL does not return valid XML\\/RSS content\"]},{\"source_name\":\"BBC News\",\"articles_found\":0,\"articles_imported\":0,\"duplicates_skipped\":0,\"errors\":[\"Invalid RSS feed: URL does not return valid XML\\/RSS content\"]},{\"source_name\":\"CNN\",\"articles_found\":0,\"articles_imported\":0,\"duplicates_skipped\":0,\"errors\":[\"cURL Error: Operation timed out after 29615 milliseconds with 376805 out of 727681 bytes received\"]},{\"source_name\":\"Reuters\",\"articles_found\":0,\"articles_imported\":0,\"duplicates_skipped\":0,\"errors\":[\"HTTP Error: 401 - Failed to fetch content from https:\\/\\/www.reuters.com\"]},{\"source_name\":\"Al Jazeera\",\"articles_found\":0,\"articles_imported\":0,\"duplicates_skipped\":0,\"errors\":[\"Invalid RSS feed: URL does not return valid XML\\/RSS content\"]},{\"source_name\":\"Dawn News\",\"articles_found\":0,\"articles_imported\":0,\"duplicates_skipped\":0,\"errors\":[\"Invalid RSS feed: URL does not return valid XML\\/RSS content\"]},{\"source_name\":\"Geo News\",\"articles_found\":0,\"articles_imported\":0,\"duplicates_skipped\":0,\"errors\":[\"Invalid RSS feed: URL does not return valid XML\\/RSS content\"]},{\"source_name\":\"ARY News\",\"articles_found\":0,\"articles_imported\":0,\"duplicates_skipped\":0,\"errors\":[\"Invalid RSS feed: URL does not return valid XML\\/RSS content\"]}]}', '0', '0', '0', '0', '0', '0.00', '2026-03-09 17:41:38';
INSERT INTO `scraping_reports` VALUES ('2', '{\"sources_processed\":12,\"articles_found\":0,\"articles_imported\":0,\"duplicates_skipped\":0,\"errors\":0,\"details\":[{\"source_name\":\"BBC News Pakistan\",\"articles_found\":0,\"articles_imported\":0,\"duplicates_skipped\":0,\"errors\":[\"HTTP Error: 404 - Failed to fetch content from http:\\/\\/feeds.bbci.co.uk\\/news\\/world\\/south_asia\\/rss.xml\"]},{\"source_name\":\"Dawn News Pakistan\",\"articles_found\":0,\"articles_imported\":0,\"duplicates_skipped\":0,\"errors\":[\"Invalid RSS feed: URL does not return valid XML\\/RSS content\"]},{\"source_name\":\"Geo News Pakistan\",\"articles_found\":0,\"articles_imported\":0,\"duplicates_skipped\":0,\"errors\":[\"Invalid RSS feed: URL does not return valid XML\\/RSS content\"]},{\"source_name\":\"ARY News Pakistan\",\"articles_found\":0,\"articles_imported\":0,\"duplicates_skipped\":0,\"errors\":[\"HTTP Error: 404 - Failed to fetch content from https:\\/\\/arynews.tv\\/en\\/feed\\/\"]},{\"source_name\":\"The Express Tribune\",\"articles_found\":0,\"articles_imported\":0,\"duplicates_skipped\":0,\"errors\":[\"Invalid RSS feed: URL does not return valid XML\\/RSS content\"]},{\"source_name\":\"BBC News\",\"articles_found\":0,\"articles_imported\":0,\"duplicates_skipped\":0,\"errors\":[\"Invalid RSS feed: URL does not return valid XML\\/RSS content\"]},{\"source_name\":\"CNN\",\"articles_found\":0,\"articles_imported\":0,\"duplicates_skipped\":0,\"errors\":[\"cURL Error: Operation timed out after 27085 milliseconds with 342430 out of 727614 bytes received\"]},{\"source_name\":\"Reuters\",\"articles_found\":0,\"articles_imported\":0,\"duplicates_skipped\":0,\"errors\":[\"HTTP Error: 401 - Failed to fetch content from https:\\/\\/www.reuters.com\"]},{\"source_name\":\"Al Jazeera\",\"articles_found\":0,\"articles_imported\":0,\"duplicates_skipped\":0,\"errors\":[\"cURL Error: Operation timed out after 30000 milliseconds with 65536 out of 87799 bytes received\"]},{\"source_name\":\"Dawn News\",\"articles_found\":0,\"articles_imported\":0,\"duplicates_skipped\":0,\"errors\":[\"Invalid RSS feed: URL does not return valid XML\\/RSS content\"]},{\"source_name\":\"Geo News\",\"articles_found\":0,\"articles_imported\":0,\"duplicates_skipped\":0,\"errors\":[\"Invalid RSS feed: URL does not return valid XML\\/RSS content\"]},{\"source_name\":\"ARY News\",\"articles_found\":0,\"articles_imported\":0,\"duplicates_skipped\":0,\"errors\":[\"Invalid RSS feed: URL does not return valid XML\\/RSS content\"]}]}', '0', '0', '0', '0', '0', '0.00', '2026-03-09 17:56:09';
INSERT INTO `scraping_reports` VALUES ('3', '{\"sources_processed\":12,\"articles_found\":0,\"articles_imported\":0,\"duplicates_skipped\":0,\"errors\":0,\"details\":[{\"source_name\":\"BBC News Pakistan\",\"articles_found\":0,\"articles_imported\":0,\"duplicates_skipped\":0,\"errors\":[\"HTTP Error: 404 - Failed to fetch content from http:\\/\\/feeds.bbci.co.uk\\/news\\/world\\/south_asia\\/rss.xml\"]},{\"source_name\":\"Dawn News Pakistan\",\"articles_found\":0,\"articles_imported\":0,\"duplicates_skipped\":0,\"errors\":[\"Invalid RSS feed: URL does not return valid XML\\/RSS content\"]},{\"source_name\":\"Geo News Pakistan\",\"articles_found\":0,\"articles_imported\":0,\"duplicates_skipped\":0,\"errors\":[\"Invalid RSS feed: URL does not return valid XML\\/RSS content\"]},{\"source_name\":\"ARY News Pakistan\",\"articles_found\":0,\"articles_imported\":0,\"duplicates_skipped\":0,\"errors\":[\"HTTP Error: 404 - Failed to fetch content from https:\\/\\/arynews.tv\\/en\\/feed\\/\"]},{\"source_name\":\"The Express Tribune\",\"articles_found\":0,\"articles_imported\":0,\"duplicates_skipped\":0,\"errors\":[\"Invalid RSS feed: URL does not return valid XML\\/RSS content\"]},{\"source_name\":\"BBC News\",\"articles_found\":0,\"articles_imported\":0,\"duplicates_skipped\":0,\"errors\":[\"cURL Error: Operation timed out after 30005 milliseconds with 0 bytes received\"]},{\"source_name\":\"CNN\",\"articles_found\":0,\"articles_imported\":0,\"duplicates_skipped\":0,\"errors\":[\"cURL Error: Could not resolve host: www.cnn.com\"]},{\"source_name\":\"Reuters\",\"articles_found\":0,\"articles_imported\":0,\"duplicates_skipped\":0,\"errors\":[\"cURL Error: Could not resolve host: www.reuters.com\"]},{\"source_name\":\"Al Jazeera\",\"articles_found\":0,\"articles_imported\":0,\"duplicates_skipped\":0,\"errors\":[\"cURL Error: Could not resolve host: www.aljazeera.com\"]},{\"source_name\":\"Dawn News\",\"articles_found\":0,\"articles_imported\":0,\"duplicates_skipped\":0,\"errors\":[\"Invalid RSS feed: URL does not return valid XML\\/RSS content\"]},{\"source_name\":\"Geo News\",\"articles_found\":0,\"articles_imported\":0,\"duplicates_skipped\":0,\"errors\":[\"Invalid RSS feed: URL does not return valid XML\\/RSS content\"]},{\"source_name\":\"ARY News\",\"articles_found\":0,\"articles_imported\":0,\"duplicates_skipped\":0,\"errors\":[\"Invalid RSS feed: URL does not return valid XML\\/RSS content\"]}]}', '0', '0', '0', '0', '0', '0.00', '2026-03-09 18:00:54';
INSERT INTO `scraping_reports` VALUES ('4', '{\"sources_processed\":9,\"articles_found\":0,\"articles_imported\":0,\"duplicates_skipped\":0,\"errors\":3,\"details\":[{\"source_name\":\"BBC News Pakistan\",\"articles_found\":0,\"articles_imported\":0,\"duplicates_skipped\":0,\"errors\":[\"cURL Error: Operation timed out after 23047 milliseconds with 0 bytes received\"]},{\"source_name\":\"Dawn News Pakistan\",\"articles_found\":0,\"articles_imported\":0,\"duplicates_skipped\":0,\"errors\":[\"cURL Error: Could not resolve host: www.dawn.com\"]},{\"source_name\":\"Geo News Pakistan\",\"articles_found\":0,\"articles_imported\":0,\"duplicates_skipped\":0,\"errors\":[\"HTTP Error: 0 - Failed to fetch content from https:\\/\\/www.geo.tv\\/rss\\/pakistan\"]},{\"source_name\":\"ARY News Pakistan\",\"articles_found\":0,\"articles_imported\":0,\"duplicates_skipped\":0,\"errors\":[\"HTTP Error: 0 - Failed to fetch content from https:\\/\\/arynews.tv\\/en\\/feed\\/\"]},{\"source_name\":\"The Express Tribune\",\"articles_found\":0,\"articles_imported\":0,\"duplicates_skipped\":0,\"errors\":[\"cURL Error: Operation timed out after 30003 milliseconds with 29730 bytes received\"]},{\"source_name\":\"BBC News\",\"articles_found\":0,\"articles_imported\":0,\"duplicates_skipped\":0,\"errors\":[\"cURL Error: Operation timed out after 30004 milliseconds with 39935 out of 62338 bytes received\"]},{\"source_name\":\"CNN\",\"articles_found\":0,\"articles_imported\":0,\"duplicates_skipped\":0,\"errors\":[\"cURL Error: Could not resolve host: www.cnn.com\"]},{\"source_name\":\"Reuters\",\"articles_found\":0,\"articles_imported\":0,\"duplicates_skipped\":0,\"errors\":[\"HTTP Error: 401 - Failed to fetch content from https:\\/\\/www.reuters.com\"]},{\"source_name\":\"Al Jazeera\",\"articles_found\":0,\"articles_imported\":0,\"duplicates_skipped\":0,\"errors\":[\"cURL Error: Operation timed out after 30009 milliseconds with 0 out of 87871 bytes received\"]}]}', '0', '0', '0', '0', '0', '0.00', '2026-03-09 18:02:41';
INSERT INTO `scraping_reports` VALUES ('5', '{\"sources_processed\":8,\"articles_found\":0,\"articles_imported\":0,\"duplicates_skipped\":0,\"errors\":4,\"details\":[{\"source_name\":\"BBC News Pakistan\",\"articles_found\":0,\"articles_imported\":0,\"duplicates_skipped\":0,\"errors\":[\"cURL Error: Connection timed out after 11062 milliseconds\"]},{\"source_name\":\"Dawn News Pakistan\",\"articles_found\":0,\"articles_imported\":0,\"duplicates_skipped\":0,\"errors\":[\"Invalid RSS feed: URL does not return valid XML\\/RSS content\"]},{\"source_name\":\"Geo News Pakistan\",\"articles_found\":0,\"articles_imported\":0,\"duplicates_skipped\":0,\"errors\":[\"Invalid RSS feed: URL does not return valid XML\\/RSS content\"]},{\"source_name\":\"ARY News Pakistan\",\"articles_found\":0,\"articles_imported\":0,\"duplicates_skipped\":0,\"errors\":[\"HTTP Error: 404 - Failed to fetch content from https:\\/\\/arynews.tv\\/en\\/feed\\/\"]},{\"source_name\":\"The Express Tribune\",\"articles_found\":0,\"articles_imported\":0,\"duplicates_skipped\":0,\"errors\":[\"cURL Error: Operation timed out after 30004 milliseconds with 12303 bytes received\"]},{\"source_name\":\"BBC News\",\"articles_found\":0,\"articles_imported\":0,\"duplicates_skipped\":0,\"errors\":[\"cURL Error: Operation timed out after 30010 milliseconds with 0 out of 62339 bytes received\"]},{\"source_name\":\"CNN\",\"articles_found\":0,\"articles_imported\":0,\"duplicates_skipped\":0,\"errors\":[\"cURL Error: Connection timed out after 9423 milliseconds\"]},{\"source_name\":\"Reuters\",\"articles_found\":0,\"articles_imported\":0,\"duplicates_skipped\":0,\"errors\":[\"cURL Error: Connection timed out after 30008 milliseconds\"]}]}', '0', '0', '0', '0', '0', '0.00', '2026-03-09 18:02:57';


-- Table structure for `search_analytics`
CREATE TABLE `search_analytics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `query` varchar(255) NOT NULL,
  `results_count` int(11) DEFAULT 0,
  `clicked_result_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `clicked_result_id` (`clicked_result_id`),
  KEY `user_id` (`user_id`),
  KEY `idx_search_query` (`query`),
  KEY `idx_search_date` (`created_at`),
  CONSTRAINT `search_analytics_ibfk_1` FOREIGN KEY (`clicked_result_id`) REFERENCES `news` (`id`) ON DELETE SET NULL,
  CONSTRAINT `search_analytics_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `settings` VALUES ('1', 'site_name', 'PK Live News', 'Website name', '2026-03-04 10:28:28', '2026-03-04 10:28:28';
INSERT INTO `settings` VALUES ('2', 'site_description', 'Your trusted source for breaking news', 'Site description', '2026-03-04 10:28:28', '2026-03-04 10:28:28';
INSERT INTO `settings` VALUES ('3', 'contact_email', 'contact@pklivenews.com', 'Contact email', '2026-03-04 10:28:28', '2026-03-04 10:28:28';
INSERT INTO `settings` VALUES ('4', 'facebook_url', 'https://facebook.com/pklivenews', 'Facebook page URL', '2026-03-04 10:28:28', '2026-03-04 10:28:28';
INSERT INTO `settings` VALUES ('5', 'twitter_url', 'https://twitter.com/pklivenews', 'Twitter profile URL', '2026-03-04 10:28:28', '2026-03-04 10:28:28';
INSERT INTO `settings` VALUES ('6', 'youtube_url', 'https://youtube.com/pklivenews', 'YouTube channel URL', '2026-03-04 10:28:28', '2026-03-04 10:28:28';


-- Table structure for `stream_cameras`
CREATE TABLE `stream_cameras` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `stream_id` int(11) NOT NULL,
  `camera_number` int(11) NOT NULL,
  `camera_name` varchar(100) NOT NULL,
  `stream_url` varchar(500) NOT NULL,
  `embed_code` text DEFAULT NULL,
  `position` enum('main','picture-in-picture','side-by-side','grid') DEFAULT 'main',
  `quality` enum('360p','480p','720p','1080p','4k') DEFAULT '1080p',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_camera` (`stream_id`,`camera_number`),
  CONSTRAINT `stream_cameras_ibfk_1` FOREIGN KEY (`stream_id`) REFERENCES `live_stream` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- Table structure for `stream_overlays`
CREATE TABLE `stream_overlays` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `stream_id` int(11) NOT NULL,
  `template_id` int(11) DEFAULT NULL,
  `overlay_name` varchar(100) NOT NULL,
  `position_x` int(11) DEFAULT 0,
  `position_y` int(11) DEFAULT 0,
  `width` int(11) DEFAULT 300,
  `height` int(11) DEFAULT 200,
  `z_index` int(11) DEFAULT 1000,
  `is_visible` tinyint(1) DEFAULT 1,
  `custom_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Custom overlay data' CHECK (json_valid(`custom_data`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `stream_id` (`stream_id`),
  KEY `template_id` (`template_id`),
  CONSTRAINT `stream_overlays_ibfk_1` FOREIGN KEY (`stream_id`) REFERENCES `live_stream` (`id`) ON DELETE CASCADE,
  CONSTRAINT `stream_overlays_ibfk_2` FOREIGN KEY (`template_id`) REFERENCES `overlay_templates` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- Table structure for `streaming_servers`
CREATE TABLE `streaming_servers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `server_name` varchar(255) NOT NULL,
  `server_region` varchar(100) DEFAULT NULL,
  `server_url` varchar(500) NOT NULL,
  `rtmp_url` varchar(500) DEFAULT NULL,
  `hls_url` varchar(500) DEFAULT NULL,
  `api_key` varchar(255) DEFAULT NULL,
  `max_bandwidth` int(11) DEFAULT 1000,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- Table structure for `subscription_alerts`
CREATE TABLE `subscription_alerts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `alert_type` enum('category','keyword','author','location') NOT NULL,
  `alert_value` varchar(255) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `notification_frequency` enum('immediate','hourly','daily') DEFAULT 'immediate',
  `last_sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_subscription_user` (`user_id`),
  KEY `idx_subscription_type` (`alert_type`),
  KEY `idx_subscription_active` (`is_active`),
  CONSTRAINT `subscription_alerts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- Table structure for `tags`
CREATE TABLE `tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `slug` varchar(50) NOT NULL,
  `color` varchar(7) DEFAULT '#007bff',
  `description` text DEFAULT NULL,
  `usage_count` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_tags_slug` (`slug`),
  KEY `idx_tags_status` (`status`),
  KEY `idx_tags_usage` (`usage_count`)
) ENGINE=InnoDB AUTO_INCREMENT=135 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `tags` VALUES ('2', 'Sports', 'sports', '#28a745', 'Sports news and match results', '0', 'active', '2026-03-06 04:00:31', '2026-03-06 04:00:31';
INSERT INTO `tags` VALUES ('3', 'Technology', 'technology', '#007bff', 'Technology news and gadgets', '0', 'active', '2026-03-06 04:00:31', '2026-03-06 04:00:31';
INSERT INTO `tags` VALUES ('4', 'Entertainment', 'entertainment', '#fd7e14', 'Entertainment and celebrity news', '0', 'active', '2026-03-06 04:00:31', '2026-03-06 04:00:31';
INSERT INTO `tags` VALUES ('5', 'Business', 'business', '#6f42c1', 'Business and financial news', '0', 'active', '2026-03-06 04:00:31', '2026-03-06 04:00:31';
INSERT INTO `tags` VALUES ('6', 'Health', 'health', '#20c997', 'Health and medical news', '0', 'active', '2026-03-06 04:00:31', '2026-03-06 04:00:31';
INSERT INTO `tags` VALUES ('7', 'Education', 'education', '#e83e8c', 'Education and learning news', '0', 'active', '2026-03-06 04:00:31', '2026-03-06 04:00:31';
INSERT INTO `tags` VALUES ('9', 'Breaking', 'breaking', '#dc3545', 'Breaking news stories', '0', 'active', '2026-03-06 04:00:31', '2026-03-06 04:00:31';
INSERT INTO `tags` VALUES ('10', 'International', 'international', '#6c757d', 'International news and events', '0', 'active', '2026-03-06 04:00:31', '2026-03-06 04:00:31';
INSERT INTO `tags` VALUES ('12', 'CNN', 'cnn', '#dc3545', 'CNN News International', '0', 'active', '2026-03-09 21:49:44', '2026-03-09 21:49:44';
INSERT INTO `tags` VALUES ('13', 'CNN International', 'cnn-international', '#dc3545', 'CNN International News', '0', 'active', '2026-03-09 21:49:44', '2026-03-09 21:49:44';
INSERT INTO `tags` VALUES ('14', 'CNN Politics', 'cnn-politics', '#dc3545', 'CNN Political News', '0', 'active', '2026-03-09 21:49:44', '2026-03-09 21:49:44';
INSERT INTO `tags` VALUES ('15', 'CNN Business', 'cnn-business', '#dc3545', 'CNN Business News', '0', 'active', '2026-03-09 21:49:44', '2026-03-09 21:49:44';
INSERT INTO `tags` VALUES ('16', 'CNN Health', 'cnn-health', '#28a745', 'CNN Health News', '0', 'active', '2026-03-09 21:49:44', '2026-03-09 21:49:44';
INSERT INTO `tags` VALUES ('17', 'CNN Entertainment', 'cnn-entertainment', '#fd7e14', 'CNN Entertainment News', '0', 'active', '2026-03-09 21:49:44', '2026-03-09 21:49:44';
INSERT INTO `tags` VALUES ('18', 'CNN Sports', 'cnn-sports', '#007bff', 'CNN Sports News', '0', 'active', '2026-03-09 21:49:44', '2026-03-09 21:49:44';
INSERT INTO `tags` VALUES ('19', 'CNN Technology', 'cnn-technology', '#6f42c1', 'CNN Technology News', '0', 'active', '2026-03-09 21:49:44', '2026-03-09 21:49:44';
INSERT INTO `tags` VALUES ('20', 'ARY', 'ary', '#FF6B35', 'ARY Digital News', '0', 'active', '2026-03-09 22:12:00', '2026-03-09 22:12:00';
INSERT INTO `tags` VALUES ('21', 'ARY International', 'ary-international', '#FF6B35', 'ARY International News', '0', 'active', '2026-03-09 22:12:00', '2026-03-09 22:12:00';
INSERT INTO `tags` VALUES ('22', 'ARY Politics', 'ary-politics', '#F7931E', 'ARY Political News', '0', 'active', '2026-03-09 22:12:00', '2026-03-09 22:12:00';
INSERT INTO `tags` VALUES ('23', 'ARY Business', 'ary-business', '#28a745', 'ARY Business News', '0', 'active', '2026-03-09 22:12:00', '2026-03-09 22:12:00';
INSERT INTO `tags` VALUES ('24', 'ARY Entertainment', 'ary-entertainment', '#fd7e14', 'ARY Entertainment News', '0', 'active', '2026-03-09 22:12:00', '2026-03-09 22:12:00';
INSERT INTO `tags` VALUES ('25', 'ARY Sports', 'ary-sports', '#007bff', 'ARY Sports News', '0', 'active', '2026-03-09 22:12:00', '2026-03-09 22:12:00';
INSERT INTO `tags` VALUES ('26', 'ARY Technology', 'ary-technology', '#6f42c1', 'ARY Technology News', '0', 'active', '2026-03-09 22:12:00', '2026-03-09 22:12:00';
INSERT INTO `tags` VALUES ('27', 'ARY Pakistan', 'ary-pakistan', '#dc3545', 'ARY Pakistan News', '0', 'active', '2026-03-09 22:12:00', '2026-03-09 22:12:00';
INSERT INTO `tags` VALUES ('28', 'ARY Metro', 'ary-metro', '#20c997', 'ARY Metro News', '0', 'active', '2026-03-09 22:12:00', '2026-03-09 22:12:00';
INSERT INTO `tags` VALUES ('29', 'ARY News', 'ary-news', '#FF6B35', 'ARY News Updates', '0', 'active', '2026-03-09 22:12:00', '2026-03-09 22:12:00';
INSERT INTO `tags` VALUES ('30', 'Breaking News', 'breaking-news', '#dc3545', 'Urgent breaking news stories', '0', 'active', '2026-03-12 16:56:57', '2026-03-12 16:56:57';
INSERT INTO `tags` VALUES ('31', 'Politics', 'politics', '#28a745', 'Political news and updates', '0', 'active', '2026-03-12 16:56:57', '2026-03-12 16:56:57';
INSERT INTO `tags` VALUES ('32', 'Pakistan', 'pakistan', '#198754', 'Pakistan-specific news', '0', 'active', '2026-03-12 16:56:57', '2026-03-12 16:56:57';


-- Table structure for `trending_tags`
CREATE TABLE `trending_tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tag_id` int(11) NOT NULL,
  `trend_date` date NOT NULL,
  `mention_count` int(11) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_tag_date` (`tag_id`,`trend_date`),
  KEY `idx_trending_tags_date` (`trend_date`),
  KEY `idx_trending_tags_count` (`mention_count`),
  CONSTRAINT `trending_tags_ibfk_1` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- Table structure for `trusted_sources`
CREATE TABLE `trusted_sources` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `source_name` varchar(255) NOT NULL,
  `source_url` varchar(500) NOT NULL,
  `domain_name` varchar(255) NOT NULL,
  `source_type` enum('NEWS_MEDIA','GOVERNMENT','ACADEMIC','FACT_CHECK','OFFICIAL','SOCIAL_MEDIA','BLOG','UNKNOWN') DEFAULT 'UNKNOWN',
  `credibility_tier` enum('TIER_1','TIER_2','TIER_3','TIER_4','TIER_5') DEFAULT 'TIER_3',
  `trust_score` decimal(5,2) NOT NULL DEFAULT 50.00,
  `reliability_score` decimal(5,2) NOT NULL DEFAULT 50.00,
  `accuracy_score` decimal(5,2) NOT NULL DEFAULT 50.00,
  `verified` tinyint(1) DEFAULT 0,
  `verification_date` datetime DEFAULT NULL,
  `verification_method` varchar(50) DEFAULT NULL,
  `reputation_score` decimal(5,2) DEFAULT NULL,
  `fact_checking_frequency` int(11) DEFAULT 0,
  `correction_frequency` int(11) DEFAULT 0,
  `country` varchar(2) DEFAULT NULL,
  `language` varchar(10) DEFAULT 'en',
  `region` varchar(100) DEFAULT NULL,
  `owner` varchar(255) DEFAULT NULL,
  `contact_email` varchar(255) DEFAULT NULL,
  `founded_year` year(4) DEFAULT NULL,
  `articles_analyzed` int(11) DEFAULT 0,
  `average_credibility` decimal(5,2) DEFAULT NULL,
  `flag_count` int(11) DEFAULT 0,
  `active` tinyint(1) DEFAULT 1,
  `blacklisted` tinyint(1) DEFAULT 0,
  `whitelist_priority` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `source_name` (`source_name`),
  UNIQUE KEY `domain_name` (`domain_name`),
  KEY `idx_domain_name` (`domain_name`),
  KEY `idx_trust_score` (`trust_score`),
  KEY `idx_credibility_tier` (`credibility_tier`),
  KEY `idx_active` (`active`),
  KEY `idx_blacklisted` (`blacklisted`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `trusted_sources` VALUES ('1', 'Reuters', 'https://www.reuters.com', 'reuters.com', 'NEWS_MEDIA', 'TIER_1', '95.00', '94.00', '96.00', '1', NULL, NULL, NULL, '0', '0', NULL, 'en', NULL, NULL, NULL, NULL, '0', NULL, '0', '1', '0', '0', '2026-03-16 21:02:52', '2026-03-16 21:02:52';
INSERT INTO `trusted_sources` VALUES ('2', 'Associated Press', 'https://www.apnews.com', 'apnews.com', 'NEWS_MEDIA', 'TIER_1', '94.00', '95.00', '93.00', '1', NULL, NULL, NULL, '0', '0', NULL, 'en', NULL, NULL, NULL, NULL, '0', NULL, '0', '1', '0', '0', '2026-03-16 21:02:52', '2026-03-16 21:02:52';
INSERT INTO `trusted_sources` VALUES ('3', 'BBC News', 'https://www.bbc.com/news', 'bbc.com', 'NEWS_MEDIA', 'TIER_1', '92.00', '91.00', '93.00', '1', NULL, NULL, NULL, '0', '0', NULL, 'en', NULL, NULL, NULL, NULL, '0', NULL, '0', '1', '0', '0', '2026-03-16 21:02:52', '2026-03-16 21:02:52';
INSERT INTO `trusted_sources` VALUES ('4', 'Al Jazeera', 'https://www.aljazeera.com', 'aljazeera.com', 'NEWS_MEDIA', 'TIER_1', '88.00', '87.00', '89.00', '1', NULL, NULL, NULL, '0', '0', NULL, 'en', NULL, NULL, NULL, NULL, '0', NULL, '0', '1', '0', '0', '2026-03-16 21:02:52', '2026-03-16 21:02:52';
INSERT INTO `trusted_sources` VALUES ('5', 'Dawn News', 'https://www.dawn.com', 'dawn.com', 'NEWS_MEDIA', 'TIER_1', '85.00', '84.00', '86.00', '1', NULL, NULL, NULL, '0', '0', NULL, 'en', NULL, NULL, NULL, NULL, '0', NULL, '0', '1', '0', '0', '2026-03-16 21:02:52', '2026-03-16 21:02:52';
INSERT INTO `trusted_sources` VALUES ('6', 'Geo TV', 'https://www.geo.tv', 'geo.tv', 'NEWS_MEDIA', 'TIER_2', '82.00', '81.00', '83.00', '0', NULL, NULL, NULL, '0', '0', NULL, 'en', NULL, NULL, NULL, NULL, '0', NULL, '0', '1', '0', '0', '2026-03-16 21:02:52', '2026-03-16 21:02:52';
INSERT INTO `trusted_sources` VALUES ('7', 'ARY News', 'https://www.arynews.tv', 'arynews.tv', 'NEWS_MEDIA', 'TIER_2', '80.00', '79.00', '81.00', '0', NULL, NULL, NULL, '0', '0', NULL, 'en', NULL, NULL, NULL, NULL, '0', NULL, '0', '1', '0', '0', '2026-03-16 21:02:52', '2026-03-16 21:02:52';
INSERT INTO `trusted_sources` VALUES ('8', 'Pakistan Government', 'https://www.gov.pk', 'gov.pk', 'GOVERNMENT', 'TIER_1', '90.00', '88.00', '92.00', '1', NULL, NULL, NULL, '0', '0', NULL, 'en', NULL, NULL, NULL, NULL, '0', NULL, '0', '1', '0', '0', '2026-03-16 21:02:52', '2026-03-16 21:02:52';
INSERT INTO `trusted_sources` VALUES ('9', 'Snopes', 'https://www.snopes.com', 'snopes.com', 'FACT_CHECK', 'TIER_1', '93.00', '94.00', '92.00', '1', NULL, NULL, NULL, '0', '0', NULL, 'en', NULL, NULL, NULL, NULL, '0', NULL, '0', '1', '0', '0', '2026-03-16 21:02:52', '2026-03-16 21:02:52';
INSERT INTO `trusted_sources` VALUES ('10', 'FactCheck.org', 'https://www.factcheck.org', 'factcheck.org', 'FACT_CHECK', 'TIER_1', '94.00', '93.00', '95.00', '1', NULL, NULL, NULL, '0', '0', NULL, 'en', NULL, NULL, NULL, NULL, '0', NULL, '0', '1', '0', '0', '2026-03-16 21:02:52', '2026-03-16 21:02:52';


-- Table structure for `user_activity`
CREATE TABLE `user_activity` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `news_id` int(11) DEFAULT NULL,
  `action` enum('view','share','comment','bookmark','like','dislike') NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `referrer` varchar(255) DEFAULT NULL,
  `duration` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_activity_user` (`user_id`),
  KEY `idx_activity_news` (`news_id`),
  KEY `idx_activity_action` (`action`),
  KEY `idx_activity_date` (`created_at`),
  CONSTRAINT `user_activity_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `user_activity_ibfk_2` FOREIGN KEY (`news_id`) REFERENCES `news` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- Table structure for `user_fake_news_reports`
CREATE TABLE `user_fake_news_reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `news_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `reporter_ip` varchar(45) DEFAULT NULL,
  `report_reason` enum('MISLEADING','FALSE_INFORMATION','BIASED','CLICKBAIT','SPAM','OTHER') NOT NULL,
  `report_details` text DEFAULT NULL,
  `evidence_urls` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`evidence_urls`)),
  `report_status` enum('PENDING','REVIEWING','VALIDATED','DISMISSED') DEFAULT 'PENDING',
  `admin_notes` text DEFAULT NULL,
  `credibility_impact` decimal(5,2) DEFAULT NULL,
  `similar_reports_count` int(11) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `reviewed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_news_id` (`news_id`),
  KEY `idx_report_reason` (`report_reason`),
  KEY `idx_report_status` (`report_status`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `user_fake_news_reports_ibfk_1` FOREIGN KEY (`news_id`) REFERENCES `news` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- Table structure for `users`
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','editor','reporter') DEFAULT 'reporter',
  `status` enum('active','blocked') DEFAULT 'active',
  `bio` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_users_email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `users` VALUES ('1', 'Admin', 'admin@pklivenews.com', NULL, '$2y$10$bWzvZaXGYej46rJ3VGQk.u0Q5YARnbIh.M.IfPphQznyEZ7EIgDBK', 'admin', 'active', NULL, NULL, '2026-03-04 10:28:28', '2026-03-04 14:06:54';
INSERT INTO `users` VALUES ('3', 'Admin', 'ibraheem47074@gmail.com', 'admin@pklivenews.com', '$2y$10$sUYJarM7dEG5lYRZfHm.gu9sp3vHuwZISIHsfwBilw0l9JD/8ecba', 'reporter', 'active', '', 'uploads/users/69b17a03f071d.jpg', '2026-03-06 17:57:35', '2026-03-11 19:22:43';
INSERT INTO `users` VALUES ('5', 'm kashif', 'kashifkhantkking94@gmail.com', '03308181569', '$2y$10$bMApT3cX99MzUjaOFKXfKutW9FrdJJVWAfl2/QEnWFMxcwoUAQMAi', '', 'active', NULL, NULL, '2026-03-12 10:08:50', '2026-03-12 10:08:50';
INSERT INTO `users` VALUES ('6', 'John Reporter', 'reporter@pklivenews.com', NULL, '$2y$10$xZXNO4OOAcZ0ciN8BeaqRexb33d.wGjs.KTNGHzvss3q8fSZXU7CO', 'reporter', 'active', NULL, NULL, '2026-03-13 05:36:30', '2026-03-13 05:36:30';
INSERT INTO `users` VALUES ('7', 'Sarah Editor', 'editor@pklivenews.com', NULL, '$2y$10$bfSTHzqDCUHXi5zspb4Kzemx6cDfOQlnoZjO.O8zV4VRAadzKMrHu', 'editor', 'active', NULL, NULL, '2026-03-13 05:36:30', '2026-03-13 05:36:30';
INSERT INTO `users` VALUES ('8', 'Mike Reporter', 'mike@pklivenews.com', NULL, '$2y$10$u2xriAZlx0sCzgRWbuNyw.w6ZSUK2Jj.zP9rjBCc8iDaPkdKBZWiS', 'reporter', 'active', NULL, NULL, '2026-03-13 05:36:30', '2026-03-13 05:36:30';



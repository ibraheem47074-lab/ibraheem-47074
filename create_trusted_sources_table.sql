-- Create trusted_sources table for AI fake news detection
CREATE TABLE IF NOT EXISTS `trusted_sources` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `domain_name` varchar(255) NOT NULL COMMENT 'Domain name of the news source',
  `source_name` varchar(255) DEFAULT NULL COMMENT 'Full name of the news source',
  `trust_score` decimal(3,2) DEFAULT '0.50' COMMENT 'Trust score between 0.00 and 1.00',
  `reputation_score` decimal(3,2) DEFAULT '0.50' COMMENT 'Reputation score between 0.00 and 1.00',
  `verified` tinyint(1) DEFAULT '0' COMMENT 'Whether the source is verified',
  `fact_check_rating` enum('high','medium','low','unknown') DEFAULT 'unknown' COMMENT 'Fact checking rating',
  `bias_rating` enum('left','center-left','center','center-right','right','unknown') DEFAULT 'unknown' COMMENT 'Political bias rating',
  `country` varchar(100) DEFAULT NULL COMMENT 'Country of origin',
  `language` varchar(10) DEFAULT 'en' COMMENT 'Primary language',
  `category` varchar(100) DEFAULT NULL COMMENT 'Primary news category',
  `description` text DEFAULT NULL COMMENT 'Description of the news source',
  `contact_info` varchar(500) DEFAULT NULL COMMENT 'Contact information',
  `social_media_links` json DEFAULT NULL COMMENT 'Social media links',
  `alexa_rank` int(11) DEFAULT NULL COMMENT 'Alexa traffic rank',
  `monthly_visitors` int(11) DEFAULT NULL COMMENT 'Estimated monthly visitors',
  `founded_year` int(4) DEFAULT NULL COMMENT 'Year the source was founded',
  `owner` varchar(255) DEFAULT NULL COMMENT 'Owner or parent company',
  `active` tinyint(1) DEFAULT '1' COMMENT 'Whether the source is active in the system',
  `last_verified` timestamp NULL DEFAULT NULL COMMENT 'Last time the source was verified',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_domain` (`domain_name`),
  KEY `idx_trust_score` (`trust_score`),
  KEY `idx_verified` (`verified`),
  KEY `idx_active` (`active`),
  KEY `idx_country` (`country`),
  KEY `idx_bias_rating` (`bias_rating`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert trusted news sources with high credibility
INSERT IGNORE INTO `trusted_sources` 
(`domain_name`, `source_name`, `trust_score`, `reputation_score`, `verified`, `fact_check_rating`, `bias_rating`, `country`, `language`, `category`, `description`, `active`) VALUES

-- International News Agencies
('reuters.com', 'Reuters', 0.95, 0.92, 1, 'high', 'center', 'United Kingdom', 'en', 'International', 'International news agency with high journalistic standards', 1),
('ap.org', 'Associated Press', 0.94, 0.91, 1, 'high', 'center', 'United States', 'en', 'International', 'US-based nonprofit news cooperative', 1),
('bbc.com', 'BBC News', 0.92, 0.89, 1, 'high', 'center-left', 'United Kingdom', 'en', 'International', 'British Broadcasting Corporation news service', 1),
('npr.org', 'NPR', 0.89, 0.87, 1, 'high', 'center', 'United States', 'en', 'International', 'National Public Radio - nonprofit news organization', 1),

-- Major US News Outlets
('nytimes.com', 'The New York Times', 0.88, 0.85, 1, 'high', 'center-left', 'United States', 'en', 'International', 'American newspaper of record', 1),
('washingtonpost.com', 'The Washington Post', 0.87, 0.84, 1, 'high', 'center-left', 'United States', 'en', 'International', 'Major American newspaper', 1),
('wsj.com', 'The Wall Street Journal', 0.86, 0.83, 1, 'high', 'center-right', 'United States', 'en', 'Business', 'American business-focused newspaper', 1),
('bloomberg.com', 'Bloomberg', 0.85, 0.82, 1, 'high', 'center', 'United States', 'en', 'Business', 'Financial news and data company', 1),

-- TV News Networks
('cnn.com', 'CNN', 0.78, 0.75, 1, 'medium', 'center-left', 'United States', 'en', 'International', 'Cable News Network', 1),
('msnbc.com', 'MSNBC', 0.75, 0.72, 1, 'medium', 'left', 'United States', 'en', 'International', 'American news cable channel', 1),
('foxnews.com', 'Fox News', 0.72, 0.69, 1, 'medium', 'right', 'United States', 'en', 'International', 'American conservative news channel', 1),
('cbsnews.com', 'CBS News', 0.80, 0.77, 1, 'medium', 'center', 'United States', 'en', 'International', 'American news broadcast network', 1),
('nbcnews.com', 'NBC News', 0.81, 0.78, 1, 'medium', 'center', 'United States', 'en', 'International', 'American news broadcast network', 1),
('abcnews.go.com', 'ABC News', 0.79, 0.76, 1, 'medium', 'center', 'United States', 'en', 'International', 'American news broadcast network', 1),

-- International Sources
('theguardian.com', 'The Guardian', 0.84, 0.81, 1, 'high', 'center-left', 'United Kingdom', 'en', 'International', 'British daily newspaper', 1),
('aljazeera.com', 'Al Jazeera', 0.77, 0.74, 1, 'medium', 'center', 'Qatar', 'en', 'International', 'Qatar-based international news network', 1),
('dw.com', 'Deutsche Welle', 0.82, 0.79, 1, 'high', 'center', 'Germany', 'en', 'International', 'German public international broadcaster', 1),
('france24.com', 'France 24', 0.80, 0.77, 1, 'medium', 'center', 'France', 'en', 'International', 'French international news network', 1),
('euronews.com', 'EuroNews', 0.79, 0.76, 1, 'medium', 'center', 'France', 'en', 'International', 'European news network', 1),

-- South Asian Sources
('dawn.com', 'Dawn', 0.75, 0.72, 1, 'medium', 'center', 'Pakistan', 'en', 'National', 'Pakistani English-language newspaper', 1),
('geo.tv', 'Geo News', 0.70, 0.67, 1, 'medium', 'center-right', 'Pakistan', 'en', 'National', 'Pakistani news channel', 1),
('tribune.com.pk', 'Express Tribune', 0.72, 0.69, 1, 'medium', 'center', 'Pakistan', 'en', 'National', 'Pakistani English-language newspaper', 1),
('arynews.tv', 'ARY News', 0.68, 0.65, 1, 'medium', 'center-right', 'Pakistan', 'en', 'National', 'Pakistani news channel', 1),
('ndtv.com', 'NDTV', 0.76, 0.73, 1, 'medium', 'center', 'India', 'en', 'National', 'Indian news network', 1),
('timesofindia.indiatimes.com', 'Times of India', 0.74, 0.71, 1, 'medium', 'center-right', 'India', 'en', 'National', 'Indian newspaper', 1),
('hindustantimes.com', 'Hindustan Times', 0.73, 0.70, 1, 'medium', 'center', 'India', 'en', 'National', 'Indian newspaper', 1),

-- Business and Financial News
('cnbc.com', 'CNBC', 0.83, 0.80, 1, 'high', 'center', 'United States', 'en', 'Business', 'American business news channel', 1),
('forbes.com', 'Forbes', 0.81, 0.78, 1, 'medium', 'center-right', 'United States', 'en', 'Business', 'American business magazine', 1),
('fortune.com', 'Fortune', 0.80, 0.77, 1, 'medium', 'center', 'United States', 'en', 'Business', 'American business magazine', 1),

-- Technology News
('techcrunch.com', 'TechCrunch', 0.82, 0.79, 1, 'medium', 'center', 'United States', 'en', 'Technology', 'Technology news website', 1),
('wired.com', 'Wired', 0.84, 0.81, 1, 'high', 'center', 'United States', 'en', 'Technology', 'Technology magazine', 1),
('theverge.com', 'The Verge', 0.81, 0.78, 1, 'medium', 'center', 'United States', 'en', 'Technology', 'Technology news website', 1),

-- Sports News
('espn.com', 'ESPN', 0.85, 0.82, 1, 'high', 'center', 'United States', 'en', 'Sports', 'American sports network', 1),
('bbc.co.uk/sport', 'BBC Sport', 0.87, 0.84, 1, 'high', 'center', 'United Kingdom', 'en', 'Sports', 'BBC sports division', 1);

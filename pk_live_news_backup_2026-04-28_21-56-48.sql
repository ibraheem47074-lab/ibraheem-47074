-- PK Live News Database Export
-- Generated on: 2026-04-28 21:56:48
-- Database: pk_live_news

CREATE TABLE `ad_clicks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ad_id` int(11) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `page_url` varchar(500) DEFAULT NULL,
  `click_time` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `ad_id` (`ad_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `ad_clicks` VALUES("1","6","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 OPR/130.0.0.0","/PK-LIVE%20NEWS/track-ad-click.php?ad_id=6&redirect=https%3A%2F%2Fexample.com","2026-04-29 00:17:01");


CREATE TABLE `ad_impressions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ad_id` int(11) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `page_url` varchar(500) DEFAULT NULL,
  `impression_time` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `ad_id` (`ad_id`)
) ENGINE=InnoDB AUTO_INCREMENT=173 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `ad_impressions` VALUES("1","1","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0","/PK-LIVE%20NEWS/index.php","2026-04-27 00:01:37");
INSERT INTO `ad_impressions` VALUES("2","1","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 OPR/130.0.0.0","/PK-LIVE%20NEWS/index.php?refresh_weather=1","2026-04-27 00:01:52");
INSERT INTO `ad_impressions` VALUES("3","4","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 OPR/130.0.0.0","/PK-LIVE%20NEWS/index.php?refresh_weather=1","2026-04-27 00:12:55");
INSERT INTO `ad_impressions` VALUES("4","4","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0","/PK-LIVE%20NEWS/","2026-04-27 00:33:16");
INSERT INTO `ad_impressions` VALUES("5","1","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0","/PK-LIVE%20NEWS/index.php?refresh_weather=1","2026-04-27 01:01:11");
INSERT INTO `ad_impressions` VALUES("6","1","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 OPR/130.0.0.0","/PK-LIVE%20NEWS/index.php","2026-04-27 01:17:13");
INSERT INTO `ad_impressions` VALUES("7","4","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 OPR/130.0.0.0","/PK-LIVE%20NEWS/index.php","2026-04-27 01:17:46");
INSERT INTO `ad_impressions` VALUES("8","4","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 OPR/130.0.0.0","/PK-LIVE%20NEWS/index.php","2026-04-27 01:18:37");
INSERT INTO `ad_impressions` VALUES("9","1","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 OPR/130.0.0.0","/PK-LIVE%20NEWS/index.php","2026-04-27 01:21:33");
INSERT INTO `ad_impressions` VALUES("10","4","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0","/PK-LIVE%20NEWS/index.php?refresh_weather=1","2026-04-27 01:24:05");
INSERT INTO `ad_impressions` VALUES("11","21","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0","/PK-LIVE%20NEWS/live.php","2026-04-27 01:24:17");
INSERT INTO `ad_impressions` VALUES("12","4","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0","/PK-LIVE%20NEWS/index.php","2026-04-27 01:25:24");
INSERT INTO `ad_impressions` VALUES("13","4","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 OPR/130.0.0.0","/PK-LIVE%20NEWS/index.php","2026-04-27 01:27:16");
INSERT INTO `ad_impressions` VALUES("14","1","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0","/PK-LIVE%20NEWS/index.php","2026-04-27 01:27:28");
INSERT INTO `ad_impressions` VALUES("15","22","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 OPR/130.0.0.0","/PK-LIVE%20NEWS/index.php?refresh_weather=1","2026-04-27 01:31:46");
INSERT INTO `ad_impressions` VALUES("16","1","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 OPR/130.0.0.0","/PK-LIVE%20NEWS/index.php","2026-04-27 01:31:47");
INSERT INTO `ad_impressions` VALUES("17","22","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 OPR/130.0.0.0","/PK-LIVE%20NEWS/index.php","2026-04-27 01:33:06");
INSERT INTO `ad_impressions` VALUES("18","1","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 OPR/130.0.0.0","/PK-LIVE%20NEWS/index.php","2026-04-27 01:33:31");
INSERT INTO `ad_impressions` VALUES("19","4","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36","/PK-LIVE%20NEWS/","2026-04-27 01:34:04");
INSERT INTO `ad_impressions` VALUES("20","1","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36","/PK-LIVE%20NEWS/index.php","2026-04-27 01:34:08");
INSERT INTO `ad_impressions` VALUES("21","1","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36","/PK-LIVE%20NEWS/index.php","2026-04-27 01:34:25");
INSERT INTO `ad_impressions` VALUES("22","22","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0","/PK-LIVE%20NEWS/index.php","2026-04-27 01:34:49");
INSERT INTO `ad_impressions` VALUES("23","4","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36","/PK-LIVE%20NEWS/index.php?refresh_weather=1","2026-04-27 12:29:10");
INSERT INTO `ad_impressions` VALUES("24","22","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 OPR/130.0.0.0","/PK-LIVE%20NEWS/index.php?refresh_weather=1","2026-04-27 12:29:10");
INSERT INTO `ad_impressions` VALUES("25","4","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 OPR/130.0.0.0","/PK-LIVE%20NEWS/index.php?refresh_weather=1","2026-04-27 12:29:20");
INSERT INTO `ad_impressions` VALUES("26","4","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 OPR/130.0.0.0","/PK-LIVE%20NEWS/news.php?slug=this-is-sport-gala-2025","2026-04-27 12:35:40");
INSERT INTO `ad_impressions` VALUES("27","1","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 OPR/130.0.0.0","/PK-LIVE%20NEWS/index.php","2026-04-27 12:35:42");
INSERT INTO `ad_impressions` VALUES("28","4","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0","/PK-LIVE%20NEWS/index.php?refresh_weather=1","2026-04-27 12:42:55");
INSERT INTO `ad_impressions` VALUES("29","1","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0","/PK-LIVE%20NEWS/index.php","2026-04-27 12:42:55");
INSERT INTO `ad_impressions` VALUES("30","1","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0","/PK-LIVE%20NEWS/index.php","2026-04-27 12:44:15");
INSERT INTO `ad_impressions` VALUES("31","1","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 OPR/130.0.0.0","/PK-LIVE%20NEWS/index.php?refresh_weather=1","2026-04-27 12:45:59");
INSERT INTO `ad_impressions` VALUES("32","1","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 OPR/130.0.0.0","/PK-LIVE%20NEWS/index.php","2026-04-27 12:47:43");
INSERT INTO `ad_impressions` VALUES("33","1","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 OPR/130.0.0.0","/PK-LIVE%20NEWS/index.php","2026-04-27 12:47:44");
INSERT INTO `ad_impressions` VALUES("34","4","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 OPR/130.0.0.0","/PK-LIVE%20NEWS/index.php","2026-04-27 12:48:10");
INSERT INTO `ad_impressions` VALUES("35","22","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 OPR/130.0.0.0","/PK-LIVE%20NEWS/index.php","2026-04-27 12:50:46");
INSERT INTO `ad_impressions` VALUES("36","1","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36","/PK-LIVE%20NEWS/","2026-04-27 12:51:24");
INSERT INTO `ad_impressions` VALUES("37","4","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 OPR/130.0.0.0","/PK-LIVE%20NEWS/news.php?slug=trump-s-strait-of-hormuz-blockade-threat-raises-risks-and-leaves-predicaments-unchanged","2026-04-27 12:52:18");
INSERT INTO `ad_impressions` VALUES("38","1","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 OPR/130.0.0.0","/PK-LIVE%20NEWS/index.php","2026-04-27 12:52:20");
INSERT INTO `ad_impressions` VALUES("39","22","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 OPR/130.0.0.0","/PK-LIVE%20NEWS/index.php","2026-04-27 12:58:22");
INSERT INTO `ad_impressions` VALUES("40","22","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0","/PK-LIVE%20NEWS/","2026-04-27 13:01:30");
INSERT INTO `ad_impressions` VALUES("41","4","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36","/PK-LIVE%20NEWS/index.php?refresh_weather=1","2026-04-27 13:01:43");
INSERT INTO `ad_impressions` VALUES("42","4","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 OPR/130.0.0.0","/PK-LIVE%20NEWS/","2026-04-27 21:48:58");
INSERT INTO `ad_impressions` VALUES("43","4","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36","/PK-LIVE%20NEWS/index.php?refresh_weather=1","2026-04-27 21:49:46");
INSERT INTO `ad_impressions` VALUES("44","4","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0","/PK-LIVE%20NEWS/index.php?refresh_weather=1","2026-04-27 21:53:51");
INSERT INTO `ad_impressions` VALUES("45","1","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36","/PK-LIVE%20NEWS/","2026-04-27 21:55:33");
INSERT INTO `ad_impressions` VALUES("46","4","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0","/PK-LIVE%20NEWS/","2026-04-27 21:58:36");
INSERT INTO `ad_impressions` VALUES("47","1","::1","Mozilla/5.0 (Linux; Android 8.0.0; SM-G955U Build/R16NW) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36","/PK-LIVE%20NEWS/index.php?refresh_weather=1","2026-04-27 21:59:07");
INSERT INTO `ad_impressions` VALUES("48","1","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0","/PK-LIVE%20NEWS/","2026-04-27 22:00:46");
INSERT INTO `ad_impressions` VALUES("49","4","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36","/PK-LIVE%20NEWS/index.php?refresh_weather=1","2026-04-27 22:05:35");
INSERT INTO `ad_impressions` VALUES("50","1","::1","Mozilla/5.0 (Linux; Android 8.0.0; SM-G955U Build/R16NW) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36","/PK-LIVE%20NEWS/","2026-04-27 22:13:01");
INSERT INTO `ad_impressions` VALUES("51","22","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0","/PK-LIVE%20NEWS/index.php?refresh_weather=1","2026-04-27 22:14:35");
INSERT INTO `ad_impressions` VALUES("52","22","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0","/PK-LIVE%20NEWS/","2026-04-27 22:14:49");
INSERT INTO `ad_impressions` VALUES("53","1","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36","/PK-LIVE%20NEWS/index.php?refresh_weather=1","2026-04-27 22:16:17");
INSERT INTO `ad_impressions` VALUES("54","1","::1","Mozilla/5.0 (Linux; Android 8.0.0; SM-G955U Build/R16NW) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36","/PK-LIVE%20NEWS/","2026-04-27 22:16:37");
INSERT INTO `ad_impressions` VALUES("55","4","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0","/PK-LIVE%20NEWS/","2026-04-27 22:19:23");
INSERT INTO `ad_impressions` VALUES("56","22","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0","/PK-LIVE%20NEWS/","2026-04-27 22:23:43");
INSERT INTO `ad_impressions` VALUES("57","4","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0","/PK-LIVE%20NEWS/","2026-04-27 22:23:54");
INSERT INTO `ad_impressions` VALUES("58","22","::1","Mozilla/5.0 (Linux; Android 8.0.0; SM-G955U Build/R16NW) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36","/PK-LIVE%20NEWS/","2026-04-27 22:26:16");
INSERT INTO `ad_impressions` VALUES("59","4","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36","/PK-LIVE%20NEWS/index.php?refresh_weather=1","2026-04-27 22:26:18");
INSERT INTO `ad_impressions` VALUES("60","4","::1","Mozilla/5.0 (Linux; Android 8.0.0; SM-G955U Build/R16NW) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36","/PK-LIVE%20NEWS/","2026-04-27 22:26:26");
INSERT INTO `ad_impressions` VALUES("61","4","::1","Mozilla/5.0 (Linux; Android 8.0.0; SM-G955U Build/R16NW) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36","/PK-LIVE%20NEWS/","2026-04-27 22:27:51");
INSERT INTO `ad_impressions` VALUES("62","22","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0","/PK-LIVE%20NEWS/","2026-04-27 22:30:17");
INSERT INTO `ad_impressions` VALUES("63","22","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0","/PK-LIVE%20NEWS/","2026-04-27 22:30:30");
INSERT INTO `ad_impressions` VALUES("64","4","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0","/PK-LIVE%20NEWS/","2026-04-27 22:30:44");
INSERT INTO `ad_impressions` VALUES("65","4","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36","/PK-LIVE%20NEWS/index.php?refresh_weather=1","2026-04-27 22:38:22");
INSERT INTO `ad_impressions` VALUES("66","22","::1","Mozilla/5.0 (Linux; Android 8.0.0; SM-G955U Build/R16NW) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36","/PK-LIVE%20NEWS/index.php?refresh_weather=1","2026-04-27 22:39:58");
INSERT INTO `ad_impressions` VALUES("67","4","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0","/PK-LIVE%20NEWS/","2026-04-27 22:40:04");
INSERT INTO `ad_impressions` VALUES("68","4","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36","/PK-LIVE%20NEWS/index.php?refresh_weather=1","2026-04-28 00:03:42");
INSERT INTO `ad_impressions` VALUES("69","22","::1","Mozilla/5.0 (Linux; Android 8.0.0; SM-G955U Build/R16NW) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36","/PK-LIVE%20NEWS/index.php?refresh_weather=1","2026-04-28 00:03:56");
INSERT INTO `ad_impressions` VALUES("70","1","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0","/PK-LIVE%20NEWS/","2026-04-28 00:14:13");
INSERT INTO `ad_impressions` VALUES("71","4","::1","Mozilla/5.0 (Linux; Android 8.0.0; SM-G955U Build/R16NW) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36","/PK-LIVE%20NEWS/index.php?refresh_weather=1","2026-04-28 00:14:20");
INSERT INTO `ad_impressions` VALUES("72","22","::1","Mozilla/5.0 (Linux; Android 8.0.0; SM-G955U Build/R16NW) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36","/PK-LIVE%20NEWS/","2026-04-28 00:15:41");
INSERT INTO `ad_impressions` VALUES("73","1","::1","Mozilla/5.0 (Linux; Android 8.0.0; SM-G955U Build/R16NW) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36","/PK-LIVE%20NEWS/","2026-04-28 00:16:30");
INSERT INTO `ad_impressions` VALUES("74","1","::1","Mozilla/5.0 (Linux; Android 8.0.0; SM-G955U Build/R16NW) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36","/PK-LIVE%20NEWS/index.php?refresh_weather=1","2026-04-28 00:27:28");
INSERT INTO `ad_impressions` VALUES("75","1","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0","/PK-LIVE%20NEWS/index.php?refresh_weather=1","2026-04-28 00:27:36");
INSERT INTO `ad_impressions` VALUES("76","4","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0","/PK-LIVE%20NEWS/","2026-04-28 00:27:37");
INSERT INTO `ad_impressions` VALUES("77","1","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0","/PK-LIVE%20NEWS/","2026-04-28 00:28:47");
INSERT INTO `ad_impressions` VALUES("78","4","::1","Mozilla/5.0 (Linux; Android 8.0.0; SM-G955U Build/R16NW) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36","/PK-LIVE%20NEWS/index.php?refresh_weather=1","2026-04-28 00:38:31");
INSERT INTO `ad_impressions` VALUES("79","1","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36","/PK-LIVE%20NEWS/","2026-04-28 00:40:11");
INSERT INTO `ad_impressions` VALUES("80","4","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36","/PK-LIVE%20NEWS/category.php?slug=education","2026-04-28 00:40:48");
INSERT INTO `ad_impressions` VALUES("81","1","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36","/PK-LIVE%20NEWS/index.php","2026-04-28 00:41:12");
INSERT INTO `ad_impressions` VALUES("82","1","::1","Mozilla/5.0 (Linux; Android 8.0.0; SM-G955U Build/R16NW) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36","/PK-LIVE%20NEWS/index.php?refresh_weather=1","2026-04-28 00:46:52");
INSERT INTO `ad_impressions` VALUES("83","22","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0","/PK-LIVE%20NEWS/index.php?refresh_weather=1","2026-04-28 00:49:23");
INSERT INTO `ad_impressions` VALUES("84","4","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0","/PK-LIVE%20NEWS/","2026-04-28 00:49:23");
INSERT INTO `ad_impressions` VALUES("85","22","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36","/PK-LIVE%20NEWS/index.php?refresh_weather=1","2026-04-28 00:51:20");
INSERT INTO `ad_impressions` VALUES("86","22","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 OPR/130.0.0.0","/PK-LIVE%20NEWS/","2026-04-28 00:51:22");
INSERT INTO `ad_impressions` VALUES("87","4","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 OPR/130.0.0.0","/PK-LIVE%20NEWS/","2026-04-28 00:53:45");
INSERT INTO `ad_impressions` VALUES("88","4","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 OPR/130.0.0.0","/PK-LIVE%20NEWS/index.php","2026-04-28 00:57:05");
INSERT INTO `ad_impressions` VALUES("89","4","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0","/PK-LIVE%20NEWS/index.php?refresh_weather=1","2026-04-28 00:59:26");
INSERT INTO `ad_impressions` VALUES("90","22","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0","/PK-LIVE%20NEWS/","2026-04-28 01:00:12");
INSERT INTO `ad_impressions` VALUES("91","22","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36","/PK-LIVE%20NEWS/index.php?refresh_weather=1","2026-04-28 01:02:14");
INSERT INTO `ad_impressions` VALUES("92","1","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36","/PK-LIVE%20NEWS/index.php","2026-04-28 01:02:14");
INSERT INTO `ad_impressions` VALUES("93","4","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36","/PK-LIVE%20NEWS/index.php","2026-04-28 01:02:15");
INSERT INTO `ad_impressions` VALUES("94","22","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36","/PK-LIVE%20NEWS/news.php?slug=knives-out-is-a-coup-brewing-in-kiev","2026-04-28 01:02:35");
INSERT INTO `ad_impressions` VALUES("95","4","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36","/PK-LIVE%20NEWS/index.php","2026-04-28 01:02:48");
INSERT INTO `ad_impressions` VALUES("96","1","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0","/PK-LIVE%20NEWS/","2026-04-28 01:07:35");
INSERT INTO `ad_impressions` VALUES("97","4","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 OPR/130.0.0.0","/PK-LIVE%20NEWS/index.php","2026-04-28 01:09:22");
INSERT INTO `ad_impressions` VALUES("98","1","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0","/PK-LIVE%20NEWS/","2026-04-28 01:10:00");
INSERT INTO `ad_impressions` VALUES("99","1","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0","/PK-LIVE%20NEWS/","2026-04-28 01:10:26");
INSERT INTO `ad_impressions` VALUES("100","1","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 OPR/130.0.0.0","/PK-LIVE%20NEWS/news.php?slug=ex-ceo-ex-presidente-da-unimed-e-outros-4-acusados-viram-r%C3%A9us-por-estelionato-e-lavagem-de-dinheiro-em-mt","2026-04-28 01:11:36");
INSERT INTO `ad_impressions` VALUES("101","1","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 OPR/130.0.0.0","/PK-LIVE%20NEWS/index.php","2026-04-28 01:11:39");
INSERT INTO `ad_impressions` VALUES("102","1","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 OPR/130.0.0.0","/PK-LIVE%20NEWS/index.php","2026-04-28 01:11:49");
INSERT INTO `ad_impressions` VALUES("103","22","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36","/PK-LIVE%20NEWS/index.php?refresh_weather=1","2026-04-28 01:13:01");
INSERT INTO `ad_impressions` VALUES("104","4","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 OPR/130.0.0.0","/PK-LIVE%20NEWS/news.php?slug=president-and-first-lady-melania-trump-demand-abc-fire-jimmy-kimmel-over-widow-joke","2026-04-28 01:17:29");
INSERT INTO `ad_impressions` VALUES("105","22","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 OPR/130.0.0.0","/PK-LIVE%20NEWS/index.php","2026-04-28 01:17:37");
INSERT INTO `ad_impressions` VALUES("106","4","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 OPR/130.0.0.0","/PK-LIVE%20NEWS/index.php","2026-04-28 01:17:43");
INSERT INTO `ad_impressions` VALUES("107","22","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36","/PK-LIVE%20NEWS/index.php","2026-04-28 01:20:19");
INSERT INTO `ad_impressions` VALUES("108","4","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36","/PK-LIVE%20NEWS/index.php","2026-04-28 01:20:28");
INSERT INTO `ad_impressions` VALUES("109","4","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36","/PK-LIVE%20NEWS/news.php?slug=trump-discussed-iran-s-hormuz-strait-proposal-with-top-aides-white-house-says","2026-04-28 01:20:47");
INSERT INTO `ad_impressions` VALUES("110","1","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36","/PK-LIVE%20NEWS/news.php?slug=trump-discussed-iran-s-hormuz-strait-proposal-with-top-aides-white-house-says","2026-04-28 01:24:10");
INSERT INTO `ad_impressions` VALUES("111","1","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36","/PK-LIVE%20NEWS/index.php","2026-04-28 01:24:12");
INSERT INTO `ad_impressions` VALUES("112","1","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36","/PK-LIVE%20NEWS/index.php","2026-04-28 01:24:14");
INSERT INTO `ad_impressions` VALUES("113","4","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0","/PK-LIVE%20NEWS/index.php","2026-04-28 01:54:49");
INSERT INTO `ad_impressions` VALUES("114","22","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0","/PK-LIVE%20NEWS/index.php","2026-04-28 02:00:02");
INSERT INTO `ad_impressions` VALUES("115","22","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0","/PK-LIVE%20NEWS/news.php?slug=us-being-humiliated-by-iran-says-german-chancellor-friedrich-merz","2026-04-28 02:00:17");
INSERT INTO `ad_impressions` VALUES("116","22","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0","/PK-LIVE%20NEWS/index.php","2026-04-28 02:00:35");
INSERT INTO `ad_impressions` VALUES("117","1","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0","/PK-LIVE%20NEWS/news.php?slug=what-people-in-beirut-think-about-the-lebanon-israel-negotiations","2026-04-28 02:01:02");
INSERT INTO `ad_impressions` VALUES("118","4","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0","/PK-LIVE%20NEWS/index.php","2026-04-28 02:01:39");
INSERT INTO `ad_impressions` VALUES("119","4","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0","/PK-LIVE%20NEWS/index.php","2026-04-28 02:02:46");
INSERT INTO `ad_impressions` VALUES("120","4","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0","/PK-LIVE%20NEWS/index.php","2026-04-28 02:02:54");
INSERT INTO `ad_impressions` VALUES("121","22","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0","/PK-LIVE%20NEWS/index.php","2026-04-28 02:04:37");
INSERT INTO `ad_impressions` VALUES("122","22","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 OPR/130.0.0.0","/PK-LIVE%20NEWS/news.php?slug=vai-chover-veja-a-previs%C3%A3o-do-tempo-para-esta-ter%C3%A7a-28-em-alagoas","2026-04-28 02:09:36");
INSERT INTO `ad_impressions` VALUES("123","4","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 OPR/130.0.0.0","/PK-LIVE%20NEWS/index.php","2026-04-28 02:09:46");
INSERT INTO `ad_impressions` VALUES("124","4","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 OPR/130.0.0.0","/PK-LIVE%20NEWS/index.php","2026-04-28 02:09:51");
INSERT INTO `ad_impressions` VALUES("125","1","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 OPR/130.0.0.0","/PK-LIVE%20NEWS/index.php","2026-04-28 02:09:55");
INSERT INTO `ad_impressions` VALUES("126","22","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0","/PK-LIVE%20NEWS/index.php?refresh_weather=1","2026-04-28 02:14:47");
INSERT INTO `ad_impressions` VALUES("127","4","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0","/PK-LIVE%20NEWS/index.php","2026-04-28 02:18:15");
INSERT INTO `ad_impressions` VALUES("128","4","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0","/PK-LIVE%20NEWS/index.php","2026-04-28 02:18:18");
INSERT INTO `ad_impressions` VALUES("129","4","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0","/PK-LIVE%20NEWS/index.php","2026-04-28 02:18:20");
INSERT INTO `ad_impressions` VALUES("130","4","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0","/PK-LIVE%20NEWS/index.php","2026-04-28 02:18:22");
INSERT INTO `ad_impressions` VALUES("131","1","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0","/PK-LIVE%20NEWS/news.php?slug=ap%C3%B3s-candidata-do-to-ir-ao-stf-para-seguir-em-concurso-entenda-por-que-h%C3%A1-exig%C3%AAncia-de-altura-na-carreira-policial","2026-04-28 02:18:42");
INSERT INTO `ad_impressions` VALUES("132","22","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0","/PK-LIVE%20NEWS/index.php","2026-04-28 02:19:17");
INSERT INTO `ad_impressions` VALUES("133","22","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0","/PK-LIVE%20NEWS/index.php","2026-04-28 02:20:44");
INSERT INTO `ad_impressions` VALUES("134","4","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0","/PK-LIVE%20NEWS/index.php","2026-04-28 02:20:47");
INSERT INTO `ad_impressions` VALUES("135","21","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0","/PK-LIVE%20NEWS/live.php","2026-04-28 02:21:17");
INSERT INTO `ad_impressions` VALUES("136","4","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 OPR/130.0.0.0","/PK-LIVE%20NEWS/news.php?slug=carteira-de-pedidos-da-embraer-chega-a-us-32-1-bilh%C3%B5es-e-bate-recorde-pela-6%C2%AA-vez-seguida","2026-04-28 02:22:08");
INSERT INTO `ad_impressions` VALUES("137","22","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 OPR/130.0.0.0","/PK-LIVE%20NEWS/index.php","2026-04-28 02:22:10");
INSERT INTO `ad_impressions` VALUES("138","21","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0","/PK-LIVE%20NEWS/live.php","2026-04-28 02:24:15");
INSERT INTO `ad_impressions` VALUES("139","21","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0","/PK-LIVE%20NEWS/live.php","2026-04-28 02:24:31");
INSERT INTO `ad_impressions` VALUES("140","6","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0","/PK-LIVE%20NEWS/live.php","2026-04-28 02:25:29");
INSERT INTO `ad_impressions` VALUES("141","22","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0","/PK-LIVE%20NEWS/index.php","2026-04-28 02:27:09");
INSERT INTO `ad_impressions` VALUES("142","22","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 OPR/130.0.0.0","/PK-LIVE%20NEWS/index.php","2026-04-28 02:36:26");
INSERT INTO `ad_impressions` VALUES("143","1","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 OPR/130.0.0.0","/PK-LIVE%20NEWS/index.php","2026-04-28 02:36:36");
INSERT INTO `ad_impressions` VALUES("144","4","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 OPR/130.0.0.0","/PK-LIVE%20NEWS/index.php","2026-04-28 02:36:45");
INSERT INTO `ad_impressions` VALUES("145","1","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 OPR/130.0.0.0","/PK-LIVE%20NEWS/index.php","2026-04-28 02:37:30");
INSERT INTO `ad_impressions` VALUES("146","1","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0","/PK-LIVE%20NEWS/","2026-04-28 02:37:39");
INSERT INTO `ad_impressions` VALUES("147","1","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 OPR/130.0.0.0","/PK-LIVE%20NEWS/index.php?refresh_weather=1","2026-04-28 23:59:09");
INSERT INTO `ad_impressions` VALUES("148","4","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 OPR/130.0.0.0","/PK-LIVE%20NEWS/index.php","2026-04-29 00:02:10");
INSERT INTO `ad_impressions` VALUES("149","4","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 OPR/130.0.0.0","/PK-LIVE%20NEWS/index.php","2026-04-29 00:02:15");
INSERT INTO `ad_impressions` VALUES("150","4","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 OPR/130.0.0.0","/PK-LIVE%20NEWS/index.php","2026-04-29 00:02:36");
INSERT INTO `ad_impressions` VALUES("151","4","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36","/PK-LIVE%20NEWS/","2026-04-29 00:06:01");
INSERT INTO `ad_impressions` VALUES("152","4","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 OPR/130.0.0.0","/PK-LIVE%20NEWS/","2026-04-29 00:11:21");
INSERT INTO `ad_impressions` VALUES("153","4","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36","/PK-LIVE%20NEWS/","2026-04-29 00:13:41");
INSERT INTO `ad_impressions` VALUES("154","22","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 OPR/130.0.0.0","/PK-LIVE%20NEWS/","2026-04-29 00:15:35");
INSERT INTO `ad_impressions` VALUES("155","6","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 OPR/130.0.0.0","/PK-LIVE%20NEWS/live.php","2026-04-29 00:16:56");
INSERT INTO `ad_impressions` VALUES("156","21","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 OPR/130.0.0.0","/PK-LIVE%20NEWS/live.php","2026-04-29 00:17:14");
INSERT INTO `ad_impressions` VALUES("157","22","::1","Mozilla/5.0 (Linux; Android 13; Pixel 7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Mobile Safari/537.36 Edg/147.0.0.0","/PK-LIVE%20NEWS/index.php","2026-04-29 00:18:43");
INSERT INTO `ad_impressions` VALUES("158","1","::1","Mozilla/5.0 (Linux; Android 13; Pixel 7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Mobile Safari/537.36 Edg/147.0.0.0","/PK-LIVE%20NEWS/category.php?slug=politics","2026-04-29 00:19:55");
INSERT INTO `ad_impressions` VALUES("159","21","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 OPR/130.0.0.0","/PK-LIVE%20NEWS/live.php","2026-04-29 00:23:25");
INSERT INTO `ad_impressions` VALUES("160","22","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 OPR/130.0.0.0","/PK-LIVE%20NEWS/index.php","2026-04-29 00:23:28");
INSERT INTO `ad_impressions` VALUES("161","1","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36","/PK-LIVE%20NEWS/index.php?refresh_weather=1","2026-04-29 00:24:09");
INSERT INTO `ad_impressions` VALUES("162","4","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36","/PK-LIVE%20NEWS/","2026-04-29 00:32:40");
INSERT INTO `ad_impressions` VALUES("163","1","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36","/PK-LIVE%20NEWS/","2026-04-29 00:41:35");
INSERT INTO `ad_impressions` VALUES("164","22","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36","/PK-LIVE%20NEWS/","2026-04-29 00:41:39");
INSERT INTO `ad_impressions` VALUES("165","1","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36","/PK-LIVE%20NEWS/","2026-04-29 00:41:44");
INSERT INTO `ad_impressions` VALUES("166","4","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36","/PK-LIVE%20NEWS/","2026-04-29 00:41:48");
INSERT INTO `ad_impressions` VALUES("167","1","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36","/PK-LIVE%20NEWS/","2026-04-29 00:44:05");
INSERT INTO `ad_impressions` VALUES("168","4","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36","/PK-LIVE%20NEWS/","2026-04-29 00:44:14");
INSERT INTO `ad_impressions` VALUES("169","1","::1","Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1","/PK-LIVE%20NEWS/index.php?refresh_weather=1","2026-04-29 00:54:28");
INSERT INTO `ad_impressions` VALUES("170","22","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 OPR/130.0.0.0","/PK-LIVE%20NEWS/","2026-04-29 00:55:39");
INSERT INTO `ad_impressions` VALUES("171","4","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 OPR/130.0.0.0","/PK-LIVE%20NEWS/","2026-04-29 00:55:46");
INSERT INTO `ad_impressions` VALUES("172","1","::1","Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1","/PK-LIVE%20NEWS/","2026-04-29 00:55:49");


CREATE TABLE `admin_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `permission_key` varchar(100) NOT NULL,
  `permission_name` varchar(100) NOT NULL,
  `permission_group` varchar(50) NOT NULL DEFAULT 'general',
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `permission_key` (`permission_key`),
  UNIQUE KEY `idx_permission_key` (`permission_key`),
  KEY `idx_permission_group` (`permission_group`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `admin_permissions` VALUES("1","all","Full Access","general","Complete system access","2026-04-10 09:14:56");
INSERT INTO `admin_permissions` VALUES("2","dashboard_view","View Dashboard","general","Access admin dashboard","2026-04-10 09:14:56");
INSERT INTO `admin_permissions` VALUES("3","user_manage","Manage Users","users","Create, edit, delete users","2026-04-10 09:14:56");
INSERT INTO `admin_permissions` VALUES("4","user_view","View Users","users","View user list and details","2026-04-10 09:14:56");
INSERT INTO `admin_permissions` VALUES("5","user_role_assign","Assign User Roles","users","Assign roles to users","2026-04-10 09:14:56");
INSERT INTO `admin_permissions` VALUES("6","content_create","Create Content","content","Create new articles and content","2026-04-10 09:14:56");
INSERT INTO `admin_permissions` VALUES("7","content_edit","Edit Content","content","Edit existing articles and content","2026-04-10 09:14:56");
INSERT INTO `admin_permissions` VALUES("8","content_publish","Publish Content","content","Publish and unpublish content","2026-04-10 09:14:56");
INSERT INTO `admin_permissions` VALUES("9","content_delete","Delete Content","content","Delete articles and content","2026-04-10 09:14:56");
INSERT INTO `admin_permissions` VALUES("10","content_manage","Manage All Content","content","Full content management access","2026-04-10 09:14:56");
INSERT INTO `admin_permissions` VALUES("11","content_moderate","Moderate Content","content","Review and moderate content","2026-04-10 09:14:56");
INSERT INTO `admin_permissions` VALUES("12","role_applications_review","Review Role Applications","applications","Review and approve/reject role applications","2026-04-10 09:14:56");
INSERT INTO `admin_permissions` VALUES("13","role_applications_manage","Manage Role Applications","applications","Full application management access","2026-04-10 09:14:56");
INSERT INTO `admin_permissions` VALUES("14","analytics_view","View Analytics","analytics","Access analytics and reports","2026-04-10 09:14:56");
INSERT INTO `admin_permissions` VALUES("15","reports_view","View Reports","analytics","Access system reports","2026-04-10 09:14:56");
INSERT INTO `admin_permissions` VALUES("16","settings_manage","Manage Settings","system","Access system settings","2026-04-10 09:14:56");
INSERT INTO `admin_permissions` VALUES("17","system_logs","View System Logs","system","Access system logs and audit trails","2026-04-10 09:14:56");
INSERT INTO `admin_permissions` VALUES("18","comments_manage","Manage Comments","content","Manage and moderate comments","2026-04-10 09:14:56");
INSERT INTO `admin_permissions` VALUES("19","news_articles_manage","Manage News Articles","content","Permission to manage news articles","2026-04-10 09:31:39");
INSERT INTO `admin_permissions` VALUES("20","polls_manage","Manage Polls","content","Permission to manage polls and surveys","2026-04-10 09:31:39");


CREATE TABLE `admin_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_name` varchar(50) NOT NULL,
  `role_level` int(11) NOT NULL DEFAULT 1,
  `permissions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`permissions`)),
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_name` (`role_name`),
  UNIQUE KEY `idx_role_name` (`role_name`),
  KEY `idx_role_level` (`role_level`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `admin_roles` VALUES("1","Super Admin","100","[\"all\"]","Full system access with all permissions","2026-04-10 09:14:56");
INSERT INTO `admin_roles` VALUES("2","Content Manager","80","[\"content_manage\", \"user_manage\", \"analytics_view\", \"role_applications_review\"]","Manage content, users, and review applications","2026-04-10 09:14:56");
INSERT INTO `admin_roles` VALUES("3","Editor","60","[\"news_articles_manage\",\"content_edit\",\"comments_manage\",\"polls_manage\",\"analytics_view\"]","Editor with content management and publishing permissions","2026-04-10 09:14:56");
INSERT INTO `admin_roles` VALUES("4","Moderator","40","[\"content_moderate\", \"comments_manage\"]","Moderate content and manage comments","2026-04-10 09:14:56");
INSERT INTO `admin_roles` VALUES("5","Reporter","20","[\"content_create\", \"comments_manage\"]","Create content and manage own comments","2026-04-10 09:14:56");


CREATE TABLE `advertisements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `position` enum('header','sidebar','footer','all','live_header','live_sidebar','live_footer','live_popup','performance_header','performance_sidebar','performance_footer','performance_inline','contact_header','contact_sidebar','contact_footer','category_header','category_sidebar','category_footer','category_inline','home_hero','home_featured','home_sidebar','home_footer','news_inline','search_sidebar','profile_sidebar') DEFAULT 'sidebar',
  `category_id` int(11) DEFAULT NULL,
  `page_type` enum('all','home','category','news','live','contact','search','profile','performance') DEFAULT 'all',
  `device_type` enum('all','desktop','mobile','tablet') DEFAULT 'all',
  `image` varchar(500) DEFAULT NULL,
  `redirect_url` varchar(500) DEFAULT NULL,
  `code` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `size` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_ad_category` (`category_id`),
  CONSTRAINT `fk_ad_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `advertisements` VALUES("1","Sample Business Ad - Sidebar","sidebar","","all","all","uploads/ads/69adaaa0ab59c.jpg","https://example-business.com","","active","2026-04-09","2026-05-09","2026-04-09 10:48:13","2026-04-26 23:57:52","");
INSERT INTO `advertisements` VALUES("2","Tech Store Promotion","header","","all","all","uploads/ads/69adaaa0ab59c.jpg","https://techstore.example","","active","2026-04-09","2026-05-09","2026-04-09 10:48:13","2026-04-26 23:57:49","");
INSERT INTO `advertisements` VALUES("3","Local Services Ad","footer","","all","all","uploads/ads/69adaaa0ab59c.jpg","https://localservices.example","","active","2026-04-09","2026-05-09","2026-04-09 10:48:13","2026-04-26 23:57:46","");
INSERT INTO `advertisements` VALUES("4","Restaurant Special Offer","sidebar","","all","all","uploads/ads/69adaaa0ab59c.jpg","https://restaurant.example","","active","2026-04-09","2026-05-09","2026-04-09 10:48:13","2026-04-26 23:57:42","");
INSERT INTO `advertisements` VALUES("5","E-commerce Banner","all","","all","all","uploads/ads/69adaaa0ab59c.jpg","https://shop.example","","active","2026-04-09","2026-05-09","2026-04-09 10:48:13","2026-04-26 23:57:36","");
INSERT INTO `advertisements` VALUES("6","Live Stream Banner Ad","live_header","","live","all","","","<a href=\"https://example.com\"><img src=\"uploads/ads/live-banner.jpg\" alt=\"Live Stream Ad\" style=\"width:100%;height:90px;\"></a>","active","2026-04-27","2026-05-27","2026-04-27 00:32:24","2026-04-27 00:32:24","");
INSERT INTO `advertisements` VALUES("7","Performance Analysis Widget","performance_sidebar","","performance","all","","","<div style=\"background:#f0f0f0;padding:10px;border:1px solid #ccc;\"><h4>Performance Tools</h4><a href=\"https://tools.example.com\">Try our Analytics</a></div>","active","2026-04-27","2026-05-27","2026-04-27 00:32:24","2026-04-27 00:32:24","");
INSERT INTO `advertisements` VALUES("8","Contact Page Service Ad","contact_sidebar","","contact","all","","","<div style=\"background:#e8f4f8;padding:15px;border-radius:5px;\"><h3>Professional Services</h3><p>Get expert help with your projects</p><a href=\"https://services.example.com\" class=\"btn btn-primary\">Learn More</a></div>","active","2026-04-27","2026-05-27","2026-04-27 00:32:24","2026-04-27 00:32:24","");
INSERT INTO `advertisements` VALUES("9","Category Featured Ad","category_header","","category","all","","","<div style=\"background:linear-gradient(45deg,#ff6b6b,#4ecdc4);color:white;padding:20px;text-align:center;\"><h2>Special Category Offer</h2><p>Exclusive deals for this category</p></div>","active","2026-04-27","2026-05-27","2026-04-27 00:32:24","2026-04-27 00:32:24","");
INSERT INTO `advertisements` VALUES("10","Home Hero Banner","home_hero","","home","all","","","<div style=\"background:url(uploads/ads/hero-bg.jpg) center/cover;height:300px;display:flex;align-items:center;justify-content:center;\"><div style=\"background:rgba(0,0,0,0.7);color:white;padding:30px;border-radius:10px;\"><h1>Big Sale Event</h1><p>Limited time offers</p></div></div>","active","2026-04-27","2026-05-27","2026-04-27 00:32:24","2026-04-27 00:32:24","");
INSERT INTO `advertisements` VALUES("11","News Inline Ad","news_inline","","news","all","","","<div style=\"border:1px solid #ddd;padding:10px;margin:10px 0;background:#f9f9f9;\"><p><strong>Sponsored Content:</strong> Check out these amazing products!</p><a href=\"https://shop.example.com\">Shop Now</a></div>","active","2026-04-27","2026-05-27","2026-04-27 00:32:24","2026-04-27 00:32:24","");
INSERT INTO `advertisements` VALUES("12","Live Stream Banner Ad","live_header","","live","all","","","<a href=\"https://example.com\"><img src=\"uploads/ads/live-banner.jpg\" alt=\"Live Stream Ad\" style=\"width:100%;height:90px;\"></a>","active","2026-04-27","2026-05-27","2026-04-27 00:33:13","2026-04-27 00:33:13","");
INSERT INTO `advertisements` VALUES("13","Performance Analysis Widget","performance_sidebar","","performance","all","","","<div style=\"background:#f0f0f0;padding:10px;border:1px solid #ccc;\"><h4>Performance Tools</h4><a href=\"https://tools.example.com\">Try our Analytics</a></div>","active","2026-04-27","2026-05-27","2026-04-27 00:33:13","2026-04-27 00:33:13","");
INSERT INTO `advertisements` VALUES("14","Contact Page Service Ad","contact_sidebar","","contact","all","","","<div style=\"background:#e8f4f8;padding:15px;border-radius:5px;\"><h3>Professional Services</h3><p>Get expert help with your projects</p><a href=\"https://services.example.com\" class=\"btn btn-primary\">Learn More</a></div>","active","2026-04-27","2026-05-27","2026-04-27 00:33:13","2026-04-27 00:33:13","");
INSERT INTO `advertisements` VALUES("15","Category Featured Ad","category_header","","category","all","","","<div style=\"background:linear-gradient(45deg,#ff6b6b,#4ecdc4);color:white;padding:20px;text-align:center;\"><h2>Special Category Offer</h2><p>Exclusive deals for this category</p></div>","active","2026-04-27","2026-05-27","2026-04-27 00:33:13","2026-04-27 00:33:13","");
INSERT INTO `advertisements` VALUES("16","Home Hero Banner","home_hero","","home","all","","","<div style=\"background:url(uploads/ads/hero-bg.jpg) center/cover;height:300px;display:flex;align-items:center;justify-content:center;\"><div style=\"background:rgba(0,0,0,0.7);color:white;padding:30px;border-radius:10px;\"><h1>Big Sale Event</h1><p>Limited time offers</p></div></div>","active","2026-04-27","2026-05-27","2026-04-27 00:33:13","2026-04-27 00:33:13","");
INSERT INTO `advertisements` VALUES("17","News Inline Ad","news_inline","","news","all","","","<div style=\"border:1px solid #ddd;padding:10px;margin:10px 0;background:#f9f9f9;\"><p><strong>Sponsored Content:</strong> Check out these amazing products!</p><a href=\"https://shop.example.com\">Shop Now</a></div>","active","2026-04-27","2026-05-27","2026-04-27 00:33:13","2026-04-27 00:33:13","");
INSERT INTO `advertisements` VALUES("21","bkuc","live_header","","live","all","","","<!-- Google AdSense -->\n<script async src=\"https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js\"\n     crossorigin=\"anonymous\"></script>\n\n<!-- Your Ad Unit -->\n<ins class=\"adsbygoogle\"\n     style=\"display:block\"\n     data-ad-client=\"ca-pub-XXXXXXXXXXXX\"\n     data-ad-slot=\"1234567890\"\n     data-ad-format=\"auto\"\n     data-full-width-responsive=\"true\"></ins>\n\n<script>\n     (adsbygoogle = window.adsbygoogle || []).push({});\n</script>","active","2026-04-27","2026-04-30","2026-04-27 01:15:42","2026-04-27 01:15:42","728x90");
INSERT INTO `advertisements` VALUES("22","Quick Fix Ad 01:16:47","sidebar","","all","all","","","<div style=\'background:#007bff;color:white;padding:10px;border-radius:5px;text-align:center;\'><h4>Quick Fix Ad</h4><p>Basic ad working!</p></div>","active","","","2026-04-27 01:16:47","2026-04-27 01:16:47","");


CREATE TABLE `affiliate_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `parent_id` (`parent_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `affiliate_categories` VALUES("1","Electronics","electronics","Mobile phones, laptops, gadgets","fa-laptop","","1","active","2026-04-09 10:57:31");
INSERT INTO `affiliate_categories` VALUES("2","Mobile Phones","mobile-phones","Smartphones and accessories","fa-mobile","","2","active","2026-04-09 10:57:31");
INSERT INTO `affiliate_categories` VALUES("3","Laptops","laptops","Laptops and computers","fa-laptop","","3","active","2026-04-09 10:57:31");
INSERT INTO `affiliate_categories` VALUES("4","Gaming","gaming","Gaming consoles and accessories","fa-gamepad","","4","active","2026-04-09 10:57:31");
INSERT INTO `affiliate_categories` VALUES("5","Cameras","cameras","Digital cameras and photography","fa-camera","","5","active","2026-04-09 10:57:31");
INSERT INTO `affiliate_categories` VALUES("6","Audio","audio","Headphones, speakers, audio equipment","fa-headphones","","6","active","2026-04-09 10:57:31");
INSERT INTO `affiliate_categories` VALUES("7","Smart Home","smart-home","Smart home devices and IoT","fa-home","","7","active","2026-04-09 10:57:31");
INSERT INTO `affiliate_categories` VALUES("8","Fashion","fashion","Clothing and accessories","fa-tshirt","","8","active","2026-04-09 10:57:31");
INSERT INTO `affiliate_categories` VALUES("9","Sports","sports","Sports equipment and gear","fa-football-ball","","9","active","2026-04-09 10:57:31");
INSERT INTO `affiliate_categories` VALUES("10","Books","books","Books and educational materials","fa-book","","10","active","2026-04-09 10:57:31");


CREATE TABLE `affiliate_clicks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `referrer` varchar(500) DEFAULT NULL,
  `click_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `converted` tinyint(1) DEFAULT 0,
  `conversion_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `click_date` (`click_date`),
  KEY `converted` (`converted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



CREATE TABLE `affiliate_products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `short_description` varchar(500) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `original_price` decimal(10,2) DEFAULT NULL,
  `currency` varchar(3) DEFAULT 'USD',
  `image_url` varchar(500) DEFAULT NULL,
  `affiliate_url` varchar(500) NOT NULL,
  `affiliate_network` enum('amazon','aliexpress','other') DEFAULT 'amazon',
  `category_id` int(11) DEFAULT NULL,
  `rating` decimal(3,2) DEFAULT 0.00,
  `review_count` int(11) DEFAULT 0,
  `availability` enum('in_stock','out_of_stock','limited') DEFAULT 'in_stock',
  `brand` varchar(100) DEFAULT NULL,
  `model` varchar(100) DEFAULT NULL,
  `tags` varchar(255) DEFAULT NULL,
  `featured` tinyint(1) DEFAULT 0,
  `status` enum('active','inactive','pending') DEFAULT 'active',
  `click_count` int(11) DEFAULT 0,
  `conversion_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `category_id` (`category_id`),
  KEY `featured` (`featured`),
  KEY `status` (`status`),
  CONSTRAINT `fk_affiliate_products_category` FOREIGN KEY (`category_id`) REFERENCES `affiliate_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



CREATE TABLE `alert_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `alert_type` varchar(50) NOT NULL DEFAULT 'general',
  `alert_message` text DEFAULT NULL,
  `alert_frequency` varchar(20) DEFAULT 'daily',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  KEY `alert_type` (`alert_type`),
  KEY `is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `approved_comments_view` AS select `c`.`id` AS `id`,`c`.`news_id` AS `news_id`,`c`.`user_id` AS `user_id`,`c`.`parent_id` AS `parent_id`,`c`.`name` AS `name`,`c`.`email` AS `email`,`c`.`comment` AS `comment`,`c`.`status` AS `status`,`c`.`ip_address` AS `ip_address`,`c`.`user_agent` AS `user_agent`,`c`.`likes_count` AS `likes_count`,`c`.`dislikes_count` AS `dislikes_count`,`c`.`is_edited` AS `is_edited`,`c`.`edited_at` AS `edited_at`,`c`.`created_at` AS `created_at`,`c`.`updated_at` AS `updated_at`,`u`.`name` AS `user_name`,`u`.`image` AS `user_avatar`,`u`.`role` AS `user_role`,(select count(0) from `comment_likes` `cl` where `cl`.`comment_id` = `c`.`id` and `cl`.`like_type` = 'like') AS `actual_likes`,(select count(0) from `comment_likes` `cl` where `cl`.`comment_id` = `c`.`id` and `cl`.`like_type` = 'dislike') AS `actual_dislikes`,(select count(0) from `comments` `cr` where `cr`.`parent_id` = `c`.`id`) AS `replies_count` from (`comments` `c` left join `users` `u` on(`c`.`user_id` = `u`.`id`)) where `c`.`status` = 'approved';

INSERT INTO `approved_comments_view` VALUES("22","21","1","","Admin","admin@pklivenews.com","hi","approved","","","0","0","0","","2026-04-24 00:23:01","2026-04-24 00:23:01","Admin","","admin","0","0","0");
INSERT INTO `approved_comments_view` VALUES("23","27","14","","","","nj","approved","","","0","0","0","","2026-04-27 13:01:44","2026-04-27 13:01:44","hasnain","","user","0","0","0");
INSERT INTO `approved_comments_view` VALUES("24","27","14","","","","nj","approved","","","0","0","0","","2026-04-27 13:01:44","2026-04-27 13:01:44","hasnain","","user","0","0","0");
INSERT INTO `approved_comments_view` VALUES("26","27","14","","","","v","approved","","","0","0","0","","2026-04-27 22:14:57","2026-04-27 22:14:57","hasnain","","user","0","0","0");


CREATE TABLE `articles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `content` text DEFAULT NULL,
  `excerpt` text DEFAULT NULL,
  `author` varchar(100) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `tags` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `image_type` varchar(50) DEFAULT 'standard',
  `source` varchar(255) DEFAULT NULL,
  `source_name` varchar(255) DEFAULT NULL,
  `status` enum('published','draft','pending') DEFAULT 'draft',
  `featured` tinyint(1) DEFAULT 0,
  `breaking_news` tinyint(1) DEFAULT 0,
  `views` int(11) DEFAULT 0,
  `published_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



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
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `categories` VALUES("2","Politics","","","","","#007bff","fas fa-newspaper","politics","","","","","","","","","active","2026-04-09 10:34:15","2026-04-09 10:34:15","");
INSERT INTO `categories` VALUES("3","Sports","","","","","#007bff","fas fa-newspaper","sports","","","","","","","","","active","2026-04-09 10:34:15","2026-04-26 22:48:04","");
INSERT INTO `categories` VALUES("4","Technology","","","","","#007bff","fas fa-newspaper","technology","","","","","","","","","active","2026-04-09 10:34:15","2026-04-13 01:31:21","");
INSERT INTO `categories` VALUES("5","Business","","","","","#007bff","fas fa-newspaper","business","","","","","","","","","active","2026-04-09 10:34:15","2026-04-13 01:31:11","");
INSERT INTO `categories` VALUES("6","Entertainment","","","","","#007bff","fas fa-newspaper","entertainment","","","","","","","","","active","2026-04-09 10:34:15","2026-04-13 01:31:13","");
INSERT INTO `categories` VALUES("7","Health","","","","","#007bff","fas fa-newspaper","health","","","","","","","","","active","2026-04-09 10:34:15","2026-04-13 01:31:14","");
INSERT INTO `categories` VALUES("8","Education","","","","","#007bff","fas fa-newspaper","education","","","","","","","","","active","2026-04-09 10:34:15","2026-04-13 00:22:58","");


CREATE TABLE `category_analytics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `view_count` int(11) DEFAULT 0,
  `click_count` int(11) DEFAULT 0,
  `article_count` int(11) DEFAULT 0,
  `date_recorded` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `category_date` (`category_id`,`date_recorded`),
  KEY `category_id` (`category_id`),
  KEY `date_recorded` (`date_recorded`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



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
) ENGINE=InnoDB AUTO_INCREMENT=67 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `channels` VALUES("4","Dunya News","news","https://youtu.be/e2XVSUYh4S0","youtube","","Leading Pakistani news channel with breaking news and current affairs","live","1295","urdu","PK","6","1","","2026-04-10 01:52:07","2026-04-24 00:51:58");
INSERT INTO `channels` VALUES("5","Samaa TV","news","https://www.youtube.com/embed/jNQXAC9IVRw","youtube","","24/7 Pakistani news channel with comprehensive coverage","live","3819","urdu","PK","7","1","","2026-04-10 01:52:07","2026-04-10 01:57:12");
INSERT INTO `channels` VALUES("6","Express News","news","https://www.youtube.com/embed/jNQXAC9IVRw","youtube","","Fast-paced Pakistani news channel with in-depth analysis","live","4354","urdu","PK","8","0","","2026-04-10 01:52:07","2026-04-10 01:52:07");
INSERT INTO `channels` VALUES("7","92 News HD","news","https://www.youtube.com/embed/jNQXAC9IVRw","youtube","","HD Pakistani news channel with modern presentation","live","3015","urdu","PK","9","0","","2026-04-10 01:52:07","2026-04-10 01:52:07");
INSERT INTO `channels` VALUES("8","PTV Sports","sports","https://www.youtube.com/embed/jNQXAC9IVRw","youtube","","Pakistan\'s premier sports channel featuring cricket, hockey, and more","live","4112","urdu","PK","10","1","","2026-04-10 01:52:07","2026-04-10 01:57:13");
INSERT INTO `channels` VALUES("9","Ten Sports","sports","https://www.youtube.com/embed/jNQXAC9IVRw","youtube","","International sports channel with live matches and highlights","live","1484","english","PK","11","0","","2026-04-10 01:52:07","2026-04-10 07:11:57");
INSERT INTO `channels` VALUES("10","Hum TV","entertainment","https://www.youtube.com/embed/jNQXAC9IVRw","youtube","","Popular Pakistani entertainment channel with dramas and shows","live","1457","urdu","PK","12","1","","2026-04-10 01:52:07","2026-04-10 01:57:07");
INSERT INTO `channels` VALUES("11","ARY Digital","entertainment","https://www.youtube.com/embed/jNQXAC9IVRw","youtube","","Leading Pakistani entertainment channel with premium dramas","live","2450","urdu","PK","13","1","","2026-04-10 01:52:07","2026-04-10 01:55:31");
INSERT INTO `channels` VALUES("12","Geo TV","entertainment","https://www.youtube.com/embed/jNQXAC9IVRw","youtube","","Popular Pakistani entertainment and drama channel","live","3137","urdu","PK","14","0","","2026-04-10 01:52:07","2026-04-10 01:52:07");
INSERT INTO `channels` VALUES("13","Business Plus","business","https://www.youtube.com/embed/jNQXAC9IVRw","youtube","","Pakistani business news and financial analysis channel","live","1948","urdu","PK","15","0","","2026-04-10 01:52:07","2026-04-10 01:52:07");
INSERT INTO `channels` VALUES("14","BBC World News","international","https://www.youtube.com/embed/jNQXAC9IVRw","youtube","","International news from BBC","live","1690","english","PK","9","1","","2026-04-10 01:52:07","2026-04-10 07:11:05");
INSERT INTO `channels` VALUES("15","CNN International","international","https://www.youtube.com/embed/jNQXAC9IVRw","youtube","","24/7 international news coverage","live","4015","english","PK","10","1","","2026-04-10 01:52:07","2026-04-24 00:56:34");
INSERT INTO `channels` VALUES("16","Al Jazeera English","international","https://www.youtube.com/embed/jNQXAC9IVRw","youtube","","Middle Eastern perspective on global news and events","live","4796","english","PK","18","0","","2026-04-10 01:52:07","2026-04-10 01:52:07");
INSERT INTO `channels` VALUES("17","Peace TV","","https://www.youtube.com/embed/jNQXAC9IVRw","youtube","","Islamic religious and educational content","live","3066","english","PK","19","0","","2026-04-10 01:52:07","2026-04-10 01:52:07");
INSERT INTO `channels` VALUES("18","ATV Music","entertainment","https://www.youtube.com/embed/jNQXAC9IVRw","youtube","","Pakistani music channel with latest songs and performances","live","1990","urdu","PK","20","0","","2026-04-10 01:52:07","2026-04-10 01:52:07");
INSERT INTO `channels` VALUES("19","Geo News Live","news","https://youtu.be/PNpD_wM1GVE","youtube","","Pakistan leading news channel with 24/7 coverage","live","1293","urdu","PK","1","1","","2026-04-10 07:11:04","2026-04-24 00:57:20");
INSERT INTO `channels` VALUES("20","ARY News Live","news","https://youtu.be/5QfmfJySn44","youtube","","Fast-paced news with comprehensive coverage","live","1869","urdu","PK","2","1","","2026-04-10 07:11:04","2026-04-24 00:50:02");
INSERT INTO `channels` VALUES("21","Dunya News Live","news","https://youtu.be/e2XVSUYh4S0","youtube","","Breaking news and current affairs","live","4385","urdu","PK","3","1","","2026-04-10 07:11:04","2026-04-24 00:56:23");
INSERT INTO `channels` VALUES("22","Samaa TV Live","news","https://www.youtube.com/embed/jNQXAC9IVRw","youtube","","24/7 news and analysis","live","1240","urdu","PK","4","0","","2026-04-10 07:11:04","2026-04-13 10:32:02");
INSERT INTO `channels` VALUES("23","Express News Live","news","https://www.youtube.com/embed/jNQXAC9IVRw","youtube","","News with in-depth analysis","live","3944","urdu","PK","5","0","","2026-04-10 07:11:04","2026-04-10 07:11:04");
INSERT INTO `channels` VALUES("24","PTV Sports Live","sports","https://www.youtube.com/embed/jNQXAC9IVRw","youtube","","Pakistan sports channel with live matches","live","3112","urdu","PK","6","1","","2026-04-10 07:11:04","2026-04-10 07:11:04");
INSERT INTO `channels` VALUES("25","Hum TV Live","entertainment","https://www.youtube.com/embed/jNQXAC9IVRw","youtube","","Popular Pakistani entertainment channel","live","2099","urdu","PK","7","1","","2026-04-10 07:11:04","2026-04-10 07:11:04");
INSERT INTO `channels` VALUES("26","ARY Digital Live","entertainment","https://www.youtube.com/embed/jNQXAC9IVRw","youtube","","Premium dramas and entertainment shows","live","2589","urdu","PK","8","1","","2026-04-10 07:11:04","2026-04-10 07:11:04");
INSERT INTO `channels` VALUES("28","BBC News","news","https://www.youtube.com/watch?v=wGBzr_8qPm4","youtube","","BBC News - Live news stream from UK","live","1","en","0","0","0","","2026-04-24 00:56:06","2026-04-28 02:21:30");
INSERT INTO `channels` VALUES("29","CNN","news","https://youtu.be/8dGlYqWWdww","youtube","","CNN - Live news stream from USA","live","4","en","0","0","0","","2026-04-24 00:56:06","2026-04-28 02:24:33");
INSERT INTO `channels` VALUES("30","Al Jazeera","news","https://youtu.be/bNyUyrR0PHo","youtube","","Al Jazeera - Live news stream from Qatar","live","11","en","0","0","0","","2026-04-24 00:56:06","2026-04-29 00:17:18");
INSERT INTO `channels` VALUES("31","Reuters","news","https://www.youtube.com/watch?v=jNQXAC9IVRw","youtube","","Reuters - Live news stream from UK","live","0","en","0","0","0","","2026-04-24 00:56:06","2026-04-24 00:56:06");
INSERT INTO `channels` VALUES("32","Fox News","news","https://www.youtube.com/watch?v=9bZkp7q19f0","youtube","","Fox News - Live news stream from USA","live","0","en","0","0","0","","2026-04-24 00:56:06","2026-04-24 00:56:06");
INSERT INTO `channels` VALUES("33","MSNBC","news","https://www.youtube.com/watch?v=9bZkp7q19f0","youtube","","MSNBC - Live news stream from USA","live","1","en","0","0","0","","2026-04-24 00:56:06","2026-04-24 00:56:45");
INSERT INTO `channels` VALUES("34","NBC News","news","https://www.youtube.com/watch?v=9bZkp7q19f0","youtube","","NBC News - Live news stream from USA","live","1","en","0","0","0","","2026-04-24 00:56:06","2026-04-24 00:56:53");
INSERT INTO `channels` VALUES("35","CBS News","news","https://www.youtube.com/watch?v=9bZkp7q19f0","youtube","","CBS News - Live news stream from USA","live","3","en","0","0","0","","2026-04-24 00:56:06","2026-04-28 02:24:24");
INSERT INTO `channels` VALUES("36","ABC News","news","https://youtu.be/P49mKO-tTNk","youtube","","ABC News - Live news stream from USA","live","6","en","0","0","0","","2026-04-24 00:56:06","2026-04-28 02:21:26");
INSERT INTO `channels` VALUES("37","The Guardian","news","https://www.youtube.com/watch?v=jNQXAC9IVRw","youtube","","The Guardian - Live news stream from UK","live","0","en","0","0","0","","2026-04-24 00:56:06","2026-04-24 00:56:06");
INSERT INTO `channels` VALUES("38","The Times","news","https://www.youtube.com/watch?v=jNQXAC9IVRw","youtube","","The Times - Live news stream from UK","live","0","en","0","0","0","","2026-04-24 00:56:06","2026-04-24 00:56:06");
INSERT INTO `channels` VALUES("39","France 24","news","https://www.youtube.com/watch?v=ojX6k_6-8dI","youtube","","France 24 - Live news stream from France","live","0","en","0","0","0","","2026-04-24 00:56:06","2026-04-24 00:56:06");
INSERT INTO `channels` VALUES("40","Deutsche Welle","news","https://www.youtube.com/watch?v=ojX6k_6-8dI","youtube","","Deutsche Welle - Live news stream from Germany","live","0","en","0","0","0","","2026-04-24 00:56:06","2026-04-24 00:56:06");
INSERT INTO `channels` VALUES("41","RT News","news","https://www.youtube.com/watch?v=ojX6k_6-8dI","youtube","","RT News - Live news stream from Russia","live","0","en","0","0","0","","2026-04-24 00:56:06","2026-04-24 00:56:06");
INSERT INTO `channels` VALUES("42","Le Monde","news","https://www.youtube.com/watch?v=ojX6k_6-8dI","youtube","","Le Monde - Live news stream from France","live","0","en","0","0","0","","2026-04-24 00:56:06","2026-04-24 00:56:06");
INSERT INTO `channels` VALUES("43","Der Spiegel","news","https://www.youtube.com/watch?v=ojX6k_6-8dI","youtube","","Der Spiegel - Live news stream from Germany","live","0","en","0","0","0","","2026-04-24 00:56:06","2026-04-24 00:56:06");
INSERT INTO `channels` VALUES("44","Corriere della Sera","news","https://www.youtube.com/watch?v=ojX6k_6-8dI","youtube","","Corriere della Sera - Live news stream from Italy","live","0","en","0","0","0","","2026-04-24 00:56:06","2026-04-24 00:56:06");
INSERT INTO `channels` VALUES("45","El Pais","news","https://www.youtube.com/watch?v=ojX6k_6-8dI","youtube","","El Pais - Live news stream from Spain","live","0","en","0","0","0","","2026-04-24 00:56:06","2026-04-24 00:56:06");
INSERT INTO `channels` VALUES("46","CCTV","news","https://www.youtube.com/watch?v=ojX6k_6-8dI","youtube","","CCTV - Live news stream from China","live","0","en","0","0","0","","2026-04-24 00:56:06","2026-04-24 00:56:06");
INSERT INTO `channels` VALUES("47","NDTV","news","https://www.youtube.com/watch?v=ojX6k_6-8dI","youtube","","NDTV - Live news stream from India","live","0","en","0","0","0","","2026-04-24 00:56:06","2026-04-24 00:56:06");
INSERT INTO `channels` VALUES("48","Times of India","news","https://www.youtube.com/watch?v=ojX6k_6-8dI","youtube","","Times of India - Live news stream from India","live","0","en","0","0","0","","2026-04-24 00:56:06","2026-04-24 00:56:06");
INSERT INTO `channels` VALUES("49","The Hindu","news","https://www.youtube.com/watch?v=ojX6k_6-8dI","youtube","","The Hindu - Live news stream from India","live","0","en","0","0","0","","2026-04-24 00:56:06","2026-04-24 00:56:06");
INSERT INTO `channels` VALUES("50","Japan Times","news","https://www.youtube.com/watch?v=ojX6k_6-8dI","youtube","","Japan Times - Live news stream from Japan","live","0","en","0","0","0","","2026-04-24 00:56:06","2026-04-24 00:56:06");
INSERT INTO `channels` VALUES("51","Sydney Morning Herald","news","https://www.youtube.com/watch?v=ojX6k_6-8dI","youtube","","Sydney Morning Herald - Live news stream from Australia","live","0","en","0","0","0","","2026-04-24 00:56:06","2026-04-24 00:56:06");
INSERT INTO `channels` VALUES("52","The Age","news","https://www.youtube.com/watch?v=ojX6k_6-8dI","youtube","","The Age - Live news stream from Australia","live","0","en","0","0","0","","2026-04-24 00:56:06","2026-04-24 00:56:06");
INSERT INTO `channels` VALUES("53","Toronto Star","news","https://www.youtube.com/watch?v=ojX6k_6-8dI","youtube","","Toronto Star - Live news stream from Canada","live","0","en","0","0","0","","2026-04-24 00:56:06","2026-04-24 00:56:06");
INSERT INTO `channels` VALUES("54","CBC News","news","https://www.youtube.com/watch?v=ojX6k_6-8dI","youtube","","CBC News - Live news stream from Canada","live","0","en","0","0","0","","2026-04-24 00:56:06","2026-04-24 00:56:06");
INSERT INTO `channels` VALUES("55","Globo News","news","https://www.youtube.com/watch?v=ojX6k_6-8dI","youtube","","Globo News - Live news stream from Brazil","live","0","en","0","0","0","","2026-04-24 00:56:06","2026-04-24 00:56:06");
INSERT INTO `channels` VALUES("56","The Jerusalem Post","news","https://www.youtube.com/watch?v=ojX6k_6-8dI","youtube","","The Jerusalem Post - Live news stream from Israel","live","0","en","0","0","0","","2026-04-24 00:56:06","2026-04-24 00:56:06");
INSERT INTO `channels` VALUES("57","Al Arabiya","news","https://youtu.be/n7eQejkXbnM","youtube","","Al Arabiya - Live news stream from Saudi Arabia","live","0","en","0","0","0","","2026-04-24 00:56:06","2026-04-24 01:01:45");
INSERT INTO `channels` VALUES("58","Arab News","news","https://youtu.be/rXnG4eiVVdM","youtube","","Arab News - Live news stream from Saudi Arabia","live","0","en","0","0","0","","2026-04-24 00:56:06","2026-04-24 01:04:09");
INSERT INTO `channels` VALUES("59","Daily Sabah","news","https://www.youtube.com/watch?v=ojX6k_6-8dI","youtube","","Daily Sabah - Live news stream from Turkey","live","0","en","0","0","0","","2026-04-24 00:56:06","2026-04-24 00:56:06");
INSERT INTO `channels` VALUES("60","Hurriyet","news","https://www.youtube.com/watch?v=ojX6k_6-8dI","youtube","","Hurriyet - Live news stream from Turkey","live","0","en","0","0","0","","2026-04-24 00:56:06","2026-04-24 00:56:06");
INSERT INTO `channels` VALUES("61","Dawn News","news","https://www.youtube.com/watch?v=ojX6k_6-8dI","youtube","","Dawn News - Live news stream from PK","live","0","en","0","0","0","","2026-04-24 00:56:06","2026-04-24 00:56:06");
INSERT INTO `channels` VALUES("62","The News","news","https://www.youtube.com/watch?v=ojX6k_6-8dI","youtube","","The News - Live news stream from PK","live","0","en","0","0","0","","2026-04-24 00:56:06","2026-04-24 00:56:06");
INSERT INTO `channels` VALUES("63","Express Tribune","news","https://www.youtube.com/watch?v=ojX6k_6-8dI","youtube","","Express Tribune - Live news stream from PK","live","0","en","0","0","0","","2026-04-24 00:56:06","2026-04-24 00:56:06");
INSERT INTO `channels` VALUES("64","Geo News","news","https://www.youtube.com/watch?v=ojX6k_6-8dI","youtube","","Geo News - Live news stream from PK","live","0","en","0","0","0","","2026-04-24 00:56:06","2026-04-24 00:56:06");
INSERT INTO `channels` VALUES("65","ARY News","news","https://www.youtube.com/watch?v=ojX6k_6-8dI","youtube","","ARY News - Live news stream from PK","live","0","en","0","0","0","","2026-04-24 00:56:06","2026-04-24 00:56:06");
INSERT INTO `channels` VALUES("66","Bkuc","news","https://web.facebook.com/reel/26453739217608751","youtube","","","live","2","en","PK","0","1","","2026-04-24 01:19:07","2026-04-24 01:42:49");


CREATE TABLE `comment_likes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `comment_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `like_type` enum('like','dislike') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_comment_user` (`comment_id`,`user_id`),
  UNIQUE KEY `unique_comment_ip` (`comment_id`,`ip_address`),
  KEY `idx_comment_id` (`comment_id`),
  KEY `idx_user_id` (`user_id`),
  CONSTRAINT `fk_comment_likes_comment` FOREIGN KEY (`comment_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_comment_likes_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



CREATE TABLE `comment_reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `comment_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `reporter_ip` varchar(45) DEFAULT NULL,
  `reason` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('pending','reviewed','dismissed') NOT NULL DEFAULT 'pending',
  `reviewed_by` int(11) DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_comment_id` (`comment_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_status` (`status`),
  KEY `fk_comment_reports_reviewer` (`reviewed_by`),
  CONSTRAINT `fk_comment_reports_comment` FOREIGN KEY (`comment_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_comment_reports_reviewer` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_comment_reports_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_news_id` (`news_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_parent_id` (`parent_id`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_news_status` (`news_id`,`status`),
  KEY `idx_comments_news_created` (`news_id`,`created_at`),
  KEY `idx_comments_parent_created` (`parent_id`,`created_at`),
  CONSTRAINT `fk_comments_news` FOREIGN KEY (`news_id`) REFERENCES `news` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_comments_parent` FOREIGN KEY (`parent_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_comments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `comments` VALUES("22","21","1","","Admin","admin@pklivenews.com","hi","approved","","","0","0","0","","2026-04-24 00:23:01","2026-04-24 00:23:01");
INSERT INTO `comments` VALUES("23","27","14","","","","nj","approved","","","0","0","0","","2026-04-27 13:01:44","2026-04-27 13:01:44");
INSERT INTO `comments` VALUES("24","27","14","","","","nj","approved","","","0","0","0","","2026-04-27 13:01:44","2026-04-27 13:01:44");
INSERT INTO `comments` VALUES("26","27","14","","","","v","approved","","","0","0","0","","2026-04-27 22:14:57","2026-04-27 22:14:57");


CREATE TABLE `content_patterns` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pattern_name` varchar(255) NOT NULL,
  `pattern_type` enum('sensationalism','bias','misinformation','clickbait','propaganda') NOT NULL,
  `pattern_regex` text DEFAULT NULL,
  `pattern_keywords` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`pattern_keywords`)),
  `confidence_weight` decimal(3,2) DEFAULT 0.50,
  `description` text DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_pattern_type` (`pattern_type`),
  KEY `idx_active` (`active`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `content_patterns` VALUES("1","Breaking News Alert","sensationalism","","[\"breaking\",\"urgent\",\"alert\",\"shocking\"]","0.70","Detects sensational breaking news language","1","2026-04-09 19:15:39","2026-04-09 19:15:39");
INSERT INTO `content_patterns` VALUES("2","Clickbait Headlines","clickbait","","[\"you won\'t believe\",\"shocking\",\"revealed\",\"secret\"]","0.75","Detects clickbait headline patterns","1","2026-04-09 19:15:39","2026-04-09 19:15:39");
INSERT INTO `content_patterns` VALUES("3","Conspiracy Language","misinformation","","[\"conspiracy\",\"cover up\",\"hidden truth\",\"they don\'t want you to know\"]","0.80","Detects conspiracy theory language","1","2026-04-09 19:15:39","2026-04-09 19:15:39");
INSERT INTO `content_patterns` VALUES("4","Emotional Manipulation","bias","","[\"outrageous\",\"disgusting\",\"horrifying\",\"unbelievable\"]","0.65","Detects emotionally manipulative language","1","2026-04-09 19:15:39","2026-04-09 19:15:39");
INSERT INTO `content_patterns` VALUES("5","Unverified Claims","misinformation","","[\"sources say\",\"rumors suggest\",\"allegedly\",\"reportedly\"]","0.60","Detects unverified claim indicators","1","2026-04-09 19:15:39","2026-04-09 19:15:39");


CREATE TABLE `edition_articles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `edition_id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  `order_index` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_edition_article` (`edition_id`,`article_id`),
  KEY `idx_edition_id` (`edition_id`),
  KEY `idx_article_id` (`article_id`),
  CONSTRAINT `edition_articles_ibfk_1` FOREIGN KEY (`edition_id`) REFERENCES `news_editions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `edition_articles_ibfk_2` FOREIGN KEY (`article_id`) REFERENCES `news` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



CREATE TABLE `edition_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `template_html` longtext DEFAULT NULL,
  `css_styles` text DEFAULT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_default` (`is_default`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `edition_templates` VALUES("1","Default Template","Default newspaper edition template","<div class=\"edition-header\">\n        <h1>{{edition_title}}</h1>\n        <p class=\"edition-date\">{{edition_date}}</p>\n    </div>\n    <div class=\"edition-content\">\n        {{articles_loop}}\n        <div class=\"article\">\n            <h3>{{article_title}}</h3>\n            <p>{{article_summary}}</p>\n        </div>\n        {{articles_loop_end}}\n    </div>",".edition-header { text-align: center; margin-bottom: 30px; }\n    .edition-content { margin: 20px 0; }\n    .article { margin-bottom: 20px; padding: 15px; border: 1px solid #ddd; }","1","2026-04-09 10:45:06","2026-04-09 10:45:06");


CREATE TABLE `events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `event_date` date NOT NULL,
  `event_time` time DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `type` enum('conference','meeting','webinar','workshop','social','sports','political','other') DEFAULT 'other',
  `status` enum('upcoming','ongoing','completed','cancelled') DEFAULT 'upcoming',
  `priority` enum('low','medium','high','urgent') DEFAULT 'medium',
  `image` varchar(255) DEFAULT NULL,
  `url` varchar(500) DEFAULT NULL,
  `organizer` varchar(255) DEFAULT NULL,
  `contact_email` varchar(255) DEFAULT NULL,
  `max_attendees` int(11) DEFAULT NULL,
  `current_attendees` int(11) DEFAULT 0,
  `is_public` tinyint(1) DEFAULT 1,
  `requires_registration` tinyint(1) DEFAULT 0,
  `registration_deadline` datetime DEFAULT NULL,
  `tags` varchar(500) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `event_date` (`event_date`),
  KEY `status` (`status`),
  KEY `type` (`type`),
  KEY `category` (`category`),
  KEY `priority` (`priority`),
  KEY `created_by` (`created_by`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



CREATE TABLE `languages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(10) NOT NULL,
  `name` varchar(100) NOT NULL,
  `native_name` varchar(100) NOT NULL,
  `flag_icon` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `languages` VALUES("1","en","English","English","us","1","1","2026-04-09 11:00:30");
INSERT INTO `languages` VALUES("2","ur","Urdu"," Urdu","pk","1","2","2026-04-09 11:00:30");
INSERT INTO `languages` VALUES("3","hi","Hindi"," ","in","1","3","2026-04-09 11:00:30");
INSERT INTO `languages` VALUES("4","zh","Chinese"," ","cn","1","4","2026-04-09 11:00:30");
INSERT INTO `languages` VALUES("5","ps","Pashto"," ","af","1","5","2026-04-09 11:00:30");


CREATE TABLE `live_chat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `channel_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_deleted` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `live_chat` VALUES("1","28","Guest","go","2026-04-24 10:24:43","0");


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
  `stopped_at` timestamp NULL DEFAULT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



CREATE TABLE `news` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(500) NOT NULL,
  `slug` varchar(500) DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `excerpt` text DEFAULT NULL,
  `image` varchar(500) DEFAULT NULL,
  `video_url` varchar(500) DEFAULT NULL,
  `video_path` varchar(500) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `channel_id` int(11) DEFAULT NULL,
  `author_id` int(11) DEFAULT NULL,
  `source_url` varchar(500) DEFAULT NULL,
  `status` enum('published','draft','featured','archived') NOT NULL DEFAULT 'published',
  `is_breaking` tinyint(1) NOT NULL DEFAULT 0,
  `news_type` varchar(50) DEFAULT 'article',
  `views` int(11) NOT NULL DEFAULT 0,
  `likes_count` int(11) DEFAULT 0,
  `comment_count` int(11) DEFAULT 0,
  `engagement_score` decimal(10,2) DEFAULT 0.00,
  `share_count` int(11) DEFAULT 0,
  `likes` int(11) NOT NULL DEFAULT 0,
  `shares` int(11) NOT NULL DEFAULT 0,
  `published_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `sentiment_score` decimal(3,2) DEFAULT 0.00,
  `sentiment_label` varchar(20) DEFAULT 'neutral',
  `summary_only` tinyint(1) DEFAULT 0,
  `image_type` enum('manual','rss','ai','scraped') DEFAULT 'manual',
  `media_type` enum('text','image','video') DEFAULT 'text',
  `source_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_news_type` (`news_type`),
  KEY `idx_news_status` (`status`,`news_type`),
  KEY `idx_news_created_at` (`created_at`),
  KEY `idx_news_source_url` (`source_url`(255)),
  KEY `fk_news_channel` (`channel_id`),
  CONSTRAINT `fk_news_channel` FOREIGN KEY (`channel_id`) REFERENCES `channels` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=91 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `news` VALUES("6","Muhammad Kashif: Lead Developer &amp; System Architect","muhammad-kashif-lead-developer-system-architect","<p class=\"text-white-50 mb-4\">Expert in PHP, MySQL, and modern web technologies. Developed comprehensive news management system with 40+ news channels, live broadcasting, weather integration, voting/polling systems, product sharing, and job portal.</p>\n<div class=\"tech-skills mb-3\"><span class=\"badge bg-primary me-2 mb-2\">PHP 8+</span>&nbsp;<span class=\"badge bg-success me-2 mb-2\">MySQL</span>&nbsp;<span class=\"badge bg-info me-2 mb-2\">JavaScript</span>&nbsp;<span class=\"badge bg-warning me-2 mb-2\">Bootstrap</span></div>\n<div class=\"achievements\">\n<h6 class=\"text-white mb-3\">🏆 Key Achievements:</h6>\n<ul class=\"text-start text-white-50\">\n<li>40+ News Channel Integration</li>\n<li>Live Broadcasting System</li>\n<li>Weather API Integration</li>\n<li>Real-time Voting &amp; Polling</li>\n<li>Product Sharing Platform</li>\n<li>Job Portal Development</li>\n</ul>\n</div>","Expert in PHP, MySQL, and modern web technologies. Developed comprehensive news management system with 40+ news channels, live broadcasting, weather integration, voting/polling systems, product sharing, and job portal.\n\nPHP 8+ MySQL JavaScript Bootstrap\n🏆 Key Achievements:\n40+ News Channel Integration\nLive Broadcasting System\nWeather API Integration\nReal-time Voting &amp; Polling\nProduct Sharing Platform\nJob Portal Development","uploads/news/69d85af48f0a4.jpg","","","2","","","https://example.com/political-news","published","1","article","7","0","0","0.00","0","0","0","2026-04-11 07:04:41","2026-04-09 10:41:28","2026-04-25 08:29:31","0.00","neutral","0","manual","text","PK Live News");
INSERT INTO `news` VALUES("9","Muhammad Ibraheem: Project Manager &amp;amp; UX Designer","muhammad-ibraheem-project-manager-ux-designer","<p class=\"text-white-50 mb-4\">Experienced in project management and system architecture. Ensuring quality delivery of complex news platforms with focus on user experience, performance optimization, and advanced feature integration.</p>\n<div class=\"tech-skills mb-3\"><span class=\"badge bg-primary me-2 mb-2\">Project Mgmt</span>&nbsp;<span class=\"badge bg-success me-2 mb-2\">UI/UX</span>&nbsp;<span class=\"badge bg-info me-2 mb-2\">Analytics</span>&nbsp;<span class=\"badge bg-warning me-2 mb-2\">SEO</span></div>\n<div class=\"achievements\">\n<h6 class=\"text-white mb-3\">🎯 Project Excellence:</h6>\n<ul class=\"text-start text-white-50\">\n<li>Multi-Channel News Management</li>\n<li>Advanced Admin Panel</li>\n<li>User Engagement Systems</li>\n<li>Security Implementation</li>\n<li>Performance Optimization</li>\n<li>Mobile Responsive Design</li>\n</ul>\n</div>","Experienced in project management and system architecture. Ensuring quality delivery of complex news platforms with focus on user experience, performance optimization, and advanced feature integration.\n\nProject Mgmt UI/UX Analytics SEO\n🎯 Project Excellence:\nMulti-Channel News Management\nAdvanced Admin Panel\nUser Engagement Systems\nSecurity Implementation\nPerformance Optimization\nMobile Responsive Design","uploads/news/img_69d7b42d3c61c_1775744045.jpg","","","2","","1","","published","1","article","14","0","0","0.00","0","0","0","2026-04-11 07:02:31","2026-04-09 19:14:05","2026-04-25 08:29:31","0.00","neutral","0","manual","text","PK Live News");
INSERT INTO `news` VALUES("12","🚨پشاور زلمی کے نام بڑا ریکارڈ🚨","","بابر اعظم اور کوسل مینڈس کا کراچی کنگز کے خلاف شاندار مظاہرہ، ایچ بی ایل پی ایس ایل میں اب تک کی سب سے زیادہ شراکت کا ریکارڈ قائم🙌🔥","بابر اعظم اور کوسل مینڈس کا کراچی کنگز کے خلاف شاندار مظاہرہ، ایچ بی ایل پی ایس ایل میں اب تک کی سب سے زیادہ شرا?...","uploads/news/img_69d9084daca67_1775831117.jpeg","","","2","","8","","published","0","article","0","0","0","0.00","0","0","0","2026-04-10 19:25:17","2026-04-10 19:25:17","2026-04-25 08:29:31","0.00","neutral","0","manual","text","PK Live News");
INSERT INTO `news` VALUES("15","Hawaii’s Kilauea erupts, blasting lava sky-high","hawaii-s-kilauea-erupts-blasting-lava-sky-high","<p>Lava shot into the air, illuminating the night sky, as <strong>Hawaii\'s Kīlauea volcano erupted</strong>, with fountains reaching up to 190 metres.</p>\n<p><em><strong>Source:</strong> <a href=\"https://www.aljazeera.com/video/newsfeed/2026/4/10/hawaiis-kilauea-erupts-blasting-lava-sky-high?traffic_source=rss\" target=\"_blank\" rel=\"noopener\">Al Jazeera</a></em></p>\n<p><strong><a href=\"https://www.aljazeera.com/video/newsfeed/2026/4/10/hawaiis-kilauea-erupts-blasting-lava-sky-high?traffic_source=rss\" target=\"_blank\" rel=\"noopener\">Read full story on Al Jazeera</a></strong></p>","Lava shot into the air, illuminating the night sky, as Hawaiis Klauea volcano erupted.","uploads/news/69d9a7255546b.png","","","2","","1","https://www.aljazeera.com/video/newsfeed/2026/4/10/hawaiis-kilauea-erupts-blasting-lava-sky-high?traffic_source=rss","published","1","rss_import","1","0","0","0.00","0","0","0","2026-04-11 06:43:08","2026-04-10 22:30:03","2026-04-25 08:29:31","0.00","0","0","manual","text","PK Live News");
INSERT INTO `news` VALUES("16","EU airline industry warns of fuel shortages if Strait of Hormuz stays closed","eu-airline-industry-warns-of-fuel-shortages-if-strait-of-hormuz-stays-closed","<p class=\"sc-1a18e57c-0 HooNV\">Europe will suffer jet fuel shortages in just three weeks if the the Strait of Hormuz does not reopen, the trade body for the continent\'s airports has warned.</p>\n<p class=\"sc-1a18e57c-0 HooNV\">The Persian Gulf is a major source of aviation fuel, accounting for about 50% of Europe\'s imports.</p>\n<p class=\"sc-1a18e57c-0 HooNV\">Airports Council International (ACI) Europe said its members had \"increasing concerns\" about the availability of jet fuel, particularly with the approach of the summer tourism season.</p>\n<p><em><strong>Source:</strong> <a href=\"https://www.bbc.com/news/articles/c3w37ggp011o?at_medium=RSS&amp;at_campaign=rss\" target=\"_blank\" rel=\"noopener\">BBC News</a></em></p>\n<p><strong><a href=\"https://www.bbc.com/news/articles/c3w37ggp011o?at_medium=RSS&amp;at_campaign=rss\" target=\"_blank\" rel=\"noopener\">Read full story on BBC News</a></strong></p>","The trade body for European airports said if the Strait of Hormuz did not open in the next three weeks, there could be shortages.","uploads/news/69d93c0fb24fc.webp","","","2","","1","https://www.bbc.com/news/articles/c3w37ggp011o?at_medium=RSS&at_campaign=rss","published","1","rss_import","1","0","0","0.00","0","0","0","2026-04-11 06:11:01","2026-04-10 22:30:10","2026-04-25 08:29:31","0.00","0","0","manual","text","PK Live News");
INSERT INTO `news` VALUES("18","Back to Earth: What happens to the Artemis II astronauts now?","back-to-earth-what-happens-to-the-artemis-ii-astronauts-now","<p class=\"sc-1a18e57c-0 HooNV\">The Artemis II crew have safely returned home after re-entering Earth\'s atmosphere at 25,000mph (40,000km/h), splashing down off the coast of California.</p>\n<p class=\"sc-1a18e57c-0 HooNV\">They have travelled deeper into space than any humans before them - just over 4,000 miles more than the record of 248,655 set by Apollo 13 in 1970.</p>\n<p class=\"sc-1a18e57c-0 HooNV\">Astronauts are highly trained to cope with the physical and mental strain of space.</p>\n<p class=\"sc-1a18e57c-0 HooNV\">Although it might seem like it would be a difficult experience to endure, astronauts talk about being in space as the highlight of their lives and say they would return in an instant.</p>\n<p class=\"sc-1a18e57c-0 HooNV\">In a press conference before landing, Christina Koch said the inconveniences, such as freeze-dried food or a toilet without much privacy, were worth it.</p>\n<p><em><strong>Source:</strong> <a href=\"https://www.bbc.com/news/articles/cpwjvgv2d4no?at_medium=RSS&amp;at_campaign=rss\" target=\"_blank\" rel=\"noopener\">BBC News</a></em></p>\n<p><strong><a href=\"https://www.bbc.com/news/articles/cpwjvgv2d4no?at_medium=RSS&amp;at_campaign=rss\" target=\"_blank\" rel=\"noopener\">Read full story on BBC News</a></strong></p>","The astronauts will have medical checks and will be reunited with their families.","uploads/news/69d9a8480d1d0.webp","","","2","","1","https://www.bbc.com/news/articles/cpwjvgv2d4no?at_medium=RSS&at_campaign=rss","published","1","rss_import","1","0","0","0.00","0","0","0","2026-04-11 06:54:55","2026-04-11 06:43:29","2026-04-25 08:29:31","0.00","0","0","manual","text","PK Live News");
INSERT INTO `news` VALUES("19","Wiseman joins his fellow crew members on front porch","iseman-joins-his-fellow-crew-members-on-front-porch","Commander Reid Wiseman has now exited the Orion module, shortly after the third crew member left the spacecraft.\nAll the astronauts are now waiting on the front porch to be escorted by two Navy helicopters.","Commander Reid Wiseman has now exited the Orion module, shortly after the third crew member left the spacecraft.\nAll the astronauts are now waiting on the front porch to be escorted by two Navy helic...","uploads/news/img_69d9a901d7095_1775872257.webp","","","2","","1","","published","0","article","0","0","0","0.00","0","0","0","2026-04-11 06:50:57","2026-04-11 06:50:57","2026-04-25 08:29:31","0.00","neutral","0","manual","text","PK Live News");
INSERT INTO `news` VALUES("20","Rawalpindiz vs Quetta Gladiators","awalpindiz-vs-uetta-ladiators","#RileeRossouw&#039;s half-century backed by a complete bowling performance kept Rawalpindiz winless in PSL 11. 🇵🇰💥","#RileeRossouw&#039;s half-century backed by a complete bowling performance kept Rawalpindiz winless in PSL 11. 🇵🇰💥...","uploads/news/img_69d9ae50ae230_1775873616.jpeg","","","2","","1","","published","0","article","2","0","0","0.00","0","0","0","2026-04-11 07:13:36","2026-04-11 07:13:36","2026-04-25 08:29:31","0.00","neutral","0","manual","text","PK Live News");
INSERT INTO `news` VALUES("21","Pakistan ambassador speaks to Al Jazeera on eve of US-Iran talks","pakistan-ambassador-speaks-to-al-jazeera-on-eve-of-us-iran-talks","<p><strong>Islamabad, Pakistan &ndash;&nbsp;</strong>Pavements&nbsp;are being painted, an already formidable security presence is being bolstered, and an air of anticipation &mdash; and anxiety &mdash; is gripping Pakistan&rsquo;s capital as it prepares to host meetings that the world will watch this weekend.</p>\n<p>Exactly six weeks after the United States and Israel launched coordinated strikes on Iran that&nbsp;<a href=\"https://www.aljazeera.com/news/2026/2/28/irans-supreme-leader-ali-khamenei-killed-in-us-israeli-attacks-reports\">killed Supreme Leader Ayatollah Ali Khamenei</a>, set off a war that has killed thousands of people across multiple countries, shut down the world&rsquo;s most critical oil passage and sent energy prices soaring, Islamabad will on Saturday host talks involving top US and Iranian officials.</p>\n<section class=\"more-on\">\n<h2 class=\"more-on__heading\">Recommended Stories</h2>\n<span class=\"screen-reader-text\">list of 4 items</span>\n<ul class=\"more-on__list\">\n<li class=\"more-on__article\"><span class=\"screen-reader-text\">list 1 of 4</span><a class=\"more-on__link\" href=\"https://www.aljazeera.com/features/2026/4/8/how-pakistan-managed-to-get-the-us-and-iran-to-a-ceasefire\">How Pakistan managed to get the US and Iran to a ceasefire</a></li>\n<li class=\"more-on__article\"><span class=\"screen-reader-text\">list 2 of 4</span><a class=\"more-on__link\" href=\"https://www.aljazeera.com/news/2026/4/7/why-jd-vance-joined-pakistans-last-ditch-us-iran-mediation-efforts\">Why JD Vance joined Pakistan&rsquo;s last-ditch US-Iran mediation efforts</a></li>\n<li class=\"more-on__article\"><span class=\"screen-reader-text\">list 3 of 4</span><a class=\"more-on__link\" href=\"https://www.aljazeera.com/news/2026/3/27/nixon-to-trump-pakistans-long-record-as-backchannel-between-rival-powers\">Nixon to Trump: Pakistan&rsquo;s long record as backchannel between rival powers</a></li>\n<li class=\"more-on__article\"><span class=\"screen-reader-text\">list 4 of 4</span><a class=\"more-on__link\" href=\"https://www.aljazeera.com/news/2026/3/31/will-china-join-pakistan-led-efforts-to-mediate-us-iran-peace\">Will China join Pakistan-led efforts to mediate US-Iran peace?</a></li>\n</ul>\n<span class=\"screen-reader-text\">end of list</span></section>\n<p>The meetings come days after both Washington and Tehran agreed to a Pakistan-mediated two-week ceasefire, and at a time when that truce is already under strain amid different interpretations of the terms of the pause in fighting &mdash; and Israel&rsquo;s intensified bombing of Lebanon.</p>\n<p>Iran&rsquo;s attacks on its Gulf neighbours, apart from Israel, amid the war have also left the world&rsquo;s biggest energy export hub and a critical nerve centre of trade, tourism and innovation on edge since the fighting started on February 28. Tehran&rsquo;s decision soon after to in effect shut down the Strait of Hormuz &mdash; through which 20 percent of the world&rsquo;s oil and gas passes during peacetime &mdash; except to ships from countries that negotiated deals with it, rattled global markets and drove energy prices to record highs.</p>\n<p>This coming weekend, senior representatives from key players in the war will converge in Pakistan&rsquo;s leafy capital in the lower reaches of the Margalla Hills.</p>\n<p><em><strong>Source:</strong> <a href=\"https://www.aljazeera.com/video/newsfeed/2026/4/11/pakistan-ambassador-speaks-to-al-jazeera-on-eve-of-us-iran-talks?traffic_source=rss\" target=\"_blank\" rel=\"noopener\">Al Jazeera</a></em></p>\n<p><strong><a href=\"https://www.aljazeera.com/video/newsfeed/2026/4/11/pakistan-ambassador-speaks-to-al-jazeera-on-eve-of-us-iran-talks?traffic_source=rss\" target=\"_blank\" rel=\"noopener\">Read full story on Al Jazeera</a></strong></p>","Pakistans ambassador to the US said weeks of intense diplomatic efforts have led to a shared commitment from all sides.","uploads/news/69d9d5355e60c.jpg","","","2","","1","https://www.aljazeera.com/video/newsfeed/2026/4/11/pakistan-ambassador-speaks-to-al-jazeera-on-eve-of-us-iran-talks?traffic_source=rss","published","1","rss_import","3","0","0","0.00","0","0","0","2026-04-11 09:59:56","2026-04-11 09:52:21","2026-04-25 08:29:31","0.00","0","0","manual","text","PK Live News");
INSERT INTO `news` VALUES("22","A shadowy, pro-Iranian group claimed a spate of attacks in Europe. But it might be a facade","shadowy-pro-ranian-group-claimed-a-spate-of-attacks-in-urope-ut-it-might-be-a-facade","<p>London &mdash; A shadowy, pro-Iran group has claimed responsibility for a spate of recent attacks on Jewish communities and American interests in Europe. The incidents, which the group posted about via social media accounts affiliated with pro-Iranian militias, include an arson attack on Jewish community-run ambulances in the United Kingdom, an explosive device detonated in front of a synagogue in Belgium and a foiled attack on a Bank of America office in France.</p>","London —  A shadowy, pro-Iran group has claimed responsibility for a spate of recent attacks on Jewish communities and American interests in Europe.\nThe incidents, which the group posted about via ...","uploads/news/69d9d68b1e728.png","","","2","","1","","published","1","article","2","0","0","0.00","0","0","0","2026-04-11 10:05:59","2026-04-11 09:54:58","2026-04-25 08:29:31","0.00","neutral","0","manual","text","PK Live News");
INSERT INTO `news` VALUES("27","Iranian authorities remain defiant, urge supporters to stay in streets","iranian-authorities-remain-defiant-urge-supporters-to-stay-in-streets","Tehran, Iran – Iranian authorities say the United States needs to do more if an agreement is to be made to end the war as they urge their supporters to maintain control of the streets.\n\nThe US delegation at Saturday’s marathon talks in Islamabad, Pakistan, “ultimately failed to gain the trust of the Iranian delegation in this round of negotiations”, said Mohammad Bagher Ghalibaf, the parliament speaker who led the Iranian team.\n\nRecommended Stories\nlist of 3 items\nlist 1 of 3Oil tankers exit Strait of Hormuz amid fragile US-Iran ceasefire\nlist 2 of 3Iran must not charge tolls in Strait of Hormuz, UN maritime chief says\nlist 3 of 3Ceasefire brings some relief for Iranians but economic outlook remains grim\nend of list\nUS President Donald Trump said on Sunday that the US Navy will immediately begin the process of “blockading any and all ships trying to enter, or leave, the Strait of Hormuz” in Iran’s southern waters. He also said the US military remains “locked and loaded” and will “finish up” Iran at the “appropriate moment”.\n\nThe fact that the Iranian delegation did not accede to Washington’s core demands of eliminating nuclear enrichment on Iranian soil and ending Iranian control over the Strait of Hormuz was welcomed by Iranian authorities on Sunday as they projected defiance.\n\n<p><em><strong>Source:</strong> <a href=\"https://www.aljazeera.com/news/2026/4/12/iranian-authorities-remain-defiant-urge-supporters-to-stay-in?traffic_source=rss\" target=\"_blank\" rel=\"noopener\">Al Jazeera</a></em></p>\n\n<p><strong><a href=\"https://www.aljazeera.com/news/2026/4/12/iranian-authorities-remain-defiant-urge-supporters-to-stay-in?traffic_source=rss\" target=\"_blank\" rel=\"noopener\">Read full story on Al Jazeera</a></strong></p>","Mohammad Bagher Ghalibaf, who led Iran&#039;s delegation in talks to end the war, said US delegation &#039;failed to gain trust&#039;.","uploads/news/69dbe0a22d5d6.webp","","","2","","1","https://www.aljazeera.com/news/2026/4/12/iranian-authorities-remain-defiant-urge-supporters-to-stay-in?traffic_source=rss","published","1","rss_import","0","0","0","0.00","0","0","0","2026-04-12 23:13:01","2026-04-12 23:03:39","2026-04-25 08:29:31","0.00","0","0","manual","text","PK Live News");
INSERT INTO `news` VALUES("28","After Iran talks falter, the big question is what happens next?","after-iran-talks-falter-the-big-question-is-what-happens-next","Twenty-one hours was not enough to end 47 years of hostility between Iran and the US.\n\nThe historic high-level talks in Islamabad, during a pause in weeks of grievous war, were always unlikely to end any other way.\n\nCalling this marathon negotiating session a failure belies the scale of the challenge in narrowing wide gaps on complex issues ranging from age-old suspicion about Iran\'s nuclear programme to new challenges this war has thrown up - most of all Iran\'s control of the strategic Strait of Hormuz, whose closure is causing economic shocks worldwide.\n\nTo do a deal, they also needed to overcome a deep chasm of distrust.\n\nA day ago, it wasn\'t even certain the two sides would meet, and even more, sit down in the same room.\n\nA longstanding political taboo was broken.\n\nThe urgent question now is: what happens next?\n\nWhat happens to the contested two-week ceasefire which pulled the world back from US President Donald Trump\'s alarming threat to destroy a \"whole civilisation\" in Iran?\n\nWould the US president be ready to send his negotiators back to the bargaining table?\n\nWe\'re hearing reports from sources here in Islamabad that some conversations have continued after US Vice-President JD Vance boarded his plane at sunrise, declaring the US delegation had made their \"final and best offer\".\n\n<p><em><strong>Source:</strong> <a href=\"https://www.bbc.com/news/articles/c5y943x2g8qo?at_medium=RSS&at_campaign=rss\" target=\"_blank\" rel=\"noopener\">BBC News</a></em></p>\n\n<p><strong><a href=\"https://www.bbc.com/news/articles/c5y943x2g8qo?at_medium=RSS&at_campaign=rss\" target=\"_blank\" rel=\"noopener\">Read full story on BBC News</a></strong></p>","Twenty-one hours was not enough to end 47 years of hostility between Iran and the US, writes the BBC&#039;s Lyse Doucet.","uploads/news/69dbdf759d7e8.webp","","","2","","1","https://www.bbc.com/news/articles/c5y943x2g8qo?at_medium=RSS&at_campaign=rss","published","1","rss_import","0","0","0","0.00","0","0","0","2026-04-12 23:07:59","2026-04-12 23:03:40","2026-04-25 08:29:31","0.00","0","0","manual","text","PK Live News");
INSERT INTO `news` VALUES("29","View image in fullscreen US-Israel war on Iran Planeloads of negotiators and too little time: US and Iran’s 21 hours of talks","View image in fullscreen US-Israel war on Iran Planeloads of negotiators and too little time: US and Iran’s 21 hours of talks","<p>The two sides turned up to test one another’s resolve. It was probably unrealistic to expect a dispute that has taken up years of discussion to be settled in one marathon session</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.aljazeera.com/video/the-listening-post/2026/4/12/us-media-trapped-between-oligarchy-and-presidency?traffic_source=rss\" target=\"_blank\" rel=\"noopener\">Al Jazeera</a></em></p>\n\n<p><strong><a href=\"https://www.aljazeera.com/video/the-listening-post/2026/4/12/us-media-trapped-between-oligarchy-and-presidency?traffic_source=rss\" target=\"_blank\" rel=\"noopener\">Read full story on Al Jazeera</a></strong></p>","The two sides turned up to test one another’s resolve. It was probably unrealistic to expect a dispute that has taken up years of discussion to be settled in one marathon session","uploads/news/69dbe92cabbcb.png","","","2","","1","https://www.aljazeera.com/video/the-listening-post/2026/4/12/us-media-trapped-between-oligarchy-and-presidency?traffic_source=rss","published","1","rss_import","0","0","0","0.00","0","0","0","2026-04-12 23:49:59","2026-04-12 23:38:21","2026-04-25 08:29:31","0.00","0","0","manual","text","PK Live News");
INSERT INTO `news` VALUES("30","Trump&#039;s Strait of Hormuz blockade threat raises risks and leaves predicaments unchanged","trump-s-strait-of-hormuz-blockade-threat-raises-risks-and-leaves-predicaments-unchanged","<p>After a diplomatic team led by Vice-President JD Vance tried, and failed, to reach a negotiated agreement to end the US war with Iran on Saturday, President Donald Trump had to decide his next move.</p>\n\nThat came on Sunday morning, in a series of Truth Social posts.\n\nThe US will impose a naval blockade of Iran, he wrote. \"No one who pays an illegal toll will have safe passage on the high seas,\" he wrote.\n\nHe also said that the US would continue clearing mines from the Strait of Hormuz in order to ensure a safe passage for allied shipping. The US military, he added, was \"locked and loaded\" and prepared to resume attacks against Iran at an \"appropriate moment\".\n\nHe went on to say that while progress had been made in the 20-hour negotiations in Islamabad, Iran would not meet the US demand that it abandon its nuclear ambitions.\n\nWhile his posts didn\'t have the apocalyptic bluster of last week\'s threat to end Iranian civilisation, they pose a number of new challenges – and risks – for the American side.\n\nLive updates on Trump\'s blockade threat\n<p><em><strong>Source:</strong> <a href=\"https://www.bbc.com/news/articles/c8dl5mly2rzo?at_medium=RSS&at_campaign=rss\" target=\"_blank\" rel=\"noopener\">BBC News</a></em></p>\n\n<p><strong><a href=\"https://www.bbc.com/news/articles/c8dl5mly2rzo?at_medium=RSS&at_campaign=rss\" target=\"_blank\" rel=\"noopener\">Read full story on BBC News</a></strong></p>","The conflict is now a test of wills - Irans capacity to absorb strikes versus Trumps tolerance for the war&#039;s costs.","uploads/news/69dbe7a3d61b3.webp","","","2","","1","https://www.bbc.com/news/articles/c8dl5mly2rzo?at_medium=RSS&at_campaign=rss","published","1","rss_import","2","0","0","0.00","0","0","0","2026-04-12 23:43:15","2026-04-12 23:38:29","2026-04-27 12:52:17","0.00","0","0","manual","text","PK Live News");
INSERT INTO `news` VALUES("34","Viktor Orban swept from power after 16 years ruling Hungary","viktor-orban-swept-from-power-after-16-years-ruling-hungary","<p>In a record turnout at the polls, Hungarians have voted out their long-serving, far-right Prime Minister Viktor Orban.</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.aljazeera.com/video/newsfeed/2026/4/13/viktor-orban-swept-from-power-after-16-years-ruling-hungary?traffic_source=rss\" target=\"_blank\" rel=\"noopener\">Al Jazeera</a></em></p>\n\n<p><strong><a href=\"https://www.aljazeera.com/video/newsfeed/2026/4/13/viktor-orban-swept-from-power-after-16-years-ruling-hungary?traffic_source=rss\" target=\"_blank\" rel=\"noopener\">Read full story on Al Jazeera</a></strong></p>","In a record turnout at the polls, Hungarians have voted out their long-serving, far-right Prime Minister Viktor Orban.","uploads/news/69dc7d0775f74.png","","","2","","1","https://www.aljazeera.com/video/newsfeed/2026/4/13/viktor-orban-swept-from-power-after-16-years-ruling-hungary?traffic_source=rss","published","1","rss_import","0","0","0","0.00","0","0","0","2026-04-13 10:20:21","2026-04-13 10:16:59","2026-04-25 08:29:31","0.00","0","0","manual","text","PK Live News");
INSERT INTO `news` VALUES("37","‘Terrible for foreign policy’: Trump attacks Pope Leo after peace appeal","terrible-for-foreign-policy-trump-attacks-pope-leo-after-peace-appeal","<p>Leo, who last year became the first US-born pope, has emerged as an outspoken critic of the US-Israeli war on Iran.</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.aljazeera.com/news/2026/4/13/terrible-for-foreign-policy-trump-attacks-pope-leo-after-peace-appeal?traffic_source=rss\" target=\"_blank\" rel=\"noopener\">Al Jazeera</a></em></p>\n\n<p><strong><a href=\"https://www.aljazeera.com/news/2026/4/13/terrible-for-foreign-policy-trump-attacks-pope-leo-after-peace-appeal?traffic_source=rss\" target=\"_blank\" rel=\"noopener\">Read full story on Al Jazeera</a></strong></p>","Leo, who last year became the first US-born pope, has emerged as an outspoken critic of the US-Israeli war on Iran.","uploads/news/69dc890b0b50a.png","","","8","","1","https://www.aljazeera.com/news/2026/4/13/terrible-for-foreign-policy-trump-attacks-pope-leo-after-peace-appeal?traffic_source=rss","published","1","rss_import","1","0","0","0.00","0","0","0","2026-04-13 11:12:19","2026-04-13 11:02:02","2026-04-25 08:29:31","0.00","0","0","manual","text","PK Live News");
INSERT INTO `news` VALUES("48","What people in Beirut think about the Lebanon-Israel negotiations","what-people-in-beirut-think-about-the-lebanon-israel-negotiations","<p>Lebanese leaders were in Washington earlier this month for the first direct negotiations with Israel in over 30 years.</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.aljazeera.com/video/newsfeed/2026/4/27/what-people-in-beirut-think-about-the-lebanon-israel-negotiations?traffic_source=rss\" target=\"_blank\" rel=\"noopener\">Al Jazeera</a></em></p>\n\n<p><strong><a href=\"https://www.aljazeera.com/video/newsfeed/2026/4/27/what-people-in-beirut-think-about-the-lebanon-israel-negotiations?traffic_source=rss\" target=\"_blank\" rel=\"noopener\">Read full story on Al Jazeera</a></strong></p>","Lebanese leaders were in Washington earlier this month for the first direct negotiations with Israel in over 30 years.","","","","1","","1","https://www.aljazeera.com/video/newsfeed/2026/4/27/what-people-in-beirut-think-about-the-lebanon-israel-negotiations?traffic_source=rss","published","0","rss_import","1","0","0","0.00","0","0","0","2026-04-28 01:11:23","2026-04-28 00:57:17","2026-04-28 02:01:01","0.00","0","0","manual","text","");
INSERT INTO `news` VALUES("49","Melania Trump urges ABC to \'take stand\' on Jimmy Kimmel after widow joke","melania-trump-urges-abc-to-take-stand-on-jimmy-kimmel-after-widow-joke","<p>In a parody aired days before the White House Correspondents\' Dinner, Kimmel called Melania an \"expectant widow\".</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.bbc.com/news/articles/c04x40d4424o?at_medium=RSS&at_campaign=rss\" target=\"_blank\" rel=\"noopener\">BBC World News</a></em></p>\n\n<p><strong><a href=\"https://www.bbc.com/news/articles/c04x40d4424o?at_medium=RSS&at_campaign=rss\" target=\"_blank\" rel=\"noopener\">Read full story on BBC World News</a></strong></p>","In a parody aired days before the White House Correspondents\' Dinner, Kimmel called Melania an \"expectant widow\".","","","","1","","1","https://www.bbc.com/news/articles/c04x40d4424o?at_medium=RSS&at_campaign=rss","published","0","rss_import","0","0","0","0.00","0","0","0","2026-04-28 01:11:23","2026-04-28 00:57:24","2026-04-28 01:11:23","0.00","0","0","manual","text","");
INSERT INTO `news` VALUES("50","Trump discussed Iran&#039;s Hormuz Strait proposal with top aides, White House says","trump-discussed-iran-s-hormuz-strait-proposal-with-top-aides-white-house-says","<p>The Trump administration has repeatedly insisted that the central goal of the conflict is keeping Iran from ever obtaining a nuclear weapon.</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.cnbc.com/2026/04/27/trump-iran-war-strait-of-hormuz-rubio.html\" target=\"_blank\" rel=\"noopener\">CNBC</a></em></p>\n\n<p><strong><a href=\"https://www.cnbc.com/2026/04/27/trump-iran-war-strait-of-hormuz-rubio.html\" target=\"_blank\" rel=\"noopener\">Read full story on CNBC</a></strong></p>","The Trump administration has repeatedly insisted that the central goal of the conflict is keeping Iran from ever obtaining a nuclear weapon.","uploads/news/69efc5c54a25f.webp","","","2","","1","https://www.cnbc.com/2026/04/27/trump-iran-war-strait-of-hormuz-rubio.html","published","1","rss_import","2","0","0","0.00","0","0","0","2026-04-28 01:23:46","2026-04-28 00:57:27","2026-04-28 01:24:10","0.00","0","0","manual","text","");
INSERT INTO `news` VALUES("51","US being ‘humiliated’ by Iran, says German Chancellor Friedrich Merz","us-being-humiliated-by-iran-says-german-chancellor-friedrich-merz","<p>Atlanticist leader says ill-prepared war is hurting Europes largest economy</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.ft.com/content/0a666824-4686-417c-9c1a-1393942cb3db\" target=\"_blank\" rel=\"noopener\">Financial Times</a></em></p>\n\n<p><strong><a href=\"https://www.ft.com/content/0a666824-4686-417c-9c1a-1393942cb3db\" target=\"_blank\" rel=\"noopener\">Read full story on Financial Times</a></strong></p>","Atlanticist leader says ill-prepared war is hurting Europes largest economy","uploads/news/69efcce21feb7.png","","","2","","1","https://www.ft.com/content/0a666824-4686-417c-9c1a-1393942cb3db","published","1","rss_import","1","0","0","0.00","0","0","0","2026-04-28 01:53:59","2026-04-28 00:57:39","2026-04-28 02:00:17","0.00","0","0","manual","text","");
INSERT INTO `news` VALUES("53","Here\'s a look inside security at the White House Correspondents\' Association Dinner","here-s-a-look-inside-security-at-the-white-house-correspondents-association-dinner","<p>Saturday\'s shooting at the White House Correspondents\' Association Dinner raised questions about how close the alleged gunman got to the president and what the Secret Service security looked like.</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.npr.org/2026/04/27/nx-s1-5801476/whca-dinner-security-secret-service-president-trump\" target=\"_blank\" rel=\"noopener\">NPR News</a></em></p>\n\n<p><strong><a href=\"https://www.npr.org/2026/04/27/nx-s1-5801476/whca-dinner-security-secret-service-president-trump\" target=\"_blank\" rel=\"noopener\">Read full story on NPR News</a></strong></p>","Saturday\'s shooting at the White House Correspondents\' Association Dinner raised questions about how close the alleged gunman got to the president and what the Secret Service security looked like.","","","","1","","1","https://www.npr.org/2026/04/27/nx-s1-5801476/whca-dinner-security-secret-service-president-trump","published","0","rss_import","0","0","0","0.00","0","0","0","2026-04-28 01:11:23","2026-04-28 00:57:50","2026-04-28 01:11:23","0.00","0","0","manual","text","");
INSERT INTO `news` VALUES("54","Knives out: Is a coup brewing in Kiev?","knives-out-is-a-coup-brewing-in-kiev","<p>As Zelensky faces pressure over war strategy and corruption fallout, Budanov is emerging as a powerful insider with presidential ambitions Read Full Article at RT.com</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.rt.com/russia/639184-knives-out-kiev-coup/?utm_source=rss&utm_medium=rss&utm_campaign=RSS\" target=\"_blank\" rel=\"noopener\">RT News</a></em></p>\n\n<p><strong><a href=\"https://www.rt.com/russia/639184-knives-out-kiev-coup/?utm_source=rss&utm_medium=rss&utm_campaign=RSS\" target=\"_blank\" rel=\"noopener\">Read full story on RT News</a></strong></p>","As Zelensky faces pressure over war strategy and corruption fallout, Budanov is emerging as a powerful insider with presidential ambitions Read Full Article at RT.com","uploads/news/rss_69efbfc75d6f1.jpeg","","","2","","1","https://www.rt.com/russia/639184-knives-out-kiev-coup/?utm_source=rss&utm_medium=rss&utm_campaign=RSS","published","0","rss_import","1","0","0","0.00","0","0","0","2026-04-27 19:05:25","2026-04-28 00:57:59","2026-04-28 01:02:35","0.00","0","0","rss","text","");
INSERT INTO `news` VALUES("55","German tourist dies after being bitten at snake show on family holiday in Egypt","german-tourist-dies-after-being-bitten-at-snake-show-on-family-holiday-in-egypt","<p>Man, 57, was watching snake-charming show when reptile crawled into his trousers, say German policeA German tourist has died after a snake crawled into his trousers and bit him as he watched a show in Egypt on a family holiday, police in Germany have said.The 57-year-old man was watching the snake-charming show at a hotel in Hurghada, a popular beach holiday destination on the Red Sea, in early April.</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.theguardian.com/world/2026/apr/27/german-tourist-dies-bite-snake-show-holiday-egypt\" target=\"_blank\" rel=\"noopener\">The Guardian</a></em></p>\n\n<p><strong><a href=\"https://www.theguardian.com/world/2026/apr/27/german-tourist-dies-bite-snake-show-holiday-egypt\" target=\"_blank\" rel=\"noopener\">Read full story on The Guardian</a></strong></p>","Man, 57, was watching snake-charming show when reptile crawled into his trousers, say German policeA German tourist has died after a snake crawled into his trousers and bit him as he watched a show in Egypt on a family holiday, police in Germany...","","","","1","","1","https://www.theguardian.com/world/2026/apr/27/german-tourist-dies-bite-snake-show-holiday-egypt","published","0","rss_import","0","0","0","0.00","0","0","0","2026-04-28 01:11:23","2026-04-28 00:58:11","2026-04-28 01:11:23","0.00","0","0","manual","text","");
INSERT INTO `news` VALUES("56","Cole Tomas Allen, Correspondents’ Dinner Shooting Suspect, Was Propelled by Outrage, Authorities Say","cole-tomas-allen-correspondents-dinner-shooting-suspect-was-propelled-by-outrage-authorities-say","<p>A man who has worked as a tutor and graduated from the California Institute of Technology was charged with trying to assassinate the president after an armed attack at the White House correspondents dinner.</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.nytimes.com/2026/04/27/us/politics/cole-allen-suspect-washington-correspondents-dinner-shooting.html\" target=\"_blank\" rel=\"noopener\">The New York Times</a></em></p>\n\n<p><strong><a href=\"https://www.nytimes.com/2026/04/27/us/politics/cole-allen-suspect-washington-correspondents-dinner-shooting.html\" target=\"_blank\" rel=\"noopener\">Read full story on The New York Times</a></strong></p>","A man who has worked as a tutor and graduated from the California Institute of Technology was charged with trying to assassinate the president after an armed attack at the White House correspondents dinner.","","","","1","","1","https://www.nytimes.com/2026/04/27/us/politics/cole-allen-suspect-washington-correspondents-dinner-shooting.html","published","0","rss_import","0","0","0","0.00","0","0","0","2026-04-28 01:11:23","2026-04-28 00:58:12","2026-04-28 01:11:23","0.00","0","0","manual","text","");
INSERT INTO `news` VALUES("57","Hezbollah and Israel swap threats and strikes across Lebanon’s border","hezbollah-and-israel-swap-threats-and-strikes-across-lebanon-s-border","<p>Iran-linked group reiterates defiance; Israeli defence minister threatens to burn all of Lebanon\'.</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.aljazeera.com/news/2026/4/27/hezbollah-and-israel-swap-threats-and-strikes-across-lebanons-border?traffic_source=rss\" target=\"_blank\" rel=\"noopener\">Al Jazeera</a></em></p>\n\n<p><strong><a href=\"https://www.aljazeera.com/news/2026/4/27/hezbollah-and-israel-swap-threats-and-strikes-across-lebanons-border?traffic_source=rss\" target=\"_blank\" rel=\"noopener\">Read full story on Al Jazeera</a></strong></p>","Iran-linked group reiterates defiance; Israeli defence minister threatens to burn all of Lebanon&#039;.","uploads/news/69efc4ee4fe2e.webp","","","2","","1","https://www.aljazeera.com/news/2026/4/27/hezbollah-and-israel-swap-threats-and-strikes-across-lebanons-border?traffic_source=rss","published","1","rss_import","0","0","0","0.00","0","0","0","2026-04-28 01:20:04","2026-04-28 01:04:29","2026-04-28 01:20:04","0.00","0","0","manual","text","");
INSERT INTO `news` VALUES("58","Starmer faces vote on inquiry over Mandelson vetting claims","starmer-faces-vote-on-inquiry-over-mandelson-vetting-claims","<p>No 10 brands the move \"a desperate political stunt by the Conservative Party\", which had asked for the vote.</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.bbc.com/news/articles/c3r3r2vzjp1o?at_medium=RSS&at_campaign=rss\" target=\"_blank\" rel=\"noopener\">BBC News</a></em></p>\n\n<p><strong><a href=\"https://www.bbc.com/news/articles/c3r3r2vzjp1o?at_medium=RSS&at_campaign=rss\" target=\"_blank\" rel=\"noopener\">Read full story on BBC News</a></strong></p>","No 10 brands the move \"a desperate political stunt by the Conservative Party\", which had asked for the vote.","","","","1","","1","https://www.bbc.com/news/articles/c3r3r2vzjp1o?at_medium=RSS&at_campaign=rss","published","0","rss_import","0","0","0","0.00","0","0","0","2026-04-28 01:11:23","2026-04-28 01:04:31","2026-04-28 01:11:23","0.00","0","0","manual","text","");
INSERT INTO `news` VALUES("59","Suspect in Washington dinner shooting charged with attempting to assassinate Trump","suspect-in-washington-dinner-shooting-charged-with-attempting-to-assassinate-trump","<p></p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.cbc.ca/news/world/livestory/white-house-correspondents-dinner-shooting-trump-targeted-charges-9.7177721?cmp=rss\" target=\"_blank\" rel=\"noopener\">CBC News</a></em></p>\n\n<p><strong><a href=\"https://www.cbc.ca/news/world/livestory/white-house-correspondents-dinner-shooting-trump-targeted-charges-9.7177721?cmp=rss\" target=\"_blank\" rel=\"noopener\">Read full story on CBC News</a></strong></p>","\n\nSource: CBC News\n\nRead full story on CBC News...","uploads/news/rss_69efc153be581.jpeg","","","1","","1","https://www.cbc.ca/news/world/livestory/white-house-correspondents-dinner-shooting-trump-targeted-charges-9.7177721?cmp=rss","published","0","rss_import","0","0","0","0.00","0","0","0","2026-04-25 21:34:12","2026-04-28 01:04:35","2026-04-28 01:09:47","0.00","0","0","rss","text","");
INSERT INTO `news` VALUES("62","Crazy Week em Ciudad del Este: onde comprar rápido e evitar filas na fronteira","crazy-week-em-ciudad-del-este-onde-comprar-rápido-e-evitar-filas-na-fronteira","<p>A proximidade da Crazy Week j comea a intensificar o movimento na fronteira entre Brasil e Paraguai, em um dos perodos mais aguardados do comrcio de Ciudad del Este. Em meio ao aumento do fluxo de turistas, especialmente brasileiros, um nome ganha protagonismo: New Zone Importados. Mais do que um ponto de compras, o espao vem se consolidando como um novo modelo comercial na regio, combinando convenincia, experincia e crescimento acelerado. Localizada logo aps a Ponte Internacional da Amizade, a New Zone se posiciona como a primeira grande parada para quem entra no Paraguai. A localizao estratgica permite que consumidores realizem suas compras de forma rpida e organizada, sem a necessidade de percorrer o centro da cidade um diferencial ainda mais relevante em perodos de alta demanda, quando filas e congestionamentos so frequentes. A proposta clara: chegar, comprar e retornar em menos tempo. Esse conceito se fortalece com o horrio de funcionamento. A loja a nica em Ciudad del Este que opera at as 22h de segunda a sbado e das 8h s 21h aos domingos. Durante a Crazy Week, no entanto, a operao ser ainda mais intensa: nos dias 8, 9 e 10 de maio, o espao funcionar 24 horas, acompanhando o ritmo do evento. New Zone. Divulgao. Crescimento acelerado e expanso contnua Desde sua inaugurao em novembro de 2024, a New Zone vem apresentando um crescimento expressivo. O espao comeou com 9 mil metros quadrados, cerca de 40 setores e mais de 300 marcas. Em pouco mais de um ano, j alcanou 10 mil metros quadrados, mais de 55 setores e ultrapassou 1.000 marcas disponveis. A expanso segue em ritmo acelerado: a projeo para 2027 atingir 30 mil metros quadrados, com mais de 70 setores e cerca de 1.500 espaos de marcas. O crescimento acompanhado por alianas com empresas nacionais e internacionais, reforando o posicionamento como um dos polos comerciais mais dinmicos da trplice fronteira. New Zone. Divulgao. Experincia, inovao e aes que atraem pblico Alm da estrutura, a New Zone aposta em experincias para atrair e engajar visitantes. Durante o ms de abril, por exemplo, a campanha de Pscoa Motoriza a sua famlia mobiliza clientes com a busca pelo Ovo esmeralda, distribudo em diferentes pontos da trplice fronteira. Outra iniciativa o Crazy Week da New Zone, para quem quer aproveitar ao mximo inclusive de madrugada. Nos dias 8, 9 e 10 de maio, a loja funcionar sem interrupes, com at 48 horas de portas abertas e promoes especiais durante a noite. Entre os destaques, est a volta das TVs a partir de 50 dlares, em aes pensadas para horrios de menor movimento. A proposta oferecer uma alternativa mais tranquila para quem deseja aproveitar as ofertas de Ciudad del Este, evitando o trnsito intenso e as filas tpicas do perodo. Ideal para consumidores que preferem fazer suas compras com mais conforto, tempo e sem limitaes de horrio. New Zone. Divulgao. Novo perfil de consumo na fronteira O avano da New Zone reflete uma mudana mais ampla no comportamento do consumidor. Se antes o foco estava exclusivamente no preo, hoje fatores como tempo, praticidade, conforto e experincia ganham protagonismo. A possibilidade de encontrar diferentes categorias de produtos em um nico espao, com acesso rpido e horrios flexveis, atende diretamente a esse novo perfil especialmente durante eventos de grande movimento como a Crazy Week. New Zone. Divulgao. Um novo polo comercial na regio Com crescimento contnuo, inovao e foco na experincia do cliente, a New Zone Importados contribui para reposicionar Ciudad del Este como um destino de compras mais moderno, eficiente e alinhado s demandas atuais. Mais do que acompanhar o movimento da fronteira, o espao passa a ditar o ritmo de uma nova fase do comrcio na regio.</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://g1.globo.com/pr/parana/especial-publicitario/new-zone-importados/noticia/2026/04/27/crazy-week-em-ciudad-del-este-onde-comprar-rapido-e-evitar-filas-na-fronteira.ghtml\" target=\"_blank\" rel=\"noopener\">Globo News</a></em></p>\n\n<p><strong><a href=\"https://g1.globo.com/pr/parana/especial-publicitario/new-zone-importados/noticia/2026/04/27/crazy-week-em-ciudad-del-este-onde-comprar-rapido-e-evitar-filas-na-fronteira.ghtml\" target=\"_blank\" rel=\"noopener\">Read full story on Globo News</a></strong></p>","A proximidade da Crazy Week j comea a intensificar o movimento na fronteira entre Brasil e Paraguai, em um dos perodos mais aguardados do comrcio de Ciudad del Este. Em meio ao aumento do fluxo de turistas, especialmente brasileiros, um nome ganha...","","","","1","","1","https://g1.globo.com/pr/parana/especial-publicitario/new-zone-importados/noticia/2026/04/27/crazy-week-em-ciudad-del-este-onde-comprar-rapido-e-evitar-filas-na-fronteira.ghtml","published","0","rss_import","0","0","0","0.00","0","0","0","2026-04-28 01:28:19","2026-04-28 01:14:19","2026-04-28 01:28:19","0.00","0","0","manual","text","");
INSERT INTO `news` VALUES("63","Ukraine may have to cede territory to keep EU hopes alive – Merz","ukraine-may-have-to-cede-territory-to-keep-eu-hopes-alive-merz","<p>German Chancellor Friedrich Merz has suggested that Ukraine may need to accept territorial losses to pave way for EU membership Read Full Article at RT.com</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.rt.com/news/639185-merz-territorial-concessions-ukraine-eu-bid/?utm_source=rss&utm_medium=rss&utm_campaign=RSS\" target=\"_blank\" rel=\"noopener\">RT News</a></em></p>\n\n<p><strong><a href=\"https://www.rt.com/news/639185-merz-territorial-concessions-ukraine-eu-bid/?utm_source=rss&utm_medium=rss&utm_campaign=RSS\" target=\"_blank\" rel=\"noopener\">Read full story on RT News</a></strong></p>","German Chancellor Friedrich Merz has suggested that Ukraine may need to accept territorial losses to pave way for EU membership Read Full Article at RT.com","uploads/news/rss_69efc3a682b9b.jpeg","","","2","","1","https://www.rt.com/news/639185-merz-territorial-concessions-ukraine-eu-bid/?utm_source=rss&utm_medium=rss&utm_campaign=RSS","published","0","rss_import","0","0","0","0.00","0","0","0","2026-04-28 01:28:19","2026-04-28 01:14:30","2026-04-28 01:28:19","0.00","0","0","rss","text","");
INSERT INTO `news` VALUES("64","President and First Lady Melania Trump Demand ABC Fire Jimmy Kimmel Over ‘Widow’ Joke","president-and-first-lady-melania-trump-demand-abc-fire-jimmy-kimmel-over-widow-joke","<p>The joke was recorded two days before the White House correspondents dinner, where a gunman tried to storm the press gala.</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.nytimes.com/2026/04/27/us/politics/trump-jimmy-kimmel-abc-widow-joke.html\" target=\"_blank\" rel=\"noopener\">The New York Times</a></em></p>\n\n<p><strong><a href=\"https://www.nytimes.com/2026/04/27/us/politics/trump-jimmy-kimmel-abc-widow-joke.html\" target=\"_blank\" rel=\"noopener\">Read full story on The New York Times</a></strong></p>","The joke was recorded two days before the White House correspondents dinner, where a gunman tried to storm the press gala.","uploads/news/69efc44416881.webp","","","2","","1","https://www.nytimes.com/2026/04/27/us/politics/trump-jimmy-kimmel-abc-widow-joke.html","published","1","rss_import","1","0","0","0.00","0","0","0","2026-04-28 01:28:19","2026-04-28 01:14:43","2026-04-28 01:28:19","0.00","0","0","manual","text","");
INSERT INTO `news` VALUES("65","Motorista morre após bater contra caminhão em rodovia no interior de SP","motorista-morre-após-bater-contra-caminhão-em-rodovia-no-interior-de-sp","<p>Um motorista morreu aps bater contra um caminho na Rodovia Pedro Rodrigues Garcia (SP-249), entre Ribeiro Branco e Itapeva (SP), na manh desta segunda-feira (27). De acordo com a Polcia Militar Rodoviria, o acidente aconteceu no quilmetro 48. O carro atingiu aa traseira do caminho. Participe do canal do g1 Itapetininga e Regio no WhatsApp A vtima ficou presa s ferragens e morreu antes da chegada do resgate. O motorista do caminho no ficou ferido. At a publicao desta reportagem, a causa do acidente e a identidade da vtima ainda no haviam sido confirmadas. Vdeos em alta no g1 Initial plugin text Veja mais notcias no g1 Itapetininga e Regio VDEOS: assista s reportagens da TV TEM</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://g1.globo.com/sp/itapetininga-regiao/noticia/2026/04/27/motorista-morre-apos-bater-contra-caminhao-em-rodovia-no-interior-de-sp.ghtml\" target=\"_blank\" rel=\"noopener\">Globo News</a></em></p>\n\n<p><strong><a href=\"https://g1.globo.com/sp/itapetininga-regiao/noticia/2026/04/27/motorista-morre-apos-bater-contra-caminhao-em-rodovia-no-interior-de-sp.ghtml\" target=\"_blank\" rel=\"noopener\">Read full story on Globo News</a></strong></p>","Um motorista morreu aps bater contra um caminho na Rodovia Pedro Rodrigues Garcia (SP-249), entre Ribeiro Branco e Itapeva (SP), na manh desta segunda-feira (27). De acordo com a Polcia Militar Rodoviria, o acidente aconteceu no quilmetro 48. O...","uploads/news/rss_69efc6fd8bb23.jpeg","","","1","","1","https://g1.globo.com/sp/itapetininga-regiao/noticia/2026/04/27/motorista-morre-apos-bater-contra-caminhao-em-rodovia-no-interior-de-sp.ghtml","published","0","rss_import","0","0","0","0.00","0","0","0","2026-04-27 20:20:32","2026-04-28 01:28:45","2026-04-28 01:54:10","0.00","0","0","rss","text","");
INSERT INTO `news` VALUES("66","Após candidata do TO ir ao STF para seguir em concurso, entenda por que há exigência de altura na carreira policial","após-candidata-do-to-ir-ao-stf-para-seguir-em-concurso-entenda-por-que-há-exigência-de-altura-na-carreira-policial","<p>Eentenda por que h exigncia de altura em concurso de carreira policial Jordana Alves Jardim, de 25 anos, foi eliminada do concurso da Polcia Militar do Tocantins (PMTO), mas conseguiu reverter a desclassificao atravs de uma liminar concedida pelo Supremo Tribunal Federal (STF). A candidata possui a estatura mnima de 1,55m que deve ser exigida para mulheres em concursos de segurana pblica, segundo entendimento do Supremo Tribunal Federal (STF). A Polcia Militar do Tocantins definiu a altura mnima de 1,60 m em seu edital. A deciso liminar publicada na quinta-feira (23), pelo ministro Cristiano Zanin, atendeu a um pedido da defesa da candidata, que havia sido eliminada pelo pelo critrio de altura exigido no edital. Clique aqui para seguir o canal do g1 TO no WhatsApp O advogado especialista em concurso pblico, Rafael Munhoz Fernandes, explica que o critrio de altura visa garantir capacidade fsica para atividades operacionais, no sendo necessrio, no entanto, para cargos administrativos. \"A exigncia de altura na carreira policial tradicionalmente justificada pela Administrao com base em critrios operacionais, como a padronizao fsica da tropa, o uso de equipamentos e a atuao em situaes que envolvem conteno, abordagem e risco\", disse. LEIA MAIS STF suspende eliminao de candidata por critrio de altura em concurso da PM do Tocantins Saiba quem a candidata que conseguiu deciso do STF para suspender eliminao do concurso da PMTO pelo critrio de altura Jordana Alves Jardim foi eliminada no concurso da PM por altura mnima de 1,55m Arquivo Pessoal/Jornada Alves De acordo com o especialista, quando um edital, ou at mesmo uma lei, estabelece uma altura mnima superior quelas utilizadas como referncia pelo STF possvel questionar judicialmente. \"O que esse tipo de deciso mostra que o critrio de altura no absoluto. Ele pode ser exigido, sim, mas precisa ter base legal e ser proporcional ao cargo. Quando h indcio de exagero ou falta de justificativa, o Judicirio pode intervir, ao menos de forma inicial, como ocorreu nesse caso\", explica. O especialista finaliza afirmando que exigncias muito superiores, sem justificativa concreta, aumentam significativamente a chance de uma eliminao como, nesse caso, ser considerada ilegal. \"Cada situao analisada individualmente. No toda exigncia de altura que ser considerada ilegal, nem toda eliminao que ser revertida\", afirmou Rafael. O g1 entrou em contato com a Polcia Militar do Tocantins para posicionamento sobre critrios de altura definidos e exigncia no edital publicado, mas no obteve resposta at o momento desta publicao. Candidata eliminada por altura mnima de 1,55m ganha direito de voltar ao concurso da PM Reproduo/Arquivo Pessoal de Jornada Alves Entenda o caso A candidata havia sido eliminada depois de aprovada no Teste de Aptido Fsica (TAF). A banca organizadora e a comisso do concurso entenderam que ela no cumpria o critrio de altura previsto no edital. Jordana entrou com a ao no domingo (19). Na deciso, o ministro citou o Tema 1.424 da Repercusso Geral, que estabelece que a altura mnima para mulheres em concursos da rea de segurana pblica de 1,55 metro. Como a candidata tem exatamente essa estatura, o STF considerou a eliminao invlida e desarrazoada, pois no houve justificativa de que essa altura impediria o exerccio do cargo. O STF tambm levou em conta o fato de que o concurso est em fase final, o que poderia causar prejuzo irreversvel candidata caso ela no retornasse imediatamente s prximas etapas. Com isso, determinou que Jordana possa seguir no certame, incluindo as fases de exames mdicos e odontolgicos. O advogado de Jordana, Wanderson Jos Lopes, afirmou que a eliminao desrespeitou a segurana jurdica. No se pode admitir que a Administrao Pblica elimine uma candidata plenamente apta com base em um critrio que desrespeita precedentes vinculantes do Supremo. Trata-se de um caso claro de ilegalidade\", afirmou o defensor. A defesa destacou o uso da \"Reclamao Constitucional\" no caso de Jordana, que permite levar o caso direto ao Supremo quando regras j decididas pela Corte so descumpridas. Isso evita a necessidade de passar por tribunais de instncias inferiores. Jordana j havia sido considerada apta em provas de esforo fsico. Reproduo/Arquivo pessoal de Jordana Alves Com a liminar, o STF determinou a comunicao imediata ao Governo do Tocantins e comisso do concurso. O governo tem 10 dias para prestar informaes ao tribunal, e a candidata tem cinco dias para comprovar o pagamento das custas processuais, para que o processo siga at o julgamento definitivo. Veja mais notcias da regio no g1 Tocantins.</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://g1.globo.com/to/tocantins/noticia/2026/04/27/apos-candidata-do-to-ir-ao-stf-para-seguir-em-concurso-entenda-por-que-ha-exigencia-de-altura-na-carreira-policial.ghtml\" target=\"_blank\" rel=\"noopener\">Globo News</a></em></p>\n\n<p><strong><a href=\"https://g1.globo.com/to/tocantins/noticia/2026/04/27/apos-candidata-do-to-ir-ao-stf-para-seguir-em-concurso-entenda-por-que-ha-exigencia-de-altura-na-carreira-policial.ghtml\" target=\"_blank\" rel=\"noopener\">Read full story on Globo News</a></strong></p>","Eentenda por que h exigncia de altura em concurso de carreira policial Jordana Alves Jardim, de 25 anos, foi eliminada do concurso da Polcia Militar do Tocantins (PMTO), mas conseguiu reverter a desclassificao atravs de uma liminar concedida pelo...","uploads/news/rss_69efc78da3893.jpeg","","","1","","1","https://g1.globo.com/to/tocantins/noticia/2026/04/27/apos-candidata-do-to-ir-ao-stf-para-seguir-em-concurso-entenda-por-que-ha-exigencia-de-altura-na-carreira-policial.ghtml","published","0","rss_import","1","0","0","0.00","0","0","0","2026-04-28 02:19:22","2026-04-28 01:31:09","2026-04-28 02:19:22","0.00","0","0","rss","text","");
INSERT INTO `news` VALUES("67","China seeks to block US tech giant Meta from AI acquisition","china-seeks-to-block-us-tech-giant-meta-from-ai-acquisition","<p>Bejing tightens scrutiny of AI industry amid intensifying geopolitical rivalry with the US over the technology.</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.aljazeera.com/news/2026/4/27/china-blocks-us-tech-giant-meta-from-acquiring-ai-startup-manus?traffic_source=rss\" target=\"_blank\" rel=\"noopener\">Al Jazeera</a></em></p>\n\n<p><strong><a href=\"https://www.aljazeera.com/news/2026/4/27/china-blocks-us-tech-giant-meta-from-acquiring-ai-startup-manus?traffic_source=rss\" target=\"_blank\" rel=\"noopener\">Read full story on Al Jazeera</a></strong></p>","Bejing tightens scrutiny of AI industry amid intensifying geopolitical rivalry with the US over the technology.","uploads/news/69efce2311138.webp","","","2","","1","https://www.aljazeera.com/news/2026/4/27/china-blocks-us-tech-giant-meta-from-acquiring-ai-startup-manus?traffic_source=rss","published","1","rss_import","0","0","0","0.00","0","0","0","2026-04-28 01:59:38","2026-04-28 01:54:25","2026-04-28 01:59:38","0.00","0","0","manual","text","");
INSERT INTO `news` VALUES("68","Canada won\'t be \'chasing a small deal\' to get U.S. tariff relief, Carney says","canada-won-t-be-chasing-a-small-deal-to-get-u-s-tariff-relief-carney-says","<p></p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.cbc.ca/news/politics/carney-interview-trade-tariff-relief-u-s-9.7178960?cmp=rss\" target=\"_blank\" rel=\"noopener\">CBC News</a></em></p>\n\n<p><strong><a href=\"https://www.cbc.ca/news/politics/carney-interview-trade-tariff-relief-u-s-9.7178960?cmp=rss\" target=\"_blank\" rel=\"noopener\">Read full story on CBC News</a></strong></p>","\n\nSource: CBC News\n\nRead full story on CBC News...","uploads/news/rss_69efcd0938c1a.jpeg","","","1","","1","https://www.cbc.ca/news/politics/carney-interview-trade-tariff-relief-u-s-9.7178960?cmp=rss","published","0","rss_import","0","0","0","0.00","0","0","0","2025-11-03 15:54:42","2026-04-28 01:54:33","2026-04-28 01:57:43","0.00","0","0","rss","text","");
INSERT INTO `news` VALUES("69","Russian citizens being held in Armenia on spying allegations","russian-citizens-being-held-in-armenia-on-spying-allegations","<p>The lawyer for Russians being held in Armenia over alleged espionage told RT the prosecution has not presented any evidence Read Full Article at RT.com</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.rt.com/russia/639181-armenia-russians-detained-espionage-allegations/?utm_source=rss&utm_medium=rss&utm_campaign=RSS\" target=\"_blank\" rel=\"noopener\">RT News</a></em></p>\n\n<p><strong><a href=\"https://www.rt.com/russia/639181-armenia-russians-detained-espionage-allegations/?utm_source=rss&utm_medium=rss&utm_campaign=RSS\" target=\"_blank\" rel=\"noopener\">Read full story on RT News</a></strong></p>","The lawyer for Russians being held in Armenia over alleged espionage told RT the prosecution has not presented any evidence Read Full Article at RT.com","uploads/news/rss_69efcd291748f.png","","","2","","1","https://www.rt.com/russia/639181-armenia-russians-detained-espionage-allegations/?utm_source=rss&utm_medium=rss&utm_campaign=RSS","published","0","rss_import","0","0","0","0.00","0","0","0","2026-04-27 20:52:53","2026-04-28 01:55:05","2026-04-28 01:57:43","0.00","0","0","rss","text","");
INSERT INTO `news` VALUES("72","Vai chover? Veja a previsão do tempo para esta terça (28) em Alagoas","vai-chover-veja-a-previsão-do-tempo-para-esta-terça-28-em-alagoas","<p>Chuva nublado, chuva Alagoas, chuva Macei g1 AL A Secretaria de Estado do Meio Ambiente e dos Recursos Hdricos (Semarh) emitiu, nesta segunda-feira (27), um aviso de Estado de Ateno para o Litoral de Alagoas, incluindo a Regio Metropolitana de Macei, e a Zona da Mata, devido previso de chuvas que podem ter acumulados significativos at a tera-feira (28). De acordo com a secretaria, a instabilidade provocada pela atuao de cavados nos baixos nveis da atmosfera, reas de baixa presso que favorecem a formao de nuvens e a ocorrncia de precipitaes. A principal preocupao com chuvas de intensidade moderada e persistente, que podem provocar alagamentos em reas urbanas com deficincia de drenagem, alm da elevao do nvel de pequenos crregos e riachos. Em caso de continuidade das chuvas, tambm h risco de deslizamentos de terra em reas de encosta. Apesar do cenrio, a Semarh informou que, at o momento, no h risco de elevao dos principais rios e lagoas do estado, que seguem sob monitoramento contnuo. A equipe tcnica mantm o acompanhamento das condies climticas e no descarta a emisso de novos avisos. Caso haja aumento nos volumes previstos, o nvel pode ser elevado para alerta meteorolgico. rgos pblicos se preparam para quadra chuvosa em Alagoas</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://g1.globo.com/al/alagoas/noticia/2026/04/27/vai-chover-veja-a-previsao-do-tempo-para-esta-terca-28-em-alagoas.ghtml\" target=\"_blank\" rel=\"noopener\">Globo News</a></em></p>\n\n<p><strong><a href=\"https://g1.globo.com/al/alagoas/noticia/2026/04/27/vai-chover-veja-a-previsao-do-tempo-para-esta-terca-28-em-alagoas.ghtml\" target=\"_blank\" rel=\"noopener\">Read full story on Globo News</a></strong></p>","Chuva nublado, chuva Alagoas, chuva Macei g1 AL A Secretaria de Estado do Meio Ambiente e dos Recursos Hdricos (Semarh) emitiu, nesta segunda-feira (27), um aviso de Estado de Ateno para o Litoral de Alagoas, incluindo a Regio Metropolitana de...","uploads/news/rss_69efcf7817ebb.jpeg","","","1","","1","https://g1.globo.com/al/alagoas/noticia/2026/04/27/vai-chover-veja-a-previsao-do-tempo-para-esta-terca-28-em-alagoas.ghtml","published","0","rss_import","1","0","0","0.00","0","0","0","2026-04-27 21:04:20","2026-04-28 02:04:56","2026-04-28 02:09:35","0.00","0","0","rss","text","");
INSERT INTO `news` VALUES("73","Who holds the cards in Iran-US talks?","who-holds-the-cards-in-iran-us-talks","<p>Both the US and Iran claim to have the upper hand in negotiations over the war, but who holds the cards?</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.aljazeera.com/video/newsfeed/2026/4/27/who-holds-the-cards-in-iran-us-talks?traffic_source=rss\" target=\"_blank\" rel=\"noopener\">Al Jazeera</a></em></p>\n\n<p><strong><a href=\"https://www.aljazeera.com/video/newsfeed/2026/4/27/who-holds-the-cards-in-iran-us-talks?traffic_source=rss\" target=\"_blank\" rel=\"noopener\">Read full story on Al Jazeera</a></strong></p>","Both the US and Iran claim to have the upper hand in negotiations over the war, but who holds the cards?","uploads/news/69efd27355779.png","","","2","","1","https://www.aljazeera.com/video/newsfeed/2026/4/27/who-holds-the-cards-in-iran-us-talks?traffic_source=rss","published","1","rss_import","0","0","0","0.00","0","0","0","2026-04-28 02:20:24","2026-04-28 02:10:28","2026-04-28 02:20:24","0.00","0","0","manual","text","");
INSERT INTO `news` VALUES("74","Carteira de pedidos da Embraer chega a US$ 32,1 bilhões e bate recorde pela 6ª vez seguida","carteira-de-pedidos-da-embraer-chega-a-us-32-1-bilhões-e-bate-recorde-pela-6ª-vez-seguida","<p>Embraer Divulgao/Embraer A Embraer, empresa aeronutica com sede em So Jos dos Campos, no interior de So Paulo, fechou o primeiro trimestre de 2026 batendo mais um recorde: a carteira de pedidos da empresa atingiu US 32,1 bilhes. O balano foi divulgado nesta segunda-feira (27). Na cotao atual, o valor equivale a pouco mais de R 159,9 bilhes. No fim do ano passado, a empresa tambm havia batido recorde. Clique aqui para seguir o canal do g1 Vale do Paraba e regio no WhatsApp Ao todo, a carteira de pedidos consolidada da Embraer cresceu 21,6 na comparao anual no primeiro trimestre deste ano. Segundo a empresa, a aviao comercial registrou uma carteira de pedidos de US 15 bilhes, representando alta de 50 na comparao anual. Vdeos em alta no g1 Confira abaixo o balano por segmento: Aviao comercial: US15 bilhes Aviao executiva: US7,6 bilhes Servios e Suporte: US5,1 bilhes Defesa e Segurana: US4,4 bilhes Segundo o balano divulgado pela empresa nesta segunda-feira, a aviao executiva entregou 29 aeronaves no primeiro trimestre deste ano, sendo 15 jatos Phenom 300, nove jatos Praetor 500, quatro Praetor 600, alm de um Phenom 100. Jato executivo Phenom da Embraer. Divulgao/Embraer J a aviao comercial fez 10 entregas, sendo 6 avies E175, alm de trs aeronaves modelo E195-E2 e uma aeronave E190-E2. A Defesa Segurana entregou cinco aeronaves, sendo quatro A-29 Super Tucano e um KC-390 Millennium. Veja mais notcias do Vale do Paraba e regio bragantina</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://g1.globo.com/sp/vale-do-paraiba-regiao/noticia/2026/04/27/carteira-de-pedidos-da-embraer-chega-a-us-321-bilhoes-e-bate-recorde-pela-6a-vez-seguida.ghtml\" target=\"_blank\" rel=\"noopener\">Globo News</a></em></p>\n\n<p><strong><a href=\"https://g1.globo.com/sp/vale-do-paraiba-regiao/noticia/2026/04/27/carteira-de-pedidos-da-embraer-chega-a-us-321-bilhoes-e-bate-recorde-pela-6a-vez-seguida.ghtml\" target=\"_blank\" rel=\"noopener\">Read full story on Globo News</a></strong></p>","Embraer Divulgao/Embraer A Embraer, empresa aeronutica com sede em So Jos dos Campos, no interior de So Paulo, fechou o primeiro trimestre de 2026 batendo mais um recorde: a carteira de pedidos da empresa atingiu US 32,1 bilhes. O balano foi...","uploads/news/rss_69efd0e4d0547.jpeg","","","1","","1","https://g1.globo.com/sp/vale-do-paraiba-regiao/noticia/2026/04/27/carteira-de-pedidos-da-embraer-chega-a-us-321-bilhoes-e-bate-recorde-pela-6a-vez-seguida.ghtml","published","0","rss_import","1","0","0","0.00","0","0","0","2026-04-27 21:08:14","2026-04-28 02:11:00","2026-04-28 02:22:08","0.00","0","0","rss","text","");
INSERT INTO `news` VALUES("75","Mali military leader Goita emerges as Russia declares it halted coup","mali-military-leader-goita-emerges-as-russia-declares-it-halted-coup","<p>First sighting of Goita since rebel attacks comes as Russia seeks to dampen speculation over ally\'s military government.</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.aljazeera.com/news/2026/4/28/malis-military-leader-goita-emerges-as-russia-declares-it-halted-coup?traffic_source=rss\" target=\"_blank\" rel=\"noopener\">Al Jazeera</a></em></p>\n\n<p><strong><a href=\"https://www.aljazeera.com/news/2026/4/28/malis-military-leader-goita-emerges-as-russia-declares-it-halted-coup?traffic_source=rss\" target=\"_blank\" rel=\"noopener\">Read full story on Al Jazeera</a></strong></p>","First sighting of Goita since rebel attacks comes as Russia seeks to dampen speculation over ally\'s military government.","","","","1","","1","https://www.aljazeera.com/news/2026/4/28/malis-military-leader-goita-emerges-as-russia-declares-it-halted-coup?traffic_source=rss","draft","0","rss_import","0","0","0","0.00","0","0","0","2026-04-28 18:48:24","2026-04-29 00:02:55","2026-04-29 00:02:55","0.00","0","0","manual","text","");
INSERT INTO `news` VALUES("76","I made \'serious mistake\' advising Starmer to appoint Mandelson, PM\'s ex-top adviser McSweeney says","i-made-serious-mistake-advising-starmer-to-appoint-mandelson-pm-s-ex-top-adviser-mcsweeney-says","<p>Morgan McSweeney says the peer did not give the \"full truth\" about his relationship with Epstein.</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.bbc.com/news/articles/czx26yz7kxzo?at_medium=RSS&at_campaign=rss\" target=\"_blank\" rel=\"noopener\">BBC News</a></em></p>\n\n<p><strong><a href=\"https://www.bbc.com/news/articles/czx26yz7kxzo?at_medium=RSS&at_campaign=rss\" target=\"_blank\" rel=\"noopener\">Read full story on BBC News</a></strong></p>","Morgan McSweeney says the peer did not give the \"full truth\" about his relationship with Epstein.","","","","1","","1","https://www.bbc.com/news/articles/czx26yz7kxzo?at_medium=RSS&at_campaign=rss","draft","0","rss_import","0","0","0","0.00","0","0","0","2026-04-28 17:33:44","2026-04-29 00:02:58","2026-04-29 00:02:58","0.00","0","0","manual","text","");
INSERT INTO `news` VALUES("77","United Arab Emirates to quit oil cartel Opec","united-arab-emirates-to-quit-oil-cartel-opec","<p>The UAE\'s decision, after nearly 60 years of membership, is seen as a potential death knell for the oil cartel.</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.bbc.com/news/articles/cj4pxwlr52yo?at_medium=RSS&at_campaign=rss\" target=\"_blank\" rel=\"noopener\">BBC World News</a></em></p>\n\n<p><strong><a href=\"https://www.bbc.com/news/articles/cj4pxwlr52yo?at_medium=RSS&at_campaign=rss\" target=\"_blank\" rel=\"noopener\">Read full story on BBC World News</a></strong></p>","The UAE\'s decision, after nearly 60 years of membership, is seen as a potential death knell for the oil cartel.","","","","1","","1","https://www.bbc.com/news/articles/cj4pxwlr52yo?at_medium=RSS&at_campaign=rss","draft","0","rss_import","0","0","0","0.00","0","0","0","2026-04-28 16:24:45","2026-04-29 00:03:08","2026-04-29 00:03:08","0.00","0","0","manual","text","");
INSERT INTO `news` VALUES("78","King Charles to make rare speech to U.S. Congress as he visits Washington","king-charles-to-make-rare-speech-to-u-s-congress-as-he-visits-washington","<p></p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.cbc.ca/news/world/livestory/king-charles-us-state-visit-9.7178240?cmp=rss\" target=\"_blank\" rel=\"noopener\">CBC News</a></em></p>\n\n<p><strong><a href=\"https://www.cbc.ca/news/world/livestory/king-charles-us-state-visit-9.7178240?cmp=rss\" target=\"_blank\" rel=\"noopener\">Read full story on CBC News</a></strong></p>","\n\nSource: CBC News\n\nRead full story on CBC News...","uploads/news/rss_69f1047752b2e.jpeg","","","1","","1","https://www.cbc.ca/news/world/livestory/king-charles-us-state-visit-9.7178240?cmp=rss","published","0","rss_import","0","0","0","0.00","0","0","0","2025-06-15 04:01:00","2026-04-29 00:03:19","2026-04-29 00:27:38","0.00","0","0","rss","text","");
INSERT INTO `news` VALUES("79","UAE to leave Opec in blow to oil cartel","uae-to-leave-opec-in-blow-to-oil-cartel","<p>Move underlines long-running frustrations with group over production quotas</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.ft.com/content/8c354f2d-3e66-47f1-aad4-9b4aa30e386d\" target=\"_blank\" rel=\"noopener\">Financial Times</a></em></p>\n\n<p><strong><a href=\"https://www.ft.com/content/8c354f2d-3e66-47f1-aad4-9b4aa30e386d\" target=\"_blank\" rel=\"noopener\">Read full story on Financial Times</a></strong></p>","Move underlines long-running frustrations with group over production quotas","uploads/news/69f10b26150bc.webp","","","2","","1","https://www.ft.com/content/8c354f2d-3e66-47f1-aad4-9b4aa30e386d","published","1","rss_import","0","0","0","0.00","0","0","0","2026-04-29 00:32:01","2026-04-29 00:03:39","2026-04-29 00:32:01","0.00","0","0","manual","text","");
INSERT INTO `news` VALUES("81","Putin warns of environmental impact of Ukrainian attacks on Russian refinery","putin-warns-of-environmental-impact-of-ukrainian-attacks-on-russian-refinery","<p>Read Full Article at RT.com</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.rt.com/russia/639230-putin-oil-refinery-attack-ukraine/?utm_source=rss&utm_medium=rss&utm_campaign=RSS\" target=\"_blank\" rel=\"noopener\">RT News</a></em></p>\n\n<p><strong><a href=\"https://www.rt.com/russia/639230-putin-oil-refinery-attack-ukraine/?utm_source=rss&utm_medium=rss&utm_campaign=RSS\" target=\"_blank\" rel=\"noopener\">Read full story on RT News</a></strong></p>","Read Full Article at RT.com","uploads/news/rss_69f104b92d9dd.jpeg","","","2","","1","https://www.rt.com/russia/639230-putin-oil-refinery-attack-ukraine/?utm_source=rss&utm_medium=rss&utm_campaign=RSS","published","0","rss_import","0","0","0","0.00","0","0","0","2026-04-28 18:17:02","2026-04-29 00:04:25","2026-04-29 00:28:31","0.00","0","0","rss","text","");
INSERT INTO `news` VALUES("82","Russia claims its Africa Corps group prevented coup in Mali after rebels seize towns","russia-claims-its-africa-corps-group-prevented-coup-in-mali-after-rebels-seize-towns","<p>Kremlin-controlled paramilitaries also alleged it inflicted irreplaceable losses on insurgents avoiding civilian casualtiesRussias defence ministry has claimed its Africa Corps the successor to the former Wagner mercenary group prevented a coup in Mali over the weekend, avoiding mass civilian casualties and inflicting irreplaceable losses on rebel insurgents.It said in a statement that its troops in the desert town of Kidal near the Algerian border had fought for more than 24 hours while completely surrounded and vastly outnumbered. It also alleged, without providing evidence, that the militants had been trained by European mercenary instructors, including Ukrainians. The casualty toll was not specified.</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.theguardian.com/world/2026/apr/28/russia-claims-its-africa-corps-group-prevented-coup-in-mali-after-rebels-seize-towns\" target=\"_blank\" rel=\"noopener\">The Guardian</a></em></p>\n\n<p><strong><a href=\"https://www.theguardian.com/world/2026/apr/28/russia-claims-its-africa-corps-group-prevented-coup-in-mali-after-rebels-seize-towns\" target=\"_blank\" rel=\"noopener\">Read full story on The Guardian</a></strong></p>","Kremlin-controlled paramilitaries also alleged it inflicted irreplaceable losses on insurgents avoiding civilian casualtiesRussias defence ministry has claimed its Africa Corps the successor to the former Wagner mercenary group prevented a coup in...","uploads/news/69f10ab24241c.webp","","","2","","1","https://www.theguardian.com/world/2026/apr/28/russia-claims-its-africa-corps-group-prevented-coup-in-mali-after-rebels-seize-towns","published","1","rss_import","0","0","0","0.00","0","0","0","2026-04-29 00:32:01","2026-04-29 00:04:38","2026-04-29 00:32:01","0.00","0","0","manual","text","");
INSERT INTO `news` VALUES("83","Families forced into displacement by famine in Sudan","families-forced-into-displacement-by-famine-in-sudan","<p>Families in Sudan face hunger and displacement during war with millions dependent on limited and inconsistent aid.</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.aljazeera.com/news/2026/4/28/families-forced-into-displacement-by-famine-in-sudan?traffic_source=rss\" target=\"_blank\" rel=\"noopener\">Al Jazeera</a></em></p>\n\n<p><strong><a href=\"https://www.aljazeera.com/news/2026/4/28/families-forced-into-displacement-by-famine-in-sudan?traffic_source=rss\" target=\"_blank\" rel=\"noopener\">Read full story on Al Jazeera</a></strong></p>","Families in Sudan face hunger and displacement during war with millions dependent on limited and inconsistent aid.","","","","1","","1","https://www.aljazeera.com/news/2026/4/28/families-forced-into-displacement-by-famine-in-sudan?traffic_source=rss","draft","0","rss_import","0","0","0","0.00","0","0","0","2026-04-28 19:14:47","2026-04-29 00:32:13","2026-04-29 00:32:13","0.00","0","0","manual","text","");
INSERT INTO `news` VALUES("84","PM won\'t face inquiry over claims he misled MPs on Mandelson vetting","pm-won-t-face-inquiry-over-claims-he-misled-mps-on-mandelson-vetting","<p>A Conservative-led motion sought to have the prime minister\'s remarks assessed by Privileges Committee.</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.bbc.com/news/articles/cx21lx9ne83o?at_medium=RSS&at_campaign=rss\" target=\"_blank\" rel=\"noopener\">BBC News</a></em></p>\n\n<p><strong><a href=\"https://www.bbc.com/news/articles/cx21lx9ne83o?at_medium=RSS&at_campaign=rss\" target=\"_blank\" rel=\"noopener\">Read full story on BBC News</a></strong></p>","A Conservative-led motion sought to have the prime minister\'s remarks assessed by Privileges Committee.","","","","1","","1","https://www.bbc.com/news/articles/cx21lx9ne83o?at_medium=RSS&at_campaign=rss","draft","0","rss_import","0","0","0","0.00","0","0","0","2026-04-28 18:48:48","2026-04-29 00:32:16","2026-04-29 00:32:16","0.00","0","0","manual","text","");
INSERT INTO `news` VALUES("85","King Charles calls for unity \'in times of great uncertainty\' in speech to U.S. Congress","king-charles-calls-for-unity-in-times-of-great-uncertainty-in-speech-to-u-s-congress","<p></p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.cbc.ca/news/world/livestory/king-charles-us-state-visit-9.7178240?cmp=rss\" target=\"_blank\" rel=\"noopener\">CBC News</a></em></p>\n\n<p><strong><a href=\"https://www.cbc.ca/news/world/livestory/king-charles-us-state-visit-9.7178240?cmp=rss\" target=\"_blank\" rel=\"noopener\">Read full story on CBC News</a></strong></p>","\n\nSource: CBC News\n\nRead full story on CBC News...","uploads/news/rss_69f10b45b55dc.jpeg","","","1","","1","https://www.cbc.ca/news/world/livestory/king-charles-us-state-visit-9.7178240?cmp=rss","published","0","rss_import","0","0","0","0.00","0","0","0","2025-06-15 04:01:00","2026-04-29 00:32:21","2026-04-29 00:40:55","0.00","0","0","rss","text","");
INSERT INTO `news` VALUES("86","Jamie Dimon warns of \'some kind of bond crisis\' ahead as global debt risks build","jamie-dimon-warns-of-some-kind-of-bond-crisis-ahead-as-global-debt-risks-build","<p>Dimon, who runs JPMorgan Chase, the world\'s largest bank by market cap, said that today\'s growing mix of risks could combine in unpredictable ways.</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.cnbc.com/2026/04/28/jamie-dimon-bond-crisis-global-debt-risks.html\" target=\"_blank\" rel=\"noopener\">CNBC</a></em></p>\n\n<p><strong><a href=\"https://www.cnbc.com/2026/04/28/jamie-dimon-bond-crisis-global-debt-risks.html\" target=\"_blank\" rel=\"noopener\">Read full story on CNBC</a></strong></p>","Dimon, who runs JPMorgan Chase, the world\'s largest bank by market cap, said that today\'s growing mix of risks could combine in unpredictable ways.","","","","1","","1","https://www.cnbc.com/2026/04/28/jamie-dimon-bond-crisis-global-debt-risks.html","draft","0","rss_import","0","0","0","0.00","0","0","0","2026-04-28 18:44:41","2026-04-29 00:32:28","2026-04-29 00:32:28","0.00","0","0","manual","text","");
INSERT INTO `news` VALUES("87","As trial against OpenAI begins, Elon Musk seeks Sam Altman\'s ouster","as-trial-against-openai-begins-elon-musk-seeks-sam-altman-s-ouster","<p>If Musk gets what he\'s asking for, it would radically re-shape one of the world\'s leading AI companies.</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.npr.org/2026/04/28/nx-s1-5801438/musk-altman-openai-trial-opening-statements\" target=\"_blank\" rel=\"noopener\">NPR News</a></em></p>\n\n<p><strong><a href=\"https://www.npr.org/2026/04/28/nx-s1-5801438/musk-altman-openai-trial-opening-statements\" target=\"_blank\" rel=\"noopener\">Read full story on NPR News</a></strong></p>","If Musk gets what he\'s asking for, it would radically re-shape one of the world\'s leading AI companies.","","","","1","","1","https://www.npr.org/2026/04/28/nx-s1-5801438/musk-altman-openai-trial-opening-statements","draft","0","rss_import","0","0","0","0.00","0","0","0","2026-04-28 14:42:06","2026-04-29 00:32:48","2026-04-29 00:32:48","0.00","0","0","manual","text","");
INSERT INTO `news` VALUES("88","Putin comments on Ukrainian strikes on Russian Black Sea oil refinery","putin-comments-on-ukrainian-strikes-on-russian-black-sea-oil-refinery","<p>Ukraine has ramped up attacks on Russias civilian infrastructure, President Vladimir Putin has said Read Full Article at RT.com</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.rt.com/russia/639230-putin-oil-refinery-attack-ukraine/?utm_source=rss&utm_medium=rss&utm_campaign=RSS\" target=\"_blank\" rel=\"noopener\">RT News</a></em></p>\n\n<p><strong><a href=\"https://www.rt.com/russia/639230-putin-oil-refinery-attack-ukraine/?utm_source=rss&utm_medium=rss&utm_campaign=RSS\" target=\"_blank\" rel=\"noopener\">Read full story on RT News</a></strong></p>","Ukraine has ramped up attacks on Russias civilian infrastructure, President Vladimir Putin has said Read Full Article at RT.com","uploads/news/rss_69f10b6947c32.jpeg","","","2","","1","https://www.rt.com/russia/639230-putin-oil-refinery-attack-ukraine/?utm_source=rss&utm_medium=rss&utm_campaign=RSS","published","0","rss_import","0","0","0","0.00","0","0","0","2026-04-28 18:17:02","2026-04-29 00:32:57","2026-04-29 00:41:16","0.00","0","0","rss","text","");
INSERT INTO `news` VALUES("89","One Person Who Appears to Be Missing From King Charles’s U.S. Itinerary: Prince Harry","one-person-who-appears-to-be-missing-from-king-charles-s-u-s-itinerary-prince-harry","<p>On a state visit designed in part to repair U.S.-British relations, King Charless schedule does not include plans to see his younger son, who lives in the United States with his family.</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.nytimes.com/2026/04/28/us/prince-harry-king-charles-us-visit.html\" target=\"_blank\" rel=\"noopener\">The New York Times</a></em></p>\n\n<p><strong><a href=\"https://www.nytimes.com/2026/04/28/us/prince-harry-king-charles-us-visit.html\" target=\"_blank\" rel=\"noopener\">Read full story on The New York Times</a></strong></p>","On a state visit designed in part to repair U.S.-British relations, King Charless schedule does not include plans to see his younger son, who lives in the United States with his family.","uploads/news/69f10f5ad9509.webp","","","2","","1","https://www.nytimes.com/2026/04/28/us/prince-harry-king-charles-us-visit.html","published","0","rss_import","0","0","0","0.00","0","0","0","2026-04-29 00:49:58","2026-04-29 00:33:12","2026-04-29 00:49:58","0.00","0","0","manual","text","");
INSERT INTO `news` VALUES("90","Corpo de homem é encontrado com marcas de tiros no litoral do Piauí","corpo-de-homem-é-encontrado-com-marcas-de-tiros-no-litoral-do-piauí","<p>IML de Teresina, Piau Andr Nascimento/ g1 Um homem, identificado como Carlos Alberto Brito dos Santos, foi encontrado morto com marcas de tiro, na manh desta tera-feira (28), em uma estrada vicinal na regio dos Tabuleiros Litorneos, entre o assentamento Cajueiro e a localidade Rebento, em Parnaba. De acordo com a Polcia Militar do Piau, o corpo apresentava duas perfuraes provocadas por disparos de arma de fogo, que atingiram a cabea e a perna da vtima. Siga o canal do g1 Piau no WhatsApp Ainda segundo a polcia, uma equipe da Fora Ttica foi acionada por volta das 8h15, aps uma denncia do encontro de um cadver na regio. Ao chegar ao local, os policiais isolaram a rea. Veja os vdeos que esto em alta no g1 Equipes do Instituto Mdico Legal (IML) e da Polcia Civil estiveram no local para realizar a percia inicial. Aps os procedimentos, o corpo foi removido. O caso ser investigado pela Polcia Civil, que ir apurar a autoria e a motivao do crime. Yngridy Vieira, estagiria sob superviso de Ilanna Serena. VDEOS: assista aos vdeos mais vistos da Rede Clube</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://g1.globo.com/pi/piaui/noticia/2026/04/28/homem-encontrado-morto.ghtml\" target=\"_blank\" rel=\"noopener\">Globo News</a></em></p>\n\n<p><strong><a href=\"https://g1.globo.com/pi/piaui/noticia/2026/04/28/homem-encontrado-morto.ghtml\" target=\"_blank\" rel=\"noopener\">Read full story on Globo News</a></strong></p>","IML de Teresina, Piau Andr Nascimento/ g1 Um homem, identificado como Carlos Alberto Brito dos Santos, foi encontrado morto com marcas de tiro, na manh desta tera-feira (28), em uma estrada vicinal na regio dos Tabuleiros Litorneos, entre o...","uploads/news/rss_69f10baa2e196.jpeg","","","1","","1","https://g1.globo.com/pi/piaui/noticia/2026/04/28/homem-encontrado-morto.ghtml","published","0","rss_import","0","0","0","0.00","0","0","0","2026-04-28 19:32:17","2026-04-29 00:34:02","2026-04-29 00:41:03","0.00","0","0","rss","text","");


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



CREATE TABLE `news_credibility_analysis` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `news_id` int(11) NOT NULL COMMENT 'Reference to news article',
  `analysis_date` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'When the analysis was performed',
  `credibility_score` decimal(5,2) DEFAULT 50.00 COMMENT 'Overall credibility score (0-100)',
  `confidence_level` decimal(3,2) DEFAULT 0.50 COMMENT 'AI confidence in the analysis (0.00-1.00)',
  `title_credibility` decimal(5,2) DEFAULT 50.00 COMMENT 'Title credibility score (0-100)',
  `content_credibility` decimal(5,2) DEFAULT 50.00 COMMENT 'Content credibility score (0-100)',
  `source_credibility` decimal(5,2) DEFAULT 50.00 COMMENT 'Source credibility score (0-100)',
  `factual_accuracy` decimal(5,2) DEFAULT 50.00 COMMENT 'Factual accuracy score (0-100)',
  `sensationalism_score` decimal(5,2) DEFAULT 0.00 COMMENT 'Sensationalism score (0-100)',
  `emotional_manipulation` decimal(5,2) DEFAULT 0.00 COMMENT 'Emotional manipulation score (0-100)',
  `clickbait_score` decimal(5,2) DEFAULT 0.00 COMMENT 'Clickbait score (0-100)',
  `propaganda_indicators` decimal(5,2) DEFAULT 0.00 COMMENT 'Propaganda indicators score (0-100)',
  `grammar_score` decimal(5,2) DEFAULT 50.00 COMMENT 'Grammar quality score (0-100)',
  `readability_score` decimal(5,2) DEFAULT 50.00 COMMENT 'Readability score (0-100)',
  `factual_density` decimal(5,2) DEFAULT 50.00 COMMENT 'Factual density score (0-100)',
  `source_verified` tinyint(1) DEFAULT 0 COMMENT 'Whether source is verified',
  `source_reputation_score` decimal(5,2) DEFAULT 50.00 COMMENT 'Source reputation score (0-100)',
  `cross_reference_count` int(11) DEFAULT 0 COMMENT 'Number of cross-references found',
  `analysis_method` varchar(50) DEFAULT 'AI_MULTIMODEL' COMMENT 'Method used for analysis',
  `processing_time_ms` int(11) DEFAULT 0 COMMENT 'Time taken for analysis in milliseconds',
  `ai_model_version` varchar(20) DEFAULT 'v2.1' COMMENT 'Version of AI model',
  `risk_level` enum('low','medium','high','critical') DEFAULT 'medium' COMMENT 'Risk level for misinformation',
  `content_category` varchar(50) DEFAULT 'general' COMMENT 'Content category',
  `requires_review` tinyint(1) DEFAULT 0 COMMENT 'Whether manual review is required',
  `auto_flagged` tinyint(1) DEFAULT 0 COMMENT 'Whether automatically flagged as suspicious',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Record creation timestamp',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_news_analysis` (`news_id`),
  KEY `idx_credibility_score` (`credibility_score`),
  KEY `idx_risk_level` (`risk_level`),
  KEY `idx_auto_flagged` (`auto_flagged`),
  KEY `idx_analysis_date` (`analysis_date`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `news_credibility_analysis` VALUES("1","46","2026-04-25 22:59:13","58.25","9.99","95.00","65.00","30.00","75.00","0.00","0.00","0.00","0.00","80.00","50.00","0.00","0","0.00","0","AI_MULTIMODEL","19","0","high","UNVERIFIED","1","0","2026-04-25 22:59:13");
INSERT INTO `news_credibility_analysis` VALUES("2","31","2026-04-26 00:31:38","72.65","9.99","100.00","85.00","60.00","83.00","0.00","0.00","0.00","0.00","75.00","34.74","27.14","0","50.00","4","AI_MULTIMODEL","4","0","medium","LIKELY_TRUE","0","0","2026-04-26 00:31:38");
INSERT INTO `news_credibility_analysis` VALUES("3","47","2026-04-26 22:30:00","62.72","9.99","100.00","65.00","30.00","75.00","0.00","0.00","0.00","0.00","95.00","50.00","22.22","0","0.00","0","AI_MULTIMODEL","27","0","medium","UNVERIFIED","1","0","2026-04-26 22:30:00");
INSERT INTO `news_credibility_analysis` VALUES("4","30","2026-04-27 12:52:17","72.20","9.99","100.00","90.00","60.00","83.00","0.00","0.00","0.00","8.00","75.00","68.04","13.49","0","50.00","6","AI_MULTIMODEL","26","0","medium","LIKELY_TRUE","0","0","2026-04-27 12:52:17");
INSERT INTO `news_credibility_analysis` VALUES("5","54","2026-04-28 01:02:35","72.88","9.99","100.00","85.00","60.00","83.00","0.00","0.00","0.00","0.00","75.00","67.20","13.19","0","50.00","2","AI_MULTIMODEL","7","0","medium","LIKELY_TRUE","0","0","2026-04-28 01:02:35");
INSERT INTO `news_credibility_analysis` VALUES("6","61","2026-04-28 01:11:35","70.02","9.99","100.00","85.00","60.00","83.00","0.00","0.00","0.00","0.00","75.00","0.00","18.19","0","50.00","2","AI_MULTIMODEL","10","0","medium","LIKELY_TRUE","0","0","2026-04-28 01:11:35");
INSERT INTO `news_credibility_analysis` VALUES("7","64","2026-04-28 01:17:25","74.19","9.99","100.00","85.00","60.00","83.00","0.00","0.00","0.00","0.00","75.00","64.46","27.63","0","50.00","2","AI_MULTIMODEL","6","0","medium","LIKELY_TRUE","0","0","2026-04-28 01:17:25");
INSERT INTO `news_credibility_analysis` VALUES("8","50","2026-04-28 01:20:47","73.05","9.99","100.00","85.00","60.00","83.00","0.00","0.00","0.00","0.00","75.00","58.22","19.40","0","50.00","2","AI_MULTIMODEL","13","0","medium","LIKELY_TRUE","0","0","2026-04-28 01:20:47");
INSERT INTO `news_credibility_analysis` VALUES("9","51","2026-04-28 02:00:17","73.37","9.99","100.00","85.00","60.00","83.00","0.00","0.00","0.00","0.00","75.00","69.66","16.90","0","50.00","2","AI_MULTIMODEL","8","0","medium","LIKELY_TRUE","0","0","2026-04-28 02:00:17");
INSERT INTO `news_credibility_analysis` VALUES("10","48","2026-04-28 02:01:01","72.13","9.99","100.00","85.00","60.00","83.00","0.00","0.00","0.00","0.00","75.00","29.37","24.66","0","50.00","4","AI_MULTIMODEL","9","0","medium","LIKELY_TRUE","0","0","2026-04-28 02:01:01");
INSERT INTO `news_credibility_analysis` VALUES("11","72","2026-04-28 02:09:35","70.57","9.99","100.00","85.00","60.00","83.00","0.00","0.00","0.00","0.00","75.00","14.15","16.60","0","50.00","2","AI_MULTIMODEL","8","0","medium","LIKELY_TRUE","0","0","2026-04-28 02:09:35");
INSERT INTO `news_credibility_analysis` VALUES("12","66","2026-04-28 02:18:41","69.04","9.99","100.00","85.00","60.00","83.00","0.00","0.00","0.00","0.00","69.00","0.27","14.29","0","50.00","3","AI_MULTIMODEL","3","0","medium","UNVERIFIED","1","0","2026-04-28 02:18:41");
INSERT INTO `news_credibility_analysis` VALUES("13","74","2026-04-28 02:22:08","71.82","9.99","100.00","85.00","60.00","83.00","0.00","0.00","0.00","0.00","75.00","14.67","28.82","0","50.00","2","AI_MULTIMODEL","2","0","medium","LIKELY_TRUE","0","0","2026-04-28 02:22:08");


CREATE TABLE `news_editions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `edition_date` date NOT NULL,
  `status` enum('draft','published','archived') DEFAULT 'draft',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_edition_date` (`edition_date`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



CREATE TABLE `news_likes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `news_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_news_user` (`news_id`,`user_id`),
  KEY `idx_news_ip` (`news_id`,`ip_address`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



CREATE TABLE `news_shares` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `news_id` int(11) NOT NULL,
  `platform` varchar(50) NOT NULL DEFAULT 'unknown',
  `user_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_news_platform` (`news_id`,`platform`),
  KEY `idx_news_user` (`news_id`,`user_id`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



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
  `is_active` tinyint(1) DEFAULT 1,
  `priority` int(11) DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_type` (`type`),
  KEY `idx_last_scraped` (`last_scraped`)
) ENGINE=InnoDB AUTO_INCREMENT=201 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `news_sources` VALUES("158","BBC News Pakistan","","http://feeds.bbci.co.uk/news/world/south_asia/rss.xml","rss","1","60","active","","2026-04-23 23:15:35","2026-04-23 23:15:35","1","10");
INSERT INTO `news_sources` VALUES("159","Dawn News","","https://www.dawn.com/feed/rss/pakistan","rss","1","60","active","","2026-04-23 23:15:35","2026-04-23 23:15:35","1","9");
INSERT INTO `news_sources` VALUES("160","Geo News","","https://www.geo.tv/rss/pakistan","rss","1","60","active","","2026-04-23 23:15:35","2026-04-23 23:15:35","1","8");
INSERT INTO `news_sources` VALUES("161","ARY News","","https://arynews.tv/en/feed/","rss","1","60","active","","2026-04-23 23:15:35","2026-04-23 23:15:35","1","7");
INSERT INTO `news_sources` VALUES("162","CNN World","","http://rss.cnn.com/rss/edition_world.rss","rss","1","60","active","2026-04-29 00:33:29","2026-04-23 23:15:35","2026-04-29 00:33:29","1","6");
INSERT INTO `news_sources` VALUES("163","Reuters World","","https://www.reuters.com/world/rss.xml","rss","1","60","active","","2026-04-23 23:15:35","2026-04-23 23:15:35","1","5");
INSERT INTO `news_sources` VALUES("164","BBC News","https://www.bbc.com/news","https://feeds.bbci.co.uk/news/rss.xml","rss","1","30","active","2026-04-29 00:33:20","2026-04-24 01:28:55","2026-04-29 00:33:20","1","1");
INSERT INTO `news_sources` VALUES("165","BBC World News","https://www.bbc.com/news/world","https://feeds.bbci.co.uk/news/world/rss.xml","rss","1","30","active","2026-04-29 00:33:23","2026-04-24 01:28:55","2026-04-29 00:33:23","1","1");
INSERT INTO `news_sources` VALUES("166","CNN News","https://www.cnn.com","https://rss.cnn.com/rss/edition.rss","rss","1","30","active","","2026-04-24 01:28:55","2026-04-24 01:28:55","1","1");
INSERT INTO `news_sources` VALUES("167","Reuters News","https://www.reuters.com","https://feeds.reuters.com/reuters/topNews","rss","1","30","active","","2026-04-24 01:28:55","2026-04-24 01:28:55","1","1");
INSERT INTO `news_sources` VALUES("168","Al Jazeera","https://www.aljazeera.com","https://www.aljazeera.com/xml/rss/all.xml","rss","1","30","active","2026-04-29 00:33:18","2026-04-24 01:28:55","2026-04-29 00:33:18","1","1");
INSERT INTO `news_sources` VALUES("169","Associated Press","https://apnews.com","https://feeds.apnews.com/rss/apf-topnews","rss","1","30","active","","2026-04-24 01:28:55","2026-04-24 01:28:55","1","1");
INSERT INTO `news_sources` VALUES("170","Fox News","https://www.foxnews.com","https://www.foxnews.com/about/rss/feedburner/foxnews/latest","rss","1","30","active","","2026-04-24 01:28:55","2026-04-24 01:28:55","1","1");
INSERT INTO `news_sources` VALUES("171","The Guardian","https://www.theguardian.com","https://www.theguardian.com/world/rss","rss","1","30","active","2026-04-29 00:34:32","2026-04-24 01:28:55","2026-04-29 00:34:32","1","1");
INSERT INTO `news_sources` VALUES("172","The New York Times","https://www.nytimes.com","https://rss.nytimes.com/services/xml/rss/nyt/HomePage.xml","rss","1","30","active","2026-04-29 00:34:32","2026-04-24 01:28:55","2026-04-29 00:34:32","1","1");
INSERT INTO `news_sources` VALUES("173","Washington Post","https://www.washingtonpost.com","https://www.washingtonpost.com/world/rss/","rss","1","30","active","","2026-04-24 01:28:55","2026-04-24 01:28:55","1","1");
INSERT INTO `news_sources` VALUES("174","NBC News","https://www.nbcnews.com","https://www.nbcnews.com/id/3032091/device/rss/rss.xml","rss","1","30","active","","2026-04-24 01:28:55","2026-04-24 01:28:55","1","1");
INSERT INTO `news_sources` VALUES("175","CBS News","https://www.cbsnews.com","https://www.cbsnews.com/rss/live/rss.rss","rss","1","30","active","","2026-04-24 01:28:55","2026-04-24 01:28:55","1","1");
INSERT INTO `news_sources` VALUES("176","ABC News","https://abcnews.go.com","https://abcnews.go.com/xml/rss/abc_us_topstories.xml","rss","1","30","active","","2026-04-24 01:28:55","2026-04-24 01:28:55","1","1");
INSERT INTO `news_sources` VALUES("177","NPR News","https://www.npr.org","https://feeds.npr.org/1001/rss.xml","rss","1","30","active","2026-04-29 00:32:48","2026-04-24 01:28:55","2026-04-29 00:32:48","1","1");
INSERT INTO `news_sources` VALUES("178","PBS NewsHour","https://www.pbs.org/newshour","https://www.pbs.org/newshour/rss/feed","rss","1","30","active","","2026-04-24 01:28:55","2026-04-24 01:28:55","1","1");
INSERT INTO `news_sources` VALUES("179","Deutsche Welle","https://www.dw.com","https://www.dw.com/en/rss/top-stories","rss","1","30","active","","2026-04-24 01:28:55","2026-04-24 01:28:55","1","1");
INSERT INTO `news_sources` VALUES("180","France 24","https://www.france24.com","https://www.france24.com/en/rss","rss","1","30","active","","2026-04-24 01:28:55","2026-04-24 01:28:55","1","1");
INSERT INTO `news_sources` VALUES("182","CNBC","https://www.cnbc.com","https://www.cnbc.com/id/100003114/device/rss/rss.html","rss","1","30","active","2026-04-29 00:33:27","2026-04-24 01:28:55","2026-04-29 00:33:27","1","1");
INSERT INTO `news_sources` VALUES("183","Express Tribune","https://tribune.com.pk","https://tribune.com.pk/rss/","rss","1","30","active","","2026-04-24 01:28:55","2026-04-24 01:28:55","1","1");
INSERT INTO `news_sources` VALUES("184","The News International","https://www.thenews.com.pk","https://www.thenews.com.pk/rss","rss","1","30","active","","2026-04-24 01:28:55","2026-04-24 01:28:55","1","1");
INSERT INTO `news_sources` VALUES("185","Pakistan Today","https://www.pakistantoday.com.pk","https://www.pakistantoday.com.pk/feed/","rss","1","30","active","","2026-04-24 01:28:55","2026-04-24 01:28:55","1","1");
INSERT INTO `news_sources` VALUES("186","Dunya News","https://www.dunyanews.tv","https://www.dunyanews.tv/rss.xml","rss","1","30","active","","2026-04-24 01:28:55","2026-04-24 01:28:55","1","1");
INSERT INTO `news_sources` VALUES("187","Samaa TV","https://www.samaa.tv","https://www.samaa.tv/feed/","rss","1","30","active","","2026-04-24 01:28:55","2026-04-24 01:28:55","1","1");
INSERT INTO `news_sources` VALUES("188","24 News HD","https://www.24news.tv","https://www.24news.tv/feed/","rss","1","30","active","","2026-04-24 01:28:55","2026-04-24 01:28:55","1","1");
INSERT INTO `news_sources` VALUES("189","BBC Urdu","https://www.bbc.com/urdu","https://www.bbc.com/urdu/rss","rss","1","30","active","","2026-04-24 01:28:55","2026-04-24 01:28:55","1","1");
INSERT INTO `news_sources` VALUES("190","VOA Urdu","https://www.voaurdu.com","https://www.voaurdu.com/a/rss","rss","1","30","active","","2026-04-24 01:28:55","2026-04-24 01:28:55","1","1");
INSERT INTO `news_sources` VALUES("191","NDTV","https://www.ndtv.com","https://feeds.ndtv.com/ndtv/rss/top-stories.xml","rss","1","30","active","","2026-04-24 01:28:55","2026-04-24 01:28:55","1","1");
INSERT INTO `news_sources` VALUES("192","Times of India","https://timesofindia.indiatimes.com","https://timesofindia.indiatimes.com/rssfeedstopstories.cms","rss","1","30","active","","2026-04-24 01:28:55","2026-04-24 01:28:55","1","1");
INSERT INTO `news_sources` VALUES("194","Toronto Star","https://www.thestar.com","https://www.thestar.com/rss?category=news","rss","1","30","active","","2026-04-24 01:28:55","2026-04-28 00:56:52","1","1");
INSERT INTO `news_sources` VALUES("195","CBC News","https://www.cbc.ca","https://www.cbc.ca/cmlink/rss-topstories","rss","1","30","active","2026-04-29 00:33:24","2026-04-24 01:28:55","2026-04-29 00:33:24","1","1");
INSERT INTO `news_sources` VALUES("196","Globo News","https://g1.globo.com","https://g1.globo.com/rss/g1/","rss","1","30","active","2026-04-29 00:34:02","2026-04-24 01:28:55","2026-04-29 00:34:02","1","1");
INSERT INTO `news_sources` VALUES("197","Financial Times","https://www.ft.com","https://www.ft.com/rss/home","rss","2","30","active","2026-04-29 00:33:37","2026-04-28 00:48:09","2026-04-29 00:33:37","1","1");
INSERT INTO `news_sources` VALUES("198","RT News","https://www.rt.com","https://www.rt.com/rss/","rss","2","30","active","2026-04-29 00:34:19","2026-04-28 00:48:09","2026-04-29 00:34:19","1","1");
INSERT INTO `news_sources` VALUES("199","CGTN","https://news.cgtn.com","https://news.cgtn.com/rss","rss","2","30","active","","2026-04-28 00:48:09","2026-04-28 00:48:09","1","1");
INSERT INTO `news_sources` VALUES("200","RFE/RL Urdu","https://urdu.rferl.org","https://urdu.rferl.org/rss","rss","2","30","active","","2026-04-28 00:48:09","2026-04-28 00:48:09","1","1");


CREATE TABLE `news_tags` (
  `news_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  PRIMARY KEY (`news_id`,`tag_id`),
  KEY `idx_news_tags_news` (`news_id`),
  KEY `idx_news_tags_tag` (`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `news_tags` VALUES("1","9");
INSERT INTO `news_tags` VALUES("1","18");
INSERT INTO `news_tags` VALUES("2","12");
INSERT INTO `news_tags` VALUES("2","13");
INSERT INTO `news_tags` VALUES("3","1");
INSERT INTO `news_tags` VALUES("3","5");
INSERT INTO `news_tags` VALUES("3","13");
INSERT INTO `news_tags` VALUES("4","5");
INSERT INTO `news_tags` VALUES("4","7");
INSERT INTO `news_tags` VALUES("4","10");
INSERT INTO `news_tags` VALUES("5","12");
INSERT INTO `news_tags` VALUES("5","15");
INSERT INTO `news_tags` VALUES("5","16");
INSERT INTO `news_tags` VALUES("6","10");
INSERT INTO `news_tags` VALUES("7","10");
INSERT INTO `news_tags` VALUES("7","14");
INSERT INTO `news_tags` VALUES("7","18");
INSERT INTO `news_tags` VALUES("8","1");
INSERT INTO `news_tags` VALUES("8","11");
INSERT INTO `news_tags` VALUES("8","12");
INSERT INTO `news_tags` VALUES("9","7");
INSERT INTO `news_tags` VALUES("9","10");
INSERT INTO `news_tags` VALUES("9","16");


CREATE TABLE `notification_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('info','success','warning','error','news','event','system') DEFAULT 'info',
  `url` varchar(500) DEFAULT NULL,
  `scheduled_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `sent_at` timestamp NULL DEFAULT NULL,
  `status` enum('pending','sent','failed') DEFAULT 'pending',
  `retry_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `scheduled_at` (`scheduled_at`),
  KEY `status` (`status`),
  CONSTRAINT `notification_queue_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



CREATE TABLE `notification_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `email_notifications` tinyint(1) DEFAULT 1,
  `push_notifications` tinyint(1) DEFAULT 1,
  `news_notifications` tinyint(1) DEFAULT 1,
  `event_notifications` tinyint(1) DEFAULT 1,
  `system_notifications` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user` (`user_id`),
  CONSTRAINT `notification_settings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `notification_settings` VALUES("1","1","1","1","1","1","1","2026-04-09 21:16:24","2026-04-09 21:16:24");


CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('info','success','warning','error','news','event','system') DEFAULT 'info',
  `url` varchar(500) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `read_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `is_read` (`is_read`),
  KEY `created_at` (`created_at`),
  KEY `type` (`type`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `notifications` VALUES("1","1","Welcome to PK Live News Notifications!","The notification system has been successfully installed. You will now receive important updates about news, events, and system activities.","success","admin-dashboard.php","1","2026-04-09 21:16:24","");
INSERT INTO `notifications` VALUES("2","1","Notification System Features","You can manage your notification preferences, view history, and send custom notifications to users from the admin panel.","info","admin/manage-notifications.php","1","2026-04-09 21:16:24","");
INSERT INTO `notifications` VALUES("3","1","Welcome to PK Live News Notifications!","The notification system has been successfully installed. You will now receive important updates about news, events, and system activities.","success","admin-dashboard.php","1","2026-04-09 21:16:46","");
INSERT INTO `notifications` VALUES("4","1","Notification System Features","You can manage your notification preferences, view history, and send custom notifications to users from the admin panel.","info","admin/manage-notifications.php","1","2026-04-09 21:16:46","");


CREATE TABLE `page_views` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `page_url` varchar(500) NOT NULL,
  `page_type` varchar(50) DEFAULT 'page',
  `page_title` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `referrer` varchar(500) DEFAULT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_page_type` (`page_type`),
  KEY `idx_ip_address` (`ip_address`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_session_id` (`session_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_page_type_created` (`page_type`,`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `page_views` VALUES("1","/index.php","home","PK Live News - Home","127.0.0.1","","","","","2026-04-12 01:54:36","2026-04-13 01:54:36");
INSERT INTO `page_views` VALUES("2","/search.php?q=pakistan","search","Search Results","127.0.0.1","","","","","2026-04-11 01:54:36","2026-04-13 01:54:36");
INSERT INTO `page_views` VALUES("3","/search.php?q=politics","search","Search Results","192.168.1.1","","","","","2026-04-10 01:54:36","2026-04-13 01:54:36");
INSERT INTO `page_views` VALUES("4","/categories.php?id=1","category","Breaking News","10.0.0.1","","","","","2026-04-13 00:54:36","2026-04-13 01:54:36");
INSERT INTO `page_views` VALUES("5","/article.php?id=123","article","Latest News Article","203.0.113.1","","","","","2026-04-13 01:54:36","2026-04-13 01:54:36");
INSERT INTO `page_views` VALUES("6","/search.php?q=sports","search","Search Results","172.16.0.1","","","","","2026-04-13 01:54:36","2026-04-13 01:54:36");
INSERT INTO `page_views` VALUES("7","/index.php","home","PK Live News - Home","192.168.1.100","","","","","2026-04-13 01:54:36","2026-04-13 01:54:36");
INSERT INTO `page_views` VALUES("8","/rss.php","rss","RSS Feed","10.0.0.50","","","","","2026-04-13 01:54:36","2026-04-13 01:54:36");
INSERT INTO `page_views` VALUES("9","/index.php","home","PK Live News - Home","127.0.0.1","","","","","2026-04-12 01:54:53","2026-04-13 01:54:53");
INSERT INTO `page_views` VALUES("10","/search.php?q=pakistan","search","Search Results","127.0.0.1","","","","","2026-04-11 01:54:53","2026-04-13 01:54:53");
INSERT INTO `page_views` VALUES("11","/search.php?q=politics","search","Search Results","192.168.1.1","","","","","2026-04-10 01:54:53","2026-04-13 01:54:53");
INSERT INTO `page_views` VALUES("12","/categories.php?id=1","category","Breaking News","10.0.0.1","","","","","2026-04-13 00:54:53","2026-04-13 01:54:53");
INSERT INTO `page_views` VALUES("13","/article.php?id=123","article","Latest News Article","203.0.113.1","","","","","2026-04-13 01:54:53","2026-04-13 01:54:53");
INSERT INTO `page_views` VALUES("14","/search.php?q=sports","search","Search Results","172.16.0.1","","","","","2026-04-13 01:54:53","2026-04-13 01:54:53");
INSERT INTO `page_views` VALUES("15","/index.php","home","PK Live News - Home","192.168.1.100","","","","","2026-04-13 01:54:53","2026-04-13 01:54:53");
INSERT INTO `page_views` VALUES("16","/rss.php","rss","RSS Feed","10.0.0.50","","","","","2026-04-13 01:54:53","2026-04-13 01:54:53");


CREATE TABLE `poll_options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `poll_id` int(11) NOT NULL,
  `option_text` varchar(255) NOT NULL,
  `votes` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `poll_id` (`poll_id`),
  CONSTRAINT `poll_options_ibfk_1` FOREIGN KEY (`poll_id`) REFERENCES `polls` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



CREATE TABLE `poll_votes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `poll_id` int(11) NOT NULL,
  `option_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) NOT NULL,
  `voted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_vote` (`poll_id`,`ip_address`),
  KEY `option_id` (`option_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `poll_votes` VALUES("1","1","1","","::1","2026-04-27 01:30:24","2026-04-27 01:30:24");
INSERT INTO `poll_votes` VALUES("2","1","1","","127.0.0.1","2026-04-27 01:30:55","2026-04-27 01:30:55");
INSERT INTO `poll_votes` VALUES("3","5","8","9","::1","2026-04-28 00:40:28","2026-04-28 00:40:28");


CREATE TABLE `polls` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `question` varchar(500) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive','ended') NOT NULL DEFAULT 'active',
  `ends_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



CREATE TABLE `post_likes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `news_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `news_id` (`news_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=264 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `post_likes` VALUES("1","9","8","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0","2026-04-10 00:57:50");
INSERT INTO `post_likes` VALUES("3","11","8","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36","2026-04-10 10:38:00");
INSERT INTO `post_likes` VALUES("88","7","9","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36","2026-04-11 06:40:09");
INSERT INTO `post_likes` VALUES("95","1","9","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36","2026-04-11 06:42:23");
INSERT INTO `post_likes` VALUES("102","15","1","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 OPR/129.0.0.0","2026-04-11 06:45:17");
INSERT INTO `post_likes` VALUES("103","2","9","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36","2026-04-11 06:45:23");
INSERT INTO `post_likes` VALUES("104","12","1","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 OPR/129.0.0.0","2026-04-11 06:45:45");
INSERT INTO `post_likes` VALUES("105","3","9","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36","2026-04-11 06:46:23");
INSERT INTO `post_likes` VALUES("106","14","1","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 OPR/129.0.0.0","2026-04-11 06:46:24");
INSERT INTO `post_likes` VALUES("107","7","1","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 OPR/129.0.0.0","2026-04-11 06:46:30");
INSERT INTO `post_likes` VALUES("112","1","1","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 OPR/129.0.0.0","2026-04-11 06:48:46");
INSERT INTO `post_likes` VALUES("113","11","1","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 OPR/129.0.0.0","2026-04-11 06:49:01");
INSERT INTO `post_likes` VALUES("114","2","1","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 OPR/129.0.0.0","2026-04-11 06:49:15");
INSERT INTO `post_likes` VALUES("115","5","9","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36","2026-04-11 06:49:23");
INSERT INTO `post_likes` VALUES("116","3","1","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 OPR/129.0.0.0","2026-04-11 06:49:45");
INSERT INTO `post_likes` VALUES("117","4","1","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 OPR/129.0.0.0","2026-04-11 06:50:30");
INSERT INTO `post_likes` VALUES("118","5","1","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 OPR/129.0.0.0","2026-04-11 06:50:44");
INSERT INTO `post_likes` VALUES("119","8","9","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36","2026-04-11 06:51:23");
INSERT INTO `post_likes` VALUES("127","14","9","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36","2026-04-11 06:56:21");
INSERT INTO `post_likes` VALUES("128","16","9","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36","2026-04-11 06:56:34");
INSERT INTO `post_likes` VALUES("132","6","1","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 OPR/129.0.0.0","2026-04-11 07:16:42");
INSERT INTO `post_likes` VALUES("133","16","1","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 OPR/129.0.0.0","2026-04-11 07:27:34");
INSERT INTO `post_likes` VALUES("136","18","10","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0","2026-04-11 07:58:55");
INSERT INTO `post_likes` VALUES("137","9","10","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0","2026-04-11 07:58:59");
INSERT INTO `post_likes` VALUES("138","19","10","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0","2026-04-11 07:59:04");
INSERT INTO `post_likes` VALUES("139","15","10","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0","2026-04-11 07:59:08");
INSERT INTO `post_likes` VALUES("140","12","10","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0","2026-04-11 07:59:13");
INSERT INTO `post_likes` VALUES("141","16","10","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0","2026-04-11 07:59:14");
INSERT INTO `post_likes` VALUES("142","6","10","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0","2026-04-11 08:18:11");
INSERT INTO `post_likes` VALUES("145","21","9","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36","2026-04-11 22:47:30");
INSERT INTO `post_likes` VALUES("146","22","9","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36","2026-04-11 22:47:34");
INSERT INTO `post_likes` VALUES("149","18","11","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 OPR/129.0.0.0","2026-04-12 23:57:56");
INSERT INTO `post_likes` VALUES("150","28","9","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36","2026-04-13 00:12:28");
INSERT INTO `post_likes` VALUES("151","27","9","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36","2026-04-13 00:12:29");
INSERT INTO `post_likes` VALUES("152","29","11","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 OPR/129.0.0.0","2026-04-13 00:14:15");
INSERT INTO `post_likes` VALUES("153","28","1","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0","2026-04-16 12:23:16");
INSERT INTO `post_likes` VALUES("156","39","1","::1","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 OPR/129.0.0.0","2026-04-23 21:16:35");
INSERT INTO `post_likes` VALUES("158","44","1","::1","Mozilla/5.0 (Linux; Android 8.0.0; SM-G955U Build/R16NW) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36 Edg/146.0.0.0","2026-04-24 14:43:11");
INSERT INTO `post_likes` VALUES("159","35","1","::1","Mozilla/5.0 (Linux; Android 8.0.0; SM-G955U Build/R16NW) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36 Edg/146.0.0.0","2026-04-24 14:43:11");
INSERT INTO `post_likes` VALUES("160","33","1","::1","Mozilla/5.0 (Linux; Android 8.0.0; SM-G955U Build/R16NW) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36 Edg/146.0.0.0","2026-04-24 14:43:11");
INSERT INTO `post_likes` VALUES("180","47","14","","","2026-04-26 23:12:41");
INSERT INTO `post_likes` VALUES("181","32","14","","","2026-04-26 23:12:51");
INSERT INTO `post_likes` VALUES("184","46","1","","","2026-04-26 23:49:14");
INSERT INTO `post_likes` VALUES("185","32","1","","","2026-04-27 12:29:10");
INSERT INTO `post_likes` VALUES("186","44","9","","","2026-04-27 12:33:41");
INSERT INTO `post_likes` VALUES("215","30","14","","","2026-04-27 22:30:35");
INSERT INTO `post_likes` VALUES("229","38","14","","","2026-04-28 00:27:55");
INSERT INTO `post_likes` VALUES("234","29","14","","","2026-04-28 00:28:14");
INSERT INTO `post_likes` VALUES("235","34","14","","","2026-04-28 00:28:17");
INSERT INTO `post_likes` VALUES("239","38","9","","","2026-04-28 00:40:14");
INSERT INTO `post_likes` VALUES("241","9","9","","","2026-04-28 00:41:31");
INSERT INTO `post_likes` VALUES("242","18","9","","","2026-04-28 00:41:41");
INSERT INTO `post_likes` VALUES("243","19","9","","","2026-04-28 00:41:43");
INSERT INTO `post_likes` VALUES("244","15","9","","","2026-04-28 00:41:48");
INSERT INTO `post_likes` VALUES("246","37","14","","","2026-04-28 00:48:52");
INSERT INTO `post_likes` VALUES("249","51","9","","","2026-04-28 01:03:11");
INSERT INTO `post_likes` VALUES("250","51","14","","","2026-04-28 02:02:59");
INSERT INTO `post_likes` VALUES("253","73","1","","","2026-04-29 00:15:40");
INSERT INTO `post_likes` VALUES("255","51","1","","","2026-04-29 00:15:48");
INSERT INTO `post_likes` VALUES("256","67","1","","","2026-04-29 00:15:50");
INSERT INTO `post_likes` VALUES("258","73","14","","","2026-04-29 00:18:54");
INSERT INTO `post_likes` VALUES("260","66","14","","","2026-04-29 00:19:00");
INSERT INTO `post_likes` VALUES("261","64","14","","","2026-04-29 00:19:13");
INSERT INTO `post_likes` VALUES("262","62","14","","","2026-04-29 00:19:16");
INSERT INTO `post_likes` VALUES("263","50","14","","","2026-04-29 00:19:21");


CREATE TABLE `role_applications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `applied_role` enum('editor','reporter') NOT NULL,
  `application_data` text DEFAULT NULL,
  `cv_file_path` varchar(500) DEFAULT NULL,
  `cv_file_name` varchar(255) DEFAULT NULL,
  `cv_file_size` int(11) DEFAULT NULL,
  `evidence_type` enum('cv_resume','portfolio','certificates','work_samples','references','publications','other') DEFAULT 'cv_resume',
  `evidence_description` text DEFAULT NULL,
  `evidence_files` text DEFAULT NULL,
  `status` enum('pending','approved','rejected','withdrawn') DEFAULT 'pending',
  `admin_notes` text DEFAULT NULL,
  `reviewed_by` int(11) DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_applied_role` (`applied_role`),
  KEY `reviewed_by` (`reviewed_by`),
  CONSTRAINT `role_applications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_applications_ibfk_2` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `role_applications` VALUES("1","14","editor","{\"experience\":\"Test exphierience for role application\",\"qualifications\":\"Test qualifications\",\"reason\":\"Test reason for applying\",\"samples\":\"Test samples\",\"availability\":\"full-time\"}","uploads/cv/test_cv.pdf","test_cv.pdf","12345","cv_resume","","","","sorry","1","2026-04-26 01:30:17","2026-04-26 01:10:40","2026-04-26 01:30:17");


CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `settings` VALUES("1","site_name","PK Live News","Site name","2026-04-09 10:54:39","2026-04-09 10:54:39");
INSERT INTO `settings` VALUES("2","site_description","Latest news from Pakistan","Site description","2026-04-09 10:54:39","2026-04-09 10:54:39");
INSERT INTO `settings` VALUES("3","site_keywords","news, pakistan, breaking news","Site keywords","2026-04-09 10:54:39","2026-04-09 10:54:39");
INSERT INTO `settings` VALUES("4","contact_email","contact@pklivenews.com","Contact email","2026-04-09 10:54:39","2026-04-09 10:54:39");
INSERT INTO `settings` VALUES("5","facebook_url","https://facebook.com/pklivenews","Facebook url","2026-04-09 10:54:39","2026-04-09 10:54:39");
INSERT INTO `settings` VALUES("6","twitter_url","https://twitter.com/pklivenews","Twitter url","2026-04-09 10:54:39","2026-04-09 10:54:39");
INSERT INTO `settings` VALUES("7","youtube_url","https://youtube.com/pklivenews","Youtube url","2026-04-09 10:54:39","2026-04-09 10:54:39");
INSERT INTO `settings` VALUES("8","instagram_url","https://instagram.com/pklivenews","Instagram url","2026-04-09 10:54:39","2026-04-09 10:54:39");
INSERT INTO `settings` VALUES("9","enable_comments","1","Enable comments","2026-04-09 10:54:39","2026-04-09 10:54:39");
INSERT INTO `settings` VALUES("10","enable_rss","1","Enable rss","2026-04-09 10:54:39","2026-04-09 10:54:39");
INSERT INTO `settings` VALUES("11","enable_weather","1","Enable weather","2026-04-09 10:54:39","2026-04-09 10:54:39");
INSERT INTO `settings` VALUES("12","enable_live_tv","1","Enable live tv","2026-04-09 10:54:39","2026-04-09 10:54:39");
INSERT INTO `settings` VALUES("13","news_per_page","10","News per page","2026-04-09 10:54:39","2026-04-09 10:54:39");
INSERT INTO `settings` VALUES("14","enable_ads","1","Enable ads","2026-04-09 10:54:39","2026-04-09 10:54:39");
INSERT INTO `settings` VALUES("15","maintenance_mode","0","Maintenance mode","2026-04-09 10:54:39","2026-04-09 10:54:39");


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
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `site_settings` VALUES("1","site_name","PK Live News","text","","2026-04-09 10:03:52","2026-04-13 00:07:47");
INSERT INTO `site_settings` VALUES("2","site_description","Latest news and updates from Pakistan","text","","2026-04-09 10:03:52","2026-04-13 00:07:47");
INSERT INTO `site_settings` VALUES("3","posts_per_page","20","number","","2026-04-09 10:03:52","2026-04-13 00:07:47");
INSERT INTO `site_settings` VALUES("4","maintenance_mode","on","boolean","","2026-04-09 10:03:52","2026-04-13 00:07:47");
INSERT INTO `site_settings` VALUES("5","show_trending_news","on","boolean","","2026-04-09 10:03:52","2026-04-13 00:07:47");
INSERT INTO `site_settings` VALUES("6","show_ads","on","boolean","","2026-04-09 10:03:52","2026-04-13 00:07:47");
INSERT INTO `site_settings` VALUES("7","default_language","en","text","","2026-04-09 10:03:52","2026-04-13 00:07:47");
INSERT INTO `site_settings` VALUES("8","contact_email","contact@pklivenews.com","text","","2026-04-09 10:03:52","2026-04-13 00:07:47");
INSERT INTO `site_settings` VALUES("9","social_media_links","{\"facebook\":\"\",\"twitter\":\"\",\"instagram\":\"\",\"youtube\":\"\"}","text","","2026-04-09 10:03:52","2026-04-09 10:03:52");
INSERT INTO `site_settings` VALUES("10","seo_meta_description","PK Live News - Your trusted source for latest news","text","","2026-04-09 10:03:52","2026-04-13 00:07:47");
INSERT INTO `site_settings` VALUES("11","seo_keywords","news, pakistan, breaking news, current affairs","text","","2026-04-09 10:03:52","2026-04-13 00:07:47");
INSERT INTO `site_settings` VALUES("12","cache_duration","3600","number","","2026-04-09 10:03:52","2026-04-13 00:07:47");
INSERT INTO `site_settings` VALUES("13","enable_comments","on","boolean","","2026-04-09 10:03:52","2026-04-13 00:07:47");
INSERT INTO `site_settings` VALUES("14","enable_rss","on","boolean","","2026-04-09 10:03:52","2026-04-13 00:07:47");
INSERT INTO `site_settings` VALUES("15","theme_color","#007bff","text","","2026-04-09 10:03:52","2026-04-09 10:03:52");
INSERT INTO `site_settings` VALUES("16","logo_path","assets/images/logo.png","text","","2026-04-09 10:03:52","2026-04-09 10:03:52");


CREATE TABLE `stream_views` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `stream_id` int(11) NOT NULL,
  `viewer_ip` varchar(45) DEFAULT NULL,
  `viewer_session` varchar(255) DEFAULT NULL,
  `started_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `stream_id` (`stream_id`),
  CONSTRAINT `stream_views_ibfk_1` FOREIGN KEY (`stream_id`) REFERENCES `live_stream` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`),
  KEY `updated_by` (`updated_by`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `system_settings` VALUES("1","site_name","PK Live News","2026-04-09 19:12:07","");
INSERT INTO `system_settings` VALUES("2","site_description","Latest news and updates from Pakistan","2026-04-09 19:12:07","");
INSERT INTO `system_settings` VALUES("3","footer_content","© 2024 PK Live News. All rights reserved.","2026-04-09 19:12:07","");
INSERT INTO `system_settings` VALUES("4","contact_email","admin@pklivenews.com","2026-04-09 19:12:07","");
INSERT INTO `system_settings` VALUES("5","maintenance_mode","0","2026-04-09 19:12:07","");
INSERT INTO `system_settings` VALUES("6","theme_color","#007bff","2026-04-09 19:12:07","");
INSERT INTO `system_settings` VALUES("7","max_file_size","5242880","2026-04-10 07:01:53","");
INSERT INTO `system_settings` VALUES("8","allowed_extensions","jpg,jpeg,png,gif,mp4,mov,avi","2026-04-10 07:01:53","");
INSERT INTO `system_settings` VALUES("9","upload_path","C:UsersDELLOneDriveDesktoppk-news.png","2026-04-10 07:01:53","");
INSERT INTO `system_settings` VALUES("10","session_timeout","3600","2026-04-10 07:03:45","");
INSERT INTO `system_settings` VALUES("11","max_login_attempts","0","2026-04-10 07:03:45","");
INSERT INTO `system_settings` VALUES("12","enable_captcha","0","2026-04-10 07:03:45","");
INSERT INTO `system_settings` VALUES("13","force_https","1","2026-04-10 07:03:45","");


CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `tag_cloud` AS select `t`.`id` AS `id`,`t`.`name` AS `name`,`t`.`slug` AS `slug`,count(`nt`.`news_id`) AS `usage_count`,count(`nt`.`news_id`) * 10 AS `weight` from ((`tags` `t` left join `news_tags` `nt` on(`t`.`id` = `nt`.`tag_id`)) left join `news` `n` on(`nt`.`news_id` = `n`.`id` and `n`.`status` = 'published')) group by `t`.`id`,`t`.`name`,`t`.`slug` order by count(`nt`.`news_id`) desc;



CREATE TABLE `tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `slug` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_tags_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



CREATE TABLE `trusted_sources` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `domain_name` varchar(255) NOT NULL,
  `source_name` varchar(255) DEFAULT NULL,
  `trust_score` decimal(3,2) DEFAULT 0.50,
  `reputation_score` decimal(3,2) DEFAULT 0.50,
  `verified` tinyint(1) DEFAULT 0,
  `fact_check_rating` enum('high','medium','low','unknown') DEFAULT 'unknown',
  `bias_rating` enum('left','center-left','center','center-right','right','unknown') DEFAULT 'unknown',
  `country` varchar(100) DEFAULT NULL,
  `language` varchar(10) DEFAULT 'en',
  `category` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `contact_info` varchar(500) DEFAULT NULL,
  `social_media_links` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`social_media_links`)),
  `alexa_rank` int(11) DEFAULT NULL,
  `monthly_visitors` int(11) DEFAULT NULL,
  `founded_year` int(4) DEFAULT NULL,
  `owner` varchar(255) DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1,
  `last_verified` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_domain` (`domain_name`),
  KEY `idx_trust_score` (`trust_score`),
  KEY `idx_verified` (`verified`),
  KEY `idx_active` (`active`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `trusted_sources` VALUES("1","reuters.com","Reuters","0.95","0.92","1","high","center","United Kingdom","en","","","","","","","","","1","","2026-04-09 19:14:51","2026-04-09 19:14:51");
INSERT INTO `trusted_sources` VALUES("2","ap.org","Associated Press","0.94","0.91","1","high","center","United States","en","","","","","","","","","1","","2026-04-09 19:14:51","2026-04-09 19:14:51");
INSERT INTO `trusted_sources` VALUES("3","bbc.com","BBC News","0.92","0.89","1","high","center-left","United Kingdom","en","","","","","","","","","1","","2026-04-09 19:14:52","2026-04-09 19:14:52");
INSERT INTO `trusted_sources` VALUES("4","dawn.com","Dawn","0.75","0.72","1","medium","center","Pakistan","en","","","","","","","","","1","","2026-04-09 19:14:52","2026-04-09 19:14:52");
INSERT INTO `trusted_sources` VALUES("5","geo.tv","Geo News","0.70","0.67","1","medium","center-right","Pakistan","en","","","","","","","","","1","","2026-04-09 19:14:52","2026-04-09 19:14:52");


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
  KEY `idx_activity_date` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



CREATE TABLE `user_admin_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `assigned_by` int(11) DEFAULT NULL,
  `assigned_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_user_role` (`user_id`,`role_id`),
  KEY `role_id` (`role_id`),
  KEY `assigned_by` (`assigned_by`),
  CONSTRAINT `user_admin_roles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_admin_roles_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `admin_roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_admin_roles_ibfk_3` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `user_admin_roles` VALUES("1","1","1","1","2026-04-10 09:14:56");


CREATE TABLE `user_analytics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `action` varchar(50) NOT NULL DEFAULT 'page_view',
  `page_url` varchar(500) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `referrer` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_action` (`action`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_session_id` (`session_id`),
  KEY `idx_user_action_date` (`user_id`,`action`,`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `user_analytics` VALUES("1","","","page_view","/index.php","127.0.0.1","","","2026-04-12 01:52:06","2026-04-13 01:52:06");
INSERT INTO `user_analytics` VALUES("2","","","page_view","/search.php","127.0.0.1","","","2026-04-11 01:52:06","2026-04-13 01:52:06");
INSERT INTO `user_analytics` VALUES("3","","","search","/search.php?q=news","127.0.0.1","","","2026-04-10 01:52:06","2026-04-13 01:52:06");
INSERT INTO `user_analytics` VALUES("4","1","","page_view","/index.php","192.168.1.1","","","2026-04-13 00:52:06","2026-04-13 01:52:06");
INSERT INTO `user_analytics` VALUES("5","1","","login","/login.php","192.168.1.1","","","2026-04-12 23:52:06","2026-04-13 01:52:06");
INSERT INTO `user_analytics` VALUES("6","2","","page_view","/categories.php","10.0.0.1","","","2026-04-13 01:52:06","2026-04-13 01:52:06");
INSERT INTO `user_analytics` VALUES("7","2","","page_view","/article.php?id=123","10.0.0.1","","","2026-04-13 01:52:06","2026-04-13 01:52:06");
INSERT INTO `user_analytics` VALUES("8","","","page_view","/rss.php","203.0.113.1","","","2026-04-13 01:52:06","2026-04-13 01:52:06");
INSERT INTO `user_analytics` VALUES("9","","","page_view","/index.php","127.0.0.1","","","2026-04-12 01:52:27","2026-04-13 01:52:27");
INSERT INTO `user_analytics` VALUES("10","","","page_view","/search.php","127.0.0.1","","","2026-04-11 01:52:27","2026-04-13 01:52:27");
INSERT INTO `user_analytics` VALUES("11","","","search","/search.php?q=news","127.0.0.1","","","2026-04-10 01:52:27","2026-04-13 01:52:27");
INSERT INTO `user_analytics` VALUES("12","1","","page_view","/index.php","192.168.1.1","","","2026-04-13 00:52:27","2026-04-13 01:52:27");
INSERT INTO `user_analytics` VALUES("13","1","","login","/login.php","192.168.1.1","","","2026-04-12 23:52:27","2026-04-13 01:52:27");
INSERT INTO `user_analytics` VALUES("14","2","","page_view","/categories.php","10.0.0.1","","","2026-04-13 01:52:27","2026-04-13 01:52:27");
INSERT INTO `user_analytics` VALUES("15","2","","page_view","/article.php?id=123","10.0.0.1","","","2026-04-13 01:52:27","2026-04-13 01:52:27");
INSERT INTO `user_analytics` VALUES("16","","","page_view","/rss.php","203.0.113.1","","","2026-04-13 01:52:27","2026-04-13 01:52:27");
INSERT INTO `user_analytics` VALUES("17","","","page_view","/index.php","127.0.0.1","","","2026-04-12 01:53:21","2026-04-13 01:53:21");
INSERT INTO `user_analytics` VALUES("18","","","page_view","/search.php","127.0.0.1","","","2026-04-11 01:53:21","2026-04-13 01:53:21");
INSERT INTO `user_analytics` VALUES("19","","","search","/search.php?q=news","127.0.0.1","","","2026-04-10 01:53:21","2026-04-13 01:53:21");
INSERT INTO `user_analytics` VALUES("20","1","","page_view","/index.php","192.168.1.1","","","2026-04-13 00:53:21","2026-04-13 01:53:21");
INSERT INTO `user_analytics` VALUES("21","1","","login","/login.php","192.168.1.1","","","2026-04-12 23:53:21","2026-04-13 01:53:21");
INSERT INTO `user_analytics` VALUES("22","2","","page_view","/categories.php","10.0.0.1","","","2026-04-13 01:53:21","2026-04-13 01:53:21");
INSERT INTO `user_analytics` VALUES("23","2","","page_view","/article.php?id=123","10.0.0.1","","","2026-04-13 01:53:21","2026-04-13 01:53:21");
INSERT INTO `user_analytics` VALUES("24","","","page_view","/rss.php","203.0.113.1","","","2026-04-13 01:53:21","2026-04-13 01:53:21");
INSERT INTO `user_analytics` VALUES("25","","","page_view","/index.php","127.0.0.1","","","2026-04-12 01:54:36","2026-04-13 01:54:36");
INSERT INTO `user_analytics` VALUES("26","","","page_view","/search.php","127.0.0.1","","","2026-04-11 01:54:36","2026-04-13 01:54:36");
INSERT INTO `user_analytics` VALUES("27","","","search","/search.php?q=news","127.0.0.1","","","2026-04-10 01:54:36","2026-04-13 01:54:36");
INSERT INTO `user_analytics` VALUES("28","1","","page_view","/index.php","192.168.1.1","","","2026-04-13 00:54:36","2026-04-13 01:54:36");
INSERT INTO `user_analytics` VALUES("29","1","","login","/login.php","192.168.1.1","","","2026-04-12 23:54:36","2026-04-13 01:54:36");
INSERT INTO `user_analytics` VALUES("30","2","","page_view","/categories.php","10.0.0.1","","","2026-04-13 01:54:36","2026-04-13 01:54:36");
INSERT INTO `user_analytics` VALUES("31","2","","page_view","/article.php?id=123","10.0.0.1","","","2026-04-13 01:54:36","2026-04-13 01:54:36");
INSERT INTO `user_analytics` VALUES("32","","","page_view","/rss.php","203.0.113.1","","","2026-04-13 01:54:36","2026-04-13 01:54:36");
INSERT INTO `user_analytics` VALUES("33","","","page_view","/index.php","127.0.0.1","","","2026-04-12 01:54:53","2026-04-13 01:54:53");
INSERT INTO `user_analytics` VALUES("34","","","page_view","/search.php","127.0.0.1","","","2026-04-11 01:54:53","2026-04-13 01:54:53");
INSERT INTO `user_analytics` VALUES("35","","","search","/search.php?q=news","127.0.0.1","","","2026-04-10 01:54:53","2026-04-13 01:54:53");
INSERT INTO `user_analytics` VALUES("36","1","","page_view","/index.php","192.168.1.1","","","2026-04-13 00:54:53","2026-04-13 01:54:53");
INSERT INTO `user_analytics` VALUES("37","1","","login","/login.php","192.168.1.1","","","2026-04-12 23:54:53","2026-04-13 01:54:53");
INSERT INTO `user_analytics` VALUES("38","2","","page_view","/categories.php","10.0.0.1","","","2026-04-13 01:54:53","2026-04-13 01:54:53");
INSERT INTO `user_analytics` VALUES("39","2","","page_view","/article.php?id=123","10.0.0.1","","","2026-04-13 01:54:53","2026-04-13 01:54:53");
INSERT INTO `user_analytics` VALUES("40","","","page_view","/rss.php","203.0.113.1","","","2026-04-13 01:54:53","2026-04-13 01:54:53");


CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `email_verified` tinyint(1) DEFAULT 0,
  `two_factor_enabled` tinyint(1) DEFAULT 0,
  `email_notifications` tinyint(1) DEFAULT 1,
  `push_notifications` tinyint(1) DEFAULT 0,
  `newsletter_subscription` tinyint(1) DEFAULT 1,
  `profile_public` tinyint(1) DEFAULT 0,
  `show_activity` tinyint(1) DEFAULT 1,
  `preferred_categories` text DEFAULT NULL,
  `language_preference` varchar(10) DEFAULT 'en',
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expires` datetime DEFAULT NULL,
  `email_verification_token` varchar(255) DEFAULT NULL,
  `email_verification_expires` datetime DEFAULT NULL,
  `department` varchar(50) DEFAULT NULL,
  `experience_level` varchar(20) DEFAULT 'junior',
  `verification_status` enum('unverified','verified','premium') DEFAULT 'unverified',
  `specialization` varchar(100) DEFAULT NULL,
  `skills` text DEFAULT NULL,
  `profile_views` int(11) DEFAULT 0,
  `login_count` int(11) DEFAULT 0,
  `last_login` datetime DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','editor','reporter','user') DEFAULT 'user',
  `admin_level` int(11) DEFAULT 0,
  `admin_permissions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`admin_permissions`)),
  `application_status` enum('none','pending','approved','rejected') DEFAULT 'none',
  `applied_role` enum('editor','reporter') DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `users` VALUES("1","Admin","admin@pklivenews.com","","","","0","1","1","0","1","0","1","8","en","","","9938e23f903f9b33f9c9114e7bb9c4ff07df4be6327ef3093d3cf61d9dd8b552","2026-04-24 21:45:23","","junior","unverified","","","0","0","","$2y$10$2nQGgzSApmGTnnwcJU79bOu9i6L64bCli/qlcNBfUDEqbKvC3PK/2","admin","100","","none","","active","2026-04-09 10:34:15");
INSERT INTO `users` VALUES("8","Muhammad Ibraheem","ibraheem47074@gmail.com","","","uploads/users/69d86e74b395b.jpg","0","0","1","0","1","0","1","","en","39f5c27a69dd0275b46d54b3ce91fc079137c0b5716bab3a1e021f0f475854ae","2026-04-11 23:57:37","","","","junior","unverified","","","0","0","","$2y$10$Me./pOd5ui2pfRe.q/X.Pe2gIQZkWGHAKLCJ.itCLCl1s1eQIBOvO","reporter","0","","none","","active","2026-04-10 00:52:05");
INSERT INTO `users` VALUES("9","Muhammad Kashif","kashif47074@gmail.com","03300394061","","","0","0","1","0","1","0","1","","en","","","","","","junior","unverified","","","0","0","","$2y$10$1pmwpC8HfQw01sPAvRvpS.J7nm2SWOFiDhfePxeUXADXcilxrtFyu","editor","0","","none","","active","2026-04-10 09:30:18");
INSERT INTO `users` VALUES("10","saim iltaf","ibraheeem47074@gmail.com","03300394061","","","0","0","1","0","1","0","1","","en","","","","","","junior","unverified","","","0","0","","$2y$10$LymDZYRoKOgnvei9Q4Ts2evqiqtSbNv94o4GoReEhMA/P73naemBG","user","0","","none","","active","2026-04-11 07:58:16");
INSERT INTO `users` VALUES("11","Salman ali","salman47074@gmail.com","+92 3118195630","","","0","0","1","0","1","0","1","","en","","","","","","junior","unverified","","","0","0","","$2y$10$E4uhQOSXwzNcJvG6kHZ7zuvtWwQFrlyIECMTKdK7WSDikMxeZ./f2","user","0","","none","","active","2026-04-12 23:35:48");
INSERT INTO `users` VALUES("13","kashif khan","kashifkhantkking@gmail.com","+92 3118195630","","","0","0","1","0","1","0","1","","en","","","","","","junior","unverified","","","0","0","","$2y$10$z2kCQNd6iuy2IudEqZUWuedlnguRepBK4WWYadyu.2UKIvoF1mv6K","reporter","0","","none","","active","2026-04-13 11:25:09");
INSERT INTO `users` VALUES("14","hasnain","Hasnain@gmail.com","03300394061","","","0","0","1","0","1","0","1","8","ur","","","","","","junior","unverified","","","0","0","","$2y$10$QrAxfSTHsZY0icoc46ubSOk.cRxrrUuNYN9sNMZf3yZmmXkvGR5/m","user","0","","rejected","","active","2026-04-25 08:05:00");



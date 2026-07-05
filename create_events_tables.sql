-- Create events table for upcoming events
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
  KEY `created_by` (`created_by`),
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create events_criteria table for user event preferences
CREATE TABLE `events_criteria` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `preferred_categories` varchar(500) DEFAULT NULL COMMENT 'Comma-separated categories',
  `preferred_types` varchar(500) DEFAULT NULL COMMENT 'Comma-separated event types',
  `min_priority` enum('low','medium','high','urgent') DEFAULT 'low',
  `notification_advance_days` int(11) DEFAULT 7 COMMENT 'Days before event to notify',
  `notification_advance_hours` int(11) DEFAULT 2 COMMENT 'Hours before event to notify',
  `email_notifications` tinyint(1) DEFAULT 1,
  `push_notifications` tinyint(1) DEFAULT 1,
  `show_past_events` tinyint(1) DEFAULT 0,
  `show_cancelled_events` tinyint(1) DEFAULT 0,
  `max_events_per_day` int(11) DEFAULT 10,
  `auto_register` tinyint(1) DEFAULT 0,
  `only_free_events` tinyint(1) DEFAULT 0,
  `location_filter` varchar(255) DEFAULT NULL COMMENT 'Preferred locations',
  `organizer_filter` varchar(500) DEFAULT NULL COMMENT 'Preferred organizers',
  `tags_filter` varchar(500) DEFAULT NULL COMMENT 'Preferred tags',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user` (`user_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create event_attendees table for event registration
CREATE TABLE `event_attendees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `registration_status` enum('registered','attended','cancelled','no_show') DEFAULT 'registered',
  `registered_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `notes` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_event_user` (`event_id`, `user_id`),
  KEY `event_id` (`event_id`),
  KEY `user_id` (`user_id`),
  FOREIGN KEY (`event_id`) REFERENCES `events`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample events
INSERT INTO `events` (`title`, `description`, `event_date`, `event_time`, `end_date`, `end_time`, `location`, `category`, `type`, `status`, `priority`, `organizer`, `contact_email`, `max_attendees`, `requires_registration`, `tags`) VALUES
('Tech Conference 2026', 'Annual technology conference featuring latest innovations', '2026-05-15', '09:00:00', '2026-05-15', '18:00:00', 'Convention Center, Karachi', 'technology', 'conference', 'upcoming', 'high', 'Tech Association', 'info@techconf.pk', 500, 1, 'technology,innovation,conference'),
('Political Rally', 'Community gathering for political discussion', '2026-04-20', '16:00:00', '2026-04-20', '20:00:00', 'Public Park, Lahore', 'politics', 'political', 'upcoming', 'medium', 'Political Party', 'contact@party.pk', 1000, 0, 'politics,community,rally'),
('Sports Tournament', 'Inter-city cricket championship', '2026-04-25', '10:00:00', '2026-04-27', '18:00:00', 'Sports Complex, Islamabad', 'sports', 'sports', 'upcoming', 'medium', 'Sports Federation', 'sports@federation.pk', 200, 1, 'sports,cricket,tournament'),
('Business Workshop', 'Entrepreneurship and startup strategies', '2026-05-01', '14:00:00', '2026-05-01', '17:00:00', 'Business Center, Karachi', 'business', 'workshop', 'upcoming', 'low', 'Business Council', 'workshop@business.pk', 50, 1, 'business,workshop,entrepreneurship'),
('Health Webinar', 'Mental health awareness session', '2026-04-18', '19:00:00', '2026-04-18', '20:30:00', 'Online', 'health', 'webinar', 'upcoming', 'medium', 'Health Organization', 'webinar@health.org', 100, 1, 'health,webinar,mental-health');

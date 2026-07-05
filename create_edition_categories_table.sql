-- Create edition_categories table
CREATE TABLE IF NOT EXISTS `edition_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `slug` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(50) DEFAULT 'fa-layer-group',
  `color` varchar(7) DEFAULT '#007bff',
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default edition categories
INSERT INTO `edition_categories` (`name`, `slug`, `description`, `icon`, `color`) VALUES
('Breaking News', 'breaking', 'Urgent breaking news editions', 'fa-exclamation-triangle', '#dc3545'),
('Morning Edition', 'morning', 'Daily morning news roundup', 'fa-sun', '#28a745'),
('Evening Edition', 'evening', 'Daily evening news summary', 'fa-moon', '#343a40'),
('Special Report', 'special', 'In-depth special reports', 'fa-star', '#ffc107'),
('Weekend Edition', 'weekend', 'Weekend news highlights', 'fa-calendar-week', '#6f42c1'),
('Regional News', 'regional', 'Regional news coverage', 'fa-map-marker-alt', '#17a2b8');

-- Create polls table
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

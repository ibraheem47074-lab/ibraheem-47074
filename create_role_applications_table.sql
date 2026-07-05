-- Create role applications table
CREATE TABLE IF NOT EXISTS `role_applications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `applied_role` enum('editor','reporter') NOT NULL,
  `application_data` text DEFAULT NULL,
  `status` enum('pending','approved','rejected','withdrawn') DEFAULT 'pending',
  `admin_notes` text DEFAULT NULL,
  `reviewed_by` int(11) DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_applied_role` (`applied_role`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add application_status column to users table if it doesn't exist
ALTER TABLE `users` 
ADD COLUMN IF NOT EXISTS `application_status` enum('none','pending','approved','rejected') DEFAULT 'none' AFTER `role`,
ADD COLUMN IF NOT EXISTS `applied_role` enum('editor','reporter') DEFAULT NULL AFTER `application_status`;

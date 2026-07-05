-- Create admin roles table
CREATE TABLE IF NOT EXISTS `admin_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_name` varchar(50) NOT NULL UNIQUE,
  `role_level` int(11) NOT NULL DEFAULT 1,
  `permissions` json DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_role_name` (`role_name`),
  KEY `idx_role_level` (`role_level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create admin permissions table
CREATE TABLE IF NOT EXISTS `admin_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `permission_key` varchar(100) NOT NULL UNIQUE,
  `permission_name` varchar(100) NOT NULL,
  `permission_group` varchar(50) NOT NULL DEFAULT 'general',
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_permission_key` (`permission_key`),
  KEY `idx_permission_group` (`permission_group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create user admin roles mapping table
CREATE TABLE IF NOT EXISTS `user_admin_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `assigned_by` int(11) DEFAULT NULL,
  `assigned_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_user_role` (`user_id`, `role_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`role_id`) REFERENCES `admin_roles` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add admin_level column to users table if it doesn't exist
ALTER TABLE `users` 
ADD COLUMN IF NOT EXISTS `admin_level` int(11) DEFAULT 0 AFTER `role`,
ADD COLUMN IF NOT EXISTS `admin_permissions` json DEFAULT NULL AFTER `admin_level`;

-- Insert default admin roles
INSERT IGNORE INTO `admin_roles` (`role_name`, `role_level`, `permissions`, `description`) VALUES
('Super Admin', 100, '["all"]', 'Full system access with all permissions'),
('Content Manager', 80, '["content_manage", "user_manage", "analytics_view", "role_applications_review"]', 'Manage content, users, and review applications'),
('Editor', 60, '["content_edit", "content_publish", "analytics_view"]', 'Edit and publish content'),
('Moderator', 40, '["content_moderate", "comments_manage"]', 'Moderate content and manage comments'),
('Reporter', 20, '["content_create", "comments_manage"]', 'Create content and manage own comments');

-- Insert default admin permissions
INSERT IGNORE INTO `admin_permissions` (`permission_key`, `permission_name`, `permission_group`, `description`) VALUES
-- General permissions
('all', 'Full Access', 'general', 'Complete system access'),
('dashboard_view', 'View Dashboard', 'general', 'Access admin dashboard'),

-- User management
('user_manage', 'Manage Users', 'users', 'Create, edit, delete users'),
('user_view', 'View Users', 'users', 'View user list and details'),
('user_role_assign', 'Assign User Roles', 'users', 'Assign roles to users'),

-- Content management
('content_create', 'Create Content', 'content', 'Create new articles and content'),
('content_edit', 'Edit Content', 'content', 'Edit existing articles and content'),
('content_publish', 'Publish Content', 'content', 'Publish and unpublish content'),
('content_delete', 'Delete Content', 'content', 'Delete articles and content'),
('content_manage', 'Manage All Content', 'content', 'Full content management access'),
('content_moderate', 'Moderate Content', 'content', 'Review and moderate content'),

-- Application management
('role_applications_review', 'Review Role Applications', 'applications', 'Review and approve/reject role applications'),
('role_applications_manage', 'Manage Role Applications', 'applications', 'Full application management access'),

-- Analytics and reports
('analytics_view', 'View Analytics', 'analytics', 'Access analytics and reports'),
('reports_view', 'View Reports', 'analytics', 'Access system reports'),

-- System settings
('settings_manage', 'Manage Settings', 'system', 'Access system settings'),
('system_logs', 'View System Logs', 'system', 'Access system logs and audit trails'),

-- Comment management
('comments_manage', 'Manage Comments', 'content', 'Manage and moderate comments');

-- Update existing admin users to Super Admin role
UPDATE `users` SET `admin_level` = 100 WHERE `role` = 'admin';

-- Assign Super Admin role to existing admin users
INSERT IGNORE INTO `user_admin_roles` (`user_id`, `role_id`, `assigned_by`)
SELECT u.id, ar.id, u.id 
FROM `users` u 
CROSS JOIN `admin_roles` ar 
WHERE u.role = 'admin' AND ar.role_name = 'Super Admin';

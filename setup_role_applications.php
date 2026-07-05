<?php
require_once 'config/database.php';

echo "Setting up role application system...\n";

// 1. Create role_applications table
echo "Creating role_applications table...\n";
$create_table = "
CREATE TABLE IF NOT EXISTS `role_applications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `applied_role` enum('editor','reporter') NOT NULL,
  `application_data` text DEFAULT NULL,
  `cv_file_path` varchar(500) DEFAULT NULL,
  `cv_file_name` varchar(255) DEFAULT NULL,
  `cv_file_size` int(11) DEFAULT NULL,
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if (mysqli_query($conn, $create_table)) {
    echo "✓ role_applications table created successfully\n";
} else {
    echo "✗ Error creating role_applications table: " . mysqli_error($conn) . "\n";
}

// 2. Add application_status columns to users table
echo "Adding application_status columns to users table...\n";
$alter_table = "
ALTER TABLE `users` 
ADD COLUMN IF NOT EXISTS `application_status` enum('none','pending','approved','rejected') DEFAULT 'none' AFTER `role`,
ADD COLUMN IF NOT EXISTS `applied_role` enum('editor','reporter') DEFAULT NULL AFTER `application_status`";

if (mysqli_query($conn, $alter_table)) {
    echo "✓ application_status columns added to users table\n";
} else {
    echo "✗ Error adding application_status columns: " . mysqli_error($conn) . "\n";
}

// 3. Create upload directories
echo "Creating upload directories...\n";
$directories = [
    'uploads/cv/',
    'uploads/cvs/'
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        if (mkdir($dir, 0755, true)) {
            echo "✓ Created directory: $dir\n";
        } else {
            echo "✗ Failed to create directory: $dir\n";
        }
    } else {
        echo "✓ Directory already exists: $dir\n";
    }
}

// 4. Check if the tables exist properly
echo "\nVerifying setup...\n";
$result = mysqli_query($conn, "SHOW TABLES LIKE 'role_applications'");
if (mysqli_num_rows($result) > 0) {
    echo "✓ role_applications table exists\n";
} else {
    echo "✗ role_applications table missing\n";
}

$result = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'application_status'");
if (mysqli_num_rows($result) > 0) {
    echo "✓ application_status column exists in users table\n";
} else {
    echo "✗ application_status column missing in users table\n";
}

echo "\nSetup complete!\n";
?>

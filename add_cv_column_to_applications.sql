-- Add CV column to role_applications table
ALTER TABLE `role_applications` 
ADD COLUMN IF NOT EXISTS `cv_file_path` varchar(500) DEFAULT NULL AFTER `application_data`,
ADD COLUMN IF NOT EXISTS `cv_file_name` varchar(255) DEFAULT NULL AFTER `cv_file_path`,
ADD COLUMN IF NOT EXISTS `cv_file_size` int(11) DEFAULT NULL AFTER `cv_file_name`;

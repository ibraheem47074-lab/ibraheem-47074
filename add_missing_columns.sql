-- SQL Script to add missing columns to users table
-- Run this script in your database management tool (phpMyAdmin, MySQL Workbench, etc.)

-- Add phone column
ALTER TABLE users ADD COLUMN phone VARCHAR(20) NULL AFTER email;

-- Add bio column
ALTER TABLE users ADD COLUMN bio TEXT NULL AFTER phone;

-- Add image column
ALTER TABLE users ADD COLUMN image VARCHAR(255) NULL AFTER bio;

-- Add email_verified column
ALTER TABLE users ADD COLUMN email_verified TINYINT(1) DEFAULT 0 AFTER image;

-- Add two_factor_enabled column
ALTER TABLE users ADD COLUMN two_factor_enabled TINYINT(1) DEFAULT 0 AFTER email_verified;

-- Add email_notifications column
ALTER TABLE users ADD COLUMN email_notifications TINYINT(1) DEFAULT 1 AFTER two_factor_enabled;

-- Add push_notifications column
ALTER TABLE users ADD COLUMN push_notifications TINYINT(1) DEFAULT 0 AFTER email_notifications;

-- Add newsletter_subscription column
ALTER TABLE users ADD COLUMN newsletter_subscription TINYINT(1) DEFAULT 1 AFTER push_notifications;

-- Add profile_public column
ALTER TABLE users ADD COLUMN profile_public TINYINT(1) DEFAULT 0 AFTER newsletter_subscription;

-- Add show_activity column
ALTER TABLE users ADD COLUMN show_activity TINYINT(1) DEFAULT 1 AFTER profile_public;

-- Add preferred_categories column
ALTER TABLE users ADD COLUMN preferred_categories TEXT NULL AFTER show_activity;

-- Add language_preference column
ALTER TABLE users ADD COLUMN language_preference VARCHAR(10) DEFAULT 'en' AFTER preferred_categories;

-- Add reset_token column
ALTER TABLE users ADD COLUMN reset_token VARCHAR(255) NULL AFTER language_preference;

-- Add reset_token_expires column
ALTER TABLE users ADD COLUMN reset_token_expires DATETIME NULL AFTER reset_token;

-- Add email_verification_token column
ALTER TABLE users ADD COLUMN email_verification_token VARCHAR(255) NULL AFTER reset_token_expires;

-- Add email_verification_expires column
ALTER TABLE users ADD COLUMN email_verification_expires DATETIME NULL AFTER email_verification_token;

-- Add department column
ALTER TABLE users ADD COLUMN department VARCHAR(50) NULL AFTER email_verification_expires;

-- Add experience_level column
ALTER TABLE users ADD COLUMN experience_level VARCHAR(20) DEFAULT 'junior' AFTER department;

-- Add verification_status column
ALTER TABLE users ADD COLUMN verification_status ENUM('unverified', 'verified', 'premium') DEFAULT 'unverified' AFTER experience_level;

-- Add specialization column
ALTER TABLE users ADD COLUMN specialization VARCHAR(100) NULL AFTER verification_status;

-- Add skills column
ALTER TABLE users ADD COLUMN skills TEXT NULL AFTER specialization;

-- Add profile_views column
ALTER TABLE users ADD COLUMN profile_views INT DEFAULT 0 AFTER skills;

-- Add login_count column
ALTER TABLE users ADD COLUMN login_count INT DEFAULT 0 AFTER profile_views;

-- Add last_login column
ALTER TABLE users ADD COLUMN last_login DATETIME NULL AFTER login_count;

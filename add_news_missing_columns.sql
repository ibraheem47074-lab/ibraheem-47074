-- SQL Script to add missing columns to news table
-- Run this script in your database management tool (phpMyAdmin, MySQL Workbench, etc.)

-- Add likes_count column if it doesn't exist
ALTER TABLE news ADD COLUMN likes_count INT(11) DEFAULT 0 AFTER views;

-- Add comment_count column if it doesn't exist  
ALTER TABLE news ADD COLUMN comment_count INT(11) DEFAULT 0 AFTER likes_count;

-- Add engagement_score column if it doesn't exist
ALTER TABLE news ADD COLUMN engagement_score DECIMAL(10,2) DEFAULT 0.00 AFTER comment_count;

-- Add share_count column if it doesn't exist
ALTER TABLE news ADD COLUMN share_count INT(11) DEFAULT 0 AFTER engagement_score;

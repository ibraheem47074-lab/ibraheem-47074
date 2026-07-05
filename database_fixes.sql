-- Database Fixes for PK Live News
-- Run these SQL commands to fix missing columns and tables

-- Fix 1: Add image column to users table if it doesn't exist
ALTER TABLE users ADD COLUMN IF NOT EXISTS image VARCHAR(255) NULL AFTER email;

-- Fix 2: Add image_type column to articles table if it doesn't exist
ALTER TABLE articles ADD COLUMN IF NOT EXISTS image_type VARCHAR(50) DEFAULT 'standard' AFTER image;

-- Fix 3: Create affiliate_products table if it doesn't exist
CREATE TABLE IF NOT EXISTS affiliate_products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2),
    affiliate_url VARCHAR(500),
    image_url VARCHAR(500),
    category VARCHAR(100),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Fix 4: Add user_id column to polls table if it doesn't exist
ALTER TABLE polls ADD COLUMN IF NOT EXISTS user_id INT NULL AFTER id;

-- Fix 5: Add source_name column to articles table if it doesn't exist
ALTER TABLE articles ADD COLUMN IF NOT EXISTS source_name VARCHAR(255) NULL AFTER source;

-- Fix 6: Add index for better performance
CREATE INDEX IF NOT EXISTS idx_articles_source_name ON articles(source_name);
CREATE INDEX IF NOT EXISTS idx_polls_user_id ON polls(user_id);

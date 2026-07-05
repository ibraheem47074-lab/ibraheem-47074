-- Fix missing columns in advertisements table

-- Add size column if it doesn't exist
ALTER TABLE advertisements ADD COLUMN IF NOT EXISTS size VARCHAR(50) DEFAULT NULL;

-- Add page_type column if it doesn't exist
ALTER TABLE advertisements ADD COLUMN IF NOT EXISTS page_type ENUM('all', 'home', 'category', 'news', 'live', 'contact', 'search', 'profile', 'performance') DEFAULT 'all';

-- Add category_id column if it doesn't exist
ALTER TABLE advertisements ADD COLUMN IF NOT EXISTS category_id INT DEFAULT NULL;

-- Add device_type column if it doesn't exist
ALTER TABLE advertisements ADD COLUMN IF NOT EXISTS device_type ENUM('all', 'desktop', 'mobile', 'tablet') DEFAULT 'all';

-- Update position column to include all new positions
ALTER TABLE advertisements MODIFY COLUMN position ENUM('header', 'sidebar', 'footer', 'all', 'live_header', 'live_sidebar', 'live_footer', 'live_popup', 'performance_header', 'performance_sidebar', 'performance_footer', 'performance_inline', 'contact_header', 'contact_sidebar', 'contact_footer', 'category_header', 'category_sidebar', 'category_footer', 'category_inline', 'home_hero', 'home_featured', 'home_sidebar', 'home_footer', 'news_inline', 'search_sidebar', 'profile_sidebar') DEFAULT 'sidebar';

-- Add foreign key for category_id if it doesn't exist
ALTER TABLE advertisements ADD CONSTRAINT IF NOT EXISTS fk_ad_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL;

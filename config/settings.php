<?php
// Dynamic Settings Management System
class SettingsManager {
    private static $settings = null;
    private static $conn;
    
    public static function init($connection) {
        self::$conn = $connection;
        self::loadSettings();
    }
    
    private static function loadSettings() {
        // Create settings table if not exists
        self::createSettingsTable();
        
        // Load all settings from database
        $query = "SELECT setting_key, setting_value FROM site_settings";
        $result = mysqli_query(self::$conn, $query);
        
        self::$settings = [];
        while ($row = mysqli_fetch_assoc($result)) {
            self::$settings[$row['setting_key']] = $row['setting_value'];
        }
        
        // Set default values for missing settings
        self::setDefaults();
    }
    
    private static function createSettingsTable() {
        $query = "CREATE TABLE IF NOT EXISTS site_settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(100) UNIQUE NOT NULL,
            setting_value TEXT,
            setting_type ENUM('text', 'number', 'boolean', 'json') DEFAULT 'text',
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        mysqli_query(self::$conn, $query);
    }
    
    private static function setDefaults() {
        $defaults = [
            'site_name' => 'PK Live News',
            'site_description' => 'Latest news and updates from Pakistan',
            'posts_per_page' => '10',
            'maintenance_mode' => 'off',
            'show_trending_news' => 'on',
            'show_ads' => 'on',
            'default_language' => 'en',
            'contact_email' => 'contact@pklivenews.com',
            'social_media_links' => json_encode([
                'facebook' => '',
                'twitter' => '',
                'instagram' => '',
                'youtube' => ''
            ]),
            'seo_meta_description' => 'PK Live News - Your trusted source for latest news',
            'seo_keywords' => 'news, pakistan, breaking news, current affairs',
            'cache_duration' => '3600',
            'enable_comments' => 'on',
            'enable_rss' => 'on',
            'theme_color' => '#007bff',
            'logo_path' => 'assets/images/logo.png'
        ];
        
        foreach ($defaults as $key => $value) {
            if (!isset(self::$settings[$key])) {
                self::set($key, $value);
            }
        }
    }
    
    public static function get($key, $default = null) {
        if (self::$settings === null) {
            throw new Exception("Settings not initialized. Call SettingsManager::init() first.");
        }
        
        return self::$settings[$key] ?? $default;
    }
    
    public static function set($key, $value, $type = 'text', $description = '') {
        if (self::$settings === null) {
            throw new Exception("Settings not initialized. Call SettingsManager::init() first.");
        }
        
        // Update database
        $query = "INSERT INTO site_settings (setting_key, setting_value, setting_type, description) 
                  VALUES (?, ?, ?, ?) 
                  ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), 
                                         setting_type = VALUES(setting_type),
                                         description = VALUES(description),
                                         updated_at = CURRENT_TIMESTAMP";
        
        $stmt = mysqli_prepare(self::$conn, $query);
        mysqli_stmt_bind_param($stmt, 'ssss', $key, $value, $type, $description);
        mysqli_stmt_execute($stmt);
        
        // Update cache
        self::$settings[$key] = $value;
        
        return true;
    }
    
    public static function getAll() {
        if (self::$settings === null) {
            throw new Exception("Settings not initialized. Call SettingsManager::init() first.");
        }
        
        return self::$settings;
    }
    
    public static function getBoolean($key, $default = false) {
        $value = self::get($key, $default);
        return in_array(strtolower($value), ['on', 'true', '1', 'yes', 'enabled']);
    }
    
    public static function getNumber($key, $default = 0) {
        $value = self::get($key, $default);
        return is_numeric($value) ? (int)$value : $default;
    }
    
    public static function getJson($key, $default = []) {
        $value = self::get($key, '{}');
        $decoded = json_decode($value, true);
        return $decoded !== null ? $decoded : $default;
    }
}

?>

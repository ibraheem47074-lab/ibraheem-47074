<?php
// Environment Configuration Loader
class EnvLoader {
    private static $loaded = false;
    
    public static function load($path = __DIR__ . '/../.env') {
        if (self::$loaded) return;
        
        if (!file_exists($path)) {
            throw new Exception("Environment file not found: {$path}");
        }
        
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) continue;
            
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                // Handle empty values
                if ($value === '""' || $value === "''") {
                    $value = '';
                }
                
                // Remove quotes if present
                $value = trim($value, '"\'');
                
                putenv("{$key}={$value}");
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            }
        }
        
        self::$loaded = true;
    }
    
    public static function get($key, $default = null) {
        return $_ENV[$key] ?? getenv($key) ?? $default;
    }
}

// Load environment
try {
    EnvLoader::load();
} catch (Exception $e) {
    // .env file not found, will use fallback values
}

// Define constants with fallback values
if (!defined('DB_HOST')) {
    define('DB_HOST', EnvLoader::get('DB_HOST', 'localhost'));
}
if (!defined('DB_USER')) {
    define('DB_USER', EnvLoader::get('DB_USER', 'u129650532_ibraheem'));
}
if (!defined('DB_PASS')) {
    define('DB_PASS', EnvLoader::get('DB_PASS', 'Khan47074$'));
}
if (!defined('DB_NAME')) {
    define('DB_NAME', EnvLoader::get('DB_NAME', 'u129650532_ibraheem'));
}
if (!defined('SITE_URL')) {
    define('SITE_URL', EnvLoader::get('SITE_URL', 'https://pk-news.com'));
}
if (!defined('SITE_NAME')) {
    define('SITE_NAME', EnvLoader::get('SITE_NAME', 'PK Live News'));
}
if (!defined('APP_ENV')) {
    define('APP_ENV', EnvLoader::get('APP_ENV', 'development'));
}
?>

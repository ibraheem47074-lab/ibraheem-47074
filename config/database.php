<?php
// Load Environment Configuration
require_once __DIR__ . '/env.php';
require_once __DIR__ . '/settings.php';

// Database Configuration from Environment
if (!defined('DB_HOST')) {
    define('DB_HOST', EnvLoader::get('DB_HOST', 'localhost'));
}
if (!defined('DB_USER')) {
    define('DB_USER', EnvLoader::get('DB_USER', 'root'));
}
if (!defined('DB_PASS')) {
    define('DB_PASS', EnvLoader::get('DB_PASS', ''));
}
if (!defined('DB_NAME')) {
    define('DB_NAME', EnvLoader::get('DB_NAME', 'pk_live_news'));
}

// Site Configuration from Environment
if (!defined('SITE_URL')) {
    define('SITE_URL', EnvLoader::get('SITE_URL', 'http://localhost/pk-live-news/'));
}
if (!defined('SITE_NAME')) {
    define('SITE_NAME', EnvLoader::get('SITE_NAME', 'PK Live News'));
}
if (!defined('APP_ENV')) {
    define('APP_ENV', EnvLoader::get('APP_ENV', 'development'));
}

// Create database connection
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Initialize Settings Manager
SettingsManager::init($conn);

// Set charset and collation
mysqli_set_charset($conn, "utf8mb4");

// Ensure consistent collation for queries
mysqli_query($conn, "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");

// Site Configuration from Environment (already defined above)
define('ADMIN_EMAIL', 'admin@pklivenews.com');
define('SUPPORT_EMAIL', 'support@pklivenews.com');
define('ADVERTISING_EMAIL', 'ads@pklivenews.com');

// Email Configuration (for production)
define('SMTP_HOST', 'smtp.pklivenews.com');
define('SMTP_USER', 'admin@pklivenews.com');
define('SMTP_PASS', 'your_email_password');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');

// File Upload Configuration
define('UPLOAD_PATH', 'uploads/');
define('MAX_FILE_SIZE', 5242880); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'mp4', 'mov', 'avi']);

// Session Configuration
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('Asia/Karachi');

// Helper Functions
function clean_input($data) {
    $data = trim($data ?? '');
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function format_date($date) {
    // Handle invalid or empty dates
    if (empty($date) || $date === '0000-00-00 00:00:00' || $date === '1970-01-01 00:00:00') {
        return 'No date';
    }
    
    $timestamp = strtotime($date);
    if ($timestamp === false || $timestamp < 0) {
        return 'Invalid date';
    }
    
    return date('M d, Y - h:i A', $timestamp);
}

function slugify($text) {
    $text = preg_replace('~[^\\pL\d]+~u', '-', $text);
    $text = trim($text, '-');
    $text = strtolower($text);
    return $text;
}

function create_slug($text) {
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/\s+/', '-', $text);
    $text = preg_replace('/-+/', '-', $text);
    return trim($text, '-');
}

function is_admin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function is_reporter() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'reporter';
}

function is_editor() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'editor';
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function get_user_role($user_id) {
    global $conn;
    $query = "SELECT role FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    return $user ? $user['role'] : null;
}

function is_user_admin($user_id) {
    $role = get_user_role($user_id);
    return $role === 'admin';
}

function is_user_editor($user_id) {
    $role = get_user_role($user_id);
    return $role === 'editor' || $role === 'admin';
}

function format_date_realtime($date) {
    // Handle invalid or empty dates
    if (empty($date) || $date === '0000-00-00 00:00:00' || $date === '1970-01-01 00:00:00') {
        return 'Date not available';
    }
    
    try {
        $now = new DateTime();
        $date_obj = new DateTime($date);
        
        // Check if date is in the future (invalid)
        if ($date_obj > $now) {
            return $date_obj->format('M j, Y') . ' at ' . $date_obj->format('g:i A');
        }
        
        $interval = $now->diff($date_obj);
        
        if ($interval->days == 0) {
            if ($interval->h == 0) {
                if ($interval->i == 0) {
                    return 'Just now';
                } elseif ($interval->i == 1) {
                    return '1 minute ago';
                } else {
                    return $interval->i . ' minutes ago';
                }
            } elseif ($interval->h == 1) {
                return '1 hour ago';
            } else {
                return $interval->h . ' hours ago';
            }
        } elseif ($interval->days == 1) {
            // Show "Yesterday at time" for yesterday's posts
            return 'Yesterday at ' . $date_obj->format('g:i A');
        } elseif ($interval->days < 7) {
            return $interval->days . ' days ago';
        } else {
            // Show real date and time for older posts
            return $date_obj->format('M j, Y') . ' at ' . $date_obj->format('g:i A');
        }
    } catch (Exception $e) {
        return 'Invalid date';
    }
}

function format_clear_date($date) {
    $date_obj = new DateTime($date);
    $now = new DateTime();
    $interval = $now->diff($date_obj);
    
    // For today's news, show time only
    if ($interval->days == 0) {
        return $date_obj->format('g:i A') . ' Today';
    }
    // For yesterday, show "Yesterday at time"
    elseif ($interval->days == 1) {
        return 'Yesterday at ' . $date_obj->format('g:i A');
    }
    // For this week, show day name and time
    elseif ($interval->days < 7) {
        return $date_obj->format('l') . ' at ' . $date_obj->format('g:i A');
    }
    // For older news, show full date
    else {
        return $date_obj->format('M j, Y') . ' at ' . $date_obj->format('g:i A');
    }
}

function format_news_date($date) {
    // Handle empty or invalid dates
    if (empty($date) || $date === '0000-00-00 00:00:00' || $date === '1970-01-01 00:00:00') {
        return 'Date not available';
    }
    
    try {
        $date_obj = new DateTime($date);
    } catch (Exception $e) {
        return 'Invalid date';
    }
    
    $now = new DateTime();
    $interval = $now->diff($date_obj);
    
    // Show relative time for recent news
    if ($interval->days == 0 && $interval->h < 6) {
        if ($interval->h == 0) {
            if ($interval->i == 0) {
                return 'Just now';
            } elseif ($interval->i < 30) {
                return $interval->i . ' minutes ago';
            } else {
                return $date_obj->format('g:i A');
            }
        } else {
            return $interval->h . ' hours ago';
        }
    }
    // Show clear date for older news
    else {
        return $date_obj->format('M j, Y') . ' • ' . $date_obj->format('g:i A');
    }
}

function format_news_content($content) {
    if (empty($content)) {
        return '';
    }
    
    // Fix HTML entity encoding issues first
    $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    
    // Fix common encoding artifacts
    $content = str_replace('#039;', "'", $content);
    $content = str_replace('&#039;', "'", $content);
    $content = str_replace('&amp;#039;', "'", $content);
    $content = str_replace('&amp;', '&', $content);
    $content = str_replace('&quot;', '"', $content);
    $content = str_replace('&lt;', '<', $content);
    $content = str_replace('&gt;', '>', $content);
    
    // Remove any PHP/HTML code that might be in the content
    $content = preg_replace('/<\?php.*?\?>/s', '', $content);
    $content = preg_replace('/<\?[^>]*>/s', '', $content);
    
    // Remove any HTML comments
    $content = preg_replace('/<!--.*?-->/s', '', $content);
    
    // Clean up any remaining HTML tags that shouldn't be there
    $content = strip_tags($content, '<p><br><strong><em><u><ol><ul><li><h1><h2><h3><h4><h5><h6><blockquote><a><img>');
    
    // Fix any broken HTML tags
    $content = preg_replace('/<p>\s*<\/p>/', '', $content);
    $content = preg_replace('/<br\s*\/?>\s*<br\s*\/?>/', '<br>', $content);
    
    // Ensure proper paragraph formatting
    $content = preg_replace('/\n\n+/', '</p><p>', $content);
    $content = preg_replace('/\n/', '<br>', $content);
    
    // Wrap in paragraphs if not already
    if (strpos($content, '<p>') === false) {
        $content = '<p>' . $content . '</p>';
    }
    
    // Final HTML entity cleanup
    $content = htmlspecialchars_decode($content, ENT_QUOTES);
    
    return $content;
}

function clean_news_content($content) {
    if (empty($content)) {
        return '';
    }
    
    // Remove all PHP code
    $content = preg_replace('/<\?php.*?\?>/s', '', $content);
    
    // Remove all HTML comments
    $content = preg_replace('/<!--.*?-->/s', '', $content);
    
    // Remove script and style tags
    $content = preg_replace('/<script.*?<\/script>/is', '', $content);
    $content = preg_replace('/<style.*?<\/style>/is', '', $content);
    
    // Remove any remaining PHP tags
    $content = preg_replace('/<\?[^>]*>/s', '', $content);
    
    // Clean up whitespace
    $content = trim($content);
    
    return $content;
}

function redirect($url) {
    header("Location: " . $url);
    exit();
}

function time_ago($datetime) {
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;
    
    if ($diff < 60) {
        return 'Just now';
    } elseif ($diff < 3600) {
        return floor($diff / 60) . ' minutes ago';
    } elseif ($diff < 86400) {
        return floor($diff / 3600) . ' hours ago';
    } elseif ($diff < 604800) {
        return floor($diff / 86400) . ' days ago';
    } else {
        return format_date($datetime);
    }
}

function format_file_size($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}
?>

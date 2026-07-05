<?php
// Multi-Language Support Functions

/**
 * Get all active languages
 */
function get_active_languages() {
    global $conn;
    
    // Check if languages table exists first
    $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'languages'");
    if (mysqli_num_rows($table_check) === 0) {
        // Return default English if table doesn't exist
        return [
            [
                'id' => 1,
                'code' => 'en',
                'name' => 'English',
                'native_name' => 'English',
                'flag_icon' => '🇺🇸',
                'is_active' => 1,
                'sort_order' => 1
            ]
        ];
    }
    
    $query = "SELECT * FROM languages WHERE is_active = 1 ORDER BY sort_order ASC";
    $result = mysqli_query($conn, $query);
    
    $languages = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $languages[] = $row;
    }
    
    return $languages;
}

/**
 * Get current user language preference
 */
function get_current_language() {
    // Priority: URL parameter > User preference > Browser detection > Default setting
    
    // 1. Check URL parameter
    if (isset($_GET['lang']) && !empty($_GET['lang'])) {
        $lang_code = clean_input($_GET['lang']);
        if (is_valid_language($lang_code)) {
            // Save to session and user preferences if logged in
            $_SESSION['current_language'] = $lang_code;
            if (is_logged_in()) {
                save_user_language_preference($_SESSION['user_id'], $lang_code);
            }
            return $lang_code;
        }
    }
    
    // 2. Check session
    if (isset($_SESSION['current_language']) && is_valid_language($_SESSION['current_language'])) {
        return $_SESSION['current_language'];
    }
    
    // 3. Check user preferences if logged in
    if (is_logged_in()) {
        $user_lang = get_user_language_preference($_SESSION['user_id']);
        if ($user_lang && is_valid_language($user_lang)) {
            $_SESSION['current_language'] = $user_lang;
            return $user_lang;
        }
    }
    
    // 4. Auto-detect from browser if enabled
    if (get_site_setting('auto_detect_language', '1') == '1') {
        $browser_lang = detect_browser_language();
        if ($browser_lang && is_valid_language($browser_lang)) {
            $_SESSION['current_language'] = $browser_lang;
            return $browser_lang;
        }
    }
    
    // 5. Fall back to default language
    $default_lang = get_site_setting('default_language', 'en');
    $_SESSION['current_language'] = $default_lang;
    return $default_lang;
}

/**
 * Check if language code is valid
 */
function is_valid_language($lang_code) {
    global $conn;
    
    // Check if languages table exists first
    $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'languages'");
    if (mysqli_num_rows($table_check) === 0) {
        // Table doesn't exist, only allow English as fallback
        return $lang_code === 'en';
    }
    
    $query = "SELECT id FROM languages WHERE code = ? AND is_active = 1";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 's', $lang_code);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    return mysqli_num_rows($result) > 0;
}

/**
 * Detect browser language
 */
function detect_browser_language() {
    if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        $langs = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
        $primary_lang = substr($langs[0], 0, 2);
        
        // Map common browser language codes to our system
        $lang_mapping = [
            'en' => 'en',
            'ur' => 'ur',
            'hi' => 'hi',
            'zh' => 'zh',
            'ps' => 'ps'
        ];
        
        return $lang_mapping[$primary_lang] ?? null;
    }
    
    return null;
}

/**
 * Save user language preference
 */
function save_user_language_preference($user_id, $language_code) {
    global $conn;
    
    // Check if table exists first
    $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'user_language_preferences'");
    if (mysqli_num_rows($table_check) === 0) {
        return; // Table doesn't exist yet, skip saving
    }
    
    $query = "INSERT INTO user_language_preferences (user_id, language_code) 
              VALUES (?, ?) 
              ON DUPLICATE KEY UPDATE language_code = VALUES(language_code), updated_at = CURRENT_TIMESTAMP";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'is', $user_id, $language_code);
    mysqli_stmt_execute($stmt);
}

/**
 * Get user language preference
 */
function get_user_language_preference($user_id) {
    global $conn;
    
    // Check if table exists first
    $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'user_language_preferences'");
    if (mysqli_num_rows($table_check) === 0) {
        return null; // Table doesn't exist yet
    }
    
    $query = "SELECT language_code FROM user_language_preferences WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        return $row['language_code'];
    }
    
    return null;
}

/**
 * Get translated text based on current language
 */
function get_translation($text, $language_code = null) {
    global $conn;
    
    if ($language_code === null) {
        $language_code = get_current_language();
    }
    
    // If the requested language is English, return original text
    if ($language_code === 'en') {
        return $text;
    }
    
    // Check if translations table exists
    $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'translations'");
    if (mysqli_num_rows($table_check) === 0) {
        return $text; // Table doesn't exist, return original
    }
    
    // Use the text as translation key (or you can use a separate key)
    $translation_key = $text;
    
    // Try to get translation from database
    $query = "SELECT translated_text FROM translations WHERE translation_key = ? AND language_code = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'ss', $translation_key, $language_code);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        return $row['translated_text'];
    }
    
    // If no translation found, return original text
    return $text;
}

/**
 * Helper function to translate text with a key
 * This is more efficient for static text that uses predefined keys
 */
function t($key, $default = null, $language_code = null) {
    global $conn;
    
    if ($language_code === null) {
        $language_code = get_current_language();
    }
    
    // If the requested language is English, return default or key
    if ($language_code === 'en') {
        return $default !== null ? $default : $key;
    }
    
    // Check if translations table exists
    $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'translations'");
    if (mysqli_num_rows($table_check) === 0) {
        return $default !== null ? $default : $key;
    }
    
    // Try to get translation from database
    $query = "SELECT translated_text FROM translations WHERE translation_key = ? AND language_code = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'ss', $key, $language_code);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        return $row['translated_text'];
    }
    
    // If no translation found, return default or key
    return $default !== null ? $default : $key;
}

/**
 * Get language-specific news title
 */
function get_news_title($news_item, $language_code = null) {
    if ($language_code === null) {
        $language_code = get_current_language();
    }
    
    $title_field = 'title';
    if ($language_code !== 'en' && !empty($news_item['title_' . $language_code])) {
        $title_field = 'title_' . $language_code;
    }
    
    return $news_item[$title_field] ?? $news_item['title'] ?? '';
}

/**
 * Get language-specific news content
 */
function get_news_content($news_item, $language_code = null) {
    if ($language_code === null) {
        $language_code = get_current_language();
    }
    
    $content_field = 'content';
    if ($language_code !== 'en' && !empty($news_item['content_' . $language_code])) {
        $content_field = 'content_' . $language_code;
    }
    
    return $news_item[$content_field] ?? $news_item['content'] ?? '';
}

/**
 * Get language-specific news summary
 */
function get_news_summary($news_item, $language_code = null) {
    if ($language_code === null) {
        $language_code = get_current_language();
    }
    
    $summary_field = 'summary';
    if ($language_code !== 'en' && !empty($news_item['summary_' . $language_code])) {
        $summary_field = 'summary_' . $language_code;
    }
    
    return $news_item[$summary_field] ?? $news_item['summary'] ?? '';
}

/**
 * Get language-specific category name
 */
function get_category_name($category, $language_code = null) {
    if ($language_code === null) {
        $language_code = get_current_language();
    }
    
    $name_field = 'name';
    if ($language_code !== 'en' && !empty($category['name_' . $language_code])) {
        $name_field = 'name_' . $language_code;
    }
    
    return $category[$name_field] ?? $category['name'] ?? '';
}

/**
 * Get language URL for current page
 */
function get_language_url($language_code) {
    $current_url = $_SERVER['REQUEST_URI'];
    
    // Remove existing lang parameter
    $url_parts = parse_url($current_url);
    parse_str($url_parts['query'] ?? '', $query_params);
    unset($query_params['lang']);
    
    // Add new lang parameter
    $query_params['lang'] = $language_code;
    
    $new_query = http_build_query($query_params);
    $new_url = $url_parts['path'];
    
    if (!empty($new_query)) {
        $new_url .= '?' . $new_query;
    }
    
    return $new_url;
}

/**
 * Generate hreflang tags for SEO
 */
function generate_hreflang_tags() {
    global $conn;
    
    $languages = get_active_languages();
    $current_url = strtok($_SERVER['REQUEST_URI'], '?'); // Remove query parameters
    
    $hreflang_tags = '';
    
    foreach ($languages as $lang) {
        $lang_url = get_language_url($lang['code']);
        $hreflang_tags .= '<link rel="alternate" hreflang="' . $lang['code'] . '" href="' . $lang_url . '">' . "\n";
    }
    
    // Add x-default for international users
    $default_lang = get_site_setting('default_language', 'en');
    $default_url = get_language_url($default_lang);
    $hreflang_tags .= '<link rel="alternate" hreflang="x-default" href="' . $default_url . '">' . "\n";
    
    return $hreflang_tags;
}

/**
 * Get site setting
 */
function get_site_setting($key, $default = null) {
    global $conn;
    
    static $settings = [];
    
    if (empty($settings)) {
        $query = "SELECT setting_key, setting_value FROM site_settings";
        $result = mysqli_query($conn, $query);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
    }
    
    return $settings[$key] ?? $default;
}

/**
 * Update site setting
 */
function update_setting($key, $value) {
    global $conn;
    
    $query = "INSERT INTO site_settings (setting_key, setting_value) 
              VALUES (?, ?) 
              ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = CURRENT_TIMESTAMP";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'ss', $key, $value);
    mysqli_stmt_execute($stmt);
}
?>

<?php
// Minimal Multi-Language Support Functions (Works without database until setup is complete)

/**
 * Get current user language (minimal version)
 */
function get_current_language_minimal() {
    // Priority: URL parameter > Session > Browser detection > Default
    
    // 1. Check URL parameter
    if (isset($_GET['lang']) && !empty($_GET['lang'])) {
        $lang_code = clean_input($_GET['lang']);
        if (in_array($lang_code, ['en', 'ur', 'hi', 'zh', 'ps'])) {
            $_SESSION['current_language'] = $lang_code;
            return $lang_code;
        }
    }
    
    // 2. Check session
    if (isset($_SESSION['current_language']) && in_array($_SESSION['current_language'], ['en', 'ur', 'hi', 'zh', 'ps'])) {
        return $_SESSION['current_language'];
    }
    
    // 3. Auto-detect from browser
    if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        $langs = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
        $primary_lang = substr($langs[0], 0, 2);
        
        $lang_mapping = [
            'en' => 'en',
            'ur' => 'ur', 
            'hi' => 'hi',
            'zh' => 'zh',
            'ps' => 'ps'
        ];
        
        if (isset($lang_mapping[$primary_lang])) {
            $_SESSION['current_language'] = $lang_mapping[$primary_lang];
            return $lang_mapping[$primary_lang];
        }
    }
    
    // 4. Fall back to English
    $_SESSION['current_language'] = 'en';
    return 'en';
}

/**
 * Get active languages (minimal version)
 */
function get_active_languages_minimal() {
    return [
        [
            'id' => 1,
            'code' => 'en',
            'name' => 'English',
            'native_name' => 'English',
            'flag_icon' => '🇺🇸',
            'is_active' => 1,
            'sort_order' => 1
        ],
        [
            'id' => 2,
            'code' => 'ur',
            'name' => 'Urdu',
            'native_name' => 'اردو',
            'flag_icon' => '🇵🇰',
            'is_active' => 1,
            'sort_order' => 2
        ],
        [
            'id' => 3,
            'code' => 'hi',
            'name' => 'Hindi',
            'native_name' => 'हिन्दी',
            'flag_icon' => '🇮🇳',
            'is_active' => 1,
            'sort_order' => 3
        ],
        [
            'id' => 4,
            'code' => 'zh',
            'name' => 'Chinese',
            'native_name' => '中文',
            'flag_icon' => '🇨🇳',
            'is_active' => 1,
            'sort_order' => 4
        ],
        [
            'id' => 5,
            'code' => 'ps',
            'name' => 'Pashto',
            'native_name' => 'پښتو',
            'flag_icon' => '🇦🇫',
            'is_active' => 1,
            'sort_order' => 5
        ]
    ];
}

/**
 * Get setting (minimal version)
 */
function get_setting_minimal($key, $default = null) {
    $defaults = [
        'default_language' => 'en',
        'enable_language_switcher' => '1',
        'show_language_flags' => '1',
        'auto_detect_language' => '1',
        'multilingual_seo' => '1'
    ];
    return $defaults[$key] ?? $default;
}

/**
 * Get language URL (minimal version)
 */
function get_language_url_minimal($language_code) {
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
 * Generate hreflang tags (minimal version)
 */
function generate_hreflang_tags_minimal() {
    $languages = get_active_languages_minimal();
    $current_url = strtok($_SERVER['REQUEST_URI'], '?');
    
    $hreflang_tags = '';
    
    foreach ($languages as $lang) {
        $lang_url = get_language_url_minimal($lang['code']);
        $hreflang_tags .= '<link rel="alternate" hreflang="' . $lang['code'] . '" href="' . $lang_url . '">' . "\n";
    }
    
    // Add x-default
    $default_url = get_language_url_minimal('en');
    $hreflang_tags .= '<link rel="alternate" hreflang="x-default" href="' . $default_url . '">' . "\n";
    
    return $hreflang_tags;
}

/**
 * Get news title (minimal version)
 */
function get_news_title_minimal($news_item, $language_code = null) {
    if ($language_code === null) {
        $language_code = get_current_language_minimal();
    }
    
    $title_field = 'title';
    if ($language_code !== 'en' && !empty($news_item['title_' . $language_code])) {
        $title_field = 'title_' . $language_code;
    }
    
    return $news_item[$title_field] ?? $news_item['title'] ?? '';
}

/**
 * Get news content (minimal version)
 */
function get_news_content_minimal($news_item, $language_code = null) {
    if ($language_code === null) {
        $language_code = get_current_language_minimal();
    }
    
    $content_field = 'content';
    if ($language_code !== 'en' && !empty($news_item['content_' . $language_code])) {
        $content_field = 'content_' . $language_code;
    }
    
    return $news_item[$content_field] ?? $news_item['content'] ?? '';
}

// Helper function (if not already defined)
if (!function_exists('clean_input')) {
    function clean_input($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }
}
?>

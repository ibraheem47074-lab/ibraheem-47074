<?php
/**
 * HTML Encoding Helper Functions
 * Properly handles HTML encoding to prevent &#039; and other encoding issues
 */

/**
 * Safely display text with proper HTML encoding
 * This prevents double encoding and handles apostrophes correctly
 */
function safe_html_display($text) {
    if (empty($text)) {
        return $text;
    }
    
    // First decode any existing HTML entities (prevents double encoding)
    $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
    
    // Then encode for safe HTML display
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

/**
 * Clean text for database storage
 * Removes problematic encoding before saving
 */
function clean_text_for_db($text) {
    if (empty($text)) {
        return $text;
    }
    
    // Decode HTML entities to plain text
    $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
    
    // Strip any remaining HTML tags
    $text = strip_tags($text);
    
    // Trim whitespace
    $text = trim($text);
    
    return $text;
}

/**
 * Fix existing encoded text in database
 * Use this to clean up data that's already encoded
 */
function fix_encoded_text($text) {
    if (empty($text)) {
        return $text;
    }
    
    // Multiple decode passes to handle double encoding
    $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
    $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
    
    // Clean up any remaining HTML entities
    $text = preg_replace('/&#[0-9]+;/', '', $text);
    
    return $text;
}

/**
 * Get news title with proper encoding
 * Replacement for get_news_title() that handles encoding correctly
 */
function get_news_title_safe($news_item, $language_code = null) {
    if ($language_code === null) {
        $language_code = get_current_language();
    }
    
    $title_field = 'title';
    if ($language_code !== 'en' && !empty($news_item['title_' . $language_code])) {
        $title_field = 'title_' . $language_code;
    }
    
    $title = $news_item[$title_field] ?? $news_item['title'] ?? '';
    
    // Fix any encoding issues before returning
    return fix_encoded_text($title);
}

/**
 * Get news content with proper encoding
 * Replacement for get_news_content() that handles encoding correctly
 */
function get_news_content_safe($news_item, $language_code = null) {
    if ($language_code === null) {
        $language_code = get_current_language();
    }
    
    $content_field = 'content';
    if ($language_code !== 'en' && !empty($news_item['content_' . $language_code])) {
        $content_field = 'content_' . $language_code;
    }
    
    $content = $news_item[$content_field] ?? $news_item['content'] ?? '';
    
    // Fix any encoding issues before returning
    return fix_encoded_text($content);
}

/**
 * Get news summary with proper encoding
 * Replacement for get_news_summary() that handles encoding correctly
 */
function get_news_summary_safe($news_item, $language_code = null) {
    if ($language_code === null) {
        $language_code = get_current_language();
    }
    
    $summary_field = 'summary';
    if ($language_code !== 'en' && !empty($news_item['summary_' . $language_code])) {
        $summary_field = 'summary_' . $language_code;
    }
    
    $summary = $news_item[$summary_field] ?? $news_item['summary'] ?? '';
    
    // Fix any encoding issues before returning
    return fix_encoded_text($summary);
}

/**
 * Display news title with proper HTML encoding
 * Use this in templates instead of htmlspecialchars(get_news_title())
 */
function display_news_title($news_item, $language_code = null) {
    $title = get_news_title_safe($news_item, $language_code);
    return safe_html_display($title);
}

/**
 * Display news content with proper HTML encoding
 * Use this in templates for content display
 */
function display_news_content($news_item, $language_code = null) {
    $content = get_news_content_safe($news_item, $language_code);
    return safe_html_display($content);
}

/**
 * Display news summary with proper HTML encoding
 * Use this in templates for summary display
 */
function display_news_summary($news_item, $language_code = null) {
    $summary = get_news_summary_safe($news_item, $language_code);
    return safe_html_display($summary);
}

/**
 * Fix apostrophes in text (common issue)
 * Specifically handles &#039; encoding
 */
function fix_apostrophes($text) {
    if (empty($text)) {
        return $text;
    }
    
    // Replace common apostrophe encodings
    $replacements = [
        '&#039;' => "'",
        '&#x27;' => "'",
        '&apos;' => "'",
        '&#39;' => "'"
    ];
    
    foreach ($replacements as $encoded => $decoded) {
        $text = str_replace($encoded, $decoded, $text);
    }
    
    return $text;
}

/**
 * Clean RSS feed content
 * Use when importing from RSS feeds to prevent encoding issues
 */
function clean_rss_content($content) {
    if (empty($content)) {
        return $content;
    }
    
    // Decode HTML entities
    $content = html_entity_decode($content, ENT_QUOTES, 'UTF-8');
    
    // Fix apostrophes
    $content = fix_apostrophes($content);
    
    // Clean up HTML
    $content = strip_tags($content, '<p><br><strong><em><a><ul><ol><li><h1><h2><h3><h4><h5><h6>');
    
    return trim($content);
}

?>

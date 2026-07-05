<?php
// Fix path for includes when called from different directories
$basePath = dirname(__DIR__) . '/';
require_once $basePath . 'config/database.php';
require_once $basePath . 'config/helpers.php';
require_once $basePath . 'includes/language_functions.php';

// Get current language and all active languages
$current_lang = get_current_language();
$languages = get_active_languages();
$show_flags = get_site_setting('show_language_flags', '1') == '1';
$switcher_enabled = get_site_setting('enable_language_switcher', '1') == '1';

if (!$switcher_enabled || empty($languages)) {
    return;
}
?>

<div class="language-switcher">
    <div class="dropdown">
        <button class="btn btn-sm btn-outline-secondary dropdown-toggle d-flex align-items-center" 
                type="button" 
                id="languageDropdown" 
                data-bs-toggle="dropdown" 
                aria-expanded="false">
            <?php if ($show_flags): ?>
                <span class="me-1"><?php echo get_flag_by_code($current_lang); ?></span>
            <?php endif; ?>
            <span><?php echo get_language_name($current_lang); ?></span>
        </button>
        
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="languageDropdown">
            <?php foreach ($languages as $lang): ?>
                <?php if ($lang['code'] !== $current_lang): ?>
                    <li>
                        <a class="dropdown-item d-flex align-items-center" 
                           href="<?php echo htmlspecialchars(get_language_url($lang['code'])); ?>">
                            <?php if ($show_flags): ?>
                                <span class="me-2"><?php echo $lang['flag_icon']; ?></span>
                            <?php endif; ?>
                            <span><?php echo htmlspecialchars($lang['native_name']); ?></span>
                            <?php if ($lang['name'] !== $lang['native_name']): ?>
                                <small class="text-muted ms-2">(<?php echo htmlspecialchars($lang['name']); ?>)</small>
                            <?php endif; ?>
                        </a>
                    </li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ul>
    </div>
</div>

<style>
.language-switcher {
    position: relative;
    z-index: 1000;
}

.language-switcher .dropdown-toggle {
    border: 1px solid #dee2e6;
    background: white;
    color: #495057;
    font-size: 0.875rem;
    padding: 0.375rem 0.75rem;
    transition: all 0.2s ease;
}

.language-switcher .dropdown-toggle:hover {
    background: #f8f9fa;
    border-color: #adb5bd;
}

.language-switcher .dropdown-menu {
    min-width: 180px;
    border: 1px solid #dee2e6;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.language-switcher .dropdown-item {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
    color: #495057;
    text-decoration: none;
    transition: all 0.2s ease;
}

.language-switcher .dropdown-item:hover {
    background: #f8f9fa;
    color: #212529;
}

.language-switcher .dropdown-item.active {
    background: #007bff;
    color: white;
}

/* Mobile responsive */
@media (max-width: 768px) {
    .language-switcher .dropdown-menu {
        min-width: 150px;
    }
    
    .language-switcher .dropdown-item {
        padding: 0.375rem 0.75rem;
        font-size: 0.8rem;
    }
}

/* RTL support for Arabic/Urdu */
[dir="rtl"] .language-switcher .dropdown-menu {
    right: auto;
    left: 0;
}

[dir="rtl"] .language-switcher .dropdown-item {
    text-align: right;
}

[dir="rtl"] .language-switcher .me-1,
[dir="rtl"] .language-switcher .me-2 {
    margin-left: 0.25rem;
    margin-right: 0;
}

[dir="rtl"] .language-switcher .ms-2 {
    margin-right: 0.5rem;
    margin-left: 0;
}
</style>

<?php
/**
 * Helper function to get flag emoji by language code
 */
function get_flag_by_code($code) {
    $flags = [
        'en' => '🇺🇸',
        'ur' => '🇵🇰',
        'hi' => '🇮🇳',
        'zh' => '🇨🇳',
        'ps' => '🇦🇫'
    ];
    
    return $flags[$code] ?? '🌐';
}

/**
 * Helper function to get language name by code
 */
function get_language_name($code) {
    global $conn;
    
    static $language_names = [];
    
    if (empty($language_names)) {
        // Check if languages table exists first
        $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'languages'");
        if (mysqli_num_rows($table_check) === 0) {
            // Return default if table doesn't exist
            return ['name' => 'English', 'native_name' => 'English'];
        }
        
        $query = "SELECT code, name, native_name FROM languages WHERE is_active = 1";
        $result = mysqli_query($conn, $query);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $language_names[$row['code']] = $row;
        }
    }
    
    return $language_names[$code]['native_name'] ?? $language_names[$code]['name'] ?? $code;
}
?>

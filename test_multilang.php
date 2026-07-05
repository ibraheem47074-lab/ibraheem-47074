<?php
// Test file for multi-language system
require_once 'config/database.php';
require_once 'config/helpers.php';
require_once 'includes/language_functions.php';

echo "<h1>Multi-Language System Test</h1>";

echo "<h2>1. Database Connection</h2>";
if ($conn) {
    echo "<div style='color: green;'>✓ Database connection successful</div>";
} else {
    echo "<div style='color: red;'>✗ Database connection failed</div>";
}

echo "<h2>2. Language Functions Test</h2>";

// Test get_active_languages
$languages = get_active_languages();
echo "<div>Active languages found: " . count($languages) . "</div>";
foreach ($languages as $lang) {
    echo "<div style='margin-left: 20px;'>→ " . $lang['flag_icon'] . " " . $lang['name'] . " (" . $lang['code'] . ")</div>";
}

// Test get_current_language
$current_lang = get_current_language();
echo "<div>Current language: $current_lang</div>";

// Test get_setting
$switcher_enabled = get_site_setting('enable_language_switcher');
echo "<div>Language switcher enabled: $switcher_enabled</div>";

echo "<h2>3. Header Include Test</h2>";
echo "<div>Testing header include...</div>";

// Capture header output
ob_start();
include 'includes/header.php';
$header_output = ob_get_clean();

if (strpos($header_output, '<!DOCTYPE html') !== false) {
    echo "<div style='color: green;'>✓ Header includes successfully</div>";
} else {
    echo "<div style='color: red;'>✗ Header include failed</div>";
}

echo "<h2>4. Language Switcher Test</h2>";
ob_start();
include 'components/language_switcher.php';
$switcher_output = ob_get_clean();

if (strpos($switcher_output, 'language-switcher') !== false) {
    echo "<div style='color: green;'>✓ Language switcher loads successfully</div>";
} else {
    echo "<div style='color: red;'>✗ Language switcher failed to load</div>";
}

echo "<h2>5. Database Tables Check</h2>";
$tables = ['languages', 'user_language_preferences', 'site_settings'];

foreach ($tables as $table) {
    $result = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
    if (mysqli_num_rows($result) > 0) {
        echo "<div style='color: green;'>✓ Table '$table' exists</div>";
    } else {
        echo "<div style='color: orange;'>⚠ Table '$table' doesn't exist (run setup script)</div>";
    }
}

echo "<h2>6. News Table Language Columns</h2>";
$columns = ['language_code', 'title_ur', 'title_hi', 'title_zh', 'title_ps'];
foreach ($columns as $column) {
    $result = mysqli_query($conn, "SHOW COLUMNS FROM news LIKE '$column'");
    if (mysqli_num_rows($result) > 0) {
        echo "<div style='color: green;'>✓ Column '$column' exists</div>";
    } else {
        echo "<div style='color: orange;'>⚠ Column '$column' doesn't exist (run setup script)</div>";
    }
}

echo "<h2>Test Complete</h2>";
echo "<div style='background: #f0f8ff; padding: 15px; border-radius: 4px; margin: 20px 0;'>";
echo "<strong>Next Steps:</strong><br>";
echo "1. If you see orange warnings above, run the setup script:<br>";
echo "   <a href='run_multilang_setup.php'>run_multilang_setup.php</a><br><br>";
echo "2. Test the main website: <a href='index.php'>index.php</a><br><br>";
echo "3. Test admin panel: <a href='admin/'>admin/</a>";
echo "</div>";

echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h1, h2 { color: #333; }
div { margin: 5px 0; padding: 5px; }
</style>";
?>

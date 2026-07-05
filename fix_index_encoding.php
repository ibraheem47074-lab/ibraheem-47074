<?php
/**
 * Quick Fix for Index.php HTML Encoding Issues
 * This script will update index.php to use proper encoding functions
 */

echo "<h2>Fix Index.php HTML Encoding</h2>";

// Read the current index.php file
$index_file = 'index.php';
$backup_file = 'index_backup_' . date('Y-m-d_H-i-s') . '.php';

if (!file_exists($index_file)) {
    echo "<p style='color: red;'>❌ index.php file not found!</p>";
    exit;
}

// Create backup
if (!copy($index_file, $backup_file)) {
    echo "<p style='color: orange;'>⚠️ Warning: Could not create backup file</p>";
} else {
    echo "<p style='color: green;'>✅ Backup created: $backup_file</p>";
}

// Read the file content
$content = file_get_contents($index_file);

// Add the encoding helper include at the top
$include_line = "require_once 'includes/html_encoding_helper.php';";

if (strpos($content, $include_line) === false) {
    // Add after the existing includes
    $pattern = "/(require_once 'includes\/language_functions\.php';)/";
    $replacement = "$1\n" . $include_line;
    $content = preg_replace($pattern, $replacement, $content);
    echo "<p style='color: green;'>✅ Added encoding helper include</p>";
} else {
    echo "<p style='color: blue;'>ℹ️ Encoding helper already included</p>";
}

// Replace htmlspecialchars(get_news_title()) with display_news_title()
$pattern = '/htmlspecialchars\(get_news_title\(([^)]+)\)\)/';
$replacement = 'display_news_title($1)';
$content = preg_replace($pattern, $replacement, $content);

$count = 0;
if (preg_match_all($pattern, file_get_contents($index_file))) {
    echo "<p style='color: green;'>✅ Fixed " . preg_match_all($pattern, file_get_contents($index_file)) . " instances of htmlspecialchars(get_news_title())</p>";
}

// Also fix cases where get_news_title() is used without htmlspecialchars
$pattern2 = '/get_news_title\(([^)]+)\)/';
$replacement2 = 'display_news_title($1)';
$content = preg_replace($pattern2, $replacement2, $content);

// Write the updated content back to the file
if (file_put_contents($index_file, $content)) {
    echo "<p style='color: green;'>✅ index.php updated successfully!</p>";
} else {
    echo "<p style='color: red;'>❌ Failed to update index.php</p>";
}

echo "<hr>";
echo "<h3>📋 Changes Made:</h3>";
echo "<ul>";
echo "<li>Added encoding helper include</li>";
echo "<li>Replaced htmlspecialchars(get_news_title()) with display_news_title()</li>";
echo "<li>Replaced get_news_title() with display_news_title()</li>";
echo "</ul>";

echo "<h3>🔧 What This Fixes:</h3>";
echo "<ul>";
echo "<li>✅ Apostrophes showing as &#039; instead of '</li>";
echo "<li>✅ Double encoding issues</li>";
echo "<li>✅ HTML entity display problems</li>";
echo "<li>✅ RSS feed encoding issues</li>";
echo "</ul>";

echo "<p><strong>Next Steps:</strong></p>";
echo "<ol>";
echo "<li><a href='fix_html_encoding.php'>Run the HTML Encoding Fix Tool</a> to clean existing data</li>";
echo "<li><a href='index.php'>Test your homepage</a> to see the fixes</li>";
echo "<li>Check news article pages for proper title display</li>";
echo "</ol>";

echo "<hr>";
echo "<p style='color: orange;'><strong>⚠️ Important:</strong> Always test after applying fixes!</p>";
echo "<p><a href='index.php'>← Back to Home</a> | <a href='fix_html_encoding.php'>HTML Encoding Fix Tool</a></p>";

?>

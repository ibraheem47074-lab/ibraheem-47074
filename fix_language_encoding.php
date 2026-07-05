<?php
/**
 * Fix Language Encoding
 * Re-inserts languages with proper UTF-8 characters
 */

require_once 'config/database.php';

echo "<h2>Fixing Language Encoding...</h2>";
echo "<meta charset=\"UTF-8\">";

// Clear existing data
mysqli_query($conn, "TRUNCATE TABLE languages");
echo "<p>Cleared existing language data</p>";

// Insert with correct UTF-8 encoding
$languages = [
    ['en', 'English', 'English', '🇺🇸', 1, 1],
    ['ur', 'Urdu', 'اردو', '🇵🇰', 1, 2],
    ['hi', 'Hindi', 'हिन्दी', '🇮🇳', 1, 3],
    ['zh', 'Chinese', '中文', '🇨🇳', 1, 4],
    ['ps', 'Pashto', 'پښتو', '🇦🇫', 1, 5]
];

$stmt = mysqli_prepare($conn, "INSERT INTO languages (code, name, native_name, flag_icon, is_active, sort_order) VALUES (?, ?, ?, ?, ?, ?)");

foreach ($languages as $lang) {
    mysqli_stmt_bind_param($stmt, 'ssssii', $lang[0], $lang[1], $lang[2], $lang[3], $lang[4], $lang[5]);
    if (mysqli_stmt_execute($stmt)) {
        echo "<p style='color: green;'>✓ Added: {$lang[1]} - {$lang[2]} {$lang[3]}</p>";
    } else {
        echo "<p style='color: red;'>✗ Error: " . mysqli_stmt_error($stmt) . "</p>";
    }
}

echo "<hr><h3>Done!</h3>";
echo "<p><a href='admin/manage_languages.php'>View Languages</a></p>";
echo "<p><strong>Delete this file after use.</strong></p>";

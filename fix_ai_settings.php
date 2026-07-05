<?php
/**
 * Quick Fix for AI Settings Error
 * This will immediately fix the "Unknown column 'value'" error
 */

require_once __DIR__ . '/config/database.php';

echo "<h2>🔧 AI Settings Error Fix</h2>";

// Step 1: Create ai_settings table if it doesn't exist
$createTable = "
CREATE TABLE IF NOT EXISTS `ai_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL UNIQUE,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('string', 'number', 'boolean', 'json') DEFAULT 'string',
  `description` text DEFAULT NULL,
  `is_encrypted` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_setting` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if (mysqli_query($conn, $createTable)) {
    echo "✅ ai_settings table created/verified<br>";
} else {
    echo "❌ Error with ai_settings table: " . mysqli_error($conn) . "<br>";
}

// Step 2: Insert essential settings that are needed
$essentialSettings = [
    'ai_image_generation_enabled' => 'true',
    'ai_default_provider' => 'openai',
    'openai_api_key' => '',
    'stability_api_key' => '',
    'replicate_api_key' => ''
];

echo "<h3>Inserting Essential Settings:</h3>";
foreach ($essentialSettings as $key => $value) {
    $insertQuery = "INSERT IGNORE INTO ai_settings (setting_key, setting_value) VALUES (?, ?)";
    $stmt = mysqli_prepare($conn, $insertQuery);
    mysqli_stmt_bind_param($stmt, 'ss', $key, $value);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "✅ Setting '$key' ready<br>";
    } else {
        echo "❌ Error with '$key': " . mysqli_stmt_error($stmt) . "<br>";
    }
    mysqli_stmt_close($stmt);
}

// Step 3: Verify the fix
echo "<h3>Verification:</h3>";
$verifyQuery = "SELECT setting_key, setting_value FROM ai_settings WHERE setting_key IN ('openai_api_key', 'ai_image_generation_enabled')";
$result = mysqli_query($conn, $verifyQuery);

if (mysqli_num_rows($result) > 0) {
    echo "✅ Database is ready! The AI settings table is working correctly.<br>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "- {$row['setting_key']}: " . ($row['setting_value'] ?: '(empty)') . "<br>";
    }
} else {
    echo "❌ Something went wrong. Please check the errors above.<br>";
}

echo "<h2>🎉 Fix Complete!</h2>";
echo "<p>You can now access the AI Image Management system:</p>";
echo "<ul>";
echo "<li><a href='admin/ai_image_management.php'>AI Image Management</a></li>";
echo "<li><a href='admin/ai_image_settings.php'>AI Settings</a></li>";
echo "<li><a href='admin/ai_image_dashboard.php'>AI Dashboard</a></li>";
echo "</ul>";
echo "<p><strong>Note:</strong> You'll need to configure your API keys in the AI Settings page to start generating images.</p>";
?>

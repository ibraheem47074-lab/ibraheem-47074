 <?php
/**
 * AI Image System Test
 * Complete test of all AI Image Generation components
 */

require_once __DIR__ . '/config/database.php';

echo "<h2>🧪 AI Image System - Complete Test</h2>";

// Test 1: Database Setup
echo "<h3>1. Database Setup Test</h3>";
$tables = ['ai_settings', 'ai_image_logs', 'ai_image_templates'];
$allTablesExist = true;

foreach ($tables as $table) {
    $result = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
    if (mysqli_num_rows($result) > 0) {
        echo "✅ Table '$table' exists<br>";
    } else {
        echo "❌ Table '$table' missing<br>";
        $allTablesExist = false;
    }
}

// Test 2: News Table Columns
echo "<h3>2. News Table Columns Test</h3>";
$requiredColumns = ['image_type', 'ai_image_status', 'ai_image_error', 'ai_image_metadata'];
$allColumnsExist = true;

foreach ($requiredColumns as $column) {
    $result = mysqli_query($conn, "SHOW COLUMNS FROM news LIKE '$column'");
    if (mysqli_num_rows($result) > 0) {
        echo "✅ Column '$column' exists<br>";
    } else {
        echo "❌ Column '$column' missing<br>";
        $allColumnsExist = false;
    }
}

// Test 3: Settings Configuration
echo "<h3>3. Settings Configuration Test</h3>";
if ($allTablesExist) {
    $settingsQuery = "SELECT setting_key, setting_value FROM ai_settings LIMIT 5";
    $result = mysqli_query($conn, $settingsQuery);
    
    if (mysqli_num_rows($result) > 0) {
        echo "✅ Settings table has data:<br>";
        while ($row = mysqli_fetch_assoc($result)) {
            echo "- {$row['setting_key']}: " . ($row['setting_value'] ?: '(empty)') . "<br>";
        }
    } else {
        echo "❌ Settings table is empty<br>";
    }
} else {
    echo "⚠️ Skipping settings test - tables missing<br>";
}

// Test 4: File Structure
echo "<h3>4. File Structure Test</h3>";
$requiredFiles = [
    'includes/ai_image_generator.php',
    'includes/smart_prompt_generator.php',
    'admin/ai_image_management.php',
    'admin/ai_image_dashboard.php',
    'admin/ai_image_queue.php',
    'admin/ai_image_settings.php',
    'admin/ai_image_logs.php',
    'admin/ai_image_edit.php'
];

$allFilesExist = true;
foreach ($requiredFiles as $file) {
    if (file_exists(__DIR__ . '/' . $file)) {
        echo "✅ $file exists<br>";
    } else {
        echo "❌ $file missing<br>";
        $allFilesExist = false;
    }
}

// Test 5: Class Instantiation
echo "<h3>5. Class Instantiation Test</h3>";
if ($allFilesExist && $allTablesExist) {
    try {
        require_once __DIR__ . '/includes/ai_image_generator.php';
        require_once __DIR__ . '/includes/smart_prompt_generator.php';
        
        $aiGenerator = new AIImageGenerator($conn);
        $promptGenerator = new SmartPromptGenerator($conn);
        
        echo "✅ AI Image Generator class instantiated<br>";
        echo "✅ Smart Prompt Generator class instantiated<br>";
        
        // Test default settings
        $defaultProvider = $aiGenerator->getDefaultProvider();
        echo "✅ Default provider: $defaultProvider<br>";
        
    } catch (Exception $e) {
        echo "❌ Class instantiation failed: " . $e->getMessage() . "<br>";
    }
} else {
    echo "⚠️ Skipping class test - files or tables missing<br>";
}

// Test 6: Sample News Article
echo "<h3>6. Sample Data Test</h3>";
$newsQuery = "SELECT id, title, image, image_type, ai_image_status FROM news LIMIT 3";
$result = mysqli_query($conn, $newsQuery);

if (mysqli_num_rows($result) > 0) {
    echo "✅ Sample news articles:<br>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "- ID: {$row['id']} | Title: " . substr($row['title'], 0, 40) . "...<br>";
        echo "  Image: " . ($row['image'] ?: 'None') . "<br>";
        echo "  Type: " . ($row['image_type'] ?: 'Not set') . "<br>";
        echo "  AI Status: " . ($row['ai_image_status'] ?: 'Not set') . "<br><br>";
    }
} else {
    echo "ℹ️ No news articles found - this is normal for fresh installation<br>";
}

// Overall Status
echo "<h2>🎯 Overall Status</h2>";
if ($allTablesExist && $allColumnsExist && $allFilesExist) {
    echo "✅ <strong>AI Image System is READY!</strong><br>";
    echo "<p>Your system is fully configured and ready to generate AI images.</p>";
    
    echo "<h3>🚀 Next Steps:</h3>";
    echo "<ol>";
    echo "<li><a href='admin/ai_image_settings.php'>Configure API Keys</a> - Add your OpenAI/Stability AI keys</li>";
    echo "<li><a href='admin/ai_image_dashboard.php'>View Dashboard</a> - Monitor AI image generation</li>";
    echo "<li><a href='admin/ai_image_queue.php'>Manage Queue</a> - Process articles needing images</li>";
    echo "<li><a href='admin/ai_image_management.php'>Full Management</a> - Complete control panel</li>";
    echo "</ol>";
    
    echo "<h3>🔑 Quick API Key Setup:</h3>";
    echo "<p><strong>OpenAI:</strong> Get key from <a href='https://platform.openai.com/api-keys' target='_blank'>platform.openai.com</a></p>";
    echo "<p><strong>Stability AI:</strong> Get key from <a href='https://platform.stability.ai/' target='_blank'>platform.stability.ai</a></p>";
    
} else {
    echo "⚠️ <strong>System needs setup</strong><br>";
    echo "<p>Please run the setup script to fix missing components:</p>";
    echo "<a href='fix_ai_settings.php' class='btn btn-primary'>🔧 Run Setup Script</a>";
}

echo "<hr>";
echo "<p><small>Test completed at: " . date('Y-m-d H:i:s') . "</small></p>";
?>

<?php
require_once 'config/database.php';

echo "<h1>PK Live News - Multi-Language Setup</h1>";
echo "<p>This script will set up the multi-language functionality for your website.</p>";

// Check if database is accessible
if (!$conn) {
    echo "<div class='alert alert-danger'>Database connection failed!</div>";
    exit;
}

echo "<h2>Step 1: Creating Database Tables</h2>";

// Read and execute the SQL setup
$sql_file = 'database_update_multilang.sql';
if (file_exists($sql_file)) {
    $sql = file_get_contents($sql_file);
    
    // Split SQL into individual statements
    $statements = explode(';', $sql);
    
    $success_count = 0;
    $error_count = 0;
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (empty($statement)) continue;
        
        if (mysqli_query($conn, $statement)) {
            $success_count++;
            echo "<div class='alert alert-success'>✓ Executed: " . htmlspecialchars(substr($statement, 0, 50)) . "...</div>";
        } else {
            $error_count++;
            $error = mysqli_error($conn);
            if (strpos($error, 'already exists') !== false || strpos($error, 'Duplicate') !== false) {
                echo "<div class='alert alert-warning'>⚠ Already exists: " . htmlspecialchars(substr($statement, 0, 50)) . "...</div>";
            } else {
                echo "<div class='alert alert-danger'>✗ Error: " . htmlspecialchars($error) . "</div>";
            }
        }
    }
    
    echo "<h3>Database Setup Complete</h3>";
    echo "<p>✓ $success_count statements executed successfully</p>";
    if ($error_count > 0) {
        echo "<p>⚠ $error_count statements had errors (some may be expected)</p>";
    }
} else {
    echo "<div class='alert alert-danger'>SQL setup file not found!</div>";
}

echo "<h2>Step 2: Verifying Language Setup</h2>";

// Check if languages table exists and has data
$lang_check = mysqli_query($conn, "SELECT COUNT(*) as count FROM languages");
if ($lang_check) {
    $row = mysqli_fetch_assoc($lang_check);
    echo "<div class='alert alert-info'>Found {$row['count']} languages in database</div>";
    
    // Show available languages
    $lang_query = "SELECT * FROM languages ORDER BY sort_order";
    $lang_result = mysqli_query($conn, $lang_query);
    
    echo "<table class='table table-bordered'>";
    echo "<tr><th>Code</th><th>Name</th><th>Native Name</th><th>Flag</th><th>Status</th></tr>";
    
    while ($lang = mysqli_fetch_assoc($lang_result)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($lang['code']) . "</td>";
        echo "<td>" . htmlspecialchars($lang['name']) . "</td>";
        echo "<td>" . htmlspecialchars($lang['native_name']) . "</td>";
        echo "<td>" . htmlspecialchars($lang['flag_icon']) . "</td>";
        echo "<td>" . ($lang['is_active'] ? 'Active' : 'Inactive') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<div class='alert alert-danger'>Languages table not found!</div>";
}

echo "<h2>Step 3: Verifying Site Settings</h2>";

// Check if site settings exist
$settings_check = mysqli_query($conn, "SELECT COUNT(*) as count FROM site_settings");
if ($settings_check) {
    $row = mysqli_fetch_assoc($settings_check);
    echo "<div class='alert alert-info'>Found {$row['count']} site settings</div>";
    
    // Show current settings
    $settings_query = "SELECT * FROM site_settings";
    $settings_result = mysqli_query($conn, $settings_query);
    
    echo "<table class='table table-bordered'>";
    echo "<tr><th>Setting</th><th>Value</th><th>Description</th></tr>";
    
    while ($setting = mysqli_fetch_assoc($settings_result)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($setting['setting_key']) . "</td>";
        echo "<td>" . htmlspecialchars($setting['setting_value']) . "</td>";
        echo "<td>" . htmlspecialchars($setting['description']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<div class='alert alert-danger'>Site settings table not found!</div>";
}

echo "<h2>Step 4: File Structure Verification</h2>";

$required_files = [
    'includes/language_functions.php' => 'Language helper functions',
    'components/language_switcher.php' => 'Language switcher component',
    'admin/manage_languages.php' => 'Language management admin panel',
    'admin/add_news_multilang.php' => 'Multilingual news editor'
];

foreach ($required_files as $file => $description) {
    if (file_exists($file)) {
        echo "<div class='alert alert-success'>✓ $file - $description</div>";
    } else {
        echo "<div class='alert alert-danger'>✗ $file - $description (MISSING)</div>";
    }
}

echo "<h2>Setup Complete!</h2>";
echo "<div class='alert alert-info'>";
echo "<strong>What's been set up:</strong><br>";
echo "• Multi-language database structure<br>";
echo "• Language management system<br>";
echo "• Language switcher for users<br>";
echo "• Multilingual news support<br>";
echo "• SEO-friendly hreflang tags<br>";
echo "<br>";
echo "<strong>Next steps:</strong><br>";
echo "1. Visit <a href='admin/manage_languages.php'>Admin Panel → Languages</a> to configure languages<br>";
echo "2. Use <a href='admin/add_news_multilang.php'>Multilingual News Editor</a> to create multi-language articles<br>";
echo "3. Test the language switcher on the main website<br>";
echo "4. Configure language settings in the admin panel<br>";
echo "</div>";

echo "<h3>Quick Links</h3>";
echo "<ul>";
echo "<li><a href='admin/'>Admin Panel</a></li>";
echo "<li><a href='admin/manage_languages.php'>Manage Languages</a></li>";
echo "<li><a href='admin/add_news_multilang.php'>Add Multilingual News</a></li>";
echo "<li><a href='index.php'>View Website</a></li>";
echo "</ul>";

echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 20px; }";
echo ".alert { padding: 10px; margin: 10px 0; border-radius: 4px; }";
echo ".alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }";
echo ".alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }";
echo ".alert-warning { background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }";
echo ".alert-info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }";
echo ".table { border-collapse: collapse; width: 100%; margin: 10px 0; }";
echo ".table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }";
echo ".table th { background: #f2f2f2; }";
echo "h1, h2, h3 { color: #333; }";
echo "a { color: #007bff; text-decoration: none; }";
echo "a:hover { text-decoration: underline; }";
echo "</style>";
?>

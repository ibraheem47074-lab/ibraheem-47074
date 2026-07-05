<?php
// Test script to verify header/footer functionality
require_once 'config/database.php';
require_once 'config/helpers.php';

// Check if system_settings table exists
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'system_settings'");
if (mysqli_num_rows($table_check) == 0) {
    echo "Creating system_settings table...<br>";
    mysqli_query($conn, "CREATE TABLE system_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(100) UNIQUE NOT NULL,
        setting_value TEXT,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        updated_by INT
    )");
    echo "Table created successfully!<br>";
}

// Test inserting some sample data
$test_data = [
    'header_content' => '<div class="alert alert-info text-center mb-0">🎉 Welcome to PK Live News! This is a custom header message from the admin panel.</div>',
    'footer_content' => '<div class="text-center py-3 bg-secondary text-white">© 2026 PK Live News - Custom Footer Content | Managed by Admin Panel</div>',
    'custom_css' => '.custom-header { border-bottom: 3px solid #dc3545; }'
];

foreach ($test_data as $key => $value) {
    $query = "INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?) 
             ON DUPLICATE KEY UPDATE setting_value = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'sss', $key, $value, $value);
    mysqli_stmt_execute($stmt);
}

echo "Test data inserted successfully!<br>";

// Test retrieving the data
echo "<h3>Testing System Settings:</h3>";
echo "<strong>Header Content:</strong><br>" . htmlspecialchars(get_system_setting('header_content')) . "<br><br>";
echo "<strong>Footer Content:</strong><br>" . htmlspecialchars(get_system_setting('footer_content')) . "<br><br>";
echo "<strong>Custom CSS:</strong><br>" . htmlspecialchars(get_system_setting('custom_css')) . "<br><br>";

echo "<h3>Next Steps:</h3>";
echo "1. Go to <a href='admin/system-settings.php'>Admin System Settings</a><br>";
echo "2. Look for the 'Header & Footer Settings' section<br>";
echo "3. Modify the content and save your changes<br>";
echo "4. Visit any page on the site to see the changes<br>";
echo "5. Only administrators can access these settings<br>";

echo "<hr><h3>Security Note:</h3>";
echo "This feature is only accessible to users with 'admin' role. Regular users cannot see or modify these settings.";
?>

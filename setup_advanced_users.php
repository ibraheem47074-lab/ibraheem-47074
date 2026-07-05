<?php
// Execute Advanced User Management Database Setup
require_once 'config/database.php';

echo "<h2>Setting up Advanced User Management System...</h2>";

// Read the SQL file
$sql_file = __DIR__ . '/advanced_user_management.sql';
if (!file_exists($sql_file)) {
    die("<p style='color: red;'>SQL file not found: $sql_file</p>");
}

$sql = file_get_contents($sql_file);

// Split SQL into individual statements
$statements = array_filter(array_map('trim', explode(';', $sql)));

$success_count = 0;
$error_count = 0;

foreach ($statements as $statement) {
    if (empty($statement)) continue;
    
    try {
        if (mysqli_query($conn, $statement)) {
            echo "<p style='color: green;'>✓ Query executed successfully</p>";
            $success_count++;
        } else {
            echo "<p style='color: orange;'>⚠ Query executed with warnings: " . mysqli_warning($conn) . "</p>";
            $success_count++;
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
        echo "<p><small>SQL: " . htmlspecialchars(substr($statement, 0, 200)) . "...</small></p>";
        $error_count++;
    }
}

echo "<hr>";
echo "<h3>Setup Complete!</h3>";
echo "<p><strong>Successful queries:</strong> $success_count</p>";
echo "<p><strong>Failed queries:</strong> $error_count</p>";

if ($error_count === 0) {
    echo "<p style='color: green; font-weight: bold;'>✓ Advanced User Management System installed successfully!</p>";
    echo "<p><a href='admin/advanced-profile.php'>Test Advanced Profile</a></p>";
    echo "<p><a href='admin/user-management.php'>Test User Management</a></p>";
} else {
    echo "<p style='color: red;'>Some errors occurred. Please check the messages above.</p>";
}

// Verify tables were created
echo "<h3>Verifying Tables...</h3>";
$tables_to_check = [
    'user_permissions',
    'user_activity_log', 
    'user_achievements',
    'user_ratings',
    'user_work_schedule',
    'advanced_roles'
];

foreach ($tables_to_check as $table) {
    $result = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
    if (mysqli_num_rows($result) > 0) {
        echo "<p style='color: green;'>✓ Table '$table' exists</p>";
    } else {
        echo "<p style='color: red;'>✗ Table '$table' missing</p>";
    }
}

// Check if users table has new columns
echo "<h3>Checking Users Table Structure...</h3>";
$check_columns = mysqli_query($conn, "DESCRIBE users");
$existing_columns = [];
while ($row = mysqli_fetch_assoc($check_columns)) {
    $existing_columns[] = $row['Field'];
}

$new_columns = [
    'department',
    'specialization', 
    'experience_level',
    'skills',
    'social_links',
    'last_login',
    'login_count',
    'profile_views',
    'articles_published',
    'is_featured',
    'verification_status',
    'preferred_language',
    'timezone',
    'notification_preferences',
    'working_hours'
];

foreach ($new_columns as $column) {
    if (in_array($column, $existing_columns)) {
        echo "<p style='color: green;'>✓ Column '$column' exists in users table</p>";
    } else {
        echo "<p style='color: orange;'>⚠ Column '$column' missing from users table</p>";
    }
}

echo "<hr>";
echo "<p><a href='admin/login.php'>Go to Admin Login</a></p>";
?>

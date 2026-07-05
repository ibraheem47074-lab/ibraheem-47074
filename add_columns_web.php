<?php
require_once 'config/database.php';

echo "<h1>Adding Missing Database Columns</h1>";

$columns_to_add = [
    'phone' => "ALTER TABLE users ADD COLUMN phone VARCHAR(20) NULL AFTER email",
    'bio' => "ALTER TABLE users ADD COLUMN bio TEXT NULL AFTER phone",
    'image' => "ALTER TABLE users ADD COLUMN image VARCHAR(255) NULL AFTER bio",
    'email_verified' => "ALTER TABLE users ADD COLUMN email_verified TINYINT(1) DEFAULT 0 AFTER image",
    'two_factor_enabled' => "ALTER TABLE users ADD COLUMN two_factor_enabled TINYINT(1) DEFAULT 0 AFTER email_verified",
    'email_notifications' => "ALTER TABLE users ADD COLUMN email_notifications TINYINT(1) DEFAULT 1 AFTER two_factor_enabled",
    'push_notifications' => "ALTER TABLE users ADD COLUMN push_notifications TINYINT(1) DEFAULT 0 AFTER email_notifications",
    'newsletter_subscription' => "ALTER TABLE users ADD COLUMN newsletter_subscription TINYINT(1) DEFAULT 1 AFTER push_notifications",
    'profile_public' => "ALTER TABLE users ADD COLUMN profile_public TINYINT(1) DEFAULT 0 AFTER newsletter_subscription",
    'show_activity' => "ALTER TABLE users ADD COLUMN show_activity TINYINT(1) DEFAULT 1 AFTER profile_public",
    'preferred_categories' => "ALTER TABLE users ADD COLUMN preferred_categories TEXT NULL AFTER show_activity",
    'language_preference' => "ALTER TABLE users ADD COLUMN language_preference VARCHAR(10) DEFAULT 'en' AFTER preferred_categories",
    'reset_token' => "ALTER TABLE users ADD COLUMN reset_token VARCHAR(255) NULL AFTER language_preference",
    'reset_token_expires' => "ALTER TABLE users ADD COLUMN reset_token_expires DATETIME NULL AFTER reset_token",
    'email_verification_token' => "ALTER TABLE users ADD COLUMN email_verification_token VARCHAR(255) NULL AFTER reset_token_expires",
    'email_verification_expires' => "ALTER TABLE users ADD COLUMN email_verification_expires DATETIME NULL AFTER email_verification_token",
    'department' => "ALTER TABLE users ADD COLUMN department VARCHAR(50) NULL AFTER email_verification_expires",
    'experience_level' => "ALTER TABLE users ADD COLUMN experience_level VARCHAR(20) DEFAULT 'junior' AFTER department",
    'verification_status' => "ALTER TABLE users ADD COLUMN verification_status ENUM('unverified', 'verified', 'premium') DEFAULT 'unverified' AFTER experience_level",
    'specialization' => "ALTER TABLE users ADD COLUMN specialization VARCHAR(100) NULL AFTER verification_status",
    'skills' => "ALTER TABLE users ADD COLUMN skills TEXT NULL AFTER specialization",
    'profile_views' => "ALTER TABLE users ADD COLUMN profile_views INT DEFAULT 0 AFTER skills",
    'login_count' => "ALTER TABLE users ADD COLUMN login_count INT DEFAULT 0 AFTER profile_views",
    'last_login' => "ALTER TABLE users ADD COLUMN last_login DATETIME NULL AFTER login_count"
];

// Get existing columns
$existing_columns = [];
$columns_query = "SHOW COLUMNS FROM users";
$columns_result = mysqli_query($conn, $columns_query);

if ($columns_result) {
    while ($column = mysqli_fetch_assoc($columns_result)) {
        $existing_columns[] = $column['Field'];
    }
}

// Add missing columns
$success_count = 0;
$error_count = 0;

foreach ($columns_to_add as $column_name => $alter_sql) {
    if (!in_array($column_name, $existing_columns)) {
        echo "<p style='color: orange;'>🔄 Adding column '$column_name'...</p>";
        
        if (mysqli_query($conn, $alter_sql)) {
            echo "<p style='color: green;'>✓ Column '$column_name' added successfully</p>";
            $success_count++;
        } else {
            echo "<p style='color: red;'>✗ Error adding column '$column_name': " . mysqli_error($conn) . "</p>";
            $error_count++;
        }
    } else {
        echo "<p style='color: blue;'>ℹ Column '$column_name' already exists</p>";
    }
}

echo "<h2>Summary</h2>";
echo "<p style='color: green;'>✓ Successfully added: $success_count columns</p>";
echo "<p style='color: red;'>✗ Failed to add: $error_count columns</p>";

if ($error_count === 0) {
    echo "<p style='color: green; font-weight: bold;'>🎉 All columns added successfully!</p>";
    echo "<p><a href='test_user_accounts.php'>Test User Accounts</a> | <a href='login.php'>Go to Login</a></p>";
} else {
    echo "<p style='color: red;'>⚠ Some columns failed to add. Please check the errors above.</p>";
}

// Verify final table structure
echo "<h2>Final Table Structure</h2>";
$final_columns_query = "SHOW COLUMNS FROM users";
$final_columns_result = mysqli_query($conn, $final_columns_query);

if ($final_columns_result) {
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($column = mysqli_fetch_assoc($final_columns_result)) {
        echo "<tr>";
        echo "<td>" . $column['Field'] . "</td>";
        echo "<td>" . $column['Type'] . "</td>";
        echo "<td>" . $column['Null'] . "</td>";
        echo "<td>" . $column['Key'] . "</td>";
        echo "<td>" . ($column['Default'] ?: 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}
?>

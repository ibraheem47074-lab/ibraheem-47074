<?php
require_once 'config/database.php';

echo "<h1>User Accounts Interface Test</h1>";

// Test database connection
echo "<h2>Database Connection Test</h2>";
if ($conn) {
    echo "<p style='color: green;'>✓ Database connection successful</p>";
} else {
    echo "<p style='color: red;'>✗ Database connection failed</p>";
}

// Test users table structure
echo "<h2>Users Table Structure</h2>";
$columns_query = "SHOW COLUMNS FROM users";
$columns_result = mysqli_query($conn, $columns_query);

if ($columns_result) {
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
    while ($column = mysqli_fetch_assoc($columns_result)) {
        echo "<tr>";
        echo "<td>" . $column['Field'] . "</td>";
        echo "<td>" . $column['Type'] . "</td>";
        echo "<td>" . $column['Null'] . "</td>";
        echo "<td>" . $column['Key'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>✗ Error checking users table: " . mysqli_error($conn) . "</p>";
}

// Test required functions
echo "<h2>Required Functions Test</h2>";
$required_functions = ['is_logged_in', 'is_admin', 'clean_input', 'redirect'];

foreach ($required_functions as $func) {
    if (function_exists($func)) {
        echo "<p style='color: green;'>✓ Function '$func' exists</p>";
    } else {
        echo "<p style='color: red;'>✗ Function '$func' missing</p>";
    }
}

// Test session
echo "<h2>Session Test</h2>";
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "<p style='color: green;'>✓ Session is active</p>";
} else {
    echo "<p style='color: orange;'>⚠ Session not active</p>";
}

// Test file includes
echo "<h2>File Includes Test</h2>";
$required_files = [
    'config/database.php',
    'config/helpers.php',
    'includes/header.php',
    'includes/footer.php'
];

foreach ($required_files as $file) {
    if (file_exists($file)) {
        echo "<p style='color: green;'>✓ File '$file' exists</p>";
    } else {
        echo "<p style='color: red;'>✗ File '$file' missing</p>";
    }
}

// Test API files
echo "<h2>API Files Test</h2>";
$api_files = [
    'api/update_profile_criteria.php',
    'api/send_verification.php',
    'api/toggle_2fa.php'
];

foreach ($api_files as $file) {
    if (file_exists($file)) {
        echo "<p style='color: green;'>✓ API file '$file' exists</p>";
    } else {
        echo "<p style='color: red;'>✗ API file '$file' missing</p>";
    }
}

// Test sample user data
echo "<h2>Sample User Data Test</h2>";
$sample_query = "SELECT id, name, email, role, status FROM users LIMIT 5";
$sample_result = mysqli_query($conn, $sample_query);

if ($sample_result) {
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th></tr>";
    while ($user = mysqli_fetch_assoc($sample_result)) {
        echo "<tr>";
        echo "<td>" . $user['id'] . "</td>";
        echo "<td>" . htmlspecialchars($user['name']) . "</td>";
        echo "<td>" . htmlspecialchars($user['email']) . "</td>";
        echo "<td>" . htmlspecialchars($user['role']) . "</td>";
        echo "<td>" . htmlspecialchars($user['status']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>✗ Error querying users: " . mysqli_error($conn) . "</p>";
}

// Check for common missing columns
echo "<h2>Missing Columns Check</h2>";
$expected_columns = [
    'phone', 'bio', 'image', 'email_verified', 'two_factor_enabled',
    'email_notifications', 'push_notifications', 'newsletter_subscription',
    'profile_public', 'show_activity', 'preferred_categories', 'language_preference',
    'reset_token', 'reset_token_expires', 'email_verification_token', 'email_verification_expires'
];

$columns_query = "SHOW COLUMNS FROM users";
$columns_result = mysqli_query($conn, $columns_query);
$existing_columns = [];

while ($column = mysqli_fetch_assoc($columns_result)) {
    $existing_columns[] = $column['Field'];
}

foreach ($expected_columns as $col) {
    if (in_array($col, $existing_columns)) {
        echo "<p style='color: green;'>✓ Column '$col' exists</p>";
    } else {
        echo "<p style='color: orange;'>⚠ Column '$col' missing (may cause errors)</p>";
    }
}

echo "<h2>Test Complete</h2>";
echo "<p><a href='login.php'>Test Login Page</a> | <a href='signup.php'>Test Signup Page</a> | <a href='profile.php'>Test Profile Page</a></p>";
?>

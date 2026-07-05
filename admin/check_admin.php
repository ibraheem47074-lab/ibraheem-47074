<?php
require_once '../config/database.php';

echo "<h2>Admin Credentials Check</h2>";

// Check users table structure
echo "<h3>Users Table Structure:</h3>";
$columns_query = "SHOW COLUMNS FROM users";
$columns_result = mysqli_query($conn, $columns_query);
echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
while ($col = mysqli_fetch_assoc($columns_result)) {
    echo "<tr><td>{$col['Field']}</td><td>{$col['Type']}</td><td>{$col['Null']}</td><td>{$col['Key']}</td></tr>";
}
echo "</table>";

// List all users
echo "<h3>All Users:</h3>";
$users_query = "SELECT id, username, email, role, status, created_at FROM users ORDER BY id ASC";
$users_result = mysqli_query($conn, $users_query);

if (mysqli_num_rows($users_result) > 0) {
    echo "<table border='1'><tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Status</th><th>Created</th></tr>";
    while ($user = mysqli_fetch_assoc($users_result)) {
        echo "<tr>";
        echo "<td>" . $user['id'] . "</td>";
        echo "<td>" . htmlspecialchars($user['username']) . "</td>";
        echo "<td>" . htmlspecialchars($user['email']) . "</td>";
        echo "<td>" . htmlspecialchars($user['role']) . "</td>";
        echo "<td>" . htmlspecialchars($user['status']) . "</td>";
        echo "<td>" . $user['created_at'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No users found in database.</p>";
}

// Check for admin specifically
echo "<h3>Admin Users:</h3>";
$admin_query = "SELECT id, username, email, role, status FROM users WHERE role = 'admin' OR role LIKE '%admin%'";
$admin_result = mysqli_query($conn, $admin_query);

if (mysqli_num_rows($admin_result) > 0) {
    echo "<table border='1'><tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Status</th></tr>";
    while ($admin = mysqli_fetch_assoc($admin_result)) {
        echo "<tr>";
        echo "<td>" . $admin['id'] . "</td>";
        echo "<td><strong>" . htmlspecialchars($admin['username']) . "</strong></td>";
        echo "<td>" . htmlspecialchars($admin['email']) . "</td>";
        echo "<td>" . htmlspecialchars($admin['role']) . "</td>";
        echo "<td>" . htmlspecialchars($admin['status']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No admin users found.</p>";
}

// Show default admin creation if no users exist
$user_count_query = "SELECT COUNT(*) as count FROM users";
$count_result = mysqli_query($conn, $user_count_query);
$user_count = mysqli_fetch_assoc($count_result);

if ($user_count['count'] == 0) {
    echo "<h3>Create Default Admin:</h3>";
    echo "<p>No users found. You can create a default admin account.</p>";
    
    // Create default admin
    $default_username = 'admin';
    $default_email = 'admin@pklivenews.com';
    $default_password = password_hash('admin123', PASSWORD_DEFAULT);
    
    $insert_query = "INSERT INTO users (username, email, password, role, status, created_at) VALUES (?, ?, ?, 'admin', 'active', NOW())";
    $stmt = mysqli_prepare($conn, $insert_query);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'sss', $default_username, $default_email, $default_password);
        if (mysqli_stmt_execute($stmt)) {
            echo "<p style='color: green;'>✓ Default admin created successfully!</p>";
            echo "<p><strong>Username:</strong> admin</p>";
            echo "<p><strong>Password:</strong> admin123</p>";
            echo "<p><strong>Email:</strong> admin@pklivenews.com</p>";
        } else {
            echo "<p style='color: red;'>✗ Failed to create default admin: " . mysqli_stmt_error($stmt) . "</p>";
        }
    }
}
?>

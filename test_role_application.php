<?php
require_once 'config/database.php';

echo "<h2>Role Application System Test</h2>";

// Test 1: Check if role_applications table exists
echo "<h3>1. Checking role_applications table...</h3>";
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'role_applications'");
if (mysqli_num_rows($table_check) > 0) {
    echo "✅ role_applications table exists<br>";
    
    // Show table structure
    $structure = mysqli_query($conn, "DESCRIBE role_applications");
    echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
    while ($row = mysqli_fetch_assoc($structure)) {
        echo "<tr><td>{$row['Field']}</td><td>{$row['Type']}</td><td>{$row['Null']}</td><td>{$row['Key']}</td></tr>";
    }
    echo "</table>";
} else {
    echo "❌ role_applications table does not exist<br>";
    
    // Create the table
    $create_table = "
    CREATE TABLE `role_applications` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) NOT NULL,
        `applied_role` enum('editor','reporter') NOT NULL,
        `application_data` text DEFAULT NULL,
        `cv_file_path` varchar(500) DEFAULT NULL,
        `cv_file_name` varchar(255) DEFAULT NULL,
        `cv_file_size` int(11) DEFAULT NULL,
        `status` enum('pending','approved','rejected','withdrawn') DEFAULT 'pending',
        `admin_notes` text DEFAULT NULL,
        `reviewed_by` int(11) DEFAULT NULL,
        `reviewed_at` datetime DEFAULT NULL,
        `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
        `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `idx_user_id` (`user_id`),
        KEY `idx_status` (`status`),
        KEY `idx_applied_role` (`applied_role`),
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
        FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if (mysqli_query($conn, $create_table)) {
        echo "✅ Created role_applications table<br>";
    } else {
        echo "❌ Error creating table: " . mysqli_error($conn) . "<br>";
    }
}

// Test 2: Check users table for required columns
echo "<h3>2. Checking users table columns...</h3>";
$users_columns = mysqli_query($conn, "DESCRIBE users");
$required_columns = ['application_status', 'applied_role'];
$existing_columns = [];

while ($row = mysqli_fetch_assoc($users_columns)) {
    $existing_columns[] = $row['Field'];
}

foreach ($required_columns as $column) {
    if (in_array($column, $existing_columns)) {
        echo "✅ Column '$column' exists<br>";
    } else {
        echo "❌ Column '$column' missing<br>";
        
        // Add missing column
        if ($column === 'application_status') {
            $alter = "ALTER TABLE users ADD COLUMN application_status enum('none','pending','approved','rejected') DEFAULT 'none' AFTER role";
        } elseif ($column === 'applied_role') {
            $alter = "ALTER TABLE users ADD COLUMN applied_role enum('editor','reporter') DEFAULT NULL AFTER application_status";
        }
        
        if (mysqli_query($conn, $alter)) {
            echo "✅ Added column '$column'<br>";
        } else {
            echo "❌ Error adding column '$column': " . mysqli_error($conn) . "<br>";
        }
    }
}

// Test 3: Check if API file exists
echo "<h3>3. Checking API files...</h3>";
if (file_exists('api/submit_role_application.php')) {
    echo "✅ submit_role_application.php exists<br>";
} else {
    echo "❌ submit_role_application.php missing<br>";
}

if (file_exists('api/application_notifications.php')) {
    echo "✅ application_notifications.php exists<br>";
} else {
    echo "❌ application_notifications.php missing<br>";
}

if (file_exists('api/withdraw_application.php')) {
    echo "✅ withdraw_application.php exists<br>";
} else {
    echo "❌ withdraw_application.php missing<br>";
}

// Test 4: Check upload directory
echo "<h3>4. Checking upload directories...</h3>";
if (is_dir('uploads/cv')) {
    echo "✅ uploads/cv directory exists<br>";
} else {
    echo "❌ uploads/cv directory missing<br>";
    if (mkdir('uploads/cv', 0755, true)) {
        echo "✅ Created uploads/cv directory<br>";
    } else {
        echo "❌ Failed to create uploads/cv directory<br>";
    }
}

// Test 5: Check if user has test data
echo "<h3>5. Checking for test users...</h3>";
$users_query = mysqli_query($conn, "SELECT id, name, email, role, application_status, applied_role FROM users LIMIT 5");
if (mysqli_num_rows($users_query) > 0) {
    echo "<table border='1'><tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>App Status</th><th>Applied Role</th></tr>";
    while ($user = mysqli_fetch_assoc($users_query)) {
        echo "<tr><td>{$user['id']}</td><td>{$user['name']}</td><td>{$user['email']}</td><td>{$user['role']}</td><td>{$user['application_status']}</td><td>{$user['applied_role']}</td></tr>";
    }
    echo "</table>";
} else {
    echo "❌ No users found in database<br>";
}

// Test 6: Check existing applications
echo "<h3>6. Checking existing applications...</h3>";
$applications_query = mysqli_query($conn, "SELECT ra.*, u.name, u.email FROM role_applications ra JOIN users u ON ra.user_id = u.id ORDER BY ra.created_at DESC");
if (mysqli_num_rows($applications_query) > 0) {
    echo "<table border='1'><tr><th>ID</th><th>User</th><th>Role</th><th>Status</th><th>Created</th></tr>";
    while ($app = mysqli_fetch_assoc($applications_query)) {
        echo "<tr><td>{$app['id']}</td><td>{$app['name']}</td><td>{$app['applied_role']}</td><td>{$app['status']}</td><td>{$app['created_at']}</td></tr>";
    }
    echo "</table>";
} else {
    echo "ℹ️ No applications found (this is normal for a new system)<br>";
}

echo "<h3>✅ Test Complete!</h3>";
echo "<p>If all checks pass, the role application system should work correctly.</p>";
echo "<p><a href='profile.php'>Go to Profile Page</a> | <a href='admin/manage_role_applications.php'>Admin Panel</a></p>";
?>

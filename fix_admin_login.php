<?php
// Fix admin login password hash issue

require_once 'config/database.php';

echo "<h2>Admin Login Fix</h2>";

// Check if admin user exists
$check_admin = "SELECT * FROM users WHERE email = 'admin@pklivenews.com'";
$result = mysqli_query($conn, $check_admin);

if ($user = mysqli_fetch_assoc($result)) {
    echo "<p style='color: blue;'>Admin user found:</p>";
    echo "<ul>";
    echo "<li>Email: " . $user['email'] . "</li>";
    echo "<li>Role: " . $user['role'] . "</li>";
    echo "<li>Status: " . $user['status'] . "</li>";
    echo "<li>Password hash: " . substr($user['password'], 0, 20) . "...</li>";
    echo "</ul>";
    
    // Test password verification
    if (password_verify('admin123', $user['password'])) {
        echo "<p style='color: green;'>Password verification: SUCCESS - Login should work!</p>";
    } else {
        echo "<p style='color: red;'>Password verification: FAILED - Fixing password...</p>";
        
        // Update password with correct hash
        $new_password = password_hash('admin123', PASSWORD_DEFAULT);
        $update_query = "UPDATE users SET password = ? WHERE email = 'admin@pklivenews.com'";
        $stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($stmt, 's', $new_password);
        
        if (mysqli_stmt_execute($stmt)) {
            echo "<p style='color: green;'>Password updated successfully!</p>";
            
            // Verify the fix
            $check_result = mysqli_query($conn, $check_admin);
            $updated_user = mysqli_fetch_assoc($check_result);
            
            if (password_verify('admin123', $updated_user['password'])) {
                echo "<p style='color: green;'>Password verification after fix: SUCCESS!</p>";
            } else {
                echo "<p style='color: red;'>Password verification after fix: STILL FAILED!</p>";
            }
        } else {
            echo "<p style='color: red;'>Error updating password: " . mysqli_error($conn) . "</p>";
        }
    }
} else {
    echo "<p style='color: red;'>Admin user not found. Creating admin user...</p>";
    
    // Create admin user
    $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
    $insert_admin = "INSERT INTO `users` (`name`, `email`, `password`, `role`, `status`) VALUES ('Admin', 'admin@pklivenews.com', ?, 'admin', 'active')";
    $stmt = mysqli_prepare($conn, $insert_admin);
    mysqli_stmt_bind_param($stmt, 's', $admin_password);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "<p style='color: green;'>Admin user created successfully!</p>";
    } else {
        echo "<p style='color: red;'>Error creating admin user: " . mysqli_error($conn) . "</p>";
    }
}

echo "<hr>";
echo "<p><a href='admin/login.php'>Go to Admin Login</a></p>";
echo "<p>Use: admin@pklivenews.com / admin123</p>";
?>

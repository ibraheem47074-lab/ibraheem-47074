<?php
// Fix admin password
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Fix Admin Password</h2>";

require_once 'config/database.php';

$email = 'admin@pklivenews.com';
$new_password = 'admin123';

// Get current user data
$query = "SELECT * FROM users WHERE email = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 's', $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($user = mysqli_fetch_assoc($result)) {
    echo "Current user found:<br>";
    echo "ID: {$user['id']}<br>";
    echo "Name: {$user['name']}<br>";
    echo "Email: {$user['email']}<br>";
    echo "Current password hash: {$user['password']}<br>";
    
    // Test current password
    if (password_verify($new_password, $user['password'])) {
        echo "<span style='color: green;'>Current password is correct!</span><br>";
    } else {
        echo "<span style='color: red;'>Current password is incorrect. Updating...</span><br>";
        
        // Generate new password hash
        $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
        echo "New password hash: $new_hash<br>";
        
        // Update password
        $update_query = "UPDATE users SET password = ? WHERE email = ?";
        $update_stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($update_stmt, 'ss', $new_hash, $email);
        
        if (mysqli_stmt_execute($update_stmt)) {
            echo "<span style='color: green;'>Password updated successfully!</span><br>";
            
            // Test new password
            $test_query = "SELECT * FROM users WHERE email = ?";
            $test_stmt = mysqli_prepare($conn, $test_query);
            mysqli_stmt_bind_param($test_stmt, 's', $email);
            mysqli_stmt_execute($test_stmt);
            $test_result = mysqli_stmt_get_result($test_stmt);
            $test_user = mysqli_fetch_assoc($test_result);
            
            if (password_verify($new_password, $test_user['password'])) {
                echo "<span style='color: green;'>New password verification SUCCESS!</span><br>";
            } else {
                echo "<span style='color: red;'>New password verification FAILED!</span><br>";
            }
        } else {
            echo "<span style='color: red;'>Password update failed: " . mysqli_error($conn) . "</span><br>";
        }
    }
} else {
    echo "<span style='color: red;'>Admin user not found!</span><br>";
    
    // Create admin user
    $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
    $insert_query = "INSERT INTO users (name, email, password, role, status) 
                    VALUES ('Admin', ?, ?, 'admin', 'active')";
    $insert_stmt = mysqli_prepare($conn, $insert_query);
    mysqli_stmt_bind_param($insert_stmt, 'ss', $email, $password_hash);
    
    if (mysqli_stmt_execute($insert_stmt)) {
        echo "<span style='color: green;'>Admin user created successfully!</span><br>";
        echo "Email: $email<br>";
        echo "Password: $new_password<br>";
    } else {
        echo "<span style='color: red;'>Failed to create admin user: " . mysqli_error($conn) . "</span><br>";
    }
}

mysqli_close($conn);
?>

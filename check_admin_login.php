<?php
require_once 'config/database.php';

$query = "SELECT * FROM users WHERE email = 'admin@pklivenews.com'";
$result = mysqli_query($conn, $query);

if ($user = mysqli_fetch_assoc($result)) {
    echo "User found: " . $user['email'] . "\n";
    echo "Role: " . $user['role'] . "\n";
    echo "Status: " . $user['status'] . "\n";
    echo "Password hash: " . $user['password'] . "\n";
    
    if (password_verify('admin123', $user['password'])) {
        echo "Password verification: SUCCESS\n";
    } else {
        echo "Password verification: FAILED\n";
        echo "Trying to verify with password hash...\n";
        
        // Check if password is stored as plain text (for debugging)
        if ($user['password'] === 'admin123') {
            echo "Password is stored as plain text - this is insecure!\n";
        } else {
            echo "Password appears to be hashed\n";
        }
    }
} else {
    echo "User not found\n";
    
    // Check if any admin users exist
    $adminQuery = "SELECT * FROM users WHERE role = 'admin'";
    $adminResult = mysqli_query($conn, $adminQuery);
    $adminCount = mysqli_num_rows($adminResult);
    echo "Admin users found: $adminCount\n";
    
    if ($adminCount > 0) {
        echo "Admin users:\n";
        while ($admin = mysqli_fetch_assoc($adminResult)) {
            echo "- " . $admin['email'] . " (Status: " . $admin['status'] . ")\n";
        }
    }
}
?>

<?php
// Test login functionality
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Login Test</h2>";

// Load database configuration
require_once 'config/database.php';

echo "Database connection: " . ($conn->query("SELECT 1") ? 'Success' : 'Failed') . "<br>";

// Test admin login
echo "<h3>Testing Admin Login</h3>";
$email = 'admin@pklivenews.com';
$password = 'admin123';

$query = "SELECT * FROM users WHERE email = ? AND status = 'active'";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 's', $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($user = mysqli_fetch_assoc($result)) {
    echo "User found: " . $user['name'] . " (" . $user['role'] . ")<br>";
    
    if (password_verify($password, $user['password'])) {
        echo "<span style='color: green;'>Password verification SUCCESS!</span><br>";
        echo "Login would redirect to: dashboard.php<br>";
    } else {
        echo "<span style='color: red;'>Password verification FAILED!</span><br>";
    }
} else {
    echo "<span style='color: red;'>User not found!</span><br>";
}

// Show all users for debugging
echo "<h3>All Users in Database</h3>";
$all_users = mysqli_query($conn, "SELECT id, name, email, role, status FROM users");
while ($user = mysqli_fetch_assoc($all_users)) {
    echo "ID: {$user['id']}, Name: {$user['name']}, Email: {$user['email']}, Role: {$user['role']}, Status: {$user['status']}<br>";
}

// Test helper functions
echo "<h3>Helper Functions Test</h3>";
echo "is_logged_in(): " . (is_logged_in() ? 'true' : 'false') . "<br>";
echo "is_admin(): " . (is_admin() ? 'true' : 'false') . "<br>";

mysqli_close($conn);
?>

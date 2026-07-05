<?php
require_once 'config/database.php';

echo "<h2>Creating Reporter and Editor Accounts</h2>";

// Create Reporter Account
$reporter_email = 'reporter@pklivenews.com';
$reporter_password = 'reporter123';
$reporter_name = 'PK News Reporter';

// Hash the password
$hashed_reporter_password = password_hash($reporter_password, PASSWORD_DEFAULT);

// Check if reporter already exists
$check_reporter = "SELECT id FROM users WHERE email = ?";
$stmt = mysqli_prepare($conn, $check_reporter);
mysqli_stmt_bind_param($stmt, 's', $reporter_email);
mysqli_stmt_execute($stmt);
$reporter_exists = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($reporter_exists) == 0) {
    // Insert reporter account
    $insert_reporter = "INSERT INTO users (name, email, password, role, status, created_at) VALUES (?, ?, ?, 'reporter', 'active', NOW())";
    $stmt = mysqli_prepare($conn, $insert_reporter);
    mysqli_stmt_bind_param($stmt, 'sss', $reporter_name, $reporter_email, $hashed_reporter_password);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "<div style='color: green; padding: 10px; margin: 10px 0; background: #d4edda; border-radius: 5px;'>";
        echo "<strong>✅ Reporter Account Created Successfully!</strong><br>";
        echo "Email: {$reporter_email}<br>";
        echo "Password: {$reporter_password}<br>";
        echo "Role: Reporter<br>";
        echo "Status: Active";
        echo "</div>";
    } else {
        echo "<div style='color: red; padding: 10px; margin: 10px 0; background: #f8d7da; border-radius: 5px;'>";
        echo "<strong>❌ Error creating reporter account!</strong><br>";
        echo "Error: " . mysqli_error($conn);
        echo "</div>";
    }
} else {
    echo "<div style='color: orange; padding: 10px; margin: 10px 0; background: #fff3cd; border-radius: 5px;'>";
    echo "<strong>⚠️ Reporter account already exists!</strong><br>";
    echo "Email: {$reporter_email}<br>";
    echo "Password: {$reporter_password}<br>";
    echo "Role: Reporter<br>";
    echo "Status: Active";
    echo "</div>";
}

// Create Editor Account
$editor_email = 'editor@pklivenews.com';
$editor_password = 'editor123';
$editor_name = 'PK News Editor';

// Hash the password
$hashed_editor_password = password_hash($editor_password, PASSWORD_DEFAULT);

// Check if editor already exists
$check_editor = "SELECT id FROM users WHERE email = ?";
$stmt = mysqli_prepare($conn, $check_editor);
mysqli_stmt_bind_param($stmt, 's', $editor_email);
mysqli_stmt_execute($stmt);
$editor_exists = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($editor_exists) == 0) {
    // Insert editor account
    $insert_editor = "INSERT INTO users (name, email, password, role, status, created_at) VALUES (?, ?, ?, 'editor', 'active', NOW())";
    $stmt = mysqli_prepare($conn, $insert_editor);
    mysqli_stmt_bind_param($stmt, 'sss', $editor_name, $editor_email, $hashed_editor_password);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "<div style='color: green; padding: 10px; margin: 10px 0; background: #d4edda; border-radius: 5px;'>";
        echo "<strong>✅ Editor Account Created Successfully!</strong><br>";
        echo "Email: {$editor_email}<br>";
        echo "Password: {$editor_password}<br>";
        echo "Role: Editor<br>";
        echo "Status: Active";
        echo "</div>";
    } else {
        echo "<div style='color: red; padding: 10px; margin: 10px 0; background: #f8d7da; border-radius: 5px;'>";
        echo "<strong>❌ Error creating editor account!</strong><br>";
        echo "Error: " . mysqli_error($conn);
        echo "</div>";
    }
} else {
    echo "<div style='color: orange; padding: 10px; margin: 10px 0; background: #fff3cd; border-radius: 5px;'>";
    echo "<strong>⚠️ Editor account already exists!</strong><br>";
    echo "Email: {$editor_email}<br>";
    echo "Password: {$editor_password}<br>";
    echo "Role: Editor<br>";
    echo "Status: Active";
    echo "</div>";
}

// Display all current users
echo "<h3>Current Users in System:</h3>";
$get_users = "SELECT id, name, email, role, status, created_at FROM users ORDER BY created_at DESC";
$users_result = mysqli_query($conn, $get_users);

if (mysqli_num_rows($users_result) > 0) {
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%; margin-top: 20px;'>";
    echo "<tr style='background: #f0f0f0;'>";
    echo "<th>ID</th>";
    echo "<th>Name</th>";
    echo "<th>Email</th>";
    echo "<th>Role</th>";
    echo "<th>Status</th>";
    echo "<th>Created</th>";
    echo "</tr>";
    
    while ($user = mysqli_fetch_assoc($users_result)) {
        echo "<tr>";
        echo "<td>" . $user['id'] . "</td>";
        echo "<td>" . htmlspecialchars($user['name']) . "</td>";
        echo "<td>" . htmlspecialchars($user['email']) . "</td>";
        echo "<td><span style='padding: 3px 8px; border-radius: 3px; background: " . 
             ($user['role'] == 'admin' ? '#dc3545' : 
              ($user['role'] == 'editor' ? '#4834d4' : 
               ($user['role'] == 'reporter' ? '#f39c12' : '#6c5ce7'))) . 
             "; color: white;'>" . ucfirst($user['role']) . "</span></td>";
        echo "<td><span style='padding: 3px 8px; border-radius: 3px; background: " . 
             ($user['status'] == 'active' ? '#28a745' : '#6c757d') . 
             "; color: white;'>" . ucfirst($user['status']) . "</span></td>";
        echo "<td>" . date('M d, Y H:i', strtotime($user['created_at'])) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>No users found in the system.</p>";
}

// Login links
echo "<h3>Quick Login Links:</h3>";
echo "<div style='margin-top: 20px; padding: 20px; background: #f8f9fa; border-radius: 5px;'>";
echo "<h4>📝 Reporter Login:</h4>";
echo "<p><strong>Email:</strong> {$reporter_email}<br>";
echo "<strong>Password:</strong> {$reporter_password}</p>";
echo "<a href='admin/login.php' style='background: #f39c12; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Login as Reporter</a><br><br>";

echo "<h4>✏️ Editor Login:</h4>";
echo "<p><strong>Email:</strong> {$editor_email}<br>";
echo "<strong>Password:</strong> {$editor_password}</p>";
echo "<a href='admin/login.php' style='background: #4834d4; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Login as Editor</a>";
echo "</div>";

echo "<h3>Role Permissions:</h3>";
echo "<div style='margin-top: 20px;'>";
echo "<h4>📝 Reporter Permissions:</h4>";
echo "<ul>";
echo "<li>✅ Create news articles</li>";
echo "<li>✅ Edit own articles</li>";
echo "<li>✅ Submit for approval</li>";
echo "<li>✅ View own statistics</li>";
echo "<li>✅ Basic permissions</li>";
echo "</ul>";

echo "<h4>✏️ Editor Permissions:</h4>";
echo "<ul>";
echo "<li>✅ Manage news articles</li>";
echo "<li>✅ Edit and publish news</li>";
echo "<li>✅ Manage comments</li>";
echo "<li>✅ Manage polls</li>";
echo "<li>✅ View statistics</li>";
echo "</ul>";
echo "</div>";

echo "<div style='margin-top: 30px; padding: 15px; background: #e7f3ff; border-left: 4px solid #007bff; border-radius: 5px;'>";
echo "<strong>📝 Note:</strong> These accounts are for testing purposes. In a production environment, please change the passwords to more secure ones.";
echo "</div>";

mysqli_close($conn);
?>

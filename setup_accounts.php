<?php
// Database connection
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'pk_live_news';

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h2>PK Live News - Account Creation</h2>";

// Reporter Account
$reporter_email = 'reporter@pklivenews.com';
$reporter_password = 'reporter123';
$reporter_name = 'PK News Reporter';
$reporter_hashed_password = password_hash($reporter_password, PASSWORD_DEFAULT);

// Check if exists
$check = $conn->query("SELECT id FROM users WHERE email = '$reporter_email'");
if ($check->num_rows == 0) {
    $sql = "INSERT INTO users (name, email, password, role, status, created_at) VALUES (?, ?, ?, 'reporter', 'active', NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $reporter_name, $reporter_email, $reporter_hashed_password);
    if ($stmt->execute()) {
        echo "<div style='color: green; background: #d4edda; padding: 10px; margin: 10px; border-radius: 5px;'>";
        echo "✅ Reporter account created successfully!<br>";
        echo "Email: $reporter_email<br>";
        echo "Password: $reporter_password";
        echo "</div>";
    }
} else {
    echo "<div style='color: orange; background: #fff3cd; padding: 10px; margin: 10px; border-radius: 5px;'>";
    echo "⚠️ Reporter account already exists: $reporter_email";
    echo "</div>";
}

// Editor Account
$editor_email = 'editor@pklivenews.com';
$editor_password = 'editor123';
$editor_name = 'PK News Editor';
$editor_hashed_password = password_hash($editor_password, PASSWORD_DEFAULT);

// Check if exists
$check = $conn->query("SELECT id FROM users WHERE email = '$editor_email'");
if ($check->num_rows == 0) {
    $sql = "INSERT INTO users (name, email, password, role, status, created_at) VALUES (?, ?, ?, 'editor', 'active', NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $editor_name, $editor_email, $editor_hashed_password);
    if ($stmt->execute()) {
        echo "<div style='color: green; background: #d4edda; padding: 10px; margin: 10px; border-radius: 5px;'>";
        echo "✅ Editor account created successfully!<br>";
        echo "Email: $editor_email<br>";
        echo "Password: $editor_password";
        echo "</div>";
    }
} else {
    echo "<div style='color: orange; background: #fff3cd; padding: 10px; margin: 10px; border-radius: 5px;'>";
    echo "⚠️ Editor account already exists: $editor_email";
    echo "</div>";
}

// Show all users
echo "<h3>All Current Users:</h3>";
$result = $conn->query("SELECT id, name, email, role, status, created_at FROM users ORDER BY created_at DESC");
if ($result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f0f0f0;'><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Created</th></tr>";
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>".$row['id']."</td>";
        echo "<td>".htmlspecialchars($row['name'])."</td>";
        echo "<td>".htmlspecialchars($row['email'])."</td>";
        echo "<td>".ucfirst($row['role'])."</td>";
        echo "<td>".ucfirst($row['status'])."</td>";
        echo "<td>".date('M d, Y', strtotime($row['created_at']))."</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<h3>Login Credentials:</h3>";
echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
echo "<h4>📝 Reporter:</h4>";
echo "Email: $reporter_email<br>";
echo "Password: $reporter_password<br>";
echo "<a href='admin/login.php' style='background: #f39c12; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Login as Reporter</a><br><br>";

echo "<h4>✏️ Editor:</h4>";
echo "Email: $editor_email<br>";
echo "Password: $editor_password<br>";
echo "<a href='admin/login.php' style='background: #4834d4; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Login as Editor</a>";
echo "</div>";

$conn->close();
?>

<?php
session_start();
require_once '../config/database.php';

echo "<h2>Publish Test Debug</h2>";

// Check database connection
if ($conn) {
    echo "<p style='color: green;'>✓ Database connection successful</p>";
} else {
    echo "<p style='color: red;'>✗ Database connection failed</p>";
}

// Check session
echo "<p>Session status: " . session_status() . "</p>";
echo "<p>Session ID: " . session_id() . "</p>";
echo "<p>User ID in session: " . ($_SESSION['user_id'] ?? 'Not set') . "</p>";
echo "<p>User role in session: " . ($_SESSION['user_role'] ?? 'Not set') . "</p>";

// Check helper functions
echo "<h3>Helper Functions Test:</h3>";
echo "<p>is_logged_in(): " . (is_logged_in() ? 'true' : 'false') . "</p>";
echo "<p>is_admin(): " . (is_admin() ? 'true' : 'false') . "</p>";
echo "<p>is_editor(): " . (is_editor() ? 'true' : 'false') . "</p>";

// Test slugify function
$test_title = "Test News Article " . date('Y-m-d H:i:s');
$test_slug = slugify($test_title);
$title_var = $test_title;
$slug_var = $test_slug;
echo "<p>Slugify test: '$test_title' -> '$test_slug'</p>";

// Check categories table
$cat_query = "SELECT COUNT(*) as count FROM categories WHERE status = 'active'";
$cat_result = mysqli_query($conn, $cat_query);
$cat_count = mysqli_fetch_assoc($cat_result);
echo "<p>Active categories: " . $cat_count['count'] . "</p>";

// Check news table structure
echo "<h3>News Table Structure:</h3>";
$columns_query = "SHOW COLUMNS FROM news";
$columns_result = mysqli_query($conn, $columns_query);
echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
while ($col = mysqli_fetch_assoc($columns_result)) {
    echo "<tr><td>{$col['Field']}</td><td>{$col['Type']}</td><td>{$col['Null']}</td><td>{$col['Key']}</td></tr>";
}
echo "</table>";

// Test simple insert
echo "<h3>Test Simple Insert:</h3>";
$test_query = "INSERT INTO news (title, slug, content, category_id, author_id, status, published_at) VALUES (?, ?, ?, ?, ?, ?, NOW())";
$stmt = mysqli_prepare($conn, $test_query);
if ($stmt) {
    $test_content = "This is a test article content.";
    $user_id = $_SESSION['user_id'] ?? 1;
    $category_id = $cat_count['count'];
    $status = 'published';
    mysqli_stmt_bind_param($stmt, 'sssiss', $title_var, $slug_var, $test_content, $category_id, $user_id, $status);
    if (mysqli_stmt_execute($stmt)) {
        $insert_id = mysqli_insert_id($conn);
        echo "<p style='color: green;'>✓ Simple insert successful! ID: $insert_id</p>";
    } else {
        echo "<p style='color: red;'>✗ Simple insert failed: " . mysqli_stmt_error($stmt) . "</p>";
    }
} else {
    echo "<p style='color: red;'>✗ Prepare failed: " . mysqli_error($conn) . "</p>";
}
?>

<?php
require_once '../config/database.php';

echo "<h1>Database Debug - News Table Structure</h1>";

// Check news table structure
$result = mysqli_query($conn, "DESCRIBE news");
if ($result) {
    echo "<h2>News Table Structure:</h2>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>{$row['Field']}</td>";
        echo "<td>{$row['Type']}</td>";
        echo "<td>{$row['Null']}</td>";
        echo "<td>{$row['Key']}</td>";
        echo "<td>{$row['Default']}</td>";
        echo "<td>{$row['Extra']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>Error: " . mysqli_error($conn) . "</p>";
}

// Test a simple insert
echo "<h2>Test Simple Insert:</h2>";

$test_title = "Test Article " . date('Y-m-d H:i:s');
$test_slug = "test-article-" . time();
$test_content = "This is a test article content.";
$test_excerpt = "Test article excerpt.";
$test_category_id = 1; // Assuming category 1 exists
$test_author_id = 1; // Assuming user 1 exists
$test_status = "published";
$test_published_at = date('Y-m-d H:i:s');

// Check if category exists
$cat_check = mysqli_query($conn, "SELECT id FROM categories WHERE id = $test_category_id LIMIT 1");
if (mysqli_num_rows($cat_check) == 0) {
    echo "<p style='color: orange;'>Warning: Category ID $test_category_id does not exist. Using first available category.</p>";
    $cat_result = mysqli_query($conn, "SELECT id FROM categories ORDER BY id LIMIT 1");
    if ($cat_row = mysqli_fetch_assoc($cat_result)) {
        $test_category_id = $cat_row['id'];
        echo "<p>Using category ID: $test_category_id</p>";
    }
}

// Check if user exists
$user_check = mysqli_query($conn, "SELECT id FROM users WHERE id = $test_author_id LIMIT 1");
if (mysqli_num_rows($user_check) == 0) {
    echo "<p style='color: orange;'>Warning: User ID $test_author_id does not exist. Using first available user.</p>";
    $user_result = mysqli_query($conn, "SELECT id FROM users ORDER BY id LIMIT 1");
    if ($user_row = mysqli_fetch_assoc($user_result)) {
        $test_author_id = $user_row['id'];
        echo "<p>Using user ID: $test_author_id</p>";
    }
}

$test_query = "INSERT INTO news (title, slug, content, excerpt, category_id, author_id, status, published_at) 
               VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

echo "<p><strong>Test Query:</strong> " . htmlspecialchars($test_query ?? '') . "</p>";
echo "<p><strong>Parameters:</strong></p>";
echo "<ul>";
echo "<li>title: $test_title</li>";
echo "<li>slug: $test_slug</li>";
echo "<li>content: $test_content</li>";
echo "<li>excerpt: $test_excerpt</li>";
echo "<li>category_id: $test_category_id</li>";
echo "<li>author_id: $test_author_id</li>";
echo "<li>status: $test_status</li>";
echo "<li>published_at: $test_published_at</li>";
echo "</ul>";

$stmt = mysqli_prepare($conn, $test_query);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, 'ssssisss', 
        $test_title, $test_slug, $test_content, $test_excerpt, 
        $test_category_id, $test_author_id, $test_status, $test_published_at
    );
    
    if (mysqli_stmt_execute($stmt)) {
        $insert_id = mysqli_insert_id($conn);
        echo "<p style='color: green;'><strong>✓ SUCCESS: Test article inserted with ID: $insert_id</strong></p>";
        
        // Verify it was inserted
        $verify = mysqli_query($conn, "SELECT * FROM news WHERE id = $insert_id");
        if ($verify_row = mysqli_fetch_assoc($verify)) {
            echo "<h3>Inserted Article Data:</h3>";
            echo "<table border='1' cellpadding='5'>";
            foreach ($verify_row as $key => $value) {
                echo "<tr><td><strong>$key</strong></td><td>" . htmlspecialchars($value ?? '') . "</td></tr>";
            }
            echo "</table>";
        }
        
        // Clean up - delete the test article
        mysqli_query($conn, "DELETE FROM news WHERE id = $insert_id");
        echo "<p style='color: orange;'>Test article cleaned up (deleted).</p>";
        
    } else {
        echo "<p style='color: red;'><strong>✗ FAILED: " . mysqli_stmt_error($stmt) . "</strong></p>";
        echo "<p style='color: red;'><strong>MySQL Error: " . mysqli_error($conn) . "</strong></p>";
    }
} else {
    echo "<p style='color: red;'><strong>✗ PREPARE FAILED: " . mysqli_error($conn) . "</strong></p>";
}

// Check recent articles
echo "<h2>Recent Articles (Last 5):</h2>";
$recent = mysqli_query($conn, "SELECT id, title, status, published_at FROM news ORDER BY id DESC LIMIT 5");
if ($recent && mysqli_num_rows($recent) > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Title</th><th>Status</th><th>Published At</th></tr>";
    while ($row = mysqli_fetch_assoc($recent)) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>" . htmlspecialchars($row['title'] ?? '') . "</td>";
        echo "<td>{$row['status']}</td>";
        echo "<td>{$row['published_at']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: orange;'>No articles found in the database.</p>";
}

echo "<p><a href='add-news.php'>← Back to Add News</a></p>";
?>

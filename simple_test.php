<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Simple Website Test</h1>";

// Test database connection
try {
    require_once 'config/database.php';
    echo "<p style='color: green;'>✓ Database connected</p>";
    
    // Get some news
    $query = "SELECT title, content, status FROM news WHERE status = 'published' ORDER BY created_at DESC LIMIT 3";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) > 0) {
        echo "<h2>Latest Published News:</h2>";
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
            echo "<h3>" . htmlspecialchars($row['title']) . "</h3>";
            echo "<p>" . substr(strip_tags($row['content']), 0, 200) . "...</p>";
            echo "<small>Status: " . $row['status'] . "</small>";
            echo "</div>";
        }
    } else {
        echo "<p style='color: orange;'>No published news found</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h2>Server Info:</h2>";
echo "<p>PHP Version: " . PHP_VERSION . "</p>";
echo "<p>Current URL: " . $_SERVER['REQUEST_URI'] . "</p>";
echo "<p>Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "</p>";

echo "<hr>";
echo "<a href='index.php'>Go to Homepage</a> | <a href='debug_website.php'>Full Debug</a>";
?>

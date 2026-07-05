<?php
require_once 'config/database.php';

echo "<h2>News Debug Information</h2>";

echo "<h3>Database Connection</h3>";
if (isset($conn) && $conn) {
    echo "<p style='color: green;'>✅ Database connection: SUCCESS</p>";
    
    echo "<h3>News Table Analysis</h3>";
    
    // Check total news count
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM news");
    $row = mysqli_fetch_assoc($result);
    echo "<p><strong>Total news articles:</strong> " . $row['count'] . "</p>";
    
    // Check published news count
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM news WHERE status = 'published'");
    $row = mysqli_fetch_assoc($result);
    echo "<p><strong>Published news articles:</strong> " . $row['count'] . "</p>";
    
    // Check featured news count
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM news WHERE status = 'featured'");
    $row = mysqli_fetch_assoc($result);
    echo "<p><strong>Featured news articles:</strong> " . $row['count'] . "</p>";
    
    echo "<h3>Recent Articles (Last 10)</h3>";
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Title</th><th>Status</th><th>Published At</th><th>Created At</th><th>Image</th></tr>";
    
    $result = mysqli_query($conn, "SELECT id, title, status, published_at, created_at, image FROM news ORDER BY created_at DESC LIMIT 10");
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars(substr($row['title'], 0, 50)) . "</td>";
        echo "<td><strong>" . $row['status'] . "</strong></td>";
        echo "<td>" . $row['published_at'] . "</td>";
        echo "<td>" . $row['created_at'] . "</td>";
        echo "<td>" . ($row['image'] ? '✅' : '❌') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3>Published Articles (Last 5)</h3>";
    $result = mysqli_query($conn, "SELECT id, title, status, published_at, created_at FROM news WHERE status = 'published' ORDER BY published_at DESC LIMIT 5");
    if (mysqli_num_rows($result) > 0) {
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Title</th><th>Published At</th><th>Slug</th></tr>";
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . htmlspecialchars($row['title']) . "</td>";
            echo "<td>" . $row['published_at'] . "</td>";
            echo "<td><a href='news.php?slug=" . $row['id'] . "' target='_blank'>Test Link</a></td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>❌ No published articles found!</p>";
    }
    
    echo "<h3>Categories Check</h3>";
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM categories");
    $row = mysqli_fetch_assoc($result);
    echo "<p><strong>Total categories:</strong> " . $row['count'] . "</p>";
    
    echo "<h3>Index.php Query Test</h3>";
    $latest_query = "SELECT n.*, c.name as category_name, u.name as author_name
                    FROM news n 
                    LEFT JOIN categories c ON n.category_id = c.id 
                    LEFT JOIN users u ON n.author_id = u.id 
                    WHERE n.status = 'published' AND n.published_at <= NOW() 
                    ORDER BY n.published_at DESC LIMIT 5";
    
    $result = mysqli_query($conn, $latest_query);
    if ($result) {
        echo "<p style='color: green;'>✅ Query executed successfully</p>";
        echo "<p><strong>Results found:</strong> " . mysqli_num_rows($result) . "</p>";
        
        if (mysqli_num_rows($result) > 0) {
            echo "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr><th>Title</th><th>Category</th><th>Author</th><th>Published At</th></tr>";
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['title']) . "</td>";
                echo "<td>" . htmlspecialchars($row['category_name'] ?? 'No Category') . "</td>";
                echo "<td>" . htmlspecialchars($row['author_name'] ?? 'No Author') . "</td>";
                echo "<td>" . $row['published_at'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    } else {
        echo "<p style='color: red;'>❌ Query failed: " . mysqli_error($conn) . "</p>";
    }
    
} else {
    echo "<p style='color: red;'>❌ Database connection: FAILED</p>";
    echo "<p>Error: " . mysqli_connect_error() . "</p>";
}

echo "<h3>PHP Info</h3>";
echo "<p>PHP Version: " . PHP_VERSION . "</p>";
echo "<p>Current Time: " . date('Y-m-d H:i:s') . "</p>";
?>

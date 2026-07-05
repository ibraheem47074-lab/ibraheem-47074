<?php
require_once '../config/database.php';

echo "<h2>Database Investigation - Published Articles</h2>";

// Check if news table exists
$news_table_check = mysqli_query($conn, "SHOW TABLES LIKE 'news'");
echo "<p><strong>News Table Exists:</strong> " . (mysqli_num_rows($news_table_check) > 0 ? "Yes" : "No") . "</p>";

if (mysqli_num_rows($news_table_check) > 0) {
    // Check total articles
    $total_result = mysqli_query($conn, "SELECT COUNT(*) as count FROM news");
    $total = mysqli_fetch_assoc($total_result)['count'];
    echo "<p><strong>Total Articles:</strong> $total</p>";
    
    if ($total > 0) {
        // Check status column
        $status_check = mysqli_query($conn, "SHOW COLUMNS FROM news LIKE 'status'");
        echo "<p><strong>Status Column Exists:</strong> " . (mysqli_num_rows($status_check) > 0 ? "Yes" : "No") . "</p>";
        
        if (mysqli_num_rows($status_check) > 0) {
            // Show all distinct status values
            $status_query = "SELECT status, COUNT(*) as count FROM news GROUP BY status";
            $status_result = mysqli_query($conn, $status_query);
            echo "<h3>Status Breakdown:</h3>";
            echo "<table border='1' cellpadding='5'>";
            echo "<tr><th>Status</th><th>Count</th></tr>";
            while ($row = mysqli_fetch_assoc($status_result)) {
                echo "<tr><td>" . htmlspecialchars($row['status']) . "</td><td>" . $row['count'] . "</td></tr>";
            }
            echo "</table>";
            
            // Show sample articles
            echo "<h3>Sample Articles:</h3>";
            $sample_query = "SELECT id, title, status, created_at FROM news LIMIT 5";
            $sample_result = mysqli_query($conn, $sample_query);
            echo "<table border='1' cellpadding='5'>";
            echo "<tr><th>ID</th><th>Title</th><th>Status</th><th>Created</th></tr>";
            while ($row = mysqli_fetch_assoc($sample_result)) {
                echo "<tr>";
                echo "<td>" . $row['id'] . "</td>";
                echo "<td>" . htmlspecialchars(substr($row['title'], 0, 50)) . "</td>";
                echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                echo "<td>" . $row['created_at'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p><strong>Status column doesn't exist!</strong></p>";
            // Show columns that do exist
            $columns_query = "SHOW COLUMNS FROM news";
            $columns_result = mysqli_query($conn, $columns_query);
            echo "<h3>Available Columns:</h3>";
            echo "<ul>";
            while ($row = mysqli_fetch_assoc($columns_result)) {
                echo "<li>" . $row['Field'] . " (" . $row['Type'] . ")</li>";
            }
            echo "</ul>";
        }
    } else {
        echo "<p><strong>No articles found in news table!</strong></p>";
    }
}

// Test query for published articles
echo "<h3>Test Query for Published Articles:</h3>";
$published_test = mysqli_query($conn, "SELECT COUNT(*) as count FROM news WHERE status = 'published'");
if ($published_test) {
    $published_count = mysqli_fetch_assoc($published_test)['count'];
    echo "<p>Published count: $published_count</p>";
} else {
    echo "<p>Query failed: " . mysqli_error($conn) . "</p>";
}
?>

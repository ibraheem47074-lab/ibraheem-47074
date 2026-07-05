<?php
/**
 * Check RSS news sources database
 */
require_once __DIR__ . '/config/database.php';

try {
    $query = "SELECT * FROM news_sources WHERE type = 'rss' AND status = 'active' ORDER BY name ASC";
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        die("Error: " . mysqli_error($conn));
    }
    
    echo "<h2>Active RSS News Sources</h2>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Name</th><th>URL</th><th>Category</th><th>Last Scraped</th></tr>";
    
    while ($source = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>{$source['id']}</td>";
        echo "<td>{$source['name']}</td>";
        echo "<td><a href='{$source['url']}' target='_blank'>" . substr($source['url'], 0, 50) . "...</a></td>";
        echo "<td>{$source['category_id']}</td>";
        echo "<td>{$source['last_scraped']}</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    echo "<p>Total sources: " . mysqli_num_rows($result) . "</p>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>

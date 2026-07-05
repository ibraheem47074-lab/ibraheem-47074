<?php
require_once '../config/database.php';

echo "<h1>Add Urgency Column to News Table</h1>";

// Check if urgency column already exists
$check_query = "SHOW COLUMNS FROM news LIKE 'urgency'";
$check_result = mysqli_query($conn, $check_query);

if (mysqli_num_rows($check_result) > 0) {
    echo "<p style='color: green;'>✅ Urgency column already exists in news table!</p>";
} else {
    // Add the urgency column
    $alter_query = "ALTER TABLE news ADD COLUMN urgency VARCHAR(20) DEFAULT 'medium' AFTER sentiment_label";
    
    echo "<h2>Adding urgency column...</h2>";
    echo "<p>SQL: " . htmlspecialchars($alter_query) . "</p>";
    
    if (mysqli_query($conn, $alter_query)) {
        echo "<p style='color: green;'>✅ Success: Urgency column added to news table!</p>";
        
        // Update existing articles to have 'medium' as default
        $update_query = "UPDATE news SET urgency = 'medium' WHERE urgency IS NULL OR urgency = ''";
        if (mysqli_query($conn, $update_query)) {
            echo "<p style='color: green;'>✅ Success: Existing articles updated with default urgency!</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ Warning: Could not update existing articles: " . mysqli_error($conn) . "</p>";
        }
        
    } else {
        echo "<p style='color: red;'>❌ Error adding urgency column: " . mysqli_error($conn) . "</p>";
    }
}

// Show current table structure
echo "<h2>Current News Table Structure (relevant columns):</h2>";
$structure_query = "DESCRIBE news";
$structure_result = mysqli_query($conn, $structure_query);

if ($structure_result) {
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f0f0f0;'><th>Field</th><th>Type</th><th>Null</th><th>Default</th></tr>";
    
    while ($row = mysqli_fetch_assoc($structure_result)) {
        // Show only relevant columns
        if (in_array($row['Field'], ['id', 'title', 'status', 'urgency', 'author_id', 'video_url', 'video_path', 'category_id'])) {
            echo "<tr>";
            echo "<td><strong>{$row['Field']}</strong></td>";
            echo "<td>{$row['Type']}</td>";
            echo "<td>{$row['Null']}</td>";
            echo "<td>" . htmlspecialchars($row['Default'] ?? 'NULL') . "</td>";
            echo "</tr>";
        }
    }
    echo "</table>";
}

echo "<p><a href='add-news.php'>← Back to Add News</a></p>";
echo "<p><a href='../index.php'>→ View Index Page</a></p>";
?>

<?php
require_once '../config/database.php';

echo "<h1>Check News Table Structure</h1>";

// Check if table exists
$table_check = mysqli_query($conn, "DESCRIBE news");

if ($table_check) {
    echo "<h2>News Table Structure:</h2>";
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f0f0f0;'>";
    echo "<th>Field</th>";
    echo "<th>Type</th>";
    echo "<th>Null</th>";
    echo "<th>Key</th>";
    echo "</tr>";
    
    while ($row = mysqli_fetch_assoc($table_check)) {
        echo "<tr>";
        echo "<td><strong>{$row['Field']}</strong></td>";
        echo "<td>{$row['Type']}</td>";
        echo "<td>{$row['Null']}</td>";
        echo "<td>{$row['Key']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>News table not found.</p>";
}

// Check if video_path column exists
echo "<h2>Check for video_path Column:</h2>";
$column_check = mysqli_query($conn, "SHOW COLUMNS FROM news LIKE 'video_path'");

if ($column_check && mysqli_num_rows($column_check) > 0) {
    echo "<p style='color: green;'>✓ video_path column exists</p>";
    
    $column_info = mysqli_fetch_assoc($column_check);
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><td><strong>Field</strong></td><td>{$column_info['Field']}</td></tr>";
    echo "<tr><td><strong>Type</strong></td><td>{$column_info['Type']}</td></tr>";
    echo "<tr><td><strong>Null</strong></td><td>{$column_info['Null']}</td></tr>";
    echo "</table>";
} else {
    echo "<p style='color: red;'>✗ video_path column does NOT exist</p>";
}

// Check total articles count
echo "<h2>Total Articles Count:</h2>";
$count_query = "SELECT COUNT(*) as total FROM news";
$count_result = mysqli_query($conn, $count_query);
if ($count_row = mysqli_fetch_assoc($count_result)) {
    echo "<p>Total articles: {$count_row['total']}</p>";
}

// Check articles with video_path
echo "<h2>Articles with video_path:</h2>";
$video_count_query = "SELECT COUNT(*) as total FROM news WHERE video_path IS NOT NULL AND video_path != ''";
$video_count_result = mysqli_query($conn, $video_count_query);
if ($video_count_row = mysqli_fetch_assoc($video_count_result)) {
    echo "<p>Articles with video_path: {$video_count_row['total']}</p>";
}

echo "<p><a href='simple_test.php'>← Back to Test</a></p>";
?>

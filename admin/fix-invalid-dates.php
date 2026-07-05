<?php
/**
 * Fix Invalid Dates Script
 * This script fixes all invalid dates in the news table
 */

require_once '../config/database.php';

echo "<h1>Fix Invalid Dates in News Table</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .info { color: blue; }
    table { border-collapse: collapse; width: 100%; margin: 20px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
    .fixed { background-color: #d4edda; }
    .invalid { background-color: #f8d7da; }
</style>";

// Check for invalid dates
$invalid_dates_query = "SELECT id, title, created_at, published_at, updated_at 
                       FROM news 
                       WHERE created_at = '0000-00-00 00:00:00' 
                       OR published_at = '0000-00-00 00:00:00' 
                       OR updated_at = '0000-00-00 00:00:00'
                       OR created_at IS NULL 
                       OR published_at IS NULL 
                       OR updated_at IS NULL
                       OR created_at = ''
                       OR published_at = ''
                       OR updated_at = ''";

$result = mysqli_query($conn, $invalid_dates_query);
$invalid_count = mysqli_num_rows($result);

echo "<h2>Found $invalid_count articles with invalid dates</h2>";

if ($invalid_count > 0) {
    echo "<table>";
    echo "<tr>
            <th>ID</th>
            <th>Title</th>
            <th>Created At</th>
            <th>Published At</th>
            <th>Updated At</th>
            <th>Status</th>
          </tr>";
    
    $fixed_count = 0;
    $current_time = date('Y-m-d H:i:s');
    
    while ($row = mysqli_fetch_assoc($result)) {
        $id = $row['id'];
        $title = htmlspecialchars(substr($row['title'], 0, 50)) . '...';
        
        // Determine which dates are invalid
        $fix_created = empty($row['created_at']) || $row['created_at'] === '0000-00-00 00:00:00';
        $fix_published = empty($row['published_at']) || $row['published_at'] === '0000-00-00 00:00:00';
        $fix_updated = empty($row['updated_at']) || $row['updated_at'] === '0000-00-00 00:00:00';
        
        echo "<tr class='invalid'>";
        echo "<td>$id</td>";
        echo "<td>$title</td>";
        echo "<td>" . ($row['created_at'] ?: 'NULL') . "</td>";
        echo "<td>" . ($row['published_at'] ?: 'NULL') . "</td>";
        echo "<td>" . ($row['updated_at'] ?: 'NULL') . "</td>";
        echo "<td>";
        
        // Fix the dates
        $update_parts = [];
        if ($fix_created) {
            $update_parts[] = "created_at = '$current_time'";
        }
        if ($fix_published) {
            $update_parts[] = "published_at = '$current_time'";
        }
        if ($fix_updated) {
            $update_parts[] = "updated_at = '$current_time'";
        }
        
        if (!empty($update_parts)) {
            $update_query = "UPDATE news SET " . implode(', ', $update_parts) . " WHERE id = $id";
            
            if (mysqli_query($conn, $update_query)) {
                echo "<span class='success'>Fixed</span>";
                $fixed_count++;
            } else {
                echo "<span class='error'>Error: " . mysqli_error($conn) . "</span>";
            }
        } else {
            echo "<span class='info'>No fix needed</span>";
        }
        
        echo "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    echo "<h2 class='success'>Fixed $fixed_count articles</h2>";
    
} else {
    echo "<p class='success'>No invalid dates found! All dates are valid.</p>";
}

// Check for views that are NULL or negative
echo "<h2>Check Views Column</h2>";
$views_query = "SELECT id, title, views FROM news WHERE views IS NULL OR views < 0";
$views_result = mysqli_query($conn, $views_query);
$invalid_views_count = mysqli_num_rows($views_result);

if ($invalid_views_count > 0) {
    echo "<p class='error'>Found $invalid_views_count articles with invalid views</p>";
    
    while ($row = mysqli_fetch_assoc($views_result)) {
        $update_query = "UPDATE news SET views = 0 WHERE id = " . $row['id'];
        mysqli_query($conn, $update_query);
        echo "<p class='success'>Fixed views for article ID: " . $row['id'] . " - " . htmlspecialchars(substr($row['title'], 0, 30)) . "...</p>";
    }
} else {
    echo "<p class='success'>All views values are valid!</p>";
}

// Show overall statistics
echo "<h2>Overall Statistics</h2>";
$stats_query = "SELECT 
    COUNT(*) as total_articles,
    COUNT(CASE WHEN created_at != '0000-00-00 00:00:00' AND created_at IS NOT NULL AND created_at != '' THEN 1 END) as valid_created,
    COUNT(CASE WHEN published_at != '0000-00-00 00:00:00' AND published_at IS NOT NULL AND published_at != '' THEN 1 END) as valid_published,
    COUNT(CASE WHEN updated_at != '0000-00-00 00:00:00' AND updated_at IS NOT NULL AND updated_at != '' THEN 1 END) as valid_updated,
    COUNT(CASE WHEN views IS NOT NULL AND views >= 0 THEN 1 END) as valid_views
    FROM news";

$stats_result = mysqli_query($conn, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);

echo "<table>";
echo "<tr><th>Total Articles</th><td>" . $stats['total_articles'] . "</td></tr>";
echo "<tr><th>Valid Created Dates</th><td>" . $stats['valid_created'] . "</td></tr>";
echo "<tr><th>Valid Published Dates</th><td>" . $stats['valid_published'] . "</td></tr>";
echo "<tr><th>Valid Updated Dates</th><td>" . $stats['valid_updated'] . "</td></tr>";
echo "<tr><th>Valid Views</th><td>" . $stats['valid_views'] . "</td></tr>";
echo "</table>";

echo "<p><a href='manage-news.php' class='btn btn-primary'>Back to Manage News</a></p>";
echo "<p><a href='../index.php' class='btn btn-secondary'>View Website</a></p>";

mysqli_close($conn);
?>

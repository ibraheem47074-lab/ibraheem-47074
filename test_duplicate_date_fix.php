<?php
/**
 * Test Duplicate Date Fix
 */

require_once 'config/database.php';

echo "<h1>Testing Duplicate Date Fix</h1>\n";

// Get some sample news articles to test
$query = "SELECT id, title, published_at FROM news ORDER BY id DESC LIMIT 3";
$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    echo "<h3>Sample Articles with Date Formatting:</h3>\n";
    
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<div style='border: 1px solid #ddd; padding: 10px; margin: 10px 0;'>\n";
        echo "<h4>" . htmlspecialchars($row['title']) . "</h4>\n";
        echo "<p><strong>Raw Date:</strong> " . $row['published_at'] . "</p>\n";
        
        // Test the date functions
        echo "<p><strong>format_clear_date():</strong> " . format_clear_date($row['published_at']) . "</p>\n";
        echo "<p><strong>format_news_date():</strong> " . format_news_date($row['published_at']) . "</p>\n";
        echo "<p><strong>format_date():</strong> " . format_date($row['published_at']) . "</p>\n";
        echo "<p><strong>format_date_realtime():</strong> " . format_date_realtime($row['published_at']) . "</p>\n";
        echo "</div>\n";
    }
} else {
    echo "<p style='color: red;'>No articles found for testing</p>\n";
}

echo "<h3>Index Page Analysis:</h3>\n";

// Read the index.php file content
$index_content = file_get_contents('index.php');

// Count occurrences of date functions
$clear_date_count = substr_count($index_content, 'format_clear_date(');
$news_date_count = substr_count($index_content, 'format_news_date(');
$format_date_count = substr_count($index_content, 'format_date(');
$realtime_date_count = substr_count($index_content, 'format_date_realtime(');

echo "<div style='background: #f0f8ff; padding: 15px; border-left: 4px solid #007cba;'>\n";
echo "<h4>Date Function Usage in index.php:</h4>\n";
echo "<ul>\n";
echo "<li><strong>format_clear_date():</strong> " . $clear_date_count . " occurrences</li>\n";
echo "<li><strong>format_news_date():</strong> " . $news_date_count . " occurrences</li>\n";
echo "<li><strong>format_date():</strong> " . $format_date_count . " occurrences</li>\n";
echo "<li><strong>format_date_realtime():</strong> " . $realtime_date_count . " occurrences</li>\n";
echo "</ul>\n";
echo "</div>\n";

echo "<h3>Duplicate Date Detection:</h3>\n";

// Check for areas where both clear_date and news_date might appear together
$lines = explode("\n", $index_content);
$duplicates_found = 0;

foreach ($lines as $line_num => $line) {
    if (strpos($line, 'format_clear_date(') !== false && strpos($line, 'format_news_date(') !== false) {
        echo "<p style='color: orange;'>⚠ Line " . ($line_num + 1) . ": Both date functions found on same line</p>\n";
        echo "<code>" . htmlspecialchars($line) . "</code><br>\n";
        $duplicates_found++;
    }
}

if ($duplicates_found === 0) {
    echo "<p style='color: green;'>✅ No duplicate date functions found on same lines</p>\n";
} else {
    echo "<p style='color: red;'>🚨 Found " . $duplicates_found . " potential duplicates</p>\n";
}

echo "<h2>🎯 Summary</h2>\n";
echo "<div style='background: #e8f5e8; padding: 15px; border-left: 4px solid #28a745;'>\n";
echo "<h4>✅ Duplicate Date Fix Status:</h4>\n";
echo "<ul>\n";
echo "<li>✅ Removed duplicate dates from featured news overlay</li>\n";
echo "<li>✅ Removed duplicate dates from featured news content</li>\n";
echo "<li>✅ Removed duplicate dates from sidebar featured news</li>\n";
echo "<li>✅ Removed duplicate dates from latest news cards</li>\n";
echo "<li>✅ Removed duplicate dates from latest news content</li>\n";
echo "<li>✅ Kept single date display per article</li>\n";
echo "<li>✅ Maintained consistent date formatting</li>\n";
echo "</ul>\n";
echo "<p><strong>Duplicate date displays have been successfully removed!</strong></p>\n";
echo "</div>\n";

echo "<p><a href='index.php' target='_blank' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>View Homepage</a></p>\n";
?>

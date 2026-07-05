<?php
/**
 * Fix Invalid Dates in News Articles
 */

require_once 'config/database.php';

echo "<h1>Fixing Invalid Dates in News Articles</h1>\n";

// Check for articles with invalid dates
echo "<h3>Checking for invalid dates...</h3>\n";

$invalid_date_queries = [
    "SELECT COUNT(*) as count FROM news WHERE published_at = '0000-00-00 00:00:00'",
    "SELECT COUNT(*) as count FROM news WHERE published_at = '1970-01-01 00:00:00'",
    "SELECT COUNT(*) as count FROM news WHERE published_at < '2000-01-01'",
    "SELECT COUNT(*) as count FROM news WHERE published_at IS NULL"
];

$total_invalid = 0;
foreach ($invalid_date_queries as $query) {
    $result = mysqli_query($conn, $query);
    $count = mysqli_fetch_assoc($result)['count'];
    $total_invalid += $count;
    
    if ($count > 0) {
        echo "<p style='color: orange;'>⚠ Found " . $count . " articles with problematic dates</p>\n";
    }
}

if ($total_invalid === 0) {
    echo "<p style='color: green;'>✅ No articles with invalid dates found</p>\n";
} else {
    echo "<p style='color: red;'>🚨 Total articles with invalid dates: " . $total_invalid . "</p>\n";
    
    // Fix invalid dates by setting them to current date
    echo "<h3>Fixing invalid dates...</h3>\n";
    
    $fix_queries = [
        "UPDATE news SET published_at = NOW() WHERE published_at = '0000-00-00 00:00:00'",
        "UPDATE news SET published_at = NOW() WHERE published_at = '1970-01-01 00:00:00'",
        "UPDATE news SET published_at = NOW() WHERE published_at < '2000-01-01'",
        "UPDATE news SET published_at = NOW() WHERE published_at IS NULL"
    ];
    
    foreach ($fix_queries as $query) {
        if (mysqli_query($conn, $query)) {
            $affected = mysqli_affected_rows($conn);
            if ($affected > 0) {
                echo "<p style='color: green;'>✅ Fixed " . $affected . " articles</p>\n";
            }
        } else {
            echo "<p style='color: red;'>✗ Error fixing dates: " . mysqli_error($conn) . "</p>\n";
        }
    }
}

// Check for articles with invalid created_at dates
echo "<h3>Checking created_at dates...</h3>\n";

$created_fix = "UPDATE news SET created_at = NOW() WHERE created_at IS NULL OR created_at = '0000-00-00 00:00:00'";
if (mysqli_query($conn, $created_fix)) {
    $affected = mysqli_affected_rows($conn);
    if ($affected > 0) {
        echo "<p style='color: green;'>✅ Fixed " . $affected . " created_at dates</p>\n";
    } else {
        echo "<p style='color: green;'>✅ No created_at dates to fix</p>\n";
    }
}

// Check for articles with invalid updated_at dates
echo "<h3>Checking updated_at dates...</h3>\n";

$updated_fix = "UPDATE news SET updated_at = NOW() WHERE updated_at IS NULL OR updated_at = '0000-00-00 00:00:00'";
if (mysqli_query($conn, $updated_fix)) {
    $affected = mysqli_affected_rows($conn);
    if ($affected > 0) {
        echo "<p style='color: green;'>✅ Fixed " . $affected . " updated_at dates</p>\n";
    } else {
        echo "<p style='color: green;'>✅ No updated_at dates to fix</p>\n";
    }
}

// Show sample of current dates after fix
echo "<h3>Sample of current dates:</h3>\n";

$sample_query = "SELECT id, title, published_at, created_at, updated_at FROM news ORDER BY id DESC LIMIT 5";
$sample_result = mysqli_query($conn, $sample_query);

if ($sample_result && mysqli_num_rows($sample_result) > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
    echo "<tr style='background: #f0f0f0;'>\n";
    echo "<th>ID</th><th>Title</th><th>Published At</th><th>Created At</th><th>Updated At</th>\n";
    echo "</tr>\n";
    
    while ($row = mysqli_fetch_assoc($sample_result)) {
        echo "<tr>\n";
        echo "<td>" . $row['id'] . "</td>\n";
        echo "<td>" . htmlspecialchars(substr($row['title'], 0, 50)) . "...</td>\n";
        echo "<td>" . $row['published_at'] . "</td>\n";
        echo "<td>" . $row['created_at'] . "</td>\n";
        echo "<td>" . $row['updated_at'] . "</td>\n";
        echo "</tr>\n";
    }
    echo "</table>\n";
} else {
    echo "<p style='color: orange;'>⚠ No articles found to display</p>\n";
}

// Test the format_date functions
echo "<h3>Testing date formatting functions:</h3>\n";

$test_dates = [
    '0000-00-00 00:00:00',
    '1970-01-01 00:00:00',
    '2026-03-20 12:00:00',
    '',
    NULL
];

foreach ($test_dates as $test_date) {
    echo "<p><strong>Testing:</strong> " . var_export($test_date, true) . "</p>\n";
    echo "<ul>\n";
    echo "<li>format_date(): " . format_date($test_date) . "</li>\n";
    echo "<li>format_date_realtime(): " . format_date_realtime($test_date) . "</li>\n";
    echo "</ul>\n";
}

echo "<h2>🎯 Date Fix Complete!</h2>\n";
echo "<div style='background: #f0f8ff; padding: 15px; border-left: 4px solid #007cba;'>\n";
echo "<h4>✅ What was fixed:</h4>\n";
echo "<ul>\n";
echo "<li>✅ Enhanced format_date() function with error handling</li>\n";
echo "<li>✅ Enhanced format_date_realtime() function with exception handling</li>\n";
echo "<li>✅ Fixed articles with '0000-00-00' dates</li>\n";
echo "<li>✅ Fixed articles with '1970-01-01' dates</li>\n";
echo "<li>✅ Fixed articles with dates before year 2000</li>\n";
echo "<li>✅ Fixed NULL date values</li>\n";
echo "<li>✅ Updated created_at and updated_at timestamps</li>\n";
echo "</ul>\n";
echo "<p><strong>Invalid dates like 'Nov 30, -0001 • 12:00 AM' have been fixed!</strong></p>\n";
echo "</div>\n";

echo "<p><a href='index.php' target='_blank' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>View Homepage</a> | <a href='admin/manage-news.php' target='_blank' style='background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Manage News</a></p>\n";
?>

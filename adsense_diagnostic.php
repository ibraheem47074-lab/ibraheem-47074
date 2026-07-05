<?php
require_once 'config/database.php';

header('Content-Type: text/plain');

echo "=== ADSENSE APPROVAL DIAGNOSTIC REPORT ===\n\n";

// Check published articles count
$result = mysqli_query($conn, "SELECT COUNT(*) as total FROM news WHERE status = 'published'");
$row = mysqli_fetch_assoc($result);
echo "1. Published Articles: " . $row['total'] . "\n";

// Check content sources
$result = mysqli_query($conn, "SELECT source, COUNT(*) as count FROM news WHERE status = 'published' GROUP BY source ORDER BY count DESC");
echo "\n2. Content by Source:\n";
while ($row = mysqli_fetch_assoc($result)) {
    echo "   - " . $row['source'] . ": " . $row['count'] . " articles\n";
}

// Check categories
$result = mysqli_query($conn, "SELECT COUNT(*) as total FROM categories");
$row = mysqli_fetch_assoc($result);
echo "\n3. Total Categories: " . $row['total'] . "\n";

// Check for articles with images
$image_check = mysqli_query($conn, "SHOW COLUMNS FROM news LIKE 'image_url'");
if (mysqli_num_rows($image_check) > 0) {
    $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM news WHERE status = 'published' AND image_url IS NOT NULL AND image_url != ''");
    $row = mysqli_fetch_assoc($result);
    echo "\n4. Articles with Images: " . $row['total'] . "\n";
} else {
    echo "\n4. Articles with Images: Not available (no image_url column)\n";
}

// Check article length (content quality)
$content_check = mysqli_query($conn, "SHOW COLUMNS FROM news LIKE 'content'");
if (mysqli_num_rows($content_check) > 0) {
    $result = mysqli_query($conn, "SELECT AVG(LENGTH(content)) as avg_length FROM news WHERE status = 'published'");
    $row = mysqli_fetch_assoc($result);
    echo "\n5. Average Article Length: " . round($row['avg_length']) . " characters\n";
} else {
    echo "\n5. Average Article Length: Not available (no content column)\n";
}

// Check for duplicate titles
$result = mysqli_query($conn, "SELECT title, COUNT(*) as count FROM news WHERE status = 'published' GROUP BY title HAVING count > 1");
$duplicates = mysqli_num_rows($result);
echo "\n6. Duplicate Article Titles: " . $duplicates . "\n";

// Check recent articles
$date_check = mysqli_query($conn, "SHOW COLUMNS FROM news LIKE 'published_at'");
if (mysqli_num_rows($date_check) > 0) {
    $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM news WHERE status = 'published' AND published_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
    $row = mysqli_fetch_assoc($result);
    echo "\n7. Articles Published (Last 7 Days): " . $row['total'] . "\n";
} else {
    echo "\n7. Articles Published (Last 7 Days): Not available (no published_at column)\n";
}

// Check for RSS imported content
$source_url_check = mysqli_query($conn, "SHOW COLUMNS FROM news LIKE 'source_url'");
if (mysqli_num_rows($source_url_check) > 0) {
    $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM news WHERE status = 'published' AND source_url IS NOT NULL AND source_url != ''");
    $row = mysqli_fetch_assoc($result);
    echo "\n8. RSS Imported Articles: " . $row['total'] . "\n";

    // Check for original content
    $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM news WHERE status = 'published' AND (source_url IS NULL OR source_url = '')");
    $row = mysqli_fetch_assoc($result);
    echo "\n9. Original Articles: " . $row['total'] . "\n";
} else {
    echo "\n8. RSS Imported Articles: Not available (no source_url column)\n";
    echo "\n9. Original Articles: Not available (no source_url column)\n";
}

echo "\n=== RECOMMENDATIONS ===\n";
echo "- Need at least 30-50 high-quality original articles\n";
echo "- Articles should be 500+ words each\n";
echo "- Mix of original and curated content (max 30% RSS)\n";
echo "- Regular publishing schedule (2-3 articles/week)\n";
echo "- All articles should have images\n";
?>

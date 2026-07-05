<?php
require_once 'config/database.php';

echo "=== AdSense Issues Fix Script ===\n\n";

// 1. Find and display duplicate titles
echo "1. Checking for duplicate article titles...\n";
$duplicate_query = "SELECT title, COUNT(*) as count, GROUP_CONCAT(id) as ids FROM news WHERE status = 'published' GROUP BY title HAVING count > 1";
$result = mysqli_query($conn, $duplicate_query);

if (mysqli_num_rows($result) > 0) {
    echo "Found " . mysqli_num_rows($result) . " duplicate titles:\n";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "  - '{$row['title']}' appears {$row['count']} times (IDs: {$row['ids']})\n";
    }
} else {
    echo "No duplicate titles found.\n";
}

// 2. Check if source column exists
echo "\n2. Checking for source column...\n";
$source_check = mysqli_query($conn, "SHOW COLUMNS FROM news LIKE 'source'");
if (mysqli_num_rows($source_check) > 0) {
    echo "✅ Source column exists.\n";
} else {
    echo "❌ Source column missing. Adding it...\n";
    $add_source = "ALTER TABLE news ADD COLUMN source VARCHAR(100) DEFAULT 'PK-LIVE' AFTER content";
    if (mysqli_query($conn, $add_source)) {
        echo "✅ Source column added successfully.\n";
    } else {
        echo "❌ Error adding source column: " . mysqli_error($conn) . "\n";
    }
}

// 3. Check if image_url column exists
echo "\n3. Checking for image_url column...\n";
$image_check = mysqli_query($conn, "SHOW COLUMNS FROM news LIKE 'image_url'");
if (mysqli_num_rows($image_check) > 0) {
    echo "✅ image_url column exists.\n";
} else {
    echo "❌ image_url column missing. Adding it...\n";
    // First check if 'image' column exists and has data
    $image_col_check = mysqli_query($conn, "SHOW COLUMNS FROM news LIKE 'image'");
    if (mysqli_num_rows($image_col_check) > 0) {
        // Add image_url column and copy data from image column
        $add_image_url = "ALTER TABLE news ADD COLUMN image_url VARCHAR(500) AFTER image";
        if (mysqli_query($conn, $add_image_url)) {
            echo "✅ image_url column added. Copying data from image column...\n";
            $copy_data = "UPDATE news SET image_url = image WHERE image IS NOT NULL AND image != ''";
            if (mysqli_query($conn, $copy_data)) {
                echo "✅ Data copied successfully.\n";
            }
        } else {
            echo "❌ Error adding image_url column: " . mysqli_error($conn) . "\n";
        }
    } else {
        // Just add the column
        $add_image_url = "ALTER TABLE news ADD COLUMN image_url VARCHAR(500) AFTER content";
        if (mysqli_query($conn, $add_image_url)) {
            echo "✅ image_url column added successfully.\n";
        } else {
            echo "❌ Error adding image_url column: " . mysqli_error($conn) . "\n";
        }
    }
}

// 4. Check articles with images after fix
echo "\n4. Checking articles with images...\n";
$image_check = mysqli_query($conn, "SHOW COLUMNS FROM news LIKE 'image_url'");
if (mysqli_num_rows($image_check) > 0) {
    $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM news WHERE status = 'published' AND image_url IS NOT NULL AND image_url != ''");
    $row = mysqli_fetch_assoc($result);
    $image_count = $row['total'];
    
    $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM news WHERE status = 'published'");
    $row = mysqli_fetch_assoc($result);
    $total_count = $row['total'];
    
    $percentage = $total_count > 0 ? round(($image_count / $total_count) * 100, 1) : 0;
    echo "Articles with images: {$image_count} ({$percentage}%)\n";
}

// 5. Update source for existing articles
echo "\n5. Updating source for existing articles...\n";
$update_source = "UPDATE news SET source = 'PK-LIVE' WHERE source IS NULL OR source = ''";
if (mysqli_query($conn, $update_source)) {
    $affected = mysqli_affected_rows($conn);
    echo "✅ Updated {$affected} articles with source.\n";
}

// 6. Check average article length
echo "\n6. Checking average article length...\n";
$content_check = mysqli_query($conn, "SHOW COLUMNS FROM news LIKE 'content'");
if (mysqli_num_rows($content_check) > 0) {
    $result = mysqli_query($conn, "SELECT AVG(LENGTH(content)) as avg_length FROM news WHERE status = 'published'");
    $row = mysqli_fetch_assoc($result);
    $avg_length = round($row['avg_length']);
    $avg_words = round($avg_length / 5);
    echo "Current average: ~{$avg_words} words ({$avg_length} chars)\n";
    echo "Target: 300+ words\n";
    if ($avg_words < 300) {
        $needed = 300 - $avg_words;
        echo "Need to add ~{$needed} more words per article on average.\n";
    }
}

echo "\n=== Fix Script Complete ===\n";
echo "Next steps:\n";
echo "1. Manually fix duplicate titles by editing or deleting duplicates\n";
echo "2. Expand short articles to reach 300+ words\n";
echo "3. Run adsense_check_web.php again to verify improvements\n";

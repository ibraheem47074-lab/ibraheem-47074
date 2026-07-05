<?php
require_once '../config/database.php';

echo "PK Live News - Check News Table Structure\n";
echo "========================================\n\n";

// Check news table structure
echo "1. News Table Structure:\n";
echo "-----------------------\n";

$result = mysqli_query($conn, 'DESCRIBE news');
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        echo "- {$row['Field']} ({$row['Type']}) {$row['Null']} {$row['Key']}\n";
    }
} else {
    echo "❌ Could not get table structure\n";
}

echo "\n2. Current INSERT Query:\n";
echo "-----------------------\n";
echo "INSERT INTO news (title, slug, content, excerpt, image, video_url, video_path, category_id, author_id, status, created_at, published_at) \n";
echo "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())\n";

echo "\n3. Count Check:\n";
echo "--------------\n";
echo "Columns: title, slug, content, excerpt, image, video_url, video_path, category_id, author_id, status, created_at, published_at (12 columns)\n";
echo "Values: ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW() (13 values)\n";
echo "❌ Mismatch: 12 columns vs 13 values\n";

echo "\n4. Fixed Query:\n";
echo "--------------\n";
echo "INSERT INTO news (title, slug, content, excerpt, image, video_url, video_path, category_id, author_id, status, created_at, published_at) \n";
echo "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())\n";
echo "Columns: 12\n";
echo "Values: ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW() (11 values)\n";
echo "❌ Still mismatched\n";

echo "\n5. Correct Query:\n";
echo "----------------\n";
echo "INSERT INTO news (title, slug, content, excerpt, image, video_url, video_path, category_id, author_id, status, created_at, published_at) \n";
echo "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())\n";
echo "Remove one placeholder to match 12 columns\n";
?>

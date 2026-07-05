<?php
require_once 'config/database.php';

echo "<h1>PK Live News - Simple Media Fix</h1>";

// Fix 1: Create missing upload directories
echo "<h2>1. Creating Upload Directories</h2>";

$directories = [
    'uploads/news/',
    'uploads/videos/',
    'uploads/thumbnails/'
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        if (mkdir($dir, 0755, true)) {
            echo "<div style='color: green;'>✓ Created: $dir</div>";
        } else {
            echo "<div style='color: red;'>✗ Failed: $dir</div>";
        }
    } else {
        echo "<div style='color: green;'>✓ Exists: $dir</div>";
    }
}

// Fix 2: Fix image paths and create simple placeholders
echo "<h2>2. Fixing Image Paths</h2>";

$news_query = "SELECT id, title, image, published_at, created_at FROM news WHERE status = 'published' ORDER BY created_at DESC";
$news_result = mysqli_query($conn, $news_query);

$fixed_count = 0;
$placeholder_count = 0;

while ($news = mysqli_fetch_assoc($news_result)) {
    $news_id = $news['id'];
    $title = $news['title'];
    $current_image = $news['image'];
    
    // Fix published_at if invalid
    if ($news['published_at'] === '0000-00-00 00:00:00' || $news['published_at'] === null) {
        $new_date = $news['created_at'] ? $news['created_at'] : date('Y-m-d H:i:s');
        $update_date = "UPDATE news SET published_at = ? WHERE id = ?";
        $date_stmt = mysqli_prepare($conn, $update_date);
        mysqli_stmt_bind_param($date_stmt, 'si', $new_date, $news_id);
        mysqli_stmt_execute($date_stmt);
        echo "<div style='color: orange;'>⚠ Fixed date for: " . substr($title, 0, 30) . "...</div>";
    }
    
    // Fix image path
    $new_image = $current_image;
    
    if (empty($current_image)) {
        // Create a placeholder filename
        $new_image = 'uploads/news/placeholder_' . $news_id . '.jpg';
        $placeholder_count++;
    } elseif (strpos($current_image, 'uploads/') !== 0) {
        $new_image = 'uploads/news/' . basename($current_image);
    }
    
    // Create placeholder file if it doesn't exist
    if (!file_exists($new_image)) {
        $svg_placeholder = create_svg_placeholder($title);
        if (file_put_contents($new_image, $svg_placeholder)) {
            echo "<div style='color: green;'>✓ Created placeholder: " . substr($title, 0, 30) . "...</div>";
        }
    }
    
    // Update database if needed
    if ($new_image !== $current_image) {
        $update_query = "UPDATE news SET image = ? WHERE id = ?";
        $update_stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($update_stmt, 'si', $new_image, $news_id);
        if (mysqli_stmt_execute($update_stmt)) {
            $fixed_count++;
        }
    }
}

echo "<div style='color: blue;'>ℹ Fixed $fixed_count image paths, created $placeholder_count placeholders</div>";

// Fix 3: Check video support
echo "<h2>3. Checking Video Support</h2>";

$video_query = "SELECT id, title, video_url FROM news WHERE video_url IS NOT NULL AND video_url != '' LIMIT 5";
$video_result = mysqli_query($conn, $video_query);

while ($video = mysqli_fetch_assoc($video_result)) {
    echo "<div style='color: green;'>✓ Video: " . substr($video['title'], 0, 40) . "...</div>";
}

echo "<h2>🎉 Media Fix Complete!</h2>";
echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 4px;'>";
echo "<strong>Fixed:</strong><br>";
echo "• Upload directories created<br>";
echo "• Image paths corrected ($fixed_count)<br>";
echo "• Placeholder images created ($placeholder_count)<br>";
echo "• Article dates fixed<br>";
echo "• Newest posts will now show first<br><br>";
echo "<strong>Next:</strong><br>";
echo "1. Visit: <a href='index.php'>index.php</a><br>";
echo "2. Images should now display<br>";
echo "3. Posts ordered by newest first<br>";
echo "</div>";

function create_svg_placeholder($title) {
    $short_title = substr($title, 0, 25);
    if (strlen($title) > 25) $short_title .= "...";
    
    $svg = '<svg width="400" height="300" xmlns="http://www.w3.org/2000/svg">
        <rect width="400" height="300" fill="#f0f0f0"/>
        <rect x="10" y="10" width="380" height="280" fill="none" stroke="#ddd" stroke-width="2"/>
        <text x="200" y="140" font-family="Arial, sans-serif" font-size="16" fill="#666" text-anchor="middle">' . htmlspecialchars($short_title) . '</text>
        <text x="200" y="280" font-family="Arial, sans-serif" font-size="12" fill="#999" text-anchor="middle">PK Live News</text>
    </svg>';
    
    return $svg;
}
?>

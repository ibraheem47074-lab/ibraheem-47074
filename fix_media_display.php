<?php
require_once 'config/database.php';

echo "<h1>PK Live News - Media Display Fix</h1>";

// Fix 1: Create missing upload directories and fix paths
echo "<h2>1. Fixing Upload Directories</h2>";

$directories = [
    'uploads/news/',
    'uploads/videos/',
    'uploads/thumbnails/',
    'uploads/temp/'
];

foreach ($directories as $dir) {
    $full_path = $dir;
    if (!is_dir($full_path)) {
        if (mkdir($full_path, 0755, true)) {
            echo "<div style='color: green;'>✓ Created directory: $dir</div>";
        } else {
            echo "<div style='color: red;'>✗ Failed to create: $dir</div>";
        }
    } else {
        echo "<div style='color: green;'>✓ Directory exists: $dir</div>";
    }
}

// Fix 2: Update database image paths to correct format
echo "<h2>2. Fixing Database Image Paths</h2>";

// Check current image paths
$check_query = "SELECT id, title, image FROM news WHERE image IS NOT NULL AND image != ''";
$check_result = mysqli_query($conn, $check_query);

$fixed_count = 0;
while ($news = mysqli_fetch_assoc($check_result)) {
    $current_image = $news['image'];
    $news_id = $news['id'];
    
    // Fix common path issues
    $new_image = $current_image;
    
    // Remove duplicate uploads/ if exists
    if (strpos($current_image, 'uploads/uploads/') === 0) {
        $new_image = str_replace('uploads/uploads/', 'uploads/', $current_image);
    }
    
    // Add uploads/ prefix if missing
    if (strpos($current_image, 'uploads/') !== 0) {
        $new_image = 'uploads/news/' . basename($current_image);
    }
    
    // Update if path changed
    if ($new_image !== $current_image) {
        $update_query = "UPDATE news SET image = ? WHERE id = ?";
        $update_stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($update_stmt, 'si', $new_image, $news_id);
        if (mysqli_stmt_execute($update_stmt)) {
            echo "<div style='color: orange;'>⚠ Fixed image path for: {$news['title']}</div>";
            $fixed_count++;
        }
    }
}

echo "<div style='color: blue;'>ℹ Fixed $fixed_count image paths</div>";

// Fix 3: Create placeholder images for missing files
echo "<h2>3. Creating Placeholder Images</h2>";

$missing_images_query = "SELECT id, title, image FROM news WHERE image IS NOT NULL AND image != '' AND status = 'published'";
$missing_result = mysqli_query($conn, $missing_images_query);

$placeholder_created = 0;
while ($news = mysqli_fetch_assoc($missing_result)) {
    $image_path = $news['image'];
    
    if (!file_exists($image_path)) {
        // Create a simple placeholder image
        $placeholder_content = create_placeholder_image($news['title']);
        if (file_put_contents($image_path, $placeholder_content)) {
            echo "<div style='color: green;'>✓ Created placeholder for: {$news['title']}</div>";
            $placeholder_created++;
        } else {
            echo "<div style='color: red;'>✗ Failed to create placeholder for: {$news['title']}</div>";
        }
    }
}

echo "<div style='color: blue;'>ℹ Created $placeholder_created placeholder images</div>";

// Fix 4: Update news ordering to show newest first
echo "<h2>4. Fixing News Ordering</h2>";

// Check for articles with invalid dates
$invalid_dates_query = "SELECT id, title, published_at, created_at FROM news 
                        WHERE published_at = '0000-00-00 00:00:00' OR published_at IS NULL 
                        ORDER BY created_at DESC LIMIT 10";
$invalid_result = mysqli_query($conn, $invalid_dates_query);

$dates_fixed = 0;
while ($news = mysqli_fetch_assoc($invalid_result)) {
    $news_id = $news['id'];
    $created_at = $news['created_at'];
    
    // Use created_at as published_at if it's valid
    if ($created_at && $created_at !== '0000-00-00 00:00:00') {
        $update_query = "UPDATE news SET published_at = created_at WHERE id = ?";
        $update_stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($update_stmt, 'i', $news_id);
        if (mysqli_stmt_execute($update_stmt)) {
            echo "<div style='color: green;'>✓ Fixed date for: {$news['title']}</div>";
            $dates_fixed++;
        }
    } else {
        // Set current date if both are invalid
        $current_date = date('Y-m-d H:i:s');
        $update_query = "UPDATE news SET published_at = ? WHERE id = ?";
        $update_stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($update_stmt, 'si', $current_date, $news_id);
        if (mysqli_stmt_execute($update_stmt)) {
            echo "<div style='color: orange;'>⚠ Set current date for: {$news['title']}</div>";
            $dates_fixed++;
        }
    }
}

echo "<div style='color: blue;'>ℹ Fixed $dates_fixed article dates</div>";

// Fix 5: Test video display
echo "<h2>5. Testing Video Support</h2>";

$video_query = "SELECT id, title, video_url FROM news WHERE video_url IS NOT NULL AND video_url != '' LIMIT 5";
$video_result = mysqli_query($conn, $video_query);

$video_count = mysqli_num_rows($video_result);
echo "<div style='color: blue;'>ℹ Found $video_count articles with videos</div>";

while ($video = mysqli_fetch_assoc($video_result)) {
    echo "<div style='color: green;'>✓ Video article: {$video['title']} - {$video['video_url']}</div>";
}

echo "<h2>🎉 Media Display Fix Complete!</h2>";
echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 4px;'>";
echo "<strong>Summary:</strong><br>";
echo "• Fixed upload directories<br>";
echo "• Corrected $fixed_count image paths<br>";
echo "• Created $placeholder_created placeholder images<br>";
echo "• Fixed $dates_fixed article dates<br>";
echo "• Found $video_count video articles<br><br>";
echo "<strong>Next Steps:</strong><br>";
echo "1. Refresh your browser<br>";
echo "2. Check index page for images<br>";
echo "3. Test video playback<br>";
echo "4. Verify new posts appear first<br>";
echo "</div>";

function create_placeholder_image($title) {
    // Create a simple 400x300 placeholder with text
    $width = 400;
    $height = 300;
    
    // Create image resource
    $img = imagecreatetruecolor($width, $height);
    
    // Set colors
    $bg_color = imagecolorallocate($img, 240, 240, 240);
    $text_color = imagecolorallocate($img, 100, 100, 100);
    $border_color = imagecolorallocate($img, 200, 200, 200);
    
    // Fill background
    imagefill($img, 0, 0, $bg_color);
    
    // Add border
    imagerectangle($img, 0, 0, $width-1, $height-1, $border_color);
    
    // Add text (shortened title)
    $short_title = substr($title, 0, 30);
    if (strlen($title) > 30) $short_title .= "...";
    
    // Center text
    $font_size = 4;
    $text_width = imagefontwidth($font_size) * strlen($short_title);
    $text_height = imagefontheight($font_size);
    $x = ($width - $text_width) / 2;
    $y = ($height - $text_height) / 2;
    
    imagestring($img, $font_size, $x, $y, $short_title, $text_color);
    
    // Add "PK Live News" watermark
    $watermark = "PK Live News";
    $watermark_width = imagefontwidth(2) * strlen($watermark);
    $watermark_x = ($width - $watermark_width) / 2;
    imagestring($img, 2, $watermark_x, $height - 20, $watermark, $text_color);
    
    // Capture output
    ob_start();
    imagepng($img);
    $image_data = ob_get_contents();
    ob_end_clean();
    
    imagedestroy($img);
    return $image_data;
}
?>

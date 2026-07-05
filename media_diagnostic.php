<?php
require_once 'config/database.php';

echo "PK Live News - Media Display Diagnostic Tool\n";
echo "============================================\n\n";

// Step 1: Check database for news with images and videos
echo "1. Checking Database for Media Content\n";
echo "--------------------------------------\n";

$news_query = "SELECT id, title, image, video_url, video_path, status, created_at FROM news ORDER BY created_at DESC LIMIT 10";
$news_result = mysqli_query($conn, $news_query);

if ($news_result && mysqli_num_rows($news_result) > 0) {
    echo "✅ Found " . mysqli_num_rows($news_result) . " recent news articles:\n\n";
    
    while ($news = mysqli_fetch_assoc($news_result)) {
        echo "Article ID: {$news['id']}\n";
        echo "Title: " . substr($news['title'], 0, 50) . "...\n";
        echo "Status: {$news['status']}\n";
        echo "Image: " . ($news['image'] ? $news['image'] : 'NULL') . "\n";
        echo "Video URL: " . ($news['video_url'] ? $news['video_url'] : 'NULL') . "\n";
        echo "Video Path: " . ($news['video_path'] ? $news['video_path'] : 'NULL') . "\n";
        
        // Check if image file exists
        if ($news['image']) {
            $image_path = '../' . $news['image'];
            if (file_exists($image_path)) {
                echo "✅ Image file exists: $image_path\n";
            } else {
                echo "❌ Image file NOT found: $image_path\n";
            }
        }
        
        // Check if video file exists
        if ($news['video_path']) {
            $video_path = '../' . $news['video_path'];
            if (file_exists($video_path)) {
                echo "✅ Video file exists: $video_path\n";
            } else {
                echo "❌ Video file NOT found: $video_path\n";
            }
        }
        
        echo "----------------------------------------\n";
    }
} else {
    echo "❌ No news articles found or query failed\n";
}

// Step 2: Check upload directories
echo "\n2. Checking Upload Directories\n";
echo "-----------------------------\n";

$upload_dirs = [
    'uploads/news/',
    'uploads/news/images/',
    'uploads/news/videos/',
    'uploads/thumbnails/',
    'uploads/videos/'
];

foreach ($upload_dirs as $dir) {
    $full_path = '../' . $dir;
    if (is_dir($full_path)) {
        echo "✅ Directory exists: $dir\n";
        if (is_writable($full_path)) {
            echo "   ✅ Writable\n";
        } else {
            echo "   ❌ Not writable\n";
        }
        
        // Count files
        $files = glob($full_path . '*');
        echo "   📁 Contains " . count($files) . " files\n";
    } else {
        echo "❌ Directory missing: $dir\n";
    }
}

// Step 3: Check specific media files
echo "\n3. Checking Specific Media Files\n";
echo "--------------------------------\n";

$media_files = [
    'uploads/news/69a9497da7b35_test.png',
    'uploads/news/69ac1d286749b.jpg',
    'uploads/news/images/img_69cdfa6cec346_1775106668.webp',
    'uploads/news/videos/vid_69cdfe3996793_1775107641.mp4'
];

foreach ($media_files as $file) {
    $full_path = '../' . $file;
    if (file_exists($full_path)) {
        $size = filesize($full_path);
        echo "✅ $file (" . round($size / 1024, 2) . " KB)\n";
    } else {
        echo "❌ $file - NOT FOUND\n";
    }
}

// Step 4: Test image URLs
echo "\n4. Testing Image URL Generation\n";
echo "--------------------------------\n";

$test_news_query = "SELECT id, title, image FROM news WHERE image IS NOT NULL AND image != '' LIMIT 3";
$test_result = mysqli_query($conn, $test_news_query);

if ($test_result && mysqli_num_rows($test_result) > 0) {
    while ($news = mysqli_fetch_assoc($test_result)) {
        $image_url = $news['image'];
        echo "Article: {$news['id']}\n";
        echo "Image URL: $image_url\n";
        
        // Check if URL is accessible via web
        if (strpos($image_url, 'http') === 0) {
            echo "   🌐 External URL\n";
        } else {
            $local_path = '../' . $image_url;
            if (file_exists($local_path)) {
                echo "   📁 Local file exists\n";
            } else {
                echo "   ❌ Local file missing\n";
            }
        }
        echo "---\n";
    }
}

// Step 5: Check for common issues
echo "\n5. Common Issues Check\n";
echo "----------------------\n";

// Check if .htaccess exists in uploads
$uploads_htaccess = '../uploads/.htaccess';
if (file_exists($uploads_htaccess)) {
    echo "✅ uploads/.htaccess exists\n";
} else {
    echo "⚠️  uploads/.htaccess missing - images may not be accessible\n";
}

// Check PHP error reporting
echo "PHP Error Reporting: " . (ini_get('display_errors') ? 'ON' : 'OFF') . "\n";
echo "PHP Memory Limit: " . ini_get('memory_limit') . "\n";
echo "PHP Max Upload Size: " . ini_get('upload_max_filesize') . "\n";
echo "PHP Max Post Size: " . ini_get('post_max_size') . "\n";

echo "\n=== Diagnostic Complete ===\n";
echo "If images/videos are not showing, check:\n";
echo "1. File paths in database vs actual file locations\n";
echo "2. File permissions on upload directories\n";
echo "3. Web server access to upload directories\n";
echo "4. Correct URL generation in templates\n";
?>

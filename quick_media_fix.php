<?php
require_once 'config/database.php';

echo "PK Live News - Quick Media Fix\n";
echo "=============================\n\n";

// Step 1: Check and fix missing upload directories
echo "1. Creating Missing Upload Directories\n";
echo "--------------------------------------\n";

$directories = [
    '../uploads/news/images/',
    '../uploads/news/videos/',
    '../uploads/thumbnails/',
    '../uploads/videos/'
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        if (mkdir($dir, 0755, true)) {
            echo "✅ Created: $dir\n";
        } else {
            echo "❌ Failed to create: $dir\n";
        }
    } else {
        echo "✅ Already exists: $dir\n";
    }
}

// Step 2: Fix image paths in database
echo "\n2. Fixing Image Paths in Database\n";
echo "--------------------------------\n";

// Check for articles with broken image paths
$fix_query = "SELECT id, title, image FROM news WHERE image IS NOT NULL AND image != ''";
$result = mysqli_query($conn, $fix_query);

$fixed_count = 0;
if ($result && mysqli_num_rows($result) > 0) {
    while ($news = mysqli_fetch_assoc($result)) {
        $current_image = $news['image'];
        $fixed_image = null;
        
        // Fix common path issues
        if (strpos($current_image, '../') === 0) {
            // Remove leading ../
            $fixed_image = ltrim($current_image, '../');
        } elseif (strpos($current_image, 'uploads/') === false) {
            // Add uploads/ prefix if missing
            $fixed_image = 'uploads/news/' . basename($current_image);
        }
        
        if ($fixed_image && $fixed_image !== $current_image) {
            $update_query = "UPDATE news SET image = ? WHERE id = ?";
            $stmt = mysqli_prepare($conn, $update_query);
            mysqli_stmt_bind_param($stmt, 'si', $fixed_image, $news['id']);
            if (mysqli_stmt_execute($stmt)) {
                echo "✅ Fixed image path for article {$news['id']}: $current_image → $fixed_image\n";
                $fixed_count++;
            }
        }
    }
}

echo "Fixed $fixed_count image paths\n";

// Step 3: Verify files exist and create placeholders if needed
echo "\n3. Verifying Media Files\n";
echo "-----------------------\n";

$check_query = "SELECT id, title, image, video_url, video_path FROM news WHERE status = 'published' ORDER BY created_at DESC LIMIT 10";
$check_result = mysqli_query($conn, $check_query);

if ($check_result && mysqli_num_rows($check_result) > 0) {
    while ($news = mysqli_fetch_assoc($check_result)) {
        echo "Article {$news['id']}: " . substr($news['title'], 0, 40) . "...\n";
        
        // Check image
        if ($news['image']) {
            $image_path = '../' . $news['image'];
            if (file_exists($image_path)) {
                echo "  ✅ Image exists: {$news['image']}\n";
            } else {
                echo "  ❌ Image missing: {$news['image']}\n";
                
                // Try to find alternative image
                $alt_image = findAlternativeImage($news['id']);
                if ($alt_image) {
                    $update_query = "UPDATE news SET image = ? WHERE id = ?";
                    $stmt = mysqli_prepare($conn, $update_query);
                    mysqli_stmt_bind_param($stmt, 'si', $alt_image, $news['id']);
                    if (mysqli_stmt_execute($stmt)) {
                        echo "  🔄 Updated with alternative: $alt_image\n";
                    }
                }
            }
        }
        
        // Check video
        if ($news['video_path']) {
            $video_path = '../' . $news['video_path'];
            if (file_exists($video_path)) {
                echo "  ✅ Video exists: {$news['video_path']}\n";
            } else {
                echo "  ❌ Video missing: {$news['video_path']}\n";
            }
        }
        
        if ($news['video_url']) {
            echo "  🌐 Video URL: {$news['video_url']}\n";
        }
        
        echo "\n";
    }
}

// Step 4: Create .htaccess for uploads if missing
echo "4. Setting up .htaccess for Uploads\n";
echo "-----------------------------------\n";

$htaccess_content = "
# Allow access to images and videos
<FilesMatch '\.(jpg|jpeg|png|gif|webp|mp4|mov|avi)$'>
    Order allow,deny
    Allow from all
</FilesMatch>

# Set proper content types
<FilesMatch '\.(jpg|jpeg)$'>
    Header set Content-Type image/jpeg
</FilesMatch>
<FilesMatch '\.png$'>
    Header set Content-Type image/png
</FilesMatch>
<FilesMatch '\.gif$'>
    Header set Content-Type image/gif
</FilesMatch>
<FilesMatch '\.webp$'>
    Header set Content-Type image/webp
</FilesMatch>
<FilesMatch '\.mp4$'>
    Header set Content-Type video/mp4
</FilesMatch>

# Prevent PHP execution
<FilesMatch '\.php$'>
    Order allow,deny
    Deny from all
</FilesMatch>
";

$htaccess_file = '../uploads/.htaccess';
if (!file_exists($htaccess_file)) {
    if (file_put_contents($htaccess_file, $htaccess_content)) {
        echo "✅ Created uploads/.htaccess\n";
    } else {
        echo "❌ Failed to create uploads/.htaccess\n";
    }
} else {
    echo "✅ uploads/.htaccess already exists\n";
}

echo "\n=== Fix Complete ===\n";
echo "Check your index page now. Images and videos should be visible.\n";
echo "If issues persist, run media_diagnostic.php for detailed analysis.\n";

// Helper function to find alternative images
function findAlternativeImage($news_id) {
    global $conn;
    
    // Look for images in uploads/news directory
    $image_dir = '../uploads/news/';
    if (is_dir($image_dir)) {
        $files = glob($image_dir . '*');
        foreach ($files as $file) {
            if (is_file($file) && preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $file)) {
                return 'uploads/news/' . basename($file);
            }
        }
    }
    
    // Look in images subdirectory
    $images_dir = '../uploads/news/images/';
    if (is_dir($images_dir)) {
        $files = glob($images_dir . '*');
        foreach ($files as $file) {
            if (is_file($file) && preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $file)) {
                return 'uploads/news/images/' . basename($file);
            }
        }
    }
    
    return null;
}
?>

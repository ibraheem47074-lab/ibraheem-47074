<?php
require_once 'config/database.php';

echo "PK Live News - Image Upload System Check\n";
echo "=======================================\n\n";

// Step 1: Check upload directory structure
echo "1. Checking Upload Directory Structure\n";
echo "-------------------------------------\n";

$upload_path = UPLOAD_PATH . 'news/';
$full_upload_path = '../' . $upload_path;

echo "Upload path: $upload_path\n";
echo "Full path: $full_upload_path\n";

if (!is_dir($full_upload_path)) {
    echo "❌ Upload directory does not exist. Creating it...\n";
    if (mkdir($full_upload_path, 0755, true)) {
        echo "✅ Upload directory created successfully\n";
    } else {
        echo "❌ Failed to create upload directory\n";
        exit(1);
    }
} else {
    echo "✅ Upload directory exists\n";
}

// Check permissions
if (is_writable($full_upload_path)) {
    echo "✅ Upload directory is writable\n";
} else {
    echo "❌ Upload directory is not writable\n";
    echo "   Current permissions: " . substr(sprintf('%o', fileperms($full_upload_path)), -4) . "\n";
}

// List existing images
$existing_images = glob($full_upload_path . '*');
if (count($existing_images) > 0) {
    echo "✅ Found " . count($existing_images) . " existing images:\n";
    foreach ($existing_images as $image) {
        $filename = basename($image);
        $filesize = filesize($image);
        echo "   - $filename (" . round($filesize / 1024, 2) . " KB)\n";
    }
} else {
    echo "⚠️  No existing images found\n";
}

// Step 2: Check database for news with images
echo "\n2. Checking Database for News with Images\n";
echo "----------------------------------------\n";

$news_with_images_query = "SELECT id, title, image, status, published_at FROM news WHERE image IS NOT NULL AND image != '' ORDER BY id DESC LIMIT 10";
$news_result = mysqli_query($conn, $news_with_images_query);

if (mysqli_num_rows($news_result) > 0) {
    echo "✅ Found " . mysqli_num_rows($news_result) . " news articles with images:\n\n";
    
    while ($news = mysqli_fetch_assoc($news_result)) {
        echo "   ID: {$news['id']}\n";
        echo "   Title: " . substr($news['title'], 0, 50) . "...\n";
        echo "   Image Path: {$news['image']}\n";
        echo "   Status: {$news['status']}\n";
        echo "   Published: {$news['published_at']}\n";
        
        // Check if image file actually exists
        $image_file_path = '../' . $news['image'];
        if (file_exists($image_file_path)) {
            echo "   ✅ Image file exists\n";
            echo "   📁 File size: " . round(filesize($image_file_path) / 1024, 2) . " KB\n";
        } else {
            echo "   ❌ Image file NOT found at: $image_file_path\n";
        }
        echo "   ------------------------------------------------\n";
    }
} else {
    echo "❌ No news articles with images found in database\n";
}

// Step 3: Check published news that should appear on index
echo "\n3. Checking Published News for Index Page\n";
echo "----------------------------------------\n";

$published_news_query = "SELECT id, title, image, status, published_at FROM news WHERE status = 'published' ORDER BY published_at DESC LIMIT 5";
$published_result = mysqli_query($conn, $published_news_query);

if (mysqli_num_rows($published_result) > 0) {
    echo "✅ Found " . mysqli_num_rows($published_result) . " published news articles:\n\n";
    
    while ($news = mysqli_fetch_assoc($published_result)) {
        echo "   ID: {$news['id']}\n";
        echo "   Title: " . substr($news['title'], 0, 50) . "...\n";
        echo "   Image: " . ($news['image'] ? "Yes ({$news['image']})" : "No") . "\n";
        echo "   Status: {$news['status']}\n";
        echo "   Published: {$news['published_at']}\n";
        
        if ($news['image']) {
            $image_file_path = '../' . $news['image'];
            if (file_exists($image_file_path)) {
                echo "   ✅ Image file exists - should display on index page\n";
            } else {
                echo "   ❌ Image file missing - will show placeholder on index page\n";
            }
        } else {
            echo "   ⚠️  No image - will show placeholder on index page\n";
        }
        echo "   ------------------------------------------------\n";
    }
} else {
    echo "❌ No published news articles found\n";
}

// Step 4: Test image upload functionality
echo "\n4. Testing Image Upload Functionality\n";
echo "-------------------------------------\n";

// Create a test image upload
$test_image_content = base64_decode('/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQH/2wBDAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQH/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwA/8A8A');

$test_filename = 'test_image_' . time() . '.jpg';
$test_filepath = $full_upload_path . $test_filename;

if (file_put_contents($test_filepath, $test_image_content)) {
    echo "✅ Test image created successfully: $test_filename\n";
    
    // Test database insertion with image
    $test_title = "Test Article with Image " . date('Y-m-d H:i:s');
    $test_slug = create_slug($test_title);
    $test_content = "This is a test article to verify image upload and display functionality.";
    $test_excerpt = "Test article with image";
    $test_image_path = $upload_path . $test_filename;
    
    $insert_query = "INSERT INTO news (title, slug, content, excerpt, image, category_id, author_id, status, published_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'published', NOW())";
    
    $stmt = mysqli_prepare($conn, $insert_query);
    $category_id = 1;
    $author_id = 1;
    
    mysqli_stmt_bind_param($stmt, 'sssssis', $test_title, $test_slug, $test_content, $test_excerpt, $test_image_path, $category_id, $author_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $test_id = mysqli_insert_id($conn);
        echo "✅ Test article with image created (ID: $test_id)\n";
        echo "   This should appear on the index page with the test image\n";
    } else {
        echo "❌ Error creating test article: " . mysqli_stmt_error($stmt) . "\n";
    }
} else {
    echo "❌ Failed to create test image\n";
}

// Step 5: Check common issues
echo "\n5. Common Issues and Solutions\n";
echo "-----------------------------\n";

echo "Potential issues with image uploads:\n";
echo "1. Upload directory permissions\n";
echo "2. PHP upload limits (upload_max_filesize, post_max_size)\n";
echo "3. Image path storage in database\n";
echo "4. Image file existence after upload\n";
echo "5. Image display on index page\n\n";

echo "Current PHP settings:\n";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
echo "post_max_size: " . ini_get('post_max_size') . "\n";
echo "max_file_uploads: " . ini_get('max_file_uploads') . "\n";
echo "memory_limit: " . ini_get('memory_limit') . "\n";

// Step 6: Create fix recommendations
echo "\n6. Fix Recommendations\n";
echo "----------------------\n";

echo "If images are not appearing on index page:\n";
echo "1. Check if images are uploaded successfully (Step 1-2)\n";
echo "2. Verify image paths are stored correctly in database (Step 2)\n";
echo "3. Ensure news articles have 'published' status (Step 3)\n";
echo "4. Check if image files exist in upload directory (Step 3)\n";
echo "5. Verify image display code in index.php\n\n";

echo "Quick fixes to try:\n";
echo "1. Set proper permissions: chmod 755 uploads/news/\n";
echo "2. Check PHP error logs for upload errors\n";
echo "3. Test with a small image file (< 1MB)\n";
echo "4. Verify MAX_FILE_SIZE constant in database.php\n";

echo "\n" . str_repeat("=", 50) . "\n";
echo "IMAGE UPLOAD SYSTEM CHECK COMPLETE!\n";
echo str_repeat("=", 50) . "\n";
echo "\nNext Steps:\n";
echo "1. Review the output above for any issues\n";
echo "2. Check if test article appears on index page\n";
echo "3. Verify image display in browser\n";
echo "4. Test manual image upload in admin panel\n";
?>

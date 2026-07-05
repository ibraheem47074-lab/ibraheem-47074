<?php
require_once 'config/database.php';

echo "PK Live News - News Display Fix Tool\n";
echo "====================================\n\n";

// Check why news with images might not appear on index page
echo "1. Checking News Status and Display Conditions\n";
echo "---------------------------------------------\n";

// Get all news and check why they might not appear
$all_news_query = "SELECT id, title, image, status, published_at, created_at FROM news ORDER BY id DESC LIMIT 10";
$all_result = mysqli_query($conn, $all_news_query);

if (mysqli_num_rows($all_result) > 0) {
    echo "Found " . mysqli_num_rows($all_result) . " recent news articles:\n\n";
    
    while ($news = mysqli_fetch_assoc($all_result)) {
        echo "Article ID: {$news['id']}\n";
        echo "Title: " . substr($news['title'], 0, 50) . "...\n";
        echo "Status: {$news['status']}\n";
        echo "Image: " . ($news['image'] ? "Yes" : "No") . "\n";
        echo "Published: {$news['published_at']}\n";
        echo "Created: {$news['created_at']}\n";
        
        // Check if it should appear on index page
        $should_appear = true;
        $reasons = [];
        
        if ($news['status'] !== 'published') {
            $should_appear = false;
            $reasons[] = "Status is '{$news['status']}' (must be 'published')";
        }
        
        if ($news['published_at'] > date('Y-m-d H:i:s')) {
            $should_appear = false;
            $reasons[] = "Published date is in the future";
        }
        
        if ($should_appear) {
            echo "✅ SHOULD APPEAR on index page\n";
        } else {
            echo "❌ WILL NOT APPEAR on index page:\n";
            foreach ($reasons as $reason) {
                echo "   - $reason\n";
            }
        }
        
        echo "------------------------------------------------\n";
    }
} else {
    echo "❌ No news articles found\n";
}

// Fix common issues
echo "\n2. Auto-Fixing Common Issues\n";
echo "--------------------------\n";

// Fix news with incorrect status
$fix_drafts_query = "UPDATE news SET status = 'published' WHERE status = 'draft' AND image IS NOT NULL AND image != '' AND published_at <= NOW()";
$fix_result = mysqli_query($conn, $fix_drafts_query);

if ($fix_result) {
    $affected_rows = mysqli_affected_rows($conn);
    if ($affected_rows > 0) {
        echo "✅ Fixed $affected_rows draft articles with images - changed to 'published'\n";
    } else {
        echo "ℹ️  No draft articles with images needed fixing\n";
    }
}

// Fix future publish dates
$fix_dates_query = "UPDATE news SET published_at = created_at WHERE published_at > NOW() AND status = 'published'";
$fix_dates_result = mysqli_query($conn, $fix_dates_query);

if ($fix_dates_result) {
    $affected_rows = mysqli_affected_rows($conn);
    if ($affected_rows > 0) {
        echo "✅ Fixed $affected_rows articles with future publish dates\n";
    } else {
        echo "ℹ️  No articles with future publish dates needed fixing\n";
    }
}

// Create a test article with image if none exist
$published_with_images_query = "SELECT COUNT(*) as count FROM news WHERE status = 'published' AND image IS NOT NULL AND image != ''";
$count_result = mysqli_query($conn, $published_with_images_query);
$count = mysqli_fetch_assoc($count_result)['count'];

if ($count == 0) {
    echo "\n⚠️  No published articles with images found. Creating a test article...\n";
    
    // Create a simple test image
    $test_image_data = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';
    $test_image_binary = base64_decode($test_image_data);
    
    $test_filename = 'test_news_' . time() . '.png';
    $upload_path = UPLOAD_PATH . 'news/' . $test_filename;
    $full_upload_path = '../' . $upload_path;
    
    // Ensure directory exists
    $upload_dir = dirname($full_upload_path);
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    if (file_put_contents($full_upload_path, $test_image_binary)) {
        echo "✅ Test image created: $test_filename\n";
        
        // Create test article
        $test_title = "Test Article with Image - " . date('Y-m-d H:i:s');
        $test_slug = create_slug($test_title);
        $test_content = "This is a test article created to verify that images appear correctly on the index page. If you can see this article with its image on the index page, then the image system is working properly.";
        $test_excerpt = "Test article to verify image display on index page";
        
        $insert_query = "INSERT INTO news (title, slug, content, excerpt, image, category_id, author_id, status, published_at, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, 'published', NOW(), NOW())";
        
        $stmt = mysqli_prepare($conn, $insert_query);
        $category_id = 1;
        $author_id = 1;
        
        mysqli_stmt_bind_param($stmt, 'sssssis', $test_title, $test_slug, $test_content, $test_excerpt, $upload_path, $category_id, $author_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $test_id = mysqli_insert_id($conn);
            echo "✅ Test article created successfully (ID: $test_id)\n";
            echo "   This should now appear on the index page with an image\n";
        } else {
            echo "❌ Error creating test article: " . mysqli_stmt_error($stmt) . "\n";
        }
    } else {
        echo "❌ Failed to create test image\n";
    }
} else {
    echo "✅ Found $count published articles with images - no test article needed\n";
}

// Final verification
echo "\n3. Final Verification\n";
echo "-------------------\n";

$final_query = "SELECT id, title, image, status, published_at FROM news WHERE status = 'published' AND image IS NOT NULL AND image != '' ORDER BY published_at DESC LIMIT 5";
$final_result = mysqli_query($conn, $final_query);

if (mysqli_num_rows($final_result) > 0) {
    echo "✅ Articles that should appear on index page with images:\n\n";
    
    while ($news = mysqli_fetch_assoc($final_result)) {
        echo "   ID: {$news['id']} - " . substr($news['title'], 0, 40) . "...\n";
        echo "   Image: {$news['image']}\n";
        echo "   Status: {$news['status']}\n";
        echo "   Published: {$news['published_at']}\n";
        
        // Check if image file exists
        $image_path = '../' . $news['image'];
        if (file_exists($image_path)) {
            echo "   ✅ Image file exists\n";
        } else {
            echo "   ❌ Image file missing!\n";
        }
        echo "   ------------------------------------------------\n";
    }
} else {
    echo "❌ No published articles with images found\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "NEWS DISPLAY FIX COMPLETE!\n";
echo str_repeat("=", 50) . "\n";
echo "\nSummary:\n";
echo "- Checked news status and display conditions\n";
echo "- Fixed common issues (draft status, future dates)\n";
echo "- Created test article if needed\n";
echo "- Verified image file existence\n";
echo "\nNext Steps:\n";
echo "1. Visit index.php to check if images appear\n";
echo "2. If still not working, check browser console for errors\n";
echo "3. Verify image paths are correct\n";
echo "4. Test manual image upload in admin panel\n";
?>

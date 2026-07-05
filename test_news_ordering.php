<?php
require_once 'config/database.php';

echo "PK Live News - Time-Based Ordering Test\n";
echo "======================================\n\n";

// Test 1: Latest News Ordering
echo "1. Testing Latest News Ordering (NEWEST FIRST)\n";
echo "---------------------------------------------\n";

$latest_query = "SELECT n.id, n.title, n.published_at, n.created_at,
                CASE 
                    WHEN n.created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR) THEN 'new'
                    WHEN n.created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR) THEN 'recent'
                    ELSE 'older'
                END as time_status
                FROM news n 
                WHERE n.status = 'published' AND n.published_at <= NOW() 
                ORDER BY n.published_at DESC, n.created_at DESC LIMIT 10";

$latest_result = mysqli_query($conn, $latest_query);

if (mysqli_num_rows($latest_result) > 0) {
    $count = 1;
    echo "✅ Found " . mysqli_num_rows($latest_result) . " latest news articles\n\n";
    
    while ($news = mysqli_fetch_assoc($latest_result)) {
        echo "  #{$count} - ID: {$news['id']}\n";
        echo "       Title: " . substr($news['title'], 0, 50) . "...\n";
        echo "       Published: {$news['published_at']}\n";
        echo "       Created: {$news['created_at']}\n";
        echo "       Status: {$news['time_status']}\n";
        echo "       ------------------------------------------------\n";
        $count++;
    }
} else {
    echo "❌ No latest news found\n";
}

// Test 2: Featured News Ordering
echo "\n2. Testing Featured News Ordering (NEWEST FIRST)\n";
echo "----------------------------------------------\n";

$featured_query = "SELECT n.id, n.title, n.published_at, n.created_at
                   FROM news n 
                   WHERE n.status = 'featured' AND n.published_at <= NOW() 
                   ORDER BY n.published_at DESC LIMIT 3";

$featured_result = mysqli_query($conn, $featured_query);

if (mysqli_num_rows($featured_result) > 0) {
    $count = 1;
    echo "✅ Found " . mysqli_num_rows($featured_result) . " featured news articles\n\n";
    
    while ($news = mysqli_fetch_assoc($featured_result)) {
        echo "  #{$count} - ID: {$news['id']}\n";
        echo "       Title: " . substr($news['title'], 0, 50) . "...\n";
        echo "       Published: {$news['published_at']}\n";
        echo "       Created: {$news['created_at']}\n";
        echo "       ------------------------------------------------\n";
        $count++;
    }
} else {
    echo "❌ No featured news found\n";
}

// Test 3: Check if published_at and created_at are properly set
echo "\n3. Testing Date Fields Consistency\n";
echo "----------------------------------\n";

$date_check_query = "SELECT id, title, published_at, created_at, 
                     TIMESTAMPDIFF(SECOND, created_at, published_at) as time_diff
                     FROM news 
                     WHERE status = 'published' 
                     ORDER BY published_at DESC LIMIT 5";

$date_result = mysqli_query($conn, $date_check_query);

if (mysqli_num_rows($date_result) > 0) {
    echo "✅ Checking date consistency for latest articles:\n\n";
    
    while ($news = mysqli_fetch_assoc($date_result)) {
        echo "  ID: {$news['id']} - " . substr($news['title'], 0, 40) . "...\n";
        echo "       Published: {$news['published_at']}\n";
        echo "       Created: {$news['created_at']}\n";
        
        if ($news['time_diff'] < 0) {
            echo "       ⚠️  WARNING: Published date is before created date!\n";
        } elseif ($news['time_diff'] == 0) {
            echo "       ✅ Published and created dates are the same\n";
        } else {
            echo "       ✅ Published {$news['time_diff']} seconds after creation\n";
        }
        echo "       ------------------------------------------------\n";
    }
} else {
    echo "❌ No published articles found\n";
}

// Test 4: Create test articles if needed
echo "\n4. Creating Test Articles (if needed)\n";
echo "-------------------------------------\n";

$test_count_query = "SELECT COUNT(*) as count FROM news WHERE status = 'published'";
$test_count_result = mysqli_query($conn, $test_count_query);
$test_count = mysqli_fetch_assoc($test_count_result)['count'];

if ($test_count < 3) {
    echo "⚠️  Less than 3 published articles found. Creating test articles...\n";
    
    for ($i = 1; $i <= 3; $i++) {
        $title = "Test News Article $i - " . date('Y-m-d H:i:s');
        $slug = create_slug($title);
        $content = "This is test article number $i created for testing time-based ordering.";
        $excerpt = "Test article $i for ordering verification";
        
        // Create with different timestamps
        $publish_time = date('Y-m-d H:i:s', strtotime("-$i hour"));
        
        $insert_query = "INSERT INTO news (title, slug, content, excerpt, category_id, author_id, status, published_at, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, 'published', ?, ?)";
        
        $stmt = mysqli_prepare($conn, $insert_query);
        $category_id = 1;
        $author_id = 1;
        $created_time = $publish_time;
        
        mysqli_stmt_bind_param($stmt, 'ssssiiss', $title, $slug, $content, $excerpt, $category_id, $author_id, $publish_time, $created_time);
        
        if (mysqli_stmt_execute($stmt)) {
            echo "  ✅ Created test article $i (Published: $publish_time)\n";
        } else {
            echo "  ❌ Error creating test article $i: " . mysqli_stmt_error($stmt) . "\n";
        }
    }
} else {
    echo "✅ Found $test_count published articles - no test articles needed\n";
}

// Test 5: Final verification
echo "\n5. Final Ordering Verification\n";
echo "----------------------------\n";

$final_query = "SELECT id, title, published_at 
                FROM news 
                WHERE status = 'published' 
                ORDER BY published_at DESC, created_at DESC LIMIT 5";

$final_result = mysqli_query($conn, $final_query);

if (mysqli_num_rows($final_result) > 0) {
    echo "✅ Final verification - Top 5 newest articles:\n\n";
    
    $prev_time = null;
    $ordering_correct = true;
    $count = 1;
    
    while ($news = mysqli_fetch_assoc($final_result)) {
        echo "  #{$count} - ID: {$news['id']}\n";
        echo "       Title: " . substr($news['title'], 0, 45) . "...\n";
        echo "       Published: {$news['published_at']}\n";
        
        if ($prev_time !== null && $news['published_at'] > $prev_time) {
            echo "       ❌ ORDERING ERROR: This article should come before the previous one!\n";
            $ordering_correct = false;
        } else {
            echo "       ✅ Correctly ordered\n";
        }
        
        $prev_time = $news['published_at'];
        echo "       ------------------------------------------------\n";
        $count++;
    }
    
    if ($ordering_correct) {
        echo "\n🎉 SUCCESS: All articles are correctly ordered by time (NEWEST FIRST)!\n";
    } else {
        echo "\n⚠️  WARNING: Ordering issues detected!\n";
    }
} else {
    echo "❌ No articles found for final verification\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "ORDERING TEST COMPLETE!\n";
echo str_repeat("=", 50) . "\n";
echo "\nSummary:\n";
echo "- Latest news should show newest articles first\n";
echo "- Featured news should also show newest first\n";
echo "- Check the output above for any ordering issues\n";
echo "- Visit index.php to verify visual ordering\n";
?>

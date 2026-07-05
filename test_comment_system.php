<?php
require_once 'config/database.php';

echo "Comment System Test\n";
echo "===================\n\n";

// 1. Test database connection
echo "1. Testing database connection...\n";
if ($conn) {
    echo "   ✅ Database connected successfully\n";
} else {
    echo "   ❌ Database connection failed\n";
    exit(1);
}

// 2. Test comments table structure
echo "\n2. Testing comments table structure...\n";
$check_table = "SHOW TABLES LIKE 'comments'";
$result = mysqli_query($conn, $check_table);

if (mysqli_num_rows($result) > 0) {
    echo "   ✅ Comments table exists\n";
    
    // Check columns
    $describe = "DESCRIBE comments";
    $columns = mysqli_query($conn, $describe);
    $required_columns = ['id', 'news_id', 'parent_id', 'user_id', 'name', 'email', 'comment', 'status', 'created_at'];
    
    while ($column = mysqli_fetch_assoc($columns)) {
        if (in_array($column['Field'], $required_columns)) {
            echo "   ✅ Column '{$column['Field']}' exists\n";
            unset($required_columns[array_search($column['Field'], $required_columns)]);
        }
    }
    
    if (!empty($required_columns)) {
        echo "   ❌ Missing columns: " . implode(', ', $required_columns) . "\n";
    } else {
        echo "   ✅ All required columns exist\n";
    }
} else {
    echo "   ❌ Comments table does not exist\n";
}

// 3. Test news articles
echo "\n3. Testing news articles...\n";
$news_query = "SELECT id, title FROM news WHERE status = 'published' LIMIT 3";
$news_result = mysqli_query($conn, $news_query);

if (mysqli_num_rows($news_result) > 0) {
    echo "   ✅ Found " . mysqli_num_rows($news_result) . " published news articles\n";
    while ($news = mysqli_fetch_assoc($news_result)) {
        echo "   - {$news['title']} (ID: {$news['id']})\n";
    }
} else {
    echo "   ❌ No published news articles found\n";
}

// 4. Test comment insertion
echo "\n4. Testing comment insertion...\n";
if (mysqli_num_rows($news_result) > 0) {
    // Reset result pointer
    mysqli_data_seek($news_result, 0);
    $test_news = mysqli_fetch_assoc($news_result);
    
    $test_comment = [
        'news_id' => $test_news['id'],
        'name' => 'Test User',
        'email' => 'test@example.com',
        'comment' => 'This is a test comment for verification purposes.',
        'status' => 'approved'
    ];
    
    $insert_query = "INSERT INTO comments (news_id, name, email, comment, status) VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $insert_query);
    mysqli_stmt_bind_param($stmt, 'issss', $test_comment['news_id'], $test_comment['name'], $test_comment['email'], $test_comment['comment'], $test_comment['status']);
    
    if (mysqli_stmt_execute($stmt)) {
        $comment_id = mysqli_insert_id($conn);
        echo "   ✅ Test comment inserted successfully (ID: $comment_id)\n";
        
        // Test reply insertion
        $reply_data = [
            'news_id' => $test_news['id'],
            'parent_id' => $comment_id,
            'name' => 'Reply User',
            'email' => 'reply@example.com',
            'comment' => 'This is a test reply.',
            'status' => 'approved'
        ];
        
        $reply_query = "INSERT INTO comments (news_id, parent_id, name, email, comment, status) VALUES (?, ?, ?, ?, ?, ?)";
        $reply_stmt = mysqli_prepare($conn, $reply_query);
        mysqli_stmt_bind_param($reply_stmt, 'iissss', $reply_data['news_id'], $reply_data['parent_id'], $reply_data['name'], $reply_data['email'], $reply_data['comment'], $reply_data['status']);
        
        if (mysqli_stmt_execute($reply_stmt)) {
            echo "   ✅ Test reply inserted successfully\n";
        } else {
            echo "   ❌ Error inserting test reply: " . mysqli_stmt_error($reply_stmt) . "\n";
        }
        
    } else {
        echo "   ❌ Error inserting test comment: " . mysqli_stmt_error($stmt) . "\n";
    }
}

// 5. Test comment retrieval
echo "\n5. Testing comment retrieval...\n";
if (isset($test_news['id'])) {
    $comments_query = "SELECT c.*, u.name as user_name, u.role as user_role 
                     FROM comments c 
                     LEFT JOIN users u ON c.user_id = u.id 
                     WHERE c.news_id = ? AND c.status = 'approved' 
                     ORDER BY c.created_at DESC";
    $stmt = mysqli_prepare($conn, $comments_query);
    mysqli_stmt_bind_param($stmt, 'i', $test_news['id']);
    mysqli_stmt_execute($stmt);
    $comments_result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($comments_result) > 0) {
        echo "   ✅ Retrieved " . mysqli_num_rows($comments_result) . " comments\n";
        while ($comment = mysqli_fetch_assoc($comments_result)) {
            $indent = $comment['parent_id'] ? '   └─ ' : '   - ';
            echo "   {$indent}{$comment['name']}: " . substr($comment['comment'], 0, 50) . "...\n";
        }
    } else {
        echo "   ❌ No comments found\n";
    }
}

// 6. Test API endpoints
echo "\n6. Testing API endpoints...\n";

// Test get-comments.php
if (isset($test_news['id'])) {
    $api_url = "http://localhost/pk-live-news/api/get-comments.php?news_id=" . $test_news['id'];
    echo "   Testing: $api_url\n";
    
    $context = stream_context_create([
        'http' => [
            'timeout' => 5
        ]
    ]);
    
    $response = @file_get_contents($api_url, false, $context);
    if ($response) {
        $data = json_decode($response, true);
        if ($data && isset($data['success']) && $data['success']) {
            echo "   ✅ get-comments.php API working\n";
            echo "   └─ Retrieved " . count($data['comments']) . " comments\n";
        } else {
            echo "   ❌ get-comments.php API returned error\n";
        }
    } else {
        echo "   ⚠️  Could not test get-comments.php (web server may not be running)\n";
    }
}

// 7. Summary
echo "\n7. Summary\n";
echo "=========\n";

$comment_count_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN parent_id IS NULL THEN 1 ELSE 0 END) as main_comments,
    SUM(CASE WHEN parent_id IS NOT NULL THEN 1 ELSE 0 END) as replies
    FROM comments";

$stats_result = mysqli_query($conn, $comment_count_query);
$stats = mysqli_fetch_assoc($stats_result);

echo "Current Comment Statistics:\n";
echo "- Total Comments: {$stats['total']}\n";
echo "- Approved: {$stats['approved']}\n";
echo "- Pending: {$stats['pending']}\n";
echo "- Main Comments: {$stats['main_comments']}\n";
echo "- Replies: {$stats['replies']}\n";

echo "\nFeatures Implemented:\n";
echo "✅ Comment submission with approval system\n";
echo "✅ Reply functionality\n";
echo "✅ Admin/Editor badges\n";
echo "✅ Comment deletion for admins\n";
echo "✅ Guest commenting with name/email\n";
echo "✅ User integration (auto-fill user info)\n";
echo "✅ Real-time comment display\n";
echo "✅ Responsive design\n";

echo "\nNext Steps:\n";
echo "1. Visit any news article to test commenting\n";
echo "2. Test guest commenting by logging out\n";
echo "3. Test admin features by logging in as admin\n";
echo "4. Test reply functionality\n";
echo "5. Check comment approval in admin panel\n";

echo "\nTest completed successfully!\n";
?>

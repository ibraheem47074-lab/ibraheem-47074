<?php
require_once 'config/database.php';

echo "PK Live News - Comments System Fix\n";
echo "=====================================\n\n";

// Step 1: Check and create comments table if needed
echo "Step 1: Checking Comments Table\n";
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'comments'");
if (mysqli_num_rows($table_check) == 0) {
    echo "❌ Comments table does not exist. Creating it...\n";
    
    $create_table = "CREATE TABLE comments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        news_id INT NOT NULL,
        user_id INT NULL,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        comment TEXT NOT NULL,
        status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (news_id) REFERENCES news(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
        INDEX idx_news_id (news_id),
        INDEX idx_status (status),
        INDEX idx_created_at (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if (mysqli_query($conn, $create_table)) {
        echo "✅ Comments table created successfully\n";
    } else {
        echo "❌ Error creating comments table: " . mysqli_error($conn) . "\n";
        exit(1);
    }
} else {
    echo "✅ Comments table exists\n";
    
    // Check table structure
    $structure = mysqli_query($conn, "DESCRIBE comments");
    $has_status = false;
    while ($row = mysqli_fetch_assoc($structure)) {
        if ($row['Field'] === 'status') {
            $has_status = true;
            break;
        }
    }
    
    // Add status column if missing
    if (!$has_status) {
        echo "⚠️  Adding missing status column...\n";
        mysqli_query($conn, "ALTER TABLE comments ADD COLUMN status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending'");
        echo "✅ Status column added\n";
    }
}

// Step 2: Check if there are any news articles
echo "\nStep 2: Checking News Articles\n";
$news_check = "SELECT COUNT(*) as total FROM news WHERE status = 'published'";
$news_result = mysqli_query($conn, $news_check);
$news_count = mysqli_fetch_assoc($news_result)['total'];

if ($news_count == 0) {
    echo "❌ No published news articles found. Comments need news articles to work.\n";
    
    // Create a test news article
    echo "Creating a test news article...\n";
    $insert_news = "INSERT INTO news (title, slug, content, excerpt, category_id, author_id, status, published_at) 
                    VALUES (?, ?, ?, ?, ?, ?, 'published', NOW())";
    
    $stmt = mysqli_prepare($conn, $insert_news);
    $title = "Test News Article for Comments";
    $slug = create_slug($title);
    $content = "This is a test news article created to test the comments functionality. You can leave comments on this article to test if the comment system is working properly.";
    $excerpt = "Test news article for testing comments system";
    $category_id = 1; // Assuming category 1 exists
    $author_id = 1; // Assuming user 1 exists
    
    mysqli_stmt_bind_param($stmt, 'ssssii', $title, $slug, $content, $excerpt, $category_id, $author_id);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "✅ Test news article created\n";
    } else {
        echo "❌ Error creating test news article: " . mysqli_stmt_error($stmt) . "\n";
    }
} else {
    echo "✅ Found $news_count published news articles\n";
}

// Step 3: Test comment submission
echo "\nStep 3: Testing Comment Submission\n";

// Get a news article for testing
$test_news_query = "SELECT id, title FROM news WHERE status = 'published' LIMIT 1";
$test_news_result = mysqli_query($conn, $test_news_query);

if (mysqli_num_rows($test_news_result) > 0) {
    $test_news = mysqli_fetch_assoc($test_news_result);
    echo "✅ Using test article: {$test_news['title']} (ID: {$test_news['id']})\n";
    
    // Test inserting a comment
    $test_comment = "This is a test comment to verify the comments system is working properly.";
    $test_insert = "INSERT INTO comments (news_id, name, email, comment, status) VALUES (?, ?, ?, ?, 'approved')";
    $stmt = mysqli_prepare($conn, $test_insert);
    
    if ($stmt) {
        $test_name = "Test User";
        $test_email = "test@example.com";
        
        mysqli_stmt_bind_param($stmt, 'isss', $test_news['id'], $test_name, $test_email, $test_comment);
        
        if (mysqli_stmt_execute($stmt)) {
            $comment_id = mysqli_insert_id($conn);
            echo "✅ Test comment inserted successfully (ID: $comment_id)\n";
        } else {
            echo "❌ Error inserting test comment: " . mysqli_stmt_error($stmt) . "\n";
        }
    } else {
        echo "❌ Error preparing test statement: " . mysqli_error($conn) . "\n";
    }
} else {
    echo "❌ No news articles available for testing\n";
}

// Step 4: Check API files
echo "\nStep 4: Checking Comment API Files\n";

$api_files = [
    'api/submit-comment.php' => 'Comment Submission API',
    'api/get-comments.php' => 'Comment Retrieval API'
];

foreach ($api_files as $file => $description) {
    if (file_exists($file)) {
        echo "✅ $description exists\n";
    } else {
        echo "❌ $description missing\n";
    }
}

// Step 5: Verify comment functionality in news.php
echo "\nStep 5: Checking News Page Comment Integration\n";

if (file_exists('news.php')) {
    $news_content = file_get_contents('news.php');
    if (strpos($news_content, 'submit_comment') !== false) {
        echo "✅ Comment form found in news.php\n";
    } else {
        echo "❌ Comment form not found in news.php\n";
    }
    
    if (strpos($news_content, 'comments_section') !== false) {
        echo "✅ Comments section found in news.php\n";
    } else {
        echo "❌ Comments section not found in news.php\n";
    }
} else {
    echo "❌ news.php not found\n";
}

// Step 6: Create a simple comment test page
echo "\nStep 6: Creating Comment Test Page\n";

$test_page_content = '<?php
require_once "config/database.php";

// Get a news article for testing
$news_query = "SELECT id, title FROM news WHERE status = \'published\' LIMIT 1";
$news_result = mysqli_query($conn, $news_query);
$news = mysqli_fetch_assoc($news_result);

$page_title = "Comment System Test";
include "includes/header.php";
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h4 class="mb-0">🧪 Comment System Test</h4>
                </div>
                <div class="card-body">
                    <h5>Testing Article: <?php echo htmlspecialchars($news["title"]); ?></h5>
                    
                    <?php
                    // Handle comment submission
                    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["test_comment"])) {
                        $name = clean_input($_POST["name"]);
                        $email = clean_input($_POST["email"]);
                        $comment = clean_input($_POST["comment"]);
                        
                        if (!empty($name) && !empty($email) && !empty($comment)) {
                            $insert = "INSERT INTO comments (news_id, name, email, comment, status) VALUES (?, ?, ?, ?, \'approved\')";
                            $stmt = mysqli_prepare($conn, $insert);
                            mysqli_stmt_bind_param($stmt, "isss", $news["id"], $name, $email, $comment);
                            
                            if (mysqli_stmt_execute($stmt)) {
                                echo \'<div class="alert alert-success">✅ Comment submitted successfully!</div>\';
                            } else {
                                echo \'<div class="alert alert-danger">❌ Error submitting comment</div>\';
                            }
                        } else {
                            echo \'<div class="alert alert-warning">⚠️ Please fill all fields</div>\';
                        }
                    }
                    
                    // Get existing comments
                    $comments_query = "SELECT * FROM comments WHERE news_id = ? AND status = \'approved\' ORDER BY created_at DESC";
                    $stmt = mysqli_prepare($conn, $comments_query);
                    mysqli_stmt_bind_param($stmt, "i", $news["id"]);
                    mysqli_stmt_execute($stmt);
                    $comments_result = mysqli_stmt_get_result($stmt);
                    ?>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Test Comment Form</h6>
                            <form method="POST">
                                <div class="mb-2">
                                    <input type="text" name="name" class="form-control" placeholder="Name" required>
                                </div>
                                <div class="mb-2">
                                    <input type="email" name="email" class="form-control" placeholder="Email" required>
                                </div>
                                <div class="mb-2">
                                    <textarea name="comment" class="form-control" placeholder="Your comment" required></textarea>
                                </div>
                                <button type="submit" name="test_comment" class="btn btn-danger btn-sm">Submit Test Comment</button>
                            </form>
                        </div>
                        <div class="col-md-6">
                            <h6>Existing Comments (<?php echo mysqli_num_rows($comments_result); ?>)</h6>
                            <?php if (mysqli_num_rows($comments_result) > 0): ?>
                                <?php while ($comment = mysqli_fetch_assoc($comments_result)): ?>
                                    <div class="border p-2 mb-2 small">
                                        <strong><?php echo htmlspecialchars($comment["name"]); ?></strong>
                                        <br><small><?php echo $comment["created_at"]; ?></small>
                                        <br><?php echo htmlspecialchars($comment["comment"]); ?>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <p class="text-muted small">No comments yet</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "includes/footer.php"; ?>';

if (file_put_contents('test_comments.php', $test_page_content)) {
    echo "✅ Comment test page created: test_comments.php\n";
} else {
    echo "❌ Error creating test page\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "COMMENTS SYSTEM FIX COMPLETE!\n";
echo str_repeat("=", 50) . "\n";
echo "\nNext Steps:\n";
echo "1. Visit: http://localhost/pk-live-news/test_comments.php\n";
echo "2. Test submitting a comment\n";
echo "3. Check if comments appear in the list\n";
echo "4. Visit a news article to test the full comment system\n";
echo "\nIf comments still don't work, check:\n";
echo "- Browser console for JavaScript errors\n";
echo "- Network tab for API call failures\n";
echo "- PHP error logs for server errors\n";
?>

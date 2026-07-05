<?php
// PK Live News - Debug Author Assignment Issue
session_start();
require_once '../config/database.php';

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Debug Author Assignment - PK Live News</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <style>
        body { background: #f8f9fa; padding: 20px; }
        .debug-container { max-width: 1200px; margin: 0 auto; }
        .card { border: none; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .error { color: #dc3545; font-weight: bold; }
        .success { color: #28a745; font-weight: bold; }
        .warning { color: #ffc107; font-weight: bold; }
        .code-block { background: #000; color: #00ff00; padding: 15px; border-radius: 5px; font-family: monospace; }
    </style>
</head>
<body>
    <div class='debug-container'>
        <div class='card'>
            <div class='card-header bg-danger text-white'>
                <h3 class='mb-0'>Debug: Author Assignment Issue</h3>
            </div>
            <div class='card-body'>";

echo "<h4>Step 1: Session Information</h4>";
echo "<p>Session Status: " . (session_status() === PHP_SESSION_ACTIVE ? 'Active' : 'Not Active') . "</p>";

if (isset($_SESSION)) {
    echo "<h5>Session Contents:</h5>";
    echo "<div class='code-block'>";
    foreach ($_SESSION as $key => $value) {
        if ($key === 'password') {
            echo "$key: [HIDDEN]\n";
        } else {
            echo "$key: " . print_r($value, true) . "\n";
        }
    }
    echo "</div>";
} else {
    echo "<p class='error'>No session data found!</p>";
}

echo "<h4>Step 2: User Authentication Check</h4>";

if (function_exists('is_logged_in')) {
    $logged_in = is_logged_in();
    echo "<p class='success'>is_logged_in(): " . ($logged_in ? 'TRUE' : 'FALSE') . "</p>";
} else {
    echo "<p class='error'>is_logged_in() function not found!</p>";
}

if (function_exists('is_admin')) {
    $is_admin = is_admin();
    echo "<p class='success'>is_admin(): " . ($is_admin ? 'TRUE' : 'FALSE') . "</p>";
} else {
    echo "<p class='error'>is_admin() function not found!</p>";
}

if (function_exists('is_editor')) {
    $is_editor = is_editor();
    echo "<p class='success'>is_editor(): " . ($is_editor ? 'TRUE' : 'FALSE') . "</p>";
} else {
    echo "<p class='error'>is_editor() function not found!</p>";
}

if (function_exists('is_reporter')) {
    $is_reporter = is_reporter();
    echo "<p class='success'>is_reporter(): " . ($is_reporter ? 'TRUE' : 'FALSE') . "</p>";
} else {
    echo "<p class='error'>is_reporter() function not found!</p>";
}

echo "<h4>Step 3: Database User Check</h4>";

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    echo "<p>Session User ID: $user_id</p>";
    
    $user_query = "SELECT * FROM users WHERE id = ?";
    $user_stmt = mysqli_prepare($conn, $user_query);
    mysqli_stmt_bind_param($user_stmt, 'i', $user_id);
    mysqli_stmt_execute($user_stmt);
    $user_result = mysqli_stmt_get_result($user_stmt);
    
    if ($user = mysqli_fetch_assoc($user_result)) {
        echo "<div class='code-block'>";
        echo "User ID: " . $user['id'] . "\n";
        echo "Username: " . $user['username'] . "\n";
        echo "Name: " . $user['name'] . "\n";
        echo "Email: " . $user['email'] . "\n";
        echo "Role: " . $user['role'] . "\n";
        echo "Status: " . $user['status'] . "\n";
        echo "</div>";
    } else {
        echo "<p class='error'>User not found in database for ID: $user_id</p>";
    }
    mysqli_stmt_close($user_stmt);
} else {
    echo "<p class='error'>No user_id in session!</p>";
}

echo "<h4>Step 4: Recent News Articles Analysis</h4>";

$recent_news_query = "SELECT id, title, author_id, created_at FROM news ORDER BY created_at DESC LIMIT 10";
$recent_result = mysqli_query($conn, $recent_news_query);

if ($recent_result && mysqli_num_rows($recent_result) > 0) {
    echo "<div class='table-responsive'>";
    echo "<table class='table table-bordered'>";
    echo "<thead><tr><th>ID</th><th>Title</th><th>Author ID</th><th>Created</th></tr></thead>";
    echo "<tbody>";
    
    while ($news = mysqli_fetch_assoc($recent_result)) {
        $author_id = $news['author_id'];
        $author_name = 'Unknown';
        
        if ($author_id) {
            $author_query = "SELECT name FROM users WHERE id = ?";
            $author_stmt = mysqli_prepare($conn, $author_query);
            mysqli_stmt_bind_param($author_stmt, 'i', $author_id);
            mysqli_stmt_execute($author_stmt);
            $author_result = mysqli_stmt_get_result($author_stmt);
            if ($author_user = mysqli_fetch_assoc($author_result)) {
                $author_name = $author_user['name'];
            }
            mysqli_stmt_close($author_stmt);
        }
        
        echo "<tr>";
        echo "<td>" . $news['id'] . "</td>";
        echo "<td>" . htmlspecialchars(substr($news['title'], 0, 50)) . "...</td>";
        echo "<td>" . $author_id . " (" . htmlspecialchars($author_name) . ")</td>";
        echo "<td>" . $news['created_at'] . "</td>";
        echo "</tr>";
    }
    echo "</tbody></table></div>";
} else {
    echo "<p class='warning'>No news articles found</p>";
}

echo "<h4>Step 5: Test Author Assignment</h4>";

echo "<div class='alert alert-info'>
    <h5>Testing Author Assignment:</h5>
    <p>This section will test if the author assignment works correctly when posting news.</p>
</div>";

// Simulate adding a test article
if (isset($_SESSION['user_id'])) {
    $test_title = "Test Article by " . ($_SESSION['name'] ?? 'Unknown User') . " - " . date('Y-m-d H:i:s');
    $test_content = "This is a test article to verify author assignment works correctly.";
    $test_slug = "test-article-" . time();
    $test_author_id = $_SESSION['user_id'];
    
    echo "<h6>Test Data:</h6>";
    echo "<div class='code-block'>";
    echo "Title: $test_title\n";
    echo "Author ID: $test_author_id\n";
    echo "Slug: $test_slug\n";
    echo "</div>";
    
    // Check if test article already exists
    $check_query = "SELECT id FROM news WHERE slug = ?";
    $check_stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($check_stmt, 's', $test_slug);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    
    if (mysqli_num_rows($check_result) == 0) {
        // Insert test article
        $insert_query = "INSERT INTO news (title, slug, content, excerpt, category_id, author_id, status, created_at, published_at) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
        
        $insert_stmt = mysqli_prepare($conn, $insert_query);
        $test_excerpt = substr($test_content, 0, 200) . '...';
        $test_category_id = 1; // Default category
        $test_status = 'draft';
        mysqli_stmt_bind_param($insert_stmt, 'ssssiiis', 
            $test_title, 
            $test_slug, 
            $test_content, 
            $test_excerpt, 
            $test_category_id,
            $test_author_id, 
            $test_status
        );
        
        if (mysqli_stmt_execute($insert_stmt)) {
            $test_id = mysqli_insert_id($conn);
            echo "<p class='success'>Test article created successfully! ID: $test_id</p>";
            echo "<p>Check your manage news page to see if this article appears with correct author.</p>";
            echo "<a href='manage-news.php' class='btn btn-primary'>Check Manage News</a>";
        } else {
            echo "<p class='error'>Failed to create test article: " . mysqli_error($conn) . "</p>";
        }
        mysqli_stmt_close($insert_stmt);
    } else {
        echo "<p class='warning'>Test article already exists. Check manage news for existing test articles.</p>";
    }
    mysqli_stmt_close($check_stmt);
} else {
    echo "<p class='error'>Cannot test - no user session found!</p>";
}

echo "<div class='alert alert-warning mt-4'>
    <h5>Common Author Assignment Issues:</h5>
    <ul>
        <li><strong>Missing user_id in session:</strong> User not properly logged in</li>
        <li><strong>Wrong author_id:</strong> Using hardcoded ID instead of session</li>
        <li><strong>SQL injection:</strong> Direct variable insertion instead of prepared statements</li>
        <li><strong>Session timing:</strong> Session expired or not started</li>
    </ul>
</div>";

echo "
            </div>
        </div>
    </div>
    
    <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>
</body>
</html>";
?>

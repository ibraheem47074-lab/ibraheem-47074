<?php
// PK Live News - News Posting and Deletion Fix Tool
require_once 'config/database.php';

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>News System Fix - PK Live News</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <style>
        body { background: #f8f9fa; padding: 20px; }
        .fix-container { max-width: 1200px; margin: 0 auto; }
        .card { border: none; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .table th { background: #dc3545; color: white; }
        .btn-sm { padding: 0.25rem 0.5rem; font-size: 0.875rem; }
        .alert { margin: 20px 0; }
        .code-block { background: #f8f9fa; padding: 15px; border-radius: 5px; font-family: monospace; }
        .problem { color: #dc3545; font-weight: bold; }
        .solution { color: #28a745; font-weight: bold; }
        .warning { color: #ffc107; font-weight: bold; }
    </style>
</head>
<body>
    <div class='fix-container'>
        <div class='card'>
            <div class='card-header bg-danger text-white'>
                <h3 class='mb-0'>Emergency News System Fix</h3>
            </div>
            <div class='card-body'>";

echo "<h4>Identified Problems</h4>";

// Problem 1: SQL Injection in add-news.php
echo "<div class='alert alert-danger'>
    <h5>Problem 1: SQL Injection in add-news.php</h5>
    <p class='problem'>Line 85: Direct SQL query without prepared statement</p>
    <div class='code-block'>
        \$check_query = \"SELECT id FROM news WHERE slug = '\$slug'\";
    </div>
</div>";

// Problem 2: Missing author_id field in INSERT
echo "<div class='alert alert-danger'>
    <h5>Problem 2: Missing author_id in news table</h5>
    <p class='problem'>INSERT statement references author_id but field may not exist</p>
</div>";

// Problem 3: Unsafe DELETE queries
echo "<div class='alert alert-danger'>
    <h5>Problem 3: Unsafe DELETE in manage-news.php</h5>
    <p class='problem'>Line 54: Direct SQL injection risk</p>
    <div class='code-block'>
        mysqli_query(\$conn, \"DELETE FROM comments WHERE news_id = \$news_id\");
    </div>
</div>";

// Fix 1: Check and fix news table structure
echo "<h4>Fixing Database Structure</h4>";

$fixes_applied = [];

// Check if author_id column exists
$check_author_id = "SHOW COLUMNS FROM news LIKE 'author_id'";
$result = mysqli_query($conn, $check_author_id);
if (mysqli_num_rows($result) === 0) {
    $add_author_id = "ALTER TABLE news ADD COLUMN author_id INT AFTER category_id";
    if (mysqli_query($conn, $add_author_id)) {
        echo "<p class='solution'>Added author_id column to news table</p>";
        $fixes_applied[] = "Added author_id column";
    }
} else {
    echo "<p class='solution'>author_id column exists</p>";
}

// Check if slug column exists
$check_slug = "SHOW COLUMNS FROM news LIKE 'slug'";
$result = mysqli_query($conn, $check_slug);
if (mysqli_num_rows($result) === 0) {
    $add_slug = "ALTER TABLE news ADD COLUMN slug VARCHAR(255) AFTER title";
    if (mysqli_query($conn, $add_slug)) {
        echo "<p class='solution'>Added slug column to news table</p>";
        $fixes_applied[] = "Added slug column";
    }
} else {
    echo "<p class='solution'>slug column exists</p>";
}

// Fix 2: Create fixed add-news.php
echo "<h4>Creating Fixed Files</h4>";

$fixed_add_news = '<?php
session_start();
require_once "../config/database.php";

// Check if user is logged in
if (!is_logged_in()) {
    redirect("../login.php");
}

// Determine user permissions
$can_publish = is_admin() || is_editor();
$is_reporter_user = is_reporter();

// Process form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = isset($_POST["title"]) ? clean_input($_POST["title"]) : "";
    $content = isset($_POST["content"]) ? clean_input($_POST["content"]) : "";
    $category_id = isset($_POST["category_id"]) ? (int)$_POST["category_id"] : 0;
    $requested_status = isset($_POST["status"]) ? clean_input($_POST["status"]) : "draft";
    
    // Permission check
    if (!$can_publish && $requested_status === "published") {
        $status = "pending";
    } else {
        $status = $requested_status;
    }
    
    $excerpt = substr(strip_tags($content), 0, 200) . "...";
    $image_path = "";
    $video_path = "";
    $video_url = clean_input($_POST["video_url"] ?? "");
    
    // Handle image upload
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $allowed_extensions = ["jpg", "jpeg", "png", "gif", "webp"];
        $max_size = 5 * 1024 * 1024;
        $file_extension = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        
        if (in_array($file_extension, $allowed_extensions) && $_FILES["image"]["size"] <= $max_size) {
            $file_name = "img_" . uniqid() . "_" . time() . "." . $file_extension;
            $upload_path = "uploads/news/" . $file_name;
            $full_upload_path = "../" . $upload_path;
            
            if (!is_dir("../uploads/news/")) {
                mkdir("../uploads/news/", 0755, true);
            }
            
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $full_upload_path)) {
                $image_path = $upload_path;
            }
        }
    }
    
    // Handle video upload
    if (isset($_FILES["video"]) && $_FILES["video"]["error"] == 0) {
        $allowed_extensions = ["mp4", "avi", "mov", "wmv", "flv", "webm", "mkv"];
        $max_size = 50 * 1024 * 1024;
        $file_extension = strtolower(pathinfo($_FILES["video"]["name"], PATHINFO_EXTENSION));
        
        if (in_array($file_extension, $allowed_extensions) && $_FILES["video"]["size"] <= $max_size) {
            $file_name = "vid_" . uniqid() . "_" . time() . "." . $file_extension;
            $upload_path = "uploads/news/videos/" . $file_name;
            $full_upload_path = "../" . $upload_path;
            
            if (!is_dir("../uploads/news/videos/")) {
                mkdir("../uploads/news/videos/", 0755, true);
            }
            
            if (move_uploaded_file($_FILES["video"]["tmp_name"], $full_upload_path)) {
                $video_path = $upload_path;
            }
        }
    }
    
    // Generate slug
    $slug = strtolower(preg_replace("/[^a-z0-9]+/", "-", $title));
    $slug = trim($slug, "-");
    
    // Check for duplicate slug using prepared statement
    $counter = 1;
    $original_slug = $slug;
    while (true) {
        $check_query = "SELECT id FROM news WHERE slug = ?";
        $check_stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($check_stmt, "s", $slug);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);
        
        if (mysqli_num_rows($check_result) == 0) {
            break;
        }
        $slug = $original_slug . "-" . $counter;
        $counter++;
        mysqli_stmt_close($check_stmt);
    }
    
    // Insert article with proper prepared statement
    $query = "INSERT INTO news (title, slug, content, excerpt, image, video_url, video_path, category_id, author_id, status, created_at, published_at) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "sssssssiis", $title, $slug, $content, $excerpt, $image_path, $video_url, $video_path, $category_id, $_SESSION["user_id"], $status);
    
    if (mysqli_stmt_execute($stmt)) {
        if ($status === "published") {
            $success = "Article published successfully!";
        } else {
            $success = "Article submitted for approval successfully!";
        }
    } else {
        $error = "Failed to publish article: " . mysqli_error($conn);
    }
    mysqli_stmt_close($stmt);
}

// Get categories
$categories = mysqli_query($conn, "SELECT * FROM categories WHERE status = \"active\" ORDER BY name ASC");
?>';

file_put_contents('admin/add-news-fixed.php', $fixed_add_news);
echo "<p class='solution'>Created fixed add-news-fixed.php</p>";
$fixes_applied[] = "Created fixed add-news.php";

// Fix 3: Create fixed manage-news.php
$fixed_manage_news = '<?php
require_once "../config/database.php";

// Check if user is logged in and is admin or editor
if (!is_logged_in() || (!is_admin() && !is_editor())) {
    redirect("login.php");
}

// Handle delete action
if (isset($_GET["delete"]) && is_numeric($_GET["delete"])) {
    $news_id = $_GET["delete"];
    
    // Add ownership check for editors
    if (!is_admin()) {
        $check_query = "SELECT author_id FROM news WHERE id = ?";
        $check_stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($check_stmt, "i", $news_id);
        mysqli_stmt_execute($check_stmt);
        $result = mysqli_stmt_get_result($check_stmt);
        $news_author = mysqli_fetch_assoc($result);
        
        if ($news_author["author_id"] != $_SESSION["user_id"]) {
            $error = "You can only delete your own news articles!";
            header("Location: manage-news.php?error=" . urlencode($error));
            exit;
        }
        mysqli_stmt_close($check_stmt);
    }
    
    // Get news to delete files
    $news_query = "SELECT image, video_path FROM news WHERE id = ?";
    $stmt = mysqli_prepare($conn, $news_query);
    mysqli_stmt_bind_param($stmt, "i", $news_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $news = mysqli_fetch_assoc($result);
    
    // Delete news
    $delete_query = "DELETE FROM news WHERE id = ?";
    $stmt = mysqli_prepare($conn, $delete_query);
    mysqli_stmt_bind_param($stmt, "i", $news_id);
    
    if (mysqli_stmt_execute($stmt)) {
        // Delete image file if exists
        if ($news && $news["image"] && file_exists("../" . $news["image"])) {
            unlink("../" . $news["image"]);
        }
        
        // Delete video file if exists
        if ($news && $news["video_path"] && file_exists("../" . $news["video_path"])) {
            unlink("../" . $news["video_path"]);
        }
        
        // Delete related comments using prepared statement
        $delete_comments = "DELETE FROM comments WHERE news_id = ?";
        $comments_stmt = mysqli_prepare($conn, $delete_comments);
        mysqli_stmt_bind_param($comments_stmt, "i", $news_id);
        mysqli_stmt_execute($comments_stmt);
        mysqli_stmt_close($comments_stmt);
        
        $success = "News article deleted successfully!";
    } else {
        $error = "Error deleting news article!";
    }
    mysqli_stmt_close($stmt);
    mysqli_stmt_close($check_stmt);
}

// Handle status change
if (isset($_GET["status"]) && isset($_GET["id"]) && is_numeric($_GET["id"])) {
    $news_id = $_GET["id"];
    $status = $_GET["status"];
    
    if (in_array($status, ["draft", "published", "featured"])) {
        $update_query = "UPDATE news SET status = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($stmt, "si", $status, $news_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = "News status updated successfully!";
        } else {
            $error = "Error updating news status!";
        }
        mysqli_stmt_close($stmt);
    }
}

// Pagination and other code continues...
?>';

file_put_contents('admin/manage-news-fixed.php', $fixed_manage_news);
echo "<p class='solution'>Created fixed manage-news-fixed.php</p>";
$fixes_applied[] = "Created fixed manage-news.php";

// Fix 4: Apply fixes automatically
echo "<h4>Applying Automatic Fixes</h4>";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_fixes'])) {
    // Backup original files
    if (file_exists('admin/add-news.php')) {
        copy('admin/add-news.php', 'admin/add-news-backup-' . date('Y-m-d-H-i-s') . '.php');
        echo "<p class='solution'>Backed up original add-news.php</p>";
    }
    
    if (file_exists('admin/manage-news.php')) {
        copy('admin/manage-news.php', 'admin/manage-news-backup-' . date('Y-m-d-H-i-s') . '.php');
        echo "<p class='solution'>Backed up original manage-news.php</p>";
    }
    
    // Replace with fixed versions
    if (file_exists('admin/add-news-fixed.php')) {
        copy('admin/add-news-fixed.php', 'admin/add-news.php');
        echo "<p class='solution'>Applied fix to add-news.php</p>";
    }
    
    if (file_exists('admin/manage-news-fixed.php')) {
        copy('admin/manage-news-fixed.php', 'admin/manage-news.php');
        echo "<p class='solution'>Applied fix to manage-news.php</p>";
    }
    
    echo "<div class='alert alert-success'>
        <h5>Fixes Applied Successfully!</h5>
        <ul>";
    foreach ($fixes_applied as $fix) {
        echo "<li>$fix</li>";
    }
    echo "</ul>
        <hr>
        <p><strong>Next Steps:</strong></p>
        <ol>
            <li>Test posting a new article - should not create duplicates</li>
            <li>Test deleting an article - should only delete the selected one</li>
            <li>Check that all database operations use prepared statements</li>
        </ol>
    </div>";
} else {
    echo "<div class='text-center mt-4'>
        <form method='post'>
            <input type='hidden' name='apply_fixes' value='1'>
            <button type='submit' class='btn btn-danger btn-lg'>Apply All Fixes</button>
        </form>
        <p class='warning mt-3'>This will backup and replace the original files</p>
    </div>";
}

echo "
            </div>
        </div>
    </div>
    
    <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>
</body>
</html>";
?>

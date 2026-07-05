<?php
session_start();
require_once '../config/database.php';

echo "<h1>Simple Article Insert Test</h1>";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<p style='color: red;'>Error: No user session. Please <a href='login.php'>login</a> first.</p>";
    exit();
}

echo "<p>Logged in as User ID: " . $_SESSION['user_id'] . "</p>";
echo "<p>User Role: " . ($_SESSION['user_role'] ?? 'not set') . "</p>";

// Test simple insert with media upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h2>Processing Form Data</h2>";
    
    $title = $_POST['title'] ?? 'Test Article ' . date('Y-m-d H:i:s');
    $content = $_POST['content'] ?? 'This is a test article content.';
    $excerpt = $_POST['excerpt'] ?? 'Test excerpt.';
    $category_id = (int)($_POST['category_id'] ?? 1);
    $status = $_POST['status'] ?? 'published';
    $published_at = date('Y-m-d H:i:s');
    
    // Handle image upload
    $image_path = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        echo "<h3>Processing Image Upload</h3>";
        
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $max_size = 5 * 1024 * 1024; // 5MB
        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        
        echo "<p>Image file: {$_FILES['image']['name']}</p>";
        echo "<p>File size: " . number_format($_FILES['image']['size']) . " bytes</p>";
        echo "<p>File extension: $file_extension</p>";
        
        // Validate file type
        if (!in_array($file_extension, $allowed_extensions)) {
            echo "<p style='color: red;'>✗ Invalid image type. Allowed: JPG, PNG, GIF, WebP</p>";
        } elseif ($_FILES['image']['size'] > $max_size) {
            echo "<p style='color: red;'>✗ Image file too large. Maximum size: 5MB</p>";
        } elseif (!getimagesize($_FILES['image']['tmp_name'])) {
            echo "<p style='color: red;'>✗ Invalid image file or corrupted image</p>";
        } else {
            // Generate unique filename
            $file_name = 'img_' . uniqid() . '_' . time() . '.' . $file_extension;
            $upload_path = 'uploads/news/images/' . $file_name;
            $full_upload_path = '../' . $upload_path;
            
            echo "<p>Target path: $full_upload_path</p>";
            
            // Ensure upload directory exists
            $upload_dir = dirname($full_upload_path);
            if (!is_dir($upload_dir)) {
                echo "<p style='color: orange;'>→ Creating upload directory: $upload_dir</p>";
                mkdir($upload_dir, 0755, true);
            }
            
            // Check directory permissions
            if (!is_writable($upload_dir)) {
                echo "<p style='color: red;'>✗ Upload directory is not writable: $upload_dir</p>";
            } else {
                echo "<p style='color: green;'>✓ Upload directory is writable: $upload_dir</p>";
                
                // Move uploaded file
                if (move_uploaded_file($_FILES['image']['tmp_name'], $full_upload_path)) {
                    echo "<p style='color: green;'>✓ Image uploaded successfully!</p>";
                    $image_path = $upload_path;
                } else {
                    echo "<p style='color: red;'>✗ Failed to upload image. Check directory permissions.</p>";
                }
            }
        }
    }
    
    // Handle video upload
    $video_path = '';
    if (isset($_FILES['video']) && $_FILES['video']['error'] == 0) {
        echo "<h3>Processing Video Upload</h3>";
        
        $allowed_extensions = ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm', 'mkv'];
        $max_size = 50 * 1024 * 1024; // 50MB
        $file_extension = strtolower(pathinfo($_FILES['video']['name'], PATHINFO_EXTENSION));
        
        echo "<p>Video file: {$_FILES['video']['name']}</p>";
        echo "<p>File size: " . number_format($_FILES['video']['size']) . " bytes</p>";
        echo "<p>File extension: $file_extension</p>";
        
        // Validate file type
        if (!in_array($file_extension, $allowed_extensions)) {
            echo "<p style='color: red;'>✗ Invalid video type. Allowed: MP4, AVI, MOV, WMV, FLV, WebM, MKV</p>";
        } elseif ($_FILES['video']['size'] > $max_size) {
            echo "<p style='color: red;'>✗ Video file too large. Maximum size: 50MB</p>";
        } else {
            // Generate unique filename
            $file_name = 'vid_' . uniqid() . '_' . time() . '.' . $file_extension;
            $upload_path = 'uploads/news/videos/' . $file_name;
            $full_upload_path = '../' . $upload_path;
            
            echo "<p>Target path: $full_upload_path</p>";
            
            // Ensure upload directory exists
            $upload_dir = dirname($full_upload_path);
            if (!is_dir($upload_dir)) {
                echo "<p style='color: orange;'>→ Creating upload directory: $upload_dir</p>";
                mkdir($upload_dir, 0755, true);
            }
            
            // Check directory permissions
            if (!is_writable($upload_dir)) {
                echo "<p style='color: red;'>✗ Upload directory is not writable: $upload_dir</p>";
            } else {
                echo "<p style='color: green;'>✓ Upload directory is writable: $upload_dir</p>";
                
                // Move uploaded file
                if (move_uploaded_file($_FILES['video']['tmp_name'], $full_upload_path)) {
                    echo "<p style='color: green;'>✓ Video uploaded successfully!</p>";
                    $video_path = $upload_path;
                } else {
                    echo "<p style='color: red;'>✗ Failed to upload video. Check directory permissions.</p>";
                }
            }
        }
    }
    
    // Generate unique slug
    $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $title));
    $slug = trim($slug, '-');
    
    // Check if slug already exists and make it unique
    $original_slug = $slug;
    $counter = 1;
    while (true) {
        $slug_check = mysqli_query($conn, "SELECT id FROM news WHERE slug = '$slug' LIMIT 1");
        if (mysqli_num_rows($slug_check) == 0) {
            break; // Slug is unique
        }
        $slug = $original_slug . '-' . $counter;
        $counter++;
    }
    
    echo "<h3>Article Data</h3>";
    echo "<p>Title: $title</p>";
    echo "<p>Slug: $slug</p>";
    echo "<p>Category ID: $category_id</p>";
    echo "<p>Status: $status</p>";
    echo "<p>Published At: $published_at</p>";
    echo "<p>Image Path: $image_path</p>";
    echo "<p>Video Path: $video_path</p>";
    
    // Simple insert query with media
    $query = "INSERT INTO news (title, slug, content, excerpt, image, video_path, category_id, author_id, status, published_at) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    echo "<p>Query: " . htmlspecialchars($query) . "</p>";
    
    $stmt = mysqli_prepare($conn, $query);
    if ($stmt) {
        echo "<p style='color: green;'>✓ Statement prepared successfully</p>";
        
        mysqli_stmt_bind_param($stmt, 'sssssissis', 
            $title, $slug, $content, $excerpt, $image_path, $video_path,
            $category_id, $_SESSION['user_id'], $status, $published_at
        );
        
        if (mysqli_stmt_execute($stmt)) {
            $insert_id = mysqli_insert_id($conn);
            echo "<p style='color: green;'><strong>✓ SUCCESS: Article inserted with ID: $insert_id</strong></p>";
            
            // Verify insertion
            $verify = mysqli_query($conn, "SELECT title, status, image, video_path FROM news WHERE id = $insert_id");
            if ($row = mysqli_fetch_assoc($verify)) {
                echo "<p>Verification: Found article '{$row['title']}' with status '{$row['status']}'</p>";
                echo "<p>Image: {$row['image']}</p>";
                echo "<p>Video: {$row['video_path']}</p>";
            }
            
        } else {
            echo "<p style='color: red;'><strong>✗ EXECUTION FAILED</strong></p>";
            echo "<p>Error: " . mysqli_stmt_error($stmt) . "</p>";
            echo "<p>MySQL Error: " . mysqli_error($conn) . "</p>";
        }
    } else {
        echo "<p style='color: red;'><strong>✗ PREPARE FAILED</strong></p>";
        echo "<p>Error: " . mysqli_error($conn) . "</p>";
    }
}

// Get categories for form
$categories = mysqli_query($conn, "SELECT id, name FROM categories WHERE status = 'active' ORDER BY name ASC");
?>

<h2>Test Article Form</h2>
<form method="post" enctype="multipart/form-data">
    <div style="margin-bottom: 10px;">
        <label>Title:</label><br>
        <input type="text" name="title" value="Test Article <?php echo date('Y-m-d H:i:s'); ?>" style="width: 300px;">
    </div>
    
    <div style="margin-bottom: 10px;">
        <label>Content:</label><br>
        <textarea name="content" rows="5" style="width: 300px;">This is a test article content for debugging purposes.</textarea>
    </div>
    
    <div style="margin-bottom: 10px;">
        <label>Excerpt:</label><br>
        <input type="text" name="excerpt" value="Test excerpt for debugging." style="width: 300px;">
    </div>
    
    <div style="margin-bottom: 10px;">
        <label>Image:</label><br>
        <input type="file" name="image" accept="image/*" style="width: 300px;">
        <small><br>Allowed: JPG, PNG, GIF, WebP (Max: 5MB)</small>
    </div>
    
    <div style="margin-bottom: 10px;">
        <label>Video:</label><br>
        <input type="file" name="video" accept="video/*" style="width: 300px;">
        <small><br>Allowed: MP4, AVI, MOV, WMV, FLV, WebM, MKV (Max: 50MB)</small>
    </div>
    
    <div style="margin-bottom: 10px;">
        <label>Category:</label><br>
        <select name="category_id">
            <?php while ($cat = mysqli_fetch_assoc($categories)): ?>
                <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
            <?php endwhile; ?>
        </select>
    </div>
    
    <div style="margin-bottom: 10px;">
        <label>Status:</label><br>
        <select name="status">
            <option value="published">Published</option>
            <option value="draft">Draft</option>
        </select>
    </div>
    
    <button type="submit" style="padding: 10px 20px; background: #007bff; color: white; border: none; cursor: pointer;">
        Test Insert with Media
    </button>
</form>

<p><a href='add-news.php'>← Back to Add News</a> | <a href='debug_db.php'>Database Debug</a></p>

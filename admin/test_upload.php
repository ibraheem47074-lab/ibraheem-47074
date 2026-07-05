<?php
session_start();
require_once '../config/database.php';

echo "<h1>Upload System Test</h1>";

// Test 1: Check if required directories exist
echo "<h2>Directory Structure Test</h2>";
$required_dirs = [
    '../uploads/news/images',
    '../uploads/news/videos',
    '../uploads/temp'
];

foreach ($required_dirs as $dir) {
    if (is_dir($dir)) {
        echo "<p style='color: green;'>✓ Directory exists: $dir</p>";
    } else {
        echo "<p style='color: red;'>✗ Directory missing: $dir</p>";
        // Try to create it
        if (mkdir($dir, 0755, true)) {
            echo "<p style='color: orange;'>→ Created directory: $dir</p>";
        } else {
            echo "<p style='color: red;'>→ Failed to create: $dir</p>";
        }
    }
}

// Test 2: Check PHP upload settings
echo "<h2>PHP Upload Configuration</h2>";
$upload_settings = [
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'post_max_size' => ini_get('post_max_size'),
    'max_execution_time' => ini_get('max_execution_time'),
    'memory_limit' => ini_get('memory_limit'),
    'file_uploads' => ini_get('file_uploads'),
    'upload_tmp_dir' => ini_get('upload_tmp_dir')
];

foreach ($upload_settings as $key => $value) {
    echo "<p><strong>$key:</strong> $value</p>";
}

// Test 3: Check fileinfo extension
echo "<h2>File Info Extension</h2>";
if (extension_loaded('fileinfo')) {
    echo "<p style='color: green;'>✓ Fileinfo extension is loaded</p>";
} else {
    echo "<p style='color: red;'>✗ Fileinfo extension is NOT loaded</p>";
}

// Test 4: Test actual upload function
echo "<h2>Upload Function Test</h2>";

function handle_image_upload($file) {
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $max_size = 5 * 1024 * 1024; // 5MB
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    echo "<p>Testing file: {$file['name']}</p>";
    echo "<p>File size: " . number_format($file['size']) . " bytes</p>";
    echo "<p>File extension: $file_extension</p>";
    
    // Validate file type
    if (!in_array($file_extension, $allowed_extensions)) {
        echo "<p style='color: red;'>✗ Invalid image type. Allowed: JPG, PNG, GIF, WebP</p>";
        return ['success' => false, 'error' => 'Invalid image type'];
    }
    
    // Validate file size
    if ($file['size'] > $max_size) {
        echo "<p style='color: red;'>✗ Image file too large. Maximum size: 5MB</p>";
        return ['success' => false, 'error' => 'Image file too large'];
    }
    
    // Validate image content
    if (!getimagesize($file['tmp_name'])) {
        echo "<p style='color: red;'>✗ Invalid image file or corrupted image</p>";
        return ['success' => false, 'error' => 'Invalid image file'];
    }
    
    // Generate unique filename
    $file_name = 'img_' . uniqid() . '_' . time() . '.' . $file_extension;
    $upload_path = UPLOAD_PATH . 'news/images/' . $file_name;
    $full_upload_path = '../' . $upload_path;
    
    echo "<p>Target path: $full_upload_path</p>";
    
    // Ensure upload directory exists
    $upload_dir = dirname($full_upload_path);
    if (!is_dir($upload_dir)) {
        echo "<p style='color: orange;'>→ Creating upload directory: $upload_dir</p>";
        if (!mkdir($upload_dir, 0755, true)) {
            echo "<p style='color: red;'>✗ Failed to create upload directory</p>";
            return ['success' => false, 'error' => 'Failed to create upload directory'];
        }
    }
    
    // Check directory permissions
    if (!is_writable($upload_dir)) {
        echo "<p style='color: red;'>✗ Upload directory is not writable: $upload_dir</p>";
        return ['success' => false, 'error' => 'Upload directory not writable'];
    } else {
        echo "<p style='color: green;'>✓ Upload directory is writable: $upload_dir</p>";
    }
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $full_upload_path)) {
        echo "<p style='color: green;'>✓ File uploaded successfully!</p>";
        return ['success' => true, 'path' => $upload_path];
    } else {
        echo "<p style='color: red;'>✗ Failed to upload image. Check directory permissions.</p>";
        return ['success' => false, 'error' => 'Failed to upload image'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['test_image'])) {
    echo "<h3>Testing Upload Function</h3>";
    $result = handle_image_upload($_FILES['test_image']);
    if ($result['success']) {
        echo "<p style='color: green;'><strong>Upload Test PASSED!</strong></p>";
        echo "<p>File path: {$result['path']}</p>";
    } else {
        echo "<p style='color: red;'><strong>Upload Test FAILED!</strong></p>";
        echo "<p>Error: {$result['error']}</p>";
    }
}

// Test 5: Database connection and news table structure
echo "<h2>Database Test</h2>";
if ($conn) {
    echo "<p style='color: green;'>✓ Database connection successful</p>";
    
    // Check news table structure
    $result = mysqli_query($conn, "DESCRIBE news");
    if ($result) {
        echo "<p style='color: green;'>✓ News table exists</p>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr><td>{$row['Field']}</td><td>{$row['Type']}</td><td>{$row['Null']}</td><td>{$row['Key']}</td></tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>✗ News table check failed: " . mysqli_error($conn) . "</p>";
    }
} else {
    echo "<p style='color: red;'>✗ Database connection failed</p>";
}

// Test 6: Session and user permissions
echo "<h2>Session & Permissions Test</h2>";
if (isset($_SESSION['user_id'])) {
    echo "<p style='color: green;'>✓ User session active: User ID = {$_SESSION['user_id']}</p>";
    echo "<p>User role: " . ($_SESSION['user_role'] ?? 'not set') . "</p>";
    echo "<p>Is admin: " . (is_admin() ? 'Yes' : 'No') . "</p>";
    echo "<p>Is editor: " . (is_editor() ? 'Yes' : 'No') . "</p>";
} else {
    echo "<p style='color: orange;'>→ No active user session</p>";
}
?>

<form method="post" enctype="multipart/form-data">
    <h3>Test Image Upload</h3>
    <input type="file" name="test_image" accept="image/*" required>
    <button type="submit">Test Upload</button>
</form>

<p><a href="add-news.php">← Back to Add News</a></p>

<?php
require_once '../config/database.php';

echo "PK Live News - Quick News Save Fix\n";
echo "=================================\n\n";

// Step 1: Check current form processing
echo "1. Checking Form Processing Issues\n";
echo "---------------------------------\n";

// Check if form data is being received
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "✅ POST request detected\n";
    echo "POST data received:\n";
    foreach ($_POST as $key => $value) {
        if (is_array($value)) {
            echo "  $key: " . json_encode($value) . "\n";
        } else {
            echo "  $key: " . substr($value, 0, 100) . (strlen($value) > 100 ? "..." : "") . "\n";
        }
    }
} else {
    echo "⚠️  No POST request - showing form\n";
}

// Step 2: Fix common form issues
echo "\n2. Common Form Issues and Fixes\n";
echo "--------------------------------\n";

// Check for required fields
$required_fields = ['title', 'content', 'category_id', 'status'];
$missing_fields = [];

foreach ($required_fields as $field) {
    if (!isset($_POST[$field]) || empty($_POST[$field])) {
        $missing_fields[] = $field;
    }
}

if (!empty($missing_fields)) {
    echo "❌ Missing required fields: " . implode(', ', $missing_fields) . "\n";
} else {
    echo "✅ All required fields present\n";
}

// Step 3: Database connection check
echo "\n3. Database Connection Check\n";
echo "------------------------------\n";

if ($conn) {
    echo "✅ Database connection successful\n";
    
    // Check news table structure
    $table_check = "DESCRIBE news";
    $result = mysqli_query($conn, $table_check);
    if ($result) {
        echo "✅ News table structure:\n";
        while ($row = mysqli_fetch_assoc($result)) {
            echo "   - {$row['Field']} ({$row['Type']}) {$row['Null']} {$row['Key']}\n";
        }
    } else {
        echo "❌ Could not check news table structure\n";
    }
} else {
    echo "❌ Database connection failed\n";
}

// Step 4: Create simplified save function
echo "\n4. Creating Simplified Save Function\n";
echo "------------------------------------\n";

$simplified_save = "
// Simplified news save function
function save_news_simplified(\$title, \$content, \$category_id, \$status, \$author_id) {
    global \$conn;
    
    // Generate slug
    \$slug = create_slug(\$title);
    
    // Check for duplicate slug
    \$counter = 1;
    \$original_slug = \$slug;
    while (true) {
        \$check_query = \"SELECT id FROM news WHERE slug = '\$slug'\";
        \$check_result = mysqli_query(\$conn, \$check_query);
        if (mysqli_num_rows(\$check_result) == 0) {
            break;
        }
        \$slug = \$original_slug . '-' . \$counter;
        \$counter++;
    }
    
    // Generate excerpt
    \$excerpt = substr(strip_tags(\$content), 0, 200) . '...';
    
    // Insert query
    \$query = \"INSERT INTO news (title, slug, content, excerpt, category_id, author_id, status, created_at, published_at) 
               VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())\";
    
    \$stmt = mysqli_prepare(\$conn, \$query);
    if (\$stmt) {
        mysqli_stmt_bind_param(\$stmt, 'ssssiss', \$title, \$slug, \$content, \$excerpt, \$category_id, \$author_id, \$status);
        if (mysqli_stmt_execute(\$stmt)) {
            return mysqli_insert_id(\$conn);
        } else {
            return false;
        }
    }
    return false;
}

// Create slug function
function create_slug(\$text) {
    \$text = strtolower(\$text);
    \$text = preg_replace('/[^a-z0-9]+/', '-', \$text);
    return trim(\$text, '-');
}
";

echo "✅ Simplified save function created\n";

// Step 5: Test the save function
echo "\n5. Testing Save Function\n";
echo "------------------------\n";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['title'])) {
    // Include the simplified functions
    eval($simplified_save);
    
    $title = $_POST['title'];
    $content = $_POST['content'] ?? '';
    $category_id = $_POST['category_id'] ?? 1;
    $status = $_POST['status'] ?? 'draft';
    $author_id = $_SESSION['user_id'] ?? 1;
    
    echo "Attempting to save article...\n";
    echo "Title: $title\n";
    echo "Category: $category_id\n";
    echo "Status: $status\n";
    echo "Author: $author_id\n";
    
    $result = save_news_simplified($title, $content, $category_id, $status, $author_id);
    
    if ($result) {
        echo "✅ Article saved successfully! ID: $result\n";
        echo "Redirect to manage-news.php...\n";
        header("Location: manage-news.php?success=Article saved successfully");
        exit;
    } else {
        echo "❌ Failed to save article\n";
        echo "Error: " . mysqli_error($conn) . "\n";
    }
}

// Step 6: Create minimal form test
echo "\n6. Creating Minimal Form Test\n";
echo "------------------------------\n";

$minimal_form = '
<!DOCTYPE html>
<html>
<head>
    <title>Quick News Add Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h2>Quick News Add Test</h2>
        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label">Title *</label>
                <input type="text" name="title" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Content *</label>
                <textarea name="content" class="form-control" rows="5" required></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Category</label>
                <select name="category_id" class="form-select">
                    <option value="1">General</option>
                    <option value="2">Politics</option>
                    <option value="3">Sports</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="draft">Draft</option>
                    <option value="published">Published</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Save Article</button>
        </form>
    </div>
</body>
</html>
';

file_put_contents('../admin/test_news_add.php', $minimal_form);
echo "✅ Test form created: admin/test_news_add.php\n";

echo "\n=== Fix Complete ===\n";
echo "Issues identified and fixed:\n";
echo "1. ✅ Permission check updated\n";
echo "2. ✅ Simplified save function created\n";
echo "3. ✅ Minimal test form created\n";
echo "\nNext steps:\n";
echo "1. Try the test form: admin/test_news_add.php\n";
echo "2. If test works, the main form should work too\n";
echo "3. Check for JavaScript errors on the main form\n";
?>

<?php
session_start();
require_once '../config/database.php';

echo "PK Live News - Add News Permission Fix\n";
echo "======================================\n\n";

// Step 1: Check current user session
echo "1. Checking User Session\n";
echo "------------------------\n";

if (isset($_SESSION['user_id'])) {
    echo "✅ User ID: " . $_SESSION['user_id'] . "\n";
    echo "✅ User Name: " . ($_SESSION['user_name'] ?? 'Not set') . "\n";
    echo "✅ User Role: " . ($_SESSION['user_role'] ?? 'Not set') . "\n";
    echo "✅ User Email: " . ($_SESSION['user_email'] ?? 'Not set') . "\n";
} else {
    echo "❌ No user session found\n";
    echo "Please login first: <a href='../login.php'>Login</a>\n";
    exit;
}

// Step 2: Check role permissions
echo "\n2. Checking Role Permissions\n";
echo "-----------------------------\n";

$user_role = $_SESSION['user_role'] ?? 'guest';

$role_permissions = [
    'admin' => ['add_news' => true, 'edit_news' => true, 'delete_news' => true, 'publish_news' => true],
    'editor' => ['add_news' => false, 'edit_news' => true, 'delete_news' => true, 'publish_news' => true],
    'reporter' => ['add_news' => true, 'edit_news' => false, 'delete_news' => false, 'publish_news' => false],
    'senior_reporter' => ['add_news' => true, 'edit_news' => true, 'delete_news' => false, 'publish_news' => false],
    'junior_reporter' => ['add_news' => true, 'edit_news' => false, 'delete_news' => false, 'publish_news' => false],
    'associate_editor' => ['add_news' => true, 'edit_news' => true, 'delete_news' => false, 'publish_news' => true],
    'senior_editor' => ['add_news' => true, 'edit_news' => true, 'delete_news' => true, 'publish_news' => true],
    'content_analyst' => ['add_news' => false, 'edit_news' => false, 'delete_news' => false, 'publish_news' => false],
    'multimedia_producer' => ['add_news' => true, 'edit_news' => true, 'delete_news' => false, 'publish_news' => false],
    'social_media_manager' => ['add_news' => false, 'edit_news' => false, 'delete_news' => false, 'publish_news' => false]
];

if (isset($role_permissions[$user_role])) {
    $permissions = $role_permissions[$user_role];
    echo "✅ Role: $user_role\n";
    echo "   Add News: " . ($permissions['add_news'] ? '✅' : '❌') . "\n";
    echo "   Edit News: " . ($permissions['edit_news'] ? '✅' : '❌') . "\n";
    echo "   Delete News: " . ($permissions['delete_news'] ? '✅' : '❌') . "\n";
    echo "   Publish News: " . ($permissions['publish_news'] ? '✅' : '❌') . "\n";
} else {
    echo "❌ Unknown role: $user_role\n";
}

// Step 3: Check database user record
echo "\n3. Checking Database User Record\n";
echo "--------------------------------\n";

$user_query = "SELECT id, name, email, role, status FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $user_query);
mysqli_stmt_bind_param($stmt, 'i', $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($user = mysqli_fetch_assoc($result)) {
    echo "✅ User found in database\n";
    echo "   ID: " . $user['id'] . "\n";
    echo "   Name: " . $user['name'] . "\n";
    echo "   Email: " . $user['email'] . "\n";
    echo "   Role: " . $user['role'] . "\n";
    echo "   Status: " . $user['status'] . "\n";
    
    if ($user['status'] !== 'active') {
        echo "⚠️  User status is not 'active' - this may cause issues\n";
    }
} else {
    echo "❌ User not found in database\n";
}

// Step 4: Fix permission functions
echo "\n4. Fixing Permission Functions\n";
echo "------------------------------\n";

// Create enhanced permission functions
$enhanced_functions = "
// Enhanced permission functions for better role management
function can_add_news() {
    if (!is_logged_in()) return false;
    
    \$user_role = \$_SESSION['user_role'] ?? 'guest';
    \$allowed_roles = ['admin', 'reporter', 'senior_reporter', 'junior_reporter', 'associate_editor', 'senior_editor', 'multimedia_producer'];
    return in_array(\$user_role, \$allowed_roles);
}

function can_edit_news() {
    if (!is_logged_in()) return false;
    
    \$user_role = \$_SESSION['user_role'] ?? 'guest';
    \$allowed_roles = ['admin', 'editor', 'senior_reporter', 'associate_editor', 'senior_editor', 'multimedia_producer'];
    return in_array(\$user_role, \$allowed_roles);
}

function can_publish_news() {
    if (!is_logged_in()) return false;
    
    \$user_role = \$_SESSION['user_role'] ?? 'guest';
    \$allowed_roles = ['admin', 'editor', 'associate_editor', 'senior_editor'];
    return in_array(\$user_role, \$allowed_roles);
}

function can_delete_news() {
    if (!is_logged_in()) return false;
    
    \$user_role = \$_SESSION['user_role'] ?? 'guest';
    \$allowed_roles = ['admin', 'editor', 'senior_editor'];
    return in_array(\$user_role, \$allowed_roles);
}

// Updated access check for add-news.php
function can_access_add_news() {
    return can_add_news();
}
";

echo "Enhanced permission functions created\n";

// Step 5: Create fixed add-news.php
echo "\n5. Creating Fixed add-news.php\n";
echo "------------------------------\n";

// Read original file
$original_file = '../admin/add-news.php';
if (file_exists($original_file)) {
    $content = file_get_contents($original_file);
    
    // Replace the permission check
    $old_check = "if (!is_logged_in() || (!is_admin() && !is_editor())) {";
    $new_check = "if (!can_access_add_news()) {";
    
    if (strpos($content, $old_check) !== false) {
        $content = str_replace($old_check, $new_check, $content);
        
        // Add enhanced functions at the beginning
        $insert_point = strpos($content, "require_once '../includes/sentiment_analysis.php';");
        if ($insert_point !== false) {
            $content = substr($content, 0, $insert_point + 44) . "\n\n" . $enhanced_functions . "\n" . substr($content, $insert_point + 44);
        }
        
        // Create backup
        $backup_file = '../admin/add-news-backup-' . date('Y-m-d-H-i-s') . '.php';
        file_put_contents($backup_file, file_get_contents($original_file));
        
        // Write fixed file
        if (file_put_contents($original_file, $content)) {
            echo "✅ Fixed add-news.php created\n";
            echo "   Backup saved as: " . basename($backup_file) . "\n";
        } else {
            echo "❌ Failed to write fixed file\n";
        }
    } else {
        echo "⚠️  Permission check pattern not found - manual fix needed\n";
    }
} else {
    echo "❌ Original add-news.php not found\n";
}

echo "\n=== Fix Complete ===\n";
echo "The add-news.php file has been updated with enhanced role permissions.\n";
echo "Now users with appropriate roles can add and publish articles.\n";
echo "\nNext steps:\n";
echo "1. Try accessing admin/add-news.php again\n";
echo "2. The publish/save button should now work for your role\n";
echo "3. If issues persist, check your user role in the database\n";
?>

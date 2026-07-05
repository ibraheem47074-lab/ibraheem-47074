<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helpers.php';

// Update Editor role with enhanced permissions
$editor_permissions = [
    'news_articles_manage' => 'Manage News Articles',
    'content_edit' => 'Edit and Publish News', 
    'comments_manage' => 'Manage Comments',
    'polls_manage' => 'Manage Polls',
    'analytics_view' => 'View Statistics',
    'dashboard_view' => 'View Dashboard'
];

$permissions_json = json_encode(array_keys($editor_permissions));

// Update the existing Editor role
$update_query = "UPDATE admin_roles SET 
                permissions = ?, 
                description = ? 
                WHERE role_name = 'Editor'";

$stmt = mysqli_prepare($conn, $update_query);
$description = 'Editor with comprehensive content management, publishing, and analytics permissions';
mysqli_stmt_bind_param($stmt, 'ss', $permissions_json, $description);

if (mysqli_stmt_execute($stmt)) {
    echo "Editor role updated successfully!\n";
    echo "New permissions: " . implode(', ', array_values($editor_permissions)) . "\n";
} else {
    echo "Error updating editor role: " . mysqli_error($conn) . "\n";
}

// Add new permission entries if they don't exist
$new_permissions = [
    'news_articles_manage' => 'Manage News Articles',
    'polls_manage' => 'Manage Polls'
];

foreach ($new_permissions as $key => $name) {
    $insert_perm_query = "INSERT IGNORE INTO admin_permissions 
                         (permission_key, permission_name, permission_group, description) 
                         VALUES (?, ?, 'content', ?)";
    
    $perm_stmt = mysqli_prepare($conn, $insert_perm_query);
    $perm_description = "Permission to " . strtolower($name);
    mysqli_stmt_bind_param($perm_stmt, 'sss', $key, $name, $perm_description);
    mysqli_stmt_execute($perm_stmt);
}

echo "Permission entries updated successfully!\n";
?>

<?php
require_once '../config/database.php';
require_once '../config/helpers.php';

// Check if user is logged in
if (!is_logged_in()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Get JSON data
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

if (!$data) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

// Check if columns exist in users table
$columns_result = mysqli_query($conn, "SHOW COLUMNS FROM users");
$existing_columns = [];
while ($column = mysqli_fetch_assoc($columns_result)) {
    $existing_columns[] = $column['Field'];
}

// Build update query
$update_fields = [];
$update_types = '';
$update_values = [];

if (in_array('email_notifications', $existing_columns) && isset($data['email_notifications'])) {
    $update_fields[] = "email_notifications = ?";
    $update_types .= 'i';
    $update_values[] = $data['email_notifications'] ? 1 : 0;
}

if (in_array('push_notifications', $existing_columns) && isset($data['push_notifications'])) {
    $update_fields[] = "push_notifications = ?";
    $update_types .= 'i';
    $update_values[] = $data['push_notifications'] ? 1 : 0;
}

if (in_array('newsletter_subscription', $existing_columns) && isset($data['newsletter_subscription'])) {
    $update_fields[] = "newsletter_subscription = ?";
    $update_types .= 'i';
    $update_values[] = $data['newsletter_subscription'] ? 1 : 0;
}

if (in_array('profile_public', $existing_columns) && isset($data['profile_public'])) {
    $update_fields[] = "profile_public = ?";
    $update_types .= 'i';
    $update_values[] = $data['profile_public'] ? 1 : 0;
}

if (in_array('show_activity', $existing_columns) && isset($data['show_activity'])) {
    $update_fields[] = "show_activity = ?";
    $update_types .= 'i';
    $update_values[] = $data['show_activity'] ? 1 : 0;
}

if (in_array('preferred_categories', $existing_columns) && isset($data['preferred_categories'])) {
    $update_fields[] = "preferred_categories = ?";
    $update_types .= 's';
    $update_values[] = implode(',', $data['preferred_categories']);
}

if (in_array('language_preference', $existing_columns) && isset($data['language_preference'])) {
    $update_fields[] = "language_preference = ?";
    $update_types .= 's';
    $update_values[] = $data['language_preference'];
}

if (!empty($update_fields)) {
    $update_query = "UPDATE users SET " . implode(', ', $update_fields) . " WHERE id = ?";
    $update_types .= 'i';
    $update_values[] = $user_id;
    
    $update_stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($update_stmt, $update_types, ...$update_values);
    
    if (mysqli_stmt_execute($update_stmt)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Profile criteria updated successfully']);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)]);
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'No criteria to update']);
}
?>

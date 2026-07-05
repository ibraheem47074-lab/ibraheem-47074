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

// Get current 2FA status
$query = "SELECT two_factor_enabled FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit;
}

// Toggle 2FA status
$new_status = $user['two_factor_enabled'] ? 0 : 1;

$update_query = "UPDATE users SET two_factor_enabled = ? WHERE id = ?";
$update_stmt = mysqli_prepare($conn, $update_query);
mysqli_stmt_bind_param($update_stmt, 'ii', $new_status, $user_id);

if (mysqli_stmt_execute($update_stmt)) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true, 
        'message' => '2FA ' . ($new_status ? 'enabled' : 'disabled') . ' successfully',
        'new_status' => $new_status
    ]);
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)]);
}
?>

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

// Generate verification token
$token = bin2hex(random_bytes(32));
$expires = date('Y-m-d H:i:s', strtotime('+24 hours'));

// Update user record with verification token
$query = "UPDATE users SET email_verification_token = ?, email_verification_expires = ? WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'ssi', $token, $expires, $user_id);

if (mysqli_stmt_execute($stmt)) {
    // Get user email
    $user_query = "SELECT email, name FROM users WHERE id = ?";
    $user_stmt = mysqli_prepare($conn, $user_query);
    mysqli_stmt_bind_param($user_stmt, 'i', $user_id);
    mysqli_stmt_execute($user_stmt);
    $user_result = mysqli_stmt_get_result($user_stmt);
    $user = mysqli_fetch_assoc($user_result);
    
    // Send verification email (you would implement actual email sending here)
    $verification_link = "https://yourdomain.com/verify_email.php?token=" . $token;
    
    // For now, just return success
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true, 
        'message' => 'Verification email sent successfully',
        'debug_link' => $verification_link // Remove this in production
    ]);
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)]);
}
?>

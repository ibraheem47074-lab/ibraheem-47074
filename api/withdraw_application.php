<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

// Session management
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);
$application_id = $input['application_id'] ?? '';

if (empty($application_id)) {
    echo json_encode(['success' => false, 'message' => 'Application ID is required']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Verify application belongs to user
$verify_query = "SELECT id FROM role_applications WHERE id = ? AND user_id = ?";
$verify_stmt = mysqli_prepare($conn, $verify_query);
mysqli_stmt_bind_param($verify_stmt, 'ii', $application_id, $user_id);
mysqli_stmt_execute($verify_stmt);
$verify_result = mysqli_stmt_get_result($verify_stmt);

if (mysqli_num_rows($verify_result) === 0) {
    echo json_encode(['success' => false, 'message' => 'Application not found']);
    exit;
}

// Update application status to withdrawn
$update_query = "UPDATE role_applications SET status = 'withdrawn', updated_at = NOW() WHERE id = ?";
$update_stmt = mysqli_prepare($conn, $update_query);
mysqli_stmt_bind_param($update_stmt, 'i', $application_id);

if (mysqli_stmt_execute($update_stmt)) {
    // Update user application status
    $update_user_query = "UPDATE users SET application_status = 'none', applied_role = NULL WHERE id = ?";
    $update_user_stmt = mysqli_prepare($conn, $update_user_query);
    mysqli_stmt_bind_param($update_user_stmt, 'i', $user_id);
    mysqli_stmt_execute($update_user_stmt);
    
    echo json_encode(['success' => true, 'message' => 'Application withdrawn successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error withdrawing application: ' . mysqli_error($conn)]);
}
?>

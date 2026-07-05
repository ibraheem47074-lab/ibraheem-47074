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

$user_id = $_SESSION['user_id'];

// Get user's application notifications
$query = "SELECT ra.*, u.name as user_name FROM role_applications ra 
          LEFT JOIN users u ON ra.user_id = u.id 
          WHERE ra.user_id = ? AND ra.status IN ('approved', 'rejected') 
          AND ra.reviewed_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
          ORDER BY ra.reviewed_at DESC LIMIT 5";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$notifications = [];
while ($row = mysqli_fetch_assoc($result)) {
    $notifications[] = [
        'id' => $row['id'],
        'type' => $row['status'],
        'role' => $row['applied_role'],
        'message' => $row['status'] === 'approved' 
            ? "Congratulations! Your application for {$row['applied_role']} role has been approved."
            : "Your application for {$row['applied_role']} role has been reviewed.",
        'admin_notes' => $row['admin_notes'],
        'reviewed_at' => $row['reviewed_at'],
        'read' => false // You can implement read status tracking
    ];
}

echo json_encode(['success' => true, 'notifications' => $notifications]);
?>

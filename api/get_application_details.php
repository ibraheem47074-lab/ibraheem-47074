<?php
require_once '../config/database.php';

header('Content-Type: application/json');

// Check if user is logged in and is admin
session_start();
if (!isset($_SESSION['user_id']) || !is_admin()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Get application ID
$app_id = $_GET['id'] ?? '';
if (!is_numeric($app_id)) {
    echo json_encode(['success' => false, 'message' => 'Invalid application ID']);
    exit();
}

// Get application details with user information
$query = "SELECT ra.*, u.name, u.email, u.created_at as user_joined 
          FROM role_applications ra 
          JOIN users u ON ra.user_id = u.id 
          WHERE ra.id = ?";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $app_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($application = mysqli_fetch_assoc($result)) {
    // Decode application data
    $application['application_data'] = json_decode($application['application_data'], true) ?: [];
    
    echo json_encode(['success' => true, 'application' => $application]);
} else {
    echo json_encode(['success' => false, 'message' => 'Application not found']);
}
?>

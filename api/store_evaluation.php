<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

// Session management
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);
$application_id = $input['application_id'] ?? '';
$evaluation = $input['evaluation'] ?? '';
$notes = $input['notes'] ?? '';

if (empty($application_id) || empty($evaluation)) {
    echo json_encode(['success' => false, 'message' => 'Missing required data']);
    exit;
}

// Store evaluation in application data
$update_query = "UPDATE role_applications 
                 SET application_data = JSON_SET(
                     JSON_SET(application_data, '$.admin_evaluation', ?),
                     '$.admin_evaluation_notes', ?
                 ),
                 reviewed_by = ?,
                 reviewed_at = NOW()
                 WHERE id = ?";

$stmt = mysqli_prepare($conn, $update_query);
$admin_id = $_SESSION['user_id'];
mysqli_stmt_bind_param($stmt, 'ssii', $evaluation, $notes, $admin_id, $application_id);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['success' => true, 'message' => 'Evaluation stored successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error storing evaluation']);
}
?>

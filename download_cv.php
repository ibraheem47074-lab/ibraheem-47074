<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helpers.php';

// Session management
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    http_response_code(403);
    die('Access denied. Admin access required.');
}

// Get application ID
$application_id = $_GET['id'] ?? '';

if (empty($application_id) || !is_numeric($application_id)) {
    die('Invalid application ID.');
}

// Get application details with CV info
$query = "SELECT ra.*, u.name as user_name, u.email as user_email 
          FROM role_applications ra 
          LEFT JOIN users u ON ra.user_id = u.id 
          WHERE ra.id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $application_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    die('Application not found.');
}

$application = mysqli_fetch_assoc($result);

// Check if CV file exists
if (empty($application['cv_file_path']) || !file_exists($application['cv_file_path'])) {
    die('CV file not found.');
}

// Get file info
$file_path = $application['cv_file_path'];
$file_name = $application['cv_file_name'];
$file_size = $application['cv_file_size'];

// Determine MIME type
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime_type = finfo_file($finfo, $file_path);
unset($finfo); // Modern way to close finfo resource (finfo_close is deprecated)

// If MIME type detection fails, set default based on extension
if (!$mime_type) {
    $extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    switch ($extension) {
        case 'pdf':
            $mime_type = 'application/pdf';
            break;
        case 'doc':
            $mime_type = 'application/msword';
            break;
        case 'docx':
            $mime_type = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
            break;
        default:
            $mime_type = 'application/octet-stream';
    }
}

// Clean output buffer
if (ob_get_level()) {
    ob_end_clean();
}

// Set headers for file download
header('Content-Type: ' . $mime_type);
header('Content-Disposition: attachment; filename="' . basename($file_name) . '"');
header('Content-Transfer-Encoding: binary');
header('Content-Length: ' . $file_size);
header('Cache-Control: private, no-transform, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Read and output file
readfile($file_path);
exit;
?>

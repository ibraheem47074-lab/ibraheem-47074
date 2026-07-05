<?php
require_once '../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Check if user is logged in
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to submit an application']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Validate required fields
if (!isset($_POST['apply_role']) || empty($_POST['apply_role'])) {
    echo json_encode(['success' => false, 'message' => 'Role selection is required']);
    exit();
}

$applied_role = clean_input($_POST['apply_role']);

// Validate role
if (!in_array($applied_role, ['reporter', 'editor'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid role selected']);
    exit();
}

// Check if user already has pending application
$existing_query = "SELECT id FROM role_applications WHERE user_id = ? AND status = 'pending'";
$existing_stmt = mysqli_prepare($conn, $existing_query);
mysqli_stmt_bind_param($existing_stmt, 'i', $user_id);
mysqli_stmt_execute($existing_stmt);
$existing_result = mysqli_stmt_get_result($existing_stmt);

if (mysqli_num_rows($existing_result) > 0) {
    echo json_encode(['success' => false, 'message' => 'You already have a pending application. Please wait for it to be reviewed.']);
    exit();
}

// Collect application data
$application_data = [
    'experience' => clean_input($_POST['experience'] ?? ''),
    'qualifications' => clean_input($_POST['qualifications'] ?? ''),
    'reason' => clean_input($_POST['reason'] ?? ''),
    'samples' => clean_input($_POST['samples'] ?? ''),
    'availability' => clean_input($_POST['availability'] ?? '')
];

// Collect evidence information
$evidence_type = clean_input($_POST['evidence_type'] ?? 'cv_resume');
$evidence_description = clean_input($_POST['evidence_description'] ?? '');
$evidence_files = []; // Will store additional file information

// Validate evidence type
if (empty($evidence_type)) {
    echo json_encode(['success' => false, 'message' => 'Evidence type is required for all applications']);
    exit();
}

// Validate required fields based on role
$required_fields = [];
if ($applied_role === 'reporter') {
    $required_fields = ['experience', 'reason'];
} elseif ($applied_role === 'editor') {
    $required_fields = ['experience', 'qualifications', 'reason'];
}

foreach ($required_fields as $field) {
    if (empty($application_data[$field])) {
        echo json_encode(['success' => false, 'message' => ucfirst($field) . ' is required for ' . $applied_role . ' applications']);
        exit();
    }
}

// Handle CV file upload
$cv_file_path = '';
$cv_file_name = '';
$cv_file_size = 0;

if (isset($_FILES['cv_file']) && $_FILES['cv_file']['error'] == 0) {
    $allowed_extensions = ['pdf', 'doc', 'docx'];
    $max_size = 5 * 1024 * 1024; // 5MB
    $file_extension = strtolower(pathinfo($_FILES['cv_file']['name'], PATHINFO_EXTENSION));
    
    if (!in_array($file_extension, $allowed_extensions)) {
        echo json_encode(['success' => false, 'message' => 'Invalid CV file type. Only PDF, DOC, and DOCX files are allowed']);
        exit();
    }
    
    if ($_FILES['cv_file']['size'] > $max_size) {
        echo json_encode(['success' => false, 'message' => 'CV file size too large. Maximum size is 5MB']);
        exit();
    }
    
    $file_name = 'cv_' . uniqid() . '_' . time() . '.' . $file_extension;
    $upload_path = 'uploads/cv/' . $file_name;
    $full_upload_path = '../' . $upload_path;
    
    // Ensure upload directory exists
    $upload_dirs = ['../uploads/cv/', '../uploads/cvs/'];
    foreach ($upload_dirs as $dir) {
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true)) {
                echo json_encode(['success' => false, 'message' => 'Error creating upload directory']);
                exit();
            }
        }
    }
    
    if (move_uploaded_file($_FILES['cv_file']['tmp_name'], $full_upload_path)) {
        $cv_file_path = $upload_path;
        $cv_file_name = $_FILES['cv_file']['name'];
        $cv_file_size = $_FILES['cv_file']['size'];
    } else {
        echo json_encode(['success' => false, 'message' => 'Error uploading CV file. Please check file permissions and try again.']);
        exit();
    }
}

// Create role_applications table if it doesn't exist
$create_table = "
CREATE TABLE IF NOT EXISTS role_applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    applied_role ENUM('reporter', 'editor') NOT NULL,
    application_data TEXT,
    cv_file_path VARCHAR(500),
    cv_file_name VARCHAR(255),
    cv_file_size INT,
    status ENUM('pending', 'approved', 'rejected', 'withdrawn') DEFAULT 'pending',
    admin_notes TEXT,
    reviewed_by INT,
    reviewed_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL
)";
if (!mysqli_query($conn, $create_table)) {
    error_log("Error creating role_applications table: " . mysqli_error($conn));
}

// Add application_status columns to users table if they don't exist
$alter_table = "
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS application_status ENUM('none', 'pending', 'approved', 'rejected') DEFAULT 'none' AFTER role,
ADD COLUMN IF NOT EXISTS applied_role ENUM('editor', 'reporter') DEFAULT NULL AFTER application_status";
if (!mysqli_query($conn, $alter_table)) {
    error_log("Error adding application_status columns: " . mysqli_error($conn));
}

// Insert application
$insert_query = "INSERT INTO role_applications (user_id, applied_role, application_data, cv_file_path, cv_file_name, cv_file_size, evidence_type, evidence_description, evidence_files) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
$insert_stmt = mysqli_prepare($conn, $insert_query);
$json_data = json_encode($application_data);
$evidence_files_json = json_encode($evidence_files);
mysqli_stmt_bind_param($insert_stmt, 'issssiss', $user_id, $applied_role, $json_data, $cv_file_path, $cv_file_name, $cv_file_size, $evidence_type, $evidence_description, $evidence_files_json);

if (mysqli_stmt_execute($insert_stmt)) {
    // Update user's application status
    $update_user_query = "UPDATE users SET application_status = 'pending', applied_role = ? WHERE id = ?";
    $update_user_stmt = mysqli_prepare($conn, $update_user_query);
    mysqli_stmt_bind_param($update_user_stmt, 'si', $applied_role, $user_id);
    mysqli_stmt_execute($update_user_stmt);
    
    echo json_encode(['success' => true, 'message' => 'Application submitted successfully!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to submit application: ' . mysqli_error($conn)]);
}
?>

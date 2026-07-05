<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helpers.php';

// Session management
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!is_logged_in()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Get current user data
$query = "SELECT * FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    redirect('login.php');
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = clean_input($_POST['name']);
    $email = clean_input($_POST['email']);
    $phone = clean_input($_POST['phone']);
    $bio = clean_input($_POST['bio']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Handle image upload and removal
    $image_path = $user['image'] ?? '';
    
    // Handle current image removal
    if (isset($_POST['remove_current_image']) && $_POST['remove_current_image'] == '1') {
        if (!empty($user['image']) && file_exists($user['image'])) {
            unlink($user['image']);
        }
        $image_path = '';
    }
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        
        if (in_array($file_extension, $allowed_extensions)) {
            if ($_FILES['image']['size'] <= MAX_FILE_SIZE) {
                $file_name = uniqid() . '.' . $file_extension;
                $upload_path = UPLOAD_PATH . 'users/' . $file_name;
                
                if (!file_exists(UPLOAD_PATH . 'users/')) {
                    mkdir(UPLOAD_PATH . 'users/', 0755, true);
                }
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                    if (!empty($user['image']) && file_exists($user['image'])) {
                        unlink($user['image']);
                    }
                    $image_path = $upload_path;
                } else {
                    $error = "Error uploading image file";
                }
            } else {
                $error = "File size too large. Maximum size is " . (MAX_FILE_SIZE / 1024 / 1024) . "MB";
            }
        } else {
            $error = "Invalid file type. Allowed types: " . implode(', ', $allowed_extensions);
        }
    }
    
    // Validate email uniqueness
    if ($email !== $user['email']) {
        $check_query = "SELECT id FROM users WHERE email = ? AND id != ?";
        $check_stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($check_stmt, 'si', $email, $user_id);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);
        
        if (mysqli_num_rows($check_result) > 0) {
            $error = "Email address already exists. Please choose a different email.";
        }
    }
    
    // Handle password change
    if (!empty($current_password) || !empty($new_password) || !empty($confirm_password)) {
        if (empty($current_password)) {
            $error = "Please enter your current password";
        } elseif (!password_verify($current_password, $user['password'])) {
            $error = "Current password is incorrect";
        } elseif (empty($new_password)) {
            $error = "Please enter a new password";
        } elseif (strlen($new_password) < 6) {
            $error = "New password must be at least 6 characters long";
        } elseif ($new_password !== $confirm_password) {
            $error = "New passwords do not match";
        }
    }
    
    if (empty($error)) {
        // Check which columns exist in users table
        $columns_result = mysqli_query($conn, "SHOW COLUMNS FROM users");
        $existing_columns = [];
        while ($column = mysqli_fetch_assoc($columns_result)) {
            $existing_columns[] = $column['Field'];
        }
        
        // Build dynamic update query based on existing columns
        $update_fields = [];
        $update_types = [];
        $update_values = [];
        
        if (in_array('name', $existing_columns)) {
            $update_fields[] = "name = ?";
            $update_types[] = "s";
            $update_values[] = $name;
        }
        
        if (in_array('email', $existing_columns)) {
            $update_fields[] = "email = ?";
            $update_types[] = "s";
            $update_values[] = $email;
        }
        
        if (in_array('phone', $existing_columns)) {
            $update_fields[] = "phone = ?";
            $update_types[] = "s";
            $update_values[] = $phone;
        }
        
        if (in_array('bio', $existing_columns)) {
            $update_fields[] = "bio = ?";
            $update_types[] = "s";
            $update_values[] = $bio;
        }
        
        if (in_array('image', $existing_columns)) {
            $update_fields[] = "image = ?";
            $update_types[] = "s";
            $update_values[] = $image_path;
        }
        
        // Add password to update if provided
        if (!empty($new_password)) {
            $update_fields[] = "password = ?";
            $update_types[] = "s";
            $update_values[] = password_hash($new_password, PASSWORD_DEFAULT);
        }
        
        $update_query = "UPDATE users SET " . implode(', ', $update_fields) . " WHERE id = ?";
        $update_types[] = "i";
        $update_values[] = $user_id;
        
        $update_stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($update_stmt, implode('', $update_types), ...$update_values);
        
        if (mysqli_stmt_execute($update_stmt)) {
            $success = "Profile updated successfully!";
            
            // Update session data
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
            
            // Refresh user data
            $user['name'] = $name;
            $user['email'] = $email;
            $user['phone'] = $phone;
            $user['bio'] = $bio;
            $user['image'] = $image_path;
        } else {
            $error = "Error updating profile: " . mysqli_error($conn);
        }
    }
}

// Get user statistics
$news_count = 0;
$comments_count = 0;
$bookmarks_count = 0;

$news_result = mysqli_query($conn, "SELECT COUNT(*) as count FROM news WHERE author_id = $user_id");
if ($news_result) {
    $news_count = $news_result->fetch_assoc()['count'];
}

$comments_result = mysqli_query($conn, "SELECT COUNT(*) as count FROM comments WHERE user_id = $user_id");
if ($comments_result) {
    $comments_count = $comments_result->fetch_assoc()['count'];
}

$bookmarks_result = mysqli_query($conn, "SELECT COUNT(*) as count FROM bookmarks WHERE user_id = $user_id");
if ($bookmarks_result) {
    $bookmarks_count = $bookmarks_result->fetch_assoc()['count'];
}

// Get user's role applications
$user_applications = [];
$applications_query = "SELECT * FROM role_applications WHERE user_id = ? ORDER BY created_at DESC";
$applications_stmt = mysqli_prepare($conn, $applications_query);
mysqli_stmt_bind_param($applications_stmt, 'i', $user_id);
mysqli_stmt_execute($applications_stmt);
$applications_result = mysqli_stmt_get_result($applications_stmt);
while ($app = mysqli_fetch_assoc($applications_result)) {
    $user_applications[] = $app;
}

// Handle role application submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_role'])) {
    $applied_role = clean_input($_POST['apply_role']);
    
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
    mysqli_query($conn, $create_table);
    
    // Add application_status columns to users table if they don't exist
    $alter_table = "
    ALTER TABLE users 
    ADD COLUMN IF NOT EXISTS application_status ENUM('none', 'pending', 'approved', 'rejected') DEFAULT 'none' AFTER role,
    ADD COLUMN IF NOT EXISTS applied_role ENUM('editor', 'reporter') DEFAULT NULL AFTER application_status";
    mysqli_query($conn, $alter_table);
    
    // Check if user already has pending application
    $existing_query = "SELECT id FROM role_applications WHERE user_id = ? AND status = 'pending'";
    $existing_stmt = mysqli_prepare($conn, $existing_query);
    mysqli_stmt_bind_param($existing_stmt, 'i', $user_id);
    mysqli_stmt_execute($existing_stmt);
    $existing_result = mysqli_stmt_get_result($existing_stmt);
    
    if (mysqli_num_rows($existing_result) > 0) {
        $error = "You already have a pending application. Please wait for it to be reviewed.";
    } else {
        // Handle CV upload
        $cv_file_path = '';
        $cv_file_name = '';
        $cv_file_size = 0;
        
        if (isset($_FILES['cv_file']) && $_FILES['cv_file']['error'] == 0) {
            $allowed_extensions = ['pdf', 'doc', 'docx'];
            $file_extension = strtolower(pathinfo($_FILES['cv_file']['name'], PATHINFO_EXTENSION));
            $max_size = 5 * 1024 * 1024; // 5MB
            
            if (!in_array($file_extension, $allowed_extensions)) {
                $error = "CV file must be PDF, DOC, or DOCX format.";
            } elseif ($_FILES['cv_file']['size'] > $max_size) {
                $error = "CV file size must be less than 5MB.";
            } else {
                // Create CV upload directory if it doesn't exist
                $cv_upload_dirs = [UPLOAD_PATH . 'cv/', UPLOAD_PATH . 'cvs/'];
                foreach ($cv_upload_dirs as $dir) {
                    if (!file_exists($dir)) {
                        mkdir($dir, 0755, true);
                    }
                }
                
                // Generate unique filename
                $unique_filename = uniqid() . '_' . $user_id . '_' . time() . '.' . $file_extension;
                $cv_file_path = UPLOAD_PATH . 'cv/' . $unique_filename;
                $cv_file_name = $_FILES['cv_file']['name'];
                $cv_file_size = $_FILES['cv_file']['size'];
                
                if (!move_uploaded_file($_FILES['cv_file']['tmp_name'], $cv_file_path)) {
                    $error = "Error uploading CV file. Please try again.";
                }
            }
        } else {
            // CV file is required
            $error = "Please upload your CV/Resume file.";
        }
        
        if (empty($error)) {
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
                $error = "Evidence type is required for all applications.";
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
                    $error = ucfirst($field) . ' is required for ' . $applied_role . ' applications.';
                    break;
                }
            }
            
            if (empty($error)) {
                $insert_query = "INSERT INTO role_applications (user_id, applied_role, application_data, cv_file_path, cv_file_name, cv_file_size, evidence_type, evidence_description, evidence_files) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $insert_stmt = mysqli_prepare($conn, $insert_query);
                $json_data = json_encode($application_data);
                $evidence_files_json = json_encode($evidence_files);
                mysqli_stmt_bind_param($insert_stmt, 'issssiss', $user_id, $applied_role, $json_data, $cv_file_path, $cv_file_name, $cv_file_size, $evidence_type, $evidence_description, $evidence_files_json);
                
                if (mysqli_stmt_execute($insert_stmt)) {
                    $success = "Your application for " . ucfirst($applied_role) . " role has been submitted successfully!";
                    
                    // Update user application status
                    $update_user_query = "UPDATE users SET application_status = 'pending', applied_role = ? WHERE id = ?";
                    $update_user_stmt = mysqli_prepare($conn, $update_user_query);
                    mysqli_stmt_bind_param($update_user_stmt, 'si', $applied_role, $user_id);
                    mysqli_stmt_execute($update_user_stmt);
                } else {
                    $error = "Error submitting application: " . mysqli_error($conn);
                    // Clean up uploaded CV if database insert failed
                    if (!empty($cv_file_path) && file_exists($cv_file_path)) {
                        unlink($cv_file_path);
                    }
                }
            }
        }
    }
}
?>
<?php 
$page_title = 'My Profile';
include 'includes/header.php'; 
?>

<style>
    /* Modern Profile Page Styles */
    
    /* Animated Background */
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            position: relative;
        }
        
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%23ffffff" fill-opacity="0.1" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,122.7C672,117,768,139,864,133.3C960,128,1056,96,1152,90.7C1248,85,1344,107,1392,117.3L1440,128L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>') no-repeat bottom;
            background-size: cover;
            z-index: -1;
        }
        
        /* Enhanced Profile Header */
        .profile-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 80px 0 60px;
            margin-bottom: 40px;
            position: relative;
            overflow: hidden;
        }
        
        .profile-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }
        
        /* Advanced Profile Image */
        .profile-image-container {
            position: relative;
            display: inline-block;
            margin-bottom: 20px;
        }
        
        .profile-image {
            width: 180px;
            height: 180px;
            border-radius: 50%;
            border: 6px solid rgba(255,255,255,0.9);
            box-shadow: 0 15px 35px rgba(0,0,0,0.3), 0 5px 15px rgba(0,0,0,0.2);
            object-fit: cover;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            z-index: 2;
        }
        
        .profile-image:hover {
            transform: scale(1.05);
            box-shadow: 0 20px 40px rgba(0,0,0,0.4), 0 8px 20px rgba(0,0,0,0.3);
        }
        
        .profile-image-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(102,126,234,0.8) 0%, rgba(118,75,162,0.8) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
            cursor: pointer;
            z-index: 3;
        }
        
        .profile-image-container:hover .profile-image-overlay {
            opacity: 1;
        }
        
        .profile-image-overlay i {
            font-size: 2rem;
            color: white;
        }
        
        /* Profile Info */
        .profile-info {
            position: relative;
            z-index: 2;
        }
        
        .profile-name {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            text-shadow: 0 2px 10px rgba(0,0,0,0.3);
            animation: fadeInUp 0.8s ease-out;
        }
        
        .profile-email {
            font-size: 1.2rem;
            opacity: 0.9;
            margin-bottom: 15px;
            animation: fadeInUp 0.8s ease-out 0.2s both;
        }
        
        .profile-role {
            display: inline-block;
            padding: 8px 20px;
            background: rgba(255,255,255,0.2);
            border: 2px solid rgba(255,255,255,0.3);
            border-radius: 25px;
            font-weight: 600;
            backdrop-filter: blur(10px);
            animation: fadeInUp 0.8s ease-out 0.4s both;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Enhanced Cards */
        .profile-card {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1), 0 8px 16px rgba(0,0,0,0.1);
            padding: 40px;
            margin-bottom: 30px;
            border: 1px solid rgba(255,255,255,0.2);
            transition: all 0.3s ease;
        }
        
        .profile-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 25px 50px rgba(0,0,0,0.15), 0 12px 24px rgba(0,0,0,0.15);
        }
        
        /* Advanced Statistics Cards */
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 20px;
            padding: 30px;
            text-align: center;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.1);
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            transition: transform 0.6s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 20px 40px rgba(102,126,234,0.3), 0 8px 16px rgba(102,126,234,0.2);
        }
        
        .stat-card:hover::before {
            transform: rotate(180deg);
        }
        
        .stat-number {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 10px;
            position: relative;
            z-index: 2;
        }
        
        .stat-label {
            font-size: 1rem;
            font-weight: 600;
            opacity: 0.9;
            position: relative;
            z-index: 2;
        }
        
        /* Enhanced Form Elements */
        .form-label {
            font-weight: 700;
            color: #495057;
            margin-bottom: 10px;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .form-control, .form-select {
            border-radius: 15px;
            border: 2px solid #e9ecef;
            padding: 15px 20px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: rgba(255,255,255,0.8);
            backdrop-filter: blur(10px);
            font-size: 1rem;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102,126,234,0.15), 0 8px 25px rgba(102,126,234,0.1);
            background: rgba(255,255,255,0.95);
            transform: translateY(-2px);
        }
        
        /* Advanced Buttons */
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 15px;
            padding: 15px 35px;
            font-weight: 700;
            font-size: 1rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.6s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(102,126,234,0.4), 0 5px 15px rgba(102,126,234,0.3);
        }
        
        .btn-primary:hover::before {
            left: 100%;
        }
        
        /* Criteria Section */
        .criteria-section {
            background: rgba(248,249,250,0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 35px;
            margin-top: 40px;
            border: 1px solid rgba(255,255,255,0.2);
        }
        
        .criteria-item {
            background: rgba(255,255,255,0.9);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 5px solid #667eea;
            transition: all 0.3s ease;
        }
        
        .criteria-item:hover {
            transform: translateX(5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        
        /* Enhanced Image Preview */
        .image-preview {
            max-width: 250px;
            max-height: 250px;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            transition: all 0.3s ease;
        }
        
        .image-preview:hover {
            transform: scale(1.05);
            box-shadow: 0 12px 35px rgba(0,0,0,0.2);
        }
        
        /* Advanced Password Strength */
        .password-strength {
            height: 6px;
            border-radius: 3px;
            margin-top: 10px;
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
        }
        
        .password-strength::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            animation: shimmer 2s infinite;
        }
        
        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }
        
        .strength-weak { 
            background: linear-gradient(90deg, #dc3545, #e74c3c); 
            width: 33%; 
        }
        .strength-medium { 
            background: linear-gradient(90deg, #ffc107, #f39c12); 
            width: 66%; 
        }
        .strength-strong { 
            background: linear-gradient(90deg, #28a745, #27ae60); 
            width: 100%; 
        }
        
        /* Upload Area */
        .upload-area {
            border: 3px dashed #667eea;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            background: rgba(102,126,234,0.05);
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .upload-area:hover {
            border-color: #764ba2;
            background: rgba(102,126,234,0.1);
            transform: translateY(-2px);
        }
        
        .upload-area.dragover {
            border-color: #28a745;
            background: rgba(40,167,69,0.1);
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .profile-header {
                padding: 60px 0 40px;
            }
            
            .profile-name {
                font-size: 2rem;
            }
            
            .profile-image {
                width: 140px;
                height: 140px;
            }
            
            .stat-number {
                font-size: 2rem;
            }
            
            .profile-card {
                padding: 25px;
            }
        }
        
        @media (max-width: 576px) {
            .profile-header {
                padding: 40px 0 30px;
            }
            
            .profile-name {
                font-size: 1.5rem;
            }
            
            .profile-image {
                width: 120px;
                height: 120px;
            }
        }
    </style>

<div class="container">
        <!-- Enhanced Profile Header -->
        <div class="profile-header text-center">
            <div class="container">
                <div class="profile-image-container">
                    <?php if (!empty($user['image'])): ?>
                        <img src="<?php echo htmlspecialchars($user['image']); ?>" 
                             class="profile-image" alt="Profile Picture">
                        <div class="profile-image-overlay" onclick="document.getElementById('image').click()">
                            <i class="fas fa-camera"></i>
                        </div>
                    <?php else: ?>
                        <div class="profile-image bg-white d-flex align-items-center justify-content-center">
                            <i class="fas fa-user fa-4x text-muted"></i>
                        </div>
                        <div class="profile-image-overlay" onclick="document.getElementById('image').click()">
                            <i class="fas fa-camera"></i>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="profile-info">
                    <h1 class="profile-name"><?php echo htmlspecialchars($user['name']); ?></h1>
                    <p class="profile-email"><?php echo htmlspecialchars($user['email']); ?></p>
                    <span class="profile-role"><?php echo ucfirst($user['role']); ?></span>
                </div>
            </div>
        </div>

        <!-- Alerts -->
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <!-- Application Status Notifications -->
        <div id="applicationNotifications" class="mb-4"></div>

        <!-- Platform Family Welcome Section -->
        <div class="profile-card mb-4">
            <div class="text-center">
                <div class="mb-4">
                    <i class="fas fa-home fa-3x text-primary mb-3"></i>
                    <h3 class="mb-3">Welcome to Your PK Live News Family!</h3>
                    <p class="lead text-muted mb-4">
                        You're not just a user - you're a valued member of our growing community of news enthusiasts, 
                        storytellers, and truth-seekers. Every contribution you make helps shape the narrative of our times.
                    </p>
                </div>
                
                <!-- Community Impact Message -->
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="text-center p-3">
                            <i class="fas fa-users fa-2x text-success mb-2"></i>
                            <h5 class="text-success">Community Member</h5>
                            <p class="small text-muted">Part of a thriving network of readers and contributors</p>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="text-center p-3">
                            <i class="fas fa-microphone fa-2x text-info mb-2"></i>
                            <h5 class="text-info">Voice Matters</h5>
                            <p class="small text-muted">Your comments and engagement drive meaningful discussions</p>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="text-center p-3">
                            <i class="fas fa-star fa-2x text-warning mb-2"></i>
                            <h5 class="text-warning">Growing Together</h5>
                            <p class="small text-muted">Every bookmark and share helps our community grow</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Statistics Cards with Impact Messaging -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="stat-card">
                    <div class="stat-number"><?php echo number_format($news_count); ?></div>
                    <div class="stat-label">Articles Published</div>
                    <div class="mt-2">
                        <?php if ($news_count > 0): ?>
                            <small class="text-white-50">
                                <i class="fas fa-trophy me-1"></i>
                                <?php echo $news_count == 1 ? 'Storyteller' : ($news_count < 5 ? 'Rising Writer' : ($news_count < 10 ? 'Established Author' : 'Master Contributor')); ?>
                            </small>
                        <?php else: ?>
                            <small class="text-white-50">
                                <i class="fas fa-pen me-1"></i>
                                Ready to share your story?
                            </small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stat-card">
                    <div class="stat-number"><?php echo number_format($comments_count); ?></div>
                    <div class="stat-label">Comments Made</div>
                    <div class="mt-2">
                        <?php if ($comments_count > 0): ?>
                            <small class="text-white-50">
                                <i class="fas fa-comments me-1"></i>
                                <?php echo $comments_count == 1 ? 'First Voice' : ($comments_count < 10 ? 'Active Discussant' : ($comments_count < 25 ? 'Community Leader' : 'Conversation Master')); ?>
                            </small>
                        <?php else: ?>
                            <small class="text-white-50">
                                <i class="fas fa-comment-dots me-1"></i>
                                Join the conversation!
                            </small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stat-card">
                    <div class="stat-number"><?php echo number_format($bookmarks_count); ?></div>
                    <div class="stat-label">Bookmarks Saved</div>
                    <div class="mt-2">
                        <?php if ($bookmarks_count > 0): ?>
                            <small class="text-white-50">
                                <i class="fas fa-bookmark me-1"></i>
                                <?php echo $bookmarks_count == 1 ? 'Curator' : ($bookmarks_count < 10 ? 'Content Collector' : 'Archive Master'); ?>
                            </small>
                        <?php else: ?>
                            <small class="text-white-50">
                                <i class="fas fa-bookmark me-1"></i>
                                Start curating!
                            </small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stat-card">
                    <div class="stat-number"><?php echo date('Y', strtotime($user['created_at'])); ?></div>
                    <div class="stat-label">Member Since</div>
                    <div class="mt-2">
                        <small class="text-white-50">
                            <i class="fas fa-calendar-heart me-1"></i>
                            <?php 
                            $years = date('Y') - date('Y', strtotime($user['created_at']));
                            echo $years == 0 ? 'New Member' : ($years == 1 ? '1 Year Strong' : $years . ' Years Loyal'); 
                            ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Contribution Impact Section -->
        <div class="profile-card mb-4">
            <h4 class="mb-4">
                <i class="fas fa-chart-line me-2"></i>Your Impact on Our Community
            </h4>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-4">
                        <h6 class="text-primary mb-3">
                            <i class="fas fa-newspaper me-2"></i>Articles Contribution
                        </h6>
                        <?php if ($news_count > 0): ?>
                            <p class="text-success">
                                <i class="fas fa-check-circle me-2"></i>
                                You've shared <strong><?php echo $news_count; ?></strong> stories with our community!
                            </p>
                            <p class="text-muted small">
                                Your articles help inform and educate thousands of readers across the region. 
                                Each story you publish contributes to a more informed society.
                            </p>
                            <?php if ($news_count < 3): ?>
                                <div class="alert alert-info mt-3">
                                    <i class="fas fa-lightbulb me-2"></i>
                                    <strong>Keep going!</strong> Share more stories to become a recognized contributor in our community.
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <p class="text-muted">
                                <i class="fas fa-pen-fancy me-2"></i>
                                Ready to share your voice with our community?
                            </p>
                            <p class="text-muted small">
                                Apply to become a <strong>Reporter</strong> and start publishing stories that matter to our readers. 
                                Your perspective is valuable and deserves to be heard!
                            </p>
                            <button class="btn btn-primary btn-sm mt-2" onclick="showApplicationForm('reporter')">
                                <i class="fas fa-edit me-2"></i>Start Writing
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-4">
                        <h6 class="text-info mb-3">
                            <i class="fas fa-comments me-2"></i>Community Engagement
                        </h6>
                        <?php if ($comments_count > 0): ?>
                            <p class="text-success">
                                <i class="fas fa-check-circle me-2"></i>
                                You've made <strong><?php echo $comments_count; ?></strong> thoughtful contributions!
                            </p>
                            <p class="text-muted small">
                                Your comments spark meaningful discussions and help create a vibrant, 
                                engaged community where diverse perspectives are valued.
                            </p>
                            <?php if ($comments_count < 10): ?>
                                <div class="alert alert-info mt-3">
                                    <i class="fas fa-star me-2"></i>
                                    <strong>Great start!</strong> Keep engaging with articles to build your reputation as a thoughtful reader.
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <p class="text-muted">
                                <i class="fas fa-comment-dots me-2"></i>
                                Your voice matters in our community discussions!
                            </p>
                            <p class="text-muted small">
                                Join the conversation by sharing your thoughts on articles. 
                                Your insights help create a richer, more diverse dialogue.
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-12">
                    <div class="alert alert-success">
                        <h6 class="alert-heading">
                            <i class="fas fa-heart me-2"></i>You're Part of Something Bigger
                        </h6>
                        <p class="mb-2">
                            Every article you read, comment you make, and story you share contributes to the 
                            <strong>PK Live News ecosystem</strong> - a platform built on truth, community, and progress.
                        </p>
                        <hr>
                        <p class="mb-0">
                            <small class="text-muted">
                                Together, we're not just consuming news - we're shaping the future of journalism in our region. 
                                Thank you for being an essential part of our family.
                            </small>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profile Form -->
        <div class="profile-card">
            <h3 class="mb-4">
                <i class="fas fa-user-edit me-2"></i>Edit Profile
            </h3>
            
            <form method="POST" enctype="multipart/form-data" id="profileForm">
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="mb-4">
                            <i class="fas fa-user me-2"></i>Personal Information
                        </h5>
                        
                        <!-- Name -->
                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name *</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?php echo htmlspecialchars($user['name']); ?>" 
                                   placeholder="Enter your full name" 
                                   required maxlength="100">
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address *</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>" 
                                   placeholder="Enter your email" 
                                   required maxlength="255">
                        </div>

                        <!-- Phone -->
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" 
                                   placeholder="Enter your phone number" 
                                   maxlength="20">
                        </div>

                        <!-- Bio -->
                        <div class="mb-3">
                            <label for="bio" class="form-label">Bio</label>
                            <textarea class="form-control" id="bio" name="bio" rows="4" 
                                      placeholder="Tell us about yourself"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                            <small class="text-muted">Brief description about yourself</small>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <h5 class="mb-4">
                            <i class="fas fa-camera me-2"></i>Profile Picture
                        </h5>
                        
                        <!-- Advanced Image Upload -->
                        <div class="mb-3">
                            <label for="image" class="form-label">Upload New Picture</label>
                            <div class="upload-area" id="uploadArea">
                                <input type="file" class="form-control d-none" id="image" name="image" accept="image/*">
                                <div class="upload-content">
                                    <i class="fas fa-cloud-upload-alt fa-3x mb-3 text-primary"></i>
                                    <h5>Drag & Drop your image here</h5>
                                    <p class="text-muted mb-2">or click to browse</p>
                                    <small class="text-muted">Supported formats: JPG, PNG, GIF, WebP (Max 5MB)</small>
                                </div>
                            </div>
                            
                            <!-- Image Preview Container -->
                            <div id="imagePreviewContainer" class="mt-3" style="display: none;">
                                <h6>Preview:</h6>
                                <div class="position-relative d-inline-block">
                                    <img id="imagePreview" class="image-preview" alt="Profile picture preview">
                                    <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2" onclick="removeImage()">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Current Image Preview -->
                            <?php if (!empty($user['image'])): ?>
                                <div class="mt-3">
                                    <h6>Current Picture:</h6>
                                    <div class="position-relative d-inline-block">
                                        <img src="<?php echo htmlspecialchars($user['image']); ?>" 
                                             class="image-preview" alt="Current profile picture">
                                        <button type="button" class="btn btn-outline-danger btn-sm position-absolute top-0 end-0 m-2" onclick="removeCurrentImage()">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <h5 class="mb-4 mt-4">
                            <i class="fas fa-lock me-2"></i>Change Password
                        </h5>
                        
                        <!-- Current Password -->
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input type="password" class="form-control" id="current_password" name="current_password" 
                                   placeholder="Enter current password to change">
                            <small class="text-muted">Leave empty if you don't want to change password</small>
                        </div>

                        <!-- New Password -->
                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" 
                                   placeholder="Enter new password">
                            <div id="passwordStrength" class="password-strength"></div>
                            <small class="text-muted">Minimum 6 characters</small>
                        </div>

                        <!-- Confirm Password -->
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                   placeholder="Confirm new password">
                        </div>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Update Profile
                    </button>
                    <button type="button" class="btn btn-outline-secondary" onclick="resetForm()">
                        <i class="fas fa-times me-2"></i>Reset
                    </button>
                </div>
            </form>
        </div>

        <!-- Profile Criteria Section -->
        <div class="criteria-section">
            <h4 class="mb-4">
                <i class="fas fa-list-check me-2"></i>Profile Criteria & Settings
            </h4>
            
            <div class="criteria-item">
                <h6><i class="fas fa-user-check me-2"></i>Account Verification</h6>
                <p class="mb-2">Status: <?php echo ($user['email_verified'] ?? false) ? '<span class="badge bg-success">Verified</span>' : '<span class="badge bg-warning">Not Verified</span>'; ?></p>
                <?php if (!($user['email_verified'] ?? false)): ?>
                    <button class="btn btn-sm btn-outline-primary" onclick="sendVerification()">
                        <i class="fas fa-envelope me-1"></i>Send Verification Email
                    </button>
                <?php endif; ?>
            </div>

            <div class="criteria-item">
                <h6><i class="fas fa-shield-alt me-2"></i>Security Settings</h6>
                <p class="mb-2">Two-Factor Authentication: <?php echo ($user['two_factor_enabled'] ?? false) ? '<span class="badge bg-success">Enabled</span>' : '<span class="badge bg-secondary">Disabled</span>'; ?></p>
                <button class="btn btn-sm btn-outline-info" onclick="toggle2FA()">
                    <i class="fas fa-key me-1"></i><?php echo ($user['two_factor_enabled'] ?? false) ? 'Disable' : 'Enable'; ?> 2FA
                </button>
            </div>

            <div class="criteria-item">
                <h6><i class="fas fa-bell me-2"></i>Notification Preferences</h6>
                <div class="form-check form-switch mb-2">
                    <input class="form-check-input" type="checkbox" id="email_notifications" 
                           <?php echo ($user['email_notifications'] ?? true) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="email_notifications">
                        Email Notifications
                    </label>
                </div>
                <div class="form-check form-switch mb-2">
                    <input class="form-check-input" type="checkbox" id="push_notifications" 
                           <?php echo ($user['push_notifications'] ?? false) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="push_notifications">
                        Push Notifications
                    </label>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="newsletter_subscription" 
                           <?php echo ($user['newsletter_subscription'] ?? true) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="newsletter_subscription">
                        Newsletter Subscription
                    </label>
                </div>
            </div>

            <div class="criteria-item">
                <h6><i class="fas fa-eye me-2"></i>Privacy Settings</h6>
                <div class="form-check form-switch mb-2">
                    <input class="form-check-input" type="checkbox" id="profile_public" 
                           <?php echo ($user['profile_public'] ?? false) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="profile_public">
                        Public Profile
                    </label>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="show_activity" 
                           <?php echo ($user['show_activity'] ?? true) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="show_activity">
                        Show Activity Status
                    </label>
                </div>
            </div>

            <div class="criteria-item">
                <h6><i class="fas fa-globe me-2"></i>Content Preferences</h6>
                <div class="mb-2">
                    <label class="form-label">Preferred Categories</label>
                    <select class="form-select" id="preferred_categories" multiple>
                        <?php
                        $cat_query = "SELECT * FROM categories WHERE status = 'active' ORDER BY name ASC";
                        $cat_result = mysqli_query($conn, $cat_query);
                        while ($category = mysqli_fetch_assoc($cat_result)) {
                            $selected = in_array($category['id'], explode(',', $user['preferred_categories'] ?? '')) ? 'selected' : '';
                            echo '<option value="' . $category['id'] . '" ' . $selected . '>' . htmlspecialchars($category['name']) . '</option>';
                        }
                        ?>
                    </select>
                    <small class="text-muted">Select your preferred news categories</small>
                </div>
                <div class="mb-2">
                    <label class="form-label">Language Preference</label>
                    <select class="form-select" id="language_preference">
                        <option value="en" <?php echo ($user['language_preference'] ?? 'en') == 'en' ? 'selected' : ''; ?>>English</option>
                        <option value="ur" <?php echo ($user['language_preference'] ?? 'en') == 'ur' ? 'selected' : ''; ?>>اردو</option>
                        <option value="hi" <?php echo ($user['language_preference'] ?? 'en') == 'hi' ? 'selected' : ''; ?>>हिन्दी</option>
                        <option value="ps" <?php echo ($user['language_preference'] ?? 'en') == 'ps' ? 'selected' : ''; ?>>پښتو</option>
                        <option value="zh" <?php echo ($user['language_preference'] ?? 'en') == 'zh' ? 'selected' : ''; ?>>中文</option>
                    </select>
                </div>
            </div>

            <div class="d-flex gap-2 mt-3">
                <button class="btn btn-primary" onclick="saveCriteria()">
                    <i class="fas fa-save me-2"></i>Save Criteria Settings
                </button>
                <button class="btn btn-outline-secondary" onclick="resetCriteria()">
                    <i class="fas fa-undo me-2"></i>Reset to Default
                </button>
            </div>
        </div>
        
        <!-- Role Application Section -->
        <div class="profile-card">
            <h3 class="mb-4">
                <i class="fas fa-user-tie me-2"></i>Role Applications
            </h3>
            
            <!-- Current Applications Status -->
            <?php if (!empty($user_applications)): ?>
                <div class="mb-4">
                    <h5 class="mb-3">Your Applications</h5>
                    <?php foreach ($user_applications as $app): ?>
                        <div class="alert alert-<?php echo $app['status'] === 'approved' ? 'success' : ($app['status'] === 'rejected' ? 'danger' : 'info'); ?> d-flex align-items-center">
                            <div class="flex-grow-1">
                                <strong>Application for <?php echo ucfirst($app['applied_role']); ?> Role</strong>
                                <br>
                                <small>
                                    Status: <span class="badge bg-<?php echo $app['status'] === 'approved' ? 'success' : ($app['status'] === 'rejected' ? 'danger' : 'warning'); ?>">
                                        <?php echo ucfirst($app['status']); ?>
                                    </span>
                                    | Applied: <?php echo date('M d, Y', strtotime($app['created_at'])); ?>
                                    <?php if ($app['reviewed_at']): ?>
                                        | Reviewed: <?php echo date('M d, Y', strtotime($app['reviewed_at'])); ?>
                                    <?php endif; ?>
                                </small>
                                <?php if ($app['admin_notes']): ?>
                                    <br><small><strong>Admin Notes:</strong> <?php echo htmlspecialchars($app['admin_notes']); ?></small>
                                <?php endif; ?>
                            </div>
                            <?php if ($app['status'] === 'pending'): ?>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="withdrawApplication(<?php echo $app['id']; ?>)">
                                    <i class="fas fa-times me-1"></i>Withdraw
                                </button>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <!-- Apply for New Role -->
            <?php if ($user['role'] === 'user'): ?>
                <?php 
                $has_pending = false;
                foreach ($user_applications as $app) {
                    if ($app['status'] === 'pending') {
                        $has_pending = true;
                        break;
                    }
                }
                ?>
                
                <?php if (!$has_pending): ?>
                    <div class="text-center mb-4">
                        <h5>Apply for Enhanced Role</h5>
                        <p class="text-muted">Apply to become a Reporter or Editor and contribute more to our news platform</p>
                    </div>
                    
                    <!-- Application Evaluation Criteria -->
                    <div class="profile-card mb-4">
                        <h4 class="mb-4">
                            <i class="fas fa-clipboard-check me-2"></i>Application Evaluation Criteria
                        </h4>
                        <p class="text-muted mb-4">
                            Review our evaluation criteria below to understand the requirements for each role. 
                            Applications are assessed based on experience, qualifications, and commitment to quality journalism.
                        </p>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="card border-primary h-100">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="mb-0">
                                            <i class="fas fa-newspaper me-2"></i>Reporter Requirements
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <ul class="list-unstyled mb-0">
                                            <li class="mb-3">
                                                <i class="fas fa-check-circle text-success me-2"></i>
                                                <strong>Minimum 6 months writing experience</strong>
                                                <br><small class="text-muted">Demonstrated experience in news writing, blogging, or content creation</small>
                                            </li>
                                            <li class="mb-3">
                                                <i class="fas fa-check-circle text-success me-2"></i>
                                                <strong>Journalism qualifications or equivalent</strong>
                                                <br><small class="text-muted">Degree in journalism, communications, or relevant field experience</small>
                                            </li>
                                            <li class="mb-3">
                                                <i class="fas fa-check-circle text-success me-2"></i>
                                                <strong>Published writing samples</strong>
                                                <br><small class="text-muted">Portfolio of previously published articles or writing samples</small>
                                            </li>
                                            <li class="mb-0">
                                                <i class="fas fa-check-circle text-success me-2"></i>
                                                <strong>Regular availability commitment</strong>
                                                <br><small class="text-muted">Consistent schedule for article submission and deadline adherence</small>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="card border-success h-100">
                                    <div class="card-header bg-success text-white">
                                        <h5 class="mb-0">
                                            <i class="fas fa-user-edit me-2"></i>Editor Requirements
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <ul class="list-unstyled mb-0">
                                            <li class="mb-3">
                                                <i class="fas fa-check-circle text-success me-2"></i>
                                                <strong>Minimum 2 years editorial experience</strong>
                                                <br><small class="text-muted">Proven track record in editing, content management, or publishing</small>
                                            </li>
                                            <li class="mb-3">
                                                <i class="fas fa-check-circle text-success me-2"></i>
                                                <strong>Excellent language skills</strong>
                                                <br><small class="text-muted">Mastery of grammar, syntax, and style guidelines</small>
                                            </li>
                                            <li class="mb-3">
                                                <i class="fas fa-check-circle text-success me-2"></i>
                                                <strong>Leadership and content management</strong>
                                                <br><small class="text-muted">Ability to guide writers and manage editorial workflows</small>
                                            </li>
                                            <li class="mb-0">
                                                <i class="fas fa-check-circle text-success me-2"></i>
                                                <strong>Quality assurance expertise</strong>
                                                <br><small class="text-muted">Strong fact-checking and content verification skills</small>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-info mt-4">
                            <h6 class="alert-heading">
                                <i class="fas fa-info-circle me-2"></i>Evaluation Process
                            </h6>
                            <p class="mb-2">
                                All applications undergo a comprehensive review process that includes:
                            </p>
                            <ul class="mb-0">
                                <li>Verification of submitted documents and qualifications</li>
                                <li>Review of writing samples and portfolio quality</li>
                                <li>Assessment of experience and references</li>
                                <li>Evaluation of availability and commitment level</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="card h-100 border-primary">
                                <div class="card-body text-center">
                                    <i class="fas fa-newspaper fa-3x text-primary mb-3"></i>
                                    <h5 class="card-title">Become a Reporter</h5>
                                    <p class="card-text">Write and publish news articles, reports, and stories</p>
                                    <button class="btn btn-primary" onclick="showApplicationForm('reporter')">
                                        <i class="fas fa-edit me-2"></i>Apply Now
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card h-100 border-success">
                                <div class="card-body text-center">
                                    <i class="fas fa-user-edit fa-3x text-success mb-3"></i>
                                    <h5 class="card-title">Become an Editor</h5>
                                    <p class="card-text">Review, edit, and manage content from reporters</p>
                                    <button class="btn btn-success" onclick="showApplicationForm('editor')">
                                        <i class="fas fa-check me-2"></i>Apply Now
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        You have a pending application. Please wait for the admin review.
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>
                    You currently have the <strong><?php echo ucfirst($user['role']); ?></strong> role.
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Application Form Modal -->
        <div class="modal fade" id="applicationModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-user-tie me-2"></i>
                            <span id="modalTitle">Role Application</span>
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST" action="api/submit_application.php" id="applicationForm" enctype="multipart/form-data">
                        <div class="modal-body">
                            <input type="hidden" name="apply_role" id="appliedRole">
                            
                            <div id="reporterForm" style="display: none;">
                                <h6 class="mb-3"><i class="fas fa-newspaper me-2"></i>Reporter Application Criteria</h6>
                                
                                <div class="mb-3">
                                    <label class="form-label">Writing Experience *</label>
                                    <textarea class="form-control" name="experience" rows="3"
                                              placeholder="Describe your writing experience..."></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Journalism Qualifications *</label>
                                    <textarea class="form-control" name="qualifications" rows="2"
                                              placeholder="Any journalism degrees, certificates, or training..."></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Why do you want to be a reporter? *</label>
                                    <textarea class="form-control" name="reason" rows="3"
                                              placeholder="Tell us why you're interested in reporting..."></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Sample Work/Portfolio</label>
                                    <textarea class="form-control" name="samples" rows="3"
                                              placeholder="Links to your published articles or writing samples..."></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Availability *</label>
                                    <select class="form-select" name="availability" required>
                                        <option value="">Select availability</option>
                                        <option value="full-time">Full Time (40+ hours/week)</option>
                                        <option value="part-time">Part Time (20-39 hours/week)</option>
                                        <option value="freelance">Freelance/Project Basis</option>
                                        <option value="weekends">Weekends Only</option>
                                    </select>
                                </div>
                                
                                <!-- Evidence Type Section -->
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-folder-open me-2"></i>Type of Evidence *
                                    </label>
                                    <select class="form-select" name="evidence_type" id="evidence_type">
                                        <option value="">Select evidence type</option>
                                        <option value="cv_resume">CV/Resume</option>
                                        <option value="portfolio">Portfolio</option>
                                        <option value="certificates">Certificates</option>
                                        <option value="work_samples">Work Samples</option>
                                        <option value="references">References</option>
                                        <option value="publications">Publications</option>
                                        <option value="other">Other</option>
                                    </select>
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Select the primary type of evidence you're providing with this application.
                                    </small>
                                </div>

                                <!-- Evidence Description -->
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-align-left me-2"></i>Evidence Description
                                    </label>
                                    <textarea class="form-control" name="evidence_description" id="evidence_description" rows="3"
                                              placeholder="Describe the evidence you're providing..."></textarea>
                                    <small class="text-muted">
                                        Provide details about your evidence, including any relevant context or highlights.
                                    </small>
                                </div>

                                <!-- CV Upload Section -->
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-file-upload me-2"></i>Upload Supporting Document *
                                        <small class="text-muted">(PDF, DOC, DOCX - Max 5MB)</small>
                                    </label>
                                    <input type="file" class="form-control" name="cv_file" id="cv_file" 
                                           accept=".pdf,.doc,.docx">
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Upload your primary evidence document (CV, portfolio, certificates, etc.).
                                        This file will be used to evaluate your application.
                                    </small>
                                    
                                    <!-- CV Preview -->
                                    <div id="cvPreview" class="mt-2" style="display: none;">
                                        <div class="alert alert-info">
                                            <i class="fas fa-file-alt me-2"></i>
                                            <span id="cvFileName"></span>
                                            <span id="cvFileSize" class="text-muted ms-2"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div id="editorForm" style="display: none;">
                                <h6 class="mb-3"><i class="fas fa-user-edit me-2"></i>Editor Application Criteria</h6>
                                
                                <div class="mb-3">
                                    <label class="form-label">Editorial Experience *</label>
                                    <textarea class="form-control" name="experience" rows="3"
                                              placeholder="Describe your editorial experience..."></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Editorial Qualifications *</label>
                                    <textarea class="form-control" name="qualifications" rows="2"
                                              placeholder="Editorial degrees, certificates, or training..."></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Why do you want to be an editor? *</label>
                                    <textarea class="form-control" name="reason" rows="3"
                                              placeholder="Tell us why you're interested in editing..."></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Editorial Skills</label>
                                    <textarea class="form-control" name="samples" rows="3"
                                              placeholder="Copy editing, content management, fact-checking skills..."></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Availability *</label>
                                    <select class="form-select" name="availability" required>
                                        <option value="">Select availability</option>
                                        <option value="full-time">Full Time (40+ hours/week)</option>
                                        <option value="part-time">Part Time (20-39 hours/week)</option>
                                        <option value="freelance">Freelance/Project Basis</option>
                                    </select>
                                </div>
                                
                                <!-- Evidence Type Section -->
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-folder-open me-2"></i>Type of Evidence *
                                    </label>
                                    <select class="form-select" name="evidence_type" id="evidence_type_editor">
                                        <option value="">Select evidence type</option>
                                        <option value="cv_resume">CV/Resume</option>
                                        <option value="portfolio">Portfolio</option>
                                        <option value="certificates">Certificates</option>
                                        <option value="work_samples">Work Samples</option>
                                        <option value="references">References</option>
                                        <option value="publications">Publications</option>
                                        <option value="other">Other</option>
                                    </select>
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Select the primary type of evidence you're providing with this application.
                                    </small>
                                </div>

                                <!-- Evidence Description -->
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-align-left me-2"></i>Evidence Description
                                    </label>
                                    <textarea class="form-control" name="evidence_description" id="evidence_description_editor" rows="3"
                                              placeholder="Describe the evidence you're providing..."></textarea>
                                    <small class="text-muted">
                                        Provide details about your evidence, including any relevant context or highlights.
                                    </small>
                                </div>

                                <!-- CV Upload Section -->
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-file-upload me-2"></i>Upload Supporting Document *
                                        <small class="text-muted">(PDF, DOC, DOCX - Max 5MB)</small>
                                    </label>
                                    <input type="file" class="form-control" name="cv_file" id="cv_file_editor" 
                                           accept=".pdf,.doc,.docx">
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Upload your primary evidence document (CV, portfolio, certificates, etc.).
                                        This file will be used to evaluate your editorial application.
                                    </small>
                                    
                                    <!-- CV Preview -->
                                    <div id="cvPreviewEditor" class="mt-2" style="display: none;">
                                        <div class="alert alert-info">
                                            <i class="fas fa-file-alt me-2"></i>
                                            <span id="cvFileNameEditor"></span>
                                            <span id="cvFileSizeEditor" class="text-muted ms-2"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-2"></i>Submit Application
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Advanced Drag and Drop Upload
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('image');
        const imagePreview = document.getElementById('imagePreview');
        const imagePreviewContainer = document.getElementById('imagePreviewContainer');
        
        // Click to upload
        uploadArea.addEventListener('click', () => {
            fileInput.click();
        });
        
        // Drag and drop events
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });
        
        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('dragover');
        });
        
        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                handleFileSelect(files[0]);
            }
        });
        
        // File input change
        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                handleFileSelect(e.target.files[0]);
            }
        });
        
        // Handle file selection
        function handleFileSelect(file) {
            // Validate file type
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            if (!allowedTypes.includes(file.type)) {
                alert('Please select a valid image file (JPG, PNG, GIF, or WebP)');
                return;
            }
            
            // Validate file size (5MB)
            if (file.size > 5 * 1024 * 1024) {
                alert('File size must be less than 5MB');
                return;
            }
            
            // Show preview
            const reader = new FileReader();
            reader.onload = (e) => {
                imagePreview.src = e.target.result;
                imagePreviewContainer.style.display = 'block';
                uploadArea.style.display = 'none';
            };
            reader.readAsDataURL(file);
        }
        
        // Remove image
        function removeImage() {
            fileInput.value = '';
            imagePreviewContainer.style.display = 'none';
            uploadArea.style.display = 'block';
        }
        
        // Remove current image
        function removeCurrentImage() {
            if (confirm('Are you sure you want to remove your current profile picture?')) {
                // Create hidden input to indicate image removal
                const removeInput = document.createElement('input');
                removeInput.type = 'hidden';
                removeInput.name = 'remove_current_image';
                removeInput.value = '1';
                document.getElementById('profileForm').appendChild(removeInput);
                
                // Hide current image preview
                const currentImageContainer = event.target.closest('.mt-3');
                currentImageContainer.style.display = 'none';
            }
        }
        
        // Enhanced Password strength checker
        document.getElementById('new_password').addEventListener('input', function() {
            const password = this.value;
            const strengthBar = document.getElementById('passwordStrength');
            
            let strength = 0;
            if (password.length >= 8) strength++;
            if (password.length >= 12) strength++;
            if (password.match(/[a-z]+/)) strength++;
            if (password.match(/[A-Z]+/)) strength++;
            if (password.match(/[0-9]+/)) strength++;
            if (password.match(/[^a-zA-Z0-9]+/)) strength++;
            
            strengthBar.className = 'password-strength';
            if (strength <= 2) {
                strengthBar.classList.add('strength-weak');
            } else if (strength <= 4) {
                strengthBar.classList.add('strength-medium');
            } else {
                strengthBar.classList.add('strength-strong');
            }
        });

        // Enhanced Form validation
        document.getElementById('profileForm').addEventListener('submit', function(e) {
            const name = document.getElementById('name').value.trim();
            const email = document.getElementById('email').value.trim();
            const currentPassword = document.getElementById('current_password').value;
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            // Clear previous errors
            clearErrors();
            
            let hasError = false;
            
            // Validate name
            if (!name) {
                showError('name', 'Please enter your full name');
                hasError = true;
            } else if (name.length < 2) {
                showError('name', 'Name must be at least 2 characters long');
                hasError = true;
            }
            
            // Validate email
            if (!email) {
                showError('email', 'Please enter your email address');
                hasError = true;
            } else if (!isValidEmail(email)) {
                showError('email', 'Please enter a valid email address');
                hasError = true;
            }
            
            // Validate password fields only if current password is entered
            if (currentPassword || newPassword || confirmPassword) {
                if (!currentPassword) {
                    showError('current_password', 'Please enter your current password');
                    hasError = true;
                }
                
                if (newPassword !== confirmPassword) {
                    showError('confirm_password', 'New passwords do not match');
                    hasError = true;
                }
                
                if (newPassword.length < 8) {
                    showError('new_password', 'New password must be at least 8 characters long');
                    hasError = true;
                }
            }
            
            if (hasError) {
                e.preventDefault();
                // Scroll to first error
                const firstError = document.querySelector('.is-invalid');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstError.focus();
                }
                return false;
            }
            
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Updating...';
            submitBtn.disabled = true;
            
            // Reset button after 3 seconds (in case of network issues)
            setTimeout(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 3000);
            
            return true;
        });
        
        // Helper functions
        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }
        
        function showError(fieldId, message) {
            const field = document.getElementById(fieldId);
            field.classList.add('is-invalid');
            
            // Remove existing error message
            const existingError = field.parentNode.querySelector('.invalid-feedback');
            if (existingError) {
                existingError.remove();
            }
            
            // Add error message
            const errorDiv = document.createElement('div');
            errorDiv.className = 'invalid-feedback';
            errorDiv.textContent = message;
            field.parentNode.appendChild(errorDiv);
            
            // Add shake animation
            field.style.animation = 'shake 0.5s';
            setTimeout(() => {
                field.style.animation = '';
            }, 500);
        }
        
        function clearErrors() {
            document.querySelectorAll('.is-invalid').forEach(field => {
                field.classList.remove('is-invalid');
            });
            document.querySelectorAll('.invalid-feedback').forEach(error => {
                error.remove();
            });
        }
        
        // Add shake animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes shake {
                0%, 100% { transform: translateX(0); }
                10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
                20%, 40%, 60%, 80% { transform: translateX(5px); }
            }
            .invalid-feedback {
                color: #dc3545;
                font-size: 0.875rem;
                margin-top: 0.25rem;
                display: block;
            }
        `;
        document.head.appendChild(style);
        
        // Load application notifications
        loadApplicationNotifications();
        
        function loadApplicationNotifications() {
            fetch('api/application_notifications.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.notifications.length > 0) {
                        const container = document.getElementById('applicationNotifications');
                        container.innerHTML = '';
                        
                        data.notifications.forEach(notification => {
                            const alertClass = notification.type === 'approved' ? 'success' : 'warning';
                            const icon = notification.type === 'approved' ? 'check-circle' : 'info-circle';
                            
                            const alertHtml = `
                                <div class="alert alert-${alertClass} alert-dismissible fade show" role="alert">
                                    <i class="fas fa-${icon} me-2"></i>
                                    <strong>${notification.message}</strong>
                                    ${notification.admin_notes ? `<br><small>Admin Notes: ${notification.admin_notes}</small>` : ''}
                                    <br><small>Reviewed: ${new Date(notification.reviewed_at).toLocaleString()}</small>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            `;
                            
                            container.innerHTML += alertHtml;
                        });
                    }
                })
                .catch(error => console.error('Error loading notifications:', error));
        }

        // Role Application Functions
        function showApplicationForm(role) {
            const modal = new bootstrap.Modal(document.getElementById('applicationModal'));
            const modalTitle = document.getElementById('modalTitle');
            const appliedRole = document.getElementById('appliedRole');
            const reporterForm = document.getElementById('reporterForm');
            const editorForm = document.getElementById('editorForm');
            
            // Reset forms and CV previews
            document.getElementById('applicationForm').reset();
            document.getElementById('cvPreview').style.display = 'none';
            document.getElementById('cvPreviewEditor').style.display = 'none';
            
            // Set role and title
            appliedRole.value = role;
            modalTitle.textContent = 'Apply for ' + ucfirst(role) + ' Role';
            
            // Show appropriate form
            if (role === 'reporter') {
                reporterForm.style.display = 'block';
                editorForm.style.display = 'none';
            } else {
                reporterForm.style.display = 'none';
                editorForm.style.display = 'block';
            }
            
            // Show modal
            modal.show();
        }
        
        function ucfirst(str) {
            return str.charAt(0).toUpperCase() + str.slice(1);
        }
        
        function withdrawApplication(applicationId) {
            if (confirm('Are you sure you want to withdraw this application?')) {
                fetch('api/withdraw_application.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ application_id: applicationId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Application withdrawn successfully!');
                        location.reload();
                    } else {
                        alert('Error withdrawing application: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error withdrawing application. Please try again.');
                });
            }
        }

        // Reset form
        function resetForm() {
            if (confirm('Are you sure you want to reset the form? Any unsaved changes will be lost.')) {
                document.getElementById('profileForm').reset();
                document.getElementById('passwordStrength').className = 'password-strength';
            }
        }

        // CV file preview functionality
        function setupCVPreview(inputId, previewId, fileNameId, fileSizeId) {
            const input = document.getElementById(inputId);
            const preview = document.getElementById(previewId);
            const fileNameSpan = document.getElementById(fileNameId);
            const fileSizeSpan = document.getElementById(fileSizeId);
            
            input.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    // Validate file type
                    const allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
                    const allowedExtensions = ['.pdf', '.doc', '.docx'];
                    const fileName = file.name.toLowerCase();
                    const isValidType = allowedTypes.includes(file.type) || allowedExtensions.some(ext => fileName.endsWith(ext));
                    
                    if (!isValidType) {
                        alert('Please select a valid CV file (PDF, DOC, or DOCX)');
                        input.value = '';
                        preview.style.display = 'none';
                        return;
                    }
                    
                    // Validate file size (5MB)
                    const maxSize = 5 * 1024 * 1024;
                    if (file.size > maxSize) {
                        alert('CV file size must be less than 5MB');
                        input.value = '';
                        preview.style.display = 'none';
                        return;
                    }
                    
                    // Show preview
                    fileNameSpan.textContent = file.name;
                    fileSizeSpan.textContent = '(' + formatFileSize(file.size) + ')';
                    preview.style.display = 'block';
                } else {
                    preview.style.display = 'none';
                }
            });
        }
        
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
        
        // Setup CV previews for both forms
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM Content Loaded - Setting up form handlers');
            
            setupCVPreview('cv_file', 'cvPreview', 'cvFileName', 'cvFileSize');
            setupCVPreview('cv_file_editor', 'cvPreviewEditor', 'cvFileNameEditor', 'cvFileSizeEditor');
            
            // Setup application form submission - Let the form submit normally
            const applicationForm = document.getElementById('applicationForm');
            console.log('Application form found:', !!applicationForm);
            
            if (applicationForm) {
                // Add validation but allow normal submission
                applicationForm.addEventListener('submit', function(e) {
                    console.log('Form submit event triggered');
                    
                    // Validate form before submission
                    const appliedRole = document.getElementById('appliedRole').value;
                    if (!appliedRole) {
                        e.preventDefault();
                        alert('Please select a role to apply for.');
                        return false;
                    }
                    
                    // Check required fields based on role
                    let missingFields = [];
                    let firstInvalidField = null;
                    
                    // Helper function to check visible field
                    function checkVisibleField(selector, fieldName) {
                        const field = applicationForm.querySelector(selector);
                        if (field && field.offsetParent !== null) { // Check if field is visible
                            const value = field.value.trim();
                            if (!value) {
                                missingFields.push(fieldName);
                                if (!firstInvalidField) firstInvalidField = field;
                            }
                            return value;
                        }
                        return '';
                    }
                    
                    // Helper function to check visible file field
                    function checkVisibleFileField(selector, fieldName) {
                        const field = applicationForm.querySelector(selector);
                        if (field && field.offsetParent !== null) { // Check if field is visible
                            const file = field.files[0];
                            if (!file) {
                                missingFields.push(fieldName);
                                if (!firstInvalidField) firstInvalidField = field;
                            }
                            return file;
                        }
                        return null;
                    }
                    
                    const experience = checkVisibleField('textarea[name="experience"]', 'Experience');
                    const reason = checkVisibleField('textarea[name="reason"]', 'Reason for applying');
                    const availability = checkVisibleField('select[name="availability"]', 'Availability');
                    const evidenceType = checkVisibleField('select[name="evidence_type"]', 'Evidence type');
                    const cvFile = checkVisibleFileField('input[type="file"]', 'Supporting document');
                    
                    if (appliedRole === 'editor') {
                        checkVisibleField('textarea[name="qualifications"]', 'Qualifications');
                    }
                    
                    if (missingFields.length > 0) {
                        e.preventDefault();
                        alert('Please fill in all required fields: ' + missingFields.join(', '));
                        
                        // Focus on first invalid field that is visible
                        if (firstInvalidField) {
                            firstInvalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            firstInvalidField.focus();
                        }
                        return false;
                    }
                    
                    // Show loading state
                    const submitBtn = this.querySelector('button[type="submit"]');
                    const originalText = submitBtn.innerHTML;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Submitting...';
                    submitBtn.disabled = true;
                    
                    // Let the form submit normally
                    console.log('Form validation passed, submitting normally...');
                });
                console.log('Form validation listener attached');
            } else {
                console.error('Application form not found in DOM');
            }
        });
        
        // Submit role application function (no longer used - using normal form submission)
        // function submitApplication() {
        //     console.log('SubmitApplication function called');
        //     
        //     const form = document.getElementById('applicationForm');
        //     if (!form) {
        //         console.error('Application form not found');
        //         alert('Form not found. Please refresh the page and try again.');
        //         return;
        //     }
        //     
        //     // Validate form
        //     const appliedRole = document.getElementById('appliedRole').value;
        //     if (!appliedRole) {
        //         alert('Please select a role to apply for.');
        //         return;
        //     }
        //     
        //     // Check required fields based on role
        //     let missingFields = [];
        //     const experience = form.querySelector('textarea[name="experience"]').value.trim();
        //     const reason = form.querySelector('textarea[name="reason"]').value.trim();
        //     const availability = form.querySelector('select[name="availability"]').value;
        //     const cvFile = form.querySelector('input[type="file"]').files[0];
        //     
        //     if (!experience) missingFields.push('Experience');
        //     if (!reason) missingFields.push('Reason for applying');
        //     if (!availability) missingFields.push('Availability');
        //     if (!cvFile) missingFields.push('CV/Resume file');
        //     
        //     if (appliedRole === 'editor') {
        //         const qualifications = form.querySelector('textarea[name="qualifications"]').value.trim();
        //         if (!qualifications) missingFields.push('Qualifications');
        //     }
        //     
        //     if (missingFields.length > 0) {
        //         alert('Please fill in all required fields: ' + missingFields.join(', '));
        //         return;
        //     }
        //     
        //     const formData = new FormData(form);
        //     const submitBtn = form.querySelector('button[type="submit"]');
        //     
        //     // Show loading state
        //     const originalText = submitBtn.innerHTML;
        //     submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Submitting...';
        //     submitBtn.disabled = true;
        //     
        //     console.log('Submitting application to API...');
        //     
        //     fetch('api/submit_role_application.php', {
        //         method: 'POST',
        //         body: formData
        //     })
        //     .then(response => {
        //         console.log('Response received:', response);
        //         return response.json();
        //     })
        //     .then(data => {
        //         console.log('Data received:', data);
        //         if (data.success) {
        //             alert('Application submitted successfully! We will review your application and respond soon.');
        //             // Close modal and refresh page to show updated status
        //             const modal = bootstrap.Modal.getInstance(document.getElementById('applicationModal'));
        //             if (modal) {
        //                 modal.hide();
        //             }
        //             setTimeout(() => {
        //                 location.reload();
        //             }, 1000);
        //         } else {
        //             alert('Error submitting application: ' + (data.message || 'Unknown error'));
        //         }
        //     })
        //     .catch(error => {
        //         console.error('Error:', error);
        //         alert('Error submitting application. Please check your internet connection and try again.');
        //     })
        //     .finally(() => {
        //         // Reset button state
        //         submitBtn.innerHTML = originalText;
        //         submitBtn.disabled = false;
        //     });
        // }
        
        // Image preview
        document.getElementById('image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.createElement('img');
                    preview.src = e.target.result;
                    preview.className = 'image-preview mt-3';
                    preview.alt = 'New profile picture';
                    
                    const container = document.getElementById('image').parentNode;
                    const existingPreview = container.querySelector('.image-preview');
                    if (existingPreview) {
                        existingPreview.replaceWith(preview);
                    } else {
                        container.appendChild(preview);
                    }
                };
                reader.readAsDataURL(file);
            }
        });

        // Save criteria settings
        function saveCriteria() {
            const criteria = {
                email_notifications: document.getElementById('email_notifications').checked,
                push_notifications: document.getElementById('push_notifications').checked,
                newsletter_subscription: document.getElementById('newsletter_subscription').checked,
                profile_public: document.getElementById('profile_public').checked,
                show_activity: document.getElementById('show_activity').checked,
                preferred_categories: Array.from(document.getElementById('preferred_categories').selectedOptions).map(option => option.value),
                language_preference: document.getElementById('language_preference').value
            };

            fetch('api/update_profile_criteria.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(criteria)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Profile criteria updated successfully!');
                } else {
                    alert('Error updating criteria: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error updating criteria. Please try again.');
            });
        }

        // Reset criteria
        function resetCriteria() {
            if (confirm('Are you sure you want to reset all criteria to default values?')) {
                document.getElementById('email_notifications').checked = true;
                document.getElementById('push_notifications').checked = false;
                document.getElementById('newsletter_subscription').checked = true;
                document.getElementById('profile_public').checked = false;
                document.getElementById('show_activity').checked = true;
                document.getElementById('preferred_categories').selectedIndex = -1;
                document.getElementById('language_preference').value = 'en';
            }
        }

        // Send verification email
        function sendVerification() {
            fetch('api/send_verification.php', {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Verification email sent successfully!');
                } else {
                    alert('Error sending verification email: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error sending verification email. Please try again.');
            });
        }

        // Toggle 2FA
        function toggle2FA() {
            fetch('api/toggle_2fa.php', {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('2FA settings updated successfully!');
                    location.reload();
                } else {
                    alert('Error updating 2FA: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error updating 2FA. Please try again.');
            });
        }
    </script>

<!-- Header Interactions Fix -->
<script src="assets/js/header-interactions-fix.js"></script>



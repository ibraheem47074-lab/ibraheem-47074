<?php
// Simple test script for role application functionality
require_once 'config/database.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "Please log in first. <a href='login.php'>Login</a>";
    exit();
}

$user_id = $_SESSION['user_id'];

// Create tables if they don't exist
echo "<h2>Role Application System Test</h2>";

// Test 1: Create role_applications table
echo "<h3>Test 1: Creating role_applications table...</h3>";
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

if (mysqli_query($conn, $create_table)) {
    echo "✓ role_applications table created successfully<br>";
} else {
    echo "✗ Error creating role_applications table: " . mysqli_error($conn) . "<br>";
}

// Test 2: Add application_status columns to users table
echo "<h3>Test 2: Adding application_status columns to users table...</h3>";
$alter_table = "
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS application_status ENUM('none', 'pending', 'approved', 'rejected') DEFAULT 'none' AFTER role,
ADD COLUMN IF NOT EXISTS applied_role ENUM('editor', 'reporter') DEFAULT NULL AFTER application_status";

if (mysqli_query($conn, $alter_table)) {
    echo "✓ application_status columns added successfully<br>";
} else {
    echo "✗ Error adding application_status columns: " . mysqli_error($conn) . "<br>";
}

// Test 3: Create upload directories
echo "<h3>Test 3: Creating upload directories...</h3>";
$directories = ['uploads/cv/', 'uploads/cvs/'];
foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        if (mkdir($dir, 0755, true)) {
            echo "✓ Created directory: $dir<br>";
        } else {
            echo "✗ Failed to create directory: $dir<br>";
        }
    } else {
        echo "✓ Directory already exists: $dir<br>";
    }
}

// Test 4: Check current user status
echo "<h3>Test 4: Current user status</h3>";
$user_query = "SELECT id, name, email, role, application_status, applied_role FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $user_query);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if ($user) {
    echo "✓ User found: " . htmlspecialchars($user['name']) . "<br>";
    echo "Current role: " . htmlspecialchars($user['role'] ?? '') . "<br>";
    echo "Application status: " . htmlspecialchars($user['application_status'] ?? '') . "<br>";
    echo "Applied role: " . htmlspecialchars($user['applied_role'] ?? '') . "<br>";
} else {
    echo "✗ User not found<br>";
}

// Test 5: Check existing applications
echo "<h3>Test 5: Existing applications</h3>";
$app_query = "SELECT * FROM role_applications WHERE user_id = ? ORDER BY created_at DESC";
$stmt = mysqli_prepare($conn, $app_query);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$applications = [];
while ($app = mysqli_fetch_assoc($result)) {
    $applications[] = $app;
}

if (count($applications) > 0) {
    echo "✓ Found " . count($applications) . " application(s)<br>";
    foreach ($applications as $app) {
        echo "- Application for " . htmlspecialchars($app['applied_role']) . 
             " (Status: " . htmlspecialchars($app['status']) . 
             ", Created: " . $app['created_at'] . ")<br>";
    }
} else {
    echo "✓ No existing applications found<br>";
}

// Test 6: Test form submission simulation
echo "<h3>Test 6: Form submission simulation</h3>";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_submit'])) {
    $test_role = clean_input($_POST['test_role']);
    $test_experience = clean_input($_POST['test_experience']);
    $test_reason = clean_input($_POST['test_reason']);
    
    // Simulate application data
    $application_data = [
        'experience' => $test_experience,
        'qualifications' => 'Test qualifications',
        'reason' => $test_reason,
        'samples' => 'Test samples',
        'availability' => 'full-time'
    ];
    
    // Check for existing pending application
    $existing_query = "SELECT id FROM role_applications WHERE user_id = ? AND status = 'pending'";
    $existing_stmt = mysqli_prepare($conn, $existing_query);
    mysqli_stmt_bind_param($existing_stmt, 'i', $user_id);
    mysqli_stmt_execute($existing_stmt);
    $existing_result = mysqli_stmt_get_result($existing_stmt);
    
    if (mysqli_num_rows($existing_result) > 0) {
        echo "✗ You already have a pending application<br>";
    } else {
        // Insert test application
        $insert_query = "INSERT INTO role_applications (user_id, applied_role, application_data, cv_file_path, cv_file_name, cv_file_size) VALUES (?, ?, ?, ?, ?, ?)";
        $insert_stmt = mysqli_prepare($conn, $insert_query);
        $json_data = json_encode($application_data);
        $cv_file_path = 'uploads/cv/test_cv.pdf';
        $cv_file_name = 'test_cv.pdf';
        $cv_file_size = 12345;
        
        mysqli_stmt_bind_param($insert_stmt, 'issssi', $user_id, $test_role, $json_data, $cv_file_path, $cv_file_name, $cv_file_size);
        
        if (mysqli_stmt_execute($insert_stmt)) {
            echo "✓ Test application submitted successfully!<br>";
            
            // Update user status
            $update_user_query = "UPDATE users SET application_status = 'pending', applied_role = ? WHERE id = ?";
            $update_user_stmt = mysqli_prepare($conn, $update_user_query);
            mysqli_stmt_bind_param($update_user_stmt, 'si', $test_role, $user_id);
            mysqli_stmt_execute($update_user_stmt);
            
            echo "✓ User status updated<br>";
        } else {
            echo "✗ Error submitting test application: " . mysqli_error($conn) . "<br>";
        }
    }
}

echo "<hr>";
echo "<h3>Test Form</h3>";
echo "<form method='post'>";
echo "<input type='hidden' name='test_submit' value='1'>";
echo "<label>Role: <select name='test_role'><option value='reporter'>Reporter</option><option value='editor'>Editor</option></select></label><br><br>";
echo "<label>Experience: <textarea name='test_experience' rows='3' required>Test experience for role application</textarea></label><br><br>";
echo "<label>Reason: <textarea name='test_reason' rows='3' required>Test reason for applying</textarea></label><br><br>";
echo "<button type='submit'>Submit Test Application</button>";
echo "</form>";

echo "<hr>";
echo "<p><a href='profile.php'>Back to Profile</a></p>";
?>

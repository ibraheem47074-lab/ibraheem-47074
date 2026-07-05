<?php
require_once '../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Validate required fields
$required_fields = ['name', 'email', 'phone'];
foreach ($required_fields as $field) {
    if (empty($_POST[$field])) {
        echo json_encode(['success' => false, 'message' => ucfirst($field) . ' is required']);
        exit();
    }
}

$job_id = isset($_POST['job_id']) ? (int)$_POST['job_id'] : 0;
$name = clean_input($_POST['name']);
$email = clean_input($_POST['email']);
$phone = clean_input($_POST['phone']);
$position = isset($_POST['position']) ? clean_input($_POST['position']) : '';
$cover_letter = isset($_POST['cover_letter']) ? clean_input($_POST['cover_letter']) : '';

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit();
}

// Validate job exists
$job_check = mysqli_query($conn, "SELECT title, company_name FROM news WHERE id = $job_id AND is_job_posting = 1");
if (mysqli_num_rows($job_check) === 0) {
    echo json_encode(['success' => false, 'message' => 'Job not found']);
    exit();
}

$job = mysqli_fetch_assoc($job_check);

// Handle file upload
$resume_path = '';
if (isset($_FILES['resume']) && $_FILES['resume']['error'] == 0) {
    $allowed_extensions = ['pdf', 'doc', 'docx'];
    $max_size = 5 * 1024 * 1024; // 5MB
    $file_extension = strtolower(pathinfo($_FILES['resume']['name'], PATHINFO_EXTENSION));
    
    if (!in_array($file_extension, $allowed_extensions)) {
        echo json_encode(['success' => false, 'message' => 'Invalid file type. Only PDF, DOC, and DOCX files are allowed']);
        exit();
    }
    
    if ($_FILES['resume']['size'] > $max_size) {
        echo json_encode(['success' => false, 'message' => 'File size too large. Maximum size is 5MB']);
        exit();
    }
    
    $file_name = 'resume_' . uniqid() . '_' . time() . '.' . $file_extension;
    $upload_path = 'uploads/resumes/' . $file_name;
    $full_upload_path = '../' . $upload_path;
    
    // Ensure upload directory exists
    if (!is_dir('../uploads/resumes/')) {
        mkdir('../uploads/resumes/', 0755, true);
    }
    
    if (move_uploaded_file($_FILES['resume']['tmp_name'], $full_upload_path)) {
        $resume_path = $upload_path;
    }
}

// Create applications table if it doesn't exist
$create_table = "
CREATE TABLE IF NOT EXISTS job_applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    job_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    position VARCHAR(255),
    cover_letter TEXT,
    resume_path VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (job_id) REFERENCES news(id) ON DELETE CASCADE
)";
mysqli_query($conn, $create_table);

// Insert application
$query = "INSERT INTO job_applications (job_id, name, email, phone, position, cover_letter, resume_path) 
          VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'issssss', $job_id, $name, $email, $phone, $position, $cover_letter, $resume_path);

if (mysqli_stmt_execute($stmt)) {
    // Send email notification to admin (optional)
    $to = ADMIN_EMAIL;
    $subject = "New Job Application: " . $job['title'];
    $message = "
    A new job application has been submitted:
    
    Job: {$job['title']}
    Company: {$job['company_name']}
    Applicant Name: $name
    Email: $email
    Phone: $phone
    Position: $position
    
    Cover Letter:
    $cover_letter
    ";
    
    $headers = "From: $email\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    
    // Uncomment to send email (requires proper email configuration)
    // mail($to, $subject, $message, $headers);
    
    echo json_encode(['success' => true, 'message' => 'Application submitted successfully!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to submit application: ' . mysqli_error($conn)]);
}
?>

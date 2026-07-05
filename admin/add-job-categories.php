<?php
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    redirect('login.php');
}

$success = '';
$error = '';

// Add parent_id column if it doesn't exist
$add_parent_column = "
ALTER TABLE categories 
ADD COLUMN IF NOT EXISTS parent_id INT DEFAULT NULL,
ADD CONSTRAINT IF NOT EXISTS fk_category_parent 
FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL
";

if (mysqli_query($conn, $add_parent_column)) {
    $success .= "Categories table updated successfully. ";
} else {
    $error .= "Error updating categories table: " . mysqli_error($conn) . " ";
}

// Add job-specific columns to news table
$add_job_columns = "
ALTER TABLE news 
ADD COLUMN IF NOT EXISTS company_name VARCHAR(255) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS job_location VARCHAR(255) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS salary VARCHAR(255) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS last_date_to_apply DATE DEFAULT NULL,
ADD COLUMN IF NOT EXISTS job_type ENUM('Full-time', 'Part-time', 'Contract', 'Freelance', 'Internship') DEFAULT NULL,
ADD COLUMN IF NOT EXISTS apply_url VARCHAR(500) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS requirements TEXT DEFAULT NULL,
ADD COLUMN IF NOT EXISTS is_job_posting BOOLEAN DEFAULT FALSE
";

if (mysqli_query($conn, $add_job_columns)) {
    $success .= "News table updated with job fields. ";
} else {
    $error .= "Error updating news table: " . mysqli_error($conn) . " ";
}

// Check if Jobs category already exists
$check_jobs = mysqli_query($conn, "SELECT id FROM categories WHERE slug = 'jobs'");
if (mysqli_num_rows($check_jobs) == 0) {
    // Insert main Jobs category
    $insert_jobs = "INSERT INTO categories (name, slug, description, status, parent_id) VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $insert_jobs);
    $name = 'Jobs';
    $slug = 'jobs';
    $description = 'All job opportunities and career updates';
    $status = 'active';
    $parent_id = null;
    
    if (mysqli_stmt_bind_param($stmt, 'ssssi', $name, $slug, $description, $status, $parent_id)) {
        if (mysqli_stmt_execute($stmt)) {
            $jobs_category_id = mysqli_insert_id($conn);
            $success .= "Main Jobs category created. ";
            
            // Insert subcategories
            $subcategories = [
                ['Govt Jobs', 'govt-jobs', 'Government job opportunities and vacancies'],
                ['Private Jobs', 'private-jobs', 'Private sector job opportunities'],
                ['Overseas Jobs', 'overseas-jobs', 'International job opportunities'],
                ['Freelance Jobs', 'freelance-jobs', 'Freelance and remote work opportunities']
            ];
            
            foreach ($subcategories as $subcat) {
                $insert_subcat = "INSERT INTO categories (name, slug, description, status, parent_id) VALUES (?, ?, ?, ?, ?)";
                $stmt_sub = mysqli_prepare($conn, $insert_subcat);
                mysqli_stmt_bind_param($stmt_sub, 'ssssi', $subcat[0], $subcat[1], $subcat[2], $status, $jobs_category_id);
                mysqli_stmt_execute($stmt_sub);
            }
            $success .= "Job subcategories created successfully. ";
        } else {
            $error .= "Error creating Jobs category: " . mysqli_error($conn) . " ";
        }
    }
} else {
    $success .= "Jobs category already exists. ";
}

// Redirect to manage categories with message
if (!empty($success)) {
    $_SESSION['success_message'] = $success;
}
if (!empty($error)) {
    $_SESSION['error_message'] = $error;
}

header('Location: manage-categories.php');
exit();
?>

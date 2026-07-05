<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect('../login.php');
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = isset($_POST['title']) ? clean_input($_POST['title']) : '';
    $content = isset($_POST['content']) ? clean_input($_POST['content']) : '';
    $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
    $status = isset($_POST['status']) ? clean_input($_POST['status']) : 'draft';
    $excerpt = substr(strip_tags($content), 0, 200) . '...';
    
    // Job-specific fields
    $company_name = isset($_POST['company_name']) ? clean_input($_POST['company_name']) : '';
    $job_location = isset($_POST['job_location']) ? clean_input($_POST['job_location']) : '';
    $salary = isset($_POST['salary']) ? clean_input($_POST['salary']) : '';
    $last_date_to_apply = isset($_POST['last_date_to_apply']) ? clean_input($_POST['last_date_to_apply']) : null;
    $job_type = isset($_POST['job_type']) ? clean_input($_POST['job_type']) : null;
    $apply_url = isset($_POST['apply_url']) ? clean_input($_POST['apply_url']) : '';
    $requirements = isset($_POST['requirements']) ? clean_input($_POST['requirements']) : '';
    $is_job_posting = 1; // Always true for job posts
    
    $image_path = '';
    $video_path = '';
    $video_url = clean_input($_POST['video_url'] ?? '');
    
    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $max_size = 5 * 1024 * 1024; // 5MB
        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        
        if (in_array($file_extension, $allowed_extensions) && $_FILES['image']['size'] <= $max_size) {
            $file_name = 'img_' . uniqid() . '_' . time() . '.' . $file_extension;
            $upload_path = 'uploads/news/' . $file_name;
            $full_upload_path = '../' . $upload_path;
            
            // Ensure upload directory exists
            if (!is_dir('../uploads/news/')) {
                mkdir('../uploads/news/', 0755, true);
            }
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $full_upload_path)) {
                $image_path = $upload_path;
            }
        }
    }
    
    // Generate slug
    $slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', $title));
    $slug = trim($slug, '-');
    
    // Check for duplicate slug
    $counter = 1;
    $original_slug = $slug;
    while (true) {
        $check_query = "SELECT id FROM news WHERE slug = '$slug'";
        $check_result = mysqli_query($conn, $check_query);
        if (mysqli_num_rows($check_result) == 0) {
            break;
        }
        $slug = $original_slug . '-' . $counter;
        $counter++;
    }
    
    // Insert job posting
    $query = "INSERT INTO news (title, slug, content, excerpt, image, video_url, video_path, category_id, author_id, status, created_at, published_at, 
              company_name, job_location, salary, last_date_to_apply, job_type, apply_url, requirements, is_job_posting) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'sssssssiissssssssi', 
        $title, $slug, $content, $excerpt, $image_path, $video_url, $video_path, 
        $category_id, $_SESSION['user_id'], $status, 
        $company_name, $job_location, $salary, $last_date_to_apply, $job_type, $apply_url, $requirements, $is_job_posting);
    
    if (mysqli_stmt_execute($stmt)) {
        $success = "Job posted successfully!";
    } else {
        $error = "Failed to post job: " . mysqli_error($conn);
    }
}

// Get job categories (Jobs and its subcategories)
$job_categories = mysqli_query($conn, "SELECT * FROM categories WHERE (slug = 'jobs' OR parent_id IN (SELECT id FROM categories WHERE slug = 'jobs')) AND status = 'active' ORDER BY name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Job - PK Live News Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .admin-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .admin-sidebar {
            background: #2d3748;
            min-height: 100vh;
            color: white;
        }
        .admin-sidebar .nav-link {
            color: #cbd5e0;
            padding: 12px 20px;
            border-radius: 5px;
            margin-bottom: 5px;
            transition: all 0.3s ease;
        }
        .admin-sidebar .nav-link:hover,
        .admin-sidebar .nav-link.active {
            background-color: #4a5568;
            color: white;
        }
        .admin-main-content {
            background-color: #f7fafc;
            min-height: 100vh;
        }
        .job-form-section {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 25px;
            margin-bottom: 20px;
        }
        .form-label {
            font-weight: 600;
            color: #2d3748;
        }
        .job-type-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.875rem;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block admin-sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h4><i class="fas fa-newspaper me-2"></i>PK Live News</h4>
                        <small>Admin Panel</small>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage-news.php">
                                <i class="fas fa-newspaper me-2"></i>Manage News
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage-categories.php">
                                <i class="fas fa-tags me-2"></i>Categories
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="add-job.php">
                                <i class="fas fa-briefcase me-2"></i>Add Job
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage-users.php">
                                <i class="fas fa-users me-2"></i>Users
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="live-stream.php">
                                <i class="fas fa-broadcast-tower me-2"></i>Live Stream
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage-comments.php">
                                <i class="fas fa-comments me-2"></i>Comments
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage-polls.php">
                                <i class="fas fa-poll me-2"></i>Polls
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage-ads.php">
                                <i class="fas fa-ad me-2"></i>Advertisements
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="settings.php">
                                <i class="fas fa-cog me-2"></i>Settings
                            </a>
                        </li>
                        <li class="nav-item mt-3">
                            <a class="nav-link text-danger" href="logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 admin-main-content">
                <!-- Header -->
                <div class="admin-header d-flex justify-content-between align-items-center py-3 px-4 mb-4 rounded">
                    <div>
                        <h1 class="h3 mb-0"><i class="fas fa-briefcase me-2"></i>Add Job Posting</h1>
                        <small>Post a new job opportunity</small>
                    </div>
                    <div>
                        <a href="manage-news.php" class="btn btn-light">
                            <i class="fas fa-arrow-left me-2"></i>Back to News
                        </a>
                    </div>
                </div>

                <!-- Alerts -->
                <?php if (isset($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Job Posting Form -->
                <form method="POST" enctype="multipart/form-data" id="jobForm">
                    <!-- Basic Information -->
                    <div class="job-form-section">
                        <h5 class="mb-4"><i class="fas fa-info-circle me-2"></i>Basic Information</h5>
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Job Title *</label>
                                    <input type="text" class="form-control" id="title" name="title" 
                                           value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>" 
                                           required placeholder="e.g., Punjab Police Jobs 2026">
                                </div>

                                <div class="mb-3">
                                    <label for="company_name" class="form-label">Company Name *</label>
                                    <input type="text" class="form-control" id="company_name" name="company_name" 
                                           value="<?php echo isset($_POST['company_name']) ? htmlspecialchars($_POST['company_name']) : ''; ?>" 
                                           required placeholder="e.g., Punjab Police Department">
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="category_id" class="form-label">Job Category *</label>
                                            <select class="form-select" id="category_id" name="category_id" required>
                                                <option value="">Select Category</option>
                                                <?php while ($cat = mysqli_fetch_assoc($job_categories)): ?>
                                                    <option value="<?php echo $cat['id']; ?>" <?php echo isset($_POST['category_id']) && $_POST['category_id'] == $cat['id'] ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($cat['name']); ?>
                                                    </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="job_type" class="form-label">Job Type</label>
                                            <select class="form-select" id="job_type" name="job_type">
                                                <option value="">Select Type</option>
                                                <option value="Full-time" <?php echo isset($_POST['job_type']) && $_POST['job_type'] == 'Full-time' ? 'selected' : ''; ?>>Full-time</option>
                                                <option value="Part-time" <?php echo isset($_POST['job_type']) && $_POST['job_type'] == 'Part-time' ? 'selected' : ''; ?>>Part-time</option>
                                                <option value="Contract" <?php echo isset($_POST['job_type']) && $_POST['job_type'] == 'Contract' ? 'selected' : ''; ?>>Contract</option>
                                                <option value="Freelance" <?php echo isset($_POST['job_type']) && $_POST['job_type'] == 'Freelance' ? 'selected' : ''; ?>>Freelance</option>
                                                <option value="Internship" <?php echo isset($_POST['job_type']) && $_POST['job_type'] == 'Internship' ? 'selected' : ''; ?>>Internship</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="image" class="form-label">Job Image</label>
                                    <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                    <small class="text-muted">Optional: Add a relevant image</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Job Details -->
                    <div class="job-form-section">
                        <h5 class="mb-4"><i class="fas fa-briefcase me-2"></i>Job Details</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="job_location" class="form-label">Location *</label>
                                    <input type="text" class="form-control" id="job_location" name="job_location" 
                                           value="<?php echo isset($_POST['job_location']) ? htmlspecialchars($_POST['job_location']) : ''; ?>" 
                                           required placeholder="e.g., Lahore, Karachi, Remote">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="salary" class="form-label">Salary</label>
                                    <input type="text" class="form-control" id="salary" name="salary" 
                                           value="<?php echo isset($_POST['salary']) ? htmlspecialchars($_POST['salary']) : ''; ?>" 
                                           placeholder="e.g., 50,000 - 80,000 PKR">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="last_date_to_apply" class="form-label">Last Date to Apply</label>
                                    <input type="date" class="form-control" id="last_date_to_apply" name="last_date_to_apply" 
                                           value="<?php echo isset($_POST['last_date_to_apply']) ? htmlspecialchars($_POST['last_date_to_apply']) : ''; ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="apply_url" class="form-label">Application URL</label>
                                    <input type="url" class="form-control" id="apply_url" name="apply_url" 
                                           value="<?php echo isset($_POST['apply_url']) ? htmlspecialchars($_POST['apply_url']) : ''; ?>" 
                                           placeholder="https://example.com/apply">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Job Description -->
                    <div class="job-form-section">
                        <h5 class="mb-4"><i class="fas fa-file-alt me-2"></i>Job Description</h5>
                        
                        <div class="mb-3">
                            <label for="content" class="form-label">Job Description *</label>
                            <textarea class="form-control" id="content" name="content" rows="6" required 
                                      placeholder="Provide detailed job description, responsibilities, and qualifications..."><?php echo isset($_POST['content']) ? htmlspecialchars($_POST['content']) : ''; ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="requirements" class="form-label">Requirements</label>
                            <textarea class="form-control" id="requirements" name="requirements" rows="4" 
                                      placeholder="List specific requirements, qualifications, and skills needed..."><?php echo isset($_POST['requirements']) ? htmlspecialchars($_POST['requirements']) : ''; ?></textarea>
                        </div>
                    </div>

                    <!-- Publishing Options -->
                    <div class="job-form-section">
                        <h5 class="mb-4"><i class="fas fa-cog me-2"></i>Publishing Options</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="draft" <?php echo isset($_POST['status']) && $_POST['status'] == 'draft' ? 'selected' : ''; ?>>Draft</option>
                                        <option value="published" <?php echo isset($_POST['status']) && $_POST['status'] == 'published' ? 'selected' : ''; ?>>Published</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="d-flex justify-content-between">
                        <a href="manage-news.php" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i>Cancel
                        </a>
                        <div>
                            <button type="submit" name="save_draft" class="btn btn-outline-primary me-2">
                                <i class="fas fa-save me-2"></i>Save as Draft
                            </button>
                            <button type="submit" name="publish" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-2"></i>Publish Job
                            </button>
                        </div>
                    </div>
                </form>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        document.getElementById('jobForm').addEventListener('submit', function(e) {
            const title = document.getElementById('title').value.trim();
            const company = document.getElementById('company_name').value.trim();
            const location = document.getElementById('job_location').value.trim();
            const category = document.getElementById('category_id').value;
            const content = document.getElementById('content').value.trim();
            
            if (!title || !company || !location || !category || !content) {
                e.preventDefault();
                alert('Please fill in all required fields');
                return false;
            }
            
            if (title.length < 5) {
                e.preventDefault();
                alert('Job title must be at least 5 characters long');
                return false;
            }
        });

        // Auto-set status based on submit button
        document.querySelector('button[name="save_draft"]').addEventListener('click', function() {
            document.getElementById('status').value = 'draft';
        });

        document.querySelector('button[name="publish"]').addEventListener('click', function() {
            document.getElementById('status').value = 'published';
        });
    </script>
</body>
</html>

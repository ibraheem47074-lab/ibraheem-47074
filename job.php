<?php
require_once 'config/database.php';

// Get job by slug
$slug = isset($_GET['slug']) ? clean_input($_GET['slug']) : '';
if (empty($slug)) {
    header('HTTP/1.0 404 Not Found');
    include '404.php';
    exit();
}

// Get job details
$query = "SELECT n.*, c.name as category_name, u.username as author_name 
          FROM news n 
          LEFT JOIN categories c ON n.category_id = c.id 
          LEFT JOIN users u ON n.author_id = u.id 
          WHERE n.slug = ? AND n.is_job_posting = 1 AND n.status = 'published'";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 's', $slug);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    header('HTTP/1.0 404 Not Found');
    include '404.php';
    exit();
}

$job = mysqli_fetch_assoc($result);

// Get related jobs (same category)
$related_query = "SELECT n.*, c.name as category_name 
                 FROM news n 
                 LEFT JOIN categories c ON n.category_id = c.id 
                 WHERE n.category_id = ? AND n.id != ? AND n.is_job_posting = 1 AND n.status = 'published' 
                 ORDER BY n.created_at DESC 
                 LIMIT 5";

$related_stmt = mysqli_prepare($conn, $related_query);
mysqli_stmt_bind_param($related_stmt, 'ii', $job['category_id'], $job['id']);
mysqli_stmt_execute($related_stmt);
$related_result = mysqli_stmt_get_result($related_stmt);

// Calculate days until deadline
$days_left = '';
$deadline_class = '';
if ($job['last_date_to_apply'] && $job['last_date_to_apply'] !== '0000-00-00') {
    $deadline = new DateTime($job['last_date_to_apply']);
    $today = new DateTime();
    $interval = $today->diff($deadline);
    
    if ($deadline < $today) {
        $days_left = 'Expired';
        $deadline_class = 'text-danger';
    } elseif ($interval->days == 0) {
        $days_left = 'Last Day';
        $deadline_class = 'text-warning';
    } elseif ($interval->days <= 3) {
        $days_left = $interval->days . ' days left';
        $deadline_class = 'text-warning';
    } else {
        $days_left = $interval->days . ' days left';
        $deadline_class = 'text-success';
    }
}

// Job type colors
$job_type_colors = [
    'Full-time' => 'primary',
    'Part-time' => 'info',
    'Contract' => 'warning',
    'Freelance' => 'success',
    'Internship' => 'secondary'
];
$job_type_color = $job_type_colors[$job['job_type']] ?? 'secondary';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($job['title']); ?> - Job | PK Live News</title>
    <meta name="description" content="<?php echo htmlspecialchars(substr(strip_tags($job['excerpt']), 0, 160)); ?>">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            background-color: #f8f9fa;
        }
        
        .job-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 60px 0 40px;
        }
        
        .job-details-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .company-logo {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 10px;
            border: 3px solid #fff;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .job-meta-badge {
            padding: 8px 16px;
            border-radius: 25px;
            font-weight: 500;
            margin: 5px;
            display: inline-block;
        }
        
        .apply-section {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .apply-btn-large {
            background: white;
            color: #28a745;
            padding: 15px 40px;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            border: none;
            font-size: 18px;
        }
        
        .apply-btn-large:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            color: #218838;
        }
        
        .deadline-alert {
            background: rgba(255,255,255,0.2);
            border-radius: 10px;
            padding: 15px;
            margin-top: 20px;
        }
        
        .requirements-list {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            border-left: 4px solid #007bff;
        }
        
        .related-job-card {
            transition: all 0.3s ease;
            border: 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .related-job-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        
        .breadcrumb {
            background: transparent;
        }
        
        .breadcrumb-item + .breadcrumb-item::before {
            content: ">";
            color: rgba(255,255,255,0.7);
        }
        
        .breadcrumb a {
            color: rgba(255,255,255,0.9);
            text-decoration: none;
        }
        
        .breadcrumb a:hover {
            color: white;
        }
        
        .job-content {
            line-height: 1.8;
            font-size: 16px;
        }
        
        .job-content h3 {
            color: #2c3e50;
            margin-top: 30px;
            margin-bottom: 15px;
            font-weight: 600;
        }
        
        .job-content ul {
            padding-left: 20px;
        }
        
        .job-content li {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <!-- Job Header -->
    <div class="job-header">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>category.php?slug=jobs">Jobs</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($job['title']); ?></li>
                </ol>
            </nav>
            
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="display-5 fw-bold mb-3"><?php echo htmlspecialchars($job['title']); ?></h1>
                    <div class="d-flex align-items-center flex-wrap">
                        <div class="me-4">
                            <i class="fas fa-building me-2"></i>
                            <strong><?php echo htmlspecialchars($job['company_name']); ?></strong>
                        </div>
                        <?php if ($job['job_location']): ?>
                            <div class="me-4">
                                <i class="fas fa-map-marker-alt me-2"></i>
                                <?php echo htmlspecialchars($job['job_location']); ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($job['job_type']): ?>
                            <div>
                                <span class="badge bg-<?php echo $job_type_color; ?> fs-6">
                                    <?php echo htmlspecialchars($job['job_type']); ?>
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <?php if ($job['image']): ?>
                        <img src="<?php echo SITE_URL . $job['image']; ?>" 
                             alt="<?php echo htmlspecialchars($job['company_name']); ?>" 
                             class="company-logo">
                    <?php else: ?>
                        <div class="company-logo d-flex align-items-center justify-content-center bg-white">
                            <i class="fas fa-briefcase fa-3x text-muted"></i>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container my-5">
        <div class="row">
            <!-- Left Column - Job Details -->
            <div class="col-lg-8">
                <div class="job-details-card mb-4">
                    <div class="card-body p-4">
                        <!-- Quick Info -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="job-meta-badge bg-light">
                                    <i class="fas fa-money-bill-wave me-2 text-success"></i>
                                    <?php echo $job['salary'] ? htmlspecialchars($job['salary']) : 'Salary not disclosed'; ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="job-meta-badge bg-light">
                                    <i class="fas fa-clock me-2 text-primary"></i>
                                    Posted <?php echo format_news_date($job['created_at']); ?>
                                </div>
                            </div>
                        </div>

                        <!-- Job Description -->
                        <div class="job-content">
                            <h3><i class="fas fa-file-alt me-2"></i>Job Description</h3>
                            <div>
                                <?php echo format_news_content($job['content']); ?>
                            </div>
                        </div>

                        <!-- Requirements -->
                        <?php if ($job['requirements']): ?>
                            <div class="job-content">
                                <h3><i class="fas fa-list-check me-2"></i>Requirements</h3>
                                <div class="requirements-list">
                                    <?php echo format_news_content($job['requirements']); ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Additional Information -->
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <h5><i class="fas fa-info-circle me-2"></i>Additional Info</h5>
                                <ul class="list-unstyled">
                                    <li><strong>Category:</strong> <?php echo htmlspecialchars($job['category_name']); ?></li>
                                    <li><strong>Posted by:</strong> <?php echo htmlspecialchars($job['author_name']); ?></li>
                                    <?php if ($job['last_date_to_apply'] && $job['last_date_to_apply'] !== '0000-00-00'): ?>
                                        <li><strong>Last Date:</strong> <?php echo date('M d, Y', strtotime($job['last_date_to_apply'])); ?></li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Apply Section -->
            <div class="col-lg-4">
                <!-- Apply Card -->
                <div class="card border-0 shadow mb-4">
                    <div class="apply-section">
                        <h4 class="mb-3"><i class="fas fa-paper-plane me-2"></i>Apply for this Job</h4>
                        <p class="mb-4">Ready to take the next step in your career?</p>
                        
                        <?php if ($job['apply_url']): ?>
                            <a href="<?php echo htmlspecialchars($job['apply_url']); ?>" 
                               target="_blank" 
                               class="apply-btn-large">
                                <i class="fas fa-external-link-alt me-2"></i>
                                Apply Now
                            </a>
                        <?php else: ?>
                            <button class="apply-btn-large" onclick="showApplyForm()">
                                <i class="fas fa-envelope me-2"></i>
                                Apply Now
                            </button>
                        <?php endif; ?>
                        
                        <?php if ($days_left): ?>
                            <div class="deadline-alert">
                                <h6 class="mb-1">
                                    <i class="fas fa-clock me-2"></i>
                                    Application Deadline
                                </h6>
                                <p class="mb-0 <?php echo $deadline_class; ?>">
                                    <strong><?php echo $days_left; ?></strong>
                                    <?php if ($job['last_date_to_apply'] && $job['last_date_to_apply'] !== '0000-00-00'): ?>
                                        <br>
                                        <small><?php echo date('F j, Y', strtotime($job['last_date_to_apply'])); ?></small>
                                    <?php endif; ?>
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Share Job -->
                <div class="card border-0 shadow mb-4">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-share-alt me-2"></i>Share this Job</h5>
                        <div class="d-flex gap-2">
                            <button class="btn btn-primary btn-sm" onclick="shareOnFacebook()">
                                <i class="fab fa-facebook-f"></i>
                            </button>
                            <button class="btn btn-info btn-sm" onclick="shareOnTwitter()">
                                <i class="fab fa-twitter"></i>
                            </button>
                            <button class="btn btn-success btn-sm" onclick="shareOnWhatsApp()">
                                <i class="fab fa-whatsapp"></i>
                            </button>
                            <button class="btn btn-secondary btn-sm" onclick="copyLink()">
                                <i class="fas fa-link"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Related Jobs -->
        <?php if (mysqli_num_rows($related_result) > 0): ?>
            <div class="mt-5">
                <h3 class="mb-4"><i class="fas fa-briefcase me-2"></i>Similar Jobs</h3>
                <div class="row">
                    <?php while ($related_job = mysqli_fetch_assoc($related_result)): ?>
                        <div class="col-md-6 mb-3">
                            <?php 
                            $job = $related_job; 
                            include 'components/job_card.php'; 
                            ?>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Apply Form Modal -->
    <div class="modal fade" id="applyModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Apply for <?php echo htmlspecialchars($job['title']); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="applicationForm">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Full Name *</label>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email *</label>
                                <input type="email" class="form-control" name="email" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone *</label>
                                <input type="tel" class="form-control" name="phone" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Current Position</label>
                                <input type="text" class="form-control" name="position">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Cover Letter</label>
                            <textarea class="form-control" name="cover_letter" rows="4" 
                                      placeholder="Tell us why you're interested in this position..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Resume/CV</label>
                            <input type="file" class="form-control" name="resume" accept=".pdf,.doc,.docx">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" onclick="submitApplication()">Submit Application</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function showApplyForm() {
            const modal = new bootstrap.Modal(document.getElementById('applyModal'));
            modal.show();
        }

        function shareOnFacebook() {
            const url = encodeURIComponent(window.location.href);
            const title = encodeURIComponent(document.title);
            window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}`, '_blank');
        }

        function shareOnTwitter() {
            const url = encodeURIComponent(window.location.href);
            const title = encodeURIComponent(document.title);
            window.open(`https://twitter.com/intent/tweet?url=${url}&text=${title}`, '_blank');
        }

        function shareOnWhatsApp() {
            const url = encodeURIComponent(window.location.href);
            const title = encodeURIComponent(document.title);
            window.open(`https://wa.me/?text=${title}%20${url}`, '_blank');
        }

        function copyLink() {
            navigator.clipboard.writeText(window.location.href).then(() => {
                alert('Link copied to clipboard!');
            });
        }

        function submitApplication() {
            const form = document.getElementById('applicationForm');
            const formData = new FormData(form);
            formData.append('job_id', '<?php echo $job['id']; ?>');
            
            fetch('<?php echo SITE_URL; ?>api/submit_application.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Application submitted successfully!');
                    bootstrap.Modal.getInstance(document.getElementById('applyModal')).hide();
                    form.reset();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                alert('Error submitting application. Please try again.');
            });
        }
    </script>
</body>
</html>

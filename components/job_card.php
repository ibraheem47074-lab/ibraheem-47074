<?php
/**
 * Job Card Component
 * Displays a job posting with all relevant details and apply button
 */

// Get job data from parameters or global scope
if (isset($job) && $job) {
    $job_id = $job['id'];
    $title = $job['title'];
    $company_name = $job['company_name'] ?? '';
    $job_location = $job['job_location'] ?? '';
    $salary = $job['salary'] ?? '';
    $last_date_to_apply = $job['last_date_to_apply'] ?? '';
    $job_type = $job['job_type'] ?? '';
    $apply_url = $job['apply_url'] ?? '';
    $requirements = $job['requirements'] ?? '';
    $excerpt = $job['excerpt'] ?? '';
    $image = $job['image'] ?? '';
    $created_at = $job['created_at'] ?? '';
    $slug = $job['slug'] ?? '';
} else {
    // Fallback if no job data
    return;
}

// Calculate days until deadline
$days_left = '';
$deadline_class = '';
if ($last_date_to_apply && $last_date_to_apply !== '0000-00-00') {
    $deadline = new DateTime($last_date_to_apply);
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
$job_type_color = $job_type_colors[$job_type] ?? 'secondary';
?>

<div class="job-card card border-0 shadow-sm mb-4 hover-lift">
    <div class="card-body p-4">
        <div class="row align-items-start">
            <!-- Job Image/Logo -->
            <div class="col-md-2 text-center mb-3 mb-md-0">
                <?php if ($image): ?>
                    <img src="<?php echo SITE_URL . $image; ?>" alt="<?php echo htmlspecialchars($company_name); ?>" 
                         class="img-fluid rounded" style="max-height: 80px; object-fit: cover;">
                <?php else: ?>
                    <div class="job-placeholder bg-light rounded d-flex align-items-center justify-content-center" 
                         style="height: 80px;">
                        <i class="fas fa-briefcase fa-2x text-muted"></i>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Job Details -->
            <div class="col-md-7">
                <div class="mb-2">
                    <h5 class="card-title mb-1">
                        <a href="<?php echo SITE_URL; ?>job.php?slug=<?php echo $slug; ?>" 
                           class="text-decoration-none job-title-link">
                            <?php echo htmlspecialchars($title); ?>
                        </a>
                    </h5>
                    <h6 class="company-name text-muted mb-2">
                        <i class="fas fa-building me-1"></i>
                        <?php echo htmlspecialchars($company_name); ?>
                    </h6>
                </div>

                <!-- Job Meta Information -->
                <div class="job-meta mb-3">
                    <div class="row g-2">
                        <?php if ($job_location): ?>
                            <div class="col-auto">
                                <span class="badge bg-light text-dark">
                                    <i class="fas fa-map-marker-alt me-1"></i>
                                    <?php echo htmlspecialchars($job_location); ?>
                                </span>
                            </div>
                        <?php endif; ?>

                        <?php if ($job_type): ?>
                            <div class="col-auto">
                                <span class="badge bg-<?php echo $job_type_color; ?>">
                                    <?php echo htmlspecialchars($job_type); ?>
                                </span>
                            </div>
                        <?php endif; ?>

                        <?php if ($salary): ?>
                            <div class="col-auto">
                                <span class="badge bg-success">
                                    <i class="fas fa-money-bill-wave me-1"></i>
                                    <?php echo htmlspecialchars($salary); ?>
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Job Description -->
                <?php if ($excerpt): ?>
                    <p class="card-text text-muted mb-2">
                        <?php echo htmlspecialchars(substr(strip_tags($excerpt), 0, 150)) . '...'; ?>
                    </p>
                <?php endif; ?>

                <!-- Requirements Preview -->
                <?php if ($requirements): ?>
                    <div class="requirements-preview mb-2">
                        <small class="text-muted">
                            <strong>Requirements:</strong> 
                            <?php echo htmlspecialchars(substr(strip_tags($requirements), 0, 100)) . '...'; ?>
                        </small>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Apply Section -->
            <div class="col-md-3 text-end">
                <!-- Deadline -->
                <?php if ($days_left): ?>
                    <div class="deadline mb-3">
                        <small class="<?php echo $deadline_class; ?>">
                            <i class="fas fa-clock me-1"></i>
                            <?php echo $days_left; ?>
                        </small>
                    </div>
                <?php endif; ?>

                <!-- Apply Button -->
                <div class="apply-section">
                    <?php if ($apply_url): ?>
                        <a href="<?php echo htmlspecialchars($apply_url); ?>" 
                           target="_blank" 
                           class="btn btn-success btn-sm w-100 mb-2 apply-btn">
                            <i class="fas fa-external-link-alt me-1"></i>
                            Apply Now
                        </a>
                    <?php else: ?>
                        <a href="<?php echo SITE_URL; ?>job.php?slug=<?php echo $slug; ?>#apply" 
                           class="btn btn-success btn-sm w-100 mb-2 apply-btn">
                            <i class="fas fa-paper-plane me-1"></i>
                            Apply Now
                        </a>
                    <?php endif; ?>

                    <!-- View Details -->
                    <a href="<?php echo SITE_URL; ?>job.php?slug=<?php echo $slug; ?>" 
                       class="btn btn-outline-primary btn-sm w-100">
                        <i class="fas fa-info-circle me-1"></i>
                        View Details
                    </a>
                </div>

                <!-- Posted Date -->
                <div class="posted-date mt-3">
                    <small class="text-muted">
                        <i class="fas fa-calendar me-1"></i>
                        Posted <?php echo format_news_date($created_at); ?>
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.job-card {
    transition: all 0.3s ease;
    border-left: 4px solid #28a745;
}

.job-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
}

.job-title-link {
    color: #2c3e50;
    transition: color 0.3s ease;
}

.job-title-link:hover {
    color: #28a745;
}

.company-name {
    font-size: 0.9rem;
}

.job-placeholder {
    border: 2px dashed #dee2e6;
}

.apply-btn {
    background: linear-gradient(135deg, #28a745, #20c997);
    border: none;
    transition: all 0.3s ease;
}

.apply-btn:hover {
    background: linear-gradient(135deg, #218838, #1ea085);
    transform: translateY(-1px);
}

.deadline {
    font-weight: 600;
}

.requirements-preview {
    background: #f8f9fa;
    padding: 8px;
    border-radius: 5px;
    border-left: 3px solid #007bff;
}

@media (max-width: 768px) {
    .job-card .col-md-3 {
        text-align: left !important;
        margin-top: 15px;
    }
    
    .job-card .apply-section {
        display: flex;
        gap: 10px;
    }
    
    .job-card .apply-section .btn {
        flex: 1;
    }
}
</style>

<?php
require_once 'config/database.php';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 12;
$offset = ($page - 1) * $per_page;

// Get category filter
$category_slug = isset($_GET['category']) ? clean_input($_GET['category']) : '';
$job_type = isset($_GET['type']) ? clean_input($_GET['type']) : '';
$location = isset($_GET['location']) ? clean_input($_GET['location']) : '';

// Build query
$where_conditions = ["n.is_job_posting = 1", "n.status = 'published'"];
$params = [];
$types = '';

if ($category_slug) {
    $where_conditions[] = "c.slug = ?";
    $params[] = $category_slug;
    $types .= 's';
}

if ($job_type) {
    $where_conditions[] = "n.job_type = ?";
    $params[] = $job_type;
    $types .= 's';
}

if ($location) {
    $where_conditions[] = "n.job_location LIKE ?";
    $params[] = '%' . $location . '%';
    $types .= 's';
}

$where_clause = "WHERE " . implode(" AND ", $where_conditions);

// Get total count
$count_query = "SELECT COUNT(*) as total 
               FROM news n 
               LEFT JOIN categories c ON n.category_id = c.id 
               $where_clause";

if (!empty($params)) {
    $count_stmt = mysqli_prepare($conn, $count_query);
    mysqli_stmt_bind_param($count_stmt, $types, ...$params);
    mysqli_stmt_execute($count_stmt);
    $count_result = mysqli_stmt_get_result($count_stmt);
} else {
    $count_result = mysqli_query($conn, $count_query);
}

$total_jobs = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_jobs / $per_page);

// Get jobs
$query = "SELECT n.*, c.name as category_name, c.slug as category_slug 
          FROM news n 
          LEFT JOIN categories c ON n.category_id = c.id 
          $where_clause 
          ORDER BY n.created_at DESC 
          LIMIT ? OFFSET ?";

$params[] = $per_page;
$params[] = $offset;
$types .= 'ii';

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$jobs_result = mysqli_stmt_get_result($stmt);

// Get job categories
$categories_query = "SELECT * FROM categories WHERE (slug = 'jobs' OR parent_id IN (SELECT id FROM categories WHERE slug = 'jobs')) AND status = 'active' ORDER BY name ASC";
$categories = mysqli_query($conn, $categories_query);

// Get unique job types
$job_types_query = "SELECT DISTINCT job_type FROM news WHERE job_type IS NOT NULL AND job_type != '' AND is_job_posting = 1 AND status = 'published'";
$job_types_result = mysqli_query($conn, $job_types_query);

// Get unique locations
$locations_query = "SELECT DISTINCT job_location FROM news WHERE job_location IS NOT NULL AND job_location != '' AND is_job_posting = 1 AND status = 'published' LIMIT 20";
$locations_result = mysqli_query($conn, $locations_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jobs - PK Live News</title>
    <meta name="description" content="Find latest job opportunities in Pakistan and abroad">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            background-color: #f8f9fa;
        }
        
        .jobs-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 80px 0 60px;
        }
        
        .filter-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            padding: 25px;
            margin-bottom: 30px;
        }
        
        .job-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            padding: 25px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
            border-left: 4px solid #28a745;
        }
        
        .job-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        
        .job-title {
            color: #2c3e50;
            font-weight: 600;
            font-size: 1.2rem;
            margin-bottom: 10px;
        }
        
        .job-title:hover {
            color: #28a745;
        }
        
        .company-name {
            color: #6c757d;
            font-size: 1rem;
            margin-bottom: 15px;
        }
        
        .job-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .job-meta-badge {
            background: #f8f9fa;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            color: #495057;
            border: 1px solid #dee2e6;
        }
        
        .apply-btn {
            background: linear-gradient(135deg, #28a745, #20c997);
            border: none;
            color: white;
            padding: 8px 20px;
            border-radius: 25px;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .apply-btn:hover {
            background: linear-gradient(135deg, #218838, #1ea085);
            transform: translateY(-2px);
            color: white;
        }
        
        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 3px 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .stats-number {
            font-size: 2rem;
            font-weight: bold;
            color: #28a745;
        }
        
        .pagination {
            justify-content: center;
            margin-top: 40px;
        }
        
        .page-link {
            color: #28a745;
            border-color: #28a745;
            margin: 0 2px;
            border-radius: 5px;
        }
        
        .page-link:hover {
            background-color: #28a745;
            border-color: #28a745;
            color: white;
        }
        
        .page-item.active .page-link {
            background-color: #28a745;
            border-color: #28a745;
        }
        
        .filter-section {
            margin-bottom: 25px;
        }
        
        .filter-title {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 1.1rem;
        }
        
        .filter-option {
            display: block;
            padding: 8px 15px;
            margin-bottom: 5px;
            border-radius: 8px;
            text-decoration: none;
            color: #495057;
            transition: all 0.3s ease;
        }
        
        .filter-option:hover {
            background-color: #f8f9fa;
            color: #28a745;
            text-decoration: none;
        }
        
        .filter-option.active {
            background-color: #28a745;
            color: white;
        }
        
        .no-jobs {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }
        
        .no-jobs i {
            font-size: 4rem;
            margin-bottom: 20px;
            color: #dee2e6;
        }
        
        .search-box {
            position: relative;
            margin-bottom: 30px;
        }
        
        .search-box input {
            padding-left: 45px;
            border-radius: 50px;
            border: 2px solid #e9ecef;
            height: 50px;
        }
        
        .search-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
        }
        
        .deadline-badge {
            background: #fff3cd;
            color: #856404;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        .deadline-urgent {
            background: #f8d7da;
            color: #721c24;
        }
        
        @media (max-width: 768px) {
            .jobs-header {
                padding: 60px 0 40px;
            }
            
            .job-meta {
                font-size: 0.8rem;
            }
            
            .job-card {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="jobs-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="display-4 fw-bold mb-3">
                        <i class="fas fa-briefcase me-3"></i>Find Your Dream Job
                    </h1>
                    <p class="lead mb-4">Discover latest job opportunities in Pakistan and abroad</p>
                    
                    <!-- Search Box -->
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <form method="GET" class="d-flex">
                            <input type="text" name="location" class="form-control" 
                                   placeholder="Search by location..." 
                                   value="<?php echo htmlspecialchars($location); ?>">
                            <button type="submit" class="btn btn-success ms-2" style="border-radius: 50px;">
                                <i class="fas fa-search me-2"></i>Search
                            </button>
                        </form>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="stats-card">
                        <div class="stats-number"><?php echo number_format($total_jobs); ?></div>
                        <div class="text-muted">Active Jobs</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container my-5">
        <div class="row">
            <!-- Filters Sidebar -->
            <div class="col-lg-3">
                <div class="filter-card">
                    <h5 class="filter-title">
                        <i class="fas fa-filter me-2"></i>Filters
                    </h5>
                    
                    <!-- Categories -->
                    <div class="filter-section">
                        <h6 class="filter-title">Categories</h6>
                        <a href="jobs.php" class="filter-option <?php echo empty($category_slug) ? 'active' : ''; ?>">
                            All Categories
                        </a>
                        <?php while ($cat = mysqli_fetch_assoc($categories)): ?>
                            <a href="jobs.php?category=<?php echo $cat['slug']; ?>" 
                               class="filter-option <?php echo $category_slug === $cat['slug'] ? 'active' : ''; ?>">
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </a>
                        <?php endwhile; ?>
                    </div>
                    
                    <!-- Job Types -->
                    <div class="filter-section">
                        <h6 class="filter-title">Job Type</h6>
                        <a href="jobs.php" class="filter-option <?php echo empty($job_type) ? 'active' : ''; ?>">
                            All Types
                        </a>
                        <?php mysqli_data_seek($job_types_result, 0); ?>
                        <?php while ($type = mysqli_fetch_assoc($job_types_result)): ?>
                            <a href="jobs.php?type=<?php echo urlencode($type['job_type']); ?>" 
                               class="filter-option <?php echo $job_type === $type['job_type'] ? 'active' : ''; ?>">
                                <?php echo htmlspecialchars($type['job_type']); ?>
                            </a>
                        <?php endwhile; ?>
                    </div>
                    
                    <!-- Locations -->
                    <div class="filter-section">
                        <h6 class="filter-title">Popular Locations</h6>
                        <?php mysqli_data_seek($locations_result, 0); ?>
                        <?php while ($loc = mysqli_fetch_assoc($locations_result)): ?>
                            <a href="jobs.php?location=<?php echo urlencode($loc['job_location']); ?>" 
                               class="filter-option <?php echo $location === $loc['job_location'] ? 'active' : ''; ?>">
                                <?php echo htmlspecialchars($loc['job_location']); ?>
                            </a>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>

            <!-- Jobs List -->
            <div class="col-lg-9">
                <!-- Results Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4>
                        <?php if ($category_slug): ?>
                            <?php 
                            $cat_query = mysqli_query($conn, "SELECT name FROM categories WHERE slug = '$category_slug'");
                            $cat_name = mysqli_fetch_assoc($cat_query)['name'];
                            echo htmlspecialchars($cat_name); 
                            ?>
                        <?php else: ?>
                            All Jobs
                        <?php endif; ?>
                        <span class="badge bg-success ms-2"><?php echo $total_jobs; ?></span>
                    </h4>
                    
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-sort me-2"></i>Sort by
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'latest'])); ?>">Latest</a></li>
                            <li><a class="dropdown-item" href="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'deadline'])); ?>">Deadline</a></li>
                        </ul>
                    </div>
                </div>

                <!-- Jobs List -->
                <?php if (mysqli_num_rows($jobs_result) > 0): ?>
                    <?php while ($job = mysqli_fetch_assoc($jobs_result)): ?>
                        <?php
                        // Calculate days until deadline
                        $days_left = '';
                        $deadline_class = '';
                        if ($job['last_date_to_apply'] && $job['last_date_to_apply'] !== '0000-00-00') {
                            $deadline = new DateTime($job['last_date_to_apply']);
                            $today = new DateTime();
                            $interval = $today->diff($deadline);
                            
                            if ($deadline < $today) {
                                $days_left = 'Expired';
                                $deadline_class = 'deadline-urgent';
                            } elseif ($interval->days <= 3) {
                                $days_left = $interval->days . ' days left';
                                $deadline_class = 'deadline-urgent';
                            } else {
                                $days_left = $interval->days . ' days left';
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
                        
                        <div class="job-card">
                            <div class="row align-items-start">
                                <div class="col-md-8">
                                    <h5 class="job-title">
                                        <a href="job.php?slug=<?php echo $job['slug']; ?>" class="text-decoration-none">
                                            <?php echo htmlspecialchars($job['title']); ?>
                                        </a>
                                    </h5>
                                    
                                    <div class="company-name mb-3">
                                        <i class="fas fa-building me-2"></i>
                                        <?php echo htmlspecialchars($job['company_name']); ?>
                                    </div>
                                    
                                    <div class="job-meta">
                                        <span class="job-meta-badge">
                                            <i class="fas fa-map-marker-alt me-1"></i>
                                            <?php echo htmlspecialchars($job['job_location']); ?>
                                        </span>
                                        
                                        <?php if ($job['job_type']): ?>
                                            <span class="job-meta-badge bg-<?php echo $job_type_color; ?> text-white">
                                                <?php echo htmlspecialchars($job['job_type']); ?>
                                            </span>
                                        <?php endif; ?>
                                        
                                        <?php if ($job['salary']): ?>
                                            <span class="job-meta-badge">
                                                <i class="fas fa-money-bill-wave me-1"></i>
                                                <?php echo htmlspecialchars($job['salary']); ?>
                                            </span>
                                        <?php endif; ?>
                                        
                                        <?php if ($days_left): ?>
                                            <span class="deadline-badge <?php echo $deadline_class; ?>">
                                                <i class="fas fa-clock me-1"></i>
                                                <?php echo $days_left; ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <p class="text-muted mb-3">
                                        <?php echo htmlspecialchars(substr(strip_tags($job['content']), 0, 200)) . '...'; ?>
                                    </p>
                                </div>
                                
                                <div class="col-md-4 text-end">
                                    <div class="mb-3">
                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-1"></i>
                                            Posted <?php echo format_news_date($job['created_at']); ?>
                                        </small>
                                    </div>
                                    
                                    <?php if ($job['apply_url']): ?>
                                        <a href="<?php echo htmlspecialchars($job['apply_url']); ?>" 
                                           target="_blank" 
                                           class="apply-btn">
                                            <i class="fas fa-external-link-alt me-2"></i>Apply Now
                                        </a>
                                    <?php else: ?>
                                        <a href="job.php?slug=<?php echo $job['slug']; ?>#apply" 
                                           class="apply-btn">
                                            <i class="fas fa-paper-plane me-2"></i>Apply Now
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                    
                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <nav aria-label="Jobs pagination">
                            <ul class="pagination">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                    
                <?php else: ?>
                    <div class="no-jobs">
                        <i class="fas fa-search"></i>
                        <h4>No jobs found</h4>
                        <p>Try adjusting your filters or search criteria</p>
                        <a href="jobs.php" class="btn btn-success">
                            <i class="fas fa-redo me-2"></i>Clear Filters
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

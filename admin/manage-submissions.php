<?php
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    redirect('login.php');
}

// Handle submission actions
if (isset($_GET['action']) && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $submission_id = $_GET['id'];
    $action = $_GET['action'];
    
    if ($action === 'delete') {
        // Delete submission
        $delete_query = "DELETE FROM role_applications WHERE id = ?";
        $stmt = mysqli_prepare($conn, $delete_query);
        mysqli_stmt_bind_param($stmt, 'i', $submission_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = "Submission deleted successfully!";
        } else {
            $error = "Error deleting submission!";
        }
    }
}

// Handle filtering
$filter_status = $_GET['filter_status'] ?? 'all';
$filter_role = $_GET['filter_role'] ?? 'all';
$search = $_GET['search'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

// Build base query
$where_conditions = [];
$params = [];
$types = '';

if ($filter_status !== 'all') {
    $where_conditions[] = "ra.status = ?";
    $params[] = $filter_status;
    $types .= 's';
}

if ($filter_role !== 'all') {
    $where_conditions[] = "ra.applied_role = ?";
    $params[] = $filter_role;
    $types .= 's';
}

if (!empty($search)) {
    $where_conditions[] = "(u.name LIKE ? OR u.email LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ss';
}

if (!empty($date_from)) {
    $where_conditions[] = "ra.created_at >= ?";
    $params[] = $date_from . ' 00:00:00';
    $types .= 's';
}

if (!empty($date_to)) {
    $where_conditions[] = "ra.created_at <= ?";
    $params[] = $date_to . ' 23:59:59';
    $types .= 's';
}

// Add evidence type filter to existing filters
$filter_evidence = $_GET['filter_evidence'] ?? 'all';
if ($filter_evidence !== 'all') {
    $where_conditions[] = "ra.evidence_type = ?";
    $params[] = $filter_evidence;
    $types .= 's';
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(' AND ', $where_conditions) : "";

// Get submissions with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

$count_query = "SELECT COUNT(*) as total FROM role_applications ra 
                JOIN users u ON ra.user_id = u.id 
                $where_clause";

if (!empty($params)) {
    $stmt = mysqli_prepare($conn, $count_query);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $count_result = mysqli_stmt_get_result($stmt);
} else {
    $count_result = mysqli_query($conn, $count_query);
}

$total_submissions = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_submissions / $per_page);

// Get submissions
$submissions_query = "SELECT ra.*, u.name, u.email, u.created_at as user_joined,
                     (SELECT COUNT(*) FROM news WHERE author_id = u.id) as user_news_count
                     FROM role_applications ra 
                     JOIN users u ON ra.user_id = u.id 
                     $where_clause
                     ORDER BY ra.created_at DESC 
                     LIMIT $offset, $per_page";

if (!empty($params)) {
    $stmt = mysqli_prepare($conn, $submissions_query);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $submissions_result = mysqli_stmt_get_result($stmt);
} else {
    $submissions_result = mysqli_query($conn, $submissions_query);
}

// Get statistics
$stats_query = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
                SUM(CASE WHEN status = 'withdrawn' THEN 1 ELSE 0 END) as withdrawn
                FROM role_applications";
$stats_result = mysqli_query($conn, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Submissions - PK Live News Admin</title>
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
        .submission-row {
            transition: all 0.3s ease;
        }
        .submission-row:hover {
            background-color: #f8f9fa;
        }
        .status-badge {
            font-size: 0.8rem;
            padding: 0.25rem 0.5rem;
        }
        .stat-card {
            border-radius: 10px;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: white;
        }
        .filter-section {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .submission-preview {
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
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
                            <a class="nav-link" href="manage-users.php">
                                <i class="fas fa-users me-2"></i>Users
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="user_analytics.php">
                                <i class="fas fa-chart-line me-2"></i>User Analytics
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="manage-submissions.php">
                                <i class="fas fa-file-alt me-2"></i>Submissions
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
                        <h1 class="h3 mb-0">Manage Submissions</h1>
                        <small>Review and manage user role applications</small>
                    </div>
                    <div>
                        <a href="manage-users.php#applications" class="btn btn-light">
                            <i class="fas fa-users me-2"></i>View Users
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

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card stat-card">
                            <div class="card-body d-flex align-items-center">
                                <div class="stat-icon bg-primary me-3">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                                <div>
                                    <h3 class="mb-0"><?php echo $stats['total']; ?></h3>
                                    <p class="mb-0 text-muted">Total Submissions</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card">
                            <div class="card-body d-flex align-items-center">
                                <div class="stat-icon bg-warning me-3">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div>
                                    <h3 class="mb-0"><?php echo $stats['pending']; ?></h3>
                                    <p class="mb-0 text-muted">Pending Review</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card">
                            <div class="card-body d-flex align-items-center">
                                <div class="stat-icon bg-success me-3">
                                    <i class="fas fa-check"></i>
                                </div>
                                <div>
                                    <h3 class="mb-0"><?php echo $stats['approved']; ?></h3>
                                    <p class="mb-0 text-muted">Approved</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card">
                            <div class="card-body d-flex align-items-center">
                                <div class="stat-icon bg-danger me-3">
                                    <i class="fas fa-times"></i>
                                </div>
                                <div>
                                    <h3 class="mb-0"><?php echo $stats['rejected']; ?></h3>
                                    <p class="mb-0 text-muted">Rejected</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter Section -->
                <div class="card filter-section mb-4">
                    <div class="card-body">
                        <h5 class="card-title mb-3">
                            <i class="fas fa-filter me-2"></i>Filter Submissions
                        </h5>
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Search</label>
                                <input type="text" name="search" class="form-control" placeholder="Search by name or email..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Status</label>
                                <select name="filter_status" class="form-select">
                                    <option value="all" <?php echo $filter_status === 'all' ? 'selected' : ''; ?>>All Status</option>
                                    <option value="pending" <?php echo $filter_status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="approved" <?php echo $filter_status === 'approved' ? 'selected' : ''; ?>>Approved</option>
                                    <option value="rejected" <?php echo $filter_status === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                    <option value="withdrawn" <?php echo $filter_status === 'withdrawn' ? 'selected' : ''; ?>>Withdrawn</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Role</label>
                                <select name="filter_role" class="form-select">
                                    <option value="all" <?php echo $filter_role === 'all' ? 'selected' : ''; ?>>All Roles</option>
                                    <option value="reporter" <?php echo $filter_role === 'reporter' ? 'selected' : ''; ?>>Reporter</option>
                                    <option value="editor" <?php echo $filter_role === 'editor' ? 'selected' : ''; ?>>Editor</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Evidence Type</label>
                                <select name="filter_evidence" class="form-select">
                                    <option value="all" <?php echo $filter_evidence === 'all' ? 'selected' : ''; ?>>All Evidence</option>
                                    <option value="cv_resume" <?php echo $filter_evidence === 'cv_resume' ? 'selected' : ''; ?>>CV/Resume</option>
                                    <option value="portfolio" <?php echo $filter_evidence === 'portfolio' ? 'selected' : ''; ?>>Portfolio</option>
                                    <option value="certificates" <?php echo $filter_evidence === 'certificates' ? 'selected' : ''; ?>>Certificates</option>
                                    <option value="work_samples" <?php echo $filter_evidence === 'work_samples' ? 'selected' : ''; ?>>Work Samples</option>
                                    <option value="references" <?php echo $filter_evidence === 'references' ? 'selected' : ''; ?>>References</option>
                                    <option value="publications" <?php echo $filter_evidence === 'publications' ? 'selected' : ''; ?>>Publications</option>
                                    <option value="other" <?php echo $filter_evidence === 'other' ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">From Date</label>
                                <input type="date" name="date_from" class="form-control" value="<?php echo htmlspecialchars($date_from); ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">To Date</label>
                                <input type="date" name="date_to" class="form-control" value="<?php echo htmlspecialchars($date_to); ?>">
                            </div>
                            <div class="col-md-1">
                                <label class="form-label">&nbsp;</label>
                                <div>
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Submissions Table -->
                <div class="card">
                    <div class="card-header bg-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Submissions (<?php echo $total_submissions; ?> total)</h5>
                            <div>
                                <button class="btn btn-sm btn-outline-primary" onclick="exportSubmissions()">
                                    <i class="fas fa-download me-1"></i>Export
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Applicant</th>
                                        <th>Role</th>
                                        <th>Evidence Type</th>
                                        <th>Experience</th>
                                        <th>Status</th>
                                        <th>Applied</th>
                                        <th>Reviewed</th>
                                        <th>CV</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($submission = mysqli_fetch_assoc($submissions_result)): ?>
                                        <?php 
                                        $app_data = json_decode($submission['application_data'], true);
                                        ?>
                                        <tr class="submission-row">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px; font-size: 14px;">
                                                        <?php echo strtoupper(substr($submission['name'], 0, 1)); ?>
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold"><?php echo htmlspecialchars($submission['name']); ?></div>
                                                        <small class="text-muted"><?php echo htmlspecialchars($submission['email']); ?></small>
                                                        <br><small class="text-muted">Articles: <?php echo $submission['user_news_count']; ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo $submission['applied_role'] === 'editor' ? 'warning' : 'info'; ?>">
                                                    <?php echo ucfirst($submission['applied_role']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php 
                                                $evidence_types = [
                                                    'cv_resume' => 'CV/Resume',
                                                    'portfolio' => 'Portfolio',
                                                    'certificates' => 'Certificates',
                                                    'work_samples' => 'Work Samples',
                                                    'references' => 'References',
                                                    'publications' => 'Publications',
                                                    'other' => 'Other'
                                                ];
                                                $evidence_type = $submission['evidence_type'] ?? 'cv_resume';
                                                $evidence_color = $evidence_type === 'cv_resume' ? 'primary' : 
                                                               ($evidence_type === 'portfolio' ? 'success' : 
                                                               ($evidence_type === 'certificates' ? 'info' : 
                                                               ($evidence_type === 'work_samples' ? 'warning' : 'secondary')));
                                                ?>
                                                <span class="badge bg-<?php echo $evidence_color; ?>" title="<?php echo htmlspecialchars($submission['evidence_description'] ?? ''); ?>">
                                                    <?php echo $evidence_types[$evidence_type] ?? 'CV/Resume'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="submission-preview" title="<?php echo htmlspecialchars($app_data['experience'] ?? ''); ?>">
                                                    <?php echo htmlspecialchars($app_data['experience'] ?? 'Not provided'); ?>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge status-badge bg-<?php echo $submission['status'] === 'pending' ? 'warning' : ($submission['status'] === 'approved' ? 'success' : ($submission['status'] === 'rejected' ? 'danger' : 'secondary')); ?>">
                                                    <?php echo ucfirst($submission['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('M d, Y H:i', strtotime($submission['created_at'])); ?></td>
                                            <td>
                                                <?php if ($submission['reviewed_at']): ?>
                                                    <?php echo date('M d, Y', strtotime($submission['reviewed_at'])); ?>
                                                <?php else: ?>
                                                    <span class="text-muted">Not reviewed</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($submission['cv_file_name'])): ?>
                                                    <a href="../<?php echo htmlspecialchars($submission['cv_file_path']); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-file-pdf me-1"></i>View CV
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">No CV</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-sm btn-outline-info" onclick="viewSubmissionDetails(<?php echo $submission['id']; ?>)" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    
                                                    <?php if ($submission['status'] === 'pending'): ?>
                                                        <button type="button" class="btn btn-sm btn-success" onclick="quickApprove(<?php echo $submission['id']; ?>, '<?php echo htmlspecialchars($submission['name']); ?>', '<?php echo $submission['applied_role']; ?>')" title="Quick Approve">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-danger" onclick="quickReject(<?php echo $submission['id']; ?>, '<?php echo htmlspecialchars($submission['name']); ?>')" title="Quick Reject">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                    
                                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteSubmission(<?php echo $submission['id']; ?>, '<?php echo htmlspecialchars($submission['name']); ?>')" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>

                        <?php if (mysqli_num_rows($submissions_result) === 0): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                                <h5>No submissions found</h5>
                                <p class="text-muted">No role applications match your criteria</p>
                            </div>
                        <?php endif; ?>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <nav aria-label="Submissions pagination" class="mt-4">
                                <ul class="pagination justify-content-center">
                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&<?php echo http_build_query(array_filter($_GET, function($k) { return $k !== 'page'; }, ARRAY_FILTER_USE_KEY)); ?>">Previous</a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>&<?php echo http_build_query(array_filter($_GET, function($k) { return $k !== 'page'; }, ARRAY_FILTER_USE_KEY)); ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <?php if ($page < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&<?php echo http_build_query(array_filter($_GET, function($k) { return $k !== 'page'; }, ARRAY_FILTER_USE_KEY)); ?>">Next</a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Submission Details Modal -->
    <div class="modal fade" id="submissionDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-file-alt me-2"></i>Submission Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="submissionDetailsContent">
                    <!-- Content will be loaded dynamically -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // View submission details
        function viewSubmissionDetails(submissionId) {
            fetch('api/get_submission_details.php?id=' + submissionId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showSubmissionDetails(data.submission);
                    } else {
                        alert('Error loading submission details: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading submission details. Please try again.');
                });
        }

        // Show submission details in modal
        function showSubmissionDetails(submission) {
            const appData = submission.application_data;
            
            const content = `
                <div class="row">
                    <div class="col-md-6">
                        <h6>Applicant Information</h6>
                        <p><strong>Name:</strong> ${submission.name}</p>
                        <p><strong>Email:</strong> ${submission.email}</p>
                        <p><strong>Applied Role:</strong> <span class="badge bg-${submission.applied_role === 'editor' ? 'warning' : 'info'}">${submission.applied_role}</span></p>
                        <p><strong>Applied:</strong> ${new Date(submission.created_at).toLocaleString()}</p>
                        <p><strong>Status:</strong> <span class="badge bg-${submission.status === 'pending' ? 'warning' : (submission.status === 'approved' ? 'success' : 'danger')}">${submission.status}</span></p>
                        <p><strong>User Articles:</strong> ${submission.user_news_count}</p>
                    </div>
                    <div class="col-md-6">
                        <h6>Evidence Information</h6>
                        <p><strong>Evidence Type:</strong> <span class="badge bg-primary">${getEvidenceTypeLabel(submission.evidence_type)}</span></p>
                        ${submission.evidence_description ? `<p><strong>Evidence Description:</strong><br>${submission.evidence_description}</p>` : ''}
                        <p><strong>Experience:</strong><br>${appData.experience || 'Not provided'}</p>
                        <p><strong>Qualifications:</strong><br>${appData.qualifications || 'Not provided'}</p>
                        <p><strong>Availability:</strong> ${appData.availability || 'Not provided'}</p>
                        ${submission.reviewed_at ? `<p><strong>Reviewed:</strong> ${new Date(submission.reviewed_at).toLocaleString()}</p>` : ''}
                    </div>
                </div>
                
                <hr>
                
                <h6>Reason for Applying</h6>
                <p>${appData.reason || 'Not provided'}</p>
                
                ${appData.samples ? `
                <h6>Sample Work/Portfolio</h6>
                <p>${appData.samples}</p>
                ` : ''}
                
                ${submission.cv_file_name ? `
                <hr>
                <h6>CV/Resume</h6>
                <p>
                    <strong>File:</strong> ${submission.cv_file_name}<br>
                    <strong>Size:</strong> ${formatFileSize(submission.cv_file_size)}<br>
                    <a href="../${submission.cv_file_path}" target="_blank" class="btn btn-sm btn-primary mt-2">
                        <i class="fas fa-download me-1"></i>Download CV
                    </a>
                </p>
                ` : ''}
                
                ${submission.admin_notes ? `
                <hr>
                <h6>Admin Notes</h6>
                <p>${submission.admin_notes}</p>
                ` : ''}
            `;
            
            document.getElementById('submissionDetailsContent').innerHTML = content;
            const modal = new bootstrap.Modal(document.getElementById('submissionDetailsModal'));
            modal.show();
        }

        // Quick approve
        function quickApprove(submissionId, userName, appliedRole) {
            if (confirm(`Approve ${userName}'s application for ${appliedRole} role?`)) {
                window.location.href = `manage-users.php?app_action=approve&app_id=${submissionId}`;
            }
        }

        // Quick reject
        function quickReject(submissionId, userName) {
            const reason = prompt(`Reject ${userName}'s application? Please provide reason:`);
            if (reason && reason.trim()) {
                window.location.href = `manage-users.php?app_action=reject&app_id=${submissionId}&admin_notes=${encodeURIComponent(reason.trim())}`;
            }
        }

        // Delete submission
        function deleteSubmission(submissionId, userName) {
            if (confirm(`Delete ${userName}'s submission? This action cannot be undone.`)) {
                window.location.href = `?action=delete&id=${submissionId}`;
            }
        }

        // Export submissions
        function exportSubmissions() {
            const params = new URLSearchParams(window.location.search);
            params.set('export', 'csv');
            window.open('api/export_submissions.php?' + params.toString());
        }

        // Format file size
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        // Get evidence type label
        function getEvidenceTypeLabel(evidenceType) {
            const evidenceTypes = {
                'cv_resume': 'CV/Resume',
                'portfolio': 'Portfolio',
                'certificates': 'Certificates',
                'work_samples': 'Work Samples',
                'references': 'References',
                'publications': 'Publications',
                'other': 'Other'
            };
            return evidenceTypes[evidenceType] || 'CV/Resume';
        }
    </script>
</body>
</html>

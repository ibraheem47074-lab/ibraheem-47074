<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

// Session management
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is admin
if (!is_admin()) {
    redirect('index.php');
}

// Handle application actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $application_id = $_POST['application_id'] ?? '';
    $admin_notes = $_POST['admin_notes'] ?? '';
    
    if (!empty($application_id)) {
        switch ($action) {
            case 'approve':
                // Get application details
                $app_query = "SELECT * FROM role_applications WHERE id = ?";
                $app_stmt = mysqli_prepare($conn, $app_query);
                mysqli_stmt_bind_param($app_stmt, 'i', $application_id);
                mysqli_stmt_execute($app_stmt);
                $app_result = mysqli_stmt_get_result($app_stmt);
                $application = mysqli_fetch_assoc($app_result);
                
                if ($application) {
                    // Update application status
                    $update_app_query = "UPDATE role_applications SET status = 'approved', admin_notes = ?, reviewed_by = ?, reviewed_at = NOW() WHERE id = ?";
                    $update_app_stmt = mysqli_prepare($conn, $update_app_query);
                    $admin_id = $_SESSION['user_id'];
                    mysqli_stmt_bind_param($update_app_stmt, 'sii', $admin_notes, $admin_id, $application_id);
                    
                    if (mysqli_stmt_execute($update_app_stmt)) {
                        // Update user role and status
                        $update_user_query = "UPDATE users SET role = ?, application_status = 'approved', applied_role = NULL WHERE id = ?";
                        $update_user_stmt = mysqli_prepare($conn, $update_user_query);
                        mysqli_stmt_bind_param($update_user_stmt, 'si', $application['applied_role'], $application['user_id']);
                        mysqli_stmt_execute($update_user_stmt);
                        
                        // Reject other pending applications for this user
                        $reject_others_query = "UPDATE role_applications SET status = 'rejected', admin_notes = 'Auto-rejected due to approval of another application.', reviewed_by = ?, reviewed_at = NOW() WHERE user_id = ? AND id != ? AND status = 'pending'";
                        $reject_others_stmt = mysqli_prepare($conn, $reject_others_query);
                        mysqli_stmt_bind_param($reject_others_stmt, 'iii', $admin_id, $application['user_id'], $application_id);
                        mysqli_stmt_execute($reject_others_stmt);
                        
                        $success = "Application approved and user role updated successfully!";
                    }
                }
                break;
                
            case 'reject':
                // Update application status
                $update_query = "UPDATE role_applications SET status = 'rejected', admin_notes = ?, reviewed_by = ?, reviewed_at = NOW() WHERE id = ?";
                $update_stmt = mysqli_prepare($conn, $update_query);
                $admin_id = $_SESSION['user_id'];
                mysqli_stmt_bind_param($update_stmt, 'sii', $admin_notes, $admin_id, $application_id);
                
                if (mysqli_stmt_execute($update_stmt)) {
                    // Update user application status
                    $update_user_query = "UPDATE users SET application_status = 'rejected', applied_role = NULL WHERE id = (SELECT user_id FROM role_applications WHERE id = ?)";
                    $update_user_stmt = mysqli_prepare($conn, $update_user_query);
                    mysqli_stmt_bind_param($update_user_stmt, 'i', $application_id);
                    mysqli_stmt_execute($update_user_stmt);
                    
                    $success = "Application rejected successfully!";
                }
                break;
        }
    }
}

// Get all applications first - basic query without JOIN
$applications_query = "SELECT * FROM role_applications ORDER BY created_at DESC";
$applications_result = mysqli_query($conn, $applications_query);

// Get user details for each application
$applications_with_users = [];
$reviewer_names = [];

if ($applications_result) {
    while ($app = mysqli_fetch_assoc($applications_result)) {
        // Get user details
        $user_query = "SELECT name, email, role FROM users WHERE id = ?";
        $user_stmt = mysqli_prepare($conn, $user_query);
        mysqli_stmt_bind_param($user_stmt, 'i', $app['user_id']);
        mysqli_stmt_execute($user_stmt);
        $user_result = mysqli_stmt_get_result($user_stmt);
        $user_data = mysqli_fetch_assoc($user_result);
        
        // Get reviewer name if needed
        if ($app['reviewed_by']) {
            $reviewer_query = "SELECT name FROM users WHERE id = ?";
            $reviewer_stmt = mysqli_prepare($conn, $reviewer_query);
            mysqli_stmt_bind_param($reviewer_stmt, 'i', $app['reviewed_by']);
            mysqli_stmt_execute($reviewer_stmt);
            $reviewer_result = mysqli_stmt_get_result($reviewer_stmt);
            $reviewer_data = mysqli_fetch_assoc($reviewer_result);
            $reviewer_names[$app['reviewed_by']] = $reviewer_data['name'] ?? 'Unknown';
        }
        
        // Combine application and user data
        $combined_app = array_merge($app, [
            'name' => $user_data['name'] ?? 'Unknown',
            'email' => $user_data['email'] ?? 'Unknown',
            'current_role' => $user_data['role'] ?? 'user'
        ]);
        
        $applications_with_users[] = $combined_app;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Role Applications - PK Live News</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        .application-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            padding: 25px;
            margin-bottom: 20px;
            border-left: 5px solid #667eea;
            transition: all 0.3s ease;
        }
        .application-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        .status-badge {
            font-size: 0.8rem;
            padding: 5px 12px;
            border-radius: 20px;
            font-weight: 600;
        }
        .status-pending { background: #ffc107; color: #856404; }
        .status-approved { background: #28a745; color: white; }
        .status-rejected { background: #dc3545; color: white; }
        .status-withdrawn { background: #6c757d; color: white; }
        .application-data {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-top: 15px;
        }
        .data-item {
            margin-bottom: 10px;
        }
        .data-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 5px;
        }
        .btn-action {
            padding: 8px 20px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-approve {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            color: white;
        }
        .btn-approve:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40,167,69,0.3);
        }
        .btn-reject {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            border: none;
            color: white;
        }
        .btn-reject:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(220,53,69,0.3);
        }
        .filter-tabs {
            background: white;
            border-radius: 15px;
            padding: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .filter-tab {
            border: none;
            background: transparent;
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .filter-tab.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .filter-tab:hover:not(.active) {
            background: #f8f9fa;
        }
    </style>
</head>
<body>
    <?php include '../includes/admin-header.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>
                <i class="fas fa-user-tie me-2"></i>Role Applications Management
            </h2>
            <div>
                <span class="badge bg-primary me-2">Total: <?php echo count($applications_with_users); ?></span>
                <span class="badge bg-warning me-2">Pending: <?php echo count(array_filter($applications_with_users, fn($app) => $app['status'] === 'pending')); ?></span>
                <span class="badge bg-success">Approved: <?php echo count(array_filter($applications_with_users, fn($app) => $app['status'] === 'approved')); ?></span>
            </div>
        </div>

        <?php if (isset($success)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Filter Tabs -->
        <div class="filter-tabs">
            <div class="d-flex flex-wrap">
                <button class="filter-tab active" onclick="filterApplications('all')">All Applications</button>
                <button class="filter-tab" onclick="filterApplications('pending')">Pending</button>
                <button class="filter-tab" onclick="filterApplications('approved')">Approved</button>
                <button class="filter-tab" onclick="filterApplications('rejected')">Rejected</button>
                <button class="filter-tab" onclick="filterApplications('withdrawn')">Withdrawn</button>
            </div>
        </div>

        <!-- Application Evaluation Criteria -->
        <div class="alert alert-info mb-4">
            <h6><i class="fas fa-clipboard-check me-2"></i>Application Evaluation Criteria</h6>
            <div class="row">
                <div class="col-md-6">
                    <strong>Reporter Requirements:</strong>
                    <ul class="mb-0 small">
                        <li>Minimum 6 months writing experience</li>
                        <li>Journalism qualifications or equivalent</li>
                        <li>Published writing samples</li>
                        <li>Regular availability commitment</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <strong>Editor Requirements:</strong>
                    <ul class="mb-0 small">
                        <li>Minimum 2 years editorial experience</li>
                        <li>Excellent language skills</li>
                        <li>Leadership and content management</li>
                        <li>Quality assurance expertise</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Applications List -->
        <div id="applicationsList">
            <?php if (count($applications_with_users) > 0): ?>
                <?php foreach ($applications_with_users as $application): ?>
                    <div class="application-card" data-status="<?php echo $application['status']; ?>">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="d-flex align-items-center mb-3">
                                    <h5 class="mb-0 me-3">
                                        <?php echo htmlspecialchars($application['name']); ?>
                                    </h5>
                                    <span class="status-badge status-<?php echo $application['status']; ?>">
                                        <?php echo ucfirst($application['status']); ?>
                                    </span>
                                    <span class="badge bg-info ms-2">
                                        <?php echo ucfirst($application['applied_role']); ?>
                                    </span>
                                    <?php if ($application['cv_file_path']): ?>
                                        <span class="badge bg-success ms-2">
                                            <i class="fas fa-file-alt me-1"></i>CV Available
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <small class="text-muted">
                                            <i class="fas fa-envelope me-1"></i>
                                            <?php echo htmlspecialchars($application['email']); ?>
                                        </small>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted">
                                            <i class="fas fa-user me-1"></i>
                                            Current Role: <?php echo ucfirst($application['current_role']); ?>
                                        </small>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-1"></i>
                                            Applied: <?php echo date('M d, Y H:i', strtotime($application['created_at'])); ?>
                                        </small>
                                    </div>
                                    <?php if ($application['reviewed_at']): ?>
                                        <div class="col-md-6">
                                            <small class="text-muted">
                                                <i class="fas fa-check-circle me-1"></i>
                                                Reviewed: <?php echo date('M d, Y H:i', strtotime($application['reviewed_at'])); ?>
                                                by <?php echo htmlspecialchars($reviewer_names[$application['reviewed_by']] ?? 'Admin'); ?>
                                            </small>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <?php if (!empty($application['application_data'])): ?>
                                    <div class="application-data">
                                        <?php 
                                        $app_data = json_decode($application['application_data'], true);
                                        if ($app_data):
                                        ?>
                                            <div class="row">
                                                <?php if (!empty($app_data['experience'])): ?>
                                                    <div class="col-md-6">
                                                        <div class="data-item">
                                                            <div class="data-label">Experience</div>
                                                            <div><?php echo nl2br(htmlspecialchars($app_data['experience'])); ?></div>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($app_data['qualifications'])): ?>
                                                    <div class="col-md-6">
                                                        <div class="data-item">
                                                            <div class="data-label">Qualifications</div>
                                                            <div><?php echo nl2br(htmlspecialchars($app_data['qualifications'])); ?></div>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($app_data['reason'])): ?>
                                                    <div class="col-md-6">
                                                        <div class="data-item">
                                                            <div class="data-label">Reason for Applying</div>
                                                            <div><?php echo nl2br(htmlspecialchars($app_data['reason'])); ?></div>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($app_data['samples'])): ?>
                                                    <div class="col-md-6">
                                                        <div class="data-item">
                                                            <div class="data-label">Samples/Portfolio</div>
                                                            <div><?php echo nl2br(htmlspecialchars($app_data['samples'])); ?></div>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($app_data['availability'])): ?>
                                                    <div class="col-md-6">
                                                        <div class="data-item">
                                                            <div class="data-label">Availability</div>
                                                            <div><?php echo htmlspecialchars(ucwords(str_replace('-', ' ', $app_data['availability']))); ?></div>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($application['cv_file_path']) && file_exists($application['cv_file_path'])): ?>
                                    <div class="mt-3">
                                        <h6><i class="fas fa-file-alt me-2"></i>CV/Resume</h6>
                                        <div class="d-flex align-items-center">
                                            <a href="../download_cv.php?id=<?php echo $application['id']; ?>" 
                                               class="btn btn-sm btn-outline-primary me-2" target="_blank">
                                                <i class="fas fa-download me-1"></i>Download CV
                                            </a>
                                            <small class="text-muted">
                                                <?php echo htmlspecialchars($application['cv_file_name']); ?>
                                                (<?php echo number_format($application['cv_file_size'] / 1024, 2); ?> KB)
                                            </small>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($application['admin_notes'])): ?>
                                    <div class="alert alert-info mt-3 mb-0">
                                        <strong>Admin Notes:</strong> <?php echo nl2br(htmlspecialchars($application['admin_notes'])); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="text-end">
                                    <?php if ($application['status'] === 'pending'): ?>
                                        <!-- Quick Actions -->
                                        <div class="mb-3">
                                            <button type="button" class="btn btn-action btn-approve btn-sm w-100 mb-2" onclick="showApproveModal(<?php echo $application['id']; ?>, '<?php echo $application['applied_role']; ?>')">
                                                <i class="fas fa-check me-1"></i>Review & Approve
                                            </button>
                                            
                                            <button type="button" class="btn btn-action btn-reject btn-sm w-100" onclick="showRejectModal(<?php echo $application['id']; ?>)">
                                                <i class="fas fa-times me-1"></i>Review & Reject
                                            </button>
                                        </div>
                                        
                                        <!-- Evaluation Score -->
                                        <div class="mb-3">
                                            <label class="form-label small">Quick Evaluation:</label>
                                            <div class="btn-group w-100" role="group">
                                                <button type="button" class="btn btn-outline-success btn-sm" onclick="quickEvaluate(<?php echo $application['id']; ?>, 'strong')">
                                                    Strong
                                                </button>
                                                <button type="button" class="btn btn-outline-warning btn-sm" onclick="quickEvaluate(<?php echo $application['id']; ?>, 'moderate')">
                                                    Moderate
                                                </button>
                                                <button type="button" class="btn btn-outline-danger btn-sm" onclick="quickEvaluate(<?php echo $application['id']; ?>, 'weak')">
                                                    Weak
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <!-- Request More Info -->
                                        <button type="button" class="btn btn-outline-info btn-sm w-100" onclick="requestMoreInfo(<?php echo $application['id']; ?>)">
                                            <i class="fas fa-info-circle me-1"></i>Request More Info
                                        </button>
                                    <?php else: ?>
                                        <div class="text-muted">
                                            <small>
                                                <?php if ($application['status'] === 'approved'): ?>
                                                    <i class="fas fa-check-circle text-success me-1"></i>
                                                    Approved by <?php echo htmlspecialchars($reviewer_names[$application['reviewed_by']] ?? 'Admin'); ?>
                                                <?php elseif ($application['status'] === 'rejected'): ?>
                                                    <i class="fas fa-times-circle text-danger me-1"></i>
                                                    Rejected by <?php echo htmlspecialchars($reviewer_names[$application['reviewed_by']] ?? 'Admin'); ?>
                                                <?php endif; ?>
                                            </small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted">No applications found</h4>
                    <p class="text-muted">There are no role applications to review at this time.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Enhanced Approval Modal -->
    <div class="modal fade" id="approveModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-check-circle me-2"></i>Approve Application
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="approveForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="approve">
                        <input type="hidden" name="application_id" id="approve_application_id">
                        
                        <!-- Application Role Display -->
                        <div class="alert alert-info mb-3">
                            <strong>Role:</strong> <span id="approve_role_display"></span>
                        </div>
                        
                        <!-- Criteria Evaluation -->
                        <h6 class="mb-3">Criteria Evaluation</h6>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="criteria_experience" required>
                                    <label class="form-check-label" for="criteria_experience">
                                        <strong>Experience Requirements Met</strong>
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="criteria_qualifications" required>
                                    <label class="form-check-label" for="criteria_qualifications">
                                        <strong>Qualifications Sufficient</strong>
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="criteria_samples" required>
                                    <label class="form-check-label" for="criteria_samples">
                                        <strong>Sample Work Quality</strong>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="criteria_availability" required>
                                    <label class="form-check-label" for="criteria_availability">
                                        <strong>Availability Suitable</strong>
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="criteria_communication" required>
                                    <label class="form-check-label" for="criteria_communication">
                                        <strong>Communication Skills</strong>
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="criteria_overview" required>
                                    <label class="form-check-label" for="criteria_overview">
                                        <strong>Overall Assessment Positive</strong>
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Approval Notes -->
                        <div class="mb-3">
                            <label class="form-label">Approval Notes</label>
                            <textarea class="form-control" name="admin_notes" rows="3"
                                      placeholder="Add any notes about this approval (optional)..."></textarea>
                        </div>
                        
                        <!-- Probation Period -->
                        <div class="mb-3">
                            <label class="form-label">Probation Period</label>
                            <select class="form-select" name="probation_period">
                                <option value="none">No Probation</option>
                                <option value="1_month">1 Month</option>
                                <option value="3_months" selected>3 Months</option>
                                <option value="6_months">6 Months</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check me-2"></i>Approve Application
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Enhanced Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-times-circle me-2"></i>Reject Application
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="rejectForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="reject">
                        <input type="hidden" name="application_id" id="reject_application_id">
                        
                        <!-- Rejection Reasons -->
                        <div class="mb-3">
                            <label class="form-label">Rejection Reason *</label>
                            <select class="form-select" name="rejection_reason" required>
                                <option value="">Select primary reason</option>
                                <option value="insufficient_experience">Insufficient Experience</option>
                                <option value="lacking_qualifications">Lacking Required Qualifications</option>
                                <option value="poor_sample_quality">Poor Sample Work Quality</option>
                                <option value="limited_availability">Limited Availability</option>
                                <option value="communication_issues">Communication Issues</option>
                                <option value="other">Other (Specify Below)</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Detailed Explanation *</label>
                            <textarea class="form-control" name="admin_notes" rows="4" required
                                      placeholder="Please provide detailed explanation for rejecting this application..."></textarea>
                        </div>
                        
                        <!-- Follow-up Options -->
                        <div class="mb-3">
                            <label class="form-label">Follow-up Action</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="suggest_improvement" id="suggest_improvement">
                                <label class="form-check-label" for="suggest_improvement">
                                    Suggest improvements for future application
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="invite_alternative" id="invite_alternative">
                                <label class="form-check-label" for="invite_alternative">
                                    Invite to apply for alternative role
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-times me-2"></i>Reject Application
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Request More Info Modal -->
    <div class="modal fade" id="requestInfoModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-info-circle me-2"></i>Request Additional Information
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="requestInfoForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="request_info">
                        <input type="hidden" name="application_id" id="request_info_application_id">
                        
                        <div class="mb-3">
                            <label class="form-label">Information Requested *</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="request_cv" id="request_cv">
                                <label class="form-check-label" for="request_cv">
                                    Request updated CV/Resume
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="request_samples" id="request_samples">
                                <label class="form-check-label" for="request_samples">
                                    Request additional writing samples
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="request_references" id="request_references">
                                <label class="form-check-label" for="request_references">
                                    Request professional references
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="request_portfolio" id="request_portfolio">
                                <label class="form-check-label" for="request_portfolio">
                                    Request portfolio link
                                </label>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Custom Request *</label>
                            <textarea class="form-control" name="custom_request" rows="3" required
                                      placeholder="Specify what additional information you need..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-info">
                            <i class="fas fa-paper-plane me-2"></i>Send Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include '../includes/admin-footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function filterApplications(status) {
            const cards = document.querySelectorAll('.application-card');
            const tabs = document.querySelectorAll('.filter-tab');
            
            // Update active tab
            tabs.forEach(tab => tab.classList.remove('active'));
            event.target.classList.add('active');
            
            // Filter cards
            cards.forEach(card => {
                if (status === 'all' || card.dataset.status === status) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }
        
        function showRejectModal(applicationId) {
            document.getElementById('reject_application_id').value = applicationId;
            const modal = new bootstrap.Modal(document.getElementById('rejectModal'));
            modal.show();
        }
        
        function showApproveModal(applicationId, role) {
            document.getElementById('approve_application_id').value = applicationId;
            document.getElementById('approve_role_display').textContent = role.charAt(0).toUpperCase() + role.slice(1);
            
            // Reset checkboxes
            document.querySelectorAll('#approveModal input[type="checkbox"]').forEach(cb => cb.checked = false);
            
            const modal = new bootstrap.Modal(document.getElementById('approveModal'));
            modal.show();
        }
        
        function requestMoreInfo(applicationId) {
            document.getElementById('request_info_application_id').value = applicationId;
            const modal = new bootstrap.Modal(document.getElementById('requestInfoModal'));
            modal.show();
        }
        
        function quickEvaluate(applicationId, evaluation) {
            const notes = {
                'strong': 'Strong candidate - meets all criteria excellently',
                'moderate': 'Moderate candidate - meets most criteria',
                'weak': 'Weak candidate - lacks key requirements'
            };
            
            // Store evaluation in session or send to server
            fetch('api/store_evaluation.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    application_id: applicationId,
                    evaluation: evaluation,
                    notes: notes[evaluation]
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update UI to show evaluation
                    const card = document.querySelector(`.application-card:has([onclick*="${applicationId}"])`);
                    if (card) {
                        const badge = document.createElement('span');
                        badge.className = `badge bg-${evaluation === 'strong' ? 'success' : evaluation === 'moderate' ? 'warning' : 'secondary'} ms-2`;
                        badge.textContent = evaluation.charAt(0).toUpperCase() + evaluation.slice(1);
                        card.querySelector('.d-flex.align-items-center').appendChild(badge);
                    }
                }
            })
            .catch(error => console.error('Error storing evaluation:', error));
        }
    </script>
</body>
</html>

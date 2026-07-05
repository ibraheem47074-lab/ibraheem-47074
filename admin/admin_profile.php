<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

// Session management
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    redirect('index.php');
}

$user_id = $_SESSION['user_id'];

// Get admin user details
$user_query = "SELECT u.*, ar.role_name as admin_role_name, ar.role_level as admin_role_level, ar.permissions as role_permissions 
               FROM users u 
               LEFT JOIN user_admin_roles uar ON u.id = uar.user_id 
               LEFT JOIN admin_roles ar ON uar.role_id = ar.id 
               WHERE u.id = ?";
$user_stmt = mysqli_prepare($conn, $user_query);
mysqli_stmt_bind_param($user_stmt, 'i', $user_id);
mysqli_stmt_execute($user_stmt);
$user_result = mysqli_stmt_get_result($user_stmt);
$user = mysqli_fetch_assoc($user_result);

// Get all admin roles
$roles_query = "SELECT * FROM admin_roles ORDER BY role_level DESC";
$roles_result = mysqli_query($conn, $roles_query);

// Get admin statistics
$stats = [
    'total_users' => 0,
    'pending_applications' => 0,
    'total_articles' => 0,
    'pending_reviews' => 0
];

// Total users
$users_query = "SELECT COUNT(*) as count FROM users";
$users_result = mysqli_query($conn, $users_query);
if ($users_result) {
    $stats['total_users'] = $users_result->fetch_assoc()['count'];
}

// Pending applications
$applications_query = "SELECT COUNT(*) as count FROM role_applications WHERE status = 'pending'";
$applications_result = mysqli_query($conn, $applications_query);
if ($applications_result) {
    $stats['pending_applications'] = $applications_result->fetch_assoc()['count'];
}

// Total articles
$articles_query = "SELECT COUNT(*) as count FROM news";
$articles_result = mysqli_query($conn, $articles_query);
if ($articles_result) {
    $stats['total_articles'] = $articles_result->fetch_assoc()['count'];
}

// Recent applications for admin dashboard
$recent_applications_query = "SELECT ra.*, u.name, u.email 
                              FROM role_applications ra 
                              LEFT JOIN users u ON ra.user_id = u.id 
                              WHERE ra.status = 'pending' 
                              ORDER BY ra.created_at DESC LIMIT 5";
$recent_applications_result = mysqli_query($conn, $recent_applications_query);

// Handle role assignment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_role'])) {
    $target_user_id = $_POST['target_user_id'];
    $role_id = $_POST['role_id'];
    
    // Check if admin has permission to assign roles
    if (has_permission('user_role_assign')) {
        // Remove existing admin roles for this user
        $delete_query = "DELETE FROM user_admin_roles WHERE user_id = ?";
        $delete_stmt = mysqli_prepare($conn, $delete_query);
        mysqli_stmt_bind_param($delete_stmt, 'i', $target_user_id);
        mysqli_stmt_execute($delete_stmt);
        
        // Assign new role
        $insert_query = "INSERT INTO user_admin_roles (user_id, role_id, assigned_by) VALUES (?, ?, ?)";
        $insert_stmt = mysqli_prepare($conn, $insert_query);
        mysqli_stmt_bind_param($insert_stmt, 'iii', $target_user_id, $role_id, $user_id);
        
        if (mysqli_stmt_execute($insert_stmt)) {
            // Update user admin level
            $role_query = "SELECT role_level FROM admin_roles WHERE id = ?";
            $role_stmt = mysqli_prepare($conn, $role_query);
            mysqli_stmt_bind_param($role_stmt, 'i', $role_id);
            mysqli_stmt_execute($role_stmt);
            $role_result = mysqli_stmt_get_result($role_stmt);
            $role_data = mysqli_fetch_assoc($role_result);
            
            $update_user_query = "UPDATE users SET admin_level = ? WHERE id = ?";
            $update_user_stmt = mysqli_prepare($conn, $update_user_query);
            mysqli_stmt_bind_param($update_user_stmt, 'ii', $role_data['role_level'], $target_user_id);
            mysqli_stmt_execute($update_user_stmt);
            
            $success = "Role assigned successfully!";
        } else {
            $error = "Error assigning role: " . mysqli_error($conn);
        }
    } else {
        $error = "You don't have permission to assign roles.";
    }
}

// Helper function to check permissions
function has_permission($permission) {
    global $user;
    
    if (!$user) return false;
    
    // Super admin has all permissions
    if ($user['admin_level'] >= 100) return true;
    
    // Check role permissions
    if (!empty($user['role_permissions'])) {
        $permissions = json_decode($user['role_permissions'], true);
        return in_array('all', $permissions) || in_array($permission, $permissions);
    }
    
    return false;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile - PK Live News</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        .admin-profile-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            padding: 30px;
            color: white;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }
        .admin-stats-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            border-left: 5px solid #667eea;
        }
        .admin-stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            color: transparent;
        }
        .permission-badge {
            font-size: 0.8rem;
            padding: 5px 12px;
            border-radius: 20px;
            background: rgba(255,255,255,0.2);
            color: white;
            margin: 2px;
            display: inline-block;
        }
        .role-badge {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 8px 16px;
            border-radius: 25px;
            font-weight: 600;
            display: inline-block;
        }
        .application-card {
            background: white;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            border-left: 4px solid #ffc107;
            transition: all 0.3s ease;
        }
        .application-card:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .admin-section-title {
            color: #667eea;
            font-weight: 600;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        .admin-section-title i {
            margin-right: 10px;
        }
        .criteria-section {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .criteria-item {
            background: white;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            border-left: 4px solid #667eea;
        }
        .criteria-title {
            font-weight: 600;
            color: #495057;
            margin-bottom: 10px;
        }
        .criteria-description {
            color: #6c757d;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <?php include '../includes/admin-header.php'; ?>

    <div class="container-fluid mt-4">
        <!-- Admin Profile Header -->
        <div class="admin-profile-card">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-3">
                        <i class="fas fa-user-shield me-3"></i>Admin Profile
                    </h1>
                    <h3 class="mb-2"><?php echo htmlspecialchars($user['name']); ?></h3>
                    <p class="mb-2"><i class="fas fa-envelope me-2"></i><?php echo htmlspecialchars($user['email']); ?></p>
                    <div class="mt-3">
                        <span class="role-badge">
                            <i class="fas fa-crown me-2"></i>
                            <?php echo $user['admin_role_name'] ? htmlspecialchars($user['admin_role_name']) : 'Administrator'; ?>
                        </span>
                        <span class="badge bg-light text-dark ms-2">Level <?php echo $user['admin_level']; ?></span>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <div class="mt-3">
                        <small>Admin Since</small>
                        <h5><?php echo date('Y', strtotime($user['created_at'])); ?></h5>
                    </div>
                </div>
            </div>
        </div>

        <?php if (isset($success)): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Admin Statistics -->
            <div class="col-lg-8">
                <h4 class="admin-section-title">
                    <i class="fas fa-chart-line"></i>Admin Dashboard Overview
                </h4>
                
                <div class="row mb-4">
                    <div class="col-md-6 mb-3">
                        <div class="admin-stats-card">
                            <div class="stat-number"><?php echo number_format($stats['total_users']); ?></div>
                            <div class="text-muted">Total Users</div>
                            <small class="text-success">
                                <i class="fas fa-users me-1"></i>Active community members
                            </small>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="admin-stats-card">
                            <div class="stat-number"><?php echo number_format($stats['pending_applications']); ?></div>
                            <div class="text-muted">Pending Applications</div>
                            <small class="text-warning">
                                <i class="fas fa-hourglass-half me-1"></i>Awaiting review
                            </small>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="admin-stats-card">
                            <div class="stat-number"><?php echo number_format($stats['total_articles']); ?></div>
                            <div class="text-muted">Total Articles</div>
                            <small class="text-info">
                                <i class="fas fa-newspaper me-1"></i>Published content
                            </small>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="admin-stats-card">
                            <div class="stat-number"><?php echo mysqli_num_rows($roles_result); ?></div>
                            <div class="text-muted">Admin Roles</div>
                            <small class="text-primary">
                                <i class="fas fa-user-tie me-1"></i>Role hierarchy
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Recent Applications -->
                <h4 class="admin-section-title">
                    <i class="fas fa-user-clock"></i>Recent Role Applications
                </h4>
                
                <?php if (mysqli_num_rows($recent_applications_result) > 0): ?>
                    <?php while ($app = mysqli_fetch_assoc($recent_applications_result)): ?>
                        <div class="application-card">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1"><?php echo htmlspecialchars($app['name']); ?></h6>
                                    <small class="text-muted"><?php echo htmlspecialchars($app['email']); ?></small>
                                    <div class="mt-2">
                                        <span class="badge bg-warning">Applying for <?php echo ucfirst($app['applied_role']); ?></span>
                                        <small class="text-muted ms-2">
                                            <i class="fas fa-calendar me-1"></i>
                                            <?php echo date('M d, Y', strtotime($app['created_at'])); ?>
                                        </small>
                                    </div>
                                </div>
                                <a href="manage_role_applications.php" class="btn btn-sm btn-primary">
                                    <i class="fas fa-eye me-1"></i>Review
                                </a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="text-center py-4 bg-light rounded">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No pending applications</h5>
                        <p class="text-muted">All role applications have been reviewed.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Admin Permissions & Criteria -->
            <div class="col-lg-4">
                <!-- Role Criteria Section -->
                <h4 class="admin-section-title">
                    <i class="fas fa-clipboard-check"></i>Role Approval Criteria
                </h4>
                
                <div class="criteria-section">
                    <h6 class="mb-3 text-primary">
                        <i class="fas fa-newspaper me-2"></i>Reporter Criteria
                    </h6>
                    <div class="criteria-item">
                        <div class="criteria-title">Writing Experience</div>
                        <div class="criteria-description">
                            Minimum 6 months of writing experience with published samples
                        </div>
                    </div>
                    <div class="criteria-item">
                        <div class="criteria-title">Journalism Qualifications</div>
                        <div class="criteria-description">
                            Degree in journalism or related field, or equivalent experience
                        </div>
                    </div>
                    <div class="criteria-item">
                        <div class="criteria-title">Content Quality</div>
                        <div class="criteria-description">
                            Demonstrate ability to write clear, engaging, and factual content
                        </div>
                    </div>
                    <div class="criteria-item">
                        <div class="criteria-title">Availability</div>
                        <div class="criteria-description">
                            Regular contribution schedule and timely response to feedback
                        </div>
                    </div>
                </div>

                <div class="criteria-section">
                    <h6 class="mb-3 text-success">
                        <i class="fas fa-user-edit me-2"></i>Editor Criteria
                    </h6>
                    <div class="criteria-item">
                        <div class="criteria-title">Editorial Experience</div>
                        <div class="criteria-description">
                            Minimum 2 years in editorial or content management role
                        </div>
                    </div>
                    <div class="criteria-item">
                        <div class="criteria-title">Language Skills</div>
                        <div class="criteria-description">
                            Excellent grammar, spelling, and content structure knowledge
                        </div>
                    </div>
                    <div class="criteria-item">
                        <div class="criteria-title">Leadership</div>
                        <div class="criteria-description">
                            Ability to guide writers and maintain content standards
                        </div>
                    </div>
                    <div class="criteria-item">
                        <div class="criteria-title">Quality Assurance</div>
                        <div class="criteria-description">
                            Strong fact-checking and content verification skills
                        </div>
                    </div>
                </div>

                <!-- Current Permissions -->
                <h4 class="admin-section-title mt-4">
                    <i class="fas fa-key"></i>Your Permissions
                </h4>
                
                <div class="bg-white rounded p-3">
                    <?php
                    $all_permissions = [
                        'dashboard_view' => 'View Dashboard',
                        'user_manage' => 'Manage Users',
                        'user_role_assign' => 'Assign Roles',
                        'content_create' => 'Create Content',
                        'content_edit' => 'Edit Content',
                        'content_publish' => 'Publish Content',
                        'content_delete' => 'Delete Content',
                        'content_moderate' => 'Moderate Content',
                        'role_applications_review' => 'Review Applications',
                        'analytics_view' => 'View Analytics',
                        'settings_manage' => 'Manage Settings'
                    ];
                    
                    foreach ($all_permissions as $key => $name):
                        if (has_permission($key)):
                    ?>
                        <span class="permission-badge">
                            <i class="fas fa-check me-1"></i><?php echo $name; ?>
                        </span>
                    <?php 
                        endif;
                    endforeach; 
                    ?>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/admin-footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

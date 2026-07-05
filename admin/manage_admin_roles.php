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

// Handle role assignment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_admin_role'])) {
    $target_user_id = $_POST['target_user_id'];
    $role_id = $_POST['admin_role_id'];
    
    // Only super admin can assign admin roles
    if (isset($_SESSION['admin_level']) && $_SESSION['admin_level'] >= 100) {
        // Remove existing admin roles for this user
        $delete_query = "DELETE FROM user_admin_roles WHERE user_id = ?";
        $delete_stmt = mysqli_prepare($conn, $delete_query);
        mysqli_stmt_bind_param($delete_stmt, 'i', $target_user_id);
        mysqli_stmt_execute($delete_stmt);
        
        // Assign new admin role
        $insert_query = "INSERT INTO user_admin_roles (user_id, role_id, assigned_by) VALUES (?, ?, ?)";
        $insert_stmt = mysqli_prepare($conn, $insert_query);
        $admin_id = $_SESSION['user_id'];
        mysqli_stmt_bind_param($insert_stmt, 'iii', $target_user_id, $role_id, $admin_id);
        
        if (mysqli_stmt_execute($insert_stmt)) {
            // Update user admin level and role
            $role_query = "SELECT role_level, role_name FROM admin_roles WHERE id = ?";
            $role_stmt = mysqli_prepare($conn, $role_query);
            mysqli_stmt_bind_param($role_stmt, 'i', $role_id);
            mysqli_stmt_execute($role_stmt);
            $role_result = mysqli_stmt_get_result($role_stmt);
            $role_data = mysqli_fetch_assoc($role_result);
            
            $update_user_query = "UPDATE users SET admin_level = ?, role = ? WHERE id = ?";
            $update_user_stmt = mysqli_prepare($conn, $update_user_query);
            mysqli_stmt_bind_param($update_user_stmt, 'isi', $role_data['role_level'], $role_data['role_name'], $target_user_id);
            mysqli_stmt_execute($update_user_stmt);
            
            $success = "Admin role assigned successfully!";
        } else {
            $error = "Error assigning admin role: " . mysqli_error($conn);
        }
    } else {
        $error = "Only Super Admin can assign admin roles.";
    }
}

// Get all admin roles
$roles_query = "SELECT * FROM admin_roles ORDER BY role_level DESC";
$roles_result = mysqli_query($conn, $roles_query);

// Get users with admin roles
$admin_users_query = "SELECT u.*, ar.role_name, ar.role_level, ar.permissions, uar.assigned_at, 
                             assigner.name as assigned_by_name
                      FROM users u 
                      LEFT JOIN user_admin_roles uar ON u.id = uar.user_id 
                      LEFT JOIN admin_roles ar ON uar.role_id = ar.id 
                      LEFT JOIN users assigner ON uar.assigned_by = assigner.id 
                      WHERE u.admin_level > 0 
                      ORDER BY u.admin_level DESC, u.name ASC";
$admin_users_result = mysqli_query($conn, $admin_users_query);

// Get users who can be assigned admin roles
$potential_users_query = "SELECT id, name, email, role FROM users WHERE admin_level = 0 ORDER BY name ASC";
$potential_users_result = mysqli_query($conn, $potential_users_query);

// Get enhanced editor performance metrics for graphs
$editor_performance_query = "SELECT 
                                u.id as user_id,
                                u.name,
                                u.email,
                                COUNT(DISTINCT a.id) as total_articles,
                                COALESCE(SUM(a.views), 0) as total_views,
                                COALESCE(SUM(a.likes), 0) as total_likes,
                                0 as total_comments,
                                COALESCE(AVG(a.views), 0) as avg_views_per_article,
                                COUNT(DISTINCT CASE WHEN a.status = 'published' AND a.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN a.id END) as articles_this_month,
                                COUNT(DISTINCT CASE WHEN a.status = 'published' AND a.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN a.id END) as articles_this_week,
                                COUNT(DISTINCT CASE WHEN a.status = 'published' AND a.created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY) THEN a.id END) as articles_today,
                                COALESCE(MAX(a.views), 0) as best_article_views,
                                COUNT(DISTINCT CASE WHEN a.status = 'published' AND a.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN a.id END) as monthly_published,
                                COUNT(DISTINCT CASE WHEN a.status = 'draft' THEN a.id END) as draft_articles
                             FROM users u 
                             LEFT JOIN news a ON u.id = a.author_id 
                             WHERE u.role = 'Editor' OR u.admin_level = 60 OR u.role = 'reporter'
                             GROUP BY u.id, u.name, u.email
                             ORDER BY total_views DESC";
$editor_performance_result = mysqli_query($conn, $editor_performance_query);

// Store performance data in associative array
$editor_performance = [];
if ($editor_performance_result) {
    while ($perf = mysqli_fetch_assoc($editor_performance_result)) {
        $editor_performance[$perf['user_id']] = $perf;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Admin Roles - PK Live News</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .admin-role-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border-left: 5px solid #667eea;
            transition: all 0.3s ease;
        }
        .admin-role-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        /* Editor Role Specific Styling */
        .editor-role-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9ff 100%);
            border-left: 5px solid #ff6b35;
            position: relative;
            overflow: hidden;
        }
        .editor-role-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 150px;
            height: 150px;
            background: linear-gradient(135deg, rgba(255,107,53,0.1) 0%, rgba(255,193,7,0.1) 100%);
            border-radius: 0 0 0 150px;
        }
        .editor-role-card .card-title {
            color: #ff6b35;
            font-weight: 700;
            position: relative;
            z-index: 1;
        }
        .editor-role-card .role-level-badge {
            background: linear-gradient(135deg, #ff6b35 0%, #ffc107 100%);
            color: white;
            position: relative;
            z-index: 1;
        }
        .editor-role-card .permission-tag {
            background: linear-gradient(135deg, rgba(255,107,53,0.1) 0%, rgba(255,193,7,0.1) 100%);
            color: #ff6b35;
            border: 1px solid rgba(255,107,53,0.3);
            font-weight: 600;
        }
        
        /* Editor User Card Styling */
        .editor-user-card {
            background: linear-gradient(135deg, #ffffff 0%, #fff8f5 100%);
            border-left: 4px solid #ff6b35;
            position: relative;
        }
        .editor-user-card::after {
            content: 'Editor';
            position: absolute;
            top: 10px;
            right: 10px;
            background: linear-gradient(135deg, #ff6b35 0%, #ffc107 100%);
            color: white;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .role-level-badge {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: 600;
        }
        .permission-tag {
            display: inline-block;
            background: #f8f9fa;
            color: #495057;
            padding: 3px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            margin: 2px;
            border: 1px solid #dee2e6;
        }
        .admin-user-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            border-left: 4px solid #28a745;
            transition: all 0.3s ease;
        }
        .admin-user-card:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .super-admin-only {
            background: linear-gradient(135deg, #ffc107 0%, #ff6b6b 100%);
            color: white;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        /* Editor Permissions Highlight */
        .editor-permission-highlight {
            background: linear-gradient(135deg, rgba(255,107,53,0.1) 0%, rgba(255,193,7,0.1) 100%);
            border: 1px solid rgba(255,107,53,0.2);
            border-radius: 8px;
            padding: 10px;
            margin-top: 10px;
        }
        .editor-permission-highlight .permission-tag {
            background: white;
            color: #ff6b35;
            border-color: #ff6b35;
            margin: 3px;
        }
        
        /* Criteria Section Styling */
        .criteria-section {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            height: 100%;
        }
        .criteria-item {
            background: white;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            border-left: 4px solid #667eea;
            transition: all 0.3s ease;
        }
        .criteria-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .criteria-title {
            font-weight: 600;
            color: #495057;
            margin-bottom: 10px;
            font-size: 0.95rem;
        }
        .criteria-description {
            color: #6c757d;
            font-size: 0.85rem;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <?php include '../includes/admin-header.php'; ?>

    <div class="container-fluid mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>
                <i class="fas fa-user-shield me-2"></i>Admin Role Management
            </h2>
            <div>
                <span class="badge bg-primary me-2">Total Admins: <?php echo mysqli_num_rows($admin_users_result); ?></span>
                <span class="badge bg-info">Roles Available: <?php echo mysqli_num_rows($roles_result); ?></span>
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

        <?php if (isset($_SESSION['admin_level']) && $_SESSION['admin_level'] >= 100): ?>
            <!-- Super Admin Section -->
            <div class="super-admin-only">
                <h5><i class="fas fa-crown me-2"></i>Super Admin Privileges</h5>
                <p class="mb-0">As a Super Admin, you can assign admin roles to users and manage the complete admin hierarchy.</p>
            </div>

            <!-- Assign Admin Role -->
            <div class="admin-role-card">
                <h4 class="mb-4">
                    <i class="fas fa-user-plus me-2"></i>Assign Admin Role
                </h4>
                
                <form method="POST" class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label">Select User</label>
                        <select class="form-select" name="target_user_id" required>
                            <option value="">Choose a user...</option>
                            <?php while ($user = mysqli_fetch_assoc($potential_users_result)): ?>
                                <option value="<?php echo $user['id']; ?>">
                                    <?php echo htmlspecialchars($user['name']); ?> (<?php echo htmlspecialchars($user['email']); ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Admin Role</label>
                        <select class="form-select" name="admin_role_id" required>
                            <option value="">Choose a role...</option>
                            <?php mysqli_data_seek($roles_result, 0); ?>
                            <?php while ($role = mysqli_fetch_assoc($roles_result)): ?>
                                <option value="<?php echo $role['id']; ?>">
                                    <?php echo htmlspecialchars($role['role_name']); ?> (Level <?php echo $role['role_level']; ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" name="assign_admin_role" class="btn btn-primary w-100">
                            <i class="fas fa-user-tag me-2"></i>Assign
                        </button>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <!-- Available Admin Roles -->
        <div class="admin-role-card">
            <h4 class="mb-4">
                <i class="fas fa-layer-group me-2"></i>Available Admin Roles
            </h4>
            
            <div class="row">
                <?php mysqli_data_seek($roles_result, 0); ?>
                <?php while ($role = mysqli_fetch_assoc($roles_result)): ?>
                    <div class="col-md-6 mb-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <?php if ($role['role_name'] === 'Editor'): ?>
                                    <h6 class="card-title mb-3">Editor</h6>
                                    <div class="mb-2">
                                        <strong>Permissions:</strong>
                                        <div class="mt-1">
                                            <?php 
                                            $permissions = json_decode($role['permissions'], true);
                                            $editor_permissions = [
                                                'news_articles_manage' => 'Manage News Articles',
                                                'content_edit' => 'Edit and Publish News',
                                                'comments_manage' => 'Manage Comments',
                                                'polls_manage' => 'Manage Polls',
                                                'analytics_view' => 'View Statistics'
                                            ];
                                            
                                            foreach ($permissions as $perm) {
                                                if (isset($editor_permissions[$perm])) {
                                                    echo '<span class="permission-tag">' . $editor_permissions[$perm] . '</span>';
                                                }
                                            }
                                            ?>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <h6 class="card-title">
                                            <?php echo htmlspecialchars($role['role_name']); ?>
                                        </h6>
                                        <span class="role-level-badge">Level <?php echo $role['role_level']; ?></span>
                                    </div>
                                    
                                    <p class="card-text small text-muted">
                                        <?php echo htmlspecialchars($role['description']); ?>
                                    </p>
                                    
                                    <div class="mb-2">
                                        <strong>Permissions:</strong>
                                        <div class="mt-1">
                                            <?php 
                                            $permissions = json_decode($role['permissions'], true);
                                            if ($permissions && in_array('all', $permissions)) {
                                                echo '<span class="permission-tag">Full Access</span>';
                                            } else {
                                                $permission_names = [
                                                    'dashboard_view' => 'Dashboard',
                                                    'user_manage' => 'User Management',
                                                    'content_manage' => 'Content Management',
                                                    'content_edit' => 'Edit and Publish News',
                                                    'news_articles_manage' => 'Manage News Articles',
                                                    'comments_manage' => 'Manage Comments',
                                                    'polls_manage' => 'Manage Polls',
                                                    'analytics_view' => 'View Statistics',
                                                    'role_applications_review' => 'Review Applications',
                                                    'settings_manage' => 'Settings'
                                                ];
                                                
                                                foreach ($permissions as $perm) {
                                                    if (isset($permission_names[$perm])) {
                                                        echo '<span class="permission-tag">' . $permission_names[$perm] . '</span>';
                                                    }
                                                }
                                            }
                                            ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>

        <!-- Role Criteria Section -->
        <div class="admin-role-card">
            <h4 class="mb-4">
                <i class="fas fa-clipboard-check me-2"></i>Role Approval Criteria
            </h4>
            
            <div class="row">
                <!-- Reporter Criteria -->
                <div class="col-lg-6 mb-3">
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
                </div>
                
                <!-- Editor Criteria -->
                <div class="col-lg-6 mb-3">
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
                </div>
            </div>
        </div>

        <!-- Reporter Performance Graphs -->
        <div class="admin-role-card">
            <h4 class="mb-4">
                <i class="fas fa-chart-line me-2"></i>Reporter Performance Analytics
            </h4>
            
            <?php if (!empty($editor_performance)): ?>
                <div class="row mb-4">
                    <!-- Performance Overview Chart -->
                    <div class="col-lg-8 mb-4">
                        <div class="card h-100">
                            <div class="card-header bg-white">
                                <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Performance Overview</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="performanceOverviewChart" height="100"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Top Performers Chart -->
                    <div class="col-lg-4 mb-4">
                        <div class="card h-100">
                            <div class="card-header bg-white">
                                <h6 class="mb-0"><i class="fas fa-trophy me-2"></i>Top Performers</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="topPerformersChart" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <!-- Activity Trends Chart -->
                    <div class="col-lg-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header bg-white">
                                <h6 class="mb-0"><i class="fas fa-chart-area me-2"></i>Activity Trends (Today, Week, Month)</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="activityTrendsChart" height="120"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Content Distribution Chart -->
                    <div class="col-lg-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header bg-white">
                                <h6 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Content Distribution</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="contentDistributionChart" height="120"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Engagement Metrics Chart -->
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-white">
                                <h6 class="mb-0"><i class="fas fa-heart me-2"></i>Engagement Metrics</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="engagementMetricsChart" height="80"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Performance Data Available</h5>
                    <p class="text-muted">No reporter/editor activity found to display performance graphs.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Current Admin Users -->
        <div class="admin-role-card">
            <h4 class="mb-4">
                <i class="fas fa-users-cog me-2"></i>Current Admin Users
            </h4>
            
            <?php if (mysqli_num_rows($admin_users_result) > 0): ?>
                <?php while ($admin_user = mysqli_fetch_assoc($admin_users_result)): ?>
                    <?php if ($admin_user['role_name'] === 'Editor'): ?>
                    <div class="admin-user-card">
                        <div class="row align-items-center mb-3">
                            <div class="col-md-4">
                                <h6 class="mb-1">
                                    <?php echo htmlspecialchars($admin_user['name']); ?>
                                    <span class="badge bg-warning ms-2">Editor</span>
                                </h6>
                                <small class="text-muted">
                                    <?php echo htmlspecialchars($admin_user['email']); ?>
                                </small>
                            </div>
                            <div class="col-md-8">
                                <div class="mb-2">
                                    <strong>Editor Permissions:</strong>
                                    <div class="mt-1">
                                        <span class="permission-tag">Manage News Articles</span>
                                        <span class="permission-tag">Edit and Publish News</span>
                                        <span class="permission-tag">Manage Comments</span>
                                        <span class="permission-tag">Manage Polls</span>
                                        <span class="permission-tag">View Statistics</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Editor Performance Section -->
                        <div class="row mt-3 pt-3 border-top">
                            <div class="col-12">
                                <h6 class="mb-3 text-primary">
                                    <i class="fas fa-chart-line me-2"></i>Performance Metrics
                                </h6>
                                <div class="row">
                                    <div class="col-md-2 col-sm-4 col-6 mb-2">
                                        <div class="text-center">
                                            <div class="h4 text-primary mb-0">
                                                <?php echo isset($editor_performance[$admin_user['id']]['total_articles']) ? number_format($editor_performance[$admin_user['id']]['total_articles']) : '0'; ?>
                                            </div>
                                            <small class="text-muted">Total Articles</small>
                                        </div>
                                    </div>
                                    <div class="col-md-2 col-sm-4 col-6 mb-2">
                                        <div class="text-center">
                                            <div class="h4 text-success mb-0">
                                                <?php echo isset($editor_performance[$admin_user['id']]['total_views']) ? number_format($editor_performance[$admin_user['id']]['total_views']) : '0'; ?>
                                            </div>
                                            <small class="text-muted">Total Views</small>
                                        </div>
                                    </div>
                                    <div class="col-md-2 col-sm-4 col-6 mb-2">
                                        <div class="text-center">
                                            <div class="h4 text-info mb-0">
                                                <?php echo isset($editor_performance[$admin_user['id']]['total_likes']) ? number_format($editor_performance[$admin_user['id']]['total_likes']) : '0'; ?>
                                            </div>
                                            <small class="text-muted">Total Likes</small>
                                        </div>
                                    </div>
                                    <div class="col-md-2 col-sm-4 col-6 mb-2">
                                        <div class="text-center">
                                            <div class="h4 text-warning mb-0">
                                                <?php echo isset($editor_performance[$admin_user['id']]['total_comments']) ? number_format($editor_performance[$admin_user['id']]['total_comments']) : '0'; ?>
                                            </div>
                                            <small class="text-muted">Total Comments</small>
                                        </div>
                                    </div>
                                    <div class="col-md-2 col-sm-4 col-6 mb-2">
                                        <div class="text-center">
                                            <div class="h4 text-secondary mb-0">
                                                <?php echo isset($editor_performance[$admin_user['id']]['avg_views_per_article']) ? number_format($editor_performance[$admin_user['id']]['avg_views_per_article'], 0) : '0'; ?>
                                            </div>
                                            <small class="text-muted">Avg Views/Article</small>
                                        </div>
                                    </div>
                                    <div class="col-md-2 col-sm-4 col-6 mb-2">
                                        <div class="text-center">
                                            <div class="h4 text-danger mb-0">
                                                <?php echo isset($editor_performance[$admin_user['id']]['articles_this_month']) ? number_format($editor_performance[$admin_user['id']]['articles_this_month']) : '0'; ?>
                                            </div>
                                            <small class="text-muted">This Month</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="admin-user-card">
                        <div class="row align-items-center">
                            <div class="col-md-4">
                                <h6 class="mb-1">
                                    <?php echo htmlspecialchars($admin_user['name']); ?>
                                    <span class="badge bg-success ms-2">
                                        <?php echo htmlspecialchars($admin_user['role_name']); ?>
                                    </span>
                                </h6>
                                <small class="text-muted">
                                    <?php echo htmlspecialchars($admin_user['email']); ?>
                                </small>
                            </div>
                            <div class="col-md-3">
                                <div>
                                    <strong>Level:</strong> <?php echo $admin_user['role_level']; ?>
                                </div>
                                <small class="text-muted">
                                    Assigned <?php echo date('M d, Y', strtotime($admin_user['assigned_at'])); ?>
                                </small>
                            </div>
                            <div class="col-md-3">
                                <?php 
                                $permissions = json_decode($admin_user['permissions'], true);
                                $count = $permissions && in_array('all', $permissions) ? 'Full Access' : count($permissions);
                                ?>
                                <div>
                                    <strong>Permissions:</strong> <?php echo $count; ?>
                                </div>
                                <?php if ($admin_user['assigned_by_name']): ?>
                                    <small class="text-muted">
                                        By <?php echo htmlspecialchars($admin_user['assigned_by_name']); ?>
                                    </small>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-2 text-end">
                                <?php if (isset($_SESSION['admin_level']) && $_SESSION['admin_level'] >= 100 && $admin_user['id'] != $_SESSION['user_id']): ?>
                                    <button class="btn btn-sm btn-outline-danger" 
                                            onclick="confirmRemoveAdmin(<?php echo $admin_user['id']; ?>, '<?php echo htmlspecialchars($admin_user['name']); ?>')">
                                        <i class="fas fa-user-times me-1"></i>Remove
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="text-center py-4">
                    <i class="fas fa-user-shield fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Admin Users Found</h5>
                    <p class="text-muted">No users have been assigned admin roles yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include '../includes/admin-footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmRemoveAdmin(userId, userName) {
            if (confirm(`Are you sure you want to remove admin privileges from ${userName}? This will downgrade them to a regular user.`)) {
                // Create form for removal
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="remove_admin_role" value="1">
                    <input type="hidden" name="target_user_id" value="${userId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Initialize Performance Charts
        <?php if (!empty($editor_performance)): ?>
        
        // Prepare performance data for charts
        const performanceData = <?php 
            $chart_data = [];
            foreach ($editor_performance as $user_id => $perf) {
                $chart_data[] = [
                    'name' => $perf['name'],
                    'total_articles' => (int)$perf['total_articles'],
                    'total_views' => (int)$perf['total_views'],
                    'total_likes' => (int)$perf['total_likes'],
                    'total_comments' => (int)$perf['total_comments'],
                    'avg_views' => round($perf['avg_views_per_article'], 1),
                    'articles_today' => (int)$perf['articles_today'],
                    'articles_this_week' => (int)$perf['articles_this_week'],
                    'articles_this_month' => (int)$perf['articles_this_month'],
                    'best_article_views' => (int)$perf['best_article_views'],
                    'draft_articles' => (int)$perf['draft_articles']
                ];
            }
            echo json_encode($chart_data);
        ?>;

        // Performance Overview Chart
        const performanceCtx = document.getElementById('performanceOverviewChart').getContext('2d');
        new Chart(performanceCtx, {
            type: 'bar',
            data: {
                labels: performanceData.map(d => d.name),
                datasets: [
                    {
                        label: 'Total Views',
                        data: performanceData.map(d => d.total_views),
                        backgroundColor: 'rgba(54, 162, 235, 0.8)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Total Articles',
                        data: performanceData.map(d => d.total_articles),
                        backgroundColor: 'rgba(255, 99, 132, 0.8)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 1,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Total Views'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Total Articles'
                        },
                        grid: {
                            drawOnChartArea: false
                        }
                    }
                }
            }
        });

        // Top Performers Chart
        const topPerformersCtx = document.getElementById('topPerformersChart').getContext('2d');
        const topPerformers = performanceData.slice(0, 5).sort((a, b) => b.total_views - a.total_views);
        new Chart(topPerformersCtx, {
            type: 'doughnut',
            data: {
                labels: topPerformers.map(d => d.name),
                datasets: [{
                    data: topPerformers.map(d => d.total_views),
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.8)',
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 206, 86, 0.8)',
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(153, 102, 255, 0.8)'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((value / total) * 100).toFixed(1);
                                return `${label}: ${value.toLocaleString()} views (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });

        // Activity Trends Chart
        const activityTrendsCtx = document.getElementById('activityTrendsChart').getContext('2d');
        new Chart(activityTrendsCtx, {
            type: 'line',
            data: {
                labels: ['Today', 'This Week', 'This Month'],
                datasets: performanceData.slice(0, 6).map((reporter, index) => ({
                    label: reporter.name,
                    data: [reporter.articles_today, reporter.articles_this_week, reporter.articles_this_month],
                    borderColor: `hsl(${index * 60}, 70%, 50%)`,
                    backgroundColor: `hsla(${index * 60}, 70%, 50%, 0.1)`,
                    tension: 0.4,
                    fill: true
                }))
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Number of Articles'
                        }
                    }
                }
            }
        });

        // Content Distribution Chart
        const contentDistCtx = document.getElementById('contentDistributionChart').getContext('2d');
        const totalPublished = performanceData.reduce((sum, d) => sum + d.total_articles, 0);
        const totalDrafts = performanceData.reduce((sum, d) => sum + d.draft_articles, 0);
        new Chart(contentDistCtx, {
            type: 'pie',
            data: {
                labels: ['Published Articles', 'Draft Articles'],
                datasets: [{
                    data: [totalPublished, totalDrafts],
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(255, 159, 64, 0.8)'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((value / total) * 100).toFixed(1);
                                return `${label}: ${value.toLocaleString()} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });

        // Engagement Metrics Chart
        const engagementCtx = document.getElementById('engagementMetricsChart').getContext('2d');
        new Chart(engagementCtx, {
            type: 'radar',
            data: {
                labels: ['Total Views', 'Total Likes', 'Total Comments', 'Avg Views/Article', 'Best Article Views'],
                datasets: performanceData.slice(0, 3).map((reporter, index) => ({
                    label: reporter.name,
                    data: [
                        reporter.total_views / 1000, // Scale down for better visualization
                        reporter.total_likes * 10, // Scale up for visibility
                        reporter.total_comments * 10, // Scale up for visibility
                        reporter.avg_views,
                        reporter.best_article_views / 100 // Scale down
                    ],
                    borderColor: `hsl(${index * 120}, 70%, 50%)`,
                    backgroundColor: `hsla(${index * 120}, 70%, 50%, 0.2)`,
                    pointBackgroundColor: `hsl(${index * 120}, 70%, 50%)`,
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: `hsl(${index * 120}, 70%, 50%)`
                }))
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    r: {
                        beginAtZero: true,
                        ticks: {
                            display: false
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.dataset.label || '';
                                const dataIndex = context.dataIndex;
                                const originalLabels = ['Total Views', 'Total Likes', 'Total Comments', 'Avg Views/Article', 'Best Article Views'];
                                const originalData = performanceData[context.datasetIndex];
                                const values = [
                                    originalData.total_views,
                                    originalData.total_likes,
                                    originalData.total_comments,
                                    originalData.avg_views,
                                    originalData.best_article_views
                                ];
                                return `${label} - ${originalLabels[dataIndex]}: ${values[dataIndex].toLocaleString()}`;
                            }
                        }
                    }
                }
            }
        });

        <?php endif; ?>
    </script>
</body>
</html>

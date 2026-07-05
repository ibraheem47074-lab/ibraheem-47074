<?php
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    redirect('login.php');
}

$success = '';
$error = '';

// Handle user management actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_user_role'])) {
        $user_id = intval($_POST['user_id']);
        $new_role = clean_input($_POST['role']);
        $new_department = clean_input($_POST['department']);
        $new_experience = clean_input($_POST['experience_level']);
        
        $update_query = "UPDATE users SET role = ?, department = ?, experience_level = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($stmt, 'sssi', $new_role, $new_department, $new_experience, $user_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = "User role and profile updated successfully!";
        } else {
            $error = "Failed to update user role!";
        }
    }
    
    if (isset($_POST['toggle_user_status'])) {
        $user_id = intval($_POST['user_id']);
        $current_status = clean_input($_POST['current_status']);
        $new_status = $current_status === 'active' ? 'blocked' : 'active';
        
        $update_query = "UPDATE users SET status = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($stmt, 'si', $new_status, $user_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = "User status updated to " . ucfirst($new_status) . "!";
        } else {
            $error = "Failed to update user status!";
        }
    }
    
    if (isset($_POST['update_verification'])) {
        $user_id = intval($_POST['user_id']);
        $verification_status = clean_input($_POST['verification_status']);
        
        $update_query = "UPDATE users SET verification_status = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($stmt, 'si', $verification_status, $user_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = "User verification status updated!";
        } else {
            $error = "Failed to update verification status!";
        }
    }
    
    if (isset($_POST['grant_permission'])) {
        $user_id = intval($_POST['user_id']);
        $permission = clean_input($_POST['permission']);
        $expires_at = !empty($_POST['expires_at']) ? clean_input($_POST['expires_at']) : null;
        
        // Check if permission already exists
        $check_query = "SELECT id FROM user_permissions WHERE user_id = ? AND permission = ?";
        $stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($stmt, 'is', $user_id, $permission);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) === 0) {
            $insert_query = "INSERT INTO user_permissions (user_id, permission, granted_by, expires_at) VALUES (?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $insert_query);
            mysqli_stmt_bind_param($stmt, 'isis', $user_id, $permission, $_SESSION['user_id'], $expires_at);
            
            if (mysqli_stmt_execute($stmt)) {
                $success = "Permission granted successfully!";
            } else {
                $error = "Failed to grant permission!";
            }
        } else {
            $error = "Permission already exists for this user!";
        }
    }
    
    if (isset($_POST['revoke_permission'])) {
        $permission_id = intval($_POST['permission_id']);
        
        $delete_query = "DELETE FROM user_permissions WHERE id = ?";
        $stmt = mysqli_prepare($conn, $delete_query);
        mysqli_stmt_bind_param($stmt, 'i', $permission_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = "Permission revoked successfully!";
        } else {
            $error = "Failed to revoke permission!";
        }
    }
}

// Get all users with advanced information
function getAllAdvancedUsers($conn) {
    $query = "SELECT u.*, 
                     (SELECT AVG(rating) FROM user_ratings WHERE rated_user_id = u.id) as average_rating,
                     (SELECT COUNT(*) FROM user_ratings WHERE rated_user_id = u.id) as total_ratings,
                     (SELECT COUNT(*) FROM user_achievements WHERE user_id = u.id) as total_achievements,
                     (SELECT COUNT(*) FROM news WHERE author_id = u.id) as total_articles
              FROM users u 
              ORDER BY u.created_at DESC";
    
    $result = mysqli_query($conn, $query);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Get user permissions
function getUserPermissionsDetailed($user_id, $conn) {
    $query = "SELECT up.*, g.name as granted_by_name 
              FROM user_permissions up 
              LEFT JOIN users g ON up.granted_by = g.id 
              WHERE up.user_id = ? 
              ORDER BY up.granted_at DESC";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Get available permissions
$available_permissions = [
    'manage_users' => 'Manage Users',
    'manage_content' => 'Manage Content',
    'manage_system' => 'Manage System',
    'view_analytics' => 'View Analytics',
    'manage_ads' => 'Manage Ads',
    'manage_live_streams' => 'Manage Live Streams',
    'approve_content' => 'Approve Content',
    'delete_content' => 'Delete Content',
    'manage_categories' => 'Manage Categories',
    'manage_tags' => 'Manage Tags',
    'edit_content' => 'Edit Content',
    'publish_content' => 'Publish Content',
    'review_content' => 'Review Content',
    'manage_reporters' => 'Manage Reporters',
    'schedule_content' => 'Schedule Content',
    'view_reports' => 'View Reports',
    'create_content' => 'Create Content',
    'upload_media' => 'Upload Media',
    'submit_content' => 'Submit Content',
    'view_own_stats' => 'View Own Stats',
    'mentor_juniors' => 'Mentor Juniors',
    'analyze_content' => 'Analyze Content',
    'manage_social' => 'Manage Social Media'
];

$users = getAllAdvancedUsers($conn);
$departments = ['editorial', 'reporting', 'technical', 'management', 'marketing', 'multimedia'];
$experience_levels = ['junior', 'intermediate', 'senior', 'expert', 'lead'];
$roles = ['admin', 'editor', 'reporter'];
$verification_statuses = ['unverified', 'verified', 'premium'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - PK Live News</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .user-card {
            border: 1px solid #dee2e6;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            transition: transform 0.2s;
        }
        .user-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .user-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
        }
        .verification-badge {
            background: #28a745;
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 15px;
            font-size: 0.75rem;
        }
        .premium-badge {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 15px;
            font-size: 0.75rem;
        }
        .status-active {
            background: #28a745;
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 15px;
            font-size: 0.75rem;
        }
        .status-blocked {
            background: #dc3545;
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 15px;
            font-size: 0.75rem;
        }
        .rating-stars {
            color: #ffc107;
        }
        .permission-tag {
            background: #e9ecef;
            padding: 0.25rem 0.5rem;
            border-radius: 15px;
            font-size: 0.8rem;
            margin: 0.125rem;
            display: inline-block;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }
        .stat-item {
            text-align: center;
            padding: 0.5rem;
            background: #f8f9fa;
            border-radius: 8px;
        }
        .stat-number {
            font-size: 1.25rem;
            font-weight: bold;
            color: #667eea;
        }
        .stat-label {
            font-size: 0.75rem;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <?php include 'includes/admin-header.php'; ?>

    <div class="container-fluid mt-4">
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-users"></i> User Management</h2>
                    <div>
                        <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                            <i class="fas fa-user-plus"></i> Add User
                        </button>
                        <button class="btn btn-outline-secondary" onclick="exportUsers()">
                            <i class="fas fa-download"></i> Export
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- User Statistics -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4><?php echo count($users); ?></h4>
                                <p class="mb-0">Total Users</p>
                            </div>
                            <i class="fas fa-users fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4><?php echo count(array_filter($users, fn($u) => $u['status'] === 'active')); ?></h4>
                                <p class="mb-0">Active Users</p>
                            </div>
                            <i class="fas fa-user-check fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4><?php echo count(array_filter($users, fn($u) => $u['verification_status'] === 'verified')); ?></h4>
                                <p class="mb-0">Verified Users</p>
                            </div>
                            <i class="fas fa-user-shield fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4><?php echo count(array_filter($users, fn($u) => $u['role'] === 'admin')); ?></h4>
                                <p class="mb-0">Admins</p>
                            </div>
                            <i class="fas fa-user-cog fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users Grid -->
        <div class="row">
            <?php foreach ($users as $user): ?>
                <div class="col-lg-6 col-xl-4">
                    <div class="user-card">
                        <div class="d-flex align-items-start mb-3">
                            <img src="<?php echo !empty($user['image']) ? '../uploads/avatars/' . $user['image'] : 'https://via.placeholder.com/60'; ?>" 
                                 alt="Avatar" class="user-avatar me-3">
                            <div class="flex-grow-1">
                                <h6 class="mb-1"><?php echo htmlspecialchars($user['name']); ?></h6>
                                <p class="text-muted mb-1"><?php echo htmlspecialchars($user['email']); ?></p>
                                <div class="mb-2">
                                    <span class="badge bg-primary"><?php echo ucfirst($user['role'] ?? ''); ?></span>
                                    <span class="badge bg-info"><?php echo ucfirst($user['department'] ?? ''); ?></span>
                                    <span class="badge bg-warning"><?php echo ucfirst($user['experience_level'] ?? ''); ?></span>
                                    <?php if ($user['verification_status'] === 'verified'): ?>
                                        <span class="verification-badge"><i class="fas fa-check-circle"></i> Verified</span>
                                    <?php elseif ($user['verification_status'] === 'premium'): ?>
                                        <span class="premium-badge"><i class="fas fa-crown"></i> Premium</span>
                                    <?php endif; ?>
                                    <span class="<?php echo $user['status'] === 'active' ? 'status-active' : 'status-blocked'; ?>">
                                        <?php echo ucfirst($user['status'] ?? ''); ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- User Stats -->
                        <div class="stats-grid">
                            <div class="stat-item">
                                <div class="stat-number"><?php echo $user['total_articles']; ?></div>
                                <div class="stat-label">Articles</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number"><?php echo $user['total_achievements']; ?></div>
                                <div class="stat-label">Achievements</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number"><?php echo round($user['average_rating'] ?? 0, 1); ?></div>
                                <div class="stat-label">Rating</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number"><?php echo $user['login_count']; ?></div>
                                <div class="stat-label">Logins</div>
                            </div>
                        </div>

                        <!-- Rating -->
                        <?php if ($user['total_ratings'] > 0): ?>
                            <div class="rating-stars mb-2">
                                <?php 
                                $rating = round($user['average_rating'], 1);
                                for ($i = 1; $i <= 5; $i++):
                                    if ($i <= $rating):
                                        echo '<i class="fas fa-star"></i>';
                                    else:
                                        echo '<i class="far fa-star"></i>';
                                    endif;
                                endfor;
                                ?>
                                <span class="ms-2 small"><?php echo $rating; ?> (<?php echo $user['total_ratings']; ?>)</span>
                            </div>
                        <?php endif; ?>

                        <!-- Action Buttons -->
                        <div class="d-flex gap-2 flex-wrap">
                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" 
                                    data-bs-target="#editUserModal<?php echo $user['id']; ?>">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal" 
                                    data-bs-target="#permissionsModal<?php echo $user['id']; ?>">
                                <i class="fas fa-key"></i> Permissions
                            </button>
                            <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" 
                                    data-bs-target="#detailsModal<?php echo $user['id']; ?>">
                                <i class="fas fa-eye"></i> Details
                            </button>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <input type="hidden" name="current_status" value="<?php echo $user['status']; ?>">
                                <button type="submit" name="toggle_user_status" 
                                        class="btn btn-sm <?php echo $user['status'] === 'active' ? 'btn-outline-danger' : 'btn-outline-success'; ?>">
                                    <i class="fas fa-<?php echo $user['status'] === 'active' ? 'ban' : 'check'; ?>"></i> 
                                    <?php echo $user['status'] === 'active' ? 'Block' : 'Activate'; ?>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Edit User Modal -->
                <div class="modal fade" id="editUserModal<?php echo $user['id']; ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Edit User: <?php echo htmlspecialchars($user['name']); ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST">
                                <div class="modal-body">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <div class="mb-3">
                                        <label class="form-label">Role</label>
                                        <select name="role" class="form-select" required>
                                            <?php foreach ($roles as $role): ?>
                                                <option value="<?php echo $role; ?>" <?php echo $user['role'] === $role ? 'selected' : ''; ?>>
                                                    <?php echo ucfirst($role); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Department</label>
                                        <select name="department" class="form-select" required>
                                            <?php foreach ($departments as $dept): ?>
                                                <option value="<?php echo $dept; ?>" <?php echo $user['department'] === $dept ? 'selected' : ''; ?>>
                                                    <?php echo ucfirst($dept); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Experience Level</label>
                                        <select name="experience_level" class="form-select" required>
                                            <?php foreach ($experience_levels as $level): ?>
                                                <option value="<?php echo $level; ?>" <?php echo $user['experience_level'] === $level ? 'selected' : ''; ?>>
                                                    <?php echo ucfirst($level); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Verification Status</label>
                                        <select name="verification_status" class="form-select">
                                            <?php foreach ($verification_statuses as $status): ?>
                                                <option value="<?php echo $status; ?>" <?php echo $user['verification_status'] === $status ? 'selected' : ''; ?>>
                                                    <?php echo ucfirst($status); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" name="update_user_role" class="btn btn-primary">Update User</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Permissions Modal -->
                <div class="modal fade" id="permissionsModal<?php echo $user['id']; ?>" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Permissions: <?php echo htmlspecialchars($user['name']); ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <h6>Current Permissions</h6>
                                <div class="mb-3">
                                    <?php 
                                    $user_permissions = getUserPermissionsDetailed($user['id'], $conn);
                                    if (!empty($user_permissions)):
                                        foreach ($user_permissions as $perm):
                                    ?>
                                        <span class="permission-tag">
                                            <?php echo $available_permissions[$perm['permission']] ?? $perm['permission']; ?>
                                            <small class="text-muted">(<?php echo date('M d, Y', strtotime($perm['granted_at'])); ?>)</small>
                                        </span>
                                        <?php 
                                        endforeach;
                                    else:
                                    ?>
                                        <p class="text-muted">No special permissions assigned</p>
                                    <?php endif; ?>
                                </div>
                                
                                <h6>Grant New Permission</h6>
                                <form method="POST" class="d-flex gap-2">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <select name="permission" class="form-select" required>
                                        <option value="">Select Permission</option>
                                        <?php foreach ($available_permissions as $key => $label): ?>
                                            <option value="<?php echo $key; ?>"><?php echo $label; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="date" name="expires_at" class="form-control" placeholder="Expires (optional)">
                                    <button type="submit" name="grant_permission" class="btn btn-primary">Grant</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Details Modal -->
                <div class="modal fade" id="detailsModal<?php echo $user['id']; ?>" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">User Details: <?php echo htmlspecialchars($user['name']); ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                                        <p><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone'] ?? 'Not provided'); ?></p>
                                        <p><strong>Role:</strong> <?php echo ucfirst($user['role'] ?? ''); ?></p>
                                        <p><strong>Department:</strong> <?php echo ucfirst($user['department'] ?? ''); ?></p>
                                        <p><strong>Experience Level:</strong> <?php echo ucfirst($user['experience_level'] ?? ''); ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Specialization:</strong> <?php echo htmlspecialchars($user['specialization'] ?? 'Not specified'); ?></p>
                                        <p><strong>Articles Published:</strong> <?php echo $user['total_articles']; ?></p>
                                        <p><strong>Profile Views:</strong> <?php echo $user['profile_views']; ?></p>
                                        <p><strong>Last Login:</strong> <?php echo $user['last_login'] ? date('M d, Y H:i', strtotime($user['last_login'])) : 'Never'; ?></p>
                                        <p><strong>Member Since:</strong> <?php echo date('M d, Y', strtotime($user['created_at'])); ?></p>
                                    </div>
                                </div>
                                <?php if (!empty($user['bio'])): ?>
                                    <div class="mt-3">
                                        <strong>Bio:</strong>
                                        <p><?php echo htmlspecialchars($user['bio']); ?></p>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($user['skills'])): ?>
                                    <div class="mt-3">
                                        <strong>Skills:</strong>
                                        <div>
                                            <?php 
                                            $skills_array = explode(',', $user['skills']);
                                            foreach ($skills_array as $skill):
                                                $skill = trim($skill);
                                                if (!empty($skill)):
                                            ?>
                                            <span class="skill-tag"><?php echo htmlspecialchars($skill); ?></span>
                                            <?php 
                                                endif;
                                            endforeach; 
                                            ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function exportUsers() {
            // Simple CSV export functionality
            const users = <?php echo json_encode($users); ?>;
            let csv = 'Name,Email,Role,Department,Status,Verification,Created\n';
            
            users.forEach(user => {
                csv += `"${user.name}","${user.email}","${user.role}","${user.department}","${user.status}","${user.verification_status}","${user.created_at}"\n`;
            });
            
            const blob = new Blob([csv], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'users_export.csv';
            a.click();
            window.URL.revokeObjectURL(url);
        }
    </script>
</body>
</html>

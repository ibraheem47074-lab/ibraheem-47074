<?php
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    redirect('login.php');
}

// Handle delete action
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $user_id = $_GET['delete'];
    
    // Prevent deleting yourself
    if ($user_id == $_SESSION['user_id']) {
        $error = "You cannot delete your own account!";
    } else {
        // Check if user has news articles
        $news_check = mysqli_query($conn, "SELECT COUNT(*) as count FROM news WHERE author_id = $user_id");
        $news_count = mysqli_fetch_assoc($news_check)['count'];
        
        if ($news_count > 0) {
            $error = "Cannot delete user - they have $news_count news articles. Please reassign or delete the articles first.";
        } else {
            // Delete user
            $delete_query = "DELETE FROM users WHERE id = $user_id";
            
            if (mysqli_query($conn, $delete_query)) {
                $success = "User deleted successfully!";
            } else {
                $error = "Error deleting user!";
            }
        }
    }
}

// Handle status change
if (isset($_GET['status']) && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $user_id = $_GET['id'];
    $status = $_GET['status'];
    
    // Prevent blocking yourself
    if ($user_id == $_SESSION['user_id'] && $status == 'blocked') {
        $error = "You cannot block your own account!";
    } elseif (in_array($status, ['active', 'blocked'])) {
        $update_query = "UPDATE users SET status = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($stmt, 'si', $status, $user_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = "User status updated successfully!";
        } else {
            $error = "Error updating user status!";
        }
    }
}

// Handle role change
if (isset($_GET['role']) && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $user_id = $_GET['id'];
    $role = $_GET['role'];
    
    // Prevent changing your own role from admin
    if ($user_id == $_SESSION['user_id'] && $role != 'admin') {
        $error = "You cannot change your own role from admin!";
    } elseif (in_array($role, ['admin', 'editor', 'reporter'])) {
        $update_query = "UPDATE users SET role = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($stmt, 'si', $role, $user_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = "User role updated successfully!";
        } else {
            $error = "Error updating user role!";
        }
    }
}

// Handle role application actions
if (isset($_GET['app_action']) && isset($_GET['app_id']) && is_numeric($_GET['app_id'])) {
    $app_id = $_GET['app_id'];
    $action = $_GET['app_action'];
    $admin_notes = $_GET['admin_notes'] ?? '';
    
    // Get application details
    $app_query = "SELECT ra.*, u.name, u.email FROM role_applications ra JOIN users u ON ra.user_id = u.id WHERE ra.id = ?";
    $app_stmt = mysqli_prepare($conn, $app_query);
    mysqli_stmt_bind_param($app_stmt, 'i', $app_id);
    mysqli_stmt_execute($app_stmt);
    $app_result = mysqli_stmt_get_result($app_stmt);
    $application = mysqli_fetch_assoc($app_result);
    
    if (!$application) {
        $error = "Application not found!";
    } elseif (in_array($action, ['approve', 'reject'])) {
        // Start transaction
        mysqli_begin_transaction($conn);
        
        try {
            // Update application status
            $update_app_query = "UPDATE role_applications SET status = ?, admin_notes = ?, reviewed_by = ?, reviewed_at = NOW() WHERE id = ?";
            $update_app_stmt = mysqli_prepare($conn, $update_app_query);
            mysqli_stmt_bind_param($update_app_stmt, 'ssii', $action, $admin_notes, $_SESSION['user_id'], $app_id);
            mysqli_stmt_execute($update_app_stmt);
            
            // Update user status and role if approved
            if ($action === 'approve') {
                $update_user_query = "UPDATE users SET role = ?, application_status = 'approved' WHERE id = ?";
                $update_user_stmt = mysqli_prepare($conn, $update_user_query);
                mysqli_stmt_bind_param($update_user_stmt, 'si', $application['applied_role'], $application['user_id']);
                mysqli_stmt_execute($update_user_stmt);
                
                $success = "Application approved! User role has been updated to " . ucfirst($application['applied_role']);
            } else {
                // Update user application status to rejected
                $update_user_query = "UPDATE users SET application_status = 'rejected' WHERE id = ?";
                $update_user_stmt = mysqli_prepare($conn, $update_user_query);
                mysqli_stmt_bind_param($update_user_stmt, 'i', $application['user_id']);
                mysqli_stmt_execute($update_user_stmt);
                
                $success = "Application rejected and user has been notified";
            }
            
            mysqli_commit($conn);
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $error = "Error processing application: " . $e->getMessage();
        }
    }
}

// Handle filtering
$filter_role = $_GET['filter_role'] ?? 'all';
$filter_status = $_GET['filter_status'] ?? 'all';
$search = $_GET['search'] ?? '';

// Build base query
$where_conditions = [];
$params = [];
$types = '';

if ($filter_role !== 'all') {
    $where_conditions[] = "u.role = ?";
    $params[] = $filter_role;
    $types .= 's';
}

if ($filter_status !== 'all') {
    $where_conditions[] = "u.status = ?";
    $params[] = $filter_status;
    $types .= 's';
}

if (!empty($search)) {
    $where_conditions[] = "(u.name LIKE ? OR u.email LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ss';
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(' AND ', $where_conditions) : "";

// Get filtered users with statistics
$users_query = "SELECT u.*, 
                (SELECT COUNT(*) FROM news WHERE author_id = u.id) as news_count,
                (SELECT COUNT(*) FROM comments WHERE user_id = u.id) as comments_count
                FROM users u 
                $where_clause
                ORDER BY u.created_at DESC";

if (!empty($params)) {
    $stmt = mysqli_prepare($conn, $users_query);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $users_result = mysqli_stmt_get_result($stmt);
} else {
    $users_result = mysqli_query($conn, $users_query);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - PK Live News Admin</title>
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
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }
        .action-buttons .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        .role-badge {
            font-size: 0.75rem;
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
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
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
                            <a class="nav-link" href="manage-submissions.php">
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
                        <h1 class="h3 mb-0">Manage Users</h1>
                        <small>Manage user accounts and permissions</small>
                    </div>
                    <div>
                        <a href="add-user.php" class="btn btn-light">
                            <i class="fas fa-user-plus me-2"></i>Add User
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

                <!-- Users Table -->
                <div class="card">
                    <div class="card-header bg-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Users (<?php echo mysqli_num_rows($users_result); ?> total)</h5>
                            <a href="user_analytics.php" class="btn btn-primary btn-sm">
                                <i class="fas fa-chart-line me-2"></i>View Analytics
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Filtering Form -->
                        <form method="GET" class="mb-4">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">Search</label>
                                    <input type="text" name="search" class="form-control" placeholder="Search users..." value="<?php echo htmlspecialchars($search); ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Role</label>
                                    <select name="filter_role" class="form-select">
                                        <option value="all" <?php echo $filter_role === 'all' ? 'selected' : ''; ?>>All Roles</option>
                                        <option value="admin" <?php echo $filter_role === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                        <option value="editor" <?php echo $filter_role === 'editor' ? 'selected' : ''; ?>>Editor</option>
                                        <option value="reporter" <?php echo $filter_role === 'reporter' ? 'selected' : ''; ?>>Reporter</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Status</label>
                                    <select name="filter_status" class="form-select">
                                        <option value="all" <?php echo $filter_status === 'all' ? 'selected' : ''; ?>>All Status</option>
                                        <option value="active" <?php echo $filter_status === 'active' ? 'selected' : ''; ?>>Active</option>
                                        <option value="blocked" <?php echo $filter_status === 'blocked' ? 'selected' : ''; ?>>Blocked</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">&nbsp;</label>
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-filter me-2"></i>Filter
                                        </button>
                                        <a href="manage-users.php" class="btn btn-secondary">
                                            <i class="fas fa-times me-2"></i>Clear
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                        <th>News</th>
                                        <th>Comments</th>
                                        <th>Joined</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($user = mysqli_fetch_assoc($users_result)): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="user-avatar me-3">
                                                        <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold"><?php echo htmlspecialchars($user['name']); ?></div>
                                                        <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                                            <small class="text-muted">(You)</small>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <a href="mailto:<?php echo htmlspecialchars($user['email']); ?>" class="text-decoration-none">
                                                    <?php echo htmlspecialchars($user['email']); ?>
                                                </a>
                                            </td>
                                            <td>
                                                <?php
                                                $role_colors = [
                                                    'admin' => 'danger',
                                                    'editor' => 'warning',
                                                    'reporter' => 'info'
                                                ];
                                                $role_color = $role_colors[$user['role']] ?? 'secondary';
                                                ?>
                                                <span class="badge bg-<?php echo $role_color; ?> role-badge">
                                                    <?php echo ucfirst($user['role']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php
                                                $status_class = $user['status'] == 'active' ? 'bg-success' : 'bg-secondary';
                                                ?>
                                                <span class="badge <?php echo $status_class; ?>">
                                                    <?php echo ucfirst($user['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-info"><?php echo $user['news_count']; ?></span>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary"><?php echo $user['comments_count']; ?></span>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                            <td>
                                                <div class="action-buttons d-flex gap-1 flex-wrap">
                                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                        <a href="edit-user.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-secondary" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        
                                                        <!-- Role Change -->
                                                        <div class="dropdown">
                                                            <button class="btn btn-sm btn-outline-warning dropdown-toggle" type="button" data-bs-toggle="dropdown" title="Change Role">
                                                                <i class="fas fa-user-tag"></i>
                                                            </button>
                                                            <ul class="dropdown-menu">
                                                                <li><a class="dropdown-item" href="?id=<?php echo $user['id']; ?>&role=admin">Admin</a></li>
                                                                <li><a class="dropdown-item" href="?id=<?php echo $user['id']; ?>&role=editor">Editor</a></li>
                                                                <li><a class="dropdown-item" href="?id=<?php echo $user['id']; ?>&role=reporter">Reporter</a></li>
                                                            </ul>
                                                        </div>
                                                        
                                                        <?php if ($user['status'] != 'active'): ?>
                                                            <a href="?id=<?php echo $user['id']; ?>&status=active" class="btn btn-sm btn-outline-success" title="Activate">
                                                                <i class="fas fa-check"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                        
                                                        <?php if ($user['status'] != 'blocked'): ?>
                                                            <a href="?id=<?php echo $user['id']; ?>&status=blocked" class="btn btn-sm btn-outline-warning" title="Block" onclick="return confirm('Are you sure you want to block this user?')">
                                                                <i class="fas fa-ban"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                        
                                                        <?php if ($user['news_count'] == 0): ?>
                                                            <a href="?delete=<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this user?')">
                                                                <i class="fas fa-trash"></i>
                                                            </a>
                                                        <?php else: ?>
                                                            <button class="btn btn-sm btn-outline-danger" disabled title="Cannot delete - has news articles">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <span class="text-muted small">Current User</span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>

                        <?php if (mysqli_num_rows($users_result) === 0): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                <h5>No users found</h5>
                                <p class="text-muted">Start by adding your first user</p>
                                <a href="add-user.php" class="btn btn-danger">
                                    <i class="fas fa-user-plus me-2"></i>Add First User
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Role Applications Section -->
                <div class="card mt-4" id="applications">
                    <div class="card-header bg-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-user-tie me-2"></i>Role Applications
                            </h5>
                            <span class="badge bg-info" id="pendingCount">0 Pending</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php
                        // Get role applications
                        $apps_query = "SELECT ra.*, u.name, u.email, u.created_at as user_joined 
                                      FROM role_applications ra 
                                      JOIN users u ON ra.user_id = u.id 
                                      ORDER BY ra.status = 'pending' DESC, ra.created_at DESC";
                        $apps_result = mysqli_query($conn, $apps_query);
                        $pending_count = 0;
                        ?>
                        
                        <?php if (mysqli_num_rows($apps_result) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Applicant</th>
                                            <th>Applied Role</th>
                                            <th>Experience</th>
                                            <th>Status</th>
                                            <th>Applied</th>
                                            <th>CV</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($app = mysqli_fetch_assoc($apps_result)): ?>
                                            <?php 
                                            $app_data = json_decode($app['application_data'], true);
                                            if ($app['status'] === 'pending') $pending_count++;
                                            ?>
                                            <tr class="<?php echo $app['status'] === 'pending' ? 'table-warning' : ''; ?>">
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="user-avatar me-3">
                                                            <?php echo strtoupper(substr($app['name'], 0, 1)); ?>
                                                        </div>
                                                        <div>
                                                            <div class="fw-bold"><?php echo htmlspecialchars($app['name']); ?></div>
                                                            <small class="text-muted"><?php echo htmlspecialchars($app['email']); ?></small>
                                                            <br><small class="text-muted">Joined: <?php echo date('M d, Y', strtotime($app['user_joined'])); ?></small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?php echo $app['applied_role'] === 'editor' ? 'warning' : 'info'; ?>">
                                                        <?php echo ucfirst($app['applied_role']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <small class="text-muted" title="<?php echo htmlspecialchars($app_data['experience'] ?? ''); ?>">
                                                        <?php echo substr(htmlspecialchars($app_data['experience'] ?? ''), 0, 50); ?>...
                                                    </small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?php echo $app['status'] === 'pending' ? 'warning' : ($app['status'] === 'approved' ? 'success' : 'danger'); ?>">
                                                        <?php echo ucfirst($app['status']); ?>
                                                    </span>
                                                    <?php if ($app['reviewed_at']): ?>
                                                        <br><small class="text-muted"><?php echo date('M d, Y', strtotime($app['reviewed_at'])); ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo date('M d, Y', strtotime($app['created_at'])); ?></td>
                                                <td>
                                                    <?php if (!empty($app['cv_file_name'])): ?>
                                                        <a href="../<?php echo htmlspecialchars($app['cv_file_path']); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-file-alt me-1"></i>View CV
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-muted">No CV</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="action-buttons d-flex gap-1 flex-wrap">
                                                        <?php if ($app['status'] === 'pending'): ?>
                                                            <!-- Approve Button -->
                                                            <button type="button" class="btn btn-sm btn-success" 
                                                                    onclick="approveApplication(<?php echo $app['id']; ?>, '<?php echo htmlspecialchars($app['name']); ?>', '<?php echo $app['applied_role']; ?>')"
                                                                    title="Approve Application">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                            
                                                            <!-- Reject Button -->
                                                            <button type="button" class="btn btn-sm btn-danger" 
                                                                    onclick="rejectApplication(<?php echo $app['id']; ?>, '<?php echo htmlspecialchars($app['name']); ?>')"
                                                                    title="Reject Application">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                        
                                                        <!-- View Details Button -->
                                                        <button type="button" class="btn btn-sm btn-outline-info" 
                                                                onclick="viewApplicationDetails(<?php echo $app['id']; ?>)"
                                                                title="View Full Application">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-user-tie fa-3x text-muted mb-3"></i>
                                <h5>No Role Applications</h5>
                                <p class="text-muted">No users have applied for enhanced roles yet</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Enhanced User Statistics -->
                <div class="row mt-4">
                    <div class="col-md-3">
                        <div class="card stat-card">
                            <div class="card-body d-flex align-items-center">
                                <div class="stat-icon bg-primary me-3">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div>
                                    <h3 class="mb-0"><?php echo mysqli_num_rows($users_result); ?></h3>
                                    <p class="mb-0 text-muted">Filtered Users</p>
                                    <small class="text-muted">of <?php 
                                        $total_all = mysqli_query($conn, "SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
                                        echo $total_all; ?> total</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card">
                            <div class="card-body d-flex align-items-center">
                                <div class="stat-icon bg-success me-3">
                                    <i class="fas fa-user-check"></i>
                                </div>
                                <div>
                                    <?php
                                    $active_count = mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE status = 'active'")->fetch_assoc()['count'];
                                    ?>
                                    <h3 class="mb-0"><?php echo $active_count; ?></h3>
                                    <p class="mb-0 text-muted">Active Users</p>
                                    <small class="text-muted"><?php 
                                        $total_all = mysqli_query($conn, "SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
                                        $percentage = $total_all > 0 ? round(($active_count / $total_all) * 100, 1) : 0;
                                        echo $percentage; ?>% active</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card">
                            <div class="card-body d-flex align-items-center">
                                <div class="stat-icon bg-danger me-3">
                                    <i class="fas fa-user-shield"></i>
                                </div>
                                <div>
                                    <?php
                                    $admin_count = mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE role = 'admin'")->fetch_assoc()['count'];
                                    ?>
                                    <h3 class="mb-0"><?php echo $admin_count; ?></h3>
                                    <p class="mb-0 text-muted">Admins</p>
                                    <small class="text-muted">System administrators</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card">
                            <div class="card-body d-flex align-items-center">
                                <div class="stat-icon bg-warning me-3">
                                    <i class="fas fa-user-edit"></i>
                                </div>
                                <div>
                                    <?php
                                    $editor_count = mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE role = 'editor'")->fetch_assoc()['count'];
                                    ?>
                                    <h3 class="mb-0"><?php echo $editor_count; ?></h3>
                                    <p class="mb-0 text-muted">Editors</p>
                                    <small class="text-muted">Content editors</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional Statistics -->
                <div class="row mt-4">
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header bg-white">
                                <h6 class="mb-0">Recent Activity</h6>
                            </div>
                            <div class="card-body">
                                <?php
                                $recent_registrations = mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetch_assoc()['count'];
                                $recent_logins = mysqli_query($conn, "SELECT COUNT(DISTINCT user_id) as count FROM user_analytics WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetch_assoc()['count'];
                                ?>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>New this week:</span>
                                    <strong><?php echo $recent_registrations; ?></strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Active this week:</span>
                                    <strong><?php echo $recent_logins ?: 'N/A'; ?></strong>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header bg-white">
                                <h6 class="mb-0">Content Statistics</h6>
                            </div>
                            <div class="card-body">
                                <?php
                                $total_articles = mysqli_query($conn, "SELECT COUNT(*) as count FROM news")->fetch_assoc()['count'];
                                $total_comments = mysqli_query($conn, "SELECT COUNT(*) as count FROM comments")->fetch_assoc()['count'];
                                ?>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Total Articles:</span>
                                    <strong><?php echo $total_articles; ?></strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Total Comments:</span>
                                    <strong><?php echo $total_comments; ?></strong>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header bg-white">
                                <h6 class="mb-0">Role Distribution</h6>
                            </div>
                            <div class="card-body">
                                <?php
                                $reporter_count = mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE role = 'reporter'")->fetch_assoc()['count'];
                                $total_all = mysqli_query($conn, "SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
                                ?>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Admins:</span>
                                    <strong><?php echo $admin_count; ?></strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Editors:</span>
                                    <strong><?php echo $editor_count; ?></strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Reporters:</span>
                                    <strong><?php echo $reporter_count; ?></strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Role Permissions Guide -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">Role Permissions</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <h6 class="text-danger"><i class="fas fa-user-shield me-2"></i>Admin</h6>
                                <ul class="list-unstyled small">
                                    <li>• Full system access</li>
                                    <li>• Manage all users</li>
                                    <li>• Manage categories</li>
                                    <li>• Control live streams</li>
                                    <li>• System settings</li>
                                </ul>
                            </div>
                            <div class="col-md-4">
                                <h6 class="text-warning"><i class="fas fa-user-edit me-2"></i>Editor</h6>
                                <ul class="list-unstyled small">
                                    <li>• Manage news articles</li>
                                    <li>• Edit and publish news</li>
                                    <li>• Manage comments</li>
                                    <li>• Manage polls</li>
                                    <li>• View statistics</li>
                                </ul>
                            </div>
                            <div class="col-md-4">
                                <h6 class="text-info"><i class="fas fa-user me-2"></i>Reporter</h6>
                                <ul class="list-unstyled small">
                                    <li>• Create news articles</li>
                                    <li>• Edit own articles</li>
                                    <li>• Submit for approval</li>
                                    <li>• View own statistics</li>
                                    <li>• Basic permissions</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Update pending count
        document.addEventListener('DOMContentLoaded', function() {
            const pendingCount = <?php echo $pending_count; ?>;
            const pendingBadge = document.getElementById('pendingCount');
            if (pendingBadge) {
                pendingBadge.textContent = pendingCount + ' Pending';
                if (pendingCount > 0) {
                    pendingBadge.classList.remove('bg-info');
                    pendingBadge.classList.add('bg-warning');
                }
            }
        });
        
        // Approve application
        function approveApplication(appId, userName, appliedRole) {
            const notes = prompt(`Approve ${userName}'s application for ${appliedRole} role?\n\nOptional admin notes:`);
            if (notes !== null) {
                const url = new URL(window.location);
                url.searchParams.set('app_action', 'approve');
                url.searchParams.set('app_id', appId);
                if (notes.trim()) {
                    url.searchParams.set('admin_notes', notes.trim());
                }
                window.location.href = url.toString();
            }
        }
        
        // Reject application
        function rejectApplication(appId, userName) {
            const notes = prompt(`Reject ${userName}'s application?\n\nPlease provide reason for rejection:`);
            if (notes !== null && notes.trim()) {
                const url = new URL(window.location);
                url.searchParams.set('app_action', 'reject');
                url.searchParams.set('app_id', appId);
                url.searchParams.set('admin_notes', notes.trim());
                window.location.href = url.toString();
            } else if (notes !== null) {
                alert('Please provide a reason for rejection.');
            }
        }
        
        // View application details
        function viewApplicationDetails(appId) {
            fetch('api/get_application_details.php?id=' + appId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showApplicationModal(data.application);
                    } else {
                        alert('Error loading application details: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading application details. Please try again.');
                });
        }
        
        // Show application details modal
        function showApplicationModal(application) {
            const appData = application.application_data;
            
            const modalHtml = `
                <div class="modal fade" id="applicationDetailsModal" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">
                                    <i class="fas fa-user-tie me-2"></i>
                                    Application Details - ${application.name}
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6>Applicant Information</h6>
                                        <p><strong>Name:</strong> ${application.name}</p>
                                        <p><strong>Email:</strong> ${application.email}</p>
                                        <p><strong>Applied Role:</strong> <span class="badge bg-${application.applied_role === 'editor' ? 'warning' : 'info'}">${application.applied_role}</span></p>
                                        <p><strong>Applied:</strong> ${new Date(application.created_at).toLocaleDateString()}</p>
                                        <p><strong>Status:</strong> <span class="badge bg-${application.status === 'pending' ? 'warning' : (application.status === 'approved' ? 'success' : 'danger')}">${application.status}</span></p>
                                    </div>
                                    <div class="col-md-6">
                                        <h6>Application Details</h6>
                                        <p><strong>Experience:</strong><br>${appData.experience || 'Not provided'}</p>
                                        <p><strong>Qualifications:</strong><br>${appData.qualifications || 'Not provided'}</p>
                                        <p><strong>Availability:</strong> ${appData.availability || 'Not provided'}</p>
                                    </div>
                                </div>
                                
                                <hr>
                                
                                <h6>Reason for Applying</h6>
                                <p>${appData.reason || 'Not provided'}</p>
                                
                                ${appData.samples ? `
                                <h6>Sample Work/Portfolio</h6>
                                <p>${appData.samples}</p>
                                ` : ''}
                                
                                ${application.cv_file_name ? `
                                <hr>
                                <h6>CV/Resume</h6>
                                <p>
                                    <strong>File:</strong> ${application.cv_file_name}<br>
                                    <strong>Size:</strong> ${formatFileSize(application.cv_file_size)}<br>
                                    <a href="../${application.cv_file_path}" target="_blank" class="btn btn-sm btn-primary mt-2">
                                        <i class="fas fa-download me-1"></i>Download CV
                                    </a>
                                </p>
                                ` : ''}
                                
                                ${application.admin_notes ? `
                                <hr>
                                <h6>Admin Notes</h6>
                                <p>${application.admin_notes}</p>
                                ` : ''}
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                ${application.status === 'pending' ? `
                                    <button type="button" class="btn btn-success" onclick="approveApplication(${application.id}, '${application.name}', '${application.applied_role}')">
                                        <i class="fas fa-check me-2"></i>Approve
                                    </button>
                                    <button type="button" class="btn btn-danger" onclick="rejectApplication(${application.id}, '${application.name}')">
                                        <i class="fas fa-times me-2"></i>Reject
                                    </button>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Remove existing modal if present
            const existingModal = document.getElementById('applicationDetailsModal');
            if (existingModal) {
                existingModal.remove();
            }
            
            // Add modal to page and show it
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            const modal = new bootstrap.Modal(document.getElementById('applicationDetailsModal'));
            modal.show();
            
            // Remove modal from DOM when hidden
            document.getElementById('applicationDetailsModal').addEventListener('hidden.bs.modal', function() {
                this.remove();
            });
        }
        
        // Format file size
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
    </script>
</body>
</html>

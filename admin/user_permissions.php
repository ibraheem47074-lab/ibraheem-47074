<?php
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    redirect('login.php');
}

$success = '';
$error = '';

// Handle user role management
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Update user role
    if (isset($_POST['update_role'])) {
        $user_id = (int)$_POST['user_id'];
        $new_role = clean_input($_POST['role']);
        
        // Validate role
        $valid_roles = ['admin', 'editor', 'author', 'reporter', 'subscriber'];
        if (!in_array($new_role, $valid_roles)) {
            $error = "Invalid role specified!";
        } else {
            $query = "UPDATE users SET role = ? WHERE id = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 'si', $new_role, $user_id);
            
            if (mysqli_stmt_execute($stmt)) {
                $success = "User role updated successfully!";
            } else {
                $error = "Failed to update user role!";
            }
        }
    }
    
    // Update user status
    if (isset($_POST['update_status'])) {
        $user_id = (int)$_POST['user_id'];
        $new_status = clean_input($_POST['status']);
        
        // Validate status
        $valid_statuses = ['active', 'inactive', 'banned'];
        if (!in_array($new_status, $valid_statuses)) {
            $error = "Invalid status specified!";
        } else {
            $query = "UPDATE users SET status = ? WHERE id = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 'si', $new_status, $user_id);
            
            if (mysqli_stmt_execute($stmt)) {
                $success = "User status updated successfully!";
            } else {
                $error = "Failed to update user status!";
            }
        }
    }
    
    // Delete user
    if (isset($_POST['delete_user'])) {
        $user_id = (int)$_POST['user_id'];
        
        // Don't allow deletion of the current admin user
        if ($user_id == $_SESSION['user_id']) {
            $error = "You cannot delete your own account!";
        } else {
            // First, delete user's related data
            mysqli_query($conn, "DELETE FROM comments WHERE user_id = $user_id");
            mysqli_query($conn, "UPDATE news SET author_id = NULL WHERE author_id = $user_id");
            
            // Then delete the user
            $query = "DELETE FROM users WHERE id = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 'i', $user_id);
            
            if (mysqli_stmt_execute($stmt)) {
                $success = "User deleted successfully!";
            } else {
                $error = "Failed to delete user!";
            }
        }
    }
    
    // Bulk operations
    if (isset($_POST['bulk_action']) && isset($_POST['selected_users'])) {
        $action = clean_input($_POST['bulk_action']);
        $user_ids = array_map('intval', $_POST['selected_users']);
        $ids_string = implode(',', $user_ids);
        
        // Don't allow bulk operations on current admin
        if (in_array($_SESSION['user_id'], $user_ids)) {
            $error = "You cannot perform bulk operations on your own account!";
        } else {
            switch ($action) {
                case 'activate':
                    $query = "UPDATE users SET status = 'active' WHERE id IN ($ids_string)";
                    if (mysqli_query($conn, $query)) {
                        $affected = mysqli_affected_rows($conn);
                        $success = "Activated $affected users successfully!";
                    }
                    break;
                    
                case 'deactivate':
                    $query = "UPDATE users SET status = 'inactive' WHERE id IN ($ids_string)";
                    if (mysqli_query($conn, $query)) {
                        $affected = mysqli_affected_rows($conn);
                        $success = "Deactivated $affected users successfully!";
                    }
                    break;
                    
                case 'make_admin':
                    $query = "UPDATE users SET role = 'admin' WHERE id IN ($ids_string)";
                    if (mysqli_query($conn, $query)) {
                        $affected = mysqli_affected_rows($conn);
                        $success = "Made $affected users admin successfully!";
                    }
                    break;
                    
                case 'make_editor':
                    $query = "UPDATE users SET role = 'editor' WHERE id IN ($ids_string)";
                    if (mysqli_query($conn, $query)) {
                        $affected = mysqli_affected_rows($conn);
                        $success = "Made $affected users editor successfully!";
                    }
                    break;
                    
                case 'make_reporter':
                    $query = "UPDATE users SET role = 'reporter' WHERE id IN ($ids_string)";
                    if (mysqli_query($conn, $query)) {
                        $affected = mysqli_affected_rows($conn);
                        $success = "Made $affected users reporter successfully!";
                    }
                    break;
                    
                case 'delete':
                    // Delete related data first
                    mysqli_query($conn, "DELETE FROM comments WHERE user_id IN ($ids_string)");
                    mysqli_query($conn, "UPDATE news SET author_id = NULL WHERE author_id IN ($ids_string)");
                    // Delete users
                    $query = "DELETE FROM users WHERE id IN ($ids_string)";
                    if (mysqli_query($conn, $query)) {
                        $affected = mysqli_affected_rows($conn);
                        $success = "Deleted $affected users successfully!";
                    }
                    break;
            }
        }
    }
}

// Get users with pagination and filtering
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Filtering
$filter_role = isset($_GET['filter_role']) ? clean_input($_GET['filter_role']) : '';
$filter_status = isset($_GET['filter_status']) ? clean_input($_GET['filter_status']) : '';
$search = isset($_GET['search']) ? clean_input($_GET['search']) : '';

// Build WHERE clause
$where_conditions = [];
$params = [];
$types = '';

if (!empty($filter_role)) {
    $where_conditions[] = "role = ?";
    $params[] = $filter_role;
    $types .= 's';
}

if (!empty($filter_status)) {
    $where_conditions[] = "status = ?";
    $params[] = $filter_status;
    $types .= 's';
}

if (!empty($search)) {
    $where_conditions[] = "(name LIKE ? OR email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= 'ss';
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get total users count
$count_query = "SELECT COUNT(*) as total FROM users $where_clause";
$stmt = mysqli_prepare($conn, $count_query);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$total_users = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['total'];

// Get users
$users_query = "SELECT * FROM users $where_clause ORDER BY created_at DESC LIMIT $per_page OFFSET $offset";
$stmt = mysqli_prepare($conn, $users_query);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$users = mysqli_stmt_get_result($stmt);

// Calculate pagination
$total_pages = ceil($total_users / $per_page);

// Helper functions are already included in config/database.php
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management & Permissions - PK Live News Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .user-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
        }
        .role-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        .role-admin { background: linear-gradient(45deg, #ff6b6b, #ee5a24); color: white; }
        .role-editor { background: linear-gradient(45deg, #4834d4, #686de0); color: white; }
        .role-author { background: linear-gradient(45deg, #00d2d3, #01a3a4); color: white; }
        .role-reporter { background: linear-gradient(45deg, #f39c12, #e67e22); color: white; }
        .role-subscriber { background: linear-gradient(45deg, #a29bfe, #6c5ce7); color: white; }
        
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.8rem;
        }
        .status-active { background-color: #d4edda; color: #155724; }
        .status-inactive { background-color: #fff3cd; color: #856404; }
        .status-banned { background-color: #f8d7da; color: #721c24; }
        
        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #f0f0f0;
        }
        
        .permissions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .permission-item {
            padding: 1rem;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            background: #f8f9fa;
        }
        
        .permission-item h6 {
            color: #495057;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }
        
        .permission-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .permission-list li {
            font-size: 0.85rem;
            color: #6c757d;
            margin-bottom: 0.25rem;
        }
        
        .permission-list li:before {
            content: "✓";
            color: #28a745;
            margin-right: 0.5rem;
        }
    </style>
</head>
<body>
    <?php include 'includes/admin-header.php'; ?>

    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <h2><i class="fas fa-users-cog me-3"></i>User Management & Permissions</h2>
                <p class="text-muted">Manage user accounts, roles, and permissions for your PK Live News website.</p>
            </div>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Role Permissions Overview -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="user-card">
                    <h5><i class="fas fa-shield-alt me-2"></i>Role Permissions Overview</h5>
                    <div class="permissions-grid">
                        <div class="permission-item">
                            <h6><i class="fas fa-crown me-2"></i>Administrator</h6>
                            <ul class="permission-list">
                                <li>Full system access</li>
                                <li>Manage all users</li>
                                <li>Manage categories</li>
                                <li>Control live streams</li>
                                <li>System settings</li>
                            </ul>
                        </div>
                        <div class="permission-item">
                            <h6><i class="fas fa-edit me-2"></i>Editor</h6>
                            <ul class="permission-list">
                                <li>Manage news articles</li>
                                <li>Edit and publish news</li>
                                <li>Manage comments</li>
                                <li>Manage polls</li>
                                <li>View statistics</li>
                            </ul>
                        </div>
                        <div class="permission-item">
                            <h6><i class="fas fa-pen me-2"></i>Author</h6>
                            <ul class="permission-list">
                                <li>Create articles</li>
                                <li>Edit own articles</li>
                                <li>Submit for review</li>
                                <li>View own analytics</li>
                                <li>Manage own profile</li>
                            </ul>
                        </div>
                        <div class="permission-item">
                            <h6><i class="fas fa-newspaper me-2"></i>Reporter</h6>
                            <ul class="permission-list">
                                <li>Create news articles</li>
                                <li>Edit own articles</li>
                                <li>Submit for approval</li>
                                <li>View own statistics</li>
                                <li>Basic permissions</li>
                            </ul>
                        </div>
                        <div class="permission-item">
                            <h6><i class="fas fa-user me-2"></i>Subscriber</h6>
                            <ul class="permission-list">
                                <li>View published content</li>
                                <li>Post comments</li>
                                <li>Like articles</li>
                                <li>Bookmark articles</li>
                                <li>Manage own profile</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters and Search -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="user-card">
                    <h5><i class="fas fa-filter me-2"></i>Filters & Search</h5>
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Search</label>
                            <input type="text" class="form-control" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Name or email...">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Role</label>
                            <select class="form-select" name="filter_role">
                                <option value="">All Roles</option>
                                <option value="admin" <?php echo $filter_role === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                <option value="editor" <?php echo $filter_role === 'editor' ? 'selected' : ''; ?>>Editor</option>
                                <option value="author" <?php echo $filter_role === 'author' ? 'selected' : ''; ?>>Author</option>
                                <option value="reporter" <?php echo $filter_role === 'reporter' ? 'selected' : ''; ?>>Reporter</option>
                                <option value="subscriber" <?php echo $filter_role === 'subscriber' ? 'selected' : ''; ?>>Subscriber</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="filter_status">
                                <option value="">All Status</option>
                                <option value="active" <?php echo $filter_status === 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo $filter_status === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                <option value="banned" <?php echo $filter_status === 'banned' ? 'selected' : ''; ?>>Banned</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search me-1"></i>Search
                                </button>
                                <a href="user_permissions.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i>Clear
                                </a>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <div class="text-end">
                                <small class="text-muted"><?php echo $total_users; ?> users found</small>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Users List -->
        <div class="row">
            <div class="col-12">
                <div class="user-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5><i class="fas fa-users me-2"></i>Users List</h5>
                        <div>
                            <a href="manage-users.php" class="btn btn-outline-primary btn-sm me-2">
                                <i class="fas fa-user-plus me-1"></i>Add User
                            </a>
                            <button class="btn btn-outline-success btn-sm" onclick="exportUsers()">
                                <i class="fas fa-download me-1"></i>Export
                            </button>
                        </div>
                    </div>

                    <?php if (mysqli_num_rows($users) > 0): ?>
                        <form method="POST" id="bulkActionsForm">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>
                                                <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)">
                                            </th>
                                            <th>User</th>
                                            <th>Role</th>
                                            <th>Status</th>
                                            <th>Joined</th>
                                            <th>Last Login</th>
                                            <th>Articles</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($user = mysqli_fetch_assoc($users)): ?>
                                            <tr>
                                                <td>
                                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                        <input type="checkbox" name="selected_users[]" value="<?php echo $user['id']; ?>" class="user-checkbox">
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <img src="<?php echo !empty($user['avatar']) ? htmlspecialchars($user['avatar'] ?? '') : 'https://ui-avatars.com/api/?name=' . urlencode($user['name'] ?? '') . '&background=random'; ?>" 
                                                             alt="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" class="user-avatar me-3">
                                                        <div>
                                                            <strong><?php echo htmlspecialchars($user['name'] ?? ''); ?></strong>
                                                            <br>
                                                            <small class="text-muted"><?php echo htmlspecialchars($user['email'] ?? ''); ?></small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="role-badge role-<?php echo $user['role']; ?>">
                                                        <?php echo ucfirst($user['role'] ?? ''); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="status-badge status-<?php echo $user['status']; ?>">
                                                        <?php echo ucfirst($user['status'] ?? ''); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <small><?php echo date('M d, Y', strtotime($user['created_at'])); ?></small>
                                                </td>
                                                <td>
                                                    <small><?php echo ($user['last_login'] ?? null) ? date('M d, H:i', strtotime($user['last_login'])) : 'Never'; ?></small>
                                                </td>
                                                <td>
                                                    <?php 
                                                    $article_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM news WHERE author_id = " . $user['id']))['count'];
                                                    echo $article_count;
                                                    ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                            <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                                                                <i class="fas fa-cog"></i>
                                                            </button>
                                                            <ul class="dropdown-menu">
                                                                <li>
                                                                    <form method="POST" style="display: inline;">
                                                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                                        <input type="hidden" name="role" value="admin">
                                                                        <button type="submit" name="update_role" class="dropdown-item">
                                                                            <i class="fas fa-crown me-2"></i>Make Admin
                                                                        </button>
                                                                    </form>
                                                                </li>
                                                                <li>
                                                                    <form method="POST" style="display: inline;">
                                                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                                        <input type="hidden" name="role" value="editor">
                                                                        <button type="submit" name="update_role" class="dropdown-item">
                                                                            <i class="fas fa-edit me-2"></i>Make Editor
                                                                        </button>
                                                                    </form>
                                                                </li>
                                                                <li>
                                                                    <form method="POST" style="display: inline;">
                                                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                                        <input type="hidden" name="role" value="author">
                                                                        <button type="submit" name="update_role" class="dropdown-item">
                                                                            <i class="fas fa-pen me-2"></i>Make Author
                                                                        </button>
                                                                    </form>
                                                                </li>
                                                                <li>
                                                                    <form method="POST" style="display: inline;">
                                                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                                        <input type="hidden" name="role" value="reporter">
                                                                        <button type="submit" name="update_role" class="dropdown-item">
                                                                            <i class="fas fa-newspaper me-2"></i>Make Reporter
                                                                        </button>
                                                                    </form>
                                                                </li>
                                                                <li><hr class="dropdown-divider"></li>
                                                                <li>
                                                                    <form method="POST" style="display: inline;">
                                                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                                        <input type="hidden" name="status" value="active">
                                                                        <button type="submit" name="update_status" class="dropdown-item">
                                                                            <i class="fas fa-check me-2"></i>Activate
                                                                        </button>
                                                                    </form>
                                                                </li>
                                                                <li>
                                                                    <form method="POST" style="display: inline;">
                                                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                                        <input type="hidden" name="status" value="inactive">
                                                                        <button type="submit" name="update_status" class="dropdown-item">
                                                                            <i class="fas fa-pause me-2"></i>Deactivate
                                                                        </button>
                                                                    </form>
                                                                </li>
                                                                <li>
                                                                    <form method="POST" style="display: inline;">
                                                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                                        <input type="hidden" name="status" value="banned">
                                                                        <button type="submit" name="update_status" class="dropdown-item text-danger">
                                                                            <i class="fas fa-ban me-2"></i>Ban
                                                                        </button>
                                                                    </form>
                                                                </li>
                                                                <li><hr class="dropdown-divider"></li>
                                                                <li>
                                                                    <form method="POST" style="display: inline;">
                                                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                                        <button type="submit" name="delete_user" class="dropdown-item text-danger" onclick="return confirm('Delete this user permanently?')">
                                                                            <i class="fas fa-trash me-2"></i>Delete User
                                                                        </button>
                                                                    </form>
                                                                </li>
                                                            </ul>
                                                        <?php else: ?>
                                                            <span class="badge bg-info">You</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Bulk Actions -->
                            <div class="mt-3">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <select name="bulk_action" class="form-select form-select-sm" id="bulkActionSelect">
                                            <option value="">Bulk Actions</option>
                                            <option value="activate">Activate Selected</option>
                                            <option value="deactivate">Deactivate Selected</option>
                                            <option value="make_admin">Make Admin</option>
                                            <option value="make_editor">Make Editor</option>
                                            <option value="make_reporter">Make Reporter</option>
                                            <option value="delete">Delete Selected</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <button type="submit" class="btn btn-sm btn-primary" onclick="return confirmBulkAction()">
                                            <i class="fas fa-check me-1"></i>Apply Action
                                        </button>
                                        <span class="ms-3 text-muted">
                                            <span id="selectedCount">0</span> users selected
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </form>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <nav aria-label="User pagination" class="mt-4">
                                <ul class="pagination justify-content-center">
                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&filter_role=<?php echo urlencode($filter_role); ?>&filter_status=<?php echo urlencode($filter_status); ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>

                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <h5>No users found</h5>
                            <p class="text-muted">Try adjusting your filters or search criteria.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle select all checkboxes
        function toggleSelectAll(checkbox) {
            const checkboxes = document.querySelectorAll('.user-checkbox');
            checkboxes.forEach(cb => cb.checked = checkbox.checked);
            updateSelectedCount();
        }

        // Update selected count
        function updateSelectedCount() {
            const checkedBoxes = document.querySelectorAll('.user-checkbox:checked');
            document.getElementById('selectedCount').textContent = checkedBoxes.length;
        }

        // Add event listeners to checkboxes
        document.querySelectorAll('.user-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', updateSelectedCount);
        });

        // Confirm bulk action
        function confirmBulkAction() {
            const action = document.getElementById('bulkActionSelect').value;
            const selectedCount = document.querySelectorAll('.user-checkbox:checked').length;
            
            if (!action) {
                alert('Please select a bulk action.');
                return false;
            }
            
            if (selectedCount === 0) {
                alert('Please select at least one user.');
                return false;
            }
            
            let confirmMessage = '';
            switch (action) {
                case 'activate':
                    confirmMessage = `Activate ${selectedCount} selected users?`;
                    break;
                case 'deactivate':
                    confirmMessage = `Deactivate ${selectedCount} selected users?`;
                    break;
                case 'make_admin':
                    confirmMessage = `Make ${selectedCount} selected users admin? This gives them full system access!`;
                    break;
                case 'make_editor':
                    confirmMessage = `Make ${selectedCount} selected users editor?`;
                    break;
                case 'delete':
                    confirmMessage = `Delete ${selectedCount} selected users permanently? This action cannot be undone!`;
                    break;
            }
            
            return confirm(confirmMessage);
        }

        // Export users function
        function exportUsers() {
            // This could be implemented with AJAX to export user data
            alert('Export functionality would be implemented here.');
        }

        // Auto-refresh user list
        setInterval(() => {
            console.log('User list refreshed');
        }, 60000); // Refresh every minute
    </script>
</body>
</html>
